<?php
/**
 * Registration handler.
 *
 * @package Fusion-Library
 * @since 1.0.0
 */

/**
 * A class to handle everything related to product registration
 *
 * @since 1.0.0
 */
class Fusion_Product_Registration {

	/**
	 * The option name.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $option_name = 'fusion_registration_data';

	/**
	 * Holding the available registration data.
	 *
	 * @access private
	 * @since 1.9.2
	 * @var array
	 */
	private $registration_data = [];

	/**
	 * The arguments that are used in the constructor.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private $args = [];

	/**
	 * The product-name converted to ID.
	 *
	 * @access private
	 * @since 1.0.0
	 * @var string
	 */
	private $product_id = '';

	/**
	 * An array of bundled products.
	 *
	 * @static
	 * @access private
	 * @since 1.0.0
	 * @var array
	 */
	private static $bundled = [];

	/**
	 * Updater
	 *
	 * @access private
	 * @since 1.0.0
	 * @var null|object Fusion_Updater.
	 */
	private $updater = null;

	/**
	 * Server API response as WP_Error object.
	 *
	 * @access private
	 * @since 3.3
	 * @var null|object WP_Error.
	 */
	private $errors = null;

	/**
	 * The class constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 * @param array $args An array of our arguments [string "type", string "name", array "bundled"].
	 */
	public function __construct( $args = [] ) {

		$this->args       = $args;
		$this->product_id = sanitize_key( $args['name'] );

		if ( isset( $args['bundled'] ) ) {
			$this->add_bundled_product( $args['bundled'] );
		}

		$this->set_registration_data();

		// Instantiate the updater.
		if ( null === $this->updater ) {
			$this->updater = new Fusion_Updater( $this );
		}

		add_action( 'wp_ajax_avada_product_registration', [ $this, 'ajax_check_registration' ] );
	}

	/**
	 * Adds a product to the array of bundled products.
	 *
	 * @access private
	 * @since 1.0.0
	 * @param array $bundled An array o bundled products.
	 */
	private function add_bundled_product( $bundled ) {

		$bundled = (array) $bundled;
		foreach ( $bundled as $product_slug => $product_name ) {
			$product = sanitize_key( $product_name );

			if ( ! isset( self::$bundled[ $product ] ) ) {
				self::$bundled[ $product ] = $this->args['name'];
			}
		}
	}

	/**
	 * Gets bundled products array.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_bundled() {

		return self::$bundled;
	}

	/**
	 * Gets the arguments.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return array
	 */
	public function get_args() {

		return $this->args;
	}

	/**
	 * Checks if the product is part of the themes or plugins
	 * purchased by the user belonging to the token.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function ajax_check_registration() {
		if ( ! isset( $_POST['avada_product_reg'] ) || ! wp_verify_nonce( $_POST['avada_product_reg'], 'avada_product_reg_nonce' ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			exit( 'Invalid request.' );
		}

		$this->check_registration();

		ob_start();
		$this->the_form();
		$response = ob_get_clean();

		exit( $response ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	/**
	 * Checks if the product is part of the themes or plugins
	 * purchased by the user belonging to the token.
	 *
	 * @access public
	 * @since 1.0.0
	 */
	public function check_registration() {

		// Sanity check. No need to do anything if we're not saving the form.
		if ( ( isset( $_POST[ $this->option_name ] ) && isset( $_POST[ $this->option_name ][ $this->product_id ] ) || isset( $_POST['avada_unregister_product'] ) ) && isset( $_POST['_wpnonce'] ) ) {

			// Reset saved errors.
			$this->errors = null;

			// Revoking or registering.
			$revoke = isset( $_POST['avada_unregister_product'] ) && '1' === $_POST['avada_unregister_product'];

			// Security check.
			check_admin_referer( $this->option_name . '_' . $this->product_id );

			// No purchase code passed and we are not revoking.
			if ( ! $revoke && ! isset( $_POST[ $this->option_name ][ $this->product_id ]['purchase_code'] ) ) {
				return;
			}

			if ( $revoke ) {
				$purchase_code = $this->get_purchase_code();
				$revoked       = $this->revoke_purchase_code( $purchase_code );

				// Always revoke, regardless of response.
				$valid         = false;
				$purchase_code = '';
				$this->registration_data[ $this->product_id ]['token'] = '';
			} else {
				$purchase_code = sanitize_text_field( wp_unslash( $_POST[ $this->option_name ][ $this->product_id ]['purchase_code'] ) );
				$purchase_code = wp_strip_all_tags( trim( $purchase_code ) );
				$valid         = $this->check_purchase( $purchase_code );
			}

			// Update saved product data.
			$this->registration_data[ $this->product_id ]['purchase_code'] = $purchase_code;
			$this->registration_data[ $this->product_id ]['is_valid']      = $valid;
			$this->registration_data[ $this->product_id ]['errors']        = null !== $this->errors ? $this->errors : '';

			$this->update_data();

			// Refresh data for grace period.
			delete_transient( 'avada_dashboard_data' );
		}
	}

