<?php

namespace OffbeatCLI\Helpers;

use WP_CLI;

final class PackageHelper
{
    private const REPO_BASE = 'http://git.raow.work:88/api/v4/projects/raow%2Foffbeat-base-module-repo/repository/';
    private const REPO_TREE = self::REPO_BASE . 'tree?ref=main&path=';
    private const REPO_FILES = self::REPO_BASE . 'files/';

    public static function fetch(string $path)
    {
        WP_CLI::log('>>> ' . $path);
        $encodedPath = urlencode($path);

        foreach (CurlHelper::getTrees(self::REPO_TREE . $encodedPath) as $file) {
            if ($file->type === 'tree') {
                self::fetch($file->path);
            } else {
                CurlHelper::downloadFile(self::REPO_FILES . $encodedPath . '/raw?ref=main', $path . '/' . $file->name);
            }
        }
    }
}
