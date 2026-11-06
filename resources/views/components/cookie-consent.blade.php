{{-- Bandeau de consentement aux cookies : carte compacte, au-dessus de la barre d'accès rapide sur téléphone --}}
<div id="cookieConsent" role="dialog" aria-live="polite" aria-labelledby="cookieConsentTitre"
     class="fixed inset-x-3 bottom-[4.75rem] z-toast hidden max-w-md bg-encre p-5 text-[0.95rem] text-white shadow-[0_18px_40px_-20px_oklch(0.2_0.03_var(--teinte-ombre)/0.6)] sm:inset-x-auto sm:left-6 lg:bottom-6">
    <p id="cookieConsentTitre" class="font-semibold">Cookies et confidentialité</p>
    <p class="mt-1.5 text-white/80">Nous utilisons des cookies pour votre panier, votre session et vos préférences. La mesure d'audience est facultative.</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <button id="cookieAccept" type="button" class="btn btn-or btn-sm">Tout accepter</button>
        <button id="cookieReject" type="button" class="btn btn-clair btn-sm">Seulement l'essentiel</button>
        <a href="{{ route('privacy') }}" class="btn btn-sm text-white/80 underline underline-offset-4 hover:text-white">En savoir plus</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const banniere = document.getElementById('cookieConsent');
    const accepter = document.getElementById('cookieAccept');
    const refuser = document.getElementById('cookieReject');

    const enregistrer = (consentement) => {
        localStorage.setItem('cookieConsent', JSON.stringify(consentement));
        banniere.classList.add('hidden');
        // Mesure d'audience : à brancher ici si consentement.analytics (identifiant Google Analytics à configurer)
    };

    accepter.addEventListener('click', () => enregistrer({ essential: true, preferences: true, analytics: true }));
    refuser.addEventListener('click', () => enregistrer({ essential: true, preferences: false, analytics: false }));

    if (!localStorage.getItem('cookieConsent')) {
        banniere.classList.remove('hidden');
    }
});
</script>
