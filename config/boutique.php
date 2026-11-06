<?php

// Identité de la boutique : un seul code source sert toutes les boutiques.
// Une nouvelle installation cliente ne change QUE son .env (et son logo via l'admin),
// jamais le code. Valeurs par défaut = l'apparence d'origine (AfriTex).

return [

    'nom' => env('BOUTIQUE_NOM') ?: 'AfriTex',

    'slogan' => env('BOUTIQUE_SLOGAN') ?: "Tissus africains, gros et détail",

    'slogan_long' => env('BOUTIQUE_SLOGAN_LONG') ?: "tissus africains au mètre et au rouleau",

    'description' => env('BOUTIQUE_DESCRIPTION') ?:
        "tissus africains de qualité au mètre, au rouleau ou en confection. Vente en gros et au détail, livraison partout en Côte d'Ivoire.",

    // Phrase sous le grand titre de la page d'accueil.
    'accroche' => env('BOUTIQUE_ACCROCHE') ?:
        "Grain de diamant, duchesse, soie, dentelle : au mètre, au rouleau ou en confection, livrés partout en Côte d'Ivoire.",

    'mot_fin' => env('BOUTIQUE_MOT_FIN') ?: "Tissus africains, livrés partout en Côte d'Ivoire.",

    'email_contact' => env('BOUTIQUE_EMAIL_CONTACT') ?: 'contact@afritex.ci',

    'email_privacy' => env('BOUTIQUE_EMAIL_PRIVACY') ?: 'privacy@afritex.ci',

    // Coordonnées affichées sur la page de confidentialité ; lignes masquées si vides.
    'telephone' => env('BOUTIQUE_TELEPHONE') ?: '',

    'adresse' => env('BOUTIQUE_ADRESSE') ?: '',

    /*
     | Couleurs (hex #rrggbb). Vide = couleur d'origine AfriTex, et le CSS reste
     | exactement celui d'AfriTex. Rôle de chacune :
     |   accent      boutons principaux, liens, focus (texte blanc dessus : viser 4,5:1)
     |   foncee      surfaces sombres : pied de page, menu admin, bandeaux
     |   seconde     accent sur surfaces sombres, bouton secondaire
     |   papier      fond des pages        surface   fond des blocs teintés
     |   encre       texte principal       texte     texte secondaire
     |   texte_doux  mentions, indications des champs
     |   trait       filets et bordures
     */
    'couleurs' => [
        'accent' => env('BOUTIQUE_COULEUR_ACCENT'),
        'foncee' => env('BOUTIQUE_COULEUR_FONCEE'),
        'seconde' => env('BOUTIQUE_COULEUR_SECONDE'),
        'papier' => env('BOUTIQUE_COULEUR_PAPIER'),
        'surface' => env('BOUTIQUE_COULEUR_SURFACE'),
        'encre' => env('BOUTIQUE_COULEUR_ENCRE'),
        'texte' => env('BOUTIQUE_COULEUR_TEXTE'),
        'texte_doux' => env('BOUTIQUE_COULEUR_TEXTE_DOUX'),
        'trait' => env('BOUTIQUE_COULEUR_TRAIT'),
    ],

    // Équivalents hex des couleurs d'origine, pour les e-mails et la barre du téléphone
    // (ils ne lisent pas les variables CSS).
    'couleurs_origine' => [
        'accent' => '#8b4513',
        'foncee' => '#4a1b0c',
        'seconde' => '#c8963e',
        'papier' => '#fdf6ee',
        'surface' => '#f6f1ec',
        'encre' => '#2b1d16',
        'texte' => '#4d3b31',
        'texte_doux' => '#6b584c',
        'trait' => '#eadfd5',
    ],

];
