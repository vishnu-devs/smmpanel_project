<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Provider;
use App\Models\Service;
use App\Models\OrderProcessingLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderProcessor
{
    /**
     * Non-retryable error patterns — retrying these will never succeed.
     * These trigger immediate failover to the next provider.
     */
    private static array $nonRetryablePatterns = [
        'not usable with api',
        'out of balance',
        'recharge your balance',
        'service not found',
        'service is disabled',
        'invalid service',
    ];

    /**
     * Route and place order at reseller provider, handling retries and auto-failovers.
     */
    public static function dispatch(Order $order): bool
    {
        $service = $order->service;
        if (!$service) {
            self::log($order->id, null, 'error', null, null, 'Service relation missing for order.', 0, 0);
            return false;
        }

        // --- Real-time Financial Loss Protection Guard ---
        // Verify that the rate customer paid is NOT less than provider's cost rate
        if ($order->quantity > 0 && $service->provider_rate > 0) {
            $userPaidRatePerK = ($order->charge / $order->quantity) * 1000;

            if ($service->provider_rate > ($userPaidRatePerK + 0.001)) {
                Log::warning("Loss protection triggered on Order #{$order->id}: Provider rate (₹{$service->provider_rate}/1K) is higher than user paid rate (₹" . number_format($userPaidRatePerK, 4) . "/1K). Deactivating Service #{$service->id} and auto-refunding customer.");

                // Deactivate loss-making service immediately
                $service->status = 'inactive';
                $service->save();

                // Auto cancel order and 100% refund customer
                try {
                    \App\Services\RefundService::process($order, 'canceled');
                    self::log($order->id, null, 'error', null, null, "Financial Loss Protection: Provider rate (₹{$service->provider_rate}/1K) > User paid rate (₹" . number_format($userPaidRatePerK, 4) . "/1K). Service auto-deactivated and order refunded.", 0, 0);
                } catch (\Exception $e) {
                    Log::error("Failed to auto-refund loss-protected order #{$order->id}: " . $e->getMessage());
                    $order->status = 'failed';
                    $order->save();
                }

                return false;
            }
        }

        // Try primary provider first
        $providersList = [];
        if ($service->provider_id && $service->provider_service_id) {
            $primaryProvider = Provider::where('status', 'active')->find($service->provider_id);
            if ($primaryProvider) {
                $providersList[] = [
                    'provider' => $primaryProvider,
                    'service_id' => $service->provider_service_id,
                    'is_primary' => true
                ];
            }
        }

        // Load failover providers
        if ($service->failover_mappings) {
            $mappings = is_string($service->failover_mappings) ? json_decode($service->failover_mappings, true) : $service->failover_mappings;
            if (is_array($mappings)) {
                // Sort by priority (ascending)
                usort($mappings, function ($a, $b) {
                    return ($a['priority'] ?? 1) <=> ($b['priority'] ?? 1);
                });

                foreach ($mappings as $m) {
                    $prov = Provider::where('status', 'active')->find($m['provider_id']);
                    if ($prov) {
                        $providersList[] = [
                            'provider' => $prov,
                            'service_id' => $m['provider_service_id'],
                            'is_primary' => false
                        ];
                    }
                }
            }
        }

        if (empty($providersList)) {
            self::log($order->id, null, 'error', null, null, 'No active providers mapped to this service.', 0, 0);
            return false;
        }

        // Track whether every single provider returned a non-retryable error
        $allNonRetryable = true;

        // Loop and try to place order
        foreach ($providersList as $candidate) {
            $provider = $candidate['provider'];
            $providerServiceId = $candidate['service_id'];
            $isPrimary = $candidate['is_primary'];

            $timeout = (int)($provider->timeout ?: 10);
            $maxRetries = (int)($provider->retry_count ?: 3);
            
            // Apply optional delivery bonus percentage buffer (e.g. 5% extra) to prevent under-delivery from provider drops
            $bonusPercent = (float)\App\Models\Setting::get('order_delivery_bonus_percent', 0);
            $providerQuantity = (int)$order->quantity;
            if ($bonusPercent > 0 && empty($order->comments)) {
                $providerQuantity = (int)ceil($order->quantity * (1 + ($bonusPercent / 100)));
            }

            $url = $provider->api_url;
            $payload = [
                'key' => $provider->api_key,
                'action' => 'add',
                'service' => $providerServiceId,
                'link' => $order->link,
                'quantity' => $providerQuantity,
            ];

            // Include custom comments for Custom Comments services
            if (!empty($order->comments)) {
                $payload['comments'] = $order->comments;
            }

            $retry = 0;
            $success = false;
            $responseJson = null;
            $errorMsg = null;
            $elapsedMs = 0;
            $isNonRetryable = false;

            while ($retry < $maxRetries && !$success) {
                $startTime = microtime(true);
                try {
                    $response = Http::timeout($timeout)->asForm()->post($url, $payload);
                    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
                    
                    if ($response->successful()) {
                        $responseJson = $response->json();
                        if (is_array($responseJson) && isset($responseJson['order'])) {
                            $success = true;
                        } else {
                            $errorMsg = 'API Response missing order parameter. Payload: ' . $response->body();

                            // Check if this is a non-retryable error — no point retrying
                            if (self::isNonRetryableError($response->body())) {
                                $isNonRetryable = true;
                                Log::warning("Non-retryable error from Provider ID {$provider->id}: {$response->body()}. Skipping retries.");

                                // Auto-deactivate service on panel if provider explicitly states service is missing/disabled
                                if (self::isServiceMissingError($response->body())) {
                                    $service->status = 'inactive';
                                    $service->save();
                                    Log::info("Auto-deactivated Service ID {$service->id} because provider returned: {$response->body()}");
                                }

                                break;
                            }
                        }
                    } else {
                        $errorMsg = 'HTTP failure status ' . $response->status() . ': ' . $response->body();
                    }
                } catch (\Exception $e) {
                    $elapsedMs = (int)((microtime(true) - $startTime) * 1000);
                    $errorMsg = 'Connection exception: ' . $e->getMessage();
                }

                if (!$success) {
                    $retry++;
                    // Wait a tiny bit before retry to prevent server slamming (50ms)
                    usleep(50000);
                }
            }

            // If this provider did NOT return a non-retryable error, at least one provider
            // had a transient failure — so the order should stay pending for retry later.
            if (!$isNonRetryable) {
                $allNonRetryable = false;
            }

            // Sync statistics to provider health tracking
            $provider->response_time_ms = $elapsedMs;
            if ($success) {
                $provider->last_sync_success = now();
                $provider->last_sync_error = null;
                $provider->failed_requests_count = 0;
                $provider->save();

                // Order matches! Update order parameters
                $order->provider_order_id = $responseJson['order'];
                $order->status = 'processing';
                $order->save();

                self::log($order->id, $provider->id, 'api_response', $payload, $responseJson, null, $elapsedMs, $retry);
                
                // Trigger background balance sync for this provider (silently)
                try {
                    self::syncBalance($provider);
                } catch (\Exception $ex) {}

                return true;
            } else {
                // Log failed attempt
                $provider->last_sync_failed = now();
                $provider->failed_requests_count += 1;
                $provider->last_sync_error = 'Order placement failed: ' . $errorMsg;
                $provider->save();

                self::log($order->id, $provider->id, 'failover_switch', $payload, null, $errorMsg, $elapsedMs, $retry);
                
                Log::warning("Order placement failed on Provider ID {$provider->id}. Error: {$errorMsg}. Attempting failover...");
            }
        }

        // If we reach here, all providers failed!
        // If every provider returned a non-retryable error, cancel and refund the order
        // instead of leaving it pending or failed.
        if ($allNonRetryable) {
            try {
                \App\Services\RefundService::process($order, 'canceled');
                self::log($order->id, null, 'error', null, null, 'All providers returned non-retryable errors (e.g. service not API-usable, insufficient balance). Order auto-canceled and refunded.', 0, 0);
                Log::warning("Order #{$order->id} automatically canceled and refunded: all providers returned non-retryable errors.");
            } catch (\Exception $e) {
                Log::error("Failed to auto-refund order #{$order->id}: " . $e->getMessage());
                $order->status = 'failed';
                $order->save();
            }
        } else {
            $order->status = 'pending';
            $order->save();
            self::log($order->id, null, 'error', null, null, 'All mapped SMM API reseller providers failed to process order. Will retry later.', 0, 0);
        }

        return false;
    }

    /**
     * Check if an API response body contains a known non-retryable error.
     */
    private static function isNonRetryableError(string $responseBody): bool
    {
        $lower = strtolower($responseBody);
        foreach (self::$nonRetryablePatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if API error specifically indicates that the service is missing or disabled at provider.
     */
    private static function isServiceMissingError(string $responseBody): bool
    {
        $lower = strtolower($responseBody);
        $patterns = [
            'service not found',
            'service is disabled',
            'invalid service',
            'service does not exist',
            'service inactive',
            'not usable with api',
        ];
        foreach ($patterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Silent helper to refresh provider balance in the background.
     */
    private static function syncBalance(Provider $provider): void
    {
        if (empty($provider->api_url) || empty($provider->api_key)) {
            return;
        }

        try {
            $response = Http::timeout(5)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'balance',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['balance']) && is_numeric($data['balance'])) {
                    $provider->balance = (float)$data['balance'];
                    $provider->save();
                }
            }
        } catch (\Throwable $e) {
            // Silently ignore background balance sync errors
        }
    }

    /**
     * Silent helper to write order processing trace records.
     */
    private static function log($orderId, $providerId, $action, $req, $res, $err, $ms, $retry)
    {
        try {
            // Mask keys in log files to protect security credentials
            if (isset($req['key'])) {
                $req['key'] = '********';
            }
            
            OrderProcessingLog::create([
                'order_id' => $orderId,
                'provider_id' => $providerId,
                'action' => $action,
                'request_payload' => $req,
                'response_payload' => $res,
                'error_message' => $err,
                'response_time_ms' => $ms,
                'retry_count' => $retry,
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to write order processing log: " . $e->getMessage());
        }
    }

    /**
     * Cancel an order at upstream SMM reseller provider (if linked) and auto-refund customer.
     */
    public static function cancel(Order $order): array
    {
        if ($order->refunded || $order->status === 'canceled') {
            return ['success' => false, 'message' => 'Order is already canceled or refunded.'];
        }

        // If order was not yet dispatched to provider or has no provider_order_id
        if (!$order->provider_order_id) {
            $refundOk = RefundService::process($order, 'canceled');
            if ($refundOk) {
                self::log($order->id, null, 'order_cancel', null, null, 'Order canceled locally and refunded.', 0, 0);
                return ['success' => true, 'message' => 'Order canceled successfully and ₹' . number_format($order->charge, 2) . ' refunded to your wallet.'];
            }
            return ['success' => false, 'message' => 'Failed to process cancellation refund.'];
        }

        // Order is linked to a provider!
        $provider = null;
        if ($order->service && $order->service->provider_id) {
            $provider = Provider::find($order->service->provider_id);
        }

        if (!$provider || empty($provider->api_url) || empty($provider->api_key)) {
            // Local cancel fallback
            $refundOk = RefundService::process($order, 'canceled');
            if ($refundOk) {
                return ['success' => true, 'message' => 'Order canceled and ₹' . number_format($order->charge, 2) . ' refunded to your wallet.'];
            }
            return ['success' => false, 'message' => 'Failed to cancel order.'];
        }

        // Call Provider API with action=cancel
        $startTime = microtime(true);
        $payload = [
            'key' => $provider->api_key,
            'action' => 'cancel',
            'orders' => $order->provider_order_id,
            'order' => $order->provider_order_id,
        ];

        try {
            $response = Http::timeout(10)->asForm()->post($provider->api_url, $payload);
            $elapsedMs = (int)((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                $data = $response->json();
                self::log($order->id, $provider->id, 'api_cancel_response', $payload, $data, null, $elapsedMs, 0);

                $isCanceled = false;
                $errorMsg = null;

                if (is_array($data)) {
                    if (isset($data[0])) {
                        $item = $data[0];
                        if (isset($item['cancel']) && !is_array($item['cancel']) && $item['cancel'] != '' && !isset($item['cancel']['error'])) {
                            $isCanceled = true;
                        } elseif (isset($item['cancel']['error'])) {
                            $errorMsg = $item['cancel']['error'];
                        } elseif (isset($item['error'])) {
                            $errorMsg = $item['error'];
                        }
                    } elseif (isset($data['cancel'])) {
                        if (is_array($data['cancel']) && isset($data['cancel']['error'])) {
                            $errorMsg = $data['cancel']['error'];
                        } else {
                            $isCanceled = true;
                        }
                    } elseif (isset($data['status']) && strtolower($data['status']) === 'success') {
                        $isCanceled = true;
                    } elseif (isset($data['error'])) {
                        $errorMsg = $data['error'];
                    }
                }

                // If provider confirms cancel or if provider reports order is not found/invalid
                if ($isCanceled || ($errorMsg && (str_contains(strtolower($errorMsg), 'not found') || str_contains(strtolower($errorMsg), 'invalid order')))) {
                    $refundOk = RefundService::process($order, 'canceled');
                    if ($refundOk) {
                        return ['success' => true, 'message' => 'Order canceled successfully on provider and ₹' . number_format($order->charge, 2) . ' refunded to your wallet!'];
                    }
                }

                $reason = $errorMsg ?: 'Provider does not permit canceling this order in its current stage.';
                return ['success' => false, 'message' => $reason];
            } else {
                return ['success' => false, 'message' => 'Provider API returned error status (' . $response->status() . ').'];
            }
        } catch (\Exception $e) {
            Log::error("Order cancel exception for Order #{$order->id}: " . $e->getMessage());
            return ['success' => false, 'message' => 'Connection error while requesting provider cancellation: ' . $e->getMessage()];
        }
    }
}
