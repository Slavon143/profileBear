<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooCartTotals' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooCartTotals extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.3
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_woo-cart-totals-shortcode-wrapper', [ $this, 'wrapper_attr' ] );
				add_shortcode( 'fusion_woo_cart_totals', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_fusion_get_woo_cart_totals', [ $this, 'ajax_query' ] );
			}


			/**
			 * Gets the query data.
			 *
			 * @access public
			 * @since 3.3
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_query( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );
				$this->args = $_POST['model']['params']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput

				$html = $this->generate_element_content();

				echo wp_json_encode( $html );
				wp_die();
			}


			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					// Element margin.
					'margin_top'                       => '',
					'margin_right'                     => '',
					'margin_bottom'                    => '',
					'margin_left'                      => '',

					// Element margin.
					'button_margin_top'                => '',
					'button_margin_right'              => '',
					'button_margin_bottom'             => '',
					'button_margin_left'               => '',

					// Cell padding.
					'cell_padding_top'                 => '',
					'cell_padding_right'               => '',
					'cell_padding_bottom'              => '',
					'cell_padding_left'                => '',

					'table_cell_backgroundcolor'       => '',
					'heading_cell_backgroundcolor'     => '',

					// Heading styles.
					'heading_color'                    => '',
					'fusion_font_family_heading_font'  => '',
					'fusion_font_variant_heading_font' => '',
					'heading_font_size'                => '',
					'heading_text_transform'           => '',
					'heading_line_height'              => '',
					'heading_letter_spacing'           => '',

					// Text styles.
					'text_color'                       => '',
					'fusion_font_family_text_font'     => '',
					'fusion_font_variant_text_font'    => '',
					'text_font_size'                   => '',
					'text_text_transform'              => '',
					'text_line_height'                 => '',
					'text_letter_spacing'              => '',

					'border_color'                     => '',

					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'class'                            => '',
					'id'                               => '',
					'animation_type'                   => '',
					'animation_direction'              => 'down',
					'animation_speed'                  => '0.1',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),

					'buttons_visibility'               => '',
					'buttons_layout'                   => '',
					'floated_buttons_alignment'        => '',
					'stacked_buttons_alignment'        => '',
					'button_span'                      => '',
				];
			}


			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output
			 */
			public function render( $args, $content = '' ) {
				if ( ! is_object( WC()->cart ) || ( WC()->cart->is_empty() && ! fusion_is_preview_frame() ) ) {
					return;
				}
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_cart_totals' );

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'woo-cart-totals-shortcode-wrapper' ) . '>' . $this->generate_element_content() . '</div>';

				$this->on_render();
				$this->counter++;
				return apply_filters( 'fusion_element_cart_totals_content', $html, $args );

			}

			/**
			 * Generates element content
			 *
			 * @return string
			 */
			public function generate_element_content() {

				if ( ! is_object( WC()->cart ) || WC()->cart->is_empty() ) {
					return '';
				}

				// Check cart items are valid.
				do_action( 'woocommerce_check_cart_items' );

				// Calc totals.
				WC()->cart->calculate_totals();

				ob_start();
				woocommerce_cart_totals();
				return ob_get_clean();
			}


			/**
			 * Generates the element styles
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			public function get_styles() {
				$this->base_selector = '.fusion-woo-cart-totals-wrapper-' . $this->counter;
				$this->dynamic_css   = [];

				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-top', $this->args['margin_top'] );
				}

				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', $this->args['margin_bottom'] );
				}

				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-left', $this->args['margin_left'] );
				}

				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $this->base_selector, 'margin-right', $this->args['margin_right'] );
				}

				$selector = $this->base_selector . ' tbody tr td, ' . $this->base_selector . ' tbody tr th';
				if ( ! $this->is_default( 'cell_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', $this->args['cell_padding_top'], true );
				}

				if ( ! $this->is_default( 'cell_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', $this->args['cell_padding_bottom'], true );
				}

				if ( ! $this->is_default( 'cell_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', $this->args['cell_padding_left'], true );
				}

				if ( ! $this->is_default( 'cell_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', $this->args['cell_padding_right'], true );
				}

				$selector = $this->base_selector . ' tbody tr th';
				if ( ! $this->is_default( 'heading_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['heading_cell_backgroundcolor'] );
				}

				if ( ! $this->is_default( 'fusion_font_family_heading_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_heading_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_heading_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_heading_font'] );
				}

				if ( ! $this->is_default( 'heading_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['heading_font_size'] );
				}

				if ( ! $this->is_default( 'heading_line_height' ) ) {
					$this->add_css_property( $selector, 'line-height', $this->args['heading_line_height'] );
				}

				if ( ! $this->is_default( 'heading_letter_spacing' ) ) {
					$this->add_css_property( $selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['heading_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'heading_text_transform' ) ) {
					$this->add_css_property( $selector, 'text-transform', $this->args['heading_text_transform'] );
				}

				$selector = $this->base_selector . ' tbody tr td';
				if ( ! $this->is_default( 'table_cell_backgroundcolor' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['table_cell_backgroundcolor'] );
				}

				$text_selector = $selector . ', ' . $this->base_selector . ' a, ' . $this->base_selector . ' .amount';
				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $text_selector, 'color', $this->args['text_color'], true );
				}

				if ( ! $this->is_default( 'heading_color' ) ) {
					$this->add_css_property( $this->base_selector . ' tbody tr th', 'color', $this->args['heading_color'], true );
				}

				if ( ! $this->is_default( 'fusion_font_family_text_font' ) ) {
					$this->add_css_property( $selector, 'font-family', $this->args['fusion_font_family_text_font'] );
				}

				if ( ! $this->is_default( 'fusion_font_variant_text_font' ) ) {
					$this->add_css_property( $selector, 'font-weight', $this->args['fusion_font_variant_text_font'] );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $selector, 'font-size', $this->args['text_font_size'] );
				}

				if ( ! $this->is_default( 'text_line_height' ) ) {
					$this->add_css_property( $selector, 'line-height', $this->args['text_line_height'] );
				}

				if ( ! $this->is_default( 'text_letter_spacing' ) ) {
					$this->add_css_property( $selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['text_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'text_text_transform' ) ) {
					$this->add_css_property( $selector, 'text-transform', $this->args['text_text_transform'] );
				}

				$selector = $this->base_selector . ' tr, ' . $this->base_selector . ' tr td, ' . $this->base_selector . ' tr th';
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selector, 'border-color', $this->args['border_color'], true );
				}

				$selector = '.fusion-woo-cart-totals-wrapper-' . $this->counter . ' div.wc-proceed-to-checkout';
				if ( 'floated' === $this->args['buttons_layout'] ) {
					$this->add_css_property( $selector, 'flex-direction', 'row' );

					if ( 'yes' === $this->args['button_span'] ) {
						$this->add_css_property( $selector, 'justify-content', 'stretch', true );
						$this->add_css_property( $selector . ' a', 'flex', '1' );
					} else {
						$this->add_css_property( $selector, 'justify-content', $this->args['floated_buttons_alignment'], true );
					}
				} else {
					$this->add_css_property( $selector, 'flex-direction', 'column', true );
					$this->add_css_property( $selector, 'align-items', $this->args['stacked_buttons_alignment'], true );
					if ( 'yes' === $this->args['button_span'] ) {
						$this->add_css_property( $selector, 'align-items', 'stretch', true );
					} else {
						$this->add_css_property( $selector, 'align-items', $this->args['stacked_buttons_alignment'], true );
					}
				}

				if ( ! $this->is_default( 'button_margin_top' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-top', $this->args['button_margin_top'] );
				}

				if ( ! $this->is_default( 'button_margin_bottom' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-bottom', $this->args['button_margin_bottom'] );
				}

				if ( ! $this->is_default( 'button_margin_left' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-left', $this->args['button_margin_left'] );
				}

				if ( ! $this->is_default( 'button_margin_right' ) ) {
					$this->add_css_property( $selector . ' a', 'margin-right', $this->args['button_margin_right'] );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-woo-cart-totals-wrapper fusion-woo-cart-totals-wrapper-' . $this->counter,
						'style' => '',
					]
				);

				if ( WC()->customer->has_calculated_shipping() ) {
					$attr['class'] .= ' calculated_shipping';
				}

				if ( 'show' === $this->args['buttons_visibility'] ) {
					$attr['class'] .= ' show-buttons';
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}


			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-cart-totals.min.css' );
			}
		}
	}

	new FusionSC_WooCartTotals();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_cart_totals() {
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooCartTotals',
				[
					'name'          => esc_attr__( 'Woo Cart Totals', 'fusion-builder' ),
					'shortcode'     => 'fusion_woo_cart_totals',
					'icon'          => 'fusiona-cart-totals',
					'help_url'      => '',
					'inline_editor' => true,
					'subparam_map'  => [
						'fusion_font_family_heading_font'  => 'heading_fonts',
						'fusion_font_variant_heading_font' => 'heading_fonts',
						'heading_font_size'                => 'heading_fonts',
						'heading_text_transform'           => 'heading_fonts',
						'heading_line_height'              => 'heading_fonts',
						'heading_letter_spacing'           => 'heading_fonts',
						'heading_color'                    => 'heading_fonts',
						'fusion_font_variant_text_font'    => 'text_fonts',
						'fusion_font_family_text_font'     => 'text_fonts',
						'text_font_size'                   => 'text_fonts',
						'text_text_transform'              => 'text_fonts',
						'text_line_height'                 => 'text_fonts',
						'text_letter_spacing'              => 'text_fonts',
						'text_color'                       => 'text_fonts',
					],
					'params'        => [
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to show or hide buttons.', 'fusion-builder' ),
							'param_name'  => 'buttons_visibility',
							'default'     => 'show',
							'value'       => [
								'show' => esc_html__( 'Show', 'fusion-builder' ),
								'hide' => esc_html__( 'Hide', 'fusion-builder' ),
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Buttons Layout', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the layout of buttons.', 'fusion-builder' ),
							'param_name'  => 'buttons_layout',
							'value'       => [
								'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
								'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
							],
							'default'     => 'floated',
							'dependency'  => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_html__( 'Buttons Horizontal Align', 'fusion-builder' ),
							'description' => esc_html__( 'Change the horizontal alignment of buttons within its container column.', 'fusion-builder' ),
							'param_name'  => 'floated_buttons_alignment',
							'default'     => 'flex-start',
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
								// We use "start/end" terminology because flex direction changes depending on RTL/LTR.
								'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
								'center'        => esc_html__( 'Center', 'fusion-builder' ),
								'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
								'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
								'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
								'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
							],
							'dependency'  => [
								[
									'element'  => 'buttons_layout',
									'value'    => 'floated',
									'operator' => '==',
								],
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_html__( 'Buttons Horizontal Align', 'fusion-builder' ),
							'description' => esc_html__( 'Change the horizontal alignment of buttons within its container column.', 'fusion-builder' ),
							'param_name'  => 'stacked_buttons_alignment',
							'grid_layout' => true,
							'back_icons'  => true,
							'icons'       => [
								'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
								'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
								'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
							],
							'value'       => [
								'flex-start' => esc_html__( 'Flex Start', 'fusion-builder' ),
								'center'     => esc_html__( 'Center', 'fusion-builder' ),
								'flex-end'   => esc_html__( 'Flex End', 'fusion-builder' ),
							],
							'default'     => 'flex-start',
							'dependency'  => [
								[
									'element'  => 'buttons_layout',
									'value'    => 'stacked',
									'operator' => '==',
								],
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],

						],
						[
							'type'        => 'radio_button_set',
							'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
							'description' => esc_attr__( 'Choose to have the button span the full width.', 'fusion-builder' ),
							'param_name'  => 'button_span',
							'value'       => [
								'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
								'no'  => esc_attr__( 'No', 'fusion-builder' ),
							],
							'default'     => 'no',
							'dependency'  => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Buttons Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'buttonsmargin',
							'value'            => [
								'button_margin_top'    => '',
								'button_margin_right'  => '',
								'button_margin_bottom' => '',
								'button_margin_left'   => '',
							],
							'dependency'       => [
								[
									'element'  => 'buttons_visibility',
									'value'    => 'show',
									'operator' => '==',
								],
							],
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'margin',
							'value'            => [
								'margin_top'    => '',
								'margin_right'  => '',
								'margin_bottom' => '',
								'margin_left'   => '',
							],
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						],
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woo-cart-totals-wrapper',
						],
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Table Cell Padding', 'fusion-builder' ),
							'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 5px 0 5px 0 value.', 'fusion-builder' ),
							'param_name'       => 'cell_padding',
							'value'            => [
								'cell_padding_top'    => '',
								'cell_padding_right'  => '',
								'cell_padding_bottom' => '',
								'cell_padding_left'   => '',
							],
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Heading Cell Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the heading cell background color. ', 'fusion-builder' ),
							'param_name'  => 'heading_cell_backgroundcolor',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Table Cell Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the table cell background color. ', 'fusion-builder' ),
							'param_name'  => 'table_cell_backgroundcolor',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'colorpickeralpha',
							'heading'     => esc_attr__( 'Table Border Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the color of the table border, ex: #000.' ),
							'param_name'  => 'border_color',
							'value'       => '',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'    => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'             => 'typography',
							'heading'          => esc_attr__( 'Heading Cell Typography', 'fusion-builder' ),
							'description'      => esc_html__( 'Controls the typography of the heading. Leave empty for the global font family.', 'fusion-builder' ),
							'param_name'       => 'heading_fonts',
							'choices'          => [
								'font-family'    => 'heading_font',
								'font-size'      => 'heading_font_size',
								'text-transform' => 'heading_text_transform',
								'line-height'    => 'heading_line_height',
								'letter-spacing' => 'heading_letter_spacing',
								'color'          => 'heading_color',
							],
							'default'          => [
								'font-family'    => '',
								'variant'        => '400',
								'font-size'      => '',
								'text-transform' => '',
								'line-height'    => '',
								'letter-spacing' => '',
								'color'          => '',
							],
							'remove_from_atts' => true,
							'global'           => true,
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'             => 'typography',
							'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
							'description'      => esc_html__( 'Controls the typography of the content text. Leave empty for the global font family.', 'fusion-builder' ),
							'param_name'       => 'text_fonts',
							'choices'          => [
								'font-family'    => 'text_font',
								'font-size'      => 'text_font_size',
								'text-transform' => 'text_text_transform',
								'line-height'    => 'text_line_height',
								'letter-spacing' => 'text_letter_spacing',
								'color'          => 'text_color',
							],
							'default'          => [
								'font-family'    => '',
								'variant'        => '400',
								'font-size'      => '',
								'text-transform' => '',
								'line-height'    => '',
								'letter-spacing' => '',
								'color'          => '',
							],
							'remove_from_atts' => true,
							'global'           => true,
							'group'            => esc_attr__( 'Design', 'fusion-builder' ),
							'callback'         => [
								'function' => 'fusion_style_block',
							],
						],
						[
							'type'        => 'checkbox_button_set',
							'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
							'param_name'  => 'hide_on_mobile',
							'value'       => fusion_builder_visibility_options( 'full' ),
							'default'     => fusion_builder_default_visibility( 'array' ),
							'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
							'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
							'param_name'  => 'class',
							'value'       => '',
						],
						[
							'type'        => 'textfield',
							'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
							'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
							'param_name'  => 'id',
							'value'       => '',
						],

					],
					'callback'      => [
						'function' => 'fusion_ajax',
						'action'   => 'fusion_get_woo_cart_totals',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_cart_totals' );
