<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use helper to safely add indexes only if they don't already exist
        $this->addIndexIfNotExists('orders', ['user_id', 'status']);
        $this->addIndexIfNotExists('transactions', ['user_id', 'status']);
        $this->addIndexIfNotExists('activity_logs', ['user_id', 'action']);
        $this->addIndexIfNotExists('wallet_transactions', ['user_id', 'action']);
        $this->addIndexIfNotExists('services', ['category_id', 'status']);
    }

    /**
     * Safely add a composite index only if it doesn't already exist.
     */
    private function addIndexIfNotExists(string $table, array $columns): void
    {
        $indexName = $table . '_' . implode('_', $columns) . '_index';

        $exists = collect(\Illuminate\Support\Facades\DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]))->isNotEmpty();

        if (!$exists) {
            Schema::table($table, function (Blueprint $table) use ($columns) {
                $table->index($columns);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['category_id', 'status']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'action']);
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'action']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
        });
    }
};
