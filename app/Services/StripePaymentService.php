<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

class StripePaymentService implements PaymentGatewayInterface
{
    protected ?StripeClient $stripe = null;

    public function __construct()
    {
        $secret = config('services.stripe.secret');
        if ($secret) {
            $this->stripe = new StripeClient($secret);
        }
    }

    public function createPayment(float $amount, string $currency, array $orderData): string
    {
        if (!$this->stripe) {
            // Mode Simulation : Retourne une URL fictive pour tester le flux sans clé API
            return route('paiement.success') . '?session_id=mock_session_' . $orderData['order_id'];
        }

        try {
            $session = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => 'Commande #' . $orderData['order_id'],
                        ],
                        'unit_amount' => (int) ($amount * 100), // Stripe works in cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('paiement.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('paiement.cancel'),
                'client_reference_id' => $orderData['order_id'],
                'metadata' => [
                    'order_id' => $orderData['order_id'],
                ],
            ]);

            return $session->url;
        } catch (\Exception $e) {
            Log::error('Stripe Payment Error: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la création de la session Stripe.');
        }
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );

            return $event->type === 'checkout.session.completed';
        } catch (\Exception $e) {
            Log::error('Stripe Webhook Error: ' . $e->getMessage());
            return false;
        }
    }
}
