( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		// Update the Mailchimp status on parent window.
		window.updateMailchimpAPI = function( status ) {
			var $mailchimpContent;

			if ( 'string' === typeof status && 'function' === typeof jQuery ) {
				$mailchimpContent = jQuery( '#fusion-mailchimp-content' );
				if ( ! $mailchimpContent.length ) {
					return false;
				}
				$mailchimpContent.find( '> div' ).hide();
				if ( 'revoke' === status ) {
					$mailchimpContent.find( '[data-id="no_token"]' ).css( { display: 'flex' } );
				} else if ( 'success' === status ) {
					$mailchimpContent.find( '[data-id="connected"]' ).css( { display: 'flex' } );
				} else {
					$mailchimpContent.find( '[data-id="error"]' ).css( { display: 'flex' } );
				}
				return 'Updated to ' + status;
			}
			return false;
		};

		// This is the auth window.
		if ( window.opener && 'function' === typeof window.opener.updateMailchimpAPI && 'object' === typeof window.fusionMailchimpOAuth && 'string' === typeof window.fusionMailchimpOAuth.status ) {
			window.opener.updateMailchimpAPI( window.fusionMailchimpOAuth.status );
			window.close();
		}

		// Firefox needs link opened via JS.
		jQuery( document ).on( 'click', '#fusion-mailchimp-content .button-primary', function( event ) {
			event.preventDefault();

			window.open( jQuery( this ).attr( 'href' ), '_blank' );
		} );
	} );
}( jQuery ) );
