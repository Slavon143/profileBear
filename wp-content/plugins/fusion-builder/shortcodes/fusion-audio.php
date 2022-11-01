<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.1
 */

if ( fusion_is_element_enabled( 'fusion_audio' ) && ! class_exists( 'FusionSC_Audio' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 2.1
	 */
	class FusionSC_Audio extends Fusion_Element {

		/**
		 * An array of the shortcode arguments.
		 *
		 * @access protected
		 * @since 2.1
		 * @var array
		 */
		protected $args;

		/**
		 * The internal container counter.
		 *
		 * @access private
		 * @since 1.3
		 * @var int
		 */
		private $counter = 1;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 2.1
		 */
		public function __construct() {
			parent::__construct();
			add_filter( 'fusion_attr_audio-shortcode', [ $this, 'attr' ] );

			add_shortcode( 'fusion_audio', [ $this, 'render' ] );

		}

		/**
		 * Gets the default values.
		 *
		 * @static
		 * @access public
		 * @since 2.1
		 * @return array
		 */
		public static function get_element_defaults() {
			$fusion_settings = awb_get_fusion_settings();
			$border_radius   = Fusion_Builder_Border_Radius_Helper::get_border_radius_array_with_fallback_value( $fusion_settings->get( 'audio_border_radius' ) );

			return [
				'animation_type'             => '',
				'animation_direction'        => 'down',
				'animation_speed'            => '',
				'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
				'margin_bottom'              => '',
				'margin_left'                => '',
				'margin_right'               => '',
				'margin_top'                 => '',
				'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
				'class'                      => '',
				'id'                         => '',
				'src'                        => '',
				'loop'                       => 'off',
				'autoplay'                   => 'off',
				'preload'                    => 'none',
				'background_color'           => $fusion_settings->get( 'audio_background_color' ),
				'progress_color'             => $fusion_settings->get( 'audio_progressbar_color' ),
				'controls_color_scheme'      => $fusion_settings->get( 'audio_controls_color_scheme' ),
				'border_size'                => $fusion_settings->get( 'audio_border_size' ),
				'border_color'               => $fusion_settings->get( 'audio_border_color' ),
				'border_radius_top_left'     => $border_radius['top_left'],
				'border_radius_top_right'    => $border_radius['top_right'],
				'border_radius_bottom_right' => $border_radius['bottom_right'],
				'border_radius_bottom_left'  => $border_radius['bottom_left'],
				'max_width'                  => $fusion_settings->get( 'audio_max_width' ),
				'box_shadow'                 => 'no',
				'box_shadow_blur'            => '',
				'box_shadow_color'           => '',
				'box_shadow_horizontal'      => '',
				'box_shadow_spread'          => '',
				'box_shadow_vertical'        => '',
			];
		}

		/**
		 * Maps settings to param variables.
		 *
		 * @static
		 * @access public
		 * @since 2.1
		 * @return array
		 */
		public static function settings_to_params() {
			return [
				'audio_background_color'            => 'background_color',
				'audio_progressbar_color'           => 'progress_color',
				'audio_controls_color_scheme'       => 'controls_color_scheme',
				'audio_border_size'                 => 'border_size',
				'audio_border_color'                => 'border_color',
				'audio_border_radius[top_left]'     => 'border_radius_top_left',
				'audio_border_radius[top_right]'    => 'border_radius_top_right',
				'audio_border_radius[bottom_right]' => 'border_radius_bottom_right',
				'audio_border_radius[bottom_left]'  => 'border_radius_bottom_left',
				'audio_max_width'                   => 'max_width',
			];
		}

		/**
		 * Render the shortcode
		 *
		 * @access public
		 * @since 2.1
		 * @param  array  $args    Shortcode parameters.
		 * @param  string $content Content between shortcode.
		 * @return string          HTML output.
		 */
		public function render( $args, $content = '' ) {

			$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_audio' );

			$border_radius               = $this->args['border_radius_top_left'] . ' ' . $this->args['border_radius_top_right'] . ' ' . $this->args['border_radius_bottom_right'] . ' ' . $this->args['border_radius_bottom_left'];
			$this->args['border_radius'] = ( '0px 0px 0px 0px' === $border_radius ) ? '' : $border_radius;

			$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
			$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
			$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
			$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

			$html = '<div ' . FusionBuilder::attributes( 'audio-shortcode' ) . '>';

			$sc_params = '';

			foreach ( [ 'src', 'loop', 'autoplay', 'preload' ] as $arg ) {
				if ( 'none' !== $this->args[ $arg ] && 'off' !== $this->args[ $arg ] ) {
					$sc_params .= ' ' . $arg . '="' . $this->args[ $arg ] . '"';
				}
			}

			$html .= do_shortcode( "[audio{$sc_params}]" );
			$html .= '</div>';

			$this->counter++;

			$this->on_render();

			return apply_filters( 'fusion_element_audio_content', $html, $args );
		}

		/**
		 * Builds the attributes array.
		 *
		 * @access public
		 * @since 2.1
		 * @return array
		 */
		public function attr() {

			$attr = [
				'class' => 'fusion-audio fusion-audio-' . $this->counter,
				'style' => '',
			];

			if ( $this->args['progress_color'] ) {
				$style = '--fusion-audio-accent-color:' . $this->args['progress_color'] . ';';
			}
			if ( '' !== $this->args['border_size'] ) {
				$style .= '--fusion-audio-border-size:' . $this->args['border_size'] . ';';
			}
			if ( $this->args['border_color'] ) {
				$style .= '--fusion-audio-border-color:' . $this->args['border_color'] . ';';
			}

			$corners = [ 'top_left', 'top_right', 'bottom_right', 'bottom_left' ];
			foreach ( $corners as $corner ) {
				if ( $this->args[ 'border_radius_' . $corner ] ) {
					$style .= '--fusion-audio-border-' . str_replace( '_', '-', $corner ) . '-radius:' . $this->args[ 'border_radius_' . $corner ] . ';';
				}
			}

			if ( $this->args['background_color'] ) {
				$style .= '--fusion-audio-background-color:' . $this->args['background_color'] . ';';
			}
			if ( $this->args['max_width'] ) {
				$style .= '--fusion-audio-max-width:' . $this->args['max_width'] . ';';
			}

			// Box shadow.
			if ( 'yes' === $this->args['box_shadow'] ) {
				$style .= '--fusion-audio-box-shadow:' . Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles( $this->args );
			}

			$style .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

			$attr['style'] = $style;

			if ( 'dark' === $this->args['controls_color_scheme'] ) {
				$attr['class'] .= ' dark-controls';
			}

			$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

			if ( $this->args['animation_type'] ) {
				$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
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
		 * Sets the necessary scripts.
		 *
		 * @access public
		 * @since 2.1
		 * @return void
		 */
		public function add_scripts() {

			Fusion_Dynamic_CSS::add_replace_pattern(
				'FUSION_AUDIO_SVG_URL',
				FUSION_BUILDER_PLUGIN_URL . 'assets/images/mejs-controls-dark.svg'
			);
		}

		/**
		 * Load base CSS.
		 *
		 * @access public
		 * @since 3.0
		 * @return void
		 */
		public function add_css_files() {
			FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/audio.min.css' );
		}

		/**
		 * Adds settings to element options panel.
		 *
		 * @access public
		 * @since 2.1
		 * @return array $sections Blog settings.
		 */
		public function add_options() {
			return [
				'audio_shortcode_section' => [
					'label'       => esc_attr__( 'Audio', 'fusion-builder' ),
					'description' => '',
					'id'          => 'audio_shortcode_section',
					'default'     => '',
					'icon'        => 'fusiona-audio',
					'type'        => 'accordion',
					'fields'      => [
						'audio_max_width'             => [
							'label'       => esc_attr__( 'Maximum Width', 'fusion-builder' ),
							'description' => esc_attr__( 'Set the maximum width using a valid CSS value.', 'fusion-builder' ),
							'id'          => 'audio_max_width',
							'default'     => '100%',
							'type'        => 'text',
							'css_vars'    => [
								[
									'name'    => '--fusion-audio-max-width-default',
									'element' => 'body',
								],
							],
						],
						'audio_background_color'      => [
							'label'       => esc_attr__( 'Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the background color for the audio player.', 'fusion-builder' ),
							'id'          => 'audio_background_color',
							'default'     => 'var(--awb-color8)',
							'type'        => 'color-alpha',
							'css_vars'    => [
								[
									'name'     => '--fusion-audio-background-color-default',
									'element'  => 'body',
									'callback' => [ 'sanitize_color' ],
								],
							],
						],
						'audio_progressbar_color'     => [
							'label'       => esc_attr__( 'Audio Progress Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Select a color for the audio progress-bar.', 'fusion-builder' ),
							'id'          => 'audio_progressbar_color',
							'default'     => 'var(--awb-color1)',
							'type'        => 'color-alpha',
							'css_vars'    => [
								[
									'name'     => '--fusion-audio-accent-color-default',
									'element'  => 'body',
									'callback' => [ 'sanitize_color' ],
								],
							],
						],
						'audio_controls_color_scheme' => [
							'label'       => esc_attr__( 'Controls Color Scheme', 'fusion-builder' ),
							'description' => esc_attr__( 'Depending on the background color you can change this value to "Light" or "Dark" to ensure controls are visible.', 'fusion-builder' ),
							'id'          => 'audio_controls_color_scheme',
							'type'        => 'radio-buttonset',
							'default'     => 'light',
							'choices'     => [
								'light' => esc_html__( 'Light', 'fusion-builder' ),
								'dark'  => esc_html__( 'Dark', 'fusion-builder' ),
							],
						],
						'audio_border_size'           => [
							'type'        => 'slider',
							'label'       => esc_attr__( 'Border Size', 'fusion-builder' ),
							'id'          => 'audio_border_size',
							'default'     => 0,
							'description' => esc_attr__( 'Set the border size.', 'fusion-builder' ),
							'choices'     => [
								'min'  => '0',
								'max'  => '10',
								'step' => '1',
							],
							'css_vars'    => [
								[
									'name'    => '--fusion-audio-border-size-default',
									'element' => 'body',
								],
							],
						],
						'audio_border_color'          => [
							'type'        => 'color-alpha',
							'label'       => esc_attr__( 'Border Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the border color for the audio player.', 'fusion-builder' ),
							'id'          => 'audio_border_color',
							'group'       => esc_attr__( 'Design', 'fusion-builder' ),
							'default'     => 'var(--awb-color1)',
							'css_vars'    => [
								[
									'name'     => '--fusion-audio-border-color-default',
									'element'  => 'body',
									'callback' => [ 'sanitize_color' ],
								],
							],
						],
						'audio_border_radius'         => [
							'label'       => esc_attr__( 'Border Radius', 'fusion-builder' ),
							'description' => esc_html__( 'Set the border radius.', 'fusion-builder' ),
							'id'          => 'audio_border_radius',
							'choices'     => [
								'top_left'     => true,
								'top_right'    => true,
								'bottom_right' => true,
								'bottom_left'  => true,
								'units'        => [ 'px', '%', 'em' ],
							],
							'default'     => [
								'top_left'     => '0px',
								'top_right'    => '0px',
								'bottom_right' => '0px',
								'bottom_left'  => '0px',
							],
							'type'        => 'border_radius',
							'css_vars'    => [
								[
									'name'    => '--fusion-audio-border-top-left-radius-default',
									'choice'  => 'top_left',
									'element' => 'body',
								],
								[
									'name'    => '--fusion-audio-border-top-right-radius-default',
									'choice'  => 'top_right',
									'element' => 'body',
								],
								[
									'name'    => '--fusion-audio-border-bottom-right-radius-default',
									'choice'  => 'bottom_right',
									'element' => 'body',
								],
								[
									'name'    => '--fusion-audio-border-bottom-left-radius-default',
									'choice'  => 'bottom_left',
									'element' => 'body',
								],
							],

							// Could update variable here, but does not look necessary as set inline.
							'transport'   => 'postMessage',
						],
					],
				],
			];
		}
	}

	new FusionSC_Audio();
}


/**
 * Map shortcode to Avada Builder
 *
 * @since 2.1
 */
function fusion_element_audio() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Audio',
			[
				'name'                     => esc_attr__( 'Audio', 'fusion-builder' ),
				'shortcode'                => 'fusion_audio',
				'icon'                     => 'fusiona-audio',
				'allow_generator'          => false,
				'inline_editor'            => false,
				'inline_editor_shortcodes' => false,
				'help_url'                 => 'https://theme-fusion.com/documentation/avada/elements/audio-element/',
				'params'                   => [
					[
						'type'        => 'uploadfile',
						'heading'     => esc_attr__( 'Audio', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an audio file.', 'fusion-builder' ),
						'param_name'  => 'src',
						'value'       => '',
						'data_type'   => 'audio',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Loop', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to loop the media.', 'fusion-builder' ),
						'param_name'  => 'loop',
						'default'     => 'off',
						'value'       => [
							'on'  => esc_html__( 'On', 'fusion-builder' ),
							'off' => esc_html__( 'Off', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Autoplay', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to autoplay the media.', 'fusion-builder' ),
						'param_name'  => 'autoplay',
						'default'     => 'off',
						'value'       => [
							'on'  => esc_html__( 'On', 'fusion-builder' ),
							'off' => esc_html__( 'Off', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Preload', 'fusion-builder' ),
						'description' => esc_html__( 'Specifies if and how the audio should be loaded when the page loads. Defaults to "None".', 'fusion-builder' ) . '<br>' . esc_attr__( '• "None": The audio should not be loaded when the page loads.', 'fusion-builder' ) . '<br>' . esc_html__( '• "Auto": The audio should be loaded entirely when the page loads.', 'fusion-builder' ) . '<br>' . esc_html__( '• "Metadata": Only metadata should be loaded when the page loads..', 'fusion-builder' ),
						'param_name'  => 'preload',
						'default'     => 'none',
						'value'       => [
							'auto'     => esc_html__( 'Auto', 'fusion-builder' ),
							'metadata' => esc_html__( 'Metadata', 'fusion-builder' ),
							'none'     => esc_html__( 'None', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color for the audio player.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'audio_background_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Controls Color Scheme', 'fusion-builder' ),
						'description' => esc_attr__( 'Depending on the background color you can change this value to "Light" or "Dark" to ensure controls are visible.', 'fusion-builder' ),
						'param_name'  => 'controls_color_scheme',
						'default'     => '',
						'value'       => [
							''      => esc_html__( 'Default', 'fusion-builder' ),
							'light' => esc_html__( 'Light', 'fusion-builder' ),
							'dark'  => esc_html__( 'Dark', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Audio Progress Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a color for the audio progress-bar.', 'fusion-builder' ),
						'param_name'  => 'progress_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'audio_progressbar_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Maximum Width', 'fusion-builder' ),
						'param_name'  => 'max_width',
						'default'     => '100%',
						'description' => esc_attr__( 'Set the maximum width using a valid CSS value.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'default'     => $fusion_settings->get( 'audio_border_size' ),
						'description' => esc_attr__( 'Set the border size. In pixels.', 'fusion-builder' ),
						'min'         => '0',
						'max'         => '10',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color for the audio player.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'audio_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					'fusion_border_radius_placeholder' => [],
					'fusion_box_shadow_no_inner_placeholder' => [],
					'fusion_animation_placeholder'     => [
						'preview_selector' => '.fusion-audio',
					],
					'fusion_margin_placeholder'        => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
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
add_action( 'fusion_builder_before_init', 'fusion_element_audio' );
