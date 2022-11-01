<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.8
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_filters_active' ) ) {

	if ( ! class_exists( 'FusionTB_WooFiltersActive' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.8
		 */
		class FusionTB_WooFiltersActive extends AWB_Woo_Filters {

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
			 * CSS Vars prefix.
			 *
			 * @access public
			 * @since 3.8
			 * @var string
			 */
			public $css_vars_prefix = '--awb-woo-filter-';

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function __construct() {
				$this->shortcode_handle = 'fusion_tb_woo_filters_active';
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
						'item_color'                    => $fusion_settings->get( 'button_accent_color' ),
						'item_bgcolor'                  => $fusion_settings->get( 'button_gradient_top_color' ),
						'item_hover_color'              => $fusion_settings->get( 'button_accent_hover_color' ),
						'item_hover_bgcolor'            => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'fusion_font_family_item_font'  => '',
						'fusion_font_variant_item_font' => '',
						'item_font_size'                => '',
						'item_line_height'              => '',
						'item_letter_spacing'           => '',
						'item_text_transform'           => '',
						'item_padding_top'              => '',
						'item_padding_right'            => '',
						'item_padding_bottom'           => '',
						'item_padding_left'             => '',
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
					'fusion_font_family_item_font'  => 'item_fonts',
					'fusion_font_variant_item_font' => 'item_fonts',
					'item_font_size'                => 'item_fonts',
					'item_line_height'              => 'item_fonts',
					'item_letter_spacing'           => 'item_fonts',
					'item_text_transform'           => 'item_fonts',
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
					'item_font_size',
					'item_letter_spacing',
					'item_padding_top',
					'item_padding_right',
					'item_padding_bottom',
					'item_padding_left',
				];

				foreach ( $units as $unit ) {
					if ( ! $this->is_default( $unit ) ) {
						$this->args[ $unit ] = fusion_library()->sanitize->get_value_with_unit( $this->args[ $unit ] );
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
						$opt['value'] = esc_html__( 'Active filters', 'fusion-builder' );
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
						'heading'          => esc_attr__( 'Active Filter Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the active filter. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'item_fonts',
						'choices'          => [
							'font-family'    => 'item_font',
							'font-size'      => 'item_font_size',
							'text-transform' => 'item_text_transform',
							'line-height'    => 'item_line_height',
							'letter-spacing' => 'item_letter_spacing',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Active Filter Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'item_padding',
						'value'            => [
							'item_padding_top'    => '',
							'item_padding_right'  => '',
							'item_padding_bottom' => '',
							'item_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Active Filter Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'item_styling',
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
						'heading'     => esc_attr__( 'Active Filter Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the item background color of active filter.', 'fusion-builder' ),
						'param_name'  => 'item_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'item_styling',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Active Filter Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the item color of active filter.', 'fusion-builder' ),
						'param_name'  => 'item_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'item_styling',
							'tab'  => 'regular',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Active Filter Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the item background color of active filter.', 'fusion-builder' ),
						'param_name'  => 'item_hover_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'item_styling',
							'tab'  => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Active Filter Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the item color of active filter.', 'fusion-builder' ),
						'param_name'  => 'item_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'item_styling',
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

				if ( ! $this->is_default( 'item_color' ) ) {
					$styles .= $this->css_vars_prefix . 'item-color:' . fusion_library()->sanitize->color( $this->args['item_color'] ) . ';';
				}

				if ( ! $this->is_default( 'item_bgcolor' ) ) {
					$styles .= $this->css_vars_prefix . 'item-bgcolor:' . fusion_library()->sanitize->color( $this->args['item_bgcolor'] ) . ';';
				}

				if ( ! $this->is_default( 'item_hover_color' ) ) {
					$styles .= $this->css_vars_prefix . 'item-hover-color:' . fusion_library()->sanitize->color( $this->args['item_hover_color'] ) . ';';
				}

				if ( ! $this->is_default( 'item_hover_bgcolor' ) ) {
					$styles .= $this->css_vars_prefix . 'item-hover-bgcolor:' . fusion_library()->sanitize->color( $this->args['item_hover_bgcolor'] ) . ';';
				}

				// Item Typo.
				$styles .= $this->get_typo_variables(
					[
						'item_font'           => 'font',
						'item_font_size'      => 'size',
						'item_line_height'    => 'line_height',
						'item_letter_spacing' => 'letter_spacing',
						'item_text_transform' => 'text_transform',
					]
				);

				// Item Padding.
				$styles .= $this->get_dimension_variables( 'item_padding' );

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
		}
	}

	/**
	 * Instantiates the class.
	 *
	 * @return object
	 */
	function awb_woo_filter_active() { // phpcs:ignore WordPress.NamingConventions
		return FusionTB_WooFiltersActive::get_instance();
	}

	// Instantiate.
	awb_woo_filter_active();
}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_filters_active() {
	if ( class_exists( 'WooCommerce' ) ) {
		$params    = [];
		$subparams = [];

		// We only need options if element is active.
		if ( function_exists( 'awb_woo_filter_active' ) ) {
			$params    = awb_woo_filter_active()->get_element_params();
			$subparams = awb_woo_filter_active()->get_element_subparams();
		}

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_WooFiltersActive',
				[
					'name'         => esc_attr__( 'Woo Filter Active', 'fusion-builder' ),
					'shortcode'    => 'fusion_tb_woo_filters_active',
					'icon'         => 'fusiona-active-filters',
					'component'    => true,
					'templates'    => [ 'content' ],
					'subparam_map' => $subparams,
					'params'       => $params,
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_tb_woo_filters_active',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'fusion_builder_before_init', 'fusion_element_woo_filters_active' );
