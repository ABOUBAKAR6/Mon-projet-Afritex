<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.tete', ['titre' => 'Espace de gestion · AfriTex'])
    <meta name="robots" content="noindex, nofollow">
</head>
{{-- Connexion à l'espace de gestion : séparée de la boutique, fond « nuit » comme le back-office d'Église --}}
<body class="relative isolate min-h-svh bg-brun-fonce text-[1.0625rem] text-encre">
    <img src="{{ asset('images/hero-bg.avif') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-20">

    <main id="contenu" class="flex min-h-svh flex-col items-center justify-center px-4 py-12">
        <div class="apparition flex items-center gap-3 text-white">
            <x-logo taille="size-11" class="bg-or text-xl text-brun-fonce" />
            <span class="leading-tight">
                <span class="titre-display block text-[1.3rem]">AfriTex</span>
                <span class="block text-[0.9rem] text-or-doux">Espace de gestion</span>
            </span>
        </div>

        <div class="apparition mt-8 w-full max-w-md bg-carte p-7 sm:p-10" style="animation-delay: 80ms">
            <h1 class="titre-display text-[1.9rem]">Connexion</h1>
            <p class="mt-2 text-encre-2">Réservé à l'équipe AfriTex : commandes, produits, catégories et comptes.</p>

            @if (session('status'))
                <x-auth-session-status class="mt-6" :status="session('status')" />
            @endif

            <form method="POST" action="{{ route('espace.connexion.envoyer', request()->query('cle') ? ['cle' => request()->query('cle')] : []) }}" class="mt-8 space-y-5">
                @csrf
                <input type="hidden" name="cle" value="{{ request()->query('cle', '') }}">
                <div>
                    <x-input-label for="email" value="Adresse e-mail" />
                    <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" />
                </div>
                <div>
                    <x-input-label for="password" value="Mot de passe" />
                    <x-champ-mot-de-passe id="password" name="password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('password')" />
                </div>
                <label for="remember" class="flex min-h-11 cursor-pointer items-center gap-3">
                    <input id="remember" type="checkbox" name="remember" class="size-5">
                    <span>Rester connecté sur cet appareil</span>
                </label>
                <x-primary-button class="btn-lg w-full">Entrer dans l'espace de gestion</x-primary-button>
            </form>
        </div>

        <a href="{{ route('accueil') }}" class="mt-8 text-[0.95rem] text-white/75 hover:text-white hover:underline">Retour à la boutique</a>
    </main>
</body>
</html>
