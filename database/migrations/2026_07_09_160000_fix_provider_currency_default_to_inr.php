<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix database-level bugs:
     * 1. Update providers.currency column default from 'USD' to 'INR'
     * 2. Update any existing providers with currency='USD' to 'INR'
     *    (since we've hardcoded INR everywhere in the app)
     * 
     * NOTE: This does NOT delete any data. Only updates defaults and values.
     */
    public function up(): void
    {
        // 1. Change column default from 'USD' to 'INR'
        DB::statement("ALTER TABLE `providers` ALTER COLUMN `currency` SET DEFAULT 'INR'");

        // 2. Update all existing providers to use INR currency
        DB::table('providers')->where('currency', '!=', 'INR')->update(['currency' => 'INR']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `providers` ALTER COLUMN `currency` SET DEFAULT 'USD'");
    }
};
