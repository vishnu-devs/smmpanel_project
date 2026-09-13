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
        // Categories
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('active'); // active, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Providers (External API Panels)
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('api_url');
            $table->string('api_key');
            $table->string('status')->default('active'); // active, inactive
            $table->decimal('balance', 15, 4)->default(0.0000);
            $table->timestamps();
        });

        // Services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_k', 15, 4); // Price charged to our clients (per 1000 items)
            $table->integer('min_quantity');
            $table->integer('max_quantity');
            $table->string('status')->default('active'); // active, inactive
            $table->foreignId('provider_id')->nullable()->constrained()->onDelete('set null');
            $table->string('provider_service_id')->nullable(); // Service ID on the provider's panel
            $table->decimal('provider_rate', 15, 4)->default(0.0000); // Provider cost rate (for profit reports)
            $table->timestamps();
        });

        // Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->string('link');
            $table->integer('quantity');
            $table->decimal('charge', 15, 4); // Amount charged to the user
            $table->integer('start_count')->default(0);
            $table->integer('remains')->default(0);
            $table->string('status')->default('pending'); // pending, processing, in_progress, completed, partial, canceled, refunded
            $table->string('provider_order_id')->nullable(); // Order ID returned by provider API
            $table->string('provider_status')->nullable(); // Current status reported by provider API
            $table->boolean('refunded')->default(false);
            $table->timestamps();
        });

        // Transactions (Balance deposits)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 4);
            $table->string('payment_gateway'); // e.g. Paytm, Razorpay, Manual UPI, Bank
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->string('payment_id')->nullable(); // Transaction ID or reference code from gateway
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Tickets (Support System)
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->string('status')->default('open'); // open, answered, client_reply, closed
            $table->timestamps();
        });

        // Ticket Messages
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // message sender
            $table->text('message');
            $table->timestamps();
        });

        // Settings (Configuration values)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('services');
        Schema::dropIfExists('providers');
        Schema::dropIfExists('categories');
    }
};
