<?php
/**
 * Handles the data table creation for form entries.
 *
 * @package fusion-builder
 * @since 3.1
 */

// WP_List_Table is not loaded automatically so we need to load it in our application.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Create a new table class that will extend the WP_List_Table.
 */
class Fusion_Form_List_Table extends WP_List_Table {

	/**
	 * Form ID.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $form_id = [];

	/**
	 * Data columns.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $columns = [];

	/**
	 * Form Fields.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $form_fields = [];

	/**
	 * Form sumissions data.
	 *
	 * @since 3.1
	 * @var array
	 */
	public $form_submissions = [];

	/**
	 * No entries text.
	 *
	 * @since 3.1
	 * @var string
	 */
	public $no_entries_text;

	/**
	 * Form field names.
	 *
	 * @since 3.6
	 * @var array
	 */
	public $field_names = [];

	/**
	 * Form field labels.
	 *
	 * @since 3.6
	 * @var array
	 */
	public $field_labels = [];

	/**
	 * Flag for if labels are valid or not. Labels are valid if there are no duplicates or missing labels.
	 *
	 * @since 3.6
	 * @var bool|null
	 */
	public $are_labels_valid = null;

	/**
	 * Class constructor.
	 *
	 * @since 3.1
	 * @access public
	 * @param array $form_id Form id.
	 */
	public function __construct( $form_id ) {
		parent::__construct();
		$this->form_id     = $form_id;
		$fusion_forms      = new Fusion_Form_DB_Forms();
		$this->form_fields = $fusion_forms->get_form_fields( $this->form_id );

		// Get all field names and not empty labels.
		foreach ( $this->form_fields as $key => $field_object ) {

			$this->field_names[] = $field_object->field_name;

			// Use field name if label is empty, for example hidden fields.
			if ( isset( $field_object->field_label ) && '' !== $field_object->field_label ) {
				$this->field_labels[] = $field_object->field_label;
			}
		}

		// Use labels if all fields have unique labels, otherwise use field names.
		$this->columns = $this->are_labels_valid() ? $this->field_labels : map_deep( $this->field_names, 'Fusion_Builder_Form_Helper::fusion_name_to_label' );

		// We don't need all.
		$this->columns = array_slice( $this->columns, 0, 7 );

		// Add actions column at the end.
		if ( 0 !== count( $this->form_fields ) ) {
			array_push( $this->columns, 'Actions' );
		}

		$this->no_entries_text = __( 'No form entries submitted yet.', 'fusion-builder' );
	}

	/**
	 * Prepare the items for the table to process.
	 *
	 * @since 3.1
	 * @access public
	 * @param int $per_page     number of items per page.
	 * @param int $current_page Current page.
	 * @return void
	 */
	public function prepare_items( $per_page = 15, $current_page = 0 ) {
		$submissions = new Fusion_Form_DB_Submissions();
		$columns     = $this->get_columns();

		if ( 0 === $current_page ) {
			$current_page = $this->get_pagenum();
		}

		$submission_args = [
			'where'    => [ 'form_id' => (int) $this->form_id ],
			'order by' => 'id DESC',
		];

		// If we want small section, limit query.
		if ( 1 < $per_page ) {
			$submission_args['limit']  = $per_page;
			$submission_args['offset'] = absint( ( $current_page - 1 ) * $per_page );
		}

		// Get submissions.
		$this->form_submissions = $submissions->get( $submission_args );
		$data                   = $this->table_data();
		$hidden                 = $this->get_hidden_columns();
		$sortable               = $this->get_sortable_columns();
		// Check the form submission type.
		$fusion_forms = new Fusion_Form_DB_Forms();
		$forms        = $fusion_forms->get_formatted();

		// Count number of entries.
		$result      = $submissions->get(
			[
				'what'  => 'COUNT(id) AS count',
				'where' => [ 'form_id' => (int) $this->form_id ],
			]
		);
		$total_items = isset( $result[0] ) ? $result[0]->count : 0;

		$this->set_pagination_args(
			[
				'total_items' => $total_items,
				'per_page'    => $per_page,
			]
		);

		$this->_column_headers = [ $columns, $hidden, $sortable ];
		$this->items           = $data;
	}

