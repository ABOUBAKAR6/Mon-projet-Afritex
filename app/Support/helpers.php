<?php

// Helpers globaux d'identité : ils lisent config/boutique.php, rempli par le .env.
// Ainsi une nouvelle boutique cliente affiche son enseigne au lieu d'AfriTex
// sans toucher au code.

if (! function_exists('boutique')) {
    /**
     * Retourne une valeur d'identité de la boutique courante
     * (nom, slogan, email_contact, email_privacy, ...).
     */
    function boutique(string $cle = 'nom'): string
    {
        return (string) config('boutique.'.$cle, config('app.name', 'AfriTex'));
    }
}

if (! function_exists('boutique_initiale')) {
    /**
     * Initiale de l'enseigne, affichée dans le carré quand aucun logo n'est envoyé.
     */
    function boutique_initiale(): string
    {
        return mb_strtoupper(mb_substr(trim(boutique('nom')), 0, 1)) ?: 'A';
    }
}

if (! function_exists('boutique_couleur_choisie')) {
    /**
     * Couleur réglée dans le .env pour ce rôle, ou null (vide ou mal écrite).
     * Seul un hex rrggbb (avec ou sans #) est accepté : la valeur part telle quelle dans le CSS.
     * Dans le .env, écrire "#8b5a99" entre guillemets, ou 8b5a99 : un # nu y ouvre un commentaire.
     */
    function boutique_couleur_choisie(string $role): ?string
    {
        $valeur = ltrim(strtolower(trim((string) config('boutique.couleurs.'.$role))), '#');

        return preg_match('/^[0-9a-f]{6}$/', $valeur) ? '#'.$valeur : null;
    }
}

if (! function_exists('couleur')) {
    /**
     * Couleur hex d'un rôle (accent, foncee, seconde, papier, surface, encre,
     * texte, texte_doux, trait) : celle du .env, sinon celle d'origine.
     * Sert là où les variables CSS ne marchent pas : e-mails, balises meta, cartes.
     */
    function couleur(string $role): string
    {
        // Deux nuances propres aux e-mails, rattachées à un rôle du .env.
        if ($role === 'carte') {
            return boutique_couleur_choisie('papier') ? '#ffffff' : '#fffcf9';
        }
        if ($role === 'bloc') {
            return boutique_couleur_choisie('surface') ?? '#f3e9df';
        }

        return boutique_couleur_choisie($role)
            ?? (string) config('boutique.couleurs_origine.'.$role, '#000000');
    }
}

if (! function_exists('boutique_teinte')) {
    /**
     * Teinte OKLCH (0-360) d'une couleur hex : donne aux ombres et aux voiles
     * la nuance de la couleur foncée de la boutique.
     */
    function boutique_teinte(string $hex): float
    {
        $lineaire = array_map(function (string $paire): float {
            $c = hexdec($paire) / 255;

            return $c <= 0.04045 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, str_split(ltrim($hex, '#'), 2));
        [$r, $g, $b] = $lineaire;

        $l = (0.4122214708 * $r + 0.5363325363 * $g + 0.0514459929 * $b) ** (1 / 3);
        $m = (0.2119034982 * $r + 0.6806995451 * $g + 0.1073969566 * $b) ** (1 / 3);
        $s = (0.0883024619 * $r + 0.2817188376 * $g + 0.6299787005 * $b) ** (1 / 3);

        $a = 1.9779984951 * $l - 2.4285922050 * $m + 0.4505937099 * $s;
        $bb = 0.0259040371 * $l + 0.7827717662 * $m - 0.8086757660 * $s;

        return round(fmod(rad2deg(atan2($bb, $a)) + 360, 360), 1);
    }
}

if (! function_exists('boutique_couleurs_css')) {
    /**
     * Déclarations CSS qui remplacent les couleurs d'origine, pour les seuls rôles
     * réglés dans le .env. Chaîne vide si aucun : le site garde alors le CSS AfriTex intact.
     * Les teintes dérivées (doux, trait, fonds teintés…) suivent via color-mix dans app.css.
     */
    function boutique_couleurs_css(): string
    {
        $variables = [
            'accent' => ['--brun' => '%s'],
            'foncee' => ['--brun-fonce' => '%s'],
            'seconde' => [
                '--or' => '%s',
                '--or-doux' => 'color-mix(in oklch, var(--or) 80%%, white)',
                '--or-clair' => 'color-mix(in oklch, var(--or) 55%%, white)',
            ],
            'papier' => [
                '--papier' => '%s',
                '--carte' => 'color-mix(in oklch, var(--papier) 35%%, white)',
            ],
            'surface' => ['--papier-2' => '%s'],
            'encre' => ['--encre' => '%s'],
            'texte' => ['--encre-2' => '%s'],
            'texte_doux' => ['--encre-3' => '%s'],
            'trait' => [
                '--trait' => '%s',
                '--trait-fort' => 'color-mix(in oklch, var(--trait) 75%%, var(--encre-3))',
            ],
        ];

        $css = '';
        foreach ($variables as $role => $declarations) {
            if (! $hex = boutique_couleur_choisie($role)) {
                continue;
            }
            foreach ($declarations as $propriete => $valeur) {
                $css .= $propriete.':'.sprintf($valeur, $hex).';';
            }
            if ($role === 'foncee') {
                $css .= '--teinte-ombre:'.boutique_teinte($hex).';';
            }
        }

        return $css;
    }
}

if (! function_exists('boutique_slug')) {
    /**
     * Slug stable de l'enseigne : sert aux clés techniques indépendantes par site
     * (localStorage, noms de fichier d'export, ...).
     */
    function boutique_slug(): string
    {
        return str(str(boutique('nom'))->slug('_'))->lower()->toString();
    }
}
