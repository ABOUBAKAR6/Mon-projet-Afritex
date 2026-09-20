<!-- Cookie Consent Banner -->
<div id="cookieConsent" class="hidden fixed bottom-0 left-0 right-0 bg-gray-900 text-white p-4 z-[200]">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex-1">
            <p class="text-sm md:text-base font-medium">
                🍪 Nous utilisons des cookies pour améliorer votre expérience : panier, session, préférences, et analytics (optionnel).
            </p>
        </div>

        <div class="flex gap-2 flex-wrap justify-end">
            <button id="cookieReject" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg text-sm font-semibold transition">
                Refuser
            </button>
            <button id="cookieAccept" class="px-4 py-2 bg-green-600 hover:bg-green-700 rounded-lg text-sm font-semibold transition">
                Accepter
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const consentBanner = document.getElementById('cookieConsent');
    const acceptBtn = document.getElementById('cookieAccept');
    const rejectBtn = document.getElementById('cookieReject');

    // Vérifier si consentement déjà donné
    const showBanner = () => {
        if (!localStorage.getItem('cookieConsent')) {
            consentBanner.classList.remove('hidden');
        }
    };

    // Accepter tout
    acceptBtn.addEventListener('click', () => {
        setCookieConsent({
            essential: true,
            preferences: true,
            analytics: true
        });
        consentBanner.classList.add('hidden');
    });

    // Refuser tout (sauf essentiels)
    rejectBtn.addEventListener('click', () => {
        setCookieConsent({
            essential: true,
            preferences: false,
            analytics: false
        });
        consentBanner.classList.add('hidden');
    });

    // Enregistrer consentement
    const setCookieConsent = (consent) => {
        localStorage.setItem('cookieConsent', JSON.stringify(consent));
        
        // Si analytics accepté, charger Google Analytics
        if (consent.analytics) {
            loadAnalytics();
        }
    };

    // Charger Google Analytics (optionnel)
    const loadAnalytics = () => {
        // À configurer : remplacer GA_ID par votre Google Analytics ID
        // window.dataLayer = window.dataLayer || [];
        // function gtag(){dataLayer.push(arguments);}
        // gtag('js', new Date());
        // gtag('config', 'GA_ID');
    };

    showBanner();
});
</script>
