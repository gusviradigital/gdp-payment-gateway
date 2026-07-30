<?php

namespace GusviraDigital\GdpPaymentGateway\Gateways;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentGatewayInterface;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class MidtransGateway implements PaymentGatewayInterface
{
    private string $serverKey;
    private string $clientKey;
    private string $mode;
    private bool $isProduction;
    private bool $isSanitized;
    private bool $is3ds;

    public function __construct(?string $serverKey = null, ?string $clientKey = null, string $mode = 'snap', ?bool $isProduction = null)
    {
        $this->serverKey = $serverKey ?? '';
        $this->clientKey = $clientKey ?? '';
        $this->mode = $mode;
        $this->isProduction = $isProduction ?? false;
        $this->isSanitized = false;
        $this->is3ds = true;

        $this->configure();
    }

    private function configure(): void
    {
        Config::$serverKey = $this->serverKey;
        Config::$clientKey = $this->clientKey;
        Config::$isProduction = $this->isProduction;
        Config::$isSanitized = $this->isSanitized;
        Config::$is3ds = $this->is3ds;
    }

    public function createTransaction(array $params): PaymentResponse
    {
        try {
            if (empty($params['order_id']) || empty($params['amount'])) {
                throw new Exception("Order ID dan Amount wajib diisi.");
            }

            $transactionDetails = [
                'order_id' => $params['order_id'],
                'gross_amount' => (int) $params['amount'],
            ];

            $payload = [
                'transaction_details' => $transactionDetails,
            ];

            if (!empty($params['customer_details'])) {
                $payload['customer_details'] = $params['customer_details'];
            }

            if (!empty($params['item_details'])) {
                $payload['item_details'] = $params['item_details'];
            }
            
            if (!empty($params['callbacks'])) {
                $payload['callbacks'] = $params['callbacks'];
            }

            $snapTransaction = Snap::createTransaction($payload);

            return new PaymentResponse(
                $snapTransaction->redirect_url,
                $params['order_id'],
                'PENDING',
                (array) $snapTransaction
            );

        } catch (Exception $e) {
            error_log("Midtrans Error: " . $e->getMessage());
            throw new Exception("Gagal membuat transaksi Midtrans: " . $e->getMessage());
        }
    }
}
