<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\Produit;
use App\Models\User;
use App\Notifications\EspaceProNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

use function Illuminate\Support\defer;

/**
 * Espace Pro (vente en gros) : demande de devis, chiffrage par la boutique,
 * acceptation ou refus par le revendeur, chiffres du tableau de bord, messages.
 */
class EspaceProService
{
    /**
     * Crée une demande de devis à partir des quantités saisies dans le tableau « Commander ».
     *
     * @param  array<int|string, array<string, int|string|null>>  $quantites  [produit_id => [format => quantité]]
     * @param  array{mode_livraison: string, adresse_livraison?: ?string, livraison_souhaitee_le?: ?string, note_client?: ?string}  $infos
     */
    public function demanderDevis(User $pro, array $quantites, array $infos): Commande
    {
        $lignes = [];
        $produits = Produit::whereIn('id', array_keys($quantites))->where('disponible', true)->get()->keyBy('id');

        foreach ($quantites as $produitId => $parFormat) {
            $produit = $produits->get((int) $produitId);
            if (! $produit || ! is_array($parFormat)) {
                continue;
            }
            foreach ($parFormat as $format => $quantite) {
                $quantite = (int) $quantite;
                $prix = $produit->prixPourPro((string) $format);
                if ($quantite < 1 || ! $prix) {
                    continue;
                }
                $lignes[] = [
                    'produit_id' => $produit->id,
                    'type_commande' => $format,
                    'quantite' => min($quantite, 9999),
                    'prix_unitaire' => $prix,
                    'sous_total' => $prix * min($quantite, 9999),
                ];
            }
        }

        if (! $lignes) {
            throw ValidationException::withMessages([
                'quantites' => 'Indiquez au moins une quantité pour un tissu.',
            ]);
        }

        $commande = DB::transaction(function () use ($pro, $lignes, $infos) {
            $commande = Commande::create([
                'user_id' => $pro->id,
                'est_pro' => true,
                'statut' => 'devis_demande',
                // Total indicatif aux prix pro : la boutique le confirme en envoyant le devis
                'total' => array_sum(array_column($lignes, 'sous_total')),
                'mode_livraison' => $infos['mode_livraison'] === 'boutique' ? 'boutique' : 'domicile',
                'adresse_livraison' => $infos['mode_livraison'] === 'boutique'
                    ? 'Retrait en boutique'
                    : (string) ($infos['adresse_livraison'] ?? ''),
                // Champs facultatifs : absents des données validées quand le formulaire ne les envoie pas
                'livraison_souhaitee_le' => ($infos['livraison_souhaitee_le'] ?? null) ?: null,
                'note_client' => ($infos['note_client'] ?? null) ?: null,
            ]);

            foreach ($lignes as $ligne) {
                CommandeItem::create($ligne + ['commande_id' => $commande->id]);
            }

            return $commande;
        });

        $boutique = $pro->comptePro?->boutique ?? $pro->name;
        $this->prevenirEquipe('manage-orders', new EspaceProNotification(
            'Nouveau devis demandé par '.$boutique,
            [
                $boutique.' demande un devis pour '.count($lignes).' ligne(s), soit '.$this->francs($commande->total).' (prix indicatifs).',
                'Vérifiez le stock, ajoutez les frais de livraison et la date, puis envoyez le devis.',
            ],
            route('admin.commande.detail', $commande),
            'Chiffrer le devis',
            'devis_demande',
        ));

        return $commande;
    }

    /**
     * La boutique chiffre le devis : quantités et prix par ligne, frais de livraison, date prévue, mot au client.
     *
     * @param  array<int|string, array{quantite?: int|string, prix_unitaire?: int|string}>  $lignes  [item_id => ...]
     */
    public function envoyerDevis(Commande $commande, array $lignes, int $frais, ?string $date, ?string $note): void
    {
        DB::transaction(function () use ($commande, $lignes, $frais, $date, $note) {
            foreach ($commande->items as $item) {
                $saisie = $lignes[$item->id] ?? null;
                if ($saisie === null) {
                    continue;
                }
                $quantite = max(0, (int) ($saisie['quantite'] ?? $item->quantite));
                if ($quantite === 0) {
                    // Ligne retirée par la boutique (rupture, tissu indisponible…)
                    $item->delete();
                    continue;
                }
                $prix = max(0, (int) ($saisie['prix_unitaire'] ?? $item->prix_unitaire));
                $item->update([
                    'quantite' => $quantite,
                    'prix_unitaire' => $prix,
                    'sous_total' => $quantite * $prix,
                ]);
            }

            $commande->load('items');
            if ($commande->items->isEmpty()) {
                throw ValidationException::withMessages(['lignes' => 'Le devis doit garder au moins une ligne.']);
            }

            $commande->update([
                'frais_livraison' => max(0, $frais),
                'total' => $commande->sousTotalArticles() + max(0, $frais),
                'livraison_prevue_le' => $date ?: null,
                'note_boutique' => $note ?: null,
                'statut' => 'devis_envoye',
                'devis_envoye_le' => now(),
            ]);
        });

        $this->prevenir($commande->user, new EspaceProNotification(
            'Votre devis n°'.$this->numero($commande).' est prêt',
            [
                'Montant : '.$this->francs($commande->total).($commande->frais_livraison ? ', livraison comprise' : '').'.',
                $commande->livraison_prevue_le
                    ? 'Livraison prévue le '.$commande->livraison_prevue_le->locale('fr')->translatedFormat('l j F').'.'
                    : 'La date de livraison vous sera confirmée.',
                'Validez-le pour passer au paiement, ou refusez-le depuis votre Espace Pro.',
            ],
            route('pro.commande', $commande),
            'Voir le devis',
            'devis_envoye',
        ));
    }

