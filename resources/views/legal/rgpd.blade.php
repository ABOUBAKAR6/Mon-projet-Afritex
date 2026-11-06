@extends('layouts.app')
@section('title', 'Politique de confidentialité · ' . boutique('nom'))
@section('content')

<x-en-tete-page titre="Confidentialité" chapeau="Quelles données nous collectons, pourquoi, et comment exercer vos droits." />

<div class="conteneur grid gap-10 pb-20 pt-12 lg:grid-cols-[16rem_1fr] lg:gap-16 lg:pb-28 lg:pt-16">
    <nav aria-label="Sommaire" class="lg:sticky lg:top-28 lg:self-start">
        <p class="font-medium">Sommaire</p>
        <ol class="mt-3 space-y-1 text-[0.95rem]">
            <li><a href="#collecte" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Collecte des données</a></li>
            <li><a href="#utilisation" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Utilisation des données</a></li>
            <li><a href="#droits" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Vos droits</a></li>
            <li><a href="#securite" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Sécurité</a></li>
        </ol>
    </nav>

    <article class="prose-afritex bg-carte p-6 sm:p-10 lg:p-12 [&>h2:first-child]:mt-0">
        <h2 id="collecte">Collecte des données</h2>
        <p>Nous collectons uniquement les informations nécessaires pour traiter vos commandes : nom, adresse de livraison, e-mail et numéro de téléphone.</p>

        <h2 id="utilisation">Utilisation des données</h2>
        <p>Vos données sont utilisées exclusivement pour :</p>
        <ul>
            <li>le traitement et l'expédition de vos commandes de tissus ;</li>
            <li>la communication concernant le statut de votre livraison ;</li>
            <li>l'amélioration de notre catalogue selon vos préférences.</li>
        </ul>

        <h2 id="droits">Vos droits</h2>
        <p>Conformément au Règlement général sur la protection des données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles. Vous pouvez supprimer votre compte depuis vos préférences, ou nous écrire pour toute autre demande.</p>

        <h2 id="securite">Sécurité</h2>
        <p>Nous mettons en œuvre des mesures de sécurité pour protéger vos informations personnelles contre tout accès non autorisé.</p>
    </article>
</div>

@endsection
