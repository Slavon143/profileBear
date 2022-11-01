<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_button' ) ) {

	if ( ! class_exists( 'FusionSC_Button' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Button extends Fusion_Element {

			/**
			 * The button counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $button_counter = 1;

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
				add_filter( 'fusion_attr_button-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_button-shortcode-icon-divder', [ $this, 'icon_divider_attr' ] );
				add_filter( 'fusion_attr_button-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_button-shortcode-button-text', [ $this, 'button_text_attr' ] );
				add_filter( 'fusion_attr_button-shortcode-container', [ $this, 'container_attr' ] );

				add_shortcode( 'fusion_button', [ $this, 'render' ] );
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
					'button_el_type'                     => 'link',
					'hide_on_mobile'                     => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                     => '',
					'class'                              => '',
					'id'                                 => '',
					'accent_color'                       => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_color' ) ) : '#ffffff',
					'accent_hover_color'                 => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_accent_hover_color' ) ) : '#ffffff',
					'bevel_color'                        => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? strtolower( $fusion_settings->get( 'button_bevel_color' ) ) : '#54770F',
					'bevel_color_hover'                  => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? strtolower( $fusion_settings->get( 'button_bevel_color_hover' ) ) : '#54770F',
					'border_color'                       => ( '' !== $fusion_settings->get( 'button_border_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_color' ) ) : '#ffffff',
					'border_hover_color'                 => ( '' !== $fusion_settings->get( 'button_border_hover_color' ) ) ? strtolower( $fusion_settings->get( 'button_border_hover_color' ) ) : '#ffffff',
					'color'                              => 'default',
					'gradient_colors'                    => '',
					'icon'                               => '',
					'icon_divider'                       => 'no',
					'icon_position'                      => 'left',
					'link'                               => '',
					'link_attributes'                    => '',
					'modal'                              => '',
					'size'                               => '',
					'margin_bottom'                      => '',
					'margin_left'                        => '',
					'margin_right'                       => '',
					'margin_top'                         => '',
					'stretch'                            => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'default_stretch_value'              => ( '' !== $fusion_settings->get( 'button_span' ) ) ? $fusion_settings->get( 'button_span' ) : 'no',
					'target'                             => '_self',
					'text_transform'                     => '',
					'title'                              => '',
					'type'                               => ( '' !== $fusion_settings->get( 'button_type' ) ) ? strtolower( $fusion_settings->get( 'button_type' ) ) : 'flat',
					'alignment'                          => '',
					'alignment_medium'                   => '',
					'alignment_small'                    => '',
					'animation_type'                     => '',
					'animation_direction'                => 'down',
					'animation_speed'                    => '',
					'animation_offset'                   => $fusion_settings->get( 'animation_offset' ),

					'padding_top'                        => '',
					'padding_right'                      => '',
					'padding_bottom'                     => '',
					'padding_left'                       => '',
					'font_size'                          => '',
					'line_height'                        => '',
					'letter_spacing'                     => '',
					'fusion_font_family_button_font'     => '',
					'fusion_font_variant_button_font'    => '',
					'gradient_start_position'            => $fusion_settings->get( 'button_gradient_start' ),
					'gradient_end_position'              => $fusion_settings->get( 'button_gradient_end' ),
					'gradient_type'                      => $fusion_settings->get( 'button_gradient_type' ),
					'radial_direction'                   => $fusion_settings->get( 'button_radial_direction' ),
					'linear_angle'                       => $fusion_settings->get( 'button_gradient_angle' ),
					'border_radius_top_left'             => $fusion_settings->get( 'button_border_radius', 'top_left' ),
					'border_radius_top_right'            => $fusion_settings->get( 'button_border_radius', 'top_right' ),
					'border_radius_bottom_right'         => $fusion_settings->get( 'button_border_radius', 'bottom_right' ),
					'border_radius_bottom_left'          => $fusion_settings->get( 'button_border_radius', 'bottom_left' ),
					'border_top'                         => '',
					'border_right'                       => '',
					'border_bottom'                      => '',
					'border_left'                        => '',

					// Combined in accent_color.
					'icon_color'                         => '',
					'text_color'                         => '',

					// Combined in accent_hover_color.
					'icon_hover_color'                   => '',
					'text_hover_color'                   => '',

					// Combined with gradient_colors.
					'gradient_hover_colors'              => '',

					'button_gradient_top_color'          => ( '' !== $fusion_settings->get( 'button_gradient_top_color' ) ) ? $fusion_settings->get( 'button_gradient_top_color' ) : '#65bc7b',
					'button_gradient_bottom_color'       => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color' ) : '#65bc7b',
					'button_gradient_top_color_hover'    => ( '' !== $fusion_settings->get( 'button_gradient_top_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_top_color_hover' ) : '#5aa86c',
					'button_gradient_bottom_color_hover' => ( '' !== $fusion_settings->get( 'button_gradient_bottom_color_hover' ) ) ? $fusion_settings->get( 'button_gradient_bottom_color_hover' ) : '#5aa86c',
					'button_accent_color'                => ( '' !== $fusion_settings->get( 'button_accent_color' ) ) ? $fusion_settings->get( 'button_accent_color' ) : '#ffffff',
					'button_accent_hover_color'          => ( '' !== $fusion_settings->get( 'button_accent_hover_color' ) ) ? $fusion_settings->get( 'button_accent_hover_color' ) : '#ffffff',
					'button_bevel_color'                 => ( '' !== $fusion_settings->get( 'button_bevel_color' ) ) ? $fusion_settings->get( 'button_bevel_color' ) : '#54770F',

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

				return apply_filters(
					'fusion_button_extras',
					[
						'border_top'       => $fusion_settings->get( 'button_border_width', 'top' ),
						'border_right'     => $fusion_settings->get( 'button_border_width', 'right' ),
						'border_bottom'    => $fusion_settings->get( 'button_border_width', 'bottom' ),
						'border_left'      => $fusion_settings->get( 'button_border_width', 'left' ),
						'padding_top'      => $fusion_settings->get( 'button_padding', 'top' ),
						'padding_right'    => $fusion_settings->get( 'button_padding', 'right' ),
						'padding_bottom'   => $fusion_settings->get( 'button_padding', 'bottom' ),
						'padding_left'     => $fusion_settings->get( 'button_padding', 'left' ),
						'button_font_size' => $fusion_settings->get( 'button_typography', 'font-size' ),
					]
				);
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras() {
				return [
					'button_border_width[top]'     => 'border_top',
					'button_border_width[right]'   => 'border_right',
					'button_border_width[bottom]'  => 'border_bottom',
					'button_border_width[left]'    => 'border_left',
					'button_padding[top]'          => 'padding_top',
					'button_padding[right]'        => 'padding_right',
					'button_padding[bottom]'       => 'padding_bottom',
					'button_padding[left]'         => 'padding_left',
					'button_typography[font-size]' => 'button_font_size',
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
					'button_type'                        => 'type',
					'button_gradient_top_color'          => 'button_gradient_top_color',
					'button_gradient_bottom_color'       => 'button_gradient_bottom_color',
					'button_gradient_top_color_hover'    => 'button_gradient_top_color_hover',
					'button_gradient_bottom_color_hover' => 'button_gradient_bottom_color_hover',
					'button_accent_color'                => 'accent_color',
					'button_accent_hover_color'          => 'accent_hover_color',
					'button_border_color'                => 'border_color',
					'button_border_hover_color'          => 'border_hover_color',
					'button_bevel_color'                 => 'bevel_color',
					'button_span'                        => 'stretch',
					'button_border_radius[top_left]'     => 'border_radius_top_left',
					'button_border_radius[top_right]'    => 'border_radius_top_right',
					'button_border_radius[bottom_right]' => 'border_radius_bottom_right',
					'button_border_radius[bottom_left]'  => 'border_radius_bottom_left',
					'button_gradient_start'              => 'gradient_start_position',
					'button_gradient_end'                => 'gradient_end_position',
					'button_gradient_type'               => 'gradient_type',
					'button_radial_direction'            => 'radial_direction',
					'button_gradient_angle'              => 'linear_angle',

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
				$this->set_element_id( $this->button_counter );
				$this->defaults = self::get_element_defaults();
				$this->validate_args( $args );

				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_button', $args );

				$icon_html = '';
				if ( $this->args['icon'] ) {
					$icon_html = '<i ' . FusionBuilder::attributes( 'button-shortcode-icon' ) . '></i>';

					if ( 'yes' === $this->args['icon_divider'] ) {
						$icon_html = '<span ' . FusionBuilder::attributes( 'button-shortcode-icon-divder' ) . '>' . $icon_html . '</span>';
					}
				}

				$button_text = '<span ' . FusionBuilder::attributes( 'button-shortcode-button-text' ) . '>' . do_shortcode( $content ) . '</span>';

				$inner_content = ( 'left' === $this->args['icon_position'] ) ? $icon_html . $button_text : $button_text . $icon_html;

				$html = $this->get_styles();

				if ( isset( $args['button_el_type'] ) && 'submit' === $args['button_el_type'] ) {
					unset( $this->args['link'] );
					unset( $this->args['target'] );
					$html .= '<button type="submit" ' . FusionBuilder::attributes( 'button-shortcode' ) . ' tabindex="' . $args['tab_index'] . '">' . $inner_content . '</button>';
				} else {
					$html .= '<a ' . FusionBuilder::attributes( 'button-shortcode' ) . '>' . $inner_content . '</a>';
				}

				$html = '<div ' . FusionBuilder::attributes( 'button-shortcode-container' ) . '>' . $html . '</div>';

				$this->button_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_button_content', $html, $args );

			}

			/**
			 * Validate args and set to object array.
			 *
			 * @access public
			 * @since 3.4
			 * @param  array $args    Shortcode parameters.
			 * @return void
			 */
			public function validate_args( $args ) {

				$this->args      = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_button' );
				$this->args      = apply_filters( 'fusion_builder_default_args', $this->args, 'fusion_button', $args );
				$fusion_settings = awb_get_fusion_settings();

				$this->args['default_size'] = false;
				if ( ( isset( $args['size'] ) && '' === $args['size'] ) || ! isset( $args['size'] ) ) {
					$this->args['default_size'] = true;
				}

				$this->args['default_stretch'] = false;
				if ( ( isset( $args['stretch'] ) && ( '' === $args['stretch'] || 'default' === $args['stretch'] ) ) || ! isset( $args['stretch'] ) ) {
					$this->args['default_stretch'] = true;
				}

				$this->args['default_type'] = false;
				if ( ( isset( $args['type'] ) && ( '' === $args['type'] || 'default' === $args['type'] ) ) || ! isset( $args['type'] ) ) {
					$this->args['default_type'] = true;
				}

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				if ( empty( $this->args['gradient_colors'] ) ) {
					$this->args['gradient_colors'] = strtolower( $this->args['button_gradient_top_color'] ) . '|' . strtolower( $this->args['button_gradient_bottom_color'] );
				}

				if ( empty( $this->args['gradient_hover_colors'] ) ) {
					$this->args['gradient_hover_colors'] = strtolower( $this->args['button_gradient_top_color_hover'] ) . '|' . strtolower( $this->args['button_gradient_bottom_color_hover'] );
				}

				// Combined variable settings.
				$this->args['old_text_color']   = isset( $this->args['text_color'] ) && '' !== $this->args['text_color'] ? $this->args['text_color'] : false;
				$this->args['icon_color']       = $this->args['text_color'] = $this->args['accent_color'];
				$this->args['icon_hover_color'] = $this->args['text_hover_color'] = $this->args['accent_hover_color'];

				if ( ! isset( $args['border_color'] ) && isset( $args['border_color'] ) && '' !== $args['border_color'] ) {
					$this->args['border_color'] = $this->args['accent_color'];
				}

				if ( ! isset( $args['border_hover_color'] ) && isset( $args['accent_hover_color'] ) && '' !== $args['accent_hover_color'] ) {
					$this->args['border_hover_color'] = $this->args['accent_hover_color'];
				}

				if ( $this->args['old_text_color'] ) {
					$this->args['text_color'] = $this->args['old_text_color'];
				}

				if ( $this->args['modal'] ) {
					$this->args['link'] = '#';
				}

				$this->args['type'] = strtolower( $this->args['type'] );

				// BC compatibility for button shape.
				if ( isset( $args['shape'] ) && '' !== $args['shape'] && ! isset( $args['border_radius'] ) && ! isset( $args['border_radius_top_left'] ) ) {
					$args['shape'] = strtolower( $args['shape'] );

					$button_radius = [
						'square'  => '0px',
						'round'   => '2px',
						'round3d' => '4px',
						'pill'    => '25px',
					];

					if ( '3d' === $this->args['type'] && 'round' === $args['shape'] ) {
						$args['shape'] = 'round3d';
					}

					$this->args['border_radius_top_left']     = isset( $button_radius[ $args['shape'] ] ) ? $button_radius[ $args['shape'] ] : '0px';
					$this->args['border_radius_top_right']    = $this->args['border_radius_top_left'];
					$this->args['border_radius_bottom_right'] = $this->args['border_radius_top_left'];
					$this->args['border_radius_bottom_left']  = $this->args['border_radius_top_left'];

				} elseif ( isset( $args['border_radius'] ) && ! isset( $args['border_radius_top_left'] ) && '' !== $args['border_radius'] ) {
					$this->args['border_radius_top_left']     = $args['border_radius'];
					$this->args['border_radius_top_right']    = $this->args['border_radius_top_left'];
					$this->args['border_radius_bottom_right'] = $this->args['border_radius_top_left'];
					$this->args['border_radius_bottom_left']  = $this->args['border_radius_top_left'];
				}

				$this->args['border_radius_top_left']     = empty( $this->args['border_radius_top_left'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] );
				$this->args['border_radius_top_right']    = empty( $this->args['border_radius_top_right'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] );
				$this->args['border_radius_bottom_right'] = empty( $this->args['border_radius_bottom_right'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] );
				$this->args['border_radius_bottom_left']  = empty( $this->args['border_radius_bottom_left'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] );
				$this->args['border_radius']              = $this->args['border_radius_top_left'] . ' ' . $this->args['border_radius_top_right'] . ' ' . $this->args['border_radius_bottom_right'] . ' ' . $this->args['border_radius_bottom_left'];

				// Legacy single border support.
				if ( isset( $args['border_width'] ) && '' !== $args['border_width'] && ! isset( $args['border_top'] ) ) {
					$this->args['border_top']    = $args['border_width'];
					$this->args['border_right']  = $this->args['border_top'];
					$this->args['border_bottom'] = $this->args['border_top'];
					$this->args['border_left']   = $this->args['border_top'];
				}

				// Check if we have any border width direction set, if so we are using border width.
				$this->args['default_border_width'] = false;
				if ( '' === $this->args['border_top'] && '' === $this->args['border_right'] && '' === $this->args['border_bottom'] && '' === $this->args['border_left'] ) {
					$this->args['default_border_width'] = true;
				} else {

					// Not using default, ensure values for each.
					$this->args['border_top']    = '' === $this->args['border_top'] ? $fusion_settings->get( 'button_border_width', 'top' ) : $this->args['border_top'];
					$this->args['border_right']  = '' === $this->args['border_right'] ? $fusion_settings->get( 'button_border_width', 'right' ) : $this->args['border_right'];
					$this->args['border_bottom'] = '' === $this->args['border_bottom'] ? $fusion_settings->get( 'button_border_width', 'bottom' ) : $this->args['border_bottom'];
					$this->args['border_left']   = '' === $this->args['border_left'] ? $fusion_settings->get( 'button_border_width', 'left' ) : $this->args['border_left'];
				}

				$this->args['border_top']    = empty( $this->args['border_top'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_top'] );
				$this->args['border_right']  = empty( $this->args['border_right'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_right'] );
				$this->args['border_bottom'] = empty( $this->args['border_bottom'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_bottom'] );
				$this->args['border_left']   = empty( $this->args['border_left'] ) ? '0' : fusion_library()->sanitize->get_value_with_unit( $this->args['border_left'] );
				$this->args['border_width']  = $this->args['border_top'] . ' ' . $this->args['border_right'] . ' ' . $this->args['border_bottom'] . ' ' . $this->args['border_left'];
			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.5
			 * @return string
			 */
			protected function get_styles() {
				$this->base_selector = '.fusion-body .fusion-button.button-' . $this->element_id;
				$this->dynamic_css   = [];
				$fusion_settings     = awb_get_fusion_settings();

				$hover_selectors = [
					$this->base_selector . ':hover',
					$this->base_selector . ':active',
					$this->base_selector . ':focus',
				];

				// If its custom, default or a custom color scheme.
				if ( 'custom' === $this->args['color'] || 'default' === $this->args['color'] || false !== strpos( $this->args['color'], 'scheme-' ) ) {

					$general_styles = $text_color_styles = $button_3d_styles = $hover_styles = $text_color_hover_styles = $gradient_styles = $gradient_hover_styles = '';

					// 3D type with custom bevel color, change box shadow color.
					if ( '3d' === $this->args['type'] ) {

						$box_shadow_3d = 'inset 0 1px 0 #fff, 0 0.15em 0 ' . $this->args['bevel_color'] . ', 0.1em 0.2em 0.2em 0.15em rgba(0, 0, 0, 0.3)';
						$this->add_css_property( $this->base_selector . '.button-3d', 'box-shadow', $box_shadow_3d );

						$box_shadow_3d = 'inset 0 1px 0 #fff, 0 1px 0 ' . $this->args['bevel_color'] . ', 0.05em 0.1em 0.1em 0.07em rgba(0, 0, 0, 0.3)';
						$this->add_css_property( $this->base_selector . '.button-3d:active', 'box-shadow', $box_shadow_3d );

						$box_shadow_3d = 'inset 0 1px 0 #fff, 0 0.15em 0 ' . $this->args['bevel_color_hover'] . ', 0.1em 0.2em 0.2em 0.15em rgba(0, 0, 0, 0.3)';
						$this->add_css_property( $this->base_selector . '.button-3d:hover', 'box-shadow', $box_shadow_3d );

						$box_shadow_3d = 'inset 0 1px 0 #fff, 0 1px 0 ' . $this->args['bevel_color_hover'] . ', 0.05em 0.1em 0.1em 0.07em rgba(0, 0, 0, 0.3)';
						$this->add_css_property( $this->base_selector . '.button-3d:hover:active', 'box-shadow', $box_shadow_3d );
					}

					if ( 'default' !== $this->args['color'] ) {
						$selectors = [
							$this->base_selector . ' .fusion-button-text',
							$this->base_selector . ' i',
						];

						if ( $this->args['old_text_color'] ) {
							$this->add_css_property( $selectors, 'color', $this->args['old_text_color'] );
						} elseif ( '' !== $this->args['accent_color'] ) {
							$this->add_css_property( $selectors, 'color', $this->args['accent_color'] );
						}

						if ( '' !== $this->args['border_color'] ) {
							$this->add_css_property( $this->base_selector, 'border-color', $this->args['border_color'] );
						}

						$selectors = [
							$this->base_selector . ':hover .fusion-button-text',
							$this->base_selector . ':hover i',
							$this->base_selector . ':focus .fusion-button-text',
							$this->base_selector . ':focus i',
							$this->base_selector . ':active .fusion-button-text',
							$this->base_selector . ':active i',
						];

						if ( $this->args['old_text_color'] ) {
							$this->add_css_property( $selectors, 'color', $this->args['old_text_color'] );
						} elseif ( '' !== $this->args['accent_hover_color'] ) {
							$this->add_css_property( $selectors, 'color', $this->args['accent_hover_color'] );
						} elseif ( '' !== $this->args['accent_color'] ) {
							$this->add_css_property( $selectors, 'color', $this->args['accent_color'] );
						}

						if ( '' !== $this->args['border_hover_color'] ) {
							$this->add_css_property( $hover_selectors, 'border-color', $this->args['border_hover_color'] );
						} elseif ( '' !== $this->args['accent_color'] ) {
							$this->add_css_property( $hover_selectors, 'border-color', $this->args['accent_color'] );
						}

						if ( '' !== $this->args['accent_color'] && 'yes' === $this->args['icon_divider'] ) {
							$this->add_css_property( $this->base_selector . ' .fusion-button-icon-divider', 'border-color', $this->args['accent_color'] );
						}

						if ( '' !== $this->args['accent_hover_color'] && 'yes' === $this->args['icon_divider'] ) {
							$selectors = [
								$this->base_selector . ':hover .fusion-button-icon-divider',
								$this->base_selector . ':active .fusion-button-icon-divider',
								$this->base_selector . ':focus .fusion-button-icon-divider',
							];

							$this->add_css_property( $selectors, 'border-color', $this->args['accent_hover_color'] );
						}
					}

					if ( '' !== $this->args['border_width'] && 'custom' === $this->args['color'] && ! $this->args['default_border_width'] ) {
						$this->add_css_property( $this->base_selector, 'border-width', $this->args['border_width'] );
						$this->add_css_property( $hover_selectors, 'border-width', $this->args['border_width'] );
					}

					$this->add_css_property( $this->base_selector, 'border-radius', $this->args['border_radius'] );

					if ( 'default' !== $this->args['color'] ) {
						if ( $this->args['gradient_colors'] ) {

							// Checking for deprecated separators.
							if ( strpos( $this->args['gradient_colors'], ';' ) ) {
								$grad_colors = explode( ';', $this->args['gradient_colors'] );
							} else {
								$grad_colors = explode( '|', $this->args['gradient_colors'] );
							}

							// Only one, just use that as background color, no gradient.
							if ( 1 === count( $grad_colors ) || empty( $grad_colors[1] ) || $grad_colors[0] === $grad_colors[1] ) {
								$this->add_css_property( $this->base_selector, 'background', $grad_colors[0] );
							} else {
								$this->add_css_property( $this->base_selector, 'background', $grad_colors[0] );

								if ( 'linear' === $this->args['gradient_type'] ) {
									$this->add_css_property( $this->base_selector, 'background-image', 'linear-gradient(' . $this->args['linear_angle'] . 'deg,' . $grad_colors[0] . ' ' . $this->args['gradient_start_position'] . '%,' . $grad_colors[1] . ' ' . $this->args['gradient_end_position'] . '%)' );
								} else {
									$this->add_css_property( $this->base_selector, 'background-image', 'radial-gradient(circle at ' . $this->args['radial_direction'] . ',' . $grad_colors[0] . ' ' . $this->args['gradient_start_position'] . '%,' . $grad_colors[1] . ' ' . $this->args['gradient_end_position'] . '%)' );
								}
							}
						}

						if ( $this->args['gradient_hover_colors'] ) {

							// Checking for deprecated separators.
							if ( strpos( $this->args['gradient_hover_colors'], ';' ) ) {
								$grad_hover_colors = explode( ';', $this->args['gradient_hover_colors'] );
							} else {
								$grad_hover_colors = explode( '|', $this->args['gradient_hover_colors'] );
							}

							if ( 1 === count( $grad_hover_colors ) || '' === $grad_hover_colors[1] || $grad_hover_colors[0] === $grad_hover_colors[1] ) {
								$this->add_css_property( $hover_selectors, 'background', $grad_hover_colors[0] );
							} else {
								$this->add_css_property( $hover_selectors, 'background', $grad_hover_colors[0] );

								if ( 'linear' === $this->args['gradient_type'] ) {
									$this->add_css_property( $hover_selectors, 'background-image', 'linear-gradient(' . $this->args['linear_angle'] . 'deg,' . $grad_hover_colors[0] . ' ' . $this->args['gradient_start_position'] . '%,' . $grad_hover_colors[1] . ' ' . $this->args['gradient_end_position'] . '%)' );
								} else {
									$this->add_css_property( $hover_selectors, 'background-image', 'radial-gradient(circle at ' . $this->args['radial_direction'] . ',' . $grad_hover_colors[0] . ' ' . $this->args['gradient_start_position'] . '%,' . $grad_hover_colors[1] . ' ' . $this->args['gradient_end_position'] . '%)' );
								}
							}
						}
					}
				}

				if ( ! $this->is_default( 'text_transform' ) && '' !== $this->args['text_transform'] ) {
					$this->add_css_property( $this->base_selector . ' .fusion-button-text', 'text-transform', $this->args['text_transform'] );
				}

				if ( '' === $this->args['size'] ) {
					if ( ! $this->is_default( 'font_size' ) ) {
						$this->add_css_property( $this->base_selector, 'font-size', fusion_library()->sanitize->get_value_with_unit( $this->args['font_size'] ) );
					}

					if ( ! $this->is_default( 'line_height' ) ) {
						$this->add_css_property( $this->base_selector, 'line-height', $this->args['line_height'] );
					}

					if ( ! $this->is_default( 'padding_top' ) ) {
						$this->add_css_property( $this->base_selector, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_top'] ) );
					}
					if ( ! $this->is_default( 'padding_right' ) ) {
						$this->add_css_property( $this->base_selector, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_right'] ) );
					}
					if ( ! $this->is_default( 'padding_bottom' ) ) {
						$this->add_css_property( $this->base_selector, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_bottom'] ) );
					}
					if ( ! $this->is_default( 'padding_left' ) ) {
						$this->add_css_property( $this->base_selector, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_left'] ) );
					}

					// If we have an icon and divider and changed either font or padding we need to calculate new spacing.
					if ( '' !== $this->args['icon'] && 'yes' === $this->args['icon_divider'] && ( ! $this->is_default( 'padding_' . $this->args['icon_position'] ) || ! $this->is_default( 'font_size' ) ) ) {
						$side_padding = ! $this->is_default( 'padding_' . $this->args['icon_position'] ) ? $this->args[ 'padding_' . $this->args['icon_position'] ] : $fusion_settings->get( 'button_padding', $this->args['icon_position'] );
						$font_size    = ! $this->is_default( 'font_size' ) ? $this->args['font_size'] : $fusion_settings->get( 'button_typography', 'font-size' );

						$this->add_css_property( $this->base_selector . ' .fusion-button-text-' . $this->args['icon_position'], 'padding-' . $this->args['icon_position'], 'calc( ' . $side_padding . ' / 2 + ' . $font_size . ' + 1px )' );

						$this->add_css_property( $this->base_selector . ' .button-icon-divider-' . $this->args['icon_position'], 'width', 'calc( ' . $side_padding . ' + ' . $font_size . ' )' );
					}
				}

				if ( ! $this->is_default( 'letter_spacing' ) ) {
					$this->add_css_property( $this->base_selector, 'letter-spacing', fusion_library()->sanitize->get_value_with_unit( $this->args['letter_spacing'] ) );
				}

				$text_styles = Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'button_font', 'array' );
				foreach ( $text_styles as $rule => $value ) {
					$this->add_css_property( $this->base_selector, $rule, $value );
				}

				$css = $this->parse_css();

				return $css ? '<style>' . $css . '</style>' : '';
			}

			/**
			 * Builds the container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function container_attr() {
				$attr = [
					'class' => '',
					'style' => '',
				];

				if ( fusion_element_rendering_is_flex() ) {

					if ( ! empty( $this->args['alignment'] ) ) {
						$attr['style'] .= 'text-align:' . $this->args['alignment'] . ';';
					}

					if ( ! empty( $this->args['alignment_medium'] ) && $this->args['alignment'] !== $this->args['alignment_medium'] ) {
						$attr['class'] .= ' md-text-align-' . $this->args['alignment_medium'];
					}

					if ( ! empty( $this->args['alignment_small'] ) && $this->args['alignment'] !== $this->args['alignment_small'] ) {
						$attr['class'] .= ' sm-text-align-' . $this->args['alignment_small'];
					}
				} else {
					$attr['class'] = 'fusion-button-wrapper';
					// Add wrapper to the button for alignment and scoped styling.
					if ( ( ! $this->args['default_stretch'] && 'yes' === $this->args['stretch'] ) || ( $this->args['default_stretch'] && 'yes' === $this->args['default_stretch_value'] ) ) {
						$attr['class'] = ' fusion-align-block';
					} elseif ( $this->args['alignment'] ) {
						$attr['class'] = ' fusion-align' . $this->args['alignment'];
					}
				}

				$attr['class'] = ltrim( $attr['class'] );
				return $attr;
			}



			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {
				$size = 'button-' . $this->args['size'];
				if ( $this->args['default_size'] ) {
					$size = 'fusion-button-default-size';
				}

				$stretch = 'fusion-button-span-' . $this->args['stretch'];
				if ( $this->args['default_stretch'] ) {
					$stretch = 'fusion-button-default-span';
				}

				$type = '';
				if ( $this->args['default_type'] ) {
					$type = 'fusion-button-default-type';
				}

				$attr['class'] = 'fusion-button button-' . $this->args['type'] . ' ' . $size . ' button-' . $this->args['color'] . ' button-' . $this->element_id . ' ' . $stretch . ' ' . $type;

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				if ( isset( $this->args['target'] ) ) {
					$attr['target'] = $this->args['target'];
					if ( '_blank' === $this->args['target'] ) {
						$attr['rel'] = 'noopener noreferrer';
					} elseif ( 'lightbox' === $this->args['target'] ) {
						$attr['rel'] = 'iLightbox';
					}
				}

				// Add additional, custom link attributes correctly formatted to the anchor.
				$attr = fusion_get_link_attributes( $this->args, $attr );

				if ( isset( $this->args['title'] ) && '' !== $this->args['title'] ) {
					$attr['title'] = $this->args['title'];
				}

				if ( isset( $this->args['link'] ) && '' !== $this->args['link'] ) {
					$attr['href'] = $this->args['link'];
				}

				if ( isset( $this->args['modal'] ) && '' !== $this->args['modal'] ) {
					$attr['data-toggle'] = 'modal';
					$attr['data-target'] = '.fusion-modal.' . $this->args['modal'];
				}

				$attr['style'] = Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( ( ! empty( $this->args['margin_right'] ) || ! empty( $this->args['margin_left'] ) ) && ( ! $this->args['default_stretch'] && 'yes' === $this->args['stretch'] ) || ( $this->args['default_stretch'] && 'yes' === $this->args['default_stretch_value'] ) ) {
					$margin_right = ! empty( $this->args['margin_right'] ) ? ' - ' . $this->args['margin_right'] : '';
					$margin_left  = ! empty( $this->args['margin_left'] ) ? ' - ' . $this->args['margin_left'] : '';

					$attr['style'] .= 'width:calc(100%' . $margin_right . $margin_left . ');';
				}

				if ( isset( $this->args['class'] ) && '' !== $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( isset( $this->args['id'] ) && '' !== $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_divider_attr() {

				$attr = [];

				$attr['class'] = 'fusion-button-icon-divider button-icon-divider-' . $this->args['icon_position'];

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {

				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['icon'] ),
					'aria-hidden' => 'true',
				];

				if ( 'yes' !== $this->args['icon_divider'] ) {
					$attr['class'] .= ' button-icon-' . $this->args['icon_position'];
				}

				if ( $this->args['icon_color'] !== $this->args['accent_color'] ) {
					$attr['style'] = 'color:' . $this->args['icon_color'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function button_text_attr() {

				$attr = [
					'class' => 'fusion-button-text',
				];

				if ( $this->args['icon'] && 'yes' === $this->args['icon_divider'] ) {
					$attr['class'] = 'fusion-button-text fusion-button-text-' . $this->args['icon_position'];
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Button settings.
			 */
			public function add_options() {
				global $dynamic_css_helpers;

				$option_name           = Fusion_Settings::get_option_name();
				$main_elements         = apply_filters( 'fusion_builder_element_classes', [ '.fusion-button-default' ], '.fusion-button-default' );
				$all_elements          = array_merge( [ '.fusion-button' ], $main_elements );
				$default_size_selector = apply_filters( 'fusion_builder_element_classes', [ ' .fusion-button-default-size' ], '.fusion-button-default-size' );
				$quantity_elements     = apply_filters( 'fusion_builder_element_classes', [ '.fusion-button-quantity' ], '.fusion-button-quantity' );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-all', Fusion_Dynamic_CSS_Helpers::get_elements_string( $all_elements ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-default-size-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $default_size_selector ) );

				// General 3d styling.
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-3d', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $all_elements, ':not(.button-flat)', '.fusion-button_type-3d' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-3d-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $all_elements, ':not(.button-flat):active', '.fusion-button_type-3d' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-3d-hover', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $all_elements, ':not(.button-flat):hover', '.fusion-button_type-3d' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-3d-hover-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $all_elements, ':not(.button-flat):hover:active', '.fusion-button_type-3d' ) ) );

				// Quantity styling.
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-quantity-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $quantity_elements ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-quantity-default-qty', Fusion_Dynamic_CSS_Helpers::get_elements_string( $quantity_elements, ' .qty' ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default', Fusion_Dynamic_CSS_Helpers::get_elements_string( $main_elements ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default:hover .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover .fusion-button-text', '' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default:focus .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus .fusion-button-text', '' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default:active .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active .fusion-button-text', '' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-default .fusion-button-text', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-button-text', '' ) ) );

				// Default gradients.
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, '', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-radial-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, '', '.fusion-button_gradient-radial' ) ) );

				// Hover gradients.
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-hover-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-radial-hover-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover', '.fusion-button_gradient-radial' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-hover', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':hover' ) ) );

				// Focus gradients.
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-focus-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-radial-focus-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus', '.fusion-button_gradient-radial' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-focus', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':focus' ) ) );

				// Active gradients.
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-active-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active', '.fusion-has-button-gradient' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-radial-active-gradient', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active', '.fusion-button_gradient-radial' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-active', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':active' ) ) );

				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-visited', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':visited' ) ) );
				Fusion_Dynamic_CSS::add_replace_pattern( '.fusion-builder-elements-button-main-span-yes', Fusion_Dynamic_CSS_Helpers::get_elements_string( $dynamic_css_helpers->map_selector( $main_elements, ':not(.fusion-button-span-no)', '.fusion-button_span-yes' ) ) );

				return [
					'button_shortcode_section' => [
						'label'  => esc_html__( 'Button', 'fusion-builder' ),
						'id'     => 'button_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-check-empty',
						'fields' => [
							'button_padding'               => [
								'label'       => esc_html__( 'Button Padding', 'Avada' ),
								'description' => esc_html__( 'Controls the padding for buttons.', 'Avada' ),
								'id'          => 'button_padding',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
								],
								'default'     => [
									'top'    => '13px',
									'right'  => '29px',
									'bottom' => '13px',
									'left'   => '29px',
								],
								'type'        => 'spacing',
								'css_vars'    => [
									[
										'name'   => '--button_padding-top',
										'choice' => 'top',
									],
									[
										'name'   => '--button_padding-bottom',
										'choice' => 'bottom',
									],
									[
										'name'   => '--button_padding-left',
										'choice' => 'left',
									],
									[
										'name'   => '--button_padding-right',
										'choice' => 'right',
									],
								],
							],
							'button_span'                  => [
								'label'       => esc_html__( 'Button Span', 'fusion-builder' ),
								'description' => esc_html__( 'Controls if the button spans the full width of its container.', 'fusion-builder' ),
								'id'          => 'button_span',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => 'body',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-button_span-$',
										'remove_attrs'  => [ 'fusion-button_span-yes', 'fusion-button_span-no' ],
										'toLowerCase'   => true,
									],
								],
							],
							'button_type'                  => [
								'label'       => esc_html__( 'Button Type', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the default button type.', 'fusion-builder' ),
								'id'          => 'button_type',
								'default'     => 'Flat',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'Flat' => esc_html__( 'Flat', 'fusion-builder' ),
									'3d'   => esc_html__( '3D', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => 'body',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-button_type-$',
										'remove_attrs'  => [ 'fusion-button_type-flat', 'fusion-button_type-3d' ],
										'toLowerCase'   => true,
									],
									[
										'element'       => '.fusion-button-default-type',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'button-$',
										'remove_attrs'  => [ 'button-3d', 'button-flat' ],
										'toLowerCase'   => true,
									],
								],
							],
							'button_typography'            => [
								'id'          => 'button_typography',
								'label'       => esc_html__( 'Button Typography', 'fusion-builder' ),
								'description' => esc_html__( 'These settings control the typography for all button text.', 'fusion-builder' ),
								'type'        => 'typography',
								'global'      => true,
								'choices'     => [
									'font-family'    => true,
									'font-size'      => true,
									'font-weight'    => true,
									'line-height'    => true,
									'letter-spacing' => true,
									'text-transform' => true,
								],
								'default'     => [
									'font-family'    => 'var(--awb-typography3-font-family)',
									'font-size'      => 'var(--awb-typography3-font-size)',
									'font-weight'    => '600',
									'line-height'    => 'var(--awb-typography3-line-height)',
									'letter-spacing' => 'var(--awb-typography3-letter-spacing)',
									'text-transform' => 'var(--awb-typography3-text-transform)',
								],
								'css_vars'    => [
									[
										'name'     => '--button_typography-font-family',
										'choice'   => 'font-family',
										'callback' => [ 'combined_font_family', 'button_typography' ],
									],
									[
										'name'     => '--button_typography-font-weight',
										'choice'   => 'font-weight',
										'callback' => [ 'font_weight_no_regular', '' ],
									],
									[
										'name'     => '--button_typography-letter-spacing',
										'choice'   => 'letter-spacing',
										'callback' => [ 'maybe_append_px', '' ],
									],
									[
										'name'   => '--button_typography-font-style',
										'choice' => 'font-style',
									],
									[
										'name'   => '--button_font_size',
										'choice' => 'font-size',
									],
									[
										'name'   => '--button_line_height',
										'choice' => 'line-height',
									],
									[
										'name'   => '--button_text_transform',
										'choice' => 'text-transform',
									],
								],
							],
							'button_gradient_top_color'    => [
								'label'       => esc_html__( 'Button Gradient Start Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the start color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_top_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_gradient_top_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'output'      => [
									[
										'element'  => 'helperElement',
										'property' => 'dummy',
										'callback' => [
											'toggle_class',
											[
												'condition' => [ 'button_gradient_bottom_color', 'not-equal-to-option' ],
												'element' => 'body',
												'className' => 'fusion-has-button-gradient',
											],
										],
										'sanitize_callback' => '__return_empty_string',
									],
								],
							],
							'button_gradient_bottom_color' => [
								'label'       => esc_html__( 'Button Gradient End Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the end color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_bottom_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_gradient_bottom_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_gradient_top_color_hover' => [
								'label'       => esc_html__( 'Button Gradient Start Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the start hover color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_top_color_hover',
								'default'     => 'hsla(var(--awb-color5-h),calc(var(--awb-color5-s) - 5%),calc(var(--awb-color5-l) - 10%),var(--awb-color5-a))',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_gradient_top_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'preview'     => [
									'selector' => '.fusion-button,.fusion-button .wpcf7-submit',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
							'button_gradient_bottom_color_hover' => [
								'label'       => esc_html__( 'Button Gradient End Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the end hover color of the button background.', 'fusion-builder' ),
								'id'          => 'button_gradient_bottom_color_hover',
								'default'     => 'hsla(var(--awb-color5-h),calc(var(--awb-color5-s) - 5%),calc(var(--awb-color5-l) - 10%),var(--awb-color5-a))',
								'type'        => 'color-alpha',
								'preview'     => [
									'selector' => '.fusion-button,.fusion-button .wpcf7-submit',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
								'css_vars'    => [
									[
										'name'     => '--button_gradient_bottom_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_gradient_start'        => [
								'label'       => esc_html__( 'Button Gradient Start', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the start position for the gradient.', 'fusion-builder' ),
								'id'          => 'button_gradient_start',
								'default'     => '0',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '100',
									'step' => '1',
								],
								'css_vars'    => [
									[
										'name'          => '--button_gradient_start',
										'value_pattern' => '$%',
									],
								],
							],
							'button_gradient_end'          => [
								'label'       => esc_html__( 'Button Gradient End', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the end position for the gradient.', 'fusion-builder' ),
								'id'          => 'button_gradient_end',
								'default'     => '100',
								'type'        => 'slider',
								'choices'     => [
									'min'  => '0',
									'max'  => '100',
									'step' => '1',
								],
								'css_vars'    => [
									[
										'name'          => '--button_gradient_end',
										'value_pattern' => '$%',
									],
								],
							],
							'button_gradient_type'         => [
								'label'       => esc_html__( 'Button Gradient Type', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the type of gradient.', 'fusion-builder' ),
								'id'          => 'button_gradient_type',
								'default'     => 'linear',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'linear' => esc_html__( 'Linear', 'fusion-builder' ),
									'radial' => esc_html__( 'Radial', 'fusion-builder' ),
								],
								'output'      => [
									[
										'element'       => 'body',
										'function'      => 'attr',
										'attr'          => 'class',
										'value_pattern' => 'fusion-button_gradient-$',
										'remove_attrs'  => [ 'fusion-button_gradient-linear', 'fusion-button_gradient-radial' ],
										'toLowerCase'   => true,
									],
								],
							],
							'button_gradient_angle'        => [
								'label'           => esc_html__( 'Button Gradient Angle', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the angle for the linear gradient.', 'fusion-builder' ),
								'id'              => 'button_gradient_angle',
								'default'         => '180',
								'type'            => 'slider',
								'soft_dependency' => true,
								'choices'         => [
									'min'  => '0',
									'max'  => '360',
									'step' => '1',
								],
								'css_vars'        => [
									[
										'name'          => '--button_gradient_angle',
										'value_pattern' => '$deg',
									],
								],
							],
							'button_radial_direction'      => [
								'label'           => esc_html__( 'Button Radial Direction', 'fusion-builder' ),
								'description'     => esc_html__( 'Select direction for radial gradient.', 'fusion-builder' ),
								'id'              => 'button_radial_direction',
								'default'         => 'center center',
								'type'            => 'select',
								'soft_dependency' => true,
								'choices'         => [
									'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
									'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
									'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
									'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
									'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
									'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
									'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
									'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
									'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
								],
								'css_vars'        => [
									[
										'name' => '--button_radial_direction',
									],
								],
							],
							'button_accent_color'          => [
								'label'       => esc_html__( 'Button Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the button text, divider and icon.', 'fusion-builder' ),
								'id'          => 'button_accent_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_accent_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_accent_hover_color'    => [
								'label'       => esc_html__( 'Button Text Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover color of the button text, divider and icon.', 'fusion-builder' ),
								'id'          => 'button_accent_hover_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_accent_hover_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
								'preview'     => [
									'selector' => '.fusion-button,.fusion-button .wpcf7-submit',
									'type'     => 'class',
									'toggle'   => 'hover',
								],
							],
							'button_bevel_color'           => [
								'label'       => esc_html__( 'Button Bevel Color For 3D Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the bevel color of the buttons when using 3D button type.', 'fusion-builder' ),
								'id'          => 'button_bevel_color',
								'default'     => 'hsla(var(--awb-color5-h),calc(var(--awb-color5-s) - 5%),calc(var(--awb-color5-l) - 10%),var(--awb-color5-a))',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_bevel_color',
										'callback' => [ 'sanitize_color' ],
									],
									[
										'name'     => '--button_box_shadow',
										'callback' => [
											'conditional_return_value',
											[
												'value_pattern' => [ 'inset 0px 1px 0px #ffffff, 0px 3px 0px $, 1px 5px 5px 3px rgba(0, 0, 0, 0.3)', 'none' ],
												'conditions'    => [
													[ 'button_type', '===', '3d' ],
												],
											],
										],
									],
								],
							],
							'button_bevel_color_hover'     => [
								'label'       => esc_html__( 'Button Hover Bevel Color For 3D Mode', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover bevel color of the buttons when using 3D button type.', 'fusion-builder' ),
								'id'          => 'button_bevel_color_hover',
								'default'     => 'hsla(var(--awb-color5-h),calc(var(--awb-color5-s) - 5%),calc(var(--awb-color5-l) - 10%),var(--awb-color5-a))',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_bevel_color_hover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_border_width'          => [
								'label'       => esc_html__( 'Button Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size for buttons.', 'fusion-builder' ),
								'id'          => 'button_border_width',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
								],
								'default'     => [
									'top'    => '0px',
									'bottom' => '0px',
									'left'   => '0px',
									'right'  => '0px',
								],
								'type'        => 'spacing',
								'css_vars'    => [
									[
										'name'   => '--button_border_width-top',
										'choice' => 'top',
										'po'     => false,
									],
									[
										'name'   => '--button_border_width-right',
										'choice' => 'right',
										'po'     => false,
									],
									[
										'name'   => '--button_border_width-bottom',
										'choice' => 'bottom',
										'po'     => false,
									],
									[
										'name'   => '--button_border_width-left',
										'choice' => 'left',
										'po'     => false,
									],
								],
							],
							'button_border_radius'         => [
								'type'        => 'border_radius',
								'label'       => esc_html__( 'Button Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius for buttons.', 'Avada' ),
								'id'          => 'button_border_radius',
								'choices'     => [
									'top_left'     => true,
									'top_right'    => true,
									'bottom_right' => true,
									'bottom_left'  => true,
									'units'        => [ 'px', '%', 'em' ],
								],
								'default'     => [
									'top_left'     => '4px',
									'top_right'    => '4px',
									'bottom_right' => '4px',
									'bottom_left'  => '4px',
								],
								'css_vars'    => [
									[
										'name'    => '--button-border-radius-top-left',
										'choice'  => 'top_left',
										'element' => 'body',
									],
									[
										'name'    => '--button-border-radius-top-right',
										'choice'  => 'top_right',
										'element' => 'body',
									],
									[
										'name'    => '--button-border-radius-bottom-right',
										'choice'  => 'bottom_right',
										'element' => 'body',
									],
									[
										'name'    => '--button-border-radius-bottom-left',
										'choice'  => 'bottom_left',
										'element' => 'body',
									],
								],
							],
							'button_border_color'          => [
								'label'       => esc_html__( 'Button Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color for buttons.', 'fusion-builder' ),
								'id'          => 'button_border_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_border_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_border_hover_color'    => [
								'label'       => esc_html__( 'Button Border Hover Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the hover border color of the button.', 'fusion-builder' ),
								'id'          => 'button_border_hover_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--button_border_hover_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'button_presets'               => [
								'label'       => esc_html__( 'Legacy Button Presets', 'Avada' ),
								'description' => esc_html__( 'Select if you would like to enable legacy color presets.', 'Avada' ),
								'id'          => 'button_presets',
								'default'     => '0',
								'type'        => 'switch',
								// No need to refresh the page.
								'transport'   => 'postMessage',
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
				$fusion_settings = awb_get_fusion_settings();

				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/button.min.css' );

				if ( defined( 'LS_PLUGIN_SLUG' ) || defined( 'RS_PLUGIN_PATH' ) ) {
					FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/button-sliders.min.css' );
				}

				if ( apply_filters( 'awb_load_button_presets', ( '1' === $fusion_settings->get( 'button_presets' ) ) ) ) {
					FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/button-presets.min.css' );
				}
			}
		}
	}

	new FusionSC_Button();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_button() {
	$fusion_settings = awb_get_fusion_settings();

	$standard_schemes = [
		'default' => esc_attr__( 'Default', 'fusion-builder' ),
		'custom'  => esc_attr__( 'Custom', 'fusion-builder' ),
	];

	$style_option = 'radio_button_set';
	if ( apply_filters( 'awb_load_button_presets', ( '1' === $fusion_settings->get( 'button_presets' ) ) ) ) {
		$style_option     = 'select';
		$standard_schemes = [
			'default'   => esc_attr__( 'Default', 'fusion-builder' ),
			'custom'    => esc_attr__( 'Custom', 'fusion-builder' ),
			'green'     => esc_attr__( 'Green', 'fusion-builder' ),
			'darkgreen' => esc_attr__( 'Dark Green', 'fusion-builder' ),
			'orange'    => esc_attr__( 'Orange', 'fusion-builder' ),
			'blue'      => esc_attr__( 'Blue', 'fusion-builder' ),
			'red'       => esc_attr__( 'Red', 'fusion-builder' ),
			'pink'      => esc_attr__( 'Pink', 'fusion-builder' ),
			'darkgray'  => esc_attr__( 'Dark Gray', 'fusion-builder' ),
			'lightgray' => esc_attr__( 'Light Gray', 'fusion-builder' ),
		];
	}

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Button',
			[
				'name'          => esc_attr__( 'Button', 'fusion-builder' ),
				'shortcode'     => 'fusion_button',
				'icon'          => 'fusiona-check-empty',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-button-preview.php',
				'preview_id'    => 'fusion-builder-block-module-button-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/button-element/',
				'inline_editor' => true,
				'subparam_map'  => [
					'fusion_font_family_button_font'  => 'main_typography',
					'fusion_font_variant_button_font' => 'main_typography',
					'font_size'                       => 'main_typography',
					'line_height'                     => 'main_typography',
					'letter_spacing'                  => 'main_typography',
					'text_transform'                  => 'main_typography',
				],
				'params'        => [
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Button URL', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'description'  => esc_attr__( "Add the button's url ex: http://example.com.", 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Button Text', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Button Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the text that will display on button.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Button Title Attribute', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => '',
						'description'  => esc_attr__( 'Set a title attribute for the button link.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Target', 'fusion-builder' ),
						'description' => esc_attr__( '_self = open in same browser tab, _blank = open in new browser tab.', 'fusion-builder' ),
						'param_name'  => 'target',
						'default'     => '_self',
						'value'       => [
							'_self'    => esc_attr__( '_self', 'fusion-builder' ),
							'_blank'   => esc_attr__( '_blank', 'fusion-builder' ),
							'lightbox' => esc_attr__( 'Lightbox', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Button Additional Attributes', 'fusion-builder' ),
						'param_name'  => 'link_attributes',
						'value'       => '',
						'description' => esc_attr__( "Add additional attributes to the anchor tag. Separate attributes with a whitespace and use single quotes on the values, doubles don't work. If you need to add square brackets, [ ], to your attributes, please use curly brackets, { }, instead. They will be replaced correctly on the frontend. ex: rel='nofollow'.", 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( "Select the button's alignment.", 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => '',
						'responsive'  => [
							'state' => 'large',
						],
						'value'       => [
							''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Modal Window Anchor', 'fusion-builder' ),
						'param_name'   => 'modal',
						'value'        => '',
						'description'  => __( 'Add the class name of the modal window you want to open on button click. <strong>NOTE:</strong> The corresponding Modal Element must be added to the same page.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'        => $style_option,
						'heading'     => esc_attr__( 'Button Style', 'fusion-builder' ),
						'description' => $fusion_settings->get( 'button_presets' ) ? esc_attr__( 'Select the button\'s color. Select default or specific color name to use Global Options presets, or select custom to use advanced color options below.', 'fusion-builder' ) : esc_attr__( 'Select the button\'s color. Select default to use Global Options values, or custom to use advanced color options below.', 'fusion-builder' ),
						'param_name'  => 'color',
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => $standard_schemes,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Start Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the start color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient End Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the end color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient Start Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the start hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_top_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_top_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Gradient End Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the end hover color of the button background.', 'fusion-builder' ),
						'param_name'  => 'button_gradient_bottom_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_gradient_bottom_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Start Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select start position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_start_position',
						'default'     => $fusion_settings->get( 'button_gradient_start' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient End Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select end position for gradient.', 'fusion-builder' ),
						'param_name'  => 'gradient_end_position',
						'default'     => $fusion_settings->get( 'button_gradient_end' ),
						'value'       => '',
						'min'         => '0',
						'max'         => '100',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Gradient Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls gradient type.', 'fusion-builder' ),
						'param_name'  => 'gradient_type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'linear' => esc_attr__( 'Linear', 'fusion-builder' ),
							'radial' => esc_attr__( 'Radial', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Radial Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Select direction for radial gradient.', 'fusion-builder' ),
						'param_name'  => 'radial_direction',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''              => esc_attr__( 'Default', 'fusion-builder' ),
							'left top'      => esc_attr__( 'Left Top', 'fusion-builder' ),
							'left center'   => esc_attr__( 'Left Center', 'fusion-builder' ),
							'left bottom'   => esc_attr__( 'Left Bottom', 'fusion-builder' ),
							'right top'     => esc_attr__( 'Right Top', 'fusion-builder' ),
							'right center'  => esc_attr__( 'Right Center', 'fusion-builder' ),
							'right bottom'  => esc_attr__( 'Right Bottom', 'fusion-builder' ),
							'center top'    => esc_attr__( 'Center Top', 'fusion-builder' ),
							'center center' => esc_attr__( 'Center Center', 'fusion-builder' ),
							'center bottom' => esc_attr__( 'Center Bottom', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'linear',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Gradient Angle', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the gradient angle. In degrees.', 'fusion-builder' ),
						'param_name'  => 'linear_angle',
						'default'     => $fusion_settings->get( 'button_gradient_angle' ),
						'value'       => '180',
						'min'         => '',
						'max'         => '360',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'gradient_type',
								'value'    => 'radial',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Accent Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover color of the button text, divider and icon.', 'fusion-builder' ),
						'param_name'  => 'accent_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_accent_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button type.', 'fusion-builder' ),
						'param_name'  => 'type',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''     => esc_attr__( 'Default', 'fusion-builder' ),
							'flat' => esc_attr__( 'Flat', 'fusion-builder' ),
							'3d'   => esc_attr__( '3D', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Bevel Color For 3D Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the bevel color of the button when using 3D button type.', 'fusion-builder' ),
						'param_name'  => 'bevel_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_bevel_color' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flat',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Hover Bevel Color For 3D Mode', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover bevel color of the button when using 3D button type.', 'fusion-builder' ),
						'param_name'  => 'bevel_color_hover',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_bevel_color_hover' ),
						'dependency'  => [
							[
								'element'  => 'type',
								'value'    => 'flat',
								'operator' => '!=',
							],
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Button Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_width',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'       => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Button Border Radius', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the border radius. Enter values including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'border_radius',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'value'            => [
							'border_radius_top_left'     => '',
							'border_radius_top_right'    => '',
							'border_radius_bottom_right' => '',
							'border_radius_bottom_left'  => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Button Border Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the hover border color of the button.', 'fusion-builder' ),
						'param_name'  => 'border_hover_color',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'default'     => $fusion_settings->get( 'button_border_hover_color' ),
						'dependency'  => [
							[
								'element'  => 'color',
								'value'    => 'custom',
								'operator' => '==',
							],
						],
						'preview'     => [
							'selector' => '.fusion-button',
							'type'     => 'class',
							'toggle'   => 'hover',
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the button size.', 'fusion-builder' ),
						'param_name'  => 'size',
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							''       => esc_attr__( 'Custom', 'fusion-builder' ),
							'small'  => esc_attr__( 'Small', 'fusion-builder' ),
							'medium' => esc_attr__( 'Medium', 'fusion-builder' ),
							'large'  => esc_attr__( 'Large', 'fusion-builder' ),
							'xlarge' => esc_attr__( 'XLarge', 'fusion-builder' ),
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the padding for the button.', 'fusion-builder' ),
						'param_name'       => 'padding',
						'group'            => esc_html__( 'Design', 'fusion-builder' ),
						'value'            => [
							'padding_top'    => '',
							'padding_right'  => '',
							'padding_bottom' => '',
							'padding_left'   => '',
						],
						'dependency'       => [
							[
								'element'  => 'size',
								'value'    => '',
								'operator' => '==',
							],
						],
					],
					[
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Typography', 'fusion-builder' ),
						/* translators: URL for the link. */
						'description'      => esc_html__( 'Controls the button typography, if left empty will inherit from globals.', 'fusion-builder' ),
						'param_name'       => 'main_typography',
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
						'choices'          => [
							'font-family'    => 'button_font',
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the button spans the full width of its container.', 'fusion-builder' ),
						'param_name'  => 'stretch',
						'default'     => 'default',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'yes'     => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'      => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_margin_placeholder'            => [
						'param_name' => 'margin',
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the icon on the button.', 'fusion-builder' ),
						'param_name'  => 'icon_position',
						'value'       => [
							'left'  => esc_attr__( 'Left', 'fusion-builder' ),
							'right' => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'left',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Divider', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to display a divider between icon and text.', 'fusion-builder' ),
						'param_name'  => 'icon_divider',
						'default'     => 'no',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
					],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-button',
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
add_action( 'fusion_builder_before_init', 'fusion_element_button' );
