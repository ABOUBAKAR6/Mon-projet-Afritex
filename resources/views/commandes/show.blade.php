@extends('layouts.app')
@section('title', 'Commande n° ' . str_pad($commande->id, 4, '0', STR_PAD_LEFT) . ' · ' . boutique('nom'))
@section('content')
@php
    $formats = [
        'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
        'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
    ];
    $adresse = collect(explode(' | ', $commande->adresse_livraison ?? ''))->reject(fn ($p) => str_starts_with($p, 'GPS:'))->filter();
@endphp

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    <a href="{{ route('commandes.index') }}" class="lien-fleche flex-row-reverse text-[0.95rem]"><span>Mes commandes</span><x-icone nom="fleche-gauche" /></a>

    <div class="apparition mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Commande n° <span class="chiffres">{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</span></h1>
            <p class="mt-1 text-encre-2">Passée le {{ $commande->created_at->translatedFormat('j F Y à H:i') }}</p>
        </div>
        <x-statut-commande :statut="$commande->statut" class="self-start !text-[0.92rem] sm:self-auto" />
    </div>

    @include('partials.suivi-commande', ['statut' => $commande->statut])

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_22rem] lg:gap-12">
        <section class="bg-carte" aria-labelledby="titre-articles">
            <h2 id="titre-articles" class="titre-display px-6 pt-6 text-[1.3rem]">Articles</h2>
            <ul class="mt-3 divide-y divide-trait">
                @foreach($commande->items as $item)
                    <li class="flex items-center gap-4 px-6 py-4">
                        <span class="size-14 shrink-0 overflow-hidden bg-papier-2">
                            @if($item->produit?->image)
                                <img src="{{ asset($item->produit->image) }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            @if($item->produit)
                                <a href="{{ route('produit.show', $item->produit->slug) }}" class="block font-medium hover:text-brun hover:underline">{{ $item->produit->nom }}</a>
                            @else
                                <span class="block font-medium">Produit retiré du catalogue</span>
                            @endif
                            <span class="block text-[0.92rem] text-encre-2">{{ $formats[$item->type_commande] ?? ucfirst($item->type_commande) }} × <span class="chiffres">{{ $item->quantite }}</span>@if($item->couleur) · couleur : {{ $item->couleur }}@endif</span>
                        </span>
                        <span class="chiffres whitespace-nowrap font-medium">{{ number_format($item->sous_total, 0, ',', ' ') }} F</span>
                    </li>
                @endforeach
            </ul>
            <dl class="space-y-2.5 border-t border-trait px-6 py-5">
                @if($commande->coupon_code)
                    <div class="flex justify-between gap-4 text-[0.95rem]">
                        <dt class="text-encre-2">Code promo {{ $commande->coupon_code }}</dt>
                        <dd class="chiffres text-succes">@if($commande->discount_amount)− {{ number_format($commande->discount_amount, 0, ',', ' ') }} F @else appliqué @endif</dd>
                    </div>
                @endif
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="font-medium">Total</dt>
                    <dd class="titre-display chiffres text-[1.7rem] text-brun">{{ number_format($commande->total, 0, ',', ' ') }} F</dd>
                </div>
            </dl>
        </section>

        <aside class="space-y-4">
            @if($adresse->isNotEmpty())
                <section class="bg-carte p-6" aria-labelledby="titre-adresse">
                    <h2 id="titre-adresse" class="titre-display text-[1.15rem]">Livraison</h2>
                    <ul class="mt-3 space-y-1 text-[0.95rem]">
                        @foreach($adresse as $ligne)
                            <li class="{{ $loop->first ? 'font-medium' : 'text-encre-2' }}">{{ $ligne }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
            @if($commande->tracking_number)
                <a href="{{ route('track', $commande->tracking_number) }}" class="btn btn-contour w-full">Page de suivi à partager</a>
            @endif
        </aside>
    </div>
</div>

@endsection
