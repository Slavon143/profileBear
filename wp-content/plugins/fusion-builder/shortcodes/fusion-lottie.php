<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.0.2
 */

if ( fusion_is_element_enabled( 'fusion_lottie' ) ) {

	if ( ! class_exists( 'FusionSC_Lottie' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.0.2
		 */
		class FusionSC_Lottie extends Fusion_Element {

			/**
			 * The lottie counter.
			 *
			 * @access private
			 * @since 3.0.2
			 * @var int
			 */
			private $counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 3.0.2
			 * @var array
			 */
			protected $args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.0.2
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_lottie-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_lottie-wrapper', [ $this, 'wrapper_attr' ] );

				add_shortcode( 'fusion_lottie', [ $this, 'render' ] );

				add_filter( 'upload_mimes', [ $this, 'allow_json' ] );
				add_filter( 'wp_check_filetype_and_ext', [ $this, 'correct_json_filetype' ], 10, 5 );
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 3.0.2
			 * @return array
			 */
			public static function get_element_defaults() {
				$fusion_settings = awb_get_fusion_settings();
				return [
					'align'                   => '',
					'align_medium'            => '',
					'align_small'             => '',
					'animation_direction'     => 'left',
					'animation_offset'        => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'         => '',
					'animation_type'          => '',
					'class'                   => '',
					'filter_blur'             => '0',
					'filter_blur_hover'       => '0',
					'filter_brightness'       => '100',
					'filter_brightness_hover' => '100',
					'filter_contrast'         => '100',
					'filter_contrast_hover'   => '100',
					'filter_hue'              => '0',
					'filter_hue_hover'        => '0',
					'filter_invert'           => '0',
					'filter_invert_hover'     => '0',
					'filter_opacity'          => '100',
					'filter_opacity_hover'    => '100',
					'filter_saturation'       => '100',
					'filter_saturation_hover' => '100',
					'filter_sepia'            => '0',
					'filter_sepia_hover'      => '0',
					'hide_on_mobile'          => fusion_builder_default_visibility( 'string' ),
					'id'                      => '',
					'json'                    => FUSION_BUILDER_PLUGIN_URL . 'assets/images/avada-icon-animated.json',
					'link'                    => '',
					'link_attributes'         => '',
					'target'                  => '_self',
					'loop'                    => 'yes',
					'margin_bottom'           => '',
					'margin_left'             => '',
					'margin_right'            => '',
					'margin_top'              => '',
					'max_width'               => '',
					'reverse'                 => 'no',
					'speed'                   => '1',
					'trigger'                 => 'none',
					'trigger_offset'          => $fusion_settings->get( 'animation_offset' ),
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 3.1
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'animation_offset' => 'animation_offset',
				];
			}

			/**
			 * Allow JSON upload.
			 *
			 * @access public
			 * @since 3.0.2
			 * @param  array $mimes Mimes allowed.
			 * @return array  Mimes to allow.
			 */
			public function allow_json( $mimes ) {
				$mimes['json'] = 'application/json';
				return $mimes;
			}

			/**
			 * Correct JSON file uploads to make them pass the WP check.
			 *
			 * WP upload validation relies on the fileinfo PHP extension, which causes inconsistencies.
			 * E.g. json file type is application/json but is reported as text/plain.
			 * ref: https://core.trac.wordpress.org/ticket/45633
			 *
			 * @access public
			 * @since 3.1
			 * @param array       $data                      Values for the extension, mime type, and corrected filename.
			 * @param string      $file                      Full path to the file.
			 * @param string      $filename                  The name of the file (may differ from $file due to
			 *                                               $file being in a tmp directory).
			 * @param string[]    $mimes                     Array of mime types keyed by their file extension regex.
			 * @param string|bool $real_mime                 The actual mime type or false if the type cannot be determined.
			 *
			 * @return array
			 */
			public function correct_json_filetype( $data, $file, $filename, $mimes, $real_mime = false ) {

				// If both ext and type are.
				if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
					return $data;
				}

				$wp_file_type = wp_check_filetype( $filename, $mimes );

				if ( 'json' === $wp_file_type['ext'] ) {
					$data['ext']  = 'json';
					$data['type'] = 'application/json';
				}

				return $data;
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 3.0.2
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {
				$this->set_element_id( $this->counter );

				$defaults   = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_lottie' );
				$this->args = $defaults;

				$tag  = '' !== $this->args['link'] ? 'a' : 'div';
				$html = '<' . $tag . ' ' . FusionBuilder::attributes( 'lottie-shortcode' ) . '></' . $tag . '>';

				// Add filter styles.
				$filter_style = Fusion_Builder_Filter_Helper::get_filter_style_element( $this->args, '.fusion-lottie-' . $this->element_id );
				if ( '' !== $filter_style ) {
					$html .= $filter_style;
				}

				$html = '<div ' . FusionBuilder::attributes( 'lottie-wrapper' ) . '>' . $html . '</div>';
				$this->counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_lottie_content', $html, $args );

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.0.2
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'fusion-lottie-animation',
				];

				if ( '' !== $this->args['json'] ) {
					$attr['data-path']    = $this->args['json'];
					$attr['data-loop']    = 'yes' === $this->args['loop'] ? 1 : 0;
					$attr['data-reverse'] = 'yes' === $this->args['reverse'] ? 1 : 0;
					$attr['data-speed']   = $this->args['speed'];
					$attr['data-trigger'] = $this->args['trigger'];
					if ( 'viewport' === $this->args['trigger'] ) {
						$attr['data-animationoffset'] = $this->args['trigger_offset'];
					}
				}

				if ( $this->args['max_width'] ) {
					$attr['style'] = 'width:100%;max-width:' . $this->args['max_width'] . ';';

					if ( '' !== $this->args['link'] ) {
						$attr['style'] .= 'display:block;';
					}
				}

				// Link if set.
				if ( '' !== $this->args['link'] ) {
					$attr['href']   = esc_attr( $this->args['link'] );
					$attr['target'] = $this->args['target'];
					if ( '_blank' === $this->args['target'] ) {
						$attr['rel'] = 'noopener noreferrer';
					}

					// Add additional, custom link attributes correctly formatted to the anchor.
					$attr = fusion_get_link_attributes( $this->args, $attr );
				}

				$align_classes = [
					'center' => 'mx-auto',
					'left'   => 'mr-auto',
					'right'  => 'ml-auto',
				];

				$align_large = ! empty( $this->args['align'] ) && 'none' !== $this->args['align'] ? $this->args['align'] : false;
				if ( $align_large ) {
					$attr['class'] .= ' lg-' . $align_classes[ $this->args['align'] ];
				}

				$align_medium = ! empty( $this->args['align_medium'] ) && 'none' !== $this->args['align_medium'] ? $this->args['align_medium'] : false;
				if ( $align_medium ) {
					$attr['class'] .= ' md-' . $align_classes[ $this->args['align_medium'] ];
				}

				$align_small = ! empty( $this->args['align_small'] ) && 'none' !== $this->args['align_small'] ? $this->args['align_small'] : false;
				if ( $align_small ) {
					$attr['class'] .= ' sm-' . $align_classes[ $this->args['align_small'] ];
				}

				return $attr;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.0.2
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = [
					'class'   => 'fusion-lottie fusion-lottie-' . $this->element_id,
					'data-id' => $this->element_id,
					'style'   => '',
				];

				// Hide on mobile.
				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}
				if ( '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				return $attr;
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'lottie',
					FusionBuilder::$js_folder_url . '/library/lottie.js',
					FusionBuilder::$js_folder_path . '/library/lottie.js',
					[],
					'5.7.1',
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-lottie',
					FusionBuilder::$js_folder_url . '/general/fusion-lottie.js',
					FusionBuilder::$js_folder_path . '/general/fusion-lottie.js',
					[ 'jquery', 'lottie' ],
					'1',
					true
				);
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0.2
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/lottie.min.css' );
			}
		}
	}

	new FusionSC_Lottie();
}


