<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@blossfechten.lv'],
            [
                'name' => 'Club Administrator',
                'password' => Hash::make('ChangeMe!2026'),
                'role' => 'admin',
                'locale' => 'lv',
                'rank' => 'Fechtmeister',
            ]
        );

        User::updateOrCreate(
            ['email' => 'member@blossfechten.lv'],
            [
                'name' => 'Demo Member',
                'password' => Hash::make('member123'),
                'role' => 'member',
                'locale' => 'en',
                'rank' => 'Scholar',
            ]
        );
    }
}
