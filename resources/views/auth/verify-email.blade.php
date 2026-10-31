<x-guest-layout>
    <h1 class="titre-display text-[2.1rem]">Vérifiez votre adresse e-mail</h1>
    <p class="mt-2 text-encre-2">Merci pour votre inscription. Cliquez sur le lien que nous venons de vous envoyer par e-mail pour activer votre compte. Rien reçu ? Nous pouvons vous en envoyer un autre.</p>

    @if (session('status') == 'verification-link-sent')
        <x-auth-session-status class="mt-6" status="Un nouveau lien vient d'être envoyé à l'adresse indiquée lors de l'inscription." />
    @endif

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="btn-lg w-full sm:w-auto">Renvoyer le lien</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-fantome w-full sm:w-auto">Se déconnecter</button>
        </form>
    </div>
</x-guest-layout>
