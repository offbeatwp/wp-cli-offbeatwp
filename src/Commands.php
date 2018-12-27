<?php
namespace OffbeatCLI;

use WP_CLI_Command;
use WP_CLI;

class Commands extends WP_CLI_Command {

    /**
     * Create fresh offbeat theme
     *
     * @subcommand init-theme
     */
    function init_theme($args, $assocArgs)
    {
        if (!isset($args[0])) {
            WP_CLI::error('Define the slug for your theme');
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-0_-]+$/', $args[0])) {
            WP_CLI::error('Theme slug is not valid, only use alphanumeric characters and _ (underscore) and - (dash) are allowed.');
            exit;
        }

        $themeSlug = $args[0];
        $newThemeDirectory = get_theme_root() . "/{$themeSlug}";

        if (!isset($assocArgs['force']) && is_dir($newThemeDirectory)) {
            WP_CLI::error("Folder ({$newThemeDirectory}) already exists");
            exit;
        }

        if (isset($assocArgs['force'])) {
            exec("rm -rf {$newThemeDirectory}");
        }

        mkdir($newThemeDirectory);
        
        $version = (isset($assocArgs['version'])) ? $assocArgs['version'] : 'master';
        $githubUrl = 'https://github.com/offbeatwp/offbeatwp.git';

        exec( "git clone {$githubUrl} {$newThemeDirectory} -b {$version}" );
        exec( "rm -rf {$newThemeDirectory}/.git" );
        exec( "composer install -d {$newThemeDirectory}" );

        WP_CLI::log('Activate Theme');
        switch_theme( $themeSlug );

        WP_CLI::success('Done');
    }
}