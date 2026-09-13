<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to add high-performance database indexes safely without any data loss.
     */
    public function up(): void
    {
        $this->addIndexIfNotExists('orders', ['status', 'provider_id']);
        $this->addIndexIfNotExists('orders', ['user_id', 'created_at']);
        $this->addIndexIfNotExists('services', ['status', 'sort_order']);
        $this->addIndexIfNotExists('categories', ['status', 'sort_order']);
        $this->addIndexIfNotExists('transactions', ['user_id', 'created_at']);
        $this->addIndexIfNotExists('tickets', ['user_id', 'status']);
        $this->addIndexIfNotExists('ticket_messages', ['ticket_id', 'created_at']);
    }

    /**
     * Safely add a composite index only if it doesn't already exist.
     */
    private function addIndexIfNotExists(string $table, array $columns): void
    {
        $indexName = $table . '_' . implode('_', $columns) . '_index';

        try {
            $exists = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();

            if (!$exists) {
                Schema::table($table, function (Blueprint $table) use ($columns) {
                    $table->index($columns);
                });
            }
        } catch (\Exception $e) {
            // Ignore if table or index doesn't exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('ticket_messages', ['ticket_id', 'created_at']);
        $this->dropIndexIfExists('tickets', ['user_id', 'status']);
        $this->dropIndexIfExists('transactions', ['user_id', 'created_at']);
        $this->dropIndexIfExists('categories', ['status', 'sort_order']);
        $this->dropIndexIfExists('services', ['status', 'sort_order']);
        $this->dropIndexIfExists('orders', ['user_id', 'created_at']);
        $this->dropIndexIfExists('orders', ['status', 'provider_id']);
    }

    private function dropIndexIfExists(string $table, array $columns): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->dropIndex($columns);
            });
        } catch (\Exception $e) {
            // Ignore if table or index doesn't exist
        }
    }
};
