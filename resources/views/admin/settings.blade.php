@extends('layouts.app')

@section('title', 'System Settings - RishiSMM')
@section('page_header', 'System Configuration')

@section('styles')
<style>
    /* Styling for settings tabs */
    .settings-tab-btn {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        padding: 12px 24px;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: var(--transition);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .settings-tab-btn:hover {
        background: rgba(255, 255, 255, 0.06);
        color: var(--text-primary);
    }
    .settings-tab-btn.active {
        background: var(--grad-insta);
        color: white;
        border-color: transparent;
        box-shadow: 0 4px 15px rgba(204, 35, 102, 0.25);
    }
    .settings-tab-panel {
        display: none;
        animation: fadeIn 0.4s ease;
    }
    .settings-tab-panel.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endsection

@section('content')
<div style="max-width: 1250px; margin: 0 auto;">

    <!-- Tab navigation headers -->
    <div class="settings-tabs" style="display: flex; gap: 10px; margin-bottom: 1.5rem; flex-wrap: wrap;">
        <button class="settings-tab-btn active" onclick="switchTab('general', event)"><i class="fa-solid fa-gears"></i> General Settings</button>
        <button class="settings-tab-btn" onclick="switchTab('security', event)"><i class="fa-solid fa-shield-halved"></i> Security & reCAPTCHA</button>
        <button class="settings-tab-btn" onclick="switchTab('payment', event)"><i class="fa-solid fa-qrcode"></i> Local UPI & Bank</button>
        <button class="settings-tab-btn" onclick="switchTab('smtp', event)"><i class="fa-solid fa-envelope"></i> SMTP Outbound Email</button>
        <button class="settings-tab-btn" onclick="switchTab('oauth', event)"><i class="fa-brands fa-google"></i> Google OAuth</button>
        <button class="settings-tab-btn" onclick="switchTab('popup', event)"><i class="fa-solid fa-bullhorn"></i> Announcement Popup</button>
    </div>

    <!-- Settings Editor Panel Block -->
    <div class="glass custom-card" style="margin-bottom: 2rem; padding: 2rem;">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
            @csrf

            <!-- 1. GENERAL SETTINGS TAB PANEL -->
            <div id="tab-general" class="settings-tab-panel active">
                <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-sliders text-gradient"></i> Core Branding & Configuration</h3>
                
                <div class="form-group">
                    <label for="set_name" class="form-label">Panel Name (Site Title)</label>
                    <input type="text" name="site_name" id="set_name" class="form-control" value="{{ $settings['site_name'] }}" required autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                </div>

                <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px dashed var(--border-color); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; color: var(--color-primary);"><i class="fa-solid fa-image" style="margin-right: 6px;"></i> Panel Logo Image</label>
                    <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <div style="width: 60px; height: 60px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); padding: 5px;">
                            <img src="{{ App\Models\Setting::getLogoUrl() }}" alt="Current Logo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <div style="flex: 1; min-width: 250px;">
                            <label class="form-label" style="font-size: 0.82rem;">Upload New Logo Image (PNG, JPG, WEBP, SVG)</label>
                            <input type="file" name="site_logo_file" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <label for="site_logo_url" class="form-label" style="font-size: 0.82rem;">OR Direct Logo Image URL</label>
                    <input type="text" name="site_logo" id="site_logo_url" class="form-control" value="{{ $settings['site_logo'] ?? '' }}" placeholder="https://example.com/logo.png" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                </div>

                <div class="form-group" style="background: rgba(255,255,255,0.02); border: 1px dashed var(--border-color); padding: 1.25rem; border-radius: var(--radius-md); margin-bottom: 1.25rem;">
                    <label class="form-label" style="font-weight: 700; color: var(--color-primary);"><i class="fa-solid fa-icons" style="margin-right: 6px;"></i> Favicon Icon (Browser Tab Icon)</label>
                    <div style="display: flex; gap: 15px; align-items: center; margin-bottom: 0.75rem; flex-wrap: wrap;">
                        <div style="width: 50px; height: 50px; border-radius: var(--radius-sm); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; background: rgba(0,0,0,0.3); padding: 5px;">
                            <img src="{{ App\Models\Setting::getFaviconUrl() }}" alt="Current Favicon" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <div style="flex: 1; min-width: 250px;">
                            <label class="form-label" style="font-size: 0.82rem;">Upload New Favicon Icon (PNG, ICO, WEBP)</label>
                            <input type="file" name="site_favicon_file" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <label for="site_favicon_url" class="form-label" style="font-size: 0.82rem;">OR Direct Favicon Image URL</label>
                    <input type="text" name="site_favicon" id="site_favicon_url" class="form-control" value="{{ $settings['site_favicon'] ?? '' }}" placeholder="https://example.com/favicon.ico" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                </div>

                <div class="form-group">
                    <label for="support_email" class="form-label">Support/Complaint Email Address</label>
                    <input type="email" name="support_email" id="support_email" class="form-control" value="{{ $settings['support_email'] }}" required placeholder="e.g. support@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourdomain.com' }}" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">This email will be displayed to users for support or complaints.</span>
                </div>

                <div class="form-group">
                    <label for="force_2fa" class="form-label">Global 2FA Security Level (Email OTP)</label>
                    <select name="force_2fa" id="force_2fa" class="form-control">
                        <option value="disabled" {{ $settings['force_2fa'] === 'disabled' ? 'selected' : '' }}>Disabled (Standard credentials login)</option>
                        <option value="enabled" {{ $settings['force_2fa'] === 'enabled' ? 'selected' : '' }}>Enabled (Force Email verification code for all users)</option>
                    </select>
                </div>

                <input type="hidden" name="currency_symbol" value="₹">

                <div class="form-group">
                    <label for="ticker_text" class="form-label">Scrolling Offer Ticker Text</label>
                    <textarea name="ticker_text" id="ticker_text" class="form-control" rows="2" placeholder="e.g. 🚀 Instant Start Services ⚡ 24/7 Support... RishiSMM Trusted by Agencies & Influencers 🟢 Get 1% Bonus on ₹1,000+ via PhonePe">{{ $settings['ticker_text'] ?? '' }}</textarea>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">This announcement/offer headline text will scroll at the top of the client dashboards. Leave empty to hide.</span>
                </div>

                <div class="form-group">
                    <label for="set_wa" class="form-label">WhatsApp Contact Number (No spaces)</label>
                    <input type="text" name="whatsapp_number" id="set_wa" class="form-control" value="{{ $settings['whatsapp_number'] }}" placeholder="e.g. 919999999999" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">Include country code, omit '+' or leading zeros.</span>
                </div>

                <div class="form-group">
                    <label for="set_wa_channel" class="form-label">
                        <i class="fa-brands fa-whatsapp text-gradient" style="color: #25d366;"></i> WhatsApp Channel Link (Follow & Updates)
                    </label>
                    <input type="url" name="whatsapp_channel_url" id="set_wa_channel" class="form-control" value="{{ $settings['whatsapp_channel_url'] ?? 'https://whatsapp.com/channel' }}" placeholder="https://whatsapp.com/channel/..." autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">Official WhatsApp Channel URL for announcements, coupons, and service updates.</span>
                </div>

                <div class="form-group">
                    <label for="set_profit_margin" class="form-label">
                        <i class="fa-solid fa-percent text-gradient"></i> Default Service Profit Margin (%)
                    </label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" name="profit_margin" id="set_profit_margin" class="form-control" value="{{ $settings['profit_margin'] ?? 20 }}" min="1" max="1000" step="any" style="max-width: 180px;" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                        <span style="font-weight: 700; color: var(--text-primary);">%</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">This margin percentage is used for calculating Net Profit on your admin dashboard and default markup on synced services.</span>
                </div>

                <div class="form-group">
                    <label for="set_order_delivery_bonus_percent" class="form-label">
                        <i class="fa-solid fa-truck-ramp-box text-gradient" style="color: var(--color-success);"></i> Order Delivery Bonus Buffer (%) <span class="badge badge-success" style="font-size: 0.7rem; margin-left: 6px;">Recommended: 3% - 5%</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="number" name="order_delivery_bonus_percent" id="set_order_delivery_bonus_percent" class="form-control" value="{{ $settings['order_delivery_bonus_percent'] ?? 3 }}" min="0" max="50" step="1" style="max-width: 180px;" autocomplete="off" data-1p-ignore data-bwignore="true" data-lpignore="true">
                        <span style="font-weight: 700; color: var(--text-primary);">% Extra Buffer</span>
                    </div>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">Sends an extra quantity percentage to upstream Provider APIs (at 0 extra cost to client). Prevents under-delivery complaints when provider bots drop 5-10%. Set 0 to disable.</span>
                </div>

                <div class="form-group" style="margin-top: 1.5rem; background: rgba(2, 132, 199, 0.05); border: 1px solid rgba(2, 132, 199, 0.3); padding: 1.25rem; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h4 style="font-weight: 700; color: var(--text-primary); margin: 0 0 4px; font-size: 1rem;">
                            <i class="fa-solid fa-share-nodes text-gradient" style="margin-right: 6px;"></i> Social Media Platforms Management
                        </h4>
                        <span style="font-size: 0.82rem; color: var(--text-secondary);">
                            Manage, add new social platforms (LinkedIn, Snapchat, Website Traffic, Pinterest, Reddit, etc.), toggle visibility, and configure default selections on a dedicated manager page.
                        </span>
                    </div>
                    <a href="{{ route('admin.platforms') }}" class="btn-gradient" style="padding: 8px 18px; font-size: 0.85rem; font-weight: 700; text-decoration: none; white-space: nowrap;">
                        <i class="fa-solid fa-arrow-right"></i> Open Social Platforms Manager
                    </a>
                </div>

                <div class="form-group" style="margin-top: 1.5rem;">
                    <label for="set_css" class="form-label">Custom Site Styles Injector (CSS)</label>
                    <textarea name="custom_css" id="set_css" class="form-control" rows="4" placeholder="/* Inject custom css codes directly */" style="font-family: monospace; font-size: 0.85rem;">{{ $settings['custom_css'] }}</textarea>
                </div>
            </div>

            <!-- 2. SECURITY & BOT PROTECTION TAB PANEL -->
            <div id="tab-security" class="settings-tab-panel">
                <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-shield-halved text-gradient"></i> Security & Bot Protection (reCAPTCHA v3)</h3>
                <p style="font-size: 0.8rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Configure bot protection, invisible reCAPTCHA v3 verification, and account security policies to shield your SMM panel against credential stuffing and automated spam attacks.
                </p>

                <!-- Google reCAPTCHA v3 Config Card -->
                <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.5rem; margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--text-primary); margin-bottom: 1rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-brands fa-google text-gradient"></i> Google reCAPTCHA v3 Bot Protection
                    </h4>
                    
                    <div class="form-group">
                        <label for="recaptcha_status" class="form-label" style="font-size: 0.82rem;">reCAPTCHA v3 Status</label>
                        <select name="recaptcha_status" id="recaptcha_status" class="form-control" style="max-width: 320px;">
                            <option value="disabled" {{ ($settings['recaptcha_status'] ?? 'disabled') === 'disabled' ? 'selected' : '' }}>Disabled (No Bot Verification)</option>
                            <option value="enabled" {{ ($settings['recaptcha_status'] ?? 'disabled') === 'enabled' ? 'selected' : '' }}>Enabled (Active Invisible Bot Defense)</option>
                        </select>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">When enabled, user authentication routes (Login, Registration, Password Reset) are protected seamlessly.</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 1.25rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="recaptcha_site_key" class="form-label" style="font-size: 0.82rem;">reCAPTCHA Site Key (Public)</label>
                            <input type="text" name="recaptcha_site_key" id="recaptcha_site_key" class="form-control" value="{{ $settings['recaptcha_site_key'] ?? '' }}" placeholder="e.g. 6Lcx..." style="padding: 8px 12px; font-size: 0.85rem; font-family: monospace;">
                            <span style="font-size: 0.73rem; color: var(--text-secondary); margin-top: 4px; display: block;">From your Google reCAPTCHA v3 console.</span>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="recaptcha_secret_key" class="form-label" style="font-size: 0.82rem;">reCAPTCHA Secret Key (AES-256 Encrypted)</label>
                            <input type="password" name="recaptcha_secret_key" id="recaptcha_secret_key" class="form-control" autocomplete="new-password" placeholder="{{ !empty($settings['has_recaptcha_secret_key']) ? '•••••••••••••••• (Leave blank to keep existing)' : 'Enter reCAPTCHA Secret Key' }}" style="padding: 8px 12px; font-size: 0.85rem; font-family: monospace;">
                            <span style="font-size: 0.73rem; color: var(--text-secondary); margin-top: 4px; display: block;">Leave blank to preserve existing encrypted secret.</span>
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 1.25rem; margin-bottom: 0;">
                        <label for="recaptcha_score_threshold" class="form-label" style="font-size: 0.82rem;">Bot Score Threshold (0.1 - 1.0)</label>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="number" name="recaptcha_score_threshold" id="recaptcha_score_threshold" class="form-control" value="{{ $settings['recaptcha_score_threshold'] ?? 0.5 }}" min="0.1" max="1.0" step="0.1" style="max-width: 150px; font-weight: 700;">
                            <span style="font-size: 0.8rem; color: var(--text-secondary);">Recommended default is <strong>0.5</strong>. Lower scores indicate automated bots and will be blocked.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. PAYMENT DETAILS TAB PANEL -->
            <div id="tab-payment" class="settings-tab-panel">
                <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-qrcode text-gradient"></i> Local UPI & Bank Details</h3>
                
                <div class="form-group">
                    <label for="set_upi" class="form-label">Business UPI ID Address</label>
                    <input type="text" name="payment_upi_id" id="set_upi" class="form-control" value="{{ $settings['payment_upi_id'] }}" placeholder="e.g. business@ybl">
                </div>

                <div class="form-group">
                    <label class="form-label">UPI QR Code</label>
                    
                    @if(!empty($settings['payment_upi_qr']))
                        <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 15px; background: rgba(255, 255, 255, 0.02); padding: 10px; border-radius: var(--radius-sm); max-width: max-content; border: 1px solid var(--border-color);">
                            <img src="{{ $settings['payment_upi_qr'] }}" alt="UPI QR" style="max-height: 80px; border-radius: var(--radius-xs); border: 1px solid var(--border-color);">
                            <span style="font-size: 0.8rem; color: var(--text-secondary);">Current QR Code Image loaded.</span>
                        </div>
                    @endif
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">Upload New QR Image File:</span>
                            <input type="file" name="payment_upi_qr_file" class="form-control" accept="image/*" style="padding: 7px 10px;">
                        </div>
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-secondary); display: block; margin-bottom: 4px;">Or paste Image URL directly:</span>
                            <input type="url" name="payment_upi_qr" id="set_qr" class="form-control" value="{{ $settings['payment_upi_qr'] }}" placeholder="https://rishismm.com/images/payment-qr.jpg">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="set_bank" class="form-label">Manual Bank Details Information</label>
                    <textarea name="payment_bank_details" id="set_bank" class="form-control" rows="5" placeholder="Enter bank name, account number, holder, and IFSC codes...">{{ $settings['payment_bank_details'] }}</textarea>
                </div>

                <!-- Deposit Bonus Tiers Section -->
                <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <h4 style="color: var(--text-primary); font-size: 1.05rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-gift text-gradient"></i> Deposit Bonus Tiers
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.82rem; line-height: 1.4; margin-bottom: 1.2rem;">
                        Automatically reward clients with extra wallet bonus balance when they deposit qualifying amounts.
                    </p>

                    <div class="form-group" style="margin-bottom: 1.2rem;">
                        <label for="deposit_bonus_status" class="form-label">Deposit Bonus System</label>
                        <select name="deposit_bonus_status" id="deposit_bonus_status" class="form-control">
                            <option value="enabled" {{ ($settings['deposit_bonus_status'] ?? 'enabled') === 'enabled' ? 'selected' : '' }}>Enabled (Apply bonuses on deposits)</option>
                            <option value="disabled" {{ ($settings['deposit_bonus_status'] ?? 'enabled') === 'disabled' ? 'selected' : '' }}>Disabled (No bonus credited)</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <!-- Tier 1 -->
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                            <strong style="color: var(--color-info); font-size: 0.85rem; text-transform: uppercase; display: block; margin-bottom: 10px;">Tier 1 Bonus</strong>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label class="form-label" style="font-size: 0.78rem;">Min Deposit (₹)</label>
                                <input type="number" name="bonus_t1_min" class="form-control" value="{{ $settings['bonus_t1_min'] ?? 100 }}" min="0" step="any">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.78rem;">Bonus Percentage (%)</label>
                                <input type="number" name="bonus_t1_percent" class="form-control" value="{{ $settings['bonus_t1_percent'] ?? 1 }}" min="0" step="any">
                            </div>
                        </div>

                        <!-- Tier 2 -->
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                            <strong style="color: var(--color-success); font-size: 0.85rem; text-transform: uppercase; display: block; margin-bottom: 10px;">Tier 2 Bonus</strong>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label class="form-label" style="font-size: 0.78rem;">Min Deposit (₹)</label>
                                <input type="number" name="bonus_t2_min" class="form-control" value="{{ $settings['bonus_t2_min'] ?? 1000 }}" min="0" step="any">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.78rem;">Bonus Percentage (%)</label>
                                <input type="number" name="bonus_t2_percent" class="form-control" value="{{ $settings['bonus_t2_percent'] ?? 2 }}" min="0" step="any">
                            </div>
                        </div>

                        <!-- Tier 3 -->
                        <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1rem;">
                            <strong style="color: #a855f7; font-size: 0.85rem; text-transform: uppercase; display: block; margin-bottom: 10px;">Tier 3 Bonus</strong>
                            <div class="form-group" style="margin-bottom: 10px;">
                                <label class="form-label" style="font-size: 0.78rem;">Min Deposit (₹)</label>
                                <input type="number" name="bonus_t3_min" class="form-control" value="{{ $settings['bonus_t3_min'] ?? 5000 }}" min="0" step="any">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <label class="form-label" style="font-size: 0.78rem;">Bonus Percentage (%)</label>
                                <input type="number" name="bonus_t3_percent" class="form-control" value="{{ $settings['bonus_t3_percent'] ?? 5 }}" min="0" step="any">
                            </div>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <h4 style="color: var(--text-primary); font-size: 1.05rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-robot text-gradient"></i> Automated UPI Payments (Auto-Approval)
                    </h4>
                    <p style="color: var(--text-secondary); font-size: 0.82rem; line-height: 1.4; margin-bottom: 1.2rem;">
                        To credit customer payments automatically, configure a notification forwarder app (e.g. <em>Webhook SMS Gateway</em> or <em>Notification Forwarder</em>) on the Android device receiving your business payment notifications or bank SMS.
                    </p>

                    <div class="form-group">
                        <label for="payment_webhook_token" class="form-label">Webhook Secret Token</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="password" name="payment_webhook_token" id="payment_webhook_token" class="form-control" autocomplete="new-password" placeholder="{{ !empty($settings['payment_webhook_token']) ? '•••••••••••••••• (Leave blank to keep existing)' : 'Enter new secret token' }}" style="font-family: monospace; font-size: 0.88rem;">
                            <button type="button" class="btn-outline" onclick="generateWebhookToken()" style="padding: 0 15px; font-size: 0.85rem; border-color: var(--border-color); white-space: nowrap; color: white; cursor: pointer;">
                                <i class="fa-solid fa-arrows-rotate"></i> Generate New Token
                            </button>
                        </div>
                        <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">This secret key protects your webhook endpoint. Leave blank to keep existing encrypted token.</span>
                    </div>

                    <div style="background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 1.25rem; margin-top: 1.2rem;">
                        <span style="font-size: 0.8rem; color: var(--text-secondary); display: block; margin-bottom: 8px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Setup Instructions:</span>
                        <ol style="margin: 0; padding-left: 18px; color: var(--text-secondary); font-size: 0.82rem; line-height: 1.6; display: flex; flex-direction: column; gap: 8px;">
                            <li><strong>Webhook URL to configure in the Android App:</strong><br>
                                <div style="display: flex; align-items: center; gap: 8px; margin-top: 4px;">
                                    <code style="background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: var(--radius-xs); color: #f43f5e; font-size: 0.82rem; font-family: monospace; word-break: break-all;">
                                        {{ route('payment.webhook.upi') }}
                                    </code>
                                    <button type="button" class="btn-outline" onclick="copyWebhookUrl()" style="padding: 3px 8px; font-size: 0.75rem; border-color: var(--border-color); color: white; white-space: nowrap; cursor: pointer;">
                                        Copy URL
                                    </button>
                                </div>
                            </li>
                            <li><strong>Custom Header in the Android App:</strong><br>
                                <span style="display: block; margin-top: 2px;">Header Name: <code style="color: var(--color-info);">X-Webhook-Token</code></span>
                            </li>
                            <li><strong>Parameters to Forward in POST Body (JSON):</strong><br>
                                <span style="display: block; margin-top: 2px;">• <code style="color: #cbd5e1;">body</code> or <code style="color: #cbd5e1;">message</code>: Raw bank SMS text</span>
                                <span style="display: block;">• <code style="color: #cbd5e1;">sender</code>: Bank SMS Sender ID</span>
                            </li>
                        </ol>
                    </div>
                </div>

                <script>
                    function generateWebhookToken() {
                        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                        let result = '';
                        for (let i = 0; i < 32; i++) {
                            result += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        const input = document.getElementById('payment_webhook_token');
                        input.type = 'text';
                        input.value = result;
                        alert("New Webhook Token generated! Click 'Save System Settings' at the bottom to save.");
                    }
                    function copyWebhookUrl() {
                        var tempInput = document.createElement("input");
                        tempInput.value = "{{ route('payment.webhook.upi') }}";
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        tempInput.setSelectionRange(0, 99999);
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(tempInput.value).catch(function() {
                                document.execCommand('copy');
                            });
                        } else {
                            document.execCommand('copy');
                        }
                        document.body.removeChild(tempInput);
                        alert("Webhook URL copied to clipboard!");
                    }
                </script>
            </div>

            <!-- 3. SMTP CONFIGURATIONS TAB PANEL -->
            <div id="tab-smtp" class="settings-tab-panel">
                <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-solid fa-envelope text-gradient"></i> SMTP Outbound Email Configuration</h3>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_host" class="form-label" style="font-size: 0.8rem;">SMTP Host Server</label>
                        <input type="text" name="mail_host" id="mail_host" class="form-control" value="{{ $settings['mail_host'] }}" placeholder="smtp.mailtrap.io" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_port" class="form-label" style="font-size: 0.8rem;">SMTP Port Number</label>
                        <input type="number" name="mail_port" id="mail_port" class="form-control" value="{{ $settings['mail_port'] }}" placeholder="587" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_encryption" class="form-label" style="font-size: 0.8rem;">Encryption Protocol</label>
                        <select name="mail_encryption" id="mail_encryption" class="form-control" style="padding: 8px 12px; font-size: 0.85rem; height: auto;">
                            <option value="tls" {{ $settings['mail_encryption'] === 'tls' ? 'selected' : '' }}>TLS</option>
                            <option value="ssl" {{ $settings['mail_encryption'] === 'ssl' ? 'selected' : '' }}>SSL</option>
                            <option value="none" {{ $settings['mail_encryption'] === 'none' ? 'selected' : '' }}>None</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 1rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_username" class="form-label" style="font-size: 0.8rem;">SMTP Username Credentials</label>
                        <input type="text" name="mail_username" id="mail_username" class="form-control" value="{{ $settings['mail_username'] }}" placeholder="user@domain.com" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_password" class="form-label" style="font-size: 0.8rem;">SMTP Account Password</label>
                        <input type="password" name="mail_password" id="mail_password" class="form-control" autocomplete="new-password" placeholder="{{ !empty($settings['has_mail_password']) ? '•••••••••••••••• (Leave blank to keep existing)' : 'Enter SMTP password' }}" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 1.5rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_from_address" class="form-label" style="font-size: 0.8rem;">Sender From Email Address</label>
                        <input type="email" name="mail_from_address" id="mail_from_address" class="form-control" value="{{ $settings['mail_from_address'] }}" placeholder="noreply@{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourdomain.com' }}" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mail_from_name" class="form-label" style="font-size: 0.8rem;">Sender Custom Display Name</label>
                        <input type="text" name="mail_from_name" id="mail_from_name" class="form-control" value="{{ $settings['mail_from_name'] }}" placeholder="{{ $settings['site_name'] }} Support" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                </div>

                <!-- SMTP Test Connection Section -->
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 15px;">
                    <h5 style="font-size: 0.85rem; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);"><i class="fa-solid fa-envelope-open-text text-gradient"></i> Run SMTP Connection Test</h5>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 12px;">Type a target recipient email to dispatch a test connection email directly using parameters entered above.</p>
                    <div style="display: flex; gap: 10px; max-width: 500px;">
                        <input type="email" name="test_email_recipient" placeholder="recipient@example.com" class="form-control" style="flex: 2; padding: 8px 12px; font-size: 0.85rem;">
                        <button type="submit" class="btn-outline" style="flex: 1; padding: 8px 12px; font-size: 0.85rem;">
                            Send Test Email
                        </button>
                    </div>
                </div>
            </div>

            <!-- 4. GOOGLE OAUTH TAB PANEL -->
            <div id="tab-oauth" class="settings-tab-panel">
                <h3 class="card-title" style="margin-bottom: 1.5rem;"><i class="fa-brands fa-google text-gradient"></i> Google OAuth Integration</h3>
                <p style="font-size: 0.75rem; color: var(--text-secondary); margin-bottom: 15px;">
                    Configure credentials to allow users to sign in and register instantly using Google accounts. Set the Google developers console redirect URI to: <code style="color: var(--color-info);">{{ url('/auth/google/callback') }}</code>
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="google_client_id" class="form-label" style="font-size: 0.8rem;">Google Client ID</label>
                        <input type="text" name="google_client_id" id="google_client_id" class="form-control" value="{{ $settings['google_client_id'] ?? '' }}" placeholder="Insert Google OAuth Client ID" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="google_client_secret" class="form-label" style="font-size: 0.8rem;">Google Client Secret</label>
                        <input type="password" name="google_client_secret" id="google_client_secret" class="form-control" autocomplete="new-password" placeholder="{{ !empty($settings['has_google_client_secret']) ? '•••••••••••••••• (Leave blank to keep existing)' : 'Insert Google OAuth Client Secret' }}" style="padding: 8px 12px; font-size: 0.85rem;">
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="google_login_status" class="form-label" style="font-size: 0.8rem;">Google Login Status</label>
                        <select name="google_login_status" id="google_login_status" class="form-control" style="padding: 8px 12px; font-size: 0.85rem; height: auto;">
                            <option value="disabled" {{ ($settings['google_login_status'] ?? 'disabled') === 'disabled' ? 'selected' : '' }}>Disabled (Hidden)</option>
                            <option value="enabled" {{ ($settings['google_login_status'] ?? 'disabled') === 'enabled' ? 'selected' : '' }}>Enabled (Visible)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 5. ANNOUNCEMENT POPUP MODAL TAB PANEL -->
            <div id="tab-popup" class="settings-tab-panel">
                <h3 class="card-title" style="margin-bottom: 0.5rem;"><i class="fa-solid fa-bullhorn text-gradient"></i> Welcome Announcement & Deals Popup Modal</h3>
                <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                    Display an interactive popup modal whenever customers open your website to promote deposit cashback, SMM panel selling (with/without hosting), and reseller programs.
                </p>

                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.25rem; margin-bottom: 1.25rem;">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="popup_status" class="form-label">Popup Status</label>
                        <select name="popup_status" id="popup_status" class="form-control">
                            <option value="enabled" {{ ($settings['popup_status'] ?? 'enabled') === 'enabled' ? 'selected' : '' }}>Enabled (Popup will show)</option>
                            <option value="disabled" {{ ($settings['popup_status'] ?? 'enabled') === 'disabled' ? 'selected' : '' }}>Disabled (Hidden)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="popup_mode" class="form-label">Display Mode <span class="text-danger">*</span></label>
                        <select name="popup_mode" id="popup_mode" class="form-control" style="border-color: rgba(220, 39, 67, 0.5);">
                            <option value="content_only" {{ ($settings['popup_mode'] ?? '') === 'content_only' ? 'selected' : '' }}>📝 Text & HTML Content Only</option>
                            <option value="image_only" {{ ($settings['popup_mode'] ?? '') === 'image_only' ? 'selected' : '' }}>🖼️ Image / Poster Banner Only</option>
                            <option value="image_and_content" {{ ($settings['popup_mode'] ?? 'image_and_content') === 'image_and_content' ? 'selected' : '' }}>🌟 Both Image & Content (Combined)</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="popup_frequency" class="form-label">Display Frequency</label>
                        <select name="popup_frequency" id="popup_frequency" class="form-control">
                            <option value="once_per_session" {{ ($settings['popup_frequency'] ?? 'once_per_session') === 'once_per_session' ? 'selected' : '' }}>Once Per Session (Recommended)</option>
                            <option value="once_per_day" {{ ($settings['popup_frequency'] ?? '') === 'once_per_day' ? 'selected' : '' }}>Once Per Day (24 Hours)</option>
                            <option value="always" {{ ($settings['popup_frequency'] ?? '') === 'always' ? 'selected' : '' }}>Always (Every Page Refresh)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="popup_title" class="form-label">Popup Modal Title</label>
                    <input type="text" name="popup_title" id="popup_title" class="form-control" value="{{ $settings['popup_title'] ?? '🔥 Special Deposit Offers & Launch Your Own SMM Panel!' }}" placeholder="e.g. Special Offers & Announcements">
                </div>

                <!-- Banner Image Upload & URL -->
                <div class="form-group">
                    <label class="form-label">Promotional Banner Image (For Image Mode or Combined Mode)</label>
                    @if(!empty($settings['popup_image']))
                        <div style="margin-bottom: 12px; display: flex; align-items: center; gap: 15px; background: rgba(255,255,255,0.02); padding: 10px 14px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                            <img src="{{ $settings['popup_image'] }}" alt="Popup Banner" style="max-height: 80px; border-radius: 6px; border: 1px solid var(--border-color);">
                            <div>
                                <strong style="display: block; font-size: 0.85rem; color: var(--text-primary);">Current Active Banner</strong>
                                <label style="font-size: 0.8rem; color: #ef4444; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-top: 4px;">
                                    <input type="checkbox" name="remove_popup_image" value="1" style="accent-color: #ef4444;"> Remove this banner image
                                </label>
                            </div>
                        </div>
                    @endif
                    <input type="file" name="popup_image_file" class="form-control" accept="image/*">
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">Upload your promotional poster / banner (JPG, PNG, WEBP). Jab aap Image Only mode select karenge to yahi poster popup me dikhega.</span>
                </div>

                <!-- Rich Popup HTML Content -->
                <div class="form-group">
                    <label for="popup_content" class="form-label">Popup Body Content (HTML / Text - For Content Mode or Combined Mode)</label>
                    <textarea name="popup_content" id="popup_content" rows="9" class="form-control" style="font-family: monospace; font-size: 0.88rem;" placeholder="Enter popup message HTML content...">{{ $settings['popup_content'] ?: '<div style="display: flex; flex-direction: column; gap: 10px; text-align: left;">
    <div style="background: rgba(220, 39, 67, 0.08); border: 1px solid rgba(220, 39, 67, 0.25); border-radius: 8px; padding: 10px 14px;">
        <strong style="color: var(--color-primary); display: block; margin-bottom: 2px;">🎁 VIP Deposit Cashback Active:</strong>
        <span style="font-size: 0.88rem; color: var(--text-secondary);">Get up to <strong>5% Extra Bonus Balance</strong> automatically credited on UPI deposits above ₹100!</span>
    </div>

    <div style="background: rgba(16, 185, 129, 0.08); border: 1px solid rgba(16, 185, 129, 0.25); border-radius: 8px; padding: 10px 14px;">
        <strong style="color: #10b981; display: block; margin-bottom: 2px;">💼 Start Your Own SMM Panel Business:</strong>
        <span style="font-size: 0.88rem; color: var(--text-secondary);">Want to sell SMM services under your own brand? We provide <strong>Ready-to-use SMM Panels</strong> with Domain & High-Speed Hosting or Without Domain/Hosting, with 24/7 dedicated support & wholesale root API pricing.</span>
    </div>

    <div style="font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-top: 4px;">
        ⚡ 250+ Instant Delivery Services | 🔒 100% Safe & Secure | 💬 24/7 Live Support
    </div>
</div>' }}</textarea>
                    <span style="font-size: 0.75rem; color: var(--text-secondary); margin-top: 4px; display: block;">Aap yaha par deposit offers, panel selling details, hosting ya custom text kabhi bhi change kar sakte hain.</span>
                </div>

                <!-- Action Button 1 (WhatsApp / Panel Inquiry) -->
                <div style="background: rgba(37, 211, 102, 0.05); border: 1px solid rgba(37, 211, 102, 0.2); border-radius: var(--radius-md); padding: 1.25rem; margin-bottom: 1.25rem;">
                    <strong style="color: #25d366; font-size: 0.92rem; display: block; margin-bottom: 10px;">
                        <i class="fa-brands fa-whatsapp"></i> Primary Action Button (e.g. Buy Panel on WhatsApp)
                    </strong>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.8rem;">Button 1 Text</label>
                            <input type="text" name="popup_btn1_text" class="form-control" value="{{ $settings['popup_btn1_text'] ?? '💬 Get Your Own SMM Panel' }}">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.8rem;">Button 1 Target URL / Link</label>
                            <input type="text" name="popup_btn1_link" class="form-control" value="{{ $settings['popup_btn1_link'] ?? '' }}" placeholder="https://wa.me/91XXXXXXXXXX or /resellers">
                        </div>
                    </div>
                </div>

                <!-- Action Button 2 (Add Funds / Wallet Bonus) -->
                <div style="background: rgba(220, 39, 67, 0.05); border: 1px solid rgba(220, 39, 67, 0.2); border-radius: var(--radius-md); padding: 1.25rem;">
                    <strong style="color: var(--color-primary); font-size: 0.92rem; display: block; margin-bottom: 10px;">
                        <i class="fa-solid fa-wallet"></i> Secondary Action Button (e.g. Add Funds)
                    </strong>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.8rem;">Button 2 Text</label>
                            <input type="text" name="popup_btn2_text" class="form-control" value="{{ $settings['popup_btn2_text'] ?? '💰 Add Funds & Get Bonus' }}">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label class="form-label" style="font-size: 0.8rem;">Button 2 Target URL / Link</label>
                            <input type="text" name="popup_btn2_link" class="form-control" value="{{ $settings['popup_btn2_link'] ?? '/add-funds' }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Global Action Submit Button -->
            <button type="submit" class="btn-gradient" style="width: 100%; padding: 14px; font-weight: bold; margin-top: 2rem;">
                Save System Variables
            </button>
        </form>
    </div>

    <!-- Manual Deposits Verification Block -->
    <div class="glass custom-card">
        <h3 class="card-title"><i class="fa-solid fa-wallet"></i> Verify Manual Deposits ({{ $pendingDeposits->count() }})</h3>
        <p style="color: var(--text-secondary); font-size: 0.85rem; margin-bottom: 1.5rem;">
            Compare reference UTRs with your UPI statements. Approving credits the user balance instantly.
        </p>

        @if($pendingDeposits->count() > 0)
            <div class="table-responsive">
                <table class="custom-table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>UTR / Reference</th>
                            <th>Amount</th>
                            <th>Remarks & Screenshot</th>
                            <th style="text-align: right; width: 420px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingDeposits as $pay)
                            <tr>
                                <td>
                                    <div style="font-weight: 600;">{{ $pay->user ? $pay->user->name : 'Deleted user' }}</div>
                                    <div style="font-size: 0.7rem; color: var(--text-secondary);">Bal: ₹{{ number_format($pay->user ? $pay->user->balance : 0, 2) }}</div>
                                </td>
                                <td style="font-family: monospace; font-weight: bold; color: var(--color-info);">
                                    {{ $pay->payment_id }}
                                </td>
                                <td style="font-weight: 700; color: var(--color-success);">
                                    ₹{{ number_format($pay->amount, 2) }}
                                </td>
                                <td style="font-size: 0.75rem; color: var(--text-secondary);">
                                    <div>{{ $pay->notes ?? '-' }}</div>
                                    @if($pay->screenshot)
                                        <div style="margin-top: 5px;">
                                            <a href="{{ route('admin.transactions.screenshot', $pay->id) }}" target="_blank" style="color: var(--color-info); text-decoration: none; font-weight: 700;">
                                                <i class="fa-solid fa-image"></i> View Receipt Screenshot
                                            </a>
                                        </div>
                                    @endif
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 10px; justify-content: flex-end; align-items: center;">
                                        <!-- Approve manual transaction -->
                                        <form action="{{ route('admin.settings.update') }}" method="POST" onsubmit="return confirm('Approve payment UTR and credit user balance?')" style="display: inline-flex; align-items: center; gap: 5px;">
                                            @csrf
                                            <input type="hidden" name="approve_txn_id" value="{{ $pay->id }}">
                                            <input type="hidden" name="site_name" value="{{ $settings['site_name'] }}">
                                            <input type="hidden" name="currency_symbol" value="₹">
                                            <button type="submit" class="btn-gradient" style="padding: 6px 12px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-emerald); white-space: nowrap; margin: 0;">
                                                Approve
                                            </button>
                                        </form>
                                        
                                        <!-- Reject manual transaction -->
                                        <form action="{{ route('admin.settings.update') }}" method="POST" onsubmit="return confirm('Reject reference UTR?')" style="display: inline-flex; align-items: center; gap: 5px;">
                                            @csrf
                                            <input type="hidden" name="reject_txn_id" value="{{ $pay->id }}">
                                            <input type="hidden" name="site_name" value="{{ $settings['site_name'] }}">
                                            <input type="hidden" name="currency_symbol" value="₹">
                                            <button type="submit" class="btn-gradient" style="padding: 6px 12px; font-size: 0.75rem; border-radius: var(--radius-sm); background: var(--grad-danger); white-space: nowrap; margin: 0;">
                                                Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div style="text-align: center; color: var(--text-muted); padding: 4rem;">
                <i class="fa-solid fa-circle-check" style="font-size: 3rem; margin-bottom: 1.5rem; color: var(--color-success); opacity: 0.7;"></i>
                <p>No manual deposits waiting for approval.</p>
            </div>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    function switchTab(tabId, event) {
        if (event) event.preventDefault();
        
        // Hide all panels
        document.querySelectorAll('.settings-tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.settings-tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected panel
        document.getElementById('tab-' + tabId).classList.add('active');
        
        // Set target button active
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('active');
        } else {
            // Fallback to find active button
            const buttons = document.querySelectorAll('.settings-tab-btn');
            buttons.forEach(btn => {
                if (btn.getAttribute('onclick').includes(tabId)) {
                    btn.classList.add('active');
                }
            });
        }
    }
</script>
@endsection
