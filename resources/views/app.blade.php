<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ boutique('nom') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">

        <!-- Google Analytics Placeholder -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=GA_MEASUREMENT_ID"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments); }
          gtag('js', new Date());
          gtag('config', 'GA_MEASUREMENT_ID');
        </script>

        <!-- Scripts -->
        @viteReactRefresh
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.jsx"])
        @vite(['resources/css/app.css'])

        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia

        <!-- Cookie Banner -->
        <div id="cookie-banner" class="fixed bottom-6 left-6 right-6 z-[100] hidden animate-bounce-in">
            <div class="max-w-4xl mx-auto bg-afri-dark text-white p-6 rounded-[30px] shadow-2xl border border-afri-gold/30 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <div class="text-2xl">🍪</div>
                    <p class="text-xs leading-relaxed opacity-80 max-w-md">
                        Nous utilisons des cookies pour optimiser votre expérience et analyser notre trafic.
                        En continuant, vous acceptez notre <a href="{{ route('privacy') }}" class="underline hover:text-afri-gold">politique de confidentialité</a>.
                    </p>
                </div>
                <button onclick="acceptCookies()" class="bg-afri-gold text-afri-dark px-8 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all hover:bg-white active:scale-95 whitespace-nowrap">
                    J'accepte
                </button>
            </div>
        </div>

        <style>
            @keyframes bounce-in {
                0% { transform: translateY(100%); opacity: 0; }
                60% { transform: translateY(-10%); opacity: 1; }
                100% { transform: translateY(0); opacity: 1; }
            }
            .animate-bounce-in {
                animation: bounce-in 0.6s cubic-bezier(0.23, 1, 0.32, 1) forwards;
            }
        </style>

        <script>
            function acceptCookies() {
                localStorage.setItem('{{ boutique_slug() }}_cookies_accepted', 'true');
                document.getElementById('cookie-banner').classList.add('hidden');
            }

            document.addEventListener('DOMContentLoaded', () => {
                if (!localStorage.getItem('{{ boutique_slug() }}_cookies_accepted')) {
                    document.getElementById('cookie-banner').classList.remove('hidden');
                }
            });
        </script>
    </body>
</html>
