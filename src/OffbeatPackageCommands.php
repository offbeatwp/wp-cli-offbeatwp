<?php

namespace OffbeatCLI;

use OffbeatCLI\Helpers\GitHelper;
use WP_CLI;
use WP_CLI_Command;

final class OffbeatPackageCommands extends WP_CLI_Command
{
    /**
     * Fetch a package.
     * @param string[] $args
     */
    public function package(array $args): void
    {
        if (count($args) < 2) {
            WP_CLI::error('Not enough arguments were provided. Expected ACTION and PACKAGE args. EG: wp offbeatwp fetch hafa/nice-day');
        }

        if ($args[0] !== 'fetch') {
            WP_CLI::error('Unknown action "' . esc_attr($args[0]) . '"');
        }

        if (strpos(getcwd(), 'themes/') === false) {
            WP_CLI::error("You should run this command in the project's theme folder.");
        }

        if (substr_count($args[1], '/') === 1) {
            [$namespace, $name] = explode('/', $args[1]);
        } else {
            $name = $args[1];
            $namespace = '';
            //WP_CLI::error('Package name must be the following format: {group}/{packagename}');
        }

        if (basename($namespace) !== $namespace || basename($name) !== $name) {
            WP_CLI::error('Invalid package name and/or namespace');
        }

        GitHelper::fetch($name);
    }
}
