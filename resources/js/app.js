import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

/*
  Envoi d'une image (photo de profil, logo) : l'image choisie est réduite dans le navigateur
  avant l'envoi, pour qu'une photo de 20 Mo prise au téléphone passe sous la limite du serveur.
  « cote » : plus grand côté en pixels. « format » : image/png garde la transparence (logo).
*/
Alpine.data('televersementImage', (cote = 600, format = 'image/jpeg') => ({
    apercu: null,
    erreur: null,
    enCours: false,

    async choisir(evenement) {
        const champ = evenement.target;
        const fichier = champ.files[0];
        this.erreur = null;
        this.apercu = null;
        if (!fichier) return;
        if (!fichier.type.startsWith('image/')) {
            this.erreur = 'Choisissez une image (JPG, PNG ou WebP).';
            champ.value = '';
            return;
        }

        this.enCours = true;
        try {
            const image = await createImageBitmap(fichier);
            const echelle = Math.min(1, cote / Math.max(image.width, image.height));
            const toile = document.createElement('canvas');
            toile.width = Math.round(image.width * echelle);
            toile.height = Math.round(image.height * echelle);
            toile.getContext('2d').drawImage(image, 0, 0, toile.width, toile.height);

            const blob = await new Promise((ok) => toile.toBlob(ok, format, 0.88));
            const extension = format === 'image/png' ? 'png' : 'jpg';
            const reduit = new File([blob], `image.${extension}`, { type: format });

            const transfert = new DataTransfer();
            transfert.items.add(reduit);
            champ.files = transfert.files;
            this.apercu = URL.createObjectURL(reduit);
        } catch {
            this.erreur = 'Cette image ne peut pas être lue. Essayez un autre fichier.';
            champ.value = '';
        } finally {
            this.enCours = false;
        }
    },

    annuler(champ) {
        this.apercu = null;
        this.erreur = null;
        champ.value = '';
    },
}));

/*
  Rangée défilante (catalogue d'une catégorie) : flèches, barre de progression et parallaxe.
  Parallaxe : chaque photo glisse un peu moins vite que sa carte, selon sa distance au centre de la rangée.
  Elle est coupée si le visiteur réduit les animations.
*/
Alpine.data('rangeeDefilante', () => ({
    part: 1,
    decalage: 0,
    auDebut: true,
    aLaFin: false,
    image: null,

    init() {
        // Pas de parallaxe au téléphone : la rangée y devient une grille de 2 colonnes (catalogue d'une famille)
        this.parallaxe = !window.matchMedia('(prefers-reduced-motion: reduce)').matches
            && window.matchMedia('(min-width: 640px)').matches;
        this.$refs.piste.classList.toggle('rangee-parallaxe', this.parallaxe);
        this.suivre();
        window.addEventListener('resize', () => this.suivre(), { passive: true });
    },

    // Une seule mise à jour par image affichée, même si l'événement scroll arrive plus souvent
    suivre() {
        if (this.image) return;
        this.image = requestAnimationFrame(() => {
            this.image = null;
            const piste = this.$refs.piste;
            const max = piste.scrollWidth - piste.clientWidth;
            this.part = piste.clientWidth / piste.scrollWidth;
            this.decalage = piste.scrollLeft / piste.clientWidth;
            this.auDebut = piste.scrollLeft <= 2;
            this.aLaFin = piste.scrollLeft >= max - 2;

            if (!this.parallaxe) return;
            const cadre = piste.getBoundingClientRect();
            const centre = cadre.left + cadre.width / 2;
            piste.querySelectorAll('article').forEach((carte) => {
                const boite = carte.getBoundingClientRect();
                const ecart = Math.max(-1, Math.min(1, (boite.left + boite.width / 2 - centre) / cadre.width));
                // 7 % de la largeur au plus : la photo est agrandie de 18 %, il reste de la marge de chaque côté
                carte.style.setProperty('--parallaxe', `${(-ecart * boite.width * 0.07).toFixed(1)}px`);
            });
        });
    },

    // Avance ou recule d'environ une largeur visible, calée sur une carte
    glisser(sens) {
        const piste = this.$refs.piste;
        const carte = piste.querySelector('article');
        const pas = carte ? carte.getBoundingClientRect().width + parseFloat(getComputedStyle(piste).columnGap || 0) : piste.clientWidth;
        const nombre = Math.max(1, Math.floor(piste.clientWidth / pas));
        piste.scrollBy({ left: sens * pas * nombre });
    },
}));

/*
  Installer l'application (PWA). Sur Android, Chrome propose l'installation par l'événement
  beforeinstallprompt, capté tôt dans layouts/app et rangé dans window.invitationInstallation.
  Sur iPhone, rien ne peut l'ouvrir : on montre le chemin Partager → « Sur l'écran d'accueil ».
  « bandeau » : la carte du bas de l'écran (refusée = cachée 14 jours) ; sinon, le bouton du menu (toujours là).
*/
const CLE_REFUS_INSTALLATION = 'installation-refusee';
const DUREE_REFUS = 14 * 24 * 3600 * 1000;

const lireStockage = (cle) => { try { return localStorage.getItem(cle); } catch { return null; } };

Alpine.data('installationAppli', (bandeau = false) => ({
    possible: !!window.invitationInstallation,
    ios: /iphone|ipad|ipod/i.test(navigator.userAgent),
    installee: window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true,
    visible: false,
    aideIos: false,

    get proposable() {
        return !this.installee && (this.possible || this.ios);
    },

    init() {
        if (this.installee) return;
        window.addEventListener('installation-possible', () => { this.possible = true; this.montrerBandeau(); });
        window.addEventListener('appinstalled', () => { this.installee = true; this.visible = false; });
        this.montrerBandeau();
    },

    montrerBandeau() {
        if (!bandeau || !this.proposable || this.visible) return;
        const refus = Number(lireStockage(CLE_REFUS_INSTALLATION));
        if (refus && Date.now() - refus < DUREE_REFUS) return;
        // Pas par-dessus le bandeau des cookies : on attendra la page suivante
        if (!lireStockage('cookieConsent')) return;
        setTimeout(() => { this.visible = true; }, 2500);
    },

    async installer() {
        const invitation = window.invitationInstallation;
        if (invitation) {
            invitation.prompt();
            const { outcome } = await invitation.userChoice;
            window.invitationInstallation = null; // Chrome ne la sert qu'une fois
            this.possible = false;
            this.visible = false;
            if (outcome === 'dismissed') this.refuser();
        } else if (this.ios) {
            this.aideIos = !this.aideIos;
        }
    },

    refuser() {
        this.visible = false;
        try { localStorage.setItem(CLE_REFUS_INSTALLATION, String(Date.now())); } catch { /* navigation privée */ }
    },
}));

Alpine.start();

/*
  Révélation au défilement : chaque élément .revele apparaît une seule fois en entrant à l'écran.
  Le décalage entre éléments d'un même groupe se règle avec style="--delai: 80ms".
  Sans IntersectionObserver (ou si le visiteur réduit les animations), tout est visible d'emblée.
*/
const elements = document.querySelectorAll('.revele');
const reduit = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

if (!('IntersectionObserver' in window) || reduit) {
    elements.forEach((el) => el.classList.add('est-visible'));
} else {
    const observateur = new IntersectionObserver(
        (entrees) => {
            entrees.forEach((entree) => {
                if (entree.isIntersecting) {
                    entree.target.classList.add('est-visible');
                    observateur.unobserve(entree.target);
                }
            });
        },
        { rootMargin: '0px 0px -8% 0px', threshold: 0.08 },
    );
    elements.forEach((el) => observateur.observe(el));
}
