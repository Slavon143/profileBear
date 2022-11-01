<?php
	
defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
$user_login = get_user_by('id', $userid)->user_login;

?>

<p>
	<?php esc_html_e( 'You have a new message.', 'b2bking');	?>
	<br /><br />
 	<?php esc_html_e( 'Username: ','b2bking'); echo esc_html($user_login); ?>
 	<br />
 	<?php esc_html_e( 'Message: ','b2bking'); echo wp_kses( $message, array( 'br' => true, 'b' => true ) ); ?>
</p>
<?php

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
