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
        Schema::create('received_payments', function (Blueprint $table) {
            $table->id();
            $table->string('utr')->unique();
            $table->decimal('amount', 15, 4);
            $table->string('status')->default('unused'); // unused, used
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sender_name')->nullable();
            $table->text('raw_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('received_payments');
    }
};
