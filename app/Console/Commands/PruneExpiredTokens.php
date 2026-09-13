<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SystemBackup;
use App\Models\ActivityLog;

class PruneExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokens:prune';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old activity logs and delete backups older than 15 days to save storage.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // 1. Delete backup records & files older than 15 days
        $oldBackups = SystemBackup::where('created_at', '<', now()->subDays(15))->get();
        $this->info("Found " . $oldBackups->count() . " old backups to delete.");

        foreach ($oldBackups as $backup) {
            $filePath = storage_path('app/private/backups/' . $backup->filename);
            if (file_exists($filePath)) {
                unlink($filePath);
                $this->info("Deleted file: " . $backup->filename);
            }
            $backup->delete();
        }

        // 2. Prune old activity logs older than 30 days
        $prunedLogsCount = ActivityLog::where('created_at', '<', now()->subDays(30))->delete();
        $this->info("Pruned {$prunedLogsCount} old activity logs.");

        return Command::SUCCESS;
    }
}
