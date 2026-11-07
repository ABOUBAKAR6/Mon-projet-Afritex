<?php

namespace App\Services;

use App\Models\Commande;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Seul point d'entrée pour faire bouger le stock d'un tissu.
 *
 * Le stock se compte en rouleaux entiers (produits.stock) + mètres du rouleau ouvert
 * (produits.metres_entames). Vendre au mètre puise dans le rouleau ouvert, puis ouvre
 * les rouleaux suivants (il faut connaître metres_par_rouleau) ; vendre au rouleau
 * retire des rouleaux entiers. Chaque opération laisse une ligne dans mouvements_stock.
 */
class GestionStock
{
    /** Réception de marchandise : ajoute des rouleaux et/ou des mètres. */
    public function arrivage(Produit $produit, int $rouleaux, float $metres, ?string $note = null, ?User $par = null, bool $remettreEnVente = false): MouvementStock
    {
        return $this->appliquer($produit, 'arrivage', $note, $par, null, function (Produit $p) use ($rouleaux, $metres, $remettreEnVente) {
            $p->stock += $rouleaux;
            $p->metres_entames = round($p->metres_entames + $metres, 2);
            if ($remettreEnVente) {
                $p->disponible = true;
            }
        });
    }

    /**
     * Sortie manuelle (perte, coupe, échantillon…). Refusée si le stock ne suffit pas :
     * contrairement à une vente déjà payée, rien n'oblige à passer sous zéro.
     */
    public function retrait(Produit $produit, int $rouleaux, float $metres, ?string $note = null, ?User $par = null): MouvementStock
    {
        return $this->appliquer($produit, 'retrait', $note, $par, null, function (Produit $p) use ($rouleaux, $metres) {
            if ($rouleaux > $p->stock) {
                throw ValidationException::withMessages(['rouleaux' => 'Il ne reste que '.$p->stock.' rouleau'.($p->stock > 1 ? 'x' : '').' entier'.($p->stock > 1 ? 's' : '').'.']);
            }
            $p->stock -= $rouleaux;

            if ($metres > $p->metres_entames + 0.001) {
                if (! $p->metres_par_rouleau) {
                    throw ValidationException::withMessages(['metres' => 'Le rouleau ouvert n\'a que '.Produit::metres(max(0, $p->metres_entames)).'. Indiquez la longueur d\'un rouleau pour en ouvrir un autre.']);
                }
                if ($metres > $p->stock * $p->metres_par_rouleau + $p->metres_entames + 0.001) {
                    throw ValidationException::withMessages(['metres' => 'Le stock ne contient pas autant de mètres.']);
                }
            }
            $this->puiserMetres($p, $metres);
        });
    }

    /** Comptage réel : le stock devient exactement ce qui a été compté. */
    public function inventaire(Produit $produit, int $rouleaux, float $metres, ?string $note = null, ?User $par = null): MouvementStock
    {
        return $this->appliquer($produit, 'inventaire', $note, $par, null, function (Produit $p) use ($rouleaux, $metres) {
            $p->stock = $rouleaux;
            $p->metres_entames = round($metres, 2);
        });
    }

    /** Nouvelle longueur de rouleau ; des mètres vendus en attente sont alors décomptés. */
    public function definirLongueur(Produit $produit, ?float $metresParRouleau, ?User $par = null): void
    {
        DB::transaction(function () use ($produit, $metresParRouleau, $par) {
            $p = Produit::lockForUpdate()->findOrFail($produit->id);
            $p->metres_par_rouleau = $metresParRouleau ?: null;
            $p->save();

            // Mètres vendus quand la longueur était inconnue : on ouvre maintenant les rouleaux nécessaires
            if ($p->metres_entames < 0 && $p->metres_par_rouleau) {
                $this->appliquer($p, 'inventaire', 'Mètres vendus décomptés après avoir indiqué la longueur du rouleau', $par, null, function (Produit $q) {
                    $this->puiserMetres($q, 0);
                });
            }
            $produit->refresh();
        });
    }

    /**
     * Ventes d'une commande payée : mètres et rouleaux retirés du stock. Les confections
     * (pantalon, chemise, tunique) ne bougent pas le stock. Une commande n'est décomptée
     * qu'une fois, même si le paiement est confirmé deux fois.
     */
    public function sortieCommande(Commande $commande): void
    {
        DB::transaction(function () use ($commande) {
            $dejaFaite = MouvementStock::where('commande_id', $commande->id)->where('type', 'vente')->lockForUpdate()->exists();
            if ($dejaFaite) {
                return;
            }

            $numero = 'Commande n° '.str_pad($commande->id, 4, '0', STR_PAD_LEFT);
            foreach ($commande->items()->with('produit')->get() as $item) {
                if (! $item->produit || (float) $item->quantite <= 0) {
                    continue;
                }
                $quantite = (float) $item->quantite;

                if ($item->type_commande === 'metre') {
                    $this->appliquer($item->produit, 'vente', $numero.' · '.Produit::metres($quantite), null, $commande, function (Produit $p) use ($quantite) {
                        $this->puiserMetres($p, $quantite);
                        $this->masquerSiEpuise($p);
                    });
                } elseif ($item->type_commande === 'rouleau') {
                    $rouleaux = (int) round($quantite);
                    $this->appliquer($item->produit, 'vente', $numero.' · '.$rouleaux.' rouleau'.($rouleaux > 1 ? 'x' : ''), null, $commande, function (Produit $p) use ($rouleaux) {
                        $p->stock = max(0, $p->stock - $rouleaux);
                        $this->masquerSiEpuise($p);
                    });
                }
            }
        });
    }

    /**
     * Retire des mètres : d'abord le rouleau ouvert, puis on ouvre des rouleaux entiers.
     * Longueur inconnue : les mètres restent « à décompter » (metres_entames négatif)
     * jusqu'à ce que la longueur soit indiquée. Plus de rouleau : on s'arrête à zéro.
     */
    private function puiserMetres(Produit $p, float $metres): void
    {
        $reste = round($p->metres_entames - $metres, 2);

        if ($reste < 0 && $p->metres_par_rouleau) {
            $aOuvrir = min($p->stock, (int) ceil(round(-$reste / $p->metres_par_rouleau, 6)));
            $p->stock -= $aOuvrir;
            $reste = round($reste + $aOuvrir * $p->metres_par_rouleau, 2);
            $reste = max(0, $reste);
        }

        $p->metres_entames = $reste;
    }

    private function masquerSiEpuise(Produit $p): void
    {
        if ($p->stock <= 0 && $p->metres_entames <= 0) {
            $p->disponible = false;
        }
    }

    private function appliquer(Produit $produit, string $type, ?string $note, ?User $par, ?Commande $commande, callable $operation): MouvementStock
    {
        return DB::transaction(function () use ($produit, $type, $note, $par, $commande, $operation) {
            $p = Produit::lockForUpdate()->findOrFail($produit->id);
            $avant = [$p->stock, (float) $p->metres_entames];

            $operation($p);
            $p->save();

            $mouvement = MouvementStock::create([
                'produit_id' => $p->id,
                'user_id' => $par?->id,
                'commande_id' => $commande?->id,
                'type' => $type,
                'rouleaux' => $p->stock - $avant[0],
                'metres' => round($p->metres_entames - $avant[1], 2),
                'rouleaux_apres' => $p->stock,
                'metres_apres' => $p->metres_entames,
                'note' => $note ? mb_substr($note, 0, 255) : null,
            ]);

            $produit->setRawAttributes($p->getAttributes(), true);

            return $mouvement;
        });
    }
}
