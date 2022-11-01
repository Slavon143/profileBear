<?php
/**
 * Handler for contact pages.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada Core
 * @subpackage Core
 * @since      3.9.2
 */

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

/**
 * Handle contact pages.
 *
 * @since 3.9.2
 */
class Fusion_Contact {

	/**
	 * The email address the contact form will be sent to.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $email_address = '';

	/**
	 * The reCAPTCHA class instance.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var bool|object
	 */
	public $re_captcha = false;

	/**
	 * The reCAPTCHA version.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $re_captcha_version = 'v3';


	/**
	 * The reCAPTCHA public key.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $re_captcha_public_key = '';

	/**
	 * The reCAPTCHA privatekey.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $re_captcha_private_key = '';

	/**
	 * The reCAPTCHA badge position.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $re_captcha_badge_position = 'inline';

	/**
	 * The reCAPTCHA score.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var float
	 */
	private $rre_captcha_score = 0.5;

	/**
	 * Position of the comment textarea.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $comment_position = 'below';

	/**
	 * Whether the privacy checkbox should be displayed.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var int
	 */
	private $privacy_checkbox = 0;

	/**
	 * Label for the privacy checkbox.
	 *
	 * @access private
	 * @since 3.9.2
	 * @var string
	 */
	private $privacy_label = '';

	/**
	 * Do we have an error?
	 *
	 * @access public
	 * @since 3.9.2
	 * @var bool
	 */
	public $has_error = false;

	/**
	 * Contact name
	 *
	 * @access public
	 * @since 3.9.2
	 * @var string
	 */
	public $error_message = '';

	/**
	 * Contact name.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var string
	 */
	public $name = '';

	/**
	 * Subject.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var string
	 */
	public $subject = '';

	/**
	 * Email address.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var string
	 */
	public $email = '';

	/**
	 * The message.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var string
	 */
	public $message = '';

	/**
	 * Data privacy confirmation checkbox text.
	 *
	 * @access public
	 * @since 3.9.2
	 * @var int
	 */
	public $data_privacy_confirmation = 0;

	/**
	 * Has the email been sent?
	 *
	 * @access public
	 * @since 3.9.2
	 * @var bool
	 */
	public $email_sent = false;

