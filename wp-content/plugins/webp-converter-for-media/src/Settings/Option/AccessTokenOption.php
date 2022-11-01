<?php

namespace WebpConverter\Settings\Option;

use WebpConverter\Repository\TokenRepository;

/**
 * {@inheritdoc}
 */
class AccessTokenOption extends OptionAbstract {

	const OPTION_NAME = 'access_token';

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
		return OptionAbstract::FORM_TYPE_SIDEBAR;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_type(): string {
		return OptionAbstract::OPTION_TYPE_TOKEN;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label(): string {
		return __( 'Access Token', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_info(): string {
		if ( $this->token_repository->get_token()->get_valid_status() ) {
			return sprintf(
			/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
				__( 'To manage your subscriptions, please visit %1$sour website%2$s.', 'webp-converter-for-media' ),
				'<a href="https://url.mattplugins.com/converter-field-access-token-management" target="_blank">',
				'</a>'
			);
		}

		return sprintf(
		/* translators: %1$s: open anchor tag, %2$s: close anchor tag, %3$s: open anchor tag, %4$s: close anchor tag */
			__( 'Provide a valid token to access %1$sthe PRO functionalities%2$s. You can find out more about it %3$shere%4$s.', 'webp-converter-for-media' ),
			'<a href="https://url.mattplugins.com/converter-field-access-token-pro-features" target="_blank">',
			'</a>',
			'<a href="https://url.mattplugins.com/converter-field-access-token-find-more" target="_blank">',
			'</a>'
		);
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
	 */
	public function get_valid_value( $current_value, array $available_values = null, array $disabled_values = null ): string {
		return sanitize_text_field( $current_value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_value( array $settings = null ): string {
		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_public_value( $current_value = null ) {
		if ( $current_value === null ) {
			return $current_value;
		}

		return substr( $current_value, 0, 32 ) . str_repeat( '*', max( ( strlen( $current_value ) - 32 ), 0 ) );
	}
}
