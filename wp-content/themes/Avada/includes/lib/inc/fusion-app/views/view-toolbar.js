/* global FusionApp, FusionEvents, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Toolbar
		FusionPageBuilder.Toolbar = window.wp.Backbone.View.extend( {

			/**
			 * Boolean if the sidebar panel is open.
			 */
			isSidebarOpen: false,

			template: FusionPageBuilder.template( jQuery( '#fusion-app-front-end-toolbar' ).html() ),

			events: {
				'click .trigger-submenu-toggling': 'toggleSubMenu',
				'click .fusion-builder-preview-viewport .toggle-viewport': 'previewViewport',
				'click .fusion-builder-preview-viewport .toggle-viewport-breakpoints': 'toggleViewportBreakpoints',
				'click #fusion-frontend-builder-toggle-global-panel': 'togglePanel',
				'click .fusion-exit-builder-list a': 'exitBuilder',
				'click [data-link]': 'languageSwitch',
				'click .preview a': 'previewToggle',
				'click .toolbar-toggle a': 'toolbarToggle',
				'click .fusion-builder-save-page': 'savePage',
				'click .fusion-builder-keyboard-shortcuts': 'openKeyBoardShortCuts',
				'change .save-wrapper .post-status input': 'updatePostStatus'
			},

			/**
			 * Initialize empty language data.
			 *
			 * @since 2.0.0
			 * @param {Object} attributes - The attributes object.
			 * @return {Object} this
			 */
			initialize: function( attributes ) {

				// Default empty language data.
				this.languageData = {
					switcher: false,
					active: false
				};

				this.viewport = 'desktop';

				// Whether to use flags for labels.
				this.languageFlags = true;
				this.previewMode   = false;

				// We need to check clicks everywhere, not just in the toolbar
				// so this can't be a standard listener in the events object.
				this.toggleSubMenusCloseHandler();

				this.listenTo( attributes.fusionApp, 'change:hasChange', this.render );
				this.listenTo( FusionEvents, 'fusion-disconnected', this.setWarningColor );
				this.listenTo( FusionEvents, 'fusion-reconnected', this.removeWarningColor );
				this.listenTo( FusionEvents, 'fusion-sidebar-toggled', this.setActiveStyling );
				this.listenTo( FusionEvents, 'fusion-app-setup', this.reEnablePreviewMode );

				// Debounced event trigger.
				this._triggerEvent = _.debounce( _.bind( this.triggerEvent, this ), 300 );
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				this.$el.html( this.template( {
					switcher: this.languageData.switcher,
					postChanged: FusionApp.get( 'hasChange' ),
					postStatus: FusionApp.getPost( 'post_status' ),
					sidebarOpen: jQuery( 'body' ).hasClass( 'expanded' )
				} ) );

				if ( 'undefined' !== typeof FusionApp && FusionApp.builderToolbarView ) {
					jQuery( '.fusion-builder-live-toolbar' ).append( FusionApp.builderToolbarView.render().el );
				}

				this.previewViewport();

				return this;
			},

			/**
			 * Changes the viewport.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			previewViewport: function( event ) {
				var self           = this,
					indicator      = jQuery( 'li.fusion-builder-preview-viewport .viewport-indicator' ),
					indicatorIcons = jQuery( indicator.find( 'a[data-indicate-viewport]' ) );

				if ( event ) {
					event.preventDefault();
					this.viewport = jQuery( event.currentTarget ).attr( 'data-viewport' );

					FusionApp.setPreviewWindowSize( this.viewport );
				}

				// Change the indicator icon depending on the active viewport.
				_.each( indicatorIcons, function( indicatorIcon ) {
					var indicatorViewport = jQuery( indicatorIcon ).data( 'indicate-viewport' );

					jQuery( indicatorIcon ).removeClass( 'active' );
					if ( self.viewport === indicatorViewport ) {
						jQuery( indicatorIcon ).addClass( 'active' );
					}
				} );

				// Mark the selected viewport as active.
				jQuery( 'a.viewport-indicator > span' ).removeClass( 'active' );
				jQuery( 'a.viewport-indicator > span[data-indicate-viewport="' + self.viewport + '"]' ).addClass( 'active' );

				jQuery( window ).trigger( 'resize' );

				// Timing related check. Sometimes the jQuery isn't loaded yet in iframe.
				if ( 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'resize' );
				}

				jQuery( '#fb-preview' ).attr( 'data-viewport', self.viewport );

				// For responsive element options.
				this.responsiveOptions( self.viewport );

				if ( event ) {
					this._triggerEvent();
				}
			},

			/**
			 * Trigger viewport update event.
			 *
			 * @since 3.0
			 * @return {void}
			 */
			triggerEvent: function() {
				FusionEvents.trigger( 'fusion-preview-viewport-update' );
			},

			/**
			 * Toggles predefined / custom viewport breakpoints.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			toggleViewportBreakpoints: function( event ) {
				var $target,
					$parent;

				if ( ! event ) {
					return;
				}

				event.preventDefault();

				$target = jQuery( event.target );
				$parent = $target.closest( 'li' );

				$target.siblings().removeClass( 'active-breakpoints' );
				$target.addClass( 'active-breakpoints' );

				$parent.find( '> ul' ).attr( 'aria-expanded', 'false' );
				$parent.find( '> #' + $target.data( 'viewport-breakpoints' ) ).attr( 'aria-expanded', 'true' );
			},

			/**
			 * Toggle preview mode.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			previewToggle: function( event ) {
				var self = this;

				if ( event ) {
					event.preventDefault();
				}

				if ( this.previewMode ) {

					// Disable preview mode.
					jQuery( 'body' ).removeClass( 'fusion-builder-preview-mode' );
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-builder-preview-mode' );
					jQuery( '.fusion-builder-live .fusion-builder-live-toolbar .fusion-toolbar-nav li.preview a' ).removeClass( 'active' );

					// If we're on preview mode, make sure the global-options sidebar is hidden.
					if ( this.isSidebarOpen ) {
						FusionApp.sidebarView.openSidebar();
						this.isSidebarOpen = false;
					}

					// Remove the stylesheet for preview CSS.
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#fusion-preview-frame-builder-no-controls-css-css' ).attr( 'media', 'none' );
					this.previewMode = false;
					FusionEvents.trigger( 'fusion-preview-toggle' );
				} else {
					self.enablePreviewMode( true );
				}
			},

			reEnablePreviewMode: function() {
				if ( this.previewMode ) {
					this.enablePreviewMode( false );
				}
			},

			/**
			 * Toggle the toolbar.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			toolbarToggle: function( event ) {

				if ( 'undefined' !== typeof event ) {
					event.preventDefault();
				}

				jQuery( 'body' ).toggleClass( 'collapsed-toolbar' );
			},

			/**
			 * Enables preview mode.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			enablePreviewMode: function( toggled ) {
				toggled = 'undefined' === typeof toggled ? false : toggled;

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'fusion-builder-preview-mode' );
				jQuery( 'body' ).addClass( 'fusion-builder-preview-mode' );
				jQuery( '.fusion-builder-live .fusion-builder-live-toolbar .fusion-toolbar-nav li.preview a' ).addClass( 'active' );

				if ( toggled ) {
					this.isSidebarOpen = FusionApp.sidebarView.panelIsOpen();

					// If we're on preview mode, make sure the global-options sidebar is hidden.
					FusionApp.sidebarView.closeSidebar();

					// Hide already open inline toolbars.
					this.clearSelection( jQuery( '#fb-preview' )[ 0 ].contentWindow );
					jQuery( '#fb-preview' ).contents().find( '.medium-editor-toolbar-actions.visible' ).removeClass( 'visible' );

					// If we're on preview mode, close open dialogs.
					jQuery( '.ui-dialog-content' ).dialog( 'close' );
				}

				// Add the stylesheet for preview CSS.
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '#fusion-preview-frame-builder-no-controls-css-css' ).attr( 'media', 'all' );

				this.previewMode = true;
				FusionEvents.trigger( 'fusion-preview-toggle' );
			},

			clearSelection: function( frameWindow ) {
				if ( frameWindow.getSelection ) {
					if ( frameWindow.getSelection().empty ) {  // Chrome
						frameWindow.getSelection().empty();
					} else if ( frameWindow.getSelection().removeAllRanges ) {  // Firefox
						frameWindow.getSelection().removeAllRanges();
					}
				} else if ( frameWindow.selection ) {  // IE?
					frameWindow.selection.empty();
				}
			},

			/**
			 * Exit the builder and return to the frontend.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			exitBuilder: function( event ) {
				var linkTag = jQuery( event.currentTarget ),
					link = linkTag.attr( 'href' ),
					frameUrl = jQuery( '#fb-preview' ).attr( 'src' );

				event.preventDefault();

				if ( ! linkTag.parent().hasClass( 'exit-to-dashboard' ) ) {
					if ( linkTag.parent().hasClass( 'exit-to-back-end' ) ) {
						link = FusionApp.data.backendLink || linkTag.data( 'admin-url' ) + '?post=' + FusionApp.getPost( 'post_id' ) + '&action=edit';
					} else {
						if ( -1 !== frameUrl.indexOf( 'builder=true' ) ) {
							frameUrl = frameUrl.split( 'builder=true' );
							frameUrl = frameUrl[ 0 ];
							if ( '?' === frameUrl[ frameUrl.length - 1 ] ) {
								frameUrl = frameUrl.slice( 0, -1 );
							}
						}

						link = frameUrl;
					}
				}

				// cmd/ctrl and click, open in new tab.
				if ( FusionApp.modifierActive ) {
					window.open( link, '_blank' );
					return;
				}

				// Make user confirm.
				if ( FusionApp.hasContentChanged( 'page' ) ) {
					FusionApp.confirmationPopup( {
						title: fusionBuilderText.unsaved_changes,
						content: fusionBuilderText.changes_will_be_lost,
						class: 'fusion-confirmation-unsaved-changes',
						actions: [
							{
								label: fusionBuilderText.cancel,
								classes: 'cancel no',
								callback: function() {
									FusionApp.confirmationPopup( {
										action: 'hide'
									} );
								}
							},
							{
								label: fusionBuilderText.just_leave,
								classes: 'dont-save yes',
								callback: function() {
									FusionApp.manualSwitch = true;
									window.location.href   = link;
								}
							},
							{
								label: fusionBuilderText.leave,
								classes: 'save yes',
								callback: function() {
									var successAction = {};

									successAction.action = 'exit_builder';
									successAction.link   = link;

									FusionApp.savePostContent( successAction );
								}
							}
						]
					} );
					return;
				}
				FusionApp.manualSwitch = true;
				window.location.href   = link;

			},

			/**
			 * Creates/updates the language switcher.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			updateLanguageSwitcher: function() {
				this.languageData = {
					switcher: FusionApp.data.languageSwitcher,
					active: FusionApp.data.language
				};
				this.render();
			},

			/**
			 * Get language flag from data.
			 *
			 * @since 2.0.0
			 * @param {Object} data - The data formatted as an object containing "flag", "country_flag_url", "native_name", "name".
			 * @param {string} id - The language ID.
			 * @return {string}
			 */
			getLanguageFlag: function( data, id ) {
				var $languageFlag = '';

				// No data, return id.
				if ( 'undefined' === typeof data ) {
					$languageFlag = id;
				}

				// Flag checks.
				if ( this.languageFlags ) {
					if ( 'undefined' !== typeof data.flag ) {
						$languageFlag = '<img src="' + data.flag + '" /> ';
					}
					if ( 'undefined' !== typeof data.country_flag_url ) {
						$languageFlag = '<img src="' + data.country_flag_url + '" /> ';
					}
				}

				return $languageFlag;
			},

			/**
			 * Get language label from data.
			 *
			 * @since 2.0.0
			 * @param {Object} data - The data formatted as an object containing "flag", "country_flag_url", "native_name", "name".
			 * @param {string} id - The language ID.
			 * @return {string}
			 */
			getLanguageLabel: function( data, id ) {
				var $languageLabel = '';

				// No data, return id.
				if ( 'undefined' === typeof data ) {
					$languageLabel = id;
				}

				// WPML and PLL checks.
				if ( 'undefined' !== typeof data.native_name ) {
					$languageLabel += data.native_name;
				}
				if ( 'undefined' !== typeof data.name ) {
					$languageLabel += data.name;
				}

				return $languageLabel;
			},

			/**
			 * Get language link from data.
			 *
			 * @since 2.0.0
			 * @param {Object} data - The data formatted as an object containing "url".
			 * @param {string} id - The language ID.
			 * @return {void}
			 */
			getLanguageLink: function( data, id ) {
				if ( 'undefined' === typeof data ) {
					return id;
				}
				if ( 'undefined' !== typeof data.url ) {
					return data.url;
				}
				return id;
			},

			/**
			 * Switch the page language.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			languageSwitch: function( event ) {
				var targetUrl = jQuery( event.currentTarget ).data( 'link' );

				event.preventDefault();

				if ( '' !== targetUrl ) {

					// If global has changed, we need to create transients before switching.
					if ( FusionApp.hasContentChanged( 'global' ) ) {
						FusionApp.fullRefresh( targetUrl, event );
					} else {
						FusionApp.checkLink( event, targetUrl );
					}
				}
			},

			/**
			 * Toggles sidebar open or closed.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			togglePanel: function( event ) {

				if ( 'undefined' !== typeof event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof FusionApp.sidebarView.togglePanel ) {
					FusionApp.sidebarView.togglePanel();
				}
			},

			setActiveStyling: function( open ) {
				var $anchor = this.$el.find( '#fusion-frontend-builder-toggle-global-panel' );

				if ( open ) {
					$anchor.addClass( 'active' );
				} else {
					$anchor.removeClass( 'active' );
				}
			},

			/**
			 * Toggles the submenu.
			 *
			 * @param {Object} event  - The click event.
			 * @return {void}
			 */
			toggleSubMenu: function( event ) {
				var eventTarget = jQuery( event.target ),
					triggers    = jQuery( '.fusion-builder-live-toolbar .trigger-submenu-toggling' ),
					subMenus    = jQuery( '.fusion-builder-live-toolbar .submenu-trigger-target' ),
					subMenu;

				if ( 'undefined' !== typeof event ) {
					event.preventDefault();
				}

				if ( jQuery( 'body' ).hasClass( 'fusion-hide-all-tooltips' ) ) {
					return;
				}

				if ( ! eventTarget.hasClass( 'trigger-submenu-toggling' ) ) {
					eventTarget = jQuery( eventTarget.closest( '.trigger-submenu-toggling' ) );
				}

				subMenu = eventTarget.parent().find( '.submenu-trigger-target' );
				if ( subMenu.length ) {
					if ( 'false' === subMenu.attr( 'aria-expanded' ) ) {
						subMenu.attr( 'aria-expanded', 'true' );
						eventTarget.addClass( 'active' );

						// Close any other open submenus that might exist.
						_.each( triggers, function( trigger ) {
							if ( jQuery( trigger )[ 0 ] !== jQuery( eventTarget )[ 0 ] ) {
								jQuery( trigger ).removeClass( 'active' );
							}
						} );
						_.each( subMenus, function( sub ) {
							if ( jQuery( sub )[ 0 ] !== jQuery( subMenu )[ 0 ] ) {
								jQuery( sub ).attr( 'aria-expanded', 'false' );
							}
						} );
					} else {
						subMenu.attr( 'aria-expanded', 'false' );
						eventTarget.removeClass( 'active' );
					}
				}
			},

			/**
			 * Closes submenus when we click outside the trigger.
			 *
			 * @return {void}
			 */
			toggleSubMenusCloseHandler: function() {

				// Passive is a significant performance improvement
				// so we should use it if supported by the browser.
				var self             = this,
					passiveSupported = false,
					passive          = false,
					options;
				try {
					options = {
						get passive() { // jshint ignore:line
							passiveSupported = true;
							return true;
						}
					};

					window.addEventListener( 'test', options, options );
					window.removeEventListener( 'test', options, options );
				} catch ( err ) {
					passiveSupported = false;
				}
				passive = passiveSupported ? { passive: true } : false;
				window.addEventListener( 'click', self.toggleSubMenusClose, passive );
				window.frames[ 0 ].window.addEventListener( 'click', self.toggleSubMenusClose, passive );
			},

			/**
			 * Closes submenus when we click outside the trigger.
			 *
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			toggleSubMenusClose: function( event ) {
				var target      = jQuery( event.target ),
					allSubMenus = jQuery( '.fusion-builder-live-toolbar .submenu-trigger-target' ),
					subMenu;

				if ( target.hasClass( 'trigger-submenu-toggling' ) || target.closest( '.trigger-submenu-toggling' ).length ) {

					// We clicked on a toggle, so we need to close all OTHER dropdowns.
					// First of all, make sure we've got the right target element.
					if ( ! target.hasClass( 'submenu-trigger-target' ) ) {
						target = target.parent().find( '.submenu-trigger-target' );
					}

					// Find the submenu.
					subMenu = target.parent().find( '.submenu-trigger-target' );

					// If we could not find the submenu, early exit.
					if ( ! subMenu.length ) {
						return;
					}

					// Go through all submenus
					_.each( allSubMenus, function( item ) {

						// Skip current item.
						if ( subMenu[ 0 ].offsetParent === item.offsetParent ) {
							return;
						}
						jQuery( item ).attr( 'aria-expanded', false );
					} );

				} else {

					// We did not click on a toggle, close ALL dropdowns.
					allSubMenus.attr( 'aria-expanded', false );

					// Go through all buttons and remove .active class.
					_.each( jQuery( '.fusion-builder-live-toolbar .trigger-submenu-toggling.active' ), function( item ) {
						jQuery( item ).removeClass( 'active' );
					} );
				}
			},

			/**
			 * Renders the FusionPageBuilder.KeyBoardShortcuts View view.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			openKeyBoardShortCuts: function( event ) {
				var view;

				if ( 'undefined' !== typeof event ) {
					event.preventDefault();
					event.stopPropagation();
				}

				if ( jQuery( '.fusion-builder-dialog' ).length && jQuery( '.fusion-builder-dialog' ).is( ':visible' ) ) {
					FusionApp.multipleDialogsNotice();
					return;
				}

				view = new FusionPageBuilder.keyBoardShorCutsView();
				view.render();
			},

			/**
			 * Sets a warning to let you know connection has been lost.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			setWarningColor: function() {
				this.$el.find( '.fusion-builder-save-page' ).addClass( 'failed' );
			},

			/**
			 * Remove warning to let you know re-connection successful.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeWarningColor: function() {
				this.$el.find( '.fusion-builder-save-page' ).removeClass( 'failed' );
			},

			/**
			 * Saves the page.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			savePage: function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				// Do not proceed if button is disabled.
				if ( 'true' === jQuery( event.target ).data( 'disabled' ) || true === jQuery( event.target ).data( 'disabled' ) ) {
					return;
				}

				FusionApp.savePostContent();
			},

			/**
			 * Updates the post status.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			updatePostStatus: function() {
				var postStatus = this.$el.find( '.save-wrapper .post-status input:checked' ).length ? this.$el.find( '.save-wrapper .post-status input:checked' ).val() : FusionApp.getPost( 'post_status' );
				FusionApp.setPost( 'post_status', postStatus );
				FusionApp.contentChange( 'page', 'page-setting' );
			},

			/**
			 * Adds data for responsive options.
			 *
			 * @since 3.0
			 * @param {String} viewport - The selected viewport.
			 * @return {void}
			 */
			responsiveOptions: function( viewport ) {
				var viewPorts = {
					'desktop': 'large',
					'tablet-portrait-custom': 'medium',
					'mobile-portrait-custom': 'small'
				};

				jQuery( 'body' ).removeClass( function ( index, className ) {
					return ( className.match( /(^|\s)fusion-builder-module-settings-\S+/g ) || [] ).join( ' ' );
				} );

				jQuery( 'body' ).addClass( 'fusion-builder-module-settings-' + viewPorts[ viewport ] );
			}

		} );
	} );
}( jQuery ) );
