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
class Fusion_Form_DB_Submissions extends Fusion_Form_DB_Items {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $table_name = 'fusion_form_submissions';

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

		$ids = (array) $ids;

		foreach ( $ids as $id ) {

			// Get the form-ID for this submission.
			$submission = $this->get( [ 'where' => [ 'id' => $id ] ] );
			if ( isset( $submission[0] ) ) {

				// Get the form-ID.
				$form_id = $submission[0]->form_id;

				// Decrease submissions count.
				$forms = new Fusion_Form_DB_Forms();
				$forms->decrease_submissions_count( $form_id );
			}

			// Delete submission.
			parent::delete( $id );

			// Delete submission entries.
			$entries = new Fusion_Form_DB_Entries();
			$entries->delete( $id, 'submission_id' );
		}
	}
}
