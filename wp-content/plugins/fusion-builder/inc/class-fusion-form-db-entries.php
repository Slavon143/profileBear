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
class Fusion_Form_DB_Entries extends Fusion_Form_DB_Items {

	/**
	 * The table name.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $table_name = 'fusion_form_entries';

	/**
	 * Delete an entry.
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

			// Check if it is an upload field and delete if so.
			$this->maybe_delete_files( $id, $id_column );

			// Delete submission.
			$db->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}{$this->table_name}` WHERE `{$wpdb->prefix}{$this->table_name}`.`$id_column` = %d", $id ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		}
	}

	/**
	 * Delete stored files if they exist.
	 *
	 * @access public
	 * @since 3.1
	 * @param int|array $id        The submission ID.
	 * @param string    $id_column The column to use in our WHERE query fragment.
	 * @return void
	 */
	public function maybe_delete_files( $id, $id_column = 'id' ) {
		$args                           = [];
		$args[ esc_attr( $id_column ) ] = $id;
		$entries                        = $this->get( [ 'where' => $args ] );

		if ( is_array( $entries ) && ! empty( $entries ) ) {
			$upload         = wp_upload_dir();
			$upload['path'] = $upload['basedir'] . '/fusion-forms';
			$upload['url']  = $upload['baseurl'] . '/fusion-forms';

			foreach ( $entries as $entry ) {

				// Check if it holds a file URL.
				if ( $entry && isset( $entry->value ) && false !== strpos( $entry->value, $upload['url'] ) ) {
					$values = explode( ' | ', $entry->value );

					foreach ( $values as $value ) {
						$file_path = str_replace( $upload['url'], $upload['path'], $value );

						// File exists, delete it.
						if ( file_exists( $file_path ) ) {
							wp_delete_file( $file_path );
						}
					}
				}
			}
		}
	}
}
