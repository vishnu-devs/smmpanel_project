@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Reseller & Provider Program - ' . App\Models\Setting::get('site_name', 'RishiSMM'))
@section('page_header', 'Reseller & Provider Program')

@section('styles')
<style>
    .reseller-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: {{ Auth::check() ? '0' : '2rem 5% 5rem 5%' }};
    }

    /* Hero Banner */
    .reseller-hero {
        text-align: center;
        padding: 3rem 1.5rem;
        border-radius: var(--radius-lg);
        margin-bottom: 4rem;
        background: radial-gradient(circle at 50% 20%, rgba(220, 39, 67, 0.2) 0%, transparent 70%),
                    var(--bg-card);
        border: 1px solid var(--border-color);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
    }
    .reseller-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(220, 39, 67, 0.12);
        border: 1px solid rgba(220, 39, 67, 0.35);
        border-radius: 30px;
        color: var(--color-primary);
        font-size: 0.88rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
    }
    .reseller-title {
        font-size: 2.8rem;
        font-weight: 800;
        line-height: 1.2;
        letter-spacing: -0.5px;
        margin-bottom: 1.25rem;
    }
    .reseller-desc {
        font-size: 1.1rem;
        color: var(--text-secondary);
        max-width: 750px;
        margin: 0 auto 2.25rem auto;
        line-height: 1.65;
    }

    /* B2B Benefits Grid */
    .b2b-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.75rem;
        margin-bottom: 5rem;
    }
    .b2b-card {
        padding: 2.25rem 2rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        transition: all 0.3s ease;
    }
    .b2b-card:hover {
        transform: translateY(-5px);
        border-color: rgba(220, 39, 67, 0.4);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
    }
    .b2b-icon-box {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
    }
    .b2b-card h3 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 10px;
        color: var(--text-primary);
    }
    .b2b-card p {
        font-size: 0.92rem;
        color: var(--text-secondary);
        line-height: 1.65;
        margin: 0;
    }

    /* Script Compatibility Section */
    .compat-box {
        padding: 2.5rem;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        margin-bottom: 5rem;
        text-align: center;
    }
    .compat-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
        margin-top: 2rem;
    }
    .compat-pill {
        padding: 12px 20px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.95rem;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.2s ease;
    }
    .compat-pill:hover {
        border-color: var(--color-primary);
        background: rgba(220, 39, 67, 0.08);
        transform: translateY(-2px);
    }

    /* 3-Step Setup */
    .setup-steps-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        margin-bottom: 5rem;
    }
    .setup-step-card {
        padding: 2.25rem 1.75rem;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        text-align: center;
        position: relative;
    }
    .setup-step-number {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin: 0 auto 1.25rem auto;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: 800;
        color: white;
        background: var(--grad-insta);
        box-shadow: 0 4px 15px rgba(220, 39, 67, 0.35);
    }
    .setup-step-card h4 {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 10px;
    }
    .setup-step-card p {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin: 0;
    }

    /* Direct Provider Banner */
    .provider-banner {
        padding: 3rem 2.5rem;
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.12), rgba(5, 150, 105, 0.05));
        border: 1px solid rgba(16, 185, 129, 0.35);
        margin-bottom: 5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 2rem;
    }
    .provider-info {
        max-width: 650px;
    }
    .provider-info h3 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .provider-info p {
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.65;
        margin: 0;
    }

    @media (max-width: 1024px) {
        .b2b-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        .setup-steps-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .reseller-title {
            font-size: 2.1rem;
        }
        .b2b-grid {
            grid-template-columns: 1fr;
        }
        .provider-banner {
            flex-direction: column;
            align-items: flex-start;
            padding: 2rem 1.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="reseller-container animate-fade-in">

    <!-- Hero Banner -->
    <div class="glass reseller-hero">
        <div class="reseller-badge">
            <i class="fa-solid fa-bolt"></i> Official B2B Reseller & Provider Network
        </div>
        <h1 class="reseller-title">
            Power Your Panel with Wholesale <span class="text-gradient">SMM Services</span>
        </h1>
        <p class="reseller-desc">
            Connect your Child Panel, SMM Script, or Digital Agency to {{ App\Models\Setting::get('site_name', 'RishiSMM') }}. Get instant automated fulfillment, direct provider rates from ₹0.05/1K, and 99.9% reliable uptime.
        </p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            @auth
                <a href="{{ route('api.docs') }}" class="btn-gradient" style="padding: 13px 32px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-code" style="margin-right: 8px;"></i> Access Developer API Docs
                </a>
                <a href="{{ route('profile') }}" class="btn-outline" style="padding: 13px 32px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-key" style="margin-right: 8px;"></i> View API Access Key
                </a>
            @else
                <a href="{{ route('register') }}" class="btn-gradient" style="padding: 13px 32px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-user-plus" style="margin-right: 8px;"></i> Register as Reseller
                </a>
                <a href="{{ route('api.docs') }}" class="btn-outline" style="padding: 13px 32px; font-size: 1rem; font-weight: 700; border-radius: var(--radius-md);">
                    <i class="fa-solid fa-code" style="margin-right: 8px;"></i> View API Docs
                </a>
            @endauth
        </div>
    </div>

    <!-- Why Resell With Us (B2B Benefits Grid) -->
    <div style="text-align: center; margin-bottom: 3.5rem;">
        <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-primary); letter-spacing: 1.5px; display: block; margin-bottom: 8px;">Reseller Advantages</span>
        <h2 style="font-size: 2.2rem; font-weight: 800;">Why 500+ Resellers Choose <span class="text-gradient">{{ App\Models\Setting::get('site_name', 'RishiSMM') }}</span></h2>
    </div>

    <div class="b2b-grid">
        <div class="glass b2b-card">
            <div class="b2b-icon-box" style="background: rgba(220, 39, 67, 0.1); color: var(--color-primary);">
                <i class="fa-solid fa-tags"></i>
            </div>
            <h3>Direct Wholesale Rates</h3>
            <p>We source directly from root provider nodes to offer the lowest market prices, allowing you to markup 40% to 100% profit on your own panel.</p>
        </div>

        <div class="glass b2b-card">
            <div class="b2b-icon-box" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                <i class="fa-solid fa-server"></i>
            </div>
            <h3>99.9% High Availability Uptime</h3>
            <p>Our server cluster automatically handles thousands of parallel API requests per minute without delays or dropped orders.</p>
        </div>

        <div class="glass b2b-card">
            <div class="b2b-icon-box" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                <i class="fa-solid fa-arrows-rotate"></i>
            </div>
            <h3>Automated Refill & Sync API</h3>
            <p>Seamless standard API endpoints for service fetching, automatic order creation, instant live status polling, and automated refill triggers.</p>
        </div>

        <div class="glass b2b-card">
            <div class="b2b-icon-box" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                <i class="fa-solid fa-gift"></i>
            </div>
            <h3>VIP Deposit Bonus Cashback</h3>
            <p>Enjoy tiered deposit bonuses on bulk balance additions. The more you deposit, the higher your bonus wallet credit.</p>
        </div>

        <div class="glass b2b-card">
            <div class="b2b-icon-box" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
                <i class="fa-solid fa-qrcode"></i>
            </div>
            <h3>Zero Fee UPI & Bank Top-ups</h3>
            <p>Automated 12-digit UTR verification with 0% gateway commission fees, ensuring 100% of your funds go directly towards your orders.</p>
        </div>

        <div class="glass b2b-card">
            <div class="b2b-icon-box" style="background: rgba(37, 211, 102, 0.1); color: #25d366;">
                <i class="fa-brands fa-whatsapp"></i>
            </div>
            <h3>Priority Reseller Support</h3>
            <p>High-volume resellers receive direct access to priority WhatsApp support for instant speed boosts, custom pricing, and technical assistance.</p>
        </div>
    </div>

    <!-- SMM Panel Script Compatibility -->
    <div class="glass compat-box">
        <h3 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px;">
            100% Compatible with All Popular <span class="text-gradient">SMM Scripts</span>
        </h3>
        <p style="color: var(--text-secondary); max-width: 650px; margin: 0 auto; font-size: 0.95rem;">
            Our standard REST API v2 integrates seamlessly with every modern SMM panel framework and custom backend.
        </p>

        <div class="compat-pills">
            <div class="compat-pill"><i class="fa-solid fa-cube text-gradient"></i> PerfectPanel</div>
            <div class="compat-pill"><i class="fa-solid fa-cubes text-gradient"></i> SmartPanel</div>
            <div class="compat-pill"><i class="fa-solid fa-layer-group text-gradient"></i> RentASMM</div>
            <div class="compat-pill"><i class="fa-solid fa-code text-gradient"></i> Custom PHP & Laravel</div>
            <div class="compat-pill"><i class="fa-brands fa-python text-gradient"></i> Python & FastAPI</div>
            <div class="compat-pill"><i class="fa-brands fa-node-js text-gradient"></i> Node.js & Express</div>
            <div class="compat-pill"><i class="fa-brands fa-telegram text-gradient"></i> Telegram SMM Bots</div>
        </div>
    </div>

    <!-- 3-Step Setup Guide -->
    <div style="text-align: center; margin-bottom: 3.5rem;">
        <span style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-primary); letter-spacing: 1.5px; display: block; margin-bottom: 8px;">Quick Integration</span>
        <h2 style="font-size: 2.2rem; font-weight: 800;">Start Reselling in <span class="text-gradient">3 Simple Steps</span></h2>
    </div>

    <div class="setup-steps-grid">
        <div class="glass setup-step-card">
            <div class="setup-step-number">1</div>
            <h4>Get Your API Key</h4>
            <p>Create a free account and navigate to your Profile page to generate your private API key.</p>
        </div>

        <div class="glass setup-step-card">
            <div class="setup-step-number">2</div>
            <h4>Add Us as Provider</h4>
            <p>Paste our API URL <code>{{ url('/api/v1') }}</code> and your API key into your panel's provider settings.</p>
        </div>

        <div class="glass setup-step-card">
            <div class="setup-step-number">3</div>
            <h4>Map Services & Profit</h4>
            <p>Import your desired services, set your profit margin, and let orders process automatically 24/7.</p>
        </div>
    </div>

    <!-- Become a Direct Service Provider / Supplier Banner -->
    <div class="glass provider-banner">
        <div class="provider-info">
            <h3><i class="fa-solid fa-handshake" style="color: #10b981;"></i> Are You a Direct Service Provider?</h3>
            <p>
                If you run your own direct Instagram servers, YouTube watch-time bots, or Telegram member infrastructure, we are looking for reliable high-speed providers! Partner with {{ App\Models\Setting::get('site_name', 'RishiSMM') }} to receive thousands of daily automated orders with instant weekly/daily settlements.
            </p>
        </div>
        @php
            $waNum = App\Models\Setting::get('whatsapp_number');
            $waClean = preg_replace('/[^0-9]/', '', $waNum);
            $siteName = App\Models\Setting::get('site_name', 'SMM Panel');
        @endphp
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            @if($waClean)
                <a href="https://wa.me/{{ $waClean }}?text={{ urlencode('Hello, I want to become a Service Provider on ' . $siteName) }}" target="_blank" class="btn-gradient" style="padding: 12px 24px; font-size: 0.95rem; font-weight: 700; background: #10b981; color: white; border-radius: var(--radius-md); text-decoration: none; display: inline-flex; align-items: gap: 8px;">
                    <i class="fa-brands fa-whatsapp"></i> Partner via WhatsApp
                </a>
            @endif
            <a href="{{ route('tickets.index') }}" class="btn-outline" style="padding: 12px 24px; font-size: 0.95rem; font-weight: 700; border-radius: var(--radius-md); text-decoration: none;">
                <i class="fa-solid fa-ticket" style="margin-right: 6px;"></i> Submit Provider Ticket
            </a>
        </div>
    </div>

</div>
@endsection
