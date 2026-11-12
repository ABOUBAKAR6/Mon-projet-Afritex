/*
 * Service worker de la boutique : rend le site installable comme une application
 * et garde l'essentiel quand la connexion coupe.
 *
 * - Pages publiques (accueil, catalogue, fiches produit…) : toujours le réseau d'abord,
 *   la dernière version vue sert seulement sans connexion.
 * - Fichiers du site (/build), photos et icônes : gardés, car leur nom change à chaque nouvelle version.
 * - Panier, compte, commandes, paiement, administration : jamais gardés (données personnelles).
 * - Sans connexion et sans copie : page /hors-ligne.
 *
 * Changer VERSION quand ce fichier change de logique : les anciens caches sont supprimés.
 */
const VERSION = 'v2';
const CACHE_PAGES = `pages-${VERSION}`;
const CACHE_FICHIERS = `fichiers-${VERSION}`;
const PAGE_HORS_LIGNE = '/hors-ligne';
const MAX_FICHIERS = 200;
const DELAI_RESEAU = 6000; // au-delà, on montre la copie gardée si elle existe

const PAGES_PUBLIQUES = [/^\/$/, /^\/catalogue/, /^\/produit\//, /^\/a-propos$/, /^\/faq$/, /^\/cgu$/, /^\/privacy$/];
const FICHIERS = [/^\/build\//, /^\/images\//, /^\/icones\//, /^\/favicon\.ico$/];
const POLICES = ['https://fonts.googleapis.com', 'https://fonts.gstatic.com'];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_PAGES)
      .then((cache) => cache.add(new Request(PAGE_HORS_LIGNE, { cache: 'reload' })))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((noms) => Promise.all(
        noms.filter((nom) => nom !== CACHE_PAGES && nom !== CACHE_FICHIERS).map((nom) => caches.delete(nom))
      ))
      .then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', (event) => {
  const requete = event.request;
  if (requete.method !== 'GET') return;

  const url = new URL(requete.url);
  const memeSite = url.origin === self.location.origin;

  if (requete.mode === 'navigate') {
    if (!memeSite) return;
    const publique = PAGES_PUBLIQUES.some((motif) => motif.test(url.pathname));
    event.respondWith(page(requete, publique));
    return;
  }

  if ((memeSite && FICHIERS.some((motif) => motif.test(url.pathname))) || POLICES.includes(url.origin)) {
    event.respondWith(fichier(requete));
  }
  // Tout le reste (formulaires, panier, API…) passe directement par le réseau.
});

/** Page : réseau d'abord ; sans réseau, la copie gardée ou la page hors ligne. */
async function page(requete, publique) {
  const cache = await caches.open(CACHE_PAGES);
  const reseau = fetch(requete).then((reponse) => {
    if (publique && reponse.ok && !reponse.redirected) {
      cache.put(requete, reponse.clone());
    }
    return reponse;
  });
  reseau.catch(() => {}); // évite une erreur dans la console quand la copie a déjà répondu

  try {
    if (!publique) return await reseau;
    const copie = await cache.match(requete);
    if (!copie) return await reseau;
    // Réseau lent : la copie gardée s'affiche, la page fraîche remplacera la copie pour la prochaine fois
    return await Promise.race([reseau, attendre(DELAI_RESEAU).then(() => copie)]);
  } catch (erreur) {
    return (publique && await cache.match(requete)) || await cache.match(PAGE_HORS_LIGNE) || Response.error();
  }
}

/** Fichiers du site, photos, polices : la copie gardée d'abord, sinon le réseau (et on garde). */
async function fichier(requete) {
  const cache = await caches.open(CACHE_FICHIERS);
  const copie = await cache.match(requete);
  if (copie) return copie;

  const reponse = await fetch(requete);
  if (reponse.ok || reponse.type === 'opaque') {
    await cache.put(requete, reponse.clone());
    limiter(cache);
  }
  return reponse;
}

/** Garde au plus MAX_FICHIERS fichiers : les plus anciens partent d'abord. */
async function limiter(cache) {
  const cles = await cache.keys();
  for (let i = 0; i < cles.length - MAX_FICHIERS; i++) {
    await cache.delete(cles[i]);
  }
}

function attendre(ms) {
  return new Promise((resoudre) => setTimeout(resoudre, ms));
}
