@extends('layouts.admin')
@section('title', 'Stock · ' . $produit->nom . ' · ' . boutique('nom'))
@section('content')
@php
    $m = fn ($metres) => \App\Models\Produit::metres($metres);
    $etat = $produit->etatStock();
    $signe = fn ($n) => $n > 0 ? '+' . $n : ($n < 0 ? '−' . abs($n) : '0');
    $signeM = fn ($n) => $n > 0 ? '+' . $m($n) : ($n < 0 ? '−' . $m(abs($n)) : '—');
    $aides = [
        'arrivage' => 'Marchandise reçue : ajoutée au stock actuel.',
        'retrait' => 'Perte, coupe, échantillon… : retiré du stock actuel. Les mètres sont pris dans le rouleau entamé, puis dans un rouleau entier.',
        'inventaire' => 'Après avoir compté : le stock devient exactement ces chiffres.',
    ];
    $boutons = ['arrivage' => 'Ajouter au stock', 'retrait' => 'Retirer du stock', 'inventaire' => 'Enregistrer l\'inventaire'];
@endphp

<a href="{{ route('admin.stock') }}" class="lien-fleche mb-5 flex-row-reverse justify-end text-[0.95rem]"><span>Tout le stock</span><x-icone nom="fleche-gauche" /></a>

<x-admin-entete :titre="$produit->nom" :description="($produit->categorie->nom ?? 'Sans catégorie') . ($produit->disponible ? '' : ' · masqué de la boutique')">
    <a href="{{ route('admin.produits', ['modifier' => $produit->id]) }}" class="btn btn-contour"><x-icone nom="crayon" /> Fiche produit</a>
</x-admin-entete>

