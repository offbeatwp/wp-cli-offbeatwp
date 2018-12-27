<?php
$composerAutoload = dirname(__FILE__) . '/vender/autoload.php';

if (is_dir($composerAutoload)) {
    require_once $composerAutoload;
}

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

WP_CLI::add_command( 'offbeatwp', OffbeatCLI\Commands::class );
