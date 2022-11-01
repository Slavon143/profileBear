<?php
/**
 * Dynamic-CSS helpers.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * The Helpers object.
 */
class Fusion_Dynamic_CSS_Helpers {

	/**
	 * An array for dynamic css.
	 *
	 * @access public
	 * @var array
	 */
	public static $dynamic_css = [];

	/**
	 * The dynamic-css after it's been parsed.
	 *
	 * @static
	 * @access private
	 * @since 1.6
	 * @var string
	 */
	private static $dynamic_css_parsed = '';

	/**
	 * Add to Dynamic CSS.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $css The existing CSS.
	 */
	public function add_css( $css ) {

		if ( ! empty( $css ) ) {
			self::$dynamic_css = array_merge_recursive( self::$dynamic_css, $css );
		}
	}

	/**
	 * Helper function.
	 * Merge and combine the CSS elements.
	 *
	 * @access public
	 * @param string|array $elements An array of our elements. Strings are directly returned.
	 * @param string       $prefix   A prefix to add to all selectors.
	 * @param string       $suffix   A suffix to add to all selectors.
	 * @return string                The imploded array of CSS selectors.
	 */
	public function implode( $elements = [], $prefix = '', $suffix = '' ) {

		$builder_status = false;
		if ( function_exists( 'fusion_is_preview_frame' ) ) {
			$builder_status = fusion_is_preview_frame();
		}

		if ( $prefix ) {
			$prefix .= ' ';
		}

		if ( $suffix ) {
			$suffix = ' ' . $suffix;
		}

		if ( is_string( $elements ) ) {
			if ( ! $builder_status || false === strpos( $prefix . $elements . $suffix, ':hover' ) ) {
				return $prefix . $elements . $suffix;
			}

			// If we are on builder and element is hover selector.
			$fake_hover = $prefix . str_replace( ':hover', '.hover', $elements ) . $suffix . ',';
			return $fake_hover . $elements;
		}

		// Make sure our values are unique.
		$elements = array_unique( $elements );

		// Sort elements alphabetically.
		// This way all duplicate items will be merged in the final CSS array.
		sort( $elements );

		// Check for hover selectors and add class equivalent.
		if ( $builder_status ) {
			foreach ( $elements as $key => $element ) {
				if ( false !== strpos( $prefix . $element . $suffix, ':hover' ) ) {
					$fake_hover = str_replace( ':hover', '.hover', $prefix . $element . $suffix );
					$elements[] = $fake_hover;
				}
				$elements[ $key ] = $prefix . $element . $suffix;
			}
		}

		// Implode items and return the value.
		return implode( ',', $elements );
	}

	/**
	 * Maps elements from dynamic css to the selector.
	 *
	 * @access public
	 * @param  array  $elements The elements.
	 * @param  string $selector_after The selector after the element.
	 * @param  string $selector_before The selector before the element.
	 * @return  array
	 */
	public function map_selector( $elements, $selector_after = '', $selector_before = '' ) {
		$array = [];
		foreach ( $elements as $element ) {
			$array[] = $selector_before . $element . $selector_after;
		}
		return $array;
	}

	/**
	 * Get the array of dynamically-generated CSS and convert it to a string.
	 * Parses the array and adds quotation marks to font families and prefixes for browser-support.
	 *
	 * @access public
	 * @param array $css         The CSS array.
	 * @param bool  $skip_filter Set to true to skip the "fusion_dynamic_css" filter.
	 * @return string
	 */
	public function parser( $css, $skip_filter = false ) {
		// Prefixes.
		foreach ( $css as $media_query => $elements ) {
			if ( 0 === strpos( $media_query, 'fusion-' ) ) {
				$calculated_media_query = Fusion_Media_Query_Scripts::get_media_query_from_key( $media_query );
				if ( $calculated_media_query ) {
					$media_query = $calculated_media_query;
				}
			}
			foreach ( $elements as $element => $style_array ) {
				foreach ( $style_array as $property => $value ) {

					// Skip invalid properties.
					if ( 'google' === $property || 'subsets' === $property || 'font-backup' === $property ) {
						continue;
					}

					// Letter-spacing.
					if ( 'letter-spacing' === $property && is_numeric( $value ) ) {
						$value = (string) $value;
						$value = trim( $value ) . 'px';
					}

					// Font-weight.
					if ( 'font-weight' === $property && 'regular' === $value ) {
						$value = '400';
					}

					// Font family.
					if ( 'font-family' === $property ) {
						if ( false === strpos( $value, ',' ) && false === strpos( $value, "'" ) && false === strpos( $value, '"' ) ) {
							$value = "'" . $value . "'";
						}
						$css[ $media_query ][ $element ]['font-family'] = $value;
					}
				}
			}
		}

		/**
		 * Process the array of CSS properties and produce the final CSS.
		 */
		$final_css = '';
		foreach ( $css as $media_query => $styles ) {

			$final_css .= ( 'global' !== $media_query ) ? $media_query . '{' : '';

			foreach ( $styles as $style => $style_array ) {
				$final_css .= $style . '{';
				foreach ( $style_array as $property => $value ) {
					if ( is_array( $value ) ) {
						foreach ( $value as $sub_value ) {
							$final_css .= $property . ':' . $sub_value . ';';
						}
					} else {
						$final_css .= $property . ':' . $value . ';';
					}
				}
				$final_css .= '}';
			}

			$final_css .= ( 'global' !== $media_query ) ? '}' : '';

		}

		return $skip_filter ? $final_css : apply_filters( 'fusion_dynamic_css', $final_css );

	}

