<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_title' ) ) {

	if ( ! class_exists( 'FusionSC_Title' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Title extends Fusion_Element {

			/**
			 * Title counter.
			 *
			 * @access protected
			 * @since 1.9
			 * @var integer
			 */
			protected $title_counter = 1;

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
				add_filter( 'fusion_attr_title-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_title-shortcode-heading', [ $this, 'heading_attr' ] );
				add_filter( 'fusion_attr_animated-text-wrapper', [ $this, 'animated_text_wrapper' ] );
				add_filter( 'fusion_attr_roated-text', [ $this, 'rotated_text_attr' ] );
				add_filter( 'fusion_attr_title-shortcode-sep', [ $this, 'sep_attr' ] );
				add_filter( 'fusion_attr_title-shortcode-href', [ $this, 'href_attr' ] );

				add_shortcode( 'fusion_title', [ $this, 'render' ] );

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
					'animation_direction'            => 'left',
					'animation_offset'               => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'                => '',
					'animation_type'                 => '',
					'hide_on_mobile'                 => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                 => '',
					'class'                          => '',
					'id'                             => '',
					'title_type'                     => 'text',
					'rotation_effect'                => 'bounceIn',
					'display_time'                   => '1200',
					'highlight_effect'               => 'circle',
					'loop_animation'                 => 'off',
					'highlight_width'                => '9',
					'highlight_top_margin'           => '0',
					'before_text'                    => '',
					'rotation_text'                  => '',
					'highlight_text'                 => '',
					'title_link'                     => 'off',
					'link_url'                       => '',
					'link_target'                    => '_self',
					'link_color'                     => $fusion_settings->get( 'link_color' ),
					'link_hover_color'               => $fusion_settings->get( 'primary_color' ),
					'fusion_font_family_title_font'  => '',
					'fusion_font_variant_title_font' => '',
					'after_text'                     => '',
					'content_align'                  => 'left',
					'content_align_medium'           => '',
					'content_align_small'            => '',
					'font_size'                      => '',
					'animated_font_size'             => '',
					'letter_spacing'                 => '',
					'line_height'                    => '',
					'link_attributes'                => '',
					'margin_bottom'                  => $fusion_settings->get( 'title_margin', 'bottom' ),
					'margin_bottom_medium'           => '',
					'margin_bottom_mobile'           => '',
					'margin_bottom_small'            => $fusion_settings->get( 'title_margin_mobile', 'bottom' ),
					'margin_top'                     => $fusion_settings->get( 'title_margin', 'top' ),
					'margin_top_medium'              => '',
					'margin_top_mobile'              => '',
					'margin_top_small'               => $fusion_settings->get( 'title_margin_mobile', 'top' ),
					'margin_right'                   => $fusion_settings->get( 'title_margin', 'right' ),
					'margin_right_medium'            => '',
					'margin_right_small'             => $fusion_settings->get( 'title_margin_mobile', 'right' ),
					'margin_left'                    => $fusion_settings->get( 'title_margin', 'left' ),
					'margin_left_medium'             => '',
					'margin_left_small'              => $fusion_settings->get( 'title_margin_mobile', 'left' ),
					'sep_color'                      => $fusion_settings->get( 'title_border_color' ),
					'size'                           => 1,
					'style_tag'                      => '',
					'style_type'                     => $fusion_settings->get( 'title_style_type' ),
					'text_color'                     => '',
					'text_shadow'                    => '',
					'text_shadow_blur'               => '',
					'text_shadow_color'              => '',
					'text_shadow_horizontal'         => '',
					'text_shadow_vertical'           => '',
					'text_transform'                 => $fusion_settings->get( 'title_text_transform' ),
					'animated_text_color'            => '',
					'highlight_color'                => '',
					'responsive_typography'          => 0.0 < $fusion_settings->get( 'typography_sensitivity' ),
					'gradient_font'                  => 'no',
					'gradient_start_color'           => '',
					'gradient_end_color'             => '',
					'gradient_start_position'        => '0',
					'gradient_end_position'          => '100',
					'gradient_type'                  => 'linear',
					'radial_direction'               => 'center center',
					'linear_angle'                   => '180',
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
					'title_margin[top]'           => 'margin_top',
					'title_margin[right]'         => 'margin_right',
					'title_margin[bottom]'        => 'margin_bottom',
					'title_margin[left]'          => 'margin_left',
					'title_margin_mobile[top]'    => 'margin_top_small',
					'title_margin_mobile[right]'  => 'margin_right_small',
					'title_margin_mobile[bottom]' => 'margin_bottom_small',
					'title_margin_mobile[left]'   => 'margin_left_small',
					'title_border_color'          => 'sep_color',
					'title_style_type'            => 'style_type',
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
					'content_break_point' => $fusion_settings->get( 'content_break_point' ),
					'visibility_large'    => $fusion_settings->get( 'visibility_large' ),
					'visibility_medium'   => $fusion_settings->get( 'visibility_medium' ),
					'visibility_small'    => $fusion_settings->get( 'visibility_small' ),
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
					'content_break_point' => 'content_break_point',
				];
			}

			/**
			 * Renders content
			 *
			 * @access public
			 * @since 3.0
			 * @param string $content Content between shortcode.
			 * @return string
			 */
			public function render_content( $content ) {
				fusion_element_rendering_elements( true );
				$content = do_shortcode( $content );

				if ( 'off' !== $this->args['title_link'] ) {
					$content = '<a ' . FusionBuilder::attributes( 'title-shortcode-href' ) . '>' . $content . '</a>';
				}

				fusion_element_rendering_elements( false );
				return $content;
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
				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_title' );
				$defaults = apply_filters( 'fusion_builder_default_args', $defaults, 'fusion_title', $args );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_title', $args );

				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );
				$defaults['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right'], 'px' );
				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left'], 'px' );

				$defaults['margin_top_medium']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top_medium'], 'px' );
				$defaults['margin_right_medium']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right_medium'], 'px' );
				$defaults['margin_bottom_medium'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom_medium'], 'px' );
				$defaults['margin_left_medium']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left_medium'], 'px' );

				$defaults['margin_top_small']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top_small'], 'px' );
				$defaults['margin_right_small']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right_small'], 'px' );
				$defaults['margin_bottom_small'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom_small'], 'px' );
				$defaults['margin_left_small']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left_small'], 'px' );

				// BC.
				$defaults['margin_top_mobile']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top_mobile'], 'px' );
				$defaults['margin_bottom_mobile'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom_mobile'], 'px' );

				$is_flex_container = fusion_element_rendering_is_flex();

				extract( $defaults );

				$this->args = $defaults;

				$this->set_text_shadow_style();

				if ( 1 === count( explode( ' ', $this->args['style_type'] ) ) ) {
					$style_type .= ' solid';
				}

				if ( ! $this->args['style_type'] || 'default' === $this->args['style_type'] ) {
					$this->args['style_type'] = $style_type = $fusion_settings->get( 'title_style_type' );
				}

				if ( 'text' !== $this->args['title_type'] ) {
					$this->args['style_type'] = $style_type = 'none';
				}

				// Make sure the title text is not wrapped with an unattributed p tag.
				$content           = preg_replace( '!^<p>(.*?)</p>$!i', '$1', trim( $content ) );
				$rotation_texts    = [];
				$bottom_highlights = [ 'underline', 'double_underline', 'underline_zigzag', 'underline_zigzag', 'curly' ];

				if ( 'rotating' === $this->args['title_type'] && $rotation_text ) {
					$rotation_texts = explode( '|', trim( $rotation_text ) );
				}

				$title_tag = 'div' === $size ? 'div' : 'h' . $size;

				if ( 'rotating' === $this->args['title_type'] ) {

					$html  = '<div ' . FusionBuilder::attributes( 'title-shortcode' ) . '>';
					$html .= '<' . $title_tag . ' ' . FusionBuilder::attributes( 'title-shortcode-heading' ) . '>';
					$html .= '<span class="fusion-animated-text-prefix">' . $before_text . '</span> ';

					if ( 0 < count( $rotation_texts ) ) {
						$html .= '<span ' . FusionBuilder::attributes( 'animated-text-wrapper' ) . '>';
						$html .= '<span class="fusion-animated-texts">';

						foreach ( $rotation_texts as $text ) {
							if ( '' !== $text ) {
								$html .= '<span ' . FusionBuilder::attributes( 'roated-text' ) . '>' . $text . '</span>';
							}
						}

						$html .= '</span></span>';
					}

					$html .= ' <span class="fusion-animated-text-postfix">' . $after_text . '</span>';
					$html .= '</' . $title_tag . '>';
					$html .= '</div>';

				} elseif ( 'highlight' === $this->args['title_type'] ) {

					$html  = '<div ' . FusionBuilder::attributes( 'title-shortcode' ) . '>';
					$html .= '<' . $title_tag . ' ' . FusionBuilder::attributes( 'title-shortcode-heading' ) . '>';
					$html .= '<span class="fusion-highlighted-text-prefix">' . $before_text . '</span> ';

					if ( $highlight_text ) {
						$html .= '<span class="fusion-highlighted-text-wrapper">';
						$html .= '<span ' . FusionBuilder::attributes( 'animated-text-wrapper' ) . '>' . $highlight_text . '</span>';
						$html .= '</span>';
					}

					$html .= ' <span class="fusion-highlighted-text-postfix">' . $after_text . '</span>';
					$html .= '</' . $title_tag . '>';
					$html .= '</div>';

				} elseif ( false !== strpos( $style_type, 'underline' ) || false !== strpos( $style_type, 'none' ) ) {
					$html = sprintf(
						'<div %s><%s %s>%s</%s></div>',
						FusionBuilder::attributes( 'title-shortcode' ),
						$title_tag,
						FusionBuilder::attributes( 'title-shortcode-heading' ),
						$this->render_content( $content ),
						$title_tag
					);
				} else {
					if ( 'right' === $this->args['content_align'] && ! $is_flex_container ) {
						$html = sprintf(
							'<div %s><div %s><div %s></div></div><span %s></span><%s %s>%s</%s></div>',
							FusionBuilder::attributes( 'title-shortcode' ),
							FusionBuilder::attributes( 'title-sep-container' ),
							FusionBuilder::attributes( 'title-shortcode-sep' ),
							FusionBuilder::attributes( 'awb-title-spacer' ),
							$title_tag,
							FusionBuilder::attributes( 'title-shortcode-heading' ),
							$this->render_content( $content ),
							$title_tag
						);
					} elseif ( 'center' === $this->args['content_align'] || $is_flex_container ) {
						$left_classes             = 'title-sep-container title-sep-container-left';
						$right_classes            = 'title-sep-container title-sep-container-right';
						$additional_left_classes  = '';
						$additional_right_classes = '';

						if ( $is_flex_container ) {
							foreach ( [ 'large', 'medium', 'small' ] as $responsive_size ) {
								$key   = 'content_align' . ( 'large' === $responsive_size ? '' : '_' . $responsive_size );
								$value = isset( $this->args[ $key ] ) && '' !== $this->args[ $key ] ? $this->args[ $key ] : $this->args['content_align'];

								if ( 'left' === $value ) {
									$additional_left_classes .= ' fusion-no-' . $responsive_size . '-visibility';
								} elseif ( 'right' === $value ) {
									$additional_right_classes .= ' fusion-no-' . $responsive_size . '-visibility';
								}
							}

							$left_classes  .= $additional_left_classes;
							$right_classes .= $additional_right_classes;
						}

						$html = sprintf(
							'<div %s><div %s><div %s></div></div><span %s></span><%s %s>%s</%s><span %s></span><div %s><div %s></div></div></div>',
							FusionBuilder::attributes( 'title-shortcode' ),
							FusionBuilder::attributes( $left_classes ),
							FusionBuilder::attributes( 'title-shortcode-sep' ),
							FusionBuilder::attributes( 'awb-title-spacer' . $additional_left_classes ),
							$title_tag,
							FusionBuilder::attributes( 'title-shortcode-heading' ),
							$this->render_content( $content ),
							$title_tag,
							FusionBuilder::attributes( 'awb-title-spacer' . $additional_right_classes ),
							FusionBuilder::attributes( $right_classes ),
							FusionBuilder::attributes( 'title-shortcode-sep' )
						);
					} else {
						$html = sprintf(
							'<div %s><%s %s>%s</%s><span %s></span><div %s><div %s></div></div></div>',
							FusionBuilder::attributes( 'title-shortcode' ),
							$title_tag,
							FusionBuilder::attributes( 'title-shortcode-heading' ),
							$this->render_content( $content ),
							$title_tag,
							FusionBuilder::attributes( 'awb-title-spacer' ),
							FusionBuilder::attributes( 'title-sep-container' ),
							FusionBuilder::attributes( 'title-shortcode-sep' )
						);
						fusion_element_rendering_elements( false );
					}
				}

				$style = '<style type="text/css">';

				if ( 'highlight' === $title_type ) {
					if ( $highlight_color ) {
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . ' svg path{stroke:' . fusion_library()->sanitize->color( $highlight_color ) . '!important}';
					}

					if ( $highlight_top_margin && in_array( $highlight_effect, $bottom_highlights, true ) ) {
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . ' svg{margin-top:' . $highlight_top_margin . 'px!important}';
					}

					if ( $highlight_width ) {
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . ' svg path{stroke-width:' . fusion_library()->sanitize->number( $highlight_width ) . '!important}';
					}
				}

				if ( 'rotating' === $title_type && $text_color && ( 'clipIn' === $rotation_effect || 'typeIn' === $rotation_effect ) ) {
					$style .= '.fusion-title.fusion-title-' . $this->title_counter . ' .fusion-animated-texts-wrapper::before{background-color:' . fusion_library()->sanitize->color( $text_color ) . '!important}';
				}

				if ( ! $is_flex_container ) {
					if ( ! ( '' === $this->args['margin_top_mobile'] && '' === $this->args['margin_bottom_mobile'] ) ) {
						$style .= '@media only screen and (max-width:' . $fusion_settings->get( 'content_break_point' ) . 'px) {';
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . '{margin-top:' . $this->args['margin_top_mobile'] . '!important;margin-bottom:' . $this->args['margin_bottom_mobile'] . '!important;}';
						$style .= '}';
					}
				} else {
					// If medium element values are set, use them.
					if ( ! ( '' === $this->args['margin_top_medium'] && '' === $this->args['margin_right_medium'] && '' === $this->args['margin_bottom_medium'] && '' === $this->args['margin_left_medium'] ) ) {
						$style .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_medium' ) . 'px) {';
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . '{margin-top:' . $this->args['margin_top_medium'] . '!important;margin-right:' . $this->args['margin_right_medium'] . '!important;margin-bottom:' . $this->args['margin_bottom_medium'] . '!important; margin-left:' . $this->args['margin_left_medium'] . '!important;}';
						$style .= '}';
					} elseif ( ! ( '' === $this->args['margin_top'] && '' === $this->args['margin_right'] && '' === $this->args['margin_bottom'] && '' === $this->args['margin_left'] ) ) {
						// If no medium element values are set, inherit large ones to make sure that not the content breakpoint media query takes over with mobile values.
						$style .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_medium' ) . 'px) {';
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . '{margin-top:' . $this->args['margin_top'] . '!important; margin-right:' . $this->args['margin_right'] . '!important;margin-bottom:' . $this->args['margin_bottom'] . '!important;margin-left:' . $this->args['margin_left'] . '!important;}';
						$style .= '}';
					}

					if ( ! ( '' === $this->args['margin_top_small'] && '' === $this->args['margin_right_small'] && '' === $this->args['margin_bottom_small'] && '' === $this->args['margin_left_small'] ) && ! ( '0px' === $this->args['margin_top_small'] && '20px' === $this->args['margin_bottom_small'] && '' === $this->args['margin_right_small'] && '' === $this->args['margin_left_small'] ) ) {
						$style .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_small' ) . 'px) {';
						$style .= '.fusion-title.fusion-title-' . $this->title_counter . '{margin-top:' . $this->args['margin_top_small'] . '!important; margin-right:' . $this->args['margin_right_small'] . '!important;margin-bottom:' . $this->args['margin_bottom_small'] . '!important; margin-left:' . $this->args['margin_left_small'] . '!important;}';
						$style .= '}';
					}
				}

				if ( 'text' === $title_type && 'on' === $title_link ) {
					if ( $link_color ) {
						$style .= '.fusion-title.fusion-title-text.fusion-title-' . $this->title_counter . ' a{color:' . fusion_library()->sanitize->color( $link_color ) . '}';
					}

					if ( $link_hover_color ) {
						$style .= '.fusion-title.fusion-title-text.fusion-title-' . $this->title_counter . ' a:hover{color:' . fusion_library()->sanitize->color( $link_hover_color ) . '}';
					}
				}

				$style .= '</style>';

				$html = $style . $html;

				$this->title_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_title_content', $html, $args );

			}

			/**
			 * Sets style for text shadow if set.
			 *
			 * @access public
			 * @since 3.1
			 * @return void
			 */
			public function set_text_shadow_style() {
				$this->args['text_shadow_styles'] = '';

				if ( 'yes' === $this->args['text_shadow'] ) {
					$text_shadow_styles = Fusion_Builder_Text_Shadow_Helper::get_text_shadow_styles(
						[
							'text_shadow_horizontal' => $this->args['text_shadow_horizontal'],
							'text_shadow_vertical'   => $this->args['text_shadow_vertical'],
							'text_shadow_blur'       => $this->args['text_shadow_blur'],
							'text_shadow_color'      => $this->args['text_shadow_color'],
						]
					);

					if ( 'yes' === $this->args['gradient_font'] ) {
						$this->args['text_shadow_styles'] = 'filter:drop-shadow(' . esc_attr( trim( $text_shadow_styles ) ) . ');';
					} else {
						$this->args['text_shadow_styles'] = 'text-shadow:' . esc_attr( trim( $text_shadow_styles ) ) . ';';
					}
				}
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
						'class'          => 'fusion-title title fusion-title-' . $this->title_counter,
						'style'          => '',
						'data-highlight' => '',
					]
				);

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( false !== strpos( $this->args['style_type'], 'underline' ) ) {
					$styles = explode( ' ', $this->args['style_type'] );

					foreach ( $styles as $style ) {
						$attr['class'] .= ' sep-' . $style;
					}

					if ( $this->args['sep_color'] ) {
						$attr['style'] = 'border-bottom-color:' . $this->args['sep_color'] . ';';
					}
				} elseif ( false !== strpos( $this->args['style_type'], 'none' ) ) {
					$attr['class'] .= ' fusion-sep-none';
				}

				if ( 'center' === $this->args['content_align'] ) {
					$attr['class'] .= ' fusion-title-center';
				}

				if ( $this->args['title_type'] ) {
					$attr['class'] .= ' fusion-title-' . $this->args['title_type'];
				}

				if ( 'text' !== $this->args['title_type'] && $this->args['loop_animation'] ) {
					$attr['class'] .= ' fusion-loop-' . $this->args['loop_animation'];
				}

				if ( 'rotating' === $this->args['title_type'] && $this->args['rotation_effect'] ) {
					$attr['class'] .= ' fusion-title-' . $this->args['rotation_effect'];
				}

				if ( 'highlight' === $this->args['title_type'] && $this->args['highlight_effect'] ) {
					$attr['data-highlight'] .= $this->args['highlight_effect'];
					$attr['class']          .= ' fusion-highlight-' . $this->args['highlight_effect'];

				}

				$title_size = 'div';
				if ( '1' == $this->args['size'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$title_size = 'one';
				} elseif ( '2' == $this->args['size'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$title_size = 'two';
				} elseif ( '3' == $this->args['size'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$title_size = 'three';
				} elseif ( '4' == $this->args['size'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$title_size = 'four';
				} elseif ( '5' == $this->args['size'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$title_size = 'five';
				} elseif ( '6' == $this->args['size'] ) { // phpcs:ignore WordPress.PHP.StrictComparisons
					$title_size = 'six';
				}

				$attr['class'] .= ' fusion-title-size-' . $title_size;

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['font_size'] ) . ';';
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( '' === $this->args['margin_top'] && '' === $this->args['margin_bottom'] ) {
					$attr['style'] .= ' margin-top:0px; margin-bottom:0px';
					$attr['class'] .= ' fusion-title-default-margin';
				}

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
			 * Builds the heading attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function heading_attr() {
				$fusion_settings = awb_get_fusion_settings();

				$attr = [
					'class' => 'title-heading-' . $this->args['content_align'],
					'style' => '',
				];

				if ( 'div' === $this->args['size'] ) {
					$attr['class'] .= ' title-heading-tag';
				}

				if ( fusion_element_rendering_is_flex() ) {
					if ( ! empty( $this->args['content_align_medium'] ) && $this->args['content_align'] !== $this->args['content_align_medium'] ) {
						$attr['class'] .= ' md-text-align-' . $this->args['content_align_medium'];
					}

					if ( ! empty( $this->args['content_align_small'] ) && $this->args['content_align'] !== $this->args['content_align_small'] ) {
						$attr['class'] .= ' sm-text-align-' . $this->args['content_align_small'];
					}
				}

				$attr['style'] .= Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'title_font' );

				if ( '' !== $this->args['margin_top'] || '' !== $this->args['margin_bottom'] ) {
					$attr['style'] .= 'margin:0;';
				}

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:1em;';
				}

				if ( $this->args['letter_spacing'] ) {
					$attr['style'] .= 'letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['letter_spacing'] ) . ';';
				}

				if ( ! empty( $this->args['text_transform'] ) ) {
					$attr['style'] .= 'text-transform:' . $this->args['text_transform'] . ';';
				}

				if ( $this->args['text_color'] ) {
					$attr['style'] .= 'color:' . fusion_library()->sanitize->color( $this->args['text_color'] ) . ';';
				}

				if ( 'text' === $this->args['title_type'] && 'yes' === $this->args['gradient_font'] ) {
					$attr['style'] .= Fusion_Builder_Gradient_Helper::get_gradient_font_string( $this->args );
					$attr['class'] .= ' awb-gradient-text';
				}

				if ( $this->args['style_tag'] ) {
					$attr['style'] .= $this->args['style_tag'];
				}

				if ( $this->args['responsive_typography'] ) {
					$data           = awb_get_responsive_type_data( $this->args['size'], $this->args['font_size'], $this->args['line_height'] );
					$attr['class'] .= ' ' . $data['class'];
					$attr['style'] .= $data['font_size'];
					$attr['style'] .= $data['min_font_size'];
					$attr['style'] .= $data['line_height'];
				} elseif ( $this->args['line_height'] ) {
					$attr['style'] .= 'line-height:' . fusion_library()->sanitize->size( $this->args['line_height'] ) . ';';
				}

				// Text shadow.
				if ( '' !== $this->args['text_shadow_styles'] ) {
					$attr['style'] .= $this->args['text_shadow_styles'];
				}

				return $attr;

			}

			/**
			 * Builds the rotated text attributes array.
			 *
			 * @access public
			 * @since 2.1
			 * @return array
			 */
			public function rotated_text_attr() {

				$attr = [
					'data-in-effect'   => $this->args['rotation_effect'],
					'class'            => 'fusion-animated-text',
					'data-in-sequence' => 'true',
					'data-out-reverse' => 'true',
				];

				$attr['data-out-effect'] = str_replace( [ 'In', 'Down' ], [ 'Out', 'Up' ], $this->args['rotation_effect'] );

				return $attr;

			}

			/**
			 * Builds the animated text wrapper attributes array.
			 *
			 * @access public
			 * @since 2.1
			 * @return array
			 */
			public function animated_text_wrapper() {
				$attr = [
					'class' => 'fusion-animated-texts-wrapper',
					'style' => '',
				];

				if ( $this->args['animated_text_color'] ) {
					$attr['style'] .= 'color:' . fusion_library()->sanitize->color( $this->args['animated_text_color'] ) . ';';
				}

				if ( $this->args['animated_font_size'] ) {
					if ( $this->args['responsive_typography'] ) {
						$data           = awb_get_responsive_type_data( $this->args['size'], $this->args['animated_font_size'], $this->args['line_height'] );
						$attr['class'] .= ' ' . $data['class'];
						$attr['style'] .= $data['font_size'];
						$attr['style'] .= $data['min_font_size'];
					}

					$attr['style'] .= 'font-size:' . fusion_library()->sanitize->get_value_with_unit( $this->args['animated_font_size'] ) . ';';
				}

				if ( 'highlight' === $this->args['title_type'] ) {
					$attr['class'] = 'fusion-highlighted-text';
				}

				if ( 'rotating' === $this->args['title_type'] ) {
					$attr['data-length'] = $this->animation_length();

					if ( $this->args['display_time'] ) {
						$attr['data-minDisplayTime'] = fusion_library()->sanitize->number( $this->args['display_time'] );
					}

					if ( $this->args['after_text'] || ( ! $this->args['before_text'] && ! $this->args['after_text'] ) ) {
						$attr['style'] .= 'text-align: center;';
					}
				}

				return $attr;
			}

			/**
			 * Get animation length based on effect.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function animation_length() {
				$animation_length = '';

				switch ( $this->args['rotation_effect'] ) {

					case 'flipInX':
					case 'bounceIn':
					case 'zoomIn':
					case 'slideInDown':
					case 'clipIn':
						$animation_length = 'line';
						break;

					case 'lightSpeedIn':
						$animation_length = 'word';
						break;

					case 'rollIn':
					case 'typeIn':
					case 'fadeIn':
						$animation_length = 'char';
						break;
				}

				return $animation_length;
			}

			/**
			 * Builds the separator attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function sep_attr() {

				$attr = [
					'class' => 'title-sep',
				];

				$styles = explode( ' ', $this->args['style_type'] );

				foreach ( $styles as $style ) {
					$attr['class'] .= ' sep-' . $style;
				}

				if ( $this->args['sep_color'] ) {
					$attr['style'] = 'border-color:' . $this->args['sep_color'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function href_attr() {

				$attr = [
					'href'  => $this->args['link_url'],
					'class' => '',
				];

				if ( FusionBuilder()->post_card_data['is_rendering'] && empty( $attr['href'] ) ) {
					$attr['href'] = get_permalink( get_the_ID() );
				}

				$attr['target'] = $this->args['link_target'];

				if ( 'text' === $this->args['title_type'] && 'on' === $this->args['title_link'] ) {
					if ( $this->args['link_color'] ) {
						$attr['class'] .= 'awb-custom-text-color';
					}

					if ( $this->args['link_hover_color'] ) {
						$attr['class'] .= ' awb-custom-text-hover-color';
					}
				}

				// Add additional, custom link attributes correctly formatted to the anchor.
				$attr = fusion_get_link_attributes( $this->args, $attr );

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

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $fusion_settings, $dynamic_css_helpers;

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-title' ], '.fusion-title' );
				$top_margin    = fusion_library()->sanitize->size( $fusion_settings->get( 'title_margin_mobile', 'top' ) ) . '!important';
				$bottom_margin = fusion_library()->sanitize->size( $fusion_settings->get( 'title_margin_mobile', 'bottom' ) ) . '!important';

				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $main_elements ) ]['margin-top']          = $top_margin;
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $main_elements ) ]['margin-bottom']       = $bottom_margin;
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $main_elements ) ]['margin-top']    = $top_margin;
				$css[ $ipad_portrait_media_query ][ $dynamic_css_helpers->implode( $main_elements ) ]['margin-bottom'] = $bottom_margin;

				$elements = array_merge(
					$dynamic_css_helpers->map_selector( $main_elements, ' .title-sep' ),
					$dynamic_css_helpers->map_selector( $main_elements, '.sep-underline' )
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'title_border_color' ) );

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Title settings.
			 */
			public function add_options() {

				return [
					'title_shortcode_section' => [
						'label'       => esc_html__( 'Title', 'fusion-builder' ),
						'description' => '',
						'id'          => 'title_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-H',
						'fields'      => [
							'title_text_transform' => [
								'label'       => esc_attr__( 'Text Transform', 'fusion-builder' ),
								'description' => esc_attr__( 'Choose how the text is displayed.', 'fusion-builder' ),
								'id'          => 'title_text_transform',
								'default'     => '',
								'type'        => 'select',
								'choices'     => [
									''           => esc_attr__( 'Default', 'fusion-builder' ),
									'none'       => esc_attr__( 'None', 'fusion-builder' ),
									'uppercase'  => esc_attr__( 'Uppercase', 'fusion-builder' ),
									'lowercase'  => esc_attr__( 'Lowercase', 'fusion-builder' ),
									'capitalize' => esc_attr__( 'Capitalize', 'fusion-builder' ),
								],
							],
							'title_style_type'     => [
								'label'       => esc_html__( 'Title Separator', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the type of title separator that will display.', 'fusion-builder' ),
								'id'          => 'title_style_type',
								'default'     => 'none',
								'type'        => 'select',
								'transport'   => 'postMessage',
								'choices'     => [
									'single solid'     => esc_html__( 'Single Solid', 'fusion-builder' ),
									'single dashed'    => esc_html__( 'Single Dashed', 'fusion-builder' ),
									'single dotted'    => esc_html__( 'Single Dotted', 'fusion-builder' ),
									'double solid'     => esc_html__( 'Double Solid', 'fusion-builder' ),
									'double dashed'    => esc_html__( 'Double Dashed', 'fusion-builder' ),
									'double dotted'    => esc_html__( 'Double Dotted', 'fusion-builder' ),
									'underline solid'  => esc_html__( 'Underline Solid', 'fusion-builder' ),
									'underline dashed' => esc_html__( 'Underline Dashed', 'fusion-builder' ),
									'underline dotted' => esc_html__( 'Underline Dotted', 'fusion-builder' ),
									'none'             => esc_html__( 'None', 'fusion-builder' ),
								],
							],
							'title_border_color'   => [
								'label'       => esc_html__( 'Title Separator Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the title separators.', 'fusion-builder' ),
								'id'          => 'title_border_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--title_border_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'title_margin'         => [
								'label'       => esc_html__( 'Title Margins', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the margin of the titles. Leave empty to use corresponding heading margins.', 'fusion-builder' ),
								'id'          => 'title_margin',
								'default'     => [
									'top'    => '10px',
									'right'  => '0px',
									'bottom' => '15px',
									'left'   => '0px',
								],
								'transport'   => 'postMessage',
								'type'        => 'spacing',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
								],
							],
							'title_margin_mobile'  => [
								'label'       => esc_html__( 'Title Mobile Margins', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the margin of the titles on mobiles. Leave empty together with desktop margins to use corresponding heading margins.', 'fusion-builder' ),
								'id'          => 'title_margin_mobile',
								'transport'   => 'postMessage',
								'default'     => [
									'top'    => '10px',
									'right'  => '0px',
									'bottom' => '10px',
									'left'   => '0px',
								],
								'type'        => 'spacing',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
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

				Fusion_Dynamic_JS::enqueue_script(
					'jquery-title-textillate',
					FusionBuilder::$js_folder_url . '/library/jquery.textillate.js',
					FusionBuilder::$js_folder_path . '/library/jquery.textillate.js',
					[ 'jquery' ],
					'2.0',
					true
				);

				Fusion_Dynamic_JS::enqueue_script( 'fusion-title' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/title.min.css' );
			}
		}
	}

	new FusionSC_Title();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_title() {
	$fusion_settings = awb_get_fusion_settings();

	$is_builder = ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() );
	$to_link    = '';

	if ( $is_builder ) {
		$to_link = '<span class="fusion-panel-shortcut" data-fusion-option="headers_typography_important_note_info">' . esc_html__( 'Global Options Heading Settings', 'fusion-builder' ) . '</span>';
	} else {
		$to_link = '<a href="' . esc_url( $fusion_settings->get_setting_link( 'headers_typography_important_note_info' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Global Options Heading Settings', 'fusion-builder' ) . '</a>';
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Title',
			[
				'name'            => esc_attr__( 'Title', 'fusion-builder' ),
				'shortcode'       => 'fusion_title',
				'icon'            => 'fusiona-H',
				'preview'         => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-title-preview.php',
				'preview_id'      => 'fusion-builder-block-module-title-preview-template',
				'allow_generator' => true,
				'inline_editor'   => true,
				'help_url'        => 'https://theme-fusion.com/documentation/avada/elements/title-element/',
				'subparam_map'    => [
					'fusion_font_family_title_font'  => 'main_typography',
					'fusion_font_variant_title_font' => 'main_typography',
					'font_size'                      => 'main_typography',
					'line_height'                    => 'main_typography',
					'letter_spacing'                 => 'main_typography',
					'text_transform'                 => 'main_typography',
				],
				'params'          => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Title Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the title type.', 'fusion-builder' ),
						'param_name'  => 'title_type',
						'value'       => [
							'text'      => esc_attr__( 'Text', 'fusion-builder' ),
							'rotating'  => esc_attr__( 'Rotating', 'fusion-builder' ),
							'highlight' => esc_attr__( 'Highlight', 'fusion-builder' ),
						],
						'default'     => 'text',
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Rotation Effect', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the style for rotation text.', 'fusion-builder' ),
						'param_name'  => 'rotation_effect',
						'default'     => 'bounceIn',
						'value'       => [
							'bounceIn'     => esc_attr__( 'Bounce', 'fusion-builder' ),
							'clipIn'       => esc_attr__( 'Clip', 'fusion-builder' ),
							'fadeIn'       => esc_attr__( 'Fade', 'fusion-builder' ),
							'flipInX'      => esc_attr__( 'Flip', 'fusion-builder' ),
							'lightSpeedIn' => esc_attr__( 'Light Speed', 'fusion-builder' ),
							'rollIn'       => esc_attr__( 'Roll', 'fusion-builder' ),
							'typeIn'       => esc_attr__( 'Typing', 'fusion-builder' ),
							'slideInDown'  => esc_attr__( 'Slide Down', 'fusion-builder' ),
							'zoomIn'       => esc_attr__( 'Zoom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'rotating',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Display Time', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the delay of animation between each text in a set. In milliseconds, 1000 = 1 second.', 'fusion-builder' ),
						'param_name'  => 'display_time',
						'value'       => '1200',
						'min'         => '0',
						'max'         => '10000',
						'step'        => '100',
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'rotating',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Highlight Effect', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the highlight effect.', 'fusion-builder' ),
						'param_name'  => 'highlight_effect',
						'default'     => 'circle',
						'value'       => [
							'circle'               => esc_attr__( 'Circle', 'fusion-builder' ),
							'curly'                => esc_attr__( 'Curly', 'fusion-builder' ),
							'underline'            => esc_attr__( 'Underline', 'fusion-builder' ),
							'double'               => esc_attr__( 'Double', 'fusion-builder' ),
							'double_underline'     => esc_attr__( 'Double Underline', 'fusion-builder' ),
							'underline_zigzag'     => esc_attr__( 'Underline Zigzag', 'fusion-builder' ),
							'diagonal_bottom_left' => esc_attr__( 'Diagonal Bottom Left', 'fusion-builder' ),
							'diagonal_top_left'    => esc_attr__( 'Diagonal Top Left', 'fusion-builder' ),
							'strikethrough'        => esc_attr__( 'Strikethrough', 'fusion-builder' ),
							'x'                    => esc_attr__( 'X', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'highlight',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Loop Animation', 'fusion-builder' ),
						'description' => esc_attr__( 'Turn on to loop the animation.', 'fusion-builder' ),
						'param_name'  => 'loop_animation',
						'default'     => 'off',
						'value'       => [
							'on'  => esc_html__( 'On', 'fusion-builder' ),
							'off' => esc_html__( 'Off', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Highlight Shape Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the width of highlight shape.', 'fusion-builder' ),
						'param_name'  => 'highlight_width',
						'value'       => '9',
						'min'         => '1',
						'max'         => '30',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'highlight',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Highlight Top Margin', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the top margin of highlight shape.', 'fusion-builder' ),
						'param_name'  => 'highlight_top_margin',
						'value'       => '0',
						'min'         => '-30',
						'max'         => '30',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'highlight',
								'operator' => '==',
							],
							[
								'element'  => 'highlight_effect',
								'value'    => 'circle',
								'operator' => '!=',
							],
							[
								'element'  => 'highlight_effect',
								'value'    => 'double',
								'operator' => '!=',
							],
							[
								'element'  => 'highlight_effect',
								'value'    => 'diagonal_bottom_left',
								'operator' => '!=',
							],
							[
								'element'  => 'highlight_effect',
								'value'    => 'diagonal_top_left',
								'operator' => '!=',
							],
							[
								'element'  => 'highlight_effect',
								'value'    => 'strikethrough',
								'operator' => '!=',
							],
							[
								'element'  => 'highlight_effect',
								'value'    => 'x',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Before Text', 'fusion-builder' ),
						'description'  => esc_html__( 'Enter before text.', 'fusion-builder' ),
						'param_name'   => 'before_text',
						'value'        => '',
						'group'        => esc_attr__( 'General', 'fusion-builder' ),
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'sortable_text',
						'heading'     => esc_attr__( 'Rotation Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter text for rotation.', 'fusion-builder' ),
						'param_name'  => 'rotation_text',
						'placeholder' => 'Text',
						'add_label'   => 'Add Rotation Text',
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'rotating',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Highlighted Text', 'fusion-builder' ),
						'description'  => esc_html__( 'Enter text which should be highlighted.', 'fusion-builder' ),
						'param_name'   => 'highlight_text',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'title_type',
								'value'    => 'highlight',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'After Text', 'fusion-builder' ),
						'description'  => esc_html__( 'Enter after text.', 'fusion-builder' ),
						'param_name'   => 'after_text',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Title', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert the title text.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Title Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if title text should have a link.', 'fusion-builder' ),
						'param_name'  => 'title_link',
						'value'       => [
							'on'  => esc_attr__( 'On', 'fusion-builder' ),
							'off' => esc_attr__( 'Off', 'fusion-builder' ),
						],
						'default'     => 'off',
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Link URL', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add an URL for the link. E.g: https://example.com.', 'fusion-builder' ),
						'param_name'   => 'link_url',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'title_link',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => __( '_self = open in same window<br />_blank = open in new window.', 'fusion-builder' ),
						'param_name'  => 'link_target',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => '_self',
						'dependency'  => [
							[
								'element'  => 'title_link',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'link_url',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to align the heading left, right or center.', 'fusion-builder' ),
						'param_name'  => 'content_align',
						'responsive'  => [
							'state'         => 'large',
							'default_value' => true,
						],
						'value'       => [
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'HTML Heading Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the heading, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'size',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
						],
						'default'     => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Animated Text Font Size', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description' => sprintf( esc_html__( 'Controls the font size of the animated text. Enter value including any valid CSS unit, ex: 20px. Leave empty if the global font size for the corresponding heading size (h1-h6) should be used: %s.', 'fusion-builder' ), $to_link ),
						'param_name'  => 'animated_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description'      => sprintf( esc_html__( 'Controls the title text typography.  Leave empty if the global typography for the corresponding heading size (h1-h6) should be used: %s.', 'fusion-builder' ), $to_link ),
						'param_name'       => 'main_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'title_font',
							'font-size'      => 'font_size',
							'line-height'    => 'line_height',
							'letter-spacing' => 'letter_spacing',
							'text-transform' => 'text_transform',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Font Color', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description' => sprintf( esc_html__( 'Controls the color of the title, ex: #000. Leave empty if the global color for the corresponding heading size (h1-h6) should be used: %s.', 'fusion-builder' ), $to_link ),
						'param_name'  => 'text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Animated Text Font Color', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description' => sprintf( esc_html__( 'Controls the color of the animated title, ex: #000. Leave empty if the global color for the corresponding heading size (h1-h6) should be used: %s.', 'fusion-builder' ), $to_link ),
						'param_name'  => 'animated_text_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '!=',
							],
						],
					],
					'fusion_text_shadow_placeholder'       => [],
					'fusion_margin_placeholder'            => [
						'param_name' => 'dimensions',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'responsive' => [
							'state' => 'large',
						],
					],
					'fusion_margin_mobile_placeholder'     => [
						'param_name'  => 'margin_mobile',
						'heading'     => esc_attr__( 'Mobile Margin', 'fusion-builder' ),
						'description' => esc_attr__( 'Spacing above and below the title on mobiles. In px, em or %, e.g. 10px.', 'fusion-builder' ),
						'value'       => [
							'margin_top_mobile'    => '',
							'margin_bottom_mobile' => '',
						],
						'dependency'  => [
							[
								'element'  => 'fusion_builder_container',
								'param'    => 'type',
								'value'    => 'flex',
								'operator' => '!=',
							],
						],
					],
					'fusion_gradient_text_placeholder'     => [
						'selector'   => '.fusion-title',
						'dependency' => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Highlight Shape Color', 'fusion-builder' ),
						'description' => esc_html__( 'Controls the color of the highlight shape, ex: #000.', 'fusion-builder' ),
						'param_name'  => 'highlight_color',
						'value'       => '',
						'default'     => 'var(--primary_color)',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'highlight',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Separator', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the kind of the title separator you want to use.', 'fusion-builder' ),
						'param_name'  => 'style_type',
						'value'       => [
							'default'          => esc_attr__( 'Default', 'fusion-builder' ),
							'single solid'     => esc_attr__( 'Single Solid', 'fusion-builder' ),
							'single dashed'    => esc_attr__( 'Single Dashed', 'fusion-builder' ),
							'single dotted'    => esc_attr__( 'Single Dotted', 'fusion-builder' ),
							'double solid'     => esc_attr__( 'Double Solid', 'fusion-builder' ),
							'double dashed'    => esc_attr__( 'Double Dashed', 'fusion-builder' ),
							'double dotted'    => esc_attr__( 'Double Dotted', 'fusion-builder' ),
							'underline solid'  => esc_attr__( 'Underline Solid', 'fusion-builder' ),
							'underline dashed' => esc_attr__( 'Underline Dashed', 'fusion-builder' ),
							'underline dotted' => esc_attr__( 'Underline Dotted', 'fusion-builder' ),
							'none'             => esc_attr__( 'None', 'fusion-builder' ),
						],
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Separator Color', 'fusion-builder' ),
						'param_name'  => 'sep_color',
						'value'       => '',
						'description' => esc_attr__( 'Controls the separator color. ', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'style_type',
								'value'    => 'none',
								'operator' => '!=',
							],
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
						'default'     => $fusion_settings->get( 'title_border_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'description' => esc_attr__( 'Controls the link color.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'title_link',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
						'default'     => $fusion_settings->get( 'link_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Hover Color', 'fusion-builder' ),
						'param_name'  => 'link_hover_color',
						'value'       => '',
						'description' => esc_attr__( 'Controls the link hover color.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'title_link',
								'value'    => 'off',
								'operator' => '!=',
							],
							[
								'element'  => 'title_type',
								'value'    => 'text',
								'operator' => '==',
							],
						],
						'default'     => $fusion_settings->get( 'primary_color' ),
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-title',
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					'fusion_sticky_visibility_placeholder' => [],
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
add_action( 'fusion_builder_before_init', 'fusion_element_title' );
