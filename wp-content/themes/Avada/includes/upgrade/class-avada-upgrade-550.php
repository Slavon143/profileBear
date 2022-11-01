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
 * @since 5.5.0
 */
class Avada_Upgrade_550 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.5.0
	 * @var string
	 */
	protected $version = '5.5.0';

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
	 * @since 5.5.0
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
	 * @since 5.5.0
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->set_post_titles_typography_options( $options );
		$options = $this->set_page_background_options( $options );
		$options = $this->set_masonry_default_options( $options );
		$options = $this->set_sliding_bar_paddings( $options );
		$options = $this->set_masonry_grid_ratio( $options );
		$options = $this->set_blog_archive_grid_options( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->set_post_titles_typography_options( $options );
			$options = $this->set_page_background_options( $options );
			$options = $this->set_masonry_default_options( $options );
			$options = $this->set_sliding_bar_paddings( $options );
			$options = $this->set_masonry_grid_ratio( $options );
			$options = $this->set_blog_archive_grid_options( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Set default values for new masonry options.
	 *
	 * @access private
	 * @since 5.5.0
	 * @param array $options The Global Options array.
	 * @return array The updated Global Options array.
	 */
	private function set_masonry_default_options( $options ) {
		$options['masonry_grid_ratio']   = 0.8;
		$options['masonry_width_double'] = 2000;

		return $options;
	}

	/**
	 * Set default values for new masonry options.
	 *
	 * @access private
	 * @since 5.5.0
	 * @param array $options The Global Options array.
	 * @return array The updated Global Options array.
	 */
	private function set_sliding_bar_paddings( $options ) {

		if ( isset( $options['slidingbar_position'] ) && ( 'top' === $options['slidingbar_position'] || 'bottom' === $options['slidingbar_position'] ) ) {
			$left_right_padding = '30px';
			if ( false !== strpos( Avada()->settings->get( 'site_width' ), '%' ) ) {
				$left_right_padding = '10px';
			}
			$options['slidingbar_content_padding'] = [
				'top'    => '35px',
				'right'  => $left_right_padding,
				'bottom' => '35px',
				'left'   => $left_right_padding,
			];
		} else {
			$options['slidingbar_content_padding'] = [
				'top'    => '60px',
				'right'  => '30px',
				'bottom' => '60px',
				'left'   => '30px',
			];
		}

		return $options;
	}

	/**
	 * Set default values for new post titles typography options.
	 *
	 * @access private
	 * @since 5.5.0
	 * @param array $options The Global Options array.
	 * @return array The updated Global Options array.
	 */
	private function set_post_titles_typography_options( $options ) {

		// For post titles.
		if ( ! isset( $options['post_title_typography'] ) || ! is_array( $options['post_title_typography'] ) ) {
			$options['post_title_typography'] = [];
		}
		if ( isset( $options['h2_typography'] ) ) {
			$options['post_title_typography']['font-weight']    = $options['h2_typography']['font-weight'];
			$options['post_title_typography']['font-family']    = $options['h2_typography']['font-family'];
			$options['post_title_typography']['font-style']     = $options['h2_typography']['font-style'];
			$options['post_title_typography']['font-options']   = $options['h2_typography']['font-options'];
			$options['post_title_typography']['font-backup']    = $options['h2_typography']['font-backup'];
			$options['post_title_typography']['subsets']        = $options['h2_typography']['subsets'];
			$options['post_title_typography']['google']         = $options['h2_typography']['google'];
			$options['post_title_typography']['letter-spacing'] = $options['h2_typography']['letter-spacing'];
			$options['post_title_typography']['color']          = $options['h2_typography']['color'];
		}
		if ( isset( $options['post_titles_font_lh'] ) ) {
			$options['post_title_typography']['line-height'] = $options['post_titles_font_lh'];
		}
		if ( isset( $options['post_titles_font_size'] ) ) {
			$options['post_title_typography']['font-size'] = $options['post_titles_font_size'];
		}

		unset( $options['post_titles_font_lh'] );
		unset( $options['post_titles_font_size'] );

		// For post title extras.
		if ( ! isset( $options['post_titles_extras_typography'] ) || ! is_array( $options['post_titles_extras_typography'] ) ) {
			$options['post_titles_extras_typography'] = [];
		}
		if ( isset( $options['h3_typography'] ) ) {
			$options['post_titles_extras_typography']['font-weight']    = $options['h3_typography']['font-weight'];
			$options['post_titles_extras_typography']['font-family']    = $options['h3_typography']['font-family'];
			$options['post_titles_extras_typography']['font-style']     = $options['h3_typography']['font-style'];
			$options['post_titles_extras_typography']['font-options']   = $options['h3_typography']['font-options'];
			$options['post_titles_extras_typography']['font-backup']    = $options['h3_typography']['font-backup'];
			$options['post_titles_extras_typography']['subsets']        = $options['h3_typography']['subsets'];
			$options['post_titles_extras_typography']['google']         = $options['h3_typography']['google'];
			$options['post_titles_extras_typography']['letter-spacing'] = $options['h3_typography']['letter-spacing'];
			$options['post_titles_extras_typography']['color']          = $options['h3_typography']['color'];
			$options['post_titles_extras_typography']['line-height']    = '1.5';
		}
		if ( isset( $options['post_titles_extras_font_size'] ) ) {
			$options['post_titles_extras_typography']['font-size'] = $options['post_titles_extras_font_size'];
		}

		unset( $options['post_titles_extras_font_size'] );

		return $options;
	}

	/**
	 * Clear page background if layout is set to wide.
	 *
	 * @access private
	 * @since 5.5.0
	 * @param array $options The Global Options array.
	 * @return array The updated Global Options array.
	 */
	private function set_page_background_options( $options ) {

		if ( isset( $options['layout'] ) && 'Wide' === $options['layout'] ) {

			$options['bg_image']['url']       = '';
			$options['bg_image']['id']        = '';
			$options['bg_image']['width']     = '';
			$options['bg_image']['height']    = '';
			$options['bg_image']['thumbnail'] = '';

			$options['bg_color'] = $options['content_bg_color'];
		}

		return $options;
	}

	/**
	 * Set the masonry grid ratio to 1.0, which is fallback value.
	 *
	 * @access private
	 * @since 5.5.0
	 * @param array $options The Global Options array.
	 * @return array The updated Global Options array.
	 */
	private function set_masonry_grid_ratio( $options ) {

		$options['masonry_grid_ratio'] = '1.0';

		return $options;
	}

	/**
	 * Set the blog archive grid values to their new names.
	 *
	 * @access private
	 * @since 5.5.0
	 * @param array $options The Global Options array.
	 * @return array The updated Global Options array.
	 */
	private function set_blog_archive_grid_options( $options ) {

		$options['blog_archive_grid_columns']        = isset( $options['blog_grid_columns'] ) ? $options['blog_grid_columns'] : '3';
		$options['blog_archive_grid_column_spacing'] = isset( $options['blog_grid_column_spacing'] ) ? $options['blog_grid_column_spacing'] : '40';

		$options['blog_grid_columns']        = '3';
		$options['blog_grid_column_spacing'] = '40';

		return $options;
	}
}
