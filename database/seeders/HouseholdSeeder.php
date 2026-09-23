<?php

namespace Database\Seeders;

use App\Models\Household;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Сидер таблицы домохозяйств
 */
class HouseholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();
        Household::create(['name' => 'Подчасовы', 'user_id' => $user->id]);
    }
}
