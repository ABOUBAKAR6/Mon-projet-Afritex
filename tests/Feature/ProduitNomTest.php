<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Deux produits ne peuvent pas porter le même nom (adresse unique) : la boutique
 * reçoit un message clair au lieu d'une erreur 500, à l'ajout comme à la modification.
 */
class ProduitNomTest extends TestCase
{
    use RefreshDatabase;

    public function test_ajouter_deux_fois_le_meme_produit_affiche_un_message_au_lieu_d_une_erreur_500(): void
    {
        $proprietaire = User::factory()->create(['role' => 'admin']);
        $categorie = Categorie::create(['nom' => 'Wax']);
        $formulaire = ['nom' => 'Chanel', 'categorie_id' => $categorie->id, 'tarifications' => [['label' => 'Au mètre', 'prix' => 600]]];

        $this->actingAs($proprietaire)->post(route('admin.produits.ajouter'), $formulaire)
            ->assertRedirect(route('admin.produits'));

        // Second envoi (double appui depuis une tablette) : refusé proprement
        $this->actingAs($proprietaire)->from(route('admin.produits'))->post(route('admin.produits.ajouter'), $formulaire)
            ->assertRedirect(route('admin.produits'))
            ->assertSessionHasErrors(['nom' => 'Un produit nommé « Chanel » existe déjà. Donnez un autre nom (ex. « Chanel bleu ») ou modifiez celui qui existe.']);
        $this->assertSame(1, Produit::where('slug', 'chanel')->count());

        // Même nom écrit autrement (majuscules) : même adresse, même refus
        $this->actingAs($proprietaire)->post(route('admin.produits.ajouter'), ['nom' => 'CHANEL'] + $formulaire)->assertSessionHasErrors('nom');

        $this->actingAs($proprietaire)->get(route('admin.produits'))->assertOk();
    }

    public function test_renommer_un_produit_avec_un_nom_deja_pris_est_refuse_mais_garder_son_nom_passe(): void
    {
        $proprietaire = User::factory()->create(['role' => 'admin']);
        $categorie = Categorie::create(['nom' => 'Wax']);
        $chanel = Produit::create(['nom' => 'Chanel', 'categorie_id' => $categorie->id, 'stock' => 0, 'disponible' => true]);
        $duchesse = Produit::create(['nom' => 'Duchesse', 'categorie_id' => $categorie->id, 'stock' => 0, 'disponible' => true]);

        $this->actingAs($proprietaire)->post(route('admin.produits.edit', $duchesse->id), ['nom' => 'Chanel', 'categorie_id' => $categorie->id])
            ->assertSessionHasErrors('nom');
        $this->assertSame('Duchesse', $duchesse->refresh()->nom);

        $this->actingAs($proprietaire)->post(route('admin.produits.edit', $chanel->id), ['nom' => 'Chanel', 'categorie_id' => $categorie->id, 'description' => 'Doux'])
            ->assertSessionHasNoErrors();
        $this->assertSame('Doux', $chanel->refresh()->description);
    }
}
