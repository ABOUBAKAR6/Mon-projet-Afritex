@extends('layouts.app')
@section('title', 'Paiement · AfriTex')
@if(str_contains($commande->adresse_livraison ?? '', 'GPS:'))
    @push('tete')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    @endpush
@endif
@section('content')
@php
    $parts = explode(' | ', $commande->adresse_livraison ?? '');
    $gpsIdx = collect($parts)->search(fn ($p) => str_starts_with($p, 'GPS:'));
    $hasGps = $gpsIdx !== false;
    $latVal = '5.3600'; $lngVal = '-4.0083';
    if ($hasGps) {
        $c = explode(', ', str_replace('GPS: ', '', $parts[$gpsIdx]));
        $latVal = $c[0] ?? '5.3600';
        $lngVal = $c[1] ?? '-4.0083';
    }
    $details = array_filter([
        'Destinataire' => $parts[0] ?? null,
        'Téléphone' => $parts[1] ?? null,
        'Ville et commune' => $parts[2] ?? null,
        'Adresse précise' => (isset($parts[3]) && !str_starts_with($parts[3], 'GPS:')) ? $parts[3] : null,
    ]);
    $operateurs = [
        'orange' => ['Orange Money', 'images/payments/orange.png'],
        'mtn' => ['MTN MoMo', 'images/payments/mtn.jpg'],
        'wave' => ['Wave', 'images/payments/wave.png'],
        'moov' => ['Moov Money', 'images/payments/moov.png'],
    ];
