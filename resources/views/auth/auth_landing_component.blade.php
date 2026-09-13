@php
    $recaptchaEnabled = App\Models\Setting::get('recaptcha_status', 'disabled') === 'enabled';
    $recaptchaSiteKey = App\Models\Setting::get('recaptcha_site_key', '');

    // Determine initial active tab based on route, query, session, or errors
    $activeTab = $activeTab ?? 'login';
    if (
        request()->is('register') || 
        request()->has('ref') || 
        session('registration_referral_code') || 
        session('referral_code') || 
        $errors->has('name') || 
        $errors->has('password_confirmation') || 
        $errors->has('terms') || 
        $errors->has('whatsapp')
    ) {
        $activeTab = 'register';
    }
@endphp

<div class="auth-landing-hero">
    <div class="auth-landing-grid">
        <!-- LEFT PANEL: Marketing & Feature Information -->
        <div class="auth-left-panel">
            <div class="auth-brand-badge">
                <i class="fa-solid fa-fire text-gradient"></i> MAIN SMM PROVIDER
            </div>
            
            <h1 class="auth-headline">
                Boost Social Growth With <span class="text-gradient">{{ App\Models\Setting::getSiteName() }}</span>.<br>
                <span style="color: var(--color-primary);">Direct Provider</span>
            </h1>

            <p class="auth-subheadline">
                Buy followers, likes, views and more across every platform — automated, instant, and the cheapest rates in the market. Trusted by thousands of resellers worldwide.
            </p>

            <div class="auth-trust-list">
                <div class="auth-trust-item">
                    <i class="fa-solid fa-bolt" style="color: #f59e0b;"></i> Instant 24/7 Delivery
                </div>
                <div class="auth-trust-item">
                    <i class="fa-solid fa-shield-halved" style="color: #10b981;"></i> 100% Safe & Secure
                </div>
                <div class="auth-trust-item">
                    <i class="fa-solid fa-headset" style="color: #3b82f6;"></i> 24/7 Customer Support
                </div>
            </div>
        </div>

        <!-- RIGHT PANEL: Neumorphic/Claymorphic Auth Card -->
        <div class="auth-right-panel">
            <div class="auth-clay-card animate-fade-in">
                <!-- Pill Tab Switcher -->
                <div class="auth-pill-tabs">
                    <button type="button" id="btnTabSignIn" class="auth-tab-btn {{ $activeTab === 'login' ? 'active' : '' }}" onclick="switchAuthTab('login')">
                        <i class="fa-solid fa-right-to-bracket" style="margin-right: 4px;"></i> Sign In
                    </button>
                    <button type="button" id="btnTabRegister" class="auth-tab-btn {{ $activeTab === 'register' ? 'active' : '' }}" onclick="switchAuthTab('register')">
                        <i class="fa-solid fa-user-plus" style="margin-right: 4px;"></i> Register
                    </button>
                </div>

                <!-- Alert Messages Container -->
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <div><p>{{ session('error') }}</p></div>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <div><p>{{ session('success') }}</p></div>
                    </div>
                @endif

                @if(session('info') || session('status'))
                    <div class="alert alert-info">
                        <i class="fa-solid fa-circle-info"></i>
                        <div><p>{{ session('info') ?? session('status') }}</p></div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-xmark"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- TAB 1: SIGN IN FORM -->
                <div id="tabSignIn" style="display: {{ $activeTab === 'login' ? 'block' : 'none' }};">
                    <div class="auth-card-header">
                        <h2 class="auth-card-title">Welcome Back</h2>
                        <p class="auth-card-subtitle">Sign in to manage your orders & wallet</p>
                    </div>

                    <form action="{{ route('login') }}" method="POST" id="landingLoginForm">
                        @csrf
                        @if($recaptchaEnabled && !empty($recaptchaSiteKey))
                            <input type="hidden" name="g-recaptcha-response" id="landing_login_recaptcha_token">
                        @endif

                        <div class="auth-input-group">
                            <label for="landing_login" class="auth-input-label">Username or Email</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="email" id="landing_login" class="auth-input-field" placeholder="Your username or email" value="{{ old('login') ?? old('email') }}" required autocomplete="username">
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="landing_password" class="auth-input-label">Password</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" id="landing_password" class="auth-input-field" placeholder="••••••••" required autocomplete="current-password">
                            </div>
                        </div>

                        <div class="auth-options-row">
                            <label class="auth-remember-label">
                                <input type="checkbox" name="remember" style="accent-color: var(--color-primary); cursor: pointer;"> Remember Me
                            </label>
                            <a href="{{ route('password.request') }}" class="auth-forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" class="auth-submit-btn">
                            <i class="fa-solid fa-right-to-bracket"></i> Sign In
                        </button>
                    </form>

                    <!-- Google OAuth -->
                    <div class="auth-divider">
                        <span>OR</span>
                    </div>

                    <a href="{{ route('auth.google') }}" class="btn-outline auth-google-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                            <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/>
                            <path d="M3.964 10.71a5.41 5.41 0 01-.282-1.71c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.89 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.164 6.656 3.58 9 3.58z" fill="#EA4335"/>
                        </svg>
                        Sign In with Google
                    </a>
                </div>

                <!-- TAB 2: REGISTER FORM -->
                <div id="tabRegister" style="display: {{ $activeTab === 'register' ? 'block' : 'none' }};">
                    <div class="auth-card-header">
                        <h2 class="auth-card-title">Create Account</h2>
                        <p class="auth-card-subtitle">Get started with {{ App\Models\Setting::get('site_name', 'SMM Panel') }} today</p>
                    </div>

                    <form action="{{ route('register') }}" method="POST" id="landingRegisterForm">
                        @csrf
                        @if($recaptchaEnabled && !empty($recaptchaSiteKey))
                            <input type="hidden" name="g-recaptcha-response" id="landing_register_recaptcha_token">
                        @endif

                        <div class="auth-input-group">
                            <label for="landing_name" class="auth-input-label">Full Name</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="name" id="landing_name" class="auth-input-field" placeholder="John Doe" value="{{ old('name') }}" required>
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="landing_email" class="auth-input-label">Email Address</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-envelope"></i>
                                <input type="email" name="email" id="landing_email" class="auth-input-field" placeholder="name@example.com" value="{{ old('email') }}" required>
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="landing_whatsapp" class="auth-input-label">WhatsApp Number (Optional)</label>
                            <div class="auth-input-wrapper">
                                <i class="fab fa-whatsapp"></i>
                                <input type="text" name="whatsapp" id="landing_whatsapp" class="auth-input-field" placeholder="e.g. +91 99999 99999" value="{{ old('whatsapp') }}">
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="landing_referral_code" class="auth-input-label">Referral Code (Optional)</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-gift"></i>
                                <input type="text" name="referral_code" id="landing_referral_code" class="auth-input-field" 
                                       placeholder="Enter referral code" 
                                       value="{{ old('referral_code', request('ref', session('registration_referral_code', session('referral_code')))) }}" 
                                       style="text-transform: uppercase; letter-spacing: 0.5px;">
                            </div>
                            <div id="landing_referral_feedback" style="font-size: 0.8rem; margin-top: 5px; font-weight: 600; display: none;"></div>
                        </div>

                        <div class="auth-input-group">
                            <label for="landing_reg_password" class="auth-input-label">Password</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" id="landing_reg_password" class="auth-input-field" placeholder="Minimum 6 characters" required>
                            </div>
                        </div>

                        <div class="auth-input-group">
                            <label for="landing_password_confirmation" class="auth-input-label">Confirm Password</label>
                            <div class="auth-input-wrapper">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password_confirmation" id="landing_password_confirmation" class="auth-input-field" placeholder="Confirm password" required>
                            </div>
                        </div>

                        <div class="auth-options-row" style="margin-bottom: 1.25rem;">
                            <label class="auth-remember-label" style="font-size: 0.85rem; line-height: 1.4;">
                                <input type="checkbox" name="terms" value="1" required style="accent-color: var(--color-primary); cursor: pointer;" {{ old('terms') ? 'checked' : '' }}> 
                                I agree to the <a href="{{ route('rules') }}" target="_blank" style="color: var(--color-primary); text-decoration: underline;">Terms & Conditions</a> and <a href="{{ route('privacy.policy') }}" target="_blank" style="color: var(--color-primary); text-decoration: underline;">Privacy Policy</a>
                            </label>
                        </div>

                        <button type="submit" class="auth-submit-btn">
                            <i class="fa-solid fa-user-plus"></i> Create Account
                        </button>
                    </form>

                    <!-- Google OAuth -->
                    <div class="auth-divider">
                        <span>OR</span>
                    </div>

                    <a href="{{ route('auth.google') }}" class="btn-outline auth-google-btn">
                        <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                            <path d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z" fill="#4285F4"/>
                            <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 009 18z" fill="#34A853"/>
                            <path d="M3.964 10.71a5.41 5.41 0 01-.282-1.71c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 000 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                            <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.89 11.426 0 9 0A8.997 8.997 0 00.957 4.958L3.964 7.29C4.672 5.164 6.656 3.58 9 3.58z" fill="#EA4335"/>
                        </svg>
                        Sign Up with Google
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .auth-landing-hero {
        padding: 3rem 5% 4rem 5%;
        position: relative;
    }
    .auth-landing-grid {
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 3.5rem;
        align-items: flex-start;
        max-width: 1200px;
        margin: 0 auto;
    }
    .auth-left-panel {
        padding-top: 1rem;
    }
    .auth-brand-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 16px;
        background: rgba(220, 39, 67, 0.12);
        border: 1px solid rgba(220, 39, 67, 0.3);
        border-radius: 30px;
        color: var(--color-primary, #dc2743);
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 1.25rem;
    }
    .auth-headline {
        font-size: 3rem;
        font-weight: 900;
        line-height: 1.18;
        letter-spacing: -1px;
        color: var(--text-primary);
        margin-bottom: 1.25rem;
    }
    .auth-subheadline {
        font-size: 1.05rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 2rem;
    }
    .auth-trust-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .auth-trust-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
    }
    .auth-clay-card {
        background: var(--bg-card, #111827);
        border: 1px solid var(--border-color, rgba(255, 255, 255, 0.12));
        border-radius: 24px;
        padding: 2.25rem 2rem;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
        position: relative;
        width: 100%;
        box-sizing: border-box;
    }
    .auth-pill-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        background: rgba(0, 0, 0, 0.22);
        border: 1px solid var(--border-color, rgba(255, 255, 255, 0.08));
        border-radius: 50px;
        padding: 4px;
        margin-bottom: 1.5rem;
    }
    .auth-tab-btn {
        background: transparent;
        border: none;
        padding: 10px 16px;
        border-radius: 40px;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--text-secondary, #94a3b8);
        cursor: pointer;
        transition: all 0.25s ease;
        text-align: center;
    }
    .auth-tab-btn.active {
        background: var(--grad-insta, linear-gradient(135deg, #dc2743, #cc2366));
        color: #ffffff !important;
        box-shadow: 0 4px 15px rgba(220, 39, 67, 0.35);
    }
    .auth-card-header {
        margin-bottom: 1.25rem;
        text-align: left;
    }
    .auth-card-title {
        font-size: 1.5rem;
        font-weight: 800;
        margin: 0 0 4px 0;
        color: var(--text-primary);
    }
    .auth-card-subtitle {
        font-size: 0.88rem;
        color: var(--text-secondary);
        margin: 0;
    }
    .auth-input-group {
        margin-bottom: 1.1rem;
        text-align: left;
    }
    .auth-input-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 5px;
        display: block;
    }
    .auth-input-wrapper {
        position: relative;
    }
    .auth-input-wrapper i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted, #64748b);
        font-size: 1rem;
    }
    .auth-input-field {
        width: 100%;
        padding: 11px 16px 11px 45px;
        background: rgba(0, 0, 0, 0.15);
        border: 1px solid var(--border-color, rgba(255, 255, 255, 0.12));
        border-radius: 50px;
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }
    .auth-input-field:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(220, 39, 67, 0.2);
    }
    .auth-options-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        font-size: 0.88rem;
    }
    .auth-remember-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--text-secondary);
        cursor: pointer;
    }
    .auth-forgot-link {
        color: var(--color-primary);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .auth-forgot-link:hover {
        text-decoration: underline;
    }
    .auth-submit-btn {
        width: 100%;
        padding: 13px 20px;
        border-radius: 50px;
        border: none;
        background: var(--grad-insta, linear-gradient(135deg, #dc2743, #cc2366));
        color: #ffffff;
        font-size: 0.98rem;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 6px 20px rgba(220, 39, 67, 0.35);
        transition: transform 0.2s, box-shadow 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .auth-submit-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 25px rgba(220, 39, 67, 0.45);
    }
    .auth-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin: 1.25rem 0;
    }
    .auth-divider::before, .auth-divider::after {
        content: "";
        height: 1px;
        background: var(--border-color, rgba(255, 255, 255, 0.1));
        flex: 1;
    }
    .auth-divider span {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .auth-google-btn {
        width: 100%;
        padding: 11px 16px;
        border-radius: 50px;
        font-size: 0.92rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        box-sizing: border-box;
    }

    @media (max-width: 991px) {
        .auth-landing-hero {
            padding: 2rem 4% 3rem 4%;
        }
        .auth-landing-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
        .auth-left-panel {
            text-align: center;
        }
        .auth-headline {
            font-size: 2.3rem;
        }
        .auth-subheadline {
            margin-left: auto;
            margin-right: auto;
        }
        .auth-trust-list {
            justify-content: center;
        }
        .auth-clay-card {
            padding: 1.75rem 1.25rem;
            border-radius: 20px;
        }
    }
</style>

<script>
    function switchAuthTab(tabName) {
        var tabSignIn = document.getElementById('tabSignIn');
        var tabRegister = document.getElementById('tabRegister');
        var btnSignIn = document.getElementById('btnTabSignIn');
        var btnRegister = document.getElementById('btnTabRegister');

        if (!tabSignIn || !tabRegister || !btnSignIn || !btnRegister) return;

        if (tabName === 'register') {
            tabSignIn.style.display = 'none';
            tabRegister.style.display = 'block';
            btnSignIn.classList.remove('active');
            btnRegister.classList.add('active');
        } else {
            tabSignIn.style.display = 'block';
            tabRegister.style.display = 'none';
            btnSignIn.classList.add('active');
            btnRegister.classList.remove('active');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Referral code debounced validator for register tab
        var refInput = document.getElementById('landing_referral_code');
        var refFeedback = document.getElementById('landing_referral_feedback');
        var checkTimeout = null;

        function checkReferralCode() {
            if (!refInput || !refFeedback) return;
            var val = refInput.value.trim();
            if (!val) {
                refFeedback.style.display = 'none';
                refFeedback.innerHTML = '';
                return;
            }

            fetch('{{ route('api.referral.check_code') }}?code=' + encodeURIComponent(val))
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    refFeedback.style.display = 'block';
                    if (data.valid) {
                        refFeedback.style.color = '#22c55e';
                        refFeedback.innerHTML = '<i class="fa-solid fa-circle-check"></i> ' + (data.message || '✓ Valid referral code');
                    } else {
                        refFeedback.style.color = '#ef4444';
                        refFeedback.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> ' + (data.message || '✕ Invalid referral code');
                    }
                })
                .catch(function() {
                    refFeedback.style.display = 'none';
                });
        }

        if (refInput) {
            refInput.addEventListener('input', function() {
                clearTimeout(checkTimeout);
                checkTimeout = setTimeout(checkReferralCode, 350);
            });
            refInput.addEventListener('blur', checkReferralCode);
            if (refInput.value.trim() !== '') {
                checkReferralCode();
            }
        }

        // reCAPTCHA v3 / Enterprise submit handler integration
        @if($recaptchaEnabled && !empty($recaptchaSiteKey))
            var loginForm = document.getElementById('landingLoginForm');
            var registerForm = document.getElementById('landingRegisterForm');

            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    var tokenInput = document.getElementById('landing_login_recaptcha_token');
                    if (tokenInput && typeof grecaptcha !== 'undefined') {
                        e.preventDefault();
                        grecaptcha.enterprise.ready(function() {
                            grecaptcha.enterprise.execute('{{ $recaptchaSiteKey }}', {action: 'login'}).then(function(token) {
                                tokenInput.value = token;
                                loginForm.submit();
                            });
                        });
                    }
                });
            }

            if (registerForm) {
                registerForm.addEventListener('submit', function(e) {
                    var tokenInput = document.getElementById('landing_register_recaptcha_token');
                    if (tokenInput && typeof grecaptcha !== 'undefined') {
                        e.preventDefault();
                        grecaptcha.enterprise.ready(function() {
                            grecaptcha.enterprise.execute('{{ $recaptchaSiteKey }}', {action: 'register'}).then(function(token) {
                                tokenInput.value = token;
                                registerForm.submit();
                            });
                        });
                    }
                });
            }
        @endif
    });
</script>
