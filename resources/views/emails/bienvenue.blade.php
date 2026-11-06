<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Bienvenue chez {{ boutique('nom') }}</title>
</head>
{{--
  E-mail de bienvenue, charte de la refonte : brun foncé, or, angles droits.
  Mise en page en tableaux et styles en ligne : seul format lu correctement par Gmail, Outlook et les webmails.
--}}
<body style="margin:0; padding:0; background:{{ couleur('surface') }}; font-family:Arial, Helvetica, sans-serif; color:{{ couleur('encre') }};">
    {{-- Aperçu affiché dans la liste des messages --}}
    <div style="display:none; max-height:0; overflow:hidden;">Votre compte {{ boutique('nom') }} est prêt. En cas d'oubli, votre mot de passe se réinitialise depuis la page de connexion.</div>

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
                            <h1 style="margin:0; font-size:26px; line-height:1.25; color:{{ couleur('encre') }};">Bienvenue, {{ $utilisateur->name }}</h1>
                            <p style="margin:16px 0 0; font-size:16px; line-height:1.6; color:{{ couleur('texte') }};">
                                Votre compte {{ boutique('nom') }} a bien été créé. Vous pouvez dès maintenant commander vos tissus au mètre, au rouleau ou en confection, et suivre vos commandes depuis votre compte.
                            </p>
                        </td>
                    </tr>

                    {{-- ADRESSE ENREGISTRÉE --}}
                    <tr>
                        <td style="padding:24px 32px 0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{{ couleur('bloc') }};">
                                <tr>
                                    <td style="padding:18px 20px;">
                                        <p style="margin:0; font-size:13px; text-transform:uppercase; letter-spacing:1px; color:{{ couleur('accent') }}; font-weight:bold;">Votre identifiant</p>
                                        <p style="margin:6px 0 0; font-size:17px; font-weight:bold; color:{{ couleur('encre') }};">{{ $utilisateur->email }}</p>
                                        <p style="margin:6px 0 0; font-size:14px; line-height:1.5; color:{{ couleur('texte') }};">C'est avec cette adresse que vous vous connectez.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- MOT DE PASSE OUBLIÉ --}}
                    <tr>
                        <td style="padding:28px 32px 0;">
                            <h2 style="margin:0; font-size:18px; color:{{ couleur('encre') }};">Mot de passe oublié ?</h2>
                            <p style="margin:10px 0 0; font-size:15px; line-height:1.6; color:{{ couleur('texte') }};">
                                Pas d'inquiétude : votre adresse est enregistrée. Sur la page de connexion, choisissez « Mot de passe oublié » : nous vous enverrons à cette adresse un lien pour en choisir un nouveau.
                            </p>
                            <p style="margin:12px 0 0; font-size:15px;">
                                <a href="{{ $lienReinitialisation }}" style="color:{{ couleur('accent') }}; font-weight:bold;">Réinitialiser mon mot de passe</a>
                            </p>
                        </td>
                    </tr>

                    {{-- ACTION --}}
                    <tr>
                        <td style="padding:32px 32px 36px;">
                            <table role="presentation" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background:{{ couleur('accent') }};">
                                        <a href="{{ $lienCatalogue }}" style="display:inline-block; padding:14px 26px; color:#ffffff; font-size:16px; font-weight:bold; text-decoration:none;">Découvrir le catalogue</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- PIED --}}
                    <tr>
                        <td style="padding:20px 32px; border-top:1px solid {{ couleur('trait') }};">
                            <p style="margin:0; font-size:13px; line-height:1.6; color:{{ couleur('texte_doux') }};">
                                Vous n'êtes pas à l'origine de cette inscription ? Ignorez ce message, ou répondez-nous pour que nous supprimions le compte.
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
