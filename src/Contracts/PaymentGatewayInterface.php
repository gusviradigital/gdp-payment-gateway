<?php

namespace GusviraDigital\GdpPaymentGateway\Contracts;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;

interface PaymentGatewayInterface
{
    /**
     * Membuat transaksi pembayaran baru.
     * 
     * @param array $params Parameter transaksi (amount, customer_details, dll)
     * @return PaymentResponse
     */
    public function createTransaction(array $params): PaymentResponse;
}
