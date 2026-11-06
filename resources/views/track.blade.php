@extends('layouts.app')
@section('title', 'Suivi de commande · ' . boutique('nom'))
@section('content')

<x-en-tete-page titre="Suivi de commande" :chapeau="'Numéro de suivi ' . $commande->tracking_number" />

<div class="conteneur max-w-4xl pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition flex flex-col gap-4 bg-carte p-6 sm:flex-row sm:items-center sm:justify-between sm:p-8">
        <div>
            <p class="text-[0.92rem] text-encre-2">Commande</p>
            <p class="titre-display chiffres text-[1.8rem]">n° {{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="sm:text-right">
            <p class="text-[0.92rem] text-encre-2">Statut actuel</p>
            <x-statut-commande :statut="$commande->statut" class="mt-1 !text-[0.95rem]" />
        </div>
    </div>

    @include('partials.suivi-commande', ['statut' => $commande->statut])

    <dl class="revele mt-8 grid gap-6 bg-carte p-6 sm:grid-cols-[12rem_1fr] sm:p-8">
        <dt class="text-encre-2">Commandée le</dt>
        <dd class="font-medium">{{ $commande->created_at->translatedFormat('j F Y à H:i') }}</dd>
        <dt class="text-encre-2">Livraison</dt>
        <dd>{{ collect(explode(' | ', $commande->adresse_livraison ?? ''))->reject(fn ($p) => str_starts_with($p, 'GPS:'))->filter()->join(', ') ?: 'Retrait en boutique' }}</dd>
    </dl>

    <a href="{{ route('accueil') }}" class="lien-fleche mt-8 flex-row-reverse justify-end"><span>Retour à la boutique</span><x-icone nom="fleche-gauche" /></a>
</div>

@endsection
