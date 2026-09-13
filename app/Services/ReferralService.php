<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralCommission;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\ActivityLog;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReferralService
{
    /**
     * Generate unique 8-character uppercase referral code
     */
    public static function generateUniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (User::where('referral_code', $code)->exists());

        return $code;
    }

    /**
     * Process referral association upon new user registration
     */
    public static function processRegistrationReferral(User $user, ?string $refCode, $request = null): void
    {
        // Ensure new user has their own referral code
        if (empty($user->referral_code)) {
            $user->referral_code = self::generateUniqueReferralCode();
            $user->save();
        }

        if (empty($refCode)) {
            return;
        }

        $referrer = User::where('referral_code', trim($refCode))->first();

        if (!$referrer || $referrer->id === $user->id) {
            return; // Invalid or self-referral attempt
        }

        // Already has a referrer established
        if ($user->referred_by) {
            return;
        }

        // Evaluate self-referral & abuse signals
        $isSuspicious = false;

        // Signal 1: Same email or matching phone
        if (!empty($user->whatsapp) && !empty($referrer->whatsapp) && $user->whatsapp === $referrer->whatsapp) {
            $isSuspicious = true;
        }

        if (strtolower($user->email) === strtolower($referrer->email)) {
            $isSuspicious = true;
        }

        // Signal 2: Registration IP check (Multiple registrations from same IP within short timeframe)
        if ($request && $request->ip()) {
            $recentSameIpCount = User::where('created_at', '>=', now()->subHours(24))
                ->where('referred_by', $referrer->id)
                ->count();

            if ($recentSameIpCount >= 5) {
                $isSuspicious = true;
            }
        }

        $status = $isSuspicious ? 'pending_review' : 'active';

        $user->referred_by = $referrer->id;
        $user->save();

        Referral::firstOrCreate(
            [
                'referrer_id' => $referrer->id,
                'referred_id' => $user->id,
            ],
            [
                'referral_code' => trim($refCode),
                'status' => $status,
            ]
        );

        ActivityLog::log('referral_registered', [
            'referrer_id' => $referrer->id,
            'referred_id' => $user->id,
            'status' => $status,
        ]);
    }

    /**
     * Calculate and credit referral commission upon successful deposit approval
     */
    public static function processDepositCommission(User $referredUser, float $depositAmount, string $depositReference): bool
    {
        // 1. Check if referral system is enabled
        $isEnabled = Setting::get('referral_status', 'disabled') === 'enabled';
        if (!$isEnabled) {
            return false;
        }

        // 2. Check if user was referred
        if (!$referredUser->referred_by) {
            return false;
        }

        $referrer = User::find($referredUser->referred_by);
        if (!$referrer || $referrer->status === 'suspended') {
            return false;
        }

        // 3. Idempotency guard: Ensure commission hasn't already been generated for this deposit reference
        $existingComm = ReferralCommission::where('deposit_reference', $depositReference)->first();
        if ($existingComm) {
            return false;
        }

        // 4. Minimum qualifying deposit check
        $minDeposit = (float) Setting::get('referral_min_deposit', 100);
        if ($depositAmount < $minDeposit) {
            return false;
        }

        // 5. Calculate commission rate and cap
        $commissionRate = (float) Setting::get('referral_commission_percent', 2.0);
        if ($commissionRate <= 0) {
            return false;
        }

        $calculatedCommission = round(($depositAmount * $commissionRate) / 100, 4);

        $maxPerDeposit = (float) Setting::get('referral_max_commission_per_deposit', 100);
        if ($maxPerDeposit > 0 && $calculatedCommission > $maxPerDeposit) {
            $calculatedCommission = $maxPerDeposit;
        }

        // Check optional monthly cap for referrer
        $monthlyCap = (float) Setting::get('referral_monthly_cap', 0);
        if ($monthlyCap > 0) {
            $thisMonthEarned = ReferralCommission::where('referrer_id', $referrer->id)
                ->where('status', 'approved')
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('commission_amount');

            if (($thisMonthEarned + $calculatedCommission) > $monthlyCap) {
                $calculatedCommission = max(0, $monthlyCap - $thisMonthEarned);
            }
        }

        if ($calculatedCommission <= 0) {
            return false;
        }

        // Check relationship status
        $referralRel = Referral::where('referrer_id', $referrer->id)
            ->where('referred_id', $referredUser->id)
            ->first();

        $relStatus = $referralRel ? $referralRel->status : 'active';

        if ($relStatus === 'blocked') {
            return false;
        }

        $commStatus = ($relStatus === 'pending_review' || $relStatus === 'suspicious') ? 'pending_review' : 'approved';

        try {
            DB::transaction(function () use ($referrer, $referredUser, $depositAmount, $depositReference, $commissionRate, $calculatedCommission, $commStatus, $referralRel) {
                $comm = ReferralCommission::create([
                    'referral_id' => $referralRel ? $referralRel->id : null,
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referredUser->id,
                    'deposit_reference' => $depositReference,
                    'deposit_amount' => $depositAmount,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $calculatedCommission,
                    'status' => $commStatus,
                ]);

                if ($commStatus === 'approved') {
                    $lockedReferrer = User::lockForUpdate()->find($referrer->id);
                    $prevBal = $lockedReferrer->balance;
                    $lockedReferrer->balance += $calculatedCommission;
                    $lockedReferrer->save();

                    WalletTransaction::create([
                        'user_id' => $lockedReferrer->id,
                        'admin_id' => null,
                        'amount' => $calculatedCommission,
                        'previous_balance' => $prevBal,
                        'new_balance' => $lockedReferrer->balance,
                        'action' => 'referral_commission',
                        'reference_id' => 'REF-' . $comm->id,
                    ]);
                }

                ActivityLog::log('referral_commission_generated', [
                    'referrer_id' => $referrer->id,
                    'referred_id' => $referredUser->id,
                    'amount' => $calculatedCommission,
                    'status' => $commStatus,
                ]);
            });

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to process referral commission for deposit {$depositReference}: " . $e->getMessage());
            return false;
        }
    }
}
