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
 * Handle migrations for Avada 6.2.2.
 *
 * @since 6.2.2
 */
class Avada_Upgrade_622 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 6.2.2
	 * @var string
	 */
	protected $version = '6.2.2';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 6.2.2
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 6.2.2
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
	 * @since 6.2.2
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_scroll_section_sensitivity_option( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_scroll_section_sensitivity_option( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate scroll section sensitivity option.
	 *
	 * @access private
	 * @since 6.2.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_scroll_section_sensitivity_option( $options ) {
		$sensitivity = isset( $options['container_hundred_percent_scroll_sensitivity'] ) ? (int) $options['container_hundred_percent_scroll_sensitivity'] : 100;

		if ( 100 >= $sensitivity ) {
			$sensitivity = 450 - ( ( 100 - $sensitivity ) * 2.5 );
		} elseif ( 100 < $sensitivity ) {
			$sensitivity = 450 + ( ( $sensitivity - 100 ) * 10 );
		}

		if ( 200 > $sensitivity ) {
			$sensitivity = 200;
		} elseif ( 950 < $sensitivity ) {
			$sensitivity = 950;
		}

		$options['container_hundred_percent_scroll_sensitivity'] = $sensitivity;

		return $options;
	}
}
