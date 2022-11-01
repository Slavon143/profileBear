<?php

class AWB_Importer_Logger extends WP_Importer_Logger {
	/**
	 * Logs with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 * @return null
	 */
	public function log( $level, $message, array $context = array() ) {
		switch ( $level ) {
			case 'emergency':
			case 'alert':
			case 'critical':
				if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
					error_log( 'Sorry, there has been an error. ' . $message );
				}
				break;

			case 'error':
			case 'warning':
			case 'notice':
			case 'info':
				if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
					error_log( $message );
				}
				break;

			case 'debug':
				if ( defined( 'IMPORT_DEBUG' ) && IMPORT_DEBUG ) {
					error_log( 'Avada Importer Debug: ' . $message );
				}
				break;
		}
	}
}
