/* global fusionBuilderConfig, FusionPageBuilderApp, fusionBuilderText, FusionPageBuilderEvents, ajaxurl */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Website = FusionPageBuilder.Studio.extend( {

		/**
		 * Init.
		 *
		 * @since 3.5
		 * @return {void}
		 */
		initialize: function() {
			this.websiteData     = false;
			this.websiteRequest  = false;
			this.layoutIsLoading = false;
			this.mediaImportKeys = [];
			this.state           = {
				current: '',
				title: '',
				nextCat: ''
			};
		},

		/**
		 * Gets the webfonts via AJAX.
		 *
		 * @since 3.5
		 * @return {void}
		 */
		getWebsiteData: function() {
			var self = this;

			if ( self.websiteData && self.websiteData ) {
				return;
			}

			if ( false !== self.websiteRequest ) {
				return self.websiteRequest;
			}
			return jQuery.post( fusionBuilderConfig.ajaxurl, { action: 'awb_load_websites', load_website_data: true, fusion_load_nonce: fusionBuilderConfig.fusion_load_nonce }, function( data ) {
				if ( ! _.isNull( data ) ) {
					self.websiteData = data;
				}
			}, 'json' );
		},

		/**
		 * Init filter sidebar.
		 *
		 * @since 3.5
		 * @param {Object} sidebar - The sidebar elements.
		 * @return {void}
		 */
		initFilter: function( $sidebar ) {
			var timeouts   = [],
				self       = this,
				$container = jQuery( '#fusion-builder-layouts-demos' );

			$sidebar.find( 'li' ).off();

			$sidebar.find( 'li' ).on( 'click', function() {
				var $studioImports = jQuery( this ).closest( '.studio-wrapper' ).find( '.studio-imports' ),
					$templates      = $studioImports.find( 'li' ),
					slug            = jQuery( this ).attr( 'data-slug' ),
					counter         = 1,
					order           = [],
					$showTemplates  = [],
					i;

				// Don't do filtering for active filter.
				if ( jQuery( this ).hasClass( 'current' ) ) {
					return false;
				}

				// Clear all timeouts to prevent animations still running.
				jQuery.each( timeouts, function( index, value ) {
					clearTimeout( value );
				} );

				// Hide detail demo.
				if ( ! $studioImports.next().hasClass( 'hidden' ) ) {
					$studioImports.removeClass( 'hidden' ).next().addClass( 'hidden' ).find( '.demo-' + self.state.current ).addClass( 'hidden' );
				}

				// Remove current from active.
				$sidebar.find( 'li.current' ).removeClass( 'current' );

				// Hide all templates.
				$templates.css( { display: 'none' } ).addClass( 'hidden' );

				// Fade correct templates in.
				$templates.each( function() {
					var $template = jQuery( this );
					if ( 'undefined' === typeof $template.data( 'slug' ) ) {
						return true;
					}

					if ( 'all' === slug || -1 !== $template.data( 'slug' ).indexOf( slug ) ) {
						$template.css( { display: 'inline-block' } );
						$showTemplates.push( $template );
					}
				} );

				setTimeout( function() {
					jQuery.each( $showTemplates, function() {
						var $template = jQuery( this ),
							position  = $template.position();

						// Loop afer all have been added to get position.
						position.$el = $template;
						order.push( position );
					} );

					// Sort top to bottom.
					order.sort( self.SortByTop );

					// Reveal top to bottom.
					for ( i = 0;  i < order.length; i++ ) {
						timeouts.push(
							self.doSetTimeout( i, order, counter )
						);
						counter++;
					}
				}, 50 );

				// Add current to show it is active.
				jQuery( this ).addClass( 'current' );
			} );

			$container.find( '#awb_sites_search_demos' ).on( 'change paste keyup', _.debounce( function( ) {
				var demoPageLink = jQuery( this ).val(),
					demoPage,
					parentDemo;

				demoPageLink = demoPageLink.replace( 'https://', '' ).replace( 'http://', '' );
				if ( '/' !== demoPageLink[ demoPageLink.length - 1 ] && ! _.isEmpty( demoPageLink ) ) {
					demoPageLink += '/';
				}

				demoPage   = $container.find( 'li[data-page-link="' + demoPageLink + '"]' );
				parentDemo = demoPage.closest( '.awb-page-item' );

				if ( demoPage.length ) {
					if ( $container.find( '.site-details' ).hasClass( 'hidden' ) ) {
						$container.find( '.site-details' ).removeClass( 'hidden' ).prev().addClass( 'hidden' );
					}
					demoPage.removeClass( 'hidden' ).siblings().addClass( 'hidden' );

					self.state.current = parentDemo.data( 'website_id' );
					self.state.title = parentDemo.data( 'website_title' );
					parentDemo.removeClass( 'hidden' ).siblings().addClass( 'hidden' );
					self.updateDetailHeader( $container );
				}

			}, 100 ) );
		},

		/**
		 * Init Import Demo.
		 *
		 * @since 3.5
		 * @param {Object} sidebar - The sidebar elements.
		 * @return {void}
		 */
		initImport: function( $container ) {
			var self = this;

			// Demo on Click.
			$container.find( '.studio-imports > li' ).on( 'click', function( event ) {
				event.preventDefault();
				self.state.current = jQuery( this ).data( 'website_id' );
				self.state.title = jQuery( this ).data( 'website_title' );
				jQuery( this ).closest( '.studio-imports' ).addClass( 'hidden' )
				.next().removeClass( 'hidden' ).find( '.demo-' + self.state.current ).removeClass( 'hidden' )
				.find( 'li.hidden' ).removeClass( 'hidden' );

				self.updateDetailHeader( $container );
			} ).end().find( '.awb-sites-back-js' ).on( 'click', function( event ) {
				event.preventDefault();
				jQuery( this ).closest( '.site-details' ).find( '.demo-' + self.state.current ).addClass( 'hidden' )
				.end().closest( '.site-details' ).addClass( 'hidden' ).prev().removeClass( 'hidden' );
			} ).end().find( '.awb-sites-import-js' ).on( 'click', this.loadDemoPage.bind( this ) )
			.end().find( '.awb-sites-next-js' ).on( 'click', function( event ) {
				event.preventDefault();
				$container.find( 'aside li[data-slug="' + self.state.nextCat + '"]' ).trigger( 'click' );
			} );

		},

		/**
		 * Update Detail Header.
		 *
		 * @since 3.5
		 * @param {Object} container - The container elements.
		 * @return {void}
		 */
		updateDetailHeader: function( $container ) {
			this.state.nextCat = $container.find( 'aside ul > li.current' ).next().data( 'slug' );
			$container.find( '.awb-sites-title' ).html( this.state.title );
			if ( this.state.nextCat && this.websiteData.tags[ this.state.nextCat ] ) {
				$container.find( '.awb-sites-next-js' ).removeClass( 'vs-hidden' ).html( this.websiteData.tags[ this.state.nextCat ].name );
			} else {
				$container.find( '.awb-sites-next-js' ).addClass( 'vs-hidden' );
			}
		},

		/**
		 * Import Website Demo Page.
		 *
		 * @since 3.5
		 * @param {Object} event - event object.
		 * @return {void}
		 */
		loadDemoPage: function( event ) {
			var self = this,
				demoName,
				postId,
				pageId;

			if ( event ) {
				event.preventDefault();
			}

			if ( true === this.layoutIsLoading ) {
				return;
			}

			this.layoutIsLoading = true;

			demoName = jQuery( event.currentTarget ).data( 'demo-name' );
			postId   = jQuery( event.currentTarget ).data( 'post-id' );
			pageId   = jQuery( event.currentTarget ).data( 'page-id' );

			jQuery.ajax( {
				type: 'POST',
				url: fusionBuilderConfig.ajaxurl,
				data: {
					action: 'awb_load_websites_page',
					fusion_load_nonce: fusionBuilderConfig.fusion_load_nonce,
					page_id: pageId,
					demo_name: demoName,
					post_id: postId
				},
				beforeSend: function() {
					FusionPageBuilderEvents.trigger( 'fusion-show-loader' );

					jQuery( 'body' ).removeClass( 'fusion_builder_inner_row_no_scroll' );
					jQuery( '.fusion_builder_modal_inner_row_overlay' ).remove();
					jQuery( '#fusion-builder-layouts' ).hide();

					jQuery( '#fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.demo_importing_content );

				}
			} )
			.done( function( data ) {
				var dataObj,
					i,
					promises = [],
					dfd      = jQuery.Deferred(),  // Master deferred.
					dfdNext  = dfd; // Next deferred in the chain.

				dataObj = JSON.parse( data );

				if ( ! dataObj.success ) {
					self.layoutIsLoading = false;
					alert( fusionBuilderText.api_error_text ); // eslint-disable-line no-alert
					FusionPageBuilderEvents.trigger( 'fusion-hide-loader' );
					return;
				}

				dfd.resolve();

				// Reset array.
				self.mediaImportKeys = [];

				// We have the content, let's check for assets.
				// Filter out empty properties (now those are empty arrays).
				if ( 'object' === typeof dataObj.avada_media ) {
					Object.keys( dataObj.avada_media ).forEach( function( key ) {
						// We expect and object.
						if ( 'object' === typeof dataObj.avada_media[ key ] && ! Array.isArray( dataObj.avada_media[ key ] ) ) {
							self.mediaImportKeys.push( key );
						}
					} );
				}

				// Import media if needed.
				if ( 0 < self.mediaImportKeys.length ) {

					// Set first AJAX response as initial data.
					self.setImportData( dataObj );

					for ( i = 0; i < self.mediaImportKeys.length; i++ ) {

						// IIFE to freeze the value of i.
						( function( k ) { // eslint-disable-line no-loop-func

							dfdNext = dfdNext.then( function() {
								return self.importMedia( self.getImportData(), self.mediaImportKeys[ k ] );
							} );

							promises.push( dfdNext );
						}( i ) );

					}

					jQuery.when.apply( null, promises ).then(
						function() {
							self.setPageContent( dataObj, self.getImportData().post_content );

							FusionPageBuilderEvents.trigger( 'fusion-hide-loader' );

							self.resetImportData();
						},
						function() {

							jQuery( '#fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.demo_importing_content_failed );

							FusionPageBuilderEvents.trigger( 'fusion-hide-loader' );

							self.resetImportData();
						}
					);
				} else {
					self.setPageContent( dataObj, dataObj.post_content );
					FusionPageBuilderEvents.trigger( 'fusion-hide-loader' );
				}
			} );
		},

		/**
		 * Imports post's media.
		 *
		 * @param {object} postData
		 * @return promise
		 */
		importMedia: function( postData, mediaKey ) {
			var self = this;

			jQuery( '#fusion-loader .awb-studio-import-status' ).html( fusionBuilderText.demo_importing_media + ' ' + mediaKey.replace( '_', ' ' ) );

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
					fusion_load_nonce: FusionPageBuilderApp.fusion_load_nonce
				},
				success: function( data ) {
					self.setImportData( data );
				}
			} );
		},

		/**
		 *
		 * @param {Object} dataObj
		 * @param {String} newContent
		 */
		setPageContent: function( dataObj, newContent ) {
			var meta;

			// New layout loaded
			FusionPageBuilderApp.layoutLoaded();

			// Set page template
			if ( 'undefined' !== typeof dataObj.page_template ) {
				jQuery( '#page_template' ).val( dataObj.page_template );
			}

			meta = dataObj.meta;

			// Set page options
			_.each( meta, function( value, name ) {
				jQuery( '#' + name ).val( value ).trigger( 'change' );
			} );

			// Create new builder layout.
			FusionPageBuilderApp.clearBuilderLayout();
			FusionPageBuilderApp.createBuilderLayout( newContent );

			this.layoutIsLoading = false;
		}
	} );

}( jQuery ) );
