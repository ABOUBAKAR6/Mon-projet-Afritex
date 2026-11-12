{{-- En-tête HTML commun : boutique, admin et pages de connexion --}}
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $titre ?? boutique('nom').', '.boutique('slogan_long'))</title>
<meta name="description" content="@yield('meta_description', boutique('nom').' : '.boutique('description'))">
@php $logoSite = \App\Models\Parametre::valeur('logo'); @endphp
@if($logoSite && is_file(public_path($logoSite)))
    <link rel="icon" href="{{ asset($logoSite) }}">
@endif
{{--
  Application installée (PWA), icônes générées aux couleurs de la boutique (IconeAppController).
  Deux applis distinctes : la boutique (démarre sur l'accueil) et, depuis /admin ou /espace-client,
  l'appli de gestion (démarre sur /espace-client, icône aux couleurs inversées).
--}}
@php $appliGestion = request()->is('admin', 'admin/*', 'espace-client', 'espace-client/*'); @endphp
<link rel="apple-touch-icon" href="{{ icone_app($appliGestion ? 'admin-apple-180' : 'apple-180') }}">
<script>window.BOUTIQUE = { slug: '{{ boutique_slug() }}' };</script>
<link rel="manifest" href="{{ $appliGestion ? '/manifest-gestion.webmanifest' : '/manifest.webmanifest' }}">
<meta name="theme-color" content="{{ couleur('foncee') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="{{ $appliGestion ? 'Admin '.boutique('nom') : boutique('nom') }}">
@if($appliGestion)
    {{-- Le service worker (public/sw.js) ne garde aucune page de gestion : il sert seulement la page hors ligne --}}
    <script>if ('serviceWorker' in navigator) window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));</script>
@endif
{{-- Barre d'état pleine : avec « black-translucent », l'en-tête passait sous l'heure de l'iPhone --}}
<meta name="apple-mobile-web-app-status-bar-style" content="black">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Poppins:wght@600&display=swap" rel="stylesheet">

{{-- Les animations au défilement ne masquent le contenu que si le script tourne --}}
<script>document.documentElement.classList.add('js')</script>

{{-- Pas de zoom de l'écran, comme dans une appli (demande de la boutique). La balise viewport suffit sur Android ;
     l'iPhone l'ignore : on y bloque le pincement (gesturestart) et le double toucher (touch-action). --}}
<style>html { touch-action: pan-x pan-y; }</style>
<script>['gesturestart', 'gesturechange', 'gestureend'].forEach((n) => document.addEventListener(n, (e) => e.preventDefault(), { passive: false }));</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])

{{-- Couleurs de la boutique réglées dans le .env (rien n'est écrit s'il n'y en a aucune) --}}
@if($couleursBoutique = boutique_couleurs_css())
    <style>html:root{ {!! $couleursBoutique !!} }</style>
@endif
