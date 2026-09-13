<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WalletTransaction;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RefundService
{
    /**
     * Complete refund logic (Full or Partial) inside an atomic DB transaction.
     *
     * @param Order $order
     * @param string $type 'canceled' | 'partial' | 'refunded'
     * @param int $remains Remaining / undelivered quantity
     * @return bool
     */
    public static function process(Order $order, string $type, int $remains = 0): bool
    {
        // 1. Strict Idempotency Check: Prevent duplicate refunds
        if ($order->refunded) {
            return false;
        }

        try {
            return DB::transaction(function () use ($order, $type, $remains) {
                // Re-fetch order and lock order & user rows to prevent race conditions
                $orderFresh = Order::lockForUpdate()->find($order->id);
                if (!$orderFresh || $orderFresh->refunded) {
                    return false;
                }

                $user = User::lockForUpdate()->findOrFail($orderFresh->user_id);
                $prevBalance = (float)$user->balance;
                $originalCharge = (float)$orderFresh->charge;
                $originalQuantity = (int)$orderFresh->quantity;

                if ($type === 'canceled' || $type === 'refunded') {
                    $refundAmount = $originalCharge;
                    $orderFresh->refunded = true;
                    $orderFresh->status = 'canceled';

                    if ($refundAmount > 0) {
                        Transaction::create([
                            'user_id' => $user->id,
                            'amount' => $refundAmount,
                            'payment_gateway' => 'System Refund',
                            'status' => 'completed',
                            'payment_id' => 'REFUND_' . $orderFresh->id,
                            'notes' => 'Full refund for canceled order #' . $orderFresh->id,
                        ]);

                        WalletTransaction::create([
                            'user_id' => $user->id,
                            'admin_id' => auth()->id() ?? null,
                            'amount' => $refundAmount,
                            'previous_balance' => $prevBalance,
                            'new_balance' => $prevBalance + $refundAmount,
                            'action' => 'order_refund',
                            'reference_id' => $orderFresh->id,
                        ]);

                        $user->balance = $prevBalance + $refundAmount;
                        $user->save();
                    }

                    $orderFresh->save();

                    ActivityLog::log('order_refund_full', [
                        'order_id' => $orderFresh->id,
                        'amount' => $refundAmount,
                        'client' => $user->email
                    ]);

                    return true;
                } elseif ($type === 'partial') {
                    $remainsQty = (int)$remains;
                    $orderFresh->status = 'partial';
                    $orderFresh->remains = $remainsQty;

                    if ($remainsQty > 0 && $originalQuantity > 0 && $originalCharge > 0) {
                        $effectiveRemains = min($remainsQty, $originalQuantity);
                        $unitPrice = $originalCharge / $originalQuantity;
                        $refundAmount = round($unitPrice * $effectiveRemains, 4);
                        $refundAmount = min($refundAmount, $originalCharge);

                        if ($refundAmount > 0) {
                            Transaction::create([
                                'user_id' => $user->id,
                                'amount' => $refundAmount,
                                'payment_gateway' => 'System Refund',
                                'status' => 'completed',
                                'payment_id' => 'REFUND_PARTIAL_' . $orderFresh->id,
                                'notes' => "Partial refund for {$effectiveRemains} remaining items in order #{$orderFresh->id}",
                            ]);

                            WalletTransaction::create([
                                'user_id' => $user->id,
                                'admin_id' => auth()->id() ?? null,
                                'amount' => $refundAmount,
                                'previous_balance' => $prevBalance,
                                'new_balance' => $prevBalance + $refundAmount,
                                'action' => 'order_refund_partial',
                                'reference_id' => $orderFresh->id,
                            ]);

                            $user->balance = $prevBalance + $refundAmount;
                            $user->save();
                            $orderFresh->refunded = true;
                        }

                        ActivityLog::log('order_refund_partial', [
                            'order_id' => $orderFresh->id,
                            'amount' => $refundAmount,
                            'remains' => $effectiveRemains,
                            'client' => $user->email
                        ]);
                    }

                    $orderFresh->save();
                    return true;
                }

                return false;
            });
        } catch (\Exception $e) {
            Log::error("RefundService processing error for Order #{$order->id}: " . $e->getMessage());
            throw $e;
        }
    }
}

