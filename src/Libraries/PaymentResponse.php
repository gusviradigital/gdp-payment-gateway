<?php

namespace GusviraDigital\GdpPaymentGateway\Libraries;

class PaymentResponse
{
    /**
     * @var string URL untuk redirect user ke halaman pembayaran
     */
    public string $paymentUrl;

    /**
     * @var string Referensi order/transaksi
     */
    public string $reference;

    /**
     * @var string Status awal pembayaran (biasanya PENDING)
     */
    public string $status;

    /**
     * @var array Payload mentah dari gateway
     */
    public array $payload;

    public function __construct(string $paymentUrl, string $reference, string $status, array $payload = [])
    {
        $this->paymentUrl = $paymentUrl;
        $this->reference = $reference;
        $this->status = $status;
        $this->payload = $payload;
    }
}
