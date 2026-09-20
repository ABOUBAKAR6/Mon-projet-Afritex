<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommandeFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_coupon_can_be_applied_and_orders_are_listed_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $produit = Produit::factory()->create([
            'nom' => 'Tissu test',
            'slug' => 'tissu-test',
            'prix_metre' => 10000,
            'prix_rouleau' => 50000,
            'prix_pantalon' => 0,
            'prix_chemise' => 0,
            'prix_tunique' => 0,
        ]);

        Coupon::create([
            'code' => 'SAVE10',
            'type' => 'percent',
            'value' => 10,
            'active' => true,
            'minimum_order' => 1000,
            'max_uses' => 10,
            'uses' => 0,
        ]);

        $this->actingAs($user);

        $this->post(route('panier.ajouter'), [
            'produit_id' => $produit->id,
            'type_commande' => 'metre',
            'quantite' => 1,
        ])->assertRedirect(route('panier'));

        $this->post(route('panier.coupon.appliquer'), [
            'coupon_code' => 'save10',
        ])->assertRedirect(route('panier'));

        $this->get(route('commandes.index'))->assertOk();
    }
}
