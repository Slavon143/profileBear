<?php
/**
 * Privacy handler.
 *
 * @package fusion-builder
 * @since 3.1
 */

/**
 * Database actions for privacy.
 *
 * @since 3.1
 */
class Fusion_Form_DB_Privacy {

	/**
	 * Should we log the IP?
	 *
	 * @access protected
	 * @since 3.1
	 * @var bool
	 */
	protected $log_ip = false;

	/**
	 * Should we log the User-Agent?
	 *
	 * @access protected
	 * @since 3.1
	 * @var bool
	 */
	protected $log_ua = false;

	/**
	 * Log expiration.
	 *
	 * This is a numeric value, representing time in seconds.
	 * Set to 0 for no expiration.
	 *
	 * @access protected
	 * @since 3.1
	 * @var int
	 */
	protected $expiration = YEAR_IN_SECONDS;

	/**
	 * Expired data scrub method.
	 *
	 * This is a string and can be either "delete", "anonymize" or "ignore".
	 * When set to "anonymize", form submissions older than $expiration will have their UA and IP columns scrubbed.
	 * When set to "delete", form submissions older than $expiration will be deleted.
	 *
	 * @access protected
	 * @since 3.1
	 * @var string
	 */
	protected $on_expiration = 'anonymize';

	/**
	 * Cleanup interval.
	 *
	 * The interval (in seconds) that will trigger a data purge.
	 *
	 * @since 3.1
	 */
	const CLEANUP_INTERVAL = DAY_IN_SECONDS;

	/**
	 * The option used for cleanup timestamp.
	 *
	 * @since 3.1
	 */
	const CLEANUP_DATETIME_OPTION = 'fusion_form_cleanup_datetime';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.1
	 */
	public function __construct() {

		// Filter the database query.
		add_filter( 'fusion_form_submissions_insert_query_args', [ $this, 'filter_submissions_query_args' ] );

		// Run cleanup on shutdown. We do this on shutdown so it doesn't interfere with the page-load.
		add_action( 'shutdown', [ $this, 'maybe_cleanup' ] );

		// Register privacy data-exporter.
		add_filter( 'wp_privacy_personal_data_exporters', [ $this, 'register_exporter' ] );

		// Register privacy data-eraser.
		add_filter( 'wp_privacy_personal_data_erasers', [ $this, 'register_eraser' ] );
	}

	/**
	 * Filters form submission query arguments.
	 *
	 * @access public
	 * @since 3.1
	 * @param array $args The query arguments.
	 * @return array
	 */
	public function filter_submissions_query_args( $args ) {

		// Remove IP data if we don't want to log it.
		if ( ! $this->should_log_ip( $args['form_id'] ) ) {
			$args['ip'] = '';
		}

		// Remove UA data if we don't want to log it.
		if ( ! $this->should_log_ua( $args['form_id'] ) ) {
			$args['user_agent'] = '';
		}

		// Modify the expiration date.
		$args['privacy_scrub_date'] = $this->get_submission_privacy_expire_date( $args['form_id'] );

		// Modify the scrub method.
		$args['on_privacy_scrub'] = $this->get_submission_scrub_action( $args['form_id'] );

		// Return modified arguments.
		return $args;
	}

