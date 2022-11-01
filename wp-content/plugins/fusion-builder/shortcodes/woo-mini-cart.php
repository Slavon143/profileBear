<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.8
 */

if ( fusion_is_element_enabled( 'fusion_woo_mini_cart' ) ) {

	if ( ! class_exists( 'Fusion_Woo_Mini_Cart' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.8
		 */
		class Fusion_Woo_Mini_Cart extends Fusion_Element {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.8
			 * @var array
			 */
			protected $defaults;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.8
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.8
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.8
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_fusion_woo_mini_cart-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_woo_mini_cart', [ $this, 'ajax_render' ] );

				add_shortcode( 'fusion_woo_mini_cart', [ $this, 'render' ] );
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
				$fusion_settings = awb_get_fusion_settings();
				return [
					'images_max_width'                    => '',
					'show_buttons'                        => 'yes',
					'buttons_stretch'                     => 'no',
					'buttons_layout'                      => 'floated',
					'buttons_alignment'                   => 'center',
					'buttons_justify'                     => 'center',
					'show_remove_icon'                    => 'yes',
					'remove_icon_color'                   => '',
					'remove_icon_bg_color'                => '',
					'remove_icon_hover_color'             => '',
					'remove_icon_hover_bg_color'          => '',
					'separator_color'                     => $fusion_settings->get( 'sep_color' ),
					'fusion_font_family_product_title_font' => '',
					'fusion_font_variant_product_title_font' => '',
					'product_title_font_size'             => '',
					'product_title_text_transform'        => '',
					'product_title_line_height'           => '',
					'product_title_letter_spacing'        => '',
					'product_title_color'                 => $fusion_settings->get( 'link_color' ),
					'product_title_hover_color'           => $fusion_settings->get( 'primary_color' ),
					'fusion_font_family_product_price_font' => '',
					'fusion_font_variant_product_price_font' => '',
					'product_price_font_size'             => '',
					'product_price_line_height'           => '',
					'product_price_letter_spacing'        => '',
					'product_price_color'                 => $fusion_settings->get( 'body_typography', 'color' ),
					'show_subtotal'                       => 'yes',
					'subtotal_alignment'                  => '',
					'fusion_font_family_subtotal_text_font' => '',
					'fusion_font_variant_subtotal_text_font' => '',
					'subtotal_text_font_size'             => '',
					'subtotal_text_line_height'           => '',
					'subtotal_text_letter_spacing'        => '',
					'subtotal_text_color'                 => $fusion_settings->get( 'body_typography', 'color' ),
					'fusion_font_family_subtotal_amount_font' => '',
					'fusion_font_variant_subtotal_amount_font' => '',
					'subtotal_amount_font_size'           => '',
					'subtotal_amount_line_height'         => '',
					'subtotal_amount_letter_spacing'      => '',
					'subtotal_amount_color'               => '',
					'icon_position'                       => 'left',
					'links_margin_top'                    => '',
					'links_margin_right'                  => '',
					'links_margin_bottom'                 => '',
					'links_margin_left'                   => '',
					'link_style'                          => 'link',
					'fusion_font_family_view_cart_font'   => '',
					'fusion_font_variant_view_cart_font'  => '',
					'view_cart_font_size'                 => '',
					'view_cart_text_transform'            => '',
					'view_cart_line_height'               => '',
					'view_cart_letter_spacing'            => '',
					'view_cart_link_color'                => $fusion_settings->get( 'link_color' ),
					'view_cart_link_hover_color'          => $fusion_settings->get( 'primary_color' ),
					'view_cart_button_size'               => '',
					'view_cart_button_border_top'         => '',
					'view_cart_button_border_right'       => '',
					'view_cart_button_border_bottom'      => '',
					'view_cart_button_border_left'        => '',
					'view_cart_button_color'              => $fusion_settings->get( 'button_accent_color' ),
					'view_cart_button_gradient_top'       => $fusion_settings->get( 'button_gradient_top_color' ),
					'view_cart_button_gradient_bottom'    => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'view_cart_button_border_color'       => $fusion_settings->get( 'button_border_color' ),
					'view_cart_button_color_hover'        => $fusion_settings->get( 'button_accent_hover_color' ),
					'view_cart_button_gradient_top_hover' => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'view_cart_button_gradient_bottom_hover' => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'view_cart_button_border_color_hover' => $fusion_settings->get( 'button_border_hover_color' ),
					'fusion_font_family_checkout_font'    => '',
					'fusion_font_variant_checkout_font'   => '',
					'checkout_font_size'                  => '',
					'checkout_text_transform'             => '',
					'checkout_line_height'                => '',
					'checkout_letter_spacing'             => '',
					'checkout_link_color'                 => $fusion_settings->get( 'link_color' ),
					'checkout_link_hover_color'           => $fusion_settings->get( 'primary_color' ),
					'checkout_button_size'                => '',
					'checkout_button_border_top'          => '',
					'checkout_button_border_right'        => '',
					'checkout_button_border_bottom'       => '',
					'checkout_button_border_left'         => '',
					'checkout_button_color'               => $fusion_settings->get( 'button_accent_color' ),
					'checkout_button_gradient_top'        => $fusion_settings->get( 'button_gradient_top_color' ),
					'checkout_button_gradient_bottom'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'checkout_button_border_color'        => $fusion_settings->get( 'button_border_color' ),
					'checkout_button_color_hover'         => $fusion_settings->get( 'button_accent_hover_color' ),
					'checkout_button_gradient_top_hover'  => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'checkout_button_gradient_bottom_hover' => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'checkout_button_border_color_hover'  => $fusion_settings->get( 'button_border_hover_color' ),
					'margin_bottom'                       => '',
					'margin_left'                         => '',
					'margin_right'                        => '',
					'margin_top'                          => '',
					'hide_on_mobile'                      => fusion_builder_default_visibility( 'string' ),
					'class'                               => '',
					'id'                                  => '',
					'animation_type'                      => '',
					'animation_direction'                 => 'down',
					'animation_speed'                     => '0.1',
					'animation_offset'                    => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.8
			 * @param array $defaults An array of defaults.
			 * @return void
			 */
			public function ajax_render( $defaults ) {
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$return_data = [];
				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$args           = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$post_id        = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					$this->defaults = self::get_element_defaults();
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_mini_cart' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_mini_cart'] = $this->get_woo_mini_cart();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.8
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_woo_mini_cart' );

				$html = '<div ' . FusionBuilder::attributes( 'fusion_woo_mini_cart-shortcode' ) . '>' . $this->get_woo_mini_cart() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_woo_mini_cart_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Mini Cart element.
			 *
			 * @static
			 * @access public
			 * @since 3.8
			 * @return string
			 */
			public function get_woo_mini_cart() {
				$content = '';

				if ( ! is_object( WC()->cart ) ) {
					return $content;
				}

				ob_start();
				woocommerce_mini_cart();
				$content = '<div class="widget_shopping_cart_content">' . ob_get_clean() . '</div>';

				return apply_filters( 'fusion_woo_component_content', $content, 'fusion_woo_mini_cart', $this->args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.8
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'awb-woo-mini-cart awb-woo-mini-cart-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( 'no' !== $this->args['show_buttons'] ) {
					if ( '' !== $this->args['icon_position'] ) {
						$attr['class'] .= ' icon-position-' . $this->args['icon_position'];
					}

					if ( '' !== $this->args['buttons_layout'] ) {
						$attr['class'] .= ' layout-' . $this->args['buttons_layout'];
					}

					if ( '' !== $this->args['buttons_stretch'] ) {
						$attr['class'] .= ' button-span-' . $this->args['buttons_stretch'];
					}

					if ( '' !== $this->args['link_style'] ) {
						$attr['class'] .= ' link-style-' . $this->args['link_style'];
					}

					if ( 'button' === $this->args['link_style'] ) {
						$attr['class'] .= '' !== $this->args['view_cart_button_size'] ? ' view-cart-button-size-' . $this->args['view_cart_button_size'] : '';
						$attr['class'] .= '' !== $this->args['checkout_button_size'] ? ' checkout-button-size-' . $this->args['checkout_button_size'] : '';
					}
				} else {
					$attr['class'] .= ' hide-buttons';
				}

				if ( 'yes' !== $this->args['show_subtotal'] ) {
					$attr['class'] .= ' hide-subtotal';
				}

				if ( 'yes' !== $this->args['show_remove_icon'] ) {
					$attr['class'] .= ' hide-remove-icon';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['style'] .= $this->get_style_variables();

				return $attr;
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.8
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-mini-cart.min.css' );
			}

			/**
			 * Get the style variables.
			 *
			 * @access protected
			 * @since 3.8
			 * @return string
			 */
			protected function get_style_variables() {
				$styles = '';

				if ( ! $this->is_default( 'images_max_width' ) ) {
					$styles .= '--awb-image-max-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['images_max_width'] ) . ';';
				}

				if ( ! $this->is_default( 'remove_icon_color' ) ) {
					$styles .= '--awb-remove-icon-color:' . fusion_library()->sanitize->color( $this->args['remove_icon_color'] ) . ';';
				}

				if ( ! $this->is_default( 'remove_icon_bg_color' ) ) {
					$styles .= '--awb-remove-icon-bg-color:' . fusion_library()->sanitize->color( $this->args['remove_icon_bg_color'] ) . ';';
				}

				// Remove icon hover styles.
				if ( ! $this->is_default( 'remove_icon_hover_color' ) ) {
					$styles .= '--awb-remove-icon-hover-color:' . fusion_library()->sanitize->color( $this->args['remove_icon_hover_color'] ) . ';';
				}

				if ( ! $this->is_default( 'remove_icon_hover_bg_color' ) ) {
					$styles .= '--awb-remove-icon-hover-bg-color:' . fusion_library()->sanitize->color( $this->args['remove_icon_hover_bg_color'] ) . ';';
				}

				if ( ! $this->is_default( 'separator_color' ) ) {
					$styles .= '--awb-separator-color:' . fusion_library()->sanitize->color( $this->args['separator_color'] ) . ';';
				}

				// Product title styles.
				$title_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'product_title_font', 'array' );

				foreach ( $title_styles as $rule => $value ) {
					$styles .= '--awb-product-title-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
				}

				if ( ! $this->is_default( 'product_title_font_size' ) ) {
					$styles .= '--awb-product-title-font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['product_title_font_size'] ) . ';';
				}

				if ( ! $this->is_default( 'product_title_line_height' ) ) {
					$styles .= '--awb-product-title-line-height:' . $this->args['product_title_line_height'] . ';';
				}

				if ( ! $this->is_default( 'product_title_letter_spacing' ) ) {
					$styles .= '--awb-product-title-letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['product_title_letter_spacing'] ) . ';';
				}

				if ( ! $this->is_default( 'product_title_text_transform' ) ) {
					$styles .= '--awb-product-title-text-transform:' . $this->args['product_title_text_transform'] . ';';
				}

				if ( ! $this->is_default( 'product_title_color' ) ) {
					$styles .= '--awb-product-title-color:' . fusion_library()->sanitize->color( $this->args['product_title_color'] ) . ';';
				}

				// Product title hover styles.
				if ( ! $this->is_default( 'product_title_hover_color' ) ) {
					$styles .= '--awb-product-title-hover-color:' . fusion_library()->sanitize->color( $this->args['product_title_hover_color'] ) . ';';
				}

				// Product quantity styles.
				$price_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'product_price_font', 'array' );

				foreach ( $price_styles as $rule => $value ) {
					$styles .= '--awb-product-price-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
				}

				if ( ! $this->is_default( 'product_price_font_size' ) ) {
					$styles .= '--awb-product-price-font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['product_price_font_size'] ) . ';';
				}

				if ( ! $this->is_default( 'product_price_line_height' ) ) {
					$styles .= '--awb-product-price-line-height:' . $this->args['product_price_line_height'] . ';';
				}

				if ( ! $this->is_default( 'product_price_letter_spacing' ) ) {
					$styles .= '--awb-product-price-letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['product_price_letter_spacing'] ) . ';';
				}

				if ( ! $this->is_default( 'product_price_color' ) ) {
					$styles .= '--awb-product-price-color:' . fusion_library()->sanitize->color( $this->args['product_price_color'] ) . ';';
				}

				if ( 'no' !== $this->args['show_subtotal'] ) {

					// Subtotal alignment.
					if ( ! $this->is_default( 'subtotal_alignment' ) ) {
						$styles .= '--awb-subtotal-alignment:' . $this->args['subtotal_alignment'] . ';';
					}

					// Subtotal text styles.
					$subtotal_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'subtotal_text_font', 'array' );

					foreach ( $subtotal_styles as $rule => $value ) {
						$styles .= '--awb-subtotal-text-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
					}

					if ( ! $this->is_default( 'subtotal_text_font_size' ) ) {
						$styles .= '--awb-subtotal-text-font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['subtotal_text_font_size'] ) . ';';
					}

					if ( ! $this->is_default( 'subtotal_text_line_height' ) ) {
						$styles .= '--awb-subtotal-text-line-height:' . $this->args['subtotal_text_line_height'] . ';';
					}

					if ( ! $this->is_default( 'subtotal_text_letter_spacing' ) ) {
						$styles .= '--awb-subtotal-text-letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['subtotal_text_letter_spacing'] ) . ';';
					}

					if ( ! $this->is_default( 'subtotal_text_color' ) ) {
						$styles .= '--awb-subtotal-text-color:' . fusion_library()->sanitize->color( $this->args['subtotal_text_color'] ) . ';';
					}

					// Subtotal amount styles.
					$subtotal_amount_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'subtotal_amount_font', 'array' );

					foreach ( $subtotal_styles as $rule => $value ) {
						$styles .= '--awb-subtotal-amount-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
					}

					if ( ! $this->is_default( 'subtotal_amount_font_size' ) ) {
						$styles .= '--awb-subtotal-amount-font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['subtotal_amount_font_size'] ) . ';';
					}

					if ( ! $this->is_default( 'subtotal_amount_line_height' ) ) {
						$styles .= '--awb-subtotal-amount-line-height:' . $this->args['subtotal_amount_line_height'] . ';';
					}

					if ( ! $this->is_default( 'subtotal_amount_letter_spacing' ) ) {
						$styles .= '--awb-subtotal-amount-letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['subtotal_amount_letter_spacing'] ) . ';';
					}

					if ( ! $this->is_default( 'subtotal_amount_color' ) ) {
						$styles .= '--awb-subtotal-amount-color:' . fusion_library()->sanitize->color( $this->args['subtotal_amount_color'] ) . ';';
					}
				}

				if ( 'no' !== $this->args['show_buttons'] ) {
					// View cart text styles.
					$view_cart_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'view_cart_font', 'array' );

					foreach ( $view_cart_styles as $rule => $value ) {
						$styles .= '--awb-view-cart-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
					}

					if ( ! $this->is_default( 'view_cart_font_size' ) ) {
						$styles .= '--awb-view-cart-font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['view_cart_font_size'] ) . ';';
					}

					if ( ! $this->is_default( 'view_cart_line_height' ) ) {
						$styles .= '--awb-view-cart-line-height:' . $this->args['view_cart_line_height'] . ';';
					}

					if ( ! $this->is_default( 'view_cart_letter_spacing' ) ) {
						$styles .= '--awb-view-cart-letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['view_cart_letter_spacing'] ) . ';';
					}

					if ( ! $this->is_default( 'view_cart_text_transform' ) ) {
						$styles .= '--awb-view-cart-text-transform:' . $this->args['view_cart_text_transform'] . ';';
					}

					// View cart link styles.
					if ( 'link' === $this->args['link_style'] ) {
						if ( ! $this->is_default( 'view_cart_link_color' ) ) {
							$styles .= '--awb-view-cart-link-color:' . fusion_library()->sanitize->color( $this->args['view_cart_link_color'] ) . ';';
						}

						if ( ! $this->is_default( 'view_cart_link_hover_color' ) ) {
							$styles .= '--awb-view-cart-link-hover-color:' . fusion_library()->sanitize->color( $this->args['view_cart_link_hover_color'] ) . ';';
						}
					}

					// View cart button size.
					if ( 'button' === $this->args['link_style'] ) {
						// Button border width.
						if ( ! $this->is_default( 'view_cart_button_border_top' ) ) {
							$styles .= '--awb-view-cart-border-top-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['view_cart_button_border_top'] ) . ';';
						}
						if ( ! $this->is_default( 'view_cart_button_border_right' ) ) {
							$styles .= '--awb-view-cart-border-right-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['view_cart_button_border_right'] ) . ';';
						}
						if ( ! $this->is_default( 'view_cart_button_border_bottom' ) ) {
							$styles .= '--awb-view-cart-border-bottom-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['view_cart_button_border_bottom'] ) . ';';
						}
						if ( ! $this->is_default( 'view_cart_button_border_left' ) ) {
							$styles .= '--awb-view-cart-border-left-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['view_cart_button_border_left'] ) . ';';
						}

						// Button gradient.
						if ( ! $this->is_default( 'view_cart_button_gradient_top' ) || ! $this->is_default( 'view_cart_button_gradient_bottom' ) ) {
							$styles .= '--awb-view-cart-button-background:' . fusion_library()->sanitize->color( $this->args['view_cart_button_gradient_top'] ) . ';';
							$styles .= '--awb-view-cart-button-background-image:linear-gradient( to top, ' . fusion_library()->sanitize->color( $this->args['view_cart_button_gradient_bottom'] ) . ', ' . fusion_library()->sanitize->color( $this->args['view_cart_button_gradient_top'] ) . ' );';
						}

						// Button border color.
						if ( ! $this->is_default( 'view_cart_button_border_color' ) ) {
							$styles .= '--awb-view-cart-border-color:' . $this->args['view_cart_button_border_color'] . ';';
						}

						// Button gradient.
						if ( ! $this->is_default( 'view_cart_button_gradient_top_hover' ) || ! $this->is_default( 'view_cart_button_gradient_bottom_hover' ) ) {
							$styles .= '--awb-view-cart-button-hover-background:' . fusion_library()->sanitize->color( $this->args['view_cart_button_gradient_top_hover'] ) . ';';
							$styles .= '--awb-view-cart-button-hover-background-image:linear-gradient( to top, ' . fusion_library()->sanitize->color( $this->args['view_cart_button_gradient_bottom_hover'] ) . ', ' . fusion_library()->sanitize->color( $this->args['view_cart_button_gradient_top_hover'] ) . ' );';
						}

						// Button border color.
						if ( ! $this->is_default( 'view_cart_button_border_color_hover' ) ) {
							$styles .= '--awb-view-cart-hover-border-color:' . fusion_library()->sanitize->color( $this->args['view_cart_button_border_color_hover'] ) . ';';
						}

						// Button text color.
						if ( ! $this->is_default( 'view_cart_button_color' ) ) {
							$styles .= '--awb-view-cart-button-color:' . fusion_library()->sanitize->color( $this->args['view_cart_button_color'] ) . ';';
						}

						// Button hover text color.
						if ( ! $this->is_default( 'view_cart_button_color_hover' ) ) {
							$styles .= '--awb-view-cart-button-hover-color:' . fusion_library()->sanitize->color( $this->args['view_cart_button_color_hover'] ) . ';';
						}
					}

					// Checkout text styles.
					$checkout_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'checkout_font', 'array' );

					foreach ( $checkout_styles as $rule => $value ) {
						$styles .= '--awb-checkout-' . str_replace( '_', '-', $rule ) . ':' . $value . ';';
					}

					if ( ! $this->is_default( 'checkout_font_size' ) ) {
						$styles .= '--awb-checkout-font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['checkout_font_size'] ) . ';';
					}

					if ( ! $this->is_default( 'checkout_line_height' ) ) {
						$styles .= '--awb-checkout-line-height:' . $this->args['checkout_line_height'] . ';';
					}

					if ( ! $this->is_default( 'checkout_letter_spacing' ) ) {
						$styles .= '--awb-checkout-letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['checkout_letter_spacing'] ) . ';';
					}

					if ( ! $this->is_default( 'checkout_text_transform' ) ) {
						$styles .= '--awb-checkout-text-transform:' . $this->args['checkout_text_transform'] . ';';
					}

					// View cart link styles.
					if ( 'link' === $this->args['link_style'] ) {

						if ( ! $this->is_default( 'checkout_link_color' ) ) {
							$styles .= '--awb-checkout-link-color:' . fusion_library()->sanitize->color( $this->args['checkout_link_color'] ) . ';';
						}

						if ( ! $this->is_default( 'checkout_link_hover_color' ) ) {
							$styles .= '--awb-checkout-link-hover-color:' . fusion_library()->sanitize->color( $this->args['checkout_link_hover_color'] ) . ';';
						}
					}

					// View cart button size.
					if ( 'button' === $this->args['link_style'] ) {
						// Button border width.
						if ( ! $this->is_default( 'checkout_button_border_top' ) ) {
							$styles .= '--awb-checkout-border-top-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['checkout_button_border_top'] ) . ';';
						}
						if ( ! $this->is_default( 'checkout_button_border_right' ) ) {
							$styles .= '--awb-checkout-border-right-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['checkout_button_border_right'] ) . ';';
						}
						if ( ! $this->is_default( 'checkout_button_border_bottom' ) ) {
							$styles .= '--awb-checkout-border-bottom-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['checkout_button_border_bottom'] ) . ';';
						}
						if ( ! $this->is_default( 'checkout_button_border_left' ) ) {
							$styles .= '--awb-checkout-border-left-width:' . fusion_library()->sanitize->get_value_with_unit( $this->args['checkout_button_border_left'] ) . ';';
						}

						// Button gradient.
						if ( ! $this->is_default( 'checkout_button_gradient_top' ) || ! $this->is_default( 'checkout_button_gradient_bottom' ) ) {
							$styles .= '--awb-checkout-button-background:' . fusion_library()->sanitize->color( $this->args['checkout_button_gradient_top'] ) . ';';
							$styles .= '--awb-checkout-button-background-image:linear-gradient( to top, ' . fusion_library()->sanitize->color( $this->args['checkout_button_gradient_bottom'] ) . ', ' . fusion_library()->sanitize->color( $this->args['checkout_button_gradient_top'] ) . ' );';
						}

						// Button border color.
						if ( ! $this->is_default( 'checkout_button_border_color' ) ) {
							$styles .= '--awb-checkout-border-color:' . fusion_library()->sanitize->color( $this->args['checkout_button_border_color'] ) . ';';
						}

						// Button gradient.
						if ( ! $this->is_default( 'checkout_button_gradient_top_hover' ) || ! $this->is_default( 'checkout_button_gradient_bottom_hover' ) ) {
							$styles .= '--awb-checkout-button-hover-background:' . fusion_library()->sanitize->color( $this->args['checkout_button_gradient_top_hover'] ) . ';';
							$styles .= '--awb-checkout-button-hover-background-image:linear-gradient( to top, ' . fusion_library()->sanitize->color( $this->args['checkout_button_gradient_bottom_hover'] ) . ', ' . fusion_library()->sanitize->color( $this->args['checkout_button_gradient_top_hover'] ) . ' );';
						}

						// Button border color.
						if ( ! $this->is_default( 'checkout_button_border_color_hover' ) ) {
							$styles .= '--awb-checkout-hover-border-color:' . fusion_library()->sanitize->color( $this->args['checkout_button_border_color_hover'] ) . ';';
						}

						// Button text color.
						if ( ! $this->is_default( 'checkout_button_color' ) ) {
							$styles .= '--awb-checkout-button-color:' . fusion_library()->sanitize->color( $this->args['checkout_button_color'] ) . ';';
						}

						// Button hover text color.
						if ( ! $this->is_default( 'checkout_button_color_hover' ) ) {
							$styles .= '--awb-checkout-button-hover-color:' . fusion_library()->sanitize->color( $this->args['checkout_button_color_hover'] ) . ';';
						}
					}

					// Buttons margin.
					if ( ! $this->is_default( 'links_margin_top' ) ) {
						$styles .= '--awb-link-margin-top:' . fusion_library()->sanitize->get_value_with_unit( $this->args['links_margin_top'] ) . ';';
					}

					if ( ! $this->is_default( 'links_margin_bottom' ) ) {
						$styles .= '--awb-link-margin-bottom:' . fusion_library()->sanitize->get_value_with_unit( $this->args['links_margin_bottom'] ) . ';';
					}

					if ( ! $this->is_default( 'links_margin_left' ) ) {
						$styles .= '--awb-link-margin-left:' . fusion_library()->sanitize->get_value_with_unit( $this->args['links_margin_left'] ) . ';';
					}

					if ( ! $this->is_default( 'links_margin_right' ) ) {
						$styles .= '--awb-link-margin-right:' . fusion_library()->sanitize->get_value_with_unit( $this->args['links_margin_right'] ) . ';';
					}

					// Buttons alignment.
					if ( 'floated' === $this->args['buttons_layout'] && '' !== $this->args['buttons_justify'] ) {
						$styles .= '--awb-links-justify:' . $this->args['buttons_justify'] . ';';
					}

					if ( 'stacked' === $this->args['buttons_layout'] && '' !== $this->args['buttons_alignment'] ) {
						$styles .= '--awb-links-alignment:' . $this->args['buttons_alignment'] . ';';
					}
				}

				return $styles;
			}
		}
	}

	new Fusion_Woo_Mini_Cart();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.8
 */
