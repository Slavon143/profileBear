<?php
/**
 * Modifications for WooCommerce.
 *
 * @author     ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class to apply woocommerce templates.
 *
 * @since 4.0.0
 */
class Avada_Woocommerce {

	/**
	 * Holds info if currently product elements are looped.
	 *
	 * @access private
	 * @var bool
	 */
	private $in_product_elements = false;

	/**
	 * Sale badge text.
	 *
	 * @access private
	 * @var string
	 */
	private $sale_text = '';

	/**
	 * Cache which placeholders are used.
	 *
	 * @access private
	 * @var arrray
	 */
	private $used_sale_text_placeholders = [
		'percentage' => false,
		'value'      => false,
	];

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		// Runs after we know of layout section overrides.
		add_action( 'wp', [ $this, 'wp' ], 20 );
		add_action( 'wp_loaded', [ $this, 'wp_loaded' ], 20 );
		add_action( 'wp_enqueue_scripts', [ $this, 'remove_woo_assets' ], 20 );

		add_filter( 'avada_woocommerce_product_images_layout', [ $this, 'avada_woocommerce_product_images_layout' ], 10 );
		add_filter( 'woocommerce_product_thumbnails_columns', [ $this, 'product_thumbnails_columns' ], 10 );

		add_filter( 'woocommerce_show_page_title', [ $this, 'shop_title' ], 10 );

		remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
		remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
		add_action( 'woocommerce_before_main_content', [ $this, 'before_container' ], 10 );
		add_action( 'woocommerce_after_main_content', [ $this, 'after_container' ], 10 );

		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		add_action( 'woocommerce_sidebar', [ $this, 'add_sidebar' ], 10 );

		add_filter( 'fusion_responsive_sidebar_order', [ $this, 'responsive_sidebar_order' ], 10 );

		// Products Loop.
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

