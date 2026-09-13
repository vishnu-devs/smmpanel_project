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
        Schema::table('providers', function (Blueprint $table) {
            $table->string('currency', 10)->default('USD')->after('balance');
            $table->integer('priority')->default(1)->after('currency');
            $table->integer('timeout')->default(10)->after('priority');
            $table->integer('retry_count')->default(3)->after('timeout');
            $table->boolean('auto_sync')->default(true)->after('retry_count');
            $table->timestamp('last_sync_success')->nullable()->after('auto_sync');
            $table->timestamp('last_sync_failed')->nullable()->after('last_sync_success');
            $table->integer('response_time_ms')->default(0)->after('last_sync_failed');
            $table->integer('failed_requests_count')->default(0)->after('response_time_ms');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->json('failover_mappings')->nullable()->after('provider_rate');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('idempotency_token', 100)->nullable()->unique()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('idempotency_token');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('failover_mappings');
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn([
                'currency',
                'priority',
                'timeout',
                'retry_count',
                'auto_sync',
                'last_sync_success',
                'last_sync_failed',
                'response_time_ms',
                'failed_requests_count',
            ]);
        });
    }
};
