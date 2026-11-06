@props(['id' => 'password'])
{{-- Champ mot de passe avec un bouton œil pour l'afficher ou le masquer.
     Usage : <x-champ-mot-de-passe id="password" name="password" required autocomplete="current-password" /> --}}
<div class="relative" x-data="{ visible: false }">
    <input id="{{ $id }}" type="password" :type="visible ? 'text' : 'password'" {{ $attributes->merge(['class' => 'champ champ-mdp pr-12']) }}>
    <button type="button" @click="visible = !visible"
            class="voir-mdp absolute inset-y-0 right-0 grid w-11 place-items-center text-encre-2 transition-colors hover:text-brun"
            aria-controls="{{ $id }}" aria-label="Afficher le mot de passe" aria-pressed="false"
            :aria-label="visible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" :aria-pressed="visible.toString()">
        <x-icone nom="oeil" x-show="! visible" />
        <x-icone nom="oeil-barre" x-show="visible" x-cloak />
    </button>
</div>
