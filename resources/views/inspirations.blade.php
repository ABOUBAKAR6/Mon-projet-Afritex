@extends('layouts.app')
@section('title', 'Inspirations · ' . boutique('nom'))
@section('content')

<x-en-tete-page titre="Inspirations" chapeau="Robes, boubous, chemises : les créations cousues par nos clients dans les tissus {{ boutique('nom') }}." />

<div class="conteneur pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="grid items-start gap-10 lg:grid-cols-[22rem_1fr] lg:gap-14">
        {{-- PARTAGER --}}
        <section class="bg-carte p-6 sm:p-8 lg:sticky lg:top-28" aria-labelledby="titre-partage">
            <h2 id="titre-partage" class="titre-display text-[1.3rem]">Partagez votre création</h2>
            <p class="mt-1 text-[0.95rem] text-encre-2">Une photo, le tissu utilisé, quelques mots.</p>
            <form action="{{ route('inspirations.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label for="tissu" class="etiquette">Tissu utilisé</label>
                    <select id="tissu" name="product_id" class="champ">
                        @foreach(\App\Models\Produit::orderBy('nom')->get(['id', 'nom']) as $produit)
                            <option value="{{ $produit->id }}">{{ $produit->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="photo" class="etiquette">Photo de la tenue</label>
                    <input id="photo" type="file" name="image" accept="image/*" required
                           class="block w-full text-[0.92rem] text-encre-2 file:mr-3 file:min-h-11 file:border-0 file:bg-brun-doux file:px-4 file:font-semibold file:text-brun-fonce hover:file:bg-brun-trait">
                </div>
                <div>
                    <label for="legende" class="etiquette">Votre histoire</label>
                    <textarea id="legende" name="caption" rows="3" class="champ" placeholder="Pour quelle occasion, quel modèle…"></textarea>
                </div>
                <button type="submit" class="btn btn-brun w-full"><x-icone nom="televersement" /> Partager</button>
            </form>
        </section>

        {{-- GALERIE --}}
        <section aria-labelledby="titre-galerie">
            <h2 id="titre-galerie" class="sr-only">Créations partagées</h2>
            @if($creations->isEmpty())
                <div class="flex min-h-64 flex-col justify-center bg-carte p-8">
                    <p class="titre-display text-[1.4rem]">Soyez le premier à partager</p>
                    <p class="mt-2 max-w-[48ch] text-encre-2">Montrez ce que vous avez cousu : votre création inspirera les prochains clients.</p>
                </div>
            @else
                <ul class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @foreach($creations as $creation)
                        <li class="revele group filet relative bg-carte" style="--delai: {{ ($loop->index % 3) * 60 }}ms">
                            <div class="aspect-[4/5] overflow-hidden bg-papier-2">
                                <img src="{{ asset('storage/' . $creation->image_path) }}" alt="{{ $creation->caption ?: 'Création de ' . $creation->user->name }}" loading="lazy" class="zoom-doux h-full w-full object-cover">
                            </div>
                            <div class="p-5">
                                <p class="font-medium">{{ $creation->user->name }}</p>
                                @if($creation->caption)
                                    <p class="mt-1 text-[0.95rem] text-encre-2">{{ $creation->caption }}</p>
                                @endif
                                @if($creation->product)
                                    <a href="{{ route('produit.show', $creation->product->slug) }}" class="lien-fleche mt-3 text-[0.92rem]">{{ $creation->product->nom }} <x-icone nom="fleche" /></a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-10">{{ $creations->links() }}</div>
            @endif
        </section>
    </div>
</div>

@endsection
