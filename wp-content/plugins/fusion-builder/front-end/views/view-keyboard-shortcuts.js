/* global FusionApp */
/* eslint no-empty-function: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Library
		FusionPageBuilder.keyBoardShorCutsView = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-front-end-keyboard-shortcuts' ).html() ),

			events: {
				'click .fusion-open-prefernces-panel': 'openPreferencePanel'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var self = this;
				this.$el.html( this.template() );

				this.$el = this.$el.dialog( {
					title: 'Keyboard Shortcuts',
					width: 600,
					height: FusionApp.dialog.dialogHeight,
					draggable: false,
					resizable: false,
					modal: true,
					dialogClass: 'fusion-builder-large-library-dialog fusion-builder-dialog fusion-builder-keyboard-shortcuts-dialog',

					open: function() {
						FusionApp.dialog.resizeDialog();
					},
					close: function() {
						self.removeDialog();
					}
				} ).closest( '.ui-dialog' );

				return this;
			},

			/**
			 * Removes the view.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeDialog: function() {
				this.remove();
			},

			/**
			 * Removes this view and open preference panel.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			openPreferencePanel: function() {
				this.remove();
				jQuery( '.fusion-builder-preferences' ).trigger( 'click' );
			}
		} );
	} );
}( jQuery ) );
