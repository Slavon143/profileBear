<?php

namespace WebpConverter\Settings\Option;

/**
 * {@inheritdoc}
 */
class MediaStatsOption extends OptionAbstract {

	const OPTION_NAME = 'media_stats';

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
		return OptionAbstract::OPTION_TYPE_TOGGLE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Optimization statistics', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		return sprintf(
		/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
			__( 'Show statistics in %1$sMedia Library%2$s', 'webp-converter-for-media' ),
			'<a href="' . admin_url( 'upload.php?mode=list' ) . '">',
			'</a>'
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_available_values( array $settings ) {
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null ) {
		return ( $current_value === 'yes' ) ? 'yes' : '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_value( array $settings = null ): string {
		return 'yes';
	}
}
