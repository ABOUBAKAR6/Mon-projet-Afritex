@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-indigo-600 mb-4">✨ Assistant Style Magique</h1>
        <p class="text-gray-600">Décrivez votre projet, et notre IA vous suggérera les tissus parfaits.</p>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-xl border-2 border-indigo-100">
        <div class="mb-6">
            <label class="block text-lg font-medium text-gray-700 mb-2">Que voulez-vous créer ?</label>
            <textarea id="prompt" rows="4" class="w-full p-4 rounded-xl border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500" placeholder="Ex: Je veux une robe élégante pour un mariage traditionnel, quelque chose de brillant et prestigieux..."></textarea>
        </div>

        <button id="suggest-btn" class="w-full bg-indigo-600 text-white py-4 px-6 rounded-xl font-bold text-lg hover:bg-indigo-700 transition-all transform hover:scale-105 flex items-center justify-center">
            <span>Obtenir des suggestions ✨</span>
        </button>

        <div id="loading" class="hidden mt-8 text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600 mx-auto"></div>
            <p class="mt-4 text-gray-500 italic">L'IA analyse vos envies...</p>
        </div>

        <div id="results" class="hidden mt-12">
            <h3 id="ai-message" class="text-xl font-semibold text-center mb-6 text-gray-800"></h3>
            <div id="product-list" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Products will be injected here -->
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('suggest-btn').addEventListener('click', async () => {
        const prompt = document.getElementById('prompt').value;
        if(!prompt) return alert('Veuillez décrire votre projet !');

        const loading = document.getElementById('loading');
        const results = document.getElementById('results');
        const productList = document.getElementById('product-list');
        const aiMessage = document.getElementById('ai-message');

        loading.classList.remove('hidden');
        results.classList.add('hidden');
        productList.innerHTML = '';

        try {
            const response = await fetch("{{ route('magic.suggest') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ prompt })
            });
            const data = await response.json();

            aiMessage.innerText = data.message;
            data.suggestions.forEach(p => {
                productList.innerHTML += `
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 text-center">
                        <img src="/storage/${p.image_path}" class="w-full h-40 object-cover rounded-md mb-4">
                        <h4 class="font-bold text-gray-800">${p.nom}</h4>
                        <p class="text-indigo-600 font-semibold">${p.prix_metre}€ / m</p>
                        <a href="/produit/${p.slug}" class="mt-3 block text-xs bg-indigo-100 text-indigo-700 py-2 rounded-md hover:bg-indigo-200 transition">Voir le produit</a>
                    </div>
                `;
            });

            results.classList.remove('hidden');
        } catch (e) {
            alert('Une erreur est survenue. Réessayez !');
        } finally {
            loading.classList.add('hidden');
        }
    });
</script>
@endsection
