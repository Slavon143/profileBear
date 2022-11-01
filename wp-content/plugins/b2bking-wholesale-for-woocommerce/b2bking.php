<?php
/*
/**
 * Plugin Name:       B2BKing Core
 * Plugin URI:        https://codecanyon.net/item/b2bking-the-ultimate-woocommerce-b2b-plugin/26689576
 * Description:       B2BKing is the complete solution for turning WooCommerce into an enterprise-level B2B e-commerce platform. Core Plugin.
 * Version:           4.2.33
 * Author:            WebWizards
 * Author URI:        webwizards.dev
 * Text Domain:       b2bking
 * Domain Path:       /languages
 * WC requires at least: 4.0.0
 * WC tested up to: 6.1.0
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'B2BKINGCORE_DIR', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'B2BKINGCORE_VERSION' ) ) {
	define(	'B2BKINGCORE_VERSION', 'v4.2.33');
}


function b2bkingcore_activate() {
	require_once B2BKINGCORE_DIR . 'includes/class-b2bking-activator.php';
	B2bkingcore_Activator::activate();
}
register_activation_hook( __FILE__, 'b2bkingcore_activate' );

require B2BKINGCORE_DIR . 'includes/class-b2bking.php';

// Load plugin language
add_action( 'init', 'b2bkingcore_load_language');
function b2bkingcore_load_language() {
   load_plugin_textdomain( 'b2bking', FALSE, basename( dirname( __FILE__ ) ) . '/languages');
}

// Begins execution of the plugin.
function b2bkingcore_run() {
	$plugin = new B2bkingcore();
}

if (!defined('B2BKING_DIR') && get_option('b2bking_main_active', 'no') === 'no'){

	b2bkingcore_run();

} else {
	add_action('plugins_loaded', function(){

		// important for correct plugin loading order
		if (class_exists('B2bking')){
			update_option('b2bking_main_active', 'yes');
		} else {
			update_option('b2bking_main_active', 'no');
		}
	});
}