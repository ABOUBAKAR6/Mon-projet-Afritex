<x-guest-layout>
    <h1 class="titre-display text-[2.1rem]">Confirmez votre mot de passe</h1>
    <p class="mt-2 text-encre-2">Cette partie de votre compte est protégée. Saisissez votre mot de passe pour continuer.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-8 space-y-5">
        @csrf
        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <x-primary-button class="btn-lg w-full">Confirmer</x-primary-button>
    </form>
</x-guest-layout>
