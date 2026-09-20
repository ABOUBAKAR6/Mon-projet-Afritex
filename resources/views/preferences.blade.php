@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">⚙️ Paramètres & Préférences</h1>
        <a href="{{ route('dashboard') }}" class="text-afri-gold hover:text-afri-brown transition">← Retour</a>
    </div>

    <!-- Tabs Navigation -->
    <div class="flex gap-2 border-b mb-6">
        <button id="tabPref" class="px-4 py-2 font-semibold border-b-2 border-afri-gold text-afri-gold transition">
            💾 Mes Préférences
        </button>
        <button id="tabNotif" class="px-4 py-2 font-semibold text-gray-600 hover:text-gray-800 transition">
            🔔 Notifications
        </button>
        <button id="tabSec" class="px-4 py-2 font-semibold text-gray-600 hover:text-gray-800 transition">
            🔒 Sécurité & Confidentialité
        </button>
    </div>

    <!-- Tab 1: Préférences -->
    <div id="contentPref" class="space-y-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Langue</h2>
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="langue" value="fr" checked class="w-4 h-4" />
                    <span class="ml-3">🇫🇷 Français</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="langue" value="en" class="w-4 h-4" />
                    <span class="ml-3">🇬🇧 English</span>
                </label>
            </div>
            <button onclick="saveLangPref()" class="mt-4 px-4 py-2 bg-afri-gold text-white rounded-lg font-semibold hover:bg-afri-brown transition">
                Enregistrer
            </button>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Thème</h2>
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="theme" value="light" checked class="w-4 h-4" />
                    <span class="ml-3">☀️ Clair</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="theme" value="dark" class="w-4 h-4" />
                    <span class="ml-3">🌙 Sombre</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="radio" name="theme" value="auto" class="w-4 h-4" />
                    <span class="ml-3">🔄 Automatique (système)</span>
                </label>
            </div>
            <button onclick="saveThemePref()" class="mt-4 px-4 py-2 bg-afri-gold text-white rounded-lg font-semibold hover:bg-afri-brown transition">
                Enregistrer
            </button>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Préférences d'Affichage</h2>
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="showImages" checked class="w-4 h-4" />
                    <span class="ml-3">Afficher les images des produits</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="compactView" class="w-4 h-4" />
                    <span class="ml-3">Vue compacte du catalogue</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="showPrices" checked class="w-4 h-4" />
                    <span class="ml-3">Afficher les prix</span>
                </label>
            </div>
            <button onclick="saveDisplayPref()" class="mt-4 px-4 py-2 bg-afri-gold text-white rounded-lg font-semibold hover:bg-afri-brown transition">
                Enregistrer
            </button>
        </div>

        <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
            <p class="text-sm text-gray-700">
                ℹ️ <strong>Vos préférences sont stockées localement</strong> sur votre appareil et synchronisées avec votre compte si connecté.
            </p>
        </div>
    </div>

    <!-- Tab 2: Notifications -->
    <div id="contentNotif" class="hidden space-y-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">Préférences de Notifications</h2>
            <div class="space-y-3">
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="notifCommande" checked class="w-4 h-4" />
                    <span class="ml-3">📦 Notifications de commande</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="notifPaiement" checked class="w-4 h-4" />
                    <span class="ml-3">💳 Confirmations de paiement</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="notifStock" checked class="w-4 h-4" />
                    <span class="ml-3">📍 Alertes de stock faible</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="notifPromo" class="w-4 h-4" />
                    <span class="ml-3">🎉 Offres et promotions</span>
                </label>
                <label class="flex items-center cursor-pointer">
                    <input type="checkbox" id="notifNewsletter" class="w-4 h-4" />
                    <span class="ml-3">📧 Newsletter hebdomadaire</span>
                </label>
            </div>
            <button onclick="saveNotifPref()" class="mt-4 px-4 py-2 bg-afri-gold text-white rounded-lg font-semibold hover:bg-afri-brown transition">
                Enregistrer
            </button>
        </div>
    </div>

    <!-- Tab 3: Sécurité & Confidentialité -->
    <div id="contentSec" class="hidden space-y-6">
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">🔒 Sécurité du Compte</h2>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-600 mb-2">Mot de passe actuel</p>
                    <input type="password" placeholder="Entrez votre mot de passe" class="w-full px-3 py-2 border rounded-lg" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-2">Nouveau mot de passe</p>
                    <input type="password" placeholder="Nouveau mot de passe" class="w-full px-3 py-2 border rounded-lg" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-2">Confirmer le nouveau mot de passe</p>
                    <input type="password" placeholder="Confirmer" class="w-full px-3 py-2 border rounded-lg" />
                </div>
                <button class="px-4 py-2 bg-afri-gold text-white rounded-lg font-semibold hover:bg-afri-brown transition">
                    Changer le mot de passe
                </button>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">🍪 Consentement aux Cookies</h2>
            <p class="text-gray-700 mb-4">Votre consentement actuel :</p>
            <div id="consentStatus" class="bg-gray-100 p-3 rounded-lg text-sm font-mono mb-4">
                Chargement...
            </div>
            <button onclick="resetCookieConsent()" class="px-4 py-2 bg-red-500 text-white rounded-lg font-semibold hover:bg-red-600 transition">
                Réinitialiser les cookies
            </button>
        </div>

        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold mb-4">📥 Télécharger mes Données</h2>
            <p class="text-gray-700 mb-4">
                Téléchargez une copie de toutes vos données personnelles au format JSON (RGPD).
            </p>
            <button onclick="downloadUserData()" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition">
                Télécharger mes données
            </button>
        </div>

        <div class="bg-white p-6 rounded-lg shadow border-2 border-red-200">
            <h2 class="text-xl font-bold mb-4 text-red-600">🗑️ Zone de Danger</h2>
            <p class="text-gray-700 mb-4">
                <strong>Attention :</strong> Ces actions sont irréversibles.
            </p>
            <button onclick="if(confirm('Êtes-vous sûr ? Cette action est irréversible.')) deleteAccount()" 
                    class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition">
                Supprimer mon compte
            </button>
        </div>
    </div>
