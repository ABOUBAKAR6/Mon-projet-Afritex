<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Commande {{ $numero }} bien reçue</title>
</head>
{{-- Même charte que emails.bienvenue : tableaux et styles en ligne pour Gmail, Outlook et les webmails --}}
@php
    $formats = [
        'metre' => 'Au mètre', 'rouleau' => 'Au rouleau', 'pantalon' => 'Pantalon',
        'chemise_courte' => 'Chemise manches courtes', 'chemise_longue' => 'Chemise manches longues',
        'tunique_courte' => 'Tunique manches courtes', 'tunique_longue' => 'Tunique manches longues',
    ];
@endphp
<body style="margin:0; padding:0; background:#f6f1ec; font-family:Arial, Helvetica, sans-serif; color:#2b1d16;">
    <div style="display:none; max-height:0; overflow:hidden;">Nous avons bien reçu votre commande {{ $numero }}. Suivez son avancement avec le numéro {{ $commande->tracking_number }}.</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f1ec;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:#fffcf9;">

                    {{-- EN-TÊTE --}}
                    <tr>
                        <td style="background:#4a1b0c; padding:24px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:40px; height:40px; background:#c8963e; color:#4a1b0c; font-size:20px; font-weight:bold; text-align:center; vertical-align:middle;">A</td>
                                    <td style="padding-left:12px; color:#ffffff; font-size:19px; font-weight:bold;">AfriTex</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- INTRODUCTION --}}
                    <tr>
                        <td style="padding:36px 32px 0;">
                            <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#8b4513; font-weight:bold;">Commande {{ $numero }}</p>
                            <h1 style="margin:8px 0 0; font-size:26px; line-height:1.25; color:#2b1d16;">Votre commande est bien reçue</h1>
                            <p style="margin:16px 0 0; font-size:16px; line-height:1.6; color:#4d3b31;">
                                Merci {{ $commande->user->name }}. Nous préparons vos tissus et vous écrirons à chaque étape.
                                @if($boutique)
                                    Votre commande sera prête au retrait en boutique dès le lendemain.
                                @else
                                    Elle vous sera livrée sous 24 à 72 h.
                                @endif
                            </p>
                        </td>
                    </tr>

                    {{-- PARCOURS DE LA COMMANDE --}}
                    <tr>
                        <td style="padding:28px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    @foreach($etapes as $cle => $libelle)
                                        @php $franchie = $position !== false && $loop->index <= $position; $courante = $loop->index === $position; @endphp
                                        <td width="20%" style="padding:0 2px; vertical-align:top;">
                                            <div style="height:4px; background:{{ $franchie ? '#8b4513' : '#eadfd5' }};"></div>
                                            <p style="margin:8px 0 0; font-size:12px; line-height:1.35; color:{{ $courante ? '#2b1d16' : ($franchie ? '#8b4513' : '#6b584c') }}; {{ $courante ? 'font-weight:bold;' : '' }}">{{ $libelle }}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- SUIVI --}}
                    <tr>
                        <td style="padding:28px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3e9df;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#8b4513; font-weight:bold;">Numéro de suivi</p>
                                        <p style="margin:6px 0 0; font-size:20px; font-weight:bold; letter-spacing:1px; color:#2b1d16;">{{ $commande->tracking_number }}</p>
                                        <p style="margin:6px 0 16px; font-size:14px; line-height:1.5; color:#4d3b31;">Suivez l'avancement à tout moment, sans vous connecter.</p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background:#8b4513;">
                                                    <a href="{{ $lienSuivi }}" style="display:inline-block; padding:13px 24px; color:#ffffff; font-size:15px; font-weight:bold; text-decoration:none;">Suivre ma commande</a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- RÉCAPITULATIF --}}
                    <tr>
                        <td style="padding:32px 32px 0;">
                            <h2 style="margin:0 0 12px; font-size:18px; color:#2b1d16;">Récapitulatif</h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;">
                                @foreach($commande->items as $item)
                                    <tr>
                                        <td style="padding:10px 0; border-top:1px solid #eadfd5; color:#2b1d16;">
                                            <strong>{{ $item->produit->nom ?? 'Produit' }}</strong><br>
                                            <span style="font-size:13px; color:#6b584c;">{{ $formats[$item->type_commande] ?? ucfirst((string) $item->type_commande) }} × {{ $quantite($item->quantite) }}</span>
                                        </td>
                                        <td align="right" style="padding:10px 0; border-top:1px solid #eadfd5; white-space:nowrap; color:#2b1d16;">{{ $montant($item->sous_total) }}</td>
                                    </tr>
                                @endforeach
                                @if($commande->discount_amount > 0)
                                    <tr>
                                        <td style="padding:10px 0; border-top:1px solid #eadfd5; color:#4d3b31;">Réduction{{ $commande->coupon_code ? ' (' . $commande->coupon_code . ')' : '' }}</td>
                                        <td align="right" style="padding:10px 0; border-top:1px solid #eadfd5; color:#4d3b31;">− {{ $montant($commande->discount_amount) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 0; border-top:2px solid #2b1d16; font-weight:bold;">Total</td>
                                    <td align="right" style="padding:12px 0; border-top:2px solid #2b1d16; font-weight:bold; font-size:17px;">{{ $montant($commande->total) }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- PAIEMENT ET REMISE --}}
                    <tr>
                        <td style="padding:24px 32px 36px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" style="padding-right:12px; vertical-align:top;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#8b4513; font-weight:bold;">Paiement</p>
                                        <p style="margin:6px 0 0; font-size:15px; font-weight:bold; color:#2b1d16;">{{ $reglement[0] }}</p>
                                        <p style="margin:4px 0 0; font-size:14px; line-height:1.5; color:#4d3b31;">{{ $reglement[1] }}</p>
                                    </td>
                                    <td width="50%" style="padding-left:12px; vertical-align:top;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#8b4513; font-weight:bold;">{{ $boutique ? 'Retrait' : 'Livraison' }}</p>
                                        @if($boutique)
                                            <p style="margin:6px 0 0; font-size:15px; font-weight:bold; color:#2b1d16;">En boutique</p>
                                            <p style="margin:4px 0 0; font-size:14px; line-height:1.5; color:#4d3b31;">Dès le lendemain. Présentez le numéro {{ $numero }} au comptoir.</p>
                                        @else
                                            <p style="margin:6px 0 0; font-size:15px; font-weight:bold; color:#2b1d16;">À domicile</p>
                                            <p style="margin:4px 0 0; font-size:14px; line-height:1.5; color:#4d3b31;">
                                                @foreach($adresse as $ligne){{ $ligne }}@if(!$loop->last)<br>@endif @endforeach
                                            </p>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- PIED --}}
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #eadfd5;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#6b584c;">
                                Retrouvez aussi cette commande dans <a href="{{ $lienCompte }}" style="color:#8b4513;">votre compte</a>. Une question ? Répondez simplement à cet e-mail.
                            </p>
                            <p style="margin:10px 0 0; font-size:13px; color:#6b584c;">AfriTex · Tissus africains, gros et détail · Côte d'Ivoire</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
