<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\ComptePro;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\EspaceProNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Espace Pro (vente en gros), de bout en bout : demande de compte, validation par la boutique,
 * pages du revendeur, demande de devis, chiffrage, acceptation, et les accès refusés.
 */
class EspaceProTest extends TestCase
{
    use RefreshDatabase;

    private User $proprietaire;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Mail::fake();
        // Les messages partent après la réponse (defer) : ici on les exécute tout de suite
        $this->withoutDefer();

        $this->proprietaire = User::factory()->create(['role' => 'admin']);
    }

    public function test_un_visiteur_demande_un_compte_pro_et_le_proprietaire_le_valide(): void
    {
        $categorie = Categorie::create(['nom' => 'Wax']);

        $this->get(route('pro.accueil'))->assertOk()->assertViewIs('pro.accueil');

        $this->post(route('pro.inscription.envoyer'), [
            'name' => 'Awa Koné',
            'email' => 'awa@atelier.test',
            'password' => 'motdepasse-solide',
            'password_confirmation' => 'motdepasse-solide',
            'boutique' => 'Atelier Awa',
            'activite' => 'atelier',
            'ville' => 'Abidjan',
            'commune' => 'Cocody',
            'whatsapp' => '07 07 07 07 07',
            'volume_mensuel' => '100k_300k',
            'familles' => [$categorie->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('pro.accueil'));

        $revendeur = User::where('email', 'awa@atelier.test')->firstOrFail();
        $this->assertAuthenticatedAs($revendeur);
        $this->assertSame('client', $revendeur->role);

        $compte = $revendeur->comptePro;
        $this->assertNotNull($compte);
        $this->assertSame('en_attente', $compte->statut);
        $this->assertSame('Atelier Awa', $compte->boutique);
        $this->assertEquals([$categorie->id], $compte->familles);
        $this->assertFalse($revendeur->estProValide());

        // L'équipe est prévenue de la demande
        Notification::assertSentTo($this->proprietaire, EspaceProNotification::class);

        // Le propriétaire examine et valide
        $this->actingAs($this->proprietaire)
            ->get(route('admin.pros'))
            ->assertOk()
            ->assertSee('Atelier Awa');

        $this->actingAs($this->proprietaire)
            ->post(route('admin.pros.valider', $compte))
            ->assertRedirect();

        $compte->refresh();
        $this->assertSame('valide', $compte->statut);
        $this->assertNotNull($compte->valide_le);
        $this->assertTrue($revendeur->fresh()->estProValide());
        Notification::assertSentTo($revendeur, EspaceProNotification::class);
    }

    public function test_le_revendeur_valide_accede_a_toutes_ses_pages(): void
    {
        $this->produitPro();
        $revendeur = $this->revendeurValide();

        $this->actingAs($revendeur)->get(route('pro.accueil'))->assertOk()->assertViewIs('pro.tableau');
        $this->actingAs($revendeur)->get(route('pro.commander'))->assertOk()->assertSee('Wax Kente');
        $this->actingAs($revendeur)->get(route('pro.commandes'))->assertOk();
        $this->actingAs($revendeur)->get(route('pro.compte'))->assertOk();

        $pdf = $this->actingAs($revendeur)->get(route('pro.catalogue'));
        $pdf->assertOk()->assertDownload();
        $this->assertStringContainsString('application/pdf', (string) $pdf->headers->get('Content-Type'));
    }

    public function test_parcours_complet_du_devis_de_la_demande_au_paiement(): void
    {
        $produit = $this->produitPro();
        $revendeur = $this->revendeurValide();

        // 1. Le revendeur demande un devis : 10 mètres au prix pro de 2 500 F
        $this->actingAs($revendeur)->post(route('pro.commander.envoyer'), $this->formulaireCommande(
            [$produit->id => ['metre' => 10]],
            ['note_client' => 'Livraison avant la fête si possible.'],
        ))->assertSessionHasNoErrors()->assertRedirect();

        $commande = Commande::where('user_id', $revendeur->id)->firstOrFail();
        $this->assertTrue($commande->est_pro);
        $this->assertSame('devis_demande', $commande->statut);
        $this->assertSame(25000, (int) $commande->total);
        $this->assertCount(1, $commande->items);
        $item = $commande->items->first();
        $this->assertSame('metre', $item->type_commande);
        $this->assertSame(10, (int) $item->quantite);
        $this->assertSame(2500, (int) $item->prix_unitaire);
        Notification::assertSentTo($this->proprietaire, EspaceProNotification::class);

        // 2. Le propriétaire chiffre le devis : remise sur le prix et frais de livraison
        $this->actingAs($this->proprietaire)
            ->get(route('admin.commande.detail', $commande))
            ->assertOk()
            ->assertSee('Chiffrer le devis');

        $this->actingAs($this->proprietaire)->post(route('admin.commande.devis', $commande), [
            'lignes' => [$item->id => ['quantite' => 10, 'prix_unitaire' => 2400]],
            'frais_livraison' => 5000,
            'livraison_prevue_le' => now()->addDays(3)->toDateString(),
            'note_boutique' => 'Stock vérifié.',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $commande->refresh();
        $this->assertSame('devis_envoye', $commande->statut);
        $this->assertSame(5000, (int) $commande->frais_livraison);
        $this->assertSame(24000 + 5000, (int) $commande->total);
        $this->assertNotNull($commande->devis_envoye_le);
        Notification::assertSentTo($revendeur, EspaceProNotification::class);

        // 3. Le revendeur voit son devis et l'accepte : il passe au paiement
        $this->actingAs($revendeur)
            ->get(route('pro.commande', $commande))
            ->assertOk()
            ->assertSee('Votre devis est prêt');

        $this->actingAs($revendeur)
            ->post(route('pro.commande.accepter', $commande))
            ->assertRedirect(route('paiement', $commande));

        $this->assertSame('en_attente', $commande->fresh()->statut);
    }

    public function test_le_revendeur_demande_une_modification_et_la_boutique_revoit_le_devis(): void
    {
        $produit = $this->produitPro();
        $revendeur = $this->revendeurValide();

        $this->actingAs($revendeur)->post(route('pro.commander.envoyer'), $this->formulaireCommande(
            [$produit->id => ['metre' => 10]],
            ['note_client' => 'Livraison le matin.'],
        ))->assertSessionHasNoErrors();
        $commande = Commande::where('user_id', $revendeur->id)->firstOrFail();
        $item = $commande->items->first();

        $this->actingAs($this->proprietaire)->post(route('admin.commande.devis', $commande), [
            'lignes' => [$item->id => ['quantite' => 10, 'prix_unitaire' => 2500]],
            'frais_livraison' => 3000,
        ])->assertSessionHasNoErrors();

        // Message vide : refusé, le devis reste à valider
        $this->actingAs($revendeur)
            ->post(route('pro.commande.modification', $commande), ['message' => ''])
            ->assertSessionHasErrors('message');
        $this->assertSame('devis_envoye', $commande->fresh()->statut);

        // Le revendeur écrit ce qu'il veut changer : le devis repart à la boutique
        $this->actingAs($revendeur)
            ->post(route('pro.commande.modification', $commande), ['message' => 'Pouvez-vous faire 2 300 F le mètre ?'])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('pro.commande', $commande));

        $commande->refresh();
        $this->assertSame('devis_demande', $commande->statut);
        $this->assertStringContainsString('Livraison le matin.', $commande->note_client);
        $this->assertStringContainsString('Pouvez-vous faire 2 300 F le mètre ?', $commande->note_client);
        Notification::assertSentTo($this->proprietaire, EspaceProNotification::class,
            fn (EspaceProNotification $n) => $n->type === 'devis_modification');

        $this->actingAs($revendeur)->get(route('pro.commande', $commande))
            ->assertOk()->assertSee('Votre demande de modification est transmise');
        $this->actingAs($this->proprietaire)->get(route('admin.commande.detail', $commande))
            ->assertOk()->assertSee('Revoir le devis')->assertSee('Renvoyer le devis modifié');

        // La boutique renvoie une nouvelle version, que le revendeur peut accepter
        $this->actingAs($this->proprietaire)->post(route('admin.commande.devis', $commande), [
            'lignes' => [$item->id => ['quantite' => 10, 'prix_unitaire' => 2300]],
            'frais_livraison' => 3000,
        ])->assertSessionHasNoErrors();
        $this->assertSame('devis_envoye', $commande->fresh()->statut);
        $this->assertSame(23000 + 3000, (int) $commande->fresh()->total);

        // Plus de modification possible une fois le devis accepté
        $this->actingAs($revendeur)->post(route('pro.commande.accepter', $commande));
        $this->actingAs($revendeur)
            ->post(route('pro.commande.modification', $commande), ['message' => 'Encore un changement'])
            ->assertSessionHas('error');
        $this->assertSame('en_attente', $commande->fresh()->statut);
    }

    public function test_un_format_a_la_piece_passe_dans_un_devis(): void
    {
        // « chemise » et « tunique » manquaient à l'ancien enum de commande_items.type_commande
        $produit = $this->produitPro('Bazin Riche', ['prix_chemise' => 12000, 'prix_pro' => ['chemise' => 9500]]);
        $revendeur = $this->revendeurValide();

        $this->actingAs($revendeur)->post(route('pro.commander.envoyer'), $this->formulaireCommande(
            // Le mètre n'a pas de prix pro sur ce tissu : la ligne est ignorée
            [$produit->id => ['chemise' => 4, 'metre' => 5]],
        ))->assertSessionHasNoErrors()->assertRedirect();

        $commande = Commande::where('user_id', $revendeur->id)->with('items')->firstOrFail();
        $this->assertSame('devis_demande', $commande->statut);
        $this->assertCount(1, $commande->items);
        $this->assertSame('chemise', $commande->items->first()->type_commande);
        $this->assertSame(9500, (int) $commande->items->first()->prix_unitaire);
        $this->assertSame(4 * 9500, (int) $commande->total);
    }

    public function test_une_demande_sans_les_champs_facultatifs_est_acceptee(): void
    {
        // Le formulaire envoie toujours la date et la note ; un autre client peut les omettre
        $produit = $this->produitPro();
        $revendeur = $this->revendeurValide();

        $this->actingAs($revendeur)->post(route('pro.commander.envoyer'), [
            'quantites' => [$produit->id => ['metre' => 2]],
            'mode_livraison' => 'boutique',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $commande = Commande::where('user_id', $revendeur->id)->firstOrFail();
        $this->assertSame('devis_demande', $commande->statut);
        $this->assertNull($commande->livraison_souhaitee_le);
        $this->assertNull($commande->note_client);
    }

    public function test_un_compte_non_valide_ne_peut_pas_commander_en_gros(): void
    {
        $enAttente = User::factory()->create(['role' => 'client']);
        $this->comptePro($enAttente, 'en_attente');

        $this->actingAs($enAttente)->get(route('pro.commander'))->assertRedirect(route('pro.accueil'));

        $sansCompte = User::factory()->create(['role' => 'client']);
        $this->actingAs($sansCompte)->get(route('pro.commander'))->assertRedirect(route('pro.accueil'));
    }

    public function test_un_revendeur_ne_voit_pas_la_commande_d_un_autre(): void
    {
        $revendeur = $this->revendeurValide();
        $autre = $this->revendeurValide();

        // Statut « en_attente » : ce test porte sur le cloisonnement, pas sur les statuts de devis
        $commandeDeLAutre = Commande::create([
            'user_id' => $autre->id,
            'est_pro' => true,
            'statut' => 'en_attente',
            'total' => 25000,
            'mode_livraison' => 'boutique',
            'adresse_livraison' => 'Retrait en boutique',
        ]);

        $this->actingAs($revendeur)->get(route('pro.commande', $commandeDeLAutre))->assertNotFound();
        $this->actingAs($autre)->get(route('pro.commande', $commandeDeLAutre))->assertOk();
    }

    public function test_le_statut_d_un_devis_ne_se_change_pas_a_la_main(): void
    {
        $revendeur = $this->revendeurValide();
        $commande = Commande::create([
            'user_id' => $revendeur->id,
            'est_pro' => true,
            'statut' => 'devis_demande',
            'total' => 25000,
            'mode_livraison' => 'boutique',
            'adresse_livraison' => 'Retrait en boutique',
        ]);

        $this->actingAs($this->proprietaire)
            ->patch(route('admin.commande.statut', $commande), ['statut' => 'confirmee'])
            ->assertRedirect(route('admin.commande.detail', $commande))
            ->assertSessionHas('error');

        $this->assertSame('devis_demande', $commande->fresh()->statut);
    }

    public function test_envoi_whatsapp_contient_produit_categorie_et_prix_depuis_commander_et_commande(): void
    {
        $this->proprietaire->update(['phone' => '07 11 22 33 44']);
        $produit = $this->produitPro('Morrocco rouge', ['prix_pro' => ['metre' => 800, 'rouleau' => 20000]]);
        $revendeur = $this->revendeurValide();

        $this->actingAs($revendeur)
            ->get(route('pro.commander'))
            ->assertOk()
            ->assertSee('data-categorie="Wax"', false)
            ->assertSee('Demander + WhatsApp')
            ->assertSee('Envoyer directement sur WhatsApp')
            ->assertSee('2250711223344', false);

        $this->actingAs($revendeur)->post(route('pro.commander.envoyer'), $this->formulaireCommande(
            [$produit->id => ['metre' => 10, 'rouleau' => 1]],
            ['mode_livraison' => 'domicile', 'adresse_livraison' => 'Adjamé', 'partager' => 'whatsapp'],
        ))->assertSessionHas('demande_whatsapp', true);

        $commande = Commande::where('user_id', $revendeur->id)->firstOrFail();
        $texte = \App\Support\MessageDevis::texte($commande, 'boutique');
        $this->assertStringContainsString('*Morrocco rouge* (Catégorie : Wax)', $texte);
        $this->assertStringContainsString('Au mètre : 10 m × 800 F = 8 000 F', $texte);
        $this->assertStringContainsString('Au rouleau : 1 rouleau × 20 000 F = 20 000 F', $texte);
        $this->assertStringContainsString('*Total indicatif : 28 000 F*', $texte);
        $this->assertStringContainsString('Livraison : Adjamé', $texte);

        $this->actingAs($revendeur)
            ->get(route('pro.commande', $commande))
            ->assertOk()
            ->assertSee('Envoyer ma demande sur WhatsApp');
    }

    // ===== Outils =====

    private function produitPro(string $nom = 'Wax Kente', array $attributs = []): Produit
    {
        $categorie = Categorie::firstOrCreate(['nom' => 'Wax']);

        return Produit::create($attributs + [
            'categorie_id' => $categorie->id,
            'nom' => $nom,
            'prix_metre' => 3000,
            'prix_rouleau' => 50000,
            'stock' => 200,
            'disponible' => true,
            'prix_pro' => ['metre' => 2500],
        ]);
    }

    /** Ce qu'envoie le formulaire « Commander » : les champs facultatifs arrivent vides. */
    private function formulaireCommande(array $quantites, array $champs = []): array
    {
        return $champs + [
            'quantites' => $quantites,
            'mode_livraison' => 'boutique',
            'adresse_livraison' => '',
            'livraison_souhaitee_le' => '',
            'note_client' => '',
        ];
    }

    private function revendeurValide(): User
    {
        $revendeur = User::factory()->create(['role' => 'client']);
        $this->comptePro($revendeur, 'valide');

        return $revendeur->fresh();
    }

    private function comptePro(User $user, string $statut): ComptePro
    {
        return ComptePro::create([
            'user_id' => $user->id,
            'boutique' => 'Boutique '.$user->id,
            'activite' => 'boutique',
            'ville' => 'Abidjan',
            'whatsapp' => '0707070707',
            'statut' => $statut,
            'valide_le' => $statut === 'valide' ? now() : null,
        ]);
    }
}
