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
        Schema::create('scratch_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('card_date');
            $table->string('status')->default('AVAILABLE'); // AVAILABLE, SCRATCHED, CREDITED
            $table->decimal('reward_amount', 10, 4);
            $table->timestamp('scratched_at')->nullable();
            $table->timestamp('credited_at')->nullable();
            $table->timestamps();

            // Strict unique constraint: Max 1 card per user per calendar day
            $table->unique(['user_id', 'card_date'], 'uniq_scratch_user_date');

            // Performance indexes for eligibility and weekly queries
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'reward_amount', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scratch_cards');
    }
};
