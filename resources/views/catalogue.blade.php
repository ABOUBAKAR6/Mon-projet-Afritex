@extends('layouts.app')
@section('content')

<div class="bg-afri-beige min-h-screen py-24 px-6 sm:px-10 lg:px-16 relative overflow-hidden">
    <!-- Background Accents -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-afri-gold/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2 translate-x-1/4"></div>
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-afri-cream rounded-full blur-3xl pointer-events-none translate-y-1/2 -translate-x-1/4"></div>

    <div class="max-w-7xl mx-auto relative z-10">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-end mb-16 gap-6">
            <div class="max-w-2xl">
                <span class="text-afri-gold text-xs uppercase tracking-[0.3em] font-bold mb-4 block">La Collection</span>
                <h2 class="text-5xl md:text-7xl font-serif font-bold text-afri-dark leading-tight">
                    Catalogue <span class="italic text-afri-brown">des Tissus</span>
                </h2>
            </div>
            <div class="text-afri-brown/60 text-sm italic font-serif">
                Une sélection rigoureuse de textiles d'exception
            </div>
        </div>

        <!-- Editorial Filter Bar -->
        <div class="mb-20">
            <form method="GET" action="{{ route('catalogue') }}" class="bg-white/50 backdrop-blur-md p-8 rounded-[40px] border border-afri-cream shadow-sm grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-10 items-end transition-all duration-500 hover:shadow-xl hover:shadow-afri-dark/5">

                <div class="flex flex-col gap-3">
                    <span class="text-[10px] text-afri-dark/40 uppercase tracking-widest font-bold">Essence</span>
                    <select name="categorie_id" class="w-full bg-transparent py-2 text-sm text-afri-dark outline-none border-b border-afri-cream focus:border-afri-brown transition-colors cursor-pointer">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('categorie_id') == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-3">
                    <span class="text-[10px] text-afri-dark/40 uppercase tracking-widest font-bold">Recherche</span>
                    <input type="text" name="recherche" placeholder="Un motif, une couleur..." value="{{ request('recherche') }}" class="w-full bg-transparent py-2 text-sm outline-none border-b border-afri-cream focus:border-afri-brown transition-colors">
                </div>

                <div class="flex flex-col gap-3">
                    <span class="text-[10px] text-afri-dark/40 uppercase tracking-widest font-bold">Budget (F/m)</span>
                    <div class="flex gap-4">
                        <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}" class="w-1/2 bg-transparent py-2 text-sm outline-none border-b border-afri-cream focus:border-afri-brown transition-colors">
                        <input type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}" class="w-1/2 bg-transparent py-2 text-sm outline-none border-b border-afri-cream focus:border-afri-brown transition-colors">
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="submit" class="flex-1 bg-afri-dark text-white px-6 py-3 rounded-2xl text-xs font-bold uppercase tracking-widest transition-all duration-300 hover:bg-afri-brown hover:-translate-y-1 active:scale-95">
                        Filtrer
                    </button>
                    <a href="{{ route('catalogue') }}" class="flex-1 bg-afri-beige text-afri-brown px-6 py-3 rounded-2xl text-center text-xs font-bold uppercase tracking-widest transition-all duration-300 hover:bg-afri-cream border border-afri-cream">
                        Reset
                    </a>
                </div>

                <div class="col-span-1 md:col-span-3 lg:col-span-4 pt-6 border-t border-afri-cream/50">
                    <div class="flex flex-wrap items-center gap-4">
                        <span class="text-[10px] text-afri-dark/40 uppercase tracking-widest font-bold mr-4">Nuances :</span>
                        @forelse($allColors as $color)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="couleurs[]" value="{{ $color }}" {{ in_array($color, request('couleurs', [])) ? 'checked' : '' }} class="hidden peer">
                                <span class="text-xs px-4 py-1.5 rounded-full border border-afri-cream bg-white text-afri-dark transition-all duration-300 peer-checked:bg-afri-dark peer-checked:text-white peer-checked:border-afri-dark group-hover:border-afri-brown">
                                    {{ $color }}
                                </span>
                            </label>
                        @empty
                            <span class="text-xs text-afri-dark/30 italic">Aucune nuance spécifiée</span>
                        @endforelse
                    </div>
                </div>
            </form>
        </div>

        <!-- Asymmetric Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16">
            @forelse($produits as $produit)
            @php
                $isFeatured = ($loop->index % 5 === 0);
                $gridClass = $isFeatured ? 'lg:col-span-2' : 'col-span-1';
                $imgHeight = $isFeatured ? 'h-96' : 'h-72';
            @endphp
            <div class="{{ $gridClass }} group relative">
                <div class="relative overflow-hidden rounded-[40px] bg-white transition-all duration-700 hover:shadow-2xl hover:shadow-afri-dark/10">

                    <!-- Image Container -->
                    <div class="relative {{ $imgHeight }} overflow-hidden bg-afri-cream">
                        @if($produit->image)
                            <img src="{{ asset($produit->image) }}" class="w-full h-full object-cover transition-transform duration-1000 group-hover:scale-110" alt="{{ $produit->nom }}">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-6xl">🧵</div>
                        @endif

                        <!-- Floating Badge -->
                        <div class="absolute top-6 left-6">
                            <span class="bg-white/80 backdrop-blur-md text-afri-dark text-[10px] font-bold uppercase px-4 py-1.5 rounded-full tracking-widest shadow-sm">
                                {{ $produit->categorie->nom }}
                            </span>
                        </div>

                        <!-- Wishlist Button -->
                        <div class="absolute top-6 right-6 z-10">
                            @auth
                                <form action="{{ route('wishlist.toggle', $produit->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="p-3 rounded-full bg-white/80 backdrop-blur-sm text-gray-400 hover:text-red-500 transition shadow-sm active:scale-90">
                                        <svg class="w-5 h-5" fill="{{ Auth::user()->wishlist()->where('produit_id', $produit->id)->exists() ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54l-1.45 1.32z" />
                                        </svg>
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="p-3 rounded-full bg-white/80 backdrop-blur-sm text-gray-400 hover:text-red-500 transition shadow-sm">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54l-1.45 1.32z" />
                                    </svg>
                                </a>
                            @endauth
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-8 bg-white">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-xl md:text-2xl font-serif font-bold text-afri-dark group-hover:text-afri-brown transition-colors leading-tight">
                                {{ $produit->nom }}
                            </h3>
                            <div class="text-afri-gold font-bold text-lg">
                                @if($produit->prix_metre) {{ number_format($produit->prix_metre, 0, ',', ' ') }} F <span class="text-[10px] uppercase opacity-60">/m</span> @endif
                            </div>
                        </div>

                        <!-- Premium Tags -->
                        <div class="flex flex-wrap gap-2 mb-8">
                            @if($produit->prix_metre)<span class="rounded-full border border-afri-cream bg-afri-beige px-3 py-1 text-[10px] text-afri-brown uppercase tracking-wider">Au mètre</span>@endif
                            @if($produit->prix_rouleau)<span class="rounded-full border border-afri-cream bg-afri-beige px-3 py-1 text-[10px] text-afri-brown uppercase tracking-wider">Au rouleau</span>@endif
                            @if($produit->prix_pantalon)<span class="rounded-full border border-afri-cream bg-afri-beige px-3 py-1 text-[10px] text-afri-brown uppercase tracking-wider">Pantalon</span>@endif
                            @if($produit->prix_chemise)<span class="rounded-full border border-afri-cream bg-afri-beige px-3 py-1 text-[10px] text-afri-brown uppercase tracking-wider">Chemise</span>@endif
                            @if($produit->prix_tunique)<span class="rounded-full border border-afri-cream bg-afri-beige px-3 py-1 text-[10px] text-afri-brown uppercase tracking-wider">Tunique</span>@endif
                        </div>

                        <a href="{{ route('produit.show', $produit->slug) }}" class="inline-flex w-full justify-center rounded-2xl bg-afri-dark text-white py-4 text-xs font-bold uppercase tracking-[0.2em] transition-all duration-300 hover:bg-afri-brown hover:shadow-lg active:scale-95">
                            Explorer la pièce
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full rounded-[40px] border border-afri-cream bg-white p-20 text-center">
                <div class="text-6xl mb-6 opacity-20">🧵</div>
                <p class="text-xl font-serif italic text-afri-brown mb-6">Notre collection est actuellement en cours de renouvellement...</p>
                <a href="{{ route('catalogue') }}" class="text-afri-dark font-bold text-sm uppercase tracking-widest border-b border-afri-dark pb-1 hover:text-afri-brown transition-colors">Réinitialiser les filtres</a>
            </div>
            @endforelse
        </div>
    </div>
</div>

<style>
    .product-card { transition: all 0.5s cubic-bezier(0.22, 1, 0.36, 1); }
</style>

@endsection