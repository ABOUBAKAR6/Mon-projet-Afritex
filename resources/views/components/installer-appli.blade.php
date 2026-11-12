{{--
  Carte « Installer l'appli » au bas de l'écran du téléphone (logique : installationAppli dans resources/js/app.js).
  Elle n'apparaît que si l'installation est possible (Chrome Android) ou sur iPhone, jamais dans l'appli déjà installée,
  ni par-dessus le bandeau des cookies. Fermée : cachée 14 jours (le menu garde son bouton).
--}}
<div x-data="installationAppli(true)" x-show="visible" x-cloak
     x-transition:enter="transition duration-300 ease-out-fort" x-transition:enter-start="opacity-0 translate-y-3" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition duration-150" x-transition:leave-end="opacity-0"
     role="dialog" aria-labelledby="installation-titre"
     class="sans-impression fixed inset-x-3 bottom-[calc(4.75rem+env(safe-area-inset-bottom))] z-toast max-w-md bg-encre p-4 text-white shadow-[0_18px_40px_-20px_oklch(0.2_0.03_var(--teinte-ombre)/0.6)] sm:inset-x-auto sm:left-6 lg:hidden">
    <div class="flex items-start gap-3">
        <img src="{{ icone_app('192') }}" alt="" width="48" height="48" class="size-12 shrink-0">
        <div class="min-w-0 flex-1">
            <p id="installation-titre" class="font-semibold">Installez l'appli {{ boutique('nom') }}</p>
            <p class="mt-0.5 text-[0.9rem] text-white/75">Vos tissus à portée de main, directement depuis l'écran d'accueil.</p>
        </div>
        <button type="button" @click="refuser()" class="-mr-1 -mt-1 flex size-10 shrink-0 items-center justify-center text-white/70 hover:text-white" aria-label="Fermer">
            <x-icone nom="fermer" class="size-5" />
        </button>
    </div>

    <p x-show="aideIos" x-cloak class="mt-3 border-t border-white/15 pt-3 text-[0.9rem] text-white/85">
        Dans Safari, touchez <x-icone nom="partager" class="inline size-[1.1rem] align-[-0.2em] text-or" /> <strong class="font-semibold">Partager</strong>
        en bas de l'écran, puis <strong class="font-semibold">« Sur l'écran d'accueil »</strong>.
    </p>

    <div class="mt-3 flex gap-2">
        <button type="button" @click="ios && aideIos ? (visible = false) : installer()" class="btn btn-or btn-sm flex-1">
            <x-icone nom="telecharger" class="size-4" />
            <span x-text="ios ? (aideIos ? 'Compris' : 'Comment faire ?') : 'Installer'">Installer</span>
        </button>
        <button type="button" @click="refuser()" class="btn btn-clair btn-sm">Plus tard</button>
    </div>
</div>
