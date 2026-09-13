<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Encryption\DecryptException;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add schema columns if not already present
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'setting_group')) {
                $table->string('setting_group', 50)->default('general')->after('id');
            }
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type', 20)->default('string')->after('value');
            }
            if (!Schema::hasColumn('settings', 'is_secret')) {
                $table->boolean('is_secret')->default(0)->after('type');
            }
            if (!Schema::hasColumn('settings', 'label')) {
                $table->string('label', 150)->nullable()->after('is_secret');
            }
        });

        // Add index on setting_group if not exists
        try {
            Schema::table('settings', function (Blueprint $table) {
                $table->index('setting_group', 'idx_settings_group');
            });
        } catch (\Exception $e) {
            // Index already exists
        }

        // 2. Comprehensive metadata definitions for all known application settings
        $definitions = [
            // General
            'site_name' => ['group' => 'general', 'type' => 'string', 'secret' => 0, 'label' => 'Site Name'],
            'support_email' => ['group' => 'general', 'type' => 'string', 'secret' => 0, 'label' => 'Support Email'],
            'currency_symbol' => ['group' => 'general', 'type' => 'string', 'secret' => 0, 'label' => 'Currency Symbol'],
            'whatsapp_number' => ['group' => 'general', 'type' => 'string', 'secret' => 0, 'label' => 'WhatsApp Support Number'],

            // Security
            'force_2fa' => ['group' => 'security', 'type' => 'boolean', 'secret' => 0, 'label' => 'Force 2FA Verification'],
            'license_key' => ['group' => 'security', 'type' => 'string', 'secret' => 1, 'label' => 'System License Key'],

            // Platform
            'default_platform' => ['group' => 'platform', 'type' => 'string', 'secret' => 0, 'label' => 'Default Platform Filter'],
            'enabled_platforms' => ['group' => 'platform', 'type' => 'json', 'secret' => 0, 'label' => 'Enabled Platforms List'],
            'profit_margin' => ['group' => 'platform', 'type' => 'float', 'secret' => 0, 'label' => 'Global Profit Margin Percentage'],

            // Appearance
            'ticker_text' => ['group' => 'appearance', 'type' => 'string', 'secret' => 0, 'label' => 'Top Marquee Ticker Text'],
            'custom_css' => ['group' => 'appearance', 'type' => 'text', 'secret' => 0, 'label' => 'Custom Global CSS'],

            // Payment
            'payment_upi_id' => ['group' => 'payment', 'type' => 'string', 'secret' => 0, 'label' => 'Payment UPI ID'],
            'payment_upi_qr' => ['group' => 'payment', 'type' => 'string', 'secret' => 0, 'label' => 'Payment UPI QR Image URL'],
            'payment_bank_details' => ['group' => 'payment', 'type' => 'text', 'secret' => 0, 'label' => 'Bank Account Transfer Details'],
            'payment_webhook_token' => ['group' => 'payment', 'type' => 'string', 'secret' => 1, 'label' => 'Payment Automation Webhook Secret Token'],

            // Mail & SMTP
            'mail_host' => ['group' => 'mail', 'type' => 'string', 'secret' => 0, 'label' => 'SMTP Mail Host Server'],
            'mail_port' => ['group' => 'mail', 'type' => 'integer', 'secret' => 0, 'label' => 'SMTP Mail Server Port'],
            'mail_username' => ['group' => 'mail', 'type' => 'string', 'secret' => 0, 'label' => 'SMTP Mail Username'],
            'mail_password' => ['group' => 'mail', 'type' => 'string', 'secret' => 1, 'label' => 'SMTP Mail Account Password'],
            'mail_encryption' => ['group' => 'mail', 'type' => 'string', 'secret' => 0, 'label' => 'SMTP Encryption Protocol (tls/ssl/none)'],
            'mail_from_address' => ['group' => 'mail', 'type' => 'string', 'secret' => 0, 'label' => 'System Outbound Sender Email Address'],
            'mail_from_name' => ['group' => 'mail', 'type' => 'string', 'secret' => 0, 'label' => 'System Outbound Sender Display Name'],

            // OAuth
            'google_client_id' => ['group' => 'oauth', 'type' => 'string', 'secret' => 0, 'label' => 'Google OAuth 2.0 Client ID'],
            'google_client_secret' => ['group' => 'oauth', 'type' => 'string', 'secret' => 1, 'label' => 'Google OAuth 2.0 Client Secret'],
            'google_login_status' => ['group' => 'oauth', 'type' => 'boolean', 'secret' => 0, 'label' => 'Google One-Tap / OAuth Login Status'],

            // Deposit Bonus
            'deposit_bonus_status' => ['group' => 'bonus', 'type' => 'boolean', 'secret' => 0, 'label' => 'Deposit Bonus Reward System Status'],
            'bonus_t1_min' => ['group' => 'bonus', 'type' => 'float', 'secret' => 0, 'label' => 'Bonus Tier 1 Minimum Deposit Amount'],
            'bonus_t1_percent' => ['group' => 'bonus', 'type' => 'float', 'secret' => 0, 'label' => 'Bonus Tier 1 Percentage Reward'],
            'bonus_t2_min' => ['group' => 'bonus', 'type' => 'float', 'secret' => 0, 'label' => 'Bonus Tier 2 Minimum Deposit Amount'],
            'bonus_t2_percent' => ['group' => 'bonus', 'type' => 'float', 'secret' => 0, 'label' => 'Bonus Tier 2 Percentage Reward'],
            'bonus_t3_min' => ['group' => 'bonus', 'type' => 'float', 'secret' => 0, 'label' => 'Bonus Tier 3 Minimum Deposit Amount'],
            'bonus_t3_percent' => ['group' => 'bonus', 'type' => 'float', 'secret' => 0, 'label' => 'Bonus Tier 3 Percentage Reward'],

            // Popup
            'popup_status' => ['group' => 'popup', 'type' => 'boolean', 'secret' => 0, 'label' => 'Announcement Popup Status'],
            'popup_mode' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Display Mode'],
            'popup_title' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Heading Title'],
            'popup_image' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Banner Image URL'],
            'popup_content' => ['group' => 'popup', 'type' => 'text', 'secret' => 0, 'label' => 'Announcement Popup Body HTML Content'],
            'popup_btn1_text' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Primary Button Text'],
            'popup_btn1_link' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Primary Button Target URL'],
            'popup_btn2_text' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Secondary Button Text'],
            'popup_btn2_link' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Secondary Button Target URL'],
            'popup_frequency' => ['group' => 'popup', 'type' => 'string', 'secret' => 0, 'label' => 'Announcement Popup Display Frequency'],
        ];

        // 3. Backfill all existing rows with metadata and encrypt secrets
        $existingRows = DB::table('settings')->get();
        $existingKeys = [];

        foreach ($existingRows as $row) {
            $existingKeys[] = $row->key;
            $meta = $definitions[$row->key] ?? [
                'group' => 'general',
                'type' => 'string',
                'secret' => 0,
                'label' => ucwords(str_replace('_', ' ', $row->key)),
            ];

            $finalValue = $row->value;

            // If this is a secret key, ensure it is encrypted safely without double encryption
            if ($meta['secret'] === 1 && !empty($row->value)) {
                $isAlreadyEncrypted = false;
                try {
                    Crypt::decryptString($row->value);
                    $isAlreadyEncrypted = true;
                } catch (DecryptException $e) {
                    $isAlreadyEncrypted = false;
                }

                if (!$isAlreadyEncrypted) {
                    $finalValue = Crypt::encryptString($row->value);
                }
            }

            DB::table('settings')->where('id', $row->id)->update([
                'setting_group' => $meta['group'],
                'type' => $meta['type'],
                'is_secret' => $meta['secret'],
                'label' => $meta['label'],
                'value' => $finalValue,
                'updated_at' => now(),
            ]);
        }

        // 4. Invalidate persistent cache tag
        Cache::forget('app_site_settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Decrypt all secret fields back to clean plaintext before removing columns
        if (Schema::hasColumn('settings', 'is_secret')) {
            $secretRows = DB::table('settings')->where('is_secret', 1)->get();
            foreach ($secretRows as $row) {
                if (!empty($row->value)) {
                    try {
                        $decrypted = Crypt::decryptString($row->value);
                        DB::table('settings')->where('id', $row->id)->update([
                            'value' => $decrypted,
                        ]);
                    } catch (DecryptException $e) {
                        // Already plaintext or undecryptable with current APP_KEY
                    }
                }
            }
        }

        // 2. Drop index and extra columns
        Schema::table('settings', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_settings_group');
            } catch (\Exception $e) {}

            $columnsToDrop = [];
            if (Schema::hasColumn('settings', 'setting_group')) {
                $columnsToDrop[] = 'setting_group';
            }
            if (Schema::hasColumn('settings', 'type')) {
                $columnsToDrop[] = 'type';
            }
            if (Schema::hasColumn('settings', 'is_secret')) {
                $columnsToDrop[] = 'is_secret';
            }
            if (Schema::hasColumn('settings', 'label')) {
                $columnsToDrop[] = 'label';
            }

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // 3. Clear cache
        Cache::forget('app_site_settings');
    }
};
