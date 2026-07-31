<?php

use GusviraDigital\GdpPaymentGateway\Support\PaymentChannelRegistry;

if (!function_exists('gdp_payment_get_active_channels')) {
    /**
     * @deprecated Gunakan class PaymentChannelRegistry secara langsung.
     */
    function gdp_payment_get_active_channels(array $config, string $baseImgUrl = '/assets/images/payment/'): object
    {
        $registry = new PaymentChannelRegistry($config, $baseImgUrl);
        return $registry->getActiveChannels();
    }
}
