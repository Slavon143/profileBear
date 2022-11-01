<?php
/**
 * The Patcher caching implementation.
 *
 * @package Fusion-Library
 * @subpackage Fusion-Patcher
 */

/**
 * Caches handler for Fusion_Patcher.
 *
 * @since 1.0.0
 */
final class Fusion_Patcher_Cache {

	/**
	 * Transient name.
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var string
	 */
	protected $transient_name = 'fusion_patches';

	/**
	 * Cache duration (in seconds).
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var int
	 */
	protected $cache_duration = 1800;

	/**
	 * Cached patches.
	 * The cache is formatted like
	 * [ 'context1' => [...patches...], 'context2' => [...patches...] ]
	 *
	 * @access protected
	 * @since 1.0.0
	 * @var array
	 */
	protected $cached_patches = [];

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->cached_patches = get_site_transient( $this->transient_name );

	}

	/**
	 * Caches patches for a specific product.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args Arguments array inherited from Fusion_Patcher.
	 * @param array $data The data we want to add to the cache.
	 */
	public function set_cache( $args = [], $data = [] ) {

		// Early exit if $args['context'] is not provided.
		if ( empty( $args ) || ! isset( $args['context'] ) ) {
			return;
		}
		// Make sure that cached patches are formatted as an array.
		if ( false === $this->cached_patches ) {
			$this->cached_patches = [];
		}
		// Cache the patches.
		$this->cached_patches[ $args['context'] ] = $data;
		set_site_transient( $this->transient_name, $this->cached_patches, $this->cache_duration );
		Fusion_Patcher_Checker::reset_cache();

	}

	/**
	 * Gets caches.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args Arguments array inherited from Fusion_Patcher.
	 * @return array|false Returns false on fail, otherwise an array/
	 */
	public function get_cache( $args = [] ) {

		// If nothing is cached, return false.
		if ( false === $this->cached_patches ) {
			return false;
		}

		$patches = [];

		// If no arguments are provided then get ALL patches.
		if ( empty( $args ) || ! isset( $args['context'] ) ) {
			// No patches were found.
			if ( ! $this->cached_patches ) {
				return [];
			}
			foreach ( $this->cached_patches as $context => $context_patches ) {
				if ( ! is_array( $context_patches ) ) {
					$context_patches = [];
				}
				$patches = array_merge( $patches, $context_patches );
			}
			$patches = array_unique( $patches );
			sort( $patches );
			return $patches;
		}

		// If we got this far, we need patches for a specific context.
		if ( isset( $this->cached_patches[ $args['context'] ] ) ) {
			return $this->cached_patches[ $args['context'] ];
		}
		// Nothing found, return an empty array.
		return [];
	}

	/**
	 * Resets caches.
	 * If $args is provided, then only the specific patches will be reset.
	 * If $args is empty, ALL patches will be reset.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param array $args Arguments array inherited from Fusion_Patcher.
	 */
	public function reset_caches( $args = [] ) {

		if ( isset( $args['context'] ) ) {
			if ( isset( $this->cached_patches[ $args['context'] ] ) ) {
				unset( $this->cached_patches[ $args['context'] ] );
				set_site_transient( $this->transient_name, $this->cached_patches, $this->cache_duration );
			}
			Fusion_Patcher_Checker::reset_cache();
			return;
		}
		Fusion_Patcher_Checker::reset_cache();
		delete_site_transient( $this->transient_name );
		delete_site_option( 'fusion_applied_patches' );
		delete_site_option( 'fusion_failed_patches' );
		delete_site_transient( Fusion_Patcher_Checker::$transient_name );
	}
}
