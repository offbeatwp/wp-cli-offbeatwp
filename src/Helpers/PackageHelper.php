<?php

namespace OffbeatCLI\Helpers;

use JsonException;
use WP_CLI;

final class PackageHelper
{
    private const REPO = 'http://git.raow.work:88/api/v4/projects/raow%2Foffbeat-base-module-repo/repository/';

    public static function fetch(string $namespace, string $name): void
    {
        // Make a cURL request to the GitLab API
        $json = CurlHelper::curlJson(self::REPO . 'tree?ref=main&path=' . $name);

        if ($json) {
            WP_CLI::log($json);

            try {
                $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                WP_CLI::error('JSON response could not be decoded: [' . $exception->getCode() .  '] ' . $exception->getMessage());
            }

            if (!is_array($data)) {
                WP_CLI::error('JSON api response is not an array: ' . $json);
            }

            foreach ($data as $file) {
                CurlHelper::curlFile(self::REPO . 'files/google%2Fapp%2FServices%2FGoogle%2FGoogleService.php/raw?ref=main', __DIR__ . '/temp');
                break;
//                if (is_array($file) && isset($file['path'], $file['name'])) {
//                    CurlHelper::curlFile('files/' . $file['path'], 'temp/' . $file['name']);
//                } else {
//                    WP_CLI::error('Unexpected response content: ' . json_encode($file));
//                }
            }

            WP_CLI::success('Folder downloaded successfully.');
        } else {
            WP_CLI::error('Error fetching folder contents, response is empty or malformed.');
        }

//        if (!mkdir($destination) && !is_dir($destination)) {
//            throw new RuntimeException('Directory "' . $destination . '" could not be created');
//        }
//
//        // Execute the Git command
//        exec("git clone --depth 1 --filter=blob:none --sparse {$url} {$destination}", $output, $exitCode);
//
//        if ($exitCode === 0) {
//            // Change into the destination folder
//            chdir($destination);
//
//            // Fetch only the specific folder using sparse checkout
//            exec("git sparse-checkout init --cone && git sparse-checkout set {$packageDir}");
//
//            echo "Folder downloaded successfully.";
//        } else {
//            echo "Error downloading folder. Exit code: $exitCode";
//        }
    }
}
