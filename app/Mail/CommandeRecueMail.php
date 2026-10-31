<?php

namespace App\Mail;

use App\Models\Commande;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * « Votre commande est bien reçue » : envoyé une fois par commande, quel que soit le paiement
 * (espèces, Mobile Money, carte) et le mode de remise (livraison à domicile, retrait en boutique).
 * Contient le récapitulatif, le parcours de la commande et le lien de suivi.
 */
class CommandeRecueMail extends Mailable
{
    public function __construct(public Commande $commande) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Commande ' . $this->numero() . ' bien reçue · AfriTex',
        );
    }

    public function content(): Content
    {
        $commande = $this->commande;
        $boutique = $commande->estEnBoutique();
        $paiement = $commande->paiement;
        $especes = !$paiement || $paiement->methode === 'especes';

        // Ce que le client doit savoir sur le règlement, selon le moyen choisi
        $reglement = match (true) {
            $especes && $boutique => ['Espèces au retrait', 'Vous réglerez ' . $this->montant($commande->total) . ' en espèces en récupérant votre commande.'],
            $especes => ['Espèces à la livraison', 'Préparez ' . $this->montant($commande->total) . ' en espèces pour le livreur.'],
            $paiement->methode === 'fedapay' => ['Mobile Money', 'Votre paiement de ' . $this->montant($commande->total) . ' est confirmé.'],
            default => ['Carte bancaire', 'Votre paiement de ' . $this->montant($commande->total) . ' est confirmé.'],
        };

        // Adresse saisie au format « Nom | téléphone | ville, commune | adresse | GPS: … | Note: … »
        $morceaux = collect(explode(' | ', (string) $commande->adresse_livraison))
            ->map(fn ($m) => trim($m))
            ->reject(fn ($m) => $m === '' || str_starts_with($m, 'GPS:'));

        return new Content(
            view: 'emails.commande-recue',
            text: 'emails.commande-recue-texte',
            with: [
                'numero' => $this->numero(),
                'boutique' => $boutique,
                'reglement' => $reglement,
                'adresse' => $morceaux->values()->all(),
                'etapes' => $this->etapes($boutique),
                'position' => array_search($commande->statut, array_keys($this->etapes($boutique)), true),
                'lienSuivi' => route('track', $commande->tracking_number),
                'lienCompte' => route('commandes.show', $commande),
                'montant' => fn ($valeur) => $this->montant($valeur),
                // 1.00 -> « 1 », 1.50 -> « 1,5 » (demi-mètre)
                'quantite' => fn ($q) => rtrim(rtrim(number_format((float) $q, 2, ',', ''), '0'), ','),
            ],
        );
    }

    private function numero(): string
    {
        return '#' . str_pad($this->commande->id, 4, '0', STR_PAD_LEFT);
    }

    private function montant($valeur): string
    {
        return number_format((float) $valeur, 0, ',', ' ') . ' F';
    }

    // Mêmes étapes que la frise de la page de suivi (partials.suivi-commande), libellées selon le mode de remise
    private function etapes(bool $boutique): array
    {
        return [
            'en_attente' => 'Reçue',
            'confirmee' => 'Confirmée',
            'en_preparation' => 'En préparation',
            'expedie' => $boutique ? 'Prête au retrait' : 'Expédiée',
            'livree' => $boutique ? 'Retirée' : 'Livrée',
        ];
    }
}
