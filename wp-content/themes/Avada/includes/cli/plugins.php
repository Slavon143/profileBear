<?php
/**
 * Install and Active Avada Plugins.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage CLI
 * @since      7.4
 *
 * @usage: wp plugin install $(wp fusion plugin url Avada-Builder) --activate --force
 */

$fusion_plugin_url_cmd = function( $args, $assoc_args ) {

	if ( empty( $args ) ) {
		WP_CLI::error( 'Plugin slug is missing.' );
	}

	$plugin_name = str_replace( '-', ' ', $args[0] );

	echo Avada()->remote_install->get_package( $plugin_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
};

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'fusion plugin url', $fusion_plugin_url_cmd );
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
