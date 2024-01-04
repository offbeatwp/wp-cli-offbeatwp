<?php

namespace OffbeatCLI\Helpers;

use Generator;
use JsonException;
use OffbeatCLI\Objects\GitlabFile;
use WP_CLI;

final class CurlHelper
{
    /** @return Generator|GitlabFile[] */
    public static function getTrees(string $url): Generator
    {
        WP_CLI::log('~ ' . $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $token = EnvHelper::getToken();
        if ($token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $token]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            WP_CLI::error('Error fetching folder contents, response is empty or malformed.');
            exit;
        }

        try {
            $items = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            WP_CLI::error('JSON response could not be decoded: [' . $exception->getCode() .  '] ' . $exception->getMessage());
            exit;
        }

        if (!is_array($items)) {
            WP_CLI::error('Reponse cannot be decoded to an array.');
        }

        foreach ($items as $item) {
            yield new GitlabFile($item);
        }
    }

    public static function downloadFile(string $url, string $path): void
    {
        $path = getcwd() . '/' . $path;
        $ch = curl_init($url . '/raw?ref=main');
        $fp = fopen($path, 'wb');

        if (!$fp) {
            WP_CLI::error('Could not fopen: ' . $path);
        }

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
            WP_CLI::log("Downloaded: {$path}");
        } else {
            WP_CLI::error("Failed to download file at url: {$url}");
        }
    }
}
