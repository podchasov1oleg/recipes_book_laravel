<?php

use App\Models\MenuDay;
use App\Models\Recipe;
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
        Schema::create('menu_day_recipe', function (Blueprint $table) {
            $table->foreignIdFor(MenuDay::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Recipe::class)->constrained()->cascadeOnDelete();

            $table->primary(['menu_day_id', 'recipe_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_day_recipe');
    }
};
