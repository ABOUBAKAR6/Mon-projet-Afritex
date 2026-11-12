@extends('layouts.app')
@section('content')
@php
    $prixMin = \App\Models\Produit::whereNotNull('prix_metre')->where('prix_metre', '>', 0)->min('prix_metre');
    $nbProduits = \App\Models\Produit::count();
    $vedettes = $produits->take(5);
    $visuels = $produits->filter(fn ($p) => $p->image)->values();
    $lienWhatsapp = config('services.whatsapp.numero') ? 'https://wa.me/' . config('services.whatsapp.numero') : null;
@endphp

{{-- GRAND VISUEL --}}
{{-- Téléphone : visuel plus bas (plus de bande d'accès rapides à chevaucher, voir plus bas) --}}
<section class="relative isolate flex min-h-[20rem] items-center overflow-hidden bg-brun-fonce text-white sm:min-h-[38rem] lg:min-h-[44rem]">
    {{-- Jacquard à motifs : bande 16:9 sur grand écran, cadrage carré sur mobile (hero en portrait) --}}
    <picture class="absolute inset-0 -z-10">
        <source media="(max-width: 639px)" srcset="{{ asset('images/hero-tissu-mobile.avif') }}" type="image/avif">
        <source media="(max-width: 639px)" srcset="{{ asset('images/hero-tissu-mobile.webp') }}" type="image/webp">
        <source srcset="{{ asset('images/hero-tissu.avif') }}" type="image/avif">
        <source srcset="{{ asset('images/hero-tissu.webp') }}" type="image/webp">
        <img src="{{ asset('images/hero-tissu.jpg') }}" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover">
    </picture>
    {{-- Voile pour garantir la lisibilité du texte blanc sur la photo --}}
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,oklch(0.16_0.04_var(--teinte-ombre)/0.58)_0%,oklch(0.16_0.04_var(--teinte-ombre)/0.42)_45%,oklch(0.16_0.04_var(--teinte-ombre)/0.74)_100%)]"></div>
    {{-- La trame du jacquard est chargée : on assombrit davantage le centre, là où se trouve le texte --}}
    <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_60%_55%_at_50%_52%,oklch(0.16_0.04_var(--teinte-ombre)/0.62)_0%,oklch(0.16_0.04_var(--teinte-ombre)/0.35)_60%,transparent_100%)]"></div>

    <div class="conteneur pb-10 pt-8 text-center md:pb-28 md:pt-12 lg:pb-36">
        <p class="apparition hidden font-display text-[0.85rem] font-semibold uppercase tracking-[0.18em] text-white/85 sm:block">{{ boutique('slogan') }}</p>
        <h1 class="titre-display apparition mx-auto max-w-[16ch] text-[2.1rem] leading-[1.1] sm:mt-5 sm:text-[3.5rem] lg:text-[4.2rem]" style="animation-delay: 80ms">Le meilleur tissu, livré chez vous</h1>
        <p class="apparition mx-auto mt-3 max-w-[46ch] text-[0.95rem] text-white/85 sm:mt-5 sm:text-base lg:text-[1.15rem]" style="animation-delay: 160ms">{{ boutique('accroche') }}</p>

        {{-- Recherche visible d'emblée (même recherche que la loupe de l'en-tête : catalogue ?recherche=) --}}
        <form method="GET" action="{{ route('catalogue') }}" role="search" class="apparition mx-auto mt-6 flex max-w-xl text-left sm:mt-8" style="animation-delay: 200ms">
            <label for="recherche-accueil" class="sr-only">Rechercher un tissu, une couleur, une famille</label>
            <div class="relative min-w-0 flex-1">
                <x-icone nom="recherche" class="pointer-events-none absolute left-4 top-1/2 size-5 -translate-y-1/2 text-encre-2" />
                <input id="recherche-accueil" type="search" name="recherche" enterkeyhint="search" autocomplete="off"
                       placeholder="Un tissu, une couleur, une famille…" class="champ h-13 border-0 pl-12 text-encre">
            </div>
            <button type="submit" class="btn btn-brun h-13 shrink-0 px-4 sm:px-6">
                <x-icone nom="fleche" class="sm:hidden" /><span class="sr-only sm:not-sr-only">Rechercher</span>
            </button>
        </form>

        <div class="apparition mt-4 flex flex-col justify-center gap-3 sm:mt-6 sm:flex-row" style="animation-delay: 240ms">
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Voir le catalogue <x-icone nom="fleche" /></a>
            @guest
                <a href="{{ route('register') }}" class="btn btn-clair btn-lg hidden sm:inline-flex">Créer un compte</a>
            @else
                <a href="{{ route('panier') }}" class="btn btn-clair btn-lg hidden sm:inline-flex">Mon panier</a>
            @endguest
        </div>
    </div>
