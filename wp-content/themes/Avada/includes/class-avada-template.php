<?php
/**
 * Templates handler.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Template handler.
 */
class Avada_Template {

	/**
	 * An array of body classes to be added.
	 *
	 * @access private
	 * @since 5.0.0
	 * @var array
	 */
	private $body_classes = [];

	/**
	 * The class constructor
	 */
	public function __construct() {
		add_action( 'wp', [ $this, 'init' ], 20 );

		add_filter( 'the_password_form', [ $this, 'the_password_form' ] );
	}

	/**
	 * Initialize the class.
	 *
	 * @since 5.0.0
	 *
	 * @return void
	 */
	public function init() {
		$this->body_classes = $this->body_classes( [] );

		add_filter( 'body_class', [ $this, 'body_class_filter' ] );

		add_filter( 'fusion_element_attributes_args', [ $this, 'body_attributes_filter' ], 10, 2 );
	}

	/**
	 * Detect if we have a sidebar.
	 */
	public function has_sidebar() {
		// Get our extra body classes.
		return ( apply_filters( 'avada_has_sidebar', in_array( 'has-sidebar', $this->body_classes, true ), $this->body_classes, 'has-sidebar' ) );
	}

	/**
	 * Detect if we have an events calendar sidebar.
	 */
	public function has_ec_sidebar() {
		// Get our extra body classes.
		return ( apply_filters( 'avada_has_sidebar', in_array( 'avada-ec-meta-layout-sidebar', $this->body_classes, true ), $this->body_classes, 'avada-ec-meta-layout-sidebar' ) );
	}

	/**
	 * Detect if we have double sidebars.
	 */
	public function double_sidebars() {

		// Get our extra body classes.
		return ( apply_filters( 'avada_has_double_sidebars', in_array( 'double-sidebars', $this->body_classes, true ), $this->body_classes, 'double-sidebars' ) );
	}

