<?php

namespace App\Services;

use App\Models\SystemBackup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupManager
{
    /**
     * Generate database SQL backup dump.
     */
    public static function backupDatabase(): bool
    {
        $filename = 'backup_db_' . date('Y_m_d_His') . '.sql';
        $backupDir = storage_path('app/private/backups');
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupPath = $backupDir . '/' . $filename;
        
        try {
            $pdo = DB::connection()->getPdo();
            $tables = [];
            $result = $pdo->query('SHOW TABLES');
            while ($row = $result->fetch(\PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }

            $sql = "-- Growinsta SMM Panel Database Backup\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                // Drop if exists statement
                $sql .= "DROP TABLE IF EXISTS `$table`;\n";
                
                // Show Create Table
                $createTableResult = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(\PDO::FETCH_ASSOC);
                $sql .= $createTableResult['Create Table'] . ";\n\n";
                
                // Fetch all data
                $rowsResult = $pdo->query("SELECT * FROM `$table`");
                while ($row = $rowsResult->fetch(\PDO::FETCH_ASSOC)) {
                    $keys = array_keys($row);
                    $escapedKeys = array_map(function ($k) { return "`$k`"; }, $keys);
                    
                    $values = array_values($row);
                    $escapedValues = array_map(function ($val) use ($pdo) {
                        if ($val === null) {
                            return 'NULL';
                        }
                        return $pdo->quote($val);
                    }, $values);
                    
                    $sql .= "INSERT INTO `$table` (" . implode(', ', $escapedKeys) . ") VALUES (" . implode(', ', $escapedValues) . ");\n";
                }
                $sql .= "\n";
            }
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

            file_put_contents($backupPath, $sql);
            $size = filesize($backupPath);

            SystemBackup::create([
                'filename' => $filename,
                'type' => 'db',
                'size_bytes' => $size,
                'status' => 'completed',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Database backup failed: " . $e->getMessage());
            SystemBackup::create([
                'filename' => $filename,
                'type' => 'db',
                'size_bytes' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Generate storage files backup zip archive.
     */
    public static function backupStorage(): bool
    {
        $filename = 'backup_storage_' . date('Y_m_d_His') . '.zip';
        $backupDir = storage_path('app/private/backups');
        
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        
        $backupPath = $backupDir . '/' . $filename;
        $sourceDir = storage_path('app');

        try {
            $zip = new \ZipArchive();
            if ($zip->open($backupPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception("Cannot open ZIP archive: " . $backupPath);
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($sourceDir),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    // Skip the backup folder itself to avoid recursive zip dumps
                    if (str_contains($filePath, 'private' . DIRECTORY_SEPARATOR . 'backups')) {
                        continue;
                    }
                    
                    $relativePath = substr($filePath, strlen($sourceDir) + 1);
                    $zip->addFile($filePath, $relativePath);
                }
            }

            $zip->close();
            $size = filesize($backupPath);

            SystemBackup::create([
                'filename' => $filename,
                'type' => 'storage',
                'size_bytes' => $size,
                'status' => 'completed',
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Storage file backup failed: " . $e->getMessage());
            SystemBackup::create([
                'filename' => $filename,
                'type' => 'storage',
                'size_bytes' => 0,
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
