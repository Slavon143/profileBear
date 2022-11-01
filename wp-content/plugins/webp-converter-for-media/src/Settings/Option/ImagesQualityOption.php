<?php

namespace WebpConverter\Settings\Option;

/**
 * {@inheritdoc}
 */
class ImagesQualityOption extends OptionAbstract {

	const OPTION_NAME = 'quality';

	/**
	 * {@inheritdoc}
	 */
	public function get_name(): string {
		return self::OPTION_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_form_name(): string {
		return OptionAbstract::FORM_TYPE_BASIC;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string {
		return OptionAbstract::OPTION_TYPE_QUALITY;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Conversion strategy', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_available_values( array $settings ): array {
		$levels = apply_filters(
			'webpc_option_quality_levels',
			[ '75', '80', '85', '90', '95' ]
		);

		$values = [];
		foreach ( $levels as $level ) {
			$level_value = (int) $level;
			if ( ( $level_value > 0 ) && ( $level_value <= 100 ) ) {
				$values[ $level_value ] = sprintf( '%s%%', $level_value );
			}
		}
		ksort( $values );
		return $values;
	}

	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null ) {
		if ( $current_value === '100' ) {
			return '95';
		} elseif ( ! array_key_exists( $current_value, $available_values ?: [] )
			|| in_array( $current_value, $disabled_values ?: [] ) ) {
			return null;
		}

		return $current_value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_value( array $settings = null ): string {
		return '85';
	}
}
