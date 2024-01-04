<?php

namespace OffbeatCLI\Helpers;

use RuntimeException;

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
        rename($tempDir . '/' . $name, '/');

        // Delete leftovers
        rmdir($tempDir);
    }
}
