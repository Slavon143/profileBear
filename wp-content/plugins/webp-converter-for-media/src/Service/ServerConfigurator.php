<?php

namespace WebpConverter\Service;

/**
 * Manages required server configuration.
 */
class ServerConfigurator {

	/**
	 * @param int $value .
	 *
	 * @return void
	 */
	public function set_memory_limit( int $value = 2 ) {
		ini_set( 'memory_limit', sprintf( '%sG', $value ) ); // phpcs:ignore
	}

	/**
	 * @param int $seconds .
	 *
	 * @return void
	 */
	public function set_execution_time( int $seconds = 120 ) {
		if ( strpos( ini_get( 'disable_functions' ) ?: '', 'set_time_limit' ) === false ) {
			set_time_limit( $seconds );
		}
	}
}
