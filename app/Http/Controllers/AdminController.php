<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Category;
use App\Models\Service;
use App\Models\Provider;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Setting;
use App\Services\OrderStatusSyncService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class AdminController extends Controller
{
    public function index()
    {
        // Calculate Total Earnings and Net Profit based on configured Service Margin %
        $marginPct = (float) Setting::get('profit_margin', 20);
        if ($marginPct <= 0) {
            $marginPct = 20;
        }

        $deliveredOrders = Order::whereIn('status', ['completed', 'partial'])->get();

        $totalRevenue = 0;
        foreach ($deliveredOrders as $ord) {
            $charged = (float) $ord->charge;
            if ($ord->status === 'partial' && $ord->remains > 0 && $ord->quantity > 0) {
                $deliveredRatio = max(0, $ord->quantity - $ord->remains) / $ord->quantity;
                $charged = $charged * $deliveredRatio;
            }
            $totalRevenue += $charged;
        }

        $totalProfit = round(($totalRevenue * $marginPct) / 100, 2);
        $totalCost = max(0, $totalRevenue - $totalProfit);
        $profitMargin = $marginPct;

        $stats = [
            'users' => User::count(),
            'orders' => Order::count(),
            'revenue' => $totalRevenue,
            'cost' => $totalCost,
            'profit' => $totalProfit,
            'profit_margin' => $profitMargin,
            'pending_tickets' => Ticket::whereIn('status', ['open', 'client_reply'])->count(),
            'providers' => Provider::count(),
            'pending_manual_payments' => Transaction::where('status', 'pending')->count(),
        ];

        // 1. Weekly Revenue (last 7 days)
        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dayName = now()->subDays($i)->format('D');
            $sum = Order::whereDate('created_at', $date)->where('status', 'completed')->sum('charge');
            $weeklyRevenue[] = [
                'day' => $dayName,
                'amount' => (float) $sum,
            ];
        }

        // 2. Top Services
        $topServices = Order::select('service_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'), \Illuminate\Support\Facades\DB::raw('sum(charge) as total_charge'))
            ->groupBy('service_id')
            ->orderBy('count', 'desc')
            ->limit(3)
            ->with('service')
            ->get();

        // 3. Refund Stats
        $refundStats = [
            'count' => Order::where('refunded', true)->count(),
            'amount' => Transaction::where('payment_gateway', 'System Refund')->sum('amount'),
        ];

        // 4. Success Rate
        $totalOrders = Order::count();
        $successRate = $totalOrders > 0 ? round((Order::where('status', 'completed')->count() / $totalOrders) * 100, 1) : 100;

        $latestOrders = Order::with(['user', 'service'])->latest()->limit(5)->get();
        $latestTickets = Ticket::with('user')->whereIn('status', ['open', 'client_reply'])->latest()->limit(5)->get();
        $pendingPayments = Transaction::with('user')->where('status', 'pending')->latest()->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats',
            'latestOrders',
            'latestTickets',
            'pendingPayments',
            'weeklyRevenue',
            'topServices',
            'refundStats',
            'successRate'
        ));
    }

    // --- User Management ---
    public function users(Request $request)
    {
        $search = $request->query('search');
        $query = User::query();

        if ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('whatsapp', 'like', "%{$search}%");
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('admin.partials.users_table', compact('users', 'search'))->render(),
            ]);
        }

        return view('admin.users', compact('users', 'search'));
    }

    public function userUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:user,admin',
            'status' => 'required|in:active,suspended',
            'balance_action' => 'required|in:no_action,add,subtract,set',
            'balance_amount' => 'nullable|numeric|min:0',
            'password' => 'nullable|string|min:6',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $user) {
                $oldRole = $user->role;
                $oldStatus = $user->status;

                // Privilege escalation checks:
                if ($user->id === auth()->id() && $request->role !== 'admin') {
                    throw new \Exception('You cannot demote yourself from the administrator role.');
                }
                if ($user->id === auth()->id() && $request->status !== 'active') {
                    throw new \Exception('You cannot suspend your own administrator account.');
                }

                $user->role = $request->role;
                $user->status = $request->status;

                if ($request->balance_action !== 'no_action' && $request->filled('balance_amount')) {
                    $amount = (float) $request->balance_amount;
                    $prevBalance = $user->balance;
                    $logAmount = 0;
                    $walletAction = '';

                    if ($request->balance_action === 'add') {
                        $user->balance += $amount;
                        $walletAction = 'admin_add';
                        $logAmount = $amount;
                    } elseif ($request->balance_action === 'subtract') {
                        $user->balance = max(0, $user->balance - $amount);
                        $walletAction = 'admin_subtract';
                        $logAmount = -$amount;
                    } elseif ($request->balance_action === 'set') {
                        $user->balance = $amount;
                        $walletAction = 'admin_set';
                        $logAmount = $amount - $prevBalance;
                    }

                    $user->save();

                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'admin_id' => auth()->id(),
                        'amount' => $logAmount,
                        'previous_balance' => $prevBalance,
                        'new_balance' => $user->balance,
                        'action' => $walletAction,
                        'reference_id' => null,
                    ]);

                    \App\Models\ActivityLog::log('admin_wallet_adjust', [
                        'client_id' => $user->id,
                        'client_email' => $user->email,
                        'amount' => $logAmount,
                        'action' => $request->balance_action
                    ]);
                }

                if ($request->filled('password')) {
                    $user->password = Hash::make($request->password);
                    \App\Models\ActivityLog::log('admin_reset_password', [
                        'client_id' => $user->id,
                        'client_email' => $user->email
                    ]);
                }

                $user->save();

                \App\Models\ActivityLog::log('admin_user_update', [
                    'client_id' => $user->id,
                    'client_email' => $user->email,
                    'role_changed' => $oldRole !== $request->role,
                    'status_changed' => $oldStatus !== $request->status,
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "User '{$user->name}' updated successfully.");
    }

    public function userDelete($id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() == $user->id) {
            return back()->with('error', 'You cannot delete your own logged-in admin account.');
        }

        $userName = $user->name;
        \App\Models\ActivityLog::log('admin_user_delete', [
            'client_id' => $user->id,
            'client_email' => $user->email,
        ]);

        $user->delete();

        return back()->with('success', "User '{$userName}' has been deleted successfully.");
    }

    private function getSafeUrl(string $fallbackRouteName): string
    {
        $prev = url()->previous();
        $fallbackUrl = route($fallbackRouteName);
        $parsedPrev = $prev ? parse_url($prev, PHP_URL_PATH) : null;

        if ($prev && !in_array($parsedPrev, ['/admin', '/admin/', '/dashboard', '/'], true)) {
            return $prev;
        }

        return $fallbackUrl;
    }

    private function safeRedirect(string $fallbackRouteName, string $flashType, string $flashMessage, bool $withInput = false)
    {
        $targetUrl = $this->getSafeUrl($fallbackRouteName);
        $redirect = redirect()->to($targetUrl)->with($flashType, $flashMessage);
        return $withInput ? $redirect->withInput() : $redirect;
    }

    // --- Category Management ---
    public function categories(Request $request)
    {
        // Auto-purge empty categories (0 services / "Provider: No services") automatically on page load
        try {
            Category::doesntHave('services')->delete();
        } catch (\Throwable $e) {}

        $search = $request->query('search');
        $platform = $request->query('platform', 'all');
        $providerId = $request->query('provider_id', 'all');
        $sort = $request->query('sort', 'order_asc');

        $providers = \App\Models\Provider::orderBy('name', 'asc')->get();

        $query = Category::with([
            'services' => function ($q) {
                $q->with('provider:id,name');
            }
        ]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        if ($providerId !== 'all') {
            if ($providerId === 'manual') {
                $query->whereHas('services', function ($q) {
                    $q->whereNull('provider_id');
                });
            } else {
                $query->whereHas('services', function ($q) use ($providerId) {
                    $q->where('provider_id', $providerId);
                });
            }
        }

        // Auto-ensure is_pinned column exists in DB table
        $hasPinnedCol = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'is_pinned');
        if (!$hasPinnedCol) {
            try {
                \Illuminate\Support\Facades\Schema::table('categories', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->tinyInteger('is_pinned')->default(0);
                });
                $hasPinnedCol = true;
            } catch (\Throwable $ex) {
                // Ignore if DB user lacks ALTER TABLE permissions
            }
        }

        $allCats = $query->get()->sort(function ($a, $b) use ($hasPinnedCol, $sort) {
            if ($sort === 'order_desc') {
                if ($hasPinnedCol && (int) ($a->is_pinned ?? 0) !== (int) ($b->is_pinned ?? 0)) {
                    return (int) ($b->is_pinned ?? 0) <=> (int) ($a->is_pinned ?? 0);
                }
                if ((int) $a->sort_order !== (int) $b->sort_order) {
                    return (int) $b->sort_order <=> (int) $a->sort_order;
                }
                return (int) $b->id <=> (int) $a->id;
            } elseif ($sort === 'id_desc') {
                return (int) $b->id <=> (int) $a->id;
            } elseif ($sort === 'id_asc') {
                return (int) $a->id <=> (int) $b->id;
            } elseif ($sort === 'name_asc') {
                return strnatcasecmp($a->name, $b->name);
            } else {
                // Default: order_asc (#1 top, sort_order asc, id asc)
                if ($hasPinnedCol && (int) ($a->is_pinned ?? 0) !== (int) ($b->is_pinned ?? 0)) {
                    return (int) ($b->is_pinned ?? 0) <=> (int) ($a->is_pinned ?? 0);
                }
                if ((int) $a->sort_order !== (int) $b->sort_order) {
                    return (int) $a->sort_order <=> (int) $b->sort_order;
                }
                return (int) $a->id <=> (int) $b->id;
            }
        })->values();

        $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

        if ($platform && $platform !== 'all') {
            $filteredCats = $allCats->filter(function ($cat) use ($platform) {
                return $cat->platform === $platform;
            })->values();
        } else {
            $filteredCats = $allCats->filter(function ($cat) use ($enabledPlatforms) {
                $catPlat = $cat->platform;
                if ($catPlat && $catPlat !== 'others') {
                    return in_array($catPlat, $enabledPlatforms, true);
                }
                return true;
            })->values();
        }

        // Paginate filtered categories 100 items per page
        $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $perPage = 100;
        $currentItems = $filteredCats->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $categories = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $filteredCats->count(),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('admin.partials.categories_table', compact('categories', 'search', 'platform', 'providers', 'providerId', 'sort'))->render(),
            ]);
        }

        return view('admin.categories', compact('categories', 'search', 'platform', 'providers', 'providerId', 'sort'));
    }

    public function categoryTogglePin($id)
    {
        $category = Category::findOrFail($id);

        if (!\Illuminate\Support\Facades\Schema::hasColumn('categories', 'is_pinned')) {
            try {
                \Illuminate\Support\Facades\Schema::table('categories', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->tinyInteger('is_pinned')->default(0);
                });
            } catch (\Throwable $ex) {
                return back()->with('error', 'Database schema missing is_pinned column.');
            }
        }

        $category->is_pinned = $category->is_pinned ? 0 : 1;
        $category->save();
        \App\Services\PlatformHelper::clearCache();

        $statusMsg = $category->is_pinned ? "Category '{$category->name}' has been PINNED to the top!" : "Category '{$category->name}' has been unpinned.";
        if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => $statusMsg]);
        }
        return $this->safeRedirect('admin.categories', 'success', $statusMsg);
    }

    public function categoryStore(Request $request)
    {
        try {
            if ($request->has('id') && empty($request->id)) {
                $request->merge(['id' => null]);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'sort_order' => 'required|integer|min:1',
                'is_pinned' => 'nullable|in:0,1',
                'id' => 'nullable|exists:categories,id',
            ]);

            $id = $request->id ?: null;
            $targetOrder = max(1, (int) $request->sort_order);
            $isPinned = $request->has('is_pinned') ? (int) $request->is_pinned : 0;

            // Auto-ensure is_pinned column exists
            $hasPinnedCol = \Illuminate\Support\Facades\Schema::hasColumn('categories', 'is_pinned');
            if (!$hasPinnedCol) {
                try {
                    \Illuminate\Support\Facades\Schema::table('categories', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->tinyInteger('is_pinned')->default(0);
                    });
                    $hasPinnedCol = true;
                } catch (\Throwable $ex) {
                    // Ignore if DDL not allowed
                }
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($id, $request, $targetOrder, $isPinned, $hasPinnedCol) {
                $submittedName = \App\Services\BrandingSanitizer::clean($request->name);
                $catData = [
                    'name' => $submittedName,
                    'status' => $request->status,
                    'sort_order' => $targetOrder,
                ];
                if ($hasPinnedCol) {
                    $catData['is_pinned'] = $isPinned;
                }

                if ($id) {
                    Category::where('id', '!=', $id)
                        ->where('sort_order', '>=', $targetOrder)
                        ->increment('sort_order');

                    $cat = Category::find($id);
                    if ($cat) {
                        if ($request->has('reset_original') && $request->reset_original == '1' && !empty($cat->original_name)) {
                            $catData['name'] = $cat->original_name;
                            $catData['is_custom_name'] = false;
                        } else {
                            if (empty($cat->original_name)) {
                                $catData['original_name'] = $submittedName;
                            }
                            if ($cat->original_name && $submittedName !== $cat->original_name) {
                                $catData['is_custom_name'] = true;
                            }
                        }
                        $cat->update($catData);
                    }
                } else {
                    Category::where('sort_order', '>=', $targetOrder)
                        ->increment('sort_order');

                    $catData['original_name'] = $submittedName;
                    $catData['is_custom_name'] = false;
                    Category::create($catData);
                }

                // Re-index all categories sequentially (1, 2, 3, 4...)
                $allCats = Category::orderBy('sort_order', 'asc')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                $index = 1;
                foreach ($allCats as $cat) {
                    if ((int) $cat->sort_order !== $index) {
                        $cat->sort_order = $index;
                        $cat->save();
                    }
                    $index++;
                }
            });

            \App\Services\PlatformHelper::clearCache();

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Category saved successfully and sort orders updated.']);
            }

            return $this->safeRedirect('admin.categories', 'success', 'Category saved successfully and sort orders updated.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => implode(' ', $ve->validator->errors()->all())], 422);
            }
            return redirect()->to($this->getSafeUrl('admin.categories'))->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Category Store Error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
            }
            return $this->safeRedirect('admin.categories', 'error', 'Unable to save category: ' . $e->getMessage(), true);
        }
    }

    public function categoryDelete($id)
    {
        $category = Category::findOrFail($id);
        Service::where('category_id', $category->id)->delete();
        $category->delete();
        \App\Services\PlatformHelper::clearCache();

        if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => 'Category and all its services deleted successfully.']);
        }

        return $this->safeRedirect('admin.categories', 'success', 'Category and all its services deleted successfully.');
    }

    public function categoryCleanEmpty()
    {
        try {
            // Only delete categories that have 0 services in total (No services)
            // NEVER delete inactive services or categories that contain inactive services
            $totalCategoriesDeleted = Category::doesntHave('services')->delete();

            \App\Services\PlatformHelper::clearCache();

            return $this->safeRedirect('admin.categories', 'success', "Storage Cleanup Complete! Removed {$totalCategoriesDeleted} empty category(ies) (categories with 0 services). All inactive categories and inactive services have been preserved.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Category Clean Empty Error: " . $e->getMessage());
            return $this->safeRedirect('admin.categories', 'error', 'Cleanup error: ' . $e->getMessage());
        }
    }

    // --- Service Management ---
    public function services(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $providerId = $request->query('provider_id');
        $status = $request->query('status', 'all');
        $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

        $query = Service::with(['category', 'provider']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhere('provider_service_id', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('provider', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($providerId) {
            $query->where('provider_id', $providerId);
        }

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $services = $query->orderBy('category_id', 'asc')->paginate(50)->withQueryString();

        $categories = Category::all()->filter(function ($cat) use ($enabledPlatforms) {
            $catPlat = $cat->platform;
            if ($catPlat && $catPlat !== 'others') {
                return in_array($catPlat, $enabledPlatforms, true);
            }
            return true;
        })->values();

        $providers = Provider::where('status', 'active')->get();

        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'html' => view('admin.partials.services_table', compact('services', 'categories', 'providers', 'search', 'categoryId', 'providerId', 'status'))->render(),
            ]);
        }

        return view('admin.services', compact('services', 'categories', 'providers', 'search', 'categoryId', 'providerId', 'status'));
    }

    public function serviceStore(Request $request)
    {
        try {
            if ($request->has('id') && empty($request->id)) {
                $request->merge(['id' => null]);
            }

            $request->validate([
                'id' => 'nullable|exists:services,id',
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255',
                'price_per_k' => 'required|numeric|min:0',
                'min_quantity' => 'required|integer|min:1',
                'max_quantity' => 'required|integer|min:1',
                'status' => 'required|in:active,inactive',
                'sort_order' => 'nullable|integer|min:1',
                'provider_id' => 'nullable|exists:providers,id',
                'provider_service_id' => 'nullable|string',
                'provider_rate' => 'nullable|numeric|min:0',
                'average_time' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $id = $request->id ?: null;
            $catId = $request->category_id;

            if ($request->filled('sort_order')) {
                $targetOrder = max(1, (int) $request->sort_order);
            } else {
                if ($id) {
                    $existingSrv = Service::find($id);
                    $targetOrder = $existingSrv ? (int) $existingSrv->sort_order : ((int) Service::where('category_id', $catId)->max('sort_order') + 1);
                } else {
                    $targetOrder = (int) Service::where('category_id', $catId)->max('sort_order') + 1;
                }
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($id, $catId, $targetOrder, $request) {
                $submittedName = \App\Services\BrandingSanitizer::clean($request->name);

                if ($id) {
                    Service::where('category_id', $catId)
                        ->where('id', '!=', $id)
                        ->where('sort_order', '>=', $targetOrder)
                        ->increment('sort_order');

                    $srv = Service::find($id);
                    if ($srv) {
                        $srvData = [
                            'category_id' => $catId,
                            'name' => $submittedName,
                            'price_per_k' => $request->price_per_k,
                            'min_quantity' => $request->min_quantity,
                            'max_quantity' => $request->max_quantity,
                            'status' => $request->status,
                            'sort_order' => $targetOrder,
                            'provider_id' => $request->provider_id,
                            'provider_service_id' => $request->provider_service_id,
                            'provider_rate' => $request->provider_rate ?? 0,
                            'average_time' => $request->average_time,
                            'description' => $request->description,
                        ];

                        if ($request->has('reset_original') && $request->reset_original == '1' && !empty($srv->original_name)) {
                            $srvData['name'] = $srv->original_name;
                            $srvData['is_custom_name'] = false;
                        } else {
                            if (empty($srv->original_name)) {
                                $srvData['original_name'] = $submittedName;
                            }
                            if ($srv->original_name && $submittedName !== $srv->original_name) {
                                $srvData['is_custom_name'] = true;
                            }
                        }
                        $srv->update($srvData);
                    }
                } else {
                    Service::where('category_id', $catId)
                        ->where('sort_order', '>=', $targetOrder)
                        ->increment('sort_order');

                    Service::create([
                        'category_id' => $catId,
                        'name' => $submittedName,
                        'original_name' => $submittedName,
                        'is_custom_name' => false,
                        'price_per_k' => $request->price_per_k,
                        'min_quantity' => $request->min_quantity,
                        'max_quantity' => $request->max_quantity,
                        'status' => $request->status,
                        'sort_order' => $targetOrder,
                        'provider_id' => $request->provider_id,
                        'provider_service_id' => $request->provider_service_id,
                        'provider_rate' => $request->provider_rate ?? 0,
                        'average_time' => $request->average_time,
                        'description' => $request->description,
                    ]);
                }

                // Re-index all services in this category sequentially
                $catServices = Service::where('category_id', $catId)
                    ->orderBy('sort_order', 'asc')
                    ->orderBy('updated_at', 'desc')
                    ->get();

                $index = 1;
                foreach ($catServices as $srvItem) {
                    if ((int) $srvItem->sort_order !== $index) {
                        $srvItem->sort_order = $index;
                        $srvItem->save();
                    }
                    $index++;
                }
            });

            \App\Services\PlatformHelper::clearCache();

            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true, 'message' => 'Service saved successfully and sort orders updated.']);
            }

            return $this->safeRedirect('admin.services', 'success', 'Service saved successfully and sort orders updated.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => $ve->validator->errors()->first()], 422);
            }
            return redirect()->to($this->getSafeUrl('admin.services'))->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Service Store Error: ' . $e->getMessage());
            if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => false, 'message' => 'Unable to save service: ' . $e->getMessage()], 500);
            }
            return $this->safeRedirect('admin.services', 'error', 'Unable to save service: ' . $e->getMessage(), true);
        }
    }

    public function serviceDelete($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        \App\Services\PlatformHelper::clearCache();

        if (request()->ajax() || request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['success' => true, 'message' => 'Service deleted successfully.']);
        }

        return $this->safeRedirect('admin.services', 'success', 'Service deleted successfully.');
    }

    public function syncPlatformStatuses(Request $request)
    {
        try {
            $result = \App\Services\PlatformHelper::syncPlatformStatuses();
            return $this->safeRedirect('admin.services', 'success', "Platform statuses synchronized successfully! Deactivated: {$result['deactivated']} disabled platform services | Active: {$result['activated']} enabled platform services.");
        } catch (\Throwable $e) {
            return $this->safeRedirect('admin.services', 'error', 'Sync failed: ' . $e->getMessage());
        }
    }

    public function serviceImport(Request $request)
    {
        $request->validate([
            'provider_id' => 'required|exists:providers,id',
            'category_id' => 'required|exists:categories,id',
            'profit_margin' => 'required|numeric|min:-100', // Profit percentage markup
        ]);

        $provider = Provider::findOrFail($request->provider_id);
        $targetCat = Category::find($request->category_id);
        if ($targetCat && $targetCat->status !== 'active') {
            $targetCat->status = 'active';
            $targetCat->save();
        }

        try {
            // Increase timeout to 120s for fetching large services list
            $response = Http::timeout(120)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'services',
            ]);

            if ($response->successful()) {
                $services = $response->json();

                if (!is_array($services)) {
                    return back()->with('error', 'Invalid API response format from Provider.');
                }

                $importedCount = 0;
                $multiplier = 1 + ($request->profit_margin / 100);

                // Save profit margin setting for persistence
                Setting::set('profit_margin', $request->profit_margin);

                $updatedCount = 0;
                foreach ($services as $srv) {
                    $rate = (float) ($srv['rate'] ?? 0);

                    $sellingPrice = $rate * $multiplier;
                    $avgTime = $srv['time'] ?? $srv['average_time'] ?? null;

                    // Check if service already exists for this provider
                    $existing = Service::where('provider_id', $provider->id)
                        ->where('provider_service_id', $srv['service'])
                        ->first();

                    if ($existing) {
                        $existing->provider_rate = $rate;
                        $existing->price_per_k = $sellingPrice;
                        $existing->min_quantity = (int) ($srv['min'] ?? 10);
                        $existing->max_quantity = (int) ($srv['max'] ?? 10000);
                        $existing->average_time = $avgTime;
                        $existing->save();
                        $updatedCount++;
                    } else {
                        Service::create([
                            'category_id' => $request->category_id,
                            'name' => str_ireplace('smmbin', 'RishiSMM', $srv['name'] ?? 'Unnamed service'),
                            'description' => $srv['description'] ?? null,
                            'price_per_k' => $sellingPrice,
                            'min_quantity' => (int) ($srv['min'] ?? 10),
                            'max_quantity' => (int) ($srv['max'] ?? 10000),
                            'status' => 'active',
                            'provider_id' => $provider->id,
                            'provider_service_id' => $srv['service'],
                            'provider_rate' => $rate,
                            'average_time' => $avgTime,
                        ]);
                        $importedCount++;
                    }
                }

                return back()->with('success', "Import complete! Successfully added {$importedCount} new services and updated {$updatedCount} existing services.");
            }

            return back()->with('error', 'Failed to fetch services. Provider API response code: ' . $response->status());
        } catch (\Exception $e) {
            return back()->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    // --- Provider Management ---
    public function providers()
    {
        $providers = Provider::all();

        // Auto-refresh balance in the background/inline if last sync was more than 5 minutes ago
        foreach ($providers as $prov) {
            if (
                $prov->status === 'active' &&
                (!$prov->last_sync_success || now()->diffInMinutes($prov->last_sync_success) >= 5)
            ) {
                try {
                    $startTime = microtime(true);
                    // 3 seconds timeout to prevent blocking page render if provider API is down
                    $response = Http::timeout(3)->asForm()->post($prov->api_url, [
                        'key' => $prov->api_key,
                        'action' => 'balance',
                    ]);
                    $elapsed = (int) ((microtime(true) - $startTime) * 1000);
                    $prov->response_time_ms = $elapsed;

                    if ($response->successful()) {
                        $data = $response->json();
                        if (is_array($data) && isset($data['balance'])) {
                            $prov->balance = (float) $data['balance'];
                            $prov->last_sync_success = now();
                            $prov->save();
                        }
                    }
                } catch (\Exception $e) {
                    // Silence errors to ensure page rendering succeeds
                }
            }
        }

        $providers = Provider::all(); // Reload updated data
        $categories = Category::orderBy('name')->get();
        return view('admin.providers', compact('providers', 'categories'));
    }

    public function providerStore(Request $request)
    {
        $isUpdate = !empty($request->id);

        $request->validate([
            'id' => 'nullable|exists:providers,id',
            'name' => 'required|string|max:255',
            'api_url' => 'required|url',
            'api_key' => $isUpdate ? 'nullable|string' : 'required|string',
            'status' => 'required|in:active,inactive',
            'priority' => 'required|integer|min:1',
            'timeout' => 'required|integer|min:5|max:120',
            'retry_count' => 'required|integer|min:1|max:5',
            'currency' => 'nullable|string|max:10',
            'auto_sync' => 'required|boolean',
        ]);

        $data = [
            'name' => $request->name,
            'api_url' => $request->api_url,
            'status' => $request->status,
            'priority' => $request->priority,
            'timeout' => $request->timeout,
            'retry_count' => $request->retry_count,
            'currency' => 'INR',
            'auto_sync' => $request->auto_sync,
        ];

        // Only update api_key if explicitly provided
        if ($request->filled('api_key')) {
            $data['api_key'] = $request->api_key;
        }

        $provider = Provider::updateOrCreate(
            ['id' => $request->id],
            $data
        );

        $action = $request->id ? 'admin_provider_update' : 'admin_provider_create';
        \App\Models\ActivityLog::log($action, ['provider_name' => $request->name]);

        return back()->with('success', 'Provider details saved successfully.');
    }

    public function providerDelete($id)
    {
        $provider = Provider::findOrFail($id);
        \App\Models\ActivityLog::log('admin_provider_delete', ['provider_name' => $provider->name]);
        $provider->delete();
        return back()->with('success', 'Provider removed successfully.');
    }

    public function providerBalanceRefresh($id)
    {
        $provider = Provider::findOrFail($id);

        try {
            $startTime = microtime(true);
            $response = Http::timeout($provider->timeout ?: 10)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'balance',
            ]);

            $elapsed = (int) ((microtime(true) - $startTime) * 1000);
            $provider->response_time_ms = $elapsed;

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['balance'])) {
                    $provider->balance = (float) $data['balance'];
                    $provider->last_sync_success = now();
                    $provider->last_sync_error = null;
                    $provider->save();

                    \App\Models\ActivityLog::log('admin_provider_sync', ['provider_name' => $provider->name]);
                    return back()->with('success', "Provider '{$provider->name}' balance updated to " . $provider->balance);
                }
            }

            $provider->last_sync_failed = now();
            $provider->failed_requests_count += 1;
            $provider->last_sync_error = 'Unable to retrieve balance. Check API details or HTTP status code: ' . $response->status();
            $provider->save();

            return back()->with('error', 'Unable to retrieve balance. Check API details.');
        } catch (\Exception $e) {
            $provider->last_sync_failed = now();
            $provider->failed_requests_count += 1;
            $provider->last_sync_error = $e->getMessage();
            $provider->save();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function providerServicesList($id)
    {
        $provider = Provider::findOrFail($id);

        try {
            // Increase timeout to 120s for fetching large services list
            $response = Http::timeout(120)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'services',
            ]);

            if ($response->successful()) {
                $services = $response->json();

                if (!is_array($services)) {
                    return response()->json(['error' => 'Invalid API response format from Provider.'], 422);
                }

                // Mark which ones are currently ACTIVE in our database
                $importedIds = Service::where('provider_id', $provider->id)
                    ->where('status', 'active')
                    ->pluck('provider_service_id')
                    ->toArray();

                $activeCategories = Category::where('status', 'active')->orderBy('name')->get(['id', 'name']);

                $result = [];
                foreach ($services as $srv) {
                    $serviceId = $srv['service'] ?? null;
                    $srvName = $srv['name'] ?? '';
                    $catName = $srv['category'] ?? '';

                    $combined = strtolower($srvName . ' ' . $catName);
                    $isForbidden = preg_match('/\b(tiktok|tik\s*tok|twitter|spotify|linkedin|google|traffic|website\s+traffic|discord|twitch|snapchat|threads|pinterest|soundcloud|vimeo|clubhouse|onlyfans|kick)\b/i', $combined);

                    // Skip only explicitly forbidden platforms
                    if ($isForbidden) {
                        continue;
                    }

                    $result[] = [
                        'service' => $serviceId,
                        'name' => $srvName,
                        'category' => $catName,
                        'rate' => $srv['rate'] ?? 0,
                        'min' => $srv['min'] ?? 0,
                        'max' => $srv['max'] ?? 0,
                        'type' => $srv['type'] ?? '-',
                        'description' => $srv['description'] ?? '',
                        'already_imported' => in_array((string) $serviceId, $importedIds),
                    ];
                }

                return response()->json([
                    'provider' => $provider->name,
                    'currency' => 'INR',
                    'total' => count($result),
                    'services' => $result,
                    'categories' => $activeCategories,
                ]);
            }

            return response()->json(['error' => 'Failed to fetch services. Provider API response code: ' . $response->status()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function providerSyncServices(Request $request, $id)
    {
        $request->validate([
            'profit_margin' => 'required|numeric|min:-100|max:10000',
        ]);

        $provider = Provider::findOrFail($id);

        try {
            // Increase timeout to 120s for fetching large services list
            $response = Http::timeout(120)->asForm()->post($provider->api_url, [
                'key' => $provider->api_key,
                'action' => 'services',
            ]);

            if (!$response->successful()) {
                return back()->with('error', 'Failed to fetch services from provider API. HTTP Status: ' . $response->status());
            }

            $services = $response->json();

            if (!is_array($services)) {
                return back()->with('error', 'Invalid API response format from provider. Expected a list of services.');
            }

            $multiplier = 1 + ($request->profit_margin / 100);

            // Save profit margin setting for persistence
            Setting::set('profit_margin', $request->profit_margin);

            $importedCount = 0;
            $skippedCount = 0;
            $categoryCache = []; // cache category name => id to avoid repeated DB lookups

            // Pre-load existing imported service IDs for this provider
            $existingServiceIds = Service::where('provider_id', $provider->id)
                ->pluck('provider_service_id')
                ->flip()
                ->toArray();

            $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();
            $updatedCount = 0;
            $srvIndex = 0;
            foreach ($services as $srv) {
                $providerServiceId = (string) ($srv['service'] ?? '');
                if (!$providerServiceId)
                    continue;
                $srvIndex++;

                $srvRawName = $srv['name'] ?? '';
                $catRawName = $srv['category'] ?? '';

                $isAllowed = \App\Services\PlatformHelper::isPlatformEnabled($srvRawName, $catRawName, $enabledPlatforms);

                $existing = Service::where('provider_id', $provider->id)
                    ->where('provider_service_id', $providerServiceId)
                    ->first();

                $rate = (float) ($srv['rate'] ?? 0);
                $sellingPrice = $rate * $multiplier;
                $avgTime = $srv['time'] ?? $srv['average_time'] ?? null;

                if (!$isAllowed) {
                    if ($existing) {
                        $existing->provider_rate = $rate;
                        $existing->price_per_k = $sellingPrice;
                        if ($existing->status === 'active') {
                            $existing->status = 'inactive';
                        }
                        $existing->save();
                    }
                    continue;
                }

                $categoryName = trim($srv['category'] ?? 'General');
                $cleanCategoryName = \App\Services\BrandingSanitizer::clean($categoryName, $provider->name) ?: 'General';

                $isJunkCat = preg_match('/\b(pvt|private|testing|test|dummy|junk|trash)\b/i', $cleanCategoryName)
                    || preg_match('/end\s*x+/i', $cleanCategoryName)
                    || preg_match('/❌\s*end/i', $cleanCategoryName)
                    || preg_match('/inttttt/i', $cleanCategoryName);

                if ($isJunkCat) {
                    if ($existing) {
                        $existing->provider_rate = $rate;
                        $existing->price_per_k = $sellingPrice;
                        if ($existing->status === 'active') {
                            $existing->status = 'inactive';
                        }
                        $existing->save();
                    }
                    continue;
                }

                if (!isset($categoryCache[$cleanCategoryName])) {
                    $category = Category::where('original_name', $cleanCategoryName)
                        ->orWhere('name', $cleanCategoryName)
                        ->first();

                    if (!$category) {
                        // Place new category at end of sort order (max sort order + 1)
                        // Do NOT override existing category sort orders set by admin
                        $nextCatOrder = (Category::max('sort_order') ?? 0) + 1;

                        $category = Category::create([
                            'name' => $cleanCategoryName,
                            'original_name' => $cleanCategoryName,
                            'is_custom_name' => false,
                            'status' => 'active',
                            'sort_order' => $nextCatOrder,
                        ]);
                    } else {
                        // Preserve existing sort_order set by admin
                        $category->original_name = $cleanCategoryName;
                        if (!$category->is_custom_name) {
                            $category->name = $cleanCategoryName;
                        }
                        if ($category->status === 'inactive') {
                            $category->status = 'active';
                        }
                        $category->save();
                    }
                    $categoryCache[$cleanCategoryName] = $category->id;
                }
                $catId = $categoryCache[$cleanCategoryName];

                if ($existing) {
                    $existing->category_id = $catId;
                    $existing->name = \App\Services\BrandingSanitizer::clean($srv['name'] ?? $existing->name, $provider->name);
                    if (isset($srv['description'])) {
                        $existing->description = \App\Services\BrandingSanitizer::clean($srv['description'], $provider->name);
                    }
                    $existing->provider_rate = $rate;
                    $existing->price_per_k = $sellingPrice;
                    $existing->min_quantity = (int) ($srv['min'] ?? 10);
                    $existing->max_quantity = (int) ($srv['max'] ?? 10000);
                    if ($avgTime) {
                        $existing->average_time = $avgTime;
                    }
                    $existing->save();
                    $updatedCount++;
                    continue;
                }

                $nextSrvOrder = (Service::where('category_id', $catId)->max('sort_order') ?? 0) + 1;
                Service::create([
                    'category_id' => $catId,
                    'name' => \App\Services\BrandingSanitizer::clean($srv['name'] ?? 'Unnamed service', $provider->name),
                    'description' => \App\Services\BrandingSanitizer::clean($srv['description'] ?? null, $provider->name),
                    'price_per_k' => $sellingPrice,
                    'min_quantity' => (int) ($srv['min'] ?? 10),
                    'max_quantity' => (int) ($srv['max'] ?? 10000),
                    'status' => 'active',
                    'sort_order' => $nextSrvOrder,
                    'provider_id' => $provider->id,
                    'provider_service_id' => $providerServiceId,
                    'provider_rate' => $rate,
                    'average_time' => $avgTime,
                ]);
                $importedCount++;
            }

            $totalApi = count($services);
            $newCategories = count($categoryCache);

            // Collect valid provider service IDs returned by API
            $apiServiceIds = array_filter(array_map(function ($s) {
                return (string) ($s['service'] ?? '');
            }, $services));

            // Auto-deactivate services no longer returned by provider API
            $disabledCount = 0;
            if (!empty($apiServiceIds)) {
                $disabledCount = Service::where('provider_id', $provider->id)
                    ->whereNotIn('provider_service_id', $apiServiceIds)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);
            }

            // Auto-delete empty categories that have 0 services
            Category::doesntHave('services')->delete();

            // Update provider sync status to successful
            $provider->last_sync_success = now();
            $provider->last_sync_error = null;
            $provider->failed_requests_count = 0;
            $provider->save();

            \App\Models\ActivityLog::log('admin_provider_sync_services', [
                'provider_name' => $provider->name,
                'total_api' => $totalApi,
                'imported' => $importedCount,
                'updated' => $updatedCount,
                'disabled' => $disabledCount,
                'categories_created' => $newCategories,
            ]);

            \App\Services\PlatformHelper::clearCache();

            return back()->with('success', "Sync complete! Fetched {$totalApi} services from {$provider->name}. Imported: {$importedCount} new | Updated: {$updatedCount} existing services | Auto-Disabled Removed: {$disabledCount}");
        } catch (\Exception $e) {
            Log::error("Provider sync services failed for [{$provider->name}]: " . $e->getMessage());
            return back()->with('error', 'Error syncing services: ' . $e->getMessage());
        }
    }

    public function providerImportSelectedServices(Request $request, $id)
    {
        $request->validate([
            'profit_margin' => 'required|numeric|min:-100|max:10000',
            'category_mapping' => 'required|string',
            'services' => 'required|array',
            'services.*.service' => 'required',
            'services.*.name' => 'required|string',
            'services.*.rate' => 'required|numeric',
        ]);

        $provider = Provider::findOrFail($id);

        try {
            $multiplier = 1 + ($request->profit_margin / 100);
            $targetCategoryId = null;

            if ($request->category_mapping !== 'auto') {
                $targetCategoryId = (int) $request->category_mapping;
                Category::findOrFail($targetCategoryId); // Validate existence
            }

            $importedCount = 0;
            $updatedCount = 0;
            $deactivatedCount = 0;
            $categoryCache = [];

            // 1. Deactivate services linked to this provider that were UNCHECKED in the request
            $selectedProviderServiceIds = array_filter(array_map(function ($s) {
                return (string) ($s['service'] ?? '');
            }, $request->services));

            if (!empty($selectedProviderServiceIds)) {
                $deactivatedCount = Service::where('provider_id', $provider->id)
                    ->whereNotIn('provider_service_id', $selectedProviderServiceIds)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);
            }

            $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

            // 2. Loop through checked services and set status = 'active'
            foreach ($request->services as $srv) {
                $providerServiceId = (string) ($srv['service'] ?? '');
                if (!$providerServiceId)
                    continue;

                $srvRawName = $srv['name'] ?? '';
                $catRawName = $srv['category'] ?? '';

                if (!\App\Services\PlatformHelper::isPlatformEnabled($srvRawName, $catRawName, $enabledPlatforms)) {
                    continue;
                }

                $rate = (float) ($srv['rate'] ?? 0);
                $sellingPrice = $rate * $multiplier;
                $avgTime = $srv['time'] ?? $srv['average_time'] ?? null;

                // Determine target category ID and ensure Category is ACTIVE
                if ($targetCategoryId !== null) {
                    $catId = $targetCategoryId;
                    $targetCat = Category::find($catId);
                    if ($targetCat && $targetCat->status !== 'active') {
                        $targetCat->status = 'active';
                        $targetCat->save();
                    }
                } else {
                    $categoryName = trim($srv['category'] ?? 'General');
                    $categoryName = \App\Services\BrandingSanitizer::clean($categoryName, $provider->name);

                    if (!isset($categoryCache[$categoryName])) {
                        $category = Category::where('name', $categoryName)->first();
                        if (!$category) {
                            $category = Category::create([
                                'name' => $categoryName,
                                'status' => 'active',
                                'sort_order' => (Category::max('sort_order') ?? 0) + 1,
                            ]);
                        } else {
                            if ($category->status !== 'active') {
                                $category->status = 'active';
                                $category->save();
                            }
                        }
                        $categoryCache[$categoryName] = $category->id;
                    }
                    $catId = $categoryCache[$categoryName];
                }

                // Check if already exists for this provider
                $existing = Service::where('provider_id', $provider->id)
                    ->where('provider_service_id', $providerServiceId)
                    ->first();

                if ($existing) {
                    $existing->category_id = $catId;
                    $existing->name = \App\Services\BrandingSanitizer::clean($srv['name'] ?? $existing->name, $provider->name);
                    if (isset($srv['description'])) {
                        $existing->description = \App\Services\BrandingSanitizer::clean($srv['description'], $provider->name);
                    }
                    $existing->provider_rate = $rate;
                    $existing->price_per_k = $sellingPrice;
                    $existing->min_quantity = (int) ($srv['min'] ?? 10);
                    $existing->max_quantity = (int) ($srv['max'] ?? 10000);
                    $existing->status = 'active'; // Ensure status is active
                    if ($avgTime) {
                        $existing->average_time = $avgTime;
                    }
                    $existing->save();
                    $updatedCount++;
                } else {
                    Service::create([
                        'category_id' => $catId,
                        'name' => \App\Services\BrandingSanitizer::clean($srv['name'] ?? 'Unnamed service', $provider->name),
                        'description' => \App\Services\BrandingSanitizer::clean($srv['description'] ?? null, $provider->name),
                        'price_per_k' => $sellingPrice,
                        'min_quantity' => (int) ($srv['min'] ?? 10),
                        'max_quantity' => (int) ($srv['max'] ?? 10000),
                        'status' => 'active',
                        'provider_id' => $provider->id,
                        'provider_service_id' => $providerServiceId,
                        'provider_rate' => $rate,
                        'average_time' => $avgTime,
                    ]);
                    $importedCount++;
                }
            }

            // 3. Deactivate categories that now have zero active services
            $activeCategoryIds = Service::where('status', 'active')->pluck('category_id')->unique();
            Category::whereNotIn('id', $activeCategoryIds)->update(['status' => 'inactive']);

            \App\Services\PlatformHelper::clearCache();

            return response()->json([
                'success' => true,
                'message' => "Successfully updated services! Active: {$importedCount} imported / {$updatedCount} updated | Deactivated: {$deactivatedCount} unchecked services."
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }

    // --- Order Management ---
    public function orders(Request $request)
    {
        if (!class_exists(OrderStatusSyncService::class)) {
            $serviceFile = app_path('Services/OrderStatusSyncService.php');
            if (file_exists($serviceFile)) {
                require_once $serviceFile;
            }
        }

        if (class_exists(OrderStatusSyncService::class)) {
            OrderStatusSyncService::sync(null, false);
        }

        $search = $request->query('search');
        $status = $request->query('status');
        $perPage = (int) $request->query('per_page', 20);
        if (!in_array($perPage, [10, 20, 50, 100, 500])) {
            $perPage = 20;
        }

        $query = Order::with(['user', 'service', 'latestLog']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                    ->orWhere('link', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($status && in_array($status, ['pending', 'processing', 'in_progress', 'completed', 'partial', 'canceled', 'failed'])) {
            $query->where('status', $status);
        }

        $orders = $query->latest()->paginate($perPage)->withQueryString();
        return view('admin.orders', compact('orders', 'search', 'status', 'perPage'));
    }

    public function orderUpdate(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,in_progress,completed,partial,canceled,failed',
            'start_count' => 'required|integer|min:0',
            'remains' => 'required|integer|min:0',
            'provider_order_id' => 'nullable|string',
        ]);

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order, $request) {
                $oldStatus = $order->status;
                $newStatus = $request->status;

                $order->start_count = $request->start_count;
                $order->remains = $request->remains;
                $order->provider_order_id = $request->provider_order_id;

                // Balance Refund Operations
                if ($newStatus === 'canceled' && $oldStatus !== 'canceled' && !$order->refunded) {
                    $order->start_count = $request->start_count;
                    $order->remains = $request->remains;
                    \App\Services\RefundService::process($order, 'canceled');
                } elseif ($newStatus === 'partial' && $oldStatus !== 'partial' && !$order->refunded) {
                    $order->start_count = $request->start_count;
                    \App\Services\RefundService::process($order, 'partial', (int) $request->remains);
                } else {
                    $order->start_count = $request->start_count;
                    $order->remains = $request->remains;
                    $order->status = $newStatus;
                    $order->save();
                }

                \App\Models\ActivityLog::log('admin_order_update', [
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus
                ]);
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Order #{$order->id} status updated successfully.");
    }

    public function orderRetry($id)
    {
        $order = Order::findOrFail($id);

        \App\Models\ActivityLog::log('admin_order_retry', ['order_id' => $order->id]);

        $success = \App\Services\OrderProcessor::dispatch($order);

        if ($success) {
            return back()->with('success', "Order #{$order->id} retried successfully at provider API!");
        }
        return back()->with('error', "Order #{$order->id} retry attempt failed. Check processing logs below.");
    }

    public function orderLogs($id)
    {
        $logs = \App\Models\OrderProcessingLog::where('order_id', $id)
            ->with('provider')
            ->latest()
            ->get();
        return response()->json($logs);
    }

    // --- Ticket & Communication Management ---
    public function tickets()
    {
        $tickets = Ticket::with('user')->latest()->paginate(15);
        return view('admin.tickets', compact('tickets'));
    }

    public function ticketShow($id)
    {
        $ticket = Ticket::with(['user', 'messages.user'])->findOrFail($id);
        return view('admin.ticket_show', compact('ticket'));
    }

    public function ticketReply(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);

        $request->validate([
            'message' => 'required|string|max:2000',
            'close' => 'nullable|boolean',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        $ticket->status = $request->has('close') ? 'closed' : 'answered';
        $ticket->save();

        return back()->with('success', 'Reply sent successfully.');
    }

    // --- Setting & Configuration Management ---
    public function settings()
    {
        $settings = [
            'site_name' => Setting::get('site_name', 'RishiSMM'),
            'site_logo' => Setting::get('site_logo', ''),
            'site_favicon' => Setting::get('site_favicon', ''),
            'support_email' => Setting::get('support_email', 'support.example.com'),
            'force_2fa' => Setting::get('force_2fa', 'disabled'),
            'currency_symbol' => Setting::get('currency_symbol', '₹'),
            'whatsapp_number' => Setting::get('whatsapp_number', ''),
            'whatsapp_channel_url' => Setting::get('whatsapp_channel_url', ''),
            'default_platform' => Setting::get('default_platform', 'all'),
            'enabled_platforms' => Setting::get('enabled_platforms', json_encode(['all', 'instagram', 'facebook', 'youtube', 'telegram', 'whatsapp', 'tiktok', 'twitter'])),
            'ticker_text' => Setting::get('ticker_text', ''),
            'payment_upi_id' => Setting::get('payment_upi_id', 'rishismm@upi'),
            'payment_upi_qr' => Setting::get('payment_upi_qr', ''),
            'payment_bank_details' => Setting::get('payment_bank_details', ''),
            'profit_margin' => Setting::get('profit_margin', 20),
            'order_delivery_bonus_percent' => (float)Setting::get('order_delivery_bonus_percent', 0),
            'payment_webhook_token' => Setting::get('payment_webhook_token') ?: (function () {
                $tok = \Illuminate\Support\Str::random(32);
                \App\Models\Setting::set('payment_webhook_token', $tok);
                return $tok;
            })(),
            'custom_css' => Setting::get('custom_css', ''),

            // SMTP Configurations (Secrets are masked for safe UI presentation)
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_password' => !empty(Setting::get('mail_password')) ? '••••••••••••••••' : '',
            'has_mail_password' => !empty(Setting::get('mail_password')),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', ''),

            // Google OAuth Configurations (Secret masked for UI)
            'google_client_id' => Setting::get('google_client_id', ''),
            'google_client_secret' => !empty(Setting::get('google_client_secret')) ? '••••••••••••••••' : '',
            'has_google_client_secret' => !empty(Setting::get('google_client_secret')),
            'google_login_status' => Setting::get('google_login_status', 'disabled'),

            // Security & Bot Protection (reCAPTCHA v3)
            'recaptcha_status' => Setting::get('recaptcha_status', 'disabled'),
            'recaptcha_site_key' => Setting::get('recaptcha_site_key', ''),
            'recaptcha_secret_key' => !empty(Setting::get('recaptcha_secret_key')) ? '••••••••••••••••' : '',
            'has_recaptcha_secret_key' => !empty(Setting::get('recaptcha_secret_key')),
            'recaptcha_score_threshold' => (float) Setting::get('recaptcha_score_threshold', 0.5),

            // Deposit Bonus Configurations
            'deposit_bonus_status' => Setting::get('deposit_bonus_status', 'enabled'),
            'bonus_t1_min' => Setting::get('bonus_t1_min', 100),
            'bonus_t1_percent' => Setting::get('bonus_t1_percent', 1),
            'bonus_t2_min' => Setting::get('bonus_t2_min', 1000),
            'bonus_t2_percent' => Setting::get('bonus_t2_percent', 2),
            'bonus_t3_min' => Setting::get('bonus_t3_min', 5000),
            'bonus_t3_percent' => Setting::get('bonus_t3_percent', 5),

            // Welcome Announcement Popup Modal Configurations
            'popup_status' => Setting::get('popup_status', 'enabled'),
            'popup_mode' => Setting::get('popup_mode', 'image_and_content'),
            'popup_title' => Setting::get('popup_title', '🔥 Special Deposit Offers & Launch Your Own SMM Panel!'),
            'popup_image' => Setting::get('popup_image', ''),
            'popup_content' => Setting::get('popup_content', ''),
            'popup_btn1_text' => Setting::get('popup_btn1_text', '💬 Get Your Own SMM Panel'),
            'popup_btn1_link' => Setting::get('popup_btn1_link', ''),
            'popup_btn2_text' => Setting::get('popup_btn2_text', '💰 Add Funds & Get Bonus'),
            'popup_btn2_link' => Setting::get('popup_btn2_link', '/add-funds'),
            'popup_frequency' => Setting::get('popup_frequency', 'once_per_session'),
        ];

        // Also fetch pending deposits list for quick action in settings page if needed
        $pendingDeposits = Transaction::with('user')->where('payment_gateway', 'UPI/Manual Bank')->where('status', 'pending')->get();

        return view('admin.settings', compact('settings', 'pendingDeposits'));
    }

    public function settingsUpdate(Request $request)
    {
        // 1. Check if there's any action on manual transactions (Approve)
        if ($request->has('approve_txn_id')) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                    $txn = Transaction::lockForUpdate()->findOrFail($request->approve_txn_id);
                    if ($txn->status === 'pending') {
                        $txn->status = 'completed';
                        if ($request->filled('notes')) {
                            $txn->notes = $request->notes;
                        }
                        $txn->save();

                        $isDeposit = ($txn->payment_gateway !== 'System Refund' && !str_starts_with($txn->payment_id, 'REFUND_'));
                        $baseAmount = (float) $txn->amount;
                        $bonusAmount = $isDeposit ? \App\Http\Controllers\PaymentController::calculateDepositBonus($baseAmount) : 0;
                        $totalAmount = $baseAmount + $bonusAmount;

                        $user = User::lockForUpdate()->findOrFail($txn->user_id);
                        $prevBalance = $user->balance;
                        $user->balance += $totalAmount;
                        $user->save();

                        // Write base deposit to transaction ledger
                        \App\Models\WalletTransaction::create([
                            'user_id' => $user->id,
                            'admin_id' => auth()->id(),
                            'amount' => $baseAmount,
                            'previous_balance' => $prevBalance,
                            'new_balance' => $prevBalance + $baseAmount,
                            'action' => 'deposit_approve',
                            'reference_id' => $txn->payment_id,
                        ]);

                        // Write deposit bonus to ledger if applicable
                        if ($bonusAmount > 0) {
                            \App\Models\WalletTransaction::create([
                                'user_id' => $user->id,
                                'admin_id' => auth()->id(),
                                'amount' => $bonusAmount,
                                'previous_balance' => $prevBalance + $baseAmount,
                                'new_balance' => $user->balance,
                                'action' => 'deposit_bonus',
                                'reference_id' => 'BONUS-' . $txn->payment_id,
                            ]);
                        }

                        // Process Referral Commission for Referrer (if applicable)
                        \App\Services\ReferralService::processDepositCommission($user, $baseAmount, $txn->payment_id);

                        \App\Models\ActivityLog::log('admin_deposit_approve', [
                            'txn_id' => $txn->id,
                            'amount' => $baseAmount,
                            'bonus' => $bonusAmount,
                            'client_email' => $user->email,
                            'utr' => $txn->payment_id
                        ]);
                    }
                });
                return back()->with('success', "Transaction verified and balance credited successfully with applicable bonus.");
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // 2. Check if there's any action on manual transactions (Reject)
        if ($request->has('reject_txn_id')) {
            try {
                \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                    $txn = Transaction::lockForUpdate()->findOrFail($request->reject_txn_id);
                    if ($txn->status === 'pending') {
                        $txn->status = 'failed';
                        if ($request->filled('notes')) {
                            $txn->notes = $request->notes;
                        }
                        $txn->save();

                        $user = $txn->user;

                        \App\Models\ActivityLog::log('admin_deposit_reject', [
                            'txn_id' => $txn->id,
                            'amount' => $txn->amount,
                            'client_email' => $user ? $user->email : 'unknown',
                            'utr' => $txn->payment_id
                        ]);
                    }
                });
                return back()->with('success', "Transaction marked as rejected.");
            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        // 3. Check if this is an SMTP test email trigger
        if ($request->filled('test_email_recipient')) {
            $request->validate([
                'test_email_recipient' => 'required|email',
                'mail_host' => 'required|string',
                'mail_port' => 'required|integer',
                'mail_username' => 'nullable|string',
                'mail_password' => 'nullable|string',
                'mail_encryption' => 'required|string|in:ssl,tls,none',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'nullable|string',
            ]);

            try {
                // Dynamically load SMTP configs for testing (preserving existing secret if field left blank)
                $activeSmtpPassword = $request->filled('mail_password') ? $request->mail_password : Setting::get('mail_password');

                config([
                    'mail.mailers.smtp.host' => $request->mail_host,
                    'mail.mailers.smtp.port' => $request->mail_port,
                    'mail.mailers.smtp.username' => $request->mail_username,
                    'mail.mailers.smtp.password' => $activeSmtpPassword,
                    'mail.mailers.smtp.encryption' => $request->mail_encryption === 'none' ? null : $request->mail_encryption,
                    'mail.from.address' => $request->mail_from_address,
                    'mail.from.name' => $request->mail_from_name ?: $request->site_name,
                ]);

                \Illuminate\Support\Facades\Mail::raw("Connection Test successful! This test email confirms your SMTP server configurations in the RishiSMM SMM Panel are working perfectly.", function ($message) use ($request) {
                    $message->to($request->test_email_recipient)->subject("RishiSMM SMM Panel - SMTP Connection Test");
                });

                return back()->with('success', 'SMTP Test Email sent successfully to: ' . $request->test_email_recipient);
            } catch (\Exception $e) {
                return back()->with('error', 'SMTP Connection failure: ' . $e->getMessage())->withInput();
            }
        }

        // 4. Standard settings update validation
        try {
            $request->validate([
                'site_name' => 'required|string|max:100',
                'site_logo' => 'nullable|string|max:1000',
                'site_logo_file' => 'nullable|file|image|mimes:jpeg,png,jpg,webp,svg,ico|max:5120',
                'site_favicon' => 'nullable|string|max:1000',
                'site_favicon_file' => 'nullable|file|image|mimes:jpeg,png,jpg,webp,svg,ico|max:5120',
                'support_email' => 'required|email|max:255',
                'force_2fa' => 'required|in:enabled,disabled',
                'currency_symbol' => 'nullable|string|max:10',

                'whatsapp_number' => 'nullable|string|max:20',
                'whatsapp_channel_url' => 'nullable|url|max:255',
                'default_platform' => 'nullable|string|in:all,instagram,facebook,youtube,telegram,whatsapp,tiktok,twitter,spotify,discord',
                'enabled_platforms' => 'nullable|array',
                'enabled_platforms.*' => 'string|in:all,instagram,facebook,youtube,telegram,whatsapp,tiktok,twitter,spotify,discord',
                'payment_upi_id' => 'nullable|string|max:100',
                'payment_upi_qr' => 'nullable|string|max:500',
                'payment_upi_qr_file' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048',
                'popup_image_file' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:2048',
                'payment_bank_details' => 'nullable|string',
                'payment_webhook_token' => 'nullable|string|max:100',
                'custom_css' => 'nullable|string',
                'ticker_text' => 'nullable|string|max:1000',

                // SMTP Options validation
                'mail_host' => 'nullable|string|max:255',
                'mail_port' => 'nullable|integer',
                'mail_username' => 'nullable|string|max:255',
                'mail_password' => 'nullable|string|max:255',
                'mail_encryption' => 'nullable|string|in:ssl,tls,none',
                'mail_from_address' => 'nullable|email|max:255',
                'mail_from_name' => 'nullable|string|max:255',

                // Google login OAuth validation
                'google_client_id' => 'nullable|string|max:255',
                'google_client_secret' => 'nullable|string|max:255',
                'google_login_status' => 'required|in:enabled,disabled',

                // reCAPTCHA v3 validation
                'recaptcha_status' => 'nullable|in:enabled,disabled',
                'recaptcha_site_key' => 'nullable|string|max:255',
                'recaptcha_secret_key' => 'nullable|string|max:255',
                'recaptcha_score_threshold' => 'nullable|numeric|min:0.1|max:1.0',
            ]);

            Setting::set('site_name', $request->site_name, 'general', 'string', 0);
            Setting::set('support_email', $request->support_email, 'general', 'string', 0);
            Setting::set('force_2fa', $request->force_2fa, 'security', 'boolean', 0);
            Setting::set('currency_symbol', '₹', 'general', 'string', 0);

            Setting::set('whatsapp_number', $request->whatsapp_number, 'general', 'string', 0);
            Setting::set('whatsapp_channel_url', $request->whatsapp_channel_url ?? 'https://whatsapp.com/channel', 'general', 'string', 0);
            Setting::set('default_platform', $request->default_platform ?? 'all', 'platform', 'string', 0);
            if ($request->filled('profit_margin')) {
                Setting::set('profit_margin', (float) $request->profit_margin, 'platform', 'float', 0);
            }

            $enabledPlatforms = $request->has('enabled_platforms') ? (array) $request->input('enabled_platforms') : ['all'];
            if (!in_array('all', $enabledPlatforms)) {
                array_unshift($enabledPlatforms, 'all');
            }
            Setting::set('enabled_platforms', json_encode(array_values(array_unique($enabledPlatforms))), 'platform', 'json', 0);

            // Automatically synchronize active/inactive status of services based on newly selected platforms
            \App\Services\PlatformHelper::syncPlatformStatuses();

            Setting::set('payment_upi_id', $request->payment_upi_id, 'payment', 'string', 0);

            if ($request->hasFile('payment_upi_qr_file')) {
                try {
                    $path = \App\Services\SecureUploadService::saveImage($request->file('payment_upi_qr_file'), 'qr');
                    Setting::set('payment_upi_qr', asset($path), 'payment', 'string', 0);
                } catch (\InvalidArgumentException $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                }
            } elseif ($request->filled('payment_upi_qr')) {
                Setting::set('payment_upi_qr', $request->payment_upi_qr, 'payment', 'string', 0);
            }

            Setting::set('payment_bank_details', $request->payment_bank_details, 'payment', 'text', 0);

            // Webhook Token: Preserve existing secret if submitted blank, or update if provided
            if ($request->filled('payment_webhook_token')) {
                Setting::set('payment_webhook_token', trim($request->payment_webhook_token), 'payment', 'string', 1);
            }

            Setting::set('custom_css', $request->custom_css, 'appearance', 'text', 0);
            Setting::set('ticker_text', $request->ticker_text, 'appearance', 'string', 0);

            // Save SMTP configurations (Preserve password if submitted blank)
            Setting::set('mail_host', $request->mail_host, 'mail', 'string', 0);
            Setting::set('mail_port', $request->mail_port, 'mail', 'integer', 0);
            Setting::set('mail_username', $request->mail_username, 'mail', 'string', 0);
            if ($request->filled('mail_password')) {
                Setting::set('mail_password', $request->mail_password, 'mail', 'string', 1);
            }
            Setting::set('mail_encryption', $request->mail_encryption, 'mail', 'string', 0);
            Setting::set('mail_from_address', $request->mail_from_address, 'mail', 'string', 0);
            Setting::set('mail_from_name', $request->mail_from_name, 'mail', 'string', 0);

            // Save Google login settings (Preserve secret if submitted blank)
            Setting::set('google_client_id', $request->google_client_id, 'oauth', 'string', 0);
            if ($request->filled('google_client_secret')) {
                Setting::set('google_client_secret', $request->google_client_secret, 'oauth', 'string', 1);
            }
            Setting::set('google_login_status', $request->google_login_status, 'oauth', 'boolean', 0);

            // Save reCAPTCHA v3 settings (Preserve secret if submitted blank)
            Setting::set('recaptcha_status', $request->input('recaptcha_status', 'disabled'), 'security', 'boolean', 0);
            Setting::set('recaptcha_site_key', $request->recaptcha_site_key, 'security', 'string', 0);
            if ($request->filled('recaptcha_secret_key')) {
                Setting::set('recaptcha_secret_key', $request->recaptcha_secret_key, 'security', 'string', 1);
            }
            if ($request->filled('recaptcha_score_threshold')) {
                Setting::set('recaptcha_score_threshold', (float) $request->recaptcha_score_threshold, 'security', 'float', 0);
            }

            // Save Deposit Bonus settings
            Setting::set('deposit_bonus_status', $request->deposit_bonus_status ?? 'enabled', 'bonus', 'boolean', 0);
            Setting::set('bonus_t1_min', $request->bonus_t1_min ?? 100, 'bonus', 'float', 0);
            Setting::set('bonus_t1_percent', $request->bonus_t1_percent ?? 1, 'bonus', 'float', 0);
            Setting::set('bonus_t2_min', $request->bonus_t2_min ?? 1000, 'bonus', 'float', 0);
            Setting::set('bonus_t2_percent', $request->bonus_t2_percent ?? 2, 'bonus', 'float', 0);
            Setting::set('bonus_t3_min', $request->bonus_t3_min ?? 5000, 'bonus', 'float', 0);
            Setting::set('bonus_t3_percent', $request->bonus_t3_percent ?? 5, 'bonus', 'float', 0);

            // Save Order Delivery Bonus Buffer Percentage (e.g. 5% extra to prevent under-delivery complaints)
            if ($request->has('order_delivery_bonus_percent')) {
                Setting::set('order_delivery_bonus_percent', max(0, (float)$request->order_delivery_bonus_percent), 'general', 'float', 0);
            }

            // Save Welcome Popup Modal settings
            if ($request->has('popup_status')) {
                Setting::set('popup_status', $request->popup_status);
            }
            if ($request->has('popup_mode')) {
                Setting::set('popup_mode', $request->popup_mode);
            }
            if ($request->has('popup_title')) {
                Setting::set('popup_title', $request->popup_title);
            }
            if ($request->has('popup_content')) {
                Setting::set('popup_content', $request->popup_content);
            }
            if ($request->has('popup_btn1_text')) {
                Setting::set('popup_btn1_text', $request->popup_btn1_text);
            }
            if ($request->has('popup_btn1_link')) {
                Setting::set('popup_btn1_link', $request->popup_btn1_link);
            }
            if ($request->has('popup_btn2_text')) {
                Setting::set('popup_btn2_text', $request->popup_btn2_text);
            }
            if ($request->has('popup_btn2_link')) {
                Setting::set('popup_btn2_link', $request->popup_btn2_link);
            }
            if ($request->has('popup_frequency')) {
                Setting::set('popup_frequency', $request->popup_frequency);
            }

            if ($request->hasFile('popup_image_file')) {
                try {
                    $path = \App\Services\SecureUploadService::saveImage($request->file('popup_image_file'), 'popup');
                    Setting::set('popup_image', asset($path));
                } catch (\InvalidArgumentException $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                }
            } elseif ($request->has('remove_popup_image') && $request->remove_popup_image == 1) {
                Setting::set('popup_image', '');
            } elseif ($request->filled('popup_image_url')) {
                Setting::set('popup_image', $request->popup_image_url);
            }

            \App\Models\ActivityLog::log('admin_settings_update');

            return back()->with('success', 'System configurations updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return back()->withErrors($ve->validator)->withInput();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Settings Update Error: ' . $e->getMessage());
            return back()->with('error', 'Unable to update configurations: ' . $e->getMessage())->withInput();
        }
    }

    // --- System Health & Cron Job Monitoring ---
    public function health()
    {
        $cronKey = Setting::get('cron_secret_key');
        if (empty($cronKey)) {
            $cronKey = \Illuminate\Support\Str::random(32);
            Setting::set('cron_secret_key', $cronKey);
        }

        $mysqlVersion = 'MySQL/MariaDB';
        try {
            $mysqlVersion = \Illuminate\Support\Facades\DB::getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Throwable $e) {
            try {
                $verRes = \Illuminate\Support\Facades\DB::select("SELECT VERSION() as v");
                $mysqlVersion = $verRes[0]->v ?? 'MySQL/MariaDB';
            } catch (\Throwable $ex) {}
        }

        $diskFree = 'Not Available';
        try {
            $bytes = @disk_free_space(base_path());
            if ($bytes !== false && $bytes > 0) {
                $diskFree = number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
            }
        } catch (\Throwable $e) {}

        $diskTotal = 'Not Available';
        try {
            $bytes = @disk_total_space(base_path());
            if ($bytes !== false && $bytes > 0) {
                $diskTotal = number_format($bytes / (1024 * 1024 * 1024), 2) . ' GB';
            }
        } catch (\Throwable $e) {}

        $failedJobs = 0;
        try {
            $failedJobs = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {}

        $failedRequests = 0;
        try {
            $failedRequests = \App\Models\OrderProcessingLog::where('action', 'failover_switch')->count();
        } catch (\Throwable $e) {}

        $health = [
            'php' => PHP_VERSION,
            'laravel' => app()->version(),
            'mysql' => $mysqlVersion,
            'disk_free' => $diskFree,
            'disk_total' => $diskTotal,
            'last_cron' => Setting::get('last_cron_execution', 'Never Run'),
            'failed_jobs' => $failedJobs,
            'failed_provider_requests' => $failedRequests,
            'queue_status' => config('queue.default', 'sync'),
            'cron_key' => $cronKey,
            'cron_web_url' => url('/cron/run?key=' . $cronKey),
            'cron_cli_cmd' => '* * * * * php ' . base_path() . '/artisan schedule:run >> /dev/null 2>&1',
            'cron_curl_cmd' => '* * * * * curl -s "' . url('/cron/run?key=' . $cronKey) . '" >/dev/null 2>&1',
        ];

        return view('admin.health', compact('health'));
    }

    /**
     * Public Web Cron Endpoint for shared hosting / external cron services (cron-job.org).
     */
    public function runCronWeb(Request $request)
    {
        @set_time_limit(120);
        @ini_set('memory_limit', '512M');
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }

        $key = Setting::get('cron_secret_key');
        $providedKey = $request->query('key') ?? $request->input('key');

        if (!empty($key) && $providedKey && !hash_equals($key, (string)$providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid cron security key.'
            ], 403);
        }

        try {
            $output = '';

            // Execute order status sync directly
            Artisan::call('orders:sync-status');
            $output .= Artisan::output();

            // Execute provider health sync
            Artisan::call('provider:sync-health');
            $output .= "\n" . Artisan::output();

            // Check & email Admin for deposits pending over 1 minute without auto-approval
            \App\Http\Controllers\PaymentController::notifyAdminPendingDeposits();

            Setting::set('last_cron_execution', now()->toDateTimeString());

            return response()->json([
                'success' => true,
                'message' => 'Web Cron executed successfully.',
                'timestamp' => now()->toDateTimeString(),
                'output' => trim($output) ?: 'Cron execution completed cleanly.'
            ]);
        } catch (\Exception $e) {
            Log::error('Web Cron Execution Failure: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Cron execution error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger manual cron execution from admin health dashboard.
     */
    public function cronRunManual()
    {
        try {
            Artisan::call('schedule:run');
            $output = Artisan::output();
            Setting::set('last_cron_execution', now()->toDateTimeString());

            return back()->with('success', 'Cron schedule executed successfully! Last run: ' . now()->format('d M Y, h:i:s A'));
        } catch (\Exception $e) {
            Log::error('Admin Manual Cron Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to run cron schedule: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate secure Cron API Key.
     */
    public function cronRegenerateKey()
    {
        $newKey = \Illuminate\Support\Str::random(32);
        Setting::set('cron_secret_key', $newKey);
        return back()->with('success', 'Cron security key regenerated successfully.');
    }

    /**
     * Securely clear system cache, precompiled templates, routes, config, and OPcache.
     */
    public function cacheClear(Request $request)
    {
        try {
            // Reset PHP OPcache if supported on hosting
            if (function_exists('opcache_reset')) {
                @opcache_reset();
            }

            // Clear view cache, route cache, config cache, event cache, and application cache
            Artisan::call('optimize:clear');

            \App\Models\ActivityLog::log('admin_cache_clear', [
                'admin_email' => Auth::user()->email ?? 'admin',
            ]);

            return back()->with('success', 'System cache, precompiled views, routes, config, and OPcache cleared successfully.');
        } catch (\Exception $e) {
            Log::error('Admin Cache Clear Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to clear system cache: ' . $e->getMessage());
        }
    }

    // --- Payments & Deposit Transactions Management ---
    public function transactions(Request $request)
    {
        $status = $request->query('status', 'all');
        $search = trim($request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Base query with eager-loaded user relationship
        $query = Transaction::with('user')->latest('id');

        if ($status !== 'all' && in_array($status, ['pending', 'completed', 'failed'])) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('payment_id', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhere('payment_gateway', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $transactions = $query->paginate(20)->withQueryString();

        // Metrics for summary KPI cards
        $metrics = [
            'total_amount' => Transaction::where('status', 'completed')->sum('amount'),
            'total_count' => Transaction::where('status', 'completed')->count(),
            'pending_count' => Transaction::where('status', 'pending')->count(),
            'pending_amount' => Transaction::where('status', 'pending')->sum('amount'),
            'today_amount' => Transaction::where('status', 'completed')->whereDate('created_at', today())->sum('amount'),
            'today_count' => Transaction::where('status', 'completed')->whereDate('created_at', today())->count(),
            'total_bonus' => \App\Models\WalletTransaction::where('action', 'deposit_bonus')->sum('amount'),
        ];

        return view('admin.transactions', compact('transactions', 'metrics', 'status', 'search', 'dateFrom', 'dateTo'));
    }

    public function transactionApprove(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $txn = Transaction::lockForUpdate()->findOrFail($id);
                if ($txn->status !== 'pending') {
                    throw new \Exception("This transaction is already marked as {$txn->status}.");
                }

                $txn->status = 'completed';
                if ($request->filled('notes')) {
                    $txn->notes = $request->notes;
                }
                $txn->save();

                $isDeposit = ($txn->payment_gateway !== 'System Refund' && !str_starts_with($txn->payment_id, 'REFUND_'));
                $baseAmount = (float) $txn->amount;
                $bonusAmount = $isDeposit ? \App\Http\Controllers\PaymentController::calculateDepositBonus($baseAmount) : 0;
                $totalAmount = $baseAmount + $bonusAmount;

                $user = User::lockForUpdate()->findOrFail($txn->user_id);
                $prevBalance = $user->balance;
                $user->balance += $totalAmount;
                $user->save();

                // 1. Write base deposit to wallet ledger
                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'admin_id' => auth()->id(),
                    'amount' => $baseAmount,
                    'previous_balance' => $prevBalance,
                    'new_balance' => $prevBalance + $baseAmount,
                    'action' => 'deposit_approve',
                    'reference_id' => $txn->payment_id,
                ]);

                // 2. Write deposit bonus to wallet ledger if applicable
                if ($bonusAmount > 0) {
                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'admin_id' => auth()->id(),
                        'amount' => $bonusAmount,
                        'previous_balance' => $prevBalance + $baseAmount,
                        'new_balance' => $user->balance,
                        'action' => 'deposit_bonus',
                        'reference_id' => 'BONUS-' . $txn->payment_id,
                    ]);
                }

                // 3. Mark matching unused ReceivedPayment as used if present
                $matchedRec = \App\Models\ReceivedPayment::where('utr', $txn->payment_id)->where('status', 'unused')->first();
                if ($matchedRec) {
                    $matchedRec->status = 'used';
                    $matchedRec->user_id = $user->id;
                    $matchedRec->save();
                }

                // 4. Process Referral Commission for Referrer (if applicable)
                if ($isDeposit) {
                    \App\Services\ReferralService::processDepositCommission($user, $baseAmount, $txn->payment_id);
                }

                // 5. Log activity
                \App\Models\ActivityLog::log('admin_deposit_approve', [
                    'txn_id' => $txn->id,
                    'amount' => $baseAmount,
                    'bonus' => $bonusAmount,
                    'client_email' => $user->email,
                    'utr' => $txn->payment_id
                ]);

                // 5. Send confirmation email to user
                try {
                    $siteName = Setting::get('site_name', 'RishiSMM');
                    $userSubject = "Deposit Approved & Balance Credited - ₹" . number_format($baseAmount, 2);
                    $userTitle = "Deposit Approved Successfully";
                    $bonusNote = $bonusAmount > 0 ? "<br>• <strong>Bonus Added:</strong> +₹" . number_format($bonusAmount, 2) : "";
                    $userBody = "Hello {$user->name},<br><br>" .
                        "Your deposit request has been verified and approved!<br><br>" .
                        "<strong>Transaction Details:</strong><br>" .
                        "• <strong>Amount Credited:</strong> ₹" . number_format($baseAmount, 2) . $bonusNote . "<br>" .
                        "• <strong>Total Added to Balance:</strong> ₹" . number_format($totalAmount, 2) . "<br>" .
                        "• <strong>UTR / Ref ID:</strong> <code style='background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;'>{$txn->payment_id}</code><br>" .
                        "• <strong>New Wallet Balance:</strong> ₹" . number_format($user->balance, 2) . "<br><br>" .
                        "Thank you for choosing {$siteName}!";

                    Setting::sendEmail(
                        $user->email,
                        $userSubject,
                        $userTitle,
                        $userBody,
                        [
                            'btnText' => 'Place New Order',
                            'btnUrl' => route('dashboard')
                        ]
                    );
                } catch (\Exception $mailEx) {
                    \Illuminate\Support\Facades\Log::error("Deposit confirmation email failed: " . $mailEx->getMessage());
                }
            });

            return back()->with('success', 'Deposit approved and balance credited to customer wallet successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function transactionReject(Request $request, $id)
    {
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($request, $id) {
                $txn = Transaction::lockForUpdate()->findOrFail($id);
                if ($txn->status !== 'pending') {
                    throw new \Exception("This transaction is already marked as {$txn->status}.");
                }

                $txn->status = 'failed';
                if ($request->filled('notes')) {
                    $txn->notes = $request->notes;
                }
                $txn->save();

                $user = $txn->user;

                \App\Models\ActivityLog::log('admin_deposit_reject', [
                    'txn_id' => $txn->id,
                    'amount' => $txn->amount,
                    'client_email' => $user ? $user->email : 'unknown',
                    'utr' => $txn->payment_id,
                    'notes' => $txn->notes,
                ]);
            });

            return back()->with('success', 'Deposit marked as rejected.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function viewScreenshot($id)
    {
        $txn = Transaction::findOrFail($id);
        if (!$txn->screenshot || str_contains($txn->screenshot, '..') || str_starts_with($txn->screenshot, '/') || str_starts_with($txn->screenshot, '\\')) {
            return abort(404, 'Payment screenshot receipt file not found.');
        }

        if (\Illuminate\Support\Facades\Storage::disk('local')->exists($txn->screenshot)) {
            return response()->file(storage_path('app/private/' . $txn->screenshot));
        }

        // Try fallback if stored direct in app/screenshots
        if (file_exists(storage_path('app/' . $txn->screenshot))) {
            return response()->file(storage_path('app/' . $txn->screenshot));
        }

        return abort(404, 'Payment screenshot receipt file not found.');
    }

    /*
     |--------------------------------------------------------------------------
     | Blog Management Methods
     |--------------------------------------------------------------------------
     */

    public function blogs(Request $request)
    {
        $query = \App\Models\Blog::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $blogs = $query->latest()->paginate(15)->withQueryString();

        return view('admin.blogs.index', compact('blogs'));
    }

    public function blogCreate()
    {
        return view('admin.blogs.create');
    }

    public function blogStore(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:4096',
            'status' => 'required|in:published,draft',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            try {
                $imagePath = \App\Services\SecureUploadService::saveImage($request->file('image'), 'blogs');
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        $slug = \App\Models\Blog::generateUniqueSlug($request->input('title'));
        $content = $request->input('content');
        $excerpt = $request->input('excerpt') ?: \Illuminate\Support\Str::limit(strip_tags($content), 160);

        \App\Models\Blog::create([
            'title' => $request->input('title'),
            'slug' => $slug,
            'category' => $request->input('category'),
            'excerpt' => $excerpt,
            'content' => $content,
            'image' => $imagePath,
            'author' => Auth::user()->name ?: 'Admin',
            'status' => $request->input('status'),
            'meta_title' => $request->input('meta_title') ?: $request->input('title'),
            'meta_description' => $request->input('meta_description') ?: $excerpt,
        ]);

        return redirect()->route('admin.blogs')->with('success', 'Blog article published successfully!');
    }

    public function blogEdit($id)
    {
        $blog = \App\Models\Blog::findOrFail($id);
        return view('admin.blogs.edit', compact('blog'));
    }

    public function blogUpdate(Request $request, $id)
    {
        $blog = \App\Models\Blog::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:4096',
            'status' => 'required|in:published,draft',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $imagePath = $blog->image;
        if ($request->hasFile('image')) {
            if ($blog->image && file_exists(public_path($blog->image))) {
                @unlink(public_path($blog->image));
            }

            try {
                $imagePath = \App\Services\SecureUploadService::saveImage($request->file('image'), 'blogs');
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        if ($blog->title !== $request->input('title')) {
            $blog->slug = \App\Models\Blog::generateUniqueSlug($request->input('title'), $blog->id);
        }

        $content = $request->input('content');
        $excerpt = $request->input('excerpt') ?: \Illuminate\Support\Str::limit(strip_tags($content), 160);

        $blog->update([
            'title' => $request->input('title'),
            'category' => $request->input('category'),
            'excerpt' => $excerpt,
            'content' => $content,
            'image' => $imagePath,
            'status' => $request->input('status'),
            'meta_title' => $request->input('meta_title') ?: $request->input('title'),
            'meta_description' => $request->input('meta_description') ?: $excerpt,
        ]);

        return redirect()->route('admin.blogs')->with('success', 'Blog post updated successfully!');
    }

    public function blogDelete($id)
    {
        $blog = \App\Models\Blog::findOrFail($id);
        if ($blog->image && file_exists(public_path($blog->image))) {
            @unlink(public_path($blog->image));
        }
        $blog->delete();
        return redirect()->route('admin.blogs')->with('success', 'Blog article deleted successfully.');
    }

    // --- REFERRAL MANAGEMENT ---
    public function referrals(Request $request)
    {
        $status = Setting::get('referral_status', 'disabled');
        $commissionPercent = (float) Setting::get('referral_commission_percent', 2.0);
        $minDeposit = (float) Setting::get('referral_min_deposit', 100);
        $maxPerDeposit = (float) Setting::get('referral_max_commission_per_deposit', 100);
        $monthlyCap = (float) Setting::get('referral_monthly_cap', 0);

        $referralsQuery = \App\Models\Referral::with(['referrer', 'referred']);
        if ($request->filled('status')) {
            $referralsQuery->where('status', $request->status);
        }
        $referrals = $referralsQuery->latest()->paginate(15, ['*'], 'referrals_page')->withQueryString();

        $commissionsQuery = \App\Models\ReferralCommission::with(['referrer', 'referred']);
        if ($request->filled('comm_status')) {
            $commissionsQuery->where('status', $request->comm_status);
        }
        $commissions = $commissionsQuery->latest()->paginate(15, ['*'], 'commissions_page')->withQueryString();

        $pendingCommissions = \App\Models\ReferralCommission::with(['referrer', 'referred'])
            ->where('status', 'pending_review')
            ->latest()
            ->get();

        $suspiciousReferrals = \App\Models\Referral::with(['referrer', 'referred'])
            ->whereIn('status', ['pending_review', 'suspicious'])
            ->latest()
            ->get();

        return view('admin.referrals', compact(
            'status',
            'commissionPercent',
            'minDeposit',
            'maxPerDeposit',
            'monthlyCap',
            'referrals',
            'commissions',
            'pendingCommissions',
            'suspiciousReferrals'
        ));
    }

    public function referralSettingsUpdate(Request $request)
    {
        $request->validate([
            'status' => 'required|in:enabled,disabled',
            'commission_percent' => 'required|numeric|min:0|max:100',
            'min_deposit' => 'required|numeric|min:0',
            'max_commission_per_deposit' => 'required|numeric|min:0',
            'monthly_cap' => 'nullable|numeric|min:0',
        ]);

        Setting::set('referral_status', $request->status, 'general', 'string', 0);
        Setting::set('referral_commission_percent', $request->commission_percent, 'general', 'string', 0);
        Setting::set('referral_min_deposit', $request->min_deposit, 'general', 'string', 0);
        Setting::set('referral_max_commission_per_deposit', $request->max_commission_per_deposit, 'general', 'string', 0);
        Setting::set('referral_monthly_cap', $request->monthly_cap ?? 0, 'general', 'string', 0);

        return back()->with('success', 'Referral system settings updated successfully.');
    }

    public function referralUpdateStatus(Request $request, $id)
    {
        $referral = \App\Models\Referral::findOrFail($id);
        $request->validate(['status' => 'required|in:active,pending_review,suspicious,blocked']);
        $referral->status = $request->status;
        $referral->save();

        return back()->with('success', "Referral #{$referral->id} status updated to {$request->status}.");
    }

    public function referralCommissionUpdateStatus(Request $request, $id)
    {
        $comm = \App\Models\ReferralCommission::findOrFail($id);
        $request->validate(['status' => 'required|in:approved,rejected,reversed']);

        if ($comm->status === $request->status) {
            return back()->with('info', 'No change in status.');
        }

        DB::transaction(function () use ($comm, $request) {
            $oldStatus = $comm->status;
            $comm->status = $request->status;
            $comm->save();

            // Credit referrer if changing from pending to approved
            if ($oldStatus !== 'approved' && $request->status === 'approved') {
                $referrer = User::lockForUpdate()->find($comm->referrer_id);
                if ($referrer) {
                    $prev = $referrer->balance;
                    $referrer->balance += $comm->commission_amount;
                    $referrer->save();

                    \App\Models\WalletTransaction::create([
                        'user_id' => $referrer->id,
                        'admin_id' => auth()->id(),
                        'amount' => $comm->commission_amount,
                        'previous_balance' => $prev,
                        'new_balance' => $referrer->balance,
                        'action' => 'referral_commission',
                        'reference_id' => 'REF-' . $comm->id,
                    ]);
                }
            }

            // Reconcile/Deduct if reversing an approved commission
            if ($oldStatus === 'approved' && in_array($request->status, ['reversed', 'rejected'])) {
                $referrer = User::lockForUpdate()->find($comm->referrer_id);
                if ($referrer) {
                    $prev = $referrer->balance;
                    $referrer->balance = max(0, $referrer->balance - $comm->commission_amount);
                    $referrer->save();

                    \App\Models\WalletTransaction::create([
                        'user_id' => $referrer->id,
                        'admin_id' => auth()->id(),
                        'amount' => -$comm->commission_amount,
                        'previous_balance' => $prev,
                        'new_balance' => $referrer->balance,
                        'action' => 'admin_adjustment',
                        'reference_id' => 'REF-REV-' . $comm->id,
                    ]);
                }
            }
        });

        return back()->with('success', "Commission #{$comm->id} status updated to {$request->status}.");
    }

    // --- CUSTOMER TIERS MANAGEMENT ---
    public function tiers()
    {
        $tiers = \App\Models\CustomerTier::orderBy('sort_order', 'asc')->orderBy('min_spending', 'asc')->get();
        return view('admin.tiers', compact('tiers'));
    }

    public function tierStore(Request $request)
    {
        $request->validate([
            'id' => 'nullable|exists:customer_tiers,id',
            'name' => 'required|string|max:100',
            'min_spending' => 'required|numeric|min:0',
            'max_spending' => 'nullable|numeric|min:0',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'sort_order' => 'required|integer|min:0',
        ]);

        \App\Models\CustomerTier::updateOrCreate(
            ['id' => $request->id],
            [
                'name' => strtoupper(trim($request->name)),
                'min_spending' => $request->min_spending,
                'max_spending' => $request->max_spending,
                'discount_percentage' => $request->discount_percentage,
                'status' => $request->status,
                'sort_order' => $request->sort_order,
            ]
        );

        return back()->with('success', 'Customer tier saved successfully.');
    }

    public function tierDelete($id)
    {
        $tier = \App\Models\CustomerTier::findOrFail($id);
        $tier->delete();
        return back()->with('success', 'Customer tier deleted successfully.');
    }

    // --- INDIVIDUAL CUSTOMER DISCOUNTS ---
    public function discounts(Request $request)
    {
        $query = \App\Models\CustomerDiscount::with(['user', 'creator']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
            });
        }

        $discounts = $query->latest()->paginate(20)->withQueryString();
        $users = User::where('status', 'active')->orderBy('name', 'asc')->get(['id', 'name', 'email']);

        return view('admin.discounts', compact('discounts', 'users'));
    }

    public function discountStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:active,inactive',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'reason' => 'nullable|string|max:255',
        ]);

        \App\Models\CustomerDiscount::updateOrCreate(
            ['user_id' => $request->user_id],
            [
                'discount_percentage' => $request->discount_percentage,
                'status' => $request->status,
                'starts_at' => $request->starts_at,
                'ends_at' => $request->ends_at,
                'reason' => $request->reason,
                'created_by' => auth()->id(),
            ]
        );

        return back()->with('success', 'Customer discount saved successfully.');
    }

    public function discountToggle($id)
    {
        $discount = \App\Models\CustomerDiscount::findOrFail($id);
        $discount->status = $discount->status === 'active' ? 'inactive' : 'active';
        $discount->save();

        return back()->with('success', 'Customer discount status updated.');
    }

    public function discountDelete($id)
    {
        $discount = \App\Models\CustomerDiscount::findOrFail($id);
        $discount->delete();

        return back()->with('success', 'Customer discount deleted successfully.');
    }

    // --- CUSTOMER FEEDBACK MANAGEMENT ---
    public function feedbacks(Request $request)
    {
        $query = \App\Models\Feedback::with('user');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $feedbacks = $query->latest()->paginate(15)->withQueryString();

        return view('admin.feedbacks', compact('feedbacks'));
    }

    public function feedbackStatusUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $feedback = \App\Models\Feedback::findOrFail($id);
        $feedback->status = $request->status;
        if ($request->has('admin_notes')) {
            $feedback->admin_notes = $request->admin_notes;
        }
        $feedback->save();

        return back()->with('success', 'Feedback status updated successfully.');
    }

    // --- SOCIAL PLATFORMS MANAGEMENT ---
    public function platforms()
    {
        $platforms = \App\Models\SocialPlatform::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.platforms', compact('platforms'));
    }

    public function platformStore(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'name' => 'required|string|max:100',
            'key' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:50',
            'keywords' => 'nullable|string|max:1000',
            'is_enabled' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $name = trim($request->name);
        $key = $request->filled('key') ? \Illuminate\Support\Str::slug($request->key, '_') : \Illuminate\Support\Str::slug($name, '_');

        if (empty($key)) {
            $key = 'platform_' . time();
        }

        // Ensure key is unique to prevent SQL duplicate entry 500 errors
        $targetId = $request->filled('id') ? (int)$request->id : null;
        $existingKeyCount = \App\Models\SocialPlatform::where('key', $key)
            ->when($targetId, function ($q) use ($targetId) {
                return $q->where('id', '!=', $targetId);
            })->count();

        if ($existingKeyCount > 0) {
            $key = $key . '_' . time();
        }

        $platformData = [
            'key' => $key,
            'name' => $name,
            'icon' => $request->filled('icon') ? trim($request->icon) : 'fa-solid fa-globe',
            'color' => $request->filled('color') ? trim($request->color) : '#0284c7',
            'keywords' => $request->keywords ? strtolower(trim($request->keywords)) : strtolower($name),
            'is_enabled' => $request->has('is_enabled') ? true : false,
            'sort_order' => (int)($request->sort_order ?? 0),
        ];

        if ($targetId) {
            $platform = \App\Models\SocialPlatform::find($targetId);
            if ($platform) {
                $platform->update($platformData);
            } else {
                \App\Models\SocialPlatform::create($platformData);
            }
        } else {
            \App\Models\SocialPlatform::create($platformData);
        }

        \App\Services\PlatformHelper::syncPlatformStatuses();

        return back()->with('success', 'Social Platform saved successfully!');
    }

    public function platformToggle($id)
    {
        $platform = \App\Models\SocialPlatform::findOrFail($id);
        $platform->is_enabled = !$platform->is_enabled;
        $platform->save();

        \App\Services\PlatformHelper::syncPlatformStatuses();

        return back()->with('success', "Platform '{$platform->name}' " . ($platform->is_enabled ? 'enabled' : 'disabled') . ' successfully.');
    }

    public function platformSetDefault($id)
    {
        \App\Models\SocialPlatform::query()->update(['is_default' => false]);
        $platform = \App\Models\SocialPlatform::findOrFail($id);
        $platform->is_default = true;
        $platform->save();

        // Also update default_platform in settings table
        Setting::set('default_platform', $platform->key, 'platform', 'string', 0);
        \App\Services\PlatformHelper::clearCache();

        return back()->with('success', "Platform '{$platform->name}' set as default pre-selected platform!");
    }

    public function platformDelete($id)
    {
        $platform = \App\Models\SocialPlatform::findOrFail($id);
        if ($platform->key === 'all') {
            return back()->with('error', 'Cannot delete the master "All Platforms" system item.');
        }
        $platform->delete();

        \App\Services\PlatformHelper::syncPlatformStatuses();

        return back()->with('success', 'Social Platform deleted successfully.');
    }
}
