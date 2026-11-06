<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Compte propriétaire fourni par le .env : chaque boutique crée le sien.
        // En production, pas de valeur par défaut : un mot de passe connu ou vide ouvrirait l'administration.
        $email = (string) env('SEED_ADMIN_EMAIL');
        $motDePasse = (string) env('SEED_ADMIN_PASSWORD');

        if (app()->isProduction()) {
            if ($email === '' || strlen($motDePasse) < 10) {
                throw new RuntimeException('Renseignez SEED_ADMIN_EMAIL et SEED_ADMIN_PASSWORD (10 caractères au moins) dans le .env avant de créer le compte administrateur.');
            }
        } else {
            $email = $email ?: 'admin@afritex.local';
            $motDePasse = $motDePasse ?: 'admin123';
        }

        User::updateOrCreate(['email' => $email], [
            'name' => 'Administrateur',
            'password' => Hash::make($motDePasse),
            'role' => 'admin',
        ]);

        // Client de démonstration : uniquement en développement, jamais sur une boutique en ligne
        if (! app()->isProduction()) {
            User::updateOrCreate(['email' => env('SEED_CLIENT_EMAIL') ?: 'client@afritex.local'], [
                'name' => 'Client',
                'password' => Hash::make(env('SEED_CLIENT_PASSWORD') ?: 'client123'),
                'role' => 'client',
            ]);
        }
    }
}
