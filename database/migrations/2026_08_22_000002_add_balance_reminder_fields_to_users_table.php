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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('balance_reminder_unsubscribed')->default(false)->after('status');
            $table->timestamp('last_balance_reminder_sent_at')->nullable()->after('balance_reminder_unsubscribed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['balance_reminder_unsubscribed', 'last_balance_reminder_sent_at']);
        });
    }
};
