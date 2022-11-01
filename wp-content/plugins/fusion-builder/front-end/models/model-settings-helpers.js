/* global FusionPageBuilderViewManager */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.SettingsHelpers = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function( ) {
			this.openSettingsView      = null;
			this.openChildSettingsView = null;

			this.modalDialogMoreView   = null;

			this.listenTo( window.FusionEvents, 'fusion-settings-modal-save', this.removeElementSettingsViewForModal );
			this.listenTo( window.FusionEvents, 'fusion-settings-modal-cancel', this.removeElementSettingsViewForModal );
			this.listenTo( window.FusionEvents, 'fusion-settings-removed', this.removeElementSettingsView );

			this.listenTo( window.FusionEvents, 'fusion-preferences-editing_mode-updated', this.editingModeChanged );
		},

		/**
		 * Check if element settings panel (dialog) should be rendered or not.
		 *
		 * @since 2.0.0
		 * @param {string} view - Element View.
		 * @return {boolean}
		 */
		shouldRenderSettings: function( view ) {
			var modelParent = view.model.get( 'parent' ),
				isChild     = 'multi_element_child' === view.model.get( 'multi' ),
				modelCid    = view.model.get( 'cid' ),
				generated   = 'generated_element' === view.model.get( 'type' ),
				rendered    = false,
				$editPanel  = 'dialog' === window.FusionApp.preferencesData.editing_mode ? jQuery( '.fusion-builder-dialog.fusion-builder-settings-dialog .ui-dialog-content' ) : window.FusionApp.sidebarView.$el.find( '.fusion-builder-custom-tab' ),
				panelCid,
				$panelWrap;

			if ( generated ) {
				if ( jQuery( '.ui-dialog-content[data-cid="' + modelCid + '"]' ).length ) {
					jQuery( '.ui-dialog-content[data-cid="' + modelCid + '"]' ).closest( '.ui-dialog' ).show();
					return false;
				}
				if ( jQuery( '.ui-dialog-content:not( [data-cid="' + modelCid + '"] )' ).length ) {
					jQuery( '.ui-dialog-content:not( [data-cid="' + modelCid + '"] )' ).closest( '.ui-dialog' ).hide();
				}
				return true;
			}

			if ( $editPanel.length ) {

				// Check if panel is already open, if so do nothing.
				$editPanel.each( function() {
					panelCid   = jQuery( this ).attr( 'data-cid' );
					$panelWrap = 'dialog' === window.FusionApp.preferencesData.editing_mode ? jQuery( this ).closest( '.fusion-builder-dialog.fusion-builder-settings-dialog' ) : jQuery( this );

					if ( parseInt( modelCid, 10 ) === parseInt( panelCid, 10 ) ) {
						$panelWrap.show();
						rendered = true;

						// continue.
						return;
					}

					$panelWrap.hide();
				} );

			}

			// Show panel if it is already rendered.
			if ( rendered ) {

				// If not dialog we have to show correct sidebar tab before exit.
				if ( 'dialog' !== window.FusionApp.preferencesData.editing_mode ) {
					window.FusionApp.sidebarView.openSidebarAndShowEOTab();
				}

				return false;
			}

			// Remove the parent view unless its a direct parent of what we want to edit.
			if ( this.openSettingsView ) {
				if ( isChild && modelParent === this.openSettingsView.model.get( 'cid' ) && 'multi_element_parent' === this.openSettingsView.model.get( 'multi' ) ) {

					if ( 'dialog' === window.FusionApp.preferencesData.editing_mode ) {
						this.openSettingsView.$el.closest( '.fusion-builder-dialog.fusion-builder-settings-dialog' ).hide();
					} else {
						this.openSettingsView.$el.hide();
					}

				} else {
					this.openSettingsView.saveSettings();
				}
			}

			// If we have open child view, remove it.
			if ( this.openChildSettingsView ) {
				this.openChildSettingsView.saveSettings();
			}

			// Set newly opened view to access.
			if ( ! isChild ) {
				this.openSettingsView = view;
			} else {
				this.openChildSettingsView = view;
			}

			return true;
		},

		removeElementSettingsView: function( cid ) {
			var settingsWrappers,
				foundCid;

			if ( this.openSettingsView && cid === this.openSettingsView.model.get( 'cid' ) ) {
				this.openSettingsView  = false;
			}
			if ( this.openChildSettingsView && cid === this.openChildSettingsView.model.get( 'cid' ) ) {
				// If the cid is from a child removed, and the settings there are present from it's parent, then show them.
				settingsWrappers = window.FusionApp.sidebarView.$el.find( '.fusion-builder-custom-tab' );
				foundCid = parseInt( settingsWrappers.attr( 'data-cid' ) );

				if ( foundCid === this.openChildSettingsView.model.get( 'parent' ) ) {
					setTimeout( function() {
						FusionPageBuilderViewManager.getView( foundCid ).settings();
					}, 20 );
				}
				this.openChildSettingsView = false;
			}
		},

		/**
		 * Remove Element Settings for Modals.
		 *
		 * A dialog modal can be either for an element settings(when the dialog option is on),
		 * or for another thing. This function will not remove the open setting if the element
		 * settings are inside the left panel(sidebar settings mode).
		 */
		removeElementSettingsViewForModal: function( cid ) {
			if ( this.openSettingsView && cid === this.openSettingsView.model.get( 'cid' ) ) {
				// Verify if the settings are inside left panel. Settings can also be out
				if ( 0 === this.openSettingsView.$el.closest( '#fusion-builder-sections-eo' ).length ) {
					this.openSettingsView = false;
				}
			}
			if ( this.openChildSettingsView && cid === this.openChildSettingsView.model.get( 'cid' ) ) {
				if ( 0 === this.openChildSettingsView.$el.closest( '#fusion-builder-sections-eo' ).length ) {
					this.openChildSettingsView = false;
				}
			}
		},

		/**
         * Render dialog more options template.
         *
         * @since 2.0.0
         * @param {Object} view - The view.
         * @param {Object} event - The event.
         * @return {void}
         */
		renderDialogMoreOptions: function( view ) {
			var $wrap = 'dialog' === window.FusionApp.preferencesData.editing_mode ? view.$el.closest( '.ui-dialog' ).find( '.fusion-utility-menu-wrap' ) : view.$el.find( '.fusion-utility-menu-wrap' );

			this.modalDialogMoreView = new FusionPageBuilder.modalDialogMore( { model: this.model } );

			jQuery( this.modalDialogMoreView.render( { view: view } ).el ).appendTo( $wrap );

			// After child modal is closed 'click' is attached again.
			$wrap.find( '.fusion-utility-menu' ).off().on( 'click', function( event ) {
				$wrap = jQuery( this ).closest( '.fusion-utility-menu-wrap' );

				$wrap.toggleClass( 'active' );

				event.stopPropagation();
				window.FusionPageBuilderApp.sizesHide( event );
			} );
		},

		/**
         * Things to be done when editing_mode preference is changed.
         */
		editingModeChanged: function() {
			if ( this.openSettingsView ) {
				this.openSettingsView.saveSettings();
			}
			if ( this.openChildSettingsView ) {
				this.openChildSettingsView.saveSettings();
			}
		}

	} );
}( jQuery ) );
