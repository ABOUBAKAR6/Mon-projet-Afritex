@extends('layouts.app')
@section('title', 'Demande de compte pro · ' . boutique('nom'))
@section('content')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    <a href="{{ route('pro.accueil') }}" class="lien-fleche flex-row-reverse text-[0.95rem]"><span>Espace Pro</span><x-icone nom="fleche-gauche" /></a>

    <div class="apparition mt-6">
        <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Demande de compte pro</h1>
        <p class="mt-1 max-w-[62ch] text-encre-2">Présentez votre activité. La boutique examine votre demande et ouvre votre accès aux prix revendeurs.</p>
    </div>

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_20rem] lg:gap-12">
        <form method="POST" action="{{ route('pro.inscription.envoyer') }}" class="space-y-6">
            @csrf

            @guest
                <section class="bg-carte p-6 sm:p-8" aria-labelledby="titre-vous">
                    <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                        <h2 id="titre-vous" class="titre-display text-[1.3rem]">Vous</h2>
                        <p class="text-[0.92rem] text-encre-2">Déjà client ? <a href="{{ route('login') }}" class="lien">Connectez-vous d'abord</a></p>
                    </div>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="name" class="etiquette">Nom et prénom</label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" class="champ" required autocomplete="name" @error('name') aria-invalid="true" aria-describedby="erreur-name" @enderror>
                            @error('name')<p id="erreur-name" class="erreur">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="email" class="etiquette">Adresse e-mail</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" class="champ" required autocomplete="email" @error('email') aria-invalid="true" aria-describedby="erreur-email" @enderror>
                            @error('email')<p id="erreur-email" class="erreur">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password" class="etiquette">Mot de passe</label>
                            <input id="password" type="password" name="password" class="champ" required autocomplete="new-password" @error('password') aria-invalid="true" aria-describedby="erreur-password" @enderror>
                            @error('password')<p id="erreur-password" class="erreur">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="password_confirmation" class="etiquette">Confirmer le mot de passe</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="champ" required autocomplete="new-password">
                        </div>
                    </div>
                </section>
            @endguest

            <section class="bg-carte p-6 sm:p-8" aria-labelledby="titre-activite">
                <h2 id="titre-activite" class="titre-display text-[1.3rem]">Votre activité</h2>
                <div class="mt-5">
                    @include('pro.partials.champs-compte', ['compte' => null])
                </div>
            </section>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-[0.92rem] text-encre-2">En envoyant ce formulaire, vous acceptez nos <a href="{{ route('cgu') }}" class="lien">conditions générales</a>.</p>
                <button type="submit" class="btn btn-brun btn-lg">Envoyer ma demande <x-icone nom="fleche" /></button>
            </div>
        </form>

        <aside class="bg-papier-2 p-6 lg:sticky lg:top-28" aria-labelledby="titre-suite">
            <h2 id="titre-suite" class="titre-display text-[1.15rem]">Ensuite</h2>
            <ol class="mt-4 space-y-4 text-[0.95rem]">
                <li class="flex gap-3">
                    <span class="titre-display chiffres w-5 shrink-0 text-brun" aria-hidden="true">1</span>
                    <span class="text-encre-2">La boutique examine votre demande et peut vous écrire sur WhatsApp.</span>
                </li>
                <li class="flex gap-3">
                    <span class="titre-display chiffres w-5 shrink-0 text-brun" aria-hidden="true">2</span>
                    <span class="text-encre-2">Vous recevez un e-mail dès que votre accès pro est ouvert.</span>
                </li>
                <li class="flex gap-3">
                    <span class="titre-display chiffres w-5 shrink-0 text-brun" aria-hidden="true">3</span>
                    <span class="text-encre-2">Vous voyez les prix pro et commandez en gros, sur devis.</span>
                </li>
            </ol>
            <p class="mt-5 border-t border-trait pt-4 text-[0.92rem] text-encre-2">En attendant, vous pouvez acheter sur la boutique au prix public.</p>
        </aside>
    </div>
</div>

@endsection
