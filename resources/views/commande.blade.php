@extends('layouts.app')
@section('title', 'Livraison · ' . boutique('nom'))
@push('tete')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush
@section('content')
@php
    $formats = [
        'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
        'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
    ];
    $villes = ['Abidjan', 'Bouaké', 'Yamoussoukro', 'Daloa', 'Korhogo', 'San-Pédro', 'Man', 'Gagnoa', 'Divo', 'Abengourou'];
    $fraisDomicile = $total >= 50000 ? 0 : 1500;
    $indicatifs = ['+225' => 'Côte d\'Ivoire +225', '+221' => 'Sénégal +221', '+223' => 'Mali +223', '+226' => 'Burkina Faso +226', '+228' => 'Togo +228', '+229' => 'Bénin +229', '+224' => 'Guinée +224', '+33' => 'France +33'];
@endphp

<div class="conteneur pb-20 pt-4 sm:pt-10 lg:pb-28 lg:pt-14">
    @include('partials.etapes-commande', ['etape' => 1])

    <h1 class="titre-display apparition mt-4 text-[1.5rem] leading-tight sm:mt-8 sm:text-[2.8rem]">Où livrer votre commande ?</h1>
    <p class="apparition mt-2 hidden max-w-[60ch] text-encre-2 sm:block" style="animation-delay: 60ms">Indiquez vos coordonnées et placez le repère sur la carte : le livreur vous trouvera sans détour.</p>

    <form method="POST" action="{{ route('commande.confirmer') }}" id="formCommande" class="mt-4 grid items-start gap-3 sm:mt-10 sm:gap-8 lg:grid-cols-[1fr_24rem] lg:gap-12">
        @csrf
        <div class="space-y-3 sm:space-y-8">
            {{-- COORDONNÉES --}}
            <section class="bg-carte p-4 sm:p-8" aria-labelledby="titre-coordonnees">
                <h2 id="titre-coordonnees" class="titre-display text-[1.1rem] sm:text-[1.3rem]">Vos coordonnées</h2>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:mt-6 sm:gap-5">
                    <div>
                        <label for="prenom" class="etiquette">Prénom</label>
                        <input id="prenom" type="text" name="prenom" required autocomplete="given-name" placeholder="Kouamé"
                               value="{{ old('prenom', auth()->check() ? explode(' ', auth()->user()->name)[0] : '') }}" class="champ">
                        @error('prenom')<p class="erreur">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="nom_complet" class="etiquette">Nom</label>
                        <input id="nom_complet" type="text" name="nom_complet" required autocomplete="family-name" placeholder="Konan" value="{{ old('nom_complet') }}" class="champ">
                        @error('nom_complet')<p class="erreur">{{ $message }}</p>@enderror
                    </div>
                    <div class="col-span-2">
                        <label for="telephone" class="etiquette">Téléphone</label>
                        <div class="flex gap-2">
                            <label for="indicatif" class="sr-only">Indicatif du pays</label>
                            <select id="indicatif" name="indicatif" class="champ w-auto max-w-[11rem]">
                                @foreach($indicatifs as $code => $libelle)
                                    <option value="{{ $code }}">{{ $libelle }}</option>
                                @endforeach
                            </select>
                            <input id="telephone" type="tel" name="telephone" required autocomplete="tel-national" placeholder="07 12 34 56 78" value="{{ old('telephone') }}" class="champ chiffres min-w-0 flex-1">
                        </div>
                        @error('telephone')<p class="erreur">{{ $message }}</p>@enderror
                    </div>
                    @guest
                        {{-- Commande sans compte : le compte est créé à la confirmation, l'e-mail sert seulement au suivi --}}
                        <div class="col-span-2">
                            <label for="email" class="etiquette">E-mail <span class="font-normal text-encre-2">(facultatif, pour recevoir le suivi)</span></label>
                            <input id="email" type="email" name="email" autocomplete="email" inputmode="email" placeholder="vous@exemple.com" value="{{ old('email') }}" class="champ">
                            @error('email')<p class="erreur">{{ $message }}</p>@enderror
                            <p class="aide">Pas besoin de créer un compte. Déjà client ? <a href="{{ route('login') }}" class="lien">Connectez-vous</a>.</p>
                        </div>
                    @endguest
                </div>
            </section>

            {{-- ADRESSE --}}
            <section class="bg-carte p-4 sm:p-8" aria-labelledby="titre-adresse">
                <h2 id="titre-adresse" class="titre-display text-[1.1rem] sm:text-[1.3rem]">Adresse de livraison</h2>
                <div class="mt-3 grid grid-cols-2 gap-3 sm:mt-6 sm:grid-cols-3 sm:gap-5">
                    <div>
                        <label for="villeSelect" class="etiquette">Ville</label>
                        <select name="ville" id="villeSelect" required onchange="updateCommunes()" class="champ">
                            <option value="">Choisir une ville</option>
                            @foreach($villes as $ville)
                                <option value="{{ $ville }}">{{ $ville }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="communeSelect" class="etiquette">Commune</label>
                        <select name="commune" id="communeSelect" required class="champ">
                            <option value="">D'abord la ville</option>
                        </select>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="quartierInput" class="etiquette">Quartier <span class="font-normal text-encre-2">(facultatif)</span></label>
                        <input type="text" name="quartier" id="quartierInput" placeholder="Riviera 2" class="champ">
                    </div>
                </div>

                <div class="mt-4 sm:mt-6">
                    <label for="searchInput" class="etiquette">Position exacte sur la carte</label>
                    <div class="relative">
                        <x-icone nom="recherche" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-encre-3" />
                        <input type="text" id="searchInput" placeholder="Rechercher une adresse, un repère connu…" class="champ pl-10" oninput="rechercherAdresse()" autocomplete="off" aria-describedby="aide-carte">
                        <div id="suggestions" class="absolute inset-x-0 top-full z-dropdown max-h-60 overflow-y-auto border border-t-0 border-trait-fort bg-carte" style="display:none;"></div>
                    </div>
                    <p id="aide-carte" class="aide hidden sm:block">Votre position est détectée automatiquement. Vous livrez ailleurs ? Cherchez l'adresse ou touchez la carte, puis faites glisser le repère.</p>
                    <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 sm:mt-3 sm:gap-y-2">
                        <button type="button" id="btnLocaliser" onclick="localiser()" class="btn btn-contour btn-sm"><x-icone nom="repere" /> Me localiser</button>
                        <p id="etatLocalisation" class="text-[0.92rem] text-encre-2" role="status" aria-live="polite"></p>
                    </div>
                    <div id="map" class="carte-livraison relative z-0 mt-2 h-52 w-full border border-trait sm:mt-3 sm:h-80"></div>
                    <p id="adresseDetectee" class="mt-3 flex items-start gap-2 bg-brun-doux p-3.5 text-[0.95rem] text-brun-fonce" style="display:none;">
                        <x-icone nom="repere" class="mt-0.5 size-4 shrink-0" />
                        <span><span class="font-medium">Adresse repérée :</span> <span id="texteAdresse"></span></span>
                    </p>
                </div>

                <div class="mt-4 sm:mt-6">
                    <label for="note_livreur" class="etiquette">Note pour le livreur <span class="font-normal text-encre-2">(facultatif)</span></label>
                    <textarea id="note_livreur" name="note_livreur" rows="2" placeholder="Portail bleu, deuxième étage…" class="champ min-h-0 sm:min-h-[6rem]"></textarea>
                </div>

                <input type="hidden" name="adresse_livraison" id="adresseComplete">
                <input type="hidden" name="latitude" id="latInput">
                <input type="hidden" name="longitude" id="lngInput">
            </section>

            {{-- MODE DE LIVRAISON --}}
            <section class="bg-carte p-4 sm:p-8" aria-labelledby="titre-mode">
                <h2 id="titre-mode" class="titre-display text-[1.1rem] sm:text-[1.3rem]">Mode de livraison</h2>
                <div class="mt-3 grid gap-2 sm:mt-6 sm:grid-cols-2 sm:gap-3">
                    <label class="choix !gap-3 !p-3 sm:!gap-4 sm:!p-4" id="label-domicile">
                        <input type="radio" name="mode_livraison" value="domicile" checked onchange="selectLivraison(this)" class="size-5">
                        <span class="flex-1">
                            <span class="block font-medium">Livraison à domicile</span>
                            <span class="block text-[0.92rem] text-encre-2">Chez vous sous 24 à 48 h</span>
                        </span>
                        <span class="chiffres font-semibold {{ $fraisDomicile ? '' : 'text-succes' }}">{{ $fraisDomicile ? '1 500 F' : 'Offerte' }}</span>
                    </label>
                    <label class="choix !gap-3 !p-3 sm:!gap-4 sm:!p-4" id="label-boutique">
                        <input type="radio" name="mode_livraison" value="boutique" onchange="selectLivraison(this)" class="size-5">
                        <span class="flex-1">
                            <span class="block font-medium">Retrait en boutique</span>
                            <span class="block text-[0.92rem] text-encre-2">Disponible dès le lendemain</span>
                        </span>
                        <span class="font-semibold text-succes">Gratuit</span>
                    </label>
                </div>
                <input type="hidden" name="frais_livraison" id="fraisLivraison" value="{{ $fraisDomicile }}">
            </section>
        </div>

        {{-- RÉCAPITULATIF --}}
        <aside class="bg-carte p-4 sm:p-6 lg:sticky lg:top-28" aria-labelledby="titre-recap">
            <h2 id="titre-recap" class="titre-display text-[1.1rem] sm:text-[1.3rem]">Votre commande</h2>
            <ul class="mt-4 divide-y divide-trait">
                @foreach($panier as $item)
                    <li class="flex justify-between gap-4 py-3 text-[0.95rem]">
                        <span>
                            <span class="block font-medium">{{ $item['nom'] }}</span>
                            <span class="block text-[0.88rem] text-encre-2">{{ $formats[$item['type_commande']] ?? $item['type_commande'] }} × <span class="chiffres">{{ $item['quantite'] }}</span></span>
                        </span>
                        <span class="chiffres whitespace-nowrap font-medium">{{ number_format($item['sous_total'], 0, ',', ' ') }} F</span>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-2 space-y-2.5 border-t border-trait pt-4 text-[0.98rem]">
                <div class="flex justify-between"><dt class="text-encre-2">Sous-total</dt><dd class="chiffres">{{ number_format($total, 0, ',', ' ') }} F</dd></div>
                <div class="flex justify-between"><dt class="text-encre-2">Livraison</dt><dd id="affichageFrais" class="chiffres">{{ $fraisDomicile ? '1 500 F' : 'Offerte' }}</dd></div>
                <div class="flex items-baseline justify-between border-t border-trait pt-4">
                    <dt class="font-medium">Total</dt>
                    <dd id="totalFinal" class="titre-display chiffres text-[1.8rem] text-brun">{{ number_format($total + $fraisDomicile, 0, ',', ' ') }} F</dd>
                </div>
            </dl>
            <button type="submit" onclick="assemblerAdresse()" class="btn btn-brun btn-lg mt-4 w-full sm:mt-6">Continuer vers le paiement <x-icone nom="fleche" /></button>
            <a href="{{ route('panier') }}" class="btn btn-fantome mt-2 w-full">Modifier mon panier</a>
        </aside>
    </form>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
var map = L.map('map').setView([5.3600, -4.0083], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

var markerIcon = L.divIcon({ html: '<span class="repere-afritex"></span>', iconSize: [22, 22], iconAnchor: [11, 11], className: '' });
var marker = null;

map.on('click', function (e) {
    choisirALaMain();
    placerMarqueur(e.latlng.lat, e.latlng.lng);
    reverseGeocode(e.latlng.lat, e.latlng.lng);
});

function placerMarqueur(lat, lng) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng], { icon: markerIcon, draggable: true }).addTo(map);
    marker.on('dragstart', choisirALaMain);
    marker.on('dragend', function (e) {
        var pos = e.target.getLatLng();
        reverseGeocode(pos.lat, pos.lng);
        document.getElementById('latInput').value = pos.lat;
        document.getElementById('lngInput').value = pos.lng;
    });
    document.getElementById('latInput').value = lat;
    document.getElementById('lngInput').value = lng;
}

var searchTimer = null;
function rechercherAdresse() {
    const query = document.getElementById('searchInput').value.trim();
    const suggestionsDiv = document.getElementById('suggestions');
    if (query.length < 3) { suggestionsDiv.style.display = 'none'; return; }
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query + ', Côte d\'Ivoire')}&limit=5&addressdetails=1`)
            .then(res => res.json())
            .then(data => {
                suggestionsDiv.innerHTML = '';
                if (data.length === 0) {
                    suggestionsDiv.innerHTML = '<p class="px-3.5 py-3 text-[0.92rem] text-encre-2">Aucune adresse trouvée. Essayez un repère proche.</p>';
                    suggestionsDiv.style.display = 'block';
                    return;
                }
                data.forEach(item => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'block w-full border-b border-trait px-3.5 py-3 text-left text-[0.92rem] transition-colors hover:bg-brun-doux';
                    btn.textContent = item.display_name;
                    btn.onclick = () => {
                        choisirALaMain();
                        map.setView([parseFloat(item.lat), parseFloat(item.lon)], 16);
                        placerMarqueur(parseFloat(item.lat), parseFloat(item.lon));
                        afficherAdresse(item.display_name);
                        document.getElementById('searchInput').value = item.display_name;
                        suggestionsDiv.style.display = 'none';
                        if (item.address) {
                            const q = item.address.suburb || item.address.neighbourhood || item.address.quarter || '';
                            if (q) document.getElementById('quartierInput').value = q;
                        }
                    };
                    suggestionsDiv.appendChild(btn);
                });
                suggestionsDiv.style.display = 'block';
            });
    }, 400);
}

document.addEventListener('click', function (e) {
    const zone = document.getElementById('suggestions');
    if (!document.getElementById('searchInput').contains(e.target) && !zone.contains(e.target)) {
        zone.style.display = 'none';
    }
});

function reverseGeocode(lat, lng) {
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&addressdetails=1`)
        .then(res => res.json())
        .then(data => {
            if (data && data.display_name) {
                afficherAdresse(data.display_name);
                document.getElementById('searchInput').value = data.display_name;
                if (data.address) {
                    const q = data.address.suburb || data.address.neighbourhood || data.address.quarter || '';
                    if (q) document.getElementById('quartierInput').value = q;
                    remplirVilleCommune(data.address);
                }
            }
        });
}

