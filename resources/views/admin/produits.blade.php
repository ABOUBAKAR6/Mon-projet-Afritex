@extends('layouts.admin')
@section('title', 'Produits · ' . boutique('nom'))
@section('content')
@php
    // Données de chaque produit pour pré-remplir la fenêtre « Modifier » (tarifs réels, sinon anciennes colonnes de prix)
    $donneesProduits = $produits->mapWithKeys(function ($p) {
        $tarifs = collect(is_array($p->tarifications) ? $p->tarifications : [])
            ->filter(fn ($t) => isset($t['label']))
            ->values();
        if ($tarifs->isEmpty()) {
            $tarifs = collect([
                ['label' => 'Au mètre', 'prix' => $p->prix_metre],
                ['label' => 'Au rouleau', 'prix' => $p->prix_rouleau],
                ['label' => 'Pantalon', 'prix' => $p->prix_pantalon],
                ['label' => 'Chemise', 'prix' => $p->prix_chemise],
                ['label' => 'Tunique', 'prix' => $p->prix_tunique],
            ])->filter(fn ($t) => $t['prix'] !== null)->values();
        }
        return [$p->id => [
            'nom' => $p->nom,
            'categorie_id' => $p->categorie_id,
            'stock' => $p->stock,
            'description' => $p->description,
            'couleurs' => implode(', ', (array) ($p->couleurs ?? [])),
            'meta_title' => $p->meta_title,
            'meta_description' => $p->meta_description,
            'image' => $p->image ? asset($p->image) : null,
            'tarifs' => $tarifs,
            'prix_pro' => (object) ($p->prix_pro ?? []),
            'couleur_image' => $p->couleur_image,
            'photos' => $p->images->map(fn ($i) => ['id' => $i->id, 'url' => asset($i->chemin), 'couleur' => $i->couleur])->values(),
        ]];
    });
@endphp

<x-admin-entete titre="Produits" :description="$produits->count() . ' produits au catalogue'">
    <button type="button" onclick="document.getElementById('modal-ajouter').showModal()" class="btn btn-brun"><x-icone nom="plus" /> Ajouter un produit</button>
</x-admin-entete>

