<?php

namespace WebpConverter\Settings\Option;

use WebpConverter\Conversion\Method\GdMethod;

/**
 * {@inheritdoc}
 */
class ExtraFeaturesOption extends OptionAbstract {

	const OPTION_NAME                = 'features';
	const OPTION_VALUE_ONLY_SMALLER  = 'only_smaller';
	const OPTION_VALUE_KEEP_METADATA = 'keep_metadata';
	const OPTION_VALUE_CRON_ENABLED  = 'cron_enabled';
	const OPTION_VALUE_DEBUG_ENABLED = 'debug_enabled';

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
		return OptionAbstract::FORM_TYPE_ADVANCED;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string {
		return OptionAbstract::OPTION_TYPE_CHECKBOX;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Extra features', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		return __( 'Options allow you to enable new functionalities that will increase capabilities of plugin', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_available_values( array $settings ): array {
		return [
			self::OPTION_VALUE_ONLY_SMALLER  => __(
				'Automatic removal of files in output formats larger than original',
				'webp-converter-for-media'
			),
			self::OPTION_VALUE_KEEP_METADATA => __(
				'Keep images metadata stored in EXIF or XMP formats (unavailable for GD conversion method)',
				'webp-converter-for-media'
			),
			self::OPTION_VALUE_CRON_ENABLED  => __(
				'Enable cron to automatically convert images from outside Media Library (images from Media Library are converted immediately after upload)',
				'webp-converter-for-media'
			),
			self::OPTION_VALUE_DEBUG_ENABLED => __(
				'Log errors while converting to debug.log file (when debugging in WordPress is active)',
				'webp-converter-for-media'
			),
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_disabled_values( array $settings ): array {
		$values = [];
		if ( ( $settings[ ConversionMethodOption::OPTION_NAME ] ?? GdMethod::METHOD_NAME ) === GdMethod::METHOD_NAME ) {
			$values[] = self::OPTION_VALUE_KEEP_METADATA;
		}
		return $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null ) {
		$valid_values = [];
		if ( ! $current_value ) {
			return $valid_values;
		}

		foreach ( $current_value as $option_value ) {
			if ( array_key_exists( $option_value, $available_values ?: [] )
				&& ! in_array( $option_value, $disabled_values ?: [] ) ) {
				$valid_values[] = $option_value;
			}
		}

		return $valid_values;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_default_value( array $settings = null ): array {
		return [
			self::OPTION_VALUE_ONLY_SMALLER,
		];
	}
}
