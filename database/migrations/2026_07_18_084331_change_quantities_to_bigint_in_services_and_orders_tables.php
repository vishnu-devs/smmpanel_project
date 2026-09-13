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
        Schema::table('services', function (Blueprint $table) {
            $table->bigInteger('min_quantity')->change();
            $table->bigInteger('max_quantity')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('quantity')->change();
            $table->bigInteger('start_count')->default(0)->change();
            $table->bigInteger('remains')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->integer('min_quantity')->change();
            $table->integer('max_quantity')->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('start_count')->default(0)->change();
            $table->integer('remains')->default(0)->change();
        });
    }
};
