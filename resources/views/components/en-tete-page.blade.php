@props(['titre', 'chapeau' => null, 'ariane' => [], 'image' => 'images/hero-bg.avif', 'vignette' => null])
{{--
  Bandeau des pages intérieures (d'après EnTetePage d'Église) : photo de tissus assombrie,
  titre centré, fil d'Ariane, chapeau facultatif. Le contenu du slot se place dessous (ex. un bouton).
  Avec « vignette » (ex. la photo d'une catégorie) : la photo, souvent petite et en portrait, sert de fond
  flouté et s'affiche nette à droite ; le texte passe à gauche.
--}}
@php $vignette = $vignette && is_file(public_path($vignette)) ? $vignette : null; @endphp
<div class="relative isolate flex min-h-[16rem] items-center overflow-hidden bg-brun-fonce text-white sm:min-h-[19rem] lg:min-h-[22rem]">
    @if($vignette)
        {{-- Flou + léger agrandissement : masque la basse définition et évite le liseré clair sur les bords --}}
        <img src="{{ asset($vignette) }}" alt="" class="absolute inset-0 -z-10 h-full w-full scale-110 object-cover blur-2xl">
        <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[oklch(0.18_0.04_var(--teinte-ombre)/0.7)]"></div>
    @elseif($image && file_exists(public_path($image)))
        <img src="{{ asset($image) }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover">
        <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[oklch(0.18_0.04_var(--teinte-ombre)/0.72)]"></div>
    @endif
    <div @class([
        'conteneur w-full py-12 lg:py-16',
        'text-center' => !$vignette,
        'grid items-center gap-8 text-center md:grid-cols-[1fr_auto] md:gap-14 md:text-left' => $vignette,
    ])>
        <div>
            <h1 class="titre-display apparition text-[2.1rem] sm:text-5xl lg:text-[3.25rem]">{{ $titre }}</h1>
            <nav aria-label="Fil d'Ariane" @class(['apparition mt-4 flex flex-wrap items-center justify-center gap-1.5 text-[0.9rem] text-white/80', 'md:justify-start' => $vignette]) style="animation-delay: 80ms">
                <a href="{{ route('accueil') }}" class="hover:text-white hover:underline">Accueil</a>
                @foreach($ariane as [$libelle, $url])
                    <x-icone nom="chevron-droite" class="size-3.5" />
                    <a href="{{ $url }}" class="hover:text-white hover:underline">{{ $libelle }}</a>
                @endforeach
                <x-icone nom="chevron-droite" class="size-3.5" />
                <span aria-current="page" class="text-white">{{ $titre }}</span>
            </nav>
            @if($chapeau)
                <p @class(['apparition mx-auto mt-5 max-w-[58ch] text-white/85 lg:text-[1.1rem]', 'md:mx-0' => $vignette]) style="animation-delay: 160ms">{{ $chapeau }}</p>
            @endif
            {{ $slot }}
        </div>
        @if($vignette)
            <img src="{{ asset($vignette) }}" alt="{{ $titre }}" fetchpriority="high"
                 class="apparition mx-auto hidden aspect-[4/5] w-52 border-[6px] border-white/90 object-cover shadow-[0_24px_50px_-20px_oklch(0.1_0.03_var(--teinte-ombre)/0.8)] md:block lg:w-60"
                 style="animation-delay: 120ms">
        @endif
    </div>
</div>
