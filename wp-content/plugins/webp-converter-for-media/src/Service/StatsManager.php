<?php

namespace WebpConverter\Service;

/**
 * Manages the statistical data saved by the plugin.
 */
class StatsManager {

	const STATS_INSTALLATION_DATE_OPTION       = 'webpc_stats_installation_date';
	const STATS_FIRST_VERSION_OPTION           = 'webpc_stats_first_version';
	const STATS_REGENERATION_IMAGES_OPTION     = 'webpc_stats_regeneration_images';
	const STATS_IMAGES_WEBP_ALL_OPTION         = 'webpc_stats_webp_all';
	const STATS_IMAGES_WEBP_UNCONVERTED_OPTION = 'webpc_stats_webp_unconverted';
	const STATS_IMAGES_AVIF_ALL_OPTION         = 'webpc_stats_avif_all';
	const STATS_IMAGES_AVIF_UNCONVERTED_OPTION = 'webpc_stats_avif_unconverted';

	/**
	 * @return void
	 */
	public function set_plugin_installation_date() {
		if ( OptionsAccessManager::get_option( self::STATS_INSTALLATION_DATE_OPTION ) !== null ) {
			return;
		}

		OptionsAccessManager::update_option(
			self::STATS_INSTALLATION_DATE_OPTION,
			gmdate( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * @return int|null
	 */
	public function get_plugin_usage_time() {
		$installation_date = OptionsAccessManager::get_option( self::STATS_INSTALLATION_DATE_OPTION );
		if ( $installation_date === null ) {
			return null;
		}

		return ( strtotime( gmdate( 'Y-m-d H:i:s' ) ) - strtotime( $installation_date ) );
	}

	/**
	 * @param string $value .
	 *
	 * @return void
	 */
	public function set_plugin_first_version( string $value ) {
		if ( OptionsAccessManager::get_option( self::STATS_FIRST_VERSION_OPTION ) !== null ) {
			return;
		}

		OptionsAccessManager::update_option(
			self::STATS_FIRST_VERSION_OPTION,
			$value
		);
	}

	/**
	 * @return int|null
	 */
	public function get_plugin_first_version() {
		return OptionsAccessManager::get_option( self::STATS_FIRST_VERSION_OPTION, null );
	}

	/**
	 * @param int $value .
	 *
	 * @return void
	 */
	public function set_regeneration_images( int $value ) {
		if ( OptionsAccessManager::get_option( self::STATS_REGENERATION_IMAGES_OPTION ) !== null ) {
			return;
		}

		OptionsAccessManager::update_option( self::STATS_REGENERATION_IMAGES_OPTION, $value );
	}

	/**
	 * @return int|null
	 */
	public function get_regeneration_images() {
		return OptionsAccessManager::get_option( self::STATS_REGENERATION_IMAGES_OPTION, null );
	}

	/**
	 * @param int $value .
	 *
	 * @return void
	 */
	public function set_images_webp_all( int $value ) {
		OptionsAccessManager::update_option( self::STATS_IMAGES_WEBP_ALL_OPTION, $value );
	}

	/**
	 * @return int|null
	 */
	public function get_images_webp_all() {
		return OptionsAccessManager::get_option( self::STATS_IMAGES_WEBP_ALL_OPTION, null );
	}

	/**
	 * @param int $value .
	 *
	 * @return void
	 */
	public function set_images_webp_unconverted( int $value ) {
		OptionsAccessManager::update_option( self::STATS_IMAGES_WEBP_UNCONVERTED_OPTION, $value );
	}

	/**
	 * @return int|null
	 */
	public function get_images_webp_unconverted() {
		return OptionsAccessManager::get_option( self::STATS_IMAGES_WEBP_UNCONVERTED_OPTION, null );
	}

	/**
	 * @param int $value .
	 *
	 * @return void
	 */
	public function set_images_avif_all( int $value ) {
		OptionsAccessManager::update_option( self::STATS_IMAGES_AVIF_ALL_OPTION, $value );
	}

	/**
	 * @return int|null
	 */
	public function get_images_avif_all() {
		return OptionsAccessManager::get_option( self::STATS_IMAGES_AVIF_ALL_OPTION, null );
	}

	/**
	 * @param int $value .
	 *
	 * @return void
	 */
	public function set_images_avif_unconverted( int $value ) {
		OptionsAccessManager::update_option( self::STATS_IMAGES_AVIF_UNCONVERTED_OPTION, $value );
	}

	/**
	 * @return int|null
	 */
	public function get_images_avif_unconverted() {
		return OptionsAccessManager::get_option( self::STATS_IMAGES_AVIF_UNCONVERTED_OPTION, null );
	}
}
