<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

/**
 * Icônes de l'application installée (PWA) : un carré aux couleurs de la boutique
 * avec l'initiale de l'enseigne, comme le logo par défaut du site.
 * Elles suivent le nom et le thème de couleurs choisis, sans fichier à fournir.
 */
class IconeAppController extends Controller
{
    /** Variante => [côté en px, cadre doré, part du côté occupée par la lettre] */
    private const VARIANTES = [
        '192' => [192, true, 0.56],
        '512' => [512, true, 0.56],
        // « maskable » : Android découpe l'icône en rond, la lettre reste dans la zone sûre
        'maskable-512' => [512, false, 0.42],
        // iPhone (écran d'accueil) : iOS arrondit lui-même les coins
        'apple-180' => [180, true, 0.56],
    ];

    /**
     * Adresse d'une icône, avec une version qui change avec le nom et les couleurs :
     * le middleware CacheImages garde les .png un an dans le navigateur.
     */
    public static function url(string $variante): string
    {
        return route('icone-app', ['variante' => $variante, 'v' => substr(self::empreinte(), 0, 10)], false);
    }

    private static function empreinte(): string
    {
        return md5(implode('|', [couleur('foncee'), couleur('seconde'), boutique_initiale(), filemtime(__FILE__)]));
    }

    public function show(string $variante): Response
    {
        // « admin-… » : l'appli de gestion, couleurs inversées (fond doré, lettre foncée)
        // pour la distinguer de l'appli de la boutique sur l'écran d'accueil
        $gestion = str_starts_with($variante, 'admin-');
        $base = $gestion ? substr($variante, 6) : $variante;
        abort_unless(isset(self::VARIANTES[$base]), 404);
        [$cote, $cadre, $partLettre] = self::VARIANTES[$base];

        $fond = $gestion ? couleur('seconde') : couleur('foncee');
        $or = $gestion ? couleur('foncee') : couleur('seconde');
        $lettre = boutique_initiale();

        $empreinte = self::empreinte();
        $chemin = storage_path("app/icones/{$variante}-{$empreinte}.png");

        if (! is_file($chemin)) {
            File::ensureDirectoryExists(dirname($chemin));
            // Les anciennes versions (autre thème, autre nom) ne servent plus
            foreach (File::glob(storage_path("app/icones/{$variante}-*.png")) as $ancien) {
                File::delete($ancien);
            }
            $this->dessiner($chemin, $cote, $cadre, $partLettre, $fond, $or, $lettre);
        }

        // Réponse ordinaire (pas response()->file) : CacheImages lui ajoute ses en-têtes de cache
        return response(file_get_contents($chemin), 200, ['Content-Type' => 'image/png']);
    }

    private function dessiner(string $chemin, int $cote, bool $cadre, float $partLettre, string $fond, string $or, string $lettre): void
    {
        // Dessin en 4x puis réduction : bords de la lettre et du cadre bien lisses
        $grand = $cote * 4;
        $image = imagecreatetruecolor($grand, $grand);
        imagealphablending($image, true);

        $couleurFond = $this->allouer($image, $fond);
        $couleurOr = $this->allouer($image, $or);
        imagefilledrectangle($image, 0, 0, $grand - 1, $grand - 1, $couleurFond);

        if ($cadre) {
            // Filet doré à angles droits, comme les encadrés du site
            $marge = (int) round($grand * 0.085);
            $epaisseur = max(4, (int) round($grand * 0.012));
            for ($i = 0; $i < $epaisseur; $i++) {
                imagerectangle($image, $marge + $i, $marge + $i, $grand - 1 - $marge - $i, $grand - 1 - $marge - $i, $couleurOr);
            }
        }

        $police = resource_path('fonts/Poppins-SemiBold.ttf');
        $taille = $grand * $partLettre * 0.75; // points → pixels (GD travaille en points à 96 dpi)
        $boite = imagettfbbox($taille, 0, $police, $lettre);
        $largeur = $boite[2] - $boite[0];
        $hauteur = $boite[1] - $boite[7];
        $x = (int) round(($grand - $largeur) / 2 - $boite[0]);
        $y = (int) round(($grand + $hauteur) / 2 - $boite[1]);
        imagettftext($image, $taille, 0, $x, $y, $couleurOr, $police, $lettre);

        $final = imagecreatetruecolor($cote, $cote);
        imagecopyresampled($final, $image, 0, 0, 0, 0, $cote, $cote, $grand, $grand);
        imagepng($final, $chemin, 9);

    }

    private function allouer(\GdImage $image, string $hex): int
    {
        $hex = ltrim($hex, '#');

        return imagecolorallocate($image, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
    }
}
