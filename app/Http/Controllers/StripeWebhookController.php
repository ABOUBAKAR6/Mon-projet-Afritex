<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\Paiement;
use App\Notifications\PaiementConfirmeNotification;
use App\Services\StripePaymentService;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StripeWebhookController extends Controller
{
    protected $stripeService;
    protected $loyaltyService;

    public function __construct(StripePaymentService $stripeService, LoyaltyService $loyaltyService)
    {
        $this->stripeService = $stripeService;
        $this->loyaltyService = $loyaltyService;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // 1. Vérifier la signature du webhook via le service Stripe
        if (!$this->stripeService->verifyWebhook(json_decode($payload, true), $sigHeader)) {
            Log::warning('Invalid Stripe Webhook Signature');
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $type = $event['type'];

        // 2. Gérer l'événement "checkout.session.completed"
        if ($type === 'checkout.session.completed') {
            $session = $event['data']['object'];
            $orderId = $session['metadata']['order_id'] ?? null;

            if (!$orderId) {
                Log::error('Stripe Webhook: Missing order_id in metadata');
                return response()->json(['error' => 'Order ID missing'], 400);
            }

            $this->confirmOrder($orderId, $session['id']);
        }

        return response()->json(['status' => 'success']);
    }

    private function confirmOrder($orderId, $stripeSessionId)
    {
        DB::transaction(function () use ($orderId, $stripeSessionId) {
            $commande = Commande::findOrFail($orderId);

            // Trouver ou créer le paiement associé
            $paiement = Paiement::where('commande_id', $orderId)
                ->where('methode', 'stripe')
                ->latest()
                ->first();

            if ($paiement) {
                $paiement->update([
                    'statut' => 'confirme',
                    'reference' => $stripeSessionId
                ]);
            } else {
                Paiement::create([
                    'commande_id' => $orderId,
                    'montant' => $commande->total,
                    'methode' => 'stripe',
                    'statut' => 'confirme',
                    'reference' => $stripeSessionId
                ]);
            }

            // Confirmer la commande et décrémenter le stock
            if ($commande->statut !== 'confirmee') {
                $commande->update(['statut' => 'confirmee']);

                // Mètres et rouleaux vendus retirés du stock, avec l'historique (une seule fois par commande)
                app(\App\Services\GestionStock::class)->sortieCommande($commande);

                // Ajouter des points de fidélité (1 point pour 10 FCFA)
                $points = (int) ($commande->total / 10);
                if ($points > 0) {
                    $this->loyaltyService->addPoints($commande->user, $points, 'Achat commande #' . $commande->id);
                }
            }

            // Notification au client
            try {
                $commande->user->notify(new PaiementConfirmeNotification($paiement ?? new Paiement()));
            } catch (\Exception $e) {
                Log::error('Webhook Notification Error: ' . $e->getMessage());
            }
            // Une seule fois, même si le retour client a déjà envoyé l'e-mail
            $commande->envoyerConfirmation();
        });
    }
}