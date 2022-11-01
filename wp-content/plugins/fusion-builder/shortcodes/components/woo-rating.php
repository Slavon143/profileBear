<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_rating' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Rating' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Rating extends Fusion_Woo_Component {

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
				parent::__construct( 'fusion_tb_woo_rating' );
				add_filter( 'fusion_attr_fusion_tb_woo_rating-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_rating', [ $this, 'ajax_render' ] );
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
					'show_count'          => 'yes',
					'placeholder'         => 'yes',
					'alignment'           => '',
					'icon_size'           => '',
					'icon_color'          => $fusion_settings->get( 'primary_color' ),
					'count_font_size'     => '',
					'count_color'         => $fusion_settings->get( 'body_typography', 'color' ),
					'count_hover_color'   => $fusion_settings->get( 'primary_color' ),
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'class'               => '',
					'id'                  => '',
					'animation_type'      => '',
					'animation_direction' => 'down',
					'animation_speed'     => '0.1',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Render for live editor.
			 *
			 * @static
			 * @access public
			 * @since 3.2
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
					$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_rating' );

					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );

					$this->emulate_product();

					if ( ! $this->is_product() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					$return_data['woo_rating'] = $this->get_woo_rating_content();
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
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_rating' );

				$this->args['icon_size']       = FusionBuilder::validate_shortcode_attr_value( $this->args['icon_size'], 'px' );
				$this->args['count_font_size'] = FusionBuilder::validate_shortcode_attr_value( $this->args['count_font_size'], 'px' );

				$this->emulate_product();

				if ( ! $this->is_product() ) {
					return;
				}

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_rating-shortcode' ) . '>' . $this->get_woo_rating_content() . '</div>';

				$this->restore_product();

				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle . '_content', $html, $args );
			}

			/**
			 * Builds HTML for Woo Rating element.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_woo_rating_content() {
				global $product;
				$content    = '';
				$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );

				if ( function_exists( 'woocommerce_template_single_rating' ) && is_object( $product ) && $product->get_rating_count() > 0 ) {
					ob_start();
					woocommerce_template_single_rating();
					$content .= ob_get_clean();
				} elseif ( comments_open() && ! $is_builder && 'no' !== $this->args['placeholder'] && ! apply_filters( 'fusion_builder_live_request', false ) ) {
					$content = $content .= '<div class="woocommerce-product-rating"><a href="#reviews" class="woocommerce-review-link fusion-no-rating" rel="nofollow">' . __( 'Be the first to leave a review.', 'fusion-builder' ) . '</a></div>';
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
				$this->base_selector = '.fusion-woo-rating-tb.fusion-woo-rating-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$selectors = [
					$this->base_selector . ' .woocommerce-product-rating .star-rating',
				];

				if ( ! $this->is_default( 'icon_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['icon_size'] );
				}

				$selectors = [
					$this->base_selector . ' .woocommerce-product-rating .star-rating:before',
					$this->base_selector . ' .woocommerce-product-rating .star-rating span:before',
				];

				if ( ! $this->is_default( 'icon_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['icon_color'] );
				}

				$selectors = [
					$this->base_selector . ' .woocommerce-product-rating a.woocommerce-review-link',
				];

				if ( ! $this->is_default( 'count_font_size' ) ) {
					$this->add_css_property( $selectors, 'font-size', $this->args['count_font_size'] );
				}

				if ( ! $this->is_default( 'count_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['count_color'] );
				}

				$selectors = [
					$this->base_selector . ' .woocommerce-product-rating a.woocommerce-review-link:hover',
				];

				if ( ! $this->is_default( 'count_hover_color' ) ) {
					$this->add_css_property( $selectors, 'color', $this->args['count_hover_color'] );
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
					'class' => 'fusion-woo-rating-tb fusion-woo-rating-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( 'yes' !== $this->args['show_count'] ) {
					$attr['class'] .= ' hide-count';
				}

				if ( '' !== $this->args['alignment'] ) {
					$attr['class'] .= ' align-' . $this->args['alignment'];
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/components/woo-rating.min.css' );
			}
		}
	}

	new FusionTB_Woo_Rating();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_rating() {

	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Rating',
			[
				'name'      => esc_attr__( 'Woo Rating', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_rating',
				'icon'      => 'fusiona-woo-rating',
				'component' => true,
				'templates' => [ 'content', 'post_cards', 'page_title_bar' ],
				'params'    => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Reviews Count', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide reviews count.', 'fusion-builder' ),
						'param_name'  => 'show_count',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Placeholder Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection to show or hide placeholder text if no review is added yet.', 'fusion-builder' ),
						'param_name'  => 'placeholder',
						'default'     => 'yes',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Make a selection for content alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
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
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Icon Size', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'description' => esc_html__( 'Controls the size of the icon. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Icon Color', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'description' => esc_html__( 'Choose icon color for rating.', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_html__( 'Reviews Count Font Size', 'fusion-builder' ),
						'param_name'  => 'count_font_size',
						'description' => esc_html__( 'Controls the size of the reviews count text. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_count',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Reviews Count Text Color', 'fusion-builder' ),
						'param_name'  => 'count_color',
						'value'       => '',
						'description' => esc_html__( 'Choose color for reviews count text.', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'body_typography', 'color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'show_count',
								'value'    => 'no',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_html__( 'Reviews Count Hover Text Color', 'fusion-builder' ),
						'param_name'  => 'count_hover_color',
						'value'       => '',
						'description' => esc_html__( 'Choose color for reviews count hover text.', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
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
						'preview_selector' => '.fusion-woo-rating-tb',
					],
				],
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_rating',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_rating' );
