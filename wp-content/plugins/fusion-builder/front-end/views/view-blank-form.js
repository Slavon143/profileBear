/* global FusionPageBuilderApp, FusionPageBuilderViewManager, fusionAllElements, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Builder Blank Page View
		FusionPageBuilder.BlankFormView = window.wp.Backbone.View.extend( {

			className: 'fusion-builder-blank-page',

			template: FusionPageBuilder.template( $( '#fusion-builder-blank-form-template' ).html() ),

			events: {
				'click .fusion-builder-new-section-add': 'addContainer',
				'click .fusion-builder-video-button': 'openVideoModal',
				'click #fusion-load-studio-dialog': 'openLibrary'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var colorScheme = this.getColorScheme( FusionApp.settings.content_bg_color );

				this.$el.html( this.template( this.model.toJSON() ) );

				this.$el.addClass( 'fusion-builder-scheme-' + colorScheme );

				this.$el.find( '#video-dialog' ).dialog( {
					dialogClass: 'fusion-builder-dialog fusion-video-dialog',
					autoOpen: false,
					modal: true,
					height: 470,
					width: 700
				} );

				return this;
			},

			/**
			 * Calculate color scheme depend on hex color.
			 *
			 * @since 2.0.0
			 * @param {string} color - The hex color code to calculate color scheme against.
			 * @return {string}
			 */
			getColorScheme: function( color ) {
				return 0.5 < jQuery.AWB_Color( color ).lightness() ? 'light' : 'dark';
			},

			/**
			 * Opens a video modal.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the modal.
			 * @return {void}
			 */
			openVideoModal: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				jQuery( '#video-dialog' ).dialog( 'open' );
				jQuery( '#video-dialog iframe' ).focus();

				jQuery( '#video-dialog iframe' )[ 0 ].contentWindow.postMessage( '{"event":"command","func":"playVideo","args":""}', '*' );
			},

			/**
			 * Adds a container.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the container addition.
			 * @return {void}
			 */
			addContainer: function( event ) {

				var elementID,
					defaultParams,
					params,
					value,
					newContainer;

				if ( event ) {
					event.preventDefault();
					FusionPageBuilderApp.newContainerAdded = true;
				}

				elementID     = FusionPageBuilderViewManager.generateCid();
				defaultParams = fusionAllElements.fusion_builder_container.params;
				params        = {};

				// Process default options for shortcode.
				_.each( defaultParams, function( param )  {
					if ( _.isObject( param.value ) ) {
						value = param[ 'default' ];
					} else {
						value = param.value;
					}
					params[ param.param_name ] = value;

					if ( 'dimension' === param.type && _.isObject( param.value ) ) {
						_.each( param.value, function( val, name )  {
							params[ name ] = val;
						} );
					}
				} );

				this.collection.add( [
					{
						type: 'fusion_builder_container',
						added: 'manually',
						element_type: 'fusion_builder_container',
						cid: elementID,
						params: params,
						view: this,
						created: 'auto'
					}
				] );

				// Make sure to add row to new container not current one.
				newContainer = FusionPageBuilderViewManager.getView( elementID );
				newContainer.addRow();

				this.removeBlankPageHelper();
			},

			/**
			 * Opens the library.
			 * Calls the LibraryView and then renders it.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			openLibrary: function( event ) {
				var view,
					libraryModel = {
						target: jQuery( event.currentTarget ).data( 'target' ),
						focus: jQuery( event.currentTarget ).data( 'focus' )
					},
					viewSettings = {
						model: libraryModel
					};

				if ( event ) {
					event.preventDefault();
					event.stopPropagation();
					FusionPageBuilderApp.sizesHide( event );
				}

				view = new FusionPageBuilder.LibraryView( viewSettings );
				view.render();
			},

			/**
			 * Removes the helper for blank pages.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeBlankPageHelper: function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this.model.destroy();

				if ( jQuery( '#video-dialog' ).length ) {
					jQuery( '#video-dialog' ).dialog( 'destroy' );
				}

				this.remove();
			}

		} );

		jQuery( 'body' ).on( 'click', '.ui-dialog-titlebar-close', function() {
			var dialog = jQuery( this ).closest( '.ui-dialog' );
			if ( dialog.find( '#video-dialog' ).length ) {
				dialog.find( '#video-dialog iframe' )[ 0 ].contentWindow.postMessage( '{"event":"command","func":"pauseVideo","args":""}', '*' );
				dialog.hide();
			}
		} );
	} );
}( jQuery ) );
