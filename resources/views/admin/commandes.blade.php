@extends('layouts.app')
@section('title', 'Gestion des Commandes - AfriTex Admin')
@section('content')

<div style="padding:30px; background:#f8f4f0; min-height:100vh;">

    {{-- EN-TÊTE --}}
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:28px;">
        <div>
            <h2 style="font-size:26px; font-weight:bold; color:#4A1B0C; margin:0;">
                📋 Gestion des Commandes
            </h2>
            <small style="color:#7a6a5a;">AfriTex — Vue administrateur</small>
        </div>
        <a href="{{ route('admin.dashboard') }}"
           style="background:white; color:#4A1B0C; border:2px solid #4A1B0C;
                  padding:8px 18px; border-radius:8px; text-decoration:none;
                  font-size:14px; font-weight:600;">
            ← Tableau de bord
        </a>
    </div>

    {{-- ALERTE --}}
    @if(session('success'))
        <div style="background:#d4edda; color:#155724; border:1px solid #c3e6cb;
                    border-radius:10px; padding:14px 20px; margin-bottom:20px;">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- STATS --}}
    <div style="display:grid; grid-template-columns:repeat(6,1fr); gap:16px; margin-bottom:28px;">

        <div style="background:white; border-radius:12px; padding:16px; text-align:center;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08);">
            <div style="font-size:22px; font-weight:bold; color:#4A1B0C;">{{ $stats['total'] }}</div>
            <div style="font-size:12px; color:#7a6a5a; margin-top:4px;">Total</div>
        </div>

        <div style="background:white; border-radius:12px; padding:16px; text-align:center;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08); border-top:3px solid #f0ad4e;">
            <div style="font-size:22px; font-weight:bold; color:#856404;">{{ $stats['en_attente'] }}</div>
            <div style="font-size:12px; color:#7a6a5a; margin-top:4px;">En attente</div>
        </div>

        <div style="background:white; border-radius:12px; padding:16px; text-align:center;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08); border-top:3px solid #0d6efd;">
            <div style="font-size:22px; font-weight:bold; color:#0d6efd;">{{ $stats['confirmee'] }}</div>
            <div style="font-size:12px; color:#7a6a5a; margin-top:4px;">Confirmées</div>
        </div>

        <div style="background:white; border-radius:12px; padding:16px; text-align:center;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08); border-top:3px solid #198754;">
            <div style="font-size:22px; font-weight:bold; color:#198754;">{{ $stats['livree'] }}</div>
            <div style="font-size:12px; color:#7a6a5a; margin-top:4px;">Livrées</div>
        </div>

        <div style="background:white; border-radius:12px; padding:16px; text-align:center;
                    box-shadow:0 2px 8px rgba(0,0,0,0.08); border-top:3px solid #dc3545;">
            <div style="font-size:22px; font-weight:bold; color:#dc3545;">{{ $stats['annulee'] }}</div>
            <div style="font-size:12px; color:#7a6a5a; margin-top:4px;">Annulées</div>
        </div>

        <div style="background:linear-gradient(135deg,#8B4513,#4A1B0C); border-radius:12px;
                    padding:16px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
            <div style="font-size:15px; font-weight:bold; color:white;">
                {{ number_format($stats['ca_jour'], 0, ',', ' ') }} F
            </div>
            <div style="font-size:12px; color:rgba(255,255,255,0.8); margin-top:4px;">CA aujourd'hui</div>
        </div>

    </div>

    {{-- FILTRES --}}
    <div style="background:white; border-radius:12px; padding:20px;
                box-shadow:0 2px 8px rgba(0,0,0,0.08); margin-bottom:24px;">
        <form method="GET" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">

            <div style="flex:2; min-width:200px;">
                <label style="font-size:13px; font-weight:600; color:#4A1B0C;
                               display:block; margin-bottom:6px;">🔍 Recherche</label>
                <input type="text" name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom, email ou n° commande..."
                       style="width:100%; padding:9px 12px; border:1px solid #ddd;
                              border-radius:8px; font-size:14px; box-sizing:border-box;">
            </div>

            <div style="flex:1; min-width:160px;">
                <label style="font-size:13px; font-weight:600; color:#4A1B0C;
                               display:block; margin-bottom:6px;">📌 Statut</label>
                <select name="statut"
                        style="width:100%; padding:9px 12px; border:1px solid #ddd;
                               border-radius:8px; font-size:14px; background:white; box-sizing:border-box;">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente"     {{ request('statut')=='en_attente'     ? 'selected':'' }}>⏳ En attente</option>
                    <option value="confirmee"      {{ request('statut')=='confirmee'      ? 'selected':'' }}>✅ Confirmée</option>
                    <option value="en_preparation" {{ request('statut')=='en_preparation' ? 'selected':'' }}>🔧 En préparation</option>
                    <option value="expedie"        {{ request('statut')=='expedie'        ? 'selected':'' }}>🚚 Expédiée</option>
                    <option value="livree"         {{ request('statut')=='livree'         ? 'selected':'' }}>📦 Livrée</option>
                    <option value="annulee"        {{ request('statut')=='annulee'        ? 'selected':'' }}>❌ Annulée</option>
                </select>
            </div>

            <div style="flex:1; min-width:160px;">
                <label style="font-size:13px; font-weight:600; color:#4A1B0C;
                               display:block; margin-bottom:6px;">📅 Date</label>
                <input type="date" name="date"
                       value="{{ request('date') }}"
                       style="width:100%; padding:9px 12px; border:1px solid #ddd;
                              border-radius:8px; font-size:14px; box-sizing:border-box;">
            </div>

            <div style="display:flex; gap:8px;">
                <button type="submit"
                        style="background:#8B4513; color:white; border:none; padding:9px 20px;
                               border-radius:8px; font-size:14px; font-weight:600; cursor:pointer;">
                    Filtrer
                </button>
                <a href="{{ route('admin.commandes') }}"
                   style="background:#f8f4f0; color:#4A1B0C; border:1px solid #ddd; padding:9px 14px;
                          border-radius:8px; font-size:14px; text-decoration:none; font-weight:600;">
                    ✕
                </a>
            </div>

        </form>
    </div>

    {{-- TABLEAU --}}
    <div style="background:white; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); overflow:hidden;">

        @if($commandes->isEmpty())
            <div style="text-align:center; padding:60px; color:#7a6a5a;">
                <div style="font-size:48px; margin-bottom:12px;">📭</div>
                <p>Aucune commande trouvée.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#FDF6EE;">
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">N° Commande</th>
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Client</th>
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Articles</th>
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Total</th>
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Paiement</th>
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Statut</th>
                            <th style="padding:14px 16px; text-align:left; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Date</th>
                            <th style="padding:14px 16px; text-align:center; font-size:13px;
                                       color:#4A1B0C; border-bottom:2px solid #e8ddd3;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $statuts = [
                                'en_attente'     => ['label'=>'En attente',     'bg'=>'#fff3cd', 'color'=>'#856404'],
                                'confirmee'      => ['label'=>'Confirmée',      'bg'=>'#cce5ff', 'color'=>'#004085'],
                                'en_preparation' => ['label'=>'En préparation', 'bg'=>'#d1ecf1', 'color'=>'#0c5460'],
                                'expedie'        => ['label'=>'Expédiée',       'bg'=>'#e2e3e5', 'color'=>'#383d41'],
                                'livree'         => ['label'=>'Livrée',         'bg'=>'#d4edda', 'color'=>'#155724'],
                                'annulee'        => ['label'=>'Annulée',        'bg'=>'#f8d7da', 'color'=>'#721c24'],
                            ];
                        @endphp

                        @foreach($commandes as $commande)
                        @php $s = $statuts[$commande->statut] ?? ['label'=>$commande->statut,'bg'=>'#e2e3e5','color'=>'#333']; @endphp
                        <tr style="border-bottom:1px solid #f0e8e0; transition:background 0.15s;"
                            onmouseover="this.style.background='#FDF6EE'"
                            onmouseout="this.style.background='white'">

                            {{-- N° Commande --}}
                            <td style="padding:14px 16px; font-weight:bold; color:#8B4513;">
                                #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}
                            </td>

                            {{-- Client --}}
                            <td style="padding:14px 16px;">
                                <div style="font-weight:600; color:#333;">{{ $commande->user->name }}</div>
                                <div style="font-size:12px; color:#7a6a5a;">{{ $commande->user->email }}</div>
                            </td>

                            {{-- Articles --}}
                            <td style="padding:14px 16px;">
                                <span style="background:#FDF6EE; border:1px solid #e8ddd3;
                                             color:#4A1B0C; padding:3px 10px; border-radius:20px;
                                             font-size:12px;">
                                    {{ $commande->items->count() }} article(s)
                                </span>
                            </td>

                            {{-- Total --}}
                            <td style="padding:14px 16px; font-weight:bold; color:#333;">
                                {{ number_format($commande->total, 0, ',', ' ') }} F CFA
                            </td>

                            {{-- Paiement --}}
                            <td style="padding:14px 16px;">
                                @if($commande->paiement)
                                    @php $ps = $commande->paiement->statut; @endphp
                                    <span style="background:{{ $ps=='paye'?'#d4edda':($ps=='en_attente'?'#fff3cd':'#f8d7da') }};
                                                 color:{{ $ps=='paye'?'#155724':($ps=='en_attente'?'#856404':'#721c24') }};
                                                 padding:3px 10px; border-radius:20px; font-size:12px;">
                                        {{ ucfirst($ps) }}
                                    </span>
                                    <div style="font-size:11px; color:#7a6a5a; margin-top:3px;">
                                        {{ $commande->paiement->methode }}
                                    </div>
                                @else
                                    <span style="background:#e2e3e5; color:#383d41; padding:3px 10px;
                                                 border-radius:20px; font-size:12px;">Non défini</span>
                                @endif
                            </td>

                            {{-- Statut --}}
                            <td style="padding:14px 16px;">
                                <span style="background:{{ $s['bg'] }}; color:{{ $s['color'] }};
                                             padding:4px 12px; border-radius:20px; font-size:12px;
                                             font-weight:600;">
                                    {{ $s['label'] }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td style="padding:14px 16px;">
                                <div style="font-size:13px; color:#333;">
                                    {{ $commande->created_at->format('d/m/Y') }}
                                </div>
                                <div style="font-size:11px; color:#7a6a5a;">
                                    {{ $commande->created_at->format('H:i') }}
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td style="padding:14px 16px; text-align:center;">
                                <div style="display:flex; gap:6px; justify-content:center; align-items:center;">

                                    {{-- Voir détail --}}
                                    <a href="{{ route('admin.commande.detail', $commande) }}"
                                       style="background:#4A1B0C; color:white; padding:6px 14px;
                                              border-radius:6px; text-decoration:none; font-size:13px;">
                                        👁 Voir
                                    </a>

                                    {{-- Changer statut --}}
                                    <div style="position:relative; display:inline-block;">
                                        <button onclick="toggleMenu({{ $commande->id }})"
                                                style="background:#8B4513; color:white; border:none;
                                                       padding:6px 12px; border-radius:6px;
                                                       font-size:13px; cursor:pointer;">
                                            ⚙ ▾
                                        </button>

                                        <div id="menu-{{ $commande->id }}"
                                             style="display:none; position:absolute; right:0; top:110%;
                                                    background:white; border:1px solid #e8ddd3;
                                                    border-radius:10px; box-shadow:0 4px 20px rgba(0,0,0,0.12);
                                                    z-index:999; min-width:180px; overflow:hidden;">
                                            <div style="padding:8px 12px; font-size:11px; color:#7a6a5a;
                                                        border-bottom:1px solid #f0e8e0; font-weight:600;">
                                                CHANGER LE STATUT
                                            </div>
                                            @foreach($statuts as $value => $info)
                                                @if($value !== $commande->statut)
                                                <form method="POST"
                                                      action="{{ route('admin.commande.statut', $commande) }}">
                                                    @csrf @method('PATCH')
                                                    <input type="hidden" name="statut" value="{{ $value }}">
                                                    <button type="submit"
                                                            style="width:100%; text-align:left; background:none;
                                                                   border:none; padding:10px 14px; cursor:pointer;
                                                                   font-size:13px; color:#333; display:block;"
                                                            onmouseover="this.style.background='#FDF6EE'"
                                                            onmouseout="this.style.background='none'">
                                                        <span style="background:{{ $info['bg'] }};
                                                                     color:{{ $info['color'] }};
                                                                     padding:2px 8px; border-radius:10px;
                                                                     font-size:12px;">
                                                            {{ $info['label'] }}
                                                        </span>
                                                    </button>
                                                </form>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div style="display:flex; justify-content:space-between; align-items:center;
                        padding:16px 20px; border-top:1px solid #f0e8e0;">
                <small style="color:#7a6a5a;">
                    {{ $commandes->firstItem() }}–{{ $commandes->lastItem() }}
                    sur {{ $commandes->total() }} commandes
                </small>
                {{ $commandes->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Script menu déroulant --}}
<script>
function toggleMenu(id) {
    // Ferme tous les autres menus
    document.querySelectorAll('[id^="menu-"]').forEach(m => {
        if (m.id !== 'menu-' + id) m.style.display = 'none';
    });
    const menu = document.getElementById('menu-' + id);
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

// Ferme les menus si on clique ailleurs
document.addEventListener('click', function(e) {
    if (!e.target.closest('[id^="menu-"]') && !e.target.closest('button[onclick]')) {
        document.querySelectorAll('[id^="menu-"]').forEach(m => m.style.display = 'none');
    }
});
</script>

@endsection