</div>

<script>
    // Charger les préférences au chargement
    function loadPreferences() {
        const prefs = cookieManager.getPreferences();
        console.log('Préférences chargées:', prefs);
        
        // Langue
        document.querySelector(`input[name="langue"][value="${prefs.langue || 'fr'}"]`).checked = true;
        
        // Thème
        document.querySelector(`input[name="theme"][value="${prefs.theme || 'light'}"]`).checked = true;
        
        // Affichage
        document.getElementById('showImages').checked = prefs.showImages !== false;
        document.getElementById('compactView').checked = prefs.compactView || false;
        document.getElementById('showPrices').checked = prefs.showPrices !== false;
        
        // Notifications
        document.getElementById('notifCommande').checked = prefs.notifications?.commande !== false;
        document.getElementById('notifPaiement').checked = prefs.notifications?.paiement !== false;
        document.getElementById('notifStock').checked = prefs.notifications?.stock !== false;
        document.getElementById('notifPromo').checked = prefs.notifications?.promo || false;
        document.getElementById('notifNewsletter').checked = prefs.notifications?.newsletter || false;
        
        // Consentement cookies
        updateConsentStatus();
    }

    // Sauvegarder préférences de langue
    function saveLangPref() {
        const langue = document.querySelector('input[name="langue"]:checked').value;
        const prefs = cookieManager.getPreferences();
        prefs.langue = langue;
        cookieManager.setPreferences(prefs);
        alert('✅ Langue enregistrée !');
    }

    // Sauvegarder préférences de thème
    function saveThemePref() {
        const theme = document.querySelector('input[name="theme"]:checked').value;
        const prefs = cookieManager.getPreferences();
        prefs.theme = theme;
        cookieManager.setPreferences(prefs);
        applyTheme(theme);
        alert('✅ Thème enregistré !');
    }

    // Appliquer le thème
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (theme === 'light') {
            document.documentElement.classList.remove('dark');
        } else if (theme === 'auto') {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    }

    // Sauvegarder préférences d'affichage
    function saveDisplayPref() {
        const prefs = cookieManager.getPreferences();
        prefs.showImages = document.getElementById('showImages').checked;
        prefs.compactView = document.getElementById('compactView').checked;
        prefs.showPrices = document.getElementById('showPrices').checked;
        cookieManager.setPreferences(prefs);
        alert('✅ Préférences d\'affichage enregistrées !');
    }

    // Sauvegarder préférences de notifications
    function saveNotifPref() {
        const prefs = cookieManager.getPreferences();
        prefs.notifications = {
            commande: document.getElementById('notifCommande').checked,
            paiement: document.getElementById('notifPaiement').checked,
            stock: document.getElementById('notifStock').checked,
            promo: document.getElementById('notifPromo').checked,
            newsletter: document.getElementById('notifNewsletter').checked
        };
        cookieManager.setPreferences(prefs);
        alert('✅ Préférences de notifications enregistrées !');
    }

    // Afficher le statut de consentement
    function updateConsentStatus() {
        const consent = cookieManager.getConsent();
        const status = document.getElementById('consentStatus');
        if (consent) {
            status.textContent = JSON.stringify(consent, null, 2);
        } else {
            status.textContent = 'Aucun consentement enregistré';
        }
    }

    // Réinitialiser le consentement aux cookies
    function resetCookieConsent() {
        if (confirm('Les cookies seront réinitialisés. Voulez-vous continuer ?')) {
            localStorage.removeItem('cookieConsent');
            location.reload();
        }
    }

    // Télécharger les données utilisateur
    function downloadUserData() {
        const userData = {
            preferences: cookieManager.getPreferences(),
            panier: cookieManager.getPanier(),
            consent: cookieManager.getConsent(),
            downloadDate: new Date().toISOString()
        };

        const dataStr = JSON.stringify(userData, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(dataBlob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `mes-donnees-afritex-${new Date().toISOString().split('T')[0]}.json`;
        link.click();
        URL.revokeObjectURL(url);
    }

    // Supprimer le compte
    async function deleteAccount() {
        try {
            const response = await fetch('/api/account/delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (response.ok) {
                alert('✅ Votre compte a été supprimé.');
                window.location.href = '/';
            } else {
                alert('❌ Erreur lors de la suppression du compte.');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('❌ Erreur réseau');
        }
    }

    // Gestion des onglets
    document.getElementById('tabPref').addEventListener('click', () => {
        switchTab('Pref');
    });
    document.getElementById('tabNotif').addEventListener('click', () => {
        switchTab('Notif');
    });
    document.getElementById('tabSec').addEventListener('click', () => {
        switchTab('Sec');
    });

    function switchTab(tabName) {
        // Masquer tous les onglets
        document.getElementById('contentPref').classList.add('hidden');
        document.getElementById('contentNotif').classList.add('hidden');
        document.getElementById('contentSec').classList.add('hidden');

        // Afficher l'onglet sélectionné
        document.getElementById('content' + tabName).classList.remove('hidden');

        // Mettre à jour le bouton actif
        document.querySelectorAll('[id^="tab"]').forEach(btn => {
            btn.classList.remove('border-b-2', 'border-afri-gold', 'text-afri-gold');
            btn.classList.add('text-gray-600', 'hover:text-gray-800');
        });
        document.getElementById('tab' + tabName).classList.add('border-b-2', 'border-afri-gold', 'text-afri-gold');
    }

    // Charger au chargement de la page
    document.addEventListener('DOMContentLoaded', loadPreferences);
</script>
@endsection
