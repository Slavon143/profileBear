<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_progress' ) ) {

	if ( ! class_exists( 'FusionSC_Progressbar' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Progressbar extends Fusion_Element {

			/**
			 * The counter.
			 *
			 * @access private
			 * @since 3.6.1
			 * @var int
			 */
			private $element_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args = [];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_progressbar-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_progressbar-shortcode-bar', [ $this, 'bar_attr' ] );
				add_filter( 'fusion_attr_progressbar-shortcode-content', [ $this, 'content_attr' ] );
				add_filter( 'fusion_attr_fusion-progressbar-text', [ $this, 'text_attr' ] );
				add_filter( 'fusion_attr_progressbar-shortcode-span', [ $this, 'span_attr' ] );

				add_shortcode( 'fusion_progress', [ $this, 'render' ] );

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
					'margin_top'                    => '',
					'margin_right'                  => '',
					'margin_bottom'                 => '',
					'margin_left'                   => '',
					'hide_on_mobile'                => fusion_builder_default_visibility( 'string' ),
					'class'                         => '',
					'id'                            => '',
					'animated_stripes'              => 'no',
					'filledcolor'                   => $fusion_settings->get( 'progressbar_filled_color' ),
					'height'                        => $fusion_settings->get( 'progressbar_height' ),
					'percentage'                    => '70',
					'show_percentage'               => 'yes',
					'striped'                       => 'no',
					'text_position'                 => $fusion_settings->get( 'progressbar_text_position' ),
					'text_align'                    => '',
					'unfilledcolor'                 => $fusion_settings->get( 'progressbar_unfilled_color' ),
					'unit'                          => '',
					'fusion_font_family_text_font'  => '',
					'fusion_font_variant_text_font' => '',
					'textcolor'                     => $fusion_settings->get( 'progressbar_text_color' ),
					'text_font_size'                => '',
					'text_line_height'              => '',
					'text_letter_spacing'           => '',
					'text_text_transform'           => '',
					'filledbordercolor'             => $fusion_settings->get( 'progressbar_filled_border_color' ),
					'filledbordersize'              => $fusion_settings->get( 'progressbar_filled_border_size' ),
					'border_radius_top_left'        => '',
					'border_radius_top_right'       => '',
					'border_radius_bottom_right'    => '',
					'border_radius_bottom_left'     => '',
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
					'progressbar_filled_color'        => 'filledcolor',
					'progressbar_height'              => 'height',
					'progressbar_text_color'          => 'textcolor',
					'progressbar_text_position'       => 'text_position',
					'progressbar_unfilled_color'      => 'unfilledcolor',
					'progressbar_filled_border_color' => 'filledbordercolor',
					'progressbar_filled_border_size'  => 'filledbordersize',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_progress' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_progress', $args );

				$defaults['filledbordersize'] = FusionBuilder::validate_shortcode_attr_value( $defaults['filledbordersize'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$text = '<span ' . FusionBuilder::attributes( 'fusion-progressbar-text' ) . '>' . $content . '</span>';

				$value = '';
				if ( 'yes' === $show_percentage ) {
					$value = '<span ' . FusionBuilder::attributes( 'fusion-progressbar-value' ) . '>' . $this->sanitize_percentage( $percentage ) . $unit . '</span>';
				}

				$text_wrapper = '<span ' . FusionBuilder::attributes( 'progressbar-shortcode-span' ) . '>' . $text . ' ' . $value . '</span>';

				$bar = '<div ' . FusionBuilder::attributes( 'progressbar-shortcode-bar' ) . '><div ' . FusionBuilder::attributes( 'progressbar-shortcode-content' ) . '></div></div>';

				if ( 'above_bar' === $text_position ) {
					$html = '<div ' . FusionBuilder::attributes( 'progressbar-shortcode' ) . '>' . $text_wrapper . ' ' . $bar . '</div>';
				} else {
					$html = '<div ' . FusionBuilder::attributes( 'progressbar-shortcode' ) . '>' . $bar . ' ' . $text_wrapper . '</div>';
				}

				$this->on_render();

				$this->element_counter++;

				return apply_filters( 'fusion_element_progress_content', $html, $args );

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
					'class' => 'fusion-progressbar',
					'style' => '',
				];

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				if ( 'above_bar' === $this->args['text_position'] ) {
					$attr['class'] .= ' fusion-progressbar-text-above-bar';
				} elseif ( 'below_bar' === $this->args['text_position'] ) {
					$attr['class'] .= ' fusion-progressbar-text-below-bar';
				} else {
					$attr['class'] .= ' fusion-progressbar-text-on-bar';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['text_align'] ) {
					$attr['style'] = 'text-align:' . $this->args['text_align'];
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				return $attr;

			}

			/**
			 * Builds the bar attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function bar_attr() {

				$attr = [
					'style' => 'background-color:' . $this->args['unfilledcolor'] . ';',
					'class' => 'fusion-progressbar-bar progress-bar',
				];

				if ( 'yes' === $this->args['striped'] ) {
					$attr['class'] .= ' progress-striped';
				}

				if ( 'yes' === $this->args['animated_stripes'] ) {
					$attr['class'] .= ' active';
				}

				if ( $this->args['height'] ) {
					$attr['style'] .= 'height:' . $this->args['height'] . ';';
				}

				if ( '' !== $this->args['border_radius_top_left'] ) {
					$attr['style'] .= 'border-top-left-radius:' . $this->args['border_radius_top_left'] . ';';
				}

				if ( '' !== $this->args['border_radius_top_right'] ) {
					$attr['style'] .= 'border-top-right-radius:' . $this->args['border_radius_top_right'] . ';';
				}

				if ( '' !== $this->args['border_radius_bottom_left'] ) {
					$attr['style'] .= 'border-bottom-left-radius:' . $this->args['border_radius_bottom_left'] . ';';
				}

				if ( '' !== $this->args['border_radius_bottom_right'] ) {
					$attr['style'] .= 'border-bottom-right-radius:' . $this->args['border_radius_bottom_right'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the content attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function content_attr() {

				$attr = [
					'class' => 'progress progress-bar-content',
					'style' => 'width:0%;background-color:' . $this->args['filledcolor'] . ';',
				];

				if ( $this->args['filledbordersize'] && $this->args['filledbordercolor'] ) {
					$attr['style'] .= 'border: ' . $this->args['filledbordersize'] . ' solid ' . $this->args['filledbordercolor'] . ';';
				}

				if ( '' !== $this->args['border_radius_top_left'] ) {
					$attr['style'] .= 'border-top-left-radius:' . $this->args['border_radius_top_left'] . ';';
				}

				if ( '' !== $this->args['border_radius_top_right'] ) {
					$attr['style'] .= 'border-top-right-radius:' . $this->args['border_radius_top_right'] . ';';
				}

				if ( '' !== $this->args['border_radius_bottom_left'] ) {
					$attr['style'] .= 'border-bottom-left-radius:' . $this->args['border_radius_bottom_left'] . ';';
				}

				if ( '' !== $this->args['border_radius_bottom_right'] ) {
					$attr['style'] .= 'border-bottom-right-radius:' . $this->args['border_radius_bottom_right'] . ';';
				}

				$attr['role']            = 'progressbar';
				$attr['aria-labelledby'] = 'awb-progressbar-label-' . $this->element_counter;
				$attr['aria-valuemin']   = '0';
				$attr['aria-valuemax']   = '100';
				$attr['aria-valuenow']   = $this->sanitize_percentage( $this->args['percentage'] );

				return $attr;

			}

			/**
			 * Builds the text attributes array.
			 *
			 * @access public
			 * @since 3.6.1
			 * @return array
			 */
			public function text_attr() {
				$attr = [
					'class' => 'fusion-progressbar-text',
					'id'    => 'awb-progressbar-label-' . $this->element_counter,
				];

				return $attr;

			}

			/**
			 * Builds the span attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function span_attr() {
				$atts = [
					'class' => 'progress-title',
					'style' => 'color:' . $this->args['textcolor'] . ';',
				];

				if ( 'on_bar' === $this->args['text_position'] ) {
					$empty_percentage = 100 - $this->sanitize_percentage( $this->args['percentage'] );
					if ( 66 > $empty_percentage ) {
						if ( ! is_rtl() ) {
							$atts['style'] .= 'right: calc(15px + ' . $empty_percentage . '%);';
						} else {
							$atts['style'] .= 'left: calc(15px + ' . $empty_percentage . '%);';
						}
					}
				}

				if ( '' !== $this->args['text_line_height'] ) {
					$atts['style'] .= 'line-height:' . $this->args['text_line_height'] . ';';
				}

				if ( '' !== $this->args['text_letter_spacing'] ) {
					$atts['style'] .= 'letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['text_letter_spacing'] ) . ';';
				}

				if ( '' !== $this->args['text_text_transform'] ) {
					$atts['style'] .= 'text-transform:' . $this->args['text_text_transform'] . ';';
				}

				if ( '' !== $this->args['text_font_size'] ) {
					$atts['style'] .= 'font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['text_font_size'] ) . ';';
				}

				$atts['style'] .= Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'text_font' );

				return $atts;
			}

			/**
			 * Sanitize the percentage value, because this can come also from a
			 * dynamic data which can be a string or a float.
			 *
			 * @since 3.6
			 * @param int|string $percentage The value to be sanitized.
			 * @return int
			 */
			protected function sanitize_percentage( $percentage ) {
				$percentage = round( floatval( $percentage ), 0 );

				if ( 0 > $percentage ) {
					$percentage = 0;
				}

				if ( 100 < $percentage ) {
					$percentage = 100;
				}

				return $percentage;
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access protected
			 * @since 1.1
			 * @return array
			 */
			protected function add_styling() {
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $fusion_settings, $dynamic_css_helpers;

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-progressbar-bar' ], '.fusion-progressbar-bar' );

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .progress-bar-content' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_filled_color' ) );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color']     = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_filled_color' ) );

				$css['global'][ $dynamic_css_helpers->implode( $main_elements ) ]['background-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_unfilled_color' ) );
				$css['global'][ $dynamic_css_helpers->implode( $main_elements ) ]['border-color']     = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_unfilled_color' ) );

				$css[ $content_media_query ]['.fusion-progressbar']['margin-bottom']                 = '10px !important';
				$css[ $six_fourty_media_query ]['.fusion-progressbar']['margin-bottom']              = '10px !important';
				$css[ $three_twenty_six_fourty_media_query ]['.fusion-progressbar']['margin-bottom'] = '10px !important';
				$css[ $ipad_portrait_media_query ]['.fusion-progressbar']['margin-bottom']           = '10px !important';

				return $css;
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @since 3.5
			 * @return array
			 */
			public static function get_element_extras() {
				return [
					'is_rtl' => is_rtl(),
				];
			}


			/**
			 * Adds settings to element options panel.
			 *
			 * @access protected
			 * @since 1.1
			 * @return array $sections Progress Bar settings.
			 */
			protected function add_options() {

				return [
					'progress_shortcode_section' => [
						'label'       => esc_html__( 'Progress Bar', 'fusion-builder' ),
						'description' => '',
						'id'          => 'progress_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-tasks',
						'fields'      => [
							'progressbar_text_position'  => [
								'label'       => esc_html__( 'Progress Bar Text Position', 'fusion-builder' ),
								'description' => esc_html__( 'Select the position of the progress bar text. Choose "Default" for Global Options selection.', 'fusion-builder' ),
								'id'          => 'progressbar_text_position',
								'default'     => 'on_bar',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'on_bar'    => esc_html__( 'On Bar', 'fusion-builder' ),
									'above_bar' => esc_html__( 'Above Bar', 'fusion-builder' ),
									'below_bar' => esc_html__( 'Below Bar', 'fusion-builder' ),
								],
							],
							'progressbar_text_color'     => [
								'label'       => esc_html__( 'Progress Bar Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the progress bar text.', 'fusion-builder' ),
								'id'          => 'progressbar_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'progressbar_height'         => [
								'label'       => esc_html__( 'Progress Bar Height', 'fusion-builder' ),
								'description' => esc_html__( 'Insert a height for the progress bar.', 'fusion-builder' ),
								'id'          => 'progressbar_height',
								'default'     => '48px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'progressbar_filled_color'   => [
								'label'       => esc_html__( 'Progress Bar Filled Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the progress bar filled area.', 'fusion-builder' ),
								'id'          => 'progressbar_filled_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'progressbar_unfilled_color' => [
								'label'       => esc_html__( 'Progress Bar Unfilled Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the progress bar unfilled area.', 'fusion-builder' ),
								'id'          => 'progressbar_unfilled_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'progressbar_filled_border_size' => [
								'label'       => esc_html__( 'Progress Bar Filled Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the progress bar filled area.', 'fusion-builder' ),
								'id'          => 'progressbar_filled_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '20',
									'step' => '1',
								],
							],
							'progressbar_filled_border_color' => [
								'label'       => esc_html__( 'Progress Bar Filled Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the progress bar filled area.', 'fusion-builder' ),
								'id'          => 'progressbar_filled_border_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access protected
			 * @since 3.2
			 * @return void
			 */
			protected function on_first_render() {
				Fusion_Dynamic_JS::enqueue_script(
					'fusion-progress',
					FusionBuilder::$js_folder_url . '/general/fusion-progress.js',
					FusionBuilder::$js_folder_path . '/general/fusion-progress.js',
					[ 'jquery' ],
					'1',
					true
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/progressbar.min.css' );
			}
		}
	}

	new FusionSC_Progressbar();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_progress() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Progressbar',
			[
				'name'          => esc_attr__( 'Progress Bar', 'fusion-builder' ),
				'shortcode'     => 'fusion_progress',
				'icon'          => 'fusiona-tasks',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-progress-preview.php',
				'preview_id'    => 'fusion-builder-block-module-progress-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/progress-bar-element/',
				'subparam_map'  => [
					'fusion_font_family_text_font'  => 'main_typography',
					'fusion_font_variant_text_font' => 'main_typography',
					'text_font_size'                => 'main_typography',
					'text_line_height'              => 'main_typography',
					'text_letter_spacing'           => 'main_typography',
					'text_text_transform'           => 'main_typography',
					'text_color'                    => 'main_typography',
				],
				'inline_editor' => true,
				'params'        => [
					[
						'type'         => 'range',
						'heading'      => esc_attr__( 'Filled Area Percentage', 'fusion-builder' ),
						'description'  => esc_attr__( 'From 1% to 100%.', 'fusion-builder' ),
						'dynamic_data' => true,
						'param_name'   => 'percentage',
						'value'        => '70',
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Progress Bar Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Text will show up on progress bar.', 'fusion-builder' ),
						'dynamic_data' => true,
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display Percentage Value', 'fusion-builder' ),
						'description' => esc_attr__( 'Select if you want the filled area percentage value to be shown.', 'fusion-builder' ),
						'param_name'  => 'show_percentage',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'yes',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Progress Bar Unit', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert a unit for the progress bar. ex %.', 'fusion-builder' ),
						'param_name'  => 'unit',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'show_percentage',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					'fusion_margin_placeholder' => [
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
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],

					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the position of the progress bar text. Choose "Default" for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'text_position',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'on_bar'    => esc_attr__( 'On Bar', 'fusion-builder' ),
							'above_bar' => esc_attr__( 'Above Bar', 'fusion-builder' ),
							'below_bar' => esc_attr__( 'Below Bar', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the alignment of the text. If the text position is "On Bar", the alignment will work only if the bar is filled over 35% percent.', 'fusion-builder' ),
						'param_name'  => 'text_align',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'percentage',
								'value'    => '34',
								'operator' => '>',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'typography',
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the text typography.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
						'choices'          => [
							'font-family'    => 'text_font',
							'font-size'      => 'text_font_size',
							'line-height'    => 'text_line_height',
							'letter-spacing' => 'text_letter_spacing',
							'text-transform' => 'text_text_transform',
							'color'          => 'textcolor',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '400',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
							'color'          => $fusion_settings->get( 'progressbar_text_color' ),
						],
						'remove_from_atts' => true,
						'global'           => true,
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Striped Filling', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get the filled area striped.', 'fusion-builder' ),
						'param_name'  => 'striped',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Animated Stripes', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to get the the stripes animated.', 'fusion-builder' ),
						'param_name'  => 'animated_stripes',
						'value'       => [
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => 'no',
						'dependency'  => [
							[
								'element'  => 'striped',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Progress Bar Height', 'fusion-builder' ),
						'description'      => esc_attr__( 'Insert a height for the progress bar. Enter value including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'dimensions',
						'value'            => [
							'height' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the filled in area. ', 'fusion-builder' ),
						'param_name'  => 'filledcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_filled_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Unfilled Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the unfilled in area. ', 'fusion-builder' ),
						'param_name'  => 'unfilledcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_unfilled_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description'      => esc_attr__( 'Enter values including any valid CSS unit, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Filled Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'filledbordersize',
						'value'       => '',
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'progressbar_filled_border_size' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the filled in area. ', 'fusion-builder' ),
						'param_name'  => 'filledbordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'progressbar_filled_border_color' ),
						'dependency'  => [
							[
								'element'  => 'filledbordersize',
								'value'    => '0',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],

				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_progress' );
