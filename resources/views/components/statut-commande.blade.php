@props(['statut', 'client' => false])
{{--
  Pastille de statut de commande : couleur réservée + point + libellé (jamais la couleur seule).
  « client » : libellés des devis vus par le revendeur (« Devis à valider ») plutôt que par l'équipe (« Devis envoyé »).
--}}
@php
    $statuts = [
        'devis_demande' => [$client ? 'Devis en préparation' : 'Devis à chiffrer', 'pastille-attente'],
        'devis_envoye' => [$client ? 'Devis à valider' : 'Devis envoyé', $client ? 'pastille-attente' : 'pastille-brun'],
        'devis_refuse' => ['Devis refusé', ''],
        'en_attente' => ['En attente', 'pastille-attente'],
        'confirmee' => ['Confirmée', 'pastille-brun'],
        'en_preparation' => ['En préparation', 'pastille-brun'],
        'expedie' => ['Expédiée', ''],
        'livree' => ['Livrée', 'pastille-succes'],
        'annulee' => ['Annulée', 'pastille-danger'],
        'paye' => ['Payé', 'pastille-succes'],
        'echoue' => ['Échoué', 'pastille-danger'],
    ];
    [$libelle, $ton] = $statuts[$statut] ?? [ucfirst(str_replace('_', ' ', (string) $statut)), ''];
@endphp
<span {{ $attributes->merge(['class' => 'pastille ' . $ton]) }}>{{ $libelle }}</span>
