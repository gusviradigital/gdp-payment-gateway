<?php

namespace GusviraDigital\GdpPaymentGateway\Contracts;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;

interface PaymentControllerInterface
{
    /**
     * Handle pembuatan transaksi
     */
    public function createTransaction(array $params): PaymentResponse;

    /**
     * Mempersiapkan data untuk response atau transient
     */
    public function prepareTransientData(PaymentResponse $response, array $baseData): array;

    /**
     * Menangani callback webhook
     * 
     * @param mixed $request Parameter payload dari request
     * @return array array berisi status dan data transaksi
     */
    public function handleCallback($request): array;
}
