<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use FedaPay\FedaPay;
use FedaPay\Transaction;
use Illuminate\Support\Facades\Log;

class FedaPayPaymentService implements PaymentGatewayInterface
{
    public function __construct()
    {
        FedaPay::setApiKey(config('services.fedapay.secret'));
        FedaPay::setEnvironment(config('services.fedapay.env'));
    }

    public function createPayment(float $amount, string $currency, array $orderData): string
    {
        try {
            $user = auth()->user();
            $customer = [
                'firstname' => $user->name,
                'email' => $user->email,
            ];

            $transaction = Transaction::create([
                'description' => 'Paiement commande #' . $orderData['order_id'],
                'amount' => $amount,
                'currency' => ['iso' => $currency],
                'callback_url' => route('paiement.callback', $orderData['order_id']),
                'customer' => $customer,
            ]);

            $token = $transaction->generateToken();
            return $token->url;
        } catch (\Exception $e) {
            Log::error('FedaPay Payment Error: ' . $e->getMessage());
            throw new \Exception('Erreur lors de la création du paiement FedaPay.');
        }
    }

    public function verifyWebhook(array $payload, string $signature): bool
    {
        // FedaPay usually handles callbacks via redirect or specific webhooks
        // For simple callback verification:
        try {
            $transactionId = $payload['transaction_id'] ?? null;
            if (!$transactionId) return false;

            $transaction = Transaction::retrieve($transactionId);
            return $transaction->status === 'approved';
        } catch (\Exception $e) {
            Log::error('FedaPay Webhook Error: ' . $e->getMessage());
            return false;
        }
    }
}
