<?php
/**
 * Avada Builder underscore.js templates.
 *
 * @package fusion-builder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the pagebuilder metabox.
 */
function fusion_pagebuilder_meta_box() {

	global $post;

	// Add RTL CSS class.
	$rtl_class = ( is_rtl() ) ? 'fusion-builder-layout-rtl' : '';

	do_action( 'fusion_builder_before' );

	wp_nonce_field( 'fusion_builder_template', 'fusion_builder_nonce' );

	// Custom CSS.
	$saved_custom_css = esc_attr( get_post_meta( $post->ID, '_fusion_builder_custom_css', true ) );
	$has_custom_css   = ( ! empty( $saved_custom_css ) ) ? 'fusion-builder-has-custom-css' : '';
	?>

	<div id="fusion_builder_main_container" class="<?php echo esc_attr( $rtl_class ); ?>" data-post-id="<?php echo esc_attr( $post->ID ); ?>"></div>
	<?php
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/app.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/multi-element-sortable-child.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/blank-page.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/container.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/row.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/nested-row.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/modal.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/column.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/nested-column.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/nested-column-library.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/column-library.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/element-library.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/generator-elements.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/element.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/element-settings.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/next-page.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/context-menu.php';
	include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/dynamic-selection.php';
	include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/repeater-fields.php';
	include FUSION_LIBRARY_PATH . '/inc/fusion-app/templates/bulk-add.php';

	if ( class_exists( 'WooCommerce' ) ) {
		include FUSION_BUILDER_PLUGIN_DIR . '/inc/templates/woo-checkout-form.php';
	}
	do_action( 'fusion_builder_after' );
}
