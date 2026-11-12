@extends('layouts.app')
@section('title', 'Espace Pro · ' . boutique('nom'))
@section('content')
@php
    $numeroWhatsapp = config('services.whatsapp.numero');
    $lienWhatsapp = $numeroWhatsapp ? 'https://wa.me/' . $numeroWhatsapp : null;
    $devisEnAttente = $commandes->firstWhere('statut', 'devis_envoye');
    $prochaine = $chiffres['prochaine_livraison'];
    $indicateurs = [
        ['Achats du mois', number_format($chiffres['achats_mois'], 0, ',', ' ') . ' F', 'Commandes confirmées depuis le 1er ' . now()->translatedFormat('F')],
        ['Commandes en cours', $chiffres['en_cours'], 'Acceptées, pas encore livrées'],
        ['Devis à valider', $chiffres['devis_a_valider'], $chiffres['devis_a_valider'] ? 'En attente de votre réponse' : 'Aucun en attente'],
        ['Prochaine livraison', $prochaine ? $prochaine->translatedFormat('j M') : 'Aucune', $prochaine ? ucfirst($prochaine->translatedFormat('l')) : 'Pas de date prévue'],
    ];
@endphp

@include('pro.partials.entete')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-12">
    <div class="apparition flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="titre-display text-[2.2rem] sm:text-[2.6rem]">Bonjour {{ \Illuminate\Support\Str::before(Auth::user()->name, ' ') }}</h1>
            <p class="mt-1 text-encre-2">Vos achats en gros, vos devis et vos prochaines livraisons.</p>
        </div>
        <a href="{{ route('pro.commander') }}" class="btn btn-brun btn-lg self-start sm:self-auto"><x-icone nom="plus" /> Nouvelle commande</a>
    </div>

    @if($chiffres['devis_a_valider'] > 0)
        <a href="{{ $devisEnAttente ? route('pro.commande', $devisEnAttente) : route('pro.commandes') }}"
           class="apparition group mt-8 flex flex-col gap-3 bg-alerte-doux p-5 transition-colors hover:bg-[color-mix(in_oklch,var(--alerte-doux)_70%,var(--or-clair))] sm:flex-row sm:items-center sm:justify-between sm:p-6" style="animation-delay: 60ms">
            <span class="flex items-start gap-3">
                <x-icone nom="courrier" class="mt-0.5 size-5 shrink-0 text-alerte" />
                <span>
                    <span class="block font-semibold text-encre">
                        {{ $chiffres['devis_a_valider'] > 1 ? $chiffres['devis_a_valider'] . ' devis attendent votre réponse' : 'Un devis attend votre réponse' }}
                    </span>
                    <span class="block text-[0.95rem] text-encre-2">Acceptez-le pour passer au paiement, ou refusez-le.</span>
                </span>
            </span>
            <span class="lien-fleche shrink-0">Voir le devis <x-icone nom="fleche" /></span>
        </a>
    @endif

    {{-- Les 4 chiffres --}}
    <dl class="apparition mt-8 grid grid-cols-2 bg-carte lg:grid-cols-4" style="animation-delay: 100ms">
        @foreach($indicateurs as [$libelle, $valeur, $detail])
            <div @class([
                'p-5 sm:p-6',
                'border-l border-trait' => $loop->index % 2 === 1,
                'border-t border-trait lg:border-t-0' => $loop->index >= 2,
                'lg:border-l' => $loop->index === 2,
            ])>
                <dt class="text-[0.9rem] text-encre-2">{{ $libelle }}</dt>
                <dd class="titre-display chiffres mt-1.5 text-[1.6rem] sm:text-[1.9rem]">{{ $valeur }}</dd>
                <dd class="mt-0.5 text-[0.85rem] text-encre-2">{{ $detail }}</dd>
            </div>
        @endforeach
    </dl>

    <div class="mt-10 grid items-start gap-8 lg:grid-cols-[1fr_20rem] lg:gap-12">
        <section aria-labelledby="titre-dernieres">
            <div class="flex items-baseline justify-between gap-4">
                <h2 id="titre-dernieres" class="titre-display text-[1.5rem]">Dernières commandes</h2>
                @if($commandes->isNotEmpty())
                    <a href="{{ route('pro.commandes') }}" class="lien-fleche text-[0.95rem]">Toutes <x-icone nom="fleche" /></a>
                @endif
            </div>

            @if($commandes->isEmpty())
                <div class="mt-4 bg-carte p-8 sm:p-10">
                    <p class="titre-display text-[1.3rem]">Aucune commande pro pour le moment</p>
                    <p class="mt-2 max-w-[52ch] text-encre-2">Saisissez vos quantités aux prix pro : la boutique vous renvoie un devis avec la livraison, que vous validez avant de payer.</p>
                    <a href="{{ route('pro.commander') }}" class="btn btn-brun mt-6">Passer ma première commande <x-icone nom="fleche" /></a>
                </div>
            @else
                <ul class="mt-4 divide-y divide-trait bg-carte">
                    @foreach($commandes as $commande)
                        <li class="grid gap-3 p-5 sm:grid-cols-[1fr_auto_auto] sm:items-center sm:gap-6">
                            <div class="min-w-0">
                                <a href="{{ route('pro.commande', $commande) }}" class="font-semibold hover:text-brun hover:underline">
                                    {{ $commande->estDevis() ? 'Devis' : 'Commande' }} n° <span class="chiffres">{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </a>
                                <p class="text-[0.92rem] text-encre-2">
                                    {{ $commande->created_at->translatedFormat('j F Y') }} · {{ $commande->items->count() }} ligne{{ $commande->items->count() > 1 ? 's' : '' }}
                                </p>
                            </div>
                            <x-statut-commande :statut="$commande->statut" client class="justify-self-start" />
                            <div class="flex items-center justify-between gap-4 sm:justify-end">
                                <span class="chiffres font-semibold">{{ number_format($commande->total, 0, ',', ' ') }} F</span>
                                <a href="{{ route('pro.commander', ['depuis' => $commande->id]) }}" class="btn btn-fantome btn-sm" title="Reprendre les quantités de cette commande">Recommander</a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <aside class="space-y-4">
            <section class="bg-brun-fonce p-6 text-white" aria-labelledby="titre-tarifs">
                <h2 id="titre-tarifs" class="titre-display text-[1.2rem]">Vos tarifs pro</h2>
                <p class="mt-2 text-[0.95rem] text-white/80">La liste à jour de nos prix revendeurs, format par format.</p>
                <a href="{{ route('pro.catalogue') }}" class="btn btn-or mt-5 w-full"><x-icone nom="telechargement" /> Télécharger le PDF</a>
            </section>
            @if($lienWhatsapp)
                <section class="bg-carte p-6" aria-labelledby="titre-aide">
                    <h2 id="titre-aide" class="titre-display text-[1.2rem]">Un besoin particulier ?</h2>
                    <p class="mt-2 text-[0.95rem] text-encre-2">Grosse quantité, tissu introuvable, livraison urgente : parlons-en directement.</p>
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn btn-contour mt-5 w-full"><x-icone nom="whatsapp" /> Écrire sur WhatsApp</a>
                </section>
            @endif
        </aside>
    </div>
</div>

@endsection
