/* global builderConfig, awbTypoData, FusionPageBuilder, builderId, fusionSettings, FusionPageBuilderApp, fusionAllElements, fusionAppConfig, FusionApp, fusionOptionName, fusionBuilderText, fusionIconSearch */
/* jshint -W020 */
var FusionEvents = _.extend( {}, Backbone.Events );

( function() {
	jQuery( document ).ready( function() {

		var fusionApp = Backbone.Model.extend( { // jshint ignore: line

			initialize: function() {
				this.builderId = builderId;

				// User is logged in and connected to back-end.
				this.connected = true;

				// This data is set by preview_data();
				this.initialData        = {};

				this.callback           = new FusionPageBuilder.Callback();
				this.dialog             = new FusionPageBuilder.Dialog();
				this.inlineEditor       = new FusionPageBuilder.inlineEditor();
				this.validate           = new FusionPageBuilder.Validate();
				this.hotkeys            = new FusionPageBuilder.Hotkeys();
				this.settings           = 'undefined' !== typeof fusionSettings ? fusionSettings : false;

				// Store TO changed (for multilingual).
				this.elementDefaults    = 'undefined' !== typeof fusionAllElements ? jQuery.extend( true, {}, fusionAllElements ) : {};
				this.editedDefaults     = {};
				this.editedTo           = {};

				// Content changed status for save button.
				this.contentChanged     = {};

				// Current data
				this.data               = {};
				this.data.postMeta      = {};
				this.data.samePage      = true;
				this.builderActive      = false;
				this.hasEditableContent = true;

				// This can have data added from external to pass on for save.
				this.customSave         = {};

				// Objects to map TO changes to defaults of others.
				this.settingsPoTo       = false;
				this.settingsToPo       = false;
				this.settingsToParams   = false;
				this.settingsToExtras   = false;
				this.storedPoCSS        = {};
				this.storedToCSS        = {};

				// UI
				this.toolbarView        = new FusionPageBuilder.Toolbar( { fusionApp: this } );
				this.builderToolbarView = false;
				this.sidebarView        = false;
				this.renderUI();

				// Preview size.
				this.previewWindowSize  = 'large';

				// Hold scripts which are being added to frame.
				this.scripts            = {};

				// Font Awesome stuff.
				this.listenTo( FusionEvents, 'fusion-preview-update', this.toggleFontAwesomePro );
				this.listenTo( FusionEvents, 'fusion-to-status_fontawesome-changed', this.FontAwesomeSubSets );

				// Preview updates.
				this.listenTo( FusionEvents, 'awb-update-studio-item-preview', this.previewColors  );

				this.setHeartbeatListeners();
				this.correctLayoutTooltipPosition();
				this.initStudioPreview();

				// Cache busting var.
				this.refreshCounter = 0;

				// Track changes made
				this.hasChange   = false;

				this.showLoader();

				this.modifierActive = false;
				window.onkeydown = this.keyActive.bind( this );
				window.onkeyup   = this.keyInactive.bind( this );

				document.getElementById( 'fb-preview' ).contentWindow.onkeydown = this.keyActive.bind( this );
				document.getElementById( 'fb-preview' ).contentWindow.onkeyup   = this.keyInactive.bind( this );

				// If page switch has been triggered manually.
				this.manualSwitch = false;

				this.linkSelectors = 'td.tribe-events-thismonth a, .tribe-events-month-event-title a,.fusion-menu a, .fusion-secondary-menu a, .fusion-logo-link, .fusion-imageframe > a, .widget a, .woocommerce-tabs a, .fusion-posts-container a:not(.fusion-rollover-gallery), .fusion-rollover .fusion-rollover-link, .project-info-box a, .fusion-meta-info-wrapper a, .related-posts a, .related.products a, .woocommerce-page .products .product a, #tribe-events-content a, .fusion-breadcrumbs a, .single-navigation a, .fusion-column-inner-bg a';
			},

			/**
			 * SIframe loaded event.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			iframeLoaded: function() {
				this.linkListeners();
				FusionEvents.trigger( 'fusion-iframe-loaded' );
			},

			/**
			 * Sets active key modifier
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			keyActive: function( event ) {
				if ( event.ctrlKey || 17 == event.keyCode || 91 == event.keyCode || 93 == event.keyCode ) {
					this.modifierActive = true;
				}
			},

			/**
			 * Resets active key modifier
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			keyInactive: function( event ) {
				if ( event.ctrlKey || 17 == event.keyCode || 91 == event.keyCode || 93 == event.keyCode ) {
					this.modifierActive = false;
				}
			},

			/**
			 * Hides frame loader.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			hideLoader: function() {
				jQuery( '#fb-preview-loader' ).removeClass( 'fusion-loading' );
				jQuery( '#fusion-frontend-builder-toggle-global-panel, #fusion-frontend-builder-toggle-global-page-settings' ).css( 'pointer-events', 'auto' );
			},

			/**
			 * Shows frame loader.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			showLoader: function() {

				if ( jQuery( 'body' ).hasClass( 'expanded' ) ) {
					jQuery( '#fb-preview-loader' ).css( 'width', 'calc(100% - ' + jQuery( '#customize-controls' ).width() + 'px)' );
				} else {
					jQuery( '#fb-preview-loader' ).css( 'width', '100%' );
				}

				jQuery( '#fusion-frontend-builder-toggle-global-panel, #fusion-frontend-builder-toggle-global-page-settings' ).css( 'pointer-events', 'none' );
				jQuery( '#fb-preview-loader' ).addClass( 'fusion-loading' );
			},

			/**
			 * Corrects the position of builder layout tooltips when they would overflow the modals.
			 *
			 * @since 2.1
			 * @return {void}
			 */
			correctLayoutTooltipPosition: function() {
				jQuery( document ).on( 'mouseenter', '.fusion-layout-buttons .fusion-builder-layout-button-load-dialog', function() {
					var tooltip                        = jQuery( this ).find( '.fusion-builder-load-template-dialog-container' ),
						tooltipOffsetLeft              = tooltip.offset().left,
						tooltipWidth                   = tooltip.outerWidth(),
						tooltipOffsetRight             = tooltipOffsetLeft + tooltipWidth,
						modalContentWrapper            = jQuery( this ).closest( '.ui-dialog-content' ),
						modalContentWrapperOffsetLeft  = modalContentWrapper.offset().left,
						modalContentWrapperWidth       = modalContentWrapper.outerWidth(),
						modalContentWrapperOffsetRight = modalContentWrapperOffsetLeft + modalContentWrapperWidth;

					if ( tooltipOffsetRight > modalContentWrapperOffsetRight ) {
						jQuery( this ).find( '.fusion-builder-load-template-dialog' ).css( 'left', '-' + ( tooltipOffsetRight - modalContentWrapperOffsetRight + 20 ) + 'px' );
					}
				} );

				jQuery( document ).on( 'mouseleave', '.fusion-layout-buttons .fusion-builder-layout-button-load-dialog', function() {
					jQuery( this ).find( '.fusion-builder-load-template-dialog' ).css( 'left', '' );
				} );

			},

			/**
			 * Inits studio previews.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			initStudioPreview: function() {

				// Studio preview.
				jQuery( 'body' ).on( 'click', '.studio-wrapper .fusion-page-layout:not(.awb-demo-pages-layout) img', function( event ) {
					var $item    = jQuery( event.currentTarget ).closest( '.fusion-page-layout' ),
						url      = $item.data( 'url' ),
						$wrapper = $item.closest( '.studio-wrapper' ),
						layoutID = $item.data( 'layout-id' );

					$wrapper.addClass( 'loading fusion-studio-preview-active' );
					$wrapper.find( '.fusion-loader' ).show();
					$wrapper.append( '<iframe class="awb-studio-preview-frame" src="' + url + '" frameBorder="0" scrolling="auto" onload="FusionApp.studioPreviewLoaded();" allowfullscreen=""></iframe>' );
					$wrapper.find( '.awb-import-options' ).addClass( 'open' );
					$wrapper.data( 'layout-id', layoutID );
				} );

				// Remove studio preview.
				jQuery( 'body' ).on( 'click', '.fusion-studio-preview-back', function( event ) {
					var $wrapper = jQuery( event.currentTarget ).closest( '.studio-wrapper' );

					event.preventDefault();

					$wrapper.removeClass( 'fusion-studio-preview-active' );
					$wrapper.find( '.awb-studio-preview-frame' ).remove();
					$wrapper.find( '.awb-import-options' ).removeClass( 'open' );
					$wrapper.removeData( 'layout-id' );
				} );

				// Import in preview.
				jQuery( 'body' ).on( 'click', '.fusion-studio-preview-active .awb-import-studio-item-in-preview', function( event ) {
					var $wrapper = jQuery( event.currentTarget ).closest( '.studio-wrapper ' ),
						dataID = $wrapper.data( 'layout-id' );

					event.preventDefault();

					jQuery( '.fusion-studio-preview-active .fusion-studio-preview-back' ).trigger( 'click' );
					jQuery( '.fusion-page-layout[data-layout-id="' + dataID + '"]' ).find( '.awb-import-studio-item' ).trigger( 'click' );
				} );
			},

			/**
			 * Actions to perform when studio preview is loaded.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			studioPreviewLoaded: function() {
				if ( 'object' === typeof FusionApp ) {
					this.previewColors();
				} else {
					jQuery( '.studio-wrapper' ).removeClass( 'loading' ).find( '.fusion-loader' ).hide();
				}
			},

			/**
			 * Trigger preview colors to update on preview.
			 *
			 * @since 3.7
			 * @return {void}
			 */
			previewColors: function() {
				var styleObject = getComputedStyle( document.getElementById( 'fb-preview' ).contentWindow.document.documentElement ),
					overWriteType    = jQuery( '.awb-import-options input[name="overwrite-type"]:checked' ).val(),
					shouldInvert     = jQuery( '.awb-import-options input[name="invert"]:checked' ).val(),
					varData          = {
						color_palette: {},
						typo_sets: {},
						shouldInvert: shouldInvert
					};

				varData = this.getOverWritePalette( varData, styleObject, overWriteType, shouldInvert );
				varData = this.getOverWriteTypography( varData, styleObject, overWriteType );

				jQuery( '.awb-studio-preview-frame' )[ 0 ].contentWindow.postMessage( varData, '*' );

				// Remove loading from preview.
				jQuery( '.studio-wrapper' ).removeClass( 'loading' ).find( '.fusion-loader' ).hide();
			},

			/**
			 * Gets overwrite palette.
			 *
			 * @since 3.7
			 * @param {Object} varData       The var data.
			 * @param {Object} styleObject   The style object.
			 * @param {String} overWriteType The overwrite type.
			 * @param {String} shouldInvert  If should invert or not.
			 * @return {object}
			 */
			getOverWritePalette: function( varData, styleObject, overWriteType, shouldInvert ) {
				if ( 'inherit' === overWriteType ) {
					switch ( shouldInvert ) {
					case 'dont-invert':
						for ( let step = 1; 9 > step; step++ ) {
							varData.color_palette[ '--awb-color' + step ]        = styleObject.getPropertyValue( '--awb-color' + step );
							varData.color_palette[ '--awb-color' + step + '-h' ] = styleObject.getPropertyValue( '--awb-color' + step + '-h' );
							varData.color_palette[ '--awb-color' + step + '-s' ] = styleObject.getPropertyValue( '--awb-color' + step + '-s' );
							varData.color_palette[ '--awb-color' + step + '-l' ] = styleObject.getPropertyValue( '--awb-color' + step + '-l' );
							varData.color_palette[ '--awb-color' + step + '-a' ] = styleObject.getPropertyValue( '--awb-color' + step + '-a' );
						}
						break;
					case 'do-invert':
						for ( let i = 1, revI = 8; 8 >= i; i++, revI-- ) {
							varData.color_palette[ '--awb-color' + i ]        = styleObject.getPropertyValue( '--awb-color' + revI );
							varData.color_palette[ '--awb-color' + i + '-h' ] = styleObject.getPropertyValue( '--awb-color' + revI + '-h' );
							varData.color_palette[ '--awb-color' + i + '-s' ] = styleObject.getPropertyValue( '--awb-color' + revI + '-s' );
							varData.color_palette[ '--awb-color' + i + '-l' ] = styleObject.getPropertyValue( '--awb-color' + revI + '-l' );
							varData.color_palette[ '--awb-color' + i + '-a' ] = styleObject.getPropertyValue( '--awb-color' + revI + '-a' );
						}
						break;
					}

					return varData;
				}


				return varData;
			},

			/**
			 * Gets typography overwrite.
			 *
			 * @since 3.7
			 * @param {Object} varData       The var data.
			 * @param {Object} styleObject   The style object.
			 * @param {String} overWriteType The overwrite type.
			 * @return {object}
			 */
			getOverWriteTypography: function( varData, styleObject, overWriteType ) {
				const subsets = [
					'font-family',
					'font-size',
					'font-weight',
					'font-style',
					'font-variant',
					'line-height',
					'letter-spacing',
					'text-transform'
				];

				if ( 'inherit' !== overWriteType ) {
					return varData;
				}

				// Global typography sets.
				for ( let step = 1; 6 > step; step++ ) {
					subsets.forEach( function( subset ) {
						subset = '--awb-typography' + step + '-' + subset;
						const value = styleObject.getPropertyValue( subset );
						if ( '' !== value ) {
							varData.typo_sets[ subset ] = value;
						}
					} );
				}

				// Headings typography.
				for ( let step = 1; 7 > step; step++ ) {
					subsets.forEach( function( subset ) {
						subset = '--h' + step + '_typography-' + subset;
						const value = styleObject.getPropertyValue( subset );
						if ( '' !== value ) {
							varData.typo_sets[ subset ] = value;
						}
					} );
				}

				return varData;
			},

			/**
			 * Listen for heartbeat changes to ensure user is logged in.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			setHeartbeatListeners: function() {
				var self = this;

				// Refresh nonces if they have signed back in.
				jQuery( document ).on( 'heartbeat-tick', function( event, data ) {

					// We have newly lost connection, set state and fire event.
					if ( 'undefined' !== typeof data[ 'wp-auth-check' ] && false === data[ 'wp-auth-check' ] && FusionApp.connected ) {
						self.connected = false;
						FusionEvents.trigger( 'fusion-disconnected' );
						window.adminpage = 'post-php';
					}

					// We have regained connection - refresh nonces, set state and fire event.
					if ( 'undefined' !== typeof data.fusion_builder ) {
						fusionAppConfig.fusion_load_nonce = data.fusion_builder.fusion_load_nonce;
						self.connected = true;
						delete window.adminpage;
						FusionEvents.trigger( 'fusion-reconnected' );
					}
				} );
			},

			renderUI: function() {

				// Panel.
				if ( 'undefined' !== typeof FusionPageBuilder.SidebarView ) {
					this.sidebarView = new FusionPageBuilder.SidebarView();
					jQuery( '.fusion-builder-panel-main' ).append( this.sidebarView.render().el );
				}

				// Icon picker pre-init.
				this.iconPicker();
			},

			/**
			 * Main init setup trigger for app. Fired from preview frame.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			setup: function() {

				this.previewWindow = jQuery( '#fb-preview' )[ 0 ].contentWindow;

				this.updateData();

				jQuery( 'body' ).append( this.toolbarView.render( ).el );

				// Start Builder
				if ( 'undefined' !== typeof FusionPageBuilder.AppView && this.getPost( 'post_type' ) && this.isEditable() ) {

					this.builderActive = true;

					// eslint-disable-next-line vars-on-top
					var hasOverrideContent	= this.data.template_override && this.data.template_override.content,
						overrideContent		= hasOverrideContent && this.data.template_override.content.post_content;

					if ( 'fusion_tb_section' !== this.data.query.post_type && hasOverrideContent && overrideContent && ! overrideContent.includes( 'fusion_tb_content' ) ) {
						this.hasEditableContent = false;
					}

					if ( 'undefined' === typeof FusionPageBuilderApp ) {

						window.FusionPageBuilderApp = new FusionPageBuilder.AppView( { // jshint ignore: line
							el: jQuery( '#fb-preview' ).contents().find( '.fusion-builder-live' )
						} );

						// Builder toolbar
						if ( 'undefined' !== typeof FusionPageBuilder.BuilderToolbar ) {
							this.builderToolbarView = new FusionPageBuilder.BuilderToolbar();
							this.toolbarView.render();
						}

					} else {
						FusionPageBuilderApp.fusionBuilderReset();
						FusionPageBuilderApp.$el = jQuery( '#fb-preview' ).contents().find( '.fusion-builder-live' );
						FusionPageBuilderApp.render();
					}

					FusionPageBuilderApp.initialBuilderLayout( this.data );

					this.listenTo( FusionEvents, 'fusion-builder-loaded', this.hideLoader );
				} else {
					this.builderActive = false;
					jQuery( document.getElementById( 'fb-preview' ).contentWindow.document ).ready( this.hideLoader );
				}

				FusionEvents.trigger( 'fusion-app-setup' );

				this.listenForLeave();

				if ( this.sidebarView || 'undefined' !== typeof FusionPageBuilderApp ) {
					this.createMapObjects();
				}

				jQuery( '#fb-preview' ).removeClass( 'refreshing' );

				if ( 'undefined' !== typeof this.hotkeys ) {
					this.hotkeys.attachListener();
				}
			},

			isEditable: function() {
				return -1 !== builderConfig.allowed_post_types.indexOf( this.getPost( 'post_type' ) ) || 'post_cards' === FusionApp.data.fusion_element_type || 'mega_menus' === FusionApp.data.fusion_element_type || true === FusionApp.data.is_shop;
			},

			linkListeners: function() {
				var self = this;

				// Events calendar events page tweaks.
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#tribe-events' ).off();
				if ( 'undefined' !== typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.tribe_ev ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( jQuery( '#fb-preview' )[ 0 ].contentWindow.tribe_ev.events ).on( 'post-collect-bar-params.tribe', function() {
						var linkHref = jQuery( '#fb-preview' )[ 0 ].contentWindow.tribe_ev.state.cur_url;
						if ( -1 !== linkHref.indexOf( '?' ) ) {
							linkHref = linkHref + '&builder=true&builder_id=' + self.builderId;
						} else {
							linkHref = linkHref + '?builder=true&builder_id=' + self.builderId;
						}
						jQuery( '#fb-preview' )[ 0 ].contentWindow.tribe_ev.state.cur_url = linkHref;
						self.showLoader();
					} );
				}

				jQuery( '#fb-preview' ).contents().on( 'click', this.linkSelectors, function( event ) {
					event.preventDefault();
					self.checkLink( event );
				} );
			},

			/**
			 * Listen for closing or history change.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			listenForLeave: function() {
				document.getElementById( 'fb-preview' ).contentWindow.addEventListener( 'beforeunload', this.leavingAlert.bind( this ) );
				window.addEventListener( 'beforeunload', this.leavingAlert.bind( this ) );
				this.manualSwitch = false;
			},

			/**
			 * Check if we should show a warning message.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			leavingAlert: function( event ) {
				if ( this.hasContentChanged() && ! this.manualSwitch ) {
					event.returnValue = fusionBuilderText.changes_will_be_lost;
				}
			},

			/**
			 * Saves the post-content.
			 *
			 * @since 2.0.0
			 * @param {Object} successAction - Action object, containing action name and params.
			 * @return {void}
			 */
			savePostContent: function( successAction ) {
				var self     = this,
					postData = this.getAjaxData( 'fusion_app_save_post_content' ),
					width    = jQuery( '.fusion-builder-save-page' ).outerWidth() + jQuery( '.fusion-exit-builder' ).outerWidth(),
					button   = jQuery( '.fusion-builder-save-page' );

				button.toggleClass( 'sending' ).blur();

				if ( 'object' === typeof successAction && 'undefined' !== typeof successAction.action && ( 'switch_page' === successAction.action || 'exit_builder' === successAction.action ) ) {
					jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).css( 'top', '54px' );
					jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).before( '<div class="fusion-builder-confirmation-modal-save"></div>' );
					jQuery( '.fusion-builder-confirmation-modal-save' ).attr( 'style', 'width:calc(100% - ' + width + 'px);' );
				}

				jQuery.ajax( {
					type: 'POST',
					url: fusionAppConfig.ajaxurl,
					dataType: 'json',
					data: postData
				} )
					.done( function( data ) {
						if ( 'object' !== typeof data ) {
							return;
						}

						if ( data.success && 'undefined' === typeof data.data.failure ) {

							// Save was successful.
							button.removeClass( 'sending' ).blur();
							button.addClass( 'success' );

							// Switch to new page after content was saved.
							if ( 'object' === typeof successAction && 'undefined' !== typeof successAction.action && 'switch_page' === successAction.action ) {
								self.switchPage( successAction.builderid, successAction.linkhref, successAction.linkhash );
							} else if ( 'object' === typeof successAction &&  'undefined' !== typeof successAction.action && 'exit_builder' === successAction.action ) {
								self.manualSwitch    = true;
								window.location.href = successAction.link;
							} else {
								setTimeout( function() {
									button.removeClass( 'success' );
									FusionApp.contentReset();
								}, 2000 );
								FusionEvents.trigger( 'fusion-app-saved' );
							}
						} else if ( 'undefined' !== typeof data.data.failure && ( 'logged_in' === data.data.failure || 'nonce_check' === data.data.failure ) ) {

							// Save failed because user is not logged in, trigger heartbeat for log in form.
							jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).css( 'top', '' );
							jQuery( '.fusion-builder-confirmation-modal-save' ).remove();
							self.hideLoader();
							button.removeClass( 'sending' ).blur();
							button.addClass( 'failed' );
							if ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.heartbeat ) {
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );
								wp.heartbeat.connectNow();
							} else {

								// No heartbeat warning.
								FusionApp.confirmationPopup( {
									title: fusionBuilderText.page_save_failed,
									content: fusionBuilderText.authentication_no_heartbeat,
									type: 'error',
									icon: '<i class="fusiona-exclamation-triangle" aria-hidden="true"></i>',
									actions: [
										{
											label: fusionBuilderText.ok,
											classes: 'save yes',
											callback: function() {

												// Try again just in case.
												if ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.heartbeat ) {
													wp.heartbeat.connectNow();
												}
												FusionApp.confirmationPopup( {
													action: 'hide'
												} );
											}
										}
									]
								} );
							}
						} else {

							// Save failed for another reason, provide details.
							jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).css( 'top', '' );
							jQuery( '.fusion-builder-confirmation-modal-save' ).remove();
							self.hideLoader();
							button.removeClass( 'sending' ).blur();
							button.addClass( 'failed' );
							setTimeout( function() {
								button.removeClass( 'failed' );
							}, 2000 );
							FusionApp.confirmationPopup( {
								title: fusionBuilderText.problem_saving,
								content: fusionBuilderText.changes_not_saved + self.getSaveMessages( data.data ),
								type: 'error',
								icon: '<i class="fusiona-exclamation-triangle" aria-hidden="true"></i>',
								actions: [
									{
										label: fusionBuilderText.ok,
										classes: 'save yes',
										callback: function() {
											if ( 'undefined' !== typeof wp && 'undefined' !== typeof wp.heartbeat ) {
												wp.heartbeat.connectNow();
											}
											FusionApp.confirmationPopup( {
												action: 'hide'
											} );
										}
									}
								]
							} );
						}
					} );
			},

			/**
			 * List out the save data.
			 *
			 * @since 2.0.0
			 * @param {Object} data - The success/fail data.
			 * @return {string} - Returns HTML.
			 */
			getSaveMessages: function( data ) {
				var returnMessages = '';

				if ( 'object' === typeof data.failure ) {
					_.each( data.failure, function( messages ) {
						if ( 'string' === typeof messages ) {
							returnMessages += '<li class="failure"><i class="fusiona-exclamation-triangle" aria-hidden="true"></i>' + messages + '</li>';
						} else if ( 'object' === typeof messages ) {
							_.each( messages, function( message ) {
								if ( 'string' === typeof message ) {
									returnMessages += '<li class="failure"><i class="fusiona-exclamation-triangle" aria-hidden="true"></i>' + message + '</li>';
								}
							} );
						}
					} );
				}

				if ( 'object' === typeof data.success ) {
					_.each( data.success, function( messages ) {
						if ( 'string' === typeof messages ) {
							returnMessages += '<li class="success"><i class="fusiona-check" aria-hidden="true"></i>' + messages + '</li>';
						} else if ( 'object' === typeof messages ) {
							_.each( messages, function( message ) {
								if ( 'string' === typeof message ) {
									returnMessages += '<li class="success"><i class="fusiona-check" aria-hidden="true"></i>' + message + '</li>';
								}
							} );
						}
					} );
				}

				if ( '' !== returnMessages ) {
					return '<ul class="fusion-save-data-list">' + returnMessages + '</ul>';
				}

				return '';
			},

			/**
			 * Maps settings to params & page-options.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			createMapObjects: function() {

				// Create the settings to params object.
				if ( ! this.settingsToParams && 'undefined' !== typeof FusionPageBuilderApp ) {
					this.createSettingsToParams();
				}

				// Create the settings to extras object.
				if ( ! this.settingsToExtras && 'undefined' !== typeof FusionPageBuilderApp ) {
					this.createSettingsToExtras();
				}

				// Create the settings to page options object.
				if ( ! this.settingsToPo ) {
					this.createSettingsToPo();
				}
			},

			/**
			 * Maps settings to settingsToParams.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			createSettingsToParams: function() {
				var settingsToParams = {},
					paramObj;

				_.each( fusionAllElements, function( element, elementID ) {
					if ( ! _.isUndefined( element.settings_to_params ) ) {
						_.each( element.settings_to_params, function( param, setting ) {
							paramObj = {
								param: _.isObject( param ) && ! _.isUndefined( param.param ) ? param.param : param,
								callback: param.callback || false,
								element: elementID
							};
							if ( _.isObject( settingsToParams[ setting ] ) ) {
								settingsToParams[ setting ].push( paramObj );
							} else {
								settingsToParams[ setting ] = [];
								settingsToParams[ setting ].push( paramObj );
							}
						} );
					}
				} );

				this.settingsToParams = settingsToParams;
			},

			/**
			 * Maps settings to settingsToExtras.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			createSettingsToExtras: function() {
				var settingsToExtras = {},
					paramObj;

				_.each( fusionAllElements, function( element, elementID ) {
					if ( ! _.isUndefined( element.settings_to_extras ) ) {
						_.each( element.settings_to_extras, function( param, setting ) {
							paramObj = {
								param: _.isObject( param ) && ! _.isUndefined( param.param ) ? param.param : param,
								callback: param.callback || false,
								element: elementID
							};
							if ( _.isObject( settingsToExtras[ setting ] ) ) {
								settingsToExtras[ setting ].push( paramObj );
							} else {
								settingsToExtras[ setting ] = [];
								settingsToExtras[ setting ].push( paramObj );
							}
						} );
					}
				} );

				this.settingsToExtras = settingsToExtras;
			},

			/**
			 * Maps settings to settingsToPo.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			createSettingsToPo: function() {
				var settingsToPo = {},
					settingsPoTo = {},
					paramObj;

				_.each( this.data.fusionPageOptions, function( tab, tabID ) {
					_.each( tab.fields, function( option, optionID ) {
						if ( ! _.isUndefined( option.to_default ) ) {

							paramObj = {
								to: _.isObject( option.to_default ) && ! _.isUndefined( option.to_default.id ) ? option.to_default.id : option.to_default,
								callback: option.to_default.callback || false,
								option: optionID,
								tab: tabID
							};

							// Process settingsToPo
							if ( _.isObject( settingsToPo[ paramObj.to ] ) ) {
								settingsToPo[ paramObj.to ].push( paramObj );
							} else {
								settingsToPo[ paramObj.to ] = [];
								settingsToPo[ paramObj.to ].push( paramObj );
							}

							// Process settingsPoTo
							if ( _.isObject( settingsPoTo[ optionID ] ) ) {
								settingsPoTo[ optionID ] = paramObj.to;
							} else {
								settingsPoTo[ optionID ] = [];
								settingsPoTo[ optionID ] = paramObj.to;
							}
						}
					} );
				} );
				this.settingsToPo = settingsToPo;
				this.settingsPoTo = settingsPoTo;
			},

			/**
			 * Update the app data with preview data on load or page change.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			updateData: function() {

				// Language is different.
				if ( 'undefined' !== typeof this.data.language && 'undefined' !== typeof this.initialData.languageTo && this.initialData.language !== this.data.language && 'undefined' !== typeof FusionApp.sidebarView ) {
					this.languageSwitch();
				}

				if ( this.getPost( 'post_id' ) === this.initialData.postDetails.post_id ) {

					this.data.samePage = true;

				} else {

					// Set correct url in browser and history.
					this.updateURL( this.initialData.postDetails.post_permalink );

					this.data = this.initialData;

					this.data.samePage             = false;
					this.contentReset( 'page' );
					this.storedPoCSS               = false;
					this.customSave = {};

					FusionEvents.trigger( 'fusion-history-clear' );

					// If toolbar exists and language set, update switcher.
					if ( false !== this.toolbarView && this.data.language ) {
						this.toolbarView.updateLanguageSwitcher();
					}

					FusionEvents.trigger( 'fusion-data-updated' );
				}
			},

			/**
			 * Get post details by key or on its own.
			 *
			 * @since 2.0.0
			 * @param {string} key - The key we want to get from postDetails. If undefined all postDetails will be fetched.
			 * @return {mixed} - Returns postDetails[ key ] if a key is defined, otherwise return postDetails.
			 */
			getPost: function( key ) {
				if ( 'object' !== typeof this.data.postDetails ) {
					return false;
				}
				if ( 'undefined' === typeof key ) {
					return jQuery.extend( true, {}, this.data.postDetails );
				}
				if ( 'undefined' === typeof this.data.postDetails[ key ] ) {
					return false;
				}
				return this.data.postDetails[ key ];
			},

			/**
			 * Get post details by key or on its own.
			 *
			 * @since 2.0.0
			 * @param {string} key - The key we want to get from postDetails. If undefined all postDetails will be fetched.
			 * @return {mixed} - Returns postDetails[ key ] if a key is defined, otherwise return postDetails.
			 */
			getDynamicPost: function( key ) {
				if ( 'post_meta' === key ) {
					if ( 'object' !== typeof this.data.examplePostDetails ) {
						return FusionApp.data.postMeta;
					}
					return this.data.examplePostDetails.post_meta;
				}
				if ( ( 'fusion_tb_section' === FusionApp.data.postDetails.post_type || 'post_cards' === FusionApp.data.fusion_element_type || 'awb_off_canvas' === FusionApp.data.postDetails.post_type ) && 'undefined' !== typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion.dynamic_content_preview_type && 'undefined' !== typeof FusionApp.initialData.dynamicPostID ) {
					return FusionApp.initialData.dynamicPostID;
				}
				if ( 'object' !== typeof this.data.examplePostDetails ) {
					return this.getPost( key );
				}
				if ( 'undefined' === typeof key ) {
					return jQuery.extend( true, {}, this.data.examplePostDetails );
				}
				if ( 'undefined' == typeof this.data.examplePostDetails[ key ] ) {
					return this.getPost( key );
				}
				return this.data.examplePostDetails[ key ];
			},

			/**
			 * Set post details by key.
			 *
			 * @since 2.0.0
			 * @param {string} key - The key of the property we want to set.
			 * @param {string} value - The value of the property we want to set.
			 * @return {void}
			 */
			setPost: function( key, value ) {
				if ( 'object' !== typeof this.data.postDetails ) {
					this.data.postDetails = {};
				}
				this.data.postDetails[ key ] = value;
			},

			/**
			 * Get preview url.
			 *
			 * @since 2.0.0
			 * @return {string} - URL.
			 */
			getPreviewUrl: function() {
				return FusionApp.previewWindow.location.href.replace( 'builder=true', 'builder=false&fbpreview=true' );
			},

			/**
			 * Updates language specific options.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			languageSwitch: function() {

				// Save defaults and edited TO.
				this.editedDefaults[ this.data.language ] = jQuery.extend( true, {}, fusionAllElements );
				this.editedTo[ this.data.language ]       = jQuery.extend( true, {}, FusionApp.settings );

				// Change setting values to those of new language.
				if ( 'undefined' !== typeof this.editedTo[ this.initialData.language ] ) {
					FusionApp.settings = this.editedTo[ this.initialData.language ];
				} else {
					FusionApp.settings = this.initialData.languageTo;
				}

				// Change option name to option for new language.
				window.fusionOptionName = this.initialData.optionName;

				// Restore element defaults, eg button color.
				if ( 'undefined' !== typeof this.editedDefaults[ this.initialData.language ] ) {
					window.fusionAllElements = jQuery.extend( true, {}, this.editedDefaults[ this.initialData.language ] );
				} else if ( 'undefined' !== typeof this.initialData.languageDefaults ) {
					window.fusionAllElements = jQuery.extend( true, fusionAllElements, this.initialData.languageDefaults );
				} else {
					window.fusionAllElements = jQuery.extend( true, {}, this.elementDefaults );
				}

				// Rebuilder sidebar views for new values.
				FusionApp.sidebarView.refreshTo();
			},

			/**
			 * Triggers a full-refresh of the preview iframe.
			 *
			 * @since 2.0.0
			 * @param {string} target - Target URL to load.
			 * @param {Object} event - Event on click that triggered.
			 * @param {Object} postDetails - Post details which should be used on refresh.
			 * @return {void}
			 */
			fullRefresh: function( target, event, postDetails ) {
				this.showLoader();

				target = 'undefined' === typeof target ? false : target;
				event  = 'undefined' === typeof event  ? {}    : event;

				this.setGoogleFonts();
				this.reInitIconPicker();

				this.doTheFullRefresh( target, event, postDetails );
			},

			/**
			 * Sets builder status in post meta..
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			setBuilderStatus: function() {
				var builderStatus = false,
					savedStatus   = 'undefined' !== typeof this.data.postMeta.fusion_builder_status ? this.data.postMeta.fusion_builder_status : false;

				if ( 'undefined' !== typeof FusionPageBuilderApp ) {
					builderStatus = 'active';
				}

				if ( builderStatus !== savedStatus ) {
					this.data.postMeta.fusion_builder_status = builderStatus;
					this.contentChange( 'page', 'page-option' );
				}
			},

			/**
			 * Get changed data for ajax requests.
			 *
			 * @since 2.0.0
			 * @param {string} action - The ajax action.
			 * @param {Object} postDetails - Post details which should be used on refresh.
			 * @return {Object} - Returns the postData.
			 */
			getAjaxData: function( action, postDetails ) {
				var postData = {
					post_id: this.getPost( 'post_id' ),
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					custom: jQuery.param( this.customSave ),
					builder_id: this.builderId
				};

				if ( 'fusion_app_full_refresh' !== action && 'fusion_app_preview_only' !== action ) {
					postData.query = FusionApp.data.query;
				}

				if ( 'undefined' === typeof postDetails ) {
					postDetails = {};
				}

				// Set the action if set.
				if ( 'string' === typeof action ) {
					postData.action = action;
				}

				// If page settings have changed then add them, but without post_content.
				if ( this.hasContentChanged( 'page', 'page-setting' ) ) {
					postData.post_details = this.getPost();
					if ( 'undefined' !== typeof postData.post_details.post_content ) {
						delete postData.post_details.post_content;
					}
				}

				// If FB is active and post_content has changed.
				if ( 'undefined' !== typeof FusionPageBuilderApp && this.hasContentChanged( 'page', 'builder-content' ) ) {

					if ( 'undefined' !== typeof postDetails.post_content ) {
						postData.post_content = postDetails.post_content;
					} else {
						FusionPageBuilderApp.builderToShortcodes();
						postData.post_content = this.getPost( 'post_content' ); // eslint-disable-line camelcase
					}

					this.setGoogleFonts();
				}

				this.setBuilderStatus();

				// If Avada panel exists and either TO or PO has changed.
				if ( this.sidebarView && ( this.hasContentChanged( 'global', 'theme-option' ) || this.hasContentChanged( 'page', 'page-option' ) ) ) {

					this.reInitIconPicker();

					if ( this.hasContentChanged( 'global', 'theme-option' ) ) {
						postData.fusion_options = jQuery.param( this.maybeEmptyArray( FusionApp.settings ) ); // eslint-disable-line camelcase
					}

					if ( this.hasContentChanged( 'page', 'page-option' ) ) {
						postData.meta_values = jQuery.param( this.data.postMeta ); // eslint-disable-line camelcase
					}
				}

				if ( 'object' === typeof postData.post_details ) {
					postData.post_details = jQuery.param( postData.post_details ); // eslint-disable-line camelcase
				}

				// Option name for multilingual saving.
				if ( 'undefined' !== typeof fusionOptionName ) {
					postData.option_name = fusionOptionName;
				}

				if ( 'object' === typeof FusionApp.data.examplePostDetails && 'undefined' !== typeof FusionApp.data.examplePostDetails.post_id ) {
					postData.target_post = FusionApp.data.examplePostDetails.post_id;
				}

				return postData;
			},

			/**
			 * Triggers a full-refresh of the preview iframe.
			 *
			 * @since 2.0.0
			 * @param {string} target - Target URL to load.
			 * @param {Object} event - Event on click that triggered.
			 * @param {Object} postDetails - Post details which should be used on refresh.
			 * @return {void}
			 */
			doTheFullRefresh: function( target, event, postDetails ) {
				var postData = this.getAjaxData( 'fusion_app_full_refresh', postDetails );

				this.refreshCounter = this.refreshCounter + 1;

				if ( jQuery( '.ui-dialog-content' ).length ) {
					jQuery( '.ui-dialog-content' ).dialog( 'close' );
				}

				jQuery( '#fb-preview' ).addClass( 'refreshing' );

				FusionEvents.trigger( 'fusion-preview-refreshed' );

				this.formPost( postData );
			},

			formPost: function( postData, newSrc, target ) {
				var $form = jQuery( '#refresh-form' ),
					src   = 'undefined' === typeof newSrc || ! newSrc ? jQuery( '#fb-preview' ).attr( 'src' ) : newSrc;

				$form.empty();

				if ( 'string' !== typeof target ) {
					target = jQuery( '#fb-preview' ).attr( 'name' );
					this.previewWindow.name = target;
				}

				$form.attr( 'target', target );
				$form.attr( 'action', src );

				_.each( postData, function( value, id ) {
					if ( 'post_content' === id ) {
						value = window.encodeURIComponent( value );
					}
					$form.append( '<input type="hidden" name="' + id + '" value="' + value + '" />' );
				} );

				this.manualSwitch = true;

				$form.submit().empty();
			},

			/**
			 * Refreshes the preview frame.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			previewRefresh: function() {
				var self          = this,
					originalCount = self.refreshCounter - 1,
					refreshString = '&refresh=' + originalCount;

				this.manualSwitch = true;

				jQuery( '#fb-preview' ).attr( 'src', function( i, val ) {
					if ( -1 === val.indexOf( '&post_id=' ) ) {
						val += '&post_id=' + self.getPost( 'post_id' );
					}

					// Make sure to add unique refresh parameter.
					if ( -1 === val.indexOf( refreshString ) ) {
						val += '&refresh=' + self.refreshCounter;
					} else {
						val = val.replace( refreshString, '&refresh=' + self.refreshCounter );
					}

					return val;
				} );

				FusionEvents.trigger( 'fusion-preview-refreshed' );
			},

			/**
			 * Checks links.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The jQuery event.
			 * @param {string} href - URL.
			 * @return {void}
			 */
			checkLink: function( event, href ) {
				var self           = this,
					linkHref       = 'undefined' === typeof href ? jQuery( event.currentTarget ).attr( 'href' ) : href,
					linkHash       = '',
					targetPathname = '',
					targetHostname = '',
					$targetEl      = this.previewWindow.jQuery( jQuery( event.currentTarget ) ),
					link,
					linkParts;

				event.preventDefault();

				// Split hash and move to end of URL.
				if ( -1 !== linkHref.indexOf( '#' ) ) {
					linkParts = linkHref.split( '#' );
					linkHref  = linkParts[ 0 ];
					linkHash  = '#_' + linkParts[ 1 ];
				}

				// Get path name from event (link).
				if ( 'object' === typeof event ) {
					targetPathname = event.currentTarget.pathname;
					targetHostname = event.currentTarget.hostname;
				}

				// If manually passing a url, get pathname from that instead.
				if ( 'undefined' !== typeof href ) {
					link           = document.createElement( 'a' );
					link.href      = href;
					targetPathname = link.pathname;
					targetHostname = link.hostname;
				}

				// Check for scroll links on same page and return.
				if ( '#' === linkHref.charAt( 0 ) || ( '' !== linkHash && targetPathname === location.pathname ) ) {
					if ( 'function' === typeof $targetEl.fusion_scroll_to_anchor_target && ! $targetEl.parent().parent().hasClass( 'wc-tabs' ) ) {
						$targetEl.fusion_scroll_to_anchor_target();
					}
					return;
				}

				// Check if flyout submenus are enabled and menu item has submenu.
				if ( $targetEl.parent().hasClass( 'menu-item' ) && $targetEl.parent().hasClass( 'menu-item-has-children' ) && $targetEl.closest( '.awb-menu' ).hasClass( 'awb-menu_flyout' ) ) {
					return;
				}

				// Check link is on same site or manually being triggered.
				if ( location.hostname === targetHostname || 'undefined' !== typeof href ) {

					this.showLoader();

					// Make user confirm.
					if ( this.hasContentChanged( 'page' ) ) {
						FusionApp.confirmationPopup( {
							title: fusionBuilderText.unsaved_changes,
							content: fusionBuilderText.changes_will_be_lost,
							class: 'fusion-confirmation-unsaved-changes',
							actions: [
								{
									label: fusionBuilderText.cancel,
									classes: 'cancel no',
									callback: function() {
										self.hideLoader();
										FusionApp.confirmationPopup( {
											action: 'hide'
										} );
									}
								},
								{
									label: fusionBuilderText.just_leave,
									classes: 'dont-save yes',
									callback: function() {
										self.switchPage( self.builderId, linkHref, linkHash );
									}
								},
								{
									label: fusionBuilderText.leave,
									classes: 'save yes',
									callback: function() {
										var successAction = {};

										successAction.action    = 'switch_page';
										successAction.builderid = self.builderId;
										successAction.linkhref  = linkHref;
										successAction.linkhash  = linkHash;

										self.savePostContent( successAction );

									}
								}
							]
						} );
					} else {
						self.switchPage( self.builderId, linkHref, linkHash );
					}
				}
			},

			switchPage: function( builderId, linkHref, linkHash ) {
				var postData = {};

				if ( jQuery( '.ui-dialog-content' ).length ) {
					jQuery( '.ui-dialog-content' ).dialog( 'close' );
				}

				jQuery( '#fb-preview' ).addClass( 'refreshing' );

				this.manualSwitch = true;

				if ( this.hasContentChanged( 'global', 'theme-option' ) ) {
					postData = {
						fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
						builder_id: this.builderId,
						action: 'fusion_app_switch_page',
						fusion_options: jQuery.param( FusionApp.settings ), // eslint-disable-line camelcase
						option_name: fusionOptionName // eslint-disable-line camelcase
					};

					jQuery( '#fb-preview' ).addClass( 'refreshing' );

					if ( -1 !== linkHref.indexOf( '?' ) ) {
						linkHref = linkHref + '&builder=true&builder_id=' + builderId + linkHash;
					} else {
						linkHref = linkHref + '?builder=true&builder_id=' + builderId + linkHash;
					}
					this.formPost( postData, linkHref );
				} else {
					this.goToURL( builderId, linkHref, linkHash );
				}
			},

			/**
			 * Goes to a URL.
			 *
			 * @param {string} builderId - The builder-ID.
			 * @param {string} linkHref - The URL.
			 * @param {string} linkHash - The hash part of the URL.
			 * @return {void}
			 */
			goToURL: function( builderId, linkHref, linkHash ) {
				var newPage;

				this.manualSwitch = true;

				// Close dialogs.
				if ( jQuery( '.ui-dialog-content' ).length ) {
					jQuery( '.ui-dialog-content' ).dialog( 'close' );
				}

				if ( jQuery( '#fusion-close-element-settings' ).length ) {
					jQuery( '#fusion-close-element-settings' ).trigger( 'click' );
				}

				jQuery( '#fusion-builder-confirmation-modal' ).hide();
				jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).hide();

				// Add necessary details to URL.
				if ( -1 !== linkHref.indexOf( '?' ) ) {
					newPage = linkHref + '&builder=true&builder_id=' + builderId + linkHash;
				} else {
					newPage = linkHref + '?builder=true&builder_id=' + builderId + linkHash;
				}

				// Change iframe URL.
				jQuery( '#fb-preview' ).attr( 'src', newPage );
			},

			/**
			 * Updates the URL.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			updateURL: function( newURL ) {
				var frameWindow   = document.getElementById( 'fb-preview' ).contentWindow,
					frameDocument = frameWindow.document;

				if ( '' === newURL || '?fb-edit=1' === newURL ) {
					newURL = jQuery( '#fb-preview' ).attr( 'src' ).split( '?' )[ 0 ] + '?fb-edit=1';
				}

				window.history.replaceState( { url: newURL }, frameDocument.title, newURL );
				document.title = frameDocument.title;
			},

			/**
			 * Removes scripts from markup and stores separately.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeScripts: function( content, cid ) {
				var $markup    = jQuery( '<div>' + content + '</div>' ),
					$scripts   = $markup.find( 'script' ),
					$injection = [];

				if ( $scripts.length ) {
					$scripts.each( function() {

						// Add script markup to injection var.
						if ( jQuery( this ).attr( 'src' ) ) {
							$injection.push( { type: 'src', value: jQuery( this ).attr( 'src' ) } );
						} else {
							$injection.push( { type: 'inline', value: jQuery( this ).html() } );
						}

						// Remove script from render.
						jQuery( this ).remove();
					} );

					this.scripts[ cid ] = $injection;
					return $markup.html();
				}
				return $markup.html();
			},

			/**
			 * Injects stored scripts.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			injectScripts: function( cid ) {
				var $body         = jQuery( '#fb-preview' ).contents().find( 'body' )[ 0 ],
					scripts       = this.scripts[ cid ],
					frameDocument = document.getElementById( 'fb-preview' ).contentWindow.document,
					oldWrite      = frameDocument.write, // jshint ignore:line
					self          = this,
					el,
					elId;

				// Turn document write off before partial request.
				frameDocument.write = function() {}; // eslint-disable-line no-empty-function

				if ( 'undefined' !== typeof scripts && scripts.length ) {
					_.each( scripts, function( script, id ) {
						elId = 'fusion-script-' + cid + '-' + id;

						// If it already exists, remove it.
						if ( jQuery( '#fb-preview' ).contents().find( 'body' ).find( '#' + elId ).length ) {
							jQuery( '#fb-preview' ).contents().find( 'body' ).find( '#' + elId ).remove();
						}

						// Create script on iframe.
						el = document.createElement( 'script' );
						el.setAttribute( 'type', 'text/javascript' );
						el.setAttribute( 'id', 'fusion-script-' + cid + '-' + id );
						if ( 'src' === script.type ) {
							el.setAttribute( 'src', script.value );
						} else {
							el.innerHTML = script.value;
						}

						// If this is a hubspot form, wait and then add to element.
						if ( 'inline' === script.type && -1 !== script.value.indexOf( 'hbspt.forms.create' ) ) {
							self.initHubSpotForm( script, cid, el );
							return;
						}

						$body.appendChild( el );
					} );
				}

				frameDocument.write = oldWrite; // jshint ignore:line
			},

			/**
			 * Init hubspot embed form.
			 *
			 * @since 2.2
			 * @return {void}
			 */
			initHubSpotForm: function( script, cid, el ) {
				var self         = this,
					timeoutValue = 'undefined' !== typeof FusionApp.previewWindow.hbspt ? 0 : 500,
					$element     = jQuery( '#fb-preview' ).contents().find( 'div[data-cid="' + cid + '"]' ).find( '.fusion-builder-element-content' ).first();

				// Keep a count of repetitions to avoid.
				this.hubspotRepeat = 'undefined' === this.hubspotRepeat ? 0 : this.hubspotRepeat + 1;
				if ( 5 < this.hubspotRepeat ) {
					return;
				}
				setTimeout( function() {
					if ( 'undefined' === typeof FusionApp.previewWindow.hbspt ) {
						self.initHubSpotForm( script, cid, el );
						return;
					}
					if ( $element.length ) {
						self.hubspotRepeat = 0;
						$element.find( '.hbspt-form' ).remove();
						$element[ 0 ].appendChild( el );
					}
				}, timeoutValue );
			},

			/**
			 * Deletes scripts from DOM when element is removed.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			deleteScripts: function( cid ) {
				var scripts = this.scripts[ cid ];

				if ( scripts ) {
					_.each( scripts, function( script, id ) {
						var elId = 'fusion-script-' + cid + '-' + id;

						// If it already exists, remove it.
						if ( jQuery( '#fb-preview' ).contents().find( 'body' ).find( '#' + elId ).length ) {
							jQuery( '#fb-preview' ).contents().find( 'body' ).find( '#' + elId ).remove();
						}
					} );
					delete this.scripts[ cid ];
				}
			},

			/**
			 * Filters elements on search.
			 *
			 * @since 2.0.0
			 * @param {Object} thisEl - jQuery DOM element.
			 * @return {void}
			 */
			elementSearchFilter: function( thisEl ) {
				var name,
					value;

				thisEl.find( '.fusion-elements-filter' ).on( 'change paste keyup', function() {

					if ( jQuery( this ).val() ) {
						value = jQuery( this ).val().toLowerCase();

						thisEl.find( '.fusion-builder-all-modules li, .studio-imports li' ).each( function() {
							var shortcode = jQuery( this ).find( '.fusion_module_label' ).length ? jQuery( this ).find( '.fusion_module_label' ).text().trim().toLowerCase() : '';

							name = jQuery( this ).find( '.fusion_module_title' ).text().trim().toLowerCase();

							// Also show portfolio on recent works search
							if ( 'portfolio' === name ) {
								name += ' recent works';
							}

							if ( 'fusion_imageframe' === shortcode ) {
								name += ' ' + fusionBuilderText.logo.trim().toLowerCase();
							}

							if ( -1 !== name.search( value ) || jQuery( this ).hasClass( 'spacer' ) ) {
								jQuery( this ).show();
							} else {
								jQuery( this ).hide();
							}
						} );
					} else {
						thisEl.find( '.fusion-builder-all-modules li' ).show();
						thisEl.find( '.studio-imports li' ).show();
					}
				} );
				setTimeout( function() {
					jQuery( '.fusion-elements-filter' ).focus();
				}, 50 );
			},

			/**
			 * Checks page content for element font families.
			 *
			 * @since 2.0.0
			 * @param object googleFonts
			 * @return {Object}
			 */
			setElementFonts: function( googleFonts ) {
				var postContent = this.getPost( 'post_content' ),
					regexp,
					fontProps,
					tempFonts = {},
					saveFonts = [];

				if ( 'string' === typeof postContent && '' !== postContent && -1 !== postContent.indexOf( 'fusion_font_' ) ) {
					regexp		= new RegExp( '(fusion_font_[^=]*=")([^"]*)"', 'g' );
					fontProps	= this.getPost( 'post_content' ).match( regexp );

					// Iterate through all font properties in post content and build font objects.
					_.each( fontProps, function( prop ) {
						var config 			= prop.slice( 0, -1 ).split( '="' ),
							key				= config[ 0 ],
							value			= config[ 1 ],
							optionId		= key.replace( /fusion_font_(family|variant)_/, '' ),
							fontProperty	= ( key.includes( 'fusion_font_variant_' ) && 'variant' ) || 'family';

						if ( '' === key && 'family' === fontProperty ) {
							return;
						}

						if ( 'object' !== typeof tempFonts[ optionId ] ) {
							tempFonts[ optionId ] = {};
						} else if ( 'family' === fontProperty && tempFonts[ optionId ].family ) {

							// If we are setting family again for something already in process, then save out incomplete and start fresh
							saveFonts.push( tempFonts[ optionId ] );
							tempFonts[ optionId ] = {};
						}

						tempFonts[ optionId ][ fontProperty ] = value;

						// If all three are set, add to save fonts and delete from temporary holder so others can be collected with same ID.
						if ( 'undefined' !== typeof tempFonts[ optionId ].family && 'undefined' !== typeof tempFonts[ optionId ].variant ) {
							saveFonts.push( tempFonts[ optionId ] );
							delete tempFonts[ optionId ];
						}
					} );

					// Check for incomplete ones with family and add them too.
					_.each( tempFonts, function( font, option ) {
						if ( 'undefined' !== typeof font.family && '' !== font.family ) {
							saveFonts.push( tempFonts[ option ] );
						}
					} );


					// Look all fonts for saving and save.
					_.each( saveFonts, function( font ) {
						if ( 'undefined' === typeof font.family || '' === font.family ) {
							return;
						}
						if ( 'undefined' === typeof googleFonts[ font.family ] ) {
							googleFonts[ font.family ] = {
								variants: []
							};
						}

						// Add the variant if it does not exist already.
						if ( 'string' === typeof font.variant && ! googleFonts[ font.family ].variants.includes( font.variant ) ) {
							googleFonts[ font.family ].variants.push( font.variant );
						}
					} );
				}

				return googleFonts;
			},

			/**
			 * Checks page content for font dependencies.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			setGoogleFonts: function() {
				var self        = this,
					googleFonts = {},
					fontFamily,
					$fontNodes  = jQuery( '#fb-preview' ).contents().find( '[data-fusion-google-font]' );

				googleFonts = this.setElementFonts( googleFonts );

				if ( $fontNodes.length ) {
					$fontNodes.each( function() {
						if ( 'undefined' === typeof googleFonts[ jQuery( this ).attr( 'data-fusion-google-font' ) ] ) {
							googleFonts[ jQuery( this ).attr( 'data-fusion-google-font' ) ] = {
								variants: []
							};
						}

						// Add the variant.
						if ( jQuery( this ).attr( 'data-fusion-google-variant' ) ) {
							googleFonts[ jQuery( this ).attr( 'data-fusion-google-font' ) ].variants.push( jQuery( this ).attr( 'data-fusion-google-variant' ) );
						}
					} );
				}

				// Delete global typographies. If is studio, then parse overwrite typography to add to meta.
				for ( fontFamily in googleFonts ) {
					if ( fontFamily.includes( 'var(' ) ) {

						// awbOriginalPalette is a variable present only on studio plugin.
						if ( window.awbOriginalPalette ) {
							addOverwriteTypographyToMeta( fontFamily );
						}
					}
				}

				// Check each has a variant selected
				_.each( googleFonts, function( font, family ) {
					if ( 'object' !== typeof font.variants || ! font.variants.length ) {
						googleFonts[ family ].variants = [ 'regular' ];
					}
				} );

				if ( 'object' === typeof this.data.postMeta._fusion_google_fonts ) {
					_.each( this.data.postMeta._fusion_google_fonts, function( fontData, currentFontFamily ) {
						_.each( fontData, function( values, key ) {
							self.data.postMeta._fusion_google_fonts[ currentFontFamily ][ key ] = _.values( values );
						} );
					} );

					// We have existing values and existing value is not the same as new.
					if ( ! _.isEqual( this.data.postMeta._fusion_google_fonts, googleFonts ) ) {

						if ( _.isEmpty( googleFonts ) ) {
							googleFonts = '';
						}
						this.data.postMeta._fusion_google_fonts = googleFonts; // eslint-disable-line camelcase
						this.contentChange( 'page', 'page-option' );
					}
				} else if ( ! _.isEmpty( googleFonts ) ) {

					// We do not have existing values and we do have fonts now.
					this.data.postMeta._fusion_google_fonts = googleFonts; // eslint-disable-line camelcase
					this.contentChange( 'page', 'page-option' );
				}

				function addOverwriteTypographyToMeta( globalVar ) {
					var typoMatch = globalVar.match( /--awb-typography(\d)/ ),
						fontName,
						fontVariant,
						uniqueFontVariant,
						variantMatch,
						i,
						typoId;

					if ( ! typoMatch[ 1 ] || ! Array.isArray( googleFonts[ globalVar ].variants ) ) {
						delete googleFonts[ globalVar ];
						return;
					}

					// Get the font family.
					typoId = typoMatch[ 1 ];
					fontName = awbTypoData.data[ 'typography' + typoId ][ 'font-family' ];
					fontVariant = [];

					// Get the global font variants and merge with non-global ones.
					for ( i = 0; i < googleFonts[ globalVar ].variants.length; i++ ) {
						if ( googleFonts[ globalVar ].variants[ i ].includes( 'var(' ) ) {
							variantMatch = googleFonts[ globalVar ].variants[ i ].match( /--awb-typography(\d)/ );

							if ( variantMatch[ 1 ] ) {
								if ( awbTypoData.data[ 'typography' + variantMatch[ 1 ] ].variant ) {
									fontVariant.push( awbTypoData.data[ 'typography' + variantMatch[ 1 ] ].variant );
								} else {
									fontVariant.push( '400' );
								}
							}

						} else {
							fontVariant.push( googleFonts[ globalVar ].variants[ i ] );
						}
					}

					// Update the font variant. If exist then concat them.
					if ( googleFonts[ fontName ] ) {
						if ( googleFonts[ fontName ].variants ) {
							googleFonts[ fontName ].variants = googleFonts[ fontName ].variants.concat( fontVariant );
						} else {
							googleFonts[ fontName ].variants = fontVariant;
						}
					} else {
						googleFonts[ fontName ] = {};
						googleFonts[ fontName ].variants = fontVariant;
					}

					// Remove duplicate variants.
					uniqueFontVariant = [];
					googleFonts[ fontName ].variants.forEach( function( el ) {
						if ( ! uniqueFontVariant.includes( el ) ) {
							uniqueFontVariant.push( el );
						}
					} );
					googleFonts[ fontName ].variants = uniqueFontVariant;

					// Finally, delete global variant.
					delete googleFonts[ globalVar ];
				}
			},

			/**
			 * Adds font awesome relative stylesheets.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			toggleFontAwesomePro: function( id ) {

				if ( 'status_fontawesome_pro' === id || ( 'fontawesome_v4_compatibility' === id && 0 === jQuery( '#fontawesome-shims-css' ).length ) ) {

					jQuery.ajax( {
						type: 'GET',
						url: fusionAppConfig.ajaxurl,
						dataType: 'json',
						data: {
							action: 'fusion_font_awesome',
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							pro_status: FusionApp.settings.status_fontawesome_pro
						}
					} )
						.done( function( response ) {
							fusionAppConfig.fontawesomeicons = response.icons;
							jQuery( '#fontawesome-css' ).attr( 'href', response.css_url );

							if ( 'fontawesome_v4_compatibility' === id ) {
								jQuery( 'body' ).append( '<link rel="stylesheet" id="fontawesome-shims-css" href="' + response.shims_url + '" type="text/css" media="all">' );
							} else {
								jQuery( '#fontawesome-shims-css' ).attr( 'href', response.css_url );
							}

							FusionApp.reInitIconPicker();
						} );

				}
			},

			/**
			 * Re inits icon picker on subset value change.
			 *
			 * @since 2.0.0
			 * @return {Object}
			 */
			FontAwesomeSubSets: function() {
				FusionApp.reInitIconPicker();
			},

			/**
			 * Checks for a context of content change.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			hasContentChanged: function( context, name ) {
				var status = false;

				if ( 'undefined' !== typeof context ) {
					if ( 'undefined' !== typeof name ) {
						status = 'undefined' !== typeof this.contentChanged[ context ] && 'undefined' !== typeof this.contentChanged[ context ][ name ] && true === this.contentChanged[ context ][ name ];
					} else {
						status = 'undefined' !== typeof this.contentChanged[ context ] && ! _.isEmpty( this.contentChanged[ context ] );
					}
				} else {
					_.each( this.contentChanged, function( scopedContext ) {
						if ( ! _.isEmpty( scopedContext )  ) {
							status = true;
						}
					} );
				}

				return status;
			},

			/**
			 * When content has been changed.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			contentChange: function( context, name ) {

				if ( 'object' !== typeof this.contentChanged[ context ] ) {
					this.contentChanged[ context ] = {};
				}

				this.contentChanged[ context ][ name ] = true;

				FusionApp.set( 'hasChange', true );
			},

			/**
			 * Preinit for icon pickers.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			iconPicker: function() {
				var icons     = fusionAppConfig.fontawesomeicons,
					output    = '<div class="fusion-icons-rendered" style="position:relative; height:0px; overflow:hidden;">',
					outputNav = '<div class="fusion-icon-picker-nav-rendered" style="height:0px; overflow:hidden;">',
					iconSubsets = {
						fas: 'Solid',
						far: 'Regular',
						fal: 'Light',
						fab: 'Brands'
					},
					outputSets  = {
						fas: '',
						fab: '',
						far: '',
						fal: ''
					},
					self = this,
					isSearchDefined = 'undefined' !== typeof fusionIconSearch && Array.isArray( fusionIconSearch );

				if ( jQuery( '.fusion-icons-rendered' ).length || ! Array.isArray( self.settings.status_fontawesome ) ) {
					return;
				}

				// Iterate through all FA icons and divide them into sets (one icon can belong to multiple sets).
				_.each( icons, function( icon, key ) {
					_.each( icon[ 1 ], function( iconSubset ) {
						if ( -1 !== self.settings.status_fontawesome.indexOf( iconSubset ) ) {
							outputSets[ iconSubset ] += '<span class="icon_preview ' + key + '" title="' + key + ' - ' + iconSubsets[ iconSubset ] + '"><i class="' + icon[ 0 ] + ' ' + iconSubset + '" data-name="' + icon[ 0 ].substr( 3 ) + '" aria-hidden="true"></i></span>';
						}
					} );
				} );

				// Add FA sets to output.
				_.each( iconSubsets, function( label, key ) {
					if ( -1 !== self.settings.status_fontawesome.indexOf( key ) ) {
						outputNav += '<a href="#fusion-' + key + '" class="fusion-icon-picker-nav-item">' + label + '</a>';
						output    += '<div id="fusion-' + key + '" class="fusion-icon-set">' + outputSets[ key ] + '</div>';
					}
				} );

				// WIP: Add custom icons.
				icons = fusionAppConfig.customIcons;
				_.each( icons, function( iconSet, IconSetKey ) {
					outputNav += '<a href="#' + IconSetKey + '" class="fusion-icon-picker-nav-item">' + iconSet.name + '</a>';
					output    += '<div id="' + IconSetKey + '" class="fusion-icon-set fusion-custom-icon-set">';
					_.each( iconSet.icons, function( icon ) {

						if ( isSearchDefined ) {
							fusionIconSearch.push( { name: icon } );
						}

						output += '<span class="icon_preview ' + icon + '" title="' + iconSet.css_prefix + icon + '"><i class="' + iconSet.css_prefix + icon + '" data-name="' + icon + '" aria-hidden="true"></i></span>';
					} );
					output += '</div>';
				} );

				outputNav += '</div>';
				output    += '</div>';

				jQuery( 'body' ).append( output + outputNav );
				jQuery( '.fusion-icon-picker-save' ).trigger( 'click' );

				if ( 'undefined' !== typeof window[ 'fusion-fontawesome-free-shims' ] ) {
					_.each( window[ 'fusion-fontawesome-free-shims' ], function( shim ) {

						if ( null !== shim[ 0 ] && null !== shim[ 2 ] ) {
							jQuery( '.fusion-icons-rendered' ).find( 'i.fa-' + shim[ 2 ] ).attr( 'data-alt-name', shim[ 0 ] );
						}

					} );
				}
			},

			/**
			 * Reinit icon picker.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			reInitIconPicker: function() {
				jQuery( '.fusion-icons-rendered' ).remove();
				jQuery( '.fusion-icon-picker-nav-rendered' ).remove();
				this.iconPicker();
			},

			checkLegacyAndCustomIcons: function( icon ) {
				var oldIconName;

				if ( '' !== icon ) {

					if ( 'fusion-prefix-' === icon.substr( 0, 14 ) ) {

						// Custom icon, we need to remove prefix.
						icon = icon.replace( 'fusion-prefix-', '' );
					} else {

						icon = icon.split( ' ' ),
						oldIconName = '';

						// Legacy FontAwesome 4.x icon, so we need check if it needs to be updated.
						if ( 'undefined' === typeof icon[ 1 ] ) {
							icon[ 1 ] = 'fas';

							if ( 'undefined' !== typeof window[ 'fusion-fontawesome-free-shims' ] ) {
								oldIconName = icon[ 0 ].substr( 3 );

								jQuery.each( window[ 'fusion-fontawesome-free-shims' ], function( i, shim ) {

									if ( shim[ 0 ] === oldIconName ) {

										// Update icon name.
										if ( null !== shim[ 2 ] ) {
											icon[ 0 ] = 'fa-' + shim[ 2 ];
										}

										// Update icon subset.
										if ( null !== shim[ 1 ] ) {
											icon[ 1 ] = shim[ 1 ];
										}

										return false;
									}
								} );
							}

							icon = icon[ 0 ] + ' ' + icon[ 1 ];
						}
					}

				}

				return icon;
			},

			/**
			 * When content has been reset to default.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			contentReset: function( context, name ) {

				if ( 'undefined' !== typeof name ) {

					// Reset for specific name.
					if ( 'undefined' !== typeof this.contentChanged[ context ] && 'undefined' !== typeof this.contentChanged[ context ][ name ] ) {
						delete this.contentChanged[ context ][ name ];
					}
				} else if ( 'undefined' !== typeof context ) {

					// Reset entire context.
					this.contentChanged[ context ] = {};
				} else {

					// Reset all.
					this.contentChanged = {};
				}

				if ( ! this.hasContentChanged() ) {
					FusionApp.set( 'hasChange', false );
				}
			},

			/**
			 * Creates and handles confirmation popups.
			 *
			 * @param {Object} args - The popup arguments.
			 * @param {string} args.title - The title.
			 * @param {string} args.content - The content for this popup.
			 * @param {string} args.type - Can be "info" or "warning". Changes the color of the icon.
			 * @param {string} args.icon - HTML for the icon.
			 * @param {string} args.class - Additional CSS classes for the popup..
			 * @param {string} args.action - If "hide", it hides the popup.
			 * @param {Array} args.actions - An array of actions. These get added as buttons.
			 * @param {Object} args.actions[0] - Each item in the actions array is an object.
			 * @param {string} args.actions[0].label - The label that will be used for the button.
			 * @param {string} args.actions[0].classes - The CSS class that will be added to the button.
			 * @param {Function} args.actions[0].callback - A function that will be executed when the button gets clicked.
			 */
			confirmationPopup: function( args ) {
				if ( 'hide' === args.action ) {

					// Hide elements.
					jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).hide();
					jQuery( '#fusion-builder-confirmation-modal' ).hide();

					// Early exit.
					return;
				}

				// Early exit if no content & title, or if there's no actions defined.
				if ( ( ! args.content && ! args.title ) || ( ! args.actions || ! args.actions[ 0 ] ) ) {
					return;
				}

				// Use default icon (exclamation mark) if no custom icon is defined.
				if ( ! args.icon ) {
					args.icon = '<i class="fas fa-exclamation" aria-hidden="true">';
				}

				// Use default type (warning) if no type is defined.
				if ( ! args.type ) {
					args.type = 'warning';
				}

				// Show the popup.
				jQuery( '#fusion-builder-confirmation-modal-dark-overlay' ).show();
				jQuery( '#fusion-builder-confirmation-modal' ).show();

				// Add the class.
				if ( 'undefined' !== typeof args[ 'class' ] ) {
					jQuery( '#fusion-builder-confirmation-modal' ).attr( 'class', args[ 'class' ] );
				}

				// Add the icon.
				jQuery( '#fusion-builder-confirmation-modal span.icon' )
					.html( args.icon )
					.removeClass( 'type-warning type-error-type-info' )
					.addClass( 'type-' + args.type );

				// Add the title.
				if ( args.title ) {
					jQuery( '#fusion-builder-confirmation-modal h3.title' ).show();
					jQuery( '#fusion-builder-confirmation-modal h3.title' ).html( args.title );
				} else {
					jQuery( '#fusion-builder-confirmation-modal h3.title' ).hide();
				}

				// Add the content.
				if ( args.content ) {
					jQuery( '#fusion-builder-confirmation-modal span.content' ).show();
					jQuery( '#fusion-builder-confirmation-modal span.content' ).html( args.content );
				} else {
					jQuery( '#fusion-builder-confirmation-modal span.content' ).hide();
				}

				// Reset the HTML for buttons so we can add anew based on the arguments we have.
				jQuery( '#fusion-builder-confirmation-modal .actions' ).html( '' );

				// Add buttons.
				_.each( args.actions, function( action ) {
					var classes = '.' + action.classes;
					if ( 0 < action.classes.indexOf( ' ' ) ) {
						classes = '.' + action.classes.replace( / /g, '.' );
					}

					jQuery( '#fusion-builder-confirmation-modal .actions' ).append( '<button class="' + action.classes + '">' + action.label + '</button>' );
					jQuery( '#fusion-builder-confirmation-modal .actions ' + classes ).on( 'click', action.callback );

				} );
			},

			/**
			 * Reset some CSS values, when modal settings dialogs get closed.
			 *
			 * @since 2.0
			 * @param {Object} modalView - View of the closed modal.
			 * @return {void}
			 */
			dialogCloseResets: function( modalView ) {
				if ( ! modalView.$el.closest( '.ui-dialog.fusion-builder-child-element' ).length ) {
					jQuery( 'body' ).removeClass( 'fusion-settings-dialog-default fusion-settings-dialog-large' );

				}

				this.previewWindow.jQuery( 'body' ).removeClass( 'fusion-dialog-ui-active' );

			},

			/**
			 * Shows multiple dialogs notice.
			 *
			 * @return {void}
			 */
			multipleDialogsNotice: function() {
				this.confirmationPopup( {
					title: fusionBuilderText.multi_dialogs,
					content: fusionBuilderText.multi_dialogs_notice,
					actions: [
						{
							label: fusionBuilderText.ok,
							classes: 'yes',
							callback: function() {
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );
							}
						}
					]
				} );
			},

			/**
			 * Getter for TO value or it's default if value is not saved yet.
			 *
			 * @since 3.0
			 * @param {string} optionKey - Option key (ID).
			 * @return {mixed}
			 */
			getSettingValue: function( settingKey ) {
				var flatOptions;

				if ( undefined === settingKey ) {
					return undefined;
				}

				if ( 'undefined' !== typeof this.settings[ settingKey ] ) {
					return this.settings[ settingKey ];
				}

				flatOptions = this.sidebarView.getFlatToObject();

				if ( 'undefined' !== typeof flatOptions[ settingKey ] && 'undefined' !== typeof flatOptions[ settingKey ][ 'default' ] ) {
					return flatOptions[ settingKey ][ 'default' ];
				}

				return undefined;
			},

			/**
			 * Getter for previewWindowSize property.
			 * Used to get 'custom' screen size, which is used to change correct options in EO.
			 *
			 * @since 3.0
			 * @return {string}
			 */
			getPreviewWindowSize: function() {
				return this.previewWindowSize;
			},

			/**
			 * Helper for responsive options.
			 *
			 * @since 2.3.0
			 * @return {string}
			 */
			getResponsiveOptionKey: function ( key, isFlex = true ) {
				var previewSize = FusionApp.getPreviewWindowSize(),
					optionKey	= ! isFlex || 'large' == previewSize ? key :  key + '_' + previewSize;
				return optionKey;
			},

			/**
			 * Setter for previewWindowSize property.
			 * Used to set 'custom' screen size, which is used to change correct options in EO.
			 *
			 * @since 3.0
			 * @param {string} newScreenSize - New screen size string.
			 * @return {void}
			 */
			setPreviewWindowSize: function( newScreenSize ) {

				if ( -1 !== newScreenSize.indexOf( 'mobile' ) ) {
					this.previewWindowSize = 'small';
				} else if ( -1 !== newScreenSize.indexOf( 'tablet' ) ) {
					this.previewWindowSize = 'medium';
				} else {
					this.previewWindowSize = 'large';
				}
			},

			/**
			 * Check for empty array values.
			 * Used to fix issue with jQuery.param omit empty array.
			 *
			 * @since 7.2
			 * @param {object} obj - Object Arrays.
			 * @return {void}
			 */
			maybeEmptyArray: function( obj ) {
				var key;
				for ( key in obj ) {
					if ( 'object' === typeof obj[ key ] && 0 == obj[ key ].length ) {
						obj[ key ] = [ '' ];
					}
				}
				return obj;
			}

		} );

		if ( 'undefined' === typeof FusionApp ) {
			window.FusionApp = new fusionApp(); // jshint ignore: line
		}
	} );
}( jQuery ) );