/**
 * Map shortcode to Avada Builder
 *
 * @since 3.0.2
 */
function fusion_element_lottie() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Lottie',
			[
				'name'      => esc_attr__( 'Lottie Animation', 'fusion-builder' ),
				'shortcode' => 'fusion_lottie',
				'icon'      => 'fusiona-lottie',
				'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/lottie-animation-element/',
				'params'    => [
					[
						'type'        => 'uploadfile',
						'heading'     => esc_attr__( 'JSON Upload', 'fusion-builder' ),
						/* translators: Link to https://lottiefiles.com/ site. */
						'description' => sprintf( __( 'Upload a lottie JSON file. Visit %s to find animations.', 'fusion-builder' ), '<a href="https://lottiefiles.com/" target="_blank" rel="noopener noreferrer">LottieFiles</a>' ),
						'param_name'  => 'json',
						'value'       => '',
						'data_type'   => 'application/json',
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Link URL', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the URL the animation will link to, ex: http://example.com.', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window<br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'target',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => '_self',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Trigger', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the trigger for the animation to play.', 'fusion-builder' ),
						'param_name'  => 'trigger',
						'default'     => 'none',
						'value'       => [
							'none'     => esc_attr__( 'None', 'fusion-builder' ),
							'viewport' => esc_attr__( 'Viewport', 'fusion-builder' ),
							'hover'    => esc_attr__( 'Hover', 'fusion-builder' ),
							'click'    => esc_attr__( 'Click', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Trigger Offset', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls when the animation should play.', 'fusion-builder' ),
						'param_name'  => 'trigger_offset',
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'trigger',
								'value'    => 'viewport',
								'operator' => '==',
							],
						],
						'value'       => [
							''                => esc_attr__( 'Default', 'fusion-builder' ),
							'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
							'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-builder' ),
							'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Loop', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls whether the animation should loop or not.', 'fusion-builder' ),
						'param_name'  => 'loop',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Reverse', 'fusion-builder' ),
						'description' => esc_attr__( 'Select yes to play the animation in reverse.', 'fusion-builder' ),
						'param_name'  => 'reverse',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Playback Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'The speed at which the animation should play.', 'fusion-builder' ),
						'param_name'  => 'speed',
						'value'       => '1',
						'min'         => '0',
						'max'         => '5',
						'step'        => '0.1',
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
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the animation should take up. Enter value including any valid CSS unit, ex: 200px. Leave empty to use full width.', 'fusion-builder' ),
						'param_name'  => 'max_width',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how to align the animation.', 'fusion-builder' ),
						'param_name'  => 'align',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							'none'   => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => 'none',
					],
					'fusion_margin_placeholder'    => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'Design', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-lottie',
					],
					'fusion_filter_placeholder'    => [
						'selector_base' => 'fusion-lottie-',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_lottie' );
