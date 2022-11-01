<?php
/**
 * Bootstrap the plugin.
 *
 * @since 2.0
 * @package fusion-builder
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

register_activation_hook( FUSION_BUILDER_PLUGIN_FILE, [ 'FusionBuilder', 'activation' ] );
register_deactivation_hook( FUSION_BUILDER_PLUGIN_FILE, [ 'FusionBuilder', 'deactivation' ] );

if ( ! class_exists( 'FusionBuilder' ) ) {
	require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-builder.php';
}

/**
 * Instantiates the FusionBuilder class.
 * Make sure the class is properly set-up.
 * The FusionBuilder class is a singleton
 * so we can directly access the one true FusionBuilder object using this function.
 *
 * @return object FusionBuilder
 */
function FusionBuilder() { // phpcs:ignore WordPress.NamingConventions
	return FusionBuilder::get_instance();
}

/**
 * Instantiate FusionBuilder class.
 */
function fusion_builder_activate() {

	// Include Fusion-Library.
	include_once FUSION_BUILDER_PLUGIN_DIR . 'inc/lib/fusion-library.php';
	do_action( 'fb_library_loaded' );
	FusionBuilder::get_instance();
}
add_action( 'after_setup_theme', 'fusion_builder_activate' );

// This needs loaded early for the filters to properly work.
require_once FUSION_BUILDER_PLUGIN_DIR . 'front-end/class-fusion-builder-front.php';

/**
 * TODO: example of adding FB options section with filter.
 *
 * @param  array $options Sections added by filter.
 * @return array $options Blog settings.
 */
function fusion_builder_add_elements_options( $options ) {
	$options['elements'] = FUSION_BUILDER_PLUGIN_DIR . 'inc/options/elements.php';

	return $options;
}

/**
 * Include element helper class.
 *
 * @since 2.1
 */
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-animation-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-border-radius-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-box-shadow-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-text-shadow-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-filter-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-margin-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-padding-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-sticky-visibility-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-gradient-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-form-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-form-logics-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-conditional-render-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-transform-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-pattern-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/helpers/class-fusion-builder-mask-helper.php';
require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-builder-element-helper.php';

require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-dummy-post.php';

/**
 * Init the languages updater.
 *
 * @since 2.1
 */
if ( ! class_exists( 'Fusion_Languages_Updater_API' ) ) {
	require_once FUSION_BUILDER_PLUGIN_DIR . 'inc/class-fusion-languages-updater-api.php';
}
new Fusion_Languages_Updater_API( 'plugin', 'fusion-builder', FUSION_BUILDER_VERSION );
