<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 3.2
 */

if ( fusion_is_element_enabled( 'fusion_tb_woo_upsells' ) ) {

	if ( ! class_exists( 'FusionTB_Woo_Upsells' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 3.2
		 */
		class FusionTB_Woo_Upsells extends Fusion_Woo_Products_Component {

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 3.2
			 */
			public function __construct() {
				$shortcode                 = 'fusion_tb_woo_upsells';
				$this->shortcode_classname = 'fusion-woo-upsells-tb';
				parent::__construct( $shortcode );
			}

			/**
			 * Checking the right page.
			 *
			 * @since 3.3
			 * @return boolean
			 */
			public function is_checking_page() {
				return $this->is_product() || is_cart() || is_page();
			}

			/**
			 * Checking if upsells.
			 *
			 * @since 3.3
			 * @return boolean
			 */
			public function is_upsells() {
				$return = $this->is_product() || ( ! is_cart() && ! is_page() );
				if ( ( fusion_doing_ajax() && isset( $_POST['fusion_load_nonce'] ) ) || fusion_is_preview_frame() ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
					$return = $this->post_target ? 'product' === $this->post_target->post_type : 'product' === get_post( $this->get_post_id() )->post_type;
				}
				return $return;
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

				if ( $this->is_upsells() ) {
					$attr['class'] .= ' up-sells upsells products';
				} else {
					$attr['class'] .= ' fusion-woo-cross-sells cross-sells products';
				}

				return $attr;
			}

			/**
			 * Get 'no related products' placeholder.
			 *
			 * @since 3.2
			 * @return string
			 */
			protected function get_placeholder() {
				$text = $this->is_upsells() ? __( 'There are no Upsells for this product.', 'fusion-builder' ) : __( 'There are no cross-sells product.', 'fusion-builder' );
				return '<div class="fusion-builder-placeholder">' . $text . '</div>';
			}

			/**
			 * Define heading text.
			 *
			 * @access public
			 * @since 3.2
			 * @return string
			 */
			public function get_main_heading() {
				if ( $this->is_upsells() ) {
					return apply_filters( 'woocommerce_product_upsells_products_heading', __( 'You may also like&hellip;', 'fusion-builder' ) );
				} else {
					return apply_filters( 'woocommerce_product_cross_sells_products_heading', __( 'You may be interested in&hellip;', 'fusion-builder' ) );
				}
			}

			/**
			 * Get product query.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function get_query() {
				if ( $this->is_upsells() ) {
					return $this->get_query_upsells();
				} else {
					return $this->get_query_cross_sells();
				}
			}

			/**
			 * Get query type.
			 *
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function query_type() {
				if ( $this->is_upsells() ) {
					return 'up-sells';
				} else {
					return 'cross-sells';
				}
			}

			/**
			 * Get product query upsells.
			 *
			 * @access public
			 * @since 3.3
			 * @return array
			 */
			public function get_query_upsells() {
				global $product;

				$args = [
					'posts_per_page' => $this->args['number_products'],
					'columns'        => $this->args['products_columns'],
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
				];
				$args = apply_filters( 'woocommerce_upsell_display_args', $args );

				$defaults = [
					'posts_per_page' => '-1',
					'columns'        => 4,
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
					'order'          => 'desc',
				];

				$args = wp_parse_args( $args, $defaults );

				// Get visible related products then sort them at random.
				$args['products'] = wc_products_array_orderby( array_filter( array_map( 'wc_get_product', $product->get_upsell_ids() ), 'wc_products_array_filter_visible' ), $args['orderby'], $args['order'] );
				$args['products'] = $args['posts_per_page'] > 0 ? array_slice( $args['products'], 0, $args['posts_per_page'] ) : $args['products'];

				return $args;
			}

			/**
			 * Get product query.
			 *
			 * @access public
			 * @since 3.2
			 * @return array
			 */
			public function get_query_cross_sells() {
				global $product;

				$args = [
					'posts_per_page' => $this->args['number_products'],
					'columns'        => $this->args['products_columns'],
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
				];

				$defaults = [
					'posts_per_page' => '2',
					'columns'        => 2,
					'orderby'        => 'rand', // @codingStandardsIgnoreLine.
					'order'          => 'desc',
				];

				$args = wp_parse_args( $args, $defaults );

				// Get visible cross sells then sort them at random.
				$cross_sells = is_object( WC()->cart ) ? WC()->cart->get_cross_sells() : [];
				$cross_sells = array_filter( array_map( 'wc_get_product', $cross_sells ), 'wc_products_array_filter_visible' );

				// Handle orderby and limit results.
				$orderby          = apply_filters( 'woocommerce_cross_sells_orderby', $args['orderby'] );
				$order            = apply_filters( 'woocommerce_cross_sells_order', $args['order'] );
				$cross_sells      = wc_products_array_orderby( $cross_sells, $orderby, $order );
				$limit            = apply_filters( 'woocommerce_cross_sells_total', $args['posts_per_page'] );
				$args['products'] = $limit > 0 ? array_slice( $cross_sells, 0, $limit ) : $cross_sells;

				// return any products for demo purpose.
				if ( is_array( $args['products'] ) && 0 === count( $args['products'] ) && $this->is_builder() ) {
					$args['fields']    = 'ids';
					$args['post_type'] = 'product';
					$products          = fusion_cached_query( $args );

					if ( $products->have_posts() ) {
						$args['products'] = array_filter( array_map( 'wc_get_product', fusion_cached_query( $args )->get_posts() ), 'wc_products_array_filter_visible' );
					}
					unset( $args['fields'] );
					unset( $args['post_type'] );
				}

				return $args;
			}

			/**
			 * Set wc loop props.
			 *
			 * @access public
			 * @since 3.2
			 * @param array $args The arguments.
			 * @return void
			 */
			public function set_loop_props( $args ) {
				$name        = $this->is_upsells() ? 'up-sells' : 'cross-sells';
				$filter_name = $this->is_upsells() ? 'woocommerce_upsells_columns' : 'woocommerce_cross_sells_columns';
				wc_set_loop_prop( 'name', $name );
				wc_set_loop_prop( 'columns', apply_filters( $filter_name, $args['columns'] ) );
			}
		}
	}

	new FusionTB_Woo_Upsells();
}

/**
 * Map shortcode to Avada Builder
 *
 * @since 3.2
 */
function fusion_component_woo_upsells() {

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionTB_Woo_Upsells',
			[
				'name'      => esc_attr__( 'Woo Up/Cross-sells', 'fusion-builder' ),
				'shortcode' => 'fusion_tb_woo_upsells',
				'icon'      => 'fusiona-woo-upsell-products',
				'params'    => fusion_get_woo_product_params(
					[
						'ajax_action'                => 'get_fusion_tb_woo_upsells',
						'animation_preview_selector' => '.fusion-woo-upsells-tb',
					]
				),
				'callback'  => [
					'function' => 'fusion_ajax',
					'action'   => 'get_fusion_tb_woo_upsells',
					'ajax'     => true,
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_component_woo_upsells' );
