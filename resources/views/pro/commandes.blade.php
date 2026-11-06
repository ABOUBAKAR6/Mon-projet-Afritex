@extends('layouts.app')
@section('title', 'Mes commandes pro · ' . boutique('nom'))
@section('content')

@include('pro.partials.entete')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-12">
    <div class="apparition flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="titre-display text-[2.2rem] sm:text-[2.6rem]">Mes commandes pro</h1>
            <p class="mt-1 text-encre-2">Vos devis et vos commandes en gros, du plus récent au plus ancien.</p>
        </div>
        <a href="{{ route('pro.commander') }}" class="btn btn-brun self-start sm:self-auto"><x-icone nom="plus" /> Nouvelle commande</a>
    </div>

    @if($commandes->isEmpty())
        <div class="apparition mt-8 grid items-center gap-8 bg-carte p-8 sm:p-12 lg:grid-cols-[1fr_auto]" style="animation-delay: 80ms">
            <div>
                <p class="titre-display text-[1.5rem]">Aucune commande pro pour le moment</p>
                <p class="mt-2 max-w-[52ch] text-encre-2">Vos demandes de devis et vos commandes en gros apparaîtront ici, avec leur suivi de livraison.</p>
            </div>
            <a href="{{ route('pro.commander') }}" class="btn btn-brun btn-lg">Commander en gros <x-icone nom="fleche" /></a>
        </div>
    @else
        <ul class="mt-8 space-y-3">
            @foreach($commandes as $commande)
                <li class="revele group filet grid gap-4 bg-carte p-5 sm:grid-cols-[1fr_auto_auto] sm:items-center sm:gap-8 sm:p-6" style="--delai: {{ min($loop->index, 5) * 60 }}ms">
                    <div class="min-w-0">
                        <a href="{{ route('pro.commande', $commande) }}" class="titre-display text-[1.15rem] after:absolute after:inset-0 hover:text-brun">
                            {{ $commande->estDevis() ? 'Devis' : 'Commande' }} n° <span class="chiffres">{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</span>
                        </a>
                        <p class="mt-0.5 text-[0.95rem] text-encre-2">
                            Demandée le {{ $commande->created_at->translatedFormat('j F Y') }}
                            · {{ $commande->items->count() }} ligne{{ $commande->items->count() > 1 ? 's' : '' }}
                        </p>
                        @if($commande->livraison_prevue_le && !in_array($commande->statut, ['livree', 'annulee', 'devis_refuse'], true))
                            <p class="mt-1.5 flex items-center gap-2 text-[0.95rem]">
                                <x-icone nom="livraison" class="size-4 text-brun" /> Livraison prévue le {{ $commande->livraison_prevue_le->translatedFormat('l j F') }}
                            </p>
                        @endif
                    </div>
                    <x-statut-commande :statut="$commande->statut" client class="justify-self-start" />
                    <div class="flex items-center justify-between gap-4 sm:justify-end">
                        <p class="titre-display chiffres text-[1.3rem]">{{ number_format($commande->total, 0, ',', ' ') }} F</p>
                        {{-- Au-dessus du lien étendu à toute la ligne --}}
                        <a href="{{ route('pro.commander', ['depuis' => $commande->id]) }}" class="btn btn-contour btn-sm relative z-10">Recommander</a>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="mt-10">{{ $commandes->links() }}</div>
    @endif
</div>

@endsection
