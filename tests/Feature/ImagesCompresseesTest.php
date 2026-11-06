<?php

namespace Tests\Feature;

use App\Models\Categorie;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

/**
 * Une photo de téléphone de 15 Mo est acceptée puis compressée en WebP à la taille d'affichage,
 * au lieu d'être refusée ; au-delà de 20 Mo, un message clair.
 */
class ImagesCompresseesTest extends TestCase
{
    use RefreshDatabase;

    /** @var array<int, string> fichiers créés dans public/ par les tests, supprimés à la fin */
    private array $fichiers = [];

    protected function tearDown(): void
    {
        foreach ($this->fichiers as $chemin) {
            @unlink(public_path($chemin));
        }
        parent::tearDown();
    }

    public function test_une_photo_de_15_mo_est_acceptee_et_compressee(): void
    {
        $proprietaire = User::factory()->create(['role' => 'admin']);
        $categorie = Categorie::create(['nom' => 'Wax']);

        // 4 000 x 3 000 px, annoncée à 15 Mo comme une photo de téléphone
        $photo = UploadedFile::fake()->image('photo-telephone.jpg', 4000, 3000)->size(15 * 1024);

        $this->actingAs($proprietaire)->post(route('admin.produits.ajouter'), [
            'nom' => 'Wax test',
            'categorie_id' => $categorie->id,
            'tarifications' => [['label' => 'Au mètre', 'prix' => 3000]],
            'image' => $photo,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.produits'));

        $produit = Produit::where('nom', 'Wax test')->firstOrFail();
        $this->fichiers[] = $produit->image;

        $this->assertStringEndsWith('.webp', $produit->image);
        $this->assertFileExists(public_path($produit->image));
        [$largeur, $hauteur] = getimagesize(public_path($produit->image));
        $this->assertSame(1600, $largeur);
        $this->assertSame(1200, $hauteur);
    }

    public function test_une_photo_de_plus_de_20_mo_est_refusee_avec_un_message(): void
    {
        $proprietaire = User::factory()->create(['role' => 'admin']);
        $categorie = Categorie::create(['nom' => 'Wax']);

        $this->actingAs($proprietaire)->post(route('admin.produits.ajouter'), [
            'nom' => 'Wax trop lourd',
            'categorie_id' => $categorie->id,
            'tarifications' => [['label' => 'Au mètre', 'prix' => 3000]],
            'image' => UploadedFile::fake()->image('enorme.jpg', 800, 600)->size(25 * 1024),
        ])->assertSessionHasErrors(['image' => 'La photo dépasse 20 Mo.']);

        $this->assertDatabaseMissing('produits', ['nom' => 'Wax trop lourd']);
    }
}