	/**
	 * Override the parent columns method. Defines the columns to use in your listing table.
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	public function get_columns() {
		return $this->columns;
	}

	/**
	 * Define which columns are hidden
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	public function get_hidden_columns() {
		return [];
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	public function get_sortable_columns() {
		return [];
	}

	/**
	 * Get the table data.
	 *
	 * @since 3.1
	 * @access public
	 * @return array
	 */
	private function table_data() {

		$data           = [];
		$form_entries   = [];
		$fusion_entries = new Fusion_Form_DB_Entries();

		foreach ( $this->form_submissions as $submission ) {
			$form_entries[ $submission->id ] = $fusion_entries->get(
				[
					'where' => [ 'submission_id' => $submission->id ],
				]
			);
		}

		foreach ( $form_entries as $key => $entries ) {

			$entries = (array) $entries;

			foreach ( $entries as $entry ) {

				$entry = (array) $entry;

				if ( isset( $this->form_fields[ $entry['field_id'] ] ) ) {
					$field_label = $this->are_labels_valid() ? $this->form_fields[ $entry['field_id'] ]->field_label : Fusion_Builder_Form_Helper::fusion_name_to_label( $this->form_fields[ $entry['field_id'] ]->field_name );

					$data[ $key ][ $field_label ] = esc_html( $entry['value'] );
				}
			}

			if ( ! isset( $data[ $key ] ) ) {
				$data[ $key ] = [];
			}

			// Add actions column at the end.
			$data[ $key ]['Actions'] = $this->column_actions( $data[ $key ], $key );
		}

		return $data;
	}

	/**
	 * Define what data to show on each column of the table
	 *
	 * @since 3.1
	 * @access public
	 * @param  array  $item        Data.
	 * @param  string $column_id - Current column id.
	 * @return string
	 */
	public function column_default( $item, $column_id ) {
		$column_name = $this->columns[ $column_id ];
		$value       = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		$value       = $this->format_column_data( $value );

		return $value;
	}

