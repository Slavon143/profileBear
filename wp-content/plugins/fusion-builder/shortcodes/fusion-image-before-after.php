<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.5
 */

if ( fusion_is_element_enabled( 'fusion_image_before_after' ) ) {

	if ( ! class_exists( 'FusionSC_ImageBeforeAfter' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @package fusion-builder
		 * @since 1.5
		 */
		class FusionSC_ImageBeforeAfter extends Fusion_Element {

			/**
			 * The before-after counter.
			 *
			 * @access private
			 * @since 1.5
			 * @var int
			 */
			private $before_after_counter = 1;

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.5
			 * @var array
			 */
			protected $args = [];

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.5
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_image-before-after-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_image-before-after-wrapper', [ $this, 'wrapper_attr' ] );
				add_filter( 'fusion_attr_image-switch-link', [ $this, 'switch_link_attr' ] );
				add_filter( 'fusion_attr_image-before-image', [ $this, 'before_image_attr' ] );
				add_filter( 'fusion_attr_image-after-image', [ $this, 'after_image_attr' ] );
				add_filter( 'fusion_attr_before-after-overlay', [ $this, 'before_after_overlay' ] );
				add_filter( 'fusion_attr_before-after-handle-type', [ $this, 'handle_type_attr' ] );

				add_shortcode( 'fusion_image_before_after', [ $this, 'render' ] );

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
					'type'                => ( '' !== $fusion_settings->get( 'before_after_type' ) ) ? strtolower( $fusion_settings->get( 'before_after_type' ) ) : 'before_after',
					'before_image'        => '',
					'before_image_id'     => '',
					'before_label'        => '',
					'after_image'         => '',
					'after_image_id'      => '',
					'after_label'         => '',
					'link'                => '',
					'target'              => '_self',
					'max_width'           => '',
					'font_size'           => $fusion_settings->get( 'before_after_font_size' ),
					'accent_color'        => $fusion_settings->get( 'before_after_accent_color' ),
					'label_placement'     => $fusion_settings->get( 'before_after_label_placement' ),
					'handle_type'         => $fusion_settings->get( 'before_after_handle_type' ),
					'handle_bg'           => $fusion_settings->get( 'before_after_handle_bg' ),
					'handle_color'        => $fusion_settings->get( 'before_after_handle_color' ),
					'alignment'           => '',
					'transition_time'     => $fusion_settings->get( 'before_after_transition_time' ),
					'offset'              => $fusion_settings->get( 'before_after_offset' ),
					'orientation'         => $fusion_settings->get( 'before_after_orientation' ),
					'handle_movement'     => $fusion_settings->get( 'before_after_handle_movement' ),
					'bordercolor'         => $fusion_settings->get( 'before_after_border_color' ),
					'borderradius'        => intval( $fusion_settings->get( 'before_after_border_radius' ) ) . 'px',
					'bordersize'          => $fusion_settings->get( 'before_after_border_size' ),
					'margin_bottom'       => '',
					'margin_left'         => '',
					'margin_right'        => '',
					'margin_top'          => '',
					'hide_on_mobile'      => fusion_builder_default_visibility( 'string' ),
					'animation_type'      => '',
					'animation_direction' => 'left',
					'animation_speed'     => '',
					'animation_offset'    => $fusion_settings->get( 'animation_offset' ),
					'class'               => '',
					'id'                  => '',
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
					'before_after_type'            => 'type',
					'before_after_font_size'       => 'font_size',
					'before_after_accent_color'    => 'accent_color',
					'before_after_label_placement' => 'label_placement',
					'before_after_handle_type'     => 'handle_type',
					'before_after_handle_bg'       => 'handle_bg',
					'before_after_handle_color'    => 'handle_color',
					'before_after_transition_time' => 'transition_time',
					'before_after_offset'          => 'offset',
					'before_after_orientation'     => 'orientation',
					'before_after_handle_movement' => 'handle_movement',
					'before_after_border_radius'   => 'borderradius',
					'before_after_border_size'     => 'bordersize',
					'before_after_border_color'    => 'stylecolor',
					'animation_offset'             => 'animation_offset',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.5
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render( $args, $content = '' ) {

				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_image_before_after' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_image_before_after', $args );

				$defaults['offset']        = $defaults['offset'] / 100;
				$defaults['font_size']     = FusionBuilder::validate_shortcode_attr_value( $defaults['font_size'], 'px' );
				$defaults['max_width']     = FusionBuilder::validate_shortcode_attr_value( $defaults['max_width'], 'px' );
				$defaults['bordersize']    = FusionBuilder::validate_shortcode_attr_value( $defaults['bordersize'], 'px' );
				$defaults['borderradius']  = FusionBuilder::validate_shortcode_attr_value( $defaults['borderradius'], 'px' );
				$defaults['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_bottom'], 'px' );
				$defaults['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_left'], 'px' );
				$defaults['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_right'], 'px' );
				$defaults['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $defaults['margin_top'], 'px' );

				$this->args = $defaults;

				$styles = $html = '';

				if ( isset( $this->args['handle_color'] ) && 'before_after' === $this->args['type'] ) {
					$color   = Fusion_Sanitize::color( $this->args['handle_color'] );
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle {';
					$styles .= 'border-color:' . $color . ';';
					$styles .= '}';
					if ( 'horizontal' === $this->args['orientation'] ) {
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-left-arrow {';
						$styles .= 'border-right-color:' . $color . ';';
						$styles .= '}';
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-right-arrow {';
						$styles .= 'border-left-color:' . $color . ';';
						$styles .= '}';
						if ( isset( $this->args['handle_type'] ) && 'diamond' === $this->args['handle_type'] ) {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-diamond .fusion-image-before-after-left-arrow::before {';
							$styles .= 'border-color:' . $color . ' !important;';
							$styles .= '}';
						} elseif ( isset( $this->args['handle_type'] ) && 'circle' === $this->args['handle_type'] ) {
							$color_obj = Fusion_Color::new_color( $color );

							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle {';
							$styles .= 'background:' . $color . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-left-arrow::before {';
							$styles .= 'border-color:' . $color_obj->getNew( 'alpha', $color_obj->alpha * 0.6 )->to_css_var_or_rgba() . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-left-arrow {';
							$styles .= 'border-right-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-right-arrow {';
							$styles .= 'border-left-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
						}
					} elseif ( 'vertical' === $this->args['orientation'] ) {
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-up-arrow {';
						$styles .= 'border-bottom-color:' . $color . ';';
						$styles .= '}';
						$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-down-arrow {';
						$styles .= 'border-top-color:' . $color . ';';
						$styles .= '}';
						if ( isset( $this->args['handle_type'] ) && 'diamond' === $this->args['handle_type'] ) {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-diamond .fusion-image-before-after-down-arrow::before {';
							$styles .= 'border-color:' . $color . ' !important;';
							$styles .= '}';
						} elseif ( isset( $this->args['handle_type'] ) && 'circle' === $this->args['handle_type'] ) {
							$color_obj = Fusion_Color::new_color( $color );

							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle {';
							$styles .= 'background:' . $color . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-down-arrow::before {';
							$styles .= 'border-color:' . $color_obj->getNew( 'alpha', $color_obj->alpha * 0.6 )->to_css_var_or_rgba() . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-up-arrow {';
							$styles .= 'border-bottom-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle-circle .fusion-image-before-after-down-arrow {';
							$styles .= 'border-top-color:' . Fusion_Helper::fusion_auto_calculate_accent_color( $color ) . ' !important;';
							$styles .= '}';
						}
					}
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle::after {';
					$styles .= 'background:' . $color . ';';
					if ( 'vertical' !== $this->args['orientation'] ) {
						$styles .= 'box-shadow: 0 3px 0 ' . $color . ', 0 0 12px rgba(51,51,51,.5);';
					}
					$styles .= '}';
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle::before {';
					$styles .= 'background:' . $color . ';';
					if ( 'vertical' !== $this->args['orientation'] ) {
						$styles .= 'box-shadow: 0 3px 0 ' . $color . ', 0 0 12px rgba(51,51,51,.5);';
					}
					$styles .= '}';

				}

				if ( isset( $this->args['handle_bg'] ) && 'before_after' === $this->args['type'] ) {
					$bg_color = Fusion_Sanitize::color( $this->args['handle_bg'] );
					if ( 'circle' !== $this->args['handle_type'] && 'arrows' !== $this->args['handle_type'] ) {
						if ( 'diamond' !== $this->args['handle_type'] ) {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-handle {';
							$styles .= 'background:' . $bg_color . ';';
							$styles .= '}';
						} else {
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-down-arrow:before,';
							$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-left-arrow:before {';
							$styles .= 'background:' . $bg_color . ';';
							$styles .= '}';
						}
					}
				}

				if ( isset( $this->args['font_size'] ) && 'before_after' === $this->args['type'] ) {
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
					$styles .= ',.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					if ( 'out-image-up-down' === $this->args['label_placement'] ) {
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					}
					$styles .= '{';
					$styles .= 'font-size:' . $this->args['font_size'] . ';';
					$styles .= '}';
				}

				if ( isset( $this->args['accent_color'] ) && 'before_after' === $this->args['type'] ) {

					$color     = Fusion_Sanitize::color( $this->args['accent_color'] );
					$color_obj = Fusion_Color::new_color( $color );
					$styles   .= '.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
					$styles   .= ',.fusion-image-before-after-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					if ( 'out-image-up-down' === $this->args['label_placement'] ) {
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-before-label:before';
						$styles .= ',.fusion-image-before-after-wrapper-' . $this->before_after_counter . ' .fusion-image-before-after-after-label:before';
					}
					$styles .= '{';
					$styles .= 'color:' . $color . ';';
					if ( 'out-image-up-down' !== $this->args['label_placement'] ) {
						$styles .= 'background:' . $color_obj->getNew( 'alpha', $color_obj->alpha * 0.15 )->to_css_var_or_rgba() . ';';
					} else {
						$styles .= 'background:transparent;';
					}
					$styles .= '}';
				}

				if ( 'switch' === $this->args['type'] && isset( $this->args['transition_time'] ) ) {
					$styles .= '.fusion-image-switch.fusion-image-before-after-' . $this->before_after_counter . ' img{';
					$styles .= 'transition: ' . $this->args['transition_time'] . 's ease-in-out;';
					$styles .= '}';
				}

				$add_border_size   = ( '0' !== $this->args['bordersize'] && 0 !== $this->args['bordersize'] && '0px' !== $this->args['bordersize'] );
				$add_border_radius = ( '0' !== $this->args['borderradius'] && 0 !== $this->args['borderradius'] && '0px' !== $this->args['borderradius'] );
				if ( $add_border_size || $add_border_radius ) {
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . ':not(.fusion-image-switch).initialized,';
					$styles .= '.fusion-image-before-after-' . $this->before_after_counter . '.fusion-image-switch img{';

					if ( $add_border_size ) {
						$styles .= 'border: ' . $this->args['bordersize'] . ' solid ' . $this->args['bordercolor'] . ';';
					}

					if ( $add_border_radius ) {
						$styles .= 'border-radius:' . $this->args['borderradius'] . ';';
					}

					$styles .= '}';
				}

				if ( '' !== $styles ) {
					$style_tag = '<style type="text/css">' . $styles . '</style>';
				}

				$html .= '<div ' . FusionBuilder::attributes( 'image-before-after-wrapper' ) . '>';

				if ( is_rtl() && 'vertical' !== $this->args['orientation'] ) {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-after-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['after_label'] ) . '"></div>';
					}
				} else {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-before-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['before_label'] ) . '"></div>';
					}
				}

				$html .= '<div ' . FusionBuilder::attributes( 'image-before-after-shortcode' ) . '>';
				if ( is_rtl() && 'vertical' !== $this->args['orientation'] ) {
					if ( isset( $this->args['after_image'] ) ) {

						$html .= '<img ' . FusionBuilder::attributes( 'image-after-image' ) . '>';
					}

					if ( isset( $this->args['before_image'] ) ) {
						$html .= '<img ' . FusionBuilder::attributes( 'image-before-image' ) . '>';
					}
				} else {

					if ( 'before_after' !== $this->args['type'] && ! empty( $this->args['link'] ) ) {
						$html .= '<a ' . FusionBuilder::attributes( 'image-switch-link' ) . '>';
					}

					if ( isset( $this->args['before_image'] ) ) {
						$html .= '<img ' . FusionBuilder::attributes( 'image-before-image' ) . '>';
					}

					if ( isset( $this->args['after_image'] ) ) {

						$html .= '<img ' . FusionBuilder::attributes( 'image-after-image' ) . '>';
					}

					if ( 'before_after' !== $this->args['type'] ) {
						$html .= '</a>';
					}
				}

				if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && ( 'image-centered' === $this->args['label_placement'] || 'image-up-down' === $this->args['label_placement'] ) ) {
					$html .= '<div ' . FusionBuilder::attributes( 'before-after-overlay' ) . '>';
					$html .= '<div class="fusion-image-before-after-before-label" data-content="' . esc_attr( $this->args['before_label'] ) . '"></div>';
					$html .= '<div class="fusion-image-before-after-after-label" data-content="' . esc_attr( $this->args['after_label'] ) . '"></div>';
					$html .= '</div>';
				}

				if ( 'before_after' === $this->args['type'] ) {
					$before_direction = ( 'vertical' === $this->args['orientation'] ? 'down' : 'left' );
					$after_direction  = ( 'vertical' === $this->args['orientation'] ? 'up' : 'right' );
					$html            .= '<div ' . FusionBuilder::attributes( 'before-after-handle-type' ) . '>';
					$html            .= '<span class="fusion-image-before-after-' . $before_direction . '-arrow"></span>';
					$html            .= '<span class="fusion-image-before-after-' . $after_direction . '-arrow"></span>';
					$html            .= '</div>';
				}

				$html .= '</div>';

				if ( is_rtl() && 'vertical' !== $this->args['orientation'] ) {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-before-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['before_label'] ) . '"></div>';
					}
				} else {
					if ( '' !== $this->args['before_label'] && '' !== $this->args['after_label'] && 'before_after' === $this->args['type'] && 'out-image-up-down' === $this->args['label_placement'] ) {
						$html .= '<div class="fusion-image-before-after-after-label before-after-label-out-image-up-down" data-content="' . esc_attr( $this->args['after_label'] ) . '"></div>';
					}
				}

				$html .= '</div>';

				$this->before_after_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_image_before_after_content', $style_tag . $html, $args );

			}

			/**
			 * Builds the before image attributes array.
			 *
			 * @access public
			 * @since 2.2
			 * @return array
			 */
			public function switch_link_attr() {
				$attr = [
					'class'  => 'fusion-image-switch-link',
					'href'   => $this->args['link'],
					'target' => $this->args['target'],
				];

				if ( '_blank' === $this->args['target'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}

				return $attr;
			}

			/**
			 * Builds the before image attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function before_image_attr() {

				$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->args['before_image_id'], $this->args['before_image'] );

				$alt = ( isset( $image_data['alt'] ) && $image_data['alt'] ) ? $image_data['alt'] : $this->args['before_label'];

				if ( isset( $image_data['url'] ) ) {
					$this->args['before_image'] = $image_data['url'];
				}

				$attr = [
					'alt'   => $alt,
					'class' => ( 'before_after' === $this->args['type'] ? 'fusion-image-before-after-before' : 'fusion-image-switch-before' ),
					'src'   => $this->args['before_image'],
				];

				if ( isset( $image_data['width'] ) && $image_data['width'] ) {
					$attr['width'] = $image_data['width'];
				}
				if ( isset( $image_data['height'] ) && $image_data['height'] ) {
					$attr['height'] = $image_data['height'];
				}
				$attr = fusion_library()->images->lazy_load_attributes( $attr, $this->args['before_image_id'] );

				return $attr;
			}

			/**
			 * Builds the after image attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function after_image_attr() {

				$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->args['after_image_id'], $this->args['after_image'] );

				$alt = ( isset( $image_data['alt'] ) && $image_data['alt'] ) ? $image_data['alt'] : $this->args['after_label'];

				if ( isset( $image_data['url'] ) ) {
					$this->args['after_image'] = $image_data['url'];
				}

				$attr = [
					'alt'   => $alt,
					'class' => ( 'before_after' === $this->args['type'] ? 'fusion-image-before-after-after' : 'fusion-image-switch-after' ),
					'src'   => $this->args['after_image'],
				];

				if ( isset( $image_data['width'] ) && $image_data['width'] ) {
					$attr['width'] = $image_data['width'];
				}
				if ( isset( $image_data['height'] ) && $image_data['height'] ) {
					$attr['height'] = $image_data['height'];
				}
				$attr = fusion_library()->images->lazy_load_attributes( $attr, $this->args['after_image_id'] );

				return $attr;
			}

			/**
			 * Builds the overlay attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function before_after_overlay() {
				$attr = [
					'class' => 'fusion-image-before-after-overlay',
				];

				if ( $this->args['label_placement'] ) {
					$attr['class'] .= ' before-after-overlay-' . $this->args['label_placement'];
				}

				return $attr;
			}

			/**
			 * Builds the wrapper attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function wrapper_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-image-before-after-wrapper',
					]
				);

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['orientation'] && 'before_after' === $this->args['type'] ) {
					$attr['class'] .= ' fusion-image-before-after-' . $this->args['orientation'];
				}

				if ( $this->args['type'] ) {
					$attr['class'] .= ' type-' . $this->args['type'];
				}

				if ( $this->args['alignment'] ) {
					$attr['class'] .= ' has-alignment';
					$attr['class'] .= ' align-' . $this->args['alignment'];
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['class'] .= ' fusion-image-before-after-wrapper-' . $this->before_after_counter;

				return $attr;
			}

			/**
			 * Builds the handle type attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function handle_type_attr() {

				$attr = [
					'class' => 'fusion-image-before-after-handle',
				];

				if ( $this->args['handle_type'] ) {
					$attr['class'] .= ' fusion-image-before-after-handle-' . $this->args['handle_type'];
				}

				return $attr;

			}

			/**
			 * Builds the prent attributes array.
			 *
			 * @access public
			 * @since 1.5
			 * @return array
			 */
			public function attr() {

				$attr = [
					'class' => 'fusion-image-before-after-element',
					'style' => '',
				];

				if ( 'switch' === $this->args['type'] ) {
					$attr['class'] .= ' fusion-image-switch';
				} elseif ( 'before_after' === $this->args['type'] ) {
					$attr['class'] .= ' fusion-image-before-after fusion-image-before-after-container';

					if ( $this->args['offset'] || 0 === $this->args['offset'] ) {
						$attr['data-offset'] = $this->args['offset'];
					}

					if ( $this->args['orientation'] ) {
						$attr['data-orientation'] = $this->args['orientation'];
					}
					if ( $this->args['handle_movement'] ) {
						if ( 'drag_click' === $this->args['handle_movement'] ) {
							$attr['data-move-with-handle-only'] = 'true';
							$attr['data-click-to-move']         = 'true';
						} elseif ( 'drag' === $this->args['handle_movement'] ) {
							$attr['data-move-with-handle-only'] = 'true';
						} elseif ( 'hover' === $this->args['handle_movement'] ) {
							$attr['data-move-slider-on-hover'] = 'true';
						}
					}
				}

				if ( $this->args['max_width'] ) {
					$attr['style'] .= 'width:100%;max-width:' . $this->args['max_width'] . ';';
				}

				$attr['class'] .= ' fusion-image-before-after-' . $this->before_after_counter;

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.5
			 * @return array $sections Image Before & Afte settings.
			 */
			public function add_options() {

				return [
					'image_before_after_shortcode_section' => [
						'label'       => __( ' Image Before & After', 'fusion-builder' ),
						'description' => '',
						'id'          => 'image_before_after_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-object-ungroup',
						'fields'      => [
							'before_after_type'            => [
								'label'       => esc_html__( 'Effect Type', 'fusion-builder' ),
								'description' => esc_html__( 'Select which type of effect your before and after image uses. "Slide" provides a handle to move back and forth while "Fade" changes the image on mouse hover.', 'fusion-builder' ),
								'id'          => 'before_after_type',
								'default'     => 'before_after',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'before_after' => esc_attr__( 'Slide', 'fusion-builder' ),
									'switch'       => esc_attr__( 'Fade', 'fusion-builder' ),
								],
							],
							'before_after_font_size'       => [
								'type'            => 'slider',
								'label'           => esc_attr__( 'Label Font Size', 'fusion-builder' ),
								'description'     => esc_attr__( 'Controls the font size of the label text. Note: font family is controlled by body font in Global Options.', 'fusion-builder' ),
								'id'              => 'before_after_font_size',
								'default'         => '14',
								'transport'       => 'postMessage',
								'choices'         => [
									'min'  => '10',
									'max'  => '100',
									'step' => '1',
								],
								'soft_dependency' => true,
							],
							'before_after_accent_color'    => [
								'label'           => esc_html__( 'Label Accent Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the label background and text. Text takes 100% of this color, background takes a % of it.', 'fusion-builder' ),
								'id'              => 'before_after_accent_color',
								'default'         => 'var(--awb-color1)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'before_after_label_placement' => [
								'label'           => esc_html__( 'Label Placement', 'fusion-builder' ),
								'description'     => esc_html__( 'Choose if labels are on top of the image and centered, on top of the image up & down or outside of the image up & down.', 'fusion-builder' ),
								'id'              => 'before_after_label_placement',
								'default'         => 'image-centered',
								'type'            => 'select',
								'transport'       => 'postMessage',
								'choices'         => [
									'image-centered'    => esc_html__( 'Image Centered', 'fusion-builder' ),
									'image-up-down'     => esc_html__( 'Image Up & Down', 'fusion-builder' ),
									'out-image-up-down' => esc_html__( 'Outside Image Up & Down', 'fusion-builder' ),
								],
								'soft_dependency' => true,
							],
							'before_after_handle_type'     => [
								'label'           => esc_html__( 'Handle Design Style', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the design of the handle used to change the before and after image.', 'fusion-builder' ),
								'id'              => 'before_after_handle_type',
								'default'         => 'default',
								'type'            => 'select',
								'transport'       => 'postMessage',
								'choices'         => [
									'default'   => esc_html__( 'Circle With Arrows', 'fusion-builder' ),
									'square'    => esc_html__( 'Square With Arrows', 'fusion-builder' ),
									'rectangle' => esc_html__( 'Rectangle With Arrows', 'fusion-builder' ),
									'arrows'    => esc_html__( 'Arrows', 'fusion-builder' ),
									'diamond'   => esc_html__( 'Diamond', 'fusion-builder' ),
									'circle'    => esc_html__( 'Single Circle', 'fusion-builder' ),
								],
								'soft_dependency' => true,
							],
							'before_after_handle_color'    => [
								'label'           => esc_html__( 'Handle Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the before and after image handle line and arrows. ex: #ffffff.', 'fusion-builder' ),
								'id'              => 'before_after_handle_color',
								'default'         => 'var(--awb-color1)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'before_after_handle_bg'       => [
								'label'           => esc_html__( 'Handle Background Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the background color of the before and after image handle switch. ex: #000000.', 'fusion-builder' ),
								'id'              => 'before_after_handle_bg',
								'default'         => 'rgba(255,255,255,0)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'before_after_offset'          => [
								'label'           => esc_html__( 'Handle Offset', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls where the handle will be positioned on page load allowing you to control how much of each image displays by default. In percentage.', 'fusion-builder' ),
								'id'              => 'before_after_offset',
								'default'         => '50',
								'type'            => 'slider',
								'transport'       => 'postMessage',
								'choices'         => [
									'min'  => '0',
									'max'  => '100',
									'step' => '1',
								],
								'soft_dependency' => true,
							],
							'before_after_orientation'     => [
								'label'           => esc_html__( 'Handle Orientation', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the position of the before and after image handle.', 'fusion-builder' ),
								'id'              => 'before_after_orientation',
								'default'         => 'horizontal',
								'type'            => 'radio-buttonset',
								'transport'       => 'postMessage',
								'choices'         => [
									'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
									'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
								],
								'soft_dependency' => true,
							],
							'before_after_handle_movement' => [
								'label'           => esc_html__( 'Handle Movement Control', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls how the viewer interacts with the image handler. The image handle can use Drag & Click, Drag Only, or Hover.', 'fusion-builder' ),
								'id'              => 'before_after_handle_movement',
								'default'         => 'drag_click',
								'type'            => 'radio-buttonset',
								'transport'       => 'postMessage',
								'choices'         => [
									'drag_click' => esc_attr__( 'Drag & Click', 'fusion-builder' ),
									'drag'       => esc_attr__( 'Drag Only', 'fusion-builder' ),
									'hover'      => esc_attr__( 'Hover', 'fusion-builder' ),
								],
								'soft_dependency' => true,
							],
							'before_after_transition_time' => [
								'label'           => esc_html__( 'Image Fade Transition Speed', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the speed of the fade transition on mouse hover. In seconds.', 'fusion-builder' ),
								'id'              => 'before_after_transition_time',
								'default'         => '0.5',
								'type'            => 'slider',
								'transport'       => 'postMessage',
								'choices'         => [
									'min'  => '0',
									'max'  => '1',
									'step' => '0.1',
								],
								'soft_dependency' => true,
							],
							'before_after_border_size'     => [
								'label'       => esc_html__( 'Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the image before & after element.', 'fusion-builder' ),
								'id'          => 'before_after_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'before_after_border_color'    => [
								'label'           => esc_html__( 'Border Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border color of the image before & after element.', 'fusion-builder' ),
								'id'              => 'before_after_border_color',
								'default'         => 'var(--awb-color3)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'before_after_border_radius'   => [
								'label'           => esc_html__( 'Border Radius', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border radius of the image before & after element.', 'fusion-builder' ),
								'id'              => 'before_after_border_radius',
								'default'         => '0px',
								'type'            => 'dimension',
								'choices'         => [ 'px', '%' ],
								'transport'       => 'postMessage',
								'soft_dependency' => true,
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
					'jquery-event-move',
					FusionBuilder::$js_folder_url . '/library/jquery.event.move.js',
					FusionBuilder::$js_folder_path . '/library/jquery.event.move.js',
					[ 'jquery' ],
					'2.0',
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-image-before-after',
					FusionBuilder::$js_folder_url . '/general/fusion-image-before-after.js',
					FusionBuilder::$js_folder_path . '/general/fusion-image-before-after.js',
					[ 'jquery', 'jquery-event-move' ],
					'1.0',
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/image-before-after.min.css' );
			}
		}
	}

	new FusionSC_ImageBeforeAfter();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_image_before_after() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_ImageBeforeAfter',
			[
				'name'       => __( 'Image Before & After', 'fusion-builder' ),
				'shortcode'  => 'fusion_image_before_after',
				'icon'       => 'fusiona-object-ungroup',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-image-before-after-preview.php',
				'preview_id' => 'fusion-builder-block-module-image-before-after-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/image-before-after-element/',
				'params'     => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Effect Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Select which type of effect your before and after image uses. “Slide” provides a handle to move back and forth while “Fade” changes the image on mouse hover.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => '',
						'value'       => [
							''             => esc_attr__( 'Default', 'fusion-builder' ),
							'before_after' => esc_attr__( 'Slide', 'fusion-builder' ),
							'switch'       => esc_attr__( 'Fade', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'Before Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload a before image to display.', 'fusion-builder' ),
						'param_name'   => 'before_image',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Before Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Before Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'before_image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Before Image Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Add text that will be displayed as a label on the before image when hovered. If left empty, no label will show.', 'fusion-builder' ),
						'param_name'  => 'before_label',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'before_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'         => 'upload',
						'heading'      => esc_attr__( 'After Image', 'fusion-builder' ),
						'description'  => esc_attr__( 'Upload an after image to display.', 'fusion-builder' ),
						'param_name'   => 'after_image',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'After Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'After Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'after_image_id',
						'value'       => '',
						'hidden'      => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'After Image Label', 'fusion-builder' ),
						'description' => esc_attr__( 'Add text that will be displayed as a label on the after image when hovered. If left empty, no label will show.', 'fusion-builder' ),
						'param_name'  => 'after_label',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'after_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Label Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size of the label text. In Pixels. Note: font family is controlled by body font in Global Options.', 'fusion-builder' ),
						'param_name'  => 'font_size',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => intval( $fusion_settings->get( 'before_after_font_size' ) ),
						'value'       => '',
						'choices'     => [
							'min'  => '10',
							'max'  => '100',
							'step' => '1',
						],
						'dependency'  => [
							[
								'element'  => 'after_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_label',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'after_label',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Label Accent Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the label background and text. Text takes 100% of this color, background takes a % of it.', 'fusion-builder' ),
						'param_name'  => 'accent_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'before_after_accent_color' ),
						'dependency'  => [
							[
								'element'  => 'after_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_label',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'after_label',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Label Placement', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if labels are on top of the image and centered, on top of the image up & down or outside of the image up & down.', 'fusion-builder' ),
						'param_name'  => 'label_placement',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''                  => __( 'Default', 'fusion-builder' ),
							'image-centered'    => __( 'Image Centered', 'fusion-builder' ),
							'image-up-down'     => __( 'Image Up & Down', 'fusion-builder' ),
							'out-image-up-down' => __( 'Outside Image Up & Down', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'after_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_label',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'after_label',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Image Fade Transition Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the speed of the fade transition on mouse hover. In seconds.', 'fusion-builder' ),
						'param_name'  => 'transition_time',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'before_after_transition_time' ),
						'min'         => '0',
						'max'         => '1',
						'step'        => '.1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'before_after',
								'operator' => '!=',
							],
						],

					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Link URL', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the URL the item will link to, ex: http://example.com.', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'dynamic_data' => true,
						'dependency'   => [
							[
								'element'  => 'before_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'after_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'before_after',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => esc_attr__( '_self = open in same browser tab, _blank = open in new browser tab.', 'fusion-builder' ),
						'param_name'  => 'target',
						'default'     => '_self',
						'value'       => [
							'_self'  => esc_attr__( '_self', 'fusion-builder' ),
							'_blank' => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'link',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'before_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'after_image',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'type',
								'value'    => 'before_after',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Handle Design Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the design of the handle used to change the before and after image.', 'fusion-builder' ),
						'param_name'  => 'handle_type',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'default'   => esc_attr__( 'Circle With Arrows', 'fusion-builder' ),
							'square'    => esc_attr__( 'Square With Arrows', 'fusion-builder' ),
							'rectangle' => esc_attr__( 'Rectangle With Arrows', 'fusion-builder' ),
							'arrows'    => esc_attr__( 'Arrows', 'fusion-builder' ),
							'diamond'   => esc_attr__( 'Diamond', 'fusion-builder' ),
							'circle'    => esc_attr__( 'Single Circle', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Handle Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the before and after image handle line and arrows. ex: #ffffff.', 'fusion-builder' ),
						'param_name'  => 'handle_color',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'before_after_handle_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Handle Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the before and after image handle switch. ex: #000000.', 'fusion-builder' ),
						'param_name'  => 'handle_bg',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'before_after_handle_bg' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
							[
								'element'  => 'handle_type',
								'value'    => 'arrows',
								'operator' => '!=',
							],
							[
								'element'  => 'handle_type',
								'value'    => 'circle',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Handle Offset', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls where the handle will be positioned on page load allowing you to control how much of each image displays by default. In percentage.', 'fusion-builder' ),
						'param_name'  => 'offset',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'before_after_offset' ),
						'min'         => '0',
						'max'         => '100',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Handle Orientation', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the position of the before and after image handle.', 'fusion-builder' ),
						'param_name'  => 'orientation',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'horizontal' => esc_attr__( 'Horizontal', 'fusion-builder' ),
							'vertical'   => esc_attr__( 'Vertical', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Handle Movement Control', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls how the viewer interacts with the image handler. The image handle can use Drag & Click, Drag Only, or Hover.', 'fusion-builder' ),
						'param_name'  => 'handle_movement',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => '',
						'value'       => [
							''           => esc_attr__( 'Default', 'fusion-builder' ),
							'drag_click' => __( 'Drag & Click', 'fusion-builder' ),
							'drag'       => esc_attr__( 'Drag Only', 'fusion-builder' ),
							'hover'      => esc_attr__( 'Hover', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'switch',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Max Width', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the maximum width the element should take up. Enter value including any valid CSS unit, ex: 200px. Leave empty to use full image width.', 'fusion-builder' ),
						'param_name'  => 'max_width',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how to align the image.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'bordersize',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'before_after_border_size' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'before_after_border_color' ),
						'dependency'  => [
							[
								'element'  => 'bordersize',
								'value'    => 0,
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the image border radius. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'borderradius',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => '',
					],
					'fusion_margin_placeholder'    => [
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-image-before-after-element',
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
add_action( 'fusion_builder_before_init', 'fusion_element_image_before_after' );
