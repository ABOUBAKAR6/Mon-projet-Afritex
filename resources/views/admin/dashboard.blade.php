@extends('layouts.admin')
@section('title', 'Tableau de bord · ' . boutique('nom'))
@section('content')
@php
    $f = fn ($n) => number_format((float) $n, 0, ',', ' ');
    $pct = fn ($part, $tout) => $tout > 0 ? (int) round($part / $tout * 100) : 0;

    // Courbe du chiffre d'affaires (30 jours) : points en pourcentage du cadre
    $jours = collect($caParJour);
    $maxJour = max(1, $jours->max());
    $nbJours = max(1, $jours->count() - 1);
    $points = $jours->values()->map(fn ($v, $i) => [round($i / $nbJours * 100, 2), round(100 - ($v / $maxJour) * 88, 2)]);
    $ligne = $points->map(fn ($p) => $p[0] . ',' . $p[1])->join(' ');
    $dernier = $points->last();
    $dates = $jours->keys()->map(fn ($d) => \Carbon\Carbon::parse($d));

    $evolution = $caPrecedent > 0 ? (int) round(($ca - $caPrecedent) / $caPrecedent * 100) : null;
    $nbCommandes = array_sum($tranches);
    $maxTranche = max(1, max($tranches));
    $moyenneTranche = $nbCommandes / count($tranches);
    $panierMoyen = $nbVentes ? (int) round($ca / $nbVentes) : 0;

    // Étapes à traiter, dans l'ordre du parcours d'une commande
    $etapes = [
        ['devis_demande', 'Devis pro à chiffrer', 'bg-or'],
        ['en_attente', 'En attente de paiement', 'bg-alerte'],
        ['confirmee', 'Confirmées, à préparer', 'bg-brun'],
        ['en_preparation', 'En préparation', 'bg-brun-fonce'],
        ['expedie', 'En livraison', 'bg-encre-2'],
    ];
    $aTraiter = collect($etapes)->sum(fn ($e) => $parStatut[$e[0]] ?? 0);

    $libellesPaiement = ['especes' => 'À la livraison', 'mobile_money' => 'Mobile Money', 'carte' => 'Carte bancaire', 'stripe' => 'Carte bancaire', 'fedapay' => 'Mobile Money'];
    $paiementsRegroupes = collect($paiements)->groupBy(fn ($n, $methode) => $libellesPaiement[$methode] ?? ucfirst(str_replace('_', ' ', $methode)), true)
        ->map(fn ($groupe) => $groupe->sum())->sortDesc();
    $totalPaiements = $paiementsRegroupes->sum();
    $couleursPaiement = ['bg-brun-fonce', 'bg-brun', 'bg-or', 'bg-trait-fort'];

    $maxVendu = max(1, collect($topProduits)->max('montant'));
    $carte = 'bg-carte p-6';
    $intertitre = 'flex items-center gap-2 text-[0.92rem] font-medium text-encre-2';
@endphp

<x-admin-entete titre="Tableau de bord" :description="'Bonjour ' . \Illuminate\Support\Str::before(Auth::user()->name, ' ') . ', voici l\'activité des 30 derniers jours.'">
    @can('manage-products')<a href="{{ route('admin.produits.ajouter.form') }}" class="btn btn-brun"><x-icone nom="plus" /> Ajouter un produit</a>@endcan
    @can('manage-orders')<a href="{{ route('admin.commandes') }}" class="btn btn-contour">Toutes les commandes</a>@endcan
</x-admin-entete>

