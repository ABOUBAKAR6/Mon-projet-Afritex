<!DOCTYPE html>
{{--
  Page montrée par l'application installée quand le téléphone n'a plus de connexion
  et que la page demandée n'a jamais été ouverte. Elle est gardée par le service worker
  (public/sw.js) : tout est dans ce fichier, sans CSS ni police à télécharger.
--}}
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="{{ couleur('foncee') }}">
    <title>Pas de connexion · {{ boutique('nom') }}</title>
    <style>
        :root { --fond: {{ couleur('papier') }}; --encre: {{ couleur('encre') }}; --texte: {{ couleur('texte') }}; --foncee: {{ couleur('foncee') }}; --or: {{ couleur('seconde') }}; --accent: {{ couleur('accent') }}; --trait: {{ couleur('trait') }}; }
        * { box-sizing: border-box; margin: 0; }
        html, body { height: 100%; }
        body { display: flex; min-height: 100svh; flex-direction: column; align-items: center; justify-content: center; gap: 1.5rem; padding: 2rem 1.25rem; background: var(--fond); color: var(--encre); font: 400 1.05rem/1.55 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; text-align: center; }
        .marque { display: flex; width: 4.5rem; height: 4.5rem; align-items: center; justify-content: center; background: var(--foncee); color: var(--or); font-size: 2.25rem; font-weight: 600; outline: 2px solid var(--or); outline-offset: -0.55rem; }
        h1 { max-width: 20ch; font-size: 1.6rem; font-weight: 600; line-height: 1.2; letter-spacing: -0.01em; }
        p { max-width: 34ch; color: var(--texte); }
        .actions { display: flex; flex-direction: column; gap: 0.75rem; width: 100%; max-width: 20rem; }
        .btn { display: flex; min-height: 3.25rem; align-items: center; justify-content: center; border: 1px solid var(--accent); padding: 0 1.25rem; font: inherit; font-weight: 500; text-decoration: none; cursor: pointer; }
        .btn-plein { background: var(--accent); color: #fff; }
        .btn-contour { background: transparent; color: var(--accent); }
        .btn:focus-visible { outline: 2px solid var(--or); outline-offset: 2px; }
    </style>
</head>
<body>
    <span class="marque" aria-hidden="true">{{ boutique_initiale() }}</span>
    <h1>Pas de connexion internet</h1>
    <p>Cette page n'est pas disponible hors connexion. Les pages déjà ouvertes restent consultables. Vérifiez votre connexion puis réessayez.</p>
    <div class="actions">
        <button type="button" class="btn btn-plein" onclick="location.reload()">Réessayer</button>
        <a href="/" class="btn btn-contour">Retour à l'accueil</a>
    </div>
    <script>
        // Le réseau revient : on recharge la page demandée
        window.addEventListener('online', () => location.reload());
    </script>
</body>
</html>
