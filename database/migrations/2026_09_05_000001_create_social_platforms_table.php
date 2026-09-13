<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('social_platforms')) {
            Schema::create('social_platforms', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('name');
                $table->string('icon')->default('fa-solid fa-globe');
                $table->string('color')->default('#0284c7');
                $table->text('keywords')->nullable();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_default')->default(false);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Seed initial platform items if empty
        if (DB::table('social_platforms')->count() === 0) {
            $defaultPlatforms = [
                [
                    'key' => 'all',
                    'name' => 'All Platforms',
                    'icon' => 'fa-solid fa-globe',
                    'color' => '#0284c7',
                    'keywords' => 'all',
                    'is_enabled' => true,
                    'is_default' => true,
                    'sort_order' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'instagram',
                    'name' => 'Instagram',
                    'icon' => 'fa-brands fa-instagram',
                    'color' => '#e1306c',
                    'keywords' => 'instagram, insta, ig, reel, threads, igv, stories',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'facebook',
                    'name' => 'Facebook',
                    'icon' => 'fa-brands fa-facebook',
                    'color' => '#1877f2',
                    'keywords' => 'facebook, fb, fanpage, meta',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'youtube',
                    'name' => 'YouTube',
                    'icon' => 'fa-brands fa-youtube',
                    'color' => '#ff0000',
                    'keywords' => 'youtube, yt, shorts, subscribers, views',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 4,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'telegram',
                    'name' => 'Telegram',
                    'icon' => 'fa-brands fa-telegram',
                    'color' => '#0088cc',
                    'keywords' => 'telegram, tg, tg member, tg view',
                    'is_enabled' => false,
                    'is_default' => false,
                    'sort_order' => 5,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'whatsapp',
                    'name' => 'WhatsApp',
                    'icon' => 'fa-brands fa-whatsapp',
                    'color' => '#25d366',
                    'keywords' => 'whatsapp, wa, wa channel',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 6,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'tiktok',
                    'name' => 'TikTok',
                    'icon' => 'fa-brands fa-tiktok',
                    'color' => '#000000',
                    'keywords' => 'tiktok, tik tok, tt',
                    'is_enabled' => false,
                    'is_default' => false,
                    'sort_order' => 7,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'twitter',
                    'name' => 'Twitter / X',
                    'icon' => 'fa-brands fa-x-twitter',
                    'color' => '#0f172a',
                    'keywords' => 'twitter, tweet, tweets, x.com, x premium',
                    'is_enabled' => false,
                    'is_default' => false,
                    'sort_order' => 8,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'spotify',
                    'name' => 'Spotify',
                    'icon' => 'fa-brands fa-spotify',
                    'color' => '#1db954',
                    'keywords' => 'spotify',
                    'is_enabled' => false,
                    'is_default' => false,
                    'sort_order' => 9,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'discord',
                    'name' => 'Discord',
                    'icon' => 'fa-brands fa-discord',
                    'color' => '#5865f2',
                    'keywords' => 'discord',
                    'is_enabled' => false,
                    'is_default' => false,
                    'sort_order' => 10,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'linkedin',
                    'name' => 'LinkedIn',
                    'icon' => 'fa-brands fa-linkedin',
                    'color' => '#0a66c2',
                    'keywords' => 'linkedin, linked in',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 11,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'snapchat',
                    'name' => 'Snapchat',
                    'icon' => 'fa-brands fa-snapchat',
                    'color' => '#fffc00',
                    'keywords' => 'snapchat, snap',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 12,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'website_traffic',
                    'name' => 'Website Traffic',
                    'icon' => 'fa-solid fa-chart-line',
                    'color' => '#10b981',
                    'keywords' => 'website traffic, traffic, referrer',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 13,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'threads',
                    'name' => 'Threads',
                    'icon' => 'fa-brands fa-threads',
                    'color' => '#000000',
                    'keywords' => 'threads',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 14,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'pinterest',
                    'name' => 'Pinterest',
                    'icon' => 'fa-brands fa-pinterest',
                    'color' => '#e60023',
                    'keywords' => 'pinterest',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 15,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'reddit',
                    'name' => 'Reddit',
                    'icon' => 'fa-brands fa-reddit',
                    'color' => '#ff4500',
                    'keywords' => 'reddit',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 16,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'quora',
                    'name' => 'Quora',
                    'icon' => 'fa-brands fa-quora',
                    'color' => '#a82400',
                    'keywords' => 'quora, quora.com',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 17,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'key' => 'google',
                    'name' => 'Google',
                    'icon' => 'fa-brands fa-google',
                    'color' => '#4285f4',
                    'keywords' => 'google, reviews',
                    'is_enabled' => true,
                    'is_default' => false,
                    'sort_order' => 18,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ];

            DB::table('social_platforms')->insert($defaultPlatforms);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_platforms');
    }
};
