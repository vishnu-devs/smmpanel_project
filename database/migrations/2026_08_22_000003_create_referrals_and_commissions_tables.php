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
        // Add referral columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('referred_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->string('referral_code', 32)->nullable()->unique()->after('referred_by');
        });

        // Create referrals table
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->string('referral_code', 32)->nullable();
            $table->string('status')->default('active'); // active, pending_review, suspicious, blocked
            $table->timestamps();

            $table->unique(['referrer_id', 'referred_id']);
        });

        // Create referral_commissions table
        Schema::create('referral_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referral_id')->nullable()->constrained('referrals')->onDelete('set null');
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->string('deposit_reference')->nullable()->index();
            $table->decimal('deposit_amount', 15, 4);
            $table->decimal('commission_rate', 8, 2);
            $table->decimal('commission_amount', 15, 4);
            $table->string('status')->default('approved'); // approved, pending_review, rejected, reversed
            $table->timestamps();

            $table->index(['referrer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_commissions');
        Schema::dropIfExists('referrals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referred_by']);
            $table->dropColumn(['referred_by', 'referral_code']);
        });
    }
};
