<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.tete')
    @stack('tete')
</head>
@php
    // [route, libellé, icône, routes actives, droit requis] : un secrétaire ne voit que ses sections (User::DROITS)
    $menuAdmin = collect([
        ['admin.dashboard', 'Tableau de bord', 'tableau-bord', ['admin.dashboard'], 'view-dashboard'],
        ['admin.commandes', 'Commandes', 'colis', ['admin.commandes', 'admin.commande.detail'], 'manage-orders'],
        ['admin.produits', 'Produits', 'boutique', ['admin.produits', 'admin.produits.*'], 'manage-products'],
        ['admin.stock', 'Stock', 'regle', ['admin.stock', 'admin.stock.*'], 'manage-products'],
        ['admin.categories', 'Catégories', 'dossier', ['admin.categories'], 'manage-categories'],
        ['admin.users', 'Utilisateurs', 'utilisateurs', ['admin.users'], 'manage-users'],
        ['admin.pros', 'Comptes pro', 'ciseaux', ['admin.pros'], 'manage-pros'],
        ['admin.equipe', 'Équipe', 'bouclier', ['admin.equipe'], 'proprietaire'],
        ['admin.compte', 'Mon compte', 'reglages', ['admin.compte'], null],
    ])->filter(fn ($entree) => $entree[4] === null || Auth::user()->can($entree[4]));
    // À traiter : commandes en attente et devis pro à chiffrer ; demandes de comptes pro à examiner
    $enAttente = Auth::user()->can('manage-orders') ? \App\Models\Commande::whereIn('statut', ['en_attente', 'devis_demande'])->count() : 0;
    $prosEnAttente = Auth::user()->can('manage-pros') ? \App\Models\ComptePro::where('statut', 'en_attente')->count() : 0;
