<?php

namespace OffbeatCLI\Helpers;

use RuntimeException;
use WP_CLI;

final class PackageHelper
{
    public static function fetch(string $packageGroup, string $packageDir): void
    {
        WP_CLI::success('Looking for: ' . $packageGroup . ' -> ' . $packageDir);

        $url = 'http://git.raow.work:88/raow/offbeat-base-module-repo.git';
        $destination = 'temp';

        if (!mkdir($destination) && !is_dir($destination)) {
            throw new RuntimeException('Directory "' . $destination . '" could not be created');
        }

        // Execute the Git command
        exec("git clone --depth 1 --filter=blob:none --sparse {$url} {$destination}", $output, $exitCode);

        if ($exitCode === 0) {
            // Change into the destination folder
            chdir($destination);

            // Fetch only the specific folder using sparse checkout
            exec("git sparse-checkout init --cone && git sparse-checkout set {$packageDir}");

            echo "Folder downloaded successfully.";
        } else {
            echo "Error downloading folder. Exit code: $exitCode";
        }
    }
}
