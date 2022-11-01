var cssua=function(e,o,i){"use strict";var s=" ua-",r=/\s*([\-\w ]+)[\s\/\:]([\d_]+\b(?:[\-\._\/]\w+)*)/,n=/([\w\-\.]+[\s\/][v]?[\d_]+\b(?:[\-\._\/]\w+)*)/g,a=/\b(?:(blackberry\w*|bb10)|(rim tablet os))(?:\/(\d+\.\d+(?:\.\w+)*))?/,b=/\bsilk-accelerated=true\b/,t=/\bfluidapp\b/,l=/(\bwindows\b|\bmacintosh\b|\blinux\b|\bunix\b)/,p=/(\bandroid\b|\bipad\b|\bipod\b|\bwindows phone\b|\bwpdesktop\b|\bxblwp7\b|\bzunewp7\b|\bwindows ce\b|\bblackberry\w*|\bbb10\b|\brim tablet os\b|\bmeego|\bwebos\b|\bpalm|\bsymbian|\bj2me\b|\bdocomo\b|\bpda\b|\bchtml\b|\bmidp\b|\bcldc\b|\w*?mobile\w*?|\w*?phone\w*?)/,c=/(\bxbox\b|\bplaystation\b|\bnintendo\s+\w+)/,d={parse:function(e,o){var i={};if(o&&(i.standalone=o),!(e=(""+e).toLowerCase()))return i;for(var s,d,m=e.split(/[()]/),w=0,_=m.length;w<_;w++)if(w%2){var u=m[w].split(";");for(s=0,d=u.length;s<d;s++)if(r.exec(u[s])){var f=RegExp.$1.split(" ").join("_"),v=RegExp.$2;(!i[f]||parseFloat(i[f])<parseFloat(v))&&(i[f]=v)}}else{var x=m[w].match(n);if(x)for(s=0,d=x.length;s<d;s++){var g=x[s].split(/[\/\s]+/);g.length&&"mozilla"!==g[0]&&(i[g[0].split(" ").join("_")]=g.slice(1).join("-"))}}if(p.exec(e))i.mobile=RegExp.$1,a.exec(e)&&(delete i[i.mobile],i.blackberry=i.version||RegExp.$3||RegExp.$2||RegExp.$1,RegExp.$1?i.mobile="blackberry":"0.0.1"===i.version&&(i.blackberry="7.1.0.0"));else if(l.exec(e))i.desktop=RegExp.$1;else if(c.exec(e)){i.game=RegExp.$1;var h=i.game.split(" ").join("_");i.version&&!i[h]&&(i[h]=i.version)}return i.intel_mac_os_x?(i.mac_os_x=i.intel_mac_os_x.split("_").join("."),delete i.intel_mac_os_x):i.cpu_iphone_os?(i.ios=i.cpu_iphone_os.split("_").join("."),delete i.cpu_iphone_os):i.cpu_os?(i.ios=i.cpu_os.split("_").join("."),delete i.cpu_os):"iphone"!==i.mobile||i.ios||(i.ios="1"),i.opera&&i.version?(i.opera=i.version,delete i.blackberry):b.exec(e)?i.silk_accelerated=!0:t.exec(e)&&(i.fluidapp=i.version),i.applewebkit?(i.webkit=i.applewebkit,delete i.applewebkit,i.opr&&(i.opera=i.opr,delete i.opr,delete i.chrome),i.safari&&(i.chrome||i.crios||i.opera||i.silk||i.fluidapp||i.phantomjs||i.mobile&&!i.ios?delete i.safari:i.version&&!i.rim_tablet_os?i.safari=i.version:i.safari={419:"2.0.4",417:"2.0.3",416:"2.0.2",412:"2.0",312:"1.3",125:"1.2",85:"1.0"}[parseInt(i.safari,10)]||i.safari)):i.msie||i.trident?(i.opera||(i.ie=i.msie||i.rv),delete i.msie,i.windows_phone_os?(i.windows_phone=i.windows_phone_os,delete i.windows_phone_os):"wpdesktop"!==i.mobile&&"xblwp7"!==i.mobile&&"zunewp7"!==i.mobile||(i.mobile="windows desktop",i.windows_phone=+i.ie<9?"7.0":+i.ie<10?"7.5":"8.0",delete i.windows_nt)):(i.gecko||i.firefox)&&(i.gecko=i.rv),i.rv&&delete i.rv,i.version&&delete i.version,i},format:function(e){function o(e,o){e=e.split(".").join("-");var i=s+e;if("string"==typeof o){for(var r=(o=o.split(" ").join("_").split(".").join("-")).indexOf("-");r>0;)i+=s+e+"-"+o.substring(0,r),r=o.indexOf("-",r+1);i+=s+e+"-"+o}return i}var i="";for(var r in e)r&&e.hasOwnProperty(r)&&(i+=o(r,e[r]));return i},encode:function(e){var o="";for(var i in e)i&&e.hasOwnProperty(i)&&(o&&(o+="&"),o+=encodeURIComponent(i)+"="+encodeURIComponent(e[i]));return o}};d.userAgent=d.ua=d.parse(o,i);var m=d.format(d.ua)+" js";return e.className?e.className=e.className.replace(/\bno-js\b/g,"")+m:e.className=m.substr(1),d}(document.documentElement,navigator.userAgent,navigator.standalone);;/* global FusionApp, FusionEvents, fusionBuilderText */
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
;/* global FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Dialog = Backbone.Model.extend( {

		initialize: function() {
			var self = this;

			// Dialog as percentage.
			this.dialogWidth  = 0.85 * jQuery( window ).width(),
			this.dialogHeight = 0.9 * jQuery( window ).height();

			// Initial dialog settings.
			this.setDialogData();

			jQuery( window ).on( 'resize', function() {
				self.resizeDialog();
			} );

			this.extendDialog();
		},

		extendDialog: function() {
			jQuery.widget( 'ui.dialog', jQuery.extend( {}, jQuery.ui.dialog.prototype, {
				_title: function( title ) {
					var $dialogContent = this.element,
						$tabMenu       = $dialogContent.find( '.fusion-builder-modal-top-container' ),
						$titleBar      = title.closest( '.ui-dialog-titlebar' );

					$dialogContent.before( $tabMenu );

					if ( $titleBar.parent( '.fusion-builder-child-element' ).length ) {
						$titleBar.find( '.ui-dialog-title' ).before( '<span class="ui-dialog-close fusion-back-menu-item"><svg version="1.1" width="18" height="18" viewBox="0 0 32 32"><path d="M12.586 27.414l-10-10c-0.781-0.781-0.781-2.047 0-2.828l10-10c0.781-0.781 2.047-0.781 2.828 0s0.781 2.047 0 2.828l-6.586 6.586h19.172c1.105 0 2 0.895 2 2s-0.895 2-2 2h-19.172l6.586 6.586c0.39 0.39 0.586 0.902 0.586 1.414s-0.195 1.024-0.586 1.414c-0.781 0.781-2.047 0.781-2.828 0z"></path></svg></span>' );
					} else if ( 'undefined' !== typeof this.options.type ) {
						$titleBar.find( '.ui-dialog-titlebar-close' ).before( '<div class="fusion-utility-menu-wrap"><span class="fusion-utility-menu fusiona-ellipsis"></span></div>' );
					}

					if ( ! this.options.title ) {
						title.html( '&#160;' );
					} else {
						title.text( this.options.title );
					}
				},
				_hide: function( event ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'fusion-dialog-ui-active' );

					this._trigger( 'close', event );
				}
			} ) );
		},

		/**
		 * Resizes dialogs.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		resizeDialog: function() {
			var titleBar = jQuery( '.fusion-builder-large-library-dialog .ui-dialog-titlebar' ),
				titleBarHeight = titleBar.length ? titleBar.height() : 0;

			this.dialogWidth  = 0.85 * jQuery( window ).width(),
			this.dialogHeight = ( 0.9 * ( jQuery( window ).height() - 54 ) ) - titleBarHeight;

			jQuery( '.fusion_builder_modal_settings:ui-dialog, #fusion-builder-front-end-library:ui-dialog, .fusion-builder-keyboard-shortcuts-dialog .ui-dialog-content:ui-dialog, .fusion-builder-preferences-dialog .ui-dialog-content:ui-dialog' ).dialog( 'option', 'width', this.dialogWidth );
			jQuery( '.fusion_builder_modal_settings:ui-dialog, #fusion-builder-front-end-library:ui-dialog, .fusion-builder-keyboard-shortcuts-dialog .ui-dialog-content:ui-dialog, .fusion-builder-preferences-dialog .ui-dialog-content:ui-dialog' ).dialog( 'option', 'height', this.dialogHeight );
		},

		/**
		 * Sets the dialog data from browser if it exists.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		setDialogData: function() {
			if ( 'undefined' !== typeof Storage && 'undefined' !== localStorage.getItem( 'dialogData' ) && localStorage.getItem( 'dialogData' ) ) {
				this.dialogData        = JSON.parse( localStorage.getItem( 'dialogData' ) );
				this.dialogData.of     = window;
				this.dialogData.width  = this.dialogData.width > jQuery( window ).width() ? jQuery( window ).width() : this.dialogData.width;
				this.dialogData.height = this.dialogData.height > jQuery( window ).height() ? jQuery( window ).height() : this.dialogData.height;
			} else {
				this.dialogData = {
					width: 450,
					height: 400,
					position: { my: 'right bottom', at: 'right-50 bottom-100', of: window }
				};
			}
		},

		/**
		 * Saves the position of a dialog.
		 *
		 * @since 2.0.0
		 * @param {Object} [offset] Contains the position left & top args.
		 * @return {void}
		 */
		saveDialogPosition: function( offset ) {
			this.dialogData.position = {
				my: 'left top',
				at: 'left+' + offset.left + ' top+' + offset.top + ''
			};
			this.storeDialogData();
		},

		/**
		 * Saves the dialog size.
		 *
		 * @since 2.0.0
		 * @param {Object} [size] Contains the width & height params.
		 * @return {void}
		 */
		saveDialogSize: function( size ) {
			this.dialogData.width  = size.width;
			this.dialogData.height = size.height;
			this.storeDialogData();
		},

		/**
		 * Checks if dialog is positioned out of viewport.
		 *
		 * @since 2.0.0
		 * @param {Object} [offset] Contains the position left & top args.
		 * @return {boolean}
		 */
		maybeRepositionDialog: function( $dialog ) {

			if ( jQuery( window ).width() < $dialog.offset().left + $dialog.width() ) {
				jQuery( $dialog ).position( {
					my: 'center',
					at: 'center',
					of: window
				} );

				return true;
			}

			return false;
		},

		/**
		 * Stored dialog data in browser.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		storeDialogData: function() {
			var saveData = jQuery.extend( true, {}, this.dialogData );

			delete saveData.of;
			delete saveData.position.of;

			if ( 'undefined' !== typeof Storage ) {
				localStorage.setItem( 'dialogData', JSON.stringify( saveData ) );
			}
		},

		/**
		 * Handle tabs in dialogs.
		 *
		 * @since 2.0.0
		 * @param {Object} [thisEl] The element.
		 * @return {void}
		 */
		dialogTabs: function( thisEl ) {
			thisEl.find( '.fusion-tabs-menu a' ).on( 'click', function( event ) {

				var target = jQuery( this ).attr( 'href' ) + '.fusion-tab-content';

				jQuery( this ).parent( 'li' ).siblings().removeClass( 'current' );
				jQuery( this ).parent( 'li' ).addClass( 'current' );
				event.preventDefault();

				thisEl.find( '.fusion-tab-content' ).hide().removeClass( 'active' );
				thisEl.find( target ).show().addClass( 'active' );

				if ( jQuery( '.fusion-builder-modal-top-container' ).find( '.fusion-elements-filter' ).length ) {
					setTimeout( function() {
						jQuery( '.fusion-builder-modal-top-container' ).find( '.fusion-elements-filter' ).focus();
					}, 50 );
				}

				FusionEvents.trigger( 'fusion-tab-changed' );

				if ( 0 < thisEl.closest( '.fusion-sidebar-section' ).length ) {
					jQuery( target ).closest( '.fusion-tabs' ).scrollTop( 0 );
				} else {
					thisEl.closest( '.ui-dialog-content' ).scrollTop( 0 );
				}
			} );

			thisEl.find( '.fusion-tabs-menu > li:first-child a' ).trigger( 'click' );
		},

		/**
		 * Adds classes necessary to prevent iframe from catching pointer events.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addResizingClasses: function() {
			jQuery( 'body' ).addClass( 'fusion-preview-block fusion-dialog-resizing' );
		},

		/**
		 * Removes classes necessary to prevent iframe from catching pointer events.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removeResizingClasses: function() {
			jQuery( 'body' ).removeClass( 'fusion-preview-block fusion-dialog-resizing' );
		},

		/**
		 * Adds modal hover event necessary to prevent iframe from catching pointer events.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addResizingHoverEvent: function() {
			jQuery( '.ui-dialog .ui-resizable-handle' ).hover(
				function() {
					jQuery( 'body' ).addClass( 'fusion-preview-block' );
				}, function() {
					if ( ! jQuery( 'body' ).hasClass( 'fusion-dialog-resizing' ) ) {
						jQuery( 'body' ).removeClass( 'fusion-preview-block' );
					}
				}
			);
		}

	} );

}( jQuery ) );
;var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Validate = Backbone.Model.extend( {

		/**
		 * Validates dimension css values.
		 *
		 * @param {string} value - The value we want to validate.
		 * @return {boolean}
		 */
		cssValue: function( value, allowNumeric ) {
			var validUnits    = [ 'rem', 'em', 'ex', '%', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ch', 'vh', 'vw', 'vmin', 'vmax' ],
				partsValidity = true,
				self          = this,
				numericValue,
				unit,
				parts;

			// 0 is always a valid value, and we can't check calc() values effectively.
			if ( '0' === value || '' === value || ( 0 <= value.indexOf( 'calc(' ) && 0 <= value.indexOf( ')' ) ) ) {
				return true;
			}

			if ( 0 <= value.indexOf( ' ' ) ) {
				parts = value.split( ' ' );
				_.each( parts, function( part ) {
					if ( ! self.cssValue( part, false ) ) {
						partsValidity = false;
					}
				} );
				return partsValidity;
			}

			// Get the numeric value.
			numericValue = parseFloat( value );

			// Get the unit
			unit = value.replace( numericValue, '' );

			if ( true === allowNumeric && ( '' === unit || ! unit ) ) {
				return true;
			}

			// Check the validity of the numeric value and units.
			if ( isNaN( numericValue ) || 0 > _.indexOf( validUnits, unit ) ) {
				return false;
			}
			return true;
		},

		/**
		 * Color validation.
		 *
		 * @since 2.0.0
		 * @param {string} value - The color-value we're validating.
		 * @param {string} mode - The color-mode (rgba or hex).
		 * @return {boolean}
		 */
		validateColor: function( value, mode ) {
			if ( '' === value ) {
				return true;
			}

			// Invalid value if not a string.
			if ( ! _.isString( value ) ) {
				return false;
			}

			if ( 0 === value.indexOf( '--' ) || ( /var\(\s*--/i ).test( value ) ) {
				return true;
			}

			if ( 'hex' === mode ) {
				return this.colorHEX( value );
			} else if ( 'rgba' === mode ) {
				return this.colorRGBA( value );
			}

			// Validate RGBA.
			if ( -1 !== value.indexOf( 'rgba' ) ) {
				return this.colorRGBA( value );
			}

			// Validate HEX.
			return this.colorHEX( value );
		},

		/**
		 * Validates a hex color.
		 *
		 * @since 2.0.0
		 * @param {string} value - The value we're validating.
		 * @return {boolean}
		 */
		colorHEX: function( value ) {
			var hexValue;

			if ( '' === value ) {
				return true;
			}

			// If value does not include '#', then it's invalid hex.
			if ( -1 === value.indexOf( '#' ) ) {
				return false;
			}

			hexValue = value.replace( '#', '' );

			// Check if hexadecimal.
			return ( ! isNaN( parseInt( hexValue, 16 ) ) );
		},

		/**
		 * Validates an rgba color.
		 *
		 * @since 2.0.0
		 * @param {string} value - The value we're validating.
		 * @return {boolean}
		 */
		colorRGBA: function( value ) {
			var valid = true,
				parts;

			if ( '' === value ) {
				return true;
			}

			if ( -1 === value.indexOf( 'rgba(' ) || -1 === value.indexOf( ')' ) ) {
				return false;
			}

			parts = value.replace( 'rgba(', '' ).replace( ')', '' ).split( ',' );
			if ( 4 !== parts.length ) {
				return false;
			}

			_.each( parts, function( part ) {
				var num = parseFloat( part, 10 );
				if ( isNaN( num ) ) {
					valid = false;
					return false;
				}
				if ( 0 > num || 255 < num ) {
					valid = false;
					return false;
				}
			} );
			return valid;
		},

		/**
		 * Adds and removes messages in the control.
		 *
		 * @param {string} id - The setting ID.
		 * @param {string} message - The message to add.
		 * @return {void}
		 */
		message: function( action, id, input, message ) {
			var element = jQuery( '.fusion-builder-option[data-option-id="' + id + '"]' ),
				messageClass   = 'fusion-builder-validation',
				messageWrapper = '<div class="' + messageClass + ' error"></div>';

			// No reason to proceed if we can't find the element.
			if ( ! element.length ) {
				return;
			}

			if ( 'add' === action ) {

				// If the message wrapper doesn't exist, add it.
				if ( ! element.find( '.' + messageClass ).length ) {
					element.find( '.option-details' ).append( messageWrapper );
					jQuery( input ).addClass( 'error' );
				}

				// Add the message to the validation error wrapper.
				element.find( '.' + messageClass ).html( message );

			} else if ( 'remove' === action ) {
				element.find( '.' + messageClass ).remove();
				jQuery( input ).removeClass( 'error' );
			}
		}
	} );
}( jQuery ) );
;/* global FusionApp, fusionSanitize */
/* eslint no-unused-vars: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Callback = Backbone.Model.extend( {
		fusionOption: function( value, args ) {
			var poValue = false;
			if ( 'object' === typeof args && 'string' === typeof args.id && 'string' === typeof args.type ) {
				if ( 'PO' === args.type && '' !== value ) {
					return value;
				}  else if ( 'PO' === args.type ) {
					return FusionApp.settings[ args.id ];
				}
				poValue = 'undefined' !== typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ args.id ] ? FusionApp.data.postMeta._fusion[ args.id ] : false;
				if ( poValue && '' !== poValue ) {
					return poValue;
				}
				return value;
			}
			return value;
		},

		awbHeaderBreakpoint: function( value, args ) {
			var $contents     = jQuery( '#fb-preview' ).contents(),
				breakpointVal = 1;

			if ( 'medium' === value || 'small' === value ) {
				breakpointVal = fusionSanitize.getOption( 'visibility_' + value );
			} else if ( 'custom' === value ) {
				breakpointVal = 'undefined' !== typeof FusionApp.data.postMeta._fusion.header_custom_breakpoint ? FusionApp.data.postMeta._fusion.header_custom_breakpoint : 800;
			}
			$contents.find( '#awb-side-header-css' ).attr( 'media', 'only screen and (min-width: ' +  parseInt( breakpointVal, 10 ) + 'px)' );
		},

		awbCustomHeaderBreakpoint: function( value, args ) {
			if ( 'undefined' !== typeof FusionApp.data.postMeta._fusion.header_breakpoint && 'custom' === FusionApp.data.postMeta._fusion.header_breakpoint ) {
				jQuery( '#fb-preview' ).contents().find( '#awb-side-header-css' ).attr( 'media', 'only screen and (min-width: ' +  parseInt( value, 10 ) + 'px)' );
			}
		},

		awbHeaderPosition: function( value, args ) {
			var $body = jQuery( '#fb-preview' ).contents().find( 'body' );

			if ( 'left' === value || 'right' === value ) {
				$body.removeClass( 'awbh-left awbh-right' ).addClass( 'side-header awbh-' + value );
				if ( 'undefined' === typeof FusionApp.data.postMeta._fusion.header_breakpoint ) {
					FusionApp.data.postMeta._fusion.header_breakpoint = 'small';
				}
				this.awbHeaderBreakpoint( FusionApp.data.postMeta._fusion.header_breakpoint, args );
			} else {
				$body.removeClass( 'side-header awbh-left awbh-right' );
			}
		}
	} );

}( jQuery ) );
;/* global FusionApp, fusionAllElements, FusionEvents, FusionPageBuilderViewManager, FusionPageBuilderApp */
/* jshint -W024, -W098*/
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Dependencies = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function( options, view, $targetEl, repeaterFields, $parentEl ) {
			var self = this,
				currentOptions;

			this.$targetEl      = 'undefined' !== typeof $targetEl ? $targetEl : view.$el;
			this.repeaterFields = 'undefined' !== typeof repeaterFields ? repeaterFields : false;
			this.$parentEl      = 'undefined' !== typeof $parentEl ? $parentEl : this.$targetEl;
			this.type           = view.type;
			this.elementView    = view;

			// Dependency object key names
			switch ( this.type ) {

			case 'TO':
				self.dependencyKey  = 'required';
				self.settingKey     = 'setting';
				self.operatorKey    = 'operator';
				currentOptions      = view.options;

				break;

			case 'PO':
				self.dependencyKey  = 'dependency';
				self.settingKey     = 'field';
				self.operatorKey    = 'comparison';
				currentOptions      = view.options;

				break;

			case 'EO':
				self.dependencyKey  = 'dependency';
				self.settingKey     = 'element';
				self.operatorKey    = 'operator';
				currentOptions      = options;

				break;
			}

			// Special case, we override view options from repeater.
			if ( self.repeaterFields ) {
				self.currentOptions = repeaterFields;
			} else {
				self.currentOptions = currentOptions;
			}

			self.parentValues  = 'undefined' !== typeof view.parentValues ? view.parentValues : false;

			self.collectDependencies();
			self.collectDependencyIds();

			if ( 'undefined' !== typeof self.dependencyIds && self.dependencyIds.length ) {
				this.$targetEl.find( self.dependencyIds.substring( 2 ) ).on( 'change paste keyup fusion-change', function() {
					self.processDependencies( jQuery( this ).attr( 'id' ), view );
				} );

				// Listen for TO changes, refresh dependencies for new default.
				if ( 'object' === typeof self.dependencies ) {
					_.each( _.keys( self.dependencies ), function( param ) {
						FusionEvents.on( 'fusion-param-default-update-' + param, function() {
							self.processDependencies( param, view );
						} );
					} );
				}
			}

			// Repeater dependency from parent view.
			if ( 'undefined' !== typeof self.parentDependencyIds && self.parentDependencyIds.length ) {
				this.$parentEl.on( 'change paste keyup fusion-change', self.parentDependencyIds.substring( 2 ), function() {
					self.processDependencies( jQuery( this ).attr( 'id' ), view, true );
				} );
			}

			self.dependenciesInitialCheck( view );

			// Process page option default values.
			if ( 'PO' === view.type ) {
				self.processPoDefaults( view );
			} else if ( 'EO' === view.type && 'undefined' !== typeof avadaPanelIFrame ) {
				self.processEoDefaults( view );
			}
		},

		/**
		 * Initial option dependencies check.
		 *
		 * @since 2.0.0
		 */
		dependenciesInitialCheck: function( view ) {
			var self = this;

			// Check for any option dependencies that are not on this tab.
			jQuery.each( _.keys( self.dependencies ), function( index, value ) { // jshint ignore: line
				if ( 'undefined' === typeof self.currentOptions[ value ] ) {
					self.processDependencies( value, view );
				}
			} );

			// Check each option on this tab.
			jQuery.each( self.currentOptions, function( index ) {
				self.processDependencies( index, view );
			} );
		},

		buildPassedArray: function( dependencies, gutterCheck ) {

			var self         = this,
				$passedArray = [],
				toName;

			// Check each dependency for that id.
			jQuery.each( dependencies, function( index, dependency ) {

				var setting     = dependency[ self.settingKey ],
					operator    = dependency[ self.operatorKey ],
					value       = dependency.value,
					hasParent   = -1 !== setting.indexOf( 'parent_' ),
					element     = self.repeaterFields && hasParent ? self.$parentEl.find( '.fusion-builder-module-settings' ).data( 'element' ) : self.$targetEl.find( '.fusion-builder-module-settings' ).data( 'element' ),
					result      = false,
					parentValue,
					containerView,
					containerParams;

				if ( self.repeaterFields && hasParent ) {
					parentValue = self.$parentEl.find( '#' + setting.replace( 'parent_', '' ) ).val();
				} else if ( 0 < self.$targetEl.find( '#' + setting ).closest( '.dynamic-param-fields' ).length ) {
					// Check and exclude for dynamic data fields.
					parentValue = self.$targetEl.find( '#' + setting ).closest( '[data-dynamic]' ).siblings().find( '#' + setting ).val();
				} else {
					const input = self.$targetEl.find( '#' + setting );
					// Multi Select option.
					if ( input.is( 'div' ) && input.is( '.fusion-form-multiple-select' ) ) {
						parentValue = [];
						input.find( '.fusion-select-options input.fusion-select-option:checked' ).each( function() {
							parentValue.push( jQuery( this ).val() );
						} );
					} else {
						parentValue = input.val();
					}
				}
				if ( 'undefined' === typeof parentValue ) {
					if ( 'TO' === self.type ) {
						parentValue = FusionApp.settings[ setting ];
					} else if ( 'PO' === self.type ) {
						if ( 'undefined' !== typeof FusionApp.data.postMeta[ setting ] ) {
							parentValue = FusionApp.data.postMeta[ setting ];
						}
						if ( 'undefined' !== typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ setting ] ) {
							parentValue = FusionApp.data.postMeta._fusion[ setting ];
						}
					}
				}

				// Use fake value if dynamic data is set.
				if ( '' === parentValue && ! hasParent && 'true' === self.$targetEl.find( '#' + setting ).closest( '.fusion-builder-option' ).attr( 'data-dynamic' ) ) {
					parentValue = 'using-dynamic-value';
				}

				// Get from element defaults.
				if ( ( 'undefined' === typeof parentValue || '' === parentValue ) && 'EO' === self.type && 'undefined' !== typeof fusionAllElements[ element ] && 'undefined' !== typeof fusionAllElements[ element ].defaults && 'undefined' !== typeof fusionAllElements[ element ].defaults[ setting ] ) {
					parentValue = fusionAllElements[ element ].defaults[ setting ];
				}

				// Hide 'flex / legacy' choice for containers when Header layout section is edited.
				if ( 'EO' === self.type && 'fusion_builder_container' === element && 'template_type' === setting && 'undefined' !== typeof FusionApp.data.template_category && 'header' === FusionApp.data.template_category ) {
					$passedArray.push( false );
					return;
				}

				// Hide or show 'flex / legacy' choice based on TO setting.
				if ( 'EO' === self.type && 'fusion_builder_container' === element && 'template_type' === setting ) {
					$passedArray.push( 'undefined' !== typeof FusionApp.settings.container_legacy_support && '1' === FusionApp.settings.container_legacy_support );
					return;
				}

				// Special check for parent container type.
				if ( 'EO' === self.type && 'fusion_builder_container' === setting && 'object' === typeof self.elementView ) {
					containerView = FusionPageBuilderViewManager.getView( self.elementView.model.get( 'parent' ) );

					if ( 'object' === typeof containerView ) {
						containerView = FusionPageBuilderApp.getParentContainer( containerView.model.get( 'parent' ) );
						if ( 'object' === typeof containerView ) {
							containerParams = 'object' === typeof containerView.values ? containerView.values : containerView.model.get( 'params' );
							parentValue     = containerParams[ ( 'undefined' !== typeof dependency.param ? dependency.param : 'type' ) ];
							$passedArray.push( self.doesTestPass( parentValue, value, operator ) );
							return;
						}
					}
				}

				if ( 'undefined' !== typeof parentValue ) {
					if ( 'TO' === self.type || 'FBE' === self.type ) {

						result = self.doesTestPass( parentValue, value, operator );

						if ( false === gutterCheck ) {
							if ( self.$targetEl.find( '[data-option-id=' + setting + ']' ).is( ':hidden' ) && ! self.$targetEl.find( '[data-option-id=' + setting + ']' ).closest( '.repeater-fields' ).length ) {
								result = false;
							}
						}

						$passedArray.push( Number( result ) );

					} else { // Page Options

						if ( '' === parentValue || 'default' === parentValue ) {

							if ( 'undefined' !== typeof FusionApp.settingsPoTo[ setting ] ) {

								// Get TO name
								toName = FusionApp.settingsPoTo[ setting ];

								// Get TO value
								parentValue = FusionApp.settings[ toName ];

								// Fix value names ( TO to PO )
								parentValue = self.fixPoToValue( parentValue );
							}
						}

						if ( 'EO' === self.type && 'undefined' !== typeof self.attributes[ setting ] && 'range' !== self.attributes[ setting ].type ) {

							// Fix value names ( TO to EO )
							parentValue = self.fixEoToValue( parentValue );
						}

						$passedArray.push( self.doesTestPass( parentValue, value, operator ) );
					}
				} else {

					// Check parent element values. For parent to child dependencies.
					if ( self.parentValues ) {
						if ( 'parent_' === setting.substring( 0, 7 ) ) {
							if ( 'object' === typeof self.parentValues && self.parentValues[ setting.replace( dependency.element.substring( 0, 7 ), '' ) ] ) {
								parentValue = self.parentValues[ setting.replace( dependency.element.substring( 0, 7 ), '' ) ];
							} else {
								parentValue = '';
							}
						}
					}

					$passedArray.push( self.doesTestPass( parentValue, value, operator ) );
				}

			} );

			return $passedArray;
		},

		/**
		 * Collect and return all dependencies.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		collectDependencies: function() {
			var self = this,
				dependency,
				optionName,
				setting,
				dependencies = [];

			jQuery.each( self.currentOptions, function( index, value ) {
				dependency = value[ self.dependencyKey ];

				// Dependency found
				if ( ! _.isUndefined( dependency ) ) {
					optionName = index;

					// Check each dependency for this option
					jQuery.each( dependency, function( i, opt ) {

						setting  = opt[ self.settingKey ];

						// If option has dependency add to check array.
						if ( _.isUndefined( dependencies[ setting ] ) ) {
							dependencies[ setting ] = [ { option: optionName, or: value.or } ];
						} else {
							dependencies[ setting ].push( { option: optionName, or: value.or } );
						}
					} );
				}
			} );

			self.dependencies = dependencies;
		},

		/**
		 * Collect IDs of options with dependencies.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		collectDependencyIds: function() {
			var self = this,
				dependency,
				setting,
				dependencyIds = '',
				parentDependencyIds = '';

			jQuery.each( self.currentOptions, function( index, value ) {
				dependency = value[ self.dependencyKey ];

				// Dependency found
				if ( ! _.isUndefined( dependency ) ) {

					// Check each dependency for this option
					jQuery.each( dependency, function( i, opt ) {
						setting = opt[ self.settingKey ];

						// Create IDs of fields to check for. ( Listeners )
						if ( 'parent_' === setting.substring( 0, 7 ) && 0 > parentDependencyIds.indexOf( '#' + setting.replace( 'parent_', '' ) ) ) {
							parentDependencyIds += ', #' + setting.replace( 'parent_', '' );
						} else if ( 0 > dependencyIds.indexOf( '#' + setting ) ) {
							dependencyIds += ', #' + setting;
						}
					} );
				}
			} );

			self.dependencyIds = dependencyIds;

			// Repeater, set parent dependency Ids.
			if ( '' !== parentDependencyIds && self.repeaterFields ) {
				self.parentDependencyIds = parentDependencyIds;
			}
		},

		/**
		 * Hide or show the control for an option.
		 *
		 * @since 2.0.0
		 * @param {boolean} [show]       Whether we want to hide or show the option.
		 * @param {string}  [optionName] The option-name.
		 * @return {void}
		 */
		hideShowOption: function( show, optionName ) {
			if ( show ) {
				this.$targetEl.find( '[data-option-id="' + optionName + '"]' ).fadeIn( 300 );
			} else {
				this.$targetEl.find( '[data-option-id="' + optionName + '"]' ).hide();
			}
		},

		/**
		 * Check option for fusion-or-gutter.
		 *
		 * @since 2.0.0
		 * @param {Object} option
		 * @return {Object}
		 */
		toGutterCheck: function( option ) {
			var singleOrGutter,
				gutterSequence,
				gutterCheck = false,
				gutter = {};

			singleOrGutter = ( ! _.isUndefined( option[ 'class' ] ) && 'fusion-or-gutter' === option[ 'class' ] ) ? option[ 'class' ] : false;

			if ( ! singleOrGutter ) {
				gutterSequence = ( ! _.isUndefined( option[ 'class' ] ) && 'fusion-or-gutter' !== option[ 'class' ] ) ? option[ 'class' ].replace( 'fusion-gutter-', '' ).split( '-' ) : false;
			}

			if ( singleOrGutter || gutterSequence ) {
				gutterCheck = true;
			}

			gutter = {
				single: singleOrGutter,
				sequence: gutterSequence,
				check: gutterCheck
			};

			return gutter;
		},

		/**
		 * Process dependencies for an option.
		 *
		 * @since 2.0.0
		 * @param {string} [currentId] The setting-ID.
		 * @return {void}
		 */
		processDependencies: function( currentId, view, fromParent ) {

			var self        = this,
				gutter      = {},
				childGutter = {},
				show        = false,
				optionName,
				passedArray,
				dependentOn,
				childOptionName,
				childDependencies,
				childPassedArray;

			if ( 'function' === typeof view.beforeProcessDependencies ) {
				view.beforeProcessDependencies();
			}

			// If fromParent is set we need to check for ID with parent_ added.
			if ( 'undefined' !== typeof fromParent && fromParent ) {
				currentId = 'parent_' + currentId;
			}

			// Loop through each option id that is dependent on this option.
			jQuery.each( self.dependencies[ currentId ], function( index, value ) {
				show        = false;
				optionName  = value.option;
				dependentOn = self.currentOptions[ optionName ][ self.dependencyKey ];
				passedArray = [];
				gutter      = {};

				if ( 'TO' === self.type || 'FBE' === self.type ) {

					// Check for fusion-or-gutter.
					gutter = self.toGutterCheck( self.currentOptions[ optionName ] );

					// Check each dependent option for that id.
					passedArray = self.buildPassedArray( dependentOn, gutter.check );

					// Show / Hide option.
					if ( gutter.sequence || gutter.single ) {
						show = self.checkGutterOptionVisibility( gutter.sequence, passedArray, gutter.single );
					} else {
						show = self.checkTOVisibility( passedArray );
					}

					self.hideShowOption( show, optionName, self.$targetEl );

					// Process children
					jQuery.each( self.dependencies[ optionName ], function( childIndex, childValue ) {
						childOptionName   = childValue.option;
						childDependencies = self.currentOptions[ childOptionName ][ self.dependencyKey ];
						show              = false;
						childGutter       = {};
						childPassedArray  = [];

						// Check for fusion-or-gutter.
						childGutter = self.toGutterCheck( self.currentOptions[ childOptionName ] );

						// Check each dependent option for that id.
						childPassedArray = self.buildPassedArray( childDependencies, childGutter.check );

						// Show / Hide option.
						if ( childGutter.sequence || childGutter.single ) {
							show = self.checkGutterOptionVisibility( childGutter.sequence, childPassedArray, childGutter.single );
						} else {
							show = self.checkTOVisibility( childPassedArray );
						}

						// Show / Hide option
						self.hideShowOption( show, childOptionName );
					} );

				} else if ( 'PO' === self.type || 'EO' === self.type ) {

					// Check each dependent option for that id.
					passedArray = self.buildPassedArray( dependentOn, gutter.check );

					// Show / Hide option.
					show = self.checkOptionVisibility( passedArray, value );
					self.hideShowOption( show, optionName );
				}
			} );
		},

		/**
		 * Compares option value with dependency value to determine if it passes or not.
		 *
		 * @since 2.0.0
		 * @param {mixed}  [parentValue] The first value in the check.
		 * @param {mixed}  [checkValue]  The 2nd value in the check.
		 * @param {string} [operation]   The check we want to perform.
		 * @return {boolean}
		 */
		doesTestPass: function( parentValue, checkValue, operation  ) {
			var show = false,
				arr,
				media;

			// If dependencies are disabled, always show the option.
			if ( 'undefined' !== FusionApp.settings.dependencies_status && 0 === parseInt( FusionApp.settings.dependencies_status ) ) {
				return true;
			}
			switch ( operation ) {
			case '=':
			case '==':
			case 'equals':

				if ( Array.isArray( parentValue ) ) {
					jQuery( parentValue[ 0 ] ).each(
						function( idx, val ) {
							if ( Array.isArray( checkValue ) ) {
								jQuery( checkValue ).each(
									function( i, v ) {
										if ( val == v ) { // jshint ignore: line
											show = true;
											return true;
										}
									}
								);
							} else if ( val == checkValue ) { // jshint ignore: line
								show = true;
								return true;
							}
						}
					);
				} else if ( Array.isArray( checkValue ) ) {
					jQuery( checkValue ).each(
						function( i, v ) {
							if ( parentValue == v ) { // jshint ignore: line
								show = true;
							}
						}
					);
				} else if ( parentValue == checkValue ) { // jshint ignore: line
					show = true;
				}
				break;

			case '!=':
			case 'not':
				if ( Array.isArray( parentValue ) ) {
					jQuery( parentValue ).each(
						function( idx, val ) {
							if ( Array.isArray( checkValue ) ) {
								jQuery( checkValue ).each(
									function( i, v ) {
										if ( val != v ) { // jshint ignore: line
											show = true;
											return true;
										}
									}
								);
							} else if ( val != checkValue ) { // jshint ignore: line
								show = true;
								return true;
							}
						}
					);
				} else if ( Array.isArray( checkValue ) ) {
					jQuery( checkValue ).each(
						function( i, v ) {
							if ( parentValue != v ) { // jshint ignore: line
								show = true;
							}
						}
					);
				} else if ( parentValue != checkValue ) { // jshint ignore: line
					show = true;
				}
				break;

			case '>':
			case 'greater':
			case 'is_larger':
				if ( parseFloat( parentValue ) > parseFloat( checkValue ) ) {
					show = true;
				}
				break;

			case '>=':
			case 'greater_equal':
			case 'is_larger_equal':
				if ( parseFloat( parentValue ) >= parseFloat( checkValue ) ) {
					show = true;
				}
				break;

			case '<':
			case 'less':
			case 'is_smaller':
				if ( parseFloat( parentValue ) < parseFloat( checkValue ) ) {
					show = true;
				}
				break;

			case '<=':
			case 'less_equal':
			case 'is_smaller_equal':
				if ( parseFloat( parentValue ) <= parseFloat( checkValue ) ) {
					show = true;
				}
				break;

			case 'contains':
				if ( jQuery.isPlainObject( parentValue ) ) {
					checkValue = Object.keys( checkValue ).map( function( key ) {
						return [ key, checkValue[ key ] ];
					} );
					parentValue = arr;
				}

				if ( jQuery.isPlainObject( checkValue ) ) {
					arr = Object.keys( checkValue ).map( function( key ) {
						return checkValue[ key ];
					} );
					checkValue = arr;
				}

				if ( Array.isArray( checkValue ) ) {
					jQuery( checkValue ).each(
						function( idx, val ) {
							var breakMe = false,
								toFind  = val[ 0 ],
								findVal = val[ 1 ];

							jQuery( parentValue ).each(
								function( i, v ) {
									var toMatch  = v[ 0 ],
										matchVal = v[ 1 ];

									if ( toFind === toMatch ) {
										if ( findVal == matchVal ) { // jshint ignore: line
											show = true;
											breakMe = true;

											return false;
										}
									}
								}
							);

							if ( true === breakMe ) {
								return false;
							}
						}
					);
				} else if ( -1 !== parentValue.toString().indexOf( checkValue ) ) {
					show = true;
				}

				break;

			case 'doesnt_contain':
			case 'not_contain':
				if ( jQuery.isPlainObject( parentValue ) ) {
					arr = Object.keys( parentValue ).map( function( key ) {
						return parentValue[ key ];
					} );
					parentValue = arr;
				}

				if ( jQuery.isPlainObject( checkValue ) ) {
					arr = Object.keys( checkValue ).map( function( key ) {
						return checkValue[ key ];
					} );
					checkValue = arr;
				}

				if ( Array.isArray( checkValue ) ) {
					jQuery( checkValue ).each(
						function( idx, val ) {
							if ( -1 === parentValue.toString().indexOf( val ) ) {
								show = true;
							}
						}
					);
				} else if ( -1 === parentValue.toString().indexOf( checkValue ) ) {
					show = true;
				}
				break;

			case 'is_empty_or':
				if ( '' === parentValue || parentValue == checkValue ) { // jshint ignore: line
					show = true;
				}
				break;

			case 'not_empty_and':
				if ( '' !== parentValue && parentValue != checkValue ) { // jshint ignore: line
					show = true;
				}
				break;

			case 'is_empty':
			case 'empty':
			case '!isset':
				if ( ! parentValue || '' === parentValue || null === parentValue ) {
					show = true;
				}
				break;

			case 'not_empty':
			case '!empty':
			case 'isset':
				if ( parentValue && '' !== parentValue && null !== parentValue ) {
					show = true;
				}
				break;

			case 'is_media':
				if ( parentValue ) {
					media = 'string' === typeof parentValue ? JSON.parse( parentValue ) : parentValue;
					if ( media && media.url ) {
						show = true;
					}
				}
				break;

			}

			return show;

		},

		/**
		 * Check page options & element options visibility.
		 *
		 * @since 2.0.0
		 * @return bool
		 */
		checkOptionVisibility: function( passedArray, value ) {
			var visible = false;

			if ( -1 === jQuery.inArray( false, passedArray ) && _.isUndefined( value.or ) ) {
				visible = true;
			} else if ( -1 !== jQuery.inArray( true, passedArray ) && ! _.isUndefined( value.or ) ) {
				visible = true;
			}

			return visible;
		},

		/**
		 * Check Global Option visibility.
		 *
		 * @since 2.0.0
		 * @return bool
		 */
		checkTOVisibility: function( passedArray ) {
			var visible = false;

			if ( -1 === jQuery.inArray( 0, passedArray ) ) {
				visible = true;
			}

			return visible;
		},

		/**
		 * Check option visibility for fusion-or-gutter options.
		 *
		 * @since 2.0.0
		 * @return bool
		 */
		checkGutterOptionVisibility: function( gutterSequence, passedArray, singleOrGutter ) {
			var overallDependencies = [],
				total               = 0,
				show                = false,
				i;

			if ( singleOrGutter ) {
				overallDependencies = passedArray;
			} else if ( 0 < gutterSequence.length ) {
				for ( i = 0; i < passedArray.length; i++ ) {

					if ( 0 === i ) {
						overallDependencies.push( passedArray[ i ] );
					} else if ( 'and' === gutterSequence[ i - 1 ] ) {
						overallDependencies[ overallDependencies.length - 1 ] = overallDependencies[ overallDependencies.length - 1 ] * passedArray[ i ];
					} else {
						overallDependencies.push( passedArray[ i ] );
					}
				}
			}

			for ( i = 0; i < overallDependencies.length; i++ ) {
				total += overallDependencies[ i ];
			}

			if ( 1 <= total ) {
				show = true;
			} else {
				show = false;
			}

			show = Boolean( show );

			return show;
		},

		/**
		 * Convert option values.
		 *
		 * @since 2.0.0
		 * @return string
		 */
		fixPoToValue: function( value ) {
			switch ( value ) {

			case 'hide':
			case '0':
				value = 'no';

				break;

			case 'show':
			case '1':
				value = 'yes';

				break;
			}

			return value;
		},

		/**
		 * Convert option values.
		 *
		 * @since 3.7.0
		 * @return string
		 */
		fixEoToValue: function( value ) {
			switch ( value ) {

			case '0':
				value = 'no';

				break;

			case '1':
				value = 'yes';

				break;
			}

			return value;
		},

		/**
		 * Process element option default values.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		processEoDefaults: function( view ) {
			var elementType     = view.model.get( 'element_type' ),
				elementDefaults = FusionApp.elementDefaults[ elementType ],
				toValue;

			if ( 'object' === typeof elementDefaults && 'object' === typeof elementDefaults.settings_to_params ) {
				_.each( elementDefaults.settings_to_params, function( eo, to ) {
					var option,
						type = '';

					toValue = FusionApp.settings[ to ];

					// Looking for sub value, get parent only.
					if ( -1 !== to.indexOf( '[' ) ) {
						to      = to.split( '[' )[ 0 ];
						toValue = FusionApp.settings[ to ];
					}

					// Get param if its an object.
					if ( 'object' === typeof eo ) {
						eo = eo.param;
					}

					option = view.$el.find( '#' + eo ).closest( '.fusion-builder-option' );

					if ( option.length ) {
						type = jQuery( option ).attr( 'class' ).split( ' ' ).pop();
					}

					if ( ! jQuery( option ).hasClass( 'fusion-builder-option range' ) ) {
						toValue = FusionApp.sidebarView.fixToValueName( to, toValue, type );
						view.$el.find( '.description [data-fusion-option="' + to + '"]' ).html( toValue );
					}
				} );
			}
		},

		/**
		 * Process page option default values.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		processPoDefaults: function( view ) {
			var thisEl = view.$el,
				toValue,
				poValue,
				subset,
				type = '',
				option;

			_.each( FusionApp.settingsPoTo, function( to, po ) {
				toValue = FusionApp.settings[ to ];

				if ( ! _.isUndefined( toValue ) ) {
					option  = thisEl.find( '[data-option-id="' + po + '"]' );
					poValue = option.val();

					if ( option.length ) {
						type = jQuery( option ).attr( 'class' ).replace( /\s+$/, '' ).split( ' ' ).pop();
					}

					subset = jQuery( option ).data( 'subset' );

					if ( 'default' !== poValue ) {

						toValue = FusionApp.sidebarView.fixToValueName( to, toValue, type, subset );

						option.find( '.description a' ).html( toValue );
					}
				}
			} );
		}

	} );

}( jQuery ) );
;var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.ViewManager = Backbone.Model.extend( {
		defaults: {
			elementCount: 0,
			views: {}
		},

		getViews: function() {
			return this.get( 'views' );
		},

		getView: function( cid ) {
			return this.get( 'views' )[ cid ];
		},

		getChildViews: function( parentID ) {
			var views      = this.get( 'views' ),
				childViews = {};

			_.each( views, function( view, key ) {
				if ( parentID === view.model.attributes.parent ) {
					childViews[ key ] = view;
				}
			} );

			return childViews;
		},

		generateCid: function() {
			var elementCount = this.get( 'elementCount' ) + 1;
			this.set( { elementCount: elementCount } );

			return elementCount;
		},

		addView: function( cid, view ) {
			var views = this.get( 'views' );

			views[ cid ] = view;
			this.set( { views: views } );
		},

		removeView: function( cid ) {
			var views    = this.get( 'views' ),
				updatedViews = {};

			_.each( views, function( value, key ) {
				if ( key != cid ) { // jshint ignore: line
					updatedViews[ key ] = value;
				}
			} );

			this.set( { views: updatedViews } );
		},

		removeViews: function() {
			var updatedViews = {};
			this.set( { views: updatedViews } );
		},

		countElementsByType: function( elementType ) {
			var views = this.get( 'views' ),
				num   = 0;

			_.each( views, function( view ) {
				if ( view.model.attributes.element_type === elementType ) {
					num++;
				}
			} );

			return num;
		},

		clear: function() {
			var views = this.get( 'views' );

			_.each( views, function( view ) {
				view.unbind();
				view.remove();
				delete view.$el;
				delete view.el;
			} );

			this.set( 'elementCount', 0 );
			this.set( 'views', {} );
		}

	} );

}( jQuery ) );
;var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.fusionActiveStates = {

	/**
	 * Preview toggle.
	 *
	 * @since 2.0.0
	 * @param {Object} event - The event.
	 * @param {Object|string} $target - The target element.
	 * @return {void}
	 */
	previewToggle: function( event, $target ) {
		var self     = this,
			type,
			selector,
			toggle,
			append,
			delay,
			data,
			persistent = true;

		$target  = 'undefined' === typeof $target ? jQuery( event.currentTarget ) : $target;
		type     = $target.data( 'type' );
		selector = $target.data( 'selector' );
		toggle   = 'undefined' !== typeof $target.data( 'toggle' ) ? $target.data( 'toggle' ) : '';
		append   = 'undefined' !== typeof $target.data( 'append' ) ? $target.data( 'append' ) : false;
		delay    = -1 !== selector.indexOf( '$el' ) ? 300 : 0,
		data     = {
			type: type,
			selector: selector,
			toggle: toggle,
			append: append
		};

		if ( event ) {
			event.preventDefault();
		}

		// If it is animations we need to remove active state since it is not persistent.
		if ( 'animation' === type && 'fusion_content_boxes' !== this.model.get( 'element_type' ) ) {
			persistent = false;
		}

		// If target is already active we active, else we deactivate.
		if ( ! $target.hasClass( 'active' ) ) {

			// Persistent state, set it active.
			if ( persistent ) {
				this.activeStates[ selector + '-' + type + '-' + toggle ] = data;
			}

			// If we are targetting the element itself we need a timeout.
			setTimeout( function() {
				self.triggerActiveState( data );
			}, delay );

		} else {

			// We want to remove it
			if ( 'undefined' !== typeof this.activeStates[ selector + '-' + type + '-' + toggle ] ) {
				this.activeStates[ selector + '-' + type + '-' + toggle ] = false;
			}

			// If we are targetting the element itself we need a timeout.
			setTimeout( function() {
				self.triggerRemoveState( data );
			}, delay );
		}

		// Toggle all at same time that are the same.
		if ( persistent ) {
			this.$el.find( '[data-type="' + type + '"][data-selector="' + selector + '"][data-toggle="' + toggle + '"]' ).toggleClass( 'active' );
		}
	},

	/**
	 * Trigger the actual state change.
	 *
	 * @since 2.0.0
	 * @param {Object} data - Data for state change.
	 * @return {void}
	 */
	triggerActiveState: function( data ) {
		var self = this,
			selectors,
			$targetEl = this.$targetEl && this.$targetEl.length ? this.$targetEl : jQuery( '#fb-preview' ).contents().find( '.fusion-builder-live' ),
			$target,
			animationDuration,
			animationDelay;

		if ( 'string' === typeof data.selector && -1 !== data.selector.indexOf( '$el' ) ) {
			$target = $targetEl;
		} else if ( $targetEl.hasClass( 'fusion-builder-column' ) || $targetEl.hasClass( 'fusion-builder-container' ) ) {
			$target = $targetEl.find( data.selector );
		} else if ( $targetEl.hasClass( 'fusion-builder-live-element' ) ) {
			$target = $targetEl.find( '.fusion-builder-element-content ' + data.selector );
		} else if ( $targetEl.hasClass( 'fusion-builder-live-child-element' ) ) {
			$target = $targetEl.find( '.fusion-builder-child-element-content ' + data.selector );
		}

		if ( 'undefined' === typeof $target || ! $target.length ) {
			return;
		}

		if ( 'animation' === data.type ) {
			if ( 'fusion_content_boxes' === this.model.get( 'element_type' ) ) {
				this.contentBoxAnimations( data );
				return;
			}

			if ( ( 'fusion_post_cards' === this.model.get( 'element_type' ) || 'fusion_tb_post_card_archives' === this.model.get( 'element_type' ) ) && $target.hasClass( 'fusion-delayed-animation' ) ) {
				this.postCardsAnimations( data, $target );
				return;
			}

			$target.each( function() {
				var $singleTarget = jQuery( this );

				data.toggle       = $singleTarget.attr( 'data-animationtype' );
				animationDuration = $singleTarget.attr( 'data-animationduration' );
				animationDelay    = $singleTarget.attr( 'data-animationdelay' );

				$singleTarget.css( '-moz-animation-duration', animationDuration + 's' );
				$singleTarget.css( '-webkit-animation-duration', animationDuration + 's' );
				$singleTarget.css( '-o-animation-duration', animationDuration + 's' );
				$singleTarget.css( 'animation-duration', animationDuration + 's' );

				$singleTarget.css( 'animation-delay', animationDelay + 's' );

				$singleTarget.removeClass( _.fusionGetAnimationTypes().join( ' ' ) );

				setTimeout( function() {
					$singleTarget.addClass( data.toggle );
				}, 50 );
			} );
			return;
		}

		// Set the state.
		if ( data.append ) {
			selectors = data.selector.split( ',' );
			_.each( selectors, function( selector ) {
				$target = $targetEl.find( selector );
				if ( $target.length ) {
					$target.addClass( selector.replace( '.', '' ) + data.toggle );
				}
			} );
		} else {
			$target.addClass( data.toggle );
		}

		// Add one time listener in case use interacts with target.
		$target.one( 'mouseleave', function() {
			self.$el.find( '[data-type="' + data.type + '"][data-selector="' + data.selector + '"][data-toggle="' + data.toggle + '"]' ).removeClass( 'active' );
			self.activeStates[ data.selector + '-' + data.type + '-' + data.toggle ] = false;
			self.triggerRemoveState( data );
		} );
	},

	/**
	 * Removes already active state.
	 *
	 * @since 2.0.0
	 * @param {Object} data - Data for state change.
	 * @return {void}
	 */
	triggerRemoveState: function( data ) {
		var selectors,
			$targetEl = this.$targetEl && this.$targetEl.length ? this.$targetEl : jQuery( '#fb-preview' ).contents().find( '.fusion-builder-live' ),
			$target;

		if ( 'string' === typeof data.selector && -1 !== data.selector.indexOf( '$el' ) ) {
			$target = $targetEl;
		} else if ( $targetEl.hasClass( 'fusion-builder-column' ) ) {
			$target = $targetEl.find( data.selector );
		} else if ( $targetEl.hasClass( 'fusion-builder-live-element' ) ) {
			$target = $targetEl.find( '.fusion-builder-element-content ' + data.selector );
		} else if ( $targetEl.hasClass( 'fusion-builder-live-child-element' ) ) {
			$target = $targetEl.find( '.fusion-builder-child-element-content ' + data.selector );
		}

		if ( 'undefined' === typeof $target || ! $target.length ) {
			return;
		}

		if ( 'animation' === data.type ) {
			$target.each( function() {
				var $singleTarget = jQuery( this );
				data.toggle       = $singleTarget.attr( 'data-animationtype' );
				$singleTarget.removeClass( data.toggle );
			} );
			return;
		}

		// Set the state.
		if ( data.append ) {
			selectors = data.selector.split( ',' );
			_.each( selectors, function( selector ) {

				$target.removeClass( selector.replace( '.', '' ) + data.toggle );
			} );
		} else {
			$target.removeClass( data.toggle );
		}
	},

	/**
	 * Adds a temporary state.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - Option node.
	 * @return {void}
	 */
	triggerTemporaryState: function( $option ) {
		if ( $option.find( '.option-preview-toggle' ).length && ! $option.find( '.option-preview-toggle' ).hasClass( 'active' ) ) {
			this.previewToggle( false, $option.find( '.option-preview-toggle' ) );
			this._tempStateRemove( $option );
		}
	},

	/**
	 * Triggers removal of state.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - Option node.
	 * @return {void}
	 */
	tempStateRemove: function( $option ) {
		if ( $option.find( '.option-preview-toggle' ).length && $option.find( '.option-preview-toggle' ).hasClass( 'active' ) ) {
			this.previewToggle( false, $option.find( '.option-preview-toggle' ) );
		}
	},

	/**
	 * Make sure any active states are set again after render.
	 *
	 * @since 2.0.0
	 * @return {void}
	 */
	triggerActiveStates: function() {

		var self = this;

		_.each( this.activeStates, function( state ) {
			self.triggerActiveState( state );
		} );
	},

	/**
	 * Make sure all states are removed on close.
	 *
	 * @since 2.0.0
	 * @return {void}
	 */
	removeActiveStates: function() {

		var self = this;

		_.each( this.activeStates, function( state ) {
			self.triggerRemoveState( state );
		} );
	},

	contentBoxAnimations: function() {
		var $delay    = 0,
			$targetEl = this.$targetEl && this.$targetEl.length ? this.$targetEl : jQuery( '#fb-preview' ).contents().find( '.fusion-builder-live' );

		$targetEl.find( '.content-box-column' ).each( function() {
			var $element = jQuery( this ),
				$target = $element.find( '.fusion-animated' ),
				$animationType,
				$animationDuration;

			setTimeout( function() {
				$target.css( 'visibility', 'visible' );

				// This code is executed for each appeared element
				$animationType = $target.data( 'animationtype' );
				$animationDuration = $target.data( 'animationduration' );

				$target.addClass( $animationType );

				if ( $animationDuration ) {
					$target.css( '-moz-animation-duration', $animationDuration + 's' );
					$target.css( '-webkit-animation-duration', $animationDuration + 's' );
					$target.css( '-o-animation-duration', $animationDuration + 's' );
					$target.css( 'animation-duration', $animationDuration + 's' );
				}

				if ( $element.closest( '.fusion-content-boxes' ).hasClass( 'content-boxes-timeline-horizontal' ) ||
					$element.closest( '.fusion-content-boxes' ).hasClass( 'content-boxes-timeline-vertical' ) ) {
					$element.addClass( 'fusion-appear' );
				}
				setTimeout( function() {
					$target.removeClass( $animationType );
				}, $animationDuration * 1000 );
			}, $delay );

			$delay += parseInt( jQuery( this ).closest( '.fusion-content-boxes' ).attr( 'data-animation-delay' ), 10 );
		} );
	},

	postCardsAnimations: function( data, $element ) {
		var $postCards        = $element,
			delay             = 0,
			animationType     = $postCards.attr( 'data-animationtype' ),
			animationDuration = $postCards.attr( 'data-animationduration' ),
			animationDelay    = parseInt( $postCards.attr( 'data-animation-delay' ) * 1000, 10 );

		$postCards.find( '.fusion-grid-column' ).css( 'visibility', 'hidden' ).each( function() {
			var $target = jQuery( this );

			setTimeout( function() {
				$target.css( 'visibility', 'visible' );
				$target.addClass( animationType );

				if ( animationDuration ) {
					$target.css( '-moz-animation-duration', animationDuration + 's' );
					$target.css( '-webkit-animation-duration', animationDuration + 's' );
					$target.css( '-o-animation-duration', animationDuration + 's' );
					$target.css( 'animation-duration', animationDuration + 's' );
				}

				setTimeout( function() {
					$target.removeClass( animationType );
				}, animationDuration * 1000 );
			}, delay );

			delay += animationDelay;
		} );
	}
};
;/* global FusionEvents, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Hotkeys = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			var self = this;

			jQuery( 'body' ).on( 'keydown', function( event ) {
				if ( self.isValidTarget( event ) ) {
					self.checkKey( event );
				}
			} );
		},

		/**
		 * Reattach listeners for iframe.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		attachListener: function() {
			var self = this;
			jQuery( document.getElementById( 'fb-preview' ).contentWindow.document ).off( 'keydown' );
			jQuery( document.getElementById( 'fb-preview' ).contentWindow.document ).on( 'keydown', function( event ) {
				if ( self.isValidTarget( event ) ) {
					self.checkKey( event );
				}
			} );
		},

		/**
		 * Check combination of keys pressed.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {void}
		 */
		checkKey: function( event ) {

			// If disabled.
			if ( ( 'undefined' !== typeof FusionApp && 'undefined' !== typeof FusionApp.preferencesData && 'off' === FusionApp.preferencesData.keyboard_shortcuts ) ) {
				return;
			}

			if ( event.ctrlKey || event.metaKey || event.shiftKey ) {

				// If only Shift key.
				if ( this.isShiftKey( event ) && ! this.isMetaKey( event ) ) {
					switch ( event.keyCode ) {

					// Key Shift + P for Preview.
					case 80:
						event.preventDefault();
						jQuery( '.fusion-toolbar-nav li.preview a' ).trigger( 'click' );
						break;

						// Key Shift + T to toggle sidebar.
					case 84:
						if ( 'undefined' !== typeof FusionApp.sidebarView ) {
							event.preventDefault();
							FusionApp.sidebarView.togglePanel();
						}
						break;
					}
				}

				// If only meta key.
				if ( this.isMetaKey( event ) && ! this.isShiftKey( event ) ) {
					switch ( event.keyCode ) {

					// Return key to close modal.
					case 13:
						if ( 0 < jQuery( '.fusion-builder-dialog .ui-dialog-buttonset .ui-button' ).length ) {
							jQuery( '.fusion-builder-dialog .ui-dialog-buttonset .ui-button' ).trigger( 'click' );
						} else {
							jQuery( '.fusion-builder-dialog .ui-button.ui-dialog-titlebar-close' ).trigger( 'click' );
						}

						break;

						// Key 1 for large view.
					case 49:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-desktop' ).trigger( 'click' );
						break;

						// Key 2 for mobile view portrait.
					case 50:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-mobile.portrait' ).trigger( 'click' );
						break;

						// Key 3 for mobile view landscape.
					case 51:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-mobile.landscape' ).trigger( 'click' );
						break;

						// Key 4 for tablet view portrait.
					case 52:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-tablet.portrait' ).trigger( 'click' );
						break;

						// Key 5 for tablet view landscape.
					case 53:
						event.preventDefault();
						jQuery( '.fusion-builder-preview-tablet.landscape' ).trigger( 'click' );
						break;

						// Key D to clear layout.
					case 68:
						event.preventDefault();
						jQuery( '.fusion-builder-clear-layout' ).trigger( 'click' );
						break;

						// Key Q to exit the builder.
					case 81:
						event.preventDefault();
						jQuery( '.fusion-exit-builder-list .exit-to-back-end a' ).trigger( 'click' );
						break;

						// Key S to save, click rather than save directly so that animations occur.
					case 83:
						event.preventDefault();
						if ( ! jQuery( '.fusion-builder-save-page' ).data( 'disabled' ) ) {
							jQuery( '.fusion-builder-save-page' ).trigger( 'click' );
						}
						break;

						// Key Y to redo builder change.
					case 89:
						event.preventDefault();
						FusionEvents.trigger( 'fusion-history-redo' );
						break;

						// Key Z to undo builder change.
					case 90:
						event.preventDefault();
						FusionEvents.trigger( 'fusion-history-undo' );
						break;
					}
				}

				// If both shift and meta key.
				if ( this.isMetaKey( event ) && this.isShiftKey( event ) ) {
					switch ( event.keyCode ) {

					// Key C to open custom css panel.
					case 67:
						if ( 0 === jQuery( 'body' ).find( '.ui-dialog' ).length && 'undefined' !== typeof FusionApp.sidebarView ) {
							event.preventDefault();
							FusionApp.sidebarView.openOption( '_fusion_builder_custom_css', 'po' );
						}
						break;

						// Key S to save, click rather than save directly so that animations occur.
					case 83:
						event.preventDefault();
						if ( 0 === jQuery( 'body' ).find( '.ui-dialog' ).length && ! FusionApp.sidebarView.panelIsOpen() ) {
							jQuery( '.fusion-builder-save-template' ).trigger( 'click' );
						}
						break;
					}
				}
			}
		},

		/**
		 * Checks if meta key is pressed.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {boolean} - Returns the bool value.
		 */
		isMetaKey: function( event ) {
			if ( event.ctrlKey || event.metaKey ) {
				return true;
			}

			return false;
		},

		/**
		 * Checks if shift key is pressed.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {boolean} - Returns the bool value.
		 */
		isShiftKey: function( event ) {
			if ( event.shiftKey ) {
				return true;
			}

			return false;
		},

		/**
		 * Checks if target is valid.
		 *
		 * @since 2.0.0
		 * @param {Object} [event] Contains event data.
		 * @return {boolean} - Returns the bool value.
		 */
		isValidTarget: function( event ) {

			//Ctrl+S/CMD+S always valid
			if (
				( 83 === event.keyCode && event.metaKey && !event.altKey ) || // CMD + s MacOS
				( 83 === event.keyCode && event.ctrlKey && !event.altKey ) // Ctrl + s Windows
			) {
				return true;
			}


			if ( jQuery( event.target ).is( 'input' ) || jQuery( event.target ).is( 'textarea' ) || jQuery( event.target ).is( '.fusion-live-editable' ) ) {
				return false;
			}

			return true;
		}
	} );

}( jQuery ) );
;var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	/**
	 * Fetch a JavaScript template for an id, and return a templating function for it.
	 *
	 * @param  {string} id   A string that corresponds to a DOM element
	 * @return {Function}    A function that lazily-compiles the template requested.
	 */
	FusionPageBuilder.template = _.memoize( function( html ) {
		var compiled,

			/*
			 * Underscore's default ERB-style templates are incompatible with PHP
			 * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
			 */
			options = {
				evaluate: /<#([\s\S]+?)#>/g,
				interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
				escape: /\{\{([^\}]+?)\}\}(?!\})/g // eslint-disable-line no-useless-escape
			};

		return function( data ) {
			compiled = compiled || _.template( html, null, options );
			return compiled( data );
		};
	} );
}( jQuery ) );
;/* global builderConfig, awbTypoData, FusionPageBuilder, builderId, fusionSettings, FusionPageBuilderApp, fusionAllElements, fusionAppConfig, FusionApp, fusionOptionName, fusionBuilderText, fusionIconSearch */
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
;/* global rangy, MediumEditor, FusionApp, fusionAllElements, fusionHistoryManager, fusionBuilderText, awbTypographySelect */
/* eslint no-unused-vars: 0 */
/* eslint no-shadow: 0 */
/* eslint no-undef: 0 */
/* eslint no-mixed-operators: 0 */
/* eslint no-empty: 0 */
/* eslint no-redeclare: 0 */
/* eslint no-unreachable: 0 */
/* eslint no-extend-native: 0 */
/* eslint no-native-reassign: 0 */
/* eslint radix: 0 */
/* eslint no-global-assign: 0 */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.inlineEditor = Backbone.Model.extend( {

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			rangy.init();
			this.createExtended();
			this.createTypography();
			this.createFontColor();
			this.createInlineShortcode();
			this.createAlign();
			this.createAnchor();
			this.createRemove();
			this.createIndent();
			this.createOutdent();

			Number.prototype.countDecimals = function() {
				if ( Math.floor( this.valueOf() ) === this.valueOf() ) {
					return 0;
				}
				return this.toString().split( '.' )[ 1 ].length || 0;
			};
		},

		/**
		 * Creates the font-size extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createExtended: function( event ) { // jshint ignore: line
			var FusionExtendedForm = MediumEditor.extensions.form.extend( {
				name: 'fusionExtended',
				action: 'fusionExtended',
				aria: fusionBuilderText.extended_options,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-ellipsis" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.subscribe( 'editableDrop', this.dragDisable.bind( this ) );
					this.subscribe( 'editableDrag', this.dragDisable.bind( this ) );
				},

				handleClick: function( event ) {
					var toolbar  = this.base.getExtensionByName( 'toolbar' );

					event.preventDefault();
					event.stopPropagation();

					toolbar.toolbar.querySelector( '.medium-editor-toolbar-actions' ).classList.toggle( 'alternative-active' );

					this.setToolbarPosition();

					return false;
				},

				dragDisable: function( event ) {
					if ( jQuery( event.target ).hasClass( '.fusion-inline-element' ) || jQuery( event.target ).find( '.fusion-inline-element' ).length ) {
						event.preventDefault();
						event.stopPropagation();
					}
				}
			} );

			MediumEditor.extensions.fusionExtended = FusionExtendedForm;
		},

		/**
		 * Creates the alignment extension.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createAlign: function( event ) { // jshint ignore: line
			var FusionAlignForm = MediumEditor.extensions.form.extend( {
				name: 'fusionAlign',
				action: 'fusionAlign',
				aria: fusionBuilderText.align_text,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-align-center" aria-hidden="true"></i>',
				hasForm: true,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
				},

				checkState: function( node ) {
					var nodes     = MediumEditor.selection.getSelectedElements( this.document ),
						align     = this.getExistingValue( nodes ),
						iconClass = 'fusiona-align-';

					if ( 'undefined' !== typeof align && nodes.length ) {
						align = 'start' === align ? 'left' : align.replace( '-moz-', '' );
						jQuery( this.button ).find( 'i' ).attr( 'class', iconClass + align );
					}
				},

				// Called when the button the toolbar is clicked
				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {
					var toolbar  = this.base.getExtensionByName( 'toolbar' );

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {
						toolbar.hideExtensionForms();
						this.showForm();
					}

					return false;
				},

				// Get text alignment.
				getExistingValue: function( nodes ) {
					var nodeIndex,
						el,
						align = 'left';

					// If there are no nodes, use the parent el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el    = nodes[ nodeIndex ];
						align = jQuery( el ).css( 'text-align' );
					}

					return align;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var $form = jQuery( this.getForm() );
					$form.find( '.medium-editor-button-active' ).removeClass( 'medium-editor-button-active' );
					$form.removeClass( 'visible' ).addClass( 'hidden' );
					setTimeout( function() {
						$form.removeClass( 'hidden' );
					}, 400 );
				},

				showForm: function() {
					var nodes = MediumEditor.selection.getSelectedElements( this.document ),
						value = this.getExistingValue( nodes ),
						form  = this.getForm(),
						targetEl;

					value = 'start' === value ? 'left' : value;

					this.base.saveSelection();
					this.hideToolbarDefaultActions();
					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					targetEl = form.querySelector( '.fusion-align-' + value );
					if ( targetEl ) {
						targetEl.classList.add( 'medium-editor-button-active' );
					}
					this.setToolbarPosition();
				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				// Form creation and event handling
				createForm: function() {
					var doc           = this.document,
						form          = doc.createElement( 'div' ),
						ul            = doc.createElement( 'ul' ),
						alignLeft     = doc.createElement( 'button' ),
						alignCenter   = doc.createElement( 'button' ),
						alignRight    = doc.createElement( 'button' ),
						alignJustify  = doc.createElement( 'button' ),
						closeForm     = doc.createElement( 'button' ),
						li            = doc.createElement( 'li' ),
						icon          = doc.createElement( 'i' );

					this.base.saveSelection();

					// Font Name Form (div)
					form.className = 'medium-editor-toolbar-form medium-editor-alternate-toolbar';
					form.id        = 'medium-editor-toolbar-form-align-' + this.getEditorId();
					ul.className   = 'medium-editor-toolbar-actions';

					// Left align.
					icon.className      = 'fusiona-align-left';
					alignLeft.className = 'fusion-align-left';
					alignLeft.setAttribute( 'title', fusionBuilderText.align_left );
					alignLeft.setAttribute( 'aria-label', fusionBuilderText.align_left );
					alignLeft.setAttribute( 'data-action', 'justifyLeft' );
					alignLeft.appendChild( icon );
					li.appendChild( alignLeft );
					ul.appendChild( li );
					this.on( alignLeft, 'click', this.applyAlignment.bind( this ), true );

					// Center align.
					li                  = doc.createElement( 'li' );
					icon                = doc.createElement( 'i' );
					icon.className      = 'fusiona-align-center';
					alignCenter.className = 'fusion-align-center';
					alignCenter.setAttribute( 'title', fusionBuilderText.align_center );
					alignCenter.setAttribute( 'aria-label', fusionBuilderText.align_center );
					alignCenter.setAttribute( 'data-action', 'justifyCenter' );
					alignCenter.appendChild( icon );
					li.appendChild( alignCenter );
					ul.appendChild( li );
					this.on( alignCenter, 'click', this.applyAlignment.bind( this ), true );

					// Right align.
					li                   = doc.createElement( 'li' );
					icon                 = doc.createElement( 'i' );
					icon.className       = 'fusiona-align-right';
					alignRight.className = 'fusion-align-right';
					alignRight.setAttribute( 'title', fusionBuilderText.align_right );
					alignRight.setAttribute( 'aria-label', fusionBuilderText.align_right );
					alignRight.setAttribute( 'data-action', 'justifyRight' );
					alignRight.appendChild( icon );
					li.appendChild( alignRight );
					ul.appendChild( li );
					this.on( alignRight, 'click', this.applyAlignment.bind( this ), true );

					// Justify align.
					li                     = doc.createElement( 'li' );
					icon                   = doc.createElement( 'i' );
					icon.className         = 'fusiona-align-justify';
					alignJustify.className = 'fusion-align-justify';
					alignJustify.setAttribute( 'title', fusionBuilderText.align_justify );
					alignJustify.setAttribute( 'aria-label', fusionBuilderText.align_justify );
					alignJustify.setAttribute( 'data-action', 'justifyFull' );
					alignJustify.appendChild( icon );
					li.appendChild( alignJustify );
					ul.appendChild( li );
					this.on( alignJustify, 'click', this.applyAlignment.bind( this ), true );

					// Close icon.
					li                     = doc.createElement( 'li' );
					icon                   = doc.createElement( 'i' );
					icon.className         = 'fusiona-check';
					closeForm.setAttribute( 'title', fusionBuilderText.accept );
					closeForm.setAttribute( 'aria-label', fusionBuilderText.accept );
					closeForm.appendChild( icon );
					li.appendChild( closeForm );
					ul.appendChild( li );
					this.on( closeForm, 'click', this.closeForm.bind( this ), true );

					form.appendChild( ul );

					return form;
				},

				applyAlignment: function( event ) {
					var action  = event.currentTarget.getAttribute( 'data-action' ),
						$target = jQuery( event.currentTarget ),
						iconClass = $target.find( 'i' ).attr( 'class' );

					$target.closest( 'ul' ).find( '.medium-editor-button-active' ).removeClass( 'medium-editor-button-active' );
					$target.addClass( 'medium-editor-button-active' );

					jQuery( this.button ).find( 'i' ).attr( 'class', iconClass );

					this.base.restoreSelection();

					this.execAction( action, { skipCheck: true } );
				},

				closeForm: function() {
					this.hideForm();
					this.base.checkSelection();
				}
			} );

			MediumEditor.extensions.fusionAlign = FusionAlignForm;
		},

		/**
		 * Creates the typography extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createTypography: function( event ) { // jshint ignore: line
			var fusionTypographyForm = MediumEditor.extensions.form.extend( {

				name: 'fusionTypography',
				action: 'fusionTypography',
				aria: fusionBuilderText.typography,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-font-solid" aria-hidden="true"></i>',
				hasForm: true,
				fonts: [],
				loadPreviews: false,
				override: false,
				parentCid: false,
				searchFonts: [],
				overrideParams: [
					'font-size',
					'line-height',
					'letter-spacing',
					'tag',
					'font-family'
				],
				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'span',
						tagNames: [ 'span', 'b', 'strong', 'a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );

					this._handleInputChange = _.debounce( _.bind( this.handleInputChange, this ), 100 );
				},

				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {
					var nodes,
						font;

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {

						this.showForm();
					}

					return false;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var self         = this,
						form         = this.getForm(),
						toolbar      = this.base.getExtensionByName( 'toolbar' ),
						timeoutValue = 50;

					if ( toolbar.toolbar.classList.contains( 'medium-toolbar-arrow-over' ) ) {
						timeoutValue = 300;
					}

					form.classList.add( 'hidden' );
					jQuery( form ).find( '.fusion-options-wrapper' ).removeClass( 'visible' );
					form.classList.remove( 'visible' );
					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );

					setTimeout( function() {
						self.setToolbarPosition();
						self.base.checkSelection();
					}, timeoutValue );

				},

				showForm: function() {
					var self    = this,
						form    = this.getForm(),
						actives = form.querySelectorAll( '.active' ),
						link    = form.querySelector( '[href="#settings"]' ),
						tab     = form.querySelector( '[data-id="settings"]' );

					this.base.saveSelection();
					this.hideToolbarDefaultActions();

					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}
					if ( link ) {
						link.classList.add( 'active' );
					}
					if ( tab ) {
						tab.classList.add( 'active' );
					}

					if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
						jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
							self.insertFamilyChoices();
							self.setFontFamilyValues();
						} );
					} else {
						this.insertFamilyChoices();
						this.setFontFamilyValues();
					}

					this.setToolbarPosition();

					this.setTagValue();
					this.setFontStyleValues();
				},

				// Get font size which is set.
				getExistingTag: function() {
					var nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange ),
						tag            = 'p',
						nodeIndex,
						el;

					if ( 'undefined' !== typeof FusionPageBuilderApp ) {
						FusionPageBuilderApp.inlineEditorHelpers.setOverrideParams( this, this.overrideParams );
					}

					// Check for parent el first.
					if ( parentEl ) {
						nodes = [ parentEl ];
					}

					// If there are no nodes, use the base el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el  = nodes[ nodeIndex ];
						tag = el.nodeName.toLowerCase();
					}

					return tag;
				},
				setTagValue: function() {
					var tag       = this.getExistingTag(),
						form      = this.getForm(),
						tagsHold  = form.querySelector( '.typography-tags' ),
						newTag    = form.querySelector( '[data-val="' + tag + '"]' );

					if ( newTag ) {
						newTag.classList.add( 'active' );
					}
				},

				// Get font size which is set.
				getExistingStyleValues: function( ) {
					var nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange ),
						nodeIndex,
						el,
						values;

					// Check for parent el first.
					if ( parentEl ) {
						nodes = [ MediumEditor.selection.getSelectedParentElement( selectionRange ) ];
					}

					// If there are no nodes, use the base el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el                   = nodes[ nodeIndex ];
						values               = {};
						values.size          = window.getComputedStyle( el, null ).getPropertyValue( 'font-size' );
						values.lineHeight    = window.getComputedStyle( el, null ).getPropertyValue( 'line-height' );
						values.letterSpacing = window.getComputedStyle( el, null ).getPropertyValue( 'letter-spacing' );

						// If it is set in the style attribute, use that.
						if ( 'undefined' !== typeof el.style.fontSize && el.style.fontSize && -1 === el.style.fontSize.indexOf( 'var(' ) ) {
							values.size = el.style.fontSize;
						}

						if ( 'undefined' !== typeof el.style.lineHeight && el.style.lineHeight && -1 === el.style.lineHeight.indexOf( 'var(' ) ) {
							values.lineHeight = el.style.lineHeight;
						}
						if ( 'undefined' !== typeof el.style.letterSpacing && el.style.letterSpacing && -1 === el.style.letterSpacing.indexOf( 'var(' ) ) {
							values.letterSpacing = el.style.letterSpacing;
						}

						// If it is data-fusion-font then prioritise that.
						if ( el.hasAttribute( 'data-fusion-font' ) ) {
							return values;
						}
					}

					return values;
				},

				getExistingFamilyValues: function() {
					var self           = this,
						nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange ),
						values         = {
							variant: 'regular',
							variantLabel: 'Default',
							family: ''
						},
						nodeIndex,
						el;

					// Check for parent el first.
					if ( parentEl ) {
						nodes = [ MediumEditor.selection.getSelectedParentElement( selectionRange ) ];
					}

					// If there are no nodes, use the base el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el = nodes[ nodeIndex ];
						values.family = window.getComputedStyle( el, null ).getPropertyValue( 'font-family' );
						if ( -1 !== values.family.indexOf( ',' ) ) {
							values.family = values.family.split( ',' )[ 0 ];
						}

						// If it is set in the style attribute, use that.
						if ( 'undefined' !== typeof el.style.fontFamily && el.style.fontFamily ) {
							values.family = el.style.fontFamily;
						}
						if ( el.hasAttribute( 'data-fusion-google-font' ) ) {
							values.family = el.getAttribute( 'data-fusion-google-font' );
						}
						values.family = values.family.replace( /"/g, '' ).replace( /'/g, '' );

						if ( el.hasAttribute( 'data-fusion-google-variant' ) ) {
							values.variant = el.getAttribute( 'data-fusion-google-variant' );
							if ( ! _.isUndefined( window.awbTypographySelect ) && ! _.isUndefined( window.awbTypographySelect.webfonts ) ) {
								variants = self.getVariants( values.family );
								_.each( variants, function( variant ) {
									if ( values.variant === variant.id ) {
										values.variantLabel = variant.label;
									}
								} );
							}
						}

						// If it is data-fusion-font then prioritise that.
						if ( el.hasAttribute( 'data-fusion-font' ) ) {
							return values;
						}
					}

					return values;

				},

				setFontFamilyValues: function( form ) {
					var values        = this.getExistingFamilyValues(),
						form          = this.getForm(),
						familyHold    = form.querySelector( '.typography-family' ),
						family        = familyHold.querySelector( '[data-value="' + values.family + '"]' ),
						variant       = form.querySelector( '#fusion-variant' ),
						variants      = form.querySelector( '.fuson-options-holder.variant' ),
						rect;

					if ( family ) {
						family.classList.add( 'active' );
					}
					if ( variant ) {
						variant.setAttribute( 'data-value', values.variant );
						variant.innerHTML = values.variantLabel;
					}
					if ( variants ) {
						this.updateVariants( values.family );
					}
				},

				setFontStyleValues: function() {
					var values        = this.getExistingStyleValues(),
						form          = this.getForm(),
						fontSize      = form.querySelector( '#font_size' ),
						lineHeight    = form.querySelector( '#line_height' ),
						letterSpacing = form.querySelector( '#letter_spacing' );

					if ( fontSize ) {
						fontSize.setAttribute( 'value', values.size );
						fontSize.value = values.size;
					}
					if ( lineHeight ) {
						lineHeight.setAttribute( 'value', values.lineHeight );
						lineHeight.value = values.lineHeight;
					}
					if ( letterSpacing ) {
						letterSpacing.setAttribute( 'value', values.letterSpacing );
						letterSpacing.value = values.letterSpacing;
					}
				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				doFormSave: function() {
					this.hideForm();
				},

				visibleY: function( el, rectTop, rectBottom ) {
					var rect   = el.getBoundingClientRect(),
						top    = rect.top,
						height = rect.height;

					if ( el.classList.contains( 'visible' ) ) {
						return false;
					}

					rect = familyHold.getBoundingClientRect();

					if ( false === top <= rectBottom ) {
						return false;
					}
					if ( ( top + height ) <= rectTop ) {
						return false;
					}

					return true;
				},

				getClosest: function( elem, selector ) {

					// Element.matches() polyfill
					if ( ! Element.prototype.matches ) {
						Element.prototype.matches =
							Element.prototype.matchesSelector ||
							Element.prototype.mozMatchesSelector ||
							Element.prototype.msMatchesSelector ||
							Element.prototype.oMatchesSelector ||
							Element.prototype.webkitMatchesSelector ||
							function( s ) {
								var matches = ( this.document || this.ownerDocument ).querySelectorAll( s ),
									i       = matches.length;

								while ( this !== 0 <= --i && matches.item( i ) ) {}
								return -1 < i;
							};
					}

					// Get the closest matching element
					for ( ; elem && elem !== document; elem = elem.parentNode ) {
						if ( elem.matches( selector ) ) {
							return elem;
						}
					}
					return null;
				},

				// Form creation and event handling
				createForm: function() {
					var self   = this,
						doc    = this.document,
						form   = doc.createElement( 'div' ),
						select = doc.createElement( 'select' ),
						close  = doc.createElement( 'a' ),
						save   = doc.createElement( 'a' ),
						option,
						i,
						navHold,
						settingsLink,
						familyLink,
						closeButton,
						tabHold,
						typographyTags,
						tags,
						typographyStyling,
						styles,
						familyTab,
						familyOptions,
						familyVariant,
						familyVariantSelect,
						familyVariantVisible,
						familyVariantOptionsHolder,
						familyVariantOptions;

					form.className = 'medium-editor-toolbar-form fusion-inline-typography';
					form.id        = 'medium-editor-toolbar-form-fontname-' + this.getEditorId();

					// Create the typography tab nav.
					navHold           = doc.createElement( 'div' );
					navHold.className = 'fusion-typography-nav';

					settingsLink = doc.createElement( 'a' );
					settingsLink.setAttribute( 'href', '#settings' );
					settingsLink.innerHTML = fusionBuilderText.typography_settings;
					settingsLink.className = 'active';
					navHold.appendChild( settingsLink );

					familyLink = doc.createElement( 'a' );
					familyLink.setAttribute( 'href', '#family' );
					familyLink.innerHTML = fusionBuilderText.typography_family;
					navHold.appendChild( familyLink );

					closeButton           = doc.createElement( 'button' );
					closeButton.className = 'fusion-inline-editor-close';
					closeButton.innerHTML = '<i class="fusiona-check" aria-hidden="true"></i>';
					navHold.appendChild( closeButton );

					tabHold = doc.createElement( 'div' );
					tabHold.className = 'fusion-typography-tabs';

					// Settings tab.
					settingsTab = doc.createElement( 'div' );
					settingsTab.setAttribute( 'data-id', 'settings' );
					settingsTab.className = 'active';
					tabHold.appendChild( settingsTab );

					// Tags bar.
					typographyTags           = doc.createElement( 'div' );
					typographyTags.className = 'typography-tags';
					typographyTags.innerHTML = '<span>' + fusionBuilderText.typography_tag + '</span>';

					tags = [ 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ];

					_.each( tags, function( tag ) {
						var button = doc.createElement( 'button' );

						if ( 1 === tag.length ) {
							button.innerHTML = tag;
						} else if ( 2 === tag.length ) {
							button.className = 'fusiona-' + tag;
						}
						button.setAttribute( 'data-val', tag );

						self.on( button, 'click', function() {
							var actives  = typographyTags.querySelectorAll( '.active' ),
								isActive = button.classList.contains( 'active' ),
								value    = 'p' === tag ? undefined : tag.replace( 'h', '' );

							if ( actives ) {
								_.each( actives, function( active ) {
									active.classList.remove( 'active' );
								} );
							}

							self.base.restoreSelection();

							// If we have an element override, update view param instead.
							if ( 'undefined' === typeof FusionPageBuilderApp || ! FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( self.parentCid, self.override.tag, value ) ) {
								self.execAction( 'append-' + tag, { skipCheck: true } );
							} else {

								// Tag changes editor, which means toolbar must close and reinit.
								self.base.checkSelection();
							}

							if ( ! isActive || tag === self.getExistingTag() ) {
								button.classList.add( 'active' );
							}
						} );

						typographyTags.appendChild( button );
					} );
					settingsTab.appendChild( typographyTags );

					// Styling bar.
					typographyStyling           = doc.createElement( 'div' );
					typographyStyling.className = 'typography-styling';

					styles = [
						{ label: fusionBuilderText.typography_fontsize, id: 'font_size', name: 'fontSize' },
						{ label: fusionBuilderText.typography_lineheight, id: 'line_height', name: 'lineHeight' },
						{ label: fusionBuilderText.typography_letterspacing, id: 'letter_spacing', name: 'letterSpacing' }
					];

					_.each( styles, function( style ) {
						var wrapper  = doc.createElement( 'div' ),
							label    = doc.createElement( 'label' ),
							input    = doc.createElement( 'input' ),
							inputUp  = doc.createElement( 'button' ),
							inputDown = doc.createElement( 'button' );

						label.setAttribute( 'for', style.id );
						label.innerHTML = style.label;

						input.setAttribute( 'type', 'text' );
						input.setAttribute( 'name', style.name );
						input.setAttribute( 'id', style.id );
						input.value = '';

						inputUp.className   = 'fusiona-arrow-up';
						inputDown.className = 'fusiona-arrow-down';

						wrapper.appendChild( label );
						wrapper.appendChild( input );
						wrapper.appendChild( inputUp );
						wrapper.appendChild( inputDown );

						self.on( input, 'change',  self._handleInputChange.bind( self ) );
						self.on( input, 'blur',  self._handleInputChange.bind( self ) );
						self.on( input, 'fusion-change',  self.handleInputChange.bind( self ) );

						self.on( inputUp, 'click', function() {
							var value  = input.value,
								number,
								unit,
								decimals;

							if ( ! value && 0 !== value ) {
								return;
							}

							number = parseFloat( value, 10 );

							if ( ! number && 0 !== number ) {
								return;
							}

							unit       = value.replace( number, '' );
							decimals   = number.countDecimals();
							increment  = 0 === decimals ? 1 : 1 / Math.pow( 10, decimals );
							number     = ( number + increment ).toFixed( decimals );

							input.value = number + unit;
							input.dispatchEvent( new Event( 'fusion-change' ) );
						} );

						self.on( inputDown, 'click', function() {
							var value  = input.value,
								number,
								unit,
								decimals;

							if ( ! value ) {
								return;
							}

							number = parseFloat( value, 10 );

							if ( ! number ) {
								return;
							}

							unit       = value.replace( number, '' );
							decimals   = number.countDecimals();
							increment  = 0 === decimals ? 1 : 1 / Math.pow( 10, decimals );
							number     = ( number - increment ).toFixed( decimals );

							input.value = number + unit;
							input.dispatchEvent( new Event( 'fusion-change' ) );
						} );

						typographyStyling.appendChild( wrapper );
					} );
					settingsTab.appendChild( typographyStyling );

					// Family tab.
					familyTab = doc.createElement( 'div' );
					familyTab.setAttribute( 'data-id', 'family' );
					tabHold.appendChild( familyTab );

					// Family selector.
					familyHold = doc.createElement( 'div' );
					familyHold.className = 'typography-family';

					if ( this.loadPreviews ) {
						this.on( familyHold, 'scroll', function() {
							var options    = familyHold.getElementsByTagName( 'div' ),
								rect       = familyHold.getBoundingClientRect(),
								rectTop    = rect.top,
								rectBottom = rect.bottom;

							_.each( options, function( option ) {
								var family = option.getAttribute( 'data-value' );
								if ( self.visibleY( option, rectTop, rectBottom ) ) {
									option.classList.add( 'visible' );
									self.webFontLoad( family );
								}
							} );
						} );
					}

					familyTab.appendChild( familyHold );

					// Right sidebar for family tab.
					familyOptions = doc.createElement( 'div' );
					familyOptions.className = 'typography-family-options';

					// Family variant.
					familyVariant = doc.createElement( 'div' );
					familyVariantVisible = doc.createElement( 'div' );
					familyVariantVisible.className = 'fusion-select-wrapper';
					familyVariantVisible.innerHTML = '<label for="variant">' + fusionBuilderText.typography_variant + '</label>';

					familyVariantSelect = doc.createElement( 'div' );
					familyVariantSelect.className = 'fusion-select fusion-selected-value';
					familyVariantSelect.id        = 'fusion-variant';
					familyVariantSelect.setAttribute( 'data-name', 'variant' );
					familyVariantSelect.setAttribute( 'data-id', 'variant' );

					familyVariantVisible.appendChild( familyVariantSelect );

					familyVariantOptions                 = doc.createElement( 'div' );
					familyVariantOptions.className       = 'fusion-options-wrapper variant';
					familyVariantOptions.innerHTML       = '<label for="variant">' + fusionBuilderText.typography_variant + '</label>';
					familyVariantOptionsHolder           = doc.createElement( 'div' );
					familyVariantOptionsHolder.className = 'fuson-options-holder variant';
					familyVariantOptions.appendChild( familyVariantOptionsHolder );

					familyVariant.appendChild( familyVariantVisible );
					familyVariant.appendChild( familyVariantOptions );

					familyOptions.appendChild( familyVariant );

					familyTab.appendChild( familyOptions );

					form.appendChild( navHold );
					form.appendChild( tabHold );

					// Handle clicks on the form itself
					this.on( form, 'click', this.handleFormClick.bind( this ) );

					// Tab clicks.
					this.on( settingsLink, 'click', this.handleTabClick.bind( this ) );
					this.on( familyLink, 'click', this.handleTabClick.bind( this ) );

					// Variant clicks.
					this.on( familyVariantVisible, 'click', this.handleVariantClick.bind( this ) );
					this.on( familyVariantOptionsHolder, 'click', this.handleOptionClick.bind( this ) );

					// Form saves.
					this.on( closeButton, 'click', this.doFormSave.bind( this ) );

					return form;
				},

				handleVariantClick: function( event ) {
					var form        = this.getForm(),
						selected    = event.currentTarget.querySelector( '.fusion-selected-value' ),
						active      = selected.getAttribute( 'data-value' ),
						type        = selected.getAttribute( 'data-id' ),
						activesHold = form.querySelector( '.fuson-options-holder.' + type ),
						actives     = activesHold.querySelectorAll( '.active' ),
						target      = activesHold.querySelector( '[data-value="' + active + '"]' ),
						dropdowns   = form.querySelectorAll( '.fusion-options-wrapper' ),
						targetDrop  = form.querySelector( '.fusion-options-wrapper.' + type );

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}

					if ( target ) {
						target.classList.add( 'active' );
					}

					if ( dropdowns ) {
						_.each( dropdowns, function( dropdown ) {
							dropdown.classList.remove( 'visible' );
						} );
					}

					if ( targetDrop ) {
						targetDrop.classList.add( 'visible' );
					}
				},

				handleOptionClick: function( event ) {
					var targetParent;

					if ( event.target.classList.contains( 'fusion-select' ) ) {
						targetParent = this.getClosest( event.target, '.fusion-options-wrapper' );
						if ( targetParent ) {
							targetParent.classList.remove( 'visible' );
						}
					}
				},

				insertFamilyChoices: function( familyHold ) {
					var self        = this,
						familyHold  = 'undefined' === typeof familyHold ? this.getForm().querySelector( '.typography-family' ) : familyHold,
						doc         = this.document,
						searchHold  = doc.createElement( 'div' ),
						search      = doc.createElement( 'input' ),
						searchIcon  = doc.createElement( 'span' ),
						searchFonts = [];

					if ( familyHold.hasChildNodes() || 'undefined' === typeof window.awbTypographySelect.webfonts ) {
						return;
					}

					// Add the search.
					searchIcon.classList.add( 'fusiona-search' );

					self.on( searchIcon, 'click', function( event ) {
						var parent = event.target.parentNode,
							searchInput;

						parent.classList.toggle( 'open' );
						if ( parent.classList.contains( 'open' ) ) {
							parent.querySelector( 'input' ).focus();
						} else {
							searchInput = parent.querySelector( 'input' );
							searchInput.value = '';
							searchInput.dispatchEvent( new Event( 'change' ) );
						}
						self.getForm().querySelector( '.typography-family' ).classList.remove( 'showing-results' );
					} );

					searchHold.appendChild( searchIcon );

					search.setAttribute( 'type', 'search' );
					search.setAttribute( 'name', 'fusion-ifs' );
					search.setAttribute( 'id', 'fusion-ifs' );
					search.placeholder = fusionBuilderText.search;

					self.on( search, 'keydown',  self.handleFontSearch.bind( self ) );
					self.on( search, 'input',  self.handleFontSearch.bind( self ) );
					self.on( search, 'change',  self.handleFontSearch.bind( self ) );

					searchHold.classList.add( 'fusion-ifs-hold' );
					searchHold.appendChild( search );

					familyHold.parentNode.appendChild( searchHold );

					// Add the custom fonts.
					if ( 'object' === typeof window.awbTypographySelect.webfonts.custom && ! _.isEmpty( window.awbTypographySelect.webfonts.custom ) ) {

						// Extra check for different empty.
						if ( 1 !== window.awbTypographySelect.webfonts.custom.length || ! ( 'object' === typeof window.awbTypographySelect.webfonts.custom[ 0 ] && '' === window.awbTypographySelect.webfonts.custom[ 0 ].family ) ) {
							option           = doc.createElement( 'div' );
							option.innerHTML = fusionBuilderText.custom_fonts;
							option.classList.add( 'fusion-cfh' );
							familyHold.appendChild( option );

							_.each( window.awbTypographySelect.webfonts.custom, function( font, index ) {
								if ( font.family && '' !== font.family ) {
									searchFonts.push( {
										id: font.family.replace( /&quot;/g, '&#39' ),
										text: font.label
									} );
								}

								option = doc.createElement( 'div' );
								option.innerHTML = font.label;
								option.setAttribute( 'data-value', font.family );
								option.setAttribute( 'data-id', font.family.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() );
								option.setAttribute( 'data-type', 'custom-font' );

								self.on( option, 'click',  self.handleFontChange.bind( self ) );

								familyHold.appendChild( option );
							} );
						}
					}

					// Add the google fonts.
					_.each( window.awbTypographySelect.webfonts.google, function( font, index ) {
						searchFonts.push( {
							id: font.family,
							text: font.label
						} );

						option = doc.createElement( 'div' );
						option.innerHTML = font.label;
						option.setAttribute( 'data-value', font.family );
						option.setAttribute( 'data-id', font.family.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() );

						if ( self.loadPreviews ) {
							option.setAttribute( 'style', 'font-family:' + font.family );
							if ( 5 > index ) {
								self.webFontLoad( font.family );
								option.classList.add( 'visible' );
							}
						}

						self.on( option, 'click',  self.handleFontChange.bind( self ) );

						familyHold.appendChild( option );
					} );

					this.searchFonts = searchFonts;
				},

				handleFontSearch: function( event ) {
					var form          = this.getForm(),
						value         = event.target.value,
						$searchHold   = jQuery( form ).find( '.fusion-ifs-hold' ),
						$selectField  = jQuery( form ).find( '.typography-family' ),
						fuseOptions,
						fuse,
						result;

					$selectField.scrollTop( 0 );

					if ( 3 > value.length ) {
						$selectField.find( '> div' ).css( 'display', 'block' );
						return;
					}

					$selectField.find( '> div' ).css( 'display', 'none' );

					fuseOptions = {
						threshold: 0.2,
						location: 0,
						distance: 100,
						maxPatternLength: 32,
						minMatchCharLength: 3,
						keys: [ 'text' ]
					};

					fuse   = new Fuse( jQuery.extend( true, this.searchFonts, {} ), fuseOptions );
					result = fuse.search( value );

					_.each( result, function( resultFont ) {
						$selectField.find( 'div[data-id="' + resultFont.id.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() + '"]' ).css( 'display', 'block' );
					} );

					$selectField.addClass( 'showing-results' );
				},

				handleTabClick: function( event ) {
					var form         = this.getForm(),
						link         = event.currentTarget,
						target       = link.getAttribute( 'href' ).replace( '#', '' ),
						tabHold      = form.querySelector( '.fusion-typography-tabs' ),
						navHold      = form.querySelector( '.fusion-typography-nav' ),
						familyHold   = form.querySelector( '.typography-family' ),
						activeFamily = familyHold.querySelector( '.active' ),
						scrollAmount;

					_.each( tabHold.children, function( tab ) {
						if ( target !== tab.getAttribute( 'data-id' ) ) {
							if ( tab.classList.contains( 'active' ) ) {
								tab.classList.remove( 'active' );
							}
						} else {
							tab.classList.add( 'active' );
						}
					} );

					_.each( navHold.querySelectorAll( '.active' ), function( nav ) {
						nav.classList.remove( 'active' );
					} );
					link.classList.add( 'active' );

					if ( ! familyHold.firstChild ) {
						this.insertFamilyChoices();
					} else if ( 'family' === target && activeFamily ) {
						scrollAmount = ( activeFamily.getBoundingClientRect().top + familyHold.scrollTop ) - familyHold.getBoundingClientRect().top;
						scrollAmount = 0 === scrollAmount ? 0 : scrollAmount - 6 - activeFamily.getBoundingClientRect().height;
						familyHold.scrollTop = scrollAmount;
					}
				},

				getParamFromTarget: function( target ) {
					switch ( target ) {
					case 'letterSpacing':
						return 'letter-spacing';
						break;

					case 'lineHeight':
						return 'line-height';
						break;

					case 'fontSize':
						return 'font-size';
						break;

					default:
						return target;
						break;
					}
				},

				handleInputChange: function( event ) { // jshint ignore: line
					var form       = this.getForm(),
						value      = event.target.value,
						target     = event.target.name,
						action     = {},
						iframe     = document.getElementById( 'fb-preview' ),
						iframeWin  = rangy.dom.getIframeWindow( iframe ),
						lineHeight = false,
						param      = this.getParamFromTarget( target ),
						element;

					this.base.restoreSelection();

					element = MediumEditor.selection.getSelectionElement( this.document );

					if ( ! element ) {
						return;
					}

					// If we have an element override, update view param instead.
					if ( 'undefined' !== typeof FusionPageBuilderApp && FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( this.parentCid, this.override[ param ], value ) ) {
						return;
					}

					this.classApplier.applyToSelection( iframeWin );

					action[ target ] = value;

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						jQuery( el ).css( action );
						el.setAttribute( 'data-fusion-font', true );
						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}

						// If font size is changed and line-height not set, update input.
						if ( 'fontSize' === target && ! lineHeight ) {
							lineHeight = form.querySelector( '#line_height' );
							lineHeight.value = 'undefined' !== typeof el.style.lineHeight && el.style.lineHeight ? el.style.lineHeight : window.getComputedStyle( el, null ).getPropertyValue( 'line-height' );
						}
					} );

					this.base.saveSelection();

					this.base.trigger( 'editableInput', {}, element );
				},
				handleFontChange: function( event ) {
					var value    = event.target.getAttribute( 'data-value' ),
						font     = event.target.classList.contains( 'fusion-select' ) ? this.getFontFamily() : value,
						self     = this,
						variant  = value;

					if ( event.target.classList.contains( 'fusion-variant-select' ) ) {
						this.updateSingleVariant( value, event.target.innerHTML );
					} else {
						this.updateSingleFamily();
						variant = this.updateVariants( font );
					}

					event.target.classList.add( 'active' );

					if ( ! font ) {
						return;
					}

					if ( -1 !== window.awbTypographySelect.webfontsStandardArray.indexOf( font ) || this.isCustomFont( font ) ) {
						this.changePreview( font, false, variant );

					} else if ( this.webFontLoad( font, variant, false ) ) {
						self.changePreview( font, true, variant );
					} else {
						jQuery( window ).one( 'fusion-font-loaded', function() {
							self.changePreview( font, true, variant );
						} );
					}
				},

				getFontFamily: function() {
					var form        = this.getForm(),
						familyHold  = form.querySelector( '.typography-family' ),
						active      = familyHold.querySelector( '.active' );

					if ( active ) {
						return active.getAttribute( 'data-value' );
					}
					return false;
				},

				getFontVariant: function() {
					var form   = this.getForm(),
						input  = form.querySelector( '#fusion-variant' );

					if ( input ) {
						return input.getAttribute( 'data-value' );
					}
					return false;
				},

				updateSingleVariant: function( value, label ) {
					var form        = this.getForm(),
						inputDiv    = form.querySelector( '#fusion-variant' ),
						optionsHold = form.querySelector( '.fuson-options-holder.variant' ),
						actives     = optionsHold.querySelectorAll( '.active' );

					inputDiv.setAttribute( 'data-value', value );
					inputDiv.innerHTML = label;

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}
				},

				updateSingleFamily: function( value, label ) {
					var form       = this.getForm(),
						familyHold = form.querySelector( '.typography-family' ),
						actives    = familyHold.querySelectorAll( '.active' );

					if ( actives ) {
						_.each( actives, function( active ) {
							active.classList.remove( 'active' );
						} );
					}
				},

				updateVariants: function( font ) {
					var self         = this,
						variants     = this.getVariants( font ),
						form         = this.getForm(),
						holder       = form.querySelector( '.fuson-options-holder.variant' ),
						inputDiv     = form.querySelector( '#fusion-variant' ),
						doc          = this.document,
						hasSelection = false,
						defaultVal   = 'regular',
						currentVal   = inputDiv.getAttribute( 'data-value' );

					while ( holder.firstChild ) {
						holder.removeChild( holder.firstChild );
					}

					if ( ! variants ) {
						variants = [
							{
								id: 'regular',
								label: fusionBuilderText.typography_default
							}
						];
					}

					// If currentVal is within variants, then use as default.
					if ( _.contains( _.pluck( variants, 'id' ), currentVal ) ) {
						defaultVal = currentVal;
					}

					_.each( variants, function( variant ) {
						var option = doc.createElement( 'div' );

						option.className = 'fusion-select fusion-variant-select';
						option.innerHTML = variant.label;
						option.setAttribute( 'data-value', variant.id );

						if ( defaultVal === variant.id ) {
							hasSelection = true;
							option.classList.add( 'active' );
							inputDiv.setAttribute( 'data-value', variant.id );
							inputDiv.innerHTML = variant.label;
						}

						self.on( option, 'click',  self.handleFontChange.bind( self ) );
						holder.appendChild( option );
					} );

					if ( ! hasSelection && holder.firstChild ) {
						holder.firstChild.classList.add( 'active' );
						defaultVal = holder.firstChild.getAttribute( 'data-value' );
						inputDiv.setAttribute( 'data-value', defaultVal );
						inputDiv.innerHTML = holder.firstChild.innerHTML;
					}

					return defaultVal;
				},

				changePreview: function( font, googleFont, variant ) {
					var iframe     = document.getElementById( 'fb-preview' ),
						iframeWin  = rangy.dom.getIframeWindow( iframe ),
						fontWeight = '',
						fontStyle  = '',
						element;

					if ( googleFont && variant ) {
						fontWeight = awbTypographySelect.getFontWeightFromVariant( variant );
						fontStyle  = awbTypographySelect.getFontStyleFromVariant( variant );
					}

					this.base.restoreSelection();

					element = MediumEditor.selection.getSelectionElement( this.document );

					if ( ! element ) {
						return;
					}

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						el.style[ 'font-family' ] = font;
						el.style[ 'font-style' ]  = fontStyle;
						el.style[ 'font-weight' ] = fontWeight;

						el.setAttribute( 'data-fusion-font', true );

						if ( googleFont ) {
							el.setAttribute( 'data-fusion-google-font', font );

							// Variant handling.
							if ( '' !== variant ) {
								el.setAttribute( 'data-fusion-google-variant', variant );
							} else {
								el.removeAttribute( 'data-fusion-google-variant' );
							}

						} else {
							el.removeAttribute( 'data-fusion-google-font' );
							el.removeAttribute( 'data-fusion-google-variant' );
						}

						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
					} );

					this.base.saveSelection();
					this.base.trigger( 'editableInput', {}, element );
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				// TODO: refactor this so its easier to lookup.
				getVariants: function( fontFamily ) {
					var variants = false;

					// Family is a variable, variant only has that selection.
					if ( -1 !== fontFamily.indexOf( 'var(' ) ) {
						return [
							{
								id: fontFamily.replace( '-font-family)', ')' ),
								label: awbTypoData.strings.global
							}
						];
					}

					if ( this.isCustomFont( fontFamily ) ) {
						return [
							{
								id: '400',
								label: 'Normal 400'
							}
						];
					}

					_.each( window.awbTypographySelect.webfonts.standard, function( font ) {
						if ( fontFamily && font.family === fontFamily ) {
							variants = font.variants;
							return font.variants;
						}
					} );

					_.each( window.awbTypographySelect.webfonts.google, function( font ) {
						if ( font.family === fontFamily ) {
							variants = font.variants;
							return font.variants;
						}
					} );
					return variants;
				},

				isCustomFont: function( family ) {
					var isCustom = false;

					// Figure out if this is a google-font.
					_.each( window.awbTypographySelect.webfonts.custom, function( font ) {
						if ( font.family === family ) {
							isCustom = true;
						}
					} );

					return isCustom;
				},
				webFontLoad: function( family, variant ) {
					var isGoogleFont = this.isGoogleFont( family ),
						scriptID,
						script;

					// Early exit if there is no font-family defined.
					if ( _.isUndefined( family ) || '' === family || ! family ) {
						return;
					}

					// Get a valid variant.
					variant = this.getValidVariant( family, variant );

					// Early exit if not a google-font.
					if ( false === isGoogleFont ) {
						return;
					}

					variant = ( _.isUndefined( variant ) || ! variant ) ? ':regular' : ':' + variant;
					family  = family.replace( /"/g, '&quot' );

					script  = family;
					script += ( variant ) ? variant : '';

					scriptID = script.replace( /:/g, '' ).replace( /"/g, '' ).replace( /'/g, '' ).replace( / /g, '' ).replace( /,/, '' );

					if ( ! jQuery( 'head' ).find( '#' + scriptID ).length ) {
						jQuery( 'head' ).first().append( '<script id="' + scriptID + '">WebFont.load({google:{families:["' + script + '"]},context:FusionApp.previewWindow,active: function(){ jQuery( window ).trigger( "fusion-font-loaded"); },});</script>' );
						return false;
					}
					return true;
				},

				isGoogleFont: function( family ) {
					var isGoogleFont = false;

					// Figure out if this is a google-font.
					_.each( window.awbTypographySelect.webfonts.google, function( font ) {
						if ( font.family === family ) {
							isGoogleFont = true;
						}
					} );

					return isGoogleFont;
				},

				getValidVariant: function( family, variant ) {
					var variants   = this.getVariants( family ),
						isValid    = false,
						hasRegular = false,
						first      = ( ! _.isUndefined( variants[ 0 ] ) && ! _.isUndefined( variants[ 0 ].id ) ) ? variants[ 0 ].id : '400';

					if ( 'string' !== typeof variant || '' === variant ) {
						variant = '400';
					}

					// Variable family, set variant value as same variable.
					if ( -1 !== family.indexOf( 'var(' ) ) {
						return family.replace( '-font-family)', ')' );
					}
					if ( this.isCustomFont( family ) ) {
						return '400';
					}

					_.each( variants, function( v ) {
						if ( variant === v.id ) {
							isValid = true;
						}
						if ( 'regular' === v.id || '400' === v.id || 400 === v.id ) {
							hasRegular = true;
						}
					} );

					if ( isValid ) {
						return variant;
					} else if ( hasRegular ) {
						return '400';
					}
					return first;
				}
			} );
			MediumEditor.extensions.fusionTypography = fusionTypographyForm;
		},

		/**
		 * Creates the font-color extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createFontColor: function( event ) { // jshint ignore: line
			var FusionFontColorForm = MediumEditor.extensions.form.extend( {
				name: 'fusionFontColor',
				action: 'fusionFontColor',
				aria: fusionBuilderText.font_color,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusion-color-preview" aria-hidden="true"></i>',
				hasForm: true,
				override: false,
				parentCid: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'span',
						tagNames: [ 'span', 'b', 'strong', 'a', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );

					this._triggerUpdate = _.debounce( _.bind( this.triggerUpdate, this ), 300 );
				},
				checkState: function( node ) {
					var nodes = MediumEditor.selection.getSelectedElements( this.document ),
						color = this.getExistingValue( nodes );

					if ( 'undefined' !== typeof color ) {
						this.button.querySelector( '.fusion-color-preview' ).style.backgroundColor = color;
					}
				},

				// Called when the button the toolbar is clicked
				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {
					var nodes,
						font;

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {

						// Get FontName of current selection (convert to string since IE returns this as number)
						nodes = MediumEditor.selection.getSelectedElements( this.document );
						font  = this.getExistingValue( nodes );
						font  = 'undefined' !== typeof font ? font : '';
						this.showForm( font );
					}

					return false;
				},

				// Get font size which is set.
				getExistingValue: function( nodes ) {
					var nodeIndex,
						color,
						el;

					if ( 'undefined' !== typeof FusionPageBuilderApp ) {
						FusionPageBuilderApp.inlineEditorHelpers.setOverrideParams( this, 'color' );
					}

					// If there are no nodes, use the parent el.
					if ( ! nodes.length ) {
						nodes = this.base.elements;
					}

					for ( nodeIndex = 0; nodeIndex < nodes.length; nodeIndex++ ) {
						el    = nodes[ nodeIndex ];
						color = 'string' == typeof el.style.color && '' !== el.style.color ? el.style.color : jQuery( el ).css( 'color' );
						if ( jQuery( el ).data( 'fusion-font' ) ) {
							return color;
						}
					}

					return color;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					this.on( this.form, 'click', this.handleFormClick.bind( this ) );
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var self         = this,
						form         = this.getForm(),
						toolbar      = this.base.getExtensionByName( 'toolbar' ),
						timeoutValue = 50;

					if ( toolbar.toolbar.classList.contains( 'medium-toolbar-arrow-over' ) ) {
						timeoutValue = 300;
					}

					form.classList.add( 'hidden' );
					form.classList.remove( 'visible' );

					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );

					this.getInput().value = '';

					setTimeout( function() {
						self.setToolbarPosition();
						self.base.checkSelection();
					}, timeoutValue );
				},

				showForm: function( fontColor ) {
					var self   = this,
						input  = this.getInput(),
						$input = jQuery( input ),
						form   = this.getForm();

					this.base.saveSelection();
					this.hideToolbarDefaultActions();
					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					this.setToolbarPosition();

					$input.val( fontColor || '' ).trigger( 'change' );

					if ( 'undefined' === typeof $input.awbColorPicker( 'instance' ) ) {
						$input.awbColorPicker( {
							width: 250,
							hide: true,
							allowToggle: false,
							change: function( event, ui, value ) {
								self.handleColorChange( value );
							},
							clear: function( event, ui ) {
								self.clearFontColor();
							}
						} );
					}

					if ( -1 === $input.val().indexOf( '--' ) ) {
						$input.awbColorPicker( 'open' );
					} else {
						$input.awbColorPicker( 'openGlobals' );
					}
				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				doFormSave: function() {

					this.hideForm();
				},

				// Form creation and event handling
				createForm: function() {
					var self   = this,
						doc    = this.document,
						form   = doc.createElement( 'div' ),
						input  = doc.createElement( 'input' ),
						close  = doc.createElement( 'button' );

					// Font Color Form (div)
					this.on( form, 'click', this.handleFormClick.bind( this ) );
					form.className = 'medium-editor-toolbar-form fusion-inline-color-picker';
					form.id        = 'medium-editor-toolbar-form-fontcolor-' + this.getEditorId();

					input.className = 'medium-editor-toolbar-input fusion-builder-color-picker-hex';
					input.type      = 'text';
					input.setAttribute( 'data-alpha', true );
					form.appendChild( input );

					close.className = 'fusion-inline-editor-close';
					close.innerHTML = '<i class="fusiona-check" aria-hidden="true"></i>';
					form.appendChild( close );

					// Handle save button clicks (capture)
					this.on( close, 'click', this.handleSaveClick.bind( this ), true );

					return form;
				},

				getInput: function() {
					return this.getForm().querySelector( 'input.medium-editor-toolbar-input' );
				},

				clearFontColor: function() {

					this.base.restoreSelection();

					// If we have an element override, update view param instead.
					if ( 'undefined' !== typeof FusionPageBuilderApp && FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( this.parentCid, this.override, '' ) ) {
						return;
					}

					MediumEditor.selection.getSelectedElements( this.document ).forEach( function( el ) {
						if ( 'undefined' !== typeof el.style && 'undefined' !== typeof el.style.color ) {
							el.style.color = '';
						}
					} );

					this.base.trigger( 'editableInput', {}, MediumEditor.selection.getSelectionElement( this.document ) );

				},

				handleColorChange: function( color ) {
					var iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						element,
						color = 'undefined' === color || 'undefined' === typeof color ? this.getInput().value : color;

					this.base.restoreSelection();

					// If we have an element override, update view param instead.
					if ( 'undefined' !== typeof FusionPageBuilderApp && FusionPageBuilderApp.inlineEditorHelpers.updateParentElementParam( this.parentCid, this.override, color, true ) ) {
						return;
					}

					element = MediumEditor.selection.getSelectionElement( this.document );

					if ( ! element ) {
						return;
					}

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						if ( el.classList.contains( 'fusion-editing' ) ) {
							jQuery( el ).css( { color: color } );
							el.classList.remove( 'fusion-editing' );

							if ( 0 === el.classList.length ) {
								el.removeAttribute( 'class' );
							}
						}
					} );

					this._triggerUpdate( element );
				},

				triggerUpdate: function( element ) {
					this.base.trigger( 'editableInput', {}, element );
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				handleSaveClick: function( event ) {

					// Clicking Save -> create the font size
					event.preventDefault();
					this.doFormSave();
				}
			} );

			MediumEditor.extensions.fusionFontColor = FusionFontColorForm;
		},

		/**
		 * Creates the drop-cap extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createInlineShortcode: function( event ) { // jshint ignore: line
			var FusionInlineShortcodeForm = MediumEditor.extensions.form.extend( {
				name: 'fusionInlineShortcode',
				action: 'fusionInlineShortcode',
				aria: fusionBuilderText.add_element,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-plus" aria-hidden="true"></i>',
				hasForm: true,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );

					// Class applier for drop cap element.
					this.dropCapClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_dropcap'
						},
						normalize: true
					} );

					// Class applier for popover element.
					this.popoverClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_popover'
						},
						normalize: true
					} );

					// Class applier for highlight element.
					this.highlightClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_highlight'
						},
						normalize: true
					} );

					// Class applier for tooltip element.
					this.tooltipClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_tooltip'
						},
						normalize: true
					} );

					// Class applier for one page text link element.
					this.onepageClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_one_page_text_link'
						},
						normalize: true
					} );

					// Class applier for modal text link element.
					this.modalLinkClassApplier = rangy.createClassApplier( 'fusion-inline-shortcode', {
						elementTagName: 'span',
						elementAttributes: {
							'data-inline-shortcode': 'true',
							'data-element': 'fusion_modal_text_link'
						},
						normalize: true
					} );
				},

				// Called when the button the toolbar is clicked
				// Overrides ButtonExtension.handleClick
				handleClick: function( event ) {

					event.preventDefault();
					event.stopPropagation();

					if ( this.isDisplayed() ) {
						this.hideForm();
					} else {
						this.showForm();
					}

					return false;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var form = this.getForm();

					form.classList.add( 'hidden' );
					form.classList.remove( 'visible' );
					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );
					this.setToolbarPosition();
				},

				showForm: function() {
					var form    = this.getForm();

					this.base.saveSelection();

					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					this.setToolbarPosition();

				},

				// Called by core when tearing down medium-editor (destroy)
				destroy: function() {
					if ( ! this.form ) {
						return false;
					}

					if ( this.form.parentNode ) {
						this.form.parentNode.removeChild( this.form );
					}

					delete this.form;
				},

				// Form creation and event handling
				createForm: function() {
					var doc           = this.document,
						form          = doc.createElement( 'div' ),
						ul            = doc.createElement( 'ul' ),
						dropcap       = doc.createElement( 'button' ),
						highlight     = doc.createElement( 'button' ),
						popover       = doc.createElement( 'button' ),
						tooltip       = doc.createElement( 'button' ),
						onepage       = doc.createElement( 'button' ),
						modalLink     = doc.createElement( 'button' ),
						li            = doc.createElement( 'li' ),
						icon          = doc.createElement( 'i' ),
						tooltipText   = false,
						onepageText   = false,
						popoverText   = false,
						highlightText = false,
						dropcapText   = false,
						modalLinkText = false;

					if ( 'undefined' !== typeof fusionAllElements.fusion_tooltip ) {
						tooltipText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_tooltip.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_one_page_text_link ) {
						onepageText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_one_page_text_link.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_popover ) {
						popoverText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_popover.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_highlight ) {
						highlightText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_highlight.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_dropcap ) {
						dropcapText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_dropcap.name );
					}
					if ( 'undefined' !== typeof fusionAllElements.fusion_modal_text_link ) {
						modalLinkText = fusionBuilderText.add_unknown.replace( '%s', fusionAllElements.fusion_modal_text_link.name );
					}

					this.base.saveSelection();

					// Font Name Form (div)
					form.className = 'medium-editor-toolbar-form medium-editor-dropdown-toolbar';
					form.id        = 'medium-editor-toolbar-form-shortcode-' + this.getEditorId();
					ul.className   = 'fusion-shortcode-form';

					li.innerHTML = 'Inline Elements';
					ul.appendChild( li );

					// Dropcap element.
					if ( dropcapText ) {
						li                = doc.createElement( 'li' );
						icon.className    = 'fusiona-font';
						dropcap.className = 'fusion-dropcap-add';
						dropcap.setAttribute( 'data-element', 'fusion_dropcap' );
						dropcap.setAttribute( 'title', dropcapText );
						dropcap.setAttribute( 'aria-label', dropcapText );
						dropcap.appendChild( icon );
						dropcap.innerHTML += fusionAllElements.fusion_dropcap.name;
						li.appendChild( dropcap );
						ul.appendChild( li );
						this.on( dropcap, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Highlight element.
					if ( highlightText ) {
						li                  = doc.createElement( 'li' );
						icon                = doc.createElement( 'i' );
						icon.className      = 'fusiona-H';
						highlight.className = 'fusion-highlight-add';
						highlight.setAttribute( 'data-element', 'fusion_highlight' );
						highlight.setAttribute( 'title', highlightText );
						highlight.setAttribute( 'aria-label', highlightText );
						highlight.appendChild( icon );
						highlight.innerHTML += fusionAllElements.fusion_highlight.name;
						li.appendChild( highlight );
						ul.appendChild( li );
						this.on( highlight, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Popover element.
					if ( popoverText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-uniF61C';
						popover.className = 'fusion-popover-add';
						popover.setAttribute( 'data-element', 'fusion_popover' );
						popover.setAttribute( 'title', popoverText );
						popover.setAttribute( 'aria-label', popoverText );
						popover.appendChild( icon );
						popover.innerHTML += fusionAllElements.fusion_popover.name;
						li.appendChild( popover );
						ul.appendChild( li );
						this.on( popover, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Tooltip element.
					if ( tooltipText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-exclamation-sign';
						tooltip.className = 'fusion-tooltip-add';
						tooltip.setAttribute( 'data-element', 'fusion_tooltip' );
						tooltip.setAttribute( 'title', tooltipText );
						tooltip.setAttribute( 'aria-label', tooltipText );
						tooltip.appendChild( icon );
						tooltip.innerHTML += fusionAllElements.fusion_tooltip.name;
						li.appendChild( tooltip );
						ul.appendChild( li );
						this.on( tooltip, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// One Page Text Link element.
					if ( onepageText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-external-link';
						onepage.className = 'fusion-onepage-add';
						onepage.setAttribute( 'data-element', 'fusion_one_page_text_link' );
						onepage.setAttribute( 'title', onepageText );
						onepage.setAttribute( 'aria-label', onepageText );
						onepage.appendChild( icon );
						onepage.innerHTML += fusionAllElements.fusion_one_page_text_link.name;
						li.appendChild( onepage );
						ul.appendChild( li );
						this.on( onepage, 'click', this.addShortcodeElement.bind( this ), true );
					}

					// Modal Text Link element.
					if ( modalLinkText ) {
						li                = doc.createElement( 'li' );
						icon              = doc.createElement( 'i' );
						icon.className    = 'fusiona-external-link';
						modalLink.className = 'fusion-modallink-add';
						modalLink.setAttribute( 'data-element', 'fusion_modal_text_link' );
						modalLink.setAttribute( 'title', modalLinkText );
						modalLink.setAttribute( 'aria-label', modalLinkText );
						modalLink.appendChild( icon );
						modalLink.innerHTML += fusionAllElements.fusion_modal_text_link.name;
						li.appendChild( modalLink );
						ul.appendChild( li );
						this.on( modalLink, 'click', this.addShortcodeElement.bind( this ), true );
					}

					form.appendChild( ul );

					this.on( form, 'click', this.handleFormClick.bind( this ) );

					return form;
				},

				addShortcodeElement: function( element ) {
					var iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						label     = element.currentTarget.getAttribute( 'data-element' );

					this.base.restoreSelection();

					switch ( label ) {
					case 'fusion_dropcap':
						this.dropCapClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_highlight':
						this.highlightClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_popover':
						this.popoverClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_tooltip':
						this.tooltipClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_one_page_text_link':
						this.onepageClassApplier.applyToSelection( iframeWin );
						break;

					case 'fusion_modal_text_link':
						this.modalLinkClassApplier.applyToSelection( iframeWin );
						break;
					}
					this.doFormSave( label );
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				doFormSave: function( label ) {
					var name = '';
					if ( 'undefined' !== typeof label && 'undefined' !== typeof fusionAllElements[ label ].name ) {
						name = fusionAllElements[ label ].name;
					}

					// Make sure editableInput is triggered.
					this.base.trigger( 'editableInput', {}, MediumEditor.selection.getSelectionElement( this.document ) );

					// If auto open is on, pause history.  It will be resumed on element settings close.
					if ( 'undefined' === typeof FusionApp || 'off' === FusionApp.preferencesData.open_settings ) {
						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added + ' ' + name + ' ' + fusionBuilderText.element );
					} else if ( 'undefined' !== typeof FusionPageBuilderApp ) {
						FusionPageBuilderApp.inlineEditors.shortcodeAdded = true;
					}

					this.base.checkSelection();
					this.hideForm();
				}
			} );

			MediumEditor.extensions.fusionInlineShortcode = FusionInlineShortcodeForm;
		},

		/**
		 * Creates the font-color extension for MediumEditor and adds the form.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createAnchor: function( event ) { // jshint ignore: line
			var FusionAnchorForm = MediumEditor.extensions.form.extend( {
				name: 'fusionAnchor',
				action: 'createLink',
				aria: fusionBuilderText.link_options,
				contentDefault: '<b>#</b>;',
				contentFA: '<i class="fusiona-link-solid" aria-hidden="true"></i>',
				hasForm: true,
				tagNames: [ 'a' ],

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );

					this._handleInputChange = _.debounce( _.bind( this.handleInputChange, this ), 500 );
					this._keyUpInputChange  = _.debounce( _.bind( this.keyUpInputChange, this ), 1500 );
				},

				handleClick: function( event ) {
					var nodes,
						font;

					event.preventDefault();
					event.stopPropagation();

					if ( ! this.isDisplayed() ) {
						this.showForm();
					}

					return false;
				},

				// Get font size which is set.
				getExistingValues: function() {
					var values = {
							href: '',
							target: ''
						},
						range = MediumEditor.selection.getSelectionRange( this.document ),
						el    = false;

					if ( 'a' === range.startContainer.nodeName.toLowerCase() ) {
						el = range.startContainer;
					} else if ( 'a' === range.endContainer.nodeName.toLowerCase() ) {
						el = range.endContainer;
					} else if ( MediumEditor.util.getClosestTag( MediumEditor.selection.getSelectedParentElement( range ), 'a' ) ) {
						el = MediumEditor.util.getClosestTag( MediumEditor.selection.getSelectedParentElement( range ), 'a' );
					}

					if ( el ) {
						values.href = el.getAttribute( 'href' );
						values.target = el.getAttribute( 'target' );
					}

					this.href = values.href;

					return values;
				},

				// Called by medium-editor to append form to the toolbar
				getForm: function() {
					if ( ! this.form ) {
						this.form = this.createForm();
					}
					return this.form;
				},

				// Used by medium-editor when the default toolbar is to be displayed
				isDisplayed: function() {
					return this.getForm().classList.contains( 'visible' );
				},

				hideForm: function() {
					var self         = this,
						form         = this.getForm(),
						toolbar      = this.base.getExtensionByName( 'toolbar' ),
						timeoutValue = 50;

					if ( toolbar.toolbar.classList.contains( 'medium-toolbar-arrow-over' ) ) {
						timeoutValue = 300;
					}

					form.classList.add( 'hidden' );
					form.classList.remove( 'visible' );

					setTimeout( function() {
						form.classList.remove( 'hidden' );
					}, 400 );

					this.getHrefInput().value     = '';
					this.getTargetInput().value   = '';
					this.getTargetInput().checked = false;

					setTimeout( function() {
						self.setToolbarPosition();
						self.base.checkSelection();
					}, timeoutValue );
				},

				getHrefInput: function() {
					return this.getForm().querySelector( '#fusion-anchor-href' );
				},

				getTargetInput: function() {
					return this.getForm().querySelector( '.switch-input' );
				},

				showForm: function( fontColor ) {
					var self  = this,
						form  = this.getForm();

					this.base.saveSelection();
					this.hideToolbarDefaultActions();

					form.classList.add( 'visible' );
					form.classList.remove( 'hidden' );

					this.setExistingValues();

					this.setToolbarPosition();

				},

				doFormSave: function() {

					this.hideForm();
				},

				setExistingValues: function() {
					var self   = this,
						values = this.getExistingValues();

					this.getHrefInput().value = values.href;
					this.getTargetInput().value = values.target;

					if ( '_blank' === values.target ) {
						this.getTargetInput().checked = true;
					}

					this.setClearVisibility();
				},

				setClearVisibility: function() {
					var form = this.getForm();

					if ( this.href && '' !== this.href ) {
						form.classList.add( 'has-link' );
					} else {
						form.classList.remove( 'has-link' );
					}
				},

				// Form creation and event handling
				createForm: function() {
					var self        = this,
						doc         = this.document,
						form        = doc.createElement( 'div' ),
						input       = doc.createElement( 'input' ),
						linkSearch  = doc.createElement( 'span' ),
						linkClear   = doc.createElement( 'button' ),
						close       = doc.createElement( 'button' ),
						label       = doc.createElement( 'label' ),
						targetHold  = doc.createElement( 'div' ),
						targetLabel = doc.createElement( 'label' ),
						targetInput = doc.createElement( 'input' ),
						labelSpan   = doc.createElement( 'span' ),
						handleSpan  = doc.createElement( 'span' ),
						helperOn    = doc.createElement( 'span' ),
						helperOff   = doc.createElement( 'span' );

					// Font Color Form (div)
					form.className = 'medium-editor-toolbar-form fusion-inline-anchor fusion-link-selector';
					form.id        = 'medium-editor-toolbar-form-anchor-' + this.getEditorId();

					input.className   = 'medium-editor-toolbar-input fusion-builder-link-field';
					input.id          = 'fusion-anchor-href';
					input.type        = 'text';
					input.placeholder = fusionBuilderText.select_link;
					form.appendChild( input );

					linkSearch.className = 'fusion-inline-anchor-search button-link-selector fusion-builder-link-button fusiona-search';
					form.appendChild( linkSearch );

					linkClear.className = 'button button-small wp-picker-clear';
					linkClear.innerHTML = '<i class="fusiona-eraser-solid" aria-hidden="true"></i>';
					form.appendChild( linkClear );

					label.className = 'switch';
					label.setAttribute( 'for', 'fusion-anchor-target-' + this.getEditorId() );

					targetHold.className = 'fusion-inline-target';

					targetLabel.innerHTML = fusionBuilderText.open_in_new_tab;

					targetHold.appendChild( targetLabel );

					targetInput.className = 'switch-input screen-reader-text';
					targetInput.name      = 'fusion-anchor-target';
					targetInput.id        = 'fusion-anchor-target-' + this.getEditorId();
					targetInput.type      = 'checkbox';
					targetInput.value     = '0';

					labelSpan.className = 'switch-label';
					labelSpan.setAttribute( 'data-on', fusionBuilderText.on );
					labelSpan.setAttribute( 'data-off', fusionBuilderText.off );

					handleSpan.className = 'switch-handle';

					helperOn.className = 'label-helper-calc-on fusion-anchor-target';
					helperOn.innerHTML = fusionBuilderText.on;

					helperOff.className = 'label-helper-calc-off fusion-anchor-target';
					helperOff.innerHTML = fusionBuilderText.off;

					label.appendChild( targetInput );
					label.appendChild( labelSpan );
					label.appendChild( handleSpan );
					label.appendChild( helperOn );
					label.appendChild( helperOff );

					targetHold.appendChild( label );

					form.appendChild( targetHold );

					close.className = 'fusion-inline-editor-close';
					close.innerHTML = '<i class="fusiona-check" aria-hidden="true"></i>';
					form.appendChild( close );

					this.on( input, 'change', this.handleInputChange.bind( this ), true );
					this.on( input, 'blur', this.handleInputChange.bind( this ), true );
					this.on( input, 'keyup', this._keyUpInputChange.bind( this ), true );
					this.on( targetInput, 'change', this._handleInputChange.bind( this ), true );

					// Handle save button clicks (capture)
					this.on( close, 'click', this.handleSaveClick.bind( this ), true );

					this.on( form, 'click', this.handleFormClick.bind( this ) );

					this.on( linkClear, 'click', this.clearLink.bind( this ) );

					setTimeout( function() {
						self.optionSwitch( jQuery( form ) );
						self.optionLinkSelector( jQuery( form ).parent() );
					}, 300 );

					return form;
				},

				clearLink: function( event ) {
					var anchor = this.getHrefInput();

					event.preventDefault();

					anchor.value = '';
					anchor.dispatchEvent( new Event( 'change' ) );
				},

				getFormOpts: function() {
					var targetCheckbox = this.getTargetInput(),
						hrefInput      = this.getHrefInput(),
						opts = {
							value: hrefInput.value.trim(),
							target: '_self',
							skipCheck: true
						};

					this.href = hrefInput.value.trim();

					if ( targetCheckbox && targetCheckbox.checked ) {
						opts.target = '_blank';
					}

					return opts;
				},

				keyUpInputChange: function( event ) {
					this.handleInputChange( event );

					// Return the focus back to the input.
					jQuery( this.getForm() ).find( '.fusion-builder-link-field' ).focus();
				},

				handleInputChange: function( event ) {
					var opts = this.getFormOpts();

					// If form is hidden, do not try to save again.
					if ( ! jQuery( this.getForm() ).hasClass( 'visible' ) ) {
						return;
					}

					this.base.restoreSelection();

					if ( '' === opts.value ) {
						this.execAction( 'unlink', opts );
					} else {
						this.execAction( this.action, opts );
					}

					this.setClearVisibility();
				},

				handleFormClick: function( event ) {

					// Make sure not to hide form when clicking inside the form
					event.stopPropagation();
				},

				handleSaveClick: function( event ) {
					this.handleInputChange( event );

					// Clicking Save -> create the font size
					event.preventDefault();

					this.doFormSave();
				}
			} );

			_.extend( FusionAnchorForm.prototype, FusionPageBuilder.options.fusionSwitchField );
			_.extend( FusionAnchorForm.prototype, FusionPageBuilder.options.fusionLinkSelector );

			MediumEditor.extensions.fusionAnchor = FusionAnchorForm;
		},

		/**
		 * Creates customized version of remove format.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		createRemove: function( event ) { // jshint ignore: line
			var FusionRemoveForm = MediumEditor.extensions.form.extend( {
				name: 'fusionRemoveFormat',
				action: 'fusionRemoveFormat',
				aria: fusionBuilderText.remove_format,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-undo" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
				},

				handleClick: function( event ) {
					var nodes          = MediumEditor.selection.getSelectedElements( this.document ),
						selectionRange = MediumEditor.selection.getSelectionRange( this.document ),
						parentEl       = MediumEditor.selection.getSelectedParentElement( selectionRange );

					event.preventDefault();
					event.stopPropagation();

					// Check for parent el first.
					if ( ! nodes.length && parentEl ) {
						nodes = [ parentEl ];
					}

					nodes.forEach( function( el ) {
						el.removeAttribute( 'data-fusion-font' );
						el.removeAttribute( 'data-fusion-google-font' );
						el.removeAttribute( 'data-fusion-google-variant' );
						el.style[ 'line-height' ]    = '';
						el.style[ 'font-size' ]      = '';
						el.style[ 'font-family' ]    = '';
						el.style[ 'letter-spacing' ] = '';

						if ( '' === el.getAttribute( 'style' ) ) {
							el.removeAttribute( 'style' );
						}
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
					} );

					this.execAction( 'removeFormat', { skipCheck: true } );

					this.base.checkSelection();

					return false;
				}
			} );

			MediumEditor.extensions.fusionRemoveFormat = FusionRemoveForm;
		},

		/*
		 * Creates customized version of remove format.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @returns {void}
		 */
		createIndent: function( event ) { // jshint ignore: line
			var fusionIndent = MediumEditor.extensions.form.extend( {
				name: 'fusionIndent',
				action: 'fusionIndent',
				aria: fusionBuilderText.indent,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-indent" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'p',
						tagNames: [ 'blockquote', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );
				},

				handleClick: function( event ) {
					var element   = MediumEditor.selection.getSelectionElement( this.document ),
						iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						paddingLeft;

					event.preventDefault();
					event.stopPropagation();

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
						paddingLeft = ( Math.round( parseInt( jQuery( el ).css( 'padding-left' ) ) / 40 ) * 40 ) + 40;
						jQuery( el ).css( { 'padding-left': paddingLeft } );
					} );

					this.base.saveSelection();

					this.base.trigger( 'editableInput', {}, element );

					this.base.checkSelection();

					return false;
				}
			} );

			MediumEditor.extensions.fusionIndent = fusionIndent;
		},

		/*
		 * Creates customized version of remove format.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event.
		 * @returns {void}
		 */
		createOutdent: function( event ) { // jshint ignore: line
			var fusionOutdent = MediumEditor.extensions.form.extend( {
				name: 'fusionOutdent',
				action: 'fusionOutdent',
				aria: fusionBuilderText.outdent,
				contentDefault: '&#xB1;',
				contentFA: '<i class="fusiona-outdent" aria-hidden="true"></i>',
				hasForm: false,

				init: function() {
					MediumEditor.extensions.form.prototype.init.apply( this, arguments );
					this.classApplier = rangy.createClassApplier( 'fusion-editing', {
						elementTagName: 'p',
						tagNames: [ 'blockquote', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ],
						normalize: true
					} );
				},

				handleClick: function( event ) {
					var element   = MediumEditor.selection.getSelectionElement( this.document ),
						iframe    = document.getElementById( 'fb-preview' ),
						iframeWin = rangy.dom.getIframeWindow( iframe ),
						paddingLeft;

					event.preventDefault();
					event.stopPropagation();

					this.classApplier.applyToSelection( iframeWin );

					element.querySelectorAll( '.fusion-editing' ).forEach( function( el ) {
						el.classList.remove( 'fusion-editing' );
						if ( 0 === el.classList.length ) {
							el.removeAttribute( 'class' );
						}
						paddingLeft = ( Math.round( parseInt( jQuery( el ).css( 'padding-left' ) ) / 40 ) * 40 ) - 40;
						jQuery( el ).css( { 'padding-left': paddingLeft } );
					} );

					this.base.saveSelection();

					this.base.trigger( 'editableInput', {}, element );

					this.base.checkSelection();

					return false;
				}
			} );

			MediumEditor.extensions.fusionOutdent = fusionOutdent;
		}

	} );
}( jQuery ) );
;/* global MediumEditor, FusionPageBuilderApp, fusionAllElements, FusionEvents, fusionGlobalManager, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.InlineEditorManager = Backbone.Model.extend( {
		defaults: {
			editorCount: 0,
			editors: {}
		},

		/**
		 * Init.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {

			// Define different toolbar button combinations.
			this.buttons = {
				simple: [
					'bold',
					'italic',
					'underline',
					'strikethrough',
					'fusionRemoveFormat'
				],
				full: [
					'fusionTypography',
					'fusionFontColor',
					'bold',
					'italic',
					'underline',
					'fusionAnchor',
					'fusionAlign',
					'strikethrough',
					'quote',
					'unorderedlist',
					'orderedlist',
					'fusionIndent',
					'fusionOutdent',
					'fusionRemoveFormat',
					'fusionExtended'
				]
			};

			// Used as a flag for auto opening settings.
			this.shortcodeAdded  = false;
			this._logChangeEvent = _.debounce( _.bind( this.logChangeEvent, this ), 500 );
		},

		addEditorInstance: function( liveElement, view, autoSelect ) {
			var self           = this,
				editors        = self.get( 'editors' ),
				editorCount    = self.get( 'editorCount' ),
				iframe         = jQuery( '#fb-preview' )[ 0 ],
				params         = view.model.get( 'params' ),
				cid            = view.model.get( 'cid' ),
				toolbar        = 'undefined' !== typeof liveElement.data( 'toolbar' ) ? liveElement.data( 'toolbar' ) : 'full',
				inlineSC       = 'undefined' !== typeof fusionAllElements[ view.model.get( 'element_type' ) ] && 'undefined' !== typeof fusionAllElements[ view.model.get( 'element_type' ) ].inline_editor_shortcodes ? fusionAllElements[ view.model.get( 'element_type' ) ].inline_editor_shortcodes : true,
				toolbars       = jQuery.extend( true, {}, this.buttons ),
				buttons        = 'undefined' !== typeof toolbars[ toolbar ] ? toolbars[ toolbar ] : toolbars.full,
				disableEditing = false,
				viewEditors;

			autoSelect = autoSelect || false;

			if ( inlineSC && ( 'full' === toolbar || true === toolbar ) ) {
				buttons.push( 'fusionInlineShortcode' );
			}

			if ( false !== toolbar ) {
				toolbar = {
					buttons: buttons
				};
			}

			if ( liveElement.attr( 'data-dynamic-content-overriding' ) ) {
				disableEditing = true;
				toolbar        = false;
			}

			editorCount++;

			editors[ editorCount ] = new MediumEditor( liveElement, {
				anchorPreview: false,
				buttonLabels: 'fontawesome',
				extensions: {
					fusionTypography: new MediumEditor.extensions.fusionTypography(),
					fusionFontColor: new MediumEditor.extensions.fusionFontColor(),
					fusionExtended: new MediumEditor.extensions.fusionExtended(),
					fusionInlineShortcode: new MediumEditor.extensions.fusionInlineShortcode(),
					fusionAlign: new MediumEditor.extensions.fusionAlign(),
					fusionAnchor: new MediumEditor.extensions.fusionAnchor(),
					fusionRemoveFormat: new MediumEditor.extensions.fusionRemoveFormat(),
					fusionIndent: new MediumEditor.extensions.fusionIndent(),
					fusionOutdent: new MediumEditor.extensions.fusionOutdent(),
					imageDragging: {}
				},
				placeholder: {
					text: 'Your Content Goes Here'
				},
				contentWindow: iframe.contentWindow,
				ownerDocument: iframe.contentWindow.document,
				elementsContainer: iframe.contentWindow.document.body,
				toolbar: toolbar,
				disableEditing: disableEditing
			} );

			editors[ editorCount ].subscribe( 'blur', function() {

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-live-editor-updated' );

				if ( 'undefined' !== typeof FusionPageBuilderApp && ! FusionPageBuilderApp.$el.hasClass( 'fusion-dialog-ui-active' ) ) {
					FusionPageBuilderApp.$el.removeClass( 'fusion-builder-no-ui' );
				}
			} );

			editors[ editorCount ].subscribe( 'editableInput', function( event, editable ) {
				var content       = self.getEditor( editorCount ).getContent(),
					param         = jQuery( editable ).data( 'param' ),
					encoding      = 'undefined' !== typeof jQuery( editable ).attr( 'data-encoding' ) ? jQuery( editable ).attr( 'data-encoding' ) : false,
					newShortcodes = content.indexOf( 'data-inline-shortcode' ),
					initialVal    = params[ param ];

				// Fix for inline font family style.
				content = content.replace( /&quot;/g, '\'' ).replace( /&nbsp;/g, ' ' );

				// Adds in any inline shortcodes.
				content = FusionPageBuilderApp.htmlToShortcode( content, cid );

				// If encoded param, need to encode before saving.
				if ( encoding ) {
					content = FusionPageBuilderApp.base64Encode( content );
				}

				params[ param ] = content;

				// Unset added so that change is shown in element settings.
				view.model.unset( 'added' );

				// Update params.
				view.model.set( 'params', params );

				// Used to make sure parent of child is updated.
				if ( 'function' === typeof view.forceUpdateParent ) {
					view.forceUpdateParent();
				}

				FusionEvents.trigger( 'fusion-inline-edited' );

				// If new shortcodes were found trigger re-render.
				if ( -1 !== newShortcodes ) {
					view.render();
				}

				if ( ! self.initialValue ) {
					self.initialValue = initialVal;
				}
				self._logChangeEvent( param, content, view );
			} );

			// Hide UI when editor is active and hovered.
			if ( 'undefined' !== typeof FusionPageBuilderApp ) {
				this.uiHideListener( liveElement );
				editors[ editorCount ].subscribe( 'focus', function() {
					FusionPageBuilderApp.$el.addClass( 'fusion-builder-no-ui' );
				} );
			}

			// If auto select is set, select all contents.
			if ( autoSelect ) {
				editors[ editorCount ].selectElement( liveElement[ 0 ] );
			}

			// Update view record of IDs.
			viewEditors = view.model.get( 'inlineEditors' );
			viewEditors.push( editorCount );
			view.model.set( 'inlineEditors', viewEditors );

			this.set( { editorCount: editorCount } );
			this.set( { editors: editors } );
		},

		logChangeEvent: function( param, value, view ) {
			var state = {
					type: 'param',
					param: param,
					newValue: value,
					cid: view.model.get( 'cid' )
				},
				elementMap = fusionAllElements[ view.model.get( 'element_type' ) ],
				paramTitle = 'object' === typeof elementMap.params[ param ] ? elementMap.params[ param ].heading : param;

			FusionEvents.trigger( 'fusion-param-changed-' + view.model.get( 'cid' ), param, value );

			// TODO: Needs checked for chart data, param is not accurate.
			state.oldValue    = this.initialValue;
			this.initialValue = false;

			// Handle multiple global elements for save.
			fusionGlobalManager.handleMultiGlobal( {
				currentModel: view.model,
				handleType: 'save',
				attributes: view.model.attributes
			} );

			FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.edited + ' ' + elementMap.name + ' - ' + paramTitle, state );
		},

		uiHideListener: function( liveElement ) {
			liveElement.hover(
				function() {
					if ( jQuery( this ).attr( 'data-medium-focused' ) ) {
						FusionPageBuilderApp.$el.addClass( 'fusion-builder-no-ui' );
					} else if ( ! FusionPageBuilderApp.$el.hasClass( 'fusion-dialog-ui-active' ) ) {
						FusionPageBuilderApp.$el.removeClass( 'fusion-builder-no-ui' );
					}
				}, function() {
					if ( ! FusionPageBuilderApp.$el.hasClass( 'fusion-dialog-ui-active' ) ) {
						FusionPageBuilderApp.$el.removeClass( 'fusion-builder-no-ui' );
					}
				}
			);
		},

		getEditor: function( id ) {
			var editors = this.get( 'editors' );
			return editors[ id ];
		},

		reInitEditor: function( id, element ) {
			var editors = this.get( 'editors' ),
				editor;

			if ( 'undefined' !== typeof editors[ id ] ) {
				editor = editors[ id ];
				editor.addElements( [ element ] );
				editor.setup();
				editor.selectElement( element );
			}
		},

		destroyEditor: function( id ) {
			var editors = this.get( 'editors' );
			if ( 'undefined' !== typeof editors[ id ] ) {
				editors[ id ].destroy();
				delete editors[ id ];
			}
			this.set( { editors: editors } );
		}

	} );

}( jQuery ) );
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionCheckboxButtonSet = {
	optionCheckboxButtonSet: function( $element ) {
		var $checkboxbuttonset,
			$visibility,
			$choice,
			$checkboxsetcontainer;

		$element = $element || this.$el;

		$checkboxbuttonset = $element.find( '.fusion-form-checkbox-button-set' );

		if ( $checkboxbuttonset.length ) {

			// For the visibility option check if choice is no or yes then convert to new style
			$visibility = $element.find( '.fusion-form-checkbox-button-set.hide_on_mobile' );
			if ( $visibility.length ) {
				$choice = $visibility.find( '.button-set-value' ).val();
				if ( 'no' === $choice || '' === $choice ) {
					$visibility.find( 'a' ).addClass( 'ui-state-active' );
				}
				if ( 'yes' === $choice ) {
					$visibility.find( 'a:not([data-value="small-visibility"])' ).addClass( 'ui-state-active' );
				}
			}

			$checkboxbuttonset.find( 'a' ).on( 'click', function( e ) {
				e.preventDefault();
				$checkboxsetcontainer = jQuery( this ).closest( '.fusion-form-checkbox-button-set' );
				jQuery( this ).toggleClass( 'ui-state-active' );
				$checkboxsetcontainer.find( '.button-set-value' ).val( $checkboxsetcontainer.find( '.ui-state-active' ).map( function( _, el ) {
					return jQuery( el ).data( 'value' );
				} ).get() ).trigger( 'change' );
			} );
		}
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionCodeBlock = {
	optionCodeBlock: function( $element ) {
		var self = this,
			$codeBlock,
			codeBlockId,
			codeElement,
			codeBlockLang,
			codeMirrorJSON;

		$element   = $element || this.$el;
		$codeBlock = $element.find( '.fusion-builder-code-block' );

		self.codeEditorOption = {};

		if ( $codeBlock.length ) {

			$codeBlock.each( function( index ) {
				codeBlockId   = jQuery( this ).attr( 'id' );
				codeElement   = $element.find( '#' + codeBlockId );
				codeBlockLang = jQuery( this ).data( 'language' );

				// Get wp.CodeMirror object json.
				codeMirrorJSON = $element.find( '.' + codeBlockId ).val();
				if ( 'undefined' !== typeof codeMirrorJSON ) {
					codeMirrorJSON = JSON.parse( codeMirrorJSON );
					codeMirrorJSON.lineNumbers = true;
					codeMirrorJSON.lineWrapping = true;
				}
				if ( 'undefined' !== typeof codeBlockLang && 'default' !== codeBlockLang ) {
					codeMirrorJSON.mode = 'text/' + codeBlockLang;
				}

				// Set index so it can be referenced.
				jQuery( this ).closest( ' .fusion-builder-option' ).attr( 'data-index', index );

				self.codeEditorOption[ index ] = wp.CodeMirror.fromTextArea( codeElement[ 0 ], codeMirrorJSON );
				self.codeEditorOption[ index ].on( 'renderLine', function( cm, line, elt ) {
					var off = wp.CodeMirror.countColumn( line.text, null, cm.getOption( 'tabSize' ) ) * self.codeEditorOption[ index ].defaultCharWidth();
					elt.style.textIndent = '-' + off + 'px';
					elt.style.paddingLeft = ( 4 + off ) + 'px';
				} );
				self.codeEditorOption[ index ].refresh();

				// Refresh editor after initialization
				setTimeout( function() {
					self.codeEditorOption[ index ].refresh();
					self.codeEditorOption[ index ].focus();
				}, 100 );

			} );
		}
	}
};
;/* globals awbPalette */
var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColorPalette = {

	optionColorPalette: function( $element ) {
		var $palettes;

		$element  = $element || this.$el;
		$palettes = $element.find( '.fusion-color-palette-options' );

		$palettes.each( function() {
			FusionPageBuilder.options.fusionColorPalette.initColorsPalette( jQuery( this ) );
		} );
	},

	initColorsPalette: function ( $paletteContainer ) {
		var paletteSaveInput;

		$paletteContainer = jQuery( $paletteContainer );
		paletteSaveInput = $paletteContainer.find( '.awb-palette-save' );

		if ( ! $paletteContainer.is( '.fusion-color-palette-options' ) ) {
			return;
		}

		if ( $paletteContainer.hasClass( 'palette-init' ) ) {
			return;
		}
		$paletteContainer.addClass( 'palette-init' );

		initializePickers();

		// Toggle open and close color.
		$paletteContainer.on( 'click', '.preview, .fusiona-pen', handleToggleColor );

		// Listen for removal of color.
		$paletteContainer.on( 'click', '.fusiona-trash-o', handleTrashIconClick );

		// Listen for the add color button.
		$paletteContainer.on( 'click', '.awb-color-palette-add-btn', handleAddColorBtnClick );

		// Bind input changes to toggle label.
		$paletteContainer.on( 'change keyup', '.color-name', handleColorNameChange );

		// Initialize all pickers.
		function initializePickers() {
			$paletteContainer.find( '.awb-picker' ).each( function() {
				initializePicker( this );
			} );
		}

		// Initialize a specific picker.
		function initializePicker( picker ) {
			var handleColorChange = _.debounce( _handleColorChange, 150 );
			jQuery( picker ).awbColorPicker( {
				change: handleColorChange
			} );
		}

		// Update the palette object when a color changes.
		function _handleColorChange( event, ui ) {
			var $target = jQuery( event.target ),
				value   = $target.val(),
				slug    = $target.closest( '.fusion-color-palette-item' ).attr( 'data-slug' );

			if ( 'object' === typeof ui ) {
				value = ui.color.toString();
			}

			addOrUpdateOptionColor( slug, { color: value } );
		}

		// Show or hide the controls to change a color.
		function handleToggleColor() {
			var picker,
				pickerInstance;

			jQuery( this ).closest( '.fusion-color-palette-item' ).find( '.awb-palette-content' ).slideToggle( 'fast' );

			// Annoying Iris visual bug, make sure that the initial draggable button is within the parent.
			picker = jQuery( this ).closest( '.fusion-color-palette-item' ).find( 'input.awb-color-picker' );
			pickerInstance = picker.awbColorPicker( 'instance' );
			if ( 'object' === typeof pickerInstance && 'function' === typeof pickerInstance.fixIrisDragButtonOutsideDragArea ) {
				pickerInstance.fixIrisDragButtonOutsideDragArea();
			}
		}

		function handleColorNameChange() {
			var paletteItem = jQuery( this ).closest( '.fusion-color-palette-item' ),
				label    = jQuery( this ).val(),
				oldLabel,
				slug     = paletteItem.attr( 'data-slug' ),
				object;

			// Change the title to the new label.
			paletteItem.find( '.label' ).text( label );

			// The checks needed because change event can trigger without any value changes.
			// As it triggers both on "keyup" and "change".
			object = getPaletteSaveObject();
			oldLabel = object[ slug ] ? object[ slug ].label : null;
			if ( oldLabel !== label && object[ slug ] ) {
				addOrUpdateOptionColor( slug, { label: label } );
			}
		}

		// When a trash icon is clicked, remove the color from the palette.
		function handleTrashIconClick() {
			var paletteItem = jQuery( this ).closest( '.fusion-color-palette-item' ),
				slug = paletteItem.attr( 'data-slug' ),
				resultConfirm;

			resultConfirm = window.confirm( window.awbPalette.removeColorAlert ); // eslint-disable-line no-alert

			if ( ! resultConfirm ) {
				return;
			}

			paletteItem.find( '.awb-palette-content' ).slideUp( 'fast' );
			paletteItem.slideUp( 'fast', function() {
				jQuery( this ).remove();
			} );

			removeOptionColor( slug );
		}

		function handleAddColorBtnClick( event ) {
			var paletteList = $paletteContainer.find( '.awb-color-palette-list' ),
				newItem     = jQuery( $paletteContainer.find( '.awb-color-palette-color-template' ).html().trim().replaceAll( /(^<!--|-->$)/g, '' ) ),
				newSlug,
				newPaletteColorObj;

			event.preventDefault();

			paletteList.append( newItem );

			newSlug = generateSlug( newItem );
			changeSlugInHTML( newItem, newSlug );

			// Initialize global colors with the new color.
			newPaletteColorObj = {
				color: newItem.find( '.awb-picker' ).val(),
				label: newItem.find( '.color-name' ).val()
			};
			addOrUpdateOptionColor( newSlug, newPaletteColorObj );

			initializePicker( newItem.find( '.awb-picker' ) );
		}

		// Helper functions

		// Update the color object in both save input global palette.
		function addOrUpdateOptionColor( slug, colorObject ) {
			var object;
			awbPalette.addOrUpdateColor( slug, colorObject );

			object = getPaletteSaveObject();
			object[ slug ] = Object.assign( {}, object[ slug ], colorObject ); // eslint-disable-line
			replacePaletteSaveObject( object );
		}

		// Removes the color object in both save input global palette.
		function removeOptionColor( slug ) {
			var object;
			if ( ! slug ) {
				return;
			}

			awbPalette.removeColor( slug );

			object = getPaletteSaveObject();
			if ( object[ slug ] ) {
				delete object[ slug ];
				replacePaletteSaveObject( object );
			}
		}

		// Get the object of colors from the save input.
		function getPaletteSaveObject() {
			var objectString = paletteSaveInput.val(),
				object;

			try {
				object = JSON.parse( objectString );
				return object;
			} catch ( e ) {
				console.error( e );
				return {};
			}
		}

		// Replace the palette save object with a new one.
		function replacePaletteSaveObject( object ) {
			paletteSaveInput.val( JSON.stringify( object ) ).trigger( 'change' );
		}

		// From a name entered by a user, generate a new unique slug.
		function generateSlug( paletteItem ) {
			var paletteSlugs = [],
				number,
				slugWithoutAppendedNumber,
				slug = 'custom_color_1';

			// Make an array with existing slugs.
			$paletteContainer.find( '.fusion-color-palette-item' ).not( paletteItem ).each( function() {
				var itemSlug = jQuery( this ).attr( 'data-slug' );
				if ( itemSlug ) {
					paletteSlugs.push( itemSlug );
				}
			} );

			// Append a number to the end of the slug, if the slug already exists.
			if ( paletteSlugs.includes( slug ) ) {
				number = 2;
				slugWithoutAppendedNumber = slug.replace( /_(\d+)$/, '' );

				while ( paletteSlugs.includes( slugWithoutAppendedNumber + '_' + number ) ) {
					number++;
				}

				slug = slugWithoutAppendedNumber + '_' + number;
			}

			return slug;
		}

		// Change the slug of a color item, only in HTML.
		function changeSlugInHTML( paletteItem, newSlug ) {
			var oldSlug = paletteItem.attr( 'data-slug' );
			if ( ! oldSlug || newSlug === oldSlug ) {
				return;
			}

			changeOldAttributeSlug( paletteItem, 'data-slug' );
			changeOldAttributeSlug( paletteItem.find( '.awb-picker' ), 'id', 'id' );
			changeOldAttributeSlug( paletteItem.find( '.awb-picker' ), 'name' );
			changeOldAttributeSlug( paletteItem.find( '.color-name' ), 'id', 'id' );
			changeOldAttributeSlug( paletteItem.find( '.color-name' ), 'name' );
			changeOldAttributeSlug( paletteItem.find( '.color-name-label' ), 'for', 'id' );
			changeOldAttributeSlug( paletteItem.find( '.color-code-label' ), 'for', 'id' );

			function changeOldAttributeSlug( input, attribute, replaceType = '' ) {
				var oldAttr = input.attr( attribute ),
					newAttr;

				if ( ! oldAttr ) {
					return;
				}

				if ( 'array' === replaceType ) {
					newAttr = oldAttr.replaceAll( '[' + oldSlug + ']', '[' + newSlug + ']' );
				} else if ( 'id' === replaceType ) {
					newAttr = oldAttr.replaceAll( '-' + oldSlug, '-' + newSlug );
				} else {
					newAttr = oldAttr.replaceAll( oldSlug, newSlug );
				}

				input.attr( attribute, newAttr );
			}
		}

	}
};
;/* global AwbTypographySet */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTypographySetsField = {

	/**
	 * Initialize the typography field.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	optionTypographySets: function( $element ) {
		var $set;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

		$set = $element.find( '.fusion-builder-option.typography-sets' );

		if ( ! $set.length ) {
			return;
		}

		// Init sets.
		if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
			jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
				new AwbTypographySet( $set[ 0 ], this );
			} );
		} else {
			new AwbTypographySet( $set[ 0 ], this );
		}
	}

};
;/* global noUiSlider, wNumb */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColumnWidth = {

	optionColumnWidth: function( $element ) {
		var $columnWidth;
		$columnWidth		= $element.find( '.fusion-form-column-width' );

		$columnWidth.each( function() {
			// Init
			var $colEl 			= jQuery( this ),
				value 			= $colEl.find( '.width-value' ).val(),
				$sliderElement  = $colEl.find( '.custom-width-range-slider' ),
				$rangeSlider,
				sliderOptions,
				fractionToDecimal;

			fractionToDecimal = function( newValue ) {
				var fraction;

				if ( ! newValue.includes( '_' ) ) {
					return '';
				}

				fraction = newValue.split( '_' );
				if ( '' === newValue ) {
					return 0;
				}
				return ( parseFloat( fraction[ 0 ] ) / parseFloat( fraction[ 1 ] ) * 100 ).toFixed( 2 );
			};

			// Init range slider
			sliderOptions = {
				start: [ value ],
				step: 0.01,
				direction: $sliderElement.data( 'direction' ),
				range: {
					min: 0,
					max: 100
				},
				format: wNumb( {
					decimals: 2
				} )
			};
			$rangeSlider = noUiSlider.create( $sliderElement[ 0 ], sliderOptions );

			// Check if it's fraction else initialize custom width.
			if ( ! value || value.includes( '_' ) || 'auto' === value ) {
				$colEl.data( 'active', 'ui-buttons' );
				$colEl.find( '.ui-input, .width-custom' ).hide();
				$colEl.find( '.ui-button[data-value="' + value + '"]' ).addClass( 'ui-state-active' );
				// Update Slider values
				$rangeSlider.set( fractionToDecimal( value ) );
				$colEl.find( '.ui-input input' ).val( fractionToDecimal( value ) );
			} else {
				$colEl.data( 'active', 'ui-input' );
				$colEl.find( '.ui-buttons, .width-default' ).hide();
			}

			// Event listeners
			$rangeSlider.on( 'slide', function( values, handle ) {
				$colEl.find( '.ui-button' ).removeClass( 'ui-state-active' );
				$colEl.find( '.custom-width-input' ).val( values[ handle ] );
				$colEl.find( '.width-value' ).val( values[ handle ] ).trigger( 'change' );
			} );

			$colEl.on( 'click', '.column-width-toggle', function() {
				$colEl.find( '.ui-input, .ui-buttons, a .label' ).toggle();
			} );

			$colEl.on( 'click', '.ui-button', function( event ) {
				var $widthBtn 		= jQuery( this ),
					width			= $widthBtn.data( 'value' );

				if ( jQuery( this ).hasClass( 'default' ) && event ) {
					event.preventDefault();
				}

				// Update Slider values
				$rangeSlider.set( fractionToDecimal( width ) );
				$colEl.find( '.ui-input input' ).val( fractionToDecimal( width ) );

				$colEl.find( '.ui-button' ).removeClass( 'ui-state-active' );
				$widthBtn.addClass( 'ui-state-active' );
				$colEl.find( '.width-value' ).val( width ).trigger( 'change' );
			} );

			$colEl.on( 'change', '.ui-input input', function() {
				var $widthInput = jQuery( this ),
					width		= $widthInput.val();

				// Update Slider values
				$rangeSlider.set( width );

				$colEl.find( '.ui-button' ).removeClass( 'ui-state-active' );
				$colEl.find( '.width-value' ).val( width ).trigger( 'change' );
			} );
		} );
	}
};
;/* global FusionPageBuilderApp, fusionAppConfig */

var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionFormOptions = {
	optionFormOptions: function ( $element ) {
		var $valuesToggle = $element.find( '#form-options-settings' ),
			$optionsGrid = $element.find( '.options-grid' ),
			$addBtn = $element.find( '.fusion-builder-add-sortable-child' ),
			$formOptions = $optionsGrid.find( '.fusion-form-options' ),
			$template = jQuery( '<li class="fusion-form-option">' + $element.find( '.fusion-form-option-template' ).html() + '</li>' ),
			$values = $optionsGrid.find( '.option-values' ),
			$bulkAdd = $element.find( '.bulk-add-modal' ),
			allowMultiple = 'yes' === $optionsGrid.data( 'multiple' ),
			updateValues;

		updateValues = function () {
			var options = [];
			$formOptions.children( 'li' ).each( function () {
				var option 		= [],
					isChecked 	= jQuery( this ).find( '.fusiona-check_circle' ).length;

				option.push( isChecked ? 1 : 0 );

				jQuery( this ).find( 'input' ).each( function () {
					option.push( this.value );
				} );
				options.push( option );
			} );
			$values
				.val( FusionPageBuilderApp.base64Encode( JSON.stringify( options ) ) )
				.trigger( 'change' );
		};

		// Init sortable
		$formOptions.sortable( {
			handle: '.fusion-sortable-move'
		} );

		// Bindings
		$formOptions.on( 'sortupdate', function () {
			updateValues();
		} );
		$formOptions.on( 'change keyup', 'input', function ( event ) {
			event.preventDefault();
			updateValues();
		} );

		$valuesToggle.on( 'click', function () {
			$optionsGrid.toggleClass( 'show-values' );
		} );

		$formOptions.on( 'click', '.fusion-sortable-remove', function ( event ) {
			event.preventDefault();
			jQuery( event.target ).closest( '.fusion-form-option' ).remove();
			updateValues();
		} );

		$formOptions.on( 'click', '.fusion-sortable-check', function( event ) {
			var $el 		= jQuery( this ).find( '.fusiona-check_circle_outline' ),
				isChecked 	= $el.hasClass( 'fusiona-check_circle' );

			event.preventDefault();

			if ( ! allowMultiple ) {
				$formOptions.find( '.fusion-sortable-check .fusiona-check_circle' ).removeClass( 'fusiona-check_circle' );
			}

			if ( isChecked ) {
				$el.removeClass( 'fusiona-check_circle' );
			} else {
				$el.addClass( 'fusiona-check_circle' );
			}
			updateValues();
		} );

		$addBtn.on( 'click', function( event ) {
			var $newEl = $template.clone( true );

			event.preventDefault();

			$formOptions.append( $newEl );
			setTimeout( function () {
				$newEl.find( '.form-option-label input' ).focus();
			}, 100 );
		} );

		$bulkAdd.on( 'click', function( event ) {
			var modalView;

			event.preventDefault();

			if ( jQuery( '.fusion-builder-settings-dialog.bulk-add-dialog' ).length ) {
				return;
			}

			modalView = new FusionPageBuilder.BulkAddView( {
				choices: fusionAppConfig.predefined_choices
			} );

			jQuery( modalView.render().el ).dialog( {
				title: 'Bulk Add / Predefined Choices',
				dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog bulk-add-dialog',
				resizable: false,
				width: 450,
				buttons: {
					'Insert Choices': function() {
						var choices = modalView.getChoices(),
							$newEl;
						_.each( choices, function( choice ) {
							$newEl 	= $template.clone( true );
							if ( choice.includes( '|' ) ) {
								choice = choice.split( '|' );
								$newEl.find( 'input.label' ).val( choice[ 0 ] );
								$newEl.find( 'input.value' ).val( choice[ 1 ] );
								$valuesToggle.prop( 'checked', true );
								$optionsGrid.addClass( 'show-values' );
							} else {
								$newEl.find( 'input.label' ).val( choice );
							}
							$formOptions.append( $newEl );
						} );

						updateValues();
						jQuery( this ).dialog( 'close' );
					},
					Cancel: function() {
						jQuery( this ).dialog( 'close' );
					}
				},
				beforeClose: function() {
					jQuery( this ).remove();
				}

			} );
		} );
	}
};
;/* global FusionPageBuilderApp, fusionBuilderText */

var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionLogics = {
	optionLogics: function ( $element ) {
		var $optionsGrid = $element.find( '.options-grid' ),
			$addBtn = $element.find( '.fusion-builder-add-sortable-child' ),
			$fusionLogics = $optionsGrid.find( '.fusion-logics' ),
			$template = jQuery( '<li class="fusion-logic">' + $element.find( '.fusion-logic-template' ).html() + '</li>' ),
			$values = $optionsGrid.find( '.logic-values' ),
			updateValues;

		updateValues = function () {
			var options = [];
			$fusionLogics.children( 'li' ).each( function () {
				var option 		= {},
					operator 	 = jQuery( this ).find( '.fusion-sortable-operator' );

				// operator.
				option.operator  =  operator.hasClass( 'and' ) ? 'and' : 'or';
				// comparison.
				option.comparison = jQuery( this ).find( '.logic-comparison-selection' ).val();
				// field.
				option.field = jQuery( this ).find( 'select.fusion-logic-choices' ).val();
				// desired value.
				option.value = jQuery( this ).find( '.fusion-logic-option' ).val();
				// additinals.
				if ( jQuery( this ).find( '.logic-additionals' ).length ) {
					option.additionals = jQuery( this ).find( '.fusion-logic-additionals-field' ).val();
				}
				options.push( option );
			} );
			$values
				.val( FusionPageBuilderApp.base64Encode( JSON.stringify( options ) ) )
				.trigger( 'change' );
		};

		// Init sortable
		$fusionLogics.sortable( {
			items: '.fusion-logic',
			tolerance: 'pointer',
			cursor: 'move',
			connectWith: '.fusion-logics',
			handle: '.fusion-logic-controller-head',
			axis: 'y'
		} );

		// Bindings
		$fusionLogics.on( 'sortupdate', function () {
			updateValues();
		} );

		$fusionLogics.on( 'change keyup', 'input', function ( event ) {
			event.preventDefault();
			updateValues();
		} );

		$fusionLogics.on( 'change', 'select.fusion-logic-option', function( event ) {
			event.preventDefault();
			updateValues();
		} );

		$fusionLogics.on( 'change', 'select.fusion-logic-choices', function( event ) {
			var allChoices  = $fusionLogics.closest( '.fusion-builder-option-logics' ).find( '.fusion-logics-all-choices' ).text(),
				selection     = jQuery( this ).val(),
				selectionText = jQuery( this ).closest( 'select' ).find( 'option:selected' ).text(),
				$wrapper      = jQuery( this ).closest( '.fusion-logic' ),
				$comparisons  = '',
				$options      = '',
				isSelected,
				currentChoice;

			event.preventDefault();

			try {
				allChoices = JSON.parse( allChoices );
			} catch ( e ) {
				allChoices = [];
			}

			$wrapper.find( 'h4.logic-title' ).text( selectionText );

			currentChoice = allChoices.find( ( { id } ) => id === selection );

			if ( 'object' === typeof currentChoice ) {
				if ( 'object' === typeof currentChoice.comparisons ) {
					jQuery.each( currentChoice.comparisons, function( comparisonValue, comparisonName ) {
						isSelected    = 'equal' === comparisonValue ? 'active' : '';
						$comparisons   += '<option value="' + comparisonValue + '" ' + isSelected + '>' + comparisonName + '</select>';
					} );
				}

				$wrapper.find( '.logic-comparison-selection' ).empty().append( $comparisons );

				switch ( currentChoice.type ) {
				case 'select':
					if ( 'object' === typeof currentChoice.options ) {
						$options += '<div class="fusion-select-wrapper">';
						$options += '<select class="fusion-logic-option fusion-hide-from-atts">';
						jQuery.each( currentChoice.options, function( key, choice ) {
							$options += '<option value="' + key + '">' + choice + '</option>';
						} );
						$options += '</select>';
						$options += '<span class="fusiona-arrow-down"></span>';
						$options += '</div>';
					}

					$wrapper.find( '.logic-value-field' ).html( $options );
					break;

				case 'text':
					$options = `<input type="text" value="" placeholder="${currentChoice.placeholder || fusionBuilderText.condition_value}" class="fusion-hide-from-atts fusion-logic-option" />`;
					$wrapper.find( '.logic-value-field' ).html( $options );
					break;
				}

				$wrapper.find( '.logic-additionals' ).remove();
				if ( 'undefined' !== typeof currentChoice.additionals ) {
					switch ( currentChoice.additionals.type ) {
					case 'select':
						if ( 'object' === typeof currentChoice.additionals.options ) {
							$options = '<div class="logic-additionals">';
							$options += '<div class="select_arrow"></div>';
							$options += '<select class="fusion-logic-additionals fusion-hide-from-atts fusion-select-field">';
							jQuery.each( currentChoice.additionals, function( key, choice ) {
								$options += '<option value="' + key + '">' + choice + '</option>';
							} );
							$options += '</select>';
							$options += '</div>';
						}

						$wrapper.find( '.logic-field' ).append( $options );
						break;

					case 'text':
						$options = '<div class="logic-additionals">';
						$options += '<input type="text" value="" placeholder="' + currentChoice.additionals.placeholder + '" class="fusion-hide-from-atts fusion-logic-additionals-field" />';
						$options += '</div>';
						$wrapper.find( '.logic-field' ).append( $options );
						break;
					}
				}
			}

			updateValues();
		} );

		$fusionLogics.on( 'click', '.fusion-sortable-remove', function ( event ) {
			event.preventDefault();
			jQuery( event.target ).closest( '.fusion-logic' ).remove();

			updateValues();
		} );

		$fusionLogics.on( 'click', '.fusion-sortable-edit, h4.logic-title', function( event ) {
			var $parent = jQuery( this ).closest( '.fusion-logic' );
			event.preventDefault();

			$parent.find( '.fusion-logic-controller-content' ).slideToggle( 'fast' );
		} );


		$fusionLogics.on( 'click', '.logic-operator', function() {
			var $el = jQuery( this ).find( '.fusion-sortable-operator' );

			if ( $el.hasClass( 'and' ) ) {
				$el.removeClass( 'and' ).addClass( 'or' );
				$el.closest( '.fusion-logic' ).addClass( 'has-or' ).attr( 'aria-label-or', fusionBuilderText.logic_separator_text );
			} else {
				$el.removeClass( 'or' ).addClass( 'and' );
				$el.closest( '.fusion-logic' ).removeClass( 'has-or' );
			}
			updateValues();
		} );

		$fusionLogics.on( 'change', '.logic-comparison-selection', function() {
			event.preventDefault();
			updateValues();
		} );

		$addBtn.on( 'click', function( event ) {
			var $newEl = $template.clone( true );

			event.preventDefault();

			$fusionLogics.find( '.fusion-logic-controller-content' ).hide();

			$fusionLogics.append( $newEl );
			updateValues();
		} );
	}
};
;/* globals FusionPageBuilderApp, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

function fusionHubSpotMapOption( $element ) {
	var self = this;

	// Cut off check.
	if ( 'object' !== typeof FusionApp.data.hubspot || 'undefined' === typeof FusionApp.data.hubspot.properties ) {
		return;
	}

	// Set reusable vars.
	this.properties  = FusionApp.data.hubspot.properties;
	this.preferences = FusionApp.data.hubspot.preferences;
	this.$el         = $element.find( '.hubspot_map .fusion-mapping' );
	this.options     = false;
	this.$input      = $element.find( 'input#hubspot_map' );
	this.values      = {};

	try {
		self.values = JSON.parse( self.$input.val() );
	} catch ( e ) {
		console.warn( 'Error triggered - ' + e );
	}

	// Initial build.
	this.updateMap();

	// Add listeners.
	FusionPageBuilderApp.collection.on( 'change reset add remove', function() {
		self.updateMap();
	} );

	this.$el.on( 'change', 'select', function() {
		self.updateValues();
	} );
}

fusionHubSpotMapOption.prototype.updateValues  = function() {
	var values = {};

	this.$el.find( 'select' ).each( function() {
		values[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
	} );

	this.values = values;

	this.$input.val( JSON.stringify( values ) );
	setTimeout( () => {
		this.$input.trigger( 'change' );
	}, 10 );
};

fusionHubSpotMapOption.prototype.updateMap  = function() {
	var formElements = false,
		self         = this,
		options      = this.getOptions();

	// Mark old ones.
	self.$el.find( '.form-input-entry' ).addClass( 'fusion-old' );

	if ( 'object' !== typeof FusionPageBuilderApp.collection ) {
		self.$el.empty();
		return;
	}

	// Filter map to only get form elements.
	formElements = _.filter( FusionPageBuilderApp.collection.models, function( element ) {
		var params = element.get( 'params' );
		if ( 'object' !== typeof params ) {
			return false;
		}
		return element.get( 'element_type' ).includes( 'fusion_form' ) && 'fusion_form_submit' !== element.get( 'element_type' ) && ( 'string' === typeof params.label || 'string' === typeof params.name );
	} );

	// Add entries.
	_.each( formElements, function( formElement ) {
		var params     = formElement.get( 'params' ),
			inputLabel = 'string' === typeof params.label && '' !== params.label ? params.label : params.name;

		// If we don't already have this, add it.
		if ( ! self.$el.find( '#fusionmap-' + params.name ).length ) {
			self.$el.append( '<div class="form-input-entry"><label for="fusionmap-' + params.name + '">' + inputLabel + '</label><div class="fusion-select-wrapper"><select class="fusion-dont-update" name="' + params.name + '" id="fusionmap-' + params.name + '">' + options + '</select><span class="fusiona-arrow-down"></span></div></div>' );
		} else {
			self.$el.find( '#fusionmap-' + params.name ).closest( '.form-input-entry' ).removeClass( 'fusion-old' ).find( 'label' ).html( inputLabel );
		}

		// Make sure value is selected.
		if ( 'string' === typeof self.values[ params.name ] ) {
			self.$el.find( '#fusionmap-' + params.name ).val( self.values[ params.name ] );
		}
	} );

	// Remove any extras still marked as old.
	self.$el.find( '.fusion-old' ).remove();
};

fusionHubSpotMapOption.prototype.getOptions = function() {
	var options       = '',
		otherOptions  = '',
		commonOptions = '',
		common        = [
			'email',
			'firstname',
			'lastname',
			'phone',
			'company'
		];

	if ( 'object' === typeof this.options ) {
		return this.options;
	}

	this.properties = _.sortBy( this.properties, 'label' );

	// Automatic propery match.
	options += '<optgroup label="' + FusionApp.data.hubspot.common + '">';
	options += '<option value="">' + FusionApp.data.hubspot.automatic + '</option>';
	options += '<option value="fusion-none">' + FusionApp.data.hubspot.none + '</option>';

	// Add actual properties.
	_.each( this.properties, function( property ) {
		if ( common.includes( property.name ) ) {
			commonOptions += '<option value="' + property.name + '">' + property.label + '</option>';
		} else {
			otherOptions  += '<option value="' + property.name + '">' + property.label + '</option>';
		}
	} );

	options += commonOptions;
	options += '</optgroup>';

	// Add communication preferences if we have them, prefixed.
	if ( 'object' === typeof this.preferences ) {
		options += '<optgroup label="' + FusionApp.data.hubspot.communicationPreferences + '">';
		_.each( this.preferences, function( preference ) {
			options += '<option value="compreference_' + preference.id + '">' + preference.name + '</option>';
		} );
		options += '</optgroup>';
	}

	if ( '' !== otherOptions ) {
		options += '<optgroup label="' + FusionApp.data.hubspot.other + '">';
		options += otherOptions;
		options += '</optgroup>';
	}
	this.options = options;

	return this.options;
};

FusionPageBuilder.options.fusionHubSpotMap = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.1
	 *
	 * @return {void}
	 */
	optionHubSpotMap: function( $element ) {
		if ( 'undefined' === typeof this.hubspotMap ) {
			this.hubspotMap = new fusionHubSpotMapOption( $element );
		}
	}
};
;/* global ajaxurl */
/* eslint no-empty-function: ["error", { "allow": ["functions"] }] */

var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

( function() {

	// Layout Options View
	FusionPageBuilder.layoutConditions = Backbone.View.extend( {
		template: FusionPageBuilder.template( jQuery( '#fusion-layout-options' ).html() ),
		events: {
			'click .close,.fusion-layout-overlay': 'closeModal',
			'change input[type="checkbox"]': 'inputChange',
			'click .layout-option-type,.layout-mode a': 'switchTab',
			'click .layout-option-parent:not(.active) .load-child': 'showChildOptions',
			'click .layout-option-parent.active .load-child': 'hideChildOptions',
			'click .load-more': '_loadMore',
			'input .layoutbox-search input[type="search"]': '_handleSearchInput',
			'keyup .layoutbox-search input[type="search"]': '_handleSearchInput',
			'click .remove-condition': 'removeCondition'
		},

		templateForChildOption: FusionPageBuilder.template( jQuery( '#fusion-layout-child-option' ).html() ),

		/**
		 * Initialize the layout
		 *
		 * @since 3.6
		 * @return {void}
		 */
		initialize: function( options ) {
			this.handleSearchInput = _.debounce( this.handleSearchInput, 300 );
			this.loadMore          = _.debounce( this.loadMore, 300 );
			this.conditions        = options.conditions;
			this.item              = options.item;
		},

		/**
		 * Calls loadMore() so it can debounce correctly
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		_loadMore: function( event ) {
			this.loadMore( event );
		},

		/**
		 * Calls handleSearchInput() so it can debounce correctly
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		_handleSearchInput: function( event ) {
			this.handleSearchInput( event );
		},

		/**
		 * Get all conditions.
		 *
		 * @since 3.6
		 * @return {object}
		 */
		getConditions: function() {
			return this.conditions;
		},

		/**
		 * Removes condition from Manage Conditions section.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		removeCondition: function( event ) {
			var conditions 	= this.getConditions(),
				$parent		= jQuery( event.target ).parent(),
				id 			= $parent.data( 'condition-id' ),
				condition 	= conditions[ id ];

			event.preventDefault();

			// Uncheck current condition box
			this.getConditionCheckbox( id ).prop( 'checked', false );
			// If it's a child condition remove it from preview
			if ( condition.parent ) {
				this.$el.find( 'li[data-condition-id="' + id + '"]' ).remove();
			}
			delete conditions[ id ];
			// Remove condition from Manage Conditions section
			$parent.remove();

			this.updateConditionsSectionsVisibility();
		},

		/**
		 * Hide or show Manage Conditions parts.
		 *
		 * @since 3.6
		 * @return {void}
		 */
		updateConditionsSectionsVisibility: function() {
			var $includeConditions 		= this.$el.find( '.include .layout-conditions' ),
				$excludeConditions 		= this.$el.find( '.exclude .layout-conditions' ),
				hasIncludeConditions	= Boolean( $includeConditions.find( 'span' ).length ),
				hasExcludeConditions	= Boolean( $excludeConditions.find( 'span' ).length );

			// If there are include or exclude conditions we show the corresponding section
			// If there are no conditions we  show empty conditions placeholder
			if ( hasIncludeConditions ) {
				$includeConditions.parent().show();
			} else {
				$includeConditions.parent().hide();
			}
			if ( hasExcludeConditions ) {
				$excludeConditions.parent().show();
			} else {
				$excludeConditions.parent().hide();
			}
			if ( hasIncludeConditions || hasExcludeConditions ) {
				this.$el.find( '.empty-conditions' ).hide();
			} else {
				this.$el.find( '.empty-conditions' ).show();
			}
		},

		/**
		 * Render the template.
		 *
		 * @since 3.6
		 * @return {Object} this.
		 */
		render: function() {
			var self		= this,
				conditions 	= this.getConditions();

			this.$el.html( this.template( this ) );

			// Update checkboxes state
			this.$el.find( 'input[type="checkbox"]' ).each( function() {
				if ( this.value in conditions && this.dataset.mode === conditions[ this.value ].mode ) {
					this.checked = true;
				}
			} );

			// Update previews and update checkboxes that were previously selected.
			_.each( this.getConditions(), function( condition, id ) {
				if ( condition.parent ) {
					self.$el.find( '.layout-option-parent[data-condition="' + condition.parent + '"] + .child-options-preview' )
						.append(
							'<li data-condition-id="' + id + '" class="preview-' + condition.mode + '">' + condition.label + '</li>'
						);
				}
				self.getConditionCheckbox( id ).prop( 'checked', true );
			} );

			this.renderConditionsSection();

			// Add listener for escape key to close modal.
			jQuery( 'body' ).on( 'keydown', function( event ) {
				if ( 27 === event.keyCode || '27' === event.keyCode ) {
					jQuery( 'body' ).off( 'keydown' );
					self.renderLayoutBoxConditionsSection();
					self.remove( event );
					return false;
				}
				return true;
			} );

			return this;
		},

		/**
		 * Returns a DOM element for condition checkbox
		 *
		 * @since 3.6
		 * @param {String} id - Condition id.
		 * @return {Object} this.
		 */
		getConditionCheckbox: function( id ) {
			var condition = this.getConditions()[ id ];
			if ( condition.parent ) {
				return this.$el.find( '#' + id.replace( '|', '\\|' ) + '-' + condition.mode );
			}
			return this.$el.find( '#' + id + '-' + condition.mode );
		},

		/**
		 * Loads child options.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {Object} this.
		 */
		showChildOptions: function( event ) {
			var $target = jQuery( event.currentTarget ),
				$parent = $target.parent();

			event.preventDefault();

			$target.find( 'i' ).addClass( 'fusiona-chevron-small-up' );

			// Hide Preview
			$parent.siblings( '.child-options-preview' ).hide();
			$parent.addClass( 'active' );
		},

		/**
		 * Hide child options.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {Object} this.
		 */
		hideChildOptions: function( event ) {
			var $input		= jQuery( event.currentTarget ),
				$parent		= $input.parent(),
				$preview 	= $parent.siblings( '.child-options-preview' );

			event.preventDefault();
			$input.find( 'i' ).removeClass( 'fusiona-chevron-small-up' );
			$parent.removeClass( 'active loading' );


			// Update and show child previews
			$preview.html( '' );
			_.each( this.getConditions(), function( condition, id ) { //eslint-disable-line no-unused-vars
				if ( condition.parent ===  $parent.data( 'condition' ) ) {
					$preview.append(
						'<li data-condition-id="' + id + '" class="preview-' + condition.mode + '">' + condition.label + '</li>'
					);
				}
			} );
			$preview.show();
		},

		/**
		 * Ajax handler
		 *
		 * @since 3.5
		 * @param {Object} data
		 * @param {Function} callback
		 * @return {Void}.
		 */
		doAjax: function( data, callback ) {
			jQuery.ajax( {
				type: 'POST',
				url: ajaxurl,
				dataType: 'json',
				data: data,
				complete: function( response ) {
					if ( 200 === response.status ) {
						return callback( response.responseJSON );
					}
					return callback( null, response );
				}
			} );
		},

		/**
		 * Fetches child options for specific parent.
		 *
		 * @since 3.6
		 * @param {Object} $parent - The layout option parent Element.
		 * @return {void}
		 */
		loadChildOptions: function( $parent ) {
			var self			= this,
				page 			= $parent.data( 'page' ),
				parentCondition = $parent.data( 'condition' );

			page = page ? parseInt( page ) + 1 : 1;

			this.doAjax( {
				action: 'fusion_admin_layout_options',
				parent: parentCondition,
				page: page,
				security: this.item.find( '#layout-conditions-nonce' ).val()
			}, function( response ) {
				if ( response.success ) {
					self.renderChildOptions( $parent, page, response.data );
				}
			} );
		},

		/**
		 * Renders child options for specific parent.
		 *
		 * @since 3.6
		 * @param {Object} $parent
		 * @param {Number} page
		 * @param {Array} options
		 * @return {void}
		 */
		renderChildOptions: function( $parent, page, options ) {
			var self 		= this,
				container 	= $parent.find( '.child-options' ),
				conditions 	= this.getConditions();

			_.each( options, function( option ) {
				option.checked = conditions[ option.id ] && conditions[ option.id ].mode;
				container.append( self.templateForChildOption( option ) );
			} );

			$parent.removeClass( 'loading' );
			// Update results page
			$parent.data( 'page', page );

			// If less than 10 results change button label and disable button
			// else show button and enable it again
			if ( 10 > options.length ) {
				$parent.find( '.load-more' ).addClass( 'disabled' );
				$parent.find( '.load-more span' ).text( $parent.find( '.load-more' ).data( 'empty' ) );
			} else {
				$parent.find( '.load-more' ).show().prop( 'disabled', false ).removeClass( 'loading' );
			}
		},

		/**
		 * Handler for load more button.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		loadMore: function( event ) {
			var $parent = jQuery( event.target ).closest( '.layout-option-parent' );
			jQuery( event.currentTarget ).addClass( 'loading' ).prop( 'disabled', true );
			this.loadChildOptions( $parent );
		},

		/**
		 * Fetches child options for specific parent.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		inputChange: function( event ) {
			var conditions 		= this.getConditions(),
				input 			= event.target,
				conditionId		= input.name,
				conditionMode 	= input.value;

			// If the user is selecting the same condition perform a deselect
			// Else if is selecting same condition but the mode is different perform a toggle
			// Else were adding a new condition
			if ( conditions[ conditionId ] && conditions[ conditionId ].mode === conditionMode ) {
				jQuery( input ).prop( 'checked', false );
				this.updateParent( input, conditionId );
				delete conditions[ conditionId ];
			} else if ( conditions[ conditionId ] ) {
				jQuery( input ).siblings( 'input' ).prop( 'checked', false );
				conditions[ conditionId ].mode = conditionMode;
				this.updateParent( input, conditionId );
			} else {
				conditions[ conditionId ] = {
					label: input.dataset.label,
					type: input.dataset.type,
					mode: conditionMode,
					[ input.dataset.type ]: conditionId,
					parent: input.dataset.parent
				};
				this.updateParent( input, conditionId );
			}

			this.renderConditionsSection();
		},

		/**
		 * Updates parent options if options selected from search results
		 * @since 3.6
		 * @param input
		 * @param conditionId
		 */
		updateParent: function( input, conditionId ) {
			// If checkbox is from search results update child option if exist
			if ( jQuery( input ).closest( '.layoutbox-search-results' ).length ) {
				this.getConditionCheckbox( conditionId ).each( function() {
					var checkbox = jQuery( this );
					var status = jQuery( input ).prop( 'checked' );
					if ( ! checkbox.is( input ) ) {
						checkbox.siblings( 'input' ).prop( 'checked', false );
						checkbox.prop( 'checked', status );
					}
				} );
			}
		},

		/**
		 * Renders conditions section
		 *
		 * @since 3.6
		 * @return {void}
		 */
		renderConditionsSection: function() {
			// TODO use DiffDOM to avoid jank.
			var $includeConditions 		= this.$el.find( '.include .layout-conditions' ),
				$excludeConditions 		= this.$el.find( '.exclude .layout-conditions' );

			$includeConditions.html( '' );
			$excludeConditions.html( '' );

			_.each( this.getConditions(), function( condition, id ) {
				var $condition = jQuery( '<span data-condition-id="' + id + '">' + condition.label + '<a href="#" class="fusiona-cross remove-condition" aria-label="Remove condition" /></span>' );
				if ( 'include' === condition.mode ) {
					$includeConditions.append( $condition );
				} else {
					$excludeConditions.append( $condition );
				}
			} );
			this.updateConditionsSectionsVisibility();
		},

		/**
		 * Handler for search input.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		handleSearchInput: function( event ) {
			var self 	= this,
				search	= event.target.value,
				$parent	= jQuery( event.target ).closest( '.layout-option-parent' ),
				conditions = this.getConditions();
			// If search is valid do ajax.
			// Else clean results and close dropdown.
			if ( search ) {
				// Add loader
				$parent.find( '.layoutbox-search-results' )
					.attr( 'data-state', 'active' )
					.html( '' )
					.append( '<div class="layoutbox-loader"><div class="fusion-builder-loader"></div></div>' );

				this.doAjax( {
					action: 'fusion_admin_layout_options',
					parent: $parent.data( 'condition' ),
					search: search,
					security: this.item.find( '#layout-conditions-nonce' ).val()
				}, function( response ) {
					var $container, hideSearch;
					if ( response.success ) {
						$container = $parent.find( '.layoutbox-search-results' );
						$container.html( '' );
						if ( response.data.length ) {
							_.each( response.data, function( result ) {
								result.checked = conditions[ result.id ] && conditions[ result.id ].mode;
								$container.append( self.templateForChildOption( result ) );
							} );
							// Hide search results when a click outside $container occurs
							hideSearch = function ( e ) {
								if ( ! $container.is( e.target ) && 0 === $container.has( e.target ).length ) {
									$container.attr( 'data-state', '' );
									jQuery( document ).off( 'mouseup', hideSearch );
								}
							};
							jQuery( document ).on( 'mouseup', hideSearch );
						} else {
							$container.attr( 'data-state', '' );
						}
					}
				} );
			} else {
				$parent.find( '.layoutbox-search-results' ).html( '' ).attr( 'data-state', '' );
			}
		},

		/**
		 * Switches a tab. Takes care of toggling the 'current' & 'inactive' classes
		 * and also changes the 'display' property of elements to properly make the switch.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		switchTab: function( event ) {
			var $tabLink = jQuery( event.currentTarget ),
				tab      = $tabLink.attr( 'href' );

			if ( event ) {
				event.preventDefault();
			}

			$tabLink.addClass( 'current' ).removeClass( 'inactive' );
			$tabLink.siblings().removeClass( 'current' ).addClass( 'inactive' );


			this.$el.find( tab ).siblings( '.layout-mode-tab, .layout-option-tab' ).hide();
			this.$el.find( tab ).show();
		},

		/**
		 * Renders conditions section
		 *
		 * @since 3.6
		 * @return {void}
		 */
		renderLayoutBoxConditionsSection: function() {
			var $layoutBox 	= this.item.find( '.awb-conditions ul' ),
				conditions	= this.getConditions();

			$layoutBox.find( '.include, .exclude' ).remove();
			$layoutBox.closest( '.awb-off-canvas-conditions-constoller' ).removeClass( 'has-conditions' );

			if ( 'object' === typeof conditions && 0 < Object.keys( conditions ).length ) {
				$layoutBox.closest( '.awb-off-canvas-conditions-constoller' ).addClass( 'has-conditions' );
				_.each( conditions, function( condition ) {
					var $condition = jQuery( '<li class="' + condition.mode + '"><span>' + condition.label + '</span></li>' );
					$layoutBox.append( $condition );
				} );
			}

			this.item.find( '.awb-conditions-value' ).val( JSON.stringify( conditions ) ).change();
		},

		/**
		 * Close layout options modal.
		 *
		 * @since 3.6
		 * @param {Object} event - The event.
		 * @return {void}
		 */
		closeModal: function( event ) {
			event.preventDefault();
			this.renderLayoutBoxConditionsSection();
			this.remove();
		}
	} );

}( jQuery ) );

FusionPageBuilder.options.fusionLayoutConditions = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.6
	 *
	 * @return {void}
	 */
	optionLayoutConditions: function( $element ) {

		if ( 'undefined' === typeof this.layoutConditions ) {
			$element.find( '.awb-manage-conditions' ).click( function( e ) {
				var conditions = jQuery( this ).closest( '.fusion-builder-option' ).find( '.awb-conditions-value' ).val();

				e.preventDefault();

				conditions            = '""' === conditions || 0 === conditions.length ? '{}' : conditions;
				this.layoutConditions = new FusionPageBuilder.layoutConditions( {
					'conditions': JSON.parse( conditions ),
					'item': jQuery( this ).closest( '.fusion-builder-option' )
				} );

				jQuery( '.layout-conditions-wrapper' ).remove();
				jQuery( 'body' ).append( '<div class="layout-conditions-wrapper"></div>' ); // Needed for styles.
				jQuery( 'body .layout-conditions-wrapper' ).prepend( this.layoutConditions.render().el );
			} );
		}
	}
};
;/* globals FusionPageBuilderApp, FusionApp, fusionSanitize */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

function fusionMailchimpMapOption( $element ) {
	var self = this;

	// Cut off check.
	if ( 'object' !== typeof FusionApp.data.mailchimp || 'undefined' === typeof FusionApp.data.mailchimp.fields || 'undefined' === typeof FusionApp.data.fusionPageOptions.form_submission.fields.mailchimp_options || 'undefined' === typeof FusionApp.data.fusionPageOptions.form_submission.fields.mailchimp_options.fields.mailchimp_lists ) {
		return;
	}

	// Set reusable vars.
	this.fields  = FusionApp.data.mailchimp.fields;
	this.$el     = $element.find( '.mailchimp_map .fusion-mapping' );
	this.options = false;
	this.$input  = $element.find( 'input#mailchimp_map' );
	this.values  = {};

	try {
		self.values = JSON.parse( self.$input.val() );
	} catch ( e ) {
		console.warn( 'Error triggered - ' + e );
	}

	// Initial build.
	this.updateMap();

	// Add listeners.
	FusionPageBuilderApp.collection.on( 'change reset add remove', function() {
		self.updateMap();
	} );

	this.$el.on( 'change', 'select', function() {
		self.updateValues();
	} );
}

fusionMailchimpMapOption.prototype.updateValues  = function() {
	var values = {};

	this.$el.find( 'select' ).each( function() {
		values[ jQuery( this ).attr( 'name' ) ] = jQuery( this ).val();
	} );

	this.values = values;

	this.$input.val( JSON.stringify( values ) );
	setTimeout( () => {
		this.$input.trigger( 'change' );
	}, 10 );
};

fusionMailchimpMapOption.prototype.updateMap  = function() {
	var formElements = false,
		self         = this,
		options      = this.getOptions();

	// Mark old ones.
	self.$el.find( '.form-input-entry' ).addClass( 'fusion-old' );

	if ( 'object' !== typeof FusionPageBuilderApp.collection ) {
		self.$el.empty();
		return;
	}

	// Filter map to only get form elements.
	formElements = _.filter( FusionPageBuilderApp.collection.models, function( element ) {
		var params = element.get( 'params' );
		if ( 'object' !== typeof params ) {
			return false;
		}
		return element.get( 'element_type' ).includes( 'fusion_form' ) && 'fusion_form_submit' !== element.get( 'element_type' ) && ( 'string' === typeof params.label || 'string' === typeof params.name );
	} );

	// Add entries.
	_.each( formElements, function( formElement ) {
		var params     = formElement.get( 'params' ),
			inputLabel = 'string' === typeof params.label && '' !== params.label ? params.label : params.name;

		// If we don't already have this, add it.
		if ( ! self.$el.find( '#fusionmap-' + params.name ).length ) {
			self.$el.append( '<div class="form-input-entry"><label for="fusionmap-' + params.name + '">' + inputLabel + '</label><div class="fusion-select-wrapper"><select class="fusion-dont-update" name="' + params.name + '" id="fusionmap-' + params.name + '">' + options + '</select><span class="fusiona-arrow-down"></span></div></div>' );
		} else {
			self.$el.find( '#fusionmap-' + params.name ).closest( '.form-input-entry' ).removeClass( 'fusion-old' ).find( 'label' ).html( inputLabel );
		}

		// Make sure value is selected.
		if ( 'string' === typeof self.values[ params.name ] ) {
			self.$el.find( '#fusionmap-' + params.name ).val( self.values[ params.name ] );
		}
	} );

	// Remove any extras still marked as old.
	self.$el.find( '.fusion-old' ).remove();
};

fusionMailchimpMapOption.prototype.getOptions = function() {
	var options       = '',
		selection     = '',
		defaultVal    = Object.keys( FusionApp.data.fusionPageOptions.form_submission.fields.mailchimp_options.fields.mailchimp_lists.choices )[ 0 ],
		otherOptions  = '',
		commonOptions = '',
		common        = [
			'EMAIL',
			'FNAME',
			'LNAME',
			'ADDRESS',
			'PHONE',
			'BIRTHDAY'
		];

	if ( 'object' === typeof this.options ) {
		return this.options;
	}

	selection   = '' === fusionSanitize.getPageOption( 'mailchimp_lists' ) ? defaultVal : fusionSanitize.getPageOption( 'mailchimp_lists' );

	this.fields = 'undefined' !== typeof this.fields[ selection ] ? this.fields[ selection ].fields : this.fields;
	this.fields = _.sortBy( this.fields, 'name' );

	// Automatic field match.
	options += '<optgroup label="' + FusionApp.data.mailchimp.common + '">';
	options += '<option value="">' + FusionApp.data.mailchimp.automatic + '</option>';
	options += '<option value="fusion-none">' + FusionApp.data.mailchimp.none + '</option>';

	// Add actual fields.
	_.each( this.fields, function( field ) {
		if ( common.includes( field.tag ) ) {
			commonOptions += '<option value="' + field.tag + '">' + field.name + '</option>';
		} else {
			otherOptions  += '<option value="' + field.tag + '">' + field.name + '</option>';
		}
	} );

	options += commonOptions;
	options += '</optgroup>';

	if ( '' !== otherOptions ) {
		options += '<optgroup label="' + FusionApp.data.mailchimp.other + '">';
		options += otherOptions;
		options += '</optgroup>';
	}
	this.options = options;

	return this.options;
};

FusionPageBuilder.options.fusionMailchimpMap = {

	/**
	 * Run actions on load.
	 *
	 * @since 3.1
	 *
	 * @return {void}
	 */
	optionMailchimpMap: function( $element ) {
		if ( 'undefined' === typeof this.mailchimpMap ) {
			this.mailchimpMap = new fusionMailchimpMapOption( $element );
		}
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColorPicker = {
	optionColorpicker: function( $element ) {
		var $colorPicker,
			self = this;

		$element     = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$colorPicker = $element.find( '.fusion-builder-color-picker-hex' );

		if ( $colorPicker.length ) {
			$colorPicker.each( function() {
				var $picker       = jQuery( this ),
					$defaultReset = $picker.closest( '.fusion-builder-option' ).find( '.fusion-builder-default-reset' ),
					parentValue   = 'undefined' !== typeof self.parentValues && 'undefined' !== typeof self.parentValues[ $picker.attr( 'id' ) ] ? self.parentValues[ $picker.attr( 'id' ) ] : false;

				// Child element inheriting default from parent.
				if ( parentValue ) {
					$picker.attr( 'data-default', parentValue );
				}

				$picker.awbColorPicker().on( 'blur', function() {
					if ( jQuery( this ).hasClass( 'iris-error' ) ) {
						jQuery( this ).removeClass( 'iris-error' );
						jQuery( this ).val( '' );
					}
				} );

				// Default reset icon, set value to empty.
				$defaultReset.on( 'click', function( event ) {
					var dataDefault,
						$input = jQuery( this ).closest( '.fusion-builder-option' ).find( '.color-picker' );

					event.preventDefault();
					dataDefault = $input.attr( 'data-default' ) || $input.attr( 'data-default-color' );

					// Make the color picker to start from the default color on open.
					if ( dataDefault ) {
						$input.val( dataDefault ).trigger( 'change' );
					}
					$input.val( '' ).trigger( 'change' );
				} );
			} );
		}
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionDateTimePicker = {
	optionDateTimePicker: function( element ) {
		var datePicker, timePicker;

		element    = element || this.$el;
		datePicker = element.find( '.fusion-datetime-datepicker' );
		timePicker = element.find( '.fusion-datetime-timepicker' );

		if ( datePicker.length ) {
			jQuery( datePicker ).fusiondatetimepicker( {
				format: 'yyyy-MM-dd',
				pickTime: false
			} );
		}

		if ( timePicker.length ) {
			jQuery( timePicker ).fusiondatetimepicker( {
				format: 'hh:mm:ss',
				pickDate: false
			} );
		}

		jQuery( datePicker ).on( 'updateDateTime', function() {
			var date = '',
				time = '',
				dateAndTime = '';

			time = jQuery( this ).closest( '.fusion-datetime-container' ).find( '.fusion-time-picker' ).val();
			date = jQuery( this ).find( '.fusion-date-picker' ).val();

			dateAndTime += date ? date + ' ' : '';
			dateAndTime += time ? time : '';

			jQuery( this ).closest( '.option-field' ).find( '.fusion-date-time-picker' ).val( dateAndTime.trim() ).trigger( 'change' );
		} );

		jQuery( timePicker ).on( 'updateDateTime', function() {
			var date = '',
				time = '',
				dateAndTime = '';

			date = jQuery( this ).closest( '.fusion-datetime-container' ).find( '.fusion-date-picker' ).val();
			time = jQuery( this ).find( '.fusion-time-picker' ).val();

			dateAndTime += date ? date + ' ' : '';
			dateAndTime += time ? time : '';

			jQuery( this ).closest( '.option-field' ).find( '.fusion-date-time-picker' ).val( dateAndTime.trim() ).trigger( 'change' );
		} );
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionDimensionField = {
	optionDimension: function( element ) {
		var dimensionField;

		element        = element || this.$el;
		dimensionField = element.find( '.single-builder-dimension' );

		if ( dimensionField.length ) {
			dimensionField.each( function() {
				jQuery( this ).find( '.fusion-builder-dimension input' ).on( 'change paste keyup', function() {
					jQuery( this ).closest( '.single-builder-dimension' ).find( 'input[type="hidden"]' ).val(
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(1) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(1) input' ).val() : '0' ) + ' ' +
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(2) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(2) input' ).val() : '0' ) + ' ' +
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(3) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(3) input' ).val() : '0' ) + ' ' +
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(4) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(4) input' ).val() : '0' )
					).trigger( 'change' );
				} );
			} );
		}
	}
};
;/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionEditor = {

	optionEditor: function( $element ) {
		var allowGenerator   = false,
			thisModel        = this.model,
			content          = '',
			$contentTextareaOption,
			textareaID,
			$contentTextareas,
			$theContent;

		$element          = $element || this.$el;
		$contentTextareas = $element.find( '.fusion-editor-field' );

		if ( 'undefined' !== typeof thisModel.get( 'allow_generator' ) && true === thisModel.get( 'allow_generator' ) ) {
			FusionPageBuilderApp.allowShortcodeGenerator = true;
			allowGenerator = true;
		}

		if ( $contentTextareas.length ) {
			$contentTextareas.each( function() {
				var $contentTextarea = jQuery( this );

				$contentTextareaOption = $contentTextarea.closest( '.fusion-builder-option' );

				content = $contentTextarea.html();

				if ( 'undefined' !== typeof thisModel.get( 'multi' ) && 'multi_element_parent' === thisModel.get( 'multi' ) ) {

					$contentTextareaOption.hide();
					$contentTextarea.attr( 'id', 'fusion_builder_content_main' );
					return;
				}

				if ( 'undefined' !== typeof thisModel.get( 'multi' ) && 'multi_element_child' === thisModel.get( 'multi' ) && 'fusion_pricing_column' !== thisModel.get( 'element_type' ) ) {
					$contentTextarea.attr( 'id', 'child_element_content' );
				}

				$contentTextarea.addClass( 'fusion-init' );

				// Called from shortcode generator
				if ( 'generated_element' === thisModel.get( 'type' ) ) {

					// TODO: unique id ( multiple mce )
					if ( 'multi_element_child' === thisModel.get( 'multi' ) ) {
						$contentTextarea.attr( 'id', 'generator_multi_child_content' );
					} else {
						$contentTextarea.attr( 'id', 'generator_element_content' );
					}

					textareaID = $contentTextarea.attr( 'id' );

					setTimeout( function() {
						$contentTextarea.wp_editor( content, textareaID );

						// If it is a placeholder, add an on focus listener.
						if ( jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
							window.tinyMCE.get( textareaID ).on( 'focus', function() {
								$theContent = window.tinyMCE.get( textareaID ).getContent();
								$theContent = jQuery( '<div/>' ).html( $theContent ).text();
								if ( $theContent === jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
									window.tinyMCE.get( textareaID ).setContent( '' );
								}
							} );
						}
						window.tinyMCE.get( textareaID ).on( 'keyup change', function() {
							var editor = window.tinyMCE.get( textareaID );

							$theContent = editor.getContent();
							jQuery( '#' + textareaID ).val( $theContent ).trigger( 'change' );
						} );
					}, 100 );
				} else {
					textareaID = $contentTextarea.attr( 'id' );

					setTimeout( function() {

						$contentTextarea.wp_editor( content, textareaID, allowGenerator );

						// If it is a placeholder, add an on focus listener.
						if ( jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
							window.tinyMCE.get( textareaID ).on( 'focus', function() {
								$theContent = window.tinyMCE.get( textareaID ).getContent();
								$theContent = jQuery( '<div/>' ).html( $theContent ).text();
								if ( $theContent === jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
									window.tinyMCE.get( textareaID ).setContent( '' );
								}
							} );
						}

						if ( window.tinyMCE.get( textareaID ) ) {
							window.tinyMCE.get( textareaID ).on( 'keyup change', function() {
								var editor = window.tinyMCE.get( textareaID );

								$theContent = editor.getContent();
								jQuery( '#' + textareaID ).val( $theContent ).trigger( 'change' );
							} );
						}

					}, 100 );
				}
			} );
		}
	}
};
;/* global FusionApp, fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionExport = {

	optionExport: function( $element ) {
		var self = this,
			$export,
			$exportMode,
			$fileDownload,
			$copyButton,
			$saveButton;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$export  = $element.find( '.fusion-builder-option.export' );

		if ( $export.length ) {
			$exportMode   = $export.find( '#fusion-export-mode' );
			$fileDownload = $export.find( '#fusion-export-file' );
			$copyButton   = $export.find( '#fusion-export-copy' );
			$saveButton   = $export.find( '#fusion-page-options-save' );

			$exportMode.on( 'change', function( event ) {
				event.preventDefault();
				$export.find( '.fusion-export-options > div' ).hide();
				$export.find( '.fusion-export-options > div[data-id="' + jQuery( event.target ).val() + '"]' ).show();
			} );

			$copyButton.on( 'click', function( event ) {
				event.preventDefault();
				jQuery( event.target ).prev( 'textarea' )[ 0 ].select();
				document.execCommand( 'copy' );
			} );

			$fileDownload.on( 'click', function( event ) {
				event.preventDefault();
				self.exportOptions( event );
			} );

			$saveButton.on( 'click', function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				if ( '' !== jQuery( '#fusion-new-page-options-name' ).val() ) {
					$export.addClass( 'partial-refresh-active' );
					self.ajaxPOSave( $export );
				}
			} );
		}
	},

	updateExportCode: function() {
		var $textArea = this.$el.find( '.fusion-builder-option.export #export-code-value' ),
			context   = $textArea.attr( 'data-context' ),
			data      = 'TO' === context ? JSON.stringify( FusionApp.settings ) : JSON.stringify( this.getFusionMeta() );

		$textArea.val( data );
	},

	exportOptions: function( event ) {
		var dataStr,
			dlAnchorElem,
			context = jQuery( event.target ).attr( 'data-context' ),
			data,
			today    = new Date(),
			date     = today.getFullYear() + '-' + ( today.getMonth() + 1 ) + '-' + today.getDate(),
			fileName = 'fusion-theme-options-' + date;

		if ( 'TO' === context || 'FBE' === context ) {
			data = FusionApp.settings;

			// So import on back-end works.
			data.fusionredux_import_export = '';
			data[ 'fusionredux-backup' ]     = 1;
		} else {
			data     = this.getFusionMeta();
			fileName = 'avada-page-options-' + date;
		}

		dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent( JSON.stringify( data ) );

		dlAnchorElem = document.createElement( 'a' );
		dlAnchorElem.setAttribute( 'href', dataStr );
		dlAnchorElem.setAttribute( 'download', fileName + '.json' );
		dlAnchorElem.click();
		dlAnchorElem.remove();
	},

	ajaxPOSave: function( $export ) {
		var data = {
			action: 'fusion_page_options_save',
			fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
			post_id: FusionApp.data.postDetails.post_id,
			custom_fields: this.getFusionMeta(),
			options_title: jQuery( '#fusion-new-page-options-name' ).val()
		};

		jQuery.get( {
			url: fusionAppConfig.ajaxurl,
			data: data,
			dataType: 'json'
		} )
			.done( function( response ) {
				jQuery( '.fusion-select-options' ).append( '<label class="fusion-select-label" data-value="' + response.saved_po_dataset_id + '">' + response.saved_po_dataset_title  + '</label>' );
				jQuery( '#fusion-new-page-options-name' ).val( '' );
				$export.removeClass( 'partial-refresh-active' );

				// This is temp ID, not used anywhere really.
				FusionApp.data.savedPageOptions[ response.saved_po_dataset_id ] = {
					id: response.saved_po_dataset_id,
					title: response.saved_po_dataset_title,
					data: response.saved_po_data
				};
			} )
			.fail( function() {
				$export.removeClass( 'partial-refresh-active' );
			} );
	},

	getFusionMeta: function() {
		return {
			_fusion: FusionApp.data.postMeta._fusion
		};
	},

	setFusionMeta: function( newMeta ) {

		jQuery.each( newMeta, function( key, value ) {
			FusionApp.data.postMeta[ key ] = value;
		} );

	}
};
;/* global Fuse, FusionApp, fusionIconSearch, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {},
	FusionDelay;

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionDelay = ( function() {
	var timer = 0;

	return function( callback, ms ) {
		clearTimeout( timer );
		timer = setTimeout( callback, ms );
	};
}() );

FusionPageBuilder.options.fusionIconPicker = {
	optionIconpicker: function( $element ) {
		var $iconPicker;

		$element    = $element || this.$el;
		$iconPicker = $element.find( '.fusion-iconpicker' );

		if ( $iconPicker.length ) {
			$iconPicker.each( function() {
				var $input     = jQuery( this ).find( '.fusion-iconpicker-input' ),
					value      = $input.val(),
					splitVal,
					$container       = jQuery( this ).find( '.icon_select_container' ),
					$containerParent = $container.parent(),
					$search          = jQuery( this ).find( '.fusion-icon-search' ),
					output           = jQuery( '.fusion-icons-rendered' ).length ? jQuery( '.fusion-icons-rendered' ).html() : '',
					outputNav        = jQuery( '.fusion-icon-picker-nav-rendered' ).length ? jQuery( '.fusion-icon-picker-nav-rendered' ).html() : '',
					selectedSetId    = '',
					customIcon       = -1 !== value.indexOf( 'fusion-prefix-' );

				$container.append( output ).before( '<div class="fusion-icon-picker-nav-wrapper"><a href="#" class="fusion-icon-picker-nav-left fusiona-arrow-left"></a><div class="fusion-icon-picker-nav">' + outputNav + '</div><a href="#" class="fusion-icon-picker-nav-right fusiona-arrow-right"></a></div>' );

				if ( '' !== value && -1 === value.indexOf( ' ' ) ) {
					if ( 'undefined' !== typeof FusionApp ) {
						value = FusionApp.checkLegacyAndCustomIcons( value );
					}

					// If custom icon we don't need to update input, just value needs converted for below.
					if ( ! customIcon ) {

						// Wait until options tab is rendered.
						setTimeout( function() {

							// Update form field with new values.
							$input.attr( 'value', value ).trigger( 'change' );
						}, 1000 );
					}
				}

				// Icon navigation link is clicked.
				$containerParent.find( '.fusion-icon-picker-nav > .fusion-icon-picker-nav-item' ).on( 'click', function( e ) {
					e.preventDefault();

					jQuery( '.fusion-icon-picker-nav-active' ).removeClass( 'fusion-icon-picker-nav-active' );
					jQuery( this ).addClass( 'fusion-icon-picker-nav-active' );
					$container.find( '.fusion-icon-set' ).css( 'display', 'none' );
					$container.find( jQuery( this ).attr( 'href' ) ).css( 'display', 'grid' );
				} );

				// Scroll nav div to right.
				$containerParent.find( '.fusion-icon-picker-nav-wrapper > .fusion-icon-picker-nav-right' ).on( 'click', function( e ) {
					e.preventDefault();

					$containerParent.find( '.fusion-icon-picker-nav' ).animate( {
						scrollLeft: '+=100'
					}, 250 );
				} );

				// Scroll nav div to left.
				$containerParent.find( '.fusion-icon-picker-nav-wrapper > .fusion-icon-picker-nav-left' ).on( 'click', function( e ) {
					e.preventDefault();

					$containerParent.find( '.fusion-icon-picker-nav' ).animate( {
						scrollLeft: '-=100'
					}, 250 );
				} );

				if ( value && '' !== value ) {
					splitVal = value.split( ' ' );

					if ( 2 === splitVal.length ) {

						// FA.
						$container.find( '.' + splitVal[ 0 ] + '.' + splitVal[ 1 ] ).parent().addClass( 'selected-element' );
					} else if ( 1 === splitVal.length ) {

						// Custom icon.
						$container.find( '.' + splitVal ).parent().addClass( 'selected-element' );
					}

					// Trigger click on parent nav tab item.
					selectedSetId = $container.find( '.selected-element' ).closest( '.fusion-icon-set' ).prepend( $container.find( '.selected-element' ) ).attr( 'id' );
					$containerParent.find( '.fusion-icon-picker-nav a[href="#' + selectedSetId + '"]' ).trigger( 'click' );
				}

				// Icon click.
				$container.find( '.icon_preview' ).on( 'click', function( event ) {
					var $icon      = jQuery( this ).find( 'i' ),
						subset     = 'fas',
						$scopedContainer = jQuery( this ).closest( '.fusion-iconpicker' ),
						fontName   = 'fa-' + $icon.attr( 'data-name' ),
						inputValue = '';


					if ( ! $icon.hasClass( 'fas' ) && ! $icon.hasClass( 'fab' ) && ! $icon.hasClass( 'far' ) && ! $icon.hasClass( 'fal' ) ) {

						// Custom icon set, so we need to add prefix.
						inputValue = 'fusion-prefix-' + $icon.attr( 'class' );
					} else if ( $icon.hasClass( 'fab' ) ) {
						subset = 'fab';
					} else if ( $icon.hasClass( 'far' ) ) {
						subset = 'far';
					} else if ( $icon.hasClass( 'fal' ) ) {
						subset = 'fal';
					}

					// FA icon.
					if ( '' === inputValue ) {
						inputValue = fontName + ' ' + subset;
					}

					if ( jQuery( this ).hasClass( 'selected-element' ) ) {
						jQuery( this ).removeClass( 'selected-element' );
						$scopedContainer.find( 'input.fusion-iconpicker-input' ).attr( 'value', '' ).trigger( 'change' );
						$scopedContainer.find( '.fusion-iconpicker-icon > span' ).attr( 'class', '' );
					} else {
						$scopedContainer.find( '.selected-element' ).removeClass( 'selected-element' );
						jQuery( event.currentTarget ).addClass( 'selected-element' );
						$scopedContainer.find( 'input.fusion-iconpicker-input' ).attr( 'value', inputValue ).trigger( 'change' );
						$scopedContainer.find( '.fusion-iconpicker-icon > span' ).attr( 'class', inputValue );
					}
				} );

				// Icon Search bar
				$search.on( 'change paste keyup', function() {
					var $searchInput = jQuery( this );

					FusionDelay( function() {
						var options,
							fuse,
							result;

						if ( $searchInput.val() && '' !== $searchInput.val() ) {
							value = $searchInput.val().toLowerCase();

							if ( 3 > value.length ) {
								return;
							}

							$container.find( '.icon_preview' ).css( 'display', 'none' );
							options = {
								threshold: 0.2,
								location: 0,
								distance: 100,
								maxPatternLength: 32,
								minMatchCharLength: 3,
								keys: [
									'name',
									'keywords',
									'categories'
								]
							};
							fuse   = new Fuse( fusionIconSearch, options );
							result = fuse.search( value );

							// Show icons.
							_.each( result, function( resultIcon ) {
								$container.find( '.icon_preview.' + resultIcon.name ).css( 'display', 'inline-flex' );
							} );

							// Add attributes to iconset containers.
							_.each( $container.find( '.fusion-icon-set' ), function( subContainer ) {
								var hasSearchResults = false;
								subContainer.classList.add( 'no-search-results' );
								subContainer.querySelectorAll( '.icon_preview' ).forEach( function( icon ) {
									if ( 'none' !== icon.style.display && subContainer.classList.contains( 'no-search-results' ) ) {
										hasSearchResults = true;
									}
								} );

								if ( ! hasSearchResults && ! subContainer.querySelector( '.no-search-results-notice' ) ) {
									jQuery( subContainer ).append( '<div class="no-search-results-notice">' + fusionBuilderText.no_results_in.replace( '%s', jQuery( 'a[href="#' + subContainer.id + '"]' ).html() ) + '</div>' );
								} else if ( hasSearchResults ) {
									subContainer.classList.remove( 'no-search-results' );
								}
							} );
						} else {
							$container.find( '.icon_preview' ).css( 'display', 'inline-flex' );
							_.each( $container.find( '.fusion-icon-set' ), function( subContainer ) {
								subContainer.classList.remove( 'no-search-results' );
							} );
						}
					}, 100 );
				} );
			} );
		}
	}
};
;/* global fusionAppConfig, FusionApp, FusionEvents, fusionBuilderText */
/* jshint -W024, -W117 */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionImportUpload = {

	optionImport: function( $element ) {
		var self = this,
			$import,
			$importMode,
			$codeArea,
			$demoImport,
			$poImport,
			$fileUpload,
			context,
			$importButton,
			$deleteButton;

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$import  = $element.find( '.fusion-builder-option.import' );

		if ( $import.length ) {
			$importMode   = $import.find( '#fusion-import-mode' );
			$codeArea     = $import.find( '#import-code-value' );
			$demoImport   = $import.find( '#fusion-demo-import' );
			$poImport     = $import.find( '#fusion-page-options-import' );
			$fileUpload   = $import.find( '.fusion-import-file-input' );
			$importButton = $import.find( '.fusion-builder-import-button' );
			$deleteButton = $import.find( '.fusion-builder-delete-button' );
			context       = $importButton.attr( 'data-context' );

			$importMode.on( 'change', function( event ) {
				event.preventDefault();
				$import.find( '.fusion-import-options > div' ).hide();
				$import.find( '.fusion-import-options > div[data-id="' + jQuery( event.target ).val() + '"]' ).show();
				$deleteButton.hide();

				if ( 'saved-page-options' === jQuery( event.target ).val() ) {
					$deleteButton.show();
				}
			} );

			$importButton.on( 'click', function( event ) {
				var uploadMode = $importMode.val();

				if ( event ) {
					event.preventDefault();
				}

				if ( 'paste' === uploadMode ) {
					$import.addClass( 'partial-refresh-active' );
					self.importCode( $codeArea.val(), context, $import );
				} else if ( 'demo' === uploadMode ) {
					$import.addClass( 'partial-refresh-active' );
					self.ajaxUrlImport( $demoImport.val(), $import );
				} else if ( 'saved-page-options' === uploadMode ) {
					$import.addClass( 'partial-refresh-active' );
					self.ajaxPOImport( $poImport.val(), $import );
				} else {
					$fileUpload.trigger( 'click' );
				}
			} );

			$deleteButton.on( 'click', function( event ) {

				if ( event ) {
					event.preventDefault();
				}

				if ( '' !== $poImport.val() ) {
					$import.addClass( 'partial-refresh-active' );
					self.ajaxPODelete( $poImport.val(), $import );
				}

			} );

			$fileUpload.on( 'change', function( event ) {
				self.prepareUpload( event, context, self );
			} );
		}
	},

	colorSchemeImport: function( $target, $option ) {
		var themeOptions,
			optionId = $option.length ? $option.attr( 'data-option-id' ) : false;

		if ( 'object' === typeof this.options[ optionId ] && 'object' === typeof this.options[ optionId ].choices[ $target.attr( 'data-value' ) ] ) {
			$option.addClass( 'partial-refresh-active' );
			themeOptions = jQuery.extend( true, {}, FusionApp.settings, this.options[ optionId ].choices[ $target.attr( 'data-value' ) ].settings );
			this.importCode( themeOptions, 'TO', $option, true, this.options[ optionId ].choices[ $target.attr( 'data-value' ) ].settings );
		}
	},

	importCode: function( code, context, $import, valid, scheme ) {
		var newOptions = code;

		context = 'undefined' === typeof context ? 'TO' : context;
		valid   = 'undefined' === typeof valid ? false : valid;
		scheme  = 'undefined' === typeof scheme ? false : scheme;

		if ( ! code || '' === code ) {
			$import.removeClass( 'partial-refresh-active' );
			return;
		}

		if ( ! valid ) {
			newOptions = JSON.parse( newOptions );
		}

		if ( 'TO' === context ) {
			FusionApp.settings    = newOptions;
			FusionApp.storedToCSS = {};
			FusionApp.contentChange( 'global', 'theme-option' );
			FusionEvents.trigger( 'fusion-to-changed' );
			FusionApp.sidebarView.clearInactiveTabs( 'to' );
			this.updateValues( scheme );
		} else {
			FusionPageBuilder.options.fusionExport.setFusionMeta( newOptions );
			FusionApp.storedPoCSS   = {};
			FusionApp.contentChange( 'page', 'page-option' );
			FusionEvents.trigger( 'fusion-po-changed' );
			FusionApp.sidebarView.clearInactiveTabs( 'po' );
		}

		$import.removeClass( 'partial-refresh-active' );
		FusionApp.fullRefresh();
	},

	ajaxUrlImport: function( toUrl, $import ) {
		var self = this;

		jQuery.ajax( {
			type: 'POST',
			url: fusionAppConfig.ajaxurl,
			dataType: 'JSON',
			data: {
				action: 'fusion_panel_import',
				fusion_load_nonce: fusionAppConfig.fusion_load_nonce, // eslint-disable-line camelcase
				toUrl: toUrl
			}
		} )
			.done( function( response ) {
				self.importCode( response, 'TO', $import );
			} )
			.fail( function() {
				$import.removeClass( 'partial-refresh-active' );
			} );
	},

	ajaxPOImport: function( poID, $import ) {
		var self = this,
			data = {
				action: 'fusion_page_options_import_saved',
				fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
				post_id: FusionApp.data.postDetails.post_id,
				saved_po_dataset_id: poID
			};

		jQuery.get( {
			url: fusionAppConfig.ajaxurl,
			data: data,
			dataType: 'json'
		} )
			.done( function( response ) {
				self.importCode( JSON.stringify( response.custom_fields ), 'PO', $import );
			} )
			.fail( function() {
				$import.removeClass( 'partial-refresh-active' );
			} );
	},

	ajaxPODelete: function( poID, $import ) {
		var data = {
			action: 'fusion_page_options_delete',
			fusion_po_nonce: jQuery( '#fusion-page-options-nonce' ).val(),
			saved_po_dataset_id: poID
		};

		jQuery.get( {
			url: fusionAppConfig.ajaxurl,
			data: data
		} )
			.done( function() {
				$import.find( '.fusion-select-label[data-value="' +  poID + '"]' ).closest( '.fusion-select-label' ).remove();
				$import.find( '.fusion-select-preview' ).html( '' );
				$import.removeClass( 'partial-refresh-active' );

				jQuery.each( FusionApp.data.savedPageOptions, function( index, value )  {
					if ( poID === value.id ) {
						delete FusionApp.data.savedPageOptions[ index ];
						return false;
					}
				} );
			} )
			.fail( function() {
				$import.removeClass( 'partial-refresh-active' );
			} );
	},

	updateValues: function( scheme ) {
		var self = this,
			options = 'undefined' === typeof scheme ? FusionApp.settings : scheme;

		_.each( options, function( value, id ) {
			self.updateValue( id, value );
		} );
	},

	updateValue: function( id, value ) {
		if ( 'primary_color' === id && this.$el.find( 'input[name="primary_color"]' ).length ) {
			this.$el.find( 'input[name="primary_color"]' ).val( value );
			this.$el.find( '[data-option-id="primary_color"] .wp-color-result' ).css( { backgroundColor: value } );
		}

		FusionApp.createMapObjects();
		this.updateSettingsToParams( id, value, true );
		this.updateSettingsToExtras( id, value, true );
		this.updateSettingsToPo( id, value );
	},

	prepareUpload: function( event, context, self ) {
		var file        = event.target.files,
			data        = new FormData(),
			$import     = jQuery( event.target ).closest( '.fusion-builder-option.import' ),
			invalidFile = false;

		$import.addClass( 'partial-refresh-active' );

		data.append( 'action', 'fusion_panel_import' );
		data.append( 'fusion_load_nonce', fusionAppConfig.fusion_load_nonce );

		jQuery.each( file, function( key, value ) {
			if ( 'json' !== value.name.substr( value.name.lastIndexOf( '.' ) + 1 ) ) {
				invalidFile = true;
			} else {
				data.append( 'po_file_upload', value );
			}
		} );

		if ( invalidFile ) {
			FusionApp.confirmationPopup( {
				title: fusionBuilderText.import_failed,
				content: fusionBuilderText.import_failed_description,
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
			$import.removeClass( 'partial-refresh-active' );
			return;
		}

		jQuery.ajax( {
			url: fusionAppConfig.ajaxurl,
			type: 'POST',
			data: data,
			cache: false,
			dataType: 'json',
			processData: false, // Don't process the files
			contentType: false // Set content type to false as jQuery will tell the server its a query string request
		} )
			.done( function( response ) {
				self.importCode( response, context, $import );
			} );
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionLinkSelectorObject = {
	optionLinkSelectorObject: function( $element ) {
		var $linkSelector;
		$element      = $element || this.$el;
		$linkSelector = $element.find( '.fusion-link-selector-object' );

		$linkSelector.each( function() {
			var $thisOption       = jQuery( this ),
				$linkButton       = jQuery( this ).find( '.fusion-builder-link-button' ),
				$toggleButton     = jQuery( this ).find( '.button-link-type-toggle' ),
				$linkSubmit       = jQuery( '#wp-link-submit' ),
				$linkTitle        = jQuery( '.wp-link-text-field' ),
				$linkTarget       = jQuery( '.link-target' ),
				$fusionLinkSubmit = jQuery( '<input type="button" name="fusion-link-submit" id="fusion-link-submit" class="button-primary" value="Set Link">' ),
				wpLinkL10n        = window.wpLinkL10n,
				linkId            = jQuery( this ).find( '.fusion-builder-link-field' ).attr( 'id' ),
				$input,
				$linkDialog,
				linkUrl,
				$inputObject,
				$inputObjectId,
				$option,
				linkObject,
				linkObjectId,
				linkTitle;

			jQuery( $toggleButton ).on( 'click', function() {
				$thisOption.find( '.fusion-builder-link-field' ).removeAttr( 'readonly' );
				$thisOption.find( '.fusion-builder-object-field' ).val( 'custom' );
				$thisOption.find( '.fusion-builder-menu-item-type' ).text( 'custom' );
				$thisOption.find( '.fusion-builder-object-id-field' ).val( 0 );
				$thisOption.find( '.fusion-builder-link-field' ).removeAttr( 'readonly' );
				jQuery( this ).hide();
			} );

			jQuery( $linkButton ).on( 'click', function( event ) {
				$fusionLinkSubmit.insertBefore( $linkSubmit );
				$option = jQuery( event.target ).closest( ' .fusion-link-selector-object' );

				// The 3 inputs.
				$input           = $option.find( '.fusion-builder-link-field' );
				$inputObject     = $option.find( '.fusion-builder-object-field' );
				$inputObjectId   = $option.find( '.fusion-builder-object-id-field' );

				linkUrl  = $input.val();
				$linkSubmit.hide();
				$linkTitle.hide();
				$linkTarget.hide();
				$fusionLinkSubmit.show();

				$linkDialog = ! window.wpLink && jQuery.fn.wpdialog && jQuery( '#wp-link' ).length ? {
					$link: ! 1,
					open: function() {
						this.$link = jQuery( '#wp-link' ).wpdialog( {
							title: wpLinkL10n.title,
							width: 480,
							height: 'auto',
							modal: ! 0,
							dialogClass: 'wp-dialog',
							zIndex: 3e5
						} );

					},
					close: function() {
						this.$link.wpdialog( 'close' );
					}
				} : window.wpLink;

				$linkDialog.fusionUpdateLink = function( scopedEvent, $scopedFusionLinkSubmit ) {
					scopedEvent.preventDefault();
					scopedEvent.stopImmediatePropagation();
					scopedEvent.stopPropagation();

					linkUrl = jQuery( '#wp-link-url' ).length ? jQuery( '#wp-link-url' ).val() : jQuery( '#url-field' ).val();
					linkObject = 'custom';
					linkObjectId = 0;

					if ( jQuery( 'span[data-permalink="' + linkUrl + '"]' ).length ) {
						linkObject = jQuery( 'span[data-permalink="' + linkUrl + '"]' ).data( 'object' );
						linkObjectId = jQuery( 'span[data-permalink="' + linkUrl + '"]' ).data( 'id' );
						$input.attr( 'readonly', true );
						$option.find( '.button-link-type-toggle' ).show();

						// Update the title input.
						linkTitle = jQuery( 'span[data-permalink="' + linkUrl + '"]' ).closest( 'li' ).find( '.item-title' ).text();
						jQuery( '[data-save-id="title"] input' ).val( linkTitle ).trigger( 'change' );
					}

					// Update all 3 inputs.
					$input.val( linkUrl ).trigger( 'change' );
					$inputObject.val( linkObject ).trigger( 'change' );
					$inputObjectId.val( linkObjectId ).trigger( 'change' );

					// Update text of object type.
					$option.find( '.fusion-builder-menu-item-type' ).text( linkObject );

					$linkSubmit.show();
					$linkTitle.show();
					$linkTarget.show();
					$scopedFusionLinkSubmit.remove();
					jQuery( '#wp-link-cancel' ).unbind( 'click' );
					$linkDialog.close();
					window.wpLink.textarea = '';
				},

				$linkDialog.open( linkId );

				jQuery( '#link-options, #wplink-link-existing-content' ).hide();
				jQuery( '#wp-link-wrap' ).addClass( 'fusion-object-link-selector' );
				jQuery( '#wp-link-url' ).val( linkUrl );
				jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
				if ( jQuery( 'span[data-permalink="' + linkUrl + '"]' ).length ) {
					jQuery( 'span[data-permalink="' + linkUrl + '"]' ).closest( 'li' ).addClass( 'selected' );
				}

				jQuery( document ).on( 'click', '#fusion-link-submit', function( scopedEvent ) {
					$linkDialog.fusionUpdateLink( scopedEvent, jQuery( this ) );
				} );
			} );

			jQuery( document ).on( 'click', '#search-panel li', function() {
				jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
				jQuery( this ).addClass( 'selected' );
			} );

			jQuery( document ).on( 'click', '#wp-link-cancel, #wp-link-close, #wp-link-backdrop', function() {
				$linkSubmit.show();
				$linkTitle.show();
				$linkTarget.show();
				$fusionLinkSubmit.remove();
			} );
		} );
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionLinkSelector = {
	optionLinkSelector: function( $element ) {
		var $linkSelector;
		$element      = $element || this.$el;
		$linkSelector = $element.find( '.fusion-link-selector' );

		if ( $linkSelector.length ) {

			$linkSelector.each( function() {
				var $linkButton       = jQuery( this ).find( '.fusion-builder-link-button' ),
					$linkSubmit       = jQuery( '#wp-link-submit' ),
					$linkTitle        = jQuery( '.wp-link-text-field' ),
					$linkTarget       = jQuery( '.link-target' ),
					$fusionLinkSubmit = jQuery( '<input type="button" name="fusion-link-submit" id="fusion-link-submit" class="button-primary" value="Set Link">' ),
					wpLinkL10n        = window.wpLinkL10n,
					$inputField       = jQuery( this ).find( '.fusion-builder-link-field' ),
					linkId            = $inputField.attr( 'id' ),
					$input,
					$linkDialog,
					linkUrl,
					$option;

				jQuery( $linkButton ).on( 'click', function( event ) {
					if ( 'fusion-link-submit' !== $linkSubmit.prev().attr( 'id' ) ) {
						$fusionLinkSubmit.insertBefore( $linkSubmit );
					}
					$option = jQuery( event.target ).closest( ' .fusion-link-selector' );
					$input  = $option.find( '.fusion-builder-link-field' );
					linkUrl = $input.val();

					$linkSubmit.hide();
					$linkTitle.hide();
					$linkTarget.hide();
					$fusionLinkSubmit.show();

					if ( 'fusion-anchor-href' === linkId ) {
						jQuery( 'body' ).append( $inputField.clone( true ).css( { display: 'none' } ) );
					}

					$linkDialog = ! window.wpLink && jQuery.fn.wpdialog && jQuery( '#wp-link' ).length ? {
						$link: ! 1,
						open: function() {
							this.$link = jQuery( '#wp-link' ).wpdialog( {
								title: wpLinkL10n.title,
								width: 480,
								height: 'auto',
								modal: ! 0,
								dialogClass: 'wp-dialog',
								zIndex: 3e5
							} );

						},
						close: function() {
							this.$link.wpdialog( 'close' );
						}
					} : window.wpLink;

					$linkDialog.fusionUpdateLink = function( scopedEvent, $scopedFusionLinkSubmit ) {
						scopedEvent.preventDefault();
						scopedEvent.stopImmediatePropagation();
						scopedEvent.stopPropagation();

						linkUrl = jQuery( '#wp-link-url' ).length ? jQuery( '#wp-link-url' ).val() : jQuery( '#url-field' ).val();

						// Update single input.
						$input.val( linkUrl ).trigger( 'change' );

						// Listener in vanilla JS so need different event.
						if ( -1 !== linkId.indexOf( 'fusion-anchor-href' ) && $input.length ) {
							$input[ 0 ].dispatchEvent( new Event( 'change' ) );
						}

						$linkSubmit.show();
						$linkTitle.show();
						$linkTarget.show();
						$scopedFusionLinkSubmit.remove();
						jQuery( '#wp-link-cancel' ).unbind( 'click' );
						$linkDialog.close();
						window.wpLink.textarea = '';
					},

					$linkDialog.open( linkId );

					// jQuery( '#link-options, #wplink-link-existing-content' ).hide();
					jQuery( '#wp-link-wrap' ).addClass( 'fusion-object-link-selector' );
					jQuery( '#wp-link-url' ).val( linkUrl );
					jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
					if ( jQuery( 'span[data-permalink="' + linkUrl + '"]' ).length ) {
						jQuery( 'span[data-permalink="' + linkUrl + '"]' ).closest( 'li' ).addClass( 'selected' );
					}

					jQuery( document ).on( 'click', '#fusion-link-submit', function( scopedEvent ) {
						$linkDialog.fusionUpdateLink( scopedEvent, jQuery( this ) );
						if ( -1 !== linkId.indexOf( 'fusion-anchor-href' ) && jQuery( '#' + linkId ).length ) {
							jQuery( '#' + linkId ).remove();
						}
					} );
				} );

				jQuery( document ).on( 'click', '#search-panel li', function() {
					jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
					jQuery( this ).addClass( 'selected' );
				} );

				jQuery( document ).on( 'click', '#wp-link-cancel, #wp-link-close, #wp-link-backdrop', function() {
					$linkSubmit.show();
					$linkTitle.show();
					$linkTarget.show();
					$fusionLinkSubmit.remove();

					if ( -1 !== linkId.indexOf( 'fusion-anchor-href' ) && jQuery( '#' + linkId ).length ) {
						jQuery( '#' + linkId ).remove();
					}
				} );
			} );

		}
	}
};
;/* global includesURL, fusionAllElements, FusionEvents, FusionPageBuilderViewManager, fusionBuilderText, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionOptionUpload = {
	removeImage: function( event ) {
		var $field,
			$upload;

		if ( event ) {
			event.preventDefault();
		}

		$field   = jQuery( event.currentTarget ).closest( '.fusion-builder-option-container' ).find( '.fusion-builder-upload-field' );
		$upload  = jQuery( event.currentTarget ).closest( '.fusion-builder-option-container' ).find( '.fusion-builder-upload-button' );

		if ( $field.hasClass( 'fusion-image-as-object' ) ) {
			$field.val( JSON.stringify( { id: '', url: '', width: '', height: '', thumbnail: '' } ) ).trigger( 'change' );
		} else {
			$field.val( '' ).trigger( 'change' );
		}

		$upload.closest( '.fusion-upload-area' ).removeClass( 'fusion-uploaded-image' );

		if ( jQuery( event.target ).closest( '.fusion-builder-module-settings' ).find( '#image_id' ).length ) {
			jQuery( event.target ).closest( '.fusion-builder-module-settings' ).find( '#image_id' ).val( '' ).trigger( 'change' );
		}

		// Url instead of image preview, clear it.
		if ( jQuery( event.currentTarget ).closest( '.fusion-builder-option-container' ).find( '.fusion-url-only-input' ).length ) {
			jQuery( event.currentTarget ).closest( '.fusion-builder-option-container' ).find( '.fusion-url-only-input' ).val( '' );
		}
		FusionEvents.trigger( 'awb-image-upload-url-' + $upload.data( 'param' ), '' );
	},

	optionUpload: function( $element ) {
		var self = this,
			$uploadButton;

		$element      = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$uploadButton = $element.find( '.fusion-builder-upload-button:not(.fusion-builder-upload-button-multiple-upload):not(.fusion-builder-upload-button-upload-images)' );

		if ( $uploadButton.length ) {
			$uploadButton.on( 'click', function( event ) {

				var fileFrame,
					$thisEl     = jQuery( this ),
					frameOptions = { // eslint-disable-line camelcase
						title: $thisEl.data( 'title' ),
						multiple: false,
						frame: 'post',
						className: 'media-frame mode-select fusion-builder-media-dialog wp-admin ' + $thisEl.data( 'id' ),
						displayUserSettings: false,
						displaySettings: true,
						allowLocalEdits: true
					};

				if ( event ) {
					event.preventDefault();
				}

				// If data-type is passed on, us that for library type.
				if ( $thisEl.data( 'type' ) ) {
					frameOptions.library = {
						type: $thisEl.data( 'type' )
					};
				}

				fileFrame                  = wp.media( frameOptions );
				wp.media.frames.file_frame = wp.media( frameOptions );

				// For attachment uploads, we need the post ID.
				if ( $thisEl.hasClass( 'fusion-builder-attachment-upload' ) ) {
					wp.media.model.settings.post.id = FusionPageBuilderApp.postID;
				}

				// Select currently active image automatically.
				fileFrame.on( 'open', function() {
					var selection = fileFrame.state().get( 'selection' ),
						library   = fileFrame.state().get( 'library' ),
						optionID  = $thisEl.parents( '.fusion-builder-option.upload' ).data( 'option-id' ),
						imageID   = $thisEl.closest( '.fusion-builder-module-settings' ).find( '#image_id' ).val(),
						id        = '',
						attachment,
						parsedObject;

					id = $thisEl.parents( '.fusion-builder-module-settings' ).find( '#' + optionID + '_id' ).val();
					id = ( 'undefined' !== typeof id ? id : imageID );

					jQuery( '.fusion-builder-media-dialog' ).addClass( 'hide-menu' );

					// Checking for different option types, see if we can fetch an ID.
					if ( ! id ) {
						if ( $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).hasClass( 'fusion-image-as-object' ) ) {
							parsedObject = $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).val();
							if ( parsedObject && 'string' === typeof parsedObject ) {
								parsedObject = JSON.parse( parsedObject );
								if ( parsedObject && 'object' === typeof parsedObject && 'undefined' !== typeof parsedObject.id ) {
									id = parsedObject.id;
								}
							}
						} else if ( $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).hasClass( 'fusion-builder-upload-field-id' ) ) {
							id = $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).val();
						}
					}

					// We have an id, use it for initial selection.
					if ( id ) {

						if ( -1 !== id.indexOf( '|' ) ) {
							id = id.split( '|' )[ 0 ];
						}

						// This ensures selection images remains first.
						library.comparator = function( a, b ) {
							var aInQuery = !! this.mirroring.get( a.cid ),
								bInQuery = !! this.mirroring.get( b.cid );

							if ( ! aInQuery && bInQuery ) {
								return -1;
							}
							if ( aInQuery && ! bInQuery ) {
								return 1;
							}
							return 0;
						};

						if ( jQuery.isNumeric( id ) ) {

							// Sets the selection and places first (only happens on first fetch)/
							attachment = wp.media.attachment( id );
							attachment.fetch( {
								success: function( att ) {
									library.add( att ? [ att ] : [] );
									selection.add( att ? [ att ] : [] );
								}
							} );
						}
					}
				} );

				fileFrame.on( 'select insert', function() {

					var imageURL,
						imageID,
						imageSize,
						state = fileFrame.state(),
						imageHeight,
						imageWidth,
						imageObject,
						imageIDField,
						optionName = $thisEl.parents( '.fusion-builder-option' ).data( 'option-id' );

					if ( 'undefined' === typeof state.get( 'selection' ) ) {
						imageURL = jQuery( fileFrame.$el ).find( '#embed-url-field' ).val();
					} else {

						state.get( 'selection' ).map( function( attachment ) {
							var element = attachment.toJSON(),
								display = state.display( attachment ).toJSON();

							imageID = element.id;
							imageSize = display.size;
							if ( element.sizes && element.sizes[ display.size ] && element.sizes[ display.size ].url ) {
								imageURL    = element.sizes[ display.size ].url;
								imageHeight = element.sizes[ display.size ].height;
								imageWidth  = element.sizes[ display.size ].width;
							} else if ( element.url ) {
								imageURL    = element.url;
								imageHeight = element.height;
								imageWidth  = element.width;
							}
							return attachment;
						} );
					}

					if ( $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).hasClass( 'fusion-image-as-object' ) ) {

						imageObject = {
							id: imageID,
							url: imageURL,
							width: imageWidth,
							height: imageHeight,
							thumbnail: ''
						};

						// Input instead of image preview, just update input value.
						if ( $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-url-only-input' ).length ) {
							$thisEl.closest( '.fusion-upload-area' ).find( '.fusion-url-only-input' ).val( imageURL );
						}
						$thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).val( JSON.stringify( imageObject ) ).trigger( 'change' );
					} else if ( $thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).hasClass( 'fusion-builder-upload-field-id' ) ) {
						$thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).data( 'url', imageURL );
						$thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).val( imageID ).trigger( 'change' );
					} else {
						$thisEl.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).val( imageURL ).trigger( 'change' );
					}

					// Set image id.
					imageIDField = $thisEl.closest( '.fusion-builder-option' ).next().find( '#' + optionName + '_id' );

					if ( 'element_content' === optionName ) {
						imageIDField = $thisEl.closest( '.fusion-builder-option' ).next().find( '#image_id' );
					}

					if ( imageIDField.length ) {
						imageIDField.val( imageID + '|' + imageSize ).trigger( 'change' );
					}

					self.fusionBuilderImagePreview( $thisEl );

				} );

				fileFrame.open();

				return false;
			} );

			$uploadButton.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).on( 'input', function() {
				self.fusionBuilderImagePreview( jQuery( this ).closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-button' ) );
			} );

			$uploadButton.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).each( function() {
				self.fusionBuilderImagePreview( jQuery( this ).closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-button' ) );
			} );
		}
	},

	optionMultiUpload: function( $element ) {
		var self = this,
			$uploadButton;

		$element      = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$uploadButton = $element.find( '.fusion-builder-upload-button.fusion-builder-upload-button-multiple-upload, .fusion-builder-upload-button.fusion-builder-upload-button-upload-images' );

		if ( $uploadButton.length ) {
			$uploadButton.on( 'click', this.openMultipleMedia );

			$uploadButton.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).on( 'input', function() {
				self.fusionBuilderImagePreview( jQuery( this ).closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-button' ) );
			} );

			$uploadButton.closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-field' ).each( function() {
				self.fusionBuilderImagePreview( jQuery( this ).closest( '.fusion-upload-area' ).find( '.fusion-builder-upload-button' ) );
			} );

			jQuery( $element ).on( 'click', '.fusion-multi-image-remove', function() {
				var input = jQuery( this ).closest( '.fusion-multiple-upload-images' ).find( '.fusion-multi-image-input' ),
					imageIDs,
					imageID,
					imageIndex;

				imageID = jQuery( this ).parent( '.fusion-multi-image' ).data( 'image-id' );
				imageIDs = input.val().split( ',' ).map( function( v ) {
					return parseInt( v, 10 );
				} );
				imageIndex = imageIDs.indexOf( imageID );
				if ( -1 !== imageIndex ) {
					imageIDs.splice( imageIndex, 1 );
				}
				imageIDs = imageIDs.join( ',' );
				input.val( imageIDs ).trigger( 'change' );
				jQuery( this ).parent( '.fusion-multi-image' ).remove();
			} );

		}
	},

	openMultipleMedia: function( event ) {

		var $thisEl,
			fileFrame,
			multiImageContainer,
			multiImageInput,
			multiUpload    = false,
			multiImages    = false,
			multiImageHtml = '',
			ids            = '',
			attachment     = '',
			attachments    = [];

		if ( event ) {
			event.preventDefault();
		}

		$thisEl = jQuery( this );

		// If its a multi upload element, clone default params.
		if ( 'fusion-multiple-upload' === $thisEl.data( 'id' ) ) {
			multiUpload = true;
		}

		if ( 'fusion-multiple-images' === $thisEl.data( 'id' ) ) {
			multiImages = true;
			multiImageContainer = jQuery( $thisEl.next( '.fusion-multiple-image-container' ) )[ 0 ];
			multiImageInput = jQuery( $thisEl ).prev( '.fusion-multi-image-input' );
		}

		fileFrame = wp.media( { // eslint-disable-line camelcase
			library: {
				type: $thisEl.data( 'type' )
			},
			title: $thisEl.data( 'title' ),
			multiple: 'between',
			frame: 'post',
			className: 'media-frame mode-select fusion-builder-media-dialog wp-admin ' + $thisEl.data( 'id' ),
			displayUserSettings: false,
			displaySettings: true,
			allowLocalEdits: true
		} );
		wp.media.frames.file_frame = fileFrame;

		// Set the media dialog box state as 'gallery' if the element is gallery.
		if ( multiImages && 'fusion_gallery' === $thisEl.data( 'element' ) ) {
			ids         = multiImageInput.val().split( ',' );
			attachments = [];
			attachment  = '';

			jQuery.each( ids, function( index, id ) {
				if ( '' !== id && 'NaN' !== id ) {
					attachment = wp.media.attachment( id );
					attachment.fetch();
					attachments.push( attachment );
				}
			} );

			wp.media._galleryDefaults.link  = 'none';
			wp.media._galleryDefaults.size  = 'thumbnail';
			fileFrame.options.syncSelection = true;

			fileFrame.options.state = ( attachments.length ) ? 'gallery-edit' : 'gallery';
		}

		// Select currently active image automatically.
		fileFrame.on( 'open', function() {
			var selection = fileFrame.state().get( 'selection' ),
				library   = fileFrame.state().get( 'library' );

			if ( multiImages ) {
				if ( 'fusion_gallery' !== $thisEl.data( 'element' ) || 'gallery-edit' !== fileFrame.options.state ) {
					jQuery( '.fusion-builder-media-dialog' ).addClass( 'hide-menu' );
				}
				selection.add( attachments );
				library.add( attachments );
			} else {
				jQuery( '.fusion-builder-media-dialog' ).addClass( 'hide-menu' );
			}
		} );

		// Set the attachment ids from gallery selection if the element is gallery.
		if ( multiImages && 'fusion_gallery' === $thisEl.data( 'element' ) ) {
			fileFrame.on( 'update', function( selection ) {
				var imageIDs = '',
					imageURL = '';

				imageIDs = selection.map( function( scopedAttachment ) {
					var imageID = scopedAttachment.id;

					if ( scopedAttachment.attributes.sizes && 'undefined' !== typeof scopedAttachment.attributes.sizes.thumbnail ) {
						imageURL = scopedAttachment.attributes.sizes.thumbnail.url;
					} else if ( scopedAttachment.attributes.url ) {
						imageURL = scopedAttachment.attributes.url;
					}

					if ( multiImages ) {
						multiImageHtml += '<div class="fusion-multi-image" data-image-id="' + imageID + '">';
						multiImageHtml += '<img src="' + imageURL + '"/>';
						multiImageHtml += '<span class="fusion-multi-image-remove dashicons dashicons-no-alt"></span>';
						multiImageHtml += '</div>';
					}
					return scopedAttachment.id;
				} );

				multiImageInput.val( imageIDs );
				jQuery( multiImageContainer ).html( multiImageHtml );
				jQuery( multiImageContainer ).trigger( 'change' );
				multiImageInput.trigger( 'change' );
			} );
		}

		fileFrame.on( 'select insert', function() {

			var imageURL,
				imageID,
				imageIDs,
				state = fileFrame.state(),
				firstElementNode,
				firstElement,
				elementCid;

			if ( 'undefined' === typeof state.get( 'selection' ) ) {
				imageURL = jQuery( fileFrame.$el ).find( '#embed-url-field' ).val();
			} else {

				imageIDs = state.get( 'selection' ).map( function( scopedAttachment ) {
					return scopedAttachment.id;
				} );

				// If its a multi image element, add the images container and IDs to input field.
				if ( multiImages ) {
					multiImageInput.val( imageIDs );
				}

				// Remove default item.
				if ( multiUpload ) {
					firstElementNode = $thisEl.closest( '.fusion-builder-main-settings' ).find( '.fusion-builder-sortable-options, .fusion-builder-sortable-children' ).find( 'li:first-child' );

					if ( firstElementNode.length ) {
						firstElement = FusionPageBuilderViewManager.getView( firstElementNode.data( 'cid' ) );

						if ( firstElement && ( 'undefined' === typeof firstElement.model.attributes.params.image || '' === firstElement.model.attributes.params.image ) ) {
							firstElementNode.find( '.fusion-builder-multi-setting-remove' ).trigger( 'click' );
						}
					}
				}

				state.get( 'selection' ).map( function( scopedAttachment ) {
					var element = scopedAttachment.toJSON(),
						display = state.display( scopedAttachment ).toJSON(),
						elementType,
						param,
						child,
						params,
						createChildren,
						defaultParams;

					imageID = element.id;
					if ( element.sizes && element.sizes[ display.size ] && element.sizes[ display.size ].url ) {
						imageURL    = element.sizes[ display.size ].url;
					} else if ( element.url ) {
						imageURL    = element.url;
					}

					if ( multiImages ) {
						multiImageHtml += '<div class="fusion-multi-image" data-image-id="' + imageID + '">';
						multiImageHtml += '<img src="' + imageURL + '"/>';
						multiImageHtml += '<span class="fusion-multi-image-remove dashicons dashicons-no-alt"></span>';
						multiImageHtml += '</div>';
					}

					// If its a multi upload element, add the image to defaults and trigger a new item to be added.
					if ( multiUpload ) {

						elementType    = $thisEl.closest( '.fusion-builder-module-settings' ).data( 'element' );
						param          = $thisEl.closest( '.fusion-builder-option' ).data( 'option-id' );
						child          = fusionAllElements[ elementType ].element_child;
						params         = fusionAllElements[ elementType ].params[ param ].child_params;
						createChildren = 'undefined' !== typeof fusionAllElements[ elementType ].params[ param ].create_children ? fusionAllElements[ elementType ].params[ param ].create_children : true;
						defaultParams  = {};

						// Save default values
						_.each( params, function( name, scopedParam ) {
							defaultParams[ scopedParam ] = fusionAllElements[ child ].params[ scopedParam ].value;
						} );

						// Set new default values
						_.each( params, function( name, scopedParam ) {
							fusionAllElements[ child ].params[ scopedParam ].value = scopedAttachment.attributes[ name ];
						} );

						if ( createChildren ) {

							// Create children
							$thisEl.closest( '.fusion-builder-main-settings' ).find( '.fusion-builder-add-multi-child' ).trigger( 'click' );
							FusionEvents.trigger( 'fusion-multi-child-update-preview' );
						}

						// Restore default values
						_.each( defaultParams, function( defaultValue, scopedParam ) {
							fusionAllElements[ child ].params[ scopedParam ].value = defaultValue;
						} );
					}
					return scopedAttachment;
				} );

				$thisEl.trigger( 'change' );

				if ( multiImages ) {
					multiImageInput.trigger( 'change' );
				}

				// Triger reRender on front-end view.
				if ( multiUpload ) {
					elementCid = $thisEl.closest( '.fusion-builder-module-settings' ).data( 'element-cid' );
					if ( 'undefined' !== typeof elementCid ) {
						FusionEvents.trigger( 'fusion-view-update-' + elementCid );
						FusionEvents.trigger( 'fusion-child-changed' );
					}
				}
			}

			jQuery( multiImageContainer ).html( multiImageHtml );
		} );

		fileFrame.open();

		return false;
	},

	fusionBuilderImagePreview: function( $uploadButton ) {
		var uploadArea   = $uploadButton.closest( '.fusion-upload-area' ),
			$uploadField = uploadArea.find( '.fusion-builder-upload-field' ),
			$preview     = $uploadField.siblings( '.fusion-builder-upload-preview' ),
			$removeBtn   = $uploadButton.siblings( '.upload-image-remove' ),
			imageFormats = [ 'gif', 'jpg', 'jpeg', 'png', 'tiff' ],
			imagePreview,
			imageIDField,
			fileType,
			attachment,
			imageURL,
			value;

		if ( $uploadField.length ) {

			// JSON.parse fails when value is empty.
			if ( '' === $uploadField.val() ) {
				value = '';
			} else {
				value = $uploadField.hasClass( 'fusion-image-as-object' ) ? JSON.parse( $uploadField.val() ) : $uploadField.val().trim();

				if ( null === value ) {
					value = '';
				}
			}

			imageURL = $uploadField.hasClass( 'fusion-image-as-object' ) && value && 'undefined' !== typeof value.url ? value.url : value;
		} else {

			// Exit if no image set.
			return;
		}

		// If its not an image we are uploading, then we don't want preview.
		if ( 'file' === uploadArea.data( 'mode' ) ) {
			return;
		}

		// Image ID is saved.
		if ( imageURL && $uploadField.hasClass( 'fusion-builder-upload-field-id' ) ) {

			if ( 'undefined' === typeof $uploadField.data( 'url' ) ) {
				attachment = wp.media.attachment( imageURL );

				attachment.fetch().then( function() {

					// On frame load we need to fetch image URL for preview.
					imageURL = 'undefined' !== typeof attachment.attributes.sizes.medium ? attachment.attributes.sizes.medium.url : attachment.attributes.sizes.full.url;
					imagePreview = '<img src="' + imageURL + '" />';
					$preview.find( 'img' ).replaceWith( imagePreview );
					uploadArea.addClass( 'fusion-uploaded-image' );
				} );

				return;
			}

			// Image was already changed, so we have URL set as data attribute.
			imageURL = $uploadField.data( 'url' );
		}

		// Trigger event with Image URL.
		FusionEvents.trigger( 'awb-image-upload-url-' + $uploadButton.data( 'param' ), imageURL );


		if ( 0 <= imageURL.indexOf( '<img' ) ) {
			imagePreview = imageURL;
		} else {
			fileType = imageURL.slice( ( imageURL.lastIndexOf( '.' ) - 1 >>> 0 ) + 2 ); // eslint-disable-line no-bitwise
			imagePreview = '<img src="' + imageURL + '" />';

			if ( ! _.isEmpty( fileType ) ) {
				if ( ! jQuery.inArray( fileType.toLowerCase(), imageFormats ) ) {
					imagePreview = '<img src="' + includesURL + '/images/media/default.png" class="icon" draggable="false" alt="">';
				}
			}
		}

		if ( 'image' !== $uploadButton.data( 'type' ) ) {
			return;
		}

		if ( $uploadButton.hasClass( 'hide-edit-buttons' ) ) {
			return;
		}

		if ( '' === imageURL ) {
			if ( $preview.length ) {
				$preview.find( 'img' ).attr( 'src', '' );
				$removeBtn.remove();
			}

			// Remove image ID if image preview is empty.
			imageIDField = $uploadButton.closest( '.fusion-builder-module-settings' ).find( '#' + $uploadButton.data( 'param' ) + '_id' );

			if ( 'element_content' === $uploadButton.data( 'param' ) ) {
				imageIDField = $uploadButton.closest( '.fusion-builder-module-settings' ).find( '#image_id' );
			}

			if ( imageIDField.length ) {
				imageIDField.val( '' ).trigger( 'change' );
			}

			return;
		}

		if ( ! $preview.length ) {
			$uploadButton.after( '<div class="fusion-uploaded-area fusion-builder-upload-preview"><img src="" alt=""><ul class="fusion-uploded-image-options"><li><a class="upload-image-remove" href="JavaScript:void(0);">' + fusionBuilderText.remove + '</a></li><li><a class="fusion-builder-upload-button fusion-upload-btn" href="JavaScript:void(0);" data-type="image">' + fusionBuilderText.edit + '</a></li></ul></div>' );
			$preview = $uploadField.siblings( '.fusion-builder-upload-preview' );
		}

		$preview.find( 'img' ).replaceWith( imagePreview );
		$preview.closest( '.fusion-upload-area' ).addClass( 'fusion-uploaded-image' );

	}
};
;/* global FusionEvents, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionImageFocusPoint = {
	optionFocusImage: function( el ) {
		var points = el.find( '.fusion-image-focus-point' );
		var model = this.model;

		points.each( function() {
			var point 	= jQuery( this ).find( '.point' );
			var field 	= jQuery( this ).find( 'input.fusion-builder-focus-point-field' );
			var preview = jQuery( this ).find( '.preview' );
			var previewImg = preview.find( '.image' );
			var placeHolder = preview.find( '.no-image-holder' );
			var paramName	= previewImg.data( 'image' );
			var image 	= el.find( `[data-option-id="${paramName}"]` ).find( '.fusion-builder-upload-preview img' );
			var imageValue = model.attributes.params[ paramName ];
			var dynamicData = model.attributes.params.dynamic_params;
			var lazy = jQuery( this ).data( 'lazy' );

			if ( dynamicData ) {
				dynamicData = FusionPageBuilderApp.base64Decode( dynamicData );
			}
			if ( dynamicData && '' !== dynamicData[ paramName ] ) {
				imageValue = false;
			}
			if ( imageValue ) {
				placeHolder.hide();
				previewImg.show();
				previewImg.append( image.clone() );
			} else {
				previewImg.hide();
				placeHolder.show();
			}
			FusionEvents.on( 'awb-image-upload-url-' + paramName, function( url ) {
				if ( url ) {
					image 	= '<img src="' + url + '" alt="">';
					previewImg.find( 'img' ).remove();
					previewImg.append( image );
					previewImg.show();
					placeHolder.hide();
				} else {
					previewImg.find( 'img' ).remove();
					previewImg.hide();
					placeHolder.show();
				}
			} );

			point.draggable( {
				containment: 'parent',
				scroll: false,
				snap: '.position-point',
				snapMode: 'inner',
				snapTolerance: 10,
				drag: function () {
					var top = parseInt( 100 * parseFloat( jQuery( this ).css( 'top' ) ) / parseFloat( jQuery( this ).parent().css( 'height' ) ) );
					var left = parseInt( 100 * parseFloat( jQuery( this ).css( 'left' ) ) / parseFloat( jQuery( this ).parent().css( 'width' ) ) );

					if ( !lazy ) {
						field.val( `${left}% ${top}%` ).trigger( 'change' );
					}

				},
				stop: function () {
					var top = parseInt( 100 * parseFloat( jQuery( this ).css( 'top' ) ) / parseFloat( jQuery( this ).parent().css( 'height' ) ) );
					var left = parseInt( 100 * parseFloat( jQuery( this ).css( 'left' ) ) / parseFloat( jQuery( this ).parent().css( 'width' ) ) );
					field.val( `${left}% ${top}%` ).trigger( 'change' );
				}
			} );

			const $defaultReset = point.closest( '.fusion-builder-option' ).find( '.fusion-builder-default-reset' );

			// Default reset icon, set value to empty.
			$defaultReset.on( 'click', function( event ) {
				var dataDefault,
					top = '50%',
					left = '50%';

				event.preventDefault();
				dataDefault = jQuery( this ).find( '.fusion-range-default' ).attr( 'data-default' ) || '';

				if ( dataDefault && 'string' === typeof dataDefault ) {
					top = dataDefault.split( ' ' )[ 1 ];
					left = dataDefault.split( ' ' )[ 0 ];
				}
				point.css( {
					top,
					left
				} );
				field.val( dataDefault ).trigger( 'change' );

			} );

			jQuery( '.position-point' ).on( 'click', function( event ) {
				var top = '50%',
					left = '50%';
				event.preventDefault();

				const $el = jQuery( this );
				if ( $el.hasClass( 'top-left' ) ) {
					top = 0;
					left = 0;
				}
				if ( $el.hasClass( 'top-center' ) ) {
					top = 0;
					left = '50%';
				}
				if ( $el.hasClass( 'top-right' ) ) {
					top = 0;
					left = '100%';
				}
				if ( $el.hasClass( 'center-left' ) ) {
					top = '50%';
					left = 0;
				}
				if ( $el.hasClass( 'center-center' ) ) {
					top = '50%';
					left = '50%';
				}
				if ( el.hasClass( 'center-right' ) ) {
					top = '50%';
					left = '100%';
				}
				if ( $el.hasClass( 'bottom-left' ) ) {
					top = '100%';
					left = 0;
				}
				if ( $el.hasClass( 'bottom-center' ) ) {
					top = '100%';
					left = '50%';
				}
				if ( $el.hasClass( 'bottom-right' ) ) {
					top = '100%';
					left = '100%';
				}
				point.css( {
					top,
					left
				} );
				field.val( `${left} ${top}` ).trigger( 'change' );

			} );
		} );


	}
};
;/* global FusionApp, fusionAllElements, fusionMailchimpMapOption, fusionHubSpotMapOption */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionToggleField = {

	optionToggle: function( context ) {
		var $toggle = this.$el.find( '.fusion-builder-option.toggle' ),
			self      = this;

		// Set context to overall view for easier access.
		this.context = context;

		this.repeaterRowId = 'undefined' === typeof this.repeaterRowId ? 0 : this.repeaterRowId;

		if ( $toggle.length ) {
			$toggle.each( function() {
				self.initToggle( jQuery( this ), context );
			} );
		}
	},

	/**
	 * Init the option.
	 *
	 * @since 2.0.0
	 * @param {Object} $toggle - jQuery object of the DOM element.
	 * @return {void}
	 */
	initToggle: function( $toggle ) {
		var self       = this,
			param      = $toggle.data( 'option-id' ),
			$target    = $toggle.find( '.toggle-wrapper' ),
			option,
			values,
			params,
			attributes,
			fields;


		switch ( this.context ) {

		case 'TO':
		case 'FBE':

			option   = this.options[ param ];
			fields   = option.fields;
			values   = FusionApp.settings;

			break;

		case 'PO':

			option   = this.options[ param ];
			fields   = option.fields;
			values   = FusionApp.data.postMeta._fusion;

			break;

		default:

			option     = fusionAllElements[ this.model.get( 'element_type' ) ].params[ param ];
			fields     = 'undefined' !== typeof option ? option.fields : {};
			attributes = jQuery.extend( true, {}, this.model.attributes );

			if ( 'function' === typeof this.filterAttributes ) {
				attributes = this.filterAttributes( attributes );
			}

			params     = attributes.params;
			values     = 'undefined' !== typeof params ? params : '';


			break;
		}
		self.createToggleRow( fields, values, $target, option.row_title );

		$toggle.on( 'click', '.toggle-title', function() {
			jQuery( this ).parent().find( '.toggle-fields' ).slideToggle( 300 );

			if ( jQuery( this ).find( '.toggle-toggle-icon' ).hasClass( 'fusiona-pen' ) ) {
				jQuery( this ).find( '.toggle-toggle-icon' ).removeClass( 'fusiona-pen' ).addClass( 'fusiona-minus' );
			} else {
				jQuery( this ).find( '.toggle-toggle-icon' ).removeClass( 'fusiona-minus' ).addClass( 'fusiona-pen' );
			}
		} );

		$toggle.one( 'click', '.toggle-title', function() {
			// Init repeaters if exists.
			const $repeater = $toggle.find( '.fusion-builder-option.repeater' );
			if ( $repeater.length && !this.repeaterInitialized ) {
				jQuery( document ).trigger( 'fusion-init-repeater-in-toggle', { $toggle, option: option.fields } );
				this.repeaterInitialized = true;
			}

			//init mailchimp map inside toggle.
			if ( $target.find( '.mailchimp_map' ) ) {
				new fusionMailchimpMapOption( $target );
			}

			//init hubspot map inside toggle.
			if ( $target.find( '.hubspot_map' ) ) {
				new fusionHubSpotMapOption( $target );
			}
		} );

	},

	/**
	 * Creates a new row for a specific repeater.
	 *
	 * @since 2.0.0
	 * @param {Object} fields - The fields.
	 * @param {Object} values - The values.
	 * @param {Object} $target - jQuery element.
	 * @param {string} rowTitle - The title for this row.
	 * @return {void}
	 */
	createToggleRow: function( fields, values, $target, rowTitle ) {
		var self       = this,
			$html      = '',
			attributes = {},
			repeater   = FusionPageBuilder.template( jQuery( '#fusion-app-repeater-fields' ).html() ),
			depFields  = {},
			value,
			optionId;

		rowTitle   = 'undefined' !== typeof rowTitle && rowTitle ? rowTitle : 'Toggle Row';

		$html += '<div class="toggle-row">';
		$html += '<div class="toggle-title">';
		$html += '<span class="toggle-toggle-icon fusiona-pen"></span>';
		$html += '<h3>' + rowTitle + '</h3>';
		$html += '<span></span>';
		$html += '</div>';
		$html += '<ul class="toggle-fields" style="display:none;">';

		this.repeaterRowId++;

		_.each( fields, function( field ) {
			optionId              = 'builder' === self.context ? field.param_name : field.id;
			value                 = values[ optionId ];
			depFields[ optionId ] = field;

			attributes = {
				field: field,
				value: value,
				context: self.context,
				rowId: self.repeaterRowId
			};
			$html += jQuery( repeater( attributes ) ).html();
		} );

		$html += '</ul>';
		$html += '</div>';

		$target.append( $html );

		if ( 'function' === typeof this.initOptions ) {
			this.initOptions( $target.children( 'div:last-child' ) );
		}

		// Check option dependencies
		if ( 'TO' !== this.context && 'FBE' !== this.context && 'PO' !== this.context && 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get ) {
			new FusionPageBuilder.Dependencies( fusionAllElements[ this.model.get( 'element_type' ) ].params, this, $target.children( 'div:last-child' ), depFields, this.$el );
		} else {
			new FusionPageBuilder.Dependencies( {}, this, $target.children( 'div:last-child' ), depFields, this.$el );
		}
	}
};
;/* global fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionMultiSelect = {
	optionMultiSelect: function( $element ) {
		var $multiselect;

		$element     = $element || this.$el;
		$multiselect = $element.find( '.fusion-form-multiple-select:not(.fusion-select-inited)' );

		if ( $multiselect.length ) {

			$multiselect.each( function() {
				var $self              = jQuery( this ),
					$selectPreview     = $self.find( '.fusion-select-preview-wrap' ),
					$selectSearchInput = $self.find( '.fusion-select-search input' ),
					$selectAddNew      = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ),
					$selectSave        = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-save' ),
					$selectCancel      = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ),
					$selectInput       = $self.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-input' );

				$self.addClass( 'fusion-select-inited' );

				// Open select dropdown.
				$selectPreview.on( 'click', function( event ) {
					var open = $self.hasClass( 'fusion-open' );

					if ( event.currentTarget !== this ) {
						return;
					}

					event.preventDefault();

					if ( ! open ) {
						$self.addClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.focus();
						}
					} else {
						$self.removeClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.val( '' ).blur();
						}
					}
				} );

				// Option is selected.
				$self.on( 'click', '.fusion-select-label', function( event ) {

					// Add / remove selected option from preview box.
					if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value[data-value="' + jQuery( this ).attr( 'for' ) + '"]' ).length ) {
						$self.find( '.fusion-select-preview' ).append( '<span class="fusion-preview-selected-value" data-value="' + jQuery( this ).attr( 'for' ) + '">' + jQuery( this ).html() + '<span class="fusion-option-remove">x</span></span>' );
					} else {
						$self.find( '.fusion-select-preview .fusion-preview-selected-value[data-value="' + jQuery( this ).attr( 'for' ) + '"]' ).remove();
					}

					// Show / hide placeholder text, ie: 'Select Categories or Leave Blank for All'
					if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
						$selectPreview.addClass( 'fusion-select-show-placeholder' );
					} else {
						$selectPreview.removeClass( 'fusion-select-show-placeholder' );
					}

					// Click event triggered by user pressing 'Enter'.
					if ( 'click' === event.type && 'undefined' !== typeof event.isTrigger && event.isTrigger ) {
						$selectPreview.trigger( 'click' );
					}
				} );

				// Clicked on Add New.
				$selectAddNew.on( 'click', function() {
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select.fusion-select-inited' ).hide();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ).hide();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew-section' ).show();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).focus();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).off( 'change keyup' );
				} );

				// Clicked on Cancel.
				$selectCancel.on( 'click', function() {
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew-section' ).hide();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select.fusion-select-inited' ).show();
					jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ).show();
				} );

				// Add with enter.
				$selectInput.on( 'keypress', function( event ) {
					if ( 13 === event.which ) {
						$selectSave.trigger( 'click' );
					}
				} );

				// Clicked on Save.
				$selectSave.on( 'click', function() {
					var terms    = [],
						ajaxData = {
							action: 'fusion_multiselect_addnew',
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce
						},
						$current = jQuery( this ),
						$options = jQuery( this ).closest( 'li.fusion-builder-option' ).find( '.fusion-select-options' ),
						values   = jQuery( this ).closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val();

					// early exit if empty field.
					if ( '' === values || 0 === values.trim().length ) {
						return;
					}

					values            = values.split( ',' );
					ajaxData.taxonomy = $current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).data( 'id' );

					// Remove existing terms.
					jQuery.each( values, function( index, value ) {
						var term_exists = false;
						value           = value.trim();

						jQuery.each( $options.find( ':checkbox' ), function() {
							var label   = jQuery( this ).data( 'label' ).toString(),
								checked = jQuery( this ).is( ':checked' );
							label = label.trim();

							if ( value.toLowerCase() === label.toLowerCase() ) {
								term_exists = true;

								if ( ! checked ) {
									$current.closest( 'li.fusion-builder-option' ).find( '.fusion-select-label[for="' + ajaxData.taxonomy + '-' + jQuery( this ).val() + '"]' ).trigger( 'click' );
								}
							}
						} );

						if ( ! term_exists ) {
							terms.push( value );
						}
					} );

					// early exit if duplicate values.
					if ( '' === terms || 0 === terms.length ) {
						$current.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ).trigger( 'click' );
						$current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val( '' );
						$current.closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select' ).removeClass( 'fusion-open' );
						return;
					}

					ajaxData.values = terms;

					// Add loader.
					$current.closest( 'li.fusion-builder-option' ).addClass( 'partial-refresh-active' );

					// Send data.
					jQuery.post( fusionAppConfig.ajaxurl, ajaxData, function( response ) {
						response = JSON.parse( response );
						if ( 'object' === typeof response ) {
							jQuery.each( response, function( term, term_id ) {
								$options.append( '<input type="checkbox" id="' + ajaxData.taxonomy + '-' + term_id + '" name="' + ajaxData.taxonomy + '[]" value="' + term_id + '" data-label="' + term + '" class="fusion-select-option fusion-multi-select-option">' );
								$options.append( '<label for="' + ajaxData.taxonomy + '-' + term_id + '" class="fusion-select-label">' + term + '</label>' );
								$current.closest( 'li.fusion-builder-option' ).find( '.fusion-select-label[for="' + ajaxData.taxonomy + '-' + term_id + '"]' ).trigger( 'click' );
								$current.closest( 'li.fusion-builder-option' ).find( '.fusion-form-multiple-select' ).removeClass( 'fusion-open' );
							} );

							// Show / hide placeholder text, ie: 'Select Categories or Leave Blank for All'
							if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
								$selectPreview.addClass( 'fusion-select-show-placeholder' );
							} else {
								$selectPreview.removeClass( 'fusion-select-show-placeholder' );
							}

							// Remove Loader.
							$current.closest( 'li.fusion-builder-option' ).removeClass( 'partial-refresh-active' );

							$current.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ).trigger( 'click' );
							$current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val( '' );
						}
					} );
				} );

				// Remove option from preview box.
				$selectPreview.find( '.fusion-select-preview' ).on( 'click', '.fusion-option-remove', function( event ) {
					event.preventDefault();

					const el = $self.find( '.fusion-select-label[for="' + jQuery( this ).parent().data( 'value' ) + '"]' );

					if ( el.length )  {
						el.trigger( 'click' );
					} else {

						// Option label not found so recall same function in line 49 with minor change.

						// Stop propagation to prevent toggle the select dropdown.
						event.stopPropagation();

						// Remove parent from preview box.
						jQuery( this ).parent().remove();

						// Show / hide placeholder text, ie: 'Select Categories or Leave Blank for All'
						if ( 0 === $self.find( '.fusion-select-preview .fusion-preview-selected-value' ).length ) {
							$selectPreview.addClass( 'fusion-select-show-placeholder' );
						} else {
							$selectPreview.removeClass( 'fusion-select-show-placeholder' );
						}

						// Click event triggered by user pressing 'Enter'.
						if ( 'click' === event.type && 'undefined' !== typeof event.isTrigger && event.isTrigger ) {
							$selectPreview.trigger( 'click' );
						}

					}
				} );

				// Search field.
				$selectSearchInput.on( 'keyup change paste', function( event ) {
					var val = jQuery( this ).val(),
						optionInputs = $self.find( '.fusion-select-option' );

					// Select option on "Enter" press if only 1 option is visible.
					if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $self.find( '.fusion-select-label:visible' ).length ) {
						$self.find( '.fusion-select-label:visible' ).trigger( 'click' );
						return;
					}

					_.each( optionInputs, function( optionInput ) {
						if ( -1 === jQuery( optionInput ).data( 'label' ).toLowerCase().indexOf( val.toLowerCase() ) ) {
							jQuery( optionInput ).siblings( '.fusion-select-label[for="' + jQuery( optionInput ).attr( 'id' ) + '"]' ).css( 'display', 'none' );
						} else {
							jQuery( optionInput ).siblings( '.fusion-select-label[for="' + jQuery( optionInput ).attr( 'id' ) + '"]' ).css( 'display', 'block' );
						}
					} );
				} );

			} );

		}
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.radioButtonSet = {
	optionRadioButtonSet: function( $element ) {
		var $radiobuttonsets, $radiobuttonset, $radiosetcontainer, optionId, $subGroup, $subgroupWrapper,
			self = this;

		$element         = $element || this.$el;
		$radiobuttonsets = $element.find( '.fusion-form-radio-button-set' );

		if ( $radiobuttonsets.length ) {
			$radiobuttonsets.each( function() {
				$radiobuttonset = jQuery( this );
				optionId        = $radiobuttonset.closest( '.fusion-builder-option' ).attr( 'data-option-id' );

				if ( 'color_scheme' !== optionId && 'scheme_type' !== optionId ) {
					$radiobuttonset.find( 'a' ).on( 'click', function( event ) {
						event.preventDefault();
						$radiosetcontainer = jQuery( this ).closest( '.fusion-form-radio-button-set' );
						$subGroup          = $radiosetcontainer.closest( '.fusion-builder-option.subgroup' );
						optionId           = $subGroup.attr( 'data-option-id' );

						$radiosetcontainer.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
						jQuery( this ).addClass( 'ui-state-active' );
						$radiosetcontainer.find( '.button-set-value' ).val( $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) ).trigger( 'change' );
						jQuery( this ).blur();

						if ( $subGroup.length ) {
							$subgroupWrapper = $subGroup.parent();
							$subgroupWrapper.find( '.fusion-subgroup-content[data-group="' + optionId + '"]' ).removeClass( 'active' );
							$subgroupWrapper.find( '.fusion-subgroup-' + $radiosetcontainer.find( '.ui-state-active' ).data( 'value' ) + '[data-group="' + optionId + '"]' ).addClass( 'active' );
						}
					} );
				} else {
					$radiobuttonset.find( 'a' ).on( 'click', function( event ) {
						event.preventDefault();
						if ( 'function' === typeof self.colorSchemeImport ) {
							self.colorSchemeImport( jQuery( event.currentTarget ), jQuery( event.currentTarget ).closest( '.fusion-builder-option' ) );
						}
					} );
				}
			} );
		}
	}
};
;/* global noUiSlider, wNumb */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionRangeField = {
	optionRange: function( $element ) {
		var self = this,
			$rangeSlider;

		$element     = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$rangeSlider = $element.find( '.fusion-slider-container' );

		if ( ! $rangeSlider.length ) {
			return;
		}

		if ( 'object' !== typeof this.$rangeSlider ) {
			this.$rangeSlider = {};
		}

		// Method for retreiving decimal places from step
		Number.prototype.countDecimals = function() { // eslint-disable-line no-extend-native
			if ( Math.floor( this.valueOf() ) === this.valueOf() ) {
				return 0;
			}
			return this.toString().split( '.' )[ 1 ].length || 0;
		};

		// Each slider on page, determine settings and create slider
		$rangeSlider.each( function() {

			var $targetId     = jQuery( this ).data( 'id' ),
				$rangeInput   = jQuery( this ).prev( '.fusion-slider-input' ),
				$min          = jQuery( this ).data( 'min' ),
				$max          = jQuery( this ).data( 'max' ),
				$step         = jQuery( this ).data( 'step' ),
				$direction    = 'ltr',
				$value        = $rangeInput.val(),
				$decimals     = $step.countDecimals(),
				$rangeCheck   = 1 === jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-with-default' ).length,
				$rangeDefault = jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-panel-options .fusion-range-default' ).length ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-panel-options .fusion-range-default' ) : false,
				$hiddenValue  = ( $rangeCheck ) ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-hidden-value' ) : false,
				$defaultValue = ( $rangeCheck ) ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-range-default' ).attr( 'data-default' ) : jQuery( this ).data( 'value' );

			self.$rangeSlider[ $targetId ] = jQuery( this )[ 0 ];

			// Check if parent has another value set to override TO default.
			if ( 'undefined' !== typeof self.parentValues && 'undefined' !== typeof self.parentValues[ $targetId ] && $rangeDefault ) {

				//  Set default values to new value.
				jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-range-default' ).attr( 'data-default', self.parentValues[ $targetId ] );
				$defaultValue = self.parentValues[ $targetId ];

				// If no current value is set, also update $value as representation on load.
				if ( ! $hiddenValue || '' === $hiddenValue.val() ) {
					$value = $defaultValue;
				}
			}

			self.createSlider( $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeCheck, $rangeDefault, $hiddenValue, $defaultValue, $direction );
		} );
	},

	createSlider: function( $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeCheck, $rangeDefault, $hiddenValue, $defaultValue, $direction ) {

		// Create slider with values passed on in data attributes.
		var self    = this,
			$slider = noUiSlider.create( self.$rangeSlider[ $targetId ], {
				start: [ $value ],
				step: $step,
				direction: $direction,
				range: {
					min: $min,
					max: $max
				},
				format: wNumb( {
					decimals: $decimals
				} ),
				default: $defaultValue
			} ),
			$notFirst = false;

		$rangeInput.closest( '.fusion-builder-option' ).attr( 'data-index', $targetId );

		// Check if default is currently set.
		if ( $rangeDefault && '' === $hiddenValue.val() ) {
			$rangeDefault.parent().addClass( 'checked' );
		}

		// If this range has a default option then if checked set slider value to data-value.
		if ( $rangeDefault ) {
			$rangeDefault.on( 'click', function( e ) {
				e.preventDefault();
				self.$rangeSlider[ $targetId ].noUiSlider.set( jQuery( this ).attr( 'data-default' ) );
				$hiddenValue.val( '' ).trigger( 'fusion-change' );
				jQuery( this ).parent().addClass( 'checked' );
			} );
		}

		// On slider move, update input. Also triggered on range init.
		$slider.on( 'update', function( values, handle ) {

			if ( $rangeCheck && $notFirst ) {
				if ( $rangeDefault ) {
					$rangeDefault.parent().removeClass( 'checked' );
				}
				$hiddenValue.val( values[ handle ] ).trigger( 'fusion-change' );
			}

			if ( $rangeDefault && $defaultValue == Object.values( values )[ 0 ] ) {
				$rangeDefault.parent().addClass( 'checked' );
			}

			// Not needed on init, value is already set in template.
			if ( true === $notFirst ) {
				jQuery( this.target ).closest( '.fusion-slider-container' ).prev().val( values[ handle ] ).trigger( 'change' );
			}

			$notFirst = true;
		} );

		// On manual input change, update slider position
		$rangeInput.on( 'blur', function() {

			if ( this.value !== self.$rangeSlider[ $targetId ].noUiSlider.get() ) {

				// This triggers 'update' event.
				self.$rangeSlider[ $targetId ].noUiSlider.set( this.value );
			}
		} );
	}
};
;/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionRawField = {
	optionRaw: function( $element ) {
		var self = this,
			$rawFields;

		$element   = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$rawFields = $element.find( '.fusion-builder-option.raw' );

		if ( $rawFields.length ) {
			$rawFields.each( function() {
				if ( 'function' === typeof self[ jQuery( this ).data( 'option-id' ) ] ) {
					self[ jQuery( this ).data( 'option-id' ) ]( jQuery( this ) );
				}
			} );
		}
	},

	visibility_large: function( $el ) {
		var $box = $el.find( 'span' );
		$box.html( FusionApp.settings.visibility_medium );
		$el.prev().find( '#slidervisibility_medium' ).on( 'change', function() {
			$box.html( jQuery( this ).val() );
		} );
	}
};
;/* global FusionApp, fusionAllElements, FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionRepeaterField = {

	optionRepeater: function( context ) {
		var $repeater = this.$el.find( '.fusion-builder-option.repeater' ),
			self      = this;

		// Set context to overall view for easier access.
		this.context = context;

		this.repeaterRowId = 'undefined' === typeof this.repeaterRowId ? 0 : this.repeaterRowId;

		if ( $repeater.length ) {
			$repeater.each( function() {
				self.initRepeater( jQuery( this ) );
			} );
		}

		jQuery( document ).on( 'fusion-init-repeater-in-toggle', function ( e, obj ) { // eslint-disable-line no-unused-vars
			$repeater = jQuery( obj.$toggle ).find( '.fusion-builder-option.repeater' );
			if ( $repeater.length ) {
				$repeater.each( function() {
					self.initRepeater( jQuery( this ), obj.option );
				} );
			}
		} );
	},

	/**
	 * Init the option.
	 *
	 * @since 2.0.0
	 * @param {Object} $repeater - jQuery object of the DOM element.
	 * @return {void}
	 */
	initRepeater: function( $repeater, options ) {
		var self       = this,
			param      = $repeater.data( 'option-id' ),
			$target    = $repeater.find( '.repeater-rows' ),
			$option    = $repeater.find( '.fusion-repeater-value' ),
			rows       = false,
			option,
			fields,
			attributes,
			params,
			values,
			rowTitle;

		// When doing a search we need to set the context correctly.
		if ( 'search' === this.context ) {
			this.context = jQuery( '.fusion-sidebar-section[data-context]' ).data( 'context' );
		}

		switch ( this.context ) {

		case 'TO':
		case 'FBE':

			options = options || this.options;
			option   = options[ param ];
			fields   = option.fields;
			values   = FusionApp.settings[ param ];

			if ( ! _.isEmpty( values ) ) {
				values = self.reduxDataCorrect( values );
				rows   = true;
			}

			break;

		case 'PO':

			options = options || this.options;
			option   = options[ param ];
			fields   = option.fields;
			values   = FusionApp.data.postMeta._fusion[ param ];

			if ( ! _.isEmpty( values ) ) {
				if ( 'string' === typeof values ) {
					values = JSON.parse( values );
					try {
						values = JSON.parse( values );
					} catch ( e ) {
						console.warn( 'Something went wrong! Error triggered - ' + e );
					}
				}
				rows   = true;
			}
			break;

		default:
			options = options || fusionAllElements[ this.model.get( 'element_type' ) ].params;
			option     = options[ param ];
			fields     = 'undefined' !== typeof option ? option.fields : {};
			attributes = jQuery.extend( true, {}, this.model.attributes );

			if ( 'function' === typeof this.filterAttributes ) {
				attributes = this.filterAttributes( attributes );
			}

			params     = attributes.params;
			values     = 'undefined' !== typeof params[ param ] ? params[ param ] : '';

			if ( 'string' === typeof values && '' !== values ) {
				values = self.getRepeaterValue( false, values );
				rows   = true;
			}

			break;
		}

		// Create the rows for existing values.
		if ( 'object' === typeof values && rows ) {
			_.each( values, function( field, index ) {
				rowTitle = 'undefined' !== typeof values[ index ][ option.bind_title ] && values[ index ][ option.bind_title ] ? values[ index ][ option.bind_title ] : '';

				// If select field use label of value.
				if ( '' !== rowTitle && 'object' === typeof option.fields[ option.bind_title ] && 'select' === option.fields[ option.bind_title ].type && 'object' === typeof option.fields[ option.bind_title ].choices ) {
					rowTitle = option.fields[ option.bind_title ].choices[ rowTitle ];
				}
				if ( '' === rowTitle && 'undefined' !== typeof option.row_title ) {
					rowTitle = option.row_title;
				}

				self.createRepeaterRow( fields, values[ index ], $target, rowTitle );
			} );
		} else {
			rowTitle = 'object' === typeof values && 'undefined' !== typeof values[ option.bind_title ] && values[ option.bind_title ] ? values[ option.bind_title ] : '';
			if ( '' === rowTitle && 'undefined' !== typeof option.row_title ) {
				rowTitle = option.row_title;
			}
			self.createRepeaterRow( fields, {}, $target, rowTitle );
		}

		// Repeater row add click event.
		$repeater.on( 'click', '.repeater-row-add', function( event ) {
			var newRowTitle = 'undefined' !== typeof option.row_title ? option.row_title : false;
			event.preventDefault();
			self.createRepeaterRow( fields, {}, $target, newRowTitle );
		} );

		// Repeater row remove click event.
		$repeater.on( 'click', '.repeater-row-remove.fusiona-trash-o', function( event ) {
			var rowIndex = jQuery( this ).closest( '.repeater-row' ).index();

			event.preventDefault();

			self.removeRepeaterRowData( $option, rowIndex );

			jQuery( this ).closest( '.repeater-row' ).remove();
		} );

		$repeater.on( 'click', '.repeater-title', function() {
			jQuery( this ).parent().find( '.repeater-fields' ).slideToggle( 300 );

			if ( jQuery( this ).find( '.repeater-toggle-icon' ).hasClass( 'fusiona-pen' ) ) {
				jQuery( this ).find( '.repeater-toggle-icon' ).removeClass( 'fusiona-pen' ).addClass( 'fusiona-minus' );
			} else {
				jQuery( this ).find( '.repeater-toggle-icon' ).removeClass( 'fusiona-minus' ).addClass( 'fusiona-pen' );
			}
		} );

		$repeater.on( 'change', '.repeater-row [name=' + option.bind_title + ']', function() {
			var title = jQuery( this ).hasClass( 'fusion-select-option' ) || jQuery( this ).hasClass( 'fusion-select-option-value' ) ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-select-label[for=' + jQuery( this ).attr( 'id' ) + '], .fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).html() : jQuery( this ).val();
			jQuery( this ).closest( '.repeater-row' ).find( '> .repeater-title > h3' ).html( title );
		} );

		$repeater.sortable( {
			handle: '.repeater-title',
			items: '.repeater-row',
			cursor: 'move',
			cancel: '.repeater-row-remove.fusiona-trash-o',
			start: function( event, ui ) {
				jQuery( this ).attr( 'data-previndex', ui.item.index() );
			},
			update: function( event, ui ) {
				var newIndex = ui.item.index(),
					oldIndex = parseInt( jQuery( this ).attr( 'data-previndex' ), 10 );

				jQuery( this ).removeAttr( 'data-previndex' );

				self.orderRepeaterData( $option, oldIndex, newIndex );
			}
		} );

	},

	/**
	 * Creates a new row for a specific repeater.
	 *
	 * @since 2.0.0
	 * @param {Object} fields - The fields.
	 * @param {Object} values - The values.
	 * @param {Object} $target - jQuery element.
	 * @param {string} rowTitle - The title for this row.
	 * @return {void}
	 */
	createRepeaterRow: function( fields, values, $target, rowTitle ) {
		var self       = this,
			$html      = '',
			attributes = {},
			repeater   = FusionPageBuilder.template( jQuery( '#fusion-app-repeater-fields' ).html() ),
			depFields  = {},
			value,
			optionId;

		rowTitle   = 'undefined' !== typeof rowTitle && rowTitle ? rowTitle : 'Repeater Row';

		$html += '<div class="repeater-row">';
		$html += '<div class="repeater-title">';
		$html += '<span class="repeater-toggle-icon fusiona-pen"></span>';
		$html += '<h3>' + rowTitle + '</h3>';
		$html += '<span class="repeater-row-remove fusiona-trash-o"></span>';
		$html += '</div>';
		$html += '<ul class="repeater-fields">';

		this.repeaterRowId++;

		_.each( fields, function( field ) {
			optionId              = 'builder' === self.context ? field.param_name : field.id;
			value                 = values[ optionId ];
			depFields[ optionId ] = field;

			attributes = {
				field: field,
				value: value,
				context: self.context,
				rowId: self.repeaterRowId
			};
			$html += jQuery( repeater( attributes ) ).html();
		} );

		$html += '</ul>';
		$html += '</div>';

		$target.append( $html );

		if ( _.isEmpty( values ) ) {
			this.addRepeaterRowData( $target, fields );
		}

		if ( 'function' === typeof this.initOptions ) {
			this.initOptions( $target.children( 'div:last-child' ) );
		}

		// Check option dependencies
		if ( 'TO' !== this.context && 'FBE' !== this.context && 'PO' !== this.context && 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.get ) {
			new FusionPageBuilder.Dependencies( fusionAllElements[ this.model.get( 'element_type' ) ].params, this, $target.children( 'div:last-child' ), depFields, this.$el );
		} else {
			new FusionPageBuilder.Dependencies( {}, this, $target.children( 'div:last-child' ), depFields, this.$el );
		}
	},

	/**
	 * Get repeater value in correct format.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery element.
	 * @param {Array|string} values - The values.
	 * @return {Object} - Values in correct format to be read.
	 */
	getRepeaterValue: function( $option, values ) {
		var self = this;

		values = 'undefined' === typeof values ? $option.val() : values;

		if ( 'string' === typeof values && '' !== values ) {
			switch ( this.context ) {

			case 'TO':
			case 'FBE':
				try {
					values = JSON.parse( values );
					if ( ! _.isEmpty( values ) ) {
						values = self.reduxDataCorrect( values );
					}
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;

			case 'PO':
				try {
					values = JSON.parse( values );
					if ( 'function' !== typeof values.splice ) {
						values = Object.values( values );
					}
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;

			default:
				try {
					values = FusionPageBuilderApp.base64Decode( values );
					values = _.unescape( values );
					values = JSON.parse( values );
				} catch ( e ) {
					console.warn( 'Something went wrong! Error triggered - ' + e );
				}
				break;
			}
		}

		if ( '' === values || _.isEmpty( values ) ) {
			values = [];
		}

		return values;
	},

	/**
	 * Adds a new row of data to the repeater data.
	 *
	 * @since 2.0.0
	 * @param {Object} $repeaters - jQuery object.
	 * @param {Object} fields - The fields.
	 * @return {void}
	 */
	addRepeaterRowData: function( $repeaters, fields ) {
		var self      = this,
			newIndex  = $repeaters.find( '.repeater-row' ).last().index(),
			$option   = $repeaters.closest( '.repeater' ).find( '.fusion-repeater-value' ),
			values    = this.getRepeaterValue( $option ),
			rowValues = {},
			defaultVal,
			paramId;

		if ( 'builder' !== this.context && 'PO' !== this.context ) {
			rowValues.fusionredux_repeater_data = {
				title: ''
			};
		}

		// Get defaults for each field.
		_.each( fields, function( field ) {
			paramId    = 'builder' === self.context ? field.param_name : field.id;
			defaultVal = 'undefined' !== typeof field[ 'default' ] && 'builder' !== self.context && ( 'select' === field.type || 'radio-buttonset' === field.type ) ? field[ 'default' ] : '';
			rowValues[ paramId ] = defaultVal;
		} );

		// Set values.
		values[ newIndex ] = rowValues;
		this.updateRepeaterValues( $option, values );
	},

	/**
	 * Removes a specific row of data from repeater object.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {number} index - Ror index.
	 * @return {void}
	 */
	removeRepeaterRowData: function( $option, index ) {
		var values = this.getRepeaterValue( $option );

		if ( 'undefined' !== typeof values[ index ] ) {
			values.splice( index, 1 );
			this.updateRepeaterValues( $option, values );
		}
	},

	/**
	 * Changes the order of a rows in repeater data (sortable).
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {number} oldIndex - The old row index.
	 * @param {number} newIndex - The new row index.
	 * @return {void}
	 */
	orderRepeaterData: function( $option, oldIndex, newIndex ) {
		var values  = this.getRepeaterValue( $option ),
			rowData = values[ oldIndex ];

		if ( 'undefined' !== typeof rowData ) {
			values.splice( oldIndex, 1 );
			values.splice( newIndex, 0, rowData );
			this.updateRepeaterValues( $option, values );
		} else {
			console.warn( 'Something went wrong! Old index data not found.' );
		}
	},

	/**
	 * Changes a specific row parameter value in repeater data.
	 *
	 * @since 2.0.0
	 * @param {Object} $option - jQuery object.
	 * @param {sring} param - The parameter we're editing.
	 * @param {number} index - The row index.
	 * @param {mixed} value - The value.
	 * @return {void}
	 */
	setRepeaterValue: function( $option, param, index, value ) {
		var values  = this.getRepeaterValue( $option );

		if ( 'undefined' !== typeof values[ index ] ) {
			values[ index ][ param ] = value;
			this.updateRepeaterValues( $option, values );
		}
	},

	/**
	 * Updates the repeater data on hidden input in correct format
	 * and trigger a change event to update.
	 *
	 * @since 2.0.0
	 * @return {void}
	 */
	updateRepeaterValues: function( $option, values ) {

		if ( '' !== values && ! _.isEmpty( values ) ) {
			switch ( this.context ) {
			case 'TO':
			case 'FBE':
				values = this.reduxDataReverse( values );
				values = JSON.stringify( values );
				break;

			case 'PO':
				values = JSON.stringify( values );
				break;

			default:
				values = JSON.stringify( values );
				values = FusionPageBuilderApp.base64Encode( values );
				break;
			}
		}
		$option.val( values ).trigger( 'change' );
	},

	/**
	 * Changes the redux data format to more logical format which is used
	 * in the builder version of repeater.
	 *
	 * @since 2.0.0
	 * @return {Object} Values in builder type readable format
	 */
	reduxDataCorrect: function( values ) {
		var newFormat = [];

		_.each( values, function( param, paramName ) {
			_.each( param, function( value, index ) {
				if ( 'undefined' === typeof newFormat[ index ] ) {
					newFormat[ index ] = {};
				}
				newFormat[ index ][ paramName ] = value;
			} );
		} );

		return newFormat;
	},

	/**
	 * Changes from builder data structure back to redux.
	 *
	 * @since 2.0.0
	 * @return {Object} Values in redux format
	 */
	reduxDataReverse: function( values ) {
		var oldFormat = {};

		_.each( values, function( param ) {
			_.each( param, function( value, paramName ) {
				if ( 'undefined' === typeof oldFormat[ paramName ] ) {
					oldFormat[ paramName ] = [];
				}
				oldFormat[ paramName ].push( value );
			} );
		} );
		return oldFormat;
	}
};
;/* global fusionAllElements, fusionAppConfig */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSelectField = {
	optionSelect: function( $element ) {
		var $selectField,
			conditions,
			param,
			defaultValue,
			value,
			params,
			self = this;

		$element     = $element || this.$el;
		$selectField = $element.find( '.fusion-select-field:not(.fusion-select-inited):not(.fusion-form-multiple-select):not(.fusion-ajax-select):not(.fusion-skip-init)' );

		if ( $selectField.length ) {

			$selectField.each( function() {
				var $self              = jQuery( this ),
					$selectDropdown    = $self.find( '.fusion-select-dropdown' ),
					$selectPreview     = $self.find( '.fusion-select-preview-wrap' ),
					$selectSearchInput = $self.find( '.fusion-select-search input' ),
					$selectPreviewText = $selectPreview.find( '.fusion-select-preview' ),
					$quickEditButton   = $self.closest( '.fusion-builder-option' ).find( '.awb-quick-edit-button' );

				$self.addClass( 'fusion-select-inited' );

				// Open select dropdown.
				$selectPreview.on( 'click', function( event ) {
					var open = $self.hasClass( 'fusion-open' );

					event.preventDefault();

					if ( ! open ) {
						$self.addClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.focus();
						}
					} else {
						$self.removeClass( 'fusion-open' );
						if ( $selectSearchInput.length ) {
							$selectSearchInput.val( '' ).blur();
						}
						$self.find( '.fusion-select-label' ).css( 'display', 'block' );
					}
				} );

				// Option is selected.
				$self.on( 'click', '.fusion-select-label', function() {
					$selectPreviewText.html( jQuery( this ).html() );
					$selectPreview.trigger( 'click' );

					$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
					jQuery( this ).addClass( 'fusion-option-selected' );

					$self.find( '.fusion-select-option-value' ).val( jQuery( this ).data( 'value' ) ).trigger( 'change', [ { userClicked: true } ] );
				} );

				// Conditional select init.
				if ( $self.is( '[data-conditions]' ) ) {
					conditions   = $self.data( 'conditions' ),
					param        = $self.closest( '.fusion-builder-option' ).attr( 'data-option-id' ),
					defaultValue = 'object' === typeof fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ] ? fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ][ 'default' ] : '',
					value        = null,
					params       = self.model.get( 'params' );

					conditions = conditions ? JSON.parse( _.unescape( conditions ) ) : false;
					if ( false !== conditions ) {
						if ( 'string' !== typeof conditions.option || 'object' !== typeof conditions.map ) {
							return;
						}

						// Check for value and if param exists.
						if ( 'undefined' !== params[ conditions.option ] ) {
							value = params[ conditions.option ];
						} else if ( 'object' === typeof fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ] ) {
							value = fusionAllElements[ self.model.get( 'element_type' ) ].params[ param ][ 'default' ];
						}

						// Param exists and we have value.
						if ( null !== value ) {

							// We have accepted values, disable rest.
							if ( 'object' === typeof conditions.map[ value ] ) {
								$self.find( '.fusion-select-label' ).addClass( 'fusion-disabled' );
								_.each( conditions.map[ value ], function( acceptedValue ) {
									$self.find( '.fusion-select-label[data-value="' + acceptedValue + '"]' ).removeClass( 'fusion-disabled' );
								} );
							} else {
								$self.find( '.fusion-select-label' ).removeClass( 'fusion-disabled' );
							}

							// Listen for changes to other option.
							self.$el.find( '#' + conditions.option ).on( 'change', function() {
								var itemValue = jQuery( this ).val(),
									dataConditions = $self.data( 'conditions' );

								dataConditions = dataConditions ? JSON.parse( _.unescape( dataConditions ) ) : false;
								if ( false === dataConditions ) {
									return;
								}

								// Find and disable options not valid.
								if ( 'object' === typeof dataConditions.map[ itemValue ] ) {
									$self.find( '.fusion-select-label' ).addClass( 'fusion-disabled' );
									_.each( dataConditions.map[ itemValue ], function( acceptedValue ) {
										$self.find( '.fusion-select-label[data-value="' + acceptedValue + '"]' ).removeClass( 'fusion-disabled' );
									} );
								} else {
									$self.find( '.fusion-select-label' ).removeClass( 'fusion-disabled' );
								}

								// If selection is now invalid, reset to default.
								if ( $self.find( '.fusion-option-selected.fusion-disabled' ).length ) {
									$self.find( '.fusion-select-option-value' ).val( defaultValue ).trigger( 'change', [ { userClicked: true, silent: true } ] );
								}
							} );
						}
					}
				}
				$self.find( '.fusion-select-option-value' ).on( 'change', function( event, data ) {
					var itemValue = jQuery( this ).val();

					if ( 'undefined' !== typeof data && 'undefined' !== typeof data.userClicked && true !== data.userClicked ) {
						return;
					}

					// Option changed progamatically, we need to update preview.
					$selectPreview.find( '.fusion-select-preview' ).html( $self.find( '.fusion-select-label[data-value="' + itemValue + '"]' ).html() );
					$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
					$selectDropdown.find( '.fusion-select-label[data-value="' + itemValue + '"]' ).addClass( 'fusion-option-selected' );

					// Quick edit option update.
					if ( $selectDropdown.closest( '.fusion-builder-option' ).find( '.awb-quick-edit-button' ).length && ( '0' == itemValue || '' == itemValue ) ) {
						$selectDropdown.closest( '.fusion-builder-option' ).find( '.awb-quick-edit-button' ).removeClass( 'has-quick-edit' );
					} else {
						$selectDropdown.closest( '.fusion-builder-option' ).find( '.awb-quick-edit-button' ).addClass( 'has-quick-edit' );
					}

				} );

				// Search field.
				$selectSearchInput.on( 'keyup change paste', function() {
					var val = jQuery( this ).val(),
						optionInputs = $self.find( '.fusion-select-label' );

					// Select option on "Enter" press if only 1 option is visible.
					if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $self.find( '.fusion-select-label:visible' ).length ) {
						$self.find( '.fusion-select-label:visible' ).trigger( 'click' );
						return;
					}

					_.each( optionInputs, function( optionInput ) {
						if ( -1 === jQuery( optionInput ).html().toLowerCase().indexOf( val.toLowerCase() ) ) {
							jQuery( optionInput ).css( 'display', 'none' );
						} else {
							jQuery( optionInput ).css( 'display', 'block' );
						}
					} );
				} );

				$quickEditButton.on( 'click', function() { // here.
					const type    = jQuery( this ).data( 'type' ),
						itemValue = jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-select-option-value' ).val(),
						items     = jQuery( this ).data( 'items' );
					let url;

					if ( 'menu' === type ) {
						window.open( fusionAppConfig.admin_url + 'nav-menus.php?action=edit&menu=' + items[ itemValue ], '_blank' ).focus();
					} else {
						url = 'live' === fusionAppConfig.builder_type ? items[ itemValue ] + '?fb-edit=1' : fusionAppConfig.admin_url + 'post.php?post=' + itemValue + '&action=edit';
						window.open( url, '_blank' ).focus();
					}
				} );

			} );
		}
	}
};
;/* globals fusionAppConfig, FusionPageBuilderApp, FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

function FASElement( el, parentScope ) {
	var self = this;

	this.$el            = jQuery( el );
	this.parentScope    = parentScope;
	this.repeaterId     = this.$el.data( 'repeater-id' );
	this.fieldId        = this.$el.data( 'field-id' );
	this.ajaxCall       = this.$el.data( 'ajax' );
	this.maxInput       = this.$el.data( 'max-input' );
	this.prefix         = this.repeaterId + this.fieldId,
	this.initialValues  = [];
	this.values         = {};
	this.searchResults  = [];
	this.ajaxInProcess  = false;
	this.options        = [];
	this.ajaxParams     = [];

	this.init();

	// Bindings
	this.search         = _.bind( this.search, this );
	this.select         = _.bind( this.select, this );
	this.removeTag      = _.bind( this.removeTag, this );
	this.addNew         = _.bind( this.addNew, this );
	this.saveNew        = _.bind( this.saveNew, this );
	this.cancelAddNew   = _.bind( this.cancelAddNew, this );
	this.verifyInput    = _.bind( this.verifyInput, this );
	this.hideDropdown   = _.bind( this.hideDropdown, this );
	this.renderOptions  = _.bind( this.renderOptions, this );
	this.$el.on( 'input keyup paste', '.fusion-ajax-select-search input', _.debounce( this.search, 300 ) );
	this.$el.on( 'click', '.fusion-select-label', _.debounce( this.select, 300 ) );
	this.$el.on( 'click', '.fusion-option-remove', this.removeTag );

	// Add New.
	this.$el.closest( 'li.fusion-builder-option' ).on( 'click', '.fusion-multiselect-addnew', this.addNew );
	this.$el.closest( 'li.fusion-builder-option' ).on( 'click', '.fusion-multiselect-cancel', this.cancelAddNew );
	this.$el.closest( 'li.fusion-builder-option' ).on( 'click', '.fusion-multiselect-save', this.saveNew );
	this.$el.closest( 'li.fusion-builder-option' ).on( 'keypress', '.fusion-multiselect-input', this.verifyInput );

	// Hide search results when a click outside $el occurs
	jQuery( document ).mouseup( function( event ) {
		if ( ! self.$el.is( event.target ) && 0 === self.$el.has( event.target ).length ) {
			self.hideDropdown();
		}
	} );
}

FASElement.prototype.removeTag  = function( event ) {
	var id = jQuery( event.target ).parent().data( 'value' );
	jQuery( event.target ).parent().remove();
	this.$el.find( '.fusion-select-label[for="' + id + '"]' ).trigger( 'click' );

	if ( this.$el.hasClass( 'fusion-ajax-single-select' ) ) {
		this.$el.find( 'input[type=search]' ).focus();
		this.$el.find( 'input[type=search]' ).val( '' );
	}
};

FASElement.prototype.addNew  = function() {
	this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-ajax-select.fusion-select-inited' ).hide();
	this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ).hide();
	this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew-section' ).show();
	this.$el.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).focus();
	this.$el.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).off( 'change keyup' );
};

FASElement.prototype.verifyInput = function( event ) {
	if ( 13 === event.which ) {
		this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-save' ).trigger( 'click' );
	}
};

FASElement.prototype.saveNew = function() {
	var terms    = [],
		ajaxData = {
			action: 'fusion_multiselect_addnew',
			fusion_load_nonce: fusionAppConfig.fusion_load_nonce
		},
		$current = this.$el,
		self     = this,
		$tags    = this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-select-tags' ),
		values   = this.$el.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val();

	// early exit if empty field.
	if ( '' === values || 0 === values.trim().length ) {
		return;
	}

	values            = values.split( ',' );
	ajaxData.taxonomy = $current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).data( 'id' );

	// Remove existing terms.
	jQuery.each( values, function( index, value ) {
		var term_exists = false;
		value           = value.trim();

		jQuery.each( $tags.find( '.fusion-select-tag' ), function() {
			var label = jQuery( this ).data( 'text' ).toString();
			label = label.trim();

			if ( value.toLowerCase() === label.toLowerCase() ) {
				term_exists = true;
			}
		} );

		if ( ! term_exists ) {
			terms.push( value );
		}
	} );

	// early exit if duplicate values.
	if ( '' === terms || 0 === terms.length ) {
		$current.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ).trigger( 'click' );
		$current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val( '' );
		return;
	}

	ajaxData.values = terms;

	// Add loader.
	$current.closest( 'li.fusion-builder-option' ).addClass( 'partial-refresh-active' );

	// Send data.
	jQuery.post( fusionAppConfig.ajaxurl, ajaxData, function( response ) {
		response = JSON.parse( response );
		if ( 'object' === typeof response ) {

			if ( 'string' === typeof FusionApp.data.postDetails[ ajaxData.taxonomy ] ) {
				FusionApp.data.postDetails[ ajaxData.taxonomy ] = FusionApp.data.postDetails[ ajaxData.taxonomy ].split( ',' );
			}

			jQuery.each( response, function( term, term_id ) {

				// Update Options.
				self.options.push( {
					'id': term_id,
					'text': term,
					'checked': true
				} );

				// Update meta.
				FusionApp.data.postDetails[ ajaxData.taxonomy ].push( term_id );
			} );

			self.renderOptions();

			// Remove Loader.
			$current.closest( 'li.fusion-builder-option' ).removeClass( 'partial-refresh-active' );

			$current.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-cancel' ).trigger( 'click' );
			$current.closest( 'li.fusion-builder-option' ).find( 'input.fusion-multiselect-input' ).val( '' );

			FusionApp.contentChange( 'page', 'page-setting' );
		}
	} );
};

FASElement.prototype.cancelAddNew  = function() {
	this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew-section' ).hide();
	this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-ajax-select.fusion-select-inited' ).show();
	this.$el.closest( 'li.fusion-builder-option' ).find( '.fusion-multiselect-addnew' ).show();
};

FASElement.prototype.showNotice = function( message ) {
	this.$el.find( '.fusion-ajax-select-notice' ).html( message ).show();
};

FASElement.prototype.hideNotice = function() {
	this.$el.find( '.fusion-ajax-select-notice' ).hide();
};

FASElement.prototype.showDropdown = function() {
	this.$el.addClass( 'fusion-open' );
};

FASElement.prototype.hideDropdown = function() {
	this.$el.removeClass( 'fusion-open' );
};

FASElement.prototype.toggleEmptySelection = function() {
	if ( this.$el.hasClass( 'fusion-ajax-single-select' ) && 1 > this.$el.find( '.fusion-select-tag' ).length ) {
		this.$el.addClass( 'fusion-ajax-empty-select' );
	} else {
		this.$el.removeClass( 'fusion-ajax-empty-select' );
	}
};

FASElement.prototype.setLoader = function( isLoading ) {
	var searchInput = this.$el.find( '.fusion-ajax-select-search input' );
	this.ajaxInProcess = isLoading;

	searchInput.attr( 'disabled', this.ajaxInProcess );

	// Return focus.
	if ( ! this.ajaxInProcess ) {
		searchInput.focus();
	}
};

FASElement.prototype.search = function( event ) {
	var self, search, item;

	self    = this;
	search  = event.target.value;
	item    = ( 2 > self.maxInput ) ? 'item' : 'items';

	event.preventDefault();

	self.$el.find( '.fusion-select-options' ).hide();

	this.options = _.filter( this.options, { checked: true } );

	this.showDropdown();

	// Max input check.
	if ( self.maxInput <= self.options.length ) {
		this.showNotice( 'You can only select ' + self.maxInput + ' ' + item );
		return;
	}

	if ( 3 <= search.length ) {
		if ( true === this.ajaxInProcess ) {
			return;
		}

		this.showNotice( '<div class="fusion-select-loader"></div>' );
		this.setLoader( true );

		jQuery.post(
			fusionAppConfig.ajaxurl,
			{
				action: this.ajaxCall,
				search: search.toLowerCase(),
				params: this.ajaxParams,
				fusion_load_nonce: fusionAppConfig.fusion_load_nonce
			},
			function( data ) {
				var results;

				data = JSON.parse( data );
				// Remove already selected values from search results.
				results =  _.filter( data.results || [], function( result ) {
					return ! _.find( self.options, function( option ) {
						return option.id == result.id;
					} );
				} );

				// No new results.
				if ( ! results.length ) {
					self.setLoader( false );
					return self.showNotice( 'No search results' );
				}
				// Update tags and options.
				self.options = self.options.concat( results );
				self.hideNotice();
				self.renderOptions();
				self.$el.find( '.fusion-select-options' ).show();
				self.setLoader( false );
			}
		);

	} else if ( 0 === search.length ) {
		this.hideDropdown();
	} else {
		this.showNotice( 'Please enter 3 or more characters' );
	}
};

FASElement.prototype.select = function( event ) {
	var input, checked, id, item;

	event.preventDefault();

	input   = jQuery( '#' + jQuery( event.target ).attr( 'for' ) );
	item    = jQuery( event.target ).closest( '.fusion-ajax-select' );
	checked = input.is( ':checked' );
	id      = input.val();

	_.each( this.options, function( option ) {
		if ( option.id == id ) {
			option.checked = checked;
		}
		return option;
	} );

	if ( item.hasClass( 'fusion-ajax-single-select' ) ) {
		this.hideDropdown();
	}

	this.renderOptions();
};

FASElement.prototype.toggleLoading = function() {
	var className = 'fusion-ajax-select-loading';
	if ( this.$el.hasClass( className ) ) {
		this.$el.removeClass( className );
	} else {
		this.$el.addClass( className );
	}
};

FASElement.prototype.getLabels = function() {
	return jQuery.ajax( {
		type: 'POST',
		url: fusionAppConfig.ajaxurl,
		data: {
			action: this.ajaxCall,
			labels: this.initialValues,
			params: this.ajaxParams,
			fusion_load_nonce: fusionAppConfig.fusion_load_nonce
		}
	} );

};

FASElement.prototype.renderOptions = function() {
	var self, $options, $tags, availableOptions, $newOptions, diff;

	self        = this;
	$options    = this.$el.find( '.fusion-select-options' );
	$tags       = this.$el.find( '.fusion-select-tags' );

	$newOptions = $options.clone();

	$newOptions.empty();
	$tags.empty();

	// Hide dropdown if there are no available options left
	availableOptions = _.filter( this.options, function( option ) {
		return ! option.checked;
	} );
	if ( ! availableOptions.length ) {
		this.hideDropdown();
	}

	_.each( this.options, function( option ) {
		var theID =  self.prefix + '-' + option.id;
		var checked = option.checked ? 'checked' : '';
		var $option = jQuery( '<input type="checkbox" id="' + theID + '" name="' + self.fieldId + '[]" value="' + option.id + '" data-label="' + option.text + '" class="fusion-select-option" ' + checked + '><label for="' + theID + '" class="fusion-select-label">' + option.text + '</label>' );
		// Add option
		$newOptions.append( $option );
		if ( checked ) {
			$option.hide();
			// Add tag
			$tags.append(
				'<span class="fusion-select-tag" data-value="' + theID + '" data-text="' + option.text + '">' + option.text + '<span class="fusion-option-remove">x</span></span>'
			);
		}
	} );

	diff = FusionPageBuilderApp._diffdom.diff( $options[ 0 ], $newOptions[ 0 ] );
	FusionPageBuilderApp._diffdom.apply( $options[ 0 ], diff );

	self.toggleEmptySelection();
};

FASElement.prototype.init = function() {
	var self, initialValues, ajaxParams;

	self = this;
	// Retrieve values from hidden inputs.
	initialValues = this.$el.find( '.initial-values' ).val();
	ajaxParams    = this.$el.find( '.params' ).val();

	// Parse initial values and additional params.
	this.initialValues  = initialValues ? JSON.parse( _.unescape( initialValues ) ) : [];
	this.ajaxParams     = ajaxParams ? JSON.parse( _.unescape( ajaxParams ) ) : [];

	self.$el.addClass( 'fusion-select-inited' );
	// Get corresponding labels for initial values.
	if ( this.initialValues.length ) {
		this.toggleLoading();
		this.getLabels().done( function( data ) {
			data = JSON.parse( data );

			self.options = data.labels || [];
			// Set as initial values.
			_.each( self.options, function( option ) {
				option.checked = true;
			} );

			self.renderOptions();
			self.toggleLoading();
		} );
	}

	self.toggleEmptySelection();
};

FusionPageBuilder.options.fusionAjaxSelect = {

	optionAjaxSelect: function( $element ) {
		var $selectField, self;

		self            = this;
		$selectField    = $element.find( '.fusion-ajax-select:not(.fusion-select-inited):not(.fusion-form-multiple-select):not(.fusion-skip-init)' );

		$selectField.each( function() {
			new FASElement( this, self );
		} );
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSortableText = {
	optionSortableText: function( $element ) {
		var $sortable;
		$element  = $element || this.$el;
		$sortable = $element.find( '.fusion-sortable-text-options' );

		$sortable.each( function() {
			var $sort = jQuery( this );

			$sort.sortable( {
				handle: '.fusion-sortable-move'
			} );
			$sort.on( 'sortupdate', function( event ) {
				var sortContainer = jQuery( event.target ),
					sortOrder = '';

				sortContainer.children( '.fusion-sortable-option' ).each( function() {
					sortOrder += jQuery( this ).find( 'input' ).val() + '|';
				} );

				sortOrder = sortOrder.slice( 0, -1 );

				sortContainer.siblings( '.sort-order' ).val( sortOrder ).trigger( 'change' );
			} );

			$sort.on( 'click', '.fusion-sortable-remove', function( event ) {
				event.preventDefault();

				jQuery( event.target ).closest( '.fusion-sortable-option' ).remove();
				$sort.trigger( 'sortupdate' );
			} );

			$sort.on( 'change keyup', 'input', function() {
				$sort.trigger( 'sortupdate' );
			} );

			$sort.prev( '.fusion-builder-add-sortable-child' ).on( 'click', function( event ) {
				var $newItem = $sort.next( '.fusion-placeholder-example' ).clone( true );

				event.preventDefault();

				$newItem.removeClass( 'fusion-placeholder-example' ).removeAttr( 'style' ).appendTo( $sort );

				setTimeout( function() {
					$sort.find( '.fusion-sortable-option:last-child input' ).focus();
				}, 100 );

				$sort.trigger( 'sortupdate' );
			} );
		} );
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionConnectedSortable = {
	optionConnectedSortable: function( $element ) {
		var $sortable;
		$sortable = $element.find( '.fusion-connected-sortable' );
		$sortable.sortable( {
			connectWith: '.fusion-connected-sortable',

			stop: function() {
				var $enabled = $element.find( '.fusion-connected-sortable-enabled' ),
					$container = $element.find( '.fusion-builder-option.connected_sortable' ),
					sortOrder     = '';
				$enabled.children( '.fusion-connected-sortable-option' ).each( function() {
					sortOrder += jQuery( this ).data( 'value' ) + ',';
				} );

				sortOrder = sortOrder.slice( 0, -1 );

				$container.find( '.fusion-connected-sortable' ).each( function() {
					if ( jQuery( this ).find( 'li' ).length ) {
						jQuery( this ).removeClass( 'empty' );
					} else {
						jQuery( this ).addClass( 'empty' );
					}
				} );

				$container.find( '.sort-order' ).val( sortOrder ).trigger( 'change' );
			}
		} ).disableSelection();
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSortable = {
	optionSortable: function( $element ) {
		var $sortable;
		$element  = $element || this.$el;
		$sortable = $element.find( '.fusion-sortable-options' );

		$sortable.each( function() {
			if ( '' === jQuery( this ).siblings( '.sort-order' ).val() ) {
				jQuery( this ).closest( '.pyre_metabox_field' ).find( '.fusion-builder-default-reset' ).addClass( 'checked' );
			}

			jQuery( this ).sortable();
			jQuery( this ).on( 'sortupdate', function( event ) {
				var sortContainer = jQuery( event.target ),
					sortOrder = '';

				sortContainer.children( '.fusion-sortable-option' ).each( function() {
					sortOrder += jQuery( this ).data( 'value' ) + ',';
				} );

				sortOrder = sortOrder.slice( 0, -1 );

				sortContainer.siblings( '.sort-order' ).val( sortOrder ).trigger( 'change' );
			} );
		} );
	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSwitchField = {
	optionSwitch: function( $element ) {
		var $checkboxes;

		$element    = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$checkboxes = jQuery( $element.find( '.fusion-builder-option.switch input[type="checkbox"]' ) );

		_.each( $checkboxes, function( checkbox ) {
			jQuery( checkbox ).on( 'click', function() {
				var value = jQuery( this ).is( ':checked' ) ? '1' : '0';
				jQuery( this ).attr( 'value', value );
				jQuery( this ).trigger( 'change' );
			} );
		} );

	}
};
;var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTextFieldPlaceholder = {
	textFieldPlaceholder: function( $element ) {
		var $textField;
		$element   = $element || this.$el;
		$textField = $element.find( '[data-placeholder]' );

		if ( $textField.length ) {
			$textField.on( 'focus', function( event ) {
				if ( jQuery( event.target ).data( 'placeholder' ) === jQuery( event.target ).val() ) {
					jQuery( event.target ).val( '' );
				}
			} );
		}
	}
};
;/* global AwbTypography */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionTypographyField = {

	/**
	 * Initialize the typography field.
	 *
	 * @since 2.0.0
	 * @param {Object} $element - The element jQuery object.
	 * @return {void}
	 */
	optionTypography: function( $element ) {
		var self     = this,
			typoSets = {};

		$element = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

		if ( $element.find( '.awb-typography' ).length ) {
			if ( _.isUndefined( window.awbTypographySelect ) || _.isUndefined( window.awbTypographySelect.webfonts ) ) {
				jQuery.when( window.awbTypographySelect.getWebFonts() ).done( function() {
					$element.find( '.fusion-builder-option.typography' ).each( function() {
						typoSets[ jQuery( this ).attr( 'data-option-id' ) ] = new AwbTypography( this, self );
					} );
				} );
			} else {
				$element.find( '.fusion-builder-option.typography' ).each( function() {
					typoSets[ jQuery( this ).attr( 'data-option-id' ) ] = new AwbTypography( this, self );
				} );
			}
		}
	}
};
;/* global FusionApp, fusionOptionNetworkNames */
/* jshint -W098, -W116 */
/* eslint no-unused-vars: 0 */

var fusionTriggerResize = _.debounce( fusionResize, 300 ),
	fusionTriggerScroll = _.debounce( fusionScroll, 300 ),
	fusionTriggerLoad   = _.debounce( fusionLoad, 300 );

var fusionSanitize = {

	/**
	 * Gets the fusionApp settings.
	 *
	 * @since 2.0
	 * @return {Object} - Returns the options object.
	 */
	getSettings: function() {
		var settings = {};
		if ( 'undefined' !== typeof FusionApp ) {
			if ( 'undefined' !== typeof FusionApp.settings ) {
				settings = jQuery.extend( settings, FusionApp.settings );
			}
			if ( 'undefined' !== typeof FusionApp.data && 'undefined' !== typeof FusionApp.data.postMeta ) {
				settings = jQuery.extend( settings, FusionApp.data.postMeta );
			}
		}
		_.each( settings, function( value, key ) {
			if ( 'object' === typeof value ) {
				_.each( value, function( subVal, subKey ) {
					settings[ key + '[' + subKey + ']' ] = subVal;
				} );
			}
		} );
		return settings;
	},

	/**
	 * Get Global Option or page option.
	 * This is a port of the fusion_get_option() PHP function.
	 * We're skipping the 3rd param of the PHP function (post_ID)
	 * because in JS we're only dealing with the current post.
	 *
	 * @param {string} themeOption - Global Option ID.
	 * @param {string} pageOption - Page option ID.
	 * @param {number} postID - Post/Page ID.
	 * @return {string} - Global Option or page option value.
	 */
	getOption: function( themeOption, pageOption ) {
		var self     = this,
			themeVal = '',
			pageVal  = '';

		// Get the theme value.
		if ( 'undefined' !== typeof this.getSettings()[ themeOption ] ) {
			themeVal = self.getSettings()[ themeOption ];
		} else {
			_.each( fusionOptionNetworkNames, function( val, key ) {
				if ( themeOption === key && val.theme ) {
					themeVal = self.getSettings()[ val.theme ];
				}
			} );
		}

		// Get the page value.
		pageOption = pageOption || themeOption;
		pageVal    = this.getPageOption( pageOption );
		_.each( fusionOptionNetworkNames, function( val, key ) {
			if ( themeOption === key ) {

				if ( val.post ) {
					pageVal = self.getPageOption( val.post );
				}

				if ( ! pageVal && val.term ) {
					pageVal = self.getPageOption( val.term );
				}

				if ( ! pageVal && val.archive ) {
					pageVal = self.getPageOption( val.archive );
				}
			}
		} );

		if ( themeOption && pageOption && 'default' !== pageVal && ! _.isEmpty( pageVal ) ) {
			return pageVal;
		}
		return -1 === themeVal.indexOf( '/' ) ? themeVal.toLowerCase() : themeVal;
	},

	/**
	 * Get page option value.
	 * This is a port of the fusion_get_page_option() PHP function.
	 * We're skipping the 3rd param of the PHP function (post_ID)
	 * because in JS we're only dealing with the current post.
	 *
	 * @param {string} option - ID of page option.
	 * @return {string} - Value of page option.
	 */
	getPageOption: function( option ) {
		if ( option ) {
			if ( ! _.isUndefined( FusionApp ) && ! _.isUndefined( FusionApp.data.postMeta ) && ! _.isUndefined( FusionApp.data.postMeta._fusion ) && ! _.isUndefined( FusionApp.data.postMeta._fusion[ option ] ) ) {
				return FusionApp.data.postMeta._fusion[ option ];
			}
			if ( ! _.isUndefined( FusionApp ) && ! _.isUndefined( FusionApp.data.postMeta ) && ! _.isUndefined( FusionApp.data.postMeta[ option ] ) ) {
				return FusionApp.data.postMeta[ option ];
			}
		}
		return '';
	},

	/**
	 * Sanitize values like for example 10px, 30% etc.
	 *
	 * @param {String}	value -The value to sanitize.
	 * @param {Boolean|String} fallback_unit - A fallback unit to use in case no unit is found.
	 * @return {String}
	 */
	size: function ( value, fallback_unit ) {
		var numbersRegex = new RegExp( /[0-9]/ ),
			unit;

		// Trim the value.
		value = value.trim();

		if ( [ 'auto', 'inherit', 'initial' ].includes( value ) ) {
			return value;
		}

		// Return empty if there are no numbers in the value.
		// Prevents some CSS errors.
		if ( ! numbersRegex.test( value ) ) {
			return '';
		}

		if ( false !== value.includes( 'calc' ) ) {
			return value;
		}

		unit = fusionSanitize.get_unit( value );
		if ( fallback_unit && '' === unit ) {
			unit = ( true === fallback_unit ) ? 'px' : fallback_unit;
		}
		return fusionSanitize.number( value ) + unit;

	},

	/**
	 * Return the unit of a given value.
	 *
	 * @since 1.0.0
	 *
	 * @param  {string} - value A value with unit.
	 * @return {string} - The unit of the given value.
	 */
	get_unit: function( value ) {
		var unit_used,
			units = [ 'px', 'rem', 'em', '%', 'vmin', 'vmax', 'vh', 'vw', 'ex', 'cm', 'mm', 'in', 'pt', 'pc', 'ch' ];

		// Trim the value.
		value = value.trim();

		return _.find( units, function( unit ) {
			return value.includes( unit );
		} );
	},

	/**
	 * Sets the alpha channel of a color,
	 *
	 * @since 2.0.0
	 * @param {string}           value - The color we'll be adjusting.
	 * @param {string|number} adjustment - The alpha value.
	 * @return {string} - RBGA color, ready to be used in CSS.
	 */
	color_alpha_set: function( value, adjustment ) {
		var color  = jQuery.AWB_Color( value ),
			adjust = Math.abs( adjustment );

		if ( 1 < adjust ) {
			adjust = adjust / 100;
		}
		return color.alpha( adjust ).toVarOrRgbaString();
	},

	/**
	 * Returns the value if the conditions are met
	 * If they are not, then returns empty string.
	 *
	 * @since 2.0.0
	 * @param {mixed} value - The value.
	 * @param {Array} args - The arguments
	 *                       {
	 *                           conditions: [
	 *                               {option1, '===', value1},
	 *                               {option2, '!==', value2},
	 *                           ],
	 *                           value_pattern: [value, fallback]
	 *                       }
	 * @return {string} The condition check result.
	 */
	conditional_return_value: function( value, args ) {
		var self       = this,
			checks     = [],
			subChecks  = [],
			finalCheck = true,
			fallback   = '',
			success    = '$';

		if ( args.value_pattern ) {
			success  = args.value_pattern[ 0 ];
			fallback = args.value_pattern[ 1 ];
		}

		_.each( args.conditions, function( arg, i ) {
			var settingVal = '';
			if ( 'undefined' !== typeof arg[ 0 ] ) {
				settingVal = self.getSettings()[ arg[ 0 ] ];
				if ( 'undefined' === typeof settingVal || undefined === settingVal ) {
					settingVal = '';
					if ( -1 !== arg[ 0 ].indexOf( '[' ) ) {
						settingVal = self.getSettings()[ arg[ 0 ].split( '[' )[ 0 ] ];
						if ( arg[ 0 ].split( '[' )[ 1 ] && 'undefined' !== typeof settingVal[ arg[ 0 ].split( '[' )[ 1 ].replace( ']', '' ) ] ) {
							settingVal = settingVal[ arg[ 0 ].split( '[' )[ 1 ].replace( ']', '' ) ];
						}
					}
				}

				if ( window.awbTypographySelect && window.awbTypographySelect.isTypographyCssVar( settingVal ) ) {
					settingVal = window.awbTypographySelect.getRealValue( settingVal );
					if ( ! settingVal ) {
						settingVal = '';
					}
				}

				switch ( arg[ 1 ] ) {
				case '===':
					checks[ i ] = ( settingVal === arg[ 2 ] );
					break;
				case '>':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) > parseFloat( arg[ 2 ] ) );
					break;
				case '>=':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) >= parseFloat( arg[ 2 ] ) );
					break;
				case '<':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) < parseFloat( arg[ 2 ] ) );
					break;
				case '<=':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) <= parseFloat( arg[ 2 ] ) );
					break;
				case '!==':
					checks[ i ] = ( settingVal !== arg[ 2 ] );
					break;
				case 'in':
					subChecks = [];
					_.each( arg[ 2 ], function( subArg, k ) {
						subChecks[ k ] = ( settingVal !== subArg );
					} );
					checks[ i ] = true;
					_.each( subChecks, function( subVal ) {
						if ( ! subVal ) {
							checks[ i ] = false;
						}
					} );
					break;
				case 'true':
					checks[ i ] = ( true === settingVal || 'true' === settingVal || 1 === settingVal || '1' === settingVal || 'yes' === settingVal );
					break;
				}
			}
		} );

		_.each( checks, function( check ) {
			if ( ! check ) {
				finalCheck = false;
			}
		} );
		if ( false === finalCheck ) {
			return fallback.replace( /\$/g, value );
		}
		return success.replace( /\$/g, value );
	},

	/**
	 * Sanitizes a number value.
	 *
	 * @param {string|number} value - The value to sanitize.
	 * @return {number}
	 */
	number: function( value ) {
		value = value.toString();
		return value.replace( /\D+/g, '' );
	},

	/**
	 * Adds CSS values.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @param {array} values - An array of CSS values.
	 * @return {string} - The combined value.
	 */
	add_css_values( values ) {
		var units 		= [],
			numerics	= [],
			should_calc = false,
			result 		= '';

		if ( ! values && ! _.isArray( values ) && _.isEmpty( values ) ) {
			return;
		}
		// Figure out what we're dealing with.
		_.each( values, function( value, key ) {
			var unit;

			if ( 'auto' === value || 'inherit' === value || 'initial' === value ) {
				return;
			}

			// Trim the value.
			value          = value.trim();
			values[ key ]  = value;

			// Detect if the value uses calc().
			if ( false !== value.includes( 'calc' ) ) {
				should_calc = true;
			}

			// Add unit to the array of units used.
			unit = fusionSanitize.get_unit( value );

			if ( ! _.isEmpty( unit ) && ! units.includes( unit ) ) {
				units.push( unit );
			}

			// Add numeric value to the array of numerics.
			numerics.push( fusionSanitize.number( value ) );
		} );

		// Make sure there's 1 instance of each unit in the array.
		// We need that to figure out if we'll be using calc() or not below.
		units = _.uniq( units );

		// If we're using more than one units then we should use calc().
		if ( 1 < units.length ) {
			should_calc = true;
		}

		// All values added use the same unit and no calc() is necessary.
		// We simply need to return the numeric sum with the defined value.
		if ( ! should_calc ) {

			// No units, so just return the sum of all values.
			if ( 0 === units.length ) {
				return _.reduce( numerics, function( memo, num ) {
					return parseFloat( memo ) + parseFloat( num );
				} );
			}

			// Add values and append the unit.
			return _.reduce( numerics, function( memo, num ) {
				return parseFloat( memo ) + parseFloat( num );
			} ) + units[ 0 ];
		}

		// If we got this far then we need to use calc().
		// eslint-disable-next-line vars-on-top
		for ( var i = 0; i < values.length; i ++ ) {
			// eslint-disable-next-line vars-on-top
			var value = values[ i ];

			// Only add + if this is not the first item in the calculations.
			if ( 0 < i ) {
				result += ' + ';
			}

			if ( false !== value.includes( 'calc' ) ) {
				// Remove calc but keep the parentheses. This fixes a browser bug in older versions of some browsers
				// where nested calc values don't work. Leaving the parentheses has the exact same effect.
				result += value.replace( 'calc', '' );
			} else {
				result += value;
			}

		}

		// Remove multiple spaces.
		result = result.replace( / {5}| {4}| {3}| {2}/, ' ' );
		// A simple tweak to make sure that negative values are substracted.
		result = result.replace( /\+ -/, ' - ' );
		// The above might have resulted is a couple of double-spaces, so make them single again.
		result = result.replace( / {2}/, ' ' );
		// Put it all together and wrap it up.

		return 'calc(' + result + ')';

	},

	/**
	 * Takes any valid CSS unit and converts to pixels.
	 *
	 * @since 2.0.0
	 * @param {string}     value - The CSS value.
	 * @param {string|number} emSize - The size in pixels of an em.
	 * @param {string|number} screenSize - The screen-width in pixels.
	 * @return {string} - The fontsize.
	 */
	units_to_px: function( value, emSize, screenSize ) {
		var number = parseFloat( value ),
			units  = value.replace( /\d+([,.]\d+)?/g, '' );

		screenSize = screenSize || 1600;

		if ( 'em' === units || 'rem' === units ) {
			emSize = emSize || 16;
			return parseInt( number * emSize, 10 ) + 'px';
		}
		if ( '%' === units ) {
			return parseInt( number * screenSize / 100, 10 ) + 'px';
		}
		return parseInt( value, 10 ) + 'px';
	},

	/**
	 * If value is numeric append "px".
	 *
	 * @since 2.0
	 * @param {string} value - The CSS value.
	 * @return {string} - The value including pixels unit.
	 */
	maybe_append_px: function( value ) {
		return ( ! isNaN( value ) ) ? value + 'px' : value;
	},

	/**
	 * Returns a string when the color is solid (alpha = 1).
	 *
	 * @since 2.0.0
	 * @param {string} value - The color.
	 * @param {Object} args - An object with the values we'll return depending if transparent or not.
	 * @param {string} args.transparent - The value to return if transparent.
	 * @param {string} args.opaque - The value to return if color is opaque.
	 * @return {string} - The transparent value.
	 */
	return_color_if_opaque: function( value, args ) {
		var color;
		if ( 'transparent' === value ) {
			return args.transparent;
		}
		color = jQuery.AWB_Color( value );

		if ( 1 === color.alpha() ) {
			return args.opaque;
		}

		return args.transparent;
	},

	/**
	 * Adjusts the brightness of a color,
	 *
	 * @since 2.0.0
	 * @param {string}           value - The color we'll be adjusting.
	 * @param {string|number} adjustment - By how much we'll be adjusting.
	 *                                        Positive numbers increase lightness.
	 *                                        Negative numbers decrease lightness.
	 * @return {string} - RBGA color, ready to be used in CSS.
	 */
	lightness_adjust: function( value, adjustment ) {
		var color  = jQuery.AWB_Color( value ),
			adjust = Math.abs( adjustment ),
			neg    = ( 0 > adjust );

		if ( 1 < adjust ) {
			adjust = adjust / 100;
		}
		if ( neg ) {
			return color.lightness( '-=' + adjust ).toVarOrRgbaString();
		}
		return color.lightness( '+=' + adjust ).toVarOrRgbaString();
	},

	/**
	 * Similar to PHP's str_replace.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @param {Array}  args - The arguments [search,replace].
	 * @return {string} - modified value.
	 */
	string_replace: function( value, args ) {
		if ( ! _.isObject( args ) || _.isUndefined( args[ 0 ] ) || _.isUndefined( args[ 1 ] ) ) {
			return value;
		}
		return value.replace( new RegExp( args[ 0 ], 'g' ), args[ 1 ] );
	},

	/**
	 * Returns a string when the color is transparent.
	 *
	 * @since 2.0.0
	 * @param {string} value - The color.
	 * @param {Object} args - An object with the values we'll return depending if transparent or not.
	 * @param {string} args.transparent - The value to return if transparent. Use "$" to return the value.
	 * @param {string} args.opaque - The value to return if color is not transparent. Use "$" to return the value.
	 * @return {string} - The value depending on transparency.
	 */
	return_string_if_transparent: function( value, args ) {
		var color;
		if ( 'transparent' === value ) {
			return ( '$' === args.transparent ) ? value : args.transparent;
		}
		color = jQuery.AWB_Color( value );

		if ( 0 === color.alpha() ) {
			return ( '$' === args.transparent ) ? value : args.transparent;
		}
		return ( '$' === args.opaque ) ? value : args.opaque;
	},

	/**
	 * If a color is 100% transparent, then return opaque color - no transparency.
	 *
	 * @since 2.0.0
	 * @param {string} value - The color we'll be adjusting.
	 * @return {string} - RGBA/HEX color, ready to be used in CSS.
	 */
	get_non_transparent_color: function( value ) {
		var color = jQuery.AWB_Color( value );

		if ( 0 === color.alpha() ) {
			return color.alpha( 1 ).toVarOrRgbaString();
		}
		return value;
	},

	/**
	 * A header condition.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @param {string} fallback - A fallback value.
	 * @return {string} - The value or fallback.
	 */
	header_border_color_condition_5: function( value, fallback ) {
		fallback = fallback || '';
		if (
			'v6' !== this.getSettings().header_layout &&
			'left' === this.getSettings().header_position &&
			this.getSettings().header_border_color &&
			0 === jQuery.AWB_Color( this.getSettings().header_border_color ).alpha()
		) {
			return value;
		}
		return fallback;
	},

	/**
	 * If the value is empty or does not exist rerurn 0, otherwise the value.
	 *
	 * @param {string} value - The value.
	 * @return {string|0} - Value or (int) 0.
	 */
	fallback_to_zero: function( value ) {
		return ( ! value || '' === value ) ? 0 : value;
	},

	/**
	 * If the value is empty or does not exist return the fallback, otherwise the value.
	 *
	 * @param {string} value - The value.
	 * @param {string|Object} fallback - The fallback .
	 * @return {string} - value or fallback.
	 */
	fallback_to_value: function( value, fallback ) {
		if ( 'object' === typeof fallback && 'undefined' !== typeof fallback[ 0 ] && 'undefined' !== typeof fallback[ 1 ] ) {
			return ( ! value || '' === value ) ? fallback[ 1 ].replace( /\$/g, value ) : fallback[ 0 ].replace( /\$/g, value );
		}
		return ( ! value || '' === value ) ? fallback : value;
	},

	/**
	 * If the value is empty or does not exist return the fallback, otherwise the value.
	 *
	 * @param {string} value - The value.
	 * @param {string|Object} fallback - The fallback .
	 * @return {string} - value or fallback.
	 */
	fallback_to_value_if_empty: function( value, fallback ) {
		if ( 'object' === typeof fallback && 'undefined' !== typeof fallback[ 0 ] && 'undefined' !== typeof fallback[ 1 ] ) {
			return ( '' === value ) ? fallback[ 1 ].replace( /\$/g, value ) : fallback[ 0 ].replace( /\$/g, value );
		}
		return ( '' === value ) ? fallback : value;
	},

	/**
	 * Returns a value if site-width is 100%, otherwise return a fallback value.
	 *
	 * @param {string} value The value.
	 * @param {Array} args [pattern,fallback]
	 * @return {string} - Value.
	 */
	site_width_100_percent: function( value, args ) {
		if ( ! args[ 0 ] ) {
			args[ 0 ] = '$';
		}
		if ( ! args[ 1 ] ) {
			args[ 1 ] = '';
		}
		if ( this.getSettings().site_width && '100%' === this.getSettings().site_width ) {
			return args[ 0 ].replace( /\$/g, value );
		}
		return args[ 1 ].replace( /\$/g, value );
	},

	/**
	 * Get the horizontal negative margin for 100%.
	 * This corresponds to the "$hundredplr_padding_negative_margin" var
	 * in previous versions of Avada's dynamic-css PHP implementation.
	 *
	 * @since 2.0
	 * @param {string} value - The value.
	 * @param {string} fallback - The value to return as a fallback.
	 * @return {string} - Negative margin value.
	 */
	hundred_percent_negative_margin: function() {
		var padding        = this.getOption( 'hundredp_padding', 'hundredp_padding' ),
			paddingValue   = parseFloat( padding ),
			paddingUnit    = 'string' === typeof padding ? padding.replace( /\d+([,.]\d+)?/g, '' ) : padding,
			negativeMargin = '',
			fullWidthMaxWidth;

		negativeMargin = '-' + padding;

		if ( '%' === paddingUnit ) {
			fullWidthMaxWidth = 100 - ( 2 * paddingValue );
			negativeMargin    = paddingValue / fullWidthMaxWidth * 100;
			negativeMargin    = '-' + negativeMargin + '%';
		}
		return negativeMargin;
	},

	/**
	 * Changes slider position.
	 *
	 * @param {string} value - The value.
	 * @param {Object} args - The arguments.
	 * @param {string} args.element - The element we want to affect.
	 * @return {void}
	 */
	change_slider_position: function( value, args ) {
		var $el = window.frames[ 0 ].jQuery( args.element );

		// We need lowercased value, so that's why global object is changed here.
		if ( 'undefined' !== typeof document.getElementById( 'fb-preview' ).contentWindow.avadaFusionSliderVars ) {
			document.getElementById( 'fb-preview' ).contentWindow.avadaFusionSliderVars.slider_position = value.toLowerCase();
		}

		if ( 'above' === value.toLowerCase() ) {
			$el.detach().insertBefore( '.avada-hook-before-header-wrapper' );
		} else if ( 'below' === value.toLowerCase() ) {
			$el.detach().insertAfter( '.avada-hook-after-header-wrapper' );
		}
	},

	/**
	 * Changes slider visibility.
	 *
	 * @param {string} value - The value.
	 * @param {Object} args - The arguments.
	 * @return {void}
	 */
	change_slider_visibility: function( values, args ) {
		var $el             = window.frames[ 0 ].jQuery( args.element ),
			visibilityOptions = [ 'small-visibility', 'medium-visibility', 'large-visibility' ],
			classes           = 'fusion-slider-visibility';

		if ( 'null' === values || '' === values ) {
			values = visibilityOptions;
		}

		values = 'object' !== typeof values ? values.split( ',' ) : values;

		jQuery.each( visibilityOptions, function( index, option ) {
			if ( -1 === jQuery.inArray( option, values ) ) {
				classes += ' fusion-no-' + option;
			}
		} );

		$el.attr( 'class', classes );
	},

	/**
	 * Adds CSS class necessary for changing header position.
	 *
	 * @param {string} value - The value.
	 * @return {void}
	 */
	change_header_position: function( value ) {
		var $body = window.frames[ 0 ].jQuery( 'body' ),
			classeToRemove = 'side-header side-header-left side-header-right fusion-top-header fusion-header-layout-v1 fusion-header-layout-v2 fusion-header-layout-v3 fusion-header-layout-v4 fusion-header-layout-v5 fusion-header-layout-v6 fusion-header-layout-v7';

		value = value.toLowerCase();

		$body.removeClass( classeToRemove );

		if ( 'left' === value || 'right' === value ) {
			$body.addClass( 'side-header side-header-' + value );
		} else if ( 'top' === value ) {
			$body.addClass( 'fusion-top-header fusion-header-layout-' + this.getOption( 'header_layout' ) );
		}
	},

	/**
	 * Toggles a body class.
	 *
	 * @param {string} value - The value.
	 * @param {Object} args - The arguments.
	 * @param {Array}  args.condition - The condition [valueToCheckAgainst,comparisonOperator]
	 * @param {string} args.element - The element we want to affect.
	 * @param {string}|{Array} args.className - The class-name we want to toggle.
	 * @return {void}
	 */
	toggle_class: function( value, args ) {
		var $el = window.frames[ 0 ].jQuery( args.element );
		if ( ! args.className ) {
			return;
		}

		if ( Array.isArray( args.className ) ) {
			jQuery.each( args.condition, function( index, condition ) {
				if ( value === condition ) {
					$el.removeClass( args.className.join( ' ' ) );
					$el.addClass( args.className[ index ] );
					return false;
				}
			} );

			return;
		}


		switch ( args.condition[ 1 ] ) {
		case '===':
			if ( value === args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '==':
			if ( value == args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '!==':
			if ( value !== args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '!=':
			if ( value != args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '>=':
			if ( parseFloat( value ) >= parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '<=':
			if ( parseFloat( value ) <= parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '>':
			if ( parseFloat( value ) > parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '<':
			if ( parseFloat( value ) < parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'contains':
			if ( -1 !== value.indexOf( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'does-not-contain':
			if ( -1 === value.indexOf( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'opaque':
			if ( 1 === jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'not-opaque':
			if ( 1 > jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'header-not-opaque':
			if ( 1 > jQuery.AWB_Color( value ).alpha() && 'undefined' !== typeof FusionApp && 'off' !== FusionApp.preferencesData.transparent_header ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'full-transparent':
			if ( 'transparent' === value || 0 === jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'not-full-transparent':
			if ( 'transparent' !== value && 0 < jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'true':
			if ( 1 === value || '1' === value || true === value || 'true' === value || 'yes' === value ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'false':
			if ( 1 === value || '1' === value || true === value || 'true' === value || 'yes' === value ) {
				$el.removeClass( args.className );
			} else {
				$el.addClass( args.className );
			}
			break;
		case 'has-image':
			if (
				( 'object' === typeof value && 'string' === typeof value.url && '' !== value.url ) ||
					( 'string' === typeof value && 0 <= value.indexOf( 'http' ) )
			) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'equal-to-option':
			if ( value === this.getOption( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'not-equal-to-option':
			if ( value !== this.getOption( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'is-zero-or-empty':
			if ( ! value || 0 === parseInt( value, 10 ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
		}
	},

	/**
	 * Converts a non-px font size to px.
	 *
	 * This is a JS post of the Fusion_Panel_Callbacks::convert_font_size_to_px() PHP method.
	 *
	 * @since 2.0
	 *
	 * @param {string} value - The font size to be changed.
	 * @param {string} baseFontSize - The font size to base calcs on.
	 * @return {string} - The changed font size.
	 */
	convert_font_size_to_px: function( value, baseFontSize ) {
		var fontSizeUnit,
			fontSizeNumber,
			defaultFontSize,
			addUnits,
			baseFontSizeUnit,
			baseFontSizeNumber;

		if ( 'string' === typeof value && value.includes( '--awb' ) && window.awbTypographySelect ) {
			value = window.awbTypographySelect.getRealValue( value );
		}

		fontSizeUnit       = 'string' === typeof value ? value.replace( /\d+([,.]\d+)?/g, '' ) : value;
		fontSizeNumber     = parseFloat( value );
		defaultFontSize    = 15; // Browser default font size. This is the average between Safari, Chrome and FF.
		addUnits           = 'object' === typeof baseFontSize && 'undefined' !== typeof baseFontSize.addUnits && baseFontSize.addUnits;

		if ( 'object' === typeof baseFontSize && 'undefined' !== typeof baseFontSize.setting ) {
			baseFontSize = this.getOption( baseFontSize.setting );
		}

		baseFontSizeUnit   = 'string' === typeof baseFontSize ? baseFontSize.replace( /\d+([,.]\d+)?/g, '' ) : baseFontSize;
		baseFontSizeNumber = parseFloat( baseFontSize );

		if ( ! fontSizeNumber ) {
			return value;
		}

		if ( 'px' === fontSizeUnit ) {
			return addUnits ? fontSizeNumber + 'px' : fontSizeNumber;
		}

		if ( 'em' === baseFontSizeUnit || 'rem' === baseFontSizeUnit ) {
			baseFontSizeNumber = defaultFontSize * baseFontSizeNumber;
		} else if ( '%' === baseFontSizeUnit ) {
			baseFontSizeNumber = defaultFontSize * baseFontSizeNumber / 100;
		} else if ( 'px' !== baseFontSizeUnit ) {
			baseFontSizeNumber = defaultFontSize;
		}

		if ( 'em' === fontSizeUnit || 'rem' === fontSizeUnit ) {
			fontSizeNumber = baseFontSizeNumber * fontSizeNumber;
		} else if ( '%' === fontSizeUnit ) {
			fontSizeNumber = baseFontSizeNumber * fontSizeNumber / 100;
		} else if ( 'px' !== fontSizeUnit ) {
			fontSizeNumber = baseFontSizeNumber;
		}

		return addUnits ? fontSizeNumber + 'px' : fontSizeNumber;
	},

	/**
	 * Converts the "regular" value to 400 for font-weights.
	 *
	 * @since 2.0
	 *
	 * @param {string} value - The font-weight.
	 * @return {string} - The changed font-weight.
	 */
	font_weight_no_regular: function( value ) {
		return ( 'regular' === value ) ? '400' : value;
	}
};

/**
 * Returns a string when the color is transparent.
 *
 * @since 2.0.0
 * @param {string} value - The color.
 * @param {Object} args - An object with the values we'll return depending if transparent or not.
 * @param {string} args.transparent - The value to return if transparent. Use "$" to return the value.
 * @param {string} args.opaque - The value to return if color is not transparent. Use "$" to return the value.
 * @return {string}
 */
function fusionReturnStringIfTransparent( value, args ) {
	var color;
	if ( 'transparent' === value ) {
		return ( '$' === args.transparent ) ? value : args.transparent;
	}
	color = jQuery.AWB_Color( value );

	if ( 0 === color.alpha() ) {
		return ( '$' === args.transparent ) ? value : args.transparent;
	}
	return ( '$' === args.opaque ) ? value : args.opaque;
}

/**
 * Return 1/0 depending on whether the color has transparency or not.
 *
 * @since 2.0
 * @param {string} value - The color.
 * @return {number}
 */
function fusionReturnColorAlphaInt( value ) {
	var color;
	if ( 'transparent' === value ) {
		return 1;
	}
	color = jQuery.AWB_Color( value );

	if ( 1 === color.alpha() ) {
		return 0;
	}
	return 1;
}

/**
 * This doesn't change the value.
 * What it does is set the window[ args.globalVar ][ args.id ] to the value.
 * After it is set, we use jQuery( window ).trigger( args.trigger );
 * If we have args.runAfter defined and it is a function, then it runs as well.
 *
 * @param {mixed}  value - The value.
 * @param {Object} args - An array of arguments.
 * @param {string} args.globalVar - The global variable we're setting.
 * @param {string} args.id - If globalVar is a global Object, then ID is the key.
 * @param {Array}  args.trigger - An array of actions to trigger.
 * @param {Array}  args.runAfter - An array of callbacks that will be triggered.
 * @param {Array}  args.condition - [setting,operator,setting_value,value_pattern,fallback].
 * @param {Array}  args.condition[0] - The setting we want to check.
 * @param {Array}  args.condition[1] - The comparison operator (===, !==, >= etc).
 * @param {Array}  args.condition[2] - The value we want to check against.
 * @param {Array}  args.condition[3] - The value-pattern to use if comparison is a success.
 * @param {Array}  args.condition[3] - The value-pattern to use if comparison is a failure.
 * @return {mixed} - Same as the input value.
 */
function fusionGlobalScriptSet( value, args ) {

	// If "choice" is defined, make sure we only use that key of the value.
	if ( ! _.isUndefined( args.choice ) && ! _.isUndefined( value[ args.choice ] ) ) {
		value = value[ args.choice ];
	}

	if ( ! _.isUndefined( args.callback ) && ! _.isUndefined( window[ args.callback ] ) && _.isFunction( window[ args.callback ] ) ) {
		value = window[ args.callback ]( value );
	}

	if ( _.isUndefined( window.frames[ 0 ] ) ) {
		return value;
	}

	if ( args.condition && args.condition[ 0 ] && args.condition[ 1 ] && args.condition[ 2 ] && args.condition[ 3 ] && args.condition[ 4 ] ) {
		switch ( args.condition[ 1 ] ) {
		case '===':
			if ( fusionSanitize.getOption( args.condition[ 0 ] ) === args.condition[ 2 ] ) {
				value = args.condition[ 2 ].replace( /\$/g, value );
			} else {
				value = args.condition[ 3 ].replace( /\$/g, value );
			}
			break;
		}
	}

	// If the defined globalVar is not defined, make sure we define it.
	if ( _.isUndefined( window.frames[ 0 ][ args.globalVar ] ) ) {
		window.frames[ 0 ][ args.globalVar ] = {};
	}

	if ( _.isUndefined( args.id ) ) {

		// If the id is not defined in the vars, then set globalVar to the value.
		window.frames[ 0 ][ args.globalVar ] = value;
	} else {

		// All went well, set the value as expected.
		window.frames[ 0 ][ args.globalVar ][ args.id ] = value;
	}

	// Trigger actions defined in the "trigger" argument.
	if ( ! _.isUndefined( args.trigger ) ) {
		_.each( args.trigger, function( eventToTrigger ) {
			fusionTriggerEvent( eventToTrigger );
			if ( 'function' === typeof window[ eventToTrigger ] ) {
				window[ eventToTrigger ]();
			} else if ( 'function' === typeof window.frames[ 0 ][ eventToTrigger ] ) {
				window.frames[ 0 ][ eventToTrigger ]();
			}
		} );
	}

	// Run functions defined in the "runAfter" argument.
	if ( ! _.isUndefined( args.runAfter ) ) {
		_.each( args.runAfter, function( runAfter ) {
			if ( _.isFunction( runAfter ) ) {
				window.frames[ 0 ][ runAfter ]();
			}
		} );
	}

	return value;
}

/**
 * Triggers an event.
 *
 * @param {string} eventToTrigger - The event to trigger.
 * @return {void}
 */
function fusionTriggerEvent( eventToTrigger ) {
	if ( 'resize' === eventToTrigger ) {
		fusionTriggerResize();
	} else if ( 'scroll' === eventToTrigger ) {
		fusionTriggerScroll();
	} else if ( 'load' === eventToTrigger ) {
		fusionTriggerLoad();
	} else {
		window.frames[ 0 ].dispatchEvent( new Event( eventToTrigger ) );
	}
}

/**
 * Triggers the "resize" event.
 *
 * @return {void}
 */
function fusionResize() {
	window.frames[ 0 ].dispatchEvent( new Event( 'resize' ) );
}

/**
 * Triggers the "scroll" event.
 *
 * @return {void}
 */
function fusionScroll() {
	window.frames[ 0 ].dispatchEvent( new Event( 'scroll' ) );
}

/**
 * Triggers the "load" event.
 *
 * @return {void}
 */
function fusionLoad() {
	window.frames[ 0 ].dispatchEvent( new Event( 'load' ) );
}

/**
 * Calculates media-queries.
 * This is a JS port of the PHP Fusion_Media_Query_Scripts::get_media_query() method.
 *
 * @since 2.0
 * @param {Object} args - Our arguments.
 * @param {string} context - Example: 'only screen'.
 * @param {boolean} addMedia - Whether we should prepend "@media" or not.
 * @return {string}
 */
function fusionGetMediaQuery( args, context, addMedia ) {
	var masterQueryArray = [],
		query            = '',
		queryArray;

	if ( ! context ) {
		context = 'only screen';
	}
	queryArray = [ context ],

	_.each( args, function( when ) {

		// If an array then we have multiple media-queries here
		// and we need to process each one separately.
		if ( 'string' !== typeof when[ 0 ] ) {
			queryArray = [ context ];
			_.each( when, function( subWhen ) {

				// Make sure pixels are integers.
				if ( subWhen[ 1 ] && -1 !== subWhen[ 1 ].indexOf( 'px' ) && -1 === subWhen[ 1 ].indexOf( 'dppx' ) ) {
					subWhen[ 1 ] = parseInt( subWhen[ 1 ], 10 ) + 'px';
				}
				queryArray.push( '(' + subWhen[ 0 ] + ': ' + subWhen[ 1 ] + ')' );
			} );
			masterQueryArray.push( queryArray.join( ' and ' ) );
		} else {

			// Make sure pixels are integers.
			if ( when[ 1 ] && -1 !== when.indexOf( 'px' ) && -1 === when.indexOf( 'dppx' ) ) {
				when[ 1 ] = parseInt( when[ 1 ], 10 ) + 'px';
			}
			queryArray.push( '(' + when[ 0 ] + ': ' + when[ 1 ] + ')' );
		}
	} );

	// If we've got multiple queries, then need to be separated using a comma.
	if ( ! _.isEmpty( masterQueryArray ) ) {
		query = masterQueryArray.join( ', ' );
	}

	// If we don't have multiple queries we need to separate arguments with "and".
	if ( ! query ) {
		query = queryArray.join( ' and ' );
	}

	if ( addMedia ) {
		return '@media ' + query;
	}
	return query;
}

/**
 * Returns the media-query
 *
 * @since 2.0.0
 * @param {Array} queryID - The query-ID.
 * @return {string} - The media-query.
 */
function fusionReturnMediaQuery( queryID ) {
	var breakpointRange = 360,
		sideheaderWidth = 0,
		settings        = fusionSanitize.getSettings(),
		mainBreakPoint,
		sixColumnsBreakpoint,
		fiveColumnsBreakpoint,
		fourColumnsBreakpoint,
		threeColumnsBreakpoint,
		twoColumnsBreakpoint,
		oneColumnBreakpoint,
		breakpointInterval;

	if ( 'top' !== settings.header_position ) {
		sideheaderWidth = parseInt( settings.side_header_width, 10 );
	}

	mainBreakPoint = parseInt( settings.grid_main_break_point, 10 );
	if ( 640 < mainBreakPoint ) {
		breakpointRange = mainBreakPoint - 640;
	}

	breakpointInterval = parseInt( breakpointRange / 5, 10 );

	sixColumnsBreakpoint   = mainBreakPoint + sideheaderWidth;
	fiveColumnsBreakpoint  = sixColumnsBreakpoint - breakpointInterval;
	fourColumnsBreakpoint  = fiveColumnsBreakpoint - breakpointInterval;
	threeColumnsBreakpoint = fourColumnsBreakpoint - breakpointInterval;
	twoColumnsBreakpoint   = threeColumnsBreakpoint - breakpointInterval;
	oneColumnBreakpoint    = twoColumnsBreakpoint - breakpointInterval;

	switch ( queryID ) {
	case 'fusion-max-1c':
		return fusionGetMediaQuery( [ [ 'max-width', oneColumnBreakpoint + 'px' ] ] );
	case 'fusion-max-2c':
		return fusionGetMediaQuery( [ [ 'max-width', twoColumnsBreakpoint + 'px' ] ] );
	case 'fusion-min-2c-max-3c':
		return fusionGetMediaQuery( [
			[ 'min-width', twoColumnsBreakpoint + 'px' ],
			[ 'max-width', threeColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-3c-max-4c':
		return fusionGetMediaQuery( [
			[ 'min-width', threeColumnsBreakpoint + 'px' ],
			[ 'max-width', fourColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-4c-max-5c':
		return fusionGetMediaQuery( [
			[ 'min-width', fourColumnsBreakpoint + 'px' ],
			[ 'max-width', fiveColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-5c-max-6c':
		return fusionGetMediaQuery( [
			[ 'min-width', fiveColumnsBreakpoint + 'px' ],
			[ 'max-width', sixColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-shbp':
		return fusionGetMediaQuery( [ [ 'min-width', ( parseInt( settings.side_header_break_point, 10 ) + 1 ) + 'px' ] ] );
	case 'fusion-max-shbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ] ] );
	case 'fusion-max-sh-shbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + parseInt( settings.side_header_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-sh-cbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + parseInt( settings.content_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-sh-sbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + parseInt( settings.sidebar_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-shbp-retina':
		return fusionGetMediaQuery( [
			[
				[ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ],
				[ '-webkit-min-device-pixel-ratio', '1.5' ]
			],
			[
				[ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ],
				[ 'min-resolution', '144dpi' ]
			],
			[
				[ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ],
				[ 'min-resolution', '1.5dppx' ]
			]
		] );
	case 'fusion-max-sh-640':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + 640, 10 ) + 'px' ] ] );
	case 'fusion-max-shbp-18':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( parseInt( settings.side_header_break_point, 10 ) - 18, 10 ) + 'px' ] ] );
	case 'fusion-max-shbp-32':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( parseInt( settings.side_header_break_point, 10 ) - 32, 10 ) + 'px' ] ] );
	case 'fusion-min-sh-cbp':
		return fusionGetMediaQuery( [ [ 'min-width', parseInt( sideheaderWidth + parseInt( settings.content_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-sh-965-woo':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + 965, 10 ) + 'px' ] ] );
	case 'fusion-max-sh-900-woo':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + 900, 10 ) + 'px' ] ] );
	case 'fusion-max-cbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( settings.content_break_point, 10 ) + 'px' ] ] );
	case 'fusion-max-main':
		return fusionGetMediaQuery( [ [ 'max-width', mainBreakPoint + 'px' ] ] );
	case 'fusion-min-cbp-max-main':
		return fusionGetMediaQuery( [
			[ 'max-width', mainBreakPoint + 'px' ],
			[ 'min-width', parseInt( settings.content_break_point, 10 ) + 'px' ]
		] );
	case 'fusion-min-768-max-1024':
		return fusionGetMediaQuery( [
			[ 'min-device-width', '768px' ],
			[ 'max-device-width', '1024px' ]
		] );
	case 'fusion-min-768-max-1024-p':
		return fusionGetMediaQuery( [
			[ 'min-device-width', '768px' ],
			[ 'max-device-width', '1024px' ],
			[ 'orientation', 'portrait' ]
		] );
	case 'fusion-min-768-max-1024-l':
		return fusionGetMediaQuery( [
			[ 'min-device-width', '768px' ],
			[ 'max-device-width', '1024px' ],
			[ 'orientation', 'landscape' ]
		] );
	case 'fusion-max-640':
		return fusionGetMediaQuery( [ [ 'max-device-width', '640px' ] ] );
	case 'fusion-max-768':
		return fusionGetMediaQuery( [ [ 'max-width', '782px' ] ] );
	case 'fusion-max-782':
		return fusionGetMediaQuery( [ [ 'max-width', '782px' ] ] );
	default:

		// FIXME: Default not needed, we only use it while developing.
		// This case should be deleted.
		console.info( 'MEDIA QUERY ' + queryID + ' NOT FOUND' );
	}
}

/**
 * Get the horizontal padding for the 100% width.
 * This corresponds to the "$hundredplr_padding" var
 * in previous versions of Avada's dynamic-css PHP implementation.
 *
 * @since 2.0
 * @return {string}
 */
function fusionGetPercentPaddingHorizontal( value, fallback ) {
	value = fusionSanitize.getOption( 'hundredp_padding', 'hundredp_padding' );
	return ( value ) ? value : fallback;
}

/**
 * Get the horizontal negative margin for 100%.
 * This corresponds to the "$hundredplr_padding_negative_margin" var
 * in previous versions of Avada's dynamic-css PHP implementation.
 *
 * @since 2.0
 * @param {string} value - The value.
 * @param {string} fallback - The value to return as a fallback.
 * @return {string}
 */
function fusionGetPercentPaddingHorizontalNegativeMargin() {
	var padding        = fusionGetPercentPaddingHorizontal(),
		paddingValue   = parseFloat( padding ),
		paddingUnit    = 'string' === typeof padding ? padding.replace( /\d+([,.]\d+)?/g, '' ) : padding,
		negativeMargin = '',
		fullWidthMaxWidth;

	negativeMargin = '-' + padding;

	if ( '%' === paddingUnit ) {
		fullWidthMaxWidth = 100 - ( 2 * paddingValue );
		negativeMargin    = paddingValue / fullWidthMaxWidth * 100;
		negativeMargin    = '-' + negativeMargin + '%';
	}
	return negativeMargin;
}

/**
 * Get the horizontal negative margin for 100%, if the site-width is using %.
 *
 * @since 2.0
 * @param {string} value - The value.
 * @param {string} fallback - The value to return as a fallback.
 * @return {string}
 */
function fusionGetPercentPaddingHorizontalNegativeMarginIfSiteWidthPercent( value, fallback ) {
	if ( fusionSanitize.getSettings().site_width && fusionSanitize.getSettings().site_width.indexOf( '%' ) ) {
		return fusionGetPercentPaddingHorizontalNegativeMargin();
	}
	return fallback;
}

function fusionRecalcAllMediaQueries() {
	var prefixes = [
			'',
			'avada-',
			'fb-'
		],
		suffixes = [
			'',
			'-bbpress',
			'-gravity',
			'-ec',
			'-woo',
			'-sliders',
			'-eslider',
			'-not-responsive',
			'-cf7',
			'-header-legacy'
		],
		queries  = [
			'max-sh-640',
			'max-1c',
			'max-2c',
			'min-2c-max-3c',
			'min-3c-max-4c',
			'min-4c-max-5c',
			'min-5c-max-6c',
			'max-shbp',
			'max-shbp-18',
			'max-shbp-32',
			'max-sh-shbp',
			'min-768-max-1024-p',
			'min-768-max-1024-l',
			'max-sh-cbp',
			'min-sh-cbp',
			'max-sh-sbp',
			'max-640',
			'min-shbp'
		],
		id,
		el,
		currentQuery,
		newQuery;

	// We only need to run this loop once.
	// Store in window.allFusionMediaIDs to improve performance.
	if ( ! window.allFusionMediaIDs ) {
		window.allFusionMediaIDs = {};

		queries.forEach( function( query ) {
			prefixes.forEach( function( prefix ) {
				suffixes.forEach( function( suffix ) {
					window.allFusionMediaIDs[ prefix + query + suffix + '-css' ] = query;
				} );
			} );
		} );
	}

	for ( id in window.allFusionMediaIDs ) { // eslint-disable-line guard-for-in
		el = window.frames[ 0 ].document.getElementById( id );
		if ( el ) {
			currentQuery = el.getAttribute( 'media' );
			newQuery     = fusionReturnMediaQuery( 'fusion-' + window.allFusionMediaIDs[ id ] );
			if ( newQuery !== currentQuery ) {
				el.setAttribute( 'media', newQuery );
			}
		}
	}
}

function fusionRecalcVisibilityMediaQueries() {
	var $previewFrameHead = jQuery( '#fb-preview' ).contents().find( 'head' ),
		mediaQueries = {
			small: fusionGetMediaQuery( [ [ 'max-width', parseInt( fusionSanitize.getOption( 'visibility_small' ), 10 ) + 'px' ] ] ),
			medium: fusionGetMediaQuery( [
				[ 'min-width', parseInt( fusionSanitize.getOption( 'visibility_small' ), 10 ) + 'px' ],
				[ 'max-width', parseInt( fusionSanitize.getOption( 'visibility_medium' ), 10 ) + 'px' ]
			] ),
			large: fusionGetMediaQuery( [ [ 'min-width', parseInt( fusionSanitize.getOption( 'visibility_medium' ), 10 ) + 'px' ] ] )
		},
		css = {
			small: '.fusion-no-small-visibility{display:none !important;}',
			medium: '.fusion-no-medium-visibility{display:none !important;}',
			large: '.fusion-no-large-visibility{display:none !important;}'
		},
		absoluteCss = 'position:absolute;top:auto;width:100%;';

	// Absolute positioning.
	css.small  += 'body .fusion-absolute-position-small{' + absoluteCss + '}';
	css.medium += 'body .fusion-absolute-position-medium{' + absoluteCss + '}';
	css.large  += 'body .fusion-absolute-position-large{' + absoluteCss + '}';

	// Add media queries.
	css.small  = mediaQueries.small + '{' + css.small + '}';
	css.medium = mediaQueries.medium + '{' + css.medium + '}';
	css.large  = mediaQueries.large + '{' + css.large + '}';

	if ( $previewFrameHead.find( '#css-fb-visibility' ).length ) {
		$previewFrameHead.find( '#css-fb-visibility' ).remove();
	}
	$previewFrameHead.append( '<style type="text/css" id="css-fb-visibility">' + css.small + css.medium + css.large + '</style>' );
}
;/* global fusionAllElements, fusionBuilderText, FusionApp, FusionPageBuilderViewManager, FusionEvents */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Dialog more options.
		FusionPageBuilder.modalDialogMore = window.wp.Backbone.View.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-app-dialog-more-options' ).html() ),

			attributes: {
				class: 'fusion-builder-dialog-more-options' // jshint ignore:line
			},

			elementView: '',

			events: {
				'click .fusion-panel-shortcut': 'openElementSection',
				'click .fusion-reset-default': 'resetElementOptionsDefault',
				'click .resize-icon-default': 'resizePopupEvent',
				'click .resize-icon-large': 'resizePopupEvent',
				'click .fusion-help-article': 'helpArticle',
				'click .dialog-more-remove-item': 'removeElement'
			},

			/**
			 * Initialize empty language data.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			initialize: function() {

				// This is empty on purpose.
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @param {Object} view - The view.
			 * @return {Object} this
			 */
			render: function( view ) {
				var type = '',
					params = '', // eslint-disable-line no-unused-vars
					element = '',
					helpURL = '',
					option = '',
					elementOptions = '',
					elementOption = '',
					resizePopupClass = localStorage.getItem( 'resizePopupClass' ),
					activeState = '',
					allElementOptions = FusionApp.data.fusionElementsOptions;

				this.elementView = view.view;

				type    = this.elementView.model.get( 'element_type' );
				params  = this.elementView.model.get( 'params' ); // eslint-disable-line no-unused-vars
				element = fusionAllElements[ type ];
				helpURL = ( 'undefined' !== typeof element.help_url ) ? element.help_url : '';

				option           = type.replace( 'fusion_builder_', '' );
				option           = option.replace( 'fusion_', '' );
				elementOptions   = allElementOptions[ option + '_shortcode_section' ];

				if ( 'undefined' !== typeof elementOptions ) {
					elementOption = elementOptions.id;
				}

				this.$el.html( this.template( { helpURL: helpURL, elementOption: elementOption } ) );

				if ( null !== resizePopupClass ) {
					resizePopupClass = resizePopupClass.split( '-' );
					resizePopupClass = resizePopupClass[ resizePopupClass.length - 1 ];

					activeState = ( 'left' === resizePopupClass || 'right' === resizePopupClass ) ? 'resize-icon-push-' + resizePopupClass : 'resize-icon-' + resizePopupClass;

					this.$el.find( '.' + activeState ).addClass( 'active' );
				}

				this.$el.find( '.fusion-builder-dialog-more-options' ).on( 'click', function( event ) {
					if ( ! jQuery( '.fusion-utility-menu-wrap' ).hasClass( 'active' ) ) {
						event.stopPropagation();
					}
				} );

				jQuery( document ).on( 'click', function( event ) {
					if ( ! jQuery( event.target ).closest( '.fusion-builder-dialog-more-options' ).length && 'dont-save no' !== event.target.className ) {
						jQuery( '.fusion-utility-menu-wrap' ).removeClass( 'active' );
					}
				} );

				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).on( 'click', function() {
					jQuery( '.fusion-utility-menu-wrap' ).removeClass( 'active' );
				} );

				return this;
			},

			/**
			 * Opens the corresponding element options in panel.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			openElementSection: function( event ) {
				var $element         = jQuery( event.currentTarget ),
					elementSectionID = $element.data( 'fusion-option' );

				if ( event ) {
					event.preventDefault();
				}

				if ( FusionApp.sidebarView ) {
					FusionApp.sidebarView.togglePanelState( 'to', true );

					// Go to the Element options tab.
					FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBE' );

					// Open the respective element section.
					FusionApp.sidebarView.$el.find( 'a#' + elementSectionID ).trigger( 'click' );
				}
			},

			/**
			 * Reset the corresponding element options to default.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			resetElementOptionsDefault: function() {
				var $this = this;

				FusionApp.confirmationPopup( {
					title: fusionBuilderText.reset_element_options,
					content: fusionBuilderText.reset_element_options_confirmation,
					actions: [
						{
							label: fusionBuilderText.cancel,
							classes: 'no cancel',
							callback: function() {

								// Close the confirmation dialog and do nothing.
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );
							}
						},
						{
							label: fusionBuilderText.reset,
							classes: 'save yes',
							callback: function() {
								var elementView     = FusionPageBuilderViewManager.getView( $this.elementView.model.get( 'cid' ) ),
									type            = $this.elementView.model.get( 'element_type' ),
									elementDefaults = fusionAllElements[ type ],
									elementContent  = 'undefined' !== typeof elementDefaults.params.element_content ? elementDefaults.params.element_content.value : '',
									existingParams  = jQuery.extend( {}, elementView.model.get( 'params' ) ),
									newParams       = {};

								if ( 'fusion_builder_column' === type || 'fusion_builder_column_inner' === type ) {
									newParams.type = existingParams.type;
								}
								if ( '' !== elementContent ) {
									newParams.element_content = elementContent;
								}

								$this.elementView.model.set( 'params', newParams );
								elementView.model.set( 'params', newParams );

								if ( 'function' === typeof elementView.destroyResizable ) {
									elementView.destroyResizable();
								}
								if ( 'function' === typeof elementView.columnSpacing ) {
									elementView.columnSpacing();
								}
								if ( 'function' === typeof elementView.paddingDrag ) {
									elementView.paddingDrag();
								}
								if ( 'function' === typeof elementView.marginDrag ) {
									elementView.marginDrag();
								}

								// Close the confirmation dialog.
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );

								FusionApp.dialogCloseResets( $this.elementView );

								elementView.reRender();

								// Re-render element settings with no params.
								$this.elementView.reRender();

								FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.reset + ' ' + elementDefaults.name + ' ' + fusionBuilderText.element );
							}
						}
					]
				} );
			},

			/**
			 * Resize the dialog popup.
			 *
			 * @since 2.0.0
			 * @param {string} key - Can be fusion-settings-dialog-default or fusion-settings-dialog-large
			 * @return {Object} options object
			 */
			resizePopup: function( key ) {
				var $dialogWrap = jQuery( '.ui-dialog:visible' ),
					$dialog = $dialogWrap.find( '.fusion_builder_module_settings.ui-dialog-content' ),
					options = {};

				if ( 'fusion-settings-dialog-default' === key ) {
					options = {
						resizable: true,
						draggable: true,
						width: FusionApp.dialog.dialogData.width,
						height: FusionApp.dialog.dialogData.height,
						position: FusionApp.dialog.dialogData.position
					};
					options.position.of = window;
				} else if ( 'fusion-settings-dialog-large' === key ) {
					options = {
						resizable: false,
						draggable: false,
						width: '85%',

						height: ( 0.85 * jQuery( window ).height() ) - $dialogWrap.find( '.ui-dialog-titlebar' ).height(),
						position: { my: 'center', at: 'center', of: window }
					};
				}

				jQuery.each( options, function( option, value ) {
					$dialog.dialog( 'option', option, value );
				} );

				return options;
			},

			/**
			 * Resize the settings popup.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			resizePopupEvent: function( event ) {
				var $resizeIcon = jQuery( event.currentTarget ),
					key = $resizeIcon.data( 'resize-key' );

				// Update body classes.
				this.updatePopupClass( key );

				// Actually resize popup.
				this.resizePopup( key );

				$resizeIcon.siblings( '.resize-icon' ).removeClass( 'active' );
				$resizeIcon.addClass( 'active' );

				$resizeIcon.closest( '.fusion-utility-menu-wrap' ).removeClass( 'active' );
			},

			/**
			 * Close the  more options sub-dialog on help article click.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The JS event.
			 * @return {void}
			 */
			helpArticle: function( event ) {
				jQuery( event.currentTarget ).closest( '.fusion-utility-menu-wrap' ).removeClass( 'active' );
			},

			/**
			 * Push the settings popup to the right side.
			 *
			 * @since 2.0.0
			 * @param {string} className Class name to be used.
			 * @return {void}
			 */
			updatePopupClass: function( className ) {

				// Remove the existing class names.
				jQuery( 'body' ).removeClass( 'fusion-settings-dialog-default fusion-settings-dialog-large' );

				// Use the one for current size.
				jQuery( 'body' ).addClass( className );

				// Store the className for other sessions.
				localStorage.setItem( 'resizePopupClass', className );
			},

			/**
			 * Remove the element from page.
			 *
			 * @since 2.0.0
			 * @param {Object} event - a JS event.
			 * @return {void}
			 */
			removeElement: function( event ) {
				var $this       = this,
					elementView = FusionPageBuilderViewManager.getView( $this.elementView.model.get( 'cid' ) );

				FusionApp.confirmationPopup( {
					title: fusionBuilderText.delete_element,
					content: fusionBuilderText.remove_element_options_confirmation,
					actions: [
						{
							label: fusionBuilderText.cancel,
							classes: 'no cancel',
							callback: function() {

								// Close the confirmation dialog and do nothing.
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );
							}
						},
						{
							label: fusionBuilderText[ 'delete' ],
							classes: 'dont-save',
							callback: function() {

								// Close the confirmation dialog and do nothing.
								FusionApp.confirmationPopup( {
									action: 'hide'
								} );

								FusionApp.dialogCloseResets( $this.elementView );

								if ( 'fusion_builder_column' === elementView.model.attributes.type || 'fusion_builder_column_inner' === elementView.model.attributes.type ) {
									elementView.removeColumn( event );
								} else if ( 'fusion_builder_container' === elementView.model.attributes.type ) {
									elementView.removeContainer( event );
								} else {
									elementView.removeElement( event );
								}
							}
						}
					]
				} );
			}
		} );
	} );
}( jQuery ) );
