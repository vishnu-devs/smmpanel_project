<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\ReceivedPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function addFunds()
    {
        $transactions = Transaction::where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        $upiQr = Setting::get('payment_upi_qr');
        $upiId = Setting::get('payment_upi_id');
        $bankDetails = Setting::get('payment_bank_details');

        return view('user.add_funds', compact('transactions', 'upiQr', 'upiId', 'bankDetails'));
    }

    public function manualPay(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_id' => 'required|digits:12|unique:transactions,payment_id',
            'notes' => 'nullable|string|max:500',
        ], [
            'payment_id.required' => 'Please enter the 12-digit UPI UTR number.',
            'payment_id.digits' => 'UTR number must be exactly 12 numeric digits (no letters, special characters, or emojis allowed).',
            'payment_id.unique' => 'This Transaction Reference/UTR number has already been submitted for verification.',
        ]);

        $utr = trim($request->payment_id);
        $amount = floatval($request->amount);

        // Check if this UTR has already been claimed by another user account
        $alreadyClaimed = ReceivedPayment::where('utr', $utr)
            ->where('status', 'used')
            ->exists();

        if ($alreadyClaimed) {
            return back()->with('error', 'This UTR has already been claimed by another user account.');
        }

        // Try to find matching unused received payment
        $receivedPayment = ReceivedPayment::where('utr', $utr)
            ->where('status', 'unused')
            ->first();

        if ($receivedPayment) {
            // Verify if amount matches (allowing floating-point delta check)
            if (abs(floatval($receivedPayment->amount) - $amount) < 0.01) {
                // Instantly approve!
                $user = Auth::user();
                
                $txn = Transaction::create([
                    'user_id' => $user->id,
                    'amount' => $amount,
                    'payment_gateway' => 'UPI/Manual Bank (Auto)',
                    'status' => 'completed',
                    'payment_id' => $utr,
                    'screenshot' => null,
                    'notes' => $request->notes,
                ]);

                $this->processAutoApproval($receivedPayment, $txn, $user);

                return redirect()->route('add_funds')->with('success', 'Payment verified and ₹' . number_format($amount, 2) . ' credited to your wallet instantly!');
            } else {
                return back()->with('error', 'UTR verification failed: The amount you entered does not match the payment received on our end. Please enter the exact amount paid.');
            }
        }

        $user = Auth::user();

        // Fallback: Create pending transaction for automated/manual verification
        $txn = Transaction::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_gateway' => 'UPI/Manual Bank',
            'status' => 'pending',
            'payment_id' => $utr,
            'screenshot' => null,
            'notes' => $request->notes,
            'admin_notified' => false,
        ]);

        ActivityLog::log('deposit_submit', [
            'amount' => $amount,
            'payment_id' => $utr
        ]);

        return redirect()->route('add_funds')->with('success', 'Your deposit request (UTR: ' . $utr . ') of ₹' . number_format($amount, 2) . ' has been submitted successfully! Our automated system is verifying your payment safely and will add it to your wallet balance shortly.');
    }

    public function pay(Request $request)
    {
        return redirect()->route('add_funds')->with('error', 'Selected payment gateway is currently under maintenance. Please use UPI/Manual payment.');
    }

    /**
     * Endpoint for SMS Reader APK to query pending customer UTRs submitted in the last 10 minutes
     * Allows reader to sleep when no pending requests exist (saves phone battery)
     */
    public function pendingUtrs(Request $request)
    {
        $expectedToken = Setting::get('payment_webhook_token');
        if (!$expectedToken) {
            $expectedToken = Str::random(32);
            Setting::set('payment_webhook_token', $expectedToken);
        }

        $receivedToken = $request->header('X-Webhook-Token') 
            ?? $request->input('token') 
            ?? $request->input('secret');

        if (!$receivedToken || $receivedToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized token.'], 401);
        }

        // Only fetch pending deposit requests submitted in the last 10 minutes
        $pending = Transaction::where('status', 'pending')
            ->where('payment_gateway', 'like', '%UPI%')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->get(['id', 'payment_id as utr', 'amount', 'created_at']);

        return response()->json([
            'status' => 'success',
            'has_pending' => $pending->count() > 0,
            'count' => $pending->count(),
            'timeout_minutes' => 10,
            'pending_transactions' => $pending,
        ], 200);
    }

    /**
     * Public Webhook endpoint for UPI Notification Webhooks
     */
    public function webhook(Request $request)
    {
        // Get expected secret token
        $expectedToken = Setting::get('payment_webhook_token');
        if (!$expectedToken) {
            $expectedToken = Str::random(32);
            Setting::set('payment_webhook_token', $expectedToken);
        }

        // Retrieve token from request headers or input parameters
        $receivedToken = $request->header('X-Webhook-Token') 
            ?? $request->input('token') 
            ?? $request->input('secret');

        if (!$receivedToken || $receivedToken !== $expectedToken) {
            return response()->json(['error' => 'Unauthorized token.'], 401);
        }

        $utr = trim($request->input('utr', ''));
        $amount = $request->input('amount');
        $senderName = $request->input('sender');
        $message = $request->input('message') ?? $request->input('text') ?? $request->input('body');

        // Parse fields from text body if they are not explicitly provided
        if (empty($utr) || empty($amount)) {
            $parsed = self::parsePaymentMessage($message);
            if ($parsed) {
                $utr = $parsed['utr'];
                $amount = $parsed['amount'];
            }
        }

        if (empty($utr) || empty($amount)) {
            Log::warning('UPI Webhook parsing failed. Raw request body: ' . json_encode($request->all()));
            return response()->json(['error' => 'Failed to parse UTR and Amount from payload.'], 422);
        }

        $amount = floatval($amount);

        // Deduplicate to avoid processing same webhook alert twice
        $exists = ReceivedPayment::where('utr', $utr)->first();
        if ($exists) {
            return response()->json(['message' => 'Notification already registered.'], 200);
        }

        // Save received payment
        $receivedPayment = ReceivedPayment::create([
            'utr' => $utr,
            'amount' => $amount,
            'status' => 'unused',
            'sender_name' => $senderName,
            'raw_data' => json_encode($request->all()),
        ]);

        // Auto-approve pending transaction if user submitted within last 10 minutes (or pending state)
        $pendingTxn = Transaction::where('payment_id', $utr)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->first();

        if ($pendingTxn) {
            if (abs(floatval($pendingTxn->amount) - $amount) < 0.01) {
                $user = User::find($pendingTxn->user_id);
                if ($user) {
                    $this->processAutoApproval($receivedPayment, $pendingTxn, $user);
                    
                    // Count remaining pending transactions in the 10-minute window
                    $remainingPending = Transaction::where('status', 'pending')
                        ->where('payment_gateway', 'like', '%UPI%')
                        ->where('created_at', '>=', now()->subMinutes(10))
                        ->count();

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment logged and pending transaction approved.',
                        'utr' => $utr,
                        'amount' => $amount,
                        'approved' => true,
                        'stop_waiting' => ($remainingPending === 0),
                        'remaining_pending_count' => $remainingPending,
                    ], 200);
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Payment logged successfully.',
            'utr' => $utr,
            'amount' => $amount,
            'approved' => false,
        ], 200);
    }

    /**
     * Parse payment details from raw SMS/notification texts
     */
    public static function parsePaymentMessage($text)
    {
        if (empty($text)) {
            return null;
        }

        // 1. Extract UTR (12 consecutive digits)
        $utr = null;
        if (preg_match('/\b(\d{12})\b/', $text, $matches)) {
            $utr = $matches[1];
        }

        // 2. Extract Amount
        $amount = null;
        if (preg_match('/(?:Rs\.?|INR|₹)\s*([\d,]+(?:\.\d{1,2})?)/i', $text, $matches)) {
            $amountStr = str_replace(',', '', $matches[1]);
            $amount = floatval($amountStr);
        } else {
            // Fallback for word patterns like "credited with 100"
            if (preg_match('/(?:credited|received|added|deposited)\s+(?:with|of)?\s*([\d,]+(?:\.\d{1,2})?)/i', $text, $matches)) {
                $amountStr = str_replace(',', '', $matches[1]);
                $amount = floatval($amountStr);
            }
        }

        if ($utr && $amount) {
            return [
                'utr' => $utr,
                'amount' => $amount
            ];
        }

        return null;
    }

    /**
     * Calculate Deposit Bonus Amount based on Configured Tiers
     */
    public static function calculateDepositBonus($amount)
    {
        $amount = floatval($amount);
        if ($amount <= 0) return 0;

        $bonusStatus = Setting::get('deposit_bonus_status', 'enabled');
        if ($bonusStatus === 'disabled') return 0;

        $t1Min = floatval(Setting::get('bonus_t1_min', 100));
        $t1Percent = floatval(Setting::get('bonus_t1_percent', 1));

        $t2Min = floatval(Setting::get('bonus_t2_min', 1000));
        $t2Percent = floatval(Setting::get('bonus_t2_percent', 2));

        $t3Min = floatval(Setting::get('bonus_t3_min', 5000));
        $t3Percent = floatval(Setting::get('bonus_t3_percent', 5));

        $bonusPercent = 0;

        if ($t3Min > 0 && $amount >= $t3Min && $t3Percent > 0) {
            $bonusPercent = $t3Percent;
        } elseif ($t2Min > 0 && $amount >= $t2Min && $t2Percent > 0) {
            $bonusPercent = $t2Percent;
        } elseif ($t1Min > 0 && $amount >= $t1Min && $t1Percent > 0) {
            $bonusPercent = $t1Percent;
        }

        if ($bonusPercent > 0) {
            return round(($amount * $bonusPercent) / 100, 2);
        }

        return 0;
    }

    /**
     * Mark received payment as used and credit the user's wallet balance
     */
    private function processAutoApproval(ReceivedPayment $receivedPayment, Transaction $transaction, User $user)
    {
        DB::transaction(function () use ($receivedPayment, $transaction, $user) {
            // Lock the user record against concurrent updates
            $lockedUser = User::lockForUpdate()->findOrFail($user->id);

            // Update received payment
            $receivedPayment->status = 'used';
            $receivedPayment->user_id = $lockedUser->id;
            $receivedPayment->save();

            // Update transaction status
            $transaction->status = 'completed';
            $transaction->save();

            $baseAmount = (float)$transaction->amount;
            $bonusAmount = self::calculateDepositBonus($baseAmount);
            $totalAmount = $baseAmount + $bonusAmount;

            // Update user balance
            $prevBalance = $lockedUser->balance;
            $lockedUser->balance += $totalAmount;
            $lockedUser->save();

            // 1. Create base deposit ledger entry
            WalletTransaction::create([
                'user_id' => $lockedUser->id,
                'admin_id' => null, // System automated approval
                'amount' => $baseAmount,
                'previous_balance' => $prevBalance,
                'new_balance' => $prevBalance + $baseAmount,
                'action' => 'deposit_approve',
                'reference_id' => $transaction->payment_id,
            ]);

            // 2. Create bonus ledger entry if bonus was added
            if ($bonusAmount > 0) {
                WalletTransaction::create([
                    'user_id' => $lockedUser->id,
                    'admin_id' => null,
                    'amount' => $bonusAmount,
                    'previous_balance' => $prevBalance + $baseAmount,
                    'new_balance' => $lockedUser->balance,
                    'action' => 'deposit_bonus',
                    'reference_id' => 'BONUS-' . $transaction->payment_id,
                ]);
            }

            // 3. Process Referral Commission for Referrer (if applicable)
            \App\Services\ReferralService::processDepositCommission($lockedUser, $baseAmount, $transaction->payment_id);

            // Log activity
            ActivityLog::log('deposit_auto_approve', [
                'txn_id' => $transaction->id,
                'amount' => $baseAmount,
                'bonus' => $bonusAmount,
                'client_email' => $user->email,
                'utr' => $transaction->payment_id,
            ]);
        });
    }

    public function history(Request $request)
    {
        $user = Auth::user();

        $query = WalletTransaction::where('user_id', $user->id);

        if ($request->filled('type')) {
            $query->where('action', $request->type);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('reference_id', 'like', "%{$search}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('user.payment_history', compact('transactions'));
    }

    /**
     * Send email alert to Admin for pending deposit requests that have reached 1 minute without auto-approval
     */
    public static function notifyAdminPendingDeposits(): void
    {
        try {
            $pendingTxns = Transaction::where('status', 'pending')
                ->where(function ($q) {
                    $q->where('admin_notified', false)
                      ->orWhereNull('admin_notified');
                })
                ->where('created_at', '<=', now()->subMinute())
                ->with('user')
                ->get();

            if ($pendingTxns->isEmpty()) {
                return;
            }

            $adminEmail = Setting::get('support_email') ?: (User::where('role', 'admin')->value('email') ?? 'support@rishismm.com');

            foreach ($pendingTxns as $txn) {
                $user = $txn->user;
                $userName = $user ? $user->name : 'Customer';
                $userEmail = $user ? $user->email : 'N/A';
                $amount = (float)$txn->amount;
                $utr = $txn->payment_id;

                $subject = "⚠️ [Action Required] Deposit Pending Verification (>1 min) - ₹" . number_format($amount, 2);
                $title = "Deposit Pending Verification (>1 minute)";
                $messageBody = "Hello Admin,<br><br>" .
                    "A customer deposit request has reached <strong>1 minute</strong> without automatic bank SMS approval.<br><br>" .
                    "<strong>Deposit Details:</strong><br>" .
                    "• <strong>Customer:</strong> {$userName} ({$userEmail})<br>" .
                    "• <strong>Amount:</strong> ₹" . number_format($amount, 2) . "<br>" .
                    "• <strong>UTR / Reference ID:</strong> <code style='background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;'>{$utr}</code><br>" .
                    "• <strong>Submitted At:</strong> " . $txn->created_at->format('d M Y, h:i A') . " (" . $txn->created_at->diffForHumans() . ")<br>" .
                    "• <strong>Current Status:</strong> Pending Auto-Verification / Manual Review<br><br>" .
                    "You can verify your bank statement or UPI app and approve manually, or leave it for auto-approval when the bank SMS syncs.";

                Setting::sendEmail(
                    $adminEmail,
                    $subject,
                    $title,
                    $messageBody,
                    [
                        'btnText' => 'Open Payments & Approve',
                        'btnUrl' => route('admin.transactions', ['status' => 'pending', 'search' => $utr])
                    ]
                );

                $txn->admin_notified = true;
                $txn->save();
            }
        } catch (\Exception $e) {
            Log::error("Failed to execute notifyAdminPendingDeposits: " . $e->getMessage());
        }
    }
}
