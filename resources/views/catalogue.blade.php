@extends('layouts.app')
@php
    $categorieActive = request('categorie_id') ? $categories->firstWhere('id', (int) request('categorie_id')) : null;
    $titrePage = $categorieActive?->nom ?? (request('recherche') ? 'Résultats pour « ' . request('recherche') . ' »' : 'Le catalogue');
    $filtresActifs = collect([request('recherche'), request('min_price'), request('max_price'), request('couleurs')])->filter()->isNotEmpty() || request('sort');
    $allColors = collect($allColors)->filter()->values();
    $tris = ['latest' => 'Les plus récents', 'price_asc' => 'Prix au mètre croissant', 'price_desc' => 'Prix au mètre décroissant'];
@endphp
@section('title', $titrePage . ' · ' . boutique('nom'))
@section('content')

{{-- Téléphone : pas de bandeau, on arrive directement sur les familles et les tissus (titre gardé pour les lecteurs d'écran) --}}
<h1 class="sr-only sm:hidden">{{ $titrePage }}</h1>

{{-- Une catégorie ouverte prend sa propre photo comme bandeau (tablette et ordinateur) --}}
<div class="hidden sm:block">
<x-en-tete-page :titre="$titrePage"
    :ariane="$categorieActive ? [['Catalogue', route('catalogue')]] : []"
    :vignette="$categorieActive?->image"
    :chapeau="$categorieActive
        ? 'Notre sélection ' . $categorieActive->nom . ' : au mètre, au rouleau ou en confection. Affinez par prix ou par couleur.'
        : 'Tissus au mètre, au rouleau ou en confection. Choisissez une famille, puis affinez par prix ou par couleur.'" />
</div>

<div class="conteneur pb-10 pt-4 sm:py-10 lg:py-14">
    {{-- Familles de tissus : accès direct --}}
    <nav aria-label="Familles de tissus" class="defilement-discret -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
        <ul class="flex w-max gap-2 sm:w-auto sm:flex-wrap">
            <li>
                <a href="{{ route('catalogue', request()->except(['categorie_id', 'page'])) }}" @if(!$categorieActive) aria-current="page" @endif
                   class="flex min-h-11 items-center px-4 text-[0.95rem] transition-colors {{ !$categorieActive ? 'bg-brun font-medium text-white' : 'bg-carte text-encre hover:bg-brun-doux hover:text-brun-fonce' }}">Toutes</a>
            </li>
            @foreach($categories as $cat)
                @php $actif = $categorieActive?->id === $cat->id; @endphp
                <li>
                    <a href="{{ route('catalogue', array_merge(request()->except(['categorie_id', 'page']), ['categorie_id' => $cat->id])) }}" @if($actif) aria-current="page" @endif
                       class="flex min-h-11 items-center whitespace-nowrap px-4 text-[0.95rem] transition-colors {{ $actif ? 'bg-brun font-medium text-white' : 'bg-carte text-encre hover:bg-brun-doux hover:text-brun-fonce' }}">{{ $cat->nom }}</a>
                </li>
            @endforeach
        </ul>
    </nav>

    {{-- Recherche, prix, couleurs, tri --}}
    <details class="group/filtres mt-6 bg-carte" {{ $filtresActifs ? 'open' : '' }} x-data x-init="if (window.matchMedia('(min-width: 64rem)').matches) $el.open = true">
        <summary class="flex min-h-14 cursor-pointer list-none items-center justify-between gap-4 px-5 font-medium lg:hidden">
            <span class="flex items-center gap-2"><x-icone nom="filtre" /> Affiner la recherche</span>
            <x-icone nom="chevron-bas" class="size-4 transition-transform group-open/filtres:rotate-180" />
        </summary>
        <form method="GET" action="{{ route('catalogue') }}" class="grid gap-5 border-t border-trait p-5 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_auto] lg:items-end lg:border-t-0 lg:p-6">
            @if($categorieActive)
                <input type="hidden" name="categorie_id" value="{{ $categorieActive->id }}">
            @endif
            <div>
                <label for="f-recherche" class="etiquette">Recherche</label>
                <input id="f-recherche" type="search" name="recherche" value="{{ request('recherche') }}" placeholder="Un motif, une couleur…" class="champ">
            </div>
            <fieldset>
                <legend class="etiquette">Prix au mètre (F)</legend>
                <div class="flex gap-2">
                    <label for="f-min" class="sr-only">Prix minimum</label>
                    <input id="f-min" type="number" min="0" step="100" name="min_price" value="{{ request('min_price') }}" placeholder="Min" class="champ chiffres">
                    <label for="f-max" class="sr-only">Prix maximum</label>
                    <input id="f-max" type="number" min="0" step="100" name="max_price" value="{{ request('max_price') }}" placeholder="Max" class="champ chiffres">
                </div>
            </fieldset>
            <div>
                <label for="f-tri" class="etiquette">Trier par</label>
                <select id="f-tri" name="sort" class="champ">
                    @foreach($tris as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected(request('sort', 'latest') === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 sm:col-span-2 lg:col-span-1">
                <button type="submit" class="btn btn-brun flex-1 lg:flex-none">Appliquer</button>
                @if($filtresActifs)
                    <a href="{{ route('catalogue', $categorieActive ? ['categorie_id' => $categorieActive->id] : []) }}" class="btn btn-contour">Effacer</a>
                @endif
            </div>

            @if($allColors->isNotEmpty())
                <fieldset class="sm:col-span-2 lg:col-span-4">
                    <legend class="etiquette">Couleurs</legend>
                    <div class="flex flex-wrap gap-2">
                        @foreach($allColors as $couleur)
                            <label class="cursor-pointer">
                                <input type="checkbox" name="couleurs[]" value="{{ $couleur }}" @checked(in_array($couleur, (array) request('couleurs', []))) class="peer sr-only">
                                <span class="flex min-h-10 items-center border border-trait-fort px-3.5 text-[0.92rem] transition-colors hover:border-brun-trait peer-checked:border-brun peer-checked:bg-brun peer-checked:text-white peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-brun">{{ $couleur }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif
        </form>
    </details>

    {{-- Résultats --}}
    <div class="mt-10 flex items-baseline justify-between gap-4">
        <h2 class="titre-display text-[1.5rem]">
            <span class="chiffres">{{ $produits->total() }}</span> {{ $produits->total() > 1 ? 'tissus' : 'tissu' }}
        </h2>
        @if($produits->lastPage() > 1)
            <p class="text-[0.95rem] text-encre-2">Page {{ $produits->currentPage() }} sur {{ $produits->lastPage() }}</p>
        @endif
    </div>

    @if($produits->isEmpty())
        <div class="mt-6 bg-carte px-6 py-16 text-center">
            <p class="titre-display text-[1.4rem]">Aucun tissu ne correspond à ces critères</p>
            <p class="mx-auto mt-2 max-w-[48ch] text-encre-2">Élargissez la fourchette de prix, retirez une couleur ou parcourez une autre famille de tissu.</p>
            <a href="{{ route('catalogue') }}" class="btn btn-brun mt-6">Voir tout le catalogue</a>
        </div>
    @elseif($categorieActive)
        {{-- Rangée défilante : déborde jusqu'au bord droit de l'écran, la carte suivante dépasse pour inviter à glisser --}}
        <div x-data="rangeeDefilante" class="mt-6">
            <div x-ref="piste" @scroll.passive="suivre" tabindex="0" role="region" aria-label="Tissus {{ $categorieActive->nom }}, défilement horizontal"
                 class="rangee defilement-discret mr-[calc(50%-50vw)] flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 motion-safe:scroll-smooth pr-4 focus-visible:outline-offset-4 sm:pr-6 lg:gap-4 lg:pr-[calc(50vw-50%)]">
                @foreach($produits as $produit)
                    <x-carte-produit :produit="$produit"
                        class="w-[78%] shrink-0 snap-start sm:w-[44%] lg:w-[calc((100%-2rem)/3.3)] xl:w-[calc((100%-3rem)/3.6)]" />
                @endforeach
            </div>

            @if($produits->count() > 1)
                <div class="mt-6 flex items-center gap-5">
                    {{-- Curseur : sa largeur = la part visible de la rangée, il avance avec le défilement --}}
                    <div class="h-0.5 flex-1 overflow-hidden bg-trait" aria-hidden="true">
                        <div class="h-full bg-brun" :style="`width: ${part * 100}%; transform: translateX(${decalage * 100}%)`"></div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="glisser(-1)" :disabled="auDebut" class="btn btn-contour size-11 p-0 disabled:opacity-35">
                            <x-icone nom="fleche-gauche" /><span class="sr-only">Tissus précédents</span>
                        </button>
                        <button type="button" @click="glisser(1)" :disabled="aLaFin" class="btn btn-contour size-11 p-0 disabled:opacity-35">
                            <x-icone nom="fleche" /><span class="sr-only">Tissus suivants</span>
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @else
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:gap-4">
            @foreach($produits as $produit)
                <x-carte-produit :produit="$produit" class="revele" style="--delai: {{ ($loop->index % 4) * 60 }}ms" />
            @endforeach
        </div>

        <div class="mt-12">
            {{ $produits->links() }}
        </div>
    @endif
</div>

@if($autresProduits->isNotEmpty())
    {{-- Les autres familles, sous la rangée de la catégorie --}}
    <section class="border-t border-trait bg-papier-2 py-14 lg:py-20" aria-labelledby="titre-autres">
        <div class="conteneur">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="titre-autres" class="titre-display text-[1.7rem] lg:text-[2.1rem]">D'autres tissus à découvrir</h2>
                    <p class="mt-1 text-encre-2">Les dernières arrivées des autres familles.</p>
                </div>
                <a href="{{ route('catalogue') }}" class="lien-fleche shrink-0">Tout le catalogue <x-icone nom="fleche" /></a>
            </div>
            {{-- Téléphone : rangée qui glisse à l'horizontale jusqu'au bord de l'écran (la carte suivante dépasse) ;
                 tablette et ordinateur : grille --}}
            <div tabindex="0" role="region" aria-label="D'autres tissus à découvrir, défilement horizontal"
                 class="defilement-discret mt-8 mr-[calc(50%-50vw)] flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 pr-4 motion-safe:scroll-smooth focus-visible:outline-offset-4 sm:mr-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:pb-0 sm:pr-0 lg:grid-cols-4 lg:gap-4">
                @foreach($autresProduits as $produit)
                    <x-carte-produit :produit="$produit" class="w-[78%] shrink-0 snap-start sm:w-auto" />
                @endforeach
            </div>
        </div>
    </section>
@endif

@endsection
