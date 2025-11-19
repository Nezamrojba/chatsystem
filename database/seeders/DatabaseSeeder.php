<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed Mazen user
        User::updateOrCreate(
            ['username' => 'Mazen'],
            [
                'name' => 'Mazen',
                'username' => 'Mazen',
                'password' => Hash::make('password'),
            ]
        );

        // Seed Maher user
        User::updateOrCreate(
            ['username' => 'Maher'],
            [
                'name' => 'Maher',
                'username' => 'Maher',
                'password' => Hash::make('password'),
            ]
        );
    }
}
