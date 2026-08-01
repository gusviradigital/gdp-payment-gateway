<?php

namespace GusviraDigital\GdpPaymentGateway\Utilities;

class InstructionHelper
{
    /**
     * Dapatkan path absolut ke direktori JSON assets.
     */
    private static function getJsonDir(): string
    {
        return dirname(__DIR__, 2) . '/assets/json/';
    }

    /**
     * Ambil informasi metode pembayaran (nama, logo, warna).
     *
     * @param string $method (e.g. bca, mandiri, qris)
     * @return array|null
     */
    public static function getPaymentMethodInfo(string $method): ?array
    {
        $filePath = self::getJsonDir() . 'payment-methods.json';
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if (!$content) {
            return null;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data[$method] ?? null;
    }

    /**
     * Ambil langkah-langkah instruksi pembayaran.
     * Mengganti placeholder {{pay_code}} dengan kode bayar aktual.
     *
     * @param string $method
     * @param string $payCode
     * @return array|null
     */
    public static function getInstructions(string $method, string $payCode = ''): ?array
    {
        $filePath = self::getJsonDir() . 'payment-instructions.json';
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        if (!$content) {
            return null;
        }

        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        if (!isset($data[$method]) || empty($data[$method]['data'])) {
            return null;
        }

        $instructions = $data[$method]['data'];

        // Ganti placeholder dengan payCode jika diberikan
        if (!empty($payCode)) {
            foreach ($instructions as &$instructionBlock) {
                if (isset($instructionBlock['steps']) && is_array($instructionBlock['steps'])) {
                    foreach ($instructionBlock['steps'] as &$step) {
                        $step = str_replace('{{pay_code}}', htmlspecialchars($payCode), $step);
                    }
                }
            }
        }

        return $instructions;
    }
}
