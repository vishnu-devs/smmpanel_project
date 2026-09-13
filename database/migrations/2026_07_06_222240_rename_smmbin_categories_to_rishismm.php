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
        try {
            $categories = \App\Models\Category::all();
            foreach ($categories as $cat) {
                if (stripos($cat->name, 'smmbin') !== false) {
                    $cat->name = str_ireplace('smmbin', 'RishiSMM', $cat->name);
                    $cat->save();
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action
    }
};
