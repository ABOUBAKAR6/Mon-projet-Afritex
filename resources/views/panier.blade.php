@extends('layouts.app')
@section('title', 'Mon panier · ' . boutique('nom'))
@section('content')
@php
    $formats = [
        'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
        'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
    ];

    // Message WhatsApp du panier pour le propriétaire (les remarques s'ajoutent en direct)
    $numeroWhatsapp = config('services.whatsapp.numero');
    $f = fn ($n) => number_format((float) $n, 0, ',', ' ') . ' F';
    $lignes = ['Bonjour ' . boutique('nom') . ',', '', 'Voici mon panier :', ''];
    foreach ($panier as $item) {
        $q = (float) $item['quantite'];
        $unite = $item['type_commande'] === 'metre' ? ' m' : ($item['type_commande'] === 'rouleau' ? ($q > 1 ? ' rouleaux' : ' rouleau') : '');
        $lignes[] = '*' . $item['nom'] . '*';
        $lignes[] = ($formats[$item['type_commande']] ?? $item['type_commande']) . ' : ' . rtrim(rtrim(number_format($q, 2, ',', ''), '0'), ',') . $unite
            . ' × ' . $f($item['prix_unitaire'] ?? ($item['sous_total'] / max(1, $q))) . ' = ' . $f($item['sous_total']);
        if (!empty($item['couleur'])) {
            $lignes[] = 'Couleur souhaitée : ' . $item['couleur'];
        }
        $lignes[] = '';
    }
    $lignes[] = '*Total : ' . $f($total ?? 0) . '*';
    $debutMessage = implode("\n", $lignes);
@endphp

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    <h1 class="titre-display apparition text-[2.2rem] sm:text-[2.8rem]">Mon panier</h1>

    @if(empty($panier))
        <div class="apparition mt-8 grid items-center gap-8 bg-carte p-8 sm:p-12 lg:grid-cols-[1fr_auto]" style="animation-delay: 80ms">
            <div>
                <p class="titre-display text-[1.5rem]">Votre panier est vide</p>
                <p class="mt-2 max-w-[52ch] text-encre-2">Parcourez nos familles de tissus, choisissez un format (au mètre ou au rouleau) et ajoutez-le ici.</p>
            </div>
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Voir le catalogue <x-icone nom="fleche" /></a>
        </div>
    @else
        <p class="apparition mt-2 text-encre-2" style="animation-delay: 60ms">{{ count($panier) }} {{ count($panier) > 1 ? 'articles' : 'article' }}</p>

        <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_24rem] lg:gap-12">
            {{-- ARTICLES --}}
            <ul class="min-w-0 divide-y divide-trait bg-carte">
                @foreach($panier as $index => $item)
                    <li class="revele grid grid-cols-[5rem_1fr] gap-4 p-4 sm:grid-cols-[6.5rem_1fr_auto] sm:items-center sm:gap-6 sm:p-5" style="--delai: {{ min($index, 5) * 60 }}ms">
                        <div class="aspect-square overflow-hidden bg-papier-2">
                            @if(!empty($item['image']) && file_exists(public_path($item['image'])))
                                <img src="{{ asset($item['image']) }}" alt="" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center text-brun-trait"><x-icone nom="image" class="size-7" /></span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="titre-display text-[1.1rem]">{{ $item['nom'] }}</p>
                            <p class="mt-1 text-[0.95rem] text-encre-2">
                                {{ $formats[$item['type_commande']] ?? ucfirst(str_replace('_', ' ', $item['type_commande'])) }}
                                · <span class="chiffres">{{ $item['quantite'] }}</span> × <span class="chiffres">{{ number_format($item['prix_unitaire'] ?? ($item['sous_total'] / max(1, $item['quantite'])), 0, ',', ' ') }} F</span>
                            </p>
                            @if(!empty($item['couleur']))
                                <p class="mt-1 text-[0.92rem]"><span class="text-encre-2">Couleur :</span> {{ $item['couleur'] }}</p>
                            @endif
                            <p class="chiffres mt-2 font-semibold sm:hidden">{{ number_format($item['sous_total'], 0, ',', ' ') }} F</p>
                        </div>
                        <div class="col-span-2 flex items-center justify-between gap-4 sm:col-span-1 sm:flex-col sm:items-end">
                            <p class="chiffres hidden text-[1.1rem] font-semibold sm:block">{{ number_format($item['sous_total'], 0, ',', ' ') }} F</p>
                            <form method="POST" action="{{ route('panier.supprimer', $item['id'] ?? $index) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-fantome btn-sm -mr-3 text-[0.92rem]">
                                    <x-icone nom="corbeille" /> Retirer<span class="sr-only"> {{ $item['nom'] }} du panier</span>
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

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
                        <p class="mt-2 flex items-center gap-1.5 text-[0.92rem] text-succes"><x-icone nom="coche" class="size-4" /> Code {{ $coupon['code'] }} appliqué</p>
                    @endif
                </form>

                <dl class="mt-4 space-y-2.5 text-[0.98rem] sm:mt-6 sm:border-t sm:border-trait sm:pt-5">
                    <div class="flex justify-between gap-4"><dt class="text-encre-2">Sous-total</dt><dd class="chiffres">{{ number_format($subtotal, 0, ',', ' ') }} F</dd></div>
                    @if($discount > 0)
                        <div class="flex justify-between gap-4"><dt class="text-encre-2">Réduction</dt><dd class="chiffres text-succes">− {{ number_format($discount, 0, ',', ' ') }} F</dd></div>
                    @endif
                    <div class="flex justify-between gap-4"><dt class="text-encre-2">Livraison</dt><dd class="text-encre-2">Calculée à l'étape suivante</dd></div>
                    <div class="flex items-baseline justify-between gap-4 border-t border-trait pt-4">
                        <dt class="font-medium">Total</dt>
                        <dd class="titre-display chiffres text-[1.8rem] text-brun">{{ number_format($total, 0, ',', ' ') }} F</dd>
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
                         lien() {
                             const fin = (this.texte.trim() ? '\n\nMes remarques : ' + this.texte.trim() : '') + '\n\nMerci !';
                             return 'https://wa.me/' + @js((string) $numeroWhatsapp) + '?text=' + encodeURIComponent(@js($debutMessage) + fin);
                         }
                     }">
                    <label for="remarques" class="etiquette hidden sm:block">Vos remarques pour la boutique <span class="font-normal text-encre-2">(facultatif)</span></label>
                    <textarea id="remarques" rows="3" maxlength="1000" x-model="texte" @input.debounce.800ms="enregistrer()" @blur="enregistrer()"
                              class="champ hidden min-h-0 sm:block" placeholder="Ex. : même bain de teinture pour les deux tissus, livraison après 17 h"></textarea>
                    <p class="aide hidden sm:block" x-text="etat" aria-live="polite">Elles partent avec votre commande.</p>
                    @if($numeroWhatsapp)
                        <a href="https://wa.me/{{ $numeroWhatsapp }}?text={{ rawurlencode($debutMessage) }}" :href="lien()" @click="enregistrer()" target="_blank" rel="noopener noreferrer"
                           class="btn btn-contour w-full sm:mt-3"><x-icone nom="message" /> Envoyer au propriétaire sur WhatsApp</a>
                    @endif
                </div>

                <a href="{{ route('commande') }}" class="btn btn-brun btn-lg mt-6 w-full">Passer la commande <x-icone nom="fleche" /></a>
                <a href="{{ route('catalogue') }}" class="btn btn-fantome mt-2 w-full">Continuer mes achats</a>
            </aside>
        </div>
    @endif
</div>

@endsection
