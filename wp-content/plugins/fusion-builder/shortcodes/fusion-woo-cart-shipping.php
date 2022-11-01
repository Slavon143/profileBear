<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_WooCartShipping' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.3
		 */
		class FusionSC_WooCartShipping extends Fusion_Element {

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
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_woo-cart-shipping-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_woo_cart_shipping', [ $this, 'render' ] );

				// Ajax mechanism for query related part.
				add_action( 'wp_ajax_fusion_get_woo_cart_shipping', [ $this, 'ajax_query' ] );
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
				$this->args = isset( $_POST['model']['params'] ) ? wp_unslash( $_POST['model']['params'] ) : []; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$html       = $this->generate_element_content();

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
					'margin_top'               => '',
					'margin_right'             => '',
					'margin_bottom'            => '',
					'margin_left'              => '',

					'hide_on_mobile'           => fusion_builder_default_visibility( 'string' ),
					'class'                    => '',
					'id'                       => '',

					// Fields.
					'field_bg_color'           => $fusion_settings->get( 'form_bg_color' ),
					'field_text_color'         => $fusion_settings->get( 'form_text_color' ),
					'field_border_color'       => $fusion_settings->get( 'form_border_color' ),
					'field_border_focus_color' => $fusion_settings->get( 'form_focus_border_color' ),

					// Animation.
					'animation_direction'      => 'left',
					'animation_offset'         => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'          => '',
					'animation_type'           => '',
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

				ob_start();
				?>
				<?php do_action( 'woocommerce_before_shipping_calculator' ); ?>
				<form <?php echo FusionBuilder::attributes( 'woo-cart-shipping-shortcode' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
					<?php echo $this->generate_element_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</form>
				<?php do_action( 'woocommerce_after_shipping_calculator' ); ?>
				<?php
				$html  = ob_get_clean();
				$html .= $this->get_styles();

				$this->on_render();
				$this->counter++;
				return apply_filters( 'fusion_element_cart_shipping_content', $html, $args );

			}


			/**
			 * Generates element content
			 *
			 * @return string
			 */
			public function generate_element_content() {
				ob_start();
				?>

				<div class="avada-shipping-calculator-form">
				<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_country', true ) ) : ?>
					<p class="form-row form-row-wide" id="shipping_country_field">
						<select name="calc_shipping_country" id="shipping_country" class="country_to_state country_select " rel="shipping_state">
							<option value=""><?php esc_html_e( 'Select a country&hellip;', 'woocommerce' ); ?></option>
							<?php
							foreach ( WC()->countries->get_shipping_countries() as $key => $value ) {
								echo '<option value="' . esc_attr( $key ) . '"' . selected( WC()->customer->get_shipping_country(), esc_attr( $key ), false ) . '>' . esc_html( $value ) . '</option>';
							}
							?>
						</select>
					</p>
				<?php endif; ?>

				<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_state', true ) ) : ?>
					<p id="shipping_state_field" class="form-row form-row-wide fusion-layout-column fusion-one-half fusion-spacing-yes">
						<?php
						$current_cc = WC()->customer->get_shipping_country();
						$current_r  = WC()->customer->get_shipping_state();
						$states     = WC()->countries->get_states( $current_cc );
						?>

						<?php if ( is_array( $states ) && empty( $states ) ) : // Hidden Input. ?>

							<input type="hidden" name="calc_shipping_state" id="shipping_state" placeholder="<?php esc_attr_e( 'State / county', 'woocommerce' ); ?>" />

						<?php elseif ( is_array( $states ) ) : // Dropdown Input. ?>


								<select name="calc_shipping_state" id="shipping_state" class="state_select" placeholder="<?php esc_attr_e( 'State / county', 'woocommerce' ); ?>">
									<option value=""><?php esc_html_e( 'Select a state&hellip;', 'woocommerce' ); ?></option>
									<?php
									foreach ( $states as $ckey => $cvalue ) {
										echo '<option value="' . esc_attr( $ckey ) . '" ' . selected( $current_r, $ckey, false ) . '>' . esc_html( $cvalue ) . '</option>';
									}
									?>
								</select>


						<?php else : // Standard Input. ?>

							<input type="text" class="input-text" value="<?php echo esc_attr( $current_r ); ?>" placeholder="<?php esc_attr_e( 'State / county', 'woocommerce' ); ?>" name="calc_shipping_state" id="calc_shipping_state" />

						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_city', true ) ) : ?>

					<p id="calc_shipping_city_field" class="form-row form-row-wide fusion-layout-column fusion-one-half fusion-spacing-yes fusion-column-last">
						<input type="text" class="input-text" value="<?php echo esc_attr( WC()->customer->get_shipping_city() ); ?>" placeholder="<?php esc_attr_e( 'City', 'woocommerce' ); ?>" name="calc_shipping_city" id="calc_shipping_city" />
					</p>

				<?php endif; ?>

				<?php if ( apply_filters( 'woocommerce_shipping_calculator_enable_postcode', true ) ) : ?>

					<div id="calc_shipping_postcode_field" class="form-row form-row-wide fusion-layout-column fusion-one-half fusion-spacing-yes fusion-column-last">
						<input type="text" class="input-text" value="<?php echo esc_attr( WC()->customer->get_shipping_postcode() ); ?>" placeholder="<?php esc_attr_e( 'Postcode / ZIP', 'woocommerce' ); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
					</div>

				<?php endif; ?>

				<p class="form-row fusion-layout-column fusion-one-half fusion-spacing-yes fusion-column-last fusion-shipping-update-totals">
					<button type="submit" name="calc_shipping" value="1" class="fusion-button button-default fusion-button-default-size button"><?php esc_attr_e( 'Update totals', 'woocommerce' ); ?></button>
				</p>

				<?php wp_nonce_field( 'woocommerce-shipping-calculator', 'woocommerce-shipping-calculator-nonce' ); ?>
				</div>
				<?php
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
				$this->base_selector = '.fusion-woocommerce-shipping-calculator-' . $this->counter;
				$this->dynamic_css   = [];

				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				$inputs = [
					$this->base_selector . ' input',
					$this->base_selector . ' select',
					$this->base_selector . ' textarea',
					$this->base_selector . ' .select2-container--default .select2-selection--single',
				];

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

				if ( ! $this->is_default( 'field_bg_color' ) ) {
					$this->add_css_property( $inputs, 'background', $this->args['field_bg_color'], true );
				}

				if ( ! $this->is_default( 'field_text_color' ) ) {
					$this->add_css_property( $inputs, 'color', $this->args['field_text_color'] );

					// Select 2.
					$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single .select2-selection__rendered', 'color', $this->args['field_text_color'], true );

					$placeholder_color  = Fusion_Color::new_color( $this->args['field_text_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
					$placeholder_inputs = [
						$this->base_selector . ' input::placeholder',
						$this->base_selector . ' textarea::placeholder',
					];
					$this->add_css_property( $placeholder_inputs, 'color', $placeholder_color );
					$this->add_css_property( $this->base_selector . ' .select2-container--default .select2-selection--single .select2-selection__rendered .select2-selection__placeholder', 'color', $placeholder_color, true );
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
						$this->add_css_property( $hover_inputs, 'border-color', $hover_color, true );
						$this->add_css_property( $this->base_selector . ' .select2-container--default:hover .select2-selection--single .select2-selection__arrow b', 'border-top-color', $hover_color, true );
					} else {
						$this->add_css_property( $this->base_selector . ' .avada-select-parent:hover .select-arrow', 'border-color', $hover_color );
						$this->add_css_property( $this->base_selector . ' .avada-select-parent:hover .select-arrow', 'color', $hover_color );
					}

					$focus_inputs = [
						$this->base_selector . ' input:focus',
						$this->base_selector . ' select:focus',
						$this->base_selector . ' textarea:focus',
					];
					$this->add_css_property( $focus_inputs, 'border-color', $this->args['field_border_focus_color'], true );
				}

				$css  = $this->parse_css();
				$css .= $this->media_query_styles();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Adds media query styles.
			 *
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function media_query_styles() {
				$fusion_settings = awb_get_fusion_settings();
				$base_selector   = '.fusion-woocommerce-shipping-calculator-' . $this->counter;

				$css  = '@media only screen and (max-width:' . $fusion_settings->get( 'content_break_point' ) . 'px) {';
				$css .= $base_selector . ' p.fusion-layout-column.fusion-column-last:last-of-type {';
				$css .= 'margin-bottom: 0px;';
				$css .= '}}';

				$css .= '@media only screen and (min-width:' . $fusion_settings->get( 'content_break_point' ) . 'px) {';
				$css .= $base_selector . ' .fusion-layout-column.fusion-column-last:last-of-type {';
				$css .= 'margin-bottom: 0px;';
				$css .= '}}';

				return $css;
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
					'content_break_point' => $fusion_settings->get( 'content_break_point' ),
				];
			}


			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'woocommerce-shipping-calculator fusion-woocommerce-shipping-calculator fusion-woocommerce-shipping-calculator-' . $this->counter,
						'style' => '',
					]
				);

				if ( $this->args['class'] ) {
					$attr['class'] .= '  ' . $this->args['class'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/woo-cart-shipping.min.css' );
			}
		}
	}

	new FusionSC_WooCartShipping();

}

/**
 * Map shortcode to Avada Builder.
 */
function fusion_element_woo_cart_shipping() {
	$fusion_settings = awb_get_fusion_settings();
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			fusion_builder_frontend_data(
				'FusionSC_WooCartShipping',
				[
					'name'          => esc_attr__( 'Woo Cart Shipping', 'fusion-builder' ),
					'shortcode'     => 'fusion_woo_cart_shipping',
					'icon'          => 'fusiona-cart-shipping',
					'help_url'      => '',
					'inline_editor' => true,
					'params'        => [
						[
							'type'             => 'dimension',
							'remove_from_atts' => true,
							'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
							'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
							'param_name'       => 'margin',
							'callback'         => [
								'function' => 'fusion_style_block',
								'args'     => [

									'dimension' => true,
								],
							],
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
						'fusion_animation_placeholder' => [
							'preview_selector' => '.fusion-woocommerce-shipping-calculator',
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
						'action'   => 'fusion_get_woo_cart_shipping',
						'ajax'     => true,
					],
				]
			)
		);
	}
}
add_action( 'wp_loaded', 'fusion_element_woo_cart_shipping' );
