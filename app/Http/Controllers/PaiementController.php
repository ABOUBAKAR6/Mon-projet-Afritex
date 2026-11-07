<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use App\Models\Produit;
use App\Notifications\PaiementConfirmeNotification;
use App\Services\FedaPayPaymentService;
use App\Services\StripePaymentService;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaiementController extends Controller
{
    protected $fedapayService;
    protected $stripeService;
    protected $loyaltyService;

    public function __construct(FedaPayPaymentService $fedapayService, StripePaymentService $stripeService, LoyaltyService $loyaltyService)
    {
        $this->fedapayService = $fedapayService;
        $this->stripeService = $stripeService;
        $this->loyaltyService = $loyaltyService;
    }

    public function index(Commande $commande)
    {
        if ($refus = $this->refuserSiNonPayable($commande)) {
            return $refus;
        }

        $commande->load('items.produit');
        return view('paiement', compact('commande'));
    }

    public function payer(Request $request, Commande $commande)
    {
        if ($refus = $this->refuserSiNonPayable($commande)) {
            return $refus;
        }

        // Cas particulier : paiement en espèces (Cash on Delivery)
        if ($request->methode === 'especes') {
            $paiement = Paiement::create([
                'commande_id' => $commande->id,
                'montant' => $commande->total,
                'methode' => 'especes',
                'statut' => 'en_attente',
                'reference' => 'CASH-' . strtoupper(uniqid())
            ]);

            $commande->update(['statut' => 'en_attente']);
            $this->decrementStock($commande);

            // Notification dans le compte du client (l'e-mail part via envoyerConfirmation)
            try {
                $commande->user->notify(new PaiementConfirmeNotification($paiement));
            } catch (\Throwable $e) {
                report($e);
            }
            $commande->envoyerConfirmation();

            $moment = $commande->estEnBoutique() ? 'au retrait en boutique' : 'à la livraison';
            return redirect()->route('accueil')
                            ->with('success', 'Commande reçue ! Un e-mail de confirmation vous a été envoyé. Préparez ' . number_format($commande->total, 0, ',', ' ') . ' F ' . $moment . '.');
        }

        // Le formulaire envoie le moyen de paiement (« mobile_money », « carte ») ; on garde le nom de la passerelle
        // dans le paiement, car le retour (callback) choisit la passerelle d'après ce nom.
        $passerelle = match ($request->methode) {
            'mobile_money', 'fedapay' => 'fedapay',
            'carte', 'stripe' => 'stripe',
            default => null,
        };

        try {
            // Choisir la passerelle de paiement
            $gateway = match($passerelle) {
                'stripe' => $this->stripeService,
                'fedapay' => $this->fedapayService,
                default => throw new \Exception('Méthode de paiement non supportée.'),
            };

            $currency = ($passerelle === 'stripe') ? 'usd' : 'XOF';
            $amount = ($passerelle === 'stripe') ? ($commande->total / 655) : $commande->total; // Approximation XOF to USD

            $paymentUrl = $gateway->createPayment($amount, $currency, [
                'order_id' => $commande->id
            ]);

            // Enregistrer le paiement localement
            Paiement::create([
                'commande_id' => $commande->id,
                'montant' => $commande->total,
                'methode' => $passerelle,
                'statut' => 'en_attente',
                'reference' => 'PAY-' . strtoupper(uniqid()),
            ]);

            return redirect()->away($paymentUrl);

        } catch (\Exception $e) {
            Log::error('Payment Execution Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erreur lors du paiement : ' . $e->getMessage());
        }
    }

    public function callback(Request $request, $commandeId)
    {
        try {
            $commande = Commande::findOrFail($commandeId);

            // On vérifie quel service utiliser pour la validation (basé sur le dernier paiement enregistré)
            $paiement = Paiement::where('commande_id', $commandeId)->latest()->first();

            if (!$paiement) {
                return redirect()->route('accueil')->with('error', 'Paiement introuvable.');
            }

            $gateway = match($paiement->methode) {
                'stripe' => $this->stripeService,
                'fedapay' => $this->fedapayService,
                default => null,
            };

            if (!$gateway) {
                return redirect()->route('accueil')->with('error', 'Passerelle de paiement non supportée.');
            }

            // Pour FedaPay, on utilise le callback classique. Pour Stripe, on utilise généralement des webhooks.
            // Ici on simule une vérification simplifiée pour le callback.
            $isApproved = $gateway->verifyWebhook($request->all(), $request->header('Stripe-Signature', ''));

            if ($isApproved) {
                $paiement->update(['statut' => 'confirme']);

                if ($commande->statut !== 'confirmee') {
                    $commande->update(['statut' => 'confirmee']);
                    $this->decrementStock($commande);

                    // Ajouter des points de fidélité (1 point pour 10 FCFA)
                    $points = (int) ($commande->total / 10);
                    if ($points > 0) {
                        $this->loyaltyService->addPoints($commande->user, $points, 'Achat commande #' . $commande->id);
                    }
                }

                try {
                    $commande->user->notify(new PaiementConfirmeNotification($paiement));
                } catch (\Exception $e) {}
                $commande->envoyerConfirmation();

                return redirect()->route('accueil')
                    ->with('success', 'Paiement confirmé ! Un e-mail de confirmation vous a été envoyé avec le lien de suivi.');
            } else {
                $paiement->update(['statut' => 'echoue']);
                return redirect()->route('paiement', $commande->id)
                    ->with('error', '❌ Le paiement a été refusé ou n\'est pas encore terminé.');
            }

        } catch (\Exception $e) {
            return redirect()->route('accueil')
                ->with('error', 'Erreur lors de la vérification du paiement : ' . $e->getMessage());
        }
    }

    /**
     * Seul le client de la commande la paie, et seulement si elle est à payer :
     * ni la commande d'un autre, ni un devis pas encore chiffré ou accepté, ni une commande déjà réglée.
     */
    private function refuserSiNonPayable(Commande $commande)
    {
        abort_unless($commande->user_id === auth()->id(), 403);

        if ($commande->estAPayer()) {
            return null;
        }

        $retour = $commande->est_pro ? route('pro.commande', $commande) : route('commandes.show', $commande);
        $message = $commande->estDevis()
            ? "Ce devis doit d'abord être chiffré par la boutique puis accepté avant le paiement."
            : "Cette commande n'est plus à payer.";

        return redirect($retour)->with('error', $message);
    }

    // Mètres et rouleaux vendus retirés du stock, avec l'historique (une seule fois par commande)
    private function decrementStock(Commande $commande)
    {
        app(\App\Services\GestionStock::class)->sortieCommande($commande);
    }
}