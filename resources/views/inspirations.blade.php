@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-center mb-8">Inspirations Communauté</h1>

    <!-- Partage Form -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-12 max-w-xl mx-auto">
        <h2 class="text-xl font-semibold mb-4">Partagez votre création !</h2>
        <form action="{{ route('inspirations.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Tissu utilisé</label>
                <select name="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach(\App\Models\Produit::all() as $produit)
                        <option value="{{ $produit->id }}">{{ $produit->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Photo de votre œuvre</label>
                <input type="file" name="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Votre commentaire</label>
                <textarea name="caption" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Racontez-nous l'histoire de cette tenue..."></textarea>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 transition">Partager</button>
        </form>
    </div>

    <!-- Gallery -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @foreach($creations as $creation)
            <div class="bg-white rounded-lg shadow-md overflow-hidden group">
                <div class="relative h-64 overflow-hidden">
                    <img src="{{ asset('storage/' . $creation->image_path) }}" alt="{{ $creation->caption }}" class="w-full h-full object-cover transition transform group-hover:scale-110">
                    <div class="absolute bottom-0 left-0 right-0 p-4 bg-gradient-to-t from-black/70 to-transparent text-white">
                        <p class="text-sm font-medium">{{ $creation->user->name }}</p>
                        <p class="text-xs opacity-80">Tissu: {{ $creation->product->nom }}</p>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-gray-600 text-sm italic">"{{ $creation->caption }}"</p>
                    <a href="{{ route('produit.show', $creation->product->slug) }}" class="mt-3 block text-center text-indigo-600 font-semibold text-sm hover:underline">
                        Acheter ce tissu &rarr;
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $creations->links() }}
    </div>
</div>
@endsection
