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
            WP_CLI::error('Not enough arguments were provided. Excepcted ACTION and PACKAGE args.');
        }

        if ($args[0] !== 'fetch') {
            WP_CLI::error('Unknown action "' . esc_attr($args[0]) . '"');
        }

        if (strpos($args[1], '/', 1) !== 1) {
            WP_CLI::error('Package name must be the following format: {group}/{packagename}');
        }

        PackageHelper::fetch($args[1]);
    }
}
