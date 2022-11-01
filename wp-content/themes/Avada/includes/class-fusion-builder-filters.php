<?php
/**
 * Additional filters for fusion-builder.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      5.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Set values in builder specific to Avada.
 */
class Fusion_Builder_Filters {

	/**
	 * A single instance of this object.
	 *
	 * @access private
	 * @since 5.0.0
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * The shortcode > option map description array.
	 *
	 * @access private
	 * @since 5.0.0
	 * @var array
	 */
	private static $shortcode_option_map_descriptions = [];

	/**
	 * Access the single instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 5.0.0
	 * @return Fusion_Builder_Filters
	 */
	public static function get_instance() {

		if ( null === self::$instance ) {
			self::$instance = new Fusion_Builder_Filters();
		}
		return self::$instance;

	}

	/**
	 * The class constructor.
	 *
	 * @access private
	 */
	private function __construct() {

		add_filter( 'fusion_builder_option_value', [ $this, 'set_builder_values' ], 10, 3 );
		add_filter( 'fusion_builder_option_dependency', [ $this, 'set_builder_dependencies' ], 10, 3 );
		add_filter( 'fusion_builder_width_hundred_percent', [ $this, 'is_post_width_hundred_percent' ] );
		add_filter( 'fusion_button_extras', [ $this, 'custom_color_extras' ] );
	}

	/**
	 * Set the custom color schemes for button view.
	 *
	 * @access public
	 * @since   6.0.0
	 * @param   array $extras extra params for view.
	 */
	public function custom_color_extras( $extras ) {
		if ( get_option( 'avada_custom_color_schemes' ) ) {
			$extras['custom_color_schemes'] = get_option( 'avada_custom_color_schemes' );
		}

		return $extras;

	}

	/**
	 * Set builder defaults from TO where necessary.
	 *
	 * @access public
	 * @since   5.0.0
	 * @param   string $value value currently being used.
	 * @param   string $shortcode name of shortcode.
	 * @param   string $option name of option.
	 * @return  string new default from theme options.
	 */
	public function set_builder_values( $value, $shortcode, $option ) {

		$shortcode_option_map = [];

		// If needs custom color schemes, add in.
		if ( ( 'color' === $option && 'fusion_button' === $shortcode ) || ( 'buttoncolor' === $option && 'fusion_tagline_box' === $shortcode ) ) {
			return Avada()->settings->get_custom_color_schemes( $value );
		}
		return $value;

	}

	/**
	 * Set builder dependencies, for those which involve TO.
	 *
	 * @since  5.0.0
	 * @param  array  $dependencies currently active dependencies.
	 * @param  string $shortcode name of shortcode.
	 * @param  string $option name of option.
	 * @return array  dependency checks.
	 */
	public function set_builder_dependencies( $dependencies, $shortcode, $option ) {
		$shortcode_option_map = [];

		// Sharing box.
		$shortcode_option_map['icons_boxed_radius']['fusion_sharing'][] = [
			'check'  => [
				'theme-option' => 'social_links_boxed',
				'value'        => '0',
				'operator'     => '==',
			],
			'output' => [
				'element'  => 'icons_boxed',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['box_colors']['fusion_sharing'][]         = [
			'check'  => [
				'theme-option' => 'social_links_boxed',
				'value'        => '0',
				'operator'     => '==',
			],
			'output' => [
				'element'  => 'icons_boxed',
				'value'    => '',
				'operator' => '!=',
			],
		];

		// If has TO related dependency, do checks.
		if ( isset( $shortcode_option_map[ $option ][ $shortcode ] ) && is_array( $shortcode_option_map[ $option ][ $shortcode ] ) ) {
			foreach ( $shortcode_option_map[ $option ][ $shortcode ] as $option_check ) {
				$option_value = Avada()->settings->get( $option_check['check']['theme-option'] );
				$pass         = false;

				// Check the result of check.
				if ( '==' === $option_check['check']['operator'] ) {
					$pass = (bool) ( $option_value == $option_check['check']['value'] ); // phpcs:ignore WordPress.PHP.StrictComparisons
				}
				if ( '!=' === $option_check['check']['operator'] ) {
					$pass = (bool) ( $option_value != $option_check['check']['value'] ); // phpcs:ignore WordPress.PHP.StrictComparisons
				}

				// If check passes then add dependency for checking.
				if ( $pass ) {
					$dependencies[] = $option_check['output'];
				}
			}
		}
		return $dependencies;
	}

	/**
	 * Checks if the current post is set to 100% width.
	 *
	 * @return bool
	 */
	public function is_post_width_hundred_percent() {
		global $post;

		$post_type = get_post_type( $post );

		switch ( $post_type ) {
			case 'avada_portfolio':
				return (bool) fusion_get_option( 'portfolio_width_100' );

			case 'post':
				return (bool) fusion_get_option( 'blog_width_100' );

			case 'product':
				return (bool) fusion_get_option( 'product_width_100' );

			default:
				return ( 'yes' === fusion_data()->post_meta( $post->ID )->get( 'blog_width_100' ) );
		}
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
