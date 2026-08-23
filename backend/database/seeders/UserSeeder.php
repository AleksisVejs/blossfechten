<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Seeded accounts are marked verified: nobody is going to click a link
        // in a mailbox that does not exist, and an account left unverified is
        // one the notification fan-out silently skips.
        User::updateOrCreate(
            ['email' => 'admin@blossfechten.lv'],
            [
                'name' => 'Club Administrator',
                'password' => Hash::make('ChangeMe!2026'),
                'role' => 'admin',
                'locale' => 'lv',
                'rank' => 'Fechtmeister',
                'email_verified_at' => now(),
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
                'email_verified_at' => now(),
            ]
        );
    }
}
