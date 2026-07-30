<?php

namespace GusviraDigital\GdpPaymentGateway\Utilities;

use GusviraDigital\GdpPaymentGateway\Support\ConfigCache;

class PaymentUtils
{
    /**
     * Separator used for prefix-provider format.
     */
    const SEPARATOR = '-';

    /**
     * List of valid providers.
     */
    const PROVIDERS = [
        'midtrans',
        'xendit',
        'tripay',
        'duitku',
        'manual',
        'paypal'
    ];

    /**
     * Add prefix to payment method identifier.
     *
     * @param string $provider
     * @param string $method
     * @return string
     */
    public static function addPrefix(string $provider, string $method): string
    {
        return strtolower($provider . self::SEPARATOR . $method);
    }

    /**
     * Parse prefixed payment method identifier.
     *
     * @param string $prefixedIdentifier
     * @return array{provider: string, method: string}|null
     */
    public static function parse(string $prefixedIdentifier): ?array
    {
        if (strpos($prefixedIdentifier, self::SEPARATOR) === false) {
            if (in_array($prefixedIdentifier, self::PROVIDERS)) {
                return [
                    'provider' => $prefixedIdentifier,
                    'method' => 'all'
                ];
            }
            return null;
        }

        $parts = explode(self::SEPARATOR, $prefixedIdentifier, 2);
        $provider = strtolower($parts[0]);
        $method = strtolower($parts[1]);

        if (!in_array($provider, self::PROVIDERS)) {
            return null;
        }

        return [
            'provider' => $provider,
            'method' => $method,
        ];
    }

    /**
     * Validate if a payment identifier has a valid prefix format.
     * 
     * @param string $identifier
     * @return bool
     */
    public static function isValid(string $identifier): bool
    {
        return self::parse($identifier) !== null;
    }

    /**
     * Remove prefix and return only the method code.
     * 
     * @param string $prefixedIdentifier
     * @return string
     */
    public static function removePrefix(string $prefixedIdentifier): string
    {
        $parsed = self::parse($prefixedIdentifier);
        return $parsed ? $parsed['method'] : $prefixedIdentifier;
    }
    
    /**
     * Get provider from prefixed identifier.
     * 
     * @param string $prefixedIdentifier
     * @return string|null
     */
    public static function getProvider(string $prefixedIdentifier): ?string
    {
        $parsed = self::parse($prefixedIdentifier);
        return $parsed ? $parsed['provider'] : null;
    }

    /**
     * Get real client IP address (supporting proxies like Cloudflare).
     *
     * @return string
     */
    public static function getClientIp(): string
    {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];
        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                return trim($ips[0]);
            }
        }
        return 'unknown';
    }

    /**
     * Get dynamic USD to IDR exchange rate.
     * Uses open.er-api.com and caches the result for 12 hours.
     * 
     * @return float Exchange rate (e.g., 15500.0)
     */
    public static function getUsdExchangeRate(): float
    {
        $cacheKey = 'usd_idr_rate';
        $cachedRate = ConfigCache::get($cacheKey);

        if ($cachedRate !== null) {
            return (float) $cachedRate;
        }

        $fallbackRate = 16000.0;

        $ch = curl_init('https://open.er-api.com/v6/latest/USD');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $body) {
            $data = json_decode($body, true);
            if (isset($data['rates']['IDR'])) {
                $rate = (float) $data['rates']['IDR'];
                ConfigCache::set($cacheKey, $rate, 12 * 3600);
                return $rate;
            }
        }

        return $fallbackRate;
    }
}
