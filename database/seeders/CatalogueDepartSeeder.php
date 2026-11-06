<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Catalogue de départ d'une nouvelle boutique : catégories, produits (prix détail et pro, stock)
 * et photos supplémentaires, repris de database/data/catalogue-depart.json.
 * Les photos elles-mêmes sont dans public/images (versionnées avec le code).
 *
 * À lancer une seule fois, sur une base neuve :
 *   php artisan db:seed --class=CatalogueDepartSeeder --force
 * Ni clients, ni commandes, ni comptes : seulement le catalogue.
 */
class CatalogueDepartSeeder extends Seeder
{
    public function run(): void
    {
        $fichier = database_path('data/catalogue-depart.json');
        if (! is_file($fichier)) {
            throw new RuntimeException('Fichier introuvable : database/data/catalogue-depart.json');
        }

        if (DB::table('categories')->exists() || DB::table('produits')->exists()) {
            throw new RuntimeException('Cette boutique a déjà des catégories ou des produits : import annulé pour ne pas créer de doublons.');
        }

        $catalogue = json_decode((string) file_get_contents($fichier), true, 512, JSON_THROW_ON_ERROR);
        $maintenant = now();

        // Une photo introuvable (ou au nom trop long pour la colonne) n'est pas reprise :
        // le produit est importé sans elle plutôt que de bloquer tout l'import.
        $photoValide = fn (?string $chemin) => $chemin && strlen($chemin) <= 255 && is_file(public_path($chemin));
        $retirees = 0;
        foreach (['categories', 'produits'] as $table) {
            foreach ($catalogue[$table] as &$ligne) {
                if ($ligne['image'] && ! $photoValide($ligne['image'])) {
                    $ligne['image'] = null;
                    $retirees++;
                }
            }
            unset($ligne);
        }
        $avant = count($catalogue['produit_images']);
        $catalogue['produit_images'] = array_values(array_filter($catalogue['produit_images'], fn ($p) => $photoValide($p['chemin'])));
        $retirees += $avant - count($catalogue['produit_images']);
        $avecDates = fn (array $lignes) => array_map(fn ($ligne) => $ligne + ['created_at' => $maintenant, 'updated_at' => $maintenant], $lignes);

        DB::transaction(function () use ($catalogue, $avecDates) {
            // Identifiants conservés : les produits pointent vers leurs catégories, les photos vers leurs produits
            foreach (array_chunk($avecDates($catalogue['categories']), 100) as $lot) {
                DB::table('categories')->insert($lot);
            }
            foreach (array_chunk($avecDates($catalogue['produits']), 100) as $lot) {
                DB::table('produits')->insert($lot);
            }
            foreach (array_chunk($avecDates($catalogue['produit_images']), 100) as $lot) {
                DB::table('produit_images')->insert($lot);
            }
        });

        $this->command?->info(sprintf(
            'Catalogue importé : %d catégories, %d produits, %d photos supplémentaires.%s',
            count($catalogue['categories']),
            count($catalogue['produits']),
            count($catalogue['produit_images']),
            $retirees ? " {$retirees} photo(s) introuvable(s) non reprise(s) : à renvoyer depuis l'administration." : ''
        ));
    }
}
