/* global FusionApp, fusionAppConfig, FusionEvents */
/* eslint no-empty-function: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Preferences.
		FusionPageBuilder.PreferencesView = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-front-end-preferences' ).html() ),
			events: {
				'click .fusion-panel-description': 'showHideDescription',
				'change .button-set-value': 'optionChanged'
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
					title: 'Preferences',
					width: 600,
					height: FusionApp.dialog.dialogHeight,
					draggable: false,
					resizable: false,
					modal: true,
					dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog fusion-builder-preferences-dialog',

					open: function( event ) {
						var dialogContent = jQuery( event.target );

						FusionPageBuilder.options.radioButtonSet.optionRadioButtonSet( dialogContent );
						FusionApp.dialog.resizeDialog();
					},

					close: function() {
						self.saveChanges();
					}
				} ).closest( '.ui-dialog' );

				return this;
			},

			/**
			 * Trigger live-update when an option changes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			optionChanged: function() {
				var $preferences = {},
					preferencesChanged = [],
					i;

				this.$el.find( 'input' ).each( function() {
					$preferences[ jQuery( this ).attr( 'id' ) ] = jQuery( this ).val();

					if ( $preferences[ jQuery( this ).attr( 'id' ) ] !== FusionApp.preferencesData[ jQuery( this ).attr( 'id' ) ] ) {
						preferencesChanged.push( jQuery( this ).attr( 'id' ) );
					}
				} );

				FusionApp.preferencesData = $preferences;

				for ( i = 0; i < preferencesChanged.length; i++ ) {
					FusionEvents.trigger( 'fusion-preferences-' + preferencesChanged[ i ] + '-updated' );
				}
			},

			/**
			 * Cancel the changes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			saveChanges: function() {
				var $preferences = {};

				jQuery( 'li.fusion-builder-preferences' ).css( 'pointer-events', 'none' );
				this.$el.find( 'input' ).each( function() {
					$preferences[ jQuery( this ).attr( 'id' ) ] = jQuery( this ).val();
				} );

				$preferences.overWriteType    = jQuery( '.awb-import-option input[name="overwrite-type"]:checked' ).val();
				$preferences.shouldInvert     = jQuery( '.awb-import-option input[name="invert"]:checked' ).val();
				$preferences.contentPlacement = jQuery( '.awb-import-option input[name="load-type"]:checked' ).val();
				$preferences.imagesImport     = jQuery( '.awb-import-option input[name="images"]:checked' ).val();

				FusionApp.preferencesData = $preferences;

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'fusion_app_save_builder_preferences',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						preferences: $preferences
					}
				} )
				.done( function( response ) {
					FusionApp.preferences = response;
					jQuery( 'li.fusion-builder-preferences' ).css( 'pointer-events', 'auto' );
				} );

				this.removeView();
			},

			/**
			 * Removes the view.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeView: function() {
				this.$el.find( '.fusion-save-element-fields' ).remove();
				this.$el.find( '.fusion-tabs-menu' ).appendTo( '#fusion-builder-front-end-library' );
				this.remove();
			},

			/**
			 * Show or hide description.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			showHideDescription: function( event ) {
				var $element = jQuery( event.currentTarget );

				$element.closest( '.fusion-builder-option' ).find( '.description' ).first().slideToggle( 250 );
				$element.toggleClass( 'active' );
			}
		} );
	} );
}( jQuery ) );