@if($errors->any())
    {{-- Les fenêtres se ferment au rechargement : le motif du refus s'affiche ici --}}
    <div class="mb-6 flex items-start gap-3 bg-danger-doux p-5 text-danger" role="alert">
        <x-icone nom="alerte" class="mt-0.5 size-5 shrink-0" />
        <div>
            <p class="font-semibold">Le produit n'a pas été enregistré.</p>
            <ul class="mt-1 space-y-0.5 text-[0.95rem]">@foreach($errors->all() as $erreur)<li>{{ $erreur }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<section class="bg-carte" aria-label="Liste des produits">
    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <label for="search" class="sr-only">Rechercher un produit</label>
        <div class="relative w-full sm:max-w-sm">
            <x-icone nom="recherche" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-encre-3" />
            <input type="search" id="search" class="champ pl-10" placeholder="Rechercher un produit" oninput="filterProduits()">
        </div>
        <p id="compte-produits" class="text-[0.92rem] text-encre-2" aria-live="polite"></p>
    </div>
    <div class="overflow-x-auto">
        <table class="tableau tableau-empile">
            <thead>
                <tr>
                    <th scope="col">Produit</th>
                    <th scope="col">Catégorie</th>
                    <th scope="col" class="text-right">Prix au mètre</th>
                    <th scope="col">Stock</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse($produits as $produit)
                    {{-- Toute la ligne ouvre la fenêtre « Modifier » (script en bas de page) --}}
                    <tr class="prod-row cursor-pointer" data-id="{{ $produit->id }}" data-nom="{{ mb_strtolower($produit->nom . ' ' . ($produit->categorie->nom ?? '')) }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden bg-papier-2 text-brun-trait">
                                    @if($produit->image && file_exists(public_path($produit->image)))
                                        <img src="{{ asset($produit->image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <x-icone nom="image" class="size-5" />
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <button type="button" onclick="openEditProduit({{ $produit->id }})" class="block max-w-full truncate text-left font-medium hover:text-brun hover:underline">{{ $produit->nom }}</button>
                                    <span class="block text-[0.85rem] text-encre-2">{{ $produit->images->count() }} photo{{ $produit->images->count() > 1 ? 's' : '' }} supplémentaire{{ $produit->images->count() > 1 ? 's' : '' }}</span>
                                </span>
                            </div>
                        </td>
                        <td data-label="Catégorie">{{ $produit->categorie->nom ?? 'Sans catégorie' }}</td>
                        <td class="chiffres whitespace-nowrap text-right font-medium" data-label="Prix au mètre">{{ $produit->prix_metre ? number_format($produit->prix_metre, 0, ',', ' ') . ' F' : 'Non défini' }}</td>
                        <td data-label="Stock" data-large>
                            <a href="{{ route('admin.stock.produit', $produit) }}" title="Gérer le stock" class="pastille {{ ['ok' => 'pastille-succes', 'faible' => 'pastille-attente', 'epuise' => 'pastille-danger'][$produit->etatStock()] }} hover:underline">
                                {{ $produit->libelleStock() }}
                            </a>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                <button type="button" class="btn btn-brun btn-sm max-sm:min-h-10 max-sm:flex-1" onclick="openEditProduit({{ $produit->id }})"><x-icone nom="crayon" /> Modifier</button>
                                <button type="button" class="btn btn-contour btn-sm max-sm:min-h-10" onclick="openPhotosModal({{ $produit->id }})" title="Photos du produit"><x-icone nom="image" /><span class="sm:sr-only 2xl:not-sr-only">Photos</span></button>
                                <a href="{{ route('produit.show', $produit->slug) }}" target="_blank" rel="noopener" class="btn btn-fantome btn-sm max-sm:min-h-10" title="Voir la page du produit dans la boutique"><x-icone nom="oeil" /><span class="sr-only 2xl:not-sr-only">Voir</span></a>
                                <form method="POST" action="{{ route('admin.produits.destroy', $produit->id) }}" onsubmit="return confirm('Supprimer définitivement « {{ addslashes($produit->nom) }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger hover:bg-danger-doux max-sm:min-h-10" title="Supprimer"><x-icone nom="corbeille" /><span class="sr-only">Supprimer {{ $produit->nom }}</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-14 text-center text-encre-2">Aucun produit pour l'instant. Ajoutez le premier tissu du catalogue.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@php
    $fenetre = 'm-auto w-[min(44rem,calc(100vw-2rem))] max-h-[calc(100svh-2rem)] overflow-y-auto bg-carte p-0 text-encre shadow-[0_24px_60px_-24px_oklch(0.2_0.03_var(--teinte-ombre)/0.5)] backdrop:bg-[oklch(0.2_0.03_var(--teinte-ombre)/0.55)]';
@endphp

{{-- FENÊTRE : AJOUTER --}}
<dialog id="modal-ajouter" class="{{ $fenetre }}" aria-labelledby="titre-ajouter">
    <form method="POST" action="{{ route('admin.produits.ajouter') }}" enctype="multipart/form-data" class="p-4 sm:p-6">
        @csrf
        <div class="flex items-start justify-between gap-4">
            <h2 id="titre-ajouter" class="titre-display text-[1.3rem]">Nouveau produit</h2>
            <button type="button" onclick="this.closest('dialog').close()" class="-m-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2"><x-icone nom="fermer" /><span class="sr-only">Fermer</span></button>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div><label for="a-nom" class="etiquette">Nom du produit</label><input id="a-nom" type="text" name="nom" class="champ" required></div>
            <div>
                <label for="a-categorie" class="etiquette">Catégorie</label>
                <select id="a-categorie" name="categorie_id" class="champ" required>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->nom }}</option>@endforeach
                </select>
            </div>
        </div>
        <fieldset class="mt-4">
            <legend class="etiquette">Tarifs</legend>
            <div id="pricing-container-add" class="space-y-2"></div>
            <button type="button" class="btn btn-contour btn-sm mt-2" onclick="addPricingRow('pricing-container-add')"><x-icone nom="plus" /> Ajouter un tarif</button>
        </fieldset>
        @include('admin.produits.prix-pro', ['prefixe' => 'a-', 'classe' => 'mt-4'])
        <div class="mt-4 grid gap-3 sm:grid-cols-[10rem_1fr]">
            <div><label for="a-stock" class="etiquette">Rouleaux en stock</label><input id="a-stock" type="number" min="0" name="stock" class="champ chiffres" value="0"></div>
            <div><label for="a-image" class="etiquette">Photo principale</label><input id="a-image" type="file" name="image" accept="image/*" class="champ py-2"></div>
        </div>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-contour" onclick="this.closest('dialog').close()">Annuler</button>
            <button type="submit" class="btn btn-brun">Enregistrer le produit</button>
        </div>
    </form>
</dialog>

{{-- FENÊTRE : MODIFIER --}}
<dialog id="modal-modifier" class="{{ $fenetre }}" aria-labelledby="titre-modifier">
    <form method="POST" id="form-modifier-produit" enctype="multipart/form-data" class="p-4 sm:p-6">
        @csrf
        <div class="flex items-start justify-between gap-4">
            <h2 id="titre-modifier" class="titre-display text-[1.3rem]">Modifier le produit</h2>
            <button type="button" onclick="this.closest('dialog').close()" class="-m-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2"><x-icone nom="fermer" /><span class="sr-only">Fermer</span></button>
        </div>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            <div><label for="edit-nom" class="etiquette">Nom du produit</label><input type="text" name="nom" id="edit-nom" class="champ" required></div>
            <div>
                <label for="edit-categorie" class="etiquette">Catégorie</label>
                <select name="categorie_id" id="edit-categorie" class="champ" required>
                    @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->nom }}</option>@endforeach
                </select>
            </div>
        </div>
        <fieldset class="mt-4">
            <legend class="etiquette">Tarifs</legend>
            <div id="pricing-container-edit" class="space-y-2"></div>
            <button type="button" class="btn btn-contour btn-sm mt-2" onclick="addPricingRow('pricing-container-edit')"><x-icone nom="plus" /> Ajouter un tarif</button>
        </fieldset>
        @include('admin.produits.prix-pro', ['prefixe' => 'edit-', 'classe' => 'mt-4'])

        <div class="mt-4">
            <label for="edit-description" class="etiquette">Description</label>
            <textarea name="description" id="edit-description" rows="3" class="champ min-h-0"></textarea>
        </div>

        <div class="mt-4">
            <label for="edit-couleurs" class="etiquette">Couleurs <span class="font-normal text-encre-2">(séparées par des virgules)</span></label>
            <input type="text" name="couleurs" id="edit-couleurs" class="champ" maxlength="1000" placeholder="Bordeaux, Bleu nuit, Noir, Or">
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-[10rem_1fr]">
            <div><label for="edit-stock" class="etiquette">Rouleaux en stock</label><input type="number" min="0" name="stock" id="edit-stock" class="champ chiffres"></div>
            <div>
                <label for="edit-image" class="etiquette">Remplacer la photo principale</label>
                <div class="flex items-center gap-3">
                    <img id="edit-img-preview" src="" alt="" class="hidden size-11 shrink-0 object-cover">
                    <input id="edit-image" type="file" name="image" accept="image/*" class="champ py-2">
                </div>
            </div>
        </div>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" class="btn btn-contour" onclick="this.closest('dialog').close()">Annuler</button>
            <button type="submit" class="btn btn-brun">Enregistrer les modifications</button>
        </div>
    </form>
