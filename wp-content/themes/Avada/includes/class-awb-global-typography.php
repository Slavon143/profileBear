<?php
/**
 * Global typography handliing.
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
 * Global typography.
 *
 * @since 7.6
 */
class AWB_Global_Typography {

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
	 * Typography data.
	 *
	 * @static
	 * @access private
	 * @since 7.6
	 * @var object
	 */
	public $typography;

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
	 * Variable values.
	 *
	 * @static
	 * @access private
	 * @since 7.6
	 * @var array
	 */
	public $values = [];

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
			self::$instance = new AWB_Global_Typography();
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
		add_filter( 'fusion_google_fonts', [ $this, 'filter_fonts' ], 2051 );

		// 'wp_enqueue_scripts' is used because in live-editor 'admin_enqueue_scripts' does not work.
		add_action( 'wp_enqueue_scripts', [ $this, 'print_inline_global_typography_declaration_in_backend' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'print_inline_global_typography_declaration_in_backend' ] );
	}

	/**
	 * Get the typography defaults.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function get_defaults() {
		return [
			'typography1' => [
				'label'          => 'Headings',
				'font-family'    => 'Inter',
				'font-backup'    => 'Arial, Helvetica, sans-serif',
				'font-size'      => '46px',
				'variant'        => '600',
				'font-weight'    => '600',
				'font-style'     => '',
				'line-height'    => '1.2',
				'letter-spacing' => '-0.015em',
				'text-transform' => 'none',
				'not_removable'  => true,
			],
			'typography2' => [
				'label'          => 'Subheadings',
				'font-family'    => 'Inter',
				'font-backup'    => 'Arial, Helvetica, sans-serif',
				'font-size'      => '24px',
				'variant'        => '600',
				'font-weight'    => '600',
				'font-style'     => '',
				'line-height'    => '1.1',
				'letter-spacing' => '0',
				'text-transform' => 'none',
				'not_removable'  => true,
			],
			'typography3' => [
				'label'          => 'Lead',
				'font-family'    => 'Inter',
				'font-backup'    => 'Arial, Helvetica, sans-serif',
				'font-size'      => '16px',
				'variant'        => '500',
				'font-weight'    => '500',
				'font-style'     => '',
				'line-height'    => '1.2',
				'letter-spacing' => '0.015em',
				'text-transform' => 'none',
				'not_removable'  => true,
			],
			'typography4' => [
				'label'          => 'Body',
				'font-family'    => 'Inter',
				'font-backup'    => 'Arial, Helvetica, sans-serif',
				'font-size'      => '16px',
				'variant'        => '400',
				'font-weight'    => '400',
				'font-style'     => '',
				'line-height'    => '1.72',
				'letter-spacing' => '0.015em',
				'text-transform' => 'none',
				'not_removable'  => true,
			],
			'typography5' => [
				'label'          => 'Small',
				'font-family'    => 'Inter',
				'font-backup'    => 'Arial, Helvetica, sans-serif',
				'font-size'      => '13px',
				'variant'        => '400',
				'font-weight'    => '400',
				'font-style'     => '',
				'line-height'    => '1.72',
				'letter-spacing' => '0.015em',
				'text-transform' => 'none',
				'not_removable'  => true,
			],
		];
	}

	/**
	 * Ensure the global set vars are loaded for live builder.  On real front-end its conditions from elements.
	 *
	 * @access public
	 * @since 7.6
	 *
	 * @param array $fields Fields to get google fronts from.
	 * @return array
	 */
	public function live_builder_fields( $fields ) {

		// if is the live builder, we want to ensure all global vars are set.
		if ( fusion_is_preview_frame() || fusion_is_builder_frame() ) {
			$typography = $this->get_typography();
			if ( ! empty( $typography ) ) {
				foreach ( $typography as $id => $data ) {
					$fields[] = $id;
				}
			}
		}
		return $fields;
	}

	/**
	 * Get typography data.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function get_typography() {
		if ( null !== $this->typography ) {
			return $this->typography;
		}

		// Only merge for valid typography data.
		$data = fusion_library()->get_option( 'typography_sets' );
		if ( is_array( $data ) ) {
			$this->typography = fusion_array_merge_recursive( $this->get_defaults(), $data );
		} else {
			$this->typography = $this->get_defaults();
		}

		// Pull variant as font-style and weight.
		foreach ( $this->typography as $slug => $data ) {
			if ( isset( $data['variant'] ) ) {
				$this->typography[ $slug ]['font-style']  = false !== strpos( $data['variant'], 'italic' ) ? 'italic' : '';
				$this->typography[ $slug ]['font-weight'] = str_replace( 'italic', '', $data['variant'] );
			}
		}

		$this->typography = apply_filters( 'awb_typography_global_data', $this->typography );
		return $this->typography;
	}

	/**
	 * Get the default typography, used in case a typography needed does not exists.
	 *
	 * @since 7.6
	 * @return array
	 */
	public function get_fallback_error_typography() {
		return [
			'label'          => esc_html__( 'Unknown Font', 'Avada' ),
			'font-family'    => esc_html__( 'Unknown Font', 'Avada' ),
			'font-size'      => '16px',
			'variant'        => '400',
			'font-weight'    => '400',
			'font-style'     => '',
			'line-height'    => '1.1',
			'letter-spacing' => '0',
			'text-transform' => 'none',
		];
	}

	/**
	 * Set variable definitions for each aspect of typography sets.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function set_variables() {
		$typography = $this->get_typography();

		$subsets = [
			'font-family',
			'font-size',
			'font-weight',
			'font-style',
			'line-height',
			'letter-spacing',
			'text-transform',
		];
		if ( is_array( $typography ) && ! empty( $typography ) ) {
			foreach ( $typography as $typo_slug => $data ) {
				foreach ( $subsets as $subset ) {
					if ( isset( $data[ $subset ] ) ) {
						if ( 'font-style' === $subset && '' === $data[ $subset ] ) {
							$data[ $subset ] = 'normal';
						} elseif ( 'font-family' === $subset && class_exists( 'Fusion_Dynamic_CSS' ) ) {
							$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
							$dynamic_css_helpers = $dynamic_css->get_helpers();
							$data[ $subset ]     = $dynamic_css_helpers->combined_font_family( $data );
						} elseif ( 'letter-spacing' === $subset && class_exists( 'Fusion_Panel_Callbacks' ) ) {
							$data[ $subset ] = Fusion_Panel_Callbacks::maybe_append_px( $data[ $subset ] );
						} elseif ( 'font-weight' === $subset && class_exists( 'Fusion_Panel_Callbacks' ) ) {
							$data[ $subset ] = Fusion_Panel_Callbacks::font_weight_no_regular( $data[ $subset ] );
						}

						Fusion_Dynamic_CSS::add_css_var(
							[
								'name'     => '--awb-' . $typo_slug . '-' . $subset,
								'value'    => $data[ $subset ],
								'element'  => ':root',
								'preserve' => true,
							]
						);
					}
				}
			}
		}
	}

	/**
	 * Get the slug from a variable string.
	 *
	 * @access public
	 * @since 7.6
	 *
	 * @param string $variable A CSS variable string.
	 * @return string
	 */
	public function get_typography_slug( $variable ) {
		return str_replace( [ 'var(--awb-', '-font-family)', '-font-size)', '-font-variant)', '-line-height)', '-letter-spacing)', '-text-transform)', ')' ], '', $variable );
	}

	/**
	 * Get real value of variable.
	 *
	 * @access public
	 * @since 7.6
	 *
	 * @param string $value CSS variable string.
	 * @param string $subset The typography subset we want.
	 * @return string
	 */
	public function get_real_value( $value, $subset = '' ) {

		// Avoid working it out multiple times if we got it already.
		if ( isset( $this->values[ $value ] ) ) {
			return $this->values[ $value ];
		}

		$slug = $this->get_typography_slug( $value );
		if ( empty( $subset ) ) {
			$subset = str_replace( [ 'var(--awb-', $slug . '-', ')' ], '', $value );
		}
		$values = $this->get_typography();
		if ( isset( $values[ $slug ] ) && isset( $values[ $slug ][ $subset ] ) ) {
			$this->value[ $value ] = $values[ $slug ][ $subset ];
			return $values[ $slug ][ $subset ];
		}
		return $value;
	}

	/**
	 * Get a variable string based on another string or slug.
	 *
	 * @access public
	 * @since 7.6
	 *
	 * @param string $input CSS variable string.
	 * @param string $subset Subset we want to get variable for.
	 * @return string
	 */
	public function get_var_string( $input = '', $subset = '' ) {
		if ( '' === $input ) {
			return '';
		}
		$slug = $this->get_typography_slug( $input );
		if ( '' === $subset ) {
			return 'var(--awb-' . $slug . ')';
		}
		return 'var(--awb-' . $slug . '-' . $subset . ')';
	}

	/**
	 * Find variable in the family and variant and replace with real values.
	 *
	 * @access public
	 * @since 7.6
	 *
	 * @param array $fonts Family family and variant array being used on page.
	 * @return array
	 */
	public function filter_fonts( $fonts ) {
		$typography = $this->get_typography();
		if ( ! empty( $fonts ) ) {
			foreach ( $fonts as $variable_font => $variants ) {
				if ( false !== strpos( $variable_font, 'var(' ) ) {
					$variable = $this->get_typography_slug( $variable_font );

					// Family is a variable, replace variable with family name in array and use weight and style from set.
					if ( isset( $typography[ $variable ] ) ) {
						$real_family = $typography[ $variable ]['font-family'];
						if ( isset( $typography[ $variable ]['variant'] ) ) {
							$variant = $typography[ $variable ]['variant'];
						} elseif ( isset( $typography[ $variable ]['font-weight'] ) && isset( $typography[ $variable ]['font-style'] ) ) {
							$variant = $typography[ $variable ]['font-weight'] . $typography[ $variable ]['font-style'];
						}

						// Unset variable font from array.
						unset( $fonts[ $variable_font ] );

						if ( isset( $fonts[ $real_family ] ) ) {

							// Family already has variants, but not this one, add it.
							if ( ! in_array( $variant, $fonts[ $real_family ] ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
								$fonts[ $real_family ][] = $variant;
							}
						} else {

							// Family is not already in map, add it with variant.
							$fonts[ $real_family ] = [ $variant ];
						}
					}
				}
			}
		}
		return $fonts;
	}

	/**
	 * Print the inline style declaration for the typography colors in backend.
	 *
	 * @since 7.6
	 */
	public function print_inline_global_typography_declaration_in_backend() {
		$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
		if ( ! is_admin() && ! $is_builder ) {
			return;
		}

		$typography_css = $this->get_global_colors_declaration_for_inline_style();
		if ( $typography_css ) {
			echo '<style id="awb-global-typography">' . $typography_css . '</style>'; // phpcs:ignore WordPress.Security
		}
	}

	/**
	 * Get an inline style declaration with all global typography.
	 *
	 * @since 7.6
	 * @return string
	 */
	public function get_global_colors_declaration_for_inline_style() {
		$css        = '';
		$typography = $this->get_typography();

		$subsets = [
			'font-family',
			'font-size',
			'font-weight',
			'font-style',
			'line-height',
			'letter-spacing',
			'text-transform',
		];
		if ( ! is_array( $typography ) || empty( $typography ) ) {
			return $css;
		}

		foreach ( $typography as $typo_slug => $data ) {
			foreach ( $subsets as $subset ) {
				if ( isset( $data[ $subset ] ) ) {
					if ( 'font-style' === $subset && '' === $data[ $subset ] ) {
						$data[ $subset ] = 'normal';
					} elseif ( 'font-family' === $subset && class_exists( 'Fusion_Dynamic_CSS' ) ) {
						$dynamic_css         = Fusion_Dynamic_CSS::get_instance();
						$dynamic_css_helpers = $dynamic_css->get_helpers();
						$data[ $subset ]     = $dynamic_css_helpers->combined_font_family( $data );
					} elseif ( 'letter-spacing' === $subset && class_exists( 'Fusion_Panel_Callbacks' ) ) {
						$data[ $subset ] = Fusion_Panel_Callbacks::maybe_append_px( $data[ $subset ] );
					} elseif ( 'font-weight' === $subset && class_exists( 'Fusion_Panel_Callbacks' ) ) {
						$data[ $subset ] = Fusion_Panel_Callbacks::font_weight_no_regular( $data[ $subset ] );
					}

					$css .= '--awb-' . $typo_slug . '-' . $subset . ':' . $data[ $subset ] . ';';
				}
			}
		}

		$css = ':root{' . $css . '}';
		return $css;
	}

	/**
	 * Check if a css value is a css typography variable.
	 *
	 * @since 3.6
	 * @param string $value The CSS variable.
	 * @return bool
	 */
	public function is_typography_css_var( $value ) {
		if ( false === strpos( $value, 'var(' ) ) {
			return false;
		}

		if ( false === strpos( $value, 'typography' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue the typography option.
	 *
	 * @access public
	 * @since 7.6
	 */
	public function enqueue() {
		if ( $this->enqueued ) {
			return;
		}

		wp_register_script( 'fuse-script', FUSION_LIBRARY_URL . '/assets/min/js/library/fuse.js', [], AVADA_VERSION, true );

		wp_register_script(
			'awb-select',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/awb-select.js',
			[ 'jquery', 'fuse-script' ],
			AVADA_VERSION,
			false
		);

		wp_register_script(
			'awb-typography-select',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/awb-typography-select.js',
			[ 'jquery' ],
			AVADA_VERSION,
			false
		);

		wp_localize_script(
			'awb-typography-select',
			'awbTypoData',
			[
				'data'    => $this->get_typography(),
				'strings' => [
					'none'              => esc_html__( 'None', 'Avada' ),
					'uppercase'         => esc_html__( 'Uppercase', 'Avada' ),
					'lowercase'         => esc_html__( 'Lowercase', 'Avada' ),
					'capitalize'        => esc_html__( 'Capitalize', 'Avada' ),
					'global'            => esc_html__( 'Global Typography', 'Avada' ),
					'custom_fonts'      => esc_html__( 'Custom Fonts', 'Avada' ),
					'adobe_fonts'       => esc_html__( 'Adobe Fonts', 'Avada' ),
					'standard_fonts'    => esc_html__( 'Standard Fonts', 'Avada' ),
					'google_fonts'      => esc_html__( 'Google Fonts', 'Avada' ),
					'unknown_font'      => esc_html__( 'Unknown Font', 'Avada' ),
					/* translators: %s is a number representing the custom typography number. */
					'new_font_name'     => esc_html__( 'Custom Typography %s', 'Avada' ),
					'set_removed_alert' => esc_html__( 'This will remove the typography set.', 'Avada' ),
				],
			]
		);

		wp_enqueue_script(
			'awb-typography',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/awb-typography.js',
			[ 'jquery', 'awb-typography-select', 'awb-select', 'underscore' ],
			AVADA_VERSION,
			false
		);

		wp_enqueue_style(
			'awb-typography',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/css/awb-typography.css',
			[],
			AVADA_VERSION
		);

		// We need template util for redux.
		if ( is_admin() && isset( $_GET['page'] ) && 'avada_options' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_enqueue_script(
				'awb-template-util',
				FUSION_LIBRARY_URL . '/inc/fusion-app/util.js',
				[ 'underscore', 'backbone' ],
				AVADA_VERSION,
				false
			);
		}

		wp_enqueue_script(
			'awb-typography-sets',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/js/awb-typography-sets.js',
			[ 'jquery', 'awb-typography-select', 'awb-select' ],
			AVADA_VERSION,
			false
		);

		wp_enqueue_style(
			'awb-typography-sets',
			FUSION_LIBRARY_URL . '/inc/fusion-app/assets/css/awb-typography-sets.css',
			[],
			AVADA_VERSION
		);

		$this->enqueued = true;
	}
}

/**
 * Instantiates the AWB_Global_Colors class.
 * Make sure the class is properly set-up.
 *
 * @since 7.6
 * @return object Fusion_App
 */
function AWB_Global_Typography() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Global_Typography::get_instance();
}
AWB_Global_Typography();
