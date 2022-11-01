<?php
/**
 * Bootstrap the plugin.
 *
 * @since 6.0
 * @package Avada-Core
 * @subpackage Core
 */

// Load the instance of the plugin.
if ( ! class_exists( 'FusionCore_Plugin' ) ) {
	require_once FUSION_CORE_PATH . '/includes/class-fusioncore-plugin.php';
}
add_action( 'plugins_loaded', [ 'FusionCore_Plugin', 'get_instance' ] );

/**
 * Setup Avada Slider.
 *
 * @since 3.1
 * @return void
 */
function setup_fusion_slider() {
	$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

	if ( ! $fusion_settings || '0' !== $fusion_settings->get( 'status_fusion_slider' ) ) {
		include_once FUSION_CORE_PATH . '/fusion-slider/class-fusion-slider.php';
	}
}
// Setup Avada Slider.
add_action( 'after_setup_theme', 'setup_fusion_slider', 10 );

/**
 * Find and include all shortcodes within shortcodes folder.
 *
 * @since 3.1
 * @return void
 */
function fusion_init_shortcodes() {
	if ( class_exists( 'Avada' ) ) {

		$fusion_settings = awb_get_fusion_settings();

		$filenames = glob( FUSION_CORE_PATH . '/shortcodes/*.php', GLOB_NOSORT );
		foreach ( $filenames as $filename ) {
			$info = pathinfo( $filename );

			if ( 'fusion-portfolio' === $info['filename'] && '0' === $fusion_settings->get( 'status_fusion_portfolio' ) || 'fusion-faq' === $info['filename'] && '0' === $fusion_settings->get( 'status_fusion_faqs' ) ) {
				continue;
			}
			require_once wp_normalize_path( $filename );
		}
	}
}
// Load all shortcode elements.
add_action( 'fusion_builder_shortcodes_init', 'fusion_init_shortcodes' );

/**
 * Enable Avada Builder elements on activation.
 *
 * @access public
 * @since 3.1
 * @return void
 */
function fusion_core_enable_elements() {
	if ( function_exists( 'fusion_builder_auto_activate_element' ) && version_compare( FUSION_BUILDER_VERSION, '1.0.6', '>' ) ) {
		fusion_builder_auto_activate_element( 'fusion_portfolio' );
		fusion_builder_auto_activate_element( 'fusion_faq' );
		fusion_builder_auto_activate_element( 'fusion_fusionslider' );
		fusion_builder_auto_activate_element( 'fusion_privacy' );
		fusion_builder_auto_activate_element( 'fusion_tb_project_details' );
	}
}

register_activation_hook( FUSION_CORE_MAIN_PLUGIN_FILE, 'fusion_core_activation' );
register_deactivation_hook( FUSION_CORE_MAIN_PLUGIN_FILE, 'fusion_core_deactivation' );

/**
 * Runs on fusion core activation hook.
 */
function fusion_core_activation() {

	// Reset patcher on activation.
	fusion_core_reset_patcher_counter();

	// Enable fusion core elements on activation.
	fusion_core_enable_elements();
}

/**
 * Runs on fusion core deactivation hook.
 */
function fusion_core_deactivation() {
	// Reset patcher on deactivation.
	fusion_core_reset_patcher_counter();

	// Delete the option to flush rewrite rules after activation.
	delete_option( 'fusion_core_flush_permalinks' );
}

/**
 * Resets the patcher counters.
 */
function fusion_core_reset_patcher_counter() {
	delete_site_transient( 'fusion_patcher_check_num' );
}

/**
 * Add content filter if WPTouch is active.
 *
 * @access public
 * @since 3.1.1
 * @return void
 */
function fusion_wptouch_compatiblity() {
	global $wptouch_pro;
	if ( true === $wptouch_pro->is_mobile_device ) {
		add_filter( 'the_content', 'fusion_remove_orphan_shortcodes', 0 );
	}
}
add_action( 'wptouch_pro_loaded', 'fusion_wptouch_compatiblity', 11 );

/**
 * Add custom thumnail column.
 *
 * @since 5.3
 * @access public
 * @param array $existing_columns Array of existing columns.
 * @return array The modified columns array.
 */
function fusion_wp_list_add_column( $existing_columns ) {

	if ( ! class_exists( 'Avada' ) ) {
		return $existing_columns;
	}

	$columns = [
		'cb'           => $existing_columns['cb'],
		'tf_thumbnail' => '<span class="dashicons dashicons-format-image"><span class="fusion-posts-image-tip">' . esc_attr__( 'Image', 'fusion-core' ) . '</span></span>',
	];

	return array_merge( $columns, $existing_columns );
}
// Add thumbnails to blog, Avada Slider and Elastic Slider.
add_filter( 'manage_post_posts_columns', 'fusion_wp_list_add_column', 10 );
add_filter( 'manage_slide_posts_columns', 'fusion_wp_list_add_column', 10 );
add_filter( 'manage_themefusion_elastic_posts_columns', 'fusion_wp_list_add_column', 10 );

