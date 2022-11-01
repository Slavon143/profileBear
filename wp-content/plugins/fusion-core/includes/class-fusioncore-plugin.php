<?php
/**
 * The main plugin class.
 *
 * @since 6.0
 * @package Avada-Core
 * @subpackage Core
 */

/**
 * The main fusion-core class.
 */
class FusionCore_Plugin {

	/**
	* Plugin version, used for cache-busting of style and script file references.
	*
	* @since   1.0.0
	* @var  string
	*/
	const VERSION = FUSION_CORE_VERSION;

	/**
	 * Instance of the class.
	 *
	 * @static
	 * @access protected
	 * @since 1.0.0
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * JS folder URL.
	 *
	 * @static
	 * @access public
	 * @since 3.0.3
	 * @var string
	 */
	public static $js_folder_url;

	/**
	 * JS folder path.
	 *
	 * @static
	 * @access public
	 * @since 3.0.3
	 * @var string
	 */
	public static $js_folder_path;

	/**
	 * Holds info if FL is present.
	 *
	 * @static
	 * @access public
	 * @since 4.0
	 * @var bool
	 */
	public static $fusion_library_exists;


	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @access private
	 * @since 1.0.0
	 */
	private function __construct() {
		$path                 = ( defined( 'AVADA_DEV_MODE' ) && true === AVADA_DEV_MODE ) ? '' : '/min';
		self::$js_folder_url  = FUSION_CORE_URL . 'js' . $path;
		self::$js_folder_path = FUSION_CORE_PATH . '/js' . $path;

		$this->includes();

		add_action( 'after_setup_theme', [ $this, 'set_fusion_library_exists' ] );
		add_action( 'after_setup_theme', [ $this, 'manage_pll_slugs' ] );
		add_action( 'after_setup_theme', [ $this, 'include_cpt_dependencies' ] );

		// Load scripts & styles.
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
			add_filter( 'fusion_dynamic_css_final', [ $this, 'scripts_dynamic_css' ] );
		}

		// Register custom post-types and taxonomies.
		add_action( 'init', [ $this, 'register_post_types' ] );

		// User agent for news feed widget.
		add_action( 'wp_feed_options', [ $this, 'feed_user_agent' ], 10, 2 );

		// Register our admin widget.
		add_action( 'wp_dashboard_setup', [ $this, 'add_dashboard_widget' ], 100 );

		// Admin menu tweaks.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		// Check if Avada Core has been updated.  Delay until after theme is available.
		add_action( 'after_setup_theme', [ $this, 'versions_compare' ] );

		// Init Widgets.
		add_action( 'widgets_init', [ $this, 'widget_init' ] );

		// Exclude post type from Events Calendar.
		add_filter( 'tribe_tickets_settings_post_types', [ $this, 'fusion_core_exclude_post_type' ] );

		// Set Avada Builder dependencies.
		add_filter( 'fusion_builder_option_dependency', [ $this, 'set_builder_dependencies' ], 10, 3 );

		// Map Avada Builder descriptions.
		add_filter( 'fusion_builder_map_descriptions', [ $this, 'map_builder_descriptions' ], 10, 1 );

