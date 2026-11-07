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

    /*
     | Thèmes que le propriétaire choisit dans l'administration (bouton « Couleurs »).
     | Le choix est enregistré en base (paramètre theme_couleurs) et passe avant le .env.
     | « D'origine » (aucun thème) = couleurs du .env, sinon celles d'AfriTex.
     | Contrastes vérifiés : texte blanc sur l'accent, textes sur le fond, accent clair
     | sur les surfaces sombres, tous au moins 4,5:1.
     */
    'themes' => [
        'indigo' => ['nom' => 'Indigo', 'couleurs' => ['accent' => '#2f4a8a', 'foncee' => '#141d3a', 'seconde' => '#d9a441', 'papier' => '#f7f8fc', 'surface' => '#eceff7', 'encre' => '#161b2e', 'texte' => '#3d4560', 'texte_doux' => '#5b647e', 'trait' => '#dde2ee']],
        'emeraude' => ['nom' => 'Émeraude', 'couleurs' => ['accent' => '#1d6a4d', 'foncee' => '#0e3125', 'seconde' => '#d4a64a', 'papier' => '#f6faf7', 'surface' => '#e9f1ec', 'encre' => '#13261d', 'texte' => '#36493f', 'texte_doux' => '#56675d', 'trait' => '#d9e6de']],
        'bordeaux' => ['nom' => 'Bordeaux', 'couleurs' => ['accent' => '#8c1d3a', 'foncee' => '#3b0b19', 'seconde' => '#d8a24a', 'papier' => '#fcf6f6', 'surface' => '#f4e9eb', 'encre' => '#2a1418', 'texte' => '#4f3237', 'texte_doux' => '#6c5055', 'trait' => '#ecdadd']],
        'prune' => ['nom' => 'Prune', 'couleurs' => ['accent' => '#8b5a99', 'foncee' => '#2b1a31', 'seconde' => '#c4a2cf', 'papier' => '#fcfafc', 'surface' => '#f3ecf5', 'encre' => '#2b1a31', 'texte' => '#5e4f65', 'texte_doux' => '#6f6076', 'trait' => '#e7d9ec']],
        'terracotta' => ['nom' => 'Terracotta', 'couleurs' => ['accent' => '#a8431c', 'foncee' => '#45190c', 'seconde' => '#e3b04b', 'papier' => '#fdf7f2', 'surface' => '#f6ebe2', 'encre' => '#2e1b12', 'texte' => '#523a2e', 'texte_doux' => '#6d5548', 'trait' => '#eddccf']],
        'lagune' => ['nom' => 'Lagune', 'couleurs' => ['accent' => '#0e6a70', 'foncee' => '#0a2f33', 'seconde' => '#e0b25a', 'papier' => '#f4fafa', 'surface' => '#e6f1f1', 'encre' => '#102526', 'texte' => '#34494a', 'texte_doux' => '#526768', 'trait' => '#d6e6e6']],
        'fuchsia' => ['nom' => 'Fuchsia', 'couleurs' => ['accent' => '#a3245e', 'foncee' => '#3d0e25', 'seconde' => '#e8b04f', 'papier' => '#fdf6f9', 'surface' => '#f6e8ef', 'encre' => '#2c1420', 'texte' => '#513442', 'texte_doux' => '#6d5160', 'trait' => '#eed9e3']],
        'ardoise' => ['nom' => 'Ardoise', 'couleurs' => ['accent' => '#2f3a44', 'foncee' => '#181d22', 'seconde' => '#c9a45c', 'papier' => '#f7f7f6', 'surface' => '#ecedec', 'encre' => '#1b1f23', 'texte' => '#43494f', 'texte_doux' => '#5f656b', 'trait' => '#dfe1e2']],
    ],

];
