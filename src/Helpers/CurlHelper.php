<?php

namespace OffbeatCLI\Helpers;

final class CurlHelper
{
    public static string $token = '';

    public static function curlJson(string $url): ?string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (self::$token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . self::$token]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: null;
    }

    public static function curlFile(string $url, string $toDir): bool
    {
        $ch = curl_init($url);
        $fp = fopen($toDir, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        if (self::$token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . self::$token]);
        }

        $success = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $success;
    }
}
