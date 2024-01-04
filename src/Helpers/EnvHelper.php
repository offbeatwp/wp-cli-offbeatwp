<?php

namespace OffbeatCLI\Helpers;

use WP_CLI;

final class EnvHelper
{
    private const TOKEN = 'GITLAB_TOKEN';

    public static function getToken(): string
    {
        $token = getenv(self::TOKEN) ?: '';

        if ($token) {
            WP_CLI::log('Using personal access token from ENV');
        } else {
            WP_CLI::log('Not using a personal access token. If needed, set one with `offbeatwp token set {YOUR_TOKEN}`');
        }

        return $token;
    }

    public static function setToken(string $token): bool
    {
        return putenv(self::TOKEN . '=' . $token);
    }
}
