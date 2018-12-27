<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Offbeat command
 *
 * @when before_wp_load
 */
$offbeat_command = function() {
	WP_CLI::success( "Offbeat." );
};
WP_CLI::add_command( 'offbeatwp', $offbeat_command );
