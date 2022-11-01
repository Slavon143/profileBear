<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_tooltip' ) ) {

	if ( ! class_exists( 'FusionSC_Tooltip' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Tooltip extends Fusion_Element {

			/**
			 * An array of the shortcode arguments.
			 *
			 * @since 1.0
			 * @access protected
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
				add_filter( 'fusion_attr_tooltip-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_tooltip', [ $this, 'render' ] );

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

				return [
					'class'     => '',
					'id'        => '',
					'animation' => false,
					'delay'     => 0,
					'placement' => 'top',
					'title'     => 'none',
					'trigger'   => 'hover',
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

				$defaults = FusionBuilder::set_shortcode_defaults( self::get_element_defaults(), $args, 'fusion_tooltip' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_tooltip', $args );

				$this->args = $defaults;

				$html = sprintf( '<span %s>%s</span>', FusionBuilder::attributes( 'tooltip-shortcode' ), do_shortcode( $content ) );

				$this->on_render();

				return apply_filters( 'fusion_element_tooltip_content', $html, $args );

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
					'class' => 'fusion-tooltip tooltip-shortcode',
				];

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['data-animation'] = $this->args['animation'];
				$attr['data-delay']     = $this->args['delay'];
				$attr['data-placement'] = $this->args['placement'];
				$attr['data-title']     = $this->args['title'];
				$attr['title']          = $this->args['title'];
				$attr['data-toggle']    = 'tooltip';
				$attr['data-trigger']   = $this->args['trigger'];

				return $attr;

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
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/tooltip.min.css' );
			}
		}
	}

	new FusionSC_Tooltip();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_tooltip() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Tooltip',
			[
				'name'           => esc_attr__( 'Tooltip', 'fusion-builder' ),
				'shortcode'      => 'fusion_tooltip',
				'icon'           => 'fusiona-exclamation-sign',
				'generator_only' => true,
				'help_url'       => 'https://theme-fusion.com/documentation/avada/elements/tooltip-element/',
				'params'         => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Tooltip Title', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the text that displays in the tooltip.', 'fusion-builder' ),
						'param_name'  => 'title',
						'value'       => '',
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Triggering Content', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert the text that will activate the tooltip when hovered or clicked.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Tooltip Position', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the display position.' ),
						'param_name'  => 'placement',
						'value'       => [
							'top'    => esc_attr__( 'Top', 'fusion-builder' ),
							'bottom' => esc_attr__( 'Bottom', 'fusion-builder' ),
							'left'   => esc_attr__( 'Left', 'fusion-builder' ),
							'right'  => esc_attr__( 'Right', 'fusion-builder' ),
						],
						'default'     => 'top',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Tooltip Trigger Action', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose action to trigger the tooltip.', 'fusion-builder' ),
						'param_name'  => 'trigger',
						'value'       => [
							'hover' => esc_attr__( 'Hover', 'fusion-builder' ),
							'click' => esc_attr__( 'Click', 'fusion-builder' ),
						],
						'default'     => 'hover',
						'group'       => esc_attr__( 'Design', 'fusion-builder' ),
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
add_action( 'fusion_builder_before_init', 'fusion_element_tooltip' );
