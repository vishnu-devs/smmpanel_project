<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon & PWA Icons -->
    <link rel="shortcut icon" href="{{ App\Models\Setting::getFaviconUrl() }}" type="image/x-icon">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#dc2743">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ App\Models\Setting::getSiteName() }}">
    <link rel="apple-touch-icon" href="{{ App\Models\Setting::getFaviconUrl() }}">

    <title>@yield('title', 'Dashboard') - {{ App\Models\Setting::getSiteName() }}</title>

    <!-- Local FontAwesome CSS with Asynchronous & Non-Blocking Loading -->
    <link rel="preload" href="{{ asset('css/all.min.css') }}" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="{{ asset('css/all.min.css') }}"></noscript>
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

    <style>
        @font-face { font-display: swap !important; }
        /* Custom styles injected from setting */
        {!! App\Models\Setting::get('custom_css', '') !!}
    </style>
    @yield('styles')
</head>

<body>

    <div class="dashboard-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="logo-container" style="justify-content: space-between; width: 100%;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="logo-icon" style="background: transparent; box-shadow: none; border-radius: 0;"><img
                            src="{{ App\Models\Setting::getLogoUrl() }}" alt="{{ App\Models\Setting::getSiteName() }}"
                            style="width: 100%; height: 100%; object-fit: contain;"></div>
                    <div class="logo-text">{{ App\Models\Setting::getSiteName() }}</div>
                </div>
                <button id="sidebarCloseBtn" class="btn-outline mobile-only-btn" aria-label="Close sidebar"
                    style="padding: 5px 8px; border: none; font-size: 1.1rem;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <nav class="nav-menu">
                @if(Auth::check() && Auth::user()->isAdmin())
                    <!-- Admin Sidebar Links -->
                    <a href="{{ route('admin.dashboard') }}"
                        class="nav-link {{ Route::is('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-chart-line"></i> Admin Dash
                    </a>
                    <a href="{{ route('admin.users') }}" class="nav-link {{ Route::is('admin.users') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Users
                    </a>
                    <a href="{{ route('admin.referrals') }}"
                        class="nav-link {{ Route::is('admin.referrals*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users-rays"></i> Referrals
                    </a>
                    <a href="{{ route('admin.tiers') }}" class="nav-link {{ Route::is('admin.tiers*') ? 'active' : '' }}">
                        <i class="fa-solid fa-layer-group"></i> Customer Tiers
                    </a>
                    <a href="{{ route('admin.discounts') }}"
                        class="nav-link {{ Route::is('admin.discounts*') ? 'active' : '' }}">
                        <i class="fa-solid fa-user-tag"></i> Customer Discounts
                    </a>
                    <a href="{{ route('admin.categories') }}"
                        class="nav-link {{ Route::is('admin.categories') ? 'active' : '' }}">
                        <i class="fa-solid fa-folder-open"></i> Categories
                    </a>
                    <a href="{{ route('admin.services') }}"
                        class="nav-link {{ Route::is('admin.services') ? 'active' : '' }}">
                        <i class="fa-solid fa-list-check"></i> Services
                    </a>
                    <a href="{{ route('admin.providers') }}"
                        class="nav-link {{ Route::is('admin.providers') ? 'active' : '' }}">
                        <i class="fa-solid fa-plug"></i> Provider APIs
                    </a>
                    <a href="{{ route('admin.orders') }}" class="nav-link {{ Route::is('admin.orders') ? 'active' : '' }}">
                        <i class="fa-solid fa-cart-shopping"></i> Orders
                    </a>
                    <a href="{{ route('admin.transactions') }}"
                        class="nav-link {{ Route::is('admin.transactions*') ? 'active' : '' }}">
                        <i class="fa-solid fa-money-bill-transfer"></i> Payments
                        @php $pendingP = \Illuminate\Support\Facades\Cache::remember('admin_pending_trans_cnt', 30, function () {
                            return App\Models\Transaction::where('status', 'pending')->count();
                        }); @endphp
                        @if($pendingP > 0)
                            <span
                                style="background: #f59e0b; color: #000; font-size: 0.75rem; font-weight: 800; padding: 2px 7px; border-radius: 10px; margin-left: auto;">{{ $pendingP }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.tickets') }}"
                        class="nav-link {{ Route::is('admin.tickets*') ? 'active' : '' }}">
                        <i class="fa-solid fa-ticket"></i> Tickets
                        @php $openT = \Illuminate\Support\Facades\Cache::remember('admin_open_tickets_cnt', 30, function () {
                            return App\Models\Ticket::whereIn('status', ['open', 'client_reply'])->count();
                        }); @endphp
                        @if($openT > 0)
                            <span
                                style="background: white; color: var(--color-primary); font-size: 0.75rem; font-weight: bold; padding: 2px 6px; border-radius: 10px; margin-left: auto;">{{ $openT }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.feedbacks') }}"
                        class="nav-link {{ Route::is('admin.feedbacks*') ? 'active' : '' }}">
                        <i class="fa-solid fa-comment-dots"></i> Feedbacks
                        @php $pendingFb = \Illuminate\Support\Facades\Cache::remember('admin_pending_fb_cnt', 30, function () {
                            return App\Models\Feedback::where('status', 'pending')->count();
                        }); @endphp
                        @if($pendingFb > 0)
                            <span
                                style="background: #ef4444; color: white; font-size: 0.75rem; font-weight: bold; padding: 2px 6px; border-radius: 10px; margin-left: auto;">{{ $pendingFb }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.settings') }}"
                        class="nav-link {{ Route::is('admin.settings') ? 'active' : '' }}">
                        <i class="fa-solid fa-gears"></i> Settings
                    </a>
                    <a href="{{ route('admin.platforms') }}"
                        class="nav-link {{ Route::is('admin.platforms*') ? 'active' : '' }}">
                        <i class="fa-solid fa-share-nodes"></i> Social Platforms
                    </a>
                    <a href="{{ route('admin.health') }}" class="nav-link {{ Route::is('admin.health') ? 'active' : '' }}">
                        <i class="fa-solid fa-heart-pulse"></i> Diagnostics
                    </a>
                    <a href="{{ route('admin.blogs') }}" class="nav-link {{ Route::is('admin.blogs*') ? 'active' : '' }}">
                        <i class="fa-solid fa-newspaper"></i> Blogs
                    </a>
                    <div style="border-top: 1px solid var(--border-color); margin: 15px 0;"></div>
                @endif

                <!-- User Sidebar Links -->
                <a href="{{ route('dashboard') }}" class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-cart-plus"></i> New Order
                </a>
                <a href="javascript:void(0)" onclick="openScratchCardModal()" class="nav-link" style="color: #ff335c;">
                    <i class="fa-solid fa-gift" style="color: #ff335c;"></i> Daily Scratch Card
                </a>
                <a href="{{ route('orders.history') }}"
                    class="nav-link {{ Route::is('orders.history') ? 'active' : '' }}">
                    <i class="fa-solid fa-history"></i> My Orders
                </a>
                <a href="{{ route('services') }}" class="nav-link {{ Route::is('services') ? 'active' : '' }}">
                    <i class="fa-solid fa-list"></i> Services List
                </a>
                <a href="{{ route('add_funds') }}" class="nav-link {{ Route::is('add_funds') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i> Add Funds
                </a>
                <a href="{{ route('payment.history') }}"
                    class="nav-link {{ Route::is('payment.history') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i> Payment History
                </a>
                <a href="{{ route('referrals') }}" class="nav-link {{ Route::is('referrals') ? 'active' : '' }}">
                    <i class="fa-solid fa-gift"></i> Refer & Earn
                </a>
                <a href="{{ route('tickets.index') }}"
                    class="nav-link {{ Route::is('tickets.index') || Route::is('tickets.show') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope-open-text"></i> Support
                </a>
                <a href="{{ route('feedback.index') }}"
                    class="nav-link {{ Route::is('feedback.index') ? 'active' : '' }}">
                    <i class="fa-solid fa-comment-dots"></i> Feedback & Ideas
                </a>
                <a href="{{ route('resellers') }}" class="nav-link {{ Route::is('resellers') ? 'active' : '' }}">
                    <i class="fa-solid fa-handshake"></i> Reseller Program
                </a>
                <a href="{{ route('api.docs') }}" class="nav-link {{ Route::is('api.docs') ? 'active' : '' }}">
                    <i class="fa-solid fa-code"></i> Developer API
                </a>
                <a href="{{ route('rules') }}" class="nav-link {{ Route::is('rules') ? 'active' : '' }}">
                    <i class="fa-solid fa-gavel"></i> Terms & Rules
                </a>
                <a href="{{ route('blog.index') }}" class="nav-link {{ Route::is('blog*') ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper"></i> Blog Articles
                </a>
                <a href="{{ route('privacy.policy') }}" class="nav-link {{ Route::is('privacy*') ? 'active' : '' }}">
                    <i class="fa-solid fa-shield-halved"></i> Privacy Policy
                </a>
                <a href="{{ route('profile') }}" class="nav-link {{ Route::is('profile') ? 'active' : '' }}">
                    <i class="fa-solid fa-user-gear"></i> My Profile
                </a>
                @php
                    $waChannelUrl = App\Models\Setting::get('whatsapp_channel_url', 'https://whatsapp.com/channel');
                @endphp
                <a href="{{ $waChannelUrl }}" target="_blank" rel="noopener noreferrer" class="nav-link"
                    style="color: #22c55e; border-left-color: #22c55e;">
                    <i class="fa-brands fa-whatsapp" style="color: #22c55e;"></i> WhatsApp Channel
                    <span
                        style="background: rgba(34, 197, 94, 0.15); border: 1px solid rgba(34, 197, 94, 0.35); color: #22c55e; font-size: 0.68rem; font-weight: 700; padding: 2px 6px; border-radius: 6px; margin-left: auto;">UPDATES</span>
                </a>
                <a href="javascript:void(0)" onclick="triggerPWAInstall()"
                    class="nav-link sidebar-install-app app-install-only-web" style="color: #10b981;">
                    <i class="fa-brands fa-android"></i> Install App
                </a>
                <a href="{{ route('logout') }}" class="nav-link" style="margin-top: auto;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </nav>
        </aside>
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Main Content Area -->
        <main class="main-content">
            @php
                $waChannelUrl = App\Models\Setting::get('whatsapp_channel_url', 'https://whatsapp.com/channel');
            @endphp

            <!-- Top Header (Responsive Desktop & Mobile Claymorphism) -->
            <header class="top-bar animate-fade-in" style="margin-bottom: 1.5rem;">

                <!-- DESKTOP HEADER (>991px) Matching Exact Screenshot -->
                <div class="top-bar-desktop"
                    style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 30px rgba(220, 39, 67, 0.07); padding: 12px 24px; display: flex; align-items: center; justify-content: space-between; width: 100%; border: 1px solid rgba(220, 39, 67, 0.05); margin-bottom: 1.5rem;">
                    <!-- Left: Welcome User Pill -->
                    <a href="{{ route('profile') }}"
                        style="background: #ffffff; border-radius: 30px; padding: 8px 18px; box-shadow: 0 6px 18px rgba(220, 39, 67, 0.08); border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; text-decoration: none; transition: var(--transition);">
                        <div
                            style="width: 28px; height: 28px; border-radius: 50%; background: #fff0f3; color: #ff335c; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                            <i class="fa-solid fa-user"></i>
                        </div>
                        <span style="font-size: 0.88rem; color: #475569; font-weight: 500;">
                            Welcome, <strong
                                style="color: #ff335c; font-weight: 800;">{{ Auth::check() ? Auth::user()->name : 'Guest' }}</strong>
                        </span>
                        <i class="fa-solid fa-chevron-down"
                            style="font-size: 0.75rem; color: #94a3b8; margin-left: 2px;"></i>
                    </a>

                    <!-- Right: Desktop Pill Widgets Row -->
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <!-- Balance Pill -->
                        <a href="{{ route('add_funds') }}"
                            style="background: #ffffff; border-radius: 30px; padding: 8px 18px; box-shadow: 0 6px 18px rgba(220, 39, 67, 0.08); border: 1px solid #f1f5f9; display: flex; align-items: center; gap: 10px; text-decoration: none; transition: var(--transition);">
                            <i class="fa-solid fa-wallet" style="color: #ff335c; font-size: 1rem;"></i>
                            <span class="user-balance-display"
                                style="font-size: 0.95rem; font-weight: 800; color: #0f172a;">
                                ₹{{ number_format(Auth::check() ? Auth::user()->balance : 0, 2) }}
                            </span>
                            <i class="fa-solid fa-chevron-down"
                                style="font-size: 0.75rem; color: #94a3b8; margin-left: 2px;"></i>
                        </a>

                        <!-- Daily Reward Pill -->
                        <button type="button" onclick="openScratchCardModal()"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; font-size: 0.88rem; font-weight: 700; color: #ff335c; background: #fff0f3; border: 1px solid rgba(220, 39, 67, 0.25); border-radius: 30px; box-shadow: 0 6px 14px rgba(220, 39, 67, 0.1); cursor: pointer; transition: var(--transition);">
                            <i class="fa-solid fa-gift" style="font-size: 1rem; color: #ff335c;"></i>
                            <span>Daily Reward</span>
                        </button>

                        <!-- Join Channel Pill -->
                        <a href="{{ $waChannelUrl }}" target="_blank" rel="noopener noreferrer"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 99px 18px; font-size: 0.88rem; font-weight: 700; color: #16a34a; background: #edfbf4; border: 1px solid rgba(34, 197, 94, 0.25); border-radius: 30px; text-decoration: none; box-shadow: 0 6px 14px rgba(34, 197, 94, 0.1); transition: var(--transition); padding-top: 9px; padding-bottom: 9px;">
                            <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem; color: #16a34a;"></i>
                            <span>Join Channel</span>
                        </a>

                        <!-- User Tier Pill -->
                        @php
                            $userTier = (Auth::check() && Auth::user()->tier_name) ? Auth::user()->tier_name : 'BRONZE';
                        @endphp
                        <span
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; font-size: 0.88rem; font-weight: 700; color: #ff335c; background: #fff0f3; border: 1px solid rgba(220, 39, 67, 0.25); border-radius: 30px; box-shadow: 0 6px 14px rgba(220, 39, 67, 0.1); text-transform: uppercase;">
                            <i class="fa-solid fa-trophy" style="font-size: 1rem; color: #ff335c;"></i>
                            <span>{{ $userTier }} TIER</span>
                        </span>
                    </div>
                </div>

                <!-- MOBILE HEADER (<=991px) Preserved Intact -->
                <div class="top-bar-mobile">
                    <!-- Header Card Container -->
                    <div class="top-header-clay-card"
                        style="background: #ffffff; border-radius: 24px; box-shadow: 0 10px 25px rgba(220, 39, 67, 0.07); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; width: 100%; border: 1px solid rgba(220, 39, 67, 0.05); margin-bottom: 1rem;">
                        <!-- Left: Brand Logo & Title -->
                        <a href="{{ route('dashboard') }}"
                            style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                            <div
                                style="width: 40px; height: 40px; border-radius: 12px; background: #0c091a; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15); flex-shrink: 0;">
                                <img src="{{ App\Models\Setting::getLogoUrl() }}"
                                    onerror="this.onerror=null; this.src='{{ asset('images/logo_smm.png') }}';"
                                    alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
                            </div>
                            <div
                                style="font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; font-family: 'Outfit', sans-serif;">
                                {{ App\Models\Setting::getSiteName() }}
                            </div>
                        </a>

                        <!-- Right: Menu Toggle Button -->
                        <button id="sidebarToggleBtn" aria-label="Open sidebar"
                            style="width: 44px; height: 44px; border-radius: 14px; background: #ffffff; box-shadow: 0 6px 18px rgba(220, 39, 67, 0.12); border: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #0f172a; padding: 0;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                stroke-linejoin="round">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        </button>
                    </div>

                    <!-- Sub-Header Pills Row (WhatsApp Channel, Daily Reward & User Role Badge) -->
                    <div
                        style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 0.75rem;">
                        <!-- WhatsApp Channel Quick Link -->
                        <a href="{{ $waChannelUrl }}" target="_blank" rel="noopener noreferrer"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; font-size: 0.88rem; font-weight: 700; color: #16a34a; background: #edfbf4; border: 1px solid rgba(34, 197, 94, 0.25); border-radius: 30px; text-decoration: none; box-shadow: 0 6px 14px rgba(34, 197, 94, 0.1); transition: var(--transition);">
                            <i class="fa-brands fa-whatsapp" style="font-size: 1.1rem; color: #16a34a;"></i>
                            <span>Join Channel</span>
                        </a>

                        <!-- Daily Reward Pill for Mobile -->
                        <button type="button" onclick="openScratchCardModal()"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; font-size: 0.88rem; font-weight: 700; color: #ff335c; background: #fff0f3; border: 1px solid rgba(220, 39, 67, 0.25); border-radius: 30px; box-shadow: 0 6px 14px rgba(220, 39, 67, 0.1); cursor: pointer; transition: var(--transition);">
                            <i class="fa-solid fa-gift" style="font-size: 1rem; color: #ff335c;"></i>
                            <span>Daily Reward</span>
                        </button>

                        <!-- User Tier Pill -->
                        @php
                            $userTier = (Auth::check() && Auth::user()->tier_name) ? Auth::user()->tier_name : 'BRONZE';
                        @endphp
                        <span
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; font-size: 0.88rem; font-weight: 700; color: #ff335c; background: #fff0f3; border: 1px solid rgba(220, 39, 67, 0.25); border-radius: 30px; box-shadow: 0 6px 14px rgba(220, 39, 67, 0.1); text-transform: uppercase;">
                            <i class="fa-solid fa-trophy" style="font-size: 1rem; color: #ff335c;"></i>
                            <span>{{ $userTier }} TIER</span>
                        </span>


                    </div>

                    <!-- My Balance Soft Clay Pill Card for Mobile -->
                    <a href="{{ route('add_funds') }}"
                        style="background: #ffffff; border-radius: 20px; box-shadow: 0 10px 25px rgba(220, 39, 67, 0.07); padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; width: 100%; border: 1px solid rgba(220, 39, 67, 0.05); margin-bottom: 0.5rem; text-decoration: none;">
                        <div
                            style="font-size: 0.85rem; font-weight: 700; color: #64748b; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-wallet" style="color: #ff335c; font-size: 1rem;"></i>
                            <span>MY BALANCE</span>
                        </div>
                        <div class="user-balance-display" style="font-size: 1.25rem; font-weight: 800; color: #ff335c;">
                            ₹{{ number_format(Auth::check() ? Auth::user()->balance : 0, 2) }}
                        </div>
                    </a>
                </div>
            </header>
            @php
                $waChannelUrl = App\Models\Setting::get('whatsapp_channel_url', 'https://whatsapp.com/channel');
            @endphp


            <!-- Alerts Notification Box -->
            @if(session('success'))
                <div class="alert alert-success animate-fade-in">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-error animate-fade-in">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-error animate-fade-in">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Announcement Ticker -->
            @if($tickerText = \App\Models\Setting::get('ticker_text'))
                <div class="ticker-wrap">
                    <div class="ticker-title"><i class="fa-solid fa-bullhorn animate-pulse"></i> Notice</div>
                    <div class="ticker">
                        <div class="ticker__item">{{ $tickerText }}</div>
                    </div>
                </div>
            @endif

            <!-- Dynamic View Content -->
            <div class="animate-fade-in" style="animation-delay: 0.1s;">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const sidebar = document.querySelector('.sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('active');
                if (overlay) overlay.classList.add('active');
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('active');
                if (overlay) overlay.classList.remove('active');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    openSidebar();
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    closeSidebar();
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function () {
                    closeSidebar();
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function (e) {
                if (window.innerWidth <= 991 && sidebar && sidebar.classList.contains('active')) {
                    if (!sidebar.contains(e.target) && toggleBtn && !toggleBtn.contains(e.target)) {
                        closeSidebar();
                    }
                }
            });
        });
    </script>

    <!-- Floating WhatsApp Support Widget -->
    @php
        $waNum = App\Models\Setting::get('whatsapp_number', '');
        $waNumClean = preg_replace('/[^0-9]/', '', $waNum);
        $siteName = App\Models\Setting::get('site_name', 'Support');
        $waMsg = urlencode("Hello {$siteName} Team, I need help with my account/order.");
        $waLink = $waNumClean ? "https://wa.me/{$waNumClean}?text={$waMsg}" : null;
    @endphp
    @if($waLink)
    <a href="{{ $waLink }}" target="_blank" rel="noopener noreferrer" class="whatsapp-float-btn"
        title="Chat with us on WhatsApp" aria-label="Chat on WhatsApp">
        <i class="fa-brands fa-whatsapp"></i>
    </a>
    @endif

    @include('partials.scratch_card_modal')
    @include('partials.welcome_popup')
    @include('partials.pwa_manager')
    @stack('modals')
    @yield('scripts')
</body>

</html>