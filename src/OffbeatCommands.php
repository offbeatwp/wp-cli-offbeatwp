<?php

namespace OffbeatCLI;

use OffbeatCLI\Helpers\PackageHelper;
use WP_CLI;
use WP_CLI_Command;

final class OffbeatCommands extends WP_CLI_Command
{
    /** @throws \WP_CLI\ExitException */
    public function package(array $args): void
    {
        if (count($args) < 2) {
            WP_CLI::error('Not enough arguments were provided. Expected ACTION and PACKAGE args. EG: wp offbeatwp fetch hafa/nice-day');
        }

        if ($args[0] !== 'fetch') {
            WP_CLI::error('Unknown action "' . esc_attr($args[0]) . '"');
        }

        if (substr_count($args[1], '/') !== 1) {
            WP_CLI::error('Package name must be the following format: {group}/{packagename}');
        }

        [$packageGroup, $packageDir] = explode('/', $args[1]);

        if (basename($packageGroup) !== $packageGroup) {
            WP_CLI::error('Invalid package group name. Did you mean ' . basename($packageGroup));
        }

        if (basename($packageDir) !== $packageDir) {
            WP_CLI::error('Invalid package name. Did you mean ' . basename($packageDir));
        }

        PackageHelper::fetch($packageGroup, $packageDir);
    }
}
