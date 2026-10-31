@extends('layouts.admin')
@section('title', 'Catégories · AfriTex')
@section('content')
@php
    $totalProduits = $categories->sum(fn ($c) => $c->produits->count());
    $fenetre = 'm-auto w-[min(32rem,calc(100vw-2rem))] bg-carte p-0 text-encre shadow-[0_24px_60px_-24px_oklch(0.2_0.03_40/0.5)] backdrop:bg-[oklch(0.2_0.03_40/0.55)]';
@endphp

<x-admin-entete titre="Catégories" :description="count($categories) . ' familles de tissus, ' . $totalProduits . ' produits au total'">
    <button type="button" onclick="document.getElementById('modal-ajouter').showModal()" class="btn btn-brun"><x-icone nom="plus" /> Ajouter une catégorie</button>
</x-admin-entete>

<section class="bg-carte" aria-label="Liste des catégories">
    <div class="p-4">
        <label for="search" class="sr-only">Rechercher une catégorie</label>
        <div class="relative sm:max-w-sm">
            <x-icone nom="recherche" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-encre-3" />
            <input type="search" id="search" class="champ pl-10" placeholder="Rechercher une catégorie" oninput="filterCategories()">
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="tableau" id="categoriesTable">
            <thead>
                <tr>
                    <th scope="col">Catégorie</th>
                    <th scope="col" class="w-[40%]">Produits</th>
                    <th scope="col"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                @php $maxProduits = max(1, $categories->max(fn ($c) => $c->produits->count())); @endphp
                @foreach($categories as $categorie)
                    @php $nb = $categorie->produits->count(); @endphp
                    <tr class="cat-row" data-nom="{{ mb_strtolower($categorie->nom) }}">
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden bg-papier-2">
                                    @if($categorie->image)
                                        <img src="{{ asset($categorie->image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <span class="titre-display text-brun-trait">{{ mb_substr($categorie->nom, 0, 1) }}</span>
                                    @endif
                                </span>
                                <span class="font-medium">{{ $categorie->nom }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="chiffres w-8 shrink-0 text-right">{{ $nb }}</span>
                                <span class="h-2 flex-1 bg-papier-2" aria-hidden="true"><span class="block h-full rounded-r-[4px] bg-brun" style="width: {{ round(($nb / $maxProduits) * 100) }}%"></span></span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" class="btn btn-fantome btn-sm" onclick="openEditModal({{ $categorie->id }}, @js($categorie->nom))"><x-icone nom="crayon" /> Modifier</button>
                                <form method="POST" action="{{ route('admin.categories.destroy', $categorie->id) }}" onsubmit="return confirm(@js('Supprimer la catégorie « ' . $categorie->nom . ' » ?'))">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm text-danger hover:bg-danger-doux"><x-icone nom="corbeille" /><span class="sr-only">Supprimer {{ $categorie->nom }}</span></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

@foreach([['modal-ajouter', 'Nouvelle catégorie', route('admin.categories.store'), 'Ajouter la catégorie', false], ['modal-modifier', 'Modifier la catégorie', '', 'Enregistrer', true]] as [$id, $titre, $action, $bouton, $modifier])
    <dialog id="{{ $id }}" class="{{ $fenetre }}" aria-labelledby="titre-{{ $id }}">
        <form method="POST" action="{{ $action }}" @if($modifier) id="form-modifier" @endif enctype="multipart/form-data" class="p-6 sm:p-8">
            @csrf
            @if($modifier) @method('PUT') @endif
            <div class="flex items-start justify-between gap-4">
                <h2 id="titre-{{ $id }}" class="titre-display text-[1.5rem]">{{ $titre }}</h2>
                <button type="button" onclick="this.closest('dialog').close()" class="-m-2 flex size-11 items-center justify-center text-encre-2 hover:bg-papier-2"><x-icone nom="fermer" /><span class="sr-only">Fermer</span></button>
            </div>
            <div class="mt-6">
                <label for="{{ $id }}-nom" class="etiquette">Nom de la catégorie</label>
                <input type="text" name="nom" id="{{ $modifier ? 'edit-nom' : $id . '-nom' }}" required class="champ" placeholder="Grain de diamant">
            </div>
            <div class="mt-5">
                <label for="{{ $id }}-image" class="etiquette">Image <span class="font-normal text-encre-2">(facultative)</span></label>
                <input type="file" name="image" id="{{ $id }}-image" accept="image/*" class="champ py-2">
            </div>
            <div class="mt-8 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" class="btn btn-contour" onclick="this.closest('dialog').close()">Annuler</button>
                <button type="submit" class="btn btn-brun">{{ $bouton }}</button>
            </div>
        </form>
    </dialog>
@endforeach

<script>
function openEditModal(id, nom) {
    document.getElementById('edit-nom').value = nom;
    document.getElementById('form-modifier').action = '/admin/categories/' + id;
    document.getElementById('modal-modifier').showModal();
}

function filterCategories() {
    const terme = document.getElementById('search').value.toLowerCase().trim();
    document.querySelectorAll('.cat-row').forEach((ligne) => {
        ligne.style.display = ligne.dataset.nom.includes(terme) ? '' : 'none';
    });
}

document.querySelectorAll('dialog').forEach((d) => d.addEventListener('click', (e) => { if (e.target === d) d.close(); }));
</script>

@endsection
