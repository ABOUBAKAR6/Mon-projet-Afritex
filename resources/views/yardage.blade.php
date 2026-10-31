@extends('layouts.app')
@section('title', 'Calculateur de métrage · AfriTex')
@section('content')
@php
    $vetements = ['robe_longue' => 'Robe longue', 'robe_courte' => 'Robe courte', 'chemise' => 'Chemise', 'pantalon' => 'Pantalon', 'tunique' => 'Tunique'];
    $tailles = ['small' => 'Petite', 'medium' => 'Moyenne', 'large' => 'Grande'];
@endphp

<div class="conteneur max-w-3xl pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition">
        <p class="intitule">Calculateur de métrage</p>
        <h1 class="titre-display mt-2 text-[2.2rem] sm:text-[2.8rem]">Combien de mètres acheter ?</h1>
        <p class="mt-2 max-w-[56ch] text-encre-2">Choisissez le vêtement et la taille : vous obtenez le métrage à prévoir, sans gaspillage.</p>
    </div>

    <div class="apparition mt-8 bg-carte p-6 sm:p-8" style="animation-delay: 80ms" x-data="{
        type: 'robe_longue',
        size: 'medium',
        result: null,
        loading: false,
        async calculate() {
            this.loading = true;
            try {
                const res = await fetch('{{ route('yardage.calculate') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ type: this.type, size: this.size })
                });
                this.result = await res.json();
            } finally {
                this.loading = false;
            }
        }
    }">
        <fieldset>
            <legend class="etiquette">Vêtement</legend>
            <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                @foreach($vetements as $valeur => $libelle)
                    <label class="choix min-h-14 !py-3">
                        <input type="radio" name="vetement" value="{{ $valeur }}" x-model="type" class="sr-only">
                        <span class="font-medium">{{ $libelle }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <fieldset class="mt-6">
            <legend class="etiquette">Taille</legend>
            <div class="grid grid-cols-3 gap-2">
                @foreach($tailles as $valeur => $libelle)
                    <label class="choix min-h-14 justify-center !py-3">
                        <input type="radio" name="taille" value="{{ $valeur }}" x-model="size" class="sr-only">
                        <span class="font-medium">{{ $libelle }}</span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <button type="button" @click="calculate()" :disabled="loading" class="btn btn-brun btn-lg mt-8 w-full">
            <x-icone nom="regle" />
            <span x-text="loading ? 'Calcul en cours…' : 'Calculer le métrage'">Calculer le métrage</span>
        </button>

        <div x-show="result" x-cloak x-transition:enter="transition duration-300 ease-out-fort" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="mt-8 grid items-center gap-6 bg-brun-fonce p-6 text-white sm:grid-cols-[auto_1fr] sm:p-8" aria-live="polite">
            <p class="titre-display chiffres text-[3.2rem] leading-none text-or"><span x-text="result?.amount"></span> <span class="text-[1.2rem]">m</span></p>
            <p class="text-white/85" x-text="result?.message"></p>
        </div>
    </div>

    <a href="{{ route('catalogue') }}" class="lien-fleche mt-8">Choisir un tissu au mètre <x-icone nom="fleche" /></a>
</div>
@endsection
