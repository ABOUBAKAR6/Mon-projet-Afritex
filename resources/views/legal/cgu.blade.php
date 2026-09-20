@extends('layouts.app')
@section('title', 'Conditions Générales d\'Utilisation — AfriTex')
@section('content')

<div class="bg-afri-beige min-h-screen py-24 px-6 sm:px-10 lg:px-16 relative">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-20 reveal">
            <span class="text-afri-gold text-xs uppercase tracking-widest font-bold mb-4 block">Conditions</span>
            <h1 class="text-4xl md:text-6xl font-serif font-bold text-afri-dark leading-tight">Conditions <span class="italic text-afri-brown">Générales</span></h1>
            <div class="h-1 w-20 bg-afri-gold mx-auto mt-8"></div>
        </div>

        <div class="bg-white rounded-[40px] p-8 md:p-16 shadow-sm border border-afri-cream space-y-12 text-afri-dark/80 leading-relaxed">
            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">1. Objet</h2>
                <p class="text-sm">
                    Les présentes conditions générales d'utilisation (CGU) ont pour objet de définir les modalités de mise à disposition et d'utilisation du site AfriTex.
                </p>
            </div>

            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">2. Ventes et Livraisons</h2>
                <p class="text-sm">
                    Toutes les commandes passées via le site sont traitées avec le plus grand soin. Les délais de livraison sont indicatifs et peuvent varier selon la destination en Côte d'Ivoire.
                </p>
            </div>

            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">3. Prix et Paiements</h2>
                <p class="text-sm">
                    Les prix sont indiqués en FCFA. AfriTex se réserve le droit de modifier ses prix à tout moment, mais les produits seront facturés sur la base du tarif en vigueur au moment de la validation de la commande.
                </p>
            </div>

            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">4. Propriété Intellectuelle</h2>
                <p class="text-sm">
                    L'ensemble du contenu du site (textes, images, design) est la propriété exclusive d'AfriTex. Toute reproduction totale ou partielle est strictement interdite.
                </p>
            </div>
        </div>

        <div class="text-center mt-16 reveal">
            <a href="{{ route('accueil') }}" class="text-afri-brown font-bold text-sm uppercase tracking-widest border-b border-afri-brown pb-1 hover:text-afri-dark transition-colors">
                Retour à la boutique
            </a>
        </div>
    </div>
</div>

<style>
    .reveal {
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.8s cubic-bezier(0.22, 1, 0.36, 1);
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