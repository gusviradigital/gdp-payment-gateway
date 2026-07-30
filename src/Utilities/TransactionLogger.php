<?php

namespace GusviraDigital\GdpPaymentGateway\Utilities;

class TransactionLogger
{
    private static string $logPath = __DIR__ . '/../../logs/';

    public static function setLogPath(string $path): void
    {
        self::$logPath = rtrim($path, '/') . '/';
        if (!is_dir(self::$logPath)) {
            @mkdir(self::$logPath, 0755, true);
        }
    }

    public static function log(string $provider, string $message, array $context = []): void
    {
        if (!is_dir(self::$logPath)) {
            return; // Silently fail if log directory is not writable
        }

        $date = date('Y-m-d');
        $time = date('Y-m-d H:i:s');
        $logFile = self::$logPath . "payment-{$provider}-{$date}.log";
        
        $contextStr = !empty($context) ? json_encode($context) : '';
        $logEntry = "[{$time}] {$message} {$contextStr}" . PHP_EOL;
        
        @file_put_contents($logFile, $logEntry, FILE_APPEND);
    }

    public static function error(string $provider, string $message, array $context = []): void
    {
        self::log($provider, "[ERROR] " . $message, $context);
    }
}
