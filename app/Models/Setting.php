<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contracts\Encryption\DecryptException;

class Setting extends Model
{
    protected $fillable = [
        'setting_group',
        'key',
        'value',
        'type',
        'is_secret',
        'label',
    ];

    /**
     * Standard list of known secret keys for automatic encryption handling
     */
    public const KNOWN_SECRETS = [
        'mail_password',
        'google_client_secret',
        'payment_webhook_token',
        'license_key',
        'recaptcha_secret_key',
    ];

    /**
     * In-memory cache for current PHP request execution
     */
    private static $cachedSettings = null;

    /**
     * Load raw settings into cache.
     * PERSISTENT CACHE CONTAINS ONLY RAW/CIPHERTEXT VALUES — NEVER PLAINTEXT SECRETS.
     */
    private static function loadSettings(): void
    {
        if (self::$cachedSettings === null) {
            try {
                self::$cachedSettings = Cache::remember('app_site_settings', 3600, function () {
                    return self::all()->keyBy('key')->map(function ($item) {
                        return [
                            'value' => $item->value, // Raw ciphertext in persistent cache
                            'setting_group' => $item->setting_group ?? 'general',
                            'type' => $item->type ?? 'string',
                            'is_secret' => (int)($item->is_secret ?? (in_array($item->key, self::KNOWN_SECRETS, true) ? 1 : 0)),
                            'label' => $item->label ?? null,
                        ];
                    })->toArray();
                });
            } catch (\Exception $e) {
                try {
                    self::$cachedSettings = self::all()->keyBy('key')->map(function ($item) {
                        return [
                            'value' => $item->value,
                            'setting_group' => $item->setting_group ?? 'general',
                            'type' => $item->type ?? 'string',
                            'is_secret' => (int)($item->is_secret ?? (in_array($item->key, self::KNOWN_SECRETS, true) ? 1 : 0)),
                            'label' => $item->label ?? null,
                        ];
                    })->toArray();
                } catch (\Exception $ex) {
                    self::$cachedSettings = [];
                }
            }
        }
    }

    /**
     * Retrieve setting value.
     * Secrets are decrypted strictly on-the-fly within active server-side PHP runtime.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        self::loadSettings();

        if (!isset(self::$cachedSettings[$key])) {
            return $default;
        }

        $item = self::$cachedSettings[$key];
        $rawValue = $item['value'] ?? null;

        if ($rawValue === null) {
            return $default;
        }

        // On-the-fly decryption for secret credentials
        $isSecret = ($item['is_secret'] ?? 0) === 1 || in_array($key, self::KNOWN_SECRETS, true);
        if ($isSecret && !empty($rawValue)) {
            try {
                return Crypt::decryptString($rawValue);
            } catch (DecryptException $e) {
                // Fallback if stored as legacy plaintext
                return $rawValue;
            }
        }

        return $rawValue;
    }

    /**
     * Store or update a setting.
     * Automatically encrypts secrets before writing to MySQL database.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $group
     * @param string|null $type
     * @param int|null $isSecret
     * @return static
     */
    public static function set($key, $value, ?string $group = null, ?string $type = null, ?int $isSecret = null)
    {
        $existing = self::where('key', $key)->first();

        $secretFlag = $isSecret !== null 
            ? $isSecret 
            : ($existing ? (int)$existing->is_secret : (in_array($key, self::KNOWN_SECRETS, true) ? 1 : 0));

        $formattedValue = is_array($value) || is_object($value) ? json_encode($value) : $value;

        // Automatically encrypt secret values if non-empty
        if ($secretFlag === 1 && !empty($formattedValue)) {
            if (!self::isEncrypted($formattedValue)) {
                $formattedValue = Crypt::encryptString((string)$formattedValue);
            }
        }

        $attributes = ['key' => $key];
        $values = [
            'value' => $formattedValue,
            'is_secret' => $secretFlag,
        ];

        if ($group !== null) {
            $values['setting_group'] = $group;
        }
        if ($type !== null) {
            $values['type'] = $type;
        }

        $setting = self::updateOrCreate($attributes, $values);

        // Invalidate persistent cache and local memory cache
        self::$cachedSettings = null;
        try {
            Cache::forget('app_site_settings');
        } catch (\Exception $e) {}

        return $setting;
    }

