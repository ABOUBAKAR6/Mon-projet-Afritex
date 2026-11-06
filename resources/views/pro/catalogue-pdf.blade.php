<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tarifs pro · {{ boutique('nom') }}</title>
    {{-- DomPDF : CSS simple, couleurs en hex (pas de variables CSS), police DejaVu pour les accents --}}
    <style>
        @page { margin: 28px 32px 40px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10.5px; color: {{ couleur('encre') }}; }
        .entete { border-bottom: 3px solid {{ couleur('accent') }}; padding-bottom: 12px; margin-bottom: 18px; }
        .entete table { width: 100%; border-collapse: collapse; }
        /* Cellules alignées en haut et une ligne par bloc : DomPDF rejette sinon hors page les lignes séparées par <br> */
        .entete td { vertical-align: top; padding: 0; }
        .nom { font-size: 22px; font-weight: bold; color: {{ couleur('foncee') }}; }
        .sous-titre { font-size: 12px; color: {{ couleur('texte') }}; margin-top: 2px; }
        .destinataire { text-align: right; font-size: 10px; color: {{ couleur('texte') }}; line-height: 1.5; }
        .destinataire-nom { color: {{ couleur('encre') }}; font-size: 11px; font-weight: bold; }
        h2 { font-size: 13px; color: {{ couleur('accent') }}; margin: 18px 0 6px; padding-bottom: 4px; border-bottom: 1px solid {{ couleur('trait') }}; }
        table.tarifs { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        table.tarifs tr { page-break-inside: avoid; }
        table.tarifs th { text-align: left; font-size: 9px; font-weight: normal; color: {{ couleur('texte') }}; background: {{ couleur('bloc') }}; padding: 5px 8px; }
        table.tarifs td { padding: 5px 8px; border-bottom: 1px solid {{ couleur('trait') }}; vertical-align: top; }
        .droite { text-align: right; }
        .prix-pro { font-weight: bold; color: {{ couleur('encre') }}; white-space: nowrap; }
        .discret { color: {{ couleur('texte_doux') }}; white-space: nowrap; }
        .produit { font-weight: bold; }
        .pied { position: fixed; bottom: -24px; left: 0; right: 0; font-size: 8.5px; color: {{ couleur('texte_doux') }}; text-align: center; }
        .vide { margin-top: 30px; color: {{ couleur('texte') }}; }
    </style>
</head>
<body>
@php $francs = fn ($montant) => number_format((float) $montant, 0, ',', ' ') . ' F'; @endphp

<div class="pied">
    Tarifs réservés à {{ $compte->boutique }}, valables au {{ now()->translatedFormat('j F Y') }}, sous réserve de stock. Le devis de la boutique fait foi.
</div>

<div class="entete">
    <table>
        <tr>
            <td>
                <div class="nom">{{ boutique('nom') }}</div>
                <div class="sous-titre">Tarifs pro, vente en gros</div>
            </td>
            <td class="destinataire">
                <div class="destinataire-nom">{{ $compte->boutique }}</div>
                <div>{{ $compte->ville }}{{ $compte->commune ? ', ' . $compte->commune : '' }}</div>
                <div>Édité le {{ now()->translatedFormat('j F Y') }}</div>
            </td>
        </tr>
    </table>
</div>

@forelse($categories as $categorie)
    <h2>{{ $categorie->nom }}</h2>
    <table class="tarifs">
        <thead>
            <tr>
                <th style="width: 40%">Tissu</th>
                <th style="width: 22%">Format</th>
                <th class="droite" style="width: 19%">Prix pro</th>
                <th class="droite" style="width: 19%">Prix public</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categorie->produits as $produit)
                @foreach($produit->formatsPro() as $format)
                    <tr>
                        <td class="produit">{{ $loop->first ? $produit->nom : '' }}</td>
                        <td>{{ $format['libelle'] }}</td>
                        <td class="droite"><span class="prix-pro">{{ $francs($format['pro']) }}</span> <span class="discret">{{ $format['unite'] }}</span></td>
                        <td class="droite discret">{{ $format['detail'] ? $francs($format['detail']) : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
@empty
    <p class="vide">Aucun tarif pro n'est encore publié.</p>
@endforelse

</body>
</html>
