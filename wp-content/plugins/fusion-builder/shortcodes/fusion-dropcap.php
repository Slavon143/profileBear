<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_dropcap' ) ) {

	if ( ! class_exists( 'FusionSC_Dropcap' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Dropcap extends Fusion_Element {

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
				add_filter( 'fusion_attr_dropcap-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_dropcap', [ $this, 'render' ] );

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
					'class'        => '',
					'id'           => '',
					'boxed'        => '',
					'boxed_radius' => '',
					'color'        => strtolower( $fusion_settings->get( 'dropcap_color' ) ),
					'text_color'   => strtolower( $fusion_settings->get( 'dropcap_text_color' ) ),
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
					'dropcap_color'      => 'color',
					'dropcap_text_color' => 'text_color',
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

				$using_default_color = false;
				if ( ( isset( $args['color'] ) && '' === $args['color'] ) || ! isset( $args['color'] ) ) {
					$using_default_color = true;
				}

				$defaults                        = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_dropcap' );
				$defaults['using_default_color'] = $using_default_color;
				$content                         = apply_filters( 'fusion_shortcode_content', $content, 'fusion_dropcap', $args );

				$this->args = $defaults;

				$html = '<span ' . FusionBuilder::attributes( 'dropcap-shortcode' ) . '>' . do_shortcode( $content ) . '</span>';

				$this->on_render();

				return apply_filters( 'fusion_element_dropcap_content', $html, $args );

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
					'class' => 'fusion-dropcap dropcap',
					'style' => '',
				];

				if ( 'yes' === $this->args['boxed'] ) {
					$attr['class'] .= ' dropcap-boxed';

					if ( $this->args['boxed_radius'] || '0' === $this->args['boxed_radius'] ) {
						$this->args['boxed_radius'] = ( 'round' === $this->args['boxed_radius'] ) ? '50%' : $this->args['boxed_radius'];
						$attr['style']              = 'border-radius:' . $this->args['boxed_radius'] . ';';
					}

					if ( ! $this->args['using_default_color'] ) {
						$attr['style'] .= 'background-color:' . $this->args['color'] . ';';
						$attr['style'] .= 'color:' . $this->args['text_color'] . ';';
					}
				} elseif ( ! $this->args['using_default_color'] ) {
					$attr['style'] .= 'color:' . $this->args['color'] . ';';
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
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/dropcap.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Dropcap settings.
			 */
			public function add_options() {

				return [
					'dropcap_shortcode_section' => [
						'label'       => esc_html__( 'Dropcap', 'fusion-builder' ),
						'description' => '',
						'id'          => 'dropcap_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-font',
						'fields'      => [
							'dropcap_color'      => [
								'label'       => esc_html__( 'Dropcap Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the dropcap text, or the dropcap box if a box is used.', 'fusion-builder' ),
								'id'          => 'dropcap_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--dropcap_color',
										'element'  => '.fusion-body .fusion-dropcap',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'dropcap_text_color' => [
								'label'       => esc_html__( 'Dropcap Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the dropcap text when a box is used.', 'fusion-builder' ),
								'id'          => 'dropcap_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--dropcap_text_color',
										'element'  => '.fusion-body .fusion-dropcap',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_Dropcap();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_dropcap() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Dropcap',
			[
				'name'           => esc_attr__( 'Dropcap', 'fusion-builder' ),
				'shortcode'      => 'fusion_dropcap',
				'generator_only' => true,
				'icon'           => 'fusiona-font',
				'help_url'       => 'https://theme-fusion.com/documentation/avada/elements/dropcap-element/',
				'params'         => [
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Dropcap Letter', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the letter to be used as dropcap.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => 'A',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the dropcap. Leave blank for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'dropcap_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the dropcap letter when using a box. Leave blank for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'dropcap_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'boxed',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Boxed Dropcap', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get a boxed dropcap.' ),
						'param_name'  => 'boxed',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Box Radius', 'fusion-builder' ),
						'param_name'  => 'boxed_radius',
						'value'       => '',
						'description' => esc_attr__( 'Choose the radius of the boxed dropcap. In pixels (px), ex: 1px, or "round".', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'boxed',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
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
add_action( 'fusion_builder_before_init', 'fusion_element_dropcap' );
