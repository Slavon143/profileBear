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
 * Handle migrations for Avada 7.1.
 *
 * @since 7.1
 */
class Avada_Upgrade_710 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.1
	 * @var string
	 */
	protected $version = '7.1.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.1
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.1
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
	 * @since 7.1
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->enable_avada_forms( $options );
		$options = $this->migrate_lazy_load( $options );
		$options = $this->migrate_form_border( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->enable_avada_forms( $options );
			$options = $this->migrate_lazy_load( $options );
			$options = $this->migrate_form_border( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Sets Avada Forms Status.
	 *
	 * @access private
	 * @since 7.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function enable_avada_forms( $options ) {
		$options['status_fusion_forms'] = '1';

		return $options;
	}

	/**
	 * Migrates the lazy load option.
	 *
	 * @access private
	 * @since 7.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_lazy_load( $options ) {

		if ( isset( $options['lazy_load'] ) ) {
			if ( '1' === $options['lazy_load'] ) {
				$options['lazy_load'] = 'avada';
			} else {
				$options['lazy_load'] = 'none';
			}
		}

		return $options;
	}

	/**
	 * Migrates the form border width option.
	 *
	 * @access private
	 * @since 7.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_form_border( $options ) {

		if ( isset( $options['form_border_width'] ) ) {
			$value = ( (int) $options['form_border_width'] ) . 'px';

			$options['form_border_width'] = [
				'top'    => $value,
				'right'  => $value,
				'bottom' => $value,
				'left'   => $value,
			];
		}

		return $options;
	}
}
