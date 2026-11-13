@props(['produit', 'grand' => false, 'favori' => null, 'titre' => 'h3', 'compact' => false])
{{--
  Carte produit : photo 4/5 sans recadrage arbitraire, filet brun qui se dessine au survol,
  catégorie, nom, prix au mètre et au rouleau. Toute la carte est cliquable ; le cœur reste au-dessus.
  « compact » : grille de 2 colonnes au téléphone (textes et marges réduits, sans « Voir le tissu ») ; normale dès sm.
--}}
@php
    $estFavori = $favori ?? (auth()->check() ? auth()->user()->wishlist()->where('produit_id', $produit->id)->exists() : false);
    // Même message que sur la page du tissu
    $numeroWhatsapp = config('services.whatsapp.numero');
    $lienWhatsapp = $numeroWhatsapp
        ? 'https://wa.me/' . $numeroWhatsapp . '?text=' . rawurlencode('Bonjour ' . boutique('nom') . ",\n\nJe suis intéressé(e) par ce tissu :\n\n*" . $produit->nom . "*\n\n" . route('produit.show', $produit->slug) . "\n\nEst-il disponible ?")
        : null;
@endphp
<article {{ $attributes->merge(['class' => 'group filet relative flex flex-col bg-carte']) }}>
    <div class="relative overflow-hidden bg-papier-2 {{ $grand ? 'aspect-[4/5] lg:aspect-auto lg:min-h-[24rem] lg:flex-1' : ($compact ? 'aspect-square sm:aspect-[4/5]' : 'aspect-[4/5]') }}">
        @if($produit->image && file_exists(public_path($produit->image)))
            <img src="{{ asset($produit->image) }}" alt="{{ $produit->nom }}" loading="lazy" decoding="async" class="zoom-doux h-full w-full object-cover">
        @else
            <div aria-hidden="true" class="flex h-full w-full items-center justify-center text-brun-trait"><x-icone nom="image" class="size-12" /></div>
        @endif

        <div class="absolute z-10 {{ $compact ? 'right-2 top-2 sm:right-3 sm:top-3' : 'right-3 top-3' }}">
            @auth
                <form action="{{ route('wishlist.toggle', $produit->id) }}" method="POST">
                    @csrf
                    <button type="submit" aria-pressed="{{ $estFavori ? 'true' : 'false' }}"
                            class="flex {{ $compact ? 'size-9 sm:size-10' : 'size-10' }} items-center justify-center bg-carte transition-[color,transform] duration-150 hover:text-brun active:scale-90 {{ $estFavori ? 'text-brun' : 'text-encre-2' }}">
                        <x-icone nom="coeur" class="size-5 {{ $estFavori ? 'fill-current' : '' }}" />
                        <span class="sr-only">{{ $estFavori ? 'Retirer ' . $produit->nom . ' des favoris' : 'Ajouter ' . $produit->nom . ' aux favoris' }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="flex {{ $compact ? 'size-9 sm:size-10' : 'size-10' }} items-center justify-center bg-carte text-encre-2 transition-colors hover:text-brun">
                    <x-icone nom="coeur" class="size-5" />
                    <span class="sr-only">Se connecter pour ajouter {{ $produit->nom }} aux favoris</span>
                </a>
            @endauth
        </div>
    </div>

    <div class="flex flex-col {{ $compact ? 'p-3 sm:p-5' : 'p-5' }} {{ $grand ? 'lg:p-7' : 'flex-1' }}">
        {{-- Famille à gauche, WhatsApp à droite (au-dessus du lien qui couvre la carte) --}}
        <div class="flex items-center justify-between gap-2">
            <p class="min-w-0 {{ $compact ? 'truncate text-[0.78rem] sm:text-[0.85rem]' : 'text-[0.85rem]' }} font-medium text-brun">{{ $produit->categorie?->nom }}</p>
            @if($lienWhatsapp)
                <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" title="Discuter sur WhatsApp"
                   class="relative z-10 -my-2 -mr-2 flex size-10 shrink-0 items-center justify-center transition-transform duration-150 active:scale-90">
                    <x-icone nom="whatsapp" class="{{ $compact ? 'size-6 sm:size-7' : 'size-7' }}" />
                    <span class="sr-only">Discuter de {{ $produit->nom }} sur WhatsApp</span>
                </a>
            @endif
        </div>
        <{{ $titre }} class="titre-display mt-1 {{ $grand ? 'text-[1.35rem] lg:text-[1.6rem]' : ($compact ? 'text-[0.98rem] leading-snug sm:text-[1.12rem]' : 'text-[1.12rem]') }}">
            <a href="{{ route('produit.show', $produit->slug) }}" class="after:absolute after:inset-0 after:content-['']">{{ $produit->nom }}</a>
        </{{ $titre }}>

        <div class="{{ $compact ? 'mt-2 text-[0.85rem] sm:mt-3 sm:text-[0.95rem]' : 'mt-3 text-[0.95rem]' }} space-y-0.5">
            @if($produit->prix_metre)
                <p><span class="chiffres font-semibold">{{ number_format($produit->prix_metre, 0, ',', ' ') }} F</span> <span class="text-encre-2">le mètre</span></p>
            @endif
            @if($produit->prix_rouleau)
                <p><span class="chiffres font-semibold">{{ number_format($produit->prix_rouleau, 0, ',', ' ') }} F</span> <span class="text-encre-2">le rouleau</span></p>
            @endif
        </div>

        <span class="lien-fleche mt-auto pt-4 text-[0.92rem] {{ $compact ? 'hidden sm:inline-flex' : '' }}">Voir le tissu <x-icone nom="fleche" /></span>
    </div>
</article>
