<?php

namespace OffbeatCLI\Helpers;

use RuntimeException;

final class GitHelper
{
    public static function fetch(string $name): void
    {
        $repositoryUrl = 'http://git.raow.work:88/raow/offbeat-base-module-repo.git';
        $targetDir = getcwd() . '/temp';

        exec("git clone --depth 1 --no-checkout {$repositoryUrl} {$targetDir}");

        if (!is_dir($targetDir) && !mkdir($targetDir) && !is_dir($targetDir)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $targetDir));
        }

        chdir($targetDir);

        exec('git config core.sparseCheckout true');

        file_put_contents('.git/info/sparse-checkout', $name);

        exec('git read-tree -mu HEAD');
        exec('git pull origin main');
    }
}
