@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto bg-white shadow-xl rounded-2xl overflow-hidden">
        <div class="bg-indigo-600 px-6 py-8 text-white text-center">
            <h1 class="text-3xl font-bold">Suivi de votre Commande</h1>
            <p class="mt-2 opacity-90">Numéro de suivi : {{ $commande->tracking_number }}</p>
        </div>

        <div class="p-8">
            <div class="flex justify-between items-center mb-8 pb-6 border-b">
                <div>
                    <p class="text-sm text-gray-500 uppercase font-semibold">Commande #</p>
                    <p class="text-xl font-bold">{{ $commande->id }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 uppercase font-semibold">Statut Actuel</p>
                    <span class="px-3 py-1 rounded-full text-sm font-bold {{ $commande->statut === 'livree' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ ucfirst($commande->statut) }}
                    </span>
                </div>
            </div>

            <div class="relative">
                <!-- Progress Bar -->
                <div class="absolute top-1/2 left-0 w-full h-1 bg-gray-200 -translate-y-1/2"></div>
                <div class="absolute top-1/2 left-0 h-1 bg-indigo-600 -translate-y-1/2 transition-all duration-500"
                     style="width: {{ $commande->statut === 'livree' ? '100%' : ($commande->statut === 'confirmee' ? '50%' : '20%') }}"></div>

                <div class="relative flex justify-between">
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center mx-auto mb-2 font-bold">1</div>
                        <p class="text-xs font-medium">Confirmée</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full {{ $commande->statut === 'livree' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center mx-auto mb-2 font-bold">2</div>
                        <p class="text-xs font-medium">En cours</p>
                    </div>
                    <div class="text-center">
                        <div class="w-8 h-8 rounded-full {{ $commande->statut === 'livree' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }} flex items-center justify-center mx-auto mb-2 font-bold">3</div>
                        <p class="text-xs font-medium">Livrée</p>
                    </div>
                </div>
            </div>

            <div class="mt-12 p-6 bg-gray-50 rounded-xl">
                <h3 class="text-lg font-bold mb-4">Détails de Livraison</h3>
                <p class="text-gray-600"><strong>Adresse :</strong> {{ $commande->adresse_livraison }}</p>
                <p class="text-gray-600 mt-2"><strong>Date de commande :</strong> {{ $commande->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('accueil') }}" class="text-indigo-600 hover:underline font-medium">← Retour à la boutique</a>
            </div>
        </div>
    </div>
</div>
@endsection