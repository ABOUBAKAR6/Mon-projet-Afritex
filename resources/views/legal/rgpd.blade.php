@extends('layouts.app')
@section('title', 'Politique de Confidentialité (RGPD) — AfriTex')
@section('content')

<div class="bg-afri-beige min-h-screen py-24 px-6 sm:px-10 lg:px-16 relative">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-20 reveal">
            <span class="text-afri-gold text-xs uppercase tracking-widest font-bold mb-4 block">Confidentialité</span>
            <h1 class="text-4xl md:text-6xl font-serif font-bold text-afri-dark leading-tight">Politique de <span class="italic text-afri-brown">Confidentialité</span></h1>
            <div class="h-1 w-20 bg-afri-gold mx-auto mt-8"></div>
        </div>

        <div class="bg-white rounded-[40px] p-8 md:p-16 shadow-sm border border-afri-cream space-y-12 text-afri-dark/80 leading-relaxed">
            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">1. Collecte des données</h2>
                <p class="text-sm">
                    Nous collectons uniquement les informations nécessaires pour traiter vos commandes : nom, adresse de livraison, email et numéro de téléphone.
                    C'est l'essence même de notre engagement envers votre vie privée.
                </p>
            </div>

            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">2. Utilisation des données</h2>
                <p class="text-sm">
                    Vos données sont utilisées exclusivement pour :
                </p>
                <ul class="list-disc list-inside mt-4 space-y-2 text-sm">
                    <li>Le traitement et l'expédition de vos commandes de tissus.</li>
                    <li>La communication concernant le statut de votre livraison.</li>
                    <li>L'amélioration de notre catalogue selon vos préférences.</li>
                </ul>
            </div>

            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">3. Vos Droits (RGPD)</h2>
                <p class="text-sm">
                    Conformément au Règlement Général sur la Protection des Données (RGPD), vous disposez d'un droit d'accès, de rectification et de suppression de vos données personnelles.
                    Pour toute demande, vous pouvez nous contacter via notre formulaire de contact.
                </p>
            </div>

            <div class="reveal">
                <h2 class="text-2xl font-serif font-bold text-afri-dark mb-4">4. Sécurité</h2>
                <p class="text-sm">
                    Nous mettons en œuvre des mesures de sécurité rigoureuses pour protéger vos informations personnelles contre tout accès non autorisé.
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