@extends('layouts.admin')
@section('title', 'Commandes · ' . boutique('nom'))
@section('content')
@php
    $statuts = [
        'en_attente' => 'En attente',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'expedie' => 'Expédiée',
        'livree' => 'Livrée',
        'annulee' => 'Annulée',
    ];
    // Filtre : les statuts de devis en plus (ils ne se choisissent pas à la main dans le menu « Statut » des lignes)
    $statutsFiltre = ['devis_demande' => 'Devis à chiffrer', 'devis_envoye' => 'Devis envoyé', 'devis_refuse' => 'Devis refusé'] + $statuts;
    $compteurs = array_values(array_filter([
        ['', 'Toutes', $stats['total'] ?? 0],
        ($stats['devis_demande'] ?? 0) ? ['devis_demande', 'Devis à chiffrer', $stats['devis_demande']] : null,
        ['en_attente', 'En attente', $stats['en_attente'] ?? 0],
        ['confirmee', 'Confirmées', $stats['confirmee'] ?? 0],
        ['en_preparation', 'En préparation', $stats['en_preparation'] ?? 0],
        ['expedie', 'Expédiées', $stats['expedie'] ?? 0],
        ['livree', 'Livrées', $stats['livree'] ?? 0],
        ['annulee', 'Annulées', $stats['annulee'] ?? 0],
    ]));
@endphp

<x-admin-entete titre="Commandes" description="Suivez chaque commande et faites-la avancer jusqu'à la livraison.">
    <p class="flex flex-col items-end bg-brun-fonce px-5 py-3 text-white">
        <span class="text-[0.85rem] text-or-doux">Encaissé aujourd'hui</span>
        <span class="titre-display chiffres text-[1.3rem] text-or">{{ number_format($stats['ca_jour'] ?? 0, 0, ',', ' ') }} F</span>
    </p>
</x-admin-entete>

