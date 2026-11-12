{{-- Étapes de la commande : trois segments, l'étape en cours en brun --}}
@php
    $etapes = ['Livraison', 'Paiement', 'Confirmation'];
@endphp
<nav aria-label="Étapes de la commande" class="apparition">
    <p class="text-[0.88rem] text-encre-2 sm:text-[0.95rem]">Étape <span class="chiffres">{{ $etape }}</span> sur <span class="chiffres">{{ count($etapes) }}</span></p>
    <ol class="mt-1.5 grid grid-cols-3 gap-2 sm:mt-3">
        @foreach($etapes as $i => $nom)
            @php $numero = $i + 1; @endphp
            <li @if($numero === $etape) aria-current="step" @endif>
                <span class="block h-1 {{ $numero <= $etape ? 'bg-brun' : 'bg-trait' }}"></span>
                <span class="mt-1 flex items-center gap-1.5 text-[0.85rem] sm:mt-2 sm:text-[0.92rem] {{ $numero === $etape ? 'font-medium text-encre' : ($numero < $etape ? 'text-brun' : 'text-encre-2') }}">
                    @if($numero < $etape)<x-icone nom="coche" class="size-4" />@endif
                    {{ $nom }}
                </span>
            </li>
        @endforeach
    </ol>
</nav>
