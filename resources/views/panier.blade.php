@extends('layouts.app')
@section('content')

<div style="padding:40px; max-width:900px; margin:0 auto;">
    <h2 style="font-family:Georgia,serif; font-size:36px; color:#4A1B0C; margin-bottom:28px;">🛒 Mon Panier</h2>

    @if(empty($panier))
    <div style="text-align:center; padding:60px; background:white; border-radius:20px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
        <div style="font-size:80px; margin-bottom:20px;">🛒</div>
        <h3 style="color:#4A1B0C; margin-bottom:12px;">Votre panier est vide</h3>
        <p style="color:#7a6a5a; margin-bottom:24px;">Découvrez nos tissus et ajoutez-les à votre panier</p>
        <a href="{{ route('catalogue') }}" style="background:#8B4513; color:white; padding:12px 28px; border-radius:20px; text-decoration:none; font-weight:500;">Voir le catalogue</a>
    </div>
    @else
    <div style="background:white; border-radius:16px; overflow:hidden; margin-bottom:20px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
        @foreach($panier as $index => $item)
        <div style="display:flex; justify-content:space-between; align-items:center; padding:20px 24px; border-bottom:1px solid #e8ddd3;">
            <div style="display:flex; align-items:center; gap:16px;">
                <div style="width:60px; height:60px; border-radius:10px; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                    @if(!empty($item['image']) && file_exists(public_path($item['image'])))
                        <img src="{{ asset($item['image']) }}" alt="{{ $item['nom'] }}" style="width:60px; height:60px; object-fit:cover; display:block;">
                    @else
                        <div style="width:60px; height:60px; background:linear-gradient(135deg, #F5E6D3, #EDD5B8); display:flex; align-items:center; justify-content:center; font-size:22px;">🧵</div>
                    @endif
                </div>
                <div>
                    <div style="font-weight:600; color:#4A1B0C; margin-bottom:4px;">{{ $item['nom'] }}</div>
                    <div style="font-size:12px; color:#7a6a5a;">{{ $item['type_commande'] }} — Qté: {{ $item['quantite'] }}</div>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:20px;">
                <div style="font-weight:600; color:#8B4513; font-size:16px;">{{ number_format($item['sous_total'], 0, ',', ' ') }} F</div>
                <form method="POST" action="{{ route('panier.supprimer', $index) }}">
                    @csrf @method('DELETE')
                    <button type="submit" style="background:#fee2e2; color:#dc2626; border:none; padding:8px 14px; border-radius:8px; cursor:pointer; font-size:13px; transition:all 0.2s ease;"
                        onmouseover="this.style.background='#dc2626'; this.style.color='white'"
                        onmouseout="this.style.background='#fee2e2'; this.style.color='#dc2626'">
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
        @endforeach
        <div style="padding:20px 24px; background:#FDF6EE; border-top:1px solid #e8ddd3;">
            <form method="POST" action="{{ route('panier.coupon.appliquer') }}" style="display:flex; gap:10px; align-items:center; margin-bottom:14px; flex-wrap:wrap;">
                @csrf
                <input type="text" name="coupon_code" placeholder="Code promo" value="{{ $coupon['code'] ?? '' }}" style="flex:1; min-width:220px; padding:11px 14px; border:1px solid #e8ddd3; border-radius:8px; font-size:14px;">
                <button type="submit" style="background:#8B4513; color:white; padding:11px 16px; border:none; border-radius:8px; cursor:pointer;">Appliquer</button>
            </form>

            @if(!empty($coupon['code']))
                <div style="margin-bottom:10px; color:#4A1B0C; font-size:13px; font-weight:600;">Coupon appliqué : {{ $coupon['code'] }} (-{{ number_format($discount, 0, ',', ' ') }} F)</div>
            @endif

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
                <strong style="font-size:18px; color:#4A1B0C;">Sous-total :</strong>
                <span style="font-size:16px; color:#4A1B0C;">{{ number_format($subtotal, 0, ',', ' ') }} F</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:8px;">
                <strong style="font-size:18px; color:#4A1B0C;">Réduction :</strong>
                <span style="font-size:16px; color:#16a34a;">-{{ number_format($discount, 0, ',', ' ') }} F</span>
            </div>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px;">
                <strong style="font-size:20px; color:#4A1B0C;">Total :</strong>
                <strong style="font-size:24px; color:#8B4513;">{{ number_format($total, 0, ',', ' ') }} F</strong>
            </div>
        </div>
    </div>
    <div style="display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap;">
        <a href="{{ route('catalogue') }}" style="background:white; color:#8B4513; padding:12px 24px; border:1px solid #e8ddd3; border-radius:8px; text-decoration:none;">Continuer les achats</a>
        <a href="{{ route('commande') }}" style="background:#8B4513; color:white; padding:12px 28px; border-radius:8px; text-decoration:none; font-weight:500; transition:all 0.2s ease;"
            onmouseover="this.style.background='#4A1B0C'"
            onmouseout="this.style.background='#8B4513'">
            Confirmer la commande →
        </a>
    </div>
    @endif
</div>

@endsection