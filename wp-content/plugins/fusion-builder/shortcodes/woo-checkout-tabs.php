<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_checkout_tabs' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Checkout_Tabs' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionTB_Woo_Checkout_Tabs extends Fusion_Woo_Component {

			/**
			 * An array of the shortcode defaults.
			 *
			 * @access protected
			 * @since 3.3
			 * @var array
			 */
			protected $defaults;

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
				parent::__construct( 'fusion_tb_woo_checkout_tabs' );
				add_filter( 'fusion_attr_fusion_tb_woo_checkout_tabs-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_checkout_tabs', [ $this, 'ajax_render' ] );
			}


			/**
			 * Check if component should render
			 *
			 * @access public
			 * @since 3.3
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
			 * @since 3.3
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'layout'                         => $fusion_settings->get( 'woocommerce_product_tab_design' ),
					'nav_content_space'              => '',
					'margin_bottom'                  => '',
					'margin_left'                    => '',
					'margin_right'                   => '',
					'margin_top'                     => '',
					'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
					'class'                          => '',
					'id'                             => '',
					'animation_type'                 => '',
					'animation_direction'            => 'down',
					'animation_speed'                => '0.1',
					'animation_offset'               => $fusion_settings->get( 'animation_offset' ),

					'backgroundcolor'                => '',
					'inactivebackgroundcolor'        => '',
					'bordercolor'                    => '',

					'field_bg_color'                 => $fusion_settings->get( 'form_bg_color' ),
					'field_text_color'               => $fusion_settings->get( 'form_text_color' ),
					'field_border_color'             => $fusion_settings->get( 'form_border_color' ),
					'field_border_focus_color'       => $fusion_settings->get( 'form_focus_border_color' ),

					'show_tab_titles'                => 'yes',

					// Nav text color.
					'active_nav_text_color'          => '',
					'inactive_nav_text_color'        => '',

					// Content padding.
					'content_padding_top'            => '',
					'content_padding_right'          => '',
					'content_padding_bottom'         => '',
					'content_padding_left'           => '',

					// Nav padding.
					'nav_padding_top'                => '',
					'nav_padding_right'              => '',
					'nav_padding_bottom'             => '',
					'nav_padding_left'               => '',

					// Text styling.
					'text_color'                     => '',
					'fusion_font_family_text_font'   => '',
					'fusion_font_variant_text_font'  => '',
					'text_font_size'                 => '',
					'text_text_transform'            => '',
					'text_line_height'               => '',
					'text_letter_spacing'            => '',

					'link_color'                     => $fusion_settings->get( 'link_color' ),
					'link_hover_color'               => $fusion_settings->get( 'primary_color' ),

					// Title styling.
					'title_color'                    => '',
					'fusion_font_family_title_font'  => '',
					'fusion_font_variant_title_font' => '',
					'title_font_size'                => '',
					'title_text_transform'           => '',
					'title_line_height'              => '',
					'title_letter_spacing'           => '',

					// Payment Label.
					'payment_label_padding_top'      => '',
					'payment_label_padding_right'    => '',
					'payment_label_padding_bottom'   => '',
					'payment_label_padding_left'     => '',
					'payment_label_bg_color'         => $fusion_settings->get( 'testimonial_bg_color' ),
					'payment_label_bg_hover_color'   => '#f0f0f0',
					'payment_label_color'            => $fusion_settings->get( 'body_typography', 'color' ),
					'payment_label_hover_color'      => '',

					// Payment description.
					'payment_padding_top'            => '',
					'payment_padding_right'          => '',
					'payment_padding_bottom'         => '',
					'payment_padding_left'           => '',
					'payment_box_bg'                 => $fusion_settings->get( 'testimonial_bg_color' ),
					'payment_color'                  => $fusion_settings->get( 'body_typography', 'color' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.3
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_tabs' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_checkout_tabs'] = $this->get_woo_checkout_tabs_content();
				}

				echo wp_json_encode( $return_data );
				wp_die();
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.3
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_tabs' );

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_checkout_tabs-shortcode' ) . '>' . $this->get_woo_checkout_tabs_content() . '</div>';

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Checkout Billing element.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_woo_checkout_tabs_content() {
				global $wp_filter;

				if ( ! is_object( WC()->cart ) ) {
					return;
				}

				$backup_callbacks = [];
				$backup_filters   = [
					'woocommerce_before_checkout_form',
					'woocommerce_after_checkout_form',
				];

				// Backup filter callbacks.
				foreach ( $backup_filters as $filter ) {
					if ( isset( $wp_filter[ $filter ] ) ) {
						$backup_callbacks[ $filter ]     = $wp_filter[ $filter ]->callbacks;
						$wp_filter[ $filter ]->callbacks = [];
					}
				}

				$content = '';

				ob_start();
				include locate_template( 'templates/wc-before-checkout-form.php' );
				wc_get_template( 'checkout/form-checkout.php', [ 'checkout' => WC()->checkout() ] );
				$content .= ob_get_clean();
				$content .= '</div>';

				if ( 'no' === $this->args['show_tab_titles'] ) {

					// Billing address heading.
					$search = [
						'<h3>' . esc_html__( 'Billing details', 'woocommerce' ) . '</h3>',
						'<h3>' . esc_html__( 'Billing &amp; Shipping', 'woocommerce' ) . '</h3>',
					];

					$replace = [ '', '' ];
					$content = str_replace( $search, $replace, $content );

					// If shipping address is enabled.
					if ( is_object( WC()->cart ) && true === WC()->cart->needs_shipping_address() ) {
						$content = preg_replace( '/<h3 id=(.+?)>(.+?)<\/h3>/is', '<div id=$1>$2</div>', $content, 1 );
					}
				}

				// Restore filter callbacks.
				foreach ( $backup_filters as $filter ) {
					if ( isset( $wp_filter[ $filter ] ) ) {
						$wp_filter[ $filter ]->callbacks = $backup_callbacks[ $filter ];
					}
				}

				return apply_filters( 'fusion_woo_component_content', $content, $this->shortcode_handle, $this->args );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function attr() {
				$attr = [
					'class' => 'fusion-woo-checkout-tabs-tb fusion-woo-checkout-tabs-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 'horizontal' === $this->args['layout'] ) {
					$attr['class'] .= ' woo-tabs-horizontal';
				}

				if ( 'no' === $this->args['show_tab_titles'] ) {
					$attr['class'] .= ' woo-tabs-titles-disabled';
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.2
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-checkout-tabs-tb.fusion-woo-checkout-tabs-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				$sides = [ 'top', 'right', 'bottom', 'left' ];

				// Margins.
				foreach ( $sides as $side ) {

					// Element margin.
					$margin_name = 'margin_' . $side;

					if ( '' !== $this->args[ $margin_name ] ) {
						$this->add_css_property( $this->base_selector, 'margin-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_name ] ) );
					}
				}

				// Paddings.
				foreach ( $sides as $side ) {
					$content_padding_name = 'content_padding_' . $side;
					$nav_padding_name     = 'nav_padding_' . $side;

					// Add content padding to style.
					if ( '' !== $this->args[ $content_padding_name ] ) {
						$this->add_css_property( $this->base_selector . ' .avada-checkout', 'padding-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $content_padding_name ] ) );
					}

					// Add nav padding to style.
					if ( '' !== $this->args[ $nav_padding_name ] ) {
						$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li > a', 'padding-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $nav_padding_name ] ) );
					}
				}

				if ( 'vertical' === $this->args['layout'] && ! $this->is_default( 'nav_content_space' ) ) {
					$this->add_css_property( $this->base_selector . ' .avada-checkout', 'margin-left', 'calc(220px + ' . fusion_library()->sanitize->get_value_with_unit( $this->args['nav_content_space'] ) . ')' );
				}

				if ( ! $this->is_default( 'backgroundcolor' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li.is-active > a', 'background-color', $this->args['backgroundcolor'] );
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li > a:hover', 'background-color', $this->args['backgroundcolor'] );
					$this->add_css_property( $this->base_selector . ' .avada-checkout', 'background-color', $this->args['backgroundcolor'] );

					// Overlay.
					$this->add_css_property( $this->base_selector . ' .blockUI.blockOverlay', 'background-color', $this->args['backgroundcolor'], true );
				}

				if ( ! $this->is_default( 'inactivebackgroundcolor' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li > a', 'background-color', $this->args['inactivebackgroundcolor'] );
				}

				if ( ! $this->is_default( 'active_nav_text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li.is-active > a', 'color', $this->args['active_nav_text_color'] );
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li.is-active > a:after', 'color', $this->args['active_nav_text_color'] );
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li > a:hover', 'color', $this->args['active_nav_text_color'] );
				}

				if ( ! $this->is_default( 'inactive_nav_text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav > li > a', 'color', $this->args['inactive_nav_text_color'] );
				}

				if ( ! $this->is_default( 'bordercolor' ) ) {

					if ( 'horizontal' === $this->args['layout'] ) {
						$this->add_css_property( $this->base_selector . '.woo-tabs-horizontal > .woocommerce-checkout-nav .is-active', 'border-color', $this->args['bordercolor'] );
					} else {
						$this->add_css_property( $this->base_selector . ' .woocommerce-checkout-nav li a', 'border-color', $this->args['bordercolor'] );
					}

					$this->add_css_property( $this->base_selector . ' .avada-checkout', 'border-color', $this->args['bordercolor'] );
					$this->add_css_property( $this->base_selector . ' .avada-checkout .shop_table tr', 'border-color', $this->args['bordercolor'] );
					$this->add_css_property( $this->base_selector . ' .avada-checkout .shop_table tfoot', 'border-color', $this->args['bordercolor'] );
				}

				// Text styles.
				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .avada-checkout', 'color', $this->args['text_color'] );
					$this->add_css_property( $this->base_selector . ' .avada-checkout .shop_table tfoot .order-total .amount', 'color', $this->args['text_color'] );
					$this->add_css_property( $this->base_selector . ' .avada-checkout .shop_table tfoot .order-total .amount', 'font-weight', '700' );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $this->base_selector . ' .avada-checkout', 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['text_font_size'] ) );
				}

				if ( ! $this->is_default( 'text_line_height' ) ) {
					$this->add_css_property( $this->base_selector, 'line-height', $this->args['text_line_height'] );
				}

				if ( ! $this->is_default( 'text_letter_spacing' ) ) {
					$this->add_css_property( $this->base_selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['text_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'text_text_transform' ) ) {
					$this->add_css_property( $this->base_selector, 'text-transform', $this->args['text_text_transform'] );
				}

				// Text typography styles.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'text_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $this->base_selector . ' .avada-checkout', $rule, $value );
				}

				// Link color.
				if ( ! $this->is_default( 'link_color' ) ) {
					$this->add_css_property( $this->base_selector . ' a:not(.fusion-button)', 'color', $this->args['link_color'] );
				}

				// Link hover color.
				if ( ! $this->is_default( 'link_hover_color' ) ) {
					$this->add_css_property( $this->base_selector . ' a:not(.fusion-button):hover', 'color', $this->args['link_hover_color'] );
				}

				// Title styles.
				$title_selectors = [
					$this->base_selector . ' .avada-checkout h3',
				];
				if ( ! $this->is_default( 'title_color' ) ) {
					$this->add_css_property( $title_selectors, 'color', $this->args['title_color'] );
				}

				if ( ! $this->is_default( 'title_font_size' ) ) {
					$this->add_css_property( $title_selectors, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['title_font_size'] ) );
				}

				if ( ! $this->is_default( 'title_line_height' ) ) {
					$this->add_css_property( $title_selectors, 'line-height', $this->args['title_line_height'] );
				}

				if ( ! $this->is_default( 'title_letter_spacing' ) ) {
					$this->add_css_property( $title_selectors, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['title_letter_spacing'] ) );
				}

				if ( ! $this->is_default( 'title_text_transform' ) ) {
					$this->add_css_property( $title_selectors, 'text-transform', $this->args['title_text_transform'] );
				}

				// Title typography styles.
				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'title_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $title_selectors, $rule, $value );
				}

				$inputs = [
					$this->base_selector . ' input',
					$this->base_selector . ' select',
					$this->base_selector . ' textarea',
				];

				if ( ! $this->is_default( 'field_bg_color' ) ) {
					$this->add_css_property( $inputs, 'background-color', $this->args['field_bg_color'] );

					// Select 2.
					if ( ! $is_builder ) {
						$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single', 'background-color', $this->args['field_bg_color'] );
					} else {
						$this->add_css_property( $this->base_selector . ' .avada-select-parent .select-arrow', 'background-color', 'transparent', true );
					}
				}

				if ( ! $this->is_default( 'field_text_color' ) ) {
					$this->add_css_property( $inputs, 'color', $this->args['field_text_color'] );

					// Select 2.
					if ( ! $is_builder ) {
						$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single .select2-selection__rendered', 'color', $this->args['field_text_color'] );
					} else {
						$this->add_css_property( $this->base_selector . ' .avada-select-parent .select-arrow', 'color', $this->args['field_text_color'] );
					}

					$placeholder_color  = Fusion_Color::new_color( $this->args['field_text_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
					$placeholder_inputs = [
						$this->base_selector . ' input::placeholder',
						$this->base_selector . ' textarea::placeholder',
					];
					$this->add_css_property( $placeholder_inputs, 'color', $placeholder_color );
					$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single .select2-selection__rendered .select2-selection__placeholder', 'color', $placeholder_color );
				}

				if ( ! $this->is_default( 'field_border_color' ) ) {
					$this->add_css_property( $inputs, 'border-color', $this->args['field_border_color'] );

					// Select 2.
					if ( ! $is_builder ) {
						$inputs = [
							$this->base_selector . ' .select2-container .select2-selection .select2-selection__arrow',
							$this->base_selector . ' .select2-container--default .select2-selection--single',
						];
						$this->add_css_property( $inputs, 'border-color', $this->args['field_border_color'] );
						$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single .select2-selection__arrow b', 'border-top-color', $this->args['field_border_color'] );
					} else {
						$this->add_css_property( $this->base_selector . ' .avada-select-parent .select-arrow', 'border-color', $this->args['field_border_color'] );
						$this->add_css_property( $this->base_selector . ' .avada-select-parent .select-arrow', 'color', $this->args['field_border_color'] );
					}
				}

				if ( ! $this->is_default( 'field_border_focus_color' ) ) {
					$hover_color  = Fusion_Color::new_color( $this->args['field_border_focus_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
					$hover_inputs = [
						$this->base_selector . ' input:hover',
						$this->base_selector . ' select:hover',
						$this->base_selector . ' textarea:hover',
					];
					$this->add_css_property( $hover_inputs, 'border-color', $hover_color );

					// Select 2.
					if ( ! $is_builder ) {
						$hover_inputs = [
							$this->base_selector . ' .select2-container:hover .select2-selection .select2-selection__arrow',
							$this->base_selector . ' .select2-container--default:hover .select2-selection--single',
						];
						$this->add_css_property( $hover_inputs, 'border-color', $hover_color );
						$this->add_css_property( $this->base_selector . ' .select2-container--default:hover .select2-selection--single .select2-selection__arrow b', 'border-top-color', $hover_color );
					} else {
						$this->add_css_property( $this->base_selector . ' .avada-select-parent:hover .select-arrow', 'border-color', $hover_color );
						$this->add_css_property( $this->base_selector . ' .avada-select-parent:hover .select-arrow', 'color', $hover_color );
					}

					$focus_inputs = [
						$this->base_selector . ' input:focus',
						$this->base_selector . ' select:focus',
						$this->base_selector . ' textarea:focus',
					];
					$this->add_css_property( $focus_inputs, 'border-color', $this->args['field_border_focus_color'] );
				}

				// Labels.
				$selector = $this->base_selector . ' .woocommerce-checkout-payment ul.wc_payment_methods li label';

				if ( ! $this->is_default( 'payment_label_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['payment_label_padding_top'] ) );
				}

				if ( ! $this->is_default( 'payment_label_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['payment_label_padding_bottom'] ) );
				}

				if ( ! $this->is_default( 'payment_label_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', 'max(2.5em,' . fusion_library()->sanitize->get_value_with_unit( $this->args['payment_label_padding_left'] ) . ')' );
				}

				if ( ! $this->is_default( 'payment_label_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['payment_label_padding_right'] ) );
				}

				if ( ! $this->is_default( 'payment_label_bg_color' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['payment_label_bg_color'] );
				}

				if ( ! $this->is_default( 'payment_label_color' ) ) {
					$this->add_css_property( $selector, 'color', $this->args['payment_label_color'] );
				}

				if ( ! $this->is_default( 'payment_label_hover_color' ) ) {
					$this->add_css_property( $selector . ':hover', 'color', $this->args['payment_label_hover_color'] );
					$this->add_css_property( $this->base_selector . ' ul li input:checked+label', 'color', $this->args['payment_label_hover_color'] );
				}

				$selector = $this->base_selector . ' .woocommerce-checkout-payment ul.wc_payment_methods li:hover label';

				if ( ! $this->is_default( 'payment_label_bg_hover_color' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['payment_label_bg_hover_color'] );
				}

				// Payment box.
				$selector = [
					$this->base_selector . ' .woocommerce-checkout-payment ul.wc_payment_methods li .payment_box',
					$this->base_selector . ' .woocommerce-checkout-payment ul.wc_payment_methods li.woocommerce-notice',
				];

				if ( ! $this->is_default( 'payment_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['payment_padding_top'] ) );
				}

				if ( ! $this->is_default( 'payment_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['payment_padding_bottom'] ) );
				}

				if ( ! $this->is_default( 'payment_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', 'max(2.5em,' . fusion_library()->sanitize->get_value_with_unit( $this->args['payment_padding_left'] ) . ')' );
				}

				if ( ! $this->is_default( 'payment_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['payment_padding_right'] ) );
				}

				if ( ! $this->is_default( 'payment_box_bg' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['payment_box_bg'] );
				}

				if ( ! $this->is_default( 'payment_color' ) ) {
					$this->add_css_property( $selector, 'color', $this->args['payment_color'] );
				}

				$css = $this->parse_css();

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
				wp_enqueue_script( 'wc-checkout' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.3
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-checkout-tabs.min.css' );
			}
		}
	}

	new FusionTB_Woo_Checkout_Tabs();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_component_woo_checkout_tabs() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Checkout_Tabs',
			[
				'name'         => esc_attr__( 'Woo Checkout Tabs', 'fusion-builder' ),
				'shortcode'    => 'fusion_tb_woo_checkout_tabs',
				'icon'         => 'fusiona-checkout-tabs',
				'subparam_map' => [
					'margin_top'                     => 'margin',
					'margin_right'                   => 'margin',
					'margin_bottom'                  => 'margin',
					'margin_left'                    => 'margin',
					'nav_padding_top'                => 'nav_padding',
					'nav_padding_right'              => 'nav_padding',
					'nav_padding_bottom'             => 'nav_padding',
					'nav_padding_left'               => 'nav_padding',
					'content_padding_top'            => 'content_padding',
					'content_padding_right'          => 'content_padding',
					'content_padding_bottom'         => 'content_padding',
					'content_padding_left'           => 'content_padding',
					'payment_label_padding_top'      => 'payment_label_padding',
					'payment_label_padding_right'    => 'payment_label_padding',
					'payment_label_padding_bottom'   => 'payment_label_padding',
					'payment_label_padding_left'     => 'payment_label_padding',
					'payment_padding_top'            => 'payment_padding',
					'payment_padding_right'          => 'payment_padding',
					'payment_padding_bottom'         => 'payment_padding',
					'payment_padding_left'           => 'payment_padding',
					'fusion_font_family_title_font'  => 'title_fonts',
					'fusion_font_variant_title_font' => 'title_fonts',
					'title_font_size'                => 'title_fonts',
					'title_text_transform'           => 'title_fonts',
					'title_line_height'              => 'title_fonts',
					'title_letter_spacing'           => 'title_fonts',
					'title_color'                    => 'title_fonts',
					'fusion_font_family_text_font'   => 'text_fonts',
					'fusion_font_variant_text_font'  => 'text_fonts',
					'text_font_size'                 => 'text_fonts',
					'text_text_transform'            => 'text_fonts',
					'text_line_height'               => 'text_fonts',
					'text_letter_spacing'            => 'text_fonts',
					'text_color'                     => 'text_fonts',
				],
				'params'       => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Choose the tabs layout.' ),
						'param_name'  => 'layout',
						'default'     => '',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_checkout_tabs',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Space Between Nav and Content', 'fusion-builder' ),
						'description' => esc_html__( 'Set space between tab nav and tab content sections. Leave empty for default value of 20px.', 'fusion-builder' ),
						'param_name'  => 'nav_content_space',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'layout',
								'value'    => 'vertical',
								'operator' => '==',
							],
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Show Tab Content Headings', 'fusion-builder' ),
						'description' => esc_html__( 'Choose to have tab content headings displayed.', 'fusion-builder' ),
						'param_name'  => 'show_tab_titles',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_checkout_tabs',
							'ajax'     => true,
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
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the background tab color. ', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Inactive Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the inactive tab background color. ', 'fusion-builder' ),
						'param_name'  => 'inactivebackgroundcolor',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Inactive Nav Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the inactive nav text color, ex: #000.' ),
						'param_name'  => 'inactive_nav_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Active Nav Text Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the active nav text color, ex: #000.' ),
						'param_name'  => 'active_nav_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Border Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the border color. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => '#e7e7e7',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Nav Padding', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 10px 0 10px 0 value.', 'fusion-builder' ),
						'param_name'       => 'nav_padding',
						'value'            => [
							'nav_padding_top'    => '',
							'nav_padding_right'  => '',
							'nav_padding_bottom' => '',
							'nav_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Content Padding', 'fusion-builder' ),
						'description'      => esc_html__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default 40px value.', 'fusion-builder' ),
						'param_name'       => 'content_padding',
						'value'            => [
							'content_padding_top'    => '',
							'content_padding_right'  => '',
							'content_padding_bottom' => '',
							'content_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Content Heading Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the content heading. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'title_fonts',
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'title_font_size',
							'text-transform' => 'title_text_transform',
							'line-height'    => 'title_line_height',
							'letter-spacing' => 'title_letter_spacing',
							'color'          => 'title_color',
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
						'dependency'       => [
							[
								'element'  => 'show_tab_titles',
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
						'heading'          => esc_attr__( 'Content Text Typography', 'fusion-builder' ),
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link color of the payments text.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link hover color of the payments text.', 'fusion-builder' ),
						'param_name'  => 'link_hover_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Field Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the form input fields.', 'fusion-builder' ),
						'param_name'  => 'field_bg_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Field Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color of the form input fields.', 'fusion-builder' ),
						'param_name'  => 'field_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Field Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the form input fields.', 'fusion-builder' ),
						'param_name'  => 'field_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Field Border Color On Focus', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the form input fields on focus.', 'fusion-builder' ),
						'param_name'  => 'field_border_focus_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'form_focus_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Payment Label Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default value.', 'fusion-builder' ),
						'param_name'       => 'payment_label_padding',
						'value'            => [
							'payment_label_padding_top'    => '',
							'payment_label_padding_right'  => '',
							'payment_label_padding_bottom' => '',
							'payment_label_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Label Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the payment label background color.', 'fusion-builder' ),
						'param_name'  => 'payment_label_bg_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Label Hover Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the payment label hover background color.', 'fusion-builder' ),
						'param_name'  => 'payment_label_bg_hover_color',
						'value'       => '',
						'default'     => '#f0f0f0',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Label Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the payment label color.', 'fusion-builder' ),
						'param_name'  => 'payment_label_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Label Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the payment label hover color.', 'fusion-builder' ),
						'param_name'  => 'payment_label_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Payment Description Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%. Leave empty to use default value.', 'fusion-builder' ),
						'param_name'       => 'payment_padding',
						'value'            => [
							'payment_padding_top'    => '',
							'payment_padding_right'  => '',
							'payment_padding_bottom' => '',
							'payment_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Description Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the payments description.', 'fusion-builder' ),
						'param_name'  => 'payment_box_bg',
						'value'       => '',
						'default'     => $fusion_settings->get( 'testimonial_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Payment Description Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the payment description text color.', 'fusion-builder' ),
						'param_name'  => 'payment_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-woo-checkout-tabs-tb',
					],
				],
				'callback'     => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_checkout_tabs',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_checkout_tabs' );
