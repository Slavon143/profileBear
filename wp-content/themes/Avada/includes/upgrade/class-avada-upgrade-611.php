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
 * Handle migrations for Avada 6.1.1.
 *
 * @since 6.1.1
 */
class Avada_Upgrade_611 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 6.1.1
	 * @var string
	 */
	protected $version = '6.1.1';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 6.1.1
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 6.1.1
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
	 * @since 6.1.1
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_css_animation_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_css_animation_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate CSS animation options.
	 *
	 * @access private
	 * @since 6.1.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_css_animation_options( $options ) {
		$option_value = '';

		if ( '1' === $options['use_animate_css'] && '1' === $options['disable_mobile_animate_css'] ) {
			$option_value = 'desktop_and_mobile';
		} elseif ( '1' === $options['use_animate_css'] && '0' === $options['disable_mobile_animate_css'] ) {
			$option_value = 'desktop';
		} else {
			$option_value = 'off';
		}

		$options['status_css_animations'] = $option_value;

		unset( $options['use_animate_css'] );
		unset( $options['disable_mobile_animate_css'] );

		return $options;
	}
}
