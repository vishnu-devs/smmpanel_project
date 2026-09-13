<?php

namespace App\Services;

use App\Models\User;
use App\Models\Order;
use App\Models\CustomerTier;
use App\Models\CustomerDiscount;

class DiscountService
{
    /**
     * Get lifetime eligible order spending for user
     */
    public static function getUserLifetimeSpending(User $user): float
    {
        return (float) Order::where('user_id', $user->id)
            ->whereIn('status', ['completed', 'processing', 'pending', 'inprogress', 'partial'])
            ->sum('charge');
    }

    /**
     * Get effective discount details following priority:
     * 1. Individual Customer Discount
     * 2. Tier Discount
     * 3. Default (0%)
     */
    public static function getUserEffectiveDiscountDetails(User $user): array
    {
        $now = now();

        // 1. Check Individual Customer Discount
        $customDiscount = CustomerDiscount::where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('id', 'desc')
            ->first();

        if ($customDiscount && (float)$customDiscount->discount_percentage > 0) {
            return [
                'discount_percent' => (float)$customDiscount->discount_percentage,
                'source' => 'individual',
                'label' => 'Custom Discount (' . (float)$customDiscount->discount_percentage . '%)',
                'reason' => $customDiscount->reason,
            ];
        }

        // 2. Check Customer Tier Discount
        $lifetimeSpending = self::getUserLifetimeSpending($user);

        $currentTier = CustomerTier::where('status', 'active')
            ->where('min_spending', '<=', $lifetimeSpending)
            ->where(function ($q) use ($lifetimeSpending) {
                $q->whereNull('max_spending')->orWhere('max_spending', '>=', $lifetimeSpending);
            })
            ->orderBy('min_spending', 'desc')
            ->first();

        $nextTier = CustomerTier::where('status', 'active')
            ->where('min_spending', '>', $lifetimeSpending)
            ->orderBy('min_spending', 'asc')
            ->first();

        if ($currentTier) {
            $remaining = $nextTier ? max(0, (float)$nextTier->min_spending - $lifetimeSpending) : 0;
            return [
                'discount_percent' => (float)$currentTier->discount_percentage,
                'source' => 'tier',
                'tier_name' => $currentTier->name,
                'label' => $currentTier->name . ' Tier (' . (float)$currentTier->discount_percentage . '%)',
                'spending' => $lifetimeSpending,
                'next_tier' => $nextTier ? $nextTier->name : null,
                'next_tier_min' => $nextTier ? (float)$nextTier->min_spending : null,
                'remaining_for_next_tier' => $remaining,
            ];
        }

        return [
            'discount_percent' => 0.00,
            'source' => 'none',
            'label' => 'Standard Rate',
            'spending' => $lifetimeSpending,
            'next_tier' => $nextTier ? $nextTier->name : null,
            'next_tier_min' => $nextTier ? (float)$nextTier->min_spending : null,
            'remaining_for_next_tier' => $nextTier ? max(0, (float)$nextTier->min_spending - $lifetimeSpending) : 0,
        ];
    }

    /**
     * Get effective discount percentage as float
     */
    public static function getUserDiscountPercentage(User $user): float
    {
        $details = self::getUserEffectiveDiscountDetails($user);
        return (float) $details['discount_percent'];
    }

    /**
     * Calculate discounted price per thousand
     */
    public static function getDiscountedPricePerK(float $pricePerK, float $discountPercent): float
    {
        if ($discountPercent <= 0) {
            return $pricePerK;
        }

        $discounted = $pricePerK * (1 - ($discountPercent / 100));
        return max(0.0001, round($discounted, 4));
    }
}
