<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_sharing' ) ) {

	if ( ! class_exists( 'FusionSC_SharingBox' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_SharingBox extends Fusion_Element {

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
			 * @since 3.1.1
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
				add_filter( 'fusion_attr_sharingbox-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_sharingbox-shortcode-tagline', [ $this, 'tagline_attr' ] );
				add_filter( 'fusion_attr_sharingbox-shortcode-social-networks', [ $this, 'social_networks_attr' ] );
				add_filter( 'fusion_attr_sharingbox-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_sharingbox-shortcode-icon-link', [ $this, 'icon_link_attr' ] );

				add_shortcode( 'fusion_sharing', [ $this, 'render' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @return array
			 * @since 2.0.0
			 */
			public static function get_element_defaults() {
				global $post;

				$fusion_settings = awb_get_fusion_settings();
				$link_and_title  = self::get_link_and_title();

				return [
					'hide_on_mobile'                   => fusion_builder_default_visibility( 'string' ),
					'sticky_display'                   => '',
					'class'                            => '',
					'id'                               => '',
					'backgroundcolor'                  => strtolower( $fusion_settings->get( 'social_bg_color' ) ),
					'description'                      => isset( $post->post_content ) ? fusion_get_content_stripped_and_excerpted( 55, $post->post_content ) : '',
					'color_type'                       => $fusion_settings->get( 'sharing_social_links_color_type' ),
					'icon_colors'                      => strtolower( $fusion_settings->get( 'sharing_social_links_icon_color' ) ),
					'box_colors'                       => strtolower( $fusion_settings->get( 'sharing_social_links_box_color' ) ),
					'icon_taglines'                    => '',
					'icon_tagline_color'               => '',
					'icon_tagline_color_hover'         => '',
					'tagline_text_size'                => '',
					'icon_size'                        => $fusion_settings->get( 'sharing_social_links_font_size' ),
					'icons_boxed'                      => ( 1 == $fusion_settings->get( 'sharing_social_links_boxed' ) ) ? 'yes' : 'no', // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
					'icons_boxed_radius'               => fusion_library()->sanitize->size( $fusion_settings->get( 'sharing_social_links_boxed_radius' ) ),
					'link'                             => $link_and_title['link'],
					'pinterest_image'                  => '',
					'pinterest_image_id'               => '',
					'tagline_size'                     => '4',
					'tagline'                          => '',
					'fusion_font_family_tagline_font'  => '',
					'fusion_font_variant_tagline_font' => '',
					'tagline_font_size'                => '',
					'tagline_letter_spacing'           => '',
					'tagline_line_height'              => '',
					'tagline_text_transform'           => '',
					'tagline_color'                    => strtolower( $fusion_settings->get( 'sharing_box_tagline_text_color' ) ),
					'title'                            => $link_and_title['title'],
					'tooltip_placement'                => strtolower( $fusion_settings->get( 'sharing_social_links_tooltip_placement' ) ),
					'social_share_links'               => implode(
						',',
						$fusion_settings->get( 'social_sharing' ) && is_array( $fusion_settings->get( 'social_sharing' ) )
							? $fusion_settings->get( 'social_sharing' ) : [
								'facebook',
								'twitter',
								'reddit',
								'linkedin',
								'whatsapp',
								'telegram',
								'tumblr',
								'pinterest',
								'vk',
								'xing',
								'email',
							]
					),
					'margin_top'                       => '60px',
					'margin_bottom'                    => '',
					'margin_left'                      => '',
					'margin_right'                     => '',
					'tagline_visibility'               => 'show',
					'animation_type'                   => '',
					'animation_direction'              => 'down',
					'animation_speed'                  => '0.1',
					'animation_offset'                 => $fusion_settings->get( 'animation_offset' ),
					'alignment'                        => 'flex-end',
					'alignment_medium'                 => '',
					'alignment_small'                  => 'space-between',
					'stacked_align'                    => 'flex-start',
					'stacked_align_medium'             => '',
					'stacked_align_small'              => '',
					'padding_bottom'                   => '',
					'padding_left'                     => '',
					'padding_right'                    => '',
					'padding_top'                      => '',
					'wrapper_padding_bottom'           => '',
					'wrapper_padding_left'             => '',
					'wrapper_padding_right'            => '',
					'wrapper_padding_top'              => '',
					'border_bottom'                    => '',
					'border_left'                      => '',
					'border_right'                     => '',
					'border_top'                       => '',
					'border_radius_top_left'           => '',
					'border_radius_top_right'          => '',
					'border_radius_bottom_right'       => '',
					'border_radius_bottom_left'        => '',
					'border_color'                     => $fusion_settings->get( 'sep_color' ),
					'tagline_placement'                => 'after',
					'separator_border_color'           => $fusion_settings->get( 'sep_color' ),
					'separator_border_sizes'           => '',
					'layout'                           => 'floated',
					'layout_medium'                    => '',
					'layout_small'                     => '',
				];
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @return array
			 * @since 2.0.0
			 */
			public static function settings_to_params() {
				return [
					'sep_color'                         => 'separator_border_color',
					'social_bg_color'                   => 'backgroundcolor',
					'social_sharing'                    => 'social_share_links',
					'sharing_social_links_color_type'   => 'color_type',
					'sharing_social_links_font_size'    => 'icon_size',
					'sharing_social_links_boxed'        => [
						'param'    => 'icons_boxed',
						'callback' => 'toYes',
					],
					'sharing_social_links_boxed_radius' => 'icons_boxed_radius',
					'sharing_box_tagline_text_color'    => 'tagline_color',
					'sharing_social_links_tooltip_placement' => 'tooltip_placement',
					'sharing_social_links_box_color'    => 'box_colors',
					'sharing_social_links_icon_color'   => 'icon_colors',

					// These are used to update social networks array.
					'sharing_email'                     => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_vk'                        => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_pinterest'                 => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_tumblr'                    => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_whatsapp'                  => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_telegram'                  => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_reddit'                    => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_linkedin'                  => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_twitter'                   => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
					'sharing_facebook'                  => [
						'param'    => 'social_networks',
						'callback' => 'createSocialNetworks',
					],
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @return array
			 * @since 2.0.0
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();

				return [
					'linktarget' => $fusion_settings->get( 'social_icons_new' ),
				];
			}

			/**
			 * Maps settings to extra variables.
			 *
			 * @static
			 * @access public
			 * @return array
			 * @since 2.0.0
			 */
			public static function settings_to_extras() {

				return [
					'social_icons_new' => 'linktarget',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 *
			 * @param array  $args Shortcode parameters.
			 * @param string $content Content between shortcode.
			 *
			 * @return string          HTML output.
			 * @since 1.0
			 */
			public function render( $args, $content = '' ) {
				$defaults                       = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_sharing' );
				$defaults['icons_boxed_radius'] = FusionBuilder::validate_shortcode_attr_value( $defaults['icons_boxed_radius'], 'px' );
				$defaults['description']        = fusion_decode_if_needed( $defaults['description'] );

				$this->args     = $defaults;
				$this->defaults = self::get_element_defaults();

				$use_brand_colors = false;
				if ( 'brand' === $this->args['color_type'] ) {
					$use_brand_colors = true;
					// Get a list of all the available social networks.
					$social_icon_boxed_colors         = Fusion_Data::fusion_social_icons( false, true );
					$social_icon_boxed_colors['mail'] = [
						'label' => esc_attr__( 'Email Address', 'fusion-builder' ),
						'color' => '#000000',
					];
				}

				$icons           = '';
				$icon_colors     = explode( '|', $this->args['icon_colors'] );
				$icon_taglines   = explode( '|', $this->args['icon_taglines'] );
				$box_colors      = explode( '|', $this->args['box_colors'] );
				$social_networks = explode( ',', $this->args['social_share_links'] );

				$num_of_icon_colors    = count( $icon_colors );
				$num_of_box_colors     = count( $box_colors );
				$social_networks_count = count( $social_networks );
				$num_of_icon_taglines  = count( $icon_taglines );

				for ( $i = 0; $i < $social_networks_count; $i ++ ) {
					if ( 1 === $num_of_icon_colors ) {
						$icon_colors[ $i ] = $icon_colors[0];
					}

					if ( 1 === $num_of_box_colors ) {
						$box_colors[ $i ] = $box_colors[0];
					}

					$network = $social_networks[ $i ];

					if ( $use_brand_colors ) {
						$icon_options = [
							'social_network' => $network,
							'icon_color'     => ( 'yes' === $this->args['icons_boxed'] ) ? '#ffffff' : $social_icon_boxed_colors[ $network ]['color'],
							'box_color'      => ( 'yes' === $this->args['icons_boxed'] ) ? $social_icon_boxed_colors[ $network ]['color'] : '',
						];

					} else {
						$icon_options = [
							'social_network' => $network,
							'icon_color'     => $i < count( $icon_colors ) ? $icon_colors[ $i ] : '',
							'box_color'      => $i < count( $box_colors ) ? $box_colors[ $i ] : '',
						];
					}

					if ( 1 === $num_of_icon_taglines ) {
						$icon_taglines[ $i ] = $icon_taglines[0];
					}
					$icon_options['tagline'] = $i < count( $icon_taglines ) ? $icon_taglines[ $i ] : '';

					$icons .= $this->generate_social_icon( $icon_options );

					if ( $this->args['separator_border_sizes'] > 0 && $i < $social_networks_count - 1 ) {
						$icons .= '<span class="sharingbox-shortcode-icon-separator"></span>';
					}
				}

				$tagline = '';
				if ( 'show' === $this->args['tagline_visibility'] && ! empty( $this->args['tagline'] ) ) {
					$tagline_tag = $this->get_tagline_tag();
					$tagline     = sprintf( '<' . $tagline_tag . ' %s>%s</' . $tagline_tag . '>', FusionBuilder::attributes( 'sharingbox-shortcode-tagline' ), $this->args['tagline'] );
				}

				$html = sprintf(
					'<div %s>%s<div %s>%s</div></div>',
					FusionBuilder::attributes( 'sharingbox-shortcode' ),
					$tagline,
					FusionBuilder::attributes( 'sharingbox-shortcode-social-networks' ),
					$icons
				);

				$html .= $this->get_styles();

				$this->counter ++;
				$this->on_render();

				return apply_filters( 'fusion_element_sharingbox_content', $html, $args );

			}


			/**
			 * Get the styles.
			 *
			 * @access protected
			 * @return string
			 * @since 3.2
			 */
			private function get_styles() {
				$fusion_settings = awb_get_fusion_settings();

				$this->base_selector    = '.sharingbox-shortcode-icon-wrapper-' . $this->counter . '';
				$this->wrapper_selector = '.fusion-sharing-box-' . $this->counter;
				$this->selectors        = [ $this->base_selector, $this->wrapper_selector ];
				$this->dynamic_css      = [];

				if ( 'hide' === $this->args['tagline_visibility'] ) {
					$this->args['layout']        = 'floated';
					$this->args['layout_medium'] = 'floated';
					$this->args['layout_small']  = 'floated';
				}

				if ( empty( $this->args['layout_medium'] ) ) {
					$this->args['layout_medium'] = $this->args['layout'];
				}
				if ( empty( $this->args['layout_small'] ) ) {
					$this->args['layout_small'] = $this->args['layout'];
				}

				if ( ! empty( $this->args['icon_taglines'] ) ) {
					if ( 'before' === $this->args['tagline_placement'] ) {
						$this->add_css_property( $this->wrapper_selector . ' .fusion-social-network-icon-tagline', 'margin-right', '0.5em', true );
					} else {
						$this->add_css_property( $this->wrapper_selector . ' .fusion-social-network-icon-tagline', 'margin-left', '0.5em', true );
					}
					$this->add_css_property( $this->base_selector . ' span a', 'align-items', 'center', true );
					$this->add_css_property( $this->base_selector . ' span a', 'display', 'flex', true );
				}

				if ( empty( $this->args['stacked_align_medium'] ) ) {
					$this->args['stacked_align_medium'] = $this->args['stacked_align'];
				}

				if ( empty( $this->args['stacked_align_small'] ) ) {
					$this->args['stacked_align_small'] = $this->args['stacked_align'];
				}

				if ( empty( $this->args['alignment_medium'] ) ) {
					$this->args['alignment_medium'] = $this->args['alignment'];
				}

				if ( empty( $this->args['alignment_small'] ) ) {
					$this->args['alignment_small'] = $this->args['alignment'];
				}

				if ( ! $this->is_default( 'alignment' ) ) {
					$this->add_css_property( [ $this->base_selector ], 'justify-content', $this->args['alignment'], true );
				}

				$selector = [ $this->wrapper_selector ];
				if ( 'floated' === $this->args['layout'] ) {
					$this->add_css_property( [ $this->wrapper_selector . ' .tagline' ], 'margin-bottom', '0', true );
				} else {
					$this->add_css_property( $selector, 'align-items', $this->args['stacked_align'], true );
					$this->add_css_property( $selector, 'justify-content', 'space-around', true );
					$this->add_css_property( [ $this->base_selector ], 'width', '100%', true );
				}

				$large_layout = 'stacked' === $this->args['layout'] ? ' column' : 'row';
				$this->add_css_property( $selector, 'flex-direction', $large_layout, true );

				if ( ! $this->is_default( 'border_color' ) ) {
					$this->add_css_property( $selector, 'border-color', $this->args['border_color'], true );
				}

				if ( ! $this->is_default( 'wrapper_padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', $this->args['wrapper_padding_top'], true );
				}

				if ( ! $this->is_default( 'wrapper_padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', $this->args['wrapper_padding_bottom'], true );
				}

				if ( ! $this->is_default( 'wrapper_padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', $this->args['wrapper_padding_left'], true );
				}

				if ( ! $this->is_default( 'wrapper_padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', $this->args['wrapper_padding_right'], true );
				}

				if ( ! $this->is_default( 'border_top' ) ) {
					$this->add_css_property( $selector, 'border-top-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_top'] ), true );
				}

				if ( ! $this->is_default( 'border_right' ) ) {
					$this->add_css_property( $selector, 'border-right-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_right'] ), true );
				}

				if ( ! $this->is_default( 'border_bottom' ) ) {
					$this->add_css_property( $selector, 'border-bottom-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_bottom'] ), true );
				}

				if ( ! $this->is_default( 'border_left' ) ) {
					$this->add_css_property( $selector, 'border-left-width', fusion_library()->sanitize->get_value_with_unit( $this->args['border_left'] ), true );
				}

				if ( ! $this->is_default( 'border_radius_top_left' ) ) {
					$this->add_css_property( $selector, 'border-top-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_left'] ), true );
				}

				if ( ! $this->is_default( 'border_radius_top_right' ) ) {
					$this->add_css_property( $selector, 'border-top-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_top_right'] ), true );
				}

				if ( ! $this->is_default( 'border_radius_bottom_right' ) ) {
					$this->add_css_property( $selector, 'border-bottom-right-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_right'] ), true );
				}

				if ( ! $this->is_default( 'border_radius_bottom_left' ) ) {
					$this->add_css_property( $selector, 'border-bottom-left-radius', fusion_library()->sanitize->get_value_with_unit( $this->args['border_radius_bottom_left'] ), true );
				}

				$selector = [ $this->base_selector . ' span:not(.sharingbox-shortcode-icon-separator)' ];
				if ( ! $this->is_default( 'padding_top' ) ) {
					$this->add_css_property( $selector, 'padding-top', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_top'] ), true );
				}

				if ( ! $this->is_default( 'padding_bottom' ) ) {
					$this->add_css_property( $selector, 'padding-bottom', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_bottom'] ), true );
				}

				if ( ! $this->is_default( 'padding_left' ) ) {
					$this->add_css_property( $selector, 'padding-left', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_left'] ), true );
				}

				if ( ! $this->is_default( 'padding_right' ) ) {
					$this->add_css_property( $selector, 'padding-right', fusion_library()->sanitize->get_value_with_unit( $this->args['padding_right'] ), true );
				}

				if ( ! $this->is_default( 'icon_tagline_color' ) ) {
					$this->add_css_property( $this->base_selector . ' a', 'color', $this->args['icon_tagline_color'] );
				}

				if ( ! $this->is_default( 'icon_tagline_color_hover' ) ) {
					$this->add_css_property( $this->base_selector . ' a:hover', 'color', $this->args['icon_tagline_color_hover'] );
				}

				if ( ! $this->is_default( 'tagline_text_size' ) ) {
					$this->add_css_property( $this->base_selector . ' a', 'font-size', $this->args['tagline_text_size'], true );
				}

				if ( ! $this->is_default( 'icon_size' ) ) {
					$this->add_css_property( $this->base_selector . ' a i', 'font-size', $this->args['icon_size'], true );
				}

				$selector = [ $this->base_selector . ' span.sharingbox-shortcode-icon-separator' ];
				if ( ! $this->is_default( 'separator_border_color' ) ) {
					$this->add_css_property( $selector, 'border-color', $this->args['separator_border_color'], true );
				}

				if ( ! $this->is_default( 'separator_border_sizes' ) ) {
					$this->args['separator_border_sizes'] = FusionBuilder::validate_shortcode_attr_value( $this->args['separator_border_sizes'], 'px' );
					$this->add_css_property( $selector, 'border-right-width', $this->args['separator_border_sizes'], true );
				}

				$css = $this->parse_css();

				$this->dynamic_css = [];
				$layout_medium     = 'stacked' === $this->args['layout_medium'] ? ' column' : 'row';
				$selector          = [ $this->wrapper_selector ];

				if ( 'floated' !== $this->args['layout_medium'] ) {
					$this->add_css_property( [ $this->wrapper_selector . ' .tagline' ], 'margin-bottom', 'revert', true );
					$this->add_css_property( [ $this->base_selector ], 'width', '100%', true );
				} else {
					$this->add_css_property( $this->wrapper_selector . ' .tagline', 'margin-right', '0.5em', true );
					$this->add_css_property( [ $this->base_selector ], 'width', 'auto', true );
					$this->add_css_property( $selector, 'align-items', 'center', true );
					$this->add_css_property( [ $this->wrapper_selector . ' .tagline' ], 'margin-bottom', '0', true );
				}
				if ( ! empty( $this->args['alignment_medium'] ) ) {
					$this->add_css_property( [ $this->base_selector ], 'justify-content', $this->args['alignment_medium'], true );
					if ( 'floated' !== $this->args['layout_medium'] ) {
						$this->add_css_property( $selector, 'align-items', $this->args['stacked_align_medium'], true );
					}
				}
				$css .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_medium' ) . 'px){' .
						$this->parse_css() . ' }';

				$layout_small      = 'stacked' === $this->args['layout_small'] ? ' column' : 'row';
				$this->dynamic_css = [];
				$this->add_css_property( $selector, 'flex-direction', $layout_small, true );
				if ( 'floated' !== $this->args['layout_small'] ) {
					$this->add_css_property( [ $this->wrapper_selector . ' .tagline' ], 'margin-bottom', 'revert', true );
					$this->add_css_property( [ $this->base_selector ], 'width', '100%', true );
				} else {
					$this->add_css_property( $this->wrapper_selector . ' .tagline', 'margin-right', '0.5em', true );
					$this->add_css_property( [ $this->wrapper_selector . ' .tagline' ], 'margin-bottom', '0', true );
					$this->add_css_property( $selector, 'align-items', 'center', true );
					$this->add_css_property( [ $this->base_selector ], 'width', 'auto', true );
				}
				if ( ! empty( $this->args['alignment_small'] ) ) {
					$this->add_css_property( $this->base_selector, 'justify-content', $this->args['alignment_small'], true );
					if ( 'floated' !== $this->args['layout_small'] ) {
						$this->add_css_property( $selector, 'align-items', $this->args['stacked_align_small'], true );
					}
				}
				$css .= '@media only screen and (max-width:' . $fusion_settings->get( 'visibility_small' ) . 'px){' .
						$this->parse_css() . ' }';

				return $css ? '<style type="text/css">' . $css . '</style>' : '';
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @return array
			 * @since 1.0
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-sharing-box fusion-sharing-box-' . $this->counter,
						'style' => '',
					]
				);

				if ( $this->args['animation_type'] ) {
					$attr = Fusion_Builder_Animation_Helper::add_animation_attributes( $this->args, $attr );
				}

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( 'yes' === $this->args['icons_boxed'] ) {
					$attr['class'] .= ' boxed-icons';
				}

				if ( $this->args['backgroundcolor'] ) {
					$attr['style'] = 'background-color:' . $this->args['backgroundcolor'] . ';';

					if ( Fusion_Color::new_color( $this->args['backgroundcolor'] )->is_color_transparent() ) {
						$attr['style'] .= 'padding:0;';
					}
				}

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['data-title']       = $this->args['title'];
				$attr['data-description'] = $this->args['description'];
				$attr['data-link']        = $this->args['link'];
				$attr['data-image']       = $this->args['pinterest_image'];

				return $attr;

			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @return array
			 * @since 1.0
			 */
			public function tagline_attr() {

				$attr = [
					'class' => 'tagline',
				];

				if ( $this->args['tagline_color'] ) {
					$attr['style'] = 'color:' . $this->args['tagline_color'] . ';';
				}

				$attr['style'] .= Fusion_Builder_Element_Helper::get_font_styling( $this->args, 'tagline_font' );

				if ( $this->args['tagline_font_size'] ) {
					$attr['style'] .= 'font-size:' . $this->args['tagline_font_size'] . ';';
				}

				if ( $this->args['tagline_line_height'] ) {
					$attr['style'] .= 'line-height:' . $this->args['tagline_line_height'] . ';';
				}

				if ( $this->args['tagline_letter_spacing'] ) {
					$attr['style'] .= 'letter-spacing:' . fusion_library()->sanitize->get_value_with_unit( $this->args['tagline_letter_spacing'] ) . ';';
				}

				if ( ! empty( $this->args['tagline_text_transform'] ) ) {
					$attr['style'] .= 'text-transform:' . $this->args['tagline_text_transform'] . ';';
				}

				return $attr;

			}

			/**
			 * Builds the social networks attributes array.
			 *
			 * @access public
			 * @return array
			 * @since 1.0
			 */
			public function social_networks_attr() {

				$attr = [
					'class' => 'fusion-social-networks sharingbox-shortcode-icon-wrapper sharingbox-shortcode-icon-wrapper-' . $this->counter,
				];

				if ( 'yes' === $this->args['icons_boxed'] ) {
					$attr['class'] .= ' boxed-icons';
				}

				return $attr;

			}

			/**
			 * Build the icon html
			 *
			 * @access public
			 * @param array $icon_options Icon options.
			 * @return string
			 * @since 3.1.1
			 */
			public function generate_social_icon( $icon_options ) {
				$icon = sprintf(
					'<span><a %s>%s<i %s aria-hidden="true"></i>%s</a></span>',
					FusionBuilder::attributes( 'sharingbox-shortcode-icon-link', $icon_options ),
					'before' === $this->args['tagline_placement'] ? $this->add_icon_tagline( $icon_options ) : '',
					FusionBuilder::attributes( 'sharingbox-shortcode-icon', $icon_options ),
					'after' === $this->args['tagline_placement'] ? $this->add_icon_tagline( $icon_options ) : ''
				);

				return $icon;
			}

			/**
			 * Build the icon tagline
			 *
			 * @access public
			 * @param array $icon_options Icon options.
			 * @return string
			 * @since 3.1.1
			 */
			public function add_icon_tagline( $icon_options ) {

				if ( ! empty( $icon_options['tagline'] ) ) {
					return sprintf( '<div class="fusion-social-network-icon-tagline">%s</div>', $icon_options['tagline'] );
				}
			}

			/**
			 * Get the tag of the tagline title.
			 *
			 * @return string
			 */
			public function get_tagline_tag() {
				$tag_option = $this->args['tagline_size'];
				if ( ! $tag_option ) {
					return 'h4';
				}

				if ( is_numeric( $tag_option ) ) {
					return 'h' . $tag_option;
				}

				return $tag_option;
			}

			/**
			 * Builds the icon link attributes array.
			 *
			 * @access public
			 *
			 * @param array $args The arguments array.
			 *
			 * @return array
			 * @since 3.1.1
			 */
			public function icon_link_attr( $args ) {
				$fusion_settings = awb_get_fusion_settings();

				$attr                   = [];
				$args['social_network'] = 'email' === $args['social_network'] ? 'mail' : $args['social_network'];
				$description            = $this->args['description'];
				$link                   = $this->args['link'];
				$title                  = $this->args['title'];
				$image                  = rawurlencode( $this->args['pinterest_image'] );
				$social_link            = $this->get_social_link_href( $args['social_network'], $link, $title, $description, $image );

				$attr['href']   = $social_link;
				$attr['target'] = ( $fusion_settings->get( 'social_icons_new' ) && 'mail' !== $args['social_network'] ) ? '_blank' : '_self';

				if ( '_blank' === $attr['target'] ) {
					$attr['rel'] = ( 'facebook' !== $args['social_network'] ? 'noopener ' : '' ) . 'noreferrer';
				}

				if ( $fusion_settings->get( 'nofollow_social_links' ) ) {
					$attr['rel'] = ( isset( $attr['rel'] ) ) ? $attr['rel'] . ' nofollow' : 'nofollow';
				}

				$tooltip            = Fusion_Social_Icon::get_social_network_name( $args['social_network'] );
				$attr['title']      = $tooltip;
				$attr['aria-label'] = $tooltip;

				if ( 'none' !== $this->args['tooltip_placement'] ) {
					$attr['data-placement'] = $this->args['tooltip_placement'];
					$attr['data-toggle']    = 'tooltip';
					$attr['data-title']     = $tooltip;
				}

				return $attr;
			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 *
			 * @param array $args The arguments array.
			 *
			 * @return array
			 * @since 1.0
			 */
			public function icon_attr( $args ) {
				if ( ! empty( $this->args['pinterest_image'] ) ) {

					$image_data = fusion_library()->images->get_attachment_data_by_helper( $this->args['pinterest_image_id'], $this->args['pinterest_image'] );

					if ( $image_data['url'] ) {
						$this->args['pinterest_image'] = $image_data['url'];
					}
				}
				$args['social_network'] = 'email' === $args['social_network'] ? 'mail' : $args['social_network'];

				$attr = [
					'class' => 'fusion-social-network-icon fusion-tooltip fusion-' . $args['social_network'] . ' awb-icon-' . $args['social_network'],
				];

				$attr['style'] = ( $args['icon_color'] ) ? 'color:' . $args['icon_color'] . ';' : '';

				if ( isset( $this->args['icons_boxed'] ) && 'yes' === $this->args['icons_boxed'] && $args['box_color'] ) {
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
			 * Generate social icon share link
			 *
			 * @param string $social_network Social network name.
			 * @param string $link           The link.
			 * @param string $title          The title.
			 * @param string $description    The description.
			 * @param string $image          The image.
			 *
			 * @return string
			 */
			public function get_social_link_href( $social_network, $link, $title, $description, $image ) {
				$social_link = '';
				switch ( $social_network ) {
					case 'facebook':
						$social_link = 'https://m.facebook.com/sharer.php?u=' . $link;
						// TODO: Use Jetpack's implementation instead.
						if ( ! wp_is_mobile() ) {
							$social_link = 'https://www.facebook.com/sharer.php?u=' . rawurlencode( $link ) . '&t=' . rawurlencode( $title );
						}
						break;
					case 'twitter':
						$social_link = 'https://twitter.com/share?text=' . rawurlencode( html_entity_decode( $title, ENT_COMPAT, 'UTF-8' ) ) . '&url=' . rawurlencode( $link );
						break;
					case 'linkedin':
						$social_link = 'https://www.linkedin.com/shareArticle?mini=true&url=' . rawurlencode( $link ) . '&title=' . rawurlencode( $title ) . '&summary=' . rawurlencode( $description );
						break;
					case 'reddit':
						$social_link = 'https://reddit.com/submit?url=' . rawurlencode( $link ) . '&amp;title=' . rawurlencode( $title );
						break;
					case 'whatsapp':
						$social_link = 'https://api.whatsapp.com/send?text=' . rawurlencode( $link );
						break;
					case 'telegram':
						$social_link = 'https://t.me/share/url?url=' . rawurlencode( $link ) . '&text=' . rawurlencode( $title ) . '';
						break;
					case 'tumblr':
						$social_link = 'https://www.tumblr.com/share/link?url=' . rawurlencode( $link ) . '&amp;name=' . rawurlencode( $title ) . '&amp;description=' . rawurlencode( $description );
						break;
					case 'pinterest':
						$social_link = 'https://pinterest.com/pin/create/button/?url=' . rawurlencode( $link ) . '&amp;description=' . rawurlencode( $description ) . '&amp;media=' . $image;
						break;
					case 'vk':
						$social_link = 'https://vkontakte.ru/share.php?url=' . rawurlencode( $link ) . '&amp;title=' . rawurlencode( $title ) . '&amp;description=' . rawurlencode( $description );
						break;
					case 'xing':
						$social_link = 'https://www.xing.com/social_plugins/share/new?sc_p=xing-share&amp;h=1&amp;url=' . rawurlencode( $link );
						break;
					case 'mail':
						$social_link = 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $link );
						break;
				}

				return $social_link;
			}


			/**
			 * Gets link and title.
			 *
			 * @static
			 * @access public
			 * @return array
			 * @since 2.3
			 */
			public static function get_link_and_title() {
				global $wp;

				if ( is_archive() ) {
					if ( is_author() ) {

						$link  = get_author_posts_url( get_the_author_meta( 'ID' ) );
						$title = wp_strip_all_tags( get_the_archive_title() );
					} elseif ( is_year() ) {
						$link  = home_url( add_query_arg( [], $wp->request ) );
						$title = esc_html( 'Yearly Archives: ' . get_query_var( 'year' ) );
					} elseif ( is_month() ) {
						$link  = home_url( add_query_arg( [], $wp->request ) );
						$title = esc_html( 'Monthly Archives: ' . get_query_var( 'year' ) . '/' . get_query_var( 'monthnum' ) );
					} elseif ( is_day() ) {
						$link  = home_url( add_query_arg( [], $wp->request ) );
						$title = esc_html( 'Daily Archives: ' . get_query_var( 'year' ) . '/' . get_query_var( 'monthnum' ) . '/' . get_query_var( 'day' ) );
					} else {
						$queried_object = get_queried_object();
						$link           = '';

						if ( isset( $queried_object->term_id ) && isset( $queried_object->taxonomy ) ) {
							$link = get_term_link( $queried_object->term_id, $queried_object->taxonomy );
						}

						$title = wp_strip_all_tags( get_the_archive_title() );
					}
				} else {
					$link  = get_permalink();
					$title = get_the_title();
				}

				return [
					'link'  => $link,
					'title' => $title,
				];
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @return array $sections Sharing Box settings.
			 * @since 1.1
			 */
			public function add_options() {

				return [
					'sharing_box_shortcode_section' => [
						'label'       => esc_html__( 'Social Sharing', 'fusion-builder' ),
						'id'          => 'sharing_box_shortcode_section',
						'description' => '',
						'type'        => 'accordion',
						'icon'        => 'fusiona-share2',
						'fields'      => [
							'social_sharing'             => [
								'label'                  => esc_html__( 'Social Networks', 'fusion-builder' ),
								'description'            => esc_html__( 'Select social network you want to be displayed in the social share box.', 'fusion-builder' ),
								'id'                     => 'social_sharing',
								'default'                => [ 'sharing_facebook', 'sharing_twitter', 'sharing_reddit' ],
								'type'                   => 'select',
								'multi'                  => true,
								'choices'                => [
									'facebook'  => esc_html__( 'Facebook', 'fusion-builder' ),
									'twitter'   => esc_html__( 'Twitter', 'fusion-builder' ),
									'reddit'    => esc_html__( 'Reddit', 'fusion-builder' ),
									'linkedin'  => esc_html__( 'LinkedIn', 'fusion-builder' ),
									'whatsapp'  => esc_html__( 'WhatsApp', 'fusion-builder' ),
									'telegram'  => esc_html__( 'Telegram', 'fusion-builder' ),
									'tumblr'    => esc_html__( 'Tumblr', 'fusion-builder' ),
									'pinterest' => esc_html__( 'Pinterest', 'fusion-builder' ),
									'vk'        => esc_html__( 'VK', 'fusion-builder' ),
									'xing'      => esc_html__( 'Xing', 'fusion-builder' ),
									'email'     => esc_html__( 'Email', 'fusion-builder' ),
								],
								'social_share_box_links' => [
									'selector'            => '.fusion-sharing-box.fusion-single-sharing-box',
									'container_inclusive' => true,
									'render_callback'     => [ 'Avada_Partial_Refresh_Callbacks', 'sharingbox' ],
									'success_trigger_event' => 'fusionInitTooltips',
								],
							],
							'sharing_social_tagline'     => [
								'label'       => esc_html__( 'Sharing Box Tagline', 'fusion-builder' ),
								'description' => esc_html__( 'Insert a tagline for the social sharing boxes.', 'fusion-builder' ),
								'id'          => 'sharing_social_tagline',
								'default'     => esc_html__( 'Share This Story, Choose Your Platform!', 'fusion-builder' ),
								'type'        => 'text',
							],
							'sharing_box_tagline_text_color' => [
								'label'       => esc_html__( 'Sharing Box Tagline Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the tagline text in the social sharing boxes.', 'fusion-builder' ),
								'id'          => 'sharing_box_tagline_text_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
							],
							'social_bg_color'            => [
								'label'       => esc_html__( 'Sharing Box Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the social sharing boxes.', 'fusion-builder' ),
								'id'          => 'social_bg_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
							],
							'social_share_box_icon_info' => [
								'label'       => esc_html__( 'Social Sharing Box Icons', 'fusion-builder' ),
								'description' => '',
								'id'          => 'social_share_box_icon_info',
								'icon'        => true,
								'type'        => 'info',
							],
							'sharing_social_links_font_size' => [
								'label'       => esc_html__( 'Sharing Box Icon Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the font size of the social icons in the social sharing boxes.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_font_size',
								'default'     => '16px',
								'type'        => 'dimension',
								'css_vars'    => [
									[
										'name'    => '--sharing_social_links_font_size',
										'element' => '.fusion-sharing-box',
									],
								],
							],
							'sharing_social_links_tooltip_placement' => [
								'label'       => esc_html__( 'Sharing Box Icons Tooltip Position', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the tooltip position of the social icons in the social sharing boxes.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_tooltip_placement',
								'default'     => 'Top',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'top'    => esc_html__( 'Top', 'fusion-builder' ),
									'right'  => esc_html__( 'Right', 'fusion-builder' ),
									'bottom' => esc_html__( 'Bottom', 'fusion-builder' ),
									'left'   => esc_html__( 'Left', 'fusion-builder' ),
									'none'   => esc_html__( 'None', 'fusion-builder' ),
								],
							],
							'sharing_social_links_color_type' => [
								'label'       => esc_html__( 'Sharing Box Icon Color Type', 'fusion-builder' ),
								'description' => esc_html__( 'Custom colors allow you to choose a color for icons and boxes. Brand colors will use the exact brand color of each network for the icons or boxes.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_color_type',
								'default'     => 'custom',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'custom' => esc_html__( 'Custom Colors', 'fusion-builder' ),
									'brand'  => esc_html__( 'Brand Colors', 'fusion-builder' ),
								],
							],
							'sharing_social_links_icon_color' => [
								'label'       => esc_html__( 'Sharing Box Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the social icons in the social sharing boxes. This color will be used for all social icons.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_icon_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'required'    => [
									[
										'setting'  => 'sharing_social_links_color_type',
										'operator' => '==',
										'value'    => 'custom',
									],
								],
							],
							'sharing_social_links_boxed' => [
								'label'       => esc_html__( 'Sharing Box Icons Boxed', 'fusion-builder' ),
								'description' => esc_html__( 'Controls if each social icon is displayed in a small box.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_boxed',
								'default'     => '0',
								'type'        => 'switch',
							],
							'sharing_social_links_box_color' => [
								'label'       => esc_html__( 'Sharing Box Icon Box Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the social icon box.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_box_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'required'    => [
									[
										'setting'  => 'sharing_social_links_boxed',
										'operator' => '==',
										'value'    => '1',
									],
									[
										'setting'  => 'sharing_social_links_color_type',
										'operator' => '==',
										'value'    => 'custom',
									],
								],
							],
							'sharing_social_links_boxed_radius' => [
								'label'       => esc_html__( 'Sharing Box Icon Boxed Radius', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the box radius of the social icon box.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_boxed_radius',
								'default'     => '4px',
								'type'        => 'dimension',
								'required'    => [
									[
										'setting'  => 'sharing_social_links_boxed',
										'operator' => '==',
										'value'    => '1',
									],
								],
							],
							'sharing_social_links_boxed_padding' => [
								'label'       => esc_html__( 'Sharing Box Icons Boxed Padding', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the interior padding of the social icon box.', 'fusion-builder' ),
								'id'          => 'sharing_social_links_boxed_padding',
								'default'     => '8px',
								'type'        => 'dimension',
								'required'    => [
									[
										'setting'  => 'sharing_social_links_boxed',
										'operator' => '==',
										'value'    => '1',
									],
								],
								'css_vars'    => [
									[
										'name' => '--sharing_social_links_boxed_padding',
									],
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
			 * @return void
			 * @since 1.1
			 */
			public function add_scripts() {
				Fusion_Dynamic_JS::enqueue_script( 'fusion-tooltip' );
				Fusion_Dynamic_JS::enqueue_script( 'fusion-sharing-box' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @return void
			 * @since 3.0
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/sharingbox.min.css' );
			}
		}
	}

	new FusionSC_SharingBox();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_sharing_box() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_SharingBox',
			[
				'name'          => esc_attr__( 'Social Sharing', 'fusion-builder' ),
				'shortcode'     => 'fusion_sharing',
				'icon'          => 'fusiona-share2',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-sharingbox-preview.php',
				'preview_id'    => 'fusion-builder-block-module-sharingbox-preview-template',
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/sharing-box-element/',
				'inline_editor' => true,
				'params'        => [
					[
						'type'        => 'multiple_select',
						'param_name'  => 'social_share_links',
						'choices'     => [
							'facebook'  => esc_html__( 'Facebook', 'fusion-builder' ),
							'twitter'   => esc_html__( 'Twitter', 'fusion-builder' ),
							'reddit'    => esc_html__( 'Reddit', 'fusion-builder' ),
							'linkedin'  => esc_html__( 'LinkedIn', 'fusion-builder' ),
							'whatsapp'  => esc_html__( 'WhatsApp', 'fusion-builder' ),
							'telegram'  => esc_html__( 'Telegram', 'fusion-builder' ),
							'tumblr'    => esc_html__( 'Tumblr', 'fusion-builder' ),
							'pinterest' => esc_html__( 'Pinterest', 'fusion-builder' ),
							'vk'        => esc_html__( 'VK', 'fusion-builder' ),
							'xing'      => esc_html__( 'Xing', 'fusion-builder' ),
							'email'     => esc_html__( 'Email', 'fusion-builder' ),
						],
						'default'     => '',
						'heading'     => esc_html__( 'Social Sharing', 'fusion-builder' ),
						'description' => esc_html__( 'Select social network you want to be displayed in the social share box.', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_html__( 'Layout', 'fusion-builder' ),
						'description' => esc_html__( 'Choose if social sharing box items should be stacked and full width, or if they should be floated.', 'fusion-builder' ),
						'param_name'  => 'layout',
						'default'     => 'floated',
						'value'       => [
							'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_html__( 'Floated', 'fusion-builder' ),
						],
						'responsive'  => [
							'state'         => 'large',
							'defaults'      => [
								'small' => 'stacked',
							],
							'default_value' => true,
						],
						'dependency'  => [
							[
								'element'  => 'tagline_visibility',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Tagline', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show or hide tagline.', 'fusion-builder' ),
						'param_name'  => 'tagline_visibility',
						'default'     => 'show',
						'value'       => [
							'show' => esc_html__( 'Show', 'fusion-builder' ),
							'hide' => esc_html__( 'Hide', 'fusion-builder' ),
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Tagline Heading Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose HTML tag of the tagline heading, either div or the heading tag, h1-h6.', 'fusion-builder' ),
						'param_name'  => 'tagline_size',
						'value'       => [
							'1'   => 'H1',
							'2'   => 'H2',
							'3'   => 'H3',
							'4'   => 'H4',
							'5'   => 'H5',
							'6'   => 'H6',
							'div' => 'DIV',
						],
						'default'     => '4',
						'dependency'  => [
							[
								'element'  => 'tagline_visibility',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tagline', 'fusion-builder' ),
						'description' => esc_attr__( 'The title tagline that will display.', 'fusion-builder' ),
						'param_name'  => 'tagline',
						'value'       => esc_attr__( 'Share This Story, Choose Your Platform!', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'tagline_visibility',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Margin', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'margin',
						'value'            => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Padding', 'fusion-builder' ),
						'description'      => esc_attr__( 'In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'wrapper_adding',
						'value'            => [
							'wrapper_padding_top'    => '',
							'wrapper_padding_right'  => '',
							'wrapper_padding_bottom' => '',
							'wrapper_padding_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color.', 'fusion-builder' ),
						'param_name'  => 'backgroundcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'social_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Border Size', 'fusion-builder' ),
						'description'      => esc_attr__( 'Controls the border size of the social sharing box. In pixels or percentage, ex: 10px or 10%.', 'fusion-builder' ),
						'param_name'       => 'border_sizes',
						'value'            => [
							'border_top'    => '',
							'border_right'  => '',
							'border_bottom' => '',
							'border_left'   => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the social sharing box.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '#cccccc',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_html__( 'Border Radius', 'fusion-builder' ),
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
						'type'             => 'typography',
						'remove_from_atts' => true,
						'global'           => true,
						'heading'          => esc_attr__( 'Tagline Typography', 'fusion-builder' ),
						'description'      => esc_html__( 'Controls the tagline typography', 'fusion-builder' ),
						'param_name'       => 'tagline_typography',
						'choices'          => [
							'font-family'    => 'tagline_font',
							'font-size'      => 'tagline_font_size',
							'line-height'    => 'tagline_line_height',
							'letter-spacing' => 'tagline_letter_spacing',
							'text-transform' => 'tagline_text_transform',
						],
						'default'          => [
							'font-family'    => '',
							'variant'        => '',
							'font-size'      => '',
							'line-height'    => '',
							'letter-spacing' => '',
							'text-transform' => '',
						],
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Tagline Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the text color.', 'fusion-builder' ),
						'param_name'  => 'tagline_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sharing_box_tagline_text_color' ),
						'dependency'  => [
							[
								'element'  => 'tagline',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'tagline_visibility',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Tagline Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the Social Sharing Box alignment.', 'fusion-builder' ),
						'param_name'  => 'stacked_align',
						'default'     => 'flex-start',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'value'       => [
							'flex-start' => esc_attr__( 'Flex Start', 'fusion-builder' ),
							'center'     => esc_attr__( 'Center', 'fusion-builder' ),
							'flex-end'   => esc_attr__( 'Flex End', 'fusion-builder' ),
						],
						'icons'       => [
							'flex-start' => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'     => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'   => '<span class="fusiona-horizontal-flex-end"></span>',
						],
						'responsive'  => [
							'state'             => 'large',
							'additional_states' => [ 'medium', 'small' ],
							'defaults'          => [
								'small'  => 'center',
								'medium' => '',
							],
						],
						'grid_layout' => true,
						'back_icons'  => true,
						'dependency'  => [
							[
								'element'  => 'tagline_visibility',
								'value'    => 'hide',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Social Icon Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the Social Icon alignment.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'default'     => 'flex-end',
						'grid_layout' => true,
						'back_icons'  => true,
						'icons'       => [
							''              => '<span class="fusiona-cog"></span>',
							'flex-start'    => '<span class="fusiona-horizontal-flex-start"></span>',
							'center'        => '<span class="fusiona-horizontal-flex-center"></span>',
							'flex-end'      => '<span class="fusiona-horizontal-flex-end"></span>',
							'space-between' => '<span class="fusiona-horizontal-space-between"></span>',
							'space-around'  => '<span class="fusiona-horizontal-space-around"></span>',
							'space-evenly'  => '<span class="fusiona-horizontal-space-evenly"></span>',
						],
						'responsive'  => [
							'state'             => 'large',
							'additional_states' => [ 'medium', 'small' ],
							'defaults'          => [
								'small'  => 'space-between',
								'medium' => '',
							],
							'default_value'     => true,
						],
						'value'       => [
							'flex-start'    => esc_html__( 'Flex Start', 'fusion-builder' ),
							'center'        => esc_html__( 'Center', 'fusion-builder' ),
							'flex-end'      => esc_html__( 'Flex End', 'fusion-builder' ),
							'space-between' => esc_html__( 'Space Between', 'fusion-builder' ),
							'space-around'  => esc_html__( 'Space Around', 'fusion-builder' ),
							'space-evenly'  => esc_html__( 'Space Evenly', 'fusion-builder' ),
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Sharing Title', 'fusion-builder' ),
						'description'  => __( 'The post title that will be shared. Leave empty to use title of current post. <strong>NOTE:</strong> Some of the social networks will ignore this option and will instead auto pull the post title based on the shared link.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Sharing Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'The link that will be shared. Leave empty to use URL of current post.', 'fusion-builder' ),
						'param_name'   => 'link',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Sharing Description', 'fusion-builder' ),
						'description'  => __( 'The description that will be shared. Leave empty to use excerpt of current post. <strong>NOTE:</strong> Some of the social networks do not offer description in their sharing options and others might ignore it and will instead auto pull the post excerpt based on the shared link.', 'fusion-builder' ),
						'param_name'   => 'description',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Boxed Social Icons', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if each social icon is displayed in a small box.', 'fusion-builder' ),
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
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Social Icon Box Radius', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the radius of the boxed icons. In pixels (px), ex: 1px, or "round". ', 'fusion-builder' ),
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
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Social Icon Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the icons. Enter value including any valid CSS unit, ex: 16px.', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'value'       => '',
						'default'     => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Social Icons Color Type', 'fusion-builder' ),
						'description' => esc_attr__( 'Custom colors allow you to choose a color for icons and boxes. Brand colors will use the exact brand color of each network for the icons or boxes. Choose default for Global Option selection.', 'fusion-builder' ),
						'param_name'  => 'color_type',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'custom' => esc_attr__( 'Custom Colors', 'fusion-builder' ),
							'brand'  => esc_attr__( 'Brand Colors', 'fusion-builder' ),
						],
						'default'     => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Social Icon Custom Colors', 'fusion-builder' ),
						'description' => esc_attr__( 'Specify the color of social icons. Use | to set the color for the individual icons. ', 'fusion-builder' ),
						'param_name'  => 'icon_colors',
						'value'       => '',
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
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Social Icon Box Colors', 'fusion-builder' ),
						'description' => esc_attr__( 'Specify the box color of social icons. Use | to set the box color for the individual icons.', 'fusion-builder' ),
						'param_name'  => 'box_colors',
						'value'       => '',
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
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Social Icon Tooltip Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the display position for tooltips. Choose default for Global Options selection.', 'fusion-builder' ),
						'param_name'  => 'tooltip_placement',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'top'    => esc_attr__( 'Top', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'Right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'upload',
						'heading'     => esc_attr__( 'Pinterest Sharing Image', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose an image to share on pinterest.', 'fusion-builder' ),
						'param_name'  => 'pinterest_image',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Pinterest Image ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Pinterest Image ID from Media Library.', 'fusion-builder' ),
						'param_name'  => 'pinterest_image_id',
						'value'       => '',
						'hidden'      => true,
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-meta-tb',
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Social Icon Box Padding', 'fusion-builder' ),
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
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Social Icon Custom Taglines', 'fusion-builder' ),
						'description' => esc_attr__( 'Specify the tagline of social icons. Use | to set the taglines for the individual icons. ', 'fusion-builder' ),
						'param_name'  => 'icon_taglines',
						'value'       => '',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Icon Tagline Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the display position for icon tagline.', 'fusion-builder' ),
						'param_name'  => 'tagline_placement',
						'value'       => [
							'before' => esc_attr__( 'Before', 'fusion-builder' ),
							'after'  => esc_attr__( 'After', 'fusion-builder' ),
						],
						'default'     => 'after',
						'dependency'  => [
							[
								'element'  => 'icon_taglines',
								'value'    => '',
								'operator' => '!=',
							],
						],
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Icon Tagline Font Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the size of the icon tagline text. Enter value including any valid CSS unit, ex: 16px.', 'fusion-builder' ),
						'param_name'  => 'tagline_text_size',
						'value'       => '',
						'group'       => esc_html__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Social Icon Tagline Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link color of the social sharing tagline.', 'fusion-builder' ),
						'param_name'  => 'icon_tagline_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_taglines',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Social Icon Tagline Hover Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the link hover color of the social sharing tagline.', 'fusion-builder' ),
						'param_name'  => 'icon_tagline_color_hover',
						'value'       => '',
						'default'     => $fusion_settings->get( 'primary_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon_taglines',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Social Icon Separator Border Size', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border size of the social icon separator.', 'fusion-builder' ),
						'param_name'  => 'separator_border_sizes',
						'min'         => '0',
						'max'         => '20',
						'step'        => '1',
						'value'       => '0',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Social Icon Separator Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the social icon separator.', 'fusion-builder' ),
						'param_name'  => 'separator_border_color',
						'value'       => '#cccccc',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'separator_border_sizes',
								'value'    => 0,
								'operator' => '>',
							],
						],
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

add_action( 'fusion_builder_before_init', 'fusion_element_sharing_box' );