{{-- Téléphone : stock, puis formulaire, puis historique. Ordinateur : formulaire à droite. --}}
<div class="grid items-start gap-6 xl:grid-cols-[1fr_24rem] xl:grid-rows-[auto_1fr]">
        {{-- Stock actuel --}}
        <section class="min-w-0 bg-brun-fonce p-6 text-white sm:p-8 xl:col-start-1 xl:row-start-1" aria-labelledby="titre-stock">
            <h2 id="titre-stock" class="flex items-center gap-2 text-[0.95rem] font-medium text-or-doux"><x-icone nom="regle" class="size-4" /> En stock</h2>
            <p class="mt-3 flex flex-wrap items-baseline gap-x-4 gap-y-1">
                <span class="titre-display chiffres text-[clamp(2.6rem,6vw,3.6rem)] leading-none">{{ $produit->stock }}</span>
                <span class="text-[1.1rem] text-white/80">rouleau{{ $produit->stock > 1 ? 'x' : '' }} entier{{ $produit->stock > 1 ? 's' : '' }}</span>
                @if($produit->metres_entames > 0)
                    <span class="chiffres text-[1.1rem] text-or">+ {{ $m($produit->metres_entames) }} dans le rouleau entamé</span>
                @endif
            </p>
            <p class="mt-3 text-[0.95rem] text-white/75">
                @if($produit->metres_par_rouleau)
                    Un rouleau fait {{ $m($produit->metres_par_rouleau) }} · <span class="chiffres font-medium text-white">{{ $m($produit->metresTotal()) }}</span> en tout
                @else
                    Longueur d'un rouleau non indiquée
                @endif
                · {{ ['ok' => 'stock correct', 'faible' => 'à surveiller (moins de ' . \App\Models\Produit::SEUIL_ROULEAUX . ' rouleaux)', 'epuise' => 'épuisé'][$etat] }}
            </p>
            @if(! $produit->metres_par_rouleau)
                <p class="mt-4 flex items-start gap-2 bg-white/10 p-3 text-[0.92rem]"><x-icone nom="alerte" class="mt-0.5 size-4 shrink-0 text-or" /> Indiquez la longueur d'un rouleau (à droite) : sans elle, les ventes au mètre ne peuvent pas ouvrir un nouveau rouleau.</p>
            @endif
            @if($produit->metres_entames < 0)
                <p class="mt-3 flex items-start gap-2 bg-white/10 p-3 text-[0.92rem]"><x-icone nom="alerte" class="mt-0.5 size-4 shrink-0 text-or" /> {{ $m(-$produit->metres_entames) }} vendus restent à décompter : ils seront retirés dès que la longueur d'un rouleau sera indiquée.</p>
            @endif
        </section>

        {{-- Historique --}}
        <section id="historique" class="order-last min-w-0 scroll-mt-24 bg-carte xl:order-none xl:col-start-1 xl:row-start-2" aria-labelledby="titre-historique">
            <h2 id="titre-historique" class="titre-display p-6 pb-4 text-[1.25rem]">Historique</h2>
            @if($mouvements->isEmpty())
                <p class="border-t border-trait px-6 py-10 text-center text-encre-2">Aucun mouvement pour l'instant. Le premier arrivage, retrait, inventaire ou vente apparaîtra ici.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="tableau tableau-empile">
                        <thead>
                            <tr>
                                <th scope="col">Opération</th>
                                <th scope="col" class="text-right">Rouleaux</th>
                                <th scope="col" class="text-right">Mètres</th>
                                <th scope="col">Stock après</th>
                                <th scope="col">Détail</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mouvements as $mvt)
                                <tr>
                                    <td>
                                        <span class="pastille {{ $mvt->pastille() }}">{{ $mvt->libelle() }}</span>
                                        <span class="chiffres mt-1 block text-[0.85rem] text-encre-2">{{ $mvt->created_at->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="chiffres text-right font-medium" data-label="Rouleaux">{{ $signe($mvt->rouleaux) }}</td>
                                    <td class="chiffres whitespace-nowrap text-right font-medium" data-label="Mètres">{{ $signeM($mvt->metres) }}</td>
                                    <td class="chiffres" data-label="Stock après">
                                        {{ $mvt->rouleaux_apres }} rouleau{{ $mvt->rouleaux_apres > 1 ? 'x' : '' }}@if($mvt->metres_apres != 0) {{ $mvt->metres_apres > 0 ? '+ ' . $m($mvt->metres_apres) : '− ' . $m(abs($mvt->metres_apres)) }}@endif
                                    </td>
                                    <td data-label="Détail" data-large>
                                        @if($mvt->commande)
                                            <a href="{{ route('admin.commande.detail', $mvt->commande) }}" class="font-medium text-brun hover:underline">{{ $mvt->note }}</a>
                                        @elseif($mvt->note)
                                            <span>{{ $mvt->note }}</span>
                                        @endif
                                        @if($mvt->user)
                                            <span class="block text-[0.85rem] text-encre-2">par {{ $mvt->user->name }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($mouvements->hasPages())
                    <div class="border-t border-trait p-4">{{ $mouvements->fragment('historique')->links() }}</div>
                @endif
            @endif
        </section>

    <aside class="min-w-0 space-y-6 xl:sticky xl:top-10 xl:col-start-2 xl:row-span-2 xl:row-start-1">
        {{-- Mouvement de stock --}}
        <section id="mouvement" class="scroll-mt-24 bg-carte p-6" aria-labelledby="titre-mouvement"
                 x-data="{ type: @js(old('type', 'arrivage')), rouleaux: @js(old('rouleaux', '')), metres: @js(old('metres', '')),
                           choisir(t) { this.type = t; if (t === 'inventaire') { this.rouleaux = {{ $produit->stock }}; this.metres = {{ max(0, (float) $produit->metres_entames) }}; } else { this.rouleaux = ''; this.metres = ''; } } }">
            <h2 id="titre-mouvement" class="titre-display text-[1.2rem]">Mouvement de stock</h2>
            <form method="POST" action="{{ route('admin.stock.mouvement', $produit) }}" class="mt-4 space-y-4">
                @csrf
                <fieldset>
                    <legend class="sr-only">Opération</legend>
                    <div class="grid grid-cols-3 gap-1.5">
                        @foreach(['arrivage' => 'Arrivage', 'retrait' => 'Retrait', 'inventaire' => 'Inventaire'] as $cle => $libelle)
                            <label class="choix min-h-11 justify-center gap-0 p-2 text-center text-[0.92rem]" :class="type === '{{ $cle }}' && 'est-choisi font-semibold'">
                                <input type="radio" name="type" value="{{ $cle }}" class="sr-only" :checked="type === '{{ $cle }}'" @change="choisir('{{ $cle }}')">
                                {{ $libelle }}
                            </label>
                        @endforeach
                    </div>
                    @foreach($aides as $cle => $aide)
                        <p class="aide mt-2" x-show="type === '{{ $cle }}'" @if($cle !== old('type', 'arrivage')) x-cloak @endif>{{ $aide }}</p>
                    @endforeach
                </fieldset>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="mvt-rouleaux" class="etiquette">Rouleaux</label>
                        <input id="mvt-rouleaux" type="number" name="rouleaux" min="0" step="1" inputmode="numeric" x-model="rouleaux" class="champ chiffres" placeholder="0">
                        @error('rouleaux')<p class="erreur">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="mvt-metres" class="etiquette">Mètres</label>
                        <input id="mvt-metres" type="number" name="metres" min="0" step="0.01" inputmode="decimal" x-model="metres" class="champ chiffres" placeholder="0">
                        @error('metres')<p class="erreur">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="mvt-note" class="etiquette">Note <span class="font-normal text-encre-2">(facultatif)</span></label>
                    <input id="mvt-note" type="text" name="note" maxlength="255" value="{{ old('note') }}" class="champ" placeholder="Ex. : livraison du fournisseur, rouleau abîmé…">
                </div>

                @unless($produit->disponible)
                    <label class="flex items-start gap-2.5 text-[0.95rem]" x-show="type === 'arrivage'">
                        <input type="checkbox" name="remettre_en_vente" value="1" checked class="mt-1 size-4 accent-[var(--brun)]">
                        <span>Remettre ce tissu en vente dans la boutique</span>
                    </label>
                @endunless

                <button type="submit" class="btn w-full" :class="type === 'retrait' ? 'btn-contour' : 'btn-brun'">
                    <span x-text="@js($boutons)[type]">{{ $boutons['arrivage'] }}</span>
                </button>
            </form>
        </section>

        {{-- Longueur d'un rouleau --}}
        <section class="bg-carte p-6" aria-labelledby="titre-longueur">
            <h2 id="titre-longueur" class="titre-display text-[1.2rem]">Longueur d'un rouleau</h2>
            <p class="mt-1 text-[0.9rem] text-encre-2">Sert à ouvrir un nouveau rouleau quand on vend au mètre.</p>
            <form method="POST" action="{{ route('admin.stock.longueur', $produit) }}" class="mt-4 flex items-end gap-2">
                @csrf @method('PATCH')
                <div class="relative flex-1">
                    <label for="longueur" class="sr-only">Mètres par rouleau</label>
                    <input id="longueur" type="number" name="metres_par_rouleau" min="0.5" max="1000" step="0.01" inputmode="decimal"
                           value="{{ old('metres_par_rouleau', $produit->metres_par_rouleau) }}" class="champ chiffres pr-10" placeholder="Ex. : 30">
                    <span class="pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-encre-3">m</span>
                </div>
                <button type="submit" class="btn btn-contour">Enregistrer</button>
            </form>
            @error('metres_par_rouleau')<p class="erreur">{{ $message }}</p>@enderror
        </section>
    </aside>
</div>
@endsection