</dialog>

{{-- FENÊTRE : PHOTOS --}}
<dialog id="modal-photos" class="{{ $fenetre }}" aria-labelledby="photos-title">
    <div class="p-6 sm:p-8">
        <div class="flex items-start justify-between gap-4">
            <h2 id="photos-title" class="titre-display text-[1.5rem]">Photos du produit</h2>
            <button type="button" onclick="this.closest('dialog').close()" class="-m-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2"><x-icone nom="fermer" /><span class="sr-only">Fermer</span></button>
        </div>
        <p class="mt-2 text-[0.95rem] text-encre-2">Écrivez la couleur montrée sur chaque photo (ex. : Orange, Vert). Sur la page du tissu, le titre devient « Famille + couleur » selon la photo regardée, et cette couleur est choisie pour la commande.</p>
        <div id="photos-container" class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-3"></div>
        {{-- Les champs « couleur » des tuiles sont rattachés à ce formulaire (attribut form) : les tuiles contiennent aussi les formulaires de suppression --}}
        <form method="POST" id="form-couleurs-photos" class="mt-4 flex justify-end">
            @csrf
            <button type="submit" class="btn btn-brun"><x-icone nom="coche" /> Enregistrer les couleurs</button>
        </form>
        <form method="POST" id="photos-form" enctype="multipart/form-data" class="mt-6 border-t border-trait pt-6">
            @csrf
            <label for="p-images" class="etiquette">Ajouter des photos</label>
            <input id="p-images" type="file" name="images[]" accept="image/*" multiple class="champ py-2">
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" class="btn btn-contour" onclick="this.closest('dialog').close()">Fermer</button>
                <button type="submit" class="btn btn-brun"><x-icone nom="televersement" /> Envoyer les photos</button>
            </div>
        </form>
    </div>