    public function accepterDevis(Commande $commande): void
    {
        $commande->update(['statut' => 'en_attente']);
        $this->signalerReponse($commande, 'accepté', 'Le revendeur va maintenant payer la commande.');
    }

    /**
     * Le revendeur demande une modification du devis envoyé : le devis repasse « à chiffrer »
     * et sa demande s'ajoute, datée, à ses notes (l'historique des échanges reste lisible des deux côtés).
     */
    public function demanderModification(Commande $commande, string $message): void
    {
        $demande = 'Demande de modification du '.now()->locale('fr')->translatedFormat('j F à H:i').' : '.trim($message);

        $commande->update([
            'statut' => 'devis_demande',
            'note_client' => trim(($commande->note_client ? $commande->note_client."\n\n" : '').$demande),
        ]);

        $boutique = $commande->user->comptePro?->boutique ?? $commande->user->name;
        $this->prevenirEquipe('manage-orders', new EspaceProNotification(
            $boutique.' demande une modification du devis n°'.$this->numero($commande),
            ['« '.trim($message).' »', 'Le devis repasse à chiffrer : ajustez-le puis renvoyez-le.'],
            route('admin.commande.detail', $commande),
            'Revoir le devis',
            'devis_modification',
        ));
    }

    public function refuserDevis(Commande $commande): void
    {
        $commande->update(['statut' => 'devis_refuse']);
        $this->signalerReponse($commande, 'refusé', 'Vous pouvez le contacter sur WhatsApp pour en discuter.');
    }

    /**
     * Les 4 chiffres du tableau de bord pro.
     *
     * @return array{achats_mois: int, en_cours: int, devis_a_valider: int, prochaine_livraison: ?\Illuminate\Support\Carbon}
     */
    public function chiffres(User $pro): array
    {
        $commandes = Commande::where('user_id', $pro->id)->where('est_pro', true);

        return [
            'achats_mois' => (int) (clone $commandes)
                ->whereIn('statut', ['confirmee', 'en_preparation', 'expedie', 'livree'])
                ->where('created_at', '>=', now()->startOfMonth())
                ->sum('total'),
            'en_cours' => (clone $commandes)->whereIn('statut', Commande::STATUTS_EN_COURS)->count(),
            'devis_a_valider' => (clone $commandes)->where('statut', 'devis_envoye')->count(),
            'prochaine_livraison' => (clone $commandes)
                ->whereIn('statut', Commande::STATUTS_EN_COURS)
                ->whereDate('livraison_prevue_le', '>=', today())
                ->orderBy('livraison_prevue_le')
                ->first()?->livraison_prevue_le,
        ];
    }

    /** Message à toute l'équipe ayant ce droit (le propriétaire a tous les droits). */
    public function prevenirEquipe(string $droit, EspaceProNotification $notification): void
    {
        $equipe = User::whereIn('role', ['admin', 'secretaire'])->get()->filter(fn (User $u) => $u->can($droit));
        $this->prevenir($equipe, $notification);
    }

    /**
     * Envoi après la réponse, sans jamais bloquer le parcours :
     * un serveur mail absent est consigné dans les logs.
     */
    public function prevenir($destinataires, EspaceProNotification $notification): void
    {
        defer(function () use ($destinataires, $notification) {
            try {
                Notification::send($destinataires, $notification);
            } catch (\Throwable $e) {
                report($e);
            }
        });
    }

    public function numero(Commande $commande): string
    {
        return str_pad((string) $commande->id, 4, '0', STR_PAD_LEFT);
    }

    private function signalerReponse(Commande $commande, string $reponse, string $suite): void
    {
        $boutique = $commande->user->comptePro?->boutique ?? $commande->user->name;
        $this->prevenirEquipe('manage-orders', new EspaceProNotification(
            $boutique.' a '.$reponse.' le devis n°'.$this->numero($commande),
            ['Montant : '.$this->francs($commande->total).'.', $suite],
            route('admin.commande.detail', $commande),
            'Voir la commande',
            'devis_'.($reponse === 'accepté' ? 'accepte' : 'refuse'),
        ));
    }

    private function francs(int|float $montant): string
    {
        return number_format((float) $montant, 0, ',', ' ').' F';
    }
}
