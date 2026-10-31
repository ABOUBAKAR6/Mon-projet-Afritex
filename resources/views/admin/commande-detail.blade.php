@extends('layouts.admin')
@section('title', 'Commande n° ' . str_pad($commande->id, 4, '0', STR_PAD_LEFT) . ' · AfriTex')
@section('content')
@php
    $formats = [
        'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
        'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
    ];
    $statuts = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'en_preparation' => 'En préparation', 'expedie' => 'Expédiée', 'livree' => 'Livrée', 'annulee' => 'Annulée'];
    $adresse = collect(explode(' | ', $commande->adresse_livraison ?? ''))->filter();
@endphp

<a href="{{ route('admin.commandes') }}" class="lien-fleche mb-5 flex-row-reverse justify-end text-[0.95rem]"><span>Toutes les commandes</span><x-icone nom="fleche-gauche" /></a>

<x-admin-entete :titre="'Commande n° ' . str_pad($commande->id, 4, '0', STR_PAD_LEFT)" :description="'Passée le ' . $commande->created_at->translatedFormat('j F Y à H:i')">
    <x-statut-commande :statut="$commande->statut" class="!px-3.5 !py-2 !text-[0.95rem]" />
</x-admin-entete>

<div class="grid items-start gap-6 xl:grid-cols-[1fr_22rem]">
    <div class="space-y-6">
        <section class="bg-carte" aria-labelledby="titre-articles">
            <h2 id="titre-articles" class="titre-display p-6 pb-4 text-[1.25rem]">Articles commandés</h2>
            <div class="overflow-x-auto">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Produit</th>
                            <th scope="col">Format</th>
                            <th scope="col" class="text-right">Qté</th>
                            <th scope="col" class="text-right">Prix unitaire</th>
                            <th scope="col" class="text-right">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->items as $item)
                            <tr>
                                <td>
                                    <span class="block font-medium">{{ $item->produit->nom ?? 'Produit supprimé' }}</span>
                                    <span class="block text-[0.85rem] text-brun">{{ $item->produit?->categorie?->nom }}</span>
                                </td>
                                <td>{{ $formats[$item->type_commande] ?? $item->type_commande }}</td>
                                <td class="chiffres text-right">{{ $item->quantite }}</td>
                                <td class="chiffres whitespace-nowrap text-right">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} F</td>
                                <td class="chiffres whitespace-nowrap text-right font-semibold">{{ number_format($item->sous_total, 0, ',', ' ') }} F</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="border-t border-trait-fort px-4 py-4 text-right font-medium">Total</td>
                            <td class="titre-display chiffres whitespace-nowrap border-t border-trait-fort px-4 py-4 text-right text-[1.3rem] text-brun">{{ number_format($commande->total, 0, ',', ' ') }} F</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        <section class="bg-carte p-6" aria-labelledby="titre-livraison">
            <h2 id="titre-livraison" class="titre-display text-[1.25rem]">Livraison</h2>
            @if($adresse->isEmpty())
                <p class="mt-3 text-encre-2">Aucune adresse indiquée.</p>
            @else
                <ul class="mt-3 space-y-1.5">
                    @foreach($adresse as $ligne)
                        <li class="{{ $loop->first ? 'font-medium' : 'text-encre-2' }}">{{ $ligne }}</li>
                    @endforeach
                </ul>
            @endif
        </section>

        @if($commande->paiement)
            <section class="bg-carte p-6" aria-labelledby="titre-paiement">
                <h2 id="titre-paiement" class="titre-display text-[1.25rem]">Paiement</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-[0.9rem] text-encre-2">Moyen</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $commande->paiement->methode)) }}</dd></div>
                    <div><dt class="text-[0.9rem] text-encre-2">Montant</dt><dd class="chiffres font-medium">{{ number_format($commande->paiement->montant, 0, ',', ' ') }} F</dd></div>
                    <div><dt class="text-[0.9rem] text-encre-2">Référence</dt><dd class="break-all font-medium">{{ $commande->paiement->reference ?: 'Aucune' }}</dd></div>
                    <div><dt class="text-[0.9rem] text-encre-2">Statut</dt><dd class="mt-0.5"><x-statut-commande :statut="$commande->paiement->statut" /></dd></div>
                </dl>
            </section>
        @endif
    </div>

    <aside class="space-y-6 xl:sticky xl:top-10">
        <section class="bg-brun-fonce p-6 text-white" aria-labelledby="titre-statut">
            <h2 id="titre-statut" class="titre-display text-[1.2rem]">Faire avancer la commande</h2>
            <form method="POST" action="{{ route('admin.commandes.statut', $commande->id) }}" class="mt-4">
                @csrf @method('PUT')
                <label for="statut" class="mb-1.5 block text-[0.9rem] text-or-doux">Nouveau statut</label>
                <select id="statut" name="statut" class="champ">
                    @foreach($statuts as $valeur => $libelle)
                        <option value="{{ $valeur }}" @selected($commande->statut === $valeur)>{{ $libelle }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-or mt-3 w-full">Mettre à jour le statut</button>
            </form>
        </section>

        <section class="bg-carte p-6" aria-labelledby="titre-client">
            <h2 id="titre-client" class="titre-display text-[1.2rem]">Client</h2>
            <div class="mt-4 flex items-center gap-3">
                <span aria-hidden="true" class="titre-display flex size-11 items-center justify-center rounded-full bg-brun-doux text-brun">{{ mb_strtoupper(mb_substr($commande->user->name ?? 'C', 0, 1)) }}</span>
                <span class="min-w-0">
                    <span class="block truncate font-medium">{{ $commande->user->name ?? 'Client supprimé' }}</span>
                    <span class="block truncate text-[0.9rem] text-encre-2">{{ $commande->user->email ?? '' }}</span>
                </span>
            </div>
            @if($commande->user?->email)
                <a href="mailto:{{ $commande->user->email }}" class="btn btn-contour mt-5 w-full"><x-icone nom="courrier" /> Écrire au client</a>
            @endif
        </section>
    </aside>
</div>

@endsection