{{-- 1. VENTES : chiffre d'affaires, commandes reçues, panier moyen --}}
<div class="grid gap-6 xl:grid-cols-[1.55fr_1fr]">
    <section class="flex flex-col bg-brun-fonce p-6 text-white sm:p-8" aria-labelledby="titre-ca">
        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 id="titre-ca" class="flex items-center gap-2 text-[0.95rem] font-medium text-or-doux"><x-icone nom="billets" class="size-4" /> Chiffre d'affaires</h2>
            <p class="text-[0.85rem] text-white/65">ventes confirmées · 30 derniers jours</p>
        </div>
        <div class="mt-3 flex flex-wrap items-end gap-x-4 gap-y-2">
            <p class="titre-display chiffres text-[clamp(2.6rem,5vw,3.6rem)] leading-none">{{ $f($ca) }} <span class="text-[1.1rem] font-normal text-white/70">F</span></p>
            @if($evolution !== null)
                <span class="mb-1.5 px-2.5 py-1 text-[0.82rem] font-medium {{ $evolution >= 0 ? 'bg-or text-brun-fonce' : 'bg-white/15 text-white' }}">
                    {{ $evolution >= 0 ? '+' : '−' }}{{ abs($evolution) }} % sur 30 jours
                </span>
            @elseif($ca > 0)
                <span class="mb-1.5 bg-white/15 px-2.5 py-1 text-[0.82rem] font-medium">Premières ventes</span>
            @endif
        </div>
        <p class="mt-2 text-[0.92rem] text-white/75">
            @if($aEncaisser > 0)
                + <span class="chiffres font-medium text-white">{{ $f($aEncaisser) }} F</span> de commandes en attente de paiement
            @else
                Aucune commande en attente de paiement
            @endif
        </p>

        {{-- Courbe : aire légère et trait, jour par jour --}}
        <div class="relative mt-6 h-44 flex-1 sm:h-52" role="img"
             aria-label="Chiffre d'affaires jour par jour sur 30 jours, maximum {{ $f($maxJour) }} F en une journée">
            <span class="chiffres absolute right-0 top-0 text-[0.75rem] text-white/50">{{ $f($maxJour) }} F</span>
            <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-x-0 bottom-6 top-5 h-[calc(100%-2.75rem)] w-full overflow-visible" aria-hidden="true">
                <line x1="0" y1="100" x2="100" y2="100" stroke="white" stroke-opacity="0.15" vector-effect="non-scaling-stroke" />
                <line x1="0" y1="12" x2="100" y2="12" stroke="white" stroke-opacity="0.1" stroke-dasharray="3 4" vector-effect="non-scaling-stroke" />
                <polygon points="0,100 {{ $ligne }} 100,100" fill="var(--or)" fill-opacity="0.16" />
                <polyline points="{{ $ligne }}" fill="none" stroke="var(--or)" stroke-width="2" stroke-linejoin="round" vector-effect="non-scaling-stroke" />
            </svg>
            <span class="absolute size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full bg-or shadow-[0_0_0_4px_color-mix(in_oklch,var(--or)_30%,transparent)]" aria-hidden="true"
                  style="left: {{ $dernier[0] }}%; top: calc(1.25rem + (100% - 2.75rem) * {{ $dernier[1] / 100 }})"></span>
            <div class="chiffres absolute inset-x-0 bottom-0 flex justify-between text-[0.75rem] text-white/55" aria-hidden="true">
                <span>{{ $dates->first()->translatedFormat('j M') }}</span>
                <span>{{ $dates->get(15)->translatedFormat('j M') }}</span>
                <span>Aujourd'hui</span>
            </div>
        </div>
        <details class="mt-4 text-[0.88rem] text-white/80">
            <summary class="cursor-pointer hover:text-white">Voir les montants jour par jour</summary>
            <ul class="chiffres mt-3 grid grid-cols-2 gap-x-6 gap-y-1 sm:grid-cols-3">
                @foreach($jours as $date => $montant)
                    <li class="flex justify-between gap-3 border-b border-white/10 py-1"><span>{{ \Carbon\Carbon::parse($date)->translatedFormat('j M') }}</span><span>{{ $f($montant) }} F</span></li>
                @endforeach
            </ul>
        </details>
    </section>

    <div class="grid gap-6">
        <section class="{{ $carte }}" aria-labelledby="titre-commandes">
            <div class="flex items-baseline justify-between gap-2">
                <h2 id="titre-commandes" class="{{ $intertitre }}"><x-icone nom="colis" class="size-4 text-brun" /> Commandes reçues</h2>
                <p class="text-[0.82rem] text-encre-2">par tranche de 3 jours</p>
            </div>
            <p class="titre-display chiffres mt-2 text-[2.4rem] leading-none">{{ $nbCommandes }}</p>
            <div class="relative mt-5 h-24" role="img" aria-label="{{ $nbCommandes }} commandes reçues sur 30 jours, {{ number_format($moyenneTranche, 1, ',', ' ') }} en moyenne par tranche de 3 jours">
                <span class="absolute inset-x-0 border-t border-dashed border-trait-fort" style="bottom: {{ round($moyenneTranche / $maxTranche * 100, 1) }}%" aria-hidden="true">
                    <span class="absolute -top-4 left-0 text-[0.72rem] text-encre-3">moy.</span>
                </span>
                <ol class="relative flex h-full items-end gap-1.5" aria-hidden="true">
                    @foreach($tranches as $i => $nombre)
                        <li class="flex h-full flex-1 flex-col justify-end">
                            <span title="{{ $debut->copy()->addDays($i * 3)->translatedFormat('j M') }} : {{ $nombre }} commande{{ $nombre > 1 ? 's' : '' }}"
                                  @class(['block rounded-t-[3px]', 'bg-brun' => $loop->last, 'bg-[color:color-mix(in_oklch,var(--brun)_45%,var(--carte))]' => !$loop->last])
                                  style="height: {{ $nombre ? max(4, round($nombre / $maxTranche * 100, 1)) : 2 }}%"></span>
                        </li>
                    @endforeach
                </ol>
            </div>
            <div class="mt-2 flex justify-between text-[0.75rem] text-encre-3" aria-hidden="true">
                <span>{{ $debut->translatedFormat('j M') }}</span><span>Aujourd'hui</span>
            </div>
        </section>

        <section class="{{ $carte }}" aria-labelledby="titre-panier">
            <div class="flex items-baseline justify-between gap-2">
                <h2 id="titre-panier" class="{{ $intertitre }}"><x-icone nom="panier" class="size-4 text-brun" /> Panier moyen</h2>
                <p class="text-[0.82rem] text-encre-2">par vente confirmée</p>
            </div>
            <p class="titre-display chiffres mt-2 text-[2.4rem] leading-none">{{ $f($panierMoyen) }} <span class="text-[1rem] font-normal text-encre-2">F</span></p>
            <p class="mt-2 text-[0.9rem] text-encre-2">{{ $nbVentes }} vente{{ $nbVentes > 1 ? 's' : '' }} confirmée{{ $nbVentes > 1 ? 's' : '' }} sur la période</p>
        </section>
    </div>
</div>

{{-- 2. OPÉRATIONS : à traiter, stock, encaissement --}}
<div class="mt-6 grid gap-6 lg:grid-cols-3">
    <section class="{{ $carte }}" aria-labelledby="titre-traiter">
        <h2 id="titre-traiter" class="{{ $intertitre }}"><x-icone nom="horloge" class="size-4 text-brun" /> À traiter</h2>
        <p class="titre-display chiffres mt-2 text-[2.4rem] leading-none">{{ $aTraiter }} <span class="text-[1rem] font-normal text-encre-2">commande{{ $aTraiter > 1 ? 's' : '' }}</span></p>
        <ul class="mt-5 space-y-1">
            @foreach($etapes as [$statut, $libelle, $couleur])
                @php $nombre = $parStatut[$statut] ?? 0; @endphp
                <li>
                    <a @can('manage-orders') href="{{ route('admin.commandes', ['statut' => $statut]) }}" @endcan
                       class="-mx-2 flex min-h-10 items-center gap-3 px-2 text-[0.95rem] transition-colors hover:bg-brun-doux">
                        <span class="size-2 shrink-0 rounded-full {{ $couleur }}" aria-hidden="true"></span>
                        <span class="flex-1 {{ $nombre ? 'text-encre' : 'text-encre-2' }}">{{ $libelle }}</span>
                        <span class="chiffres {{ $nombre ? 'font-semibold' : 'text-encre-3' }}">{{ $nombre }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
        @if($prosEnAttente > 0)
            <a @can('manage-pros') href="{{ route('admin.pros', ['statut' => 'en_attente']) }}" @endcan
               class="mt-4 flex items-center gap-3 bg-alerte-doux px-3 py-2.5 text-[0.92rem] transition-colors hover:bg-[color:color-mix(in_oklch,var(--alerte-doux)_80%,var(--alerte))]">
                <x-icone nom="ciseaux" class="size-4 shrink-0 text-alerte" />
                <span class="flex-1">{{ $prosEnAttente }} demande{{ $prosEnAttente > 1 ? 's' : '' }} de compte pro à examiner</span>
                <x-icone nom="chevron-droite" class="size-4 text-alerte" />
            </a>
        @endif
    </section>

    <section class="{{ $carte }}" aria-labelledby="titre-stock">
        <div class="flex items-baseline justify-between gap-2">
            <h2 id="titre-stock" class="{{ $intertitre }}"><x-icone nom="alerte" class="size-4 text-brun" /> Stock à surveiller</h2>
            @can('manage-products')<a href="{{ route('admin.produits') }}" class="lien-fleche text-[0.88rem]">Gérer <x-icone nom="fleche" /></a>@endcan
        </div>
        @if($stocksFaibles->isEmpty())
            <p class="mt-6 flex items-center gap-2 bg-succes-doux p-4 text-[0.95rem] text-succes"><x-icone nom="coche" class="size-4" /> Tous les produits en vente ont au moins 5 unités.</p>
        @else
            <ul class="mt-4 divide-y divide-trait">
                @foreach($stocksFaibles as $produit)
                    <li class="flex items-center gap-3 py-2.5">
                        <span class="size-10 shrink-0 overflow-hidden bg-papier-2">
                            @if($produit->image && file_exists(public_path($produit->image)))
                                <img src="{{ asset($produit->image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-[0.95rem] font-medium">{{ $produit->nom }}</span>
                            <span class="block text-[0.82rem] {{ $produit->stock <= 0 ? 'font-medium text-danger' : 'text-encre-2' }}">
                                {{ $produit->stock <= 0 ? 'Épuisé' : $produit->stock . ' en stock' }} · seuil 5
                            </span>
                        </span>
                        <span class="h-1.5 w-14 shrink-0 bg-papier-2" aria-hidden="true">
                            <span class="block h-full {{ $produit->stock <= 1 ? 'bg-danger' : 'bg-alerte' }}" style="width: {{ max(4, min(100, $produit->stock / 5 * 100)) }}%"></span>
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <section class="{{ $carte }}" aria-labelledby="titre-encaissement">
        <h2 id="titre-encaissement" class="{{ $intertitre }}"><x-icone nom="carte-bancaire" class="size-4 text-brun" /> Encaissement et livraison</h2>
        <p class="mt-1 text-[0.85rem] text-encre-2">Sur les ventes confirmées des 30 derniers jours</p>
        @foreach([
            ['Encaissé', $pct($encaisse, $ca), $f($encaisse) . ' F sur ' . $f($ca) . ' F', 'bg-brun'],
            ['Livrées', $pct($nbLivrees, $nbVentes), $nbLivrees . ' sur ' . $nbVentes . ' vente' . ($nbVentes > 1 ? 's' : ''), 'bg-or'],
        ] as [$libelle, $valeur, $detail, $couleur])
            <div class="mt-5">
                <div class="flex items-baseline justify-between gap-3">
                    <span class="font-medium">{{ $libelle }}</span>
                    <span class="titre-display chiffres text-[1.6rem] leading-none">{{ $valeur }} %</span>
                </div>
                <div class="mt-2 h-2.5 bg-papier-2" role="meter" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $valeur }}" aria-label="{{ $libelle }}">
                    <div class="h-full {{ $couleur }}" style="width: {{ $valeur }}%"></div>
                </div>
                <p class="chiffres mt-1.5 text-[0.85rem] text-encre-2">{{ $detail }}</p>
            </div>
        @endforeach
    </section>
</div>

{{-- 3. DÉTAIL : dernières commandes, détail et gros, paiements, meilleures ventes --}}
<div class="mt-6 grid items-start gap-6 xl:grid-cols-[1.55fr_1fr]">
    <section class="bg-carte" aria-labelledby="titre-recentes">
        <div class="flex items-baseline justify-between gap-4 p-6 pb-3">
            <h2 id="titre-recentes" class="{{ $intertitre }}"><x-icone nom="colis" class="size-4 text-brun" /> Dernières commandes</h2>
            @can('manage-orders')<a href="{{ route('admin.commandes') }}" class="lien-fleche text-[0.88rem]">Tout voir <x-icone nom="fleche" /></a>@endcan
        </div>
        @if($commandesRecentes->isEmpty())
            <p class="px-6 pb-8 pt-2 text-encre-2">Aucune commande pour le moment.</p>
        @else
            <ul class="divide-y divide-trait">
                @foreach($commandesRecentes as $commande)
                    @php $client = $commande->user?->comptePro?->boutique ?? $commande->user?->name ?? 'Client supprimé'; @endphp
                    <li class="relative flex items-center gap-4 px-6 py-3.5 transition-colors hover:bg-brun-doux">
                        <span aria-hidden="true" class="titre-display flex size-10 shrink-0 items-center justify-center rounded-full {{ $commande->est_pro ? 'bg-brun-fonce text-or' : 'bg-brun-doux text-brun' }}">{{ mb_strtoupper(mb_substr($client, 0, 1)) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="flex items-center gap-2">
                                @can('manage-orders')
                                    <a href="{{ route('admin.commande.detail', $commande) }}" class="truncate font-medium after:absolute after:inset-0 hover:text-brun">{{ $client }}</a>
                                @else
                                    <span class="truncate font-medium">{{ $client }}</span>
                                @endcan
                                @if($commande->est_pro)<span class="pastille pastille-brun !py-0.5">Pro</span>@endif
                            </span>
                            <span class="chiffres block text-[0.85rem] text-encre-2">n° {{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }} · {{ $commande->created_at->translatedFormat('D j M') }}</span>
                        </span>
                        <span class="flex flex-col items-end gap-1">
                            <span class="chiffres font-semibold">{{ $f($commande->total) }} F</span>
                            <x-statut-commande :statut="$commande->statut" />
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>

    <div class="grid gap-6">
        <section class="{{ $carte }}" aria-labelledby="titre-repartition">
            <h2 id="titre-repartition" class="{{ $intertitre }}"><x-icone nom="boutique" class="size-4 text-brun" /> Détail, gros et paiements</h2>
            <dl class="mt-4 grid grid-cols-2 gap-4">
                <div>
                    <dt class="text-[0.85rem] text-encre-2">Détail</dt>
                    <dd class="titre-display chiffres text-[1.6rem] leading-tight">{{ $f($caDetail) }} F</dd>
                </div>
                <div>
                    <dt class="text-[0.85rem] text-encre-2">Gros (Espace Pro)</dt>
                    <dd class="titre-display chiffres text-[1.6rem] leading-tight">{{ $f($caGros) }} F</dd>
                </div>
            </dl>
            <div class="mt-3 flex h-2.5 gap-0.5 bg-papier-2" aria-hidden="true">
                @if($ca > 0)
                    <span class="bg-brun" style="width: {{ $pct($caDetail, $ca) }}%"></span>
                    <span class="bg-or" style="width: {{ $pct($caGros, $ca) }}%"></span>
                @endif
            </div>
            <ul class="mt-2 flex flex-wrap gap-x-6 gap-y-1 text-[0.85rem]">
                <li class="flex items-center gap-2"><span class="size-2 rounded-full bg-brun" aria-hidden="true"></span> Détail <span class="chiffres font-medium">{{ $pct($caDetail, $ca) }} %</span></li>
                <li class="flex items-center gap-2"><span class="size-2 rounded-full bg-or" aria-hidden="true"></span> Gros <span class="chiffres font-medium">{{ $pct($caGros, $ca) }} %</span></li>
            </ul>

            <h3 class="mt-6 border-t border-trait pt-4 text-[0.85rem] text-encre-2">Moyens de paiement</h3>
            @if($totalPaiements === 0)
                <p class="mt-2 text-[0.92rem] text-encre-2">Aucun paiement sur la période.</p>
            @else
                <div class="mt-2 flex h-2.5 gap-0.5" aria-hidden="true">
                    @foreach($paiementsRegroupes as $libelle => $nombre)
                        <span class="{{ $couleursPaiement[$loop->index] ?? 'bg-trait-fort' }}" style="width: {{ $pct($nombre, $totalPaiements) }}%"></span>
                    @endforeach
                </div>
                <ul class="mt-3 space-y-1.5 text-[0.88rem]">
                    @foreach($paiementsRegroupes as $libelle => $nombre)
                        <li class="flex items-center gap-2">
                            <span class="size-2 rounded-full {{ $couleursPaiement[$loop->index] ?? 'bg-trait-fort' }}" aria-hidden="true"></span>
                            <span class="flex-1">{{ $libelle }}</span>
                            <span class="chiffres font-medium">{{ $pct($nombre, $totalPaiements) }} %</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="{{ $carte }}" aria-labelledby="titre-top">
            <h2 id="titre-top" class="{{ $intertitre }}"><x-icone nom="etoile" class="size-4 text-brun" /> Meilleures ventes</h2>
            <p class="mt-1 text-[0.85rem] text-encre-2">En montant, sur les ventes confirmées des 30 derniers jours</p>
            @if($topProduits->isEmpty())
                <p class="mt-4 bg-papier-2 p-4 text-[0.92rem] text-encre-2">Les meilleures ventes apparaîtront ici dès les premières commandes confirmées.</p>
            @else
                <ol class="mt-4 space-y-3.5">
                    @foreach($topProduits as $top)
                        <li>
                            <div class="flex items-baseline justify-between gap-4 text-[0.92rem]">
                                <span class="truncate font-medium">{{ $top->nom }}</span>
                                <span class="chiffres shrink-0 text-encre-2">{{ $f($top->montant) }} F</span>
                            </div>
                            <div class="mt-1.5 h-1.5 bg-papier-2" aria-hidden="true">
                                <div class="h-full bg-brun" style="width: {{ max(3, round($top->montant / $maxVendu * 100)) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</div>

@endsection
