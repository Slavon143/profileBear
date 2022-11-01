<?php

namespace WebpConverter\Exception;

/**
 * {@inheritdoc}
 */
class ImageAnimatedException extends ExceptionAbstract {

	const ERROR_MESSAGE = '"%s" is an unsupported animated image file.';
	const ERROR_CODE    = 'invalid_animated_image';

	/**
	 * {@inheritdoc}
	 */
	public function get_error_message( array $values ): string {
		return sprintf( self::ERROR_MESSAGE, $values[0] );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_error_status(): string {
		return self::ERROR_CODE;
	}
}
