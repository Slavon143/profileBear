<?php // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery
/**
 * Creates database tables.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Creates database tables.
 *
 * @since 3.1
 */
class Fusion_Form_DB_Install {

	/**
	 * Table arguments.
	 *
	 * @access protected
	 * @since 3.1
	 * @var array
	 */
	protected $tables = [
		'fusion_forms'            => [
			'unique_key'  => [ 'id', 'form_id' ],
			'primary_key' => 'id',
			'columns'     => [

				// The form ID.
				[
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'auto_increment' => true,
					'not_null'       => true,
				],

				// The form post-ID.
				[
					'name'     => 'form_id',
					'type'     => 'bigint(20)',
					'not_null' => true,
				],

				// Form views.
				[
					'name'    => 'views',
					'type'    => 'bigint(20)',
					'default' => 0,
				],

				// Form submissions.
				[
					'name'    => 'submissions_count',
					'type'    => 'bigint(20)',
					'default' => 0,
				],

				// Extra data.
				[
					'name' => 'data',     // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				],
			],
		],
		'fusion_form_entries'     => [
			'unique_key'  => [ 'id' ],
			'primary_key' => 'id',
			'columns'     => [

				// Row ID: 1 row per-field per-submission.
				// A form with 5 fields will generate 5 rows on submission.
				[
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'not_nulll'      => true,
					'auto_increment' => true,
				],

				// Submission ID.
				[
					'name'     => 'submission_id',
					'type'     => 'bigint(20)',
					'not_null' => true,
				],

				// The form ID.
				// This stores the "id" reference from the "fusion_forms" table,
				// not the "form_id".
				[
					'name'     => 'form_id',
					'type'     => 'bigint(20)',
					'not_null' => true,
				],

				// The field ID.
				// This stores the "id" reference from the "fusion_form_fields" table.
				[
					'name'     => 'field_id',
					'type'     => 'bigint(20)',
					'not_null' => true,
				],

				// The value to store.
				[
					'name' => 'value',
					'type' => 'longtext',
				],

				// Whether this entry is privacy-related or not.
				// Determines if the entry should be purged on privacy-clean or not.
				[
					'name'    => 'privacy',
					'type'    => 'boolean',
					'default' => 'NULL',
				],

				// Extra data.
				[
					'name' => 'data',     // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				],
			],
		],
		'fusion_form_submissions' => [
			'unique_key'  => [ 'id' ],
			'primary_key' => 'id',
			'columns'     => [

				// Submission ID.
				[
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'not_null'       => true,
					'auto_increment' => true,
				],

				// The form for this submission.
				// This stores the "id" reference from the "fusion_forms" table,
				// not the "form_id".
				[
					'name'     => 'form_id',
					'type'     => 'bigint(20)',
					'not_null' => true,
				],

				// Time of submission.
				[
					'name'     => 'time',
					'type'     => 'datetime',
					'not_null' => true,
				],

				// Where the form was submitted from.
				[
					'name'     => 'source_url',
					'type'     => 'varchar(512)',
					'not_null' => true,
				],

				// The post-ID from which this submission came.
				[
					'name'    => 'post_id',
					'type'    => 'bigint(20)',
					'default' => 'NULL',
				],

				// The user-ID if logged-in.
				[
					'name' => 'user_id',
					'type' => 'bigint(20)',
				],

				// The user-agent.
				// Also see the Fusion_Form_DB_Privacy class.
				[
					'name' => 'user_agent',
					'type' => 'text',
				],

				// The IP.
				// Also see the Fusion_Form_DB_Privacy class.
				[
					'name' => 'ip',
					'type' => 'varchar(512)',
				],

				// Whether this submission has been read or not.
				[
					'name'    => 'is_read',
					'type'    => 'boolean',
					'default' => 'NULL',
				],

				// Privacy data-scrubbing date.
				// Also see the Fusion_Form_DB_Privacy class.
				[
					'name' => 'privacy_scrub_date',
					'type' => 'date',
				],

				// Privacy data-scrubbing action.
				// Also see the Fusion_Form_DB_Privacy class.
				[
					'name'     => 'on_privacy_scrub',
					'type'     => 'varchar(20)',
					'not_null' => true,
				],

				// Extra data.
				[
					'name' => 'data',     // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				],
			],
		],
		'fusion_form_fields'      => [
			'unique_key'  => [ 'id' ],
			'primary_key' => 'id',
			'columns'     => [

				// The field ID.
				[
					'name'           => 'id',
					'type'           => 'bigint(20)',
					'not_null'       => true,
					'auto_increment' => true,
				],

				// The form ID.
				// This stores the "id" reference from the "fusion_forms" table,
				// not the "form_id".
				[
					'name'     => 'form_id',
					'type'     => 'bigint(20)',
					'not_null' => true,
				],

				// The field name.
				[
					'name'     => 'field_name',
					'type'     => 'varchar(256)',
					'not_null' => true,
				],

				// The field label.
				[
					'name' => 'field_label',
					'type' => 'varchar(256)',
				],

				// Extra data.
				[
					'name' => 'data',     // Future-proofing.
					'type' => 'longtext', // Can contain serialized data.
				],
			],
		],
	];

	/**
	 * The Constructor.
	 *
	 * @since 3.1
	 * @access public
	 */
	public function __construct() {

	}

	/**
	 * Create tables.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function create_tables() {
		global $wpdb;

		// Get collation.
		$charset_collate = $wpdb->get_charset_collate();

		/**
		 * Loop tables.
		 *
		 * This will generate the query needed, and create the table.
		 */
		foreach ( $this->tables as $table_name => $table_args ) {
			$query_array = [];

			/**
			 * Loop columns for this table.
			 *
			 * Generates the query fragment for this column
			 * which will be them used to build the final query.
			 */
			foreach ( $table_args['columns'] as $column ) {

				// Basic row properties.
				$query_fragment = [
					$column['name'],
					$column['type'],
				];

				// Add "NOT NULL" if needed.
				if ( isset( $column['not_null'] ) && $column['not_null'] ) {
					$query_fragment[] = 'NOT NULL';
				}

				// Add "AUTO_INCREMENT" if needed.
				if ( isset( $column['auto_increment'] ) && $column['auto_increment'] ) {
					$query_fragment[] = 'AUTO_INCREMENT';
				}

				// Add "DEFAULT" if needed.
				if ( isset( $column['default'] ) ) {
					$query_fragment[] = "DEFAULT {$column['default']}";
				}

				// Add our row to the query array.
				$query_array[] = implode( ' ', $query_fragment );
			}

			// Add "UNIQUE KEY" if needed.
			if ( isset( $table_args['unique_key'] ) ) {
				foreach ( $table_args['unique_key'] as $unique_key ) {
					$query_array[] = "UNIQUE KEY $unique_key ($unique_key)";
				}
			}

			// Add "PRIMARY KEY" if needed.
			if ( isset( $table_args['primary_key'] ) ) {
				$query_array[] = "PRIMARY KEY {$table_args['primary_key']} ({$table_args['primary_key']})";
			}

			// Build the query string.
			$columns_query_string = implode( ', ', $query_array );

			// Run the SQL query.
			$wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}$table_name` ($columns_query_string) $charset_collate" ); // phpcs:ignore WordPress.DB
		}

	}
}
