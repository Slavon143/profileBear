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
 * Handle migrations for Avada 6.1.
 *
 * @since 6.1
 */
class Avada_Upgrade_610 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 6.1
	 * @var string
	 */
	protected $version = '6.1.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 6.1
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 6.1
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
	 * @since 6.1
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_button_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_button_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate button options.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_button_options( $options ) {
		$button_shape = strtolower( $options['button_shape'] );

		$button_radius = [
			'square'  => '0',
			'round'   => '2',
			'round3d' => '4',
			'pill'    => '25',
		];

		if ( '3d' === $options['button_type'] && 'round' === $button_shape ) {
			$button_shape = 'round3d';
		}

		$options['button_border_radius'] = $button_radius[ $button_shape ];

		unset( $options['button_shape'] );

		$options['button_border_color']       = $options['button_accent_color'];
		$options['button_border_hover_color'] = $options['button_accent_hover_color'];

		return $options;
	}
}