	/**
	 * Returns the sidebar-1 & sidebar-2 context.
	 *
	 * @param int $sidebar Sidebar 1 or 2 (values: 1/2).
	 * @return mixed
	 */
	private function sidebar_context( $sidebar = 1 ) {

		$c_page_id             = Avada()->fusion_library->get_page_id();
		$post_type             = get_post_type( $c_page_id );
		$sidebars_option_names = avada_get_sidebar_post_meta_option_names( $post_type );

		// Check for global options first.
		if ( ! is_archive() && $sidebars_option_names[3] && Avada()->settings->get( $sidebars_option_names[3] ) ) {
			$sidebar_1 = ( 'None' !== Avada()->settings->get( $sidebars_option_names[0] ) ) ? [ Avada()->settings->get( $sidebars_option_names[0] ) ] : '';
			$sidebar_2 = ( 'None' !== Avada()->settings->get( $sidebars_option_names[1] ) ) ? [ Avada()->settings->get( $sidebars_option_names[1] ) ] : '';

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
			$sidebar_1 = [ ( 'None' !== Avada()->settings->get( $sidebars_option_names[0] ) ) ? Avada()->settings->get( $sidebars_option_names[0] ) : '' ];
		}

		if ( isset( $sidebar_2[0] ) && 'default_sidebar' === $sidebar_2[0] ) {
			$sidebar_2 = [ ( 'None' !== Avada()->settings->get( $sidebars_option_names[1] ) ) ? Avada()->settings->get( $sidebars_option_names[1] ) : '' ];
		}

		if ( is_home() ) {
			$sidebar_1 = Avada()->settings->get( 'blog_archive_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'blog_archive_sidebar_2' );
		}

		if ( is_archive() && ( ! Fusion_Helper::is_buddypress() && ! Fusion_Helper::is_bbpress() && ( class_exists( 'WooCommerce' ) && ! is_shop() ) || ! class_exists( 'WooCommerce' ) ) && ! is_post_type_archive( 'avada_portfolio' ) && ! is_tax( 'portfolio_category' ) && ! is_tax( 'portfolio_skills' ) && ! is_tax( 'portfolio_tags' ) && ! ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) {
			$sidebar_1 = Avada()->settings->get( 'blog_archive_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'blog_archive_sidebar_2' );
		}

		if ( is_post_type_archive( 'avada_portfolio' ) || is_tax( 'portfolio_category' ) || is_tax( 'portfolio_skills' ) || is_tax( 'portfolio_tags' ) ) {
			$sidebar_1 = Avada()->settings->get( 'portfolio_archive_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'portfolio_archive_sidebar_2' );
		}

		if ( class_exists( 'WooCommerce' ) && ( ( Fusion_Helper::is_woocommerce() && is_tax() ) || ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) ) ) {
			$sidebar_1 = Avada()->settings->get( 'woocommerce_archive_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'woocommerce_archive_sidebar_2' );
		}

		if ( is_search() ) {
			$sidebar_1 = Avada()->settings->get( 'search_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'search_sidebar_2' );
		}

		if ( Fusion_Helper::is_buddypress() || Fusion_Helper::bbp_is_forum_archive() || Fusion_Helper::bbp_is_topic_archive() || Avada_Helper::bbp_is_user_home() || Fusion_Helper::bbp_is_search() ) {
			$sidebar_1 = Avada()->settings->get( 'ppbress_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'ppbress_sidebar_2' );
		}

		if ( class_exists( 'Tribe__Events__Main' ) && Fusion_Helper::is_events_archive( $c_page_id ) && ! is_tag() ) {
			$sidebar_1 = Avada()->settings->get( 'ec_sidebar' );
			$sidebar_2 = Avada()->settings->get( 'ec_sidebar_2' );
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
	 * Filters body element's attributes.
	 *
	 * @since 7.8
	 *
	 * @param  array $args Element attributes.
	 * @param  array $el   Element tag.
	 * @return array The merged and extended body classes.
	 */
	public function body_attributes_filter( $args, $el ) {

		if ( 'body' === $el && ! isset( $args['data-awb-post-id'] ) ) {
			$args['data-awb-post-id'] = get_the_ID();
		}

		return $args;
	}

	/**
	 * Adds extra classes for the <body> element, using the 'body_class' filter.
	 * Documentation: https://codex.wordpress.org/Plugin_API/Filter_Reference/body_class
	 *
	 * @since 5.0.0
	 *
	 * @param  array $classes CSS classes.
	 * @return array The merged and extended body classes.
	 */
	public function body_class_filter( $classes ) {
		$classes = array_merge( $classes, $this->body_classes );

		return $classes;
	}

	/**
	 * Calculate any extra classes for the <body> element.
	 *
	 * @param  array $classes CSS classes.
	 * @return array The needed body classes.
	 */
	private function body_classes( $classes ) {

		$sidebar_1 = $this->sidebar_context( 1 );
		$sidebar_2 = $this->sidebar_context( 2 );

		$sidebar_1_original = $sidebar_1;
		$sidebar_2_original = $sidebar_2;

		$sidebar_1 = empty( $sidebar_1 ) ? 'None' : $sidebar_1;
		$sidebar_2 = empty( $sidebar_2 ) ? 'None' : $sidebar_2;

		$c_page_id = Avada()->fusion_library->get_page_id();

		$classes[] = 'fusion-body';

		if ( ! is_rtl() ) {
			$classes[] = 'ltr';
		}

		if ( is_page_template( 'blank.php' ) ) {
			$classes[] = 'fusion-blank-page';
		}

		if ( fusion_get_option( 'header_sticky' ) ) {
			$classes[] = 'fusion-sticky-header';
		}
		if ( ! fusion_get_option( 'header_sticky_tablet' ) ) {
			$classes[] = 'no-tablet-sticky-header';
		}
		if ( ! fusion_get_option( 'header_sticky_mobile' ) ) {
			$classes[] = 'no-mobile-sticky-header';
		}
		if ( ! Avada()->settings->get( 'mobile_slidingbar_widgets' ) ) {
			$classes[] = 'no-mobile-slidingbar';
		}
		if ( 'mobile' === fusion_get_option( 'status_totop' ) || 'off' === fusion_get_option( 'status_totop' ) ) {
			$classes[] = 'no-desktop-totop';
		}
		if ( false === strpos( fusion_get_option( 'status_totop' ), 'mobile' ) ) {
			$classes[] = 'no-mobile-totop';
		}
		if ( fusion_get_option( 'avada_rev_styles' ) ) {
			$classes[] = 'avada-has-rev-slider-styles';
		}
		if ( ! fusion_get_option( 'status_outline' ) ) {
			$classes[] = 'fusion-disable-outline';
		}

		$classes[] = 'fusion-sub-menu-' . Avada()->settings->get( 'main_menu_sub_menu_animation' );

		$classes[] = 'mobile-logo-pos-' . strtolower( Avada()->settings->get( 'logo_alignment' ) );

		$classes[] = 'layout-' . strtolower( fusion_get_option( 'layout' ) ) . '-mode';

		$classes[] = 'avada-has-boxed-modal-shadow-' . fusion_get_option( 'boxed_modal_shadow' );

		$classes[] = 'layout-scroll-offset-' . Avada()->settings->get( 'scroll_offset' );

		if ( 0 === intval( fusion_get_option( 'margin_offset[top]' ) ) ) {
			$classes[] = 'avada-has-zero-margin-offset-top';
		}

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
			if ( Avada()->settings->get( 'bbpress_global_sidebar' ) ) {
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

		if ( 'no' !== fusion_get_page_option( 'display_header', $c_page_id ) ) {
			if ( 'left' === fusion_get_option( 'header_position' ) || 'right' === fusion_get_option( 'header_position' ) ) {
				$classes[] = 'side-header';
			} else {
				$classes[] = 'fusion-top-header';
			}

			if ( 'left' === fusion_get_option( 'header_position' ) ) {
				$classes[] = 'side-header-left';
			} elseif ( 'right' === fusion_get_option( 'header_position' ) ) {
				$classes[] = 'side-header-right';
			}
			$classes[] = 'menu-text-align-' . strtolower( Avada()->settings->get( 'menu_text_align' ) );
		} else {
			$classes[] = 'avada-has-header-hidden';
		}

		if ( class_exists( 'WooCommerce' ) ) {
			$classes[] = 'fusion-woo-product-design-' . Avada()->settings->get( 'woocommerce_product_box_design' );

			$classes[] = 'fusion-woo-shop-page-columns-' . fusion_get_option( 'woocommerce_shop_page_columns' );
			$classes[] = 'fusion-woo-related-columns-' . fusion_get_option( 'woocommerce_related_columns' );
			$classes[] = 'fusion-woo-archive-page-columns-' . fusion_get_option( 'woocommerce_archive_page_columns' );

			if ( Avada()->settings->get( 'woocommerce_equal_heights' ) ) {
				$classes[] = 'fusion-woocommerce-equal-heights';
			}

			if ( Avada()->settings->get( 'woocommerce_one_page_checkout' ) && is_checkout() && ! fusion_library()->woocommerce->is_checkout_layout() ) {
				$classes[] = 'avada-woo-one-page-checkout';
			}

			if ( 'avada' === apply_filters( 'avada_woocommerce_product_images_layout', 'avada' ) ) {
				$classes[] = 'avada-has-woo-gallery-disabled';
			}

			$using_woo_horizontal_tabs = apply_filters( 'fusion_add_woo_horizontal_tabs_body_class', 'horizontal' === Avada()->settings->get( 'woocommerce_product_tab_design' ) && ( is_singular( 'product' ) || ( is_account_page() || is_checkout() && ! fusion_library()->woocommerce->is_checkout_layout() ) ) );
			if ( $using_woo_horizontal_tabs ) {
				$classes[] = 'woo-tabs-horizontal';
			}

			// Woo sale badge shape flag.
			if ( 'circle' === Avada()->settings->get( 'woo_sale_badge_shape' ) ) {
				$classes[] = 'woo-sale-badge-circle';
			}

			// Woo outofstock badge shape flag.
			$outofstock_badge = Avada()->settings->get( 'woo_outofstock_badge_shape' );
			if ( '' !== $outofstock_badge ) {
				$classes[] = 'woo-outofstock-badge-' . $outofstock_badge;
			}

			if ( ! is_null( fusion_library()->woocommerce ) && fusion_library()->woocommerce->is_checkout_layout() ) {
				$classes[] = 'woocommerce';
			}
		}

		if ( function_exists( 'ubermenu_get_menu_instance_by_theme_location' ) && ubermenu_get_menu_instance_by_theme_location( 'main_navigation' ) ) {
			$classes[] = 'fusion-ubermenu-support';
		}

		$classes[] = 'mobile-menu-design-' . Avada()->settings->get( 'mobile_menu_design' );

		if ( ! wp_is_mobile() || ( wp_is_mobile() && Avada()->settings->get( 'disable_mobile_image_hovers' ) ) ) {
			$classes[] = 'fusion-image-hovers';
		}

		if ( Avada()->settings->get( 'pagination_text_display' ) ) {
			$classes[] = 'fusion-show-pagination-text';
		} else {
			$classes[] = 'fusion-hide-pagination-text';
		}

		$classes[] = 'fusion-header-layout-' . Avada()->settings->get( 'header_layout' );

		$classes[] = fusion_get_option( 'responsive' ) ? 'avada-responsive' : 'avada-not-responsive';

		$footer_fx_class  = 'avada-footer-fx-';
		$footer_fx_class .= str_replace( [ 'footer_area_', 'footer_', '_' ], [ '', '', '-' ], Avada()->settings->get( 'footer_special_effects' ) );

		$classes[] = $footer_fx_class;
		$classes[] = 'avada-menu-highlight-style-' . Avada()->settings->get( 'menu_highlight_style' );
		$classes[] = 'fusion-search-form-' . esc_attr( Avada()->settings->get( 'search_form_design' ) );

		if ( 'top' === fusion_get_option( 'header_position' ) ) {
			$classes[] = 'fusion-main-menu-search-' . esc_attr( Avada()->settings->get( 'main_nav_search_layout' ) );
		} else {
			$classes[] = 'fusion-main-menu-search-dropdown';
		}

		$classes[] = 'fusion-avatar-' . esc_attr( Avada()->settings->get( 'avatar_shape' ) );

		if ( 'top' === fusion_get_option( 'header_position' ) && fusion_get_option( 'header_sticky_shrinkage' ) ) {
			$classes[] = 'avada-sticky-shrinkage';
		}

		if ( Avada()->settings->get( 'avada_styles_dropdowns' ) ) {
			$classes[] = 'avada-dropdown-styles';
		}

		$classes[] = 'avada-blog-layout-' . Avada()->settings->get( 'blog_layout' );
		$classes[] = 'avada-blog-archive-layout-' . Avada()->settings->get( 'blog_archive_layout' );

		if ( class_exists( 'Tribe__Events__Main' ) ) {
			if ( '100-width.php' !== tribe_get_option( 'tribeEventsTemplate', 'default' ) && ( ! is_singular( 'tribe_events' ) || ( is_singular( 'tribe_events' ) && 'sidebar' === Avada()->settings->get( 'ec_meta_layout' ) ) ) ) {
				$classes[] = 'avada-ec-not-100-width';
			}
			$classes[] = 'avada-ec-meta-layout-' . Avada()->settings->get( 'ec_meta_layout' );
		}

		if ( Avada()->settings->get( 'image_rollover' ) ) {
			$classes[] = 'avada-image-rollover-yes';
			$classes[] = 'avada-image-rollover-direction-' . Avada()->settings->get( 'image_rollover_direction' );
		} else {
			$classes[] = 'avada-image-rollover-no';
		}
		if ( Avada()->settings->get( 'icon_circle_image_rollover' ) ) {
			$classes[] = 'avada-image-rollover-circle-yes';
		} else {
			$classes[] = 'avada-image-rollover-circle-no';
		}
		if ( Avada()->settings->get( 'header_shadow' ) ) {
			$classes[] = 'avada-header-shadow-yes';
		} else {
			$classes[] = 'avada-header-shadow-no';
		}

		if ( fusion_get_option( 'logo_background' ) ) {
			$classes[] = 'avada-has-logo-background';
		}

		$classes[] = 'avada-menu-icon-position-' . Avada()->settings->get( 'menu_icon_position' );

		if ( Avada()->settings->get( 'megamenu_shadow' ) ) {
			$classes[] = 'avada-has-megamenu-shadow';
		}

		if ( Avada()->settings->get( 'mainmenu_dropdown_display_divider' ) ) {
			$classes[] = 'avada-has-mainmenu-dropdown-divider';
		}

		if ( Avada()->settings->get( 'main_nav_icon_circle' ) ) {
			$classes[] = 'fusion-has-main-nav-icon-circle';
		}

		if ( fusion_get_option( 'header_100_width' ) ) {
			$classes[] = 'avada-has-header-100-width';
		}

		if ( fusion_get_option( 'page_title_100_width' ) ) {
			$classes[] = 'avada-has-pagetitle-100-width';
		}

		if ( fusion_get_option( 'page_title_bg_full' ) ) {
			$classes[] = 'avada-has-pagetitle-bg-full';
		}

		if ( fusion_get_option( 'bg_pattern_option' ) && ! ( fusion_get_option( 'bg_color' ) || fusion_get_option( 'bg_image[url]' ) ) ) {
			$classes[] = 'avada-has-page-background-pattern';
		}

		if ( fusion_get_option( 'page_title_bg_parallax' ) ) {
			$classes[] = 'avada-has-pagetitle-bg-parallax';
		}

		if ( fusion_get_option( 'mobile_menu_search' ) ) {
			$classes[] = 'avada-has-mobile-menu-search';
		}

		if ( fusion_get_option( 'main_nav_search_icon' ) ) {
			$classes[] = 'avada-has-main-nav-search-icon';
		}

		if ( fusion_get_option( 'megamenu_item_display_divider' ) ) {
			$classes[] = 'avada-has-megamenu-item-divider';
		}

		if ( fusion_get_option( 'footer_100_width' ) ) {
			$classes[] = 'avada-has-100-footer';
		}

		if ( ! fusion_get_option( 'breadcrumb_mobile' ) ) {
			$classes[] = 'avada-has-breadcrumb-mobile-hidden';
		}

		if ( 'auto' === fusion_get_option( 'page_title_mobile_height' ) ) {
			$classes[] = 'avada-has-page-title-mobile-height-auto';
		}

		if ( fusion_get_option( 'page_title_bg_retina[url]' ) ) {
			$classes[] = 'avada-has-pagetitlebar-retina-bg-image';
		}

		$classes[] = 'avada-has-titlebar-' . fusion_get_option( 'page_title_bar' );

		if ( fusion_get_option( 'footerw_bg_image[url]' ) ) {
			$classes[] = 'avada-has-footer-widget-bg-image';
		}

		if ( 0 === Fusion_Color::new_color( fusion_get_option( 'header_border_color' ) )->alpha ) {
			$classes[] = 'avada-header-border-color-full-transparent';
		}

		if ( 0 === Fusion_Color::new_color( fusion_get_option( 'grid_separator_color' ) )->alpha ) {
			$classes[] = 'avada-has-transparent-grid-sep-color';
		}

		if ( 0 === Fusion_Color::new_color( fusion_get_option( 'social_bg_color' ) )->alpha ) {
			$classes[] = 'avada-social-full-transparent';
		}

		if ( fusion_get_option( 'slidingbar_widgets' ) ) {
			$classes[] = 'avada-has-slidingbar-widgets';
			$classes[] = 'avada-has-slidingbar-position-' . fusion_get_option( 'slidingbar_position' );
			$classes[] = 'avada-slidingbar-toggle-style-' . fusion_get_option( 'slidingbar_toggle_style' );
			if ( fusion_get_option( 'slidingbar_sticky' ) ) {
				$classes[] = 'avada-has-slidingbar-sticky';
			}
			if ( fusion_get_option( 'slidingbar_border' ) ) {
				$classes[] = 'avada-has-slidingbar-border';
			}
			if ( false !== strpos( '%', fusion_get_option( 'slidingbar_width' ) ) ) {
				$classes[] = 'avada-has-slidingbar-width-percent';
			}
		}

		if ( '' !== fusion_get_option( 'bg_image[url]' ) && fusion_get_option( 'bg_full' ) ) {
			$classes[] = 'avada-has-bg-image-full';
		}

		if ( '' !== fusion_get_option( 'header_bg_image[url]' ) ) {
			$classes[] = 'avada-has-header-bg-image';
			$classes[] = 'avada-header-bg-' . fusion_get_option( 'header_bg_repeat' );

			if ( fusion_get_option( 'header_bg_full' ) ) {
				$classes[] = 'avada-has-header-bg-full';
			}

			if ( fusion_get_option( 'header_bg_parallax' ) ) {
				$classes[] = 'avada-has-header-bg-parallax';
			}
		}

		if ( Fusion_Color::new_color( Avada()->settings->get( 'header_top_bg_color' ) )->alpha < 1 ) {
			$classes[] = 'avada-header-top-bg-not-opaque';
		}

		if ( 0 === Fusion_Color::new_color( Avada()->settings->get( 'timeline_color' ) )->alpha ) {
			$classes[] = 'avada-has-transparent-timeline_color';
		}

		if ( Fusion_Color::new_color( Avada()->settings->get( 'content_bg_color' ) )->alpha < 1 ) {
			$classes[] = 'avada-content-bg-not-opaque';
		}

		$classes[] = 'avada-has-pagination-' . fusion_get_option( 'pagination_sizing' );

		if ( fusion_get_page_option( 'fallback', $c_page_id ) ) {
			$classes[] = 'avada-has-slider-fallback-image';
		}

		$classes[] = 'avada-flyout-menu-direction-' . fusion_get_option( 'flyout_menu_direction' );

		if ( function_exists( 'has_blocks' ) && has_blocks() ) {
			$classes[] = 'avada-has-blocks';
		}

		if ( Fusion_Helper::tribe_is_v2_views_enabled() ) {
			$classes[] = 'avada-ec-views-v2';
		} else {
			$classes[] = 'avada-ec-views-v1';
		}

		return $classes;
	}

	/**
	 * The comment template.
	 *
	 * @access public
	 * @param Object     $comment The comment.
	 * @param array      $args    The comment arguments.
	 * @param int|string $depth   The comment depth.
	 */
	public function comment_template( $comment, $args, $depth ) {
		?>
		<?php $add_below = ''; ?>
		<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
			<div class="the-comment">
				<div class="avatar"><?php echo get_avatar( $comment, 54 ); ?></div>
				<div class="comment-box">
					<div class="comment-author meta">
						<strong><?php echo get_comment_author_link(); ?></strong>
						<?php
						printf(
							/* translators: %1$s: Comment date. %2$s: Comment time. */
							esc_attr__( '%1$s at %2$s', 'Avada' ),
							get_comment_date(), // phpcs:ignore WordPress.Security.EscapeOutput
							get_comment_time() // phpcs:ignore WordPress.Security.EscapeOutput
						);

						edit_comment_link( __( ' - Edit', 'Avada' ), '  ', '' );

						comment_reply_link(
							array_merge(
								$args,
								[
									'reply_text' => __( ' - Reply', 'Avada' ),
									'add_below'  => 'comment',
									'depth'      => $depth,
									'max_depth'  => $args['max_depth'],
								]
							)
						);
						?>
					</div>
					<div class="comment-text">
						<?php if ( '0' == $comment->comment_approved ) : // phpcs:ignore WordPress.PHP.StrictComparisons ?>
							<em><?php esc_attr_e( 'Your comment is awaiting moderation.', 'Avada' ); ?></em>
							<br />
						<?php endif; ?>
						<?php comment_text(); ?>
					</div>
				</div>
			</div>
		<?php
	}

	/**
	 * The password protected form template.
	 *
	 * @since 5.1
	 * @access public
	 * @param string $output The form HTML.
	 * @return string The changed output depth.
	 */
	public function the_password_form( $output ) {

		$output = str_replace( 'type="submit"', 'class="fusion-button button-default fusion-button-default-size" type="submit"', $output );

		return $output;
	}

	/**
	 * The title template.
	 *
	 * @access public
	 * @param string     $content       The content.
	 * @param int|string $size          The size.
	 * @param string     $content_align The content alignment.
	 */
	public function title_template( $content = '', $size = '2', $content_align = '' ) {
		$margin_top    = Avada()->settings->get( 'title_margin', 'top' );
		$margin_bottom = Avada()->settings->get( 'title_margin', 'bottom' );
		$sep_color     = Avada()->settings->get( 'title_border_color' );
		$style_type    = Avada()->settings->get( 'title_style_type' );
		$size_array    = [
			'1' => 'one',
			'2' => 'two',
			'3' => 'three',
			'4' => 'four',
			'5' => 'five',
			'6' => 'six',
		];

		if ( ! $content_align ) {
			$content_align = 'left';
			if ( is_rtl() ) {
				$content_align = 'right';
			}
		}

		$classes        = '';
		$styles         = '';
		$heading_styles = '';
		$sep_styles     = '';

		$classes_array = explode( ' ', $style_type );
		foreach ( $classes_array as $class ) {
			$classes .= ' sep-' . $class;
		}

		if ( $margin_top ) {
			$styles .= sprintf( 'margin-top:%s;', Fusion_Sanitize::get_value_with_unit( $margin_top ) );
		}
		if ( $margin_bottom ) {
			$styles .= sprintf( 'margin-bottom:%s;', Fusion_Sanitize::get_value_with_unit( $margin_bottom ) );
		}

		if ( '' !== $margin_top || '' !== $margin_bottom ) {
			$heading_styles .= 'margin:0;';
		}

		if ( false !== strpos( $style_type, 'underline' ) || false !== strpos( $style_type, 'none' ) ) {

			if ( false !== strpos( $style_type, 'underline' ) && $sep_color ) {
				$styles .= 'border-bottom-color:' . $sep_color;
			} elseif ( false !== strpos( $style_type, 'none' ) ) {
				$classes .= ' fusion-sep-none';
			}
			?>
			<div class="fusion-title fusion-title-size-<?php echo esc_attr( $size_array[ $size ] ); ?><?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $styles ); ?>">
				<h<?php echo (int) $size; ?> class="title-heading-<?php echo esc_attr( $content_align ); ?>" style="<?php echo esc_attr( $heading_styles ); ?>">
					<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</h<?php echo (int) $size; ?>>
			</div>
			<?php
		} else {
			if ( 'right' === $content_align ) {
				?>
				<div class="fusion-title fusion-title-size-<?php echo esc_attr( $size_array[ $size ] ); ?><?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $styles ); ?>">
					<div class="title-sep-container">
						<div class="title-sep<?php echo esc_attr( $classes ); ?>"></div>
					</div>
					<span class="awb-title-spacer"></span>
					<h<?php echo (int) $size; ?> class="title-heading-<?php echo esc_attr( $content_align ); ?>" style="<?php echo esc_attr( $heading_styles ); ?>">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</h<?php echo (int) $size; ?>>
				</div>
				<?php
			} elseif ( 'center' === $content_align ) {
				?>
				<div class="fusion-title fusion-title-center fusion-title-size-<?php echo esc_attr( $size_array[ $size ] ); ?><?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $styles ); ?>">
					<div class="title-sep-container title-sep-container-left">
						<div class="title-sep<?php echo esc_attr( $classes ); ?>"></div>
					</div>
					<span class="awb-title-spacer"></span>
					<h<?php echo (int) $size; ?> class="title-heading-<?php echo esc_attr( $content_align ); ?>" style="<?php echo esc_attr( $heading_styles ); ?>">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</h<?php echo (int) $size; ?>>
					<span class="awb-title-spacer"></span>
					<div class="title-sep-container title-sep-container-right">
						<div class="title-sep<?php echo esc_attr( $classes ); ?>"></div>
					</div>
				</div>
				<?php
			} else {
				?>
				<div class="fusion-title fusion-title-size-<?php echo esc_attr( $size_array[ $size ] ); ?><?php echo esc_attr( $classes ); ?>" style="<?php echo esc_attr( $styles ); ?>">
					<h<?php echo (int) $size; ?> class="title-heading-<?php echo esc_attr( $content_align ); ?>" style="<?php echo esc_attr( $heading_styles ); ?>">
						<?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</h<?php echo (int) $size; ?>>
					<span class="awb-title-spacer"></span>
					<div class="title-sep-container">
						<div class="title-sep<?php echo esc_attr( $classes ); ?>"></div>
					</div>
				</div>
				<?php
			}
		}
	}

	/**
	 * Render footer content.
	 *
	 * @access public
	 * @since 6.2
	 */
	public function render_footer() {
		$footer_parallax_class = ( 'footer_parallax_effect' === Avada()->settings->get( 'footer_special_effects' ) ) ? ' fusion-footer-parallax' : '';
		?>

		<div class="fusion-footer<?php echo esc_attr( $footer_parallax_class ); ?>">
				<?php get_template_part( 'templates/footer-content' ); ?>
		</div> <!-- fusion-footer -->

		<?php
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
