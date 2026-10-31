@props(['utilisateur', 'taille' => 'size-10'])
{{-- Photo de profil, ou les initiales du nom quand il n'y en a pas --}}
@php
    $initiales = collect(preg_split('/\s+/', trim($utilisateur->name)))->filter()->take(2)
        ->map(fn ($mot) => mb_strtoupper(mb_substr($mot, 0, 1)))->implode('');
@endphp
@if($utilisateur->photo && is_file(public_path($utilisateur->photo)))
    <img src="{{ asset($utilisateur->photo) }}" alt="" {{ $attributes->class([$taille, 'shrink-0 rounded-full object-cover']) }}>
@else
    <span aria-hidden="true" {{ $attributes->class([$taille, 'flex shrink-0 items-center justify-center rounded-full bg-or font-semibold text-brun-fonce']) }}>{{ $initiales ?: '?' }}</span>
@endif
