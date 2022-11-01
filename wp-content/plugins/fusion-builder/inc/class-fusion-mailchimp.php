<?php
/**
 * Fusion Mailchimp.
 *
 * @package Fusion-Builder
 * @since 3.5
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Fusion Mailchimp class.
 *
 * @since 3.5
 */
class Fusion_Mailchimp {
	/**
	 * The one, true instance of this object.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var object
	 */
	private static $instance;

	/**
	 * API key.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $key = null;

	/**
	 * Token data.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $token = null;

	/**
	 * Server prefix.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $dc = null;

	/**
	 * Fields.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $fields = null;

	/**
	 * Lists.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $lists = null;

	/**
	 * Localize status.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $localize_status = null;

	/**
	 * Type of connection.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $type;

	/**
	 * Markup for notices.
	 *
	 * @static
	 * @access private
	 * @since 3.5
	 * @var mixed
	 */
	private $notices = '';

	/**
	 * Class constructor.
	 *
	 * @since 3.5
	 * @access private
	 */
	private function __construct() {
		$fusion_settings = awb_get_fusion_settings();
		$this->type      = $fusion_settings->get( 'mailchimp_api' );

		// Enqueue the OAuth script where required.
		$this->oauth_enqueue();

		// Add the PO options to the form CPT.
		add_filter( 'avada_form_submission_sections', [ $this, 'maybe_add_option' ] );

		// This is a redirect from our site with token.
		if ( is_admin() && current_user_can( 'manage_options' ) ) {

			// Trying to save a token.
			if ( isset( $_GET['mailchimp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$this->authenticate();
			}

			// Trying to revoke a token.
			if ( isset( $_GET['revoke_mailchimp'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				$this->revoke_access();
			}
		}

		// Render notices if we have any.
		add_action( 'avada_dashboard_notices', [ $this, 'render_notices' ] );

		// Reset Caches.
		add_action( 'wp_ajax_fusion_reset_mailchimp_caches', [ $this, 'reset_caches_handler' ] );

		// If not enabled, no need to load anything.
		if ( ! apply_filters( 'fusion_load_mailchimp', ( 'off' !== $this->type ) ) ) {
			return;
		}

		// Enqueue the JS script for the PO mapping option.
		add_action( 'avada_page_option_scripts', [ $this, 'option_script' ], 10, 2 );

		// Add fields list to live editor.
		add_action( 'fusion_app_preview_data', [ $this, 'add_preview_data' ], 10, 3 );
	}

	/**
	 * If set, render admin notices.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function render_notices() {
		if ( '' !== $this->notices ) {
			echo $this->notices; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Add note for rendering.
	 *
	 * @access public
	 * @since 3.5
	 * @param string $message The message to display.
	 * @param string $type The type of message.
	 * @return void
	 */
	public function add_notice( $message = '', $type = 'success' ) {
		$this->notices .= '<div id="fusion-mailchimp-notice" class="notice notice-' . esc_attr( $type ) . ' avada-db-card avada-db-' . esc_attr( $type ) . '" style="display:block !important;"><h2>' . esc_html( $message ) . '</h2></div>';
	}

	/**
	 * Add fields options to live editor.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function oauth_enqueue() {

		// Back-end TO page, enqueue so markup is updated.
		if ( is_admin() && current_user_can( 'manage_options' ) && ( ( isset( $_GET['page'] ) && 'avada_options' === $_GET['page'] ) || isset( $_GET['mailchimp'] ) || isset( $_GET['revoke_mailchimp'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_api_script' ] );
		}

		// Live editor JS script always, in case they change value.
		add_action( 'fusion_builder_enqueue_live_scripts', [ $this, 'enqueue_api_script' ] );
	}

	/**
	 * Add fields options to live editor.
	 *
	 * @access public
	 * @since  3.5
	 * @param  array  $data The data already added.
	 * @param  int    $page_id The post ID being edited.
	 * @param  string $post_type The post type being edited.
	 * @return array $data The data with panel data added.
	 */
	public function add_preview_data( $data, $page_id = 0, $post_type = 'page' ) {
		if ( 'fusion_form' === $post_type ) {
			$data['mailchimp'] = [
				'fields'    => $this->get_all_fields(),
				'automatic' => __( 'Automatic Field', 'fusion-builder' ),
				'none'      => __( 'No Field', 'fusion-builder' ),
				'common'    => __( 'Common Fields', 'fusion-builder' ),
				'other'     => __( 'Other Fields', 'fusion-builder' ),
			];

		}
		return $data;
	}

	/**
	 * Enqueue script for handling OAuth.
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function enqueue_api_script() {
		wp_enqueue_script( 'fusion_mailchimp_oauth', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/js/fusion-mailchimp-oauth.js', [], FUSION_BUILDER_VERSION, true );

		wp_localize_script(
			'fusion_mailchimp_oauth',
			'fusionMailchimpOAuth',
			[
				'status' => $this->localize_status,
			]
		);
	}

	/**
	 * If we have details to try and connect.
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function can_connect() {
		if ( 'auth' === $this->type ) {
			return $this->get_token();
		} elseif ( 'key' === $this->type ) {
			return $this->get_api_key();
		}
		return false;
	}

	/**
	 * Get the API key
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function get_api_key() {

		// We already have retrieved key.
		if ( null !== $this->key ) {
			return $this->key;
		}

		// No transient.
		$fusion_settings = awb_get_fusion_settings();
		$this->key       = $fusion_settings->get( 'mailchimp_key' );

		if ( empty( $this->key ) ) {
			$this->key = false;
		}

		return $this->key;
	}

	/**
	 * Get the token data.
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function get_token() {

		// We already have retrieved a token, continue to use it.
		if ( null !== $this->token ) {
			return $this->token;
		}

		$this->token = get_option( 'fusion_mailchimp_token' );

		// No transient.
		if ( ! $this->token ) {
			$this->token = false;
		}

		// Return what we have.
		return $this->token;
	}

	/**
	 * Get the server prefix.
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function get_server_prefix() {

		// We already have retrieved a server prefix, continue to use it.
		if ( null !== $this->dc ) {
			return $this->dc;
		}

		if ( 'auth' === $this->type ) {
			$this->dc = get_option( 'fusion_mailchimp_dc' );
		} elseif ( 'key' === $this->type ) {
			$key      = $this->get_api_key();
			$dc       = explode( '-', $this->get_api_key() );
			$this->dc = isset( $dc[1] ) ? $dc[1] : false;
		}

		// Return what we have.
		return $this->dc;
	}

	/**
	 * Render info about connection status.
	 *
	 * @since 3.5
	 * @access public
	 * @return string
	 */
	public function maybe_render_button() {
		$auth_url = 'https://login.mailchimp.com/oauth2/authorize?response_type=code&client_id=594428288149&redirect_uri=' . FUSION_UPDATES_URL . '/mailchimp-api&state=' . rawurlencode( admin_url( 'admin.php?page=avada' ) );

		$type = 'connected';
		if ( isset( $_GET['error'] ) && ! empty( $_GET['error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
			$type = 'error';
		} elseif ( ! $this->get_token() ) {
			$type = 'no_token';
		}

		$output  = '<div id="fusion-mailchimp-content">';
		$output .= '<div data-id="error" style="display:' . ( 'error' === $type ? 'flex' : 'none' ) . '">';
		$output .= '<span><strong>' . esc_html__( 'There was a problem when trying to connect. ', 'fusion-builder' ) . '</strong>';
		$output .= '<a target="_blank" href="https://theme-fusion.com/documentation/avada/forms/how-to-integrate-mailchimp-with-avada-forms/">' . esc_html__( 'Mailchimp integration with Avada Forms documentation.', 'fusion-builder' ) . '</a></span>';
		$output .= '<a class="button-primary" target="_blank" href="' . $auth_url . '">' . esc_html__( 'Try again.', 'fusion-builder' ) . '</a>';
		$output .= '</div>';
		$output .= '<div data-id="no_token"  style="display:' . ( 'no_token' === $type ? 'flex' : 'none' ) . '">';
		$output .= '<span><strong>' . esc_html__( 'Currently not connected. ', 'fusion-builder' ) . '</strong>';
		$output .= '<a target="_blank" href="https://theme-fusion.com/documentation/avada/forms/how-to-integrate-mailchimp-with-avada-forms/">' . esc_html__( 'Mailchimp integration with Avada Forms documentation.', 'fusion-builder' ) . '</a></span>';
		$output .= '<a class="button-primary" target="_blank" href="' . $auth_url . '">' . esc_html__( 'Connect with Mailchimp', 'fusion-builder' ) . '</a>';
		$output .= '</div>';
		$output .= '<div data-id="connected"  style="display:' . ( 'connected' === $type ? 'flex' : 'none' ) . '">';
		$output .= '<strong>' . esc_html__( 'Connected with Mailchimp', 'fusion-builder' ) . '</strong>';
		$output .= '<a class="button-primary" target="_blank" href="' . esc_url( admin_url( 'admin.php?page=avada&revoke_mailchimp=1' ) ) . '">' . __( 'Revoke Access', 'fusion-builder' ) . '</a>';
		$output .= '</div>';

		return $output;
	}

	/**
	 * Revoke account access.
	 *
	 * @since 3.5
	 * @access public
	 * @return void
	 */
	public function revoke_access() {
		$this->reset_token();
		$this->localize_response( 'revoke' );
		$this->add_notice( __( 'Your Mailchimp account has been disconnected.', 'fusion-builder' ), 'success' );
		$this->update_global( 'off' );
	}

	/**
	 * Update global Mailchimp option.
	 *
	 * @since 3.5
	 * @access public
	 * @param string $type Type of Mailchimp api.
	 * @return void
	 */
	private function update_global( $type = 'auth' ) {
		$fusion_settings = awb_get_fusion_settings();
		$fusion_settings->set( 'mailchimp_api', $type );
		$this->type = $type;

		delete_transient( 'fusion_tos' );
		delete_transient( 'fusion_fb_tos' );
	}

	/**
	 * Localize the API scripts.
	 *
	 * @since 3.5
	 * @access public
	 * @param string $status Status type for localization.
	 * @return void
	 */
	private function localize_response( $status = 'error' ) {
		$this->localize_status = $status;
	}

	/**
	 * Reset stored access token.
	 *
	 * @since 3.5
	 * @access public
	 * @return void
	 */
	public function reset_token() {
		delete_option( 'fusion_mailchimp_token' );
		delete_option( 'fusion_mailchimp_dc' );
		$this->token = null;
	}

	/**
	 * Retrieve API response.
	 *
	 * @since 3.5
	 * @access public
	 * @param string $action Action/endpoint for API.
	 * @param array  $options Options for request.
	 * @return mixed
	 */
	public function api_request( $action = '', $options = [] ) {
		if ( '' === $action || ! $this->can_connect() || ! $this->get_server_prefix() ) {
			return false;
		}

		$method              = 'GET';
		$submission_response = [];
		$url                 = 'https://' . $this->get_server_prefix() . '.api.mailchimp.com/3.0/';

		$args = [
			'headers'      => [
				'User-Agent' => 'Fusion Mailchimp',
			],
			'timeout'      => 60,
			'headers_data' => false,
		];

		// Shared args.
		if ( 'auth' === $this->type ) {
			$args['headers']['Authorization'] = 'Bearer ' . $this->token;
		}

		// Switch for action, vary url and args.
		switch ( $action ) {
			case 'get_lists':
				$url = $url . 'lists?count=1000&fields=lists.id,lists.name';
				break;
			case 'get_fields':
				$url = $url . 'lists/' . $options['id'] . '/merge-fields?count=1000';
				break;
			case 'update_member':
				$url            = $url . 'lists/' . $options['id'] . '/members/' . md5( strtolower( $options['email'] ) );
				$args['body']   = wp_json_encode( $options['body'] );
				$args['method'] = 'PUT';
				$method         = 'POST';
				break;
		}

		// Check for no URL.
		if ( ! $url ) {
			return;
		}

		// If we are connecting via key, add it.
		if ( 'key' === $this->type ) {
			$args['headers']['Authorization'] = 'apikey: ' . $this->key;
		}

		// If we are connecting via token, add it.
		if ( 'auth' === $this->type ) {
			$args['headers']['Authorization'] = 'apikey: ' . $this->token;
		}

		// We have URL, token, action and args.  Send the API request.
		if ( 'GET' === $method ) {
			$response = wp_remote_get( $url, $args );
		} else {
			$response = wp_remote_request( $url, $args );
		}

		// Token invalid, reset token.
		if ( 401 === (int) wp_remote_retrieve_response_code( $response ) ) {
			$this->reset_token();
			$this->api_request( $action, $options );
		}

		// Check for error.
		if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {

			if ( is_array( $response ) && isset( $response['response']['code'] ) && 200 !== $response['response']['code'] ) {

				$response = json_decode( $response['body'], true );

				$submission_response = [
					'status' => $response['status'],
					'detail' => isset( $response['detail'] ) ? $response['detail'] : '',
					'errors' => isset( $response['errors'] ) ? $response['errors'] : [],
				];

				return 'update_member' === $action ? wp_json_encode( $submission_response ) : false;
			} else {
				$submission_response = [
					'status'  => isset( $response['response']['code'] ) ? $response['response']['code'] : '',
					'message' => isset( $response['response']['message'] ) ? $response['response']['message'] : '',
				];

				return 'update_member' === $action ? wp_json_encode( $submission_response ) : json_decode( $response['body'], true );
			}
		}

		return false;
	}

	/**
	 * Add field data.
	 *
	 * @since 3.5
	 * @access public
	 * @param string $post_type Post type being added to.
	 * @return void
	 */
	public function option_script( $post_type ) {
		// Not editing a form then we don't need it.
		if ( 'fusion_form' !== $post_type ) {
			return;
		}

		// No connection to API then it can't work.
		if ( ! $this->can_connect() ) {
			return;
		}

		wp_enqueue_script( 'fusion_mailchimp_option', FUSION_BUILDER_PLUGIN_URL . 'assets/admin/js/fusion-mailchimp-option.js', [], FUSION_BUILDER_VERSION, true );

		$fields = $this->get_all_fields();
		if ( $fields ) {

			// Add field data.
			wp_localize_script(
				'fusion_mailchimp_option',
				'fusionMailchimp',
				[
					'fields'    => $fields,
					'automatic' => __( 'Automatic Field', 'fusion-builder' ),
					'none'      => __( 'No Field', 'fusion-builder' ),
					'common'    => __( 'Common Fields', 'fusion-builder' ),
					'other'     => __( 'Other Fields', 'fusion-builder' ),
				]
			);
		}
	}

	/**
	 * Get full array of fields.
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function get_all_fields() {

		// Have already retrieved, return.
		if ( null !== $this->fields ) {
			return $this->fields;
		}

		// Retrieve from transient if available.
		$fields = get_transient( 'fusion_mailchimp_fields' );

		if ( $fields ) {
			$this->fields = $fields;
			return $this->fields;
		}

		// Not in transient, need to request it.
		$lists = $this->get_lists();

		if ( is_array( $lists ) && ! empty( $lists ) ) {
			foreach ( $lists['lists'] as $list ) {
				$fields_data[] = [
					'tag'       => 'EMAIL',
					'name'      => __( 'Email Address', 'fusion-builder' ),
					'type'      => 'email',
					'help_text' => '',
					'merge_id'  => '0',
				];
				$fields        = $this->api_request( 'get_fields', [ 'id' => $list['id'] ] );
				foreach ( $fields['merge_fields'] as $field ) {
					$fields_data[] = [
						'tag'       => $field['tag'],
						'name'      => $field['name'],
						'type'      => $field['type'],
						'help_text' => $field['help_text'],
						'merge_id'  => $field['merge_id'],
					];
				}
				$this->fields[ $list['id'] ] = [
					'name'   => $list['name'],
					'fields' => $fields_data,
				];
			}
		}

		if ( $this->fields ) {
			set_transient( 'fusion_mailchimp_fields', $this->fields, DAY_IN_SECONDS );
		}
		return $this->fields;
	}

	/**
	 * Get full array of lists;
	 *
	 * @since 3.5
	 * @access public
	 * @return mixed
	 */
	public function get_lists() {

		// Have already retrieved, return.
		if ( null !== $this->lists ) {
			return $this->lists;
		}

		// Retrieve from transient if available.
		$lists = get_transient( 'fusion_mailchimp_lists' );
		if ( $lists ) {
			$this->lists = $lists;
			return $this->lists;
		}

		// Not in transient, need to request it.
		$this->lists = $this->api_request( 'get_lists' );
		if ( $this->lists ) {
			set_transient( 'fusion_mailchimp_lists', $this->lists, DAY_IN_SECONDS );
		}
		return $this->lists;
	}

	/**
	 * Get the token data and store it.
	 *
	 * @since 3.5
	 * @access public
	 * @return void
	 */
	public function authenticate() {

		// Some kind of error reporting here.
		if ( ! isset( $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->localize_response( 'error' );
			$this->add_notice( __( 'There was an error authenticating your Mailchimp token.', 'fusion-builder' ), 'notice' );
			return;
		}

		// Transient with expiry to match.
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$dc    = sanitize_text_field( wp_unslash( $_GET['dc'] ) ); // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		update_option( 'fusion_mailchimp_token', $token );
		update_option( 'fusion_mailchimp_dc', $dc );
		$this->token = $token;
		$this->dc    = $dc;

		$this->localize_response( 'success' );
		$this->add_notice( __( 'Your Mailchimp account has been successfully connected.', 'fusion-builder' ), 'success' );

		$this->update_global( 'auth' );
	}

	/**
	 * Create a Mailchimp contact.
	 *
	 * @since 3.5
	 * @access public
	 * @param array $form_data Data from form which needs to be stored.
	 * @param array $form_id   The form ID.
	 * @param array $labels    Array of label and field names.
	 * @return mixed
	 */
	public function create_contact( $form_data, $form_id, $labels = [] ) {

		$list_id = fusion_data()->post_meta( $form_id )->get( 'mailchimp_lists' );
		$mapping = fusion_data()->post_meta( $form_id )->get( 'mailchimp_map' );
		$opt_in  = fusion_data()->post_meta( $form_id )->get( 'mailchimp_double_opt_in' );

		if ( ! empty( $mapping ) && is_string( $mapping ) ) {
			$mapping = json_decode( $mapping, true );
		}

		// Fields list, try to auto match.
		$fields = (array) $this->get_all_fields();
		$fields = ! empty( $list_id ) ? $fields[ $list_id ] : [];
		$fields = is_array( $fields ) && isset( $fields['fields'] ) ? $fields['fields'] : [];

		// Empty starting data.
		$mapped_data = [];

		// Request options.
		$options = [];

		// Array of assigned fields.
		$used_fields = [];

		// Loop each form field to check for mapping match.
		foreach ( $form_data['data'] as $field => $value ) {
			$field_value = ( is_array( $value ) ) ? implode( ' | ', $value ) : $value;

			// Update to correct format.
			$form_data['data'][ $field ] = $field_value;

			// Check if we have a desired field set in map.
			if ( isset( $mapping[ $field ] ) && '' !== $mapping[ $field ] ) {

				// If its set to have no field match, entirely exclude from matching.
				if ( 'fusion-none' === $mapping[ $field ] ) {
					unset( $form_data['data'][ $field ] );
					unset( $labels[ $field ] );
					continue;
				}

				// If we are matching to email, set as target contact.
				if ( 'EMAIL' === $mapping[ $field ] ) {
					$options['email'] = $field_value;
				}

				// Add to mapped data we will send.
				$mapped_data[ $mapping[ $field ] ] = $field_value;

				$used_fields[ $mapping[ $field ] ] = true;
			}
		}

		// Auto matching if not all are set already.
		if ( count( $form_data['data'] ) !== count( $mapped_data ) ) {
			foreach ( $fields as $field ) {

				$value = false;

				// Field is already assigned, do not assign again.
				if ( isset( $used_fields[ $field['tag'] ] ) ) {
					continue;
				}

				// Field name matches input name.
				if ( isset( $form_data['data'][ $field['name'] ] ) ) {
					$value                        = $form_data['data'][ $field['name'] ];
					$mapped_data[ $field['tag'] ] = $value;

					// Field tag matches input label.
				} elseif ( false !== $field_id = array_search( $field['tag'], $labels, true ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments, WordPress.CodeAnalysis.AssignmentInCondition
					$value                        = $form_data['data'][ $field_id ];
					$mapped_data[ $field['tag'] ] = $value;

					// Field name matches input label.
				} elseif ( false !== $field_id = array_search( $field['name'], $labels, true ) ) { // phpcs:ignore Squiz.PHP.DisallowMultipleAssignments, WordPress.CodeAnalysis.AssignmentInCondition
					$value                        = $form_data['data'][ $field_id ];
					$mapped_data[ $field['tag'] ] = $value;

					// Field tag matches input name.
				} elseif ( isset( $form_data['data'][ str_replace( ' ', '', strtolower( $field['tag'] ) ) ] ) ) {
					$field_id                     = str_replace( ' ', '', strtolower( $field['tag'] ) );
					$value                        = $form_data['data'][ $field_id ];
					$mapped_data[ $field['tag'] ] = $value;
				}

				// If email is one, add to options.
				if ( $value && 'email' === $field['name'] ) {
					$options['email'] = $value;
				}
			}
		}

		// No valid email, contact cannot be created or updated.
		if ( ! isset( $options['email'] ) || empty( $options['email'] ) ) {
			return;
		}

		// We made it this far, add data and set request.

		$options['body']['merge_fields']  = $mapped_data;
		$options['body']['email_address'] = $options['email'];
		$options['body']['status_if_new'] = 'yes' === $opt_in ? 'pending' : 'subscribed';

		$options['body']['status'] = 'subscribed';
		$options['id']             = $list_id;

		return $this->api_request( 'update_member', $options );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @param array $sections Page options.
	 * @since 3.5
	 */
	public function maybe_add_option( $sections ) {
		if ( 'off' === $this->type ) {
			$mailchimp_link = '<a target="_blank" rel="noopener noreferrer" href="http://eepurl.com/hCanr5">Mailchimp account</a>';
			$document_link  = '<a target="_blank" rel="noopener noreferrer" href="https://theme-fusion.com/documentation/avada/forms/how-to-integrate-mailchimp-with-avada-forms/">Mailchimp integration guide</a>';
			$sections['form_submission']['fields']['mailchimp_info'] = [
				'type'        => 'custom',
				'label'       => '',

				/* translators: 1: Mailchimp link. 2: Documentation link. */
				'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( 'Sign up for a %1$s and manage your contacts in their free CRM.  For more information check out our %2$s. ', 'fusion-builder' ), $mailchimp_link, $document_link ) . '</div>',
				'id'          => 'mailchimp_info',
				'dependency'  => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
					[
						'field'      => 'form_actions',
						'value'      => 'mailchimp',
						'comparison' => 'contains',
					],
				],
			];

			return $sections;
		}

		$lists = $this->get_lists();
		if ( $this->can_connect() && $lists ) {
			$lists_data = [];
			foreach ( $lists['lists'] as $list ) {
				$lists_data[ $list['id'] ] = $list['name'];
			}

			$sections['form_submission']['fields']['mailchimp_options'] = [
				'type'       => 'toggle',
				'row_title'  => esc_html__( 'Mailchimp', 'fusion-builder' ),
				'id'         => 'mailchimp_options',
				'dependency' => [
					[
						'field'      => 'form_type',
						'value'      => 'ajax',
						'comparison' => '==',
					],
					[
						'field'      => 'form_actions',
						'value'      => 'mailchimp',
						'comparison' => 'contains',
					],
				],
				'fields'     => [
					'mailchimp_info'          => [
						'type'        => 'custom',
						'label'       => '',
						'description' => '<div class="fusion-redux-important-notice">' . __( 'You are currently connected to the Mailchimp API.', 'fusion-builder' ) . '</div>',
						'id'          => 'mailchimp_info',
					],
					'mailchimp_action'        => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Mailchimp Action', 'fusion-builder' ),
						'description' => esc_html__( 'Select if you want to perform a Mailchimp action after form submission.', 'fusion-builder' ),
						'id'          => 'mailchimp_action',
						'default'     => 'no',
						'transport'   => 'postMessage',
						'choices'     => [
							'no'      => esc_html__( 'None', 'fusion-builder' ),
							'contact' => esc_html__( 'Create/Update Contact', 'fusion-builder' ),
						],
					],
					'mailchimp_lists'         => [
						'type'        => 'select',
						'label'       => esc_html__( 'Mailchimp List', 'fusion-builder' ),
						'description' => __( 'Select Mailchimp list.', 'fusion-builder' ),
						'id'          => 'mailchimp_lists',
						'choices'     => $lists_data,
						'transport'   => 'postMessage',
						'dependency'  => [
							[
								'field'      => 'mailchimp_action',
								'value'      => 'contact',
								'comparison' => '==',
							],
						],
					],
					'mailchimp_double_opt_in' => [
						'type'        => 'radio-buttonset',
						'label'       => esc_html__( 'Double Opt-In', 'fusion-builder' ),
						'description' => __( 'With double opt-in, everyone who signs up will receive a follow-up email with a confirmation link to verify their subscription.', 'fusion-builder' ),
						'id'          => 'mailchimp_double_opt_in',
						'default'     => 'no',
						'transport'   => 'postMessage',
						'choices'     => [
							'yes' => esc_html__( 'Yes', 'fusion-builder' ),
							'no'  => esc_html__( 'No', 'fusion-builder' ),
						],
						'dependency'  => [
							[
								'field'      => 'mailchimp_action',
								'value'      => 'contact',
								'comparison' => '==',
							],
							[
								'field'      => 'mailchimp_lists',
								'value'      => '',
								'comparison' => '!=',
							],
						],
					],
					'mailchimp_map'           => [
						'type'        => 'mailchimp_map',
						'label'       => esc_html__( 'Mailchimp Mapping', 'fusion-builder' ),
						'description' => __( 'Map fields from the form to Mailchimp list merge tags. <strong>NOTE:</strong> The email property is required for creating or updating a contact. When mapping is set to "Automatic", Avada will try to map based on field label, name and tags.', 'fusion-builder' ),
						'id'          => 'mailchimp_map',
						'transport'   => 'postMessage',
						'dependency'  => [
							[
								'field'      => 'mailchimp_action',
								'value'      => 'contact',
								'comparison' => '==',
							],
							[
								'field'      => 'mailchimp_lists',
								'value'      => '',
								'comparison' => '!=',
							],
						],
					],
				],
			];

			return $sections;
		}

		$mailchimp_link = '<a target="_blank" rel="noopener noreferrer" href="' . esc_url( admin_url( 'themes.php?page=avada_options#mailchimp_api' ) ) . '">Mailchimp</a>';
		$sections['form_submission']['fields']['mailchimp_info'] = [
			'type'        => 'custom',
			'label'       => '',
			/* translators: Global link. */
			'description' => '<div class="fusion-redux-important-notice">' . sprintf( __( 'Connect to your %s account to create contacts from your form.', 'fusion-builder' ), $mailchimp_link ) . '</div>',
			'id'          => 'mailchimp_info',
		];

		return $sections;
	}

	/**
	 * Handles resetting caches.
	 *
	 * @access public
	 * @since 3.5
	 * @return void
	 */
	public function reset_caches_handler() {
		if ( is_multisite() && is_main_site() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				delete_transient( 'fusion_mailchimp_lists' );
				delete_transient( 'fusion_mailchimp_fields' );
				restore_current_blog();
			}
			return;
		}
		delete_transient( 'fusion_mailchimp_lists' );
		delete_transient( 'fusion_mailchimp_fields' );
	}

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @static
	 * @access public
	 * @since 3.5
	 */
	public static function get_instance() {

		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null === self::$instance ) {
			self::$instance = new Fusion_Mailchimp();
		}
		return self::$instance;
	}
}

/**
 * Instantiates the Fusion_Mailchimp class.
 * Make sure the class is properly set-up.
 *
 * @since object 3.5
 * @return object Fusion_App
 */
function Fusion_Mailchimp() { // phpcs:ignore WordPress.NamingConventions
	return Fusion_Mailchimp::get_instance();
}
Fusion_Mailchimp();
