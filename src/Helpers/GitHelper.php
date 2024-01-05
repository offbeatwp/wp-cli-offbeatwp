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
        $tempDirPath = $cwd . '/temp';

        exec("git clone --depth 1 --no-checkout {$repositoryUrl} {$tempDirPath}");

        if (!is_dir($tempDirPath) && !mkdir($tempDirPath) && !is_dir($tempDirPath)) {
            throw new RuntimeException('Directory "' . $tempDirPath .'" was not created');
        }

        chdir($tempDirPath);

        exec('git config core.sparseCheckout true');

        file_put_contents('.git/info/sparse-checkout', $name);

        exec('git read-tree -mu HEAD');
        exec('git pull origin main');

        chdir($cwd);

        // Move from temp to src
        self::moveDirContent($tempDirPath . '/' . $name, $cwd);

        // Delete leftovers
        self::removeDirectoryRecursively($tempDirPath);

        WP_CLI::success('Fetch complete!');
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
                    WP_CLI::log(WP_CLI::colorize("%cAdded:%n {$targetPath}"));
                } else {
                    WP_CLI::error("%yFailed to move:%n {$targetPath}");
                }
            }
        }
    }

    private static function removeDirectoryRecursively(string $dir): bool
    {
        foreach (scandir($dir) as $file) {
            if ($file !== '.' && $file !== '..' && $file[0] !== '/') {
                if (is_dir($dir . '/' . $file)) {
                    self::removeDirectoryRecursively($dir . '/' . $file);
                } else {
                    unlink($dir . '/' . $file);
                }
            }
        }

        return rmdir($dir);
    }
}
