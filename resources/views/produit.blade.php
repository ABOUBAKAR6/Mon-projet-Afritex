@extends('layouts.app')
@section('title', ($produit->meta_title ?: $produit->nom) . ' · ' . boutique('nom'))
@section('meta_description', $produit->meta_description ?: 'Achetez ' . $produit->nom . ' chez '.boutique('nom').' : au mètre ou au rouleau, livré partout en Côte d\'Ivoire.')
@php
    // Formats de vente acceptés par le panier (CartService::addItem) et leur prix
    $formats = collect([
        'metre' => ['Au mètre', $produit->prix_metre, 'le mètre'],
        'rouleau' => ['Au rouleau', $produit->prix_rouleau, 'le rouleau'],
    ])->filter(fn ($f) => !empty($f[1]) && $f[1] > 0);
@endphp
{{-- Téléphone et tablette : la barre d'achat fixe remplace la barre Catalogue / Panier / Connexion --}}
@if($formats->isNotEmpty())
    @section('sans_acces_rapides', '1')
@endif
@section('content')
@php
    // Photos et la couleur que chacune montre (photo principale : produits.couleur_image ; les autres : produit_images.couleur)
    $photos = collect([['chemin' => $produit->image, 'couleur' => $produit->couleur_image]])
        ->merge($produit->images->map(fn ($i) => ['chemin' => $i->chemin, 'couleur' => $i->couleur]))
        ->filter(fn ($p) => $p['chemin'] && file_exists(public_path($p['chemin'])))
        ->unique('chemin')->values();
    $images = $photos->pluck('chemin');

    // Titre selon la photo regardée : « Famille couleur » (ex. « Morrocco orange »), sinon le nom du tissu
    $baseTitre = $produit->categorie?->nom ?: $produit->nom;
    $titres = $photos->map(fn ($p) => filled($p['couleur'])
        ? $baseTitre.' '.mb_strtolower(mb_substr($p['couleur'], 0, 1)).mb_substr($p['couleur'], 1)
        : $produit->nom)->values();
    $couleursPhotos = $photos->pluck('couleur')->values();

    // Pastilles : couleurs saisies pour le tissu + couleurs des photos, sans doublon
    $listeCouleurs = collect((array) ($produit->couleurs ?? []))->merge($photos->pluck('couleur')->filter())
        ->unique(fn ($c) => mb_strtolower($c))->values();

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

    $estFavori = auth()->check() && auth()->user()->wishlist()->where('produit_id', $produit->id)->exists();
    $lienFamille = $produit->categorie ? route('catalogue', ['categorie_id' => $produit->categorie->id]) : route('catalogue');

    // Rangée d'assurances (téléphone et tablette) : uniquement des engagements déjà affichés sur le site
    $assurances = [
        ['bouclier', 'Qualité garantie'],
        ['regle', $formats->count() > 1 ? 'Au mètre ou au rouleau' : ($formats->keys()->first() === 'rouleau' ? 'Au rouleau' : 'Au mètre')],
        ['livraison', 'Livré en 24 à 72 h'],
        ['mobile', 'Mobile Money'],
    ];
@endphp

<div class="conteneur pb-8 sm:pb-20 sm:pt-6 lg:pb-28 lg:pt-8">
    {{-- Fil d'Ariane : pas au téléphone (la flèche sur la photo et la famille ramènent à la liste) --}}
    <nav aria-label="Fil d'Ariane" class="hidden flex-wrap items-center gap-1.5 text-[0.9rem] text-encre-2 sm:flex">
        <a href="{{ route('accueil') }}" class="hover:text-brun hover:underline">Accueil</a>
        <x-icone nom="chevron-droite" class="size-3.5" />
        <a href="{{ route('catalogue') }}" class="hover:text-brun hover:underline">Catalogue</a>
        @if($produit->categorie)
            <x-icone nom="chevron-droite" class="size-3.5" />
            <a href="{{ $lienFamille }}" class="hover:text-brun hover:underline">{{ $produit->categorie->nom }}</a>
        @endif
        <x-icone nom="chevron-droite" class="size-3.5" />
        <span aria-current="page" class="text-encre">{{ $produit->nom }}</span>
    </nav>

    {{--
      Téléphone : photo bord à bord, puis la fiche qui remonte sur le bas de la photo, puis l'achat.
      Tablette : photo à gauche, fiche et achat à droite.
      Ordinateur (lg) : inchangé, photo sur 7 colonnes, fiche et achat sur 5.
    --}}
    {{--
      Photo regardée (« photo ») partagée par la galerie, le titre et l'achat :
      la couleur de la photo est annoncée (événement photo-couleur) et choisie pour la commande ;
      toucher une pastille (événement choisir-couleur) montre la photo de cette couleur.
    --}}
    <div class="grid sm:mt-6 sm:grid-cols-2 sm:grid-rows-[auto_1fr] sm:gap-x-8 sm:gap-y-6 lg:grid-cols-12 lg:grid-rows-none lg:gap-x-16 lg:gap-y-0"
         x-data="{
             photo: 0,
             couleurActive: '',
             toucherRond(nom) {
                 window.dispatchEvent(new CustomEvent('choisir-couleur-rond', { detail: nom }));
                 this.montrerCouleur(nom);
             },
             titres: @js($titres),
             couleursPhotos: @js($couleursPhotos),
             get titre() { return this.titres[this.photo] || @js($produit->nom) },
             init() { this.$watch('photo', (i) => this.annoncer(i)); this.$nextTick(() => this.annoncer(0)) },
             annoncer(i) { const c = this.couleursPhotos[i]; if (c) window.dispatchEvent(new CustomEvent('photo-couleur', { detail: c })) },
             montrerCouleur(nom) {
                 const i = this.couleursPhotos.findIndex((c) => c && c.toLowerCase() === String(nom).toLowerCase());
                 if (i < 0 || i === this.photo) return;
                 this.photo = i;
                 const bande = document.getElementById('bande-photos');
                 if (bande && bande.offsetParent) bande.scrollTo({ left: i * bande.clientWidth, behavior: 'smooth' });
             },
         }"
         @choisir-couleur.window="montrerCouleur($event.detail)"
         @couleur-choisie.window="couleurActive = $event.detail">

        {{-- GALERIE --}}
        <div class="sm:sticky sm:top-24 sm:col-start-1 sm:row-span-2 sm:row-start-1 sm:self-start lg:static lg:col-span-7 lg:col-start-1 lg:row-span-2 lg:row-start-1 lg:self-auto"
             x-data="{ images: @js($images->map(fn ($c) => asset($c))->values()) }">

            {{-- Téléphone : photos qui glissent au doigt, bord à bord, retour et favori posés dessus --}}
            <div class="relative -mx-4 sm:hidden">
                @if($images->isNotEmpty())
                    <div id="bande-photos" class="defilement-discret flex aspect-square snap-x snap-mandatory overflow-x-auto bg-papier-2"
                         @scroll.passive="photo = Math.round($el.scrollLeft / $el.clientWidth)"
                         aria-label="Photos de {{ $produit->nom }}" role="region" tabindex="0">
                        @foreach($images as $i => $chemin)
                            <img src="{{ asset($chemin) }}" alt="{{ $i === 0 ? $produit->nom : $produit->nom.', photo '.($i + 1) }}"
                                 @if($i > 0) loading="lazy" @endif decoding="async" class="h-full w-full shrink-0 snap-center object-cover">
                        @endforeach
                    </div>
                @else
                    <div class="flex aspect-square items-center justify-center bg-papier-2 text-brun-trait"><x-icone nom="image" class="size-16" /></div>
                @endif

                <a href="{{ $lienFamille }}" onclick="if (document.referrer.indexOf(location.origin) === 0 && history.length > 1) { history.back(); return false; }"
                   class="absolute left-4 top-4 flex size-11 items-center justify-center bg-carte text-encre transition-transform duration-150 active:scale-95">
                    <x-icone nom="fleche-gauche" class="size-5" />
                    <span class="sr-only">Retour</span>
                </a>
                <div class="absolute right-4 top-4">
                    @auth
                        <form action="{{ route('wishlist.toggle', $produit->id) }}" method="POST">
                            @csrf
                            <button type="submit" aria-pressed="{{ $estFavori ? 'true' : 'false' }}"
                                    class="flex size-11 items-center justify-center bg-carte transition-transform duration-150 active:scale-95 {{ $estFavori ? 'text-brun' : 'text-encre' }}">
                                <x-icone nom="coeur" class="size-5 {{ $estFavori ? 'fill-current' : '' }}" />
                                <span class="sr-only">{{ $estFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' }}</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="flex size-11 items-center justify-center bg-carte text-encre transition-transform duration-150 active:scale-95">
                            <x-icone nom="coeur" class="size-5" />
                            <span class="sr-only">Se connecter pour ajouter aux favoris</span>
                        </a>
                    @endauth
                </div>

                @if($images->count() > 1)
                    <p class="chiffres absolute bottom-3 right-4 bg-encre px-2 py-0.5 text-[0.8rem] font-medium text-papier" aria-hidden="true">
                        <span x-text="photo + 1">1</span> / {{ $images->count() }}
                    </p>
                @endif
            </div>

            {{-- Tablette et ordinateur : grande photo (loupe au survol) et vignettes --}}
            <div class="hidden sm:block">
                <div class="group relative aspect-square cursor-zoom-in overflow-hidden bg-papier-2"
                     @mousemove="if (window.matchMedia('(hover: hover)').matches) { const r = $el.getBoundingClientRect(); $refs.photo.style.transformOrigin = `${(($event.clientX - r.left) / r.width) * 100}% ${(($event.clientY - r.top) / r.height) * 100}%`; $refs.photo.style.transform = 'scale(1.8)'; }"
                     @mouseleave="$refs.photo.style.transform = 'scale(1)'">
                    @if($images->isNotEmpty())
                        <img x-ref="photo" :src="images[photo]" src="{{ asset($images->first()) }}" alt="{{ $produit->nom }}" class="h-full w-full object-cover transition-transform duration-500 ease-out-fort">
                    @else
                        <div class="flex h-full w-full items-center justify-center text-brun-trait"><x-icone nom="image" class="size-16" /></div>
                    @endif
                </div>

                @if($images->count() > 1)
                    <ul class="mt-3 grid grid-cols-5 gap-2 sm:grid-cols-6">
                        @foreach($images as $i => $chemin)
                            <li>
                                <button type="button" @click="photo = {{ $i }}" :aria-pressed="photo === {{ $i }}"
                                        class="relative block aspect-square w-full overflow-hidden bg-papier-2 transition-opacity duration-200"
                                        :class="photo === {{ $i }} ? 'outline outline-2 -outline-offset-2 outline-brun' : 'opacity-70 hover:opacity-100'">
                                    <img src="{{ asset($chemin) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                    <span class="sr-only">Afficher la photo {{ $i + 1 }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- FICHE : sous la photo, serrée au téléphone. Famille à gauche, logo WhatsApp à droite sur la même ligne. --}}
        <div class="pt-3 sm:col-start-2 sm:row-start-1 sm:pt-0 lg:col-span-5 lg:col-start-8 lg:row-start-1">
            <div class="flex min-h-9 items-center justify-between gap-3">
                @if($produit->categorie)
                    <a href="{{ $lienFamille }}" class="intitule inline-block hover:underline">{{ $produit->categorie->nom }}</a>
                @else
                    <span></span>
                @endif
                @if($lienWhatsappProduit)
                    {{-- Le message cite le titre affiché (donc la couleur de la photo regardée) --}}
                    <a href="{{ $lienWhatsappProduit }}" target="_blank" rel="noopener noreferrer" title="Discuter sur WhatsApp"
                       :href="'https://wa.me/' + @js((string) $numeroWhatsapp) + '?text=' + encodeURIComponent(@js($debutMessage) + '\n\n*' + titre + '*' + @js($finMessage))"
                       class="-mr-1 flex size-9 shrink-0 items-center justify-center transition-transform duration-150 active:scale-90">
                        <x-icone nom="whatsapp" class="size-7" />
                        <span class="sr-only">Discuter de ce tissu sur WhatsApp</span>
                    </a>
                @endif
            </div>
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="titre-display text-[1.6rem] leading-tight sm:mt-1 sm:text-[2.3rem] lg:text-[2.6rem]"><span x-text="titre">{{ $titres->first() ?? $produit->nom }}</span></h1>
                </div>

                {{-- Couleurs en ronds à côté du titre : toucher = choisir la couleur et voir sa photo (re-toucher = annuler) --}}
                @if($listeCouleurs->isNotEmpty())
                    @php $nbRonds = $listeCouleurs->count() > 6 ? 5 : 6; @endphp
                    <div class="grid shrink-0 grid-cols-3 justify-items-center" role="group" aria-label="Couleurs disponibles">
                        @foreach($listeCouleurs->take($nbRonds) as $nomRond)
                            @php $teinteRond = teinte_couleur($nomRond); @endphp
                            <button type="button" @click="toucherRond(@js($nomRond))" :aria-pressed="(couleurActive || '').toLowerCase() === @js(mb_strtolower($nomRond))"
                                    title="{{ $nomRond }}" class="group/rond flex size-9 items-center justify-center rounded-full">
                                <span aria-hidden="true"
                                      class="flex size-6 items-center justify-center rounded-full border border-black/15 text-[0.7rem] font-semibold text-encre-2 transition-[box-shadow,transform] duration-150 group-active/rond:scale-90"
                                      :class="(couleurActive || '').toLowerCase() === @js(mb_strtolower($nomRond)) && 'ring-2 ring-brun ring-offset-2 ring-offset-papier'"
                                      style="background: {{ $teinteRond ?? 'var(--papier-2)' }}">{{ $teinteRond ? '' : mb_strtoupper(mb_substr($nomRond, 0, 1)) }}</span>
                                <span class="sr-only">{{ $nomRond }}</span>
                            </button>
                        @endforeach
                        @if($listeCouleurs->count() > $nbRonds)
                            <a href="#titre-couleurs" class="flex size-9 items-center justify-center text-[0.85rem] font-medium text-encre-2">+{{ $listeCouleurs->count() - $nbRonds }}<span class="sr-only"> autres couleurs</span></a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Note : seulement quand il y a de vrais avis --}}
            @if($nbAvis > 0)
                <a href="#avis" class="mt-2 inline-flex items-center gap-2 text-[0.95rem] text-encre-2 hover:text-brun sm:mt-3">
                    <span class="flex text-or" aria-hidden="true">
                        @for($i = 1; $i <= 5; $i++)
                            <x-icone nom="etoile" class="size-4 {{ $i <= floor($moyenne) ? 'fill-current' : 'text-trait-fort' }}" />
                        @endfor
                    </span>
                    <span><span class="chiffres">{{ number_format($moyenne, 1, ',', ' ') }}</span> sur 5, {{ $nbAvis }} avis</span>
                </a>
            @endif
        </div>

        {{-- ACHAT --}}
        <div class="mt-3 sm:col-start-2 sm:row-start-2 sm:mt-0 lg:col-span-5 lg:col-start-8 lg:row-start-2">
            <div class="lg:sticky lg:top-28 lg:pt-8">
                @if($formats->isEmpty())
                    <p class="bg-alerte-doux p-4 text-alerte">Ce tissu n'a pas encore de prix en ligne. Contactez-nous pour une commande.</p>
                    @if($lienWhatsappProduit)
                        <a href="{{ $lienWhatsappProduit }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun btn-lg mt-4 w-full"><x-icone nom="whatsapp" /> Discuter sur WhatsApp</a>
                    @endif
                @else
                    {{-- Choix du format, quantité et total en direct --}}
                    <form action="{{ route('panier.ajouter') }}" method="POST"
                          @photo-couleur.window="couleur = $event.detail; autre = false"
                          @choisir-couleur-rond.window="choisirCouleur($event.detail)"
                          x-data="{
                              formats: @js($formats->map(fn ($f) => (float) $f[1])),
                              unites: @js($formats->map(fn ($f) => $f[2])),
                              choix: @js($formats->keys()->first()),
                              qte: 1,
                              couleur: '',
                              init() { this.$watch('couleur', (v) => window.dispatchEvent(new CustomEvent('couleur-choisie', { detail: v }))) },
                              autre: false,
                              choisirCouleur(nom) {
                                  this.couleur = this.couleur === nom ? '' : nom;
                                  this.autre = false;
                                  if (this.couleur) window.dispatchEvent(new CustomEvent('choisir-couleur', { detail: this.couleur }));
                              },
                              ouvrirAutre() { this.autre = !this.autre; this.couleur = ''; if (this.autre) this.$nextTick(() => this.$refs.autreCouleur.focus()) },
                              get prix() { return this.formats[this.choix] || 0 },
                              get total() { return this.prix * (parseInt(this.qte) || 0) },
                              f(n) { return new Intl.NumberFormat('fr-FR').format(n).replace(/\s/g, ' ') + ' F' },
                              moins() { this.qte = Math.max(1, (parseInt(this.qte) || 1) - 1) },
                              plus() { this.qte = (parseInt(this.qte) || 0) + 1 },
                              libelles: @js($formats->map(fn ($f) => $f[0])),
                              get lienWhatsapp() {
                                  const n = parseInt(this.qte) || 1;
                                  const unite = this.choix === 'metre' ? ' m' : (this.choix === 'rouleau' ? (n > 1 ? ' rouleaux' : ' rouleau') : (n > 1 ? ' pièces' : ' pièce'));
                                  const texte = @js($debutMessage) + '\n\n*' + @js($produit->nom) + '*\n' + this.libelles[this.choix] + ' : ' + n + unite
                                      + ' × ' + this.f(this.prix) + ' = ' + this.f(this.prix * n) + (this.couleur.trim() ? '\nCouleur souhaitée : ' + this.couleur.trim() : '') + @js($finMessage);
                                  return 'https://wa.me/' + @js((string) $numeroWhatsapp) + '?text=' + encodeURIComponent(texte);
                              }
                          }">
                        @csrf
                        <input type="hidden" name="produit_id" value="{{ $produit->id }}">
                        {{-- Une seule quantité envoyée, quel que soit le sélecteur utilisé (barre du bas ou bloc ordinateur) --}}
                        <input type="hidden" name="quantite" :value="parseInt(qte) || 1" value="1">

                        <p class="titre-display chiffres text-[1.7rem] leading-none sm:text-[2.4rem]">
                            <span x-text="f(prix)">{{ number_format($formats->first()[1], 0, ',', ' ') }} F</span>
                            <span class="font-sans text-[1rem] font-normal text-encre-2" x-text="unites[choix]">{{ $formats->first()[2] }}</span>
                        </p>

                        <fieldset class="mt-4 sm:mt-7">
                            <legend class="etiquette">Format</legend>
                            <div @class(['grid gap-2', 'grid-cols-2' => $formats->count() > 1])>
                                @foreach($formats as $cle => [$libelle, $prix, $unite])
                                    <label class="choix min-h-[4rem] gap-3 !p-3 sm:min-h-[4.25rem] sm:gap-4 sm:!p-3.5">
                                        <input type="radio" name="type_commande" value="{{ $cle }}" x-model="choix" class="sr-only" @checked($loop->first)>
                                        <span class="min-w-0 flex-1">
                                            <span class="block font-medium leading-snug">{{ $libelle }}</span>
                                            <span class="chiffres block text-[0.88rem] text-encre-2 sm:text-[0.92rem]">{{ number_format($prix, 0, ',', ' ') }} F {{ $unite }}</span>
                                        </span>
                                        <span aria-hidden="true" class="hidden size-5 shrink-0 items-center justify-center border border-trait-fort bg-carte sm:flex" :class="choix === '{{ $cle }}' && '!border-brun !bg-brun text-white'">
                                            <x-icone nom="coche" class="size-3.5" x-show="choix === '{{ $cle }}'" />
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- Quantité et total : ordinateur seulement (téléphone et tablette : barre fixe en bas) --}}
                        <div class="mt-6 hidden grid-cols-[auto_1fr] items-end gap-4 lg:grid">
                            <div>
                                <label for="quantite" class="etiquette" x-text="choix === 'metre' ? 'Nombre de mètres' : 'Quantité'">Quantité</label>
                                <div class="flex h-13 items-stretch border border-trait-fort bg-carte">
                                    <button type="button" @click="moins()" class="flex w-12 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                        <x-icone nom="moins" class="size-4" /><span class="sr-only">Diminuer</span>
                                    </button>
                                    <input id="quantite" type="number" min="1" x-model="qte" value="1"
                                           class="chiffres w-16 border-0 bg-transparent text-center text-[1.05rem] font-semibold focus:ring-0">
                                    <button type="button" @click="plus()" class="flex w-12 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">
                                        <x-icone nom="plus" class="size-4" /><span class="sr-only">Augmenter</span>
                                    </button>
                                </div>
                            </div>
                            <div class="text-right" aria-live="polite">
                                <p class="text-[0.92rem] text-encre-2">Total</p>
                                <p class="titre-display chiffres text-[1.7rem] leading-tight text-brun" x-text="f(total)">{{ number_format($formats->first()[1], 0, ',', ' ') }} F</p>
                            </div>
                        </div>

                        {{-- Couleur voulue par le client : enregistrée avec l'article, visible par la boutique dans la commande --}}
                        @if($listeCouleurs->isNotEmpty())
                            {{-- Couleurs saisies dans l'admin : une pastille à toucher (re-toucher = annuler), « Autre » pour une demande libre --}}
                            <div class="mt-4 sm:mt-6" role="group" aria-labelledby="titre-couleurs">
                                <p id="titre-couleurs" class="etiquette">Couleur <span class="font-normal text-encre-2">(facultatif)</span></p>
                                <input type="hidden" name="couleur" :value="couleur">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($listeCouleurs as $nomCouleur)
                                        @php $teinte = teinte_couleur($nomCouleur); @endphp
                                        <button type="button" @click="choisirCouleur(@js($nomCouleur))" :aria-pressed="couleur === @js($nomCouleur)"
                                                class="flex min-h-11 items-center gap-2 border border-trait-fort bg-carte px-3 text-[0.95rem] transition-colors duration-150 hover:border-brun-trait active:scale-[0.97]"
                                                :class="couleur === @js($nomCouleur) && '!border-brun !bg-brun-doux font-medium text-brun-fonce shadow-[inset_0_0_0_1px_var(--brun)]'">
                                            @if($teinte)
                                                <span aria-hidden="true" class="size-4 shrink-0 border border-black/15" style="background: {{ $teinte }}"></span>
                                            @endif
                                            {{ $nomCouleur }}
                                        </button>
                                    @endforeach
                                    <button type="button" @click="ouvrirAutre()" :aria-expanded="autre" aria-controls="autre-couleur"
                                            class="flex min-h-11 items-center gap-1.5 border border-dashed border-trait-fort px-3 text-[0.95rem] text-encre-2 transition-colors duration-150 hover:border-brun-trait hover:text-encre"
                                            :class="autre && '!border-solid !border-brun text-brun-fonce'">
                                        <x-icone nom="plus" class="size-4" /> Autre
                                    </button>
                                </div>
                                <div id="autre-couleur" x-show="autre" x-cloak class="mt-3">
                                    <label for="couleur-libre" class="sr-only">Autre couleur souhaitée</label>
                                    <input id="couleur-libre" x-ref="autreCouleur" type="text" maxlength="100" x-model="couleur" autocomplete="off"
                                           class="champ" placeholder="Décrivez la couleur souhaitée">
                                </div>
                                <p class="aide hidden lg:block">La boutique la reçoit avec votre commande et dans le message WhatsApp.</p>
                                @error('couleur')<p class="erreur">{{ $message }}</p>@enderror
                            </div>
                        @else
                            <div class="mt-5">
                                <label for="couleur" class="etiquette">Couleur souhaitée <span class="font-normal text-encre-2">(facultatif)</span></label>
                                <input id="couleur" type="text" name="couleur" maxlength="100" x-model="couleur" autocomplete="off"
                                       class="champ" placeholder="Ex. : bleu nuit, comme sur la photo">
                                <p class="aide hidden lg:block">La boutique la reçoit avec votre commande et dans le message WhatsApp.</p>
                                @error('couleur')<p class="erreur">{{ $message }}</p>@enderror
                            </div>
                        @endif

                        {{-- Boutons : ordinateur --}}
                        <div class="hidden lg:block">
                            @auth
                                <button type="submit" class="btn btn-brun btn-lg mt-6 w-full"><x-icone nom="panier" /> Ajouter au panier</button>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-brun btn-lg mt-6 w-full">Se connecter pour commander</a>
                                <p class="mt-2 text-center text-[0.92rem] text-encre-2">Pas encore de compte ? <a href="{{ route('register') }}" class="lien">Créez-le en une minute</a>.</p>
                            @endauth

                            @if($lienWhatsappProduit)
                                {{-- Le message reprend le format, la quantité et le total choisis ci-dessus --}}
                                <a href="{{ $lienWhatsappProduit }}" :href="lienWhatsapp" target="_blank" rel="noopener noreferrer" class="btn btn-contour btn-lg mt-3 w-full"><x-icone nom="whatsapp" /> Discuter sur WhatsApp</a>
                            @endif
                        </div>

                        {{-- Téléphone et tablette : WhatsApp en lien discret --}}
                        @if($lienWhatsappProduit)
                            <a href="{{ $lienWhatsappProduit }}" :href="lienWhatsapp" target="_blank" rel="noopener noreferrer"
                               class="mt-4 inline-flex min-h-11 items-center gap-2 font-medium text-brun underline-offset-4 hover:underline lg:hidden">
                                <x-icone nom="whatsapp" class="size-5" /> Une question ? Discuter sur WhatsApp
                            </a>
                        @endif

                        {{-- BARRE D'ACHAT FIXE (téléphone et tablette), à la place de la barre d'accès rapide --}}
                        <div class="sans-impression fixed inset-x-0 bottom-0 z-sticky border-t border-trait bg-papier pb-[env(safe-area-inset-bottom)] lg:hidden">
                            <div class="conteneur flex items-center gap-3 py-2">
                                {{-- Tablette : rappel du tissu et du prix --}}
                                <div class="hidden min-w-0 flex-1 sm:block">
                                    <p class="truncate font-medium leading-snug">{{ $produit->nom }}</p>
                                    <p class="chiffres text-[0.92rem] text-encre-2"><span x-text="f(prix)"></span> <span x-text="unites[choix]"></span></p>
                                </div>

                                @auth
                                    <div class="flex h-12 shrink-0 items-stretch border border-trait-fort bg-carte">
                                        <button type="button" @click="moins()" class="flex w-10 items-center justify-center text-encre-2 active:bg-brun-doux">
                                            <x-icone nom="moins" class="size-4" /><span class="sr-only">Diminuer</span>
                                        </button>
                                        <label for="quantite-barre" class="sr-only" x-text="choix === 'metre' ? 'Nombre de mètres' : 'Quantité'">Quantité</label>
                                        <span class="flex items-center">
                                            <input id="quantite-barre" type="number" min="1" inputmode="numeric" x-model="qte"
                                                   class="chiffres w-9 border-0 bg-transparent p-0 text-right text-[1.05rem] font-semibold focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none">
                                            <span class="pl-0.5 pr-1 text-[0.85rem] text-encre-2" x-text="choix === 'metre' ? 'm' : ''">m</span>
                                        </span>
                                        <button type="button" @click="plus()" class="flex w-10 items-center justify-center text-encre-2 active:bg-brun-doux">
                                            <x-icone nom="plus" class="size-4" /><span class="sr-only">Augmenter</span>
                                        </button>
                                    </div>
                                    <button type="submit" class="btn btn-brun h-12 min-w-0 flex-1 px-4 sm:flex-none sm:px-8" aria-live="polite">
                                        <span class="truncate">Ajouter<span class="hidden sm:inline"> au panier</span>&nbsp;·&nbsp;<span class="chiffres" x-text="f(total)">{{ number_format($formats->first()[1], 0, ',', ' ') }} F</span></span>
                                    </button>
                                @else
                                    @if($lienWhatsappProduit)
                                        <a href="{{ $lienWhatsappProduit }}" :href="lienWhatsapp" target="_blank" rel="noopener noreferrer"
                                           class="btn btn-contour h-12 w-12 shrink-0 !px-0">
                                            <x-icone nom="whatsapp" class="size-5" /><span class="sr-only">Discuter sur WhatsApp</span>
                                        </a>
                                    @endif
                                    <a href="{{ route('login') }}" class="btn btn-brun h-12 flex-1 px-4 sm:flex-none sm:px-8">Se connecter pour commander</a>
                                @endauth
                            </div>
                        </div>
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

                {{-- Téléphone et tablette : les engagements de la boutique en une rangée d'icônes --}}
                <ul class="mt-5 grid grid-cols-4 gap-1 border-y border-trait py-4 text-center text-[0.8rem] leading-tight text-encre-2 lg:hidden">
                    @foreach($assurances as [$icone, $libelle])
                        <li class="flex flex-col items-center gap-1.5 px-0.5">
                            <x-icone :nom="$icone" class="size-5 text-brun" />
                            <span>{{ $libelle }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- Ordinateur : livraison, paiement, qualité en détail --}}
                <dl class="mt-8 hidden divide-y divide-trait border-y border-trait text-[0.95rem] lg:block">
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
    @if($nbAvis > 0)
        {{-- Au téléphone : titre sur une ligne et formulaire replié derrière un bouton --}}
        <section id="avis" class="mt-8 grid gap-4 bg-papier-2 p-4 sm:mt-20 sm:gap-10 sm:p-10 lg:mt-28 lg:grid-cols-12 lg:gap-16 lg:p-14" aria-labelledby="titre-avis">
            <div class="revele lg:col-span-4" x-data="{ formulaire: false }">
                <h2 class="titre-display text-[1.25rem] sm:hidden">Avis clients <span class="chiffres font-sans text-[0.95rem] font-normal text-encre-2">· {{ number_format($moyenne, 1, ',', ' ') }} sur 5 ({{ $nbAvis }})</span></h2>
                <div class="hidden sm:block"><x-titre-bloc intitule="Avis clients" titre="Ce qu'en disent nos clients" id="titre-avis" /></div>
                <p class="mt-6 hidden items-baseline gap-2 sm:flex">
                    <span class="titre-display chiffres text-[3rem] leading-none text-brun">{{ number_format($moyenne, 1, ',', ' ') }}</span>
                    <span class="text-encre-2">sur 5, d'après {{ $nbAvis }} avis</span>
                </p>

                @auth
                    <button type="button" @click="formulaire = !formulaire" :aria-expanded="formulaire" aria-controls="formulaire-avis"
                            class="btn btn-contour mt-3 w-full sm:hidden"><x-icone nom="etoile" /> <span x-text="formulaire ? 'Fermer' : 'Donner votre avis'">Donner votre avis</span></button>
                    @include('partials.formulaire-avis')
                @else
                    <p class="mt-2 text-[0.95rem] text-encre-2 sm:mt-8 sm:text-base"><a href="{{ route('login') }}" class="lien">Connectez-vous</a> pour donner votre avis sur ce tissu.</p>
                @endauth
            </div>

            <div class="lg:col-span-8">
                @foreach($produit->reviews as $avis)
                    <figure class="revele border-b border-trait py-4 first:pt-0 last:border-0 sm:py-6" style="--delai: {{ min($loop->index, 4) * 60 }}ms">
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
                @endforeach
            </div>
        </section>
    @else
        {{-- Pas encore d'avis : pas de bloc vide, une invitation courte et le formulaire --}}
        <section id="avis" class="mt-6 sm:mt-20 sm:border-t sm:border-trait sm:pt-10 lg:mt-28 lg:grid lg:grid-cols-12 lg:gap-16" aria-labelledby="titre-avis" x-data="{ formulaire: false }">
            <div class="lg:col-span-5">
                <p class="intitule hidden sm:block">Avis clients</p>
                <h2 id="titre-avis" class="titre-display text-[1.25rem] sm:mt-2 sm:text-[2.3rem] lg:text-[2.6rem]">Soyez le premier à donner votre avis</h2>
                <p class="mt-1.5 text-[0.95rem] text-encre-2 sm:mt-3 sm:text-base">Vous avez cousu ce tissu ? Votre retour aidera les prochains clients à choisir.</p>
                @auth
                    <button type="button" @click="formulaire = !formulaire" :aria-expanded="formulaire" aria-controls="formulaire-avis"
                            class="btn btn-contour mt-4 w-full sm:hidden"><x-icone nom="etoile" /> <span x-text="formulaire ? 'Fermer' : 'Donner votre avis'">Donner votre avis</span></button>
                @else
                    <p class="mt-3 text-[0.95rem] sm:mt-6 sm:text-base"><a href="{{ route('login') }}" class="lien">Connectez-vous</a> pour donner votre avis.</p>
                @endauth
            </div>
            @auth
                <div class="sm:max-w-xl lg:col-span-7 lg:max-w-none">
                    @include('partials.formulaire-avis', ['classe' => 'lg:mt-0'])
                </div>
            @endauth
        </section>
    @endif

    {{-- DANS LA MÊME FAMILLE --}}
    @php $similaires = ($produitsSimilaires ?? collect())->where('id', '!=', $produit->id)->take(4); @endphp
    @if($similaires->isNotEmpty())
        <section class="mt-12 sm:mt-20 lg:mt-28" aria-labelledby="titre-similaires">
            <div class="revele flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <x-titre-bloc intitule="Dans la même famille" titre="Vous aimerez aussi" id="titre-similaires" />
                @if($produit->categorie)
                    <a href="{{ $lienFamille }}" class="lien-fleche shrink-0">Tout {{ $produit->categorie->nom }} <x-icone nom="fleche" /></a>
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
