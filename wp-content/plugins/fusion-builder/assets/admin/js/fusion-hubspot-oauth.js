( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		// Update the HubSpot status on parent window.
		window.updateHubSpotAPI = function( status ) {
			var $hubspotContent;

			if ( 'string' === typeof status && 'function' === typeof jQuery ) {
				$hubspotContent = jQuery( '#fusion-hubspot-content' );
				if ( ! $hubspotContent.length ) {
					return false;
				}
				$hubspotContent.find( '> div' ).hide();
				if ( 'revoke' === status ) {
					$hubspotContent.find( '[data-id="no_token"]' ).css( { display: 'flex' } );
				} else if ( 'success' === status ) {
					$hubspotContent.find( '[data-id="connected"]' ).css( { display: 'flex' } );
				} else {
					$hubspotContent.find( '[data-id="error"]' ).css( { display: 'flex' } );
				}
				return 'Updated to ' + status;
			}
			return false;
		};

		// This is the auth window.
		if ( window.opener && 'function' === typeof window.opener.updateHubSpotAPI && 'object' === typeof window.fusionHubspotOAuth && 'string' === typeof window.fusionHubspotOAuth.status ) {
			window.opener.updateHubSpotAPI( window.fusionHubspotOAuth.status );
			window.close();
		}

		// Firefox needs link opened via JS.
		jQuery( document ).on( 'click', '#fusion-hubspot-content .button-primary', function( event ) {
			event.preventDefault();

			window.open( jQuery( this ).attr( 'href' ), '_blank' );
		} );
	} );
}( jQuery ) );
