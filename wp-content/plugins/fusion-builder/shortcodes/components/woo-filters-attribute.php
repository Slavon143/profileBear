<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.8
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_filters_attribute' ) ) {

	if ( ! class_exists( 'FusionTB_WooFiltersAttribute' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.8
		 */
		class FusionTB_WooFiltersAttribute extends AWB_Woo_Filters {

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
				$this->shortcode_handle = 'fusion_tb_woo_filters_attribute';
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
						'attribute'                      => '',
						'display_type'                   => 'list',
						'query_type'                     => 'and',
						'list_color'                     => $fusion_settings->get( 'link_color' ),
						'list_hover_color'               => $fusion_settings->get( 'primary_color' ),
						'list_bgcolor'                   => '',
						'list_hover_bgcolor'             => '',
						'list_sep_color'                 => $fusion_settings->get( 'sep_color' ),
						'list_padding_top'               => '',
						'list_padding_right'             => '',
						'list_padding_bottom'            => '',
						'list_padding_left'              => '',
						'list_align'                     => 'space-between',
						'fusion_font_family_list_item_font' => '',
						'fusion_font_variant_list_item_font' => '',
						'list_item_font_size'            => '',
						'list_item_line_height'          => '',
						'list_item_letter_spacing'       => '',
						'list_item_text_transform'       => '',
						'attr_padding_top'               => '',
						'attr_padding_right'             => '',
						'attr_padding_bottom'            => '',
						'attr_padding_left'              => '',
						'attr_color'                     => $fusion_settings->get( 'link_color' ),
						'attr_bgcolor'                   => '',
						'attr_border_color'              => $fusion_settings->get( 'link_color' ),
						'attr_hover_color'               => $fusion_settings->get( 'primary_color' ),
						'attr_hover_bgcolor'             => '',
						'attr_border_hover_color'        => $fusion_settings->get( 'primary_color' ),
						'dd_color'                       => $fusion_settings->get( 'form_text_color' ),
						'dd_bgcolor'                     => $fusion_settings->get( 'form_bg_color' ),
						'dd_hover_color'                 => '',
						'dd_hover_bgcolor'               => '',
						'dd_border_color'                => $fusion_settings->get( 'form_border_color' ),
						'fusion_font_family_count_font'  => '',
						'fusion_font_variant_count_font' => '',
						'show_count'                     => 'yes',
						'count_font_size'                => '',
						'count_line_height'              => '',
						'count_letter_spacing'           => '',
						'count_text_transform'           => '',
						'count_color'                    => '',
						'count_hover_color'              => '',
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
					'fusion_font_family_list_item_font'  => 'list_item_fonts',
					'fusion_font_variant_list_item_font' => 'list_item_fonts',
					'list_item_font_size'                => 'list_item_fonts',
					'list_item_line_height'              => 'list_item_fonts',
					'list_item_letter_spacing'           => 'list_item_fonts',
					'list_item_text_transform'           => 'list_item_fonts',
					'fusion_font_family_count_font'      => 'count_fonts',
					'fusion_font_variant_count_font'     => 'count_fonts',
					'count_font_size'                    => 'count_fonts',
					'count_line_height'                  => 'count_fonts',
					'count_letter_spacing'               => 'count_fonts',
					'count_text_transform'               => 'count_fonts',
				];

				return array_merge( $options, $params );
			}

			/**
			 * Gets the widget instance options.
			 *
			 * @static
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function get_widget_instance() {
				return [
					'title'        => $this->args['title'],
					'attribute'    => $this->args['attribute'],
					'display_type' => $this->args['display_type'],
					'query_type'   => $this->args['query_type'],
				];
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
					'list_padding_top',
					'list_padding_right',
					'list_padding_bottom',
					'list_padding_left',
					'list_item_font_size',
					'list_item_letter_spacing',
					'attr_padding_top',
					'attr_padding_right',
					'attr_padding_bottom',
					'attr_padding_left',
					'count_font_size',
				];

				$colors = [
					'list_color',
					'list_hover_color',
					'list_bgcolor',
					'list_hover_bgcolor',
					'list_sep_color',
					'attr_color',
					'attr_bgcolor',
					'attr_border_color',
					'attr_hover_color',
					'attr_hover_bgcolor',
					'attr_border_hover_color',
					'dd_color',
					'dd_bgcolor',
					'dd_hover_color',
					'dd_hover_bgcolor',
					'dd_border_color',
					'count_color',
					'count_hover_color',
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
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.8.1
			 * @return array
			 */
			public function attr() {
				$attr           = parent::attr();
				$attr['class'] .= ' awb-attribute-type-' . $this->get_attribute_type( $this->args['attribute'] );
				return $attr;
			}

			/**
			 * Fetch general options.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function fetch_general_options() {
				$options              = parent::fetch_general_options();
				$attribute_array      = [];
				$attribute_taxonomies = wc_get_attribute_taxonomies();
				$return               = [];

				if ( ! empty( $attribute_taxonomies ) ) {
					foreach ( $attribute_taxonomies as $tax ) {
						if ( taxonomy_exists( wc_attribute_taxonomy_name( $tax->attribute_name ) ) ) {
							$attribute_array[ $tax->attribute_name ] = $tax->attribute_label;
						}
					}
				}

				$params = [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Attribute', 'fusion-builder' ),
						'param_name'  => 'attribute',
						'default'     => '',
						'value'       => $attribute_array,
						'description' => esc_attr__( 'Select the attribute that will display on element.', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_filters_attribute',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Display Type', 'fusion-builder' ),
						'param_name'  => 'display_type',
						'default'     => 'list',
						'value'       => [
							'list'     => esc_html__( 'List', 'fusion-builder' ),
							'dropdown' => esc_html__( 'Dropdown', 'fusion-builder' ),
						],
						'description' => esc_attr__( 'Select display type that will display on element.', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_filters_attribute',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Query Type', 'fusion-builder' ),
						'param_name'  => 'query_type',
						'default'     => 'and',
						'value'       => [
							'and' => esc_html__( 'AND', 'fusion-builder' ),
							'or'  => esc_html__( 'OR', 'fusion-builder' ),
						],
						'description' => esc_attr__( 'Select query type that will display on element.', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_filters_attribute',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
				];

				foreach ( $options as $opt ) {
					if ( 'title' === $opt['param_name'] ) {
						$opt['value'] = esc_html__( 'Filter by', 'fusion-builder' );
					}
					if ( in_array( $opt['param_name'], [ 'title', 'title_size' ], true ) ) {
						$opt['callback']['action'] = "get_{$this->shortcode_handle}";
					}
					$return[] = $opt;

					// Insert element params after title param.
					if ( 'title_size' === $opt['param_name'] ) {
						foreach ( $params as $param ) {
							$return[] = $param;
						}
					}
				}

				return $return;
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
						'heading'          => esc_attr__( 'Attribute Item Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the list item. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'list_item_fonts',
						'choices'          => [
							'font-family'    => 'list_item_font',
							'font-size'      => 'list_item_font_size',
							'text-transform' => 'list_item_text_transform',
							'line-height'    => 'list_item_line_height',
							'letter-spacing' => 'list_item_letter_spacing',
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
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Attribute Item Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'list_padding',
						'value'            => [
							'list_padding_top'    => '',
							'list_padding_right'  => '',
							'list_padding_bottom' => '',
							'list_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Attribute Item Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment within the details area.', 'fusion-builder' ),
						'param_name'  => 'list_align',
						'default'     => 'space-between',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'value'       => [
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Attribute Separator Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the list separator of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'list_sep_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Attribute Item Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'list_item_styling_options',
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
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the list background color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'list_bgcolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'list_item_styling_options',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the list color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'list_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'list_item_styling_options',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the list background color hover of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'list_hover_bgcolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'list_item_styling_options',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the list hover color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'list_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'list_item_styling_options',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Count Number', 'fusion-builder' ),
						'description' => esc_attr__( 'Display the count number of items.', 'fusion-builder' ),
						'param_name'  => 'show_count',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Count Number Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the count number. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'count_fonts',
						'choices'          => [
							'font-family'    => 'count_font',
							'font-size'      => 'count_font_size',
							'text-transform' => 'count_text_transform',
							'line-height'    => 'count_line_height',
							'letter-spacing' => 'count_letter_spacing',
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
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
							[
								'element'  => 'show_count',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Count Number Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the count number color.', 'fusion-builder' ),
						'param_name'  => 'count_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
							[
								'element'  => 'show_count',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Count Number Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the count number hover color.', 'fusion-builder' ),
						'param_name'  => 'count_hover_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
							[
								'element'  => 'show_count',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Swatch Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'attr_padding',
						'value'            => [
							'attr_padding_top'    => '',
							'attr_padding_right'  => '',
							'attr_padding_bottom' => '',
							'attr_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Swatches Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Control the styling of attribute swatch.', 'fusion-builder' ),
						'param_name'       => 'attribute_styling',
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
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the attribute background color.', 'fusion-builder' ),
						'param_name'  => 'attr_bgcolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'attribute_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the attribute color.', 'fusion-builder' ),
						'param_name'  => 'attr_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'attribute_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the attribute border color.', 'fusion-builder' ),
						'param_name'  => 'attr_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'attribute_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the attribute hover background color.', 'fusion-builder' ),
						'param_name'  => 'attr_hover_bgcolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'attribute_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the attribute hover color.', 'fusion-builder' ),
						'param_name'  => 'attr_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'attribute_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the attribute hover border color.', 'fusion-builder' ),
						'param_name'  => 'attr_border_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'attribute_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'dropdown',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Dropdown Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the dropdown border color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'dd_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_border_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'list',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Dropdown Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'dd_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'regular' => esc_html__( 'Regular', 'fusion-builder' ),
							'hover'   => esc_html__( 'Hover / Selected', 'fusion-builder' ),
						],
						'icons'            => [
							'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
							'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
						],
						'dependency'       => [
							[
								'element'  => 'display_type',
								'value'    => 'list',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Dropdown Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the dropdown background color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'dd_bgcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_bg_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'dd_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'list',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Dropdown Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the dropdown text color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'dd_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_text_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'dd_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'list',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Dropdown Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the dropdown hover background color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'dd_hover_bgcolor',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'dd_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'list',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Dropdown Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the dropdown text hover color of attribute filter.', 'fusion-builder' ),
						'param_name'  => 'dd_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'dd_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'display_type',
								'value'    => 'list',
								'operator' => '!=',
							],
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

				// List Item Padding.
				$styles .= $this->get_dimension_variables( 'list_padding' );

				// List Item Typo.
				$styles .= $this->get_typo_variables(
					[
						'list_item_font'           => 'font',
						'list_item_font_size'      => 'size',
						'list_item_line_height'    => 'line_height',
						'list_item_letter_spacing' => 'letter_spacing',
						'list_item_text_transform' => 'text_transform',
					]
				);

				// Colors style.
				$colors = [
					'list_color',
					'list_hover_color',
					'list_bgcolor',
					'list_hover_bgcolor',
					'list_sep_color',
					'attr_color',
					'attr_bgcolor',
					'attr_border_color',
					'attr_hover_color',
					'attr_hover_bgcolor',
					'attr_border_hover_color',
					'dd_color',
					'dd_bgcolor',
					'dd_hover_color',
					'dd_hover_bgcolor',
					'dd_border_color',
					'count_color',
					'count_hover_color',
				];

				foreach ( $colors as $color ) {
					if ( ! $this->is_default( $color ) ) {
						$styles .= sprintf( '%s: %s;', $this->css_vars_prefix . str_replace( '_', '-', $color ), $this->args[ $color ] );
					}
				}

				// Attribute Padding.
				$styles .= $this->get_dimension_variables( 'attr_padding' );

				if ( ! $this->is_default( 'list_align' ) ) {
					$styles .= sprintf( '%s: %s;', $this->css_vars_prefix . 'list-align', $this->args['list_align'] );
				}

				// Count Typo.
				$styles .= $this->get_typo_variables(
					[
						'count_font'           => 'font',
						'count_font_size'      => 'size',
						'count_line_height'    => 'line_height',
						'count_letter_spacing' => 'letter_spacing',
						'count_text_transform' => 'text_transform',
					]
				);

				if ( ! $this->is_default( 'show_count' ) ) {
					$styles .= sprintf( '%s: %s;', $this->css_vars_prefix . 'show-count', 'none' );
				}

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
				WC()->query->product_query( $GLOBALS['wp_query'] );
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function on_first_render() {
				wp_enqueue_script( 'selectWoo' );
				wp_enqueue_style( 'select2' );
			}

			/**
			 * Fires on render.
			 *
			 * @access protected
			 * @since 3.8
			 */
			protected function on_render() {
				if ( ! $this->has_rendered ) {
					$this->on_first_render();
					$this->has_rendered = true;
				}

				if ( 'dropdown' === $this->args['display_type'] ) {
					wc_enqueue_js(
						"
						jQuery( '.dropdown_layered_nav_" . $this->args['attribute'] . "' ).on( 'select2:open', function( e ) {
							jQuery( this ).data( 'select2' ).\$dropdown.addClass( 'awb-woo-filters' )[0].style.cssText += '" . esc_js( $this->get_style_variables() ) . "';
						} );
					"
					);
				}
			}

			/**
			 * Get attribute type.
			 *
			 * @access protected
			 * @param string $name Attribute name.
			 * @since 3.8.1
			 */
			protected function get_attribute_type( $name ) {
				global $wpdb;

				$type      = 'select';
				$attribute = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
					$wpdb->prepare(
						"
						SELECT attribute_type
						FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = %s
						",
						$name
					)
				);
				if ( $attribute ) {
					$type = $attribute->attribute_type;
				}
				return $type;
			}

			/**
			 * Add extra classes to the element div.
			 *
			 * @access public
			 * @since 3.8.1
			 * @return string
			 */
			public function add_extra_classes() {
				return ' awb-attribute-type-' . $this->get_attribute_type( $this->args['attribute'] );
			}
		}
	}

	/**
	 * Instantiates the class.
	 *
	 * @return object
	 */
	function awb_woo_filter_attribute() { // phpcs:ignore WordPress.NamingConventions
		return FusionTB_WooFiltersAttribute::get_instance();
	}

	// Instantiate.
	awb_woo_filter_attribute();
}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_filters_attribute() {
	if ( class_exists( 'WooCommerce' ) ) {
		$params    = [];
		$subparams = [];

		// We only need options if element is active.
		if ( function_exists( 'awb_woo_filter_attribute' ) ) {
			$params    = awb_woo_filter_attribute()->get_element_params();
			$subparams = awb_woo_filter_attribute()->get_element_subparams();
		}

		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionTB_WooFiltersAttribute',
				[
					'name'         => esc_attr__( 'Woo Filter By Attribute', 'fusion-builder' ),
					'shortcode'    => 'fusion_tb_woo_filters_attribute',
					'icon'         => 'fusiona-filter-by-attributes',
					'component'    => true,
					'templates'    => [ 'content' ],
					'subparam_map' => $subparams,
					'params'       => $params,
					'callback'     => [
						'function' => 'fusion_ajax',
						'action'   => 'get_fusion_tb_woo_filters_attribute',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_filters_attribute' );
