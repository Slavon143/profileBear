<?php
/**
 * A collection of callback methods.
 *
 * @package Fusion-Library
 * @since 2.0
 */

/**
 * A collection of sanitization methods.
 */
class Fusion_Panel_Callbacks {

	/**
	 * Sets alpha to color.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $color The color.
	 * @return string
	 */
	public static function sanitize_color( $color ) {
		if ( false !== strpos( $color, 'var(' ) ) {
			return $color;
		}
		$obj = Fusion_Color::new_color( $color );
		return $obj->to_css( $obj->mode );
	}

	/**
	 * Takes any valid CSS unit and converts to pixels.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The CSS value.
	 * @return string
	 */
	public static function units_to_px( $value ) {
		return Fusion_Sanitize::units_to_px( $value );
	}

	/**
	 * Sets alpha to color.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $color The color.
	 * @param float  $alpha The alpha value. float between 0 and 1.
	 * @return string
	 */
	public static function color_alpha_set( $color, $alpha = 1 ) {
		return Fusion_Color::new_color( $color )->get_new( 'alpha', $alpha )->to_css_var_or_rgba();
	}

	/**
	 * Checks conditions and returns either the value or an empty string.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @param array  $args  Arguments containing the conditions we want to check.
	 * @return string
	 */
	public static function conditional_return_value( $value, $args ) {
		$numeric  = false;
		$checks   = [];
		$fallback = isset( $args['value_pattern'] ) ? $args['value_pattern'][1] : '';
		$success  = isset( $args['value_pattern'] ) ? $args['value_pattern'][0] : '$';

		foreach ( $args['conditions'] as $i => $arg ) {
			$saved = fusion_get_option( $arg[0] );
			if ( false !== strpos( $arg[0], '[' ) ) {
				$parts = explode( '[', $arg[0] );
				$saved = fusion_get_option( $parts[0] );
				if ( isset( $parts[1] ) ) {
					$saved = isset( $saved[ str_replace( ']', '', $parts[1] ) ] ) ? $saved[ str_replace( ']', '', $parts[1] ) ] : '';
				}
			}

			if ( class_exists( 'AWB_Global_Typography' ) && AWB_Global_Typography::get_instance()->is_typography_css_var( $saved ) ) {
				$saved = AWB_Global_Typography::get_instance()->get_real_value( $saved );
			}

			switch ( $arg[1] ) {
				case '===':
					$checks[ $i ] = ( $saved === $arg[2] );
					break;
				case '>':
					$checks[ $i ] = ( Fusion_Sanitize::number( Fusion_Sanitize::units_to_px( $saved ) ) > Fusion_Sanitize::number( $arg[2] ) );
					break;
				case '>=':
					$checks[ $i ] = ( Fusion_Sanitize::number( Fusion_Sanitize::units_to_px( $saved ) ) >= Fusion_Sanitize::number( $arg[2] ) );
					break;
				case '<':
					$checks[ $i ] = ( Fusion_Sanitize::number( Fusion_Sanitize::units_to_px( $saved ) ) < Fusion_Sanitize::number( $arg[2] ) );
					break;
				case '<=':
					$checks[ $i ] = ( Fusion_Sanitize::number( Fusion_Sanitize::units_to_px( $saved ) ) <= Fusion_Sanitize::number( $arg[2] ) );
					break;
				case '!==':
					$checks[ $i ] = ( $saved !== $arg[2] );
					break;
				case 'in':
					$sub_checks = [];
					foreach ( $arg[2] as $k => $sub_arg ) {
						$sub_checks[ $k ] = ( $saved !== $sub_arg );
					}
					$checks[ $i ] = true;
					foreach ( $sub_checks as $sub_val ) {
						if ( ! $sub_val ) {
							$checks[ $i ] = false;
						}
					}
					break;
				case 'true':
					$checks[ $i ] = in_array( $saved, [ true, 'true', 1, '1', 'yes' ], true );
					break;
			}
		}

		foreach ( $checks as $check ) {
			if ( ! $check ) {
				return str_replace( '$', $value, $fallback );
			}
		}
		return str_replace( '$', $value, $success );
	}

	/**
	 * Returns a different value depending on whether the color is transparent or opaque.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The color value.
	 * @param array  $args  The arguments ['transparent'=>'foo','opaque'=>'bar'].
	 * @return string
	 */
	public static function return_color_if_opaque( $value, $args ) {
		if ( 'transparent' === $value ) {
			return $args['transparent'];
		}

		$color = Fusion_Color::new_color( $value );

		if ( 1 > $color->alpha ) {
			return $args['transparent'];
		}
		return $args['opaque'];
	}

	/**
	 * Returns a different value depending on whether the color is transparent or not.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The color value.
	 * @param array  $args  The arguments ['transparent'=>'foo','opaque'=>'bar'].
	 * @return string
	 */
	public static function return_string_if_transparent( $value, $args ) {
		if ( 'transparent' === $value ) {
			return ( '$' === $args['transparent'] ) ? $value : $args['transparent'];
		}
		$color = Fusion_Color::new_color( $value );

		if ( 0 === $color->alpha ) {
			return ( '$' === $args['transparent'] ) ? $value : $args['transparent'];
		}
		return ( '$' === $args['opaque'] ) ? $value : $args['opaque'];
	}

	/**
	 * Adjusts the brightness of a color,
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string           $value      The color we'll be adjusting.
	 * @param string|int|float $adjustment By how much we'll be adjusting.
	 *                                     Positive numbers increase lightness.
	 *                                     Negative numbers decrease lightness.
	 * @return string                      RBGA color, ready to be used in CSS.
	 */
	public static function lightness_adjust( $value, $adjustment ) {
		$adjustment = Fusion_Sanitize::number( $adjustment );
		if ( 1 >= abs( $adjustment ) ) {
			$adjustment *= 100;
		}
		return Fusion_Color::new_color( Fusion_Sanitize::color( $value ) )->adjust_brightness( $adjustment );
	}

