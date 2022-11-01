<?php
/**
 * The Smart Media Categories (SMC) Plugin.
 *
 * Provides support to assign taxonomy terms to Media Library items based on
 * the terms of their parent post/page.
 *
 * @package   Smart_Media_Categories
 * @author    David Lingren <david@davidlingren.com>
 * @license   GPL-2.0+
 * @copyright 2014-2017 David Lingren
 *
 * @wordpress-plugin
 * Plugin Name: Smart Media Categories
 * Plugin URI:  http://davidlingren.com/
 * Description: Assigns taxonomy terms to Media Library items based on the terms of their parent post/page.
 * Version:     1.1.7
 * Author:      David Lingren
 * Author URI:  http://davidlingren.com/
 * Text Domain: smart-media-categories
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path: /languages
 *
 * Created for support topic "How do I assign all images in posts to the post's categories?"
 * opened on 10/24/2013 by "rosmo01".
 * https://wordpress.org/support/topic/how-do-i-assign-all-images-in-posts-to-the-posts-categories/
 *
 * Enhanced for support topic "assign taxonomies to attachments for a standard WP gallery"
 * opened on  8/24/2017 by "maxgx".
 * https://wordpress.org/support/topic/assign-taxonomies-to-attachments-for-a-standard-wp-gallery/
 *
 * Based on Tom McFarlin's "WordPress Plugin Boilerplate", v2.6.1
 *  - http://tommcfarlin.com/wordpress-plugin-boilerplate/
 *  - http://github.com/tommcfarlin/WordPress-Widget-Boilerplate
 *
 * The Settings page is based on Tom McFarlin's "WordPress Settings Sandbox"
 * - http://wp.tutsplus.com/series/the-complete-guide-to-the-wordpress-settings-api/
 * - http://tommcfarlin.com/wordpress-settings-api-example/
 * - https://github.com/tommcfarlin/WordPress-Settings-Sandbox
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/*----------------------------------------------------------------------------*
 * Public-Facing Functionality
 *----------------------------------------------------------------------------*/

// Plugin class for the public-facing side of the WordPress site.
require_once( plugin_dir_path( __FILE__ ) . 'public/class-smart-media-categories.php' );

/*
 * Register hooks that are fired when the plugin is activated or deactivated.
 * When the plugin is deleted, the uninstall.php file is loaded.
 */
register_activation_hook( __FILE__, array( 'Smart_Media_Categories', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Smart_Media_Categories', 'deactivate' ) );

// Create an instance of the public-facing class.
add_action( 'plugins_loaded', array( 'Smart_Media_Categories', 'get_instance' ) );

/*----------------------------------------------------------------------------*
 * Dashboard and Administrative Functionality
 *----------------------------------------------------------------------------*/

/*
 * Create an instance of the administrative-side class, if appropriate.
 *
 * If you want to include Ajax within the dashboard, change the following
 * conditional to:
 *
 * if ( is_admin() ) {
 *   ...
 * }
 *
 * The code below is intended to to give the lightest footprint possible.
 */

if ( is_admin() /* && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) */ ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-smart-media-categories-admin.php' );
	add_action( 'plugins_loaded', array( 'Smart_Media_Categories_Admin', 'get_instance' ) );
//error_log( __LINE__ . ' smart-media-categories.php is_admin() Support _REQUEST = ' . var_export( $_REQUEST, true ), 0 );
}

// WP REST API calls need everything loaded to process uploads
if ( isset( $_SERVER['REQUEST_URI'] ) && 0 === strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) ) { // phpcs:ignore
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-smart-media-categories-admin.php' );
	add_action( 'plugins_loaded', array( 'Smart_Media_Categories_Admin', 'get_instance' ) );
//error_log( __LINE__ . ' smart-media-categories.php /wp-json/ Support _REQUEST = ' . var_export( $_REQUEST, true ), 0 );
}



// Look for Postie chron job
if ( isset( $_REQUEST['doing_wp_cron'] ) && class_exists( 'Postie', false ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'admin/class-smart-media-categories-admin.php' );
	add_action( 'postie_session_start', array( 'Smart_Media_Categories_Admin', 'get_instance' ) );
//error_log( __LINE__ . ' smart-media-categories.php performed postie add_action', 0 );
}

?>