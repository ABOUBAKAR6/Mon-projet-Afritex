@extends('layouts.app')
@section('title', 'Mes favoris · AfriTex')
@section('content')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Mes favoris</h1>
            <p class="mt-1 text-encre-2">Les tissus que vous avez mis de côté, pour plus tard.</p>
        </div>
        @if($wishlist->isNotEmpty())
            <p class="text-encre-2"><span class="chiffres">{{ $wishlist->count() }}</span> {{ $wishlist->count() > 1 ? 'tissus' : 'tissu' }}</p>
        @endif
    </div>

    @if($wishlist->isEmpty())
        <div class="apparition mt-8 grid items-center gap-8 bg-carte p-8 sm:p-12 lg:grid-cols-[1fr_auto]" style="animation-delay: 80ms">
            <div>
                <p class="titre-display text-[1.5rem]">Aucun favori pour l'instant</p>
                <p class="mt-2 max-w-[52ch] text-encre-2">Touchez le cœur sur un tissu du catalogue pour le retrouver ici.</p>
            </div>
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Explorer le catalogue <x-icone nom="fleche" /></a>
        </div>
    @else
        <div class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 lg:gap-4">
            @foreach($wishlist as $item)
                @php $produit = $item->produit; @endphp
                @continue(!$produit)
                <div class="revele flex flex-col" style="--delai: {{ ($loop->index % 4) * 60 }}ms">
                    <x-carte-produit :produit="$produit" :favori="true" class="flex-1" />
                    <div class="grid grid-cols-[1fr_auto] gap-px bg-trait">
                        @if($produit->prix_metre)
                            <form action="{{ route('panier.ajouter') }}" method="POST">
                                @csrf
                                <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                                <input type="hidden" name="type_commande" value="metre">
                                <input type="hidden" name="quantite" value="1">
                                <button type="submit" class="flex h-12 w-full items-center justify-center gap-2 bg-carte text-[0.92rem] font-semibold text-brun transition-colors hover:bg-brun hover:text-white">
                                    <x-icone nom="panier" class="size-4" /> 1 mètre au panier
                                </button>
                            </form>
                        @else
                            <a href="{{ route('produit.show', $produit->slug) }}" class="flex h-12 items-center justify-center bg-carte text-[0.92rem] font-semibold text-brun hover:bg-brun-doux">Choisir un format</a>
                        @endif
                        <form action="{{ route('wishlist.remove', $item->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="flex size-12 items-center justify-center bg-carte text-encre-2 transition-colors hover:bg-danger-doux hover:text-danger">
                                <x-icone nom="corbeille" class="size-4" /><span class="sr-only">Retirer {{ $produit->nom }} des favoris</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
