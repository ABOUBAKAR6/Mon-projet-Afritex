@extends('layouts.app')
@section('title', 'Mon compte pro · ' . boutique('nom'))
@section('content')

@include('pro.partials.entete')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-12">
    <div class="apparition">
        <h1 class="titre-display text-[2.2rem] sm:text-[2.6rem]">Mon compte pro</h1>
        <p class="mt-1 text-encre-2">Les informations de votre activité, utilisées par la boutique pour vos devis et vos livraisons.</p>
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_20rem] lg:gap-12">
        <form method="POST" action="{{ route('pro.compte.maj') }}" class="bg-carte p-6 sm:p-8">
            @csrf @method('PATCH')
            @include('pro.partials.champs-compte', ['compte' => $compte])
            <div class="mt-8 flex justify-end border-t border-trait pt-6">
                <button type="submit" class="btn btn-brun">Enregistrer les modifications</button>
            </div>
        </form>

        <aside class="space-y-4">
            <section class="bg-carte p-6" aria-labelledby="titre-statut">
                <h2 id="titre-statut" class="titre-display text-[1.15rem]">Statut</h2>
                <p class="mt-3"><span class="pastille pastille-succes">Compte pro ouvert</span></p>
                @if($compte->valide_le)
                    <p class="mt-3 text-[0.95rem] text-encre-2">Depuis le {{ $compte->valide_le->translatedFormat('j F Y') }}</p>
                @endif
            </section>
            <section class="bg-carte p-6" aria-labelledby="titre-connexion">
                <h2 id="titre-connexion" class="titre-display text-[1.15rem]">Connexion</h2>
                <dl class="mt-3 space-y-3 text-[0.95rem]">
                    <div><dt class="text-encre-2">Nom</dt><dd class="font-medium">{{ Auth::user()->name }}</dd></div>
                    <div><dt class="text-encre-2">E-mail</dt><dd class="break-all font-medium">{{ Auth::user()->email }}</dd></div>
                </dl>
                @if(Route::has('preferences'))
                    <a href="{{ route('preferences') }}" class="lien-fleche mt-4 text-[0.95rem]">Préférences du compte <x-icone nom="fleche" /></a>
                @endif
            </section>
        </aside>
    </div>
</div>

@endsection
