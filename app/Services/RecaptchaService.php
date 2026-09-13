<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    /**
     * Verify Google reCAPTCHA token against Google siteverify API.
     *
     * @param string|null $token
     * @param string|null $action Expected action name (e.g. 'login', 'register')
     * @param string|null $ip Remote user IP address
     * @return bool
     */
    public static function verify(?string $token, ?string $action = null, ?string $ip = null): bool
    {
        $status = Setting::get('recaptcha_status', 'disabled');
        if ($status !== 'enabled') {
            return true;
        }

        $siteKey = Setting::get('recaptcha_site_key');
        $secretKey = Setting::get('recaptcha_secret_key');

        // If enabled but keys are not configured yet, allow request to prevent lockout
        if (empty($siteKey) || empty($secretKey)) {
            return true;
        }

        // Token must be present when enabled & keys configured
        if (empty($token)) {
            $isLocal = app()->environment('local') || in_array($ip, ['127.0.0.1', '::1'], true) || in_array(request()->getHost(), ['localhost', '127.0.0.1'], true);
            if ($isLocal) {
                Log::info('reCAPTCHA token was empty in local development environment; allowed for development.');
                return true;
            }
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            if (!$response->successful()) {
                Log::warning('reCAPTCHA verification HTTP failed', ['status' => $response->status()]);
                return false;
            }

            $data = $response->json();

            if (!isset($data['success']) || $data['success'] !== true) {
                Log::warning('reCAPTCHA verification returned unsuccessful', ['response' => $data]);
                $errorCodes = $data['error-codes'] ?? [];
                $isLocal = app()->environment('local') || in_array($ip, ['127.0.0.1', '::1'], true) || in_array(request()->getHost(), ['localhost', '127.0.0.1'], true);
                if ($isLocal && in_array('hostname-mismatch', $errorCodes, true)) {
                    Log::info('reCAPTCHA hostname-mismatch in local environment; allowed for development.');
                    return true;
                }
                return false;
            }

            // Verify action name if provided (case-insensitive)
            if ($action && isset($data['action'])) {
                if (strcasecmp($data['action'], $action) !== 0) {
                    Log::warning('reCAPTCHA action mismatch', ['expected' => $action, 'got' => $data['action']]);
                }
            }

            // If score is present (reCAPTCHA v3 / Enterprise), verify score threshold
            if (isset($data['score'])) {
                $minScore = (float) Setting::get('recaptcha_score_threshold', 0.5);
                if ((float) $data['score'] < $minScore) {
                    Log::warning('reCAPTCHA score too low', ['score' => $data['score'], 'min' => $minScore]);
                    return false;
                }
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception: ' . $e->getMessage());
            return false;
        }
    }
}
