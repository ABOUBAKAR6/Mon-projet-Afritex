@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-afri-dark mb-4">📏 Calculateur de Métrage</h1>
        <p class="text-gray-600">Ne gaspillez plus votre tissu ! Estimez la quantité nécessaire pour votre projet.</p>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-lg border-2 border-afri-gold/20" x-data="{
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
                const data = await res.json();
                this.result = data;
            } finally {
                this.loading = false;
            }
        }
    }">
        <div class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Quel type de vêtement ?</label>
                <select x-model="type" class="w-full p-3 rounded-lg border-gray-300 focus:ring-afri-gold focus:border-afri-gold">
                    <option value="robe_longue">Robe Longue</option>
                    <option value="robe_courte">Robe Courte</option>
                    <option value="chemise">Chemise</option>
                    <option value="pantalon">Pantalon</option>
                    <option value="tunique">Tunique</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-2">Quelle taille ?</label>
                <div class="flex gap-4">
                    <template x-for="s in ['small', 'medium', 'large']">
                        <button @click="size = s"
                                :class="size === s ? 'bg-afri-gold text-white' : 'bg-gray-100 text-gray-600'"
                                class="flex-1 py-2 rounded-lg font-semibold transition-all uppercase text-xs"
                                x-text="s">
                        </button>
                    </template>
                </div>
            </div>

            <button @click="calculate()" class="w-full bg-afri-dark text-white py-4 rounded-xl font-bold hover:bg-black transition-all transform active:scale-95">
                <span x-show="!loading">Calculer le métrage ✨</span>
                <span x-show="loading" class="animate-pulse">Analyse en cours...</span>
            </button>

            <div x-show="result" class="mt-8 p-6 bg-afri-beige/30 rounded-xl border-l-4 border-afri-gold text-center">
                <p class="text-gray-600 italic mb-2" x-text="result.message"></p>
                <div class="text-4xl font-bold text-afri-dark">
                    <span x-text="result.amount"></span> <span class="text-lg">mètres</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
