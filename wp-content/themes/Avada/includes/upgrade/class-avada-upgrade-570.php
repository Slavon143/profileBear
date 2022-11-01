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
 * Handle migrations for Avada 5.7.
 *
 * @since 5.7
 */
class Avada_Upgrade_570 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.3.0
	 * @var string
	 */
	protected $version = '5.7.0';

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
	 * @since 5.6.0
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();

		add_action( 'init', [ $this, 'migrate_fusion_slider_options' ], 99 );
	}

	/**
	 * Migrate options.
	 *
	 * @since 5.6.0
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->set_mobile_header( $options );
		$options = $this->set_page_title_subheader_text_color( $options );
		$options = $this->set_title_separator_theme_option_default( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->set_mobile_header( $options );
			$options = $this->set_page_title_subheader_text_color( $options );
			$options = $this->set_title_separator_theme_option_default( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Set the mobile header background.
	 *
	 * @access private
	 * @since 5.6.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_mobile_header( $options ) {
		if ( isset( $options['mobile_header_bg_color'] ) ) {
			$color     = Fusion_Color::new_color( Fusion_Sanitize::color( $options['mobile_header_bg_color'] ) );
			$rgb_color = $color->toCSS( 'rgb' );

			if ( 0 === $color->alpha && isset( $options['bg_color'] ) ) {
				$rgb_color = Fusion_Color::new_color( Fusion_Sanitize::color( $options['bg_color'] ) )->toCSS( 'rgb' );
			}

			$options['mobile_header_bg_color']         = $rgb_color;
			$options['mobile_archive_header_bg_color'] = $rgb_color;
		}

		return $options;
	}

	/**
	 * Sets the page title subheader text color.
	 *
	 * @access private
	 * @since 5.7.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_page_title_subheader_text_color( $options ) {
		if ( isset( $options['page_title_color'] ) ) {
			$options['page_title_subheader_color'] = $options['page_title_color'];
		}

		return $options;
	}

	/**
	 * Sets the title element separator Global Option default value.
	 *
	 * @access private
	 * @since 5.7.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function set_title_separator_theme_option_default( $options ) {
		if ( isset( $options['title_style_type'] ) && '' === $options['title_style_type'] ) {
			$options['title_style_type'] = 'none';
		}

		return $options;
	}

	/**
	 * Migrate Avada Slider options to term meta table.
	 *
	 * @access public
	 * @since 5.7.0
	 */
	public function migrate_fusion_slider_options() {
		global $sitepress;
		$taxonomy = 'slide-page';
		$action   = 'register_taxonomy';

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$action( $taxonomy, null );
		}

		$args = [
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'number'     => 0,
			'fields'     => 'id=>name',
		];

		// Polylang: query fetch terms for all languages.
		if ( function_exists( 'pll_default_language' ) ) {
			$args['lang'] = '';
		}

		if ( $sitepress ) {

				// WPML: remove filters so terms for all languages are fetched.
				remove_filter( 'get_terms_args', [ $sitepress, 'get_terms_args_filter' ] );
				remove_filter( 'get_term', [ $sitepress, 'get_term_adjust_id' ] );
				remove_filter( 'terms_clauses', [ $sitepress, 'terms_clauses' ] );
				$sliders = get_terms( $args );
				add_filter( 'terms_clauses', [ $sitepress, 'terms_clauses' ], 10, 4 );
				add_filter( 'get_term', [ $sitepress, 'get_term_adjust_id' ], 1, 1 );
				add_filter( 'get_terms_args', [ $sitepress, 'get_terms_args_filter' ], 10, 2 );
		} else {
				$sliders = get_terms( $args );
		}

		foreach ( $sliders as $term_id => $term_name ) {
			$slider_options = get_option( 'taxonomy_' . $term_id );
			if ( $slider_options ) {
				$updated = update_term_meta( $term_id, 'fusion_slider_options', $slider_options );

				if ( false !== $updated && false === is_wp_error( $updated ) ) {

					// Delete wp_options' entry as don't need it any more.
					delete_option( 'taxonomy_' . $term_id );
				}
			}
		}

	}

}
