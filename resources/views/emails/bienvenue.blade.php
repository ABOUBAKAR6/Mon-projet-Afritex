<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Bienvenue chez AfriTex</title>
</head>
{{--
  E-mail de bienvenue, charte de la refonte : brun foncé, or, angles droits.
  Mise en page en tableaux et styles en ligne : seul format lu correctement par Gmail, Outlook et les webmails.
--}}
<body style="margin:0; padding:0; background:#f6f1ec; font-family:Arial, Helvetica, sans-serif; color:#2b1d16;">
    {{-- Aperçu affiché dans la liste des messages --}}
    <div style="display:none; max-height:0; overflow:hidden;">Votre compte AfriTex est prêt. En cas d'oubli, votre mot de passe se réinitialise depuis la page de connexion.</div>

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

                    {{-- CORPS --}}
                    <tr>
                        <td style="padding:36px 32px 8px;">
                            <h1 style="margin:0; font-size:26px; line-height:1.25; color:#2b1d16;">Bienvenue, {{ $utilisateur->name }}</h1>
                            <p style="margin:16px 0 0; font-size:16px; line-height:1.6; color:#4d3b31;">
                                Votre compte AfriTex a bien été créé. Vous pouvez dès maintenant commander vos tissus au mètre, au rouleau ou en confection, et suivre vos commandes depuis votre compte.
                            </p>
                        </td>
                    </tr>

                    {{-- ADRESSE ENREGISTRÉE --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f3e9df;">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:#8b4513; font-weight:bold;">Votre identifiant</p>
                                        <p style="margin:6px 0 0; font-size:17px; font-weight:bold; color:#2b1d16;">{{ $utilisateur->email }}</p>
                                        <p style="margin:6px 0 0; font-size:14px; line-height:1.5; color:#4d3b31;">C'est avec cette adresse que vous vous connectez.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- MOT DE PASSE OUBLIÉ --}}
                    <tr>
                        <td style="padding:28px 32px 0;">
                            <h2 style="margin:0; font-size:18px; color:#2b1d16;">Mot de passe oublié ?</h2>
                            <p style="margin:10px 0 0; font-size:15px; line-height:1.6; color:#4d3b31;">
                                Pas d'inquiétude : votre adresse est enregistrée. Sur la page de connexion, choisissez « Mot de passe oublié » : nous vous enverrons à cette adresse un lien pour en choisir un nouveau.
                            </p>
                            <p style="margin:12px 0 0; font-size:15px;">
                                <a href="{{ $lienReinitialisation }}" style="color:#8b4513; font-weight:bold;">Réinitialiser mon mot de passe</a>
                            </p>
                        </td>
                    </tr>

                    {{-- ACTION --}}
                    <tr>
                        <td style="padding:32px 32px 36px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:#8b4513;">
                                        <a href="{{ $lienCatalogue }}" style="display:inline-block; padding:14px 26px; color:#ffffff; font-size:16px; font-weight:bold; text-decoration:none;">Découvrir le catalogue</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- PIED --}}
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid #eadfd5;">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:#6b584c;">
                                Vous n'êtes pas à l'origine de cette inscription ? Ignorez ce message, ou répondez-nous pour que nous supprimions le compte.
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
