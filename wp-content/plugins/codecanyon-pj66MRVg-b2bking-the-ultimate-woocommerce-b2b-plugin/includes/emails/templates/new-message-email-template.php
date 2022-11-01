<?php
	
defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );

$namestr = explode(':',esc_html__('Name: ', 'b2bking'))[0];

// check if this is quote request started by guest
if ( ( explode(':',$userid)[0] ) === $namestr ){		
	// if the username starts with 'name'
	$user_login = $userid;
} else {
	$user_login = get_user_by('id', $userid)->user_login;
}

?>

<p>
	<?php 

	// check if conversation is QUOTE
	$quote = 'no';
	$requester = get_post_meta($conversationid, 'b2bking_quote_requester', true);
	if (!empty($requester)){
		// is a quote

		// check if it has only 1 message = the first message in the quote
		$msgnr = get_post_meta($conversationid, 'b2bking_conversation_messages_number', true);
		if (intval($msgnr) === 1){
			$quote = 'yes';
		} else {
			// if guest there are messages
			if (intval($msgnr) === 2 && strpos($requester, '@') !== false) {
				$quote = 'yes';
			}
		}
	}

	if ($quote === 'no'){
		esc_html_e( 'You have a new message.', 'b2bking');	
	} else {
		esc_html_e( 'You received a new quote request.', 'b2bking');	
	}


	?>
	<br /><br />
 	<?php 
 		if ( ( explode(':',$userid)[0] ) !== 'Quoteemail' ){

 			echo '<b>'.esc_html__( 'User: ','b2bking').'</b>'; 

 			echo esc_html($user_login); 

 			?>
 			<br><br>
 			<?php
 		}
 	?>
 	<?php 

 	if ($quote === 'no'){

 		echo '<b>'.esc_html__( 'Message: ','b2bking').'</b>';

 	}
 	echo wp_kses( $message, array( 'br' => true, 'b' => true, 'a' => array(
 	        'href' => array() ) ) ); ?>
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
