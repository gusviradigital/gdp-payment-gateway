<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Exception;

class PaypalController extends PaymentController
{
    public function __construct(array $config)
    {
        parent::__construct('paypal', $config);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        $paypalParams = [
            'order_id' => $params['order_id'],
            'amount' => $params['amount'],
            'description' => $params['description'] ?? 'Payment',
            'customer_details' => $params['customer_details'] ?? [],
        ];

        if (isset($params['return_url'])) {
            $paypalParams['return_url'] = $params['return_url'];
        }
        if (isset($params['cancel_url'])) {
            $paypalParams['cancel_url'] = $params['cancel_url'];
        }
        
        return parent::createTransaction($paypalParams);
    }

    public function prepareTransientData(PaymentResponse $response, array $baseData): array
    {
        $data = $baseData;

        if (isset($response->paymentUrl)) {
            $data['checkout_url'] = $response->paymentUrl;
            $data['payment_url'] = $response->paymentUrl;
        }
        
        if (isset($response->reference)) {
            $data['paypal_order_id'] = $response->reference;
        }

        return $data;
    }

    public function handleCallback($payload, array $headers = []): array
    {
        return ['status' => 'success'];
    }
}
