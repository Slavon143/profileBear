<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_separator' ) ) {

	if ( ! class_exists( 'FusionSC_Separator' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Separator extends Fusion_Element {

			/**
			 * The one, true instance of this object.
			 *
			 * @static
			 * @access private
			 * @since 3.3
			 * @var object
			 */
			private static $instance;

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
				add_filter( 'fusion_attr_separator-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_separator-shortcode-border-part', [ $this, 'border_part_attr' ] );
				add_filter( 'fusion_attr_separator-shortcode-icon-wrapper', [ $this, 'icon_wrapper_attr' ] );
				add_filter( 'fusion_attr_separator-shortcode-icon', [ $this, 'icon_attr' ] );

				add_shortcode( 'fusion_separator', [ $this, 'render' ] );
			}

			/**
			 * Creates or returns an instance of this class.
			 *
			 * @static
			 * @access public
			 * @since 3.3
			 */
			public static function get_instance() {

				// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
				if ( null === self::$instance ) {
					self::$instance = new FusionSC_Separator();
				}
				return self::$instance;
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
					'hide_on_mobile'    => fusion_builder_default_visibility( 'string' ),
					'sticky_display'    => '',
					'class'             => '',
					'id'                => '',
					'alignment'         => 'center',
					'bottom_margin'     => '',
					'border_size'       => $fusion_settings->get( 'separator_border_size' ),
					'flex_grow'         => '',
					'icon'              => '',
					'icon_size'         => $fusion_settings->get( 'separator_icon_size' ),
					'icon_color'        => $fusion_settings->get( 'separator_icon_color', false, '' ),
					'icon_circle'       => $fusion_settings->get( 'separator_circle' ),
					'icon_circle_color' => $fusion_settings->get( 'separator_circle_bg_color' ),
					'sep_color'         => $fusion_settings->get( 'sep_color' ),
					'style_type'        => $fusion_settings->get( 'separator_style_type' ),
					'top_margin'        => '',
					'width'             => '',
					'bottom'            => '', // Deprecated.
					'color'             => '', // Deprecated.
					'style'             => '', // Deprecated.
					'top'               => '', // Deprecated.
					'position'          => '', // Hidden field for position type.
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
					'separator_border_size'     => 'border_size',
					'separator_icon_size'       => 'icon_size',
					'separator_icon_color'      => 'icon_color',
					'separator_circle'          => 'icon_circle',
					'separator_circle_bg_color' => 'icon_circle_color',
					'sep_color'                 => 'sep_color',
					'separator_style_type'      => 'style_type',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_separator' );

				$defaults['border_size']   = FusionBuilder::validate_shortcode_attr_value( $defaults['border_size'], 'px' );
				$defaults['width']         = FusionBuilder::validate_shortcode_attr_value( $defaults['width'], 'px' );
				$defaults['top_margin']    = FusionBuilder::validate_shortcode_attr_value( $defaults['top_margin'], 'px' );
				$defaults['bottom_margin'] = FusionBuilder::validate_shortcode_attr_value( $defaults['bottom_margin'], 'px' );

				if ( '0' === $defaults['icon_circle'] ) {
					$defaults['icon_circle'] = 'no';
				}

				if ( ! isset( $args['style_type'] ) ) {
					$defaults['style_type'] = 'none';
				}

				if ( $defaults['style'] ) {
					$defaults['style_type'] = $defaults['style'];
				} elseif ( 'default' === $defaults['style_type'] ) {
					$defaults['style_type'] = $fusion_settings->get( 'separator_style_type' );
				}

				extract( $defaults );

				$this->args = $defaults;

				$this->args['style_type'] = str_replace( ' ', '|', $this->args['style_type'] );

				if ( $this->args['bottom'] ) {
					$this->args['bottom_margin'] = FusionBuilder::validate_shortcode_attr_value( $this->args['bottom'], 'px' );
				}

				if ( $this->args['color'] ) {
					$this->args['sep_color'] = $this->args['color'];
				}

				// Fallback, in case TO is unset, which was need for installs before 7.0.
				if ( empty( $this->args['icon_color'] ) ) {
					$this->args['icon_color'] = $this->args['sep_color'];
				}

				if ( $this->args['top'] ) {
					$this->args['top_margin'] = FusionBuilder::validate_shortcode_attr_value( $this->args['top'], 'px' );

					if ( ! $this->args['bottom'] && 'none' !== $defaults['style'] ) {
						$this->args['bottom_margin'] = FusionBuilder::validate_shortcode_attr_value( $this->args['top'], 'px' );
					}
				}

				$contents = '';
				if ( 'none' !== $this->args['style_type'] ) {
					$contents = '<div ' . FusionBuilder::attributes( 'separator-shortcode-border-part' ) . '></div>';

					if ( $this->args['icon'] ) {
						$contents .= '<span ' . FusionBuilder::attributes( 'separator-shortcode-icon-wrapper' ) . '><i ' . FusionBuilder::attributes( 'separator-shortcode-icon' ) . '></i></span>';
						$contents .= '<div ' . FusionBuilder::attributes( 'separator-shortcode-border-part' ) . '></div>';
					}
				}

				if ( fusion_builder_container()->is_flex() || 'absolute' === $this->args['position'] ) {
					$html = '<div ' . FusionBuilder::attributes( 'separator-shortcode' ) . '>' . $contents . '</div>';
				} else {
					$html  = '<div ' . FusionBuilder::attributes( 'fusion-sep-clear' ) . '></div><div ' . FusionBuilder::attributes( 'separator-shortcode' ) . '>' . $contents . '</div>';
					$html .= '<div ' . FusionBuilder::attributes( 'fusion-sep-clear' ) . '></div>';
				}

				$this->on_render();

				return apply_filters( 'fusion_element_separator_content', $html, $args );

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
						'class' => 'fusion-separator',
						'style' => '',
					]
				);

				$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

				if ( $this->args['icon'] ) {
					$attr['class'] .= ' fusion-has-icon';
				}

				if ( ! $this->args['width'] || '100%' === $this->args['width'] ) {
					$attr['class'] .= ' fusion-full-width-sep';
				}

				if ( 'absolute' === $this->args['position'] ) {
					$attr['class'] .= ' fusion-absolute-separator';
					$attr['class'] .= ' fusion-align-' . $this->args['alignment'];
					if ( 1 === (int) $this->args['border_size'] ) {
						$attr['class'] .= ' fusion-single-px';
					}
					if ( '' === $this->args['width'] ) {
						$this->args['width'] = '100%';
					}
				} elseif ( fusion_builder_container()->is_flex() ) {
					if ( 'center' === $this->args['alignment'] ) {
						$attr['style'] .= 'align-self: center;margin-left: auto;margin-right: auto;';
					} elseif ( 'right' === $this->args['alignment'] ) {
						$attr['style'] .= 'align-self: ' . ( is_rtl() ? 'flex-start;' : 'flex-end;' ) . 'margin-left:auto;';
					} else {
						$attr['style'] .= 'align-self: ' . ( is_rtl() ? 'flex-end;' : 'flex-start;' ) . 'margin-right:auto;';
					}
				} else {
					if ( 'center' === $this->args['alignment'] ) {
						$attr['style'] .= 'margin-left: auto;margin-right: auto;';
					} else {
						$attr['style'] .= 'float:' . $this->args['alignment'] . ';';
						$attr['class'] .= ' fusion-clearfix';
					}
				}

				if ( $this->args['flex_grow'] ) {
					$attr['style'] .= 'flex-grow:' . $this->args['flex_grow'] . ';';
				}

				if ( $this->args['top_margin'] ) {
					$attr['style'] .= 'margin-top:' . $this->args['top_margin'] . ';';
				}
				if ( $this->args['bottom_margin'] ) {
					$attr['style'] .= 'margin-bottom:' . $this->args['bottom_margin'] . ';';
				}

				$attr['style'] .= 'width:100%;';

				if ( $this->args['width'] ) {
					$attr['style'] .= 'max-width:' . $this->args['width'] . ';';
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
			 * Builds the border parts attributes array.
			 *
			 * @access public
			 * @since 3.0
			 * @return array
			 */
			public function border_part_attr() {
				$attr = [
					'class' => 'fusion-separator-border',
					'style' => '',
				];

				$styles = explode( '|', $this->args['style_type'] );

				if ( ! in_array( 'none', $styles, true ) && ! in_array( 'single', $styles, true ) && ! in_array( 'double', $styles, true ) && ! in_array( 'shadow', $styles, true ) ) {
					$styles[] .= 'single';
				}

				foreach ( $styles as $style ) {
					$attr['class'] .= ' sep-' . $style;
				}

				if ( $this->args['sep_color'] ) {
					if ( 'shadow' === $this->args['style_type'] ) {

						$shadow = 'background:radial-gradient(ellipse at 50% -50% , ' . $this->args['sep_color'] . ' 0px, rgba(255, 255, 255, 0) 80%) repeat scroll 0 0 rgba(0, 0, 0, 0);';

						$attr['style']  = $shadow;
						$attr['style'] .= str_replace( 'radial-gradient', '-webkit-radial-gradient', $shadow );
						$attr['style'] .= str_replace( 'radial-gradient', '-moz-radial-gradient', $shadow );
						$attr['style'] .= str_replace( 'radial-gradient', '-o-radial-gradient', $shadow );
					} elseif ( 'none' !== $this->args['style_type'] ) {

						$attr['style'] = 'border-color:' . $this->args['sep_color'] . ';';
					}
				}

				if ( in_array( 'single', $styles, true ) ) {
					$attr['style'] .= 'border-top-width:' . $this->args['border_size'] . ';';
				}

				if ( in_array( 'double', $styles, true ) ) {
					$attr['style'] .= 'border-top-width:' . $this->args['border_size'] . ';border-bottom-width:' . $this->args['border_size'] . ';';
				}

				return $attr;
			}

			/**
			 * Builds the icon-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_wrapper_attr() {

				$attr = [
					'class' => 'icon-wrapper',
				];

				$circle_color = $this->args['sep_color'];
				if ( 'no' === $this->args['icon_circle'] ) {
					$circle_color = 'transparent';
				}

				$attr['style'] = 'border-color:' . $circle_color . ';';

				if ( $this->args['icon_circle_color'] && 'no' !== $this->args['icon_circle'] ) {
					$attr['style'] .= 'background-color:' . $this->args['icon_circle_color'] . ';';
				}

				if ( '' !== $this->args['icon_size'] ) {
					$attr['style'] .= 'font-size:' . fusion_library()->sanitize->size( $this->args['icon_size'] ) . 'px;';
					$attr['style'] .= 'width: 1.75em; height: 1.75em;';
				}

				if ( $this->args['border_size'] ) {
					$attr['style'] .= 'border-width:' . fusion_library()->sanitize->size( $this->args['border_size'] ) . ';';
					$attr['style'] .= 'padding:' . fusion_library()->sanitize->size( $this->args['border_size'] ) . ';';
				}

				$styles = explode( '|', $this->args['style_type'] );
				if ( in_array( 'single', $styles, true ) ) {
					$margin_top     = (int) str_replace( 'px', '', $this->args['border_size'] );
					$attr['style'] .= 'margin-top:-' . ( $margin_top / 2 ) . 'px';
				}

				return $attr;

			}

			/**
			 * Builds the icon attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function icon_attr() {
				$attr = [
					'class'       => fusion_font_awesome_name_handler( $this->args['icon'] ),
					'style'       => 'font-size: inherit;',
					'aria-hidden' => 'true',
				];

				if ( ! empty( $this->args['icon_color'] ) ) {
					$attr['style'] .= 'color:' . $this->args['icon_color'] . ';';
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

				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $fusion_settings, $dynamic_css_helpers;

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-separator' ], '.fusion-separator' );

				$elements = array_merge(
					$dynamic_css_helpers->map_selector( $main_elements, '.sep-single' ),
					$dynamic_css_helpers->map_selector( $main_elements, '.sep-double' ),
					$dynamic_css_helpers->map_selector( $main_elements, '.sep-dashed' ),
					$dynamic_css_helpers->map_selector( $main_elements, '.sep-dotted' )
				);
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'sep_color' ) );

				// Content separator for blog.
				$elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-body .fusion-content-sep:not([class*="sep-"])' ], '.fusion-body .fusion-content-sep' );

				$separator_style_type  = $fusion_settings->get( 'separator_style_type' );
				$separator_border_size = $fusion_settings->get( 'separator_border_size' ) . 'px';
				if ( false !== strpos( $separator_style_type, 'none' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'none';
				} elseif ( false !== strpos( $separator_style_type, 'single' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']           = 'auto';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom']    = 'none';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top-width'] = $separator_border_size;
				} elseif ( false !== strpos( $separator_style_type, 'double' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top-width']    = $separator_border_size;
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom-width'] = $separator_border_size;
				} elseif ( false !== strpos( $separator_style_type, 'shadow' ) ) {
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']        = '1px';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-top']    = 'none';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['border-bottom'] = 'none';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background']    = sprintf( '-webkit-radial-gradient(ellipse at 50%% -50%% , %s 0px, rgba(255, 255, 255, 0) 80%%) repeat scroll 0 0 rgba(0, 0, 0, 0)', $fusion_settings->get( 'sep_color' ) );
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background']    = sprintf( 'radial-gradient(ellipse at 50%% -50%% , %s 0px, rgba(255, 255, 255, 0) 80%%) repeat scroll 0 0 rgba(0, 0, 0, 0)', $fusion_settings->get( 'sep_color' ) );

					$elements = $dynamic_css_helpers->map_selector( $elements, ':after' );
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['display']    = 'block';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['content']    = '""';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['margin-top'] = '10px';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['height']     = '6px';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['width']      = '100%';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = '-webkit-radial-gradient(ellipse at 50% -50% , rgba(0, 0, 0, 0.5) 0px, rgba(255, 255, 255, 0) 65%);';
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['background'] = 'radial-gradient(ellipse at 50% -50% , rgba(0, 0, 0, 0.5) 0px, rgba(255, 255, 255, 0) 65%);';
				}

				return $css;

			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/separator.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Separator settings.
			 */
			public function add_options() {

				return [
					'separator_shortcode_section' => [
						'label'       => esc_html__( 'Separator', 'fusion-builder' ),
						'description' => '',
						'id'          => 'separator_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-minus',
						'fields'      => [
							'separator_style_type'      => [
								'label'       => esc_html__( 'Separator Style', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the line style of all separators, divider lines on portfolio archives, blog archives, product archives and more.', 'fusion-builder' ),
								'id'          => 'separator_style_type',
								'default'     => 'double',
								'type'        => 'select',
								'transport'   => 'postMessage',
								'choices'     => [
									'none'          => esc_attr__( 'No Style', 'fusion-builder' ),
									'single'        => esc_attr__( 'Single Border Solid', 'fusion-builder' ),
									'double'        => esc_attr__( 'Double Border Solid', 'fusion-builder' ),
									'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-builder' ),
									'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-builder' ),
									'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-builder' ),
									'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-builder' ),
									'shadow'        => esc_attr__( 'Shadow', 'fusion-builder' ),
								],
							],
							'sep_color'                 => [
								'label'       => esc_html__( 'Separator Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of all separators, divider lines and borders for meta, previous & next, filters, archive pages, boxes around number pagination, sidebar widgets, accordion divider lines, counter boxes and more.', 'fusion-builder' ),
								'id'          => 'sep_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'css_vars'    => [
									[
										'name'     => '--sep_color',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'separator_border_size'     => [
								'label'       => esc_html__( 'Border Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border size of the separator.', 'fusion-builder' ),
								'id'          => 'separator_border_size',
								'default'     => '1',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '0',
									'max'  => '50',
									'step' => '1',
								],
							],
							'separator_icon_size'       => [
								'label'       => esc_html__( 'Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the icon size of the separator.', 'fusion-builder' ),
								'id'          => 'separator_icon_size',
								'default'     => '16',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '100',
									'step' => '1',
								],
							],
							'separator_icon_color'      => [
								'label'       => esc_html__( 'Icon Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the Icon', 'fusion-builder' ),
								'id'          => 'separator_icon_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'separator_circle'          => [
								'label'       => esc_html__( 'Separator Circle', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on if you want to display a circle around the separator icon.', 'fusion-builder' ),
								'id'          => 'separator_circle',
								'default'     => '1',
								'type'        => 'switch',
								'transport'   => 'postMessage',
							],
							'separator_circle_bg_color' => [
								'label'       => esc_html__( 'Circle Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the background color of the circle around the icon.', 'fusion-builder' ),
								'id'          => 'separator_circle_bg_color',
								'default'     => 'rgba(255,255,255,0)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
								'required'    => [
									[
										'setting'  => 'separator_circle',
										'operator' => '==',
										'value'    => '1',
									],
								],
							],
						],
					],
				];
			}
		}
	}

	/**
	 * Instantiates the separator element.
	 *
	 * @return object FusionSC_Separator
	 */
	function fusion_separator() { // phpcs:ignore WordPress.NamingConventions
		return FusionSC_Separator::get_instance();
	}

	// Instantiate separator.
	fusion_separator();
}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_separator() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Separator',
			[
				'name'       => esc_attr__( 'Separator', 'fusion-builder' ),
				'shortcode'  => 'fusion_separator',
				'icon'       => 'fusiona-minus',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-separator-preview.php',
				'preview_id' => 'fusion-builder-block-module-separator-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/separator-element/',
				'params'     => [
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Style', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the separator line style.', 'fusion-builder' ),
						'param_name'  => 'style_type',
						'value'       => [
							'default'       => esc_attr__( 'Default', 'fusion-builder' ),
							'none'          => esc_attr__( 'No Style', 'fusion-builder' ),
							'single solid'  => esc_attr__( 'Single Border Solid', 'fusion-builder' ),
							'double solid'  => esc_attr__( 'Double Border Solid', 'fusion-builder' ),
							'single|dashed' => esc_attr__( 'Single Border Dashed', 'fusion-builder' ),
							'double|dashed' => esc_attr__( 'Double Border Dashed', 'fusion-builder' ),
							'single|dotted' => esc_attr__( 'Single Border Dotted', 'fusion-builder' ),
							'double|dotted' => esc_attr__( 'Double Border Dotted', 'fusion-builder' ),
							'shadow'        => esc_attr__( 'Shadow', 'fusion-builder' ),
						],
						'default'     => 'default',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Flex Grow', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the amount (in parts) of the available space inside the flex column the separator should take up. If the separator is the only element in the column using flex grow, any value above 0 will let it take up all available space. Set to 0 to disable its ability to grow.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'param_name'  => 'flex_grow',
						'value'       => '0',
						'min'         => '0',
						'max'         => '10',
						'step'        => '1',
						'dependency'  => [
							[
								'element'  => 'fusion_builder_container',
								'param'    => 'type',
								'value'    => 'flex',
								'operator' => '==',
							],
						],
					],
					'fusion_margin_placeholder'            => [
						'param_name'  => 'dimensions',
						'description' => esc_attr__( 'Spacing above and below the separator. In px, em or %, e.g. 10px.', 'fusion-builder' ),
						'value'       => [
							'top_margin'    => '',
							'bottom_margin' => '',

						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Separator Width', 'fusion-builder' ),
						'param_name'       => 'dimensions_width',
						'value'            => [
							'width' => '',
						],
						'description'      => esc_attr__( 'In pixels (px or %), ex: 1px, ex: 50%. Leave blank for full width.', 'fusion-builder' ),
						'group'            => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Alignment', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the separator alignment; only works when a width is specified.', 'fusion-builder' ),
						'param_name'  => 'alignment',
						'value'       => [
							'center' => esc_attr__( 'Center', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'center',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'value'       => '',
						'min'         => '0',
						'max'         => '50',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'separator_border_size' ),
						'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Separator Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the separator color.', 'fusion-builder' ),
						'param_name'  => 'sep_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'sep_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Select Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Icon Size', 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'value'       => '',
						'min'         => '1',
						'max'         => '100',
						'step'        => '1',
						'default'     => $fusion_settings->get( 'separator_icon_size' ),
						'description' => esc_attr__( 'In pixels. ', 'fusion-builder' ),
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Icon Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the color of the icon. Will fall back to the separator color, if no color is set here and in the Global Options.', 'fusion-builder' ),
						'param_name'  => 'icon_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'separator_icon_color', false, '' ),
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
						'heading'     => esc_attr__( 'Circled Icon', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have a circle in separator color around the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_circle',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
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
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Circle Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the circle around the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_circle_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'separator_circle_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'icon',
								'value'    => '',
								'operator' => '!=',
							],
							[
								'element'  => 'icon_circle',
								'value'    => 'no',
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
add_action( 'fusion_builder_before_init', 'fusion_element_separator' );
