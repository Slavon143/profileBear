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
 * Handle migrations for Avada 7.0
 *
 * @since 7.0
 */
class Avada_Upgrade_700 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.0
	 * @var string
	 */
	protected $version = '7.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.0
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.0
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
	 * @since 7.0
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->enable_container_legacy_mode( $options );
		$options = $this->migrate_full_width_border_size( $options );
		$options = $this->migrate_separator_icon_color( $options );
		$options = $this->disable_xing_sharingbox( $options );
		$options = $this->migrate_content_breakpoint( $options );
		$options = $this->migrate_content_boxes_title_size( $options );
		$options = $this->convert_muli_to_mulish( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->enable_container_legacy_mode( $options );
			$options = $this->migrate_full_width_border_size( $options );
			$options = $this->migrate_separator_icon_color( $options );
			$options = $this->disable_xing_sharingbox( $options );
			$options = $this->migrate_content_breakpoint( $options );
			$options = $this->migrate_content_boxes_title_size( $options );
			$options = $this->convert_muli_to_mulish( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate full_width_border_size option.
	 *
	 * @access private
	 * @since 7.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_content_breakpoint( $options ) {
		if ( isset( $options['content_break_point'] ) ) {
			$header_position   = isset( $options['header_position'] ) ? $options['header_position'] : 'top';
			$side_header_width = isset( $options['side_header_width'] ) ? $options['side_header_width'] : 280;
			$side_header_width = 'top' === $header_position ? 0 : $side_header_width;

			$old_breakpoint = (int) $options['content_break_point'] + (int) $side_header_width;
			$medium_break   = isset( $options['visibility_medium'] ) ? (int) $options['visibility_medium'] : 1024;
			$small_break    = isset( $options['visibility_small'] ) ? (int) $options['visibility_small'] : 640;

			if ( ceil( $old_breakpoint - $medium_break ) < ceil( $old_breakpoint - $small_break ) ) {
				$options['col_width_medium'] = '1_1';
			}
		}
		return $options;
	}
	/**
	 * Migrate full_width_border_size option.
	 *
	 * @access private
	 * @since 7.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_full_width_border_size( $options ) {
		if ( isset( $options['full_width_border_size'] ) && ! isset( $options['full_width_border_sizes'] ) ) {
			$options['full_width_border_sizes'] = [
				'top'    => absint( $options['full_width_border_size'] ) . 'px',
				'bottom' => absint( $options['full_width_border_size'] ) . 'px',
				'left'   => '0px',
				'right'  => '0px',
			];
		}
		return $options;
	}

	/**
	 * Migrate the separator icon color..
	 *
	 * @access private
	 * @since 7.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_separator_icon_color( $options ) {

		$options['separator_icon_color'] = '';

		return $options;
	}

	/**
	 * Enable container legacy mode for users who update.
	 *
	 * @access private
	 * @since 7.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function enable_container_legacy_mode( $options ) {

		$options['container_legacy_support'] = '1';

		return $options;
	}

	/**
	 * Disable Xing in sharingbox for users who update.
	 *
	 * @access private
	 * @since 7.0
	 * @param array $options The global styles array.
	 * @return array
	 */
	private function disable_xing_sharingbox( $options ) {
		$options['sharing_xing'] = '0';
		return $options;
	}

	/**
	 * Change title size to pixel only value.
	 *
	 * @access private
	 * @since 7.0
	 * @param array $options The global styles array.
	 * @return array
	 */
	private function migrate_content_boxes_title_size( $options ) {
		$options['content_box_title_size'] = filter_var( $options['content_box_title_size'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		return $options;
	}

	/**
	 * Convert "Muli" font-name to "Mulish".
	 *
	 * @access private
	 * @since 7.0
	 * @see https://github.com/google/fonts/pull/2504
	 * @param array $options The global styles array.
	 * @return array
	 */
	private function convert_muli_to_mulish( $options ) {
		$typo_options = [
			'footer_headings_typography',
			'nav_typography',
			'mobile_menu_typography',
			'body_typography',
			'h1_typography',
			'h2_typography',
			'h3_typography',
			'h4_typography',
			'h5_typography',
			'h6_typography',
			'post_title_typography',
			'post_titles_extras_typography',
			'button_typography',
		];

		foreach ( $typo_options as $option_name ) {
			if ( isset( $options[ $option_name ] ) && isset( $options[ $option_name ]['font-family'] ) && 'Muli' === $options[ $option_name ]['font-family'] ) {
				$options[ $option_name ]['font-family'] = 'Mulish';
			}
		}

		return $options;
	}
}
