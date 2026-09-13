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
        // Modify providers.api_key to text type
        Schema::table('providers', function (Blueprint $table) {
            $table->text('api_key')->change();
        });

        // Add screenshot field to transactions table
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('screenshot')->nullable()->after('payment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('screenshot');
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->string('api_key')->change();
        });
    }
};
