@extends('layouts.app')
@section('title', 'Alertes de stock · ' . boutique('nom'))
@section('content')

<div class="conteneur max-w-4xl pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="titre-display text-[2.2rem] sm:text-[2.8rem]">Alertes de stock</h1>
            <p class="mt-1 text-encre-2">Soyez prévenu quand un tissu que vous suivez se fait rare.</p>
        </div>
        <a href="{{ route('preferences') }}" class="btn btn-fantome self-start sm:self-auto"><x-icone nom="reglages" /> Préférences</a>
    </div>

    @if($alertes->isEmpty())
        <div class="apparition mt-8 grid items-center gap-8 bg-carte p-8 sm:p-12 lg:grid-cols-[1fr_auto]" style="animation-delay: 80ms">
            <div>
                <p class="titre-display text-[1.5rem]">Aucune alerte pour l'instant</p>
                <p class="mt-2 max-w-[52ch] text-encre-2">Créez une alerte depuis un tissu pour être prévenu quand son stock baisse.</p>
            </div>
            <a href="{{ route('catalogue') }}" class="btn btn-brun btn-lg">Parcourir les tissus</a>
        </div>
    @else
        <ul class="mt-8 space-y-3">
            @foreach($alertes as $alerte)
                @php $faible = $alerte->produit->stock_disponible <= $alerte->seuil_minimum; @endphp
                <li class="revele grid gap-4 bg-carte p-5 sm:grid-cols-[1fr_auto] sm:items-center sm:p-6" style="--delai: {{ min($loop->index, 5) * 60 }}ms">
                    <div>
                        <p class="titre-display text-[1.15rem]">{{ $alerte->produit->nom }}</p>
                        <p class="mt-1 text-[0.95rem] text-encre-2">Prévenir sous <span class="chiffres font-medium text-encre">{{ $alerte->seuil_minimum }}</span> unités · créée le {{ $alerte->created_at->translatedFormat('j F Y') }}</p>
                        <span class="pastille mt-3 {{ $faible ? 'pastille-attente' : 'pastille-succes' }}">
                            {{ $faible ? 'Stock faible' : 'Stock suffisant' }}, {{ $alerte->produit->stock_disponible }} disponibles
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" onclick="basculerAlerte({{ $alerte->id }})" aria-pressed="{{ $alerte->est_active ? 'true' : 'false' }}"
                                class="btn {{ $alerte->est_active ? 'btn-brun' : 'btn-contour' }}">
                            {{ $alerte->est_active ? 'Alerte active' : 'Alerte en pause' }}
                        </button>
                        <button type="button" onclick="supprimerAlerte({{ $alerte->id }})" class="btn btn-danger">
                            <x-icone nom="corbeille" /><span class="sr-only">Supprimer l'alerte pour {{ $alerte->produit->nom }}</span>
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</div>

<script>
    const jeton = document.querySelector('meta[name="csrf-token"]')?.content;

    async function basculerAlerte(id) {
        try {
            const reponse = await fetch(`/alertes-stock/${id}/basculer`, { method: 'POST', headers: { 'X-CSRF-TOKEN': jeton } });
            reponse.ok ? location.reload() : alert('La modification a échoué. Réessayez.');
        } catch (e) {
            alert('Connexion impossible. Vérifiez votre réseau.');
        }
    }

    async function supprimerAlerte(id) {
        if (!confirm('Supprimer cette alerte de stock ?')) return;
        try {
            const reponse = await fetch(`/alertes-stock/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': jeton } });
            reponse.ok ? location.reload() : alert('La suppression a échoué. Réessayez.');
        } catch (e) {
            alert('Connexion impossible. Vérifiez votre réseau.');
        }
    }
</script>
@endsection
