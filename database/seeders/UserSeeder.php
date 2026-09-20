<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Créer un utilisateur admin
        User::create([
            'name' => 'Admin',
            'email' => 'admin@afritex.local',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Créer un utilisateur client
        User::create([
            'name' => 'Client',
            'email' => 'client@afritex.local',
            'password' => Hash::make('client123'),
            'role' => 'client',
        ]);
    }
}
