<x-guest-layout>
    <h1 class="titre-display text-[2.1rem]">Bon retour parmi nous</h1>
    <p class="mt-2 text-encre-2">Connectez-vous pour retrouver votre panier, vos favoris et vos commandes.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <div class="flex items-baseline justify-between gap-4">
                <x-input-label for="password" value="Mot de passe" />
                @if (Route::has('password.request'))
                    <a class="mb-1.5 text-[0.9rem] text-brun hover:underline" href="{{ route('password.request') }}">Mot de passe oublié ?</a>
                @endif
            </div>
            <x-champ-mot-de-passe id="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <label for="remember_me" class="flex min-h-11 cursor-pointer items-center gap-3">
            <input id="remember_me" type="checkbox" name="remember" class="size-5">
            <span>Rester connecté sur cet appareil</span>
        </label>

        <x-primary-button class="btn-lg w-full">Se connecter</x-primary-button>
    </form>

    <p class="mt-8 border-t border-trait pt-6 text-encre-2">Pas encore de compte ? <a href="{{ route('register') }}" class="lien font-medium">Créer un compte</a></p>
</x-guest-layout>