		add_action( 'woocommerce_before_shop_loop_item', [ $this, 'before_shop_loop_item' ] );
		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'after_shop_loop_item' ], 30 );
		add_action( 'woocommerce_before_subcategory', [ $this, 'before_shop_loop_item' ], 5 );
		add_action( 'woocommerce_after_subcategory', [ $this, 'after_shop_loop_item' ], 30 );

		add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'add_product_wrappers_open' ], 30 );
		add_action( 'woocommerce_shop_loop_item_title', [ $this, 'product_title' ], 10 );
		add_action( 'woocommerce_after_shop_loop_item_title', [ $this, 'add_product_wrappers_close' ], 20 );

		add_action( 'avada_woocommerce_buttons_on_rollover', [ $this, 'template_loop_add_to_cart' ], 10 );
		add_action( 'avada_woocommerce_buttons_on_rollover', [ $this, 'rollover_buttons_linebreak' ], 15 );
		add_action( 'avada_woocommerce_buttons_on_rollover', [ $this, 'show_details_button' ], 20 );

		if ( 'clean' === Avada()->settings->get( 'woocommerce_product_box_design' ) ) {

			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
			add_action( 'woocommerce_after_shop_loop_item', [ $this, 'before_shop_item_buttons' ], 9 );

		} else {

			remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'before_shop_loop_item_title_open' ], 5 );
			add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'before_shop_loop_item_title_close' ], 20 );
			add_action( 'woocommerce_after_shop_loop_item', [ $this, 'before_shop_item_buttons' ], 5 );
			add_action( 'woocommerce_after_shop_loop_item', [ $this, 'template_loop_add_to_cart' ], 10 );
			add_action( 'woocommerce_after_shop_loop_item', [ $this, 'show_details_button' ], 15 );

			// Badges, open wrapper.
			add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'open_badges_wrapper' ], 6 );

			// Out of stock.
			add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'show_product_loop_outofstock_flash' ], 7 );

			// Sale.
			remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
			add_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 7 );

			// Badges, close wrapper.
			add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'close_badges_wrapper' ], 8 );
		}

		add_action( 'woocommerce_after_shop_loop_item', [ $this, 'after_shop_item_buttons' ], 20 );

		// Add product-title class to the cart item name link.
		add_filter( 'woocommerce_cart_item_name', [ $this, 'cart_item_name' ], 10 );

		remove_action( 'woocommerce_proceed_to_checkout', 'woocommerce_button_proceed_to_checkout', 20 );
		add_action( 'woocommerce_proceed_to_checkout', [ $this, 'proceed_to_checkout' ], 10 );

		add_action( 'woocommerce_before_account_navigation', [ $this, 'avada_top_user_container' ], 10 );

		// Filter the pagination.
		add_filter( 'woocommerce_pagination_args', [ $this, 'change_pagination' ] );
		add_filter( 'woocommerce_comment_pagination_args', [ $this, 'change_pagination' ] );

		// Account Page.
		add_action( 'woocommerce_account_dashboard', [ $this, 'account_dashboard' ], 5 );
		add_action( 'woocommerce_before_account_orders', [ $this, 'before_account_content_heading' ] );
		add_action( 'woocommerce_before_account_downloads', [ $this, 'before_account_content_heading' ] );
		add_action( 'woocommerce_before_account_payment_methods', [ $this, 'before_account_content_heading' ] );
		add_action( 'woocommerce_edit_account_form_start', [ $this, 'before_account_content_heading' ] );

		remove_action( 'woocommerce_view_order', 'woocommerce_order_details_table', 10 );
		add_action( 'woocommerce_view_order', [ $this, 'view_order' ], 10 );
		add_action( 'woocommerce_thankyou', [ $this, 'view_order' ] );

		add_filter( 'woocommerce_account_menu_item_classes', [ $this, 'account_menu_item_classes' ], 10, 2 );

		add_action( 'wp_loaded', [ $this, 'wpml_fix' ], 30 );

		add_filter( 'woocommerce_post_class', [ $this, 'change_product_class' ] );
		add_filter( 'product_cat_class', [ $this, 'change_product_cats_class' ] );

		remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
		add_action( 'woocommerce_after_customer_login_form', [ $this, 'after_customer_login_form' ] );
		add_action( 'woocommerce_before_customer_login_form', [ $this, 'before_customer_login_form' ] );
		add_filter( 'get_product_search_form', [ $this, 'product_search_form' ] );
		remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		add_action( 'pre_get_posts', [ $this, 'product_ordering' ], 5 );
		add_filter( 'loop_shop_per_page', [ $this, 'loop_shop_per_page' ] );

		add_action( 'woocommerce_before_shop_loop_item_title', [ $this, 'thumbnail' ], 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );

		add_filter( 'wp_nav_menu_items', [ $this, 'add_woo_cart_to_widget' ], 20, 2 );
		add_filter( 'woocommerce_add_to_cart_fragments', [ $this, 'header_add_to_cart_fragment' ] );

		add_action( 'woocommerce_single_product_summary', [ $this, 'single_product_summary_open' ], 1 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'single_product_summary_close' ], 100 );

		add_action( 'woocommerce_after_single_product_summary', [ $this, 'after_single_product_summary' ], 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		add_action( 'woocommerce_after_single_product_summary', [ $this, 'output_related_products' ], 15 );
		remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
		add_action( 'woocommerce_after_single_product_summary', [ $this, 'upsell_display' ], 10 );

		add_action( 'woocommerce_before_cart_table', [ $this, 'before_cart_table' ], 20 );
		add_action( 'woocommerce_after_cart_table', [ $this, 'after_cart_table' ], 20 );

		add_action( 'woocommerce_cart_collaterals', [ $this, 'cart_collaterals' ], 5 );
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
		add_action( 'woocommerce_cart_collaterals', [ $this, 'cross_sell_display' ], 5 );

		// Make sure that the single product shortcode does not use default column amount.
		add_filter( 'shortcode_atts_product', [ $this, 'change_product_shortcode_atts' ], 20, 4 );

		// Quick view hooks.
		if ( Avada()->settings->get( 'woocommerce_enable_quick_view' ) ) {
			$this->quick_view_init();
		}

		// Remove WC customizer options.
		add_filter( 'loop_shop_columns', [ $this, 'remove_woo_customizer_columns' ] );

		// Add notice to WC customizer panel.
		add_action( 'customize_register', [ $this, 'add_woocommerce_customizer_notice' ] );

		add_action( 'fusion_woocommerce_after_shop_loop_item', [ $this, 'woocommerce_after_shop_loop_item' ] );

		add_filter( 'woocommerce_default_catalog_orderby', [ $this, 'woocommerce_default_catalog_orderby' ], 99999 );

		if ( class_exists( 'WC_Additional_Variation_Images' ) ) {
			add_action( 'wp_footer', [ $this, 'supress_variation_events' ] );
		}

		// Custom shop page.
		add_filter( 'theme_page_templates', [ $this, 'load_theme_page_templates' ], 11, 3 );
		if ( ! $this->is_wc_shop_loop_enabled() ) {
			add_filter( 'template_include', [ $this, 'shop_template_loader' ], 11 );
		}
	}

	/**
	 * WP hook calls to delay.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function wp() {
		$version        = Avada::get_theme_version();
		$layout_product = function_exists( 'Fusion_Builder_WooCommerce' ) && Fusion_Builder_WooCommerce()->is_layout_product();

		if ( ! $layout_product ) {
			$this->single_product_init();
		}

		$this->assets_init( $layout_product );

		// This filter needs to be registered before 'wp_enqueue_scripts'.
		add_filter( 'woocommerce_enqueue_styles', [ $this, 'remove_woo_scripts' ] );

		// Checkout page hooks.
		$this->checkout_init();
	}

	/**
	 * WP hook calls to delay.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function wp_loaded() {
		$this->prepare_sale_flash();
	}

	/**
	 * Enqueue and dequeue assets.
	 *
	 * @access public
	 * @since 7.2
	 * @param bool $layout_product Whether its a single product with content layout or not.
	 * @return void
	 */
	public function assets_init( $layout_product = false ) {
		$version          = Avada::get_theme_version();
		$js_folder_suffix = AVADA_DEV_MODE ? '/assets/js' : '/assets/min/js';
		$js_folder_url    = Avada::$template_dir_url . $js_folder_suffix;
		$js_folder_path   = Avada::$template_dir_path . $js_folder_suffix;

		// Main shared.
		Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woocommerce.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woocommerce.min.css' );

		if ( is_checkout() && ! fusion_library()->woocommerce->is_checkout_layout() ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-legacy-checkout.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-legacy-checkout.min.css' );
		}

		if ( fusion_library()->woocommerce->is_checkout_layout() ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-new-checkout.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-new-checkout.min.css' );
		}

		// Quick view only if enabled.
		if ( Avada()->settings->get( 'woocommerce_enable_quick_view' ) ) {
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-quick-view.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-quick-view.min.css' );

			Fusion_Media_Query_Scripts::$media_query_assets[] = [
				'avada-max-sh-cbp-woo-quick-view',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-woo-quick-view.min.css',
				[],
				$version,
				Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-sh-cbp' ),
			];

			Fusion_Media_Query_Scripts::$media_query_assets[] = [
				'avada-min-sh-cbp-woo-quick-view',
				get_template_directory_uri() . '/assets/css/media/min-sh-cbp-woo-quick-view.min.css',
				[],
				$version,
				Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-min-sh-cbp' ),
			];
		}

		// If we are not on a single product layout.
		if ( ! $layout_product ) {

			// We only need these on legacy single produt.
			if ( is_product() ) {

				// Additional info.
				Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css' );
			}

			// Legacy product CSS.
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-legacy-product.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-legacy-product.min.css' );

			// Woo notices can be on any Woo page.
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-notices.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-notices.min.css' );

			// Gallery can be in quick view.
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-product-images.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-product-images.min.css' );

			// Check if can be moved to single product only.
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-tabs.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-tabs.min.css' );
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-reviews.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-reviews.min.css' );
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-additional-info.min.css' );

			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-products.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-products.min.css' );
			Fusion_Dynamic_CSS::enqueue_style( Avada::$template_dir_path . '/assets/css/dynamic/woocommerce/woo-sorting.min.css', Avada::$template_dir_url . '/assets/css/dynamic/woocommerce/woo-sorting.min.css' );

			Fusion_Media_Query_Scripts::$media_query_assets[] = [
				'avada-max-sh-cbp-woo-tabs',
				get_template_directory_uri() . '/assets/css/media/max-sh-cbp-woo-tabs.min.css',
				[],
				$version,
				Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-sh-cbp' ),
			];

			// JS scripts.
			Fusion_Dynamic_JS::enqueue_script(
				'avada-woo-product-images',
				$js_folder_url . '/general/avada-woo-product-images.js',
				$js_folder_path . '/general/avada-woo-product-images.js',
				[ 'jquery', 'fusion-lightbox', 'fusion-flexslider' ],
				$version,
				true
			);

			Fusion_Dynamic_JS::register_script(
				'avada-woo-products',
				$js_folder_url . '/general/avada-woo-products.js',
				$js_folder_path . '/general/avada-woo-products.js',
				[ 'jquery', 'fusion-lightbox', 'fusion-flexslider' ],
				$version,
				true
			);

			Fusion_Dynamic_JS::enqueue_script(
				'avada-woocommerce',
				$js_folder_url . '/general/avada-woocommerce.js',
				$js_folder_path . '/general/avada-woocommerce.js',
				[ 'jquery', 'modernizr', 'fusion-equal-heights', 'fusion-lightbox', 'avada-woo-products' ],
				$version,
				true
			);

			Fusion_Dynamic_JS::localize_script(
				'avada-woocommerce',
				'avadaWooCommerceVars',
				self::get_avada_wc_vars()
			);
		}

		Fusion_Media_Query_Scripts::$media_query_assets[] = [
			'avada-min-768-max-1024-woo',
			get_template_directory_uri() . '/assets/css/media/min-768-max-1024-woo.min.css',
			[],
			$version,
			Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-min-768-max-1024' ),
		];

		Fusion_Media_Query_Scripts::$media_query_assets[] = [
			'avada-max-sh-640-woo',
			get_template_directory_uri() . '/assets/css/media/max-sh-640-woo.min.css',
			[],
			$version,
			Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-sh-640' ),
		];

		Fusion_Media_Query_Scripts::$media_query_assets[] = [
			'avada-max-sh-cbp-woo',
			get_template_directory_uri() . '/assets/css/media/max-sh-cbp-woo.min.css',
			[],
			$version,
			Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-max-sh-cbp' ),
		];

		Fusion_Media_Query_Scripts::$media_query_assets[] = [
			'avada-min-sh-cbp-woo',
			get_template_directory_uri() . '/assets/css/media/min-sh-cbp-woo.min.css',
			[],
			$version,
			Fusion_Media_Query_Scripts::get_media_query_from_key( 'fusion-min-sh-cbp' ),
		];
	}

	/**
	 * Init single product.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function single_product_init() {
		add_filter( 'avada_single_product_images_wrapper_classes', [ $this, 'add_single_product_images_wrapper_classes' ], 10 );

		// Remove zoom and lightbox scripts, if not used on single product pages.
		if ( ! Avada()->settings->get( 'woocommerce_product_images_zoom' ) ) {
			remove_theme_support( 'wc-product-gallery-zoom' );
		}

		if ( 'avada' === apply_filters( 'avada_woocommerce_product_images_layout', 'avada' ) ) {
			remove_theme_support( 'wc-product-gallery-lightbox' );
		}

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'add_product_border' ], 19 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'template_single_title' ], 5 );
		add_action( 'woocommerce_single_product_summary', [ $this, 'stock_html' ], 10 );
		add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 11 );
	}

	/**
	 * Remove WooCommerce core assets, since we add our own.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function remove_woo_assets() {
		if ( Avada()->settings->get( 'status_lightbox' ) ) {
			wp_dequeue_script( 'prettyPhoto' );
			wp_dequeue_script( 'prettyPhoto-init' );
			wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
		}

		// Dequeue flexslider since we already enquque our own (jquery-flexslider).
		if ( is_product() ) {
			wp_dequeue_script( 'flexslider' );
		}

		if ( 'off' === Avada()->settings->get( 'load_block_styles' ) ) {
			wp_dequeue_style( 'wc-block-style' );
		}
	}

	/**
	 * Removes WooCommerce scripts.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param array $scripts The WooCommerce scripts.
	 * @return array
	 */
	public function remove_woo_scripts( $scripts ) {

		if ( isset( $scripts['woocommerce-layout'] ) ) {
			unset( $scripts['woocommerce-layout'] );
		}
		if ( isset( $scripts['woocommerce-smallscreen'] ) ) {
			unset( $scripts['woocommerce-smallscreen'] );
		}
		if ( isset( $scripts['woocommerce-general'] ) ) {
			unset( $scripts['woocommerce-general'] );
		}
		return $scripts;

	}

	/**
	 * Init quick view.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function quick_view_init() {
		add_action( 'avada_after_main_content', [ $this, 'quick_view_load_container' ] );
		add_action( 'wp_ajax_fusion_quick_view_load', [ $this, 'quick_view_load_product' ] );
		add_action( 'wp_ajax_nopriv_fusion_quick_view_load', [ $this, 'quick_view_load_product' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'quick_view_enqueue_scripts' ] );
		add_action( 'fusion_quick_view_summary_content', [ $this, 'template_single_title' ], 5 );
		add_action( 'fusion_quick_view_summary_content', [ $this, 'stock_html' ], 10 );
		add_action( 'fusion_quick_view_summary_content', 'woocommerce_template_single_price', 10 );
		add_action( 'fusion_quick_view_summary_content', 'woocommerce_template_single_rating', 11 );
		add_action( 'fusion_quick_view_summary_content', [ $this, 'add_product_border' ], 19 );
		add_action( 'fusion_quick_view_summary_content', 'woocommerce_template_single_excerpt', 20 );
		add_action( 'fusion_quick_view_summary_content', 'woocommerce_template_single_add_to_cart', 30 );
		/**
		 * WIP
		add_action( 'fusion_quick_view_summary_content', 'woocommerce_template_single_meta', 40 );
		add_action( 'fusion_quick_view_summary_content', [ $this, 'after_single_product_summary' ], 50 );
		 */
	}

	/**
	 * Add special class for the single product images wrapper.
	 *
	 * @access public
	 * @since 7.2
	 * @param string $classes The single product images wrapper classes.
	 * @return string The filtered classes.
	 */
	public function add_single_product_images_wrapper_classes( $classes ) {
		$classes .= ' avada-product-images-global';

		if ( 'avada' === apply_filters( 'avada_woocommerce_product_images_layout', 'avada' ) ) {
			$classes .= ' avada-product-images-thumbnails-' . Avada()->settings->get( 'woocommerce_product_images_thumbnail_position' );
		}

		return $classes;
	}

	/**
	 * Set the product image layout.
	 *
	 * @access public
	 * @since 7.2
	 * @param string $layout The product thumbnails layout.
	 * @return string The filtered layout.
	 */
	public function avada_woocommerce_product_images_layout( $layout ) {
		return Avada()->settings->get( 'woocommerce_product_images_layout' );
	}

	/**
	 * Init checkout page.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function checkout_init() {
		add_filter( 'woocommerce_order_button_html', [ $this, 'order_button_html' ] );

		add_action( 'woocommerce_checkout_terms_and_conditions', [ $this, 'change_allowed_post_tags_before_terms' ], 15 );
		add_action( 'woocommerce_checkout_terms_and_conditions', [ $this, 'change_allowed_post_tags_after_terms' ], 35 );

		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		if ( ! fusion_library()->woocommerce->is_checkout_layout() ) {
			// Add welcome user bar to checkout page.
			add_action( 'woocommerce_before_checkout_form', [ $this, 'avada_top_user_container' ], 1 );

			add_action( 'woocommerce_before_checkout_form', [ $this, 'checkout_coupon_form' ], 10 );
			add_action( 'woocommerce_checkout_after_order_review', [ $this, 'checkout_after_order_review' ], 20 );

			if ( ! Avada()->settings->get( 'woocommerce_one_page_checkout' ) ) {
				add_action( 'woocommerce_before_checkout_form', [ $this, 'before_checkout_form' ] );
				add_action( 'woocommerce_after_checkout_form', [ $this, 'after_checkout_form' ] );
			} else {
				add_action( 'woocommerce_checkout_before_customer_details', [ $this, 'checkout_before_customer_details' ] );
				add_action( 'woocommerce_checkout_after_customer_details', [ $this, 'checkout_after_customer_details' ] );
			}

			add_action( 'woocommerce_checkout_billing', [ $this, 'checkout_billing' ], 20 );
			add_action( 'woocommerce_checkout_shipping', [ $this, 'checkout_shipping' ], 20 );
		}

		add_filter( 'woocommerce_enable_order_notes_field', [ $this, 'enable_order_notes_field' ] );
	}

	/**
	 * Runs the `woocommerce_after_shop_loop_item` hook.
	 *
	 * @access public
	 * @since 6.2.0
	 * @return void
	 */
	public function woocommerce_after_shop_loop_item() {
		$this->in_product_elements = true;
		do_action( 'woocommerce_after_shop_loop_item' );
		$this->in_product_elements = false;
	}

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @static
	 * @access private
	 * @since 3.7.2
	 * @return string woocommerce version number or null.
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Add content before the container.
	 *
	 * @access public
	 */
	public function before_container() {
		ob_start();
		Avada()->layout->add_class( 'content_class' );
		$content_class = ob_get_clean();

		ob_start();
		Avada()->layout->add_style( 'content_style' );
		$content_css = ob_get_clean();
		?>
		<div class="woocommerce-container">
			<section id="content"<?php echo $content_class . ' ' . $content_css; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<?php
	}

	/**
	 * Returns false.
	 *
	 * @access public
	 * @return false
	 */
	public function shop_title() {
		return false;
	}

	/**
	 * Closes 2 divs that were previously opened.
	 *
	 * @access public
	 */
	public function after_container() {
		get_template_part( 'templates/wc-after-container' );
	}

	/**
	 * Adds the sidebar.
	 *
	 * @access public
	 */
	public function add_sidebar() {
		do_action( 'avada_after_content' );
	}

	/**
	 * Adds necessary selector to sidebar order array.
	 *
	 * @access public
	 * @param array $sidebar_order Array of selectors.
	 */
	public function responsive_sidebar_order( $sidebar_order ) {
		$key = array_search( 'content', $sidebar_order, true );
		if ( false !== $key ) {
			$sidebar_order[ $key ] .= ', .woocommerce-container';
		}

		return $sidebar_order;
	}

	/**
	 * Opens badges wrapper.
	 *
	 * @access public
	 */
	public function open_badges_wrapper() {
		global $product;

		if ( $product->is_on_sale() || ! $product->is_in_stock() ) {
			echo '<div class="fusion-woo-badges-wrapper">';
		}
	}

	/**
	 * Closes badges wrapper.
	 *
	 * @access public
	 */
	public function close_badges_wrapper() {
		global $product;
		if ( $product->is_on_sale() || ! $product->is_in_stock() ) {
			echo '</div>';
		}
	}

	/**
	 * Prints the out of stock warning.
	 *
	 * @access public
	 */
	public function show_product_loop_outofstock_flash() {
		get_template_part( 'templates/wc-product-loop-outofstock-flash' );
	}

	/**
	 * Adds the link to permalink.
	 *
	 * @access public
	 */
	public function before_shop_loop_item_title_open() {
		get_template_part( 'templates/wc-before-shop-loop-item-title-open' );
	}

	/**
	 * Closes the link.
	 *
	 * @access public
	 */
	public function before_shop_loop_item_title_close() {
		get_template_part( 'templates/wc-before-shop-loop-item-title-close' );
	}

	/**
	 * Content before the item buttons.
	 *
	 * @access public
	 */
	public function before_shop_item_buttons() {
		if ( ! $this->in_product_elements ) {
			get_template_part( 'templates/wc-before-shop-item-buttons' );
		}
	}

	/**
	 * Add to cart loop.
	 *
	 * @access public
	 * @param array $args The arguments.
	 */
	public function template_loop_add_to_cart( $args = [] ) {
		global $product;

		if ( ! $this->in_product_elements ) {

			if ( $product && ( ( $product->is_purchasable() && $product->is_in_stock() ) || $product->is_type( 'external' ) || $product->is_type( 'auction' ) ) ) {

				$defaults = [
					'quantity'   => 1,
					'class'      => implode(
						' ',
						array_filter(
							[
								'button',
								'product_type_' . $product->get_type(),
								$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
								$product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
							]
						)
					),
					'attributes' => [
						'data-product_id'  => $product->get_id(),
						'data-product_sku' => $product->get_sku(),
						'aria-label'       => $product->add_to_cart_description(),
						'rel'              => 'nofollow',
					],
				];

				$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

				if ( isset( $args['attributes']['aria-label'] ) ) {
					$args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
				}

				wc_get_template( 'loop/add-to-cart.php', $args );
			}
		}
	}

	/**
	 * Adds the linebreak where needed.
	 *
	 * @access public
	 */
	public function rollover_buttons_linebreak() {
		global $product;
		if ( $product && ( ( $product->is_purchasable() && $product->is_in_stock() ) || $product->is_type( 'external' ) ) ) {
			get_template_part( 'templates/wc-rollover-buttons-linebreak' );
		}
	}

	/**
	 * Renders the "Details" button.
	 *
	 * @access public
	 */
	public function show_details_button() {
		if ( ! $this->in_product_elements ) {
			get_template_part( 'templates/wc-show-details-button' );
		}
	}

	/**
	 * Closes 2 divs that were previously opened.
	 *
	 * @access public
	 */
	public function after_shop_item_buttons() {
		if ( ! $this->in_product_elements ) {
			get_template_part( 'templates/wc-after-shop-item-buttons' );
		}
	}

	/**
	 * Adds a div that is used for borders.
	 *
	 * @access public
	 */
	public function add_product_border() {
		get_template_part( 'templates/wc-add-product-border' );
	}

	/**
	 * Modifies the pagination.
	 *
	 * @access public
	 * @param array $options An array of our options.
	 * @return array         The options, modified.
	 */
	public function change_pagination( $options ) {
		$options['prev_text'] = '<span class="page-prev"></span><span class="page-text">' . esc_attr__( 'Previous', 'Avada' ) . '</span>';
		$options['next_text'] = '<span class="page-text">' . esc_attr__( 'Next', 'Avada' ) . '</span><span class="page-next"></span>';
		$options['type']      = 'plain';
		$options['mid_size']  = Avada()->settings->get( 'pagination_range' );
		$options['end_size']  = Avada()->settings->get( 'pagination_start_end_range' );

		return $options;
	}

	/**
	 * Filters single product gallery thumbnail columns.
	 *
	 * @since 5.1
	 * @access public
	 * @param string $columns Holds the number of gallery thumbnail columns.
	 * @return string The altered gallery thumbnail columns.
	 */
	public function product_thumbnails_columns( $columns ) {
		return Avada()->settings->get( 'woocommerce_gallery_thumbnail_columns' );
	}

	/**
	 * Open wrapper divs.
	 *
	 * @access public
	 */
	public function add_product_wrappers_open() {
		get_template_part( 'templates/wc-add-product-wrappers-open' );
	}

	/**
	 * Adds wrapper to the single products in product loop.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function before_shop_loop_item() {
		if ( ! $this->in_product_elements ) {
			get_template_part( 'templates/wc-open-product-main-wrapper' );
		}
	}

	/**
	 * Closes wrapper addedto the single products in product loop.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function after_shop_loop_item() {
		if ( ! $this->in_product_elements ) {
			get_template_part( 'templates/wc-close-product-main-wrapper' );
		}
	}

	/**
	 * Renders the product title.
	 *
	 * @access public
	 */
	public function product_title() {
		get_template_part( 'templates/wc-product-title' );
	}

	/**
	 * Closes previously opened wrappers.
	 *
	 * @access public
	 */
	public function add_product_wrappers_close() {
		get_template_part( 'templates/wc-add-product-wrappers-close' );
	}

	/**
	 * Single Product Page functions.
	 *
	 * @access public
	 */
	public function template_single_title() {
		get_template_part( 'templates/wc-single-title' );
	}

	/**
	 * Add the availability HTML.
	 *
	 * @access public
	 */
	public function stock_html() {
		get_template_part( 'templates/wc-stock' );
	}

	/**
	 * Adds the product-title class to the cart item name link.
	 *
	 * @since 5.1
	 * @access public
	 * @param string $name The cart item name, can be wrapped by an a tag or not.
	 * @return string The cart item name.
	 */
	public function cart_item_name( $name ) {
		if ( false !== strpos( $name, 'href=' ) ) {
			return str_replace( '<a', '<a class="product-title"', $name );
		}
		return $name;
	}

	/**
	 * Added in the 'woocommerce_proceed_to_checkout' action.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function proceed_to_checkout() {
		get_template_part( 'templates/wc-proceed-to-checkout' );
	}

	/**
	 * Add the view-order markup.
	 *
	 * @param int $order_id The ID of the order we're querying.
	 */
	public function view_order( $order_id ) {
		include locate_template( 'templates/wc-view-order.php' );
	}

	/**
	 * Add 'is-active' CSS class if on 'my-account/view-order' page
	 *
	 * @param array  $classes  Array of menu item classes.
	 * @param string $endpoint Current menu item endpoint.
	 */
	public function account_menu_item_classes( $classes, $endpoint ) {

		if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'view-order' ) && 'orders' === $endpoint ) {
				$classes[] = 'is-active';
		}

		return $classes;
	}

	/**
	 * Account Page functions.
	 *
	 * @access public
	 */
	public function avada_top_user_container() {
		get_template_part( 'templates/wc-top-user-container' );
	}

	/**
	 * Change the HTML of the checkout button.
	 *
	 * @since 5.1
	 * @access public
	 * @param string $html The checkout button HTML.
	 * @return string The changed HTML.
	 */
	public function order_button_html( $html ) {
		return str_replace( 'class="button', 'class="button fusion-button button-default fusion-button-default-size', $html );
	}

	/**
	 * Calls filter to change allowed post tags.
	 *
	 * @since 6.2
	 * @access public
	 * @return void
	 */
	public function change_allowed_post_tags_before_terms() {
		add_filter( 'wp_kses_allowed_html', [ $this, 'change_wp_kses_allowed_html_before_terms' ] );
	}

	/**
	 * Calls filter to change allowed post tags.
	 *
	 * @since 6.2
	 * @access public
	 * @return void
	 */
	public function change_allowed_post_tags_after_terms() {
		add_filter( 'wp_kses_allowed_html', [ $this, 'change_wp_kses_allowed_html_after_terms' ] );
	}

	/**
	 * Change allowed post tags.
	 *
	 * @since 6.2
	 * @access public
	 * @param array $tags The allowed HTML tags.
	 * @return array $tags The allowed HTML tags.
	 */
	public function change_wp_kses_allowed_html_before_terms( $tags ) {
		$tags['style'] = [ 'type' => true ];

		return $tags;
	}

	/**
	 * Change allowed post tags.
	 *
	 * @since 6.2
	 * @param array $tags The allowed HTML tags.
	 * @return array $tags The allowed HTML tags.
	 */
	public function change_wp_kses_allowed_html_after_terms( $tags ) {
		unset( $tags['style'] );

		return $tags;
	}

	/**
	 * The account dashboard.
	 *
	 * @access public
	 */
	public function account_dashboard() {
		?>
		<style>
		.woocommerce-MyAccount-content{ display: -webkit-flex;display:flex;-webkit-flex-flow: column wrap;flex-flow: column nowrap; }
		.avada-woocommerce-myaccount-heading{ -webkit-order: 0;order: 0; }
		.woocommerce-MyAccount-content > p, .woocommerce-MyAccount-content > div, .woocommerce-MyAccount-content > span{ -webkit-order: 1;order: 1; }
		.woocommerce-MyAccount-content > p:first-child { display: none; }
		</style>
		<?php
		$this->before_account_content_heading();
	}

	/**
	 * Content injected before the content heading.
	 *
	 * @access public
	 */
	public function before_account_content_heading() {
		if ( is_account_page() ) {
			$account_items   = wc_get_account_menu_items();
			$heading_content = esc_attr__( 'Dashboard', 'Avada' );

			if ( is_wc_endpoint_url( 'orders' ) ) {
				$heading_content = $account_items['orders'];
			} elseif ( is_wc_endpoint_url( 'downloads' ) ) {
				$heading_content = $account_items['downloads'];
			} elseif ( is_wc_endpoint_url( 'payment-methods' ) ) {
				$heading_content = $account_items['payment-methods'];
			} elseif ( is_wc_endpoint_url( 'edit-account' ) ) {
				$heading_content = $account_items['edit-account'];
			}
			?>
			<h2 class="avada-woocommerce-myaccount-heading">
				<?php echo $heading_content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</h2>
			<?php
		}
	}

	/**
	 * Dealing with mini-cart cache in internal browser storage.
	 * Response to action 'woocommerce_add_to_cart_hash', which overwrites the default WC cart hash and cookies.
	 *
	 * @access public
	 * @since 5.0.2
	 * @param string $hash Default WC hash.
	 * @param array  $cart WC variable holding contents of the cart without language information.
	 */
	public function add_to_cart_hash( $hash, $cart ) {

		$hash = $this->get_cart_hash( $cart );
		if ( ! headers_sent() ) {
			wc_setcookie( 'woocommerce_cart_hash', $hash );
		}
		return $hash;
	}

	/**
	 * Dealing with mini-cart cache in internal browser storage.
	 *
	 * @access private
	 * @since 5.0.2
	 * @param  array $cart WC variable holding contents of the cart without language information.
	 * @return string Cart hash with language information
	 */
	private function get_cart_hash( $cart ) {

		$lang = Fusion_Multilingual::get_active_language();
		return md5( wp_json_encode( $cart ) . $lang );

	}

	/**
	 * Dealing with mini-cart cache in internal browser storage.
	 * Sets 'woocommerce_cart_hash' cookie.
	 *
	 * @access private
	 * @since 5.0.2
	 * @param array $cart wc variable holding contents of the cart without language information.
	 */
	private function set_cookies_cart_hash( $cart ) {

		if ( ! $cart ) {
			return;
		}
		$hash = $this->get_cart_hash( $cart );
		wc_setcookie( 'woocommerce_cart_hash', $hash );

	}

	/**
	 * Dealing with mini-cart cache in internal browser storage.
	 * Response to action 'woocommerce_cart_loaded_from_session'.
	 *
	 * @access public
	 * @since 5.0.2
	 * @param WC_Cart $wc_cart wc object without language information.
	 */
	public function cart_loaded_from_session( $wc_cart ) {

		if ( headers_sent() || ! $wc_cart ) {
			return;
		}
		$cart = $wc_cart->get_cart_for_session();
		$this->set_cookies_cart_hash( $cart );

	}

	/**
	 * Dealing with mini-cart cache in internal browser storage.
	 * Response to action 'woocommerce_set_cart_cookies', which overwrites the default WC cart hash and cookies.
	 *
	 * @access public
	 * @since 5.0.2
	 * @param bool $set is true if cookies need to be set, otherwse they are unset in calling function.
	 */
	public function set_cart_cookies( $set ) {

		if ( $set ) {
			$wc      = WC();
			$wc_cart = $wc->cart;
			$cart    = $wc_cart->get_cart_for_session();
			$this->set_cookies_cart_hash( $cart );
		}
	}

	/**
	 * Fix for WPML.
	 *
	 * @access public
	 * @since 5.1 (Moved from the constructor - Props @andreagrillo)
	 */
	public function wpml_fix() {
		if ( class_exists( 'SitePress' ) ) {
			$cart_hash_filter = 'woocommerce_cart_hash';
			if ( version_compare( self::get_wc_version(), '3.6', '<' ) ) {
				$cart_hash_filter = 'woocommerce_add_to_cart_hash';
			}

			add_filter( $cart_hash_filter, [ $this, 'add_to_cart_hash' ], 5, 2 );
			add_action( 'woocommerce_cart_loaded_from_session', [ $this, 'cart_loaded_from_session' ], 5 );
			add_action( 'woocommerce_set_cart_cookies', [ $this, 'set_cart_cookies' ] );
		}
	}

	/**
	 * Changes the markup for the product search form.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param string $form The HTML of the form.
	 * @return string      Modified HTML of the form.
	 */
	public function product_search_form( $form ) {
		ob_start();
		get_template_part( 'templates/wc-product-search-form' );
		return ob_get_clean();
	}

	/**
	 * Closes the div.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function checkout_after_order_review() {
		echo ( Avada()->settings->get( 'woocommerce_one_page_checkout' ) ) ? '</div>' : '';
	}

	/**
	 * Open a div if needed.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function before_customer_login_form() {
		echo ( 'yes' !== get_option( 'woocommerce_enable_myaccount_registration' ) ) ? '<div id="customer_login" class="woocommerce-content-box full-width">' : '';
	}

	/**
	 * Markup to add after the customer-login form.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function after_customer_login_form() {
		echo ( 'yes' !== get_option( 'woocommerce_enable_myaccount_registration' ) ) ? '</div>' : '';
	}

	/**
	 * The avada_change_product_class hook - Function to add 'product-list-view' class if the list view is being displayed.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $classes An array containing class names for the particular post / product.
	 * @return array $classes An array containing additional class 'product-list-view' if the product view is set to list.
	 */
	public function change_product_class( $classes ) {
		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );
			$product_view = ( isset( $params['product_view'] ) ) ? $params['product_view'] : Avada()->settings->get( 'woocommerce_product_view' );
			$classes[]    = 'product-' . $product_view . '-view';
		} else {
			$classes[] = 'product-grid-view';
		}
		return $classes;
	}

	/**
	 * Function to add 'product-list-view' class to product categories.
	 *
	 * @access public
	 * @since 7.2
	 * @param array $classes An array containing class names for the particular product archive.
	 * @return array $classes An array containing additional class 'product-list-view'.
	 */
	public function change_product_cats_class( $classes ) {
		$classes[] = 'product-grid-view';

		return $classes;
	}

	/**
	 * Controls the actions adding the ordering boxes.
	 *
	 * @access public
	 * @since 5.0.4
	 * @param object $query The main query.
	 * @return void
	 */
	public function product_ordering( $query ) {

		// We only want to affect the main query and no ordering on search page.
		if ( ! $query->is_main_query() || is_search() ) {
			return;
		}

		$display_type = '';
		if ( fusion_is_shop( $query->get( 'page_id' ) ) ) {
			$display_type = get_option( 'woocommerce_shop_page_display', '' );
		} elseif ( $query->is_tax( get_object_taxonomies( 'product' ) ) ) {
			$parent_id    = get_queried_object_id();
			$display_type = get_term_meta( $parent_id, 'display_type', true );
			$display_type = '' === $display_type ? get_option( 'woocommerce_category_archive_display', '' ) : $display_type;
		}

		if ( 'subcategories' !== $display_type && ( fusion_is_shop( $query->get( 'page_id' ) ) || $query->is_post_type_archive( 'product' ) || $query->is_tax( get_object_taxonomies( 'product' ) ) ) ) {

			if ( Avada()->settings->get( 'woocommerce_avada_ordering' ) || Avada()->settings->get( 'woocommerce_toggle_grid_list' ) ) {
				remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
				add_action( 'woocommerce_before_shop_loop', [ $this, 'catalog_ordering' ], 30 );

				add_filter( 'woocommerce_get_catalog_ordering_args', [ $this, 'get_catalog_ordering_args' ], 20 );
			}
		}
	}

	/**
	 * Modified the ordering of products.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function catalog_ordering() {
		get_template_part( 'templates/wc-catalog-ordering' );
	}

	/**
	 * Gets the catalogue ordering arguments.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The arguments.
	 * @return array
	 */
	public function get_catalog_ordering_args( $args ) {
		global $woocommerce;
		$woo_default_catalog_orderby = apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby', '' ) );

		// On search pages use "Relevance" as default.
		if ( is_search() ) {
			$woo_default_catalog_orderby = 'relevance';
		}

		// Get the query args.
		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );
		}

		// Get order by.
		$pob = ( ! empty( $params['product_orderby'] ) && 'default' !== $params['product_orderby'] ) ? $params['product_orderby'] : $woo_default_catalog_orderby;

		// Get order.
		$po = 'asc';
		if ( isset( $params['product_order'] ) ) {
			// Dedicated ordering.
			$po = $params['product_order'];
		} else {
			// Get the correct default order.
			$po = 'asc';
			if ( 'date' === $pob || 'popularity' === $pob || 'rating' === $pob || 'price-desc' === $pob ) {
				$po = 'desc';
			}
		}

		// Remove posts_clause filter, if default ordering is set to rating or popularity to make custom ordering work correctly.
		if ( 'default' !== $pob ) {
			if ( false !== strpos( $woo_default_catalog_orderby, 'price' ) || 'popularity' === $woo_default_catalog_orderby || 'rating' === $woo_default_catalog_orderby ) {
				WC()->query->remove_ordering_args();
			}
		}

		$orderby  = 'date';
		$order    = strtoupper( $po );
		$meta_key = '';

		switch ( $pob ) {
			case 'menu_order':
			case 'default':
				$orderby = $args['orderby'];
				break;
			case 'date':
				$orderby = 'date';
				break;
			case 'price':
			case 'price-desc':
				$callback = 'DESC' === $order ? 'order_by_price_desc_post_clauses' : 'order_by_price_asc_post_clauses';
				add_filter( 'posts_clauses', [ WC()->query, $callback ] );
				break;
			case 'relevance':
				$orderby = 'relevance';
				$order   = 'DESC';
				break;
			case 'popularity':
				$meta_key = 'total_sales';
				add_filter( 'posts_clauses', [ $this, 'order_by_popularity_post_clauses' ] );
				add_action( 'wp', [ $this, 'remove_ordering_args_filters' ] );
				break;
			case 'rating':
				$meta_key = '_wc_average_rating';
				$orderby  = [
					'meta_value_num' => strtoupper( $po ),
					'ID'             => 'ASC',
				];
				break;
			case 'name':
				$orderby = 'title';
				break;
		}

		$args['orderby']  = $orderby;
		$args['order']    = $order;
		$args['meta_key'] = $meta_key; // phpcs:ignore WordPress.DB.SlowDBQuery

		return $args;
	}

	/**
	 * The order_by_popularity_post_clauses method.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param array $args The arguments array.
	 * @return array The altered arguments array.
	 */
	public function order_by_popularity_post_clauses( $args ) {
		global $wpdb;
		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );
		}

		$order = 'DESC';
		if ( ! empty( $params['product_order'] ) && 'ASC' === strtoupper( $params['product_order'] ) ) {
			$order = 'ASC';
		}

		$join_sql = $args['join'];
		if ( ! strstr( $join_sql, 'wc_product_meta_lookup' ) ) {
			$join_sql .= " LEFT JOIN {$wpdb->wc_product_meta_lookup} wc_product_meta_lookup ON $wpdb->posts.ID = wc_product_meta_lookup.product_id ";
		}

		$args['join']    = $join_sql;
		$args['orderby'] = ' wc_product_meta_lookup.total_sales ' . $order . ', wc_product_meta_lookup.product_id ' . $order;
		return $args;
	}

	/**
	 * Removes the order_by_popularity_post_clauses filters.
	 *
	 * @access public
	 * @since 5.0.4
	 */
	public function remove_ordering_args_filters() {
		remove_filter( 'posts_clauses', [ $this, 'order_by_popularity_post_clauses' ] );
	}

	/**
	 * Determine how many products we want to show per page.
	 *
	 * @access public
	 * @since 5.1.0
	 * @return int
	 */
	public function loop_shop_per_page() {

		if ( isset( $_SERVER['QUERY_STRING'] ) ) {
			parse_str( sanitize_text_field( wp_unslash( $_SERVER['QUERY_STRING'] ) ), $params );
		}

		$per_page = 12;
		if ( Avada()->settings->get( 'woo_items' ) ) {
			$per_page = Avada()->settings->get( 'woo_items' );
		}

		return ( ! empty( $params['product_count'] ) ) ? $params['product_count'] : $per_page;
	}

	/**
	 * Shows the product image.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function thumbnail() {

		$mode = Avada()->settings->get( 'woocommerce_product_box_design' );
		$mode = ( ! $mode ) ? 'classic' : $mode;
		get_template_part( 'templates/wc-thumbnail', $mode );
	}

	/**
	 * Adds cart menu item.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param string       $items The menu items.
	 * @param array|Object $args  The menu arguments.
	 * @return string
	 */
	public function add_woo_cart_to_widget( $items, $args ) {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return $items;
		}
		$ubermenu = false;
		if ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( $args->theme_location ) ) {
			// Disable woo cart on ubermenu navigations.
			$ubermenu = true;
		}
		if ( false === $ubermenu && 'fusion-widget-menu' === $args->container_class ) {
			$items .= fusion_add_woo_cart_to_widget_html();
		}

		return $items;
	}

	/**
	 * Modify the cart ajax.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $fragments Ajax fragments handled by WooCommerce.
	 * @return array
	 */
	public function header_add_to_cart_fragment( $fragments ) {
		global $wpdb;

		$header_top_cart                          = avada_nav_woo_cart( 'secondary' );
		$fragments['.fusion-secondary-menu-cart'] = $header_top_cart;

		$header_cart = avada_nav_woo_cart( 'main' );
		$fragments['.fusion-main-menu-cart:not(.menu-item-type-custom)'] = $header_cart;

		$flyout_menu_cart                         = avada_flyout_menu_woo_cart();
		$fragments['.fusion-flyout-cart-wrapper'] = $flyout_menu_cart;

		// Get cart contents count.
		$cart_contents_count = WC()->cart->get_cart_contents_count();

		// Get meta only for cart-menu special links.
		$meta_rows = wp_cache_get( 'avada_woo_nav_items', 'avada' );
		if ( false === $meta_rows ) {
			$meta_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				"SELECT postmeta.post_id, postmeta.meta_value, posts.post_title FROM $wpdb->postmeta AS postmeta
				INNER JOIN $wpdb->posts AS posts ON postmeta.post_id = posts.ID
				WHERE postmeta.meta_key = '_menu_item_fusion_megamenu' AND postmeta.meta_value LIKE '%fusion-woo-cart%'",
				OBJECT
			);
			wp_cache_set( 'avada_woo_nav_items', $meta_rows, 'avada' );
		}

		$legacy_nav_fragment_selector = '.fusion-widget-cart';
		$menu_el_nav_fragments        = [];
		foreach ( $meta_rows as $meta_row ) {
			$menu_item_meta = maybe_unserialize( $meta_row->meta_value );
			if ( is_array( $menu_item_meta ) && isset( $menu_item_meta['special_link'] ) && 'fusion-woo-cart' === $menu_item_meta['special_link'] ) {
				$legacy_nav_fragment_selector .= ':not(.menu-item-' . $meta_row->post_id . ')';
				$menu_el_nav_fragments[]       = [
					'item' => [
						'post_id'    => $meta_row->post_id,
						'post_title' => $meta_row->post_title,
					],
					'meta' => $menu_item_meta,
				];
			}
		}

		// Add legacy-headers fragment.
		$fragments[ $legacy_nav_fragment_selector ] = fusion_add_woo_cart_to_widget_html();

		// Add menu special-links fragments.
		foreach ( $menu_el_nav_fragments as $menu_item ) {

			// Cart counter.
			if ( isset( $menu_item['meta']['show_woo_cart_counter'] ) && 'yes' === $menu_item['meta']['show_woo_cart_counter'] ) {

				// Check for custom styling.
				$counter_style = '';
				if ( isset( $menu_item['meta']['highlight_label_background'] ) && ! empty( $menu_item['meta']['highlight_label_background'] ) ) {
					$counter_style .= 'background-color:' . $menu_item['meta']['highlight_label_background'] . ';';
				}

				if ( isset( $menu_item['meta']['highlight_label_border_color'] ) && ! empty( $menu_item['meta']['highlight_label_border_color'] ) ) {
					$counter_style .= 'border-color:' . $menu_item['meta']['highlight_label_border_color'] . ';';
				}

				if ( isset( $menu_item['meta']['highlight_label_color'] ) && ! empty( $menu_item['meta']['highlight_label_color'] ) ) {
					$counter_style .= 'color:' . $menu_item['meta']['highlight_label_color'] . ';';
				}
				$counter_style = '' === $counter_style ? '' : ' style="' . $counter_style . '"';

				// Output with custom styling.
				$fragments[ '.menu-item-' . $menu_item['item']['post_id'] . ' > a .fusion-widget-cart-number' ] = '<span class="fusion-widget-cart-number"' . $counter_style . ' data-digits="' . strlen( $cart_contents_count ) . '" data-cart-count="' . esc_attr( $cart_contents_count ) . '">' . $cart_contents_count . '</span>';
			}

			// Dropdown.
			if ( isset( $menu_item['meta']['show_woo_cart_contents'] ) && 'yes' === $menu_item['meta']['show_woo_cart_contents'] ) {
				$fragments[ '.menu-item-' . $menu_item['item']['post_id'] . ' > .sub-menu' ] = avada_menu_element_woo_cart();
			}
		}

		return $fragments;
	}

	/**
	 * Opens a div.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function single_product_summary_open() {
		echo '<div class="summary-container">';
	}

	/**
	 * Closes the div.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function single_product_summary_close() {
		echo '</div>';
	}

	/**
	 * Markup to add after the summary on single products.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function after_single_product_summary() {
		get_template_part( 'templates/wc-after-single-product-summary' );
	}

	/**
	 * Add related products.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function output_related_products() {
		global $post;

		$number_of_columns = fusion_get_page_option( 'number_of_related_products', $post->ID );
		if ( in_array( $number_of_columns, [ 'default', '' ] ) || ! $number_of_columns ) { // phpcs:ignore WordPress.PHP.StrictInArray
			$number_of_columns = Avada()->settings->get( 'woocommerce_related_columns' );
		}

		if ( '0' === $number_of_columns ) {
			return;
		}

		$args = [
			'posts_per_page' => $number_of_columns,
			'columns'        => $number_of_columns,
			'orderby'        => 'rand',
		];

		echo '<div class="fusion-clearfix"></div>';
		woocommerce_related_products( apply_filters( 'woocommerce_output_related_products_args', $args ) );
	}

	/**
	 * Displays upsells.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function upsell_display() {

		global $product, $post;

		$upsells = $product->get_upsell_ids();

		if ( 0 === count( $upsells ) ) {
			return;
		}

		$number_of_columns = fusion_get_page_option( 'number_of_related_products', $post->ID );
		if ( in_array( $number_of_columns, [ 'default', '' ] ) || ! $number_of_columns ) { // phpcs:ignore WordPress.PHP.StrictInArray
			$number_of_columns = Avada()->settings->get( 'woocommerce_related_columns' );
		}

		if ( '0' === $number_of_columns ) {
			return;
		}

		echo '<div class="fusion-clearfix"></div>';
		woocommerce_upsell_display( - 1, $number_of_columns );
	}

	/**
	 * Add markup before the cart table.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args Not really used here.
	 */
	public function before_cart_table( $args ) {
		?>
		<div class="woocommerce-content-box full-width clearfix">
				<?php /* translators: Number. */ ?>
				<h2><?php printf( esc_attr( _n( 'You Have %s Item In Your Cart', 'You Have %s Items In Your Cart', WC()->cart->get_cart_contents_count(), 'Avada' ) ), esc_html( number_format_i18n( WC()->cart->get_cart_contents_count() ) ) ); ?></h2>
			<?php
	}

	/**
	 * Adds markup after the cart table.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args Not used here.
	 */
	public function after_cart_table( $args ) {
		echo '</div>';
	}

	/**
	 * Adds coupon code form.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The formarguments.
	 */
	public function cart_collaterals( $args ) {
		get_template_part( 'templates/wc-cart-collaterals' );
	}

	/**
	 * Displays cross-sell.
	 *
	 * @access public
	 * @since 5.1.0
	 */
	public function cross_sell_display() {
		$crosssells        = WC()->cart->get_cross_sells();
		$number_of_columns = Avada()->settings->get( 'woocommerce_related_columns' );

		if ( 0 === count( $crosssells ) || '0' === $number_of_columns ) {
			return;
		}

		woocommerce_cross_sell_display( apply_filters( 'woocommerce_cross_sells_total', - 1 ), $number_of_columns );
	}

	/**
	 * Adds coupon form in the checkout page.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The form arguments.
	 */
	public function checkout_coupon_form( $args ) {
		include locate_template( 'templates/wc-checkout-coupon-form.php' );
	}

	/**
	 * Markup to add before the checkout form.
	 *
	 * @param array $args Not used in this context.
	 */
	public function before_checkout_form( $args ) {
		include locate_template( 'templates/wc-before-checkout-form.php' );
	}

	/**
	 * Closes the div after the checkout form.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The arguments (not used here).
	 */
	public function after_checkout_form( $args ) {
		echo '</div>';
	}

	/**
	 * Markup to add before the customer details form.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The form arguments. Not used in the context of this function.
	 */
	public function checkout_before_customer_details( $args ) {
		global $woocommerce;

		if ( WC()->cart->needs_shipping() && ! wc_ship_to_billing_address_only() || apply_filters( 'woocommerce_enable_order_notes_field', get_option( 'woocommerce_enable_order_comments', 'yes' ) === 'yes' ) && ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) ) {
			return;
		}
		echo '<div class="avada-checkout-no-shipping">';
	}

	/**
	 * Adds markup after the customer details form.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The form arguments. Not used in the context of this function.
	 */
	public function checkout_after_customer_details( $args ) {
		global $woocommerce;

		if ( WC()->cart->needs_shipping() && ! wc_ship_to_billing_address_only() || apply_filters( 'woocommerce_enable_order_notes_field', get_option( 'woocommerce_enable_order_comments', 'yes' ) === 'yes' ) && ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) ) {
			echo '<div class="clearboth"></div>';
		} else {
			echo '<div class="clearboth"></div></div>';
		}
		echo '<div class="woocommerce-content-box full-width">';
	}

	/**
	 * Add checkout billing markup.
	 *
	 * @param array $args The form arguments. Not used in the context of this function.
	 */
	public function checkout_billing( $args ) {
		global $woocommerce;

		$data_name = 'order_review';
		if ( WC()->cart->needs_shipping() && ! wc_ship_to_billing_address_only() || apply_filters( 'woocommerce_enable_order_notes_field', get_option( 'woocommerce_enable_order_comments', 'yes' ) === 'yes' ) && ( ! WC()->cart->needs_shipping() || wc_ship_to_billing_address_only() ) ) {
			$data_name = 'col-2';
		}
		?>
		<?php if ( ! Avada()->settings->get( 'woocommerce_one_page_checkout' ) ) : ?>
			<a data-name="<?php echo esc_attr( $data_name ); ?>" href="#" class="fusion-button button-default fusion-button-default-size button continue-checkout">
				<?php esc_attr_e( 'Continue', 'Avada' ); ?>
			</a>
			<div class="clearboth"></div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Add checkout shipping markup.
	 *
	 * @access public
	 * @since 5.1.0
	 * @param array $args The form arguments. Not used in the context of this function.
	 */
	public function checkout_shipping( $args ) {
		?>
		<?php if ( ! Avada()->settings->get( 'woocommerce_one_page_checkout' ) ) : ?>
			<a data-name="order_review" href="#" class="fusion-button button-default fusion-button-default-size continue-checkout button">
				<?php esc_attr_e( 'Continue', 'Avada' ); ?>
			</a>
			<div class="clearboth"></div>
		<?php endif; ?>
		<?php
	}

	/**
	 * Determines if we should enable order notes or not.
	 *
	 * @access public
	 * @since 5.1.0
	 * @return bool
	 */
	public function enable_order_notes_field() {
		return ( ! Avada()->settings->get( 'woocommerce_enable_order_notes' ) ) ? 0 : 1;
	}

	/**
	 * Remove columns and rows option from WooCommerce customizer panel.
	 *
	 * @access public
	 * @since 5.4.2
	 * @param int $cols Number of columns.
	 * @return int
	 */
	public function remove_woo_customizer_columns( $cols ) {
		return $cols;
	}

	/**
	 * Add notice to WooCommerce customizer panel.
	 *
	 * @access public
	 * @since 5.4.2
	 * @param Object $wp_customize Customizer object.
	 * @return void
	 */
	public function add_woocommerce_customizer_notice( $wp_customize ) {
		$wp_customize->add_control(
			'avada_woocommerce_column_notice',
			[
				'label'       => __( 'NOTE', 'Avada' ),
				/* translators: WC Customizer notice. */
				'description' => sprintf( __( 'You can control the <a href="%1$s" target="_blank">number of products per page</a> and the <a href="%2$s" target="_blank">number of columns for the main shop page</a> from Avada\`s Global Options panel.', 'Avada' ), Avada()->settings->get_setting_link( 'woo_items' ), Avada()->settings->get_setting_link( 'woocommerce_shop_page_columns' ) ),
				'section'     => 'woocommerce_product_catalog',
				'settings'    => 'woocommerce_default_catalog_orderby',
				'type'        => 'hidden',

			]
		);
	}

	/**
	 * Filters the single product shortcode and sets amount of columns to 1.
	 *
	 * @access public
	 * @since 5.5
	 * @param array  $out       The output array of shortcode attributes.
	 * @param array  $pairs     The supported attributes and their defaults.
	 * @param array  $atts      The user defined shortcode attributes.
	 * @param string $shortcode The shortcode name.
	 *
	 * @return array $out The attribute output array.
	 */
	public function change_product_shortcode_atts( $out, $pairs, $atts, $shortcode ) {
		if ( ! isset( $atts['columns'] ) ) {
			$out['columns'] = '1';
		}
		return $out;
	}

	/**
	 * Creates the quick view container.
	 *
	 * @access public
	 * @since 6.1
	 *
	 * @return void
	 */
	public function quick_view_load_container() {
		get_template_part( 'templates/wc-quick-view-container' );
	}

	/**
	 * Creates the product quick view.
	 *
	 * @access public
	 * @since 6.1
	 *
	 * @return void
	 */
	public function quick_view_load_product() {
		global $post, $product, $woocommerce;

		check_ajax_referer( 'fusion_quick_view_nonce', 'nonce' );

		if ( isset( $_POST['product'] ) ) {
			$product_id = sanitize_text_field( wp_unslash( $_POST['product'] ) );
			$post       = get_post( $product_id ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride
			$product    = wc_get_product( $product_id );

			ob_start();

			get_template_part( 'templates/wc-quick-view-product' );

			$output = ob_get_contents();
			ob_end_clean();
			echo $output; // phpcs:ignore WordPress.Security.EscapeOutput
		}

		wp_die();
	}

	/**
	 * Enqueue scripts for quick view.
	 *
	 * @access public
	 * @since 6.1
	 *
	 * @return void
	 */
	public function quick_view_enqueue_scripts() {
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// WooCommerce Bundled Products plugin, load scripts and styles.
		if ( class_exists( 'WC_PB_QV_Compatibility' ) && ! is_cart() && ! is_checkout() ) {
			do_action( 'wc_quick_view_enqueue_scripts' );
		}
	}

	/**
	 * Filter the default catalog ordering value.
	 *
	 * @access public
	 * @since 6.2.2
	 *
	 * @param string $default_order The default order.
	 * @return string The filtered default oder.
	 */
	public function woocommerce_default_catalog_orderby( $default_order ) {
		if ( ! $default_order ) {
			$default_order = 'menu_order';
		}

		return $default_order;
	}

	/**
	 * Get avada WC localize script vars.
	 *
	 * @access public
	 * @since 7.2
	 *
	 * @return array The localize WC vars.
	 */
	public static function get_avada_wc_vars() {
		$shop_page_bg_color = fusion_get_option( 'content_bg_color', 'content_bg_color', get_option( 'woocommerce_shop_page_id' ) );

		return [
			'order_actions'                 => __( 'Details', 'Avada' ),
			'title_style_type'              => Avada()->settings->get( 'title_style_type' ),
			'woocommerce_shop_page_columns' => Avada()->settings->get( 'woocommerce_shop_page_columns' ),
			'woocommerce_checkout_error'    => esc_attr__( 'Not all fields have been filled in correctly.', 'Avada' ),
			'related_products_heading_size' => ( false === avada_is_page_title_bar_enabled( get_the_ID() ) ? '2' : '3' ),
			'ajaxurl'                       => admin_url( 'admin-ajax.php' ),
			'shop_page_bg_color'            => $shop_page_bg_color,
			'shop_page_bg_color_lightness'  => Fusion_Color::new_color( $shop_page_bg_color )->lightness,
			'post_title_font_size'          => Fusion_Sanitize::convert_font_size_to_px( Avada()->settings->get( 'post_title_typography', 'font-size' ), Avada()->settings->get( 'post_title_typography', 'font-size' ) ),
		];
	}

	/**
	 * Does stuff necessary for sale badge text filtering.
	 *
	 * @access public
	 * @since 7.2
	 * @return void
	 */
	public function prepare_sale_flash() {

		$this->sale_text = Avada()->settings->get( 'woo_sale_badge_text' );

		if ( '' !== $this->sale_text ) {

			if ( false !== strpos( $this->sale_text, '[percentage]' ) ) {
				$this->used_sale_text_placeholders['percentage'] = true;
			}

			if ( false !== strpos( $this->sale_text, '[value]' ) ) {
				$this->used_sale_text_placeholders['value'] = true;
			}

			add_filter( 'woocommerce_sale_flash', [ $this, 'modify_sale_badge' ], 20, 3 );
		}
	}

	/**
	 * Filter sale flash.
	 *
	 * @access public
	 * @since 7.2
	 * @param string $html    The badge html.
	 * @param object $post    The post object.
	 * @param object $product The product object.
	 * @return string
	 */
	public function modify_sale_badge( $html, $post, $product ) {

		$sale_text = $this->sale_text;

		// Calc percentage.
		if ( true === $this->used_sale_text_placeholders['percentage'] ) {
			$sale_text = str_replace( '[percentage]', fusion_library()->woocommerce->calc_product_discount( $product ), $sale_text );
		}

		// Calc value.
		if ( true === $this->used_sale_text_placeholders['value'] ) {
			$sale_text = str_replace( '[value]', fusion_library()->woocommerce->calc_product_discount( $product, 'value' ), $sale_text );
		}

		return '<span class="onsale">' . $sale_text . '</span>';
	}

	/**
	 * Adds JS to supress variation events.
	 *
	 * @access public
	 * @since 7.3
	 * @return void
	 */
	public function supress_variation_events() {
		if ( ! is_product() ) {
			return;
		}
		?>
		<script>
		document.addEventListener( 'DOMContentLoaded', fusionSupressVariationEvents );
		function fusionSupressVariationEvents() {
			jQuery( 'form.variations_form' ).on( 'show_variation', function( event ) {
				if ( jQuery( event.target ).hasClass( 'avada-variation' ) ) {
					event.stopImmediatePropagation();
				}
			} );
		}
		</script>
		<?php
	}

	/**
	 * Restore page templates for shop page.
	 *
	 * @access public
	 * @since 7.3
	 * @param array   $page_templates Templates array.
	 * @param string  $theme Classname.
	 * @param WP_Post $post The current post object.
	 * @return array
	 */
	public function load_theme_page_templates( $page_templates, $theme, $post ) {
		$shop_page_id = wc_get_page_id( 'shop' );

		if ( $post && absint( $post->ID ) === $shop_page_id ) {
			$post_type     = get_post_type( $post );
			$all_templates = wp_get_theme()->get_post_templates();
			if ( isset( $all_templates[ $post_type ] ) ) {
				$page_templates = array_merge(
					[
						'default' => apply_filters( 'default_page_template_title', esc_html__( 'Default Template', 'fusion-builder' ), 'meta-box' ),
					],
					$all_templates[ $post_type ]
				);
			}
		}
		return $page_templates;
	}

	/**
	 * Load general template on shop page.
	 *
	 * @access public
	 * @since 7.3
	 * @param string $template Template string.
	 * @return string
	 */
	public function shop_template_loader( $template ) {
		global $wp_query;
		$shop_page_id = wc_get_page_id( 'shop' );
		if ( ( ! is_search() && is_post_type_archive( 'product' ) ) || is_page( $shop_page_id ) ) {
			$wp_query->queried_object    = $wp_query->post = $GLOBALS['post'] = get_post( wc_get_page_id( 'shop' ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
			$wp_query->queried_object_id = (int) $wp_query->queried_object->ID;
			$wp_query->is_singular       = 1;
			$wp_query->posts             = [ $wp_query->queried_object ];
			$wp_query->post_count        = 1;
			return get_page_template();
		}
		return $template;
	}

	/**
	 * Check whether show WC shop loop is enabled.
	 *
	 * @access public
	 * @since 7.3
	 * @return boolean
	 */
	public function is_wc_shop_loop_enabled() {

		// Early exit if shop page is not set.
		if ( -1 === (int) wc_get_page_id( 'shop' ) ) {
			return false;
		}

		$return = 'no' !== fusion_data()->post_meta( wc_get_page_id( 'shop' ) )->get( 'show_wc_shop_loop' );
		if ( fusion_is_preview_frame() && Fusion_App()->is_full_refresh() ) {
			$meta_values = Fusion_App()->get_data( 'meta_values' );
			if ( isset( $meta_values['_fusion'] ) && isset( $meta_values['_fusion']['show_wc_shop_loop'] ) ) {
				$return = 'no' !== $meta_values['_fusion']['show_wc_shop_loop'];
			}
		}
		return $return;
	}
}