</dialog>

<script>
const produits = @json($donneesProduits);
const jeton = '{{ csrf_token() }}';
let indexTarif = 0;

function filterProduits() {
    const terme = document.getElementById('search').value.toLowerCase().trim();
    let visibles = 0;
    document.querySelectorAll('.prod-row').forEach((ligne) => {
        const ok = ligne.dataset.nom.includes(terme);
        ligne.style.display = ok ? '' : 'none';
        if (ok) visibles++;
    });
    document.getElementById('compte-produits').textContent = terme ? `${visibles} résultat${visibles > 1 ? 's' : ''}` : '';
}

function addPricingRow(containerId, label = '', prix = '') {
    const requis = containerId === 'pricing-container-add' ? 'required' : '';
    const i = indexTarif++;
    const ligne = document.createElement('div');
    ligne.className = 'grid grid-cols-[1fr_9rem_auto] gap-2';
    ligne.innerHTML = `
        <input type="text" name="tarifications[${i}][label]" class="champ" placeholder="Au mètre" aria-label="Format" ${requis}>
        <input type="number" min="0" name="tarifications[${i}][prix]" class="champ chiffres" placeholder="Prix en F" aria-label="Prix en francs CFA" ${requis}>
        <button type="button" class="flex size-11 items-center justify-center text-encre-2 transition-colors hover:bg-danger-doux hover:text-danger" onclick="this.parentElement.remove()" aria-label="Retirer ce tarif">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>`;
    ligne.querySelector('input[type=text]').value = label ?? '';
    ligne.querySelector('input[type=number]').value = prix ?? '';
    document.getElementById(containerId).appendChild(ligne);
}

function openEditProduit(id) {
    const p = produits[id];
    if (!p) return;
    document.getElementById('edit-nom').value = p.nom ?? '';
    document.getElementById('edit-categorie').value = p.categorie_id ?? '';
    document.getElementById('edit-stock').value = p.stock ?? 0;
    document.getElementById('edit-description').value = p.description ?? '';
    document.getElementById('edit-couleurs').value = p.couleurs ?? '';

    const conteneur = document.getElementById('pricing-container-edit');
    conteneur.innerHTML = '';
    (p.tarifs.length ? p.tarifs : [{ label: 'Au mètre', prix: '' }]).forEach((t) => addPricingRow('pricing-container-edit', t.label, t.prix));

    @foreach(array_keys(\App\Models\Produit::FORMATS) as $cle)
    document.getElementById('edit-pro-{{ $cle }}').value = p.prix_pro[@js($cle)] ?? '';
    @endforeach

    const apercu = document.getElementById('edit-img-preview');
    apercu.classList.toggle('hidden', !p.image);
    apercu.src = p.image ?? '';

    document.getElementById('form-modifier-produit').action = `/admin/produits/${id}/modifier`;
    document.getElementById('modal-modifier').showModal();
}

