{{-- Version texte : sortie brute ({!! !!}), sinon Blade transformerait > ou une apostrophe en entités HTML --}}
COMMANDE {!! $numero !!}
Votre commande est bien reçue

Merci {!! $commande->user->name !!}. Nous préparons vos tissus et vous écrirons à chaque étape.
{!! $boutique ? 'Votre commande sera prête au retrait en boutique dès le lendemain.' : 'Elle vous sera livrée sous 24 à 72 h.' !!}

Parcours : {!! collect($etapes)->values()->map(fn ($e, $i) => $i === $position ? '[' . $e . ']' : $e)->join(' > ') !!}

Numéro de suivi : {!! $commande->tracking_number !!}
Suivre ma commande : {!! $lienSuivi !!}

RÉCAPITULATIF
@foreach($commande->items as $item)
- {!! $item->produit->nom ?? 'Produit' !!} × {!! $quantite($item->quantite) !!} : {!! $montant($item->sous_total) !!}
@endforeach
@if($commande->discount_amount > 0)
Réduction : − {!! $montant($commande->discount_amount) !!}
@endif
Total : {!! $montant($commande->total) !!}

PAIEMENT : {!! $reglement[0] !!}
{!! $reglement[1] !!}

{!! $boutique ? 'RETRAIT : en boutique, dès le lendemain. Présentez le numéro ' . $numero . ' au comptoir.' : 'LIVRAISON : à domicile, ' . implode(', ', $adresse) !!}

Retrouvez cette commande dans votre compte : {!! $lienCompte !!}
Une question ? Répondez simplement à cet e-mail.

{{ boutique('nom') }} · {{ boutique('slogan') }} · Côte d'Ivoire
