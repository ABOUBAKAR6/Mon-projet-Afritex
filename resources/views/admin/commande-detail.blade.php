@extends('layouts.app')
@section('content')

<div style="padding:40px; max-width:900px; margin:0 auto;">
    
    <!-- HEADER -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
        <div>
            <a href="{{ route('admin.commandes') }}" style="color:#8B4513; text-decoration:none; font-size:14px;">← Retour aux commandes</a>
            <h2 style="font-family:Georgia,serif; font-size:32px; color:#4A1B0C; margin:8px 0 0;">Commande #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</h2>
            <p style="color:#7a6a5a; font-size:14px;">{{ $commande->created_at->format('d/m/Y à H:i') }}</p>
        </div>
        <span style="padding:8px 20px; border-radius:20px; font-size:14px; font-weight:600;
            background: {{ $commande->statut == 'confirmee' ? '#d1fae5' : ($commande->statut == 'livree' ? '#dbeafe' : ($commande->statut == 'annulee' ? '#fee2e2' : '#fef3c7')) }};
            color: {{ $commande->statut == 'confirmee' ? '#065f46' : ($commande->statut == 'livree' ? '#1e40af' : ($commande->statut == 'annulee' ? '#991b1b' : '#92400e')) }};">
            {{ ucfirst($commande->statut) }}
        </span>
    </div>

    <div style="display:grid; grid-template-columns:2fr 1fr; gap:24px;">
        
        <!-- COLONNE GAUCHE -->
        <div>
            <!-- ARTICLES -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
                <h3 style="color:#4A1B0C; margin-bottom:20px; font-size:18px; display:flex; align-items:center; gap:8px;">
                    📦 Articles commandés
                </h3>
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#FDF6EE;">
                            <th style="padding:12px; text-align:left; border-bottom:1px solid #e8ddd3; color:#7a6a5a; font-size:13px;">Produit</th>
                            <th style="padding:12px; text-align:left; border-bottom:1px solid #e8ddd3; color:#7a6a5a; font-size:13px;">Type</th>
                            <th style="padding:12px; text-align:center; border-bottom:1px solid #e8ddd3; color:#7a6a5a; font-size:13px;">Qté</th>
                            <th style="padding:12px; text-align:right; border-bottom:1px solid #e8ddd3; color:#7a6a5a; font-size:13px;">Prix unit.</th>
                            <th style="padding:12px; text-align:right; border-bottom:1px solid #e8ddd3; color:#7a6a5a; font-size:13px;">Sous-total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commande->items as $item)
                        <tr>
                            <td style="padding:14px 12px; border-bottom:1px solid #e8ddd3;">
                                <div style="font-weight:600; color:#4A1B0C;">{{ $item->produit->nom }}</div>
                                <div style="font-size:12px; color:#C8963E;">{{ $item->produit->categorie->nom }}</div>
                            </td>
                            <td style="padding:14px 12px; border-bottom:1px solid #e8ddd3; color:#7a6a5a; font-size:13px;">{{ $item->type_commande }}</td>
                            <td style="padding:14px 12px; border-bottom:1px solid #e8ddd3; text-align:center; font-weight:500;">{{ $item->quantite }}</td>
                            <td style="padding:14px 12px; border-bottom:1px solid #e8ddd3; text-align:right; color:#8B4513;">{{ number_format($item->prix_unitaire, 0, ',', ' ') }} F</td>
                            <td style="padding:14px 12px; border-bottom:1px solid #e8ddd3; text-align:right; font-weight:600; color:#4A1B0C;">{{ number_format($item->sous_total, 0, ',', ' ') }} F</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background:#FDF6EE;">
                            <td colspan="4" style="padding:16px 12px; text-align:right; font-weight:600; font-size:16px; color:#4A1B0C;">TOTAL</td>
                            <td style="padding:16px 12px; text-align:right; font-weight:700; font-size:18px; color:#8B4513;">{{ number_format($commande->total, 0, ',', ' ') }} F CFA</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- ADRESSE LIVRAISON -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
                <h3 style="color:#4A1B0C; margin-bottom:16px; font-size:18px;">📍 Adresse de livraison</h3>
                <p style="color:#4A1B0C; font-size:15px; line-height:1.6; background:#FDF6EE; padding:14px; border-radius:8px;">
                    {{ $commande->adresse_livraison ?: 'Non spécifiée' }}
                </p>
            </div>

            <!-- PAIEMENT -->
            @if($commande->paiement)
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
                <h3 style="color:#4A1B0C; margin-bottom:16px; font-size:18px;">💳 Paiement</h3>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <div style="background:#FDF6EE; padding:14px; border-radius:8px;">
                        <div style="font-size:12px; color:#7a6a5a; margin-bottom:4px;">Méthode</div>
                        <div style="font-weight:600; color:#4A1B0C;">{{ ucfirst(str_replace('_', ' ', $commande->paiement->methode)) }}</div>
                    </div>
                    <div style="background:#FDF6EE; padding:14px; border-radius:8px;">
                        <div style="font-size:12px; color:#7a6a5a; margin-bottom:4px;">Montant</div>
                        <div style="font-weight:600; color:#8B4513;">{{ number_format($commande->paiement->montant, 0, ',', ' ') }} F CFA</div>
                    </div>
                    <div style="background:#FDF6EE; padding:14px; border-radius:8px;">
                        <div style="font-size:12px; color:#7a6a5a; margin-bottom:4px;">Référence</div>
                        <div style="font-weight:600; color:#4A1B0C; font-size:13px;">{{ $commande->paiement->reference }}</div>
                    </div>
                    <div style="background:#FDF6EE; padding:14px; border-radius:8px;">
                        <div style="font-size:12px; color:#7a6a5a; margin-bottom:4px;">Statut</div>
                        <div style="font-weight:600; color:#065f46;">✅ {{ ucfirst($commande->paiement->statut) }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- COLONNE DROITE -->
        <div>
            <!-- CLIENT -->
            <div style="background:white; border-radius:16px; padding:24px; margin-bottom:20px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
                <h3 style="color:#4A1B0C; margin-bottom:16px; font-size:18px;">👤 Client</h3>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
                    <div style="width:48px; height:48px; background:linear-gradient(135deg, #8B4513, #C8963E); border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-size:18px; font-weight:600;">
                        {{ strtoupper(substr($commande->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight:600; color:#4A1B0C;">{{ $commande->user->name }}</div>
                        <div style="font-size:13px; color:#7a6a5a;">{{ $commande->user->email }}</div>
                    </div>
                </div>
                <a href="mailto:{{ $commande->user->email }}" style="display:block; background:#FDF6EE; color:#8B4513; padding:10px; border-radius:8px; text-align:center; text-decoration:none; font-size:13px; border:1px solid #e8ddd3;">
                    ✉️ Envoyer un email
                </a>
            </div>

            <!-- CHANGER STATUT -->
            <div style="background:white; border-radius:16px; padding:24px; box-shadow:0 4px 20px rgba(139,69,19,0.08);">
                <h3 style="color:#4A1B0C; margin-bottom:16px; font-size:18px;">🔄 Changer le statut</h3>
                <form method="POST" action="{{ route('admin.commandes.statut', $commande->id) }}">
                    @csrf @method('PUT')
                    <select name="statut" style="width:100%; padding:12px; border:1px solid #e8ddd3; border-radius:8px; margin-bottom:12px; color:#4A1B0C; font-size:14px;">
                        <option value="en_attente" {{ $commande->statut == 'en_attente' ? 'selected' : '' }}>⏳ En attente</option>
                        <option value="confirmee" {{ $commande->statut == 'confirmee' ? 'selected' : '' }}>✅ Confirmée</option>
                        <option value="livree" {{ $commande->statut == 'livree' ? 'selected' : '' }}>🚚 Livrée</option>
                        <option value="annulee" {{ $commande->statut == 'annulee' ? 'selected' : '' }}>❌ Annulée</option>
                    </select>
                    <button type="submit" style="width:100%; background:#8B4513; color:white; padding:12px; border:none; border-radius:8px; cursor:pointer; font-size:14px; font-weight:500; transition:all 0.2s ease;"
                        onmouseover="this.style.background='#4A1B0C'"
                        onmouseout="this.style.background='#8B4513'">
                        Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
