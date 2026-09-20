<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mise à jour de votre commande</title>
</head>
<body style="margin:0; padding:0; background:#f5f0eb; font-family:Arial, sans-serif;">

    <div style="max-width:600px; margin:40px auto; background:white;
                border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">

        {{-- EN-TÊTE --}}
        <div style="background:linear-gradient(135deg, #8B4513, #4A1B0C);
                    padding:32px; text-align:center;">
            <h1 style="color:white; margin:0; font-size:24px; letter-spacing:1px;">
                🧵 AfriTex
            </h1>
            <p style="color:rgba(255,255,255,0.8); margin:8px 0 0;">
                Vente de tissus de qualité
            </p>
        </div>

        {{-- CORPS --}}
        <div style="padding:32px;">

            <p style="font-size:16px; color:#333;">
                Bonjour <strong>{{ $commande->user->name }}</strong>,
            </p>

            <p style="color:#555; line-height:1.6;">
                Nous vous informons que le statut de votre commande a été mis à jour.
            </p>

            {{-- NUMÉRO COMMANDE --}}
            <div style="background:#FDF6EE; border-left:4px solid #8B4513;
                        padding:16px; border-radius:8px; margin:24px 0;">
                <div style="font-size:13px; color:#7a6a5a; margin-bottom:4px;">Numéro de commande</div>
                <div style="font-size:22px; font-weight:bold; color:#4A1B0C;">
                    #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}
                </div>
            </div>

            {{-- CHANGEMENT DE STATUT --}}
            @php
                $statuts = [
                    'en_attente'     => ['label' => 'En attente',     'emoji' => '⏳', 'color' => '#f0ad4e'],
                    'confirmee'      => ['label' => 'Confirmée',      'emoji' => '✅', 'color' => '#0d6efd'],
                    'en_preparation' => ['label' => 'En préparation', 'emoji' => '🔧', 'color' => '#0dcaf0'],
                    'expedie'        => ['label' => 'Expédiée',       'emoji' => '🚚', 'color' => '#6c757d'],
                    'livree'         => ['label' => 'Livrée',         'emoji' => '📦', 'color' => '#198754'],
                    'annulee'        => ['label' => 'Annulée',        'emoji' => '❌', 'color' => '#dc3545'],
                ];
                $nouveau = $statuts[$commande->statut] ?? ['label' => $commande->statut, 'emoji' => '🔄', 'color' => '#333'];
                $ancien  = $statuts[$ancienStatut]     ?? ['label' => $ancienStatut,     'emoji' => '🔄', 'color' => '#333'];
            @endphp

            <div style="display:flex; align-items:center; justify-content:center;
                        gap:16px; margin:24px 0; text-align:center;">
                {{-- Ancien statut --}}
                <div style="flex:1; padding:12px; background:#f8f9fa;
                            border-radius:10px; color:#666;">
                    <div style="font-size:20px;">{{ $ancien['emoji'] }}</div>
                    <div style="font-size:12px; margin-top:4px;">{{ $ancien['label'] }}</div>
                </div>

                {{-- Flèche --}}
                <div style="font-size:24px; color:#8B4513;">→</div>

                {{-- Nouveau statut --}}
                <div style="flex:1; padding:12px; border-radius:10px; color:white;
                            background:{{ $nouveau['color'] }};">
                    <div style="font-size:20px;">{{ $nouveau['emoji'] }}</div>
                    <div style="font-size:12px; margin-top:4px; font-weight:bold;">
                        {{ $nouveau['label'] }}
                    </div>
                </div>
            </div>

            {{-- MESSAGE SELON STATUT --}}
            @if($commande->statut === 'confirmee')
                <p style="color:#555; background:#e8f5e9; padding:12px; border-radius:8px;">
                    🎉 Bonne nouvelle ! Votre commande a été <strong>confirmée</strong>.
                    Nous préparons vos tissus avec soin.
                </p>
            @elseif($commande->statut === 'en_preparation')
                <p style="color:#555; background:#e3f2fd; padding:12px; border-radius:8px;">
                    🔧 Votre commande est en cours de <strong>préparation</strong>.
                    Nous vous tiendrons informé(e) de la suite.
                </p>
            @elseif($commande->statut === 'expedie')
                <p style="color:#555; background:#f3e5f5; padding:12px; border-radius:8px;">
                    🚚 Votre commande a été <strong>expédiée</strong> !
                    Elle sera bientôt chez vous.
                </p>
            @elseif($commande->statut === 'livree')
                <p style="color:#555; background:#e8f5e9; padding:12px; border-radius:8px;">
                    📦 Votre commande a été <strong>livrée</strong> avec succès !
                    Merci pour votre confiance chez AfriTex. 🙏
                </p>
            @elseif($commande->statut === 'annulee')
                <p style="color:#555; background:#ffebee; padding:12px; border-radius:8px;">
                    ❌ Votre commande a été <strong>annulée</strong>.
                    Pour toute question, contactez-nous.
                </p>
            @endif

            {{-- RÉCAPITULATIF COMMANDE --}}
            <div style="margin:24px 0; border:1px solid #e8ddd3; border-radius:10px; overflow:hidden;">
                <div style="background:#FDF6EE; padding:12px 16px; font-weight:bold; color:#4A1B0C;">
                    📋 Récapitulatif
                </div>
                <div style="padding:16px;">
                    <table style="width:100%; border-collapse:collapse;">
                        @foreach($commande->items as $item)
                        <tr>
                            <td style="padding:6px 0; color:#555;">
                                {{ $item->produit->nom }}
                                <span style="font-size:11px; color:#999;">
                                    ({{ ucfirst(str_replace('_', ' ', $item->type_commande)) }})
                                </span>
                            </td>
                            <td style="padding:6px 0; text-align:right; color:#333;">
                                {{ $item->quantite }} × {{ number_format($item->prix_unitaire, 0, ',', ' ') }} F
                            </td>
                        </tr>
                        @endforeach
                        <tr style="border-top:2px solid #e8ddd3;">
                            <td style="padding:10px 0; font-weight:bold; color:#4A1B0C;">TOTAL</td>
                            <td style="padding:10px 0; text-align:right; font-weight:bold;
                                       font-size:18px; color:#8B4513;">
                                {{ number_format($commande->total, 0, ',', ' ') }} F CFA
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <p style="color:#777; font-size:13px; line-height:1.6;">
                Pour toute question, répondez à cet email ou contactez notre équipe.<br>
                Merci de faire confiance à <strong>AfriTex</strong> ! 🧵
            </p>
        </div>

        {{-- PIED DE PAGE --}}
        <div style="background:#4A1B0C; padding:20px; text-align:center;">
            <p style="color:rgba(255,255,255,0.7); font-size:12px; margin:0;">
                © {{ date('Y') }} AfriTex — Tous droits réservés
            </p>
        </div>
    </div>

</body>
</html>