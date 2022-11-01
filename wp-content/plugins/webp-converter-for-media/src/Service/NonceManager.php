<?php

namespace WebpConverter\Service;

/**
 * Manages generation and validation of Nonce values.
 */
class NonceManager {

	/**
	 * @var int
	 */
	private $nonce_lifetime = ( 24 * 60 * 60 );

	/**
	 * @var bool
	 */
	private $use_user;

	public function __construct( int $nonce_lifetime = null, bool $use_user = true ) {
		if ( $nonce_lifetime !== null ) {
			$this->nonce_lifetime = $nonce_lifetime;
		}
		$this->use_user = $use_user;
	}

	public function generate_nonce( string $nonce_action ): string {
		if ( ! $this->use_user ) {
			$user_id = (int) wp_get_current_user()->ID;
			wp_set_current_user( 0 );
		}

		add_filter( 'nonce_life', [ $this, 'set_nonce_lifetime' ] );
		$nonce_value = wp_create_nonce( $nonce_action );
		remove_filter( 'nonce_life', [ $this, 'set_nonce_lifetime' ] );

		if ( ! $this->use_user && isset( $user_id ) ) {
			wp_set_current_user( $user_id );
		}

		return $nonce_value;
	}

	public function verify_nonce( string $nonce_value, string $nonce_action ): bool {
		if ( ! $this->use_user ) {
			$user_id = (int) wp_get_current_user()->ID;
			wp_set_current_user( 0 );
		}

		add_filter( 'nonce_life', [ $this, 'set_nonce_lifetime' ] );
		$nonce_status = wp_verify_nonce( $nonce_value, $nonce_action );
		remove_filter( 'nonce_life', [ $this, 'set_nonce_lifetime' ] );

		if ( ! $this->use_user && isset( $user_id ) ) {
			wp_set_current_user( $user_id );
		}

		return (bool) $nonce_status;
	}

	/**
	 * @internal
	 */
	public function set_nonce_lifetime(): int {
		return $this->nonce_lifetime;
	}
}
