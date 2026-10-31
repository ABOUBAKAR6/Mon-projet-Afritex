@extends('layouts.app')
@section('title', 'Notre histoire · AfriTex')
@section('content')
@php
    $photos = \App\Models\Produit::whereNotNull('image')->inRandomOrder()->take(3)->get(['nom', 'image'])
        ->filter(fn ($p) => file_exists(public_path($p->image)))->values();
@endphp

<x-en-tete-page titre="Notre histoire" chapeau="AfriTex est née d'une envie simple : rendre les plus beaux tissus africains accessibles à chaque couturier, au mètre comme au rouleau." />

<div class="conteneur pb-20 pt-16 lg:pb-28 lg:pt-24">
    <section class="grid items-center gap-14 lg:grid-cols-2 lg:gap-20" aria-labelledby="titre-matiere">
        <div class="revele relative mb-12 mr-10 sm:mr-16">
            <img src="{{ asset($photos->get(0)?->image ?? 'images/hero-bg.avif') }}" alt="{{ $photos->get(0)?->nom ?? '' }}" class="aspect-[4/5] w-full max-w-md object-cover">
            @if($photos->get(1))
                <img src="{{ asset($photos->get(1)->image) }}" alt="{{ $photos->get(1)->nom }}" loading="lazy"
                     class="absolute -bottom-12 -right-10 aspect-square w-[52%] max-w-64 border-[6px] border-papier object-cover sm:-right-16">
            @endif
        </div>
        <div class="revele" style="--delai: 120ms">
            <x-titre-bloc intitule="La matière" titre="Une passion pour le tissu" id="titre-matiere" />
            <div class="mt-6 space-y-4 text-[1.05rem] lg:text-[1.12rem]">
                <p>AfriTex est né d'une volonté simple mais ambitieuse : sublimer la richesse des textiles africains en les proposant sous un angle contemporain.</p>
                <p class="text-encre-2">Nous sélectionnons des étoffes dont la qualité, la finesse et le motif racontent une histoire. Chaque fil, chaque trame est choisi pour sa capacité à faire d'un vêtement une pièce dont on se souvient.</p>
            </div>
        </div>
    </section>

    <section class="mt-24 grid items-center gap-14 lg:mt-32 lg:grid-cols-2 lg:gap-20" aria-labelledby="titre-detail">
        <div class="revele order-2 lg:order-1">
            <x-titre-bloc intitule="Le détail" titre="Aucun compromis sur la qualité" id="titre-detail" />
            <div class="mt-6 space-y-4 text-[1.05rem] lg:text-[1.12rem]">
                <p>Pour nous, le luxe ne réside pas dans l'ostentatoire, mais dans la précision. Du coton glacé à la soie japonaise, chaque tissu est choisi avec soin.</p>
                <p class="text-encre-2">Notre mission : accompagner les créateurs, les couturiers et les passionnés dans la réalisation de pièces d'exception, avec un accès simple aux plus beaux tissus du moment.</p>
            </div>
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg mt-8">Découvrir les tissus <x-icone nom="fleche" /></a>
        </div>
        <div class="revele order-1 lg:order-2" style="--delai: 120ms">
            <img src="{{ asset($photos->get(2)?->image ?? 'images/hero-bg.avif') }}" alt="{{ $photos->get(2)?->nom ?? '' }}" loading="lazy" class="aspect-[5/4] w-full object-cover">
        </div>
    </section>
</div>

<section class="bg-brun-fonce py-20 text-white lg:py-28">
    <figure class="conteneur revele max-w-4xl text-center">
        <blockquote class="titre-display text-[1.8rem] leading-snug sm:text-[2.3rem]">« Le textile est le langage silencieux de l'élégance. »</blockquote>
        <figcaption class="mt-6 text-or-doux">La maison AfriTex</figcaption>
    </figure>
</section>

@endsection
