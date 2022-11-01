/* global fusionBuilderConfig, ajaxurl */
( function( jQuery ) {
	var currentPage = 1,
		totalPages  = 0;

	'use strict';

	jQuery( document ).ready( function() {

		jQuery( '.fusion-remove-form-entry' ).on( 'click', function( event ) {
			var self = this;

			event.preventDefault();

			if ( window.confirm( fusionBuilderConfig.remove_entry_message ) ) { // eslint-disable-line no-alert
				jQuery.ajax( {
					type: 'POST',
					url: fusionBuilderConfig.ajaxurl,
					data: {
						action: 'fusion_remove_form_entry',
						fusion_entry_nonce: fusionBuilderConfig.fusion_entry_nonce,
						entry: jQuery( this ).data( 'key' )
					},
					complete: function() {
						jQuery( self ).closest( 'tr' ).remove();

						setTimeout( function() {
							if ( ! jQuery( '.avada_page_avada-form-entries .row-actions' ).length ) {
								jQuery( '#fusion-form-export' ).addClass( 'disabled' );
							}
						}, 50 );
					}
				} );
			}
		} );

		// Export form entries button.
		jQuery( '#fusion-form-export' ).on( 'click', function( event ) {
			event.preventDefault();

			if ( jQuery( '.avada_page_avada-form-entries .row-actions' ).length ) {
				exportForms( jQuery( this ).data( 'form-id' ) );
			}

		} );

		function exportForms( formID ) {

			// Starting export, show status.
			if ( 0 === totalPages ) {
				jQuery( '#fusion-form-export' ).addClass( 'disabled' );
				jQuery( '#fusion-form-export-status' ).css( 'opacity', '1' );
			}

			jQuery.ajax( {
				url: ajaxurl,
				type: 'get',
				data: {
					action: 'fusion_form_export',
					formID: formID,
					currentPage: currentPage,
					totalPages: totalPages,
					nonce: jQuery( '#fusion-form-export-nonce' ).val()
				},
				dataType: 'json'
			} )
			.done( function( result ) {

				// Stop if error.
				if ( 'error_nonce' === result.status || ( 0 < totalPages && currentPage > totalPages ) ) {
					return;
				}

				// Export in progress.
				if ( 'export_processing' === result.status ) {
					currentPage++;

					// First step done.
					if ( 0 === totalPages ) {
						totalPages = result.total_pages;
					}

					// Update export status.
					jQuery( '#fusion-form-export-status-bar' ).css( 'width', ( currentPage / totalPages ) * 100 + '%' ); // eslint-disable-line no-mixed-operators

					exportForms( formID );
				}

				// Export done (last step processed).
				if ( 'export_done' === result.status ) {
					currentPage = 1;
					totalPages  = 0;

					// Reset status.
					jQuery( '#fusion-form-export-status' ).css( 'opacity', '0' );
					jQuery( '#fusion-form-export-status-bar' ).css( 'width', '0' );
					jQuery( '#fusion-form-export' ).removeClass( 'disabled' );

					// Trigger download and cleanup.
					window.location.href = jQuery( '#fusion-form-export' ).attr( 'href' ) + '&nonce=' + jQuery( '#fusion-form-export-nonce' ).val();
				}
			} );
		}

	} );
}( jQuery ) );
