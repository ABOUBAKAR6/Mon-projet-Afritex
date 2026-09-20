@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 md:py-16">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-afri-dark">Mes commandes</h1>
            <p class="text-sm text-gray-500">Retrouvez l’historique de vos achats et leur évolution.</p>
        </div>
        <a href="{{ route('panier') }}" class="inline-flex items-center justify-center rounded-full bg-afri-brown text-white px-5 py-3 text-sm font-semibold">Retour au panier</a>
    </div>

    @if($commandes->isEmpty())
        <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center">
            <p class="text-lg font-semibold text-afri-dark">Aucune commande pour le moment.</p>
            <p class="text-sm text-gray-500 mt-2">Passez votre première commande pour voir l’historique ici.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($commandes as $commande)
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-afri-dark">Commande #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}</p>
                            <p class="text-sm text-gray-500">Passée le {{ $commande->created_at->translatedFormat('d M Y à H:i') }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-afri-beige px-3 py-1 text-xs font-semibold text-afri-brown">{{ ucfirst($commande->statut) }}</span>
                            <span class="text-sm font-semibold text-afri-dark">{{ number_format($commande->total, 0, ',', ' ') }} F</span>
                        </div>
                    </div>

                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <div class="flex flex-wrap gap-2 text-sm text-gray-600">
                            @foreach($commande->items->take(3) as $item)
                                <span class="rounded-full bg-gray-100 px-3 py-1">{{ $item->produit?->nom ?? 'Produit' }}</span>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <a href="{{ route('commandes.show', $commande) }}" class="text-sm font-semibold text-afri-brown hover:underline">Voir le détail</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8">
            {{ $commandes->links() }}
        </div>
    @endif
</div>
@endsection
