<?php

namespace OffbeatCLI\Helpers;

use OffbeatCLI\OffbeatScaffoldingCommands;
use RuntimeException;
use WP_CLI;

final class OffbeatScaffoldHelper
{
    public static function filterName(?string $name): string
    {
        if (empty($name)) {
            WP_CLI::error('Please define a name');
            exit;
        }

        if (preg_match('/[^a-zA-Z0-9]/', $name)) {
            WP_CLI::error('Name contains not supported characters');
            exit;
        }

        if (preg_match('/^\d/', $name)) {
            WP_CLI::error('Name can not start with a number');
            exit;
        }

        return ucfirst($name);
    }

    public static function filterCpt(?string $customType, string $kind): string
    {
        if (empty($customType)) {
            WP_CLI::error('Please define a custom ' . $kind . ' with --' . $kind . "=\"\"");
            exit;
        }

        if (strpos($customType, ' ')) {
            WP_CLI::error('No spaces are allowed in custom post/taxonomy types');
        }

        if (strlen($customType) > 20) {
            WP_CLI::error('Custom post/taxonomy names should not exceed 20 characters');
            exit;
        }

        return strtolower($customType);
    }

    public static function makeCustomType(array $args, array $assocArgs, string $kind): void
    {
        $name = self::filterName($args[0]);
        $cpt = self::filterCpt($assocArgs[$kind], $kind);

        $directory = get_template_directory() . '/app/Models/';

        $namespace = 'App\Models';
        $classname = $name . 'Model';

        $path = $directory . $classname . '.php';

        if (isset($assocArgs['module'])) {
            $module = $assocArgs['module'];

            $modulePath = get_template_directory() . '/modules/' . $module . '/';

            if (preg_match('/[^a-zA-Z0-9]/', $module) || preg_match('/^\d/', $module) || !is_dir($modulePath)) {
                WP_CLI::error('Module does not exists');
                exit;
            }

            $directory = $modulePath . '/Models/';

            if (!is_dir($directory) && !mkdir($directory) && !is_dir($directory)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $directory));
            }

            $namespace = "Modules\\{$module}\Models";
            $path = $directory . $classname . '.php';
        }

        if (!is_dir($directory)) {
            WP_CLI::error("Path does not exists ({$path})");
            exit;
        }

        if (file_exists($path)) {
            WP_CLI::error('Model already exists');
            exit;
        }

        $modelFile = fopen($path, 'wb');

        $modelTemplateName = ($kind === OffbeatScaffoldingCommands::ARG_POST) ? 'PostModel' : 'TermModel';
        $modelFileContent = file_get_contents(get_template_directory() . '/vendor/offbeatwp/framework/templates/' . $modelTemplateName . '.txt');
        $modelFileContent = str_replace(['{{ namespace }}', '{{ classname }}', '{{ ' . $kind . ' }}'], [$namespace, $classname, $cpt], $modelFileContent);

        fwrite($modelFile, $modelFileContent);
        fclose($modelFile);

        WP_CLI::success("Model created in {$path}\n");
    }
}
