@extends('layouts.admin')
@section('title', ($commande->estDevis() ? 'Devis' : 'Commande') . ' n° ' . str_pad($commande->id, 4, '0', STR_PAD_LEFT) . ' · ' . boutique('nom'))
@section('content')
@php
    $statuts = ['en_attente' => 'En attente', 'confirmee' => 'Confirmée', 'en_preparation' => 'En préparation', 'expedie' => 'Expédiée', 'livree' => 'Livrée', 'annulee' => 'Annulée'];
    $adresse = collect(explode(' | ', $commande->adresse_livraison ?? ''))->filter();
    $francs = fn ($montant) => number_format((float) $montant, 0, ',', ' ') . ' F';
    $comptePro = $commande->est_pro ? $commande->user?->comptePro : null;
    // Devis pro encore modifiable : demandé, ou envoyé sans réponse du revendeur
    $chiffrable = $commande->est_pro && in_array($commande->statut, ['devis_demande', 'devis_envoye'], true);
    $numero = str_pad($commande->id, 4, '0', STR_PAD_LEFT);
    // WhatsApp du revendeur avec tout le devis écrit (tissus, quantités, prix, total, livraison)
    $whatsappDevis = $comptePro ? \App\Support\MessageDevis::lien($comptePro->lienWhatsapp(), $commande, 'revendeur') : null;
@endphp

<a href="{{ route('admin.commandes') }}" class="lien-fleche mb-5 flex-row-reverse justify-end text-[0.95rem]"><span>Toutes les commandes</span><x-icone nom="fleche-gauche" /></a>

<x-admin-entete :titre="($commande->estDevis() ? 'Devis pro' : 'Commande') . ' n° ' . $numero" :description="($commande->est_pro ? 'Demandé' : 'Passée') . ' le ' . $commande->created_at->translatedFormat('j F Y à H:i') . ($comptePro ? ' par ' . $comptePro->boutique : '')">
    @if($commande->est_pro && !$commande->estDevis())
        <span class="pastille pastille-brun !px-3.5 !py-2 !text-[0.95rem]">Commande pro</span>
    @endif
    <x-statut-commande :statut="$commande->statut" class="!px-3.5 !py-2 !text-[0.95rem]" />
</x-admin-entete>

