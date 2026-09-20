<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AfriTex — Le meilleur tissu')</title>
    <meta name="description" content="@yield('meta_description', 'Découvrez la collection AfriTex : tissus africains de qualité supérieure, livraison rapide en Côte d\'Ivoire.')">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#C8963E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/cookie-manager.js') }}"></script>

    @if(config('analytics.google_analytics_id'))
        @include('components.google-analytics')
    @endif

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #FDF6EE; }
        h1, h2, h3, .font-serif { font-family: 'Fraunces', serif; }
        .product-card { transition: all 0.3s ease; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 8px 25px rgba(0,0,0,0.12) !important; }
        [x-cloak] { display: none !important; }

        /* Navigation Animation */
        nav.main-nav {
            transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
        }
        nav.nav-hidden {
            transform: translateY(-100%);
        }
    </style>
</head>
<body x-data="{ mobileMenuOpen: false, searchOpen: false, openAide: false }">

    <!-- BARRE PROMO PREMIUM -->
    <div class="bg-afri-gold text-white text-center py-2 text-xs font-medium tracking-widest uppercase">
        Livraison gratuite pour toute commande supérieure à 50 000 FCFA !
        <a href="{{ route('catalogue') }}" class="text-white font-bold ml-2 underline hover:text-afri-dark transition-colors">Commander maintenant</a>
    </div>

    <!-- NAVBAR PRINCIPALE PREMIUM -->
    <nav id="main-nav" class="main-nav bg-afri-dark text-afri-cream sticky top-0 z-50 shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">

                <!-- BURGER MENU TRIGGER -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-afri-cream p-2 focus:outline-none mr-2 hover:text-afri-gold transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileMenuOpen, 'block': !mobileMenuOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'block': mobileMenuOpen, 'hidden': !mobileMenuOpen}" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>

                <!-- LOGO LUXE -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('accueil') }}" class="text-3xl font-serif font-bold whitespace-nowrap tracking-tight">
                        Afri<span class="text-afri-gold">Tex</span>.
                    </a>
                </div>

                <!-- BARRE DE RECHERCHE (Desktop) -->
                <div class="hidden md:flex flex-1 justify-center px-8">
                    <form method="GET" action="{{ route('catalogue') }}" class="flex w-full max-w-lg">
                        <input type="text" name="recherche" placeholder="Recherchez un tissu, une catégorie..." value="{{ request('recherche') }}"
                            class="w-full px-6 py-2 rounded-l-full text-sm outline-none text-gray-800 bg-white/10 border border-white/10 text-white placeholder:text-white/40 focus:bg-white focus:text-gray-800 transition-all">
                        <button type="submit" class="bg-afri-gold text-white px-6 py-2 rounded-r-full text-sm font-bold hover:bg-opacity-90 transition uppercase tracking-widest">
                            Rechercher
                        </button>
                    </form>
                </div>

                <!-- LIENS & ACTIONS -->
                <div class="flex items-center gap-6">
                    <!-- Aide Dropdown (Desktop) -->
                    <div class="relative">
                        <button @click="openAide = !openAide" class="text-sm font-medium hover:text-afri-gold transition flex items-center gap-1 uppercase tracking-widest">
                            Aide
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="openAide" @click.outside="openAide = false" x-cloak class="absolute right-0 mt-4 w-48 bg-white text-gray-800 rounded-2xl shadow-2xl py-4 z-[60] border border-afri-cream">
                            <a href="{{ route('faq') }}" @click="openAide = false" class="block px-6 py-3 text-xs hover:bg-afri-beige transition">FAQ & Aide</a>
                            <a href="{{ route('privacy') }}" @click="openAide = false" class="block px-6 py-3 text-xs hover:bg-afri-beige transition">Confidentialité</a>
                            <a href="{{ route('cgu') }}" @click="openAide = false" class="block px-6 py-3 text-xs hover:bg-afri-beige transition">CGU</a>
                        </div>
                    </div>

                    <!-- Search Trigger (Mobile) -->
                    <button @click="searchOpen = !searchOpen" class="text-afri-cream p-2 focus:outline-none transition hover:text-afri-gold">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    @auth
                        <a href="{{ route('panier') }}" class="text-sm relative hover:text-afri-gold transition uppercase tracking-widest font-medium">
                             Panier
                            @php $panierCount = count(session()->get('panier', [])); @endphp
                            @if($panierCount > 0)
                                <span class="absolute -top-2 -right-3 bg-afri-gold text-white text-[10px] w-4 h-4 rounded-full flex items-center justify-center">{{ $panierCount }}</span>
                            @endif
                        </a>
                        @if(Auth::user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="text-sm text-afri-gold hover:underline uppercase tracking-widest font-bold">⚙️ Admin</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-white/10 border border-white/20 text-white px-4 py-2 rounded-full text-xs hover:bg-white/20 transition uppercase tracking-widest">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:flex items-center gap-2 text-sm hover:text-afri-gold transition uppercase tracking-widest font-medium">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2a5 5 0 110 10 5 5 0 010-10zm0 10c-5.33 0-8 2.67-8 4v3h16v-3c0-1.33-2.67-4-8-4z"/></svg>
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="bg-afri-gold text-white px-6 py-2 rounded-full text-xs font-bold hover:bg-opacity-90 transition uppercase tracking-widest shadow-lg shadow-afri-gold/20">S'inscrire</a>
                    @endauth
                </div>
            </div>
        </div>

        <!-- SEARCH BAR (Mobile Overlay) -->
        <div x-show="searchOpen" x-cloak class="md:hidden bg-afri-dark border-t border-white/10 p-4">
            <form method="GET" action="{{ route('catalogue') }}" class="flex w-full">
                <input type="text" name="recherche" placeholder="Recherchez..." value="{{ request('recherche') }}"
                    class="w-full px-4 py-2 rounded-l-lg text-sm text-gray-800 outline-none">
                <button type="submit" class="bg-afri-gold text-white px-4 py-2 rounded-r-lg text-sm font-semibold">Chercher</button>
            </form>
        </div>
    </nav>

    <!-- SIDE MENU (Burger Drawer) -->
    <div x-show="mobileMenuOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed top-0 left-0 w-80 h-full bg-white shadow-2xl z-[100] transition-transform transform"
         x-cloak>
        <div class="flex flex-col h-full">
            <div class="bg-afri-dark p-6 text-afri-cream flex justify-between items-center">
                <span class="text-xl font-serif font-bold">Menu</span>
                <button @click="mobileMenuOpen = false" class="text-white text-2xl">&times;</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <div class="mb-8">
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Catégories de tissus</h3>
                    <div class="space-y-3">
                        @foreach($globalCategories as $cat)
                            <a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}" class="flex items-center gap-3 text-gray-700 hover:text-afri-brown transition font-medium py-2 border-b border-gray-50">
                                @if(!empty($cat->image) && file_exists(public_path($cat->image)))
                                    <img src="{{ asset($cat->image) }}" alt="{{ $cat->nom }}" class="w-8 h-8 object-cover rounded-md">
                                @else
                                    <span class="text-lg">🧵</span>
                                @endif
                                {{ $cat->nom }}
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="mb-8">
                    <h3 class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-4">Informations</h3>
                    <div class="space-y-3">
                        <a href="{{ route('faq') }}" class="block text-gray-700 hover:text-afri-brown transition py-2 border-b border-gray-50">FAQ & Aide</a>
                        <a href="{{ route('privacy') }}" class="block text-gray-700 hover:text-afri-brown transition py-2 border-b border-gray-50">Confidentialité</a>
                        <a href="{{ route('cgu') }}" class="block text-gray-700 hover:text-afri-brown transition py-2 border-b border-gray-50">CGU</a>
                        <a href="https://wa.me/YOUR_NUMBER" target="_blank" class="block text-gray-700 hover:text-afri-brown transition py-2 border-b border-gray-50">WhatsApp Support</a>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 border-t">
                @auth
                    <a href="{{ route('logout') }}" class="block w-full text-center bg-red-500 text-white py-2 rounded-lg font-bold">Déconnexion</a>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center bg-afri-dark text-white py-2 rounded-lg font-bold mb-2">Connexion</a>
                    <a href="{{ route('register') }}" class="block w-full text-center bg-afri-gold text-white py-2 rounded-lg font-bold">S'inscrire</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- BACKGROUND OVERLAY -->
    <div x-show="mobileMenuOpen"
         @click="mobileMenuOpen = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-[90]"
         style="display: none;"
         x-cloak></div>

    <!-- MESSAGES -->
    @if(session('success'))
    <div class="fixed top-20 right-5 bg-afri-dark text-white px-6 py-3 rounded-xl z-[110] shadow-lg" id="toast">
        ✅ {{ session('success') }}
    </div>
    <script>setTimeout(() => { const t = document.getElementById('toast'); if(t) t.style.display='none'; }, 3000);</script>
    @endif

    @if(session('error'))
    <div class="fixed top-20 right-5 bg-red-600 text-white px-6 py-3 rounded-xl z-[110] shadow-lg" id="toast-error">
        ❌ {{ session('error') }}
    </div>
    <script>setTimeout(() => { const t = document.getElementById('toast-error'); if(t) t.style.display='none'; }, 3000);</script>
    @endif

    <!-- CONTENU -->
    <main>
        @yield('content')
    </main>

    <!-- FOOTER PREMIUM (Synchronisé avec le design React) -->
    <footer class="bg-afri-dark text-white pt-20 pb-10 px-6 sm:px-10 lg:px-16 relative overflow-hidden">
        <!-- Background Accent -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-afri-gold/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/4"></div>

        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-16 mb-20 relative z-10">

            <!-- Column 1: Brand & Social -->
            <div class="flex flex-col space-y-6">
                <h3 class="font-serif text-3xl font-bold tracking-tight text-afri-cream">Afri<span class="text-afri-gold">Tex</span>.</h3>
                <p class="text-afri-cream/50 text-sm leading-relaxed max-w-xs">
                    L'excellence du textile africain. Nous sélectionnons les matières les plus nobles pour sublimer vos créations.
                </p>
                <div class="flex gap-4">
                    @foreach(['IG', 'TK', 'FB'] as $social)
                    <a href="#" class="w-8 h-8 rounded-full border border-afri-gold/30 flex items-center justify-center text-[10px] font-bold hover:bg-afri-gold hover:text-afri-dark transition-all duration-300">
                        {{ $social }}
                    </a>
                    @endforeach
                </div>
            </div>

            <!-- Column 2: Exploration -->
            <div class="space-y-6">
                <h4 class="text-xs uppercase tracking-[0.2em] font-bold text-afri-gold">Exploration</h4>
                <ul class="space-y-3 text-sm text-afri-cream/60">
                    <li><a href="{{ route('catalogue') }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-afri-gold">→</span> Tous les tissus</a></li>
                    <li><a href="{{ route('catalogue') }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-afri-gold">→</span> Vente en Gros</a></li>
                    <li><a href="{{ route('about') }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-afri-gold">→</span> Notre Histoire</a></li>
                    <li><a href="{{ route('faq') }}" class="hover:text-white transition-colors flex items-center gap-2"><span class="text-afri-gold">→</span> FAQ</a></li>
                </ul>
            </div>

            <!-- Column 3: Assistance & Legal -->
            <div class="space-y-6">
                <h4 class="text-xs uppercase tracking-[0.2em] font-bold text-afri-gold">Assistance</h4>
                <ul class="space-y-3 text-sm text-afri-cream/60">
                    <li><a href="{{ route('faq') }}" class="hover:text-white transition-colors">Centre d'aide</a></li>
                    <li><a href="{{ route('privacy') }}" class="hover:text-white transition-colors">Confidentialité</a></li>
                    <li><a href="{{ route('cgu') }}" class="hover:text-white transition-colors">Conditions Générales</a></li>
                    <li><a href="https://wa.me/YOUR_NUMBER" target="_blank" class="hover:text-white transition-colors flex items-center gap-2">WhatsApp Support</a></li>
                </ul>
            </div>

            <!-- Column 4: Private Circle -->
            <div class="space-y-6">
                <h4 class="text-xs uppercase tracking-[0.2em] font-bold text-afri-gold">Le Cercle Privé</h4>
                <p class="text-sm text-afri-cream/60 leading-relaxed">
                    Rejoignez notre cercle exclusif pour accéder aux nouvelles collections en avant-première.
                </p>
                <form class="flex gap-2">
                    <input type="email" placeholder="Votre email" class="bg-white/10 border border-afri-gold/20 rounded-full px-4 py-2 text-xs outline-none focus:border-afri-gold transition-all flex-1 text-white placeholder:text-afri-cream/30">
                    <button class="bg-afri-gold text-afri-dark px-4 py-2 rounded-full text-[10px] font-bold uppercase tracking-widest hover:bg-white transition-all">OK</button>
                </form>
            </div>
        </div>

        <div class="max-w-7xl mx-auto pt-10 border-t border-afri-gold/10 flex flex-col md:flex-row justify-between items-center gap-6 text-[10px] uppercase tracking-widest text-afri-//colors font-medium">
            <span>© 2026 AfriTex — Tous droits réservés</span>
            <span class="flex items-center gap-2">Fait avec <span class="text-afri-gold">❤️</span> en Côte d'Ivoire</span>
        </div>
    </footer>

    <!-- FLOATING WHATSAPP CONCIERGERIE -->
    <a
        href="https://wa.me/YOUR_NUMBER"
        target="_blank"
        rel="noopener noreferrer"
        class="fixed bottom-8 right-8 z-[100] group flex items-center gap-3 bg-white p-3 rounded-full shadow-2xl border border-afri-cream hover:scale-110 transition-all duration-500"
    >
        <div class="bg-green-500 w-12 h-12 rounded-full flex items-center justify-center text-white text-2xl shadow-inner">
            💬
        </div>
        <div class="pr-4 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-4 group-hover:translate-x-0">
            <span class="text-[10px] font-bold uppercase tracking-tighter text-afri-dark block leading-none">Conciergerie</span>
            <span class="text-[9px] text-afri-brown/60">Besoin d'un conseil ?</span>
        </div>
    </a>

    <script>
        // Navbar hide/show on scroll
        let lastScrollY = window.scrollY;
        const nav = document.getElementById('main-nav');

        window.addEventListener('scroll', () => {
            if (window.scrollY < 10) {
                nav.classList.remove('nav-hidden');
            } else if (window.scrollY > lastScrollY) {
                nav.classList.add('nav-hidden');
            } else {
                nav.classList.remove('nav-hidden');
            }
            lastScrollY = window.scrollY;
        }, { passive: true });
    </script>

    <!-- Cookie Consent Banner -->
    @include('components.cookie-consent')

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('SW registered!', reg))
                    .catch(err => console.log('SW registration failed: ', err));
            });
        }
    </script>
    </body>
</html>
