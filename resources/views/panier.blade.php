@extends('layouts.app')
@section('title', 'Mon panier · ' . boutique('nom'))
{{-- Téléphone et tablette : la barre « Total · Commander » remplace la barre Catalogue / Panier / Connexion --}}
@if(!empty($panier))
    @section('sans_acces_rapides', '1')
@endif
@section('content')
@php
    $formats = [
        'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
        'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
    ];
    // Message WhatsApp du panier pour la boutique ($debutMessage, construit par CommandeController) :
    // remis à jour quand une quantité change, les remarques s'ajoutent en direct
    $numeroWhatsapp = config('services.whatsapp.numero');
@endphp

<div class="conteneur pb-20 pt-6 sm:pt-10 lg:pb-28 lg:pt-14">
    <h1 class="titre-display apparition text-[2rem] sm:text-[2.8rem]">Mon panier</h1>

    @if(empty($panier))
        <div class="apparition mt-8 grid items-center gap-8 bg-carte p-8 sm:p-12 lg:grid-cols-[1fr_auto]" style="animation-delay: 80ms">
            <div>
                <p class="titre-display text-[1.5rem]">Votre panier est vide</p>
                <p class="mt-2 max-w-[52ch] text-encre-2">Parcourez nos familles de tissus, choisissez un format (au mètre ou au rouleau) et ajoutez-le ici.</p>
            </div>
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Voir le catalogue <x-icone nom="fleche" /></a>
        </div>
    @else
        <p class="apparition mt-1 text-encre-2 sm:mt-2" style="animation-delay: 60ms">{{ count($panier) }} {{ count($panier) > 1 ? 'articles' : 'article' }}</p>

        {{-- Totaux et message WhatsApp tenus à jour quand une ligne change de quantité (événement « panier-maj ») --}}
        <div class="mt-6 grid items-start gap-6 sm:mt-8 sm:gap-8 lg:grid-cols-[1fr_24rem] lg:gap-12"
             x-data="{
                 sousTotal: {{ (float) $subtotal }},
                 remise: {{ (float) $discount }},
                 total: {{ (float) $total }},
                 message: @js($debutMessage),
                 erreur: '',
                 f(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)).replace(/\s/g, ' ') + ' F' },
             }"
             @panier-maj="sousTotal = $event.detail.sous_total; remise = $event.detail.remise; total = $event.detail.total; message = $event.detail.message; erreur = ''"
             @panier-erreur="erreur = 'Quantité non enregistrée : vérifiez votre connexion puis réessayez.'">

            {{-- ARTICLES --}}
            <div class="min-w-0">
                <ul class="divide-y divide-trait bg-carte">
                    @foreach($panier as $index => $item)
                        @php
                            $idLigne = $item['id'] ?? $index;
                            $prixUnitaire = $item['prix_unitaire'] ?? ($item['sous_total'] / max(1, $item['quantite']));
                            $auMetre = $item['type_commande'] === 'metre';
                        @endphp
                        <li class="revele grid grid-cols-[4.5rem_1fr] gap-x-3 gap-y-3 p-4 sm:grid-cols-[6.5rem_1fr_auto] sm:items-center sm:gap-6 sm:p-5" style="--delai: {{ min($index, 5) * 60 }}ms"
                            x-data="{
                                qte: {{ (int) $item['quantite'] }},
                                ligne: {{ (float) $item['sous_total'] }},
                                envoi: false,
                                minuterie: null,
                                changer(pas) { this.qte = Math.max(1, Math.min(10000, (parseInt(this.qte) || 1) + pas)); this.ligne = this.qte * {{ (float) $prixUnitaire }}; this.planifier() },
                                planifier() { clearTimeout(this.minuterie); this.minuterie = setTimeout(() => this.envoyer(), 450) },
                                async envoyer() {
                                    this.envoi = true;
                                    try {
                                        const r = await fetch(@js(route('panier.quantite', $idLigne)), {
                                            method: 'PATCH',
                                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) },
                                            body: JSON.stringify({ quantite: parseInt(this.qte) || 1 }),
                                        });
                                        if (!r.ok) throw new Error(r.status);
                                        const d = await r.json();
                                        this.ligne = d.ligne;
                                        this.$dispatch('panier-maj', d);
                                    } catch (e) {
                                        this.$dispatch('panier-erreur');
                                    } finally {
                                        this.envoi = false;
                                    }
                                },
                            }">
                            <div class="aspect-square overflow-hidden bg-papier-2">
                                @if(!empty($item['image']) && file_exists(public_path($item['image'])))
                                    <img src="{{ asset($item['image']) }}" alt="" class="h-full w-full object-cover">
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-brun-trait"><x-icone nom="image" class="size-7" /></span>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <p class="titre-display text-[1.05rem] leading-snug sm:text-[1.1rem]">{{ $item['nom'] }}</p>
                                <p class="mt-1 text-[0.92rem] text-encre-2 sm:text-[0.95rem]">
                                    {{ $formats[$item['type_commande']] ?? ucfirst(str_replace('_', ' ', $item['type_commande'])) }}
                                    @if(!empty($item['couleur'])) · Couleur : <span class="text-encre">{{ $item['couleur'] }}</span>@endif
                                </p>
                                <p class="chiffres mt-0.5 text-[0.92rem] text-encre-2">{{ number_format($prixUnitaire, 0, ',', ' ') }} F {{ $auMetre ? 'le mètre' : ($item['type_commande'] === 'rouleau' ? 'le rouleau' : 'l\'unité') }}</p>
                            </div>

                            {{-- Téléphone : quantité à gauche, montant et corbeille à droite. Ordinateur : colonne à droite. --}}
                            <div class="col-span-2 flex items-center justify-between gap-3 sm:col-span-1 sm:flex-col sm:items-end sm:gap-2">
                                <div class="flex h-10 items-stretch border border-trait-fort bg-carte" :class="envoi && 'opacity-70'">
                                    <button type="button" @click="changer(-1)" :disabled="qte <= 1" class="flex w-10 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce active:bg-brun-doux disabled:opacity-35">
                                        <x-icone nom="moins" class="size-4" /><span class="sr-only">Diminuer la quantité de {{ $item['nom'] }}</span>
                                    </button>
                                    <span class="chiffres flex min-w-[3.25rem] items-center justify-center gap-0.5 px-1 font-semibold" aria-live="polite">
                                        <span x-text="qte">{{ (int) $item['quantite'] }}</span>@if($auMetre)<span class="text-[0.85rem] font-normal text-encre-2">&nbsp;m</span>@endif
                                    </span>
                                    <button type="button" @click="changer(1)" class="flex w-10 items-center justify-center text-encre-2 transition-colors hover:bg-brun-doux hover:text-brun-fonce active:bg-brun-doux">
                                        <x-icone nom="plus" class="size-4" /><span class="sr-only">Augmenter la quantité de {{ $item['nom'] }}</span>
                                    </button>
                                </div>

                                <div class="flex items-center gap-1 sm:flex-col sm:items-end sm:gap-1">
                                    <p class="chiffres text-[1.05rem] font-semibold sm:order-first sm:text-[1.1rem]" x-text="f(ligne)">{{ number_format($item['sous_total'], 0, ',', ' ') }} F</p>
                                    <form method="POST" action="{{ route('panier.supprimer', $idLigne) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="flex size-10 items-center justify-center text-encre-2 transition-colors hover:text-alerte sm:size-auto sm:gap-1.5 sm:py-1 sm:text-[0.92rem]">
                                            <x-icone nom="corbeille" class="size-[1.15rem]" /><span class="sr-only sm:not-sr-only">Retirer</span><span class="sr-only"> {{ $item['nom'] }} du panier</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <p x-show="erreur" x-cloak x-text="erreur" role="alert" class="mt-3 bg-alerte-doux p-3 text-[0.95rem] text-alerte"></p>
            </div>

            {{-- RÉCAPITULATIF --}}
            <aside class="revele min-w-0 bg-carte p-5 sm:p-6 lg:sticky lg:top-28" style="--delai: 120ms" aria-labelledby="titre-recap">
                <h2 id="titre-recap" class="titre-display text-[1.3rem]">Récapitulatif</h2>

                {{-- Téléphone : récapitulatif réduit au total (code promo, livraison et remarques retirés à la demande de la boutique) --}}
                <form method="POST" action="{{ route('panier.coupon.appliquer') }}" class="mt-5 hidden sm:block">
                    @csrf
                    <label for="coupon" class="etiquette">Code promo</label>
                    <div class="flex gap-2">
                        <input id="coupon" type="text" name="coupon_code" value="{{ $coupon['code'] ?? '' }}" placeholder="Votre code" class="champ uppercase">
                        <button type="submit" class="btn btn-contour">Appliquer</button>
                    </div>
                    @error('coupon_code')<p class="erreur">{{ $message }}</p>@enderror
                    @if(!empty($coupon['code']))
                        <p class="mt-2 flex items-center gap-1.5 text-[0.92rem] text-succes" x-show="remise > 0"><x-icone nom="coche" class="size-4" /> Code {{ $coupon['code'] }} appliqué</p>
                    @endif
                </form>

                <dl class="mt-4 space-y-2.5 text-[0.98rem] sm:mt-6 sm:border-t sm:border-trait sm:pt-5">
                    <div class="flex justify-between gap-4"><dt class="text-encre-2">Sous-total</dt><dd class="chiffres" x-text="f(sousTotal)">{{ number_format($subtotal, 0, ',', ' ') }} F</dd></div>
                    <div class="flex justify-between gap-4" x-show="remise > 0" @if($discount <= 0) x-cloak @endif><dt class="text-encre-2">Réduction</dt><dd class="chiffres text-succes">− <span x-text="f(remise)">{{ number_format($discount, 0, ',', ' ') }} F</span></dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-encre-2">Livraison</dt><dd class="text-encre-2">Calculée à l'étape suivante</dd></div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-trait pt-4">
                        <dt class="font-medium">Total</dt>
                        <dd class="titre-display chiffres text-[1.8rem] text-brun" x-text="f(total)">{{ number_format($total, 0, ',', ' ') }} F</dd>
                    </div>
                </dl>

                <p class="mt-5 hidden items-start gap-2.5 bg-papier-2 p-4 text-[0.92rem] text-encre-2 sm:flex">
                    <x-icone nom="livraison" class="mt-0.5 size-4 shrink-0 text-brun" />
                    Livraison partout en Côte d'Ivoire sous 24 à 72 h, ou retrait gratuit en boutique.
                </p>

                {{-- Remarques pour la boutique : enregistrées en tapant, jointes à la commande, envoyables sur WhatsApp --}}
                <div class="mt-5 sm:mt-6 sm:border-t sm:border-trait sm:pt-5"
                     x-data="{
                         texte: @js($remarques ?? ''),
                         dernier: @js($remarques ?? ''),
                         etat: 'Elles partent avec votre commande.',
                         enregistrer() {
                             if (this.texte === this.dernier) return;
                             this.dernier = this.texte;
                             fetch(@js(route('panier.remarques')), { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }, body: JSON.stringify({ remarques: this.texte }) })
                                 .then((r) => this.etat = r.ok ? 'Remarques enregistrées : elles partent avec votre commande.' : 'Remarques non enregistrées, réessayez.')
                                 .catch(() => this.etat = 'Remarques non enregistrées, réessayez.');
                         },
                         lien(debut) {
                             const fin = (this.texte.trim() ? '\n\nMes remarques : ' + this.texte.trim() : '') + '\n\nMerci !';
                             return 'https://wa.me/' + @js((string) $numeroWhatsapp) + '?text=' + encodeURIComponent(debut + fin);
                         }
                     }">
                    <label for="remarques" class="etiquette hidden sm:block">Vos remarques pour la boutique <span class="font-normal text-encre-2">(facultatif)</span></label>
                    <textarea id="remarques" rows="3" maxlength="1000" x-model="texte" @input.debounce.800ms="enregistrer()" @blur="enregistrer()"
                              class="champ hidden min-h-0 sm:block" placeholder="Ex. : même bain de teinture pour les deux tissus, livraison après 17 h"></textarea>
                    <p class="aide hidden sm:block" x-text="etat" aria-live="polite">Elles partent avec votre commande.</p>
                    @if($numeroWhatsapp)
                        <a href="https://wa.me/{{ $numeroWhatsapp }}?text={{ rawurlencode($debutMessage) }}" :href="lien(message)" @click="enregistrer()" target="_blank" rel="noopener noreferrer"
                           class="btn btn-contour w-full sm:mt-3"><x-icone nom="message" /> Envoyer au propriétaire sur WhatsApp</a>
                    @endif
                </div>

                {{-- Ordinateur : bouton ici. Téléphone et tablette : dans la barre fixe du bas. --}}
                <a href="{{ route('commande') }}" class="btn btn-brun btn-lg mt-6 hidden w-full lg:flex">Passer la commande <x-icone nom="fleche" /></a>
                <a href="{{ route('catalogue') }}" class="btn btn-fantome mt-2 w-full">Continuer mes achats</a>
            </aside>

            {{-- BARRE FIXE (téléphone et tablette) : total à jour et « Commander », comme la barre d'achat de la page produit --}}
            <div class="sans-impression fixed inset-x-0 bottom-0 z-sticky border-t border-trait bg-papier pb-[env(safe-area-inset-bottom)] lg:hidden">
                <div class="conteneur flex items-center gap-4 py-2">
                    <div class="min-w-0 flex-1">
                        <p class="text-[0.85rem] leading-tight text-encre-2">Total <span class="hidden sm:inline">· livraison calculée ensuite</span></p>
                        <p class="titre-display chiffres text-[1.35rem] leading-tight text-brun" x-text="f(total)" aria-live="polite">{{ number_format($total, 0, ',', ' ') }} F</p>
                    </div>
                    <a href="{{ route('commande') }}" class="btn btn-brun h-12 shrink-0 px-6 sm:px-10">Commander <x-icone nom="fleche" /></a>
                </div>
            </div>
        </div>
    @endif
</div>

@endsection
