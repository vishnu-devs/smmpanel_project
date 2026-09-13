<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">

    <!-- SEO Meta Tags -->
    <title>@yield('title', App\Models\Setting::getSiteName() . ' - Best SMM Panel')</title>
    <meta name="description"
        content="@yield('meta_description', App\Models\Setting::getSiteName() . ' is the best and cheapest SMM panel for Instagram followers, YouTube subscribers, TikTok likes, and other social media growth services. Instant delivery and automated processing.')">
    <meta name="keywords"
        content="smm panel, cheap smm panel, instagram followers, youtube subscribers, tiktok likes, buy followers, growinsta, rishismm">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', App\Models\Setting::getSiteName() . ' - Best SMM Panel')">
    <meta property="og:description"
        content="@yield('meta_description', App\Models\Setting::getSiteName() . ' is the best and cheapest SMM panel for Instagram followers, YouTube subscribers, TikTok likes, and other social media growth services. Instant delivery and automated processing.')">
    <meta property="og:image" content="{{ App\Models\Setting::getLogoUrl() }}">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="@yield('title', App\Models\Setting::getSiteName() . ' - Best SMM Panel')">
    <meta name="twitter:description"
        content="@yield('meta_description', App\Models\Setting::getSiteName() . ' is the best and cheapest SMM panel for Instagram followers, YouTube subscribers, TikTok likes, and other social media growth services. Instant delivery and automated processing.')">
    <meta name="twitter:image" content="{{ App\Models\Setting::getLogoUrl() }}">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ App\Models\Setting::getFaviconUrl() }}" type="image/x-icon">

    <!-- PWA Manifest & Mobile Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#dc2743">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ App\Models\Setting::getSiteName() }}">
    <link rel="apple-touch-icon" href="{{ App\Models\Setting::getFaviconUrl() }}">

    <!-- Local FontAwesome CSS -->
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">

    @php
        $recaptchaEnabled = App\Models\Setting::get('recaptcha_status', 'disabled') === 'enabled';
        $recaptchaSiteKey = App\Models\Setting::get('recaptcha_site_key', '');
    @endphp
    @if($recaptchaEnabled && !empty($recaptchaSiteKey))
        <script src="https://www.google.com/recaptcha/enterprise.js?render={{ $recaptchaSiteKey }}" data-cfasync="false"
            async defer></script>
    @endif

    <style>
        /* Neon / Claymorphic Responsive Header */
        .landing-header {
            position: sticky;
            top: 0;
            z-index: 1000;
            padding: 10px 3%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(220, 39, 67, 0.12);
            box-shadow: 0 4px 20px rgba(220, 39, 67, 0.07);
            transition: all 0.3s ease;
        }

        .landing-nav {
            display: flex;
            gap: 0.35rem;
            align-items: center;
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        .landing-nav-link {
            color: #475569;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 6px 12px;
            border-radius: 20px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .landing-nav-link:hover {
            color: #ff335c;
            background: rgba(220, 39, 67, 0.06);
        }

        .landing-nav-link.active {
            color: #ff335c;
            background: #fff0f3;
            border: 1px solid rgba(220, 39, 67, 0.2);
            box-shadow: 0 2px 8px rgba(220, 39, 67, 0.1);
        }

        .hero-section {
            padding: 5rem 5% 4rem 5%;
            text-align: center;
            position: relative;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
            letter-spacing: -1px;
        }

        .hero-desc {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto 2.5rem auto;
        }

        .landing-footer {
            border-top: 1px solid var(--border-color);
            padding: 3rem 5%;
            margin-top: 5rem;
            text-align: center;
            background-color: var(--bg-card);
        }

        .footer-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .mobile-only-nav-toggle {
            display: none;
        }

        @media(max-width: 991px) {
            .mobile-only-nav-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .landing-header {
                padding: 10px 4%;
            }

            .landing-nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                padding: 1.25rem 1.5rem;
                flex-direction: column;
                gap: 8px;
                align-items: stretch;
                border-bottom: 1px solid rgba(220, 39, 67, 0.15);
                box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
                border-radius: 0 0 24px 24px;
            }

            .landing-nav.active {
                display: flex;
                animation: slideDown 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            }

            .landing-nav-link {
                padding: 12px 18px;
                border-radius: 14px;
                font-size: 0.95rem;
                justify-content: flex-start;
                border: 1px solid transparent;
            }

            .landing-nav-link:hover,
            .landing-nav-link.active {
                background: #fff0f3;
                border-color: rgba(220, 39, 67, 0.15);
            }

            .hero-title {
                font-size: 2.2rem !important;
            }

            .hero-desc {
                font-size: 1rem;
            }

            .hero-section {
                padding: 3rem 5%;
            }

            .hero-section div {
                flex-direction: column !important;
                gap: 10px !important;
            }

            .hero-section div .btn-gradient,
            .hero-section div .btn-outline {
                width: 100% !important;
                padding: 12px 20px !important;
                font-size: 0.95rem !important;
                border-radius: var(--radius-md) !important;
            }

            .landing-footer div {
                flex-direction: column !important;
                gap: 12px !important;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @yield('styles')
</head>

<body>

    <!-- Landing Page Navbar (Vibrant Neon Glass Header) -->
    <header class="landing-header">
        <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
            <a href="{{ Auth::check() ? route('dashboard') : route('home') }}" style="text-decoration: none;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div
                        style="width: 40px; height: 40px; border-radius: 12px; background: #0c091a; display: flex; align-items: center; justify-content: center; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.15); flex-shrink: 0;">
                        <img src="{{ App\Models\Setting::getLogoUrl() }}"
                            onerror="this.onerror=null; this.src='{{ asset('images/logo_smm.png') }}';" alt="Logo"
                            style="width: 100%; height: 100%; object-fit: contain;">
                    </div>
                    <div
                        style="font-size: 1.45rem; font-weight: 900; color: #0f172a; letter-spacing: -0.5px; font-family: 'Outfit', sans-serif;">
                        {{ App\Models\Setting::getSiteName() }}
                    </div>
                </div>
            </a>

            <button id="navToggleBtn" class="mobile-only-nav-toggle" aria-label="Toggle Navigation Menu"
                style="width: 44px; height: 44px; border-radius: 14px; background: #ffffff; box-shadow: 0 6px 18px rgba(220, 39, 67, 0.12); border: 1px solid #f1f5f9; cursor: pointer; color: #0f172a; padding: 0;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
        </div>

        <nav class="landing-nav" id="landingNavMenu">
            @auth
                <a href="{{ route('dashboard') }}" class="landing-nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            @else
                <a href="{{ route('home') }}" class="landing-nav-link {{ Route::is('home') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i> Home
                </a>
            @endauth

            <a href="{{ route('services') }}" class="landing-nav-link {{ Route::is('services') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check"></i> Services
            </a>
            <a href="{{ route('api.docs') }}" class="landing-nav-link {{ Route::is('api.docs') ? 'active' : '' }}">
                <i class="fa-solid fa-code"></i> API
            </a>
            <a href="{{ route('resellers') }}" class="landing-nav-link {{ Route::is('resellers') ? 'active' : '' }}">
                <i class="fa-solid fa-handshake"></i> Resellers
            </a>
            <a href="{{ route('blog.index') }}" class="landing-nav-link {{ Route::is('blog*') ? 'active' : '' }}">
                <i class="fa-solid fa-newspaper"></i> Blog
            </a>
            <a href="{{ route('rules') }}" class="landing-nav-link {{ Route::is('rules') ? 'active' : '' }}">
                <i class="fa-solid fa-gavel"></i> Rules
            </a>
            <a href="{{ route('privacy.policy') }}"
                class="landing-nav-link {{ Route::is('privacy*') ? 'active' : '' }}">
                <i class="fa-solid fa-shield-halved"></i> Privacy
            </a>

            @php
                $waChannelUrl = App\Models\Setting::get('whatsapp_channel_url', 'https://whatsapp.com/channel');
            @endphp
            <a href="{{ $waChannelUrl }}" target="_blank" rel="noopener noreferrer" class="landing-nav-link"
                style="color: #16a34a; background: #edfbf4; border: 1px solid rgba(34, 197, 94, 0.25);">
                <i class="fa-brands fa-whatsapp" style="font-size: 1.05rem;"></i> Channel
            </a>

            <a href="javascript:void(0)" onclick="triggerPWAInstall()"
                class="landing-nav-link landing-install-app app-install-only-web"
                style="color: #0891b2; background: #ecfeff; border: 1px solid rgba(6, 182, 212, 0.25);">
                <i class="fa-brands fa-android" style="font-size: 1.05rem;"></i> App
            </a>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-gradient"
                    style="padding: 9px 20px; font-size: 0.88rem; border-radius: 20px; text-decoration: none; margin-left: 4px;">
                    Dashboard
                </a>
            @endauth
        </nav>
    </header>

    <!-- Main Section -->
    <main>
        <!-- Announcement Ticker -->
        @if($tickerText = \App\Models\Setting::get('ticker_text'))
            <div class="ticker-wrap" style="margin: 0 5% 1.25rem 5%; width: auto;">
                <div class="ticker-title"><i class="fa-solid fa-bullhorn animate-pulse"></i> Notice</div>
                <div class="ticker">
                    <div class="ticker__item">{{ $tickerText }}</div>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Landing Page Footer -->
    <footer class="landing-footer">
        <div class="footer-logo">
            <div class="logo-icon" style="background: transparent; box-shadow: none; border-radius: 0;"><img
                    src="{{ App\Models\Setting::getLogoUrl() }}" alt="{{ App\Models\Setting::getSiteName() }}"
                    style="width: 100%; height: 100%; object-fit: contain;"></div>
            <div class="logo-text" style="font-size: 1.4rem;">{{ App\Models\Setting::getSiteName() }}</div>
        </div>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
            Automated Social Media SMM Panel. Real followers, likes, comments, and views.
        </p>
        <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 2rem; flex-wrap: wrap;">
            <a href="{{ route('home') }}" class="landing-nav-link">Home</a>
            <a href="{{ route('services') }}" class="landing-nav-link">Services</a>
            <a href="{{ route('api.docs') }}" class="landing-nav-link">API</a>
            <a href="{{ route('resellers') }}" class="landing-nav-link">Resellers</a>
            <a href="{{ $waChannelUrl }}" target="_blank" rel="noopener noreferrer" class="landing-nav-link"
                style="color: #22c55e; font-weight: 700;">
                <i class="fa-brands fa-whatsapp"></i> WhatsApp Channel
            </a>
            <a href="{{ route('blog.index') }}" class="landing-nav-link">Blog</a>
            <a href="{{ route('faq') }}" class="landing-nav-link">FAQ</a>
            <a href="{{ route('rules') }}" class="landing-nav-link">Rules</a>
            <a href="{{ route('privacy.policy') }}" class="landing-nav-link">Privacy Policy</a>
        </div>
        <p style="color: var(--text-muted); font-size: 0.8rem;">
            &copy; {{ date('Y') }} All rights reserved.
        </p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.getElementById('navToggleBtn');
            const menu = document.getElementById('landingNavMenu');

            if (toggle && menu) {
                toggle.addEventListener('click', function () {
                    menu.classList.toggle('active');
                    if (menu.classList.contains('active')) {
                        toggle.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        `;
                    } else {
                        toggle.innerHTML = `
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="3" y1="12" x2="21" y2="12"></line>
                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                <line x1="3" y1="18" x2="21" y2="18"></line>
                            </svg>
                        `;
                    }
                });
            }
        });
    </script>
    @include('partials.welcome_popup')
    @include('partials.pwa_manager')
    @stack('modals')
    @yield('scripts')
</body>

</html>