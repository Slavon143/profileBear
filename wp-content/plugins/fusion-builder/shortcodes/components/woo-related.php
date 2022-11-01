<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_related' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Related' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Related extends Fusion_Woo_Products_Component {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				$shortcode                 = 'fusion_tb_woo_related';
				$this->shortcode_classname = 'fusion-woo-related-tb';
				parent::__construct( $shortcode );
			}


			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function attr() {
				$attr = parent::attr();

				$attr['class'] .= ' related products';

				return $attr;
			}

			/**
			 * Get 'no related products' placeholder.
			 *
			 * @since 3.2
			 * @return string
			 */
			protected function get_placeholder() {
				return '<div class="fusion-builder-placeholder">' . esc_html__( 'There are no related products.', 'fusion-builder' ) . '</div>';
			}

			/**
			 * Define heading text.
			 *
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_main_heading() {
				return apply_filters( 'woocommerce_product_related_products_heading', __( 'Related products', 'fusion-builder' ) );
			}

			/**
			 * Get product query.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function get_query() {
				global $product;

				$args = [
					'posts_per_page' => $this->args['number_products'],
					'columns'        => $this->args['products_columns'],
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
				];
				$args = apply_filters( 'woocommerce_output_related_products_args', $args );

				$defaults = [
					'posts_per_page' => 2,
					'columns'        => 2,
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
					'order'          => 'desc',
				];

				$args = wp_parse_args( $args, $defaults );

				// Get visible related products then sort them at random.
				$args['products'] = array_filter( array_map( 'wc_get_product', wc_get_related_products( $product->get_id(), $args['posts_per_page'], $product->get_upsell_ids() ) ), 'wc_products_array_filter_visible' );

				// Handle orderby.
				$args['products'] = wc_products_array_orderby( $args['products'], $args['orderby'], $args['order'] );

				return $args;
			}

			/**
			 * Set wc loop props.
			 *
			 * @access public
			 * @param  array $args Shortcode parameters.
			 * @since 3.2
			 * @return void
			 */
			public function set_loop_props( $args ) {
				wc_set_loop_prop( 'name', 'related' );
				wc_set_loop_prop( 'columns', apply_filters( 'woocommerce_related_products_columns', $args['columns'] ) );
			}
		}
	}

	new FusionTB_Woo_Related();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_related() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Related',
			[
				'name'      => esc_attr__( 'Woo Related Products', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_related',
				'icon'      => 'fusiona-woo-related-products',
				'component' => true,
				'templates' => [ 'content' ],
				'params'    => fusion_get_woo_product_params(
					[
						'ajax_action'                => 'get_fusion_tb_woo_related',
						'animation_preview_selector' => '.fusion-woo-related-tb',
					]
				),
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_related',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_related' );
