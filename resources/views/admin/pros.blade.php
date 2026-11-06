@extends('layouts.admin')
@section('title', 'Comptes pro · ' . boutique('nom'))
@section('content')
@php
    $onglets = [
        [null, 'Tous', $compteurs->sum()],
        ['en_attente', 'À examiner', $compteurs['en_attente'] ?? 0],
        ['valide', 'Ouverts', $compteurs['valide'] ?? 0],
        ['refuse', 'Refusés ou suspendus', $compteurs['refuse'] ?? 0],
    ];
    $categories = \App\Models\Categorie::pluck('nom', 'id');
    $fenetre = 'm-auto w-[min(32rem,calc(100vw-2rem))] bg-carte p-0 text-encre shadow-[0_24px_60px_-24px_oklch(0.2_0.03_var(--teinte-ombre)/0.5)] backdrop:bg-[oklch(0.2_0.03_var(--teinte-ombre)/0.55)]';
@endphp

<x-admin-entete titre="Comptes pro" description="Les revendeurs qui achètent en gros : examinez les demandes, ouvrez ou suspendez leur accès aux prix pro." />

<nav aria-label="Filtrer par statut" class="defilement-discret -mx-4 mb-6 overflow-x-auto px-4 sm:mx-0 sm:px-0">
    <ul class="flex gap-1.5">
        @foreach($onglets as [$valeur, $libelle, $nombre])
            @php $actif = $filtre === $valeur; @endphp
            <li>
                <a href="{{ route('admin.pros', array_filter(['statut' => $valeur])) }}" @if($actif) aria-current="page" @endif
                   class="flex min-h-11 items-center gap-2 whitespace-nowrap px-4 text-[0.95rem] transition-colors {{ $actif ? 'bg-brun-fonce text-white' : 'bg-carte text-encre-2 hover:bg-brun-doux hover:text-brun-fonce' }}">
                    {{ $libelle }}
                    <span class="chiffres text-[0.85rem] {{ $actif ? 'text-or' : 'text-encre-3' }}">{{ $nombre }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

@if($comptes->isEmpty())
    <div class="bg-carte px-6 py-14 text-center">
        <p class="titre-display text-[1.3rem]">{{ $filtre === 'en_attente' ? 'Aucune demande à examiner' : 'Aucun compte pro ici' }}</p>
        <p class="mx-auto mt-2 max-w-[48ch] text-encre-2">Les couturiers, ateliers et boutiques font leur demande depuis la page Espace Pro de la boutique.</p>
        <a href="{{ route('pro.accueil') }}" target="_blank" class="lien-fleche mt-5">Voir la page Espace Pro <x-icone nom="fleche" /></a>
    </div>
@else
    <ul class="space-y-3">
        @foreach($comptes as $compte)
            @php
                $familles = collect($compte->familles ?? [])->map(fn ($id) => $categories[$id] ?? null)->filter();
                $tonStatut = ['en_attente' => ['À examiner', 'pastille-attente'], 'valide' => ['Ouvert', 'pastille-succes'], 'refuse' => [$compte->valide_le ? 'Suspendu' : 'Refusé', 'pastille-danger']][$compte->statut] ?? [$compte->statut, ''];
            @endphp
            <li class="bg-carte" x-data="{ details: {{ $compte->estEnAttente() ? 'true' : 'false' }} }">
                <div class="grid gap-4 p-5 sm:p-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center lg:gap-8">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5">
                            <h2 class="titre-display text-[1.2rem]">{{ $compte->boutique }}</h2>
                            <span class="pastille {{ $tonStatut[1] }}">{{ $tonStatut[0] }}</span>
                        </div>
                        <p class="mt-1 text-[0.95rem] text-encre-2">
                            {{ $compte->libelleActivite() }} · {{ $compte->ville }}{{ $compte->commune ? ', ' . $compte->commune : '' }}
                            · {{ $compte->user->name ?? 'Utilisateur supprimé' }}
                        </p>
                        <p class="mt-1 text-[0.85rem] text-encre-2">
                            Demande du {{ $compte->created_at->translatedFormat('j F Y') }}
                            @if($compte->commandes_pro) · <span class="chiffres">{{ $compte->commandes_pro }}</span> commande{{ $compte->commandes_pro > 1 ? 's' : '' }} pro @endif
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <a href="{{ $compte->lienWhatsapp() }}" target="_blank" rel="noopener noreferrer" class="btn btn-fantome btn-sm"><x-icone nom="message" /> WhatsApp</a>
                        <button type="button" @click="details = !details" :aria-expanded="details" aria-controls="details-{{ $compte->id }}" class="btn btn-fantome btn-sm">
                            Détails <x-icone nom="chevron-bas" class="transition-transform duration-200" ::class="details && 'rotate-180'" />
                        </button>
                        @unless($compte->estValide())
                            <form method="POST" action="{{ route('admin.pros.valider', $compte) }}">
                                @csrf
                                <button type="submit" class="btn btn-brun btn-sm"><x-icone nom="coche" /> {{ $compte->estRefuse() ? 'Rouvrir l\'accès' : 'Valider' }}</button>
                            </form>
                        @endunless
                        @unless($compte->estRefuse())
                            <button type="button" class="btn btn-contour btn-sm"
                                    onclick="ouvrirRefus(@js(route('admin.pros.refuser', $compte)), @js($compte->boutique), {{ $compte->estValide() ? 'true' : 'false' }})">
                                {{ $compte->estValide() ? 'Suspendre' : 'Refuser' }}
                            </button>
                        @endunless
                    </div>
                </div>

                <div id="details-{{ $compte->id }}" x-show="details" x-cloak class="border-t border-trait px-5 py-5 sm:px-6">
                    <dl class="grid gap-x-8 gap-y-4 text-[0.95rem] sm:grid-cols-2 xl:grid-cols-4">
                        <div><dt class="text-encre-2">E-mail</dt><dd class="break-all font-medium">{{ $compte->user->email ?? 'Aucun' }}</dd></div>
                        <div><dt class="text-encre-2">WhatsApp</dt><dd class="chiffres font-medium">{{ $compte->whatsapp }}</dd></div>
                        <div><dt class="text-encre-2">Achats par mois</dt><dd class="font-medium">{{ $compte->libelleVolume() ?? 'Non précisé' }}</dd></div>
                        <div><dt class="text-encre-2">RCCM</dt><dd class="font-medium">{{ $compte->rccm ?: 'Non renseigné' }}</dd></div>
                        @if($familles->isNotEmpty())
                            <div class="sm:col-span-2 xl:col-span-4">
                                <dt class="text-encre-2">Tissus achetés</dt>
                                <dd class="mt-1 flex flex-wrap gap-1.5">
                                    @foreach($familles as $famille)<span class="pastille">{{ $famille }}</span>@endforeach
                                </dd>
                            </div>
                        @endif
                        @if($compte->presentation)
                            <div class="sm:col-span-2 xl:col-span-4">
                                <dt class="text-encre-2">Présentation</dt>
                                <dd class="mt-1 max-w-[75ch] whitespace-pre-line">{{ $compte->presentation }}</dd>
                            </div>
                        @endif
                        @if($compte->estRefuse() && $compte->motif_refus)
                            <div class="sm:col-span-2 xl:col-span-4">
                                <dt class="text-encre-2">Motif communiqué</dt>
                                <dd class="mt-1">{{ $compte->motif_refus }}</dd>
                            </div>
                        @endif
                        @if($compte->valide_le && $compte->estValide())
                            <div><dt class="text-encre-2">Ouvert le</dt><dd class="font-medium">{{ $compte->valide_le->translatedFormat('j F Y') }}</dd></div>
                        @endif
                    </dl>
                </div>
            </li>
        @endforeach
    </ul>

    <div class="mt-6">{{ $comptes->links() }}</div>
@endif

{{-- FENÊTRE : REFUSER OU SUSPENDRE --}}
<dialog id="fenetre-refus" class="{{ $fenetre }}" aria-labelledby="titre-refus">
    <form method="POST" id="form-refus" class="p-6 sm:p-8">
        @csrf
        <div class="flex items-start justify-between gap-4">
            <h2 id="titre-refus" class="titre-display text-[1.4rem]">Refuser la demande</h2>
            <button type="button" onclick="this.closest('dialog').close()" class="-m-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2"><x-icone nom="fermer" /><span class="sr-only">Fermer</span></button>
        </div>
        <p id="texte-refus" class="mt-3 text-encre-2"></p>
        <div class="mt-5">
            <label for="motif_refus" class="etiquette">Motif communiqué au revendeur <span class="font-normal text-encre-2">(facultatif)</span></label>
            <input id="motif_refus" type="text" name="motif_refus" maxlength="255" autofocus class="champ" placeholder="Ex. : activité hors de notre zone de livraison">
            <p class="aide">Il figure dans l'e-mail envoyé et sur sa page Espace Pro.</p>
        </div>
        <div class="mt-8 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-contour" onclick="this.closest('dialog').close()">Annuler</button>
            <button type="submit" id="bouton-refus" class="btn btn-danger">Refuser la demande</button>
        </div>
    </form>
</dialog>

<script>
function ouvrirRefus(action, boutique, suspension) {
    document.getElementById('form-refus').action = action;
    document.getElementById('titre-refus').textContent = suspension ? 'Suspendre l\'accès pro' : 'Refuser la demande';
    document.getElementById('bouton-refus').textContent = suspension ? 'Suspendre l\'accès' : 'Refuser la demande';
    document.getElementById('texte-refus').textContent = suspension
        ? boutique + ' ne verra plus les prix pro et ne pourra plus commander en gros. Ses commandes passées restent visibles dans Commandes.'
        : boutique + ' sera prévenu par e-mail. Il pourra toujours acheter sur la boutique au prix public.';
    document.getElementById('motif_refus').value = '';
    document.getElementById('fenetre-refus').showModal();
}
</script>

@endsection
