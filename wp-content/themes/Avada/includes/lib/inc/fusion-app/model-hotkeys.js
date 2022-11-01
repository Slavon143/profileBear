/* global FusionEvents, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Hotkeys = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			var self = this;

			jQuery( 'body' ).on( 'keydown', function( event ) {
				if ( self.isValidTarget( event ) ) {
					self.checkKey( event );
				}
			} );
		},

		/**
		 * Reattach listeners for iframe.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		attachListener: function() {
			var self = this;
			jQuery( document.getElementById( 'fb-preview' ).contentWindow.document ).off( 'keydown' );
			jQuery( document.getElementById( 'fb-preview' ).contentWindow.document ).on( 'keydown', function( event ) {
				if ( self.isValidTarget( event ) ) {
					self.checkKey( event );
				}
			} );
		},

		/**
		 * Check combination of keys pressed.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {void}
		 */
		checkKey: function( event ) {

			// If disabled.
			if ( ( 'undefined' !== typeof FusionApp && 'undefined' !== typeof FusionApp.preferencesData && 'off' === FusionApp.preferencesData.keyboard_shortcuts ) ) {
				return;
			}

			if ( event.ctrlKey || event.metaKey || event.shiftKey ) {

				// If only Shift key.
				if ( this.isShiftKey( event ) && ! this.isMetaKey( event ) ) {
					switch ( event.keyCode ) {

					// Key Shift + P for Preview.
					case 80:
						event.preventDefault();
						jQuery( '.fusion-toolbar-nav li.preview a' ).trigger( 'click' );
						break;

						// Key Shift + T to toggle sidebar.
					case 84:
						if ( 'undefined' !== typeof FusionApp.sidebarView ) {
							event.preventDefault();
							FusionApp.sidebarView.togglePanel();
						}
						break;
					}
				}

				// If only meta key.
				if ( this.isMetaKey( event ) && ! this.isShiftKey( event ) ) {
					switch ( event.keyCode ) {

					// Return key to close modal.
					case 13:
						if ( 0 < jQuery( '.fusion-builder-dialog .ui-dialog-buttonset .ui-button' ).length ) {
							jQuery( '.fusion-builder-dialog .ui-dialog-buttonset .ui-button' ).trigger( 'click' );
						} else {
							jQuery( '.fusion-builder-dialog .ui-button.ui-dialog-titlebar-close' ).trigger( 'click' );
						}

						break;

						// Key 1 for large view.
					case 49:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-desktop' ).trigger( 'click' );
						break;

						// Key 2 for mobile view portrait.
					case 50:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-mobile.portrait' ).trigger( 'click' );
						break;

						// Key 3 for mobile view landscape.
					case 51:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-mobile.landscape' ).trigger( 'click' );
						break;

						// Key 4 for tablet view portrait.
					case 52:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-tablet.portrait' ).trigger( 'click' );
						break;

						// Key 5 for tablet view landscape.
					case 53:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-tablet.landscape' ).trigger( 'click' );
						break;

						// Key D to clear layout.
					case 68:
						event.preventDefault();
						jQuery( '.fusion-builder-clear-layout' ).trigger( 'click' );
						break;

						// Key Q to exit the builder.
					case 81:
						event.preventDefault();
						jQuery( '.fusion-exit-builder-list .exit-to-back-end a' ).trigger( 'click' );
						break;

						// Key S to save, click rather than save directly so that animations occur.
					case 83:
						event.preventDefault();
						if ( ! jQuery( '.fusion-builder-save-page' ).data( 'disabled' ) ) {
							jQuery( '.fusion-builder-save-page' ).trigger( 'click' );
						}
						break;

						// Key Y to redo builder change.
					case 89:
						event.preventDefault();
						FusionEvents.trigger( 'fusion-history-redo' );
						break;

						// Key Z to undo builder change.
					case 90:
						event.preventDefault();
						FusionEvents.trigger( 'fusion-history-undo' );
						break;
					}
				}

				// If both shift and meta key.
				if ( this.isMetaKey( event ) && this.isShiftKey( event ) ) {
					switch ( event.keyCode ) {

					// Key C to open custom css panel.
					case 67:
						if ( 0 === jQuery( 'body' ).find( '.ui-dialog' ).length && 'undefined' !== typeof FusionApp.sidebarView ) {
							event.preventDefault();
							FusionApp.sidebarView.openOption( '_fusion_builder_custom_css', 'po' );
						}
						break;

						// Key S to save, click rather than save directly so that animations occur.
					case 83:
						event.preventDefault();
						if ( 0 === jQuery( 'body' ).find( '.ui-dialog' ).length && ! FusionApp.sidebarView.panelIsOpen() ) {
							jQuery( '.fusion-builder-save-template' ).trigger( 'click' );
						}
						break;
					}
				}
			}
		},

		/**
		 * Checks if meta key is pressed.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {boolean} - Returns the bool value.
		 */
		isMetaKey: function( event ) {
			if ( event.ctrlKey || event.metaKey ) {
				return true;
			}

			return false;
		},

		/**
		 * Checks if shift key is pressed.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {boolean} - Returns the bool value.
		 */
		isShiftKey: function( event ) {
			if ( event.shiftKey ) {
				return true;
			}

			return false;
		},

		/**
		 * Checks if target is valid.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {boolean} - Returns the bool value.
		 */
		isValidTarget: function( event ) {

			//Ctrl+S/CMD+S always valid
			if (
				( 83 === event.keyCode && event.metaKey && !event.altKey ) || // CMD + s MacOS
				( 83 === event.keyCode && event.ctrlKey && !event.altKey ) // Ctrl + s Windows
			) {
				return true;
			}


			if ( jQuery( event.target ).is( 'input' ) || jQuery( event.target ).is( 'textarea' ) || jQuery( event.target ).is( '.fusion-live-editable' ) ) {
				return false;
			}

			return true;
		}
	} );

}( jQuery ) );