@endphp
{{-- Back-office AfriTex : menu « nuit » en brun foncé, page active soulignée d'un trait or (comme Église) --}}
<body class="bg-papier text-[0.98rem] text-encre" x-data="{ menu: false }" @keydown.escape.window="menu = false">
<a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-toast focus:bg-brun focus:px-4 focus:py-2 focus:text-white">Aller au contenu</a>

<div class="lg:grid lg:min-h-svh lg:grid-cols-[16.5rem_1fr]">
    {{-- Barre du haut (téléphone) --}}
    <header class="sticky top-0 z-sticky flex h-16 items-center justify-between bg-brun-fonce px-4 text-white lg:hidden">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
            <x-logo taille="size-9" class="bg-or text-brun-fonce" />
            <span class="titre-display">{{ boutique('nom') }} · Admin</span>
        </a>
        <button type="button" @click="menu = true" aria-controls="menu-admin" :aria-expanded="menu" class="-mr-2 flex size-11 items-center justify-center hover:bg-white/10">
            <x-icone nom="menu" class="size-6" /><span class="sr-only">Ouvrir le menu</span>
        </button>
    </header>

    <div x-show="menu" x-cloak @click="menu = false" x-transition.opacity class="fixed inset-0 z-overlay bg-[oklch(0.2_0.03_var(--teinte-ombre)/0.5)] lg:hidden"></div>

    {{-- Menu latéral --}}
    <aside id="menu-admin"
           class="fixed inset-y-0 left-0 z-modal flex w-72 -translate-x-full flex-col bg-brun-fonce text-white/80 transition-transform duration-300 ease-out-fort lg:sticky lg:top-0 lg:z-auto lg:h-svh lg:w-auto lg:translate-x-0"
           :class="menu && '!translate-x-0'">
        <div class="flex h-20 items-center justify-between px-6">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <x-logo class="bg-or text-lg text-brun-fonce" />
                <span class="leading-tight">
                    <span class="titre-display block text-[1.15rem] text-white">{{ boutique('nom') }}</span>
                    <span class="block text-[0.82rem] text-or-doux">Administration</span>
                </span>
            </a>
            <button type="button" @click="menu = false" class="-mr-2 flex size-11 items-center justify-center hover:bg-white/10 lg:hidden">
                <x-icone nom="fermer" class="size-5" /><span class="sr-only">Fermer le menu</span>
            </button>
        </div>

        <nav aria-label="Administration" class="flex-1 overflow-y-auto px-3 py-2">
            <ul class="space-y-0.5">
                @foreach($menuAdmin as [$route, $titre, $icone, $actifs, $droit])
                    @php $actif = request()->routeIs(...$actifs); @endphp
                    <li>
                        <a href="{{ route($route) }}" @if($actif) aria-current="page" @endif
                           class="relative flex min-h-11 items-center gap-3 px-3 transition-colors hover:bg-white/10 hover:text-white {{ $actif ? 'bg-white/[0.09] font-medium text-white' : '' }}">
                            <x-icone :nom="$icone" class="size-[1.15rem] {{ $actif ? 'text-or' : '' }}" />
                            <span class="flex-1">{{ $titre }}</span>
                            @if($route === 'admin.commandes' && $enAttente > 0)
                                <span class="chiffres min-w-6 bg-or px-1.5 text-center text-[0.78rem] font-semibold leading-6 text-brun-fonce" title="Commandes en attente et devis à chiffrer">{{ $enAttente }}</span>
                            @endif
                            @if($route === 'admin.pros' && $prosEnAttente > 0)
                                <span class="chiffres min-w-6 bg-or px-1.5 text-center text-[0.78rem] font-semibold leading-6 text-brun-fonce" title="Demandes de comptes pro à examiner">{{ $prosEnAttente }}</span>
                            @endif
                            @if($actif)<span aria-hidden="true" class="absolute inset-x-3 bottom-0 h-0.5 bg-or-doux"></span>@endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="border-t border-white/10 px-3 py-4">
            @can('proprietaire')
                @php
                    $themeActif = boutique_theme();
                    $choixCouleurs = ['origine' => ['nom' => 'D\'origine', 'palette' => boutique_palette(null)]]
                        + collect(config('boutique.themes'))->map(fn ($t, $cle) => ['nom' => $t['nom'], 'palette' => boutique_palette($cle)])->all();
                    $pastille = fn ($p) => 'background: conic-gradient(from 225deg, ' . $p['foncee'] . ' 0 50%, ' . $p['accent'] . ' 0 85%, ' . $p['seconde'] . ' 0)';
                @endphp
                {{-- Couleurs de toute la boutique : un clic applique le thème au site, à l'admin et aux e-mails --}}
                <div x-data="{ ouvert: false }" @keydown.escape.stop="ouvert = false">
                    <button type="button" @click="ouvert = !ouvert" :aria-expanded="ouvert" aria-controls="choix-couleurs"
                            class="flex min-h-11 w-full items-center gap-3 px-3 text-left transition-colors hover:bg-white/10 hover:text-white">
                        <span class="size-[1.15rem] shrink-0 rounded-full ring-1 ring-white/40" style="{{ $pastille($choixCouleurs[$themeActif ?? 'origine']['palette']) }}" aria-hidden="true"></span>
                        <span class="flex-1">Couleurs <span class="text-white/55">· {{ $choixCouleurs[$themeActif ?? 'origine']['nom'] }}</span></span>
                        <x-icone nom="chevron-bas" class="size-4 transition-transform duration-200" x-bind:class="ouvert && 'rotate-180'" />
                    </button>
                    <div id="choix-couleurs" x-show="ouvert" x-cloak x-transition.opacity.duration.150ms class="px-3 pb-3 pt-1">
                        <p class="text-[0.8rem] text-white/60">Toute la boutique change de couleur.</p>
                        <form method="POST" action="{{ route('admin.couleurs') }}" class="mt-2 grid grid-cols-5 gap-1">
                            @csrf
                            @foreach($choixCouleurs as $cle => $choix)
                                @php $estActif = ($themeActif ?? 'origine') === $cle; @endphp
                                <button type="submit" name="theme" value="{{ $cle }}" title="{{ $choix['nom'] }}" aria-pressed="{{ $estActif ? 'true' : 'false' }}"
                                        class="group flex h-11 items-center justify-center">
                                    <span @class([
                                            'relative flex size-8 items-center justify-center rounded-full transition-transform duration-150 group-hover:scale-110 group-active:scale-95',
                                            'ring-2 ring-white ring-offset-2 ring-offset-brun-fonce' => $estActif,
                                            'ring-1 ring-white/25' => !$estActif,
                                          ]) style="{{ $pastille($choix['palette']) }}">
                                        @if($estActif)<x-icone nom="coche" class="size-4 text-white drop-shadow" />@endif
                                    </span>
                                    <span class="sr-only">{{ $choix['nom'] }}{{ $estActif ? ' (couleurs actuelles)' : '' }}</span>
                                </button>
                            @endforeach
                        </form>
                    </div>
                </div>
            @endcan
            <a href="{{ route('accueil') }}" class="flex min-h-11 items-center gap-3 px-3 transition-colors hover:bg-white/10 hover:text-white">
                <x-icone nom="oeil" class="size-[1.15rem]" /> Voir la boutique
            </a>
            <form method="POST" action="{{ route('espace.deconnexion') }}">
                @csrf
                <button type="submit" class="flex min-h-11 w-full items-center gap-3 px-3 text-left transition-colors hover:bg-white/10 hover:text-white">
                    <x-icone nom="sortie" class="size-[1.15rem]" /> Se déconnecter
                </button>
            </form>
            <a href="{{ route('admin.compte') }}" class="mt-3 flex min-h-14 items-center gap-3 px-3 py-2 transition-colors hover:bg-white/10">
                <x-avatar :utilisateur="Auth::user()" taille="size-9" class="text-[0.85rem]" />
                <span class="min-w-0 leading-tight">
                    <span class="block truncate text-[0.92rem] font-medium text-white">{{ Auth::user()->name }}</span>
                    <span class="block text-[0.8rem] text-or-doux">{{ Auth::user()->estProprietaire() ? 'Propriétaire' : 'Secrétaire' }} · Mon compte</span>
                </span>
            </a>
        </div>
    </aside>

    <main id="contenu" class="min-w-0">
        <div class="mx-auto w-full max-w-[90rem] px-4 pb-16 pt-8 sm:px-6 lg:px-10 lg:pt-10">
            @php
                // Envoi refusé par PHP avant l'ouverture de la session : voir bootstrap/app.php
                $alertes = [
                    'success' => session('success'),
                    'error' => session('error') ?? (request('fichier') === 'trop-lourd'
                        ? 'Le fichier envoyé est trop lourd (' . ini_get('post_max_size') . ' au plus). Choisissez une image plus légère.'
                        : null),
                ];
            @endphp
            @foreach(['success' => 'succes', 'error' => 'danger'] as $cle => $ton)
                @if($alertes[$cle])
                    <div x-data="{ visible: true }" x-show="visible" role="{{ $ton === 'danger' ? 'alert' : 'status' }}"
                         class="mb-6 flex items-start gap-3 p-4 {{ $ton === 'danger' ? 'bg-danger-doux text-danger' : 'bg-succes-doux text-succes' }}">
                        <x-icone :nom="$ton === 'danger' ? 'alerte' : 'coche'" class="mt-0.5 size-4 shrink-0" />
                        <p class="flex-1">{{ $alertes[$cle] }}</p>
                        <button type="button" @click="visible = false" class="-m-1 p-1 opacity-70 hover:opacity-100"><x-icone nom="fermer" class="size-4" /><span class="sr-only">Fermer</span></button>
                    </div>
                @endif
            @endforeach

            @yield('content')
        </div>
    </main>
</div>

<script>
// Lignes et cartes entières cliquables (attribut data-lien) : pratique au téléphone.
// Les liens, boutons, menus et champs à l'intérieur gardent leur propre action.
document.addEventListener('click', (e) => {
    const cible = e.target.closest('[data-lien]');
    if (!cible || e.target.closest('a, button, input, select, textarea, label, summary, details, form, dialog')) return;
    if (window.getSelection()?.toString()) return;
    if (e.ctrlKey || e.metaKey) { window.open(cible.dataset.lien, '_blank'); return; }
    window.location.href = cible.dataset.lien;
});
</script>
@stack('scripts')
</body>
</html>
