<?php
/**
 * Import demos for fusion-builder.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      6.0.0
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Demos importer.
 */
class Fusion_Builder_Demos_Theme_Options {

	/**
	 * The remote API URL.
	 *
	 * @static
	 * @access protected
	 * @since 6.0.0
	 * @var string
	 */
	protected static $remote_api_url = FUSION_UPDATES_URL . '/avada_demo';

	/**
	 * Transient name for saving data.
	 *
	 * @static
	 * @access protected
	 * @since 6.0.0
	 * @var string
	 */
	protected static $transient_name = 'avada-builder-demo-theme-options';

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 6.0
	 */
	public function __construct() {

		$this->get_uncompressed_data();
		add_filter( 'awb_global_options', [ $this, 'demo_array' ] );
	}

	/**
	 * Gets the demos data from the remote server (or locally if remote is unreachable)
	 * decodes the JSON object and returns an array.
	 *
	 * @static
	 * @access public
	 * @since 6.0
	 * @return array
	 */
	public function get_uncompressed_data() {

		$demos = get_transient( self::$transient_name );

		// Reset demos if reset_transient=1.
		if ( isset( $_GET['reset_transient'] ) && '1' === $_GET['reset_transient'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$demos = false;
		}

		// If the transient does not exist or we've reset it, continue to get the JSON.
		if ( false === $demos ) {

			// Get the demo details from the remote server.
			$args = [
				'user-agent' => 'avada-user-agent',
			];

			$remote_demos = wp_remote_retrieve_body( wp_remote_get( self::$remote_api_url, $args ) );
			$remote_demos = json_decode( $remote_demos, true );
			if ( ! empty( $remote_demos ) && $remote_demos && function_exists( 'json_last_error' ) && json_last_error() === JSON_ERROR_NONE ) {
				$demos = $remote_demos;
			}
			set_transient( self::$transient_name, $demos, WEEK_IN_SECONDS );
		}
		return $demos;
	}

	/**
	 * Get array of demo options.
	 *
	 * @since 6.0
	 * @access private
	 * @param array $demo_choices Array of demo choices for import.
	 * @return array
	 */
	public function demo_array( $demo_choices ) {

		$demos = $this->get_uncompressed_data();

		if ( ! $demos ) {
			return $demo_choices;
		}

		// Check all option available.
		foreach ( $demos as $demo_id => $demo_info ) {
			$demo_name = esc_html( ucwords( str_replace( '_', ' ', $demo_id ) ) );

			// Check if version is supported.
			if ( isset( $demo_info['minVersion'] ) ) {
				$min_version   = Avada_Helper::normalize_version( $demo_info['minVersion'] );
				$theme_version = Avada_Helper::normalize_version( Avada()->get_theme_version() );
				if ( version_compare( $theme_version, $min_version ) < 0 ) {
					continue;
				}
			}

			if ( isset( $demo_info['themeOptionsJSON'] ) ) {

				// Demo has not been downloaded, use external.
				$demo_choices[ $demo_info['themeOptionsJSON'] ] = $demo_name;
			}
		}

		asort( $demo_choices );

		$default_choice = [ '' => esc_attr__( 'Select Prebuilt Website', 'Avada' ) ];

		return $default_choice + $demo_choices;
	}
}
