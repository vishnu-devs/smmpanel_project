<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Notification' }} - {{ App\Models\Setting::get('site_name', 'RishiSMM') }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #0c091a;
            color: #ffffff;
            -webkit-font-smoothing: antialiased;
        }

        .wrapper {
            width: 100%;
            background-color: #0c091a;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #130f26;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #2a244d;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        }

        .header {
            padding: 24px 30px;
            text-align: center;
            border-bottom: 1px solid #2a244d;
            background-color: #130f26;
        }

        .logo-img {
            max-width: 150px;
            height: auto;
            display: block;
            margin: 0 auto;
            border: 0;
            outline: none;
            text-decoration: none;
        }

        .logo-fallback {
            font-size: 24px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 0.5px;
            text-decoration: none;
            display: inline-block;
        }

        .logo-fallback span {
            color: #ff335c;
        }

        .content {
            padding: 40px 30px;
            background-color: #130f26;
        }

        .title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #ffffff;
        }

        .text {
            font-size: 16px;
            line-height: 1.6;
            color: #c5c2d9;
            margin-bottom: 30px;
        }

        .btn-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            padding: 14px 35px;
            font-size: 16px;
            font-weight: 600;
            color: #ffffff !important;
            text-decoration: none;
            background: linear-gradient(135deg, #dc2743 0%, #cc2366 100%);
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(220, 39, 67, 0.3);
            transition: all 0.3s ease;
        }

        .otp-box {
            display: block;
            width: fit-content;
            margin: 20px auto;
            padding: 15px 40px;
            background: rgba(220, 39, 67, 0.1);
            border: 2px dashed #dc2743;
            border-radius: 8px;
            font-size: 32px;
            font-weight: 800;
            letter-spacing: 6px;
            color: #ff335c;
            text-align: center;
        }

        .footer {
            padding: 30px;
            text-align: center;
            background-color: #0c091a;
            border-top: 1px solid #2a244d;
            font-size: 13px;
            color: #6c678a;
        }

        .footer a {
            color: #ff335c;
            text-decoration: none;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header section with Clean Brand Typography -->
            <div class="header">
                <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td align="center">
                            @php
                                $siteName = App\Models\Setting::get('site_name', 'RishiSMM');
                            @endphp
                            <a href="{{ config('app.url') }}" target="_blank" style="text-decoration: none; display: inline-block;">
                                <div style="font-size: 26px; font-weight: 900; color: #ffffff; letter-spacing: 0.5px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                    Rishi<span style="color: #ff335c;">SMM</span>
                                </div>
                            </a>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Content section -->
            <div class="content">
                @if(isset($title))
                    <div class="title">{{ $title }}</div>
                @endif

                <div class="text">
                    {!! $messageBody !!}
                </div>

                @if(isset($otp))
                    <div class="otp-box">
                        {{ $otp }}
                    </div>
                @endif

                @if(isset($btnText) && isset($btnUrl))
                    <div class="btn-container">
                        <a href="{{ $btnUrl }}" class="btn" target="_blank">{{ $btnText }}</a>
                    </div>
                @endif
            </div>

            <!-- Footer section -->
            <div class="footer">
                <p>You received this email because you are registered on
                    {{ App\Models\Setting::get('site_name', 'RishiSMM') }}.
                </p>
                @php
                    $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourdomain.com';
                    $defaultEmail = 'support@' . $domain;
                @endphp
                <p>For support or inquiries, email us at <a
                        href="mailto:{{ App\Models\Setting::get('support_email', $defaultEmail) }}">{{
                        App\Models\Setting::get('support_email', $defaultEmail) }}</a></p>
                @if(isset($unsubscribeUrl) && !empty($unsubscribeUrl))
                    <p style="margin-top: 15px; font-size: 12px; color: #8c87ab;">
                        Don't want to receive weekly balance reminder emails? 
                        <a href="{{ $unsubscribeUrl }}" target="_blank" style="color: #ff335c; text-decoration: underline;">Unsubscribe here</a>
                    </p>
                @endif
                <p>&copy; {{ date('Y') }} All rights reserved.</p>
            </div>
        </div>
    </div>
</body>

</html>