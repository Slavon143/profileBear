<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_counters_box' ) ) {

	if ( ! class_exists( 'FusionSC_CountersBox' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_CountersBox extends Fusion_Element {

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @since 1.0.0
			 * @access protected
			 * @var array
			 */
			protected $child_args;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_filter( 'fusion_attr_counters-box-shortcode', [ $this, 'parent_attr' ] );
				add_filter( 'fusion_attr_counter-box-container', [ $this, 'container_attr' ] );
				add_shortcode( 'fusion_counters_box', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_counter-box-shortcode', [ $this, 'child_attr' ] );
				add_filter( 'fusion_attr_counter-box-shortcode-icon', [ $this, 'icon_attr' ] );
				add_filter( 'fusion_attr_counter-box-shortcode-unit', [ $this, 'unit_attr' ] );
				add_filter( 'fusion_attr_counter-box-shortcode-counter', [ $this, 'counter_attr' ] );
				add_filter( 'fusion_attr_counter-box-shortcode-counter-container', [ $this, 'counter_container_attr' ] );
				add_filter( 'fusion_attr_counter-box-shortcode-content', [ $this, 'content_attr' ] );
				add_shortcode( 'fusion_counter_box', [ $this, 'render_child' ] );

			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_defaults( $context = '' ) {

				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'margin_top'       => '',
					'margin_right'     => '',
					'margin_bottom'    => '',
					'margin_left'      => '',
					'hide_on_mobile'   => fusion_builder_default_visibility( 'string' ),
					'class'            => '',
					'id'               => '',
					'animation_offset' => $fusion_settings->get( 'animation_offset' ),
					'body_color'       => strtolower( $fusion_settings->get( 'counter_box_body_color' ) ),
					'body_size'        => fusion_library()->sanitize->size( $fusion_settings->get( 'counter_box_body_size' ) ),
					'border_color'     => strtolower( $fusion_settings->get( 'counter_box_border_color' ) ),
					'color'            => strtolower( $fusion_settings->get( 'counter_box_color' ) ),
					'columns'          => '',
					'icon'             => '',
					'icon_size'        => fusion_library()->sanitize->size( $fusion_settings->get( 'counter_box_icon_size' ) ),
					'icon_top'         => strtolower( $fusion_settings->get( 'counter_box_icon_top' ) ),
					'title_size'       => fusion_library()->sanitize->size( $fusion_settings->get( 'counter_box_title_size' ) ),
				];
				$child  = [
					'class'     => '',
					'id'        => '',
					'decimals'  => '0',
					'delimiter' => '',
					'direction' => 'up',
					'icon'      => '',
					'unit'      => '',
					'unit_pos'  => 'suffix',
					'value'     => '20',
				];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params( $context = '' ) {

				$parent = [
					'animation_offset'         => 'animation_offset',
					'counter_box_body_color'   => 'body_color',
					'counter_box_body_size'    => 'body_size',
					'counter_box_border_color' => 'border_color',
					'counter_box_color'        => 'color',
					'counter_box_icon_size'    => 'icon_size',
					'counter_box_icon_top'     => 'icon_top',
					'counter_box_title_size'   => 'title_size',
					'counter_box_speed'        => 'counter_speed',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
			}

			/**
			 * Gets the default values.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @param string $context Whether we want parent or child.
			 *                        Returns array( parent, child ) if empty.
			 * @return array
			 */
			public static function get_element_extras( $context = '' ) {

				$fusion_settings = awb_get_fusion_settings();

				$parent = [
					'counter_speed' => $fusion_settings->get( 'counter_box_speed' ),
				];
				$child  = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				}
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @param string $context Whether we want parent or child.
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_extras( $context = '' ) {

				$parent = [
					'counter_box_speed' => 'counter_speed',
				];

				$child = [];

				if ( 'parent' === $context ) {
					return $parent;
				} elseif ( 'child' === $context ) {
					return $child;
				} else {
					return [
						'parent' => $parent,
						'child'  => $child,
					];
				}
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
			public function render_parent( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_counters_box' );

				$defaults['title_size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['title_size'], '' );
				$defaults['icon_size']  = FusionBuilder::validate_shortcode_attr_value( $defaults['icon_size'], '' );
				$defaults['body_size']  = FusionBuilder::validate_shortcode_attr_value( $defaults['body_size'], '' );

				extract( $defaults );

				$this->parent_args = $defaults;

				$this->parent_args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_bottom'], 'px' );
				$this->parent_args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_left'], 'px' );
				$this->parent_args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_right'], 'px' );
				$this->parent_args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_top'], 'px' );

				$this->parent_args['columns'] = min( 6, $this->parent_args['columns'] );

				$this->set_num_of_columns( $content );

				$html = '<div ' . FusionBuilder::attributes( 'counters-box-shortcode' ) . '>' . do_shortcode( $content ) . '</div>';

				$html = fusion_maybe_add_clearfix( $html );

				$this->on_render();

				return apply_filters( 'fusion_element_counter_boxes_parent_content', $html, $args );

			}

			/**
			 * Builds the parent attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function parent_attr() {

				$attr = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-counters-box counters-box row fusion-clearfix fusion-columns-' . $this->parent_args['columns'],
						'style' => '',
					]
				);

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->parent_args );

				if ( $this->parent_args['class'] ) {
					$attr['class'] .= ' ' . $this->parent_args['class'];
				}

				if ( $this->parent_args['id'] ) {
					$attr['id'] = $this->parent_args['id'];
				}

				return $attr;

			}

			/**
			 * Builds the container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function container_attr() {
				return [
					'class' => 'counter-box-container',
					'style' => 'border: 1px solid ' . $this->parent_args['border_color'] . ';',
				];
			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args     Shortcode parameters.
			 * @param  string $content  Content between shortcode.
			 * @return string           HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults         = self::get_element_defaults( 'child' );
				$defaults['icon'] = $this->parent_args['icon'];
				$defaults         = FusionBuilder::set_shortcode_defaults( $defaults, $args, 'fusion_counter_box' );
				$content          = apply_filters( 'fusion_shortcode_content', $content, 'fusion_counter_box', $args );

				extract( $defaults );

				$this->child_args = $defaults;

				$value                     = str_replace( ',', '.', $value );
				$this->child_args['value'] = $value;

				$float = explode( '.', $value );
				if ( isset( $float[1] ) ) {
					$this->child_args['decimals'] = strlen( $float[1] );
				}

				$unit_output = '';
				if ( $unit ) {
					$unit_output = '<span ' . FusionBuilder::attributes( 'counter-box-shortcode-unit' ) . '>' . $unit . '</span>';
				}

				$init_value = $this->child_args['value'];
				if ( 'up' === $direction ) {
					$init_value = 0;
				}

				$counter = '<span ' . FusionBuilder::attributes( 'counter-box-shortcode-counter' ) . '>' . $init_value . '</span>';

				$icon_output = '';
				if ( $icon ) {
					$icon_output = '<i ' . FusionBuilder::attributes( 'counter-box-shortcode-icon' ) . '></i>';
				}

				if ( 'prefix' === $unit_pos ) {
					$counter = $icon_output . $unit_output . $counter;
				} else {
					$counter = $icon_output . $counter . $unit_output;
				}

				$counter_wrapper = '<div ' . FusionBuilder::attributes( 'counter-box-shortcode-counter-container' ) . '>' . $counter . '</div>';
				$content_output  = '<div ' . FusionBuilder::attributes( 'counter-box-shortcode-content' ) . '>' . do_shortcode( $content ) . '</div>';

				$html = '<div ' . FusionBuilder::attributes( 'counter-box-shortcode' ) . '><div ' . FusionBuilder::attributes( 'counter-box-container' ) . '>' . $counter_wrapper . $content_output . '</div></div>';

				return apply_filters( 'fusion_element_counter_boxes_child_content', $html, $args );

			}

			/**
			 * Builds the child attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function child_attr() {

				$attr    = [];
				$columns = 1;
				if ( $this->parent_args['columns'] && ! empty( $this->parent_args['columns'] ) ) {
					$columns = 12 / $this->parent_args['columns'];
				}

				$attr['class'] = 'fusion-counter-box fusion-column col-counter-box counter-box-wrapper col-lg-' . $columns . ' col-md-' . $columns . ' col-sm-' . $columns;

				if ( '5' === $this->parent_args['columns'] || 5 === $this->parent_args['columns'] ) {
					$attr['class'] = 'fusion-counter-box fusion-column col-counter-box counter-box-wrapper col-lg-2 col-md-2 col-sm-2';
				}

				if ( 'yes' === $this->parent_args['icon_top'] ) {
					$attr['class'] .= ' fusion-counter-box-icon-top';
				}

				if ( $this->child_args['class'] ) {
					$attr['class'] .= ' ' . $this->child_args['class'];
				}

				if ( $this->child_args['id'] ) {
					$attr['id'] = $this->child_args['id'];
				}

				if ( $this->parent_args['animation_offset'] ) {
					$animations = FusionBuilder::animations( [ 'offset' => $this->parent_args['animation_offset'] ] );

					$attr = array_merge( $attr, $animations );
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
					'class'       => 'counter-box-icon fontawesome-icon ' . fusion_font_awesome_name_handler( $this->child_args['icon'] ),
					'style'       => 'font-size:' . $this->parent_args['icon_size'] . 'px;',
					'aria-hidden' => 'true',
				];

				return $attr;
			}

			/**
			 * Builds the unit attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function unit_attr() {
				return [
					'class' => 'unit',
				];
			}

			/**
			 * Builds the counter attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function counter_attr() {
				return [
					'class'          => 'display-counter',
					'data-value'     => $this->child_args['value'],
					'data-delimiter' => $this->child_args['delimiter'],
					'data-direction' => $this->child_args['direction'],
					'data-decimals'  => $this->child_args['decimals'],
				];
			}

			/**
			 * Builds the container attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function counter_container_attr() {
				return [
					'class' => 'content-box-percentage content-box-counter',
					'style' => 'color:' . $this->parent_args['color'] . ';font-size:' . $this->parent_args['title_size'] . 'px;line-height:normal;',
				];
			}

			/**
			 * Builds the content attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function content_attr() {
				return [
					'class' => 'counter-box-content',
					'style' => 'color:' . $this->parent_args['body_color'] . ';font-size:' . $this->parent_args['body_size'] . 'px;',
				];
			}

			/**
			 * Calculate the number of columns automatically
			 *
			 * @access public
			 * @since 1.0
			 * @param  string $content Content to be parsed.
			 */
			public function set_num_of_columns( $content ) {
				if ( ! $this->parent_args['columns'] ) {
					preg_match_all( '/(\[fusion_counter_box (.*?)\](.*?)\[\/fusion_counter_box\])/s', $content, $matches );
					if ( is_array( $matches ) && ! empty( $matches ) ) {
						$this->parent_args['columns'] = min( 6, count( $matches[0] ) );
					} else {
						$this->parent_args['columns'] = 1;
					}
				} elseif ( $this->parent_args['columns'] > 6 ) {
					$this->parent_args['columns'] = 6;
				}
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

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-counters-box' ], '.fusion-counters-box' );

				$elements = $dynamic_css_helpers->map_selector( $main_elements, ' .content-box-percentage' );
				$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['color'] = fusion_library()->sanitize->color( $fusion_settings->get( 'counter_filled_color' ) );

				$css[ $content_media_query ]['.fusion-counters-box .fusion-counter-box']['margin-bottom']                  = '20px';
				$css[ $content_media_query ]['.fusion-counters-box .fusion-counter-box']['padding']                        = '0 15px';
				$css[ $content_media_query ]['.fusion-counters-box .fusion-counter-box:last-child']['margin-bottom']       = '0';
				$css[ $ipad_portrait_media_query ]['.fusion-counters-box .fusion-counter-box']['margin-bottom']            = '20px';
				$css[ $ipad_portrait_media_query ]['.fusion-counters-box .fusion-counter-box']['padding']                  = '0 15px';
				$css[ $ipad_portrait_media_query ]['.fusion-counters-box .fusion-counter-box:last-child']['margin-bottom'] = '0';

				return $css;

			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				$fusion_settings = awb_get_fusion_settings();

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-counters-box',
					FusionBuilder::$js_folder_url . '/general/fusion-counters-box.js',
					FusionBuilder::$js_folder_path . '/general/fusion-counters-box.js',
					[ 'jquery', 'fusion-animations', 'jquery-count-to' ],
					'1',
					true
				);
				Fusion_Dynamic_JS::localize_script(
					'fusion-counters-box',
					'fusionCountersBox',
					[
						'counter_box_speed' => intval( $fusion_settings->get( 'counter_box_speed' ) ),
					]
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/counters-box.min.css' );
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Counter Boxes settings.
			 */
			public function add_options() {

				return [
					'counters_box_shortcode_section' => [
						'label'       => esc_html__( 'Counter Boxes', 'fusion-builder' ),
						'description' => '',
						'id'          => 'counters_box_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-browser',
						'fields'      => [
							'counter_box_speed'        => [
								'label'       => esc_html__( 'Counter Boxes Speed', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the speed of the counter boxes elements. ex: 1000 = 1 second.', 'fusion-builder' ),
								'id'          => 'counter_box_speed',
								'default'     => '1000',
								'type'        => 'slider',
								'output'      => [
									[
										'element'     => 'helperElement',
										'property'    => 'dummy',
										'js_callback' => [
											'fusionGlobalScriptSet',
											[
												'globalVar' => 'fusionCountersBox',
												'id'      => 'counter_box_speed',
												'trigger' => [ '' ],
											],
										],
										'sanitize_callback' => '__return_empty_string',
									],
								],
								'choices'     => [
									'min'  => '500',
									'max'  => '20000',
									'step' => '250',
								],
							],
							'counter_box_color'        => [
								'label'       => esc_html__( 'Counter Boxes Value Font Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the counter values and icons.', 'fusion-builder' ),
								'id'          => 'counter_box_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'counter_box_title_size'   => [
								'label'       => esc_html__( 'Counter Boxes Value Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of the counter value.', 'fusion-builder' ),
								'id'          => 'counter_box_title_size',
								'default'     => '50',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '200',
									'step' => '1',
								],
							],
							'counter_box_icon_size'    => [
								'label'       => esc_html__( 'Counter Boxes Icon Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of the icon.', 'fusion-builder' ),
								'id'          => 'counter_box_icon_size',
								'default'     => '50',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '500',
									'step' => '1',
								],
							],
							'counter_box_body_color'   => [
								'label'       => esc_html__( 'Counter Boxes Body Font Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the counter boxes body text.', 'fusion-builder' ),
								'id'          => 'counter_box_body_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'counter_box_body_size'    => [
								'label'       => esc_html__( 'Counter Boxes Body Font Size', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the size of the counter boxes body text.', 'fusion-builder' ),
								'id'          => 'counter_box_body_size',
								'default'     => '14',
								'type'        => 'slider',
								'transport'   => 'postMessage',
								'choices'     => [
									'min'  => '1',
									'max'  => '200',
									'step' => '1',
								],
							],
							'counter_box_border_color' => [
								'label'       => esc_html__( 'Counter Boxes Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the counter boxes border.', 'fusion-builder' ),
								'id'          => 'counter_box_border_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'counter_box_icon_top'     => [
								'label'       => esc_html__( 'Counter Boxes Icon On Top', 'fusion-builder' ),
								'description' => esc_html__( 'Turn on to display the icon on top of the counter value.', 'fusion-builder' ),
								'id'          => 'counter_box_icon_top',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'On', 'fusion-builder' ),
									'no'  => esc_html__( 'Off', 'fusion-builder' ),
								],
							],
						],
					],
				];
			}
		}
	}

	new FusionSC_CountersBox();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_counters_box() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_CountersBox',
			[
				'name'          => esc_attr__( 'Counter Boxes', 'fusion-builder' ),
				'shortcode'     => 'fusion_counters_box',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_counter_box',
				'icon'          => 'fusiona-browser',
				'preview'       => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-counter-box-preview.php',
				'preview_id'    => 'fusion-builder-block-module-counter-box-preview-template',
				'child_ui'      => true,
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/counter-boxes-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this counter box.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_counter_box value="20" delimiter="" unit="" unit_pos="suffix" icon="" direction="up"]' . esc_attr__( 'Your Content Goes Here', 'fusion-builder' ) . '[/fusion_counter_box]',
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Number of Columns', 'fusion-builder' ),
						'description' => esc_attr__( 'Set the number of columns per row.', 'fusion-builder' ),
						'param_name'  => 'columns',
						'value'       => '4',
						'min'         => '1',
						'max'         => '6',
						'step'        => '1',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Box Value Font Color', 'fusion-builder' ),
						'description' => esc_attr__( "Controls the color of the counter 'value' and icon.", 'fusion-builder' ),
						'param_name'  => 'color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_box_color' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Counter Box Value Font Size', 'fusion-builder' ),
						'description' => esc_attr__( "Controls the size of the counter 'value'. Enter the font size without 'px' ex: 50.", 'fusion-builder' ),
						'param_name'  => 'title_size',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_box_title_size' ),
						'min'         => '1',
						'max'         => '200',
						'step'        => '1',
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Global setting for all counter boxes, this can be overridden individually. Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Counter Box Icon Size', 'fusion-builder' ),
						'description' => esc_attr__( "Controls the size of the icon. Enter the font size without 'px'. Default is 50.", 'fusion-builder' ),
						'param_name'  => 'icon_size',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_box_icon_size' ),
						'min'         => '1',
						'max'         => '500',
						'step'        => '1',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Counter Box Icon Top', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the position of the icon.', 'fusion-builder' ),
						'param_name'  => 'icon_top',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Box Body Font Color', 'fusion-builder' ),
						'param_name'  => 'body_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_box_body_color' ),
						'description' => esc_attr__( 'Controls the color of the counter body text.', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Counter Box Body Font Size', 'fusion-builder' ),
						'description' => esc_attr__( "Controls the size of the counter body text. Enter the font size without 'px' ex: 13.", 'fusion-builder' ),
						'param_name'  => 'body_size',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_box_body_size' ),
						'min'         => '1',
						'max'         => '200',
						'step'        => '1',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Counter Box Border Color', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'description' => esc_attr__( 'Controls the color of the border.', 'fusion-builder' ),
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_box_border_color' ),
					],
					[
						'type'        => 'select',
						'heading'     => esc_attr__( 'Offset of Animation', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls when the animation should start.', 'fusion-builder' ),
						'param_name'  => 'animation_offset',
						'value'       => [
							''                => esc_attr__( 'Default', 'fusion-builder' ),
							'top-into-view'   => esc_attr__( 'Top of element hits bottom of viewport', 'fusion-builder' ),
							'top-mid-of-view' => esc_attr__( 'Top of element hits middle of viewport', 'fusion-builder' ),
							'bottom-in-view'  => esc_attr__( 'Bottom of element enters viewport', 'fusion-builder' ),
						],
						'default'     => '',
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
				],
			],
			'parent'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_counters_box' );

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_counter_box() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_CountersBox',
			[
				'name'                     => esc_attr__( 'Counter Box', 'fusion-builder' ),
				'description'              => esc_attr__( 'Enter some content for this block.', 'fusion-builder' ),
				'shortcode'                => 'fusion_counter_box',
				'hide_from_builder'        => true,
				'inline_editor'            => true,
				'inline_editor_shortcodes' => false,
				'params'                   => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Counter Value', 'fusion-builder' ),
						'description' => esc_attr__( 'The number to which the counter will animate. Decimal numbers are supported by using the "." (period) delimiter.', 'fusion-builder' ),
						'param_name'  => 'value',
						'value'       => '20',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Delimiter Digit', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert a delimiter digit for better readability. ex: ,', 'fusion-builder' ),
						'param_name'  => 'delimiter',
						'value'       => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Counter Box Unit', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert a unit for the counter. ex %', 'fusion-builder' ),
						'param_name'  => 'unit',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Unit Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the positioning of the unit.', 'fusion-builder' ),
						'param_name'  => 'unit_pos',
						'value'       => [
							'suffix' => esc_attr__( 'After Counter', 'fusion-builder' ),
							'prefix' => esc_attr__( 'Before Counter', 'fusion-builder' ),
						],
						'default'     => 'suffix',
					],
					[
						'type'        => 'iconpicker',
						'heading'     => esc_attr__( 'Icon', 'fusion-builder' ),
						'param_name'  => 'icon',
						'value'       => '',
						'description' => esc_attr__( 'Click an icon to select, click again to deselect.', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Counter Direction', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to count up or down.', 'fusion-builder' ),
						'param_name'  => 'direction',
						'value'       => [
							'up'   => esc_attr__( 'Count Up', 'fusion-builder' ),
							'down' => esc_attr__( 'Count Down', 'fusion-builder' ),
						],
						'default'     => 'up',
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Counter Box Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert text for counter box.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_counter_box' );
