@extends('layouts.app')
@section('content')

<div style="max-width:1400px; margin:0 auto; padding:16px;">

    <!-- RANGÉE PRINCIPALE : Catégories + Slider + Bannières -->
    <div style="display:grid; grid-template-columns:220px 1fr 200px; gap:16px; margin-bottom:24px;">

        <!-- MENU CATEGORIES GAUCHE -->
        <div style="background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08); height:fit-content;">
            <div style="background:#4A1B0C; padding:14px 16px; color:white; font-weight:600; font-size:14px;">
                ☰ Toutes les catégories
            </div>
            @foreach($categories as $cat)
            <a href="{{ route('catalogue', ['categorie_id' => $cat->id]) }}"
               class="cat-item"
               style="display:flex; align-items:center; gap:10px; padding:10px 16px; color:#333; text-decoration:none; font-size:13px; border-bottom:1px solid #f5f5f5; transition:all 0.2s;">
                <span style="font-size:16px;">🧵</span>
                {{ $cat->nom }}
            </a>
            @endforeach
        </div>

        <!-- SLIDER CENTRAL -->
        <div style="position:relative; border-radius:12px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div id="slider" style="position:relative; height:380px;">

                <!-- SLIDE 1 -->
                <div class="slide" style="position:absolute; inset:0; background:linear-gradient(135deg, #6B2D0F 0%, #A0522D 100%); display:flex; align-items:center; padding:40px; transition:opacity 0.5s ease;">
                    <div style="color:white;">
                        <div style="background:#C8963E; display:inline-block; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:12px;">NOUVEAUTÉ</div>
                        <h2 style="font-family:Georgia,serif; font-size:36px; line-height:1.2; margin-bottom:12px;">Grain de Diamant<br><span style="color:#C8963E;">Premium</span></h2>
                        <p style="font-size:15px; opacity:0.85; margin-bottom:20px;">À partir de <strong>2 500 FCFA/mètre</strong></p>
                        <a href="{{ route('catalogue') }}" style="background:white; color:#8B4513; padding:12px 28px; border-radius:30px; text-decoration:none; font-weight:700; font-size:14px;">J'ACHÈTE →</a>
                    </div>
                    <div style="position:absolute; right:40px; font-size:120px; opacity:0.2;">🧵</div>
                </div>

                <!-- SLIDE 2 -->
                <div class="slide" style="position:absolute; inset:0; background:linear-gradient(135deg, #1a3a4a 0%, #2d6a8a 100%); display:flex; align-items:center; padding:40px; opacity:0; transition:opacity 0.5s ease;">
                    <div style="color:white;">
                        <div style="background:#C8963E; display:inline-block; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:12px;">BESTSELLER</div>
                        <h2 style="font-family:Georgia,serif; font-size:36px; line-height:1.2; margin-bottom:12px;">Soie Japonais<br><span style="color:#C8963E;">Pure</span></h2>
                        <p style="font-size:15px; opacity:0.85; margin-bottom:20px;">À partir de <strong>4 500 FCFA/mètre</strong></p>
                        <a href="{{ route('catalogue') }}" style="background:white; color:#2d6a8a; padding:12px 28px; border-radius:30px; text-decoration:none; font-weight:700; font-size:14px;">J'ACHÈTE →</a>
                    </div>
                    <div style="position:absolute; right:40px; font-size:120px; opacity:0.2;">✨</div>
                </div>

                <!-- SLIDE 3 -->
                <div class="slide" style="position:absolute; inset:0; background:linear-gradient(135deg, #2d4a1a 0%, #5a8a2d 100%); display:flex; align-items:center; padding:40px; opacity:0; transition:opacity 0.5s ease;">
                    <div style="color:white;">
                        <div style="background:#C8963E; display:inline-block; padding:4px 14px; border-radius:20px; font-size:12px; font-weight:600; margin-bottom:12px;">PROMOTION</div>
                        <h2 style="font-family:Georgia,serif; font-size:36px; line-height:1.2; margin-bottom:12px;">Dentelle<br><span style="color:#C8963E;">1000F Spécial</span></h2>
                        <p style="font-size:15px; opacity:0.85; margin-bottom:20px;">À partir de <strong>1 000 FCFA/mètre</strong></p>
                        <a href="{{ route('catalogue') }}" style="background:white; color:#5a8a2d; padding:12px 28px; border-radius:30px; text-decoration:none; font-weight:700; font-size:14px;">J'ACHÈTE →</a>
                    </div>
                    <div style="position:absolute; right:40px; font-size:120px; opacity:0.2;">🌸</div>
                </div>

            </div>

            <!-- DOTS -->
            <div style="position:absolute; bottom:16px; left:50%; transform:translateX(-50%); display:flex; gap:8px;">
                <div class="dot active" onclick="goToSlide(0)" style="width:10px; height:10px; border-radius:50%; background:white; cursor:pointer; opacity:1; transition:opacity 0.3s;"></div>
                <div class="dot" onclick="goToSlide(1)" style="width:10px; height:10px; border-radius:50%; background:white; cursor:pointer; opacity:0.5; transition:opacity 0.3s;"></div>
                <div class="dot" onclick="goToSlide(2)" style="width:10px; height:10px; border-radius:50%; background:white; cursor:pointer; opacity:0.5; transition:opacity 0.3s;"></div>
            </div>

            <!-- ARROWS -->
            <button onclick="prevSlide()" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.2); border:none; color:white; width:36px; height:36px; border-radius:50%; cursor:pointer; font-size:16px;">‹</button>
            <button onclick="nextSlide()" style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.2); border:none; color:white; width:36px; height:36px; border-radius:50%; cursor:pointer; font-size:16px;">›</button>
        </div>

        <!-- BANNIÈRES DROITE -->
        <div style="display:flex; flex-direction:column; gap:12px;">
            <div style="background:linear-gradient(135deg, #4A1B0C, #8B4513); border-radius:12px; padding:20px; color:white; text-align:center; flex:1;">
                <div style="font-size:32px; margin-bottom:8px;">📦</div>
                <div style="font-size:12px; font-weight:700; margin-bottom:4px;">VENTE EN GROS</div>
                <div style="font-size:11px; opacity:0.8; margin-bottom:12px;">Tarifs avantageux</div>
                <a href="{{ route('catalogue') }}" style="background:white; color:#8B4513; padding:6px 14px; border-radius:20px; text-decoration:none; font-size:11px; font-weight:700;">Commander</a>
            </div>
            <div style="background:linear-gradient(135deg, #C8963E, #E8B84B); border-radius:12px; padding:20px; color:white; text-align:center; flex:1;">
                <div style="font-size:32px; margin-bottom:8px;">🚚</div>
                <div style="font-size:12px; font-weight:700; margin-bottom:4px;">LIVRAISON</div>
                <div style="font-size:11px; opacity:0.8; margin-bottom:12px;">Partout en CI</div>
                <a href="{{ route('catalogue') }}" style="background:white; color:#C8963E; padding:6px 14px; border-radius:20px; text-decoration:none; font-size:11px; font-weight:700;">En savoir +</a>
            </div>
            <div style="background:linear-gradient(135deg, #1a3a4a, #2d6a8a); border-radius:12px; padding:20px; color:white; text-align:center; flex:1;">
                <div style="font-size:32px; margin-bottom:8px;">💎</div>
                <div style="font-size:12px; font-weight:700; margin-bottom:4px;">QUALITÉ</div>
                <div style="font-size:11px; opacity:0.8; margin-bottom:12px;">Garantie premium</div>
                <a href="{{ route('catalogue') }}" style="background:white; color:#1a3a4a; padding:6px 14px; border-radius:20px; text-decoration:none; font-size:11px; font-weight:700;">Découvrir</a>
            </div>
        </div>

    </div>

    <!-- BARRE VENTES FLASH -->
    <div style="background:white; border-radius:12px; padding:16px 20px; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
        <div style="display:flex; align-items:center; gap:12px;">
            <span style="font-size:20px;">⚡</span>
            <span style="font-weight:700; color:#4A1B0C; font-size:16px;">Offres Spéciales</span>
            <span style="color:#7a6a5a; font-size:14px;">| Tissus en promotion</span>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            <span style="color:#7a6a5a; font-size:13px;">Expire dans :</span>
            <div style="display:flex; gap:4px;">
                <span id="hours" style="background:#4A1B0C; color:white; padding:4px 10px; border-radius:6px; font-size:14px; font-weight:700;">00</span>
                <span style="color:#4A1B0C; font-weight:700;">:</span>
                <span id="minutes" style="background:#4A1B0C; color:white; padding:4px 10px; border-radius:6px; font-size:14px; font-weight:700;">00</span>
                <span style="color:#4A1B0C; font-weight:700;">:</span>
                <span id="seconds" style="background:#4A1B0C; color:white; padding:4px 10px; border-radius:6px; font-size:14px; font-weight:700;">00</span>
            </div>
        </div>
        <a href="{{ route('catalogue') }}" style="color:#8B4513; text-decoration:none; font-size:14px; font-weight:600; border-bottom:1px solid #8B4513;">Voir tout →</a>
    </div>

    <!-- PRODUITS POPULAIRES -->
    <div style="margin-bottom:24px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 style="font-family:Georgia,serif; font-size:22px; color:#4A1B0C; margin:0;">🔥 Produits Populaires</h2>
            <a href="{{ route('catalogue') }}" style="color:#8B4513; text-decoration:none; font-size:14px; font-weight:600;">Voir tout →</a>
        </div>
        <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:12px;">
            @forelse($produits as $produit)
            <div class="product-card" style="background:white; border-radius:10px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                <a href="{{ route('produit.show', $produit->slug) }}" style="text-decoration:none; color:inherit;">
                    <div style="height:140px; background:linear-gradient(135deg, #F5E6D3, #EDD5B8); display:flex; align-items:center; justify-content:center; font-size:50px; overflow:hidden;">
                        @if($produit->image)
                        <img src="{{ asset($produit->image) }}" style="width:100%; height:100%; object-fit:cover;">
                        @else 🧵 @endif
                    </div>
                    <div style="padding:10px;">
                        <div style="font-size:11px; color:#C8963E; font-weight:600; margin-bottom:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $produit->categorie->nom }}</div>
                        <div style="font-size:12px; font-weight:600; color:#333; margin-bottom:6px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $produit->nom }}</div>
                        <div style="font-size:13px; color:#8B4513; font-weight:700; margin-bottom:8px;">
                            @if($produit->prix_metre) {{ number_format($produit->prix_metre, 0, ',', ' ') }} F/m @endif
                        </div>
                        <div style="background:#8B4513; color:white; padding:6px; border-radius:6px; text-align:center; font-size:11px; font-weight:600;">
                            Voir le produit
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <p style="color:#7a6a5a; grid-column:span 6; text-align:center; padding:40px;">Aucun produit disponible</p>
            @endforelse
        </div>
    </div>

    <!-- SECTION GROS / DETAIL -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
        <div style="background:linear-gradient(135deg, #4A1B0C, #8B4513); border-radius:12px; padding:32px; color:white; position:relative; overflow:hidden;">
            <div style="position:absolute; right:-10px; top:-10px; font-size:80px; opacity:0.1;">📦</div>
            <span style="background:rgba(255,255,255,0.2); font-size:11px; padding:3px 12px; border-radius:20px; font-weight:600;">VENTE EN GROS</span>
            <h3 style="font-family:Georgia,serif; font-size:24px; margin:12px 0; ">Commandez en grande quantité</h3>
            <p style="font-size:14px; opacity:0.85; margin-bottom:20px; line-height:1.6;">Tarifs dégressifs pour revendeurs et couturiers professionnels.</p>
            <a href="{{ route('catalogue') }}" style="background:white; color:#8B4513; padding:10px 24px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:700;">Commander en gros →</a>
        </div>
        <div style="background:linear-gradient(135deg, #C8963E, #E8B84B); border-radius:12px; padding:32px; color:white; position:relative; overflow:hidden;">
            <div style="position:absolute; right:-10px; top:-10px; font-size:80px; opacity:0.1;">🛍️</div>
            <span style="background:rgba(255,255,255,0.2); font-size:11px; padding:3px 12px; border-radius:20px; font-weight:600;">VENTE AU DÉTAIL</span>
            <h3 style="font-family:Georgia,serif; font-size:24px; margin:12px 0;">Achetez au mètre ou à la pièce</h3>
            <p style="font-size:14px; opacity:0.85; margin-bottom:20px; line-height:1.6;">Parfait pour les particuliers et petits projets de couture.</p>
            <a href="{{ route('catalogue') }}" style="background:white; color:#C8963E; padding:10px 24px; border-radius:20px; text-decoration:none; font-size:13px; font-weight:700;">Commander au détail →</a>
        </div>
    </div>

</div>

<script>
// SLIDER
let current = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');

function goToSlide(n) {
    slides[current].style.opacity = '0';
    dots[current].style.opacity = '0.5';
    current = n;
    slides[current].style.opacity = '1';
    dots[current].style.opacity = '1';
}

function nextSlide() { goToSlide((current + 1) % slides.length); }
function prevSlide() { goToSlide((current - 1 + slides.length) % slides.length); }

setInterval(nextSlide, 4000);

// COMPTE A REBOURS
function updateTimer() {
    const now = new Date();
    const end = new Date();
    end.setHours(23, 59, 59, 0);
    const diff = end - now;
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    document.getElementById('hours').textContent = String(h).padStart(2, '0');
    document.getElementById('minutes').textContent = String(m).padStart(2, '0');
    document.getElementById('seconds').textContent = String(s).padStart(2, '0');
}
setInterval(updateTimer, 1000);
updateTimer();
</script>

@endsection