		add_action( 'fusion_builder_enqueue_live_scripts', [ $this, 'live_scripts' ] );
	}

	/**
	 * Include files.
	 *
	 * @access public
	 * @since 4.0
	 * @return void
	 */
	public function includes() {

		require_once FUSION_CORE_PATH . '/includes/class-fusion-contact.php';

		if ( ! class_exists( 'Avada' ) || ! class_exists( 'Fusion_Builder' ) ) {
			require_once FUSION_CORE_PATH . '/shortcodes/fusion-contact-form.php';
		}

		// Load widget classes.
		$filenames = glob( FUSION_CORE_PATH . '/includes/widget/*.php', GLOB_NOSORT );
		foreach ( $filenames as $filename ) {
			require_once wp_normalize_path( $filename );
		}
	}

	/**
	 * Include CPT files.
	 *
	 * @access public
s	 * @since 5.1.2
	 * @return void
	 */
	public function include_cpt_dependencies() {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		if ( ! $fusion_settings || '0' !== $fusion_settings->get( 'status_fusion_portfolio' ) ) {
			require_once FUSION_CORE_PATH . '/includes/class-fusion-portfolio.php';
		}

		if ( ! $fusion_settings || '0' !== $fusion_settings->get( 'status_fusion_faqs' ) ) {
			require_once FUSION_CORE_PATH . '/includes/class-fusion-faqs.php';
		}
	}

	/**
	 * Sets the Fusion Library constant.
	 *
	 * @access public
	 * @since 5.9.2
	 * @return void
	 */
	public function set_fusion_library_exists() {
		self::$fusion_library_exists = class_exists( 'Fusion' );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 1.0.0
	 * @return object  A single instance of the class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set yet, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;

	}

	/**
	 * Get the option default value.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string|array $option_name         The option name. If an array then it's [ option, sub-item ].
	 * @param array        $default_values      An array of default values.
	 * @param string       $sanitation_function A callback method from the Fusion_Sanitize object.
	 * @return string|mixed The correct default value.
	 */
	public static function get_option_default_value( $option_name, $default_values, $sanitation_function = false ) {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;
		$option_value    = '';

		if ( $fusion_settings ) {
			if ( is_array( $option_name ) ) {
				$option_value = $fusion_settings->get( $option_name[0], $option_name[1] );
			} else {
				$option_value = $fusion_settings->get( $option_name );
			}

			if ( $sanitation_function ) {
				Fusion_Sanitize::$sanitation_function( $option_value );
			}
		} elseif ( array_key_exists( $option_name, $default_values ) ) {
			$option_value = $default_values[ $option_name ];
		}

		return $option_value;
	}

	/**
	 * Gets the value of a Global Option.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @param string|null  $option The option.
	 * @param string|false $subset The sub-option in case of an array.
	 */
	public static function get_option( $option = null, $subset = false ) {

		$value = '';
		// If Fusion_Settings is available, use it to get the theme-option.
		if ( class_exists( 'Fusion_Settings' ) ) {
			$fusion_settings = awb_get_fusion_settings();
			$value           = $fusion_settings->get( $option, $subset );
		}
		return apply_filters( 'fusion_core_get_option', $value, $option, $subset );

	}

	/**
	 * Returns a cached query.
	 * If the query is not cached then it caches it and returns the result.
	 *
	 * @static
	 * @access public
	 * @param string|array $args Same as in WP_Query.
	 * @return object
	 */
	public static function fusion_core_cached_query( $args ) {

		// Make sure cached queries are not language agnostic.
		if ( class_exists( 'Fusion_Multilingual' ) ) {
			if ( is_array( $args ) ) {
				$args['fusion_lang'] = Fusion_Multilingual::get_active_language();
			} else {
				$args .= '&fusion_lang=' . Fusion_Multilingual::get_active_language();
			}
		}

		$query_id = md5( maybe_serialize( $args ) );
		$query    = wp_cache_get( $query_id, 'avada' );
		if ( false === $query ) {
			$query = new WP_Query( $args );
			wp_cache_set( $query_id, $query, 'avada' );
		}
		return $query;

	}

	/**
	 * Returns array of available Avada sliders.
	 *
	 * @access public
	 * @since 3.1.6
	 * @param string $add_select_slider_label Sets a "Add Slider" label at the beginning of the array.
	 * @return array
	 */
	public static function get_fusion_sliders( $add_select_slider_label = '' ) {
		$slides_array = [];

		if ( $add_select_slider_label ) {
			$slides_array[''] = esc_html( $add_select_slider_label );
		}

		$slides = [];
		$slides = get_terms( 'slide-page' );
		if ( $slides && ! isset( $slides->errors ) ) {
			$slides = maybe_unserialize( $slides );
			foreach ( $slides as $key => $val ) {
				$slides_array[ $val->slug ] = $val->name . ' (#' . $val->term_id . ')';
			}
		}
		return $slides_array;
	}

	/**
	 * Enqueues scripts.
	 *
	 * @access public
	 */
	public function scripts() {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		// If we're using a CSS to file compiler there's no need to enqueue separate file.
		// It will be added directly to the compiled CSS (@see scripts_dynamic_css method).
		if ( $fusion_settings ) {
			if ( 'off' !== $fusion_settings->get( 'css_cache_method' ) ) {
				return;
			}
		}

		wp_enqueue_style( 'fusion-core-comment-form', FUSION_CORE_URL . 'css/comment-form.min.css', [], FUSION_CORE_VERSION );
		wp_enqueue_style( 'fusion-core-privacy', FUSION_CORE_URL . 'css/privacy.min.css', [], FUSION_CORE_VERSION );

		if ( false === $fusion_settings || '0' !== $fusion_settings->get( 'status_fusion_faqs' ) ) {
			wp_enqueue_style( 'fusion-core-faqs', FUSION_CORE_URL . 'css/faqs.min.css', [], FUSION_CORE_VERSION );
		}

		if ( false === $fusion_settings || '0' !== $fusion_settings->get( 'status_fusion_portfolio' ) ) {
			wp_enqueue_style( 'fusion-core-portfolio', FUSION_CORE_URL . 'css/portfolio.min.css', [], FUSION_CORE_VERSION );
		}
	}

	/**
	 * Enqueues live scripts.
	 *
	 * @access public
	 * @since 4.0
	 */
	public function live_scripts() {

		wp_enqueue_script( 'fusion_builder_portfolio_element', FUSION_CORE_URL . 'shortcodes/previews/front-end/elements/view-portfolio.js', [], self::VERSION, true );
		wp_enqueue_script( 'fusion_builder_faq_element', FUSION_CORE_URL . 'shortcodes/previews/front-end/elements/view-faq.js', [], self::VERSION, true );
		wp_enqueue_script( 'fusion_builder_fusionslider_element', FUSION_CORE_URL . 'shortcodes/previews/front-end/elements/view-fusionslider.js', [], self::VERSION, true );
		wp_enqueue_script( 'fusion_builder_privacy_element', FUSION_CORE_URL . 'shortcodes/previews/front-end/elements/view-privacy.js', [], self::VERSION, true );
		wp_enqueue_script( 'fusion_builder_project_details_element', FUSION_CORE_URL . 'shortcodes/previews/front-end/elements/view-project-details.js', [], self::VERSION, true );
	}

	/**
	 * Adds styles to the compiled dynamic-css.
	 *
	 * @access public
	 * @since 3.1.5
	 * @param string $original_styles The compiled dynamic-css styles.
	 * @return string The dynamic-css with extra css apended if needed.
	 */
	public function scripts_dynamic_css( $original_styles ) {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		if ( $fusion_settings ) {
			if ( 'off' !== $fusion_settings->get( 'css_cache_method' ) ) {
				$wp_filesystem = Fusion_Helper::init_filesystem();

				$original_styles = $wp_filesystem->get_contents( FUSION_CORE_PATH . '/css/comment-form.min.css' ) . $original_styles;
				$original_styles = $wp_filesystem->get_contents( FUSION_CORE_PATH . '/css/privacy.min.css' ) . $original_styles;

				if ( '0' !== $fusion_settings->get( 'status_fusion_portfolio' ) ) {
					$original_styles = $wp_filesystem->get_contents( FUSION_CORE_PATH . '/css/portfolio.min.css' ) . $original_styles;
				}

				if ( '0' !== $fusion_settings->get( 'status_fusion_faqs' ) ) {
					$original_styles = $wp_filesystem->get_contents( FUSION_CORE_PATH . '/css/faqs.min.css' ) . $original_styles;
				}

				// Stylesheet ID: fusion-core-style.
				return $original_styles;
			}
		}

		return $original_styles;
	}

	/**
	 * Register custom post types.
	 *
	 * @access public
	 * @since 3.1.0
	 */
	public function register_post_types() {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		if ( ! $fusion_settings ) {
			$fusion_settings_array = [
				'portfolio_slug' => 'portfolio-items',
				'faq_slug'       => 'faq-items',
				'status_eslider' => '1',
			];
			if ( class_exists( 'Fusion_Settings' ) ) {
				$fusion_settings = Fusion_Settings::get_instance();

				$fusion_settings_array = [
					'portfolio_slug' => $fusion_settings->get( 'portfolio_slug' ),
					'faq_slug'       => $fusion_settings->get( 'faq_slug' ),
					'status_eslider' => $fusion_settings->get( 'status_eslider' ),
				];
			}
		} else {
			$fusion_settings_array = [
				'portfolio_slug' => $fusion_settings->get( 'portfolio_slug' ),
				'faq_slug'       => $fusion_settings->get( 'faq_slug' ),
				'status_eslider' => $fusion_settings->get( 'status_eslider' ),
			];
		}

		$permalinks = get_option( 'avada_permalinks' );

		// Portfolio.
		if ( ! class_exists( 'Fusion_Settings' ) || '0' !== $fusion_settings->get( 'status_fusion_portfolio' ) ) {
			register_post_type(
				'avada_portfolio',
				[
					'labels'       => [
						'name'                     => _x( 'Portfolio', 'Post Type General Name', 'fusion-core' ),
						'singular_name'            => _x( 'Portfolio', 'Post Type Singular Name', 'fusion-core' ),
						'add_new_item'             => __( 'Add New Portfolio Post', 'fusion-core' ),
						'edit_item'                => __( 'Edit Portfolio Post', 'fusion-core' ),
						'item_published'           => __( 'Portfolio published.', 'fusion-core' ),
						'item_published_privately' => __( 'Portfolio published privately.', 'fusion-core' ),
						'item_reverted_to_draft'   => __( 'Portfolio reverted to draft.', 'fusion-core' ),
						'item_scheduled'           => __( 'Portfolio scheduled.', 'fusion-core' ),
						'item_updated'             => __( 'Portfolio updated.', 'fusion-core' ),
					],
					'public'       => true,
					'has_archive'  => true,
					'rewrite'      => [
						'slug' => $fusion_settings_array['portfolio_slug'],
					],
					'show_in_rest' => true,
					'supports'     => [ 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes', 'post-formats' ],
					'can_export'   => true,
				]
			);

			register_taxonomy(
				'portfolio_category',
				'avada_portfolio',
				[
					'hierarchical' => true,
					'label'        => esc_attr__( 'Portfolio Categories', 'fusion-core' ),
					'query_var'    => true,
					'rewrite'      => [
						'slug'       => empty( $permalinks['portfolio_category_base'] ) ? _x( 'portfolio_category', 'slug', 'fusion-core' ) : $permalinks['portfolio_category_base'],
						'with_front' => false,
					],
					'show_in_rest' => true,
				]
			);

			register_taxonomy(
				'portfolio_skills',
				'avada_portfolio',
				[
					'hierarchical' => true,
					'label'        => esc_attr__( 'Portfolio Skills', 'fusion-core' ),
					'query_var'    => true,
					'labels'       => [
						'add_new_item' => esc_attr__( 'Add New Skill', 'fusion-core' ),
					],
					'rewrite'      => [
						'slug'       => empty( $permalinks['portfolio_skills_base'] ) ? _x( 'portfolio_skills', 'slug', 'fusion-core' ) : $permalinks['portfolio_skills_base'],
						'with_front' => false,
					],
					'show_in_rest' => true,
				]
			);

			register_taxonomy(
				'portfolio_tags',
				'avada_portfolio',
				[
					'hierarchical' => false,
					'label'        => esc_attr__( 'Portfolio Tags', 'fusion-core' ),
					'query_var'    => true,
					'rewrite'      => [
						'slug'       => empty( $permalinks['portfolio_tags_base'] ) ? _x( 'portfolio_tags', 'slug', 'fusion-core' ) : $permalinks['portfolio_tags_base'],
						'with_front' => false,
					],
					'show_in_rest' => true,
				]
			);
		}

		// FAQ.
		if ( ! class_exists( 'Fusion_Settings' ) || '0' !== $fusion_settings->get( 'status_fusion_faqs' ) ) {
			register_post_type(
				'avada_faq',
				[
					'labels'       => [
						'name'                     => _x( 'FAQs', 'Post Type General Name', 'fusion-core' ),
						'singular_name'            => _x( 'FAQ', 'Post Type Singular Name', 'fusion-core' ),
						'add_new_item'             => __( 'Add New FAQ Post', 'fusion-core' ),
						'edit_item'                => __( 'Edit FAQ Post', 'fusion-core' ),
						'item_published'           => __( 'FAQ published.', 'fusion-core' ),
						'item_published_privately' => __( 'FAQ published privately.', 'fusion-core' ),
						'item_reverted_to_draft'   => __( 'FAQ reverted to draft.', 'fusion-core' ),
						'item_scheduled'           => __( 'FAQ scheduled.', 'fusion-core' ),
						'item_updated'             => __( 'FAQ updated.', 'fusion-core' ),
					],
					'public'       => true,
					'has_archive'  => true,
					'rewrite'      => [
						'slug' => $fusion_settings_array['faq_slug'],
					],
					'show_in_rest' => true,
					'supports'     => [ 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', 'page-attributes', 'post-formats' ],
					'can_export'   => true,
				]
			);

			register_taxonomy(
				'faq_category',
				'avada_faq',
				[
					'hierarchical' => true,
					'label'        => __( 'FAQ Categories', 'fusion-core' ),
					'query_var'    => true,
					'rewrite'      => true,
					'show_in_rest' => true,
				]
			);
		}

		// Elastic Slider.
		if ( ! class_exists( 'Fusion_Settings' ) || '0' !== $fusion_settings_array['status_eslider'] ) {
			register_post_type(
				'themefusion_elastic',
				[
					'public'              => true,
					'has_archive'         => false,
					'rewrite'             => [
						'slug' => 'elastic-slide',
					],
					'supports'            => [ 'title', 'thumbnail' ],
					'can_export'          => true,
					'menu_position'       => 100,
					'publicly_queryable'  => false,
					'exclude_from_search' => true,
					'labels'              => [
						'name'                     => _x( 'Elastic Sliders', 'Post Type General Name', 'fusion-core' ),
						'singular_name'            => _x( 'Elastic Slide', 'Post Type Singular Name', 'fusion-core' ),
						'menu_name'                => esc_attr__( 'Elastic Slider', 'fusion-core' ),
						'parent_item_colon'        => esc_attr__( 'Parent Slide:', 'fusion-core' ),
						'all_items'                => esc_attr__( 'Add or Edit Slides', 'fusion-core' ),
						'view_item'                => esc_attr__( 'View Slides', 'fusion-core' ),
						'add_new_item'             => esc_attr__( 'Add New Slide', 'fusion-core' ),
						'add_new'                  => esc_attr__( 'Add New Slide', 'fusion-core' ),
						'edit_item'                => esc_attr__( 'Edit Slide', 'fusion-core' ),
						'update_item'              => esc_attr__( 'Update Slide', 'fusion-core' ),
						'search_items'             => esc_attr__( 'Search Slide', 'fusion-core' ),
						'not_found'                => esc_attr__( 'Not found', 'fusion-core' ),
						'not_found_in_trash'       => esc_attr__( 'Not found in Trash', 'fusion-core' ),
						'item_published'           => __( 'Slide published.', 'fusion-core' ),
						'item_published_privately' => __( 'Slide published privately.', 'fusion-core' ),
						'item_reverted_to_draft'   => __( 'Slide reverted to draft.', 'fusion-core' ),
						'item_scheduled'           => __( 'Slide scheduled.', 'fusion-core' ),
						'item_updated'             => __( 'Slide updated.', 'fusion-core' ),
					],
				]
			);

			register_taxonomy(
				'themefusion_es_groups',
				'themefusion_elastic',
				[
					'hierarchical' => false,
					'query_var'    => true,
					'rewrite'      => true,
					'labels'       => [
						'name'                       => _x( 'Groups', 'Taxonomy General Name', 'fusion-core' ),
						'singular_name'              => _x( 'Group', 'Taxonomy Singular Name', 'fusion-core' ),
						'menu_name'                  => esc_attr__( 'Add or Edit Groups', 'fusion-core' ),
						'all_items'                  => esc_attr__( 'All Groups', 'fusion-core' ),
						'parent_item_colon'          => esc_attr__( 'Parent Group:', 'fusion-core' ),
						'new_item_name'              => esc_attr__( 'New Group Name', 'fusion-core' ),
						'add_new_item'               => esc_attr__( 'Add Groups', 'fusion-core' ),
						'edit_item'                  => esc_attr__( 'Edit Group', 'fusion-core' ),
						'update_item'                => esc_attr__( 'Update Group', 'fusion-core' ),
						'separate_items_with_commas' => esc_attr__( 'Separate groups with commas', 'fusion-core' ),
						'search_items'               => esc_attr__( 'Search Groups', 'fusion-core' ),
						'add_or_remove_items'        => esc_attr__( 'Add or remove groups', 'fusion-core' ),
						'choose_from_most_used'      => esc_attr__( 'Choose from the most used groups', 'fusion-core' ),
						'not_found'                  => esc_attr__( 'Not Found', 'fusion-core' ),
					],
				]
			);
		}

		// qTranslate and mqTranslate custom post type support.
		if ( function_exists( 'qtrans_getLanguage' ) ) {
			add_action( 'portfolio_category_add_form', 'qtrans_modifyTermFormFor' );
			add_action( 'portfolio_category_edit_form', 'qtrans_modifyTermFormFor' );
			add_action( 'portfolio_skills_add_form', 'qtrans_modifyTermFormFor' );
			add_action( 'portfolio_skills_edit_form', 'qtrans_modifyTermFormFor' );
			add_action( 'portfolio_tags_add_form', 'qtrans_modifyTermFormFor' );
			add_action( 'portfolio_tags_edit_form', 'qtrans_modifyTermFormFor' );
			add_action( 'faq_category_edit_form', 'qtrans_modifyTermFormFor' );
		}

		// Check if flushing permalinks required and flush them.
		$flush_permalinks = get_option( 'fusion_core_flush_permalinks' );
		if ( ! $flush_permalinks ) {
			flush_rewrite_rules();
			update_option( 'fusion_core_flush_permalinks', true );
		}
	}

	/**
	 * Elastic Slider admin menu.
	 *
	 * @access public
	 */
	public function admin_menu() {
		global $submenu;
		unset( $submenu['edit.php?post_type=themefusion_elastic'][10] );
	}

	/**
	 * Compares db and plugin versions and does stuff if needed.
	 *
	 * @access public
	 * @since 3.1.5
	 */
	public function versions_compare() {

		$db_version = get_option( 'fusion_core_version', false );

		if ( ! $db_version || FUSION_CORE_VERSION !== $db_version ) {

			// Run activation related steps.
			delete_option( 'fusion_core_flush_permalinks' );

			if ( class_exists( 'Fusion_Cache' ) ) {
				$fusion_cache = new Fusion_Cache();
				$fusion_cache->reset_all_caches();
			}
			fusion_core_enable_elements();

			// Update version in the database.
			update_option( 'fusion_core_version', FUSION_CORE_VERSION );
		}
	}

	/**
	 * Return post types to exclude from events calendar.
	 *
	 * @since 3.3.0
	 * @access public
	 * @param array $all_post_types All allowed post types in events calendar.
	 * @return array
	 */
	public function fusion_core_exclude_post_type( $all_post_types ) {

		unset( $all_post_types['slide'] );
		unset( $all_post_types['themefusion_elastic'] );

		return $all_post_types;
	}

	/**
	 * Set builder element dependencies, for those which involve EO.
	 *
	 * @since  3.3.0
	 * @param  array  $dependencies currently active dependencies.
	 * @param  string $shortcode name of shortcode.
	 * @param  string $option name of option.
	 * @return array  dependency checks.
	 */
	public function set_builder_dependencies( $dependencies, $shortcode, $option ) {
		$fusion_settings      = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;
		$shortcode_option_map = [];

		// Portfolio.
		$portfolio_is_single_column                                   = [
			'check'  => [
				'element-global' => 'portfolio_columns',
				'value'          => '1',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'columns',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['column_spacing']['fusion_portfolio'][] = $portfolio_is_single_column;
		$shortcode_option_map['equal_heights']['fusion_portfolio'][]  = $portfolio_is_single_column;

		$shortcode_option_map['grid_element_color']['fusion_portfolio'][]        = [
			'check'  => [
				'element-global' => 'portfolio_text_layout',
				'value'          => 'boxed',
				'operator'       => '!=',
			],
			'output' => [
				'element'  => 'text_layout',
				'value'    => 'default',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['grid_box_color']['fusion_portfolio'][]            = [
			'check'  => [
				'element-global' => 'portfolio_text_layout',
				'value'          => 'no_text',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'text_layout',
				'value'    => 'default',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['grid_separator_style_type']['fusion_portfolio'][] = [
			'check'  => [
				'element-global' => 'portfolio_text_layout',
				'value'          => 'boxed',
				'operator'       => '!=',
			],
			'output' => [
				'element'  => 'text_layout',
				'value'    => 'default',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['grid_separator_color']['fusion_portfolio'][]      = [
			'check'  => [
				'element-global' => 'portfolio_text_layout',
				'value'          => 'boxed',
				'operator'       => '!=',
			],
			'output' => [
				'element'  => 'text_layout',
				'value'    => 'default',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['portfolio_layout_padding']['fusion_portfolio'][]  = [
			'check'  => [
				'element-global' => 'portfolio_text_layout',
				'value'          => 'unboxed',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'text_layout',
				'value'    => 'default',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['excerpt_length']['fusion_portfolio'][]            = [
			'check'  => [
				'element-global' => 'portfolio_content_length',
				'value'          => 'full_content',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'content_length',
				'value'    => 'default',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['excerpt_length']['fusion_portfolio'][]            = [
			'check'  => [
				'element-global' => 'portfolio_content_length',
				'value'          => 'excerpt',
				'operator'       => '!=',
			],
			'output' => [
				'element'  => 'content_length',
				'value'    => 'default',
				'operator' => '!=',
			],
		];

		$shortcode_option_map['strip_html']['fusion_portfolio'][] = [
			'check'  => [
				'element-global' => 'portfolio_content_length',
				'value'          => 'full_content',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'content_length',
				'value'    => 'default',
				'operator' => '!=',
			],
		];

		// FAQs.
		$shortcode_option_map['divider_line']['fusion_faq'][]     = [
			'check'  => [
				'element-global' => 'faq_accordion_boxed_mode',
				'value'          => '1',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'boxed_mode',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['border_size']['fusion_faq'][]      = [
			'check'  => [
				'element-global' => 'faq_accordion_boxed_mode',
				'value'          => '0',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'boxed_mode',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['border_color']['fusion_faq'][]     = [
			'check'  => [
				'element-global' => 'faq_accordion_boxed_mode',
				'value'          => '0',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'boxed_mode',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['background_color']['fusion_faq'][] = [
			'check'  => [
				'element-global' => 'faq_accordion_boxed_mode',
				'value'          => '0',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'boxed_mode',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['hover_color']['fusion_faq'][]      = [
			'check'  => [
				'element-global' => 'faq_accordion_boxed_mode',
				'value'          => '0',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'boxed_mode',
				'value'    => '',
				'operator' => '!=',
			],
		];
		$shortcode_option_map['icon_box_color']['fusion_faq'][]   = [
			'check'  => [
				'element-global' => 'faq_accordion_icon_boxed',
				'value'          => '0',
				'operator'       => '==',
			],
			'output' => [
				'element'  => 'icon_boxed_mode',
				'value'    => '',
				'operator' => '!=',
			],
		];

		// If has TO related dependency, do checks.
		if ( $fusion_settings && isset( $shortcode_option_map[ $option ][ $shortcode ] ) && is_array( $shortcode_option_map[ $option ][ $shortcode ] ) ) {
			foreach ( $shortcode_option_map[ $option ][ $shortcode ] as $option_check ) {
				$option_value = $fusion_settings->get( $option_check['check']['element-global'] );
				$pass         = false;

				// Check the result of check.
				if ( '==' === $option_check['check']['operator'] ) {
					$pass = ( $option_value == $option_check['check']['value'] ); // phpcs:ignore WordPress.PHP.StrictComparisons
				}
				if ( '!=' === $option_check['check']['operator'] ) {
					$pass = ( $option_value != $option_check['check']['value'] ); // phpcs:ignore WordPress.PHP.StrictComparisons
				}

				// If check passes then add dependency for checking.
				if ( $pass ) {
					$dependencies[] = $option_check['output'];
				}
			}
		}
		return $dependencies;
	}

	/**
	 * Returns equivalent global information for FB param.
	 *
	 * @since 3.3.0
	 * @param array $shortcode_option_map Shortcodes description map array.
	 */
	public function map_builder_descriptions( $shortcode_option_map ) {

		// Portfolio.
		$shortcode_option_map['portfolio_layout_padding']['fusion_portfolio']       = [
			'theme-option' => 'portfolio_layout_padding',
			'subset'       => [ 'top', 'right', 'bottom', 'left' ],
		];
		$shortcode_option_map['picture_size']['fusion_portfolio']                   = [
			'theme-option' => 'portfolio_featured_image_size',
			'type'         => 'select',
		];
		$shortcode_option_map['text_layout']['fusion_portfolio']                    = [
			'theme-option' => 'portfolio_text_layout',
			'type'         => 'select',
		];
		$shortcode_option_map['portfolio_text_alignment']['fusion_portfolio']       = [
			'theme-option' => 'portfolio_text_alignment',
			'type'         => 'select',
		];
		$shortcode_option_map['columns']['fusion_portfolio']                        = [
			'theme-option' => 'portfolio_columns',
			'type'         => 'range',
		];
		$shortcode_option_map['column_spacing']['fusion_portfolio']                 = [
			'theme-option' => 'portfolio_column_spacing',
			'type'         => 'range',
		];
		$shortcode_option_map['number_posts']['fusion_portfolio']                   = [
			'theme-option' => 'portfolio_items',
			'type'         => 'range',
		];
		$shortcode_option_map['pagination_type']['fusion_portfolio']                = [
			'theme-option' => 'portfolio_pagination_type',
			'type'         => 'select',
		];
		$shortcode_option_map['content_length']['fusion_portfolio']                 = [
			'theme-option' => 'portfolio_content_length',
			'type'         => 'select',
		];
		$shortcode_option_map['excerpt_length']['fusion_portfolio']                 = [
			'theme-option' => 'portfolio_excerpt_length',
			'type'         => 'range',
		];
		$shortcode_option_map['portfolio_title_display']['fusion_portfolio']        = [
			'theme-option' => 'portfolio_title_display',
			'type'         => 'select',
		];
		$shortcode_option_map['strip_html']['fusion_portfolio']                     = [
			'theme-option' => 'portfolio_strip_html_excerpt',
			'type'         => 'yesno',
		];
		$shortcode_option_map['grid_box_color']['fusion_portfolio']                 = [
			'theme-option' => 'timeline_bg_color',
			'reset'        => true,
		];
		$shortcode_option_map['grid_element_color']['fusion_portfolio']             = [
			'theme-option' => 'timeline_color',
			'reset'        => true,
		];
		$shortcode_option_map['grid_separator_style_type']['fusion_portfolio']      = [
			'theme-option' => 'grid_separator_style_type',
			'type'         => 'select',
		];
		$shortcode_option_map['grid_separator_color']['fusion_portfolio']           = [
			'theme-option' => 'grid_separator_color',
			'reset'        => true,
		];
		$shortcode_option_map['portfolio_masonry_grid_ratio']['fusion_portfolio']   = [
			'theme-option' => 'masonry_grid_ratio',
			'type'         => 'range',
		];
		$shortcode_option_map['portfolio_masonry_width_double']['fusion_portfolio'] = [
			'theme-option' => 'masonry_width_double',
			'type'         => 'range',
		];

		// FAQs.
		$shortcode_option_map['featured_image']['fusion_faq']             = [
			'theme-option' => 'faq_featured_image',
			'type'         => 'yesno',
		];
		$shortcode_option_map['filters']['fusion_faq']                    = [
			'theme-option' => 'faq_filters',
			'type'         => 'select',
		];
		$shortcode_option_map['type']['fusion_faq']                       = [
			'theme-option' => 'faq_accordion_type',
			'type'         => 'select',
		];
		$shortcode_option_map['divider_line']['fusion_faq']               = [
			'theme-option' => 'faq_accordion_divider_line',
			'type'         => 'yesno',
		];
		$shortcode_option_map['divider_color']['fusion_faq']              = [
			'theme-option' => 'faq_accordion_divider_color',
			'reset'        => true,
		];
		$shortcode_option_map['divider_hover_color']['fusion_faq']        = [
			'theme-option' => 'faq_accordion_divider_hover_color',
			'reset'        => true,
		];
		$shortcode_option_map['title_font']['fusion_faq']                 = [
			'theme-option' => 'faq_accordion_title_typography',
			'subset'       => 'font-family',
			'type'         => 'select',
		];
		$shortcode_option_map['title_font_size']['fusion_faq']            = [
			'theme-option' => 'faq_accordion_title_typography',
			'subset'       => 'font-size',
			'type'         => 'select',
		];
		$shortcode_option_map['title_color']['fusion_faq']                = [
			'theme-option' => 'faq_accordion_title_typography',
			'subset'       => 'color',
			'reset'        => true,
		];
		$shortcode_option_map['content_font']['fusion_faq']               = [
			'theme-option' => 'faq_accordion_content_typography',
			'subset'       => 'font-family',
			'type'         => 'select',
		];
		$shortcode_option_map['content_font_size']['fusion_faq']          = [
			'theme-option' => 'faq_accordion_content_typography',
			'subset'       => 'font-size',
			'type'         => 'select',
		];
		$shortcode_option_map['content_color']['fusion_faq']              = [
			'theme-option' => 'faq_accordion_content_typography',
			'subset'       => 'color',
			'reset'        => true,
		];
		$shortcode_option_map['boxed_mode']['fusion_faq']                 = [
			'theme-option' => 'faq_accordion_boxed_mode',
			'type'         => 'yesno',
		];
		$shortcode_option_map['border_size']['fusion_faq']                = [
			'theme-option' => 'faq_accordion_border_size',
			'type'         => 'range',
		];
		$shortcode_option_map['border_color']['fusion_faq']               = [
			'theme-option' => 'faq_accordian_border_color',
			'reset'        => true,
		];
		$shortcode_option_map['background_color']['fusion_faq']           = [
			'theme-option' => 'faq_accordian_background_color',
			'reset'        => true,
		];
		$shortcode_option_map['hover_color']['fusion_faq']                = [
			'theme-option' => 'faq_accordian_hover_color',
			'reset'        => true,
		];
		$shortcode_option_map['title_font_size']['fusion_faq']            = [
			'theme-option' => 'faq_accordion_title_typography',
			'subset'       => 'font-size',
		];
		$shortcode_option_map['icon_size']['fusion_faq']                  = [
			'theme-option' => 'faq_accordion_icon_size',
			'type'         => 'range',
		];
		$shortcode_option_map['icon_color']['fusion_faq']                 = [
			'theme-option' => 'faq_accordian_icon_color',
			'reset'        => true,
		];
		$shortcode_option_map['icon_boxed_mode']['fusion_faq']            = [
			'theme-option' => 'faq_accordion_icon_boxed',
			'type'         => 'yesno',
		];
		$shortcode_option_map['icon_box_color']['fusion_faq']             = [
			'theme-option' => 'faq_accordian_inactive_color',
			'reset'        => true,
		];
		$shortcode_option_map['icon_alignment']['fusion_faq']             = [
			'theme-option' => 'faq_accordion_icon_align',
			'type'         => 'select',
		];
		$shortcode_option_map['toggle_hover_accent_color']['fusion_faq']  = [
			'theme-option' => 'faq_accordian_active_color',
			'reset'        => true,
		];
		$shortcode_option_map['toggle_active_accent_color']['fusion_faq'] = [
			'theme-option' => 'faq_accordian_active_accent_color',
			'reset'        => true,
		];

		return $shortcode_option_map;
	}

	/**
	 * Register widgets.
	 *
	 * @since 4.0
	 * @access public
	 * @return void
	 */
	public function widget_init() {

		register_widget( 'Fusion_Widget_Ad_125_125' );
		register_widget( 'Fusion_Widget_Author' );
		register_widget( 'Fusion_Widget_Contact_Info' );
		register_widget( 'Fusion_Widget_Tabs' );
		register_widget( 'Fusion_Widget_Recent_Works' );
		register_widget( 'Fusion_Widget_Tweets' );
		register_widget( 'Fusion_Widget_Flickr' );
		register_widget( 'Fusion_Widget_Social_Links' );
		register_widget( 'Fusion_Widget_Facebook_Page' );
		register_widget( 'Fusion_Widget_Menu' );
		register_widget( 'Fusion_Widget_Vertical_Menu' );
		register_widget( 'Fusion_Widget_Form' );
	}

	/**
	 * Adds the news dashboard widget.
	 *
	 * @since 4.0
	 * @access public
	 * @return void
	 */
	public function add_dashboard_widget() {

		// Create the widget.
		wp_add_dashboard_widget( 'themefusion-news', apply_filters( 'avada_dashboard_widget_title', esc_attr__( 'Avada News', 'Avada' ) ), [ $this, 'display_news_dashboard_widget' ] );

		// Make sure our widget is on top off all others.
		global $wp_meta_boxes;

		// Get the regular dashboard widgets array.
		$normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

		$fusion_widget_backup = [];
		if ( isset( $normal_dashboard['themefusion-news'] ) ) {
			// Backup and delete our new dashboard widget from the end of the array.
			$fusion_widget_backup = [
				'themefusion-news' => $normal_dashboard['themefusion-news'],
			];
			unset( $normal_dashboard['themefusion-news'] );
		}

		// Merge the two arrays together so our widget is at the beginning.
		$sorted_dashboard = array_merge( $fusion_widget_backup, $normal_dashboard );

		// Save the sorted array back into the original metaboxes.
		$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard; // phpcs:ignore WordPress.WP.GlobalVariablesOverride
	}

	/**
	 * Renders the news dashboard widget.
	 *
	 * @since 4.0
	 * @access public
	 * @return void
	 */
	public function display_news_dashboard_widget() {
		$dashboard_data = [];
		if ( class_exists( 'Avada' ) ) {
			$dashboard_data = Avada::get_data();
		}

		// Create two feeds, the first being just a leading article with data and summary, the second being a normal news feed.
		$feeds = [
			'news' => [
				'link'         => 'https://theme-fusion.com/blog/',
				'url'          => 'https://theme-fusion.com/feed/',
				'title'        => esc_attr__( 'Avada News', 'fusion-core' ),
				'items'        => 4,
				'show_summary' => 1,
				'show_author'  => 0,
				'show_date'    => 0,
			],
		];
		?>
		<div class="fusion-dbw-wrapper">
			<div class="fusion-dbw-header">
				<div class="fusion-dbw-logo">
					<span class="fusion-dbw-image-wrapper">
						<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/logo@2x.png' ); ?>" width="115" height="25" alt="<?php esc_attr_e( 'Avada Logo', 'fusion-core' ); ?>">
					</span>
					<?php $version = defined( 'AVADA_VERSION' ) ? 'v' . AVADA_VERSION : ''; ?>
					<span class="fusion-dbw-version"><?php echo esc_html( apply_filters( 'avada_db_version', $version ) ); ?></span>
				</div>

				<?php
				$sale_classes = '';
				if ( ! empty( $dashboard_data['price'] ) ) {
					/* translators: Item price. */
					$buy_button_text = sprintf( esc_html__( 'Only %s - Buy Now', 'Avada' ), $dashboard_data['price'] );

					if ( ! empty( $dashboard_data['on_sale'] ) && $dashboard_data['on_sale'] ) {
						/* translators: Item price. */
						$buy_button_text = sprintf( esc_html__( 'On Sale - Only %s', 'Avada' ), $dashboard_data['price'] );
						$sale_classes    = ' avada-db-sale-button';
					}
				} else {
					$buy_button_text = esc_html__( 'Buy Another License', 'Avada' );
				}
				?>
				<a href="<?php echo esc_url( 'https://1.envato.market/E7kX9' ); ?>" class="fusion-dbw-license button button-primary<?php echo esc_attr( $sale_classes ); ?>" target="_blank" rel="noopener noreferrer"><span class="avada-db-buy-now-button-text"><?php echo esc_html( $buy_button_text ); ?></span></a>
			</div>
			<?php wp_dashboard_primary_output( 'themefusion-news', $feeds ); ?>

			<div class="fusion-dbw-footer">
				<ul>
					<li class="fusion-dbw-footer-blog>"><a href="<?php echo esc_url( 'https://theme-fusion.com/blog/' ); ?>" target="_blank"><?php esc_html_e( 'Blog', 'fusion-core' ); ?> <span class="screen-reader-text"><?php esc_html_e( '(opens in a new window)', 'fusion-core' ); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></li>
					<li class="fusion-dbw-footer-docs>"><a href="<?php echo esc_url( 'https://theme-fusion.com/support/' ); ?>" target="_blank"><?php esc_html_e( 'Docs', 'fusion-core' ); ?> <span class="screen-reader-text"><?php esc_html_e( '(opens in a new window)', 'fusion-core' ); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></li>
					<li class="fusion-dbw-footer-ticket>"><a href="<?php echo esc_url( 'https://theme-fusion.com/support/submit-a-ticket/' ); ?>" target="_blank"><?php esc_html_e( 'Ticket', 'fusion-core' ); ?> <span class="screen-reader-text"><?php esc_html_e( '(opens in a new window)', 'fusion-core' ); ?></span><span aria-hidden="true" class="dashicons dashicons-external"></span></a></li>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Changes the user agent for the Avada news widget.
	 *
	 * @since 4.0
	 * @access public
	 * @param  object $feed  SimplePie feed object, passed by reference.
	 * @param  mixed  $url   URL of feed to retrieve. If an array of URLs, the feeds are merged.
	 * @return void
	 */
	public function feed_user_agent( $feed, $url ) {

		if ( 'https://theme-fusion.com/feed/' === $url ) {
			$feed->set_useragent( 'Avada RSS Feed' );
		}
	}

	/**
	 * Handles PLL-slug translations.
	 *
	 * @access public
	 * @since 6.1
	 * @return void
	 */
	public function manage_pll_slugs() {
		if ( class_exists( 'Fusion_Multilingual' ) && Fusion_Multilingual::is_pll() ) {
			require_once 'class-fusion-pll-post-type.php';
			require_once 'class-fusion-pll-rewrite-slugs.php';
			new Fusion_PLL_Rewrite_Slugs();
			add_filter( 'fusion_pll_translated_post_type_rewrite_slugs', [ $this, 'pll_slugs' ] );
		}
	}

	/**
	 * Filters the slugs for languages.
	 *
	 * @access public
	 * @since 6.1
	 * @param array $slugs An array of slug definitions for PLL.
	 * @return array
	 */
	public function pll_slugs( $slugs ) {
		$langs                  = Fusion_Multilingual::get_available_languages();
		$default_option_name    = Fusion_Settings::get_original_option_name();
		$default_option_val     = get_option( $default_option_name );
		$default_portfolio_slug = ( isset( $default_option_val['portfolio_slug'] ) ) ? $default_option_val['portfolio_slug'] : false;
		$default_faq_slug       = ( isset( $default_option_val['faq_slug'] ) ) ? $default_option_val['faq_slug'] : false;

		$slugs['avada_portfolio'] = [];
		$slugs['avada_faq']       = [];
		foreach ( $langs as $lang ) {

			// Get the option-name for this language.
			$lang_option_name = $default_option_name;
			if ( ! in_array( $lang, [ '', 'en' ], true ) ) {
				$lang_option_name .= '_' . $lang;
			}

			// Get the slug from options.
			$option_val     = $default_option_val;
			$portfolio_slug = $default_portfolio_slug;
			$faq_slug       = $default_faq_slug;
			if ( $lang_option_name !== $default_option_name ) {
				$option_val     = get_option( $lang_option_name );
				$portfolio_slug = ( isset( $option_val['portfolio_slug'] ) ) ? $option_val['portfolio_slug'] : false;
				$faq_slug       = ( isset( $option_val['faq_slug'] ) ) ? $option_val['faq_slug'] : false;
			}

			if ( $portfolio_slug ) {
				$slugs['avada_portfolio'][ $lang ] = [
					'has_archive' => true,
					'rewrite'     => [
						'slug' => $portfolio_slug,
					],
				];
			}
			if ( $faq_slug ) {
				$slugs['avada_faq'][ $lang ] = [
					'has_archive' => true,
					'rewrite'     => [
						'slug' => $faq_slug,
					],
				];
			}
		}

		return $slugs;
	}
}
