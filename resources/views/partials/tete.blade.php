{{-- En-tête HTML commun : boutique, admin et pages de connexion --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $titre ?? boutique('nom').', '.boutique('slogan_long'))</title>
<meta name="description" content="@yield('meta_description', boutique('nom').' : '.boutique('description'))">
@php $logoSite = \App\Models\Parametre::valeur('logo'); @endphp
@if($logoSite && is_file(public_path($logoSite)))
    <link rel="icon" href="{{ asset($logoSite) }}">
    <link rel="apple-touch-icon" href="{{ asset($logoSite) }}">
@endif
<script>window.BOUTIQUE = { slug: '{{ boutique_slug() }}' };</script>
<link rel="manifest" href="/manifest.webmanifest">
<meta name="theme-color" content="{{ couleur('foncee') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Poppins:wght@600&display=swap" rel="stylesheet">

{{-- Les animations au défilement ne masquent le contenu que si le script tourne --}}
<script>document.documentElement.classList.add('js')</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Couleurs de la boutique réglées dans le .env (rien n'est écrit s'il n'y en a aucune) --}}
@if($couleursBoutique = boutique_couleurs_css())
    <style>html:root{ {!! $couleursBoutique !!} }</style>
@endif
