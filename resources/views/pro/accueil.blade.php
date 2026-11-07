@extends('layouts.app')
@section('title', 'Espace Pro, achat de tissus en gros · ' . boutique('nom'))
@section('meta_description', 'Couturiers, ateliers et boutiques : ouvrez un compte pro chez ' . boutique('nom') . ' pour commander vos tissus en gros aux prix revendeurs.')
@section('content')
@php
    // Présentation publique de l'Espace Pro, ou état de la demande de l'utilisateur connecté (en attente, refusée)
    $numeroWhatsapp = config('services.whatsapp.numero');
    $lienWhatsapp = $numeroWhatsapp ? 'https://wa.me/' . $numeroWhatsapp : null;
    $etapes = [
        ['Demandez votre compte', 'Le nom de votre atelier ou de votre boutique, votre ville et votre numéro WhatsApp suffisent.'],
        ['La boutique l\'ouvre', 'Nous vérifions votre demande et vous prévenons par e-mail dès que votre accès pro est ouvert.'],
        ['Commandez sur devis', 'Saisissez vos quantités aux prix pro. Nous confirmons le stock, la livraison et la date, vous validez puis payez.'],
    ];
    $avantages = [
        ['etiquette' => 'Prix revendeurs', 'icone' => 'billets', 'texte' => 'Un prix pro pour chaque format : au mètre ou au rouleau.'],
        ['etiquette' => 'Toute la commande sur une page', 'icone' => 'regle', 'texte' => 'Tous les tissus et leurs formats dans un seul tableau. Une commande passée se reprend en un clic.'],
        ['etiquette' => 'Devis avant paiement', 'icone' => 'courrier', 'texte' => 'Rien n\'est payé avant que vous ayez accepté le devis, frais de livraison et date compris.'],
        ['etiquette' => 'Tarifs en PDF', 'icone' => 'telechargement', 'texte' => 'La liste de nos prix pro, à jour, à télécharger quand vous préparez vos achats.'],
    ];
@endphp

<x-en-tete-page titre="Espace Pro" chapeau="Couturiers, ateliers de confection, boutiques de pagnes : achetez vos tissus en gros aux prix revendeurs.">
    @if(!$compte)
        <div class="apparition mt-8 flex flex-col justify-center gap-3 sm:flex-row" style="animation-delay: 240ms">
            <a href="{{ route('pro.inscription') }}" class="btn btn-or btn-lg">Demander un compte pro <x-icone nom="fleche" /></a>
            @guest
                <a href="{{ route('login') }}" class="btn btn-clair btn-lg">J'ai déjà un compte</a>
            @endguest
        </div>
    @endif
</x-en-tete-page>

