<x-guest-layout>
    <h1 class="titre-display text-[2.1rem]">Créer votre compte</h1>
    <p class="mt-2 text-encre-2">Pour commander, suivre vos livraisons et garder vos tissus favoris.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Nom complet" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-input-label for="email" value="Adresse e-mail" />
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <div>
                <x-input-label for="phone" value="Téléphone" />
                <x-text-input id="phone" type="tel" name="phone" :value="old('phone')" required autocomplete="tel" placeholder="07 12 34 56 78" />
                <x-input-error :messages="$errors->get('phone')" />
            </div>
        </div>

        <div>
            <x-input-label for="password" value="Mot de passe" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" aria-describedby="aide-mdp" />
            <p id="aide-mdp" class="aide">8 caractères minimum.</p>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="btn-lg w-full">Créer mon compte</x-primary-button>
        <p class="text-[0.9rem] text-encre-2">En créant un compte, vous acceptez nos <a href="{{ route('cgu') }}" class="lien">conditions générales</a>.</p>
    </form>

    <p class="mt-8 border-t border-trait pt-6 text-encre-2">Déjà client ? <a href="{{ route('login') }}" class="lien font-medium">Se connecter</a></p>
</x-guest-layout>
