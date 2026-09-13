<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;
use App\Models\Order;
use App\Models\User;
use App\Models\Provider;
use App\Models\WalletTransaction;
use App\Models\ActivityLog;
use App\Models\Setting;
use App\Services\OrderStatusSyncService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

        $categories = \Illuminate\Support\Facades\Cache::remember('rishismm_db_categories_light_v1', 300, function () use ($enabledPlatforms) {
            $cats = Category::where('status', 'active')
                ->whereHas('services', function ($q) {
                    $q->where('status', 'active');
                })
                ->select('id', 'name', 'is_pinned', 'sort_order', 'status')
                ->get()
                ->filter(function ($cat) {
                    $name = strtolower($cat->name ?? '');
                    return !(preg_match('/\b(pvt|private|testing|test|dummy|junk|trash)\b/i', $name) ||
                        preg_match('/end\s*x+/i', $name) ||
                        preg_match('/❌\s*end/i', $name) ||
                        preg_match('/inttttt/i', $name));
                });

            $cats = $cats->sort(function ($a, $b) {
                $pinA = (int)($a->is_pinned ?? 0);
                $pinB = (int)($b->is_pinned ?? 0);
                if ($pinA !== $pinB) {
                    return $pinB <=> $pinA;
                }

                $orderA = (int)($a->sort_order ?? 0);
                $orderB = (int)($b->sort_order ?? 0);
                if ($orderA !== $orderB) {
                    return $orderA <=> $orderB;
                }

                return (int)($a->id ?? 0) <=> (int)($b->id ?? 0);
            })->values();

            if (class_exists(\App\Services\BrandingSanitizer::class)) {
                foreach ($cats as $cat) {
                    $cat->name = \App\Services\BrandingSanitizer::clean($cat->name);
                }
            }

            return $cats;
        });

        // Determine initial category to load services for
        $preselectedSrvId = $request->query('service_id') ?? session('service_id') ?? old('service_id');
        $initialCatId = null;
        $initialServices = collect();

        if ($preselectedSrvId) {
            $srv = Service::with('category')->find($preselectedSrvId);
            if ($srv) {
                $initialCatId = $srv->category_id;
            }
        }

        if (!$initialCatId && $categories->count() > 0) {
            $initialCatId = $categories->first()->id;
        }

        if ($initialCatId) {
            $initialServices = Service::where('status', 'active')
                ->where('category_id', $initialCatId)
                ->select('id', 'category_id', 'name', 'original_name', 'price_per_k', 'min_quantity', 'max_quantity', 'description', 'average_time', 'sort_order', 'status')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get();

            if (class_exists(\App\Services\BrandingSanitizer::class)) {
                foreach ($initialServices as $srv) {
                    $srv->name = \App\Services\BrandingSanitizer::clean($srv->name);
                    $srv->description = \App\Services\BrandingSanitizer::clean($srv->description);
                    $srv->is_custom_comments = $srv->isCustomComments();
                }
            }
        }

        try {
            $recentOrders = Order::where('user_id', Auth::id())
                ->with('service')
                ->latest()
                ->limit(5)
                ->get();
        } catch (\Throwable $e) {
            $recentOrders = collect();
        }

        try {
            $topServices = \Illuminate\Support\Facades\Cache::remember('rishismm_top_services_v1', 600, function () {
                $top = Order::select('service_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_orders'))
                    ->whereHas('service', function ($q) {
                        $q->where('status', 'active');
                    })
                    ->groupBy('service_id')
                    ->orderBy('total_orders', 'desc')
                    ->limit(5)
                    ->with(['service' => function ($q) {
                        $q->with('category');
                    }])
                    ->get();

                if ($top->count() < 5) {
                    $existingIds = $top->pluck('service_id')->filter()->toArray();
                    $additionalServices = Service::where('status', 'active')
                        ->whereNotIn('id', $existingIds)
                        ->with('category')
                        ->limit(5 - count($existingIds))
                        ->get();

                    foreach ($additionalServices as $srv) {
                        $mockItem = new \stdClass();
                        $mockItem->service_id = $srv->id;
                        $mockItem->total_orders = 0;
                        $mockItem->service = $srv;
                        $top->push($mockItem);
                    }
                }
                return $top;
            });
        } catch (\Throwable $e) {
            $topServices = collect();
        }

        $categoriesLight = $categories->map(function ($c) {
            return [
                'id' => $c->id,
                'name' => $c->name,
                'is_pinned' => (int)($c->is_pinned ?? 0),
                'sort_order' => (int)($c->sort_order ?? 0),
            ];
        })->values();

        $defaultPlatform = 'all';

        return view('user.dashboard', compact('categories', 'categoriesLight', 'initialServices', 'initialCatId', 'recentOrders', 'topServices', 'defaultPlatform'));
    }

    public function getServicesApi(Request $request)
    {
        $catId = $request->query('category_id');
        $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

        if ($catId) {
            $cat = Category::where('status', 'active')->find($catId);
            if (!$cat) {
                return response()->json([]);
            }
            $services = Service::where('status', 'active')
                ->where('category_id', $catId)
                ->select('id', 'category_id', 'name', 'original_name', 'price_per_k', 'min_quantity', 'max_quantity', 'description', 'average_time', 'sort_order', 'status')
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'desc')
                ->get()
                ->filter(function ($srv) use ($cat, $enabledPlatforms) {
                    return \App\Services\PlatformHelper::isPlatformEnabled($srv->name, $cat->name, $enabledPlatforms);
                })
                ->values();

            if (class_exists(\App\Services\BrandingSanitizer::class)) {
                foreach ($services as $srv) {
                    $srv->name = \App\Services\BrandingSanitizer::clean($srv->name);
                    $srv->description = \App\Services\BrandingSanitizer::clean($srv->description);
                    $srv->is_custom_comments = $srv->isCustomComments();
                }
            }

            return response()->json($services);
        }

        // Return all active services cached
        $cacheKey = 'rishismm_db_all_services_json_v1';
        $servicesData = \Illuminate\Support\Facades\Cache::remember($cacheKey, 300, function () use ($enabledPlatforms) {
            $cats = Category::where('status', 'active')
                ->whereHas('services', function ($q) {
                    $q->where('status', 'active');
                })
                ->with([
                    'services' => function ($query) {
                        $query->where('status', 'active')
                            ->select('id', 'category_id', 'name', 'original_name', 'price_per_k', 'min_quantity', 'max_quantity', 'description', 'average_time', 'sort_order', 'status')
                            ->orderBy('sort_order', 'asc')
                            ->orderBy('id', 'desc');
                    }
                ])
                ->get()
                ->filter(function ($cat) {
                    $name = strtolower($cat->name ?? '');
                    return !(preg_match('/\b(pvt|private|testing|test|dummy|junk|trash)\b/i', $name) ||
                        preg_match('/end\s*x+/i', $name) ||
                        preg_match('/❌\s*end/i', $name) ||
                        preg_match('/inttttt/i', $name));
                });

            $all = [];
            foreach ($cats as $cat) {
                if (!$cat->services) continue;
                $filtered = $cat->services->filter(function ($srv) use ($cat, $enabledPlatforms) {
                    return \App\Services\PlatformHelper::isPlatformEnabled($srv->name, $cat->name, $enabledPlatforms);
                });

                foreach ($filtered as $srv) {
                    $srvArr = $srv->toArray();
                    if (class_exists(\App\Services\BrandingSanitizer::class)) {
                        $srvArr['name'] = \App\Services\BrandingSanitizer::clean($srv->name);
                        $srvArr['description'] = \App\Services\BrandingSanitizer::clean($srv->description);
                    }
                    $srvArr['is_custom_comments'] = $srv->isCustomComments();
                    $srvArr['cat_name'] = class_exists(\App\Services\BrandingSanitizer::class) ? \App\Services\BrandingSanitizer::clean($cat->name) : $cat->name;
                    $all[] = $srvArr;
                }
            }
            return $all;
        });

        return response()->json($servicesData);
    }

    public function place(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'link' => 'required|url',
            'quantity' => 'nullable|integer|min:1',
            'comments' => 'nullable|string',
            'idempotency_token' => 'required|string|max:100',
        ]);

        // Check if this idempotency token was already processed (duplicate submission prevention)
        $existingOrder = Order::where('idempotency_token', $request->idempotency_token)->first();
        if ($existingOrder) {
            return redirect()->route('dashboard', ['service_id' => $existingOrder->service_id])
                ->with('success', 'Order was already placed successfully! Order ID: #' . $existingOrder->id)
                ->with('service_id', $existingOrder->service_id);
        }

        // Resolve service strictly by primary database ID first to prevent cross-provider service ID collisions
        $service = Service::where('status', 'active')->where('id', $request->service_id)->first();
        if (!$service) {
            $service = Service::where('status', 'active')->where('provider_service_id', $request->service_id)->first();
        }
        if (!$service) {
            return back()->with('error', 'Selected service was not found or is currently inactive.')->withInput();
        }

        // Determine if service requires custom comments
        $isCustomComments = $service->isCustomComments();
        $normalizedComments = null;

        if ($isCustomComments) {
            $rawComments = (string)$request->input('comments', '');
            $commentLines = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $rawComments)),
                fn($line) => $line !== ''
            ));

            if (empty($commentLines)) {
                return back()->with('error', 'Please enter at least one valid comment (one comment per line).')->withInput();
            }

            $quantity = count($commentLines);
            $normalizedComments = implode("\r\n", $commentLines);
        } else {
            $quantity = (int)$request->input('quantity', 0);
            if ($quantity <= 0) {
                return back()->with('error', 'Please enter a valid order quantity.')->withInput();
            }
        }

        // 1. Quantity Validation
        if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
            $err = "Quantity must be between {$service->min_quantity} and {$service->max_quantity}.";
            if ($isCustomComments) {
                $err .= " (You provided {$quantity} comments).";
            }
            return back()->with('error', $err)->withInput();
        }

        // 2. Duplicate Active Order Prevention on Same Link for the Same Action Type (e.g. Views on Views)
        $duplicateActive = Order::findActiveOrderOnSameLink($request->link, $service, Auth::id());
        if ($duplicateActive) {
            $activeOrd = $duplicateActive['order'];
            $actType = $duplicateActive['action_type'];
            return back()->with('error', "⚠️ An active order (#{$activeOrd->id}) for {$actType} is already in progress on this link! Please wait until order #{$activeOrd->id} is Completed before placing another {$actType} order on the same link.")->withInput();
        }

        // 3. Cost Calculation with Effective Customer Discount (Individual Overrides Tier)
        $discountPercent = \App\Services\DiscountService::getUserDiscountPercentage(Auth::user());
        $discountedPricePerK = \App\Services\DiscountService::getDiscountedPricePerK((float)$service->price_per_k, $discountPercent);
        $charge = ($discountedPricePerK / 1000) * $quantity;

        // Perform Database Transaction for wallet integrity
        try {
            $order = DB::transaction(function () use ($service, $request, $quantity, $normalizedComments, $charge) {
                // Enforce row-level lock on user balance to prevent race conditions (double spend)
                $user = User::lockForUpdate()->findOrFail(Auth::id());

                // 3. Balance Check
                if ($user->balance < $charge) {
                    throw new \Exception('Insufficient funds. Please add funds to place this order.');
                }

                $prevBalance = $user->balance;

                // 4. Deduct Balance
                $user->balance -= $charge;
                $user->save();

                // 5. Create Order with Idempotency Token
                $order = Order::create([
                    'user_id' => $user->id,
                    'service_id' => $service->id,
                    'link' => $request->link,
                    'comments' => $normalizedComments,
                    'quantity' => $quantity,
                    'charge' => $charge,
                    'start_count' => 0,
                    'remains' => $quantity,
                    'status' => 'pending',
                    'idempotency_token' => $request->idempotency_token,
                ]);

                // 6. Log Wallet Transaction Ledger
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => -$charge,
                    'previous_balance' => $prevBalance,
                    'new_balance' => $user->balance,
                    'action' => 'order_place',
                    'reference_id' => $order->id,
                ]);

                ActivityLog::log('order_place', [
                    'order_id' => $order->id,
                    'charge' => $charge,
                    'service' => $service->name
                ]);

                return $order;
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate idempotency token gracefully (SQLSTATE 23000 / Error 1062)
            if ($e->getCode() == 23000 || str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '1062')) {
                $existingOrder = Order::where('idempotency_token', $request->idempotency_token)->first();
                $orderIdText = $existingOrder ? ' Order ID: #' . $existingOrder->id : '';
                $srvId = $existingOrder ? $existingOrder->service_id : $service->id;
                return redirect()->route('dashboard', ['service_id' => $srvId])
                    ->with('success', 'Order was already placed successfully!' . $orderIdText)
                    ->with('service_id', $srvId);
            }
            return back()->with('error', 'Database error: ' . $e->getMessage())->withInput();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        // 7. Process Order Reseller Placement (Failover & Retry handling managed in OrderProcessor)
        \App\Services\OrderProcessor::dispatch($order);

        return redirect()->route('dashboard', ['service_id' => $service->id])
            ->with('success', 'Order placed successfully! Order ID: #' . $order->id)
            ->with('service_id', $service->id);
    }

    public function massPlace(Request $request)
    {
        $request->validate([
            'mass_order' => 'required|string',
            'idempotency_token' => 'nullable|string|max:100',
        ]);

        $rawInput = (string) $request->input('mass_order', '');
        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $rawInput)),
            fn($line) => $line !== ''
        ));

        if (empty($lines)) {
            return back()->with('error', 'Please enter at least one order line.')->withInput()->with('active_tab', 'mass');
        }

        if (count($lines) > 100) {
            return back()->with('error', 'Maximum 100 orders can be submitted in a single Mass Order request.')->withInput()->with('active_tab', 'mass');
        }

        $user = Auth::user();
        $discountPercent = \App\Services\DiscountService::getUserDiscountPercentage($user);

        $placedOrders = [];
        $errors = [];
        $totalCharged = 0;

        foreach ($lines as $index => $line) {
            $lineNum = $index + 1;

            $serviceIdInput = null;
            $link = null;
            $quantityInput = null;

            // 1. Try smart regex pattern matching: [service_id] [separator] [URL] [separator] [quantity]
            if (preg_match('/^(\d+)\s*[\|\,\;\:\s\t\-]+\s*(https?:\/\/[^\s\|\,\;\t]+)\s*[\|\,\;\:\s\t\-]+\s*(\d+)$/i', $line, $m)) {
                $serviceIdInput = $m[1];
                $link = $m[2];
                $quantityInput = $m[3];
            } else {
                // 2. Fallback to pipe split
                $parts = array_map('trim', explode('|', $line));
                if (count($parts) === 3) {
                    [$serviceIdInput, $link, $quantityInput] = $parts;
                } else {
                    // 3. Fallback to space / tab split
                    $parts = array_values(array_filter(preg_split('/\s+/', $line)));
                    if (count($parts) === 3) {
                        [$serviceIdInput, $link, $quantityInput] = $parts;
                    }
                }
            }

            if (!$serviceIdInput || !$link || !$quantityInput) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => 'Invalid format. Use: service_id link quantity (Separated by space, comma, or pipe |)'
                ];
                continue;
            }

            if (!filter_var($link, FILTER_VALIDATE_URL)) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => 'Invalid URL link format.'
                ];
                continue;
            }

            $serviceId = (int)$serviceIdInput;
            $service = Service::where('status', 'active')->where('id', $serviceId)->first();
            if (!$service) {
                $service = Service::where('status', 'active')->where('provider_service_id', $serviceId)->first();
            }
            if (!$service) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => "Service #{$serviceIdInput} not found or is currently inactive."
                ];
                continue;
            }

            if ($service->isCustomComments()) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => "Service #{$service->id} ({$service->name}) requires custom comments and cannot be placed via standard Mass Order."
                ];
                continue;
            }

            $quantity = (int)$quantityInput;
            if ($quantity <= 0) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => 'Quantity must be a positive integer.'
                ];
                continue;
            }

            if ($quantity < $service->min_quantity || $quantity > $service->max_quantity) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => "Quantity must be between {$service->min_quantity} and {$service->max_quantity}."
                ];
                continue;
            }

            $duplicateActive = Order::findActiveOrderOnSameLink($link, $service, $user->id);
            if ($duplicateActive) {
                $activeOrd = $duplicateActive['order'];
                $actType = $duplicateActive['action_type'];
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => "An active order (#{$activeOrd->id}) for {$actType} is already in progress on this link."
                ];
                continue;
            }

            $discountedPricePerK = \App\Services\DiscountService::getDiscountedPricePerK((float)$service->price_per_k, $discountPercent);
            $charge = ($discountedPricePerK / 1000) * $quantity;

            try {
                $order = DB::transaction(function () use ($user, $service, $link, $quantity, $charge, $index) {
                    $lockedUser = User::lockForUpdate()->findOrFail($user->id);

                    if ($lockedUser->balance < $charge) {
                        throw new \Exception('Insufficient funds to place this order line.');
                    }

                    $prevBalance = $lockedUser->balance;
                    $lockedUser->balance -= $charge;
                    $lockedUser->save();

                    $idempotencyToken = 'mass_' . $lockedUser->id . '_' . time() . '_' . $index . '_' . \Illuminate\Support\Str::random(6);

                    $order = Order::create([
                        'user_id' => $lockedUser->id,
                        'service_id' => $service->id,
                        'link' => $link,
                        'quantity' => $quantity,
                        'charge' => $charge,
                        'start_count' => 0,
                        'remains' => $quantity,
                        'status' => 'pending',
                        'idempotency_token' => $idempotencyToken,
                    ]);

                    WalletTransaction::create([
                        'user_id' => $lockedUser->id,
                        'amount' => -$charge,
                        'previous_balance' => $prevBalance,
                        'new_balance' => $lockedUser->balance,
                        'action' => 'order_place',
                        'reference_id' => $order->id,
                    ]);

                    ActivityLog::log('order_place', [
                        'order_id' => $order->id,
                        'charge' => $charge,
                        'service' => $service->name,
                        'is_mass_order' => true
                    ]);

                    return $order;
                });

                \App\Services\OrderProcessor::dispatch($order);
                $placedOrders[] = $order->id;
                $totalCharged += $charge;
            } catch (\Exception $e) {
                $errors[] = [
                    'line' => $lineNum,
                    'content' => $line,
                    'reason' => $e->getMessage()
                ];
            }
        }

        $successCount = count($placedOrders);
        $totalLines = count($lines);

        if ($successCount === 0 && !empty($errors)) {
            return back()->with('error', "Failed to place any orders from Mass Order request. Please review the errors below.")
                ->with('mass_errors', $errors)
                ->withInput()
                ->with('active_tab', 'mass');
        }

        $msg = "Mass Order Processed! Successfully placed {$successCount} of {$totalLines} order(s). Total Charged: ₹" . number_format($totalCharged, 2);
        if (!empty($placedOrders)) {
            $msg .= " (Order IDs: #" . implode(', #', $placedOrders) . ")";
        }

        $redirect = redirect()->route('dashboard')->with('success', $msg)->with('active_tab', 'mass');
        if (!empty($errors)) {
            $redirect->with('mass_errors', $errors);
        }

        return $redirect;
    }

    public function history(Request $request)
    {
        if (!class_exists(OrderStatusSyncService::class)) {
            $serviceFile = app_path('Services/OrderStatusSyncService.php');
            if (file_exists($serviceFile)) {
                require_once $serviceFile;
            }
        }

        if (class_exists(OrderStatusSyncService::class)) {
            OrderStatusSyncService::sync(Auth::id(), false);
        }

        $status = $request->query('status');
        $search = trim((string) $request->query('search', ''));

        $query = Order::where('user_id', Auth::id())->with('service');

        if ($status && in_array($status, ['pending', 'processing', 'in_progress', 'completed', 'partial', 'canceled', 'failed'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $cleanSearch = ltrim($search, '#');
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('id', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('provider_order_id', 'LIKE', "%{$cleanSearch}%")
                  ->orWhere('link', 'LIKE', "%{$search}%")
                  ->orWhereHas('service', function ($sq) use ($search) {
                      $sq->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('user.orders', compact('orders', 'status', 'search'));
    }

    public function refill(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if (!$order->canRefill()) {
            return back()->with('error', 'This order is not eligible for automated refill.');
        }

        $provider = Provider::find($order->service->provider_id);
        if (!$provider || $provider->status !== 'active') {
            return back()->with('error', 'Linked API provider is currently inactive.');
        }

        try {
            // Increased timeout to 30s to prevent transient cURL timeouts on slow provider endpoints
            $response = Http::timeout(30)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'refill',
                'order' => $order->provider_order_id,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::info("Refill API response for Order #{$order->id} (Provider Order #{$order->provider_order_id}): " . $response->body());

                if (is_array($data)) {
                    $refillId = $data['refill'] ?? $data['refill_id'] ?? ($data[0]['refill'] ?? null);

                    if ($refillId) {
                        return back()->with('success', 'Refill request submitted successfully! Refill ID: #' . $refillId);
                    }

                    $errorMsg = $data['error'] ?? $data['message'] ?? ($data[0]['error'] ?? null);
                    if ($errorMsg) {
                        return back()->with('error', 'Provider Refill Error: ' . $errorMsg);
                    }

                    if (isset($data['status']) && strtolower($data['status']) === 'success') {
                        return back()->with('success', 'Refill request submitted successfully to provider.');
                    }
                }

                $bodyStr = trim($response->body());
                if (!empty($bodyStr)) {
                    return back()->with('error', 'Provider Response: ' . \Illuminate\Support\Str::limit($bodyStr, 150));
                }
            }

            return back()->with('error', 'Failed to connect with SMM provider refill API (HTTP ' . $response->status() . ').');
        } catch (\Exception $e) {
            Log::error("Refill exception for order #{$order->id}: " . $e->getMessage());
            return back()->with('error', 'Refill request failed: ' . $e->getMessage());
        }
    }

    /**
     * Customer-side cancel action with provider sync and wallet refund.
     */
    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if (!$order->canCancel()) {
            return back()->with('error', 'This order cannot be canceled in its current stage (Status: ' . ucfirst($order->status) . ').');
        }

        $result = \App\Services\OrderProcessor::cancel($order);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function activeStatus(Request $request)
    {
        if (!$request->expectsJson() && !$request->ajax()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Sync statuses with external provider APIs in real-time (caches calls for 45s)
        \App\Services\OrderStatusSyncService::sync(Auth::id(), false);

        $orders = Order::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'processing', 'in_progress'])
            ->select(['id', 'status', 'remains', 'start_count', 'provider_order_id', 'updated_at'])
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'status' => $order->status,
                    'remains' => $order->remains,
                    'start_count' => $order->start_count,
                    'provider_order_id' => $order->provider_order_id,
                    'updated_at' => $order->updated_at->toIso8601String(),
                ];
            });

        return response()->json([
            'orders' => $orders
        ]);
    }
}
