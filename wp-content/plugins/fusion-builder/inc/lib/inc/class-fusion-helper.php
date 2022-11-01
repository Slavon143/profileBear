<?php
/**
 * Helper methods.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * Includes static helper methods.
 */
final class Fusion_Helper {

	/**
	 * Converts a PHP version to 3-part.
	 *
	 * @static
	 * @access public
	 * @param  string $ver The verion number.
	 * @return string
	 */
	public static function normalize_version( $ver ) {
		if ( ! is_string( $ver ) ) {
			return $ver;
		}
		$ver_parts = explode( '.', $ver );
		$count     = count( $ver_parts );
		// Keep only the 1st 3 parts if longer.
		if ( 3 < $count ) {
			return absint( $ver_parts[0] ) . '.' . absint( $ver_parts[1] ) . '.' . absint( $ver_parts[2] );
		}
		// If a single digit, then append '.0.0'.
		if ( 1 === $count ) {
			return absint( $ver_parts[0] ) . '.0.0';
		}
		// If 2 digits, append '.0'.
		if ( 2 === $count ) {
			return absint( $ver_parts[0] ) . '.' . absint( $ver_parts[1] ) . '.0';
		}
		return $ver;
	}

	/**
	 * Instantiates the WordPress filesystem.
	 *
	 * @static
	 * @access public
	 * @return object WP_Filesystem
	 */
	public static function init_filesystem() {

		$credentials = [];

		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' );
		}

		$method = defined( 'FS_METHOD' ) ? FS_METHOD : false;

		if ( 'ftpext' === $method ) {
			// If defined, set it to that, Else, set to NULL.
			$credentials['hostname'] = defined( 'FTP_HOST' ) ? preg_replace( '|\w+://|', '', FTP_HOST ) : null;
			$credentials['username'] = defined( 'FTP_USER' ) ? FTP_USER : null;
			$credentials['password'] = defined( 'FTP_PASS' ) ? FTP_PASS : null;

			// Set FTP port.
			if ( strpos( $credentials['hostname'], ':' ) && null !== $credentials['hostname'] ) {
				list( $credentials['hostname'], $credentials['port'] ) = explode( ':', $credentials['hostname'], 2 );
				if ( ! is_numeric( $credentials['port'] ) ) {
					unset( $credentials['port'] );
				}
			} else {
				unset( $credentials['port'] );
			}

			// Set connection type.
			if ( ( defined( 'FTP_SSL' ) && FTP_SSL ) && 'ftpext' === $method ) {
				$credentials['connection_type'] = 'ftps';
			} elseif ( ! array_filter( $credentials ) ) {
				$credentials['connection_type'] = null;
			} else {
				$credentials['connection_type'] = 'ftp';
			}
		}

		// The WordPress filesystem.
		global $wp_filesystem;

		if ( empty( $wp_filesystem ) ) {
			require_once wp_normalize_path( ABSPATH . '/wp-admin/includes/file.php' );
			WP_Filesystem( $credentials );
		}

