<?php

namespace WebpConverter\Settings\Option;

use WebpConverter\Loader\HtaccessLoader;
use WebpConverter\Loader\PassthruLoader;

/**
 * {@inheritdoc}
 */
class LoaderTypeOption extends OptionAbstract {

	const OPTION_NAME = 'loader_type';

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
		return OptionAbstract::OPTION_TYPE_RADIO;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Image loading mode', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		return __( 'By changing image loading mode it allows you to bypass some server configuration problems.', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_available_values( array $settings ): array {
		return [
			HtaccessLoader::LOADER_TYPE => sprintf(
			/* translators: %s: loader type */
				__( '%s (recommended)', 'webp-converter-for-media' ),
				__( 'via .htaccess', 'webp-converter-for-media' )
			),
			PassthruLoader::LOADER_TYPE => sprintf(
			/* translators: %s: loader type */
				__( '%s (without rewrites in .htaccess files or Nginx configuration)', 'webp-converter-for-media' ),
				'Pass Thru'
			),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null ) {
		if ( ! array_key_exists( $current_value, $available_values ?: [] )
			|| in_array( $current_value, $disabled_values ?: [] ) ) {
			return null;
		}

		return $current_value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_value( array $settings = null ): string {
		return HtaccessLoader::LOADER_TYPE;
	}
}
