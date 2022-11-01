<?php
/**
 * The Fusion_Builder_Dynamic_CSS class.
 * Handles generating the dynamic-css.
 *
 * @package fusion-builder
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle generating the dynamic CSS.
 *
 * @since 1.1.0
 */
class Fusion_Builder_Dynamic_CSS extends Fusion_Dynamic_CSS {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		parent::get_instance();

		if ( ! class_exists( 'Avada' ) ) {
			add_filter( 'fusion_dynamic_css', [ $this, 'custom_css' ] );
		}

		add_filter( 'fusion_dynamic_css_array', [ $this, 'fusion_builder_dynamic_css' ] );
		add_action( 'fusionredux/options/fusion_options/saved', [ $this, 'reset_all_caches' ], 999 );
	}

	/**
	 * Appends the custom-css option to the dynamic-css.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param string $css The final CSS.
	 * @return string
	 */
	public function custom_css( $css ) {

		// Append the user-entered dynamic CSS.
		$option = get_option( Fusion_Settings::get_option_name(), [] );
		if ( isset( $option['custom_css'] ) && ! empty( $option['custom_css'] ) ) {
			$css .= wp_strip_all_tags( $option['custom_css'] );
		}

		return $css;

	}

	/**
	 * Format of the $css array:
	 * $css['media-query']['element']['property'] = value
	 *
	 * If no media query is required then set it to 'global'
	 *
	 * If we want to add multiple values for the same property then we have to make it an array like this:
	 * $css[media-query][element]['property'][] = value1
	 * $css[media-query][element]['property'][] = value2
	 *
	 * Multiple values defined as an array above will be parsed separately.
	 *
	 * @param array $original_css The existing CSS.
	 */
	public function fusion_builder_dynamic_css( $original_css = [] ) {
		$fusion_settings     = awb_get_fusion_settings();
		$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
		$dynamic_css_helpers = $dynamic_css->get_helpers();
		$css                 = [];

		$info_background_color = '' !== $fusion_settings->get( 'info_bg_color' ) ? strtolower( $fusion_settings->get( 'info_bg_color' ) ) : '#ffffff';
		$info_accent_color     = $fusion_settings->get( 'info_accent_color' );

		$danger_background_color = '' !== $fusion_settings->get( 'danger_bg_color' ) ? strtolower( $fusion_settings->get( 'danger_bg_color' ) ) : '#f2dede';
		$danger_accent_color     = $fusion_settings->get( 'danger_accent_color' );

		$success_background_color = '' !== $fusion_settings->get( 'success_bg_color' ) ? strtolower( $fusion_settings->get( 'success_bg_color' ) ) : '#dff0d8';
		$success_accent_color     = $fusion_settings->get( 'success_accent_color' );

		$warning_background_color = '' !== $fusion_settings->get( 'warning_bg_color' ) ? strtolower( $fusion_settings->get( 'warning_bg_color' ) ) : '#fcf8e3';
		$warning_accent_color     = $fusion_settings->get( 'warning_accent_color' );

		$alert_box_text_align = $fusion_settings->get( 'alert_box_text_align' ) ? $fusion_settings->get( 'alert_box_text_align' ) : '';
		$alert_text_transform = $fusion_settings->get( 'alert_text_transform' ) ? $fusion_settings->get( 'alert_text_transform' ) : 'normal';
		$alert_border_size    = $fusion_settings->get( 'alert_border_size' ) ? Fusion_Sanitize::size( $fusion_settings->get( 'alert_border_size' ), 'px' ) : '1px';

		if ( class_exists( 'GFForms' ) ) {
			$elements = [
				'.gform_wrapper .gfield_error .gfield_validation_message',
				'.gform_wrapper .gform_validation_errors',
			];

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['text-align']     = $alert_box_text_align;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['text-transform'] = $alert_text_transform;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border']         = $alert_border_size . ' solid ' . $danger_accent_color;

			if ( 'yes' === $fusion_settings->get( 'alert_box_shadow' ) ) {
				$css['global']['.gform_wrapper .gform_validation_errors']['box-shadow'] = '0 1px 1px rgba(0, 0, 0, 0.1)';
			}

			$elements = [
				'.gform_wrapper .gfield_required',
				'.gform_wrapper .gfield_error label',
				'.gform_wrapper .gfield_error .gfield_validation_message',
			];

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = $danger_accent_color;
		}

		if ( defined( 'WPCF7_PLUGIN' ) ) {
			// CF7 error notice.
			$elements = [
				'.wpcf7 .wpcf7-form.invalid .wpcf7-response-output',
				'.wpcf7 .wpcf7-form.unaccepted .wpcf7-response-output',
				'.wpcf7 .wpcf7-form.spam .wpcf7-response-output',
				'.wpcf7 .wpcf7-form.failed .wpcf7-response-output',
			];

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $danger_background_color;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border']           = $alert_border_size . ' solid ' . $danger_accent_color;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']            = $danger_accent_color;

			// CF7 success notice.
			$css['global']['.wpcf7 .wpcf7-form.sent .wpcf7-response-output']['background-color'] = $success_background_color;
			$css['global']['.wpcf7 .wpcf7-form.sent .wpcf7-response-output']['border']           = $alert_border_size . ' solid ' . $success_accent_color;
			$css['global']['.wpcf7 .wpcf7-form.sent .wpcf7-response-output']['color']            = $success_accent_color;
		}

		if ( class_exists( 'WooCommerce' ) ) {
			// WooCommerce error notice.
			$css['global']['.woocommerce-error li']['background-color'] = $danger_background_color;
			$css['global']['.woocommerce-error li']['border']           = $alert_border_size . ' solid ' . $danger_accent_color;
			$css['global']['.woocommerce-error li']['color']            = $danger_accent_color;

			// WooCommerce general notice.
			$elements = [
				'.woocommerce .woocommerce-info',
				'.woocommerce .woocommerce-message',
				'.woocommerce .return-to-shop',
			];

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $info_background_color;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top']       = $alert_border_size . ' solid ' . $info_accent_color;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom']    = $alert_border_size . ' solid ' . $info_accent_color;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']            = $info_accent_color;

			$elements = [
				'.validate-required.woocommerce-invalid input',
				'.validate-required.woocommerce-invalid .select2-selection--single',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['box-shadow'] = 'inset 3px 0 0 ' . $danger_accent_color . '!important';

			$elements = [
				'.validate-required.woocommerce-validated input',
				'.validate-required.woocommerce-validated .select2-selection--single',
			];
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['box-shadow'] = 'inset 3px 0 0 ' . $success_accent_color . '!important';
		}

		if ( class_exists( 'bbPress' ) ) {
			// bbPress general notice.
			$elements = [
				'div.bbp-template-notice',
				'div.indicator-hint',
			];

			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = $info_background_color;
			$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border']     = $alert_border_size . ' solid ' . $info_accent_color;

		}

		// FIXME: Block below should get revisited as it might not be needed at all.
		// General notice incl. Events.
		$elements = [
			'.fusion-alert.alert-info',
			'.tribe-events-notices',
		];

		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = $info_background_color;
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = $info_accent_color;
		$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color']            = $info_accent_color;

		// Error notice.
		$css['global']['.alert-danger']['background-color'] = $danger_background_color;
		$css['global']['.alert-danger']['border-color']     = $danger_accent_color;
		$css['global']['.alert-danger']['color']            = $danger_accent_color;

		// Success notice.
		$css['global']['.alert-success']['background-color'] = $success_background_color;
		$css['global']['.alert-success']['border-color']     = $success_accent_color;
		$css['global']['.alert-success']['color']            = $success_accent_color;

		// Warning notice.
		$css['global']['.alert-warning']['background-color'] = $warning_background_color;
		$css['global']['.alert-warning']['border-color']     = $warning_accent_color;
		$css['global']['.alert-warning']['color']            = $warning_accent_color;

		// End FIXME.
		$css = array_replace_recursive( $css, $original_css );

		return apply_filters( 'avada_dynamic_css_array', $css );

	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
