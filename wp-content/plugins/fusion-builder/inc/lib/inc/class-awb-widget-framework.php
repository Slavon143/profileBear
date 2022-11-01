<?php
/**
 * A class to create the legacy widget framework.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Fusion-Library
 * @since      3.9
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * A class to create the legacy widget framework.
 */
class AWB_Widget_Framework {

	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @since 3.9
	 * @access private
	 * @var null|object
	 */
	private static $instance = null;

	/**
	 * Sidebar body classes.
	 *
	 * @since 3.9
	 * @access private
	 * @var string
	 */
	private $sidebar_body_classes = [ 'awb-no-sidebars' ];

	/**
	 * An array of all our sidebars.
	 *
	 * @since 3.9
	 * @access public
	 * @var array
	 */
	public $sidebars = [];

	/**
	 * Returns a single instance of the object (singleton).
	 *
	 * @since 3.9
	 * @access public
	 * @return object
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new AWB_Widget_Framework();
		}
		return self::$instance;
	}

	/**
	 * Construct the object.
	 *
	 * @since 5.3.0
	 * @access public
	 */
	public function __construct() {

		// Some class will be added in any case.
		add_filter( 'body_class', [ $this, 'add_body_classes' ] );

		// Check the global option for legacy widget areas.
		$global_options = get_option( 'fusion_options' );
		if ( isset( $global_options['status_widget_areas'] ) && '0' === $global_options['status_widget_areas'] ) {

			// Remove sidebar Page Options.
			add_filter( 'fusion_pagetype_data', [ $this, 'remove_sidebars_page_options' ], 10, 2 );

			// Remove widgets screen.
			add_filter( 'current_theme_supports-widgets', '__return_false' );

			return;
		}

		// Add GO sidebar tab.
		add_filter( 'avada_options_sections', [ $this, 'add_global_options_sidebar' ] );

		// Add widget area (sidebar) related classes to the body tag.
		add_action( 'wp', [ $this, 'set_sidebar_body_classes' ], 15 );

		// Add sidebars to the layout.
		add_action( 'wp', [ $this, 'add_sidebars' ], 20 );
		add_action( 'wp', [ $this, 'add_no_sidebar_layout_styling' ], 20 );     

		// Register the widget areas.
		add_action( 'widgets_init', [ $this, 'register_widget_area' ] );

		// Instantiate AWB_Widget_Style.
		$widget_styles = AWB_Widget_Style::get_instance();

		// Init Widgets.
		if ( defined( 'FUSION_CORE_VERSION' ) && version_compare( FUSION_CORE_VERSION, '5.9', '>=' ) ) {
			$this->load_widget_classes();
			add_action( 'widgets_init', [ $this, 'init_widgets' ] );
		}

		// Add the widget and widget area elements
		add_action( 'awb_init_elements', [ $this, 'init_elements' ] );

		// Add widget related scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );

		// Map deprecated filters.
		add_filter( 'awb_content_tag_class', [ $this, 'fusion_content_class' ], PHP_INT_MAX );
		add_filter( 'awb_content_tag_style', [ $this, 'fusion_content_style' ], PHP_INT_MAX );

		add_filter( 'awb_aside_1_tag_class', [ $this, 'fusion_sidebar_1_class' ], PHP_INT_MAX );
		add_filter( 'awb_aside_1_tag_style', [ $this, 'fusion_sidebar_1_style' ], PHP_INT_MAX );
		add_filter( 'awb_aside_1_tag_data', [ $this, 'fusion_sidebar_1_data' ], PHP_INT_MAX );

		add_filter( 'awb_aside_2_tag_class', [ $this, 'fusion_sidebar_2_class' ], PHP_INT_MAX );
		add_filter( 'awb_aside_2_tag_style', [ $this, 'fusion_sidebar_2_style' ], PHP_INT_MAX );
		add_filter( 'awb_aside_2_tag_data', [ $this, 'fusion_sidebar_2_data' ], PHP_INT_MAX );

	}

	public function add_global_options_sidebar( $sections ) {
		$new_sections = [];
		$counter      = 1;

		foreach ( $sections as $index => $section ) {
			if ( 11 === $counter && class_exists( 'Avada' ) ) {
				include_once Avada::$template_dir_path . '/includes/options/sidebars.php';

				$new_sections = avada_options_section_sidebars( $new_sections );
			} elseif ( 'layout' === $index ) {
				$options = $this->add_layout_section_options();

				$section['fields'] = array_merge( $section['fields'], $options );

			}

			$new_sections[ $index ] = $section;
			$counter++;
		}

		return $new_sections;
	}

	/**
	 * Removes the sidebars tab from Page Options.
	 *
	 * @since 3.9
	 * @access public
	 * @param array  $pagetype_data Array of tabs per post type.
	 * @param string $posttype post The current post type.
	 * @return The filtered tabs per post type array.
	 */
	public function remove_sidebars_page_options( $pagetype_data, $posttype ) {
		

		if ( isset( $pagetype_data[ $posttype ] ) ) {
			$key = array_search( 'sidebars', $pagetype_data[ $posttype ] );
		} else {
			$key      = array_search( 'sidebars', $pagetype_data['default'] );
			$posttype = 'default';
		}
		
		if ( false !== $key ) {
			unset( $pagetype_data[ $posttype ][ $key ] );
		}

		return $pagetype_data;
	}

	public function add_layout_section_options() {
		$options = [
			'single_sidebar_layouts_info' => [
				'label'       => esc_html__( 'Single Sidebar Layouts', 'fusion-builder' ),
				'description' => '',
				'id'          => 'single_sidebar_layouts_info',
				'type'        => 'info',
			],
			'sidebar_width'               => [
				'label'       => esc_html__( 'Single Sidebar Width', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the width of the sidebar when only one sidebar is present.', 'fusion-builder' ),
				'id'          => 'sidebar_width',
				'default'     => '24%',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--sidebar_width',
					],
				],
			],
			'sidebar_gutter'              => [
				'label'       => esc_html__( 'Single Sidebar Gutter', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the space between the main content and a single sidebar.', 'fusion-builder' ),
				'id'          => 'sidebar_gutter',
				'default'     => '6%',
				'type'        => 'dimension',
				'css_vars'    => [
					[
						'name' => '--sidebar_gutter',
					],
				],
			],
			'dual_sidebar_layouts_info'   => [
				'label'       => esc_html__( 'Dual Sidebar Layouts', 'fusion-builder' ),
				'description' => '',
				'id'          => 'dual_sidebar_layouts_info',
				'type'        => 'info',
			],
			'sidebar_2_1_width'           => [
				'label'       => esc_html__( 'Dual Sidebar Width 1', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the width of sidebar 1 when dual sidebars are present.', 'fusion-builder' ),
				'id'          => 'sidebar_2_1_width',
				'default'     => '20%',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--sidebar_2_1_width',
					],
				],
			],
			'sidebar_2_2_width'           => [
				'label'       => esc_html__( 'Dual Sidebar Width 2', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the width of sidebar 2 when dual sidebars are present.', 'fusion-builder' ),
				'id'          => 'sidebar_2_2_width',
				'default'     => '20%',
				'type'        => 'dimension',
				'choices'     => [ 'px', '%' ],
				'css_vars'    => [
					[
						'name' => '--sidebar_2_2_width',
					],
				],
			],
			'dual_sidebar_gutter'         => [
				'label'       => esc_html__( 'Dual Sidebar Gutter', 'fusion-builder' ),
				'description' => esc_html__( 'Controls the space between the main content and the sidebar when dual sidebars are present.', 'fusion-builder' ),
				'id'          => 'dual_sidebar_gutter',
				'default'     => '4%',
				'type'        => 'dimension',
				'css_vars'    => [
					[
						'name' => '--dual_sidebar_gutter',
					],
				],
			],  
		];

		return $options;
	}

