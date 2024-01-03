<?php

namespace OffbeatCLI\Helpers;

use WP_CLI;

final class PackageHelper
{
    public static function fetch(string $package): void
    {
        [$packageGroup, $packageName] = explode('/', $package);

        WP_CLI::success('Looking for: ' . $packageGroup . ' -> ' . $packageName);
    }
}
