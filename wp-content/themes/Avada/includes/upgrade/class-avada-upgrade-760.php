<?php
/**
 * Upgrades Handler.
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
 * Handle migrations for Avada 7.6
 *
 * @since 7.6
 */
class Avada_Upgrade_760 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.6
	 * @var string
	 */
	protected $version = '7.6.0';

	/**
	 * Whether to migrate colors.
	 *
	 * @access protected
	 * @since 7.6
	 * @var string
	 */
	private $migrate_colors = false;

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.6
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.6
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		if ( function_exists( 'AWB_Global_Colors' ) ) {
			$this->migrate_colors = true;
		} elseif ( file_exists( Avada::$template_dir_path . '/includes/class-awb-global-colors.php' ) ) {
			require_once Avada::$template_dir_path . '/includes/class-awb-global-colors.php';
			$this->migrate_colors = true;
		}
		$this->migrate_options();
	}

	/**
	 * Migrate options.
	 *
	 * @since 7.6
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->enable_avada_off_canvas( $options );
		$options = $this->migrate_social_links_colors( $options );
		$options = $this->migrate_toggles( $options );
		$options = $this->migrate_button_typo( $options );

		if ( $this->migrate_colors ) {
			$options = $this->migrate_color_palette( $options );
		}

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->enable_avada_off_canvas( $options );
			$options = $this->migrate_social_links_colors( $options );
			$options = $this->migrate_toggles( $options );
			$options = $this->migrate_button_typo( $options );

			if ( $this->migrate_colors ) {
				$options = $this->migrate_color_palette( $options );
			}

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Checks if color doesn't alpha set to 0.
	 *
	 * @access private
	 * @since 7.5
	 * @param array $color Color to check.
	 * @return boolean.
	 */
	public function alpha_zero( $color ) {

		// Remove spaces.
		$color = str_replace( ' ', '', $color );

		// Not rgba format.
		if ( false !== strpos( $color, 'rgba(' ) && false !== strpos( $color, ',0)' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all colors found in global options.
	 *
	 * @access public
	 * @since 7.6
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	public function get_color_values( $options ) {
		$values        = [];
		$options       = serialize( $options ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$regex_pattern = '/#([a-f0-9]{6}|[a-f0-9]{3})|rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*(\d*(?:\.\d+)?)\)|rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)/';

		// Get all the colors in the value.
		preg_match_all( $regex_pattern, $options, $matches );

		if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {
			foreach ( $matches[0] as $color ) {
				if ( false === $this->alpha_zero( $color ) ) {
					// Make it consistent rgba.
					$value = Fusion_Color::new_color( $color );
					$value = $value->toCss( 'rgba' );

					if ( isset( $values[ $value ] ) ) {
						$values[ $value ] += 1;
					} else {
						$values[ $value ] = 1;
					}
				}
			}
		}

		arsort( $values );

		return $values;
	}

	/**
	 * Color array for required data.
	 *
	 * @access public
	 * @since 7.6
	 * @param array $color_object The colors array.
	 * @return array
	 */
	public function create_color_array( $color_object ) {
		return [
			'color'     => $color_object->toCss( 'rgba' ),
			'luminance' => $color_object->luminance,
			'alpha'     => $color_object->alpha,
			'hsl'       => $color_object->toCss( 'hsl' ),
			'hs'        => $color_object->hue . $color_object->saturation,
			'hl'        => $color_object->hue . $color_object->lightness,
			'sl'        => $color_object->saturation . $color_object->lightness,
		];
	}

	/**
	 * Get a variation of the color and insert
	 *
	 * @access public
	 * @since 7.6
	 * @param array   $colors  The colors array.
	 * @param int     $index   The index.
	 * @param boolean $further The distance.
	 * @return array
	 */
	public function invent_color( $colors = [], $index = 0, $further = false ) {
		$saturation = Fusion_Color::new_color( $colors[ $index ]['color'] )->saturation;

		$lightness = Fusion_Color::new_color( $colors[ $index ]['color'] )->lightness;
		$distance  = ! $further ? 15 : 30;
		if ( 50 > $lightness ) {
			$new_lightness = $lightness + $distance;
		} else {
			$new_lightness = $lightness - $distance;
		}
		$new_color = Fusion_Color::new_color( $colors[ $index ]['color'] )->getNew( 'lightness', $new_lightness );
		$colors[]  = $this->create_color_array( $new_color );

		return $colors;
	}

	/**
	 * Get color values used globally.
	 *
	 * @access public
	 * @since 7.6
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	public function migrate_color_palette( $options = [] ) {

		// Get values used in globals.
		$values = $this->get_color_values( $options );

		// No values are found.
		if ( empty( $values ) ) {
			$options['color_palette'] = AWB_Global_Colors()->get_defaults();
			return $options;
		}

		$colors = [];

		// Main primary color.
		if ( $options['primary_color'] ) {
			$primary           = Fusion_Color::new_color( $options['primary_color'] )->getNew( 'alpha', 100 );
			$colors['primary'] = $this->create_color_array( $primary );
			unset( $values[ $colors['primary']['color'] ] );
		}

		// Fill in the rest of the top 8.  Primary, foreground text and background is force included just now.
		$count = isset( $options['primary_color'] ) ? 1 : 0;
		foreach ( $values as $value => $color_usage ) {
			$color_object = Fusion_Color::new_color( $value );

			// Only full alpha colors in palette.
			if ( 1 !== $color_object->alpha ) {
				continue;
			}

			// If we have a decent number of found colors, dont use Avada green in palette, unless its already added via primary.
			if ( 10 < count( $values ) && 'rgb(101,188,123)' === $color_object->toCss( 'rgb' ) ) {
				continue;
			}

			$colors[ 'common' . $count ] = $this->create_color_array( $color_object );
			$count++;

			unset( $values[ $value ] );

			// We only want the top 8.
			if ( 8 === $count ) {
				break;
			}
		}

		// Sort by luminance.
		usort(
			$colors,
			function( $a, $b ) {
				return $b['luminance'] - $a['luminance'];
			}
		);

		// See if we need to fill in some colors.
		$color_count = count( $colors );
		$missing     = 8 - $color_count;

		// If barely any colors found, lets just bail, something is not right.
		if ( 5 <= $missing ) {
			$options['color_palette'] = AWB_Global_Colors()->get_defaults();
			return $options;

			// We have a few missing, invent them.
		} elseif ( 0 < $missing ) {
			if ( 1 === $missing ) {
				$colors = $this->invent_color( $colors, 3 );
			} elseif ( 2 === $missing ) {
				$colors = $this->invent_color( $colors, 2 );
				$colors = $this->invent_color( $colors, 3 );
			} elseif ( 3 === $missing ) {
				$colors = $this->invent_color( $colors, 1 );
				$colors = $this->invent_color( $colors, 2 );
				$colors = $this->invent_color( $colors, 3 );
			} elseif ( 4 === $missing ) {
				$colors = $this->invent_color( $colors, 0 );
				$colors = $this->invent_color( $colors, 1 );
				$colors = $this->invent_color( $colors, 2 );
				$colors = $this->invent_color( $colors, 3 );
			}

			// Sort by luminance again, after new additions.
			usort(
				$colors,
				function( $a, $b ) {
					return $b['luminance'] - $a['luminance'];
				}
			);
		}

		$defaults = AWB_Global_Colors()->get_defaults();

		// Create the core palette.  Top 8 sorted by luminance.
		$count = 1;
		foreach ( $colors as $index => $color ) {
			$palette_slug                       = 'color' . $count;
			$defaults[ $palette_slug ]['color'] = $color['color'];
			$count++;
		}

		$cutoff = 1;

		// Add the rest of the values as custom colors.
		if ( ! empty( $values ) ) {
			foreach ( $values as $value => $colors_usage ) {

				// If we have reached cut off minimum number of usage, break.
				if ( (int) $colors_usage < $cutoff ) {
					break;
				}

				$color_object = Fusion_Color::new_color( $value );
				$count++;
				$palette_slug              = 'custom' . $count;
				$defaults[ $palette_slug ] = [
					'color' => $color_object->toCss( 'rgba' ),
					'label' => 'Custom ' . $count,
				];
				unset( $values[ $value ] );

				if ( $count > 5 ) {
					$cutoff = round( $count / 5 );
				}
			}
		}

		// Save new data.
		$options['color_palette'] = $defaults;

		return $options;
	}

	/**
	 * Sets Avada Off Canvas Status.
	 *
	 * @access private
	 * @since 7.6
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function enable_avada_off_canvas( $options ) {
		$options['status_awb_Off_Canvas'] = '1';

		return $options;
	}

	/**
	 * Sets social links options.
	 *
	 * @access private
	 * @since 7.6
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_social_links_colors( $options ) {
		if ( isset( $options['social_links_icon_color'] ) ) {
			$color       = Fusion_Color::new_color( $options['social_links_icon_color'] );
			$alpha       = $color->alpha * 0.8;
			$hover_color = Fusion_Color::new_color( $options['social_links_icon_color'] )->getNew( 'alpha', $alpha );
		
			$options['social_links_icon_color_hover'] = $hover_color->toCss( 'rgba' );
		}
		
		if ( isset( $options['social_links_box_color'] ) ) {
			$color       = Fusion_Color::new_color( $options['social_links_box_color'] );
			$alpha       = $color->alpha * 0.8;
			$hover_color = Fusion_Color::new_color( $options['social_links_box_color'] )->getNew( 'alpha', $alpha );
		
			$options['social_links_box_color_hover'] = $hover_color->toCss( 'rgba' );
		}

		return $options;            
	}

	/**
	 * Sets toggles options.
	 *
	 * @access private
	 * @since 7.6
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_toggles( $options ) {

		if ( isset( $options['sep_color'] ) ) {
			$options['accordion_divider_color']       = $options['sep_color'];
			$options['accordion_divider_hover_color'] = $options['sep_color'];
		}

		if ( isset( $options['h4_typography'] ) ) {
			$options['accordion_title_typography'] = [
				'font-family' => $options['h4_typography']['font-family'],
				'font-weight' => $options['h4_typography']['font-weight'],
			];
		}

		if ( isset( $options['link_color'] ) ) {
			$options['accordion_title_typography']['color'] = $options['link_color'];
		}

		if ( isset( $options['accordion_title_font_size'] ) ) {
			$options['accordion_title_typography']['font-size'] = $options['accordion_title_font_size'];
			unset( $options['accordion_title_font_size'] );
		}

		if ( isset( $options['body_typography'] ) ) {
			$options['accordion_content_typography'] = [
				'font-family' => $options['body_typography']['font-family'],
				'font-weight' => $options['body_typography']['font-weight'],
				'font-size'   => $options['body_typography']['font-size'],
				'color'       => $options['body_typography']['color'],
			];
		}

		$options['accordian_active_accent_color'] = '';

		return $options;
	}

	/**
	 * Sets button typography options.
	 *
	 * @access private
	 * @since 7.6
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_button_typo( $options ) {

		// Set variable if not exists.
		if ( ! isset( $options['button_typography'] ) ) {
			$options['button_typography'] = [];
		}

		if ( isset( $options['button_font_size'] ) ) {
			$options['button_typography']['font-size'] = $options['button_font_size'];
		}

		if ( isset( $options['button_line_height'] ) ) {
			$options['button_typography']['line-height'] = $options['button_line_height'];
		}

		if ( isset( $options['button_text_transform'] ) ) {
			$options['button_typography']['text-transform'] = $options['button_text_transform'];
		}

		return $options;
	}
}
