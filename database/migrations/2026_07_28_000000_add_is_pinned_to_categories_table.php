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
        if (Schema::hasTable('categories') && !Schema::hasColumn('categories', 'is_pinned')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->tinyInteger('is_pinned')->default(0)->after('sort_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories') && Schema::hasColumn('categories', 'is_pinned')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('is_pinned');
            });
        }
    }
};
