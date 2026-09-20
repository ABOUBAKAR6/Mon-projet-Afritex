@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 md:py-16">
    <a href="{{ route('commandes.index') }}" class="text-sm font-semibold text-afri-brown hover:underline">← Retour à mes commandes</a>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-afri-dark">Commande #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</h1>
                <p class="text-sm text-gray-500 mt-1">Statut actuel : <span class="font-semibold text-afri-brown">{{ ucfirst($commande->statut) }}</span></p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Montant total</p>
                <p class="text-xl font-bold text-afri-dark">{{ number_format($commande->total, 0, ',', ' ') }} F</p>
            </div>
        </div>

        @if($commande->coupon_code)
            <div class="mt-6 rounded-xl bg-afri-beige p-4 text-sm text-afri-brown">
                Code promo appliqué : <span class="font-semibold">{{ $commande->coupon_code }}</span>
                @if($commande->discount_amount)
                    <span class="ml-2">(-{{ number_format($commande->discount_amount, 0, ',', ' ') }} F)</span>
                @endif
            </div>
        @endif

        <div class="mt-8">
            <h2 class="text-lg font-semibold text-afri-dark mb-4">Produits commandés</h2>
            <div class="space-y-3">
                @foreach($commande->items as $item)
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between rounded-xl border border-gray-200 p-4">
                        <div>
                            <p class="font-semibold text-afri-dark">{{ $item->produit?->nom ?? 'Produit' }}</p>
                            <p class="text-sm text-gray-500">{{ ucfirst($item->type_commande) }} × {{ $item->quantite }}</p>
                        </div>
                        <div class="text-sm font-semibold text-afri-brown">{{ number_format($item->sous_total, 0, ',', ' ') }} F</div>
                    </div>
                @endforeach
            </div>
        </div>

        @if($commande->adresse_livraison)
            <div class="mt-8 rounded-xl bg-gray-50 p-4">
                <h2 class="text-lg font-semibold text-afri-dark mb-2">Adresse de livraison</h2>
                <p class="text-sm text-gray-600">{{ $commande->adresse_livraison }}</p>
            </div>
        @endif
    </div>
</div>
@endsection
