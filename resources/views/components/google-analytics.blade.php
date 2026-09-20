<!-- Google Analytics Script (chargé conditionnellement) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('analytics.google_analytics_id') }}"></script>
<script>
    // Vérifier le consentement avant de charger GA
    const consent = JSON.parse(localStorage.getItem('cookieConsent') || '{}');
    
    if (consent.analytics) {
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('analytics.google_analytics_id') }}', {
            'anonymize_ip': true,
            'allow_google_signals': false
        });
    }
</script>
