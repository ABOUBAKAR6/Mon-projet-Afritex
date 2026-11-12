<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * Deux applis installables (PWA) : la boutique, qui démarre sur l'accueil,
 * et la gestion, annoncée par les pages /espace-client et /admin, qui démarre sur /espace-client.
 */
class AppliInstallableTest extends TestCase
{
    public function test_la_boutique_annonce_l_appli_boutique(): void
    {
        $this->get(route('hors-ligne'))->assertOk();

        $manifeste = $this->get('/manifest.webmanifest')->assertOk()->json();
        $this->assertSame('/', $manifeste['start_url']);
        $this->assertSame('/', $manifeste['id']);
    }

    public function test_l_espace_de_gestion_annonce_sa_propre_appli(): void
    {
        $page = $this->get(route('espace.connexion'))->assertOk();
        $page->assertSee('/manifest-gestion.webmanifest', false);
        $page->assertDontSee('href="/manifest.webmanifest"', false);

        $manifeste = $this->get('/manifest-gestion.webmanifest')->assertOk()->json();
        $this->assertSame('/espace-client', $manifeste['start_url']);
        $this->assertSame('/espace-client', $manifeste['id']);
        $this->assertStringStartsWith('Admin ', $manifeste['short_name']);

        // Icônes de gestion (couleurs inversées) bien servies, en PNG à la bonne taille
        foreach ($manifeste['icons'] as $icone) {
            $reponse = $this->get($icone['src'])->assertOk();
            [$largeur] = getimagesizefromstring($reponse->getContent());
            $this->assertSame((int) explode('x', $icone['sizes'])[0], $largeur);
        }
    }

    public function test_une_variante_d_icone_inconnue_repond_404(): void
    {
        $this->get('/icones/app-admin-n-importe.png')->assertNotFound();
        $this->get('/icones/app-999.png')->assertNotFound();
    }
}
