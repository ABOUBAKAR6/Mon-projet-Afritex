<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $sujet }}</title>
</head>
{{--
  E-mails de l'Espace Pro (compte pro, devis), même charte que l'e-mail de bienvenue.
  Mise en page en tableaux et styles en ligne : seul format lu correctement par Gmail, Outlook et les webmails.
  Données : $nom, $sujet, $lignes, $lien, $bouton (voir App\Notifications\EspaceProNotification).
--}}
<body style="margin:0; padding:0; background:{{ couleur('surface') }}; font-family:Arial, Helvetica, sans-serif; color:{{ couleur('encre') }};">
    {{-- Aperçu affiché dans la liste des messages --}}
    <div style="display:none; max-height:0; overflow:hidden;">{{ $lignes[0] ?? $sujet }}</div>

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

                    {{-- CORPS --}}
                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:{{ couleur('accent') }}; font-weight:bold;">Espace Pro</p>
                            <h1 style="margin:8px 0 0; font-size:26px; line-height:1.25; color:{{ couleur('encre') }};">{{ $sujet }}</h1>
                            <p style="margin:20px 0 0; font-size:16px; line-height:1.6; color:{{ couleur('texte') }};">Bonjour {{ $nom }},</p>
                            @foreach ($lignes as $ligne)
                                <p style="margin:12px 0 0; font-size:16px; line-height:1.6; color:{{ couleur('texte') }};">{{ $ligne }}</p>
                            @endforeach
                        </td>
                    </tr>

                    {{-- ACTION --}}
                    <tr>
                        <td style="padding:28px 32px 0;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:{{ couleur('accent') }};">
                                        <a href="{{ $lien }}" style="display:inline-block; padding:14px 26px; color:#ffffff; font-size:16px; font-weight:bold; text-decoration:none;">{{ $bouton }}</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- SIGNATURE : le revendeur est salué par l'équipe ; l'équipe reçoit une ligne neutre --}}
                    <tr>
                        <td style="padding:28px 32px 36px;">
                            @if ($pourEquipe)
                                <p style="margin:0; font-size:14px; line-height:1.6; color:{{ couleur('texte_doux') }};">Message automatique de l'Espace Pro {{ boutique('nom') }}.</p>
                            @else
                                <p style="margin:0; font-size:15px; line-height:1.6; color:{{ couleur('texte') }};">
                                    À bientôt,<br>
                                    <strong style="color:{{ couleur('encre') }};">L'équipe {{ boutique('nom') }}</strong>
                                </p>
                            @endif
                        </td>
                    </tr>

                    {{-- PIED --}}
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid {{ couleur('trait') }};">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:{{ couleur('texte_doux') }};">
                                Le bouton ne s'ouvre pas ? Copiez ce lien dans votre navigateur :<br>
                                <a href="{{ $lien }}" style="color:{{ couleur('accent') }}; word-break:break-all;">{{ $lien }}</a>
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
