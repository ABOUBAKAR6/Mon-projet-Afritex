<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.tete')
    <script src="{{ asset('js/cookie-manager.js') }}"></script>

    @if(config('analytics.google_analytics_id'))
        @include('components.google-analytics')
    @endif

    @stack('tete')
</head>
@php
    $numeroWhatsapp = config('services.whatsapp.numero');
    $lienWhatsapp = $numeroWhatsapp ? 'https://wa.me/' . $numeroWhatsapp : null;

    $nbPanier = 0;
    if (auth()->check()) {
        try {
            $nbPanier = collect(app(\App\Services\CartService::class)->getCart())->sum(fn ($i) => 1);
        } catch (\Throwable $e) {
            $nbPanier = 0;
        }
    }

    $navigation = [
        ['titre' => 'Catalogue', 'route' => 'catalogue', 'actif' => ['catalogue', 'produit.show']],
        ['titre' => 'À propos', 'route' => 'about', 'actif' => ['about']],
        ['titre' => 'Aide', 'route' => 'faq', 'actif' => ['faq', 'cgu', 'privacy']],
    ];
@endphp
<body class="boutique" x-data="{ menu: false, recherche: false }" x-effect="document.body.style.overflow = menu ? 'hidden' : ''" @keydown.escape.window="menu = false; recherche = false">
<div class="flex min-h-svh flex-col">

    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:left-3 focus:top-3 focus:z-toast focus:bg-brun focus:px-4 focus:py-2 focus:text-white">Aller au contenu</a>

    {{-- BANDEAU D'INFORMATIONS DÉFILANT --}}
    @php
        $infos = [
            ['texte' => 'Livraison offerte', 'detail' => 'dès 50 000 F d\'achat', 'lien' => route('catalogue')],
            ['texte' => 'Partout en Côte d\'Ivoire', 'detail' => 'livré sous 24 à 72 h', 'lien' => route('faq')],
            ['texte' => 'Mobile Money', 'detail' => 'Orange Money, MTN, Wave, Moov ou paiement à la livraison', 'lien' => route('faq')],
            ['texte' => 'Gros et détail', 'detail' => 'revendeurs : commandez en gros dans l\'Espace Pro', 'lien' => route('pro.accueil')],
            ['texte' => 'Retours', 'detail' => 'acceptés sous 7 jours', 'lien' => route('cgu')],
        ];
    @endphp
    <div class="bandeau-defilant sans-impression overflow-hidden bg-brun text-[0.8125rem] text-white" aria-label="Informations pratiques">
        <div class="bandeau-defilant__piste flex w-max py-1.5">
            @foreach([0, 1] as $copie)
                <ul class="flex shrink-0 items-center" @if($copie) aria-hidden="true" @endif>
                    @foreach($infos as $info)
                        <li class="flex items-center whitespace-nowrap">
                            <a href="{{ $info['lien'] }}" class="inline-flex items-center gap-2 hover:underline" @if($copie) tabindex="-1" @endif>
                                <span class="font-display font-semibold uppercase tracking-wide">{{ $info['texte'] }}</span>
                                <span class="opacity-85">{{ $info['detail'] }}</span>
                            </a>
                            <span aria-hidden="true" class="mx-6 text-[0.7rem] text-or">✦</span>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </div>

    {{-- EN-TÊTE --}}
    <header class="sans-impression sticky top-0 z-sticky border-b border-trait bg-carte">
        <div class="conteneur grid h-16 grid-cols-[1fr_auto] items-center gap-6 lg:h-20 lg:grid-cols-[1fr_auto_1fr]">
            <a href="{{ route('accueil') }}" class="flex min-w-0 items-center gap-3" @click="menu = false">
                <x-logo taille="size-10 lg:size-11" class="bg-brun-fonce text-xl text-or" />
                <span class="min-w-0 leading-tight">
                    <span class="titre-display block text-[1.2rem] lg:text-[1.4rem]">{{ boutique('nom') }}</span>
                    <span class="block truncate text-[0.8rem] text-encre-2 lg:text-sm">{{ boutique('slogan') }}</span>
                </span>
            </a>

            <nav aria-label="Navigation principale" class="hidden items-center gap-1 lg:flex">
                @foreach($navigation as $lien)
                    @php $actif = request()->routeIs(...$lien['actif']); @endphp
                    <a href="{{ route($lien['route']) }}" @if($actif) aria-current="page" @endif
                       class="relative px-3.5 py-2 text-[0.95rem] transition-colors hover:bg-brun-doux hover:text-brun-fonce {{ $actif ? 'font-medium text-brun-fonce' : 'text-encre-2' }}">
                        {{ $lien['titre'] }}
                        @if($actif)<span aria-hidden="true" class="absolute inset-x-3.5 -bottom-[1.3rem] h-0.5 bg-brun"></span>@endif
                    </a>
                    @if($loop->first)
                        {{-- Catégories : panneau déroulant --}}
                        <div class="relative" x-data="{ ouvert: false }" @click.outside="ouvert = false" @keydown.escape="ouvert = false">
                            <button type="button" @click="ouvert = !ouvert" :aria-expanded="ouvert" aria-controls="panneau-categories"
                                    class="flex items-center gap-1 px-3.5 py-2 text-[0.95rem] text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                Catégories <x-icone nom="chevron-bas" class="size-4 transition-transform duration-200" ::class="ouvert && 'rotate-180'" />
                            </button>
                            <div id="panneau-categories" x-show="ouvert" x-cloak
                                 x-transition:enter="transition duration-200 ease-out-fort" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition duration-150 ease-out" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                                 class="absolute left-1/2 top-full z-dropdown mt-[1.15rem] w-[34rem] -translate-x-1/2 border border-trait bg-carte p-3 shadow-[0_18px_40px_-24px_oklch(0.25_0.04_var(--teinte-ombre)/0.45)]">
                                <ul class="grid grid-cols-2 gap-x-2">
                                    @foreach($globalCategories as $cat)
                                        <li>
                                            <a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}" class="flex items-center gap-3 px-2 py-2 text-[0.95rem] text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                                @if(!empty($cat->image) && file_exists(public_path($cat->image)))
                                                    <img src="{{ asset($cat->image) }}" alt="" class="size-9 shrink-0 object-cover">
                                                @else
                                                    <span aria-hidden="true" class="titre-display flex size-9 shrink-0 items-center justify-center bg-brun-doux text-sm text-brun">{{ mb_substr($cat->nom, 0, 1) }}</span>
                                                @endif
                                                {{ $cat->nom }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('catalogue') }}" class="lien-fleche mt-2 flex border-t border-trait px-2 pt-3 text-[0.92rem]">Tout le catalogue <x-icone nom="fleche" /></a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </nav>

            <div class="flex items-center justify-end gap-1 sm:gap-2">
                <button type="button" @click="recherche = !recherche; $nextTick(() => recherche && $refs.champRecherche.focus())" :aria-expanded="recherche" aria-controls="barre-recherche"
                        class="flex size-11 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                    <x-icone nom="recherche" class="size-[1.35rem]" />
                    <span class="sr-only">Rechercher un tissu</span>
                </button>

                @auth
                    <div class="relative hidden sm:block" x-data="{ ouvert: false }" @click.outside="ouvert = false" @keydown.escape="ouvert = false">
                        <button type="button" @click="ouvert = !ouvert" :aria-expanded="ouvert" aria-controls="menu-compte"
                                class="flex h-11 items-center gap-2 px-3 text-[0.95rem] text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                            <x-icone nom="compte" class="size-[1.3rem]" />
                            <span class="hidden max-w-[9rem] truncate xl:inline">{{ \Illuminate\Support\Str::before(Auth::user()->name, ' ') }}</span>
                            <x-icone nom="chevron-bas" class="size-4" />
                        </button>
                        <div id="menu-compte" x-show="ouvert" x-cloak
                             x-transition:enter="transition duration-200 ease-out-fort" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 top-full z-dropdown mt-2 w-60 border border-trait bg-carte py-2 shadow-[0_18px_40px_-24px_oklch(0.25_0.04_var(--teinte-ombre)/0.45)]">
                            <p class="truncate px-4 pb-2 text-sm text-encre-2">{{ Auth::user()->email }}</p>
                            @php
                                $liensCompte = array_filter([
                                    ['pro.accueil', 'Espace Pro', 'boutique'],
                                    ['commandes.index', 'Mes commandes', 'colis'],
                                    ['wishlist', 'Mes favoris', 'coeur'],
                                    ['alertes.index', 'Alertes de stock', 'cloche'],
                                    ['yardage.index', 'Calculateur de métrage', 'regle'],
                                    ['inspirations.index', 'Inspirations', 'ciseaux'],
                                    ['preferences', 'Préférences', 'reglages'],
                                ], fn ($l) => Route::has($l[0]));
                            @endphp
                            @foreach($liensCompte as [$nom, $titre, $icone])
                                <a href="{{ route($nom) }}" class="flex items-center gap-3 px-4 py-2.5 text-[0.95rem] text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                    <x-icone :nom="$icone" class="size-[1.1rem] text-encre-2" /> {{ $titre }}
                                </a>
                            @endforeach
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-trait pt-1">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-4 py-2.5 text-left text-[0.95rem] text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                    <x-icone nom="sortie" class="size-[1.1rem] text-encre-2" /> Se déconnecter
                                </button>
                            </form>
                        </div>
                    </div>
                    <a href="{{ route('panier') }}" class="btn btn-brun hidden sm:inline-flex" @if(request()->routeIs('panier')) aria-current="page" @endif>
                        <x-icone nom="panier" /> Panier
                        @if($nbPanier > 0)
                            <span class="chiffres -mr-1 ml-0.5 flex min-w-6 items-center justify-center bg-or px-1.5 text-[0.8rem] font-semibold leading-6 text-brun-fonce">{{ $nbPanier }}</span>
                        @endif
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-fantome hidden sm:inline-flex">Se connecter</a>
                    <a href="{{ route('register') }}" class="btn btn-brun hidden sm:inline-flex">Créer un compte</a>
                @endauth

                <button type="button" @click="menu = !menu" :aria-expanded="menu" aria-controls="menu-mobile"
                        class="-mr-2 flex size-11 items-center justify-center hover:bg-papier-2 lg:hidden">
                    <x-icone nom="menu" class="size-6" x-show="!menu" />
                    <x-icone nom="fermer" class="size-6" x-show="menu" x-cloak />
                    <span class="sr-only" x-text="menu ? 'Fermer le menu' : 'Ouvrir le menu'">Ouvrir le menu</span>
                </button>
            </div>
        </div>

        {{-- Barre de recherche dépliable --}}
        <div id="barre-recherche" x-show="recherche" x-cloak
             x-transition:enter="transition duration-200 ease-out-fort" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-trait bg-papier">
            <form method="GET" action="{{ route('catalogue') }}" role="search" class="conteneur flex gap-2 py-3">
                <label for="recherche-globale" class="sr-only">Rechercher un tissu, une couleur, une catégorie</label>
                <input id="recherche-globale" x-ref="champRecherche" type="search" name="recherche" value="{{ request('recherche') }}"
                       placeholder="Un tissu, une couleur, une catégorie…" class="champ flex-1">
                <button type="submit" class="btn btn-brun"><x-icone nom="recherche" /> Rechercher</button>
            </form>
        </div>

        {{-- Menu mobile plein écran --}}
        <div id="menu-mobile" x-show="menu" x-cloak class="absolute inset-x-0 top-full h-[calc(100svh-4rem)] overflow-y-auto border-t border-trait bg-papier lg:hidden">
            <div class="conteneur flex flex-col py-4">
                <nav aria-label="Navigation mobile" class="flex flex-col">
                    @foreach($navigation as $lien)
                        @php $actif = request()->routeIs(...$lien['actif']); @endphp
                        <a href="{{ route($lien['route']) }}" @click="menu = false" @if($actif) aria-current="page" @endif
                           class="-mx-3 flex min-h-14 items-center border-b border-trait px-3 text-[1.35rem] transition-colors hover:bg-brun-doux hover:text-brun-fonce {{ $actif ? 'bg-brun-doux font-medium text-brun-fonce' : 'text-encre' }}">
                            <span class="titre-display">{{ $lien['titre'] }}</span>
                        </a>
                    @endforeach
                    @php $actifPro = request()->routeIs('pro.*'); @endphp
                    <a href="{{ route('pro.accueil') }}" @click="menu = false" @if($actifPro) aria-current="page" @endif
                       class="-mx-3 flex min-h-14 items-center border-b border-trait px-3 text-[1.35rem] transition-colors hover:bg-brun-doux hover:text-brun-fonce {{ $actifPro ? 'bg-brun-doux font-medium text-brun-fonce' : 'text-encre' }}">
                        <span class="titre-display">Espace Pro</span>
                    </a>
                    @auth
                        <a href="{{ route('commandes.index') }}" class="-mx-3 flex min-h-14 items-center border-b border-trait px-3 text-[1.35rem] text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                            <span class="titre-display">Mes commandes</span>
                        </a>
                        <a href="{{ route('wishlist') }}" class="-mx-3 flex min-h-14 items-center border-b border-trait px-3 text-[1.35rem] text-encre transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                            <span class="titre-display">Mes favoris</span>
                        </a>
                    @endauth
                </nav>

                <h2 class="mt-8 text-[0.95rem] font-medium text-encre-2">Catégories de tissus</h2>
                <ul class="mt-3 grid grid-cols-2 gap-2">
                    @foreach($globalCategories as $cat)
                        <li>
                            <a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}" class="flex min-h-12 items-center bg-carte px-3 text-[0.98rem] text-encre active:bg-brun-doux">{{ $cat->nom }}</a>
                        </li>
                    @endforeach
                </ul>

                @auth
                    <a href="{{ route('panier') }}" class="btn btn-brun btn-lg mt-8"><x-icone nom="panier" /> Voir mon panier @if($nbPanier > 0)({{ $nbPanier }})@endif</a>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="btn btn-contour btn-lg w-full">Se déconnecter</button>
                    </form>
                @else
                    <a href="{{ route('register') }}" class="btn btn-brun btn-lg mt-8">Créer un compte</a>
                    <a href="{{ route('login') }}" class="btn btn-contour btn-lg mt-3">Se connecter</a>
                @endauth
            </div>
        </div>
    </header>

    {{-- MESSAGES --}}
    @foreach(['success' => 'succes', 'error' => 'danger'] as $cle => $ton)
        @if(session($cle))
            <div x-data="{ visible: true }" x-init="setTimeout(() => visible = false, 5000)" x-show="visible"
                 x-transition:enter="transition duration-300 ease-out-fort" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition duration-200 ease-out" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 role="{{ $ton === 'danger' ? 'alert' : 'status' }}"
                 class="fixed bottom-20 right-4 z-toast flex max-w-sm items-start gap-3 bg-encre px-5 py-4 text-[0.95rem] text-papier shadow-[0_18px_40px_-20px_oklch(0.2_0.03_var(--teinte-ombre)/0.6)] lg:bottom-6 lg:right-6">
                <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center {{ $ton === 'danger' ? 'bg-danger' : 'bg-succes' }}">
                    <x-icone :nom="$ton === 'danger' ? 'alerte' : 'coche'" class="size-3.5 text-white" />
                </span>
                <p class="flex-1">{{ session($cle) }}</p>
                <button type="button" @click="visible = false" class="-m-1 p-1 text-white/70 hover:text-white">
                    <x-icone nom="fermer" class="size-4" /><span class="sr-only">Fermer le message</span>
                </button>
            </div>
        @endif
    @endforeach

    {{-- CONTENU --}}
    {{-- overflow-x-clip : les rangées qui débordent jusqu'au bord de l'écran (100vw inclut la barre de défilement) ne font pas glisser la page --}}
    <main id="contenu" class="flex-1 overflow-x-clip">
        @isset($header)
            <div class="border-b border-trait bg-carte">
                <div class="conteneur py-8">{{ $header }}</div>
            </div>
        @endisset
        @yield('content')
        {{ $slot ?? '' }}
    </main>

    {{-- PIED DE PAGE --}}
    <footer class="sans-impression relative isolate overflow-hidden bg-brun-fonce pb-16 text-white lg:pb-0">
        <div class="border-b border-white/15">
            <div class="conteneur grid gap-5 py-8 md:grid-cols-[1fr_auto] md:items-end lg:py-10">
                <div>
                    <p class="text-or-doux">Un métrage à calculer, une commande en gros, un conseil sur un tissu ?</p>
                    @if($lienWhatsapp)
                        <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="titre-display mt-1 inline-block text-[clamp(1.75rem,3.6vw,2.5rem)] text-or transition-colors hover:text-or-clair">Écrivez-nous sur WhatsApp</a>
                    @else
                        <a href="{{ route('faq') }}" class="titre-display mt-1 inline-block text-[clamp(1.75rem,3.6vw,2.5rem)] text-or transition-colors hover:text-or-clair">Nous sommes là pour vous aider</a>
                    @endif
                </div>
                <div class="flex flex-col gap-3 sm:flex-row">
                    @if($lienWhatsapp)
                        <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn btn-clair btn-lg"><x-icone nom="message" /> WhatsApp</a>
                    @endif
                    <a href="{{ route('catalogue') }}" class="btn btn-or btn-lg">Voir le catalogue</a>
                </div>
            </div>
        </div>

        @php
            $lienPied = 'inline-flex min-h-11 items-center text-white/80 transition-colors hover:text-or sm:min-h-0';
            $intertitre = 'mb-3 text-[0.8125rem] font-medium uppercase tracking-[0.08em] text-or-doux';
        @endphp
        <div class="conteneur grid grid-cols-2 gap-x-6 gap-y-8 py-10 md:grid-cols-4 lg:gap-12 lg:py-12">
            <div>
                <p class="{{ $intertitre }}">La boutique</p>
                <ul class="space-y-0 sm:space-y-2">
                    <li><a href="{{ route('catalogue') }}" class="{{ $lienPied }}">Tout le catalogue</a></li>
                    <li><a href="{{ route('pro.accueil') }}" class="{{ $lienPied }}">Espace Pro, vente en gros</a></li>
                    <li><a href="{{ route('about') }}" class="{{ $lienPied }}">Notre histoire</a></li>
                    <li><a href="{{ route('faq') }}" class="{{ $lienPied }}">Questions fréquentes</a></li>
                </ul>
            </div>
            <div>
                <p class="{{ $intertitre }}">Tissus</p>
                <ul class="space-y-0 sm:space-y-2">
                    @foreach($globalCategories->take(5) as $cat)
                        <li><a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}" class="{{ $lienPied }}">{{ $cat->nom }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="{{ $intertitre }}">Mon compte</p>
                <ul class="space-y-0 sm:space-y-2">
                    @auth
                        <li><a href="{{ route('commandes.index') }}" class="{{ $lienPied }}">Mes commandes</a></li>
                        <li><a href="{{ route('wishlist') }}" class="{{ $lienPied }}">Mes favoris</a></li>
                        <li><a href="{{ route('panier') }}" class="{{ $lienPied }}">Mon panier</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="{{ $lienPied }}">Se connecter</a></li>
                        <li><a href="{{ route('register') }}" class="{{ $lienPied }}">Créer un compte</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <p class="{{ $intertitre }}">Informations</p>
                <ul class="space-y-0 sm:space-y-2">
                    <li><a href="{{ route('cgu') }}" class="{{ $lienPied }}">Conditions générales</a></li>
                    <li><a href="{{ route('privacy') }}" class="{{ $lienPied }}">Confidentialité</a></li>
                    @if($lienWhatsapp)
                        <li><a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="{{ $lienPied }}">Assistance WhatsApp</a></li>
                    @endif
                </ul>
            </div>
        </div>

        {{-- Le nom revient en pleine largeur pour clore la page --}}
        <div class="conteneur overflow-hidden">
            <p aria-hidden="true" class="titre-display select-none text-[clamp(4.5rem,19vw,15rem)] leading-[0.8] tracking-[-0.04em] text-[color:color-mix(in_oklch,var(--or)_30%,var(--brun-fonce))]">{{ boutique('nom') }}</p>
        </div>
        <div class="border-t border-white/15">
            <div class="conteneur flex flex-col gap-2 py-6 text-sm text-white/70 sm:flex-row sm:justify-between">
                <span>© {{ date('Y') }} {{ boutique('nom') }}. Tous droits réservés.</span>
                <span>{{ boutique('mot_fin') }}</span>
            </div>
        </div>
    </footer>

    {{-- BARRE D'ACCÈS RAPIDE (téléphone) --}}
    <nav aria-label="Accès rapides" class="sans-impression fixed inset-x-0 bottom-0 z-sticky border-t border-trait bg-papier pb-[env(safe-area-inset-bottom)] lg:hidden">
        <ul class="mx-auto grid max-w-md grid-cols-3">
            @php
                $rapides = [
                    ['Catalogue', route('catalogue'), 'boutique', request()->routeIs('catalogue', 'produit.show')],
                    ['Panier' . ($nbPanier > 0 ? ' (' . $nbPanier . ')' : ''), route('panier'), 'panier', request()->routeIs('panier', 'commande', 'paiement')],
                    auth()->check()
                        ? ['Commandes', route('commandes.index'), 'colis', request()->routeIs('commandes.*')]
                        : ['Connexion', route('login'), 'compte', request()->routeIs('login')],
                ];
            @endphp
            @foreach($rapides as [$titre, $url, $icone, $actif])
                <li>
                    <a href="{{ $url }}" @if($actif) aria-current="page" @endif class="flex h-16 flex-col items-center justify-center gap-1 text-[0.8rem] font-medium {{ $actif ? 'text-brun-fonce' : 'text-encre-2' }}">
                        <x-icone :nom="$icone" class="size-[1.35rem]" />
                        {{ $titre }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</div>

    @include('components.cookie-consent')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
