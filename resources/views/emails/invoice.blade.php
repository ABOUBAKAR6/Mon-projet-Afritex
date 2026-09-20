<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture AfriTex</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #d32f2f; margin-bottom: 5px; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; }
        .invoice-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .invoice-table th { background: #f2f2f2; border: 1px solid #ddd; padding: 8px; text-align: left; }
        .invoice-table td { border: 1px solid #ddd; padding: 8px; }
        .totals { text-align: right; font-size: 14px; }
        .footer { text-align: center; margin-top: 30px; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>AfriTex</h1>
        <p>Vêtements et Tissus de Qualité</p>
    </div>

    <table class="info-table">
        <tr>
            <td>
                <strong>Facturé à :</strong><br>
                {{ $commande->user->name }}<br>
                {{ $commande->user->email }}
            </td>
            <td style="text-align: right;">
                <strong>Facture N° :</strong> #{{ $commande->id }}<br>
                <strong>Date :</strong> {{ $commande->created_at->format('d/m/Y') }}<br>
                <strong>Statut :</strong> {{ strtoupper($commande->statut) }}
            </td>
        </tr>
    </table>

    <table class="invoice-table">
        <thead>
            <tr>
                <th>Produit</th>
                <th>Quantité</th>
                <th>Prix Unitaire</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($commande->items as $item)
            <tr>
                <td>{{ $item->produit->nom }}</td>
                <td>{{ $item->quantite }}</td>
                <td>{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($item->prix_unitaire * $item->quantite, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <strong>Total Général : {{ number_format($commande->total, 0, ',', ' ') }} FCFA</strong>
    </div>

    <div class="footer">
        Merci pour votre confiance !<br>
        AfriTex - Boutique en ligne officielle.
    </div>
</body>
</html>