	/**
	 * Display button with link to display all form fields in popup.
	 *
	 * @since 3.1
	 * @access public
	 * @param  array $entry Singhe entry data.
	 * @param  int   $key   Singhe entry key.
	 * @return string
	 */
	public function column_actions( $entry, $key ) {

		$submissions_obj = new Fusion_Form_DB_Submissions();
		$html            = '<div class="row-actions fusion-form-entries">';
		$html           .= '<span class"view_details"><a href="#" onclick="jQuery(\'.single-entry-' . $key . '\').toggleClass( \'hidden\' ); return false;">' . __( 'View All Details', 'fusion-builder' ) . '</a> | </span>';
		$html           .= '<span class="trash"><a href="#" class="fusion-remove-form-entry" data-key="' . $key . '">' . __( 'Delete', 'fusion-builder' ) . '</a></span>';
		$html           .= '</div>';
		$html           .= '<div class="single-entry-' . $key . ' fusion-form-single-entry-popup-overlay hidden" onclick="jQuery(\'.single-entry-' . $key . '\').toggleClass( \'hidden\' ); return false;"></div>';
		$html           .= '<div class="single-entry-' . $key . ' fusion-form-single-entry-popup hidden">';
		$html           .= '<a href="#" onclick="jQuery(\'.single-entry-' . $key . '\').toggleClass( \'hidden\' ); return false;" class="single-entry-' . $key . ' dashicons dashicons-no-alt fusion-form-single-entry-popup-close hidden"></a>';
		$html           .= '<div class="fusion-form-single-entry-popup-inner">';

		foreach ( $entry as $label => $value ) {
			$html .= '<div class="fusion-form-single-entry">';
			$html .= '<div class="fusion-form-single-entry-label">';
			$html .= $label;
			$html .= '</div>';
			$html .= '<div class="fusion-form-single-entry-value">';
			$html .= $this->format_column_data( $value );
			$html .= '</div>';
			$html .= '</div>';
		}

		$submissions = $submissions_obj->get(
			[
				'where' => [ 'id' => (int) $key ],
			]
		);

		if ( isset( $submissions[0] ) ) {

			// remove form DB ID.
			unset( $submissions[0]->form_id );

			// remove is_read (we don't use it for now).
			unset( $submissions[0]->is_read );

			// remove serialized data (we don't use it for now).
			$data = json_decode( $submissions[0]->data, true );

			if ( ( ! JSON_ERROR_NONE === json_last_error() || 'NULL' !== $data ) && ! ( isset( $data['hubspot_response'] ) || isset( $data['email_errors'] ) || isset( $data['mailchimp_response'] ) ) ) {
				unset( $submissions[0]->data );
			}
		}

		$html .= '<div class="fusion-form-single-entry fusion-form-single-entry-submission-meta">';
		$html .= '<h3>' . __( 'Additional Information', 'fusion-builder' ) . '</h3>';
		$html .= '</div>';

		foreach ( $submissions[0] as $label => $value ) {
			if ( 'data' === $label && is_array( $data ) ) {
				foreach ( $data as $data_label => $data_value ) {
					$html .= $this->column_data( $data_label, $data_value );
				}
			} else {
				$html .= $this->column_data( $label, $value );
			}
		}

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Formats column data.
	 *
	 * @since 3.8
	 * @access public
	 * @param  string $value Column value.
	 * @return string
	 */
	public function format_column_data( $value ) {

		$values = explode( ' | ', $value );
		$is_url = false;

		if ( 1 < count( $values ) && false === strpos( $value, 'fusion-form-entries' ) ) {
			$is_url = true;
			foreach ( $values as $index => $value ) {
				if ( ! filter_var( $value, FILTER_VALIDATE_URL ) ) {
					$is_url = false;
					break;
				} else {
					$values[ $index ] = '<a href="' . esc_url_raw( $value ) . '" target="_blank">' . $value . '</a>';
				}
			}
		}

		if ( $is_url ) {
			$value = implode( ' | ', $values );
		} elseif ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
			$value = '<a href="' . esc_url_raw( $value ) . '" target="_blank">' . esc_html( $value ) . '</a>';
		}

		return $value;
	}

	/**
	 * Adds column data.
	 *
	 * @since 3.1
	 * @access public
	 * @param  string $label Row label.
	 * @param  string $value Row value.
	 * @return string
	 */
	public function column_data( $label, $value ) {
		$label = 'id' === $label ? __( 'Submission Id', 'fusion-builder' ) : $label;
		$label = 'hubspot_response' === $label ? __( 'HubSpot Response', 'fusion-builder' ) : $label;
		$label = 'email_errors' === $label ? __( 'Email Errors', 'fusion-builder' ) : $label;
		$label = 'mailchimp_response' === $label ? __( 'MailChimp Response', 'fusion-builder' ) : $label;

		$html  = '<div class="fusion-form-single-entry">';
		$html .= '<div class="fusion-form-single-entry-label">';
		$html .= ucwords( str_replace( '_', ' ', $label ) );
		$html .= '</div>';
		$html .= '<div class="fusion-form-single-entry-value">';
		$html .= esc_html( $value );
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Display custom text if no form entries are submitted.
	 *
	 * @since 3.1
	 * @access public
	 * @return void
	 */
	public function no_items() {
		echo esc_html( $this->no_entries_text );
	}

	/**
	 * Checks if labels for all fields are populated and there are no duplicates.
	 *
	 * @since 3.6
	 * @access protected
	 * @return bool
	 */
	protected function are_labels_valid() {

		if ( null === $this->are_labels_valid ) {
			$this->are_labels_valid = count( $this->field_names ) === count( $this->field_labels ) && count( array_unique( $this->field_labels ) ) === count( $this->field_labels );
		}

		return $this->are_labels_valid;
	}
}
