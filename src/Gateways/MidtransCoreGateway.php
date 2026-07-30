<?php

namespace GusviraDigital\GdpPaymentGateway\Gateways;

use Midtrans\Config;
use Midtrans\CoreApi;
use Midtrans\Transaction;
use Exception;

class MidtransCoreGateway
{
    private string $serverKey;
    private string $clientKey;
    private bool $isProduction;
    private bool $isSanitized;
    private bool $is3ds;

    public function __construct(?string $serverKey = null, ?string $clientKey = null, ?bool $isProduction = null)
    {
        $this->serverKey = $serverKey ?? '';
        $this->clientKey = $clientKey ?? '';
        $this->isProduction = $isProduction ?? false;
        $this->isSanitized = true;
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

    public function charge(array $params)
    {
        try {
            return CoreApi::charge($params);
        } catch (Exception $e) {
            throw new Exception("Midtrans Charge Failed: " . $e->getMessage());
        }
    }

    public function status(string $orderId)
    {
        try {
            return Transaction::status($orderId);
        } catch (Exception $e) {
            throw new Exception("Midtrans Status Check Failed: " . $e->getMessage());
        }
    }

    public function cancel(string $orderId)
    {
        try {
            return Transaction::cancel($orderId);
        } catch (Exception $e) {
            throw new Exception("Midtrans Cancel Failed: " . $e->getMessage());
        }
    }

    public function refund(string $orderId, ?int $amount = null, string $reason = '')
    {
        $params = ['reason' => $reason];
        if ($amount !== null) {
            $params['amount'] = $amount;
        }

        try {
            return Transaction::refund($orderId, $params);
        } catch (Exception $e) {
            throw new Exception("Midtrans Refund Failed: " . $e->getMessage());
        }
    }
}
