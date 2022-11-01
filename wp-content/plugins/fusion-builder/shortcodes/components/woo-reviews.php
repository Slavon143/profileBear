<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_reviews' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Reviews' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Reviews extends Fusion_Woo_Component {

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
			 * An array of the unmerged shortcode arguments.
			 *
			 * @access protected
			 * @since 3.2
			 * @var array
			 */
			protected $params;

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
				parent::__construct( 'fusion_tb_woo_reviews' );
				add_filter( 'fusion_attr_fusion_tb_woo_reviews-shortcode', [ $this, 'attr' ] );

				// Ajax mechanism for live editor.
				add_action( 'wp_ajax_get_fusion_tb_woo_reviews', [ $this, 'ajax_render' ] );
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
					// Element margin.
					'margin_top'                    => '',
					'margin_right'                  => '',
					'margin_bottom'                 => '',
					'margin_left'                   => '',

					// Heading.
					'show_tab_title'                => 'yes',
					'title_size'                    => 'h2',

					// Borders.
					'border_size'                   => '',
					'border_color'                  => '',

					// Text styling.
					'text_color'                    => '',
					'fusion_font_family_text_font'  => '',
					'fusion_font_variant_text_font' => '',
					'text_font_size'                => '',
					'text_text_transform'           => '',
					'text_line_height'              => '',
					'text_letter_spacing'           => '',

					'stars_color'                   => '',
					'rating_box_bg_color'           => '',
					'rating_box_active_bg_color'    => '',

					// Button styles.
					'button_style'                  => '',
					'button_size'                   => '',
					'button_stretch'                => 'no',
					'button_border_top'             => '',
					'button_border_right'           => '',
					'button_border_bottom'          => '',
					'button_border_left'            => '',
					'button_color'                  => '',
					'button_gradient_top'           => $fusion_settings->get( 'button_gradient_top_color' ),
					'button_gradient_bottom'        => $fusion_settings->get( 'button_gradient_bottom_color' ),
					'button_border_color'           => $fusion_settings->get( 'button_gradient_top_color_hover' ),
					'button_color_hover'            => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
					'button_gradient_top_hover'     => '',
					'button_gradient_bottom_hover'  => '',
					'button_border_color_hover'     => '',

					'hide_on_mobile'                => fusion_builder_default_visibility( 'string' ),
					'class'                         => '',
					'id'                            => '',
					'animation_type'                => '',
					'animation_direction'           => 'down',
					'animation_speed'               => '0.1',
					'animation_offset'              => $fusion_settings->get( 'animation_offset' ),
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
				global $product, $post, $withcomments;
				check_ajax_referer( 'fusion_load_nonce', 'fusion_load_nonce' );

				$live_request = false;

				// From Ajax Request.
				if ( isset( $_POST['model'] ) && isset( $_POST['model']['params'] ) && ! apply_filters( 'fusion_builder_live_request', false ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$defaults     = $_POST['model']['params']; // phpcs:ignore WordPress.Security
					$return_data  = [];
					$live_request = true;
					fusion_set_live_data();
					add_filter( 'fusion_builder_live_request', '__return_true' );
				}

				if ( class_exists( 'Fusion_App' ) && $live_request ) {

					$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : get_the_ID(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
					if ( ( ! $post_id || -99 === $post_id ) || ( isset( $_POST['post_id'] ) && 'fusion_tb_section' === get_post_type( $_POST['post_id'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						echo wp_json_encode( [] );
						wp_die();
					}

					$this->emulate_product();

					if ( ! $this->is_product() ) {
						echo wp_json_encode( $return_data );
						wp_die();
					}

					// Needed in order to bypass early exit in comments_template function.
					$withcomments = true;

					// We need to set global $post because Woo template expects it.
					$post = get_post( $product->get_id() );

					$return_data['woo_reviews'] = $this->get_woo_reviews_content( $defaults, $post_id );
					$this->restore_product();

					// Restore global $post.
					$post = null;
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
				$this->params   = $args;
				$this->defaults = self::get_element_defaults();
				$this->args     = FusionBuilder::set_shortcode_defaults( $this->defaults, $args, 'fusion_tb_woo_reviews' );

				$this->emulate_product();

				if ( ! $this->is_product() ) {
					return;
				}

				// Legacy single border width.
				if ( isset( $args['button_border_width'] ) && ! isset( $args['button_border_top'] ) ) {
					$this->args['button_border_top']    = $args['button_border_width'];
					$this->args['button_border_right']  = $this->args['button_border_top'];
					$this->args['button_border_bottom'] = $this->args['button_border_top'];
					$this->args['button_border_left']   = $this->args['button_border_top'];
				}

				$html  = $this->get_styles();
				$html .= '<div ' . FusionBuilder::attributes( 'fusion_tb_woo_reviews-shortcode' ) . '>' . $this->get_woo_reviews_content( $this->args ) . '</div>';

				$this->restore_product();

				$this->counter++;

				// Remove inline script if in Live Editor.
				if ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) {
					$html = str_replace( [ '<script', '</script>' ], [ '<!--<script', '</script>-->' ], $html );
				}

				$this->on_render();

				return apply_filters( 'fusion_component_' . $this->shortcode_handle, $html, $args );
			}

			/**
			 * Builds HTML for Woo Rating element.
			 *
			 * @static
			 * @access public
			 * @since 3.2
			 * @param array $args The arguments.
			 * @return string
			 */
			public function get_woo_reviews_content( $args ) {
				global $woocommerce, $product;

				$content = '';
				if ( is_object( $product ) ) {
					ob_start();
					comments_template();
					$content = ob_get_clean();
				}

				if ( 'yes' === $args['show_tab_title'] && ! $this->is_default( 'title_size' ) ) {
					$opening_tag = '<' . $this->args['title_size'] . ' class="woocommerce-Reviews-title';
					$closing_tag = '</' . $this->args['title_size'] . '>';
					$count       = 1;
					$content     = str_replace( [ '<h2 class="woocommerce-Reviews-title', '</h2>' ], [ $opening_tag, $closing_tag ], $content, $count );
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
				$this->base_selector = '.fusion-woo-reviews-tb.fusion-woo-reviews-tb-' . $this->counter;
				$this->dynamic_css   = [];

				$sides = [ 'top', 'right', 'bottom', 'left' ];

				foreach ( $sides as $side ) {

					// Element margin.
					$margin_name = 'margin_' . $side;

					if ( '' !== $this->args[ $margin_name ] ) {
						$this->add_css_property( $this->base_selector, 'margin-' . $side, fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_name ] ) );
					}
				}

				// Text styles.
				if ( ! $this->is_default( 'text_color' ) ) {
					$this->add_css_property( $this->base_selector, 'color', $this->args['text_color'] );
					$this->add_css_property( '#wrapper ' . $this->base_selector . ' .meta', 'color', $this->args['text_color'] );
					$this->add_css_property( [ $this->base_selector . ' .stars a', $this->base_selector . ' .stars a:after' ], 'color', $this->args['text_color'] );
				}

				if ( ! $this->is_default( 'text_font_size' ) ) {
					$this->add_css_property( $this->base_selector, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['text_font_size'] ) );
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
					$this->add_css_property( $this->base_selector, $rule, $value );
				}

				// Border.
				if ( ! $this->is_default( 'border_size' ) ) {
					$this->add_css_property( $this->base_selector . ' #reviews li .comment-text', 'border-width', $this->args['border_size'] . 'px' );
				}

				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $this->base_selector . ' #reviews li .comment-text', 'border-color', $this->args['border_color'] );
				}

				if ( ! $this->is_default( 'stars_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .comment-text .star-rating:before', 'color', $this->args['stars_color'] );
					$this->add_css_property( $this->base_selector . ' .comment-text .star-rating span:before', 'color', $this->args['stars_color'] );
				}

				if ( ! $this->is_default( 'rating_box_bg_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .stars > span > a', 'background-color', $this->args['rating_box_bg_color'] );
				}

				if ( ! $this->is_default( 'rating_box_active_bg_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .stars > span > a:hover', 'background-color', $this->args['rating_box_active_bg_color'] );
					$this->add_css_property( $this->base_selector . ' .stars > span > a.active', 'background-color', $this->args['rating_box_active_bg_color'] );
				}

				// Custom add to cart button styling.
				if ( ! $this->is_default( 'button_style' ) ) {

					$button = '.fusion-body ' . $this->base_selector . ' #reviews input#submit.submit';

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
					$border_set = false;
					if ( ! $this->is_default( 'button_border_top' ) ) {
						$border_set = true;
						$this->add_css_property( $button, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_top'] ) );
					}
					if ( ! $this->is_default( 'button_border_right' ) ) {
						$border_set = true;
						$this->add_css_property( $button, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_right'] ) );
					}
					if ( ! $this->is_default( 'button_border_bottom' ) ) {
						$border_set = true;
						$this->add_css_property( $button, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_bottom'] ) );
					}
					if ( ! $this->is_default( 'button_border_left' ) ) {
						$border_set = true;
						$this->add_css_property( $button, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_left'] ) );
					}

					if ( $border_set ) {
						$this->add_css_property( $button, 'border-style', 'solid' );
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
					'class' => 'fusion-woo-reviews-tb fusion-woo-reviews-tb-' . $this->counter,
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( 'no' === $this->args['show_tab_title'] ) {
					$attr['class'] .= ' woo-reviews-hide-heading';
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
				if ( class_exists( 'Avada' ) ) {
					Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-reviews.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-reviews.min.css' );
				}
			}
		}
	}

	new FusionTB_Woo_Reviews();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_reviews() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Reviews',
			[
				'name'                    => esc_attr__( 'Woo Reviews', 'fusion-builder' ),
				'shortcode'               => 'fusion_tb_woo_reviews',
				'icon'                    => 'fusiona-woo-reviews',
				'component'               => true,
				'templates'               => [ 'content' ],
				'components_per_template' => 1,
				'subparam_map'            => [
					'fusion_font_family_text_font'  => 'main_typography',
					'fusion_font_variant_text_font' => 'main_typography',
					'text_font_size'                => 'main_typography',
					'text_text_transform'           => 'main_typography',
					'text_line_height'              => 'main_typography',
					'text_letter_spacing'           => 'main_typography',
					'text_color'                    => 'main_typography',
				],
				'params'                  => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Heading', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have heading displayed.', 'fusion-builder' ),
						'param_name'  => 'show_tab_title',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_reviews',
							'ajax'     => true,
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'HTML Heading Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the heading, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'title_size',
						'value'       => [
							'h1'  => 'H1',
							'h2'  => 'H2',
							'h3'  => 'H3',
							'h4'  => 'H4',
							'h5'  => 'H5',
							'h6'  => 'H6',
							'div' => 'DIV',
						],
						'default'     => 'h2',
						'callback'    => [
							'function' => 'fusion_ajax',
							'action'   => 'get_fusion_tb_woo_reviews',
							'ajax'     => true,
						],
					],
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
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'         => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Content Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the typography of the text. Leave empty for the global font family.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
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
						'type'        => 'range',
						'heading'     => esc_attr__( 'Review Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the review border size. In pixels.', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'value'       => '1',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Review Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the review border color.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => '#f2efef',
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Stars Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of review stars, ex: #000.' ),
						'param_name'  => 'stars_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Rating Box Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the rating box background color, ex: #000.' ),
						'param_name'  => 'rating_box_bg_color',
						'value'       => 'rgba(0,0,0,0.025)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'callback'    => [
							'function' => 'fusion_style_block',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Active Rating Box Background Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the rating box background color when hovering or in active state, ex: #000.' ),
						'param_name'  => 'rating_box_active_bg_color',
						'value'       => 'rgba(0,0,0,0.075)',
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
						'preview_selector' => '.fusion-woo-reviews-tb',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Submit Review Button Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select whether you want to custom style the submit review button.', 'fusion-builder' ),
						'param_name'  => 'button_style',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
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
						'type'             => 'subgroup',
						'heading'          => esc_html__( 'Button Styling', 'fusion-builder' ),
						'description'      => esc_html__( 'Use filters to see specific type of content.', 'fusion-builder' ),
						'param_name'       => 'button_styling',
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
				],
				'callback'                => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_reviews',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_reviews' );
