<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Couleurs d'un tissu : saisies dans l'admin (texte libre séparé par des virgules),
 * proposées en pastilles sur la page produit, et le catalogue en 2 colonnes au téléphone.
 */
class CouleursProduitTest extends TestCase
{
    use RefreshDatabase;

    public function test_l_admin_enregistre_les_couleurs_et_la_page_produit_les_propose(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $produit = $this->produit();

        $this->actingAs($admin)->post(route('admin.produits.edit', $produit->id), [
            'nom' => $produit->nom,
            'categorie_id' => $produit->categorie_id,
            'tarifications' => [['label' => 'Au mètre', 'prix' => 3000]],
            'couleurs' => "bordeaux, Bleu nuit ;noir\n BORDEAUX ,, ",
        ])->assertRedirect(route('admin.produits'));

        $this->assertSame(['Bordeaux', 'Bleu nuit', 'Noir'], $produit->fresh()->couleurs);

        $page = $this->get(route('produit.show', $produit->slug))->assertOk();
        $page->assertSee('titre-couleurs', false);
        $page->assertSeeInOrder(['Bordeaux', 'Bleu nuit', 'Noir', 'Autre']);
        // Une seule couleur envoyée au panier : le champ caché
        $this->assertSame(1, substr_count($page->getContent(), 'name="couleur"'));
    }

    public function test_sans_couleurs_le_champ_libre_reste_et_vider_le_champ_efface_les_couleurs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $produit = $this->produit(['couleurs' => ['Or']]);

        $this->actingAs($admin)->post(route('admin.produits.edit', $produit->id), [
            'nom' => $produit->nom,
            'categorie_id' => $produit->categorie_id,
            'tarifications' => [['label' => 'Au mètre', 'prix' => 3000]],
            'couleurs' => '',
        ]);

        $this->assertNull($produit->fresh()->couleurs);

        $page = $this->get(route('produit.show', $produit->slug))->assertOk();
        $page->assertDontSee('titre-couleurs', false);
        $page->assertSee('Couleur souhaitée');
    }

    public function test_le_catalogue_affiche_les_cartes_compactes_en_deux_colonnes(): void
    {
        $this->produit();

        $this->get(route('catalogue'))->assertOk()->assertSee('grid grid-cols-2', false);
    }

    private function produit(array $attributs = []): Produit
    {
        $categorie = Categorie::create(['nom' => 'Grain de Diamant']);

        return Produit::create($attributs + [
            'categorie_id' => $categorie->id,
            'nom' => 'Grain de diamant bordeaux',
            'prix_metre' => 3000,
            'prix_rouleau' => 50000,
            'stock' => 5,
            'disponible' => 1,
        ]);
    }
}
