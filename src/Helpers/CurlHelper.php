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

        $token = EnvHelper::getToken();
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: null;
    }

    public static function curlFile(string $path, string $toDir): void
    {
        $ch = curl_init($path);
        $fp = fopen($toDir, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $token = EnvHelper::getToken();
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
        }

        $success = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        if ($success) {
            WP_CLI::log("File downloaded: {$path}");
        } else {
            WP_CLI::error("Failed to download file: {$path}");
        }
    }
}
