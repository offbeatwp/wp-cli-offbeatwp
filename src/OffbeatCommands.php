<?php

namespace OffbeatCLI;

use OffbeatCLI\Helpers\EnvHelper;
use OffbeatCLI\Helpers\GitHelper;
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

        if (strpos(getcwd(), 'themes/') === false) {
            WP_CLI::error("You should run this command in the project's theme folder.");
        }

        if (substr_count($args[1], '/') !== 1) {
            WP_CLI::error('Package name must be the following format: {group}/{packagename}');
        }

        [$namespace, $name] = explode('/', $args[1]);

        if (basename($namespace) !== $namespace || basename($name) !== $name) {
            WP_CLI::error('Invalid package name and/or namespace');
        }

        GitHelper::fetch($name);
        //ApiHelper::fetch($name);
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
            EnvHelper::getToken(); // Do NOT echo the token
            exit;
        }

        // Add token to assignment
        $value = '';

        if ($action === 'set') {
            if (strlen($token) < 20) {
                WP_CLI::error('Invalid token provided');
            } else {
                WP_CLI::log('Updating token...');
            }

            $value = $token;
        }

        EnvHelper::setToken($value);
    }
}
