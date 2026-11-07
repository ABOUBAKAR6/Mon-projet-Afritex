<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seuls « Au mètre » et « Au rouleau » sont en vente : les confections (pantalon, chemise,
 * tunique) n'apparaissent plus et le panier les refuse, même si un prix reste en base.
 */
class FormatsVenteTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_page_produit_et_le_panier_ne_proposent_que_le_metre_et_le_rouleau(): void
    {
        $categorie = Categorie::create(['nom' => 'Wax']);
        $tissu = Produit::create([
            'categorie_id' => $categorie->id, 'nom' => 'Grain de diamant bleu', 'stock' => 10, 'disponible' => true,
            'prix_metre' => 3000, 'prix_rouleau' => 50000, 'prix_pantalon' => 6000, 'prix_chemise' => 5000, 'prix_tunique' => 4500,
        ]);

        $this->get(route('produit.show', $tissu->slug))->assertOk()
            ->assertSee('Au mètre')->assertSee('Au rouleau')
            ->assertDontSee('Pantalon')->assertDontSee('Chemise manches')->assertDontSee('Tunique manches');
        $this->get(route('catalogue'))->assertOk()->assertDontSee('Aussi en confection');

        $client = User::factory()->create();
        $this->actingAs($client)->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'pantalon', 'quantite' => 1])
            ->assertSessionHasErrors('type_commande');
        $this->actingAs($client)->post(route('panier.ajouter'), ['produit_id' => $tissu->id, 'type_commande' => 'metre', 'quantite' => 3])
            ->assertSessionHasNoErrors()->assertRedirect(route('panier'));

        $this->assertSame(['metre', 'rouleau'], array_keys(Produit::FORMATS));
    }
}
