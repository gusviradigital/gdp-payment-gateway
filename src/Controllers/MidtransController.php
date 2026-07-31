<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use GusviraDigital\GdpPaymentGateway\Gateways\MidtransCoreGateway;
use GusviraDigital\GdpPaymentGateway\Utilities\SignatureVerifier;
use Exception;

class MidtransController extends PaymentController
{
    public function __construct(array $config)
    {
        parent::__construct('midtrans', $config);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        $method = $params['method'] ?? 'snap';

        if ($method === 'snap') {
            return parent::createTransaction($params);
        }

        $core = new MidtransCoreGateway(
            $this->config['server_key'] ?? null,
            $this->config['client_key'] ?? null,
            $this->config['is_production'] ?? null
        );
        
        $program_name = $params['description'] ?? 'Payment';
        $order_id = $params['order_id'];
        $amount = $params['amount'];

        $chargeParams = [
            'payment_type' => '',
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => $amount,
            ],
        ];

        switch ($method) {
            case 'bca':
            case 'bni':
            case 'bri':
            case 'permata':
            case 'cimb':
            case 'cimb_niaga':
            case 'danamon':
            case 'seabank':
            case 'bsi': // BSI Virtual Account
            case 'other_va': // Other Banks
                $bank_code = $method;
                
                // Normalisasi kode bank Midtrans
                if ($method === 'cimb_niaga') $bank_code = 'cimb';
                // Midtrans menggunakan BNI (atau Permata/Mandiri) untuk menerima jaringan bank lain (ALTO/Prima/ATM Bersama)
                if ($method === 'other_va') $bank_code = 'bni'; 
                
                $chargeParams['payment_type'] = ($method === 'permata') ? 'permata' : 'bank_transfer';
                if ($method !== 'permata') {
                    $chargeParams['bank_transfer'] = ['bank' => $bank_code];
                }
                break;

            case 'echannel':
                $chargeParams['payment_type'] = 'echannel';
                $chargeParams['echannel'] = [
                    'bill_info1' => 'Payment:' . substr($program_name, 0, 10),
                    'bill_info2' => 'Payment',
                ];
                break;

            case 'gopay':
                $chargeParams['payment_type'] = 'gopay';
                $chargeParams['gopay'] = [
                    'enable_callback' => true,
                    'callback_url' => $params['return_url'] ?? ($this->config['return_url'] ?? '')
                ];
                break;

            case 'shopeepay':
                $chargeParams['payment_type'] = 'shopeepay';
                $chargeParams['shopeepay'] = [
                    'callback_url' => $params['return_url'] ?? ($this->config['return_url'] ?? '')
                ];
                break;

            case 'ovo':
                $chargeParams['payment_type'] = 'ovo';
                // OVO pada Core API umumnya membutuhkan nomor HP dari objek OVO
                $phone = $params['phone'] ?? ($params['customer_details']['phone'] ?? '');
                if (!empty($phone)) {
                    $chargeParams['ovo'] = ['phone_number' => $phone];
                }
                break;

            case 'credit_card':
            case 'amex':
                $chargeParams['payment_type'] = 'credit_card';
                // Credit Card via Core API WAJIB membutuhkan token_id yang dikirim dari frontend (Midtrans.js)
                if (!empty($params['token_id'])) {
                    $chargeParams['credit_card'] = [
                        'token_id' => $params['token_id'],
                        'authentication' => true, // Mengaktifkan 3D Secure
                    ];
                }
                break;

            case 'dana':
                $chargeParams['payment_type'] = 'dana';
                break;

            case 'qris':
            case 'gopay_dynamic_qris':
            case 'gopay_static_qris':
                $chargeParams['payment_type'] = 'qris';
                
                // Mengambil acquirer secara dinamis dari $params atau $config
                $acquirer = $params['qris_acquirer'] ?? ($this->config['qris_acquirer'] ?? null);
                
                // Jika acquirer di-set, tambahkan ke payload
                if (!empty($acquirer)) {
                    $chargeParams['qris'] = ['acquirer' => $acquirer];
                }
                break;
            
            case 'akulaku':
                $chargeParams['payment_type'] = 'akulaku';
                break;
                
            case 'kredivo':
                $chargeParams['payment_type'] = 'kredivo';
                break;

            case 'indomaret':
            case 'alfamart':
                $chargeParams['payment_type'] = 'cstore';
                $chargeParams['cstore'] = [
                    'store' => $method,
                    'message' => 'Payment ' . substr($program_name, 0, 20),
                ];
                break;

            default:
                throw new Exception("Metode pembayaran Midtrans tidak dikenali: $method");
        }

        $result = $core->charge($chargeParams);
        $paymentUrl = '';
        $reference = $order_id;
        $status = $result->transaction_status ?? 'pending';
        
        if (isset($result->actions)) {
            foreach ($result->actions as $action) {
                if ($action->name === 'deeplink-redirect' || $action->name === 'generate-qr-code') {
                    if ($action->name === 'deeplink-redirect') {
                        $paymentUrl = $action->url;
                    } elseif (empty($paymentUrl)) {
                        $paymentUrl = $action->url;
                    }
                }
            }
        }
        
        return new PaymentResponse($paymentUrl, $reference, $status, (array) $result);
    }

    public function handleCallback($payload, array $headers = []): array
    {
        $params = is_string($payload) ? json_decode($payload, true) : (array)$payload;

        $order_id = $params['order_id'] ?? '';
        $status_code = $params['status_code'] ?? '';
        $gross_amount = $params['gross_amount'] ?? '';
        $signature_key = $params['signature_key'] ?? '';
        $transaction_status = $params['transaction_status'] ?? '';

        if (empty($order_id) || empty($signature_key)) {
            throw new Exception("Invalid Midtrans Notification Payload");
        }

        $serverKey = $this->config['server_key'] ?? '';
        
        if (!SignatureVerifier::verifyMidtrans($order_id, $status_code, $gross_amount, $serverKey, $signature_key)) {
            throw new Exception("Midtrans Invalid Signature");
        }

        $status = 'pending';
        if ($transaction_status === 'capture' || $transaction_status === 'settlement') {
            $status = 'success';
        } else if ($transaction_status === 'cancel' || $transaction_status === 'deny' || $transaction_status === 'expire') {
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
