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
        Schema::table('menu_days', function (Blueprint $table) {
            $table->dropUnique(['day']);
            $table->unique(['household_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_days', function (Blueprint $table) {
            $table->index('household_id');
            $table->dropUnique(['household_id', 'day']);
            $table->unique(['day']);
        });
    }
};