@endphp

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    @include('partials.etapes-commande', ['etape' => 2])

    <h1 class="titre-display apparition mt-8 text-[2.2rem] sm:text-[2.8rem]">Comment souhaitez-vous payer ?</h1>
    <p class="apparition mt-2 text-encre-2" style="animation-delay: 60ms">Commande n° <span class="chiffres">{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</span>. Vérifiez l'adresse, puis choisissez votre moyen de paiement.</p>

    <div class="mt-10 grid items-start gap-8 lg:grid-cols-[1fr_24rem] lg:gap-12">
        <div class="space-y-8">
            {{-- ADRESSE --}}
            <section class="bg-carte p-6 sm:p-8" aria-labelledby="titre-livraison">
                <div class="flex items-baseline justify-between gap-4">
                    <h2 id="titre-livraison" class="titre-display text-[1.3rem]">Livraison</h2>
                    <a href="{{ route('commande') }}" class="lien text-[0.95rem]">Modifier l'adresse</a>
                </div>
                <dl class="mt-5 grid gap-x-8 gap-y-4 sm:grid-cols-2">
                    @foreach($details as $libelle => $valeur)
                        <div class="{{ $libelle === 'Adresse précise' ? 'sm:col-span-2' : '' }}">
                            <dt class="text-[0.92rem] text-encre-2">{{ $libelle }}</dt>
                            <dd class="font-medium">{{ $valeur }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if($hasGps)
                    <div id="miniMap" class="carte-livraison relative z-0 mt-6 h-52 w-full border border-trait"></div>
                @endif
            </section>

            {{-- PAIEMENT --}}
            <section class="bg-carte p-6 sm:p-8" aria-labelledby="titre-paiement">
                <h2 id="titre-paiement" class="titre-display text-[1.3rem]">Moyen de paiement</h2>
                <form method="POST" action="{{ route('paiement.payer', $commande->id) }}" id="formPaiement" class="mt-6">
                    @csrf
                    <input type="hidden" name="operateur" id="operateurChoisi">

                    <div class="space-y-3">
                        {{-- MOBILE MONEY --}}
                        <label class="choix" id="label-mobile_money">
                            <input type="radio" name="methode" value="mobile_money" required onclick="selectMethode('mobile_money')" class="size-5">
                            <x-icone nom="mobile" class="size-6 text-brun" />
                            <span class="flex-1">
                                <span class="block font-medium">Mobile Money</span>
                                <span class="block text-[0.92rem] text-encre-2">Orange Money, MTN MoMo, Wave, Moov</span>
                            </span>
                        </label>
                        <div id="zone-mobile_money" class="border-l-2 border-brun bg-papier-2 p-5" style="display:none;">
                            <p class="etiquette">Votre opérateur</p>
                            <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                                @foreach($operateurs as $id => [$nom, $logo])
                                    <button type="button" id="op-{{ $id }}" onclick="choisirOperateur('{{ $id }}', '{{ $nom }}')"
                                            class="op-carte flex h-20 flex-col items-center justify-center gap-1.5 border border-trait-fort bg-carte text-[0.85rem] transition-colors hover:border-brun-trait">
                                        <img src="{{ asset($logo) }}" alt="" class="h-9 w-auto object-contain">
                                        {{ $nom }}
                                    </button>
                                @endforeach
                            </div>
                            <div id="zone-numero-mobile" class="mt-5" style="display:none;">
                                <label for="numeroMobile" class="etiquette">Numéro <span id="labelOperateur"></span></label>
                                <div class="flex gap-2">
                                    <span class="chiffres flex min-h-11 items-center bg-carte px-3.5 font-medium">+225</span>
                                    <input type="tel" name="numero_mobile" id="numeroMobile" placeholder="07 00 00 00 00" class="champ chiffres flex-1" autocomplete="tel-national">
                                </div>
                            </div>
                        </div>

                        {{-- CARTE BANCAIRE --}}
                        <label class="choix" id="label-carte">
                            <input type="radio" name="methode" value="carte" onclick="selectMethode('carte')" class="size-5">
                            <x-icone nom="carte-bancaire" class="size-6 text-brun" />
                            <span class="flex-1">
                                <span class="block font-medium">Carte bancaire</span>
                                <span class="block text-[0.92rem] text-encre-2">Visa, Mastercard, Maestro</span>
                            </span>
                        </label>
                        <div id="zone-carte" class="border-l-2 border-brun bg-papier-2 p-5" style="display:none;">
                            <div>
                                <label for="numero_carte" class="etiquette">Numéro de carte</label>
                                <input id="numero_carte" type="text" name="numero_carte" inputmode="numeric" autocomplete="cc-number" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCarte(this)" class="champ chiffres tracking-wider">
                            </div>
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div>
                                    <label for="expiry" class="etiquette">Expiration</label>
                                    <input id="expiry" type="text" name="expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM / AA" maxlength="7" oninput="formatExpiry(this)" class="champ chiffres">
                                </div>
                                <div>
                                    <label for="cvv" class="etiquette">Code de sécurité</label>
                                    <input id="cvv" type="password" name="cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="3 chiffres" maxlength="3" class="champ chiffres">
                                </div>
                            </div>
                        </div>

                        {{-- ESPÈCES --}}
                        <label class="choix" id="label-especes">
                            <input type="radio" name="methode" value="especes" onclick="selectMethode('especes')" class="size-5">
                            <x-icone nom="billets" class="size-6 text-brun" />
                            <span class="flex-1">
                                <span class="block font-medium">Paiement à la livraison</span>
                                <span class="block text-[0.92rem] text-encre-2">En espèces, à la réception du colis</span>
                            </span>
                        </label>
                        <div id="zone-especes" class="border-l-2 border-succes bg-succes-doux p-5 text-succes" style="display:none;">
                            <p>Le livreur encaissera <strong class="chiffres">{{ number_format($commande->total, 0, ',', ' ') }} F</strong> à la remise de votre commande.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-brun btn-lg mt-8 w-full" id="btnPayer">Choisissez un moyen de paiement</button>
                    <p class="mt-3 flex items-center justify-center gap-2 text-[0.9rem] text-encre-2"><x-icone nom="bouclier" class="size-4" /> Paiement sécurisé par FedaPay</p>
                </form>
            </section>
        </div>

        {{-- RÉCAPITULATIF --}}
        <aside class="bg-carte p-6 lg:sticky lg:top-28" aria-labelledby="titre-recap">
            <h2 id="titre-recap" class="titre-display text-[1.3rem]">Votre commande</h2>
            <ul class="mt-4 divide-y divide-trait">
                @foreach($commande->items as $item)
                    <li class="flex items-center gap-3 py-3 text-[0.95rem]">
                        <span class="size-12 shrink-0 overflow-hidden bg-papier-2">
                            @if($item->produit && $item->produit->image)
                                <img src="{{ asset($item->produit->image) }}" alt="" class="h-full w-full object-cover">
                            @endif
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate font-medium">{{ $item->produit->nom ?? 'Produit' }}</span>
                            <span class="block text-[0.88rem] text-encre-2"><span class="chiffres">{{ $item->quantite }}</span> × {{ ucfirst(str_replace('_', ' ', $item->type_commande)) }}</span>
                        </span>
                        <span class="chiffres whitespace-nowrap font-medium">{{ number_format($item->sous_total, 0, ',', ' ') }} F</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-2 space-y-2.5 border-t border-trait pt-4 text-[0.98rem]">
                @if(($commande->frais_livraison ?? 0) > 0)
                    <div class="flex justify-between"><dt class="text-encre-2">Livraison</dt><dd class="chiffres">{{ number_format($commande->frais_livraison, 0, ',', ' ') }} F</dd></div>
                @endif
                <div class="flex items-baseline justify-between border-t border-trait pt-4">
                    <dt class="font-medium">Total à payer</dt>
                    <dd class="titre-display chiffres text-[1.8rem] text-brun">{{ number_format($commande->total, 0, ',', ' ') }} F</dd>
                </div>
            </dl>
        </aside>
    </div>
</div>

@if($hasGps)
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif
<script>
const totalAmount = '{{ number_format($commande->total, 0, ",", " ") }}';

function selectMethode(val) {
    ['mobile_money', 'carte', 'especes'].forEach(m => {
        const zone = document.getElementById('zone-' + m);
        if (zone) zone.style.display = 'none';
    });
    const zone = document.getElementById('zone-' + val);
    if (zone) zone.style.display = 'block';
    document.querySelector(`input[value="${val}"]`).checked = true;

    const libelles = {
        especes: 'Confirmer la commande, ' + totalAmount + ' F',
        mobile_money: 'Payer ' + totalAmount + ' F par Mobile Money',
        carte: 'Payer ' + totalAmount + ' F par carte',
    };
    document.getElementById('btnPayer').textContent = libelles[val];
}

function choisirOperateur(id, nom) {
    document.querySelectorAll('.op-carte').forEach(el => {
        el.classList.remove('!border-brun', 'bg-brun-doux');
        el.setAttribute('aria-pressed', 'false');
    });
    const el = document.getElementById('op-' + id);
    if (el) {
        el.classList.add('!border-brun', 'bg-brun-doux');
        el.setAttribute('aria-pressed', 'true');
    }
    document.getElementById('operateurChoisi').value = id;
    document.getElementById('labelOperateur').textContent = nom;
    document.getElementById('zone-numero-mobile').style.display = 'block';
}

function formatCarte(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = val.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(input) {
    let val = input.value.replace(/\D/g, '');
    if (val.length >= 2) val = val.substring(0, 2) + ' / ' + val.substring(2, 4);
    input.value = val;
}

@if($hasGps)
document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('miniMap', { zoomControl: false, dragging: false, scrollWheelZoom: false }).setView([{{ $latVal }}, {{ $lngVal }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);
    var icon = L.divIcon({ html: '<span class="repere-afritex"></span>', iconSize: [22, 22], iconAnchor: [11, 11], className: '' });
    L.marker([{{ $latVal }}, {{ $lngVal }}], { icon: icon }).addTo(map);
    setTimeout(() => map.invalidateSize(), 300);
});
@endif
</script>

@endsection
