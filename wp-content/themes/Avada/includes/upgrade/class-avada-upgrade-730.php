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
 * Handle migrations for Avada 7.3
 *
 * @since 7.3
 */
class Avada_Upgrade_730 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.2
	 * @var string
	 */
	protected $version = '7.3.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.3
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.3
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		// Clear privacy preference, since they have changed.
		$user = get_current_user_id();
		if ( $user ) {
			delete_user_meta( $user, 'the-meta' );
		}

		$this->migrate_options();
	}

	/**
	 * Migrate options.
	 *
	 * @since 7.1
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->title_margins( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->title_margins( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Add left and right 0px margins for titles.
	 *
	 * @access private
	 * @since 7.3
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function title_margins( $options ) {

		if ( isset( $options['title_margin'] ) && is_array( $options['title_margin'] ) ) {
			$options['title_margin']['left']  = '0px';
			$options['title_margin']['right'] = '0px';
		}
		if ( isset( $options['title_margin_mobile'] ) && is_array( $options['title_margin_mobile'] ) ) {
			$options['title_margin_mobile']['left']  = '0px';
			$options['title_margin_mobile']['right'] = '0px';
		}

		return $options;
	}
}
