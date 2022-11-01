<?php
/**
 * Instantiate the builder.
 *
 * @since 2.0
 * @package fusion-library
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Fusion_App' ) ) {
	include_once wp_normalize_path( dirname( __FILE__ ) . '/class-fusion-app.php' );
}
add_action( 'init', 'load_builder_class' );

/**
 * Instantiate Fusion_App class.
 */
function load_builder_class() {

	$app = Fusion_App::get_instance();

	if ( apply_filters( 'fusion_load_live_editor', is_user_logged_in() ) && ( $app->get_builder_status() || $app->get_preview_status() || $app->get_ajax_status() ) ) {

		// Load internal modules ( panel, fusion builder ).
		do_action( 'fusion_load_internal_module' );

		// Action for loading custom modules.
		do_action( 'fusion_load_module' );
	}
}
