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
 * Handle migrations for Avada 5.5.2.
 *
 * @since 5.5.2
 */
class Avada_Upgrade_552 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.5.2
	 * @var string
	 */
	protected $version = '5.5.2';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 5.5.2
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.5.2
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
	 * @access protected
	 * @since 5.5.2
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->set_sidebar_gutter_options( $options );
		$options = $this->set_alert_boxes_accent_colors( $options );
		$options = $this->set_archive_header_background_color( $options );
		$options = $this->turn_off_whats_app_social_sharing( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->set_sidebar_gutter_options( $options );
			$options = $this->set_alert_boxes_accent_colors( $options );
			$options = $this->set_archive_header_background_color( $options );
			$options = $this->turn_off_whats_app_social_sharing( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Set default values for new sidebar_gutter option.
	 *
	 * @access private
	 * @since 5.5.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_sidebar_gutter_options( $options ) {

		// Single sidebar gutter set.
		if ( ! isset( $options['sidebar_gutter'] ) || empty( $options['sidebar_gutter'] ) ) {

			// Default to 80px.
			$options['sidebar_gutter'] = '80px';

			// If we're not using px for site-width, set default value to 6%.
			if ( isset( $options['site_width'] ) && false === strpos( $options['site_width'], 'px' ) ) {
				$options['sidebar_gutter'] = '6%';
			}
		}

		// Dual sidebar gutter set.
		if ( ! isset( $options['dual_sidebar_gutter'] ) || empty( $options['dual_sidebar_gutter'] ) ) {

			// Default to 40px.
			$options['dual_sidebar_gutter'] = '40px';

			// If we're not using px for site-width, set default value to 3%.
			if ( isset( $options['site_width'] ) && false === strpos( $options['site_width'], 'px' ) ) {
				$options['dual_sidebar_gutter'] = '3%';
			}
		}
		return $options;
	}

	/**
	 * Set the alert boxes accent colors according to the bg color.
	 *
	 * @access private
	 * @since 5.5.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_alert_boxes_accent_colors( $options ) {

		$info_bg_color    = ( isset( $options['info_bg_color'] ) ) ? $options['info_bg_color'] : '#ffffff';
		$danger_bg_color  = ( isset( $options['danger_bg_color'] ) ) ? $options['danger_bg_color'] : '#f2dede';
		$success_bg_color = ( isset( $options['success_bg_color'] ) ) ? $options['success_bg_color'] : '#dff0d8';
		$warning_bg_color = ( isset( $options['warning_bg_color'] ) ) ? $options['warning_bg_color'] : '#fcf8e3';

		$options['info_accent_color']    = Fusion_Helper::fusion_auto_calculate_accent_color( $info_bg_color );
		$options['danger_accent_color']  = Fusion_Helper::fusion_auto_calculate_accent_color( $danger_bg_color );
		$options['success_accent_color'] = Fusion_Helper::fusion_auto_calculate_accent_color( $success_bg_color );
		$options['warning_accent_color'] = Fusion_Helper::fusion_auto_calculate_accent_color( $warning_bg_color );

		return $options;
	}

	/**
	 * Set the archive header bg color according to header bg color.
	 *
	 * @access private
	 * @since 5.5.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_archive_header_background_color( $options ) {
		$header_bg_color = ( isset( $options['header_bg_color'] ) ) ? $options['header_bg_color'] : '#ffffff';
		$archive_color   = Fusion_Color::new_color( $header_bg_color );
		$archive_color   = $archive_color->to_css( 'rgb' );

		$options['archive_header_bg_color'] = $archive_color;

		return $options;
	}

	/**
	 * Turns off Whats App social sharin box icon..
	 *
	 * @access private
	 * @since 5.5.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function turn_off_whats_app_social_sharing( $options ) {

		$options['sharing_whatsapp'] = '0';

		return $options;
	}

}
