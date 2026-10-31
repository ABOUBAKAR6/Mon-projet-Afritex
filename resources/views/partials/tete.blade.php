{{-- En-tête HTML commun : boutique, admin et pages de connexion --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $titre ?? 'AfriTex, tissus africains au mètre et au rouleau')</title>
<meta name="description" content="@yield('meta_description', 'AfriTex : tissus africains de qualité au mètre, au rouleau ou en confection. Vente en gros et au détail, livraison partout en Côte d\'Ivoire.')">
@php $logoSite = \App\Models\Parametre::valeur('logo'); @endphp
@if($logoSite && is_file(public_path($logoSite)))
    <link rel="icon" href="{{ asset($logoSite) }}">
    <link rel="apple-touch-icon" href="{{ asset($logoSite) }}">
@endif
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#4A1B0C">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Poppins:wght@600&display=swap" rel="stylesheet">

{{-- Les animations au défilement ne masquent le contenu que si le script tourne --}}
<script>document.documentElement.classList.add('js')</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
