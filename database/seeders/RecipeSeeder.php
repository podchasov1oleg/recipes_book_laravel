<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Recipe::factory()
             ->hasAttached(Product::inRandomOrder()->take(3)->get())
             ->count(10)
             ->create();
    }
}
