@extends('layouts.admin')
@section('title', 'Équipe · ' . boutique('nom'))
@section('content')
@php
    $fenetre = 'm-auto w-[min(36rem,calc(100vw-2rem))] bg-carte p-0 text-encre shadow-[0_24px_60px_-24px_oklch(0.2_0.03_var(--teinte-ombre)/0.5)] backdrop:bg-[oklch(0.2_0.03_var(--teinte-ombre)/0.55)]';
    $erreursCreation = $errors->creation;
@endphp

<div class="max-w-5xl space-y-4">
    {{-- En-tête dans la même largeur que la liste : le bouton s'aligne sur les cartes --}}
    <x-admin-entete titre="Équipe" description="Les secrétaires qui vous aident à gérer la boutique, et ce que chacun peut faire.">
        <button type="button" onclick="document.getElementById('modal-secretaire').showModal()" class="btn btn-brun"><x-icone nom="plus" /> Ajouter un secrétaire</button>
    </x-admin-entete>

    {{-- Le propriétaire : rappel, sans réglage --}}
    <section class="flex flex-wrap items-center gap-4 bg-carte p-5 sm:p-6" aria-label="Propriétaire">
        <x-avatar :utilisateur="Auth::user()" taille="size-12" />
        <div class="min-w-0 flex-1">
            <p class="font-medium">{{ Auth::user()->name }} <span class="font-normal text-encre-2">(vous)</span></p>
            <p class="text-[0.92rem] text-encre-2">{{ Auth::user()->email }}</p>
        </div>
        <span class="pastille pastille-brun">Propriétaire · tous les droits</span>
    </section>

    @forelse($secretaires as $secretaire)
        @php $accordes = $secretaire->permissions->pluck('name')->all(); @endphp
        <section class="bg-carte p-5 sm:p-6" aria-labelledby="secretaire-{{ $secretaire->id }}">
            <div class="flex flex-wrap items-center gap-4">
                <x-avatar :utilisateur="$secretaire" taille="size-12" />
                <div class="min-w-0 flex-1">
                    <h2 id="secretaire-{{ $secretaire->id }}" class="font-medium">{{ $secretaire->name }}</h2>
                    <p class="text-[0.92rem] text-encre-2">{{ $secretaire->email }}@if($secretaire->phone) · {{ $secretaire->phone }}@endif</p>
                </div>
                <span class="pastille">Secrétaire · {{ count($accordes) }} {{ count($accordes) > 1 ? 'droits' : 'droit' }}</span>
            </div>

            <form method="POST" action="{{ route('admin.equipe.update', $secretaire) }}" class="mt-5 border-t border-trait pt-5">
                @csrf @method('PUT')
                <fieldset>
                    <legend class="etiquette">Ce que {{ $secretaire->name }} peut faire</legend>
                    <div class="mt-1 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($droits as $cle => $droit)
                            <label class="flex min-h-11 cursor-pointer items-start gap-3 border border-trait p-3 transition-colors hover:border-brun-trait has-[:checked]:border-brun has-[:checked]:bg-brun-doux">
                                <input type="checkbox" name="droits[]" value="{{ $cle }}" @checked(in_array($cle, $accordes)) class="mt-0.5 size-5 shrink-0">
                                <span>
                                    <span class="block font-medium">{{ $droit['libelle'] }}</span>
                                    <span class="block text-[0.88rem] text-encre-2">{{ $droit['aide'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                    <button type="submit" class="btn btn-brun">Enregistrer les droits</button>
                    <button type="submit" form="retirer-{{ $secretaire->id }}" class="btn btn-sm text-danger hover:bg-danger-doux">
                        <x-icone nom="sortie" /> Retirer l'accès
                    </button>
                </div>
            </form>
            <form id="retirer-{{ $secretaire->id }}" method="POST" action="{{ route('admin.equipe.destroy', $secretaire) }}"
                  onsubmit="return confirm(@js($secretaire->name . ' n\'aura plus accès à l\'espace de gestion. Son compte redevient un compte client. Continuer ?'))">
                @csrf @method('DELETE')
            </form>
        </section>
    @empty
        <section class="bg-carte px-6 py-14 text-center">
            <p class="titre-display text-[1.35rem]">Aucun secrétaire pour l'instant</p>
            <p class="mx-auto mt-2 max-w-[52ch] text-encre-2">Créez un compte pour la personne qui vous aide : vous choisissez ce qu'elle peut voir et modifier, et vous pouvez le changer à tout moment.</p>
            <button type="button" onclick="document.getElementById('modal-secretaire').showModal()" class="btn btn-brun mt-6"><x-icone nom="plus" /> Ajouter un secrétaire</button>
        </section>
    @endforelse

    <p class="flex items-start gap-2 px-1 text-[0.92rem] text-encre-2">
        <x-icone nom="bouclier" class="mt-0.5 size-4 shrink-0" />
        Restent réservés au propriétaire : l'équipe, les droits d'administration et le logo de la boutique. Chaque secrétaire gère son propre profil depuis « Mon compte ».
    </p>
</div>

{{-- CRÉATION D'UN SECRÉTAIRE --}}
<dialog id="modal-secretaire" class="{{ $fenetre }}" aria-labelledby="titre-modal-secretaire">
    <form method="POST" action="{{ route('admin.equipe.store') }}" class="p-6 sm:p-8">
        @csrf
        <div class="flex items-start justify-between gap-4">
            <h2 id="titre-modal-secretaire" class="titre-display text-[1.5rem]">Nouveau secrétaire</h2>
            <button type="button" onclick="this.closest('dialog').close()" class="-m-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2"><x-icone nom="fermer" /><span class="sr-only">Fermer</span></button>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label for="s-nom" class="etiquette">Nom complet</label>
                <input type="text" name="name" id="s-nom" value="{{ old('name') }}" required autocomplete="off" class="champ">
                <x-input-error :messages="$erreursCreation->get('name')" class="mt-2" />
            </div>
            <div>
                <label for="s-email" class="etiquette">Adresse e-mail</label>
                <input type="email" name="email" id="s-email" value="{{ old('email') }}" required autocomplete="off" class="champ">
                <x-input-error :messages="$erreursCreation->get('email')" class="mt-2" />
            </div>
            <div>
                <label for="s-tel" class="etiquette">Téléphone <span class="font-normal text-encre-2">(facultatif)</span></label>
                <input type="tel" name="phone" id="s-tel" value="{{ old('phone') }}" autocomplete="off" class="champ">
            </div>
            <div class="sm:col-span-2" x-data="{ visible: false }">
                <label for="s-mdp" class="etiquette">Mot de passe de départ</label>
                <div class="relative">
                    <input :type="visible ? 'text' : 'password'" type="password" name="password" id="s-mdp" autocomplete="new-password" class="champ pr-24" aria-describedby="s-mdp-aide">
                    <button type="button" @click="visible = !visible" class="absolute inset-y-0 right-0 px-4 text-[0.92rem] text-brun hover:underline" x-text="visible ? 'Masquer' : 'Afficher'">Afficher</button>
                </div>
                <p id="s-mdp-aide" class="aide mt-1.5">8 caractères au moins. Transmettez-le à la personne : elle pourra le changer dans « Mon compte ». Si l'adresse est déjà celle d'un client, laissez vide pour garder son mot de passe.</p>
                <x-input-error :messages="$erreursCreation->get('password')" class="mt-2" />
            </div>
        </div>

        <fieldset class="mt-6">
            <legend class="etiquette">Droits accordés</legend>
            <div class="mt-1 grid gap-2 sm:grid-cols-2">
                @foreach($droits as $cle => $droit)
                    <label class="flex min-h-11 cursor-pointer items-start gap-3 border border-trait p-3 transition-colors hover:border-brun-trait has-[:checked]:border-brun has-[:checked]:bg-brun-doux">
                        <input type="checkbox" name="droits[]" value="{{ $cle }}" @checked(in_array($cle, old('droits', ['manage-orders']))) class="mt-0.5 size-5 shrink-0">
                        <span>
                            <span class="block font-medium">{{ $droit['libelle'] }}</span>
                            <span class="block text-[0.88rem] text-encre-2">{{ $droit['aide'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="mt-8 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-contour" onclick="this.closest('dialog').close()">Annuler</button>
            <button type="submit" class="btn btn-brun">Créer le compte</button>
        </div>
    </form>
</dialog>

<script>
document.querySelectorAll('dialog').forEach((d) => d.addEventListener('click', (e) => { if (e.target === d) d.close(); }));
@if($erreursCreation->any())
    // Erreur à la création : la fenêtre se rouvre avec les champs déjà remplis
    document.getElementById('modal-secretaire').showModal();
@endif
</script>

@endsection
