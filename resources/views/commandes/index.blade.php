@extends('layouts.app')
@section('title', 'Mes commandes · AfriTex')
@section('content')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition">
        <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Mes commandes</h1>
        <p class="mt-1 text-encre-2">L'historique de vos achats et l'avancement de chaque livraison.</p>
    </div>

    @if($commandes->isEmpty())
        <div class="apparition mt-8 grid items-center gap-8 bg-carte p-8 sm:p-12 lg:grid-cols-[1fr_auto]" style="animation-delay: 80ms">
            <div>
                <p class="titre-display text-[1.5rem]">Aucune commande pour le moment</p>
                <p class="mt-2 max-w-[52ch] text-encre-2">Votre première commande apparaîtra ici, avec son suivi de livraison.</p>
            </div>
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Voir le catalogue <x-icone nom="fleche" /></a>
        </div>
    @else
        <ul class="mt-8 space-y-3">
            @foreach($commandes as $commande)
                <li class="revele" style="--delai: {{ min($loop->index, 5) * 60 }}ms">
                    <a href="{{ route('commandes.show', $commande) }}" class="group filet grid gap-4 bg-carte p-5 transition-colors hover:bg-brun-doux sm:grid-cols-[1fr_auto_auto] sm:items-center sm:gap-8 sm:p-6">
                        <div class="min-w-0">
                            <p class="titre-display text-[1.15rem]">Commande n° <span class="chiffres">{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</span></p>
                            <p class="mt-0.5 text-[0.95rem] text-encre-2">Passée le {{ $commande->created_at->translatedFormat('j F Y') }}</p>
                            <p class="mt-2 truncate text-[0.95rem]">
                                {{ $commande->items->take(3)->map(fn ($i) => $i->produit?->nom ?? 'Produit')->join(', ') }}@if($commande->items->count() > 3), et {{ $commande->items->count() - 3 }} autre(s)@endif
                            </p>
                        </div>
                        <x-statut-commande :statut="$commande->statut" class="justify-self-start" />
                        <div class="flex items-center justify-between gap-6 sm:justify-end">
                            <p class="titre-display chiffres text-[1.3rem]">{{ number_format($commande->total, 0, ',', ' ') }} F</p>
                            <x-icone nom="chevron-droite" class="size-5 text-brun transition-transform group-hover:translate-x-1" />
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="mt-10">{{ $commandes->links() }}</div>
    @endif
</div>

@endsection
