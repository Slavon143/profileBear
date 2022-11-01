<?php

namespace WebpConverter\Conversion\Cron;

/**
 * Stores data related to automatic image conversion.
 */
class CronStatusManager {

	const CRON_PATHS_TRANSIENT         = 'webpc_cron_paths';
	const CRON_PATHS_SKIPPED_TRANSIENT = 'webpc_cron_paths_skipped';
	const CRON_STATUS_LOCKED_TRANSIENT = 'webpc_cron_locked';
	const CRON_REQUEST_ID_TRANSIENT    = 'webpc_cron_request_id';
	const CRON_PATHS_LIMIT             = 1000;

	/**
	 * @param string[] $paths           .
	 * @param bool     $use_paths_limit .
	 *
	 * @return void
	 */
	public function set_paths_to_conversion( array $paths, bool $use_paths_limit = true ) {
		set_site_transient(
			self::CRON_PATHS_TRANSIENT,
			( $use_paths_limit ) ? array_slice( $paths, 0, self::CRON_PATHS_LIMIT ) : $paths,
			3600
		);
	}

	/**
	 * @param string[] $paths .
	 *
	 * @return void
	 */
	public function set_paths_skipped( array $paths ) {
		$counter = max( ( count( $paths ) - self::CRON_PATHS_LIMIT ), 0 );
		set_site_transient( self::CRON_PATHS_SKIPPED_TRANSIENT, $counter, 3600 );
	}

	/**
	 * @return string[]
	 */
	public function get_paths_to_conversion(): array {
		$paths = get_site_transient( self::CRON_PATHS_TRANSIENT );
		return $paths ?: [];
	}

	public function get_paths_counter(): int {
		$paths_count   = count( $this->get_paths_to_conversion() );
		$paths_skipped = get_site_transient( self::CRON_PATHS_SKIPPED_TRANSIENT ) ?: 0;
		return ( $paths_count + $paths_skipped );
	}

	/**
	 * @param bool $new_status         .
	 * @param bool $is_long_expiration .
	 *
	 * @return void
	 */
	public function set_conversion_status_locked( bool $new_status = true, bool $is_long_expiration = false ) {
		set_site_transient(
			self::CRON_STATUS_LOCKED_TRANSIENT,
			( $new_status ) ? 'yes' : null,
			( $is_long_expiration ) ? 900 : 60
		);
		if ( $new_status === true ) {
			$this->reset_conversion_request_id();
		}
	}

	public function is_conversion_locked(): bool {
		return ( get_site_transient( self::CRON_STATUS_LOCKED_TRANSIENT ) === 'yes' );
	}

	public function refresh_conversion_request_id(): string {
		$request_id = uniqid( '', true );
		set_site_transient( self::CRON_REQUEST_ID_TRANSIENT, $request_id, 60 );
		return $request_id;
	}

	/**
	 * @return void
	 */
	public function reset_conversion_request_id() {
		set_site_transient( self::CRON_REQUEST_ID_TRANSIENT, null );
	}

	/**
	 * @return string|null
	 */
	public function get_conversion_request_id() {
		$request_id = get_site_transient( self::CRON_REQUEST_ID_TRANSIENT );
		return $request_id ?: null;
	}
}
