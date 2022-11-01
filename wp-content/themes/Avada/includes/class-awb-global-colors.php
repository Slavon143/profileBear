<?php
/**
 * Global color palette handliing.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      7.6
 * @phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 */

/**
 * Setup wizard handling.
 *
 * @since 7.6
 */
class AWB_Global_Colors {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 7.6
	 * @var object
	 */
	private static $instance;

	/**
	 * Palette data.
	 *
	 * @static
	 * @access private
	 * @since 7.6
	 * @var object
	 */
	public $palette;

	/**
	 * Whether we have enqueued already or not.
	 *
	 * @static
	 * @access private
	 * @since 7.6
	 * @var boolean
	 */
	public $enqueued = false;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 7.6
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Global_Colors();
		}
		return self::$instance;
	}

	/**
	 * The class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', [ $this, 'set_variables' ], 5 );

		// 'wp_enqueue_scripts' is used because in live-editor 'admin_enqueue_scripts' does not work.
		add_action( 'wp_enqueue_scripts', [ $this, 'print_inline_global_colors_declaration_in_backend' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'print_inline_global_colors_declaration_in_backend' ] );
	}

	/**
	 * Get palette defaults.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function get_defaults() {
		return [
			'color1' => [
				'color' => 'rgba(255,255,255,1)',
				'label' => esc_html__( 'Color 1', 'Avada' ),
			],
			'color2' => [
				'color' => 'rgba(249,249,251,1)',
				'label' => esc_html__( 'Color 2', 'Avada' ),
			],
			'color3' => [
				'color' => 'rgba(242,243,245,1)',
				'label' => esc_html__( 'Color 3', 'Avada' ),
			],
			'color4' => [
				'color' => 'rgba(101,189,125,1)',
				'label' => esc_html__( 'Color 4', 'Avada' ),
			],
			'color5' => [
				'color' => 'rgba(25,143,217,1)',
				'label' => esc_html__( 'Color 5', 'Avada' ),
			],
			'color6' => [
				'color' => 'rgba(67,69,73,1)',
				'label' => esc_html__( 'Color 6', 'Avada' ),
			],
			'color7' => [
				'color' => 'rgba(33,35,38,1)',
				'label' => esc_html__( 'Color 7', 'Avada' ),
			],
			'color8' => [
				'color' => 'rgba(20,22,23,1)',
				'label' => esc_html__( 'Color 8', 'Avada' ),
			],
		];
	}

	/**
	 * Get palette data.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function get_palette() {
		if ( null !== $this->palette ) {
			return $this->palette;
		}

		// Only merge for valid palette data.
		$data = fusion_library()->get_option( 'color_palette' );
		if ( is_array( $data ) ) {
			$this->palette = array_merge( $this->get_defaults(), $data );
		} else {
			$this->palette = $this->get_defaults();
		}

		$this->palette = apply_filters( 'awb-color-palette-data', $this->palette ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		return $this->palette;
	}

	/**
	 * Get a color object by slug. False if doesn't exist.
	 *
	 * @since 7.6
	 * @param string $color_slug Color slug.
	 * @return array|false False if the slug doesn't exist.
	 */
	public function get_color_by_slug( $color_slug ) {
		$color_palette = $this->get_palette();
		if ( isset( $color_palette[ $color_slug ] ) ) {
			return $color_palette[ $color_slug ];
		}

		return false;
	}

	/**
	 * Get the default color in case a color needed does not exists.
	 *
	 * @since 7.6
	 * @return array
	 */
	public function get_fallback_error_color() {
		return [
			'color' => '#ffffff',
			'label' => esc_html__( 'Unknown Color', 'Avada' ),
		];
	}


	/**
	 * Get an inline style declaration with all global colors.
	 *
	 * @since 7.6
	 * @return string
	 */
	public function get_global_colors_declaration_for_inline_style() {
		$css = '';

		$palette = $this->get_palette();
		if ( ! is_array( $palette ) || empty( $palette ) ) {
			return $css;
		}

		foreach ( $palette as $color_slug => $data ) {
			$color_object = Fusion_Color::new_color( $data['color'] );

			$css .= '--awb-' . $color_slug . ':' . $data['color'] . ';';

			$css .= '--awb-' . $color_slug . '-h:' . $color_object->hue . ';';
			$css .= '--awb-' . $color_slug . '-s:' . $color_object->saturation . '%;';
			$css .= '--awb-' . $color_slug . '-l:' . $color_object->lightness . '%;';
			$css .= '--awb-' . $color_slug . '-a:' . ( $color_object->alpha * 100 ) . '%;';
		}

		if ( function_exists( 'awb_get_fusion_settings' ) ) {
			$fusion_settings = awb_get_fusion_settings();
			$primary_color   = $fusion_settings->get( 'primary_color' );
			$css            .= '--primary_color:' . $primary_color . ';';
		}

		if ( $css ) {
			$css = ':root{' . $css . '}';
		}

		return $css;
	}

	/**
	 * Print the inline style declaration for the global colors.
	 *
	 * @since 7.6
	 */
	public function print_inline_global_colors_declaration_in_backend() {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		if ( ! is_admin() && ! $is_builder ) {
			return;
		}

		echo '<style id="awb-global-colors">' . $this->get_global_colors_declaration_for_inline_style() . '</style>'; // phpcs:ignore WordPress.Security
	}

	/**
	 * Set variable definitions for each color in palette.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function set_variables() {
		$palette = $this->get_palette();
		if ( is_array( $palette ) && ! empty( $palette ) ) {
			foreach ( $palette as $color_slug => $data ) {
				Fusion_Dynamic_CSS::add_css_var(
					[
						'name'     => '--awb-' . $color_slug,
						'value'    => $data['color'],
						'element'  => ':root',
						'preserve' => true,
					]
				);

				// Create separate HSLA variables.
				$color_object = Fusion_Color::new_color( $data['color'] );
				Fusion_Dynamic_CSS::add_css_var(
					[
						'name'     => '--awb-' . $color_slug . '-h',
						'value'    => $color_object->hue,
						'element'  => ':root',
						'preserve' => true,
					]
				);
				Fusion_Dynamic_CSS::add_css_var(
					[
						'name'     => '--awb-' . $color_slug . '-s',
						'value'    => $color_object->saturation . '%',
						'element'  => ':root',
						'preserve' => true,
					]
				);
				Fusion_Dynamic_CSS::add_css_var(
					[
						'name'     => '--awb-' . $color_slug . '-l',
						'value'    => $color_object->lightness . '%',
						'element'  => ':root',
						'preserve' => true,
					]
				);
				Fusion_Dynamic_CSS::add_css_var(
					[
						'name'     => '--awb-' . $color_slug . '-a',
						'value'    => ( $color_object->alpha * 100 ) . '%',
						'element'  => ':root',
						'preserve' => true,
					]
				);
			}
		}
	}

	/**
	 * Enqueue the picker.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function enqueue() {
		if ( $this->enqueued ) {
			return;
		}

		wp_register_script(
			'iris',
			admin_url( 'js/iris.min.js' ),
			[ 'jquery', 'jquery-color', 'jquery-touch-punch' ],
			AVADA_VERSION,
			true
		);

		wp_enqueue_script(
			'awb-palette',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/awbPalette.js',
			[ 'jquery', 'jquery-color', 'underscore' ],
			AVADA_VERSION,
			true
		);
		wp_localize_script(
			'awb-palette',
			'awbPalette',
			[
				'data'             => $this->get_palette(),
				'global'           => esc_html__( 'Global Color', 'Avada' ),
				'unknownColor'     => esc_html__( 'Unknown Color', 'Avada' ),
				'removeColorAlert' => esc_html__( 'This will remove the color from the palette.', 'Avada' ),
				'goLink'           => esc_url_raw( admin_url( 'themes.php?page=avada_options' ) ),
			]
		);

		wp_enqueue_script(
			'awb-color-picker',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/awb-color-picker.js',
			[ 'underscore', 'iris', 'awb-palette' ],
			AVADA_VERSION,
			true
		);


		wp_enqueue_style(
			'awb-color-picker',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/css/awb-color-picker.css',
			[],
			AVADA_VERSION
		);

		if ( is_rtl() ) {
			wp_enqueue_style(
				'awb-color-picker-rtl',
				FUSION_LIBRARY_URL . '/inc/fusion-app/assets/css/awb-color-picker-rtl.css',
				[],
				AVADA_VERSION
			);
		}

		$this->enqueued = true;
	}

	/**
	 * Giving a css var value, try to get the color slug that is using it. Return
	 * false if the value doesn't contain any global CSS var inside.
	 *
	 * Ex: get_color_slug_from_css_var( 'var(--awb-color1)' ) -> 'color1'.
	 * get_color_slug_from_css_var( 'hsla(var(--awb-color8-h),var(...etc' ) -> 'color8'.
	 * get_color_slug_from_css_var( 'rgba(0,0,0,1)' ) -> false.
	 *
	 * @since 7.6
	 * @param string $css_value The full css value property.
	 * @return string|false The color slug, or false if not using a global value.
	 */
	public function get_color_slug_from_css_var( $css_value ) {
		if ( strpos( $css_value, 'var(--awb-' ) === false ) {
			return false;
		}

		$is_hsla = preg_match( '/^\s*hsla\s*\(/i', $css_value );

		if ( $is_hsla ) {
			preg_match( '/var\s*\(\s*--awb-\w+-h\W.*var\s*\(\s*--awb-(\w+)-s\W/i', $css_value, $matches );
			if ( isset( $matches[1] ) ) {
				return $matches[1];
			} else {
				return false;
			}
		} elseif ( preg_match( '/var\s*\(\s*--awb-(\w+)/', $css_value, $matches ) ) {
			if ( isset( $matches[1] ) ) {
				return $matches[1];
			} else {
				return false;
			}
		}

		return false;
	}

}

/**
 * Instantiates the AWB_Global_Colors class.
 * Make sure the class is properly set-up.
 *
 * @since 7.6
 * @return AWB_Global_Colors
 */
function AWB_Global_Colors() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Global_Colors::get_instance();
}
AWB_Global_Colors();
