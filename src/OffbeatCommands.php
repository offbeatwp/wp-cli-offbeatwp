<?php

namespace OffbeatCLI;

use OffbeatCLI\Helpers\CurlHelper;
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

        if (basename($packageGroup) !== $packageGroup || basename($packageDir) !== $packageDir) {
            WP_CLI::error('Invalid package name and/or namcespace');
        }

        PackageHelper::fetch($packageGroup, $packageDir);
    }

    /**
     * Set or clear your private access token.
     * @param string[] $args
     */
    public function token(array $args): void
    {
        $action = strtolower($args[0] ?? '');

        // Validate action
        if (!in_array($action, ['set', 'clear', 'check'], true)) {
            WP_CLI::error('Invalid argument. Expected either "set" or "clear"');
        }

        // Validate arguments
        $token = $args[1] ?? '';
        $expectedArgs = ($action === 'set') ? 2 : 1;
        $argCount = count($args);

        if (count($args) !== $expectedArgs) {
            WP_CLI::error('Invalid number of arguments provided. Got ' . $argCount . ' but expected ' . $expectedArgs);
        }

        // Check action
        if ($action === 'check') {
            CurlHelper::getPrivateToken(); // Do NOT echo the token
            exit;
        }

        // Add token to assignment
        $assignment = 'TOKEN=';

        if ($action === 'set') {
            if (strlen($token) < 20) {
                WP_CLI::error('Invalid token provided');
            } else {
                WP_CLI::log('Updating token...');
            }

            $assignment .= $token;
        }

        // Write to env
        $result = putenv($assignment);

        if ($result) {
            WP_CLI::success('Token was saved successfully');
        } else {
            WP_CLI::error('Failed to save token');
        }
    }
}
