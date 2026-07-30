<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Exception;

class ManualController extends PaymentController
{
    public function __construct(array $config)
    {
        parent::__construct('manual', $config);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        $accountIndex = $params['account_index'] ?? 0;
        $accounts = $this->config['accounts'] ?? [];
        
        if (!isset($accounts[$accountIndex])) {
            if (!empty($accounts)) {
                $accountIndex = 0;
            } else {
                throw new Exception("Rekening tujuan tidak tersedia di konfigurasi.");
            }
        }
        
        $account = $accounts[$accountIndex];
        
        return new PaymentResponse(
            '',
            $params['order_id'],
            'PENDING',
            ['account_details' => $account]
        );
    }

    public function prepareTransientData(PaymentResponse $response, array $baseData): array
    {
        $data = $baseData;
        $data['is_manual'] = true;
        if (isset($response->payload['account_details'])) {
            $data['manual_account'] = $response->payload['account_details'];
        }
        return $data;
    }

    public function handleCallback($payload, array $headers = []): array
    {
        return ['status' => 'ignored', 'message' => 'Manual payment has no callback'];
    }
}
