{{-- Version texte : sortie brute ({!! !!}), sinon Blade transformerait une apostrophe ou > en entités HTML --}}
ESPACE PRO
{!! $sujet !!}

Bonjour {!! $nom !!},

@foreach ($lignes as $ligne)
{!! $ligne !!}
@endforeach

{!! $bouton !!} : {!! $lien !!}

@if ($pourEquipe)
Message automatique de l'Espace Pro {!! boutique('nom') !!}.
@else
À bientôt,
L'équipe {!! boutique('nom') !!}
@endif

{!! boutique('nom') !!} · {!! boutique('slogan') !!} · Côte d'Ivoire