	/**
	 * Update data to database.
	 *
	 * @access public
	 * @since 3.3
	 * @return void
	 */
	public function update_data() {
		$save_data = $this->registration_data;

		// Filter out non-persistent error messages.
		if ( isset( $save_data[ $this->product_id ]['errors'] ) && is_wp_error( $save_data[ $this->product_id ]['errors'] ) ) {
			$error_code = $save_data[ $this->product_id ]['errors']->get_error_code();
			if ( 400 === $error_code ) {
				$save_data[ $this->product_id ]['errors'] = '';
			}
		} else {
			$save_data[ $this->product_id ]['errors'] = '';
		}

		update_option( $this->option_name, $save_data );
	}

	/**
	 * Update data to database, CLI version.
	 *
	 * @access public
	 * @since 3.4
	 * @param array $registration_data Registration data.
	 * @return void
	 */
	public function cli_update_data( $registration_data ) {

		// Early exit.
		if ( empty( $registration_data ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		$save_data = $registration_data;

		// Filter out non-persistent error messages.
		if ( isset( $save_data['avada']['errors'] ) && is_wp_error( $save_data['avada']['errors'] ) ) {
			$error_code = $save_data['avada']['errors']->get_error_code();
			if ( 400 === $error_code ) {
				$save_data['avada']['errors'] = '';
			}
		} else {
			$save_data['avada']['errors'] = '';
		}

		update_option( $this->option_name, $save_data );
	}

	/**
	 * Get errors property.
	 *
	 * @access public
	 * @since 3.4
	 * @return null|object
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Check if purchase code is valid.
	 *
	 * @access public
	 * @since 3.3
	 * @param string $purchase purchase code.
	 * @return bool
	 */
	public function check_purchase( $purchase = '' ) {
		if ( '' === $purchase ) {
			$this->errors = $this->get_error( 400, 'auth' );
			return false;
		}
		if ( false === strpos( $purchase, '-' ) && 32 === strlen( $purchase ) ) {
			$this->errors = $this->get_error( 401, 'token' );
			return;
		}
		if ( 36 !== strlen( $purchase ) || 4 !== substr_count( $purchase, '-' ) ) {
			$this->errors = $this->get_error( 401, 'auth' );
			return false;
		}

		$args     = [
			'timeout'    => 60,
			'user-agent' => 'fusion-purchase-code',
		];
		$response = wp_remote_get( FUSION_UPDATES_URL . '/wp-json/avada-api/validate-code/' . $purchase, $args );

		if ( is_wp_error( $response ) ) {
			$this->errors = $response;
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 399 < $code && 501 > $code ) {
			$this->errors = $this->get_error( $code, 'auth' );
			return false;
		}
		$response = isset( $response['body'] ) ? json_decode( $response['body'], true ) : false;
		if ( true === $response ) {
			return true;
		}

		$this->get_error( $code, 'auth' );
		return false;
	}

	/**
	 * Registration doesn't appear to be valid.
	 *
	 * @access public
	 * @param object $error WordPress error object.
	 * @return object
	 * @since 3.3
	 */
	public function invalidate( $error = '' ) {
		$this->registration_data[ $this->product_id ]['is_valid'] = false;
		$this->registration_data[ $this->product_id ]['errors']   = $error;
		$this->update_data();
		return $error;
	}

	/**
	 * Get error for code.
	 *
	 * @access public
	 * @param int    $code HTTP code.
	 * @param string $request Request type.
	 * @return object WP error
	 * @since 3.3
	 */
	public function get_error( $code = 403, $request = 'auth' ) {
		$support_link = '<a href="https://theme-fusion.com/contact-us/">ThemeFusion</a>';

		switch ( (int) $code ) {
			// No code.
			case 400:
				if ( 'revoke' === $request ) {
					return new WP_Error( $code, __( 'No purchase code was passed on to be revoked.', 'Avada' ) );
				}
				if ( 'prebuilt' === $request ) {
					return new WP_Error( $code, __( 'In order to import a prebuilt website Avada must be registered. No purchase code was found.', 'Avada' ) );
				}
				return new WP_Error( $code, __( 'No purchase code was provided.', 'Avada' ) );

			// No domain.
			case 417:
				if ( 'revoke' === $request ) {
					return new WP_Error( $code, __( 'No domain was passed on and therefore revoke could not be confirmed.', 'Avada' ) );
				}
				if ( 'prebuilt' === $request ) {
					return new WP_Error( $code, __( 'In order to import a prebuilt website Avada must be registered. No domain was passed on, therefore validation could not be confirmed.', 'Avada' ) );
				}
				return new WP_Error( $code, __( 'No domain was passed on in the request, this is needed to confirm registration.', 'Avada' ) );

			// Invalid purchase code.
			case 401:
				if ( 'token' === $request ) {
					return new WP_Error( $code, __( 'Invalid purchase code. Code provided appears to be a token instead.', 'Avada' ) );
				}
				if ( 'download' === $request ) {
					return $this->invalidate( new WP_Error( $code, __( 'Invalid purchase code.', 'Avada' ) ) );
				}
				if ( 'prebuilt' === $request ) {
					return $this->invalidate( new WP_Error( $code, __( 'In order to import a prebuilt website Avada must be registered. The purchase code being used does not seem to be valid.', 'Avada' ) ) );
				}
				return new WP_Error( $code, __( 'Invalid purchase code.', 'Avada' ) );

			// Envato forbidden.
			case 403:
				return new WP_Error( $code, __( 'Envato API did not respond. Either the purchase code is incorrect or the API is temporarily unavailable.', 'Avada' ) );

			// Domain mismatch.
			case 409:
				if ( 'download' === $request ) {
					return $this->invalidate( new WP_Error( $code, __( 'The purchase code is already being used on another domain.', 'Avada' ) ) );
				}
				if ( 'revoke' === $request ) {
					return new WP_Error( $code, __( 'The current domain does not match our records and therefore was not revoked.', 'Avada' ) );
				}
				if ( 'prebuilt' === $request ) {
					return $this->invalidate( new WP_Error( $code, __( 'In order to import a prebuilt website Avada must be registered. The current domain does not match our records.', 'Avada' ) ) );
				}
				return new WP_Error( $code, __( 'The purchase code is already being used on another domain.', 'Avada' ) );

			// Staging mismatch.
			case 412:
				if ( 'download' === $request ) {
					return $this->invalidate( new WP_Error( $code, __( 'The purchase code is already being used on another staging domain.', 'Avada' ) ) );
				}
				if ( 'prebuilt' === $request ) {
					return $this->invalidate( new WP_Error( $code, __( 'In order to import a prebuilt website Avada must be registered. The current staging domain does not match our records.', 'Avada' ) ) );
				}
				return new WP_Error( $code, __( 'The purchase code is already being used on another staging domain.', 'Avada' ) );

			// Purchase code locked.
			case 423:
				/* translators: "ThemeFusion" contact link. */
				return $this->invalidate( new WP_Error( $code, sprintf( __( 'This purchase code has been locked, as it was used in a manner that violates our license terms. Please contact %s to resolve.', 'Avada' ), $support_link ) ) );

			// Envato API limited.
			case 429:
				return new WP_Error( $code, __( 'Sorry, the API is currently overloaded. Please try again later.', 'Avada' ) );

			// Too many registrations.
			case 406:
				/* translators: "ThemeFusion" contact link. */
				return new WP_Error( $code, sprintf( __( 'The purchase code has been registered too many times. Please contact %s to resolve.', 'Avada' ), $support_link ) );
		}

		return new WP_Error( $code, __( 'Unknown error encountered. Please try again later.', 'Avada' ) );
	}

	/**
	 * Bypass active or not.
	 *
	 * @access public
	 * @since 3.3
	 */
	public function bypass_active() {
		$data = Avada::get_data();
		if ( isset( $data['bypass_active'] ) && $data['bypass_active'] ) {
			return true;
		}
		return false;
	}

	/**
	 * Whether user should see restricted UI or not.
	 *
	 * @access public
	 * @param string $type section type.
	 * @since 3.3
	 */
	public function should_show( $type = 'plugins' ) {
		return $this->is_registered() || $this->legacy_support() || $this->bypass_active();
	}

	/**
	 * Reset token because its invalid.
	 *
	 * @access public
	 * @since 3.3
	 */
	public function reset_token() {
		$this->registration_data[ $this->product_id ]['token'] = '';
		$this->update_data();
	}

	/**
	 * Revoke purchase code.
	 *
	 * @access public
	 * @param string $purchase Purchase code to revoke.
	 * @since 3.3
	 */
	public function revoke_purchase_code( $purchase = '' ) {
		$args     = [
			'timeout'    => 60,
			'user-agent' => 'fusion-purchase-code',
		];
		$response = wp_remote_get( FUSION_UPDATES_URL . '/wp-json/avada-api/revoke-code/' . $purchase, $args );

		if ( is_wp_error( $response ) ) {
			$this->errors = $response;
			return false;
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( 399 < $code && 501 > $code ) {
			$this->errors = $this->get_error( $code, 'revoke' );
			return false;
		}
		$response = isset( $response['body'] ) ? json_decode( $response['body'], true ) : false;
		if ( true === $response ) {
			return true;
		}

		$this->get_error( $code, 'revoke' );
		return false;
	}

	/**
	 * Check if updates can be served in grace period.
	 *
	 * @access public
	 * @since 1.9.2
	 * @param string $product_id The plugin/theme ID.
	 * @return bool
	 */
	public function legacy_support( $product_id = '' ) {
		if ( ! $product_id ) {
			$product_id = $this->product_id;
		}

		if ( ! isset( $this->registration_data[ $product_id ] ) ) {
			return false;
		}

		// No token, need to register.
		if ( '' === $this->get_token() ) {
			return false;
		}

		$data = Avada::get_data();

		// Token and no grace set, must be first plugin update.
		if ( ! isset( $data['legacy_end'] ) || '' === $data['legacy_end'] ) {
			return true;
		}

		// Current time is before end date, grace is valid.
		$current    = new DateTime( gmdate( 'D, d M Y H:i' ) );
		$legacy_end = new DateTime( gmdate( 'D, d M Y H:i', $data['legacy_end'] ) );
		if ( $current < $legacy_end ) {
			return true;
		}

		// Grace has ended.
		return false;
	}

	/**
	 * Set available registration data.
	 *
	 * @access public
	 * @since 1.9.2
	 * @return void
	 */
	public function set_registration_data() {
		$registration_data        = [];
		$registration_data_stored = get_option( $this->option_name, [] );

		$registration_data_dummy = [
			'token'         => '',
			'purchase_code' => '',
			'is_valid'      => 'false',
			'scopes'        => [],
			'errors'        => '',
		];

		foreach ( $registration_data_stored as $product => $data ) {
			$registration_data[ $product ] = wp_parse_args( $data, $registration_data_dummy );
		}

		// No data at all, set it to dummy data.
		if ( ! isset( $registration_data[ $this->product_id ] ) ) {
			$registration_data[ $this->product_id ] = $registration_data_dummy;
		}

		// if we have stored errors, set them to display.
		if ( isset( $registration_data[ $this->product_id ]['errors'] ) && '' !== $registration_data[ $this->product_id ]['errors'] ) {
			$this->errors = $registration_data[ $this->product_id ]['errors'];
		}

		$this->registration_data = $registration_data;
	}

	/**
	 * Check if product is part of registration data and is also valid.
	 *
	 * @access public
	 * @since 1.9.2
	 * @param string $product_id The plugin/theme ID.
	 * @return bool
	 */
	public function is_registered( $product_id = '' ) {
		if ( ! $product_id ) {
			$product_id = $this->product_id;
		}

		if ( ! isset( $this->registration_data[ $product_id ] ) ) {
			return false;
		}

		if ( '' === $this->registration_data[ $product_id ]['purchase_code'] ) {
			return false;
		}

		// Is the product registered?
		if ( true === $this->registration_data[ $product_id ]['is_valid'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Legacy update and builder has not been updated yet.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return boolean
	 */
	public function is_legacy_update() {
		return '' !== $this->get_token() && defined( 'FUSION_BUILDER_VERSION' ) && version_compare( FUSION_BUILDER_VERSION, '3.3', '<' );
	}

	/**
	 * If it should appear as if registered.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return boolean
	 */
	public function appear_registered() {
		return $this->is_registered() || $this->is_legacy_update();
	}

	/**
	 * Returns the stored token for the product.
	 *
	 * @access public
	 * @since 1.0.0
	 * @param string $product_id The product-ID.
	 * @return string The current token.
	 */
	public function get_token( $product_id = '' ) {
		if ( '' === $product_id ) {
			$product_id = $this->product_id;
		}

		if ( isset( $this->registration_data[ $product_id ] ) ) {
			return $this->registration_data[ $product_id ]['token'];
		}

		return '';
	}

	/**
	 * Returns the purchase code
	 *
	 * @access public
	 * @since 3.3
	 * @param string $product_id The product-ID.
	 * @return string The current token.
	 */
	public function get_purchase_code( $product_id = '' ) {
		if ( '' === $product_id ) {
			$product_id = $this->product_id;
		}

		if ( isset( $this->registration_data[ $product_id ] ) ) {
			return $this->registration_data[ $product_id ]['purchase_code'];
		}

		return '';
	}

	/**
	 * Prints the registration form.
	 *
	 * @access public
	 * @since 1.0.0
	 * @return void
	 */
	public function the_form() {

		/**
		 * Check registration. Now done in the admin class.
		 *
		$this->check_registration();
		 */

		// Get the stored token.
		$token         = $this->get_token();
		$purchase_code = $this->get_purchase_code();

		// Is the product registered?
		$is_registered = $this->appear_registered();
		?>
		<h2 class="avada-db-reg-heading">
			<?php if ( $purchase_code ) : ?>
				<?php if ( $is_registered ) : ?>
					<i class="fusiona-verified avada-db-reg-icon"></i>
				<?php else : ?>
					<i class="fusiona-cross avada-db-reg-icon"></i>
				<?php endif; ?>
			<?php else : ?>
				<i class="fusiona-unlock avada-db-reg-icon"></i>
			<?php endif; ?>
			<?php if ( ! $is_registered ) : ?>
				<span class="avada-db-reg-heading-text"><?php esc_html_e( 'Register Your Website', 'Avada' ); ?></span>
			<?php else : ?>
				<span class="avada-db-reg-heading-text"><?php esc_html_e( 'Your Website is Registered', 'Avada' ); ?></span>
			<?php endif; ?>
			<span class="avada-db-card-heading-badge avada-db-card-heading-badge-howto">
				<i class="fusiona-help-outlined"></i>
				<span class="avada-db-card-heading-badge-text"><?php esc_html_e( 'How To?', 'Avada' ); ?></span>
			</span>
		</h2>
		<div class="avada-db-reg-form-container">
			<?php if ( $is_registered ) : ?>
				<p class="avada-db-reg-text">
					<?php
						/* translators: Link. */
						printf( __( 'Congratulations, and thank you for registering your website. To manage your licenses, sign up on %s.', 'Avada' ), '<a href="https://theme-fusion.com/support/account/" target="_blank">theme-fusion.com</a>' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
			<?php else : ?>
				<p class="avada-db-reg-text">
					<?php 
					esc_html_e( 'Please enter your Avada purchase code and get access to our prebuilt websites, auto-updates, and premium plugins. The purchase code and site URL will be sent to a ThemeFusion server located in the U.S. to verify the purchase.', 'Avada' );

					// Add note about installing plugins on Setup page.
					if ( isset( $_GET['page'] ) && 'avada-setup' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						echo '&nbsp;';
						esc_html_e( 'After registration is completed required Avada plugins will be installed and activated if needed.', 'Avada' );
					}
					?>
				</p>
			<?php endif; ?>

			<form class="avada-db-reg-form" method="post">
				<div class="avada-db-reg-input-wrapper">
					<div class="avada-db-reg-loader"><span class="avada-db-loader"></span></div>
					<i class="fusiona-key avada-db-reg-input-icon"></i>
					<?php
						$disabled = '';

					if ( $is_registered ) {
						$code_length   = strlen( $purchase_code ) / 2;
						$purchase_code = substr( $purchase_code, 0, $code_length ) . str_repeat( '*', $code_length );
						$disabled      = ' ';
					}
					?>
					<input type="text" class="avada-db-registration-input" name="<?php echo esc_attr( "{$this->option_name}[{$this->product_id}][purchase_code]" ); ?>" value="<?php echo esc_attr( $purchase_code ); ?>"<?php echo esc_attr( $disabled ); ?> />
				</div>
				<?php $reg_button_text = __( 'Register Now', 'Avada' ); ?>
				<?php if ( $is_registered ) : ?>
					<?php $reg_button_text = __( 'Unregister', 'Avada' ); ?>
					<input type="hidden" name="avada_unregister_product" value="1">
				<?php endif; ?>
				<?php if ( isset( $_GET['no_ajax_reg'] ) && '1' === $_GET['no_ajax_reg'] ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
					<input type="hidden" name="no_ajax_reg" value="1">
				<?php endif; ?>
				<input type="hidden" name="action" value="avada_product_registration">
				<?php wp_nonce_field( $this->option_name . '_' . $this->product_id ); ?>
				<?php wp_nonce_field( 'avada_product_reg_nonce', 'avada_product_reg' ); ?>
				<?php submit_button( esc_html( $reg_button_text ), 'primary avada-db-reg-button', 'submit', false ); ?>
			</form>

			<?php if ( $this->errors ) : ?>
				<div class="avada-db-card-error">
					<?php echo $this->errors->get_error_message(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<div class="avada-db-reg-howto">
				<h3 class="avada-db-reg-howto-heading"><?php esc_html_e( 'How To Find Your Purchase Code', 'Avada' ); ?></h3>
				<ol class="avada-db-reg-howto-list avada-db-card-text-small">
					<li>
						<?php
						printf(
							/* translators: "ThemeForest sign in link" link. */
							__( 'Sign in to your %s. <strong>IMPORTANT:</strong> You must be signed into the same ThemeForest account that purchased Avada. If you are signed in already, look in the top menu bar to ensure it is the right account.', 'Avada' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<a href="https://themeforest.net/sign_in" target="_blank">' . esc_html__( 'ThemeForest account', 'Avada' ) . '</a>'
						);
						?>
					</li>
					<li>
						<?php
						printf(
							/* translators: "Generate A Personal Token" link. */
							__( 'Visit the %s. You should see a row for Avada.  If you don\'t, please re-check step 1 that you are on the correct account.', 'Avada' ), // phpcs:ignore WordPress.Security.EscapeOutput
							'<a href="https://themeforest.net/downloads" target="_blank">' . esc_html__( 'ThemeForest downloads page', 'Avada' ) . '</a>'
						);
						?>
					</li>
					<li>
						<?php
							esc_html_e( 'Click the download button in the Avada row.', 'Avada' )
						?>
					</li>
					<li>
						<?php
							esc_html_e( 'Select either License certificate & purchase code (PDF) or License certificate & purchase code (text). This should then download either a text or PDF file.', 'Avada' )
						?>
					</li>
					<li>
						<?php
							esc_html_e( 'Open up that newly downloaded file and copy the Item Purchase Code.', 'Avada' )
						?>
					</li>
				</ol>
			</div>
		</div>
		<?php
	}
}

/* Omit closing PHP tag to avoid "Headers already sent" issues. */
