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
 * Handle migrations for Avada 5.9.1.
 *
 * @since 5.9.1
 */
class Avada_Upgrade_591 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.9.1
	 * @var string
	 */
	protected $version = '5.9.1';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 5.9.1
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.9.1
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
	 * @since 5.9.1
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_search_options( $options );
		$options = $this->migrate_old_color_options( $options );
		$options = $this->migrate_megamenu_width( $options );
		$options = $this->migrate_totop_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_search_options( $options );
			$options = $this->migrate_old_color_options( $options );
			$options = $this->migrate_megamenu_width( $options );
			$options = $this->migrate_totop_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate the search page Global Options.
	 *
	 * @access private
	 * @since 5.9.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_search_options( $options ) {

		$options['search_filter_results'] = '1';

		return $options;
	}

	/**
	 * Migrate old color Global Options where the default was set inside the options.
	 *
	 * @access private
	 * @since 5.9.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_old_color_options( $options ) {

		if ( ! isset( $options['menu_icon_hover_color'] ) ) {
			$options['menu_icon_hover_color'] = ( isset( $options['primary_color'] ) && ! empty( $options['primary_color'] ) ) ? $options['primary_color'] : '#65bc7b';
		}

		if ( ! isset( $options['footer_link_color_hover'] ) ) {
			$options['footer_link_color_hover'] = ( isset( $options['primary_color'] ) && ! empty( $options['primary_color'] ) ) ? $options['primary_color'] : '#ffffff';
		}

		if ( ! isset( $options['copyright_text_color'] ) ) {
			$options['copyright_text_color'] = ( isset( $options['footer_text_color'] ) && ! empty( $options['footer_text_color'] ) ) ? $options['footer_text_color'] : '#ffffff';
		}

		if ( ! isset( $options['copyright_link_color'] ) ) {
			$options['copyright_link_color'] = ( isset( $options['footer_link_color'] ) && ! empty( $options['footer_link_color'] ) ) ? $options['footer_link_color'] : '#ffffff';
		}

		if ( ! isset( $options['copyright_link_color_hover'] ) ) {
			$options['copyright_link_color_hover'] = ( isset( $options['footer_link_color_hover'] ) && ! empty( $options['footer_link_color_hover'] ) ) ? $options['footer_link_color_hover'] : '#ffffff';
		}

		if ( ! isset( $options['map_overlay_color'] ) ) {
			$options['map_overlay_color'] = ( isset( $options['primary_color'] ) && ! empty( $options['primary_color'] ) ) ? $options['primary_color'] : '#65bc7b';
		}

		if ( ! isset( $options['faq_accordian_active_color'] ) ) {
			$options['faq_accordian_active_color'] = ( isset( $options['primary_color'] ) && ! empty( $options['primary_color'] ) ) ? $options['primary_color'] : '#65bc7b';
		}

		if ( ! isset( $options['accordian_active_color'] ) ) {
			$options['accordian_active_color'] = ( isset( $options['primary_color'] ) && ! empty( $options['primary_color'] ) ) ? $options['primary_color'] : '#65bc7b';
		}

		return $options;
	}

	/**
	 * Migrate the megamenu width Global Option.
	 *
	 * @access private
	 * @since 5.9.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_megamenu_width( $options ) {

		$options['megamenu_width'] = 'custom_width';

		// Site width in px.
		if ( false !== strpos( $options['site_width'], 'px' ) && false === strpos( $options['site_width'], 'calc' ) ) {
			$site_width = (int) str_replace( 'px', '', $options['site_width'] );

			if ( $site_width < (int) $options['megamenu_max_width'] ) {
				$options['megamenu_width'] = 'site_width';
			}
		}

		return $options;
	}

	/**
	 * Migrate the to top Global Options.
	 *
	 * @access private
	 * @since 5.9.1
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_totop_options( $options ) {
		if ( '1' === $options['status_totop'] ) {
			if ( '1' === $options['status_totop_mobile'] ) {
				$options['status_totop'] = 'desktop_and_mobile';
			} else {
				$options['status_totop'] = 'desktop';
			}
		} else {
			$options['status_totop'] = 'off';
		}

		unset( $options['status_totop_mobile'] );

		return $options;
	}

}
