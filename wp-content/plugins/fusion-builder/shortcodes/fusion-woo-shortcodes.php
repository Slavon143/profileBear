<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_woo_shortcodes' ) && class_exists( 'WooCommerce' ) ) {

	if ( ! class_exists( 'FusionSC_FusionWooShortcodes' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_FusionWooShortcodes extends Fusion_Element {

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
				add_shortcode( 'fusion_woo_shortcodes', [ $this, 'render' ] );

				add_filter( 'fusion_woo_shortcodes_content', 'shortcode_unautop' );
				add_filter( 'fusion_woo_shortcodes_content', 'do_shortcode' );
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
				$html = apply_filters( 'fusion_woo_shortcodes_content', fusion_builder_fix_shortcodes( $content ) );

				$this->on_render();

				return apply_filters( 'fusion_element_woo_shortcodes_content', $html, $args );
			}
		}
	}

	new FusionSC_FusionWooShortcodes();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_woo_shortcodes() {
	if ( class_exists( 'WooCommerce' ) ) {
		fusion_builder_map(
			[
				'name'                              => esc_attr__( 'Woo Shortcodes', 'fusion-builder' ),
				'shortcode'                         => 'fusion_woo_shortcodes',
				'icon'                              => 'fusiona-tag',
				'admin_enqueue_js'                  => FUSION_BUILDER_PLUGIN_URL . 'shortcodes/js/fusion-woo-shortcodes.js',
				'front_end_custom_settings_view_js' => FUSION_BUILDER_PLUGIN_URL . 'inc/templates/custom/front-end/js/fusion-woo-shortcodes-settings.js',
				'help_url'                          => 'https://theme-fusion.com/documentation/avada/elements/woocommerce-shortcodes-element/',
				'params'                            => [
					[
						'type'             => 'select',
						'heading'          => esc_attr__( 'Shortcode', 'fusion-builder' ),
						'description'      => esc_attr__( 'Choose woocommerce shortcode.', 'fusion-builder' ),
						'skip_debounce'    => true,
						'param_name'       => 'fusion_woo_shortcode',
						'value'            => [
							'1' => esc_attr__( 'Order tracking', 'fusion-builder' ),
							'2' => esc_attr__( 'Product price/cart button', 'fusion-builder' ),
							'3' => esc_attr__( 'Product by SKU/ID', 'fusion-builder' ),
							'4' => esc_attr__( 'Products by SKU/ID', 'fusion-builder' ),
							'5' => esc_attr__( 'Product categories', 'fusion-builder' ),
							'6' => esc_attr__( 'Products by category slug', 'fusion-builder' ),
							'7' => esc_attr__( 'Recent products', 'fusion-builder' ),
							'8' => esc_attr__( 'Featured products', 'fusion-builder' ),
							'9' => esc_attr__( 'Shop Message', 'fusion-builder' ),
						],
						'default'          => '',
						'remove_from_atts' => true,
					],
					[
						'type'        => 'textarea',
						'heading'     => esc_attr__( 'Shortcode content', 'fusion-builder' ),
						'description' => esc_attr__( 'Shortcode will appear here.', 'fusion-builder' ),
						'param_name'  => 'element_content',
						'value'       => '[woocommerce_order_tracking]',
					],
				],
			]
		);
	}
}
add_action( 'fusion_builder_before_init', 'fusion_element_woo_shortcodes' );