// Ville et commune déduites de l'adresse trouvée, seulement si le client ne les a pas déjà choisies
const sansAccents = (s) => (s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
function remplirVilleCommune(a) {
    const villeSelect = document.getElementById('villeSelect');
    const communeSelect = document.getElementById('communeSelect');
    const lieux = [a.city, a.town, a.village, a.municipality, a.county, a.state].map(sansAccents);
    const ville = Object.keys(communes).find((v) => lieux.some((l) => l && l.includes(sansAccents(v))));
    if (!ville) return;
    if (!villeSelect.value) {
        villeSelect.value = ville;
        updateCommunes(false);
    }
    if (villeSelect.value === ville && !communeSelect.value) {
        const quartiers = [a.city_district, a.borough, a.suburb, a.quarter, a.neighbourhood].map(sansAccents);
        const commune = communes[ville].find((c) => quartiers.some((x) => x && (x.includes(sansAccents(c)) || sansAccents(c).includes(x))));
        if (commune) communeSelect.value = commune;
    }
}

/*
  Localisation en temps réel : dès l'ouverture de la page, le repère suit la position de l'appareil
  pendant qu'elle se précise (GPS), puis s'arrête quand elle est précise à 25 m ou au bout de 30 s.
  Si le client place le repère lui-même (clic, glisser, recherche), son choix l'emporte.
*/
var suivi = null, minuterieSuivi = null, cercle = null, meilleurePrecision = Infinity, dernierGeocodage = 0, manuel = false;

function dire(texte, ton) {
    const etat = document.getElementById('etatLocalisation');
    etat.textContent = texte;
    etat.className = 'text-[0.92rem] ' + (ton === 'erreur' ? 'text-danger' : ton === 'ok' ? 'text-succes' : 'text-encre-2');
}

function arreterSuivi() {
    if (suivi !== null) navigator.geolocation.clearWatch(suivi);
    suivi = null;
    clearTimeout(minuterieSuivi);
}

function choisirALaMain() {
    if (manuel) return;
    manuel = true;
    arreterSuivi();
    if (cercle) { map.removeLayer(cercle); cercle = null; }
    dire('Repère placé à la main.');
}

function localiser() {
    if (!('geolocation' in navigator)) {
        dire('Votre navigateur ne permet pas la localisation. Placez le repère sur la carte.', 'erreur');
        return;
    }
    if (!window.isSecureContext) {
        dire('La localisation automatique demande une connexion sécurisée (https). Placez le repère sur la carte.', 'erreur');
        return;
    }
    manuel = false;
    meilleurePrecision = Infinity;
    arreterSuivi();
    dire('Recherche de votre position…');
    suivi = navigator.geolocation.watchPosition(surPosition, surErreur, { enableHighAccuracy: true, maximumAge: 0, timeout: 20000 });
    minuterieSuivi = setTimeout(() => {
        arreterSuivi();
        if (meilleurePrecision < Infinity) dire(`Position trouvée à ${Math.round(meilleurePrecision)} m près. Ajustez le repère si besoin.`, 'ok');
    }, 30000);
}

function surPosition(position) {
    if (manuel) return;
    const { latitude: lat, longitude: lng, accuracy } = position.coords;
    if (accuracy > meilleurePrecision) return; // une mesure moins précise que la précédente ne déplace pas le repère
    const premiere = meilleurePrecision === Infinity;
    meilleurePrecision = accuracy;

    placerMarqueur(lat, lng);
    if (cercle) {
        cercle.setLatLng([lat, lng]).setRadius(accuracy);
    } else {
        cercle = L.circle([lat, lng], { radius: accuracy, color: '{{ couleur('accent') }}', weight: 1, fillColor: '{{ couleur('accent') }}', fillOpacity: 0.12, interactive: false }).addTo(map);
    }
    if (premiere) map.setView([lat, lng], accuracy < 150 ? 17 : 15);
    else map.panTo([lat, lng]);

    const precise = accuracy <= 25;
    dire(precise ? `Vous êtes ici, à ${Math.round(accuracy)} m près.` : `Position trouvée à ${Math.round(accuracy)} m près, elle se précise…`, precise ? 'ok' : null);
    if (precise) arreterSuivi();

    // Adresse : au plus une demande toutes les 4 s (règle d'usage de Nominatim)
    const maintenant = Date.now();
    if (premiere || precise || maintenant - dernierGeocodage > 4000) {
        dernierGeocodage = maintenant;
        reverseGeocode(lat, lng);
    }
}

function surErreur(erreur) {
    if (meilleurePrecision < Infinity && erreur.code !== 1) return; // on garde la position déjà trouvée
    arreterSuivi();
    const messages = {
        1: 'Localisation refusée. Autorisez-la dans votre navigateur, ou placez le repère sur la carte.',
        2: 'Position introuvable pour le moment. Touchez « Me localiser » pour réessayer.',
        3: 'La localisation prend trop de temps. Touchez « Me localiser » pour réessayer.',
    };
    dire(messages[erreur.code] || messages[2], 'erreur');
}

function afficherAdresse(texte) {
    document.getElementById('texteAdresse').textContent = texte;
    document.getElementById('adresseDetectee').style.display = 'flex';
}

const communes = {
    'Abidjan': ['Abobo','Adjamé','Attécoubé','Cocody','Koumassi','Marcory','Plateau','Port-Bouët','Treichville','Yopougon'],
    'Bouaké': ['Bouaké Centre','Koko','Sokoura','Belleville'],
    'Yamoussoukro': ['Habitat','N\'Zuessi','Dioulakro','Morofè'],
    'Daloa': ['Daloa Centre','Lobia','Préfecture'],
    'Korhogo': ['Korhogo Centre','Soba','Kassiré'],
    'San-Pédro': ['San-Pédro Centre','Bardo','Cité'],
    'Man': ['Man Centre','Libreville','Domoraud'],
    'Gagnoa': ['Gagnoa Centre','Dioulabougou'],
    'Divo': ['Divo Centre','Guitry'],
    'Abengourou': ['Abengourou Centre','Zaranou'],
};

function updateCommunes(centrer) {
    const ville = document.getElementById('villeSelect').value;
    const select = document.getElementById('communeSelect');
    select.innerHTML = '<option value="">Choisir une commune</option>';
    if (communes[ville]) {
        communes[ville].forEach(c => {
            const opt = document.createElement('option');
            opt.value = c; opt.textContent = c;
            select.appendChild(opt);
        });
    }
    const coords = {
        'Abidjan': [5.3600, -4.0083], 'Bouaké': [7.6833, -5.0333],
        'Yamoussoukro': [6.8206, -5.2736], 'Daloa': [6.8742, -6.4502],
        'Korhogo': [9.4579, -5.6290], 'San-Pédro': [4.7485, -6.6363],
        'Man': [7.4128, -7.5538], 'Gagnoa': [6.1319, -5.9503],
        'Divo': [5.8370, -5.3564], 'Abengourou': [6.7297, -3.4962],
    };
    // Choisir une ville recentre la carte, sauf quand la ville vient de la localisation automatique
    if (centrer !== false && coords[ville]) {
        choisirALaMain();
        map.setView(coords[ville], 13);
    }
}

function assemblerAdresse() {
    const prenom = document.querySelector('[name=prenom]').value;
    const nom = document.querySelector('[name=nom_complet]').value;
    const indicatif = document.querySelector('[name=indicatif]').value;
    const tel = document.querySelector('[name=telephone]').value;
    const ville = document.querySelector('[name=ville]').value;
    const commune = document.querySelector('[name=commune]').value;
    const quartier = document.querySelector('[name=quartier]').value;
    const adresseMap = document.getElementById('texteAdresse').textContent;
    const note = document.querySelector('[name=note_livreur]').value;
    const lat = document.getElementById('latInput').value;
    const lng = document.getElementById('lngInput').value;

    let complet = `${prenom} ${nom} | ${indicatif}${tel} | ${ville}, ${commune}`;
    if (quartier) complet += `, ${quartier}`;
    if (adresseMap) complet += ` | ${adresseMap}`;
    if (lat && lng) complet += ` | GPS: ${parseFloat(lat).toFixed(5)}, ${parseFloat(lng).toFixed(5)}`;
    if (note) complet += ` | Note: ${note}`;

    document.getElementById('adresseComplete').value = complet;
}

const baseTotal = {{ $total }};
// Livraison à domicile : 1 500 F, offerte dès 50 000 F d'achat (règle annoncée sur le site)
function selectLivraison(radio) {
    const frais = radio.value === 'domicile' && baseTotal < 50000 ? 1500 : 0;
    document.getElementById('fraisLivraison').value = frais;
    document.getElementById('affichageFrais').textContent = frais > 0 ? '1 500 F' : (radio.value === 'domicile' ? 'Offerte' : 'Gratuit');
    document.getElementById('totalFinal').textContent = new Intl.NumberFormat('fr-FR').format(baseTotal + frais).replace(/\s/g, ' ') + ' F';
}

setTimeout(() => map.invalidateSize(), 300);

// Localisation automatique dès l'ouverture de la page
localiser();
</script>

@endsection
