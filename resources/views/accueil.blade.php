@extends('layouts.app')
@section('content')

<div id="hero-container" class="relative h-screen w-full overflow-hidden bg-[#4A1B0C]" style="background-image: url('{{ asset('images/hero-bg.avif') }}'); background-size: cover; background-position: center;">
    <!-- Entrance Grid Layer -->
    <div id="entrance-grid" class="absolute inset-0 z-50 pointer-events-none" style="display: none;"></div>

    <!-- Parallax Background Layer - FIXED VISIBILITY -->
    <div id="parallax-bg" class="absolute top-0 left-0 w-full h-[120%] pointer-events-none z-10 transition-opacity duration-1000"
         style="opacity: 1; background-image: url('{{ asset('images/hero-bg.avif') }}'); background-size: cover; background-position: center; will-change: transform;">
    </div>

    <!-- Gradient Overlay -->
    <div class="absolute inset-0 z-20 bg-gradient-to-b from-[#4A1B0C]/60 via-transparent to-[#4A1B0C]/80 pointer-events-none"></div>

    <!-- Hero Content -->
    <div class="relative z-30 h-full flex flex-col items-center justify-center text-center px-6 text-white">
        <h1 id="hero-title" class="font-serif text-5xl md:text-7xl mb-4 leading-tight transition-all duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]" style="opacity: 1; transform: translateY(0);">
            Le meilleur <span class="text-afri-gold">tissu</span>,<br/>livré chez vous
        </h1>
        <p id="hero-desc" class="text-lg md:text-xl opacity-80 max-w-2xl mx-auto transition-all duration-700 delay-300 ease-[cubic-bezier(0.22,1,0.36,1)]" style="opacity: 1; transform: translateY(0);">
            Découvrez notre collection de tissus africains de qualité<br/>
            Vente en gros et en détail
        </p>
        <div id="hero-btns" class="mt-10 flex flex-col sm:flex-row gap-4 justify-center items-center transition-all duration-700 delay-500 ease-[cubic-bezier(0.22,1,0.36,1)]" style="opacity: 1; transform: translateY(0);">
            <a href="{{ route('catalogue') }}" class="bg-white text-afri-brown px-8 py-3 rounded-full font-bold text-sm transition-all hover:-translate-y-1 hover:shadow-2xl active:scale-95">
                Voir le catalogue
            </a>
            @guest
            <a href="{{ route('register') }}" class="border-2 border-white/60 text-white px-8 py-3 rounded-full text-sm transition-all hover:bg-white/10 active:scale-95">
                S'inscrire gratuitement
            </a>
            @endguest
        </div>
    </div>
</div>

<!-- REST OF THE PAGE (Trust Bar, Categories, etc.) -->
<div class="bg-afri-dark py-3 px-6 flex flex-wrap justify-center gap-4 sm:gap-6 text-white/80 text-xs md:text-sm">
    @foreach(['✓ Livraison rapide', '✓ Gros & Détail', '✓ Qualité garantie', '✓ Paiement sécurisé'] as $item)
    <span class="whitespace-nowrap">{{ $item }}</span>
    @endforeach
</div>

<div class="animate-section px-6 py-16 bg-afri-beige">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h2 class="font-serif text-3xl md:text-4xl text-afri-dark">Nos catégories</h2>
            <a href="{{ route('catalogue') }}" class="text-afri-brown text-sm border-b border-afri-brown hover:text-afri-dark transition">Voir tout →</a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-4">
            @foreach($categories as $cat)
            <a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}"
               class="group bg-white border border-gray-200 rounded-xl p-4 text-center text-afri-dark transition-all duration-300 hover:-translate-y-2 hover:border-afri-brown hover:shadow-lg">
                <div class="text-3xl mb-2 group-hover:scale-110 transition-transform">
                    @if($cat->image)
                        <img src="{{ asset($cat->image) }}" class="w-full h-full object-cover rounded-full aspect-square">
                    @else
                        🧵
                    @endif
                </div>
                <div class="text-[11px] font-medium uppercase tracking-wider truncate">{{ $cat->nom }}</div>
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="animate-section px-6 py-16 bg-white">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-8">
            <h2 class="font-serif text-3xl md:text-4xl text-afri-dark">Produits populaires</h2>
            <a href="{{ route('catalogue') }}" class="text-afri-brown text-sm border-b border-afri-brown hover:text-afri-dark transition">Voir tout →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @forelse($produits as $produit)
            <div class="product-card border border-gray-200 rounded-2xl overflow-hidden bg-afri-beige group transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl">
                <div class="h-48 bg-gradient-to-br from-afri-cream to-[#EDD5B8] flex items-center justify-center text-6xl overflow-hidden">
                    @if($produit->image)
                        <img src="{{ asset($produit->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    @else
                        🧵
                    @endif
                </div>
                <div class="p-4">
                    <div class="text-[11px] text-afri-gold font-bold uppercase mb-1 tracking-widest">{{ $produit->categorie->nom }}</div>
                    <div class="text-sm font-bold mb-2 text-afri-dark truncate">{{ $produit->nom }}</div>
                    <div class="text-xs text-afri-brown font-medium mb-4">
                        @if($produit->prix_metre) 📏 {{ number_format($produit->prix_metre, 0, ',', ' ') }} F/mètre @endif
                    </div>
                    <a href="{{ route('produit.show', $produit->slug) }}" class="block w-full bg-afri-brown text-white py-2 rounded-lg text-center text-xs font-medium transition hover:bg-afri-dark">
                        Voir le produit →
                    </a>
                </div>
            </div>
            @empty
            <p class="text-gray-400 text-center col-span-full py-10">Aucun produit pour le moment.</p>
            @endforelse
        </div>
    </div>
