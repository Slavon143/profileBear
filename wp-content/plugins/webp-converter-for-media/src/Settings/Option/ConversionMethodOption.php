<?php

namespace WebpConverter\Settings\Option;

use WebpConverter\Conversion\Method\MethodFactory;
use WebpConverter\Repository\TokenRepository;

/**
 * {@inheritdoc}
 */
class ConversionMethodOption extends OptionAbstract {

	const OPTION_NAME = 'method';

	/**
	 * @var TokenRepository
	 */
	private $token_repository;

	/**
	 * Object of integration class supports all output formats.
	 *
	 * @var MethodFactory
	 */
	private $methods_integration;

	public function __construct( TokenRepository $token_repository ) {
		$this->token_repository    = $token_repository;
		$this->methods_integration = new MethodFactory();
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
		return __( 'Conversion method', 'webp-converter-for-media' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_notice_lines() {
		$notice = [
			__( 'The Remote server allows you to reduce the server load, because your images are converted by our server. This option is also useful when the server does not meet all of the plugin\'s technical requirements.', 'webp-converter-for-media' ),
		];

		if ( $this->token_repository->get_token()->get_token_value() === null ) {
			$notice[] = sprintf(
			/* translators: %1$s: open anchor tag, %2$s: close anchor tag */
				__( '%1$sUpgrade to PRO%2$s', 'webp-converter-for-media' ),
				'<a href="https://url.mattplugins.com/converter-field-conversion-method-info" target="_blank">',
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
		return $this->methods_integration->get_methods();
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return string[]
	 */
	public function get_disabled_values( array $settings ): array {
		$methods           = $this->methods_integration->get_methods();
		$methods_available = $this->methods_integration->get_available_methods();
		return array_keys( array_diff( $methods, $methods_available ) );
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
		$methods_available = $this->methods_integration->get_available_methods();
		return array_keys( $methods_available )[0] ?? '';
	}
}
