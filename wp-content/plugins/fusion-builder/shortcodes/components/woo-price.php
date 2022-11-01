<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_price' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Price' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Price extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_woo_price' );
				add_filter( 'fusion_attr_fusion_tb_woo_price-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_price', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular();
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'show_sale'                            => 'yes',
					'sale_position'                        => 'right',
					'layout'                               => 'floated',
					'show_stock'                           => 'yes',
					'show_badge'                           => 'Yes',
					'discount_type'                        => 'percent',
					'badge_position'                       => 'right',
					'alignment'                            => 'flex-start',
					'price_font_size'                      => '',
					'price_text_transform'                 => '',
					'price_line_height'                    => '',
					'price_letter_spacing'                 => '',
					'price_color'                          => $fusion_settings->get( 'primary_color' ),
					'fusion_font_family_price_typography'  => 'inherit',
					'fusion_font_variant_price_typography' => '400',
					'sale_font_size'                       => '',
					'sale_text_transform'                  => '',
					'sale_line_height'                     => '',
					'sale_letter_spacing'                  => '',
					'sale_color'                           => $fusion_settings->get( 'body_typography', 'color' ),
					'fusion_font_family_sale_typography'   => 'inherit',
					'fusion_font_variant_sale_typography'  => '400',
					'stock_font_size'                      => '',
					'stock_text_transform'                 => '',
					'stock_line_height'                    => '',
					'stock_letter_spacing'                 => '',
					'stock_color'                          => $fusion_settings->get( 'body_typography', 'color' ),
					'fusion_font_family_stock_typography'  => 'inherit',
					'fusion_font_variant_stock_typography' => '400',
					'badge_font_size'                      => '',
					'badge_text_transform'                 => '',
					'badge_line_height'                    => '',
					'badge_letter_spacing'                 => '',
					'badge_text_color'                     => $fusion_settings->get( 'primary_color' ),
					'fusion_font_family_badge_typography'  => 'inherit',
					'fusion_font_variant_badge_typography' => '400',
					'badge_bg_color'                       => '',
					'badge_border_size'                    => '1',
					'badge_border_color'                   => $fusion_settings->get( 'primary_color' ),
					'border_radius_bottom_left'            => '',
					'border_radius_bottom_right'           => '',
					'border_radius_top_left'               => '',
					'border_radius_top_right'              => '',
					'margin_bottom'                        => '',
					'margin_left'                          => '',
					'margin_right'                         => '',
					'margin_top'                           => '',
					'hide_on_mobile'                       => fusion_builder_default_visibility( 'string' ),
					'class'                                => '',
					'id'                                   => '',
					'animation_type'                       => '',
					'animation_direction'                  => 'down',
					'animation_speed'                      => '0.1',
					'animation_offset'                     => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function ajax_render() {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$return_data = [];
				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_price' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					$this->emulate_product();

					if ( ! $this->is_product() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					$return_data['woo_price'] = $this->get_woo_price_content();
					$this->restore_product();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				global $product;

				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_price' );

				$border_radius_top_left      = $this->args['border_radius_top_left'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] ) : '0px';
				$border_radius_top_right     = $this->args['border_radius_top_right'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] ) : '0px';
				$border_radius_bottom_right  = $this->args['border_radius_bottom_right'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] ) : '0px';
				$border_radius_bottom_left   = $this->args['border_radius_bottom_left'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] ) : '0px';
				$this->args['border_radius'] = $border_radius_top_left . ' ' . $border_radius_top_right . ' ' . $border_radius_bottom_right . ' ' . $border_radius_bottom_left;

				$this->args['badge_border_size'] = FusionBuilder::validate_shortcode_attr_value( $this->args['badge_border_size'], 'px' );

				$this->emulate_product();

				if ( ! $this->is_product() || ! is_object( $product ) ) {
					return;
				}

				$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_price-shortcode' ) . '>' . $this->get_woo_price_content() . $this->get_styles() . '</div>';

				$this->restore_product();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Price element.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @return string
			 */
			public function get_woo_price_content() {
				global $avada_woocommerce;

				$content = '';

				if ( function_exists( 'woocommerce_template_single_price' ) ) {
					add_filter( 'woocommerce_product_price_class', [ $this, 'product_price_class' ], 20, 1 );
					ob_start();
					woocommerce_template_single_price();
					$content .= ob_get_clean();
					remove_filter( 'woocommerce_product_price_class', [ $this, 'product_price_class' ], 20 );
				}

				if ( function_exists( 'woocommerce_show_product_sale_flash' ) && 'no' !== $this->args['show_badge'] ) {

					if ( class_exists( 'Avada' ) && null !== $avada_woocommerce ) {
						remove_filter( 'woocommerce_sale_flash', [ $avada_woocommerce, 'modify_sale_badge' ], 20, 3 );
					}

					add_filter( 'woocommerce_sale_flash', [ $this, 'add_discount_to_sale_badge' ], 20, 3 );
					ob_start();
					woocommerce_show_product_sale_flash();
					$content .= ob_get_clean();
					remove_filter( 'woocommerce_sale_flash', [ $this, 'add_discount_to_sale_badge' ], 20 );

					if ( class_exists( 'Avada' ) && null !== $avada_woocommerce ) {
						add_filter( 'woocommerce_sale_flash', [ $avada_woocommerce, 'modify_sale_badge' ], 20, 3 );
					}
				}

				if ( function_exists( 'wc_get_stock_html' ) && 'no' !== $this->args['show_stock'] ) {
					$content .= wc_get_stock_html( $this->product );
				}

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-price-tb.fusion-woo-price-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$selectors = [
					$this->base_selector . ' .price',
					$this->base_selector . ' .price ins .amount',
					$this->base_selector . ' .price del .amount',
					$this->base_selector . ' .price > .amount',
				];

				if ( ! $this->is_default( 'price_font_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['price_font_size'] );
				}

				if ( ! $this->is_default( 'price_line_height' ) ) {
					$this->add_css_property( $selectors, 'line-height', $this->args['price_line_height'] );
				}

				if ( ! $this->is_default( 'price_letter_spacing' ) ) {
					$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['price_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'price_text_transform' ) ) {
					$this->add_css_property( $selectors, 'text-transform', $this->args['price_text_transform'] );
				}

				if ( ! $this->is_default( 'price_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['price_color'] );
				}

				$price_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'price_typography', 'array' );

				foreach ( $price_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}

				$selectors = [
					$this->base_selector . ' .price del .amount',
				];

				if ( ! $this->is_default( 'sale_font_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['sale_font_size'] );
				}

				if ( ! $this->is_default( 'sale_line_height' ) ) {
					$this->add_css_property( $selectors, 'line-height', $this->args['sale_line_height'] );
				}

				if ( ! $this->is_default( 'sale_letter_spacing' ) ) {
					$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['sale_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'sale_text_transform' ) ) {
					$this->add_css_property( $selectors, 'text-transform', $this->args['sale_text_transform'] );
				}

				if ( ! $this->is_default( 'sale_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['sale_color'] );
				}

				$sale_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'sale_typography', 'array' );

				foreach ( $sale_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}

				$selectors = [
					$this->base_selector . ' p.stock',
				];

				if ( ! $this->is_default( 'stock_font_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['stock_font_size'] );
				}

				if ( ! $this->is_default( 'stock_line_height' ) ) {
					$this->add_css_property( $selectors, 'line-height', $this->args['stock_line_height'] );
				}

				if ( ! $this->is_default( 'stock_letter_spacing' ) ) {
					$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['stock_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'stock_text_transform' ) ) {
					$this->add_css_property( $selectors, 'text-transform', $this->args['stock_text_transform'] );
				}

				if ( ! $this->is_default( 'stock_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['stock_color'] );
				}

				$stock_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'stock_typography', 'array' );

				foreach ( $stock_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}

				$selectors = [
					$this->base_selector . ' .fusion-onsale',
				];

				$this->add_css_property( $selectors, 'border-radius', $this->args['border_radius'] );

				if ( ! $this->is_default( 'badge_font_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['badge_font_size'] );
				}

				if ( ! $this->is_default( 'badge_line_height' ) ) {
					$this->add_css_property( $selectors, 'line-height', $this->args['badge_line_height'] );
				}

				if ( ! $this->is_default( 'badge_letter_spacing' ) ) {
					$this->add_css_property( $selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['badge_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'badge_text_transform' ) ) {
					$this->add_css_property( $selectors, 'text-transform', $this->args['badge_text_transform'] );
				}

				if ( ! $this->is_default( 'badge_text_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['badge_text_color'] );
				}

				$badge_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'badge_typography', 'array' );

				foreach ( $badge_styles as $rule => $value ) {
					$this->add_css_property( $selectors, $rule, $value );
				}

				if ( ! $this->is_default( 'badge_bg_color' ) ) {
					$this->add_css_property( $selectors, 'background', $this->args['badge_bg_color'] );
				}

				if ( ! $this->is_default( 'badge_border_size' ) ) {
					$this->add_css_property( $selectors, 'border-width', $this->args['badge_border_size'] );
				}

				if ( ! $this->is_default( 'badge_border_color' ) ) {
					$this->add_css_property( $selectors, 'border-color', $this->args['badge_border_color'] );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Adds discount value to sale badge.
			 *
			 * @static
			 * @access public
			 * @since 2.4
			 * @param string $html    The badge html.
			 * @param object $post    The post object.
			 * @param object $product The product object.
			 * @return string
			 */
			public function add_discount_to_sale_badge( $html, $post, $product ) {

				$percentage = fusion_library()->woocommerce->calc_product_discount( $product, $this->args['discount_type'] );

				/* translators: Discount in %, ie 15% Off. */
				return '<p class="fusion-onsale">' . sprintf( esc_html__( '%s Off', 'fusion-builder' ), $percentage ) . '</p>';
			}

			/**
			 * Adds class to product price.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @param string $class The existing class.
			 * @return string
			 */
			public function product_price_class( $class ) {
				global $product;
				if ( is_object( $product ) && $product->is_on_sale() ) {
					if ( $product->is_type( 'variable' ) ) {
						$prices = $product->get_variation_prices();
						$class .= ( 1 === count( array_unique( $prices['sale_price'] ) ) ) ? ' has-sale' : '';
					} else {
						$class .= ' has-sale';
					}
				}

				return $class;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-woo-price-tb fusion-woo-price-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( 'yes' !== $this->args['show_sale'] ) {
					$attr['class'] .= ' hide-sale';
				}

				if ( '' !== $this->args['sale_position'] ) {
					$attr['class'] .= ' sale-position-' . $this->args['sale_position'];
				}

				if ( '0px' !== $this->args['badge_border_size'] ) {
					$attr['class'] .= ' has-border';
				}

				if ( '' !== $this->args['layout'] ) {
					$attr['class'] .= ' ' . $this->args['layout'];
				}

				if ( '' !== $this->args['badge_position'] && 'no' !== $this->args['show_badge'] ) {
					$attr['class'] .= ' has-badge badge-position-' . $this->args['badge_position'];
				}

				if ( '' !== $this->args['alignment'] ) {
					$attr['style'] .= 'justify-content:' . $this->args['alignment'] . ';';
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
			 * @since 3.2
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-price.min.css' );
			}
		}
	}

	new FusionTB_Woo_Price();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_price() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Price',
			[
				'name'                    => esc_attr__( 'Woo Price', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_woo_price',
				'icon'                    => 'fusiona-woo-price',
				'subparam_map'            => [
					'price_font_size'                      => 'price_fonts',
					'price_text_transform'                 => 'price_fonts',
					'price_line_height'                    => 'price_fonts',
					'price_letter_spacing'                 => 'price_fonts',
					'price_color'                          => 'price_fonts',
					'fusion_font_family_price_typography'  => 'price_fonts',
					'fusion_font_variant_price_typography' => 'price_fonts',
					'sale_font_size'                       => 'sale_fonts',
					'sale_text_transform'                  => 'sale_fonts',
					'sale_line_height'                     => 'sale_fonts',
					'sale_letter_spacing'                  => 'sale_fonts',
					'sale_color'                           => 'sale_fonts',
					'fusion_font_family_sale_typography'   => 'sale_fonts',
					'fusion_font_variant_sale_typography'  => 'sale_fonts',
					'stock_font_size'                      => 'stock_fonts',
					'stock_text_transform'                 => 'stock_fonts',
					'stock_line_height'                    => 'stock_fonts',
					'stock_letter_spacing'                 => 'stock_fonts',
					'stock_color'                          => 'stock_fonts',
					'fusion_font_family_stock_typography'  => 'stock_fonts',
					'fusion_font_variant_stock_typography' => 'stock_fonts',
					'badge_font_size'                      => 'badge_fonts',
					'badge_text_transform'                 => 'badge_fonts',
					'badge_line_height'                    => 'badge_fonts',
					'badge_letter_spacing'                 => 'badge_fonts',
					'badge_text_color'                     => 'badge_fonts',
					'fusion_font_family_badge_typography'  => 'badge_fonts',
					'fusion_font_variant_badge_typography' => 'badge_fonts',
				],
				'component'               => true,
				'templates'               => [ 'content', 'post_cards', 'page_title_bar' ],
				'components_per_template' => 1,
				'params'                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Sale Old Price', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide sale old price.', 'fusion-builder' ),
						'param_name'  => 'show_sale',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Sale Old Price Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection for the sale old price position.', 'fusion-builder' ),
						'param_name'  => 'sale_position',
						'default'     => 'right',
						'value'       => [
							'left'  => esc_attr__( 'Before Regular', 'fusion-builder' ),
							'right' => esc_attr__( 'After Regular', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_sale',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Make a selection for layout. Floated will have the price and sale old price side by side. Stacked will have one per row.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'default'     => 'floated',
						'value'       => [
							'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_html__( 'Floated', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_sale',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Stock', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide stock.', 'fusion-builder' ),
						'param_name'  => 'show_stock',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_price',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Discount Badge', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide discount badge.', 'fusion-builder' ),
						'param_name'  => 'show_badge',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_price',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Discount Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection whether badge should show percentage discount or amount.', 'fusion-builder' ),
						'param_name'  => 'discount_type',
						'default'     => 'percent',
						'value'       => [
							'percent' => esc_attr__( 'Percentage', 'fusion-builder' ),
							'amount'  => esc_attr__( 'Amount', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_price',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Badge Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection for the badge position.', 'fusion-builder' ),
						'param_name'  => 'badge_position',
						'default'     => 'right',
						'value'       => [
							'left'  => esc_attr__( 'Before Price', 'fusion-builder' ),
							'right' => esc_attr__( 'After Price', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
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
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Price Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the price text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'price_fonts',
						'choices'          => [
							'font-family'    => 'price_typography',
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
							'color'          => $fusion_settings->get( 'primary_color' ),
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
						'heading'          => esc_attr__( 'Sale Old Price Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the sale old price text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'sale_fonts',
						'choices'          => [
							'font-family'    => 'sale_typography',
							'font-size'      => 'sale_font_size',
							'text-transform' => 'sale_text_transform',
							'line-height'    => 'sale_line_height',
							'letter-spacing' => 'sale_letter_spacing',
							'color'          => 'sale_color',
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
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_sale',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Stock Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the stock text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'stock_fonts',
						'choices'          => [
							'font-family'    => 'stock_typography',
							'font-size'      => 'stock_font_size',
							'text-transform' => 'stock_text_transform',
							'line-height'    => 'stock_line_height',
							'letter-spacing' => 'stock_letter_spacing',
							'color'          => 'stock_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'body_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_stock',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Discount Badge Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the discount badge text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'badge_fonts',
						'choices'          => [
							'font-family'    => 'badge_typography',
							'font-size'      => 'badge_font_size',
							'text-transform' => 'badge_text_transform',
							'line-height'    => 'badge_line_height',
							'letter-spacing' => 'badge_letter_spacing',
							'color'          => 'badge_text_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'primary_color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Discount Badge Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a color for the discount badge background.', 'fusion-builder' ),
						'param_name'  => 'badge_bg_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Discount Badge Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the Discount Badge. In pixels.', 'fusion-builder' ),
						'param_name'  => 'badge_border_size',
						'value'       => '1',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Discount Badge Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a color for the discount badge border.', 'fusion-builder' ),
						'param_name'  => 'badge_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'badge_border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Discount Badge Border Radius', 'fusion-builder' ),
						'description'      => __( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
						'dependency'       => [
							[
								'element'  => 'show_badge',
								'value'    => 'no',
								'operator' => '!=',
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-price-tb',
					],
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_price',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_price' );