</div>

<div class="animate-section px-6 py-16 bg-afri-beige">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="group bg-white border border-gray-200 rounded-3xl p-8 relative overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl">
            <span class="bg-afri-brown text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Vente en Gros</span>
            <h3 class="font-serif text-2xl mt-4 mb-2 text-afri-dark">Commandes en gros</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">Profitez de tarifs avantageux pour les achats en grande quantité.</p>
            <a href="{{ route('catalogue') }}" class="inline-block bg-afri-brown text-white px-6 py-2 rounded-full text-xs font-bold transition hover:bg-afri-dark">Commander en gros</a>
        </div>
        <div class="group bg-white border border-gray-200 rounded-3xl p-8 relative overflow-hidden transition-all hover:-translate-y-1 hover:shadow-xl">
            <span class="bg-afri-brown text-white text-[10px] px-3 py-1 rounded-full font-bold uppercase">Vente au détail</span>
            <h3 class="font-serif text-2xl mt-4 mb-2 text-afri-dark">Commandes au détail</h3>
            <p class="text-gray-500 text-sm leading-relaxed mb-6">Achetez exactement ce dont vous avez besoin, au mètre ou à la pièce.</p>
            <a href="{{ route('catalogue') }}" class="inline-block bg-afri-brown text-white px-6 py-2 rounded-full text-xs font-bold transition hover:bg-afri-dark">Commander au détail</a>
        </div>
    </div>
</div>

<script>

document.addEventListener('DOMContentLoaded', () => {

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const grid = document.getElementById('entrance-grid');
    const bg = document.getElementById('parallax-bg');
    const title = document.getElementById('hero-title');
    const desc = document.getElementById('hero-desc');
    const btns = document.getElementById('hero-btns');

    if (!bg) {
        return;
    }

    const hasSeenEntrance = sessionStorage.getItem('afritex_entrance_done');

    if (hasSeenEntrance || reducedMotion) {
        bg.style.opacity = '1';
        title.style.opacity = '1'; title.style.transform = 'translateY(0)';
        desc.style.opacity = '1'; desc.style.transform = 'translateY(0)';
        btns.style.opacity = '1'; btns.style.transform = 'translateY(0)';
    } else {
        const rows = 6, cols = 10;
        grid.style.display = 'grid';
        grid.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        grid.style.gridTemplateRows = `repeat(${rows}, 1fr)`;

        for (let i = 0; i < rows * cols; i++) {
            const tile = document.createElement('div');
            const r = Math.floor(i / cols);
            const c = i % cols;
            const rx = (Math.random() - 0.5) * 1000;
            const ry = (Math.random() - 0.5) * 1000;
            const rot = (Math.random() - 0.5) * 90;

            Object.assign(tile.style, {
                backgroundImage: `url('{{ asset('images/hero-bg.avif') }}')`,
                backgroundSize: 'cover',
                backgroundPosition: `${(c / (cols-1)) * 100}% ${(r / (rows-1)) * 100}%`,
                transform: `translate3d(${rx}px, ${ry}px, 0) rotate(${rot}deg)`,
                opacity: '0',
                transition: 'transform 0.7s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.7s ease',
                willChange: 'transform, opacity'
            });
            grid.appendChild(tile);

            setTimeout(() => {
                tile.style.transform = 'translate3d(0,0,0) rotate(0deg)';
                tile.style.opacity = '1';
            }, (i * 20) + 100);
        }

        setTimeout(() => {
            bg.style.opacity = '1';
            title.style.opacity = '1'; title.style.transform = 'translateY(0)';
            desc.style.opacity = '1'; desc.style.transform = 'translateY(0)';
            btns.style.opacity = '1'; btns.style.transform = 'translateY(0)';
            sessionStorage.setItem('afritex_entrance_done', 'true');
            setTimeout(() => grid.style.display = 'none', 1000);
        }, 2500);
    }

    if (!reducedMotion) {
        let requestID;
        const handleScroll = () => {
            const scrollY = window.scrollY;
            bg.style.transform = `translate3d(0, ${scrollY * 0.3}px, 0)`;
            requestID = requestAnimationFrame(handleScroll);
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        requestID = requestAnimationFrame(handleScroll);
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) entry.target.classList.add('visible');
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-section').forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(40px)';
        el.style.transition = 'all 0.7s cubic-bezier(0.22, 1, 0.36, 1)';
        observer.observe(el);
    });
});
</script>

<style>
.animate-section.visible {
    opacity: 1 !important;
    transform: translateY(0) !important;
}
</style>

@endsection
