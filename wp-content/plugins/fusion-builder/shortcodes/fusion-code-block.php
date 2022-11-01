<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_code' ) ) {

	if ( ! class_exists( 'FusionSC_Code_Block' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Code_Block extends Fusion_Element {

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
				add_shortcode( 'fusion_code', [ $this, 'render' ] );
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
				$content = fusion_decode_if_needed( $content );
				$content = apply_filters( 'fusion_shortcode_content', $content, 'fusion_code', $args );

				$this->on_render();

				return apply_filters( 'fusion_element_code_block_content', do_shortcode( html_entity_decode( $content, ENT_QUOTES ) ), $args );
			}
		}
	}

	new FusionSC_Code_Block();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_code_block() {
	fusion_builder_map(
		[
			'name'        => esc_attr__( 'Code Block', 'fusion-builder' ),
			'shortcode'   => 'fusion_code',
			'icon'        => 'fusiona-code',
			'escape_html' => true,
			'help_url'    => 'https://theme-fusion.com/documentation/avada/elements/code-block-element/',
			'params'      => [
				[
					'type'        => 'code',
					'heading'     => esc_attr__( 'Code', 'fusion-builder' ),
					'description' => esc_attr__( 'Enter some content for this codeblock.', 'fusion-builder' ),
					'param_name'  => 'element_content',
					'value'       => '',
				],
			],
		]
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_code_block' );
