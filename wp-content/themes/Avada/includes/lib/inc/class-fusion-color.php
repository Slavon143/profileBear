<?php
/**
 * Plugin Name:   Fusion Library
 * Description:   A PHP library for color manipulation
 * Author:        ThemeFusion
 * Author URI:    https://theme-fusion.com
 * Version:       1.1.0
 *
 * @package     Fusion Library
 * @category    Core
 * @author      ThemeFusion
 * @copyright   Copyright (c) ThemeFusion
 * @since       1.0
 *
 */

// phpcs:ignoreFile

if ( ! class_exists( 'Fusion_Color' ) ) {
	/**
	 * The color calculations class.
	 */
	class Fusion_Color {

		/**
		 * An array of our instances.
		 *
		 * @static
		 * @access public
		 * @since 1.0.0
		 * @var array
		 */
		public static $instances = array();

		/**
		 * The color initially set.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var mixed
		 */
		public $color;

		/**
		 * A fallback color in case of failure.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var mixed
		 */
		public $fallback = '#ffffff';

		/**
		 * Fallback object from the fallback color.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var object
		 */
		public $fallback_obj;

		/**
		 * The mode we're using for this color.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		public $mode = 'hex';

		/**
		 * An array containing all word-colors (white/blue/red etc)
		 * and their corresponding HEX codes.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var array
		 */
		public $word_colors = array();

		/**
		 * The hex code of the color.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var string
		 */
		public $hex;

		/**
		 * Red value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var int
		 */
		public $red   = 0;

		/**
		 * Green value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var int
		 */
		public $green = 0;

		/**
		 * Blue value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var int
		 */
		public $blue  = 0;

		/**
		 * Alpha value (min:0, max: 1)
		 *
		 * @access public
		 * @since 1.0.0
		 * @var float|int
		 */
		public $alpha = 1;

		/**
		 * Hue value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var float
		 */
		public $hue;

		/**
		 * Saturation value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var float
		 */
		public $saturation;

		/**
		 * Lightness value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var float
		 */
		public $lightness;

		/**
		 * Chroma value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var float
		 */
		public $chroma;

		/**
		 * An array containing brightnesses.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var array
		 */
		public $brightness = array();

		/**
		 * Luminance value.
		 *
		 * @access public
		 * @since 1.0.0
		 * @var float
		 */
		public $luminance;

		/**
		 * Closest word color match.
		 *
		 * @access public
		 * @since 3.1
		 * @var string
		 */
		public $closets_word_color_match = 'black';

		/**
		 * If the current color is from a global color, then remember this.
		 *
		 * @since 7.7
		 * @var string
		 */
		public $global_color_slug = '';

		/**
		 * The class constructor.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @param string|array $color The color.
		 * @param string       $mode  The color mode. Leave empty to auto-detect.
		 */
		protected function __construct( $color = '', $mode = 'auto' ) {
			$this->color = $this->evaluate_color( $color );

			if ( is_array( $color ) && isset( $color['fallback'] ) ) {
				$this->fallback = $color['fallback'];
				$this->fallback_obj = self::newColor( $this->fallback );
			}

			if ( ! method_exists( $this, 'from_' . $mode ) ) {
				$mode = $this->get_mode( $this->color );
			}

			$this->mode = $mode;

			if ( ! $mode ) {
				return;
			}

			$this->mode = $mode;
			$method = 'from_' . $mode;
			// Call the from_{$color_mode} method.
			$this->$method();

			// Make alpha an int if is 0 or 1;
			if ( 0.001 > $this->alpha ) {
				$this->alpha = 0;
			} elseif( 0.999 < $this->alpha ) {
				$this->alpha = 1;
			}
		}

		/**
		 * Gets an instance for this color.
		 * We use a separate instance per color
		 * because there's no need to create a completely new instance each time we call this class.
		 * Instead using instances helps us improve performance & footprint.
		 *
		 * @static
		 * @access public
		 * @since 1.0.0
		 * @param string|array $color The color.
		 * @param string       $mode  Mode to be used.
		 * @param string       $global_color_slug The global color slug to remember where this color comes from.
		 * @return Fusion_Color (object)
		 */
		public static function newColor( $color, $mode = 'auto', $global_color_slug = '' ) {

			// Get an md5 for this color.
			$color_md5 = ( is_array( $color ) ) ? md5( wp_json_encode( $color ) . $mode ) : md5( $color . $mode );
			// Set the instance if it does not already exist.
			if ( ! isset( self::$instances[ $color_md5 ] ) ) {
				self::$instances[ $color_md5 ] = new self( $color, $mode );
				if ( $global_color_slug ) {
					self::$instances[ $color_md5 ]->global_color_slug = $global_color_slug;
				}
			}
			return self::$instances[ $color_md5 ];
		}

		/**
		 * Alias of the newColor method.
		 *
		 * @static
		 * @access public
		 * @since 1.1
		 * @param string|array $color The color.
		 * @param string       $mode  Mode to be used.
		 * @param string       $global_color_slug The global color slug to remember where this color comes from.
		 * @return Fusion_Color (object)
		 */
		public static function new_color( $color, $mode = 'auto', $global_color_slug = '' ) {
			return self::newColor( $color, $mode, $global_color_slug );
		}

		/**
		 * Allows us to get a new instance by modifying a property of the existing one.
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string           $property   Can be one of the following:
		 *                             red,
		 *                             green,
		 *                             blue,
		 *                             alpha,
		 *                             hue,
		 *                             saturation,
		 *                             lightness,
		 *                             brightness.
		 * @param int|float|string $value      The new value.
		 * @return Fusion_Color|null
		 */
		public function getNew( $property = '', $value = '' ) {

			if ( in_array( $property, array( 'red', 'green', 'blue', 'alpha' ), true ) ) {
				// Check if we're changing any of the rgba values.
				$value = max( 0, min( 255, $value ) );
				if ( 'red' === $property ) {
					return self::new_color( 'rgba(' . $value . ',' . $this->green . ',' . $this->blue . ',' . $this->alpha . ')', 'rgba', $this->global_color_slug );
				} elseif ( 'green' === $property ) {
					return self::new_color( 'rgba(' . $this->red . ',' . $value . ',' . $this->blue . ',' . $this->alpha . ')', 'rgba', $this->global_color_slug );
				} elseif ( 'blue' === $property ) {
					return self::new_color( 'rgba(' . $this->red . ',' . $this->green . ',' . $value . ',' . $this->alpha . ')', 'rgba', $this->global_color_slug );
				} elseif ( 'alpha' === $property ) {
					return self::new_color( 'rgba(' . $this->red . ',' . $this->green . ',' . $this->blue . ',' . $value . ')', 'rgba', $this->global_color_slug );
				}
			} elseif ( in_array( $property, array( 'hue', 'saturation', 'lightness' ), true ) ) {
				// Check if we're changing any of the hsl values.
				$value = ( 'hue' === $property ) ? max( 0, min( 360, $value ) ) : max( 0, min( 100, $value ) );

				if ( 'hue' === $property ) {
					return self::new_color( 'hsla(' . $value . ',' . $this->saturation . '%,' . $this->lightness . '%,' . $this->alpha . ')', 'hsla', $this->global_color_slug );
				} elseif ( 'saturation' === $property ) {
					return self::new_color( 'hsla(' . $this->hue . ',' . $value . '%,' . $this->lightness . '%,' . $this->alpha . ')', 'hsla', $this->global_color_slug );
				} elseif ( 'lightness' === $property ) {
					return self::new_color( 'hsla(' . $this->hue . ',' . $this->saturation . '%,' . $value . '%,' . $this->alpha . ')', 'hsla', $this->global_color_slug );
				}
			} elseif ( 'brightness' === $property ) {
				// Check if we're changing the brightness.
				if ( $value < $this->brightness['total'] ) {
					$red   = max( 0, min( 255, $this->red - ( $this->brightness['total'] - $value ) ) );
					$green = max( 0, min( 255, $this->green - ( $this->brightness['total'] - $value ) ) );
					$blue  = max( 0, min( 255, $this->blue - ( $this->brightness['total'] - $value ) ) );
				} elseif ( $value > $this->brightness['total'] ) {
					$red   = max( 0, min( 255, $this->red + ( $value - $this->brightness['total'] ) ) );
					$green = max( 0, min( 255, $this->green + ( $value - $this->brightness['total'] ) ) );
					$blue  = max( 0, min( 255, $this->blue + ( $value - $this->brightness['total'] ) ) );
				} else {
					// If it's not smaller and it's not greater, then it's equal.
					return $this;
				}
				return self::new_color( 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $this->alpha . ')', 'rgba', $this->global_color_slug );
			}
			return null;
		}

		/**
		 * Alias for the getNew method.
		 *
		 * @access public
		 * @since 1.1.0
		 * @param string           $property   Can be one of the following:
		 *                             red,
		 *                             green,
		 *                             blue,
		 *                             alpha,
		 *                             hue,
		 *                             saturation,
		 *                             lightness,
		 *                             brightness.
		 * @param int|float|string $value      The new value.
		 * @return Fusion_Color|null
		 */
		public function get_new( $property = '', $value = '' ) {
			return $this->getNew( $property, $value );
		}

		/**
		 * Figure out what mode we're using.
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string|array $color The color we're querying.
		 * @return string
		 */
		public function get_mode( $color ) {

			// Check if value is an array.
			if ( is_array( $color ) ) {
				// Does the array have an 'rgba' key?
				if ( isset( $color['rgba'] ) ) {
					$this->color = $color['rgba'];
					return 'rgba';
				} elseif ( isset( $color['color'] ) ) {
					// Does the array have a 'color' key?
					$this->color = $color['color'];
					if ( is_string( $color['color'] ) && false !== strpos( $color['color'], 'rgba' ) ) {
						return 'rgba';
					}
					return 'hex';
				}
				// Is this a simple array with 4 items?
				if ( 4 === count( $color ) && isset( $color[0] ) && isset( $color[1] ) && isset( $color[2] ) && isset( $color[3] ) ) {
					$this->color = 'rgba(' . intval( $color[0] ) . ',' . intval( $color[1] ) . ',' . intval( $color[2] ) . ',' . intval( $color[3] ) . ')';
					return 'rgba';
				} elseif ( 3 === count( $color ) && isset( $color[0] ) && isset( $color[1] ) && isset( $color[2] ) ) {
					// Is this a simple array with 3 items?
					$this->color = 'rgba(' . intval( $color[0] ) . ',' . intval( $color[1] ) . ',' . intval( $color[2] ) . ',1)';
					return 'rgba';
				}

				// Check for other keys in the array and get values from there.
				$finders_keepers = array(
					'r'       => 'red',
					'g'       => 'green',
					'b'       => 'blue',
					'a'       => 'alpha',
					'red'     => 'red',
					'green'   => 'green',
					'blue'    => 'blue',
					'alpha'   => 'alpha',
					'opacity' => 'alpha',
				);
				$found = false;
				foreach ( $finders_keepers as $finder => $keeper ) {
					if ( isset( $color[ $finder ] ) ) {
						$found = true;
						$this->$keeper = $color[ $finder ];
					}
				}

				// We failed, use fallback.
				if ( ! $found ) {
					$this->from_fallback();
					return $this->mode;
				}

				// We did not fail, so use rgba values recovered above.
				$this->color = 'rgba(' . $this->red . ',' . $this->green . ',' . $this->blue . ',' . $this->alpha . ')';
				return 'rgba';
			}

			$color = trim( strtolower( $color ) );

			if ( 'transparent' === $color ) {
				$color = 'rgba(255,255,255,0)';
				$this->color = $color;
			}

			// If a string and 3 or 6 characters long, add # since it's a hex.
			if ( 3 === strlen( $this->color ) || 6 === strlen( $this->color ) && false === strpos( $this->color, '#' ) ) {
				$this->color = '#' . $this->color;
				$color = $this->color;
			}

			// If we got this far, it's not an array.
			// Check for key identifiers in the value.
			$finders_keepers = array(
				'#'    => 'hex',
				'rgba' => 'rgba',
				'rgb'  => 'rgb',
				'hsla' => 'hsla',
				'hsl'  => 'hsl',
			);
			foreach ( $finders_keepers as $finder => $keeper ) {
				if ( false !== strrpos( $color, $finder ) ) {

					// Make sure hex colors have 6 digits and not more.
					if ( '#' === $finder && 7 < strlen( $color ) ) {
						$this->color = substr( $color, 0, 7 );
					}

					return $keeper;
				}
			}
			// Perhaps we're using a word like "orange"?
			$wordcolors = $this->get_word_colors();
			if ( array_key_exists( $color, $wordcolors ) ) {
				$this->color = '#' . $wordcolors[ $color ];
				return 'hex';
			}
			// Fallback to hex.

			$this->color = $this->fallback;
			return 'hex';
		}

		/**
		 * Replace variables with values where possible.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return void
		 */
		protected function evaluate_color( $color ) {
			if ( is_array( $color ) && isset( $color[ 'color' ] ) ) {
				$color = $color[ 'color' ];
			}

			if ( ! is_string( $color ) || ! function_exists( 'AWB_Global_Colors' ) ) {
				return $color;
			}

			$var_pos = strpos( $color, 'var' );
			if ( false === $var_pos ) {
				return $color;
			}

			$palettes  = AWB_Global_Colors()->get_palette();

			// Single var, find and replace with value.
			if ( 0 === $var_pos ) {
				$slug     = str_replace( [ 'var(--awb-', ')', ' ' ], '', $color );

				if ( isset( $palettes[ $slug ] ) ) {
					// Set the global slug to remember where this color comes from.
					$this->global_color_slug = $slug;
					return $palettes[ $slug ]['color'];
				}

				// Failed, return default.
				return 'hsla(0,0%,0%,1)';
			}

			// TODO: try to refactor or avoid this.
			$parts     = explode( ',', $color );

			if ( 4 === count( $parts ) ) {
				$map = [
					0 => 'hue',
					1 => 'saturation',
					2 => 'lightness',
					3 => 'alpha',
				];

				$color_object = false;
				foreach ( $parts as $index => $part ) {
					$finder = $part;
					$suffix = '-' . substr( $map[ $index ], 0, 1 );

					// No calc, should be a simple var, find it and add the value in.
					if ( false === strpos( $finder, 'calc(' ) ) {
						if ( ! $color_object ) {
							$finder = str_replace( [ 'var(--awb-', 'hsla(', ')', ' ', $suffix  ], '', $finder );
							if ( isset( $palettes[ $finder ] ) ) {
								// Set the global slug to remember where this color comes from.
								$this->global_color_slug = $finder;
								$color_object        = Fusion_Color::new_color( $palettes[ $finder ]['color'] );
								$parts[ $index ]     = $color_object->{ $map[ $index ] };
							}
						} else {
							$parts[ $index ] = $color_object->{ $map[ $index ] };
						}
					} else if ( strpos( $finder, ' + ' ) || strpos( $finder, ' - ' ) ) {
						$explode_at = strpos( $finder, ' + ' ) ? ' + ' : ' - ';
						$sign       = ' - ' === $explode_at ? '-' : '';
						$params     = explode( $explode_at, $part );
						$params[1]  = str_replace( [ ' ', '%', ')' ], '', $params[1] );

						if ( 2 === count( $params ) ) {

							// Work out the variable value.
							if ( ! $color_object ) {
								$finder = str_replace( [ 'hsla(', 'calc(', 'var(--awb-', ')', ' ', $suffix ], '', $params[0] );

								if ( isset( $palettes[ $finder ] ) ) {
									// Set the global slug to remember where this color comes from.
									if ( ! $this->global_color_slug ) {
										$this->global_color_slug = $finder;
									}
									$color_object        = Fusion_Color::new_color( $palettes[ $finder ]['color'] );
									$params[0]           = $color_object->{ $map[ $index ] };
								}
							} else {
								$params[0] = $color_object->{ $map[ $index ] };
							}

							// Add the two together.
							if ( 0 === $index ) {
								$parts[ $index ] = (float) $params[0] + (float) ( $sign . $params[1] ) % 360;
							} else if ( 1 === $index || 2 === $index ) {
								$parts[ $index ] = ( (float) $params[0] + (float) ( $sign . $params[1] ) );
							} else {
								$parts[ $index ] = ( ( (float) $params[0] * 100 ) + (float) ( $sign . $params[1] ) ) / 100;
							}
						}
					}
				}

				$parts[1] = min( max( 0, $parts[1] ), 100 );
				$parts[2] = min( max( 0, $parts[2] ), 100 );
				$parts[1] .= '%';
				$parts[2] .= '%';
				$color = 'hsla(' . implode( ',', $parts ) . ')';
			}

			// Tried our best but non-existent variable no doubt.
			if ( false !== strpos( $color, 'var' ) ) {
				return 'hsla(0,0%,0%,1)';
			}
			return $color;
		}

		/**
		 * Starts with a HEX color and calculates all other properties.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return void
		 */
		protected function from_hex() {

			if ( ! function_exists( 'sanitize_hex_color' ) ) {
				require_once wp_normalize_path( ABSPATH . WPINC . '/class-wp-customize-manager.php' );
			}
			// Is this perhaps a word-color?
			$word_colors = $this->get_word_colors();
			if ( array_key_exists( $this->color, $word_colors ) ) {
				$this->color = '#' . $word_colors[ $this->color ];
			}
			// Sanitize color.
			$this->hex = sanitize_hex_color( maybe_hash_hex_color( $this->color ) );
			$hex = ltrim( $this->hex, '#' );

			// Fallback if needed.
			if ( ! $hex || 3 > strlen( $hex ) ) {
				$this->from_fallback();
				return;
			}
			// Make sure we have 6 digits for the below calculations.
			if ( 3 === strlen( $hex ) ) {
				$hex = ltrim( $this->hex, '#' );
				$hex = substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) . substr( $hex, 2, 1 ) . substr( $hex, 2, 1 );
			}

			// Set red, green, blue.
			$this->red   = hexdec( substr( $hex, 0, 2 ) );
			$this->green = hexdec( substr( $hex, 2, 2 ) );
			$this->blue  = hexdec( substr( $hex, 4, 2 ) );
			$this->alpha = 1;
			// Set other color properties.
			$this->set_brightness();
			$this->set_hsl();
			$this->set_luminance();
			$this->set_closest_word_color_match();

		}

		/**
		 * Starts with an RGB color and calculates all other properties.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return void
		 */
		protected function from_rgb() {
			$value = explode( ',', str_replace( array( ' ', 'rgb', '(', ')' ), '', $this->color ) );
			// Set red, green, blue.
			$this->red   = ( isset( $value[0] ) ) ? intval( $value[0] ) : 255;
			$this->green = ( isset( $value[1] ) ) ? intval( $value[1] ) : 255;
			$this->blue  = ( isset( $value[2] ) ) ? intval( $value[2] ) : 255;
			$this->alpha = 1;
			// Set the hex.
			$this->hex = $this->rgb_to_hex( $this->red, $this->green, $this->blue );
			// Set other color properties.
			$this->set_brightness();
			$this->set_hsl();
			$this->set_luminance();
			$this->set_closest_word_color_match();
		}

		/**
		 * Starts with an RGBA color and calculates all other properties.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return void
		 */
		protected function from_rgba() {
			// Set r, g, b, a properties.
			$value = explode( ',', str_replace( array( ' ', 'rgba', '(', ')' ), '', $this->color ) );
			$this->red   = ( isset( $value[0] ) ) ? intval( $value[0] ) : 255;
			$this->green = ( isset( $value[1] ) ) ? intval( $value[1] ) : 255;
			$this->blue  = ( isset( $value[2] ) ) ? intval( $value[2] ) : 255;
			$this->alpha = ( isset( $value[3] ) ) ? filter_var( $value[3], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION ) : 1;
			// Limit values in the range of 0 - 255.
			$this->red   = max( 0, min( 255, $this->red ) );
			$this->green = max( 0, min( 255, $this->green ) );
			$this->blue  = max( 0, min( 255, $this->blue ) );
			// Limit values 0 - 1.
			$this->alpha = max( 0, min( 1, $this->alpha ) );
			// Set hex.
			$this->hex = $this->rgb_to_hex( $this->red, $this->green, $this->blue );
			// Set other color properties.
			$this->set_brightness();
			$this->set_hsl();
			$this->set_luminance();
			$this->set_closest_word_color_match();
		}

		/**
		 * Starts with an HSL color and calculates all other properties.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return void
		 */
		protected function from_hsl() {
			$value = explode( ',', str_replace( array( ' ', 'hsl', '(', ')', '%' ), '', $this->color ) );
			$this->hue        = floatval( $value[0] );
			$this->saturation = floatval( $value[1] );
			$this->lightness  = floatval( $value[2] );
			$this->from_hsl_array();
			$this->set_closest_word_color_match();
		}

		/**
		 * Starts with an HSLA color and calculates all other properties.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return void
		 */
		protected function from_hsla() {
			$value = explode( ',', str_replace( array( ' ', 'hsla', '(', ')', '%' ), '', $this->color ) );
			$this->hue        = floatval( $value[0] );
			$this->saturation = floatval( $value[1] );
			$this->lightness  = floatval( $value[2] );
			$this->alpha      = floatval( $value[3] );

			$value =  explode( ',', str_replace( array( ' ', 'hsla', '(', ')' ), '', $this->color ) );
			if ( false !== strpos( $value[3], '%' ) &&  false === strpos( $value[3], 'calc' ) ) {
				$this->alpha = $this->alpha / 100;
			}

			$this->from_hsl_array();
			$this->set_closest_word_color_match();
		}

		/**
		 * Generates the HEX value of a color given values for $red, $green, $blue.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @param int|string $red   The red value of this color.
		 * @param int|string $green The green value of this color.
		 * @param int|string $blue  The blue value of this color.
		 * @return string
		 */
		protected function rgb_to_hex( $red, $green, $blue ) {
			// Get hex values properly formatted.
			$hex_red   = $this->dexhex_double_digit( $red );
			$hex_green = $this->dexhex_double_digit( $green );
			$hex_blue  = $this->dexhex_double_digit( $blue );
			return '#' . $hex_red . $hex_green . $hex_blue;
		}

		/**
		 * Convert a decimal value to hex and make sure it's 2 characters.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @param int|string $value The value to convert.
		 * @return string
		 */
		protected function dexhex_double_digit( $value ) {
			$value = dechex( $value );
			if ( 1 === strlen( $value ) ) {
				$value = '0' . $value;
			}
			return $value;
		}

		/**
		 * Calculates the red, green, blue values of an HSL color.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @see https://gist.github.com/brandonheyer/5254516
		 */
		protected function from_hsl_array() {
			$h = $this->hue / 360;
			$s = $this->saturation / 100;
			$l = $this->lightness / 100;

			$r = $l;
			$g = $l;
			$b = $l;
			$v = ( $l <= 0.5 ) ? ( $l * ( 1.0 + $s ) ) : ( $l + $s - $l * $s );
			if ( $v > 0 ) {
				$m = $l + $l - $v;
				$sv = ( $v - $m ) / $v;
				$h *= 6.0;
				$sextant = floor( $h );
				$fract = $h - $sextant;
				$vsf = $v * $sv * $fract;
				$mid1 = $m + $vsf;
				$mid2 = $v - $vsf;
				switch ( $sextant ) {
					case 0:
						$r = $v;
						$g = $mid1;
						$b = $m;
						break;
					case 1:
						$r = $mid2;
						$g = $v;
						$b = $m;
						break;
					case 2:
						$r = $m;
						$g = $v;
						$b = $mid1;
						break;
					case 3:
						$r = $m;
						$g = $mid2;
						$b = $v;
						break;
					case 4:
						$r = $mid1;
						$g = $m;
						$b = $v;
						break;
					case 5:
						$r = $v;
						$g = $m;
						$b = $mid2;
						break;
				}
			}
			$this->red   = round( $r * 255, 0 );
			$this->green = round( $g * 255, 0 );
			$this->blue  = round( $b * 255, 0 );

			$this->hex = $this->rgb_to_hex( $this->red, $this->green, $this->blue );
			$this->set_luminance();
		}

		/**
		 * Returns a CSS-formatted value for colors.
		 *
		 * NOTE: Deprecated in favor of "to_css_var_or_rgba()" if the color can be a
		 * global variable and depending if where it is used supports it.
		 *
		 * @access public
		 * @since 1.0.0
		 * @param string $mode The mode we're using.
		 * @return string
		 */
		public function toCSS( $mode = 'hex' ) {

			$value = '';

			switch ( $mode ) {
				case 'hex':
					$value = strtolower( $this->hex );
					break;
				case 'rgba':
					$value = 'rgba(' . $this->red . ',' . $this->green . ',' . $this->blue . ',' . $this->alpha . ')';
					break;
				case 'rgb':
					$value = 'rgb(' . $this->red . ',' . $this->green . ',' . $this->blue . ')';
					break;
				case 'hsl':
					$value = 'hsl(' . $this->hue . ',' . round( $this->saturation ) . '%,' . round( $this->lightness ) . '%)';
					break;
				case 'hsla':
					$value = 'hsla(' . $this->hue . ',' . round( $this->saturation ) . '%,' . round( $this->lightness ) . '%,' . $this->alpha . ')';
					break;
			}
			return $value;
		}

		/**
		 * Alias for the toCSS method.
		 *
		 * NOTE: Deprecated in favor of "to_css_var_or_rgba()" if the color can be a
		 * global variable and depending if where it is used supports it.
		 *
		 * @access public
		 * @since 1.1
		 * @param string $mode The mode we're using.
		 * @return string
		 */
		public function to_css( $mode = 'hex' ) {
			return $this->toCSS( $mode );
		}

		/**
		 * Try to find a global CSS variable for the current color, or return a rgba value.
		 *
		 * @since 3.7
		 * @param string $mode The mode we're using.
		 * @return string
		 */
		public function to_css_var_or_rgba() {
			$css_value = 'rgba(' . $this->red . ',' . $this->green . ',' . $this->blue . ',' . $this->alpha . ')';

			if ( ! function_exists( 'AWB_Global_Colors' ) ) {
				return $css_value;
			}

			$color_slug = $this->global_color_slug;
			$palettes  = AWB_Global_Colors()->get_palette();
			if ( $color_slug && isset( $palettes[ $color_slug ], $palettes[ $color_slug ]['color'] ) ) {
				if ( ! is_string( $palettes[ $color_slug ]['color'] ) ) {
					return $css_value;
				}

				$hue_part = 'var(--awb-' . $color_slug . '-h)';
				$saturation_part = 'var(--awb-' . $color_slug . '-s)';
				$lightness_part = 'var(--awb-' . $color_slug . '-l)';
				$alpha_part = 'var(--awb-' . $color_slug . '-a)';

				$changed = false;
				$palette_color_obj = Fusion_Color::new_color( $palettes[ $color_slug ]['color'] );

				if ( $this->hue !== $palette_color_obj->hue ) {
					$difference = round( ( $palette_color_obj->hue - $this->hue ) );
					$sign = ( $difference > 0 ? ' - ' : ' + ' );

					$changed = true;
					$hue_part = 'calc(' . $hue_part . $sign . abs( $difference ) . ')';
				}

				if ( $this->saturation !== $palette_color_obj->saturation ) {
					$difference = round( ( $palette_color_obj->saturation - $this->saturation ) );
					$sign = ( $difference > 0 ? ' - ' : ' + ' );

					$changed = true;
					$saturation_part = 'calc(' . $saturation_part . $sign . abs( $difference ) . '%)';
				}

				if ( $this->lightness !== $palette_color_obj->lightness ) {
					$difference = round( ( $palette_color_obj->lightness - $this->lightness ) );
					$sign = ( $difference > 0 ? ' - ' : ' + ' );

					$changed = true;
					$lightness_part = 'calc(' . $lightness_part . $sign . abs( $difference ) . '%)';
				}

				if ( $this->alpha !== $palette_color_obj->alpha ) {
					$difference = round( ( $palette_color_obj->alpha - $this->alpha ) * 100 );
					$sign = ( $difference > 0 ? ' - ' : ' + ' );

					$changed = true;
					$alpha_part = 'calc(' . $alpha_part . $sign . abs( $difference ) . '%)';
				}

				if ( $changed ) {
					$css_value = 'hsla(' . $hue_part . ',' . $saturation_part . ',' . $lightness_part . ',' . $alpha_part . ')';
				} else {
					$css_value = 'var(--awb-' . $color_slug . ')';
				}
			}

			return $css_value;
		}

		/**
		 * Sets the HSL values of a color based on the values of red, green, blue.
		 *
		 * @access public
		 * @since 1.0.0
		 */
		protected function set_hsl() {
			$red   = $this->red / 255;
			$green = $this->green / 255;
			$blue  = $this->blue / 255;

			$max = max( $red, $green, $blue );
			$min = min( $red, $green, $blue );

			$lightness  = ( $max + $min ) / 2;
			$difference = $max - $min;

			if ( ! $difference ) {
				$hue = $saturation = 0; // Achromatic.
			} else {
				$saturation = $difference / ( 1 - abs( 2 * $lightness - 1 ) );
				switch ( $max ) {
					case $red:
						$hue = 60 * fmod( ( ( $green - $blue ) / $difference ), 6 );
						if ( $blue > $green ) {
							$hue += 360;
						}
						break;
					case $green:
						$hue = 60 * ( ( $blue - $red ) / $difference + 2 );
						break;
					case $blue:
						$hue = 60 * ( ( $red - $green ) / $difference + 4 );
						break;
				}
			}

			$this->hue        = round( $hue );
			$this->saturation = round( $saturation * 100 );
			$this->lightness  = round( $lightness * 100 );
		}

		/**
		 * Sets the brightness of a color based on the values of red, green, blue.
		 *
		 * @access protected
		 * @since 1.0.0
		 */
		protected function set_brightness() {
			$this->brightness = array(
				'red'   => round( $this->red * .299 ),
				'green' => round( $this->green * .587 ),
				'blue'  => round( $this->blue * .114 ),
				'total' => intval( ( $this->red * .299 ) + ( $this->green * .587 ) + ( $this->blue * .114 ) ),
				'level' => $this->calc_color_brightness(),
			);
		}

		/**
		 * Sets the luminance of a color (range:0-255) based on the values of red, green, blue.
		 *
		 * @access protected
		 * @since 1.0.0
		 */
		protected function set_luminance() {
			$lum = ( 0.2126 * $this->red ) + ( 0.7152 * $this->green ) + ( 0.0722 * $this->blue );
			$this->luminance = round( $lum );
		}

		/**
		 * Gets an array of all the wordcolors.
		 *
		 * @access protected
		 * @since 1.0.0
		 * @return array
		 */
		protected function get_word_colors() {
			return array(
				'aliceblue'            => 'F0F8FF',
				'antiquewhite'         => 'FAEBD7',
				'aqua'                 => '00FFFF',
				'aquamarine'           => '7FFFD4',
				'azure'                => 'F0FFFF',
				'beige'                => 'F5F5DC',
				'bisque'               => 'FFE4C4',
				'black'                => '000000',
				'blanchedalmond'       => 'FFEBCD',
				'blue'                 => '0000FF',
				'blueviolet'           => '8A2BE2',
				'brown'                => 'A52A2A',
				'burlywood'            => 'DEB887',
				'cadetblue'            => '5F9EA0',
				'chartreuse'           => '7FFF00',
				'chocolate'            => 'D2691E',
				'coral'                => 'FF7F50',
				'cornflowerblue'       => '6495ED',
				'cornsilk'             => 'FFF8DC',
				'crimson'              => 'DC143C',
				'cyan'                 => '00FFFF',
				'darkblue'             => '00008B',
				'darkcyan'             => '008B8B',
				'darkgoldenrod'        => 'B8860B',
				'darkgray'             => 'A9A9A9',
				'darkgreen'            => '006400',
				'darkgrey'             => 'A9A9A9',
				'darkkhaki'            => 'BDB76B',
				'darkmagenta'          => '8B008B',
				'darkolivegreen'       => '556B2F',
				'darkorange'           => 'FF8C00',
				'darkorchid'           => '9932CC',
				'darkred'              => '8B0000',
				'darksalmon'           => 'E9967A',
				'darkseagreen'         => '8FBC8F',
				'darkslateblue'        => '483D8B',
				'darkslategray'        => '2F4F4F',
				'darkslategrey'        => '2F4F4F',
				'darkturquoise'        => '00CED1',
				'darkviolet'           => '9400D3',
				'deeppink'             => 'FF1493',
				'deepskyblue'          => '00BFFF',
				'dimgray'              => '696969',
				'dimgrey'              => '696969',
				'dodgerblue'           => '1E90FF',
				'firebrick'            => 'B22222',
				'floralwhite'          => 'FFFAF0',
				'forestgreen'          => '228B22',
				'fuchsia'              => 'FF00FF',
				'gainsboro'            => 'DCDCDC',
				'ghostwhite'           => 'F8F8FF',
				'gold'                 => 'FFD700',
				'goldenrod'            => 'DAA520',
				'gray'                 => '808080',
				'green'                => '008000',
				'greenyellow'          => 'ADFF2F',
				'grey'                 => '808080',
				'honeydew'             => 'F0FFF0',
				'hotpink'              => 'FF69B4',
				'indianred'            => 'CD5C5C',
				'indigo'               => '4B0082',
				'ivory'                => 'FFFFF0',
				'khaki'                => 'F0E68C',
				'lavender'             => 'E6E6FA',
				'lavenderblush'        => 'FFF0F5',
				'lawngreen'            => '7CFC00',
				'lemonchiffon'         => 'FFFACD',
				'lightblue'            => 'ADD8E6',
				'lightcoral'           => 'F08080',
				'lightcyan'            => 'E0FFFF',
				'lightgoldenrodyellow' => 'FAFAD2',
				'lightgray'            => 'D3D3D3',
				'lightgreen'           => '90EE90',
				'lightgrey'            => 'D3D3D3',
				'lightpink'            => 'FFB6C1',
				'lightsalmon'          => 'FFA07A',
				'lightseagreen'        => '20B2AA',
				'lightskyblue'         => '87CEFA',
				'lightslategray'       => '778899',
				'lightslategrey'       => '778899',
				'lightsteelblue'       => 'B0C4DE',
				'lightyellow'          => 'FFFFE0',
				'lime'                 => '00FF00',
				'limegreen'            => '32CD32',
				'linen'                => 'FAF0E6',
				'magenta'              => 'FF00FF',
				'maroon'               => '800000',
				'mediumaquamarine'     => '66CDAA',
				'mediumblue'           => '0000CD',
				'mediumorchid'         => 'BA55D3',
				'mediumpurple'         => '9370D0',
				'mediumseagreen'       => '3CB371',
				'mediumslateblue'      => '7B68EE',
				'mediumspringgreen'    => '00FA9A',
				'mediumturquoise'      => '48D1CC',
				'mediumvioletred'      => 'C71585',
				'midnightblue'         => '191970',
				'mintcream'            => 'F5FFFA',
				'mistyrose'            => 'FFE4E1',
				'moccasin'             => 'FFE4B5',
				'navajowhite'          => 'FFDEAD',
				'navy'                 => '000080',
				'oldlace'              => 'FDF5E6',
				'olive'                => '808000',
				'olivedrab'            => '6B8E23',
				'orange'               => 'FFA500',
				'orangered'            => 'FF4500',
				'orchid'               => 'DA70D6',
				'palegoldenrod'        => 'EEE8AA',
				'palegreen'            => '98FB98',
				'paleturquoise'        => 'AFEEEE',
				'palevioletred'        => 'DB7093',
				'papayawhip'           => 'FFEFD5',
				'peachpuff'            => 'FFDAB9',
				'peru'                 => 'CD853F',
				'pink'                 => 'FFC0CB',
				'plum'                 => 'DDA0DD',
				'powderblue'           => 'B0E0E6',
				'purple'               => '800080',
				'red'                  => 'FF0000',
				'rosybrown'            => 'BC8F8F',
				'royalblue'            => '4169E1',
				'saddlebrown'          => '8B4513',
				'salmon'               => 'FA8072',
				'sandybrown'           => 'F4A460',
				'seagreen'             => '2E8B57',
				'seashell'             => 'FFF5EE',
				'sienna'               => 'A0522D',
				'silver'               => 'C0C0C0',
				'skyblue'              => '87CEEB',
				'slateblue'            => '6A5ACD',
				'slategray'            => '708090',
				'slategrey'            => '708090',
				'snow'                 => 'FFFAFA',
				'springgreen'          => '00FF7F',
				'steelblue'            => '4682B4',
				'tan'                  => 'D2B48C',
				'teal'                 => '008080',
				'thistle'              => 'D8BFD8',
				'tomato'               => 'FF6347',
				'turquoise'            => '40E0D0',
				'violet'               => 'EE82EE',
				'wheat'                => 'F5DEB3',
				'white'                => 'FFFFFF',
				'whitesmoke'           => 'F5F5F5',
				'yellow'               => 'FFFF00',
				'yellowgreen'          => '9ACD32',
			);
		}

		/**
		 * Gets an array of all the word colors with their HEX, RGB and HSL values.
		 *
		 * @static
		 * @access public
		 * @since 3.1
		 * @return array The array of all word colors and their values.
		 */
		public static function get_word_colors_with_values( $extended = false ) {
			$base_colors = [
				'aqua' => [
					'hex' => '00FFFF',
					'rgb' => [ 'red' => '0', 'green' => '255', 'blue' => '255' ],
					'hsl' => [ 'hue' => '180', 'saturation' => '100', 'lightness' => '50' ],
				],
				'black' => [
					'hex' => '000000',
					'rgb' => [ 'red' => '0', 'green' => '0', 'blue' => '0' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '0' ],
				],
				'blue' => [
					'hex' => '0000FF',
					'rgb' => [ 'red' => '0', 'green' => '0', 'blue' => '255' ],
					'hsl' => [ 'hue' => '240', 'saturation' => '100', 'lightness' => '50' ],
				],
				'brown' => [
					'hex' => 'A52A2A',
					'rgb' => [ 'red' => '165', 'green' => '42', 'blue' => '42' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '59', 'lightness' => '41' ],
				],
				'cyan' => [
					'hex' => '00FFFF',
					'rgb' => [ 'red' => '0', 'green' => '255', 'blue' => '255' ],
					'hsl' => [ 'hue' => '180', 'saturation' => '100', 'lightness' => '50' ],
				],
				'gray' => [
					'hex' => '808080',
					'rgb' => [ 'red' => '128', 'green' => '128', 'blue' => '128' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '50' ],
				],
				'green' => [
					'hex' => '008000',
					'rgb' => [ 'red' => '0', 'green' => '128', 'blue' => '0' ],
					'hsl' => [ 'hue' => '120', 'saturation' => '100', 'lightness' => '25' ],
				],
				'grey' => [
					'hex' => '808080',
					'rgb' => [ 'red' => '128', 'green' => '128', 'blue' => '128' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '50' ],
				],
				'indigo' => [
					'hex' => '4B0082',
					'rgb' => [ 'red' => '75', 'green' => '0', 'blue' => '130' ],
					'hsl' => [ 'hue' => '275', 'saturation' => '100', 'lightness' => '25' ],
				],
				'lightgray' => [
					'hex' => 'D3D3D3',
					'rgb' => [ 'red' => '211', 'green' => '211', 'blue' => '211' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '83' ],
				],
				'lime' => [
					'hex' => '00FF00',
					'rgb' => [ 'red' => '0', 'green' => '255', 'blue' => '0' ],
					'hsl' => [ 'hue' => '120', 'saturation' => '100', 'lightness' => '50' ],
				],
				'magenta' => [
					'hex' => 'FF00FF',
					'rgb' => [ 'red' => '255', 'green' => '0', 'blue' => '255' ],
					'hsl' => [ 'hue' => '300', 'saturation' => '100', 'lightness' => '50' ],
				],
				'navy' => [
					'hex' => '000080',
					'rgb' => [ 'red' => '0', 'green' => '0', 'blue' => '128' ],
					'hsl' => [ 'hue' => '240', 'saturation' => '100', 'lightness' => '25' ],
				],
				'olive' => [
					'hex' => '808000',
					'rgb' => [ 'red' => '128', 'green' => '128', 'blue' => '0' ],
					'hsl' => [ 'hue' => '60', 'saturation' => '100', 'lightness' => '25' ],
				],
				'orange' => [
					'hex' => 'FFA500',
					'rgb' => [ 'red' => '255', 'green' => '165', 'blue' => '0' ],
					'hsl' => [ 'hue' => '39', 'saturation' => '100', 'lightness' => '50' ],
				],
				'pink' => [
					'hex' => 'FFC0CB',
					'rgb' => [ 'red' => '255', 'green' => '192', 'blue' => '203' ],
					'hsl' => [ 'hue' => '350', 'saturation' => '100', 'lightness' => '88' ],
				],
				'purple' => [
					'hex' => '800080',
					'rgb' => [ 'red' => '128', 'green' => '0', 'blue' => '128' ],
					'hsl' => [ 'hue' => '300', 'saturation' => '100', 'lightness' => '25' ],
				],
				'red' => [
					'hex' => 'FF0000',
					'rgb' => [ 'red' => '255', 'green' => '0', 'blue' => '0' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '100', 'lightness' => '50' ],
				],
				'silver' => [
					'hex' => 'C0C0C0',
					'rgb' => [ 'red' => '192', 'green' => '192', 'blue' => '192' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '75' ],
				],
				'violet' => [
					'hex' => 'EE82EE',
					'rgb' => [ 'red' => '238', 'green' => '130', 'blue' => '238' ],
					'hsl' => [ 'hue' => '300', 'saturation' => '76', 'lightness' => '72' ],
				],
				'white' => [
					'hex' => 'FFFFFF',
					'rgb' => [ 'red' => '255', 'green' => '255', 'blue' => '255' ],
					'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '100' ],
				],
				'yellow' => [
					'hex' => 'FFFF00',
					'rgb' => [ 'red' => '255', 'green' => '255', 'blue' => '0' ],
					'hsl' => [ 'hue' => '60', 'saturation' => '100', 'lightness' => '50' ],
				],
			];

			if ( $extended ) {

				$extended_colors = [
					'aliceblue' => [
						'hex' => 'F0F8FF',
						'rgb' => [ 'red' => '240', 'green' => '248', 'blue' => '255' ],
						'hsl' => [ 'hue' => '208', 'saturation' => '100', 'lightness' => '97' ],
					],
					'antiquewhite' => [
						'hex' => 'FAEBD7',
						'rgb' => [ 'red' => '250', 'green' => '235', 'blue' => '215' ],
						'hsl' => [ 'hue' => '34', 'saturation' => '78', 'lightness' => '91' ],
					],
					'aquamarine' => [
						'hex' => '7FFFD4',
						'rgb' => [ 'red' => '127', 'green' => '255', 'blue' => '212' ],
						'hsl' => [ 'hue' => '160', 'saturation' => '100', 'lightness' => '75' ],
					],
					'azure' => [
						'hex' => 'F0FFFF',
						'rgb' => [ 'red' => '240', 'green' => '255', 'blue' => '255' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '100', 'lightness' => '97' ],
					],
					'beige' => [
						'hex' => 'F5F5DC',
						'rgb' => [ 'red' => '245', 'green' => '245', 'blue' => '220' ],
						'hsl' => [ 'hue' => '60', 'saturation' => '56', 'lightness' => '91' ],
					],
					'bisque' => [
						'hex' => 'FFE4C4',
						'rgb' => [ 'red' => '255', 'green' => '228', 'blue' => '196' ],
						'hsl' => [ 'hue' => '33', 'saturation' => '100', 'lightness' => '88' ],
					],
					'blanchedalmond' => [
						'hex' => 'FFEBCD',
						'rgb' => [ 'red' => '255', 'green' => '235', 'blue' => '205' ],
						'hsl' => [ 'hue' => '36', 'saturation' => '100', 'lightness' => '90' ],
					],
					'blueviolet' => [
						'hex' => '8A2BE2',
						'rgb' => [ 'red' => '138', 'green' => '43', 'blue' => '226' ],
						'hsl' => [ 'hue' => '271', 'saturation' => '76', 'lightness' => '53' ],
					],
					'burlywood' => [
						'hex' => 'DEB887',
						'rgb' => [ 'red' => '222', 'green' => '184', 'blue' => '135' ],
						'hsl' => [ 'hue' => '34', 'saturation' => '57', 'lightness' => '70' ],
					],
					'cadetblue' => [
						'hex' => '5F9EA0',
						'rgb' => [ 'red' => '95', 'green' => '158', 'blue' => '160' ],
						'hsl' => [ 'hue' => '182', 'saturation' => '25', 'lightness' => '50' ],
					],
					'chartreuse' => [
						'hex' => '7FFF00',
						'rgb' => [ 'red' => '127', 'green' => '255', 'blue' => '0' ],
						'hsl' => [ 'hue' => '90', 'saturation' => '100', 'lightness' => '50' ],
					],
					'chocolate' => [
						'hex' => 'D2691E',
						'rgb' => [ 'red' => '210', 'green' => '105', 'blue' => '30' ],
						'hsl' => [ 'hue' => '25', 'saturation' => '75', 'lightness' => '47' ],
					],
					'coral' => [
						'hex' => 'FF7F50',
						'rgb' => [ 'red' => '255', 'green' => '127', 'blue' => '80' ],
						'hsl' => [ 'hue' => '16', 'saturation' => '100', 'lightness' => '66' ],
					],
					'cornflowerblue' => [
						'hex' => '6495ED',
						'rgb' => [ 'red' => '100', 'green' => '149', 'blue' => '237' ],
						'hsl' => [ 'hue' => '219', 'saturation' => '79', 'lightness' => '66' ],
					],
					'cornsilk' => [
						'hex' => 'FFF8DC',
						'rgb' => [ 'red' => '255', 'green' => '248', 'blue' => '220' ],
						'hsl' => [ 'hue' => '48', 'saturation' => '100', 'lightness' => '93' ],
					],
					'crimson' => [
						'hex' => 'DC143C',
						'rgb' => [ 'red' => '220', 'green' => '20', 'blue' => '60' ],
						'hsl' => [ 'hue' => '348', 'saturation' => '83', 'lightness' => '47' ],
					],
					'darkblue' => [
						'hex' => '00008B',
						'rgb' => [ 'red' => '0', 'green' => '0', 'blue' => '139' ],
						'hsl' => [ 'hue' => '240', 'saturation' => '100', 'lightness' => '27' ],
					],
					'darkcyan' => [
						'hex' => '008B8B',
						'rgb' => [ 'red' => '0', 'green' => '139', 'blue' => '139' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '100', 'lightness' => '27' ],
					],
					'darkgoldenrod' => [
						'hex' => 'B8860B',
						'rgb' => [ 'red' => '184', 'green' => '134', 'blue' => '11' ],
						'hsl' => [ 'hue' => '43', 'saturation' => '89', 'lightness' => '38' ],
					],
					'darkgray' => [
						'hex' => 'A9A9A9',
						'rgb' => [ 'red' => '169', 'green' => '169', 'blue' => '169' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '66' ],
					],
					'darkgreen' => [
						'hex' => '006400',
						'rgb' => [ 'red' => '0', 'green' => '100', 'blue' => '0' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '100', 'lightness' => '20' ],
					],
					'darkgrey' => [
						'hex' => 'A9A9A9',
						'rgb' => [ 'red' => '169', 'green' => '169', 'blue' => '169' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '66' ],
					],
					'darkkhaki' => [
						'hex' => 'BDB76B',
						'rgb' => [ 'red' => '189', 'green' => '183', 'blue' => '107' ],
						'hsl' => [ 'hue' => '56', 'saturation' => '38', 'lightness' => '58' ],
					],
					'darkmagenta' => [
						'hex' => '8B008B',
						'rgb' => [ 'red' => '139', 'green' => '0', 'blue' => '139' ],
						'hsl' => [ 'hue' => '300', 'saturation' => '100', 'lightness' => '27' ],
					],
					'darkolivegreen' => [
						'hex' => '556B2F',
						'rgb' => [ 'red' => '85', 'green' => '107', 'blue' => '47' ],
						'hsl' => [ 'hue' => '82', 'saturation' => '39', 'lightness' => '30' ],
					],
					'darkorange' => [
						'hex' => 'FF8C00',
						'rgb' => [ 'red' => '255', 'green' => '140', 'blue' => '0' ],
						'hsl' => [ 'hue' => '33', 'saturation' => '100', 'lightness' => '50' ],
					],
					'darkorchid' => [
						'hex' => '9932CC',
						'rgb' => [ 'red' => '153', 'green' => '50', 'blue' => '204' ],
						'hsl' => [ 'hue' => '280', 'saturation' => '61', 'lightness' => '50' ],
					],
					'darkred' => [
						'hex' => '8B0000',
						'rgb' => [ 'red' => '139', 'green' => '0', 'blue' => '0' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '100', 'lightness' => '27' ],
					],
					'darksalmon' => [
						'hex' => 'E9967A',
						'rgb' => [ 'red' => '233', 'green' => '150', 'blue' => '122' ],
						'hsl' => [ 'hue' => '15', 'saturation' => '72', 'lightness' => '70' ],
					],
					'darkseagreen' => [
						'hex' => '8FBC8F',
						'rgb' => [ 'red' => '143', 'green' => '188', 'blue' => '143' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '25', 'lightness' => '65' ],
					],
					'darkslateblue' => [
						'hex' => '483D8B',
						'rgb' => [ 'red' => '72', 'green' => '61', 'blue' => '139' ],
						'hsl' => [ 'hue' => '248', 'saturation' => '39', 'lightness' => '39' ],
					],
					'darkslategray' => [
						'hex' => '2F4F4F',
						'rgb' => [ 'red' => '47', 'green' => '79', 'blue' => '79' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '25', 'lightness' => '25' ],
					],
					'darkslategrey' => [
						'hex' => '2F4F4F',
						'rgb' => [ 'red' => '47', 'green' => '79', 'blue' => '79' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '25', 'lightness' => '25' ],
					],
					'darkturquoise' => [
						'hex' => '00CED1',
						'rgb' => [ 'red' => '0', 'green' => '206', 'blue' => '209' ],
						'hsl' => [ 'hue' => '181', 'saturation' => '100', 'lightness' => '41' ],
					],
					'darkviolet' => [
						'hex' => '9400D3',
						'rgb' => [ 'red' => '148', 'green' => '0', 'blue' => '211' ],
						'hsl' => [ 'hue' => '282', 'saturation' => '100', 'lightness' => '41' ],
					],
					'deeppink' => [
						'hex' => 'FF1493',
						'rgb' => [ 'red' => '255', 'green' => '20', 'blue' => '147' ],
						'hsl' => [ 'hue' => '328', 'saturation' => '100', 'lightness' => '54' ],
					],
					'deepskyblue' => [
						'hex' => '00BFFF',
						'rgb' => [ 'red' => '0', 'green' => '191', 'blue' => '255' ],
						'hsl' => [ 'hue' => '195', 'saturation' => '100', 'lightness' => '50' ],
					],
					'dimgray' => [
						'hex' => '696969',
						'rgb' => [ 'red' => '105', 'green' => '105', 'blue' => '105' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '41' ],
					],
					'dimgrey' => [
						'hex' => '696969',
						'rgb' => [ 'red' => '105', 'green' => '105', 'blue' => '105' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '41' ],
					],
					'dodgerblue' => [
						'hex' => '1E90FF',
						'rgb' => [ 'red' => '30', 'green' => '144', 'blue' => '255' ],
						'hsl' => [ 'hue' => '210', 'saturation' => '100', 'lightness' => '56' ],
					],
					'firebrick' => [
						'hex' => 'B22222',
						'rgb' => [ 'red' => '178', 'green' => '34', 'blue' => '34' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '68', 'lightness' => '42' ],
					],
					'floralwhite' => [
						'hex' => 'FFFAF0',
						'rgb' => [ 'red' => '255', 'green' => '250', 'blue' => '240' ],
						'hsl' => [ 'hue' => '40', 'saturation' => '100', 'lightness' => '97' ],
					],
					'forestgreen' => [
						'hex' => '228B22',
						'rgb' => [ 'red' => '34', 'green' => '139', 'blue' => '34' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '61', 'lightness' => '34' ],
					],
					'fuchsia' => [
						'hex' => 'FF00FF',
						'rgb' => [ 'red' => '255', 'green' => '0', 'blue' => '255' ],
						'hsl' => [ 'hue' => '300', 'saturation' => '100', 'lightness' => '50' ],
					],
					'gainsboro' => [
						'hex' => 'DCDCDC',
						'rgb' => [ 'red' => '220', 'green' => '220', 'blue' => '220' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '86' ],
					],
					'ghostwhite' => [
						'hex' => 'F8F8FF',
						'rgb' => [ 'red' => '248', 'green' => '248', 'blue' => '255' ],
						'hsl' => [ 'hue' => '240', 'saturation' => '100', 'lightness' => '99' ],
					],
					'gold' => [
						'hex' => 'FFD700',
						'rgb' => [ 'red' => '255', 'green' => '215', 'blue' => '0' ],
						'hsl' => [ 'hue' => '51', 'saturation' => '100', 'lightness' => '50' ],
					],
					'goldenrod' => [
						'hex' => 'DAA520',
						'rgb' => [ 'red' => '218', 'green' => '165', 'blue' => '32' ],
						'hsl' => [ 'hue' => '43', 'saturation' => '74', 'lightness' => '49' ],
					],
					'greenyellow' => [
						'hex' => 'ADFF2F',
						'rgb' => [ 'red' => '173', 'green' => '255', 'blue' => '47' ],
						'hsl' => [ 'hue' => '84', 'saturation' => '100', 'lightness' => '59' ],
					],
					'honeydew' => [
						'hex' => 'F0FFF0',
						'rgb' => [ 'red' => '240', 'green' => '255', 'blue' => '240' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '100', 'lightness' => '97' ],
					],
					'hotpink' => [
						'hex' => 'FF69B4',
						'rgb' => [ 'red' => '255', 'green' => '105', 'blue' => '180' ],
						'hsl' => [ 'hue' => '330', 'saturation' => '100', 'lightness' => '71' ],
					],
					'indianred' => [
						'hex' => 'CD5C5C',
						'rgb' => [ 'red' => '205', 'green' => '92', 'blue' => '92' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '53', 'lightness' => '58' ],
					],
					'ivory' => [
						'hex' => 'FFFFF0',
						'rgb' => [ 'red' => '255', 'green' => '255', 'blue' => '240' ],
						'hsl' => [ 'hue' => '60', 'saturation' => '100', 'lightness' => '97' ],
					],
					'khaki' => [
						'hex' => 'F0E68C',
						'rgb' => [ 'red' => '240', 'green' => '230', 'blue' => '140' ],
						'hsl' => [ 'hue' => '54', 'saturation' => '77', 'lightness' => '75' ],
					],
					'lavender' => [
						'hex' => 'E6E6FA',
						'rgb' => [ 'red' => '230', 'green' => '230', 'blue' => '250' ],
						'hsl' => [ 'hue' => '240', 'saturation' => '67', 'lightness' => '94' ],
					],
					'lavenderblush' => [
						'hex' => 'FFF0F5',
						'rgb' => [ 'red' => '255', 'green' => '240', 'blue' => '245' ],
						'hsl' => [ 'hue' => '340', 'saturation' => '100', 'lightness' => '97' ],
					],
					'lawngreen' => [
						'hex' => '7CFC00',
						'rgb' => [ 'red' => '124', 'green' => '252', 'blue' => '0' ],
						'hsl' => [ 'hue' => '90', 'saturation' => '100', 'lightness' => '49' ],
					],
					'lemonchiffon' => [
						'hex' => 'FFFACD',
						'rgb' => [ 'red' => '255', 'green' => '250', 'blue' => '205' ],
						'hsl' => [ 'hue' => '54', 'saturation' => '100', 'lightness' => '90' ],
					],
					'lightblue' => [
						'hex' => 'ADD8E6',
						'rgb' => [ 'red' => '173', 'green' => '216', 'blue' => '230' ],
						'hsl' => [ 'hue' => '195', 'saturation' => '53', 'lightness' => '79' ],
					],
					'lightcoral' => [
						'hex' => 'F08080',
						'rgb' => [ 'red' => '240', 'green' => '128', 'blue' => '128' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '79', 'lightness' => '72' ],
					],
					'lightcyan' => [
						'hex' => 'E0FFFF',
						'rgb' => [ 'red' => '224', 'green' => '255', 'blue' => '255' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '100', 'lightness' => '94' ],
					],
					'lightgoldenrodyellow' => [
						'hex' => 'FAFAD2',
						'rgb' => [ 'red' => '250', 'green' => '250', 'blue' => '210' ],
						'hsl' => [ 'hue' => '60', 'saturation' => '80', 'lightness' => '90' ],
					],
					'lightgreen' => [
						'hex' => '90EE90',
						'rgb' => [ 'red' => '144', 'green' => '238', 'blue' => '144' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '73', 'lightness' => '75' ],
					],
					'lightgrey' => [
						'hex' => 'D3D3D3',
						'rgb' => [ 'red' => '211', 'green' => '211', 'blue' => '211' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '83' ],
					],
					'lightpink' => [
						'hex' => 'FFB6C1',
						'rgb' => [ 'red' => '255', 'green' => '182', 'blue' => '193' ],
						'hsl' => [ 'hue' => '351', 'saturation' => '100', 'lightness' => '86' ],
					],
					'lightsalmon' => [
						'hex' => 'FFA07A',
						'rgb' => [ 'red' => '255', 'green' => '160', 'blue' => '122' ],
						'hsl' => [ 'hue' => '17', 'saturation' => '100', 'lightness' => '74' ],
					],
					'lightseagreen' => [
						'hex' => '20B2AA',
						'rgb' => [ 'red' => '32', 'green' => '178', 'blue' => '170' ],
						'hsl' => [ 'hue' => '177', 'saturation' => '70', 'lightness' => '41' ],
					],
					'lightskyblue' => [
						'hex' => '87CEFA',
						'rgb' => [ 'red' => '135', 'green' => '206', 'blue' => '250' ],
						'hsl' => [ 'hue' => '203', 'saturation' => '92', 'lightness' => '75' ],
					],
					'lightslategray' => [
						'hex' => '778899',
						'rgb' => [ 'red' => '119', 'green' => '136', 'blue' => '153' ],
						'hsl' => [ 'hue' => '210', 'saturation' => '14', 'lightness' => '53' ],
					],
					'lightslategrey' => [
						'hex' => '778899',
						'rgb' => [ 'red' => '119', 'green' => '136', 'blue' => '153' ],
						'hsl' => [ 'hue' => '210', 'saturation' => '14', 'lightness' => '53' ],
					],
					'lightsteelblue' => [
						'hex' => 'B0C4DE',
						'rgb' => [ 'red' => '176', 'green' => '196', 'blue' => '222' ],
						'hsl' => [ 'hue' => '214', 'saturation' => '41', 'lightness' => '78' ],
					],
					'lightyellow' => [
						'hex' => 'FFFFE0',
						'rgb' => [ 'red' => '255', 'green' => '255', 'blue' => '224' ],
						'hsl' => [ 'hue' => '60', 'saturation' => '100', 'lightness' => '94' ],
					],
					'limegreen' => [
						'hex' => '32CD32',
						'rgb' => [ 'red' => '50', 'green' => '205', 'blue' => '50' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '61', 'lightness' => '50' ],
					],
					'linen' => [
						'hex' => 'FAF0E6',
						'rgb' => [ 'red' => '250', 'green' => '240', 'blue' => '230' ],
						'hsl' => [ 'hue' => '30', 'saturation' => '67', 'lightness' => '94' ],
					],
					'maroon' => [
						'hex' => '800000',
						'rgb' => [ 'red' => '128', 'green' => '0', 'blue' => '0' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '100', 'lightness' => '25' ],
					],
					'mediumaquamarine' => [
						'hex' => '66CDAA',
						'rgb' => [ 'red' => '102', 'green' => '205', 'blue' => '170' ],
						'hsl' => [ 'hue' => '160', 'saturation' => '51', 'lightness' => '60' ],
					],
					'mediumblue' => [
						'hex' => '0000CD',
						'rgb' => [ 'red' => '0', 'green' => '0', 'blue' => '205' ],
						'hsl' => [ 'hue' => '240', 'saturation' => '100', 'lightness' => '40' ],
					],
					'mediumorchid' => [
						'hex' => 'BA55D3',
						'rgb' => [ 'red' => '186', 'green' => '85', 'blue' => '211' ],
						'hsl' => [ 'hue' => '288', 'saturation' => '59', 'lightness' => '58' ],
					],
					'mediumpurple' => [
						'hex' => '9370D0',
						'rgb' => [ 'red' => '147', 'green' => '112', 'blue' => '208' ],
						'hsl' => [ 'hue' => '262', 'saturation' => '51', 'lightness' => '63' ],
					],
					'mediumseagreen' => [
						'hex' => '3CB371',
						'rgb' => [ 'red' => '60', 'green' => '179', 'blue' => '113' ],
						'hsl' => [ 'hue' => '147', 'saturation' => '50', 'lightness' => '47' ],
					],
					'mediumslateblue' => [
						'hex' => '7B68EE',
						'rgb' => [ 'red' => '123', 'green' => '104', 'blue' => '238' ],
						'hsl' => [ 'hue' => '249', 'saturation' => '80', 'lightness' => '67' ],
					],
					'mediumspringgreen' => [
						'hex' => '00FA9A',
						'rgb' => [ 'red' => '0', 'green' => '250', 'blue' => '154' ],
						'hsl' => [ 'hue' => '157', 'saturation' => '100', 'lightness' => '49' ],
					],
					'mediumturquoise' => [
						'hex' => '48D1CC',
						'rgb' => [ 'red' => '72', 'green' => '209', 'blue' => '204' ],
						'hsl' => [ 'hue' => '178', 'saturation' => '60', 'lightness' => '55' ],
					],
					'mediumvioletred' => [
						'hex' => 'C71585',
						'rgb' => [ 'red' => '199', 'green' => '21', 'blue' => '133' ],
						'hsl' => [ 'hue' => '322', 'saturation' => '81', 'lightness' => '43' ],
					],
					'midnightblue' => [
						'hex' => '191970',
						'rgb' => [ 'red' => '25', 'green' => '25', 'blue' => '112' ],
						'hsl' => [ 'hue' => '240', 'saturation' => '64', 'lightness' => '27' ],
					],
					'mintcream' => [
						'hex' => 'F5FFFA',
						'rgb' => [ 'red' => '245', 'green' => '255', 'blue' => '250' ],
						'hsl' => [ 'hue' => '150', 'saturation' => '100', 'lightness' => '98' ],
					],
					'mistyrose' => [
						'hex' => 'FFE4E1',
						'rgb' => [ 'red' => '255', 'green' => '228', 'blue' => '225' ],
						'hsl' => [ 'hue' => '6', 'saturation' => '100', 'lightness' => '94' ],
					],
					'moccasin' => [
						'hex' => 'FFE4B5',
						'rgb' => [ 'red' => '255', 'green' => '228', 'blue' => '181' ],
						'hsl' => [ 'hue' => '38', 'saturation' => '100', 'lightness' => '85' ],
					],
					'navajowhite' => [
						'hex' => 'FFDEAD',
						'rgb' => [ 'red' => '255', 'green' => '222', 'blue' => '173' ],
						'hsl' => [ 'hue' => '36', 'saturation' => '100', 'lightness' => '84' ],
					],
					'oldlace' => [
						'hex' => 'FDF5E6',
						'rgb' => [ 'red' => '253', 'green' => '245', 'blue' => '230' ],
						'hsl' => [ 'hue' => '39', 'saturation' => '85', 'lightness' => '95' ],
					],
					'olivedrab' => [
						'hex' => '6B8E23',
						'rgb' => [ 'red' => '107', 'green' => '142', 'blue' => '35' ],
						'hsl' => [ 'hue' => '80', 'saturation' => '60', 'lightness' => '35' ],
					],
					'orangered' => [
						'hex' => 'FF4500',
						'rgb' => [ 'red' => '255', 'green' => '69', 'blue' => '0' ],
						'hsl' => [ 'hue' => '16', 'saturation' => '100', 'lightness' => '50' ],
					],
					'orchid' => [
						'hex' => 'DA70D6',
						'rgb' => [ 'red' => '218', 'green' => '112', 'blue' => '214' ],
						'hsl' => [ 'hue' => '302', 'saturation' => '59', 'lightness' => '65' ],
					],
					'palegoldenrod' => [
						'hex' => 'EEE8AA',
						'rgb' => [ 'red' => '238', 'green' => '232', 'blue' => '170' ],
						'hsl' => [ 'hue' => '55', 'saturation' => '67', 'lightness' => '80' ],
					],
					'palegreen' => [
						'hex' => '98FB98',
						'rgb' => [ 'red' => '152', 'green' => '251', 'blue' => '152' ],
						'hsl' => [ 'hue' => '120', 'saturation' => '93', 'lightness' => '79' ],
					],
					'paleturquoise' => [
						'hex' => 'AFEEEE',
						'rgb' => [ 'red' => '175', 'green' => '238', 'blue' => '238' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '65', 'lightness' => '81' ],
					],
					'palevioletred' => [
						'hex' => 'DB7093',
						'rgb' => [ 'red' => '219', 'green' => '112', 'blue' => '147' ],
						'hsl' => [ 'hue' => '340', 'saturation' => '60', 'lightness' => '65' ],
					],
					'papayawhip' => [
						'hex' => 'FFEFD5',
						'rgb' => [ 'red' => '255', 'green' => '239', 'blue' => '213' ],
						'hsl' => [ 'hue' => '37', 'saturation' => '100', 'lightness' => '92' ],
					],
					'peachpuff' => [
						'hex' => 'FFDAB9',
						'rgb' => [ 'red' => '255', 'green' => '218', 'blue' => '185' ],
						'hsl' => [ 'hue' => '28', 'saturation' => '100', 'lightness' => '86' ],
					],
					'peru' => [
						'hex' => 'CD853F',
						'rgb' => [ 'red' => '205', 'green' => '133', 'blue' => '63' ],
						'hsl' => [ 'hue' => '30', 'saturation' => '59', 'lightness' => '53' ],
					],
					'plum' => [
						'hex' => 'DDA0DD',
						'rgb' => [ 'red' => '221', 'green' => '160', 'blue' => '221' ],
						'hsl' => [ 'hue' => '300', 'saturation' => '47', 'lightness' => '75' ],
					],
					'powderblue' => [
						'hex' => 'B0E0E6',
						'rgb' => [ 'red' => '176', 'green' => '224', 'blue' => '230' ],
						'hsl' => [ 'hue' => '187', 'saturation' => '52', 'lightness' => '80' ],
					],
					'rosybrown' => [
						'hex' => 'BC8F8F',
						'rgb' => [ 'red' => '188', 'green' => '143', 'blue' => '143' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '25', 'lightness' => '65' ],
					],
					'royalblue' => [
						'hex' => '4169E1',
						'rgb' => [ 'red' => '65', 'green' => '105', 'blue' => '225' ],
						'hsl' => [ 'hue' => '225', 'saturation' => '73', 'lightness' => '57' ],
					],
					'saddlebrown' => [
						'hex' => '8B4513',
						'rgb' => [ 'red' => '139', 'green' => '69', 'blue' => '19' ],
						'hsl' => [ 'hue' => '25', 'saturation' => '76', 'lightness' => '31' ],
					],
					'salmon' => [
						'hex' => 'FA8072',
						'rgb' => [ 'red' => '250', 'green' => '128', 'blue' => '114' ],
						'hsl' => [ 'hue' => '6', 'saturation' => '93', 'lightness' => '71' ],
					],
					'sandybrown' => [
						'hex' => 'F4A460',
						'rgb' => [ 'red' => '244', 'green' => '164', 'blue' => '96' ],
						'hsl' => [ 'hue' => '28', 'saturation' => '87', 'lightness' => '67' ],
					],
					'seagreen' => [
						'hex' => '2E8B57',
						'rgb' => [ 'red' => '46', 'green' => '139', 'blue' => '87' ],
						'hsl' => [ 'hue' => '146', 'saturation' => '50', 'lightness' => '36' ],
					],
					'seashell' => [
						'hex' => 'FFF5EE',
						'rgb' => [ 'red' => '255', 'green' => '245', 'blue' => '238' ],
						'hsl' => [ 'hue' => '25', 'saturation' => '100', 'lightness' => '97' ],
					],
					'sienna' => [
						'hex' => 'A0522D',
						'rgb' => [ 'red' => '160', 'green' => '82', 'blue' => '45' ],
						'hsl' => [ 'hue' => '19', 'saturation' => '56', 'lightness' => '40' ],
					],
					'skyblue' => [
						'hex' => '87CEEB',
						'rgb' => [ 'red' => '135', 'green' => '206', 'blue' => '235' ],
						'hsl' => [ 'hue' => '197', 'saturation' => '71', 'lightness' => '73' ],
					],
					'slateblue' => [
						'hex' => '6A5ACD',
						'rgb' => [ 'red' => '106', 'green' => '90', 'blue' => '205' ],
						'hsl' => [ 'hue' => '248', 'saturation' => '53', 'lightness' => '58' ],
					],
					'slategray' => [
						'hex' => '708090',
						'rgb' => [ 'red' => '112', 'green' => '128', 'blue' => '144' ],
						'hsl' => [ 'hue' => '210', 'saturation' => '13', 'lightness' => '50' ],
					],
					'slategrey' => [
						'hex' => '708090',
						'rgb' => [ 'red' => '112', 'green' => '128', 'blue' => '144' ],
						'hsl' => [ 'hue' => '210', 'saturation' => '13', 'lightness' => '50' ],
					],
					'snow' => [
						'hex' => 'FFFAFA',
						'rgb' => [ 'red' => '255', 'green' => '250', 'blue' => '250' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '100', 'lightness' => '99' ],
					],
					'springgreen' => [
						'hex' => '00FF7F',
						'rgb' => [ 'red' => '0', 'green' => '255', 'blue' => '127' ],
						'hsl' => [ 'hue' => '150', 'saturation' => '100', 'lightness' => '50' ],
					],
					'steelblue' => [
						'hex' => '4682B4',
						'rgb' => [ 'red' => '70', 'green' => '130', 'blue' => '180' ],
						'hsl' => [ 'hue' => '207', 'saturation' => '44', 'lightness' => '49' ],
					],
					'tan' => [
						'hex' => 'D2B48C',
						'rgb' => [ 'red' => '210', 'green' => '180', 'blue' => '140' ],
						'hsl' => [ 'hue' => '34', 'saturation' => '44', 'lightness' => '69' ],
					],
					'teal' => [
						'hex' => '008080',
						'rgb' => [ 'red' => '0', 'green' => '128', 'blue' => '128' ],
						'hsl' => [ 'hue' => '180', 'saturation' => '100', 'lightness' => '25' ],
					],
					'thistle' => [
						'hex' => 'D8BFD8',
						'rgb' => [ 'red' => '216', 'green' => '191', 'blue' => '216' ],
						'hsl' => [ 'hue' => '300', 'saturation' => '24', 'lightness' => '80' ],
					],
					'tomato' => [
						'hex' => 'FF6347',
						'rgb' => [ 'red' => '255', 'green' => '99', 'blue' => '71' ],
						'hsl' => [ 'hue' => '9', 'saturation' => '100', 'lightness' => '64' ],
					],
					'turquoise' => [
						'hex' => '40E0D0',
						'rgb' => [ 'red' => '64', 'green' => '224', 'blue' => '208' ],
						'hsl' => [ 'hue' => '174', 'saturation' => '72', 'lightness' => '56' ],
					],
					'wheat' => [
						'hex' => 'F5DEB3',
						'rgb' => [ 'red' => '245', 'green' => '222', 'blue' => '179' ],
						'hsl' => [ 'hue' => '39', 'saturation' => '77', 'lightness' => '83' ],
					],
					'whitesmoke' => [
						'hex' => 'F5F5F5',
						'rgb' => [ 'red' => '245', 'green' => '245', 'blue' => '245' ],
						'hsl' => [ 'hue' => '0', 'saturation' => '0', 'lightness' => '96' ],
					],
					'yellowgreen' => [
						'hex' => '9ACD32',
						'rgb' => [ 'red' => '154', 'green' => '205', 'blue' => '50' ],
						'hsl' => [ 'hue' => '80', 'saturation' => '61', 'lightness' => '50' ],
					],
				];
			} else {
				$extended_colors = [];
			}

			$colors = array_merge( $base_colors, $extended_colors );

			return $colors;
		}

		/**
		 * Get closest word color match of a given color.
		 *
		 * @access protected
		 * @since 3.1
		 * @return void
		 */
		protected function set_closest_word_color_match() {
			$word_colors = self::get_word_colors_with_values();
			$rgb_diff    = $hsl_diff = $diff = 0;
			$total_diff  = -1;

			foreach( $word_colors as $word_color => $values ) {
				if ( $this->hex === '#' . $values['hex'] ) {
					$this->closets_word_color_match = $word_color;
					break;
				} else {
					$rgb_diff = sqrt( ( $this->red - $values['rgb']['red'] ) ** 2 + ( $this->green - $values['rgb']['green'] ) ** 2 + ( $this->blue - $values['rgb']['blue'] ) ** 2 );
					$hsl_diff = sqrt( ( $this->hue - $values['hsl']['hue'] ) ** 2 + ( $this->saturation - $values['hsl']['saturation'] ) ** 2 + ( $this->lightness - $values['hsl']['lightness'] ) ** 2 );
					$hsl_diff = 0;
					$diff = ( $rgb_diff + $hsl_diff ) / 2;

					if ( $total_diff < 0 || $total_diff > $diff ) {
					  $total_diff                     = $diff;
					  $this->closets_word_color_match = $word_color;
					}
				}
			}
		}

		/**
		 * Calculate the brightness of a color.
		 *
		 * @access protected
		 * @since 3.6.0
		 * @return integer brightness level.
		 */
		protected function calc_color_brightness() {
			$brightness_level = 150;
			if ( ! is_string( $this->color ) ) {
				return $brightness_level;
			}

			if ( in_array( strtolower( $this->color ), [ 'black', 'navy', 'purple', 'maroon', 'indigo', 'darkslategray', 'darkslateblue', 'darkolivegreen', 'darkgreen', 'darkblue' ], true ) ) {

				$brightness_level = 0;

			} elseif ( 0 === strpos( $this->color, '#' ) || 0 === strpos( $this->color, 'rgb' ) || ctype_xdigit( $this->color ) ) {

				$brightness_level = sqrt( pow( $this->red, 2 ) * 0.299 + pow( $this->green, 2 ) * 0.587 + pow( $this->blue, 2 ) * 0.114 );

			}

			return (int) round( $brightness_level );
		}

		/**
		 * Figure out if a color is transparent or not.
		 *
		 * @since 3.6.0
		 * @return bool
		 */
		public function is_color_transparent() {
			if ( 'transparent' === $this->color ) {
				return true;
			}
			return ( 0 === $this->alpha );
		}

		/**
		 * Adjusts brightness of the $hex and rgba colors.
		 *
		 * @since 3.6.0
		 * @param   int    $steps A value between -255 (darken) and 255 (lighten).
		 * @return  string        Returns hex color, rgba or fusion global variable, depending on input.
		 */
		public function adjust_brightness( $steps ) {
			$lightness = absint( round( $this->lightness + ( $steps / 2.55 ) ) );
			$lightness = max( 0, min( $lightness, 100 ) );
			return $this->get_new( 'lightness', $lightness )->to_css_var_or_rgba();
		}

		/**
		 * Determines if a color needs adjusting or not.
		 *
		 * @since 3.6.0
		 * @access public
		 * @return bool
		 */
		public function is_needs_adjustment() {
			if ( '#ffffff' === $this->color || $this->is_color_transparent() ) {
				return true;
			}

			return false;
		}

		/**
		 * Convert color to hsl.
		 *
		 * @since 3.6.0
		 * @access public
		 */
		public function to_hsl() {
			return [
				'hue' => $this->hue,
				'sat' => $this->saturation,
				'lum' => $this->lightness,
			];
		}

		/**
		 * Use fallback object.
		 *
		 * @access protected
		 * @since 1.2.0
		 */
		protected function from_fallback() {
			$this->color = $this->fallback;

			if ( ! $this->fallback_obj ) {
				$this->fallback_obj = self::newColor( $this->fallback );
			}
			$this->color      = $this->fallback_obj->color;
			$this->mode       = $this->fallback_obj->mode;
			$this->red        = $this->fallback_obj->red;
			$this->green      = $this->fallback_obj->green;
			$this->blue       = $this->fallback_obj->blue;
			$this->alpha      = $this->fallback_obj->alpha;
			$this->hue        = $this->fallback_obj->hue;
			$this->saturation = $this->fallback_obj->saturation;
			$this->lightness  = $this->fallback_obj->lightness;
			$this->luminance  = $this->fallback_obj->luminance;
			$this->hex        = $this->fallback_obj->hex;
		}

		/**
		 * Handle non-existing public methods.
		 *
		 * @access public
		 * @since 1.1.0
		 * @param string $name      The method name.
		 * @param mixed  $arguments The method arguments.
		 * @return mixed
		 */
		public function __call( $name, $arguments ) {
			if ( method_exists( $this, $name ) ) {
				call_user_func( array( $this, $name ), $arguments );
			} else {
				return $arguments;
			}
		}

		/**
		 * Handle non-existing public static methods.
		 *
		 * @static
		 * @access public
		 * @since 1.1.0
		 * @param string $name      The method name.
		 * @param mixed  $arguments The method arguments.
		 * @return mixed
		 */
		public static function __callStatic( $name, $arguments ) {
			if ( method_exists( __CLASS__, $name ) ) {
				call_user_func( array( __CLASS__, $name ), $arguments );
			} else {
				return $arguments;
			}
		}
	}
}