	/**
	 * Check if we want to log IP for a form submission.
	 *
	 * @access protected
	 * @since 3.1
	 * @return bool
	 */
	protected function should_log_ip() {

		if ( isset( $_POST['formData'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			parse_str( wp_unslash( $_POST['formData'] ), $form_data ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			if ( isset( $form_data['fusion_privacy_store_ip_ua'] ) ) {
				return ( true === $form_data['fusion_privacy_store_ip_ua'] || 'true' === $form_data['fusion_privacy_store_ip_ua'] );
			}
		}
		return $this->log_ip;
	}

	/**
	 * Check if we want to log IP for a form submission.
	 *
	 * @access protected
	 * @since 3.1
	 * @return bool
	 */
	protected function should_log_ua() {

		if ( isset( $_POST['formData'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			parse_str( wp_unslash( $_POST['formData'] ), $form_data ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			if ( isset( $form_data['fusion_privacy_store_ip_ua'] ) ) {
				return ( true === $form_data['fusion_privacy_store_ip_ua'] || 'true' === $form_data['fusion_privacy_store_ip_ua'] );
			}
		}
		return $this->log_ua;
	}

	/**
	 * Get The expiration date for privacy-related data (IP & UA).
	 *
	 * @access protected
	 * @since 3.1
	 * @return string Return gmdate result.
	 */
	protected function get_submission_privacy_expire_date() {

		$time_from_now = $this->expiration;

		// Get current time.
		$current = (int) gmdate( 'U' );

		if ( isset( $_POST['formData'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			parse_str( wp_unslash( $_POST['formData'] ), $form_data ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			if ( isset( $form_data['fusion_privacy_expiration_interval'] ) ) {
				$time_from_now = absint( $form_data['fusion_privacy_expiration_interval'] ) * MONTH_IN_SECONDS;
			}
		}

		// Calculate the expiration time.
		$expire_time = $current + $time_from_now;

		// Return expiration properly formatted as date.
		// No time needed, we store it in the datyabase as date since scrubbing runs once/day.
		return gmdate( 'Y-m-d', $expire_time );
	}

	/**
	 * Get The scrub action that should be performed on expiration.
	 *
	 * @access protected
	 * @since 3.1
	 * @return string Returns anonymize|delete|ignore.
	 */
	protected function get_submission_scrub_action() {

		$on_expiration = $this->on_expiration;

		if ( isset( $_POST['formData'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			parse_str( wp_unslash( $_POST['formData'] ), $form_data ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			if ( isset( $form_data['privacy_expiration_action'] ) ) {
				$on_expiration = $form_data['privacy_expiration_action'];
			}
		}
		if ( 'anonymize' === $on_expiration || 'delete' === $on_expiration || 'ignore' === $on_expiration ) {
			return $on_expiration;
		}

		// Fallback just to be sure.
		return 'anonymize';
	}

	/**
	 * Checks if we need to run the scrubbing action oln this page-load
	 * and triggers the purge when needed.
	 *
	 * @access public
	 * @since 3.1
	 * @return void
	 */
	public function maybe_cleanup() {

		// Early exit if we don't need to cleanup.
		if ( ! $this->should_cleanup() ) {
			return;
		}

		$this->cleanup();
	}

	/**
	 * Checks if we need to cleanup the db.
	 *
	 * @access protected
	 * @since 3.1
	 * @return bool
	 */
	protected function should_cleanup() {
		$cleanup_time = (int) get_option( self::CLEANUP_DATETIME_OPTION );

		/**
		 * Return true if cleanup option is not set, or if time has elapsed.
		 *
		 * The 1st part of this condition will force the action to run the 1st time,
		 * therefore triggering the option to be updated in the clenup() method
		 * and preventing loops.
		 */
		return ( ! $cleanup_time || $cleanup_time <= time() );
	}

	/**
	 * Do the cleanup.
	 *
	 * @access protected
	 * @since 3.1
	 * @return void
	 */
	protected function cleanup() {
		global $wpdb;

		// The db.
		$db = new Fusion_Form_DB();

		// Get all submissions.
		// We only want submissions that have expired dates.
		$submissions = $db->get_results(
			$wpdb->prepare(
				"SELECT * FROM `{$wpdb->prefix}fusion_form_submissions` WHERE `{$wpdb->prefix}fusion_form_submissions`.`privacy_scrub_date` < %s",
				gmdate( 'Y-m-d' )
			)
		);

		// Cleanup the submissions.
		foreach ( $submissions as $entry ) {
			$this->cleanup_submission( $entry );
		}

		// Update the option so we know when to next run the cleanup action.
		$next_cleanup = time() + self::CLEANUP_INTERVAL;
		update_option( self::CLEANUP_DATETIME_OPTION, $next_cleanup );
	}

	/**
	 * Cleanup a submission's data.
	 *
	 * @access protected
	 * @since 3.1
	 * @param stdClass     $entry        The submission.
	 * @param string|false $force_action Leave false to do what the entry needs.
	 *                                   Set to delete|anonymize|ignore to force an action.
	 * @return void
	 */
	protected function cleanup_submission( $entry, $force_action = false ) {
		global $wpdb;
		$db = new Fusion_Form_DB();

		// Determine what we want to do.
		$action = $force_action ? $force_action : $entry->on_privacy_scrub;

		// Delete the entry if that's what we want to do.
		if ( 'delete' === $action ) {
			$submission = new Fusion_Form_DB_Submissions();
			$submission->delete( $entry->id );
		}

		// Anonymize the data.
		if ( 'anonymize' === $action ) {
			$db->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}fusion_form_submissions` SET `user_id` = 0 WHERE `{$wpdb->prefix}fusion_form_submissions`.`id` = %d;", $entry->id ) );
			$db->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}fusion_form_submissions` SET `ip` = '' WHERE `{$wpdb->prefix}fusion_form_submissions`.`id` = %d;", $entry->id ) );
			$db->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}fusion_form_submissions` SET `user_agent` = '' WHERE `{$wpdb->prefix}fusion_form_submissions`.`id` = %d;", $entry->id ) );
			$db->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}fusion_form_submissions` SET `user_agent` = '' WHERE `{$wpdb->prefix}fusion_form_submissions`.`id` = %d;", $entry->id ) );
			$db->query( $wpdb->prepare( "DELETE FROM `{$wpdb->prefix}fusion_form_entries` WHERE `{$wpdb->prefix}fusion_form_entries`.`submission_id` = %d AND `{$wpdb->prefix}fusion_form_entries`.`privacy` = 1", $entry->id ) );

		}

		// Remove privacy_scrub_date.
		$db->query( $wpdb->prepare( "UPDATE `{$wpdb->prefix}fusion_form_submissions` SET `privacy_scrub_date` = NULL WHERE `{$wpdb->prefix}fusion_form_submissions`.`id` = %d;", $entry->id ) );
	}

	/**
	 * Register the data exporter.
	 *
	 * @access public
	 * @since 3.1
	 * @link https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-exporter-to-your-plugin/
	 * @param array $exporters An array of all exporters.
	 * @return array           Returns $exporters with our own added.
	 */
	public function register_exporter( $exporters ) {
		$exporters['fusion-forms'] = [
			'exporter_friendly_name' => esc_html__( 'Form Submissions', 'fusion-builder' ),
			'callback'               => [ $this, 'personal_data_exporter' ],
		];
		return $exporters;
	}

	/**
	 * Register the data eraser.
	 *
	 * @access public
	 * @since 3.1
	 * @link https://developer.wordpress.org/plugins/privacy/adding-the-personal-data-eraser-to-your-plugin/
	 * @param array $erasers An array of all erasers.
	 * @return array         Returns $erasers with our own added.
	 */
	public function register_eraser( $erasers ) {
		$erasers['fusion-forms'] = [
			'eraser_friendly_name' => esc_html__( 'Form Submissions', 'fusion-builder' ),
			'callback'             => [ $this, 'personal_data_eraser' ],
		];
		return $erasers;
	}

	/**
	 * The privacy data exporter.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $email_address The form submission author email address.
	 * @return array                An array of personal data.
	 */
	public function personal_data_exporter( $email_address ) {
		global $wpdb;

		$data_to_export = [];

		// Get all submissions for this email.
		$submissions = $this->get_submissions_by_email( $email_address );

		// Get all fields. Will be used to get the field labels.
		$fusion_fields = new Fusion_Form_DB_Fields();
		$all_fields    = $fusion_fields->get();

		// Loop all user submissions and create the data to be exported.
		foreach ( $submissions as $entry ) {
			$submission_data_to_export = [];

			// Get the fields for this submission.
			$fusion_entries = new Fusion_Form_DB_Entries();
			$entry_fields   = $fusion_entries->get(
				[
					'where' => [ 'submission_id' => $entry->id ],
				]
			);

			// Loop submission fields and add data.
			foreach ( $entry_fields as $field ) {
				$submission_data_to_export[] = [
					'name'  => $this->get_field_label( $field, $all_fields ),
					'value' => $field->value,
				];
			}

			// Add submission to export-data array.
			$data_to_export[] = [
				'group_id'          => 'fusion_forms',
				'group_label'       => esc_html__( 'Form Submissions', 'fusion-builder' ),
				'group_description' => esc_html__( 'User&#8217;s form submissions data.' ),
				'item_id'           => "submission-{$entry->id}",
				'data'              => $submission_data_to_export,
			];
		}

		return [
			'data' => $data_to_export,
			'done' => true,
		];
	}

	/**
	 * Get the label for a field.
	 *
	 * Helper method for data exporter.
	 *
	 * @access protected
	 * @since 3.1
	 * @param stdClass $field  The field.
	 * @param array    $fields An array of all fields.
	 * @return string          Returns the field label.
	 */
	protected function get_field_label( $field, $fields ) {
		foreach ( $fields as $single_field ) {
			if ( $single_field->id === $field->field_id && $single_field->form_id === $field->form_id ) {
				return $single_field->field_label;
			}
		}
		return '';
	}

	/**
	 * Personal data eraser.
	 *
	 * @access public
	 * @since 3.1
	 * @param string $email_address The form submission author email address.
	 * @return array
	 */
	public function personal_data_eraser( $email_address ) {

		$items_removed = false;

		// Get all submissions for this email address.
		$submissions = $this->get_submissions_by_email( $email_address );

		// Loop submissions.
		foreach ( $submissions as $entry ) {

			// Sanity check.
			if ( ! is_object( $entry ) || ! isset( $entry->id ) ) {
				continue;
			}

			// Tell WP-Core that we have removed items.
			$items_removed = true;

			// Delete the submission.
			$this->cleanup_submission( $entry, 'delete' );
		}

		return [
			'items_removed'  => $items_removed,
			'items_retained' => false,
			'messages'       => [],
			'done'           => true,
		];
	}

	/**
	 * Get submissions by email address.
	 *
	 * @access protected
	 * @since 3.1
	 * @param string $email_address The email address.
	 * @return array
	 */
	protected function get_submissions_by_email( $email_address ) {
		global $wpdb;

		// Get the user-ID.
		$user = get_user_by( 'email', $email_address );

		// Init the db.
		$db = new Fusion_Form_DB();

		$submissions_by_user_id = [];
		$submissions_ids        = [];
		$submissions            = [];

		// Get submissions by user-ID.
		// This will account for submissions made by logged-in users.
		if ( $user ) {

			// Get submissions for this user.
			$submissions_by_user_id = $db->get_results(
				$wpdb->prepare(
					"SELECT * FROM `{$wpdb->prefix}fusion_form_submissions` WHERE `{$wpdb->prefix}fusion_form_submissions`.`user_id` = %d",
					$user->ID
				)
			);
		}

		// Get submissions by email address.
		// This will account for submissions made by logged-out users.
		$fusion_entries = new Fusion_Form_DB_Entries();
		$fields         = $fusion_entries->get(
			[
				'where' => [ 'value' => "'" . $email_address . "'" ],
			]
		);

		// Add logged-in submissions to our submissions array.
		foreach ( $submissions_by_user_id as $submission ) {
			$submissions[]     = $submission;
			$submissions_ids[] = $submission->id;
		}

		// Add logged-out submissions to our submissions array.
		foreach ( $fields as $field ) {

			// If this submission is not already in our array, add it.
			if ( ! in_array( $field->submission_id, $submissions_ids ) ) { // phpcs:ignore WordPress.PHP.StrictInArray

				// Get the submission object from its ID.
				$submission = $db->get_results(
					$wpdb->prepare(
						"SELECT * FROM `{$wpdb->prefix}fusion_form_submissions` WHERE `{$wpdb->prefix}fusion_form_submissions`.`id` = %d",
						$field->submission_id
					)
				);

				// Add to the array.
				$submissions[]     = $submission[0];
				$submissions_ids[] = $field->submission_id;
			}
		}

		return $submissions;
	}
}
