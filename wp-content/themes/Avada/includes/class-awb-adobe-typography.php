<?php
/**
 * Adobe Typography handling.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @since      7.7
 */

/**
 * Adobe Typography.
 *
 * @since 7.6
 */
class AWB_Adobe_Typography {

	/**
	 * The one, true instance of this object.
	 *
	 * @since 7.7
	 * @var AWB_Adobe_Typography|null
	 */
	private static $instance;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since 7.7
	 * @return AWB_Adobe_Typography
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new AWB_Adobe_Typography();
		}
		return self::$instance;
	}

	/**
	 * The class constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_filter( 'pre_update_option_fusion_options', __CLASS__ . '::refresh_adobe_fonts_on_option_update', 10, 2 );
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::enqueue_adobe_fonts' );
		add_action( 'wp_ajax_fusion_reset_avada_fonts_cache', __CLASS__ . '::ajax_refresh_cache' );
	}

	/**
	 * Enqueue Adobe fonts if the ID is present.
	 *
	 * @return void
	 */
	public static function enqueue_adobe_fonts() {
		$all_options = get_option( 'fusion_options' );
		if ( ! is_array( $all_options ) || empty( $all_options['adobe_fonts_id'] ) ) {
			return;
		}
		$id = $all_options['adobe_fonts_id'];

		wp_enqueue_style( 'awb-adobe-external-style', self::get_adobe_fonts_url_from_id( $id ), [], AVADA_VERSION, 'all' );
	}

	/**
	 * Check if on option update, the Adobe Fonts needs to be refreshed, and refresh if necessary.
	 *
	 * @param array $new_settings The new settings.
	 * @param array $old_settings The old settings.
	 * @return array
	 */
	public static function refresh_adobe_fonts_on_option_update( $new_settings, $old_settings ) {
		if ( ! isset( $new_settings['adobe_fonts_id'] ) ) {
			self::update_adobe_fonts_data( '' );
			return $new_settings;
		}

		if ( ! isset( $old_settings['adobe_fonts_id'] ) ) {
			self::update_adobe_fonts_data( $new_settings['adobe_fonts_id'] );
			return $new_settings;
		}

		if ( $old_settings['adobe_fonts_id'] !== $new_settings['adobe_fonts_id'] ) {
			self::update_adobe_fonts_data( $new_settings['adobe_fonts_id'] );
			return $new_settings;
		}

		return $new_settings;
	}

	/**
	 * Refresh the Adobe Fonts cache via ajax request.
	 *
	 * @return void
	 */
	public static function ajax_refresh_cache() {
		$fusion_options = get_option( 'fusion_options', [] );

		$id = '';
		if ( isset( $fusion_options['adobe_fonts_id'] ) && is_string( $fusion_options['adobe_fonts_id'] ) ) {
			$id = $fusion_options['adobe_fonts_id'];
		}

		self::update_adobe_fonts_data( $id );
		die();
	}

	/**
	 * Updates the font names into an option array.
	 *
	 * @param string $id Id of the project.
	 * @return void
	 */
	public static function update_adobe_fonts_data( $id ) {
		// Holds all the fonts names and variants.
		$adobe_option_name = 'avada_adobe_fonts';
		// True if the fonts parsing was unsuccessful.
		$adobe_error_option_name = 'avada_adobe_fonts_error';
		// Used to prevent cache when an Adobe(TypeKit) css file is refreshed.
		$adobe_refresh_time = 'avada_adobe_fonts_refresh_time';

		// Empty value or no value.
		if ( ! $id ) {
			delete_option( $adobe_option_name );
			delete_option( $adobe_refresh_time );
			delete_option( $adobe_error_option_name );
			return;
		}

		$link = self::get_adobe_fonts_url_from_id( $id );

		// Get the CSS file from remote.
		$response = wp_remote_get( $link, [ 'timeout' => 10 ] );
		if ( is_wp_error( $response ) ) {
			// Note: no option name update here. If it fails because of timeout or link doesn't work for the moment, at least front-end fonts will work good.
			update_option( $adobe_error_option_name, true );
			update_option( $adobe_refresh_time, time() );
			return;
		}
		$css_contents = wp_remote_retrieve_body( $response );
		if ( ! is_string( $css_contents ) ) {
			update_option( $adobe_option_name, [] );
			update_option( $adobe_refresh_time, time() );
			update_option( $adobe_error_option_name, true );
			return;
		}

		update_option( $adobe_option_name, self::get_adobe_fonts_from_css( $css_contents ) );
		update_option( $adobe_refresh_time, time() );
		update_option( $adobe_error_option_name, false );
	}

	/**
	 * Get the Adobe Url from ID.
	 *
	 * @param string $id The ID.
	 * @param bool   $append_time Whether to append time or not to prevent cache.
	 * @return string
	 */
	public static function get_adobe_fonts_url_from_id( $id, $append_time = true ) {
		$link = 'https://use.typekit.net/' . $id . '.css';
		if ( $append_time ) {
			$time  = get_option( 'avada_adobe_fonts_refresh_time', time() );
			$link .= '?timestamp=' . $time;
		}

		return $link;
	}

	/**
	 * Get the Adobe Fonts with their variants in an associative array.
	 *
	 * @param string $css_contents The css contents of the adobe file.
	 * @return array
	 */
	public static function get_adobe_fonts_from_css( $css_contents ) {
		$font_data             = [];
		$variants_translations = Fusion_App()->get_variants_translations();

		$font_faces_regex = '/@font-face\s*{[^}]*}/s';
		preg_match_all( $font_faces_regex, $css_contents, $font_faces );

		if ( ! isset( $font_faces, $font_faces[0] ) ) {
			return $font_data;
		}
		$font_faces = $font_faces[0];

		// Construct the font data.
		foreach ( $font_faces as $font_face ) {
			$font_info_regex = '/font-family:([^,;]*)[^;]*;.*?font-style:([^;]*);.*?font-weight:([^;]*);/s';
			$matches         = preg_match( $font_info_regex, $font_face, $font_info_matches );

			if ( ! $matches ) {
				continue;
			}

			$family_slug   = $font_info_matches[1];
			$family_style  = $font_info_matches[2];
			$family_weight = $font_info_matches[3];

			// Remove spaces and quotes before and after.
			$family_slug = preg_replace( '/^\s*[\'\"]?/', '', $family_slug );
			$family_slug = preg_replace( '/[\'\"]?\s*$/', '', $family_slug );
			$variant_id  = $family_weight . ( 'normal' === $family_style ? '' : $family_style );

			// Special case.
			if ( '400italic' === $variant_id ) {
				$variant_id = 'italic';
			}

			$variant_label = ( isset( $variants_translations[ $variant_id ] ) ? $variants_translations[ $variant_id ] : $variant_id );

			if ( ! is_string( $family_slug ) ) {
				continue;
			}

			if ( isset( $font_data[ $family_slug ] ) && is_array( $font_data[ $family_slug ]['variants'] ) ) {
				$variant_to_add                                       = [
					'id'    => $variant_id,
					'label' => $variant_label,
				];
				$font_data[ $family_slug ]['variants'][ $variant_id ] = $variant_to_add;
			} else {
				$font_data[ $family_slug ] = [
					'label'     => ucwords( str_replace( '-', ' ', $family_slug ) ),
					'font_slug' => $family_slug,
					'variants'  => [
						$variant_id => [
							'id'    => $variant_id,
							'label' => $variant_label,
						],
					],
				];
			}
		}

		// Order the variants.
		foreach ( $font_data as $font_id => $font ) {
			$variants_ordered = [];

			// Order by variants translations key.
			foreach ( array_keys( $variants_translations ) as $variant_id ) {
				if ( isset( $font['variants'][ $variant_id ] ) ) {
					$variants_ordered[ $variant_id ] = $font['variants'][ $variant_id ];
				}
			}

			// Append other variants at the end, to not forgot them if a variant id doesn't have translation.
			foreach ( $font['variants'] as $variant_id => $variant_data ) {
				if ( ! isset( $variants_ordered[ $variant_id ] ) ) {
					$variants_ordered[ $variant_id ] = $font['variants'][ $variant_id ];
				}
			}

			$font_data[ $font_id ]['variants'] = array_values( $variants_ordered );
		}

		return $font_data;
	}

	/**
	 * Check if a font-family is an Adobe Font.
	 *
	 * @param string $font_family The font family id.
	 * @return bool
	 */
	public static function is_adobe_font( $font_family ) {
		$adobe_fonts = get_option( 'avada_adobe_fonts', [] );
		if ( is_array( $adobe_fonts ) && isset( $adobe_fonts[ $font_family ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the display name of the Adobe Font.
	 *
	 * @param string $font_family The font family id.
	 * @return string
	 */
	public static function get_adobe_display_name( $font_family ) {
		$adobe_fonts = get_option( 'avada_adobe_fonts', [] );
		if ( is_array( $adobe_fonts ) && isset( $adobe_fonts[ $font_family ] ) ) {
			return $adobe_fonts[ $font_family ]['label'];
		}

		return $font_family;
	}

	/**
	 * Gets a basic html for displaying included adobe fonts.
	 *
	 * @return string
	 */
	public static function get_adobe_included_fonts_display_html() {
		$fonts_data = get_option( 'avada_adobe_fonts', [] );

		if ( ! is_array( $fonts_data ) || empty( $fonts_data ) ) {
			return '<p>' . esc_html__( 'No Adobe Fonts detected.', 'Avada' ) . '</p>';
		}

		$html = '<p><strong>' . esc_html__( 'Adobe Fonts detected:', 'Avada' ) . '</strong></p>';
		foreach ( $fonts_data as $font ) {
			$html .= '<p><strong>' . $font['label'] . ':</strong> ';

			foreach ( $font['variants'] as $key => $variant ) {
				$html .= $variant['label'];

				if ( array_key_last( $font['variants'] ) !== $key ) {
					$html .= ', ';
				} else {
					$html .= '.';
				}
			}

			$html .= '</p>';
		}

		return $html;
	}
}

/**
 * Instantiates the AWB_Adobe_Typography class.
 * Make sure the class is properly set-up.
 *
 * @since 7.7
 * @return AWB_Adobe_Typography
 */
function AWB_Adobe_Typography() { // phpcs:ignore WordPress.NamingConventions
	return AWB_Adobe_Typography::get_instance();
}
AWB_Adobe_Typography();
