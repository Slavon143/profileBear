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
 * Handle migrations for Avada 6.2.
 *
 * @since 6.2.0
 */
class Avada_Upgrade_620 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @var string
	 */
	protected $version = '6.2.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 6.2.0
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 6.2.0
	 * @return void
	 */
	protected function migration_process() {
		$available_languages       = Fusion_Multilingual::get_available_languages();
		self::$available_languages = ( ! empty( $available_languages ) ) ? $available_languages : [ '' ];

		$this->migrate_options();
		$this->migrate_fusion_slider_responsive_typography();
	}

	/**
	 * Migrate options.
	 *
	 * @since 6.2.0
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_separator_options( $options );
		$options = $this->migrate_load_more_options( $options );
		$options = $this->migrate_responsive_typography_options( $options );
		$options = $this->migrate_first_featured_image_options( $options );
		$options = $this->migrate_icon_element_options( $options );
		$options = $this->migrate_countdown_options( $options );
		$options = $this->migrate_rollover_options( $options );
		$options = $this->migrate_load_block_styles_option( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_separator_options( $options );
			$options = $this->migrate_load_more_options( $options );
			$options = $this->migrate_responsive_typography_options( $options );
			$options = $this->migrate_first_featured_image_options( $options );
			$options = $this->migrate_icon_element_options( $options );
			$options = $this->migrate_countdown_options( $options );
			$options = $this->migrate_rollover_options( $options );
			$options = $this->migrate_load_block_styles_option( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Migrate button options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_separator_options( $options ) {
		if ( isset( $options['body_typography'] ) && isset( $options['body_typography']['font-size'] ) ) {
			$options['separator_icon_size'] = intval( $options['body_typography']['font-size'] );
		}
		return $options;
	}

	/**
	 * Migrate load more  options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_load_more_options( $options ) {

		// Blog element.
		if ( isset( $options['blog_load_more_posts_button_bg_color'] ) ) {
			$blog_color_obj  = Fusion_Color::new_color( Fusion_Sanitize::color( $options['blog_load_more_posts_button_bg_color'] ) );
			$blog_text_color = $this->get_readable_color( $blog_color_obj );
			$blog_color_obj  = $blog_color_obj->getNew( 'alpha', 0.8 );

			$options['blog_element_load_more_posts_hover_button_text_color'] = $blog_text_color;
			$options['blog_element_load_more_posts_hover_button_bg_color']   = $blog_color_obj->to_css( 'rgba' );
			$options['blog_element_load_more_posts_button_text_color']       = $blog_text_color;
			$options['blog_element_load_more_posts_button_bg_color']         = $options['blog_load_more_posts_button_bg_color'];
		}

		if ( isset( $options['portfolio_load_more_posts_button_bg_color'] ) ) {
			// Portfolio element.
			$portfolio_color_obj  = Fusion_Color::new_color( Fusion_Sanitize::color( $options['portfolio_load_more_posts_button_bg_color'] ) );
			$portfolio_text_color = $this->get_readable_color( $portfolio_color_obj );
			$portfolio_color_obj  = $portfolio_color_obj->getNew( 'alpha', 0.8 );

			$options['portfolio_element_load_more_posts_hover_button_text_color'] = $portfolio_text_color;
			$options['portfolio_element_load_more_posts_hover_button_bg_color']   = $portfolio_color_obj->to_css( 'rgba' );
			$options['portfolio_element_load_more_posts_button_text_color']       = $portfolio_text_color;
			$options['portfolio_element_load_more_posts_button_bg_color']         = $options['portfolio_load_more_posts_button_bg_color'];

			unset( $options['portfolio_load_more_posts_button_bg_color'] );
		}

		// Portfolio archive.
		if ( isset( $options['portfolio_archive_load_more_posts_button_bg_color'] ) ) {
			$portfolio_archive_color_obj  = Fusion_Color::new_color( Fusion_Sanitize::color( $options['portfolio_archive_load_more_posts_button_bg_color'] ) );
			$read_color_args              = [
				'threshold' => '0.547',
				'dark'      => '#fff',
				'light'     => '#333',
			];
			$portfolio_archive_text_color = $this->get_readable_color( $portfolio_archive_color_obj, $read_color_args );
			$portfolio_archive_color_obj  = $portfolio_archive_color_obj->getNew( 'alpha', 0.8 );

			$options['portfolio_archive_load_more_posts_hover_button_text_color'] = $portfolio_archive_text_color;
			$options['portfolio_archive_load_more_posts_hover_button_bg_color']   = $portfolio_archive_color_obj->to_css( 'rgba' );
			$options['portfolio_archive_load_more_posts_button_text_color']       = $portfolio_archive_text_color;
		}

		// Blog archive.
		if ( isset( $options['blog_load_more_posts_button_bg_color'] ) ) {
			$blog_archive_color_obj  = Fusion_Color::new_color( Fusion_Sanitize::color( $options['blog_load_more_posts_button_bg_color'] ) );
			$blog_archive_text_color = $this->get_readable_color( $blog_archive_color_obj );

			$blog_archive_color_obj = $blog_archive_color_obj->getNew( 'alpha', 0.8 );

			$options['blog_load_more_posts_hover_button_text_color'] = $blog_archive_text_color;
			$options['blog_load_more_posts_hover_button_bg_color']   = $blog_archive_color_obj->to_css( 'rgba' );
			$options['blog_load_more_posts_button_text_color']       = $blog_archive_text_color;
		}

		return $options;
	}

	/**
	 * Migrate first-featured-image option.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_first_featured_image_options( $options ) {
		if ( isset( $options['portfolio_disable_first_featured_image'] ) ) {
			$options['show_first_featured_image'] = $options['portfolio_disable_first_featured_image'];
		}

		return $options;
	}

	/**
	 * Migrate responsive typography options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Optionsgs array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_responsive_typography_options( $options ) {

		// If responsive-typography was disabled, set typography_sensitivity to 0.
		if ( ! isset( $options['typography_responsive'] ) || 0 === $options['typography_responsive'] || '0' === $options['typography_responsive'] ) {
			$options['typography_sensitivity'] = 0;
		}

		// Update value.
		$options['typography_sensitivity'] = min( 1, max( 0, ( 0.9 * floatval( $options['typography_sensitivity'] ) ) ) );

		return $options;
	}

	/**
	 * Migrate load more  options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @return void
	 */
	private function migrate_fusion_slider_responsive_typography() {

		$sliders = get_terms(
			[
				'taxonomy'   => 'slide-page',
				'hide_empty' => false,
			]
		);

		foreach ( $sliders as $slider ) {

			// Make sure we've got an object to work with.
			if ( ! is_object( $slider ) ) {
				continue;
			}

			// Migrate data.
			$sensitivity = fusion_data()->term_meta( $slider->term_id )->get( 'typo_sensitivity' );
			$sensitivity = ( '' === $sensitivity ) ? 0.6 : $sensitivity;
			$sensitivity = min( 1, max( 0, ( 0.9 * floatval( $sensitivity ) ) ) );
			fusion_data()->term_meta( $slider->term_id )->set( 'typo_sensitivity', $sensitivity );
		}
	}

	/**
	 * Gets a readable color based on threshold.
	 *
	 * @static
	 * @access public
	 * @since 6.2.0
	 * @param string $color_object The color object we'll be basing our calculations on.
	 * @param string $args         The arguments ['threshold'=>0.5,'dark'=>'#fff','light'=>'#333'].
	 * @return string
	 */
	private static function get_readable_color( $color_object, $args = [] ) {
		if ( ! is_array( $args ) ) {
			$args = [];
		}
		if ( ! isset( $args['threshold'] ) ) {
			$args['threshold'] = .547;
		}
		if ( ! isset( $args['light'] ) ) {
			$args['light'] = '#333';
		}
		if ( ! isset( $args['dark'] ) ) {
			$args['dark'] = '#fff';
		}
		if ( 1 > $args['threshold'] ) {
			$args['threshold'] = $args['threshold'] * 256;
		}

		$brightness = isset( $color_object->brightness['level'] ) ? $color_object->brightness['level'] : 150;

		return $args['threshold'] < $brightness ? $args['light'] : $args['dark'];
	}

	/**
	 * Migrate icon element options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_icon_element_options( $options ) {
		if ( isset( $options['icon_color'] ) ) {
			$options['icon_color_hover'] = $options['icon_color'];
		}
		if ( isset( $options['icon_circle_color'] ) ) {
			$options['icon_circle_color_hover'] = $options['icon_circle_color'];
		}
		if ( isset( $options['icon_border_color'] ) ) {
			$options['icon_border_color_hover'] = $options['icon_border_color'];
		}

		return $options;
	}

	/**
	 * Migrate countdown element options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_countdown_options( $options ) {
		if ( isset( $options['countdown_counter_text_color'] ) ) {
			$options['countdown_label_color'] = $options['countdown_counter_text_color'];
		}

		return $options;
	}

	/**
	 * Migrate rollover options.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_rollover_options( $options ) {
		$link_image_rollover = ( ! isset( $options['link_image_rollover'] ) || '1' === $options['link_image_rollover'] || 1 === $options['link_image_rollover'] || true === $options['link_image_rollover'] );
		$zoom_image_rollover = ( ! isset( $options['zoom_image_rollover'] ) || '1' === $options['zoom_image_rollover'] || 1 === $options['zoom_image_rollover'] || true === $options['zoom_image_rollover'] );

		$options['image_rollover_icons'] = 'linkzoom';
		if ( $link_image_rollover && ! $zoom_image_rollover ) {
			$options['image_rollover_icons'] = 'link';
		} elseif ( ! $link_image_rollover && $zoom_image_rollover ) {
			$options['image_rollover_icons'] = 'zoom';
		} elseif ( ! $link_image_rollover && ! $zoom_image_rollover ) {
			$options['image_rollover_icons'] = 'no';
		}

		return $options;
	}

	/**
	 * Sets the "load_block_styles" option to "auto" on update.
	 *
	 * @access private
	 * @since 6.2.0
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_load_block_styles_option( $options ) {
		if ( ! isset( $options['load_block_styles'] ) ) {
			$options['load_block_styles'] = 'auto';
		}
		return $options;
	}
}
