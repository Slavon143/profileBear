/* global FusionApp, FusionPageBuilderApp, fusionAppConfig, fusionGlobalManager, fusionBuilderText, FusionEvents, fusionAllElements, FusionPageBuilderViewManager */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Elements View
		FusionPageBuilder.ElementLibraryView = FusionPageBuilder.BaseLibraryView.extend( {

			className: 'fusion_builder_modal_settings',
			events: {
				'click .fusion-builder-all-modules .fusion-builder-element:not(.fusion-builder-element-generator,.fusion-builder-disabled-element)': 'addModule',
				'click .fusion_builder_custom_elements_load': 'addCustomModule',
				'click .fusion-builder-column-layouts li': 'addNestedColumns',
				'click .awb-import-options-toggle': 'toggleImportOptions',
				'click .awb-import-studio-item': 'loadStudioElement',
				'change .awb-import-options .awb-import-style input[name="overwrite-type"]': 'triggerPreviewChanges',
				'change .awb-import-options .awb-import-inversion input[name="invert"]': 'triggerPreviewChanges'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function( attributes ) {
				this.options = attributes;

				if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
					this.template = FusionPageBuilder.template( jQuery( '#fusion-builder-generator-modules-template' ).html() );
				} else if ( 'fusion_builder_column_inner' === this.model.get( 'element_type' ) ) {
					this.template = FusionPageBuilder.template( jQuery( '#fusion-builder-nested-column-modules-template' ).html() );
				} else {
					this.template = FusionPageBuilder.template( jQuery( '#fusion-builder-modules-template' ).html() );
				}
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				this.$el.html( this.template( FusionPageBuilderApp.elements ) );

				// Load saved elements
				FusionPageBuilderApp.showSavedElements( 'elements', this.$el.find( '#custom-elements' ) );

				FusionApp.elementSearchFilter( this.$el );

				FusionApp.dialog.dialogTabs( this.$el );

				this.loadStudio( 'elements' );

				return this;
			},

			/**
			 * Adds a custom element and triggers an ajax call.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addCustomModule: function( event ) {
				var layoutID,
					title,
					self = this,
					isGlobal,
					targetElement;

				if ( event ) {
					event.preventDefault();
				}

				if ( true === FusionApp.layoutIsLoading ) {
					return;
				}

				FusionApp.layoutIsLoading = true;

				layoutID      = jQuery( event.currentTarget ).closest( 'li' ).data( 'layout_id' );
				title         = jQuery( event.currentTarget ).find( '.fusion_module_title' ).text();
				isGlobal      = jQuery( event.currentTarget ).closest( 'li' ).hasClass( 'fusion-global' );

				if ( 'undefined' !== typeof this.options.targetElement ) {
					targetElement = this.options.targetElement;
				}

				jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '0' );
				jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).show();

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						fusion_is_global: isGlobal,
						fusion_layout_id: layoutID
					}
				} )
				.done( function( data ) {
					var dataObj = JSON.parse( data );

					if ( 'undefined' !== typeof targetElement ) {
						FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentColumnId, false, false, targetElement, 'after' );
					} else {
						FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentColumnId );
					}

					FusionApp.layoutIsLoading = false;

					jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '1' );
					jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).hide();

					if ( isGlobal ) {
						setTimeout( fusionGlobalManager.handleGlobalsFromLibrary, 500, layoutID, FusionPageBuilderApp.parentColumnId );
					}
				} )
				.always( function() {
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_custom_element + title );

					FusionEvents.trigger( 'fusion-content-changed' );
					self.removeView();
				} );
			},

			/**
			 * Adds an element.
			 *
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addModule: function( event ) {
				var $thisEl,
					label,
					params,
					multi,
					type,
					name,
					defaultParams,
					allowGenerator,
					currentModel,
					childUi,
					elementParams,
					MultiGlobalArgs;

				if ( event ) {
					event.preventDefault();
				}

				$thisEl = jQuery( event.currentTarget );
				label   = $thisEl.find( '.fusion_module_label' ).text();

				if ( label in fusionAllElements ) {

					defaultParams  = fusionAllElements[ label ].params;
					multi          = fusionAllElements[ label ].multi;
					type           = fusionAllElements[ label ].shortcode;
					name           = fusionAllElements[ label ].name;
					allowGenerator = fusionAllElements[ label ].allow_generator;
					childUi        = fusionAllElements[ label ].child_ui;

				} else {
					defaultParams = '';
					multi   = '';
					type   = '';
					allowGenerator = '';
				}

				params = {};

				// Process default parameters from shortcode
				_.each( defaultParams, function( param )  {
					params[ param.param_name ] = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
				} );

				elementParams = {
					type: 'element',
					added: 'manually',
					cid: FusionPageBuilderViewManager.generateCid(),
					element_type: type,
					params: params,
					parent: this.attributes[ 'data-parent_cid' ],
					view: this.options.view,
					allow_generator: allowGenerator,
					inline_editor: FusionPageBuilderApp.inlineEditorHelpers.inlineEditorAllowed( type ),
					multi: multi,
					child_ui: childUi,
					at_index: FusionPageBuilderApp.getCollectionIndex( this.options.targetElement )
				};

				if ( 'undefined' !== typeof this.options.targetElement ) {
					elementParams.targetElement = this.options.targetElement;
					elementParams.targetElementPosition = 'after';
				}

				currentModel = this.collection.add( [ elementParams ] );

				this.remove();

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: currentModel[ 0 ],
					handleType: 'save',
					attributes: currentModel[ 0 ].attributes
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added + ' ' + name + ' ' + fusionBuilderText.element );

				FusionEvents.trigger( 'fusion-content-changed' );

			},

			/**
			 * Adds nested columns.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addNestedColumns: function( event ) {
				var moduleID,
					$layoutEl,
					layout,
					layoutElementsNum,
					innerRow,
					elementParams;

				if ( event ) {
					event.preventDefault();
				}

				moduleID = FusionPageBuilderViewManager.generateCid();

				elementParams = {
					type: 'fusion_builder_row_inner',
					element_type: 'fusion_builder_row_inner',
					added: 'manually',
					cid: moduleID,
					parent: this.model.get( 'cid' ),
					view: this,
					at_index: FusionPageBuilderApp.getCollectionIndex( this.options.targetElement )
				};

				if ( 'undefined' !== typeof this.options.targetElement ) {
					elementParams.targetElement = this.options.targetElement;
					elementParams.targetElementPosition = 'after';
				}

				this.collection.add( [ elementParams ] );

				innerRow = FusionPageBuilderViewManager.getView( moduleID );

				FusionPageBuilderApp.activeModal = 'column';

				$layoutEl         = jQuery( event.target ).is( 'li' ) ? jQuery( event.target ) : jQuery( event.target ).closest( 'li' );
				layout            = $layoutEl.data( 'layout' ).split( ',' );
				layoutElementsNum = _.size( layout );

				_.each( layout, function( element, index ) {
					var updateContent    = layoutElementsNum == ( index + 1 ) ? 'true' : 'false'; // jshint ignore: line
					innerRow.addNestedColumn( element, false );
				} );

				this.remove();

				FusionEvents.trigger( 'fusion-content-changed' );
				innerRow.setRowData();

				// Used to ensure if cancel that the columns are part of initial content.
				innerRow.updateSavedContent();

				if ( event ) {
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_nested_columns );
					FusionEvents.trigger( 'fusion-content-changed' );
				}

				setTimeout( function() {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-content-changed', innerRow.$el.closest( '.fusion-builder-column-outer' ).data( 'cid' ) );
				}, 300 );
			},

			/**
			 * Adds studio element.
			 *
			 * @since 2.0.0
			 * @param {Object} [event]         The event.
			 * @return {void}
			 */
			loadStudioElement: function( event ) {
				var self          = this,
					importOptions = this.getImportOptions( event ),
					targetElement;

				if ( event ) {
					event.preventDefault();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

				if ( 'undefined' !== typeof this.options.targetElement ) {
					targetElement = this.options.targetElement;
				}

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						fusion_is_global: false,
						fusion_layout_id: importOptions.layoutID,
						fusion_studio: true,
						overWriteType: importOptions.overWriteType,
						shouldInvert: importOptions.shouldInvert,
						imagesImport: importOptions.imagesImport,
						category: 'elements',
						post_id: FusionApp.getPost( 'post_id' )
					},

					beforeSend: function() {
						self.beforeStudioItemImport();
					},

					success: function( data ) {
						var i,
							promises = [],
							dfd      = jQuery.Deferred(),  // Master deferred.
							dfdNext  = dfd; // Next deferred in the chain.

						dfd.resolve();

						// Reset array.
						self.mediaImportKeys = [];

						// We have the content, let's check for assets.
						// Filter out empty properties (now those are empty arrays).
						if ( 'object' === typeof data.avada_media ) {
							Object.keys( data.avada_media ).forEach( function( key ) {
								// We expect and object.
								if ( 'object' === typeof data.avada_media[ key ] && ! Array.isArray( data.avada_media[ key ] ) ) {
									self.mediaImportKeys.push( key );
								}
							} );
						}

						// Import studio media if needed.
						if ( 0 < self.mediaImportKeys.length ) {

							// Set first AJAX response as initial data.
							FusionPageBuilderApp.studio.setImportData( data );

							for ( i = 0; i < self.mediaImportKeys.length; i++ ) {

								// IIFE to freeze the value of i.
								( function( k ) { // eslint-disable-line no-loop-func

									dfdNext = dfdNext.then( function() {
										return self.importStudioMedia( FusionPageBuilderApp.studio.getImportData(), self.mediaImportKeys[ k ], importOptions );
									} );

									promises.push( dfdNext );
								}( i ) );

							}

							jQuery.when.apply( null, promises ).then(
								function() {

									/*
									var lastAjaxResponse;

									if ( 1 === promises.length ) {
										lastAjaxResponse = arguments[ 0 ];
									} else {
										lastAjaxResponse = arguments[ promises.length - 1 ][ 0 ];
									}
									*/

									if ( 'undefined' !== typeof targetElement ) {
										FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentColumnId, false, false, targetElement, 'after' );
									} else {
										FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentColumnId );
									}

									FusionPageBuilderApp.layoutIsLoading = false;
									FusionEvents.trigger( 'fusion-studio-content-imported', FusionPageBuilderApp.studio.getImportData() );

									self.studioElementImportComplete( event );

									// Reset import data.
									FusionPageBuilderApp.studio.resetImportData();
								},
								function() {

									self.studioImportModalView.updateStatus( fusionBuilderText.studio_importing_content_failed );

									self.studioElementImportComplete( event );

									// Reset import data.
									FusionPageBuilderApp.studio.resetImportData();
								}
							);
						} else {

							if ( 'undefined' !== typeof targetElement ) {
								FusionPageBuilderApp.shortcodesToBuilder( data.post_content, FusionPageBuilderApp.parentColumnId, false, false, targetElement, 'after' );
							} else {
								FusionPageBuilderApp.shortcodesToBuilder( data.post_content, FusionPageBuilderApp.parentColumnId );
							}

							FusionPageBuilderApp.layoutIsLoading = false;
							FusionEvents.trigger( 'fusion-studio-content-imported', data );

							self.studioElementImportComplete( event );
						}
					}
				} );
			},

			/**
			 * Does what needs to be done when element is imported.
			 *
			 * @since 3.5
			 * @param {Object} event - The event.
			 */
			studioElementImportComplete: function( event ) {
				var $layout           = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					title             = $layout.find( '.fusion_module_title' ).text();

				FusionPageBuilderApp.loaded = true;
				FusionEvents.trigger( 'fusion-builder-loaded' );

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_studio_element + title );

				FusionEvents.trigger( 'fusion-content-changed' );

				// Remove modal view.
				this.studioImportModalView.remove();

				// Close library modal.
				this.removeView();
			}
		} );
	} );
}( jQuery ) );