/**
 * Renders the contents of the thumbnail column.
 *
 * @since 5.3
 * @access public
 * @param string $column current column name.
 * @param int    $post_id cureent post ID.
 * @return void
 */
function fusion_add_thumbnail_in_column( $column, $post_id ) {

	if ( ! class_exists( 'Avada' ) ) {
		return;
	}

	switch ( $column ) {
		case 'tf_thumbnail':
			echo '<a href="' . esc_url_raw( get_edit_post_link( $post_id ) ) . '">';
			if ( has_post_thumbnail( $post_id ) ) {
				echo get_the_post_thumbnail( $post_id, 'thumbnail' );
			} else {
				echo '<img  src="data:image/png;base64,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">';
			}
			echo '</a>';

			break;
	}
}
add_action( 'manage_post_posts_custom_column', 'fusion_add_thumbnail_in_column', 10, 2 );
add_action( 'manage_slide_posts_custom_column', 'fusion_add_thumbnail_in_column', 10, 2 );
add_action( 'manage_themefusion_elastic_posts_custom_column', 'fusion_add_thumbnail_in_column', 10, 2 );

/**
 * Removes unregistered shortcodes.
 *
 * @access public
 * @since 3.1.1
 * @param string $content item content.
 * @return string
 */
function fusion_remove_orphan_shortcodes( $content ) {

	if ( false === strpos( $content, '[fusion' ) ) {
		return $content;
	}

	global $shortcode_tags;

	// Check for active shortcodes.
	$active_shortcodes = ( is_array( $shortcode_tags ) && ! empty( $shortcode_tags ) ) ? array_keys( $shortcode_tags ) : [];

	// Avoid "/" chars in content breaks preg_replace.
	$unique_string_one = md5( microtime() );
	$content           = str_replace( '[/fusion_', $unique_string_one, $content );

	$unique_string_two = md5( microtime() + 1 );
	$content           = str_replace( '/fusion_', $unique_string_two, $content );
	$content           = str_replace( $unique_string_one, '[/fusion_', $content );

	if ( ! empty( $active_shortcodes ) ) {
		// Be sure to keep active shortcodes.
		$keep_active = implode( '|', $active_shortcodes );
		$content     = preg_replace( '~(?:\[/?)(?!(?:' . $keep_active . '))[^/\]]+/?\]~s', '', $content );
	} else {
		// Strip all shortcodes.
		$content = preg_replace( '~(?:\[/?)[^/\]]+/?\]~s', '', $content );

	}

	// Set "/" back to its place.
	$content = str_replace( $unique_string_two, '/', $content );

	return $content;
}

/**
 * Remove post type from the link selector.
 *
 * @since 1.0
 * @param array $query Default query for link selector.
 * @return array $query
 */
function fusion_core_wp_link_query_args( $query ) {

	// Get array key for the post type 'slide'.
	$slide_post_type_key = array_search( 'slide', $query['post_type'], true );

	// Remove the post type from query.
	if ( $slide_post_type_key ) {
		unset( $query['post_type'][ $slide_post_type_key ] );
	}

	// Get array key for the post type 'themefusion_elastic'.
	$elastic_slider_post_type_key = array_search( 'themefusion_elastic', $query['post_type'], true );

	// Remove the post type from query.
	if ( $elastic_slider_post_type_key ) {
		unset( $query['post_type'][ $elastic_slider_post_type_key ] );
	}

	// Return updated query.
	return $query;
}

add_filter( 'wp_link_query_args', 'fusion_core_wp_link_query_args' );


/**
 * Add Template Builder extensions.
 *
 * @since 2.2
 */
require_once FUSION_CORE_PATH . '/includes/class-fusioncore-template-builder.php';

/**
 * Init the languages updater.
 *
 * @since 4.1
 */
if ( ! class_exists( 'Fusion_Languages_Updater_API' ) ) {
	require_once FUSION_CORE_PATH . '/includes/class-fusion-languages-updater-api.php';
}
new Fusion_Languages_Updater_API( 'plugin', 'fusion-core', FUSION_CORE_VERSION );

/**
 * Filter WooCommerce Bundled Product plugin compatibility modules.
 *
 * @access public
 * @since 5.1
 *
 * @param array $module_paths The compatibility module paths.
 * @return array The filteres compatibility module paths.
 */
function fusion_core_woocommerce_bundles_compatibility_modules( $module_paths ) {
	if ( ! isset( $module_paths['quickview'] ) ) {
		$module_paths['quickview'] = 'modules/class-wc-pb-qv-compatibility.php';
	}

	return $module_paths;
}
add_filter( 'woocommerce_bundles_compatibility_modules', 'fusion_core_woocommerce_bundles_compatibility_modules', 10000 );
