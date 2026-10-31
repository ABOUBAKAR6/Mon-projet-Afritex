{{-- Frise de suivi d'une commande : étapes franchies en brun, étape en cours soulignée --}}
@php
    $etapes = [
        'en_attente' => 'Reçue',
        'confirmee' => 'Confirmée',
        'en_preparation' => 'En préparation',
        'expedie' => 'Expédiée',
        'livree' => 'Livrée',
    ];
    $cles = array_keys($etapes);
    $position = array_search($statut, $cles, true);
@endphp

@if($statut === 'annulee')
    <p class="mt-8 flex items-center gap-3 bg-danger-doux p-5 text-danger">
        <x-icone nom="alerte" class="size-5 shrink-0" /> Cette commande a été annulée. Pour toute question, contactez-nous.
    </p>
@else
    <ol class="revele mt-8 grid grid-cols-5 gap-1.5 bg-carte p-5 sm:gap-2 sm:p-6" aria-label="Avancement de la commande">
        @foreach($etapes as $cle => $libelle)
            @php $franchie = $position !== false && $loop->index <= $position; $courante = $loop->index === $position; @endphp
            <li @if($courante) aria-current="step" @endif>
                <span class="block h-1 {{ $franchie ? 'bg-brun' : 'bg-trait' }}"></span>
                <span class="mt-2.5 flex flex-col gap-1 text-[0.82rem] sm:flex-row sm:items-center sm:gap-1.5 sm:text-[0.92rem] {{ $courante ? 'font-medium text-encre' : ($franchie ? 'text-brun' : 'text-encre-2') }}">
                    @if($franchie && !$courante)<x-icone nom="coche" class="size-4 shrink-0" />@endif
                    {{ $libelle }}
                </span>
            </li>
        @endforeach
    </ol>
@endif
