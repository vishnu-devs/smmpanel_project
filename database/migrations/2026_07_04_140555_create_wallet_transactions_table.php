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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('amount', 15, 4);
            $table->decimal('previous_balance', 15, 4);
            $table->decimal('new_balance', 15, 4);
            $table->string('action'); // e.g. order_place, order_refund, deposit_approve, admin_adjustment
            $table->string('reference_id')->nullable(); // order_id, txn_id, etc.
            $table->timestamp('created_at')->useCurrent();
            
            // Add performance index
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