	/**
	 * Returns the dynamic CSS.
	 * If possible, it also caches the CSS using WordPress transients.
	 *
	 * @access public
	 * @return  string  the dynamically-generated CSS.
	 */
	public function dynamic_css_cached() {

		// Get the page ID.
		$dynamic_css_obj = Fusion_Dynamic_CSS::get_instance();
		$mode            = $dynamic_css_obj->get_mode();

		$cache = false;

		// Only cache if css_cache_method is set to 'db'.
		if ( 'inline' === $mode ) {
			$cache = true;
		}

		// If WP_DEBUG set to true, caching is off in TO or Avada is not active if being used (e.g. WP Touch), then do not cache.
		if ( 'off' === fusion_library()->get_option( 'css_cache_method' ) || $dynamic_css_obj->is_cache_disabled() ) {
			$cache = false;
		}

		if ( $cache ) {
			// If we're compiling to file, and this is a fallback, 1hr caching, 1 day for db mode.
			$cache_time = ( 'db' === fusion_library()->get_option( 'css_cache_method' ) ) ? DAY_IN_SECONDS : HOUR_IN_SECONDS;
			$c_page_id  = fusion_library()->get_page_id();
			$page_id    = ( $c_page_id ) ? $c_page_id : 'global';

			$transient_name = 'fusion_dynamic_css_' . $this->get_dynamic_css_id();

			// Check if the dynamic CSS needs updating.
			// If it does, then calculate the CSS and then update the transient.
			if ( $dynamic_css_obj->needs_update() ) {

				// Calculate the dynamic CSS.
				$dynamic_css = $dynamic_css_obj->generate_final_css();

				// Set the transient for an hour.
				set_transient( $transient_name, $dynamic_css, $cache_time );

				$option             = get_option( 'fusion_dynamic_css_posts', [] );
				$option[ $page_id ] = true;
				update_option( 'fusion_dynamic_css_posts', $option );
			} else {

				// Check if the transient exists.
				// If it does not exist, then generate the CSS and update the transient.
				$dynamic_css = get_transient( $transient_name );
				if ( false === $dynamic_css ) {

					// Calculate the dynamic CSS.
					$dynamic_css = $dynamic_css_obj->generate_final_css();

					// Set the transient for an hour.
					set_transient( $transient_name, $dynamic_css, $cache_time );
				}
			}
		} else {
			// Calculate the dynamic CSS.
			$dynamic_css = $dynamic_css_obj->generate_final_css();
		}

		return $dynamic_css;
	}

