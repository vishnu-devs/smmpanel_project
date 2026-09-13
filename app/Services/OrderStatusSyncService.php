<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Provider;
use App\Models\OrderProcessingLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderStatusSyncService
{
    /**
     * Sync active orders and un-refunded partial orders with SMM API providers.
     */
    public static function sync(?int $userId = null, bool $force = false): void
    {
        $cacheKey = $userId ? "order_sync_user_{$userId}" : "order_sync_all";
        
        // Prevent calling SMM APIs too frequently unless forced
        if (!$force && Cache::has($cacheKey)) {
            return;
        }
        
        Cache::put($cacheKey, true, 30);

        // Fetch active orders (pending, processing, in_progress) OR un-refunded partial orders
        $query = Order::where(function ($q) {
            $q->whereIn('status', ['pending', 'processing', 'in_progress'])
              ->orWhere(function ($sub) {
                  $sub->where('status', 'partial')
                      ->where('refunded', false);
              });
        })->whereNotNull('provider_order_id')
          ->where('provider_order_id', '!=', '');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $orders = $query->with('service')->get();

        if ($orders->isEmpty()) {
            return;
        }

        // Pre-fetch actual provider_id from OrderProcessingLog for failover or reassigned orders
        $orderIds = $orders->pluck('id')->toArray();
        $logProviderMap = OrderProcessingLog::whereIn('order_id', $orderIds)
            ->where('action', 'api_response')
            ->whereNotNull('provider_id')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('order_id')
            ->pluck('provider_id', 'order_id')
            ->toArray();

        $anyLogMap = OrderProcessingLog::whereIn('order_id', $orderIds)
            ->whereNotNull('provider_id')
            ->orderBy('id', 'desc')
            ->get()
            ->unique('order_id')
            ->pluck('provider_id', 'order_id')
            ->toArray();

        // Group orders by actual provider (processing log provider ID first, falling back to service provider ID, then any log)
        $grouped = $orders->groupBy(function ($ord) use ($logProviderMap, $anyLogMap) {
            return $logProviderMap[$ord->id] 
                ?? ($ord->service ? $ord->service->provider_id : null) 
                ?? ($anyLogMap[$ord->id] ?? null);
        });

        foreach ($grouped as $providerId => $providerOrders) {
            if (!$providerId) continue;
            
            $provider = Provider::where('status', 'active')->find($providerId);
            if (!$provider) continue;

            // Map clean provider order IDs -> Order objects to handle '#' or whitespace in DB
            $idOrderMap = [];
            $cleanBatchIds = [];

            foreach ($providerOrders as $order) {
                $rawId = (string)$order->provider_order_id;
                $cleanId = preg_replace('/[^0-9a-zA-Z]/', '', $rawId);
                if ($cleanId !== '') {
                    $idOrderMap[$cleanId] = $order;
                    $idOrderMap[$rawId] = $order;
                    $cleanBatchIds[] = $cleanId;
                }
            }

            if (empty($cleanBatchIds)) continue;

            // Smaller batch chunks (25 orders max) for fast provider API response and no timeout
            $batches = array_chunk(array_unique($cleanBatchIds), 25);

            foreach ($batches as $batchIds) {
                try {
                    $apiPayload = [
                        'key' => trim($provider->api_key),
                        'action' => 'status',
                        'orders' => implode(',', $batchIds)
                    ];
                    if (count($batchIds) === 1) {
                        $apiPayload['order'] = $batchIds[0];
                    }

                    $data = self::postApi(trim($provider->api_url), $apiPayload);

                    if (is_array($data)) {
                        $searchData = isset($data['orders']) && is_array($data['orders']) 
                            ? $data['orders'] 
                            : (isset($data['data']) && is_array($data['data']) ? $data['data'] : $data);

                        foreach ($batchIds as $bid) {
                            $order = $idOrderMap[$bid] ?? null;
                            if (!$order) continue;

                            $info = $searchData[$bid] ?? $searchData[(string)$bid] ?? $searchData[(int)$bid] ?? null;

                            if (!$info) {
                                $info = $searchData['#' . $bid] ?? null;
                            }

                            // Single order status response fallback (e.g. StarOfSMM returning object directly)
                            if (!$info && count($batchIds) === 1 && (isset($data['status']) || isset($data['order_status']) || isset($data['remains']))) {
                                $info = $data;
                            }

                            if (is_array($info)) {
                                $rawStatus = (string)($info['status'] ?? $info['order_status'] ?? '');
                                $rawStatusClean = strtolower(trim($rawStatus));
                                
                                $s = str_replace(['-', '_'], ' ', $rawStatusClean);
                                $s = trim(preg_replace('/\s+/', ' ', $s));

                                if (str_contains($s, 'partial')) {
                                    $newStatus = 'partial';
                                } elseif (str_contains($s, 'cancel') || str_contains($s, 'refund') || str_contains($s, 'fail') || str_contains($s, 'reject') || str_contains($s, 'abort')) {
                                    $newStatus = 'canceled';
                                } elseif (str_contains($s, 'completed') || str_contains($s, 'complete') || str_contains($s, 'success') || str_contains($s, 'done') || str_contains($s, 'finish')) {
                                    $newStatus = 'completed';
                                } elseif (str_contains($s, 'progress') || str_contains($s, 'run') || $s === 'active' || $s === 'working') {
                                    $newStatus = 'in_progress';
                                } elseif (str_contains($s, 'process') || str_contains($s, 'prepar')) {
                                    $newStatus = 'processing';
                                } elseif (str_contains($s, 'pend') || str_contains($s, 'wait') || str_contains($s, 'queue') || str_contains($s, 'hold') || $s === 'new') {
                                    $newStatus = 'pending';
                                } else {
                                    $newStatus = null;
                                }

                                $hasChanges = false;

                                $startCountRaw = $info['start_count'] ?? $info['start_number'] ?? $info['startcount'] ?? $info['start_cnt'] ?? null;
                                $remainsRaw = $info['remains'] ?? $info['remains_count'] ?? $info['remain'] ?? $info['left'] ?? null;

                                if ($startCountRaw !== null && is_numeric($startCountRaw)) {
                                    $parsedStartCount = (int)$startCountRaw;
                                    if ($order->start_count !== $parsedStartCount) {
                                        $order->start_count = $parsedStartCount;
                                        $hasChanges = true;
                                    }
                                }

                                if ($remainsRaw !== null && is_numeric($remainsRaw)) {
                                    $parsedRemains = (int)$remainsRaw;
                                    if ($order->remains !== $parsedRemains) {
                                        $order->remains = $parsedRemains;
                                        $hasChanges = true;
                                    }
                                }

                                // Auto-reset remains to 0 if order completed
                                if ($newStatus === 'completed' && $order->remains !== 0) {
                                    $order->remains = 0;
                                    $hasChanges = true;
                                }

                                if ($newStatus && $order->status !== $newStatus) {
                                    $order->status = $newStatus;
                                    $hasChanges = true;
                                }

                                if ($newStatus === 'canceled') {
                                    if (!$order->refunded) {
                                        RefundService::process($order, 'canceled');
                                        $hasChanges = false;
                                    }
                                } elseif ($newStatus === 'partial') {
                                    $remainsVal = ($remainsRaw !== null && is_numeric($remainsRaw)) 
                                        ? (int)$remainsRaw 
                                        : (int)$order->remains;

                                    if ($remainsVal <= 0 && $order->quantity > 0) {
                                        if ($startCountRaw !== null && is_numeric($startCountRaw) && (int)$startCountRaw > 0 && (int)$order->start_count > 0) {
                                            $delivered = max(0, (int)$startCountRaw - (int)$order->start_count);
                                            $remainsVal = max(1, (int)$order->quantity - $delivered);
                                        } else {
                                            $remainsVal = (int)$order->quantity;
                                        }
                                    }

                                    if ($remainsVal > 0 && !$order->refunded) {
                                        RefundService::process($order, 'partial', $remainsVal);
                                        $hasChanges = false;
                                    }
                                }

                                if ($hasChanges) {
                                    $order->save();
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("OrderStatusSyncService error for provider {$provider->name}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Optimized cURL HTTP POST helper with IPv4 forcing, HTTP 1.1, SSL bypass, and fast timeouts.
     */
    private static function postApi(string $url, array $payload): ?array
    {
        // 1. Primary High-Performance Native cURL Engine with IPv4 Forced
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            // Critical options to eliminate 15s cPanel timeouts:
            if (defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); // Force IPv4 routing immediately
            }
            if (defined('CURL_HTTP_VERSION_1_1')) {
                curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1); // HTTP/1.1 compatibility
            }
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
            curl_setopt($ch, CURLOPT_TIMEOUT, 8);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                'Accept: application/json, text/plain, */*',
                'Accept-Language: en-US,en;q=0.9',
                'Connection: keep-alive'
            ]);
            
            $body = curl_exec($ch);
            $err = curl_error($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($body && !$err && $code >= 200 && $code < 400) {
                $json = json_decode($body, true);
                if (is_array($json)) {
                    return $json;
                }
            } else {
                if ($err) {
                    Log::warning("OrderStatusSyncService: cURL Primary warning for {$url}: {$err} (HTTP {$code})");
                }
            }
        } catch (\Throwable $e) {
            Log::warning("OrderStatusSyncService: cURL Primary exception for {$url}: " . $e->getMessage());
        }

        // 2. Secondary Fallback: Laravel Http Facade
        try {
            $response = Http::timeout(20)
                ->withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
                    'Accept' => 'application/json, text/plain, */*'
                ])
                ->asForm()
                ->post($url, $payload);

            if ($response->successful()) {
                $json = $response->json();
                if (is_array($json)) {
                    return $json;
                }
            }
        } catch (\Throwable $e) {
            Log::error("OrderStatusSyncService: Laravel Http fallback error for {$url}: " . $e->getMessage());
        }

        return null;
    }
}
