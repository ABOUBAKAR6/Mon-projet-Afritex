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
        this.parallaxe = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
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
