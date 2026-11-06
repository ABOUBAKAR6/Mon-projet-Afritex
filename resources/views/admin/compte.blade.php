@extends('layouts.admin')
@section('title', 'Mon compte · AfriTex')
@section('content')
@php
    // Réglages en deux colonnes : intitulé et aide à gauche, formulaire à droite
    $bloc = 'grid gap-6 bg-carte p-6 sm:p-8 lg:grid-cols-[17rem_1fr] lg:gap-12';
    $logoPresent = $logo && is_file(public_path($logo));
@endphp

<x-admin-entete titre="Mon compte" :description="Auth::user()->can('proprietaire') ? 'Votre profil et l’identité de la boutique.' : 'Votre profil : photo, coordonnées et mot de passe.'" />

<div class="max-w-5xl space-y-4">
    {{-- PHOTO DE PROFIL --}}
    <section class="{{ $bloc }}" aria-labelledby="titre-photo"
             x-data="televersementImage(600, 'image/jpeg')">
        <div>
            <h2 id="titre-photo" class="titre-display text-[1.3rem]">Photo de profil</h2>
            <p class="mt-1.5 text-[0.95rem] text-encre-2">Affichée dans le menu de l'administration. JPG, PNG ou WebP. Les grandes photos sont réduites automatiquement.</p>
        </div>
        <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
            <div class="relative size-28 shrink-0">
                <x-avatar :utilisateur="$admin" taille="size-28" class="text-[2rem]" x-show="!apercu" />
                <img x-show="apercu" x-cloak :src="apercu" alt="Aperçu de la nouvelle photo" class="size-28 rounded-full object-cover">
            </div>
            <div class="flex-1">
                <form method="POST" action="{{ route('admin.compte.photo') }}" enctype="multipart/form-data">
                    @csrf
                    <label for="photo" class="etiquette">{{ $admin->photo ? 'Changer de photo' : 'Ajouter une photo' }}</label>
                    <input type="file" name="photo" id="photo" accept="image/jpeg,image/png,image/webp" required class="champ py-2" @change="choisir" aria-describedby="erreur-photo">
                    <x-input-error id="erreur-photo" :messages="$errors->get('photo')" class="mt-2" />
                    <p x-show="erreur" x-cloak x-text="erreur" role="alert" class="erreur mt-2"></p>
                    <p x-show="enCours" x-cloak class="aide mt-2">Préparation de l'image…</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-brun" :disabled="!apercu"><x-icone nom="televersement" /> Enregistrer la photo</button>
                        <button type="button" x-show="apercu" x-cloak class="btn btn-fantome" @click="annuler($root.querySelector('#photo'))">Annuler</button>
                    </div>
                </form>
                @if($admin->photo)
                    <form method="POST" action="{{ route('admin.compte.photo.supprimer') }}" class="mt-2" x-show="!apercu" onsubmit="return confirm('Retirer votre photo de profil ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm text-danger hover:bg-danger-doux"><x-icone nom="corbeille" /> Retirer la photo</button>
                    </form>
                @endif
            </div>
        </div>
    </section>

    {{-- INFORMATIONS --}}
    <section class="{{ $bloc }}" aria-labelledby="titre-infos">
        <div>
            <h2 id="titre-infos" class="titre-display text-[1.3rem]">Informations</h2>
            <p class="mt-1.5 text-[0.95rem] text-encre-2">L'adresse e-mail sert à vous connecter à l'espace de gestion.</p>
        </div>
        <form method="POST" action="{{ route('admin.compte.informations') }}" class="grid gap-5 sm:grid-cols-2">
            @csrf @method('PATCH')
            <div class="sm:col-span-2">
                <label for="name" class="etiquette">Nom complet</label>
                <input type="text" name="name" id="name" value="{{ old('name', $admin->name) }}" required autocomplete="name" class="champ">
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>
            <div>
                <label for="email" class="etiquette">Adresse e-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email', $admin->email) }}" required autocomplete="email" class="champ">
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <label for="phone" class="etiquette">Téléphone <span class="font-normal text-encre-2">(facultatif)</span></label>
                <input type="tel" name="phone" id="phone" value="{{ old('phone', $admin->phone) }}" autocomplete="tel" class="champ" placeholder="07 00 00 00 00">
                <x-input-error :messages="$errors->get('phone')" class="mt-2" />
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-brun">Enregistrer les informations</button>
            </div>
        </form>
    </section>

    {{-- MOT DE PASSE --}}
    <section class="{{ $bloc }}" aria-labelledby="titre-mdp">
        <div>
            <h2 id="titre-mdp" class="titre-display text-[1.3rem]">Mot de passe</h2>
            <p class="mt-1.5 text-[0.95rem] text-encre-2">8 caractères au moins. Confirmez d'abord votre mot de passe actuel.</p>
        </div>
        <form method="POST" action="{{ route('admin.compte.mot-de-passe') }}" class="grid gap-5 sm:grid-cols-2">
            @csrf @method('PUT')
            <div class="sm:col-span-2 sm:max-w-[calc(50%-0.625rem)]">
                <label for="current_password" class="etiquette">Mot de passe actuel</label>
                <x-champ-mot-de-passe name="current_password" id="current_password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->motDePasse->get('current_password')" class="mt-2" />
            </div>
            <div>
                <label for="password" class="etiquette">Nouveau mot de passe</label>
                <x-champ-mot-de-passe name="password" id="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->motDePasse->get('password')" class="mt-2" />
            </div>
            <div>
                <label for="password_confirmation" class="etiquette">Confirmer le nouveau mot de passe</label>
                <x-champ-mot-de-passe name="password_confirmation" id="password_confirmation" required autocomplete="new-password" />
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="btn btn-brun">Changer le mot de passe</button>
            </div>
        </form>
    </section>

    {{-- LOGO DE LA BOUTIQUE : réservé au propriétaire --}}
    @can('proprietaire')
    <section class="{{ $bloc }}" aria-labelledby="titre-logo"
             x-data="televersementImage(512, 'image/png')">
        <div>
            <h2 id="titre-logo" class="titre-display text-[1.3rem]">Logo de la boutique</h2>
            <p class="mt-1.5 text-[0.95rem] text-encre-2">Remplace le « A » dans l'en-tête de la boutique, sur les pages de connexion et dans l'onglet du navigateur. Une image carrée, PNG à fond transparent de préférence. Les grandes images sont réduites automatiquement.</p>
        </div>
        <div>
            {{-- Aperçu sur les deux fonds où le logo apparaît : en-tête clair de la boutique, menu brun de l'administration --}}
            <div class="grid grid-cols-2 gap-px bg-trait sm:max-w-md">
                <div class="flex items-center gap-3 bg-papier p-4">
                    <img x-show="apercu" x-cloak :src="apercu" alt="" class="size-11 object-contain">
                    <span x-show="!apercu"><x-logo taille="size-11" class="bg-brun-fonce text-xl text-or" /></span>
                    <span class="titre-display text-[1.15rem]">AfriTex</span>
                </div>
                <div class="flex items-center gap-3 bg-brun-fonce p-4 text-white">
                    <img x-show="apercu" x-cloak :src="apercu" alt="" class="size-11 object-contain">
                    <span x-show="!apercu"><x-logo taille="size-11" class="bg-or text-xl text-brun-fonce" /></span>
                    <span class="titre-display text-[1.15rem]">AfriTex</span>
                </div>
            </div>
            <p class="mt-2 text-[0.88rem] text-encre-2">Aperçu sur la boutique (à gauche) et dans l'administration (à droite).</p>

            <form method="POST" action="{{ route('admin.compte.logo') }}" enctype="multipart/form-data" class="mt-6">
                @csrf
                <label for="logo" class="etiquette">{{ $logoPresent ? 'Changer le logo' : 'Ajouter un logo' }}</label>
                <input type="file" name="logo" id="logo" accept="image/jpeg,image/png,image/webp" required class="champ py-2 sm:max-w-md" @change="choisir">
                <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                <p x-show="erreur" x-cloak x-text="erreur" role="alert" class="erreur mt-2"></p>
                <p x-show="enCours" x-cloak class="aide mt-2">Préparation de l'image…</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-brun" :disabled="!apercu"><x-icone nom="televersement" /> Enregistrer le logo</button>
                    <button type="button" x-show="apercu" x-cloak class="btn btn-fantome" @click="annuler($root.querySelector('#logo'))">Annuler</button>
                </div>
            </form>
            @if($logoPresent)
                <form method="POST" action="{{ route('admin.compte.logo.supprimer') }}" class="mt-2" x-show="!apercu" onsubmit="return confirm('Retirer le logo et revenir au « A » ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm text-danger hover:bg-danger-doux"><x-icone nom="corbeille" /> Retirer le logo</button>
                </form>
            @endif
        </div>
    </section>
    @endcan
</div>
@endsection
