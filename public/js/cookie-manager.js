// Cookie Management Utility
class CookieManager {
    constructor() {
        this.consentKey = 'cookieConsent';
        this.panierKey = 'panierAfriTex';
        this.preferencesKey = 'userPreferences';
    }

    // Obtenir consentement
    getConsent() {
        const consent = localStorage.getItem(this.consentKey);
        return consent ? JSON.parse(consent) : null;
    }

    // Définir consentement
    setConsent(consent) {
        localStorage.setItem(this.consentKey, JSON.stringify(consent));
    }

    // Vérifier si consentement donné
    hasConsent() {
        return this.getConsent() !== null;
    }

    // Panier persistant
    getPanier() {
        const panier = localStorage.getItem(this.panierKey);
        return panier ? JSON.parse(panier) : [];
    }

    setPanier(panier) {
        localStorage.setItem(this.panierKey, JSON.stringify(panier));
        // Émettre événement personnalisé
        document.dispatchEvent(new CustomEvent('panierUpdated', { detail: { panier } }));
    }

    addToPanier(produit) {
        const panier = this.getPanier();
        const existing = panier.find(p => p.id === produit.id);
        
        if (existing) {
            existing.quantite += produit.quantite || 1;
        } else {
            panier.push({
                id: produit.id,
                nom: produit.nom,
                prix: produit.prix,
                quantite: produit.quantite || 1,
                image: produit.image
            });
        }
        
        this.setPanier(panier);
        return panier;
    }

    removeFromPanier(produitId) {
        const panier = this.getPanier();
        const filtered = panier.filter(p => p.id !== produitId);
        this.setPanier(filtered);
        return filtered;
    }

    updatePanier(produitId, quantite) {
        const panier = this.getPanier();
        const item = panier.find(p => p.id === produitId);
        
        if (item) {
            if (quantite <= 0) {
                return this.removeFromPanier(produitId);
            }
            item.quantite = quantite;
            this.setPanier(panier);
        }
        
        return panier;
    }

    // Préférences utilisateur
    getPreferences() {
        const prefs = localStorage.getItem(this.preferencesKey);
        return prefs ? JSON.parse(prefs) : {
            langue: 'fr',
            theme: 'light',
            notifications: true
        };
    }

    setPreferences(preferences) {
        localStorage.setItem(this.preferencesKey, JSON.stringify(preferences));
    }

    // Synchroniser panier (localStorage → session/serveur)
    async syncPanierToServer() {
        const panier = this.getPanier();
        
        if (panier.length === 0) return;

        try {
            const response = await fetch('/api/panier/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                },
                body: JSON.stringify({ panier })
            });

            if (response.ok) {
                // Panier synchronisé
                console.log('Panier synchronisé');
            }
        } catch (error) {
            console.error('Erreur synchronisation panier:', error);
        }
    }

    // Initialiser panier depuis serveur (au chargement)
    async initPanier() {
        try {
            const response = await fetch('/api/panier/current');
            const data = await response.json();
            
            if (data.panier && data.panier.length > 0) {
                this.setPanier(data.panier);
            }
        } catch (error) {
            console.error('Erreur chargement panier:', error);
        }
    }

    // Vider panier
    clearPanier() {
        localStorage.removeItem(this.panierKey);
        document.dispatchEvent(new CustomEvent('panierUpdated', { detail: { panier: [] } }));
    }

    // Vider tout (consentement, panier, prefs)
    clearAll() {
        localStorage.removeItem(this.consentKey);
        localStorage.removeItem(this.panierKey);
        localStorage.removeItem(this.preferencesKey);
    }
}

// Instance globale
const cookieManager = new CookieManager();

// Au chargement, synchroniser le panier
document.addEventListener('DOMContentLoaded', () => {
    cookieManager.initPanier();
});
