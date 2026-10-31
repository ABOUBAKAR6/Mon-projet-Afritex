<x-guest-layout>
    <h1 class="titre-display text-[2.1rem]">Mot de passe oublié</h1>
    <p class="mt-2 text-encre-2">Indiquez votre adresse e-mail : nous vous envoyons un lien pour choisir un nouveau mot de passe.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <x-primary-button class="btn-lg w-full">Recevoir le lien</x-primary-button>
    </form>

    <p class="mt-8 border-t border-trait pt-6 text-encre-2"><a href="{{ route('login') }}" class="lien font-medium">Retour à la connexion</a></p>
</x-guest-layout>
