<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 2.1
 */

if ( fusion_is_element_enabled( 'fusion_video' ) ) {

	if ( ! class_exists( 'FusionSC_Video' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 2.1
		 */
		class FusionSC_Video extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 2.1
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 2.1
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_video-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_video-element', [ $this, 'video_attr' ] );
				add_filter( 'fusion_attr_video-wrapper', [ $this, 'wrapper_attr' ] );

				add_shortcode( 'fusion_video', [ $this, 'render' ] );

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

				return [
					'alignment'                  => '',
					'autoplay'                   => 'yes',
					'border_radius_bottom_left'  => '',
					'border_radius_bottom_right' => '',
					'border_radius_top_left'     => '',
					'border_radius_top_right'    => '',
					'box_shadow'                 => 'no',
					'box_shadow_blur'            => '',
					'box_shadow_color'           => '',
					'box_shadow_horizontal'      => '',
					'box_shadow_spread'          => '',
					'box_shadow_vertical'        => '',
					'controls'                   => $fusion_settings->get( 'video_controls' ),
					'class'                      => '',
					'css_id'                     => '',
					'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
					'loop'                       => 'yes',
					'margin_top'                 => '',
					'margin_bottom'              => '',
					'mute'                       => 'yes',
					'overlay_color'              => '',
					'preload'                    => $fusion_settings->get( 'video_preload' ),
					'preview_image'              => '',
					'video'                      => '',
					'video_webm'                 => '',
					'width'                      => $fusion_settings->get( 'video_max_width' ),
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
					'video_max_width' => 'width',
					'video_controls'  => 'controls',
					'video_preload'   => 'preload',
				];
			}

			/**
			 * Render the shortcode.
			 *
			 * @access public
			 * @since 2.1
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_video' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_video', $args );

				$border_radius_top_left     = $defaults['border_radius_top_left'] ? fusion_library()->sanitize->get_value_with_unit( $defaults['border_radius_top_left'] ) : '0px';
				$border_radius_top_right    = $defaults['border_radius_top_right'] ? fusion_library()->sanitize->get_value_with_unit( $defaults['border_radius_top_right'] ) : '0px';
				$border_radius_bottom_right = $defaults['border_radius_bottom_right'] ? fusion_library()->sanitize->get_value_with_unit( $defaults['border_radius_bottom_right'] ) : '0px';
				$border_radius_bottom_left  = $defaults['border_radius_bottom_left'] ? fusion_library()->sanitize->get_value_with_unit( $defaults['border_radius_bottom_left'] ) : '0px';
				$border_radius              = $border_radius_top_left . ' ' . $border_radius_top_right . ' ' . $border_radius_bottom_right . ' ' . $border_radius_bottom_left;
				$border_radius              = ( '0px 0px 0px 0px' === $border_radius ) ? '' : $border_radius;
				$defaults['border_radius']  = $border_radius;

				// Box shadow.
				if ( 'yes' === $defaults['box_shadow'] ) {
					$defaults['box_shadow'] = esc_attr( trim( Fusion_Builder_Box_Shadow_Helper::get_box_shadow_styles( $defaults ) ) );
				}

				$this->args = $defaults;

				$html  = '<div ' . FusionBuilder::attributes( 'video-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'video-wrapper' ) . '>';
				$html .= '<video ' . FusionBuilder::attributes( 'video-element' ) . '>';

				if ( '' !== $this->args['video_webm'] ) {
					$html .= '<source src="' . $this->args['video_webm'] . '" type="video/webm">';
				}

				if ( '' !== $this->args['video'] ) {
					$html .= '<source src="' . $this->args['video'] . '" type="video/mp4">';
				}
				$html .= esc_html__( 'Sorry, your browser doesn\'t support embedded videos.', 'fusion-builder' );
				$html .= '</video>';
				$html .= '</div>';
				$html .= '</div>';

				$this->on_render();

				return apply_filters( 'fusion_element_video_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.1
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-video fusion-selfhosted-video',
						'style' => '',
					]
				);

				if ( '' !== $this->args['alignment'] ) {
					if ( fusion_element_rendering_is_flex() ) {
						// RTL adjust.
						if ( is_rtl() && 'center' !== $this->args['alignment'] ) {
							$this->args['alignment'] = 'left' === $this->args['alignment'] ? 'right' : 'left';
						}

						if ( 'left' === $this->args['alignment'] ) {
							$attr['style'] .= 'align-self:flex-start;';
						} elseif ( 'right' === $this->args['alignment'] ) {
							$attr['style'] .= 'align-self:flex-end;';
						} else {
							$attr['style'] .= 'align-self:center;';
						}
					} else {
						$attr['class'] .= ' fusion-align' . $this->args['alignment'];
					}
				}
				if ( '' !== $this->args['margin_top'] ) {
					$attr['style'] .= 'margin-top:' . fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) . ';';
				}
				if ( '' !== $this->args['margin_bottom'] ) {
					$attr['style'] .= 'margin-bottom:' . fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) . ';';
				}
				if ( $this->args['width'] ) {
					$attr['style'] .= 'max-width:' . $this->args['width'] . ';';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}
				if ( $this->args['css_id'] ) {
					$attr['id'] = $this->args['css_id'];
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.1
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = [
					'class' => 'video-wrapper',
					'style' => '',
				];

				if ( '' !== $this->args['border_radius'] ) {
					$attr['style'] .= 'border-radius:' . $this->args['border_radius'] . ';';
				}

				if ( 'no' !== $this->args['box_shadow'] ) {
					$attr['style'] .= 'box-shadow:' . $this->args['box_shadow'] . ';';
				}

				if ( '' !== $this->args['overlay_color'] ) {
					$alpha = 1;
					if ( class_exists( 'Fusion_Color' ) ) {
						$alpha = Fusion_Color::new_color( $this->args['overlay_color'] )->alpha;
					}
					if ( 1 === $alpha ) {
						$this->args['overlay_color'] = Fusion_Color::new_color( $this->args['overlay_color'] )->get_new( 'alpha', '0.5' )->to_css_var_or_rgba();
					}
					$attr['class'] .= ' fusion-video-overlay';
					$attr['style'] .= 'background-color:' . $this->args['overlay_color'] . ';';
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 2.1
			 * @return array
			 */
			public function video_attr() {

				$attr = [
					'playsinline' => 'true',
					'width'       => '100%',
					'style'       => 'object-fit: cover;',
				];

				if ( 'yes' === $this->args['autoplay'] ) {
					$attr['autoplay'] = 'true';
				}

				if ( 'yes' === $this->args['mute'] ) {
					$attr['muted'] = 'true';
				}

				if ( 'yes' === $this->args['loop'] ) {
					$attr['loop'] = 'true';
				}

				if ( '' !== $this->args['preview_image'] ) {
					$attr['poster'] = $this->args['preview_image'];
				}

				if ( '' !== $this->args['preload'] ) {
					$attr['preload'] = $this->args['preload'];
				}
				if ( 'yes' === $this->args['controls'] ) {
					$attr['controls'] = true;
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/video.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 2.1
			 * @return array $sections Video settings.
			 */
			public function add_options() {
				return [
					'video_shortcode_section' => [
						'label'       => esc_attr__( 'Video', 'fusion-builder' ),
						'description' => '',
						'id'          => 'video_shortcode_section',
						'default'     => '',
						'icon'        => 'fusiona-video',
						'type'        => 'accordion',
						'fields'      => [
							'video_max_width' => [
								'label'       => esc_attr__( 'Maximum Width', 'fusion-builder' ),
								'description' => esc_attr__( 'Set the maximum width using a valid CSS value.', 'fusion-builder' ),
								'id'          => 'video_max_width',
								'default'     => '100%',
								'type'        => 'text',
								'css_vars'    => [
									[
										'name'    => '--fusion-video-max-width-default',
										'element' => 'body',
									],
								],
							],
							'video_controls'  => [
								'label'       => esc_attr__( 'Video Controls', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls whether the video controls should show or not.', 'fusion-builder' ),
								'id'          => 'video_controls',
								'type'        => 'radio-buttonset',
								'default'     => 'yes',
								'choices'     => [
									'yes' => esc_html__( 'Show', 'fusion-builder' ),
									'no'  => esc_html__( 'Hide', 'fusion-builder' ),
								],
							],
							'video_preload'   => [
								'label'       => esc_attr__( 'Video Preloading', 'fusion-builder' ),
								'description' => esc_attr__( 'Controls how / if the browser should preload the video. Choose "Metadata" if only the video metadata should be preloaded on page load or "Auto" to preload the full video on page load.', 'fusion-builder' ),
								'id'          => 'video_preload',
								'type'        => 'radio-buttonset',
								'default'     => 'auto',
								'choices'     => [
									'auto'     => esc_attr__( 'Auto', 'fusion-builder' ),
									'metadata' => esc_attr__( 'Metadata', 'fusion-builder' ),
									'none'     => esc_attr__( 'None', 'fusion-builder' ),
								],
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_Video();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 2.1
 */
function fusion_element_video() {
	if ( ! function_exists( 'fusion_builder_frontend_data' ) ) {
		return;
	}
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Video',
			[
				'name'       => esc_attr__( 'Video', 'fusion-builder' ),
				'shortcode'  => 'fusion_video',
				'icon'       => 'fusiona-video',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-video-preview.php',
				'preview_id' => 'fusion-builder-block-module-video-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/video-element/',
				'params'     => [
					[
						'type'         => 'uploadfile',
						'heading'      => esc_attr__( 'Video MP4 Upload', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add your MP4 video file. This format must be included to render your video with cross-browser compatibility.', 'fusion-builder' ),
						'dynamic_data' => true,
						'param_name'   => 'video',
						'value'        => '',
					],
					[
						'type'         => 'uploadfile',
						'heading'      => esc_attr__( 'Video WebM Upload', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add your WebM video file. This is optional, only MP4 is required to render your video with cross-browser compatibility.', 'fusion-builder' ),
						'dynamic_data' => true,
						'param_name'   => 'video_webm',
						'value'        => '',
					],

					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Video Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the video should take up. Enter value in pixel (px) or percentage (%), ex: 200px. Leave empty to use full video width.', 'fusion-builder' ),
						'param_name'  => 'width',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Video Controls', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls whether the video controls should show or not.', 'fusion-builder' ),
						'param_name'  => 'controls',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Show', 'fusion-builder' ),
							'no'  => esc_attr__( 'Hide', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Video Preloading', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls how / if the browser should preload the video. Choose "Metadata" if only the video metadata should be preloaded on page load (in Chrome needed for the preview image to load) or "Auto" to preload the full video on page load.', 'fusion-builder' ),
						'param_name'  => 'preload',
						'value'       => [
							''         => esc_attr__( 'Default', 'fusion-builder' ),
							'auto'     => esc_attr__( 'Auto', 'fusion-builder' ),
							'metadata' => esc_attr__( 'Metadata', 'fusion-builder' ),
							'none'     => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Loop Video', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls whether the video should loop or not.', 'fusion-builder' ),
						'param_name'  => 'loop',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Autoplay Video', 'fusion-builder' ),
						'description' => __( '<strong>IMPORTANT:</strong> In some modern browsers, videos with sound won\'t be autoplayed.', 'fusion-builder' ),
						'param_name'  => 'autoplay',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Mute Video', 'fusion-builder' ),
						'description' => __( '<strong>IMPORTANT:</strong> In some modern browsers, videos with sound won\'t be autoplayed.', 'fusion-builder' ),
						'param_name'  => 'mute',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],

					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Preview Image', 'fusion-builder' ),
						'description' => __( 'Upload an image to display as a video preview. <strong>IMPORTANT:</strong> In Chrome the preview image will only be displayed if "Video Preloading" is set to "Metadata".', 'fusion-builder' ),
						'param_name'  => 'preview_image',
						'value'       => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Overlay Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the overlay color of the video element.', 'fusion-builder' ),
						'param_name'  => 'overlay_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => '',
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
					'fusion_box_shadow_no_inner_placeholder' => [],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the video's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder' => [
						'param_name'  => 'spacing',
						'description' => esc_attr__( 'Spacing above and below the video. Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
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
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'css_id',
						'value'       => '',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_video' );
