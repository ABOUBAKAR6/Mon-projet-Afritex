@extends('layouts.app')
@section('title', 'FAQ — Questions Fréquentes | AfriTex')
@section('content')

<div class="bg-afri-beige min-h-screen py-24 px-6 sm:px-10 lg:px-16 relative">
    <div class="max-w-3xl mx-auto relative z-10">
        <div class="text-center mb-20 reveal">
            <span class="text-afri-gold text-xs uppercase tracking-widest font-bold mb-4 block">Assistance</span>
            <h1 class="text-4xl md:text-6xl font-serif font-bold text-afri-dark leading-tight">Centre <span class="italic text-afri-brown">d'Aide</span></h1>
            <p class="text-afri-brown/60 mt-6 max-w-md mx-auto">Toutes les réponses à vos questions pour une expérience d'achat sereine.</p>
        </div>

        <div class="space-y-6">
            @php
                $faqs = [
                    ['q' => 'Quels sont vos délais de livraison ?', 'a' => 'Nous livrons partout en Côte d\'Ivoire. Les délais varient généralement entre 24h et 72h selon votre localisation.'],
                    ['q' => 'Comment sont calculés les prix au mètre ?', 'a' => 'Nos prix sont fixés selon la qualité et la rareté du tissu. Le prix indiqué correspond à un mètre linéaire de tissu.'],
                    ['q' => 'Puis-je retourner un produit ?', 'a' => 'Oui, nous acceptons les retours sous 7 jours après réception, à condition que le tissu n\'ait pas été coupé ou endommagé.'],
                    ['q' => 'Proposez-vous des tarifs pour les grossistes ?', 'a' => 'Absolument. Nous avons une section dédiée aux commandes en gros avec des tarifs préférentiels pour les revendeurs et créateurs.'],
                    ['q' => 'Comment suivre ma commande ?', 'a' => 'Une fois votre commande expédiée, vous recevrez un numéro de suivi que vous pourrez utiliser sur notre page de tracking.'],
                ];
            @endphp

            @foreach($faqs as $faq)
            <div class="reveal bg-white rounded-[30px] border border-afri-cream overflow-hidden transition-all duration-300 hover:shadow-xl hover:shadow-afri-dark/5">
                <button onclick="toggleFaq(this)" class="w-full px-8 py-6 text-left flex justify-between items-center group">
                    <span class="text-lg font-serif font-bold text-afri-dark group-hover:text-afri-brown transition-colors">{{ $faq['q'] }}</span>
                    <span class="text-afri-gold transition-transform duration-300" data-icon="plus">＋</span>
                </button>
                <div class="max-h-0 overflow-hidden transition-all duration-500 ease-in-out">
                    <div class="px-8 pb-8 text-afri-brown/70 text-sm leading-relaxed">
                        {{ $faq['a'] }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="text-center mt-20 reveal">
            <p class="text-afri-dark/60 mb-6 italic">Une question sans réponse ?</p>
            <a href="https://wa.me/YOUR_NUMBER" class="bg-afri-dark text-white px-10 py-4 rounded-full font-bold uppercase tracking-widest text-xs transition-all hover:bg-afri-brown shadow-lg inline-block">
                Contacter la Conciergerie WhatsApp
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
    function toggleFaq(btn) {
        const content = btn.nextElementSibling;
        const icon = btn.querySelector('[data-icon]');

        if (content.style.maxHeight) {
            content.style.maxHeight = '';
            icon.style.transform = 'rotate(0deg)';
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
            icon.style.transform = 'rotate(45deg)';
        }
    }

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