<?php

namespace OffbeatCLI\Helpers;

use WP_CLI;

final class EnvHelper
{
    private const TOKEN = 'GITLAB_TOKEN';

    private static ?array $env = null;
    private static bool $logTokenMsg = true;

    public static function getToken(): string
    {
        if (EnvHelper::$env === null) {
            if (file_exists('.env')) {
                self::$env = parse_ini_file('.env') ?: [];
            } else {
                self::$env = [];
            }
        }

        $token = self::$env[self::TOKEN] ?? '';

        if (self::$logTokenMsg) {
            if ($token) {
                WP_CLI::log('Using personal access token from ENV');
            } else {
                WP_CLI::log('Not using a personal access token. If needed, set one with `offbeatwp token set {YOUR_TOKEN}`');
            }

            self::$logTokenMsg = false;
        }

        return $token;
    }

    public static function setToken(string $token): void
    {
        if (!file_exists('.env')) {
            file_put_contents('.env', '');
        }

        $envFile = fopen('.env', 'wb');

        if ($envFile) {
            fwrite($envFile, self::TOKEN . '=' . $token);
            fclose($envFile);

            WP_CLI::success('Updated token');
        } else {
            WP_CLI::error('Failed to update token');
        }
    }
}
