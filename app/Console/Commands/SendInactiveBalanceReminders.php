<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class SendInactiveBalanceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:inactive-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send weekly wallet balance reminder emails to inactive customers with positive balance';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $siteName = Setting::get('site_name', 'RishiSMM');
        $sevenDaysAgo = now()->subDays(7);
        $sentCount = 0;
        $failedCount = 0;
        $consecutiveFailures = 0;
        $maxPerBatch = 15; // Max 15 emails per run to stay well below cPanel hourly defer limits

        $this->info("Processing inactive customer balance reminders (max {$maxPerBatch} per run with 2s pacing)...");

        // Fetch max 15 eligible users per run
        $users = User::query()
            ->where('status', '!=', 'suspended')
            ->where('balance', '>', 0)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->where(function ($query) {
                $query->where('balance_reminder_unsubscribed', false)
                      ->orWhereNull('balance_reminder_unsubscribed');
            })
            ->where(function ($query) use ($sevenDaysAgo) {
                $query->whereNull('last_balance_reminder_sent_at')
                      ->orWhere('last_balance_reminder_sent_at', '<', $sevenDaysAgo);
            })
            ->whereDoesntHave('orders', function ($query) use ($sevenDaysAgo) {
                $query->where('created_at', '>=', $sevenDaysAgo);
            })
            ->limit($maxPerBatch)
            ->get();

        foreach ($users as $user) {
            // Circuit breaker: Stop run if 3 consecutive SMTP failures occur to protect host reputation
            if ($consecutiveFailures >= 3) {
                $this->warn("Circuit breaker triggered: 3 consecutive email failures. Pausing batch execution.");
                break;
            }

            try {
                // Email format validation
                if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                    $this->warn("Skipping invalid email format for user ID {$user->id}: {$user->email}");
                    continue;
                }

                $unsubscribeUrl = URL::signedRoute('balance.unsubscribe', ['user' => $user->id]);
                $formattedBalance = number_format((float)$user->balance, 2);
                $subject = "You have ₹{$formattedBalance} available balance on {$siteName}";
                $title = "Hi " . htmlspecialchars($user->name) . ",";
                
                $messageBody = "Your <strong>{$siteName}</strong> account currently has <strong>₹{$formattedBalance}</strong> available balance.<br><br>" .
                               "We noticed that you haven't placed an order recently. Your balance is still available and can be used anytime for our social media services.";

                // Send email
                $sent = Setting::sendEmail(
                    $user->email,
                    $subject,
                    $title,
                    $messageBody,
                    [
                        'btnText' => 'Use Your Balance',
                        'btnUrl' => route('login'),
                        'unsubscribeUrl' => $unsubscribeUrl,
                    ]
                );

                if ($sent) {
                    $user->last_balance_reminder_sent_at = now();
                    $user->save();
                    $sentCount++;
                    $consecutiveFailures = 0;
                    $this->info("✓ Sent to user ID {$user->id} ({$user->email})");
                } else {
                    $failedCount++;
                    $consecutiveFailures++;
                    $this->error("✕ Failed to send to user ID {$user->id} ({$user->email})");
                }
            } catch (\Throwable $e) {
                Log::error("Failed to send balance reminder to user ID {$user->id}: " . $e->getMessage());
                $failedCount++;
                $consecutiveFailures++;
            }

            // ONE-BY-ONE PACING: 2 seconds delay between every single email
            usleep(2000000);
        }

        $this->info("Completed sending balance reminders. Sent: {$sentCount}, Failed: {$failedCount}");
        return Command::SUCCESS;
    }
}
