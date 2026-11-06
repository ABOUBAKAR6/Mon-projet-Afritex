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
            @if(config('services.whatsapp.numero'))
                <a href="https://wa.me/{{ config('services.whatsapp.numero') }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun mt-6"><x-icone nom="message" /> Écrire sur WhatsApp</a>
            @endif
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

        <form method="POST" action="{{ route('pro.commander.envoyer') }}" x-data="commandePro(@js(old('mode_livraison', 'domicile')), @js($tousLesNoms))" x-init="recalculer()" @input="recalculer()"
              class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_22rem] lg:gap-10">
            @csrf

            <div class="min-w-0">
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
                    <p class="erreur mt-4 flex items-center gap-2 bg-danger-doux p-4" role="alert"><x-icone nom="alerte" class="size-4 shrink-0" /> {{ $message }}</p>
                @enderror

                @foreach($categories as $categorie)
                    @php $noms = $categorie->produits->map(fn ($p) => mb_strtolower($p->nom))->values(); @endphp
                    <section class="mt-8 first-of-type:mt-6" x-show="familleVisible(@js($noms))" aria-labelledby="famille-{{ $categorie->id }}">
                        <h2 id="famille-{{ $categorie->id }}" class="titre-display scroll-mt-28 text-[1.4rem]">{{ $categorie->nom }}</h2>
                        <ul class="mt-3 divide-y divide-trait bg-carte">
                            @foreach($categorie->produits as $produit)
                                <li class="grid gap-4 p-4 sm:p-5 md:grid-cols-[minmax(0,14rem)_1fr] md:gap-6" x-show="visible(@js(mb_strtolower($produit->nom)))">
                                    {{-- La photo s'agrandit sur place : le revendeur reste dans l'Espace Pro, sans passer par la fiche publique --}}
                                    @php $photo = $produit->image && file_exists(public_path($produit->image)) ? asset($produit->image) : null; @endphp
                                    <div class="flex items-center gap-3 md:items-start">
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
                                            <span class="size-14 shrink-0 bg-papier-2 md:size-16"></span>
                                        @endif
                                        <span class="min-w-0 font-medium leading-snug">{{ $produit->nom }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-[repeat(auto-fill,minmax(10rem,1fr))]">
                                        @foreach($produit->formatsPro() as $cle => $format)
                                            @php $id = 'q-' . $produit->id . '-' . $cle; @endphp
                                            <div data-cellule class="border border-trait p-3 transition-colors">
                                                <label for="{{ $id }}" class="block text-[0.85rem] font-medium text-encre-2">{{ $format['libelle'] }}</label>
                                                <p class="chiffres mt-0.5 font-semibold text-brun">{{ $francs($format['pro']) }} <span class="text-[0.8rem] font-normal text-encre-2">{{ $format['unite'] }}</span></p>
                                                @if($format['detail'] && $format['detail'] > $format['pro'])
                                                    <p class="chiffres text-[0.8rem] text-encre-2">Prix public {{ $francs($format['detail']) }}</p>
                                                @endif
                                                <input id="{{ $id }}" type="number" name="quantites[{{ $produit->id }}][{{ $cle }}]"
                                                       value="{{ $quantites[$produit->id][$cle] ?? '' }}" data-prix="{{ $format['pro'] }}"
                                                       min="0" max="9999" step="1" inputmode="numeric" placeholder="0"
                                                       class="champ chiffres mt-2 min-h-10 text-right">
                                            </div>
                                        @endforeach
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endforeach

                <p x-show="aucunResultat()" x-cloak class="mt-8 bg-carte p-8 text-center text-encre-2">Aucun tissu ne correspond à « <span x-text="recherche"></span> ».</p>

                {{-- Téléphone : le total reste sous les yeux pendant la saisie, au-dessus de la barre d'accès rapide --}}
                <div class="sticky bottom-[calc(4.5rem+env(safe-area-inset-bottom))] z-dropdown mt-6 flex items-center justify-between gap-4 bg-encre px-4 py-3 text-papier shadow-[0_18px_40px_-20px_oklch(0.2_0.03_var(--teinte-ombre)/0.6)] lg:hidden">
                    <p class="leading-tight">
                        <span class="block text-[0.8rem] text-papier/75" x-text="lignes + (lignes > 1 ? ' lignes' : ' ligne')">0 ligne</span>
                        <span class="titre-display chiffres text-[1.15rem]" x-text="francs(total)">0 F</span>
                    </p>
                    <a href="#recapitulatif" class="btn btn-or btn-sm">Finaliser</a>
                </div>
            </div>

            {{-- Récapitulatif et livraison --}}
            <aside id="recapitulatif" class="scroll-mt-28 space-y-4 lg:sticky lg:top-28">
                <section class="bg-carte p-6" aria-labelledby="titre-recap">
                    <h2 id="titre-recap" class="titre-display text-[1.25rem]">Votre demande</h2>
                    <dl class="mt-4 space-y-2" aria-live="polite">
                        <div class="flex justify-between gap-4 text-[0.95rem]">
                            <dt class="text-encre-2">Lignes</dt>
                            <dd class="chiffres" x-text="lignes">0</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-4 border-t border-trait pt-3">
                            <dt class="font-medium">Total indicatif</dt>
                            <dd class="titre-display chiffres text-[1.6rem] text-brun" x-text="francs(total)">0 F</dd>
                        </div>
                    </dl>
                    <p class="aide">Aux prix pro, hors livraison. Le devis de la boutique fait foi.</p>
                </section>

                <section class="space-y-5 bg-carte p-6" aria-labelledby="titre-livraison">
                    <h2 id="titre-livraison" class="titre-display text-[1.25rem]">Livraison</h2>

                    <fieldset>
                        <legend class="sr-only">Mode de livraison</legend>
                        <div class="grid gap-2">
                            <label class="choix py-3">
                                <input type="radio" name="mode_livraison" value="domicile" x-model="mode" class="size-4 accent-[var(--brun)]">
                                <span class="flex items-center gap-2 text-[0.95rem]"><x-icone nom="livraison" class="size-4 text-brun" /> Livraison à mon adresse</span>
                            </label>
                            <label class="choix py-3">
                                <input type="radio" name="mode_livraison" value="boutique" x-model="mode" class="size-4 accent-[var(--brun)]">
                                <span class="flex items-center gap-2 text-[0.95rem]"><x-icone nom="boutique" class="size-4 text-brun" /> Retrait en boutique</span>
                            </label>
                        </div>
                        @error('mode_livraison')<p class="erreur">{{ $message }}</p>@enderror
                    </fieldset>

                    <div x-show="mode === 'domicile'">
                        <label for="adresse_livraison" class="etiquette">Adresse de livraison</label>
                        <textarea id="adresse_livraison" name="adresse_livraison" rows="3" maxlength="500" class="champ min-h-0" :required="mode === 'domicile'"
                                  placeholder="Ville, commune, quartier, repère" @error('adresse_livraison') aria-invalid="true" aria-describedby="erreur-adresse" @enderror>{{ old('adresse_livraison', $adresseParDefaut) }}</textarea>
                        @error('adresse_livraison')<p id="erreur-adresse" class="erreur">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="livraison_souhaitee_le" class="etiquette">Date souhaitée <span class="font-normal text-encre-2">(facultatif)</span></label>
                        <input id="livraison_souhaitee_le" type="date" name="livraison_souhaitee_le" value="{{ old('livraison_souhaitee_le') }}" min="{{ today()->toDateString() }}" class="champ chiffres" @error('livraison_souhaitee_le') aria-invalid="true" aria-describedby="erreur-date" @enderror>
                        @error('livraison_souhaitee_le')<p id="erreur-date" class="erreur">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="note_client" class="etiquette">Note pour la boutique <span class="font-normal text-encre-2">(facultatif)</span></label>
                        <textarea id="note_client" name="note_client" rows="3" maxlength="1000" class="champ min-h-0" placeholder="Coloris précis, découpe, créneau de livraison…">{{ old('note_client') }}</textarea>
                        @error('note_client')<p class="erreur">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <button type="submit" class="btn btn-brun btn-lg w-full">Demander le devis</button>
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
    // Tableau de commande pro : total indicatif en direct, filtre par nom, mise en avant des cases remplies
    function commandePro(modeInitial, noms) {
        return {
            recherche: '',
            mode: modeInitial,
            photo: { src: '', nom: '' },
            agrandir(src, nom) {
                this.photo = { src, nom };
                this.$refs.fenetrePhoto.showModal();
            },
            lignes: 0,
            total: 0,
            recalculer() {
                let lignes = 0;
                let total = 0;
                this.$root.querySelectorAll('input[data-prix]').forEach((champ) => {
                    const quantite = Math.min(9999, Math.max(0, parseInt(champ.value, 10) || 0));
                    if (quantite > 0) {
                        lignes++;
                        total += quantite * Number(champ.dataset.prix);
                    }
                    const cellule = champ.closest('[data-cellule]');
                    cellule.classList.toggle('border-brun', quantite > 0);
                    cellule.classList.toggle('bg-brun-doux', quantite > 0);
                    cellule.classList.toggle('border-trait', quantite === 0);
                });
                this.lignes = lignes;
                this.total = total;
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
