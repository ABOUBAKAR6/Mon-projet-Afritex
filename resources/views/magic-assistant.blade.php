@extends('layouts.app')
@section('title', 'Conseil tissu · AfriTex')
@section('content')

<div class="conteneur max-w-3xl pb-20 pt-10 lg:pb-28 lg:pt-14">
    <div class="apparition">
        <p class="intitule">Conseil tissu</p>
        <h1 class="titre-display mt-2 text-[2.2rem] sm:text-[2.8rem]">Quel tissu pour votre projet ?</h1>
        <p class="mt-2 max-w-[56ch] text-encre-2">Décrivez la tenue que vous voulez coudre : l'assistant vous propose les tissus du catalogue qui conviennent.</p>
    </div>

    <div class="apparition mt-8 bg-carte p-6 sm:p-8" style="animation-delay: 80ms">
        <label for="prompt" class="etiquette">Votre projet</label>
        <textarea id="prompt" rows="4" class="champ" placeholder="Une robe élégante pour un mariage traditionnel, quelque chose de brillant et prestigieux…"></textarea>
        <p id="erreur-prompt" class="erreur hidden">Décrivez votre projet en quelques mots.</p>

        <button id="suggest-btn" type="button" class="btn btn-brun btn-lg mt-5 w-full"><x-icone nom="etincelle" /> Voir les tissus conseillés</button>

        <p id="loading" class="mt-6 hidden items-center justify-center gap-3 text-encre-2" role="status">
            <span class="size-5 animate-spin rounded-full border-2 border-brun-trait border-t-brun" aria-hidden="true"></span>
            Recherche des tissus adaptés…
        </p>

        <div id="results" class="mt-10 hidden" aria-live="polite">
            <p id="ai-message" class="titre-display text-[1.2rem]"></p>
            <ul id="product-list" class="mt-5 grid gap-3 sm:grid-cols-3"></ul>
        </div>
    </div>
</div>

<script>
    document.getElementById('suggest-btn').addEventListener('click', async () => {
        const prompt = document.getElementById('prompt').value.trim();
        const erreur = document.getElementById('erreur-prompt');
        erreur.classList.toggle('hidden', !!prompt);
        if (!prompt) return;

        const loading = document.getElementById('loading');
        const results = document.getElementById('results');
        const liste = document.getElementById('product-list');

        loading.classList.replace('hidden', 'flex');
        results.classList.add('hidden');
        liste.innerHTML = '';

        try {
            const reponse = await fetch("{{ route('magic.suggest') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ prompt }),
            });
            const data = await reponse.json();
            document.getElementById('ai-message').innerText = data.message;
            data.suggestions.forEach((p) => {
                const li = document.createElement('li');
                li.className = 'group filet relative bg-papier-2';
                li.innerHTML = `
                    <div class="aspect-square overflow-hidden"><img src="/storage/${p.image_path}" alt="" class="zoom-doux h-full w-full object-cover"></div>
                    <div class="p-4">
                        <a href="/produit/${p.slug}" class="font-medium after:absolute after:inset-0 after:content-['']"></a>
                        <p class="chiffres mt-1 text-[0.92rem] text-encre-2"></p>
                    </div>`;
                li.querySelector('a').textContent = p.nom;
                li.querySelector('p').textContent = new Intl.NumberFormat('fr-FR').format(p.prix_metre).replace(/\s/g, ' ') + ' F le mètre';
                liste.appendChild(li);
            });
            results.classList.remove('hidden');
        } catch (e) {
            alert('Le conseil n\'a pas pu être chargé. Réessayez dans un instant.');
        } finally {
            loading.classList.replace('flex', 'hidden');
        }
    });
</script>
@endsection
