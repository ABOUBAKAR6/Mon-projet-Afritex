@props(['titre', 'description' => null])
{{-- En-tête de page de l'administration : titre, phrase d'aide, actions à droite --}}
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="titre-display text-[1.9rem] sm:text-[2.2rem]">{{ $titre }}</h1>
        @if($description)
            <p class="mt-1 text-encre-2">{{ $description }}</p>
        @endif
    </div>
    @if(trim($slot))
        <div class="flex flex-wrap gap-2">{{ $slot }}</div>
    @endif
</div>
