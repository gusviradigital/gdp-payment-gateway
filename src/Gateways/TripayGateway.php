<?php

namespace GusviraDigital\GdpPaymentGateway\Gateways;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentGatewayInterface;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use ZerosDev\TriPay\Client;
use ZerosDev\TriPay\Transaction;
use ZerosDev\TriPay\Support\Constant;
use Exception;

class TripayGateway implements PaymentGatewayInterface
{
    private string $apiKey;
    private string $privateKey;
    private string $merchantCode;
    private bool $isProduction;
    private string $returnUrl;
    private string $callbackUrl;

    public function __construct(?string $apiKey = null, ?string $privateKey = null, ?string $merchantCode = null, ?bool $isProduction = null, ?string $returnUrl = null, ?string $callbackUrl = null)
    {
        $this->apiKey = $apiKey ?? '';
        $this->privateKey = $privateKey ?? '';
        $this->merchantCode = $merchantCode ?? '';
        $this->isProduction = $isProduction ?? false;
        $this->returnUrl = $returnUrl ?? '';
        $this->callbackUrl = $callbackUrl ?? '';
    }

    private function getClient(): Client
    {
        return new Client([
            'api_key' => $this->apiKey,
            'private_key' => $this->privateKey,
            'merchant_code' => $this->merchantCode,
            'mode' => $this->isProduction ? Constant::MODE_PRODUCTION : Constant::MODE_DEVELOPMENT,
        ]);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        try {
            $client = $this->getClient();
            $transaction = new Transaction($client);

            if (!empty($params['item_details']) && is_array($params['item_details'])) {
                foreach ($params['item_details'] as $item) {
                    $transaction->addOrderItem(
                        $item['name'] ?? 'Item',
                        $item['price'] ?? 0,
                        $item['quantity'] ?? 1
                    );
                }
            } else {
                 $transaction->addOrderItem(
                    'Payment',
                    $params['amount'],
                    1
                );
            }

            $payload = [
                'method' => $params['method'] ?? 'BRIVA',
                'merchant_ref' => $params['order_id'],
                'customer_name' => ($params['customer_details']['first_name'] ?? 'Guest') . ' ' . ($params['customer_details']['last_name'] ?? ''),
                'customer_email' => $params['customer_details']['email'] ?? 'noreply@example.com',
                'customer_phone' => $params['customer_details']['phone'] ?? '080000000000',
                'expired_time' => isset($params['expired_time']) ? $params['expired_time'] : (time() + (24 * 60 * 60)),
                'return_url' => $params['return_url'] ?? $this->returnUrl,
                'callback_url' => $params['callback_url'] ?? $this->callbackUrl
            ];

            $result = $transaction->create($payload);
            $responseBody = json_decode($result->getBody()->getContents(), true);

            if (!$responseBody['success']) {
                throw new Exception("Tripay Error: " . ($responseBody['message'] ?? 'Unknown error'));
            }

            $data = $responseBody['data'];

            return new PaymentResponse(
                $data['checkout_url'],
                $data['reference'],
                'PENDING',
                $data
            );

        } catch (Exception $e) {
            throw new Exception("Gagal membuat transaksi Tripay: " . $e->getMessage());
        }
    }
}
