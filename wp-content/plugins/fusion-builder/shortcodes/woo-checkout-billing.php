<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_checkout_billing' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Checkout_Billing' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionTB_Woo_Checkout_Billing extends Fusion_Woo_Component {

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
				parent::__construct( 'fusion_tb_woo_checkout_billing' );
				add_filter( 'fusion_attr_fusion_tb_woo_checkout_billing-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_checkout_billing', [ $this, 'ajax_render' ] );
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
					'margin_bottom'            => '',
					'margin_left'              => '',
					'margin_right'             => '',
					'margin_top'               => '',
					'field_bg_color'           => $fusion_settings->get( 'form_bg_color' ),
					'field_text_color'         => $fusion_settings->get( 'form_text_color' ),
					'field_border_color'       => $fusion_settings->get( 'form_border_color' ),
					'field_border_focus_color' => $fusion_settings->get( 'form_focus_border_color' ),
					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',
					'animation_type'           => '',
					'animation_direction'      => 'down',
					'animation_speed'          => '0.1',
					'animation_offset'         => $fusion_settings->get( 'animation_offset' ),
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_billing' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
					$return_data['woo_checkout_billing'] = $this->get_woo_checkout_billing_content();
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
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_checkout_billing' );

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_checkout_billing-shortcode' ) . '>' . $this->get_woo_checkout_billing_content() . '</div>';

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
			public function get_woo_checkout_billing_content() {
				$content = '';

				if ( ! is_object( WC()->cart ) || 0 === WC()->cart->get_cart_contents_count() ) {
					return $content;
				}

				ob_start();
				do_action( 'woocommerce_checkout_billing' );
				$content = preg_replace( '#<h3>(.*?)</h3>#', '', ob_get_clean(), 1 );

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
					'class' => 'fusion-woo-checkout-billing-tb fusion-woo-checkout-billing-tb-' . $this->counter,
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

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-woo-checkout-billing-tb-' . $this->counter;
				$this->dynamic_css   = [];
				$is_builder          = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				$inputs = [
					$this->base_selector . ' input',
					$this->base_selector . ' select',
					$this->base_selector . ' textarea',
				];

				if ( ! $this->is_default( 'field_bg_color' ) ) {
					$this->add_css_property( $inputs, 'background', $this->args['field_bg_color'], $is_builder );

					// Select 2.
					if ( ! $is_builder ) {
						$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single', 'background-color', $this->args['field_bg_color'] );
					} else {
						$this->add_css_property( $this->base_selector . ' .avada-select-parent .select-arrow', 'background-color', 'transparent', true );
					}
				}

				if ( ! $this->is_default( 'field_text_color' ) ) {
					$this->add_css_property( $inputs, 'color', $this->args['field_text_color'], $is_builder );

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
					$this->add_css_property( $inputs, 'border-color', $this->args['field_border_color'], $is_builder );

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
					$this->add_css_property( $hover_inputs, 'border-color', $hover_color, $is_builder );

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

				$css = $this->parse_css();
				return $css ? '<style type="text/css">' . $css . '</style>' : '';
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
		}
	}

	new FusionTB_Woo_Checkout_Billing();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_component_woo_checkout_billing() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Checkout_Billing',
			[
				'name'      => esc_attr__( 'Woo Checkout Billing', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_checkout_billing',
				'icon'      => 'fusiona-checkout-billing',
				'params'    => [
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
						'preview_selector' => '.fusion-woo-checkout-billing-tb',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_checkout_billing',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_checkout_billing' );
