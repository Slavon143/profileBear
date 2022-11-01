<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_menu_anchor' ) ) {

	if ( ! class_exists( 'FusionSC_MenuAnchor' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_MenuAnchor extends Fusion_Element {

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
				add_filter( 'fusion_attr_menu-anchor-shortcode', [ $this, 'attr' ] );
				add_shortcode( 'fusion_menu_anchor', [ $this, 'render' ] );

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
					'name'  => '',
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

				$defaults = shortcode_atts( self::get_element_defaults(), $args, 'fusion_menu_anchor' );
				$content  = apply_filters( 'fusion_shortcode_content', $content, 'fusion_menu_anchor', $args );

				extract( $defaults );

				$this->args = $defaults;

				$html = '<div ' . FusionBuilder::attributes( 'menu-anchor-shortcode' ) . '></div>';

				$this->on_render();

				return apply_filters( 'fusion_element_menu_anchor_content', $html, $args );

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
					'class' => 'fusion-menu-anchor',
					'id'    => $this->args['name'],
				];

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				return $attr;

			}
		}
	}

	new FusionSC_MenuAnchor();

}

/**
 * Map shortcode to Avada Builder
 *
 * @since 1.0
 */
function fusion_element_menu_anchor() {
	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_MenuAnchor',
			[
				'name'       => esc_attr__( 'Menu Anchor', 'fusion-builder' ),
				'shortcode'  => 'fusion_menu_anchor',
				'icon'       => 'fusiona-anchor',
				'preview'    => FUSION_BUILDER_PLUGIN_DIR . 'inc/templates/previews/fusion-menu-anchor-preview.php',
				'preview_id' => 'fusion-builder-block-module-menu-anchor-preview-template',
				'help_url'   => 'https://theme-fusion.com/documentation/avada/elements/menu-anchor-element/',
				'params'     => [
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Name', 'fusion-builder' ),
						'param_name'  => 'name',
						'value'       => '',
						'description' => esc_attr__( 'This name will be the id you will have to use in your one page menu.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
					],

				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_menu_anchor' );