{{-- Filtres rapides par statut --}}
<nav aria-label="Filtrer par statut" class="defilement-discret -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
    <ul class="flex w-max gap-1 bg-carte p-1 sm:w-auto">
        @foreach($compteurs as [$valeur, $libelle, $nombre])
            @php $actif = (string) request('statut', '') === $valeur; @endphp
            <li>
                <a href="{{ route('admin.commandes', array_filter(array_merge(request()->except(['statut', 'page']), ['statut' => $valeur]))) }}" @if($actif) aria-current="page" @endif
                   class="flex min-h-10 items-center gap-2 whitespace-nowrap px-3.5 text-[0.95rem] transition-colors {{ $actif ? 'bg-brun text-white' : 'text-encre-2 hover:bg-brun-doux hover:text-brun-fonce' }}">
                    {{ $libelle }} <span class="chiffres {{ $actif ? 'text-white/80' : 'text-encre-3' }}">{{ $nombre }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

{{-- Recherche et date --}}
<form method="GET" class="mt-4 grid gap-3 bg-carte p-4 sm:grid-cols-[1fr_12rem_12rem_auto] sm:items-end">
    <div>
        <label for="f-recherche" class="etiquette">Recherche</label>
        <input id="f-recherche" type="search" name="search" value="{{ request('search') }}" placeholder="Nom, e-mail ou numéro de commande" class="champ">
    </div>
    <div>
        <label for="f-statut" class="etiquette">Statut</label>
        <select id="f-statut" name="statut" class="champ">
            <option value="">Tous</option>
            @foreach($statutsFiltre as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected(request('statut') === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="f-date" class="etiquette">Date</label>
        <input id="f-date" type="date" name="date" value="{{ request('date') }}" class="champ">
    </div>
    <div class="flex gap-2">
        <button type="submit" class="btn btn-brun">Filtrer</button>
        @if(request()->hasAny(['search', 'statut', 'date']))
            <a href="{{ route('admin.commandes') }}" class="btn btn-contour">Effacer</a>
        @endif
    </div>
</form>

{{-- Tableau --}}
<section class="mt-4 bg-carte" aria-label="Liste des commandes">
    @if($commandes->isEmpty())
        <div class="px-6 py-16 text-center">
            <p class="titre-display text-[1.3rem]">Aucune commande trouvée</p>
            <p class="mt-1 text-encre-2">Modifiez les filtres ou effacez la recherche.</p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="tableau tableau-empile">
                <thead>
                    <tr>
                        <th scope="col">Commande</th>
                        <th scope="col">Client</th>
                        <th scope="col" class="text-right">Articles</th>
                        <th scope="col">Paiement</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="text-right">Total</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commandes as $commande)
                        <tr data-lien="{{ route('admin.commande.detail', $commande) }}">
                            <td>
                                <a href="{{ route('admin.commande.detail', $commande) }}" class="chiffres font-semibold text-brun hover:underline">n° {{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</a>
                                @if($commande->est_pro)<span class="pastille pastille-brun ml-1.5 !py-0.5">Pro</span>@endif
                                <span class="block text-[0.85rem] text-encre-2">{{ $commande->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td data-label="Client" data-large>
                                <span class="block font-medium">{{ $commande->user->name ?? 'Client supprimé' }}</span>
                                <span class="block text-[0.85rem] text-encre-2">{{ $commande->user->email ?? '' }}</span>
                            </td>
                            <td class="chiffres text-right" data-label="Articles">{{ $commande->items->count() }}</td>
                            <td data-label="Paiement">
                                @if($commande->paiement)
                                    <x-statut-commande :statut="$commande->paiement->statut" />
                                    <span class="mt-1 block text-[0.85rem] text-encre-2">{{ ucfirst(str_replace('_', ' ', $commande->paiement->methode)) }}</span>
                                @else
                                    <span class="text-encre-2">Non défini</span>
                                @endif
                            </td>
                            <td data-label="Statut"><x-statut-commande :statut="$commande->statut" /></td>
                            <td class="chiffres whitespace-nowrap text-right font-semibold" data-label="Total">{{ number_format($commande->total, 0, ',', ' ') }} F</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.commande.detail', $commande) }}" class="btn btn-fantome btn-sm">Détail</a>
                                    {{-- Un devis pro en cours avance par son chiffrage et la réponse du revendeur, pas à la main --}}
                                    @if(in_array($commande->statut, ['devis_demande', 'devis_envoye'], true))
                                        <a href="{{ route('admin.commande.detail', $commande) }}" class="btn btn-contour btn-sm">{{ $commande->statut === 'devis_demande' ? 'Chiffrer' : 'Modifier' }}</a>
                                    @else
                                    <div class="relative" x-data="{ ouvert: false }" @click.outside="ouvert = false" @keydown.escape="ouvert = false">
                                        <button type="button" @click="ouvert = !ouvert" :aria-expanded="ouvert" class="btn btn-contour btn-sm">
                                            Statut <x-icone nom="chevron-bas" />
                                        </button>
                                        <div x-show="ouvert" x-cloak x-transition.origin.top.right
                                             class="absolute left-0 top-full z-dropdown mt-1 w-52 sm:left-auto sm:right-0 border border-trait bg-carte py-1 text-left shadow-[0_18px_40px_-24px_oklch(0.25_0.04_var(--teinte-ombre)/0.45)]">
                                            <p class="px-3.5 py-2 text-[0.85rem] text-encre-2">Passer la commande en :</p>
                                            @foreach($statuts as $valeur => $libelle)
                                                @continue($valeur === $commande->statut)
                                                <form method="POST" action="{{ route('admin.commande.statut', $commande) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="statut" value="{{ $valeur }}">
                                                    <button type="submit" class="flex min-h-10 w-full items-center px-3.5 transition-colors hover:bg-brun-doux">
                                                        <x-statut-commande :statut="$valeur" />
                                                    </button>
                                                </form>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-trait p-4">{{ $commandes->links() }}</div>
    @endif
</section>

@endsection
