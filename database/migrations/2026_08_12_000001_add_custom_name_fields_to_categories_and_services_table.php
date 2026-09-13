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
        Schema::table('categories', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('name');
            $table->boolean('is_custom_name')->default(false)->after('original_name');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('original_name')->nullable()->after('name');
            $table->boolean('is_custom_name')->default(false)->after('original_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'is_custom_name']);
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['original_name', 'is_custom_name']);
        });
    }
};
