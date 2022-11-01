<?php
/**
 * Add an element to fusion-builder.
 *
 * @package fusion-builder
 * @since 1.0
 */

if ( fusion_is_element_enabled( 'fusion_login' ) ||
	fusion_is_element_enabled( 'fusion_register' ) ||
	fusion_is_element_enabled( 'fusion_lost_password' ) ) {

	if ( ! class_exists( 'FusionSC_Login' ) ) {
		/**
		 * Shortcode class.
		 *
		 * @since 1.0
		 */
		class FusionSC_Login extends Fusion_Element {

			/**
			 * Element counter, used for CSS.
			 *
			 * @since 1.0
			 * @var int $args
			 */
			private $login_counter = 0;

			/**
			 * Parameters from the shortcode.
			 *
			 * @access protected
			 * @since 1.0
			 * @var array $args
			 */
			protected $args;

			/**
			 * Whether the nonces script has already been added to the footer or not.
			 *
			 * @static
			 * @access private
			 * @since 2.0.0
			 * @var bool
			 */
			private static $nonce_added_to_footer = false;

			/**
			 * The reCAPTCHA class instance
			 *
			 * @access public
			 * @var bool|object
			 */
			public $re_captcha = false;

			/**
			 * Whats the error?
			 *
			 * @access public
			 * @var string
			 */
			public $captcha_error = '';

			/**
			 * ReCapatcha error flag.
			 *
			 * @access public
			 * @var bool
			 */
			public $recaptcha_has_error = false;

			/**
			 * The internal container counter.
			 *
			 * @access private
			 * @since 3.1
			 * @var int
			 */
			public $counter = 0;

			/**
			 * Constructor.
			 *
			 * @access public
			 * @since 1.0
			 */
			public function __construct() {
				parent::__construct();
				add_action( 'lostpassword_post', [ $this, 'lost_password_redirect' ] );
				add_filter( 'login_redirect', [ $this, 'login_redirect' ], 10, 3 );
				add_filter( 'registration_errors', [ $this, 'registration_error_redirect' ], 10, 3 );
				add_action( 'wp_authenticate', [ $this, 'login_auth' ], 10, 2 );

				add_filter( 'fusion_attr_login-shortcode', [ $this, 'attr' ] );
				add_filter( 'fusion_attr_login-shortcode-form', [ $this, 'form_attr' ] );
				add_filter( 'fusion_attr_login-shortcode-button', [ $this, 'button_attr' ] );

				add_shortcode( 'fusion_login', [ $this, 'render_login' ] );
				add_shortcode( 'fusion_register', [ $this, 'render_register' ] );
				add_shortcode( 'fusion_lost_password', [ $this, 'render_lost_password' ] );

				add_action( 'wp_ajax_fusion_login_nonce', [ $this, 'ajax_get_login_nonce_field' ] );
				add_action( 'wp_ajax_nopriv_fusion_login_nonce', [ $this, 'ajax_get_login_nonce_field' ] );
				add_action( 'wp_footer', [ $this, 'print_login_nonce_script' ], 99 );
			}


			/**
			 * Add default values to shortcode parameters.
			 *
			 * @since 1.0
			 *
			 * @param  array  $args      Shortcode paramters.
			 * @param  string $shortcode Shortcode name.
			 * @return array             Shortcode paramters with default values where necesarry.
			 */
			public static function get_element_defaults( $args = '', $shortcode = false ) {
				$fusion_settings = awb_get_fusion_settings();

				$defaults = FusionBuilder::set_shortcode_defaults(
					[
						'margin_top'            => '',
						'margin_right'          => '',
						'margin_bottom'         => '',
						'margin_left'           => '',
						'hide_on_mobile'        => fusion_builder_default_visibility( 'string' ),
						'class'                 => '',
						'id'                    => '',
						'button_fullwidth'      => $fusion_settings->get( 'button_span' ),
						'caption'               => '',
						'caption_color'         => '',
						'form_field_layout'     => $fusion_settings->get( 'user_login_form_field_layout' ),
						'form_background_color' => $fusion_settings->get( 'user_login_form_background_color' ),
						'heading'               => '',
						'heading_color'         => '',
						'link_color'            => '',
						'lost_password_link'    => '',
						'redirection_link'      => '',
						'register_link'         => '',
						'register_note'         => '',
						'show_labels'           => $fusion_settings->get( 'user_login_form_show_labels' ),
						'show_placeholders'     => $fusion_settings->get( 'user_login_form_show_placeholders' ),
						'show_remember_me'      => $fusion_settings->get( 'user_login_form_show_remember_me' ),
						'text_align'            => $fusion_settings->get( 'user_login_text_align' ),

						'disable_form'          => '', // Only for demo usage.

					],
					$args,
					$shortcode
				);

				$defaults['main_container'] = ( $defaults['disable_form'] ) ? 'div' : 'form';
				$defaults['label_class']    = ( 'yes' === $defaults['show_labels'] ) ? 'fusion-login-label' : 'fusion-hidden-content';

				return $defaults;
			}

			/**
			 * Maps settings to param variables.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function settings_to_params() {
				return [
					'button_span'                       => 'button_fullwidth',
					'user_login_form_field_layout'      => 'form_field_layout',
					'user_login_form_background_color'  => 'form_background_color',
					'user_login_form_show_labels'       => 'show_labels',
					'user_login_form_show_placeholders' => 'show_placeholders',
					'user_login_form_show_remember_me'  => 'show_remember_me',
					'user_login_text_align'             => 'text_align',
				];
			}

			/**
			 * Used to set any other variables for use on front-end editor template.
			 *
			 * @static
			 * @access public
			 * @since 2.0.0
			 * @return array
			 */
			public static function get_element_extras() {
				$fusion_settings = awb_get_fusion_settings();
				$extras          = [
					'username_text'     => esc_attr__( 'Username', 'fusion-builder' ),
					'password_text'     => esc_attr__( 'Password', 'fusion-builder' ),
					'login_text'        => esc_attr__( 'Log in', 'fusion-builder' ),
					'rememberme_text'   => esc_html__( 'Remember Me', 'fusion-builder' ),
					'lost_text'         => esc_attr__( 'Lost password?', 'fusion-builder' ),
					'register_text'     => esc_attr__( 'Register', 'fusion-builder' ),
					'lostfull_text'     => esc_attr__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'fusion-builder' ),
					'useroremail_text'  => esc_attr__( 'Username or Email', 'fusion-builder' ),
					'reset_text'        => esc_attr__( 'Reset Password', 'fusion-builder' ),
					'email_text'        => esc_attr__( 'Email', 'fusion-builder' ),
					'registerfull_text' => esc_attr__( 'Registration confirmation will be emailed to you.', 'fusion-builder' ),
				];

				// Added for front-end preview.
				if ( is_user_logged_in() ) {
					$user = get_user_by( 'id', get_current_user_id() );
					/* translators: The user's display-name. */
					$extras['welcome_text']   = sprintf( esc_attr__( 'Welcome %s', 'fusion-builder' ), ucwords( $user->display_name ) );
					$extras['user_avatar']    = get_avatar( $user->ID, apply_filters( 'fusion_login_box_avatar_size', 50 ) );
					$extras['dashboard_text'] = esc_attr__( 'Dashboard', 'fusion-builder' );
					$extras['profile_text']   = esc_attr__( 'Profile', 'fusion-builder' );
					$extras['logout_text']    = esc_attr__( 'Logout', 'fusion-builder' );
				}
				return $extras;
			}

			/**
			 * Render the login shortcode.
			 *
			 * @since 1.0
			 *
			 * @param  array  $args       Shortcode paramters.
			 * @param  string $content    Content between shortcode.
			 * @return string               HTML output.
			 */
			public function render_login( $args, $content = '' ) {
				global $fusion_settings;
				$defaults = $this->get_element_defaults( $args, 'fusion_login' );

				$defaults['action'] = 'login';

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$styles = $this->get_style_tag();

				$html = '<div ' . FusionBuilder::attributes( 'login-shortcode' ) . '>' . $styles;

				if ( ! is_user_logged_in() ) {
					$user_login = ( isset( $_GET['log'] ) ) ? sanitize_text_field( wp_unslash( $_GET['log'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$html .= ( $heading ) ? '<h3 class="fusion-login-heading">' . $heading . '</h3>' : '';
					$html .= ( $caption ) ? '<div class="fusion-login-caption">' . $caption . '</div>' : '';

					$html .= '<' . $main_container . ' ' . FusionBuilder::attributes( 'login-shortcode-form' ) . '>';

					// Get the success/error notices.
					$html .= $this->render_notices( $action );

					$html       .= '<div class="fusion-login-fields">';
					$html       .= '<div class="fusion-login-input-wrapper">';
					$html       .= '<label class="' . $label_class . '" for="user_login-' . $this->login_counter . '">' . esc_html__( 'Username or Email', 'fusion-builder' ) . '</label>';
					$placeholder = ( 'yes' === $show_placeholders ) ? ' placeholder="' . esc_attr__( 'Username or Email', 'fusion-builder' ) . '"' : '';
					$html       .= '<input type="text" name="log"' . $placeholder . ' value="' . esc_attr( $user_login ) . '" size="20" class="fusion-login-username input-text" id="user_login-' . $this->login_counter . '" />';
					$html       .= '</div>';

					$html       .= '<div class="fusion-login-input-wrapper">';
					$html       .= '<label class="' . $label_class . '" for="user_pass-' . $this->login_counter . '">' . esc_html__( 'Password', 'fusion-builder' ) . '</label>';
					$placeholder = ( 'yes' === $show_placeholders ) ? ' placeholder="' . esc_attr__( 'Password', 'fusion-builder' ) . '"' : '';
					$html       .= '<input type="password" name="pwd"' . $placeholder . ' value="" size="20" class="fusion-login-password input-text" id="user_pass-' . $this->login_counter . '" />';
					$html       .= '</div>';

					if ( $fusion_settings->get( 'recaptcha_login_form' ) ) {
						$html .= $this->get_recaptcha_field();
					}
					$html .= '</div>';

					$html .= '<div class="fusion-login-additional-content">';

					$html .= '<div class="fusion-login-submit-wrapper">';
					$html .= '<button ' . FusionBuilder::attributes( 'login-shortcode-button' ) . '>' . esc_html__( 'Log in', 'fusion-builder' ) . '</button>';

					// Set the query string for successful password reset.
					if ( ! $redirection_link ) {
						$redirection_link = $this->get_redirection_link();
					}
					$html .= $this->render_hidden_login_inputs( apply_filters( 'fusion_builder_user_login_redirection_link', $redirection_link ) );

					$html .= '</div>';

					$html .= '<div class="fusion-login-links">';

					if ( 'yes' === $show_remember_me ) {
						$html .= '<label class="fusion-login-remember-me"><input name="rememberme" type="checkbox" id="rememberme" value="forever" />' . esc_html__( 'Remember Me', 'fusion-builder' ) . '</label>';
					}

					if ( '' !== $lost_password_link ) {
						$html .= '<a class="fusion-login-lost-passowrd" target="_self" href="' . $lost_password_link . '">' . esc_html__( 'Lost password?', 'fusion-builder' ) . '</a>';
					}
					if ( '' !== $register_link ) {
						$html .= '<a class="fusion-login-register" target="_self" href="' . $register_link . '">' . esc_html__( 'Register', 'fusion-builder' ) . '</a>';
					}
					$html .= '</div>';
					$html .= '</div>';

					$html .= '</' . $main_container . '>';
				} else {
					$user = get_user_by( 'id', get_current_user_id() );

					/* translators: The user's display-name. */
					$html .= '<div class="fusion-login-caption">' . sprintf( esc_html__( 'Welcome %s', 'fusion-builder' ), ucwords( $user->display_name ) ) . '</div>';
					$html .= '<div class="fusion-login-avatar">' . get_avatar( $user->ID, apply_filters( 'fusion_login_box_avatar_size', 50 ) ) . '</div>';
					$html .= '<ul class="fusion-login-loggedin-links">';
					$html .= '<li><a href="' . get_dashboard_url() . '">' . esc_html__( 'Dashboard', 'fusion-builder' ) . '</a></li>';
					$html .= '<li><a href="' . get_edit_user_link( $user->ID ) . '">' . esc_html__( 'Profile', 'fusion-builder' ) . '</a></li>';
					$html .= '<li><a href="' . wp_logout_url( get_permalink() ) . '">' . esc_html__( 'Logout', 'fusion-builder' ) . '</a></li>';
					$html .= '</ul>';

				}

				$html .= '</div>';
				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_element_user_login_content', $html, $args );
			}

			/**
			 * Render the register shortcode.
			 *
			 * @since 1.8.0
			 *
			 * @param  array  $args       Shortcode paramters.
			 * @param  string $content    Content between shortcode.
			 * @return string               HTML output.
			 */
			public function render_register( $args, $content = '' ) {
				global $fusion_settings;
				// Compatibility fix for versions prior to FB 1.5.2.
				if ( ! isset( $args['register_note'] ) ) {
					$args['register_note'] = esc_attr__( 'Registration confirmation will be emailed to you.', 'fusion-builder' );
				}

				$defaults = $this->get_element_defaults( $args, 'fusion_register' );

				$defaults['action'] = 'register';

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$styles = $this->get_style_tag();

				$html = '';

				if ( ! is_user_logged_in() ) {
					$html .= '<div ' . FusionBuilder::attributes( 'login-shortcode' ) . '>' . $styles;
					$html .= ( $heading ) ? '<h3 class="fusion-login-heading">' . $heading . '</h3>' : '';
					$html .= ( $caption ) ? '<div class="fusion-login-caption">' . $caption . '</div>' : '';

					$html .= '<' . $main_container . ' ' . FusionBuilder::attributes( 'login-shortcode-form' ) . '>';

					// Get the success/error notices.
					$html .= $this->render_notices( $action );

					// Values from failed attempt.
					$username = isset( $_GET['user_login'] ) ? sanitize_text_field( wp_unslash( $_GET['user_login'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$email    = isset( $_GET['user_email'] ) ? sanitize_text_field( wp_unslash( $_GET['user_email'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$html       .= '<div class="fusion-login-fields">';
					$html       .= '<div class="fusion-login-input-wrapper">';
					$html       .= '<label class="' . $label_class . '" for="user_login-' . $this->login_counter . '">' . esc_html__( 'Username', 'fusion-builder' ) . '</label>';
					$placeholder = ( 'yes' === $show_placeholders ) ? ' placeholder="' . esc_attr__( 'Username', 'fusion-builder' ) . '"' : '';
					$html       .= '<input type="text" name="user_login"' . $placeholder . ' value="' . $username . '" size="20" class="fusion-login-username input-text" id="user_login-' . $this->login_counter . '" />';
					$html       .= '</div>';

					$html       .= '<div class="fusion-login-input-wrapper">';
					$html       .= '<label class="' . $label_class . '" for="user_email-' . $this->login_counter . '">' . esc_html__( 'Email', 'fusion-builder' ) . '</label>';
					$placeholder = ( 'yes' === $show_placeholders ) ? ' placeholder="' . esc_attr__( 'Email', 'fusion-builder' ) . '"' : '';
					$html       .= '<input type="email" name="user_email"' . $placeholder . ' value="' . $email . '" size="20" class="fusion-login-email input-text" id="user_email-' . $this->login_counter . '" />';
					$html       .= '</div>';

					if ( $fusion_settings->get( 'recaptcha_login_form' ) ) {
						$html .= $this->get_recaptcha_field();
					}

					// Action for adding your own fields.
					ob_start();
					do_action( 'fusion_register_form', $this->args );
					$html .= ob_get_clean();

					/* Only added as honeypot for spambots. */
					$html .= '<div class="fusion-login-input-wrapper fusion-hidden">';
					$html .= '<label class="fusion-hidden-content" for="confirm_email">Please leave this field empty</label>';
					$html .= '<input class="fusion-hidden-content" type="text" name="confirm_email" id="confirm_email" value="">';
					$html .= '</div>';
					$html .= '</div>';

					$html .= '<div class="fusion-login-additional-content">';
					$html .= ( $register_note ) ? '<p class="fusion-login-registration-confirm fusion-login-input-wrapper">' . htmlspecialchars_decode( $register_note, ENT_HTML5 ) . '</p>' : '';

					$html .= '<div class="fusion-login-submit-wrapper">';
					$html .= '<button ' . FusionBuilder::attributes( 'login-shortcode-button' ) . '>' . esc_html__( 'Register', 'fusion-builder' ) . '</button>';

					// Set the query string for successful password reset.
					if ( ! $redirection_link ) {
						$redirection_link = $this->get_redirection_link();
					}
					$html .= $this->render_hidden_login_inputs(
						apply_filters(
							'fusion_builder_user_register_redirection_link',
							$redirection_link,
							[
								'action'  => 'register',
								'success' => '1',
							]
						)
					);

					$html .= '</div>';
					$html .= '</div>';

					$html .= '</' . $main_container . '>';
					$html .= '</div>';
				} else {
					$html .= do_shortcode( '[fusion_alert type="general"]' . esc_html__( 'You are already signed up.', 'fusion-builder' ) . '[/fusion_alert]' );
				}

				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_element_user_register_content', $html, $args );
			}

			/**
			 * Render the lost password shortcode.
			 *
			 * @since 1.8.0
			 *
			 * @param  array  $args       Shortcode paramters.
			 * @param  string $content    Content between shortcode.
			 * @return string               HTML output.
			 */
			public function render_lost_password( $args, $content = '' ) {
				global $fusion_settings;
				$defaults = $this->get_element_defaults( $args, 'fusion_lost_password' );

				$defaults['action'] = 'lostpassword';

				extract( $defaults );

				$this->args = $defaults;

				$this->args['margin_bottom'] = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_bottom'], 'px' );
				$this->args['margin_left']   = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_left'], 'px' );
				$this->args['margin_right']  = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_right'], 'px' );
				$this->args['margin_top']    = FusionBuilder::validate_shortcode_attr_value( $this->args['margin_top'], 'px' );

				$styles = $this->get_style_tag();

				$html = '';

				if ( ! is_user_logged_in() ) {

					$html .= '<div ' . FusionBuilder::attributes( 'login-shortcode' ) . '>' . $styles;
					$html .= ( $heading ) ? '<h3 class="fusion-login-heading">' . $heading . '</h3>' : '';
					$html .= ( $caption ) ? '<div class="fusion-login-caption">' . $caption . '</div>' : '';

					$html .= '<' . $main_container . ' ' . FusionBuilder::attributes( 'login-shortcode-form' ) . '>';

					// Get the success/error notices.
					$html .= $this->render_notices( $action );

					$html .= '<p class="fusion-login-input-wrapper">' . esc_html__( 'Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.', 'fusion-builder' ) . '</p>';

					$html       .= '<div class="fusion-login-input-wrapper">';
					$html       .= '<label class="' . $label_class . '" for="user_login">' . esc_html__( 'Username or Email', 'fusion-builder' ) . '</label>';
					$placeholder = ( 'yes' === $show_placeholders ) ? ' placeholder="' . esc_attr__( 'Username or Email', 'fusion-builder' ) . '"' : '';
					$html       .= '<input type="text" name="user_login"' . $placeholder . 'value="" size="20" class="fusion-login-username input-text" id="user_login"/>';
					$html       .= '</div>';

					if ( $fusion_settings->get( 'recaptcha_login_form' ) ) {
						$html .= $this->get_recaptcha_field();
					}

					$html .= '<div class="fusion-login-submit-wrapper">';
					$html .= '<button ' . FusionBuilder::attributes( 'login-shortcode-button' ) . '>' . esc_html__( 'Reset Password', 'fusion-builder' ) . '</button>';

					// Set the query string for successful password reset.
					if ( ! $redirection_link ) {
						$redirection_link = $this->get_redirection_link();
					}
					$html .= $this->render_hidden_login_inputs(
						apply_filters(
							'fusion_builder_user_lost_password_redirection_link',
							$redirection_link,
							[
								'action'  => 'lostpassword',
								'success' => '1',
							]
						)
					);

					$html .= '</div>';
					$html .= '</' . $main_container . '>';
					$html .= '</div>';

				} else {
					$html .= do_shortcode( '[fusion_alert type="general"]' . esc_html__( 'You are already signed in.', 'fusion-builder' ) . '[/fusion_alert]' );
				}

				$this->counter++;
				$this->on_render();

				return apply_filters( 'fusion_element_user_lost_password_content', $html, $args );
			}

			/**
			 * Render ReCaptcha html.
			 *
			 * @access public
			 * @since 3.3
			 * @return string
			 */
			public function get_recaptcha_field() {
				$fusion_settings = awb_get_fusion_settings();

				ob_start();
				$badge_position = $fusion_settings->get( 'recaptcha_badge_position' );
				?>
				<?php if ( 'inline' === $badge_position ) { ?>
					<div class="fusion-login-input-wrapper">
				<?php } ?>
				<?php if ( $fusion_settings->get( 'recaptcha_public' ) && $fusion_settings->get( 'recaptcha_private' ) ) : ?>
					<?php if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) : ?>
						<div
							id="g-recaptcha-<?php echo esc_attr( $this->counter ); ?>"
							class="fusion-form-recaptcha-v2"
							data-theme="<?php echo esc_attr( $fusion_settings->get( 'recaptcha_color_scheme' ) ); ?>"
							data-sitekey="<?php echo esc_attr( $fusion_settings->get( 'recaptcha_public' ) ); ?>"
							data-tabindex="">
						</div>
					<?php else : ?>
						<?php $hide_badge_class = 'hide' === $badge_position ? ' fusion-form-hide-recaptcha-badge' : ''; ?>
						<div
							id="g-recaptcha-<?php echo esc_attr( $this->counter ); ?>"
							class="fusion-form-recaptcha-v3 recaptcha-container <?php echo esc_attr( $hide_badge_class ); ?>"
							data-sitekey="<?php echo esc_attr( $fusion_settings->get( 'recaptcha_public' ) ); ?>"
							data-badge="<?php echo esc_attr( $badge_position ); ?>">
						</div>
						<input
							type="hidden"
							name="fusion-form-recaptcha-response"
							class="g-recaptcha-response"
							id="fusion-form-recaptcha-response-<?php echo esc_attr( $this->counter ); ?>"
							value="">
					<?php endif; ?>
				<?php elseif ( is_user_logged_in() && current_user_can( 'manage_options' ) ) : ?>
						<div class="fusion-builder-placeholder"><?php echo esc_html__( 'reCAPTCHA configuration error. Please check the Global Options settings and your reCAPTCHA account settings.', 'fusion-builder' ); ?></div>
				<?php endif; ?>
				<?php if ( 'inline' === $badge_position ) { ?>
					<div>
				<?php } ?>
				<?php
				$recaptcha_content = ob_get_clean();

				return $recaptcha_content;
			}

			/**
			 * Render the needed hidden login inputs.
			 *
			 * @access public
			 * @since 1.0
			 * @param  string $redirection_link A redirection link.
			 * @param  array  $query_args       The query arguments.
			 * @return string
			 */
			public function render_hidden_login_inputs( $redirection_link = '', $query_args = [] ) {
				$html = '';
				if ( ! $this->args['disable_form'] ) {

					$html .= '<input type="hidden" name="user-cookie" value="1" />';

					// If no redirection link is given, get ones.
					if ( empty( $redirection_link ) ) {
						$redirection_link = wp_get_referer();
						if ( isset( $_SERVER['REQUEST_URI'] ) && isset( $_SERVER['HTTP_HOST'] ) ) {
							$redirection_link = ( is_ssl() ? 'https://' : 'http://' ) . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) . esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );
						}

						// Redirection and source input.
						$redirection_link = remove_query_arg( 'loggedout', $redirection_link );
					}

					if ( ! empty( $query_args ) ) {
						$redirection_link = add_query_arg( $query_args, $redirection_link );
					}

					$html .= '<input type="hidden" name="redirect_to" value="' . esc_url( $redirection_link ) . '" />';
					$html .= '<input type="hidden" name="fusion_login_box" value="true" />';
					$html .= wp_referer_field( false );

				}
				// Prevent hijacking of the form.
				$html .= '<span class="fusion-login-nonce" style="display:none;"></span>';

				return $html;

			}

			/**
			 * Generates nonce field, used in AJAX request.
			 *
			 * @access public
			 */
			public function ajax_get_login_nonce_field() {
				wp_nonce_field( 'fusion-login', '_wpnonce', false, true );
				wp_die();
			}

			/**
			 * Prints nonce AJAX script.
			 *
			 * @access public
			 */
			public function print_login_nonce_script() {

				// If we've already added the script to the footer
				// there's no need to proceed any further.
				if ( self::$nonce_added_to_footer ) {
					return;
				}
				global $fusion_settings;
				// Set self::$nonce_added_to_footer to true to avoid adding it multiple times.
				self::$nonce_added_to_footer = true;
				?>
				<script type="text/javascript">
				jQuery( document ).ready( function() {
					var ajaxurl = '<?php echo esc_url_raw( admin_url( 'admin-ajax.php' ) ); ?>';
					if ( 0 < jQuery( '.fusion-login-nonce' ).length ) {
						jQuery.get( ajaxurl, { 'action': 'fusion_login_nonce' }, function( response ) {
							jQuery( '.fusion-login-nonce' ).html( response );
						});
					}
				});
				<?php if ( $fusion_settings->get( 'recaptcha_login_form' ) ) : ?>
					<?php if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) { ?>
				jQuery( window ).on( 'load', function() {
					var reCaptchaID;
					jQuery.each( jQuery( '.fusion-form-recaptcha-v2' ), function( index, reCaptcha ) { // eslint-disable-line no-unused-vars
						reCaptchaID = jQuery( this ).attr( 'id' );

						grecaptcha.render( reCaptchaID, {
							sitekey: jQuery( this ).data( 'sitekey' ),
							type: jQuery( this ).data( 'type' ),
							theme: jQuery( this ).data( 'theme' )
						} );
					} );
				});
				<?php } else { ?>

				var active_captcha = [];
				var fusionOnloadCallback = function () {
					grecaptcha.ready( function () {
						jQuery( '.g-recaptcha-response' ).each( function () {
							var el = jQuery( this );
							var container_div = jQuery( el ).parent().find( 'div.recaptcha-container' );
							var id = container_div.attr( 'id' );

							if( 0 === container_div.length || 'undefined' !== typeof active_captcha[id] ||
							( 1 === jQuery( '.fusion-modal' ).find( container_div ).length && jQuery( container_div ).parents( '.fusion-modal' ).is( ':hidden' ) ) ) {
								return;
							}

							var renderId = grecaptcha.render(
									id,
									{
										sitekey: container_div.data( 'sitekey' ),
										badge: container_div.data( 'badge' ),
										size: 'invisible'
									}
							);
							active_captcha[ id ] = renderId;

							grecaptcha.execute( renderId, { action: 'contact_form' } ).then( function ( token ) {
								jQuery( el ).val( token );
							});
						});
					});
				};
				<?php } ?>
				<?php endif; ?>
				</script>
				<?php
			}

			/**
			 * Deals with the different requests.
			 *
			 * @since 1.8.0
			 */
			public function login_init() {
				check_admin_referer( 'fusion-login' );
				$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : 'login';

				$action = 'reauth';
				if ( isset( $_POST['wp-submit'] ) ) {
					$action = 'post-data';
				}

				$redirect_link = $this->get_redirection_link();

				// Redirect to change password form.
				if ( 'resetpass' === $action ) {
					wp_safe_redirect( add_query_arg( [ 'action' => 'resetpass' ], $redirect_link ) );
					exit;
				}

				if (
					'post-data' === $action || // Don't mess with POST requests.
					'reauth' === $action || // Need to reauthorize.
					'logout' === $action       // User is logging out.
				) {
					return;
				}

				wp_safe_redirect( $redirect_link );
				exit;
			}

			/**
			 * Constructs a redirection link, either from the $redirect_to variable or from the referer.
			 *
			 * @access public
			 * @since 1.0
			 * @param bool $error Whether we have an error or not.
			 * @return string The redirection link.
			 */
			public function get_redirection_link( $error = false ) {
				$redirection_link = '';
				$referer          = fusion_get_referer();

				if ( $error && $referer ) {
					$redirection_link = $referer;
				} elseif ( isset( $_REQUEST['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					$redirection_link = esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} elseif ( $referer ) {
					$referer_array = wp_parse_url( $referer );
					$referer       = $referer_array['scheme'] . '://' . $referer_array['host'] . $referer_array['path'];

					// If there's a valid referrer, and it's not the default log-in screen.
					if ( ! empty( $referer ) && ! strstr( $referer, 'wp-login' ) && ! strstr( $referer, 'wp-admin' ) ) {
						$redirection_link = $referer;
					}
				}

				return $redirection_link;
			}

			/**
			 * Redirects after the login, both on success and error.
			 *
			 * @since 1.8.0
			 *
			 * @param string           $redirect_to           The redirect destination URL.
			 * @param string           $requested_redirect_to The requested redirect destination URL passed as a parameter.
			 * @param WP_User|WP_Error $user        WP_User object if login was successful, WP_Error object otherwise.
			 * @return string The redirection link.
			 */
			public function login_redirect( $redirect_to, $requested_redirect_to, $user ) {

				// Make sure we come from the login box.
				if ( isset( $_POST['fusion_login_box'] ) ) {

					check_admin_referer( 'fusion-login' );
					// If we have no errors, remove the action query arg.
					if ( ! isset( $user->errors ) ) {
						return $redirect_to;
					}

					$redirection_link = $this->get_redirection_link( true );
					$redirection_link = add_query_arg(
						[
							'action'    => 'login',
							'success'   => '0',
							'recaptcha' => $this->recaptcha_has_error,
						],
						$redirection_link
					);

					// Fill it login.
					if ( isset( $_POST['log'] ) ) {
						$redirection_link = add_query_arg( [ 'log' => sanitize_text_field( wp_unslash( $_POST['log'] ) ) ], $redirection_link );
					}

					// Redirect to the page with the login box with error code.
					wp_safe_redirect( $redirection_link );
					exit;
				}
				return $redirect_to;
			}

			/**
			 * Redirects after the login, both on success and error.
			 *
			 * @since 1.8.0
			 *
			 * @param WP_Error $errors              A WP_Error object containing any errors encountered during registration.
			 * @param string   $sanitized_user_login  User's username after it has been sanitized.
			 * @param string   $user_email            User's email.
			 * @return void|WP_Error                Error object.
			 */
			public function registration_error_redirect( $errors, $sanitized_user_login, $user_email ) {
				global $fusion_settings;

				// Make sure we come from the login box.
				if ( isset( $_POST['fusion_login_box'] ) ) {
					check_admin_referer( 'fusion-login' );
					$redirection_link = $this->get_redirection_link();
					if ( $fusion_settings->get( 'recaptcha_login_form' ) ) {
						$this->process_recaptcha();
					}

					// Redirect spammers directly to success page.
					if ( ! isset( $_POST['confirm_email'] ) || '' !== $_POST['confirm_email'] && apply_filters( 'fusion_registration_honeypot', true ) ) {
						wp_safe_redirect(
							add_query_arg(
								[
									'action'  => 'register',
									'success' => '1',
								],
								$redirection_link
							)
						);
						exit;
					}

					// Error - prepare query strings for front end notice output.
					if ( ! empty( $errors->errors ) || $this->recaptcha_has_error ) {
						$redirection_link = $this->get_redirection_link( true );
						$redirection_link = add_query_arg(
							[
								'action'  => 'register',
								'success' => '0',
							],
							$redirection_link
						);

						// Empty username.
						if ( isset( $errors->errors['empty_username'] ) ) {
							$redirection_link = add_query_arg( [ 'empty_username' => '1' ], $redirection_link );
						}
						// Empty email.
						if ( isset( $errors->errors['empty_email'] ) ) {
							$redirection_link = add_query_arg( [ 'empty_email' => '1' ], $redirection_link );
						}
						// Username exists.
						if ( isset( $errors->errors['username_exists'] ) ) {
							$redirection_link = add_query_arg( [ 'username_exists' => '1' ], $redirection_link );
						}
						// Email exists.
						if ( isset( $errors->errors['email_exists'] ) ) {
							$redirection_link = add_query_arg( [ 'email_exists' => '1' ], $redirection_link );
						}

						// ReCAPTCHA fails.
						if ( $this->recaptcha_has_error ) {
							$redirection_link = add_query_arg( [ 'recaptcha' => '1' ], $redirection_link );
						}

						if ( isset( $_POST['user_login'] ) ) {
							$redirection_link = add_query_arg( [ 'user_login' => sanitize_text_field( wp_unslash( $_POST['user_login'] ) ) ], $redirection_link );
						}
						if ( isset( $_POST['user_email'] ) ) {
							$redirection_link = add_query_arg( [ 'user_email' => sanitize_text_field( wp_unslash( $_POST['user_email'] ) ) ], $redirection_link );
						}

						$redirection_link = apply_filters( 'fusion_registration_error_redirect', $redirection_link, $errors );
						wp_safe_redirect( $redirection_link );
						exit;
					}
				}

				return $errors;
			}

			/**
			 * Redirects on lost password submission error..
			 *
			 * @since 1.8.0
			 *
			 * @return void
			 */
			public function lost_password_redirect() {
				global $fusion_settings;

				// Make sure we come from the login box.
				if ( isset( $_POST['fusion_login_box'] ) && isset( $_POST['user_login'] ) ) {
					if ( $fusion_settings->get( 'recaptcha_login_form' ) ) {
						$this->process_recaptcha();
					}

					check_admin_referer( 'fusion-login' );
					$redirection_link = add_query_arg(
						[
							'action'  => 'lostpassword',
							'success' => '0',
						],
						$this->get_redirection_link( true )
					);
					$user_data        = '';

					$user_login = sanitize_text_field( wp_unslash( $_POST['user_login'] ) );
					$user_login = trim( $user_login );

					// Error - empty input.
					if ( empty( $user_login ) ) {
						$redirection_link = add_query_arg( [ 'empty_login' => '1' ], $redirection_link );
						// Check email.
					} elseif ( $this->recaptcha_has_error ) {
						$redirection_link = add_query_arg( [ 'recaptcha' => '1' ], $redirection_link );
					} elseif ( is_email( $user_login ) ) {
						$user_data = get_user_by( 'email', $user_login );
						// Error - invalid email.
						if ( empty( $user_data ) ) {
							$redirection_link = add_query_arg( [ 'unregistered_mail' => '1' ], $redirection_link );
						}
					} else {
						// Check username.
						$login     = $user_login;
						$user_data = get_user_by( 'login', $login );

						// Error - invalid username.
						if ( empty( $user_data ) ) {
							$redirection_link = add_query_arg( [ 'unregistered_user' => '1' ], $redirection_link );
						}
					}

					// Redirect on error.
					if ( empty( $user_data ) ) {
						wp_safe_redirect( $redirection_link );
						exit;
					}
				}
			}

			/**
			 * The wp_authenticate hook. Prevent creating user session if captcha is failed.
			 *
			 * @param string $login     The login name.
			 * @param string $password  The password.
			 * @return void
			 */
			public function login_auth( $login, $password ) {
				global $fusion_settings;
				if ( ! isset( $_POST['fusion_login_box'] ) || ! $fusion_settings->get( 'recaptcha_login_form' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
					return;
				}

				$this->process_recaptcha();
				if ( $this->recaptcha_has_error ) {
					add_action( 'authenticate', '__return_false', 30, 2 );
				}
			}

			/**
			 * Checks if recaptcha is enabled for the element
			 *
			 * @since 3.3
			 *
			 * @param boolean|string $shortcode  The shortcode.
			 * @param boolean|string $param_name The param name.
			 * @return boolean
			 */
			public function is_captcha_enabled( $shortcode = false, $param_name = false ) {
				if ( ! $shortcode || ! $param_name ) {
					return false;
				}

				$referer = wp_get_referer();
				if ( empty( $referer ) ) {
					return false;
				}

				$id = url_to_postid( $referer );
				if ( empty( $id ) ) {
					return false;
				}

				$post = get_post( $id );

				if ( ! ( $post instanceof WP_Post ) ) {
					return false;
				}

				preg_match( '/\[' . $shortcode . '.*' . $param_name . '="([^"]+)"/im', $post->post_content, $res );

				if ( ! isset( $res[1] ) || 'yes' !== $res[1] ) {
					return false;
				}

				return true;
			}

			/**
			 * Check reCAPTCHA.
			 *
			 * @since 3.3
			 * @access private
			 * @return void
			 */
			private function process_recaptcha() {
				$fusion_settings = awb_get_fusion_settings();

				$re_captcha_wrapper = new Fusion_ReCaptcha( $fusion_settings->get( 'recaptcha_private' ) );
				$this->re_captcha   = $re_captcha_wrapper->recaptcha;

				if ( $this->re_captcha ) {
					$re_captcha_response = null;
					// Was there a reCAPTCHA response?
					$post_recaptcha_response = ( isset( $_POST['g-recaptcha-response'] ) ) ? trim( wp_unslash( $_POST['g-recaptcha-response'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

					$server_remote_addr = ( isset( $_SERVER['REMOTE_ADDR'] ) ) ? trim( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput, WordPress.Security.NonceVerification

					if ( $post_recaptcha_response && ! empty( $post_recaptcha_response ) ) {
						if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) {
							$re_captcha_response = $this->re_captcha->verify( $post_recaptcha_response, $server_remote_addr );
						} else {
							$site_url            = get_option( 'siteurl' );
							$url_parts           = wp_parse_url( $site_url );
							$site_url            = isset( $url_parts['host'] ) ? $url_parts['host'] : $site_url;
							$re_captcha_response = $this->re_captcha->setExpectedHostname( apply_filters( 'avada_recaptcha_hostname', $site_url ) )->setExpectedAction( 'contact_form' )->setScoreThreshold( $fusion_settings->get( 'recaptcha_score' ) )->verify( $post_recaptcha_response, $server_remote_addr );
						}
					} else {
						$this->recaptcha_has_error = true;
					}

					// Check the reCAPTCHA response.
					if ( null === $re_captcha_response || ! $re_captcha_response->isSuccess() ) {
						$this->recaptcha_has_error = true;
					}
				}
			}

			/**
			 * Renders the response messages after form submission.
			 *
			 * @since 1.8.0
			 *
			 * @param string $context The context of the calling form.
			 * @return string
			 */
			public function render_notices( $context = '' ) {

				// Make sure we have some query string returned; if not we had a successful login.
				if ( isset( $_GET['action'] ) && $_GET['action'] === $context ) { // phpcs:ignore WordPress.Security.NonceVerification
					$notice_array = [
						'login'        => [
							'error' => esc_html__( 'Login failed, please try again.', 'fusion-builder' ),
						],
						'register'     => [
							'success'         => esc_html__( 'Registration complete. Please check your email.', 'fusion-builder' ),
							'empty_username'  => esc_html__( 'Please enter a username.', 'fusion-builder' ),
							'empty_email'     => esc_html__( 'Please type your email address.', 'fusion-builder' ),
							'username_exists' => esc_html__( 'This username is already registered. Please choose another one.', 'fusion-builder' ),
							'email_exists'    => esc_html__( 'This email is already registered, please choose another one.', 'fusion-builder' ),
							'generic_error'   => esc_html__( 'Something went wrong during registration. Please try again.', 'fusion-builder' ),
						],
						'lostpassword' => [
							'success'           => esc_html__( 'Check your email for the confirmation link.', 'fusion-builder' ),
							'empty_login'       => esc_html__( 'Enter a username or email address.', 'fusion-builder' ),
							'unregistered_user' => esc_html__( 'Invalid username.', 'fusion-builder' ),
							'unregistered_mail' => esc_html__( 'There is no user registered with that email address.', 'fusion-builder' ),
							'generic_error'     => esc_html__( 'Invalid username or email.', 'fusion-builder' ),
						],
						'all'          => [
							'captcha_failed' => esc_html__( 'Sorry, ReCaptcha could not verify that you are a human. Please try again.', 'fusion-builder' ),
						],
					];

					$success         = ( isset( $_GET['success'] ) && '1' === $_GET['success'] ) ? true : false;  // phpcs:ignore WordPress.Security.NonceVerification
					$recaptcha_error = ( isset( $_GET['recaptcha'] ) && '1' === $_GET['recaptcha'] ) ? true : false;  // phpcs:ignore WordPress.Security.NonceVerification
					$notice_array    = apply_filters( 'fusion_user_login_notices_array', $notice_array, sanitize_text_field( wp_unslash( $_GET['action'] ) ), $success );  // phpcs:ignore WordPress.Security.NonceVerification

					// Login - there is only an error message and it is always the same.
					if ( 'login' === $_GET['action'] && ! $success ) { // phpcs:ignore WordPress.Security.NonceVerification
						if ( $recaptcha_error ) {
							$notice_type = 'error';
							$notices     = $notice_array['all']['captcha_failed'];
						} else {
							$notice_type = 'error';
							$notices     = $notice_array['login']['error'];
						}
						// Registration.
					} elseif ( 'register' === $_GET['action'] ) {  // phpcs:ignore WordPress.Security.NonceVerification
						// Success.
						if ( $success ) {
							$notice_type = 'success';
							$notices     = $notice_array['register']['success'];
							// Error.
						} else {
							$notice_type = 'error';
							$notices     = '';

							foreach ( $notice_array['register'] as $key => $message ) {
								if ( isset( $_GET[ $key ] ) && $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
									$notices .= $message . '<br />';
								}
							}

							if ( $recaptcha_error ) {
								$notices .= $notice_array['all']['captcha_failed'] . '<br />';
							}

							// Generic Error.
							if ( '' === $notices ) {
								$notices .= $notice_array['register']['generic_error'];
								// Delete the last line break.
							} else {
								$notices = substr( $notices, 0, strlen( $notices ) - 6 );
							}
						}
					} elseif ( 'lostpassword' === $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification
						// Lost password.
						if ( $success ) {
							// Success.
							$notice_type = 'success';
							$notices     = $notice_array['lostpassword']['success'];
						} else {
							// Error.
							$notice_type = 'error';
							$notices     = '';

							foreach ( $notice_array['lostpassword'] as $key => $message ) {
								if ( isset( $_GET[ $key ] ) && $_GET[ $key ] ) { // phpcs:ignore WordPress.Security.NonceVerification, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
									$notices .= $message . '<br />';
								}
							}

							if ( $recaptcha_error ) {
								$notices .= $notice_array['all']['captcha_failed'] . '<br />';
							}

							// Generic Error.
							if ( '' === $notices ) {
								$notices .= $notice_array['lostpassword']['generic_error'];
							} else {
								// Delete the last line break.
								$notices = substr( $notices, 0, strlen( $notices ) - 6 );
							}
						}
					}

					return do_shortcode( '[fusion_alert type="' . $notice_type . '"]' . $notices . '[/fusion_alert]' );
				}
				return '';
			}

			/**
			 * Constructs the scoped style tag for the login box.
			 *
			 * @since 1.8.0
			 *
			 * @return string The scoped styles.
			 */
			public function get_style_tag() {
				$this->login_counter++;

				$styles = '';

				if ( $this->args['heading_color'] ) {
					$styles .= '.fusion-login-box-' . $this->login_counter . ' .fusion-login-heading{color:' . $this->args['heading_color'] . ';}';
				}

				if ( $this->args['caption_color'] ) {
					$styles .= '.fusion-login-box-' . $this->login_counter . ' .fusion-login-caption{color:' . $this->args['caption_color'] . ';}';
				}

				if ( $this->args['link_color'] ) {
					$styles .= '.fusion-login-box-' . $this->login_counter . ' a{color:' . $this->args['link_color'] . ';}';
				}

				if ( $styles ) {
					$styles = '<style type="text/css">' . $styles . '</style>';
				}

				return $styles;
			}

			/**
			 * Builds the attributes array.
			 *
			 * @access public
			 * @since 1.0
			 * @return array
			 */
			public function attr() {

				$attr = fusion_builder_visibility_atts(
					$this->args['hide_on_mobile'],
					[
						'class' => 'fusion-login-box fusion-login-box-' . $this->login_counter . ' fusion-login-box-' . $this->args['action'] . ' fusion-login-align-' . $this->args['text_align'] . ' fusion-login-field-layout-' . $this->args['form_field_layout'],
						'style' => '',
					]
				);

				$attr['style'] .= Fusion_Builder_Margin_Helper::get_margins_style( $this->args );

				if ( $this->args['class'] ) {
					$attr['class'] .= ' ' . $this->args['class'];
				}

				if ( $this->args['id'] ) {
					$attr['id'] = $this->args['id'];
				}

				return $attr;

			}

			/**
			 * Attributes function for the form container.
			 *
			 * @since 1.0
			 *
			 * @return array The attributes.
			 */
			public function form_attr() {

				$attr = [
					'class' => 'fusion-login-form',
				];

				if ( $this->args['form_background_color'] ) {
					$attr['style'] = 'background-color:' . $this->args['form_background_color'] . ';';

					if ( Fusion_Color::new_color( $this->args['form_background_color'] )->is_color_transparent() ) {
						$attr['style'] .= 'padding:0;';
					}
				}

				if ( $this->args['disable_form'] ) {
					return $attr;
				}

				$attr['name']   = $this->args['action'] . 'form';
				$attr['id']     = $this->args['action'] . 'form';
				$attr['method'] = 'post';

				if ( 'login' === $this->args['action'] ) {
					$attr['action'] = site_url( 'wp-login.php', 'login_post' );
				} else {
					$attr['action'] = site_url( add_query_arg( [ 'action' => $this->args['action'] ], 'wp-login.php' ), 'login_post' );
				}

				return $attr;

			}

			/**
			 * Attribues function for the button.
			 *
			 * @since 1.0
			 *
			 * @return array The attributes.
			 */
			public function button_attr() {
				$attr = [
					'class' => 'fusion-login-button fusion-button button-default fusion-button-default-size',
				];

				if ( 'yes' !== $this->args['button_fullwidth'] ) {
					$attr['class'] .= ' fusion-login-button-no-fullwidth';
				}

				$attr['type'] = 'submit';
				$attr['name'] = 'wp-submit';

				return $attr;

			}

			/**
			 * Builds the dynamic styling.
			 *
			 * @access public
			 * @since 1.1
			 * @return array
			 */
			public function add_styling() {
				global $dynamic_css_helpers, $content_media_query;

				$fusion_settings = awb_get_fusion_settings();
				$css             = [];

				$main_elements = apply_filters( 'fusion_builder_element_classes', [ '.fusion-login-box' ], '.fusion-login-box' );

				if ( 'yes' === $fusion_settings->get( 'button_span' ) && class_exists( 'WooCommerce' ) ) {
					$elements = $dynamic_css_helpers->map_selector( $main_elements, '.fusion-login-box-submit' );
					$css['global'][ $dynamic_css_helpers->implode( $elements ) ]['float'] = 'none';
				}

				$elements = [
					'.fusion-login-box.fusion-login-field-layout-floated .fusion-login-fields',
					'.fusion-login-box.fusion-login-field-layout-floated.fusion-login-align-textflow.fusion-login-box-login .fusion-login-additional-content',
					'.fusion-login-box.fusion-login-field-layout-floated.fusion-login-align-textflow.fusion-login-box-register .fusion-login-additional-content',
				];

				$css[ $content_media_query ][ $dynamic_css_helpers->implode( $elements ) ]['display'] = 'block';

				$css[ $content_media_query ]['.fusion-login-box.fusion-login-field-layout-floated .fusion-login-links']['margin'] = '0 -10px';
				$css[ $content_media_query ]['.fusion-login-box.fusion-login-field-layout-floated.fusion-login-align-textflow.fusion-login-box-register .fusion-login-registration-confirm']['margin'] = '0 0 20px 0';
				$css[ $content_media_query ]['.fusion-login-box.fusion-login-field-layout-floated.fusion-login-align-textflow.fusion-login-box-login .fusion-login-submit-wrapper']['margin-bottom']   = '20px';

				return $css;

			}

			/**
			 * Adds settings to element options panel.
			 *
			 * @access public
			 * @since 1.1
			 * @return array $sections User Login settings.
			 */
			public function add_options() {

				return [
					'user_login_shortcode_section' => [
						'label'       => esc_html__( 'User Login', 'fusion-builder' ),
						'id'          => 'user_login_shortcode_section',
						'description' => '',
						'type'        => 'accordion',
						'icon'        => 'fusiona-calendar-check-o',
						'fields'      => [
							'user_login_text_align'        => [
								'label'       => esc_html__( 'User Login Text Align', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the alignment of all user login content. "Text Flow" follows the default text align of the site. "Center" will center all elements.', 'fusion-builder' ),
								'id'          => 'user_login_text_align',
								'default'     => 'center',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'textflow' => esc_html__( 'Text Flow', 'fusion-builder' ),
									'center'   => esc_html__( 'Center', 'fusion-builder' ),
								],
							],
							'user_login_form_field_layout' => [
								'label'       => esc_html__( 'User Login Form Field Layout', 'fusion-builder' ),
								'description' => __( 'Choose if form fields should be stacked and full width, or if they should be floated. <strong>IMPORTANT:</strong> This option only works for the login and the register form.', 'fusion-builder' ),
								'id'          => 'user_login_form_field_layout',
								'default'     => 'stacked',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'stacked' => esc_html__( 'Stacked', 'fusion-builder' ),
									'floated' => esc_html__( 'Floated', 'fusion-builder' ),
								],
							],
							'user_login_form_show_labels'  => [
								'label'       => esc_html__( 'User Login Show Labels', 'fusion-builder' ),
								'description' => esc_html__( 'Controls if the form field labels should be shown.', 'fusion-builder' ),
								'id'          => 'user_login_form_show_labels',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
							],
							'user_login_form_show_placeholders' => [
								'label'       => esc_html__( 'User Login Show Placeholders', 'fusion-builder' ),
								'description' => esc_html__( 'Controls if the form field placeholders should be shown.', 'fusion-builder' ),
								'id'          => 'user_login_form_show_placeholders',
								'default'     => 'yes',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
							],
							'user_login_form_show_remember_me' => [
								'label'       => esc_html__( 'User Login Show Remember Me Checkbox', 'fusion-builder' ),
								'description' => esc_html__( 'Controls if the remenber me checkbox should be displayed in the login form.', 'fusion-builder' ),
								'id'          => 'user_login_form_show_remember_me',
								'default'     => 'no',
								'type'        => 'radio-buttonset',
								'transport'   => 'postMessage',
								'choices'     => [
									'yes' => esc_html__( 'Yes', 'fusion-builder' ),
									'no'  => esc_html__( 'No', 'fusion-builder' ),
								],
							],
							'user_login_form_background_color' => [
								'label'       => esc_html__( 'User Login Form Background Color', 'fusion-builder' ),
								'description' => esc_html__( 'Controls the color of the form background.', 'fusion-builder' ),
								'id'          => 'user_login_form_background_color',
								'default'     => 'var(--awb-color2)',
								'type'        => 'color-alpha',
								'transport'   => 'postMessage',
							],
						],
					],
				];
			}

			/**
			 * Sets the necessary scripts.
			 *
			 * @access public
			 * @since 3.2
			 * @return void
			 */
			public function on_first_render() {
				$fusion_settings = awb_get_fusion_settings();

				if ( $fusion_settings->get( 'recaptcha_login_form' ) && $fusion_settings->get( 'recaptcha_public' ) && $fusion_settings->get( 'recaptcha_private' ) && ! function_exists( 'recaptcha_get_html' ) && ! class_exists( 'ReCaptcha' ) ) {
					$recaptcha_script_uri = 'https://www.google.com/recaptcha/api.js?render=explicit&hl=' . get_locale() . '&onload=fusionOnloadCallback';
					if ( 'v2' === $fusion_settings->get( 'recaptcha_version' ) ) {
						$recaptcha_script_uri = 'https://www.google.com/recaptcha/api.js?hl=' . get_locale();
					}
					Fusion_Dynamic_JS::enqueue_script( 'recaptcha-api', $recaptcha_script_uri, '', [], FUSION_BUILDER_VERSION, false );
				}
				Fusion_Dynamic_JS::enqueue_script( 'fusion-button' );
			}

			/**
			 * Load base CSS.
			 *
			 * @access public
			 * @since 3.0
			 * @return void
			 */
			public function add_css_files() {
				FusionBuilder()->add_element_css( FUSION_BUILDER_PLUGIN_DIR . 'assets/css/shortcodes/login.min.css' );
			}
		}
	}

	new FusionSC_Login();

}

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_login() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Login',
			[
				'name'        => esc_html__( 'User Login', 'fusion-builder' ),
				'description' => esc_html__( 'Enter some content for this block', 'fusion-builder' ),
				'shortcode'   => 'fusion_login',
				'icon'        => 'fusiona-calendar-check-o',
				'help_url'    => 'https://theme-fusion.com/documentation/avada/elements/user-login-element/',
				'params'      => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the alignment of all content parts. "Text Flow" follows the default text align of the site. "Center" will center all elements.', 'fusion-builder' ),
						'param_name'  => 'text_align',
						'value'       => [
							''         => esc_attr__( 'Default', 'fusion-builder' ),
							'textflow' => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'center'   => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Form Field Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if form fields should be stacked and full width, or if they should be floated.', 'fusion-builder' ),
						'param_name'  => 'form_field_layout',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Heading', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a heading text.', 'fusion-builder' ),
						'param_name'  => 'heading',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a heading color.', 'fusion-builder' ),
						'param_name'  => 'heading_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'heading',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Caption', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a caption text.', 'fusion-builder' ),
						'param_name'  => 'caption',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Caption Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a caption color.', 'fusion-builder' ),
						'param_name'  => 'caption_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'caption',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Labels', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the form field labels should be shown.', 'fusion-builder' ),
						'param_name'  => 'show_labels',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Placeholders', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the form field placeholders should be shown.', 'fusion-builder' ),
						'param_name'  => 'show_placeholders',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the button span the full width.', 'fusion-builder' ),
						'param_name'  => 'button_fullwidth',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a background color for the form wrapping box.', 'fusion-builder' ),
						'param_name'  => 'form_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'user_login_form_background_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a link color.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Remember Me Checkbox', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the remember me checkbox should be displayed in the login form.', 'fusion-builder' ),
						'param_name'  => 'show_remember_me',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Redirection Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the url to which a user should redirected after form submission. Leave empty to use the same page.', 'fusion-builder' ),
						'param_name'   => 'redirection_link',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Register Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the url the "Register" link should open.', 'fusion-builder' ),
						'param_name'   => 'register_link',
						'value'        => '',
						'dynamic_data' => true,
					],
					[
						'type'         => 'link_selector',
						'heading'      => esc_attr__( 'Lost Password Link', 'fusion-builder' ),
						'description'  => esc_attr__( 'Add the url the "Lost Password" link should open.', 'fusion-builder' ),
						'param_name'   => 'lost_password_link',
						'value'        => '',
						'dynamic_data' => true,
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_login' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_lost_password() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Login',
			[
				'name'      => esc_html__( 'User Lost Password', 'fusion-builder' ),
				'shortcode' => 'fusion_lost_password',
				'icon'      => 'fusiona-calendar-check-o',
				'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/user-lost-password-element/',
				'params'    => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the alignment of all content parts. "Text Flow" follows the default text align of the site. "Center" will center all elements.', 'fusion-builder' ),
						'param_name'  => 'text_align',
						'value'       => [
							''         => esc_attr__( 'Default', 'fusion-builder' ),
							'textflow' => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'center'   => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Heading', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a heading text.', 'fusion-builder' ),
						'param_name'  => 'heading',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a heading color.', 'fusion-builder' ),
						'param_name'  => 'heading_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'heading',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Caption', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a caption text.', 'fusion-builder' ),
						'param_name'  => 'caption',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Caption Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a caption color.', 'fusion-builder' ),
						'param_name'  => 'caption_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'caption',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Labels', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the form field labels should be shown.', 'fusion-builder' ),
						'param_name'  => 'show_labels',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Placeholders', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the form field placeholders should be shown.', 'fusion-builder' ),
						'param_name'  => 'show_placeholders',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the button span the full width.', 'fusion-builder' ),
						'param_name'  => 'button_fullwidth',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a background color for the form wrapping box.', 'fusion-builder' ),
						'param_name'  => 'form_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'user_login_form_background_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a link color.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
					],
					[
						'type'        => 'link_selector',
						'heading'     => esc_attr__( 'Redirection Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the url to which a user should redirected after form submission. Leave empty to use the same page.', 'fusion-builder' ),
						'param_name'  => 'redirection_link',
						'value'       => '',
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_lost_password' );

/**
 * Map shortcode to Avada Builder.
 *
 * @since 1.0
 */
function fusion_element_register() {
	$fusion_settings = awb_get_fusion_settings();

	fusion_builder_map(
		fusion_builder_frontend_data(
			'FusionSC_Login',
			[
				'name'      => esc_html__( 'User Register', 'fusion-builder' ),
				'shortcode' => 'fusion_register',
				'icon'      => 'fusiona-calendar-check-o',
				'help_url'  => 'https://theme-fusion.com/documentation/avada/elements/user-register-element/',
				'params'    => [
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Text Align', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose the alignment of all content parts. "Text Flow" follows the default text align of the site. "Center" will center all elements.', 'fusion-builder' ),
						'param_name'  => 'text_align',
						'value'       => [
							''         => esc_attr__( 'Default', 'fusion-builder' ),
							'textflow' => esc_attr__( 'Text Flow', 'fusion-builder' ),
							'center'   => esc_attr__( 'Center', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Form Field Layout', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose if form fields should be stacked and full width, or if they should be floated.', 'fusion-builder' ),
						'param_name'  => 'form_field_layout',
						'value'       => [
							''        => esc_attr__( 'Default', 'fusion-builder' ),
							'stacked' => esc_attr__( 'Stacked', 'fusion-builder' ),
							'floated' => esc_attr__( 'Floated', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Heading', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a heading text.', 'fusion-builder' ),
						'param_name'  => 'heading',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Heading Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a heading color.', 'fusion-builder' ),
						'param_name'  => 'heading_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'heading',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Caption', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a caption text.', 'fusion-builder' ),
						'param_name'  => 'caption',
						'value'       => esc_attr__( 'Your Content Goes Here', 'fusion-builder' ),
						'placeholder' => true,
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Caption Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a caption color.', 'fusion-builder' ),
						'param_name'  => 'caption_color',
						'value'       => '',
						'dependency'  => [
							[
								'element'  => 'caption',
								'value'    => '',
								'operator' => '!=',
							],
						],
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Labels', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the form field labels should be shown.', 'fusion-builder' ),
						'param_name'  => 'show_labels',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Show Placeholders', 'fusion-builder' ),
						'description' => esc_attr__( 'Controls if the form field placeholders should be shown.', 'fusion-builder' ),
						'param_name'  => 'show_placeholders',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'radio_button_set',
						'heading'     => esc_attr__( 'Button Span', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose to have the button span the full width.', 'fusion-builder' ),
						'param_name'  => 'button_fullwidth',
						'value'       => [
							''    => esc_attr__( 'Default', 'fusion-builder' ),
							'yes' => esc_attr__( 'Yes', 'fusion-builder' ),
							'no'  => esc_attr__( 'No', 'fusion-builder' ),
						],
						'default'     => '',
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Form Background Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a background color for the form wrapping box.', 'fusion-builder' ),
						'param_name'  => 'form_background_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'user_login_form_background_color' ),
					],
					[
						'type'        => 'colorpickeralpha',
						'heading'     => esc_attr__( 'Link Color', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a link color.', 'fusion-builder' ),
						'param_name'  => 'link_color',
						'value'       => '',
						'default'     => $fusion_settings->get( 'link_color' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'Registration Notice', 'fusion-builder' ),
						'description' => esc_attr__( 'Choose a notice text that will be displayed before the register button. Leave empty if no text should be displayed.', 'fusion-builder' ),
						'param_name'  => 'register_note',
						'value'       => esc_attr__( 'Registration confirmation will be emailed to you.', 'fusion-builder' ),
					],
					[
						'type'        => 'link_selector',
						'heading'     => esc_attr__( 'Redirection Link', 'fusion-builder' ),
						'description' => esc_attr__( 'Add the url to which a user should redirected after form submission. Leave empty to use the same page.', 'fusion-builder' ),
						'param_name'  => 'redirection_link',
						'value'       => '',
					],
					'fusion_margin_placeholder' => [
						'param_name' => 'margin',
						'group'      => esc_attr__( 'General', 'fusion-builder' ),
						'value'      => [
							'margin_top'    => '',
							'margin_right'  => '',
							'margin_bottom' => '',
							'margin_left'   => '',
						],
					],
					[
						'type'        => 'checkbox_button_set',
						'heading'     => esc_attr__( 'Element Visibility', 'fusion-builder' ),
						'param_name'  => 'hide_on_mobile',
						'value'       => fusion_builder_visibility_options( 'full' ),
						'default'     => fusion_builder_default_visibility( 'array' ),
						'description' => esc_attr__( 'Choose to show or hide the element on small, medium or large screens. You can choose more than one at a time.', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS Class', 'fusion-builder' ),
						'description' => esc_attr__( 'Add a class to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'class',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
					[
						'type'        => 'textfield',
						'heading'     => esc_attr__( 'CSS ID', 'fusion-builder' ),
						'description' => esc_attr__( 'Add an ID to the wrapping HTML element.', 'fusion-builder' ),
						'param_name'  => 'id',
						'value'       => '',
						'group'       => esc_attr__( 'General', 'fusion-builder' ),
					],
				],
			]
		)
	);
}
add_action( 'fusion_builder_before_init', 'fusion_element_register' );
