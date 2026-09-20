<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CategorieSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Grain de Diamant', 'Morrocco', 'Duchesse',
            'Duchesse Italien', 'Supercant', '360',
            'Soie Japonais', 'Tuile Tirant', 'Tuile Brillant',
            'Dentelle 1000F', 'Dentelle Chère', 'Coton Glacé',
            'Jeans', 'Obama'
        ];

        foreach ($categories as $cat) {
            \App\Models\Categorie::create(['nom' => $cat]);
        }
    }
}