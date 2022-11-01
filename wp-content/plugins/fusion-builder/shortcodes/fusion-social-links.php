<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_social_links' ) ) {

	if ( ! class_exists( 'FusionSC_SocialLinks' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_SocialLinks extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $args;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.3
			 * @var int
			 */
			private $counter = 1;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_social-links-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_social-links-shortcode-social-networks', [ $this, 'social_networks_attr' ] );
				add_filter( 'fusion_attr_social-links-shortcode-icon', [ $this, 'icon_attr' ] );

				add_shortcode( 'fusion_social_links', [ $this, 'render' ] );

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
					'sticky_display'         => '',
					'class'                  => '',
					'id'                     => '',
					'font_size'              => $fusion_settings->get( 'social_links_font_size' ),
					'icons_boxed'            => ( 1 == $fusion_settings->get( 'social_links_boxed' ) ) ? 'yes' : $fusion_settings->get( 'social_links_boxed' ), // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					'icons_boxed_radius'     => fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_boxed_radius' ) ),
					'color_type'             => $fusion_settings->get( 'social_links_color_type' ),
					'icon_colors'            => $fusion_settings->get( 'social_links_icon_color' ),
					'box_colors'             => $fusion_settings->get( 'social_links_box_color' ),
					'icon_order'             => '',
					'show_custom'            => 'no',
					'alignment'              => '',
					'alignment_medium'       => '',
					'alignment_small'        => '',
					'tooltip_placement'      => strtolower( $fusion_settings->get( 'social_links_tooltip_placement' ) ),
					'facebook'               => '',
					'tiktok'                 => '',
					'twitch'                 => '',
					'twitter'                => '',
					'instagram'              => '',
					'linkedin'               => '',
					'dribbble'               => '',
					'rss'                    => '',
					'youtube'                => '',
					'pinterest'              => '',
					'flickr'                 => '',
					'vimeo'                  => '',
					'tumblr'                 => '',
					'discord'                => '',
					'digg'                   => '',
					'blogger'                => '',
					'skype'                  => '',
					'myspace'                => '',
					'deviantart'             => '',
					'yahoo'                  => '',
					'reddit'                 => '',
					'forrst'                 => '',
					'paypal'                 => '',
					'dropbox'                => '',
					'soundcloud'             => '',
					'vk'                     => '',
					'wechat'                 => '',
					'whatsapp'               => '',
					'telegram'               => '',
					'teams'                  => '',
					'xing'                   => '',
					'yelp'                   => '',
					'spotify'                => '',
					'email'                  => '',
					'phone'                  => '',

					'icon_colors_hover'      => $fusion_settings->get( 'social_links_icon_color_hover' ),
					'box_colors_hover'       => $fusion_settings->get( 'social_links_box_color_hover' ),

					'box_padding_top'        => '',
					'box_padding_right'      => '',
					'box_padding_bottom'     => '',
					'box_padding_left'       => '',

					// border.
					'box_border_top'         => ( '' !== $fusion_settings->get( 'social_links_border', 'top' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_border', 'top' ) ) : '1px',
					'box_border_right'       => ( '' !== $fusion_settings->get( 'social_links_border', 'right' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_border', 'right' ) ) : '1px',
					'box_border_bottom'      => ( '' !== $fusion_settings->get( 'social_links_border', 'bottom' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_border', 'bottom' ) ) : '1px',
					'box_border_left'        => ( '' !== $fusion_settings->get( 'social_links_border', 'left' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_border', 'left' ) ) : '1px',

					'box_border_color'       => $fusion_settings->get( 'social_links_border_color' ),
					'box_border_color_hover' => $fusion_settings->get( 'social_links_border_color_hover' ),

					// margin.
					'margin_top'             => ( '' !== $fusion_settings->get( 'social_links_margin', 'top' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_margin', 'top' ) ) : '0px',
					'margin_right'           => ( '' !== $fusion_settings->get( 'social_links_margin', 'right' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_margin', 'right' ) ) : '0px',
					'margin_bottom'          => ( '' !== $fusion_settings->get( 'social_links_margin', 'bottom' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_margin', 'bottom' ) ) : '0px',
					'margin_left'            => ( '' !== $fusion_settings->get( 'social_links_margin', 'left' ) ) ? fusion_library()->sanitize->size( $fusion_settings->get( 'social_links_margin', 'left' ) ) : '0px',
					'margin_top_medium'      => '',
					'margin_right_medium'    => '',
					'margin_bottom_medium'   => '',
					'margin_left_medium'     => '',
					'margin_top_small'       => '',
					'margin_right_small'     => '',
					'margin_bottom_small'    => '',
					'margin_left_small'      => '',

					// animation.
					'animation_direction'    => 'left',
					'animation_offset'       => $fusion_settings->get( 'animation_offset' ),
					'animation_speed'        => '',
					'animation_type'         => '',
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
					'social_links_boxed'                 => [
						'param'    => 'icons_boxed',
						'callback' => 'toYes',
					],
					'social_links_margin[top]'           => 'margin_top',
					'social_links_margin[right]'         => 'margin_right',
					'social_links_margin[bottom]'        => 'margin_bottom',
					'social_links_margin[left]'          => 'margin_left',
					'social_links_boxed_radius'          => 'icons_boxed_radius',
					'social_links_font_size'             => 'font_size',
					'social_links_tooltip_placement'     => [
						'param'    => 'tooltip_placement',
						'callback' => 'toLowerCase',
					],
					'social_links_color_type'            => 'color_type',
					'social_links_icon_color'            => 'icon_colors',
					'social_links_box_color'             => 'box_colors',
					'social_links_icon_color_hover'      => 'icon_colors_hover',
					'social_links_box_color_hover'       => 'box_colors_hover',
					'social_links_boxed_padding[top]'    => 'box_padding_top',
					'social_links_boxed_padding[right]'  => 'box_padding_right',
					'social_links_boxed_padding[bottom]' => 'box_padding_bottom',
					'social_links_boxed_padding[left]'   => 'box_padding_left',
					'social_links_border[top]'           => 'box_border_top',
					'social_links_border[right]'         => 'box_border_right',
					'social_links_border[bottom]'        => 'box_border_bottom',
					'social_links_border[left]'          => 'box_border_left',
					'social_links_border_color'          => 'box_border_color',
					'social_links_border_color_hover'    => 'box_border_color_hover',
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
					'linktarget'                => $fusion_settings->get( 'social_icons_new' ),
					'social_media_icons'        => $fusion_settings->get( 'social_media_icons' ),
					'boxed_padding'             => $fusion_settings->get( 'social_links_boxed_padding' ),
					'social_links_box_color'    => $fusion_settings->get( 'social_links_box_color' ),
					'social_links_icon_color'   => $fusion_settings->get( 'social_links_icon_color' ),
					'social_links_border_color' => $fusion_settings->get( 'social_links_border_color' ),
					'body_font_size'            => $fusion_settings->get( 'body_typography', 'font-size' ),
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
					'social_icons_new'           => 'linktarget',
					'social_links_box_color'     => 'social_links_box_color',
					'social_links_icon_color'    => 'social_links_icon_color',
					'social_media_icons'         => 'social_media_icons',
					'social_links_boxed_padding' => 'boxed_padding',
					'social_links_border_color'  => 'box_border_color',
					'body_typography'            => 'body_font_size',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_social_links' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_social_links', $args );

				foreach ( $args as $key => $arg ) {
					if ( false !== strpos( $key, 'custom_' ) ) {
						$defaults[ $key ] = $arg;
					}
				}
				$defaults['icons_boxed_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['icons_boxed_radius'], 'px' );
				$defaults['font_size']          = FusionBuilder::validate_shortcode_attr_value( $defaults['font_size'], 'px' );

				extract( $defaults );

				$this->args = $defaults;

				if ( empty( $defaults['color_type'] ) ) {
					$defaults['box_colors']  = $fusion_settings->get( 'social_links_box_color' );
					$defaults['icon_colors'] = $fusion_settings->get( 'social_links_icon_color' );
				}

				$social_networks = fusion_builder_get_social_networks( $defaults );

				$social_networks = fusion_builder_sort_social_networks( $social_networks );

				$icons = fusion_builder_build_social_links( $social_networks, 'social-links-shortcode-icon', $defaults );

				$html  = '<div ' . FusionBuilder::attributes( 'social-links-shortcode' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'social-links-shortcode-social-networks' ) . '>';
				$html .= '<div ' . FusionBuilder::attributes( 'fusion-social-networks-wrapper' ) . '>';
				$html .= $icons;
				$html .= '</div>';
				$html .= '</div>';
				$html .= '</div>';

				if ( $alignment && ! fusion_element_rendering_is_flex() ) {
					$html = '<div class="align' . $alignment . '">' . $html . '</div>';
				}

				$html .= $this->get_styles();
				$this->counter ++;
				$this->on_render();

				return apply_filters( 'fusion_element_social_links_content', $html, $args );

			}

			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @return string
			 * @since 3.3
			 */
			private function get_styles() {
				$fusion_settings = awb_get_fusion_settings();

				$this->base_selector = '.fusion-social-links-' . $this->counter;
				$this->dynamic_css   = [];

				if ( fusion_element_rendering_is_flex() && ! $this->is_default( 'alignment' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'text-align', $this->args['alignment'] );
				}

				// Icon styles.
				if ( 'brand' !== $this->args['color_type'] ) {
					$icon_selector = [ $this->base_selector . ' .boxed-icons .fusion-social-network-icon' ];
					if ( '' !== $this->args['box_border_top'] ) {
						$this->add_css_property( $icon_selector, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['box_border_top'] ), true );
					}
					if ( '' !== $this->args['box_border_right'] ) {
						$this->add_css_property( $icon_selector, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['box_border_right'] ), true );
					}
					if ( '' !== $this->args['box_border_bottom'] ) {
						$this->add_css_property( $icon_selector, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['box_border_bottom'] ), true );
					}
					if ( '' !== $this->args['box_border_left'] ) {
						$this->add_css_property( $icon_selector, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['box_border_left'] ), true );
					}
					if ( '' !== $this->args['box_border_color'] ) {
						$this->add_css_property( $icon_selector, 'border-color', $this->args['box_border_color'], true );
					}

					$icon_selector = [ $this->base_selector . ' .boxed-icons .fusion-social-network-icon:hover' ];
					if ( '' !== $this->args['box_colors_hover'] ) {
						$this->add_css_property( $icon_selector, 'background-color', $this->args['box_colors_hover'], true );
					}
					if ( '' !== $this->args['box_border_color_hover'] ) {
						$this->add_css_property( $icon_selector, 'border-color', $this->args['box_border_color_hover'], true );
					}

					$icon_selector = [ $this->base_selector . ' .fusion-social-network-icon:hover' ];
					if ( '' !== $this->args['icon_colors_hover'] ) {
						$this->add_css_property( $icon_selector, 'color', $this->args['icon_colors_hover'], true );
					}
				}

				// Apply boxed padding.
				if ( 'yes' === $this->args['icons_boxed'] ) {
					$icon_selector = [ $this->base_selector . ' .boxed-icons .fusion-social-network-icon' ];
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {
						$key = 'box_padding_' . $direction;
						if ( ! $this->is_default( $key ) ) {
							$this->add_css_property( $icon_selector, 'padding-' . $direction, fusion_library()->sanitize->get_value_with_unit( $this->args[ $key ] ), true );
						}
					}
				}

				$css = $this->parse_css();

				if ( fusion_element_rendering_is_flex() && ! $this->is_default( 'alignment_medium' ) ) {
					$this->dynamic_css = [];
					$this->add_css_property( [ $this->base_selector ], 'text-align', $this->args['alignment_medium'] );
					$css .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_medium' ) . 'px){' .
						$this->parse_css() . ' }';
				}

				if ( fusion_element_rendering_is_flex() && ! $this->is_default( 'alignment_small' ) ) {
					$this->dynamic_css = [];
					$this->add_css_property( [ $this->base_selector ], 'text-align', $this->args['alignment_small'] );
					$css .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_small' ) . 'px){' .
						$this->parse_css() . ' }';
				}

				$css .= $this->build_margin_styles();

				return $css ? '<style type="text/css">' . $css . '</style>' : '';
			}

			/**
			 * Builds margin styles.
			 *
			 * @access public
			 * @since 1.0
			 * @return string
			 */
			public function build_margin_styles() {
				// Responsive Margin.
				$fusion_settings = awb_get_fusion_settings();
				$styles          = '';

				foreach ( [ 'large', 'medium', 'small' ] as $size ) {
					$margin_styles = '';
					foreach ( [ 'top', 'right', 'bottom', 'left' ] as $direction ) {

						$margin_key = 'large' === $size ? 'margin_' . $direction : 'margin_' . $direction . '_' . $size;
						if ( '' !== $this->args[ $margin_key ] ) {
							$margin_styles .= 'margin-' . $direction . ' : ' . fusion_library()->sanitize->get_value_with_unit( $this->args[ $margin_key ] ) . ';';
						}
					}

					if ( '' === $margin_styles ) {
						continue;
					}

					$margin_styles = '.fusion-social-links-' . $this->counter . '{ ' . $margin_styles . '}';

					// Large styles, no wrapping needed.
					if ( 'large' === $size ) {
						$styles .= $margin_styles;
					} else {
						// Medium and Small size screen styles.
						$styles .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_' . $size ) . 'px) {' . $margin_styles . '}';
					}
				}

				return $styles;
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
						'class' => 'fusion-social-links fusion-social-links-' . $this->counter,
					]
				);

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

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
			 * Builds the social-networks attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function social_networks_attr() {

				$attr = [
					'class' => 'fusion-social-networks',
				];

				if ( 'yes' === $this->args['icons_boxed'] ) {
					$attr['class'] .= ' boxed-icons';
				}

				return $attr;

			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @param array $args The arguments array.
			 * @return array
			 */
			public function icon_attr( $args ) {
				$fusion_settings = awb_get_fusion_settings();

				$attr = [
					'class' => 'fusion-social-network-icon fusion-tooltip fusion-' . $args['social_network'],
					'style' => '',
				];

				$tooltip = Fusion_Social_Icon::get_social_network_name( $args['social_network'] );
				if ( '' === $args['social_network'] || 'custom_' === substr( $args['social_network'], 0, 7 ) ) {
					$attr['class']         .= 'custom ';
					$args['social_network'] = strtolower( str_replace( ' ', '-', $tooltip ) );
				} elseif ( ! empty( $args['icon_mark'] ) ) {
					$attr['class'] .= ' ' . $args['icon_mark'];
				} else {
					$attr['class'] .= ' awb-icon-' . $args['social_network'];
				}

				if ( 'none' !== strtolower( $this->args['tooltip_placement'] ) ) {
					$attr['data-placement'] = strtolower( $this->args['tooltip_placement'] );
					$attr['data-title']     = $tooltip;
					$attr['data-toggle']    = 'tooltip';
				}

				$attr['title'] = $tooltip;

				$attr['aria-label'] = $args['social_network'];

				$link = $args['social_link'];

				$attr['target'] = ( $fusion_settings->get( 'social_icons_new' ) ) ? '_blank' : '_self';

				if ( '_blank' === $attr['target'] ) {
					$attr['rel'] = 'noopener noreferrer';
				}

				if ( 'mail' === $args['social_network'] ) {
					$link = $args['social_link'];
					if ( 'http' !== substr( $args['social_link'], 0, 4 ) ) {
						if ( apply_filters( 'fusion_disable_antispambot', false ) ) {
							$link = 'mailto:' . str_replace( 'mailto:', '', $args['social_link'] );
						} else {
							$link = 'mailto:' . antispambot( str_replace( 'mailto:', '', $args['social_link'] ) );
						}
					}
				}

				if ( 'phone' === $args['social_network'] ) {
					$link           = 'tel:' . str_replace( 'tel:', '', $args['social_link'] );
					$attr['target'] = '_self';
				}

				$attr['href'] = $link;

				if ( $fusion_settings->get( 'nofollow_social_links' ) ) {
					$attr['rel'] = ( isset( $attr['rel'] ) ) ? $attr['rel'] . ' nofollow' : 'nofollow';
				}

				if ( $args['icon_color'] ) {
					$attr['style'] = 'color:' . $args['icon_color'] . ';';
				}

				if ( $this->args['font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['font_size'] . ';';

					if ( 'yes' === $this->args['icons_boxed'] ) {
						$font_size      = ( false === strpos( $this->args['font_size'], 'px' ) ) ? fusion_library()->sanitize->convert_font_size_to_px( $this->args['font_size'], $fusion_settings->get( 'body_typography', 'font-size' ) ) . 'px' : $this->args['font_size'];
						$attr['style'] .= 'width:' . $font_size . ';';
					}
				}

				if ( 'yes' === $this->args['icons_boxed'] && $args['box_color'] ) {
					$attr['style'] .= 'background-color:' . $args['box_color'] . ';border-color:' . $args['box_color'] . ';';
				}

				if ( 'yes' === $this->args['icons_boxed'] && $this->args['icons_boxed_radius'] || '0' === $this->args['icons_boxed_radius'] ) {
					if ( 'round' === $this->args['icons_boxed_radius'] ) {
						$this->args['icons_boxed_radius'] = '50%';
					}
					$attr['style'] .= 'border-radius:' . $this->args['icons_boxed_radius'] . ';';
				}

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Social Links settings.
			 */
			public function add_options() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'social_links_shortcode_section' => [
						'label'       => esc_html__( 'Social Links Element', 'fusion-builder' ),
						'description' => '',
						'id'          => 'social_links_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-link',
						'fields'      => [
							'social_links_info'            => [
								'id'          => 'social_links_info',
								'type'        => 'custom',
								'description' => '<div class="fusion-redux-important-notice">' . __( '<strong>IMPORTANT NOTE:</strong> These social icon global options control both the social link element and person element.', 'fusion-builder' ) . '</div>',
							],
							'social_links_margin'          => [
								'label'       => esc_html__( 'Margins', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the margin around social links element.', 'fusion-builder' ),
								'id'          => 'social_links_margin',
								'type'        => 'spacing',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => true,
									'left'   => true,
									'bottom' => true,
									'right'  => true,
								],
								'default'     => [
									'top'    => '0px',
									'left'   => '0px',
									'bottom' => '0px',
									'right'  => '0px',
								],
							],
							'social_links_tooltip_placement' => [
								'label'       => esc_html__( 'Social Links Icons Tooltip Position', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the tooltip position of the social links icons.', 'fusion-builder' ),
								'id'          => 'social_links_tooltip_placement',
								'default'     => 'Top',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'Top'    => esc_html__( 'Top', 'fusion-builder' ),
									'Right'  => esc_html__( 'Right', 'fusion-builder' ),
									'Bottom' => esc_html__( 'Bottom', 'fusion-builder' ),
									'Left'   => esc_html__( 'Left', 'fusion-builder' ),
									'None'   => esc_html__( 'None', 'fusion-builder' ),
								],
							],
							'social_links_font_size'       => [
								'label'       => esc_html__( 'Icons Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size for the social link icons.', 'fusion-builder' ),
								'id'          => 'social_links_font_size',
								'default'     => '16px',
								'type'        => 'dimension',
								'css_vars'    => [
									[
										'name'     => '--social_links_font_size',
										'element'  => '.fusion-social-links',
										'callback' => [ 'convert_font_size_to_px', '' ],
									],
								],
							],
							'social_links_color_type'      => [
								'label'       => esc_html__( 'Icon Color Type', 'fusion-builder' ),
								'description' => esc_html__( 'Custom colors allow you to choose a color for icons and boxes. Brand colors will use the exact brand color of each network for the icons or boxes.', 'fusion-builder' ),
								'id'          => 'social_links_color_type',
								'default'     => 'custom',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'custom' => esc_html__( 'Custom Colors', 'fusion-builder' ),
									'brand'  => esc_html__( 'Brand Colors', 'fusion-builder' ),
								],
							],
							'social_links_icon_color'      => [
								'label'           => esc_html__( 'Icons Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the custom icons.', 'fusion-builder' ),
								'id'              => 'social_links_icon_color',
								'default'         => 'var(--awb-color6)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_icon_color_hover' => [
								'label'           => esc_html__( 'Icons Hover Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the color of the custom icons on hover.', 'fusion-builder' ),
								'id'              => 'social_links_icon_color_hover',
								'default'         => 'var(--awb-color4)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_boxed'           => [
								'label'       => esc_html__( 'Boxed Style', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to have the icon displayed in a small box. Turn off to have the icon displayed with no box.', 'fusion-builder' ),
								'id'          => 'social_links_boxed',
								'default'     => '0',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'social_links_boxed_padding'   => [
								'label'           => esc_html__( 'Boxed Padding', 'fusion-builder' ),
								'id'              => 'social_links_boxed_padding',
								'default'         => '8px',
								'type'            => 'dimension',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
								'css_vars'        => [
									[
										'name'    => '--social_links_boxed_padding',
										'element' => '.fusion-social-links',
									],
								],
							],
							'social_links_box_color'       => [
								'label'           => esc_html__( 'Box Background', 'fusion-builder' ),
								'description'     => esc_html__( 'Select a custom social icon box background color.', 'fusion-builder' ),
								'id'              => 'social_links_box_color',
								'default'         => 'var(--awb-color2)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_box_color_hover' => [
								'label'           => esc_html__( 'Box Hover Background', 'fusion-builder' ),
								'description'     => esc_html__( 'Select a custom social icon box background color on hover.', 'fusion-builder' ),
								'id'              => 'social_links_box_color_hover',
								'default'         => 'var(--awb-color1)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_border'          => [
								'label'       => esc_html__( 'Box Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the borders size of social icon box.', 'fusion-builder' ),
								'id'          => 'social_links_border',
								'type'        => 'spacing',
								'transport'   => 'postMessage',
								'choices'     => [
									'top'    => true,
									'left'   => true,
									'bottom' => true,
									'right'  => true,
								],
								'default'     => [
									'top'    => '0px',
									'left'   => '0px',
									'bottom' => '0px',
									'right'  => '0px',
								],
							],
							'social_links_boxed_radius'    => [
								'label'           => esc_html__( 'Box Radius', 'fusion-builder' ),
								'description'     => esc_html__( 'Box radius for the social icons.', 'fusion-builder' ),
								'id'              => 'social_links_boxed_radius',
								'default'         => '4px',
								'type'            => 'dimension',
								'choices'         => [ 'px', 'em' ],
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_border_color'    => [
								'label'           => esc_html__( 'Border Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the borders color of social icon box.', 'fusion-builder' ),
								'id'              => 'social_links_border_color',
								'default'         => 'var(--awb-color3)',
								'type'            => 'color-alpha',
								'transport'       => 'postMessage',
								'soft_dependency' => true,
							],
							'social_links_border_color_hover' => [
								'label'           => esc_html__( 'Border Hover Color', 'fusion-builder' ),
								'description'     => esc_html__( 'Controls the borders color of social icon box on hover.', 'fusion-builder' ),
								'id'              => 'social_links_border_color_hover',
								'default'         => 'var(--awb-color4)',
								'type'            => 'color-alpha',
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-tooltip' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/social-links.min.css' );
			}
		}
	}

	new FusionSC_SocialLinks();

}
/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_social_links() {
	$fusion_settings = awb_get_fusion_settings();

	$social_options = [
		'name'      => esc_attr__( 'Social Links', 'fusion-builder' ),
		'shortcode' => 'fusion_social_links',
		'icon'      => 'fusiona-link',
		'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/social-links-element/',
		'params'    => [
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
				'description' => esc_attr__( "Select the icon's alignment.", 'fusion-builder' ),
				'param_name'  => 'alignment',
				'value'       => [
					''       => esc_attr__( 'Text Flow', 'fusion-builder' ),
					'left'   => esc_attr__( 'Left', 'fusion-builder' ),
					'center' => esc_attr__( 'Center', 'fusion-builder' ),
					'right'  => esc_attr__( 'Right', 'fusion-builder' ),
				],
				'responsive'  => [
					'state' => 'large',
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			'fusion_margin_placeholder'    => [
				'param_name' => 'margin',
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
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Tooltip Position', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the display position for tooltips.', 'fusion-builder' ),
				'param_name'  => 'tooltip_placement',
				'value'       => [
					''       => esc_attr__( 'Default', 'fusion-builder' ),
					'top'    => esc_attr__( 'Top', 'fusion-builder' ),
					'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
					'left'   => esc_attr__( 'Left', 'fusion-builder' ),
					'Right'  => esc_attr__( 'Right', 'fusion-builder' ),
					'none'   => esc_html__( 'None', 'fusion-builder' ),
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Font Size', 'fusion-builder' ),
				'description' => esc_attr__( 'Controls the font size for the social icons. Enter value including CSS unit (px, em, rem), ex: 10px', 'fusion-builder' ),
				'param_name'  => 'font_size',
				'value'       => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Boxed Style', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose to get boxed icons.', 'fusion-builder' ),
				'param_name'  => 'icons_boxed',
				'value'       => [
					''    => esc_attr__( 'Default', 'fusion-builder' ),
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'             => 'dimension',
				'remove_from_atts' => true,
				'heading'          => esc_attr__( 'Boxed Padding', 'fusion-builder' ),
				'description'      => esc_attr__( 'Controls the padding of boxed icons.', 'fusion-builder' ),
				'param_name'       => 'box_padding',
				'value'            => [
					'box_padding_top'    => '',
					'box_padding_right'  => '',
					'box_padding_bottom' => '',
					'box_padding_left'   => '',
				],
				'dependency'       => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
				],
				'group'            => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'             => 'dimension',
				'remove_from_atts' => true,
				'heading'          => esc_attr__( 'Box Border Size', 'fusion-builder' ),
				'description'      => esc_attr__( 'Controls the border size. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
				'param_name'       => 'box_border',
				'value'            => [
					'box_border_top'    => '',
					'box_border_right'  => '',
					'box_border_bottom' => '',
					'box_border_left'   => '',
				],
				'group'            => esc_attr__( 'Design', 'fusion-builder' ),
				'dependency'       => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'textfield',
				'heading'     => esc_attr__( 'Box Border Radius', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the border radius of the boxed icons. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
				'param_name'  => 'icons_boxed_radius',
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Color Type', 'fusion-builder' ),
				'description' => esc_attr__( 'Choose the color type of social icons. Brand colors will use the exact brand color of each network for the icons or boxes.', 'fusion-builder' ),
				'param_name'  => 'color_type',
				'value'       => [
					''       => esc_attr__( 'Default', 'fusion-builder' ),
					'custom' => esc_attr__( 'Custom Colors', 'fusion-builder' ),
					'brand'  => esc_attr__( 'Brand Colors', 'fusion-builder' ),
				],
				'default'     => '',
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			// colors group.
			[
				'type'             => 'subgroup',
				'heading'          => esc_attr__( 'Custom Colors', 'fusion-builder' ),
				'description'      => esc_attr__( 'Customize social icons appearance.', 'fusion-builder' ),
				'param_name'       => 'custom_colors',
				'default'          => 'regular',
				'group'            => esc_attr__( 'Design', 'fusion-builder' ),
				'remove_from_atts' => true,
				'value'            => [
					'regular' => esc_attr__( 'Regular', 'fusion-builder' ),
					'hover'   => esc_attr__( 'Hover', 'fusion-builder' ),
				],
				'icons'            => [
					'regular' => '<span class="fusiona-regular-state" style="font-size:18px;"></span>',
					'hover'   => '<span class="fusiona-hover-state" style="font-size:18px;"></span>',
				],
				'dependency'       => [
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the color of social icons.', 'fusion-builder' ),
				'param_name'  => 'icon_colors',
				'default'     => $fusion_settings->get( 'social_links_icon_color' ),
				'subgroup'    => [
					'name' => 'custom_colors',
					'tab'  => 'regular',
				],
				'dependency'  => [
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the color of social icons on hover.', 'fusion-builder' ),
				'param_name'  => 'icon_colors_hover',
				'default'     => $fusion_settings->get( 'social_links_icon_color_hover' ),
				'subgroup'    => [
					'name' => 'custom_colors',
					'tab'  => 'hover',
				],
				'dependency'  => [
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the box background color of social icons.', 'fusion-builder' ),
				'param_name'  => 'box_colors',
				'default'     => $fusion_settings->get( 'social_links_box_color' ),
				'subgroup'    => [
					'name' => 'custom_colors',
					'tab'  => 'regular',
				],
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the box background color of social icons on hover.', 'fusion-builder' ),
				'param_name'  => 'box_colors_hover',
				'default'     => $fusion_settings->get( 'social_links_box_color_hover' ),
				'subgroup'    => [
					'name' => 'custom_colors',
					'tab'  => 'hover',
				],
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the border color of social icons.', 'fusion-builder' ),
				'param_name'  => 'box_border_color',
				'default'     => $fusion_settings->get( 'social_links_border_color' ),
				'subgroup'    => [
					'name' => 'custom_colors',
					'tab'  => 'regular',
				],
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'        => 'colorpickeralpha',
				'heading'     => esc_attr__( 'Border color', 'fusion-builder' ),
				'description' => esc_attr__( 'Specify the border color of social icons on hover.', 'fusion-builder' ),
				'param_name'  => 'box_border_color_hover',
				'default'     => $fusion_settings->get( 'social_links_border_color_hover' ),
				'subgroup'    => [
					'name' => 'custom_colors',
					'tab'  => 'hover',
				],
				'dependency'  => [
					[
						'element'  => 'icons_boxed',
						'value'    => 'no',
						'operator' => '!=',
					],
					[
						'element'  => 'color_type',
						'value'    => 'brand',
						'operator' => '!=',
					],
				],
				'group'       => esc_attr__( 'Design', 'fusion-builder' ),
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Blogger Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Blogger link.', 'fusion-builder' ),
				'param_name'   => 'blogger',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Deviantart Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Deviantart link.', 'fusion-builder' ),
				'param_name'   => 'deviantart',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Discord Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Discord link.', 'fusion-builder' ),
				'param_name'   => 'discord',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Digg Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Digg link.', 'fusion-builder' ),
				'param_name'   => 'digg',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Dribbble Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Dribbble link.', 'fusion-builder' ),
				'param_name'   => 'dribbble',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Dropbox Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Dropbox link.', 'fusion-builder' ),
				'param_name'   => 'dropbox',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Facebook Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Facebook link.', 'fusion-builder' ),
				'param_name'   => 'facebook',
				'value'        => '#',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Flickr Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Flickr link.', 'fusion-builder' ),
				'param_name'   => 'flickr',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Forrst Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Forrst link.', 'fusion-builder' ),
				'param_name'   => 'forrst',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Instagram Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Instagram link.', 'fusion-builder' ),
				'param_name'   => 'instagram',
				'value'        => '#',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'LinkedIn Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom LinkedIn link.', 'fusion-builder' ),
				'param_name'   => 'linkedin',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Myspace Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Myspace link.', 'fusion-builder' ),
				'param_name'   => 'myspace',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'PayPal Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom PayPal link.', 'fusion-builder' ),
				'param_name'   => 'paypal',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Pinterest Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Pinterest link.', 'fusion-builder' ),
				'param_name'   => 'pinterest',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Reddit Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Reddit link.', 'fusion-builder' ),
				'param_name'   => 'reddit',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'RSS Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom RSS link.', 'fusion-builder' ),
				'param_name'   => 'rss',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Skype Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Skype link.', 'fusion-builder' ),
				'param_name'   => 'skype',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'SoundCloud Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom SoundCloud link.', 'fusion-builder' ),
				'param_name'   => 'soundcloud',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Spotify Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Spotify link.', 'fusion-builder' ),
				'param_name'   => 'spotify',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Teams Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Microsoft Teams link.', 'fusion-builder' ),
				'param_name'   => 'teams',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Telegram Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Telegram link.', 'fusion-builder' ),
				'param_name'   => 'telegram',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Tiktok Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Tiktok link.', 'fusion-builder' ),
				'param_name'   => 'tiktok',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Tumblr Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Tumblr link.', 'fusion-builder' ),
				'param_name'   => 'tumblr',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Twitch Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Twitch link.', 'fusion-builder' ),
				'param_name'   => 'twitch',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Twitter Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Twitter link.', 'fusion-builder' ),
				'param_name'   => 'twitter',
				'value'        => '#',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Vimeo Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Vimeo link.', 'fusion-builder' ),
				'param_name'   => 'vimeo',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'VK Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom VK link.', 'fusion-builder' ),
				'param_name'   => 'vk',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'WeChat Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom WeChat link.', 'fusion-builder' ),
				'param_name'   => 'wechat',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'WhatsApp Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom WhatsApp link.', 'fusion-builder' ),
				'param_name'   => 'whatsapp',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Xing Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Xing link.', 'fusion-builder' ),
				'param_name'   => 'xing',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Yahoo Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Yahoo link.', 'fusion-builder' ),
				'param_name'   => 'yahoo',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Yelp Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Yelp link.', 'fusion-builder' ),
				'param_name'   => 'yelp',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Youtube Link', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert your custom Youtube link.', 'fusion-builder' ),
				'param_name'   => 'youtube',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Email Address', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert an email address to display the email icon.', 'fusion-builder' ),
				'param_name'   => 'email',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'         => 'textfield',
				'heading'      => esc_attr__( 'Phone Number', 'fusion-builder' ),
				'description'  => esc_attr__( 'Insert a phone number to display the phone icon.', 'fusion-builder' ),
				'param_name'   => 'phone',
				'value'        => '',
				'dynamic_data' => true,
			],
			[
				'type'        => 'radio_button_set',
				'heading'     => esc_attr__( 'Show Custom Social Icons', 'fusion-builder' ),
				'description' => esc_attr__( 'Show the custom social icons specified in Global Options.', 'fusion-builder' ),
				'param_name'  => 'show_custom',
				'value'       => [
					'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
					'no'  => esc_attr__( 'No', 'fusion-builder' ),
				],
				'default'     => 'no',
			],
			'fusion_animation_placeholder' => [
				'preview_selector' => '.fusion-social-links',
			],
		],
	];
	$custom_social_networks = fusion_builder_get_custom_social_networks();
	if ( is_array( $custom_social_networks ) ) {
		$custom_networks = [];
		foreach ( $custom_social_networks as $key => $custom_network ) {
			$social_options['params'][] = [
				'type'        => 'textfield',
				/* translators: The network-name. */
				'heading'     => sprintf( esc_attr__( '%s Link', 'fusion-builder' ), $custom_network['title'] ),
				'description' => esc_attr__( 'Insert your custom social link.', 'fusion-builder' ),
				'param_name'  => 'custom_' . $key,
				'value'       => '',
				'dependency'  => [
					[
						'element'  => 'show_custom',
						'value'    => 'yes',
						'operator' => '==',
					],
				],
			];
		}
	}
	$social_options['params'][]                                       = [
		'type'        => 'checkbox_button_set',
		'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
		'param_name'  => 'hide_on_mobile',
		'value'       => fusion_builder_visibility_options( 'full' ),
		'default'     => fusion_builder_default_visibility( 'array' ),
		'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
	];
	$social_options['params']['fusion_sticky_visibility_placeholder'] = [];
	$social_options['params'][]                                       = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
		'param_name'  => 'class',
		'value'       => '',
		'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
	];
	$social_options['params'][]                                       = [
		'type'        => 'textfield',
		'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
		'param_name'  => 'id',
		'value'       => '',
		'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
	];
	fusion_builder_map( fusion_builder_frontend_data( 'FusionSC_SocialLinks', $social_options ) );
}
add_action( 'fusion_builder_before_init', 'fusion_element_social_links' );
