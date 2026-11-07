@extends('layouts.app')
@section('title', ($produit->meta_title ?: $produit->nom) . ' · ' . boutique('nom'))
@section('meta_description', $produit->meta_description ?: 'Achetez ' . $produit->nom . ' chez '.boutique('nom').' : au mètre, au rouleau ou en confection, livré partout en Côte d\'Ivoire.')
@section('content')
@php
    // Formats de vente acceptés par le panier (CartService::addItem) et leur prix
    $formats = collect([
        'metre' => ['Au mètre', $produit->prix_metre, 'le mètre'],
        'rouleau' => ['Au rouleau', $produit->prix_rouleau, 'le rouleau'],
        'pantalon' => ['Pantalon', $produit->prix_pantalon, 'la pièce'],
        'chemise_courte' => ['Chemise manches courtes', $produit->prix_chemise, 'la pièce'],
        'chemise_longue' => ['Chemise manches longues', $produit->prix_chemise, 'la pièce'],
        'tunique_courte' => ['Tunique manches courtes', $produit->prix_tunique, 'la pièce'],
        'tunique_longue' => ['Tunique manches longues', $produit->prix_tunique, 'la pièce'],
    ])->filter(fn ($f) => !empty($f[1]) && $f[1] > 0);

    $images = collect([$produit->image])->merge($produit->images->pluck('chemin'))
        ->filter(fn ($c) => $c && file_exists(public_path($c)))->unique()->values();

    $nbAvis = $produit->reviews?->count() ?? 0;

    // « Discuter sur WhatsApp » : message déjà écrit pour la boutique (masqué si WHATSAPP_NUMERO est vide)
    $numeroWhatsapp = config('services.whatsapp.numero');
    $debutMessage = 'Bonjour ' . boutique('nom') . ",\n\nJe suis intéressé(e) par ce tissu :";
    $finMessage = "\n\n" . route('produit.show', $produit->slug) . "\n\nEst-il disponible ?";
    $lienWhatsappProduit = $numeroWhatsapp
        ? 'https://wa.me/' . $numeroWhatsapp . '?text=' . rawurlencode($debutMessage . "\n\n*" . $produit->nom . '*' . $finMessage)
        : null;
    $moyenne = $nbAvis > 0 ? round($produit->averageRating(), 1) : 0;

    $caracteristiques = collect([
        'Composition' => $produit->composition ?? null,
        'Origine' => $produit->origine ?? null,
        'Entretien' => $produit->entretien ?? null,
        'Grammage' => $produit->poids ?? null,
        'Couleurs' => is_array($produit->couleurs) ? implode(', ', $produit->couleurs) : null,
    ])->filter();
@endphp

