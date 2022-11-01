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
 * Handle migrations for Avada 7.4
 *
 * @since 7.4
 */
class Avada_Upgrade_740 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.4
	 * @var string
	 */
	protected $version = '7.4.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.4
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.4
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();
	}

	/**
	 * Migrate options.
	 *
	 * @since 7.4
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->deprecate_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->deprecate_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Remove smooth scrolling and set scrollbar colors.
	 *
	 * @access private
	 * @since 7.4
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function deprecate_options( $options ) {

		if ( isset( $options['smooth_scrolling'] ) && '1' === $options['smooth_scrolling'] ) {
			$options['custom_scrollbar']     = '1';
			$options['scrollbar_background'] = '#555555';
			$options['scrollbar_handle']     = '#303030';
			unset( $options['smooth_scrolling'] );
		}

		$options['button_presets'] = '1';
		return $options;
	}
}
