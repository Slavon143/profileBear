<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_counters_circle' ) ) {

	if ( ! class_exists( 'FusionSC_CountersCircle' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_CountersCircle extends Fusion_Element {

			/**
			 * Parent SC arguments.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array
			 */
			protected $parent_args;

			/**
			 * Child SC arguments.
			 *
			 * @access protected
			 * @since 1.0
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
				add_filter( 'fusion_attr_counters-circle-shortcode', [ $this, 'parent_attr' ] );
				add_shortcode( 'fusion_counters_circle', [ $this, 'render_parent' ] );

				add_filter( 'fusion_attr_counter-circle-shortcode', [ $this, 'child_attr' ] );
				add_filter( 'fusion_attr_counter-circle-wrapper-shortcode', [ $this, 'child_wrapper_attr' ] );
				add_shortcode( 'fusion_counter_circle', [ $this, 'render_child' ] );

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
				];

				$child = [
					'class'         => '',
					'id'            => '',
					'countdown'     => 'no',
					'filledcolor'   => strtolower( $fusion_settings->get( 'counter_filled_color' ) ),
					'unfilledcolor' => strtolower( $fusion_settings->get( 'counter_unfilled_color' ) ),
					'scales'        => 'no',
					'size'          => '220',
					'speed'         => '1500',
					'value'         => '1',
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
					'animation_offset' => 'animation_offset',
				];

				$child = [
					'counter_filled_color'   => 'filledcolor',
					'counter_unfilled_color' => 'unfilledcolor',
				];

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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'parent' ), $args, 'fusion_counters_circle' );

				extract( $defaults );

				$this->parent_args = $defaults;

				$this->parent_args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_bottom'], 'px' );
				$this->parent_args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_left'], 'px' );
				$this->parent_args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_right'], 'px' );
				$this->parent_args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->parent_args['margin_top'], 'px' );

				$html = '<div ' . FusionBuilder::attributes( 'counters-circle-shortcode' ) . '>' . do_shortcode( $content ) . '</div>';

				$this->on_render();

				return apply_filters( 'fusion_element_counter_circles_parent_content', $html, $args );

			}

			/**
			 * Builds the parent attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function parent_attr() {

				$attr           = fusion_builder_visibility_atts(
					$this->parent_args['hide_on_mobile'],
					[
						'class' => 'fusion-counters-circle counters-circle',
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

				if ( $this->parent_args['animation_offset'] ) {
					$animations = FusionBuilder::animations( [ 'offset' => $this->parent_args['animation_offset'] ] );
					$attr       = array_merge( $attr, $animations );
				}

				return $attr;

			}

			/**
			 * Render the child shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args    Shortcode parameters.
			 * @param  string $content Content between shortcode.
			 * @return string          HTML output.
			 */
			public function render_child( $args, $content = '' ) {

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults( 'child' ), $args, 'fusion_counter_circle' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_counter_circle', $args );

				$defaults['size'] = FusionBuilder::validate_shortcode_attr_value( $defaults['size'], '' );

				extract( $defaults );

				$this->child_args = $defaults;

				$this->child_args['scales'] = false;
				if ( 'yes' === $scales ) {
					$this->child_args['scales'] = true;
				}

				$this->child_args['countdown'] = false;
				if ( 'yes' === $countdown ) {
					$this->child_args['countdown'] = true;
				}

				$output = '<div ' . FusionBuilder::attributes( 'counter-circle-shortcode' ) . '><div class="fusion-counter-circle-content-inner">' . do_shortcode( $content ) . '</div></div>';
				$output = '<div ' . FusionBuilder::attributes( 'counter-circle-wrapper-shortcode' ) . '>' . $output . '</div>';

				return apply_filters( 'fusion_element_counter_circles_child_content', $output, $args );

			}

			/**
			 * Builds the child attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function child_attr() {

				$attr = [
					'class' => 'fusion-counter-circle counter-circle counter-circle-content',
				];

				if ( $this->child_args['class'] ) {
					$attr['class'] .= ' ' . $this->child_args['class'];
				}

				if ( $this->child_args['id'] ) {
					$attr['id'] = $this->child_args['id'];
				}

				$multiplicator = $this->child_args['size'] / 220;
				$stroke_size   = 11 * $multiplicator;
				$font_size     = 50 * $multiplicator;

				$attr['data-percent'] = $this->sanitize_percentage( $this->child_args['value'] );

				if ( $this->child_args['countdown'] ) {
					$attr['data-percent-original'] = $this->sanitize_percentage( $this->child_args['value'] );
				}

				$attr['data-countdown']     = $this->child_args['countdown'];
				$attr['data-filledcolor']   = Fusion_Color::new_color( $this->child_args['filledcolor'] )->toCss( 'rgba' );
				$attr['data-unfilledcolor'] = Fusion_Color::new_color( $this->child_args['unfilledcolor'] )->toCss( 'rgba' );
				$attr['data-scale']         = $this->child_args['scales'];
				$attr['data-size']          = $this->child_args['size'];
				$attr['data-speed']         = $this->child_args['speed'];
				$attr['data-strokesize']    = $stroke_size;

				$attr['style'] = 'font-size:' . $font_size . 'px;height:' . $this->child_args['size'] . 'px;width:' . $this->child_args['size'] . 'px;';

				return $attr;

			}

			/**
			 * Builds the child-wrapper attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function child_wrapper_attr() {

				$attr = [
					'class'             => 'counter-circle-wrapper',
					'style'             => 'height:' . $this->child_args['size'] . 'px;width:' . $this->child_args['size'] . 'px;',
					'data-originalsize' => $this->child_args['size'],
				];

				return $attr;
			}

			/**
			 * Sanitize the percentage value, because this can come also from a
			 * dynamic data which can be a string or a float.
			 *
			 * @since 3.6
			 * @param int|string $percentage The value to be sanitized.
			 * @return int
			 */
			protected function sanitize_percentage( $percentage ) {
				$percentage = round( floatval( $percentage ), 0 );

				if ( 0 > $percentage ) {
					$percentage = 0;
				}

				if ( 100 < $percentage ) {
					$percentage = 100;
				}

				return $percentage;
			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Counter Circle settings.
			 */
			public function add_options() {

				return [
					'counters_circle_shortcode_section' => [
						'label'       => esc_html__( 'Counter Circles', 'fusion-builder' ),
						'description' => '',
						'id'          => 'counters_circle_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-clock',
						'fields'      => [
							'counter_filled_color'   => [
								'label'       => esc_html__( 'Counter Circles Filled Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the filled circle.', 'fusion-builder' ),
								'id'          => 'counter_filled_color',
								'default'     => 'var(--awb-color5)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
							'counter_unfilled_color' => [
								'label'       => esc_html__( 'Counter Circles Unfilled Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the unfilled circle.', 'fusion-builder' ),
								'id'          => 'counter_unfilled_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {
				global $wp_version, $content_media_query, $six_fourty_media_query, $three_twenty_six_fourty_media_query, $ipad_portrait_media_query, $content_min_media_query;

				$css[ $six_fourty_media_query ]['.fusion-counters-circle .counter-circle-wrapper']['display']      = 'block';
				$css[ $six_fourty_media_query ]['.fusion-counters-circle .counter-circle-wrapper']['margin-right'] = 'auto';
				$css[ $six_fourty_media_query ]['.fusion-counters-circle .counter-circle-wrapper']['margin-left']  = 'auto';

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

				Fusion_Dynamic_JS::enqueue_script(
					'jquery-easy-pie-chart',
					FusionBuilder::$js_folder_url . '/library/jquery.easyPieChart.js',
					FusionBuilder::$js_folder_path . '/library/jquery.easyPieChart.js',
					[ 'jquery' ],
					'2.1.7',
					true
				);

				Fusion_Dynamic_JS::enqueue_script(
					'fusion-counters-circle',
					FusionBuilder::$js_folder_url . '/general/fusion-counters-circle.js',
					FusionBuilder::$js_folder_path . '/general/fusion-counters-circle.js',
					[ 'jquery', 'fusion-animations', 'jquery-count-to', 'jquery-easy-pie-chart' ],
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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/counters-circle.min.css' );
			}
		}
	}

	new FusionSC_CountersCircle();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_counters_circle() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_CountersCircle',
			[
				'name'          => esc_attr__( 'Counter Circles', 'fusion-builder' ),
				'shortcode'     => 'fusion_counters_circle',
				'multi'         => 'multi_element_parent',
				'element_child' => 'fusion_counter_circle',
				'sortable'      => false,
				'icon'          => 'fusiona-clock',
				'help_url'      => 'https://theme-fusion.com/documentation/avada/elements/counter-circles-element/',
				'params'        => [
					[
						'type'        => 'tinymce',
						'heading'     => esc_attr__( 'Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Enter some content for this counter circle.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[fusion_counter_circle value="50" filledcolor="" unfilledcolor="" size="220" scales="no" countdown="no" speed="1500"]' . esc_attr__( 'Counter Content', 'fusion-builder' ) . '[/fusion_counter_circle]',
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
add_action( 'fusion_builder_before_init', 'fusion_element_counters_circle' );

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_counter_circle() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_CountersCircle',
			[
				'name'              => esc_attr__( 'Counter Circle', 'fusion-builder' ),
				'description'       => esc_attr__( 'Enter some content for this block.', 'fusion-builder' ),
				'shortcode'         => 'fusion_counter_circle',
				'hide_from_builder' => true,
				'params'            => [
					[
						'type'         => 'range',
						'heading'      => esc_attr__( 'Filled Area Percentage', 'fusion-builder' ),
						'description'  => esc_attr__( 'From 1% to 100%.', 'fusion-builder' ),
						'dynamic_data' => true,
						'param_name'   => 'value',
						'value'        => '50',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Filled Color', 'fusion-builder' ),
						'param_name'  => 'filledcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_filled_color' ),
						'description' => esc_attr__( 'Controls the color of the filled in area. ', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Unfilled Color', 'fusion-builder' ),
						'param_name'  => 'unfilledcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'counter_unfilled_color' ),
						'description' => esc_attr__( 'Controls the color of the unfilled in area. ', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Size of the Counter', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert size of the counter in px. ex: 220.', 'fusion-builder' ),
						'param_name'  => 'size',
						'value'       => '200',
						'min'         => '1',
						'max'         => '1000',
						'step'        => '1',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Scales', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to show a scale around circles.', 'fusion-builder' ),
						'param_name'  => 'scales',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Countdown', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to let the circle filling move counter clockwise.', 'fusion-builder' ),
						'param_name'  => 'countdown',
						'value'       => [
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => 'no',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Animation Speed', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert animation speed in milliseconds.', 'fusion-builder' ),
						'param_name'  => 'speed',
						'value'       => '1500',
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Counter Circle Text', 'fusion-builder' ),
						'description'  => esc_attr__( 'Insert text for counter circle box, keep it short.', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => esc_attr__( 'Counter Content', 'fusion-builder' ),
						'placeholder'  => true,
						'dynamic_data' => true,
					],
				],
			],
			'child'
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_counter_circle' );
