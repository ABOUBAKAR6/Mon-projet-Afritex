@extends('layouts.admin')
@section('title', 'Tableau de bord · AfriTex')
@section('content')
@php
    $nbClients = \App\Models\User::count();
    $nbEnAttente = \App\Models\Commande::where('statut', 'en_attente')->count();

    // Chiffre d'affaires confirmé des 7 derniers jours (fourni par le contrôleur)
    $jours = collect($ca_evolution ?? [])->map(fn ($montant, $date) => [
        'date' => \Carbon\Carbon::parse($date),
        'montant' => (float) $montant,
    ])->values();
    $maxJour = max(1, $jours->max('montant'));
    $totalSemaine = $jours->sum('montant');
    $meilleurJour = $jours->sortByDesc('montant')->first();
    // Graduation : 0, la moitié et le haut de l'axe (arrondi à un pas lisible)
    $pas = $maxJour > 100000 ? 50000 : ($maxJour > 20000 ? 10000 : ($maxJour > 5000 ? 2000 : 1000));
    $haut = max($pas, ceil($maxJour / $pas) * $pas);
    $f = fn ($n) => number_format($n, 0, ',', ' ') . ' F';

    $maxVendu = max(1, collect($top_produits ?? [])->max('total_vendu'));
@endphp

<x-admin-entete titre="Tableau de bord" :description="'Bonjour ' . \Illuminate\Support\Str::before(Auth::user()->name, ' ') . ', voici l\'activité de la boutique.'">
    @can('manage-products')<a href="{{ route('admin.produits.ajouter.form') }}" class="btn btn-brun"><x-icone nom="plus" /> Ajouter un produit</a>@endcan
    @can('manage-categories')<a href="{{ route('admin.categories') }}" class="btn btn-contour">Gérer les catégories</a>@endcan
</x-admin-entete>

{{-- CHIFFRES CLÉS --}}
<section aria-label="Chiffres clés" class="grid grid-cols-2 bg-carte lg:grid-cols-5 lg:divide-x lg:divide-trait">
    <div class="col-span-2 border-b border-trait p-6 lg:col-span-1 lg:border-b-0">
        <p class="text-[0.92rem] text-encre-2">Chiffre d'affaires confirmé</p>
        <p class="titre-display chiffres mt-2 text-[2.2rem] leading-none">{{ $f($ca_total) }}</p>
        <p class="mt-2 text-[0.88rem] text-encre-2">Commandes au statut « confirmée »</p>
    </div>
    <div class="border-b border-r border-trait p-6 lg:border-b-0 lg:border-r-0">
        <p class="text-[0.92rem] text-encre-2">Aujourd'hui</p>
        <p class="titre-display chiffres mt-2 text-[1.7rem] leading-none">{{ $f($ca_jour) }}</p>
    </div>
    <a @can('manage-orders') href="{{ route('admin.commandes', ['statut' => 'en_attente']) }}" @endcan class="group filet border-b border-trait p-6 transition-colors hover:bg-brun-doux lg:border-b-0">
        <p class="text-[0.92rem] text-encre-2">Commandes</p>
        <p class="titre-display chiffres mt-2 text-[1.7rem] leading-none">{{ $total_commandes }}</p>
        <p class="mt-2 text-[0.88rem] {{ $nbEnAttente ? 'font-medium text-alerte' : 'text-encre-2' }}">{{ $nbEnAttente }} en attente</p>
    </a>
    <a @can('manage-products') href="{{ route('admin.produits') }}" @endcan class="group filet border-r border-trait p-6 transition-colors hover:bg-brun-doux lg:border-r-0">
        <p class="text-[0.92rem] text-encre-2">Produits</p>
        <p class="titre-display chiffres mt-2 text-[1.7rem] leading-none">{{ $total_produits }}</p>
        <p class="mt-2 text-[0.88rem] text-encre-2">dans {{ $total_categories }} catégories</p>
    </a>
    <a @can('manage-users') href="{{ route('admin.users') }}" @endcan class="group filet p-6 transition-colors hover:bg-brun-doux">
        <p class="text-[0.92rem] text-encre-2">Comptes</p>
        <p class="titre-display chiffres mt-2 text-[1.7rem] leading-none">{{ $nbClients }}</p>
        <p class="mt-2 text-[0.88rem] text-encre-2">inscrits</p>
    </a>