	/**
	 * Runs str_replace.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @param array  $args  The arguments [search,replace].
	 * @return string
	 */
	public static function string_replace( $value, $args ) {
		return str_replace( $args[0], $args[1], $value );
	}

	/**
	 * If the color has 0 alpha, return hex.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The color.
	 * @return string
	 */
	public static function get_non_transparent_color( $value ) {
		$color = Fusion_Color::new_color( $value );
		if ( 0 === $color->alpha || 0.0 === $color->alpha ) {
			$color = $color->get_new( 'alpha', '1' );
			return $color->to_css_var_or_rgba();
		}
		return $color->to_css_var_or_rgba();
	}

	/**
	 * Header border-color custom condition 5.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value    The value.
	 * @param string $fallback A fallback value.
	 * @return string
	 */
	public static function header_border_color_condition_5( $value, $fallback = '' ) {
		$header_border_color = Fusion_Color::new_color( fusion_get_option( 'header_border_color' ) );
		if ( 'v6' !== fusion_get_option( 'header_layout' ) && 'left' === fusion_get_option( 'header_position' ) && 0 === $header_border_color->alpha ) {
			return $value;
		}
		return $fallback;
	}

	/**
	 * Returns the value if site-width is using % values,
	 * otherwise return empty string.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @param array  $args  Array(0=>'$',1=>'fallback').
	 * @return string
	 */
	public static function site_width_100_percent( $value, $args = [] ) {
		if ( ! isset( $args[0] ) && ! isset( $args[1] ) ) {
			$args = [ '$', '' ];
		}
		if ( '100%' === fusion_get_option( 'site_width' ) ) {
			return str_replace( '$', $value, $args[0] );
		}
		return $args[1];
	}

	/**
	 * Get the negative margin for 100%-width.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @return string
	 */
	public static function hundred_percent_negative_margin() {
		$c_page_id       = fusion_library()->get_page_id();
		$padding         = Fusion_Sanitize::size( fusion_get_option( 'hundredp_padding', 'hundredp_padding', $c_page_id ) );
		$padding_value   = Fusion_Sanitize::number( $padding );
		$padding_unit    = Fusion_Sanitize::get_unit( $padding );
		$negative_margin = '-' . $padding_value . $padding_unit;

		if ( '%' === $padding_unit ) {
			$fullwidth_max_width = 100 - 2 * $padding_value;
			$negative_margin     = '-' . $padding_value / $fullwidth_max_width * 100 . $padding_unit;
		}
		return $negative_margin;
	}

	/**
	 * Fallback to 0 if value does not exist or is empty.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function fallback_to_zero( $value ) {
		return self::fallback_to_value( $value, '0' );
	}

	/**
	 * Fallback to another value if value does not exist or is empty.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @param string $fallback The fallback value.
	 * @return string
	 */
	public static function fallback_to_value( $value, $fallback ) {
		if ( is_array( $fallback ) && isset( $fallback[0] ) && isset( $fallback[1] ) ) {
			return ( ! $value || '' === $value ) ? str_replace( '$', $value, $fallback[1] ) : str_replace( '$', $value, $fallback[0] );
		}
		return ( ! $value || '' === $value ) ? $fallback : $value;
	}

	/**
	 * Fallback to another value if value does not exist.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @param string $fallback The fallback value.
	 * @return string
	 */
	public static function fallback_to_value_if_empty( $value, $fallback ) {
		if ( is_array( $fallback ) && isset( $fallback[0] ) && isset( $fallback[1] ) ) {
			return ( '' === $value ) ? str_replace( '$', $value, $fallback[1] ) : str_replace( '$', $value, $fallback[0] );
		}
		return ( '' === $value ) ? $fallback : $value;
	}

	/**
	 * If value is numeric append "px".
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The value.
	 * @return string
	 */
	public static function maybe_append_px( $value ) {
		if ( is_numeric( $value ) ) {
			return $value . 'px';
		}
		return $value;
	}

	/**
	 * Converts a non-px font size to px.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @see Fusion_Sanitize::convert_font_size_to_px().
	 * @param string $font_size The font size to be changed.
	 * @param string $base_font_size The font size to base calcs on.
	 * @return string The changed font size.
	 */
	public static function convert_font_size_to_px( $font_size, $base_font_size ) {
		$add_units = ( is_array( $base_font_size ) && isset( $base_font_size['addUnits'] ) && $base_font_size['addUnits'] );
		if ( is_array( $base_font_size ) && isset( $base_font_size['setting'] ) ) {
			$base_font_size = fusion_get_option( $base_font_size['setting'] );
		}
		$value = Fusion_Sanitize::convert_font_size_to_px( $font_size, $base_font_size );

		return ( $add_units ) ? $value . 'px' : $value;
	}

	/**
	 * Combines font-family & font-backup options.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value       The font-family value.
	 * @param string $option_name The name of the option.
	 * @return string
	 */
	public static function combined_font_family( $value, $option_name ) {
		$helpers = new Fusion_Dynamic_CSS_Helpers();
		$combo   = $helpers->combined_font_family( fusion_get_option( $option_name ) );
		return $combo ? $combo : $value;
	}

	/**
	 * Converts the "regular" value to 400 for font-weights.
	 *
	 * @static
	 * @access public
	 * @since 2.0
	 * @param string $value The font-weight.
	 * @return string       The changed font-weight.
	 */
	public static function font_weight_no_regular( $value ) {
		return ( 'regular' === $value ) ? '400' : $value;
	}
}
