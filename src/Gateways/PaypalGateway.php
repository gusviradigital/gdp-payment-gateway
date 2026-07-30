<?php

namespace GusviraDigital\GdpPaymentGateway\Gateways;

use GusviraDigital\GdpPaymentGateway\Contracts\PaymentGatewayInterface;
use GusviraDigital\GdpPaymentGateway\Libraries\PaymentResponse;
use GusviraDigital\GdpPaymentGateway\Utilities\PaymentUtils;
use PaypalServerSdkLib\Environment;
use PaypalServerSdkLib\Authentication\ClientCredentialsAuthCredentialsBuilder;
use PaypalServerSdkLib\PaypalServerSdkClientBuilder;
use PaypalServerSdkLib\Models\Builders\OrderRequestBuilder;
use PaypalServerSdkLib\Models\CheckoutPaymentIntent;
use PaypalServerSdkLib\Models\Builders\PurchaseUnitRequestBuilder;
use PaypalServerSdkLib\Models\Builders\AmountWithBreakdownBuilder;
use PaypalServerSdkLib\Models\Builders\OrderApplicationContextBuilder;
use Exception;

class PaypalGateway implements PaymentGatewayInterface
{
    private $client;
    private string $returnUrl;
    private string $cancelUrl;
    private string $appName;

    public function __construct(?string $clientId = null, ?string $clientSecret = null, ?bool $isSandbox = null, ?string $returnUrl = null, ?string $cancelUrl = null, ?string $appName = null)
    {
        $clientId = $clientId ?? '';
        $clientSecret = $clientSecret ?? '';
        $isSandbox = $isSandbox ?? false;
        $this->returnUrl = $returnUrl ?? '';
        $this->cancelUrl = $cancelUrl ?? '';
        $this->appName = $appName ?? 'Payment Gateway';

        if (empty($clientId) || empty($clientSecret)) {
            throw new Exception("Kredensial PayPal (Client ID / Secret) belum dikonfigurasi.");
        }

        $environment = $isSandbox ? Environment::SANDBOX : Environment::PRODUCTION;

        $this->client = PaypalServerSdkClientBuilder::init()
            ->clientCredentialsAuthCredentials(
                ClientCredentialsAuthCredentialsBuilder::init($clientId, $clientSecret)
            )
            ->environment($environment)
            ->build();
    }

    public function createTransaction(array $params): PaymentResponse
    {
        try {
            $exchangeRate = PaymentUtils::getUsdExchangeRate();
            $idrAmount = (float) $params['amount'];
            $usdAmount = round($idrAmount / $exchangeRate, 2);

            if ($usdAmount <= 0) {
                $usdAmount = 0.01;
            }

            $returnUrl = $params['return_url'] ?? $this->returnUrl;
            $cancelUrl = $params['cancel_url'] ?? $this->cancelUrl;

            $ordersController = $this->client->getOrdersController();

            $applicationContext = OrderApplicationContextBuilder::init()
                ->cancelUrl($cancelUrl)
                ->returnUrl($returnUrl)
                ->brandName($this->appName)
                ->userAction("PAY_NOW")
                ->build();

            $amountBreakdown = AmountWithBreakdownBuilder::init('USD', (string) $usdAmount)->build();

            $purchaseUnit = PurchaseUnitRequestBuilder::init($amountBreakdown)
                ->referenceId($params['order_id'])
                ->customId($params['order_id'])
                ->description($params['description'] ?? 'Payment')
                ->build();

            $orderRequest = OrderRequestBuilder::init(CheckoutPaymentIntent::CAPTURE, [$purchaseUnit])
                ->applicationContext($applicationContext)
                ->build();

            $collect = [
                'body' => $orderRequest,
                'prefer' => 'return=representation'
            ];

            $apiResponse = $ordersController->createOrder($collect);

            $statusCode = $apiResponse->getStatusCode();
            if ($statusCode !== 201 && $statusCode !== 200) {
                throw new Exception("Gagal membuat Order PayPal. Status code: " . $statusCode);
            }

            $order = $apiResponse->getResult();
            
            $checkoutUrl = '';
            $links = $order->getLinks();
            if ($links) {
                foreach ($links as $link) {
                    if ($link->getRel() === 'approve') {
                        $checkoutUrl = $link->getHref();
                        break;
                    }
                }
            }

            if (empty($checkoutUrl)) {
                throw new Exception("Tidak dapat menemukan URL Approve PayPal.");
            }

            $orderArray = method_exists($order, 'jsonSerialize') ? $order->jsonSerialize() : [];

            return new PaymentResponse(
                $checkoutUrl,
                $order->getId(), 
                'PENDING',
                (array) $orderArray
            );

        } catch (Exception $e) {
            throw new Exception("Gagal membuat transaksi PayPal: " . $e->getMessage());
        }
    }
}
