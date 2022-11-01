<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_one_page_text_link' ) ) {

	if ( ! class_exists( 'FusionSC_OnePageTextLink' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_OnePageTextLink extends Fusion_Element {

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
				add_filter( 'fusion_attr_one-page-text-link-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_one_page_text_link', [ $this, 'render' ] );

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
					'class' => '',
					'id'    => '',
					'link'  => '',
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

				$defaults = shortcode_atts( self::get_element_defaults(), $args, 'fusion_one_page_text_link' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_one_page_text_link', $args );

				$this->args = $defaults;

				$html = '<a ' . FusionBuilder::attributes( 'one-page-text-link-shortcode' ) . '>' . do_shortcode( $content ) . '</a>';

				$this->on_render();

				return apply_filters( 'fusion_element_one_page_link_content', $html, $args );

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
					'class' => 'fusion-one-page-text-link',
				];

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				$attr['href'] = $this->args['link'];

				return $attr;

			}
		}
	}

	new FusionSC_OnePageTextLink();

}

/**
 * Map shortcode to Avada Builder
 */
function fusion_element_one_page_text_link() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_OnePageTextLink',
			[
				'name'      => esc_attr__( 'One Page Text Link', 'fusion-builder' ),
				'shortcode' => 'fusion_one_page_text_link',
				'icon'      => 'fusiona-external-link',
				'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/one-page-text-link-element/',
				'params'    => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Name Of Anchor', 'fusion-builder' ),
						'description' => esc_attr__( 'Unique identifier of the anchor to scroll to on click. Anchor names need to be prefixed with a hastag, ex: #anchorname.', 'fusion-builder' ),
						'param_name'  => 'link',
						'value'       => '',
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Text or HTML code', 'fusion-builder' ),
						'description' => esc_attr__( 'Insert text or HTML code here (e.g: HTML for image). This content will be used to trigger the scrolling to the anchor.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '',
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
add_action( 'fusion_builder_before_init', 'fusion_element_one_page_text_link' );
