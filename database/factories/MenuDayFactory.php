<?php

namespace Database\Factories;

use App\Models\MenuDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuDay>
 */
class MenuDayFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'day' => $this->faker->unique()->date(),
        ];
    }
}