	/**
	 * The class constructor.
	 *
	 * @access public
	 * @since 3.9.2
	 * @param array $args An array of arguments for the contact.
	 * @return void
	 */
	public function __construct( $args ) {
		$this->email_address             = isset( $args['email_address'] ) ? $args['email_address'] : '';
		$this->re_captcha_version        = isset( $args['recaptcha_version'] ) ? $args['recaptcha_version'] : 'v3';
		$this->re_captcha_public_key     = isset( $args['public_key'] ) ? $args['public_key'] : '';
		$this->re_captcha_private_key    = isset( $args['private_key'] ) ? $args['private_key'] : '';
		$this->re_captcha_badge_position = isset( $args['badge_position'] ) ? $args['badge_position'] : 'inline';
		$this->re_captcha_score          = isset( $args['recaptcha_score'] ) ? $args['recaptcha_score'] : 0.5;
		$this->comment_position          = isset( $args['comment_position'] ) ? $args['comment_position'] : 'below';
		$this->privacy_checkbox          = isset( $args['privacy_checkbox'] ) ? $args['privacy_checkbox'] : 0;
		$this->privacy_label             = isset( $args['privacy_label'] ) ? $args['privacy_label'] : esc_html__( 'By checking this box, you confirm that you have read and are agreeing to our terms of use regarding the storage of the data submitted through this form.', 'fusion-core' );

		$this->init_recaptcha();
		if ( isset( $_POST['submit'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->set_error_message();
			$this->process_name();
			$this->process_subject();
			$this->process_email();
			$this->process_message();

			if ( $this->privacy_checkbox ) {
				$this->process_data_privacy_confirmation();
			}
			$this->process_recaptcha();

			if ( ! $this->has_error ) {
				$this->send_email();
			}
		}
	}

	/**
	 * Setup reCAPTCHA.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function init_recaptcha() {
		if ( $this->re_captcha_public_key && $this->re_captcha_private_key && ! function_exists( 'recaptcha_get_html' ) && ! class_exists( 'ReCaptcha' ) ) {
			require_once FUSION_LIBRARY_PATH . '/inc/recaptcha/src/autoload.php';
			// We use a wrapper class to avoid fatal errors due to syntax differences on PHP 5.2.
			require_once FUSION_LIBRARY_PATH . '/inc/recaptcha/class-fusion-recaptcha.php';

			// Instantiate reCAPTCHA object.
			$re_captcha_wrapper = new Fusion_ReCaptcha( $this->re_captcha_private_key );
			$this->re_captcha   = $re_captcha_wrapper->recaptcha;
		}
	}

	/**
	 * Init and set the error message.
	 *
	 * @access private
	 * @since 3.9.2
	 * @param string|false $message The message we want to set.
	 * @return void
	 */
	private function set_error_message( $message = false ) {
		if ( $message ) {
			$this->error_message = $message;
		} else {
			$this->error_message = __( 'Please check if you\'ve filled all the fields with valid information. Thank you.', 'fusion-core' );
			if ( $this->privacy_checkbox ) {
				$this->error_message = __( 'Please check if you\'ve filled all the fields with valid information and that the data privacy terms confirmation box is checked. Thank you.', 'fusion-core' );
			}
		}
	}

	/**
	 * Check to make sure that the name field is not empty.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function process_name() {
		$post_contact_name = ( isset( $_POST['contact_name'] ) ) ? sanitize_text_field( wp_unslash( $_POST['contact_name'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' === $post_contact_name || esc_attr__( 'Name (required)', 'fusion-core' ) === $post_contact_name ) {
			$this->has_error = true;
		} else {
			$this->name = $post_contact_name;
		}
	}

	/**
	 * Subject field is not required.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function process_subject() {
		$post_url      = ( isset( $_POST['url'] ) ) ? sanitize_text_field( wp_unslash( $_POST['url'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$this->subject = ( function_exists( 'stripslashes' ) ) ? stripslashes( $post_url ) : $post_url;
	}

	/**
	 * Check to make sure sure that a valid email address is submitted.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function process_email() {
		$email = ( isset( $_POST['email'] ) ) ? trim( sanitize_email( wp_unslash( $_POST['email'] ) ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

		if ( '' === $email || esc_attr__( 'Email (required)', 'fusion-core' ) === $email ) {
			$this->has_error = true;
		} elseif ( false === filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$this->has_error = true;
		} else {
			$this->email = trim( $email );
		}
	}

	/**
	 * Check to make sure a message was entered.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function process_message() {
		if ( function_exists( 'sanitize_textarea_field' ) ) {
			$message = ( isset( $_POST['msg'] ) ) ? sanitize_textarea_field( wp_unslash( $_POST['msg'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
		} else {
			$message = ( isset( $_POST['msg'] ) ) ? wp_unslash( $_POST['msg'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput
		}
		if ( '' === $message || esc_attr__( 'Message', 'fusion-core' ) === $message ) {
			$this->has_error = true;
		} else {
			$this->message = ( function_exists( 'stripslashes' ) ) ? stripslashes( $message ) : $message;
		}
	}

	/**
	 * Check privacy data checkbox.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function process_data_privacy_confirmation() {
		$data_privacy_confirmation = ( isset( $_POST['data_privacy_confirmation'] ) ) ? sanitize_text_field( wp_unslash( $_POST['data_privacy_confirmation'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

		if ( ! $data_privacy_confirmation ) {
			$this->has_error = true;
		} else {
			$this->data_privacy_confirmation = (int) $data_privacy_confirmation;
		}
	}

	/**
	 * Check reCAPTCHA.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function process_recaptcha() {
		if ( $this->re_captcha ) {
			$re_captcha_response = null;
			// Was there a reCAPTCHA response?
			if ( 'v2' === $this->re_captcha_version ) {
				$post_recaptcha_response = ( isset( $_POST['g-recaptcha-response'] ) ) ? trim( wp_unslash( $_POST['g-recaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
			} else {
				$post_recaptcha_response = ( isset( $_POST['fusion-recaptcha-response'] ) ) ? trim( wp_unslash( $_POST['fusion-recaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification
			}

			$server_remote_addr = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? trim( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

			if ( $post_recaptcha_response && ! empty( $post_recaptcha_response ) ) {
				if ( 'v2' === $this->re_captcha_version ) {
					$re_captcha_response = $this->re_captcha->verify( $post_recaptcha_response, $server_remote_addr );
				} else {
					$site_url            = get_option( 'siteurl' );
					$url_parts           = wp_parse_url( $site_url );
					$site_url            = isset( $url_parts['host'] ) ? $url_parts['host'] : $site_url;
					$re_captcha_response = $this->re_captcha->setExpectedHostname( apply_filters( 'avada_recaptcha_hostname', $site_url ) )->setExpectedAction( 'contact_form' )->setScoreThreshold( $this->re_captcha_score )->verify( $post_recaptcha_response, $server_remote_addr );
				}
			}

			// Check the reCAPTCHA response.
			if ( null === $re_captcha_response || ! $re_captcha_response->isSuccess() ) {
				$this->has_error = true;

				$error_codes = [];
				if ( null !== $re_captcha_response ) {
					$error_codes = $re_captcha_response->getErrorCodes();
				}

				if ( empty( $error_codes ) || in_array( 'score-threshold-not-met', $error_codes, true ) ) {
					$this->error_message = __( 'Sorry, reCAPTCHA could not verify that you are a human. Please try again.', 'fusion-core' );
				} else {
					$this->error_message = __( 'reCAPTCHA configuration error. Please check the Global Options settings and your reCAPTCHA account settings.', 'fusion-core' );
				}
			}
		}
	}

	/**
	 * Send the email.
	 *
	 * @access private
	 * @since 3.9.2
	 * @return void
	 */
	private function send_email() {
		$name                      = esc_html( $this->name );
		$email                     = sanitize_email( $this->email );
		$subject                   = wp_filter_kses( $this->subject );
		$message                   = wp_filter_kses( $this->message );
		$data_privacy_confirmation = ( $this->data_privacy_confirmation ) ? esc_html__( 'confirmed', 'fusion-core' ) : '';

		if ( function_exists( 'stripslashes' ) ) {
			$subject = stripslashes( $subject );
			$message = stripslashes( $message );
		}

		$message = html_entity_decode( $message );

		/* translators: The name. */
		$body = sprintf( esc_attr__( 'Name: %s', 'fusion-core' ), " $name \n\n" );
		/* translators: The email. */
		$body .= sprintf( esc_attr__( 'Email: %s', 'fusion-core' ), " $email \n\n" );
		/* translators: The subject. */
		$body .= sprintf( esc_attr__( 'Subject: %s', 'fusion-core' ), " $subject \n\n" );
		/* translators: The comments. */
		$body .= sprintf( esc_attr__( 'Message: %s', 'fusion-core' ), "\n$message \n\n" );

		if ( $this->privacy_checkbox ) {
			/* translators: The data privacy terms. */
			$body .= sprintf( esc_attr__( 'Data Privacy Terms: %s', 'fusion-core' ), " $data_privacy_confirmation" );
		}

		$headers = 'Reply-To: ' . $name . ' <' . $email . '>' . "\r\n";

		wp_mail( $this->email_address, $subject, $body, $headers );
		$this->email_sent = true;

		if ( $this->email_sent ) {
			$_POST['contact_name']              = '';
			$_POST['email']                     = '';
			$_POST['url']                       = '';
			$_POST['msg']                       = '';
			$_POST['data_privacy_confirmation'] = 0;

			$this->name                      = '';
			$this->email                     = '';
			$this->subject                   = '';
			$this->message                   = '';
			$this->data_privacy_confirmation = 0;
		}
	}

	/**
	 * Output the contact form recaptcha script.
	 *
	 * @access public
	 * @since 3.9.2
	 * @return void
	 */
	public function get_recaptcha_script() {
		?>
		<script type="text/javascript">
			var fusionOnloadCallback = function() {
				grecaptcha.ready( function() {
					var renderId = grecaptcha.render( 'recaptcha-container', {
						'sitekey': '<?php echo esc_html( $this->re_captcha_public_key ); ?>',
						'badge': '<?php echo esc_html( $this->re_captcha_badge_position ); ?>',
						'size': 'invisible'
					} );

					grecaptcha.execute( renderId, { action: 'contact_form' } ).then( function( token ) {
						jQuery( '.fusion-contact-form' ).find( '#fusion-recaptcha-response' ).val( token );
					} );
				} );
			};
		</script>
		<?php

		if ( $this->re_captcha_public_key && $this->re_captcha_private_key && ! function_exists( 'recaptcha_get_html' ) && ! class_exists( 'ReCaptcha' ) ) {
			$recaptcha_script_uri = 'https://www.google.com/recaptcha/api.js?render=explicit&hl=' . get_locale() . '&onload=fusionOnloadCallback';
			if ( 'v2' === $this->re_captcha_version ) {
				$recaptcha_script_uri = 'https://www.google.com/recaptcha/api.js?hl=' . get_locale();
			}
			wp_enqueue_script( 'recaptcha-api', $recaptcha_script_uri, [], FUSION_CORE_VERSION, false );
		}
	}

	/**
	 * Output the form error messages.
	 *
	 * @access public
	 * @since 3.9.2
	 * @return void
	 */
	public function get_error_messages() {
		?>
		<?php if ( current_user_can( 'edit_theme_options' ) ) : ?>
			<?php if ( ! $this->email_address ) : // Email address not set. ?>
				<?php if ( shortcode_exists( 'fusion_alert' ) ) : ?>
					<?php echo do_shortcode( '[fusion_alert type="error"]' . esc_html__( 'Form email address is not set in Global Options. Please fill in a valid address to make contact form work.', 'fusion-core' ) . '[/fusion_alert]' ); ?>
				<?php else : ?>
					<h3 style="color:#b94a48;">
						<?php esc_html_e( 'Form email address is not set in Global Options. Please fill in a valid address to make contact form work.', 'fusion-core' ); ?>
					</h3>
				<?php endif; ?>
			<?php endif; ?>
			<br />
		<?php endif; ?>

		<?php if ( $this->has_error ) : // If errors are found. ?>
			<?php if ( shortcode_exists( 'fusion_alert' ) ) : ?>
				<?php echo do_shortcode( '[fusion_alert type="error"]' . esc_html( $this->error_message ) . '[/fusion_alert]' ); ?>
			<?php else : ?>
				<h3 style="color:#b94a48;">
					<?php echo esc_html( $this->error_message ); ?>
				</h3>
			<?php endif; ?>
			<br />
		<?php endif; ?>

		<?php if ( $this->email_sent && $this->email_address ) : // If email is sent. ?>
			<?php if ( shortcode_exists( 'fusion_alert' ) ) : ?>
				<?php
				$success_message = sprintf(
					/* translators: The name from the contact form. */
					esc_html__( 'Thank you %s for using our contact form! Your email was successfully sent!', 'fusion-core' ),
					'<strong>' . esc_html( $this->name ) . '</strong>'
				);
				echo do_shortcode( '[fusion_alert type="success"]' . $success_message . '[/fusion_alert]' );
				?>
			<?php else : ?>
				<h3 style="color:#468847;">
					<?php
					printf(
						/* translators: The name from the contact form. */
						esc_html__( 'Thank you %s for using our contact form! Your email was successfully sent!', 'fusion-core' ),
						'<strong>' . esc_html( $this->name ) . '</strong>'
					);
					?>
				</h3>
			<?php endif; ?>
			<br />
		<?php endif; ?>
		<?php
	}

	/**
	 * Output the contact form.
	 *
	 * @access public
	 * @since 3.9.2
	 * @return void
	 */
	public function get_contact_form() {
		?>
		<form action="" method="post" class="fusion-contact-form">
			<?php if ( 'above' === $this->comment_position ) : ?>
				<div id="comment-textarea">
					<textarea name="msg" id="comment" cols="39" rows="4" tabindex="4" class="textarea-comment" placeholder="<?php esc_attr_e( 'Message', 'fusion-core' ); ?>" aria-label="<?php esc_attr_e( 'Message', 'fusion-core' ); ?>"><?php echo esc_textarea( $this->message ); ?></textarea>
				</div>
			<?php endif; ?>

			<div id="comment-input">
				<input type="text" name="contact_name" id="author" value="<?php echo esc_attr( $this->name ); ?>" placeholder="<?php esc_attr_e( 'Name (required)', 'fusion-core' ); ?>" size="22" required aria-required="true" aria-label="<?php esc_attr_e( 'Name (required)', 'fusion-core' ); ?>" class="input-name">
				<input type="email" name="email" id="email" value="<?php echo esc_attr( $this->email ); ?>" placeholder="<?php esc_attr_e( 'Email (required)', 'fusion-core' ); ?>" size="22" required aria-required="true" aria-label="<?php esc_attr_e( 'Email (required)', 'fusion-core' ); ?>" class="input-email">
				<input type="text" name="url" id="url" value="<?php echo esc_attr( $this->subject ); ?>" placeholder="<?php esc_attr_e( 'Subject', 'fusion-core' ); ?>" aria-label="<?php esc_attr_e( 'Subject', 'fusion-core' ); ?>" size="22" class="input-website">
			</div>

			<?php if ( 'above' !== $this->comment_position ) : ?>
				<div id="comment-textarea" class="fusion-contact-comment-below">
					<textarea name="msg" id="comment" cols="39" rows="4" class="textarea-comment" placeholder="<?php esc_attr_e( 'Message', 'fusion-core' ); ?>" aria-label="<?php esc_attr_e( 'Message', 'fusion-core' ); ?>"><?php echo esc_textarea( $this->message ); ?></textarea>
				</div>
			<?php endif; ?>

			<?php if ( $this->privacy_checkbox ) : ?>
				<div id="comment-privacy-checkbox-wrapper" class="fusion-comment-privacy-checkbox-wrapper">
					<input type="checkbox" value="1" <?php checked( $this->data_privacy_confirmation, 1 ); ?> required aria-required="true" id="data-privacy-confirmation" name="data_privacy_confirmation" class="fusion-comment-privacy-checkbox" />
					<label for="data-privacy-confirmation"><?php echo $this->privacy_label; // phpcs:ignore WordPress.Security.EscapeOutput ?></label>
				</div>
			<?php endif; ?>

			<?php if ( $this->re_captcha_public_key && $this->re_captcha_private_key ) : ?>
				<div id="comment-recaptcha">
					<?php if ( 'v2' === $this->re_captcha_version ) : ?>
						<div class="g-recaptcha" data-theme="<?php echo esc_attr( $this->recaptcha_color_scheme ); ?>" data-sitekey="<?php echo esc_attr( $this->re_captcha_public_key ); ?>"></div>
					<?php else : ?>
						<?php $hide_badge_class = 'hide' === $this->re_captcha_badge_position ? ' fusion-hide-recaptcha-badge' : ''; ?>
						<div id="recaptcha-container" class="recaptcha-container<?php echo esc_attr( $hide_badge_class ); ?>"></div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div id="comment-submit-container">
				<?php if ( 'v3' === $this->re_captcha_version ) : ?>
					<input type="hidden" name="fusion-recaptcha-response" id="fusion-recaptcha-response" value="">
				<?php endif; ?>

				<input name="submit" type="submit" id="submit" value="<?php esc_html_e( 'Submit Form', 'fusion-core' ); ?>" class="comment-submit fusion-button fusion-button-default fusion-button-default-size">
			</div>
		</form>
		<?php
	}
}
