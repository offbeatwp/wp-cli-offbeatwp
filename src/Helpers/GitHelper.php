<?php

namespace OffbeatCLI\Helpers;

use RuntimeException;
use WP_CLI;

final class GitHelper
{
    public static function fetch(string $name): void
    {
        // Pull from git
        $cwd = getcwd();
        $repositoryUrl = 'http://git.raow.work:88/raow/offbeat-base-module-repo.git';
        $tempDir = $cwd . '/temp';

        exec("git clone --depth 1 --no-checkout {$repositoryUrl} {$tempDir}");

        if (!is_dir($tempDir) && !mkdir($tempDir) && !is_dir($tempDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $tempDir));
        }

        chdir($tempDir);

        exec('git config core.sparseCheckout true');

        file_put_contents('.git/info/sparse-checkout', $name);

        exec('git read-tree -mu HEAD');
        exec('git pull origin main');

        chdir($cwd);

        // Move from temp to src
        self::moveDirContent($tempDir . '/' . $name, $cwd);

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
        WP_CLI::log(json_encode($files));

        if (!$files) {
            WP_CLI::error('Failed to scan ' . $sourceDir);
        }

        foreach ($files as $file) {
            if ($file[0] !== '.') {
                $sourcePath = $sourceDir . '/' . $file;
                $targetPath = $targetDir . '/' . $file;

                WP_CLI::log($sourcePath . ' -> ' . $targetPath);

                if (rename($sourcePath, $targetPath)) {
                    WP_CLI::log("Moved: {$file}");
                } else {
                    WP_CLI::error("Failed to move: {$file}");
                }
            }
        }
    }
}
