@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold">📍 Mes Alertes de Stock</h1>
        <a href="{{ route('preferences') }}" class="text-afri-gold hover:text-afri-brown transition">← Paramètres</a>
    </div>

    @if($alertes->isEmpty())
        <div class="bg-gray-100 p-8 rounded-lg text-center">
            <p class="text-gray-600 mb-4">Vous n'avez pas encore d'alertes de stock.</p>
            <p class="text-sm text-gray-500 mb-6">
                Créez une alerte pour être notifié quand un produit a un stock faible.
            </p>
            <a href="{{ route('catalogue') }}" class="inline-block px-6 py-3 bg-afri-gold text-white rounded-lg font-semibold hover:bg-afri-brown transition">
                Parcourir les produits
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($alertes as $alerte)
                <div class="bg-white p-6 rounded-lg shadow flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold">
                            {{ $alerte->produit->nom }}
                        </h3>
                        <p class="text-gray-600 text-sm">
                            Seuil minimum : <strong>{{ $alerte->seuil_minimum }} unités</strong>
                            @if($alerte->produit->stock_disponible <= $alerte->seuil_minimum)
                                <span class="ml-2 inline-block px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded">
                                    ⚠️ Stock faible ({{ $alerte->produit->stock_disponible }} en stock)
                                </span>
                            @else
                                <span class="ml-2 inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded">
                                    ✅ Stock OK ({{ $alerte->produit->stock_disponible }} en stock)
                                </span>
                            @endif
                        </p>
                        <p class="text-gray-500 text-xs mt-2">
                            Créée le {{ $alerte->created_at->format('d M Y à H:i') }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="basculerAlerte({{ $alerte->id }})" 
                                class="px-4 py-2 {{ $alerte->est_active ? 'bg-green-600 hover:bg-green-700' : 'bg-gray-400 hover:bg-gray-500' }} text-white rounded-lg font-semibold transition">
                            {{ $alerte->est_active ? '✓ Activée' : '✗ Désactivée' }}
                        </button>
                        <button onclick="supprimerAlerte({{ $alerte->id }})" 
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold transition">
                            Supprimer
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
    // Basculer une alerte
    async function basculerAlerte(alerteId) {
        try {
            const response = await fetch(`/alertes-stock/${alerteId}/basculer`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                }
            });

            if (response.ok) {
                location.reload();
            } else {
                alert('❌ Erreur lors de la modification');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('❌ Erreur réseau');
        }
    }

    // Supprimer une alerte
    async function supprimerAlerte(alerteId) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette alerte ?')) {
            try {
                const response = await fetch(`/alertes-stock/${alerteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    }
                });

                if (response.ok) {
                    location.reload();
                } else {
                    alert('❌ Erreur lors de la suppression');
                }
            } catch (error) {
                console.error('Erreur:', error);
                alert('❌ Erreur réseau');
            }
        }
    }
</script>
@endsection
