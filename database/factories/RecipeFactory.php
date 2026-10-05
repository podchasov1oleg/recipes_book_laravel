<?php

namespace Database\Factories;

use App\Models\Household;
use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->unique()->word(),
            'servings' => $this->faker->numberBetween(1, 8),
            'household_id' => Household::factory(),
        ];
    }
}
