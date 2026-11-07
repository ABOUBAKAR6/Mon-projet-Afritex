<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Couleur de tissu souhaitée (page produit) et remarques du panier : elles suivent
 * le panier jusqu'à la commande, où la boutique les retrouve.
 */
class CouleurRemarquesTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_couleur_et_les_remarques_vont_jusqu_a_la_commande(): void
    {
        Event::fake();
        $categorie = Categorie::create(['nom' => 'Wax']);
        $tissu = Produit::create(['categorie_id' => $categorie->id, 'nom' => 'Duchesse unie', 'stock' => 10, 'disponible' => true, 'prix_metre' => 3000, 'prix_rouleau' => 50000]);
        $client = User::factory()->create();
        $this->actingAs($client);

        // Deux couleurs du même tissu = deux lignes ; la même couleur s'additionne
        $this->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'metre', 'quantite' => 3, 'couleur' => ' bleu nuit '])->assertSessionHasNoErrors();
        $this->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'metre', 'quantite' => 2, 'couleur' => 'rouge bordeaux']);
        $this->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'metre', 'quantite' => 1, 'couleur' => 'bleu nuit']);
        $this->assertSame(['bleu nuit' => 4, 'rouge bordeaux' => 2], CartItem::orderBy('couleur')->pluck('quantite', 'couleur')->map(fn ($q) => (int) $q)->all());

        // Remarques enregistrées depuis le panier (appel en arrière-plan)
        $this->postJson(route('panier.remarques'), ['remarques' => 'Même bain de teinture, livraison après 17 h'])->assertOk();
        $this->get(route('panier'))->assertOk()
            ->assertSee('Couleur :', false)->assertSee('bleu nuit')->assertSee('Même bain de teinture', false);

        $this->post(route('commande.confirmer'), ['adresse_livraison' => 'Cocody, Abidjan', 'mode_livraison' => 'domicile']);

        $commande = Commande::with('items')->sole();
        $this->assertSame('Même bain de teinture, livraison après 17 h', $commande->note_client);
        $this->assertEqualsCanonicalizing(['bleu nuit', 'rouge bordeaux'], $commande->items->pluck('couleur')->all());
        $this->assertNull(session('panier_remarques'));
    }

    public function test_sans_couleur_rien_n_est_enregistre_et_une_couleur_trop_longue_est_refusee(): void
    {
        $categorie = Categorie::create(['nom' => 'Wax']);
        $tissu = Produit::create(['categorie_id' => $categorie->id, 'nom' => 'Soie', 'stock' => 5, 'disponible' => true, 'prix_metre' => 2000]);
        $this->actingAs(User::factory()->create());

        $this->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'metre', 'quantite' => 1, 'couleur' => '']);
        $this->assertNull(CartItem::sole()->couleur);

        $this->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'metre', 'couleur' => str_repeat('x', 101)])
            ->assertSessionHasErrors('couleur');
    }
}
