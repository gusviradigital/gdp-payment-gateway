<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Exception;

class DuitkuController extends PaymentController
{
    public function __construct(array $config)
    {
        parent::__construct('duitku', $config);
    }
 
    public function createTransaction(array $params): PaymentResponse
    {
        $duitkuParams = [
            'order_id' => $params['order_id'],
            'amount' => $params['amount'],
            'method' => strtoupper($params['method'] ?? ''),
            'customer_details' => $params['customer_details'] ?? [],
            'description' => 'Payment ' . ($params['description'] ?? ''),
        ];
        
        if (isset($params['return_url'])) {
            $duitkuParams['return_url'] = $params['return_url'];
        }
        if (isset($params['callback_url'])) {
            $duitkuParams['callback_url'] = $params['callback_url'];
        }
        
        return parent::createTransaction($duitkuParams);
    }

    public function prepareTransientData(PaymentResponse $response, array $baseData): array
    {
        $data = $baseData;
        $payload = $response->payload;
        
        if (isset($payload['va_number'])) {
             $data['va_number'] = $payload['va_number'];
        }
        if (isset($payload['qr_string'])) {
             $data['qr_url'] = $payload['qr_string']; 
        }
        if (isset($payload['paymentUrl'])) {
            $data['checkout_url'] = $payload['paymentUrl'];
            $data['payment_url'] = $payload['paymentUrl'];
        }
        
        return $data;
    }

    public function handleCallback($payload, array $headers = []): array
    {
        $params = is_string($payload) ? json_decode($payload, true) : (array)$payload;
        
        $merchantCode = $this->config['merchant_code'] ?? '';
        $apiKey = $this->config['api_key'] ?? '';
        
        $amount = $params['amount'] ?? '';
        $merchantOrderId = $params['merchantOrderId'] ?? '';
        $signature = $params['signature'] ?? '';
        $resultCode = $params['resultCode'] ?? '';

        if (empty($merchantOrderId) || empty($signature)) {
             throw new Exception("Invalid Duitku Notification");
        }

        $expectedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);

        if ($expectedSignature !== $signature) {
            throw new Exception("Invalid Duitku signature");
        }
        
        $status = 'pending';
        if ($resultCode === '00') {
             $status = 'success';
        } else if ($resultCode === '01') {
             $status = 'failed';
        }
        
        return [
            'status' => 'success',
            'transaction_status' => $status,
            'order_id' => $merchantOrderId,
            'raw_response' => $params
        ];
    }
}
