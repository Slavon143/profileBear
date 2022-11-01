<?php

namespace WebpConverter\Conversion\Format;

use WebpConverter\Conversion\Method\RemoteMethod;
use WebpConverter\Repository\TokenRepository;

/**
 * Supports AVIF as output format for images.
 */
class AvifFormat extends FormatAbstract {

	const FORMAT_EXTENSION = 'avif';

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	public function __construct( TokenRepository $token_repository = null ) {
		$this->token_repository = $token_repository ?: new TokenRepository();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_extension(): string {
		return self::FORMAT_EXTENSION;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_mime_type(): string {
		return 'image/avif';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		if ( $this->token_repository->get_token()->get_valid_status() ) {
			return 'AVIF';
		}

		return sprintf(
			'%1$s (%2$s)',
			'AVIF',
			sprintf(
			/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
				__( 'available in %1$sthe PRO version%2$s', 'webp-converter-for-media' ),
				'<a href="https://url.mattplugins.com/converter-field-output-format-avif-upgrade" target="_blank">',
				'</a>'
			)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available( string $conversion_method ): bool {
		return ( $this->token_repository->get_token()->get_valid_status() && ( $conversion_method === RemoteMethod::METHOD_NAME ) );
	}
}
