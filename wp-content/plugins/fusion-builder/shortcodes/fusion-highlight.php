<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_highlight' ) ) {

	if ( ! class_exists( 'FusionSC_Highlight' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Highlight extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_highlight-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_highlight', [ $this, 'render' ] );
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
					'background'              => 'yes',
					'background_style'        => 'full',
					'class'                   => '',
					'id'                      => '',
					'color'                   => $fusion_settings->get( 'primary_color' ),
					'text_color'              => '',
					'rounded'                 => 'no',
					'gradient_font'           => 'no',
					'gradient_start_color'    => '',
					'gradient_end_color'      => '',
					'gradient_start_position' => '0',
					'gradient_end_position'   => '100',
					'gradient_type'           => 'linear',
					'radial_direction'        => 'center center',
					'linear_angle'            => '180',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'primary_color' => 'color',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_highlight' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_highlight', $args );

				$this->args = $defaults;

				$html = '<span ' . FusionBuilder::attributes( 'highlight-shortcode' ) . '>' . do_shortcode( $content ) . '</span>';

				$this->on_render();

				return apply_filters( 'fusion_element_highlight_content', $html, $args );

			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/highlight.min.css' );
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'fusion-highlight',
					'style' => '',
				];

				if ( $this->args['text_color'] ) {
					$attr['class'] .= ' custom-textcolor';
				} else {
					$brightness_level = Fusion_Color::new_color( $this->args['color'] )->brightness;
					$attr['class']   .= ( $brightness_level['total'] > 140 ) ? ' light' : ' dark';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( 'black' === $this->args['color'] ) {
					$attr['class'] .= ' highlight2';
				} else {
					$attr['class'] .= ' highlight1';
				}

				if ( 'no' !== $this->args['background'] ) {
					if ( 'full' === $this->args['background_style'] ) {
						$attr['class'] .= ' awb-highlight-background';
						$attr['style'] .= 'background-color:' . $this->args['color'] . ';';

						if ( 'yes' === $this->args['rounded'] ) {
							$attr['class'] .= ' rounded';
						}
					} else {
						$attr['style'] .= 'background:linear-gradient(to top, ' . $this->args['color'] . ' 40%, transparent 40%);';
					}
				} elseif ( 'yes' === $this->args['gradient_font'] ) {
					$attr['style'] .= Fusion_Builder_Gradient_Helper::get_gradient_font_string( $this->args );
					$attr['class'] .= ' awb-gradient-text';
				}

				if ( $this->args['text_color'] ) {
					$attr['style'] .= 'color:' . $this->args['text_color'] . ';';
				}

				return $attr;
			}
		}
	}

	new FusionSC_Highlight();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_highlight() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Highlight',
			[
				'name'           => esc_attr__( 'Highlight', 'fusion-builder' ),
				'shortcode'      => 'fusion_highlight',
				'icon'           => 'fusiona-H',
				'generator_only' => true,
				'help_url'       => 'https://theme-fusion.com/documentation/avada/elements/highlight-element/',
				'params'         => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Background', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if you would like a highlight background or not.', 'fusion-builder' ),
						'param_name'  => 'background',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Background Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the background highlight style.', 'fusion-builder' ),
						'param_name'  => 'background_style',
						'value'       => [
							'full'         => esc_attr__( 'Full', 'fusion-builder' ),
							'marker_style' => esc_attr__( 'Marker Style', 'fusion-builder' ),
						],
						'default'     => 'full',
						'dependency'  => [
							[
								'element'  => 'background',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Pick a highlight background color.', 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'dependency'  => [
							[
								'element'  => 'background',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Rounded Corners', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have rounded corners.', 'fusion-builder' ),
						'param_name'  => 'rounded',
						'value'       => [
							'yes' => __( 'Yes', 'fusion-builder' ),
							'no'  => __( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'background',
								'value'    => 'yes',
								'operator' => '==',
							],
							[
								'element'  => 'background_style',
								'value'    => 'full',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Font Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Pick a text-color for your highlight. Leave empty to use an auto-calculated value.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => '',
					],
					'fusion_gradient_text_placeholder' => [
						'selector'   => '.fusion-highlight',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'dependency' => [
							[
								'element'  => 'background',
								'value'    => 'yes',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some text to highlight.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_highlight' );
