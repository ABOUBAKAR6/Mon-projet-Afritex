@extends('layouts.app')
@section('title', 'Page non trouvée — AfriTex')
@section('content')

<div class="bg-afri-beige min-h-screen flex items-center justify-center px-6 relative overflow-hidden">
    <div class="absolute top-0 left-0 w-full h-full pointer-events-none">
        <div class="absolute top-1/4 left-1/4 w-64 h-64 bg-afri-gold/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-afri-cream rounded-full blur-3xl"></div>
    </div>

    <div class="text-center z-10 max-w-2xl">
        <h1 class="text-[15rem] md:text-[20rem] font-serif font-bold text-afri-dark leading-none mb-0">404</h1>
        <div class="text-center -mt-20 md:-mt-40">
            <h2 class="text-3xl md:text-5xl font-serif font-bold text-afri-brown mb-6">Oups ! Cette pièce est introuvable.</h2>
            <p class="text-afri-dark/60 text-lg mb-12 max-w-md mx-auto leading-relaxed">
                Il semble que le fil se soit emmêlé. La page que vous recherchez n'existe plus ou a été déplacée.
            </p>
            <a href="{{ route('accueil') }}" class="bg-afri-dark text-white px-10 py-4 rounded-full font-bold uppercase tracking-widest text-xs transition-all hover:bg-afri-brown shadow-xl active:scale-95">
                Retourner au catalogue
            </a>
        </div>
    </div>
</div>

<style>
    .text-afri-dark { color: #4A1B0C; }
    .text-afri-brown { color: #8B4513; }
    .bg-afri-beige { background-color: #FDF6EE; }
    .bg-afri-gold { background-color: #C8963E; }
    .bg-afri-cream { background-color: #F5E6D3; }
</style>
@endsection