<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_notices' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Notices' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Notices extends Fusion_Woo_Component {

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
			 * Whether we are requesting from editor.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $live_ajax = false;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				parent::__construct( 'fusion_tb_woo_notices' );
				add_filter( 'fusion_attr_fusion_tb_woo_notices-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_fusion_tb_woo_notices-notice-icon', [ $this, 'notice_icon_attr' ] );
				add_filter( 'fusion_attr_fusion_tb_woo_notices-success-icon', [ $this, 'success_icon_attr' ] );
				add_filter( 'fusion_attr_fusion_tb_woo_notices-error-icon', [ $this, 'error_icon_attr' ] );
				add_filter( 'fusion_attr_fusion_tb_woo_notices-cart-icon', [ $this, 'cart_icon_attr' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_get_fusion_tb_woo_notices', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.2
			 * @return boolean
			 */
			public function should_render() {
				return is_singular() || is_shop() || is_product_category() || is_product_tag();
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
					'margin_bottom'              => '',
					'margin_left'                => '',
					'margin_right'               => '',
					'margin_top'                 => '',
					'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
					'class'                      => '',
					'id'                         => '',
					'animation_type'             => '',
					'animation_direction'        => 'down',
					'animation_speed'            => '0.1',
					'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
					'show_button'                => 'yes',
					'padding_top'                => '',
					'padding_right'              => '',
					'padding_bottom'             => '',
					'padding_left'               => '',
					'font_size'                  => '',
					'font_color'                 => '',
					'link_color'                 => '',
					'link_hover_color'           => '',
					'alignment'                  => 'left',
					'border_sizes_top'           => '',
					'border_sizes_right'         => '',
					'border_sizes_bottom'        => '',
					'border_sizes_left'          => '',
					'border_radius_top_left'     => '',
					'border_radius_top_right'    => '',
					'border_radius_bottom_right' => '',
					'border_radius_bottom_left'  => '',
					'border_style'               => 'solid',
					'border_color'               => '',
					'background_color'           => '',
					'icon'                       => 'fa-check-circle far',
					'icon_size'                  => '',
					'icon_color'                 => '',
					'success_border_color'       => '',
					'success_background_color'   => '',
					'success_icon'               => '',
					'success_icon_color'         => '',
					'success_text_color'         => '',
					'success_link_color'         => '',
					'success_link_hover_color'   => '',
					'error_border_color'         => '',
					'error_background_color'     => '',
					'error_icon'                 => '',
					'error_icon_color'           => '',
					'error_text_color'           => '',
					'error_link_color'           => '',
					'error_link_hover_color'     => '',
					'cart_icon_style'            => '',
					'cart_icon'                  => 'awb-icon-shopping-cart',
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, $this->shortcode_handle );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					$this->emulate_product();

					if ( ! $this->is_product() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					$this->live_ajax = true;

					$return_data['woo_notices'] = $this->get_notices();
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
				$this->emulate_product();

				$this->defaults = self::get_element_defaults();

				$this->args = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_notices' );

				$html  = '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_notices-shortcode' ) . '>';
				$html .= '<div class="woocommerce-notices-wrapper">';
				$html .= $this->get_notices();
				$html .= '</div>';
				$html .= '</div>';

				$this->restore_product();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo product images.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_notices() {
				if ( ( fusion_is_preview_frame() && ! is_preview_only() ) || $this->live_ajax ) {
					global $product, $post;
					$title = $this->is_product() ? $product->get_title() : $post->post_title;

					wc_add_notice( __( 'This is an error notice example.', 'fusion-builder' ), 'error' );
					/* translators: View Cart Link, Items notice. */
					wc_add_notice( sprintf( '<a href="#" class="button wc-forward">%s</a> %s', __( 'View cart', 'fusion-builder' ), sprintf( __( '"%s" has been added to your cart.', 'fusion-builder' ), $title ) ), 'success' );
					wc_add_notice( __( 'This is a general notice example.', 'fusion-builder' ), 'notice' );
				}

				if ( is_cart() && is_object( WC()->cart ) && WC()->cart->is_empty() && ! fusion_is_preview_frame() ) {
					ob_start();
					wc_empty_cart_message();
					$empty_msg = wp_strip_all_tags( ob_get_clean() );
					wc_add_notice( $empty_msg, 'notice' );
				}

				if ( fusion_library()->woocommerce->is_checkout_layout() && ! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required() && ! is_user_logged_in() ) {
					wc_add_notice( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ), 'error', [ 'class' => 'fusion-login-required' ] );
				}

				$content = '';
				ob_start();
				$this->print_notices();
				$content  = ob_get_clean();
				$content .= $this->get_styles();

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
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
					'class' => 'fusion-woo-notices-tb fusion-woo-notices-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( '' !== $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( '' !== $this->args['alignment'] ) {
					$attr['class'] .= ' alignment-text-' . $this->args['alignment'];
				}

				if ( '' !== $this->args['show_button'] ) {
					$attr['class'] .= ' show-button-' . $this->args['show_button'];
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
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function notice_icon_attr() {

				if ( empty( $this->args['notice_icon'] ) ) {
					$this->args['notice_icon'] = $this->args['icon'];
				}

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['notice_icon'] ) . ' fusion-woo-notices-tb-icon',
					'aria-hidden' => 'true',
				];

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function success_icon_attr() {

				if ( empty( $this->args['success_icon'] ) ) {
					$this->args['success_icon'] = $this->args['icon'];
				}

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['success_icon'] ) . ' fusion-woo-notices-tb-icon',
					'aria-hidden' => 'true',
				];

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function error_icon_attr() {

				if ( empty( $this->args['error_icon'] ) ) {
					$this->args['error_icon'] = $this->args['icon'];
				}

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['error_icon'] ) . ' fusion-woo-notices-tb-icon',
					'aria-hidden' => 'true',
				];

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.7
			 * @return array
			 */
			public function cart_icon_attr() {
				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['cart_icon'] ),
					'aria-hidden' => 'true',
				];

				return $attr;
			}

			/**
			 * Check for icon exists.
			 *
			 * @access public
			 * @since 3.2
			 * @param string $type message type.
			 * @return string
			 */
			public function has_icon( $type ) {

				if ( ! empty( $this->args[ $type . '_icon' ] ) ) {
					return true;
				}

				if ( ! empty( $this->args['icon'] ) ) {
					return true;
				}

				return false;
			}

			/**
			 * Prints notices.
			 *
			 * @access public
			 * @since 3.2
			 * @param bool $return should we return or not.
			 * @return string
			 */
			public function print_notices( $return = false ) {
				$notices = '';

				if ( is_object( WC()->session ) && function_exists( 'wc_notice_count' ) ) {
					$all_notices  = WC()->session->get( 'wc_notices', [] );
					$notice_types = apply_filters( 'woocommerce_notice_types', [ 'error', 'success', 'notice' ] );

					// Buffer output.
					ob_start();

					foreach ( $notice_types as $notice_type ) {
						if ( wc_notice_count( $notice_type ) > 0 ) {
							$messages = [];

							$notice_icon = '';
							if ( $this->has_icon( $notice_type ) ) {
								$notice_icon = '<i ' . FusionBuilder::attributes( 'fusion_tb_woo_notices-' . $notice_type . '-icon' ) . '></i>';
							}

							foreach ( $all_notices[ $notice_type ] as $key => $notice ) {
								$messages[] = isset( $notice['notice'] ) ? $notice['notice'] : $notice;

								if ( isset( $all_notices[ $notice_type ][ $key ]['notice'] ) ) {
									$text_msg    = $all_notices[ $notice_type ][ $key ]['notice'];
									$grab_button = '';

									if ( preg_match( '/<a\s(.+?)>(.+?)<\/a>/i', $text_msg, $matches ) ) {
										$grab_button = $matches[0];
										$text_msg    = str_replace( $grab_button, '', $text_msg );

										if ( 'success' === $notice_type && 'custom' === $this->args['cart_icon_style'] && '' !== $grab_button ) {
											$icon_cart_content = '<i ' . FusionBuilder::attributes( 'fusion_tb_woo_notices-cart-icon' ) . '></i>';
											$grab_button       = sprintf( '<a href="%s" tabindex="1" class="button wc-forward">%s %s</a>', esc_url( wc_get_cart_url() ), $icon_cart_content, esc_html__( 'View cart', 'fusion-builder' ) );
										}
									}
									$text_msg = sprintf( '%s <span class="wc-notices-text">%s</span> %s', $notice_icon, $text_msg, $grab_button );

									$all_notices[ $notice_type ][ $key ]['notice'] = $text_msg;
								}
							}

							wc_get_template(
								"notices/{$notice_type}.php",
								[
									'messages' => array_filter( $messages ), // @deprecated 3.9.0
									'notices'  => array_filter( $all_notices[ $notice_type ] ),
								]
							);
						}
					}

					wc_clear_notices();

					$notices = wc_kses_notice( ob_get_clean() );
				}

				if ( $return ) {
					return $notices;
				}

				echo $notices; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-notices-tb.fusion-woo-notices-tb-' . $this->counter;
				$this->dynamic_css   = [];
				$selector_messages   = [
					$this->base_selector . ' .woocommerce-info',
					$this->base_selector . ' .woocommerce-message',
				];
				$selector_error      = [
					$this->base_selector . ' .woocommerce-error li',
				];
				$selector_notices    = array_merge( $selector_messages, $selector_error );

				// Margin styles.
				if ( ! $this->is_default( 'margin_top' ) ) {
					$this->add_css_property( $selector_notices, 'margin-top', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) );
				}
				if ( ! $this->is_default( 'margin_right' ) ) {
					$this->add_css_property( $selector_notices, 'margin-right', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_right'] ) );
				}
				if ( ! $this->is_default( 'margin_bottom' ) ) {
					$this->add_css_property( $selector_notices, 'margin-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) );
				}
				if ( ! $this->is_default( 'margin_left' ) ) {
					$this->add_css_property( $selector_notices, 'margin-left', fusion_library()->sanitize->get_value_with_unit( $this->args['margin_left'] ) );
				}

				// Padding styles.
				if ( ! $this->is_default( 'padding_top' ) ) {
					$this->add_css_property( $selector_notices, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_top'] ) );
				}
				if ( ! $this->is_default( 'padding_right' ) ) {
					$this->add_css_property( $selector_notices, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_right'] ) );
				}
				if ( ! $this->is_default( 'padding_bottom' ) ) {
					$this->add_css_property( $selector_notices, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_bottom'] ) );
				}
				if ( ! $this->is_default( 'padding_left' ) ) {
					$this->add_css_property( $selector_notices, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_left'] ) );
				}

				// Text Styles.
				if ( ! $this->is_default( 'font_size' ) ) {
					$this->add_css_property( $selector_notices, 'font-size', $this->args['font_size'] );
				}
				if ( ! $this->is_default( 'font_color' ) ) {
					$this->add_css_property( $selector_notices, 'color', $this->args['font_color'] );
				}

				// Border size.
				if ( ! $this->is_default( 'border_sizes_top' ) ) {
					$this->add_css_property( $selector_notices, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_top'] ) );
				}
				if ( ! $this->is_default( 'border_sizes_right' ) ) {
					$this->add_css_property( $selector_notices, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_right'] ) );
				}
				if ( ! $this->is_default( 'border_sizes_bottom' ) ) {
					$this->add_css_property( $selector_notices, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_bottom'] ) );
				}
				if ( ! $this->is_default( 'border_sizes_left' ) ) {
					$this->add_css_property( $selector_notices, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_sizes_left'] ) );
				}

				// Border radius.
				if ( ! $this->is_default( 'border_radius_top_left' ) ) {
					$this->add_css_property( $selector_notices, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] ) );
				}
				if ( ! $this->is_default( 'border_radius_top_right' ) ) {
					$this->add_css_property( $selector_notices, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] ) );
				}
				if ( ! $this->is_default( 'border_radius_bottom_right' ) ) {
					$this->add_css_property( $selector_notices, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] ) );
				}
				if ( ! $this->is_default( 'border_radius_bottom_left' ) ) {
					$this->add_css_property( $selector_notices, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] ) );
				}

				// Border style.
				if ( ! $this->is_default( 'border_style' ) ) {
					$this->add_css_property( $selector_notices, 'border-style', $this->args['border_style'] );
				}

				// Border color.
				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selector_notices, 'border-color', $this->args['border_color'] );
				}

				// Background color.
				if ( ! $this->is_default( 'background_color' ) ) {
					$this->add_css_property( $selector_notices, 'background-color', $this->args['background_color'] );
				}

				// Icon Styles.
				$selectors = [
					$this->base_selector . ' .woocommerce-info .fusion-woo-notices-tb-icon',
					$this->base_selector . ' .woocommerce-message .fusion-woo-notices-tb-icon',
					$this->base_selector . ' .woocommerce-error .fusion-woo-notices-tb-icon',
				];
				if ( ! $this->is_default( 'icon_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['icon_size'] . 'px' );
				}
				if ( ! $this->is_default( 'icon_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['icon_color'] );
				}

				// Remove default cart icon styles.
				$selectors = [
					$this->base_selector . ' .woocommerce-message .wc-forward:before',
					$this->base_selector . ' .woocommerce-info .wc-forward:before',
				];
				if ( ! $this->is_default( 'cart_icon_style' ) ) {
					$this->add_css_property( $selectors, 'content', '""' );
					$this->add_css_property( $selectors, 'margin-right', '0' );
				}

				// Link & Hover Styles.
				$selectors = [
					$this->base_selector . ' .woocommerce-info .wc-forward',
					$this->base_selector . ' .woocommerce-message .wc-forward',
					$this->base_selector . ' .woocommerce-error .wc-forward',
				];
				if ( ! $this->is_default( 'link_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['link_color'] );
				}
				$selectors = [
					$this->base_selector . ' .woocommerce-info .wc-forward:hover',
					$this->base_selector . ' .woocommerce-message .wc-forward:hover',
					$this->base_selector . ' .woocommerce-error .wc-forward:hover',
				];
				if ( ! $this->is_default( 'link_hover_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['link_hover_color'] );
				}

				// Success styles.
				$selectors = [
					$this->base_selector . ' .woocommerce-message',
				];
				if ( ! $this->is_default( 'success_border_color' ) ) {
					$this->add_css_property( $selectors, 'border-color', $this->args['success_border_color'] );
				}
				if ( ! $this->is_default( 'success_background_color' ) ) {
					$this->add_css_property( $selectors, 'background-color', $this->args['success_background_color'] );
				}
				if ( ! $this->is_default( 'success_text_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['success_text_color'] );
				}
				$selectors = [
					$this->base_selector . ' .woocommerce-message .fusion-woo-notices-tb-icon',
				];
				if ( ! $this->is_default( 'success_icon_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['success_icon_color'] );
				}

				// Success Link & Hover Styles.
				$selectors = [
					$this->base_selector . ' .woocommerce-message .wc-forward',
				];
				if ( ! $this->is_default( 'success_link_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['success_link_color'] );
				}
				$selectors = [
					$this->base_selector . ' .woocommerce-message .wc-forward:hover',
				];
				if ( ! $this->is_default( 'success_link_hover_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['success_link_hover_color'] );
				}

				// Error styles.
				if ( ! $this->is_default( 'error_border_color' ) ) {
					$this->add_css_property( $selector_error, 'border-color', $this->args['error_border_color'] );
				}
				if ( ! $this->is_default( 'error_background_color' ) ) {
					$this->add_css_property( $selector_error, 'background-color', $this->args['error_background_color'] );
				}
				if ( ! $this->is_default( 'error_text_color' ) ) {
					$this->add_css_property( $selector_error, 'color', $this->args['error_text_color'] );
				}
				$selectors = [
					$this->base_selector . ' .woocommerce-error .fusion-woo-notices-tb-icon',
				];
				if ( ! $this->is_default( 'error_icon_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['error_icon_color'] );
				}

				// Error Link & Hover Styles.
				$selectors = [
					$this->base_selector . ' .woocommerce-error .wc-forward',
				];
				if ( ! $this->is_default( 'error_link_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['error_link_color'] );
				}
				$selectors = [
					$this->base_selector . ' .woocommerce-error .wc-forward:hover',
				];
				if ( ! $this->is_default( 'error_link_hover_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['error_link_hover_color'] );
				}

				$css = $this->parse_css();

				// Some responsive fix.
				$this->dynamic_css = [];
				$selectors         = [
					$this->base_selector . '.alignment-text-left:not(.button-position-left) .woocommerce-info .fusion-woo-notices-tb-icon',
					$this->base_selector . '.alignment-text-left:not(.button-position-left) .woocommerce-message .fusion-woo-notices-tb-icon',
					$this->base_selector . '.alignment-text-left:not(.button-position-left) .woocommerce-error .fusion-woo-notices-tb-icon',
				];
				$this->add_css_property( $selectors, 'float', 'left' );
				$this->add_css_property( $selectors, 'line-height', 'inherit' );
				$css .= sprintf( '@media %s { %s }', Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-640' ), $this->parse_css() );

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function on_first_render() {

				// On first render is also called for live editor so when you add the element.  We don't need that here.
				if ( null === $this->args || empty( $this->args ) ) {
					return;
				}

				if ( function_exists( 'is_checkout' ) && is_checkout() || function_exists( 'is_cart' ) && is_cart() ) {
					Fusion_Dynamic_JS::enqueue_script(
						'fusion-woo-notices',
						FusionBuilder::$js_folder_url . '/general/fusion-woo-notices.js',
						FusionBuilder::$js_folder_path . '/general/fusion-woo-notices.js',
						[ 'wc-checkout' ],
						'1',
						true
					);

					if ( isset( $this->args ) ) {

						$error_icon   = $this->has_icon( 'error' ) ? '<i ' . FusionBuilder::attributes( 'fusion_tb_woo_notices-error-icon' ) . '></i>' : '';
						$notice_icon  = $this->has_icon( 'notice' ) ? '<i ' . FusionBuilder::attributes( 'fusion_tb_woo_notices-notice-icon' ) . '></i>' : '';
						$success_icon = $this->has_icon( 'success' ) ? '<i ' . FusionBuilder::attributes( 'fusion_tb_woo_notices-success-icon' ) . '></i>' : '';

						Fusion_Dynamic_JS::localize_script(
							'fusion-woo-notices',
							'fusionWooNoticesVars',
							[
								'error_icon'     => $error_icon,
								'login_required' => ! WC()->checkout()->is_registration_enabled() && WC()->checkout()->is_registration_required(),
								'is_logged_in'   => is_user_logged_in(),
							]
						);
					}
				}
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function add_css_files() {
				if ( class_exists( 'Avada' ) ) {
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-notices.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-notices.min.css' );
				}
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-notices.min.css' );
			}
		}
	}

	new FusionTB_Woo_Notices();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_notices() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Notices',
			[
				'name'             => esc_attr__( 'Woo Notices', 'fusion-builder' ),
				'shortcode'        => 'fusion_tb_woo_notices',
				'icon'             => 'fusiona-woo-notices',
				'template_tooltip' => __( 'This element should only be added 1 time.', 'fusion-builder' ),
				'callback'         => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_notices',
					'ajax'     => true,
				],
				'params'           => [
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to align the content left, right or center.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'left',
						'value'       => [
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'dimension',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the notice.', 'fusion-builder' ),
						'param_name'  => 'border_sizes',
						'value'       => [
							'border_sizes_top'    => '',
							'border_sizes_right'  => '',
							'border_sizes_bottom' => '',
							'border_sizes_left'   => '',
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Border Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border style of the notice.', 'fusion-builder' ),
						'param_name'  => 'border_style',
						'value'       => [
							'solid'  => esc_attr__( 'Solid', 'fusion-builder' ),
							'dashed' => esc_attr__( 'Dashed', 'fusion-builder' ),
							'dotted' => esc_attr__( 'Dotted', 'fusion-builder' ),
						],
						'default'     => 'solid',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description'      => __( 'Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Text Size', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the size of the notice text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the notice icon. In pixels.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'value'       => '',
						'min'         => '0',
						'max'         => '250',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Cart Icon Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the cart icon style of the notice.', 'fusion-builder' ),
						'param_name'  => 'cart_icon_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_notices',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'show_button',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_html__( 'Cart Icon', 'fusion-builder' ),
						'param_name'  => 'cart_icon',
						'value'       => '',
						'description' => esc_html__( 'Select icon for cart message.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_notices',
							'ajax'     => true,
						],
						'dependency'  => [
							[
								'element'  => 'cart_icon_style',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'show_button',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Button', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide button.', 'fusion-builder' ),
						'param_name'  => 'show_button',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
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
						'preview_selector' => '.fusion-woo-notices-tb',
					],
					[
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Notice Types Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'notice_types_styling',
						'default'          => 'notice',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'remove_from_atts' => true,
						'value'            => [
							'notice'  => esc_html__( 'General', 'fusion-builder' ),
							'success' => esc_html__( 'Success State', 'fusion-builder' ),
							'error'   => esc_html__( 'Error State', 'fusion-builder' ),
						],
						'icons'            => [
							'notice'  => '<span class="fusiona-globe" style="font-size:18px;"></span>',
							'success' => '<span class="fusiona-check_circle" style="font-size:18px;"></span>',
							'error'   => '<span class="fusiona-exclamation-sign" style="font-size:18px;"></span>',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background-color for the notice message.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border-color for the notice message.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_html__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => 'fa-check-circle far',
						'description' => esc_html__( 'Select icon for notice message.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_notices',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the icon color for the notice message.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the text color for the notice message.', 'fusion-builder' ),
						'param_name'  => 'font_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Link Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the link color for the notice message.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Link Hover Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the link hover color for the notice message.', 'fusion-builder' ),
						'param_name'  => 'link_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'notice',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background-color for the success message.', 'fusion-builder' ),
						'param_name'  => 'success_background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border-color for the success message.', 'fusion-builder' ),
						'param_name'  => 'success_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_html__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'success_icon',
						'value'       => '',
						'description' => esc_html__( 'Select icon for success message.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_notices',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the icon color for the success message.', 'fusion-builder' ),
						'param_name'  => 'success_icon_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the text color for the success message.', 'fusion-builder' ),
						'param_name'  => 'success_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Link Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the link color for the success message.', 'fusion-builder' ),
						'param_name'  => 'success_link_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Link Hover Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the link hover color for the success message.', 'fusion-builder' ),
						'param_name'  => 'success_link_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'success',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background-color for the error message.', 'fusion-builder' ),
						'param_name'  => 'error_background_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border-color for the error message.', 'fusion-builder' ),
						'param_name'  => 'error_border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_html__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'error_icon',
						'value'       => '',
						'description' => esc_html__( 'Select icon for error message.', 'fusion-builder' ),
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_notices',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the icon color for the error message.', 'fusion-builder' ),
						'param_name'  => 'error_icon_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the text color for the error message.', 'fusion-builder' ),
						'param_name'  => 'error_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Link Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the link color for the error message.', 'fusion-builder' ),
						'param_name'  => 'error_link_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Link Hover Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the link hover color for the error message.', 'fusion-builder' ),
						'param_name'  => 'error_link_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'subgroup'    => [
							'name' => 'notice_types_styling',
							'tab'  => 'error',
						],
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_notices' );
