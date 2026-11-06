<x-guest-layout>
    <h1 class="titre-display text-[2.1rem]">Nouveau mot de passe</h1>
    <p class="mt-2 text-encre-2">Choisissez un mot de passe que vous n'utilisez nulle part ailleurs.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Adresse e-mail" />
            <x-text-input id="email" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div>
            <x-input-label for="password" value="Nouveau mot de passe" />
            <x-champ-mot-de-passe id="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>
        <div>
            <x-input-label for="password_confirmation" value="Confirmer le mot de passe" />
            <x-champ-mot-de-passe id="password_confirmation" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="btn-lg w-full">Enregistrer le mot de passe</x-primary-button>
    </form>
</x-guest-layout>
