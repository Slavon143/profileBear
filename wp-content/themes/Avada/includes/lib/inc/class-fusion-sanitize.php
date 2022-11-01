<?php
/**
 * A collection of sanitization methods.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * A collection of sanitization methods.
 */
class Fusion_Sanitize {

	/**
	 * Sanitize values like for example 10px, 30% etc.
	 *
	 * @param string       $value         The value to sanitize.
	 * @param false|string $fallback_unit A fallback unit to use in case no unit is found.
	 * @return string
	 */
	public static function size( $value, $fallback_unit = false ) {

		if ( false !== strpos( $value, '--awb' ) ) {
			return $value;
		}

		// Trim the value.
		$value = trim( $value );

		if ( in_array( $value, [ 'auto', 'inherit', 'initial' ], true ) ) {
			return $value;
		}

		// Return empty if there are no numbers in the value.
		// Prevents some CSS errors.
		if ( ! preg_match( '#[0-9]#', $value ) ) {
			return '';
		}

		if ( false !== strpos( $value, 'calc' ) ) {
			return $value;
		}

		$unit = self::get_unit( $value );
		if ( $fallback_unit && '' === $unit ) {
			$unit = ( true === $fallback_unit ) ? 'px' : $fallback_unit;
		}
		return self::number( $value ) . $unit;

	}

	/**
	 * Return the unit of a given value.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value A value with unit.
	 * @return string The unit of the given value.
	 */
	public static function get_unit( $value ) {

		if ( false !== strpos( $value, '--awb' ) && function_exists( 'AWB_Global_Typography' ) ) {
			$value = AWB_Global_Typography()->get_real_value( $value );
		}

		$unit_used = '';

		// Trim the value.
		$value = trim( $value );

		// The array of valid units.
		$units = [ 'px', 'rem', 'em', '%', 'vmin', 'vmax', 'vh', 'vw', 'ex', 'cm', 'mm', 'in', 'pt', 'pc', 'ch' ];

		foreach ( $units as $unit ) {

			// Find what unit we're using.
			if ( false !== strpos( $value, $unit ) ) {
				$unit_used = $unit;
				break;
			}
		}

		return $unit_used;

	}

	/**
	 * Adds a specified unit to a unitless value and keeps the value unchanged if a unit is present.
	 * A forced unit replace can also be done.
	 *
	 * @param string $value         A value like a margin setting etc., with or without unit.
	 * @param string $unit          A unit that should be appended to unitless values.
	 * @param string $unit_handling 'add': only add $unit if $value is unitless.
	 *                              'force_replace': replace the unit of $value with $unit.
	 */
	public static function get_value_with_unit( $value, $unit = 'px', $unit_handling = 'add' ) {

		if ( false !== strpos( $value, '--awb' ) ) {
			return $value;
		}

		$raw_values = [];

		// Trim the value.
		$value = trim( $value );

		if ( in_array( $value, [ 'auto', 'inherit', 'initial' ], true ) ) {
			return $value;
		}

		// Return empty if there are no numbers in the value.
		// Prevents some CSS errors.
		if ( ! preg_match( '#[0-9]#', $value ) ) {
			return;
		}

		// Explode if has multiple values.
		$values = explode( ' ', $value );

		if ( is_array( $values ) && ! empty( $values ) ) {
			foreach ( $values as $value ) {
				$raw_value = self::number( $value );

				// Isn't a number, do not add unit.
				if ( ! is_numeric( $raw_value ) ) {
					$raw_values[] = $value;
					continue;
				}

				if ( $value === $raw_value ) {
					$value = $raw_value . $unit;
				} elseif ( 'force_replace' === $unit_handling ) {
					$value = $raw_value . $unit;
				}

				$raw_values[] = $value;
			}

			return implode( ' ', $raw_values );

		}
		$raw_value = self::number( $value );

		if ( $value === $raw_value ) {
			return $raw_value . $unit;
		}
		if ( 'force_replace' === $unit_handling ) {
			return $raw_value . $unit;
		}

		return $value;
	}

	/**
	 * Sanitises a HEX value.
	 * (part of the Kirki Toolkit).
	 * The way this works is by splitting the string in 6 substrings.
	 * Each sub-string is individually sanitized, and the result is then returned.
	 *
	 * @param string $color The hex value of a color.
	 * @return string
	 */
	public static function hex( $color ) {
		return Fusion_Color::new_color( $color )->to_css( 'hex' );
	}

	/**
	 * Sanitizes an rgba color value.
	 * (part of the Kirki Toolkit).
	 *
	 * @param  string $value The value to sanitize.
	 * @return  string
	 */
	public static function rgba( $value ) {
		return Fusion_Color::new_color( $value )->to_css( 'rgba' );
	}

	/**
	 * Sanitize colors.
	 * (part of the Kirki Toolkit).
	 * Determine if the current value is a hex or an rgba color and call the appropriate method.
	 *
	 * @since 1.0.0
	 *
	 * @param  string $value   string  hex or rgba color.
	 * @return string
	 */
	public static function color( $value ) {

		// If its a variable, just return it.
		if ( is_string( $value ) && ( '--' === substr( $value, 0, 2 ) || false !== strpos( $value, 'var' ) ) ) {
			return $value;
		}
		$color_obj = Fusion_Color::new_color( $value );
		$mode      = ( is_array( $value ) ) ? 'rgba' : $color_obj->mode;
		return $color_obj->to_css( $mode );
	}

