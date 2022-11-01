<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.3
 */

if ( fusion_is_element_enabled( 'fusion_scroll_progress' ) && ! class_exists( 'FusionSC_ScrollProgress' ) ) {
	/**
	 * Shortcode class.
	 *
	 * @since 3.3
	 */
	class FusionSC_ScrollProgress extends Fusion_Element {

		/**
		 * An array of the shortcode arguments.
		 *
		 * @access protected
		 * @since 3.3
		 * @var array
		 */
		protected $args;

		/**
		 * The internal element counter.
		 *
		 * @access private
		 * @since 3.3
		 * @var int
		 */
		private $element_counter = 1;

		/**
		 * Constructor.
		 *
		 * @access public
		 * @since 3.3
		 */
		public function __construct() {
			parent::__construct();
			add_filter( 'fusion_attr_scroll-progress-shortcode', [ $this, 'attr' ] );

			add_shortcode( 'fusion_scroll_progress', [ $this, 'render' ] );

		}

		/**
		 * Gets the default values.
		 *
		 * @static
		 * @access public
		 * @since 3.3
		 * @return array
		 */
		public static function get_element_defaults() {
			$fusion_settings = awb_get_fusion_settings();
			$border_radius   = Fusion_Builder_Border_Radius_Helper::get_border_radius_array_with_fallback_value( $fusion_settings->get( 'scroll_progress_border_radius' ) );

			return [
				'animation_direction'        => 'down',
				'animation_offset'           => $fusion_settings->get( 'animation_offset' ),
				'animation_speed'            => '',
				'animation_type'             => '',
				'background_color'           => $fusion_settings->get( 'scroll_progress_background_color' ),
				'border_size'                => $fusion_settings->get( 'scroll_progress_border_size' ),
				'border_color'               => $fusion_settings->get( 'scroll_progress_border_color' ),
				'border_radius_top_left'     => $border_radius['top_left'],
				'border_radius_top_right'    => $border_radius['top_right'],
				'border_radius_bottom_right' => $border_radius['bottom_right'],
				'border_radius_bottom_left'  => $border_radius['bottom_left'],
				'class'                      => '',
				'height'                     => $fusion_settings->get( 'scroll_progress_height' ),
				'hide_on_mobile'             => fusion_builder_default_visibility( 'string' ),
				'id'                         => '',
				'position'                   => $fusion_settings->get( 'scroll_progress_position' ),
				'progress_color'             => $fusion_settings->get( 'scroll_progress_progress_color' ),
				'sticky_display'             => '',
				'z_index'                    => '',
			];
		}

		/**
		 * Maps settings to param variables.
		 *
		 * @static
		 * @access public
		 * @since 3.3
		 * @return array
		 */
		public static function settings_to_params() {
			return [
				'scroll_progress_background_color'         => 'background_color',
				'scroll_progress_progress_color'           => 'progress_color',
				'scroll_progress_border_size'              => 'border_size',
				'scroll_progress_border_color'             => 'border_color',
				'scroll_progress_border_radius[top_left]'  => 'border_radius_top_left',
				'scroll_progress_border_radius[top_right]' => 'border_radius_top_right',
				'scroll_progress_border_radius[bottom_right]' => 'border_radius_bottom_right',
				'scroll_progress_border_radius[bottom_left]' => 'border_radius_bottom_left',
				'scroll_progress_position'                 => 'position',
				'scroll_progress_progress_height'          => 'height',
			];
		}

		/**
		 * Render the shortcode
		 *
		 * @access public
		 * @since 3.3
		 * @param  array  $args    Shortcode parameters.
		 * @param  string $content Content between shortcode.
		 * @return string          HTML output.
		 */
		public function render( $args, $content = '' ) {

			$this->args = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_scroll_progress' );

			$border_radius               = $this->args['border_radius_top_left'] . ' ' . $this->args['border_radius_top_right'] . ' ' . $this->args['border_radius_bottom_right'] . ' ' . $this->args['border_radius_bottom_left'];
			$this->args['border_radius'] = ( '0px 0px 0px 0px' === $border_radius ) ? '' : $border_radius;

			$html = '<progress ' . FusionBuilder::attributes( 'scroll-progress-shortcode' ) . '></progress>';

			$html .= $this->get_styles(); // Get custom styles.

			$this->element_counter++;

			$this->on_render();

			return apply_filters( 'fusion_element_scroll_progress_content', $html, $args );
		}

		/**
		 * Builds the attributes array.
		 *
		 * @access public
		 * @since 3.3
		 * @return array
		 */
		public function attr() {

			$attr = [
				'class' => 'fusion-scroll-progress fusion-scroll-progress-' . $this->element_counter,
				'max'   => '100',
				'value' => '',
			];

			if ( 'flow' !== $this->args['position'] ) {
				$attr['class'] .= ' fusion-fixed-' . $this->args['position'];
			}

			$attr = fusion_builder_visibility_atts( $this->args['hide_on_mobile'], $attr );

			$attr['class'] .= Fusion_Builder_Sticky_Visibility_Helper::get_sticky_class( $this->args['sticky_display'] );

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
			 * Get the styles.
			 *
			 * @access protected
			 * @since 3.3
			 * @return string
			 */
		protected function get_styles() {
			$this->base_selector = '.fusion-scroll-progress-' . $this->element_counter;
			$this->dynamic_css   = [];

			if ( $this->args['z_index'] && 'flow' !== $this->args['position'] ) {
				$this->add_css_property( $this->base_selector, 'z-index', $this->args['z_index'], true );
			}

			if ( $this->args['height'] ) {
				$this->add_css_property( $this->base_selector, 'height', $this->args['height'] );
				$this->add_css_property( $this->base_selector . '::-moz-progress-bar', 'height', $this->args['height'] );
				$this->add_css_property( $this->base_selector . '::-webkit-progress-bar', 'height', $this->args['height'] );
				$this->add_css_property( $this->base_selector . '::-webkit-progress-value', 'height', $this->args['height'] );
			}

			if ( $this->args['background_color'] ) {
				$this->add_css_property( $this->base_selector, 'background-color', $this->args['background_color'] );
				$this->add_css_property( $this->base_selector . '::-webkit-progress-bar', 'background-color', $this->args['background_color'] );
			}

			if ( $this->args['progress_color'] ) {
				$this->add_css_property( $this->base_selector . '::-moz-progress-bar', 'background-color', $this->args['progress_color'] );
				$this->add_css_property( $this->base_selector . '::-webkit-progress-value', 'background-color', $this->args['progress_color'] );

			}

			if ( $this->args['border_size'] && $this->args['border_color'] ) {
				$this->add_css_property( $this->base_selector . '::-webkit-progress-value', 'border', fusion_library()->sanitize->get_value_with_unit( $this->args['border_size'] ) . ' solid ' . $this->args['border_color'] );
				$this->add_css_property( $this->base_selector . '::-moz-progress-bar', 'border', fusion_library()->sanitize->get_value_with_unit( $this->args['border_size'] ) . ' solid ' . $this->args['border_color'] );
			}

			if ( $this->args['border_radius'] ) {
				$this->add_css_property( $this->base_selector, 'border-radius', $this->args['border_radius'] );
				$this->add_css_property( $this->base_selector . '::-moz-progress-bar', 'border-radius', $this->args['border_radius'] );
				$this->add_css_property( $this->base_selector . '::-webkit-progress-bar', 'border-radius', $this->args['border_radius'] );
				$this->add_css_property( $this->base_selector . '::-webkit-progress-value', 'border-radius', $this->args['border_radius'] );
			}

			$css = $this->parse_css();

			return $css ? '<style>' . $css . '</style>' : '';
		}


		/**
		 * Sets the necessary scripts.
		 *
		 * @access protected
		 * @since 3.3
		 * @return void
		 */
		protected function on_first_render() {
			Fusion_Dynamic_JS::enqueue_script(
				'fusion-scroll-progress',
				FusionBuilder::$js_folder_url . '/general/fusion-scroll-progress.js',
				FusionBuilder::$js_folder_path . '/general/fusion-scroll-progress.js',
				[ 'jquery', 'fusion-animations' ],
				'1',
				true
			);
		}

		/**
		 * Load base CSS.
		 *
		 * @access public
		 * @since 3.3
		 * @return void
		 */
		public function add_css_files() {
			FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/scroll-progress.min.css' );
		}

		/**
		 * Adds settings to element options panel.
		 *
		 * @access public
		 * @since 2.1
		 * @return array $sections Blog settings.
		 */
		public function add_options() {
			return [
				'scroll_progress_shortcode_section' => [
					'label'       => esc_attr__( 'Scroll Progress', 'fusion-builder' ),
					'description' => '',
					'id'          => 'scroll_progress_shortcode_section',
					'default'     => '',
					'icon'        => 'fusiona-scroll-progress',
					'type'        => 'accordion',
					'fields'      => [
						'scroll_progress_position'         => [
							'label'       => esc_attr__( 'Progress Bar Position', 'fusion-builder' ),
							'description' => esc_attr__( 'Select the position of the progress bar..', 'fusion-builder' ),
							'id'          => 'scroll_progress_position',
							'type'        => 'radio-buttonset',
							'default'     => 'flow',
							'choices'     => [
								'flow'   => esc_attr__( 'Content Flow', 'fusion-builder' ),
								'top'    => esc_attr__( 'Fixed to Top', 'fusion-builder' ),
								'bottom' => esc_attr__( 'Fixed to Bottom', 'fusion-builder' ),
							],
							'transport'   => 'postMessage',
						],
						'scroll_progress_height'           => [
							'label'       => esc_html__( 'Progress Bar Height', 'fusion-builder' ),
							'description' => esc_html__( 'Insert a height for the progress bar.', 'fusion-builder' ),
							'id'          => 'scroll_progress_height',
							'default'     => '10px',
							'type'        => 'dimension',
							'transport'   => 'postMessage',
						],
						'scroll_progress_background_color' => [
							'label'       => esc_attr__( 'Background Color', 'fusion-builder' ),
							'description' => esc_attr__( 'Controls the background color of the progress bar.', 'fusion-builder' ),
							'id'          => 'scroll_progress_background_color',
							'default'     => 'var(--awb-color2)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'scroll_progress_progress_color'   => [
							'label'       => esc_html__( 'Progress Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the color of the progress bar.', 'fusion-builder' ),
							'id'          => 'scroll_progress_progress_color',
							'default'     => 'var(--awb-color4)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'scroll_progress_border_size'      => [
							'label'       => esc_html__( 'Progress Bar Border Size', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the border size of the progress bar.', 'fusion-builder' ),
							'id'          => 'scroll_progress_border_size',
							'default'     => '0',
							'type'        => 'slider',
							'transport'   => 'postMessage',
							'choices'     => [
								'min'  => '0',
								'max'  => '20',
								'step' => '1',
							],
						],
						'scroll_progress_border_color'     => [
							'label'       => esc_html__( 'Progress Bar Border Color', 'fusion-builder' ),
							'description' => esc_html__( 'Controls the border color of the progress bar.', 'fusion-builder' ),
							'id'          => 'scroll_progress_border_color',
							'default'     => 'var(--awb-color1)',
							'type'        => 'color-alpha',
							'transport'   => 'postMessage',
						],
						'scroll_progress_border_radius'    => [
							'label'       => esc_attr__( 'Border Radius', 'fusion-builder' ),
							'description' => esc_html__( 'Set the border radius of the progress bar.', 'fusion-builder' ),
							'id'          => 'scroll_progress_border_radius',
							'type'        => 'border_radius',
							'choices'     => [
								'top_left'     => true,
								'top_right'    => true,
								'bottom_right' => true,
								'bottom_left'  => true,
								'units'        => [ 'px', '%', 'em' ],
							],
							'default'     => [
								'top_left'     => '0px',
								'top_right'    => '0px',
								'bottom_right' => '0px',
								'bottom_left'  => '0px',
							],
							'transport'   => 'postMessage',
						],
					],
				],
			];
		}
	}

	new FusionSC_ScrollProgress();
}


/**
 * Map shortcode to Avada Builder
 *
 * @since 3.3
 */
function fusion_element_scroll_progress() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_ScrollProgress',
			[
				'name'                     => esc_attr__( 'Scroll Progress', 'fusion-builder' ),
				'shortcode'                => 'fusion_scroll_progress',
				'icon'                     => 'fusiona-scroll-progress',
				'allow_generator'          => false,
				'inline_editor'            => false,
				'inline_editor_shortcodes' => false,
				'help_url'                 => 'https://theme-fusion.com/documentation/avada/elements/scroll-progress-element/',
				'params'                   => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Progress Bar Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Select the position of the progress bar.', 'fusion-builder' ),
						'param_name'  => 'position',
						'value'       => [
							''       => esc_attr__( 'Default', 'fusion-builder' ),
							'flow'   => esc_attr__( 'Content Flow', 'fusion-builder' ),
							'top'    => esc_attr__( 'Fixed to Top', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Fixed to Bottom', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Z-Index', 'fusion-builder' ),
						'description' => esc_attr__( 'Value for the progress bar\'s z-index CSS property. For fixed positions, the default value is set to 99998. If you see page contents above the progress bar, you can also set a higher z-index on the wrapping container to fix it.', 'fusion-builder' ),
						'param_name'  => 'z_index',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'position',
								'value'    => 'flow',
								'operator' => '!=',
							],
						],
					],
					[
						'type'             => 'dimension',
						'remove_from_atts' => true,
						'heading'          => esc_attr__( 'Progress Bar Height', 'fusion-builder' ),
						'description'      => esc_attr__( 'Insert a height for the progress bar. Enter value including any valid CSS unit, ex: 10px.', 'fusion-builder' ),
						'param_name'       => 'dimensions',
						'value'            => [
							'height' => '',
						],
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the progress bar.', 'fusion-builder' ),
						'param_name'  => 'background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'scroll_progress_background_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Progress Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Select a color for the progress bar.', 'fusion-builder' ),
						'param_name'  => 'progress_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'scroll_progress_progress_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'range',
						'heading'     => esc_attr__( 'Border Size', 'fusion-builder' ),
						'param_name'  => 'border_size',
						'default'     => $fusion_settings->get( 'scroll_progress_border_size' ),
						'description' => esc_attr__( 'Set the border size. In pixels.', 'fusion-builder' ),
						'min'         => '0',
						'max'         => '10',
						'step'        => '1',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color for the progress bar.', 'fusion-builder' ),
						'param_name'  => 'border_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'scroll_progress_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
						'dependency'  => [
							[
								'element'  => 'border_size',
								'value'    => '0',
								'operator' => '!=',
							],
						],
					],
					'fusion_border_radius_placeholder'     => [],
					'fusion_animation_placeholder'         => [
						'preview_selector' => '.fusion-scroll-progress',
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
add_action( 'fusion_builder_before_init', 'fusion_element_scroll_progress' );
