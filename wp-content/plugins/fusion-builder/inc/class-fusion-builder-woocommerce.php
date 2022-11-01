<?php
/**
 * Fusion Builder WooCommerce.
 *
 * @package Fusion-Builder
 * @since 3.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Woo class.
 *
 * @since 3.2
 */
class Fusion_Builder_WooCommerce {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.2
	 * @var object
	 */
	private static $instance;

	/**
	 * If single product content is active.
	 *
	 * @access private
	 * @since 3.2
	 * @var object
	 */
	private $active = false;


	/**
	 * Class constructor.
	 *
	 * @since 3.2
	 * @access private
	 */
	private function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 10 );

		add_action( 'avada_after_main_content', [ $this, 'add_woocommerce_structured_data' ] );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Builder_WooCommerce();
		}
		return self::$instance;
	}

	/**
	 * Init.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public function init() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_filter( 'fusion_set_overrides', [ $this, 'check_for_override' ] );
			if ( ! empty( $_POST['calc_shipping'] ) && class_exists( 'WC_Shortcode_Cart' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				WC_Shortcode_Cart::calculate_shipping();
				unset( $_POST['calc_shipping'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
			}
		}
	}

	/**
	 * Enqueue WooCommerce scripts.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public function enqueue_scripts() {
		if ( apply_filters( 'awb_enqueue_woocommerce_frontend_scripts', class_exists( 'WooCommerce' ) && fusion_is_preview_frame() ) ) {
			wp_enqueue_script( 'zoom' );
			wp_enqueue_script( 'flexslider' );

			wp_enqueue_script( 'photoswipe-ui-default' );
			wp_enqueue_style( 'photoswipe-default-skin' );
			add_action( 'wp_footer', 'woocommerce_photoswipe' );

			wp_enqueue_script( 'wc-single-product' );
		}
	}

	/**
	 * Checks for override and initiates if found.
	 *
	 * @static
	 * @access public
	 * @param array $overrides Overrides array.
	 * @since 3.2
	 */
	public function check_for_override( $overrides = [] ) {
		if ( isset( $overrides['content'] ) && is_product() || ( fusion_is_preview_frame() && 'fusion_tb_section' === get_post_type() && has_term( 'content', 'fusion_tb_category' ) ) ) {
			$this->init_single_product();
		}
		return $overrides;
	}

	/**
	 * Checks for overrides and sets status.
	 *
	 * @static
	 * @access public
	 * @since 3.2
	 */
	public function init_single_product() {
		$this->active = true;

		add_filter( 'wc_get_template', [ $this, 'filter_woo_templates' ], 10, 5 );

		do_action( 'avada_builder_single_product' );

		// Modify body classes if needed.
		add_filter( 'fusion_add_woo_horizontal_tabs_body_class', '__return_false', 99 );
	}

	/**
	 * Change the template path to bypass Avada regular templates.
	 *
	 * @access public
	 * @param string $template      Template location.
	 * @param string $template_name Template name.
	 * @param array  $args          Arguments. (default: array).
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path  Default path. (default: '').
	 * @since 3.2
	 */
	public function filter_woo_templates( $template = '', $template_name = '', $args = [], $template_path = '', $default_path = '' ) {
		if ( class_exists( 'Avada' ) && ( strpos( $template, 'cart.php' ) || strpos( $template, 'variable.php' ) ) ) {
			$avada_path = Avada::$template_dir_path . '/woocommerce/';
			$woo_path   = wp_normalize_path( WC()->plugin_path() . '/templates/' );
			$template   = wp_normalize_path( $template );

			// We are on single product, we force to default.
			if ( $this->is_layout_product() ) {
				$new_template = str_replace( $avada_path, $woo_path, $template );

				// We are not on a single product, allow Avada ones to override.
			} else {
				$new_template = str_replace( $woo_path, $avada_path, $template );
			}

			// Ensure template file actually exists.
			if ( file_exists( $new_template ) ) {
				return $new_template;
			}
		}

		// No alternative found, return usual.
		return $template;
	}

	/**
	 * Checks if currently a layout product page.
	 *
	 * @access public
	 * @since 3.2
	 */
	public function is_layout_product() {
		return $this->active;
	}

	/**
	 * Get the page option from the template if not set in post.
	 *
	 * @since 2.2
	 * @access public
	 * @param array  $data Full data array.
	 * @param object $post Post object from target post.
	 * @return mixed
	 */
	public function add_product_data( $data, $post ) {
		if ( isset( $post->post_type ) && 'product' === $post->post_type ) {
			$product = wc_get_product( $post->ID );

			$data['examplePostDetails']['woo'] = [
				'featured'           => $product->get_featured(),
				'catalog_visibility' => $product->get_catalog_visibility(),
				'description'        => $product->get_description(),
				'short_description'  => $product->get_short_description(),
				'sku'                => $product->get_sku(),
				'menu_order'         => $product->get_menu_order(),
				'virtual'            => $product->get_virtual(),
				'price'              => $product->get_price(),
				'regular_price'      => $product->get_regular_price(),
				'sales_badge'        => $product->get_sale_price(),
				'data_on_sale_from'  => $product->get_date_on_sale_from(),
				'date_on_sale_to'    => $product->get_date_on_sale_to(),
				'total_sales'        => $product->get_total_sales(),
			];
		}
		return $data;
	}

	/**
	 * Add WooCommerce structured data, when using custom single product layout.
	 *
	 * @access public
	 * @since 4.5
	 * @return void
	 */
	public function add_woocommerce_structured_data() {
		global $woocommerce;

		if ( $this->is_layout_product() ) {
			$woocommerce->structured_data->generate_website_data();
			$woocommerce->structured_data->generate_product_data();
		}
	}
}

/**
 * Instantiates the Fusion_Woo class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.2
 * @return object Fusion_App
 */
function Fusion_Builder_WooCommerce() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Builder_WooCommerce::get_instance();
}
Fusion_Builder_WooCommerce();
