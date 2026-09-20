<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produit;

class ProduitSeeder extends Seeder
{
    public function run(): void
    {
        $produits = [
            ['categorie_id' => 1, 'nom' => 'Grain de Diamant Premium', 'prix_metre' => 2500, 'prix_rouleau' => 45000, 'prix_pantalon' => 5000, 'prix_chemise' => 4500, 'prix_tunique' => 4000, 'stock' => 100],
            ['categorie_id' => 2, 'nom' => 'Morrocco Classique', 'prix_metre' => 3000, 'prix_rouleau' => 50000, 'prix_pantalon' => 6000, 'prix_chemise' => 5000, 'prix_tunique' => 4500, 'stock' => 80],
            ['categorie_id' => 3, 'nom' => 'Duchesse Brillant', 'prix_metre' => 3500, 'prix_rouleau' => 60000, 'prix_pantalon' => 7000, 'prix_chemise' => 6000, 'prix_tunique' => 5500, 'stock' => 60],
            ['categorie_id' => 4, 'nom' => 'Duchesse Italien Gold', 'prix_metre' => 4000, 'prix_rouleau' => 70000, 'prix_pantalon' => 8000, 'prix_chemise' => 7000, 'prix_tunique' => 6500, 'stock' => 50],
            ['categorie_id' => 5, 'nom' => 'Supercant Luxe', 'prix_metre' => 2000, 'prix_rouleau' => 35000, 'prix_pantalon' => 4000, 'prix_chemise' => 3500, 'prix_tunique' => 3000, 'stock' => 120],
            ['categorie_id' => 6, 'nom' => '360 Premium', 'prix_metre' => 2500, 'prix_rouleau' => 40000, 'prix_pantalon' => 5000, 'prix_chemise' => 4000, 'prix_tunique' => 3500, 'stock' => 90],
            ['categorie_id' => 7, 'nom' => 'Soie Japonais Pure', 'prix_metre' => 4500, 'prix_rouleau' => 80000, 'prix_pantalon' => 9000, 'prix_chemise' => 8000, 'prix_tunique' => 7500, 'stock' => 40],
            ['categorie_id' => 8, 'nom' => 'Tuile Tirant Classic', 'prix_metre' => 2000, 'prix_rouleau' => 35000, 'prix_pantalon' => 4000, 'prix_chemise' => 3500, 'prix_tunique' => 3000, 'stock' => 70],
            ['categorie_id' => 9, 'nom' => 'Tuile Brillant Gold', 'prix_metre' => 2500, 'prix_rouleau' => 42000, 'prix_pantalon' => 5000, 'prix_chemise' => 4500, 'prix_tunique' => 4000, 'stock' => 60],
            ['categorie_id' => 10, 'nom' => 'Dentelle 1000F Special', 'prix_metre' => 1000, 'prix_rouleau' => 18000, 'prix_pantalon' => 2000, 'prix_chemise' => 1800, 'prix_tunique' => 1500, 'stock' => 150],
            ['categorie_id' => 11, 'nom' => 'Dentelle Chère Premium', 'prix_metre' => 5000, 'prix_rouleau' => 90000, 'prix_pantalon' => 10000, 'prix_chemise' => 9000, 'prix_tunique' => 8000, 'stock' => 30],
            ['categorie_id' => 12, 'nom' => 'Coton Glacé Frais', 'prix_metre' => 1500, 'prix_rouleau' => 25000, 'prix_pantalon' => 3000, 'prix_chemise' => 2500, 'prix_tunique' => 2000, 'stock' => 200],
            ['categorie_id' => 13, 'nom' => 'Jeans Premium', 'prix_metre' => 2000, 'prix_rouleau' => 35000, 'prix_pantalon' => 4000, 'prix_chemise' => 3500, 'prix_tunique' => 3000, 'stock' => 100],
            ['categorie_id' => 14, 'nom' => 'Obama Classic', 'prix_metre' => 3000, 'prix_rouleau' => 55000, 'prix_pantalon' => 6000, 'prix_chemise' => 5000, 'prix_tunique' => 4500, 'stock' => 80],
        ];

        foreach ($produits as $produit) {
            Produit::create($produit);
        }
    }
}
