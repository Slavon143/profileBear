<?php

namespace WebpConverter\Service;

/**
 * Manages generation of server paths.
 */
class PathsGenerator {

	/**
	 * Returns path to root directory of WordPress installation.
	 */
	public static function get_wordpress_root_path(): string {
		return apply_filters(
			'webpc_site_root',
			preg_replace( '/(\/|\\\\)/', DIRECTORY_SEPARATOR, dirname( wp_upload_dir()['basedir'], 2 ) )
		);
	}

	/**
	 * Returns path to DOCUMENT_ROOT, by default: "%{DOCUMENT_ROOT}/".
	 */
	public static function get_rewrite_root(): string {
		$root_document      = preg_replace( '/(\/|\\\\)/', '/', rtrim( $_SERVER['DOCUMENT_ROOT'], '\/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$root_document_real = preg_replace( '/(\/|\\\\)/', '/', rtrim( realpath( $_SERVER['DOCUMENT_ROOT'] ) ?: '', '\/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$root_wordpress     = preg_replace( '/(\/|\\\\)/', '/', rtrim( self::get_wordpress_root_path(), '\/' ) );

		$root_path   = trim( str_replace( $root_document_real ?: '', '', $root_wordpress ?: '' ), '\/' );
		$root_suffix = str_replace( '//', '/', sprintf( '/%s/', $root_path ) );

		return apply_filters(
			'webpc_htaccess_rewrite_root',
			( $root_document !== $root_document_real ) ? ( $root_wordpress . '/' ) : ( '%{DOCUMENT_ROOT}' . $root_suffix )
		);
	}

	/**
	 * Returns prefix used before "wp-content/uploads-webpc/", by default: "/".
	 */
	public static function get_rewrite_path(): string {
		$root_document_real = preg_replace( '/(\/|\\\\)/', '/', rtrim( realpath( $_SERVER['DOCUMENT_ROOT'] ) ?: '', '\/' ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$root_wordpress     = preg_replace( '/(\/|\\\\)/', '/', rtrim( self::get_wordpress_root_path(), '\/' ) );

		$root_path = trim( str_replace( $root_document_real ?: '', '', $root_wordpress ?: '' ), '\/' );

		return apply_filters(
			'webpc_htaccess_rewrite_path',
			str_replace( '//', '/', sprintf( '/%s/', $root_path ) )
		);
	}
}
