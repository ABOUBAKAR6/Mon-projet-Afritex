<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Produit;
use App\Models\ProduitImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Une couleur par photo (fenêtre « Photos » de l'admin) : la page produit affiche
 * « Famille + couleur » selon la photo regardée et propose ces couleurs en pastilles.
 */
class CouleursPhotosTest extends TestCase
{
    use RefreshDatabase;

    private array $fichiers = [];

    protected function tearDown(): void
    {
        File::delete($this->fichiers);
        parent::tearDown();
    }

    public function test_l_admin_donne_une_couleur_a_chaque_photo_et_la_page_titre_famille_plus_couleur(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $produit = $this->produitAvecPhotos();
        [$verte, $orange] = $produit->images->all();
        $autre = $this->produitAvecPhotos('Duchesse');

        $this->actingAs($admin)->post(route('admin.produit.couleurs-photos', $produit), [
            'couleur_image' => ' rouge ',
            'couleurs_photos' => [$verte->id => 'vert', $orange->id => '', $autre->images->first()->id => 'Piège'],
        ])->assertRedirect(route('admin.produits'));

        // La page Produits de l'admin s'affiche, avec la couleur de chaque photo prête pour la fenêtre « Photos »
        $this->actingAs($admin)->get(route('admin.produits'))->assertOk()
            ->assertSee('form-couleurs-photos', false)->assertSee('"couleur_image":"Rouge"', false);

        $this->assertSame('Rouge', $produit->fresh()->couleur_image);
        $this->assertSame('Vert', $verte->fresh()->couleur);
        $this->assertNull($orange->fresh()->couleur);
        // Une photo d'un autre produit n'est pas modifiée
        $this->assertNull($autre->images->first()->fresh()->couleur);

        $page = $this->get(route('produit.show', $produit->slug))->assertOk();
        $page->assertSee('Morrocco rouge');                 // titre de la première photo
        $page->assertSee('Morrocco vert', false);           // titre de la 2e photo (données Alpine)
        $page->assertSee('Morrocco Classique');             // nom du tissu rappelé
        $page->assertSee('titre-couleurs', false);          // pastilles tirées des couleurs des photos
        $page->assertSeeInOrder(['Rouge', 'Vert', 'Autre']);
    }

    public function test_sans_couleurs_de_photo_le_titre_reste_le_nom_du_tissu(): void
    {
        $produit = $this->produitAvecPhotos();

        $page = $this->get(route('produit.show', $produit->slug))->assertOk();
        $page->assertSee('Morrocco Classique');
        $page->assertDontSee('titre-couleurs', false);
    }

    private function produitAvecPhotos(string $famille = 'Morrocco'): Produit
    {
        $categorie = Categorie::create(['nom' => $famille]);
        $produit = Produit::create([
            'categorie_id' => $categorie->id,
            'nom' => $famille.' Classique',
            'prix_metre' => 3000,
            'stock' => 5,
            'disponible' => 1,
            'image' => $this->photo(),
        ]);
        ProduitImage::create(['produit_id' => $produit->id, 'chemin' => $this->photo()]);
        ProduitImage::create(['produit_id' => $produit->id, 'chemin' => $this->photo()]);

        return $produit->load('images');
    }

    // Vraie petite image dans public/ : la page ne montre que les photos présentes sur le disque
    private function photo(): string
    {
        $chemin = 'images/produits/test-couleur-'.uniqid().'.png';
        File::ensureDirectoryExists(public_path('images/produits'));
        $image = imagecreatetruecolor(4, 4);
        imagepng($image, public_path($chemin));
        $this->fichiers[] = public_path($chemin);

        return $chemin;
    }
}
