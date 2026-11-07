@props(['produit', 'grand' => false, 'favori' => null, 'titre' => 'h3'])
{{--
  Carte produit : photo 4/5 sans recadrage arbitraire, filet brun qui se dessine au survol,
  catégorie, nom, prix au mètre et au rouleau. Toute la carte est cliquable ; le cœur reste au-dessus.
--}}
@php
    $estFavori = $favori ?? (auth()->check() ? auth()->user()->wishlist()->where('produit_id', $produit->id)->exists() : false);
@endphp
<article {{ $attributes->merge(['class' => 'group filet relative flex flex-col bg-carte']) }}>
    <div class="relative overflow-hidden bg-papier-2 {{ $grand ? 'aspect-[4/5] lg:aspect-auto lg:min-h-[24rem] lg:flex-1' : 'aspect-[4/5]' }}">
        @if($produit->image && file_exists(public_path($produit->image)))
            <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}" loading="lazy" decoding="async" class="zoom-doux h-full w-full object-cover">
        @else
            <div aria-hidden="true" class="flex h-full w-full items-center justify-center text-brun-trait"><x-icone nom="image" class="size-12" /></div>
        @endif

        <div class="absolute right-3 top-3 z-10">
            @auth
                <form action="{{ route('wishlist.toggle', $produit->id) }}" method="POST">
                    @csrf
                    <button type="submit" aria-pressed="{{ $estFavori ? 'true' : 'false' }}"
                            class="flex size-10 items-center justify-center bg-carte transition-[color,transform] duration-150 hover:text-brun active:scale-90 {{ $estFavori ? 'text-brun' : 'text-encre-2' }}">
                        <x-icone nom="coeur" class="size-5 {{ $estFavori ? 'fill-current' : '' }}" />
                        <span class="sr-only">{{ $estFavori ? 'Retirer ' . $produit->nom . ' des favoris' : 'Ajouter ' . $produit->nom . ' aux favoris' }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex size-10 items-center justify-center bg-carte text-encre-2 transition-colors hover:text-brun">
                    <x-icone nom="coeur" class="size-5" />
                    <span class="sr-only">Se connecter pour ajouter {{ $produit->nom }} aux favoris</span>
                </a>
            @endauth
        </div>
    </div>

    <div class="flex flex-col p-5 {{ $grand ? 'lg:p-7' : 'flex-1' }}">
        @if($produit->categorie)
            <p class="text-[0.85rem] font-medium text-brun">{{ $produit->categorie->nom }}</p>
        @endif
        <{{ $titre }} class="titre-display mt-1 {{ $grand ? 'text-[1.35rem] lg:text-[1.6rem]' : 'text-[1.12rem]' }}">
            <a href="{{ route('produit.show', $produit->slug) }}" class="after:absolute after:inset-0 after:content-['']">{{ $produit->nom }}</a>
        </{{ $titre }}>

        <div class="mt-3 space-y-0.5 text-[0.95rem]">
            @if($produit->prix_metre)
                <p><span class="chiffres font-semibold">{{ number_format($produit->prix_metre, 0, ',', ' ') }} F</span> <span class="text-encre-2">le mètre</span></p>
            @endif
            @if($produit->prix_rouleau)
                <p><span class="chiffres font-semibold">{{ number_format($produit->prix_rouleau, 0, ',', ' ') }} F</span> <span class="text-encre-2">le rouleau</span></p>
            @endif
        </div>

        <span class="lien-fleche mt-auto pt-4 text-[0.92rem]">Voir le tissu <x-icone nom="fleche" /></span>
    </div>
</article>
