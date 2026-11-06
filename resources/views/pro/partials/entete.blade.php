{{--
  En-tête de l'espace de travail du revendeur validé : bandeau sombre à son enseigne,
  puis les onglets soulignés (défilables sur téléphone). Inclus en haut de chaque page pro.
--}}
@php
    $compte = Auth::user()->comptePro;
    $onglets = [
        ['pro.accueil', 'Tableau de bord', 'tableau-bord', ['pro.accueil']],
        ['pro.commander', 'Commander', 'panier', ['pro.commander']],
        ['pro.commandes', 'Mes commandes pro', 'colis', ['pro.commandes', 'pro.commande']],
        ['pro.compte', 'Mon compte pro', 'reglages', ['pro.compte']],
    ];
@endphp
<div class="sans-impression bg-brun-fonce text-white">
    <div class="conteneur flex flex-wrap items-center justify-between gap-x-6 gap-y-3 py-5">
        <p class="min-w-0 leading-tight">
            <span class="block text-[0.85rem] font-medium text-or-doux">Espace Pro</span>
            <span class="titre-display block truncate text-[1.35rem] sm:text-[1.5rem]">{{ $compte->boutique }}</span>
        </p>
        <a href="{{ route('pro.catalogue') }}" class="btn btn-clair btn-sm"><x-icone nom="telechargement" /> Tarifs pro en PDF</a>
    </div>
</div>
<div class="sans-impression border-b border-trait bg-carte">
    <nav aria-label="Espace Pro" class="conteneur defilement-discret flex overflow-x-auto">
        @foreach($onglets as [$route, $titre, $icone, $actifs])
            @php $actif = request()->routeIs(...$actifs); @endphp
            <a href="{{ route($route) }}" @if($actif) aria-current="page" @endif
               class="relative flex min-h-14 shrink-0 items-center gap-2 px-3.5 text-[0.95rem] transition-colors first:-ml-3.5 hover:bg-brun-doux hover:text-brun-fonce {{ $actif ? 'font-medium text-brun-fonce' : 'text-encre-2' }}">
                <x-icone :nom="$icone" class="size-[1.1rem]" /> {{ $titre }}
                @if($actif)<span aria-hidden="true" class="absolute inset-x-3.5 bottom-0 h-0.5 bg-brun"></span>@endif
            </a>
        @endforeach
    </nav>
</div>
