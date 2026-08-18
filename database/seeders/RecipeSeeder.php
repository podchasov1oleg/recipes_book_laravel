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
        for ($i = 0; $i < 10; $i++) {
            Recipe::factory()
                ->hasAttached(Product::inRandomOrder()->take(rand(1, 5))->get())
                ->create();
        }
    }
}
