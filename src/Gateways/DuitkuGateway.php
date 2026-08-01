<?php

namespace GusviraDigital\GdpPaymentGateway\Gateways;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentGatewayInterface;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Duitku\Api;
use Duitku\Pop;
use Duitku\Config;
use Exception;

class DuitkuGateway implements PaymentGatewayInterface
{
    private string $merchantCode;
    private string $apiKey;
    private bool $sandboxMode;
    private Config $duitkuConfig;
    private string $returnUrl;
    private string $callbackUrl;
    private string $mode;

    public function __construct(
        ?string $merchantCode = null, 
        ?string $apiKey = null, 
        ?bool $sandboxMode = null, 
        ?string $returnUrl = null, 
        ?string $callbackUrl = null,
        ?string $mode = 'v2'
    ) {
        $this->merchantCode = $merchantCode ?? '';
        $this->apiKey = $apiKey ?? '';
        $this->sandboxMode = $sandboxMode ?? false;
        $this->returnUrl = $returnUrl ?? '';
        $this->callbackUrl = $callbackUrl ?? '';
        $this->mode = strtolower($mode ?? 'v2');

        $this->duitkuConfig = new Config(
            $this->apiKey,
            $this->merchantCode,
            $this->sandboxMode,
            true, 
            false  
        );
    }
 
    public function createTransaction(array $params): PaymentResponse
    {
        $duitkuParams = [
            'paymentAmount'     => $params['amount'],
            'merchantOrderId'   => $params['order_id'],
            'productDetails'    => $params['description'] ?? 'Payment',
            'email'             => $params['customer_details']['email'] ?? '',
            'phoneNumber'       => $params['customer_details']['phone'] ?? '',
            'customerVaName'    => ($params['customer_details']['first_name'] ?? '') . ' ' . ($params['customer_details']['last_name'] ?? ''),
            'callbackUrl'       => $params['callback_url'] ?? $this->callbackUrl,
            'returnUrl'         => $params['return_url'] ?? $this->returnUrl,
            'expiryPeriod'      => 1440,
            'paymentMethod'     => $params['method'] ?? '',
        ];

        $result = $this->createInvoice($duitkuParams);

        if (!$result['success'] && isset($result['statusMessage'])) {
             throw new Exception("Duitku Error: " . $result['statusMessage']);
        }
        
        return new PaymentResponse(
            $result['paymentUrl'] ?? '#',
            $result['reference'] ?? $params['order_id'],
            'PENDING',
            $result
        );
    }

    private function createInvoice(array $params): array
    {
        try {
            $defaultParams = [
                'paymentAmount'     => 0,
                'paymentMethod'     => '', 
                'merchantOrderId'   => time(),
                'productDetails'    => 'Payment',
                'email'             => '',
                'phoneNumber'       => '',
                'customerVaName'    => '',
                'callbackUrl'       => $this->callbackUrl,
                'returnUrl'         => $this->returnUrl,
                'expiryPeriod'      => 1440
            ];

            $finalParams = array_merge($defaultParams, $params);
            
            if ($this->mode === 'pop') {
                if (!isset($finalParams['itemDetails'])) {
                    $finalParams['itemDetails'] = [[
                        'name' => $finalParams['productDetails'],
                        'price' => $finalParams['paymentAmount'],
                        'quantity' => 1
                    ]];
                }
                $response = Pop::createInvoice($finalParams, $this->duitkuConfig);
            } else {
                $finalParams['paymentMethod'] = $finalParams['paymentMethod'] ?? '';
                $response = Api::createInvoice($finalParams, $this->duitkuConfig);
            }

            $result = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON response from Duitku: ' . $response);
            }

            if (isset($result['paymentUrl'])) {
                 $result['success'] = true;
            } else {
                 $result['success'] = false;
            }

            return $result;

        } catch (Exception $e) {
            return [
                'success' => false,
                'statusCode' => '500',
                'statusMessage' => $e->getMessage()
            ];
        }
    }
}