	/**
	 * Combines google-fonts & fallback fonts.
	 *
	 * @access public
	 * @since 5.0.0
	 * @param array $typo_array The typography setting as saved in the db.
	 * @return string
	 */
	public function combined_font_family( $typo_array = [] ) {

		$google_font    = isset( $typo_array['font-family'] ) ? $typo_array['font-family'] : false;
		$fallback_fonts = isset( $typo_array['font-backup'] ) ? $typo_array['font-backup'] : false;

		// If a global variable is set to the font, then return the variable,
		// since the global typography variable will also contain the fallback fonts.
		if ( false !== strpos( $google_font, 'var(' ) ) {
			return $google_font;
		}

		// Exit early by returning the fallback font
		// in case no google-font is defined.
		if ( false === $google_font ) {
			return $this->format_font_family( $fallback_fonts );
		}

		// Exit early returning the google font
		// in case no fallback font is defined.
		if ( false === $fallback_fonts || '' === $fallback_fonts ) {
			return $this->format_font_family( $google_font );
		}

		// Exit early returning the google (primary) font
		// in case google font is set to use standard font and it's the same as fallback font.
		if ( $google_font === $fallback_fonts ) {
			return $this->format_font_family( $google_font );
		}

		// Return the sum of the font-families properly formatted.
		return $this->format_font_family( $google_font . ', ' . $fallback_fonts );

	}

	/**
	 * Formats the font-family for CSS use.
	 *
	 * @access public
	 * @since 5.0.3
	 * @param string $family The font-family to use.
	 * @return string
	 */
	public function format_font_family( $family ) {

		// Make sure nothing malicious comes through.
		$family = wp_strip_all_tags( $family );

		// Remove quotes and double-quotes.
		// We'll add these back later if they are indeed needed.
		$family = str_replace( [ '"', "'" ], '', $family );

		if ( empty( $family ) ) {
			return '';
		}

		$families = [];
		// If multiple font-families, make sure each-one of them is sanitized separately.
		if ( false !== strpos( $family, ',' ) ) {
			$families = explode( ',', $family );
			foreach ( $families as $key => $value ) {
				$value = trim( $value );
				// Add quotes if needed.
				if ( false !== strpos( $value, ' ' ) ) {
					$value = '"' . $value . '"';
				}
				$families[ $key ] = $value;
			}
			$family = implode( ', ', $families );
		} else {
			// Add quotes if needed.
			if ( false !== strpos( $family, ' ' ) ) {
				$family = '"' . $family . '"';
			}
		}
		return $family;
	}

	/**
	 * Get the dynamic-css ID.
	 *
	 * @access public
	 * @since 1.6
	 * @return string
	 */
	public function get_dynamic_css_id() {
		$ids       = get_option( 'fusion_dynamic_css_ids', [] );
		$c_page_id = fusion_library()->get_page_id();
		$page_id   = ( $c_page_id ) ? $c_page_id : 'global';

		// If a layout is used, and we don't have a specific page ID, need to make sure they get different CSS IDs.
		if ( ! $c_page_id && class_exists( 'Fusion_Template_Builder' ) ) {
			$layout = Fusion_Template_Builder::get_instance()->get_override( 'layout' );

			if ( $layout && 'global' !== $layout->ID ) {
				$page_id = $layout->ID;
			}
		}

		if ( ! isset( $ids[ $page_id ] ) || ! $ids[ $page_id ] ) {
			$dynamic_css_obj = Fusion_Dynamic_CSS::get_instance();
			$dynamic_css     = $dynamic_css_obj->generate_final_css();
			$ids[ $page_id ] = md5( $dynamic_css );
			update_option( 'fusion_dynamic_css_ids', $ids );
		}
		return $ids[ $page_id ];
	}

	/**
	 * Get the dynamic-css.
	 *
	 * @access public
	 * @since 1.6
	 * @return string
	 */
	public function get_dynamic_css() {
		if ( ! self::$dynamic_css_parsed ) {
			$dynamic_css_array        = apply_filters( 'fusion_dynamic_css_array', self::$dynamic_css );
			self::$dynamic_css_parsed = $this->parser( $dynamic_css_array );
		}
		return self::$dynamic_css_parsed;
	}

	/**
	 * Combine element arrays to a single string.
	 * This helps clean-up our act and produces cleaner & more minimized CSS.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string|array $elements The elements.
	 * @return string
	 */
	public static function get_elements_string( $elements ) {

		// If it's a string, split to an array using comma as a delimiter.
		if ( is_string( $elements ) ) {
			$elements = explode( ',', $elements );
		}

		// Remove spaces etc from the beginning and end of elements.
		foreach ( $elements as $key => $element ) {
			$elements[ $key ] = trim( $element );
		}

		// Remove duplicates.
		$elements = array_unique( $elements );

		// Sort items in the array.
		sort( $elements );

		// Return the cleaned-up array as a string using comma as a delimiter.
		return implode( ',', $elements );
	}
}
