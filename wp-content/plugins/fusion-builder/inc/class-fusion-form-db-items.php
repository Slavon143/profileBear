<?php
/**
 * Abstraction for submissions & forms.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Form Submissions & Forms abstraction.
 *
 * @since 3.1
 */
abstract class Fusion_Form_DB_Items {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $table_name;

	/**
	 * Get form submissions.
	 *
	 * @param array $args An array of arguments for the query.
	 * @return array      An array of submissions.
	 */
	public function get( $args = [] ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		// The table name.
		$table_name = $wpdb->prefix . $this->table_name;

		if ( ! isset( $args['what'] ) ) {
			$args['what'] = '*';
		}

		// The query basics.
		$query = 'SELECT ' . $args['what'] . " FROM `$table_name`";

		// Build the WHERE fragment of the query.
		if ( isset( $args['where'] ) ) {
			$where = [];
			foreach ( $args['where'] as $where_fragment_key => $where_fragment_val ) {
				$where[] = "$where_fragment_key = $where_fragment_val";
			}

			$query .= ' WHERE ' . implode( ' AND ', $where );
		}

		// Build the ORDER BY fragment of the query.
		if ( isset( $args['order by'] ) ) {
			$query .= ' ORDER BY ' . $args['order by'];
		}

		// Build the LIMIT fragment of the query.
		if ( isset( $args['limit'] ) ) {
			$query .= ' LIMIT ' . absint( $args['limit'] );
		}

		// Build the OFFSET fragment of the query.
		if ( isset( $args['offset'] ) ) {
			$query .= ' OFFSET ' . absint( $args['offset'] );
		}

		return $db->get_results( $query );
	}

	/**
	 * Insert form submission meta to database.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;

		// Insert form into database.
		$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . $this->table_name,
			apply_filters( $this->table_name . '_insert_query_args', $args )
		);

		// Get last inserted forms row id.
		return $wpdb->insert_id;
	}

	/**
	 * Update item.
	 *
	 * @since 3.1
	 * @access public
	 * @param array        $data         Data to update (in column => value pairs).
	 *                                   Both $data columns and $data values should be "raw"
	 *                                   (neither should be SQL escaped).
	 *                                   Sending a null value will cause the column to be set to NULL -
	 *                                   the corresponding format is ignored in this case.
	 * @param array        $where        A named array of WHERE clauses (in column => value pairs).
	 *                                   Multiple clauses will be joined with ANDs.
	 *                                   Both $where columns and $where values should be "raw".
	 *                                   Sending a null value will create an IS NULL comparison -
	 *                                   the corresponding format will be ignored in this case.
	 * @param array|string $format       An array of formats to be mapped to each of the values in $data.
	 *                                   If string, that format will be used for all of the values in $data.
	 *                                   A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                                   If omitted, all values in $data will be treated as strings
	 *                                   unless otherwise specified in wpdb::$field_types.
	 * @param array|string $where_format An array of formats to be mapped to each of the values in $where.
	 *                                   If string, that format will be used for all of the items in $where.
	 *                                   A format is one of '%d', '%f', '%s' (integer, float, string).
	 *                                   If omitted, all values in $where will be treated as strings.
	 * @return void
	 */
	public function update( $data, $where, $format = null, $where_format = null ) {
		global $wpdb;

		// Update item.
		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prefix . $this->table_name,
			$data,
			$where,
			$format,
			$where_format
		);
	}

	/**
	 * Delete a submission.
	 *
	 * @access public
	 * @since 3.1
	 * @param int|array $ids       The submission ID(s).
	 * @param string    $id_column The column to use in our WHERE query fragment.
	 * @return void
	 */
	public function delete( $ids, $id_column = 'id' ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		$ids = (array) $ids;

		foreach ( $ids as $id ) {

			// Make sure $id is an integer.
			$id = (int) $id;

			// Delete submission.
			$db->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}{$this->table_name}` WHERE `{$wpdb->prefix}{$this->table_name}`.`$id_column` = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}
}
