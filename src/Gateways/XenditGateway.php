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

    public function __construct(?string $apiKey = null, ?string $returnUrl = null)
    {
        $this->apiKey = $apiKey ?? '';
        $this->returnUrl = $returnUrl ?? '';
        $this->configure();
    }

    private function configure(): void
    {
        Configuration::setXenditKey($this->apiKey);
    }

    public function createTransaction(array $params): PaymentResponse
    {
        try {
            $apiInstance = new InvoiceApi();
            
            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => $params['order_id'],
                'amount' => $params['amount'],
                'payer_email' => $params['customer_details']['email'] ?? null,
                'description' => $params['description'] ?? 'Payment for Order ' . $params['order_id'],
                'invoice_duration' => 86400, 
                'currency' => 'IDR',
                'success_redirect_url' => $params['return_url'] ?? $this->returnUrl,
                'failure_redirect_url' => $params['return_url'] ?? $this->returnUrl
            ]);

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
