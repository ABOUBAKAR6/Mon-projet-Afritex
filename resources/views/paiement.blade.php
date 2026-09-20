@extends('layouts.app')
@section('content')

@php
    $parts = explode(' | ', $commande->adresse_livraison ?? '');
    $gpsIdx = collect($parts)->search(fn($p) => str_starts_with($p, 'GPS:'));
    $hasGps = $gpsIdx !== false;
    $latVal = '5.3600'; $lngVal = '-4.0083';
    if ($hasGps) {
        $gpsStr = str_replace('GPS: ', '', $parts[$gpsIdx]);
        $c = explode(', ', $gpsStr);
        $latVal = $c[0] ?? '5.3600';
        $lngVal = $c[1] ?? '-4.0083';
    }
@endphp

<style>
.pay-container { max-width: 1100px; margin: 0 auto; padding: 40px 20px; font-family: 'DM Sans', sans-serif; }
.pay-header { text-align: center; margin-bottom: 40px; }
.pay-header h2 { font-family: 'Playfair Display', serif; font-size: 32px; color: #4A1B0C; font-weight: 700; margin-bottom: 10px; }
.pay-header p { color: #7a6a5a; font-size: 16px; }

.pay-steps { display: flex; justify-content: center; align-items: center; margin-bottom: 50px; gap: 10px; }
.step-item { display: flex; align-items: center; gap: 10px; }
.step-dot { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; transition: all 0.3s; border: 2px solid #e8ddd3; background: white; color: #aaa; }
.step-dot.active { background: #8B4513; color: white; border-color: #8B4513; box-shadow: 0 0 15px rgba(139,69,19,0.3); }
.step-dot.completed { background: #10b981; color: white; border-color: #10b981; }
.step-line { width: 60px; height: 3px; background: #e8ddd3; transition: all 0.3s; }
.step-line.active { background: #8B4513; }
.step-text { font-size: 14px; font-weight: 600; color: #aaa; }
.step-text.active { color: #4A1B0C; }

.pay-grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; align-items: start; }
@media (max-width: 992px) { .pay-grid { grid-template-columns: 1fr; } }

.pay-section { background: white; border-radius: 24px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #f0ebe5; margin-bottom: 24px; }
.section-title { font-family: 'Playfair Display', serif; font-size: 20px; color: #4A1B0C; font-weight: 700; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; }
.section-title span { background: #FDF6EE; color: #8B4513; width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }

.addr-card { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }
.addr-item { background: #FAFAF8; padding: 16px; border-radius: 16px; border: 1px solid #f0ebe5; }
.addr-label { font-size: 12px; color: #aaa; font-weight: 600; text-transform: uppercase; margin-bottom: 4px; display: block; }
.addr-val { font-size: 15px; color: #2d2d2d; font-weight: 600; }

.payment-options { display: flex; flex-direction: column; gap: 12px; }
.payment-item { display: flex; align-items: center; gap:15px; padding: 18px; border: 2px solid #e8ddd3; border-radius: 16px; cursor: pointer; transition: all 0.2s; background: white; position: relative; }
.payment-item:hover { border-color: #C8963E; background: #FFFBF5; transform: translateX(5px); }
.payment-item.selected { border-color: #8B4513; background: #FDF6EE; box-shadow: 0 4px 12px rgba(139,69,19,0.1); }
.payment-item input { width: 20px; height: 20px; accent-color: #8B4513; }
.payment-info { flex: 1; }
.payment-name { font-weight: 700; color: #4A1B0C; font-size: 16px; }
.payment-desc { font-size: 13px; color: #7a6a5a; }

.detail-zone { margin-top: 15px; padding: 20px; background: #FAFAF8; border-radius: 16px; border: 1px solid #e8ddd3; display: none; animation: slideDown 0.3s ease-out; }
@keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

.op-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 12px; margin: 15px 0; }
.op-card { padding: 15px; border: 2px solid #e8ddd3; border-radius: 12px; cursor: pointer; transition: all 0.2s; background: white; display: flex; align-items: center; justify-content: center; }
.op-card:hover, .op-card.selected { border-color: #8B4513; background: #FDF6EE; transform: translateY(-3px); }
.op-card img { width: 50px; height: 50px; object-fit: contain; }

.input-group { margin-bottom: 15px; }
.input-label { font-size: 13px; color: #7a6a5a; font-weight: 600; margin-bottom: 6px; display: block; }
.input-field { width: 100%; padding: 12px 16px; border: 1.5px solid #e8ddd3; border-radius: 12px; font-size: 15px; outline: none; transition: all 0.2s; }
.input-field:focus { border-color: #8B4513; box-shadow: 0 0 0 3px rgba(139,69,19,0.1); }

.btn-confirm { width: 100%; background: linear-gradient(135deg, #8B4513, #4A1B0C); color: white; padding: 18px; border: none; border-radius: 16px; font-size: 18px; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 6px 20px rgba(139,69,19,0.3); margin-top: 20px; }
.btn-confirm:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(139,69,19,0.4); }

.recap-card { background: white; border-radius: 24px; padding: 25px; position: sticky; top: 100px; box-shadow: 0 15px 35px rgba(0,0,0,0.06); border: 1px solid #f0ebe5; }
.recap-title { font-family: 'Playfair Display', serif; font-size: 20px; color: #4A1B0C; font-weight: 700; margin-bottom: 20px; text-align: center; }
.recap-item { display: flex; align-items: center; gap: 15px; padding: 12px 0; border-bottom: 1px solid #f9f5f0; }
.recap-img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; background: #f5f5f5; }
.recap-info { flex: 1; }
.recap-name { font-size: 13px; font-weight: 600; color: #2d2d2d; }
.recap-meta { font-size: 11px; color: #7a6a5a; }
.recap-price { font-size: 13px; font-weight: 700; color: #8B4513; }

.total-box { background: #FDF6EE; border-radius: 16px; padding: 20px; margin-top: 20px; border: 1px dashed #C8963E; }
.total-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: #7a6a5a; }
.total-final { display: flex; justify-content: space-between; margin-top: 12px; padding-top: 12px; border-top: 1px solid #e8ddd3; }
.total-final span { font-size: 18px; font-weight: 800; color: #4A1B0C; }
.total-final strong { font-size: 24px; color: #8B4513; }
</style>

<div class="pay-container">
    <div class="pay-header">
        <h2>Finaliser votre commande</h2>
        <p>Veuillez vérifier vos informations et choisir votre mode de paiement</p>
    </div>

    <div class="pay-steps">
        <div class="step-item">
            <div class="step-dot completed">✓</div>
            <span class="step-text">Adresse</span>
        </div>
        <div class="step-line active"></div>
        <div class="step-item">
            <div class="step-dot active">2</div>
            <span class="step-text active">Paiement</span>
        </div>
        <div class="step-line"></div>
        <div class="step-item">
            <div class="step-dot">3</div>
            <span class="step-text">Confirmation</span>
        </div>
    </div>

    <div class="pay-grid">
        <div class="pay-main">
            {{-- ADRESSE --}}
            <div class="pay-section">
                <div class="section-title"> Détails de livraison</div>
                <div class="addr-card">
                    @if(isset($parts[0]))
                    <div class="addr-item">
                        <span class="addr-label">Destinataire</span>
                        <span class="addr-val">{{ $parts[0] }}</span>
                    </div>
                    @endif
                    @if(isset($parts[1]))
                    <div class="addr-item">
                        <span class="addr-label">Téléphone</span>
                        <span class="addr-val">{{ $parts[1] }}</span>
                    </div>
                    @endif
                    @if(isset($parts[2]))
                    <div class="addr-item">
                        <span class="addr-label">Ville / Commune</span>
                        <span class="addr-val">{{ $parts[2] }}</span>
                    </div>
                    @endif
                    @if(isset($parts[3]))
                    <div class="addr-item" style="grid-column: span 2;">
                        <span class="addr-label">Adresse précise</span>
                        <span class="addr-val">{{ $parts[3] }}</span>
                    </div>
                    @endif
                </div>
                @if($hasGps)
                <div id="miniMap" style="width:100%; height:200px; border-radius:16px; border:1px solid #e8ddd3; margin-top:20px; overflow:hidden;"></div>
                @endif
                <div style="text-align: right; margin-top: 15px;">
                    <a href="{{ route('commande') }}" style="font-size:13px; color:#8B4513; font-weight:600; text-decoration:none; border-bottom:1px solid #8B4513;">Modifier l'adresse →</a>
                </div>
            </div>

            {{-- PAIEMENT --}}
            <div class="pay-section">
                <div class="section-title"> Mode de paiement</div>
                <form method="POST" action="{{ route('paiement.payer', $commande->id) }}" id="formPaiement">
                    @csrf
                    <input type="hidden" name="operateur" id="operateurChoisi">

                    <div class="payment-options">
                        {{-- MOBILE MONEY --}}
                        <label class="payment-item" id="label-mobile_money">
                            <input type="radio" name="methode" value="mobile_money" required onclick="selectMethode('mobile_money')">
                            <div class="payment-info">
                                <div class="payment-name">Mobile Money</div>
                                <div class="payment-desc">Orange Money, MTN MoMo, Wave, Moov</div>
                            </div>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8963E" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </label>
                        <div class="detail-zone" id="zone-mobile_money">
                            <p class="input-label">Sélectionnez votre opérateur :</p>
                            <div class="op-grid">
                                <div class="op-card" id="op-orange" onclick="choisirOperateur('orange','Orange Money')">
                                    <img src="{{ asset('images/payments/orange.png') }}" alt="Orange Money">
                                </div>
                                <div class="op-card" id="op-mtn" onclick="choisirOperateur('mtn','MTN MoMo')">
                                    <img src="{{ asset('images/payments/mtn.jpg') }}" alt="MTN MoMo">
                                </div>
                                <div class="op-card" id="op-wave" onclick="choisirOperateur('wave','Wave')">
                                    <img src="{{ asset('images/payments/wave.png') }}" alt="Wave">
                                </div>
                                <div class="op-card" id="op-moov" onclick="choisirOperateur('moov','Moov Money')">
                                    <img src="{{ asset('images/payments/moov.png') }}" alt="Moov Money">
                                </div>
                            </div>
                            <div id="zone-numero-mobile" style="display:none; margin-top:15px;">
                                <label class="input-label">Numéro <span id="labelOperateur" style="color:#8B4513;"></span> <span style="color:red;">*</span></label>
                                <div style="display:flex; gap:10px;">
                                    <span style="padding:12px; background:#f0ebe5; border-radius:12px; font-weight:700; color:#4A1B0C; min-width:80px; text-align:center;">🇨🇮 +225</span>
                                    <input type="tel" name="numero_mobile" id="numeroMobile" placeholder="07 00 00 00 00" class="input-field" style="flex:1;">
                                </div>
                            </div>
                        </div>

                        {{-- CARTE BANCAIRE --}}
                        <label class="payment-item" id="label-carte">
                            <input type="radio" name="methode" value="carte" onclick="selectMethode('carte')">
                            <div class="payment-info">
                                <div class="payment-name">Carte Bancaire</div>
                                <div class="payment-desc">Visa, Mastercard, Maestro</div>
                            </div>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8963E" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </label>
                        <div class="detail-zone" id="zone-carte">
                            <div style="display:flex; gap:10px; margin-bottom:15px;">
                                <span style="padding:5px 12px; background:white; border:1px solid #e8ddd3; border-radius:6px; font-size:12px; font-weight:800; color:#1A1F71;">VISA</span>
                                <span style="padding:5px 12px; background:white; border:1px solid #e8ddd3; border-radius:6px; font-size:12px; font-weight:800; color:#EB001B;">Mastercard</span>
                            </div>
                            <div class="input-group">
                                <label class="input-label">Numéro de carte <span style="color:red;">*</span></label>
                                <input type="text" name="numero_carte" placeholder="0000 0000 0000 0000" maxlength="19" oninput="formatCarte(this)" class="input-field" style="font-family:monospace; letter-spacing:2px;">
                            </div>
                            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                                <div class="input-group">
                                    <label class="input-label">Expiration <span style="color:red;">*</span></label>
                                    <input type="text" name="expiry" placeholder="MM/AA" maxlength="7" oninput="formatExpiry(this)" class="input-field">
                                </div>
                                <div class="input-group">
                                    <label class="input-label">CVV <span style="color:red;">*</span></label>
                                    <input type="password" name="cvv" placeholder="•••" maxlength="3" class="input-field">
                                </div>
                            </div>
                        </div>

                        {{-- ESPÈCES --}}
                        <label class="payment-item" id="label-especes">
                            <input type="radio" name="methode" value="especes" onclick="selectMethode('especes')">
                            <div class="payment-info">
                                <div class="payment-name">Paiement à la livraison</div>
                                <div class="payment-desc">Payez en espèces lors de la réception</div>
                            </div>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C8963E" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </label>
                        <div class="detail-zone" id="zone-especes" style="background:#f0fdf4; border-color:#86efac;">
                            <div style="display:flex; align-items:center; gap:15px;">
                                <div style="display:none;"></div>
                                <div style="font-weight:700; color:#166534; font-size:15px;">Confirmation de livraison</div>
                                <div style="font-size:13px; color:#166534;">Le montant de <strong>{{ number_format($commande->total, 0, ',', ' ') }} FCFA</strong> sera collecté à la livraison.</div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn-confirm" id="btnPayer">
                        Choisir un mode de paiement
                    </button>
                </form>
            </div>
        </div>

        <div class="pay-sidebar">
            <div class="recap-card">
                <div class="recap-title">Votre Panier</div>
                <div class="recap-list">
                    @foreach($commande->items as $item)
                    <div class="recap-item">
                        @if($item->produit && $item->produit->image)
                            <img src="{{ asset($item->produit->image) }}" class="recap-img">
                        @else
                            <div class="recap-img" style="display:flex; align-items:center; justify-content:center; background:#f5f5f5; font-size:20px;">🧵</div>
                        @endif
                        <div class="recap-info">
                            <div class="recap-name">{{ $item->produit->nom ?? 'Produit' }}</div>
                            <div class="recap-meta">{{ $item->quantite }} x {{ ucfirst(str_replace('_',' ',$item->type_commande)) }}</div>
                        </div>
                        <div class="recap-price">{{ number_format($item->sous_total, 0, ',', ' ') }} F</div>
                    </div>
                    @endforeach
                </div>

                <div class="total-box">
                    <div class="total-row">
                        <span>Sous-total</span>
                        <span>{{ number_format($commande->total, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="total-row">
                        <span>Livraison</span>
                        <span style="color:#10b981; font-weight:600;">{{ ($commande->frais_livraison ?? 0) > 0 ? number_format($commande->frais_livraison, 0, ',', ' ').' F' : 'Gratuit' }}</span>
                    </div>
                    <div class="total-final">
                        <span>Total</span>
                        <strong>{{ number_format($commande->total, 0, ',', ' ') }} F</strong>
                    </div>
                </div>

                <div style="margin-top:20px; text-align:center;">
                    <div style="font-size:11px; color:#aaa; margin-bottom:8px;">Paiement sécurisé par</div>
                    <div style="font-size:14px; font-weight:800; color:#4A1B0C; letter-spacing:1px;">FEDAPAY</div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($hasGps)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endif

<script>
const totalAmount = '{{ number_format($commande->total, 0, ",", " ") }}';

function selectMethode(val) {
    ['mobile_money','carte','especes'].forEach(m => {
        const lbl = document.getElementById('label-'+m);
        const zone = document.getElementById('zone-'+m);
        if (lbl) { lbl.classList.remove('selected'); }
        if (zone) { zone.style.display='none'; }
    });

    const actif = document.getElementById('label-'+val);
    const zone = document.getElementById('zone-'+val);
    if (actif) { actif.classList.add('selected'); }
    if (zone) { zone.style.display='block'; }

    document.querySelector(`input[value="${val}"]`).checked = true;

    const btn = document.getElementById('btnPayer');
    if (val === 'especes') {
        btn.innerHTML = 'Confirmer la commande — ' + totalAmount + ' FCFA';
        btn.style.background = 'linear-gradient(135deg,#10b981,#059669)';
    } else if (val === 'mobile_money') {
        btn.innerHTML = 'Payer par Mobile Money — ' + totalAmount + ' FCFA';
        btn.style.background = 'linear-gradient(135deg,#8B4513,#4A1B0C)';
    } else if (val === 'carte') {
        btn.innerHTML = 'Payer par Carte — ' + totalAmount + ' FCFA';
        btn.style.background = 'linear-gradient(135deg,#1A1F71,#0d1252)';
    }
}

function choisirOperateur(id, nom) {
    ['orange','mtn','wave','moov'].forEach(op => {
        const el = document.getElementById('op-'+op);
        if (el) { el.classList.remove('selected'); }
    });
    const el = document.getElementById('op-'+id);
    if (el) { el.classList.add('selected'); }
    document.getElementById('operateurChoisi').value = id;
    document.getElementById('labelOperateur').textContent = nom;
    document.getElementById('zone-numero-mobile').style.display = 'block';
}

function formatCarte(input) {
    let val = input.value.replace(/\D/g,'').substring(0,16);
    input.value = val.replace(/(.{4})/g,'$1 ').trim();
}

function formatExpiry(input) {
    let val = input.value.replace(/\D/g,'');
    if (val.length >= 2) val = val.substring(0,2)+' / '+val.substring(2,4);
    input.value = val;
}

@if($hasGps)
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('miniMap',{zoomControl:false,dragging:false,scrollWheelZoom:false})
               .setView([{{ $latVal }},{{ $lngVal }}],15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    var icon = L.divIcon({
        html:'<div style="background:#8B4513;width:14px;height:14px;border-radius:50% 50% 50% 0;transform:rotate(-45deg);border:2px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
        iconSize:[14,14],iconAnchor:[7,14],className:''
    });
    L.marker([{{ $latVal }},{{ $lngVal }}],{icon:icon}).addTo(map);
    setTimeout(()=>map.invalidateSize(),300);
});
@endif
</script>

@endsection