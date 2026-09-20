<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Models\Produit;
use Illuminate\Support\Str;

#[Signature('app:generate-product-slugs')]
#[Description('Génère les slugs pour tous les produits existants')]
class GenerateProductSlugs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $produits = Produit::all();
        $count = 0;

        $this->info('Génération des slugs en cours...');

        foreach ($produits as $produit) {
            $slug = Str::slug($produit->nom);

            // Gérer les doublons de slugs
            $originalSlug = $slug;
            $i = 1;
            while (Produit::where('slug', $slug)->where('id', '!=', $produit->id)->exists()) {
                $slug = $originalSlug . '-' . $i++;
            }

            $produit->update(['slug' => $slug]);
            $count++;
        }

        $this->info("Succès ! $count slugs ont été générés.");
    }
}
