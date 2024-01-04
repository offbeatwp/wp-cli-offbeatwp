<?php

namespace OffbeatCLI\Helpers;

use Generator;
use JsonException;
use OffbeatCLI\Objects\GitlabFile;
use WP_CLI;

final class ApiHelper
{
    private const REPO_BASE = 'http://git.raow.work:88/api/v4/projects/raow%2Foffbeat-base-module-repo/repository/';
    private const REPO_TREE = self::REPO_BASE . 'tree?ref=main&path=';
    private const REPO_FILES = self::REPO_BASE . 'files/';

    public static function fetch(string $path): void
    {
        WP_CLI::log('>>> ' . $path);

        foreach (self::getTrees($path) as $file) {
            if ($file->type === 'tree') {
                self::fetch($file->path);
            } else {
                self::downloadFile($file->path);
            }
        }
    }

    /** @return Generator<GitlabFile>|GitlabFile[] */
    private static function getTrees(string $path): Generator
    {
        $url = self::REPO_TREE . urlencode($path);
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

    private static function downloadFile(string $path): void
    {
        $url = self::REPO_FILES . urlencode($path) . '/raw?ref=main';
        WP_CLI::log('>>> ' . $url);

        $path = getcwd() . '/' . $path;
        $ch = curl_init($url);
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
