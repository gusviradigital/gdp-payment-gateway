<?php

namespace GusviraDigital\GdpPaymentGateway\Events;

class PaymentPending
{
    public string $orderId;
    public ?string $transactionId;
    public string $provider;
    public array $rawResponse;

    public function __construct(string $orderId, ?string $transactionId, string $provider, array $rawResponse = [])
    {
        $this->orderId = $orderId;
        $this->transactionId = $transactionId;
        $this->provider = $provider;
        $this->rawResponse = $rawResponse;
    }
}
