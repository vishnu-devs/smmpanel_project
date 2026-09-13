<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('helpers.php'))) {
            require_once app_path('helpers.php');
        }
    }

    public function boot(): void
    {
        // Enforce Core Project License Module Integrity
        if (!class_exists(\App\Services\LicenseService::class)) {
            http_response_code(403);
            die("SYSTEM ERROR: Core Security License Module missing or tampered.");
        }

        // 0. Set default pagination style
        \Illuminate\Pagination\Paginator::defaultView('partials.pagination');

        // 1. Configure Custom Rate Limiters
        // Web Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('login', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('register', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('order', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('ticket', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('api', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });

        // Mobile API Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('mobile-login', function (\Illuminate\Http\Request $request) {
            $email = strtolower(trim((string)$request->input('email', '')));
            $key = $email ? sha1($email) . '|' . $request->ip() : $request->ip();
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($key);
        });

        \Illuminate\Support\Facades\RateLimiter::for('mobile-register', function (\Illuminate\Http\Request $request) {
            return [
                \Illuminate\Cache\RateLimiting\Limit::perMinute(3)->by($request->ip()),
                \Illuminate\Cache\RateLimiting\Limit::perHour(10)->by($request->ip()),
            ];
        });

        \Illuminate\Support\Facades\RateLimiter::for('mobile-google', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(10)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('mobile-orders', function (\Illuminate\Http\Request $request) {
            $token = $request->bearerToken() ?: $request->input('api_token') ?: $request->input('key');
            $userId = $request->user()?->id;
            if (!$userId && $token) {
                $user = \App\Models\User::where('api_key', $token)->first();
                $userId = $user?->id;
            }
            $key = $userId ? 'usr:' . $userId : 'ip:' . $request->ip();
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(30)->by($key);
        });

        \Illuminate\Support\Facades\RateLimiter::for('mobile-utr', function (\Illuminate\Http\Request $request) {
            $token = $request->bearerToken() ?: $request->input('api_token') ?: $request->input('key');
            $userId = $request->user()?->id;
            if (!$userId && $token) {
                $user = \App\Models\User::where('api_key', $token)->first();
                $userId = $user?->id;
            }
            $key = $userId ? 'usr:' . $userId : 'ip:' . $request->ip();
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($key);
        });

        \Illuminate\Support\Facades\RateLimiter::for('mobile-tickets', function (\Illuminate\Http\Request $request) {
            $token = $request->bearerToken() ?: $request->input('api_token') ?: $request->input('key');
            $userId = $request->user()?->id;
            if (!$userId && $token) {
                $user = \App\Models\User::where('api_key', $token)->first();
                $userId = $user?->id;
            }
            $key = $userId ? 'usr:' . $userId : 'ip:' . $request->ip();
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($key);
        });

        \Illuminate\Support\Facades\RateLimiter::for('reseller-api', function (\Illuminate\Http\Request $request) {
            $apiKey = $request->input('key');
            if ($apiKey) {
                $user = \App\Models\User::where('api_key', $apiKey)->where('status', 'active')->first();
                if ($user) {
                    return \Illuminate\Cache\RateLimiting\Limit::perMinute(180)->by('reseller:' . $user->id);
                }
            }
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(20)->by('reseller_ip:' . $request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('catalog', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });

        // 2. Dynamic SMTP Mail Config overrides
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                $host = \App\Models\Setting::get('mail_host');
                if ($host) {
                    config([
                        'mail.mailers.smtp.host' => $host,
                        'mail.mailers.smtp.port' => \App\Models\Setting::get('mail_port', 587),
                        'mail.mailers.smtp.username' => \App\Models\Setting::get('mail_username'),
                        'mail.mailers.smtp.password' => \App\Models\Setting::get('mail_password'),
                        'mail.mailers.smtp.encryption' => \App\Models\Setting::get('mail_encryption', 'tls'),
                        'mail.from.address' => \App\Models\Setting::get('mail_from_address', 'noreply@rishismm.com'),
                        'mail.from.name' => \App\Models\Setting::get('mail_from_name', \App\Models\Setting::get('site_name', 'RishiSMM')),
                    ]);
                }

                // Dynamic Google OAuth Injection
                config([
                    'services.google.client_id' => \App\Models\Setting::get('google_client_id'),
                    'services.google.client_secret' => \App\Models\Setting::get('google_client_secret'),
                    'services.google.redirect' => url('/auth/google/callback'),
                ]);
            }
        } catch (\Exception $e) {
            // Silence settings loading failures during initial migrations
        }
    }
}
