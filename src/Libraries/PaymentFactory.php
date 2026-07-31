<?php

namespace GusviraDigital\GdpPaymentGateway\Libraries;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentGatewayInterface;
use GusviraDigital\GdpPaymentGateway\Gateways\MidtransGateway;
use GusviraDigital\GdpPaymentGateway\Gateways\XenditGateway;
use GusviraDigital\GdpPaymentGateway\Gateways\TripayGateway;
use GusviraDigital\GdpPaymentGateway\Gateways\DuitkuGateway;
use GusviraDigital\GdpPaymentGateway\Gateways\PaypalGateway;
use Exception;

class PaymentFactory
{
    public static function create(string $provider, array $config): PaymentGatewayInterface
    {
        $provider = strtolower($provider);

        switch ($provider) {
            case 'midtrans':
                return new MidtransGateway(
                    $config['server_key'] ?? null,
                    $config['client_key'] ?? null,
                    'snap',
                    $config['is_production'] ?? null,
                    $config['return_url'] ?? null
                );

            case 'xendit':
                return new XenditGateway(
                    $config['api_key'] ?? null,
                    $config['return_url'] ?? null,
                    $config['mode'] ?? 'invoice'
                );

            case 'tripay':
                return new TripayGateway(
                    $config['api_key'] ?? null,
                    $config['private_key'] ?? null,
                    $config['merchant_code'] ?? null,
                    $config['is_production'] ?? null,
                    $config['return_url'] ?? null,
                    $config['callback_url'] ?? null
                );

            case 'duitku':
                return new DuitkuGateway(
                    $config['merchant_code'] ?? null,
                    $config['api_key'] ?? null,
                    $config['sandbox_mode'] ?? null,
                    $config['return_url'] ?? null,
                    $config['callback_url'] ?? null
                );

            case 'paypal':
                return new PaypalGateway(
                    $config['client_id'] ?? null,
                    $config['client_secret'] ?? null,
                    $config['sandbox_mode'] ?? null,
                    $config['return_url'] ?? null,
                    $config['cancel_url'] ?? null,
                    $config['app_name'] ?? null
                );

            default:
                throw new Exception("Provider pembayaran tidak valid: $provider");
        }
    }
}
