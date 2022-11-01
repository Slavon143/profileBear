/* global FusionPageBuilderViewManager, fusionAppConfig, FusionEvents, fusionBuilderText, FusionPageBuilderApp, fusionGlobalManager */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.ElementSettingsParent = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-child-sortables' ).html() ),
			events: {
				'click .fusion-builder-add-multi-child': 'addChildElement',
				'click .fusion-builder-add-predefined-multi-child': 'addPredefinedChildElement',
				'click .fusion-builder-add-multi-gallery-images': 'openMediaUploaderForGalleryAndImageCarousel',
				'click .fusion-builder-multi-setting-remove': 'removeChildElement',
				'click .fusion-builder-multi-setting-clone': 'cloneChildElement',
				'click .fusion-builder-multi-setting-options': 'editChildElement'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				this.elementView = FusionPageBuilderViewManager.getView( this.model.get( 'cid' ) );
				this.listenTo( FusionEvents, 'fusion-child-changed', this.render );
				this.listenTo( this.model.children, 'add', this.render );
				this.listenTo( this.model.children, 'remove', this.render );
				this.listenTo( this.model.children, 'sort', this.render );
				this.settingsView = this.attributes.settingsView;
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				this.$el.html( this.template( this.model ) );
				this.sortableOptions();

				return this;
			},

			/**
			 * Make sortable.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			sortableOptions: function() {
				var self = this;

				this.$el.find( '.fusion-builder-sortable-children' ).sortable( {
					axis: 'y',
					cancel: '.fusion-builder-multi-setting-remove, .fusion-builder-multi-setting-options, .fusion-builder-multi-setting-clone',
					helper: 'clone',

					update: function( event, ui ) {
						var content   = '',
							newIndex    = ui.item.parent().children( '.ui-sortable-handle' ).index( ui.item ),
							elementView = FusionPageBuilderViewManager.getView( ui.item.data( 'cid' ) ),
							childView;

						// Update collection
						FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, self.model.get( 'cid' ) );

						self.$el.find( '.fusion-builder-sortable-children li' ).each( function() {
							childView = FusionPageBuilderViewManager.getView( jQuery( this ).data( 'cid' ) );
							content  += FusionPageBuilderApp.generateElementShortcode( childView.$el, false );
						} );

						self.elementView.setContent( content );

						// After sorting of children remove the preview block class, as the mouseleave sometimes isn't triggered.
						if ( ! jQuery( 'body' ).hasClass( 'fusion-sidebar-resizing' ) && jQuery( 'body' ).hasClass( 'fusion-preview-block' ) ) {
							jQuery( 'body' ).removeClass( 'fusion-preview-block' );
						}

						// Save history state
						FusionEvents.trigger( 'fusion-history-save-step', window.fusionBuilderText.moved + ' ' + window.fusionAllElements[ childView.model.get( 'element_type' ) ].name + ' ' + window.fusionBuilderText.element );
					}
				} );
			},

			/**
			 * Adds a child element view and renders it.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addChildElement: function( event, predefinedParams ) {

				if ( event ) {
					event.preventDefault();
				}

				this.elementView.addChildElement( null, predefinedParams );
				this.render();

				this.settingsView.optionHasChanged = true;
			},

			/**
			 * Adds a children element views and renders it, used in Bulk Add dialog.
			 *
			 * @since 3.5.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addPredefinedChildElement: function( event ) {
				var self = this,
					modalView;

				if ( event ) {
					event.preventDefault();
				}

				if ( jQuery( '.fusion-builder-settings-bulk-dialog' ).length ) {
					return;
				}

				modalView = new FusionPageBuilder.BulkAddView( {
					choices: fusionAppConfig.predefined_choices
				} );

				jQuery( modalView.render().el ).dialog( {
					title: ( fusionBuilderText.bulk_add + ' / ' + fusionBuilderText.bulk_add_predefined ),
					dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog bulk-add-dialog',
					resizable: false,
					width: 450,
					buttons: [
						{
							text: fusionBuilderText.cancel,
							click: function() {
								jQuery( this ).dialog( 'close' );
							}
						},
						{
							text: fusionBuilderText.bulk_add_insert_choices,
							click: function() {
								var choices = modalView.getChoices();

								event.preventDefault();

								_.each( choices, function( choice ) {
									var predefinedParams = {};

									if ( -1 !== choice.indexOf( '||' ) ) {

										// We have multiple params in one choice.
										_.each( choice.split( '||' ), function( param ) {
											var paramKeyValue = param.split( '|' );

											predefinedParams[ paramKeyValue[ 0 ] ] = {};
											predefinedParams[ paramKeyValue[ 0 ] ].param_name = paramKeyValue[ 0 ].trim();
											predefinedParams[ paramKeyValue[ 0 ] ].value      = paramKeyValue[ 1 ].trim();

										} );
									} else {

										// Use choice as element_content.
										predefinedParams = {
											'element_content': {
												param_name: 'element_content',
												value: choice
											}
										};
									}

									self.addChildElement( null, predefinedParams );
								} );

								jQuery( this ).dialog( 'close' );
							},
							class: 'ui-button-blue'
						}
					],
					open: function() {
						jQuery( '.fusion-builder-modal-settings-container' ).css( 'z-index', 9998 );
					},
					beforeClose: function() {
						jQuery( '.fusion-builder-modal-settings-container' ).css( 'z-index', 99999 );
						jQuery( this ).remove();
					}

				} );
			},

			/**
			 * Open a WP Media, to select multiple images, for Gallery Element and Image Carousel Element.
			 *
			 * @since 3.5.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			openMediaUploaderForGalleryAndImageCarousel: function( event ) {
				var multi_upload_button = jQuery( event.currentTarget ).closest( '.fusion-tabs' ).find( '.fusion-multiple-upload-image input' );
				var originalButtonEvent;
				if ( event ) {
					event.preventDefault();
				}

				originalButtonEvent = this.settingsView.openMultipleMedia.bind( multi_upload_button );
				originalButtonEvent( event );
			},

			/**
			 * Removes a child element view and rerenders.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeChildElement: function( event ) {
				var childCid,
					childView,
					MultiGlobalArgs;

				childCid  = jQuery( event.target ).closest( '.fusion-builder-data-cid' ).data( 'cid' );
				childView = FusionPageBuilderViewManager.getView( childCid );

				event.preventDefault();

				childView.removeElement( event );
				this.render();

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: childView.model,
					handleType: 'changeOption'
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				this.settingsView.optionHasChanged = true;
			},

			/**
			 * Clones a child element view and rerenders.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			cloneChildElement: function( event ) {
				var childCid,
					childView,
					parentView,
					MultiGlobalArgs;

				childCid   = jQuery( event.target ).closest( '.fusion-builder-data-cid' ).data( 'cid' );
				childView  = FusionPageBuilderViewManager.getView( childCid );
				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'cid' ) );

				event.preventDefault();

				childView.cloneElement();

				this.render();

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: childView.model,
					handleType: 'changeOption'
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				this.settingsView.optionHasChanged = true;
			},

			/**
			 * Edits a child element view and rerenders.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			editChildElement: function( event ) {
				var childCid  = jQuery( event.target ).closest( '.fusion-builder-data-cid' ).data( 'cid' ),
					childView = FusionPageBuilderViewManager.getView( childCid );

				event.preventDefault();

				childView.settings();
			}
		} );
	} );
}( jQuery ) );
