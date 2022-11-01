<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.8
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_filters_price' ) ) {

	if ( ! class_exists( 'FusionTB_WooFiltersPrice' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.8
		 */
		class FusionTB_WooFiltersPrice extends AWB_Woo_Filters {

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.8
			 * @var int
			 */
			private $element_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.8
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function __construct() {
				$this->shortcode_handle = 'fusion_tb_woo_filters_price';
				parent::__construct();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public static function get_element_defaults() {
				$defaults        = parent::get_element_defaults();
				$fusion_settings = awb_get_fusion_settings();

				$args = wp_parse_args(
					[
						'range_filled_color'             => $fusion_settings->get( 'primary_color' ),
						'range_unfilled_color'           => $fusion_settings->get( 'sep_color' ),
						'range_button_color'             => $fusion_settings->get( 'button_accent_color' ),
						'range_button_bgcolor'           => $fusion_settings->get( 'button_gradient_top_color' ),
						'range_button_hover_color'       => $fusion_settings->get( 'button_accent_hover_color' ),
						'range_button_hover_bgcolor'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'range_handle_bgcolor'           => '',
						'range_handle_border_color'      => '#333333',
						'fusion_font_family_price_font'  => '',
						'fusion_font_variant_price_font' => '',
						'price_font_size'                => '',
						'price_line_height'              => '',
						'price_letter_spacing'           => '',
						'price_text_transform'           => '',
						'price_color'                    => $fusion_settings->get( 'body_typography', 'color' ),
					],
					$defaults
				);

				return $args;
			}

			/**
			 * Get element subparams.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function get_element_subparams() {
				$options = parent::get_element_subparams();

				$params = [
					'fusion_font_family_price_font'  => 'price_fonts',
					'fusion_font_variant_price_font' => 'price_fonts',
					'price_font_size'                => 'price_fonts',
					'price_line_height'              => 'price_fonts',
					'price_letter_spacing'           => 'price_fonts',
					'price_text_transform'           => 'price_fonts',
					'price_color'                    => 'price_fonts',
				];

				return array_merge( $options, $params );
			}

			/**
			 * Validate the arguments into correct format.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function validate_args() {
				parent::validate_args();

				$units = [
					'price_font_size',
					'price_letter_spacing',
				];

				$colors = [
					'price_color',
				];

				foreach ( $units as $unit ) {
					if ( ! $this->is_default( $unit ) ) {
						$this->args[ $unit ] = fusion_library()->sanitize->get_value_with_unit( $this->args[ $unit ] );
					}
				}

				foreach ( $colors as $color ) {
					if ( ! $this->is_default( $color ) ) {
						$this->args[ $color ] = fusion_library()->sanitize->color( $this->args[ $color ] );
					}
				}

			}

			/**
			 * Fetch general options.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function fetch_general_options() {
				$options = parent::fetch_general_options();
				$params  = [];

				foreach ( $options as $opt ) {
					if ( 'title' === $opt['param_name'] ) {
						$opt['value'] = esc_html__( 'Filter by price', 'fusion-builder' );
					}
					if ( in_array( $opt['param_name'], [ 'title', 'title_size' ], true ) ) {
						$opt['callback']['action'] = "get_{$this->shortcode_handle}";
					}

					$params[] = $opt;
				}

				return $params;
			}

			/**
			 * Fetch design options.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function fetch_design_options() {
				$options         = parent::fetch_design_options();
				$fusion_settings = awb_get_fusion_settings();

				$params = [
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Price Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the price. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'price_fonts',
						'choices'          => [
							'font-family'    => 'price_font',
							'font-size'      => 'price_font_size',
							'text-transform' => 'price_text_transform',
							'line-height'    => 'price_line_height',
							'letter-spacing' => 'price_letter_spacing',
							'color'          => 'price_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'body_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range filled color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_filled_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Unfilled Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range unfilled color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_unfilled_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Handle Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range handle background color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_handle_bgcolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Handle Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range handle border color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_handle_border_color',
						'value'       => '',
						'default'     => '#333333',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'range_button_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Active', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range button background color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_button_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'range_button_styling',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range button color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_button_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'range_button_styling',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range button background color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_button_hover_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'range_button_styling',
							'tab'  => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the range button color of price filter.', 'fusion-builder' ),
						'param_name'  => 'range_button_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'range_button_styling',
							'tab'  => 'hover',
						],
					],
				];

				foreach ( $params as $param ) {
					$options[] = $param;
				}

				return $options;
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.8
			 * @return string
			 */
			protected function get_style_variables() {
				$styles = parent::get_style_variables();

				if ( ! $this->is_default( 'range_filled_color' ) ) {
					$styles .= $this->css_vars_prefix . 'range-filled-color:' . fusion_library()->sanitize->color( $this->args['range_filled_color'] ) . ';';
				}

				if ( ! $this->is_default( 'range_unfilled_color' ) ) {
					$styles .= $this->css_vars_prefix . 'range-unfilled-color:' . fusion_library()->sanitize->color( $this->args['range_unfilled_color'] ) . ';';
				}

				if ( ! $this->is_default( 'range_button_color' ) ) {
					$styles .= $this->css_vars_prefix . 'range-button-color:' . fusion_library()->sanitize->color( $this->args['range_button_color'] ) . ';';
				}

				if ( ! $this->is_default( 'range_button_bgcolor' ) ) {
					$styles .= $this->css_vars_prefix . 'range-button-bgcolor:' . fusion_library()->sanitize->color( $this->args['range_button_bgcolor'] ) . ';';
				}

				if ( ! $this->is_default( 'range_button_hover_color' ) ) {
					$styles .= $this->css_vars_prefix . 'range-button-hover-color:' . fusion_library()->sanitize->color( $this->args['range_button_hover_color'] ) . ';';
				}

				if ( ! $this->is_default( 'range_button_hover_bgcolor' ) ) {
					$styles .= $this->css_vars_prefix . 'range-button-hover-bgcolor:' . fusion_library()->sanitize->color( $this->args['range_button_hover_bgcolor'] ) . ';';
				}

				if ( ! $this->is_default( 'range_handle_bgcolor' ) ) {
					$styles .= $this->css_vars_prefix . 'range-handle-bgcolor:' . fusion_library()->sanitize->color( $this->args['range_handle_bgcolor'] ) . ';';
				}

				if ( ! $this->is_default( 'range_handle_border_color' ) ) {
					$styles .= $this->css_vars_prefix . 'range-handle-border-color:' . fusion_library()->sanitize->color( $this->args['range_handle_border_color'] ) . ';';
				}

				// Price Typo.
				$styles .= $this->get_typo_variables(
					[
						'price_font'           => 'font',
						'price_font_size'      => 'size',
						'price_line_height'    => 'line_height',
						'price_letter_spacing' => 'letter_spacing',
						'price_text_transform' => 'text_transform',
						'price_color'          => 'color',
					]
				);

				return $styles;
			}

			/**
			 * Emulate filter element for LE.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function emulate_filter_element() {
				$_GET['min_price'] = 1;
				$_GET['max_price'] = 100;

				WC()->query->product_query( $GLOBALS['wp_query'] );
			}

			/**
			 * Restore filter element for LE.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function restore_filter_element() {
				unset( $_GET['min_price'] ); // phpcs:ignore WordPress.Security.NonceVerification
				unset( $_GET['max_price'] ); // phpcs:ignore WordPress.Security.NonceVerification
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function on_first_render() {
				wp_enqueue_script( 'wc-price-slider' );
			}
		}
	}

	/**
	 * Instantiates the class.
	 *
	 * @return object
	 */
	function awb_woo_filter_price() { // phpcs:ignore WordPress.NamingConventions
		return FusionTB_WooFiltersPrice::get_instance();
	}

	// Instantiate.
	awb_woo_filter_price();
}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_filters_price() {
	if ( class_exists( 'WooCommerce' ) ) {
		$params    = [];
		$subparams = [];

		// We only need options if element is active.
		if ( function_exists( 'awb_woo_filter_price' ) ) {
			$params    = awb_woo_filter_price()->get_element_params();
			$subparams = awb_woo_filter_price()->get_element_subparams();
		}

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_WooFiltersPrice',
				[
					'name'         => esc_attr__( 'Woo Filter By Price', 'fusion-builder' ),
					'shortcode'    => 'fusion_tb_woo_filters_price',
					'icon'         => 'fusiona-filter-by-price',
					'component'    => true,
					'templates'    => [ 'content' ],
					'subparam_map' => $subparams,
					'params'       => $params,
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_tb_woo_filters_price',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'fusion_builder_before_init', 'fusion_element_woo_filters_price' );
