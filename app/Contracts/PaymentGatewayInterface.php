<?php

namespace App\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment process.
     *
     * @param float $amount
     * @param string $currency
     * @param array $orderData
     * @return string Redirect URL or payment reference
     */
    public function createPayment(float $amount, string $currency, array $orderData): string;

    /**
     * Verify a payment callback/webhook.
     *
     * @param array $payload
     * @param string $signature
     * @return bool
     */
    public function verifyWebhook(array $payload, string $signature): bool;
}
