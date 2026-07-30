<?php

namespace GusviraDigital\GdpPaymentGateway\Controllers;

use Exception;

class CallbackController
{
    /**
     * Dispatch webhook payload ke controller yang sesuai
     */
    public function handle(string $provider, $payload, array $config, array $headers = []): array
    {
        try {
            switch ($provider) {
                case 'midtrans':
                    $controller = new MidtransController($config);
                    break;
                case 'xendit':
                    $controller = new XenditController($config);
                    break;
                case 'tripay':
                    $controller = new TripayController($config);
                    break;
                case 'duitku':
                    $controller = new DuitkuController($config);
                    break;
                case 'paypal':
                    $controller = new PaypalController($config);
                    break;
                case 'manual':
                    $controller = new ManualController($config);
                    break;
                default:
                    throw new Exception("Provider webhook tidak valid: {$provider}");
            }

            return $controller->handleCallback($payload, $headers);
            
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
}
