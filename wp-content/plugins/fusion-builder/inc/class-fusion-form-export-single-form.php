<?php
/**
 * Handles the single form export.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Create a new class to handle single form export through WP Export.
 *
 * @since 3.1
 */
class Fusion_Form_Export_Single_Form {

	/**
	 * Fake form date.
	 *
	 * @since 3.1
	 * @var date
	 */
	public $fake_date;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 * @access public
	 */
	public function __construct() {

		// Due to a lack of hooks, we're using what we hope is an unlikely date match.
		$this->fake_date = '1970-01-05'; // Y-m-d.

		add_action( 'init', [ $this, 'init' ] );
	}

	/**
	 * Add actions and filters for export.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function init() {
		if ( current_user_can( 'export' ) ) {
			add_filter( 'export_args', [ $this, 'export_args' ] );
			add_filter( 'query', [ $this, 'query' ] );
			add_filter( 'export_wp_filename', [ $this, 'fusion_form_export_filename' ], 10 );
		}
	}

	/**
	 * Modify export arguments,
	 * except if normal export.
	 *
	 * @param array $args Query args for determining what should be exported.
	 * @return $args Modified query
	 */
	public function export_args( $args ) {

		// If no export_form var, it's a normal export - don't interfere.
		if ( ! isset( $_GET['export_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $args;
		}

		// Use our fake date so the query is easy to find (because we don't have a good hook to use).
		$args['content']    = 'post';
		$args['start_date'] = $this->fake_date;
		$args['end_date']   = $this->fake_date;

		return $args;
	}

	/**
	 * Filter query.
	 * Look for 'tagged' query, replace with one matching the needs.
	 *
	 * @param string $query SQL query.
	 * @return string Modified SQL query
	 */
	public function query( $query ) {
		global $wpdb;

		if ( ! isset( $_GET['export_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return $query;
		}

		// This is the query WP will build (given our arg filtering above)
		// Since the current_filter isn't narrow, we'll check each query
		// to see if it matches, then if it we replace it.
		$test = $wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts}  WHERE {$wpdb->posts}.post_type = 'post' AND {$wpdb->posts}.post_status != 'auto-draft' AND {$wpdb->posts}.post_date >= %s AND {$wpdb->posts}.post_date < %s",
			gmdate( 'Y-m-d', strtotime( $this->fake_date ) ),
			gmdate( 'Y-m-d', strtotime( '+1 month', strtotime( $this->fake_date ) ) )
		);

		if ( $test !== $query ) {
			return $query;
		}

		// Divide query.
		$split = explode( 'WHERE', $query );

		$form_ids     = (array) wp_unslash( $_GET['export_form'] ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		$form_ids     = array_map( 'intval', $form_ids );
		$form_ids_var = implode( "','", $form_ids );

		// Replace WHERE clause.
		$split[1] = $wpdb->prepare( " {$wpdb->posts}.ID IN (%s)", $form_ids_var );
		$split[1] = stripcslashes( $split[1] );

		// Put query back together.
		$query = implode( 'WHERE', $split );

		return $query;
	}

	/**
	 * Export Filename for elements/templates
	 *
	 * @param string $wp_filename Export file name.
	 * @return string $wp_filename New export file name depends on the post type
	 */
	public function fusion_form_export_filename( $wp_filename ) {

		if ( isset( $_GET['export_form'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$form_ids    = (array) wp_unslash( $_GET['export_form'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
			$form_ids    = implode( '-', $form_ids );
			$wp_filename = 'fusion_form[' . $form_ids . ']-export.xml';
		}
		return $wp_filename;
	}
}

