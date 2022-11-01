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
 * Handle migrations for Avada 6.0.0.
 *
 * @since 6.0.0
 */
class Avada_Upgrade_600 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 6.0.0
	 * @var string
	 */
	protected $version = '6.0.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 6.0.0
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 6.0.0
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();
	}

	/**
	 * Changes options.
	 *
	 * @access protected
	 * @since 6.0.0
	 * @return void
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_sidebar_width_options( $options );
		$options = $this->migrate_lowercase_options( $options );
		$options = $this->migrate_underscore_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_sidebar_width_options( $options );
			$options = $this->migrate_lowercase_options( $options );
			$options = $this->migrate_underscore_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate sidebar width options.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_sidebar_width_options( $options ) {
		$sidebar_width_options = [
			'sidebar_width',
			'sidebar_2_1_width',
			'sidebar_2_2_width',
			'ec_sidebar_width',
			'ec_sidebar_2_1_width',
			'ec_sidebar_2_2_width',
		];

		foreach ( $sidebar_width_options as $sidebar_width ) {
			if ( isset( $options[ $sidebar_width ] ) && is_numeric( $options[ $sidebar_width ] ) ) {
				$options[ $sidebar_width ] .= ( 100 > $options[ $sidebar_width ] ) ? '%' : 'px';
			}
		}

		return $options;
	}

	/**
	 * Migrate lowercase options.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_lowercase_options( $options ) {
		$lowercase_options = [
			'logo_alignment',
			'excerpt_base',
			'lightbox_animation_speed',
			'blog_layout',
			'blog_archive_layout',
			'search_layout',
			'blog_pagination_type',
			'search_pagination_type',
			'content_length',
			'portfolio_archive_content_length',
			'scheme_type',
			'sidenav_behavior',
			'header_position',
			'slider_position',
			'header_left_content',
			'header_right_content',
			'header_v4_content',
			'layout',
			'boxed_modal_shadow',
			'page_title_bar_bs',
			'header_social_links_tooltip_placement',
			'footer_social_links_tooltip_placement',
			'sharing_social_links_tooltip_placement',
		];

		foreach ( $lowercase_options as $option ) {
			if ( isset( $options[ $option ] ) ) {
				$options[ $option ] = strtolower( $options[ $option ] );
			}
		}

		return $options;
	}

	/**
	 * Migrate underscore options.
	 *
	 * @access private
	 * @since 6.0.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_underscore_options( $options ) {
		$underscore_options = [
			'blog_pagination_type',
			'search_pagination_type',
			'content_length',
			'portfolio_archive_content_length',
			'header_left_content',
			'header_right_content',
			'header_v4_content',
			'page_title_bar_bs',
		];

		foreach ( $underscore_options as $option ) {
			if ( isset( $options[ $option ] ) ) {
				$options[ $option ] = str_replace( ' ', '_', $options[ $option ] );
			}
		}

		return $options;
	}
}
