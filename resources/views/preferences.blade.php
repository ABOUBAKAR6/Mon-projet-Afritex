@extends('layouts.app')
@section('title', 'Préférences · ' . boutique('nom'))
@section('content')
@php
    $onglet = 'flex min-h-11 items-center gap-2 border-b-2 px-4 text-[0.95rem] font-medium transition-colors';
    $carte = 'bg-carte p-6 sm:p-8';
    $option = 'flex min-h-11 cursor-pointer items-center gap-3';
@endphp

<div class="conteneur max-w-4xl pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition">
        <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Préférences</h1>
        <p class="mt-1 text-encre-2">Affichage, notifications et confidentialité de votre compte.</p>
    </div>

    <div role="tablist" aria-label="Rubriques" class="defilement-discret mt-8 flex overflow-x-auto border-b border-trait">
        <button id="tabPref" role="tab" type="button" class="{{ $onglet }} border-brun text-brun-fonce">Affichage</button>
        <button id="tabNotif" role="tab" type="button" class="{{ $onglet }} border-transparent text-encre-2 hover:text-encre">Notifications</button>
        <button id="tabSec" role="tab" type="button" class="{{ $onglet }} border-transparent text-encre-2 hover:text-encre">Confidentialité</button>
    </div>

    {{-- AFFICHAGE --}}
    <div id="contentPref" role="tabpanel" aria-labelledby="tabPref" class="mt-6 space-y-4">
        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Langue</h2>
            <div class="mt-4 space-y-1">
                <label class="{{ $option }}"><input type="radio" name="langue" value="fr" checked class="size-5"> Français</label>
                <label class="{{ $option }}"><input type="radio" name="langue" value="en" class="size-5"> English</label>
            </div>
            <button type="button" onclick="saveLangPref()" class="btn btn-contour mt-5">Enregistrer la langue</button>
        </section>

        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Thème</h2>
            <div class="mt-4 space-y-1">
                <label class="{{ $option }}"><input type="radio" name="theme" value="light" checked class="size-5"> Clair</label>
                <label class="{{ $option }}"><input type="radio" name="theme" value="dark" class="size-5"> Sombre</label>
                <label class="{{ $option }}"><input type="radio" name="theme" value="auto" class="size-5"> Comme mon appareil</label>
            </div>
            <button type="button" onclick="saveThemePref()" class="btn btn-contour mt-5">Enregistrer le thème</button>
        </section>

        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Catalogue</h2>
            <div class="mt-4 space-y-1">
                <label class="{{ $option }}"><input type="checkbox" id="showImages" checked class="size-5"> Afficher les photos des tissus</label>
                <label class="{{ $option }}"><input type="checkbox" id="compactView" class="size-5"> Vue compacte</label>
                <label class="{{ $option }}"><input type="checkbox" id="showPrices" checked class="size-5"> Afficher les prix</label>
            </div>
            <button type="button" onclick="saveDisplayPref()" class="btn btn-contour mt-5">Enregistrer l'affichage</button>
        </section>

        <p class="flex items-start gap-2.5 bg-papier-2 p-4 text-[0.95rem] text-encre-2">
            <x-icone nom="reglages" class="mt-0.5 size-4 shrink-0 text-brun" />
            Ces préférences sont gardées sur cet appareil.
        </p>
    </div>

    {{-- NOTIFICATIONS --}}
    <div id="contentNotif" role="tabpanel" aria-labelledby="tabNotif" class="mt-6 hidden">
        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Ce que vous souhaitez recevoir</h2>
            <div class="mt-4 space-y-1">
                <label class="{{ $option }}"><input type="checkbox" id="notifCommande" checked class="size-5"> Suivi de mes commandes</label>
                <label class="{{ $option }}"><input type="checkbox" id="notifPaiement" checked class="size-5"> Confirmations de paiement</label>
                <label class="{{ $option }}"><input type="checkbox" id="notifStock" checked class="size-5"> Alertes de stock</label>
                <label class="{{ $option }}"><input type="checkbox" id="notifPromo" class="size-5"> Offres et promotions</label>
                <label class="{{ $option }}"><input type="checkbox" id="notifNewsletter" class="size-5"> Lettre d'information</label>
            </div>
            <button type="button" onclick="saveNotifPref()" class="btn btn-brun mt-5">Enregistrer</button>
        </section>
    </div>

    {{-- CONFIDENTIALITÉ --}}
    <div id="contentSec" role="tabpanel" aria-labelledby="tabSec" class="mt-6 hidden space-y-4">
        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Mot de passe</h2>
            <div class="mt-4 grid gap-4 sm:max-w-md">
                <div><label for="mdp-actuel" class="etiquette">Mot de passe actuel</label><input id="mdp-actuel" type="password" autocomplete="current-password" class="champ"></div>
                <div><label for="mdp-nouveau" class="etiquette">Nouveau mot de passe</label><input id="mdp-nouveau" type="password" autocomplete="new-password" class="champ"></div>
                <div><label for="mdp-confirmation" class="etiquette">Confirmer le nouveau mot de passe</label><input id="mdp-confirmation" type="password" autocomplete="new-password" class="champ"></div>
            </div>
            <button type="button" class="btn btn-brun mt-5">Changer le mot de passe</button>
        </section>

        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Cookies</h2>
            <p class="mt-2 text-encre-2">Votre choix actuel :</p>
            <pre id="consentStatus" class="mt-3 overflow-x-auto bg-papier-2 p-4 text-[0.85rem]">Chargement…</pre>
            <button type="button" onclick="resetCookieConsent()" class="btn btn-contour mt-5">Revoir mon choix</button>
        </section>

        <section class="{{ $carte }}">
            <h2 class="titre-display text-[1.2rem]">Mes données</h2>
            <p class="mt-2 max-w-[60ch] text-encre-2">Téléchargez une copie des données gardées sur cet appareil (préférences, panier, consentement), au format JSON.</p>
            <button type="button" onclick="downloadUserData()" class="btn btn-contour mt-5"><x-icone nom="telechargement" /> Télécharger mes données</button>
        </section>

        <section class="border border-[color:color-mix(in_oklch,var(--danger)_30%,transparent)] bg-carte p-6 sm:p-8">
            <h2 class="titre-display text-[1.2rem] text-danger">Supprimer mon compte</h2>
            <p class="mt-2 max-w-[60ch] text-encre-2">Votre compte, vos favoris et votre historique seront effacés. Cette action est définitive.</p>
            <button type="button" onclick="if (confirm('Supprimer définitivement votre compte {{ str_replace("'", '’', boutique('nom')) }} ?')) deleteAccount()" class="btn btn-danger mt-5">Supprimer mon compte</button>
        </section>
    </div>
