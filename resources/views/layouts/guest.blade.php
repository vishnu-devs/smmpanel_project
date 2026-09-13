<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ App\Models\Setting::getFaviconUrl() }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ App\Models\Setting::getFaviconUrl() }}">
    
    <title>@yield('title', App\Models\Setting::getSiteName())</title>
    
    <!-- Local FontAwesome CSS -->
    <link rel="stylesheet" href="{{ asset('css/all.min.css') }}">
    <!-- Custom Style CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ filemtime(public_path('css/style.css')) }}">
    
    @php
        $recaptchaEnabled = App\Models\Setting::get('recaptcha_status', 'disabled') === 'enabled';
        $recaptchaSiteKey = App\Models\Setting::get('recaptcha_site_key', '');
    @endphp
    @if($recaptchaEnabled && !empty($recaptchaSiteKey))
        <script src="https://www.google.com/recaptcha/enterprise.js?render={{ $recaptchaSiteKey }}" data-cfasync="false" async defer></script>
    @endif
    
    <style>
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 0;
            margin: 0;
            background: radial-gradient(circle at 10% 20%, rgba(220, 39, 67, 0.08) 0%, transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(124, 58, 237, 0.08) 0%, transparent 40%),
                        var(--bg-main, #0b0f19);
        }
        
        .auth-card {
            width: 100%;
            max-width: 480px;
            padding: 3rem 2.5rem;
            border-radius: var(--radius-lg);
            text-align: center;
        }
        
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 2rem;
        }
        
        .auth-header {
            margin-bottom: 2rem;
        }
        
        .auth-header h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 6px;
        }
        
        .auth-header p {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Modern Alert Banners */
        .alert {
            padding: 12px 16px;
            border-radius: var(--radius-md, 10px);
            margin-bottom: 1.5rem;
            font-size: 0.88rem;
            font-weight: 500;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            text-align: left;
            word-break: break-word;
        }
        .alert p {
            margin: 0;
        }
        .alert p + p {
            margin-top: 4px;
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.35);
            color: #fca5a5;
        }
        .alert-error i {
            color: #ef4444;
            font-size: 1.15rem;
            margin-top: 1px;
            flex-shrink: 0;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #6ee7b7;
        }
        .alert-success i {
            color: #10b981;
            font-size: 1.15rem;
            margin-top: 1px;
            flex-shrink: 0;
        }
        .alert-info {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.35);
            color: #93c5fd;
        }
        .alert-info i {
            color: #3b82f6;
            font-size: 1.15rem;
            margin-top: 1px;
            flex-shrink: 0;
        }
    </style>
    @yield('styles')
</head>
<body>

    @yield('content')

    @yield('scripts')
</body>
</html>
