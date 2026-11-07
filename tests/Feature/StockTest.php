<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use App\Services\GestionStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stock des tissus en rouleaux entiers + mètres du rouleau entamé :
 * ventes au mètre et au rouleau, arrivages, retraits, inventaires, historique, accès.
 */
class StockTest extends TestCase
{
    use RefreshDatabase;

    private User $proprietaire;

    protected function setUp(): void
    {
        parent::setUp();
        $this->proprietaire = User::factory()->create(['role' => 'admin']);
    }

    public function test_vente_au_metre_puise_dans_le_rouleau_entame_puis_ouvre_un_rouleau(): void
    {
        $tissu = $this->tissu(['stock' => 4, 'metres_entames' => 5, 'metres_par_rouleau' => 30]);
        $commande = $this->commande([['metre', 12, $tissu]]);

        app(GestionStock::class)->sortieCommande($commande);

        $tissu->refresh();
        // 5 m entamés + 1 rouleau ouvert (30 m) - 12 m = 3 rouleaux + 23 m
        $this->assertSame(3, $tissu->stock);
        $this->assertEqualsWithDelta(23.0, $tissu->metres_entames, 0.001);
        $this->assertSame("3 rouleaux + 23\u{00A0}m", $tissu->libelleStock());

        $mvt = MouvementStock::sole();
        $this->assertSame('vente', $mvt->type);
        $this->assertSame($commande->id, $mvt->commande_id);
        $this->assertSame(-1, $mvt->rouleaux);
        $this->assertEqualsWithDelta(18.0, $mvt->metres, 0.001);
    }

    public function test_vente_au_rouleau_retire_des_rouleaux_et_masque_le_tissu_epuise(): void
    {
        $tissu = $this->tissu(['stock' => 2]);
        $commande = $this->commande([['rouleau', 2, $tissu]]);

        app(GestionStock::class)->sortieCommande($commande);

        $tissu->refresh();
        $this->assertSame(0, $tissu->stock);
        $this->assertSame('epuise', $tissu->etatStock());
        $this->assertFalse((bool) $tissu->disponible);
    }

    public function test_une_commande_n_est_decomptee_qu_une_fois_et_les_confections_ne_bougent_pas_le_stock(): void
    {
        $tissu = $this->tissu(['stock' => 10]);
        $commande = $this->commande([['rouleau', 1, $tissu], ['pantalon', 3, $tissu]]);

        app(GestionStock::class)->sortieCommande($commande);
        app(GestionStock::class)->sortieCommande($commande); // paiement confirmé deux fois

        $this->assertSame(9, $tissu->refresh()->stock);
        $this->assertSame(1, MouvementStock::count());
    }

    public function test_longueur_inconnue_garde_les_metres_a_decompter_jusqu_a_ce_qu_elle_soit_indiquee(): void
    {
        $tissu = $this->tissu(['stock' => 5]);
        app(GestionStock::class)->sortieCommande($this->commande([['metre', 8, $tissu]]));

        $tissu->refresh();
        $this->assertSame(5, $tissu->stock);
        $this->assertEqualsWithDelta(-8.0, $tissu->metres_entames, 0.001);

        $this->actingAs($this->proprietaire)
            ->patch(route('admin.stock.longueur', $tissu), ['metres_par_rouleau' => 30])
            ->assertRedirect(route('admin.stock.produit', $tissu));

        $tissu->refresh();
        $this->assertSame(4, $tissu->stock);
        $this->assertEqualsWithDelta(22.0, $tissu->metres_entames, 0.001);
    }

    public function test_le_proprietaire_ajoute_retire_et_compte_le_stock_depuis_la_section_stock(): void
    {
        $tissu = $this->tissu(['stock' => 3, 'metres_par_rouleau' => 25, 'disponible' => false]);
        $this->actingAs($this->proprietaire);

        $this->get(route('admin.stock'))->assertOk()->assertSee($tissu->nom);
        $this->get(route('admin.stock', ['filtre' => 'surveiller']))->assertOk()->assertSee($tissu->nom);

        $this->post(route('admin.stock.mouvement', $tissu), ['type' => 'arrivage', 'rouleaux' => 10, 'metres' => 0, 'note' => 'Fournisseur', 'remettre_en_vente' => 1])
            ->assertRedirect(route('admin.stock.produit', $tissu));
        $tissu->refresh();
        $this->assertSame(13, $tissu->stock);
        $this->assertTrue((bool) $tissu->disponible);

        $this->post(route('admin.stock.mouvement', $tissu), ['type' => 'retrait', 'rouleaux' => 0, 'metres' => 10]);
        $tissu->refresh();
        $this->assertSame(12, $tissu->stock);
        $this->assertEqualsWithDelta(15.0, $tissu->metres_entames, 0.001);

        // Retrait plus grand que le stock : refusé, rien ne bouge
        $this->post(route('admin.stock.mouvement', $tissu), ['type' => 'retrait', 'rouleaux' => 50])->assertSessionHasErrors('rouleaux');
        $this->assertSame(12, $tissu->refresh()->stock);

        $this->post(route('admin.stock.mouvement', $tissu), ['type' => 'inventaire', 'rouleaux' => 7, 'metres' => 4.5]);
        $tissu->refresh();
        $this->assertSame(7, $tissu->stock);
        $this->assertEqualsWithDelta(4.5, $tissu->metres_entames, 0.001);

        $this->assertSame(['arrivage', 'retrait', 'inventaire'], MouvementStock::orderBy('id')->pluck('type')->all());
        $this->assertSame($this->proprietaire->id, MouvementStock::first()->user_id);
        $this->get(route('admin.stock.produit', $tissu))->assertOk()->assertSee('Fournisseur')->assertSee('Inventaire');
    }

    public function test_un_client_n_accede_pas_au_stock(): void
    {
        $tissu = $this->tissu(['stock' => 3]);
        $client = User::factory()->create(['role' => 'client']);

        // L'administration se cache aux clients : 404, comme si la page n'existait pas
        $this->actingAs($client)->get(route('admin.stock'))->assertNotFound();
        $this->actingAs($client)->post(route('admin.stock.mouvement', $tissu), ['type' => 'arrivage', 'rouleaux' => 99])->assertNotFound();
        $this->assertSame(3, $tissu->refresh()->stock);
    }

    private function tissu(array $attributs = []): Produit
    {
        $categorie = Categorie::firstOrCreate(['nom' => 'Wax']);

        return Produit::create($attributs + [
            'categorie_id' => $categorie->id,
            'nom' => 'Grain de diamant bleu',
            'prix_metre' => 3000,
            'prix_rouleau' => 50000,
            'stock' => 0,
            'disponible' => true,
        ]);
    }

    /** @param array<int, array{0: string, 1: float, 2: Produit}> $lignes */
    private function commande(array $lignes): Commande
    {
        $commande = Commande::create([
            'user_id' => User::factory()->create()->id,
            'statut' => 'confirmee',
            'total' => 10000,
            'mode_livraison' => 'boutique',
            'adresse_livraison' => 'Retrait en boutique',
        ]);
        foreach ($lignes as [$format, $quantite, $produit]) {
            CommandeItem::create([
                'commande_id' => $commande->id,
                'produit_id' => $produit->id,
                'type_commande' => $format,
                'quantite' => $quantite,
                'prix_unitaire' => 1000,
                'sous_total' => 1000 * $quantite,
            ]);
        }

        return $commande;
    }
}
