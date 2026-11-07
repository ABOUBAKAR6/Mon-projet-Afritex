<?php

namespace App\Support;

use App\Models\Commande;

/**
 * Message WhatsApp d'un devis ou d'une commande pro, prêt à envoyer :
 * un bloc par tissu (nom, format, quantité en mètres ou en rouleaux, prix, sous-total),
 * puis les montants, la livraison et les notes, séparés par des lignes vides.
 *
 *  - pour « revendeur » : la boutique envoie le devis au revendeur ;
 *  - pour « boutique »  : le revendeur envoie sa demande au propriétaire.
 */
class MessageDevis
{
    public static function texte(Commande $commande, string $pour = 'revendeur'): string
    {
        $commande->loadMissing(['items.produit', 'user.comptePro']);
        $numero = str_pad($commande->id, 4, '0', STR_PAD_LEFT);
        $devis = $commande->estDevis();
        $revendeur = $commande->user?->comptePro?->boutique ?? $commande->user?->name ?? '';
        $indicatif = $commande->statut === 'devis_demande';

        $lignes = [];
        if ($pour === 'revendeur') {
            $lignes[] = 'Bonjour'.($revendeur ? ' '.$revendeur : '').',';
            $lignes[] = '';
            $lignes[] = $devis
                ? 'Voici votre devis n° '.$numero.' chez '.boutique('nom').' :'
                : 'Voici le récapitulatif de votre commande n° '.$numero.' chez '.boutique('nom').' :';
        } else {
            $lignes[] = 'Bonjour '.boutique('nom').',';
            $lignes[] = '';
            $lignes[] = ($devis ? 'Voici ma demande de devis n° ' : 'Voici ma commande n° ').$numero
                .($revendeur ? ' ('.$revendeur.')' : '').' :';
        }
        $lignes[] = '';

        $sousTotal = 0;
        foreach ($commande->items as $item) {
            if ((float) $item->quantite <= 0) {
                continue;
            }
            $sousTotal += (int) $item->sous_total;
            $lignes[] = '*'.($item->produit->nom ?? 'Tissu retiré du catalogue').'*';
            $lignes[] = $item->libelleFormat().' : '.self::quantite($item->type_commande, (float) $item->quantite)
                .' × '.self::francs($item->prix_unitaire).' = '.self::francs($item->sous_total);
            $lignes[] = '';
        }

        if ($commande->frais_livraison !== null) {
            $lignes[] = 'Tissus : '.self::francs($sousTotal);
            $lignes[] = 'Livraison : '.($commande->frais_livraison ? self::francs($commande->frais_livraison) : 'offerte');
        }
        $lignes[] = '*'.($indicatif ? 'Total indicatif' : 'Total').' : '.self::francs($commande->total).'*';
        $lignes[] = '';

        $lignes[] = $commande->mode_livraison === 'boutique'
            ? 'Retrait en boutique'
            : 'Livraison : '.($commande->adresse_livraison ?: 'adresse à préciser');
        if ($commande->livraison_prevue_le) {
            $lignes[] = 'Date prévue : '.$commande->livraison_prevue_le->translatedFormat('l j F Y');
        } elseif ($commande->livraison_souhaitee_le) {
            $lignes[] = 'Date souhaitée : '.$commande->livraison_souhaitee_le->translatedFormat('l j F Y');
        }

        $note = $pour === 'revendeur' ? $commande->note_boutique : $commande->note_client;
        if ($note) {
            $lignes[] = '';
            $lignes[] = ($pour === 'revendeur' ? 'Mot de la boutique : ' : 'Ma note : ').$note;
        }

        $lignes[] = '';
        if ($pour === 'revendeur') {
            $lignes[] = ($commande->statut === 'devis_envoye' ? 'Validez le devis dans votre Espace Pro : ' : 'Détail dans votre Espace Pro : ')
                .route('pro.commande', $commande);
        }
        $lignes[] = 'Merci !';

        return implode("\n", $lignes);
    }

    /** Lien wa.me qui ouvre WhatsApp avec le message déjà écrit. */
    public static function lien(string $lienWhatsapp, Commande $commande, string $pour = 'revendeur'): string
    {
        return $lienWhatsapp.'?text='.rawurlencode(self::texte($commande, $pour));
    }

    private static function quantite(?string $format, float $quantite): string
    {
        $lisible = rtrim(rtrim(number_format($quantite, 2, ',', ''), '0'), ',');

        return match ($format) {
            'metre' => $lisible.' m',
            'rouleau' => $lisible.' rouleau'.($quantite > 1 ? 'x' : ''),
            default => $lisible.' pièce'.($quantite > 1 ? 's' : ''),
        };
    }

    private static function francs($montant): string
    {
        return number_format((float) $montant, 0, ',', ' ').' F';
    }
}
