<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentControllerInterface;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentFactory;

abstract class PaymentController implements PaymentControllerInterface
{
    protected string $provider;
    protected array $config;

    public function __construct(string $provider, array $config = [])
    {
        $this->provider = $provider;
        $this->config = $config;
    }

    public function createTransaction(array $params): PaymentResponse
    {
        $gateway = PaymentFactory::create($this->provider, $this->config);
        return $gateway->createTransaction($params);
    }

    public function prepareTransientData(PaymentResponse $response, array $baseData): array
    {
        return array_merge($baseData, [
            'payment_url' => $response->paymentUrl,
            'status' => $response->status,
            'reference' => $response->reference
        ]);
    }

    abstract public function handleCallback($payload, array $headers = []): array;
}
