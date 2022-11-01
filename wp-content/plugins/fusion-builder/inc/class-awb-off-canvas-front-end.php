<?php
/**
 * Off Canvas Front End class.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Builder
 * @since      3.6
 */

/**
 * Adds Off Canvas feature.
 */
class AWB_Off_Canvas_Front_End extends AWB_Layout_Conditions {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.6
	 * @var object
	 */
	private static $instance;

	/**
	 * Off Canvas post type handle.
	 *
	 * @access private
	 * @since 3.6
	 * @var string
	 */
	private $post_type = 'awb_off_canvas';

	/**
	 * Current page off canvases array.
	 *
	 * @access public
	 * @since 3.6
	 * @var array
	 */
	public static $current = [];

	/**
	 * The class constructor.
	 *
	 * @access private
	 * @since 3.6
	 * @return void
	 */
	private function __construct() {
		if ( ! apply_filters( 'fusion_load_off_canvas', true ) || false === self::is_enabled() || fusion_is_preview_frame() || fusion_is_builder_frame() ) {
			return;
		}

		add_action( 'wp_footer', [ $this, 'insert' ], 0 ); // Set priority to 0 for rendering before the js compiler.
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'styles' ] );
	}

	/**
	 * Get page options.
	 *
	 * @access public
	 * @since 3.6
	 * @param String $id off canvas ID.
	 * @return Array Page options array.
	 */
	public static function get_options( $id ) {
		$fusion_settings = awb_get_fusion_settings();

		$options = wp_parse_args(
			(array) fusion_data()->post_meta( $id )->get_all_meta(),
			[
				// General.
				'type'                           => 'popup',
				'width'                          => '',
				'width_medium'                   => '',
				'width_small'                    => '',
				'height'                         => 'fit',
				'custom_height'                  => '',
				'custom_height_medium'           => '',
				'custom_height_small'            => '',
				'horizontal_position'            => 'center',
				'horizontal_position_medium'     => '',
				'horizontal_position_small'      => '',
				'vertical_position'              => 'center',
				'vertical_position_medium'       => '',
				'vertical_position_small'        => '',
				'content_layout'                 => 'column',
				'align_content'                  => 'flex-start',
				'valign_content'                 => 'flex-start',
				'content_wrap'                   => 'wrap',
				'enter_animation'                => '',
				'enter_animation_direction'      => 'static',
				'enter_animation_speed'          => 0.5,
				'exit_animation'                 => '',
				'exit_animation_direction'       => 'static',
				'exit_animation_speed'           => 0.5,

				'off_canvas_state'               => 'closed',
				'sb_height'                      => '',
				'position'                       => 'left',
				'transition'                     => 'overlap',
				'css_class'                      => '',

				'sb_enter_animation'             => 'slideShort',
				'sb_enter_animation_speed'       => 0.5,
				'sb_exit_animation'              => 'slideShort',
				'sb_exit_animation_speed'        => 0.5,
				// Design.
				'background_color'               => '#ffffff',
				'background_image'               => '',
				'background_position'            => 'left top',
				'background_repeat'              => 'repeat',
				'background_size'                => '',
				'background_custom_size'         => '',
				'background_blend_mode'          => 'none',
				'oc_scrollbar'                   => 'default',
				'oc_scrollbar_background'        => '#f2f3f5',
				'oc_scrollbar_handle_color'      => '#65bc7b',
				'margin'                         => '',
				'padding'                        => '',
				'box_shadow'                     => 'no',
				'box_shadow_position'            => '',
				'box_shadow_blur'                => '0',
				'box_shadow_spread'              => '0',
				'box_shadow_color'               => '',
				'border_radius'                  => '',
				'border_width'                   => '',
				'border_color'                   => '',

				// Overlay.
				'overlay'                        => 'yes',
				'overlay_z_index'                => '',
				'overlay_page_scrollbar'         => 'yes',
				'overlay_background_color'       => 'rgba(0,0,0,0.8)',
				'overlay_background_image'       => '',
				'overlay_background_position'    => 'left top',
				'overlay_background_repeat'      => 'repeat',
				'overlay_background_size'        => '',
				'overlay_background_custom_size' => '',
				'overlay_background_blend_mode'  => 'none',

				// close.
				'overlay_close_on_click'         => 'yes',
				'close_on_esc'                   => 'yes',
				'auto_close_after_time'          => '',
				'close_button'                   => 'yes',
				'close_button_position'          => 'right',
				'show_close_button_after_time'   => '',
				'close_button_margin'            => '',
				'close_button_color'             => '',
				'close_button_color_hover'       => '',
				'close_icon_size'                => '16',
				'close_button_custom_icon'       => '',

				// Triggers.
				'on_page_load'                   => 'no',
				'time_on_page'                   => 'no',
				'time_on_page_duration'          => '',
				'on_scroll'                      => 'no',
				'scroll_direction'               => 'up',
				'scroll_to'                      => 'position',
				'scroll_position'                => '',
				'scroll_element'                 => '',
				'on_click'                       => 'no',
				'on_click_element'               => '',
				'exit_intent'                    => 'no',
				'after_inactivity'               => 'no',
				'inactivity_duration'            => '',
				'on_add_to_cart'                 => 'no',

				// Rules.
				'frequency'                      => 'forever',
				'frequency_xtimes'               => '',
				'frequency_xdays'                => '',
				'after_x_page_views'             => 'no',
				'number_of_page_views'           => '',
				'after_x_sessions'               => 'no',
				'number_of_sessions'             => '',
				'when_arriving_from'             => '',
				'users'                          => 'all',
				'users_roles'                    => '',
				'device'                         => '',

				// Get Element Appearance Animations option.
				'status_css_animations'          => $fusion_settings->get( 'status_css_animations' ),

				'is_mobile'                      => wp_is_mobile(),
			]
		);
		$options = self::filter_options( $options );
		if ( 'full' === $options['height'] ) {
			$options['vertical_position'] = 'flex-end';
		}

		return $options;
	}

	/**
	 * Check if off canvas has rules.
	 * users and device rules excluded because it works with php.
	 *
	 * @access public
	 * @since 3.6.2
	 * @param String $id     Off canvas ID.
	 * @return String Off    Canvas template.
	 */
	public static function has_js_rules( $id ) {
		$options = self::get_options( $id );
		if ( ! empty( $options['frequency'] ) && 'forever' !== $options['frequency'] ) {
			return true;
		}
		if ( 'yes' === $options['after_x_page_views'] ) {
			return true;
		}
		if ( 'yes' === $options['after_x_sessions'] ) {
			return true;
		}
		if ( ! empty( $options['when_arriving_from'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Off Canvas Render.
	 *
	 * @access public
	 * @since 3.6
	 * @param String  $id             Off canvas ID.
	 * @param Boolean $force          Force render?.
	 * @param Boolean $content_filter Should filter content?.
	 * @return String Off             Canvas template.
	 */
	public static function render( $id, $force = false, $content_filter = true ) {
		if ( ( ! self::is_current_user_can( $id ) || ! self::is_current_device_fit( $id ) ) && ! $force ) {
			return;
		}
		$content   = self::get_content( $id, $content_filter );
		$content   = '<div class="off-canvas-content">' . do_shortcode( $content ) . '</div>';
		$style     = self::get_style( $id );
		$close_btn = self::close_button( $id );

		$html  = $style . '<div ' . self::wrap_attr( $id ) . '><div class="awb-off-canvas" tabindex="-1">' . $close_btn . '<div ' . self::attr( $id ) . '>' . $content . '</div></div></div>';
		$html .= self::get_script( $id );

		return apply_filters( 'fusion_off_canvas_content', $html );
	}

	/**
	 * Wrap Attributes.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $id Off Canvas post ID.
	 * @return string Wrap attributes.
	 */
	public static function wrap_attr( $id ) {
		$atts            = [];
		$atts['id']      = 'awb-oc-' . $id;
		$atts['class']   = 'awb-off-canvas-wrap';
		$atts['data-id'] = $id;

		$options = self::get_options( $id );

		if ( '' !== $options['css_class'] ) {
			$atts['class'] .= ' ' . $options['css_class'];
		}

		if ( '' !== $options['type'] ) {
			$atts['class'] .= ' type-' . $options['type'];
		}

		if ( 'sliding-bar' === $options['type'] && '' !== $options['position'] ) {
			$atts['class'] .= ' position-' . $options['position'];
		}

		if ( 'no' === $options['overlay'] ) {
			$atts['class'] .= ' overlay-disabled';
		}
		if ( 'no' === $options['overlay_close_on_click'] ) {
			$atts['class'] .= ' overlay-disable-close';
		}
		if ( 'no' === $options['close_on_esc'] ) {
			$atts['class'] .= ' disable-close-on-esc';
		}

		if ( ( 'sliding-bar' === $options['type'] && 'opened' === $options['off_canvas_state'] && ! self::has_js_rules( $id ) ) || ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) ) {
			$atts['class'] .= ' awb-show';
		}
		if ( isset( $_GET['awb-studio-off-canvas'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$atts['class'] .= ' init-for-studio';
		}
		$str = [];
		foreach ( $atts as $k => $v ) {
			$str[] = $k . '="' . esc_attr( $v ) . '"';
		}

		return implode( ' ', $str );
	}

	/**
	 * Off Canvas Attributes.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $id Off Canvas post ID.
	 * @return string Wrap attributes.
	 */
	public static function attr( $id ) {
		$atts          = [];
		$atts['class'] = 'awb-off-canvas-inner';
		$options       = self::get_options( $id );

		$content = self::get_content( $id );
		if ( empty( $content ) ) {
			$atts['class'] .= ' is-empty';
		}
		if ( 'sliding-bar' === $options['type'] && 'opened' === $options['off_canvas_state'] && ! self::has_js_rules( $id ) ) {
			$atts['style'] = 'visibility: visible;';
		}
		if ( 'hidden' === $options['oc_scrollbar'] ) {
			$atts['class'] .= ' hidden-scrollbar';
		}
		$str = [];
		foreach ( $atts as $k => $v ) {
			$str[] = $k . '="' . esc_attr( $v ) . '"';
		}

		return implode( ' ', $str );
	}

	/**
	 * Get Close button.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $id Off Canvas post ID.
	 * @return String Close button HTML.
	 */
	public static function close_button( $id ) {
		$options = self::get_options( $id );
		if ( 'no' === $options['close_button'] ) {
			return;
		}

		$class = 'off-canvas-close';
		if ( ! empty( $options['close_button_custom_icon'] ) ) {
			$class .= ' ' . fusion_font_awesome_name_handler( $options['close_button_custom_icon'] );
		} else {
			$class .= ' awb-icon-close';
		}
		if ( ! empty( $options['show_close_button_after_time'] ) && intval( $options['show_close_button_after_time'] ) ) {
			$class .= ' hidden';
		}
		$btn = '<button class="' . esc_attr( $class ) . '" aria-label="' . esc_attr__( 'Close', 'fusion-builder' ) . '"></button>';

		return $btn;
	}

	/**
	 * Get off canvas CSS style.
	 *
	 * @access public
	 * @since 3.6
	 * @param String $id off canvas ID.
	 * @return String off canvas css style.
	 */
	public static function get_style( $id ) {
		$fusion_settings = awb_get_fusion_settings();

		$options          = self::get_options( $id );
		$wrap_selector    = '#awb-oc-' . $id;
		$selector         = $wrap_selector . ' .awb-off-canvas';
		$inner_selector   = $wrap_selector . ' .awb-off-canvas-inner';
		$content_selector = $wrap_selector . ' .awb-off-canvas .off-canvas-content';
		$style            = '';

		$wrap_style  = '';
		$wrap_style .= ! empty( $options['horizontal_position'] ) ? 'justify-content:' . $options['horizontal_position'] . ';' : '';
		$wrap_style .= ! empty( $options['vertical_position'] ) ? 'align-items:' . $options['vertical_position'] . ';' : '';
		$wrap_style .= ! empty( $options['overlay_z_index'] ) ? 'z-index:' . $options['overlay_z_index'] . ';' : '';

		// Overlay background.
		if ( 'yes' === $options['overlay'] ) {
			$wrap_style .= ! empty( $options['overlay_background_color'] ) ? 'background-color:' . $options['overlay_background_color'] . ';' : '';
			if ( ! empty( $options['overlay_background_image'] ) ) {
				$overlay_background_image = $options['overlay_background_image'];
				if ( is_array( $overlay_background_image ) ) {
					$overlay_background_image = isset( $overlay_background_image['url'] ) ? $overlay_background_image['url'] : '';
				}
				$wrap_style .= 'background-image:url(' . $overlay_background_image . ');';
				$wrap_style .= ! empty( $options['overlay_background_repeat'] ) ? 'background-repeat:' . $options['overlay_background_repeat'] . ';' : '';
				$wrap_style .= ! empty( $options['overlay_background_position'] ) ? 'background-position:' . $options['overlay_background_position'] . ';' : '';
				$wrap_style .= 'none' !== $options['overlay_background_blend_mode'] ? 'background-blend-mode:' . $options['overlay_background_blend_mode'] . ';' : '';
				if ( '' !== $options['overlay_background_size'] ) {
					if ( 'custom' === $options['overlay_background_size'] ) {
						$width       = ! empty( $options['overlay_background_custom_size']['width'] ) ? fusion_library()->sanitize->get_value_with_unit( $options['overlay_background_custom_size']['width'] ) : '';
						$height      = ! empty( $options['overlay_background_custom_size']['height'] ) ? fusion_library()->sanitize->get_value_with_unit( $options['overlay_background_custom_size']['height'] ) : '';
						$wrap_style .= $width ? 'background-size:' . $width . ' ' . $height . ';' : '';
					} else {
						$wrap_style .= 'background-size:' . $options['overlay_background_size'] . ';';
					}
				}
			}
		}
		$style .= $wrap_style ? $wrap_selector . '{' . $wrap_style . '}' : '';

		if ( 'sliding-bar' !== $options['type'] ) {
			// Horizontal position responsive options.
			foreach ( [ 'medium', 'small' ] as $size ) {
				$key   = 'horizontal_position_' . $size;
				$media = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $size ) );

				if ( '' === $options[ $key ] ) {
					continue;
				}

				$style .= sprintf( '%s { %s }', $media, $wrap_selector . '{justify-content: ' . $options[ $key ] . '}' );
			}

			// Vertical position responsive options.
			foreach ( [ 'medium', 'small' ] as $size ) {
				$key   = 'vertical_position_' . $size;
				$media = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $size ) );

				if ( '' === $options[ $key ] ) {
					continue;
				}

				$style .= sprintf( '%s { %s }', $media, $wrap_selector . '{align-items: ' . $options[ $key ] . '}' );
			}
		}

		$main_oc_style  = '';
		$main_oc_style .= ! empty( $options['width'] ) ? 'width:' . fusion_library()->sanitize->get_value_with_unit( $options['width'] ) . ';' : '';
		if ( ! empty( $options['height'] ) ) {
			if ( 'full' === $options['height'] ) {
				$main_oc_style .= 'height:100vh;';
			}
			if ( 'custom' === $options['height'] && ! empty( $options['custom_height'] ) ) {
				$main_oc_style .= 'height:' . fusion_library()->sanitize->get_value_with_unit( $options['custom_height'] ) . ';';
			}
		}
		$main_oc_style .= self::get_spacing( $options, 'margin' );
		$style         .= $main_oc_style ? $selector . '{' . $main_oc_style . '}' : '';

		$off_canvas_style  = '';
		$off_canvas_style .= self::get_shadow( $options );
		$off_canvas_style .= self::get_border( $options );

		$off_canvas_style .= ! empty( $options['background_color'] ) ? 'background-color:' . $options['background_color'] . ';' : '';
		if ( ! empty( $options['background_image'] ) ) {
			$background_image = $options['background_image'];
			if ( is_array( $background_image ) ) {
				$background_image = isset( $background_image['url'] ) ? $background_image['url'] : '';
			}
			$off_canvas_style .= 'background-image:url(' . $background_image . ');';
			$off_canvas_style .= ! empty( $options['background_repeat'] ) ? 'background-repeat:' . $options['background_repeat'] . ';' : '';
			$off_canvas_style .= ! empty( $options['background_position'] ) ? 'background-position:' . $options['background_position'] . ';' : '';
			$off_canvas_style .= 'none' !== $options['background_blend_mode'] ? 'background-blend-mode:' . $options['background_blend_mode'] . ';' : '';
			if ( '' !== $options['background_size'] ) {
				if ( 'custom' === $options['background_size'] ) {
					$width             = ! empty( $options['background_custom_size']['width'] ) ? fusion_library()->sanitize->get_value_with_unit( $options['background_custom_size']['width'] ) : '';
					$height            = ! empty( $options['background_custom_size']['height'] ) ? fusion_library()->sanitize->get_value_with_unit( $options['background_custom_size']['height'] ) : '';
					$off_canvas_style .= $width ? 'background-size:' . $width . ' ' . $height . ';' : '';
				} else {
					$off_canvas_style .= 'background-size:' . $options['background_size'] . ';';
				}
			}
		}
		$style .= $off_canvas_style ? $inner_selector . '{' . $off_canvas_style . '}' : '';

		// scrollbar options.
		$scrollbar_style = '';
		if ( 'custom' === $options['oc_scrollbar'] ) {
			// Firefox.
			$scrollbar_style .= $content_selector . '{scrollbar-width: thin; scrollbar-color: ' . $options['oc_scrollbar_handle_color'] . ' ' . $options['oc_scrollbar_background'] . ';}';

			// Chrome, Safari, Edge.
			$scrollbar_style .= $content_selector . '::-webkit-scrollbar{width:10px;}' . $content_selector . '::-webkit-scrollbar-track{background:' . $options['oc_scrollbar_background'] . ';}' . $content_selector . '::-webkit-scrollbar-thumb{background:' . $options['oc_scrollbar_handle_color'] . ';}';
		}
		$style .= $scrollbar_style && ! fusion_is_preview_frame() ? $scrollbar_style : '';

		// Width responsive options.
		foreach ( [ 'medium', 'small' ] as $size ) {
			$key   = 'width_' . $size;
			$media = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $size ) );

			if ( '' === $options[ $key ] ) {
				continue;
			}

			$style .= sprintf( '%s { %s }', $media, $selector . '{width: ' . fusion_library()->sanitize->get_value_with_unit( $options[ $key ] ) . '}' );
		}

		// Height responsive options.
		if ( 'custom' === $options['height'] && 'sliding-bar' !== $options['type'] ) {
			foreach ( [ 'medium', 'small' ] as $size ) {
				$key   = 'custom_height_' . $size;
				$media = sprintf( '@media only screen and (max-width:%spx)', $fusion_settings->get( 'visibility_' . $size ) );

				if ( '' === $options[ $key ] ) {
					continue;
				}

				$style .= sprintf( '%s { %s }', $media, $selector . '{height: ' . fusion_library()->sanitize->get_value_with_unit( $options[ $key ] ) . '}' );
			}
		}

		$content_style = '';
		// Padding to the content.
		$content_style .= self::get_spacing( $options, 'padding' );

		// Flex Alignment options.
		if ( ! empty( $options['content_layout'] ) && 'block' !== $options['content_layout'] ) {
			$content_style .= 'flex-direction:' . $options['content_layout'] . ';';
			$content_style .= 'justify-content:' . $options['align_content'] . ';';
		}
		if ( ! empty( $options['content_layout'] ) && 'block' === $options['content_layout'] ) {
			$content_style .= 'display:' . $options['content_layout'] . ';';
		}
		if ( ! empty( $options['valign_content'] ) && 'row' === $options['content_layout'] ) {
			$content_style .= 'align-items:' . $options['valign_content'] . ';';
		}
		if ( ! empty( $options['content_wrap'] ) && 'row' === $options['content_layout'] ) {
			$content_style .= 'flex-wrap:' . $options['content_wrap'] . ';';
		}
		if ( 'column' === $options['content_layout'] ) {
			$content_style .= 'flex-wrap: nowrap;';
		}
		$style .= $content_style ? $selector . ' .off-canvas-content{' . $content_style . '}' : '';

		$close_button_style = '';
		if ( ! isset( $options['close_button_margin']['top'] ) || '' === $options['close_button_margin']['top'] ) {
			$close_button_style .= 'margin-top:20px;';
		}
		if ( 'left' === $options['close_button_position'] ) {
			$close_button_style .= 'right: auto; left: 0;';
			if ( ! isset( $options['close_button_margin']['left'] ) || '' === $options['close_button_margin']['left'] ) {
				$close_button_style .= 'margin-left:20px;';
			}
			if ( ! isset( $options['close_button_margin']['right'] ) || '' === $options['close_button_margin']['right'] ) {
				$close_button_style .= 'margin-right:0;';
			}
		} else {
			if ( ! isset( $options['close_button_margin']['left'] ) || '' === $options['close_button_margin']['left'] ) {
				$close_button_style .= 'margin-left:0;';
			}
			if ( ! isset( $options['close_button_margin']['right'] ) || '' === $options['close_button_margin']['right'] ) {
				$close_button_style .= 'margin-right:20px;';
			}
		}
		$close_button_style .= self::get_spacing( $options, 'close_button_margin', 'margin' );

		if ( ! empty( $options['close_button_color'] ) ) {
			$close_button_style .= 'color:' . $options['close_button_color'] . ';';
		}
		if ( ! empty( $options['close_icon_size'] ) ) {
			$close_button_style .= 'font-size:' . $options['close_icon_size'] . 'px;';
		}
		$style .= $close_button_style ? $selector . ' .off-canvas-close{' . $close_button_style . '}' : '';

		$close_button_style_hover = '';
		if ( ! empty( $options['close_button_color_hover'] ) ) {
			$close_button_style_hover .= 'color:' . $options['close_button_color_hover'] . ';';
		}
		$style .= $close_button_style_hover ? $selector . ' .off-canvas-close:hover{' . $close_button_style_hover . '}' : '';
		if ( 'sliding-bar' === $options['type'] && 'opened' === $options['off_canvas_state'] && 'push' === $options['transition'] && ! self::has_js_rules( $id ) ) {
			if ( 'right' === $options['position'] ) {
				$style .= '#wrapper{margin-right:-' . fusion_library()->sanitize->get_value_with_unit( $options['width'] ) . ';}';
			}
			if ( 'left' === $options['position'] ) {
				$style .= '#wrapper{margin-left:' . fusion_library()->sanitize->get_value_with_unit( $options['width'] ) . ';}';
			}
		}
		// Prevent higher & duplicated custom CSS in LE.
		if ( ! fusion_is_preview_frame() ) {
			$custom_css = get_post_meta( $id, '_fusion_builder_custom_css', true );
			$style     .= $custom_css ? $custom_css : '';
		}
		$style = $style ? '<style id="awb-off-canvas-style-block-' . $id . '">' . $style . '</style>' : '';

		return $style;
	}

	/**
	 * Get spacing.
	 *
	 * @access public
	 * @since 3.6
	 * @param Array  $options options array.
	 * @param String $key spacing option key.
	 * @param String $prop CSS property.
	 * @return String Spacing css.
	 */
	public static function get_spacing( $options, $key, $prop = '' ) {
		if ( empty( $options[ $key ] ) || ! is_array( $options[ $key ] ) ) {
			return;
		}

		$prop   = $prop ? $prop : $key;
		$prefix = '';
		if ( 'margin' === $prop || 'padding' === $prop ) {
			$prefix = $prop . '-';
		}
		$css = '';
		foreach ( $options[ $key ] as $k => $v ) {
			if ( '' === $v ) {
				continue;
			}
			$css .= $prefix . $k . ':' . fusion_library()->sanitize->get_value_with_unit( $v ) . ';';
		}

		return $css;
	}

	/**
	 * Get box shadow.
	 *
	 * @access public
	 * @since 3.6
	 * @param Array $options options array.
	 * @return String Box shadow css.
	 */
	public static function get_shadow( $options ) {
		if ( 'yes' !== $options['box_shadow'] ) {
			return;
		}
		$css    = '';
		$h      = '0';
		$v      = '0';
		$blur   = $options['box_shadow_blur'];
		$spread = $options['box_shadow_spread'];
		$color  = $options['box_shadow_color'];
		if ( ! empty( $options['box_shadow_position'] ) && is_array( $options['box_shadow_position'] ) ) {
			$h = isset( $options['box_shadow_position']['horizontal'] ) ? $options['box_shadow_position']['horizontal'] : $h;
			$v = isset( $options['box_shadow_position']['vertical'] ) ? $options['box_shadow_position']['vertical'] : $v;
		}
		$css = 'box-shadow:' . fusion_library()->sanitize->get_value_with_unit( $h ) . ' ' . fusion_library()->sanitize->get_value_with_unit( $v ) . ' ' . fusion_library()->sanitize->get_value_with_unit( $blur ) . ' ' . fusion_library()->sanitize->get_value_with_unit( $spread ) . ' ' . $color . ';';
		return $css;
	}

	/**
	 * Get border.
	 *
	 * @access public
	 * @since 3.6
	 * @param Array $options options array.
	 * @return String borders css.
	 */
	public static function get_border( $options ) {
		$css = '';

		// Border radius.
		if ( ! empty( $options['border_radius'] ) && is_array( $options['border_radius'] ) ) {
			foreach ( $options['border_radius'] as $k => $v ) {
				if ( $v ) {
					$css .= 'border-' . str_replace( '_', '-', $k ) . '-radius:' . fusion_library()->sanitize->get_value_with_unit( $v ) . ';';
				}
			}
		}

		// Border width.
		if ( ! empty( $options['border_width'] ) && is_array( $options['border_width'] ) ) {
			foreach ( $options['border_width'] as $k => $v ) {
				if ( $v ) {
					$css .= 'border-' . $k . '-width:' . fusion_library()->sanitize->get_value_with_unit( $v ) . ';';
				}
			}
		}
		// Border color.
		if ( ! empty( $options['border_color'] ) ) {
			$css .= 'border-color:' . $options['border_color'] . ';';
		}
		return $css;
	}

	/**
	 * Insert Off Canvas to wp footer.
	 *
	 * @access public
	 * @since 3.6
	 * @return void
	 */
	public function insert() {
		$off_canvases = $this->get_current_page_off_canvases();

		foreach ( $off_canvases as $id => $v ) {
			echo self::render( $id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Get off canvases assigned to current page.
	 *
	 * @since 3.6
	 * @return array Array of off canvases.
	 * @access public
	 */
	public function get_current_page_off_canvases() {
		$args         = [
			'post_type'      => [ $this->post_type ],
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'post__not_in'   => array_keys( self::$current ),
		];
		$off_canvases = fusion_cached_query( $args );
		$off_canvases = $off_canvases->posts;

		if ( is_array( $off_canvases ) ) {
			foreach ( $off_canvases as $off_canvas ) {
				if ( $this->check_full_conditions( $off_canvas ) ) {
					self::$current[ $off_canvas->ID ] = $off_canvas->post_title;
				}
			}
		}

		return self::$current;
	}

	/**
	 * Check if current post matched conditions of template.
	 *
	 * @since 3.6
	 * @param WP_Post $template    Section post object.
	 * @return bool Whether it passed or not.
	 * @access public
	 */
	private function check_full_conditions( $template ) {

		if ( 'yes' === fusion_data()->post_meta( $template->ID )->get( 'conditions_enabled' ) ) {
			$conditions = self::get_conditions( $template );
			if ( is_array( $conditions ) && 0 < count( $conditions ) ) {
				foreach ( $conditions as $condition ) {
					if ( isset( $condition['type'] ) && '' !== $condition['type'] && isset( $condition[ $condition['type'] ] ) ) {
						$type    = $condition['type'];
						$exclude = 'exclude' === $condition['mode'];

						$pass = 'archives' === $type ? $this->check_archive_condition( $condition ) : $this->check_singular_condition( $condition );

						// If it doesn't pass all exclude conditions check is false.
						// If all exclude conditions are valid and we find one valid condition check is true.
						if ( $exclude && ! $pass ) {
							return false;
						} elseif ( ! $exclude && $pass ) {
							return true;
						}
					}
				}
			} else {
				return true;
			}
		}

		// The default behavior.
		return false;
	}

	/**
	 * Get conditions.
	 *
	 * @static
	 * @since 3.6
	 * @param WP_Post $template Section post object.
	 * @return array  $return Array of conditions.
	 * @access private
	 */
	private static function get_conditions( $template ) {
		if ( $template ) {
			$data = json_decode( wp_unslash( fusion_data()->post_meta( $template->ID )->get( 'layout_conditions' ) ), true );
			if ( is_array( $data ) ) {
				return self::group_conditions( $data );
			}
		}
		return false;
	}

	/**
	 * Modify options mostly for the sliding bar type.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $options Options array to modify.
	 * @return array The modified options array.
	 */
	public static function filter_options( $options ) {
		if ( 'sliding-bar' !== $options['type'] ) {
			return $options;
		}
		$options['enter_animation']          = $options['sb_enter_animation'];
		$options['sb_enter_animation_speed'] = $options['sb_enter_animation_speed'];
		$options['exit_animation']           = $options['sb_exit_animation'];
		$options['sb_exit_animation_speed']  = $options['sb_exit_animation_speed'];

		if ( 'left' === $options['position'] ) {
			$options['height']                    = 'full';
			$options['enter_animation_direction'] = 'left';
			$options['exit_animation_direction']  = 'left';
			$options['vertical_position']         = 'flex-start';
			if ( is_rtl() ) {
				$options['horizontal_position'] = 'flex-end';
			} else {
				$options['horizontal_position'] = 'flex-start';
			}
		}
		if ( 'right' === $options['position'] ) {
			$options['height']                    = 'full';
			$options['enter_animation_direction'] = 'right';
			$options['exit_animation_direction']  = 'right';
			$options['vertical_position']         = 'flex-start';
			if ( is_rtl() ) {
				$options['horizontal_position'] = 'flex-start';
			} else {
				$options['horizontal_position'] = 'flex-end';
			}
		}
		if ( 'top' === $options['position'] ) {
			$height                               = $options['sb_height'] ? $options['sb_height'] : 'auto';
			$options['width']                     = '100vw';
			$options['height']                    = 'custom';
			$options['custom_height']             = $height;
			$options['enter_animation_direction'] = 'down';
			$options['exit_animation_direction']  = 'up';
			$options['vertical_position']         = 'flex-start';
			$options['horizontal_position']       = 'flex-start';
		}
		if ( 'bottom' === $options['position'] ) {
			$height                               = $options['sb_height'] ? $options['sb_height'] : 'auto';
			$options['width']                     = '100vw';
			$options['height']                    = 'custom';
			$options['custom_height']             = $height;
			$options['enter_animation_direction'] = 'up';
			$options['exit_animation_direction']  = 'down';
			$options['vertical_position']         = 'flex-end';
			$options['horizontal_position']       = 'flex-start';
		}

		return $options;
	}

	/**
	 * Test users rules against current user .
	 *
	 * @access public
	 * @since 3.6
	 * @param string $id off canvas ID.
	 * @return bool true if current user can see popup false if not.
	 */
	public static function is_current_user_can( $id ) {
		$users       = fusion_data()->post_meta( $id )->get( 'users' );
		$users_roles = fusion_data()->post_meta( $id )->get( 'users_roles' );

		if ( ! empty( $users ) ) {
			if ( 'logged-out' === $users ) {
				if ( is_user_logged_in() ) {
					return false;
				}
			}
			if ( 'logged-in' === $users ) {
				if ( ! is_user_logged_in() ) {
					return false;
				}
				if ( ! empty( $users_roles ) ) {
					$current_user = wp_get_current_user();
					$intersect    = array_intersect( $users_roles, $current_user->roles );

					if ( ! empty( $current_user ) ) {
						if ( empty( $intersect ) ) {
							return false;
						}
					}
				}
			}
		}

		// default behavior.
		return true;
	}

	/**
	 * Test device rules against user device.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $id off canvas ID.
	 * @return bool true if current device can display popup false if not.
	 */
	public static function is_current_device_fit( $id ) {
		$devices = fusion_data()->post_meta( $id )->get( 'device' );

		if ( ! empty( $devices ) && is_array( $devices ) ) {
			$current_device = 'desktop';
			if ( fusion_library()->device_detection->is_mobile() ) {
				$current_device = 'mobile';
			} elseif ( fusion_library()->device_detection->is_tablet() ) {
				$current_device = 'tablet';
			} elseif ( ! wp_is_mobile() ) {
				$current_device = 'desktop';
			}
			if ( ! in_array( $current_device, $devices, true ) ) {
				return false;
			}
		}
		// default behavior.
		return true;
	}

	/**
	 * Get off canvas content.
	 *
	 * @access public
	 * @since 3.6
	 * @param string  $id     off canvas ID.
	 * @param boolean $filter Should filter content or not.
	 * @return array Page options array.
	 */
	public static function get_content( $id, $filter = false ) {
		$off_canvas = get_post( $id );
		if ( $off_canvas ) {
			if ( ! $filter ) {
				return $off_canvas->post_content;
			}

			$content = apply_filters( 'the_content', $off_canvas->post_content );
			$content = str_replace( ']]>', ']]&gt;', $content );

			return $content;
		}
		return false;
	}

	/**
	 * Get script with off canvas options object.
	 *
	 * @access public
	 * @since 3.6
	 * @param string $id off canvas ID.
	 * @return array Page options array.
	 */
	public static function get_script( $id ) {
		$options                 = self::get_options( $id );
		$options['has_js_rules'] = self::has_js_rules( $id );
		$json                    = wp_json_encode( $options );
		return '<script>window.off_canvas_' . $id . ' = ' . $json . ';</script>';
	}

	/**
	 * Enqueue JS Scripts.
	 *
	 * @access public
	 * @since 3.6
	 * @return void.
	 */
	public static function scripts() {
		Fusion_Dynamic_JS::enqueue_script(
			'awb-off-canvas',
			FusionBuilder::$js_folder_url . '/general/awb-off-canvas.js',
			FusionBuilder::$js_folder_path . '/general/awb-off-canvas.js',
			[ 'jquery' ],
			FUSION_BUILDER_VERSION,
			true
		);
	}

	/**
	 * Enqueue styles on frontend.
	 *
	 * @since 3.6
	 * @access public
	 * @return void
	 */
	public function styles() {
		Fusion_Dynamic_CSS::enqueue_style(
			FUSION_BUILDER_PLUGIN_DIR . 'assets/css/off-canvas.min.css',
			FUSION_BUILDER_PLUGIN_URL . 'assets/css/off-canvas.min.css'
		);
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new AWB_Off_Canvas_Front_End();
		}
		return self::$instance;
	}

	/**
	 * Checks if off canvas are enabled.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @return bool
	 */
	public static function is_enabled() {
		$fusion_settings = awb_get_fusion_settings();

		$status_awb_off_canvas = $fusion_settings->get( 'status_awb_Off_Canvas' );
		$status_awb_off_canvas = '0' === $status_awb_off_canvas ? false : true;
		return boolval( apply_filters( 'fusion_load_off_canvas', $status_awb_off_canvas ) );
	}

	/**
	 * Add it to used items stack.
	 *
	 * @static
	 * @access public
	 * @since 3.6
	 * @param int $off_canvas_post_id Off Canvas post ID.
	 * @return void
	 */
	public static function add_off_canvas_to_stack( $off_canvas_post_id ) {

		// Early exit if Off Canvas is not published.
		if ( ! $off_canvas_post_id || 'publish' !== get_post_status( $off_canvas_post_id ) ) {
			return;
		}

		if ( ! isset( self::$current[ $off_canvas_post_id ] ) ) {
			self::$current[ $off_canvas_post_id ] = $off_canvas_post_id;
		}
	}

	/**
	 * Getter for available Off Canvas items, returns ID => post_title pair.
	 *
	 * @access public
	 * @since 3.6
	 * @return array
	 */
	public function get_available_items() {

		$off_canvas_items = [];
		$args             = [
			'post_type'           => 'awb_off_canvas',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
			'posts_per_page'      => -1,
			'fields'              => 'id=>parent',
		];

		// Don't fetch entire object.
		add_filter( 'posts_fields', [ $this, 'filter_wp_query_return_fields' ], 10, 2 );

		$query = new WP_Query( $args );

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				$off_canvas_items[ $post->ID ] = $post->post_title;
			}
		}

		remove_filter( 'posts_fields', [ $this, 'filter_wp_query_return_fields' ], 10 );

		return $off_canvas_items;
	}

	/**
	 * Filter for fetching only necessary post object's fields (leaving out post_parent results in PHP notice).
	 *
	 * @access public
	 * @param string $fields query fields.
	 * @param object $query query object.
	 * @since 3.6
	 */
	public function filter_wp_query_return_fields( $fields, $query ) {
		global $wpdb;

		return "{$wpdb->posts}.ID, {$wpdb->posts}.post_parent, {$wpdb->posts}.post_title";
	}
}

/**
 * Instantiates the AWB_Off_Canvas_Front_End class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.6
 * @return object AWB_Off_Canvas_Front_End
 */
function AWB_Off_Canvas_Front_End() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Off_Canvas_Front_End::get_instance();
}
AWB_Off_Canvas_Front_End();
