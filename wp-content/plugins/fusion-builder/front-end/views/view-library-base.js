/* global FusionPageBuilderEvents, fusionBuilderText, FusionPageBuilderApp, fusionAppConfig, FusionApp, FusionEvents, ajaxurl */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Base Library View
		FusionPageBuilder.BaseLibraryView = window.wp.Backbone.View.extend( {

			/**
			 * Array of studio content's media.
			 */
			mediaImportKeys: [],

			/**
			 * Studio Import Modal View.
			 */
			studioImportModalView: null,

			/**
			 * Demo Import Modal View.
			 */
			demoImportModalView: null,

			initialize: function( attributes ) {
				this.options = attributes;

				this.listenTo( FusionEvents, 'fusion-studio-content-imported', this.loadBuilderAssets );
				this.listenTo( FusionEvents, 'awb-studio-import-modal-closed', this.removeView );

			},

			triggerPreviewChanges: function( event ) {
				var $wrapper =  jQuery( event.currentTarget ).closest( '.studio-wrapper' ),
					changeCounter = 0,
					overWriteType,
					shouldInvert,
					options;

				// Early exit if not in preview mode.
				if ( ! $wrapper.hasClass( 'fusion-studio-preview-active' ) ) {
					return;
				}

				overWriteType = $wrapper.find( 'input[name="overwrite-type"]:checked' ).val();
				shouldInvert  = $wrapper.find( 'input[name="invert"]:checked' ).val();

				options = {
					'overWriteType': overWriteType,
					'shouldInvert': shouldInvert
				};

				jQuery.each( options, function( index, value ) {

					if ( options[ index ] !== FusionApp.preferencesData[ index ] ) {
						FusionApp.preferencesData[ index ] = options[ index ];
						changeCounter++;
					}
				} );

				if ( 0 < changeCounter ) {
					FusionEvents.trigger( 'awb-update-studio-item-preview' );
				}
			},

			removeView: function() {
				FusionPageBuilderApp.activeModal = '';
				this.remove();
			},

			loadStudio: function( type ) {
				var self              = this,
					$container        = 'fusion_template' === type ? jQuery( '#fusion-builder-' + type + '-studio' ) : this.$el.find( '#fusion-builder-' + type + '-studio' ),
					$layoutsContainer = $container.find( '.studio-imports' ),
					$sidebar          = $container.find( 'aside > ul' );

				// Early exit if studio is not active.
				if ( '1' !== fusionAppConfig.studio_status ) {
					return;
				}

				FusionPageBuilderApp.activeStudio = type;

				FusionPageBuilderApp.studio.setOptions( $layoutsContainer );

				// Ajax request for data.
				if ( ! $layoutsContainer.children().length ) {

					// Already have the data, append the layouts we want.
					if ( FusionPageBuilderApp.studio.studioData ) {
						self.insertStudioContent( $layoutsContainer, $sidebar, type );
						return;
					}

					// Make the request and do it on success.
					jQuery.when( FusionPageBuilderApp.studio.getStudioData() ).done( function() {
						self.insertStudioContent( $layoutsContainer, $sidebar, type );
					} );
					return;
				}

				FusionPageBuilderApp.studio.initFilter( $sidebar );
				FusionPageBuilderApp.studio.initLazyLoad( $layoutsContainer );
			},

			insertStudioContent: function( $layoutsContainer, $sidebar, type ) {
				var studioTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_studio_layout' ).html() ),
					studioElements = {};

				$layoutsContainer.prev( '.fusion-loader' ).hide();

				// TB section.
				if ( 'fusion_template' === type && 'string' === typeof FusionApp.data.template_category ) {
					type = FusionApp.data.template_category;
				}

				// Forms.
				if ( 'fusion_template' === type && 'string' === typeof FusionApp.data.postDetails.post_type && 'fusion_form' === FusionApp.data.postDetails.post_type ) {
					type = 'forms';
				}

				// Off Canvas.
				if ( 'fusion_template' === type && 'string' === typeof FusionApp.data.postDetails.post_type && 'awb_off_canvas' === FusionApp.data.postDetails.post_type ) {
					type = 'awb_off_canvas';
				}

				if ( 'object' === typeof FusionPageBuilderApp.studio.studioData && null !== FusionPageBuilderApp.studio.studioData && 'undefined' !== typeof FusionPageBuilderApp.studio.studioData[ type ] ) {
					studioElements = FusionPageBuilderApp.studio.filterLayouts( FusionPageBuilderApp.studio.studioData[ type ] );

					_.each( studioElements, function( templateData ) {
						$layoutsContainer.append( jQuery( studioTemplate( templateData ) ) );
					} );

					// TODO: needs to be translatable.
					$sidebar.append( '<li data-slug="all" class="current">' + fusionBuilderText.all + ' <span>' + _.size( studioElements ) + '</span></li>' );
					_.each( FusionPageBuilderApp.studio.studioData.studio_tags[ type ], function( templateTag ) {
						$sidebar.append( '<li data-slug="' + templateTag.slug + '">' + templateTag.name + ' <span>' + templateTag.count + '</span></li>' );
					} );
				}
				FusionPageBuilderApp.studio.initFilter( $sidebar );
				FusionPageBuilderApp.studio.initLazyLoad( $layoutsContainer );
			},

			/**
			* Toggles import options.
			*
			* @since 3.7
			* @param {Object} event - The event.
			* @return {void}
			*/
			toggleImportOptions: function( event ) {
				var $wrapper = jQuery( event.currentTarget ).closest( '.studio-wrapper' );

				if ( ! $wrapper.hasClass( 'fusion-studio-preview-active' ) ) {
					$wrapper.find( '.awb-import-options' ).toggleClass( 'open' );
				}
			},

			/**
			 * Things to do before studio item import starts.
			 *
			 * @return void
			 */
			beforeStudioItemImport: function() {
				// Hide library modal and it's overlay.
				this.$el.closest( '.ui-dialog' ).css( 'display', 'none' ).next( '.ui-widget-overlay' ).css( 'display', 'none' );

				// Display import modal.
				this.studioImportModalView = new FusionPageBuilder.StudioImportModalView();
				jQuery( 'body' ).append( this.studioImportModalView.render().el );

				this.studioImportModalView.updateStatus( fusionBuilderText.studio_importing_content );
			},

			/**
			 * Imports studio post's media.
			 *
			 * @param {object} postData
			 * * @param {string} mediaKey
			 * * @param {object} importOptions
			 * @return promise
			 */
			importStudioMedia: function( postData, mediaKey, importOptions ) {

				this.studioImportModalView.updateStatus( fusionBuilderText.studio_importing_media + ' ' + mediaKey.replace( '_', ' ' ) );
				this.studioImportModalView.updateProgressBar( postData.avada_media, mediaKey );

				return jQuery.ajax( {
					type: 'POST',
					url: ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'awb_studio_import_media',
						data: {
							mediaImportKey: mediaKey,
							postData: postData
						},
						overWriteType: importOptions.overWriteType,
						shouldInvert: importOptions.shouldInvert,
						imagesImport: importOptions.imagesImport,
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce
					},
					success: function( data ) {
						FusionPageBuilderApp.studio.setImportData( data );
					}
				} );
			},

			/**
			 * Imports demo page's media.
			 *
			 * @param {object} postData
			 * @return promise
			 */
			importDemoPageMedia: function( postData, mediaKey ) {

				this.demoImportModalView.updateStatus( fusionBuilderText.demo_importing_media + ' ' + mediaKey.replace( '_', ' ' ) );
				this.demoImportModalView.updateProgressBar( postData.avada_media, mediaKey );

				return jQuery.ajax( {
					type: 'POST',
					url: ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'awb_studio_import_media',
						data: {
							mediaImportKey: mediaKey,
							postData: postData
						},
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce
					},
					success: function( data ) {
						FusionPageBuilderApp.website.setImportData( data );
					}
				} );
			},

			loadWebsite: function() {
				var self              = this,
					$container        = jQuery( '#fusion-builder-layouts-demos' ),
					$sidebar          = $container.find( 'aside' ),
					$layoutsContainer = $container.find( '.studio-imports' );

				// Ajax request for data.
				if ( ! $container.find( '.studio-imports' ).children().length ) {

					// Already have the data, append the layouts we want.
					if ( FusionPageBuilderApp.website.websiteData ) {
						self.insertWebsiteContent( $container );
						return;
					}

					// Make the request and do it on success.
					jQuery.when( FusionPageBuilderApp.website.getWebsiteData() ).done( function() {
						self.insertWebsiteContent( $container );
					} );

				}

				FusionPageBuilderApp.website.initFilter( $sidebar );
				FusionPageBuilderApp.website.initLazyLoad( $layoutsContainer );
				FusionPageBuilderApp.website.initImport( $container );
			},

			insertWebsiteContent: function( $container ) {
				var $layoutsContainer = $container.find( '.studio-imports' ),
					$sidebar        = $container.find( 'aside' ),
					$pagesContainer = $container.find( '.awb-pages-container' ),
					siteTemplate    = FusionPageBuilder.template( jQuery( '#tmpl-fusion_website_layout' ).html() ),
					pageTemplate    = FusionPageBuilder.template( jQuery( '#tmpl-fusion_website_pages' ).html() ),
					sidebarTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_website_tags' ).html() ),
					siteElements    = {},
					classes;

				$layoutsContainer.prev( '.fusion-loader' ).hide();
				if ( 'object' === typeof FusionPageBuilderApp.website.websiteData ) {
					$container.find( '.awb-sites-failed-msg' ).hide()
					.end().find( '.awb-sites-wrapper' ).css( 'display', '' );

					siteElements = FusionPageBuilderApp.website.websiteData.data;

					_.each( siteElements, function( templateData ) {
						$layoutsContainer.append( jQuery( siteTemplate( templateData ) ) );
						$pagesContainer.append( jQuery( pageTemplate( templateData ) ) );
					} );

					$sidebar.append( jQuery( sidebarTemplate( FusionPageBuilderApp.website.websiteData ) ) );
				} else {
					$container.find( '.awb-sites-failed-msg' ).show()
					.end().find( '.awb-sites-wrapper' ).hide();
				}
				FusionPageBuilderApp.website.initFilter( $sidebar );
				FusionPageBuilderApp.website.initLazyLoad( $layoutsContainer );
				FusionPageBuilderApp.website.initImport( $container );
			},

			/**
			 * Things to do before demo page import starts.
			 *
			 * @return void
			 */
			beforeDemoPageImport: function() {
				// Close library modal.
				this.removeView();

				// Display import modal.
				this.demoImportModalView = new FusionPageBuilder.DemoImportModalView();
				jQuery( 'body' ).append( this.demoImportModalView.render().el );

				this.demoImportModalView.updateStatus( fusionBuilderText.demo_importing_content );
			},

			/**
			 * Dynamically loads assets which are referenced in the studio content import.
			 *
			 * @param {Object} post_data
			 */
			loadBuilderAssets: function( post_data ) {

				// Enqueue custom icons' CSS file, update global icons object and reinit iconpickers.
				if ( 'undefined' !== typeof post_data.custom_icons ) {

					jQuery.each( post_data.custom_icons, function( index, customIconSet ) {

						// Builder window.
						jQuery( 'head' ).append( '<link rel="stylesheet" id="' + customIconSet.post_name + '" href="' + customIconSet.css_url + '" type="text/css" media="all">' );

						// Preview window.
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'head' ).append( '<link rel="stylesheet" id="' + customIconSet.post_name + '" href="' + customIconSet.css_url + '" type="text/css" media="all">' );

						// Update global icons object.

						// It should be an object, wp_localize_script doesnt convert empty array to an object.
						if ( Array.isArray( fusionAppConfig.customIcons ) && 0 === fusionAppConfig.customIcons.length ) {
							fusionAppConfig.customIcons = {};
						}

						fusionAppConfig.customIcons[ customIconSet.post_name ] = customIconSet;
					} );

					// Reinit icon picker after all new custom icon sets were added.
					FusionApp.reInitIconPicker();
				}

			},

			/**
			* Get import options.
			*
			* @since 3.7
			* @param {Object} event - The event.
			* @return {object}
			*/
			getImportOptions: function( event ) {
				var $wrapper         =  jQuery( event.currentTarget ).closest( '.studio-wrapper' ),
					overWriteType    = $wrapper.find( 'input[name="overwrite-type"]:checked' ).val(),
					shouldInvert     = $wrapper.find( 'input[name="invert"]:checked' ).val(),
					contentPlacement = $wrapper.find( 'input[name="load-type"]:checked' ).val(),
					imagesImport     = $wrapper.find( 'input[name="images"]:checked' ).val(),
					layoutID         = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ).data( 'layout-id' ),
					options;

					options = {
						'overWriteType': overWriteType,
						'layoutID': layoutID,
						'shouldInvert': shouldInvert,
						'contentPlacement': contentPlacement,
						'imagesImport': imagesImport
					};

					if ( this.areOptionsChanged( options ) ) {
						this.saveStudioPreferences( options );
					}

					return options;
			},

			/**
			* Checks if studio options are changed.
			*
			* @since 3.7
			* @param {Object} options - The options.
			* @return {Boolean}
			*/
			areOptionsChanged: function( options ) {
				var preferencesChanged = [],
					i;

				jQuery.each( options, function( index, value ) {

					if ( 'layoutID' === index ) {
						return true;
					}

					if ( options[ index ] !== FusionApp.preferencesData[ index ] ) {
						preferencesChanged.push( index );
					}
				} );

				for ( i = 0; i < preferencesChanged.length; i++ ) {
					FusionEvents.trigger( 'fusion-preferences-' + preferencesChanged[ i ] + '-updated' );
				}

				return preferencesChanged.length ? true : false;
			},

			/**
			* Saves studio preferences.
			*
			* @since 3.7
			* @param {Object} options - The options.
			* @return {void}
			*/
			saveStudioPreferences: function( options ) {

				// Update data.
				FusionApp.preferencesData.overWriteType    = options.overWriteType;
				FusionApp.preferencesData.shouldInvert     = options.shouldInvert;
				FusionApp.preferencesData.contentPlacement = options.contentPlacement;
				FusionApp.preferencesData.imagesImport     = options.imagesImport;

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					dataType: 'JSON',
					data: {
						action: 'fusion_app_save_builder_preferences',
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						preferences: FusionApp.preferencesData
					}
				} )
				.done( function( response ) {
					FusionApp.preferences = response;
				} );
			}
	} );
  } );
}( jQuery ) );
