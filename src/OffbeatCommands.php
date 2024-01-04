<?php

namespace OffbeatCLI;

use OffbeatCLI\Helpers\PackageHelper;
use WP_CLI;
use WP_CLI_Command;

final class OffbeatCommands extends WP_CLI_Command
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

        PackageHelper::fetch($packageGroup, $packageDir, []);
    }

    /**
     * Set or clear your private access token.
     * @param string[] $args
     */
    public function token(array $args): void
    {
        // Validate arguments
        $action = strtolower($args[0] ?? '');

        if (in_array($action, ['set', 'clear'], true)) {
            WP_CLI::error('Invalid argument. Expected either "set" or "clear".');
        }

        $assignment = 'TOKEN=';
        $expectedArgs = ($action === 'set') ? 2 : 1;

        if (count($args) !== $expectedArgs) {
            WP_CLI::error('Invalid number of arguments provided.');
        }

        // Add token to assignment
        if ($action === 'set') {
            if (strlen($args[1]) < 20) {
                WP_CLI::error('Invalid token provided.');
            }

            $assignment .= $args[1];
        }

        // Write to env
        $result = putenv($assignment);

        if ($result) {
            WP_CLI::success('Saved token!');
        } else {
            WP_CLI::error('Failed to save token.');
        }
    }
}
