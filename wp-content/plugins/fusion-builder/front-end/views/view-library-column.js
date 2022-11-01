/* global FusionApp, FusionPageBuilderApp, fusionAppConfig, fusionBuilderText, FusionEvents, fusionAllElements, FusionPageBuilderViewManager */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Column Library View
		FusionPageBuilder.ColumnLibraryView = FusionPageBuilder.BaseLibraryView.extend( {

			className: 'fusion_builder_modal_settings',
			template: FusionPageBuilder.template( jQuery( '#fusion-builder-column-library-template' ).html() ),
			events: {
				'click .fusion-builder-column-layouts li': 'addColumns',
				'click .fusion_builder_custom_columns_load': 'addCustomColumn',
				'click .awb-import-options-toggle': 'toggleImportOptions',
				'click .awb-import-studio-item': 'loadStudioColumn',
				'change .awb-import-options .awb-import-style input[name="overwrite-type"]': 'triggerPreviewChanges',
				'change .awb-import-options .awb-import-inversion input[name="invert"]': 'triggerPreviewChanges'
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				FusionPageBuilderApp.showSavedElements( 'columns', this.$el.find( '#custom-columns' ) );

				FusionApp.elementSearchFilter( this.$el );

				FusionApp.dialog.dialogTabs( this.$el );

				this.loadStudio( 'columns' );

				return this;
			},

			/**
			 * Adds a custom column.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addCustomColumn: function( event ) {
				var thisModel,
					layoutID,
					title,
					self = this,
					isGlobal;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

				thisModel = this.model;
				layoutID  = jQuery( event.currentTarget ).data( 'layout_id' );
				title     = jQuery( event.currentTarget ).find( '.fusion_module_title' ).text();
				isGlobal  = jQuery( event.currentTarget ).closest( 'li' ).hasClass( 'fusion-global' );

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

					FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentRowId );

					FusionPageBuilderApp.layoutIsLoading = false;

					jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '1' );
					jQuery( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).hide();

					if ( isGlobal ) {
						setTimeout( window.fusionGlobalManager.handleGlobalsFromLibrary, 500, layoutID, FusionPageBuilderApp.parentRowId );
					}

				} )
				.always( function() {

					// Unset 'added' attribute from newly created row model
					thisModel.unset( 'added' );

					// Save history state
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_custom_column + title );

					FusionEvents.trigger( 'fusion-content-changed' );
					self.removeView();
				} );
			},

			/**
			 * Adds columns.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addColumns: function( event ) {

				var that,
					$layoutEl,
					layout,
					layoutElementsNum,
					thisView,
					defaultParams,
					params,
					value,
					rowView,
					updateContent,
					columnAttributes,
					columnCids = [],
					columnCid,
					columnView,
					atIndex,
					targetElement,
					lastCreated;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				that              = this;
				$layoutEl         = jQuery( event.target ).is( 'li' ) ? jQuery( event.target ) : jQuery( event.target ).closest( 'li' );
				layout            = $layoutEl.data( 'layout' ).split( ',' );
				layoutElementsNum = _.size( layout );
				thisView          = this.options.view,
				targetElement     = ( 'undefined' !== typeof this.options.targetElement ) ? this.options.targetElement : false;

				atIndex = FusionPageBuilderApp.getCollectionIndex( targetElement );

				_.each( layout, function( element, index ) {

					// Get default settings
					defaultParams = fusionAllElements.fusion_builder_column.params;
					params        = {};
					columnCid     = FusionPageBuilderViewManager.generateCid();
					columnCids.push( columnCid );

					// Process default parameters from shortcode
					_.each( defaultParams, function( param )  {
						value = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
						params[ param.param_name ] = value;
					} );

					params.type = element;

					updateContent    = layoutElementsNum == ( index + 1 ) ? 'true' : 'false'; // jshint ignore: line
					columnAttributes = {
						type: 'fusion_builder_column',
						element_type: 'fusion_builder_column',
						cid: columnCid,
						parent: that.model.get( 'cid' ),
						view: thisView,
						params: params,
						at_index: atIndex
					};

					// Append to last created column
					if ( lastCreated ) {
						targetElement = FusionPageBuilderViewManager.getView( lastCreated );
						targetElement = targetElement.$el;
					}

					if ( targetElement ) {
						columnAttributes.targetElement = targetElement;
						columnAttributes.targetElementPosition = 'after';
					}

					FusionPageBuilderApp.collection.add( [ columnAttributes ] );

					lastCreated = columnCid;

					if ( 'new' === atIndex ) {
						atIndex = 1;
					} else {
						atIndex++;
					}
				} );

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				// Update view column calculations.
				rowView = FusionPageBuilderViewManager.getView( FusionPageBuilderApp.parentRowId );

				if ( rowView ) {
					rowView.createVirtualRows();
					rowView.updateColumnsPreview();
				}

				FusionEvents.trigger( 'fusion-content-changed' );
				this.removeView();

				if ( event ) {

					_.each( columnCids, function( cid ) {
						columnView = FusionPageBuilderViewManager.getView( cid );
						if ( columnView ) {
							columnView.scrollHighlight( cid === columnCid );
						}
					} );

					// Save history state
					if ( true === FusionPageBuilderApp.newContainerAdded ) {
						window.fusionHistoryState = fusionBuilderText.added_section; // jshint ignore: line
						FusionPageBuilderApp.newContainerAdded = false;
					} else {
						window.fusionHistoryState = fusionBuilderText.added_columns; // jshint ignore: line
					}

					FusionEvents.trigger( 'fusion-history-save-step', window.fusionHistoryState );
				}
			},

			/**
			 * Adds studio column.
			 *
			 * @since 2.0.0
			 * @param {Object} [event]         The event.
			 * @return {void}
			 */
			loadStudioColumn: function( event ) {
				var self          = this,
					parentID      = this.model.get( 'parent' ),
					parentView    = FusionPageBuilderViewManager.getView( parentID ),
					targetColumn  = ( 'undefined' !== typeof this.options.targetElement ) ? this.options.targetElement : false,
					importOptions = this.getImportOptions( event );

				if ( false === targetColumn && parentView.$el instanceof jQuery ) {
					targetColumn = parentView.$el.find( '.fusion-builder-column' ).last();
				}

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

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
						category: 'columns',
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

									FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentRowId, false, false, targetColumn, 'after' );
									FusionPageBuilderApp.layoutIsLoading = false;
									FusionEvents.trigger( 'fusion-studio-content-imported', FusionPageBuilderApp.studio.getImportData() );

									self.studioColumnImportComplete( event, targetColumn );

									FusionPageBuilderApp.studio.resetImportData();
								},
								function() {

									self.studioImportModalView.updateStatus( fusionBuilderText.studio_importing_content_failed );

									self.studioColumnImportComplete( event, targetColumn );
								}
							);
						} else {

							FusionPageBuilderApp.shortcodesToBuilder( data.post_content, FusionPageBuilderApp.parentRowId, false, false, targetColumn, 'after' );
							FusionPageBuilderApp.layoutIsLoading = false;
							FusionEvents.trigger( 'fusion-studio-content-imported', data );

							self.studioColumnImportComplete( event, targetColumn );
						}
					}
				} );
			},

			/**
			 * Does what needs to be done when column is imported.
			 *
			 * @since 3.5
			 * @param {Object} event - The event.
			 * @param {jQuery} targetColumn - The Column after which the new column is inserted.
			 */
			studioColumnImportComplete: function( event, targetColumn ) {
				var $layout      = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					$scroll_elem = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-one-page-text-link' ),
					title        = $layout.find( '.fusion_module_title' ).text(),
					cid          = '';

				FusionPageBuilderApp.loaded = true;
				FusionEvents.trigger( 'fusion-builder-loaded' );

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				// Scroll to column container.
				if ( targetColumn.length ) {
					cid = targetColumn.next( '.fusion-builder-column' ).attr( 'data-cid' );
					if ( 'undefined' !== typeof cid && cid ) {
						$scroll_elem.attr( 'href', '#fusion-column-' + cid ).fusion_scroll_to_anchor_target( 15 );
					}
				}

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added_studio_column + title );

				FusionEvents.trigger( 'fusion-content-changed' );

				// Remove modal view.
				this.studioImportModalView.remove();

				// Close library modal.
				this.removeView();
			}
		} );
	} );
}( jQuery ) );
