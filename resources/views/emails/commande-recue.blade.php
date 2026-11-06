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
<body style="margin:0; padding:0; background:{{ couleur('surface') }}; font-family:Arial, Helvetica, sans-serif; color:{{ couleur('encre') }};">
    <div style="display:none; max-height:0; overflow:hidden;">Nous avons bien reçu votre commande {{ $numero }}. Suivez son avancement avec le numéro {{ $commande->tracking_number }}.</div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ couleur('surface') }};">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px; background:{{ couleur('carte') }};">

                    {{-- EN-TÊTE --}}
                    <tr>
                        <td style="background:{{ couleur('foncee') }}; padding:24px 32px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="width:40px; height:40px; background:{{ couleur('seconde') }}; color:{{ couleur('foncee') }}; font-size:20px; font-weight:bold; text-align:center; vertical-align:middle;">{{ boutique_initiale() }}</td>
                                    <td style="padding-left:12px; color:#ffffff; font-size:19px; font-weight:bold;">{{ boutique('nom') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- INTRODUCTION --}}
                    <tr>
                        <td style="padding:36px 32px 0;">
                            <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:{{ couleur('accent') }}; font-weight:bold;">Commande {{ $numero }}</p>
                            <h1 style="margin:8px 0 0; font-size:26px; line-height:1.25; color:{{ couleur('encre') }};">Votre commande est bien reçue</h1>
                            <p style="margin:16px 0 0; font-size:16px; line-height:1.6; color:{{ couleur('texte') }};">
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
                                            <div style="height:4px; background:{{ $franchie ? couleur('accent') : couleur('trait') }};"></div>
                                            <p style="margin:8px 0 0; font-size:12px; line-height:1.35; color:{{ $courante ? couleur('encre') : ($franchie ? couleur('accent') : couleur('texte_doux')) }}; {{ $courante ? 'font-weight:bold;' : '' }}">{{ $libelle }}</p>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- SUIVI --}}
                    <tr>
                        <td style="padding:28px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ couleur('bloc') }};">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:{{ couleur('accent') }}; font-weight:bold;">Numéro de suivi</p>
                                        <p style="margin:6px 0 0; font-size:20px; font-weight:bold; letter-spacing:1px; color:{{ couleur('encre') }};">{{ $commande->tracking_number }}</p>
                                        <p style="margin:6px 0 16px; font-size:14px; line-height:1.5; color:{{ couleur('texte') }};">Suivez l'avancement à tout moment, sans vous connecter.</p>
                                        <table role="presentation" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="background:{{ couleur('accent') }};">
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
                            <h2 style="margin:0 0 12px; font-size:18px; color:{{ couleur('encre') }};">Récapitulatif</h2>
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:15px;">
                                @foreach($commande->items as $item)
                                    <tr>
                                        <td style="padding:10px 0; border-top:1px solid {{ couleur('trait') }}; color:{{ couleur('encre') }};">
                                            <strong>{{ $item->produit->nom ?? 'Produit' }}</strong><br>
                                            <span style="font-size:13px; color:{{ couleur('texte_doux') }};">{{ $formats[$item->type_commande] ?? ucfirst((string) $item->type_commande) }} × {{ $quantite($item->quantite) }}</span>
                                        </td>
                                        <td align="right" style="padding:10px 0; border-top:1px solid {{ couleur('trait') }}; white-space:nowrap; color:{{ couleur('encre') }};">{{ $montant($item->sous_total) }}</td>
                                    </tr>
                                @endforeach
                                @if($commande->discount_amount > 0)
                                    <tr>
                                        <td style="padding:10px 0; border-top:1px solid {{ couleur('trait') }}; color:{{ couleur('texte') }};">Réduction{{ $commande->coupon_code ? ' (' . $commande->coupon_code . ')' : '' }}</td>
                                        <td align="right" style="padding:10px 0; border-top:1px solid {{ couleur('trait') }}; color:{{ couleur('texte') }};">− {{ $montant($commande->discount_amount) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td style="padding:12px 0; border-top:2px solid {{ couleur('encre') }}; font-weight:bold;">Total</td>
                                    <td align="right" style="padding:12px 0; border-top:2px solid {{ couleur('encre') }}; font-weight:bold; font-size:17px;">{{ $montant($commande->total) }}</td>
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
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:{{ couleur('accent') }}; font-weight:bold;">Paiement</p>
                                        <p style="margin:6px 0 0; font-size:15px; font-weight:bold; color:{{ couleur('encre') }};">{{ $reglement[0] }}</p>
                                        <p style="margin:4px 0 0; font-size:14px; line-height:1.5; color:{{ couleur('texte') }};">{{ $reglement[1] }}</p>
                                    </td>
                                    <td width="50%" style="padding-left:12px; vertical-align:top;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:{{ couleur('accent') }}; font-weight:bold;">{{ $boutique ? 'Retrait' : 'Livraison' }}</p>
                                        @if($boutique)
                                            <p style="margin:6px 0 0; font-size:15px; font-weight:bold; color:{{ couleur('encre') }};">En boutique</p>
                                            <p style="margin:4px 0 0; font-size:14px; line-height:1.5; color:{{ couleur('texte') }};">Dès le lendemain. Présentez le numéro {{ $numero }} au comptoir.</p>
                                        @else
                                            <p style="margin:6px 0 0; font-size:15px; font-weight:bold; color:{{ couleur('encre') }};">À domicile</p>
                                            <p style="margin:4px 0 0; font-size:14px; line-height:1.5; color:{{ couleur('texte') }};">
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
                        <td style="padding:20px 32px; border-top:1px solid {{ couleur('trait') }};">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:{{ couleur('texte_doux') }};">
                                Retrouvez aussi cette commande dans <a href="{{ $lienCompte }}" style="color:{{ couleur('accent') }};">votre compte</a>. Une question ? Répondez simplement à cet e-mail.
                            </p>
                            <p style="margin:10px 0 0; font-size:13px; color:{{ couleur('texte_doux') }};">{{ boutique('nom') }} · {{ boutique('slogan') }} · Côte d'Ivoire</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
