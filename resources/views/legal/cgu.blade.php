@extends('layouts.app')
@section('title', 'Conditions générales · AfriTex')
@section('content')

<x-en-tete-page titre="Conditions générales" chapeau="Les règles d'utilisation du site AfriTex et des commandes passées en ligne." />

<div class="conteneur grid gap-10 pb-20 pt-12 lg:grid-cols-[16rem_1fr] lg:gap-16 lg:pb-28 lg:pt-16">
    <nav aria-label="Sommaire" class="lg:sticky lg:top-28 lg:self-start">
        <p class="font-medium">Sommaire</p>
        <ol class="mt-3 space-y-1 text-[0.95rem]">
            <li><a href="#objet" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Objet</a></li>
            <li><a href="#ventes" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Ventes et livraisons</a></li>
            <li><a href="#prix" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Prix et paiements</a></li>
            <li><a href="#propriete" class="flex min-h-10 items-center text-encre-2 hover:text-brun">Propriété intellectuelle</a></li>
        </ol>
    </nav>

    <article class="prose-afritex bg-carte p-6 sm:p-10 lg:p-12 [&>h2:first-child]:mt-0">
        <h2 id="objet">Objet</h2>
        <p>Les présentes conditions générales d'utilisation (CGU) ont pour objet de définir les modalités de mise à disposition et d'utilisation du site AfriTex.</p>

        <h2 id="ventes">Ventes et livraisons</h2>
        <p>Toutes les commandes passées via le site sont traitées avec le plus grand soin. Les délais de livraison sont indicatifs et peuvent varier selon la destination en Côte d'Ivoire.</p>

        <h2 id="prix">Prix et paiements</h2>
        <p>Les prix sont indiqués en francs CFA. AfriTex se réserve le droit de modifier ses prix à tout moment, mais les produits sont facturés sur la base du tarif en vigueur au moment de la validation de la commande.</p>

        <h2 id="propriete">Propriété intellectuelle</h2>
        <p>L'ensemble du contenu du site (textes, images, design) est la propriété exclusive d'AfriTex. Toute reproduction totale ou partielle est strictement interdite.</p>
    </article>
</div>

@endsection
