<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Exception;

class TripayController extends PaymentController
{
    public function __construct(array $config)
    {
        parent::__construct('tripay', $config);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        $tripayParams = [
            'method' => $params['method'] ?? 'BRIVA',
            'order_id' => $params['order_id'],
            'amount' => $params['amount'],
            'customer_details' => $params['customer_details'] ?? [],
            'item_details' => $params['item_details'] ?? [
                [
                    'name' => 'Payment ' . ($params['description'] ?? ''),
                    'price' => $params['amount'],
                    'quantity' => 1
                ]
            ],
            'expired_time' => time() + (24 * 60 * 60), 
        ];

        if (isset($params['return_url'])) {
            $tripayParams['return_url'] = $params['return_url'];
        }
        if (isset($params['callback_url'])) {
            $tripayParams['callback_url'] = $params['callback_url'];
        }
        
        return parent::createTransaction($tripayParams);
    }

    public function prepareTransientData(PaymentResponse $response, array $baseData): array
    {
        $data = $baseData;
        $payload = $response->payload;

        if (isset($payload['pay_code'])) {
            $data['payment_code'] = $payload['pay_code'];
        }
        if (isset($payload['qr_url'])) {
            $data['qr_url'] = $payload['qr_url'];
        }
        if (isset($payload['checkout_url'])) {
            $data['checkout_url'] = $payload['checkout_url'];
            $data['payment_url'] = $payload['checkout_url'];
        }
        
        return $data;
    }

    public function handleCallback($payload, array $headers = []): array
    {
        $json = is_string($payload) ? $payload : json_encode($payload);
        $params = json_decode($json, true);
        
        if (empty($params)) {
             throw new Exception("Invalid Tripay Notification");
        }
        
        $callbackSignature = $headers['x-callback-signature'] ?? '';
        $privateKey = $this->config['private_key'] ?? '';
        
        $expectedSignature = hash_hmac('sha256', $json, $privateKey);

        if (empty($callbackSignature) || $expectedSignature !== $callbackSignature) {
            throw new Exception("Invalid Tripay callback signature");
        }

        $tripay_status = $params['status'] ?? '';
        $order_id = $params['merchant_ref'] ?? '';
        
        $status = 'pending';
        if ($tripay_status === 'PAID') {
             $status = 'success';
        } else if ($tripay_status === 'EXPIRED' || $tripay_status === 'FAILED') {
             $status = 'failed';
        }
        
        return [
            'status' => 'success',
            'transaction_status' => $status,
            'order_id' => $order_id,
            'raw_response' => $params
        ];
    }
}
