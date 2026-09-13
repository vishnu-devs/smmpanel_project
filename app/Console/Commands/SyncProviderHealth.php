<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Provider;
use Illuminate\Support\Facades\Http;

class SyncProviderHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'provider:sync-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and synchronize active SMM reseller API balances and health parameters.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $providers = Provider::where('status', 'active')->get();
        $this->info("Found " . $providers->count() . " active providers to check.");

        foreach ($providers as $prov) {
            $this->info("Checking provider: " . $prov->name);
            $startTime = microtime(true);
            try {
                $response = Http::timeout($prov->timeout ?: 10)->asForm()->post($prov->api_url, [
                    'key' => $prov->api_key,
                    'action' => 'balance',
                ]);
                
                $elapsed = (int)((microtime(true) - $startTime) * 1000);
                $prov->response_time_ms = $elapsed;

                if ($response->successful()) {
                    $data = $response->json();
                    if (is_array($data) && isset($data['balance'])) {
                        $prov->balance = (float)$data['balance'];
                        $prov->last_sync_success = now();
                        $prov->last_sync_error = null;
                        $prov->failed_requests_count = 0;
                        $prov->save();
                        $this->info("Success: balance set to " . $prov->balance);
                    } else {
                        $prov->last_sync_failed = now();
                        $prov->failed_requests_count += 1;
                        $prov->last_sync_error = 'Invalid JSON response payload or missing balance from provider API.';
                        $prov->save();
                        $this->error("Failed: invalid response payload.");
                    }
                } else {
                    $prov->last_sync_failed = now();
                    $prov->failed_requests_count += 1;
                    $prov->last_sync_error = 'HTTP Request failed with status ' . $response->status();
                    $prov->save();
                    $this->error("Failed: HTTP status " . $response->status());
                }
            } catch (\Exception $e) {
                $elapsed = (int)((microtime(true) - $startTime) * 1000);
                $prov->response_time_ms = $elapsed;
                $prov->last_sync_failed = now();
                $prov->failed_requests_count += 1;
                $prov->last_sync_error = $e->getMessage();
                $prov->save();
                $this->error("Failed: " . $e->getMessage());
            }
        }

        // Set cron heart beat stamp for dashboard
        \App\Models\Setting::set('last_cron_execution', now()->toDateTimeString());
        return Command::SUCCESS;
    }
}
