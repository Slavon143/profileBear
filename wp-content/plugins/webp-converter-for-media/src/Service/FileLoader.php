<?php

namespace WebpConverter\Service;

use WebpConverter\Loader\PassthruLoader;
use WebpConverter\PluginData;
use WebpConverter\PluginInfo;

/**
 * Returns size of image downloaded based on server path or URL.
 */
class FileLoader {

	/**
	 * @var PluginInfo
	 */
	private $plugin_info;

	/**
	 * @var PluginData
	 */
	private $plugin_data;

	public function __construct( PluginInfo $plugin_info, PluginData $plugin_data ) {
		$this->plugin_info = $plugin_info;
		$this->plugin_data = $plugin_data;
	}

	/**
	 * Checks size of file by sending request using active image loader.
	 *
	 * @param string   $url         URL of image.
	 * @param bool     $set_headers Whether to send headers to confirm that browser supports WebP?
	 * @param int|null $ver_param   Additional GET param.
	 *
	 * @return int Size of retrieved file.
	 */
	public function get_file_size_by_url( string $url, bool $set_headers = true, int $ver_param = null ): int {
		$headers = [
			'Accept: image/webp',
			'Referer: ' . $this->plugin_info->get_plugin_directory_url(),
		];

		$image_url = ( new PassthruLoader( $this->plugin_info, $this->plugin_data ) )->update_image_urls( $url, true );
		if ( $ver_param !== null ) {
			$image_url = add_query_arg( 'ver', $ver_param, $image_url );
		}
		$image_url = apply_filters( 'webpc_debug_image_url', $image_url );

		return self::get_file_size_for_loaded_file( $image_url, ( $set_headers ) ? $headers : [] );
	}

	/**
	 * Returns size of file.
	 *
	 * @param string $path Server path of file.
	 *
	 * @return int Size of file.
	 */
	public function get_file_size_by_path( string $path ): int {
		return ( file_exists( $path ) ) ? ( filesize( $path ) ?: 0 ) : 0;
	}

	/**
	 * Checks size of file by sending cURL request.
	 *
	 * @param string   $url     URL of image.
	 * @param string[] $headers Headers for cURL connection.
	 *
	 * @return int Size of retrieved file.
	 */
	private function get_file_size_for_loaded_file( string $url, array $headers ): int {
		foreach ( wp_get_nocache_headers() as $header_key => $header_value ) {
			$headers[] = sprintf( '%s: %s', $header_key, $header_value );
		}

		$ch = curl_init( $url );
		if ( $ch === false ) {
			return 0;
		}

		if ( isset( $_SERVER['PHP_AUTH_USER'] ) && isset( $_SERVER['PHP_AUTH_PW'] ) ) {
			curl_setopt( $ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC );
			curl_setopt( $ch, CURLOPT_USERPWD, sprintf( '%1$s:%2$s', $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		}

		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
		curl_setopt( $ch, CURLOPT_FRESH_CONNECT, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		$response = curl_exec( $ch );
		$code     = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		curl_close( $ch );

		return ( $code === 200 )
			? strlen( is_string( $response ) ? $response : '' )
			: 0;
	}
}
