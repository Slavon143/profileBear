<?php
/**
 * Adds Custom Icon functionality.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      2.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

if ( ! defined( 'FUSION_ICONS_BASE_DIR' ) || ! defined( 'FUSION_ICONS_BASE_URL' ) ) {
	$upload_dir = wp_upload_dir();

	if ( ! defined( 'FUSION_ICONS_BASE_DIR' ) ) {
		define( 'FUSION_ICONS_BASE_DIR', trailingslashit( $upload_dir['basedir'] ) . 'fusion-icons/' );
	}

	if ( ! defined( 'FUSION_ICONS_BASE_URL' ) ) {
		define( 'FUSION_ICONS_BASE_URL', trailingslashit( $upload_dir['baseurl'] ) . 'fusion-icons/' );
	}
}

require_once FUSION_LIBRARY_PATH . '/inc/custom-icons/custom-icons-helpers.php';
require_once FUSION_LIBRARY_PATH . '/inc/custom-icons/class-fusion-custom-icon-set.php';


// Create object and add custom icons feature.
$fusion_custom_icons = Fusion_Custom_Icon_Set::get_instance();

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