	/**
	 * Gets the rgba value of the $hex color.
	 * (part of the Kirki Toolkit).
	 *
	 * @param string $hex     The hex value of a color.
	 * @param int    $opacity Opacity level (1-100).
	 * @return string
	 */
	public static function get_rgba( $hex = '#fff', $opacity = 100 ) {
		$color_obj = Fusion_Color::new_color( $hex );
		$alpha     = ( 1 < $opacity ) ? $opacity / 100 : $opacity;
		return $color_obj->get_new( 'alpha', $alpha )->to_css( 'rgba' );
	}

	/**
	 * Gets the rgb value of the $hex color.
	 * (part of the Kirki Toolkit).
	 *
	 * @param   string  $hex     The hex value of a color.
	 * @param   boolean $implode Whether we want to implode the values or not.
	 * @return  array|string
	 */
	public static function get_rgb( $hex, $implode = false ) {
		$color_obj = Fusion_Color::new_color( $hex );
		if ( $implode ) {
			return $color_obj->to_css( 'rgb' );
		}
		return [
			$color_obj->red,
			$color_obj->green,
			$color_obj->blue,
		];
	}

	/**
	 * Strips the alpha value from an RGBA color string.
	 *
	 * @param   string $rgba    The RGBA color string.
	 * @return  string          The corresponding RGB string.
	 */
	public static function rgba_to_rgb( $rgba ) {
		$color_obj = Fusion_Color::new_color( $rgba );
		return $color_obj->to_css( 'rgb' );
	}

	/**
	 * Properly escape some characters in image URLs so that they may be properly used in CSS.
	 * From W3C:
	 * > Some characters appearing in an unquoted URI,
	 * > such as parentheses, white space characters, single quotes (') and double quotes ("),
	 * > must be escaped with a backslash so that the resulting URI value is a URI token: '\(', '\)'.
	 *
	 * @param  string $url The URL to modify.
	 * @return string The modified URL.
	 */
	public static function css_asset_url( $url ) {

		$url = esc_url_raw( $url );

		$url = str_replace( '(', '\(', $url );
		$url = str_replace( ')', '\)', $url );
		$url = str_replace( '"', '\"', $url );
		$url = str_replace( ' ', '\ ', $url );
		$url = str_replace( "'", "\'", $url );

		return $url;

	}

	/**
	 * Removes the scheme of the passed URL to fit the current page.
	 *
	 * @param string $url The URL that needs sanitation.
	 * @return string     Full URL without scheme.
	 */
	public static function get_url_with_correct_scheme( $url ) {
		return set_url_scheme( $url );
	}

	/**
	 * Sanitizes a number value.
	 *
	 * @param string|int|float $value The value to sanitize.
	 * @return float|int
	 */
	public static function number( $value ) {
		return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
	}


	/**
	 * Orders an array like another one with the same keys.
	 *
	 * @since 1.0.0
	 *
	 * @param array $to_be_ordered The array that should be ordered.
	 * @param array $order_like The array that should be used to order $to_be_ordered.
	 *
	 * @return array The correctly ordered version of $to_be_ordered.
	 */
	public static function order_array_like_array( array $to_be_ordered, array $order_like ) {
		$ordered = [];

		foreach ( $order_like as $key => $value ) {
			if ( array_key_exists( $key, $to_be_ordered ) ) {
				$ordered[ $key ] = $to_be_ordered[ $key ];
				unset( $to_be_ordered[ $key ] );
			}
		}

		return $ordered + $to_be_ordered;
	}

	/**
	 * Sanitizes the envato token & refreshes the transients.
	 *
	 * @access public
	 * @param string $value The token.
	 * @return string
	 */
	public static function envato_token( $value ) {
		delete_transient( 'avada_is_envato_valid' );
		if ( is_string( $value ) ) {
			return trim( $value );
		}
		return '';
	}