<div class="conteneur pb-20 pt-12 lg:pb-28 lg:pt-16">

    {{-- État de la demande --}}
    @if($compte?->estEnAttente())
        <section class="apparition grid gap-8 bg-carte p-6 sm:p-10 lg:grid-cols-[1fr_20rem] lg:gap-14" aria-labelledby="titre-demande">
            <div>
                <span class="pastille pastille-attente">Demande en attente</span>
                <h2 id="titre-demande" class="titre-display mt-4 text-[1.75rem] sm:text-[2.1rem]">Votre demande est en cours d'examen</h2>
                <p class="mt-3 max-w-[60ch] text-encre-2">
                    Nous vérifions la demande de <strong class="font-medium text-encre">{{ $compte->boutique }}</strong>.
                    Vous recevrez un e-mail à <span class="text-encre">{{ Auth::user()->email }}</span> dès que votre accès pro sera ouvert.
                </p>
                @if($lienWhatsapp)
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="lien-fleche mt-6">Une question sur votre demande ? Écrivez-nous sur WhatsApp <x-icone nom="fleche" /></a>
                @endif
            </div>
            <dl class="space-y-4 border-t border-trait pt-6 text-[0.95rem] lg:border-l lg:border-t-0 lg:pl-10 lg:pt-0">
                <div><dt class="text-encre-2">Activité</dt><dd class="font-medium">{{ $compte->libelleActivite() }}</dd></div>
                <div><dt class="text-encre-2">Ville</dt><dd class="font-medium">{{ $compte->ville }}{{ $compte->commune ? ', ' . $compte->commune : '' }}</dd></div>
                <div><dt class="text-encre-2">WhatsApp</dt><dd class="chiffres font-medium">{{ $compte->whatsapp }}</dd></div>
                <div><dt class="text-encre-2">Envoyée le</dt><dd class="font-medium">{{ $compte->created_at->translatedFormat('j F Y') }}</dd></div>
            </dl>
        </section>
    @elseif($compte?->estRefuse())
        <section class="apparition bg-carte p-6 sm:p-10" aria-labelledby="titre-demande">
            <span class="pastille pastille-danger">Accès pro fermé</span>
            <h2 id="titre-demande" class="titre-display mt-4 text-[1.75rem] sm:text-[2.1rem]">
                {{ $compte->valide_le ? 'Votre accès pro est suspendu' : 'Nous ne pouvons pas ouvrir votre compte pro pour le moment' }}
            </h2>
            @if($compte->motif_refus)
                <p class="mt-4 max-w-[60ch] bg-papier-2 px-5 py-4"><span class="font-medium">Motif :</span> {{ $compte->motif_refus }}</p>
            @endif
            <p class="mt-4 max-w-[60ch] text-encre-2">Vous pouvez toujours acheter sur la boutique au prix public. Si votre situation a changé, écrivez-nous pour en parler.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                @if($lienWhatsapp)
                    <a href="{{ $lienWhatsapp }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun"><x-icone nom="message" /> Nous écrire sur WhatsApp</a>
                @endif
                <a href="{{ route('catalogue') }}" class="btn btn-contour">Voir le catalogue</a>
            </div>
        </section>
    @endif

    {{-- Comment ça marche --}}
    <section @class(['revele', 'mt-16 lg:mt-24' => $compte]) aria-labelledby="titre-etapes">
        <h2 id="titre-etapes" class="titre-display text-[1.9rem] sm:text-[2.3rem]">Comment ça marche</h2>
        <ol class="mt-8 grid gap-x-10 gap-y-8 md:grid-cols-3">
            @foreach($etapes as [$titre, $texte])
                <li class="border-t-2 border-brun pt-5">
                    <span class="titre-display chiffres text-[2.5rem] leading-none text-brun" aria-hidden="true">{{ $loop->iteration }}</span>
                    <h3 class="titre-display mt-3 text-[1.25rem]">{{ $titre }}</h3>
                    <p class="mt-2 max-w-[38ch] text-encre-2">{{ $texte }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Ce que contient l'Espace Pro --}}
    <section class="revele mt-16 grid gap-10 bg-papier-2 p-6 sm:p-10 lg:mt-24 lg:grid-cols-[18rem_1fr] lg:gap-16 lg:p-14" aria-labelledby="titre-avantages">
        <div>
            <h2 id="titre-avantages" class="titre-display text-[1.75rem] sm:text-[2.1rem]">Ce que vous y trouvez</h2>
            <p class="mt-3 text-encre-2">Un espace réservé aux professionnels du tissu, ouvert après validation de votre demande.</p>
        </div>
        <ul class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
            @foreach($avantages as $avantage)
                <li class="flex gap-4">
                    <span class="flex size-11 shrink-0 items-center justify-center bg-carte text-brun"><x-icone :nom="$avantage['icone']" class="size-5" /></span>
                    <span>
                        <span class="block font-semibold">{{ $avantage['etiquette'] }}</span>
                        <span class="mt-1 block text-[0.98rem] text-encre-2">{{ $avantage['texte'] }}</span>
                    </span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Appel final --}}
    @if(!$compte)
        <section class="revele mt-16 flex flex-col gap-6 border-t border-trait pt-10 md:flex-row md:items-end md:justify-between lg:mt-24" aria-labelledby="titre-appel">
            <div>
                <h2 id="titre-appel" class="titre-display text-[1.75rem] sm:text-[2.1rem]">Prêt à acheter en gros ?</h2>
                <p class="mt-2 max-w-[56ch] text-encre-2">
                    @auth
                        Votre demande part avec votre compte {{ Auth::user()->email }} : il ne reste qu'à présenter votre activité.
                    @else
                        La demande prend deux minutes. Déjà client de la boutique ? Connectez-vous d'abord, votre demande sera rattachée à votre compte.
                    @endauth
                </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                @guest
                    <a href="{{ route('login') }}" class="btn btn-contour btn-lg">Se connecter</a>
                @endguest
                <a href="{{ route('pro.inscription') }}" class="btn btn-brun btn-lg">Demander un compte pro <x-icone nom="fleche" /></a>
            </div>
        </section>
    @endif
</div>

@endsection