</section>

{{-- ACCÈS RAPIDES, À CHEVAL SUR LE VISUEL --}}
@php
    $blocs = [
        [
            'intitule' => 'Livraison',
            'valeur' => '24 à 72 h',
            'grand' => true,
            'texte' => 'Partout en Côte d\'Ivoire, offerte dès 50 000 F d\'achat.',
            'lien' => [route('faq'), 'Délais et zones'],
        ],
        [
            'intitule' => 'Au mètre',
            'valeur' => $prixMin ? 'Dès ' . number_format($prixMin, 0, ',', ' ') . ' F' : 'Au mètre',
            'grand' => (bool) $prixMin,
            'texte' => 'Le métrage exact pour votre modèle, ou le rouleau entier pour l\'atelier.',
            'lien' => [route('catalogue'), 'Voir les prix'],
        ],
        [
            'intitule' => 'Paiement',
            'valeur' => 'Mobile Money',
            'grand' => false,
            'texte' => 'Orange Money, MTN, Wave, Moov, carte bancaire ou espèces à la livraison.',
            'lien' => [route('faq'), 'Comment payer'],
        ],
    ];
@endphp
{{-- Pas au téléphone : il n'y montrait que le bloc Livraison, retiré à la demande de la boutique --}}
<div class="conteneur relative z-10 -mt-20 hidden md:block lg:-mt-24">
    <div class="grid bg-carte md:grid-cols-3 md:divide-x md:divide-trait">
        @foreach($blocs as $i => $bloc)
            <div class="group filet relative flex-col p-7 transition-colors duration-200 hover:bg-brun-doux lg:p-8 {{ $i === 0 ? 'flex' : 'hidden md:flex' }}">
                <h2 class="text-[0.8125rem] font-semibold uppercase tracking-[0.14em] text-brun">{{ $bloc['intitule'] }}</h2>
                <p class="titre-display chiffres mt-3.5 {{ $bloc['grand'] ? 'text-[2.6rem] leading-[0.95] tracking-[-0.03em]' : 'text-[1.5rem] leading-tight' }}">{{ $bloc['valeur'] }}</p>
                <p class="mt-2 text-[0.95rem] text-encre-2">{{ $bloc['texte'] }}</p>
                <a href="{{ $bloc['lien'][0] }}" class="lien-fleche mt-auto pt-5 text-[0.9rem] after:absolute after:inset-0 after:content-['']">{{ $bloc['lien'][1] }} <x-icone nom="fleche" /></a>
            </div>
        @endforeach
    </div>
</div>

{{-- FAMILLES DE TISSUS --}}
@if($categories->isNotEmpty())
<section class="pt-10 md:pt-20 lg:pt-28" aria-labelledby="titre-categories">
    <div class="conteneur">
        <div class="revele flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
            <x-titre-bloc intitule="Nos tissus" titre="Choisissez votre famille de tissu" id="titre-categories" />
            <a href="{{ route('catalogue') }}" class="lien-fleche shrink-0">Tout le catalogue <x-icone nom="fleche" /></a>
        </div>

        {{-- Grille de tuiles (2 colonnes au téléphone) ; au téléphone, une dernière tuile seule est centrée sous les autres --}}
        <ul aria-label="Familles de tissus" class="mt-6 grid grid-cols-2 gap-2 sm:mt-10 sm:grid-cols-3 sm:gap-3 lg:grid-cols-5">
            @foreach($categories as $cat)
                <li @class(['col-span-2 w-[calc(50%-0.25rem)] justify-self-center sm:col-span-1 sm:w-auto sm:justify-self-stretch' => $loop->last && $loop->count % 2 === 1])>
                    <a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}" class="group filet relative block bg-carte">
                        <div class="aspect-square overflow-hidden bg-papier-2">
                            @if(!empty($cat->image) && file_exists(public_path($cat->image)))
                                <img src="{{ asset($cat->image) }}" alt="" loading="lazy" decoding="async" class="zoom-doux h-full w-full object-cover">
                            @else
                                <span aria-hidden="true" class="titre-display flex h-full w-full items-center justify-center text-[3.5rem] text-brun-trait">{{ mb_substr($cat->nom, 0, 1) }}</span>
                            @endif
                        </div>
                        <span class="flex min-h-14 items-center justify-between gap-2 px-4 py-3 text-[0.98rem] font-medium transition-colors group-hover:text-brun-fonce">
                            {{ $cat->nom }}
                            <x-icone nom="fleche" class="size-4 shrink-0 text-brun opacity-0 transition-[opacity,transform] duration-200 group-hover:translate-x-1 group-hover:opacity-100" />
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- SÉLECTION --}}
<section class="pt-8 sm:pt-20 lg:pt-28" aria-labelledby="titre-selection">
    <div class="conteneur">
        <div class="revele flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
            <x-titre-bloc intitule="La sélection" titre="Les tissus les plus demandés" id="titre-selection" />
            <a href="{{ route('catalogue') }}" class="lien-fleche shrink-0">Voir les {{ $nbProduits }} tissus <x-icone nom="fleche" /></a>
        </div>

        @if($vedettes->isEmpty())
            <p class="mt-10 bg-carte p-10 text-center text-encre-2">Les premiers tissus arrivent bientôt dans la boutique.</p>
        @else
            {{-- Téléphone : rangée qui glisse à l'horizontale (la carte suivante dépasse) ; tablette et ordinateur : grille --}}
            <div tabindex="0" role="region" aria-label="Les tissus les plus demandés, défilement horizontal"
                 class="defilement-discret mt-6 mr-[calc(50%-50vw)] flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 pr-4 motion-safe:scroll-smooth focus-visible:outline-offset-4 sm:mt-10 sm:mr-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:pb-0 sm:pr-0 lg:grid-cols-4 lg:gap-4">
                @foreach($vedettes as $produit)
                    <x-carte-produit :produit="$produit" :grand="$loop->first"
                        class="w-[78%] shrink-0 snap-start sm:w-auto {{ $loop->first ? 'sm:col-span-2 lg:row-span-2' : '' }}" />
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- COMMENT ACHETER --}}
{{-- « Comment acheter » : pas au téléphone (retiré à la demande de la boutique ; la barre du bas mène au catalogue) --}}
<section class="mt-20 hidden bg-papier-2 py-16 sm:block lg:mt-28 lg:py-24" aria-labelledby="titre-achat">
    <div class="conteneur grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
        <div class="revele relative mb-12 mr-10 sm:mr-16">
            @if($visuels->get(1))
                <img src="{{ asset($visuels->get(1)->image) }}" alt="{{ $visuels->get(1)->nom }}" loading="lazy" class="aspect-[4/5] w-full max-w-md object-cover">
            @else
                <img src="{{ asset('images/hero-bg.avif') }}" alt="" loading="lazy" class="aspect-[4/5] w-full max-w-md object-cover">
            @endif
            @if($visuels->get(2))
                <img src="{{ asset($visuels->get(2)->image) }}" alt="{{ $visuels->get(2)->nom }}" loading="lazy"
                     class="absolute -bottom-12 -right-10 aspect-square w-[52%] max-w-64 border-[6px] border-papier-2 object-cover sm:-right-16">
            @endif
            @if($prixMin)
                <p class="absolute -right-10 top-6 bg-papier-2 py-2 pl-4 text-right sm:-right-16">
                    <span class="block text-[0.95rem] text-encre-2">Dès</span>
                    <span class="titre-display chiffres block text-[2.6rem] leading-none text-brun sm:text-[3.2rem]">{{ number_format($prixMin, 0, ',', ' ') }} F</span>
                    <span class="block text-[0.95rem] text-encre-2">le mètre</span>
                </p>
            @endif
        </div>

        <div class="revele" style="--delai: 120ms">
            <x-titre-bloc intitule="Comment acheter" titre="Au mètre ou au rouleau" id="titre-achat" />
            <p class="mt-5 text-[1.05rem] font-medium lg:text-[1.15rem]">Achetez exactement ce dont vous avez besoin, au mètre, ou profitez de tarifs avantageux sur les grandes quantités.</p>

            <dl class="mt-8 divide-y divide-trait border-y border-trait">
                <div class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-6">
                    <dt class="titre-display text-[1.05rem]">Au détail</dt>
                    <dd class="text-encre-2">Le métrage exact de votre modèle, à partir d'un mètre.</dd>
                </div>
                <div class="grid gap-1 py-4 sm:grid-cols-[9rem_1fr] sm:gap-6">
                    <dt class="titre-display text-[1.05rem]">En gros</dt>
                    <dd class="text-encre-2">Le rouleau entier, pour les ateliers, tailleurs et revendeurs.</dd>
                </div>
            </dl>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Choisir un tissu <x-icone nom="fleche" /></a>
                @auth
                    @if(Route::has('yardage.index'))
                        <a href="{{ route('yardage.index') }}" class="btn btn-contour btn-lg"><x-icone nom="regle" /> Calculer mon métrage</a>
                    @endif
                @elseif($lienWhatsapp)
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn btn-contour btn-lg"><x-icone nom="message" /> Demander conseil</a>
                @endauth
            </div>
        </div>
    </div>
</section>

@endsection
