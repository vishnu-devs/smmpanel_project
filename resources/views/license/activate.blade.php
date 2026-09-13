<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="default-src * 'unsafe-inline' 'unsafe-eval' data: blob:;">
    <title>License Verification - {{ \App\Models\Setting::getSiteName() }}</title>
    <link rel="shortcut icon" href="{{ \App\Models\Setting::getFaviconUrl() }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        :root {
            --bg-dark: #070b14;
            --card-bg: rgba(18, 25, 43, 0.92);
            --border-color: rgba(255, 255, 255, 0.12);
            --accent-purple: #6366f1;
            --accent-cyan: #06b6d4;
            --accent-green: #10b981;
            --accent-red: #ef4444;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: var(--bg-dark);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(99, 102, 241, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(6, 182, 212, 0.18) 0%, transparent 45%);
            background-attachment: fixed;
        }

        .license-card {
            background: var(--card-bg);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.7);
            width: 100%;
            max-width: 520px;
            overflow: hidden;
            animation: fadeIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-bg {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.18), rgba(6, 182, 212, 0.12));
            border-bottom: 1px solid var(--border-color);
            padding: 36px 28px 28px 28px;
            text-align: center;
        }

        .brand-logo {
            max-height: 52px;
            max-width: 220px;
            object-fit: contain;
            margin-bottom: 14px;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.3));
        }

        .brand-title {
            font-size: 1.4rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            margin-bottom: 4px;
        }

        .brand-sub {
            font-size: 0.85rem;
            color: #94a3b8;
            margin-bottom: 16px;
        }

        .domain-chip {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 50px;
            padding: 8px 18px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.88rem;
            color: #38bdf8;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .card-body-content {
            padding: 32px 28px;
        }

        .alert-custom {
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger-custom {
            background: rgba(239, 68, 68, 0.18);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }

        .alert-success-custom {
            background: rgba(16, 185, 129, 0.18);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 20px;
            width: 100%;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.35);
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.15);
        }

        .status-locked {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.35);
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.15);
        }

        .details-box {
            background: rgba(10, 15, 26, 0.7);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 18px 20px;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .details-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
        }

        .details-label {
            color: #94a3b8;
            font-weight: 500;
        }

        .details-val {
            font-weight: 700;
        }

        .val-domain { color: #38bdf8; }
        .val-expiry { color: #34d399; }
        .val-key { font-family: 'JetBrains Mono', monospace; color: #fbbf24; }

        .btn-gradient {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 24px;
            background: linear-gradient(135deg, var(--accent-purple), var(--accent-cyan));
            border: none;
            border-radius: 14px;
            color: #ffffff !important;
            font-weight: 700;
            font-size: 0.98rem;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.35);
        }

        .btn-gradient:hover {
            opacity: 0.94;
            transform: translateY(-2px);
            box-shadow: 0 14px 30px rgba(99, 102, 241, 0.5);
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            color: #cbd5e1;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            color: #64748b;
            font-size: 0.95rem;
        }

        .custom-input {
            width: 100%;
            background: rgba(10, 15, 26, 0.95);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 13px 16px 13px 44px;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.92rem;
            outline: none;
            transition: all 0.25s ease;
        }

        .custom-input:focus {
            border-color: var(--accent-purple);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
            background: rgba(10, 15, 26, 1);
        }

        .input-help {
            font-size: 0.78rem;
            color: #64748b;
            margin-top: 6px;
        }

        .footer-note {
            margin-top: 24px;
            padding-top: 18px;
            border-top: 1px solid var(--border-color);
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }

        .contact-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 4px 12px;
            background: rgba(99, 102, 241, 0.12);
            border: 1px solid rgba(99, 102, 241, 0.25);
            border-radius: 50px;
            color: #a5b4fc;
            font-size: 0.76rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="license-card">
        <!-- Header -->
        <div class="header-bg">
            <img src="{{ \App\Models\Setting::getLogoUrl() }}" alt="Logo" class="brand-logo" onerror="this.src='{{ asset('images/logo_smm.png') }}'">
            <div class="brand-title">{{ \App\Models\Setting::getSiteName() }}</div>
            <div class="brand-sub">1-Year Domain License Verification System</div>
            
            <div class="domain-chip">
                <i class="fa-solid fa-globe"></i>
                <span>{{ $licenseInfo['current_domain'] }}</span>
            </div>
        </div>

        <!-- Body -->
        <div class="card-body-content">
            @if(session('error'))
                <div class="alert-custom alert-danger-custom">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="alert-custom alert-success-custom">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($licenseInfo['is_valid'])
                <div class="status-badge status-active">
                    <i class="fa-solid fa-shield-check"></i> System Fully Licensed & Active
                </div>

                <div class="details-box">
                    <div class="details-row">
                        <span class="details-label">Licensed Domain:</span>
                        <span class="details-val val-domain">{{ $licenseInfo['domain'] }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">Expires On:</span>
                        <span class="details-val val-expiry">{{ date('d M Y, H:i', strtotime($licenseInfo['expires_at'])) }}</span>
                    </div>
                    <div class="details-row">
                        <span class="details-label">License Key:</span>
                        <span class="details-val val-key">{{ substr($licenseInfo['key'], 0, 16) }}...</span>
                    </div>
                </div>

                <a href="{{ route('home') }}" class="btn-gradient">
                    <span>Enter Panel Application</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @else
                <div class="status-badge status-locked">
                    <i class="fa-solid fa-lock"></i> Panel Locked - License Required
                </div>

                <p style="text-align: center; font-size: 0.84rem; color: #94a3b8; margin-bottom: 22px; line-height: 1.5;">
                    Please enter a valid 1-Year License Key issued for <strong style="color: #ffffff;">{{ $licenseInfo['current_domain'] }}</strong> to activate your panel.
                </p>

                <form action="{{ route('license.verify') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">1-Year License Key</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-key input-icon"></i>
                            <input type="text" name="license_key" class="custom-input" placeholder="RISHI-LIC-XXXX-XXXX-XXXX" value="{{ old('license_key') }}" required autofocus>
                        </div>
                        <div class="input-help">Format: <code>RISHI-LIC-8F9A-2B3C-4D5E</code></div>
                    </div>

                    <button type="submit" class="btn-gradient">
                        <i class="fa-solid fa-key"></i>
                        <span>Verify & Activate License</span>
                    </button>
                </form>

                <div class="footer-note">
                    <div>Need a license key for this domain?</div>
                    <div class="contact-badge">Contact Licensing Portal Admin</div>
                </div>
            @endif
        </div>
    </div>

</body>
</html>
