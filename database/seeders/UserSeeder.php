<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Сидер таблицы пользователей
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Подчасов Олег',
            'email' => 'podchasov.oleg@gmail.com',
            'password' => Hash::make('podchasov'),
        ]);
    }
}
