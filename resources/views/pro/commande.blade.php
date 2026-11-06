@extends('layouts.app')
@php
    $numero = str_pad($commande->id, 4, '0', STR_PAD_LEFT);
    $nature = $commande->estDevis() ? 'Devis' : 'Commande';
    $francs = fn ($montant) => number_format((float) $montant, 0, ',', ' ') . ' F';
    $numeroWhatsapp = config('services.whatsapp.numero');
    $lienWhatsapp = $numeroWhatsapp ? 'https://wa.me/' . $numeroWhatsapp : null;
    $sousTotal = $commande->sousTotalArticles();
@endphp
@section('title', $nature . ' n° ' . $numero . ' · ' . boutique('nom'))
@section('content')

@include('pro.partials.entete')

<div class="conteneur pb-20 pt-8 lg:pb-28 lg:pt-10">
    <a href="{{ route('pro.commandes') }}" class="lien-fleche flex-row-reverse text-[0.95rem]"><span>Mes commandes pro</span><x-icone nom="fleche-gauche" /></a>

    <div class="apparition mt-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="titre-display text-[2.2rem] sm:text-[2.6rem]">{{ $nature }} n° <span class="chiffres">{{ $numero }}</span></h1>
            <p class="mt-1 text-encre-2">{{ $nature === 'Devis' ? 'Demandé' : 'Passée' }} le {{ $commande->created_at->translatedFormat('j F Y à H:i') }}</p>
        </div>
        <x-statut-commande :statut="$commande->statut" client class="self-start !text-[0.92rem] sm:self-auto" />
    </div>

    {{-- Où en est le devis ou la commande --}}
    @switch($commande->statut)
        @case('devis_demande')
            <div class="apparition mt-8 flex items-start gap-4 bg-carte p-5 sm:p-6" style="animation-delay: 60ms">
                <x-icone nom="horloge" class="mt-0.5 size-5 shrink-0 text-brun" />
                <div>
                    @if($commande->devis_envoye_le)
                        {{-- Déjà envoyé une fois : le revendeur a demandé une modification --}}
                        <p class="font-semibold">Votre demande de modification est transmise</p>
                        <p class="mt-1 max-w-[64ch] text-encre-2">La boutique revoit le devis et vous prévient par e-mail dès que la nouvelle version est prête. Votre demande figure dans « Échanges ».</p>
                    @else
                        <p class="font-semibold">La boutique prépare votre devis</p>
                        <p class="mt-1 max-w-[64ch] text-encre-2">Elle vérifie le stock, ajoute les frais de livraison et la date, puis vous prévient par e-mail. Les montants ci-dessous sont indicatifs, aux prix pro.</p>
                    @endif
                </div>
            </div>
            @break

        @case('devis_envoye')
            <section class="apparition mt-8 grid gap-6 bg-brun-fonce p-6 text-white sm:p-8 lg:grid-cols-[1fr_auto] lg:items-end lg:gap-12" style="animation-delay: 60ms" aria-labelledby="titre-reponse"
                     x-data="{ modifier: {{ $errors->has('message') ? 'true' : 'false' }} }">
                <div>
                    <h2 id="titre-reponse" class="titre-display text-[1.6rem] sm:text-[1.9rem]">Votre devis est prêt</h2>
                    <dl class="mt-4 grid gap-x-10 gap-y-3 sm:grid-cols-[auto_auto] sm:justify-start">
                        <div>
                            <dt class="text-[0.9rem] text-or-doux">Montant total</dt>
                            <dd class="titre-display chiffres text-[1.7rem] text-or">{{ $francs($commande->total) }}</dd>
                        </div>
                        <div>
                            <dt class="text-[0.9rem] text-or-doux">Livraison prévue</dt>
                            <dd class="titre-display text-[1.25rem] sm:mt-1.5">{{ $commande->livraison_prevue_le ? ucfirst($commande->livraison_prevue_le->translatedFormat('l j F')) : 'Date à confirmer' }}</dd>
                        </div>
                    </dl>
                    @if($commande->note_boutique)
                        <p class="mt-5 max-w-[64ch] border-t border-white/15 pt-4 text-white/85"><span class="font-medium text-white">Mot de la boutique :</span> {{ $commande->note_boutique }}</p>
                    @endif
                </div>
                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col">
                    <form method="POST" action="{{ route('pro.commande.accepter', $commande) }}">
                        @csrf
                        <button type="submit" class="btn btn-or btn-lg w-full">Accepter et payer <x-icone nom="fleche" /></button>
                    </form>
                    <button type="button" @click="modifier = !modifier; modifier && $nextTick(() => $refs.message.focus())" :aria-expanded="modifier" aria-controls="demande-modification"
                            class="btn btn-clair w-full">Demander une modification</button>
                    <form method="POST" action="{{ route('pro.commande.refuser', $commande) }}" onsubmit="return confirm('Refuser ce devis ? La boutique en sera informée.')">
                        @csrf
                        <button type="submit" class="btn w-full text-white/80 underline-offset-4 hover:text-white hover:underline">Refuser le devis</button>
                    </form>
                </div>

                {{-- Le devis ne convient pas : le revendeur dit ce qu'il veut changer, la boutique le revoit --}}
                <form id="demande-modification" method="POST" action="{{ route('pro.commande.modification', $commande) }}" x-show="modifier" x-cloak
                      class="border-t border-white/15 pt-6 lg:col-span-2">
                    @csrf
                    <label for="message" class="block font-medium">Que souhaitez-vous changer ?</label>
                    <p id="aide-message" class="mt-1 text-[0.92rem] text-white/75">Un prix, une quantité, un coloris, la date de livraison… La boutique revoit le devis et vous renvoie une nouvelle version.</p>
                    <textarea id="message" name="message" x-ref="message" rows="3" maxlength="1000" required
                              aria-describedby="aide-message{{ $errors->has('message') ? ' erreur-message' : '' }}"
                              class="champ mt-3 min-h-0" placeholder="Ex. : pouvez-vous faire 1 100 F le mètre pour 50 mètres ?">{{ old('message') }}</textarea>
                    @error('message')<p id="erreur-message" class="mt-1.5 text-sm font-medium text-or-clair">{{ $message }}</p>@enderror
                    <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" @click="modifier = false" class="btn btn-clair">Annuler</button>
                        <button type="submit" class="btn btn-or">Envoyer à la boutique <x-icone nom="fleche" /></button>
                    </div>
                </form>
            </section>
            @break

        @case('devis_refuse')
            <div class="apparition mt-8 flex flex-col gap-4 bg-carte p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6" style="animation-delay: 60ms">
                <p class="text-encre-2">Vous avez refusé ce devis. Vous pouvez reprendre ses quantités pour une nouvelle demande.</p>
                <a href="{{ route('pro.commander', ['depuis' => $commande->id]) }}" class="btn btn-brun shrink-0">Refaire une demande</a>
            </div>
            @break

        @default
            @include('partials.suivi-commande', ['statut' => $commande->statut])
            @if($commande->estAPayer())
                <div class="mt-4 flex flex-col gap-4 bg-alerte-doux p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <p class="flex items-start gap-3"><x-icone nom="carte-bancaire" class="mt-1 size-5 shrink-0 text-alerte" /> <span>Devis accepté : il reste à régler la commande pour qu'elle soit préparée.</span></p>
                    <a href="{{ route('paiement', $commande) }}" class="btn btn-brun shrink-0">Payer {{ $francs($commande->total) }}</a>
                </div>
            @endif
    @endswitch

    <div class="mt-8 grid items-start gap-8 lg:grid-cols-[1fr_22rem] lg:gap-12">
        <section class="bg-carte" aria-labelledby="titre-lignes">
            <h2 id="titre-lignes" class="titre-display px-6 pt-6 text-[1.3rem]">Tissus commandés</h2>
            <div class="mt-3 overflow-x-auto">
                <table class="tableau">
                    <thead>
                        <tr>
                            <th scope="col">Tissu</th>
                            <th scope="col">Format</th>
                            <th scope="col" class="text-right">Qté</th>
                            <th scope="col" class="text-right">Prix pro</th>
                            <th scope="col" class="text-right">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->items as $item)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <span class="size-11 shrink-0 overflow-hidden bg-papier-2">
                                            @if($item->produit?->image)
                                                <img src="{{ asset($item->produit->image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                            @endif
                                        </span>
                                        <span class="font-medium">{{ $item->produit->nom ?? 'Tissu retiré du catalogue' }}</span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap">{{ $item->libelleFormat() }}</td>
                                <td class="chiffres text-right">{{ $item->quantiteLisible() }}</td>
                                <td class="chiffres whitespace-nowrap text-right">{{ $francs($item->prix_unitaire) }}</td>
                                <td class="chiffres whitespace-nowrap text-right font-semibold">{{ $francs($item->sous_total) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <dl class="space-y-2.5 border-t border-trait px-6 py-5">
                @if($commande->frais_livraison !== null)
                    <div class="flex justify-between gap-4 text-[0.95rem]">
                        <dt class="text-encre-2">Tissus</dt>
                        <dd class="chiffres">{{ $francs($sousTotal) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 text-[0.95rem]">
                        <dt class="text-encre-2">Livraison</dt>
                        <dd class="chiffres">{{ $commande->frais_livraison ? $francs($commande->frais_livraison) : 'Offerte' }}</dd>
                    </div>
                @endif
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="font-medium">{{ $commande->statut === 'devis_demande' ? 'Total indicatif' : 'Total' }}</dt>
                    <dd class="titre-display chiffres text-[1.7rem] text-brun">{{ $francs($commande->total) }}</dd>
                </div>
            </dl>
        </section>

        <aside class="space-y-4">
            <section class="bg-carte p-6" aria-labelledby="titre-livraison">
                <h2 id="titre-livraison" class="titre-display text-[1.15rem]">Livraison</h2>
                <dl class="mt-3 space-y-3 text-[0.95rem]">
                    <div>
                        <dt class="text-encre-2">{{ $commande->mode_livraison === 'boutique' ? 'Mode' : 'Adresse' }}</dt>
                        <dd class="font-medium">{{ $commande->mode_livraison === 'boutique' ? 'Retrait en boutique' : $commande->adresse_livraison }}</dd>
                    </div>
                    @if($commande->livraison_souhaitee_le)
                        <div>
                            <dt class="text-encre-2">Date souhaitée</dt>
                            <dd class="font-medium">{{ ucfirst($commande->livraison_souhaitee_le->translatedFormat('l j F Y')) }}</dd>
                        </div>
                    @endif
                    @if($commande->livraison_prevue_le && $commande->statut !== 'devis_envoye')
                        <div>
                            <dt class="text-encre-2">Date prévue par la boutique</dt>
                            <dd class="font-medium">{{ ucfirst($commande->livraison_prevue_le->translatedFormat('l j F Y')) }}</dd>
                        </div>
                    @endif
                </dl>
            </section>

            @if($commande->note_client || ($commande->note_boutique && $commande->statut !== 'devis_envoye'))
                <section class="bg-carte p-6" aria-labelledby="titre-notes">
                    <h2 id="titre-notes" class="titre-display text-[1.15rem]">Échanges</h2>
                    <dl class="mt-3 space-y-3 text-[0.95rem]">
                        @if($commande->note_client)
                            <div><dt class="text-encre-2">Votre note</dt><dd class="whitespace-pre-line">{{ $commande->note_client }}</dd></div>
                        @endif
                        @if($commande->note_boutique && $commande->statut !== 'devis_envoye')
                            <div><dt class="text-encre-2">Mot de la boutique</dt><dd class="whitespace-pre-line">{{ $commande->note_boutique }}</dd></div>
                        @endif
                    </dl>
                </section>
            @endif

            @if($commande->statut !== 'devis_refuse')
                <a href="{{ route('pro.commander', ['depuis' => $commande->id]) }}" class="btn btn-contour w-full">Recommander ces quantités</a>
            @endif
            @if($commande->tracking_number && !$commande->estDevis())
                <a href="{{ route('track', $commande->tracking_number) }}" class="btn btn-fantome w-full">Page de suivi à partager</a>
            @endif
            @if($lienWhatsapp)
                <a href="{{ $lienWhatsapp }}?text={{ rawurlencode('Bonjour, au sujet de ma ' . mb_strtolower($nature) . ' pro n° ' . $numero) }}" target="_blank" rel="noopener noreferrer" class="btn btn-fantome w-full"><x-icone nom="message" /> En parler sur WhatsApp</a>
            @endif
        </aside>
    </div>
</div>

@endsection
