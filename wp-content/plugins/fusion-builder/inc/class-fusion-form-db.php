<?php // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
/**
 * Handles the database table manipulation.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Handles the database table.
 *
 * @since 3.1
 */
class Fusion_Form_DB {

	/**
	 * The Constructor.
	 *
	 * @since 3.1
	 * @access public
	 */
	public function __construct() {
	}

	/**
	 * Get cached $wpdb->query().
	 *
	 * An alias of the get_cached_query abstraction.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $query The query we want to cache.
	 * @return mixed Returns the query results.
	 */
	public function query( $query ) {
		return $this->get_cached_query( 'query', $query );
	}

	/**
	 * Get cached $wpdb->get_var().
	 *
	 * An alias of the get_cached_query abstraction.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $query The query we want to cache.
	 * @return mixed Returns the query results.
	 */
	public function get_var( $query ) {
		return $this->get_cached_query( 'get_var', $query );
	}

	/**
	 * Get cached $wpdb->get_results().
	 *
	 * An alias of the get_cached_query abstraction.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $query The query we want to cache.
	 * @return mixed Returns the query results.
	 */
	public function get_results( $query ) {
		return $this->get_cached_query( 'get_results', $query );
	}

	/**
	 * Get cached query.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $method The $wpdb method we want to run.
	 * @param string $query  The query we want to cache.
	 * @return mixed Returns the query results.
	 */
	public function get_cached_query( $method, $query ) {
		global $wpdb;

		// Build a unique cache key for this query.
		$cache_key = "fusion_builder_form_{$method}_" . md5( $query );

		// Check if we have a cached result for this query.
		$result = wp_cache_get( $cache_key );

		// No cache was found, run the query and cache it.
		if ( false === $result ) {
			switch ( $method ) {
				case 'get_var':
					$result = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;

				case 'get_results':
					$result = $wpdb->get_results( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;

				default:
					$result = $wpdb->query( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
					break;
			}

			// Set the cache.
			wp_cache_set( $cache_key, $result );
		}

		return $result;
	}
}