@if(session('devis_whatsapp') && $whatsappDevis)
    {{-- Après « Envoyer + WhatsApp » : le devis est enregistré, il reste à l'ouvrir dans WhatsApp --}}
    <div class="mb-6 flex flex-col gap-4 bg-succes-doux p-5 sm:flex-row sm:items-center sm:justify-between" role="status">
        <p class="flex items-start gap-3"><x-icone nom="message" class="mt-0.5 size-5 shrink-0 text-succes" /> <span>Devis enregistré. Ouvrez WhatsApp pour l'envoyer à {{ $comptePro->boutique }}, le message est déjà écrit.</span></p>
        <a href="{{ $whatsappDevis }}" target="_blank" rel="noopener noreferrer" class="btn btn-brun shrink-0"><x-icone nom="message" /> Ouvrir WhatsApp</a>
    </div>
@endif

<div class="grid items-start gap-6 xl:grid-cols-[1fr_22rem]">
    <div class="min-w-0 space-y-6">
        @if($chiffrable)
            {{-- Chiffrage du devis : quantités et prix ajustables, livraison, date, mot au revendeur --}}
            @php
                $lignesInitiales = $commande->items->map(fn ($item) => [
                    'id' => $item->id,
                    'quantite' => (int) old('lignes.' . $item->id . '.quantite', $item->quantite),
                    'prix' => (int) old('lignes.' . $item->id . '.prix_unitaire', $item->prix_unitaire),
                ])->values();
            @endphp
            <form method="POST" action="{{ route('admin.commande.devis', $commande) }}" class="bg-carte"
                  x-data="chiffrage(@js($lignesInitiales), {{ (int) old('frais_livraison', $commande->frais_livraison ?? 0) }})" aria-labelledby="titre-chiffrage">
                @csrf
                <div class="flex flex-col gap-1 p-6 pb-4 sm:flex-row sm:items-baseline sm:justify-between">
                    <h2 id="titre-chiffrage" class="titre-display text-[1.25rem]">{{ $commande->statut === 'devis_envoye' ? 'Modifier le devis envoyé' : ($commande->devis_envoye_le ? 'Revoir le devis' : 'Chiffrer le devis') }}</h2>
                    <p class="text-[0.9rem] text-encre-2">Prix pro du catalogue pré-remplis. Une quantité à 0 retire la ligne.</p>
                </div>

                @if($errors->any())
                    <div class="mx-6 mb-4 bg-danger-doux p-4 text-danger" role="alert">
                        <ul class="space-y-1 text-[0.92rem]">@foreach($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="tableau tableau-empile">
                        <thead>
                            <tr>
                                <th scope="col">Produit et format</th>
                                <th scope="col" class="text-right">Qté</th>
                                <th scope="col" class="text-right">Prix unitaire</th>
                                <th scope="col" class="text-right">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commande->items as $item)
                                @php $produit = $item->produit; @endphp
                                <tr :class="lignes[{{ $loop->index }}].quantite <= 0 && 'opacity-50'">
                                    <td>
                                        <span class="block font-medium">{{ $produit->nom ?? 'Produit supprimé' }}</span>
                                        <span class="block text-[0.85rem] text-encre-2">
                                            <span class="font-medium text-brun">{{ $item->libelleFormat() }}</span>@if($produit) · @can('manage-products')<a href="{{ route('admin.stock.produit', $produit) }}" class="hover:text-brun hover:underline">stock {{ $produit->libelleStock() }}</a>@else stock {{ $produit->libelleStock() }}@endcan @endif
                                        </span>
                                    </td>
                                    <td class="text-right" data-label="Quantité">
                                        <label for="q-{{ $item->id }}" class="sr-only">Quantité, {{ $produit->nom ?? 'produit' }}</label>
                                        <input id="q-{{ $item->id }}" type="number" name="lignes[{{ $item->id }}][quantite]" min="0" max="9999" step="1" inputmode="numeric" required
                                               x-model.number="lignes[{{ $loop->index }}].quantite" class="champ chiffres ml-auto min-h-10 w-20 text-right">
                                    </td>
                                    <td class="text-right" data-label="Prix unitaire">
                                        <label for="p-{{ $item->id }}" class="sr-only">Prix unitaire, {{ $produit->nom ?? 'produit' }}</label>
                                        <div class="relative ml-auto w-32">
                                            <input id="p-{{ $item->id }}" type="number" name="lignes[{{ $item->id }}][prix_unitaire]" min="0" step="1" inputmode="numeric" required
                                                   x-model.number="lignes[{{ $loop->index }}].prix" class="champ chiffres min-h-10 pr-8 text-right">
                                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-encre-3">F</span>
                                        </div>
                                    </td>
                                    <td class="chiffres whitespace-nowrap text-right font-semibold" data-label="Sous-total" x-text="francs(sousTotal(lignes[{{ $loop->index }}]))">{{ $francs($item->sous_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="border-t border-trait-fort px-4 py-3 text-right text-encre-2">Tissus</td>
                                <td class="chiffres whitespace-nowrap border-t border-trait-fort px-4 py-3 text-right" x-text="francs(totalArticles())"></td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-2 text-right">
                                    <label for="frais_livraison" class="text-encre-2">Frais de livraison</label>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <div class="relative ml-auto w-32">
                                        <input id="frais_livraison" type="number" name="frais_livraison" min="0" step="1" inputmode="numeric" x-model.number="frais" class="champ chiffres min-h-10 pr-8 text-right">
                                        <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-encre-3">F</span>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3" class="px-4 py-4 text-right font-medium">Total du devis</td>
                                <td class="titre-display chiffres whitespace-nowrap px-4 py-4 text-right text-[1.3rem] text-brun" x-text="francs(total())">{{ $francs($commande->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="grid gap-5 border-t border-trait p-6 sm:grid-cols-[14rem_1fr]">
                    <div>
                        <label for="livraison_prevue_le" class="etiquette">Livraison prévue le</label>
                        <input id="livraison_prevue_le" type="date" name="livraison_prevue_le" class="champ chiffres"
                               value="{{ old('livraison_prevue_le', ($commande->livraison_prevue_le ?? $commande->livraison_souhaitee_le)?->toDateString()) }}">
                        @if($commande->livraison_souhaitee_le)
                            <p class="aide">Souhaitée : {{ $commande->livraison_souhaitee_le->translatedFormat('j F') }}</p>
                        @endif
                    </div>
                    <div>
                        <label for="note_boutique" class="etiquette">Mot au revendeur <span class="font-normal text-encre-2">(facultatif)</span></label>
                        <textarea id="note_boutique" name="note_boutique" rows="2" maxlength="1000" class="champ min-h-0" placeholder="Ex. : le bleu est remplacé par le bleu nuit, même prix.">{{ old('note_boutique', $commande->note_boutique) }}</textarea>
                    </div>
                </div>
                <div class="flex flex-col gap-3 border-t border-trait p-6 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-[0.92rem] text-encre-2">Le revendeur est prévenu par e-mail et valide le devis depuis son Espace Pro avant de payer.</p>
                    <div class="flex shrink-0 flex-col gap-2 sm:flex-row">
                        @if($comptePro)
                            <button type="submit" name="partager" value="whatsapp" class="btn btn-contour"><x-icone nom="message" /> Envoyer + WhatsApp</button>
                        @endif
                        <button type="submit" class="btn btn-brun">{{ $commande->devis_envoye_le ? 'Renvoyer le devis modifié' : 'Envoyer le devis' }}</button>
                    </div>
                </div>
            </form>
        @else
            <section class="bg-carte" aria-labelledby="titre-articles">
                <h2 id="titre-articles" class="titre-display p-6 pb-4 text-[1.25rem]">Articles commandés</h2>
                <div class="overflow-x-auto">
                    <table class="tableau tableau-empile">
                        <thead>
                            <tr>
                                <th scope="col">Produit</th>
                                <th scope="col">Format</th>
                                <th scope="col" class="text-right">Qté</th>
                                <th scope="col" class="text-right">Prix unitaire</th>
                                <th scope="col" class="text-right">Sous-total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($commande->items as $item)
                                <tr>
                                    <td>
                                        <span class="block font-medium">{{ $item->produit->nom ?? 'Produit supprimé' }}</span>
                                        <span class="block text-[0.85rem] text-brun">{{ $item->produit?->categorie?->nom }}</span>
                                    </td>
                                    <td data-label="Format">{{ $item->libelleFormat() }}</td>
                                    <td class="chiffres text-right" data-label="Quantité">{{ $item->quantiteLisible() }}</td>
                                    <td class="chiffres whitespace-nowrap text-right" data-label="Prix unitaire">{{ $francs($item->prix_unitaire) }}</td>
                                    <td class="chiffres whitespace-nowrap text-right font-semibold" data-label="Sous-total">{{ $francs($item->sous_total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            @if($commande->frais_livraison !== null)
                                <tr>
                                    <td colspan="4" class="border-t border-trait-fort px-4 py-3 text-right text-encre-2">Livraison</td>
                                    <td class="chiffres whitespace-nowrap border-t border-trait-fort px-4 py-3 text-right">{{ $commande->frais_livraison ? $francs($commande->frais_livraison) : 'Offerte' }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td colspan="4" @class(['px-4 py-4 text-right font-medium', 'border-t border-trait-fort' => $commande->frais_livraison === null])>Total</td>
                                <td @class(['titre-display chiffres whitespace-nowrap px-4 py-4 text-right text-[1.3rem] text-brun', 'border-t border-trait-fort' => $commande->frais_livraison === null])>{{ $francs($commande->total) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>
        @endif

        <section class="bg-carte p-6" aria-labelledby="titre-livraison">
            <h2 id="titre-livraison" class="titre-display text-[1.25rem]">Livraison</h2>
            @if($adresse->isEmpty())
                <p class="mt-3 text-encre-2">Aucune adresse indiquée.</p>
            @else
                <ul class="mt-3 space-y-1.5">
                    @foreach($adresse as $ligne)
                        <li class="{{ $loop->first ? 'font-medium' : 'text-encre-2' }}">{{ $ligne }}</li>
                    @endforeach
                </ul>
            @endif
            @if($commande->est_pro && ($commande->livraison_souhaitee_le || $commande->livraison_prevue_le))
                <dl class="mt-4 grid gap-x-8 gap-y-3 border-t border-trait pt-4 sm:grid-cols-2">
                    @if($commande->livraison_souhaitee_le)
                        <div><dt class="text-[0.9rem] text-encre-2">Souhaitée par le revendeur</dt><dd class="font-medium">{{ ucfirst($commande->livraison_souhaitee_le->translatedFormat('l j F Y')) }}</dd></div>
                    @endif
                    @if($commande->livraison_prevue_le)
                        <div><dt class="text-[0.9rem] text-encre-2">Prévue par la boutique</dt><dd class="font-medium">{{ ucfirst($commande->livraison_prevue_le->translatedFormat('l j F Y')) }}</dd></div>
                    @endif
                </dl>
            @endif
        </section>

        @if($commande->paiement)
            <section class="bg-carte p-6" aria-labelledby="titre-paiement">
                <h2 id="titre-paiement" class="titre-display text-[1.25rem]">Paiement</h2>
                <dl class="mt-4 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    <div><dt class="text-[0.9rem] text-encre-2">Moyen</dt><dd class="font-medium">{{ ucfirst(str_replace('_', ' ', $commande->paiement->methode)) }}</dd></div>
                    <div><dt class="text-[0.9rem] text-encre-2">Montant</dt><dd class="chiffres font-medium">{{ $francs($commande->paiement->montant) }}</dd></div>
                    <div><dt class="text-[0.9rem] text-encre-2">Référence</dt><dd class="break-all font-medium">{{ $commande->paiement->reference ?: 'Aucune' }}</dd></div>
                    <div><dt class="text-[0.9rem] text-encre-2">Statut</dt><dd class="mt-0.5"><x-statut-commande :statut="$commande->paiement->statut" /></dd></div>
                </dl>
            </section>
        @endif
    </div>

    <aside class="space-y-6 xl:sticky xl:top-10">
        @if($commande->estDevis())
            <section class="bg-brun-fonce p-6 text-white" aria-labelledby="titre-statut">
                <h2 id="titre-statut" class="titre-display text-[1.2rem]">Où en est le devis</h2>
                <p class="mt-3 text-[0.95rem] text-white/85">
                    @switch($commande->statut)
                        @case('devis_demande')
                            @if($commande->devis_envoye_le)
                                Le revendeur demande une modification du devis envoyé le {{ $commande->devis_envoye_le->translatedFormat('j F') }}. Lisez sa demande dans « Sa note », ajustez le devis puis renvoyez-le.
                            @else
                                À chiffrer : ajustez les lignes, ajoutez la livraison et la date, puis envoyez le devis. Le revendeur le valide avant de payer.
                            @endif
                            @break
                        @case('devis_envoye')
                            Envoyé le {{ $commande->devis_envoye_le?->translatedFormat('j F à H:i') }}. En attente de la réponse du revendeur : vous pouvez encore le modifier.
                            @break
                        @default
                            Le revendeur a refusé ce devis. Contactez-le pour en discuter, ou laissez-le tel quel.
                    @endswitch
                </p>
            </section>
        @else
            <section class="bg-brun-fonce p-6 text-white" aria-labelledby="titre-statut">
                <h2 id="titre-statut" class="titre-display text-[1.2rem]">Faire avancer la commande</h2>
                <form method="POST" action="{{ route('admin.commandes.statut', $commande->id) }}" class="mt-4">
                    @csrf @method('PUT')
                    <label for="statut" class="mb-1.5 block text-[0.9rem] text-or-doux">Nouveau statut</label>
                    <select id="statut" name="statut" class="champ">
                        @foreach($statuts as $valeur => $libelle)
                            <option value="{{ $valeur }}" @selected($commande->statut === $valeur)>{{ $libelle }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-or mt-3 w-full">Mettre à jour le statut</button>
                </form>
            </section>
        @endif

        @if($comptePro)
            <section class="bg-carte p-6" aria-labelledby="titre-revendeur">
                <h2 id="titre-revendeur" class="titre-display text-[1.2rem]">Revendeur</h2>
                <p class="mt-3 font-medium">{{ $comptePro->boutique }}</p>
                <p class="text-[0.9rem] text-encre-2">{{ $comptePro->libelleActivite() }} · {{ $comptePro->ville }}</p>
                <p class="mt-1 truncate text-[0.9rem] text-encre-2">{{ $commande->user->name }} · {{ $commande->user->email }}</p>
                @if($commande->note_client)
                    <div class="mt-4 border-t border-trait pt-4">
                        <p class="text-[0.9rem] text-encre-2">Sa note</p>
                        <p class="mt-1 whitespace-pre-line">{{ $commande->note_client }}</p>
                    </div>
                @endif
                <div class="mt-5 grid gap-2">
                    <a href="{{ $whatsappDevis }}" target="_blank" rel="noopener noreferrer" class="btn btn-contour w-full"><x-icone nom="message" /> {{ $commande->estDevis() ? 'Envoyer le devis sur WhatsApp' : 'Envoyer le récapitulatif sur WhatsApp' }}</a>
                    @if($chiffrable)
                        <p class="text-[0.82rem] text-encre-2">Le message reprend le devis enregistré. Après une modification, utilisez « Envoyer + WhatsApp ».</p>
                    @endif
                    <a href="mailto:{{ $commande->user->email }}" class="btn btn-fantome w-full"><x-icone nom="courrier" /> Écrire un e-mail</a>
                </div>
            </section>
        @else
            <section class="bg-carte p-6" aria-labelledby="titre-client">
                <h2 id="titre-client" class="titre-display text-[1.2rem]">Client</h2>
                <div class="mt-4 flex items-center gap-3">
                    <span aria-hidden="true" class="titre-display flex size-11 items-center justify-center rounded-full bg-brun-doux text-brun">{{ mb_strtoupper(mb_substr($commande->user->name ?? 'C', 0, 1)) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate font-medium">{{ $commande->user->name ?? 'Client supprimé' }}</span>
                        <span class="block truncate text-[0.9rem] text-encre-2">{{ $commande->user->email ?? '' }}</span>
                    </span>
                </div>
                @if($commande->user?->email)
                    <a href="mailto:{{ $commande->user->email }}" class="btn btn-contour mt-5 w-full"><x-icone nom="courrier" /> Écrire au client</a>
                @endif
            </section>
        @endif
    </aside>
</div>

@if($chiffrable)
    @push('scripts')
    <script>
        // Chiffrage du devis : sous-totaux et total recalculés pendant la saisie
        function chiffrage(lignes, frais) {
            return {
                lignes,
                frais,
                sousTotal(ligne) {
                    return Math.max(0, Number(ligne.quantite) || 0) * Math.max(0, Number(ligne.prix) || 0);
                },
                totalArticles() {
                    return this.lignes.reduce((somme, ligne) => somme + this.sousTotal(ligne), 0);
                },
                total() {
                    return this.totalArticles() + Math.max(0, Number(this.frais) || 0);
                },
                francs(montant) {
                    return new Intl.NumberFormat('fr-FR').format(montant).replace(/[  ]/g, ' ') + ' F';
                },
            };
        }
    </script>
    @endpush
@endif

@endsection
