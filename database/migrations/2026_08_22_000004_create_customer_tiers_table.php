<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('min_spending', 15, 4)->default(0);
            $table->decimal('max_spending', 15, 4)->nullable();
            $table->decimal('discount_percentage', 8, 2)->default(0);
            $table->string('status')->default('active'); // active, inactive
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Insert default initial tiers
        DB::table('customer_tiers')->insert([
            [
                'name' => 'BRONZE',
                'min_spending' => 0.0000,
                'max_spending' => 4999.9999,
                'discount_percentage' => 0.00,
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'SILVER',
                'min_spending' => 5000.0000,
                'max_spending' => 24999.9999,
                'discount_percentage' => 0.50,
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'GOLD',
                'min_spending' => 25000.0000,
                'max_spending' => 99999.9999,
                'discount_percentage' => 1.00,
                'status' => 'active',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PLATINUM',
                'min_spending' => 100000.0000,
                'max_spending' => null,
                'discount_percentage' => 2.00,
                'status' => 'active',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_tiers');
    }
};
