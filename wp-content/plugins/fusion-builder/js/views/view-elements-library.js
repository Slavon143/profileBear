/* global FusionPageBuilderEvents, fusionBuilderConfig, FusionPageBuilderViewManager, FusionPageBuilderApp, fusionHistoryManager, fusionBuilderText, fusionAllElements */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Builder Elements View
		FusionPageBuilder.ElementLibraryView = FusionPageBuilder.BaseLibraryView.extend( {

			className: 'fusion_builder_modal_settings',

			template: FusionPageBuilder.template( $( '#fusion-builder-modules-template' ).html() ),

			events: {
				'click .fusion-builder-all-modules .fusion-builder-element:not(.fusion-builder-element-generator,.fusion-builder-disabled-element)': 'addModule',
				'click .fusion_builder_custom_elements_load': 'addCustomModule',
				'click .fusion-builder-column-layouts li': 'addNestedColumns',
				'click .awb-import-options-toggle': 'toggleImportOptions',
				'click .awb-import-studio-item': 'loadStudioElement'
			},

			render: function() {
				var self = this;

				this.$el.html( this.template( FusionPageBuilderViewManager.toJSON() ) );

				// Load saved elements
				FusionPageBuilderApp.showSavedElements( 'elements', this.$el.find( '#custom-elements' ) );

				// If adding element to nested column
				if ( 'true' === FusionPageBuilderApp.innerColumn ) {
					this.$el.addClass( 'fusion-add-to-nested' );
				}

				// Load studio.
				this.loadStudio( 'elements' );

				setTimeout( function() {
					self.$el.find( '.fusion-elements-filter' ).focus();
				}, 50 );

				return this;
			},

			addCustomModule: function( event ) {
				var layoutID,
					title,
					isGlobal;

				if ( event ) {
					event.preventDefault();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}

				FusionPageBuilderApp.layoutIsLoading = true;

				layoutID = $( event.currentTarget ).closest( 'li' ).data( 'layout_id' );
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

					FusionPageBuilderApp.shortcodesToBuilder( dataObj.post_content, FusionPageBuilderApp.parentColumnId );
					FusionPageBuilderApp.layoutIsLoading = false;

					$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).css( 'opacity', '1' );
					$( event.currentTarget ).parent( '.fusion-builder-all-modules' ).prev( '#fusion-loader' ).hide();

					// Check for globals.
					setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, FusionPageBuilderApp.parentColumnId );
				} )
				.always( function() {

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.added_custom_element + title;

					FusionPageBuilderEvents.trigger( 'fusion-element-added' );
				} );
			},

			loadStudioElement: function( event ) {
				var layoutID,
					self           = this,
					$layout        = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					importOptions  = FusionPageBuilderApp.studio.getImportOptions( event ),
					targetElement;

				if ( event ) {
					event.preventDefault();
				}

				if ( true === FusionPageBuilderApp.layoutIsLoading ) {
					return;
				}
				FusionPageBuilderApp.layoutIsLoading = true;

				layoutID  = $layout.data( 'layout-id' );

				if ( 'undefined' !== typeof this.options.targetElement ) {
					targetElement = this.options.targetElement;
				}

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
						category: 'elements',
						post_id: fusionBuilderConfig.post_id
					},

					beforeSend: function() {
						jQuery( '#fusion-builder-elements-studio' ).find( '.fusion-loader' ).show();
						jQuery( '#fusion-builder-elements-studio' ).find( '.studio-wrapper' ).addClass( 'loading' );

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

									if ( 'undefined' !== typeof targetElement ) {
										FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentColumnId, false, false, targetElement, 'after' );
									} else {
										FusionPageBuilderApp.shortcodesToBuilder( FusionPageBuilderApp.studio.getImportData().post_content, FusionPageBuilderApp.parentColumnId );
									}

									FusionPageBuilderApp.layoutIsLoading = false;
									FusionPageBuilderEvents.trigger( 'fusion-studio-content-imported', FusionPageBuilderApp.studio.getImportData() );

									self.studioElementImportComplete( event );

									FusionPageBuilderApp.studio.resetImportData();
								},
								function() {

									jQuery( '.fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.studio_importing_content_failed );

									self.studioElementImportComplete( event );

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
							FusionPageBuilderEvents.trigger( 'fusion-studio-content-imported', data );

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
				var $layout  = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
					title    = $layout.find( '.fusion_module_title' ).text();

				jQuery( '#fusion-builder-elements-studio' ).find( '.fusion-loader' ).hide();
				jQuery( '#fusion-builder-elements-studio' ).find( '.studio-wrapper' ).removeClass( 'loading' );
				FusionPageBuilderApp.loaded = true;

				// Unset 'added' attribute from newly created row model
				this.model.unset( 'added' );

				// Save history state
				fusionHistoryManager.turnOnTracking();
				window.fusionHistoryState = fusionBuilderText.added_studio_element + title;

				FusionPageBuilderEvents.trigger( 'fusion-element-added' );
			},

			addModule: function( event ) {
				var $thisEl,
					label,
					params,
					defaultParams,
					multi,
					type,
					name,
					allowGenerator;

				if ( event ) {
					event.preventDefault();
				}

				$thisEl = $( event.currentTarget );
				label   = $thisEl.find( '.fusion_module_label' ).text();

				if ( label in fusionAllElements ) {

					params = fusionAllElements[ label ].params;
					multi  = fusionAllElements[ label ].multi;
					type   = fusionAllElements[ label ].shortcode;
					name   = fusionAllElements[ label ].name;
					allowGenerator = fusionAllElements[ label ].allow_generator;

					// Get default options
					defaultParams = fusionAllElements[ label ].params;
					params = {};

					// Process default parameters from shortcode
					_.each( defaultParams, function( param )  {
						params[ param.param_name ] = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
					} );

				} else {
					params = '';
					multi  = '';
					type   = '';
					allowGenerator = '';
				}

				if ( event ) {
					window.fusionHistoryState = fusionBuilderText.added + ' ' + name + ' ' + fusionBuilderText.element;
				}

				this.collection.add( [
					{
						type: 'element',
						added: 'manually',
						cid: FusionPageBuilderViewManager.generateCid(),
						element_type: type,
						params: params,
						parent: this.attributes[ 'data-parent_cid' ],
						view: this.options.view,
						allow_generator: allowGenerator,
						multi: multi
					}
				] );

				this.remove();

				FusionPageBuilderEvents.trigger( 'fusion-element-added' );

			},

			addNestedColumns: function( event, appendAfter ) {
				var moduleID,
					that,
					$layoutEl,
					layout,
					layoutElementsNum,
					thisView,
					defaultParams,
					params,
					value;

				if ( event ) {
					event.preventDefault();
				}

				moduleID = FusionPageBuilderViewManager.generateCid();

				this.collection.add( [
					{
						type: 'fusion_builder_row_inner',
						element_type: 'fusion_builder_row_inner',
						cid: moduleID,
						parent: this.model.get( 'cid' ),
						view: this,
						appendAfter: appendAfter
					}
				] );

				FusionPageBuilderApp.activeModal = 'column';

				that              = this;
				$layoutEl         = $( event.target ).is( 'li' ) ? $( event.target ) : $( event.target ).closest( 'li' );
				layout            = $layoutEl.data( 'layout' ).split( ',' );
				layoutElementsNum = _.size( layout );
				thisView          = this.options.view;

				// Get default options
				defaultParams = fusionAllElements.fusion_builder_column_inner.params;
				params = {};

				// Process default parameters from shortcode
				_.each( defaultParams, function( param )  {
					if ( _.isObject( param.value ) ) {
						value = param[ 'default' ];
					} else {
						value = param.value;
					}
					params[ param.param_name ] = value;
				} );

				_.each( layout, function( element, index ) { // jshint ignore:line
					var columnAttributes = {
						type: 'fusion_builder_column_inner',
						element_type: 'fusion_builder_column_inner',
						cid: FusionPageBuilderViewManager.generateCid(),
						parent: moduleID,
						layout: element,
						view: thisView,
						params: params
					};

					that.collection.add( [ columnAttributes ] );

				} );

				this.remove();

				FusionPageBuilderEvents.trigger( 'fusion-columns-added' );

				if ( event ) {

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.added_nested_columns;

					FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );
				}
			}
		} );
	} );
}( jQuery ) );
