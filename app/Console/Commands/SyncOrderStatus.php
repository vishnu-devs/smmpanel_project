<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Provider;
use App\Services\RefundService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncOrderStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:sync-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize order status and remains with SMM API providers in optimized batches.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting background order status synchronization...");
        if (!class_exists(\App\Services\OrderStatusSyncService::class)) {
            $serviceFile = app_path('Services/OrderStatusSyncService.php');
            if (file_exists($serviceFile)) {
                require_once $serviceFile;
            }
        }

        if (class_exists(\App\Services\OrderStatusSyncService::class)) {
            \App\Services\OrderStatusSyncService::sync(null, true);
        }
        $this->info("Background order status synchronization completed.");
        return Command::SUCCESS;
    }
}
