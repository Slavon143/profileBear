<?php
/*
/**
 * Plugin Name:       B2BKing Pro
 * Plugin URI:        woocommerce-b2b-plugin.com
 * Description:       B2BKing is the complete solution for turning WooCommerce into an enterprise-level B2B e-commerce platform.
 * Version:           4.2.7
 * Author:            WebWizards
 * Author URI:        webwizards.dev
 * Text Domain:       b2bking
 * Domain Path:       /languages
 * WC requires at least: 3.0.0
 * WC tested up to: 6.8.1
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'B2BKING_VERSION' ) ) {
	define(	'B2BKING_VERSION', 'v4.2.7');
}

if ( ! defined( 'B2BKING_DIR' ) ) {
	define( 'B2BKING_DIR', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'B2BKINGMAIN_DIR' ) ) {
	define( 'B2BKINGMAIN_DIR', plugin_dir_path( __FILE__ ) );
}

// Begins execution of the plugin.

if (!function_exists('b2bking_run')){
	function b2bking_run() {

		require_once ( B2BKING_DIR . 'includes/class-b2bking-global-helper.php' );

		if (!function_exists('b2bking')){
			function b2bking() {
			    return B2bking_Globalhelper::init();
			}
		}

		if (!function_exists('b2bking_activate')){

			function b2bking_activate() {
				require_once B2BKING_DIR . 'includes/class-b2bking-activator.php';
				B2bking_Activator::activate();
			}
			
		}

		register_activation_hook( __FILE__, 'b2bking_activate' );


		require B2BKING_DIR . 'includes/class-b2bking.php';

		// Load plugin language
		add_action( 'plugins_loaded', 'b2bking_load_language');
		function b2bking_load_language() {
			load_plugin_textdomain( 'b2bking', FALSE, basename( dirname( __FILE__ ) ) . '/languages');
		}

		global $b2bking_plugin;
		$b2bking_plugin = new B2bking();
	}

	b2bking_run();
} else {
	
    register_activation_hook( __FILE__, 'b2bking_activation_error' );
    function b2bking_activation_error() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
        wp_die( 'The plugin could not be activated because another version of B2BKing Pro, version '.B2BKING_VERSION.' is already active. <strong>Please deactivate version '.B2BKING_VERSION.' before activating this one.</strong>');
    }

}

