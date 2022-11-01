<?php
/**
 * Handler for dynamic-css.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle generating the dynamic CSS.
 */
class Avada_Dynamic_CSS {

	/**
	 * The Fusion_Dynamic_CSS object.
	 *
	 * @access protected
	 * @since 5.1.0
	 * @var object
	 */
	protected $fusion_dynamic_css;

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		$this->fusion_dynamic_css = Fusion_Dynamic_CSS::get_instance();
		add_action( 'wp', [ $this, 'init' ] );

	}

	/**
	 * Add extra actions.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function init() {

		if ( ! function_exists( 'fusion_is_preview_frame' ) || ! fusion_is_preview_frame() ) {
			// If CSS is cached in file or async media queries are turned off load custom CSS in make_css(), otherwise use
			// fusion_library_inline_dynamic_css filter.
			if ( '0' === Avada()->settings->get( 'media_queries_async' ) || 'file' === Avada()->settings->get( 'css_cache_method' ) ) {
				add_filter( 'fusion_dynamic_css_final', [ $this, 'fusion_add_custom_css_to_dynamic' ], 999 );
			} else {
				add_filter( 'fusion_library_inline_custom_css', [ $this, 'fusion_add_custom_css_to_dynamic' ] );
			}
		} else {
			/**
			* We're in FB preview mode.
			*  Print custom CSS after 'inline' CSS.
			*/
			add_action( 'wp_head', [ $this, 'fusion_dynamic_css_head' ], 1000 );
		}
		// Backwards-compatibility.
		add_filter( 'fusion_dynamic_css', [ $this, 'avada_dynamic_css_filter' ] );

	}

	/**
	 * Appends the custom-css option to the dynamic-css.
	 *
	 * @access public
	 * @since 5.1
	 * @param string $css The final CSS.
	 * @return string
	 */
	public function fusion_add_custom_css_to_dynamic( $css ) {

		// Append the user-entered dynamic CSS.
		$option = get_option( Avada::get_option_name(), [] );
		if ( isset( $option['custom_css'] ) && ! empty( $option['custom_css'] ) ) {
			$css .= wp_strip_all_tags( $option['custom_css'] );
		}
		return $css;

	}

	/**
	 * Prints the custom-css option to the <head> section.
	 * For example when page is displayed in FB preview frame.
	 *
	 * @access public
	 * @since 5.1
	 */
	public function fusion_dynamic_css_head() {

		$css = '';

		// Append the user-entered dynamic CSS.
		$option = get_option( Avada::get_option_name(), [] );
		if ( isset( $option['custom_css'] ) && ! empty( $option['custom_css'] ) ) {
			$css = wp_strip_all_tags( $option['custom_css'] );
		}

		echo '<style type="text/css" id="fusion-builder-custom-css">' . $css . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Backwards-compatibility:
	 * 'avada_dynamic_css' filter was replaced by 'fusion_dynamic_css'.
	 *
	 * @access public
	 * @since 5.1
	 * @param string $css The final CSS.
	 * @return string
	 */
	public function avada_dynamic_css_filter( $css ) {
		return apply_filters( 'avada_dynamic_css', $css );
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
