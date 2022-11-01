<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_cart' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Cart' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Cart extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $args;

			/**
			 * An array of the shortcode extras.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $extras;

			/**
			 * An array of the unmerged shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $params;

			/**
			 * Whether we are requesting from editor.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $live_ajax = false;

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
				parent::__construct( 'fusion_tb_woo_cart' );
				add_filter( 'fusion_attr_fusion_tb_woo_cart-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_cart-icon', [ $this, 'icon_attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_cart', [ $this, 'ajax_render' ] );

				$this->add_third_party_hooks();
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
					$args = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					fusion_set_live_data();

					// Ensure legacy templates are not used.
					if ( function_exists( 'Fusion_Builder_WooCommerce' ) ) {
						Fusion_Builder_WooCommerce()->init_single_product();
					}
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$this->live_ajax = true;

					$return_data['markup'] = $this->render( $args );
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'woocommerce_variations' => $fusion_settings->get( 'woocommerce_variations' ),
					'body_font_size'         => $fusion_settings->get( 'body_typography', 'font-size' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'woocommerce_variations' => 'woocommerce_variations',
					'body_typography'        => 'body_font_size',
				];
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

					// Variation tab.
					'border_sizes_top'                     => '',
					'border_sizes_right'                   => '',
					'border_sizes_bottom'                  => '',
					'border_sizes_left'                    => '',
					'border_color'                         => '',
					'cell_padding_top'                     => '',
					'cell_padding_right'                   => '',
					'cell_padding_bottom'                  => '',
					'cell_padding_left'                    => '',
					'cell_background'                      => '',
					'variation_layout'                     => 'floated',
					'label_area_width'                     => '',
					'text_align'                           => '',
					'label_color'                          => '',
					'label_font_size'                      => '',
					'label_text_transform'                 => '',
					'label_line_height'                    => '',
					'label_letter_spacing'                 => '',
					'fusion_font_family_label_typography'  => 'inherit',
					'fusion_font_variant_label_typography' => '400',
					'select_style'                         => '',
					'select_height'                        => '',
					'select_font_size'                     => '',
					'select_color'                         => '',
					'select_background'                    => '',
					'select_border_color'                  => '',
					'select_border_sizes_top'              => '',
					'select_border_sizes_right'            => '',
					'select_border_sizes_bottom'           => '',
					'select_border_sizes_left'             => '',
					'border_radius_top_left'               => '',
					'border_radius_top_right'              => '',
					'border_radius_bottom_right'           => '',
					'border_radius_bottom_left'            => '',
					'swatch_style'                         => '',
					'swatch_margin_top'                    => '',
					'swatch_margin_right'                  => '',
					'swatch_margin_bottom'                 => '',
					'swatch_margin_left'                   => '',
					'swatch_background_color'              => '',
					'swatch_background_color_active'       => '',
					'swatch_border_sizes_top'              => '',
					'swatch_border_sizes_right'            => '',
					'swatch_border_sizes_bottom'           => '',
					'swatch_border_sizes_left'             => '',
					'swatch_border_color'                  => '',
					'swatch_border_color_active'           => '',
					'color_swatch_height'                  => '',
					'color_swatch_width'                   => '',
					'color_swatch_padding_top'             => '',
					'color_swatch_padding_right'           => '',
					'color_swatch_padding_bottom'          => '',
					'color_swatch_padding_left'            => '',
					'color_swatch_border_radius_top_left'  => '',
					'color_swatch_border_radius_top_right' => '',
					'color_swatch_border_radius_bottom_right' => '',
					'color_swatch_border_radius_bottom_left' => '',
					'image_swatch_height'                  => '',
					'image_swatch_width'                   => '',
					'image_swatch_padding_top'             => '',
					'image_swatch_padding_right'           => '',
					'image_swatch_padding_bottom'          => '',
					'image_swatch_padding_left'            => '',
					'image_swatch_border_radius_top_left'  => '',
					'image_swatch_border_radius_top_right' => '',
					'image_swatch_border_radius_bottom_right' => '',
					'image_swatch_border_radius_bottom_left' => '',
					'button_swatch_height'                 => '',
					'button_swatch_width'                  => '',
					'button_swatch_padding_top'            => '',
					'button_swatch_padding_right'          => '',
					'button_swatch_padding_bottom'         => '',
					'button_swatch_padding_left'           => '',
					'button_swatch_border_radius_top_left' => '',
					'button_swatch_border_radius_top_right' => '',
					'button_swatch_border_radius_bottom_right' => '',
					'button_swatch_border_radius_bottom_left' => '',
					'button_swatch_font_size'              => '',
					'button_swatch_color'                  => '',
					'button_swatch_color_active'           => '',

					// Details.
					'info_padding_top'                     => '',
					'info_padding_right'                   => '',
					'info_padding_bottom'                  => '',
					'info_padding_left'                    => '',
					'info_background'                      => '',
					'info_border_sizes_top'                => '',
					'info_border_sizes_right'              => '',
					'info_border_sizes_bottom'             => '',
					'info_border_sizes_left'               => '',
					'info_border_color'                    => '',
					'info_border_radius_top_left'          => '',
					'info_border_radius_top_right'         => '',
					'info_border_radius_bottom_right'      => '',
					'info_border_radius_bottom_left'       => '',
					'info_align'                           => 'flex-start',
					'description_color'                    => '',
					'description_font_size'                => '',
					'description_text_transform'           => '',
					'description_line_height'              => '',
					'description_letter_spacing'           => '',
					'fusion_font_family_description_typography' => 'inherit',
					'fusion_font_variant_description_typography' => '400',
					'description_order'                    => 'before',
					'show_sale'                            => 'yes',
					'sale_order'                           => 'after',
					'show_price'                           => 'yes',
					'price_font_size'                      => '',
					'price_text_transform'                 => '',
					'price_line_height'                    => '',
					'price_letter_spacing'                 => '',
					'price_color'                          => '',
					'sale_font_size'                       => '',
					'sale_text_transform'                  => '',
					'sale_line_height'                     => '',
					'sale_letter_spacing'                  => '',
					'sale_color'                           => '',
					'show_stock'                           => 'yes',
					'stock_font_size'                      => '',
					'stock_text_transform'                 => '',
					'stock_line_height'                    => '',
					'stock_letter_spacing'                 => '',
					'stock_color'                          => '',
					'fusion_font_family_price_typography'  => 'inherit',
					'fusion_font_variant_price_typography' => '400',
					'fusion_font_family_sale_typography'   => 'inherit',
					'fusion_font_variant_sale_typography'  => '400',
					'fusion_font_family_stock_typography'  => 'inherit',
					'fusion_font_variant_stock_typography' => '400',
					'variation_clear'                      => 'absolute',
					'clear_content'                        => '',
					'clear_icon'                           => '',
					'clear_text'                           => '',
					'clear_margin_top'                     => '',
					'clear_margin_right'                   => '',
					'clear_margin_bottom'                  => '',
					'clear_margin_left'                    => '',
					'clear_color'                          => '',
					'clear_color_hover'                    => '',

					// Cart.
					'button_margin_top'                    => '',
					'button_margin_right'                  => '',
					'button_margin_bottom'                 => '',
					'button_margin_left'                   => '',
					'button_layout'                        => 'floated',
					'button_align'                         => 'flex-start',
					'button_justify'                       => 'flex-start',
					'quantity_style'                       => '',
					'quantity_width'                       => '',
					'quantity_height'                      => '',
					'quantity_radius_top_left'             => '',
					'quantity_radius_top_right'            => '',
					'quantity_radius_bottom_right'         => '',
					'quantity_radius_bottom_left'          => '',
					'quantity_font_size'                   => '',
					'quantity_color'                       => '',
					'quantity_background'                  => '',
					'quantity_border_sizes_top'            => '',
					'quantity_border_sizes_right'          => '',
					'quantity_border_sizes_bottom'         => '',
					'quantity_border_sizes_left'           => '',
					'quantity_border_color'                => '',
					'qbutton_border_sizes_top'             => '',
					'qbutton_border_sizes_right'           => '',
					'qbutton_border_sizes_bottom'          => '',
					'qbutton_border_sizes_left'            => '',
					'qbutton_color'                        => '',
					'qbutton_background'                   => '',
					'qbutton_border_color'                 => '',
					'qbutton_color_hover'                  => '',
					'qbutton_background_hover'             => '',
					'qbutton_border_color_hover'           => '',
					'button_style'                         => '',
					'button_size'                          => '',
					'button_stretch'                       => 'no',
					'button_border_top'                    => '',
					'button_border_right'                  => '',
					'button_border_bottom'                 => '',
					'button_border_left'                   => '',

					'button_icon'                          => '',
					'icon_position'                        => 'left',
					'button_color'                         => '',
					'button_gradient_top'                  => $fusion_settings->get( 'button_gradient_top_color' ),
					'button_gradient_bottom'               => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'button_border_color'                  => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'button_color_hover'                   => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'button_gradient_top_hover'            => '',
					'button_gradient_bottom_hover'         => '',
					'button_border_color_hover'            => '',
				];
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
				$this->emulate_product();

				if ( ! $this->is_product() ) {
					return;
				}
				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tb_woo_cart' );
				$this->extras   = self::get_element_extras();

				// Legacy single border width.
				if ( isset( $args['button_border_width'] ) && ! isset( $args['button_border_top'] ) ) {
					$this->args['button_border_top']    = $args['button_border_width'];
					$this->args['button_border_right']  = $this->args['button_border_top'];
					$this->args['button_border_bottom'] = $this->args['button_border_top'];
					$this->args['button_border_left']   = $this->args['button_border_top'];
				}

				if ( 'always' === $this->args['show_price'] ) {
					add_filter( 'woocommerce_show_variation_price', [ $this, 'always_show_price' ], 10, 3 );
				}

				if ( ! empty( $this->args['button_icon'] ) ) {
					add_filter( 'woocommerce_product_single_add_to_cart_text', [ $this, 'add_icon_placeholder' ], 20 );
				}
				if ( ! $this->is_default( 'clear_content' ) ) {
					add_filter( 'woocommerce_reset_variations_link', [ $this, 'clear_link' ], 20 );
				}
				add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'button_wrapper_open' ] );
				add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'button_wrapper_close' ] );

				if ( $this->live_ajax ) {
					$html = $this->get_cart();
				} else {
					$html = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_cart-shortcode' ) . '>' . $this->get_cart() . $this->get_styles() . '</div>';
				}

				if ( 'always' === $this->args['show_price'] ) {
					remove_filter( 'woocommerce_show_variation_price', [ $this, 'always_show_price' ], 10, 3 );
				}

				if ( ! empty( $this->args['button_icon'] ) ) {
					remove_filter( 'woocommerce_product_single_add_to_cart_text', [ $this, 'add_icon_placeholder' ], 20 );
					$html = str_replace( '@|@', '<i ' . FusionBuilder::attributes( 'cart-icon' ) . '></i>', $html );
				}
				if ( ( 'text' === $this->args['clear_content'] && ! $this->is_default( 'clear_text' ) ) || ( 'icon' === $this->args['clear_content'] && ! $this->is_default( 'clear_icon' ) ) ) {
					remove_filter( 'woocommerce_reset_variations_link', [ $this, 'clear_link' ], 20 );
				}
				remove_action( 'woocommerce_before_add_to_cart_button', [ $this, 'button_wrapper_open' ] );
				remove_action( 'woocommerce_after_add_to_cart_button', [ $this, 'button_wrapper_close' ] );

				$this->restore_product();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Show the variation price, even if all variations share the same price.
			 *
			 * @access public
			 * @since 3.7
			 * @param bool                $show_price Holds if the variation price should be shown.
			 * @param WC_Product_Variable $variation Current product variation.
			 * @param WC_Product          $product The current variable product.
			 * @return bool Will always be true to make sure price is shown regardless.
			 */
			public function always_show_price( $show_price, $variation, $product ) {
				return true;
			}


			/**
			 * Add an icon to the button text.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $text Button text.
			 * @return string
			 */
			public function add_icon_placeholder( $text = '' ) {
				if ( 'left' === $this->args['icon_position'] ) {
					return '@|@' . $text;
				}
				return $text . '@|@';
			}

			/**
			 * Wrap button and quantity for consistent styling.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function button_wrapper_open() {
				echo '<div class="fusion-button-wrapper">';
			}

			/**
			 * Closing button wrapper.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function button_wrapper_close() {
				echo '</div>';
			}

			/**
			 * Builds HTML for Woo Cart element.
			 *
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_cart() {
				$content = '';
				ob_start();
				do_action( 'awb_before_woo_add_to_cart_content' );
				woocommerce_template_single_add_to_cart();
				do_action( 'awb_after_woo_add_to_cart_content' );
				$content .= ob_get_clean();

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.0
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-cart-' . $this->counter;
				$this->dynamic_css   = [];
				$fusion_settings     = awb_get_fusion_settings();

				// Variation margins.
				$table = $this->base_selector . ' table.variations';
				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $table, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $table, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $table, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $table, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				$table_td = $this->base_selector . ' table tr > *';

				// Border size.
				if ( ! $this->is_default( 'border_sizes_top' ) ) {
					$this->add_css_property( $table_td, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_top'] ) );
				}
				if ( ! $this->is_default( 'border_sizes_right' ) ) {
					$this->add_css_property( $table_td, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_right'] ) );
				}
				if ( ! $this->is_default( 'border_sizes_bottom' ) ) {
					$this->add_css_property( $table_td, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_bottom'] ) );
				}
				if ( ! $this->is_default( 'border_sizes_left' ) ) {
					$this->add_css_property( $table_td, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_left'] ) );
				}

				// Border color.
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $table_td, 'border-color', $this->args['border_color'] );
				}

				// Cell padding.
				if ( ! $this->is_default( 'cell_padding_top' ) ) {
					$this->add_css_property( $table_td, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['cell_padding_top'] ) );
				}
				if ( ! $this->is_default( 'cell_padding_right' ) ) {
					$this->add_css_property( $table_td, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['cell_padding_right'] ) );
				}
				if ( ! $this->is_default( 'cell_padding_bottom' ) ) {
					$this->add_css_property( $table_td, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['cell_padding_bottom'] ) );
				}
				if ( ! $this->is_default( 'cell_padding_left' ) ) {
					$this->add_css_property( $table_td, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['cell_padding_left'] ) );
				}

				// Cell background.
				if ( ! $this->is_default( 'cell_background' ) ) {
					$this->add_css_property( $table_td, 'background-color', $this->args['cell_background'] );
				}

				// Variation layout.
				$label = $this->base_selector . ' tr .label';
				if ( 'floated' !== $this->args['variation_layout'] ) {
					$table_tr = $this->base_selector . ' table tr';
					$this->add_css_property( $table_tr, 'display', 'flex' );
					$this->add_css_property( $table_tr, 'flex-direction', 'column' );
					$this->add_css_property( $table_tr, 'width', '100%' );
				} elseif ( ! $this->is_default( 'label_area_width' ) ) {
					$this->add_css_property( $label, 'width', fusion_library()->sanitize->get_value_with_unit( $this->args['label_area_width'] ) );
				}

				// Label align.
				if ( ! $this->is_default( 'text_align' ) ) {
					$this->add_css_property( $label, 'text-align', $this->args['text_align'] );

					$map_flex = [
						'center' => 'center',
						'left'   => ( is_rtl() ? 'flex-end' : 'flex-start' ),
						'right'  => ( is_rtl() ? 'flex-start' : 'flex-end' ),
					];
					$this->add_css_property( $table . ' .avada-select-wrapper', 'justify-content', $map_flex[ $this->args['text_align'] ] );
				}

				// Label text styling, share with grouped.
				$label = [
					$this->base_selector . ' tr .label',
					$this->base_selector . ' .woocommerce-grouped-product-list label',
					$this->base_selector . ' .woocommerce-grouped-product-list label a',
					$this->base_selector . ' .woocommerce-grouped-product-list .amount',
				];

				// Label text color.
				if ( ! $this->is_default( 'label_color' ) ) {
					$this->add_css_property( $label, 'color', $this->args['label_color'] );
				}

				// Label font size.
				if ( ! $this->is_default( 'label_font_size' ) ) {
					$this->add_css_property( $label, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['label_font_size'] ) );
				}

				if ( ! $this->is_default( 'label_line_height' ) ) {
					$this->add_css_property( $label, 'line-height', $this->args['label_line_height'] );
				}

				if ( ! $this->is_default( 'label_letter_spacing' ) ) {
					$this->add_css_property( $label, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['label_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'label_text_transform' ) ) {
					$this->add_css_property( $label, 'text-transform', $this->args['label_text_transform'] );
				}

				// Font family and weight.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'label_typography', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $label, $rule, $value );
				}

				// Select variation type styling.
				if ( ! $this->is_default( 'select_style' ) ) {
					$select = $table . ' select';
					$arrow  = $table . ' .select-arrow';
					$both   = [ $select, $arrow ];

					// Select height.
					if ( ! $this->is_default( 'select_height' ) ) {
						$this->add_css_property( $select, 'height', fusion_library()->sanitize->get_value_with_unit( $this->args['select_height'] ) );
					}

					// Select text size.
					if ( ! $this->is_default( 'select_font_size' ) ) {
						$this->add_css_property( $select, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['select_font_size'] ) );
						$this->add_css_property( $arrow, 'font-size', 'calc( ( ' . fusion_library()->sanitize->get_value_with_unit( $this->args['select_font_size'] ) . ' ) * .75 )', true );
					}

					// Select text color.
					if ( ! $this->is_default( 'select_color' ) ) {
						$this->add_css_property( $both, 'color', $this->args['select_color'] );
					}

					// Select background.
					if ( ! $this->is_default( 'select_background' ) ) {
						$this->add_css_property( $select, 'background-color', $this->args['select_background'] );
					}

					// Border color.
					if ( ! $this->is_default( 'select_border_color' ) ) {
						$border_colors = [
							$select,
							$select . ':focus',
						];
						$this->add_css_property( $border_colors, 'border-color', $this->args['select_border_color'] );
					}

					// Select borders.
					if ( ! $this->is_default( 'select_border_sizes_top' ) && '' !== $this->args['select_border_sizes_top'] ) {
						$this->add_css_property( $select, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_top'] ) );
						$this->add_css_property( $arrow, 'top', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_top'] ) );
					}
					if ( ! $this->is_default( 'select_border_sizes_right' ) && '' !== $this->args['select_border_sizes_right'] ) {
						$this->add_css_property( $select, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_right'] ) );
					}
					if ( ! $this->is_default( 'select_border_sizes_bottom' ) && '' !== $this->args['select_border_sizes_bottom'] ) {
						$this->add_css_property( $select, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_bottom'] ) );
						$this->add_css_property( $arrow, 'bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_bottom'] ) );
					}
					if ( ! $this->is_default( 'select_border_sizes_left' ) && '' !== $this->args['select_border_sizes_left'] ) {
						$this->add_css_property( $select, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_left'] ) );
					}

					// Border separator with arrow.
					if ( ! $this->is_default( 'select_border_color' ) && ! $this->is_default( 'select_border_sizes_right' ) && ! $this->is_default( 'select_border_sizes_left' ) ) {
						$this->add_css_property( $arrow, 'border-left', fusion_library()->sanitize->get_value_with_unit( $this->args['select_border_sizes_left'] ) . ' solid ' . $this->args['select_border_color'] );
					}

					// Select border radius.
					if ( ! $this->is_default( 'border_radius_top_left' ) ) {
						$this->add_css_property( $select, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] ) );
					}
					if ( ! $this->is_default( 'border_radius_top_right' ) ) {
						$this->add_css_property( $select, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] ) );
					}
					if ( ! $this->is_default( 'border_radius_bottom_right' ) ) {
						$this->add_css_property( $select, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] ) );
					}
					if ( ! $this->is_default( 'border_radius_bottom_left' ) ) {
						$this->add_css_property( $select, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] ) );
					}
				}

				// Swatch styling if enabled.
				if ( ! $this->is_default( 'swatch_style' ) && $fusion_settings->get( 'woocommerce_variations' ) ) {
					$color_swatch    = $table . ' .avada-color-select';
					$image_swatch    = $table . ' .avada-image-select';
					$button_swatch   = $table . ' .avada-button-select';
					$swatches        = [
						$color_swatch,
						$image_swatch,
						$button_swatch,
					];
					$active_swatches = [
						$color_swatch . '[data-checked]',
						$image_swatch . '[data-checked]',
						$button_swatch . '[data-checked]',
					];
					$hover_swatches  = [
						$color_swatch . ':hover',
						$image_swatch . ':hover',
						$button_swatch . ':hover',
						$color_swatch . ':focus:not( [data-checked] )',
						$image_swatch . ':focus:not( [data-checked] )',
						$button_swatch . ':focus:not( [data-checked] )',
					];

					// General swatch styling.
					if ( ! $this->is_default( 'swatch_margin_top' ) ) {
						$this->add_css_property( $swatches, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_margin_top'] ) );
					}
					if ( ! $this->is_default( 'swatch_margin_right' ) ) {
						$this->add_css_property( $swatches, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_margin_right'] ) );
					}
					if ( ! $this->is_default( 'swatch_margin_bottom' ) ) {
						$this->add_css_property( $swatches, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_margin_bottom'] ) );
					}
					if ( ! $this->is_default( 'swatch_margin_left' ) ) {
						$this->add_css_property( $swatches, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_margin_left'] ) );
					}

					if ( ! $this->is_default( 'swatch_background_color' ) ) {
						$this->add_css_property( $swatches, 'background-color', $this->args['swatch_background_color'] );
					}
					if ( ! $this->is_default( 'swatch_background_color_active' ) ) {
						$this->add_css_property( $active_swatches, 'background-color', $this->args['swatch_background_color_active'] );
					}

					if ( ! $this->is_default( 'swatch_border_sizes_top' ) && '' !== $this->args['swatch_border_sizes_top'] ) {
						$this->add_css_property( $swatches, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_border_sizes_top'] ) );
					}
					if ( ! $this->is_default( 'swatch_border_sizes_right' ) && '' !== $this->args['swatch_border_sizes_right'] ) {
						$this->add_css_property( $swatches, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_border_sizes_right'] ) );
					}
					if ( ! $this->is_default( 'swatch_border_sizes_bottom' ) && '' !== $this->args['swatch_border_sizes_bottom'] ) {
						$this->add_css_property( $swatches, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_border_sizes_bottom'] ) );
					}
					if ( ! $this->is_default( 'swatch_border_sizes_left' ) && '' !== $this->args['swatch_border_sizes_left'] ) {
						$this->add_css_property( $swatches, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['swatch_border_sizes_left'] ) );
					}

					if ( ! $this->is_default( 'swatch_border_color' ) ) {
						$this->add_css_property( $swatches, 'border-color', $this->args['swatch_border_color'] );
					}

					if ( ! $this->is_default( 'swatch_border_color_active' ) ) {
						$this->add_css_property( $active_swatches, 'border-color', $this->args['swatch_border_color_active'] );

						$hover_color = Fusion_Color::new_color( $this->args['swatch_border_color_active'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
						$this->add_css_property( $hover_swatches, 'border-color', $hover_color );
					}

					// Color swatch.
					if ( ! $this->is_default( 'color_swatch_height' ) ) {
						$this->add_css_property( $color_swatch, 'height', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_height'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_width' ) ) {
						$width = 'auto' === $this->args['color_swatch_width'] ? 'auto' : fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_width'] );
						$this->add_css_property( $color_swatch, 'width', $width );
					}
					if ( ! $this->is_default( 'color_swatch_padding_top' ) && '' !== $this->args['color_swatch_padding_top'] ) {
						$this->add_css_property( $color_swatch, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_padding_top'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_padding_right' ) && '' !== $this->args['color_swatch_padding_right'] ) {
						$this->add_css_property( $color_swatch, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_padding_right'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_padding_bottom' ) && '' !== $this->args['color_swatch_padding_bottom'] ) {
						$this->add_css_property( $color_swatch, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_padding_bottom'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_padding_left' ) && '' !== $this->args['color_swatch_padding_left'] ) {
						$this->add_css_property( $color_swatch, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_padding_left'] ) );
					}

					$color_swatch_radius = [
						$color_swatch,
						$color_swatch . ' span',
					];
					if ( ! $this->is_default( 'color_swatch_border_radius_top_left' ) ) {
						$this->add_css_property( $color_swatch_radius, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_border_radius_top_left'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_border_radius_top_right' ) ) {
						$this->add_css_property( $color_swatch_radius, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_border_radius_top_right'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_border_radius_bottom_right' ) ) {
						$this->add_css_property( $color_swatch_radius, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_border_radius_bottom_right'] ) );
					}
					if ( ! $this->is_default( 'color_swatch_border_radius_bottom_left' ) ) {
						$this->add_css_property( $color_swatch_radius, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['color_swatch_border_radius_bottom_left'] ) );
					}

					// Image swatch.
					if ( ! $this->is_default( 'image_swatch_height' ) ) {
						$this->add_css_property( $image_swatch, 'height', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_height'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_width' ) ) {
						$width = 'auto' === $this->args['image_swatch_width'] ? 'auto' : fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_width'] );
						$this->add_css_property( $image_swatch, 'width', $width );
						if ( 'auto' !== $this->args['image_swatch_width'] ) {
							$this->add_css_property( $image_swatch . ' img', 'width', '100%' );
						}
					}
					if ( ! $this->is_default( 'image_swatch_padding_top' ) && '' !== $this->args['image_swatch_padding_top'] ) {
						$this->add_css_property( $image_swatch, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_padding_top'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_padding_right' ) && '' !== $this->args['image_swatch_padding_right'] ) {
						$this->add_css_property( $image_swatch, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_padding_right'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_padding_bottom' ) && '' !== $this->args['image_swatch_padding_bottom'] ) {
						$this->add_css_property( $image_swatch, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_padding_bottom'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_padding_left' ) && '' !== $this->args['image_swatch_padding_left'] ) {
						$this->add_css_property( $image_swatch, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_padding_left'] ) );
					}

					$image_swatch_radius = [
						$image_swatch,
						$image_swatch . ' img',
					];
					if ( ! $this->is_default( 'image_swatch_border_radius_top_left' ) ) {
						$this->add_css_property( $image_swatch_radius, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_border_radius_top_left'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_border_radius_top_right' ) ) {
						$this->add_css_property( $image_swatch_radius, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_border_radius_top_right'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_border_radius_bottom_right' ) ) {
						$this->add_css_property( $image_swatch_radius, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_border_radius_bottom_right'] ) );
					}
					if ( ! $this->is_default( 'image_swatch_border_radius_bottom_left' ) ) {
						$this->add_css_property( $image_swatch_radius, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['image_swatch_border_radius_bottom_left'] ) );
					}

					// Button swatch.
					if ( ! $this->is_default( 'button_swatch_height' ) ) {
						$this->add_css_property( $button_swatch, 'height', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_height'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_width' ) ) {
						$width = 'auto' === $this->args['button_swatch_width'] ? 'auto' : fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_width'] );
						$this->add_css_property( $button_swatch, 'width', $width );
					}
					if ( ! $this->is_default( 'button_swatch_padding_top' ) && '' !== $this->args['button_swatch_padding_top'] ) {
						$this->add_css_property( $button_swatch, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_padding_top'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_padding_right' ) && '' !== $this->args['button_swatch_padding_right'] ) {
						$this->add_css_property( $button_swatch, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_padding_right'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_padding_bottom' ) && '' !== $this->args['button_swatch_padding_bottom'] ) {
						$this->add_css_property( $button_swatch, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_padding_bottom'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_padding_left' ) && '' !== $this->args['button_swatch_padding_left'] ) {
						$this->add_css_property( $button_swatch, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_padding_left'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_border_radius_top_left' ) ) {
						$this->add_css_property( $button_swatch, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_border_radius_top_left'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_border_radius_top_right' ) ) {
						$this->add_css_property( $button_swatch, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_border_radius_top_right'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_border_radius_bottom_right' ) ) {
						$this->add_css_property( $button_swatch, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_border_radius_bottom_right'] ) );
					}
					if ( ! $this->is_default( 'button_swatch_border_radius_bottom_left' ) ) {
						$this->add_css_property( $button_swatch, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_border_radius_bottom_left'] ) );
					}

					if ( ! $this->is_default( 'button_swatch_font_size' ) ) {
						$this->add_css_property( $button_swatch, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['button_swatch_font_size'] ) );
					}

					if ( ! $this->is_default( 'button_swatch_color' ) ) {
						$this->add_css_property( $button_swatch, 'color', $this->args['button_swatch_color'] );
					}
					if ( ! $this->is_default( 'button_swatch_color_active' ) ) {
						$full_swatches = [
							$color_swatch . '[data-checked]',
							$image_swatch . '[data-checked]',
							$button_swatch . '[data-checked]',
							$color_swatch . ':hover',
							$image_swatch . ':hover',
							$button_swatch . ':hover',
							$color_swatch . ':focus',
							$image_swatch . ':focus',
							$button_swatch . ':focus',
						];
						$this->add_css_property( $full_swatches, 'color', $this->args['button_swatch_color_active'] );
					}
				}

				$info = $this->base_selector . ' .woocommerce-variation';

				// Info padding.
				if ( ! $this->is_default( 'info_padding_top' ) ) {
					$this->add_css_property( $info, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['info_padding_top'] ) );
				}
				if ( ! $this->is_default( 'info_padding_right' ) ) {
					$this->add_css_property( $info, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['info_padding_right'] ) );
				}
				if ( ! $this->is_default( 'info_padding_bottom' ) ) {
					$this->add_css_property( $info, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['info_padding_bottom'] ) );
				}
				if ( ! $this->is_default( 'info_padding_left' ) ) {
					$this->add_css_property( $info, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['info_padding_left'] ) );
				}

				// Info background.
				if ( ! $this->is_default( 'info_background' ) ) {
					$this->add_css_property( $info, 'background-color', $this->args['info_background'] );
				}

				// Info border size.
				if ( ! $this->is_default( 'info_border_sizes_top' ) ) {
					$this->add_css_property( $info, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_sizes_top'] ) );
				}
				if ( ! $this->is_default( 'info_border_sizes_right' ) ) {
					$this->add_css_property( $info, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_sizes_right'] ) );
				}
				if ( ! $this->is_default( 'info_border_sizes_bottom' ) ) {
					$this->add_css_property( $info, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_sizes_bottom'] ) );
				}
				if ( ! $this->is_default( 'info_border_sizes_left' ) ) {
					$this->add_css_property( $info, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_sizes_left'] ) );
				}

				// Info border color.
				if ( ! $this->is_default( 'info_border_color' ) ) {
					$this->add_css_property( $info, 'border-color', $this->args['info_border_color'] );
				}

				// Info border radius.
				if ( ! $this->is_default( 'info_border_radius_top_left' ) ) {
					$this->add_css_property( $info, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_radius_top_left'] ) );
				}
				if ( ! $this->is_default( 'info_border_radius_top_right' ) ) {
					$this->add_css_property( $info, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_radius_top_right'] ) );
				}
				if ( ! $this->is_default( 'info_border_radius_bottom_right' ) ) {
					$this->add_css_property( $info, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_radius_bottom_right'] ) );
				}
				if ( ! $this->is_default( 'info_border_radius_bottom_left' ) ) {
					$this->add_css_property( $info, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['info_border_radius_bottom_left'] ) );
				}

				$description = $info . ' .woocommerce-variation-description';

				// Info text align.
				if ( ! $this->is_default( 'info_align' ) ) {
					$this->add_css_property( $info, 'justify-content', $this->args['info_align'] );

					$direction = is_rtl() ? 'right' : 'left';
					if ( 'flex-end' === $this->args['info_align'] ) {
						$direction = is_rtl() ? 'left' : 'right';
					} elseif ( 'center' === $this->args['info_align'] ) {
						$direction = 'center';
					}
					$this->add_css_property( $description, 'text-align', $direction );
				}

				// Description text color.
				if ( ! $this->is_default( 'description_color' ) ) {
					$this->add_css_property( $description, 'color', $this->args['description_color'] );
				}

				if ( ! $this->is_default( 'description_font_size' ) ) {
					$this->add_css_property( $description, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['description_font_size'] ) );
				}

				if ( ! $this->is_default( 'description_line_height' ) ) {
					$this->add_css_property( $description, 'line-height', $this->args['description_line_height'] );
				}

				if ( ! $this->is_default( 'description_letter_spacing' ) ) {
					$this->add_css_property( $description, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['description_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'description_text_transform' ) ) {
					$this->add_css_property( $description, 'text-transform', $this->args['description_text_transform'] );
				}

				// Description font family and weight.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'description_typography', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $description, $rule, $value );
				}

				// Order for description.
				if ( 'after' === $this->args['description_order'] ) {
					$this->add_css_property( $description, 'order', '2' );
				}

				// Hide old sale price.
				if ( 'no' === $this->args['show_sale'] ) {
					$this->add_css_property( $info . ' .price del', 'display', 'none' );
				}

				// Sale order.
				if ( 'before' === $this->args['sale_order'] ) {
					$this->add_css_property( $info . ' .price del', 'margin-' . ( is_rtl() ? 'left' : 'right' ), '0.5em' );
				} else {
					$this->add_css_property( $info . ' .price', 'flex-direction', 'row-reverse' );
					$this->add_css_property( $info . ' .price del', 'margin-' . ( is_rtl() ? 'right' : 'left' ), '0.5em' );
				}

				// Price font size.
				$prices = [
					$info . ' .price',
					$info . ' .price > .amount',
					$info . ' .price ins .amount',
				];
				if ( ! $this->is_default( 'price_font_size' ) ) {
					$this->add_css_property( $prices, 'font-size', $this->args['price_font_size'] );
				}

				if ( ! $this->is_default( 'price_line_height' ) ) {
					$this->add_css_property( $prices, 'line-height', $this->args['price_line_height'] );
				}

				if ( ! $this->is_default( 'price_letter_spacing' ) ) {
					$this->add_css_property( $prices, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['price_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'price_text_transform' ) ) {
					$this->add_css_property( $prices, 'text-transform', $this->args['price_text_transform'] );
				}

				// Price font color.
				if ( ! $this->is_default( 'price_color' ) ) {
					$this->add_css_property( $prices, 'color', $this->args['price_color'] );
				}

				// Price font family and weight.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'price_typography', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $prices, $rule, $value );
				}

				// Sale font size.
				$sales = [
					$info . ' .price del',
					$info . ' .price del .amount',
				];
				if ( ! $this->is_default( 'sale_font_size' ) ) {
					$this->add_css_property( $sales, 'font-size', $this->args['sale_font_size'] );
				}

				if ( ! $this->is_default( 'sale_line_height' ) ) {
					$this->add_css_property( $sales, 'line-height', $this->args['sale_line_height'] );
				}

				if ( ! $this->is_default( 'sale_letter_spacing' ) ) {
					$this->add_css_property( $sales, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['sale_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'sale_text_transform' ) ) {
					$this->add_css_property( $sales, 'text-transform', $this->args['sale_text_transform'] );
				}

				// Sale font color.
				if ( ! $this->is_default( 'sale_color' ) ) {
					$this->add_css_property( $sales, 'color', $this->args['sale_color'] );
				}

				// Sale font family and weight.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'sale_typography', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $sales, $rule, $value );
				}

				// Stock font size.
				$stock = [
					$this->base_selector . ' .stock',
					$info . ' .woocommerce-variation-availability',
				];
				if ( ! $this->is_default( 'stock_font_size' ) ) {
					$this->add_css_property( $stock, 'font-size', $this->args['stock_font_size'] );
				}

				if ( ! $this->is_default( 'stock_line_height' ) ) {
					$this->add_css_property( $stock, 'line-height', $this->args['stock_line_height'] );
				}

				if ( ! $this->is_default( 'stock_letter_spacing' ) ) {
					$this->add_css_property( $stock, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['stock_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'stock_text_transform' ) ) {
					$this->add_css_property( $stock, 'text-transform', $this->args['stock_text_transform'] );
				}

				// Stock font color.
				if ( ! $this->is_default( 'stock_color' ) ) {
					$this->add_css_property( $stock, 'color', $this->args['stock_color'] );
				}

				// Stock font family and weight.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'stock_typography', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $stock, $rule, $value );
				}

				// Variation clear.
				$variation_clear = $this->base_selector . ' .reset_variations';
				if ( 'hide' !== $this->args['variation_clear'] ) {

					if ( 'absolute' !== $this->args['variation_clear'] ) {
						$this->add_css_property( $variation_clear, 'position', 'static' );
						$this->add_css_property( $variation_clear, 'display', 'inline-block' );
						$this->add_css_property( $variation_clear, 'right', 'initial' );
						$this->add_css_property( $variation_clear, 'top', 'initial' );

						// Offset the label cell so text vertically alignment ignores reset link.
						if ( 'floated' === $this->args['variation_layout'] ) {
							$top_margin    = empty( $this->args['clear_margin_top'] ) ? '0px' : fusion_library()->sanitize->get_value_with_unit( $this->args['clear_margin_top'] );
							$bottom_margin = empty( $this->args['clear_margin_bottom'] ) ? '0px' : fusion_library()->sanitize->get_value_with_unit( $this->args['clear_margin_bottom'] );
							$this->add_css_property( $this->base_selector . ' .variations tr:last-of-type .label', 'padding-bottom', Fusion_Sanitize::add_css_values( [ $this->extras['body_font_size'], $top_margin, $bottom_margin ] ) );
						}
					}

					// Variation clear margin.
					if ( ! $this->is_default( 'clear_margin_top' ) ) {
						$this->add_css_property( $variation_clear, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['clear_margin_top'] ) );
					}
					if ( ! $this->is_default( 'clear_margin_right' ) ) {
						$this->add_css_property( $variation_clear, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['clear_margin_right'] ) );
					}
					if ( ! $this->is_default( 'clear_margin_bottom' ) ) {
						$this->add_css_property( $variation_clear, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['clear_margin_bottom'] ) );
					}
					if ( ! $this->is_default( 'clear_margin_left' ) ) {
						$this->add_css_property( $variation_clear, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['clear_margin_left'] ) );
					}

					// Variation clear color.
					if ( ! $this->is_default( 'clear_color' ) ) {
						$this->add_css_property( $variation_clear, 'color', $this->args['clear_color'] );
					}

					// Variation clear color.
					if ( ! $this->is_default( 'clear_color_hover' ) ) {
						$this->add_css_property( $variation_clear . ':hover', 'color', $this->args['clear_color_hover'] );
					}
				} else {
					$this->add_css_property( $variation_clear, 'display', 'none', true );
				}

				// Button area alignment and spacing.
				$button_wrapper = $this->base_selector . ' .fusion-button-wrapper';

				// Button alignment.
				if ( 'stacked' === $this->args['button_layout'] ) {
					$this->add_css_property( $button_wrapper, 'flex-direction', 'column' );
					$this->add_css_property( $button_wrapper, 'align-items', $this->args['button_align'] );

					$button_wrapper_quantity = $button_wrapper . ' .quantity';
					$this->add_css_property( $button_wrapper_quantity, 'margin-bottom', '1.2em' );
					$this->add_css_property( $button_wrapper_quantity, 'margin-right', '0' );
				} elseif ( ! $this->is_default( 'button_justify' ) ) {
					$this->add_css_property( $button_wrapper, 'justify-content', $this->args['button_justify'] );

					$direction = is_rtl() ? 'left' : 'right';
					$this->add_css_property( $button_wrapper . ' .quantity', 'margin-' . $direction, '1.2em' );
				}

				// Button area margin.
				if ( ! $this->is_default( 'button_margin_top' ) ) {
					$this->add_css_property( $button_wrapper, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_top'] ) );
				}
				if ( ! $this->is_default( 'button_margin_right' ) ) {
					$this->add_css_property( $button_wrapper, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_right'] ) );
				}
				if ( ! $this->is_default( 'button_margin_bottom' ) ) {
					$this->add_css_property( $button_wrapper, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'button_margin_left' ) ) {
					$this->add_css_property( $button_wrapper, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['button_margin_left'] ) );
				}

				// Custom quantity styling if enabled.
				if ( ! $this->is_default( 'quantity_style' ) ) {
					$quantity_input   = '.fusion-body #main ' . $this->base_selector . ' .quantity input[type="number"].qty';
					$quantity_buttons = '.fusion-body #main ' . $this->base_selector . ' .quantity input[type="button"]';
					$quantity_both    = [ $quantity_input, $quantity_buttons ];

					// Quantity height.
					$height = '36px';
					if ( ! $this->is_default( 'quantity_height' ) ) {
						$height = fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_height'] );
						$this->add_css_property( $quantity_both, 'height', $height );
						$this->add_css_property( $quantity_buttons, 'width', $height );
					}

					// Quantity width.
					$width = '36px';
					if ( ! $this->is_default( 'quantity_width' ) ) {
						$width = fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_width'] );

						if ( false !== strpos( $width, '%' ) ) {
							$this->add_css_property( $quantity_input, 'width', 'calc( 100% - ' . $height . ' - ' . $height . ' )' );
						} else {
							$this->add_css_property( $quantity_input, 'width', $width );
						}
					}

					// Quantity wrapper.
					if ( ! $this->is_default( 'quantity_width' ) || ! $this->is_default( 'quantity_height' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity', 'width', 'calc( ' . $width . ' + ' . $height . ' + ' . $height . ' )' );
					}

					// Quantity border radius left side.
					if ( ! $this->is_default( 'quantity_radius_top_left' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .minus', 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_top_left'] ) );
					}
					if ( ! $this->is_default( 'quantity_radius_bottom_left' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .minus', 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_bottom_left'] ) );
					}

					// Quantity border radius right side.
					if ( ! $this->is_default( 'quantity_radius_top_right' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .plus', 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_top_right'] ) );
					}
					if ( ! $this->is_default( 'quantity_radius_bottom_left' ) ) {
						$this->add_css_property( $this->base_selector . ' .quantity .plus', 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_radius_bottom_right'] ) );
					}

					// Quantity input font size.
					if ( ! $this->is_default( 'quantity_font_size' ) ) {
						$quantity_font = [
							$quantity_input,
							$quantity_buttons,
							$this->base_selector . ' .quantity',
						];
						$this->add_css_property( $quantity_font, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_font_size'] ) );
					}

					// Quantity input text color.
					if ( ! $this->is_default( 'quantity_color' ) ) {
						$this->add_css_property( $quantity_input, 'color', $this->args['quantity_color'] );
					}

					// Quantity input background color.
					if ( ! $this->is_default( 'quantity_background' ) ) {
						$this->add_css_property( $quantity_input, 'background-color', $this->args['quantity_background'] );
					}

					// Quantity input border size.
					if ( ! $this->is_default( 'quantity_border_sizes_top' ) ) {
						$this->add_css_property( $quantity_input, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_top'] ) );
					}
					if ( ! $this->is_default( 'quantity_border_sizes_right' ) ) {
						$this->add_css_property( $quantity_input, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_right'] ) );
					}
					if ( ! $this->is_default( 'quantity_border_sizes_bottom' ) ) {
						$this->add_css_property( $quantity_input, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_bottom'] ) );
					}
					if ( ! $this->is_default( 'quantity_border_sizes_left' ) ) {
						$this->add_css_property( $quantity_input, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['quantity_border_sizes_left'] ) );
					}

					// Quantity input border color.
					if ( ! $this->is_default( 'quantity_border_color' ) ) {
						$this->add_css_property( $quantity_input, 'border-color', $this->args['quantity_border_color'] );
					}

					// Quantity buttons border size.
					if ( ! $this->is_default( 'qbutton_border_sizes_top' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_top'] ) );
					}
					if ( ! $this->is_default( 'qbutton_border_sizes_right' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_right'] ) );
					}
					if ( ! $this->is_default( 'qbutton_border_sizes_bottom' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_bottom'] ) );
					}
					if ( ! $this->is_default( 'qbutton_border_sizes_left' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['qbutton_border_sizes_left'] ) );
					}

					// Quantity button text color.
					if ( ! $this->is_default( 'qbutton_color' ) ) {
						$this->add_css_property( $quantity_buttons, 'color', $this->args['qbutton_color'] );
					}

					// Quantity button background color.
					if ( ! $this->is_default( 'qbutton_background' ) ) {
						$this->add_css_property( $quantity_buttons, 'background-color', $this->args['qbutton_background'] );
					}

					// Quantity button border color.
					if ( ! $this->is_default( 'qbutton_border_color' ) ) {
						$this->add_css_property( $quantity_buttons, 'border-color', $this->args['qbutton_border_color'] );
					}

					$hover_buttons = [
						$quantity_buttons . ':hover',
						$quantity_buttons . ':focus',
					];

					// Quantity button hover text color.
					if ( ! $this->is_default( 'qbutton_color_hover' ) ) {
						$this->add_css_property( $hover_buttons, 'color', $this->args['qbutton_color_hover'] );
					}

					// Quantity button hover background color.
					if ( ! $this->is_default( 'qbutton_background_hover' ) ) {
						$this->add_css_property( $hover_buttons, 'background-color', $this->args['qbutton_background_hover'] );
					}

					// Quantity button hover border color.
					if ( ! $this->is_default( 'qbutton_border_color_hover' ) ) {
						$this->add_css_property( $hover_buttons, 'border-color', $this->args['qbutton_border_color_hover'] );
					}
				}

				// Custom add to cart button styling.
				if ( ! $this->is_default( 'button_style' ) ) {

					$button = '.fusion-body ' . $this->base_selector . ' .fusion-button-wrapper .button';

					// Button size.
					if ( ! $this->is_default( 'button_size' ) ) {

						$button_size_map = [
							'small'  => [
								'padding'     => '9px 20px',
								'line_height' => '14px',
								'font_size'   => '12px',
							],
							'medium' => [
								'padding'     => '11px 23px',
								'line_height' => '16px',
								'font_size'   => '13px',
							],
							'large'  => [
								'padding'     => '13px 29px',
								'line_height' => '17px',
								'font_size'   => '14px',
							],
							'xlarge' => [
								'padding'     => '17px 40px',
								'line_height' => '21px',
								'font_size'   => '18px',
							],
						];

						if ( isset( $button_size_map[ $this->args['button_size'] ] ) ) {
							$button_dimensions = $button_size_map[ $this->args['button_size'] ];
							$this->add_css_property( $button, 'padding', $button_dimensions['padding'] );
							$this->add_css_property( $button, 'line-height', $button_dimensions['line_height'] );
							$this->add_css_property( $button, 'font-size', $button_dimensions['font_size'] );
						}
					}

					// Button stretch.
					if ( ! $this->is_default( 'button_stretch' ) ) {
						$this->add_css_property( $button, 'flex', '1' );
						$this->add_css_property( $button, 'width', '100%' );
					}

					// Button border width.
					if ( ! $this->is_default( 'button_border_top' ) ) {
						$this->add_css_property( $button, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_top'] ) );
					}
					if ( ! $this->is_default( 'button_border_right' ) ) {
						$this->add_css_property( $button, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_right'] ) );
					}
					if ( ! $this->is_default( 'button_border_bottom' ) ) {
						$this->add_css_property( $button, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_bottom'] ) );
					}
					if ( ! $this->is_default( 'button_border_left' ) ) {
						$this->add_css_property( $button, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_left'] ) );
					}

					// Button text color.
					if ( ! $this->is_default( 'button_color' ) ) {
						$this->add_css_property( $button, 'color', $this->args['button_color'] );
					}

					// Button gradient.
					if ( ( isset( $this->params['button_gradient_top'] ) && '' !== $this->params['button_gradient_top'] ) || ( isset( $this->params['button_gradient_bottom'] ) && '' !== $this->params['button_gradient_bottom'] ) ) {
						$this->add_css_property( $button, 'background', $this->args['button_gradient_top'] );
						$this->add_css_property( $button, 'background-image', 'linear-gradient( to top, ' . $this->args['button_gradient_bottom'] . ', ' . $this->args['button_gradient_top'] . ' )' );
					}

					// Button border color.
					if ( ! $this->is_default( 'button_border_color' ) ) {
						$this->add_css_property( $button, 'border-color', $this->args['button_border_color'] );
					}

					$button_hover = $button . ':hover';

					// Button hover text color.
					if ( ! $this->is_default( 'button_color_hover' ) ) {
						$this->add_css_property( $button_hover, 'color', $this->args['button_color_hover'] );
					}

					// Button gradient.
					if ( ( isset( $this->params['button_gradient_top_hover'] ) && '' !== $this->params['button_gradient_top_hover'] ) || ( isset( $this->params['button_gradient_bottom_hover'] ) && '' !== $this->params['button_gradient_bottom_hover'] ) ) {
						$this->add_css_property( $button_hover, 'background', $this->args['button_gradient_top_hover'] );
						$this->add_css_property( $button_hover, 'background-image', 'linear-gradient( to top, ' . $this->args['button_gradient_bottom_hover'] . ', ' . $this->args['button_gradient_top_hover'] . ' )' );
					}

					// Button border color.
					if ( ! $this->is_default( 'button_border_color_hover' ) ) {
						$this->add_css_property( $button_hover, 'border-color', $this->args['button_border_color_hover'] );
					}
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
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
					'class'       => 'fusion-woo-cart fusion-woo-cart-' . $this->counter,
					'style'       => '',
					'data-type'   => esc_attr( $this->product->get_type() ),
					'data-layout' => $this->args['variation_layout'],
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( '' !== $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( 'no' === $this->args['show_price'] ) {
					$attr['class'] .= ' hide-price';
				}

				if ( 'no' === $this->args['show_stock'] ) {
					$attr['class'] .= ' hide-stock';
				}
				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function icon_attr() {

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['button_icon'] ),
					'aria-hidden' => 'true',
				];

				$attr['class'] .= ' button-icon-' . $this->args['icon_position'];

				return $attr;

			}

			/**
			 * Change clear variaion link content.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $html The link markup.
			 * @return string
			 */
			public function clear_link( $html = '' ) {
				if ( 'text' === $this->args['clear_content'] ) {
					return '<a class="reset_variations" href="#">' . esc_html( $this->args['clear_text'] ) . '</a>';
				} elseif ( 'icon' === $this->args['clear_content'] ) {
					$icon_class = fusion_font_awesome_name_handler( $this->args['clear_icon'] );
					return '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear', 'woocommerce' ) . '"><i aria-hidden="true" class="' . $icon_class . '"></i></a>';
				}
				return $html;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-cart.min.css' );
			}

			/**
			 * Add third party hooks to our awb_after_woo_add_to_cart_content action.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_third_party_hooks() {

				// Change action for the WooCommerce Paypal Payments plugin.
				if ( class_exists( 'WooCommerce\PayPalCommerce\Button\Assets\SmartButton' ) ) {
					add_filter(
						'woocommerce_paypal_payments_single_product_renderer_hook',
						function() {
							return 'awb_woocommerce_paypal_payments_single_product_renderer_hook';
						}
					);
					add_action( 'wp', [ $this, 'catch_woocommerce_paypal_payments_single_product' ], 20 );
				}

				if ( class_exists( 'Woocommerce_Catalog_Enquiry' ) && ! is_admin() && ! fusion_doing_ajax() ) {
					add_action( 'wp', [ $this, 'change_woocommerce_catalog_enquiry_action' ] );
				}

				if ( class_exists( 'YITH_YWGC_Frontend' ) ) {
					add_action( 'awb_after_woo_add_to_cart_content', [ $this, 'change_yith_ywgc_action' ] );
				}
			}

			/**
			 * Catches the PayPal button render from WooCommerce Paypal Payments plugin and moves it to correct hook.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function catch_woocommerce_paypal_payments_single_product() {
				ob_start();
				do_action( 'awb_woocommerce_paypal_payments_single_product_renderer_hook' );
				$paypal_button = ob_get_clean();

				$action_name    = 'woocommerce_single_product_summary';
				$layout_product = function_exists( 'Fusion_Builder_WooCommerce' ) && Fusion_Builder_WooCommerce()->is_layout_product();

				if ( $layout_product ) {
					$action_name = 'awb_after_woo_add_to_cart_content';
				}

				add_action(
					$action_name,
					function() use ( $paypal_button ) {
						echo $paypal_button; // phpcs:ignore WordPress.Security.EscapeOutput
					}
				);
			}


			/**
			 * Add Woocommerce Catalog Enquiry plugin hooks to our awb_after_woo_add_to_cart_content action.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function change_woocommerce_catalog_enquiry_action() {
				global $wp_filter;

				// phpcs:disable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
				if ( class_exists( 'Woocommerce_Catalog_Enquiry_Pro' ) ) {
					global $Woocommerce_Catalog_Enquiry_Pro;
					$Woocommerce_Catalog_Enquiry_Pro->frontend->catalog_woocommerce_template_single();
				} else {
					global $Woocommerce_Catalog_Enquiry;
					$Woocommerce_Catalog_Enquiry->frontend->catalog_woocommerce_template_single();
				}
				// phpcs:enable WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase

				if ( isset( $wp_filter['woocommerce_single_product_summary'] ) ) {
					foreach ( $wp_filter['woocommerce_single_product_summary'] as $index => $actions ) {
						foreach ( $actions as $name => $action ) {
							if ( false !== strpos( $name, 'add_form_for_enquiry' ) || false !== strpos( $name, 'add_form_for_enquiry_without_popup' ) ) {
								add_action( 'awb_before_woo_add_to_cart_content', $action['function'] );
							}
						}
					}
				}
			}

			/**
			 * Add YITH Gift Cards plugin hooks to our awb_after_woo_add_to_cart_content action.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function change_yith_ywgc_action() {
				$yith_gift_cards_class = YITH_YWGC_Frontend::get_instance();
				ob_start();
				$yith_gift_cards_class->show_gift_card_product_template();
				$gift_card = ob_get_clean();
				$gift_card = str_replace( 'gift_card_template_button', 'gift_card_template_button fusion-button-wrapper', $gift_card );

				echo $gift_card; // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}

	new FusionTB_Woo_Cart();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_cart() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Cart',
			[
				'name'         => esc_attr__( 'Woo Add To Cart', 'fusion-builder' ),
				'shortcode'    => 'fusion_tb_woo_cart',
				'icon'         => 'fusiona-woo-add-to-cart',
				'component'    => true,
				'templates'    => [ 'content' ],
				'subparam_map' => [
					'margin_top'                           => 'margin',
					'margin_right'                         => 'margin',
					'margin_bottom'                        => 'margin',
					'margin_left'                          => 'margin',
					'border_sizes_top'                     => 'border_sizes',
					'border_sizes_right'                   => 'border_sizes',
					'border_sizes_bottom'                  => 'border_sizes',
					'border_sizes_left'                    => 'border_sizes',
					'cell_padding_top'                     => 'padding_dimensions',
					'cell_padding_right'                   => 'padding_dimensions',
					'cell_padding_bottom'                  => 'padding_dimensions',
					'cell_padding_left'                    => 'padding_dimensions',
					'label_area_width'                     => 'label_width',
					'select_height'                        => 'field_height',
					'select_border_sizes_top'              => 'select_border_sizes',
					'select_border_sizes_right'            => 'select_border_sizes',
					'select_border_sizes_bottom'           => 'select_border_sizes',
					'select_border_sizes_left'             => 'select_border_sizes',
					'border_radius_top_left'               => 'border_radius',
					'border_radius_top_right'              => 'border_radius',
					'border_radius_bottom_right'           => 'border_radius',
					'border_radius_bottom_left'            => 'border_radius',
					'clear_margin_top'                     => 'clear_margin_dimensions',
					'clear_margin_right'                   => 'clear_margin_dimensions',
					'clear_margin_bottom'                  => 'clear_margin_dimensions',
					'clear_margin_left'                    => 'clear_margin_dimensions',
					'info_padding_top'                     => 'info_padding_dimensions',
					'info_padding_right'                   => 'info_padding_dimensions',
					'info_padding_bottom'                  => 'info_padding_dimensions',
					'info_padding_left'                    => 'info_padding_dimensions',
					'info_border_sizes_top'                => 'info_border_sizes',
					'info_border_sizes_right'              => 'info_border_sizes',
					'info_border_sizes_bottom'             => 'info_border_sizes',
					'info_border_sizes_left'               => 'info_border_sizes',
					'info_border_radius_top_left'          => 'info_border_radius',
					'info_border_radius_top_right'         => 'info_border_radius',
					'info_border_radius_bottom_right'      => 'info_border_radius',
					'info_border_radius_bottom_left'       => 'info_border_radius',
					'button_margin_top'                    => 'button_margin',
					'button_margin_right'                  => 'button_margin',
					'button_margin_bottom'                 => 'button_margin',
					'button_margin_left'                   => 'button_margin',
					'quantity_width'                       => 'quantity_height_field',
					'quantity_height'                      => 'quantity_height_field',
					'quantity_radius_top_left'             => 'quantity_border_radius',
					'quantity_radius_top_right'            => 'quantity_border_radius',
					'quantity_radius_bottom_right'         => 'quantity_border_radius',
					'quantity_radius_bottom_left'          => 'quantity_border_radius',
					'quantity_border_sizes_top'            => 'quantity_border_sizes',
					'quantity_border_sizes_right'          => 'quantity_border_sizes',
					'quantity_border_sizes_bottom'         => 'quantity_border_sizes',
					'quantity_border_sizes_left'           => 'quantity_border_sizes',
					'qbutton_border_sizes_top'             => 'qbutton_border_sizes',
					'qbutton_border_sizes_right'           => 'qbutton_border_sizes',
					'qbutton_border_sizes_bottom'          => 'qbutton_border_sizes',
					'qbutton_border_sizes_left'            => 'qbutton_border_sizes',
					'fusion_font_family_label_typography'  => 'label_fonts',
					'fusion_font_variant_label_typography' => 'label_fonts',
					'label_font_size'                      => 'label_fonts',
					'label_text_transform'                 => 'label_fonts',
					'label_line_height'                    => 'label_fonts',
					'label_letter_spacing'                 => 'label_fonts',
					'label_color'                          => 'label_fonts',
					'fusion_font_family_price_typography'  => 'price_fonts',
					'fusion_font_variant_price_typography' => 'price_fonts',
					'price_font_size'                      => 'price_fonts',
					'price_text_transform'                 => 'price_fonts',
					'price_line_height'                    => 'price_fonts',
					'price_letter_spacing'                 => 'price_fonts',
					'price_color'                          => 'price_fonts',
					'fusion_font_family_sale_typography'   => 'sale_fonts',
					'fusion_font_variant_sale_typography'  => 'sale_fonts',
					'sale_font_size'                       => 'sale_fonts',
					'sale_text_transform'                  => 'sale_fonts',
					'sale_line_height'                     => 'sale_fonts',
					'sale_letter_spacing'                  => 'sale_fonts',
					'sale_color'                           => 'sale_fonts',
					'fusion_font_family_stock_typography'  => 'stock_fonts',
					'fusion_font_variant_stock_typography' => 'stock_fonts',
					'stock_font_size'                      => 'stock_fonts',
					'stock_text_transform'                 => 'stock_fonts',
					'stock_line_height'                    => 'stock_fonts',
					'stock_letter_spacing'                 => 'stock_fonts',
					'stock_color'                          => 'stock_fonts',
					'description_font_size'                => 'description_fonts',
					'description_text_transform'           => 'description_fonts',
					'description_line_height'              => 'description_fonts',
					'description_letter_spacing'           => 'description_fonts',
					'description_color'                    => 'description_fonts',
					'fusion_font_family_description_typography' => 'description_fonts',
					'fusion_font_variant_description_typography' => 'description_fonts',
				],
				'params'       => [
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
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Variations Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Cell Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the variation table cells.', 'fusion-builder' ),
						'param_name'  => 'border_sizes',
						'value'       => [
							'border_sizes_top'    => '',
							'border_sizes_right'  => '',
							'border_sizes_bottom' => '',
							'border_sizes_left'   => '',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Cell Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the variation table cells', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Cell Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of the variation table cells.  Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding_dimensions',
						'value'            => [
							'cell_padding_top'    => '',
							'cell_padding_right'  => '',
							'cell_padding_bottom' => '',
							'cell_padding_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Cell Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the variation table cells.', 'fusion-builder' ),
						'param_name'  => 'cell_background',
						'value'       => '',
						'default'     => 'rgba(255,255,255,0)',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Variation Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout for the variations.  Floated will have the label and select side by side.  Stacked will have one per row.', 'fusion-builder' ),
						'param_name'  => 'variation_layout',
						'default'     => 'floated',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'value'       => [
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Label Width', 'fusion-builder' ),
						'description'      => esc_html__( 'Leave empty for automatic width.  Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'label_width',
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'label_area_width' => '',
						],
						'dependency'       => [
							[
								'element'  => 'variation_layout',
								'value'    => 'floated',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the text alignment for the variation label and variation swatches.', 'fusion-builder' ),
						'param_name'  => 'text_align',
						'default'     => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Label Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the label text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'label_fonts',
						'choices'          => [
							'font-family'    => 'label_typography',
							'font-size'      => 'label_font_size',
							'text-transform' => 'label_text_transform',
							'line-height'    => 'label_line_height',
							'letter-spacing' => 'label_letter_spacing',
							'color'          => 'label_color',
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
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Select Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the select fields for variations.', 'fusion-builder' ),
						'param_name'  => 'select_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Select Height', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'field_height',
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'value'            => [
							'select_height' => '',
						],
						'dependency'       => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Select Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the select field. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'select_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Select Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'select_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_text_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Select Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'select_background',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_bg_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Select Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the select fields.', 'fusion-builder' ),
						'param_name'  => 'select_border_sizes',
						'value'       => [
							'select_border_sizes_top'    => '',
							'select_border_sizes_right'  => '',
							'select_border_sizes_bottom' => '',
							'select_border_sizes_left'   => '',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'dependency'  => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Select Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'select_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_border_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Select Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'select_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Swatch Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the Avada variation swatches.', 'fusion-builder' ),
						'param_name'  => 'swatch_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Swatch Item Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'swatch_margin',
						'value'            => [
							'swatch_margin_top'    => '',
							'swatch_margin_right'  => '',
							'swatch_margin_bottom' => '',
							'swatch_margin_left'   => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Swatch Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the color, image and button swatch fields.', 'fusion-builder' ),
						'param_name'  => 'swatch_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_bg_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Swatch Active Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the color, image and button swatch fields when active.', 'fusion-builder' ),
						'param_name'  => 'swatch_background_color_active',
						'value'       => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Swatch Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the color, image and button swatch fields.', 'fusion-builder' ),
						'param_name'  => 'swatch_border_sizes',
						'value'       => [
							'swatch_border_sizes_top'    => '',
							'swatch_border_sizes_right'  => '',
							'swatch_border_sizes_bottom' => '',
							'swatch_border_sizes_left'   => '',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Swatch Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the color, image and button swatch fields.', 'fusion-builder' ),
						'param_name'  => 'swatch_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_border_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Swatch Active Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the color, image and button swatch fields when active.', 'fusion-builder' ),
						'param_name'  => 'swatch_border_color_active',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_focus_border_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Color Swatch Dimensions', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'color_swatch_dimensions',
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'value'            => [
							'color_swatch_width'  => '',
							'color_swatch_height' => '',
						],
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Color Swatch Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of the color swatches.  Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'color_swatch_padding',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'color_swatch_padding_top'    => '',
							'color_swatch_padding_right'  => '',
							'color_swatch_padding_bottom' => '',
							'color_swatch_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Color Swatch Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'color_swatch_border_radius',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'color_swatch_border_radius_top_left'  => '',
							'color_swatch_border_radius_top_right' => '',
							'color_swatch_border_radius_bottom_right' => '',
							'color_swatch_border_radius_bottom_left' => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Image Swatch Dimensions', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'image_swatch_dimensions',
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'value'            => [
							'image_swatch_height' => '',
							'image_swatch_width'  => '',
						],
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Image Swatch Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of the image swatches.  Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'image_swatch_padding',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'image_swatch_padding_top'    => '',
							'image_swatch_padding_right'  => '',
							'image_swatch_padding_bottom' => '',
							'image_swatch_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Image Swatch Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'image_swatch_border_radius',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'image_swatch_border_radius_top_left'  => '',
							'image_swatch_border_radius_top_right' => '',
							'image_swatch_border_radius_bottom_right' => '',
							'image_swatch_border_radius_bottom_left' => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Button Swatch Dimensions', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.  Leave empty for auto.', 'fusion-builder' ),
						'param_name'       => 'button_swatch_dimensions',
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'value'            => [
							'button_swatch_width'  => '',
							'button_swatch_height' => '',
						],
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Swatch Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of the button swatches.  Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'button_swatch_padding',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'button_swatch_padding_top'    => '',
							'button_swatch_padding_right'  => '',
							'button_swatch_padding_bottom' => '',
							'button_swatch_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Button Swatch Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'button_swatch_border_radius',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'button_swatch_border_radius_top_left'  => '',
							'button_swatch_border_radius_top_right' => '',
							'button_swatch_border_radius_bottom_right' => '',
							'button_swatch_border_radius_bottom_left' => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Button Swatch Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the button swatches. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'button_swatch_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Swatch Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button swatches.', 'fusion-builder' ),
						'param_name'  => 'button_swatch_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Swatch Active Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button swatches when active.', 'fusion-builder' ),
						'param_name'  => 'button_swatch_color_active',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'swatch_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Variation Clear', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls how you want to disable the variation clear link.', 'fusion-builder' ),
						'param_name'  => 'variation_clear',
						'value'       => [
							'absolute' => esc_attr__( 'Absolute', 'fusion-builder' ),
							'inline'   => esc_attr__( 'Inline', 'fusion-builder' ),
							'hide'     => esc_attr__( 'Hide', 'fusion-builder' ),
						],
						'default'     => 'hide',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_cart',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Clear Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the content type for the clear link.  Default will use WooCommerce text string.', 'fusion-builder' ),
						'param_name'  => 'clear_content',
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'text' => esc_attr__( 'Text', 'fusion-builder' ),
							'icon' => esc_attr__( 'Icon', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'variation_clear',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_cart',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Clear Icon', 'fusion-builder' ),
						'param_name'  => 'clear_icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'variation_clear',
								'value'    => 'hide',
								'operator' => '!=',
							],
							[
								'element'  => 'clear_content',
								'value'    => 'icon',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_cart',
							'ajax'     => true,
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Clear Text', 'fusion-builder' ),
						'param_name'   => 'clear_text',
						'value'        => '',
						'description'  => esc_attr__( 'Custom text to use for the variation clear link.', 'fusion-builder' ),
						'dynamic_data' => true,
						'group'        => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'   => [
							[
								'element'  => 'variation_clear',
								'value'    => 'hide',
								'operator' => '!=',
							],
							[
								'element'  => 'clear_content',
								'value'    => 'text',
								'operator' => '==',
							],
						],
						'callback'     => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_cart',
							'ajax'     => true,
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Clear Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the margin of the clear link.  Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'clear_margin_dimensions',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'clear_margin_top'    => '',
							'clear_margin_right'  => '',
							'clear_margin_bottom' => '',
							'clear_margin_left'   => '',
						],
						'group'            => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'variation_clear',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Clear Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the clear link.', 'fusion-builder' ),
						'param_name'  => 'clear_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'variation_clear',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Clear Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the clear link on hover.', 'fusion-builder' ),
						'param_name'  => 'clear_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'group'       => esc_attr__( 'Variations', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'variation_clear',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Details Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding of the variation details area.  Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'info_padding_dimensions',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'info_padding_top'    => '',
							'info_padding_right'  => '',
							'info_padding_bottom' => '',
							'info_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Details', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Details Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the variation details area.', 'fusion-builder' ),
						'param_name'  => 'info_background',
						'value'       => '',
						'default'     => 'rgba(255,255,255,0)',
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Details Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the variation details area.', 'fusion-builder' ),
						'param_name'  => 'info_border_sizes',
						'value'       => [
							'info_border_sizes_top'    => '',
							'info_border_sizes_right'  => '',
							'info_border_sizes_bottom' => '',
							'info_border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Details Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the variation details area.', 'fusion-builder' ),
						'param_name'  => 'info_border_color',
						'value'       => '',
						'default'     => 'rgba(255,255,255,0)',
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Details Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'info_border_radius',
						'group'            => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'info_border_radius_top_left'  => '',
							'info_border_radius_top_right' => '',
							'info_border_radius_bottom_right' => '',
							'info_border_radius_bottom_left' => '',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Details Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment within the details area.', 'fusion-builder' ),
						'param_name'  => 'info_align',
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
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( ' Description Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the description text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'description_fonts',
						'choices'          => [
							'font-family'    => 'description_typography',
							'font-size'      => 'description_font_size',
							'text-transform' => 'description_text_transform',
							'line-height'    => 'description_line_height',
							'letter-spacing' => 'description_letter_spacing',
							'color'          => 'description_color',
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
						'group'            => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Description Order', 'fusion-builder' ),
						'description' => esc_attr__( 'Display order for description.  Can be before price/stock or after..', 'fusion-builder' ),
						'param_name'  => 'description_order',
						'value'       => [
							'before' => esc_attr__( 'Before Price', 'fusion-builder' ),
							'after'  => esc_attr__( 'After Price', 'fusion-builder' ),
						],
						'default'     => 'before',
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Price', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide the variation price. "Always" will display the price even if all variations share the same. "Auto" will only display the price when variations have different prices.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'param_name'  => 'show_price',
						'default'     => 'yes',
						'value'       => [
							'always' => esc_attr__( 'Always', 'fusion-builder' ),
							'yes'    => esc_attr__( 'Auto', 'fusion-builder' ),
							'no'     => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_cart_hide',
							'args'     => [
								'selector' => 'hide-price',
							],
						],
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
						'group'            => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_price',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Sale Old Price', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide sale old price.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'param_name'  => 'show_sale',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'show_price',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Sale Old Price Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if the sale old price should be before or after the regular price.', 'fusion-builder' ),
						'param_name'  => 'sale_order',
						'default'     => 'after',
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'value'       => [
							'before' => esc_attr__( 'Before Regular', 'fusion-builder' ),
							'after'  => esc_attr__( 'After Regular', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'show_price',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'show_sale',
								'value'    => 'no',
								'operator' => '!=',
							],
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
						'group'            => esc_attr__( 'Details', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_price',
								'value'    => 'no',
								'operator' => '!=',
							],
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
						'description' => esc_attr__( 'Make a selection to show or hide the variation stock.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Details', 'fusion-builder' ),
						'param_name'  => 'show_stock',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_cart_hide',
							'args'     => [
								'selector' => 'hide-stock',
							],
						],
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
						'group'            => esc_attr__( 'Details', 'fusion-builder' ),
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
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Cart Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'button_margin',
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
						'value'            => [
							'button_margin_top'    => '',
							'button_margin_right'  => '',
							'button_margin_bottom' => '',
							'button_margin_left'   => '',
						],
						'group'            => esc_attr__( 'Cart', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout for the quantity and add to cart button.  Floated will have them side by side.  Stacked will have one per row.', 'fusion-builder' ),
						'param_name'  => 'button_layout',
						'default'     => 'floated',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'value'       => [
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'button_justify',
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
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'button_layout',
								'value'    => 'floated',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'button_align',
						'default'     => 'flex-start',
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
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'button_layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Quantity Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the quantity field.', 'fusion-builder' ),
						'param_name'  => 'quantity_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Quantity Input Dimensions', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'quantity_height_field',
						'group'            => esc_attr__( 'Cart', 'fusion-builder' ),
						'value'            => [
							'quantity_width'  => '',
							'quantity_height' => '',
						],
						'dependency'       => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Quantity Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'quantity_border_radius',
						'group'            => esc_attr__( 'Cart', 'fusion-builder' ),
						'value'            => [
							'quantity_radius_top_left'     => '',
							'quantity_radius_top_right'    => '',
							'quantity_radius_bottom_right' => '',
							'quantity_radius_bottom_left'  => '',
						],
						'dependency'       => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Quantity Input Font Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the font size of the select field. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'quantity_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Input Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Input Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_background',
						'value'       => '',
						'default'     => 'rgba(255,255,255,0)',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Quantity Input Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_border_sizes',
						'value'       => [
							'quantity_border_sizes_top'    => '',
							'quantity_border_sizes_right'  => '',
							'quantity_border_sizes_bottom' => '',
							'quantity_border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Input Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'quantity_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'sep_color' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Quantity Button Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_border_sizes',
						'value'       => [
							'qbutton_border_sizes_top'    => '',
							'qbutton_border_sizes_right'  => '',
							'qbutton_border_sizes_bottom' => '',
							'qbutton_border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Quantity Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'quantity_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Cart', 'fusion-builder' ),
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
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_color',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_background',
						'value'       => '',
						'default'     => $fusion_settings->get( 'qty_bg_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_background_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'qty_bg_hover_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Quantity Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the select fields.', 'fusion-builder' ),
						'param_name'  => 'qbutton_border_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'quantity_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'quantity_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Add To Cart Button Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the add to cart button.', 'fusion-builder' ),
						'param_name'  => 'button_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'button_size',
						'default'     => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the button spans the full width/remaining width of row.', 'fusion-builder' ),
						'param_name'  => 'button_stretch',
						'default'     => 'no',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'button_border_width',
						'value'            => [
							'button_border_top'    => '',
							'button_border_right'  => '',
							'button_border_bottom' => '',
							'button_border_left'   => '',
						],
						'group'            => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'button_icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_cart',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the icon on the button.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_cart',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
							[
								'element'  => 'button_icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'button_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Cart', 'fusion-builder' ),
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
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'button_border_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Cart', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'subgroup'    => [
							'name' => 'button_styling',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'button_style',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-cart-tb',
					],
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_cart',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_cart' );
