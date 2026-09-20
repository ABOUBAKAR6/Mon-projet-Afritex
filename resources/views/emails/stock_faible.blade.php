@component('mail::message')
# ⚠️ Alert: Stock faible pour {{ $produit->nom }}

Le produit **{{ $produit->nom }}** a atteint un stock inférieur ou égal à votre seuil défini ({{ $seuil }} unités).

**Stock actuel :** {{ $produit->stock ?? $produit->stock_disponible ?? 'inconnu' }}

@component('mail::button', ['url' => $url])
Voir le produit
@endcomponent

Merci de votre confiance,

L'équipe AfriTex
@endcomponent