		return $wp_filesystem;
	}

	/**
	 * Auto calculate accent color, based on provided background color.
	 *
	 * @since 1.5.2
	 * @param  string $color color base value.
	 * @return string
	 */
	public static function fusion_auto_calculate_accent_color( $color ) {
		$color_obj = Fusion_Color::new_color( $color );

		// Not black.
		if ( 0 < $color_obj->lightness ) {
			if ( 25 > $color_obj->lightness ) {

				// Colors with very little lightness.
				return $color_obj->getNew( 'lightness', $color_obj->lightness * 4 )->toCSS( 'rgba' );
			} elseif ( 50 > $color_obj->lightness ) {
				return $color_obj->getNew( 'lightness', $color_obj->lightness * 2 )->toCSS( 'rgba' );
			} elseif ( 50 <= $color_obj->lightness ) {
				return $color_obj->getNew( 'lightness', $color_obj->lightness / 2 )->toCSS( 'rgba' );
			}
		} else {
			// // Black.
			return $color_obj->getNew( 'lightness', 70 )->toCSS( 'rgba' );
		}
	}

	/**
	 * Check if we're on a WooCommerce page.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @return bool
	 */
	public static function is_woocommerce() {

		if ( function_exists( 'is_woocommerce' ) ) {
			return (bool) is_woocommerce();
		}
		return false;

	}

	/**
	 * Check if we're on a bbPress page.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @return bool
	 */
	public static function is_bbpress() {

		if ( function_exists( 'is_bbpress' ) ) {
			return (bool) is_bbpress();
		}
		return false;

	}

	/**
	 * Check if we're on a bbPress forum archive.
	 *
	 * @static
	 * @access public
	 * @since 1.1.0
	 * @return bool
	 */
	public static function bbp_is_forum_archive() {
		if ( function_exists( 'bbp_is_forum_archive' ) ) {
			return (bool) bbp_is_forum_archive();
		}
		return false;
	}

	/**
	 * Check if we're on a bbPress topic archive.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @return bool
	 */
	public static function bbp_is_topic_archive() {

		if ( function_exists( 'bbp_is_topic_archive' ) ) {
			return (bool) bbp_is_topic_archive();
		}
		return false;

	}

	/**
	 * Check if we're on a bbPress search-results page.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @return bool
	 */
	public static function bbp_is_search() {

		if ( function_exists( 'bbp_is_search' ) ) {
			return (bool) bbp_is_search();
		}
		return false;

	}

	/**
	 * Check if we're on a bbPress tag archive page.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @return bool
	 */
	public static function bbp_is_topic_tag() {

		if ( function_exists( 'bbp_is_topic_tag' ) ) {
			return (bool) bbp_is_topic_tag();
		}
		return false;

	}

	/**
	 * Check if we're on a buddyPress page.
	 *
	 * @static
	 * @access public
	 * @since 3.0
	 * @return bool
	 */
	public static function is_buddypress() {

		if ( function_exists( 'is_buddypress' ) ) {
			return (bool) is_buddypress();
		}
		return false;

	}

	/**
	 * Check if we're on an Event post.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param int|null $post_id The post ID.
	 * @return bool
	 */
	public static function tribe_is_event( $post_id = null ) {
		if ( function_exists( 'tribe_is_event' ) ) {
			return tribe_is_event( $post_id );
		}
		return false;
	}

	/**
	 * Check if we're in an events archive.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @param int|null $post_id The post ID.
	 * @return bool
	 */
	public static function is_events_archive( $post_id = null ) {
		if ( is_post_type_archive( 'tribe_events' ) || ( self::tribe_is_event( $post_id ) && is_archive() ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the V2 view templates are used.
	 *
	 * @static
	 * @access public
	 * @since 2.2
	 * @return bool
	 */
	public static function tribe_is_v2_views_enabled() {
		if ( function_exists( 'tribe_events_views_v2_is_enabled' ) ) {
			return tribe_events_views_v2_is_enabled();
		}
		return false;
	}

	/**
	 * Get the contents of the title bar.
	 *
	 * @static
	 * @access public
	 * @param  int  $post_id               The post ID.
	 * @param  bool $get_secondary_content Determine if we want secondary content.
	 * @return array
	 */
	public static function fusion_get_page_title_bar_contents( $post_id, $get_secondary_content = true ) {

		if ( $get_secondary_content ) {
			ob_start();

			if ( 'breadcrumbs' === fusion_get_option( 'page_title_bar_bs' ) ) {
				fusion_breadcrumbs();
			} elseif ( 'search_box' === fusion_get_option( 'page_title_bar_bs' ) ) {
				get_search_form();
			}
			$secondary_content = ob_get_contents();
			ob_get_clean();
		} else {
			$secondary_content = '';
		}

		$title                       = '';
		$subtitle                    = '';
		$page_title_custom_text      = fusion_get_page_option( 'page_title_custom_text', $post_id );
		$page_title_custom_subheader = fusion_get_page_option( 'page_title_custom_subheader', $post_id );
		$page_title_text             = fusion_get_option( 'page_title_bar_text' );

		if ( ! empty( $page_title_custom_text ) ) {
			$title = $page_title_custom_text;
		}

		if ( ! empty( $page_title_custom_subheader ) ) {
			$subtitle = $page_title_custom_subheader;
		}

		if ( is_search() ) {
			/* translators: The search query. */
			$title    = sprintf( esc_html__( 'Search results for: %s', 'fusion-builder' ), get_search_query() );
			$subtitle = '';
		}

		if ( ! $title ) {
			$title = class_exists( 'Fusion_Dynamic_Data_Callbacks' ) ? Fusion_Dynamic_Data_Callbacks::fusion_get_object_title( $post_id ) : get_the_title( $post_id );

			// Only assign blog title Global Option to default blog page and not posts page.
			if ( is_home() && 'page' !== get_option( 'show_on_front' ) ) {
				$title = Avada()->settings->get( 'blog_title' );
			}

			if ( is_404() ) {
				$title = esc_html__( 'Error 404 Page', 'fusion-builder' );
			}

			if ( class_exists( 'Tribe__Events__Main' ) && ( tribe_is_events_front_page() || ( self::tribe_is_event( $post_id ) && ! is_single() && ! is_home() && ! is_tag() ) || self::is_events_archive( $post_id ) && ! is_tag() || ( self::is_events_archive( $post_id ) && is_404() ) ) ) {
				$title = tribe_get_events_title();
			} elseif ( is_archive() && ! self::is_bbpress() && ! is_search() ) {
				if ( is_day() ) {
					/* translators: Date. */
					$title = sprintf( esc_html__( 'Daily Archives: %s', 'fusion-builder' ), '<span>' . get_the_date() . '</span>' );
				} elseif ( is_month() ) {
					/* translators: Date. */
					$title = sprintf( esc_html__( 'Monthly Archives: %s', 'fusion-builder' ), '<span>' . get_the_date( 'F Y' ) . '</span>' );
				} elseif ( is_year() ) {
					/* translators: Date. */
					$title = sprintf( esc_html__( 'Yearly Archives: %s', 'fusion-builder' ), '<span> ' . get_the_date( 'Y' ) . '</span>' );
				} elseif ( is_author() ) {
					$curauth = get_user_by( 'id', get_query_var( 'author' ) );
					$title   = $curauth->nickname;
				} elseif ( is_post_type_archive() ) {
					$title = post_type_archive_title( '', false );

					$sermon_settings = get_option( 'wpfc_options' );
					if ( is_post_type_archive( 'wpfc_sermon' ) && is_array( $sermon_settings ) ) {
						$title = $sermon_settings['archive_title'];
					}
				} else {
					$title = single_cat_title( '', false );
				}
			} elseif ( class_exists( 'bbPress' ) && self::is_bbpress() && self::bbp_is_forum_archive() ) {
				$title = post_type_archive_title( '', false );
			}

			if ( class_exists( 'WooCommerce' ) && self::is_woocommerce() && ( is_product() || is_shop() ) && ! is_search() ) {
				if ( ! is_product() ) {
					$title = woocommerce_page_title( false );
				}
			}
		}

		// Only assign blog subtitle Global Option to default blog page and not posts page.
		if ( ! $subtitle && is_home() && 'page' !== get_option( 'show_on_front' ) ) {
			$subtitle = fusion_get_option( 'blog_subtitle' );
		}

		if ( 'hide' !== fusion_get_option( 'page_title_bar' ) && ! $page_title_text ) {
			$title    = '';
			$subtitle = '';
		}

		return apply_filters( 'avada_page_title_bar_contents', [ $title, $subtitle, $secondary_content ] );
	}
}