</section>

<div class="mt-6 grid items-start gap-6 xl:grid-cols-[1.6fr_1fr]">
    {{-- VENTES DES 7 DERNIERS JOURS --}}
    <section class="bg-carte p-6" aria-labelledby="titre-ventes">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="titre-ventes" class="titre-display text-[1.25rem]">Ventes confirmées, 7 derniers jours</h2>
            <p class="chiffres text-[1.1rem] font-semibold">{{ $f($totalSemaine) }}</p>
        </div>
        <p class="mt-1 text-[0.95rem] text-encre-2">
            @if($totalSemaine > 0)
                Meilleur jour : {{ $meilleurJour['date']->translatedFormat('l j F') }}, {{ $f($meilleurJour['montant']) }}.
            @else
                Aucune vente confirmée cette semaine.
            @endif
        </p>

        <div class="mt-6 grid grid-cols-[auto_1fr] gap-3">
            {{-- Axe : trois graduations discrètes --}}
            <div class="chiffres flex h-56 flex-col justify-between pb-7 text-right text-[0.8rem] text-encre-3" aria-hidden="true">
                <span>{{ $f($haut) }}</span>
                <span>{{ $f($haut / 2) }}</span>
                <span>0 F</span>
            </div>
            <div class="relative h-56">
                <div class="pointer-events-none absolute inset-x-0 top-0 h-[calc(100%-1.75rem)]" aria-hidden="true">
                    <span class="absolute inset-x-0 top-0 border-t border-dashed border-trait"></span>
                    <span class="absolute inset-x-0 top-1/2 border-t border-dashed border-trait"></span>
                    <span class="absolute inset-x-0 bottom-0 border-t border-trait-fort"></span>
                </div>
                <ol class="relative grid h-full grid-cols-7 gap-2 sm:gap-3" aria-label="Ventes par jour">
                    @foreach($jours as $jour)
                        @php
                            $hauteur = $jour['montant'] > 0 ? max(2, round(($jour['montant'] / $haut) * 100, 1)) : 0;
                            $estAujourdhui = $jour['date']->isToday();
                        @endphp
                        <li class="group relative flex flex-col justify-end" tabindex="0"
                            aria-label="{{ $jour['date']->translatedFormat('l j F') }} : {{ $f($jour['montant']) }}">
                            <div class="relative flex h-[calc(100%-1.75rem)] items-end justify-center">
                                <span class="block w-full max-w-12 rounded-t-[4px] transition-colors duration-150 {{ $estAujourdhui ? 'bg-brun' : 'bg-[color:color-mix(in_oklch,var(--brun)_70%,var(--carte))]' }} group-hover:bg-brun-fonce group-focus:bg-brun-fonce"
                                      style="height: {{ $hauteur }}%"></span>
                                {{-- Infobulle au survol ou au focus --}}
                                <span class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-2 -translate-x-1/2 whitespace-nowrap bg-encre px-2.5 py-1.5 text-[0.82rem] text-white opacity-0 transition-opacity duration-150 group-hover:opacity-100 group-focus:opacity-100"
                                      style="bottom: {{ $hauteur }}%">
                                    <span class="block text-white/70">{{ $jour['date']->translatedFormat('l j') }}</span>
                                    <span class="chiffres font-semibold">{{ $f($jour['montant']) }}</span>
                                </span>
                            </div>
                            <span class="mt-2 h-5 text-center text-[0.8rem] {{ $estAujourdhui ? 'font-semibold text-encre' : 'text-encre-2' }}">{{ $estAujourdhui ? 'Auj.' : $jour['date']->translatedFormat('D') }}</span>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <details class="mt-4 text-[0.92rem]">
            <summary class="cursor-pointer text-brun hover:underline">Voir les montants en tableau</summary>
            <table class="tableau mt-3">
                <thead><tr><th scope="col">Jour</th><th scope="col" class="text-right">Montant confirmé</th></tr></thead>
                <tbody>
                    @foreach($jours as $jour)
                        <tr><td>{{ $jour['date']->translatedFormat('l j F') }}</td><td class="chiffres text-right">{{ $f($jour['montant']) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    </section>

    {{-- PRODUITS LES PLUS VENDUS --}}
    <section class="bg-carte p-6" aria-labelledby="titre-top">
        <h2 id="titre-top" class="titre-display text-[1.25rem]">Les plus vendus</h2>
        <p class="mt-1 text-[0.95rem] text-encre-2">Quantités commandées, toutes périodes.</p>
        @if(collect($top_produits ?? [])->isEmpty())
            <p class="mt-6 bg-papier-2 p-5 text-encre-2">Les ventes apparaîtront ici dès les premières commandes.</p>
        @else
            <ol class="mt-6 space-y-4">
                @foreach($top_produits as $top)
                    <li>
                        <div class="flex items-baseline justify-between gap-4 text-[0.95rem]">
                            <span class="truncate font-medium">{{ $top->nom }}</span>
                            <span class="chiffres shrink-0 text-encre-2">{{ number_format($top->total_vendu, 0, ',', ' ') }}</span>
                        </div>
                        <div class="mt-1.5 h-2 bg-papier-2" aria-hidden="true">
                            <div class="h-full rounded-r-[4px] bg-brun" style="width: {{ round(($top->total_vendu / $maxVendu) * 100) }}%"></div>
                        </div>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
</div>

<div class="mt-6 grid items-start gap-6 xl:grid-cols-[1.6fr_1fr]">
    {{-- DERNIÈRES COMMANDES --}}
    <section class="bg-carte" aria-labelledby="titre-recentes">
        <div class="flex items-baseline justify-between gap-4 p-6 pb-4">
            <h2 id="titre-recentes" class="titre-display text-[1.25rem]">Dernières commandes</h2>
            @can('manage-orders')<a href="{{ route('admin.commandes') }}" class="lien-fleche text-[0.92rem]">Toutes les commandes <x-icone nom="fleche" /></a>@endcan
        </div>
        <div class="overflow-x-auto">
            <table class="tableau">
                <thead>
                    <tr>
                        <th scope="col">Commande</th>
                        <th scope="col">Client</th>
                        <th scope="col">Statut</th>
                        <th scope="col" class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commandes_recentes as $commande)
                        <tr class="relative">
                            <td>@can('manage-orders')<a href="{{ route('admin.commande.detail', $commande->id) }}" class="chiffres font-semibold text-brun after:absolute after:inset-0 after:content-[''] hover:underline">n° {{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</a>@else<span class="chiffres font-semibold">n° {{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</span>@endcan</td>
                            <td>{{ $commande->user->name ?? 'Client supprimé' }}<span class="block text-[0.85rem] text-encre-2">{{ $commande->created_at->diffForHumans() }}</span></td>
                            <td><x-statut-commande :statut="$commande->statut" /></td>
                            <td class="chiffres text-right font-medium">{{ $f($commande->total) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-10 text-center text-encre-2">Aucune commande pour le moment.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- STOCKS FAIBLES --}}
    <section class="bg-carte p-6" aria-labelledby="titre-stocks">
        <h2 id="titre-stocks" class="titre-display text-[1.25rem]">Stocks à réassortir</h2>
        <p class="mt-1 text-[0.95rem] text-encre-2">Produits en vente avec moins de 5 unités.</p>
        @if(collect($stocks_faibles ?? [])->isEmpty())
            <p class="mt-6 flex items-center gap-2 bg-succes-doux p-4 text-succes"><x-icone nom="coche" class="size-4" /> Tous les stocks sont suffisants.</p>
        @else
            <ul class="mt-5 divide-y divide-trait">
                @foreach($stocks_faibles as $produit)
                    <li class="flex items-center justify-between gap-4 py-3">
                        <span class="min-w-0 truncate font-medium">{{ $produit->nom }}</span>
                        <span class="pastille {{ $produit->stock <= 0 ? 'pastille-danger' : 'pastille-attente' }}">{{ $produit->stock <= 0 ? 'Épuisé' : $produit->stock . ' restant' . ($produit->stock > 1 ? 's' : '') }}</span>
                    </li>
                @endforeach
            </ul>
            @can('manage-products')<a href="{{ route('admin.produits') }}" class="lien-fleche mt-4 text-[0.92rem]">Mettre à jour les stocks <x-icone nom="fleche" /></a>@endcan
        @endif
    </section>
</div>

@endsection
