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

        $token = self::getPrivateToken();
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?: null;
    }

    public static function curlFile(string $url, string $toDir): void
    {
        $ch = curl_init($url);
        $fp = fopen($toDir, 'wb');

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $token = self::getPrivateToken();
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
        }

        $success = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        if ($success) {
            WP_CLI::log("File downloaded: {$url}");
        } else {
            WP_CLI::error("Failed to download file: {$url}");
        }
    }

    public static function getPrivateToken(): string
    {
        $token = getenv('GITLAB_TOKEN') ?: '';

        if ($token) {
            WP_CLI::log('Using personal access token from ENV');
        } else {
            WP_CLI::log('Not using a personal access token. If needed, set one with `offbeatwp token set {YOUR_TOKEN}`');
        }

        return $token;
    }
}
