@extends('layouts.landing')

@section('title', App\Models\Setting::get('site_name', 'RishiSMM') . ' - #1 Best & Cheapest SMM Panel in India')

@section('styles')
<style>
    /* Hero Section Styles */
    .hero-wrapper {
        padding: 3.5rem 5% 4rem 5%;
        position: relative;
    }
    .hero-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 3rem;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
    }
    .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(220, 39, 67, 0.1);
        border: 1px solid rgba(220, 39, 67, 0.3);
        border-radius: 30px;
        color: var(--color-primary);
        font-size: 0.88rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
    }
    .hero-title-main {
        font-size: 3.2rem;
        font-weight: 800;
        line-height: 1.18;
        letter-spacing: -1px;
        margin-bottom: 1.25rem;
    }
    .hero-desc-text {
        font-size: 1.1rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 2rem;
    }

    /* Embedded Quick Login Card in Hero */
    .quick-login-card {
        border-radius: var(--radius-lg);
        padding: 2.25rem;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25);
        position: relative;
    }
    .quick-login-title {
        font-size: 1.35rem;
        font-weight: 700;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Live Stats Ribbon */
    .stats-ribbon {
        max-width: 1200px;
        margin: 0 auto 5rem auto;
        padding: 0 5%;
    }
    .stats-ribbon-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
    }
    .stat-box {
        padding: 1.75rem 1.25rem;
        border-radius: var(--radius-md);
        text-align: center;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        transition: transform 0.3s ease;
    }
    .stat-box:hover {
        transform: translateY(-4px);
    }
    .stat-number {
        font-size: 2.2rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 6px;
        background: var(--grad-insta);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .stat-label {
        font-size: 0.88rem;
        color: var(--text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Section Headings */
    .section-header-block {
        text-align: center;
        max-width: 750px;
        margin: 0 auto 3.5rem auto;
    }
    .section-tag {
        display: inline-block;
        font-size: 0.82rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--color-primary);
        letter-spacing: 1.5px;
        margin-bottom: 8px;
    }
    .section-title {
        font-size: 2.3rem;
        font-weight: 800;
        line-height: 1.25;
        margin-bottom: 12px;
    }
    .section-subtitle {
        font-size: 1rem;
        color: var(--text-secondary);
        line-height: 1.6;
    }

    /* Platform Showcase Cards */
    .platform-cards-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    .platform-showcase-card {
        border-radius: var(--radius-md);
        padding: 1.75rem 1.5rem;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        text-align: left;
    }
    .platform-showcase-card:hover {
        transform: translateY(-5px);
        border-color: rgba(220, 39, 67, 0.4);
        box-shadow: 0 10px 25px rgba(220, 39, 67, 0.1);
    }
    .platform-icon-circle {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        margin-bottom: 1.25rem;
    }
    .platform-card-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-primary);
    }
    .platform-card-desc {
        font-size: 0.88rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 1.25rem;
        flex-grow: 1;
    }
    .platform-card-link {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--color-primary);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: gap 0.2s ease;
    }
    .platform-card-link:hover {
        gap: 10px;
    }

    /* 4-Step Process Timeline */
    .step-process-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
    }
    .step-card {
        padding: 2rem 1.5rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        text-align: center;
        position: relative;
    }
    .step-badge {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin: 0 auto 1.25rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 800;
        color: white;
        background: var(--grad-insta);
        box-shadow: 0 4px 15px rgba(220, 39, 67, 0.35);
    }
    .step-card h4 {
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-primary);
    }
    .step-card p {
        font-size: 0.88rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    /* Features Grid (Why Choose Us) */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.75rem;
        max-width: 1200px;
        margin: 0 auto;
    }
    .feature-card {
        padding: 2rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        transition: all 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 255, 255, 0.15);
    }
    .feature-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 1.25rem;
    }
    .feature-card h3 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .feature-card p {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    /* Interactive Accordion FAQ */
    .faq-wrapper {
        max-width: 850px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .faq-item {
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .faq-question {
        padding: 1.25rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text-primary);
        user-select: none;
    }
    .faq-question i {
        font-size: 0.9rem;
        color: var(--color-primary);
        transition: transform 0.3s ease;
    }
    .faq-item.active .faq-question i {
        transform: rotate(180deg);
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        padding: 0 1.5rem;
        color: var(--text-secondary);
        font-size: 0.92rem;
        line-height: 1.7;
    }
    .faq-item.active .faq-answer {
        padding-bottom: 1.25rem;
    }

    /* Bottom CTA Card */
    .cta-banner {
        max-width: 1200px;
        margin: 5rem auto 2rem auto;
        padding: 0 5%;
    }
    .cta-inner {
        padding: 3.5rem 2.5rem;
        border-radius: var(--radius-lg);
        background: radial-gradient(circle at 10% 20%, rgba(220, 39, 67, 0.25) 0%, transparent 60%),
                    radial-gradient(circle at 90% 80%, rgba(124, 58, 237, 0.25) 0%, transparent 60%),
                    var(--bg-card);
        border: 1px solid rgba(220, 39, 67, 0.3);
        text-align: center;
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }

    /* Responsive Queries */
    @media (max-width: 1024px) {
        .platform-cards-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .step-process-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .features-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .hero-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
        .hero-title-main {
            font-size: 2.4rem;
        }
        .stats-ribbon-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .platform-cards-grid {
            grid-template-columns: 1fr;
        }
        .step-process-grid {
            grid-template-columns: 1fr;
        }
        .features-grid {
            grid-template-columns: 1fr;
        }
        .cta-inner {
            padding: 2.5rem 1.5rem;
        }
    }

    /* Mobile App Showcase Section Styles */
    .app-showcase-section {
        padding: 2.5rem 5% 4rem 5%;
        position: relative;
    }
    .app-showcase-container {
        max-width: 1200px;
        margin: 0 auto;
        border-radius: var(--radius-lg);
        padding: 3.5rem 3rem;
        border: 1px solid rgba(220, 39, 67, 0.35);
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.98), rgba(30, 27, 75, 0.92)) !important;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(220, 39, 67, 0.15);
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 3rem;
        align-items: center;
        box-sizing: border-box;
        width: 100%;
    }
    .app-title-main {
        font-size: 2.5rem;
        font-weight: 800;
        line-height: 1.2;
        margin: 10px 0 16px 0;
        color: #ffffff !important;
    }
    .app-desc-main {
        font-size: 1.02rem;
        color: #e2e8f0 !important;
        line-height: 1.6;
        margin-bottom: 2rem;
    }
    .app-features-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.25rem;
        margin-bottom: 2.25rem;
    }
    .app-feature-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        text-align: left;
    }
    .app-buttons-wrap {
        display: flex;
        gap: 14px;
        flex-wrap: wrap;
    }
    .app-preview-wrapper {
        display: flex;
        justify-content: center;
        position: relative;
        width: 100%;
    }
    .app-preview-card {
        width: 100%;
        max-width: 320px;
        background: rgba(15, 23, 42, 0.95);
        border: 2px solid rgba(220, 39, 67, 0.4);
        border-radius: 28px;
        padding: 24px 20px;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.6), 0 0 35px rgba(220, 39, 67, 0.3);
        text-align: center;
        box-sizing: border-box;
    }

    @media (max-width: 991px) {
        .app-showcase-container {
            grid-template-columns: 1fr;
            gap: 2.5rem;
            padding: 2.5rem 2rem;
            text-align: center;
        }
        .app-buttons-wrap {
            justify-content: center;
        }
    }

    @media (max-width: 600px) {
        .app-showcase-section {
            padding: 1.5rem 12px 3rem 12px;
        }
        .app-showcase-container {
            padding: 1.75rem 1.25rem;
            border-radius: 20px;
        }
        .app-title-main {
            font-size: 1.7rem;
            line-height: 1.25;
        }
        .app-desc-main {
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .app-features-grid {
            grid-template-columns: 1fr;
            gap: 14px;
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .app-buttons-wrap {
            flex-direction: column;
            width: 100%;
            gap: 10px;
        }
        .app-buttons-wrap button {
            width: 100%;
            justify-content: center;
            padding: 12px 16px;
            font-size: 0.92rem;
        }
        .app-preview-card {
            max-width: 100%;
            padding: 20px 16px;
            border-radius: 20px;
        }
    }
</style>
@endsection

@section('content')

    <!-- 1. Hero Section with Auth Landing Component -->
    @auth
        <section class="hero-wrapper">
            <div class="hero-grid">
                <!-- Left Info Block -->
                <div class="animate-fade-in">
                    <div class="hero-badge">
                        <i class="fa-solid fa-crown"></i> #1 Most Affordable SMM Panel in India
                    </div>
                    <h1 class="hero-title-main">
                        Automate & Scale Your Social Media with <span class="text-gradient">{{ App\Models\Setting::get('site_name', 'RishiSMM') }}</span>
                    </h1>
                    <p class="hero-desc-text">
                        Get genuine high-retention Instagram Followers, Likes, YouTube Views, Telegram Members, and Facebook Engagement with instant automated delivery and 24/7 dedicated support.
                    </p>

                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <a href="{{ route('services') }}" class="btn-outline" style="padding: 12px 24px; font-size: 0.95rem; border-radius: var(--radius-md);">
                            <i class="fa-solid fa-list-check" style="margin-right: 8px;"></i> Explore 250+ Services
                        </a>
                        <a href="{{ route('rules') }}" class="btn-outline" style="padding: 12px 24px; font-size: 0.95rem; border-radius: var(--radius-md); border-color: rgba(255,255,255,0.15);">
                            <i class="fa-solid fa-gavel" style="margin-right: 8px;"></i> Rules & Guidelines
                        </a>
                    </div>
                </div>

                <!-- Right Logged-in Welcome Card -->
                <div class="animate-fade-in" style="animation-delay: 0.15s;">
                    <div class="glass quick-login-card">
                        <div style="text-align: center; padding: 1.5rem 0;">
                            <div style="width: 70px; height: 70px; border-radius: 50%; background: var(--grad-insta); margin: 0 auto 1.25rem auto; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white;">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <h3 style="font-size: 1.4rem; font-weight: 700; margin-bottom: 6px;">Welcome Back, {{ Auth::user()->name }}!</h3>
                            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">
                                Wallet Balance: <strong style="color: var(--color-success); font-size: 1.1rem;">₹{{ number_format(Auth::user()->balance, 2) }}</strong>
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <a href="{{ route('dashboard') }}" class="btn-gradient" style="padding: 12px; font-size: 1rem; border-radius: var(--radius-md);">
                                    <i class="fa-solid fa-cart-plus" style="margin-right: 8px;"></i> Go to New Order Dashboard
                                </a>
                                <a href="{{ route('add_funds') }}" class="btn-outline" style="padding: 12px; font-size: 0.95rem; border-radius: var(--radius-md);">
                                    <i class="fa-solid fa-wallet" style="margin-right: 8px;"></i> Add Funds to Wallet
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @else
        @include('auth.auth_landing_component')
    @endauth
                </div>
            </div>
        </div>
    </section>

    <!-- 2. Live Stats Ribbon -->
    <section class="stats-ribbon animate-fade-in">
        <div class="stats-ribbon-grid">
            <div class="glass stat-box">
                <div class="stat-number">1.68M+</div>
                <div class="stat-label">Orders Completed</div>
            </div>
            <div class="glass stat-box">
                <div class="stat-number">35K+</div>
                <div class="stat-label">Happy Active Users</div>
            </div>
            <div class="glass stat-box">
                <div class="stat-number">250+</div>
                <div class="stat-label">Live Active Services</div>
            </div>
            <div class="glass stat-box">
                <div class="stat-number">0.1s</div>
                <div class="stat-label">Average API Speed</div>
            </div>
        </div>
    </section>

    <!-- 2.5 Dedicated Mobile App Showcase & Install Section (Visible on Web, Hidden in APK) -->
    <section class="app-showcase-section app-install-only-web">
        <div class="glass app-showcase-container">
            <div>
                <span class="section-tag" style="display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-mobile-screen-button"></i> 1-Click Mobile App
                </span>
                <h2 class="app-title-main">
                    Install <span class="text-gradient">{{ App\Models\Setting::get('site_name', 'RishiSMM') }} App</span> On Your Phone
                </h2>
                <p class="app-desc-main">
                    Enjoy lightning-fast order placement, instant live order updates, and 1-tap wallet deposits directly from your home screen. No browser required.
                </p>

                <div class="app-features-grid">
                    <div class="app-feature-item">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(220, 39, 67, 0.15); border: 1px solid rgba(220, 39, 67, 0.3); color: #dc2743; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div>
                            <strong style="color: #ffffff; font-size: 0.95rem; display: block;">2x Faster Speed</strong>
                            <span style="color: #94a3b8; font-size: 0.82rem;">Instant load time without browser caching delay.</span>
                        </div>
                    </div>

                    <div class="app-feature-item">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                            <i class="fa-solid fa-lock"></i>
                        </div>
                        <div>
                            <strong style="color: #ffffff; font-size: 0.95rem; display: block;">Always Logged In</strong>
                            <span style="color: #94a3b8; font-size: 0.82rem;">Never re-type passwords again. Secure 1-tap open.</span>
                        </div>
                    </div>

                    <div class="app-feature-item">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.3); color: #38bdf8; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div>
                            <strong style="color: #ffffff; font-size: 0.95rem; display: block;">Live Order Alerts</strong>
                            <span style="color: #94a3b8; font-size: 0.82rem;">Real-time notifications for completed orders & deposits.</span>
                        </div>
                    </div>

                    <div class="app-feature-item">
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(234, 179, 8, 0.15); border: 1px solid rgba(234, 179, 8, 0.3); color: #eab308; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; flex-shrink: 0;">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>
                        <div>
                            <strong style="color: #ffffff; font-size: 0.95rem; display: block;">100% Safe & Light</strong>
                            <span style="color: #94a3b8; font-size: 0.82rem;">Under 2MB size, zero battery drain & smooth UI.</span>
                        </div>
                    </div>
                </div>

                <div class="app-buttons-wrap">
                    <button type="button" onclick="triggerPWAInstall()" class="btn-gradient" style="padding: 14px 28px; font-size: 1rem; font-weight: 800; border-radius: var(--radius-md); display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 10px 25px rgba(220, 39, 67, 0.4); cursor: pointer; border: none;">
                        <i class="fa-brands fa-android" style="font-size: 1.3rem;"></i> Install Android App
                    </button>
                    <button type="button" onclick="triggerPWAInstall()" class="btn-outline" style="padding: 14px 24px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md); display: inline-flex; align-items: center; gap: 10px; cursor: pointer; border-color: rgba(255, 255, 255, 0.25);">
                        <i class="fa-brands fa-apple" style="font-size: 1.3rem;"></i> Add to iPhone
                    </button>
                </div>
            </div>

            <div class="app-preview-wrapper">
                <div class="app-preview-card">
                    <div style="width: 80px; height: 80px; margin: 0 auto 16px auto; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 25px rgba(220,39,67,0.45); border: 2px solid rgba(255,255,255,0.15);">
                        <img src="{{ asset('images/icons/icon-192x192.png') }}" alt="App Logo" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <h4 style="font-size: 1.35rem; font-weight: 800; color: #ffffff; margin-bottom: 4px;">
                        {{ App\Models\Setting::get('site_name', 'RishiSMM') }} App
                    </h4>
                    <span style="font-size: 0.8rem; color: #10b981; font-weight: 700; display: inline-block; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); padding: 3px 10px; border-radius: 20px; margin-bottom: 1.5rem;">
                        <i class="fa-solid fa-circle-check"></i> Official Mobile App
                    </span>

                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 14px; padding: 14px; margin-bottom: 1.25rem; text-align: left;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">
                            <span>App Size:</span> <strong style="color: #ffffff;">1.8 MB (Ultra Lite)</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #cbd5e1; margin-bottom: 6px;">
                            <span>Compatibility:</span> <strong style="color: #ffffff;">Android & iOS</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.82rem; color: #cbd5e1;">
                            <span>Version:</span> <strong style="color: #38bdf8;">v2.4.0 (Latest)</strong>
                        </div>
                    </div>

                    <button type="button" onclick="triggerPWAInstall()" class="btn-gradient" style="width: 100%; padding: 12px; font-size: 0.95rem; font-weight: 800; border-radius: 12px; border: none; cursor: pointer;">
                        <i class="fa-solid fa-download"></i> 1-Click Install Now
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- 3. All-in-One Social Platform Grid -->
    <section style="padding: 3rem 5% 5rem 5%;">
        <div class="section-header-block">
            <span class="section-tag">Supported Networks</span>
            <h2 class="section-title">All-in-One SMM Panel for <span class="text-gradient">Every Platform</span></h2>
            <p class="section-subtitle">Boost your presence across all major social media platforms with verified, high-speed, and secure services.</p>
        </div>

        <div class="platform-cards-grid">
            <!-- Instagram -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(225, 48, 108, 0.15); color: #e1306c;">
                    <i class="fa-brands fa-instagram"></i>
                </div>
                <h3 class="platform-card-title">Instagram SMM Panel</h3>
                <p class="platform-card-desc">Real & Non-Drop Followers, Reels Views with High Watch-Time, Instant Likes, Custom Comments, and Story Reach.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- YouTube -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(255, 0, 0, 0.15); color: #ff0000;">
                    <i class="fa-brands fa-youtube"></i>
                </div>
                <h3 class="platform-card-title">YouTube SMM Panel</h3>
                <p class="platform-card-desc">Monetization Watch Time Hours, Non-Drop Subscribers, High Retention Views, Shorts Views, and Video Likes.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- Telegram -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(0, 136, 204, 0.15); color: #0088cc;">
                    <i class="fa-brands fa-telegram"></i>
                </div>
                <h3 class="platform-card-title">Telegram SMM Panel</h3>
                <p class="platform-card-desc">Channel Members, Group Members, Auto Post Views for last 20 posts, and Emoji Reactions at micro prices.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- WhatsApp -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(37, 211, 102, 0.15); color: #25d366;">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <h3 class="platform-card-title">WhatsApp SMM Panel</h3>
                <p class="platform-card-desc">WhatsApp Channel Followers, Community Members, and Status Reactions to scale your business broadcasts.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- Facebook -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(24, 119, 242, 0.15); color: #1877f2;">
                    <i class="fa-brands fa-facebook"></i>
                </div>
                <h3 class="platform-card-title">Facebook SMM Panel</h3>
                <p class="platform-card-desc">Page Likes & Followers, Profile Followers, Post Likes, Video Views, and Live Stream Viewers.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- Twitter / X -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(29, 161, 242, 0.15); color: #1da1f2;">
                    <i class="fa-brands fa-x-twitter"></i>
                </div>
                <h3 class="platform-card-title">Twitter / X SMM Panel</h3>
                <p class="platform-card-desc">Twitter Followers, Retweets, Likes, Tweet Impressions, Poll Votes, and Space Listeners.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- Spotify -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(30, 215, 96, 0.15); color: #1ed760;">
                    <i class="fa-brands fa-spotify"></i>
                </div>
                <h3 class="platform-card-title">Spotify SMM Panel</h3>
                <p class="platform-card-desc">Track Plays, Monthly Listeners, Playlist Followers, and Artist Profile Followers to grow your music streams.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>

            <!-- TikTok -->
            <div class="glass platform-showcase-card">
                <div class="platform-icon-circle" style="background: rgba(0, 242, 254, 0.15); color: #00f2fe;">
                    <i class="fa-brands fa-tiktok"></i>
                </div>
                <h3 class="platform-card-title">TikTok SMM Panel</h3>
                <p class="platform-card-desc">TikTok Followers, Video Likes, High Retention Views, Shares, and Live Stream Views delivered instantly.</p>
                <a href="{{ route('services') }}" class="platform-card-link">View Services <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </section>

    <!-- 4. How It Works (4-Step Process) -->
    <section style="padding: 5rem 5%; background: rgba(255, 255, 255, 0.01);">
        <div class="section-header-block">
            <span class="section-tag">Simple Process</span>
            <h2 class="section-title">How to Use <span class="text-gradient">{{ App\Models\Setting::get('site_name', 'RishiSMM') }}</span></h2>
            <p class="section-subtitle">Get started and scale your social media profiles in 4 easy steps.</p>
        </div>

        <div class="step-process-grid">
            <div class="glass step-card">
                <div class="step-badge">1</div>
                <h4>Register Account</h4>
                <p>Sign up for free in 10 seconds or log in using Google OAuth with one click.</p>
            </div>
            <div class="glass step-card">
                <div class="step-badge">2</div>
                <h4>Add Funds (UPI/QR)</h4>
                <p>Scan our automated UPI QR code and submit your 12-digit UTR for fast wallet balance recharge.</p>
            </div>
            <div class="glass step-card">
                <div class="step-badge">3</div>
                <h4>Select Service</h4>
                <p>Choose your platform, select from 250+ active services, and paste your target link.</p>
            </div>
            <div class="glass step-card">
                <div class="step-badge">4</div>
                <h4>Instant Delivery</h4>
                <p>Our automated high-speed servers dispatch your order immediately with live status tracking.</p>
            </div>
        </div>
    </section>

    <!-- 5. Why Choose Us (6 Features Grid) -->
    <section style="padding: 5rem 5%;">
        <div class="section-header-block">
            <span class="section-tag">Why We Are #1</span>
            <h2 class="section-title">Why Choose <span class="text-gradient">Our Platform</span>?</h2>
            <p class="section-subtitle">Trusted by thousands of digital marketers, agencies, and influencers across India and worldwide.</p>
        </div>

        <div class="features-grid">
            <div class="glass feature-card">
                <div class="feature-icon-box" style="background: rgba(220, 39, 67, 0.1); color: var(--color-primary);">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <h3>Superfast API Dispatch</h3>
                <p>Our orders are connected directly to direct provider nodes, ensuring your order starts within seconds of placement.</p>
            </div>

            <div class="glass feature-card">
                <div class="feature-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <h3>Cheapest Wholesale Pricing</h3>
                <p>We provide wholesale SMM rates starting at just ₹0.05 per 1,000 quantity, maximizing your profit margins as a reseller.</p>
            </div>

            <div class="glass feature-card">
                <div class="feature-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h3>100% Safe & Secure</h3>
                <p>We never ask for account passwords. All deliveries use natural algorithmic pacing to keep your profile 100% safe.</p>
            </div>

            <div class="glass feature-card">
                <div class="feature-icon-box" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </div>
                <h3>Automated Refill Guarantee</h3>
                <p>Services labeled with Refill come with free automated or one-click button refill warranty if any drop occurs.</p>
            </div>

            <div class="glass feature-card">
                <div class="feature-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                    <i class="fa-solid fa-qrcode"></i>
                </div>
                <h3>Instant UPI QR Payments</h3>
                <p>Support for Google Pay, PhonePe, Paytm, and all Indian UPI apps with 12-digit UTR verification and zero transaction fees.</p>
            </div>

            <div class="glass feature-card">
                <div class="feature-icon-box" style="background: rgba(37, 211, 102, 0.1); color: #25d366;">
                    <i class="fa-brands fa-whatsapp"></i>
                </div>
                <h3>24/7 Priority Support</h3>
                <p>Have an inquiry? Our dedicated customer care team is available around the clock via Support Tickets and WhatsApp Chat.</p>
            </div>
        </div>
    </section>

    <!-- 6. Interactive FAQ Accordion -->
    <section style="padding: 4rem 5% 5rem 5%; background: rgba(255, 255, 255, 0.01);">
        <div class="section-header-block">
            <span class="section-tag">Got Questions?</span>
            <h2 class="section-title">Frequently Asked <span class="text-gradient">Questions</span></h2>
            <p class="section-subtitle">Everything you need to know about our services, payments, and order fulfillment.</p>
        </div>

        <div class="faq-wrapper">
            <div class="glass faq-item active">
                <div class="faq-question">
                    <span>What is an SMM Panel?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer" style="max-height: 200px;">
                    An SMM (Social Media Marketing) Panel is an online portal where individuals, influencers, and digital marketing agencies can buy social media services like followers, likes, comments, watch time, and views to boost digital credibility.
                </div>
            </div>

            <div class="glass faq-item">
                <div class="faq-question">
                    <span>How do I add money to my wallet?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Go to the <strong>Add Funds</strong> page, scan the UPI QR code using any app (GPay, PhonePe, Paytm, BHIM), complete the payment, and enter your 12-digit numeric UTR reference number. Your funds will be credited to your wallet balance.
                </div>
            </div>

            <div class="glass faq-item">
                <div class="faq-question">
                    <span>Are these services safe for my social media account?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Yes, 100%! We only need your public username or post link. We never ask for your account password, and our delivery methods comply with platform safety limits.
                </div>
            </div>

            <div class="glass faq-item">
                <div class="faq-question">
                    <span>How fast will my order start?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Most orders marked with "Instant Start" begin within 0 to 5 minutes of order placement. You can monitor the real-time status (Pending, Processing, Completed) directly on your Orders History page.
                </div>
            </div>

            <div class="glass faq-item">
                <div class="faq-question">
                    <span>What does the "Refill" button mean?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    If a service has a 30-Day, 60-Day, or Lifetime Refill guarantee and you experience any drop, you can click the "Refill" button on your My Orders page to automatically restore the dropped quantity for free.
                </div>
            </div>

            <div class="glass faq-item">
                <div class="faq-question">
                    <span>Can I connect my own website or panel using API?</span>
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
                <div class="faq-answer">
                    Yes! We provide full Reseller API documentation with standard SMM endpoints. Generate your API key in your Profile and integrate with any SMM script or custom backend effortlessly.
                </div>
            </div>
        </div>
    </section>

    <!-- 7. Call To Action Banner -->
    <section class="cta-banner animate-fade-in">
        <div class="glass cta-inner">
            <h2 style="font-size: 2.4rem; font-weight: 800; margin-bottom: 12px;">
                Ready to Grow Your Social Media Presence?
            </h2>
            <p style="color: var(--text-secondary); font-size: 1.05rem; max-width: 650px; margin: 0 auto 2rem auto;">
                Join 35,000+ creators, brands, and agencies leveraging {{ App\Models\Setting::get('site_name', 'RishiSMM') }} for high-speed social growth today.
            </p>
            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-gradient" style="padding: 14px 36px; font-size: 1.05rem; font-weight: 700; border-radius: var(--radius-md);">
                        <i class="fa-solid fa-cart-plus" style="margin-right: 8px;"></i> Place an Order Now
                    </a>
                @else
                    <a href="{{ route('register') }}" class="btn-gradient" style="padding: 14px 36px; font-size: 1.05rem; font-weight: 700; border-radius: var(--radius-md);">
                        <i class="fa-solid fa-user-plus" style="margin-right: 8px;"></i> Create Free Account
                    </a>
                    <a href="{{ route('login') }}" class="btn-outline" style="padding: 14px 36px; font-size: 1.05rem; font-weight: 700; border-radius: var(--radius-md);">
                        <i class="fa-solid fa-right-to-bracket" style="margin-right: 8px;"></i> Sign In
                    </a>
                @endauth
            </div>
        </div>
    </section>

    <!-- Interactive FAQ Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');

                question.addEventListener('click', function() {
                    const isOpen = item.classList.contains('active');

                    // Close all others
                    faqItems.forEach(otherItem => {
                        otherItem.classList.remove('active');
                        otherItem.querySelector('.faq-answer').style.maxHeight = null;
                    });

                    if (!isOpen) {
                        item.classList.add('active');
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                    }
                });
            });
        });
    </script>

@endsection
