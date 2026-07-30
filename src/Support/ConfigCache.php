<?php

namespace GusviraDigital\GdpPaymentGateway\Support;

class ConfigCache
{
    private static array $cache = [];
    private static string $cachePath = __DIR__ . '/../../cache/';

    public static function setPath(string $path): void
    {
        self::$cachePath = rtrim($path, '/') . '/';
        if (!is_dir(self::$cachePath)) {
            @mkdir(self::$cachePath, 0755, true);
        }
    }

    public static function set(string $key, $value, int $ttl = 3600): void
    {
        self::$cache[$key] = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        if (is_dir(self::$cachePath)) {
            $data = json_encode(self::$cache[$key]);
            @file_put_contents(self::$cachePath . md5($key) . '.cache', $data);
        }
    }

    public static function get(string $key, $default = null)
    {
        if (isset(self::$cache[$key])) {
            if (time() <= self::$cache[$key]['expires']) {
                return self::$cache[$key]['value'];
            }
            unset(self::$cache[$key]);
        }

        $file = self::$cachePath . md5($key) . '.cache';
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            if ($data && time() <= $data['expires']) {
                self::$cache[$key] = $data;
                return $data['value'];
            }
            @unlink($file);
        }

        return $default;
    }

    public static function delete(string $key): void
    {
        unset(self::$cache[$key]);
        $file = self::$cachePath . md5($key) . '.cache';
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
