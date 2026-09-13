<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'link',
        'comments',
        'quantity',
        'charge',
        'start_count',
        'remains',
        'status',
        'provider_order_id',
        'provider_status',
        'refunded',
        'idempotency_token',
    ];

    protected $casts = [
        'charge' => 'decimal:4',
        'quantity' => 'integer',
        'start_count' => 'integer',
        'remains' => 'integer',
        'refunded' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (!\App\Services\LicenseService::isLicenseValid()) {
                throw new \Exception('License Lock Violation: Cannot place order on un-licensed domain.');
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function processingLogs()
    {
        return $this->hasMany(OrderProcessingLog::class);
    }

    public function latestLog()
    {
        return $this->hasOne(OrderProcessingLog::class)->latestOfMany();
    }

    /**
     * Get actual current end count based on start count and delivered quantity.
     */
    public function getEndCountAttribute(): int
    {
        if ($this->start_count === null) {
            return 0;
        }
        $delivered = max(0, (int)$this->quantity - (int)$this->remains);
        return (int)$this->start_count + $delivered;
    }

    /**
     * Get expected target end count when order completes fully.
     */
    public function getTargetEndCountAttribute(): int
    {
        if ($this->start_count === null) {
            return 0;
        }
        return (int)$this->start_count + (int)$this->quantity;
    }

    /**
     * Determine if this order is eligible for refill based on status, provider link, and service refill availability.
     */
    public function canRefill(): bool
    {
        if (!in_array($this->status, ['completed', 'partial'])) {
            return false;
        }

        if (!$this->provider_order_id) {
            return false;
        }

        $service = $this->service;
        if (!$service) {
            return false;
        }

        $text = strtolower(($service->name ?? '') . ' ' . ($service->description ?? '') . ' ' . ($service->original_name ?? ''));

        // If service explicitly states No Refill
        if (preg_match('/\b(no\s*refill|no-refill|non\s*refill|without\s*refill|no\s*refil)\b/i', $text) ||
            str_contains($text, '♻️nr') ||
            str_contains($text, '⚠️ no refill') ||
            str_contains($text, 'no refill') ||
            preg_match('/[|\[\(\s]nr[\]\)\s|]/i', $text)) {
            return false;
        }

        // Must explicitly mention Refill / R30 / R60 / R365 / Auto Refill / ♻️ / 🔄
        if (preg_match('/\b(refill|refil|auto\s*refill|button\s*refill|r30|r60|r90|r180|r365)\b/i', $text) ||
            str_contains($text, '♻️') ||
            str_contains($text, '🔄')) {
            return true;
        }

        return false;
    }

    /**
     * Determine if this order is eligible for customer cancellation and refund.
     */
    public function canCancel(): bool
    {
        if ($this->refunded || in_array($this->status, ['completed', 'canceled', 'refunded'])) {
            return false;
        }

        return in_array($this->status, ['pending', 'processing']);
    }

    /**
     * Normalize URL/Link for social media platforms to extract the canonical post ID, video ID, or profile handle.
     */
    public static function normalizeLink($url)
    {
        $url = trim((string)$url);
        if (empty($url)) {
            return '';
        }

        // 1. Instagram Post / Reel / TV check (e.g. instagram.com/reel/SHORTCODE, /p/SHORTCODE)
        if (preg_match('/(?:instagram\.com|instagr\.am)\/(?:reel|p|tv|reels)\/([A-Za-z0-9_-]+)/i', $url, $m)) {
            return 'instagram:post:' . $m[1];
        }
        // Instagram Profile check (e.g. instagram.com/username or @username)
        if (preg_match('/(?:instagram\.com|instagr\.am)\/([A-Za-z0-9_.-]+)/i', $url, $m)) {
            $user = strtolower(trim($m[1], '@'));
            if (!in_array($user, ['p', 'reel', 'reels', 'tv', 'stories', 'explore', 'direct', 'accounts'])) {
                return 'instagram:user:' . $user;
            }
        }

        // 2. YouTube Video check
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/|v\/)|youtu\.be\/)([A-Za-z0-9_-]{11})/i', $url, $m)) {
            return 'youtube:video:' . $m[1];
        }
        // YouTube Channel / Handle check
        if (preg_match('/youtube\.com\/(@[A-Za-z0-9_.-]+|channel\/[A-Za-z0-9_-]+|c\/[A-Za-z0-9_-]+)/i', $url, $m)) {
            return 'youtube:channel:' . strtolower($m[1]);
        }

        // 3. Telegram check
        if (preg_match('/(?:t\.me|telegram\.me|telegram\.dog)\/([A-Za-z0-9_]+)/i', $url, $m)) {
            return 'telegram:' . strtolower($m[1]);
        }

        // 4. Facebook check
        if (preg_match('/facebook\.com\/([A-Za-z0-9_.-]+)/i', $url, $m)) {
            return 'facebook:' . strtolower($m[1]);
        }

        // 5. TikTok check
        if (preg_match('/tiktok\.com\/(?:@[A-Za-z0-9_.-]+\/video\/(\d+)|([A-Za-z0-9_.-]+))/i', $url, $m)) {
            return 'tiktok:' . strtolower($m[1] ?: $m[2]);
        }

        // 6. Generic URL normalization: strip protocol, www., query strings, trailing slashes
        $parsed = parse_url($url);
        $host = preg_replace('/^(www\.|m\.)/i', '', strtolower($parsed['host'] ?? ''));
        $path = rtrim($parsed['path'] ?? '', '/');
        return $host . $path;
    }

    /**
     * Identify the primary action type (Views, Likes, Followers, Comments, etc.) of a service.
     */
    /**
     * Convert mathematical fancy Unicode characters (bold, italic, script, fullwidth, etc.) to standard ASCII text.
     */
    public static function convertUnicodeToAscii($text)
    {
        if (empty($text)) {
            return '';
        }

        if (class_exists('Normalizer')) {
            $text = \Normalizer::normalize($text, \Normalizer::FORM_KD);
        }

        $out = '';
        $len = mb_strlen($text, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $code = mb_ord($char, 'UTF-8');

            if ($code >= 0x1D400 && $code <= 0x1D7FF) {
                if ($code >= 0x1D400 && $code <= 0x1D419) { $char = chr(0x41 + ($code - 0x1D400)); }
                elseif ($code >= 0x1D41A && $code <= 0x1D433) { $char = chr(0x61 + ($code - 0x1D41A)); }
                elseif ($code >= 0x1D434 && $code <= 0x1D44D) { $char = chr(0x41 + ($code - 0x1D434)); }
                elseif ($code >= 0x1D44E && $code <= 0x1D467) { $char = chr(0x61 + ($code - 0x1D44E)); }
                elseif ($code >= 0x1D468 && $code <= 0x1D481) { $char = chr(0x41 + ($code - 0x1D468)); }
                elseif ($code >= 0x1D482 && $code <= 0x1D49B) { $char = chr(0x61 + ($code - 0x1D482)); }
                elseif ($code >= 0x1D4D0 && $code <= 0x1D4E9) { $char = chr(0x41 + ($code - 0x1D4D0)); }
                elseif ($code >= 0x1D4EA && $code <= 0x1D503) { $char = chr(0x61 + ($code - 0x1D4EA)); }
                elseif ($code >= 0x1D56C && $code <= 0x1D585) { $char = chr(0x41 + ($code - 0x1D56C)); }
                elseif ($code >= 0x1D586 && $code <= 0x1D59F) { $char = chr(0x61 + ($code - 0x1D586)); }
                elseif ($code >= 0x1D5A0 && $code <= 0x1D5B9) { $char = chr(0x41 + ($code - 0x1D5A0)); }
                elseif ($code >= 0x1D5BA && $code <= 0x1D5D3) { $char = chr(0x61 + ($code - 0x1D5BA)); }
                elseif ($code >= 0x1D5D4 && $code <= 0x1D5ED) { $char = chr(0x41 + ($code - 0x1D5D4)); }
                elseif ($code >= 0x1D5EE && $code <= 0x1D607) { $char = chr(0x61 + ($code - 0x1D5EE)); }
                elseif ($code >= 0x1D608 && $code <= 0x1D621) { $char = chr(0x41 + ($code - 0x1D608)); }
                elseif ($code >= 0x1D622 && $code <= 0x1D63B) { $char = chr(0x61 + ($code - 0x1D622)); }
                elseif ($code >= 0x1D670 && $code <= 0x1D689) { $char = chr(0x41 + ($code - 0x1D670)); }
                elseif ($code >= 0x1D68A && $code <= 0x1D6A3) { $char = chr(0x61 + ($code - 0x1D68A)); }
            } elseif ($code >= 0xFF01 && $code <= 0xFF5E) {
                $char = chr($code - 0xFEE0);
            }
            $out .= $char;
        }
        return $out;
    }

    /**
     * Helper to match action type keyword from normalized text.
     */
    private static function matchActionKeyword($text)
    {
        $text = strtolower(self::convertUnicodeToAscii($text));
        if (empty($text)) {
            return null;
        }

        if (preg_match('/\b(watch\s*time|watchtime|hours)\b/i', $text)) {
            return 'Watch Time';
        }
        if (preg_match('/\b(like|likes|reaction|reactions|upvote|upvotes|heart|hearts)\b/i', $text)) {
            return 'Likes';
        }
        if (preg_match('/\b(share|shares|retweet|retweets)\b/i', $text)) {
            return 'Shares';
        }
        if (preg_match('/\b(save|saves|bookmark|bookmarks)\b/i', $text)) {
            return 'Saves';
        }
        if (preg_match('/\b(repost|reposts)\b/i', $text)) {
            return 'Reposts';
        }
        if (preg_match('/\b(comment|comments|custom comment|emoji comment|replies)\b/i', $text)) {
            return 'Comments';
        }
        if (preg_match('/\b(view|views|impression|impressions|reach|play|plays|story view)\b/i', $text)) {
            return 'Views';
        }
        if (preg_match('/\b(follower|followers|subscriber|subscribers|member|members|follow|subs)\b/i', $text)) {
            return 'Followers / Subscribers';
        }

        return null;
    }

    /**
     * Identify the primary action type (Views, Likes, Followers, Comments, etc.) of a service.
     */
    public static function getServiceActionType($service)
    {
        if (!$service) {
            return 'General';
        }

        // 1. Check Service Name first (most specific)
        if (!empty($service->name)) {
            $type = self::matchActionKeyword($service->name);
            if ($type) {
                return $type;
            }
        }

        // 2. Check original_name if present
        if (!empty($service->original_name)) {
            $type = self::matchActionKeyword($service->original_name);
            if ($type) {
                return $type;
            }
        }

        // 3. Fallback to Category Name ONLY if Category Name defines an action keyword
        if ($service->category && !empty($service->category->name)) {
            $type = self::matchActionKeyword($service->category->name);
            if ($type) {
                return $type;
            }
        }

        return 'Service #' . $service->id;
    }

    /**
     * Check if there is an active order (pending, processing, in_progress) on the same link for the same action type.
     */
    public static function findActiveOrderOnSameLink($link, $targetService, $userId = null)
    {
        $normalizedLink = self::normalizeLink($link);
        $targetActionType = self::getServiceActionType($targetService);

        $activeStatuses = ['pending', 'processing', 'in_progress', 'in progress', 'inprogress'];

        $query = self::whereIn('status', $activeStatuses)
            ->with(['service.category']);

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        $activeOrders = $query->get();

        foreach ($activeOrders as $order) {
            if (!$order->service) {
                continue;
            }

            $orderLink = self::normalizeLink($order->link);
            if ($orderLink === $normalizedLink) {
                $orderActionType = self::getServiceActionType($order->service);
                if ($orderActionType === $targetActionType) {
                    return [
                        'order' => $order,
                        'action_type' => $targetActionType,
                    ];
                }
            }
        }

        return null;
    }
}
