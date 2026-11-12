@extends('layouts.admin')
@section('title', 'Ajouter un produit · ' . boutique('nom'))
@section('content')
@php
    $prix = [['prix_metre', 'Au mètre'], ['prix_rouleau', 'Au rouleau']];
@endphp

<a href="{{ route('admin.produits') }}" class="lien-fleche mb-5 flex-row-reverse justify-end text-[0.95rem]"><span>Tous les produits</span><x-icone nom="fleche-gauche" /></a>

<x-admin-entete titre="Ajouter un produit" description="Enregistrez un nouveau tissu dans le catalogue." />

<form method="POST" action="{{ route('admin.produits.ajouter') }}" enctype="multipart/form-data" class="grid items-start gap-6 xl:grid-cols-[1fr_22rem]">
    @csrf
    <div class="space-y-6">
        <section class="bg-carte p-6 sm:p-8" aria-labelledby="titre-infos">
            <h2 id="titre-infos" class="titre-display text-[1.25rem]">Le tissu</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="nom" class="etiquette">Nom du produit</label>
                    <input id="nom" type="text" name="nom" value="{{ old('nom') }}" class="champ" required placeholder="Wax hollandais premium">
                    @error('nom')<p class="erreur">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="categorie" class="etiquette">Catégorie</label>
                    <select id="categorie" name="categorie_id" class="champ" required>
                        <option value="">Choisir une catégorie</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('categorie_id') == $cat->id)>{{ $cat->nom }}</option>
                        @endforeach
                    </select>
                    @error('categorie_id')<p class="erreur">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="mt-5">
                <label for="description" class="etiquette">Description</label>
                <textarea id="description" name="description" rows="5" class="champ" placeholder="La matière, le tombé, le motif, les usages conseillés…">{{ old('description') }}</textarea>
            </div>
            <div class="mt-5">
                <label for="couleurs" class="etiquette">Couleurs disponibles <span class="font-normal text-encre-2">(facultatif)</span></label>
                <input id="couleurs" type="text" name="couleurs" value="{{ old('couleurs') }}" class="champ" maxlength="1000" placeholder="Bordeaux, Bleu nuit, Noir, Or" aria-describedby="aide-couleurs">
                <p id="aide-couleurs" class="aide">Séparées par des virgules. Le client les choisit d'un geste sur la page du tissu.</p>
                @error('couleurs')<p class="erreur">{{ $message }}</p>@enderror
            </div>
        </section>

        <section class="bg-carte p-6 sm:p-8" aria-labelledby="titre-tarifs">
            <h2 id="titre-tarifs" class="titre-display text-[1.25rem]">Tarifs</h2>
            <p class="mt-1 text-encre-2">Laissez vide un format qui n'est pas proposé pour ce tissu.</p>
            <div class="mt-5 grid gap-4 sm:grid-cols-3 lg:grid-cols-5">
                @foreach($prix as [$champ, $libelle])
                    <div>
                        <label for="{{ $champ }}" class="etiquette">{{ $libelle }}</label>
                        <div class="relative">
                            <input id="{{ $champ }}" type="number" min="0" name="{{ $champ }}" value="{{ old($champ) }}" class="champ chiffres pr-8" placeholder="0">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-encre-3">F</span>
                        </div>
                    </div>
                @endforeach
            </div>
            @include('admin.produits.prix-pro', ['prefixe' => 'page-', 'classe' => 'mt-8 border-t border-trait pt-6'])
        </section>
    </div>

    <aside class="space-y-6 xl:sticky xl:top-10">
        <section class="bg-carte p-6" aria-labelledby="titre-stock">
            <h2 id="titre-stock" class="titre-display text-[1.25rem]">Stock et photo</h2>
            <div class="mt-5">
                <label for="stock" class="etiquette">Rouleaux en stock au départ</label>
                <input id="stock" type="number" min="0" name="stock" value="{{ old('stock', 0) }}" class="champ chiffres">
            </div>
            <div class="mt-5" x-data="{ apercu: null }">
                <label for="image" class="etiquette">Photo principale</label>
                <div class="relative mb-3 flex aspect-[4/5] items-center justify-center overflow-hidden bg-papier-2 text-brun-trait">
                    <img x-show="apercu" x-cloak :src="apercu" alt="Aperçu de la photo" class="absolute inset-0 h-full w-full object-cover">
                    <x-icone nom="image" class="size-10" x-show="!apercu" />
                </div>
                <input id="image" type="file" name="image" accept="image/*" class="champ py-2" @change="apercu = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" aria-describedby="aide-image">
                <p id="aide-image" class="aide">Jusqu'à 20 Mo : la photo est compressée automatiquement.</p>
                @error('image')<p class="erreur">{{ $message }}</p>@enderror
            </div>
        </section>
        <button type="submit" class="btn btn-brun btn-lg w-full">Enregistrer le produit</button>
    </aside>
</form>

@endsection
