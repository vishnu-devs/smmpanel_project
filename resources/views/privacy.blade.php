@extends(Auth::check() ? 'layouts.app' : 'layouts.landing')

@section('title', 'Privacy Policy - ' . App\Models\Setting::get('site_name', 'SMM Panel'))
@section('page_header', 'Privacy Policy')

@section('styles')
<style>
    .policy-container {
        max-width: 900px;
        margin: 0 auto;
        padding-bottom: 3rem;
    }
    .policy-card {
        border-radius: var(--radius-lg);
        padding: 2.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
        background: var(--bg-card);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    .policy-header-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px;
        background: rgba(220, 39, 67, 0.1);
        border: 1px solid rgba(220, 39, 67, 0.3);
        border-radius: 30px;
        color: var(--color-primary);
        font-size: 0.85rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }
    .policy-section {
        margin-bottom: 2.25rem;
    }
    .policy-section:last-child {
        margin-bottom: 0;
    }
    .policy-section h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .policy-section p {
        color: var(--text-secondary);
        font-size: 0.95rem;
        line-height: 1.7;
        margin-bottom: 10px;
    }
    .policy-list {
        list-style: none;
        padding: 0;
        margin: 12px 0 0 0;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .policy-list-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 0.92rem;
        color: var(--text-secondary);
        line-height: 1.6;
        background: rgba(255, 255, 255, 0.02);
        padding: 10px 14px;
        border-radius: var(--radius-sm);
        border: 1px solid rgba(255, 255, 255, 0.04);
    }
    .policy-list-item i {
        color: #10b981;
        margin-top: 4px;
        font-size: 0.9rem;
    }
    .highlight-card {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(37, 99, 235, 0.03));
        border: 1px solid rgba(59, 130, 246, 0.3);
        border-radius: var(--radius-md);
        padding: 1.25rem 1.5rem;
        margin: 1.5rem 0;
    }
    .highlight-card h4 {
        color: #3b82f6;
        font-size: 1.05rem;
        font-weight: 700;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .highlight-card p {
        margin: 0;
        color: var(--text-primary);
        font-size: 0.9rem;
        line-height: 1.6;
    }
</style>
@endsection

@section('content')
<div class="policy-container animate-fade-in">

    <div class="glass policy-card">
        <div style="text-align: center; margin-bottom: 2.5rem; border-bottom: 1px solid var(--border-color); padding-bottom: 2rem;">
            <div class="policy-header-badge">
                <i class="fa-solid fa-shield-halved"></i> Privacy & Data Protection
            </div>
            <h1 style="font-size: 2.2rem; font-weight: 800; margin-bottom: 10px; letter-spacing: -0.5px;">
                Privacy <span class="text-gradient">Policy</span>
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem; max-width: 600px; margin: 0 auto;">
                Effective Date: {{ date('F d, Y') }} | Last Updated for {{ App\Models\Setting::get('site_name', 'RishiSMM') }}
            </p>
        </div>

        <!-- 1. Introduction -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-circle-info text-gradient"></i> 1. Introduction</h3>
            <p>
                Welcome to <strong>{{ App\Models\Setting::get('site_name', 'RishiSMM') }}</strong> ("we", "our", or "us"). We are committed to protecting your privacy and ensuring your personal information is handled in a safe and responsible manner.
            </p>
            <p>
                This Privacy Policy explains how we collect, use, store, and protect your data when you visit our website, register an account, add funds to your wallet, or order social media marketing (SMM) services.
            </p>
        </div>

        <!-- 2. Information We Collect -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-database text-gradient"></i> 2. Information We Collect</h3>
            <p>
                We collect only the essential information required to deliver services, manage your wallet, and prevent fraudulent activities:
            </p>
            <div class="policy-list">
                <div class="policy-list-item">
                    <i class="fa-solid fa-user-check"></i>
                    <div><strong>Account Information:</strong> Name, Email Address, Username, and Encrypted Password created during registration or Google OAuth login.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-receipt"></i>
                    <div><strong>Transaction & Payment Details:</strong> 12-Digit Bank/UPI UTR Number, payment amount, date/time, and gateway mode. <em>We never store your bank account passwords, UPI PINs, or credit card CVV details.</em></div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-link"></i>
                    <div><strong>Order Information:</strong> Public Social Media profile links, post URLs, channels, and targeted quantities provided for order fulfillment.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-shield"></i>
                    <div><strong>Technical & Security Logs:</strong> IP address, device browser type, and authentication timestamps used strictly for 2FA verification and fraud prevention.</div>
                </div>
            </div>
        </div>

        <!-- 3. How We Use Your Information -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-gears text-gradient"></i> 3. How We Use Your Information</h3>
            <p>
                The information we collect is utilized strictly for the following purposes:
            </p>
            <div class="policy-list">
                <div class="policy-list-item">
                    <i class="fa-solid fa-check"></i>
                    <div>To automate order dispatch and track delivery progress via provider APIs.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-check"></i>
                    <div>To verify UPI/Bank deposit UTR references and credit wallet funds accurately.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-check"></i>
                    <div>To send Two-Factor Authentication (2FA) security OTPs and critical account alerts.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-check"></i>
                    <div>To provide 24/7 customer support via ticket inquiries and official WhatsApp channels.</div>
                </div>
            </div>
        </div>

        <!-- 4. Security & Data Protection -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-lock text-gradient"></i> 4. Data Protection & Security</h3>
            <p>
                We employ industry-standard security protocols to protect your personal information against unauthorized access, alteration, or disclosure:
            </p>
            <div class="highlight-card">
                <h4><i class="fa-solid fa-shield-virus"></i> Security Safeguards</h4>
                <p>
                    • All passwords are irreversibly hashed using modern <strong>Bcrypt algorithms</strong>.<br>
                    • All data transmission is secured with <strong>256-bit SSL/TLS Encryption</strong>.<br>
                    • Optional/Mandatory <strong>Email OTP 2FA</strong> ensures that only verified owners can access accounts.<br>
                    • We <strong>NEVER</strong> sell, rent, or trade your personal information to third-party advertisers.
                </p>
            </div>
        </div>

        <!-- 5. Third-Party Service Providers -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-plug text-gradient"></i> 5. Third-Party API Providers</h3>
            <p>
                To deliver automated social media services, your target link/username and order quantity are transmitted to our verified upstream server APIs. We <strong>never share your personal email, password, or financial information</strong> with any third-party providers.
            </p>
        </div>

        <!-- 6. Zero Tolerance Fraud Policy -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-triangle-exclamation" style="color: #ef4444;"></i> 6. Zero Tolerance Anti-Fraud Policy</h3>
            <p>
                {{ App\Models\Setting::get('site_name', 'RishiSMM') }} strictly prohibits the use of our services for illegal campaigns, money doubling scams, phishing links, carding, or hate speech. Accounts engaged in fraudulent UPI chargebacks or fake UTR submissions will be permanently blocked, and their IP logs will be shared with relevant law enforcement cyber authorities.
            </p>
        </div>

        <!-- 7. Cookies & Session Management -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-cookie-bite text-gradient"></i> 7. Cookies & Session Tokens</h3>
            <p>
                We use necessary session cookies and CSRF tokens strictly to keep you securely logged in and protect your account from cross-site request forgery. You can configure your browser to reject cookies, though some interactive dashboard features may require cookies to function properly.
            </p>
        </div>

        <!-- 8. User Rights & Account Control -->
        <div class="policy-section">
            <h3><i class="fa-solid fa-user-shield text-gradient"></i> 8. Your Rights & Account Control</h3>
            <p>
                You have full control over your personal data:
            </p>
            <div class="policy-list">
                <div class="policy-list-item">
                    <i class="fa-solid fa-arrow-right"></i>
                    <div>You can update your name, email, or password at any time from your <strong>Profile Settings</strong>.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-arrow-right"></i>
                    <div>You can generate, revoke, or regenerate your Reseller API Key instantly.</div>
                </div>
                <div class="policy-list-item">
                    <i class="fa-solid fa-arrow-right"></i>
                    <div>You can request complete account closure or balance history exports by contacting our support team.</div>
                </div>
            </div>
        </div>

        <!-- 9. Contact & Support -->
        <div class="policy-section" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem; margin-top: 2rem;">
            <h3><i class="fa-solid fa-headset text-gradient"></i> 9. Contact Us</h3>
            <p>
                If you have any questions, suggestions, or concerns regarding our Privacy Policy or data protection practices, please contact us:
            </p>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 15px;">
                @if($email = App\Models\Setting::get('support_email'))
                    <a href="mailto:{{ $email }}" class="btn-outline" style="padding: 10px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-envelope"></i> {{ $email }}
                    </a>
                @endif
                @php
                    $waNum = App\Models\Setting::get('whatsapp_number');
                    $waClean = preg_replace('/[^0-9]/', '', $waNum);
                @endphp
                @if($waClean)
                    <a href="https://wa.me/{{ $waClean }}" target="_blank" class="btn-gradient" style="padding: 10px 18px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: #25d366; color: white;">
                        <i class="fa-brands fa-whatsapp"></i> WhatsApp Support
                    </a>
                @endif
            </div>
        </div>

    </div>

</div>
@endsection
