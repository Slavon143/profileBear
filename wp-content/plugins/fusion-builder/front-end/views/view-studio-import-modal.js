/* global FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Studio import modal view.
		FusionPageBuilder.StudioImportModalView = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-studio-import-modal' ).html() ),
			className: 'fusion-builder-studio-import-modal',
			events: {
				'click .awb-admin-modal-corner-close': 'closeModal'
			},

			/**
			 * Renders the view.
			 *
			 * @since 3.5.0
			 * @return {Object} this
			 */
			render: function() {

				this.$el.html( this.template() );

				return this;
			},

			/**
			 * Updates modal status.
			 *
			 * @since 3.5.0
			 * @param {String} status - New status text.
			 * @return {void}
			 */
			updateStatus: function( status ) {
				this.$el.find( '.awb-admin-modal-status-bar .awb-admin-modal-status-bar-label' ).html( status );
			},

			/**
			 * Updates modal progress.
			 *
			 * @since 3.5.0
			 * @param {Object} avadaMedia - Avada Media object, all things we need to import.
			 * @param {String} currentImportKey - Object key which is currently being imported.
			 * @return {void}
			 */
			updateProgressBar: function( avadaMedia, currentImportKey ) {
				var mediaKeys = Object.keys( avadaMedia ),
					progress = ( mediaKeys.indexOf( currentImportKey ) + 1 ) / mediaKeys.length;

				this.$el.find( '.awb-admin-modal-status-bar .awb-admin-modal-status-bar-progress-bar' ).css( 'width', ( 100 * progress ) + '%' );
			},

			/**
			 * Remove the view.
			 *
			 * @since 3.5.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 */
			closeModal: function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				FusionEvents.trigger( 'awb-studio-import-modal-closed' );

				this.remove();

			}
		} );
	} );
}( jQuery ) );
