<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_popover' ) ) {

	if ( ! class_exists( 'FusionSC_Popover' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Popover extends Fusion_Element {

			/**
			 * The popover counter.
			 *
			 * @access private
			 * @since 1.0
			 * @var int
			 */
			private $popover_counter = 1;

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
				add_filter( 'fusion_attr_popover-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_popover', [ $this, 'render' ] );

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
					'class'            => '',
					'id'               => '',
					'animation'        => true,
					'content'          => '',
					'content_bg_color' => '',
					'delay'            => '50',
					'placement'        => strtolower( $fusion_settings->get( 'popover_placement' ) ),
					'title'            => '',
					'title_bg_color'   => '',
					'bordercolor'      => '',
					'textcolor'        => '',
					'trigger'          => 'click',
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
					'popover_placement' => 'placement',
				];
			}

			/**
			 * Render the shortcode
			 *
			 * @access public
			 * @since 1.0
			 * @param  array  $args       Shortcode parameters.
			 * @param  string $sc_content Content between shortcode.
			 * @return string             HTML output.
			 */
			public function render( $args, $sc_content = '' ) {
				$fusion_settings = awb_get_fusion_settings();

				$defaults   = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_popover' );
				$sc_content = apply_filters( 'fusion_shortcode_content', $sc_content, 'fusion_popover', $args );

				if ( 'default' === $defaults['placement'] ) {
					$defaults['placement'] = strtolower( $fusion_settings->get( 'popover_placement' ) );
				}

				extract( $defaults );

				$this->args = $defaults;

				$arrow_color = $content_bg_color;
				if ( 'bottom' === $placement ) {
					$arrow_color = $title_bg_color;
				}

				$styles = '';

				if ( '' !== $bordercolor ) {
					$styles .= '.popover.popover-' . $this->popover_counter . ' { border-color:' . $bordercolor . '; }';
				}
				// Title styles.
				$title_styles = '';
				if ( '' !== $title_bg_color ) {
					$title_styles .= 'background-color:' . $title_bg_color . ';';
				}
				if ( '' !== $textcolor ) {
					$title_styles .= 'color:' . $textcolor . ';';
				}
				if ( '' !== $bordercolor ) {
					$title_styles .= 'border-color:' . $bordercolor . ';';
				}

				if ( '' !== $title_styles ) {
					$styles .= '.popover.popover-' . $this->popover_counter . ' .popover-title{' . $title_styles . '}';
				}

				// Content styles.
				$content_styles = '';
				if ( '' !== $content_bg_color ) {
					$content_styles .= 'background-color:' . $content_bg_color . ';';
				}
				if ( '' !== $textcolor ) {
					$content_styles .= 'color:' . $textcolor . ';';
				}
				if ( '' !== $content_styles ) {
					$styles .= '.popover.popover-' . $this->popover_counter . ' .popover-content{' . $content_styles . '}';
				}

				// Arrow borders.
				if ( '' !== $bordercolor ) {
					$styles .= '.popover.popover-' . $this->popover_counter . '.' . $placement . ' .arrow{border-' . $placement . '-color:' . $bordercolor . ';}';
				}
				if ( '' !== $arrow_color ) {
					$styles .= '.popover.popover-' . $this->popover_counter . '.' . $placement . ' .arrow:after{border-' . $placement . '-color:' . $arrow_color . ';}';
				}

				if ( '' !== $styles ) {
					$styles = '<style type="text/css">' . $styles . '</style>';
				}

				$html = '<span ' . FusionBuilder::attributes( 'popover-shortcode' ) . '>' . $styles;

				$prev_state = fusion_element_rendering_elements();

				fusion_element_rendering_elements( true );
				$html .= do_shortcode( $sc_content );
				fusion_element_rendering_elements( $prev_state );

				$html .= '</span>';

				if ( fusion_element_rendering_is_flex() ) {
					$html = '<div>' . $html . '</div>';
				}

				$this->popover_counter++;

				$this->on_render();

				return apply_filters( 'fusion_element_popover_content', $html, $args );

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
					'class' => 'fusion-popover popover-' . $this->popover_counter,
				];

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				// Check if content contains characters which need escaped.
				$content           = fusion_decode_if_needed( $this->args['content'] );
				$escaped_content   = esc_attr( $content );
				$content_attribute = 'data-content';
				if ( $content !== $escaped_content ) {
					$content_attribute = 'data-html-content';
				}

				$attr['data-animation']     = $this->args['animation'];
				$attr['data-class']         = 'popover-' . $this->popover_counter;
				$attr['data-container']     = 'popover-' . $this->popover_counter;
				$attr[ $content_attribute ] = $escaped_content;
				$attr['data-delay']         = $this->args['delay'];
				$attr['data-placement']     = strtolower( $this->args['placement'] );
				$attr['data-title']         = $this->args['title'];
				$attr['data-toggle']        = 'popover';
				$attr['data-trigger']       = $this->args['trigger'];

				return $attr;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections Popover settings.
			 */
			public function add_options() {

				return [
					'popover_shortcode_section' => [
						'label'       => esc_html__( 'Popover', 'fusion-builder' ),
						'description' => '',
						'id'          => 'popover_shortcode_section',
						'type'        => 'accordion',
						'icon'        => 'fusiona-uniF61C',
						'fields'      => [
							'popover_heading_bg_color' => [
								'label'       => esc_html__( 'Popover Heading Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the popover heading background.', 'fusion-builder' ),
								'id'          => 'popover_heading_bg_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--popover_heading_bg_color',
										'element'  => '.popover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'popover_content_bg_color' => [
								'label'       => esc_html__( 'Popover Content Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of popover content background.', 'fusion-builder' ),
								'id'          => 'popover_content_bg_color',
								'default'     => 'var(--awb-color1)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--popover_content_bg_color',
										'element'  => '.popover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'popover_border_color'     => [
								'label'       => esc_html__( 'Popover Border Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the border color of popover box.', 'fusion-builder' ),
								'id'          => 'popover_border_color',
								'default'     => 'var(--awb-color3)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--popover_border_color',
										'element'  => '.popover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'popover_text_color'       => [
								'label'       => esc_html__( 'Popover Text Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the popover text.', 'fusion-builder' ),
								'id'          => 'popover_text_color',
								'default'     => 'var(--awb-color8)',
								'type'        => 'color-alpha',
								'css_vars'    => [
									[
										'name'     => '--popover_text_color',
										'element'  => '.popover',
										'callback' => [ 'sanitize_color' ],
									],
								],
							],
							'popover_placement'        => [
								'label'       => esc_html__( 'Popover Position', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the position of the popover in reference to the triggering element.', 'fusion-builder' ),
								'id'          => 'popover_placement',
								'default'     => 'Top',
								'type'        => 'radio-buttonset',
								'choices'     => [
									'Top'    => esc_html__( 'Top', 'fusion-builder' ),
									'Right'  => esc_html__( 'Right', 'fusion-builder' ),
									'Bottom' => esc_html__( 'Bottom', 'fusion-builder' ),
									'Left'   => esc_html__( 'Left', 'fusion-builder' ),
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
				Fusion_Dynamic_JS::enqueue_script( 'fusion-popover' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/popover.min.css' );
			}
		}
	}

	new FusionSC_Popover();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_popover() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Popover',
			[
				'name'      => esc_attr__( 'Popover', 'fusion-builder' ),
				'shortcode' => 'fusion_popover',
				'icon'      => 'fusiona-uniF61C',
				'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/popover-element/',
				'params'    => [
					[
						'type'         => 'tinymce',
						'heading'      => esc_attr__( 'Triggering Content', 'fusion-builder' ),
						'param_name'   => 'element_content',
						'value'        => '',
						'description'  => esc_attr__( 'Content that will trigger the popover.', 'fusion-builder' ),
						'dynamic_data' => true,
					],
					[
						'type'         => 'textfield',
						'heading'      => esc_attr__( 'Popover Heading', 'fusion-builder' ),
						'description'  => esc_attr__( 'Heading text of the popover.', 'fusion-builder' ),
						'param_name'   => 'title',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Heading Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the popover heading. ', 'fusion-builder' ),
						'param_name'  => 'title_bg_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_heading_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'         => 'raw_textarea',
						'heading'      => esc_attr__( 'Contents Inside Popover', 'fusion-builder' ),
						'description'  => esc_attr__( 'Text to be displayed inside the popover.', 'fusion-builder' ),
						'param_name'   => 'content',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Content Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the background color of the popover content area. ', 'fusion-builder' ),
						'param_name'  => 'content_bg_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_content_bg_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Border Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls the border color of the of the popover box. ', 'fusion-builder' ),
						'param_name'  => 'bordercolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_border_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Popover Text Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls all the text color inside the popover box.', 'fusion-builder' ),
						'param_name'  => 'textcolor',
						'value'       => '',
						'default'     => $fusion_settings->get( 'popover_text_color' ),
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Popover Trigger Method', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose mouse action to trigger popover.' ),
						'param_name'  => 'trigger',
						'value'       => [
							'hover' => esc_attr__( 'Hover', 'fusion-builder' ),
							'click' => esc_attr__( 'Click', 'fusion-builder' ),
						],
						'default'     => 'click',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Popover Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the display position of the popover. Choose default for Global Options selection.' ),
						'param_name'  => 'placement',
						'value'       => [
							'default' => esc_attr__( 'Default', 'fusion-builder' ),
							'top'     => esc_attr__( 'Top', 'fusion-builder' ),
							'bottom'  => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'    => esc_attr__( 'Left', 'fusion-builder' ),
							'right'   => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'default',
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
add_action( 'fusion_builder_before_init', 'fusion_element_popover' );
