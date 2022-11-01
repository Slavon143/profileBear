<?php
/**
 * Shared CLI functions.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage CLI
 * @since      7.3
 */

$fusion_clear_caches_cmd = function() {
	if ( function_exists( 'avada_reset_all_caches' ) ) {
		avada_reset_all_caches();
		WP_CLI::success( 'Avada Cache cleared.' );
	} else {
		WP_CLI::error( 'Avada Cache could not be cleared.' );
	}
};

if ( class_exists( 'WP_CLI' ) ) {
	WP_CLI::add_command( 'fusion clear_caches', $fusion_clear_caches_cmd );
}
