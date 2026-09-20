@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 md:py-16">
    <div class="flex items-center justify-between mb-10">
        <h1 class="text-3xl font-serif font-bold text-[#4A1B0C]">Ma Liste de Souhaits</h1>
        <span class="text-sm text-gray-500">{{ $wishlist->count() }} produit(s) sauvegardé(s)</span>
    </div>

    @if($wishlist->isEmpty())
        <div class="bg-white rounded-[24px] border border-[#e8ddd3] p-16 text-center shadow-sm">
            <div class="text-6xl mb-6">🤍</div>
            <h2 class="text-xl font-semibold text-[#4A1B0C] mb-2">Votre liste est vide</h2>
            <p class="text-gray-500 mb-8">Commencez à ajouter vos tissus préférés à vos favoris !</p>
            <a href="{{ route('catalogue') }}" class="inline-flex rounded-2xl bg-[#8B4513] px-8 py-3 text-white font-bold transition hover:bg-[#5d330f]">
                Explorer le catalogue
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($wishlist as $item)
                @php $produit = $item->produit; @endphp
                <div class="product-card overflow-hidden rounded-[24px] border border-[#e8ddd3] bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg relative group">
                    <!-- Bouton Supprimer -->
                    <form action="{{ route('wishlist.remove', $item->id) }}" method="POST" class="absolute top-4 right-4 z-10">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 bg-white/80 backdrop-blur-sm rounded-full text-gray-400 hover:text-red-500 transition shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 22H7.862a2 2 0 01-1.995-1.858L5 10V5a2 2 0 012-2h8a2 2 0 012 2v5zM5 10h14M12 10V5"/></svg>
                        </button>
                    </form>

                    <div class="h-56 bg-gradient-to-br from-[#F5E6D3] to-[#EDD5B8] flex items-center justify-center overflow-hidden">
                        @if($produit->image)
                            <img src="{{ asset($produit->image) }}" class="h-full w-full object-cover" alt="{{ $produit->nom }}">
                        @else
                            <span class="text-sm text-gray-400">Image non disponible</span>
                        @endif
                    </div>

                    <div class="p-5">
                        <div class="text-[10px] uppercase tracking-[0.2em] text-[#C8963E] font-semibold mb-2">{{ $produit->categorie->nom }}</div>
                        <div class="text-base font-semibold text-[#4A1B0C] mb-3 line-clamp-2">{{ $produit->nom }}</div>
                        <div class="mb-4 text-sm font-semibold text-[#8B4513]">
                            @if($produit->prix_metre) 📏 {{ number_format($produit->prix_metre, 0, ',', ' ') }} F/mètre @endif
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('produit.show', $produit->slug) }}" class="flex-1 text-center rounded-xl border border-[#e8ddd3] py-2 text-xs font-medium text-gray-600 hover:bg-gray-50 transition">
                                Voir
                            </a>
                            <form action="{{ route('panier.ajouter') }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                                <input type="hidden" name="type_commande" value="metre">
                                <input type="hidden" name="quantite" value="1">
                                <button type="submit" class="w-full rounded-xl bg-[#8B4513] py-2 text-xs font-bold text-white hover:bg-[#5d330f] transition">
                                    Ajouter
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection