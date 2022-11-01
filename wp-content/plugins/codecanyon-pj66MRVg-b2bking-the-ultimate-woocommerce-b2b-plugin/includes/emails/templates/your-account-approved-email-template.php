<?php
	
defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );


$user = get_user_by('email', $email->recipient);
$first_name = get_user_meta($user->ID, 'billing_first_name', true);

if (!empty($first_name)){
	echo esc_html__('Hi ','b2bking').$first_name;
	echo '<br>';
}
?>
<p>
	<?php esc_html_e( 'Congratulations! Your account has been approved.', 'b2bking');	?>
	<br />
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
