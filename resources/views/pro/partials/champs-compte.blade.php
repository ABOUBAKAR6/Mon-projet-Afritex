{{--
  Champs de l'activité du revendeur, communs à la demande de compte et à « Mon compte pro ».
  $compte (facultatif) pré-remplit ; old() reprend la saisie après une erreur.
--}}
@php
    $compte = $compte ?? null;
    $famillesChoisies = array_map('intval', (array) old('familles', $compte?->familles ?? []));
@endphp
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="boutique" class="etiquette">Nom de l'atelier ou de la boutique</label>
        <input id="boutique" type="text" name="boutique" value="{{ old('boutique', $compte?->boutique) }}" class="champ" required maxlength="120" autocomplete="organization" @error('boutique') aria-invalid="true" aria-describedby="erreur-boutique" @enderror>
        @error('boutique')<p id="erreur-boutique" class="erreur">{{ $message }}</p>@enderror
    </div>

    <fieldset class="sm:col-span-2">
        <legend class="etiquette">Votre activité</legend>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach(\App\Models\ComptePro::ACTIVITES as $valeur => $libelle)
                <label class="choix min-h-14 py-3">
                    <input type="radio" name="activite" value="{{ $valeur }}" class="size-4 accent-[var(--brun)]" required @checked(old('activite', $compte?->activite) === $valeur)>
                    <span class="text-[0.95rem]">{{ $libelle }}</span>
                </label>
            @endforeach
        </div>
        @error('activite')<p class="erreur">{{ $message }}</p>@enderror
    </fieldset>

    <div>
        <label for="ville" class="etiquette">Ville</label>
        <input id="ville" type="text" name="ville" value="{{ old('ville', $compte?->ville) }}" class="champ" required maxlength="80" autocomplete="address-level2" @error('ville') aria-invalid="true" aria-describedby="erreur-ville" @enderror>
        @error('ville')<p id="erreur-ville" class="erreur">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="commune" class="etiquette">Commune ou quartier <span class="font-normal text-encre-2">(facultatif)</span></label>
        <input id="commune" type="text" name="commune" value="{{ old('commune', $compte?->commune) }}" class="champ" maxlength="80" autocomplete="address-level3">
        @error('commune')<p class="erreur">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="whatsapp" class="etiquette">Numéro WhatsApp</label>
        <input id="whatsapp" type="tel" name="whatsapp" value="{{ old('whatsapp', $compte?->whatsapp) }}" class="champ chiffres" required inputmode="tel" autocomplete="tel" aria-describedby="aide-whatsapp{{ $errors->has('whatsapp') ? ' erreur-whatsapp' : '' }}" @error('whatsapp') aria-invalid="true" @enderror>
        <p id="aide-whatsapp" class="aide">La boutique vous y contacte pour les devis et les livraisons.</p>
        @error('whatsapp')<p id="erreur-whatsapp" class="erreur">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="volume_mensuel" class="etiquette">Achats de tissu par mois <span class="font-normal text-encre-2">(facultatif)</span></label>
        <select id="volume_mensuel" name="volume_mensuel" class="champ">
            <option value="">Je ne sais pas encore</option>
            @foreach(\App\Models\ComptePro::VOLUMES as $valeur => $libelle)
                <option value="{{ $valeur }}" @selected(old('volume_mensuel', $compte?->volume_mensuel) === $valeur)>{{ $libelle }}</option>
            @endforeach
        </select>
        @error('volume_mensuel')<p class="erreur">{{ $message }}</p>@enderror
    </div>

    @if($categories->isNotEmpty())
        <fieldset class="sm:col-span-2">
            <legend class="etiquette">Les tissus que vous achetez le plus <span class="font-normal text-encre-2">(facultatif)</span></legend>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $categorie)
                    <label class="choix min-h-11 gap-2.5 px-3.5 py-2">
                        <input type="checkbox" name="familles[]" value="{{ $categorie->id }}" class="size-4 accent-[var(--brun)]" @checked(in_array($categorie->id, $famillesChoisies, true))>
                        <span class="text-[0.95rem]">{{ $categorie->nom }}</span>
                    </label>
                @endforeach
            </div>
            @error('familles')<p class="erreur">{{ $message }}</p>@enderror
            @error('familles.*')<p class="erreur">{{ $message }}</p>@enderror
        </fieldset>
    @endif

    <div class="sm:col-span-2">
        <label for="rccm" class="etiquette">Numéro RCCM <span class="font-normal text-encre-2">(facultatif)</span></label>
        <input id="rccm" type="text" name="rccm" value="{{ old('rccm', $compte?->rccm) }}" class="champ sm:max-w-sm" maxlength="60" aria-describedby="aide-rccm">
        <p id="aide-rccm" class="aide">Si votre activité est immatriculée. Sans RCCM, votre demande reste possible.</p>
        @error('rccm')<p class="erreur">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="presentation" class="etiquette">Quelques mots sur votre activité <span class="font-normal text-encre-2">(facultatif)</span></label>
        <textarea id="presentation" name="presentation" rows="4" maxlength="1000" class="champ" placeholder="Ce que vous confectionnez ou vendez, depuis quand, votre clientèle…">{{ old('presentation', $compte?->presentation) }}</textarea>
        @error('presentation')<p class="erreur">{{ $message }}</p>@enderror
    </div>
</div>