</div>

<script>
    function loadPreferences() {
        const prefs = cookieManager.getPreferences();
        document.querySelector(`input[name="langue"][value="${prefs.langue || 'fr'}"]`).checked = true;
        document.querySelector(`input[name="theme"][value="${prefs.theme || 'light'}"]`).checked = true;
        document.getElementById('showImages').checked = prefs.showImages !== false;
        document.getElementById('compactView').checked = prefs.compactView || false;
        document.getElementById('showPrices').checked = prefs.showPrices !== false;
        document.getElementById('notifCommande').checked = prefs.notifications?.commande !== false;
        document.getElementById('notifPaiement').checked = prefs.notifications?.paiement !== false;
        document.getElementById('notifStock').checked = prefs.notifications?.stock !== false;
        document.getElementById('notifPromo').checked = prefs.notifications?.promo || false;
        document.getElementById('notifNewsletter').checked = prefs.notifications?.newsletter || false;
        updateConsentStatus();
    }

    function saveLangPref() {
        const prefs = cookieManager.getPreferences();
        prefs.langue = document.querySelector('input[name="langue"]:checked').value;
        cookieManager.setPreferences(prefs);
        alert('Langue enregistrée.');
    }

    function saveThemePref() {
        const theme = document.querySelector('input[name="theme"]:checked').value;
        const prefs = cookieManager.getPreferences();
        prefs.theme = theme;
        cookieManager.setPreferences(prefs);
        applyTheme(theme);
        alert('Thème enregistré.');
    }

    function applyTheme(theme) {
        const sombre = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', sombre);
    }

    function saveDisplayPref() {
        const prefs = cookieManager.getPreferences();
        prefs.showImages = document.getElementById('showImages').checked;
        prefs.compactView = document.getElementById('compactView').checked;
        prefs.showPrices = document.getElementById('showPrices').checked;
        cookieManager.setPreferences(prefs);
        alert('Préférences d\'affichage enregistrées.');
    }

    function saveNotifPref() {
        const prefs = cookieManager.getPreferences();
        prefs.notifications = {
            commande: document.getElementById('notifCommande').checked,
            paiement: document.getElementById('notifPaiement').checked,
            stock: document.getElementById('notifStock').checked,
            promo: document.getElementById('notifPromo').checked,
            newsletter: document.getElementById('notifNewsletter').checked,
        };
        cookieManager.setPreferences(prefs);
        alert('Préférences de notifications enregistrées.');
    }

    function updateConsentStatus() {
        const consent = cookieManager.getConsent();
        document.getElementById('consentStatus').textContent = consent ? JSON.stringify(consent, null, 2) : 'Aucun choix enregistré';
    }

    function resetCookieConsent() {
        if (confirm('Votre choix sur les cookies sera effacé et le bandeau réapparaîtra. Continuer ?')) {
            localStorage.removeItem('cookieConsent');
            location.reload();
        }
    }

    function downloadUserData() {
        const userData = {
            preferences: cookieManager.getPreferences(),
            panier: cookieManager.getPanier(),
            consent: cookieManager.getConsent(),
            downloadDate: new Date().toISOString(),
        };
        const url = URL.createObjectURL(new Blob([JSON.stringify(userData, null, 2)], { type: 'application/json' }));
        const lien = document.createElement('a');
        lien.href = url;
        lien.download = `mes-donnees-{{ boutique_slug() }}-${new Date().toISOString().split('T')[0]}.json`;
        lien.click();
        URL.revokeObjectURL(url);
    }

    async function deleteAccount() {
        try {
            const reponse = await fetch('/api/account/delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
            });
            if (reponse.ok) {
                alert('Votre compte a été supprimé.');
                window.location.href = '/';
            } else {
                alert('La suppression a échoué. Réessayez ou contactez-nous.');
            }
        } catch (erreur) {
            alert('Connexion impossible. Vérifiez votre réseau et réessayez.');
        }
    }

    const onglets = ['Pref', 'Notif', 'Sec'];
    function switchTab(nom) {
        onglets.forEach((o) => {
            const actif = o === nom;
            document.getElementById('content' + o).classList.toggle('hidden', !actif);
            const bouton = document.getElementById('tab' + o);
            bouton.setAttribute('aria-selected', actif ? 'true' : 'false');
            bouton.classList.toggle('border-brun', actif);
            bouton.classList.toggle('text-brun-fonce', actif);
            bouton.classList.toggle('border-transparent', !actif);
            bouton.classList.toggle('text-encre-2', !actif);
        });
    }
    onglets.forEach((o) => document.getElementById('tab' + o).addEventListener('click', () => switchTab(o)));

    document.addEventListener('DOMContentLoaded', loadPreferences);
</script>
@endsection
