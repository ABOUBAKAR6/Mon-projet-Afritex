{{--
  Prix pro d'un produit, un par format de vente (Produit::FORMATS) : réservés aux revendeurs validés.
  $prefixe : préfixe des id (a-, edit-, page-) ; $valeurs : prix déjà enregistrés (fenêtre « Modifier » : remplis en JS) ;
  $classe : classes du bloc (marge haute par défaut).
--}}
@php $valeurs = $valeurs ?? []; @endphp
<fieldset class="{{ $classe ?? 'mt-6' }}">
    <legend class="etiquette">Prix pro <span class="font-normal text-encre-2">(facultatif, Espace Pro)</span></legend>
    <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 sm:gap-3 lg:grid-cols-5">
        @foreach(\App\Models\Produit::FORMATS as $cle => $format)
            <div>
                <label for="{{ $prefixe }}pro-{{ $cle }}" class="mb-1 block text-[0.85rem] text-encre-2">{{ $format['libelle'] }}</label>
                <div class="relative">
                    <input id="{{ $prefixe }}pro-{{ $cle }}" type="number" min="0" step="1" inputmode="numeric" name="prix_pro[{{ $cle }}]"
                           value="{{ old('prix_pro.' . $cle, $valeurs[$cle] ?? '') }}" class="champ chiffres pr-8" placeholder="0">
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-encre-3">F</span>
                </div>
            </div>
        @endforeach
    </div>
    @error('prix_pro.*')<p class="erreur">{{ $message }}</p>@enderror
</fieldset>
