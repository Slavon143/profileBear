<?php
/**
 * Correlate Theme-Options, Page-Options and Taxonomy Options.
 *
 * @since 2.0
 * @package avada
 */

/**
 * The Fusion_Options_Map object.
 */
class Fusion_Options_Map {

	/**
	 * An array of bool options.
	 *
	 * @static
	 * @access protected
	 * @since 2.2.0
	 * @var array
	 */
	protected static $bool_options = [
		'faq_related_posts',
		'bg_full',
		'page_title_bg_full',
		'header_bg_full',
		'header_bg_parallax',
		'avada_rev_styles',
		'page_title_bg_full',
		'page_title_bg_parallax',
		'header_100_width',
		'page_title_100_width',
		'footer_100_width',
		'footer_widgets',
		'footer_copyright',
		'content_bg_full',
		'portfolio_width_100',
		'blog_width_100',
		'product_width_100',
		'page_title_bar_text',
		'portfolio_project_desc_title',
		'portfolio_project_details',
		'show_first_featured_image',
		'portfolio_link_icon_target',
		'portfolio_related_posts',
		'related_posts',
		'portfolio_social_sharing_box',
		'events_social_sharing_box',
		'social_sharing_box',
		'blog_pn_nav',
		'portfolio_pn_nav',
		'woocommerce_product_images_layout',
		'breadcrumb_mobile',
		'responsive',
		'smooth_scrolling',
		'bg_pattern_option',
		'header_sticky',
		'header_sticky_tablet',
		'header_sticky_mobile',
		'header_sticky_shrinkage',
		'main_nav_search_icon',
		'mobile_menu_submenu_indicator',
		'media_queries_async',
		'css_vars',
		'header_sticky_shadow',
		'bg_pattern_option',
		'page_title_fading',
		'live_search',
		'search_limit_to_post_titles',
		'live_search_display_featured_image',
		'live_search_display_post_type',
		'lightbox_post_images',
		'lightbox_social',
		'lightbox_desc',
		'lightbox_title',
		'lightbox_autoplay',
		'lightbox_gallery',
		'lightbox_arrows',
		'mobile_nav_submenu_slideout',
		'defer_jquery',
		'defer_styles',
		'image_rollover',
		'cats_image_rollover',
		'title_image_rollover',
		'enable_language_updates',
		'custom_scrollbar',
	];

	/**
	 * The options map.
	 *
	 * @static
	 * @access private
	 * @since 2.0
	 * @var array
	 */
	private static $map = [
		'header_bg_color'        => [
			'archive' => 'archive_header_bg_color',
		],
		'mobile_header_bg_color' => [
			'archive' => 'mobile_archive_header_bg_color',
			'term'    => 'mobile_header_bg_color',
		],
		'page_title_bar'         => [
			'is_home'          => 'blog_show_page_title_bar',
			'is_tag'           => 'blog_page_title_bar',
			'is_category'      => 'blog_page_title_bar',
			'is_author'        => 'blog_page_title_bar',
			'is_date'          => 'blog_page_title_bar',
			'is_singular_post' => 'blog_page_title_bar',
		],
	];

	/**
	 * Cached options-map.
	 *
	 * @static
	 * @access protected
	 * @since 2.2.0
	 * @var array
	 */
	protected static $cached_option_map = [];

	/**
	 * Have the bool options already been mapped?
	 *
	 * This is a performance improvement to avoid running the same loop constantly in the get_option_map function.\
	 *
	 * @static
	 * @access private
	 * @since 2.2.0
	 * @var bool
	 */
	public static $bool_options_parsed = false;

