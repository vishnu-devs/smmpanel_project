<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Service;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\ReceivedPayment;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\ActivityLog;
use App\Models\Provider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppApiController extends Controller
{
    /**
     * Google Login via Email / Handoff
     */
    public function googleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'nullable|string',
            'google_id' => 'nullable|string',
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name ?? 'Google User',
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make(Str::random(16)),
                'google_id' => $request->google_id ?? 'google_app_' . Str::random(10),
                'status' => 'active',
                'balance' => 0.00,
                'api_key' => Str::random(32),
            ]);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is suspended or inactive.'
            ], 403);
        }

        if (empty($user->api_key)) {
            $user->api_key = Str::random(32);
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Google Login successful',
            'token' => $user->api_key,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'balance' => (float) $user->balance,
                'role' => $user->role,
            ]
        ]);
    }
    /**
     * Helper to authenticate user via Token / API Key from Header or Input
     */
    private function getAuthenticatedUser(Request $request)
    {
        $token = $request->header('Authorization');
        if ($token && str_starts_with($token, 'Bearer ')) {
            $token = substr($token, 7);
        }
        if (!$token) {
            $token = $request->input('api_token') ?? $request->input('key');
        }

        if (!$token) {
            return null;
        }

        return User::where('api_key', $token)->where('status', 'active')->first();
    }

    /**
     * 1. App User Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', strtolower(trim($request->email)))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid email or password.'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'status' => 'error',
                'message' => 'Your account is suspended or inactive. Please contact support.'
            ], 403);
        }

        // Ensure user has an API Key / Token
        if (empty($user->api_key)) {
            $user->api_key = Str::random(32);
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $user->api_key,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'balance' => (float) $user->balance,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * 2. App User Registration
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $apiKey = Str::random(32);

        $user = User::create([
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'whatsapp' => $request->whatsapp ? trim($request->whatsapp) : null,
            'password' => Hash::make($request->password),
            'balance' => 0.00,
            'role' => 'user',
            'status' => 'active',
            'api_key' => $apiKey,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully!',
            'token' => $apiKey,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'balance' => (float) $user->balance,
                'role' => $user->role,
            ]
        ], 201);
    }

    /**
     * 3. Native Google Sign-In / Auth with Cryptographic Token Verification
     */
    public function googleAuth(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $idToken = trim($request->input('id_token'));

        try {
            // Verify ID token with Google's official OAuth2 tokeninfo service
            $response = \Illuminate\Support\Facades\Http::timeout(5)->get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken,
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired Google authentication credential.'
                ], 401);
            }

            $payload = $response->json();

            // Validate standard Google token claims
            $issuer = $payload['iss'] ?? '';
            $validIssuers = ['accounts.google.com', 'https://accounts.google.com'];
            if (!in_array($issuer, $validIssuers, true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Google token issuer.'
                ], 401);
            }

            // Validate token expiration
            $exp = (int) ($payload['exp'] ?? 0);
            if ($exp < time()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Google authentication token has expired.'
                ], 401);
            }

            // Validate email verification claim
            $emailVerified = $payload['email_verified'] ?? false;
            $isEmailVerified = ($emailVerified === true || $emailVerified === 'true' || $emailVerified === 1 || $emailVerified === '1');
            if (!$isEmailVerified) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unverified Google email account.'
                ], 401);
            }

            // Validate audience if configured in settings
            $configuredClientId = \App\Models\Setting::get('google_client_id');
            if (!empty($configuredClientId)) {
                $audience = $payload['aud'] ?? '';
                if ($audience !== $configuredClientId) {
                    \Illuminate\Support\Facades\Log::warning('Google Auth audience mismatch', [
                        'expected_client_id_present' => true,
                    ]);
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Google client ID mismatch.'
                    ], 401);
                }
            }

            $googleId = (string) ($payload['sub'] ?? '');
            $email = strtolower(trim((string) ($payload['email'] ?? '')));
            $name = trim((string) ($payload['name'] ?? 'Google User'));

            if (empty($email) || empty($googleId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Incomplete Google credential payload.'
                ], 401);
            }

            // Match user by google_id or verified email
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if (!$user) {
                $apiKey = Str::random(32);
                $user = User::create([
                    'name' => $name ?: 'Google User',
                    'email' => $email,
                    'password' => Hash::make(Str::random(24)),
                    'balance' => 0.00,
                    'role' => 'user',
                    'status' => 'active',
                    'api_key' => $apiKey,
                    'google_id' => $googleId,
                ]);

                \App\Models\ActivityLog::log('user_register_google_api', ['email' => $user->email]);
            } else {
                if ($user->status !== 'active') {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Account is deactivated or suspended.'
                    ], 403);
                }

                // Link google_id if not previously set
                if (empty($user->google_id)) {
                    $user->google_id = $googleId;
                }
                if (empty($user->api_key)) {
                    $user->api_key = Str::random(32);
                }
                $user->save();

                \App\Models\ActivityLog::log('login_google_api', ['email' => $user->email]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Google authentication successful',
                'token' => $user->api_key,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'whatsapp' => $user->whatsapp,
                    'balance' => (float) $user->balance,
                    'role' => $user->role,
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Google Auth API Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to authenticate with Google. Please try again.'
            ], 500);
        }
    }

    /**
     * 4. User Profile & Balance
     */
    public function getProfile(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $totalOrders = Order::where('user_id', $user->id)->count();
        $totalSpent = Order::where('user_id', $user->id)->whereNotIn('status', ['canceled', 'refunded'])->sum('charge');

        return response()->json([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'balance' => (float) $user->balance,
                'role' => $user->role,
                'api_key' => $user->api_key,
                'total_orders' => $totalOrders,
                'total_spent' => (float) $totalSpent,
            ]
        ]);
    }

    /**
     * 5. Categories & Services (Nested Catalog)
     */
    public function getCatalog(Request $request)
    {
        $categories = \Illuminate\Support\Facades\Cache::remember('rishismm_app_api_catalog', 300, function () {
            $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

            return Category::where('status', 'active')
                ->whereHas('services', function ($q) {
                    $q->where('status', 'active');
                })
                ->with([
                    'services' => function ($query) {
                        $query->where('status', 'active')
                            ->select('id', 'category_id', 'name', 'price_per_k', 'min_quantity', 'max_quantity', 'description')
                            ->orderBy('sort_order', 'asc')
                            ->orderBy('id', 'desc');
                    }
                ])
                ->get()
                ->filter(function ($cat) use ($enabledPlatforms) {
                    if (!$cat->services)
                        return false;

                    $cat->services = $cat->services->filter(function ($srv) use ($cat, $enabledPlatforms) {
                        return \App\Services\PlatformHelper::isPlatformEnabled($srv->name, $cat->name, $enabledPlatforms);
                    })->values();

                    return $cat->services->count() > 0;
                })
                ->sort(function ($a, $b) {
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
                })
                ->values()
                ->map(function ($cat) {
                    return [
                        'id' => $cat->id,
                        'name' => \App\Services\BrandingSanitizer::clean($cat->name),
                        'icon' => $cat->icon ?? 'star',
                        'services' => $cat->services->map(function ($s) {
                            return [
                                'id' => $s->id,
                                'name' => \App\Services\BrandingSanitizer::clean($s->name),
                                'rate' => (float) $s->price_per_k,
                                'min' => (int) $s->min_quantity,
                                'max' => (int) $s->max_quantity,
                                'type' => 'Default',
                                'description' => strip_tags(\App\Services\BrandingSanitizer::clean($s->description ?? '')),
                            ];
                        })
                    ];
                });
        });

        return response()->json([
            'status' => 'success',
            'categories' => $categories
        ]);
    }

    /**
     * 6. Place New Order
     */
    public function placeOrder(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'link' => 'required|string|max:1000',
            'quantity' => 'nullable|integer|min:1',
            'custom_comments' => 'nullable|string',
            'comments' => 'nullable|string',
        ]);

        $service = Service::where('id', $request->service_id)->where('status', 'active')->first();
        if (!$service) {
            return response()->json(['status' => 'error', 'message' => 'Service is currently unavailable.'], 400);
        }

        $isCustomComments = $service->isCustomComments();
        $normalizedComments = null;

        if ($isCustomComments || $request->filled('custom_comments') || $request->filled('comments')) {
            $rawComments = (string) ($request->input('custom_comments') ?? $request->input('comments', ''));
            $commentLines = array_values(array_filter(
                array_map('trim', preg_split('/\r\n|\r|\n/', $rawComments)),
                fn($line) => $line !== ''
            ));

            if ($isCustomComments && empty($commentLines)) {
                return response()->json(['status' => 'error', 'message' => 'Please enter at least one valid comment (one comment per line).'], 422);
            }

            if (!empty($commentLines)) {
                $quantity = count($commentLines);
                $normalizedComments = implode("\r\n", $commentLines);
            } else {
                $quantity = (int) $request->quantity;
            }
        } else {
            $quantity = (int) $request->quantity;
        }

        if ($quantity < $service->min_quantity) {
            return response()->json(['status' => 'error', 'message' => "Minimum quantity is {$service->min_quantity}"], 422);
        }
        if ($quantity > $service->max_quantity) {
            return response()->json(['status' => 'error', 'message' => "Maximum quantity is {$service->max_quantity}"], 422);
        }

        // Duplicate Active Order Prevention on Same Link for the Same Action Type
        $duplicateActive = Order::findActiveOrderOnSameLink($request->link, $service, $user->id);
        if ($duplicateActive) {
            $activeOrd = $duplicateActive['order'];
            $actType = $duplicateActive['action_type'];
            return response()->json([
                'status' => 'error',
                'message' => "⚠️ An active order (#{$activeOrd->id}) for {$actType} is already in progress on this link! Please wait until order #{$activeOrd->id} is Completed before placing another {$actType} order on the same link."
            ], 422);
        }

        $charge = ($service->price_per_k * $quantity) / 1000;

        try {
            $result = DB::transaction(function () use ($user, $service, $request, $quantity, $normalizedComments, $charge) {
                // Enforce row-level lock on user balance to prevent race conditions (double spend)
                $lockedUser = User::lockForUpdate()->findOrFail($user->id);

                if ($lockedUser->balance < $charge) {
                    throw new \Exception('Insufficient wallet balance. Required: ₹' . number_format($charge, 2) . ', Available: ₹' . number_format($lockedUser->balance, 2));
                }

                $prevBalance = $lockedUser->balance;

                // Deduct balance atomically
                $lockedUser->balance -= $charge;
                $lockedUser->save();

                // Create Order with Idempotency Token
                $order = Order::create([
                    'user_id' => $lockedUser->id,
                    'service_id' => $service->id,
                    'link' => trim($request->link),
                    'comments' => $normalizedComments,
                    'quantity' => $quantity,
                    'charge' => $charge,
                    'start_count' => 0,
                    'remains' => $quantity,
                    'status' => 'pending',
                    'provider_id' => $service->provider_id,
                    'idempotency_token' => 'app_' . Str::random(40),
                ]);

                // Create Wallet Transaction Audit Ledger Entry
                WalletTransaction::create([
                    'user_id' => $lockedUser->id,
                    'amount' => -$charge,
                    'previous_balance' => $prevBalance,
                    'new_balance' => $lockedUser->balance,
                    'action' => 'app_order_place',
                    'reference_id' => $order->id,
                ]);

                ActivityLog::log('app_order_place', [
                    'order_id' => $order->id,
                    'charge' => $charge,
                    'service' => $service->name,
                    'client' => $lockedUser->email,
                ]);

                return [
                    'order' => $order,
                    'new_balance' => (float) $lockedUser->balance,
                ];
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }

        $order = $result['order'];
        $newBalance = $result['new_balance'];

        // Dispatch order to provider with automatic failover, retries, and balance sync OUTSIDE the DB lock
        \App\Services\OrderProcessor::dispatch($order);

        return response()->json([
            'status' => 'success',
            'message' => 'Order placed successfully!',
            'order' => [
                'id' => $order->id,
                'service_name' => $service->name,
                'quantity' => $order->quantity,
                'charge' => (float) $order->charge,
                'status' => $order->status,
                'link' => $order->link,
                'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            ],
            'new_balance' => $newBalance
        ]);
    }

    /**
     * 7. User Orders History
     */
    public function getOrders(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        \App\Services\OrderStatusSyncService::sync($user->id, false);

        $query = Order::where('user_id', $user->id)->with('service');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('link', 'like', "%{$search}%")
                    ->orWhereHas('service', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $orders = $query->orderBy('id', 'desc')->paginate(20);

        return response()->json([
            'status' => 'success',
            'orders' => $orders->map(function ($o) {
                return [
                    'id' => $o->id,
                    'service_name' => $o->service ? $o->service->name : 'Custom Service',
                    'link' => $o->link,
                    'quantity' => (int) $o->quantity,
                    'charge' => (float) $o->charge,
                    'start_count' => (int) $o->start_count,
                    'end_count' => (int) $o->end_count,
                    'target_end_count' => (int) $o->target_end_count,
                    'remains' => (int) $o->remains,
                    'status' => $o->status,
                    'date' => $o->created_at ? $o->created_at->format('d M Y, h:i A') : '',
                ];
            }),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'total' => $orders->total()
        ]);
    }

    /**
     * Cancel an active/pending order and auto-refund to user wallet.
     */
    public function cancelOrder(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $order = Order::where('user_id', $user->id)->find($id);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        if (!$order->canCancel()) {
            return response()->json(['status' => 'error', 'message' => 'This order cannot be canceled in its current stage (Status: ' . ucfirst($order->status) . ').'], 400);
        }

        $result = \App\Services\OrderProcessor::cancel($order);

        if ($result['success']) {
            $user->refresh();
            return response()->json([
                'status' => 'success',
                'message' => $result['message'],
                'new_balance' => (float) $user->balance,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => $result['message'],
        ], 400);
    }

    /**
     * 8. Add Funds QR & Details
     */
    public function getPaymentDetails(Request $request)
    {
        $upiQr = Setting::get('payment_upi_qr');
        $upiId = Setting::get('payment_upi_id', 'paytmqr28100505010118r3tcv58814@paytm');
        $siteName = Setting::get('site_name', 'Rishi SMM');

        $qrUrl = '';
        if ($upiQr) {
            $qrUrl = str_starts_with($upiQr, 'http') ? $upiQr : url($upiQr);
        }

        return response()->json([
            'status' => 'success',
            'upi_id' => $upiId,
            'qr_image' => $qrUrl,
            'merchant_name' => $siteName,
            'instructions' => '1. Scan QR or Pay via any UPI App (GPay, PhonePe, Paytm).' . "\n" . '2. Copy the 12-digit UTR / Reference ID.' . "\n" . '3. Enter amount & UTR below to credit balance instantly.'
        ]);
    }

    /**
     * 9. Submit UTR / Transaction ID for Auto Verification
     */
    public function submitUtr(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'utr' => 'required|digits:12|unique:transactions,payment_id',
        ]);

        $utr = trim($request->utr);
        $amount = (float) $request->amount;

        // Check if already claimed
        $alreadyClaimed = ReceivedPayment::where('utr', $utr)->where('status', 'used')->exists();
        if ($alreadyClaimed) {
            return response()->json(['status' => 'error', 'message' => 'This UTR has already been claimed.'], 400);
        }

        // Try automatic verification with received payments table
        $received = ReceivedPayment::where('utr', $utr)->where('status', 'unused')->first();

        if ($received) {
            $actualAmount = (float) $received->amount;
            DB::beginTransaction();
            try {
                $user->balance += $actualAmount;
                $user->save();

                $received->status = 'used';
                $received->user_id = $user->id;
                $received->save();

                Transaction::create([
                    'user_id' => $user->id,
                    'payment_method' => 'UPI Auto',
                    'amount' => $actualAmount,
                    'payment_id' => $utr,
                    'status' => 'approved',
                    'notes' => 'Auto-approved via App UPI verification',
                ]);

                WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $actualAmount,
                    'type' => 'credit',
                    'description' => "Wallet Funded ₹{$actualAmount} via UPI UTR: {$utr}",
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => "Payment verified! ₹" . number_format($actualAmount, 2) . " credited to your wallet.",
                    'new_balance' => (float) $user->balance
                ]);
            } catch (\Throwable $e) {
                DB::rollBack();
                return response()->json(['status' => 'error', 'message' => 'Verification failed: ' . $e->getMessage()], 500);
            }
        }

        // Create pending manual transaction for admin review
        Transaction::create([
            'user_id' => $user->id,
            'payment_method' => 'UPI Manual',
            'amount' => $amount,
            'payment_id' => $utr,
            'status' => 'pending',
            'notes' => 'Submitted via Android App',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'UTR submitted successfully! Your balance will be credited as soon as verified.'
        ]);
    }

    /**
     * 10. Support Tickets List & Create
     */
    public function getTickets(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $tickets = Ticket::where('user_id', $user->id)
            ->with([
                'messages' => function ($q) {
                    $q->orderBy('id', 'asc');
                }
            ])
            ->get()
            ->map(function ($t) use ($user) {
                return [
                    'id' => $t->id,
                    'subject' => $t->subject,
                    'status' => $t->status,
                    'created_at' => $t->created_at ? $t->created_at->format('d M Y') : '',
                    'messages' => $t->messages->map(function ($m) use ($user) {
                        return [
                            'id' => $m->id,
                            'message' => $m->message,
                            'is_user' => ($m->user_id == $user->id),
                            'time' => $m->created_at ? $m->created_at->format('h:i A') : '',
                        ];
                    })
                ];
            });

        return response()->json([
            'status' => 'success',
            'tickets' => $tickets
        ]);
    }

    public function createTicket(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'subject' => trim($request->subject),
            'status' => 'open',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => trim($request->message),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Support ticket opened successfully!',
            'ticket_id' => $ticket->id
        ]);
    }

    /**
     * Get App Config (site name, logo, whatsapp channel, notice ticker, support number)
     */
    public function getAppConfig(Request $request)
    {
        $siteName = Setting::get('site_name', 'SMM Panel');
        $logoUrl = Setting::getLogoUrl();
        $waChannelUrl = Setting::get('whatsapp_channel_url', '');
        $waNumber = Setting::get('whatsapp_number', '');
        $tickerText = Setting::get('ticker_text', 'Welcome to ' . $siteName);

        return response()->json([
            'status' => 'success',
            'config' => [
                'site_name' => $siteName,
                'logo_url' => str_starts_with($logoUrl, 'http') ? $logoUrl : url($logoUrl),
                'whatsapp_channel_url' => $waChannelUrl,
                'whatsapp_number' => $waNumber,
                'ticker_text' => $tickerText,
            ]
        ]);
    }

    /**
     * Update Profile Info
     */
    public function updateProfile(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'whatsapp' => 'nullable|string|max:20',
        ]);

        $user->name = trim($request->name);
        if ($request->filled('whatsapp')) {
            $user->whatsapp = trim($request->whatsapp);
        }
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'whatsapp' => $user->whatsapp,
                'balance' => (float) $user->balance,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Change Password
     */
    public function changePassword(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Incorrect current password.'], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['status' => 'success', 'message' => 'Password updated successfully!']);
    }

    /**
     * Regenerate Developer API Key
     */
    public function generateApiKey(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $user->api_key = Str::random(32);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'API Key regenerated successfully!',
            'api_key' => $user->api_key
        ]);
    }

    /**
     * Refill Order Request
     */
    public function refillOrder(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $order = Order::where('user_id', $user->id)->find($id);
        if (!$order) {
            return response()->json(['status' => 'error', 'message' => 'Order not found.'], 404);
        }

        if (!$order->canRefill()) {
            return response()->json(['status' => 'error', 'message' => 'Refill is not available for this order.'], 400);
        }

        // Trigger Refill logic
        $order->refill_status = 'pending';
        $order->save();

        return response()->json(['status' => 'success', 'message' => 'Refill request submitted successfully!']);
    }

    /**
     * Payment & Wallet Transactions History
     */
    public function getPaymentHistory(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $transactions = Transaction::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => 'success',
            'transactions' => $transactions->map(function ($t) {
                return [
                    'id' => $t->id,
                    'payment_method' => $t->payment_method,
                    'amount' => (float) $t->amount,
                    'payment_id' => $t->payment_id,
                    'status' => $t->status,
                    'notes' => $t->notes,
                    'date' => $t->created_at ? $t->created_at->format('d M Y, h:i A') : '',
                ];
            }),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
        ]);
    }

    /**
     * Daily Scratch Card Status
     */
    public function getScratchCardToday(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        try {
            $service = app(\App\Services\ScratchCardService::class);
            $card = $service->getTodayCard($user);

            return response()->json([
                'status' => 'success',
                'available' => ($card->status === 'AVAILABLE'),
                'card_id' => $card->id,
                'reward_amount' => (float) ($card->reward_amount ?? 0),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'success', 'available' => false, 'card_id' => 0, 'reward_amount' => 0]);
        }
    }

    /**
     * Scratch Daily Reward
     */
    public function scratchCard(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate(['card_id' => 'required|integer']);

        try {
            $service = app(\App\Services\ScratchCardService::class);
            $result = $service->scratchCard($user, (int) $request->card_id);
            $user->refresh();

            return response()->json([
                'status' => 'success',
                'message' => $result['message'] ?? 'Reward claimed!',
                'reward_amount' => (float) ($result['reward_amount'] ?? 0),
                'new_balance' => (float) $user->balance,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Referrals Stats
     */
    public function getReferrals(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $refCode = $user->referral_code ?? strtoupper(substr(md5($user->id . 'rishi'), 0, 8));
        $refUrl = url('/register?ref=' . $refCode);
        $totalReferrals = User::where('referred_by', $user->id)->count();

        return response()->json([
            'status' => 'success',
            'referral_code' => $refCode,
            'referral_url' => $refUrl,
            'total_referrals' => $totalReferrals,
            'commission_rate' => 5, // 5% referral bonus
            'referral_earnings' => (float) ($user->referral_earnings ?? 0),
        ]);
    }

    /**
     * Reply to Ticket
     */
    public function replyTicket(Request $request, $id)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate(['message' => 'required|string|max:2000']);

        $ticket = Ticket::where('user_id', $user->id)->find($id);
        if (!$ticket) {
            return response()->json(['status' => 'error', 'message' => 'Ticket not found.'], 404);
        }

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => trim($request->message),
        ]);

        $ticket->status = 'client_reply';
        $ticket->save();

        return response()->json(['status' => 'success', 'message' => 'Reply sent successfully!']);
    }

    /**
     * Feedback History & Create
     */
    public function getFeedback(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $feedbacks = \App\Models\Feedback::where('user_id', $user->id)->latest()->get();

        return response()->json([
            'status' => 'success',
            'feedbacks' => $feedbacks->map(function ($f) {
                return [
                    'id' => $f->id,
                    'category' => $f->category,
                    'title' => $f->title,
                    'details' => $f->details,
                    'status' => $f->status,
                    'date' => $f->created_at ? $f->created_at->format('d M Y') : '',
                ];
            })
        ]);
    }

    public function createFeedback(Request $request)
    {
        $user = $this->getAuthenticatedUser($request);
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized / Invalid Token'], 401);
        }

        $request->validate([
            'category' => 'required|string',
            'title' => 'required|string|max:200',
            'details' => 'required|string|max:2000',
        ]);

        \App\Models\Feedback::create([
            'user_id' => $user->id,
            'category' => $request->category,
            'title' => trim($request->title),
            'details' => trim($request->details),
            'status' => 'pending',
        ]);

        return response()->json(['status' => 'success', 'message' => 'Feedback submitted successfully!']);
    }

    /**
     * Blogs
     */
    public function getBlogs(Request $request)
    {
        $blogs = \App\Models\Blog::where('status', 'published')->latest()->get();

        return response()->json([
            'status' => 'success',
            'blogs' => $blogs->map(function ($b) {
                return [
                    'id' => $b->id,
                    'title' => $b->title,
                    'slug' => $b->slug,
                    'excerpt' => Str::limit(strip_tags($b->content), 120),
                    'date' => $b->created_at ? $b->created_at->format('d M Y') : '',
                ];
            })
        ]);
    }

    public function getBlogDetail(Request $request, $slug)
    {
        $blog = \App\Models\Blog::where('slug', $slug)->where('status', 'published')->first();
        if (!$blog) {
            return response()->json(['status' => 'error', 'message' => 'Article not found.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'blog' => [
                'id' => $blog->id,
                'title' => $blog->title,
                'content' => $blog->content,
                'date' => $blog->created_at ? $blog->created_at->format('d M Y') : '',
            ]
        ]);
    }

    /**
     * Terms / Rules / Resellers / Privacy Text
     */
    public function getInfoPage(Request $request, $page)
    {
        $title = 'Information';
        $content = '';

        if ($page === 'rules') {
            $title = 'Terms & Rules';
            $content = "1. Account Safety: We never ask for passwords.\n2. Orders once placed cannot be canceled unless auto-failed by server.\n3. Refill Warranty: Services with Refill tag come with free 30-day warranty.\n4. Payments: Instant UPI QR deposits zero transaction fees.";
        } elseif ($page === 'resellers') {
            $title = 'Reseller Program';
            $content = "RishiSMM provides wholesale rates for digital agencies & resellers.\n- Connect via API using API Key\n- Automated dispatch node\n- Tiered VIP discounts based on monthly volume.";
        } elseif ($page === 'privacy') {
            $title = 'Privacy Policy';
            $content = "Your privacy is important to us. We store account email and order details securely. We never share personal info with third parties.";
        }

        return response()->json([
            'status' => 'success',
            'title' => $title,
            'content' => $content
        ]);
    }
}
