/* global FusionPageBuilderApp, fusionAppConfig, FusionApp, FusionEvents, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Builder Toolbar
	FusionPageBuilder.BuilderToolbar = window.wp.Backbone.View.extend( {

		template: FusionPageBuilder.template( jQuery( '#fusion-builder-front-end-toolbar' ).html() ),
		className: 'fusion-toolbar-nav fb',
		tagName: 'ul',
		events: {
			'click .fusion-builder-clear-layout': 'clearLayout',
			'click .fusion-builder-open-library': 'openLibrary',
			'click .fusion-builder-save-template': 'openLibrary',
			'click #fusion-builder-toolbar-new-post .add-new': 'newPost',
			'click .fusion-builder-preferences': 'openPreferences',
			'click #fusion-builder-toolbar-history-menu': 'preventDefault',
			'click .fusion-preview-only-link': 'generatePreview'
		},

		initialize: function() {
			this.builderHistory = new FusionPageBuilder.BuilderHistory();
			this.listenTo( FusionEvents, 'fusion-post_title-changed', this.updatePreviewTitle );
		},

		/**
		 * Renders the view.
		 *
		 * @since 2.0.0
		 * @return {Object} this
		 */
		render: function() {
			this.$el.html( this.template() );
			this.$el.find( '.fusion-builder-history-container' ).append( this.builderHistory.render().el );
			this.delegateEvents();

			return this;
		},

		/**
		 * Make sure all the unsaved content is set like on frame refresh, then open page.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {Object} this
		 */
		generatePreview: function( event ) {
			var $element = jQuery( event.currentTarget );

			if ( 'undefined' !== typeof event ) {
				event.preventDefault();
				event.stopPropagation();
			}

			if ( $element.attr( 'data-disabled' ) ) {
				return;
			}

			$element.attr( 'data-disabled', true );

			// Avada Builder
			if ( 'undefined' !== typeof FusionPageBuilderApp ) {
				FusionPageBuilderApp.builderToShortcodes();
			}

			// Fusion Panel
			if ( this.sidebarView ) {
				this.setGoogleFonts();
			}

			FusionApp.formPost( FusionApp.getAjaxData( 'fusion_app_preview_only' ), false, '_blank' );

			$element.removeAttr( 'data-disabled' );
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

			if ( 'undefined' !== typeof event ) {
				event.preventDefault();
				event.stopPropagation();
			}

			if ( jQuery( '.fusion-builder-dialog' ).length && jQuery( '.fusion-builder-dialog' ).is( ':visible' ) ) {
				FusionApp.multipleDialogsNotice();
				return;
			}

			view = new FusionPageBuilder.LibraryView( viewSettings );
			view.render();
		},

		/**
		 * Clears the layout.
		 * Calls FusionPageBuilderApp.clearLayout
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		clearLayout: function( event ) {
			if ( event ) {
				event.preventDefault();
			}

			FusionApp.confirmationPopup( {
				title: fusionBuilderText.are_you_sure,
				content: fusionBuilderText.are_you_sure_you_want_to_delete_this_layout,
				actions: [
					{
						label: fusionBuilderText.cancel,
						classes: 'cancel',
						callback: function() {
							FusionApp.confirmationPopup( {
								action: 'hide'
							} );
						}
					},
					{
						label: fusionBuilderText.remove,
						classes: 'delete-layout',
						callback: function() {

							// Close dialogs.
							if ( jQuery( '.ui-dialog-content' ).length ) {
								jQuery( '.ui-dialog-content' ).dialog( 'close' );
							}

							FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.layout_cleared );
							FusionPageBuilderApp.clearLayout( event );

							FusionApp.confirmationPopup( {
								action: 'hide'
							} );
						}
					}
				]
			} );
		},

		/**
		 * Create a new draft of specific post type.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		newPost: function( event ) {
			var postType = jQuery( event.currentTarget ).data( 'post-type' );

			if ( event ) {
				event.preventDefault();
			}

			jQuery.ajax( {
				type: 'POST',
				url: fusionAppConfig.ajaxurl,
				dataType: 'JSON',
				data: {
					action: 'fusion_create_post',
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					post_type: postType
				}
			} )
			.done( function( response ) {
				FusionApp.checkLink( event, response.permalink );
			} );
		},

		/**
		 * Renders the FusionPageBuilder.PreferencesView view.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		openPreferences: function( event ) {
			var view;

			if ( 'undefined' !== typeof event ) {
				event.preventDefault();
				event.stopPropagation();
			}

			if ( jQuery( '.fusion-builder-dialog' ).length && jQuery( '.fusion-builder-dialog' ).is( ':visible' ) ) {
				FusionApp.multipleDialogsNotice();
				return;
			}

			view = new FusionPageBuilder.PreferencesView();
			view.render();
		},

		/**
		 * Prevents default action.
		 *
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		preventDefault: function( event ) {
			event.preventDefault();
		},

		/**
		 * Updates the text for the title of the page.
		 *
		 * @return {void}
		 */
		updatePreviewTitle: function() {
			this.$el.find( '.fusion-preview-only-link strong' ).html( FusionApp.getPost( 'post_title' ) );
		}
	} );
}( jQuery ) );
