<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

/**
 * Images envoyées depuis l'administration ou par les clients : une photo de téléphone de 15 Mo
 * est acceptée, puis redressée (orientation EXIF), réduite à la taille utile à l'affichage
 * et compressée en WebP. Un format illisible par GD (HEIC d'iPhone…) est gardé tel quel plutôt que refusé.
 */
class OptimiseurImage
{
    /** Poids maximal accepté à l'envoi, en Ko (règle de validation « max: ») : 20 Mo. */
    public const POIDS_MAX_KO = 20480;

    /** Côté le plus long, en pixels, selon l'usage. */
    public const PRODUIT = 1600;
    public const CATEGORIE = 1200;
    public const LOGO = 600;
    public const PORTRAIT = 400;

    private const QUALITE_WEBP = 80;
    private const QUALITE_JPEG = 82;

    /**
     * Enregistre l'image dans public/{dossier} et renvoie le chemin public (« images/produits/xxx.webp »).
     */
    public function enregistrer(UploadedFile $fichier, string $dossier, int $cote = self::PRODUIT): string
    {
        $dossier = trim($dossier, '/');

        return $dossier.'/'.$this->enregistrerDans($fichier, public_path($dossier), $cote);
    }

    /**
     * Enregistre l'image dans un dossier absolu (ex. storage/app/public/creations) et renvoie le nom du fichier.
     */
    public function enregistrerDans(UploadedFile $fichier, string $dossierAbsolu, int $cote = self::PRODUIT): string
    {
        if (! is_dir($dossierAbsolu)) {
            mkdir($dossierAbsolu, 0755, true);
        }
        $base = time().'_'.Str::random(10);

        $image = $this->ouvrir($fichier);
        if (! $image) {
            // Format non pris en charge par GD : on garde l'original plutôt que de refuser l'envoi
            $nom = $base.'.'.(strtolower($fichier->getClientOriginalExtension()) ?: ($fichier->extension() ?: 'jpg'));
            $fichier->move($dossierAbsolu, $nom);

            return $nom;
        }

        $image = $this->redresser($image, $fichier);
        $image = $this->reduire($image, $cote);

        if (function_exists('imagewebp')) {
            $nom = $base.'.webp';
            $ok = imagewebp($image, $dossierAbsolu.DIRECTORY_SEPARATOR.$nom, self::QUALITE_WEBP);
        } else {
            $nom = $base.'.jpg';
            $ok = imagejpeg($this->surFondBlanc($image), $dossierAbsolu.DIRECTORY_SEPARATOR.$nom, self::QUALITE_JPEG);
        }
        imagedestroy($image);

        if (! $ok) {
            // Échec d'écriture compressée : dernier recours, le fichier d'origine
            $nom = $base.'.'.($fichier->extension() ?: 'jpg');
            $fichier->move($dossierAbsolu, $nom);
        }

        return $nom;
    }

    /** Ouvre l'image avec GD ; null si le format n'est pas lisible ou trop lourd pour la mémoire. */
    private function ouvrir(UploadedFile $fichier): ?\GdImage
    {
        $chemin = $fichier->getRealPath();
        $infos = $chemin ? @getimagesize($chemin) : false;
        if (! $infos || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        // Décoder une photo de 48 Mpx demande ~250 Mo : mémoire relevée pour cette seule requête
        $besoin = (int) ($infos[0] * $infos[1] * 5 * 1.2) + 64 * 1024 * 1024;
        if ($besoin > $this->octets(ini_get('memory_limit'))) {
            @ini_set('memory_limit', (string) max($besoin, 512 * 1024 * 1024));
        }
        if ($besoin > $this->octets(ini_get('memory_limit'))) {
            return null;
        }

        $image = @imagecreatefromstring((string) file_get_contents($chemin));

        return $image instanceof \GdImage ? $image : null;
    }

    /** Applique l'orientation EXIF des photos de téléphone (sinon elles s'affichent couchées). */
    private function redresser(\GdImage $image, UploadedFile $fichier): \GdImage
    {
        if (! function_exists('exif_read_data') || ! in_array($fichier->getMimeType(), ['image/jpeg', 'image/jpg'], true)) {
            return $image;
        }
        $orientation = (int) (@exif_read_data($fichier->getRealPath())['Orientation'] ?? 1);
        $angle = [3 => 180, 6 => -90, 8 => 90][$orientation] ?? 0;
        if ($angle === 0) {
            return $image;
        }
        $tournee = imagerotate($image, $angle, 0);
        if ($tournee === false) {
            return $image;
        }
        imagedestroy($image);

        return $tournee;
    }

    /** Réduit l'image pour que son plus grand côté ne dépasse pas $cote (jamais d'agrandissement). */
    private function reduire(\GdImage $image, int $cote): \GdImage
    {
        $largeur = imagesx($image);
        $hauteur = imagesy($image);
        $ratio = $cote / max($largeur, $hauteur);
        if ($ratio >= 1) {
            return $this->garderTransparence($image);
        }

        $nouvelle = imagecreatetruecolor(max(1, (int) round($largeur * $ratio)), max(1, (int) round($hauteur * $ratio)));
        imagealphablending($nouvelle, false);
        imagesavealpha($nouvelle, true);
        imagecopyresampled($nouvelle, $image, 0, 0, 0, 0, imagesx($nouvelle), imagesy($nouvelle), $largeur, $hauteur);
        imagedestroy($image);

        return $nouvelle;
    }

    /** Un logo PNG transparent le reste en WebP. */
    private function garderTransparence(\GdImage $image): \GdImage
    {
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /** JPEG sans transparence : les zones transparentes deviennent blanches au lieu de noires. */
    private function surFondBlanc(\GdImage $image): \GdImage
    {
        $fond = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($fond, 0, 0, imagecolorallocate($fond, 255, 255, 255));
        imagecopy($fond, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        return $fond;
    }

    private function octets(string|false $valeur): int
    {
        $valeur = trim((string) $valeur);
        if ($valeur === '' || $valeur === '-1') {
            return PHP_INT_MAX;
        }
        $nombre = (int) $valeur;

        return match (strtolower(substr($valeur, -1))) {
            'g' => $nombre * 1024 ** 3,
            'm' => $nombre * 1024 ** 2,
            'k' => $nombre * 1024,
            default => $nombre,
        };
    }
}
