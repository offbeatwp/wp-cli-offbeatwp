<?php

namespace OffbeatCLI\Helpers;

use RuntimeException;
use WP_CLI;

final class GitHelper
{
    public static function fetch(string $name): void
    {
        // Pull from git
        $repositoryUrl = 'http://git.raow.work:88/raow/offbeat-base-module-repo.git';
        $tempDir = getcwd() . '/temp';

        exec("git clone --depth 1 --no-checkout {$repositoryUrl} {$tempDir}");

        if (!is_dir($tempDir) && !mkdir($tempDir) && !is_dir($tempDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempDir));
        }

        chdir($tempDir);

        exec('git config core.sparseCheckout true');

        file_put_contents('.git/info/sparse-checkout', $name);

        exec('git read-tree -mu HEAD');
        exec('git pull origin main');

        // Move from temp to src
        self::moveDirContent($tempDir . '/' . $name, getcwd());

        // Delete leftovers
        rmdir($tempDir . '/' . $name);
        rmdir($tempDir);
    }

    /**
     * Moves content from one dir to another.<br>
     * Any files whose name start with <b>.</b> are ignored.
     */
    public static function moveDirContent(string $sourceDir, string $targetDir): void
    {
        $files = scandir($sourceDir);

        if (!$files) {
            WP_CLI::error('Failed to scan ' . $sourceDir);
        }

        foreach ($files as $file) {
            if ($file[0] === '.') {
                $sourcePath = $sourceDir . '/' . $file;
                $targetPath = $targetDir . '/' . $file;

                if (rename($sourcePath, $targetPath)) {
                    WP_CLI::log("Moved: {$file}");
                } else {
                    WP_CLI::error("Failed to move: {$file}");
                }
            }
        }
    }
}