	/**
	 * Get the option-map.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public static function get_option_map() {
		if ( ! self::$bool_options_parsed ) {
			foreach ( self::$bool_options as $bool_option ) {
				if ( ! isset( self::$map[ $bool_option ] ) ) {
					self::$map[ $bool_option ] = [];
				}
				self::$map[ $bool_option ]['is_bool'] = true;
			}
			self::$bool_options_parsed = true;
		}
		return apply_filters( 'fusion_get_option_names', self::$map );
	}

	/**
	 * Get the name of an option.
	 *
	 * @static
	 * @since 2.0
	 * @param string $option  The option-name in the map.
	 * @param string $context Can be 'theme', 'post', 'term' or 'archive'.
	 * @return string|array
	 */
	public static function get_option_name( $option, $context = 'theme' ) {

		// Change context if we're on an archive.
		$option_names = self::get_option_map();
		$id           = Fusion::get_instance()->get_page_id();

		if ( 'theme' === $context && false !== strpos( $id, 'archive' ) || false === $id ) {
			$context = 'archive';
			foreach ( $option_names as $name => $options ) {
				if ( isset( $options['archive'] ) && $options['archive'] === $option ) {
					$option = $name;
				}
			}
		}

		// Set the context in caches if it doesn't already exist.
		if ( ! isset( self::$cached_option_map[ $context ] ) ) {
			self::$cached_option_map[ $context ] = [];
		}

		// Set the ID in caches if it doesn't already exist.
		if ( ! isset( self::$cached_option_map[ $context ][ $id ] ) ) {
			self::$cached_option_map[ $context ][ $id ] = [];
		}

		// Return the cached option-name if it already exists.
		if ( is_string( $option ) && isset( self::$cached_option_map[ $context ][ $id ][ $option ] ) ) {
			return self::$cached_option_map[ $context ][ $id ][ $option ];
		}

		if ( isset( $option_names[ $option ] ) ) {
			if ( 'theme' === $context || 'archive' === $context ) {
				foreach ( [ 'is_home', 'is_tag', 'is_category', 'is_author', 'is_date', 'is_singular_post' ] as $condition ) {

					if ( isset( $option_names[ $option ][ $condition ] ) ) {
						$evaluate = (bool) ( function_exists( $condition ) && $condition() );
						$evaluate = ( 'is_singular_post' === $condition && is_singular( 'post' ) ) ? true : $evaluate;

						if ( $evaluate ) {
							// Cache and return.
							self::$cached_option_map[ $context ][ $id ][ $option ] = $option_names[ $option ][ $condition ];
							return $option_names[ $option ][ $condition ];
						}
					}
				}
			}

			if ( isset( $option_names[ $option ][ $context ] ) ) {
				// Cache and return.
				self::$cached_option_map[ $context ][ $id ][ $option ] = $option_names[ $option ][ $context ];
				return $option_names[ $option ][ $context ];
			}
		}

		// Cache and return.
		self::$cached_option_map[ $context ][ $id ][ $option ] = $option;
		return $option;
	}

	/**
	 * Get the option-name using TO-name as a reference.
	 *
	 * @static
	 * @since 2.0
	 * @param string $option The option-name in theme-options.
	 * @return string|array
	 */
	public static function get_option_name_from_theme_option( $option ) {

		// Get the full map.
		$option_names = self::get_option_map();
		$option_array = [];

		// Loop the map to find our option.
		foreach ( $option_names as $id => $definition ) {

			// If the option is the key, return it.
			if ( $option === $id ) {
				return $option;
			}

			// If we found the option as a TO, return the ID.
			if ( isset( $definition['theme'] ) && $option === $definition['theme'] ) {
				return $id;
			}

			// If TO is an array, we'll need some extra calculations.
			if ( isset( $definition['theme'] ) && is_array( $definition['theme'] ) && isset( $definition['theme'][0] ) && $option === $definition['theme'][0] ) {
				$option_array[ $definition['theme'][1] ] = $id;
			}
		}
		return empty( $option_array ) ? $option : $option_array;
	}

	/**
	 * Get a map reference from an option-name and the context of that option.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $option  The option-name in the map.
	 * @param string $context Can be 'theme', 'post', 'term' or 'archive'.
	 * @return string
	 */
	public static function get_map_key_from_context( $option, $context = 'theme' ) {
		$map = self::get_option_map();
		foreach ( $map as $key => $args ) {
			if ( isset( $args[ $context ] ) && $option === $args[ $context ] ) {
				return $key;
			}
		}
		return $option;
	}

	/**
	 * Get an array of bool options.
	 *
	 * @static
	 * @access public
	 * @since 2.2.0
	 * @return array
	 */
	public static function get_bool_options() {
		return self::$bool_options;
	}
}
