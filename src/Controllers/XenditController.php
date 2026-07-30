<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Exception;

class XenditController extends PaymentController
{
    public function __construct(array $config)
    {
        parent::__construct('xendit', $config);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        $xenditParams = [
            'order_id' => $params['order_id'],
            'amount' => $params['amount'],
            'customer_details' => $params['customer_details'] ?? [],
            'description' => 'Payment ' . ($params['description'] ?? ''),
        ];
        
        if (isset($params['method']) && $params['method'] !== 'all' && $params['method'] !== 'xendit') {
             $methodCode = strtoupper($params['method']);
             
             $aliases = [
                  'BCA_VIRTUAL_ACCOUNT' => 'BCA',
                  'BNI_VIRTUAL_ACCOUNT' => 'BNI',
                  'BRI_VIRTUAL_ACCOUNT' => 'BRI',
                  'MANDIRI_VIRTUAL_ACCOUNT' => 'MANDIRI',
                  'PERMATA_VIRTUAL_ACCOUNT' => 'PERMATA',
                  'BSI_VIRTUAL_ACCOUNT' => 'BSI',
                  'BJB_VIRTUAL_ACCOUNT' => 'BJB',
                  'CIMB_VIRTUAL_ACCOUNT' => 'CIMB',
                  'BNC_VIRTUAL_ACCOUNT' => 'BNC',
                  'HANA_VIRTUAL_ACCOUNT' => 'HANA',
                  'MUAMALAT_VIRTUAL_ACCOUNT' => 'MUAMALAT',
                  'BSS_VIRTUAL_ACCOUNT' => 'SAHABAT_SAMPOERNA',
                  'CARDS' => 'CREDIT_CARD',
                  'BRI_DIRECT_DEBIT' => 'DD_BRI',
                  'MANDIRIVA' => 'MANDIRI',
                  'BNIVA' => 'BNI',
                  'BRIVA' => 'BRI',
                  'PERMATAVA' => 'PERMATA',
                  'BCAVA' => 'BCA',
                  'CIMBVA' => 'CIMB',
                  'BSIVA' => 'BSI',
              ];
             
             if (isset($aliases[$methodCode])) {
                 $methodCode = $aliases[$methodCode];
             }

             $xenditParams['payment_methods'] = [$methodCode];
        }

        if (isset($params['return_url'])) {
            $xenditParams['return_url'] = $params['return_url'];
        }

        try {
            return parent::createTransaction($xenditParams);
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'payment method choices did not match') !== false) {
                throw new Exception("Metode pembayaran tidak tersedia untuk akun ini.");
            }
            throw $e;
        }
    }

    public function handleCallback($payload, array $headers = []): array
    {
        $params = is_string($payload) ? json_decode($payload, true) : (array)$payload;

        $callbackToken = $headers['x-callback-token'] ?? '';
        $expectedToken = $this->config['callback_token'] ?? '';

        if (!empty($expectedToken) && $callbackToken !== $expectedToken) {
            throw new Exception("Invalid Xendit callback token.");
        }

        $order_id = $params['external_id'] ?? '';
        $xendit_status = $params['status'] ?? '';

        if (empty($order_id)) {
            throw new Exception("Invalid Request: No External ID");
        }

        $status = 'pending';
        if ($xendit_status === 'PAID' || $xendit_status === 'SETTLED') {
            $status = 'success';
        } else if ($xendit_status === 'EXPIRED') {
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
