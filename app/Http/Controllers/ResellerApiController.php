<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResellerApiController extends Controller
{
    public function handle(Request $request)
    {
        // System License Enforcement
        if (!\App\Services\LicenseService::isLicenseValid()) {
            return response()->json(['error' => 'System Lock: This installation requires a valid 1-Year License Key bound to domain ' . request()->getHost() . '.'], 403);
        }

        // 1. API Key Validation (Supports 'key', 'api_key', 'token', or Bearer Header)
        $apiKey = $request->input('key') ?? $request->input('api_key') ?? $request->input('token') ?? $request->bearerToken();
        if (!$apiKey) {
            return response()->json(['error' => 'API key is required'], 400);
        }

        $user = User::where('api_key', trim($apiKey))->where('status', 'active')->first();
        if (!$user) {
            return response()->json(['error' => 'Invalid API key or account suspended'], 401);
        }

        // 2. Action Routing
        $action = $request->input('action');
        if (!$action) {
            return response()->json(['error' => 'Action parameter is required'], 400);
        }

        switch ($action) {
            case 'services':
                return $this->getServices();
                
            case 'add':
                return $this->addOrder($request, $user);
                
            case 'status':
                return $this->getOrderStatus($request, $user);
                
            case 'balance':
                return $this->getBalance($user);
                
            default:
                return response()->json(['error' => 'Invalid action. Supported actions: services, add, status, balance'], 400);
        }
    }

    private function getServices()
    {
        $services = Service::where('status', 'active')
            ->with('category')
            ->get()
            ->map(function ($s) {
                return [
                    'service' => $s->id,
                    'name' => $s->name,
                    'type' => $s->isCustomComments() ? 'Custom Comments' : 'Default',
                    'category' => $s->category ? $s->category->name : 'General',
                    'rate' => (float)$s->price_per_k,
                    'min' => (int)$s->min_quantity,
                    'max' => (int)$s->max_quantity,
                    'description' => $s->description,
                ];
            });

        return response()->json($services);
    }

    private function getBalance($user)
    {
        return response()->json([
            'balance' => number_format((float)$user->balance, 2, '.', ''),
            'currency' => 'INR',
        ]);
    }

    private function addOrder(Request $request, $user)
    {
        $serviceId = $request->input('service');
        $link = $request->input('link');
        $quantity = $request->input('quantity');

        if (!$serviceId || !$link) {
            return response()->json(['error' => 'Parameters service and link are required'], 400);
        }

        // Resolve service by primary database ID first to prevent cross-provider service ID collisions
        $service = Service::where('status', 'active')->where('id', $serviceId)->first();
        if (!$service) {
            $service = Service::where('status', 'active')->where('provider_service_id', $serviceId)->first();
        }
        if (!$service) {
            return response()->json(['error' => 'Service not found or inactive'], 404);
        }

        $isCustomComments = $service->isCustomComments();
        $normalizedComments = null;

        $rawComments = $request->input('comments') ?? $request->input('custom_comments');
        if ($rawComments !== null && trim((string)$rawComments) !== '') {
            $commentLines = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', (string)$rawComments)),
                fn($line) => $line !== ''
            ));
            if (!empty($commentLines)) {
                $normalizedComments = implode("\r\n", $commentLines);
                $quantity = count($commentLines);
            }
        } elseif ($isCustomComments) {
            return response()->json(['error' => 'Parameter comments is required for custom comments services (one comment per line)'], 400);
        }

        $quantity = (int)$quantity;
        if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
            return response()->json(['error' => "Quantity must be between {$service->min_quantity} and {$service->max_quantity}"], 400);
        }

        // Check for duplicate active orders on same link for the same action type
        $duplicateActive = Order::findActiveOrderOnSameLink($link, $service, $user->id);
        if ($duplicateActive) {
            $activeOrd = $duplicateActive['order'];
            $actType = $duplicateActive['action_type'];
            return response()->json([
                'error' => "Active order #{$activeOrd->id} for {$actType} is already in progress on this link. Please wait until it completes."
            ], 400);
        }

        // Calculate Cost with Effective Customer Discount
        $discountPercent = \App\Services\DiscountService::getUserDiscountPercentage($user);
        $discountedPricePerK = \App\Services\DiscountService::getDiscountedPricePerK((float)$service->price_per_k, $discountPercent);
        $charge = ($discountedPricePerK / 1000) * $quantity;

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $service, $link, $quantity, $normalizedComments, $charge) {
                // Enforce row-level lock on reseller client to prevent concurrent balance manipulation
                $lockedUser = User::lockForUpdate()->findOrFail($user->id);

                if ($lockedUser->balance < $charge) {
                    throw new \Exception('Insufficient balance');
                }

                $prevBalance = $lockedUser->balance;

                // Place Order
                $lockedUser->balance -= $charge;
                $lockedUser->save();

                $order = Order::create([
                    'user_id' => $lockedUser->id,
                    'service_id' => $service->id,
                    'link' => $link,
                    'comments' => $normalizedComments,
                    'quantity' => $quantity,
                    'charge' => $charge,
                    'start_count' => 0,
                    'remains' => $quantity,
                    'status' => 'pending',
                    'idempotency_token' => 'api_' . \Illuminate\Support\Str::random(40),
                ]);

                // Write to audit ledger
                \App\Models\WalletTransaction::create([
                    'user_id' => $lockedUser->id,
                    'amount' => -$charge,
                    'previous_balance' => $prevBalance,
                    'new_balance' => $lockedUser->balance,
                    'action' => 'api_order_place',
                    'reference_id' => $order->id,
                ]);

                \App\Models\ActivityLog::log('api_order_place', [
                    'order_id' => $order->id,
                    'charge' => $charge,
                    'client' => $lockedUser->email
                ]);

                return $order;
            });
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        // Dispatch order to provider with automatic failover, retries, and balance sync
        \App\Services\OrderProcessor::dispatch($order);

        // Send email notification of order placement via API
        $siteName = \App\Models\Setting::get('site_name', 'RishiSMM');
        $subject = "API Order Placed successfully - Order #{$order->id}";
        $title = "Order Placement Confirmation";
        $messageBody = "Hello,<br><br>" .
                       "This is to confirm that your API order has been received and processed successfully.<br><br>" .
                       "<strong>Order Details:</strong><br>" .
                       "• Order ID: #{$order->id}<br>" .
                       "• Service: {$service->name}<br>" .
                       "• Link: <a href='{$link}' target='_blank'>{$link}</a><br>" .
                       "• Quantity: {$quantity}<br>" .
                       "• Price: ₹" . number_format($charge, 4) . "<br>" .
                       "• Status: " . ucfirst($order->status) . "<br><br>" .
                       "Thank you for using our reseller API.";

        \App\Models\Setting::sendEmail(
            $user->email,
            $subject,
            $title,
            $messageBody
        );

        return response()->json([
            'order' => $order->id,
        ]);
    }

    private function getOrderStatus(Request $request, $user)
    {
        $orderId = $request->input('order');
        $ordersInput = $request->input('orders');

        // Support multiple order check
        if ($ordersInput) {
            $ids = is_array($ordersInput) ? $ordersInput : explode(',', $ordersInput);
            $orders = Order::where('user_id', $user->id)->whereIn('id', $ids)->get();
            $res = [];
            foreach ($orders as $order) {
                $res[$order->id] = [
                    'charge' => (float)$order->charge,
                    'start_count' => (int)$order->start_count,
                    'end_count' => (int)$order->end_count,
                    'target_end_count' => (int)$order->target_end_count,
                    'status' => ucfirst($order->status),
                    'remains' => (int)$order->remains,
                    'currency' => 'INR',
                ];
            }
            return response()->json($res);
        }

        if (!$orderId) {
            return response()->json(['error' => 'Order ID is required'], 400);
        }

        $order = Order::where('user_id', $user->id)->find($orderId);
        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        return response()->json([
            'charge' => (float)$order->charge,
            'start_count' => (int)$order->start_count,
            'end_count' => (int)$order->end_count,
            'target_end_count' => (int)$order->target_end_count,
            'status' => ucfirst($order->status),
            'remains' => (int)$order->remains,
            'currency' => 'INR',
        ]);
    }
}
