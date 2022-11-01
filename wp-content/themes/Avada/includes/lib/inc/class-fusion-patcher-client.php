<?php
/**
 * Gets tha patches.
 *
 * @package Fusion-Library
 * @subpackage Fusion-Patcher
 */

/**
 * Handles getting patches remotely and preparing them for Avada.
 *
 * @since 1.0.0
 */
class Fusion_Patcher_Client {

	/**
	 * Patches array.
	 *
	 * @var bool|array
	 */
	public static $patches;

	/**
	 * The URL of the patches remote server.
	 *
	 * @static
	 * @var string
	 */
	public static $remote_patches_uri = FUSION_UPDATES_URL . '/avada_patch/';

	/**
	 * Gets an array of all our patches.
	 * If we have these cached then use caches,
	 * otherwise query the server.
	 *
	 * @param array $args An array of arguments inherited from Fusion_Patcher.
	 * @return array
	 */
	public static function get_patches( $args = [] ) {
		// Get a new instance of this object.
		$client = new self();
		// Set the $args property.
		$client->args = $args;
		// Get the patches.
		if ( $client->get_cached() ) {
			self::$patches = $client->get_cached();
		} else {
			self::$patches = $client->query_patch_server();
			// Cache the patches.
			$client->cache_response();
		}
		// Returns a formatted array of patches.
		return $client->prepare_patches( self::$patches );
	}

	/**
	 * Queries the patches server for a list of patches.
	 *
	 * @return bool|array
	 */
	private function query_patch_server() {
		global $is_apache, $is_IIS, $wp_version;
		$args = [
			'limit' => true,
		];

		if ( defined( 'AVADA_VERSION' ) ) {
			$args['avada_version'] = AVADA_VERSION;
		}
		if ( defined( 'FUSION_CORE_VERSION' ) ) {
			$args['fusion_core_version'] = FUSION_CORE_VERSION;
		}
		if ( defined( 'FUSION_BUILDER_VERSION' ) ) {
			$args['fusion_builder_version'] = FUSION_BUILDER_VERSION;
		}

		// Pass on reg data just in case.
		if ( class_exists( 'Avada' ) ) {
			if ( Avada()->registration->is_registered() ) {
				$args['code'] = Avada()->registration->get_purchase_code();
			} elseif ( Avada()->registration->legacy_support() ) {
				$args['token'] = Avada()->registration->get_token();
			}
		}

		// Bypass cache if forced.
		if ( isset( $_GET['reset_transient'] ) && '1' === $_GET['reset_transient'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$args['avada-api'] = 1;
		}

		// Build the remote server URL using the provided version.
		$url = add_query_arg( $args, self::$remote_patches_uri );

		// Get the server response.
		$response = wp_remote_get(
			$url,
			[
				'user-agent' => 'fusion-patcher-client',
			]
		);

		// Return false if we couldn't get to the server.
		if ( is_wp_error( $response ) ) {
			/* translators: Update server url. */
			new Fusion_Patcher_Admin_Notices( 'server-unreachable', sprintf( esc_html__( 'The ThemeFusion patches server could not be reached. Please contact your host to unblock the "%s" domain.', 'Avada' ), FUSION_UPDATES_URL ) );
			return false;
		}

		// Return false if the response does not have a body.
		if ( ! isset( $response['body'] ) || 200 !== $response['response']['code'] ) {
			return false;
		}
		$json = $response['body'];

		// Response may have comments from caching plugins making it invalid.
		if ( false !== strpos( $response['body'], '<!--' ) ) {
			$json = explode( '<!--', $json );
			return json_decode( $json[0] );
		}
		return json_decode( $json );
	}

	/**
	 * Decodes patches if needed.
	 *
	 * @return array
	 */
	private function prepare_patches() {
		self::$patches = (array) self::$patches;
		$patches       = [];

		if ( ! empty( self::$patches ) ) {
			foreach ( self::$patches as $patch_id => $patch_args ) {
				$patches[ $patch_id ] = (array) $patch_args;
				if ( empty( $patch_args ) ) {
					continue;
				}
				foreach ( $patch_args as $key => $patch ) {
					$patches[ $patch_id ][ $key ] = (array) $patch;
					foreach ( $patches[ $patch_id ]['patch'] as $patch_key => $args ) {
						$args                                        = (array) $args;
						$args['reference']                           = fusion_decode_input( $args['reference'] );
						$patches[ $patch_id ]['patch'][ $patch_key ] = $args;
					}
				}
			}
		}
		return $patches;
	}

	/**
	 * Gets the cached patches.
	 */
	private function get_cached() {
		$cache = new Fusion_Patcher_Cache();
		// Force getting new options from the server if needed.
		if ( $_GET && isset( $_GET['fusion-reset-cached-patches'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$cache->reset_caches();
			return false;
		}
		return $cache->get_cache( $this->args );
	}

	/**
	 * Caches the patches.
	 *
	 * @access private
	 * @since 1.0.0
	 * @return void
	 */
	private function cache_response() {

		if ( false !== self::$patches && ! empty( self::$patches ) ) {
			$cache = new Fusion_Patcher_Cache();
			$cache->set_cache( $this->args, self::$patches );
		}
	}
}
