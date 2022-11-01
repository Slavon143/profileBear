<?php
/**
 * Plugin Name: Avada Builder
 * Plugin URI: https://theme-fusion.com
 * Description: The advanced, premium drag & drop Avada Website Builder. Streamline your work and save time for the more important things.
 * Version: 3.8.2
 * Author: ThemeFusion
 * Author URI: https://theme-fusion.com
 * Requires PHP: 5.6
 *
 * @package fusion-builder
 * @since 1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Developer mode.
if ( ! defined( 'FUSION_BUILDER_DEV_MODE' ) ) {
	define( 'FUSION_BUILDER_DEV_MODE', false );
}

// Plugin version.
if ( ! defined( 'FUSION_BUILDER_VERSION' ) ) {
	define( 'FUSION_BUILDER_VERSION', '3.8.2' );
}

// Minimum PHP version required.
if ( ! defined( 'FUSION_BUILDER_MIN_PHP_VER_REQUIRED' ) ) {
	define( 'FUSION_BUILDER_MIN_PHP_VER_REQUIRED', '5.6' );
}

// Minimum WP version required.
if ( ! defined( 'FUSION_BUILDER_MIN_WP_VER_REQUIRED' ) ) {
	define( 'FUSION_BUILDER_MIN_WP_VER_REQUIRED', '4.5' );
}

// Plugin Folder Path.
if ( ! defined( 'FUSION_BUILDER_PLUGIN_DIR' ) ) {
	define( 'FUSION_BUILDER_PLUGIN_DIR', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
}

// Plugin Folder URL.
if ( ! defined( 'FUSION_BUILDER_PLUGIN_URL' ) ) {
	define( 'FUSION_BUILDER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}

// Plugin Root File.
if ( ! defined( 'FUSION_BUILDER_PLUGIN_FILE' ) ) {
	define( 'FUSION_BUILDER_PLUGIN_FILE', wp_normalize_path( __FILE__ ) );
}

/**
 * Compatibility check.
 *
 * Check that the site meets the minimum requirements for the plugin before proceeding.
 *
 * @since 4.0
 */
if ( version_compare( $GLOBALS['wp_version'], FUSION_BUILDER_MIN_WP_VER_REQUIRED, '<' ) || version_compare( PHP_VERSION, FUSION_BUILDER_MIN_PHP_VER_REQUIRED, '<' ) ) {
	require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/bootstrap-compat.php';
	return;
}

/**
 * Bootstrap the plugin.
 *
 * @since 4.0
 */
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/bootstrap.php';
