<?php

namespace GusviraDigital\GdpPaymentGateway\Utilities;

class SignatureVerifier
{
    /**
     * Verify Midtrans Signature
     */
    public static function verifyMidtrans(string $orderId, string $statusCode, string $grossAmount, string $serverKey, string $signatureToVerify): bool
    {
        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        return hash_equals($expectedSignature, $signatureToVerify);
    }

    /**
     * Verify Tripay Signature
     */
    public static function verifyTripay(string $jsonPayload, string $privateKey, string $signatureToVerify): bool
    {
        $expectedSignature = hash_hmac('sha256', $jsonPayload, $privateKey);
        return hash_equals($expectedSignature, $signatureToVerify);
    }

    /**
     * Verify Duitku Signature
     */
    public static function verifyDuitku(string $merchantCode, string $amount, string $merchantOrderId, string $apiKey, string $signatureToVerify): bool
    {
        $expectedSignature = md5($merchantCode . $amount . $merchantOrderId . $apiKey);
        return $expectedSignature === $signatureToVerify;
    }

    /**
     * Verify Xendit Callback Token
     */
    public static function verifyXendit(string $callbackToken, string $xenditToken): bool
    {
        return hash_equals($xenditToken, $callbackToken);
    }
}
