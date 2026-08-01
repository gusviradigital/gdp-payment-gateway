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
        $params = is_string($payload) ? json_decode($payload, true) : (array)$payload;
        
        $eventType = $params['event_type'] ?? '';
        $resource = $params['resource'] ?? [];
        
        $status = 'pending';
        // Status webhook PayPal yang sering ditemui
        if ($eventType === 'PAYMENT.CAPTURE.COMPLETED' || $eventType === 'CHECKOUT.ORDER.APPROVED') {
            $status = 'success';
        } elseif ($eventType === 'PAYMENT.CAPTURE.DENIED' || $eventType === 'PAYMENT.CAPTURE.REFUNDED' || $eventType === 'PAYMENT.CAPTURE.DECLINED') {
            $status = 'failed';
        }
        
        $orderId = '';
        if (isset($resource['custom_id'])) {
            $orderId = $resource['custom_id'];
        } elseif (isset($resource['purchase_units'][0]['custom_id'])) {
            $orderId = $resource['purchase_units'][0]['custom_id'];
        }

        if (empty($orderId)) {
            // Beberapa event mungkin tidak memiliki custom_id langsung di resource
            // Jika ini terjadi, kembalikan saja sukses HTTP agar PayPal tidak mencoba mengirim ulang terus-menerus
            return [
                'status' => 'success',
                'transaction_status' => 'unknown',
                'order_id' => '',
                'raw_response' => $params
            ];
        }

        return [
            'status' => 'success',
            'transaction_status' => $status,
            'order_id' => $orderId,
            'raw_response' => $params
        ];
    }
}
