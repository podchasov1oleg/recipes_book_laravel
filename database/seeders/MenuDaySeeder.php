<?php

namespace Database\Seeders;

use App\Models\MenuDay;
use App\Models\Recipe;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class MenuDaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // создать дни текущей недели
        $monday = Carbon::now()->startOfWeek(CarbonInterface::MONDAY);
        $weekDays = [];

        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = $monday->copy()->addDays($i);
        }

        $collection = collect($weekDays)->random(rand(3, 6));

        foreach ($collection as $item) {
             MenuDay::factory()
                 ->hasAttached(Recipe::inRandomOrder()->take(rand(1,3))->get())
                 ->create(['day' => $item]);
        }
    }
}
