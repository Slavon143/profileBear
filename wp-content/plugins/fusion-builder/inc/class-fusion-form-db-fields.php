<?php
/**
 * Form Submission Handler.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Form Submission.
 *
 * @since 3.1
 */
class Fusion_Form_DB_Fields extends Fusion_Form_DB_Items {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $table_name = 'fusion_form_fields';

	/**
	 * Get form fields for the form id given.
	 *
	 * @since 3.4
	 * @access public
	 * @param int $form_id Form ID to get fields for.
	 * @return array
	 */
	public function get_form_fields( $form_id ) {
		global $wpdb;

		$form_fields  = [];
		$fields_db    = [];
		$field_labels = [];
		$field_names  = [];

		$query             = "SELECT p.post_content FROM $wpdb->posts AS p INNER JOIN {$wpdb->prefix}fusion_forms AS ff ON p.ID = ff.form_id WHERE ff.id = %d";
		$results           = $wpdb->get_results( $wpdb->prepare( $query, (int) $form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
		$form_post_content = $results && isset( $results[0] ) ? $results[0]->post_content : '';

		// Get labels and names for all fields / inputs.
		if ( '' !== $form_post_content ) {

			// Get form field names.
			preg_match_all( '/\[fusion_form_[^\]]*\sname=\"([^\"]*)\"/', $form_post_content, $matches );
			$field_names = isset( $matches[1] ) ? $matches[1] : [];

			// Get form field labels.
			preg_match_all( '/\[fusion_form_[^\]]*\slabel=\"([^\"]*)\"/', $form_post_content, $matches );
			$field_labels = isset( $matches[1] ) ? $matches[1] : [];

			// If (some) labels are missing or empty use name instead.
			if ( count( $field_names ) !== count( array_filter( $field_labels ) ) ) {
				$field_labels = map_deep( $field_names, 'Fusion_Builder_Form_Helper::fusion_name_to_label' );
			}

			// Fetch field_id and field_name.
			$query   = "SELECT id AS field_id, field_name AS field_name FROM {$wpdb->prefix}fusion_form_fields AS fff WHERE fff.form_id = %d";
			$results = $wpdb->get_results( $wpdb->prepare( $query, (int) $form_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL

			// Reformat results as field_name => field_id array.
			if ( $results && is_array( $results ) ) {
				foreach ( $results as $result ) {
					$fields_db[ $result->field_name ] = $result->field_id;
				}
			}

			$count = count( $field_names );

			// Loop through matched names & labels and create field_id => { field_label, field_name } array.
			for ( $i = 0; $i < $count; $i++ ) {

				// Field names in DB are lowercased.
				if ( isset( $fields_db[ strtolower( $field_names[ $i ] ) ] ) ) {
					$obj              = new stdClass();
					$obj->field_label = $field_labels[ $i ];
					$obj->field_name  = $field_names[ $i ];

					// Field names in DB are lowercased.
					$form_fields[ $fields_db[ strtolower( $field_names[ $i ] ) ] ] = $obj;
				}
			}
		}

		return $form_fields;
	}
}
