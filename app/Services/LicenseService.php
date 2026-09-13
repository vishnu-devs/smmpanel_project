<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LicenseService
{
    public const MASTER_SECRET = 'RISHI_SMM_MASTER_SECRET_2026_VISHU_DEV';
    public const LICENSE_SERVER_API = 'https://license-smm.codebyvishu.in/public/index.php?action=api-verify';

    /**
     * Get normalized current domain name (e.g. rishismm.codebyvishu.in or demosmm.codebyvishu.in)
     */
    public static function getCurrentDomain(): string
    {
        $host = request()->getHost() ?: ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $host = strtolower(trim($host));
        return preg_replace('/^www\./i', '', $host);
    }

    /**
     * Generate HMAC SHA256 signature for domain and expiry timestamp
     */
    public static function generateSignature(string $domain, string $expiresAt): string
    {
        $data = strtolower(trim($domain)) . '|' . trim($expiresAt);
        return hash_hmac('sha256', $data, self::MASTER_SECRET);
    }

    /**
     * Core Interlock: Enforce license status or redirect/abort request
     */
    public static function enforceLicenseOrAbort(): void
    {
        // Allowed paths exempt from license lock
        $path = request()->getPathInfo();
        if (str_starts_with($path, '/license') || str_starts_with($path, '/uploads') || str_starts_with($path, '/images')) {
            return;
        }

        if (!self::isLicenseValid()) {
            if (request()->expectsJson() || request()->is('api/*')) {
                response()->json([
                    'error' => 'System Lock: This installation requires a valid 1-Year License Key bound to domain ' . request()->getHost() . '.',
                    'redirect' => url('/license'),
                ], 403)->send();
                exit;
            }

            header('Location: ' . url('/license'));
            exit;
        }
    }

    /**
     * Check if current panel installation has a valid, active, non-expired license for THIS domain.
     */
    public static function isLicenseValid(): bool
    {
        try {
            $currentDomain = self::getCurrentDomain();

            $savedKey = Setting::get('license_key', '');
            $savedDomain = Setting::get('license_domain', '');
            $savedExpires = Setting::get('license_expires_at', '');
            $savedSig = Setting::get('license_signature', '');

            if (empty($savedKey) || empty($savedDomain) || empty($savedExpires) || empty($savedSig)) {
                return false;
            }

            // 1. Anti-Cloning & Database Import Check: Domain must strictly match current domain
            if (strtolower($savedDomain) !== strtolower($currentDomain)) {
                Log::warning("License Violation: Database clone detected! License bound to '{$savedDomain}' but running on '{$currentDomain}'.");
                return false;
            }

            // 2. Expiry Date Check: Expiry timestamp must be in the future
            if (strtotime($savedExpires) < time()) {
                Log::warning("License Violation: License expired on {$savedExpires}.");
                return false;
            }

            // 3. HMAC Signature Check: Validate payload integrity
            $expectedSig = self::generateSignature($savedDomain, $savedExpires);
            if (!hash_equals($expectedSig, $savedSig)) {
                Log::warning("License Violation: License signature mismatch or tampered payload!");
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("License Check Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate / Verify a new License Key strictly via License Server API
     */
    public static function verifyAndActivateKey(string $key): array
    {
        $key = strtoupper(trim($key));
        if (empty($key)) {
            return ['success' => false, 'message' => 'Please enter a valid 1-Year License Key.'];
        }

        $currentDomain = self::getCurrentDomain();

        // 1. Send remote API verification request to https://license-smm.codebyvishu.in
        try {
            $apiUrl = env('LICENSE_SERVER_API_URL', self::LICENSE_SERVER_API);

            $response = Http::timeout(10)->post($apiUrl, [
                'license_key' => $key,
                'domain' => $currentDomain,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (!empty($data['valid']) && $data['valid'] === true) {
                    $expiresAt = $data['expires_at'] ?? '';
                    $receivedSig = $data['signature'] ?? '';
                    $serverDomain = strtolower(trim($data['domain'] ?? $currentDomain));

                    // Security Check 1: Ensure domain bound on License Server matches current domain
                    if ($serverDomain !== strtolower($currentDomain)) {
                        return [
                            'success' => false,
                            'message' => "Security Error: License Key is strictly bound to domain '{$serverDomain}', but this panel is running on '{$currentDomain}'.",
                        ];
                    }

                    // Security Check 2: Verify HMAC SHA256 Signature
                    $expectedSig = self::generateSignature($currentDomain, $expiresAt);
                    if (!hash_equals($expectedSig, $receivedSig)) {
                        return [
                            'success' => false,
                            'message' => "Security Error: License signature verification failed or payload signature was tampered.",
                        ];
                    }

                    // Store verified active license details in database settings
                    Setting::set('license_key', $key, 'system', 'string', 0);
                    Setting::set('license_domain', $currentDomain, 'system', 'string', 0);
                    Setting::set('license_expires_at', $expiresAt, 'system', 'string', 0);
                    Setting::set('license_signature', $receivedSig, 'system', 'string', 0);
                    Setting::set('license_status', 'active', 'system', 'string', 0);

                    return [
                        'success' => true,
                        'message' => "License Key verified and activated successfully for domain '{$currentDomain}'! Valid until {$expiresAt}.",
                        'data' => $data,
                    ];
                }

                // If License Server rejected the key
                return [
                    'success' => false,
                    'message' => $data['message'] ?? "Invalid License Key! This key was not issued for domain '{$currentDomain}' on the License Server.",
                ];
            } else {
                return [
                    'success' => false,
                    'message' => "Unable to connect to License Server (HTTP " . $response->status() . "). Please try again in a few moments.",
                ];
            }
        } catch (\Throwable $e) {
            Log::error("License Verification Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "License verification error: Could not verify key with License Server (" . $e->getMessage() . ").",
            ];
        }
    }
}
