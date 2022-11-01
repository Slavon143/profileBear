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
 * Handle migrations for Avada 7.2.
 *
 * @since 7.2
 */
class Avada_Upgrade_720 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.2
	 * @var string
	 */
	protected $version = '7.2.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.2
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.2
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
	 * @since 7.2
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_woo_settings( $options );
		$options = $this->set_preload_fonts_status( $options );
		$options = $this->migrate_social_sharing_box_settings( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_woo_settings( $options );
			$options = $this->set_preload_fonts_status( $options );
			$options = $this->migrate_social_sharing_box_settings( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrates the Woo options.
	 *
	 * @access private
	 * @since 7.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_woo_settings( $options ) {

		if ( isset( $options['disable_woo_gallery'] ) ) {
			$options['woocommerce_product_images_layout'] = $options['disable_woo_gallery'] ? 'avada' : 'woocommerce';
		}

		if ( isset( $options['enable_woo_gallery_zoom'] ) ) {
			$options['woocommerce_product_images_zoom'] = $options['enable_woo_gallery_zoom'];
		}

		$options['woocommerce_gallery_thumbnail_width'] = 100;

		$options['woocommerce_archive_grid_column_spacing'] = '20';

		return $options;
	}

	/**
	 * Sets preload fonts TO status.
	 *
	 * @access private
	 * @since 7.2
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_preload_fonts_status( $options ) {
		$options['preload_fonts'] = 'none';

		return $options;
	}

	/**
	 * Migrates the Social Sharing Box options
	 *
	 * @access private
	 * @since 7.1
	 * @param array $options  The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_social_sharing_box_settings( $options ) {
		$legacy_setting = [
			'sharing_facebook',
			'sharing_twitter',
			'sharing_reddit',
			'sharing_linkedin',
			'sharing_whatsapp',
			'sharing_tumblr',
			'sharing_pinterest',
			'sharing_vk',
			'sharing_xing',
			'sharing_email',
		];

		$new_option = [];
		foreach ( $legacy_setting as $option ) {
			if ( isset( $options[ $option ] ) && $options[ $option ] ) {
				$new_option[] = str_replace( 'sharing_', '', $option );
			}
		}
		$options['social_sharing'] = $new_option;

		return $options;
	}

}
