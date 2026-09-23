<?php

namespace Database\Seeders;

use App\Models\Household;
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
        $household = Household::first();

        for ($i = 0; $i < 10; $i++) {
            Recipe::factory()
                ->for($household)
                ->hasAttached(
                    Product::inRandomOrder()->take(rand(1, 5))->get(),
                    ['quantity' => rand(1, 500)],
                )
                ->create();
        }
    }
}
