<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Core Settings
        Setting::set('site_name', 'RishiSMM');
        Setting::set('currency_symbol', '₹');
        Setting::set('whatsapp_number', '919999999999');
        Setting::set('payment_upi_id', 'rishismm@upi');
        Setting::set('payment_upi_qr', '');
        Setting::set('payment_bank_details', "Bank: HDFC Bank\nAccount: 50100123456789\nIFSC: HDFC0000123\nHolder: RishiSMM Solutions");
        
        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'yourdomain.com';
        Setting::set('support_email', 'support@' . $domain);
        Setting::set('usd_to_inr_rate', '83.00');
        Setting::set('ticker_text', '🚀 Instant Start Services ⚡ 24/7 Support... RishiSMM Trusted by Agencies & Influencers 🟢 Get 1% Bonus on ₹1,000+ via PhonePe (Instant Bonus)');

        // 2. Seed Default Admin Account
        User::create([
            'name' => 'RishiSMM Admin',
            'email' => 'admin@rishismm.com',
            'whatsapp' => '919999999999',
            'password' => Hash::make('admin123'),
            'balance' => 0.0000,
            'role' => 'admin',
            'api_key' => Str::random(40),
            'status' => 'active',
        ]);

        // 3. Seed Default Client Account (Loaded with ₹500 balance for instant testing)
        User::create([
            'name' => 'Demo Client',
            'email' => 'client@rishismm.com',
            'whatsapp' => '918888888888',
            'password' => Hash::make('client123'),
            'balance' => 500.0000,
            'role' => 'user',
            'api_key' => Str::random(40),
            'status' => 'active',
        ]);

        // 4. Seed Demo Categories
        $catInsta = Category::create([
            'name' => 'Instagram Services',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $catYT = Category::create([
            'name' => 'YouTube Services',
            'status' => 'active',
            'sort_order' => 2,
        ]);

        $catFB = Category::create([
            'name' => 'Facebook Services',
            'status' => 'active',
            'sort_order' => 3,
        ]);

        // 5. Seed Demo Services
        Service::create([
            'category_id' => $catInsta->id,
            'name' => 'Instagram Followers [Real & Active - Lifetime Guarantee]',
            'description' => "Speed: 10K-20K/Day\nStart Time: 0-1 Hour\nDrop Rate: 0-3% Max\nGuarantee: Lifetime Auto-Refill",
            'price_per_k' => 85.5000,
            'min_quantity' => 100,
            'max_quantity' => 15000,
            'status' => 'active',
        ]);

        Service::create([
            'category_id' => $catInsta->id,
            'name' => 'Instagram Likes [Super Instant - High Quality]',
            'description' => "Speed: 50K/Day\nStart Time: Instant\nDrop Rate: No Drop\nLink format: Full Post Link",
            'price_per_k' => 18.2000,
            'min_quantity' => 50,
            'max_quantity' => 5000,
            'status' => 'active',
        ]);

        Service::create([
            'category_id' => $catYT->id,
            'name' => 'YouTube Subscribers [Non-Drop - Real Users]',
            'description' => "Speed: 100-300/Day\nStart Time: 12-24 Hours\nDrop Rate: Zero Drop\nLink format: YouTube Channel Link",
            'price_per_k' => 750.0000,
            'min_quantity' => 100,
            'max_quantity' => 2000,
            'status' => 'active',
        ]);

        Service::create([
            'category_id' => $catYT->id,
            'name' => 'YouTube Views [High Retention - Monetizable]',
            'description' => "Speed: 5K-10K/Day\nStart Time: 0-2 Hours\nDrop Rate: Non-Drop\nLink format: YouTube Video URL",
            'price_per_k' => 120.0000,
            'min_quantity' => 500,
            'max_quantity' => 50000,
            'status' => 'active',
        ]);

        Service::create([
            'category_id' => $catFB->id,
            'name' => 'Facebook Page Likes & Followers [Real Accounts]',
            'description' => "Speed: 1K-2K/Day\nStart Time: 1-4 Hours\nDrop Rate: Max 2%\nLink format: Facebook Page URL",
            'price_per_k' => 220.0000,
            'min_quantity' => 100,
            'max_quantity' => 5000,
            'status' => 'active',
        ]);

        Service::create([
            'category_id' => $catFB->id,
            'name' => 'Facebook Video Views [Instant Start - Real Users]',
            'description' => "Speed: 10K-30K/Day\nStart Time: Instant\nDrop Rate: Non-Drop\nLink format: Facebook Video URL",
            'price_per_k' => 45.0000,
            'min_quantity' => 500,
            'max_quantity' => 100000,
            'status' => 'active',
        ]);
    }
}
