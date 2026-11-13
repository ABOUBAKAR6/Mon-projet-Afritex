<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Commande sans compte : le visiteur remplit son panier, donne ses coordonnées à l'étape Livraison,
 * son compte est créé à la confirmation et il passe au paiement. Un e-mail déjà pris est refusé.
 */
class CommandeSansCompteTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_commande_sans_creer_de_compte(): void
    {
        Mail::fake();
        $produit = $this->produit();

        $this->post(route('panier.ajouter'), ['produit_id' => $produit->id, 'type_commande' => 'metre', 'quantite' => 3, 'couleur' => 'Or'])
            ->assertRedirect(route('panier'));
        $this->get(route('panier'))->assertOk()->assertSee('Morrocco Classique');
        $this->get(route('commande'))->assertOk()->assertSee('id="email"', false);

        $reponse = $this->post(route('commande.confirmer'), $this->coordonnees());

        $client = User::where('name', 'Awa Koné')->firstOrFail();
        $this->assertTrue($client->estSansEmail());
        $this->assertSame('+2250701020304', $client->phone);
        $this->assertAuthenticatedAs($client);

        $commande = Commande::where('user_id', $client->id)->firstOrFail();
        $reponse->assertRedirect(route('paiement', $commande->id));
        $this->assertSame('Or', $commande->items()->first()->couleur);
        $this->assertEquals(3, $commande->items()->first()->quantite);

        // Pas d'e-mail vers une adresse fictive
        $this->assertNull($client->routeNotificationForMail());
        $this->assertFalse($commande->envoyerConfirmation(false));
        Mail::assertNothingSent();
    }

    public function test_un_email_deja_utilise_n_ouvre_pas_le_compte_d_un_autre(): void
    {
        $existant = User::factory()->create(['email' => 'deja@exemple.com']);
        $produit = $this->produit();

        $this->post(route('panier.ajouter'), ['produit_id' => $produit->id, 'type_commande' => 'metre', 'quantite' => 1]);
        $this->from(route('commande'))->post(route('commande.confirmer'), $this->coordonnees(['email' => 'DEJA@exemple.com']))
            ->assertRedirect(route('commande'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame(0, Commande::where('user_id', $existant->id)->count());
    }

    public function test_coordonnees_obligatoires_pour_un_visiteur(): void
    {
        $produit = $this->produit();
        $this->post(route('panier.ajouter'), ['produit_id' => $produit->id, 'type_commande' => 'metre', 'quantite' => 1]);

        $this->post(route('commande.confirmer'), ['mode_livraison' => 'domicile'])
            ->assertSessionHasErrors(['prenom', 'nom_complet', 'telephone']);
        $this->assertGuest();
    }

    private function coordonnees(array $en_plus = []): array
    {
        return $en_plus + [
            'prenom' => 'Awa',
            'nom_complet' => 'Koné',
            'indicatif' => '+225',
            'telephone' => '07 01 02 03 04',
            'email' => '',
            'mode_livraison' => 'domicile',
            'adresse_livraison' => 'Awa Koné | +2250701020304 | Abidjan, Cocody',
        ];
    }

    private function produit(): Produit
    {
        $categorie = Categorie::create(['nom' => 'Morrocco']);

        return Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => 'Morrocco Classique',
            'prix_metre' => 3000,
            'stock' => 5,
            'disponible' => 1,
        ]);
    }
}
