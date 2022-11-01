<?php
/**
 * Compatibility tweaks for 3rd-party plugins.
 *
 * @since 2.0.2
 * @package fusion-library
 */

$request_uri             = ( isset( $_SERVER['REQUEST_URI'] ) ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
$fusion_is_builder_frame = ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), 'fb-edit' ) );
$fusion_is_preview_frame = ( $request_uri && false !== strpos( $request_uri, 'builder=true' ) || ( false !== strpos( $request_uri, 'builder_id' ) && false !== strpos( $request_uri, 'fbpreview=true' ) ) );

/**
 * Filters the option value for litespeed-cache.
 *
 * @since 2.0.2
 */
add_filter(
	'option_litespeed-cache-conf',
	/**
	 * Filters the option value, adding an exclusion of the builder preview.
	 *
	 * @since 2.0.2
	 * @param array $val The option value.
	 * @return array
	 */
	function( $val ) {
		$val = (array) $val;

		$val['excludes_qs'] = ( ! isset( $val['excludes_qs'] ) ) ? '' : $val['excludes_qs'];
		$val['excludes_qs'] = "builder=true\r\n" . $val['excludes_qs'];
		return $val;
	}
);

/**
 * Hooks on both the builder and preview frames.
 *
 * @since 2.0.2
 */
if ( $fusion_is_builder_frame || $fusion_is_preview_frame ) {

	/**
	 * Litespeed Cache.
	 *
	 * @since 2.0.2
	 */
	add_filter( 'litespeed_option_css_combine', '__return_empty_string', 999 );
	add_filter( 'litespeed_option_js_combine', '__return_empty_string', 999 );
	add_filter( 'litespeed_option_optm_js_defer', '__return_empty_string', 999 );

	/**
	 * WP-Smush.
	 *
	 * @since 2.0.2
	 */
	add_filter( 'wp_smush_should_skip_parse', '__return_true', 999 );

	/**
	 * Easy Social Share Buttons.
	 *
	 * @since 2.0.2
	 */
	add_filter( 'essb_live_customizer_can_run', '__return_false', 999 );

	/**
	 * Autoptimize.
	 *
	 * @since 2.0.2
	 */
	add_filter( 'autoptimize_filter_noptimize', '__return_true', 999 );

	add_action(
		'after_setup_theme',
		/**
		 * Run extra actions on after_setuo_theme.
		 *
		 * @since 2.0.2
		 */
		function() {
			global $revext;

			/**
			 * Essential grid when revslider is active.
			 *
			 * @since 2.0.2
			 */
			if ( $revext ) {
				remove_action( 'wp_footer', [ $revext, 'add_eg_additional_inline_javascript' ] );
			}
		}
	);
}

/**
 * Hooks specific to the builder frame.
 *
 * @since 2.0.2
 */
if ( $fusion_is_builder_frame ) {

	/**
	 * NextGen gallery.
	 *
	 * @since 2.0.2
	 */
	add_filter( 'run_ngg_resource_manager', '__return_false', 999 );

	add_action(
		'after_setup_theme',
		/**
		 * Run extra actions on after_setuo_theme.
		 *
		 * @since 2.0.2
		 */
		function() {
			global $smile_modals, $smile_info_bars, $smile_slide_ins;

			/**
			 * Convert plus global modals.
			 *
			 * @since 2.0.2
			 */
			if ( $smile_modals ) {
				remove_action( 'wp_footer', [ $smile_modals, 'load_modal_globally' ] );
			}

			/**
			 * Convert plus global info bars..
			 *
			 * @since 2.0.3
			 */
			if ( $smile_info_bars ) {
				remove_action( 'wp_footer', [ $smile_info_bars, 'load_info_bar_globally' ] );
			}

			/**
			 * Convert plus global info bars..
			 *
			 * @since 2.0.3
			 */
			if ( $smile_slide_ins ) {
				remove_action( 'wp_footer', [ $smile_slide_ins, 'load_slide_in_globally' ] );
			}
		}
	);
}
