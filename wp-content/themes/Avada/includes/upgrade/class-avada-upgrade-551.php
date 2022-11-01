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
 * Handle migrations for Avada 5.5.0.
 *
 * @since 5.5.1
 */
class Avada_Upgrade_551 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.5.1
	 * @var string
	 */
	protected $version = '5.5.1';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access  private
	 * @var  array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.5.1
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
	 * @since 5.5.1
	 * @access protected
	 */
	protected function migrate_options() {

		$options = get_option( $this->option_name, [] );
		$options = $this->correct_chart_default_value( $options );
		$options = $this->page_title_bar_hover( $options );

		update_option( $this->option_name, $options );

		foreach ( self::$available_languages as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			// Get language specific TOs.
			$options = get_option( $this->option_name . '_' . $language, [] );

			$options = $this->correct_chart_default_value( $options );
			$options = $this->page_title_bar_hover( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Corrects default Chart Gridline Color value.
	 *
	 * @access private
	 * @since 5.5.1
	 * @param array $options Global Options.
	 * @return array The updated Global Options array.
	 */
	private function correct_chart_default_value( $options ) {

		if ( isset( $options['chart_gridline_color'] ) && 'rgba(0, 0, 0, 0.1)' === $options['chart_gridline_color'] ) {
			$options['chart_gridline_color'] = 'rgba(0,0,0,0.1)';
		}

		return $options;
	}

	/**
	 * Set page title bar hover color.
	 *
	 * @access private
	 * @since 5.5.1
	 * @param array $options Global Options.
	 * @return array The updated Global Options array.
	 */
	private function page_title_bar_hover( $options ) {

		if ( isset( $options['breadcrumbs_text_color'] ) ) {
			$options['breadcrumbs_text_hover_color'] = $options['breadcrumbs_text_color'];
		}

		return $options;
	}
}
