/* global FusionPageBuilderEvents, fusionBuilderText, FusionPageBuilderApp, fusionBuilderConfig, ajaxurl */
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

			initialize: function( attributes ) {
				this.options = attributes;
				this.listenTo( FusionPageBuilderEvents, 'fusion-columns-added', this.removeView );
				this.listenTo( FusionPageBuilderEvents, 'fusion-modal-view-removed', this.removeView );
				this.listenTo( FusionPageBuilderEvents, 'fusion-studio-content-imported', this.loadBuilderAssets );

				this.attachDynamicEvents();
			},

			/**
			 * Attach events to elements which are added dynamically (needed because we dont set view.el).
			 */
			attachDynamicEvents: function() {}, // eslint-disable-line no-empty-function

			/**
			 * Remove events to elements which are added dynamically.
			 */
			removeDynamicEvents: function() {},  // eslint-disable-line no-empty-function

			removeView: function() {
				FusionPageBuilderApp.activeModal = '';
				this.removeDynamicEvents();
				this.remove();
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

			loadStudio: function( type ) {
				var self              = this,
					$container        = 'fusion_template' === type ? jQuery( '#fusion-builder-' + type + '-studio' ) : this.$el.find( '#fusion-builder-' + type + '-studio' ),
					$layoutsContainer = $container.find( '.studio-imports' ),
					$sidebar          = $container.find( 'aside > ul' );

				// Early exit if studio is not active.
				if ( '1' !== fusionBuilderConfig.studio_status ) {
					return;
				}

				// Set import options.
				FusionPageBuilderApp.studio.setImportOptions( $layoutsContainer );

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

				// TB sections.
				if ( 'fusion_template' === type && 'string' === typeof fusionBuilderConfig.template_category && 0 < fusionBuilderConfig.template_category.length ) {
					type = fusionBuilderConfig.template_category;
				}

				// Forms.
				if ( 'fusion_template' === type && 'string' === typeof fusionBuilderConfig.post_type && 'fusion_form' === fusionBuilderConfig.post_type ) {
					type = 'forms';
				}

				// Off Canvas.
				if ( 'fusion_template' === type && 'string' === typeof fusionBuilderConfig.post_type && 'awb_off_canvas' === fusionBuilderConfig.post_type ) {
					type = 'awb_off_canvas';
				}

				if ( 'object' === typeof FusionPageBuilderApp.studio.studioData && 'undefined' !== typeof FusionPageBuilderApp.studio.studioData[ type ] ) {
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
			 * Imports studio post's media.
			 *
			 * @param {object} postData
			 * @param {string} mediaKey
			 * @param {object} importOptions
			 * @return promise
			 */
			importStudioMedia: function( postData, mediaKey, importOptions ) {

				jQuery( '.fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.studio_importing_media + ' ' + mediaKey.replace( '_', ' ' ) );

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
						fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce
					},
					success: function( data ) {
						FusionPageBuilderApp.studio.setImportData( data );
					}
				} );
			},

			loadWebsite: function() {
				var self   	   = this,
					$container = jQuery( '#fusion-builder-layouts-demos' );

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
			},

			insertWebsiteContent: function( $container ) {
				var $layoutsContainer = $container.find( '.studio-imports' ),
					$sidebar        = $container.find( 'aside' ),
					$pagesContainer = $container.find( '.awb-pages-container' ),
					siteTemplate    = FusionPageBuilder.template( jQuery( '#tmpl-fusion_website_layout' ).html() ),
					pageTemplate    = FusionPageBuilder.template( jQuery( '#tmpl-fusion_website_pages' ).html() ),
					sidebarTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_website_tags' ).html() ),
					siteElements    = {},
					counter         = 0,
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

						// Update global icons object.

						// It should be an object, wp_localize_script doesnt convert empty array to an object.
						if ( Array.isArray( fusionBuilderConfig.customIcons ) && 0 === fusionBuilderConfig.customIcons.length ) {
							fusionBuilderConfig.customIcons = {};
						}

						fusionBuilderConfig.customIcons[ customIconSet.post_name ] = customIconSet;
					} );

					// Reinit icon picker after all new custom icon sets were added.
					FusionPageBuilder.reInitIconPicker();
				}
			}
		} );
	} );
}( jQuery ) );