    /**
     * Get all settings in a group with secrets MASKED by default for fail-safe UI presentation.
     *
     * @param string $groupName
     * @return array
     */
    public static function getGroup(string $groupName): array
    {
        self::loadSettings();
        $result = [];

        foreach (self::$cachedSettings as $key => $meta) {
            if (($meta['setting_group'] ?? 'general') === $groupName) {
                if (($meta['is_secret'] ?? 0) === 1 || in_array($key, self::KNOWN_SECRETS, true)) {
                    $result[$key] = !empty($meta['value']) ? '••••••••••••••••' : '';
                } else {
                    $result[$key] = $meta['value'];
                }
            }
        }

        return $result;
    }

    /**
     * Get all settings in a group with decrypted plaintext secrets.
     * INTERNAL ONLY: For trusted background PHP server processes.
     *
     * @param string $groupName
     * @return array
     */
    public static function getGroupDecrypted(string $groupName): array
    {
        self::loadSettings();
        $result = [];

        foreach (self::$cachedSettings as $key => $meta) {
            if (($meta['setting_group'] ?? 'general') === $groupName) {
                $result[$key] = self::get($key);
            }
        }

        return $result;
    }

    /**
     * Check if a string is already encrypted using active APP_KEY.
     *
     * @param string|null $value
     * @return bool
     */
    public static function isEncrypted(?string $value): bool
    {
        if (empty($value) || !is_string($value)) {
            return false;
        }

        try {
            Crypt::decryptString($value);
            return true;
        } catch (DecryptException $e) {
            return false;
        }
    }

    /**
     * Dispatch email notification using Blade template with high deliverability headers & dynamic SMTP configuration.
     */
    public static function sendEmail($to, $subject, $title, $messageBody, $options = []): bool
    {
        $to = trim((string)$to);
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            Log::warning("Email dispatch skipped due to invalid recipient email: '{$to}'");
            return false;
        }

        try {
            // Dynamically load real SMTP settings from database if configured
            $host = self::get('mail_host');
            $username = self::get('mail_username');
            $rawPassword = self::get('mail_password');

            if (!empty($host) && !empty($username)) {
                $port = (int) self::get('mail_port', 465);
                $encryption = self::get('mail_encryption', 'ssl');
                $fromAddress = self::get('mail_from_address', $username);
                $fromName = self::get('mail_from_name', self::get('site_name', 'RishiSMM'));

                $password = '';
                if (!empty($rawPassword)) {
                    try {
                        $password = \Illuminate\Support\Facades\Crypt::decryptString($rawPassword);
                    } catch (\Exception $e) {
                        $password = $rawPassword;
                    }
                }

                config([
                    'mail.default' => 'smtp',
                    'mail.mailers.smtp.transport' => 'smtp',
                    'mail.mailers.smtp.host' => $host,
                    'mail.mailers.smtp.port' => $port,
                    'mail.mailers.smtp.username' => $username,
                    'mail.mailers.smtp.password' => $password,
                    'mail.mailers.smtp.encryption' => $encryption,
                    'mail.from.address' => $fromAddress,
                    'mail.from.name' => $fromName,
                ]);

                app()->get('mail.manager')->forgetMailers();
            }

            $unsubscribeUrl = $options['unsubscribeUrl'] ?? null;

            Mail::send('emails.notification', [
                'title' => $title,
                'messageBody' => $messageBody,
                'otp' => $options['otp'] ?? null,
                'btnText' => $options['btnText'] ?? null,
                'btnUrl' => $options['btnUrl'] ?? null,
                'unsubscribeUrl' => $unsubscribeUrl,
            ], function ($message) use ($to, $subject, $unsubscribeUrl) {
                $message->to($to)->subject($subject);

                if (!empty($unsubscribeUrl)) {
                    $headers = $message->getHeaders();
                    $headers->addTextHeader('List-Unsubscribe', "<{$unsubscribeUrl}>");
                    $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
                }
            });

            return true;
        } catch (\Exception $e) {
            Log::error("Email dispatch failed to {$to}: " . $e->getMessage());
            return false;
        }
    }

    public static function getLogoUrl(): string
    {
        $logo = self::get('site_logo');
        if ($logo) {
            return (str_starts_with($logo, 'http://') || str_starts_with($logo, 'https://') || str_starts_with($logo, '/')) ? $logo : asset($logo);
        }
        return asset('images/logo_smm.png');
    }

    public static function getFaviconUrl(): string
    {
        $favicon = self::get('site_favicon');
        if ($favicon) {
            return (str_starts_with($favicon, 'http://') || str_starts_with($favicon, 'https://') || str_starts_with($favicon, '/')) ? $favicon : asset($favicon);
        }
        return asset('images/logo_smm.png');
    }

    public static function getSiteName(): string
    {
        return self::get('site_name', 'RishiSMM');
    }
}
