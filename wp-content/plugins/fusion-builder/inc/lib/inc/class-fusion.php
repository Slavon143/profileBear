<?php
/**
 * The main Fusion library object.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * The main Fusion library object.
 */
class Fusion {

	/**
	 * The one, true instance of the object.
	 *
	 * @static
	 * @access public
	 * @var null|object
	 */
	public static $instance = null;

	/**
	 * The current page ID.
	 *
	 * @access public
	 * @var bool|int
	 */
	public static $c_page_id = false;

	/**
	 * An instance of the Fusion_Images class.
	 *
	 * IMPORTANT NOTE: Use the get_images_obj() method to get this.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Images
	 */
	public $images;

	/**
	 * An instance of the Fusion_Multilingual class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Multilingual
	 */
	public $multilingual;

	/**
	 * An instance of the Fusion_Scripts class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Scripts
	 */
	public $scripts;

	/**
	 * An instance of the Fusion_Panel class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Scripts
	 */
	public $panel;

	/**
	 * An instance of the Fusion_Dynamic_JS class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Dynamic_JS
	 */
	public $dynamic_js;

	/**
	 * An instance of the Fusion_Font_Awesome class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Font_Awesome
	 */
	public $fa;

	/**
	 * Fusion_Social_Sharing.
	 *
	 * @access public
	 * @since 1.9.2
	 * @var object
	 */
	public $social_sharing;

	/**
	 * An instance of the Fusion_Media_Query_Scripts class.
	 *
	 * @access public
	 * @since 1.0.0
	 * @var Fusion_Media_Query_Scripts
	 */
	public $mq_scripts;

	/**
	 * An instance of the Fusion_WooCommerce class.
	 *
	 * @access public
	 * @since 3.2
	 * @var Fusion_WooCommerce
	 */
	public $woocommerce;

	/**
	 * Page conditions that have been evaluated.
	 *
	 * @access public
	 * @since 3.3
	 * @var array
	 */
	public $conditional_loading = [];

	/**
	 * An instance of the Fusion_Device_Detection class.
	 *
	 * @access public
	 * @since 3.4
	 * @var Fusion_Device_Detection
	 */
	public $device_detection;

	/**
	 * The class constructor
	 */
	private function __construct() {
		add_action( 'wp', [ $this, 'set_page_id' ] );
		add_action( 'plugins_loaded', [ $this, 'multilingual_data' ] );

		if ( ! defined( 'AVADA_VERSION' ) && ! FUSION_LIBRARY_DEV_MODE ) {
			$this->images = new Fusion_Images();
		}

		$this->sanitize         = new Fusion_Sanitize();
		$this->scripts          = new Fusion_Scripts();
		$this->dynamic_js       = new Fusion_Dynamic_JS();
		$this->mq_scripts       = new Fusion_Media_Query_Scripts();
		$this->fa               = new Fusion_Font_Awesome();
		$this->social_sharing   = new Fusion_Social_Sharing();
		$this->device_detection = new Fusion_Device_Detection();

		$widget_framework = AWB_Widget_Framework::get_instance();

		// To early for class_exists( 'WooCommerce' ) check.
		$this->woocommerce = new Fusion_WooCommerce();

		if ( $this->supported_plugins_changed() && class_exists( 'Fusion_Cache' ) ) {
			$fusion_cache = new Fusion_Cache();
			$fusion_cache->reset_all_caches();
		}

		if ( is_admin() ) {
			new Fusion_Privacy();
		}

		add_action( 'admin_body_class', [ $this, 'admin_body_class' ] );

		add_action( 'wp_head', [ $this, 'add_analytics_code' ], 10000 );

		add_action( 'wp_loaded', [ $this, 'add_styles' ] );

		// Add needed action and filter to make sure queries with offset have correct pagination.
		add_action( 'pre_get_posts', [ $this, 'query_offset' ], 1 );
		add_filter( 'found_posts', [ $this, 'adjust_offset_pagination' ], 1, 2 );

		add_action( 'wp_loaded', [ $this, 'clear_object_cache' ] );

		// Rename default template.
		add_filter( 'default_page_template_title', [ $this, 'default_page_template_title' ] );

		// Set default template.
		add_action( 'add_meta_boxes', [ $this, 'set_default_teamplte' ], 1 );
	}

