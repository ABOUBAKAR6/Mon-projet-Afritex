@extends('layouts.app')
@section('content')

<style>
.order-container { max-width: 1100px; margin: 0 auto; padding: 40px 20px; font-family: 'DM Sans', sans-serif; }
.order-header { text-align: center; margin-bottom: 40px; }
.order-header h2 { font-family: 'Playfair Display', serif; font-size: 32px; color: #4A1B0C; font-weight: 700; margin-bottom: 10px; }
.order-header p { color: #7a6a5a; font-size: 16px; }

.order-steps { display: flex; justify-content: center; align-items: center; margin-bottom: 50px; gap: 10px; }
.step-item { display: flex; align-items: center; gap: 10px; }
.step-dot { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; transition: all 0.3s; border: 2px solid #e8ddd3; background: white; color: #aaa; }
.step-dot.active { background: #8B4513; color: white; border-color: #8B4513; box-shadow: 0 0 15px rgba(139,69,19,0.3); }
.step-line { width: 60px; height: 3px; background: #e8ddd3; transition: all 0.3s; }
.step-line.active { background: #8B4513; }
.step-text { font-size: 14px; font-weight: 600; color: #aaa; }
.step-text.active { color: #4A1B0C; }

.order-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start; }
@media (max-width: 992px) { .order-grid { grid-template-columns: 1fr; } }

.order-section { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #f0ebe5; margin-bottom: 24px; }
.section-title { font-family: 'Playfair Display', serif; font-size: 20px; color: #4A1B0C; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
.section-title span { background: #FDF6EE; color: #8B4513; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }

.form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 20px; }
.input-group { margin-bottom: 20px; }
.input-label { font-size: 13px; color: #7a6a5a; font-weight: 600; margin-bottom: 6px; display: block; }
.input-field { width: 100%; padding: 12px 16px; border: 1.5px solid #e8ddd3; border-radius: 12px; font-size: 15px; outline: none; transition: all 0.2s; background: white; box-sizing: border-box; }
.input-field:focus { border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139,69,19,0.1); }

.delivery-options { display: flex; flex-direction: column; gap: 12px; }
.delivery-item { display: flex; align-items: center; gap:15px; padding: 18px; border: 2px solid #e8ddd3; border-radius: 16px; cursor: pointer; transition: all 0.2s; background: white; position: relative; }
.delivery-item:hover { border-color: #C8963E; background: #FFFBF5; transform: translateX(5px); }
.delivery-item.selected { border-color: #8B4513; background: #FDF6EE; box-shadow: 0 4px 12px rgba(139,69,19,0.1); }
.delivery-item input { width: 20px; height: 20px; accent-color: #8B4513; }
.delivery-info { flex: 1; }
.delivery-name { font-weight: 700; color: #4A1B0C; font-size: 16px; }
.delivery-desc { font-size: 13px; color: #7a6a5a; }

.map-section { margin-top: 20px; }
.search-box { position: relative; margin-bottom: 15px; }
.search-input { width: 100%; padding: 12px 44px 12px 14px; border: 1.5px solid #e8ddd3; border-radius: 12px; font-size: 15px; outline: none; transition: all 0.2s; }
.search-input:focus { border-color: #8B4513; }
.search-btn { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #8B4513; font-weight: 600; cursor: pointer; font-size: 14px; }
.suggestions-list { position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1.5px solid #e8ddd3; border-top: none; border-radius: 0 0 12px 12px; z-index: 1000; max-height: 200px; overflow-y: auto; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
.suggestion-item { padding: 12px 14px; cursor: pointer; font-size: 13px; color: #2d2d2d; border-bottom: 1px solid #f0ebe5; transition: background 0.15s; }
.suggestion-item:hover { background: #FDF6EE; }
.detected-address { margin-top: 10px; padding: 12px 16px; background: #FDF6EE; border-radius: 12px; border-left: 4px solid #8B4513; font-size: 13px; color: #4A1B0C; }

.recap-card { background: white; border-radius: 24px; padding: 25px; position: sticky; top: 100px; box-shadow: 0 15px 35px rgba(0,0,0,0.06); border: 1px solid #f0ebe5; }
.recap-title { font-family: 'Playfair Display', serif; font-size: 20px; color: #4A1B0C; font-weight: 700; margin-bottom: 20px; text-align: center; }
.recap-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f9f5f0; gap: 8px; }
.recap-text { font-size: 13px; color: #4A1B0C; flex: 1; }
.recap-subtext { font-size: 11px; color: #7a6a5a; display: block; }
.recap-price { font-size: 13px; font-weight: 700; color: #8B4513; white-space: nowrap; }

.total-box { background: #FDF6EE; border-radius: 16px; padding: 20px; margin-top: 20px; border: 1px dashed #C8963E; }
.total-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #7a6a5a; }
.total-final { display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e8ddd3; }
.total-final span { font-size: 18px; font-weight: 800; color: #4A1B0C; }
.total-final strong { font-size: 24px; color: #8B4513; }

.btn-proceed { width: 100%; background: linear-gradient(135deg, #8B4513, #4A1B0C); color: white; padding: 18px; border: none; border-radius: 16px; font-size: 18px; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 20px rgba(139,69,19,0.3); margin-top: 20px; }
.btn-proceed:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(139,69,19,0.4); }
</style>

<div class="order-container">
    <div class="order-header">
        <h2>Confirmer la livraison</h2>
        <p>L'excellence du tissu, livrée avec soin à votre adresse</p>
    </div>

    <div class="order-steps">
        <div class="step-item">
            <div class="step-dot active">1</div>
            <span class="step-text active">Adresse</span>
        </div>
        <div class="step-line active"></div>
        <div class="step-item">
            <div class="step-dot">2</div>
            <span class="step-text">Paiement</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <div class="step-dot">3</div>
            <span class="step-text">Confirmation</span>
        </div>
    </div>

    <form method="POST" action="{{ route('commande.confirmer') }}" id="formCommande">
        @csrf
        <div class="order-grid">
            <div class="order-main">
                {{-- ADRESSE --}}
                <div class="order-section">
                    <div class="section-title"> Informations de livraison</div>

                    <div class="form-grid">
                        <div class="input-group">
                            <label class="input-label">Prénom <span style="color:red;">*</span></label>
                            <input type="text" name="prenom" required placeholder="Ex: Kouamé"
                                   value="{{ old('prenom', explode(' ', auth()->user()->name)[0]) }}"
                                   class="input-field">
                        </div>
                        <div class="input-group">
                            <label class="input-label">Nom <span style="color:red;">*</span></label>
                            <input type="text" name="nom_complet" required placeholder="Ex: Konan"
                                   class="input-field">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Téléphone <span style="color:red;">*</span></label>
                        <div style="display:flex; gap:10px;">
                            <select name="indicatif" class="input-field" style="width:120px; cursor:pointer;">
                                <option value="+225">🇨🇮 +225</option>
                                <option value="+221">🇸🇳 +221</option>
                                <option value="+223">🇲🇱 +223</option>
                                <option value="+226">🇧🇫 +226</option>
                                <option value="+228">🇹🇬 +228</option>
                                <option value="+229">🇧🇯 +229</option>
                                <option value="+224">🇬🇳 +224</option>
                                <option value="+33"> 🇫🇷 +33</option>
                            </select>
                            <input type="tel" name="telephone" required placeholder="07 12 34 56 78" class="input-field" style="flex:1;">
                        </div>
                    </div>

                    <div class="input-group">
                        <label class="input-label">Ville <span style="color:red;">*</span></label>
                        <select name="ville" id="villeSelect" required onchange="updateCommunes()" class="input-field" style="cursor:pointer;">
                            <option value="">-- Choisir une ville --</option>
                            <option value="Abidjan">Abidjan</option>
                            <option value="Bouaké">Bouaké</option>
                            <option value="Yamoussoukro">Yamoussoukro</option>
                            <option value="Daloa">Daloa</option>
                            <option value="Korhogo">Korhogo</option>
                            <option value="San-Pédro">San-Pédro</option>
                            <option value="Man">Man</option>
                            <option value="Gagnoa">Gagnoa</option>
                            <option value="Divo">Divo</option>
                            <option value="Abengourou">Abengourou</option>
                        </select>
                    </div>

                    <div class="form-grid">
                        <div class="input-group">
                            <label class="input-label">Commune <span style="color:red;">*</span></label>
                            <select name="commune" id="communeSelect" required class="input-field" style="cursor:pointer;">
                                <option value="">-- Commune --</option>
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Quartier</label>
                            <input type="text" name="quartier" id="quartierInput" placeholder="Ex: Cocody Riviera..." class="input-field">
                        </div>
                    </div>

                    <div class="map-section">
                        <label class="input-label">Localisation précise sur la carte <span style="color:red;">*</span></label>
                        <div class="search-box">
                            <input type="text" id="searchInput" placeholder="Rechercher votre adresse..." class="search-input" oninput="rechercherAdresse()">
                            <span class="search-btn" onclick="rechercherAdresse()">Chercher</span>
                            <div id="suggestions" class="suggestions-list" style="display:none;"></div>
                        </div>
                        <div id="map" style="width:100%; height:300px; border-radius:16px; border:1.5px solid #e8ddd3; overflow:hidden; z-index:1;"></div>
                        <div id="adresseDetectee" class="detected-address" style="display:none;">
                            <strong>Adresse détectée :</strong> <span id="texteAdresse"></span>
                        </div>
                    </div>

                    <div class="input-group" style="margin-top:20px;">
                        <label class="input-label">Note pour le livreur (optionnel)</label>
                        <textarea name="note_livreur" rows="3" placeholder="Ex: Sonner au portail bleu, 2ème étage..." class="input-field" style="resize:vertical;"></textarea>
                    </div>

                    <input type="hidden" name="adresse_livraison" id="adresseComplete">
                    <input type="hidden" name="latitude"  id="latInput">
                    <input type="hidden" name="longitude" id="lngInput">
                </div>

                {{-- MODE DE LIVRAISON --}}
                <div class="order-section">
                    <div class="section-title"> Mode de livraison</div>
                    <div class="delivery-options">
                        <label class="delivery-item selected" id="label-domicile">
                            <input type="radio" name="mode_livraison" value="domicile" checked onchange="selectLivraison(this)">
                            <div class="delivery-info">
                                <div class="delivery-name">Livraison à domicile</div>
                                <div class="delivery-desc">Livré chez vous sous 24–48h</div>
                            </div>
                            <div style="font-weight:700; color:#8B4513; font-size:16px;">1 500 F</div>
                        </label>
                        <label class="delivery-item" id="label-boutique">
                            <input type="radio" name="mode_livraison" value="boutique" onchange="selectLivraison(this)">
                            <div class="delivery-info">
                                <div class="delivery-name">🏪 Retrait en boutique</div>
                                <div class="delivery-desc">Gratuit — Disponible dès demain</div>
                            </div>
                            <div style="font-weight:700; color:#10b981; font-size:16px;">Gratuit</div>
                        </label>
                    </div>
                    <input type="hidden" name="frais_livraison" id="fraisLivraison" value="1500">
                </div>
            </div>

            <div class="order-sidebar">
                <div class="recap-card">
                    <div class="recap-title">Votre Panier</div>
                    <div class="recap-list">
                        @foreach($panier as $item)
                        <div class="recap-item">
                            <span class="recap-text">
                                {{ $item['nom'] }}
                                <span class="recap-subtext">{{ $item['type_commande'] }} × {{ $item['quantite'] }}</span>
                            </span>
                            <span class="recap-price">{{ number_format($item['sous_total'], 0, ',', ' ') }} F</span>
                        </div>
                        @endforeach
                    </div>

                    <div class="total-box">
                        <div class="total-row">
                            <span>Sous-total</span>
                            <span>{{ number_format($total, 0, ',', ' ') }} F</span>
                        </div>
                        <div class="total-row">
                            <span>Livraison</span>
                            <span id="affichageFrais" style="color:#8B4513; font-weight:600;">1 500 F</span>
                        </div>
                        <div class="total-final">
                            <span>Total</span>
                            <strong id="totalFinal">{{ number_format($total + 1500, 0, ',', ' ') }} F</strong>
                        </div>
                    </div>

                    <button type="submit" onclick="assemblerAdresse()" class="btn-proceed">
                        Passer au paiement →
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
var map = L.map('map').setView([5.3600, -4.0083], 12);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap' }).addTo(map);

var markerIcon = L.divIcon({
    html: '<div style="background:#8B4513; width:20px; height:20px; border-radius:50% 50% 50% 0; transform:rotate(-45deg); border:3px solid white; box-shadow:0 2px 8px rgba(0,0,0,0.3);"></div>',
    iconSize: [20, 20], iconAnchor: [10, 20], className: ''
});
var marker = null;

map.on('click', function(e) {
    placerMarqueur(e.latlng.lat, e.latlng.lng);
    reverseGeocode(e.latlng.lat, e.latlng.lng);
});

function placerMarqueur(lat, lng) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng], { icon: markerIcon, draggable: true }).addTo(map);
    marker.on('dragend', function(e) {
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
                    suggestionsDiv.innerHTML = '<div style="padding:12px 14px; color:#aaa; font-size:13px;">Aucun résultat trouvé</div>';
                    suggestionsDiv.style.display = 'block';
                    return;
                }
                data.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'suggestion-item';
                    div.innerHTML = item.display_name;
                    div.onclick = () => {
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
                    suggestionsDiv.appendChild(div);
                });
                suggestionsDiv.style.display = 'block';
            });
    }, 400);
}

document.addEventListener('click', function(e) {
    if (!document.getElementById('searchInput').contains(e.target)) {
        document.getElementById('suggestions').style.display = 'none';
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
                }
            }
        });
}

function afficherAdresse(texte) {
    document.getElementById('texteAdresse').textContent = texte;
    document.getElementById('adresseDetectee').style.display = 'block';
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

function updateCommunes() {
    const ville = document.getElementById('villeSelect').value;
    const select = document.getElementById('communeSelect');
    select.innerHTML = '<option value="">-- Commune --</option>';
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
    if (coords[ville]) map.setView(coords[ville], 13);
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
function selectLivraison(radio) {
    const frais = radio.value === 'domicile' ? 1500 : 0;
    document.getElementById('fraisLivraison').value = frais;
    document.getElementById('affichageFrais').textContent = frais > 0 ? '1 500 F' : 'Gratuit';
    document.getElementById('totalFinal').textContent = new Intl.NumberFormat('fr-FR').format(baseTotal + frais) + ' F';
    document.getElementById('label-domicile').classList.toggle('selected', radio.value === 'domicile');
    document.getElementById('label-boutique').classList.toggle('selected', radio.value === 'boutique');
}

setTimeout(() => map.invalidateSize(), 300);
</script>

@endsection