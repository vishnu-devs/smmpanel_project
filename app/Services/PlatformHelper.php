<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Service;
use App\Models\Setting;
use App\Models\SocialPlatform;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PlatformHelper
{
    const CACHE_KEY = 'rishismm_active_catalog_v4';
    const PLATFORMS_CACHE_KEY = 'rishismm_db_social_platforms_v1';

    private static ?array $memoizedKeywords = null;
    private static array $textDetectionCache = [];

    /**
     * Get all social platforms (active and inactive) from DB with caching.
     */
    public static function getAllPlatforms()
    {
        return Cache::remember(self::PLATFORMS_CACHE_KEY, 3600, function () {
            if (Schema::hasTable('social_platforms')) {
                $dbPlatforms = SocialPlatform::orderBy('sort_order', 'asc')->orderBy('id', 'asc')->get();
                if ($dbPlatforms->count() > 0) {
                    return $dbPlatforms;
                }
            }
            return collect();
        });
    }

    /**
     * Get list of enabled platform keys configured by the admin.
     *
     * @return array
     */
    public static function getEnabledPlatforms(): array
    {
        // 1. Try DB model first
        $all = self::getAllPlatforms();
        if ($all->count() > 0) {
            return $all->where('is_enabled', true)->pluck('key')->toArray();
        }

        // 2. Fallback to Setting table if model isn't seeded yet
        $raw = Setting::get('enabled_platforms', null);
        if ($raw === null) {
            return ['all', 'instagram', 'facebook', 'youtube', 'telegram', 'whatsapp', 'tiktok', 'twitter', 'linkedin', 'snapchat', 'website_traffic', 'threads', 'pinterest', 'reddit', 'quora', 'google'];
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded) || empty($decoded)) {
            return ['all'];
        }

        return $decoded;
    }

    /**
     * Detect social platform key from service name and category name.
     *
     * @param string|null $serviceName
     * @param string|null $categoryName
     * @return string|null
     */
    public static function detectPlatform(?string $serviceName, ?string $categoryName = null): ?string
    {
        $catPlatform = $categoryName ? self::detectPlatformFromText($categoryName) : null;
        $srvPlatform = $serviceName ? self::detectPlatformFromText($serviceName) : null;

        if ($catPlatform !== null) {
            return $catPlatform;
        }

        return $srvPlatform;
    }

    /**
     * Match platform name against text using dynamic DB keywords and word boundary rules.
     *
     * @param string|null $text
     * @return string|null
     */
    public static function detectPlatformFromText(?string $text): ?string
    {
        if (!$text) {
            return null;
        }

        if (array_key_exists($text, self::$textDetectionCache)) {
            return self::$textDetectionCache[$text];
        }

        $cleanText = strtolower($text);
        
        // Remove special unicode / diacritics / emojis to ensure clean ASCII matching
        $cleanText = preg_replace('/[^\x20-\x7E]/', ' ', $cleanText);

        $platforms = self::getAllPlatforms();

        if ($platforms->count() > 0) {
            if (self::$memoizedKeywords === null) {
                $keywordList = [];
                foreach ($platforms as $platform) {
                    if ($platform->key === 'all') {
                        continue;
                    }
                    foreach ($platform->keywords_array as $kw) {
                        $kw = trim(strtolower($kw));
                        if (!empty($kw)) {
                            $keywordList[] = [
                                'kw' => $kw,
                                'key' => $platform->key,
                                'len' => mb_strlen($kw),
                                'regex' => '/\b' . preg_quote($kw, '/') . '\b/i',
                            ];
                        }
                    }
                }

                usort($keywordList, function ($a, $b) {
                    return $b['len'] <=> $a['len'];
                });

                self::$memoizedKeywords = $keywordList;
            }

            foreach (self::$memoizedKeywords as $item) {
                if (preg_match($item['regex'], $cleanText) || str_contains($cleanText, $item['kw'])) {
                    return self::$textDetectionCache[$text] = $item['key'];
                }
            }
            return self::$textDetectionCache[$text] = null;
        }

        // Hardcoded Fallback if DB table doesn't exist
        $res = null;
        if (preg_match('/\b(facebook|fb|fanpage)\b/i', $cleanText)) $res = 'facebook';
        else if (preg_match('/\b(instagram|insta)\b/i', $cleanText)) $res = 'instagram';
        else if (preg_match('/\b(youtube|yt)\b/i', $cleanText)) $res = 'youtube';
        else if (preg_match('/\b(tiktok|tik\s*tok)\b/i', $cleanText)) $res = 'tiktok';
        else if (preg_match('/\b(telegram|tg)\b/i', $cleanText)) $res = 'telegram';
        else if (preg_match('/\b(whatsapp|wa)\b/i', $cleanText)) $res = 'whatsapp';
        else if (preg_match('/\b(twitter|tweet|tweets|x\.com)\b/i', $cleanText)) $res = 'twitter';
        else if (preg_match('/\b(spotify)\b/i', $cleanText)) $res = 'spotify';
        else if (preg_match('/\b(discord)\b/i', $cleanText)) $res = 'discord';
        else if (preg_match('/\b(linkedin|linked\s*in)\b/i', $cleanText)) $res = 'linkedin';
        else if (preg_match('/\b(snapchat|snap)\b/i', $cleanText)) $res = 'snapchat';
        else if (preg_match('/\b(traffic|website\s*traffic)\b/i', $cleanText)) $res = 'website_traffic';
        else if (preg_match('/\b(threads)\b/i', $cleanText)) $res = 'threads';
        else if (preg_match('/\b(pinterest)\b/i', $cleanText)) $res = 'pinterest';
        else if (preg_match('/\b(reddit)\b/i', $cleanText)) $res = 'reddit';

        return self::$textDetectionCache[$text] = $res;
    }

    /**
     * Check if a service/category belongs to an enabled platform.
     *
     * @param string|null $serviceName
     * @param string|null $categoryName
     * @param array|null $enabledPlatforms
     * @return bool
     */
    public static function isPlatformEnabled(?string $serviceName, ?string $categoryName = null, ?array $enabledPlatforms = null): bool
    {
        $enabled = $enabledPlatforms ?? self::getEnabledPlatforms();
        $platform = self::detectPlatform($serviceName, $categoryName);

        // If a specific platform is detected
        if ($platform !== null) {
            return in_array($platform, $enabled, true);
        }

        // If no specific platform recognized (general/traffic/etc.), allowed if 'all' is present
        return in_array('all', $enabled, true);
    }

    /**
     * Sync active/inactive statuses of all services in database based on current enabled platforms.
     *
     * @return array
     */
    public static function syncPlatformStatuses(): array
    {
        $enabledPlatforms = self::getEnabledPlatforms();
        $deactivatedCount = 0;

        Service::with('category')->orderBy('id')->chunkById(500, function ($services) use ($enabledPlatforms, &$deactivatedCount) {
            foreach ($services as $service) {
                $catName = $service->category ? $service->category->name : '';
                $isAllowed = self::isPlatformEnabled($service->name, $catName, $enabledPlatforms);

                if (!$isAllowed && $service->status === 'active') {
                    $service->status = 'inactive';
                    $service->save();
                    $deactivatedCount++;
                }
            }
        });

        self::clearCache();

        return [
            'deactivated' => $deactivatedCount,
            'activated' => 0,
        ];
    }

    /**
     * Clear the cached catalog
     */
    public static function clearCache(): void
    {
        self::$memoizedKeywords = null;
        self::$textDetectionCache = [];
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::PLATFORMS_CACHE_KEY);
        Cache::forget('rishismm_landing_services_catalog');
        Cache::forget('rishismm_app_api_catalog');
        Cache::forget('rishismm_db_categories_light_v1');
        Cache::forget('rishismm_db_all_services_json_v1');
    }
}