	/**
	 * Access the single instance of this class.
	 *
	 * @return Fusion
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Gets the current page ID.
	 *
	 * @return string The current page ID.
	 */
	public function get_page_id() {
		if ( ! self::$c_page_id ) {
			$this->set_page_id();
		}
		return apply_filters( 'fusion-page-id', self::$c_page_id ); // phpcs:ignore WordPress.NamingConventions.ValidHookName
	}

	/**
	 * Sets the current page ID.
	 *
	 * @uses self::c_page_id
	 */
	public function set_page_id() {
		if ( ! self::$c_page_id ) {
			self::$c_page_id = self::c_page_id();
		}
	}

	/**
	 * Gets the current page ID.
	 *
	 * @return bool|int
	 */
	private static function c_page_id() {
		global $wp_query;
		if ( get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) && is_home() ) {
			return get_option( 'page_for_posts' );
		}

		if ( ! $wp_query ) {
			return false;
		}

		$c_page_id = get_queried_object_id();
		if ( ( function_exists( 'fusion_is_preview_frame' ) && fusion_is_preview_frame() ) || ( function_exists( 'fusion_is_builder_frame' ) && fusion_is_builder_frame() ) ) {
			$page_id   = isset( $_POST['post_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$c_page_id = $page_id ? $page_id : $c_page_id;
		}

		// The WooCommerce shop page.
		if ( ! is_admin() && class_exists( 'WooCommerce' ) && is_shop() ) {
			return (int) get_option( 'woocommerce_shop_page_id' );
		}
		// The WooCommerce product_cat taxonomy page.
		if ( ! is_admin() && class_exists( 'WooCommerce' ) && ( ! is_shop() && ( is_tax( 'product_cat' ) || is_tax( 'product_tag' ) ) ) ) {
			return $c_page_id . '-archive'; // So that other POs do not apply to arhives if post ID matches.
		}
		// The homepage.
		if ( 'posts' === get_option( 'show_on_front' ) && is_home() ) {
			return $c_page_id;
		}
		if ( ! is_singular() && is_archive() ) {
			return $c_page_id . '-archive'; // So that other POs do not apply to arhives if post ID matches.
		}
		if ( ! is_singular() ) {
			return false;
		}
		return $c_page_id;
	}
	/**
	 * Gets the value of a Global Option.
	 *
	 * @static
	 * @access public
	 * @param string|null               $option  The option.
	 * @param string|false              $subset  The sub-option in case of an array.
	 * @param string|array|null|boolean $default The default fallback value.
	 */
	public function get_option( $option = null, $subset = false, $default = null ) {

		$fusion_settings = awb_get_fusion_settings();

		return $fusion_settings->get( $option, $subset, $default );
	}

	/**
	 * Check if the supported plugins array has changed.
	 * If a supported plugin was activated or deactivated
	 * we should reset all caches.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @return bool True if changed, false if unchanged.
	 */
	protected function supported_plugins_changed() {
		$classes_to_check   = [
			'WPCF7',
			'bbPress',
			'WooCommerce',
			'Tribe__Events__Main',
		];
		$constants_to_check = [
			'LS_PLUGIN_VERSION',
			'RS_PLUGIN_PATH',
		];

		$supported_saved    = get_option( 'fusion_supported_plugins_active', [] );
		$supported_detected = [];
		foreach ( $classes_to_check as $class ) {
			if ( class_exists( $class ) ) {
				$supported_detected[] = $class;
			}
		}
		foreach ( $constants_to_check as $constant ) {
			if ( defined( $constant ) ) {
				$supported_detected[] = $constant;
			}
		}
		if ( $supported_detected !== $supported_saved ) {
			update_option( 'fusion_supported_plugins_active', $supported_detected );
			return true;
		}
		return false;
	}

	/**
	 * Adds classes to the <body> element using admin_body_class filter.
	 *
	 * @access public
	 * @since 1.3.0
	 * @param string $classes The CSS classes.
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		global $wp_version;
		if ( version_compare( $wp_version, '4.9-beta', '<' ) ) {
			$classes .= ' fusion-colorpicker-legacy ';
		}
		return $classes;
	}

	/**
	 * Adds analytics code.
	 *
	 * @access public
	 * @since 1.9.2
	 * @return void
	 */
	public function add_analytics_code() {
		/**
		 * The setting below is not sanitized. In order to be able to take advantage of this,
		 * a user would have to gain access to the database or the filesystem to add a new filter,
		 * in which case this is the least of your worries.
		 */
		echo apply_filters( 'fusion_google_analytics', $this->get_option( 'google_analytics' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Add Multilingual Data.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function multilingual_data() {
		$this->multilingual = new Fusion_Multilingual();
	}

	/**
	 * Gets the Fusion_Images object.
	 *
	 * NOTE: Do not remove, needed for users updating from 6.1.2.
	 *
	 * @since 2.2.0
	 * @return Fusion_Images
	 */
	public function get_images_obj() {
		if ( ! $this->images ) {
			$this->images = new Fusion_Images();
		}
		return $this->images;
	}

	/**
	 * Adds offset to the query.
	 *
	 * @since 2.2
	 * @param object $query The query.
	 */
	public function query_offset( $query ) {
		// Check if we are in a blog shortcode query and if offset is set.
		if ( is_admin() || ( is_object( $query ) && ( $query->is_main_query() || is_array( $query->query ) && ! isset( $query->query['blog_sc_query'] ) && ! isset( $query->query['portfolio_sc_query'] ) && ! isset( $query->query['post_cards_query'] ) ) ) || ! isset( $query->query['offset'] ) ) {
			return;
		}

		// The query is paged.
		if ( $query->is_paged ) {
			// Manually determine page query offset (offset + ( current page - 1 ) x posts per page ).
			$page_offset = (int) $query->query['offset'] + ( ( $query->query_vars['paged'] - 1 ) * $query->query['posts_per_page'] );

			// Apply adjusted page offset.
			$query->set( 'offset', $page_offset );

			// This is the first page, so we can just use the offset.
		} else {
			$query->set( 'offset', $query->query['offset'] );
		}
	}


	/**
	 * Adds an offset to the pagination.
	 *
	 * @since 2.2
	 * @param int    $found_posts How many posts we found.
	 * @param object $query       The query.
	 * @return int
	 */
	public function adjust_offset_pagination( $found_posts, $query ) {
		// Modification only in a blog shortcode query with set offset.
		if ( ( isset( $query->query['blog_sc_query'] ) || isset( $query->query['portfolio_sc_query'] ) || isset( $query->query['post_cards_query'] ) ) && isset( $query->query['offset'] ) && '' !== $query->query['offset'] ) {
			// Reduce found_posts count by the offset.
			return $found_posts - $query->query['offset'];
		}
		return $found_posts;
	}

	/**
	 * Enqueue shared styles.
	 *
	 * @since 3.4
	 * @return void
	 */
	public function add_styles() {

		if ( apply_filters( 'avada_load_icomoon', true ) ) {
			Fusion_Dynamic_CSS::enqueue_style( FUSION_LIBRARY_PATH . '/assets/css/icomoon.min.css', FUSION_LIBRARY_URL . '/assets/css/icomoon.min.css' );
		}
	}

	/**
	 * Clear the object cache ob post save and removal.
	 *
	 * @access public
	 * @since 7.8
	 * @return void
	 */
	public function clear_object_cache() {
		if ( '1' === $this->get_option( 'clear_object_cache' ) ) {
			add_action( 'save_post', 'wp_cache_flush' );
			add_action( 'delete_post', 'wp_cache_flush' );
		}
	}

	/**
	 * Renames default page template title.
	 *
	 * @since 7.8
	 * @return string
	 */
	public function default_page_template_title() {
		return __( 'Site Width', 'fusion-builder' );
	}

	/**
	 * Sets default template.
	 *
	 * @since 7.8
	 * @return void
	 */
	public function set_default_teamplte() {
		global $post;

		if ( 'page' === $post->post_type && '' === $post->page_template && function_exists( 'Avada' ) && '100_width' === Avada()->settings->get( 'page_template' ) ) {
			$post->page_template = '100-width.php';
		}
	}
}
