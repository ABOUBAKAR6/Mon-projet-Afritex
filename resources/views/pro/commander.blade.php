@extends('layouts.app')
@section('title', 'Commander en gros · ' . boutique('nom'))
@section('content')
@php
    $francs = fn ($montant) => number_format((float) $montant, 0, ',', ' ') . ' F';
    // Dernière adresse de livraison du client, sans les coordonnées GPS ajoutées par la carte du panier
    $adresseParDefaut = collect(explode(' | ', (string) $derniereAdresse))
        ->reject(fn ($partie) => str_starts_with($partie, 'GPS:'))
        ->filter()
        ->join(', ');
    $tousLesNoms = $categories->flatMap(fn ($c) => $c->produits->map(fn ($p) => mb_strtolower($p->nom)))->values();
    $waBoutique = lien_whatsapp_boutique();
    $nomRevendeur = $compte->boutique ?? auth()->user()?->name ?? '';
@endphp

@include('pro.partials.entete')

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-12">
    <div class="apparition">
        <h1 class="titre-display text-[2.2rem] sm:text-[2.6rem]">Commander en gros</h1>
        <p class="mt-1 max-w-[68ch] text-encre-2">Saisissez vos quantités aux prix pro. La boutique confirme le stock, la livraison et la date dans un devis : vous ne payez qu'après l'avoir accepté.</p>
    </div>

    @if($categories->isEmpty())
        <div class="apparition mt-8 bg-carte p-8 sm:p-12" style="animation-delay: 80ms">
            <p class="titre-display text-[1.5rem]">Les tarifs pro arrivent</p>
            <p class="mt-2 max-w-[56ch] text-encre-2">La boutique n'a pas encore mis en ligne ses prix revendeurs. En attendant, écrivez-nous pour une commande en gros.</p>
            <a href="{{ $waBoutique }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun mt-6"><x-icone nom="message" /> Écrire sur WhatsApp</a>
        </div>
    @else
        @if($source)
            <div class="apparition mt-8 flex flex-col gap-3 bg-brun-doux p-5 sm:flex-row sm:items-center sm:justify-between" style="animation-delay: 60ms">
                <p class="flex items-start gap-3">
                    <x-icone nom="coche" class="mt-1 size-4 shrink-0 text-brun" />
                    <span>Quantités reprises de la commande n° <span class="chiffres">{{ str_pad($source->id, 4, '0', STR_PAD_LEFT) }}</span>. Ajustez-les si besoin.</span>
                </p>
                <a href="{{ route('pro.commander') }}" class="lien shrink-0 text-[0.95rem]">Repartir d'une commande vide</a>
            </div>
        @endif

        <form method="POST" action="{{ route('pro.commander.envoyer') }}"
              x-data="commandePro(@js(old('mode_livraison', 'domicile')), @js($tousLesNoms), @js($waBoutique), @js(boutique('nom')), @js($nomRevendeur), @js(old('adresse_livraison', $adresseParDefaut)), @js(old('livraison_souhaitee_le', '')), @js(old('note_client', '')))"
              x-init="recalculer()" @input="recalculer()"
              class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_24rem] lg:gap-10">
            @csrf

            <div class="min-w-0 space-y-6">
                {{-- Recherche et accès direct aux familles --}}
                <div class="flex flex-col gap-3 bg-carte p-4 sm:flex-row sm:items-center">
                    <label for="recherche-pro" class="sr-only">Chercher un tissu</label>
                    <div class="relative w-full sm:max-w-xs">
                        <x-icone nom="recherche" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-encre-3" />
                        <input id="recherche-pro" type="search" x-model="recherche" class="champ pl-10" placeholder="Chercher un tissu" autocomplete="off">
                    </div>
                    @if($categories->count() > 1)
                        <nav aria-label="Familles de tissus" class="defilement-discret -mx-4 flex gap-1.5 overflow-x-auto px-4 sm:mx-0 sm:flex-wrap sm:overflow-visible sm:px-0">
                            @foreach($categories as $categorie)
                                <a href="#famille-{{ $categorie->id }}" class="shrink-0 whitespace-nowrap bg-papier-2 px-3 py-2 text-[0.9rem] text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce">{{ $categorie->nom }}</a>
                            @endforeach
                        </nav>
                    @endif
                </div>

                @error('quantites')
                    <p class="erreur flex items-center gap-2 bg-danger-doux p-4" role="alert"><x-icone nom="alerte" class="size-4 shrink-0" /> {{ $message }}</p>
                @enderror

                @foreach($categories as $categorie)
                    @php $noms = $categorie->produits->map(fn ($p) => mb_strtolower($p->nom))->values(); @endphp
                    <section x-show="familleVisible(@js($noms))" aria-labelledby="famille-{{ $categorie->id }}">
                        <div class="flex items-baseline justify-between gap-4">
                            <h2 id="famille-{{ $categorie->id }}" class="titre-display scroll-mt-28 text-[1.4rem]">{{ $categorie->nom }}</h2>
                            <span class="text-[0.85rem] text-encre-2">{{ $categorie->produits->count() }} tissu{{ $categorie->produits->count() > 1 ? 's' : '' }}</span>
                        </div>
                        <ul class="mt-3 divide-y divide-trait bg-carte">
                            @foreach($categorie->produits as $produit)
                                <li class="grid gap-4 p-4 sm:p-5 md:grid-cols-[minmax(0,14rem)_1fr] md:items-center md:gap-6" x-show="visible(@js(mb_strtolower($produit->nom)))">
                                    {{-- La photo s'agrandit sur place : le revendeur reste dans l'Espace Pro, sans passer par la fiche publique --}}
                                    @php $photo = $produit->image && file_exists(public_path($produit->image)) ? asset($produit->image) : null; @endphp
                                    <div class="flex items-center gap-3.5">
                                        @if($photo)
                                            <button type="button" @click="agrandir(@js($photo), @js($produit->nom))"
                                                    class="group relative size-14 shrink-0 overflow-hidden bg-papier-2 md:size-16">
                                                <img src="{{ $photo }}" alt="" loading="lazy" class="h-full w-full object-cover transition-transform duration-200 group-hover:scale-105">
                                                <span class="absolute inset-0 flex items-center justify-center bg-[oklch(0.2_0.03_var(--teinte-ombre)/0.45)] text-white opacity-0 transition-opacity group-hover:opacity-100 group-focus-visible:opacity-100">
                                                    <x-icone nom="oeil" class="size-5" />
                                                </span>
                                                <span class="sr-only">Agrandir la photo de {{ $produit->nom }}</span>
                                            </button>
                                        @else
                                            <span class="flex size-14 shrink-0 items-center justify-center bg-papier-2 text-brun-trait md:size-16">
                                                <x-icone nom="image" class="size-5" />
                                            </span>
                                        @endif
                                        <span class="min-w-0">
                                            <span class="block font-medium leading-snug">{{ $produit->nom }}</span>
                                            <span class="mt-0.5 block text-[0.82rem] text-encre-2">{{ $categorie->nom }}</span>
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2.5 sm:grid-cols-[repeat(auto-fill,minmax(10.5rem,1fr))]">
                                        @foreach($produit->formatsPro() as $cle => $format)
                                            @php $id = 'q-' . $produit->id . '-' . $cle; @endphp
                                            <div data-cellule class="flex flex-col justify-between border border-trait p-3.5 transition-colors">
                                                <div>
                                                    <label for="{{ $id }}" class="block text-[0.85rem] font-medium text-encre-2">{{ $format['libelle'] }}</label>
                                                    <p class="chiffres mt-0.5 font-semibold text-brun">{{ $francs($format['pro']) }} <span class="text-[0.8rem] font-normal text-encre-2">{{ $format['unite'] }}</span></p>
                                                    @if($format['detail'] && $format['detail'] > $format['pro'])
                                                        <p class="chiffres text-[0.8rem] text-encre-2">Prix public {{ $francs($format['detail']) }}</p>
                                                    @endif
                                                </div>
                                                <input id="{{ $id }}" type="number" name="quantites[{{ $produit->id }}][{{ $cle }}]"
                                                       value="{{ $quantites[$produit->id][$cle] ?? '' }}" data-prix="{{ $format['pro'] }}"
                                                       data-produit="{{ $produit->nom }}" data-categorie="{{ $categorie->nom }}"
                                                       data-format="{{ $format['libelle'] }}" data-cle="{{ $cle }}"
                                                       min="0" max="9999" step="1" inputmode="numeric" placeholder="0"
                                                       class="champ chiffres mt-3 min-h-10 text-right">
                                            </div>
                                        @endforeach
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach

                <p x-show="aucunResultat()" x-cloak class="bg-carte p-8 text-center text-encre-2">Aucun tissu ne correspond à « <span x-text="recherche"></span> ».</p>

                {{-- Livraison placée sous les tissus : équilibre la page et garde le récapitulatif compact à droite --}}
                <section class="bg-carte p-6" aria-labelledby="titre-livraison">
                    <h2 id="titre-livraison" class="titre-display text-[1.25rem]">Livraison</h2>

                    <fieldset class="mt-4">
                        <legend class="sr-only">Mode de livraison</legend>
                        <div class="grid gap-2.5 sm:grid-cols-2">
                            <label class="choix py-3" :class="mode === 'domicile' && 'est-choisi'">
                                <input type="radio" name="mode_livraison" value="domicile" x-model="mode" class="size-4 accent-[var(--brun)]">
                                <span class="flex items-center gap-2 text-[0.95rem]"><x-icone nom="livraison" class="size-4 text-brun" /> Livraison à mon adresse</span>
                            </label>
                            <label class="choix py-3" :class="mode === 'boutique' && 'est-choisi'">
                                <input type="radio" name="mode_livraison" value="boutique" x-model="mode" class="size-4 accent-[var(--brun)]">
                                <span class="flex items-center gap-2 text-[0.95rem]"><x-icone nom="boutique" class="size-4 text-brun" /> Retrait en boutique</span>
                            </label>
                        </div>
                        @error('mode_livraison')<p class="erreur">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div x-show="mode === 'domicile'" class="sm:col-span-2">
                            <label for="adresse_livraison" class="etiquette">Adresse de livraison</label>
                            <textarea id="adresse_livraison" name="adresse_livraison" rows="2" maxlength="500" x-model="adresse" class="champ min-h-0" :required="mode === 'domicile'"
                                      placeholder="Ville, commune, quartier, repère" @error('adresse_livraison') aria-invalid="true" aria-describedby="erreur-adresse" @enderror>{{ old('adresse_livraison', $adresseParDefaut) }}</textarea>
                            @error('adresse_livraison')<p id="erreur-adresse" class="erreur">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="livraison_souhaitee_le" class="etiquette">Date souhaitée <span class="font-normal text-encre-2">(facultatif)</span></label>
                            <input id="livraison_souhaitee_le" type="date" name="livraison_souhaitee_le" x-model="dateSouhaitee" value="{{ old('livraison_souhaitee_le') }}" min="{{ today()->toDateString() }}" class="champ chiffres" @error('livraison_souhaitee_le') aria-invalid="true" aria-describedby="erreur-date" @enderror>
                            @error('livraison_souhaitee_le')<p id="erreur-date" class="erreur">{{ $message }}</p>@enderror
                        </div>

                        <div :class="mode === 'boutique' && 'sm:col-span-1'">
                            <label for="note_client" class="etiquette">Note pour la boutique <span class="font-normal text-encre-2">(facultatif)</span></label>
                            <textarea id="note_client" name="note_client" rows="2" maxlength="1000" x-model="note" class="champ min-h-0" placeholder="Coloris précis, découpe, créneau…">{{ old('note_client') }}</textarea>
                            @error('note_client')<p class="erreur">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- Téléphone : le total reste sous les yeux pendant la saisie, au-dessus de la barre d'accès rapide --}}
                <div class="sticky bottom-[calc(4.5rem+env(safe-area-inset-bottom))] z-dropdown flex items-center justify-between gap-4 bg-encre px-4 py-3 text-papier shadow-[0_18px_40px_-20px_oklch(0.2_0.03_var(--teinte-ombre)/0.6)] lg:hidden">
                    <p class="leading-tight">
                        <span class="block text-[0.8rem] text-papier/75" x-text="lignes + (lignes > 1 ? ' lignes' : ' ligne')">0 ligne</span>
                        <span class="titre-display chiffres text-[1.15rem]" x-text="francs(total)">0 F</span>
                    </p>
                    <a href="#recapitulatif" class="btn btn-or btn-sm">Finaliser</a>
                </div>
            </div>

            {{-- Récapitulatif détaillé et envoi (devis ou WhatsApp du propriétaire) --}}
            <aside id="recapitulatif" class="min-w-0 scroll-mt-28 space-y-4 lg:sticky lg:top-28">
                <section class="bg-carte p-6" aria-labelledby="titre-recap">
                    <h2 id="titre-recap" class="titre-display text-[1.25rem]">Votre demande</h2>

                    <p x-show="articles.length === 0" class="mt-3 border-y border-trait py-4 text-[0.9rem] text-encre-2">
                        Saisissez vos quantités à gauche pour afficher le détail par produit et catégorie.
                    </p>

                    <ul x-show="articles.length > 0" x-cloak class="mt-4 max-h-64 divide-y divide-trait overflow-y-auto border-y border-trait" aria-label="Tissus sélectionnés">
                        <template x-for="art in articles" :key="art.id">
                            <li class="py-2.5 text-[0.9rem]">
                                <div class="flex items-baseline justify-between gap-3">
                                    <span class="font-medium text-encre" x-text="art.produit"></span>
                                    <span class="chiffres shrink-0 font-semibold text-encre" x-text="francs(art.sousTotal)"></span>
                                </div>
                                <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 text-[0.82rem] text-encre-2">
                                    <span x-text="art.categorie"></span>
                                    <span aria-hidden="true">·</span>
                                    <span class="font-medium text-brun" x-text="art.format"></span>
                                    <span aria-hidden="true">:</span>
                                    <span class="chiffres" x-text="art.uniteQte + ' × ' + francs(art.prix)"></span>
                                </p>
                            </li>
                        </template>
                    </ul>

                    <dl class="mt-4 space-y-2" aria-live="polite">
                        <div class="flex justify-between gap-4 text-[0.92rem]">
                            <dt class="text-encre-2">Lignes</dt>
                            <dd class="chiffres font-medium" x-text="lignes">0</dd>
                        </div>
                        <div class="flex justify-between gap-4 text-[0.92rem]">
                            <dt class="text-encre-2">Livraison</dt>
                            <dd class="truncate text-right font-medium" x-text="mode === 'boutique' ? 'Retrait en boutique' : (adresse.trim() || 'À mon adresse')"></dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 border-t border-trait pt-3">
                            <dt class="font-medium">Total indicatif</dt>
                            <dd class="titre-display chiffres text-[1.6rem] text-brun" x-text="francs(total)">0 F</dd>
                        </div>
                    </dl>
                    <p class="aide mt-2">Aux prix pro, hors livraison. Le devis de la boutique fait foi.</p>

                    <div class="mt-5 space-y-2.5">
                        <button type="submit" class="btn btn-brun btn-lg w-full">Demander le devis</button>
                        <button type="submit" name="partager" value="whatsapp" class="btn btn-contour w-full">
                            <x-icone nom="message" /> Demander + WhatsApp
                        </button>
                        <a x-show="lignes > 0" x-cloak :href="lienWhatsapp()" target="_blank" rel="noopener noreferrer"
                           class="btn btn-fantome w-full text-[0.92rem]">
                            <x-icone nom="message" /> Envoyer directement sur WhatsApp
                        </a>
                        <p class="aide text-center" x-show="lignes === 0">Saisissez au moins une quantité.</p>
                    </div>
                </section>
            </aside>

            {{-- Photo agrandie d'un tissu, sans quitter la commande --}}
            <dialog x-ref="fenetrePhoto" @click.self="$el.close()" aria-labelledby="titre-photo"
                    class="m-auto w-[min(40rem,calc(100vw-2rem))] bg-carte p-0 text-encre shadow-[0_24px_60px_-24px_oklch(0.2_0.03_var(--teinte-ombre)/0.5)] backdrop:bg-[oklch(0.2_0.03_var(--teinte-ombre)/0.7)]">
                <div class="flex items-center justify-between gap-4 px-5 py-3">
                    <h2 id="titre-photo" class="titre-display text-[1.15rem]" x-text="photo.nom"></h2>
                    <button type="button" @click="$refs.fenetrePhoto.close()" class="-mr-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2">
                        <x-icone nom="fermer" /><span class="sr-only">Fermer la photo</span>
                    </button>
                </div>
                <img :src="photo.src" :alt="photo.nom" class="max-h-[75svh] w-full bg-papier-2 object-contain">
            </dialog>
        </form>
    @endif
</div>

@endsection

@push('scripts')
<script>
    // Tableau de commande pro : détail des lignes, total indicatif en direct, et message WhatsApp complet
    function commandePro(modeInitial, noms, waBase, boutiqueNom, revendeurNom, adresseInitiale, dateInitiale, noteInitiale) {
        return {
            recherche: '',
            mode: modeInitial,
            adresse: adresseInitiale || '',
            dateSouhaitee: dateInitiale || '',
            note: noteInitiale || '',
            photo: { src: '', nom: '' },
            agrandir(src, nom) {
                this.photo = { src, nom };
                this.$refs.fenetrePhoto.showModal();
            },
            articles: [],
            lignes: 0,
            total: 0,
            quantiteUnite(cle, qte) {
                if (cle === 'metre') return `${qte} m`;
                if (cle === 'rouleau') return `${qte} rouleau${qte > 1 ? 'x' : ''}`;
                return `${qte} pièce${qte > 1 ? 's' : ''}`;
            },
            recalculer() {
                let lignes = 0;
                let total = 0;
                const articles = [];
                this.$root.querySelectorAll('input[data-prix]').forEach((champ) => {
                    const quantite = Math.min(9999, Math.max(0, parseInt(champ.value, 10) || 0));
                    const prix = Number(champ.dataset.prix);
                    if (quantite > 0) {
                        lignes++;
                        const sousTotal = quantite * prix;
                        total += sousTotal;
                        articles.push({
                            id: champ.id,
                            produit: champ.dataset.produit || '',
                            categorie: champ.dataset.categorie || '',
                            format: champ.dataset.format || '',
                            cle: champ.dataset.cle || '',
                            quantite,
                            uniteQte: this.quantiteUnite(champ.dataset.cle, quantite),
                            prix,
                            sousTotal,
                        });
                    }
                    const cellule = champ.closest('[data-cellule]');
                    cellule.classList.toggle('border-brun', quantite > 0);
                    cellule.classList.toggle('bg-brun-doux', quantite > 0);
                    cellule.classList.toggle('border-trait', quantite === 0);
                });
                this.articles = articles;
                this.lignes = lignes;
                this.total = total;
            },
            texteWhatsapp() {
                const lignes = [
                    `Bonjour ${boutiqueNom},`,
                    '',
                    `Voici ma demande de devis${revendeurNom ? ' (' + revendeurNom + ')' : ''} :`,
                    '',
                ];
                this.articles.forEach((art) => {
                    lignes.push(`*${art.produit}*${art.categorie ? ' (Catégorie : ' + art.categorie + ')' : ''}`);
                    lignes.push(`${art.format} : ${art.uniteQte} × ${this.francs(art.prix)} = ${this.francs(art.sousTotal)}`);
                    lignes.push('');
                });
                lignes.push(`*Total indicatif : ${this.francs(this.total)}*`);
                lignes.push('');
                lignes.push(this.mode === 'boutique'
                    ? 'Retrait en boutique'
                    : `Livraison : ${this.adresse.trim() || 'adresse à préciser'}`);
                if (this.dateSouhaitee) {
                    lignes.push(`Date souhaitée : ${this.dateSouhaitee}`);
                }
                if (this.note.trim()) {
                    lignes.push('');
                    lignes.push(`Ma note : ${this.note.trim()}`);
                }
                lignes.push('');
                lignes.push('Merci !');
                return lignes.join('\n');
            },
            lienWhatsapp() {
                if (this.lignes === 0) return '#recapitulatif';
                return `${waBase}?text=${encodeURIComponent(this.texteWhatsapp())}`;
            },
            terme() {
                return this.recherche.trim().toLowerCase();
            },
            visible(nom) {
                return !this.terme() || nom.includes(this.terme());
            },
            familleVisible(noms) {
                return noms.some((nom) => this.visible(nom));
            },
            aucunResultat() {
                return this.terme() !== '' && !this.familleVisible(noms);
            },
            francs(montant) {
                return new Intl.NumberFormat('fr-FR').format(montant).replace(/[  ]/g, ' ') + ' F';
            },
        };
    }
</script>
@endpush