	/**
	 * Adds CSS values.
	 *
	 * @static
	 * @access public
	 * @since 5.1.0
	 * @param array $values An array of CSS values.
	 * @return string       The combined value.
	 */
	public static function add_css_values( $values = [] ) {

		if ( ! is_array( $values ) || empty( $values ) ) {
			return '0';
		}

		$units       = [];
		$numerics    = [];
		$should_calc = false;
		// Figure out what we're dealing with.
		foreach ( $values as $key => $value ) {

			if ( 'auto' === $value || 'inherit' === $value || 'initial' === $value ) {
				continue;
			}

			// Trim the value.
			$value          = trim( $value );
			$values[ $key ] = $value;

			// Detect if the value uses calc().
			if ( false !== strpos( $value, 'calc' ) ) {
				$should_calc = true;
			}

			// Add unit to the array of units used.
			$unit = trim( self::get_unit( $value ) );
			if ( ! empty( $unit ) && ! in_array( $unit, $units, true ) ) {
				$units[] = $unit;
			}

			// Add numeric value to the array of numerics.
			$numerics[] = self::number( $value );
		}

		// Make sure there's 1 instance of each unit in the array.
		// We need that to figure out if we'll be using calc() or not below.
		$units = array_unique( $units );

		// If we're using more than one units then we should use calc().
		if ( 1 < count( $units ) ) {
			$should_calc = true;
		}

		// All values added use the same unit and no calc() is necessary.
		// We simply need to return the numeric sum with the defined value.
		if ( ! $should_calc ) {

			// No units, so just return the sum of all values.
			if ( 0 === count( $units ) ) {
				return array_sum( $numerics );
			}

			// Add values and append the unit.
			return array_sum( $numerics ) . $units[0];
		}

		// If we got this far then we need to use calc().
		$result = '';
		$i      = 0;
		foreach ( $values as $value ) {
			// Only add + if this is not the first item in the calculations.
			if ( 0 < $i ) {
				$result .= ' + ';
			}
			$i++;
			if ( false !== strpos( $value, 'calc' ) ) {
				// Remove calc but keep the parentheses. This fixes a browser bug in older versions of some browsers
				// where nested calc values don't work. Leaving the parentheses has the exact same effect.
				$result .= str_replace( 'calc', '', $value );
				continue;
			}

			$result .= $value;
		}

		// Remove multiple spaces.
		$result = str_replace( [ '     ', '    ', '   ', '  ' ], ' ', $result );
		// A simple tweak to make sure that negative values are substracted.
		$result = str_replace( '+ -', ' - ', $result );
		// The above might have resulted is a couple of double-spaces, so make them single again.
		$result = str_replace( '  ', ' ', $result );
		// Put it all together and wrap it up.
		return 'calc(' . $result . ')';
	}

	/**
	 * Takes any valid CSS unit and converts to pixels.
	 *
	 * @static
	 * @access public
	 * @since 1.3.0
	 * @param string     $value          The CSS value.
	 * @param string|int $body_font_size The body font-size, used to calculate em/rem.
	 * @param string|int $screen_size    In pixels.
	 * @return string
	 */
	public static function units_to_px( $value, $body_font_size = 16, $screen_size = 1920 ) {
		if ( false !== strpos( $value, '--awb' ) && function_exists( 'AWB_Global_Typography' ) ) {
			$value = AWB_Global_Typography()->get_real_value( $value );
		}
		$number = self::number( $value );
		$units  = self::get_unit( $value );

		// Calculate size if em/rem.
		if ( 'em' === $units || 'rem' === $units ) {
			return intval( $number * $body_font_size ) . 'px';
		}

		// Calculate size if using percent (%).
		if ( '%' === $units || 'vw' === $units ) {
			return intval( $number * $screen_size / 100 ) . 'px';
		}

		// Fallback to the value as-is.
		return intval( $number ) . 'px';
	}

	/**
	 * Converts a non-px font size to .
	 *
	 * @since 1.9
	 *
	 * @param string $font_size The font size to be changed.
	 * @param string $base_font_size The font size to base calcs on.
	 * @return string The changed font size.
	 */
	public static function convert_font_size_to_px( $font_size, $base_font_size ) {
		if ( false !== strpos( $font_size, '--awb' ) && function_exists( 'AWB_Global_Typography' ) ) {
			$font_size = AWB_Global_Typography()->get_real_value( $font_size );
		}
		$font_size_unit   = self::get_unit( $font_size );
		$font_size_number = self::number( $font_size );

		if ( 'rem' === $font_size_unit ) {
			$body_font_size = fusion_library()->get_option( 'body_typography', 'font-size' );
			$base_font_size = $body_font_size ? $body_font_size : $base_font_size;
		}

		if ( false !== strpos( $base_font_size, '--awb' ) && function_exists( 'AWB_Global_Typography' ) ) {
			$base_font_size = AWB_Global_Typography()->get_real_value( $base_font_size );
		}

		$base_font_size_unit   = self::get_unit( $base_font_size );
		$base_font_size_number = self::number( $base_font_size );

		if ( ! $font_size_number ) {
			return $font_size;
		}

		if ( 'px' === $font_size_unit ) {
			return $font_size_number;
		}

		// Browser default font size. This is the average between Safari, Chrome and FF.
		$default_font_size = 15;

		if ( 'em' === $base_font_size_unit || 'rem' === $base_font_size_unit ) {
			$base_font_size_number = $default_font_size * $base_font_size_number;
		} elseif ( '%' === $base_font_size_unit ) {
			$base_font_size_number = $default_font_size * $base_font_size_number / 100;
		} elseif ( 'px' !== $base_font_size_unit ) {
			$base_font_size_number = $default_font_size;
		}

		if ( 'em' === $font_size_unit || 'rem' === $font_size_unit ) {
			$font_size_number = $base_font_size_number * $font_size_number;
		} elseif ( '%' === $font_size_unit ) {
			$font_size_number = $base_font_size_number * $font_size_number / 100;
		} elseif ( 'px' !== $font_size_unit ) {
			$font_size_number = $base_font_size_number;
		}

		return $font_size_number;
	}
}