function fusion_element_woo_mini_cart() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'Fusion_Woo_Mini_Cart',
			[
				'name'         => esc_attr__( 'Woo Mini Cart', 'fusion-builder' ),
				'shortcode'    => 'fusion_woo_mini_cart',
				'icon'         => 'fusiona-mini-cart',
				'subparam_map' => [
					'fusion_font_family_product_title_font' => 'product_title_fonts',
					'fusion_font_variant_product_title_font' => 'product_title_fonts',
					'product_title_font_size'            => 'product_title_fonts',
					'product_title_line_height'          => 'product_title_fonts',
					'product_title_letter_spacing'       => 'product_title_fonts',
					'product_title_text_transform'       => 'product_title_fonts',
					'product_title_color'                => 'product_title_fonts',
					'fusion_font_family_product_price_font' => 'product_price_fonts',
					'fusion_font_variant_product_price_font' => 'product_price_fonts',
					'product_price_font_size'            => 'product_price_fonts',
					'product_price_line_height'          => 'product_price_fonts',
					'product_price_letter_spacing'       => 'product_price_fonts',
					'product_price_color'                => 'product_price_fonts',
					'fusion_font_family_subtotal_text_font' => 'subtotal_text_fonts',
					'fusion_font_variant_subtotal_text_font' => 'subtotal_text_fonts',
					'subtotal_text_font_size'            => 'subtotal_text_fonts',
					'subtotal_text_line_height'          => 'subtotal_text_fonts',
					'subtotal_text_letter_spacing'       => 'subtotal_text_fonts',
					'subtotal_text_color'                => 'subtotal_text_fonts',
					'fusion_font_family_subtotal_amount_font' => 'subtotal_amount_fonts',
					'fusion_font_variant_subtotal_amount_font' => 'subtotal_amount_fonts',
					'subtotal_amount_font_size'          => 'subtotal_amount_fonts',
					'subtotal_amount_line_height'        => 'subtotal_amount_fonts',
					'subtotal_amount_letter_spacing'     => 'subtotal_amount_fonts',
					'subtotal_amount_color'              => 'subtotal_amount_fonts',
					'fusion_font_family_view_cart_font'  => 'view_cart_fonts',
					'fusion_font_variant_view_cart_font' => 'view_cart_fonts',
					'view_cart_font_size'                => 'view_cart_fonts',
					'view_cart_line_height'              => 'view_cart_fonts',
					'view_cart_letter_spacing'           => 'view_cart_fonts',
					'view_cart_text_transform'           => 'view_cart_fonts',
					'fusion_font_family_checkout_font'   => 'checkout_fonts',
					'fusion_font_variant_checkout_font'  => 'checkout_fonts',
					'checkout_font_size'                 => 'checkout_fonts',
					'checkout_text_transform'            => 'checkout_fonts',
					'checkout_line_height'               => 'checkout_fonts',
					'checkout_letter_spacing'            => 'checkout_fonts',
				],
				'params'       => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Images Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the image should take up. Enter value including any valid CSS unit, ex: 60px. Leave empty for default value.', 'fusion-builder' ),
						'param_name'  => 'images_max_width',
						'value'       => '',
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Separator Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the line style color of separators.', 'fusion-builder' ),
						'param_name'  => 'separator_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Product Title Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the product title. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'product_title_fonts',
						'choices'          => [
							'font-family'    => 'product_title_font',
							'font-size'      => 'product_title_font_size',
							'text-transform' => 'product_title_text_transform',
							'line-height'    => 'product_title_line_height',
							'letter-spacing' => 'product_title_letter_spacing',
							'color'          => 'product_title_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'text-transform' => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'link_color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Product Title Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover color of the product title.', 'fusion-builder' ),
						'param_name'  => 'product_title_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart-item a:not(.remove)',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Product Price Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the product price. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'product_price_fonts',
						'choices'          => [
							'font-family'    => 'product_price_font',
							'font-size'      => 'product_price_font_size',
							'line-height'    => 'product_price_line_height',
							'letter-spacing' => 'product_price_letter_spacing',
							'color'          => 'product_price_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
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
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Subtotal', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if subtotal should show or not.', 'fusion-builder' ),
						'param_name'  => 'show_subtotal',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Subtotal Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the alignment.', 'fusion-builder' ),
						'param_name'  => 'subtotal_alignment',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_subtotal',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Subtotal Text Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the subtotal text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'subtotal_text_fonts',
						'choices'          => [
							'font-family'    => 'subtotal_text_font',
							'font-size'      => 'subtotal_text_font_size',
							'line-height'    => 'subtotal_text_line_height',
							'letter-spacing' => 'subtotal_text_letter_spacing',
							'color'          => 'subtotal_text_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => $fusion_settings->get( 'body_typography', 'color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_subtotal',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Subtotal Amount Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the subtotal amount. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'subtotal_amount_fonts',
						'choices'          => [
							'font-family'    => 'subtotal_amount_font',
							'font-size'      => 'subtotal_amount_font_size',
							'line-height'    => 'subtotal_amount_line_height',
							'letter-spacing' => 'subtotal_amount_letter_spacing',
							'color'          => 'subtotal_amount_color',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'color'          => '',
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_subtotal',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Remove Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if remove icon should show or not.', 'fusion-builder' ),
						'param_name'  => 'show_remove_icon',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Remove Icon Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'remove_icon_styling_options',
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
								'element'  => 'show_remove_icon',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon color.', 'fusion-builder' ),
						'param_name'  => 'remove_icon_color',
						'value'       => '',
						'default'     => 'var(--awb-color1)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'remove_icon_styling_options',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_remove_icon',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the icon.', 'fusion-builder' ),
						'param_name'  => 'remove_icon_bg_color',
						'value'       => '',
						'default'     => 'var(--awb-color4)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'remove_icon_styling_options',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_remove_icon',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the icon color.', 'fusion-builder' ),
						'param_name'  => 'remove_icon_hover_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'remove_icon_styling_options',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_remove_icon',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart li .remove',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the icon.', 'fusion-builder' ),
						'param_name'  => 'remove_icon_hover_bg_color',
						'value'       => '',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'remove_icon_styling_options',
							'tab'  => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_remove_icon',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart li .remove',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Buttons', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if view cart and checkout links should or not.', 'fusion-builder' ),
						'param_name'  => 'show_buttons',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if buttons spans the full width/remaining width of row.', 'fusion-builder' ),
						'param_name'  => 'buttons_stretch',
						'default'     => 'no',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout for buttons. Floated will have them side by side.  Stacked will have one per row.', 'fusion-builder' ),
						'param_name'  => 'buttons_layout',
						'default'     => 'floated',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'value'       => [
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'buttons_alignment',
						'default'     => 'center',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
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
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'buttons_layout',
								'value'    => 'floated',
								'operator' => '!=',
							],
							[
								'element'  => 'buttons_stretch',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Buttons Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the content alignment.', 'fusion-builder' ),
						'param_name'  => 'buttons_justify',
						'default'     => 'center',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
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
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'buttons_layout',
								'value'    => 'floated',
								'operator' => '==',
							],
							[
								'element'  => 'buttons_stretch',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to have link or button for view cart and checkout texts.', 'fusion-builder' ),
						'param_name'  => 'link_style',
						'value'       => [
							'link'   => esc_attr__( 'Text Link', 'fusion-builder' ),
							'button' => esc_attr__( 'Button', 'fusion-builder' ),
						],
						'default'     => 'link',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Links Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'links_margin',
						'group'            => esc_attr__( 'Links', 'fusion-builder' ),
						'value'            => [
							'links_margin_top'    => '',
							'links_margin_right'  => '',
							'links_margin_bottom' => '',
							'links_margin_left'   => '',
						],
						'dependency'       => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the view cart and checkout icons position.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'View Cart Text Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the view cart text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'view_cart_fonts',
						'choices'          => [
							'font-family'    => 'view_cart_font',
							'font-size'      => 'view_cart_font_size',
							'text-transform' => 'view_cart_text_transform',
							'line-height'    => 'view_cart_line_height',
							'letter-spacing' => 'view_cart_letter_spacing',
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
						'group'            => esc_attr__( 'Links', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'View Cart Link Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'view_cart_link_styling_options',
						'default'          => 'regular',
						'group'            => esc_html__( 'Links', 'fusion-builder' ),
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
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the view cart link.', 'fusion-builder' ),
						'param_name'  => 'view_cart_link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'view_cart_link_styling_options',
							'tab'  => 'regular',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover text color of the view cart link.', 'fusion-builder' ),
						'param_name'  => 'view_cart_link_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'view_cart_link_styling_options',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a:not(.checkout)',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'View Cart Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the view cart button size.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_size',
						'default'     => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
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
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'View Cart Button Border Size', 'fusion-builder' ),
						'param_name'       => 'view_cart_button_border_width',
						'description'      => esc_attr__( 'Controls the view cart button border size. In pixels.', 'fusion-builder' ),
						'group'            => esc_attr__( 'Links', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'value'            => [
							'view_cart_button_border_top'  => '',
							'view_cart_button_border_right' => '',
							'view_cart_button_border_bottom' => '',
							'view_cart_button_border_left' => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [
								'dimension' => true,
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'View Cart Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'view_cart_button_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Links', 'fusion-builder' ),
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
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient top color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_gradient_top',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient bottom color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_gradient_bottom',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text hover color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a:not(.checkout)',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient top hover color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_gradient_top_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a:not(.checkout)',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient bottom hover color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_gradient_bottom_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a:not(.checkout)',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'View Cart Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover border color of the view cart button.', 'fusion-builder' ),
						'param_name'  => 'view_cart_button_border_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'subgroup'    => [
							'name' => 'view_cart_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a:not(.checkout)',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Checkout Text Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the checkout text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'checkout_fonts',
						'choices'          => [
							'font-family'    => 'checkout_font',
							'font-size'      => 'checkout_font_size',
							'text-transform' => 'checkout_text_transform',
							'line-height'    => 'checkout_line_height',
							'letter-spacing' => 'checkout_letter_spacing',
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
						'group'            => esc_attr__( 'Links', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
						'dependency'       => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Checkout Link Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'checkout_link_styling_options',
						'default'          => 'regular',
						'group'            => esc_html__( 'Links', 'fusion-builder' ),
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
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the checkout link.', 'fusion-builder' ),
						'param_name'  => 'checkout_link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'checkout_link_styling_options',
							'tab'  => 'regular',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover text color of the checkout link.', 'fusion-builder' ),
						'param_name'  => 'checkout_link_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'checkout_link_styling_options',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a.checkout',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'link',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Checkout Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the checkout button size.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_size',
						'default'     => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
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
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Checkout Button Border Size', 'fusion-builder' ),
						'param_name'       => 'checkout_button_border_width',
						'description'      => esc_attr__( 'Controls the checkout button border size. In pixels.', 'fusion-builder' ),
						'group'            => esc_attr__( 'Links', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'value'            => [
							'checkout_button_border_top'   => '',
							'checkout_button_border_right' => '',
							'checkout_button_border_bottom' => '',
							'checkout_button_border_left'  => '',
						],
						'callback'         => [
							'function' => 'fusion_style_block',
							'args'     => [

								'dimension' => true,
							],
						],
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Checkout Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'checkout_button_styling',
						'default'          => 'regular',
						'group'            => esc_html__( 'Links', 'fusion-builder' ),
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
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Gradient Top Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient top color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_gradient_top',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Gradient Bottom Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient bottom color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_gradient_bottom',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'regular',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Text Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text hover color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a.checkout',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Gradient Top Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient top hover color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_gradient_top_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a.checkout',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Gradient Bottom Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient bottom hover color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_gradient_bottom_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a.checkout',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Checkout Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover border color of the checkout button.', 'fusion-builder' ),
						'param_name'  => 'checkout_button_border_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Links', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'subgroup'    => [
							'name' => 'checkout_button_styling',
							'tab'  => 'hover',
						],
						'preview'     => [
							'selector' => '.woocommerce-mini-cart__buttons.buttons a.checkout',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
						'dependency'  => [
							[
								'element'  => 'show_buttons',
								'value'    => 'no',
								'operator' => '!=',
							],
							[
								'element'  => 'link_style',
								'value'    => 'button',
								'operator' => '==',
							],
						],
						'callback'    => [
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
					'fusion_animation_placeholder' => [
						'preview_selector' => '.awb-woo-mini-cart',
					],
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_woo_mini_cart',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_woo_mini_cart' );
