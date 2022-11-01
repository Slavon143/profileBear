<?php
/**
 * Forms Handler.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Form Submission.
 *
 * @since 3.1
 */
class Fusion_Form_DB_Forms extends Fusion_Form_DB_Items {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $table_name = 'fusion_forms';

	/**
	 * Insert form to database.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $args The arguments.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert( $args ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		// Make sure $args['form_id'] is a number.
		$sanitized_form_id = $args['form_id'] ? absint( $args['form_id'] ) : false;

		if ( $args['form_id'] ) {
			// Check if the form id already exists.
			$is_id = $db->get_var(
				$wpdb->prepare(
					"SELECT `id` FROM `{$wpdb->prefix}{$this->table_name}` WHERE form_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$sanitized_form_id
				)
			);
		}

		return ( $is_id ) ? $is_id : parent::insert( $args );
	}

	/**
	 * Get forms.
	 *
	 * @param array $args An array of arguments for the query.
	 * @return array      An array of forms.
	 */
	public function get_formatted( $args = [] ) {
		global $wpdb;

		$results = $this->get( $args );

		// Format the results.
		$forms = [];
		foreach ( $results as $form_object ) {

			// Deleted form's data is not removed from form tables, so we filter out deleted form posts here.
			if ( ! empty( $wpdb->get_results( "SELECT ID from $wpdb->posts WHERE ID = $form_object->form_id AND post_type = 'fusion_form'" ) ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL
				$forms[ $form_object->id ] = [
					'form_id' => $form_object->form_id,
					'views'   => $form_object->views,
				];
			}
		}
		return $forms;
	}

	/**
	 * Insert form field to database.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $form_id     Form ID.
	 * @param string $field_name  Form field name.
	 * @param string $field_label Form field label.
	 * @return integer|boolean The last insert id or false if query failed.
	 */
	public function insert_form_field( $form_id, $field_name, $field_label ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		$sanitized_form_id     = absint( $form_id );
		$sanitized_field_name  = sanitize_key( $field_name );
		$sanitized_field_label = wp_strip_all_tags( $field_label );

		$fusion_fields = new Fusion_Form_DB_Fields();

		// Check if the field name for the same form is already exists.
		$is_id = $db->get_var(
			$wpdb->prepare(
				"SELECT `id` FROM `{$wpdb->prefix}fusion_form_fields` WHERE form_id = %d AND field_name = %s",
				$sanitized_form_id,
				$sanitized_field_name
			)
		);

		if ( ! $is_id ) {

			// Insert form field into database.
			return $fusion_fields->insert(
				[
					'form_id'     => $sanitized_form_id,
					'field_name'  => $sanitized_field_name,
					'field_label' => $sanitized_field_label,
				]
			);
		}

		// Update field label and field name to latest one.
		$fusion_fields->update(
			[
				'field_name'  => $sanitized_field_name,
				'field_label' => $sanitized_field_label,
			],
			[ 'id' => $is_id ],
			[ '%s' ],
			[ '%s' ]
		);

		return $is_id;
	}

	/**
	 * Get form fields for the form id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param int $form_id Form ID to get fields for.
	 * @return array
	 */
	public function get_form_fields( $form_id ) {
		$fields = new Fusion_Form_DB_Fields();

		return $fields->get_form_fields( $form_id );
	}

	/**
	 * Reset views for the form id(s) given.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_ids Form ID(s).
	 * @return void
	 */
	public function reset_views( $form_ids ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		$form_ids = (array) $form_ids;
		foreach ( $form_ids as $id ) {

			// Clear form views from forms table.
			$db->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}fusion_forms SET views = 0 WHERE `id` = %d", $id ) );
		}
	}

	/**
	 * Reset views for the form id(s) given.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_ids Form ID(s).
	 * @return void
	 */
	public function reset_submissions_count( $form_ids ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		$form_ids = (array) $form_ids;
		foreach ( $form_ids as $id ) {

			// Clear form submissions_count from forms table.
			$db->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}fusion_forms SET submissions_count = 0 WHERE `id` = %d", $id ) );
		}
	}

	/**
	 * Clear form entries for the form id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_ids Form ID.
	 * @return void
	 */
	public function clear_form_entries( $form_ids ) {

		$fields      = new Fusion_Form_DB_Fields();
		$submissions = new Fusion_Form_DB_Submissions();
		$entries     = new Fusion_Form_DB_Entries();

		$form_ids = (array) $form_ids;

		// Reset views.
		$this->reset_views( $form_ids );

		// Reset submissions count.
		$this->reset_submissions_count( $form_ids );

		// Delete form entries from form fields table.
		$fields->delete( $form_ids, 'form_id' );

		// Delete form submission entries.
		$entries->delete( $form_ids, 'form_id' );

		// Delete form submissions.
		$submissions->delete( $form_ids, 'form_id' );
	}

	/**
	 * Delete form entries for the form post id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_id Form ID.
	 * @return void
	 */
	public function delete_form_by_post_id( $form_id ) {

		// Get form id for selected form.
		$forms = $this->get(
			[
				'where' => [ 'form_id' => $form_id ],
			]
		);

		if ( $forms ) {

			foreach ( $forms as $form ) {
				$this->delete( $form->id );
			}
		}
	}

	/**
	 * Delete form entries for the form id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param array  $ids       Form ID(s).
	 * @param string $id_column The column our $ids are referring to.
	 * @return void
	 */
	public function delete( $ids, $id_column = 'id' ) {

		$ids = (array) $ids;

		// Delete the form post.
		foreach ( $ids as $id ) {
			$forms = $this->get( [ 'where' => [ $id_column => $id ] ] );

			foreach ( $forms as $form ) {
				wp_delete_post( $form->form_id, true );
			}
		}

		// Delete forms from forms table.
		parent::delete( $ids );

		// Delete form entries from all tables.
		$this->clear_form_entries( $ids );
	}

	/**
	 * Increment views count for the form id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $form_id Form ID to update views.
	 * @return bool
	 */
	public function increment_views( $form_id ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		// Update the entry.
		return $db->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}fusion_forms SET views = views + 1  WHERE form_id = %d",
				absint( $form_id )
			)
		);
	}

	/**
	 * Increment submissions count for the form id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $form_id Form ID to update submissions_count.
	 * @return bool
	 */
	public function increment_submissions_count( $form_id ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		// Update the entry.
		return $db->query(
			$wpdb->prepare(
				"UPDATE `{$wpdb->prefix}fusion_forms` SET `submissions_count` = `submissions_count` + 1 WHERE `id` = %d",
				absint( $form_id )
			)
		);
	}

	/**
	 * Decrease submissions count for the form id given.
	 *
	 * @since 3.1
	 * @access public
	 * @param string $form_id Form ID to update submissions_count.
	 * @return bool
	 */
	public function decrease_submissions_count( $form_id ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		// Update the entry.
		return $db->query(
			$wpdb->prepare(
				"UPDATE {$wpdb->prefix}fusion_forms SET submissions_count = submissions_count - 1  WHERE id = %d",
				absint( $form_id )
			)
		);
	}
}
