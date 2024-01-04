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
            throw new RuntimeException('Directory "' . $tempDir .'" was not created');
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
        self::removeDirectoryRecursively($tempDir);
    }

    /**
     * Moves content from one dir to another.<br>
     * Any files whose name starts with <b>.</b> are ignored.
     */
    public static function moveDirContent(string $sourceDir, string $targetDir): void
    {
        foreach (scandir($sourceDir) as $file) {
            if ($file[0] !== '.') {
                $sourcePath = $sourceDir . '/' . $file;
                $targetPath = $targetDir . '/' . $file;

                if (is_dir($sourcePath)) {
                    if (!file_exists($targetPath) && !mkdir($targetPath) && !is_dir($targetPath)) {
                        throw new RuntimeException('Directory "' . $targetPath . '" was not created');
                    }

                    self::moveDirContent($sourcePath, $targetPath);
                } elseif (file_exists($targetPath)) {
                    WP_CLI::log("Skipped: {$file}");
                } elseif (rename($sourcePath, $targetPath)) {
                    WP_CLI::log("Added: {$file}");
                } else {
                    WP_CLI::error("Failed to move: {$file}");
                }
            }
        }
    }

    private static function removeDirectoryRecursively(string $dir): bool
    {
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..' && $file[0] !== '/') {
                if (is_dir("$dir/$file")) {
                    self::removeDirectoryRecursively("$dir/$file");
                } else {
                    unlink("$dir/$file");
                }
            }
        }

        return rmdir($dir);
    }
}
