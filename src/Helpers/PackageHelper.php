<?php

namespace OffbeatCLI\Helpers;

use WP_CLI;

final class PackageHelper
{
    public static function fetch(string $repository, string $packageDir, array $assocArgs): void
    {
        WP_CLI::log('Looking for: ' . $repository . ' -> ' . $packageDir);

        if ($repository === 'vollegrond') {
            $repository = 'offbeat-base-module-repo';
        }

        // Either username and accessToken from args or use defaults
        $username = $assocArgs['name'] ?? 'raow';
        $accessToken = $assocArgs['token'] ?? null;

        // Specify the local destination folder on your server
        $localFolderPath = '/path/on/server/where/to/save/';

        // GitLab API URL for fetching the contents of a directory
        $apiUrl = "http://git.raow.work:88/api/v4/projects/{$username}%2F{$repository}/repository/tree?ref=main&path={$packageDir}";

        // Make a cURL request to the GitLab API
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($accessToken) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['PRIVATE-TOKEN: ' . $accessToken]);
        }

        $response = curl_exec($ch);
        curl_close($ch);

        if ($response && is_string($response)) {
            $data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($data)) {
                WP_CLI::error('Invalid response: ' . gettype($data) . (is_scalar($data) ? ' ' . $data : ''));
            }

            foreach ($data as $file) {
                $ch = curl_init($file['web_url'] . '/raw');
                $fp = fopen($localFolderPath . $file['name'], 'wb');

                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);

                curl_exec($ch);
                curl_close($ch);
                fclose($fp);

                WP_CLI::log("File downloaded: {$file['name']}");
            }

            WP_CLI::success("Folder downloaded successfully.");
            echo "Folder downloaded successfully.";
        } else {
            WP_CLI::error("Error fetching folder contents.");
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
