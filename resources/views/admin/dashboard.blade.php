@extends('layouts.app')
@section('content')

<div class="bg-[#FDF6EE] min-h-screen py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-10 gap-4">
            <h2 class="text-3xl font-serif font-bold text-[#4A1B0C]">📊 Tableau de bord Admin</h2>
            <div class="flex gap-3">
                <a href="{{ route('admin.produits.ajouter') }}" class="bg-[#8B4513] text-white px-4 py-2 rounded-xl text-sm font-bold hover:bg-[#5d330f] transition shadow-sm">
                    + Nouveau Produit
                </a>
                <a href="{{ route('admin.categories.store') }}" class="bg-white border border-[#e8ddd3] text-[#8B4513] px-4 py-2 rounded-xl text-sm font-bold hover:bg-gray-50 transition shadow-sm">
                    + Nouvelle Catégorie
                </a>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
            <!-- CA TOTAL -->
            <div class="bg-white p-6 rounded-3xl border border-[#e8ddd3] shadow-sm group transition hover:shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-amber-100 rounded-2xl text-amber-600 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">CA Total</span>
                </div>
                <div class="text-2xl font-bold text-[#4A1B0C]">{{ number_format($ca_total, 0, ',', ' ') }} F</div>
                <div class="text-[11px] text-green-600 font-medium mt-1">Revenus confirmés</div>
            </div>

            <!-- CA JOUR -->
            <div class="bg-white p-6 rounded-3xl border border-[#e8ddd3] shadow-sm group transition hover:shadow-md">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-blue-100 rounded-2xl text-blue-600 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Aujourd'hui</span>
                </div>
                <div class="text-2xl font-bold text-[#4A1B0C]">{{ number_format($ca_jour, 0, ',', ' ') }} F</div>
                <div class="text-[11px] text-blue-600 font-medium mt-1">Ventes du jour</div>
            </div>

            <!-- PRODUITS -->
            <a href="{{ route('admin.produits') }}" class="bg-white p-6 rounded-3xl border border-[#e8ddd3] shadow-sm group transition hover:shadow-md hover:-translate-y-1 block">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-brown-100 rounded-2xl text-[#8B4513] group-hover:scale-110 transition" style="background-color: #FDF6EE;">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Catalogue</span>
                </div>
                <div class="text-2xl font-bold text-[#4A1B0C]">{{ $total_produits }}</div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">Produits actifs</div>
            </a>

            <!-- COMMANDES -->
            <a href="{{ route('admin.commandes') }}" class="bg-white p-6 rounded-3xl border border-[#e8ddd3] shadow-sm group transition hover:shadow-md hover:-translate-y-1 block">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-red-100 rounded-2xl text-red-600 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Ventes</span>
                </div>
                <div class="text-2xl font-bold text-[#4A1B0C]">{{ $total_commandes }}</div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">Commandes totales</div>
            </a>

            <!-- UTILISATEURS -->
            <a href="{{ route('admin.users') }}" class="bg-white p-6 rounded-3xl border border-[#e8ddd3] shadow-sm group transition hover:shadow-md hover:-translate-y-1 block">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-3 bg-purple-100 rounded-2xl text-purple-600 group-hover:scale-110 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Membres</span>
                </div>
                <div class="text-2xl font-bold text-[#4A1B0C]">{{ \App\Models\User::count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium mt-1">Utilisateurs inscrits</div>
            </a>
        </div>

        {{-- COMMANDES RÉCENTES --}}
        <div class="bg-white rounded-3xl border border-[#e8ddd3] shadow-sm overflow-hidden">
            <div class="p-6 border-b border-[#e8ddd3] flex justify-between items-center">
                <h3 class="text-lg font-bold text-[#4A1B0C]">Dernières commandes</h3>
                <a href="{{ route('admin.commandes') }}" class="text-xs font-bold text-[#8B4513] hover:underline">Voir tout →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($commandes_recentes as $commande)
                            @php
                                $colors = [
                                    'en_attente' => 'bg-amber-100 text-amber-700',
                                    'confirmee' => 'bg-blue-100 text-blue-700',
                                    'en_preparation' => 'bg-indigo-100 text-indigo-700',
                                    'expedie' => 'bg-gray-100 text-gray-700',
                                    'livree' => 'bg-green-100 text-green-700',
                                    'annulee' => 'bg-red-100 text-red-700',
                                ];
                                $c = $colors[$commande->statut] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-bold text-[#8B4513]">
                                    #{{ str_pad($commande->id, 4, '0', STR_PAD_LEFT) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $commande->user->name }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-[#4A1B0C]">
                                    {{ number_format($commande->total, 0, ',', ' ') }} F
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="{{ $c }} px-3 py-1 rounded-full text-[11px] font-bold uppercase">
                                        {{ str_replace('_', ' ', $commande->statut) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('admin.commande.detail', $commande->id) }}"
                                       class="text-xs font-bold text-white bg-[#4A1B0C] px-3 py-1.5 rounded-lg hover:bg-[#8B4513] transition">
                                        👁 Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">
                                    Aucune commande récente à afficher.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection