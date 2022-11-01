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
 * Handle migrations for Avada 5.3.0.
 *
 * @since 5.4.0
 */
class Avada_Upgrade_540 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 5.3.0
	 * @var string
	 */
	protected $version = '5.4.0';

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 5.4.0
	 * @return void
	 */
	protected function migration_process() {

		$this->migrate_slidingbar_link_color_hover();

		add_action( 'init', [ $this, 'migrate_fusion_slider_options' ], 20 );
	}

	/**
	 * Migrate sliding bar link hover color from primary color.
	 *
	 * @access private
	 * @since 5.4.0
	 * @return void
	 */
	private function migrate_slidingbar_link_color_hover() {
		$options = get_option( $this->option_name, [] );

		$options['slidingbar_link_color_hover'] = $options['primary_color'];

		update_option( $this->option_name, $options );
	}

	/**
	 * Migrate Avada Slider options.
	 *
	 * @access public
	 * @since 5.4.0
	 * @return void
	 */
	public function migrate_fusion_slider_options() {

		$args = [
			'taxonomy'   => 'slide-page',
			'number'     => 0,
			'hide_empty' => false,
		];

		$sliders = get_terms( $args );

		foreach ( $sliders as $slider ) {
			if ( isset( $slider->term_id ) ) {
				$slider_settings = get_option( 'taxonomy_' . $slider->term_id );

				$slider_settings['slider_indicator'] = '';

				if ( isset( $slider_settings['pagination_circles'] ) && $slider_settings['pagination_circles'] ) {
					$slider_settings['slider_indicator'] = 'pagination_circles';
					// In this case #000 is default.
					$slider_settings['slider_indicator_color'] = '#000000';
				}

				// Scroll down indicator has higher priority if 'pagination_circles' were enabled as well.
				if ( isset( $slider_settings['scroll_down_indicator'] ) && $slider_settings['scroll_down_indicator'] ) {
					$slider_settings['slider_indicator'] = 'scroll_down_indicator';

					if ( isset( $slider_settings['scroll_down_indicator_color'] ) ) {
						$slider_settings['slider_indicator_color'] = $slider_settings['scroll_down_indicator_color'];
					}
				}

				unset( $slider_settings['pagination_circles'] );
				unset( $slider_settings['scroll_down_indicator'] );
				unset( $slider_settings['scroll_down_indicator_color'] );

				// Save the option array.
				update_option( 'taxonomy_' . $slider->term_id, $slider_settings );
			}
		}

	}

}
