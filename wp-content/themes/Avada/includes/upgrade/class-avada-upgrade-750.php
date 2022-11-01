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
 * Handle migrations for Avada 7.5
 *
 * @since 7.5
 */
class Avada_Upgrade_750 extends Avada_Upgrade_Abstract {

	/**
	 * The version.
	 *
	 * @access protected
	 * @since 7.2
	 * @var string
	 */
	protected $version = '7.5.0';

	/**
	 * An array of all available languages.
	 *
	 * @static
	 * @access private
	 * @since 7.5
	 * @var array
	 */
	private static $available_languages = [];

	/**
	 * The actual migration process.
	 *
	 * @access protected
	 * @since 7.5
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
	 * @since 7.5
	 * @access protected
	 */
	protected function migrate_options() {
		$available_langs = self::$available_languages;

		$options = get_option( $this->option_name, [] );
		$options = $this->migrate_css_combination( $options );
		$options = $this->enable_avada_studio( $options );
		$options = $this->icon_border_radius( $options );
		$options = $this->migrate_buttons( $options );
		$options = $this->migrate_checklist( $options );

		update_option( $this->option_name, $options );

		foreach ( $available_langs as $language ) {

			// Skip langs that are already done.
			if ( '' === $language ) {
				continue;
			}

			$options = get_option( $this->option_name . '_' . $language, [] );
			$options = $this->migrate_css_combination( $options );
			$options = $this->enable_avada_studio( $options );
			$options = $this->icon_border_radius( $options );
			$options = $this->migrate_buttons( $options );

			update_option( $this->option_name . '_' . $language, $options );
		}
	}

	/**
	 * Sets the thrid party asset combination to enabled for users updating.
	 *
	 * @access private
	 * @since 7.5
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_css_combination( $options ) {
		if ( isset( $options['css_cache_method'] ) && 'file' === $options['css_cache_method'] ) {
			$options['css_combine_third_party_assets'] = '1';
		}

		return $options;
	}

	/**
	 * Migrate global button size to individual options.
	 *
	 * @access private
	 * @since 7.5
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_buttons( $options ) {

		// Set new size options.
		$button_size = isset( $options['button_size'] ) ? strtolower( $options['button_size'] ) : 'large';
		if ( 'xlarge' === $button_size ) {
			$options['button_font_size']   = '18px';
			$options['button_line_height'] = '21px';
			$options['button_padding']     = [
				'top'    => '17px',
				'right'  => '40px',
				'bottom' => '17px',
				'left'   => '40px',
			];

			$options['qty_font_size'] = '18px';
			$options['qty_size']      = [
				'width'  => '55px',
				'height' => '53px',
			];
		} elseif ( 'large' === $button_size ) {
			$options['button_font_size']   = '14px';
			$options['button_line_height'] = '17px';
			$options['button_padding']     = [
				'top'    => '13px',
				'right'  => '29px',
				'bottom' => '13px',
				'left'   => '29px',
			];

			$options['qty_font_size'] = '14px';
			$options['qty_size']      = [
				'width'  => '42px',
				'height' => '40px',
			];
		} elseif ( 'medium' === $button_size ) {
			$options['button_font_size']   = '13px';
			$options['button_line_height'] = '16px';
			$options['button_padding']     = [
				'top'    => '11px',
				'right'  => '23px',
				'bottom' => '11px',
				'left'   => '23px',
			];

			$options['qty_font_size'] = '13px';
			$options['qty_size']      = [
				'width'  => '38px',
				'height' => '36px',
			];
		} elseif ( 'small' === $button_size ) {
			$options['button_font_size']   = '12px';
			$options['button_line_height'] = '14px';
			$options['button_padding']     = [
				'top'    => '9px',
				'right'  => '20px',
				'bottom' => '9px',
				'left'   => '20px',
			];

			$options['qty_font_size'] = '12px';
			$options['qty_size']      = [
				'width'  => '33px',
				'height' => '31px',
			];
		} else {
			$options['button_font_size']   = '14px';
			$options['button_line_height'] = '1';
			$options['button_padding']     = [
				'top'    => '13px',
				'right'  => '29px',
				'bottom' => '13px',
				'left'   => '29px',
			];

			$options['qty_font_size'] = '13px';
			$options['qty_size']      = [
				'width'  => '38px',
				'height' => '36px',
			];
		}

		// New gradient options.
		if ( ! isset( $options['button_gradient_start'] ) ) {
			$options['button_gradient_start'] = '0';
		}
		if ( ! isset( $options['button_gradient_end'] ) ) {
			$options['button_gradient_end'] = '100';
		}
		if ( ! isset( $options['button_gradient_type'] ) ) {
			$options['button_gradient_type'] = 'linear';
		}
		if ( ! isset( $options['button_gradient_angle'] ) ) {
			$options['button_gradient_angle'] = '180';
		}

		// Split border size into 4.
		if ( isset( $options['button_border_width'] ) && is_string( $options['button_border_width'] ) ) {
			$border_size                    = Fusion_Sanitize::get_value_with_unit( $options['button_border_width'] );
			$options['button_border_width'] = [
				'top'    => $border_size,
				'right'  => $border_size,
				'bottom' => $border_size,
				'left'   => $border_size,
			];
		}

		// Split border radius into 4.
		if ( isset( $options['button_border_radius'] ) && is_string( $options['button_border_radius'] ) ) {
			if ( 'round' === $options['button_border_radius'] ) {
				$border_radius = '50%';
			} else {
				$border_radius = Fusion_Sanitize::get_value_with_unit( $options['button_border_radius'] );
			}
			$options['button_border_radius'] = [
				'top_left'     => $border_radius,
				'top_right'    => $border_radius,
				'bottom_left'  => $border_radius,
				'bottom_right' => $border_radius,
			];
		}

		if ( isset( $options['button_bevel_color'] ) ) {
			$options['button_bevel_color_hover'] = $options['button_bevel_color'];
		}
		return $options;
	}

	/**
	 * Sets Avada Studio Status.
	 *
	 * @access private
	 * @since 7.5
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function enable_avada_studio( $options ) {
		$options['status_avada_studio'] = '1';

		return $options;
	}

	/**
	 * Sets Icon brder radius.
	 *
	 * @access private
	 * @since 7.5
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function icon_border_radius( $options ) {
		$options['icon_border_radius'] = [
			'top_left'     => '50%',
			'top_right'    => '50%',
			'bottom_left'  => '50%',
			'bottom_right' => '50%',
		];

		return $options;
	}

	/**
	 * Migrate global checklist odd/even row background.
	 *
	 * @access private
	 * @since 7.5
	 * @param array $options The Global Options array.
	 * @return array         The updated Global Options array.
	 */
	private function migrate_checklist( $options ) {
		if ( ! isset( $options['checklist_odd_row_bgcolor'] ) ) {
			$options['checklist_odd_row_bgcolor'] = 'rgba(255,255,255,0)';
		}
		if ( ! isset( $options['checklist_even_row_bgcolor'] ) ) {
			$options['checklist_even_row_bgcolor'] = 'rgba(255,255,255,0)';
		}
		if ( ! isset( $options['checklist_item_padding'] ) ) {
			$options['checklist_item_padding'] = [
				'top'    => '0.35em',
				'right'  => '0',
				'bottom' => '0.35em',
				'left'   => '0',
			];
		}
		if ( isset( $options['body_typography'] ) ) {
			if ( ! isset( $options['checklist_text_color'] ) ) {
				$options['checklist_text_color'] = $options['body_typography']['color'];
			}
		}
		return $options;
	}
}
