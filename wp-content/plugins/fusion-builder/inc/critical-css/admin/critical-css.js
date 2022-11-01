/* global criticalCSS, ajaxurl, generateCriticalCSS, BrowserInterfaceIframe */
( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		var $disablePreview = jQuery( '.awb-modal-overlay' ),
			$progressBar    = jQuery( '.awb-admin-modal-wrap .awb-admin-modal-status-bar .awb-admin-modal-status-bar-progress-bar' ),
			AWBCriticalCSS = {

				options: {
					urls: {},
					viewports: [ 'mobile', 'desktop' ],
					reloadPage: false,
					buttonSelector: null,
					messageSelector: null
				},

				loopIndex: 0,

				urlsLength: 0,

				$button: null,

				$message: null,

				init: function( options ) {

					// Merge options with defaults.
					this.options = jQuery.extend( {}, this.options, options );

					// Get URLs length.
					this.urlsLength = Object.keys( this.options.urls ).length;

					if ( options.buttonSelector ) {
						this.$button = jQuery( options.buttonSelector );
					}

					if ( options.messageSelector ) {
						this.$message = jQuery( options.messageSelector );
					}

					// Just in case.
					this.resetLoopIndex();
				},

				resetLoopIndex: function() {
					this.loopIndex = 0;
				},

				incrementLoopIndex: function() {
					this.loopIndex++;
				},

				updateMessageElement: function( text ) {

					if ( this.$message ) {
						this.$message.html( text );
					}
				},

				updateProgressBarWidth( width ) {
					if ( $progressBar && 0 < $progressBar.length ) {
						$progressBar.css( 'width', width );
					}
				},

				ifGenerateMobileCSS: function() {
					return -1 !== this.options.viewports.indexOf( 'mobile' );
				},

				ifGenerateDesktopCSS: function() {
					return -1 !== this.options.viewports.indexOf( 'desktop' );
				},

				createCriticalCSS: async function() {
					var self = this,
						pcString = '',
						criticalCSSData = {},
						ajaxData = {},
						saveCSS  = {},
						savePreloads = {},
						postID  = Object.keys( self.options.urls )[ self.loopIndex ],
						url     = self.options.urls[ postID ],
						generateOptions = {
							urls: [ url ],
							progressCallback: ( step, stepCount ) => { // eslint-disable-line no-unused-vars
							},
							filters: {
								properties: ( key, value ) => true // eslint-disable-line no-unused-vars
							}
						};

					self.incrementLoopIndex();

					// If there are more than one URL to be processed, prepend message with this.
					if ( 1 < self.urlsLength ) {
						pcString = self.loopIndex + '/' + self.urlsLength + ' - ';
					}

					// Update message, now generating mobile CSS.
					self.updateMessageElement( pcString + criticalCSS.generateMobile );

					if ( 1 === self.urlsLength ) {
						self.updateProgressBarWidth( '45%' );
					} else {
						self.updateProgressBarWidth( ( 95 * ( self.loopIndex / self.urlsLength ) ) + '%' );
					}

					// Mobile.
					if ( true === self.ifGenerateMobileCSS() ) {
						// Set Mobile specific options.
						generateOptions.viewports = [ { width: 360, height: 640 } ];
						generateOptions.browserInterface = new BrowserInterfaceIframe( {
							requestGetParameters: {
								mcritical: '1'
							},
							loadTimeout: 120 * 1000,
							verifyPage: ( rawUrl, contentWindow, contentDocument ) => true, // eslint-disable-line no-unused-vars
							allowScripts: false
						} );

						criticalCSSData = await generateCriticalCSS( generateOptions );

						saveCSS.mobile      = criticalCSSData[ 0 ];
						savePreloads.mobile = criticalCSSData[ 2 ];

						// Update message.
						self.updateMessageElement( pcString + criticalCSS.generateDesktop );
					}

					// Desktop.
					if ( true === self.ifGenerateDesktopCSS() ) {

						// Set desktop specific options.
						generateOptions.viewports        = [ { width: 1920, height: 1080 } ];
						generateOptions.browserInterface = new BrowserInterfaceIframe( {
							requestGetParameters: {
								dcritical: '1'
							},
							loadTimeout: 120 * 1000,
							verifyPage: ( rawUrl, contentWindow, contentDocument ) => true, // eslint-disable-line no-unused-vars
							allowScripts: false
						} );

						criticalCSSData = await generateCriticalCSS( generateOptions );

						saveCSS.desktop      = criticalCSSData[ 0 ];
						savePreloads.desktop = criticalCSSData[ 2 ];

						// Update message.
						self.updateMessageElement( pcString + criticalCSS.saving );

						if ( 1 === self.urlsLength ) {
							self.updateProgressBarWidth( '90%' );
						}
					}

					ajaxData = {
						css: saveCSS,
						preloads: savePreloads,
						post_id: postID,
						action: 'awb_critical_css',
						security: jQuery( '#fusion-page-options-nonce' ).val()
					};

					// Save both sets of generated CSS.
					jQuery.post( criticalCSS.ajaxurl, ajaxData, function( response ) {

						// Error saving the new CSS.
						if ( 'object' !== typeof response || ! response.success ) {

							self.updateMessageElement( pcString + response.data );

							setTimeout( function() {
								self.$button.removeClass( 'processing disabled' );
							}, 3000 );
							return;
						}

						self.updateMessageElement( pcString + criticalCSS.successComplete );

						// If this is the last one, we can give completed message.
						if ( self.urlsLength === self.loopIndex ) {

							self.updateProgressBarWidth( '100%' );

							if ( true === self.options.reloadPage ) {
								location.reload( true );
							} else if ( self.$button ) {
								self.$button.removeClass( 'processing disabled' );
							}
						} else {
							self.createCriticalCSS();
						}
					} );
				}

			};

		// Generating and adding new CSS to table.
		jQuery( '#awb-critical-form' ).on( 'submit', function( event ) {
			var $form      = jQuery( this ),
				$submit    = $form.find( 'input[type="submit"]' ),
				$message   = jQuery( '.awb-admin-modal-wrap .awb-admin-modal-status-bar .awb-admin-modal-status-bar-label' ),
				urls       = false,
				urlsLength = 0;

			event.preventDefault();

			// No double processing.
			if ( $submit.hasClass( 'processing' ) ) {
				return;
			}

			// Open modal.
			jQuery( 'body' ).addClass( 'fusion_builder_no_scroll' );
			$disablePreview.show();
			jQuery( '.awb-admin-modal-wrap' ).css( 'display', 'block' );

			// Add message to loader.
			$message.text( criticalCSS.gatheringURLs );

			// Disable form and show status.
			$submit.addClass( 'processing' );

			// Fetch the actual URLs for generation.
			jQuery.get(
				ajaxurl,
				$form.serialize(),
				function( response ) {
					var criticalCSSObj,
						options;

					// Error fetching the URLs.
					if ( 'object' !== typeof response || ! response.success ) {
						$message.text( response.data );
						setTimeout( function() {
							$submit.removeClass( 'processing' );
						}, 3000 );
						return;
					}

					// Set URLs for testing.
					urls = response.data;

					// Check we have object of URLs.
					if ( 'object' !== typeof urls ) {
						$message.text( criticalCSS.noURLs );
						setTimeout( function() {
							$submit.removeClass( 'processing' );
						}, 3000 );
						return;
					}

					urlsLength = Object.keys( urls ).length;
					if ( 0 === urlsLength ) {
						$message.text( criticalCSS.noURLs );
						setTimeout( function() {
							$submit.removeClass( 'processing' );
						}, 3000 );
						return;
					}

					criticalCSSObj = Object.create( AWBCriticalCSS );
					options = {
						buttonSelector: $submit,
						messageSelector: $message,
						urls: urls,
						viewports: [ 'mobile', 'desktop' ],
						reloadPage: true
					};

					// Init object.
					criticalCSSObj.init( options );

					// Generete CSS.
					criticalCSSObj.createCriticalCSS();
				}
			);
			return false;
		} );

		// Bulk regenerate.
		jQuery( '#awb-critical-css' ).on( 'submit', function( event ) {
			var $form      = jQuery( this ),
				$submit    = $form.find( 'input[type="submit"]' ),
				$message   = jQuery( '.awb-admin-modal-wrap .awb-admin-modal-status-bar .awb-admin-modal-status-bar-label' ),
				urls       = false,
				urlsLength = 0;

			// Select fields are synced, so need to check only one.
			if ( 'awb_bulk_delete_css' === $form.find( '#bulk-action-selector-top' ).val() ) {
				return;
			}

			event.preventDefault();

			// Return after preventing submitting form without selection.
			if ( '-1' === $form.find( '#bulk-action-selector-top' ).val() ) {
				return;
			}

			// No double processing.
			if ( $submit.hasClass( 'processing' ) ) {
				return;
			}

			// Open modal.
			jQuery( 'body' ).addClass( 'fusion_builder_no_scroll' );
			$disablePreview.show();
			jQuery( '.awb-admin-modal-wrap' ).css( 'display', 'block' );

			if ( 0 === $form.find( '.check-column input[name="post[]"]:checked' ).length ) {
				$message.text( criticalCSS.noSelection );
				return;
			}

			// Add message to loader.
			$message.text( criticalCSS.gatheringURLs );

			// Disable form and show status.
			$submit.addClass( 'processing' );

			// Fetch the actual URLs for generation.
			jQuery.get(
				ajaxurl,
				$form.serialize(),
				function( response ) {
					var criticalCSSObj,
						options;

					// Error fetching the URLs.
					if ( 'object' !== typeof response || ! response.success ) {
						$message.text( response.data );
						setTimeout( function() {
							$submit.removeClass( 'processing' );
						}, 3000 );
						return;
					}

					// Set URLs for testing.
					urls = response.data;

					// Check we have object of URLs.
					if ( 'object' !== typeof urls ) {
						$message.text( criticalCSS.noURLs );
						setTimeout( function() {
							$submit.removeClass( 'processing' );
						}, 3000 );
						return;
					}

					urlsLength = Object.keys( urls ).length;
					if ( 0 === urlsLength ) {
						$message.text( criticalCSS.noURLs );
						setTimeout( function() {
							$submit.removeClass( 'processing' );
						}, 3000 );
						return;
					}

					criticalCSSObj = Object.create( AWBCriticalCSS );
					options = {
						buttonSelector: $submit,
						messageSelector: $message,
						urls: urls,
						viewports: [ 'mobile', 'desktop' ],
						reloadPage: true
					};

					// Init object.
					criticalCSSObj.init( options );

					// Generete CSS.
					criticalCSSObj.createCriticalCSS();
				}
			);
			return false;
		} );

		// Single row regenerate CSS.
		jQuery( '.awb-update-row, .awb-regenerate-mobile-css, .awb-regenerate-desktop-css' ).on( 'click', function( event ) {
			var $button  = jQuery( this ),
				$row     = $button.closest( 'tr' ),
				urls       = false,
				urlsLength = 0,
				data       = {
					action: 'awb_regenerate_critical_css',
					awb_critical_id: $row.attr( 'data-id' )
				},
				viewports = [ 'mobile', 'desktop' ],
				updatingMobileCSS  = $button.hasClass( 'awb-regenerate-mobile-css' ),
				updatingDesktopCSS = $button.hasClass( 'awb-regenerate-desktop-css' );

			event.preventDefault();

			if ( updatingMobileCSS || updatingDesktopCSS ) {
				$row.addClass( 'processing' );

				if ( updatingMobileCSS ) {
					viewports = [ 'mobile' ];
				} else {
					viewports = [ 'desktop' ];
				}
			}

			// No double processing.
			if ( $button.hasClass( 'processing' ) ) {
				return;
			}

			// Disable form and show status.
			$button.addClass( 'processing disabled' );

			// Fetch the actual URLs for generation.
			jQuery.get(
				ajaxurl,
				data,
				function( response ) {
					var criticalCSSObj,
						options;

					// Error fetching the URLs.
					if ( 'object' !== typeof response || ! response.success ) {
						setTimeout( function() {
							$button.removeClass( 'processing disabled' );
						}, 3000 );
						return;
					}

					// Set URLs for testing.
					urls = response.data;

					// Check we have object of URLs.
					if ( 'object' !== typeof urls ) {
						setTimeout( function() {
							$button.removeClass( 'processing disabled' );
						}, 3000 );
						return;
					}

					urlsLength = Object.keys( urls ).length;
					if ( 0 === urlsLength ) {
						setTimeout( function() {
							$button.removeClass( 'processing disabled' );
						}, 3000 );
						return;
					}

					criticalCSSObj = Object.create( AWBCriticalCSS );
					options = {
						buttonSelector: $button,
						urls: urls,
						viewports: viewports
					};

					// Init object.
					criticalCSSObj.init( options );

					// Generate CSS.
					criticalCSSObj.createCriticalCSS().then( function() {
						var successIcon = '<i class="fusiona-checkmark"></i>';

						$row.removeClass( 'processing' );

						// Add check icon when regenerating is done.
						if ( updatingMobileCSS ) {
							$row.find( '.column-mobile_css .awb-ccss-icon' ).html( successIcon );
						} else if ( updatingDesktopCSS ) {
							$row.find( '.column-dektop_css .awb-ccss-icon' ).html( successIcon );
						} else {
							$row.find( '.awb-ccss-icon' ).html( successIcon );
						}

					} );
				}
			);
			return false;
		} );

		// Dependency for specific page selection.
		jQuery( '#awb-critical-type' ).on( 'change', function( event ) { // eslint-disable-line no-unused-vars
			if ( 'specific_pages' === jQuery( this ).val() ) {
				jQuery( '.pyre_metabox_field' ).show();
			} else {
				jQuery( '.pyre_metabox_field' ).hide();
			}
		} );

		// Modal.
		jQuery( '.awb-admin-modal-corner-close' ).on( 'click', function( e ) {
			var $modal = jQuery( this ).closest( '.awb-admin-modal-wrap' );
			e.preventDefault();

			$modal.find( '.awb-admin-modal-status-bar-label span' ).html( '' );

			jQuery( 'body' ).removeClass( 'fusion_builder_no_scroll' );
			$disablePreview.hide();

			$modal.css( 'display', 'none' );
		} );

	} );

}( jQuery ) );
