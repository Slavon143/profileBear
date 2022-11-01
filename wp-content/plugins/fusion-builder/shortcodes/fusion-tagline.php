<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_tagline_box' ) ) {

	if ( ! class_exists( 'FusionSC_Tagline' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Tagline extends Fusion_Element {

			/**
			 * The tagline box counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $tagline_box_counter = 1;

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
				add_filter( 'fusion_attr_tagline-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_tagline-shortcode-reading-box', [ $this, 'reading_box_attr' ] );
				add_filter( 'fusion_attr_tagline-shortcode-button', [ $this, 'button_attr' ] );

				add_shortcode( 'fusion_tagline_box', [ $this, 'render' ] );

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
					'hide_on_mobile'                    => fusion_builder_default_visibility( 'string' ),
					'class'                             => '',
					'id'                                => '',
					'backgroundcolor'                   => $fusion_settings->get( 'tagline_bg' ),
					'border'                            => '0px',
					'bordercolor'                       => $fusion_settings->get( 'tagline_border_color' ),
					'button'                            => '',
					'buttoncolor'                       => 'default',
					'button_border_radius_top_left'     => $fusion_settings->get( 'button_border_radius', 'top_left' ),
					'button_border_radius_top_right'    => $fusion_settings->get( 'button_border_radius', 'top_right' ),
					'button_border_radius_bottom_right' => $fusion_settings->get( 'button_border_radius', 'bottom_right' ),
					'button_border_radius_bottom_left'  => $fusion_settings->get( 'button_border_radius', 'bottom_left' ),
					'button_size'                       => 'default-size',
					'button_type'                       => $fusion_settings->get( 'button_type' ),
					'content_alignment'                 => 'left',
					'description'                       => '',
					'highlightposition'                 => 'left',
					'link'                              => '',
					'linktarget'                        => '_self',
					'padding_top'                       => '',
					'padding_right'                     => '',
					'padding_bottom'                    => '',
					'padding_left'                      => '',
					'margin_bottom'                     => ( '' !== $fusion_settings->get( 'tagline_margin', 'bottom' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'tagline_margin', 'bottom' ) ) : '0px',
					'margin_top'                        => ( '' !== $fusion_settings->get( 'tagline_margin', 'top' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'tagline_margin', 'top' ) ) : '0px',
					'modal'                             => '',
					'shadow'                            => 'no',
					'shadowopacity'                     => '0.7',
					'title'                             => '',
					'animation_type'                    => '',
					'animation_direction'               => 'left',
					'animation_speed'                   => '',
					'animation_offset'                  => $fusion_settings->get( 'animation_offset' ),
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
					'tagline_bg'             => 'backgroundcolor',
					'tagline_border_color'   => 'bordercolor',
					'button_border_radius'   => 'button_border_radius',
					'button_size'            => 'button_size',
					'button_type'            => 'button_type',
					'tagline_margin[top]'    => 'margin_top',
					'tagline_margin[bottom]' => 'margin_bottom',
					'animation_offset'       => 'animation_offset',
				];
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
					'primary_color' => esc_attr( $fusion_settings->get( 'primary_color' ) ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {

				return [
					'primary_color' => 'primary_color',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tagline_box' );
				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_tagline_box', $args );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_tagline_box', $args );

				$defaults['border'] = FusionBuilder::validate_shortcode_attr_value( $defaults['border'], 'px' );

				if ( $defaults['modal'] ) {
					$defaults['link'] = '#';
				}

				// BC compatibility for button shape.
				if ( isset( $args['button_shape'] ) && ! isset( $args['button_border_radius'] ) && ! isset( $args['border_radius_top_left'] ) ) {
					$args['button_shape'] = strtolower( $args['button_shape'] );

					$button_radius = [
						'square'  => '0px',
						'round'   => '2px',
						'round3d' => '4px',
						'pill'    => '25px',
					];

					if ( '3d' === $defaults['button_type'] && 'round' === $args['button_shape'] ) {
						$args['button_shape'] = 'round3d';
					}

					$defaults['button_border_radius_top_left']     = isset( $button_radius[ $args['button_shape'] ] ) ? $button_radius[ $args['button_shape'] ] : '0px';
					$defaults['button_border_radius_top_right']    = $defaults['button_border_radius_top_left'];
					$defaults['button_border_radius_bottom_right'] = $defaults['button_border_radius_top_left'];
					$defaults['button_border_radius_bottom_left']  = $defaults['button_border_radius_top_left'];
				} elseif ( isset( $args['buton_border_radius'] ) && ! isset( $args['button_border_radius_top_left'] ) ) {
					$defaults['button_border_radius_top_left']     = $args['buton_border_radius'];
					$defaults['button_border_radius_top_right']    = $defaults['button_border_radius_top_left'];
					$defaults['button_border_radius_bottom_right'] = $defaults['button_border_radius_top_left'];
					$defaults['button_border_radius_bottom_left']  = $defaults['button_border_radius_top_left'];
				}

				$defaults['button_type'] = strtolower( $defaults['button_type'] );

				$defaults['description'] = fusion_decode_if_needed( $defaults['description'] );

				if ( ! empty( htmlspecialchars( fusion_decode_input( $defaults['title'] ) ) ) ) {
					$defaults['title'] = fusion_decode_if_needed( $defaults['title'] );
				}

				extract( $defaults );

				$this->args     = $defaults;
				$desktop_button = $title_tag = $additional_content = '';

				$this->args['padding_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_bottom'], 'px' );
				$this->args['padding_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_left'], 'px' );
				$this->args['padding_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_right'], 'px' );
				$this->args['padding_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['padding_top'], 'px' );

				$fusion_settings = awb_get_fusion_settings();
				if ( ! apply_filters( 'awb_load_button_presets', ( '1' === $fusion_settings->get( 'button_presets' ) ) ) ) {
					$this->args['buttoncolor'] = 'default';
				}

				// Single string for CSS.
				$this->args['button_border_radius'] = fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_radius_top_left'] ) . ' ' . fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_radius_top_right'] ) . ' ' . fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_radius_bottom_right'] ) . ' ' . fusion_library()->sanitize->get_value_with_unit( $this->args['button_border_radius_bottom_left'] );

				$styles = apply_filters( 'fusion_builder_tagline_box_style', "<style type='text/css'>.reading-box-container-{$this->tagline_box_counter} .element-bottomshadow:before,.reading-box-container-{$this->tagline_box_counter} .element-bottomshadow:after{opacity:{$shadowopacity};}</style>", $defaults, $this->tagline_box_counter );

				if ( isset( $title ) && $title ) {
					$title_tag = '<h2>' . $title . '</h2>';
				}

				$addition_content_class = '';

				if ( isset( $description ) && $description ) {
					if ( isset( $title ) && $title ) {
						$addition_content_class = ' fusion-reading-box-additional';
					}

					$additional_content    .= '<div class="reading-box-description' . $addition_content_class . '">' . $description . '</div>';
					$addition_content_class = '';
				} else {
					if ( isset( $title ) && $title ) {
						$addition_content_class = ' fusion-reading-box-additional';
					}
				}

				if ( $content ) {
					fusion_element_rendering_elements( true );
					$additional_content .= '<div class="reading-box-additional' . $addition_content_class . '">' . do_shortcode( $content ) . '</div>';
					fusion_element_rendering_elements( false );
				}

				if ( ( isset( $link ) && $link ) && ( isset( $button ) && $button ) && 'center' !== $this->args['content_alignment'] ) {

					$button_margin_class = '';
					if ( $additional_content ) {
						$button_margin_class = ' fusion-desktop-button-margin';
					}

					$this->args['button_class'] = ' fusion-desktop-button fusion-tagline-button continue' . $button_margin_class;
					$desktop_button             = '<a ' . FusionBuilder::attributes( 'tagline-shortcode-button' ) . '><span>' . $button . '</span></a>';
				}

				if ( $additional_content ) {
					$additional_content .= '<div class="fusion-clearfix"></div>';

					$additional_content = $desktop_button . $title_tag . $additional_content;
				} elseif ( 'center' === $this->args['content_alignment'] ) {
					$additional_content = $title_tag;
				} else {
					$additional_content = '<div class="fusion-reading-box-flex">';
					if ( 'left' === $this->args['content_alignment'] ) {
						$additional_content .= $title_tag . $desktop_button;
					} else {
						$additional_content .= $desktop_button . $title_tag;
					}
					$additional_content .= '</div>';
				}

				if ( ( isset( $link ) && $link ) && ( isset( $button ) && $button ) ) {
					$this->args['button_class'] = ' fusion-mobile-button';
					$additional_content        .= '<a ' . FusionBuilder::attributes( 'tagline-shortcode-button' ) . '><span>' . $button . '</span></a>';
				}

				$html = $styles . '<div ' . FusionBuilder::attributes( 'tagline-shortcode' ) . '><div ' . FusionBuilder::attributes( 'tagline-shortcode-reading-box' ) . '>' . $additional_content . '</div></div>';

				$this->tagline_box_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_tagline_content', $html, $args );

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
						'class' => 'fusion-reading-box-container reading-box-container-' . $this->tagline_box_counter,
					]
				);

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] = '';

				if ( $this->args['margin_top'] || '0' === $this->args['margin_top'] ) {
					$attr['style'] .= 'margin-top:' . fusion_library()->sanitize->get_value_with_unit( $this->args['margin_top'] ) . ';';
				}

				if ( $this->args['margin_bottom'] || '0' === $this->args['margin_bottom'] ) {
					$attr['style'] .= 'margin-bottom:' . fusion_library()->sanitize->get_value_with_unit( $this->args['margin_bottom'] ) . ';';
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
			 * Builds the reading-box attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function reading_box_attr() {

				$attr = [
					'class' => 'reading-box',
				];

				if ( 'right' === $this->args['content_alignment'] ) {
					$attr['class'] .= ' reading-box-right';
				} elseif ( 'center' === $this->args['content_alignment'] ) {
					$attr['class'] .= ' reading-box-center';
				}

				if ( 'yes' === $this->args['shadow'] ) {
					$attr['class'] .= ' element-bottomshadow';
				}

				$attr['style']  = 'background-color:' . $this->args['backgroundcolor'] . ';';
				$attr['style'] .= 'border-width:' . $this->args['border'] . ';';
				$attr['style'] .= 'border-color:' . $this->args['bordercolor'] . ';';
				if ( 'none' !== $this->args['highlightposition'] ) {
					if ( str_replace( 'px', '', $this->args['border'] ) > 3 ) {
						$attr['style'] .= 'border-' . $this->args['highlightposition'] . '-width:' . $this->args['border'] . ';';
					} else {
						$attr['style'] .= 'border-' . $this->args['highlightposition'] . '-width:3px;';
					}
					$attr['style'] .= 'border-' . $this->args['highlightposition'] . '-color:var(--primary_color);';
				}
				$attr['style'] .= 'border-style:solid;';

				$attr['style'] .= Fusion_Builder_Padding_Helper::get_paddings_style( $this->args );

				return $attr;
			}

			/**
			 * Builds the button attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function button_attr() {

				$attr          = [
					'class' => 'button fusion-button button-' . $this->args['buttoncolor'] . ' fusion-button-' . $this->args['button_size'] . ' button-' . $this->args['button_size'] . ' button-' . $this->args['button_type'] . $this->args['button_class'],
					'style' => '',
				];
				$attr['class'] = strtolower( $attr['class'] );

				if ( 'right' === $this->args['content_alignment'] ) {
					$attr['class'] .= ' continue-left';
				} elseif ( 'center' === $this->args['content_alignment'] ) {
					$attr['class'] .= ' continue-center';
				} else {
					$attr['class'] .= ' continue-right';
				}

				if ( 'flat' === $this->args['button_type'] ) {
					$attr['style'] .= '-webkit-box-shadow:none;-moz-box-shadow:none;box-shadow:none;';
				}

				$attr['href']   = $this->args['link'];
				$attr['target'] = $this->args['linktarget'];

				if ( '_blank' === $attr['target'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}

				if ( $this->args['modal'] ) {
					$attr['data-toggle'] = 'modal';
					$attr['data-target'] = '.' . $this->args['modal'];
				}

				if ( $this->args['button_border_radius'] ) {
					$attr['style'] .= 'border-radius:' . $this->args['button_border_radius'];
				}

				return $attr;

			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query, $dynamic_css_helpers;

				$fusion_settings = awb_get_fusion_settings();

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-reading-box-container' ], '.fusion-reading-box-container' );

				if ( 'yes' === $fusion_settings->get( 'button_span' ) ) {
					$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-desktop-button' );
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width'] = 'auto';
				}

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .reading-box' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'tagline_bg' ) );

				$css[ $content_media_query ]['.fusion-reading-box-container .fusion-reading-box-flex']['display'] = 'block';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-desktop-button' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display']     = 'none';
				$css[ $content_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-mobile-button' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display']       = 'block';
				$css[ $content_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display']   = 'none';
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['float']   = 'none';

				$elements = $dynamic_css_helpers->map_selector( $elements, '.continue-center' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .continue-center' );
				$css[ $content_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'inline-block';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .reading-box.reading-box-center' );
				$css[ $content_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['text-align'] = 'center';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .reading-box.reading-box-right' );
				$css[ $content_min_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['text-align'] = 'right';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .continue' );
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Tagline settings.
			 */
			public function add_options() {

				return [
					'tagline_box_shortcode_section' => [
						'label'       => esc_html__( 'Tagline Box', 'fusion-builder' ),
						'description' => '',
						'id'          => 'tagline_box_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-list-alt',
						'fields'      => [
							'tagline_bg'           => [
								'label'       => esc_html__( 'Tagline Box Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the tagline box background.', 'fusion-builder' ),
								'id'          => 'tagline_bg',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'tagline_border_color' => [
								'label'       => esc_html__( 'Tagline Box Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of the tagline box.', 'fusion-builder' ),
								'id'          => 'tagline_border_color',
								'default'     => 'rgba(226,226,226,0)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'tagline_margin'       => [
								'label'       => esc_html__( 'Tagline Box Top/Bottom Margins', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the top/bottom margin of the tagline box.', 'fusion-builder' ),
								'id'          => 'tagline_margin',
								'default'     => [
									'top'    => '0px',
									'bottom' => '20px',
								],
								'type'        => 'spacing',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => true,
									'bottom' => true,
								],
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {

				Fusion_Dynamic_JS::enqueue_script( 'fusion-button' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/tagline.min.css' );
			}
		}
	}

	new FusionSC_Tagline();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_tagline_box() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tagline',
			[
				'name'            => esc_attr__( 'Tagline Box', 'fusion-builder' ),
				'shortcode'       => 'fusion_tagline_box',
				'icon'            => 'fusiona-list-alt',
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-tagline-preview.php',
				'preview_id'      => 'fusion-builder-block-module-tagline-preview-template',
				'allow_generator' => true,
				'inline_editor'   => true,
				'help_url'        => 'https://theme-fusion.com/documentation/avada/elements/tagline-box-element/',
				'params'          => [
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color. ', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tagline_bg' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Shadow', 'fusion-builder' ),
						'description' => esc_attr__( 'Show the shadow below the box.', 'fusion-builder' ),
						'param_name'  => 'shadow',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Shadow Opacity', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the opacity of the shadow.', 'fusion-builder' ),
						'param_name'  => 'shadowopacity',
						'min'         => '0',
						'max'         => '1',
						'step'        => '0.05',
						'value'       => '0.7',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'shadow',
								'value'    => 'yes',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'param_name'  => 'border',
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'min'         => '0',
						'max'         => '20',
						'value'       => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color.', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'tagline_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'border',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Highlight Border Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the highlight. This border highlight is from Global Options primary color and does not take the color from border color above.', 'fusion-builder' ),
						'param_name'  => 'highlightposition',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'top'    => esc_attr__( 'Top', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'none'   => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'     => 'left',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Content Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the content should be displayed.', 'fusion-builder' ),
						'param_name'  => 'content_alignment',
						'value'       => [
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Button Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'The url the button will link to.', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Button Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the text that will display in the button.', 'fusion-builder' ),
						'param_name'  => 'button',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window <br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'linktarget',
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
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Modal Window Anchor', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the class name of the modal window you want to open on button click.', 'fusion-builder' ),
						'param_name'  => 'modal',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's size. Choose default for Global Option selection.", 'fusion-builder' ),
						'param_name'  => 'button_size',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Type', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's type.", 'fusion-builder' ),
						'param_name'  => 'button_type',
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'flat' => esc_attr__( 'Flat', 'fusion-builder' ),
							'3d'   => esc_attr__( '3D', 'fusion-builder' ),
						],
						'default'     => '',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Button Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the border radius. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'button_border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'button_border_radius_top_left'     => '',
							'button_border_radius_top_right'    => '',
							'button_border_radius_bottom_right' => '',
							'button_border_radius_bottom_left'  => '',
						],
						'dependency'       => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => ( apply_filters( 'awb_load_button_presets', ( '1' === $fusion_settings->get( 'button_presets' ) ) ) ? 'select' : 'hidden' ),
						'heading'     => esc_attr__( 'Button Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the button color.', 'fusion-builder' ),
						'param_name'  => 'buttoncolor',
						'value'       => [
							'default'   => esc_attr__( 'Default', 'fusion-builder' ),
							'green'     => esc_attr__( 'Green', 'fusion-builder' ),
							'darkgreen' => esc_attr__( 'Dark Green', 'fusion-builder' ),
							'orange'    => esc_attr__( 'Orange', 'fusion-builder' ),
							'blue'      => esc_attr__( 'Blue', 'fusion-builder' ),
							'red'       => esc_attr__( 'Red', 'fusion-builder' ),
							'pink'      => esc_attr__( 'Pink', 'fusion-builder' ),
							'darkgray'  => esc_attr__( 'Dark Gray', 'fusion-builder' ),
							'lightgray' => esc_attr__( 'Light Gray', 'fusion-builder' ),
						],
						'default'     => 'default',
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Tagline Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the title text.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Tagline Description', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the description text.', 'fusion-builder' ),
						'param_name'   => 'description',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Additional Content', 'fusion-builder' ),
						'description'  => esc_attr__( 'This is additional content you can add to the tagline box. This will show below the title and description if one is used.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Tagline Box Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					'fusion_margin_placeholder'    => [
						'param_name'  => 'dimensions',
						'description' => esc_attr__( 'Spacing above and below the tagline. In px, em or %, e.g. 10px.', 'fusion-builder' ),
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-reading-box-container',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
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
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_tagline_box' );
