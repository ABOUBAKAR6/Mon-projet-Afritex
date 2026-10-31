@props(['intitule' => null, 'titre', 'clair' => false, 'niveau' => 'h2'])
{{-- Intitulé de section + titre (comme TitreBloc d'Église). « clair » pour les fonds sombres. --}}
<div {{ $attributes }}>
    @if($intitule)
        <p class="intitule {{ $clair ? '!text-or' : '' }}">{{ $intitule }}</p>
    @endif
    <{{ $niveau }} class="titre-display mt-2 text-[1.9rem] sm:text-[2.3rem] lg:text-[2.6rem] {{ $clair ? 'text-white' : '' }}">{{ $titre }}</{{ $niveau }}>
</div>
