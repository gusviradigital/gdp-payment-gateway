<?php

namespace GusviraDigital\GdpPaymentGateway\Gateways;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentGatewayInterface;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use Exception;

class XenditGateway implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $returnUrl;
    private string $mode;

    public function __construct(?string $apiKey = null, ?string $returnUrl = null, string $mode = 'invoice')
    {
        $this->apiKey = $apiKey ?? '';
        $this->returnUrl = $returnUrl ?? '';
        $this->mode = $mode;
        $this->configure();
    }

    private function configure(): void
    {
        Configuration::setXenditKey($this->apiKey);
    }

    private function createCoreApiTransaction(array $params): PaymentResponse
    {
        try {
            $apiInstance = new \Xendit\PaymentRequest\PaymentRequestApi();

            $methodCode = $params['payment_methods'][0] ?? '';
            $type = 'VIRTUAL_ACCOUNT'; // Default
            
            // Tentukan tipe payment method Xendit
            $ewallets = ['GOPAY', 'OVO', 'DANA', 'LINKAJA', 'SHOPEEPAY', 'ASTRAPAY'];
            $qris = ['QRIS'];
            $retail = ['ALFAMART', 'INDOMARET'];
            $cards = ['CREDIT_CARD'];
            $paylaters = ['KREDIVO', 'AKULAKU', 'UANGME', 'INDODANA'];
            $directDebits = ['BRI_DIRECT_DEBIT', 'DD_BRI', 'DD_MANDIRI', 'BCA_KLIKPAY', 'BRI_EPAY', 'JENIUSPAY'];
            
            if (in_array($methodCode, $ewallets)) $type = 'EWALLET';
            elseif (in_array($methodCode, $qris)) $type = 'QR_CODE';
            elseif (in_array($methodCode, $retail)) $type = 'OVER_THE_COUNTER';
            elseif (in_array($methodCode, $cards)) $type = 'CARD';
            elseif (in_array($methodCode, $paylaters)) $type = 'PAYLATER';
            elseif (in_array($methodCode, $directDebits)) $type = 'DIRECT_DEBIT';

            $paymentMethod = [
                'type' => $type,
                'reusability' => 'ONE_TIME_USE',
                'reference_id' => (string) $params['order_id']
            ];

            if ($type === 'VIRTUAL_ACCOUNT') {
                $paymentMethod['virtual_account'] = [
                    'channel_code' => $methodCode,
                    'channel_properties' => [
                        'customer_name' => $params['customer_details']['first_name'] ?? 'Donatur'
                    ]
                ];
            } elseif ($type === 'EWALLET') {
                $paymentMethod['ewallet'] = [
                    'channel_code' => $methodCode,
                    'channel_properties' => [
                        'success_return_url' => $params['return_url'] ?? $this->returnUrl,
                        'failure_return_url' => $params['return_url'] ?? $this->returnUrl
                    ]
                ];
            } elseif ($type === 'QR_CODE') {
                $paymentMethod['qr_code'] = [
                    'channel_code' => $methodCode,
                ];
            } elseif ($type === 'OVER_THE_COUNTER') {
                $paymentMethod['over_the_counter'] = [
                    'channel_code' => $methodCode,
                    'channel_properties' => [
                        'customer_name' => $params['customer_details']['first_name'] ?? 'Donatur'
                    ]
                ];
            } elseif ($type === 'CARD') {
                if (empty($params['token_id'])) {
                    throw new Exception("Transaksi Kartu Kredit / Debit membutuhkan token_id. Pastikan Xendit.js terpasang pada frontend.");
                }
                $paymentMethod['card'] = [
                    'channel_properties' => [
                        'success_return_url' => $params['return_url'] ?? $this->returnUrl,
                        'failure_return_url' => $params['return_url'] ?? $this->returnUrl
                    ],
                    'card_information' => [
                        'token_id' => $params['token_id']
                    ]
                ];
            } elseif ($type === 'PAYLATER') {
                $paymentMethod['paylater'] = [
                    'channel_code' => $methodCode,
                    'channel_properties' => [
                        'success_return_url' => $params['return_url'] ?? $this->returnUrl,
                        'failure_return_url' => $params['return_url'] ?? $this->returnUrl
                    ]
                ];
            } elseif ($type === 'DIRECT_DEBIT') {
                $finalChannelCode = $methodCode;
                if (in_array($methodCode, ['BRI_DIRECT_DEBIT', 'DD_BRI'])) $finalChannelCode = 'BRI';
                if (in_array($methodCode, ['MANDIRI_DIRECT_DEBIT', 'DD_MANDIRI'])) $finalChannelCode = 'MANDIRI';
                $channelProps = [
                    'success_return_url' => $params['return_url'] ?? $this->returnUrl,
                    'failure_return_url' => $params['return_url'] ?? $this->returnUrl,
                    'email' => $params['customer_details']['email'] ?? 'donatur@example.com',
                    'mobile_number' => $params['customer_details']['phone'] ?? '+6281234567890'
                ];
                
                if ($finalChannelCode === 'BRI') {
                    if (empty($params['card_last_four']) || empty($params['card_expiry'])) {
                        throw new Exception("BRI Direct Debit membutuhkan 4 digit terakhir kartu dan masa berlaku (MM/YY).");
                    }
                    $channelProps['card_last_four'] = $params['card_last_four'];
                    $channelProps['card_expiry'] = $params['card_expiry'];
                }

                $paymentMethod['direct_debit'] = [
                    'channel_code' => $finalChannelCode,
                    'channel_properties' => $channelProps
                ];
            }

            $requestParamsArray = [
                'reference_id' => (string) $params['order_id'],
                'amount' => (float) $params['amount'],
                'currency' => 'IDR',
                'payment_method' => $paymentMethod
            ];
            
            // Tambahkan data customer (Wajib untuk Direct Debit, Paylater, dll)
            $requestParamsArray['customer'] = [
                'reference_id' => 'cust_' . $params['order_id'],
                'type' => 'INDIVIDUAL',
                'individual_detail' => [
                    'given_names' => $params['customer_details']['first_name'] ?? 'Donatur'
                ]
            ];
            if (!empty($params['customer_details']['email'])) {
                $requestParamsArray['customer']['email'] = $params['customer_details']['email'];
            }
            if (!empty($params['customer_details']['phone'])) {
                $requestParamsArray['customer']['mobile_number'] = $params['customer_details']['phone'];
            }

            $requestParams = new \Xendit\PaymentRequest\PaymentRequestParameters($requestParamsArray);

            // Call SDK: $idempotency_key, $for_user_id, $with_split_rule, $payment_request_parameters
            $result = $apiInstance->createPaymentRequest(null, null, null, $requestParams);
            $resultArr = json_decode(json_encode($result), true);

            // Normalize for frontend (mock Midtrans format)
            if (isset($resultArr['payment_method']['virtual_account']['channel_properties']['virtual_account_number'])) {
                $resultArr['va_numbers'] = [
                    [
                        'bank' => strtolower($resultArr['payment_method']['virtual_account']['channel_code'] ?? ''),
                        'va_number' => $resultArr['payment_method']['virtual_account']['channel_properties']['virtual_account_number']
                    ]
                ];
            }

            $paymentUrl = null;
            if (isset($resultArr['actions'])) {
                foreach ($resultArr['actions'] as $action) {
                    if (isset($action['action']) && $action['action'] === 'QR_CODE') {
                        $resultArr['actions'][] = [
                            'name' => 'generate-qr-code',
                            'url' => $action['url'] ?? ''
                        ];
                        if (!$paymentUrl) $paymentUrl = $action['url'] ?? '';
                    } elseif (isset($action['action']) && in_array($action['action'], ['MOBILE_WEB_CHECKOUT', 'MOBILE_DEEPLINK', 'DESKTOP_WEB_CHECKOUT', 'WEB_CHECKOUT'])) {
                        $resultArr['actions'][] = [
                            'name' => 'deeplink-redirect',
                            'url' => $action['url'] ?? ''
                        ];
                        $paymentUrl = $action['url'] ?? '';
                    }
                }
            }

            return new PaymentResponse(
                $paymentUrl ?? '', // Akan ada URL untuk QRIS/E-Wallet atau string kosong untuk VA
                $result->getId(),
                $result->getStatus(),
                $resultArr
            );

        } catch (Exception $e) {
            throw new Exception("Gagal membuat transaksi Xendit Core API: " . $e->getMessage());
        }
    }

    public function createTransaction(array $params): PaymentResponse
    {
        if ($this->mode === 'coreapi') {
            return $this->createCoreApiTransaction($params);
        }
        try {
            $apiInstance = new InvoiceApi();
            
            $invoiceArgs = [
                'external_id' => (string) $params['order_id'],
                'amount' => (float) $params['amount'],
                'description' => $params['description'] ?? 'Payment for Order ' . $params['order_id'],
                'invoice_duration' => 86400, 
                'currency' => 'IDR'
            ];

            if (!empty($params['customer_details']['email'])) {
                $invoiceArgs['payer_email'] = $params['customer_details']['email'];
            }

            $returnUrl = $params['return_url'] ?? $this->returnUrl;
            if (!empty($returnUrl)) {
                $invoiceArgs['success_redirect_url'] = $returnUrl;
                $invoiceArgs['failure_redirect_url'] = $returnUrl;
            }

            $createInvoiceRequest = new CreateInvoiceRequest($invoiceArgs);

            if (!empty($params['payment_methods']) && is_array($params['payment_methods'])) {
                $createInvoiceRequest->setPaymentMethods($params['payment_methods']);
            }
            
            $result = $apiInstance->createInvoice($createInvoiceRequest);

            return new PaymentResponse(
                $result->getInvoiceUrl(),
                $result->getExternalId(),
                $result->getStatus(),
                json_decode(json_encode($result), true)
            );

        } catch (Exception $e) {
            throw new Exception("Gagal membuat transaksi Xendit: " . $e->getMessage());
        }
    }

    public function checkStatus(string $orderId)
    {
        try {
            $apiInstance = new InvoiceApi();
            $result = $apiInstance->getInvoices(null, $orderId);
            
            if (count($result) > 0) {
                return $result[0];
            }
            
            return null;

        } catch (Exception $e) {
            return null;
        }
    }
}
