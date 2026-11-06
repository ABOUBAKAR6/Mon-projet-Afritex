@extends('layouts.app')
@section('title', 'Page introuvable · ' . boutique('nom'))
@section('content')

<section class="relative isolate overflow-hidden bg-brun-fonce text-white">
    <img src="{{ asset('images/hero-bg.avif') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover opacity-25">
    <div class="conteneur grid min-h-[32rem] items-center gap-10 py-16 lg:min-h-[36rem] lg:grid-cols-[auto_1fr] lg:gap-20">
        <p aria-hidden="true" class="titre-display apparition chiffres text-[clamp(7rem,22vw,15rem)] leading-[0.8] tracking-[-0.04em] text-or">404</p>
        <div class="apparition max-w-[34rem]" style="animation-delay: 100ms">
            <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Ce tissu n'est plus en rayon</h1>
            <p class="mt-4 text-white/85 lg:text-[1.1rem]">La page que vous cherchez a été déplacée ou n'existe plus. Le catalogue, lui, est bien là.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('catalogue') }}" class="btn btn-or btn-lg">Voir le catalogue <x-icone nom="fleche" /></a>
                <a href="{{ route('accueil') }}" class="btn btn-clair btn-lg">Retour à l'accueil</a>
            </div>
        </div>
    </div>
</section>

@endsection
