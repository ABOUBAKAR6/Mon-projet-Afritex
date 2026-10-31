<!DOCTYPE html>
<html lang="fr">
<head>
    @include('partials.tete')
</head>
<body class="boutique">
    {{-- Connexion et inscription : photo de tissus à gauche, formulaire à droite --}}
    <div class="grid min-h-svh lg:grid-cols-[1fr_minmax(28rem,36rem)]">
        <aside class="relative isolate hidden overflow-hidden bg-brun-fonce text-white lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <img src="{{ asset('images/hero-bg.avif') }}" alt="" class="absolute inset-0 -z-10 h-full w-full object-cover">
            <div aria-hidden="true" class="absolute inset-0 -z-10 bg-[oklch(0.18_0.04_40/0.62)]"></div>

            <a href="{{ route('accueil') }}" class="flex items-center gap-3 self-start">
                <x-logo taille="size-11" class="bg-or text-xl text-brun-fonce" />
                <span class="titre-display text-[1.4rem]">AfriTex</span>
            </a>

            <div class="max-w-lg">
                <p class="titre-display text-[2.6rem] leading-[1.1] xl:text-[3.2rem]">Le meilleur tissu, livré chez vous</p>
                <ul class="mt-8 space-y-3 text-white/85">
                    <li class="flex items-center gap-3"><x-icone nom="regle" class="size-5 text-or" /> Au mètre, au rouleau ou en confection</li>
                    <li class="flex items-center gap-3"><x-icone nom="livraison" class="size-5 text-or" /> Livré partout en Côte d'Ivoire sous 24 à 72 h</li>
                    <li class="flex items-center gap-3"><x-icone nom="mobile" class="size-5 text-or" /> Paiement par Mobile Money ou à la livraison</li>
                </ul>
            </div>
        </aside>

        <main id="contenu" class="flex flex-col bg-papier px-5 py-8 sm:px-10 lg:px-14 lg:py-12">
            <a href="{{ route('accueil') }}" class="flex items-center gap-3 self-start lg:hidden">
                <x-logo class="bg-brun-fonce text-xl text-or" />
                <span class="titre-display text-[1.25rem]">AfriTex</span>
            </a>
            <div class="apparition mx-auto my-auto w-full max-w-md py-10">
                {{ $slot }}
            </div>
            <p class="text-[0.9rem] text-encre-2"><a href="{{ route('accueil') }}" class="hover:text-brun hover:underline">Retour à la boutique</a> · <a href="{{ route('cgu') }}" class="hover:text-brun hover:underline">Conditions générales</a></p>
        </main>
    </div>
</body>
</html>
