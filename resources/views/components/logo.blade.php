@props(['taille' => 'size-10'])
{{--
  Logo de la boutique : l'image envoyée depuis « Mon compte » de l'administration,
  sinon le carré à l'initiale de l'enseigne. « taille » fixe les dimensions, « class » habille le carré.
--}}
@php $logo = \App\Models\Parametre::valeur('logo'); @endphp
@if($logo && is_file(public_path($logo)))
    <img src="{{ asset($logo) }}" alt="" aria-hidden="true" class="{{ $taille }} shrink-0 object-contain">
@else
    <span aria-hidden="true" {{ $attributes->class(['titre-display flex shrink-0 items-center justify-center', $taille]) }}>{{ boutique_initiale() }}</span>
@endif
