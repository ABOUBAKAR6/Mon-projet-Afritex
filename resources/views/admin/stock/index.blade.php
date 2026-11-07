@extends('layouts.admin')
@section('title', 'Stock · ' . boutique('nom'))
@section('content')
@php
    $etats = [
        'ok' => ['En stock', 'pastille-succes'],
        'faible' => ['À surveiller', 'pastille-attente'],
        'epuise' => ['Épuisé', 'pastille-danger'],
    ];
@endphp

<x-admin-entete titre="Stock" description="Rouleaux et mètres restants pour chaque tissu. Les ventes payées sont retirées toutes seules.">
    <p class="flex flex-col items-end bg-brun-fonce px-5 py-3 text-white">
        <span class="text-[0.85rem] text-or-doux">Rouleaux en stock</span>
        <span class="titre-display chiffres text-[1.3rem] text-or">{{ number_format($totalRouleaux, 0, ',', ' ') }}</span>
    </p>
</x-admin-entete>

{{-- Filtres rapides --}}
<nav aria-label="Filtrer le stock" class="defilement-discret -mx-4 overflow-x-auto px-4 sm:mx-0 sm:px-0">
    <ul class="flex w-max gap-1 bg-carte p-1 sm:w-auto">
        @foreach(\App\Http\Controllers\Admin\StockController::FILTRES as $cle => $libelle)
            @php $actif = $filtre === $cle; @endphp
            <li>
                <a href="{{ route('admin.stock', $cle === 'tous' ? [] : ['filtre' => $cle]) }}" @if($actif) aria-current="page" @endif
                   class="flex min-h-10 items-center gap-2 whitespace-nowrap px-3.5 text-[0.95rem] transition-colors {{ $actif ? 'bg-brun text-white' : 'text-encre-2 hover:bg-brun-doux hover:text-brun-fonce' }}">
                    {{ $libelle }} <span class="chiffres {{ $actif ? 'text-white/80' : 'text-encre-3' }}">{{ $compteurs[$cle] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</nav>

<section class="mt-4 bg-carte" aria-label="Stock des tissus">
    <div class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
        <label for="recherche-stock" class="sr-only">Rechercher un tissu</label>
        <div class="relative w-full sm:max-w-sm">
            <x-icone nom="recherche" class="pointer-events-none absolute left-3.5 top-1/2 size-4 -translate-y-1/2 text-encre-3" />
            <input type="search" id="recherche-stock" class="champ pl-10" placeholder="Rechercher un tissu" oninput="filtrerStock(this.value)">
        </div>
        <p id="compte-stock" class="text-[0.92rem] text-encre-2" aria-live="polite"></p>
    </div>

    @if($produits->isEmpty())
        <p class="border-t border-trait px-6 py-14 text-center text-encre-2">Aucun tissu dans cette liste.</p>
    @else
        <div class="overflow-x-auto">
            <table class="tableau tableau-empile">
                <thead>
                    <tr>
                        <th scope="col">Tissu</th>
                        <th scope="col">En stock</th>
                        <th scope="col">Rouleau</th>
                        <th scope="col" class="text-right">Mètres en tout</th>
                        <th scope="col">État</th>
                        <th scope="col"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($produits as $produit)
                        @php [$etatLibelle, $etatPastille] = $etats[$produit->etatStock()]; @endphp
                        <tr class="ligne-stock" data-lien="{{ route('admin.stock.produit', $produit) }}" data-nom="{{ mb_strtolower($produit->nom . ' ' . ($produit->categorie->nom ?? '')) }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-11 shrink-0 items-center justify-center overflow-hidden bg-papier-2 text-brun-trait">
                                        @if($produit->image && file_exists(public_path($produit->image)))
                                            <img src="{{ asset($produit->image) }}" alt="" loading="lazy" class="h-full w-full object-cover">
                                        @else
                                            <x-icone nom="image" class="size-5" />
                                        @endif
                                    </span>
                                    <span class="min-w-0">
                                        <a href="{{ route('admin.stock.produit', $produit) }}" class="block truncate font-medium hover:text-brun hover:underline">{{ $produit->nom }}</a>
                                        <span class="block text-[0.85rem] text-encre-2">{{ $produit->categorie->nom ?? 'Sans catégorie' }}</span>
                                    </span>
                                </div>
                            </td>
                            <td data-label="En stock">
                                <span class="titre-display chiffres block text-[1.15rem] leading-tight">{{ $produit->stock }} <span class="text-[0.85rem] font-normal text-encre-2">rouleau{{ $produit->stock > 1 ? 'x' : '' }}</span></span>
                                @if($produit->metres_entames > 0)
                                    <span class="chiffres block text-[0.85rem] text-encre-2">+ {{ \App\Models\Produit::metres($produit->metres_entames) }} entamés</span>
                                @elseif($produit->metres_entames < 0)
                                    <span class="chiffres block text-[0.85rem] text-alerte">{{ \App\Models\Produit::metres(-$produit->metres_entames) }} vendus à décompter</span>
                                @endif
                            </td>
                            <td data-label="Rouleau">
                                @if($produit->metres_par_rouleau)
                                    <span class="chiffres">{{ \App\Models\Produit::metres($produit->metres_par_rouleau) }}</span>
                                @else
                                    <span class="text-alerte">À indiquer</span>
                                @endif
                            </td>
                            <td class="chiffres whitespace-nowrap text-right" data-label="Mètres en tout">
                                {{ $produit->metresTotal() !== null ? \App\Models\Produit::metres($produit->metresTotal()) : '—' }}
                            </td>
                            <td data-label="État">
                                <span class="pastille {{ $etatPastille }}">{{ $etatLibelle }}</span>
                                @unless($produit->disponible)
                                    <span class="mt-1 block text-[0.8rem] text-encre-2">Masqué de la boutique</span>
                                @endunless
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.stock.produit', $produit) }}#mouvement" class="btn btn-brun btn-sm max-sm:min-h-10 max-sm:flex-1"><x-icone nom="plus" /> Ajouter du stock</a>
                                    <a href="{{ route('admin.stock.produit', $produit) }}#historique" class="btn btn-contour btn-sm max-sm:min-h-10">Historique</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@push('scripts')
<script>
    // Recherche instantanée dans la liste affichée
    function filtrerStock(valeur) {
        const terme = valeur.toLowerCase().trim();
        let visibles = 0;
        document.querySelectorAll('.ligne-stock').forEach((ligne) => {
            const ok = ligne.dataset.nom.includes(terme);
            ligne.style.display = ok ? '' : 'none';
            if (ok) visibles++;
        });
        document.getElementById('compte-stock').textContent = terme ? `${visibles} résultat${visibles > 1 ? 's' : ''}` : '';
    }
</script>
@endpush
@endsection
