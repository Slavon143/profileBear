<?php
/**
 * Avada-Builder Shortcode Element.
 *
 * @package Avada-Core
 * @since 3.9.2
 */

/**
 * Shortcode class.
 *
 * @package Avada-Core
 * @since 3.9.2
 */
class FusionSC_Contact_Form {

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 3.9.2
	 */
	public function __construct() {
		add_shortcode( 'fusion_contact_form', [ $this, 'render' ] );

		// Add the contact form to Avada's contact page template.
		add_action( 'avada_add_contact_template_contents', [ $this, 'echo_contact_form' ] );
	}

	/**
	 * Echos the element.
	 *
	 * @access public
	 * @since 3.9.2
	 * @return void
	 */
	public function echo_contact_form() {
		$this->render( [ 'echo' => 1 ] );
	}

	/**
	 * Render the element.
	 *
	 * @access public
	 * @since 3.9.2
	 * @param  array  $args    Shortcode parameters.
	 * @param  string $content Content between shortcode.
	 * @return void|string     HTML output.
	 */
	public function render( $args, $content = '' ) {
		$fusion_settings = class_exists( 'Fusion_Settings' ) ? awb_get_fusion_settings() : false;

		$args = shortcode_atts(
			[
				'email_address'          => $fusion_settings ? $fusion_settings->get( 'email_address' ) : '',
				'recaptcha_version'      => $fusion_settings ? $fusion_settings->get( 'recaptcha_version' ) : 'v3',
				'recaptcha_color_scheme' => $fusion_settings ? $fusion_settings->get( 'recaptcha_color_scheme' ) : 'light',
				'public_key'             => $fusion_settings ? $fusion_settings->get( 'recaptcha_public' ) : '',
				'private_key'            => $fusion_settings ? $fusion_settings->get( 'recaptcha_private' ) : '',
				'badge_position'         => $fusion_settings ? $fusion_settings->get( 'recaptcha_badge_position' ) : 'inline',
				'comment_position'       => $fusion_settings ? $fusion_settings->get( 'contact_comment_position' ) : 'below',
				'privacy_checkbox'       => $fusion_settings ? $fusion_settings->get( 'contact_form_privacy_checkbox' ) : 0,
				'privacy_label'          => $fusion_settings ? $fusion_settings->get( 'contact_form_privacy_label' ) : '',
				'echo'                   => 0,
			],
			$args,
			'fusion_contact_form'
		);

		/**
		 * Instantiate the Fusion_Contact class.
		 */
		$fusion_contact = new Fusion_Contact( $args );

		ob_start();
		$fusion_contact->get_recaptcha_script();
		$fusion_contact->get_error_messages();
		$fusion_contact->get_contact_form();

		$html = apply_filters( 'fusion_element_contact_form_content', ob_get_clean(), $args );

		if ( ! $args['echo'] ) {
			return $html;
		}

		// No need to escape this, it's just the forms that have already been properly escaped.
		echo $html; // phpcs:ignore WordPress.Security.EscapeOutput
	}

}

new FusionSC_Contact_Form();
