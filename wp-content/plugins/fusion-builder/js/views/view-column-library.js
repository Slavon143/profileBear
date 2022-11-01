/* global FusionPageBuilderEvents, fusionBuilderConfig, FusionPageBuilderApp, fusionHistoryManager, fusionBuilderText, fusionAllElements, FusionPageBuilderViewManager */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Column Library View
		FusionPageBuilder.ColumnLibraryView = FusionPageBuilder.BaseLibraryView.extend( {

			className: 'fusion_builder_modal_settings',

			template: FusionPageBuilder.template( $( '#fusion-builder-column-library-template' ).html() ),

			events: {
				'click .fusion-builder-column-layouts li': 'addColumns',
				'click .fusion_builder_custom_columns_load': 'addCustomColumn',
				'click .fusion_builder_custom_sections_load': 'addCustomSection',
				'click .fusion-special-item': 'addSpecialItem',
				'click .awb-import-options-toggle': 'toggleImportOptions',
				'click #fusion-builder-sections-studio .awb-import-studio-item': 'loadStudioContainer',
				'click #fusion-builder-columns-studio .awb-import-studio-item': 'loadStudioColumn'
			},

			render: function() {
				var self = this;

				this.$el.html( this.template( this.model.toJSON() ) );

				// Show saved custom columns
				FusionPageBuilderApp.showSavedElements( 'columns', this.$el.find( '#custom-columns' ) );

				// Show saved custom sections
				if ( 'container' === FusionPageBuilderApp.activeModal ) {
					FusionPageBuilderApp.showSavedElements( 'sections', this.$el.find( '#custom-sections' ) );
					this.loadStudio( 'sections' );
				} else {
					this.loadStudio( 'columns' );
				}

				setTimeout( function() {
					self.$el.find( '.fusion-elements-filter' ).focus();
				}, 50 );

				return this;
			},

			loadStudioColumn: function( event ) {
				var layoutID,
					self          = this,
					$layout       = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					importOptions = FusionPageBuilderApp.studio.getImportOptions( event );

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

				layoutID  = $layout.data( 'layout-id' );

				jQuery.ajax( {
					type: 'POST',
					url: FusionPageBuilderApp.ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce,
						fusion_is_global: false,
						fusion_layout_id: layoutID,
						overWriteType: importOptions.overWriteType,
						shouldInvert: importOptions.shouldInvert,
						imagesImport: importOptions.imagesImport,
						fusion_studio: true,
						category: 'columns',
						post_id: fusionBuilderConfig.post_id
					},

					beforeSend: function() {
						jQuery( '#fusion-builder-columns-studio' ).find( '.fusion-loader' ).show();
						jQuery( '#fusion-builder-columns-studio' ).find( '.studio-wrapper' ).addClass( 'loading' );

						jQuery( '.fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.studio_importing_content );
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

									FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentRowId );
									FusionPageBuilderApp.layoutIsLoading = false;
									FusionPageBuilderEvents.trigger( 'fusion-studio-content-imported', FusionPageBuilderApp.studio.getImportData() );

									self.studioColumnImportComplete( event );

									FusionPageBuilderApp.studio.resetImportData();
								},
								function() {

									jQuery( '.fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.studio_importing_content_failed );

									self.studioColumnImportComplete( event );

									FusionPageBuilderApp.studio.resetImportData();
								}
							);
						} else {

							FusionPageBuilderApp.shortcodesToBuilder( data.post_content, FusionPageBuilderApp.parentRowId );
							FusionPageBuilderApp.layoutIsLoading = false;
							FusionPageBuilderEvents.trigger( 'fusion-studio-content-imported', data );

							self.studioColumnImportComplete( event );
						}
					}
				} );
			},

			/**
			 * Does what needs to be done when column is imported.
			 *
			 * @since 3.5
			 * @param {Object} event - The event.
			 */
			studioColumnImportComplete: function( event ) {
				var $layout           = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					title             = $layout.find( '.fusion_module_title' ).text();

				jQuery( '#fusion-builder-columns-studio' ).find( '.fusion-loader' ).hide();
				jQuery( '#fusion-builder-columns-studio' ).find( '.studio-wrapper' ).removeClass( 'loading' );

				FusionPageBuilderApp.loaded = true;

				// Save history state
				fusionHistoryManager.turnOnTracking();
				window.fusionHistoryState = fusionBuilderText.added_studio_column + title;

				FusionPageBuilderEvents.trigger( 'fusion-columns-added' );
				FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );


				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );
			},

			loadStudioContainer: function( event ) {
				var self              = this,
					parentID          = this.model.get( 'parent' ),
					parentView        = FusionPageBuilderViewManager.getView( parentID ),
					$layout           = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					importOptions     = FusionPageBuilderApp.studio.getImportOptions( event ),
					layoutID,
					targetContainer;

				targetContainer = parentView.$el.prev( '.fusion_builder_container' );
				FusionPageBuilderApp.targetContainerCID = targetContainer.find( '.fusion-builder-data-cid' ).data( 'cid' );

				if ( event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof parentView ) {
					parentView.removeContainer();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}

				FusionPageBuilderApp.layoutIsLoading = true;

				layoutID = $layout.data( 'layout-id' );

				jQuery.ajax( {
					type: 'POST',
					url: FusionPageBuilderApp.ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce,
						fusion_is_global: false,
						fusion_layout_id: layoutID,
						overWriteType: importOptions.overWriteType,
						shouldInvert: importOptions.shouldInvert,
						imagesImport: importOptions.imagesImport,
						fusion_studio: true,
						category: 'sections',
						post_id: fusionBuilderConfig.post_id
					},

					beforeSend: function() {
						jQuery( '#fusion-builder-sections-studio' ).find( '.fusion-loader' ).show();
						jQuery( '#fusion-builder-sections-studio' ).find( '.studio-wrapper' ).addClass( 'loading' );

						jQuery( '.fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.studio_importing_content );
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

									FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentRowId );
									FusionPageBuilderApp.layoutIsLoading = false;
									FusionPageBuilderEvents.trigger( 'fusion-studio-content-imported', FusionPageBuilderApp.studio.getImportData() );

									self.studioContainerImportComplete( event );

									FusionPageBuilderApp.studio.resetImportData();
								},
								function() {

									jQuery( '.fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.studio_importing_content_failed );

									self.studioContainerImportComplete( event );

									FusionPageBuilderApp.studio.resetImportData();
								}
							);
						} else {

							FusionPageBuilderApp.shortcodesToBuilder( data.post_content, FusionPageBuilderApp.parentRowId );
							FusionPageBuilderApp.layoutIsLoading = false;
							FusionPageBuilderEvents.trigger( 'fusion-studio-content-imported', data );

							self.studioContainerImportComplete( event );
						}
					}
				} );
			},

			/**
			 * Does what needs to be done when container is imported.
			 *
			 * @since 3.5
			 * @param {Object} event - The event.
			 */
			studioContainerImportComplete: function( event ) {
				var $layout = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					title   = $layout.find( '.fusion_module_title' ).text();

				jQuery( '#fusion-builder-sections-studio' ).find( '.fusion-loader' ).hide();
				jQuery( '#fusion-builder-sections-studio' ).find( '.studio-wrapper' ).removeClass( 'loading' );

				FusionPageBuilderApp.loaded = true;

				// Save history state
				fusionHistoryManager.turnOnTracking();
				window.fusionHistoryState = fusionBuilderText.added_studio_section + title;

				FusionPageBuilderEvents.trigger( 'fusion-columns-added' );
				FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );

				// Unset 'added' attribute from newly created section model
				this.model.unset( 'added' );

			},

			addCustomColumn: function( event ) {
				var thisModel,
					layoutID,
					isGlobal,
					title;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}

				FusionPageBuilderApp.layoutIsLoading = true;

				thisModel = this.model;
				layoutID  = $( event.currentTarget ).data( 'layout_id' );
				title     = $( event.currentTarget ).find( '.fusion_module_title' ).text();
				isGlobal  = $( event.currentTarget ).closest( 'li' ).hasClass( 'fusion-global' );

				$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '0' );
				$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).show();

				$.ajax( {
					type: 'POST',
					url: FusionPageBuilderApp.ajaxurl,
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce,
						fusion_is_global: isGlobal,
						fusion_layout_id: layoutID
					}
				} )
				.done( function( data ) {
					var dataObj = JSON.parse( data );

					FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentRowId );

					FusionPageBuilderApp.layoutIsLoading = false;

					$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '1' );
					$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).hide();

					// Check for globals.
					setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, FusionPageBuilderApp.parentRowId );

				} )
				.always( function() {

					// Unset 'added' attribute from newly created row model
					thisModel.unset( 'added' );

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.added_custom_column + title;

					FusionPageBuilderEvents.trigger( 'fusion-columns-added' );
					FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );
				} );
			},

			addColumns: function( event ) {

				var that,
					$layoutEl,
					layout,
					layoutElementsNum,
					thisView,
					defaultParams,
					value;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.activeModal = 'column';

				that              = this;
				$layoutEl         = $( event.target ).is( 'li' ) ? $( event.target ) : $( event.target ).closest( 'li' );
				layout            = $layoutEl.data( 'layout' ).split( ',' );
				layoutElementsNum = _.size( layout );
				thisView          = this.options.view;

				// Get default settings
				defaultParams = fusionAllElements.fusion_builder_column.params;

				_.each( layout, function( element, index ) {
					var params = {},
						updateContent,
						columnAttributes;

					// Process default parameters from shortcode
					_.each( defaultParams, function( param )  {
						if ( _.isObject( param.value ) ) {
							value = param[ 'default' ];
						} else {
							value = param.value;
						}
						params[ param.param_name ] = value;
					} );

					updateContent    = layoutElementsNum == ( index + 1 ) ? 'true' : 'false'; // jshint ignore:line
					columnAttributes = {
						type: 'fusion_builder_column',
						element_type: 'fusion_builder_column',
						cid: FusionPageBuilderViewManager.generateCid(),
						parent: that.model.get( 'cid' ),
						layout: element,
						view: thisView,
						params: params
					};

					that.collection.add( [ columnAttributes ] );

				} );

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				FusionPageBuilderEvents.trigger( 'fusion-columns-added' );

				if ( event ) {

					// Save history state
					fusionHistoryManager.turnOnTracking();

					if ( true === FusionPageBuilderApp.newContainerAdded ) {
						window.fusionHistoryState = fusionBuilderText.added_section;
						FusionPageBuilderApp.newContainerAdded = false;
					} else {
						window.fusionHistoryState = fusionBuilderText.added_columns;
					}

					FusionPageBuilderEvents.trigger( 'fusion-element-added' );
				}

			},

			addCustomSection: function( event ) {
				var thisModel  = this.model,
					parentID   = this.model.get( 'parent' ),
					parentView = FusionPageBuilderViewManager.getView( parentID ),
					layoutID,
					isGlobal,
					title,
					targetContainer;

				targetContainer = parentView.$el.prev( '.fusion_builder_container' );
				FusionPageBuilderApp.targetContainerCID = targetContainer.find( '.fusion-builder-data-cid' ).data( 'cid' );

				if ( event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof parentView ) {
					parentView.removeContainer();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}

				FusionPageBuilderApp.layoutIsLoading = true;

				layoutID = $( event.currentTarget ).data( 'layout_id' );
				title    = $( event.currentTarget ).find( '.fusion_module_title' ).text();
				isGlobal = $( event.currentTarget ).closest( 'li' ).hasClass( 'fusion-global' );

				$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '0' );
				$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).show();

				$.ajax( {
					type: 'POST',
					url: FusionPageBuilderApp.ajaxurl,
					data: {
						action: 'fusion_builder_load_layout',
						fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce,
						fusion_is_global: isGlobal,
						fusion_layout_id: layoutID
					}
				} )
				.done( function( data ) {
					var dataObj = JSON.parse( data );

					FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentRowId );

					FusionPageBuilderApp.layoutIsLoading = false;

					$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '1' );
					$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).hide();

				} )
				.always( function() {

					// Unset 'added' attribute from newly created section model
					thisModel.unset( 'added' );

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.added_custom_section + title;

					FusionPageBuilderEvents.trigger( 'fusion-columns-added' );
					FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );
				} );
			},

			addSpecialItem: function( event ) {
				var parentID   = this.model.get( 'parent' ),
					parentView = FusionPageBuilderViewManager.getView( parentID ),
					targetContainer,
					moduleID,
					params = {};

				if ( event ) {
					event.preventDefault();
				}

				targetContainer = parentView.$el.prev( '.fusion_builder_container' );
				FusionPageBuilderApp.targetContainerCID = targetContainer.find( '.fusion-builder-data-cid' ).data( 'cid' );
				moduleID = FusionPageBuilderViewManager.generateCid();

				this.collection.add( [
					{
						type: jQuery( event.currentTarget ).data( 'type' ),
						added: 'manually',
						module_type: jQuery( event.currentTarget ).data( 'type' ),
						cid: moduleID,
						params: params,
						view: parentView,
						appendAfter: targetContainer,
						created: 'auto'
					}
				] );

				if ( 'undefined' !== typeof parentView ) {
					FusionPageBuilderApp.targetContainerCID = '';
					parentView.removeContainer();
				}

				// Save history state
				fusionHistoryManager.turnOnTracking();
				window.fusionHistoryState = fusionBuilderText.added_special_item + jQuery( event.currentTarget ).find( '.fusion_module_title' ).text();

				FusionPageBuilderEvents.trigger( 'fusion-columns-added' );
				FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );

			}

		} );

	} );

}( jQuery ) );
