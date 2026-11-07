@extends('layouts.app')
@section('title', 'Questions fréquentes · ' . boutique('nom'))
@section('content')
@php
    $lienWhatsapp = config('services.whatsapp.numero') ? 'https://wa.me/' . config('services.whatsapp.numero') : null;
    $faqs = [
        ['Quels sont vos délais de livraison ?', 'Nous livrons partout en Côte d\'Ivoire. Les délais varient généralement entre 24 et 72 heures selon votre localisation.'],
        ['Combien coûte la livraison ?', 'La livraison à domicile est facturée 1 500 F, et offerte dès 50 000 F d\'achat. Le retrait en boutique est gratuit, dès le lendemain de la commande.'],
        ['Quels moyens de paiement acceptez-vous ?', 'Mobile Money (Orange Money, MTN MoMo, Wave, Moov), carte bancaire (Visa, Mastercard, Maestro), ou espèces à la livraison. Les paiements en ligne sont sécurisés par FedaPay.'],
        ['Comment sont calculés les prix au mètre ?', 'Le prix dépend de la qualité et de la rareté du tissu. Le prix affiché correspond à un mètre linéaire. Vous pouvez aussi acheter le rouleau entier.'],
        ['Combien de mètres dois-je commander ?', 'Cela dépend du vêtement et de la taille. Une fois connecté, le calculateur de métrage vous donne une estimation pour une robe, une chemise, un pantalon ou une tunique.'],
        ['Proposez-vous des tarifs pour les grossistes ?', 'Oui. Le rouleau entier est vendu à un tarif avantageux par rapport au mètre, pensé pour les ateliers, tailleurs et revendeurs. Pour de grandes quantités, écrivez-nous.'],
        ['Puis-je retourner un produit ?', 'Oui, nous acceptons les retours sous 7 jours après réception, à condition que le tissu n\'ait pas été coupé ni endommagé.'],
        ['Comment suivre ma commande ?', 'Retrouvez chaque commande et son avancement dans « Mes commandes ». Une fois votre commande expédiée, vous recevez aussi un numéro de suivi à partager.'],
    ];
@endphp

<x-en-tete-page titre="Questions fréquentes" chapeau="Livraison, paiement, métrage, retours : les réponses aux questions que l'on nous pose le plus." />

<div class="conteneur grid gap-10 pb-20 pt-12 lg:grid-cols-[1fr_2fr] lg:gap-16 lg:pb-28 lg:pt-16">
    <aside class="revele lg:sticky lg:top-28 lg:self-start">
        <x-titre-bloc intitule="Aide" titre="Une question sans réponse ?" />
        <p class="mt-4 text-encre-2">Écrivez-nous : nous répondons aux questions sur les tissus, les métrages et les commandes en gros.</p>
        @if($lienWhatsapp)
            <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun btn-lg mt-6"><x-icone nom="message" /> Nous écrire sur WhatsApp</a>
        @endif
        <p class="mt-6 text-[0.95rem] text-encre-2">Voir aussi les <a href="{{ route('cgu') }}" class="lien">conditions générales</a> et la <a href="{{ route('privacy') }}" class="lien">politique de confidentialité</a>.</p>
    </aside>

    <div class="divide-y divide-trait border-y border-trait">
        @foreach($faqs as [$question, $reponse])
            <details class="revele group" style="--delai: {{ $loop->index * 40 }}ms" @if($loop->first) open @endif>
                <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-6 py-5 [&::-webkit-details-marker]:hidden">
                    <span class="titre-display text-[1.12rem] transition-colors group-hover:text-brun-fonce lg:text-[1.2rem]">{{ $question }}</span>
                    <span class="flex size-9 shrink-0 items-center justify-center bg-brun-doux text-brun transition-transform duration-200 group-open:rotate-45" aria-hidden="true">
                        <x-icone nom="plus" class="size-4" />
                    </span>
                </summary>
                <p class="max-w-[62ch] pb-6 pr-14 text-encre-2">{{ $reponse }}</p>
            </details>
        @endforeach
    </div>
</div>

@endsection