	/**
	 * Detect if we have a sidebar.
	 * 
	 * @since 3.9
	 * @access public
	 * @return bool Whether we have a sidebar.
	 */
	public function has_sidebar() {
		return ( apply_filters( 'avada_has_sidebar', in_array( 'has-sidebar', $this->sidebar_body_classes, true ), $this->sidebar_body_classes, 'has-sidebar' ) );
	}

	/**
	 * Detect if we have an events calendar sidebar.
	 * 
	 * @since 3.9
	 * @access public
	 * @return bool Whether we have a TEC sidebar.
	 */
	public function has_sidebar_tec() {

		return ( apply_filters( 'avada_has_sidebar', in_array( 'avada-ec-meta-layout-sidebar', $this->sidebar_body_classes, true ), $this->sidebar_body_classes, 'avada-ec-meta-layout-sidebar' ) );
	}

	/**
	 * Detect if we have double sidebars.
	 * 
	 * @since 3.9
	 * @access public
	 * @return bool Whether we have double sidebars.
	 */
	public function has_double_sidebars() {

		return ( apply_filters( 'avada_has_double_sidebars', in_array( 'double-sidebars', $this->sidebar_body_classes, true ), $this->sidebar_body_classes, 'double-sidebars' ) );
	}

	/**
	 * Set sidebar body classes.
	 * 
	 * @since 3.9
	 * @access public
	 * @return void
	 */
	public function set_sidebar_body_classes() {
		$classes   = [];
		$sidebar_1 = $this->sidebar_context( 1 );
		$sidebar_2 = $this->sidebar_context( 2 );

		$sidebar_1_original = $sidebar_1;
		$sidebar_2_original = $sidebar_2;

		$sidebar_1 = empty( $sidebar_1 ) ? 'None' : $sidebar_1;
		$sidebar_2 = empty( $sidebar_2 ) ? 'None' : $sidebar_2;

		$c_page_id = fusion_library()->get_page_id();

		if ( is_array( $sidebar_1 ) && ! empty( $sidebar_1 ) && ( $sidebar_1[0] || '0' == $sidebar_1[0] ) && ! Fusion_Helper::is_buddypress() && ! Fusion_Helper::is_bbpress() && ! is_page_template( '100-width.php' ) && ! is_page_template( 'blank.php' ) && ( ! class_exists( 'WooCommerce' ) || ( class_exists( 'WooCommerce' ) && ! is_cart() && ! is_checkout() && ! is_account_page() && ! ( get_option( 'woocommerce_thanks_page_id' ) && is_page( get_option( 'woocommerce_thanks_page_id' ) ) ) ) ) ) { // phpcs:ignore WordPress.PHP.StrictComparisons
			$classes[] = 'has-sidebar';
		}

		if ( is_array( $sidebar_1 ) && $sidebar_1[0] && is_array( $sidebar_2 ) && $sidebar_2[0] && ! Fusion_Helper::is_buddypress() && ! Fusion_Helper::is_bbpress() && ! is_page_template( '100-width.php' ) && ! is_page_template( 'blank.php' ) && ( ! class_exists( 'WooCommerce' ) || ( class_exists( 'WooCommerce' ) && ! is_cart() && ! is_checkout() && ! is_account_page() && ! ( get_option( 'woocommerce_thanks_page_id' ) && is_page( get_option( 'woocommerce_thanks_page_id' ) ) ) ) ) ) {
			$classes[] = 'double-sidebars';
		}

		if ( is_page_template( 'side-navigation.php' ) && 0 !== get_queried_object_id() ) {
			$classes[] = 'has-sidebar';

			if ( is_array( $sidebar_2 ) && $sidebar_2[0] ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( is_home() ) {
			if ( 'None' !== $sidebar_1 ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( is_archive() && ( ! ( class_exists( 'BuddyPress' ) && Fusion_Helper::is_buddypress() ) && ! ( class_exists( 'bbPress' ) && Fusion_Helper::is_bbpress() ) && ! ( class_exists( 'Tribe__Events__Main' ) && Fusion_Helper::is_events_archive( $c_page_id ) ) && ( class_exists( 'WooCommerce' ) && ! is_shop() ) || ! class_exists( 'WooCommerce' ) ) && ! is_tax( 'portfolio_category' ) && ! is_tax( 'portfolio_skills' ) && ! is_tax( 'portfolio_tags' ) && ! ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
			if ( 'None' !== $sidebar_1 ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( is_tax( 'portfolio_category' ) || is_tax( 'portfolio_skills' ) || is_tax( 'portfolio_tags' ) ) {
			if ( 'None' !== $sidebar_1 ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() && ! is_search() ) {
			if ( 'None' !== $sidebar_1 ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( is_search() && ! is_tax() ) {
			if ( 'None' !== $sidebar_1 ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( ( Fusion_Helper::is_bbpress() || Fusion_Helper::is_buddypress() ) && ! Fusion_Helper::bbp_is_forum_archive() && ! Fusion_Helper::bbp_is_topic_archive() && ! Avada_Helper::bbp_is_user_home() && ! Fusion_Helper::bbp_is_search() ) {
			if ( fusion_library()->get_option( 'bbpress_global_sidebar' ) ) {
				$sidebar_1 = is_array( $sidebar_1 ) ? $sidebar_1[0] : $sidebar_1;
				$sidebar_1 = empty( $sidebar_1 ) ? 'None' : $sidebar_1;
				$sidebar_2 = is_array( $sidebar_2 ) ? $sidebar_2[0] : $sidebar_2;
				$sidebar_2 = empty( $sidebar_2 ) ? 'None' : $sidebar_2;

				if ( 'None' !== $sidebar_1 ) {
					$classes[] = 'has-sidebar';
				}
				if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
					$classes[] = 'double-sidebars';
				}
			} else {
				if ( is_array( $sidebar_1 ) && $sidebar_1[0] ) {
					$classes[] = 'has-sidebar';
				}
				if ( is_array( $sidebar_1 ) && $sidebar_1[0] && is_array( $sidebar_2 ) && $sidebar_2[0] ) {
					$classes[] = 'double-sidebars';
				}
			}
		}

		if ( ( Fusion_Helper::is_bbpress() || Fusion_Helper::is_buddypress() ) && ( Fusion_Helper::bbp_is_forum_archive() || Fusion_Helper::bbp_is_topic_archive() || Avada_Helper::bbp_is_user_home() || Fusion_Helper::bbp_is_search() ) ) {
			if ( 'None' !== $sidebar_1 ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( class_exists( 'Tribe__Events__Main' ) && Fusion_Helper::is_events_archive( $c_page_id ) && ! is_tag() ) {
			$classes[] = 'tribe-filter-live';

			if ( '100-width.php' !== tribe_get_option( 'tribeEventsTemplate', 'default' ) ) {
				if ( 'None' !== $sidebar_1 ) {
					$classes[] = 'has-sidebar';
				}
				if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 ) {
					$classes[] = 'double-sidebars';
				}
			}
		}

		$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
		if ( $override ) {

			$has_sidebar_key         = array_search( 'has-sidebar', $classes, true );
			$has_double_sidebars_key = array_search( 'double-sidebars', $classes, true );

			if ( is_array( $sidebar_1_original ) && ! empty( $sidebar_1_original ) && $sidebar_1_original[0] ) {
				$classes[] = 'has-sidebar';

				if ( is_array( $sidebar_2_original ) && ! empty( $sidebar_2_original ) && $sidebar_2_original[0] ) {
					$classes[] = 'double-sidebars';
				} elseif ( $has_double_sidebars_key ) {
					unset( $classes[ $has_double_sidebars_key ] );
				}
			} else {
				if ( $has_sidebar_key ) {
					unset( $classes[ $has_sidebar_key ] );
				}
				if ( $has_double_sidebars_key ) {
					unset( $classes[ $has_double_sidebars_key ] );
				}
			}
		}

		$this->sidebar_body_classes = $classes;
	}

	/**
	 * Adds extra classes for the <body> element, using the 'body_class' filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param  array $classes CSS classes.
	 * @return array The merged and extended body classes.
	 */
	public function add_body_classes( $classes ) {
		$classes = array_merge( $classes, $this->sidebar_body_classes );

		return $classes;
	}   

	/**
	 * Returns the sidebar-1 & sidebar-2 context.
	 *
	 * @param int $sidebar Sidebar 1 or 2 (values: 1/2).
	 * @return mixed
	 */
	private function sidebar_context( $sidebar = 1 ) {
		$c_page_id             = fusion_library()->get_page_id();
		$post_type             = get_post_type( $c_page_id );
		$sidebars_option_names = $this->get_sidebar_post_meta_option_names( $post_type );

		// Check for global options first.
		if ( ! is_archive() && $sidebars_option_names[3] && fusion_library()->get_option( $sidebars_option_names[3] ) ) {
			$sidebar_1 = ( 'None' !== fusion_library()->get_option( $sidebars_option_names[0] ) ) ? [ fusion_library()->get_option( $sidebars_option_names[0] ) ] : '';
			$sidebar_2 = ( 'None' !== fusion_library()->get_option( $sidebars_option_names[1] ) ) ? [ fusion_library()->get_option( $sidebars_option_names[1] ) ] : '';

			if ( 2 === $sidebar ) {
				/**
				 * Apply the "avada_sidebar_context" filter.
				 *
				 * @since 6.2.0
				 * @param string     $sidebar_2 The 2nd sidebar.
				 * @param int|string $c_page_id The page-ID.
				 * @param int        $sidebar   The sidebar-nr (1|2).
				 * @param bool       $global    Whether this is a global override or not.
				 * @return string               Returns $sidebar_2.
				 */
				return apply_filters( 'avada_sidebar_context', $sidebar_2, $c_page_id, $sidebar, true );
			}
			/**
			 * Apply the "avada_sidebar_context" filter.
			 *
			 * @since 6.2.0
			 * @param string     $sidebar_1 The 2nd sidebar.
			 * @param int|string $c_page_id The page-ID.
			 * @param int        $sidebar   The sidebar-nr (1|2).
			 * @param bool       $global    Whether this is a global override or not.
			 * @return string               Returns $sidebar_1.
			 */
			return apply_filters( 'avada_sidebar_context', $sidebar_1, $c_page_id, $sidebar, true );
		}

		$sidebar_1 = (array) fusion_get_option( $sidebars_option_names[0] );
		$sidebar_2 = (array) fusion_get_option( $sidebars_option_names[1] );

		$sidebar_1[0] = maybe_unserialize( $sidebar_1[0] );
		$sidebar_1[0] = is_array( $sidebar_1[0] ) ? $sidebar_1[0][0] : $sidebar_1[0];

		$sidebar_2[0] = maybe_unserialize( $sidebar_2[0] );
		$sidebar_2[0] = is_array( $sidebar_2[0] ) ? $sidebar_2[0][0] : $sidebar_2[0];

		$sidebar_1_original = $sidebar_1;
		$sidebar_2_original = $sidebar_2;

		if ( isset( $sidebar_1[0] ) && 'default_sidebar' === $sidebar_1[0] ) {
			$sidebar_1 = [ ( 'None' !== fusion_library()->get_option( $sidebars_option_names[0] ) ) ? fusion_library()->get_option( $sidebars_option_names[0] ) : '' ];
		}

		if ( isset( $sidebar_2[0] ) && 'default_sidebar' === $sidebar_2[0] ) {
			$sidebar_2 = [ ( 'None' !== fusion_library()->get_option( $sidebars_option_names[1] ) ) ? fusion_library()->get_option( $sidebars_option_names[1] ) : '' ];
		}

		if ( is_home() ) {
			$sidebar_1 = fusion_library()->get_option( 'blog_archive_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'blog_archive_sidebar_2' );
		}

		if ( is_archive() && ( ! Fusion_Helper::is_buddypress() && ! Fusion_Helper::is_bbpress() && ( class_exists( 'WooCommerce' ) && ! is_shop() ) || ! class_exists( 'WooCommerce' ) ) && ! is_post_type_archive( 'avada_portfolio' ) && ! is_tax( 'portfolio_category' ) && ! is_tax( 'portfolio_skills' ) && ! is_tax( 'portfolio_tags' ) && ! ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
			$sidebar_1 = fusion_library()->get_option( 'blog_archive_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'blog_archive_sidebar_2' );
		}

		if ( is_post_type_archive( 'avada_portfolio' ) || is_tax( 'portfolio_category' ) || is_tax( 'portfolio_skills' ) || is_tax( 'portfolio_tags' ) ) {
			$sidebar_1 = fusion_library()->get_option( 'portfolio_archive_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'portfolio_archive_sidebar_2' );
		}

		if ( class_exists( 'WooCommerce' ) && ( ( Fusion_Helper::is_woocommerce() && is_tax() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) ) {
			$sidebar_1 = fusion_library()->get_option( 'woocommerce_archive_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'woocommerce_archive_sidebar_2' );
		}

		if ( is_search() ) {
			$sidebar_1 = fusion_library()->get_option( 'search_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'search_sidebar_2' );
		}

		if ( Fusion_Helper::is_buddypress() || Fusion_Helper::bbp_is_forum_archive() || Fusion_Helper::bbp_is_topic_archive() || Avada_Helper::bbp_is_user_home() || Fusion_Helper::bbp_is_search() ) {
			$sidebar_1 = fusion_library()->get_option( 'ppbress_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'ppbress_sidebar_2' );
		}

		if ( class_exists( 'Tribe__Events__Main' ) && Fusion_Helper::is_events_archive( $c_page_id ) && ! is_tag() ) {
			$sidebar_1 = fusion_library()->get_option( 'ec_sidebar' );
			$sidebar_2 = fusion_library()->get_option( 'ec_sidebar_2' );
		}

		if ( isset( $sidebar_1[0] ) && 'string' === gettype( $sidebar_1[0] ) && 'none' === strtolower( $sidebar_1[0] ) ) {
			$sidebar_1[0] = '';
		}

		if ( isset( $sidebar_2[0] ) && 'string' === gettype( $sidebar_2[0] ) && 'none' === strtolower( $sidebar_2[0] ) ) {
			$sidebar_2[0] = '';
		}

		// If we have an override, ignore global.
		if ( 'template_sidebar' === $sidebars_option_names[0] ) {
			$sidebar_1 = fusion_get_option( $sidebars_option_names[0] );
			$sidebar_1 = ! empty( $sidebar_1 ) ? (array) $sidebar_1 : '';

			$sidebar_2 = fusion_get_option( $sidebars_option_names[1] );
			$sidebar_2 = ! empty( $sidebar_2 ) ? (array) $sidebar_2 : '';
		}

		$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
		if ( $override ) {
			$sidebar_1 = $sidebar_1_original;
			$sidebar_2 = $sidebar_2_original;
		}

		if ( 2 === $sidebar ) {
			/**
			 * Apply the "avada_sidebar_context" filter.
			 *
			 * @since 6.2.0
			 * @param string     $sidebar_2 The 2nd sidebar.
			 * @param int|string $c_page_id The page-ID.
			 * @param int        $sidebar   The sidebar-nr (1|2).
			 * @param bool       $global    Whether this is a global override or not.
			 * @return string               Returns $sidebar_2.
			 */
			return apply_filters( 'avada_sidebar_context', $sidebar_2, $c_page_id, $sidebar, false );
		}

		/**
		 * Apply the "avada_sidebar_context" filter.
		 *
		 * @since 6.2.0
		 * @param string     $sidebar_1 The 2nd sidebar.
		 * @param int|string $c_page_id The page-ID.
		 * @param int        $sidebar   The sidebar-nr (1|2).
		 * @param bool       $global    Whether this is a global override or not.
		 * @return string               Returns $sidebar_1.
		 */
		return apply_filters( 'avada_sidebar_context', $sidebar_1, $c_page_id, $sidebar, false );
	}   

	/**
	 * Add sidebar(s) to the pages.
	 *
	 * @return void
	 */
	public function add_sidebars() {

		$load_sidebars = false;

		// Append sidebar to after content div.
		if ( $this->has_sidebar() && ! $this->has_double_sidebars() ) {
			add_action( 'avada_after_content', [ $this, 'append_sidebar_single' ] );
			$load_sidebars = true;
		} elseif ( $this->has_double_sidebars() ) {
			add_action( 'avada_after_content', [ $this, 'append_sidebar_double' ] );
			$load_sidebars = true;
		} elseif ( ! $this->has_sidebar() && ( is_page_template( 'side-navigation.php' ) || ( is_singular( 'tribe_events' ) && 'sidebar' === fusion_library()->get_option( 'ec_meta_layout' ) ) ) ) {
			add_action( 'avada_after_content', [ $this, 'append_sidebar_single' ] );
			$load_sidebars = true;
		}

		if ( $load_sidebars ) {
			// Get the sidebars and assign to public variable.
			$this->sidebars = $this->get_sidebar_settings( $this->sidebar_options() );

			// Set styling to content and sidebar divs.
			$this->add_sidebar_layout_styling( $this->sidebars );

			add_filter( 'fusion_responsive_sidebar_order', [ $this, 'correct_responsive_sidebar_order' ] );
		}
	}

	/**
	 * Get sidebar settings based on the page type.
	 *
	 * @return array
	 */
	public function sidebar_options() {
		$post_id = fusion_library()->get_page_id();

		if ( is_home() ) {
			$sidebars = [
				'global'    => '1',
				'sidebar_1' => fusion_library()->get_option( 'blog_archive_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'blog_archive_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'blog_sidebar_position' ),
			];
		} elseif ( Fusion_Helper::is_bbpress() ) {
			$sidebars = [
				'global'    => fusion_library()->get_option( 'bbpress_global_sidebar' ),
				'sidebar_1' => fusion_library()->get_option( 'ppbress_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'ppbress_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'bbpress_sidebar_position' ),
			];

			if ( Fusion_Helper::bbp_is_forum_archive() || Fusion_Helper::bbp_is_topic_archive() || Avada_Helper::bbp_is_user_home() || Fusion_Helper::bbp_is_search() ) {
				$sidebars = [
					'global'    => '1',
					'sidebar_1' => fusion_library()->get_option( 'ppbress_sidebar' ),
					'sidebar_2' => fusion_library()->get_option( 'ppbress_sidebar_2' ),
					'position'  => fusion_library()->get_option( 'bbpress_sidebar_position' ),
				];
			}
		} elseif ( Fusion_Helper::is_buddypress() ) {
			$sidebars = [
				'global'    => fusion_library()->get_option( 'bbpress_global_sidebar' ),
				'sidebar_1' => fusion_library()->get_option( 'ppbress_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'ppbress_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'bbpress_sidebar_position' ),
			];
		} elseif ( class_exists( 'WooCommerce' ) && ( is_product() || is_shop() ) && ! is_search() ) {
			$sidebars = [
				'global'    => fusion_library()->get_option( 'woo_global_sidebar' ),
				'sidebar_1' => fusion_library()->get_option( 'woo_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'woo_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'woo_sidebar_position' ),
			];
		} elseif ( class_exists( 'WooCommerce' ) && ( ( Fusion_Helper::is_woocommerce() && is_tax() ) || is_product_taxonomy() ) ) {
			$sidebars = [
				'global'    => '1',
				'sidebar_1' => fusion_library()->get_option( 'woocommerce_archive_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'woocommerce_archive_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'woo_sidebar_position' ),
			];
		} elseif ( is_page() ) {
			$sidebars = [
				'global'    => fusion_library()->get_option( 'pages_global_sidebar' ),
				'sidebar_1' => fusion_library()->get_option( 'pages_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'pages_sidebar_2' ),
				'position'  => fusion_get_option( 'default_sidebar_pos' ),
			];
		} elseif ( is_single() ) {
			$sidebars = apply_filters(
				'avada_single_post_sidebar_theme_options',
				[
					'global'    => fusion_library()->get_option( 'posts_global_sidebar' ),
					'sidebar_1' => fusion_library()->get_option( 'posts_sidebar' ),
					'sidebar_2' => fusion_library()->get_option( 'posts_sidebar_2' ),
					'position'  => fusion_library()->get_option( 'blog_sidebar_position' ),
				]
			);

			if ( is_singular( 'avada_portfolio' ) ) {
				$sidebars = [
					'global'    => fusion_library()->get_option( 'portfolio_global_sidebar' ),
					'sidebar_1' => fusion_library()->get_option( 'portfolio_sidebar' ),
					'sidebar_2' => fusion_library()->get_option( 'portfolio_sidebar_2' ),
					'position'  => fusion_library()->get_option( 'portfolio_sidebar_position' ),
				];
			} elseif ( is_singular( 'tribe_events' ) || is_singular( 'tribe_organizer' ) || is_singular( 'tribe_venue' ) ) {
				$sidebars = [
					'global'    => fusion_library()->get_option( 'ec_global_sidebar' ),
					'sidebar_1' => fusion_library()->get_option( 'ec_sidebar' ),
					'sidebar_2' => fusion_library()->get_option( 'ec_sidebar_2' ),
					'position'  => fusion_library()->get_option( 'ec_sidebar_pos' ),
				];

				if ( is_singular( 'tribe_organizer' ) || is_singular( 'tribe_venue' ) ) {
					$sidebars['global'] = 1;
				}
			}
		} elseif ( is_archive() && ! is_search() ) {
			$sidebars = [
				'global'    => '1',
				'sidebar_1' => fusion_library()->get_option( 'blog_archive_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'blog_archive_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'blog_sidebar_position' ),
			];

			if ( is_post_type_archive( 'avada_portfolio' ) || is_tax( 'portfolio_category' ) || is_tax( 'portfolio_skills' ) || is_tax( 'portfolio_tags' ) ) {
				$sidebars = [
					'global'    => '1',
					'sidebar_1' => fusion_library()->get_option( 'portfolio_archive_sidebar' ),
					'sidebar_2' => fusion_library()->get_option( 'portfolio_archive_sidebar_2' ),
					'position'  => fusion_library()->get_option( 'portfolio_sidebar_position' ),
				];
			}
		} elseif ( is_search() ) {
			$sidebars = [
				'global'    => '1',
				'sidebar_1' => fusion_library()->get_option( 'search_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'search_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'search_sidebar_position' ),
			];
		} else {
			$sidebars = [
				'global'    => fusion_library()->get_option( 'pages_global_sidebar' ),
				'sidebar_1' => fusion_library()->get_option( 'pages_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'pages_sidebar_2' ),
				'position'  => fusion_get_option( 'default_sidebar_pos' ),
			];
		}

		if ( Fusion_Helper::is_events_archive( $post_id ) && ! is_tag() ) {
			$sidebars = [
				'global'    => '1',
				'sidebar_1' => fusion_library()->get_option( 'ec_sidebar' ),
				'sidebar_2' => fusion_library()->get_option( 'ec_sidebar_2' ),
				'position'  => fusion_library()->get_option( 'ec_sidebar_pos' ),
			];
		}

		// Remove sidebars from the certain woocommerce pages.
		if ( class_exists( 'WooCommerce' ) ) {
			if ( is_cart() || is_checkout() || is_account_page() || ( get_option( 'woocommerce_thanks_page_id' ) && is_page( get_option( 'woocommerce_thanks_page_id' ) ) ) ) {
				$sidebars = [];
			}
		}

		// Add sticky sidebar Global Option to the array.
		$sidebars['sticky'] = fusion_library()->get_option( 'sidebar_sticky' );

		return $sidebars;
	}

	/**
	 * Get the sidebars.
	 *
	 * @param array $sidebar_options Our sidebar options.
	 * @return array
	 */
	public function get_sidebar_settings( $sidebar_options = [] ) {

		$post_id   = fusion_library()->get_page_id();
		$post_type = get_post_type( $post_id );

		// This is an archive, get the post-type from the taxonomy.
		if ( false !== strpos( $post_id, 'archive' ) ) {
			$term_id = absint( $post_id );
			$term    = get_term( $term_id );

			// Get the taxonomy name.
			$tax_name = ( isset( $term->taxonomy ) ) ? $term->taxonomy : false;
			if ( $tax_name ) { // Make sure tax is OK to avoid PHP errors.

				// Get the taxonomy object from its name, and then the assigned post-type.
				$taxonomy = get_taxonomy( $tax_name );

				// If we found the taxonomy and it has a post-type assigned, set it as our $post_type.
				if ( is_object( $taxonomy ) && isset( $taxonomy->object_type ) && isset( $taxonomy->object_type[0] ) ) {
					$post_type = $taxonomy->object_type[0];
				}
			}
		}

		$sidebars_option_names = $this->get_sidebar_post_meta_option_names( $post_type );

		// Post options.
		$sidebar_1        = (array) fusion_get_option( $sidebars_option_names[0] );
		$sidebar_2        = (array) fusion_get_option( $sidebars_option_names[1] );
		$sidebar_position = strtolower( fusion_get_option( $sidebars_option_names[2] ) );
		$sidebar_sticky   = fusion_get_option( 'sidebar_sticky' );

		$sidebar_1[0] = maybe_unserialize( $sidebar_1[0] );
		$sidebar_1[0] = is_array( $sidebar_1[0] ) ? $sidebar_1[0][0] : $sidebar_1[0];
		$sidebar_2[0] = maybe_unserialize( $sidebar_2[0] );
		$sidebar_2[0] = is_array( $sidebar_2[0] ) ? $sidebar_2[0][0] : $sidebar_2[0];

		// If we have an override, ignore global.
		if ( 'template_sidebar' === $sidebars_option_names[0] ) {
			$sidebar_options['global'] = false;
		}

		if ( is_array( $sidebar_1 ) && '0' === $sidebar_1[0] ) {
			$sidebar_1 = [ 'Blog Sidebar' ];
		}

		if ( is_array( $sidebar_2 ) && '0' === $sidebar_2[0] ) {
			$sidebar_2 = [ 'Blog Sidebar' ];
		}

		// Get sidebars and position from global options if it's being forced globally.
		if ( array_key_exists( 'global', $sidebar_options ) && $sidebar_options['global'] ) {
			$sidebar_1        = [ ( 'None' !== $sidebar_options['sidebar_1'] ) ? $sidebar_options['sidebar_1'] : '' ];
			$sidebar_2        = [ ( 'None' !== $sidebar_options['sidebar_2'] ) ? $sidebar_options['sidebar_2'] : '' ];
			$sidebar_position = strtolower( $sidebar_options['position'] );

		} else {
			if ( isset( $sidebar_1[0] ) && 'default_sidebar' === $sidebar_1[0] ) {
				$sidebar_1 = [ ( 'None' !== $sidebar_options['sidebar_1'] ) ? $sidebar_options['sidebar_1'] : '' ];
			}

			if ( isset( $sidebar_2[0] ) && 'default_sidebar' === $sidebar_2[0] ) {
				$sidebar_2 = [ ( 'None' !== $sidebar_options['sidebar_2'] ) ? $sidebar_options['sidebar_2'] : '' ];
			}
		}

		// If sidebar position is default.
		if ( 'default' === $sidebar_position || ! $sidebar_position ) {
			$sidebar_position = fusion_library()->get_option( $sidebars_option_names[2] );
		}

		// Reverse sidebar position if double sidebars are used and position is right.
		if ( $this->has_double_sidebars() && 'right' === $sidebar_position ) {
			$sidebar_1_placeholder = $sidebar_1;
			$sidebar_2_placeholder = $sidebar_2;

			// Reverse the sidebars.
			$sidebar_1 = $sidebar_2_placeholder;
			$sidebar_2 = $sidebar_1_placeholder;
		}

		// Set the sticky sidebar option.
		if ( 'default' === $sidebar_sticky || empty( $sidebar_sticky ) ) {
			$sidebar_sticky = $sidebar_options['sticky'];
		}

		$return = [
			'position' => $sidebar_position,
			'sticky'   => $sidebar_sticky,
		];

		if ( $sidebar_1 ) {
			$return['sidebar_1'] = $sidebar_1[0];
		}

		if ( $sidebar_2 ) {
			$return['sidebar_2'] = $sidebar_2[0];
		}

		// Add sidebar 1 margin, if double sidebars are used.
		if ( $this->has_double_sidebars() ) {
			$half_margin = 'calc(' . str_replace( 'calc', '', fusion_library()->get_option( 'sidebars_gutter' ) ) . ' / 2)';

			$sidebar_2_1_width = Fusion_Sanitize::size( fusion_library()->get_option( 'sidebar_2_1_width' ) );
			if ( false === strpos( $sidebar_2_1_width, 'px' ) && false === strpos( $sidebar_2_1_width, '%' ) ) {
				$sidebar_2_1_width = ( 100 > intval( $sidebar_2_1_width ) ) ? intval( $sidebar_2_1_width ) . '%' : intval( $sidebar_2_1_width ) . 'px';
			}

			$sidebar_2_2_width = Fusion_Sanitize::size( fusion_library()->get_option( 'sidebar_2_2_width' ) );
			if ( false === strpos( $sidebar_2_2_width, 'px' ) && false === strpos( $sidebar_2_2_width, '%' ) ) {
				$sidebar_2_2_width = ( 100 > intval( $sidebar_2_2_width ) ) ? intval( $sidebar_2_2_width ) . '%' : intval( $sidebar_2_2_width ) . 'px';
			}

			$sidebar_2_1_margin = Fusion_Sanitize::add_css_values( [ '-100%', $half_margin, $sidebar_2_2_width ] );
			$sidebar_2_2_margin = $half_margin;

			$return['sidebar_1_data'] = [
				'width'  => $sidebar_2_1_width,
				'margin' => $sidebar_2_1_margin,
			];

			$return['sidebar_2_data'] = [
				'width'  => $sidebar_2_2_width,
				'margin' => $sidebar_2_2_margin,
			];
		}

		return $return;
	}

	/**
	 * Get the post-meta name depending on the post-type.
	 *
	 * @since 6.2.0
	 * @param string $post_type The post-type.
	 * @return string
	 */
	public function get_sidebar_post_meta_option_names( $post_type ) {
		$sidebars = [ '', '', '', '' ];

		switch ( $post_type ) {
			case 'page':
				$sidebars = [ 'pages_sidebar', 'pages_sidebar_2', 'default_sidebar_pos', 'pages_global_sidebar' ];
				break;

			case 'avada_portfolio':
				$sidebars = [ 'portfolio_sidebar', 'portfolio_sidebar_2', 'portfolio_sidebar_position', 'portfolio_global_sidebar' ];
				break;

			case 'product':
				$sidebars = [ 'woo_sidebar', 'woo_sidebar_2', 'woo_sidebar_position', 'woo_global_sidebar' ];
				break;

			case 'tribe_events':
			case 'tribe_organizer':
			case 'tribe_venue':
				$sidebars = [ 'ec_sidebar', 'ec_sidebar_2', 'ec_sidebar_pos', 'ec_global_sidebar' ];
				break;

			case 'forum':
			case 'topic':
			case 'reply':
				$sidebars = [ 'ppbress_sidebar', 'ppbress_sidebar_2', 'bbpress_sidebar_position', 'bbpress_global_sidebar' ];
				break;

			case false:
				break;

			default:
				$sidebars = [ 'posts_sidebar', 'posts_sidebar_2', 'blog_sidebar_position', 'posts_global_sidebar' ];
				break;
		}

		$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
		if ( $override ) {
			$sidebars[0] = 'template_sidebar';
			$sidebars[1] = 'template_sidebar_2';
			$sidebars[2] = 'template_sidebar_position';
		}

		return apply_filters( 'avada_sidebar_post_meta_option_names', $sidebars, $post_type );
	}   

	/**
	 * Apply inline styling and classes to the layout structure when no sidebars are used.
	 *
	 * @since 5.3
	 * @access public
	 * @return void
	 */
	public function add_no_sidebar_layout_styling() {

		// Check for sidebar location and apply styling to the content or sidebar div.
		if ( ! $this->has_sidebar() && ! ( ( is_page_template( 'side-navigation.php' ) && 0 !== get_queried_object_id() ) || is_singular( 'tribe_events' ) ) ) {
			add_filter( 'awb_content_tag_style', [ $this, 'full_width_content_style' ] );

			if ( is_archive() || is_home() ) {
				add_filter( 'awb_content_tag_class', [ $this, 'full_width_content_class' ] );
			}
		}
	}

	/**
	 * Apply inline styling and classes to the layout structure when sidebars are used.
	 *
	 * @param array $sidebars The sidebars array.
	 * @return void
	 */
	public function add_sidebar_layout_styling( $sidebars ) {

		// Add sidebar class.
		add_filter( 'awb_aside_1_tag_class', [ $this, 'sidebar_class' ] );
		add_filter( 'awb_aside_2_tag_class', [ $this, 'sidebar_class' ] );

		add_filter( 'awb_aside_1_tag_class', [ $this, 'sidebar_1_name_class' ] );
		add_filter( 'awb_aside_2_tag_class', [ $this, 'sidebar_2_name_class' ] );

		// Add sidebar sticky class.
		add_filter( 'awb_aside_1_tag_class', [ $this, 'sidebar_sticky_class' ] );
		add_filter( 'awb_aside_2_tag_class', [ $this, 'sidebar_sticky_class' ] );

		// Check for sidebar location and apply styling to the content or sidebar div.
		if ( ! $this->has_sidebar() && ! ( ( is_page_template( 'side-navigation.php' ) && 0 !== get_queried_object_id() ) || is_singular( 'tribe_events' ) ) ) {
			add_filter( 'awb_content_tag_style', [ $this, 'full_width_content_style' ] );

			if ( is_archive() || is_home() ) {
				add_filter( 'awb_content_tag_class', [ $this, 'full_width_content_class' ] );
			}
		} elseif ( 'left' === $sidebars['position'] ) {
			add_filter( 'awb_content_tag_style', [ $this, 'float_right_style' ] );
			add_filter( 'awb_aside_1_tag_style', [ $this, 'float_left_style' ] );
			add_filter( 'awb_aside_1_tag_class', [ $this, 'side_nav_left_class' ] );
		} elseif ( 'right' === $sidebars['position'] ) {
			add_filter( 'awb_content_tag_style', [ $this, 'float_left_style' ] );
			add_filter( 'awb_aside_1_tag_style', [ $this, 'float_right_style' ] );
			add_filter( 'awb_aside_1_tag_class', [ $this, 'side_nav_right_class' ] );
		}

		// Page has double sidebars.
		if ( $this->has_double_sidebars() ) {
			add_filter( 'awb_content_tag_style', [ $this, 'float_left_style' ] );
			add_filter( 'awb_aside_1_tag_style', [ $this, 'float_left_style' ] );
			add_filter( 'awb_aside_2_tag_style', [ $this, 'float_left_style' ] );

			if ( 'right' === $sidebars['position'] ) {
				add_filter( 'awb_aside_2_tag_class', [ $this, 'side_nav_right_class' ] );
			}
		}

	}

	/**
	 * Add full width inline styling to the content tag.
	 *
	 * @since 3.9
	 * @access public
	 * @param  string $styles A string of style attributes.
	 * @return string
	 */
	public function full_width_content_style( $styles ) {
		$styles .= 'width: 100%;';
		return $styles;
	}

	/**
	 * Add full width class to the content tag.
	 *
	 * @since 3.9
	 * @access public
	 * @param  string $classes Classes to apply to the content tag.
	 * @return string
	 */
	public function full_width_content_class( $classes ) {
		$classes .= ' full-width';
		return $classes;
	}

	/**
	 * Float right styling.
	 *
	 * @since 3.9
	 * @access public
	 * @param  string $styles A string of style attributes.
	 * @return string
	 */
	public function float_right_style( $styles ) {
		$styles .= 'float: right;';
		return $styles;
	}

	/**
	 * Float left styling.
	 *
	 * @since 3.9
	 * @access public
	 * @param  string $styles A string of style attributes.
	 * @return string
	 */
	public function float_left_style( $styles ) {
		$styles .= 'float: left;';
		return $styles;
	}

	/**
	 * Add sidebar class to the sidebars.
	 *
	 * @param  string $classes Classes to apply to the aside tag.
	 * @return string
	 */
	public function sidebar_class( $classes ) {
		$classes .= 'sidebar fusion-widget-area fusion-content-widget-area';
		return $classes;
	}

	/**
	 * Add sidebar name as class for sidebar 1.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $classes Classes to apply to the aside tag.
	 * @return string $classes Classes to apply to the aside tag including sidebar name.
	 */
	public function sidebar_1_name_class( $classes ) {
		$sidebar_position = ( 'right' !== $this->sidebars['position'] || ( isset( $this->sidebars['sidebar_2'] ) && '' !== $this->sidebars['sidebar_2'] ) ) ? 'left' : 'right';
		$sidebar_name     = isset( $this->sidebars['sidebar_1'] ) ? ' fusion-' . strtolower( sidebar_generator::name_to_class( $this->sidebars['sidebar_1'] ) ) : ' fusion-default-sidebar';
		$classes         .= ' fusion-sidebar-' . $sidebar_position . $sidebar_name;

		return $classes;
	}

	/**
	 * Add sidebar name as class for sidebar 2.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $classes Classes to apply to the aside tag.
	 * @return string $classes Classes to apply to the aside tag including sidebar name
	 */
	public function sidebar_2_name_class( $classes ) {
		$classes .= ' fusion-sidebar-right fusion-' . strtolower( sidebar_generator::name_to_class( $this->sidebars['sidebar_2'] ) );
		return $classes;
	}

	/**
	 * Add sidebar sticky class to the sidebars.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $classes Classes to apply to the aside tag.
	 * @return string $classes Classes to apply to the aside tag including sidebar sticky class.
	 */
	public function sidebar_sticky_class( $classes ) {

		// If sticky param is either not defined yet or set to none, there is nothing to do.
		if ( ! isset( $this->sidebars['sticky'] ) || ! $this->sidebars['sticky'] || 'none' === $this->sidebars['sticky'] ) {
			return $classes;
		} elseif ( 'both' === $this->sidebars['sticky'] ) {
			$classes .= ' fusion-sticky-sidebar';
		} elseif ( false !== strpos( $classes, 'fusion-sidebar-left' ) && 'left' === $this->sidebars['position'] && 'sidebar_one' === $this->sidebars['sticky'] ) {
			$classes .= ' fusion-sticky-sidebar';
		} elseif ( false !== strpos( $classes, 'fusion-sidebar-right' ) && 'right' === $this->sidebars['position'] && 'sidebar_one' === $this->sidebars['sticky'] ) {
			$classes .= ' fusion-sticky-sidebar';
		} elseif ( false !== strpos( $classes, 'fusion-sidebar-left' ) && 'right' === $this->sidebars['position'] && 'sidebar_two' === $this->sidebars['sticky'] ) {
			$classes .= ' fusion-sticky-sidebar';
		} elseif ( false !== strpos( $classes, 'fusion-sidebar-right' ) && 'left' === $this->sidebars['position'] && 'sidebar_two' === $this->sidebars['sticky'] ) {
			$classes .= ' fusion-sticky-sidebar';
		}

		return $classes;
	}

	/**
	 * Add side nav right class when sidebar position is right.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $classes A string of style attributes.
	 * @return string
	 */
	public function side_nav_right_class( $classes ) {
		if ( is_page_template( 'side-navigation.php' ) ) {
			$classes .= ' side-nav-right';
		}
		return $classes;
	}

	/**
	 * Add side nav left class when sidebar position is left.
	 *
	 * @since 3.9
	 * @access public
	 * @param  string $classes A string of style attributes.
	 * @return string
	 */
	public function side_nav_left_class( $classes ) {
		if ( is_page_template( 'side-navigation.php' ) ) {
			$classes .= ' side-nav-left';
		}
		return $classes;
	}

	/**
	 * Changes the responsive sidebar order, if right positioning and dounble sidebars are used..
	 *
	 * @since 3.9
	 * @access public
	 * @param array $sidebar_order The ordered array of sidebars.
	 * @return array The changed ordered sidebar array.
	 */
	public function correct_responsive_sidebar_order( $sidebar_order ) {
		if ( isset( $this->sidebars['sidebar_2_data'] ) && 'right' === $this->sidebars['position'] ) {
			foreach ( $sidebar_order as $key => $element ) {
				if ( 'sidebar' === $element ) {
					$sidebar_order[ $key ] = 'sidebar-2';
				} elseif ( 'sidebar-2' === $element ) {
					$sidebar_order[ $key ] = 'sidebar';
				}
			}
		}

		return $sidebar_order;
	}

	/**
	 * Append single sidebar to a page.
	 *
	 * @return void
	 */
	public function append_sidebar_single() {
		include FUSION_LIBRARY_PATH . '/inc/templates/sidebar-1.php';
	}

	/**
	 * Append double sidebar to a page.
	 *
	 * @return void
	 */
	public function append_sidebar_double() {
		include FUSION_LIBRARY_PATH . '/inc/templates/sidebar-1.php';
		include FUSION_LIBRARY_PATH . '/inc/templates/sidebar-2.php';
	}

	/**
	 * Register widget areas.
	 * 
	 * @since 3.9
	 * @access public
	 * @return void 
	 */
	public function register_widget_area() {

		// Main Blog widget area.
		register_sidebar(
			[
				'name'          => __( 'Default Widget Area', 'fusion-builder' ),
				'id'            => 'avada-blog-sidebar',
				'description'   => __( 'Default widget area of Avada', 'fusion-builder' ),
				'before_widget' => '<section id="%1$s" class="widget %2$s">',
				'after_widget'  => '</section>',
				'before_title'  => '<div class="heading"><h4 class="widget-title">',
				'after_title'   => '</h4></div>',
			]
		);

		// Footer widget areas.
		$columns = (int) fusion_library()->get_option( 'footer_widgets_columns' ) + 1;
		$columns = ( ! $columns || 1 === $columns ) ? 5 : $columns;

		for ( $i = 1; $i < $columns; $i++ ) {

			register_sidebar(
				[
					'name'          => sprintf( __( 'Footer Widget Area %s', 'fusion-builder' ), $i ),
					'id'            => 'avada-footer-widget-' . $i,
					'before_widget' => '<section id="%1$s" class="fusion-footer-widget-column widget %2$s">',
					'after_widget'  => '<div style="clear:both;"></div></section>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				]
			);

		}

		// Sliding bar widget areas.
		$columns = (int) fusion_library()->get_option( 'slidingbar_widgets_columns' ) + 1;
		$columns = ( ! $columns || 1 === $columns ) ? 5 : $columns;

		for ( $i = 1; $i < $columns; $i++ ) {

			register_sidebar(
				[
					'name'          => sprintf( __( 'Sliding Bar Widget Area %s', 'fusion-builder' ), $i ),
					'id'            => 'avada-slidingbar-widget-' . $i,
					'before_widget' => '<section id="%1$s" class="fusion-slidingbar-widget-column widget %2$s">',
					'after_widget'  => '<div style="clear:both;"></div></section>',
					'before_title'  => '<h4 class="widget-title">',
					'after_title'   => '</h4>',
				]
			);

		}
	}

	/**
	 * load widget classes.
	 *
	 * @since 3.9
	 * @access public
	 * @return void
	 */
	public function load_widget_classes() {
		$filenames = glob( FUSION_LIBRARY_PATH . '/inc/widgets/*.php', GLOB_NOSORT );
		foreach ( $filenames as $filename ) {
			require_once wp_normalize_path( $filename );
		}
	}

	/**
	 * Register widgets.
	 *
	 * @since 3.9
	 * @access public
	 * @return void
	 */
	public function init_widgets() {

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
	 * Get registered widget areas.
	 *
	 * @since 3.9
	 * @access public
	 * @return array
	 */
	public function get_widget_areas() {
		global $wp_registered_sidebars;

		$widget_areas = [];

		foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
			$widget_areas[ $sidebar_id ] = $sidebar['name'];
		}

		return $widget_areas;
	}   

	/**
	 * Add styles and scripts.
	 *
	 * @since 3.9
	 * @access public
	 * @return void
	 */
	public function init_elements() {
		if ( defined( 'FUSION_BUILDER_PLUGIN_URL' ) ) {
			require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/fusion-widget-area.php';
			require_once FUSION_BUILDER_PLUGIN_DIR . 'shortcodes/fusion-widget.php';
		}
	}

	/**
	 * Add widget relatedscripts.
	 *
	 * @since 3.9
	 * @access public
	 * @return void
	 */
	public function enqueue_script() {
		global $fusion_library_latest_version;

		if ( $this->has_sidebar() || $this->has_double_sidebars() || $this->has_sidebar_tec() ) {
			Fusion_Dynamic_JS::enqueue_script(
				'awb-widget-areas',
				Fusion_Scripts::$js_folder_url . '/general/awb-widget-areas.js',
				Fusion_Scripts::$js_folder_path . '/general/awb-widget-areas.js',
				[ 'jquery', 'modernizr', 'jquery-sticky-kit' ],
				$fusion_library_latest_version,
				true
			);

			Fusion_Dynamic_JS::localize_script(
				'awb-widget-areas',
				'avadaSidebarsVars',
				[
					'header_position'            => fusion_get_option( 'header_position' ),
					'header_layout'              => fusion_library()->get_option( 'header_layout' ),
					'header_sticky'              => fusion_get_option( 'header_sticky' ),
					'header_sticky_type2_layout' => fusion_library()->get_option( 'header_sticky_type2_layout' ),
					'side_header_break_point'    => fusion_library()->get_option( 'side_header_break_point' ) ? (int) fusion_library()->get_option( 'side_header_break_point' ) : 800,
					'header_sticky_tablet'       => fusion_get_option( 'header_sticky_tablet' ),
					'sticky_header_shrinkage'    => fusion_get_option( 'header_sticky_shrinkage' ),
					'nav_height'                 => (int) fusion_library()->get_option( 'nav_height' ),
					'sidebar_break_point'        => fusion_library()->get_option( 'sidebar_break_point' ),
				]
			);          
		}
	}

	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_content_class( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}

	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_content_style( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}

	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_sidebar_1_class( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}
	
	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_sidebar_1_style( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}
	
	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_sidebar_1_data( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}

	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_sidebar_2_class( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}
	
	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_sidebar_2_style( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}
	
	/**
	 * Hook deprecated filter to new filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $elements The elements of the new filter.
	 * @return string
	 */
	public function fusion_sidebar_2_data( $elements ) {
		return $this->map_deprecated_filters( __FUNCTION__, $elements );
	}   

	/**
	 * Join the elements of the deprecated filter.
	 *
	 * @since 3.9
	 * @access public
	 * @param string $tag The filter name.
	 * @param array  $elements The elements to be joined.
	 * @return string
	 */
	public function map_deprecated_filters( $tag, $elements ) {
		$array_elements = apply_filters( $tag, [] );

		if ( ! empty( $array_elements ) ) {
			$spacer   = empty( $elements ) ? '' : ' ';
			$elements = $spacer . join( ' ', $array_elements );
		}

		return $elements;
	}   
}

function AWB_Widget_Framework() {
	return AWB_Widget_Framework::get_instance();
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
