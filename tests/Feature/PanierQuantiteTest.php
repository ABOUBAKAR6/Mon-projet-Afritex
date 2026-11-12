<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Categorie;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Boutons – et + du panier : la quantité d'une ligne change sans recharger la page,
 * les totaux et le message WhatsApp reviennent à jour, et une ligne d'un autre client reste intouchable.
 */
class PanierQuantiteTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_client_change_la_quantite_d_une_ligne_et_recoit_les_totaux(): void
    {
        $client = User::factory()->create();
        $produit = $this->produit();

        $this->actingAs($client)->post(route('panier.ajouter'), ['produit_id' => $produit->id, 'type_commande' => 'metre', 'quantite' => 2]);
        $ligne = CartItem::firstOrFail();

        $reponse = $this->actingAs($client)->patchJson(route('panier.quantite', $ligne->id), ['quantite' => 5])->assertOk();

        $this->assertSame(5, (int) $ligne->fresh()->quantite);
        $this->assertEquals(15000, $reponse->json('ligne'));
        $this->assertEquals(15000, $reponse->json('total'));
        $this->assertStringContainsString('Au mètre : 5 m', $reponse->json('message'));

        // La page du panier montre la barre « Commander » à la place de la barre d'accès rapides
        $page = $this->actingAs($client)->get(route('panier'))->assertOk();
        $page->assertSee('Commander');
        $page->assertDontSee('aria-label="Accès rapides"', false);
    }

    public function test_quantite_invalide_ou_ligne_d_un_autre_client_refusees(): void
    {
        $proprietaire = User::factory()->create();
        $autre = User::factory()->create();
        $produit = $this->produit();

        $this->actingAs($proprietaire)->post(route('panier.ajouter'), ['produit_id' => $produit->id, 'type_commande' => 'metre', 'quantite' => 1]);
        $ligne = CartItem::firstOrFail();

        $this->actingAs($proprietaire)->patchJson(route('panier.quantite', $ligne->id), ['quantite' => 0])->assertStatus(422);
        $this->actingAs($autre)->patchJson(route('panier.quantite', $ligne->id), ['quantite' => 9])->assertNotFound();

        $this->assertSame(1, (int) $ligne->fresh()->quantite);
    }

    private function produit(): Produit
    {
        $categorie = Categorie::create(['nom' => 'Morrocco']);

        return Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Morrocco rouge',
            'prix_metre' => 3000,
            'prix_rouleau' => 50000,
            'stock' => 5,
            'disponible' => 1,
        ]);
    }
}
