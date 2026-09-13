<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Service;

class LandingController extends Controller
{
    public function index()
    {
        // If user is already logged in, redirect directly to dashboard (New Order)
        if (\Illuminate\Support\Facades\Auth::check()) {
            return redirect()->route('dashboard');
        }

        // Public homepage view for guests
        return view('home');
    }

    public function services()
    {
        $categories = \Illuminate\Support\Facades\Cache::remember('rishismm_landing_services_catalog', 300, function () {
            $enabledPlatforms = \App\Services\PlatformHelper::getEnabledPlatforms();

            $cats = Category::where('status', 'active')
                ->whereHas('services', function ($q) {
                    $q->where('status', 'active');
                })
                ->with(['services' => function ($query) {
                    $query->where('status', 'active')
                        ->select('id', 'category_id', 'name', 'price_per_k', 'min_quantity', 'max_quantity', 'description', 'average_time', 'sort_order', 'status')
                        ->orderBy('sort_order', 'asc')
                        ->orderBy('id', 'desc');
                }])
                ->get()
                ->filter(function ($cat) use ($enabledPlatforms) {
                    if (!$cat->services) return false;

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
                ->values();

            if (class_exists(\App\Services\BrandingSanitizer::class)) {
                foreach ($cats as $cat) {
                    $cat->name = \App\Services\BrandingSanitizer::clean($cat->name);
                    if ($cat->services) {
                        foreach ($cat->services as $srv) {
                            $srv->name = \App\Services\BrandingSanitizer::clean($srv->name);
                            $srv->description = \App\Services\BrandingSanitizer::clean($srv->description);
                        }
                    }
                }
            }

            return $cats;
        });

        return view('services', compact('categories'));
    }

    public function apiDocs()
    {
        return view('api_docs');
    }

    public function faq()
    {
        return view('faq');
    }

    public function rules()
    {
        return view('rules');
    }

    public function privacyPolicy()
    {
        return view('privacy');
    }

    public function resellers()
    {
        return view('resellers');
    }

    public function downloadApp()
    {
        $apkPath = public_path('downloads/RishiSMM.apk');
        if (file_exists($apkPath)) {
            return response()->download($apkPath, 'RishiSMM.apk', [
                'Content-Type' => 'application/vnd.android.package-archive',
            ]);
        }
        return redirect()->route('home');
    }

    public function manifest()
    {
        $path = public_path('manifest.json');
        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/manifest+json; charset=utf-8',
                'Access-Control-Allow-Origin' => '*',
            ]);
        }
        
        $siteName = \App\Models\Setting::get('site_name', 'RishiSMM');
        $manifest = [
            'name' => "{$siteName} - Automated SMM Panel",
            'short_name' => $siteName,
            'description' => "India's #1 Fastest & Cheapest SMM Panel",
            'start_url' => '/',
            'id' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#0f172a',
            'theme_color' => '#dc2743',
            'icons' => [
                [
                    'src' => '/images/icons/icon-192x192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => '/images/icons/icon-512x512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
            ]
        ];
        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function serviceWorker()
    {
        $path = public_path('sw.js');
        if (file_exists($path)) {
            return response()->file($path, [
                'Content-Type' => 'application/javascript; charset=utf-8',
                'Service-Worker-Allowed' => '/',
            ]);
        }
        return response("// RishiSMM Service Worker Fallback\nself.addEventListener('fetch', function(e){});", 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function iconFile($file)
    {
        $path = public_path("images/icons/{$file}");
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'image/png']);
        }
        $fallback = public_path('images/logo_smm.png');
        if (file_exists($fallback)) {
            return response()->file($fallback, ['Content-Type' => 'image/png']);
        }
        abort(404);
    }

    public function unsubscribeBalanceReminder(\App\Models\User $user, Request $request)
    {
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired unsubscribe authorization token.');
        }

        $user->balance_reminder_unsubscribed = true;
        $user->save();

        return view('auth.unsubscribed', ['userEmail' => $user->email]);
    }

    public function referrals()
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        // Ensure user has referral code
        if (empty($user->referral_code)) {
            $user->referral_code = \App\Services\ReferralService::generateUniqueReferralCode();
            $user->save();
        }

        $referralUrl = route('register', ['ref' => $user->referral_code]);

        $totalReferrals = \App\Models\Referral::where('referrer_id', $user->id)->count();
        $activeReferrals = \App\Models\Referral::where('referrer_id', $user->id)->where('status', 'active')->count();

        $totalEarnings = \App\Models\ReferralCommission::where('referrer_id', $user->id)->where('status', 'approved')->sum('commission_amount');
        $pendingEarnings = \App\Models\ReferralCommission::where('referrer_id', $user->id)->where('status', 'pending_review')->sum('commission_amount');

        $commissions = \App\Models\ReferralCommission::with('referred')
            ->where('referrer_id', $user->id)
            ->latest()
            ->paginate(15);

        $commissionPercent = (float)\App\Models\Setting::get('referral_commission_percent', 2.0);
        $minDeposit = (float)\App\Models\Setting::get('referral_min_deposit', 100);

        return view('user.referrals', compact('user', 'referralUrl', 'totalReferrals', 'activeReferrals', 'totalEarnings', 'pendingEarnings', 'commissions', 'commissionPercent', 'minDeposit'));
    }
}
