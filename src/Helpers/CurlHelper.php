<?php

namespace OffbeatCLI\Helpers;

use WP_CLI;

final class CurlHelper
{
    public static function curlJson(string $url): ?string
    {
        WP_CLI::log($url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $token = getenv('TOKEN');
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
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

        $token = getenv('TOKEN');
        if (getenv('TOKEN')) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
        }

        $success = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $success;
    }
}
