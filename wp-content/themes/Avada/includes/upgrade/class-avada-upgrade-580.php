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
 * Handle migrations for Avada 5.8.
 *
 * @since 5.8
 */
class Avada_Upgrade_580 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.8
	 * @var string
	 */
	protected $version = '5.8.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 5.8
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.8.0
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
	 * @since 5.8.0
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_fa_status( $options );
		$options = $this->migrate_flyout_menu_padding( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_fa_status( $options );
			$options = $this->migrate_flyout_menu_padding( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate FontAwesome option.
	 *
	 * @access private
	 * @since 5.8.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_fa_status( $options ) {

		if ( isset( $options['status_fontawesome'] ) ) {
			if ( '1' === $options['status_fontawesome'] ) {
				$options['status_fontawesome']           = [ 'fab', 'far', 'fas' ];
				$options['fontawesome_v4_compatibility'] = '1';
			} else {
				$options['status_fontawesome']           = [];
				$options['fontawesome_v4_compatibility'] = '0';
			}
		}

		return $options;
	}

	/**
	 * Migrate flyout menu padding option.
	 *
	 * @access private
	 * @since 5.8.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_flyout_menu_padding( $options ) {
		$nav_font_size      = Avada()->settings->get( 'nav_typography', 'font-size' );
		$nav_font_size_unit = Fusion_Sanitize::get_unit( $nav_font_size );
		$nav_font_size      = Fusion_Sanitize::number( $nav_font_size );

		$base_font_size      = Avada()->settings->get( 'body_typography', 'font-size' );
		$base_font_size_unit = Fusion_Sanitize::get_unit( $base_font_size );
		$base_font_size      = Fusion_Sanitize::number( $base_font_size );

		// Browser default font size. This is the average between Safari, Chrome and FF.
		$default_font_size = 15;

		if ( 'em' === $base_font_size_unit || 'rem' === $base_font_size_unit ) {
			$base_font_size = $default_font_size * $base_font_size;
		} elseif ( '%' === $base_font_size_unit ) {
			$base_font_size = $default_font_size * $base_font_size / 100;
		} elseif ( 'px' !== $base_font_size_unit ) {
			$base_font_size = $default_font_size;
		}

		if ( 'em' === $nav_font_size_unit || 'rem' === $nav_font_size_unit ) {
			$nav_font_size = $base_font_size * $nav_font_size;
		} elseif ( '%' === $nav_font_size_unit ) {
			$nav_font_size = $base_font_size * $nav_font_size / 100;
		} elseif ( 'px' !== $nav_font_size_unit ) {
			$nav_font_size = $base_font_size;
		}

		$options['flyout_menu_item_padding'] = round( 2 * $nav_font_size );

		return $options;
	}
}
