@extends('layouts.app')
@section('title', $produit->nom . ' — AfriTex')
@section('meta_description', 'Achetez ' . $produit->nom . ' au meilleur prix chez AfriTex. Tissu de qualité supérieure, livraison rapide partout en Côte d\'Ivoire.')
@section('content')

<div class="bg-afri-beige min-h-screen py-20 px-6 sm:px-10 lg:px-16 relative overflow-hidden">
    <!-- Background Accents -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-afri-gold/10 rounded-full blur-3xl pointer-events-none -translate-y-1/4 translate-x-1/4"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-afri-cream rounded-full blur-3xl pointer-events-none translate-y-1/4 -translate-x-1/4"></div>

    <div class="max-w-7xl mx-auto relative z-10">

        <!-- MINIMAL BREADCRUMBS -->
        <nav class="flex text-[10px] uppercase tracking-[0.2em] text-afri-brown/50 mb-16 overflow-x-auto whitespace-nowrap pb-2">
            <a href="{{ route('accueil') }}" class="hover:text-afri-dark transition-colors">Accueil</a>
            <span class="mx-3 opacity-30">/</span>
            <a href="{{ route('catalogue') }}" class="hover:text-afri-dark transition-colors">Catalogue</a>
            <span class="mx-3 opacity-30">/</span>
            <a href="{{ route('catalogue', ['categorie_id' => $produit->categorie?->id]) }}" class="hover:text-afri-dark transition-colors">{{ $produit->categorie?->nom ?? 'Catégorie' }}</a>
            <span class="mx-3 opacity-30">/</span>
            <span class="text-afri-dark font-bold">{{ $produit->nom }}</span>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-start">

            <!-- PRODUCT GALLERY (Curated Experience) -->
            <div class="lg:col-span-7 space-y-8" x-data="{
                images: [
                    '{{ $produit->image ? asset($produit->image) : '' }}',
                    @foreach($produit->images as $img) '{{ asset($img->chemin) }}', @endforeach
                ],
                activeIndex: 0,
                get mainImage() {
                    return this.images[this.activeIndex] || 'https://via.placeholder.com/800?text=Tissu';
                }
            }">
                <!-- Main Stage -->
                <div class="relative aspect-square bg-white rounded-[60px] overflow-hidden shadow-2xl group cursor-zoom-in border border-afri-cream">
                    <img :src="mainImage"
                         class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-110"
                         @mousemove="
                            const { offsetX, offsetY, target } = $event;
                            const { width, height } = target;
                            const x = (offsetX / width) * 100;
                            const y = (offsetY / height) * 100;
                            target.style.transformOrigin = `${x}% ${y}%`;
                            target.style.transform = 'scale(1.8)';
                         "
                         @mouseleave="$el.style.transform = 'scale(1)'"
                         alt="{{ $produit->nom }}">

                    <!-- Floating Stock Badge -->
                    <div class="absolute top-6 left-6 z-10 bg-white/80 backdrop-blur-md px-4 py-2 rounded-full text-[10px] font-bold text-afri-dark uppercase tracking-widest shadow-sm border border-afri-cream">
                        Disponibilité : {{ $produit->stock }} unités
                    </div>
                </div>

                <!-- Thumbnails Strip -->
                <div class="flex gap-4 overflow-x-auto pb-4">
                    <div @click="activeIndex = 0"
                         class="w-24 h-24 shrink-0 rounded-3xl cursor-pointer overflow-hidden transition-all duration-500 border-2"
                         :class="activeIndex === 0 ? 'border-afri-dark scale-105 shadow-lg' : 'border-transparent opacity-60 hover:opacity-100'">
                        @if($produit->image)
                            <img src="{{ asset($produit->image) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-afri-cream text-xs text-afri-brown">Pas d'image</div>
                        @endif
                    </div>
                    @if($produit->images && $produit->images->count() > 0)
                        @foreach($produit->images as $index => $img)
                        <div @click="activeIndex = {{ $index + 1 }}"
                             class="w-24 h-24 shrink-0 rounded-3xl cursor-pointer overflow-hidden transition-all duration-500 border-2"
                             :class="activeIndex === {{ $index + 1 }} ? 'border-afri-dark scale-105 shadow-lg' : 'border-transparent opacity-60 hover:opacity-100'">
                            <img src="{{ asset($img->chemin) }}" class="w-full h-full object-cover">
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- PRODUCT DETAILS (Editorial Column) -->
            <div class="lg:col-span-5 flex flex-col">
                <div class="mb-12">
                    <span class="text-afri-gold text-xs uppercase tracking-[0.3em] font-bold mb-4 block">{{ $produit->categorie?->nom ?? 'Catégorie' }}</span>
                    <h1 class="text-4xl md:text-6xl font-serif font-bold text-afri-dark mb-6 leading-tight">{{ $produit->nom }}</h1>

                    <!-- Ratings -->
                    <div class="flex items-center gap-4 mb-10">
                        <div class="flex text-afri-gold">
                            @php
                                $reviewCount = $produit->reviews?->count() ?? 0;
                                $avg = $reviewCount > 0 ? round($produit->averageRating(), 1) : 0;
                                $stars = floor($avg);
                            @endphp
                            @for($i=0; $i<5; $i++)
                                <span class="{{ $i < $stars ? 'text-afri-gold' : 'text-afri-cream' }} text-xl">★</span>
                            @endfor
                        </div>
                        <span class="text-sm text-afri-brown/60 font-medium italic">
                            @if($reviewCount > 0)
                                {{ $reviewCount }} avis de nos clients
                            @else
                                Soyez le premier à donner votre avis
                            @endif
                        </span>
                    </div>
                </div>

                <!-- PRICE SELECTOR (Luxury Options) -->
                <div class="mb-12" x-data="{
                    selectedType: '{{ $produit->prix_metre ? 'metre' : ($produit->prix_rouleau ? 'rouleau' : 'pantalon') }}',
                    qty: 1,
                    get currentPrice() {
                        const prices = {
                            metre: {{ $produit->prix_metre ?? 0 }},
                            rouleau: {{ $produit->prix_rouleau ?? 0 }},
                            pantalon: {{ $produit->prix_pantalon ?? 0 }},
                            chemise: {{ $produit->prix_chemise ?? 0 }},
                            tunique: {{ $produit->prix_tunique ?? 0 }},
                        };
                        return (prices[this.selectedType] || 0).toLocaleString('fr-FR') + ' F';
                    }
                }">
                    <label class="block text-[11px] font-bold text-afri-dark/40 uppercase tracking-widest mb-6">Choisir le format d'acquisition</label>
                    <div class="grid grid-cols-2 gap-4 mb-10">
                        @if($produit->prix_metre)
                        <div @click="selectedType = 'metre'"
                             class="cursor-pointer p-6 rounded-3xl border-2 transition-all duration-500 group"
                             :class="selectedType === 'metre' ? 'border-afri-dark bg-white shadow-xl' : 'border-afri-cream bg-white/50 hover:border-afri-gold/30'">
                            <div class="text-[10px] uppercase font-bold mb-1 transition-colors" :class="selectedType === 'metre' ? 'text-afri-gold' : 'text-afri-brown/50'">Au mètre</div>
                            <div class="text-xl font-serif font-bold transition-colors" :class="selectedType === 'metre' ? 'text-afri-dark' : 'text-afri-brown/80'">{{ number_format($produit->prix_metre, 0, ',', ' ') }} F</div>
                        </div>
                        @endif

                        @if($produit->prix_rouleau)
                        <div @click="selectedType = 'rouleau'"
                             class="cursor-pointer p-6 rounded-3xl border-2 transition-all duration-500 group"
                             :class="selectedType === 'rouleau' ? 'border-afri-dark bg-white shadow-xl' : 'border-afri-cream bg-white/50 hover:border-afri-gold/30'">
                            <div class="text-[10px] uppercase font-bold mb-1 transition-colors" :class="selectedType === 'rouleau' ? 'text-afri-gold' : 'text-afri-brown/50'">Au rouleau</div>
                            <div class="text-xl font-serif font-bold transition-colors" :class="selectedType === 'rouleau' ? 'text-afri-dark' : 'text-afri-brown/80'">{{ number_format($produit->prix_rouleau, 0, ',', ' ') }} F</div>
                        </div>
                        @endif
                    </div>

                    <!-- ACTION SECTION -->
                    <div class="flex flex-col gap-6">
                        @auth
                        <form action="{{ route('panier.ajouter') }}" method="POST" class="flex flex-col gap-6">
                            @csrf
                            <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                            <input type="hidden" name="type_commande" :value="selectedType">

                            <div class="flex gap-6 items-center">
                                <div class="flex items-center border-2 border-afri-cream rounded-full bg-white px-2 py-1">
                                    <button type="button" @click="qty = Math.max(1, qty - 1)" class="w-10 h-10 flex items-center justify-center hover:text-afri-gold transition text-xl font-light">-</button>
                                    <input type="number" name="quantite" x-model="qty" class="w-12 text-center font-bold outline-none bg-transparent" value="1">
                                    <button type="button" @click="qty++" class="w-10 h-10 flex items-center justify-center hover:text-afri-gold transition text-xl font-light">+</button>
                                </div>
                                <button type="submit" class="flex-1 bg-afri-dark text-white py-5 rounded-full font-bold uppercase tracking-widest text-xs transition-all hover:bg-afri-brown hover:shadow-2xl active:scale-95">
                                    Ajouter à la collection
                                </button>
                            </div>
                        </form>
                        @else
                        <a href="{{ route('login') }}" class="block w-full bg-afri-dark text-white text-center py-5 rounded-full font-bold uppercase tracking-widest text-xs transition-all hover:bg-afri-brown shadow-lg">
                            S'identifier pour commander
                        </a>
                        @endauth

                        <a href="{{ route('commande') }}" class="flex items-center justify-center w-full border-2 border-afri-dark text-afri-dark py-5 rounded-full font-bold uppercase tracking-widest text-xs transition-all hover:bg-afri-dark hover:text-white active:scale-95 text-center">
                            Commande Express
                        </a>
                    </div>
                </div>

                <!-- TECHNICAL SPEC SHEET -->
                <div class="mt-16 border-t border-afri-cream pt-12">
                    <h3 class="text-xs font-bold text-afri-dark uppercase tracking-[0.3em] mb-8">Spécifications Techniques</h3>
                    <div class="grid grid-cols-2 gap-x-12 gap-y-8">
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold tracking-widest">Composition</span>
                            <span class="text-sm text-afri-dark font-medium">{{ $produit->composition ?? '100% Coton Premium' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold tracking-widest">Origine</span>
                            <span class="text-sm text-afri-dark font-medium">{{ $produit->origine ?? 'Côte d\'Ivoire' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold tracking-widest">Entretien</span>
                            <span class="text-sm text-afri-dark font-medium">{{ $produit->entretien ?? 'Lavage à la main recommandé' }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold tracking-widest">Poids / Grammage</span>
                            <span class="text-sm text-afri-dark font-medium">{{ $produit->poids ?? 'Standard' }}</span>
                        </div>
                    </div>

                    @if($produit->description)
                    <div class="mt-12 p-8 bg-white rounded-[30px] border border-afri-cream shadow-sm">
                        <h4 class="text-xs font-bold text-afri-gold uppercase mb-3 tracking-widest">Note du Designer</h4>
                        <p class="text-sm text-afri-brown/70 leading-relaxed italic">"{{ $produit->description }}"</p>
                    </div>
                    @endif
                </div>

                <!-- SHIPPING INFO -->
                <div id="shipping-info" class="mt-12 pt-12 border-t border-afri-cream">
                    <h4 class="text-xs font-bold text-afri-dark uppercase tracking-[0.3em] mb-6">Logistique & Services</h4>
                    <div class="space-y-0">
                        <div class="py-5 border-b border-afri-cream/50 flex flex-col group transition-all duration-300 hover:bg-white px-4 rounded-xl shipping-row" style="opacity:0; transform:translateY(20px)">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold transition-colors group-hover:text-afri-gold">Zone de livraison</span>
                            <span class="text-sm text-afri-dark font-medium">Partout en Côte d'Ivoire — Délai : 24h à 72h</span>
                        </div>
                        <div class="py-5 border-b border-afri-cream/50 flex flex-col group transition-all duration-300 hover:bg-white px-4 rounded-xl shipping-row" style="opacity:0; transform:translateY(20px)">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold transition-colors group-hover:text-afri-gold">Offre Livraison</span>
                            <span class="text-sm text-afri-dark font-medium">Gratuite pour toute commande supérieure à 50 000 FCFA</span>
                        </div>
                        <div class="py-5 border-b border-afri-cream/50 flex flex-col group transition-all duration-300 hover:bg-white px-4 rounded-xl shipping-row" style="opacity:0; transform:translateY(20px)">
                            <span class="text-[10px] text-afri-brown/40 uppercase font-bold transition-colors group-hover:text-afri-gold">Garantie & Retours</span>
                            <span class="text-sm text-afri-dark font-medium">Qualité garantie — Retours acceptés sous 7 jours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- REVIEWS SECTION -->
        <div id="reviews" class="mt-32 grid grid-cols-1 lg:grid-cols-3 gap-16 border-t border-afri-cream pt-20">
            <div class="lg:col-span-1">
                <h3 class="text-3xl font-serif font-bold text-afri-dark mb-8">Le Livre d'Or</h3>
                @auth
                    <form action="{{ route('produit.review.store', $produit->id) }}" method="POST" class="bg-white p-8 rounded-[40px] border border-afri-cream shadow-sm space-y-6">
                        @csrf
                        <div class="flex flex-col gap-2">
                            <label class="block text-[10px] font-bold text-afri-dark/40 uppercase tracking-widest">Note globale</label>
                            <select name="rating" class="w-full p-3 bg-afri-beige/30 rounded-2xl text-sm outline-none focus:ring-2 ring-afri-gold/20">
                                <option value="5">5 ★★★★★ (Exceptionnel)</option>
                                <option value="4">4 ★★★★☆ (Très bien)</option>
                                <option value="3">3 ★★★☆☆ (Correct)</option>
                                <option value="2">2 ★★☆☆☆ (Décevant)</option>
                                <option value="1">1 ★☆☆☆☆ (Médiocre)</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label class="block text-[10px] font-bold text-afri-dark/40 uppercase tracking-widest">Votre expérience</label>
                            <textarea name="comment" rows="4" class="w-full p-4 bg-afri-beige/30 rounded-2xl text-sm outline-none focus:ring-2 ring-afri-gold/20" placeholder="Partagez vos impressions..."></textarea>
                        </div>
                        <button type="submit" class="w-full bg-afri-dark text-white py-4 rounded-full font-bold text-xs uppercase tracking-widest transition-all hover:bg-afri-brown active:scale-95">
                            Publier mon avis
                        </button>
                    </form>
                @else
                    <div class="bg-afri-beige p-8 rounded-[40px] border border-afri-cream text-center">
                        <p class="text-sm text-afri-brown/60 mb-6 italic">Connectez-vous pour partager votre avis sur cette pièce.</p>
                        <a href="{{ route('login') }}" class="inline-block text-afri-dark font-bold text-xs uppercase tracking-widest border-b border-afri-dark pb-1 hover:text-afri-brown transition-colors">S'identifier</a>
                    </div>
                @endauth
            </div>

            <div class="lg:col-span-2">
                <h3 class="text-3xl font-serif font-bold text-afri-dark mb-8">Témoignages</h3>
                <div class="space-y-6">
                    @forelse($produit->reviews as $review)
                        <div class="bg-white p-8 rounded-[40px] border border-afri-cream shadow-sm transition-all duration-300 hover:shadow-xl hover:shadow-afri-dark/5">
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-afri-gold/20 flex items-center justify-center text-afri-gold font-bold">
                                        {{ substr($review->user?->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <span class="font-bold text-sm text-afri-dark">{{ $review->user?->name ?? 'Client Anonyme' }}</span>
                                        <span class="text-[10px] text-afri-brown/40 uppercase tracking-tighter">{{ $review->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                <div class="text-afri-gold text-sm font-bold">
                                    @for($i=0; $i<5; $i++)
                                        <span class="{{ $i < $review->rating ? 'text-afri-gold' : 'text-afri-cream' }}">★</span>
                                    @endfor
                                </div>
                            </div>
                            <p class="text-sm text-afri-brown/70 leading-relaxed italic">"{{ $review->comment }}"</p>
                        </div>
                    @empty
                    <div class="text-center py-20 bg-white rounded-[40px] border border-dashed border-afri-cream">
                        <div class="text-4xl mb-4 opacity-20">✨</div>
                        <p class="text-sm text-afri-brown/40 italic">Aucun témoignage pour le moment. Soyez le premier à décrire cette pièce.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- SIMILAR PIECES -->
        <div class="mt-32">
            <div class="flex justify-between items-end mb-12">
                <h2 class="text-3xl font-serif font-bold text-afri-dark leading-tight">Pièces <span class="italic text-afri-brown">Complémentaires</span></h2>
                <a href="{{ route('catalogue') }}" class="text-afri-brown text-xs font-bold uppercase tracking-widest border-b border-afri-brown pb-1 hover:text-afri-dark transition-colors">Tout explorer →</a>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                @foreach($categories as $cat)
                    @foreach($cat->produits?->take(1) ?? [] as $sim)
                        @if($sim->id !== $produit->id)
                        <a href="{{ route('produit.show', $sim->slug) }}"
                           class="group relative bg-white rounded-3xl overflow-hidden border border-afri-cream transition-all duration-500 hover:-translate-y-2 hover:shadow-2xl hover:shadow-afri-dark/10">
                            <div class="h-40 bg-afri-cream overflow-hidden">
                                @if($sim->image)
                                    <img src="{{ asset($sim->image) }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl opacity-20">🧵</div>
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="text-[9px] text-afri-gold font-bold uppercase mb-1 tracking-widest">{{ $sim->categorie?->nom ?? 'Catégorie' }}</div>
                                <div class="text-xs font-bold text-afri-dark truncate mb-2">{{ $sim->nom }}</div>
                                <div class="text-xs font-bold text-afri-brown">
                                    @if($sim->prix_metre) {{ number_format($sim->prix_metre, 0, ',', ' ') }} F/m @endif
                                </div>
                            </div>
                        </a>
                        @endif
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const shippingSection = document.getElementById('shipping-info');
    const rows = document.querySelectorAll('.shipping-row');

    if (!shippingSection || rows.length === 0) return;

    if (reducedMotion) {
        rows.forEach(row => {
            row.style.opacity = '1';
            row.style.transform = 'translateY(0)';
        });
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                rows.forEach((row, index) => {
                    setTimeout(() => {
                        row.style.opacity = '1';
                        row.style.transform = 'translateY(0)';
                    }, index * 80);
                });
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    observer.observe(shippingSection);
});
</script>

<style>
    .transition-all { transition-property: all; }
</style>
@endsection