<?php

namespace WebpConverter\Settings\Option;

use WebpConverter\Repository\TokenRepository;

/**
 * {@inheritdoc}
 */
class ImageResizeOption extends OptionAbstract {

	const OPTION_NAME = 'image_resize';

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository = $token_repository;
	}

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
		return OptionAbstract::OPTION_TYPE_IMAGE_SIZE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Maximum image dimensions', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		$message = __( 'Resize large images to maximum dimensions in pixels during image conversion, keeping the original aspect ratio', 'webp-converter-for-media' );

		if ( ! $this->token_repository->get_token()->get_valid_status() ) {
			return sprintf(
				'%1$s (%2$s)',
				$message,
				sprintf(
				/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
					__( 'available in %1$sthe PRO version%2$s', 'webp-converter-for-media' ),
					'<a href="https://url.mattplugins.com/converter-field-image-resize-upgrade" target="_blank">',
					'</a>'
				)
			);
		}
		return $message;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_lines() {
		$size   = $this->get_max_image_size();
		$notice = [
			sprintf(
			/* translators: %1$s: width value, %2$s: height value */
				__( 'Reduce the weight of converted images that are larger than their largest image size (thumbnail size) used in your theme even more. The recommended value for you is %1$s x %2$s pixels.', 'webp-converter-for-media' ),
				$size['width'],
				$size['height']
			),
		];

		if ( $this->token_repository->get_token()->get_token_value() === null ) {
			$notice[] = sprintf(
			/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
				__( '%1$sUpgrade to PRO%2$s', 'webp-converter-for-media' ),
				'<a href="https://url.mattplugins.com/converter-field-image-resize-info" target="_blank">',
				' <span class="dashicons dashicons-arrow-right-alt"></span></a>'
			);
		}
		return $notice;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_available_values( array $settings ): array {
		return [];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_disabled_values( array $settings ): array {
		if ( ! $this->token_repository->get_token()->get_valid_status() ) {
			return [ 'yes' ];
		}
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null ) {
		if ( ! is_array( $current_value ) ) {
			return [ '', '', '' ];
		}

		$value_min = intval( $current_value[1] ?? '' );
		$value_max = intval( $current_value[2] ?? '' );

		return [
			( ( $current_value[0] ?? '' ) === 'yes' ) ? 'yes' : '',
			( $value_min <= 1 ) ? '' : (string) $value_min,
			( $value_max <= 1 ) ? '' : (string) $value_max,
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return mixed[]
	 */
	public function get_default_value( array $settings = null ): array {
		$size = $this->get_max_image_size();
		return [ '', $size['width'], $size['height'] ];
	}

	/**
	 * @return int[]
	 */
	private function get_max_image_size(): array {
		$sizes = ( function_exists( 'wp_get_registered_image_subsizes' ) )
			? wp_get_registered_image_subsizes()
			: wp_get_additional_image_sizes();

		$column_width  = array_column( $sizes, 'width' );
		$column_height = array_column( $sizes, 'height' );

		return [
			'width'  => ( $column_width ) ? max( $column_width ) : 0,
			'height' => ( $column_height ) ? max( $column_height ) : 0,
		];
	}
}
