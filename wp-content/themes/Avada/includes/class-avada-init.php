<?php
/**
 * Initializes Avada basic components.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      3.8
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Initializes Avada basic components.
 */
class Avada_Init {

	/**
	 * Constructor.
	 *
	 * @access  public
	 */
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, 'set_builder_status' ], 10 );
		add_action( 'after_setup_theme', [ $this, 'add_theme_supports' ], 10 );
		add_action( 'after_setup_theme', [ $this, 'register_nav_menus' ] );
		add_action( 'after_setup_theme', [ $this, 'add_image_size' ] );
		add_filter( 'image_size_names_choose', [ $this, 'add_image_sizes_to_media_library_dialog' ] );
		add_action( 'init', [ $this, 'init' ] );

		if ( class_exists( 'BuddyPress' ) && ! Fusion_Helper::is_buddypress() ) {
			add_action( 'init', [ $this, 'remove_buddypress_redirection' ], 5 );
		}

		if ( class_exists( 'Convert_Plug' ) ) {
			add_action( 'init', [ $this, 'remove_convert_plus_notices' ] );
		}

		if ( class_exists( 'GF_User_Registration_Bootstrap' ) ) {
			add_action( 'init', [ $this, 'change_gravity_user_registration_priority' ] );
		}

		// Init FPO for Event Espresso plugin.
		if ( class_exists( 'EE_Calendar' ) ) {
			add_filter( 'fusion_page_options_init', [ $this, 'init_fusion_page_option_for_event_espresso' ] );
		}

		// Allow shortcodes in widget text.
		add_filter( 'widget_text', 'do_shortcode' );

		add_filter( 'wp_nav_menu_args', [ $this, 'main_menu_args' ], 5 );
		add_action( 'after_switch_theme', [ $this, 'theme_activation' ] );
		add_action( 'switch_theme', [ $this, 'theme_deactivation' ] );

		// Term meta migration for WordPress 4.4.
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );

		// Remove post_format from preview link.
		add_filter( 'preview_post_link', [ $this, 'remove_post_format_from_link' ], 9999 );

		add_filter( 'wp_tag_cloud', [ $this, 'remove_font_size_from_tagcloud' ] );

		// Add contact methods for author page.
		add_filter( 'user_contactmethods', [ $this, 'modify_contact_methods' ] );

		add_filter( 'wpcf7_form_response_output', [ $this, 'modify_wpcf7_notices' ], 10, 4 );

		if ( ! is_admin() ) {
			add_filter( 'pre_get_posts', [ $this, 'modify_search_filter' ] );
			add_filter( 'pre_get_posts', [ $this, 'empty_search_filter' ] );
			add_filter( 'posts_search', [ $this, 'limit_search_to_title_only' ], 500, 2 );
			add_filter( 'search_form_after_fields', [ $this, 'force_product_archive_load' ] );
		}

		// Check if we've got a task to remove backup data.
		if ( false !== get_option( 'scheduled_avada_fusionbuilder_migration_cleanups', false ) ) {
			add_action( 'init', [ 'Fusion_Builder_Migrate', 'cleanup_backups' ] );
		}

		add_action( 'wp_footer', [ $this, 'add_wp_footer_scripts' ], 9999 );

		// Live Search.
		add_action( 'wp_ajax_live_search_retrieve_posts', [ $this, 'live_search_retrieve_posts' ] );
		add_action( 'wp_ajax_nopriv_live_search_retrieve_posts', [ $this, 'live_search_retrieve_posts' ] );

		add_filter( 'fusion_google_maps_api_key', 'normalize_whitespace' );
	}

	/**
	 * Formats the locale.
	 *
	 * @access  public
	 * @param  string $locale The language locale.
	 * @param  string $domain The textdomain.
	 * @return  string
	 */
	public function change_locale( $locale, $domain ) {
		return $domain . '-' . $locale;
	}

	/**
	 * Formats the locale using lowercase characters.
	 *
	 * @access  public
	 * @param  string $locale The language locale.
	 * @param  string $domain The textdomain.
	 * @return  string
	 */
	public function change_locale_lowercase( $locale, $domain ) {
		return strtolower( $domain ) . '-' . $locale;
	}

	/**
	 * Conditionally add theme_support for fusion_builder.
	 *
	 * @access  public
	 */
	public function set_builder_status() {
		$builder_settings = get_option( 'fusion_builder_settings', [] );

		if ( isset( $builder_settings['enable_builder_ui'] ) && $builder_settings['enable_builder_ui'] ) {
			add_theme_support( 'fusion_builder' );
		}
	}

	/**
	 * Stores the theme version in the options table in the WordPress database.
	 *
	 * @access  public
	 */
	public function set_theme_version() {
		if ( function_exists( 'wp_get_theme' ) ) {
			$theme_obj     = wp_get_theme();
			$theme_version = $theme_obj->get( 'Version' );

			if ( $theme_obj->parent_theme ) {
				$template_dir  = basename( Avada::$template_dir_path );
				$theme_obj     = wp_get_theme( $template_dir );
				$theme_version = $theme_obj->get( 'Version' );
			}

			update_option( 'avada_theme_version', $theme_version );
		}

	}

	/**
	 * Additional actions that need to run on init.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function init() {
		Avada::get_options();
	}

	/**
	 * Add theme_supports.
	 *
	 * @access  public
	 */
	public function add_theme_supports() {

		// Default WP generated title support.
		add_theme_support( 'title-tag' );

		// Default RSS feed links.
		add_theme_support( 'automatic-feed-links' );

		// Default custom header.
		add_theme_support( 'custom-header' );

		// Default custom backgrounds.
		add_theme_support( 'custom-background' );

		/**
		 * WooCommerce Support.
		 * Add theme support for zoom and lightbox generally.
		 * For legacy layouts it is disabled in class-avada-woocommerce.php.
		 * For layout section products it is in class-fusion-woocommerce.php and in the element file.
		 */
		add_theme_support(
			'woocommerce',
			[
				'gallery_thumbnail_image_width' => (int) Avada()->settings->get( 'woocommerce_gallery_thumbnail_width' ),
			]
		);

		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-slider' );
		add_theme_support( 'wc-product-gallery-lightbox' );

		// Post Formats.
		add_theme_support( 'post-formats', [ 'gallery', 'link', 'image', 'quote', 'video', 'audio', 'chat' ] );
		// Add post thumbnail functionality.
		add_theme_support( 'post-thumbnails' );

		// Add Avada Builder Demos support.
		add_theme_support( 'fusion-builder-demos' );

	}

	/**
	 * Add image sizes.
	 *
	 * @access  public
	 */
	public function add_image_size() {
		add_image_size( 'blog-large', 669, 272, true );
		add_image_size( 'blog-medium', 320, 202, true );
		add_image_size( 'recent-posts', 700, 441, true );
		add_image_size( 'recent-works-thumbnail', 66, 66, true );

		// Image sizes used for grid layouts.
		add_image_size( 'fusion-200', 200, '', false );
		add_image_size( 'fusion-400', 400, '', false );
		add_image_size( 'fusion-600', 600, '', false );
		add_image_size( 'fusion-800', 800, '', false );
		add_image_size( 'fusion-1200', 1200, '', false );
	}

	/**
	 * Add image sizes to WP Media Library Dialog.
	 *
	 * @since 5.3
	 * @access public
	 * @param array $sizes The image sizes already in the WP Meida Library Dialog.
	 * @return array Image sizes for WP Media Library Dialog.
	 */
	public function add_image_sizes_to_media_library_dialog( $sizes ) {
		/* translators: image size. */
		$sizes['fusion-200'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 200 );
		/* translators: image size. */
		$sizes['fusion-400'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 400 );
		/* translators: image size. */
		$sizes['fusion-600'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 600 );
		/* translators: image size. */
		$sizes['fusion-800'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 800 );
		/* translators: image size. */
		$sizes['fusion-1200'] = sprintf( esc_attr__( 'Avada Grid %s', 'Avada' ), 1200 );

		return $sizes;
	}

	/**
	 * Register navigation menus.
	 *
	 * @access  public
	 */
	public function register_nav_menus() {

		register_nav_menu( 'main_navigation', 'Main Navigation' );
		register_nav_menu( 'top_navigation', 'Top Navigation' );
		register_nav_menu( 'mobile_navigation', 'Mobile Navigation' );
		register_nav_menu( '404_pages', '404 Useful Pages' );
		register_nav_menu( 'sticky_navigation', 'Sticky Header Navigation' );

	}

	/**
	 * Theme activation actions.
	 *
	 * @access  public
	 */
	public function theme_activation() {

		update_option(
			'shop_catalog_image_size',
			[
				'width'  => 500,
				'height' => '',
				0,
			]
		);
		update_option(
			'shop_single_image_size',
			[
				'width'  => 700,
				'height' => '',
				0,
			]
		);
		update_option(
			'shop_thumbnail_image_size',
			[
				'width'  => 120,
				'height' => '',
				0,
			]
		);

		update_option( 'woocommerce_single_image_width', 700 );
		update_option( 'woocommerce_thumbnail_image_width', 500 );
		update_option( 'woocommerce_thumbnail_cropping', 'uncropped' );

		// Delete the patcher caches.
		delete_site_transient( 'fusion_patcher_check_num' );
		// Delete compiled JS.
		fusion_reset_all_caches();

	}

	/**
	 * Theme activation actions.
	 *
	 * @access  public
	 */
	public function theme_deactivation() {

		// Delete the patcher caches.
		delete_site_transient( 'fusion_patcher_check_num' );
		// Delete compiled JS.
		fusion_reset_all_caches();

	}

	/**
	 * WIP
	public function migrate_term_data() {
		$version = get_bloginfo( 'version' );
		$function_test = function_exists( 'add_term_meta' );
		if ( version_compare( $version, '4.4', '>=' ) && ! $function_test ) {}
	}
	 */

	/**
	 * Get the main menu arguments.
	 *
	 * @access public
	 * @param  array $args The arguments.
	 * @return  array The arguments modified.
	 */
	public function main_menu_args( $args ) {

		global $post;

		$c_page_id = Avada()->fusion_library->get_page_id();

		if ( fusion_get_page_option( 'displayed_menu', $c_page_id ) &&
			'default' !== fusion_get_page_option( 'displayed_menu', $c_page_id ) &&
			( 'main_navigation' === $args['theme_location'] || 'sticky_navigation' === $args['theme_location'] )
		) {
			$menu         = fusion_get_page_option( 'displayed_menu', $c_page_id );
			$args['menu'] = $menu;
		}

		return $args;

	}

	/**
	 * Inject some HTML to fix a youtube flash bug.
	 *
	 * @access  public
	 */
	public function youtube_flash_fix() {
		echo '<div class="fusion-youtube-flash-fix">&shy;<style type="text/css"> iframe { visibility: hidden; opacity: 0; } </style></div>';
	}

	/**
	 * Removes the post format from links.
	 *
	 * @access  public
	 * @param  string $url The URL to process.
	 * @return  string The URL with post_format stripped.
	 */
	public function remove_post_format_from_link( $url ) {
		$url = remove_query_arg( 'post_format', $url );
		return $url;
	}

	/**
	 * Removes font-sizes from the tagclouds.
	 *
	 * @param string $tagcloud The markup of tagclouds.
	 * @return string
	 */
	public function remove_font_size_from_tagcloud( $tagcloud ) {
		return preg_replace( '/ style=(["\'])[^\1]*?\1/i', '', $tagcloud, -1 );
	}

	/**
	 * Modifies user contact methods and adds some more social networks.
	 *
	 * @param array $profile_fields The profile fields.
	 * @return array The profile fields with additional contact methods.
	 */
	public function modify_contact_methods( $profile_fields ) {
		// Add new fields.
		$profile_fields['author_email']    = 'Email (Author Page)';
		$profile_fields['author_facebook'] = 'Facebook (Author Page)';
		$profile_fields['author_twitter']  = 'Twitter (Author Page)';
		$profile_fields['author_linkedin'] = 'LinkedIn (Author Page)';
		$profile_fields['author_dribble']  = 'Dribble (Author Page)';
		$profile_fields['author_whatsapp'] = 'WhatsApp (Author Page)';
		$profile_fields['author_custom']   = 'Custom Message (Author Page)';

		return $profile_fields;
	}

	/**
	 * Modifies the HTML for WPCF7 notices.
	 *
	 * @access public
	 * @since 5.5
	 * @param string            $output              The HTML.
	 * @param string            $class               The CSS classes that will be added to the element.
	 * @param string            $content             The notice content.
	 * @param WPCF7_ContactForm $contact_form_object An instance of the WPCF7_ContactForm object.
	 * @return string                                Notice HTML.
	 */
	public function modify_wpcf7_notices( $output, $class, $content, $contact_form_object ) {
		if ( shortcode_exists( 'fusion_alert' ) ) {
			return do_shortcode( '[fusion_alert class="' . $class . '" type="custom"]' . $content . '[/fusion_alert]' );
		}
		return $output;
	}

	/**
	 * Removes the BuddyPress redirection actions.
	 *
	 * @access public
	 */
	public function remove_buddypress_redirection() {
		remove_action( 'bp_init', 'bp_core_wpsignup_redirect' );
	}

	/**
	 * Removes admin notices from Convert Plus plugin.
	 *
	 * @since 5.4.1
	 * @access public
	 * @return void
	 */
	public function remove_convert_plus_notices() {
		if ( ! defined( 'BSF_PRODUCTS_NOTICES' ) ) {
			define( 'BSF_PRODUCTS_NOTICES', false );
		}
	}

	/**
	 * Changes the hook priority of the GF_User_Registration->maybe_activate_user() function.
	 *
	 * @since 5.1
	 * @access public
	 * @return void
	 */
	public function change_gravity_user_registration_priority() {
		remove_action( 'wp', [ gf_user_registration(), 'maybe_activate_user' ] );
		add_action( 'wp', [ gf_user_registration(), 'maybe_activate_user' ], 999 );
	}

	/**
	 * Adds needed argument to FPO loading if clause, so that options will be displayed on Events Espresso pages.
	 *
	 * @since 5.4.2
	 * @access public
	 * @param bool $additional_argument Additional argument.
	 * @return bool
	 */
	public function init_fusion_page_option_for_event_espresso( $additional_argument ) {
		global $pagenow;

		$additional_argument = 'admin.php' === $pagenow && isset( $_GET['page'] ) && 'espresso_events' === $_GET['page'] && isset( $_GET['action'] ) && 'edit' === $_GET['action'] && isset( $_GET['post'] ); // phpcs:ignore WordPress.Security.NonceVerification

		return $additional_argument;
	}

	/**
	 * Performs the live search for posts.
	 *
	 * @since 5.9
	 * @access public
	 * @return void.
	 */
	public function live_search_retrieve_posts() {

		if ( isset( $_POST['post_type'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			if ( 1 === count( $_POST['post_type'] ) && isset( $_POST['post_type'][0] ) && 'any' === $_POST['post_type'][0] ) { // phpcs:ignore WordPress.Security.NonceVerification
				$post_types = 'any';
			} else {
				$post_types = $_POST['post_type']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
			}
		} else {
			$post_types = $this->get_search_results_post_types();
		}

		$args = [
			's'                   => trim( strip_tags( $_POST['search'] ) ), // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification, WordPress.WP.AlternativeFunctions
			'post_type'           => $post_types,
			'posts_per_page'      => isset( $_POST['per_page'] ) ? (int) wp_unslash( $_POST['per_page'] ) : 10, // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput, WPThemeReview.CoreFunctionality.PostsPerPage.posts_per_page_posts_per_page, WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
			'post_status'         => 'publish',
			'ignore_sticky_posts' => 1,
		];

		if ( function_exists( 'wc_get_product_visibility_term_ids' ) && ( 'any' === $args['post_type'] || ( is_array( $args['post_type'] ) && in_array( 'product', $args['post_type'] ) ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			$product_visibility_term_ids = wc_get_product_visibility_term_ids();

			$args['tax_query']   = []; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			$args['tax_query'][] = [
				'taxonomy' => 'product_visibility',
				'field'    => 'term_taxonomy_id',
				'terms'    => $product_visibility_term_ids['exclude-from-search'],
				'operator' => 'NOT IN',
			];
		}

		$args = apply_filters( 'fusion_live_search_query_args', $args );

		$search_limit_to_post_titles = isset( $_POST['search_limit_to_post_titles'] ) ? $_POST['search_limit_to_post_titles'] : Avada()->settings->get( 'search_limit_to_post_titles' ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

		if ( $search_limit_to_post_titles ) {
			add_filter( 'posts_where', [ $this, 'limit_wp_query_to_title_only' ], 10, 2 );
			$search_results = fusion_cached_query( $args );
			remove_filter( 'posts_where', [ $this, 'limit_wp_query_to_title_only' ] );
		} else {
			$search_results = fusion_cached_query( $args );
		}

		$result_suggestions = [];
		if ( $search_results->have_posts() ) {
			$display_post_type      = isset( $_POST['display_post_type'] ) ? (bool) wp_unslash( $_POST['display_post_type'] ) : true; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			$display_featured_image = isset( $_POST['show_feat_img'] ) ? (bool) wp_unslash( $_POST['show_feat_img'] ) : true; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput

			while ( $search_results->have_posts() ) {
				$search_results->the_post();
				global $post;

				$post_type = '';
				if ( $display_post_type ) {
					$post_type_obj = get_post_type_object( get_post_type( $post->ID ) );
					$post_type     = ( $post_type_obj ) ? $post_type_obj->labels->singular_name : $post_type;
				}

				$result_suggestions[] = [
					'id'        => esc_attr( $post->ID ),
					'type'      => $post_type,
					'title'     => get_the_title( $post->ID ),
					'post_url'  => get_the_permalink( $post->ID ),
					'image_url' => $display_featured_image ? get_the_post_thumbnail_url( $post->ID, 'recent-works-thumbnail' ) : '',
				];
			}
		}

		wp_reset_postdata();

		wp_send_json( $result_suggestions );
	}

	/**
	 * Modifies the WP_Query SQL query to limit to post titles.
	 *
	 * @since 5.9
	 * @access public
	 * @param string $where    Search SQL for WHERE clause.
	 * @param object $wp_query The search query.
	 * @return string $where   The modified clause.
	 */
	public function limit_wp_query_to_title_only( $where, $wp_query ) {
		global $wpdb;

		$query_vars = $wp_query->query_vars;
		$title      = $wp_query->get( 's' );
		if ( $title ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $title ) ) . '%\'';
		}

		return $where;
	}

	/**
	 * Modifies the search SQL query to limit to post titles.
	 *
	 * @since 5.9
	 * @access public
	 * @param string $search   Search SQL for WHERE clause.
	 * @param object $wp_query The search query.
	 * @return string $where The modified clause.
	 */
	public function limit_search_to_title_only( $search, $wp_query ) {
		global $wpdb;

		$search_limit_to_post_titles = Avada()->settings->get( 'search_limit_to_post_titles' );

		// If there's an URL override replace the settings value.
		if ( isset( $_GET['search_limit_to_post_titles'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$search_limit_to_post_titles = $_GET['search_limit_to_post_titles']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		}

		// If there is no search term, skip process.
		if ( empty( $search ) || ! $search_limit_to_post_titles ) {
			return $search;
		}
		$query_vars = $wp_query->query_vars;
		$n          = ! empty( $query_vars['exact'] ) ? '' : '%';
		$search     = '';
		$searchand  = '';

		foreach ( (array) $query_vars['search_terms'] as $term ) {
			$term      = esc_sql( $wpdb->esc_like( $term ) );
			$search   .= "{$searchand}($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$searchand = ' AND ';
		}

		if ( ! empty( $search ) ) {
			$search = " AND ({$search}) ";

			if ( ! is_user_logged_in() ) {
				$search .= " AND ($wpdb->posts.post_password = '') ";
			}
		}

		return $search;
	}

	/**
	 * Modifies the search filter.
	 *
	 * @access public
	 * @param object $query The search query.
	 * @return object $query The modified search query.
	 */
	public function modify_search_filter( $query ) {
		if ( is_search() && $query->is_search && ! empty( $query->query['s'] ) ) {
			if ( isset( $_GET ) && ( 2 < count( $_GET ) || ( 2 === count( $_GET ) && ! isset( $_GET['lang'] ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return $query;
			}

			$query->set( 'post_type', $this->get_search_results_post_types() );
		}
		return $query;
	}

	/**
	 *  Adds hidden input field when 'Search Results Content' has only Products post type to force Woo archive load.
	 *
	 * @access public
	 * @since 7.2
	 * @param array $args Search form args.
	 * @return array
	 */
	public function force_product_archive_load( $args ) {
		$search_post_types = $this->get_search_results_post_types();
		if ( is_array( $search_post_types ) && 1 === count( $search_post_types ) && 'product' === $search_post_types[0] ) {
			$args['after_fields'] .= '<input type="hidden" value="product" name="post_type" />';
		}
		return $args;
	}

	/**
	 * Make WordPress respect the search template on an empty search.
	 *
	 * @param  object $query The WP_Query object.
	 * @return  object
	 */
	public function empty_search_filter( $query ) {

		if ( isset( $_GET['s'] ) && empty( $_GET['s'] ) && $query->is_main_query() ) { // phpcs:ignore WordPress.Security.NonceVerification
			$query->is_search = true;
			$query->is_home   = false;
		}

		return $query;

	}

	/**
	 * Add scripts to the wp_footer action hook.
	 *
	 * @since 5.3.1
	 * @access public
	 * @return void.
	 */
	public function add_wp_footer_scripts() {
		/**
		 * Echo the scripts added to the "before </body>" field in Global Options.
		 * The 'space_body' setting is not sanitized.
		 * In order to be able to take advantage of this,
		 * a user would have to gain access to the database
		 * in which case this is the least of your worries.
		 */
		echo Avada()->settings->get( 'space_body' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Gets the post types for search results filtering.
	 *
	 * @since 5.9.1
	 * @access public
	 * @return array|string The post types the search should be limited to.
	 */
	public function get_search_results_post_types() {
		$search_post_types = Avada()->settings->get( 'search_content' );

		if ( ! Avada()->settings->get( 'search_filter_results' ) ) {
			$search_post_types = 'any';
		}

		return apply_filters( 'avada_search_results_post_types', $search_post_types );
	}

}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