// Texte venant de la base, placé dans du HTML : on échappe
const echapper = (texte) => String(texte ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

// Tuile d'une photo : image, champ « couleur » (rattaché à form-couleurs-photos), et suppression pour les photos supplémentaires
function tuilePhoto({ url, nomChamp, couleur, etiquette, idSuppression }) {
    const tuile = document.createElement('div');
    tuile.className = 'flex flex-col bg-papier-2';
    const champId = 'couleur-' + nomChamp.replace(/\W+/g, '-');
    tuile.innerHTML = `
        <div class="relative aspect-square">
            ${url ? `<img src="${echapper(url)}" alt="" class="h-full w-full object-cover">` : '<span class="flex h-full items-center justify-center text-encre-2">Pas de photo</span>'}
            ${etiquette ? `<span class="absolute left-1.5 top-1.5 bg-encre px-2 py-0.5 text-[0.8rem] text-papier">${etiquette}</span>` : ''}
            ${idSuppression ? `
            <form method="POST" action="/admin/images/${idSuppression}" class="absolute right-1.5 top-1.5" onsubmit="return confirm('Supprimer cette photo ?')">
                <input type="hidden" name="_token" value="${jeton}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="flex size-9 items-center justify-center bg-carte text-danger hover:bg-danger hover:text-white" aria-label="Supprimer cette photo">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </form>` : ''}
        </div>
        <label for="${champId}" class="sr-only">Couleur de cette photo</label>
        <input id="${champId}" type="text" name="${nomChamp}" form="form-couleurs-photos" maxlength="40" value="${echapper(couleur)}"
               placeholder="Couleur" class="champ min-h-11 border-x-0 border-b-0 text-[0.95rem]">`;
    return tuile;
}

function openPhotosModal(id) {
    const conteneur = document.getElementById('photos-container');
    const produit = produits[id] ?? {};
    const photos = produit.photos ?? [];
    conteneur.innerHTML = '';
    conteneur.appendChild(tuilePhoto({ url: produit.image, nomChamp: 'couleur_image', couleur: produit.couleur_image, etiquette: 'Principale' }));
    photos.forEach((photo) => {
        conteneur.appendChild(tuilePhoto({ url: photo.url, nomChamp: `couleurs_photos[${photo.id}]`, couleur: photo.couleur, idSuppression: photo.id }));
    });
    document.getElementById('form-couleurs-photos').action = `/admin/produits/${id}/couleurs-photos`;
    document.getElementById('photos-form').action = `/admin/produits/${id}/images`;
    document.getElementById('modal-photos').showModal();
}

async function generateAiDescription() {
    const productId = document.getElementById('form-modifier-produit').action.split('/').slice(-2, -1)[0];
    const keywords = document.getElementById('ai-keywords').value.trim();
    const bouton = document.querySelector('.btn-ai');
    if (!keywords) {
        document.getElementById('ai-keywords').focus();
        return;
    }
    bouton.disabled = true;
    try {
        const reponse = await fetch(`/admin/produits/${productId}/ai-description`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': jeton },
            body: JSON.stringify({ keywords }),
        });
        const data = await reponse.json();
        if (data.description) document.getElementById('edit-description').value = data.description;
    } catch (e) {
        alert('La description n\'a pas pu être générée.');
    } finally {
        bouton.disabled = false;
    }
}

// Fenêtre d'ajout : une première ligne de tarif prête à remplir
addPricingRow('pricing-container-add', 'Au mètre', '');

// Fermer une fenêtre en cliquant sur le voile
document.querySelectorAll('dialog').forEach((d) => d.addEventListener('click', (e) => { if (e.target === d) d.close(); }));

// Un clic n'importe où sur la ligne d'un produit ouvre sa fenêtre « Modifier »
// (les boutons, liens et le formulaire de suppression gardent leur action)
document.querySelectorAll('.prod-row').forEach((ligne) => ligne.addEventListener('click', (e) => {
    if (e.target.closest('a, button, input, select, textarea, label, form')) return;
    if (window.getSelection()?.toString()) return;
    openEditProduit(Number(ligne.dataset.id));
}));

// Ouverture directe d'un produit depuis le tableau de bord : /admin/produits?modifier=12
const produitAModifier = new URLSearchParams(window.location.search).get('modifier');
if (produitAModifier) openEditProduit(Number(produitAModifier));
</script>

@endsection
