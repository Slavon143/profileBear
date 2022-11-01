( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		// Update the Instagram status on parent window.
		window.updateInstagramAPI = function( status ) {
			var $instagramContent;

			if ( 'string' === typeof status && 'function' === typeof jQuery ) {
				$instagramContent = jQuery( '#fusion-instagram-content' );
				if ( ! $instagramContent.length ) {
					return false;
				}
				$instagramContent.find( '> div' ).hide();
				if ( 'revoke' === status ) {
					$instagramContent.find( '[data-id="no_token"]' ).css( { display: 'flex' } );
				} else if ( 'success' === status ) {
					$instagramContent.find( '[data-id="connected"]' ).css( { display: 'flex' } );
				} else {
					$instagramContent.find( '[data-id="error"]' ).css( { display: 'flex' } );
				}
				return 'Updated to ' + status;
			}
			return false;
		};

		// This is the auth window.
		if ( window.opener && 'function' === typeof window.opener.updateInstagramAPI && 'object' === typeof window.fusionInstagramOAuth && 'string' === typeof window.fusionInstagramOAuth.status ) {
			window.opener.updateInstagramAPI( window.fusionInstagramOAuth.status );
			window.close();
		}

		// Firefox needs link opened via JS.
		jQuery( document ).on( 'click', '#fusion-instagram-content .button-primary', function( event ) {
			event.preventDefault();
			window.open( jQuery( this ).attr( 'href' ), '_blank' );
		} );
	} );
}( jQuery ) );
