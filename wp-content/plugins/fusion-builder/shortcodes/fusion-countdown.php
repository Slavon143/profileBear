<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_countdown' ) ) {

	if ( ! class_exists( 'FusionSC_Countdown' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Countdown extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * The countdown counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $countdown_counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_countdown-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_countdown-shortcode-countdown-wrapper', [ $this, 'countdown_wrapper_attr' ] );
				add_filter( 'fusion_attr_countdown-shortcode-counter-wrapper', [ $this, 'counter_wrapper_attr' ] );
				add_filter( 'fusion_attr_countdown-shortcode-link', [ $this, 'link_attr' ] );

				add_shortcode( 'fusion_countdown', [ $this, 'render' ] );

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
					'hide_on_mobile'         => fusion_builder_default_visibility( 'string' ),
					'class'                  => '',
					'id'                     => '',
					'background_color'       => $fusion_settings->get( 'countdown_background_color' ),
					'background_image'       => $fusion_settings->get( 'countdown_background_image', 'url' ),
					'background_position'    => $fusion_settings->get( 'countdown_background_position' ),
					'background_repeat'      => $fusion_settings->get( 'countdown_background_repeat' ),
					'border_radius'          => '',
					'counter_box_color'      => $fusion_settings->get( 'countdown_counter_box_color' ),
					'counter_box_spacing'    => $fusion_settings->get( 'countdown_counter_box_spacing' ),
					'counter_border_color'   => $fusion_settings->get( 'countdown_counter_border_color' ),
					'counter_border_radius'  => $fusion_settings->get( 'countdown_counter_border_radius' ),
					'counter_border_size'    => $fusion_settings->get( 'countdown_counter_border_size' ),
					'counter_font_size'      => $fusion_settings->get( 'countdown_counter_font_size' ),
					'counter_padding_bottom' => $fusion_settings->get( 'countdown_counter_padding', 'bottom' ),
					'counter_padding_left'   => $fusion_settings->get( 'countdown_counter_padding', 'left' ),
					'counter_padding_right'  => $fusion_settings->get( 'countdown_counter_padding', 'right' ),
					'counter_padding_top'    => $fusion_settings->get( 'countdown_counter_padding', 'top' ),
					'counter_text_color'     => $fusion_settings->get( 'countdown_counter_text_color' ),
					'countdown_end'          => '2000-01-01 00:00:00',
					'dash_titles'            => 'short',
					'heading_font_size'      => $fusion_settings->get( 'countdown_heading_font_size' ),
					'heading_text'           => '',
					'heading_text_color'     => $fusion_settings->get( 'countdown_heading_text_color' ),
					'label_color'            => $fusion_settings->get( 'countdown_label_color' ),
					'label_font_size'        => $fusion_settings->get( 'countdown_label_font_size' ),
					'label_position'         => $fusion_settings->get( 'countdown_label_position' ),
					'layout'                 => $fusion_settings->get( 'countdown_layout' ),
					'link_text'              => '',
					'link_text_color'        => $fusion_settings->get( 'countdown_link_text_color' ),
					'link_target'            => $fusion_settings->get( 'countdown_link_target' ),
					'link_url'               => '',
					'link_attributes'        => '',
					'show_weeks'             => $fusion_settings->get( 'countdown_show_weeks' ),
					'subheading_font_size'   => $fusion_settings->get( 'countdown_subheading_font_size' ),
					'subheading_text'        => '',
					'subheading_text_color'  => $fusion_settings->get( 'countdown_subheading_text_color' ),
					'timezone'               => $fusion_settings->get( 'countdown_timezone' ),
					'animation_type'         => '',
					'animation_direction'    => 'down',
					'animation_speed'        => '',
					'animation_offset'       => $fusion_settings->get( 'animation_offset' ),
					'element_margin_top'     => '',
					'element_margin_bottom'  => '',
					'element_margin_left'    => '',
					'element_margin_right'   => '',
					'display_when_ended'     => '',
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
					'countdown_background_color'        => 'background_color',
					'countdown_background_image'        => [
						'param'    => 'background_image',
						'callback' => 'urlFromObject',
					],
					'countdown_background_position'     => 'background_position',
					'countdown_background_repeat'       => 'background_repeat',
					'countdown_counter_border_color'    => 'counter_border_color',
					'countdown_counter_border_radius'   => 'counter_border_radius',
					'countdown_counter_border_size'     => 'counter_border_size',
					'countdown_counter_box_color'       => 'counter_box_color',
					'countdown_counter_box_spacing'     => 'counter_box_spacing',
					'countdown_counter_font_size'       => 'counter_font_size',
					'countdown_counter_padding[bottom]' => 'counter_padding_bottom',
					'countdown_counter_padding[left]'   => 'counter_padding_left',
					'countdown_counter_padding[right]'  => 'counter_padding_right',
					'countdown_counter_padding[top]'    => 'counter_padding_bottom',
					'countdown_counter_text_color'      => 'counter_text_color',
					'countdown_heading_font_size'       => 'heading_font_size',
					'countdown_heading_text_color'      => 'heading_text_color',
					'countdown_label_color'             => 'label_color',
					'countdown_label_font_size'         => 'label_font_size',
					'countdown_label_position'          => 'label_position',
					'countdown_layout'                  => 'layout',
					'countdown_link_text_color'         => 'link_text_color',
					'countdown_link_target'             => 'link_target',
					'countdown_show_weeks'              => 'show_weeks',
					'countdown_subheading_font_size'    => 'subheading_font_size',
					'countdown_subheading_text_color'   => 'subheading_text_color',
					'countdown_timezone'                => 'timezone',
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
				return [
					'gmt_offset'   => get_option( 'gmt_offset' ),
					'weeks_text'   => esc_attr__( 'Weeks', 'fusion-builder' ),
					'days_text'    => esc_attr__( 'Days', 'fusion-builder' ),
					'hrs_text'     => esc_attr__( 'Hrs', 'fusion-builder' ),
					'hours_text'   => esc_attr__( 'Hours', 'fusion-builder' ),
					'min_text'     => esc_attr__( 'Min', 'fusion-builder' ),
					'minutes_text' => esc_attr__( 'Minutes', 'fusion-builder' ),
					'sec_text'     => esc_attr__( 'Sec', 'fusion-builder' ),
					'seconds_text' => esc_attr__( 'Seconds', 'fusion-builder' ),
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
				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_countdown' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_countdown', $args );

				$defaults['border_radius']         = FusionBuilder::validate_shortcode_attr_value( $defaults['border_radius'], 'px' );
				$defaults['counter_border_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['counter_border_size'], 'px' );
				$defaults['counter_border_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['counter_border_radius'], 'px' );

				if ( ! isset( $args['counter_border_radius'] ) ) {
					$defaults['counter_border_radius'] = $defaults['border_radius'];
				}

				if ( ! isset( $args['label_color'] ) ) {
					$defaults['label_color'] = $defaults['counter_text_color'];
				}

				if ( 'default' === $defaults['link_target'] ) {
					$defaults['link_target'] = $fusion_settings->get( 'countdown_link_target' );
				}

				$this->args = $defaults;

				if ( 'hide' === $this->args['display_when_ended'] && $this->is_counter_ended() ) {
					return '';
				}

				$html  = '<div ' . FusionBuilder::attributes( 'countdown-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'countdown-shortcode-countdown-wrapper' ) . '>';
				$html .= $this->get_styles();

				if ( $this->args['subheading_text'] || $this->args['heading_text'] ) {
					$html .= '<div ' . FusionBuilder::attributes( 'fusion-countdown-heading-wrapper' ) . '>';
					$html .= '<div ' . FusionBuilder::attributes( 'fusion-countdown-subheading' ) . '>' . $this->args['subheading_text'] . '</div>';
					$html .= '<div ' . FusionBuilder::attributes( 'fusion-countdown-heading' ) . '>' . $this->args['heading_text'] . '</div>';
					$html .= '</div>';
				}

				$html .= '<div ' . FusionBuilder::attributes( 'countdown-shortcode-counter-wrapper' ) . '>';

				$dashes = [
					[
						'show'      => $this->args['show_weeks'],
						'class'     => 'weeks',
						'shortname' => esc_html__( 'Weeks', 'fusion-builder' ),
						'longname'  => esc_html__( 'Weeks', 'fusion-builder' ),
					],
					[
						'show'      => 'yes',
						'class'     => 'days',
						'shortname' => esc_html__( 'Days', 'fusion-builder' ),
						'longname'  => esc_html__( 'Days', 'fusion-builder' ),
					],
					[
						'show'      => 'yes',
						'class'     => 'hours',
						'shortname' => esc_html__( 'Hrs', 'fusion-builder' ),
						'longname'  => esc_html__( 'Hours', 'fusion-builder' ),
					],
					[
						'show'      => 'yes',
						'class'     => 'minutes',
						'shortname' => esc_html__( 'Min', 'fusion-builder' ),
						'longname'  => esc_html__( 'Minutes', 'fusion-builder' ),
					],
					[
						'show'      => 'yes',
						'class'     => 'seconds',
						'shortname' => esc_html__( 'Sec', 'fusion-builder' ),
						'longname'  => esc_html__( 'Seconds', 'fusion-builder' ),
					],
				];

				if ( 'text_flow' !== $this->args['label_position'] ) {
					$this->args['dash_titles'] = 'long';
				}

				$dash_class              = '';
				$alpha_counter_box_color = 1;

				if ( class_exists( 'Fusion_Color' ) ) {
					$alpha_counter_box_color = Fusion_Color::new_color( $this->args['counter_box_color'] )->alpha;
				}

				if ( ! $this->args['counter_box_color'] || 0 === $alpha_counter_box_color || 'transparent' === $this->args['counter_box_color'] ) {
					$dash_class = ' fusion-no-bg';
				}

				$dashes_count = count( $dashes );

				for ( $i = 0; $i < $dashes_count; $i++ ) {
					if ( 'yes' === $dashes[ $i ]['show'] ) {
						$html .= '<div class="fusion-dash-wrapper ' . $dash_class . '">';
						$html .= '<div class="fusion-dash fusion-dash-' . $dashes[ $i ]['class'] . '">';
						$html .= '<div class="fusion-digit-wrapper">';
						if ( 'days' === $dashes[ $i ]['class'] ) {
							$html .= '<div class="fusion-thousand-digit fusion-digit">0</div>';
						}
						if ( 'weeks' === $dashes[ $i ]['class'] || 'days' === $dashes[ $i ]['class'] ) {
							$html .= '<div class="fusion-hundred-digit fusion-digit">0</div>';
						}
						$html .= '<div class="fusion-digit">0</div><div class="fusion-digit">0</div>';
						$html .= '</div>';
						$html .= '<div class="fusion-dash-title">' . esc_html( $dashes[ $i ][ $this->args['dash_titles'] . 'name' ] ) . '</div>';
						$html .= '</div></div>';
					}
				}

				$html .= '</div>';

				if ( $this->args['link_url'] ) {
					$html     .= '<div ' . FusionBuilder::attributes( 'fusion-countdown-link-wrapper' ) . '>';
						$html .= '<a ' . FusionBuilder::attributes( 'countdown-shortcode-link' ) . '>' . $this->args['link_text'] . '</a>';
					$html     .= '</div>';
				}

				$html .= do_shortcode( $content );
				$html .= '</div>';
				$html .= '</div>';

				$this->countdown_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_countdown_content', $html, $args );

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
					'class' => 'fusion-countdown fusion-countdown-' . $this->countdown_counter . ' fusion-countdown-' . $this->args['layout'] . ' fusion-countdown-label-' . $this->args['label_position'],
				];

				if ( $this->args['heading_text'] || $this->args['subheading_text'] ) {
					$attr['class'] .= ' fusion-countdown-has-heading';
				}

				if ( $this->args['link_text'] ) {
					$attr['class'] .= ' fusion-countdown-has-link';
				}

				$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

				$alpha_background_color = 1;

				if ( class_exists( 'Fusion_Color' ) ) {
					$alpha_background_color = Fusion_Color::new_color( $this->args['background_color'] )->alpha;
				}

				if ( ! $this->args['background_image'] && ( ! $this->args['background_color'] || 0 === $alpha_background_color || 'transparent' === $this->args['background_color'] ) ) {
					$attr['class'] .= ' fusion-no-bg';
				}

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				return $attr;
			}

			/**
			 * Builds the countdown-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function countdown_wrapper_attr() {
				$attr = [
					'class' => 'fusion-countdown-wrapper',
				];

				return $attr;
			}

			/**
			 * Builds the counter-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function counter_wrapper_attr() {

				$attr = [
					'class' => 'fusion-countdown-counter-wrapper',
					'id'    => 'fusion-countdown-' . $this->countdown_counter,
				];

				if ( ! $this->args['subheading_text'] && ! $this->args['heading_text'] && ! $this->args['link_url'] ) {
					$attr['style'] = 'flex-grow: 1;';
				}

				if ( 'site_time' === $this->args['timezone'] ) {
					$attr['data-gmt-offset'] = get_option( 'gmt_offset' );
				}

				if ( $this->args['countdown_end'] ) {
					if ( is_array( $this->args['countdown_end'] ) && isset( $this->args['countdown_end']['date'] ) ) {
						$this->args['countdown_end'] = $this->args['countdown_end']['date'];
					}
					$attr['data-timer'] = date( 'Y-m-d-H-i-s', strtotime( $this->args['countdown_end'] ) );
				}

				$attr['data-omit-weeks'] = ( 'yes' === $this->args['show_weeks'] ) ? '0' : '1';

				return $attr;
			}

			/**
			 * Checks if counter is ended.
			 *
			 * @access public
			 * @since 3.3
			 * @return bool
			 */
			public function is_counter_ended() {
				if ( ! is_array( $this->args['countdown_end'] ) && empty( $this->args['countdown_end'] ) ) {
					return true;
				}

				if ( is_array( $this->args['countdown_end'] ) && ( ! isset( $this->args['countdown_end']['date'] ) || empty( $this->args['countdown_end']['date'] ) ) ) {
					return true;
				}

				$end_date = is_array( $this->args['countdown_end'] ) ? strtotime( $this->args['countdown_end']['date'] ) : strtotime( $this->args['countdown_end'] );
				$now      = time();

				if ( isset( $this->args['countdown_end']['args']['start_date'] ) && ! empty( $this->args['countdown_end']['args']['start_date'] ) ) {
					if ( ! is_numeric( $this->args['countdown_end']['args']['start_date'] ) ) {
						$this->args['countdown_end']['args']['start_date'] = strtotime( $this->args['countdown_end']['args']['start_date'] );
					}
					if ( 0 > $now - $this->args['countdown_end']['args']['start_date'] ) {
						return true;
					}
				}
				return 0 > $end_date - $now;
			}

			/**
			 * Builds the link attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function link_attr() {

				$attr = [
					'class'  => 'fusion-countdown-link',
					'target' => $this->args['link_target'],
					'href'   => $this->args['link_url'],
				];

				if ( '_blank' === $this->args['link_target'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}

				// Add additional, custom link attributes correctly formatted to the anchor.
				$attr = fusion_get_link_attributes( $this->args, $attr );

				return $attr;
			}

			/**
			 * Gets the CSS styles.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function get_styles() {
				$this->base_selector = '.fusion-countdown-' . $this->countdown_counter . ' ';
				$this->dynamic_css   = [];

				if ( $this->args['background_image'] && ! $this->is_default( 'background_image' ) ) {
					$this->add_css_property( $this->base_selector, 'background', 'url(' . $this->args['background_image'] . ') ' . $this->args['background_position'] . ' ' . $this->args['background_repeat'] . ' ' . $this->args['background_color'] );
					if ( 'no-repeat' === $this->args['background_repeat'] ) {
						$this->add_css_property( $this->base_selector, '-webkit-background-size', 'cover' );
						$this->add_css_property( $this->base_selector, '-moz-background-size', 'cover' );
						$this->add_css_property( $this->base_selector, '-o-background-size', 'cover' );
						$this->add_css_property( $this->base_selector, 'background-size', 'cover' );
					}
				} elseif ( ! $this->is_default( 'background_color' ) ) {
					$this->add_css_property( $this->base_selector, 'background-color', $this->args['background_color'] );
				}

				if ( $this->args['border_radius'] && ! $this->is_default( 'border_radius' ) ) {
					$this->add_css_property( $this->base_selector, 'border-radius', $this->args['border_radius'] );
				}

				if ( ! $this->is_default( 'counter_box_spacing' ) ) {
					$spacing_value = fusion_library()->sanitize->number( $this->args['counter_box_spacing'] ) / 2;
					$spacing_unit  = fusion_library()->sanitize->get_unit( $this->args['counter_box_spacing'] );
					$this->add_css_property( $this->base_selector . '.fusion-dash-wrapper', 'padding', $spacing_value . $spacing_unit );
					$this->add_css_property( $this->base_selector . '.fusion-countdown-counter-wrapper', 'margin', '0 calc(7.5px - ' . $spacing_value . $spacing_unit . ')' );
				}

				$selector = $this->base_selector . '.fusion-dash';

				if ( ! $this->is_default( 'counter_box_color' ) ) {
					$this->add_css_property( $selector, 'background-color', $this->args['counter_box_color'] );
				}

				$padding_top    = $this->args['counter_padding_top'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['counter_padding_top'] ) : '0';
				$padding_right  = $this->args['counter_padding_right'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['counter_padding_right'] ) : '0';
				$padding_bottom = $this->args['counter_padding_bottom'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['counter_padding_bottom'] ) : '0';
				$padding_left   = $this->args['counter_padding_left'] ? fusion_library()->sanitize->get_value_with_unit( $this->args['counter_padding_left'] ) : '0';

				$this->add_css_property( $selector, 'padding', $padding_top . ' ' . $padding_right . ' ' . $padding_bottom . ' ' . $padding_left );

				if ( '0' !== $this->args['counter_border_size'] && 0 !== $this->args['counter_border_size'] ) {
					$this->add_css_property( $selector . '.fusion-dash', 'border', $this->args['counter_border_size'] . ' solid ' . $this->args['counter_border_color'] );
				}

				if ( ! $this->is_default( 'counter_border_radius' ) ) {
					$this->add_css_property( $selector, 'border-radius', $this->args['counter_border_radius'] );
				}

				if ( ! $this->is_default( 'counter_font_size' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-countdown-counter-wrapper', 'font-size', $this->args['counter_font_size'] );
				}

				if ( ! $this->is_default( 'counter_text_color' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-countdown-counter-wrapper', 'color', $this->args['counter_text_color'] );
				}

				if ( ! $this->is_default( 'label_font_size' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-dash-title', 'font-size', $this->args['label_font_size'] );
				}

				if ( ! $this->is_default( 'label_color' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-dash-title', 'color', $this->args['label_color'] );
				}

				if ( ! $this->is_default( 'heading_font_size' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-countdown-heading', 'font-size', $this->args['heading_font_size'] );
				}

				if ( ! $this->is_default( 'heading_text_color' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-countdown-heading', 'color', $this->args['heading_text_color'] );
				}

				if ( ! $this->is_default( 'subheading_font_size' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-countdown-subheading', 'font-size', $this->args['subheading_font_size'] );
				}

				if ( ! $this->is_default( 'subheading_text_color' ) ) {
					$this->add_css_property( $this->base_selector . '.fusion-countdown-subheading', 'color', $this->args['subheading_text_color'] );
				}

				if ( ! $this->is_default( 'link_text_color' ) ) {
					$this->add_css_property( $this->base_selector . ' .fusion-countdown-link', 'color', $this->args['link_text_color'] );
				}

				if ( ! $this->is_default( 'element_margin_top' ) && '' !== $this->args['element_margin_top'] ) {
					$this->add_css_property( $this->base_selector, 'margin-top', $this->args['element_margin_top'] );
				}

				if ( ! $this->is_default( 'element_margin_bottom' ) && '' !== $this->args['element_margin_bottom'] ) {
					$this->add_css_property( $this->base_selector, 'margin-bottom', $this->args['element_margin_bottom'] );
				}

				if ( ! $this->is_default( 'element_margin_left' ) && '' !== $this->args['element_margin_left'] ) {
					$this->add_css_property( $this->base_selector, 'margin-left', $this->args['element_margin_left'] );
				}

				if ( ! $this->is_default( 'element_margin_right' ) && '' !== $this->args['element_margin_right'] ) {
					$this->add_css_property( $this->base_selector, 'margin-right', $this->args['element_margin_right'] );
				}

				$css = $this->parse_css();
				return $css ? '<style type="text/css">' . $css . '</style>' : '';
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $dynamic_css_helpers;

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-countdown' ], '.fusion-countdown.fusion-countdown-floated' );

				$elements = array_merge(
					[ '.fusion-countdown .fusion-countdown-wrapper' ],
					$dynamic_css_helpers->map_selector( $main_elements, ' .fusion-countdown-heading-wrapper' ),
					$dynamic_css_helpers->map_selector( $main_elements, ' .fusion-countdown-link-wrapper' )
				);
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-countdown-heading-wrapper' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['text-align'] = 'center';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, '.fusion-countdown-has-heading .fusion-countdown-counter-wrapper' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-top'] = '1em';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, '.fusion-countdown-has-link .fusion-countdown-counter-wrapper' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['margin-bottom'] = '1em';

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .fusion-countdown-link-wrapper' );
				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['text-align'] = 'center';

				return $css;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1.0
			 * @return array $sections Countdown settings.
			 */
			public function add_options() {
				return [
					'countdown_shortcode_section' => [
						'label'  => esc_html__( 'Countdown', 'fusion-builder' ),
						'id'     => 'countdown_shortcode_section',
						'type'   => 'accordion',
						'icon'   => 'fusiona-calendar-check-o',
						'fields' => [
							'countdown_timezone'           => [
								'label'       => esc_html__( 'Countdown Timezone', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the timezone that is used for the countdown calculation.', 'fusion-builder' ),
								'id'          => 'countdown_timezone',
								'default'     => 'site_time',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'site_time' => esc_html__( 'Site Timezone', 'fusion-builder' ),
									'user_time' => esc_html__( 'User Timezone', 'fusion-builder' ),
								],
							],
							'countdown_layout'             => [
								'label'       => esc_html__( 'Countdown Layout', 'fusion-builder' ),
								'description' => esc_html__( 'Select the layout of the coundown element.', 'fusion-builder' ),
								'id'          => 'countdown_layout',
								'default'     => 'floated',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'floated' => esc_html__( 'Floated', 'fusion-builder' ),
									'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
								],
							],
							'countdown_show_weeks'         => [
								'label'       => esc_html__( 'Countdown Show Weeks', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display the number of weeks in the countdown.', 'fusion-builder' ),
								'id'          => 'countdown_show_weeks',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'On', 'fusion-builder' ),
									'no'  => esc_html__( 'Off', 'fusion-builder' ),
								],
							],
							'countdown_label_position'     => [
								'label'       => esc_html__( 'Countdown Label Position', 'fusion-builder' ),
								'description' => esc_html__( 'Select the position of the date/time labels.', 'fusion-builder' ),
								'id'          => 'countdown_label_position',
								'default'     => 'text_flow',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'text_flow' => esc_attr__( 'Text Flow', 'fusion-builder' ),
									'top'       => esc_html__( 'Top', 'fusion-builder' ),
									'bottom'    => esc_html__( 'Bottom', 'fusion-builder' ),
								],
							],
							'countdown_background_color'   => [
								'label'       => esc_html__( 'Countdown Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color for the countdown box.', 'fusion-builder' ),
								'id'          => 'countdown_background_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--countdown_background_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'countdown_background_image'   => [
								'label'       => esc_html__( 'Countdown Background Image', 'fusion-builder' ),
								'description' => esc_html__( 'Select an image for the countdown box background.', 'fusion-builder' ),
								'id'          => 'countdown_background_image',
								'default'     => '',
								'mod'         => '',
								'type'        => 'media',
								'transport'   => 'postMessage',
							],
							'countdown_background_repeat'  => [
								'label'       => esc_html__( 'Countdown Background Repeat', 'fusion-builder' ),
								'description' => esc_html__( 'Controls how the background image repeats.', 'fusion-builder' ),
								'id'          => 'countdown_background_repeat',
								'default'     => 'no-repeat',
								'type'        => 'select',
								'transport'   => 'postMessage',
								'choices'     => [
									'repeat'    => esc_html__( 'Repeat All', 'fusion-builder' ),
									'repeat-x'  => esc_html__( 'Repeat Horizontal', 'fusion-builder' ),
									'repeat-y'  => esc_html__( 'Repeat Vertical', 'fusion-builder' ),
									'no-repeat' => esc_html__( 'Repeat None', 'fusion-builder' ),
								],
							],
							'countdown_background_position' => [
								'label'       => esc_html__( 'Countdown Background Position', 'fusion-builder' ),
								'description' => esc_html__( 'Controls how the background image is positioned.', 'fusion-builder' ),
								'id'          => 'countdown_background_position',
								'default'     => 'center center',
								'type'        => 'select',
								'transport'   => 'postMessage',
								'choices'     => [
									'top left'      => esc_html__( 'top left', 'fusion-builder' ),
									'top center'    => esc_html__( 'top center', 'fusion-builder' ),
									'top right'     => esc_html__( 'top right', 'fusion-builder' ),
									'center left'   => esc_html__( 'center left', 'fusion-builder' ),
									'center center' => esc_html__( 'center center', 'fusion-builder' ),
									'center right'  => esc_html__( 'center right', 'fusion-builder' ),
									'bottom left'   => esc_html__( 'bottom left', 'fusion-builder' ),
									'bottom center' => esc_html__( 'bottom center', 'fusion-builder' ),
									'bottom right'  => esc_html__( 'bottom right', 'fusion-builder' ),
								],
							],
							'countdown_counter_box_spacing' => [
								'label'       => esc_html__( 'Countdown Counter Box Spacing', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the spacing between the counter boxes.', 'fusion-builder' ),
								'id'          => 'countdown_counter_box_spacing',
								'default'     => '10px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'countdown_counter_box_color'  => [
								'label'       => esc_html__( 'Countdown Counter Box Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color for the counter boxes.', 'fusion-builder' ),
								'id'          => 'countdown_counter_box_color',
								'default'     => 'var(--awb-color7)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name' => '--countdown_counter_box_color',
									],
								],
							],
							'countdown_counter_padding'    => [
								'label'       => esc_html__( 'Countdown Counter Box Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Set the padding for the counter boxes. ', 'fusion-builder' ),
								'id'          => 'countdown_counter_padding',
								'choices'     => [
									'top'    => true,
									'right'  => true,
									'bottom' => true,
									'left'   => true,
								],
								'default'     => [
									'top'    => '0.6em',
									'right'  => '1.1em',
									'bottom' => '0.6em',
									'left'   => '1.1em',
								],
								'type'        => 'spacing',
								'transport'   => 'postMessage',
							],
							'countdown_counter_border_size' => [
								'label'       => esc_html__( 'Countdown Counter Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the counter boxes.', 'fusion-builder' ),
								'id'          => 'countdown_counter_border_size',
								'default'     => '0',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'countdown_counter_border_color' => [
								'label'           => esc_html__( 'Countdown Counter Border Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the border color of the counter boxes.', 'fusion-builder' ),
								'id'              => 'countdown_counter_border_color',
								'default'         => 'var(--awb-color7)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'countdown_counter_border_radius' => [
								'label'       => esc_html__( 'Countdown Counter Border Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border radius of the counter boxes.', 'fusion-builder' ),
								'id'          => 'countdown_counter_border_radius',
								'default'     => '4px',
								'type'        => 'dimension',
								'choices'     => [ 'px', '%' ],
								'transport'   => 'postMessage',
							],
							'countdown_counter_font_size'  => [
								'label'       => esc_html__( 'Countdown Counter Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size for the countdown timer.', 'fusion-builder' ),
								'id'          => 'countdown_counter_font_size',
								'default'     => '18px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'countdown_counter_text_color' => [
								'label'       => esc_html__( 'Countdown Counter Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color for the countdown timer text.', 'fusion-builder' ),
								'id'          => 'countdown_counter_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--countdown_counter_text_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'countdown_label_font_size'    => [
								'label'       => esc_html__( 'Countdown Counter Label Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size for the countdown label.', 'fusion-builder' ),
								'id'          => 'countdown_label_font_size',
								'default'     => '18px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'countdown_label_color'        => [
								'label'       => esc_html__( 'Countdown Counter Label Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color for the countdown timer labels.', 'fusion-builder' ),
								'id'          => 'countdown_label_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--countdown_label_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'countdown_heading_font_size'  => [
								'label'       => esc_html__( 'Countdown Heading Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size for the countdown heading.', 'fusion-builder' ),
								'id'          => 'countdown_heading_font_size',
								'default'     => '18px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'countdown_heading_text_color' => [
								'label'       => esc_html__( 'Countdown Heading Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color for the countdown headings.', 'fusion-builder' ),
								'id'          => 'countdown_heading_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--countdown_heading_text_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'countdown_subheading_font_size' => [
								'label'       => esc_html__( 'Countdown Subheading Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size for the countdown subheading.', 'fusion-builder' ),
								'id'          => 'countdown_subheading_font_size',
								'default'     => '14px',
								'type'        => 'dimension',
								'transport'   => 'postMessage',
							],
							'countdown_subheading_text_color' => [
								'label'       => esc_html__( 'Countdown Subheading Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color for the countdown subheadings.', 'fusion-builder' ),
								'id'          => 'countdown_subheading_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'countdown_link_text_color'    => [
								'label'       => esc_html__( 'Countdown Link Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color for the countdown link text.', 'fusion-builder' ),
								'id'          => 'countdown_link_text_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'countdown_link_target'        => [
								'label'       => esc_html__( 'Countdown Link Target', 'fusion-builder' ),
								'description' => esc_html__( 'Controls how the link will open.', 'fusion-builder' ),
								'id'          => 'countdown_link_target',
								'default'     => '_self',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'_self'  => esc_html__( 'Same Window', 'fusion-builder' ),
									'_blank' => esc_html__( 'New Window', 'fusion-builder' ),
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
					'fusion-count-down',
					FusionBuilder::$js_folder_url . '/general/fusion-countdown.js',
					FusionBuilder::$js_folder_path . '/general/fusion-countdown.js',
					[ 'jquery', 'fusion-animations', 'jquery-count-down' ],
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/countdown.min.css' );
			}
		}
	}

	new FusionSC_Countdown();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_countdown() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Countdown',
			[
				'name'          => esc_attr__( 'Countdown', 'fusion-builder' ),
				'shortcode'     => 'fusion_countdown',
				'icon'          => 'fusiona-calendar-check-o',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-countdown-preview.php',
				'preview_id'    => 'fusion-builder-block-module-countdown-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/countdown-element/',
				'inline_editor' => true,
				'params'        => [
					[
						'type'         => 'date_time_picker',
						'heading'      => esc_attr__( 'Countdown Timer End', 'fusion-builder' ),
						'description'  => __( 'Set the end date and time for the countdown time. Click the calendar icon to use the date picker. Use SQL time format: YYYY-MM-DD HH:MM:SS. E.g: 2016-05-10 12:30:00.', 'fusion-builder' ),
						'param_name'   => 'countdown_end',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Timezone', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose which timezone should be used for the countdown calculation.', 'fusion-builder' ),
						'param_name'  => 'timezone',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'site_time' => esc_attr__( 'Timezone of Site', 'fusion-builder' ),
							'user_time' => esc_attr__( 'Timezone of User', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the layout of the coundown element.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Weeks', 'fusion-builder' ),
						'description' => esc_attr__( 'Select "yes" to show weeks for longer countdowns.', 'fusion-builder' ),
						'param_name'  => 'show_weeks',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Label Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the position of the date/time labels.', 'fusion-builder' ),
						'param_name'  => 'label_position',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'text_flow' => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'top'       => esc_attr__( 'Top', 'fusion-builder' ),
							'bottom'    => esc_attr__( 'Bottom', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Display When Inactive', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if the element should be shown when inactive.', 'fusion-builder' ),
						'param_name'  => 'display_when_ended',
						'default'     => 'show',
						'value'       => [
							'show' => esc_html__( 'Show', 'fusion-builder' ),
							'hide' => esc_html__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Counter Box Spacing', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the space between the counter boxes. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'counter_box_spacing',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Box Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a background color for the counter boxes.', 'fusion-builder' ),
						'param_name'  => 'counter_box_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_counter_box_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Counter Box Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'In pixels.', 'fusion-builder' ),
						'param_name'  => 'counter_border_size',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'countdown_counter_border_size' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Box Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color for the counter boxes.', 'fusion-builder' ),
						'param_name'  => 'counter_border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_counter_border_size' ),
						'dependency'  => [
							[
								'element'  => 'counter_border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Counter Box Border Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border radius for the counter boxes. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
						'param_name'  => 'counter_border_radius',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Counter Box Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'Set the padding for the counter boxes. Enter values including any valid CSS unit, ex: 4%.', 'fusion-builder' ),
						'param_name'       => 'counter_padding',
						'value'            => [
							'counter_padding_top'    => '',
							'counter_padding_right'  => '',
							'counter_padding_bottom' => '',
							'counter_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Counter Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the countdown timer. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'counter_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a text color for the countdown timer.', 'fusion-builder' ),
						'param_name'  => 'counter_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_counter_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Counter Label Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the countdown label. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'label_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Label Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a text color for the countdown timer labels.', 'fusion-builder' ),
						'param_name'  => 'label_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_label_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Heading Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Choose a heading text for the countdown.', 'fusion-builder' ),
						'param_name'   => 'heading_text',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Heading Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the countdown heading. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'heading_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a text color for the countdown heading.', 'fusion-builder' ),
						'param_name'  => 'heading_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_heading_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Subheading Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Choose a subheading text for the countdown.', 'fusion-builder' ),
						'param_name'   => 'subheading_text',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Subheading Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the font size for the countdown subheading. Enter value including any valid CSS unit, ex: 20px.', 'fusion-builder' ),
						'param_name'  => 'subheading_font_size',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Subheading Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a text color for the countdown subheading.', 'fusion-builder' ),
						'param_name'  => 'subheading_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_subheading_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Link URL', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add a url for the link. E.g: http://example.com.', 'fusion-builder' ),
						'param_name'   => 'link_url',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Link Text', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a link text for the countdown.', 'fusion-builder' ),
						'param_name'  => 'link_text',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'link_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Link Target', 'fusion-builder' ),
						'description' => esc_attr__(
							'_self = open in same window
	 				                                      _blank = open in new window',
							'fusion-builder'
						),
						'param_name'  => 'link_target',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'_self'   => esc_attr__( '_self', 'fusion-builder' ),
							'_blank'  => esc_attr__( '_blank', 'fusion-builder' ),
						],
						'default'     => 'default',
						'dependency'  => [
							[
								'element'  => 'link_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a text color for the countdown link.', 'fusion-builder' ),
						'param_name'  => 'link_text_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_link_text_color' ),
						'group'       => __( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'link_url',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'element_margin',
						'value'            => [
							'element_margin_top'    => '',
							'element_margin_right'  => '',
							'element_margin_bottom' => '',
							'element_margin_left'   => '',
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the radius of outer box. In pixels (px), ex: 1px.', 'fusion-builder' ),
						'param_name'  => 'border_radius',
						'value'       => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a background color for the countdown wrapping box.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'countdown_background_color' ),
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Background Image', 'fusion-builder' ),
						'description' => esc_attr__( 'Upload an image to display in the background.', 'fusion-builder' ),
						'param_name'  => 'background_image',
						'value'       => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the position of the background image.', 'fusion-builder' ),
						'param_name'  => 'background_position',
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
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Background Repeat', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose how the background image repeats.' ),
						'param_name'  => 'background_repeat',
						'value'       => [
							''          => esc_attr__( 'Default', 'fusion-builder' ),
							'no-repeat' => esc_attr__( 'No Repeat', 'fusion-builder' ),
							'repeat'    => esc_attr__( 'Repeat Vertically and Horizontally', 'fusion-builder' ),
							'repeat-x'  => esc_attr__( 'Repeat Horizontally', 'fusion-builder' ),
							'repeat-y'  => esc_attr__( 'Repeat Vertically', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Background', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'background_image',
								'value'    => '',
								'operator' => '!=',
							],
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
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
					],
					'fusion_animation_placeholder' => [
						'preview_selector' => '.fusion-meta-tb',
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_countdown' );
