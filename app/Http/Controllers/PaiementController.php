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
        $commande->load('items.produit');
        return view('paiement', compact('commande'));
    }

    public function payer(Request $request, Commande $commande)
    {
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

            $commande->user->notify(new PaiementConfirmeNotification($paiement));

            return redirect()->route('accueil')
                            ->with('success', '✅ Commande confirmée ! Préparez ' . number_format($commande->total, 0, ',', ' ') . ' FCFA à la livraison !');
        }

        try {
            // Choisir la passerelle de paiement
            $gateway = match($request->methode) {
                'stripe' => $this->stripeService,
                'fedapay' => $this->fedapayService,
                default => throw new \Exception('Méthode de paiement non supportée.'),
            };

            $currency = ($request->methode === 'stripe') ? 'usd' : 'XOF';
            $amount = ($request->methode === 'stripe') ? ($commande->total / 655) : $commande->total; // Approximation XOF to USD

            $paymentUrl = $gateway->createPayment($amount, $currency, [
                'order_id' => $commande->id
            ]);

            // Enregistrer le paiement localement
            Paiement::create([
                'commande_id' => $commande->id,
                'montant' => $commande->total,
                'methode' => $request->methode,
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

                return redirect()->route('accueil')
                    ->with('success', '🎉 Paiement confirmé ! Merci pour votre commande AfriTex !');
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

    private function decrementStock(Commande $commande)
    {
        DB::transaction(function () use ($commande) {
            foreach ($commande->items as $item) {
                $produit = $item->produit;
                if ($produit) {
                    $produit->decrement('stock', $item->quantite);

                    if ($produit->stock <= 0) {
                        $produit->update(['disponible' => false]);
                    }
                }
            }
        });
    }
}