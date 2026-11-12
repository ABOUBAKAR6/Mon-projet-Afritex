{{--
  Formulaire « Donner votre avis » de la page produit. Au téléphone il reste replié derrière
  un bouton (variable Alpine « formulaire » du bloc parent) ; il est toujours ouvert dès la tablette.
--}}
<form id="formulaire-avis" action="{{ route('produit.review.store', $produit->id) }}" method="POST" x-show="formulaire" x-cloak
      class="mt-3 space-y-4 bg-carte p-4 sm:mt-8 sm:!block sm:space-y-5 sm:p-6 {{ $classe ?? '' }}">
    @csrf
    <p class="hidden font-medium sm:block">Donner votre avis</p>
    <div>
        <label for="note" class="etiquette">Votre note</label>
        <select id="note" name="rating" class="champ">
            <option value="5">5 sur 5, excellent</option>
            <option value="4">4 sur 5, très bien</option>
            <option value="3">3 sur 5, correct</option>
            <option value="2">2 sur 5, décevant</option>
            <option value="1">1 sur 5, mauvais</option>
        </select>
    </div>
    <div>
        <label for="commentaire" class="etiquette">Votre commentaire</label>
        <textarea id="commentaire" name="comment" rows="3" class="champ min-h-0 sm:min-h-[7rem]" placeholder="La tenue du tissu, le rendu des couleurs, la confection…"></textarea>
    </div>
    <button type="submit" class="btn btn-brun w-full">Publier mon avis</button>
</form>