<div class="conteneur pb-8 pt-4 sm:pb-20 sm:pt-6 lg:pb-28 lg:pt-8">
    {{-- Fil d'Ariane : pas au téléphone (la famille, au-dessus du titre, ramène à la liste) --}}
    <nav aria-label="Fil d'Ariane" class="hidden flex-wrap items-center gap-1.5 text-[0.9rem] text-encre-2 sm:flex">
        <a href="{{ route('accueil') }}" class="hover:text-brun hover:underline">Accueil</a>
        <x-icone nom="chevron-droite" class="size-3.5" />
        <a href="{{ route('catalogue') }}" class="hover:text-brun hover:underline">Catalogue</a>
        @if($produit->categorie)
            <x-icone nom="chevron-droite" class="size-3.5" />
            <a href="{{ route('catalogue', ['categorie_id' => $produit->categorie->id]) }}" class="hover:text-brun hover:underline">{{ $produit->categorie->nom }}</a>
        @endif
        <x-icone nom="chevron-droite" class="size-3.5" />
        <span aria-current="page" class="text-encre">{{ $produit->nom }}</span>
    </nav>

    {{-- Sur téléphone : titre, puis photo, puis achat. Sur ordinateur : photo à gauche, titre et achat à droite. --}}
    <div class="grid gap-8 sm:mt-6 lg:grid-cols-12 lg:gap-x-16 lg:gap-y-0">
        <div class="lg:col-span-5 lg:col-start-8 lg:row-start-1">
            @if($produit->categorie)
                <a href="{{ route('catalogue', ['categorie_id' => $produit->categorie->id]) }}" class="intitule inline-block hover:underline">{{ $produit->categorie->nom }}</a>
            @endif
            <h1 class="titre-display mt-2 text-[2.1rem] sm:text-[2.6rem]">{{ $produit->nom }}</h1>

            <a href="#avis" class="mt-3 inline-flex items-center gap-2 text-[0.95rem] text-encre-2 hover:text-brun">
                <span class="flex text-or" aria-hidden="true">
                    @for($i = 1; $i <= 5; $i++)
                        <x-icone nom="etoile" class="size-4 {{ $i <= floor($moyenne) ? 'fill-current' : 'text-trait-fort' }}" />
                    @endfor
                </span>
                @if($nbAvis > 0)
                    <span><span class="chiffres">{{ number_format($moyenne, 1, ',', ' ') }}</span> sur 5, {{ $nbAvis }} avis</span>
                @else
                    <span>Pas encore d'avis</span>
                @endif
            </a>
        </div>

        {{-- GALERIE --}}
        <div class="lg:col-span-7 lg:col-start-1 lg:row-span-2 lg:row-start-1" x-data="{ active: 0, images: @js($images->map(fn ($c) => asset($c))->values()) }">
            <div class="group relative aspect-square cursor-zoom-in overflow-hidden bg-papier-2"
                 @mousemove="if (window.matchMedia('(hover: hover)').matches) { const r = $el.getBoundingClientRect(); $refs.photo.style.transformOrigin = `${(($event.clientX - r.left) / r.width) * 100}% ${(($event.clientY - r.top) / r.height) * 100}%`; $refs.photo.style.transform = 'scale(1.8)'; }"
                 @mouseleave="$refs.photo.style.transform = 'scale(1)'">
                @if($images->isNotEmpty())
                    <img x-ref="photo" :src="images[active]" src="{{ asset($images->first()) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover transition-transform duration-500 ease-out-fort">
                @else
                    <div class="flex h-full w-full items-center justify-center text-brun-trait"><x-icone nom="image" class="size-16" /></div>
                @endif
            </div>

            @if($images->count() > 1)
                <ul class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6">
                    @foreach($images as $i => $chemin)
                        <li>
                            <button type="button" @click="active = {{ $i }}" :aria-pressed="active === {{ $i }}"
                                    class="relative block aspect-square w-full overflow-hidden bg-papier-2 transition-opacity duration-200"
                                    :class="active === {{ $i }} ? 'outline outline-2 -outline-offset-2 outline-brun' : 'opacity-70 hover:opacity-100'">
                                <img src="{{ asset($chemin) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                <span class="sr-only">Afficher la photo {{ $i + 1 }}</span>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- ACHAT --}}
        <div class="lg:col-span-5 lg:col-start-8 lg:row-start-2">
            <div class="lg:sticky lg:top-28 lg:pt-8">
                @if($formats->isEmpty())
                    <p class="bg-alerte-doux p-4 text-alerte">Ce tissu n'a pas encore de prix en ligne. Contactez-nous pour une commande.</p>
                    @if($lienWhatsappProduit)
                        <a href="{{ $lienWhatsappProduit }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun btn-lg mt-4 w-full"><x-icone nom="message" /> Discuter sur WhatsApp</a>
                    @endif
                @else
                    {{-- Choix du format, quantité et total en direct --}}
                    <form action="{{ route('panier.ajouter') }}" method="POST"
                          x-data="{
                              formats: @js($formats->map(fn ($f) => (float) $f[1])),
                              unites: @js($formats->map(fn ($f) => $f[2])),
                              choix: @js($formats->keys()->first()),
                              qte: 1,
                              get prix() { return this.formats[this.choix] || 0 },
                              get total() { return this.prix * (parseInt(this.qte) || 0) },
                              f(n) { return new Intl.NumberFormat('fr-FR').format(n).replace(/\s/g, ' ') + ' F' },
                              libelles: @js($formats->map(fn ($f) => $f[0])),
                              get lienWhatsapp() {
                                  const n = parseInt(this.qte) || 1;
                                  const unite = this.choix === 'metre' ? ' m' : (this.choix === 'rouleau' ? (n > 1 ? ' rouleaux' : ' rouleau') : (n > 1 ? ' pièces' : ' pièce'));
                                  const texte = @js($debutMessage) + '\n\n*' + @js($produit->nom) + '*\n' + this.libelles[this.choix] + ' : ' + n + unite
                                      + ' × ' + this.f(this.prix) + ' = ' + this.f(this.prix * n) + @js($finMessage);
                                  return 'https://wa.me/' + @js((string) $numeroWhatsapp) + '?text=' + encodeURIComponent(texte);
                              }
                          }">
                        @csrf
                        <input type="hidden" name="produit_id" value="{{ $produit->id }}">

                        <p class="titre-display chiffres text-[2.4rem] leading-none">
                            <span x-text="f(prix)">{{ number_format($formats->first()[1], 0, ',', ' ') }} F</span>
                            <span class="font-sans text-[1rem] font-normal text-encre-2" x-text="unites[choix]">{{ $formats->first()[2] }}</span>
                        </p>

                        <fieldset class="mt-7">
                            <legend class="etiquette">Format</legend>
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach($formats as $cle => [$libelle, $prix, $unite])
                                    <label class="choix min-h-[4.25rem] !p-3.5">
                                        <input type="radio" name="type_commande" value="{{ $cle }}" x-model="choix" class="sr-only" @checked($loop->first)>
                                        <span class="flex-1">
                                            <span class="block font-medium leading-snug">{{ $libelle }}</span>
                                            <span class="chiffres block text-[0.92rem] text-encre-2">{{ number_format($prix, 0, ',', ' ') }} F {{ $unite }}</span>
                                        </span>
                                        <span aria-hidden="true" class="flex size-5 shrink-0 items-center justify-center border border-trait-fort bg-carte" :class="choix === '{{ $cle }}' && '!border-brun !bg-brun text-white'">
                                            <x-icone nom="coche" class="size-3.5" x-show="choix === '{{ $cle }}'" />
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        <div class="mt-6 grid grid-cols-[auto_1fr] items-end gap-4">
                            <div>
                                <label for="quantite" class="etiquette" x-text="choix === 'metre' ? 'Nombre de mètres' : 'Quantité'">Quantité</label>
                                <div class="flex h-13 items-stretch border border-trait-fort bg-carte">
                                    <button type="button" @click="qte = Math.max(1, (parseInt(qte) || 1) - 1)" class="flex w-12 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                        <x-icone nom="moins" class="size-4" /><span class="sr-only">Diminuer</span>
                                    </button>
                                    <input id="quantite" type="number" name="quantite" min="1" x-model="qte" value="1"
                                           class="chiffres w-16 border-0 bg-transparent text-center text-[1.05rem] font-semibold focus:ring-0">
                                    <button type="button" @click="qte = (parseInt(qte) || 0) + 1" class="flex w-12 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                        <x-icone nom="plus" class="size-4" /><span class="sr-only">Augmenter</span>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right" aria-live="polite">
                                <p class="text-[0.92rem] text-encre-2">Total</p>
                                <p class="titre-display chiffres text-[1.7rem] leading-tight text-brun" x-text="f(total)">{{ number_format($formats->first()[1], 0, ',', ' ') }} F</p>
                            </div>
                        </div>

                        @auth
                            <button type="submit" class="btn btn-brun btn-lg mt-6 w-full"><x-icone nom="panier" /> Ajouter au panier</button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-brun btn-lg mt-6 w-full">Se connecter pour commander</a>
                            <p class="mt-2 text-center text-[0.92rem] text-encre-2">Pas encore de compte ? <a href="{{ route('register') }}" class="lien">Créez-le en une minute</a>.</p>
                        @endauth

                        @if($lienWhatsappProduit)
                            {{-- Le message reprend le format, la quantité et le total choisis ci-dessus --}}
                            <a href="{{ $lienWhatsappProduit }}" :href="lienWhatsapp" target="_blank" rel="noopener noreferrer" class="btn btn-contour btn-lg mt-3 w-full"><x-icone nom="message" /> Discuter sur WhatsApp</a>
                        @endif
                    </form>
                @endif

                @php $etatStock = $produit->etatStock(); @endphp
                <p class="mt-5 flex items-center gap-2 text-[0.95rem] {{ $etatStock === 'ok' ? 'text-succes' : 'text-alerte' }}">
                    <span class="size-2 rounded-full bg-current" aria-hidden="true"></span>
                    @if($etatStock === 'ok')
                        En stock
                    @elseif($etatStock === 'faible')
                        Stock limité : {{ $produit->libelleStock() }}
                    @else
                        Bientôt de retour en stock
                    @endif
                </p>

                {{-- Livraison, paiement, qualité : pas au téléphone (retiré à la demande de la boutique) --}}
                <dl class="mt-8 hidden divide-y divide-trait border-y border-trait text-[0.95rem] sm:block">
                    <div class="flex gap-4 py-3.5">
                        <x-icone nom="livraison" class="mt-0.5 shrink-0 text-brun" />
                        <div><dt class="font-medium">Livraison partout en Côte d'Ivoire</dt><dd class="text-encre-2">Sous 24 à 72 h, offerte dès 50 000 F d'achat.</dd></div>
                    </div>
                    <div class="flex gap-4 py-3.5">
                        <x-icone nom="mobile" class="mt-0.5 shrink-0 text-brun" />
                        <div><dt class="font-medium">Paiement à votre convenance</dt><dd class="text-encre-2">Mobile Money, carte bancaire ou espèces à la livraison.</dd></div>
                    </div>
                    <div class="flex gap-4 py-3.5">
                        <x-icone nom="bouclier" class="mt-0.5 shrink-0 text-brun" />
                        <div><dt class="font-medium">Qualité garantie</dt><dd class="text-encre-2">Retours acceptés sous 7 jours.</dd></div>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    {{-- DESCRIPTION ET CARACTÉRISTIQUES (pas au téléphone, retiré à la demande de la boutique) --}}
    @if($produit->description || $caracteristiques->isNotEmpty())
        <section class="revele mt-20 hidden gap-10 sm:grid lg:mt-28 lg:grid-cols-12 lg:gap-16" aria-labelledby="titre-description">
            <x-titre-bloc intitule="Le tissu" titre="Description" id="titre-description" class="lg:col-span-4" />
            <div class="lg:col-span-8">
                @if($produit->description)
                    <div class="max-w-[68ch] whitespace-pre-line text-[1.05rem] leading-relaxed lg:text-[1.12rem]">{{ $produit->description }}</div>
                @endif
                @if($caracteristiques->isNotEmpty())
                    <dl class="mt-8 grid gap-x-10 gap-y-5 sm:grid-cols-2">
                        @foreach($caracteristiques as $libelle => $valeur)
                            <div>
                                <dt class="text-[0.92rem] text-encre-2">{{ $libelle }}</dt>
                                <dd class="font-medium">{{ $valeur }}</dd>
                            </div>
                        @endforeach
                    </dl>
                @endif
            </div>
        </section>
    @endif

    {{-- AVIS --}}
    <section id="avis" class="mt-12 grid gap-10 bg-papier-2 p-6 sm:mt-20 sm:p-10 lg:mt-28 lg:grid-cols-12 lg:gap-16 lg:p-14" aria-labelledby="titre-avis">
        <div class="revele lg:col-span-4">
            <x-titre-bloc intitule="Avis clients" titre="Ce qu'en disent nos clients" id="titre-avis" />
            @if($nbAvis > 0)
                <p class="mt-6 flex items-baseline gap-2">
                    <span class="titre-display chiffres text-[3rem] leading-none text-brun">{{ number_format($moyenne, 1, ',', ' ') }}</span>
                    <span class="text-encre-2">sur 5, d'après {{ $nbAvis }} avis</span>
                </p>
            @endif

            @auth
                <form action="{{ route('produit.review.store', $produit->id) }}" method="POST" class="mt-8 space-y-5 bg-carte p-6">
                    @csrf
                    <p class="font-medium">Donner votre avis</p>
                    <div>
                        <label for="note" class="etiquette">Votre note</label>
                        <select id="note" name="rating" class="champ">
                            <option value="5">5 sur 5, excellent</option>
                            <option value="4">4 sur 5, très bien</option>
                            <option value="3">3 sur 5, correct</option>
                            <option value="2">2 sur 5, décevant</option>
                            <option value="1">1 sur 5, mauvais</option>
                        </select>
                    </div>
                    <div>
                        <label for="commentaire" class="etiquette">Votre commentaire</label>
                        <textarea id="commentaire" name="comment" rows="4" class="champ" placeholder="La tenue du tissu, le rendu des couleurs, la confection…"></textarea>
                    </div>
                    <button type="submit" class="btn btn-brun w-full">Publier mon avis</button>
                </form>
            @else
                <p class="mt-8 text-encre-2"><a href="{{ route('login') }}" class="lien">Connectez-vous</a> pour donner votre avis sur ce tissu.</p>
            @endauth
        </div>

        <div class="lg:col-span-8">
            @forelse($produit->reviews as $avis)
                <figure class="revele border-b border-trait py-6 first:pt-0 last:border-0" style="--delai: {{ min($loop->index, 4) * 60 }}ms">
                    <div class="flex items-center justify-between gap-4">
                        <figcaption class="flex items-center gap-3">
                            <span aria-hidden="true" class="titre-display flex size-10 items-center justify-center rounded-full bg-brun-doux text-brun">{{ mb_strtoupper(mb_substr($avis->user?->name ?? 'C', 0, 1)) }}</span>
                            <span>
                                <span class="block font-medium">{{ $avis->user?->name ?? 'Client' }}</span>
                                <span class="block text-[0.88rem] text-encre-2">{{ $avis->created_at->diffForHumans() }}</span>
                            </span>
                        </figcaption>
                        <span class="flex text-or" aria-label="{{ $avis->rating }} sur 5">
                            @for($i = 1; $i <= 5; $i++)
                                <x-icone nom="etoile" class="size-4 {{ $i <= $avis->rating ? 'fill-current' : 'text-trait-fort' }}" />
                            @endfor
                        </span>
                    </div>
                    @if($avis->comment)
                        <blockquote class="mt-4 max-w-[62ch] text-[1.02rem]">{{ $avis->comment }}</blockquote>
                    @endif
                </figure>
            @empty
                <div class="flex h-full min-h-48 flex-col items-start justify-center bg-carte p-8">
                    <p class="titre-display text-[1.3rem]">Aucun avis pour le moment</p>
                    <p class="mt-2 text-encre-2">Vous avez cousu ce tissu ? Votre retour aidera les prochains clients à choisir.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- DANS LA MÊME FAMILLE --}}
    @php $similaires = ($produitsSimilaires ?? collect())->where('id', '!=', $produit->id)->take(4); @endphp
    @if($similaires->isNotEmpty())
        <section class="mt-12 sm:mt-20 lg:mt-28" aria-labelledby="titre-similaires">
            <div class="revele flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <x-titre-bloc intitule="Dans la même famille" titre="Vous aimerez aussi" id="titre-similaires" />
                @if($produit->categorie)
                    <a href="{{ route('catalogue', ['categorie_id' => $produit->categorie->id]) }}" class="lien-fleche shrink-0">Tout {{ $produit->categorie->nom }} <x-icone nom="fleche" /></a>
                @endif
            </div>
            {{-- Téléphone : rangée qui glisse à l'horizontale jusqu'au bord de l'écran (la carte suivante dépasse) ;
                 tablette et ordinateur : grille --}}
            <div tabindex="0" role="region" aria-label="Vous aimerez aussi, défilement horizontal"
                 class="defilement-discret mt-8 mr-[calc(50%-50vw)] flex snap-x snap-mandatory gap-3 overflow-x-auto pb-2 pr-4 motion-safe:scroll-smooth focus-visible:outline-offset-4 sm:mt-10 sm:mr-0 sm:grid sm:grid-cols-2 sm:overflow-visible sm:pb-0 sm:pr-0 lg:grid-cols-4 lg:gap-4">
                @foreach($similaires as $sim)
                    <x-carte-produit :produit="$sim" class="w-[78%] shrink-0 snap-start sm:w-auto" />
                @endforeach
            </div>
        </section>
    @endif
</div>

@endsection
