@extends('layouts.app')
@section('title', 'L\'Héritage AfriTex — Notre Histoire')
@section('content')

<div class="bg-afri-beige min-h-screen py-24 px-6 sm:px-10 lg:px-16 relative overflow-hidden">
    <!-- Background Blobs -->
    <div class="absolute top-0 left-0 w-[600px] h-[600px] bg-afri-gold/10 rounded-full blur-3xl pointer-events-none -translate-y-1/2 -translate-x-1/4"></div>
    <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-afri-cream rounded-full blur-3xl pointer-events-none translate-y-1/2 translate-x-1/4"></div>

    <div class="max-w-5xl mx-auto relative z-10">
        <!-- Hero Section -->
        <div class="text-center mb-32 reveal">
            <span class="text-afri-gold text-xs uppercase tracking-[0.3em] font-bold mb-4 block">Notre Histoire</span>
            <h1 class="text-5xl md:text-8xl font-serif font-bold text-afri-dark leading-tight mb-8">
                L'Art du <span class="italic text-afri-brown">Textile</span> <br>Africain
            </h1>
            <div class="h-1 w-24 bg-afri-gold mx-auto"></div>
        </div>

        <!-- Story Section 1 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-20 items-center mb-32">
            <div class="relative reveal">
                <div class="aspect-[4/5] rounded-[60px] overflow-hidden shadow-2xl rotate-3 hover:rotate-0 transition-transform duration-700">
                    <img src="https://images.unsplash.com/photo-1528459801613-067f51839a96?auto=format&fit=crop&q=80&w=1000" alt="Tissus Heritage" class="w-full h-full object-cover">
                </div>
                <div class="absolute -bottom-8 -right-8 w-48 h-48 bg-afri-gold/20 rounded-full blur-2xl"></div>
            </div>
            <div class="space-y-8 reveal" style="transition-delay: 0.2s;">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-afri-dark leading-tight">Une Passion pour la <br>Matière</h2>
                <p class="text-afri-brown/70 text-lg leading-relaxed">
                    AfriTex est né d'une volonté simple mais ambitieuse : sublimer la richesse des textiles africains en les proposant sous un angle contemporain et luxueux.
                </p>
                <p class="text-afri-brown/70 text-lg leading-relaxed">
                    Nous parcourons le continent pour sélectionner des étoffes dont la qualité, la finesse et le motif racontent une histoire. Chaque fil, chaque trame est choisi pour sa capacité à transformer un vêtement en une œuvre d'art.
                </p>
            </div>
        </div>

        <!-- Story Section 2 -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-20 items-center mb-32">
            <div class="space-y-8 order-2 md:order-1 reveal">
                <h2 class="text-3xl md:text-4xl font-serif font-bold text-afri-dark leading-tight">L'Excellence <br>du Détail</h2>
                <p class="text-afri-brown/70 text-lg leading-relaxed">
                    Pour nous, le luxe ne réside pas dans l'ostentatoire, mais dans la précision. De la sélection du coton glacé à la finesse de la soie japonaise, nous ne faisons aucun compromis sur la qualité.
                </p>
                <p class="text-afri-brown/70 text-lg leading-relaxed">
                    Notre mission est d'accompagner les créateurs, les couturiers et les passionnés dans la réalisation de pièces d'exception, en leur offrant un accès privilégié aux plus beaux tissus du moment.
                </p>
            </div>
            <div class="relative order-1 md:order-2 reveal" style="transition-delay: 0.2s;">
                <div class="aspect-[4/5] rounded-[60px] overflow-hidden shadow-2xl -rotate-3 hover:rotate-0 transition-transform duration-700">
                    <img src="https://images.unsplash.com/photo-1584184928617-e6772f392944?auto=format&fit=crop&q=80&w=1000" alt="Atelier Textile" class="w-full h-full object-cover">
                </div>
                <div class="absolute -top-8 -left-8 w-48 h-48 bg-afri-cream/50 rounded-full blur-2xl"></div>
            </div>
        </div>

        <!-- Final Quote -->
        <div class="text-center py-20 reveal">
            <p class="text-2xl md:text-4xl font-serif italic text-afri-dark max-w-3xl mx-auto leading-relaxed">
                "Le textile est le langage silencieux de l'élégance."
            </p>
            <div class="mt-8 text-afri-gold font-bold uppercase tracking-widest text-sm">— La Maison AfriTex</div>
        </div>
    </div>
</div>

<style>
    .reveal {
        opacity: 0;
        transform: translateY(30px);
        transition: all 1s cubic-bezier(0.22, 1, 0.36, 1);
    }
    .reveal.active {
        opacity: 1;
        transform: translateY(0);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
    });
</script>
@endsection