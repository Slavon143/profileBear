/* global customizer, Fuse, FusionPageBuilderApp, FusionApp, FusionEvents, fusionBuilderText, fusionSanitize, fusionAppConfig, awbPalette */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Sidebar panel model.
	FusionPageBuilder.Panel = Backbone.Model.extend( {
		defaults: {
			type: 'panel'
		}
	} );

	// Sidebar tab model.
	FusionPageBuilder.Tab = Backbone.Model.extend( {
		defaults: {
			type: 'tab'
		}
	} );

	// Builder Container View
	FusionPageBuilder.SidebarView = Backbone.View.extend( {
		template: FusionPageBuilder.template( jQuery( '#fusion-builder-sidebar-template' ).html() ),
		events: {
			'click .fusion-builder-toggles a': 'switchContext',
			'focus .fusion-builder-search': 'initSearch',
			'keypress .fusion-builder-search': 'checkResults',
			'click .fusion-builder-go-back': 'switchInnerContext'
		},
		currentSectionTarget: false,
		fbeOptionsAdded: false,

		/**
		 * Initialize the builder sidebar.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.toSearch     = false;
			this.poSearch     = false;
			this._showResults = _.debounce( _.bind( this.showResults, this ), 500 );
			this.viewManager  = jQuery.extend( true, {}, new FusionPageBuilder.ViewManager() );

			// Default panel data.
			this.panelData = {
				open: false,
				width: 327,
				context: 'to',
				dialog: false
			};

			// Toggle the panel.
			this.setPanelStates();

			this.flatToObject  = false;
			this.flatPoObject  = false;
			this.searchContext = 'to';

			// Elements outside of view.
			this.$body         = jQuery( 'body' );
			this.$previewPanel = this.$body.find( '#customize-preview' );

			// Add listeners.
			this.listenTo( FusionEvents, 'fusion-to-posts_slideshow_number-changed', this.recreatePoTab );
			this.listenTo( FusionEvents, 'fusion-postMessage-custom_fonts', this.updateCustomFonts );
			this.listenTo( FusionEvents, 'fusion-to-portfolio_equal_heights-changed', this.togglePortfolioEqualHeights );
			this.listenTo( FusionEvents, 'fusion-app-setup', this.setup );

			this.listenTo( FusionEvents, 'fusion-preferences-editing_mode-updated', this.setDialogMode );
			this.listenTo( FusionEvents, 'fusion-preferences-sidebar_position-updated', this.setDialogMode );
			this.listenTo( FusionEvents, 'fusion-preferences-sidebar_overlay-updated', this.setDialogMode );
		},

		/**
		 * Render the template.
		 *
		 * @since 2.0.0
		 * @return {Object} this.
		 */
		render: function() {
			this.$el.html( this.template( this.panelData ) );
			this.addToPanels();
			this.resizableDrag();

			this.setPanelStyling();
			this.setActiveTab();

			return this;
		},

		setup: function() {
			this.addPoPanels( FusionApp.data.samePage );
			this.addFBEPanels();
			this.setDialogMode();
			this.setArchiveMode();
			this.setPostTypeLabel();

			// Add shortcuts if isn't editing a fusion library element.
			// PO shortcuts are filtered server side.
			if ( false === FusionApp.data.is_fusion_element && 'undefined' === typeof FusionApp.data.template_category ) {
				this.createEditShortcuts();
			}
		},

		setDialogMode: function() {
			var preferences = 'undefined' !== typeof FusionApp && 'undefined' !== typeof FusionApp.preferencesData ? FusionApp.preferencesData : false;

			if ( preferences && 'dialog' === preferences.editing_mode ) {
				this.$el.find( '#customize-controls' ).attr( 'data-dialog', true );
				this.updatePanelData( 'dialog', true );
			} else {
				this.$el.find( '#customize-controls' ).attr( 'data-dialog', false );
				this.updatePanelData( 'dialog', false );
			}

			if ( preferences ) {
				if ( 'right' === preferences.sidebar_position && ! this.$body.hasClass( 'sidebar-right' ) ) {
					this.$body.addClass( 'sidebar-right' );
					this.destroyResizable();
					this.resizableDrag();
					this.setPanelStyling();
				} else if ( 'left' === preferences.sidebar_position && this.$body.hasClass( 'sidebar-right' ) ) {
					this.$body.removeClass( 'sidebar-right' );
					this.destroyResizable();
					this.resizableDrag();
					this.setPanelStyling();
				}

				if ( 'on' === preferences.sidebar_overlay ) {
					this.$previewPanel.addClass( 'fusion-overlay-mode' );
				} else {
					this.$previewPanel.removeClass( 'fusion-overlay-mode' );
				}
			}
		},

		setPostTypeLabel: function () {
			var $element = this.$el.find( '.label.fusion-po-only' );
			if ( 'fusion_tb_section' === FusionApp.data.postDetails.post_type && 0 < $element.length ) {
				$element.html( $element.data( 'layout' ) );
			} else {
				$element.html( $element.data( 'name' ) );
			}
		},

		setArchiveMode: function() {
			if ( 'undefined' !== typeof FusionApp && FusionApp.data.is_archive ) {
				this.$el.find( '#customize-controls' ).attr( 'data-archive', true );
			} else {
				this.$el.find( '#customize-controls' ).attr( 'data-archive', false );
			}
			if ( 'undefined' !== typeof FusionApp ) {
				this.$el.find( '#customize-controls' ).attr( 'data-editor', FusionApp.builderActive );
			}
		},

		/**
		 * Add edit icons to elements in the preview.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		createEditShortcuts: function() {
			var self      = this,
				toOptions = self.getFlatToObject(),
				poOptions = self.getFlatPoObject(),
				$preview  = jQuery( '#fb-preview' ).contents().find( 'html' ),
				$element,
				shortcuts = jQuery.extend( true, self.createShortcutsObject( toOptions ), self.createShortcutsObject( poOptions, 'po' ) );

			// Add shortcuts to DOM.
			_.each( shortcuts, function( html, selector ) {
				$element = $preview.find( selector );

				if ( $element.length && ! $element.hasClass( 'fusion-panel-customizable' ) ) {

					$element.append( '<span class="fusion-panel-shortcuts-wrapper"><span class="fusion-panel-shortcuts">' + html.join( '' ) + '</span></span>' );
					$element.addClass( 'fusion-panel-customizable' );

					if ( 'static' === $element.css( 'position' ) ) {
						$element.addClass( 'fusion-panel-customizable-needs-positioned' );
					}
				}
			} );

			$preview.on( 'click', '.fusion-panel-shortcut', function( event ) {
				var $trigger = jQuery( this );

				if ( 'undefined' === typeof $trigger.attr( 'href' ) ) {
					event.preventDefault();

					self.shortcutClick( $trigger );
				}
			} );
		},

		/**
		 * Creates shortcuts object.
		 *
		 * @param {object} options TO or PO flat object.
		 * @param {string} context Can be 'to' or 'po'.
		 * @return {object} Shortcuts object.
		 */
		createShortcutsObject: function( options, context ) {
			var shortcuts = {},
				selectors,
				shortcutContext;

			context = 'undefined' !== typeof context ? context : '';

			// Iterate options and build selector => shortcuts object.
			_.each( options, function( option ) {
				if ( 'object' === typeof option.edit_shortcut ) {

					selectors = 'string' === typeof option.edit_shortcut.selector ? option.edit_shortcut.selector.split( ',' ) : option.edit_shortcut.selector;

					// Loop through all selectors.
					_.each( selectors, function( selector ) {
						selector = selector.trim();

						// Loop through all shortcuts for those selectors.
						_.each( option.edit_shortcut.shortcuts, function( shortCut ) {
							var hasAriaLabel = shortCut.aria_label || false,
								target     = '',
								icon       = '',
								openParent = '',
								html       = '',
								callback   = '',
								cssClass,
								shortCutCopy = jQuery.extend( true, {}, shortCut );

							if ( hasAriaLabel ) {

								// Check if shortcut should be added or not.
								if ( 'undefined' !== typeof shortCutCopy && '' !== shortCutCopy.disable_on_template_override &&
									'undefined' !== typeof FusionApp.data.template_override && 'undefined' !== typeof FusionApp.data.template_override[ shortCutCopy.disable_on_template_override ] && false !== FusionApp.data.template_override[ shortCutCopy.disable_on_template_override ] ) {

										// Continue.
										return;
								}

								// Check if template should be edited or not.
								if ( 'undefined' !== typeof shortCutCopy && '' !== shortCutCopy.link_to_template_if_override_active &&
									'undefined' !== typeof FusionApp.data.template_override && 'undefined' !== typeof FusionApp.data.template_override[ shortCutCopy.link_to_template_if_override_active ] && false !== FusionApp.data.template_override[ shortCutCopy.link_to_template_if_override_active ] ) {

									// Construct link.
									if ( FusionApp.data.template_override[ shortCutCopy.link_to_template_if_override_active ].permalink && -1 !== FusionApp.data.template_override[ shortCutCopy.link_to_template_if_override_active ].permalink.indexOf( '?' ) ) {
										shortCutCopy.link = FusionApp.data.template_override[ shortCutCopy.link_to_template_if_override_active ].permalink + '&fb-edit=1&target_post_id=' + FusionApp.data.postDetails.post_id;
									} else {
										shortCutCopy.link = FusionApp.data.template_override[ shortCutCopy.link_to_template_if_override_active ].permalink + '?fb-edit=1&target_post_id=' + FusionApp.data.postDetails.post_id;
									}

									// Parent window should be redirected.
									shortCutCopy.target = '_top';

									// Change Aria Label to "Edit Template";
									shortCutCopy.aria_label = 'undefined' !== typeof fusionBuilderText[ 'edit_' + shortCutCopy.link_to_template_if_override_active + '_layout_section' ] ? fusionBuilderText[ 'edit_' + shortCutCopy.link_to_template_if_override_active + '_layout_section' ] : fusionBuilderText.edit_layout_section;

									if ( 'undefined' !== typeof shortCutCopy.override_icon ) {
										icon = shortCut.override_icon;
									}
								}

								// Shortcuts with custom link set.
								if ( 'undefined' !== typeof shortCutCopy.link ) {
									target   = 'undefined' !== typeof shortCutCopy.target ? shortCutCopy.target : '_blank';
									cssClass = 'undefined' !== typeof shortCutCopy.css_class ? shortCutCopy.css_class : '';

									// If there wasn't override.
									if ( '' === icon ) {
										icon = 'undefined' !== typeof shortCutCopy.icon ? shortCutCopy.icon : 'fusiona-pen';
									}

									html = '<a class="fusion-panel-shortcut ' + cssClass + '" href="' + shortCutCopy.link + '" target="' + target + '" aria-label="' + shortCutCopy.aria_label + '"><span class="' + icon + '"></span></a>';
								} else {

									// If there wasn't override.
									if ( '' === icon ) {
										icon = 'undefined' !== typeof shortCutCopy.icon ? shortCutCopy.icon : 'fusiona-cog';
									}
									if ( 'undefined' !== typeof shortCutCopy.open_parent ) {
										openParent = ' data-fusion-option-open-parent="true"';
									}
									if ( 'undefined' !== typeof shortCutCopy.callback ) {
										callback = ' data-callback="' + shortCutCopy.callback + '"';
									}

									shortcutContext = 'undefined' !== typeof shortCutCopy.context ? shortCutCopy.context : context;

									html = '<span class="fusion-panel-shortcut" data-fusion-option="' + option.id + '" aria-label="' + shortCutCopy.aria_label + '" data-context="' + shortcutContext + '"' + openParent + callback + '><span class="' + icon + '"></span></span>';
								}

								if ( 'undefined' === typeof shortcuts[ selector ] ) {
									shortcuts[ selector ] = [];
								}

								// Add shortcuts in correct order if it is set.
								if ( 'undefined' !== typeof shortCutCopy.order ) {
									shortcuts[ selector ][ shortCutCopy.order ] = html;
								} else {
									shortcuts[ selector ].push( html );
								}
							}
						} );
					} );
				}
			} );

			return shortcuts;
		},

		/**
		 * Fires on a shortcut trigger click.
		 *
		 * @since 2.0.0
		 * @param {Object} $trigger - jQuery trigger element object.
		 * @return {void}
		 */
		shortcutClick: function( $trigger ) {
			if ( $trigger.attr( 'data-callback' ) && 'function' === typeof FusionApp.callback[ $trigger.attr( 'data-callback' ) ] ) {
				FusionApp.callback[ $trigger.attr( 'data-callback' ) ]( $trigger );
			} else {
				this.openOption( $trigger.data( 'fusion-option' ), $trigger.data( 'context' ), $trigger.data( 'fusion-option-open-parent' ) );
			}
		},

		/**
		 * Opens relevant option.
		 *
		 * @since 2.0.0
		 * @param {string} option - The option we want to focus on or parent tab ID.
		 * @param {string} context - TO/PO.
		 * @param {boolean} openParent - Whether we should open the parent or not.
		 * @return {void}
		 */
		openOption: function( option, context, openParent ) {
			var self = this,
				flatOptions,
				tab,
				poObject,
				tabId;

			// Unset context check if we have a PO first, otherwise will fallback to TO.
			if ( 'undefined' === typeof context ) {
				poObject = this.getFlatPoObject();
				if ( 'undefined' !== typeof poObject[ option ] && 'object' === typeof FusionApp.data.postMeta._fusion && 'undefined' !== typeof FusionApp.data.postMeta._fusion[ option ] && '' !== FusionApp.data.postMeta._fusion[ option ] ) {
					context = 'po';
				}
			}
			context     = 'undefined' === typeof context || ! context ? 'to' : context;
			flatOptions = 'po' !== context ? this.getFlatToObject() : this.getFlatPoObject();
			tab         = flatOptions[ option ];
			tabId       = 'object' === typeof tab ? tab.tab_id : false;

			openParent =  'undefined' !== typeof openParent ? openParent : false;

			// Open parent section.
			if ( true === openParent ) {
				tabId = 'undefined' !== typeof tab ? tab.parent_id : option;
			}

			context = context.toLowerCase();

			this.togglePanelState( context, true );

			this.setActiveTab( context );

			if ( tab && 'FBE' === tab.location ) {
				this.switchActiveContext( '#fusion-builder-sections-to', 'FBE' );
			}

			// Open/create tab.
			if ( ! this.$el.find( '#tab-' + tabId ).length || ! this.$el.find( '#tab-' + tabId ).is( ':visible' ) ) {
				if ( this.$el.find( '.fusion-sidebar-section:visible #' + tabId ).length ) {
					this.$el.find( '.fusion-sidebar-section:visible #' + tabId ).trigger( 'click' );
				} else if ( this.$el.find( '.fusion-sidebar-section:visible #heading_' + tabId ).length ) {

					// If parent panel isn't visible.
					if ( true !== self.$el.find( '.fusion-panels' ).is( ':visible' ) ) {
						self.$el.find( '.fusion-builder-custom-tab:visible .fusion-builder-go-back' ).trigger( 'click' );
						self.$el.find( '.fusion-panels' ).show();
					}

					// If parent panel section is already expanded no need to trigger click.
					if ( 'true' !== this.$el.find( '.fusion-sidebar-section:visible #heading_' + tabId ).attr( 'aria-expanded' ) ) {
						this.$el.find( '.fusion-sidebar-section:visible #heading_' + tabId ).trigger( 'click' );
					}

				}
			}

			setTimeout( function() {
				if ( ! openParent ) {
					self.scrollToElement( self.$el.find( '[data-option-id="' + tab.id + '"]:visible' ) );
				} else {
					self.scrollToElement( self.$el.find( '.fusion-sidebar-section:visible #heading_' + tabId ) );
				}

			}, 50 );
		},

		/**
		 * Scroll to an element in sidebar.
		 *
		 * @since 2.0.0
		 * @param {Objct} $element - The jQuery element to target.
		 * @param {boolean} smooth - Do we want smooth scroll or not?
		 * @return {void}
		 */
		scrollToElement: function( $element, smooth ) {
			var $section       = this.$el.find( '.fusion-sidebar-section:visible .fusion-tabs' ),
				stickyScroll   = this.$el.find( '.fusion-builder-toggles' ).outerHeight() + this.$el.find( '.fusion-panel-section-header-wrapper' ).outerHeight(),
				optionPosition = 0;

			if ( ! $section.is( ':visible' ) ) {
				$section = this.$el.find( '.fusion-sidebar-section:visible .fusion-panels' );
			}

			smooth = 'undefined' === typeof smooth ? true : smooth;
			if ( $element.length ) {
				if ( smooth ) {
					optionPosition = $element.position().top + $section.scrollTop() - stickyScroll;
					$section.animate( {
						scrollTop: optionPosition
					}, 450 );
				} else {
					optionPosition = $element.position().top + $section.scrollTop() - stickyScroll;
					$section.scrollTop( optionPosition );
				}
			}
		},

		/**
		 * Create flat object for easier finding a specific option.
		 *
		 * @since 2.0.0
		 * @return {Object} this.
		 */
		getFlatToObject: function() {
			var flatFields = {};

			if ( false !== this.flatToObject ) {
				return this.flatToObject;
			}

			_.each( customizer, function( panel, panelKey ) {
				_.each( panel.fields, function( tab, tabKey ) {
					if ( 'sub-section' === tab.type || 'accordion' === tab.type ) {
						_.each( tab.fields, function( field, fieldKey ) {
							field.tab_id           = tabKey;
							field.location         = 'TO';
							flatFields[ fieldKey ] = field;
							field.parent_id        = panelKey;
						} );
					} else {
						tab.tab_id           = panelKey;
						tab.location         = 'TO';
						flatFields[ tabKey ] = tab;
					}
				} );
			} );

			if ( FusionApp.data && FusionApp.data.fusionElementsOptions ) {
				_.each( FusionApp.data.fusionElementsOptions, function( panel, panelKey ) {
					_.each( panel.fields, function( tab, tabKey ) {
						if ( 'sub-section' === tab.type || 'accordion' === tab.type ) {
							_.each( tab.fields, function( field, fieldKey ) {
								field.tab_id           = tabKey;
								field.location         = 'FBE';
								flatFields[ fieldKey ] = field;
							} );
						} else {
							tab.tab_id           = panelKey;
							tab.location         = 'FBE';
							flatFields[ tabKey ] = tab;
						}
					} );
				} );
			}

			this.flatToObject = flatFields;
			return this.flatToObject;
		},

		/**
		 * Create flat object for easier finding a specific option.
		 *
		 * @since 2.0.0
		 * @return {Object} this.
		 */
		getFlatPoObject: function() {
			var flatFields  = {},
				pageOptions = jQuery.extend( true, {}, FusionApp.data.fusionPageOptions );

			if ( false !== this.flatPoObject ) {
				return this.flatPoObject;
			}

			_.each( pageOptions, function( panel, panelKey ) {
				_.each( panel.fields, function( tab, tabKey ) {
					if ( 'sub-section' === tab.type || 'accordion' === tab.type ) {
						_.each( tab.fields, function( field, fieldKey ) {
							field.tab_id           = tabKey;
							field.location         = 'po';
							flatFields[ fieldKey ] = field;
						} );
					} else {
						tab.tab_id           = panelKey;
						tab.location         = 'po';
						flatFields[ tabKey ] = tab;
					}
				} );
			} );

			this.flatPoObject = flatFields;
			return this.flatPoObject;
		},

		/**
		 * Initialization of search.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initSearch: function() {
			var context = 'TO',
				options = {
					threshold: 0.3,
					minMatchCharLength: 3,
					keys: [ 'label' ]
				};

			// Get the context from the active tab.
			if ( 'po' === this.panelData.context ) {
				context = 'PO';
			}

			this.searchContext = context;

			if ( 'TO' === context ) {
				this.toSearch = new Fuse( _.values( this.getFlatToObject() ), options );
			} else {
				this.poSearch = new Fuse( _.values( this.getFlatPoObject() ), options );
			}
		},

		/**
		 * Clear sidebar search.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		clearSearch: function() {
			this.$el.find( '.fusion-builder-search' ).val( '' );
		},

		/**
		 * Show toggles.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showToggles: function() {
			this.$el.find( '.fusion-builder-toggles' ).show();
		},

		/**
		 * Hide toggles.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		hideToggles: function() {
			this.$el.find( '.fusion-builder-toggles' ).hide();
		},

		destroyResizable: function() {
			this.$el.find( '#customize-controls' ).resizable( 'destroy' );
		},

		/**
		 * Make sidebar resizable.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		resizableDrag: function() {
			var self       = this,
				$sidebar   = this.$el.find( '#customize-controls' ),
				handle     = this.$body.hasClass( 'sidebar-right' ) ? 'w' : 'e',
				urlParams  = new URLSearchParams( window.location.search ),
				maxSBWidth = null !== urlParams.get( 'sb-max' ) ? parseInt( urlParams.get( 'sb-max' ) ) : 640;

			// On start can sometimes be laggy/late.
			$sidebar.hover(
				function() {
					self.$body.addClass( 'fusion-preview-block' );
				}, function() {
					if ( ! self.$body.hasClass( 'fusion-sidebar-resizing' ) ) {
						self.$body.removeClass( 'fusion-preview-block' );
					}
				}
			);

			$sidebar.resizable( {
				handles: handle,
				minWidth: 327,
				maxWidth: maxSBWidth,
				start: function() {
					self.$body.addClass( 'fusion-preview-block' ).addClass( 'fusion-sidebar-resizing' );
				},
				resize: function( event, ui ) {
					var width = ( 327 >= ui.size.width ) ? 327 : ui.size.width;

					width = ( maxSBWidth < width ) ? maxSBWidth : width;

					if ( self.$body.hasClass( 'sidebar-right' ) ) {
						self.$previewPanel.css( 'padding-right', width ).css( 'padding-left', 0 );
					} else {
						self.$previewPanel.css( 'padding-left', width ).css( 'padding-right', 0 );
					}
				},
				stop: function( event, ui ) {
					var width = ( 327 >= ui.size.width ) ? 327 : ui.size.width;

					width = ( maxSBWidth < width ) ? maxSBWidth : width;

					if ( self.$body.hasClass( 'sidebar-right' ) ) {
						$sidebar.css( { left: 'auto', right: 0 } );
					}

					// Store the size for later use on reload.
					self.updatePanelData( 'width', width );

					self.$body.removeClass( 'fusion-preview-block' ).removeClass( 'fusion-sidebar-resizing' );
				}
			} );
		},

		/**
		 * Check search results.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {void}
		 */
		checkResults: function( event ) {
			this._showResults( event );
		},

		/**
		 * Show search results.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {void}
		 */
		showResults: function( event ) {
			var queryTerm = jQuery( event.currentTarget ).val(),
				context   = this.panelData.context,
				results   = 'po' !== context ? this.toSearch.search( queryTerm ) : this.poSearch.search( queryTerm ),
				$section  = this.$el.find( '#fusion-builder-sections-' + context ),
				fields    = {},
				tabSettings,
				view;

			_.each( results, function( field ) {
				fields[ field.id ] = field;
			} );

			tabSettings = {
				model: new FusionPageBuilder.Tab( {
					fields: fields,
					id: 'fusion-builder-results',
					type: 'search',
					label: fusionBuilderText.search_results,
					context: this.searchContext
				} )
			};
			view = new FusionPageBuilder.TabView( tabSettings );

			// Remove other existing tabs and views. (for preview purposes).
			this.clearTabs( context );

			// Delete existing results.
			$section.find( '#tab-fusion-builder-results' ).remove();

			// Add new results.
			$section.find( '.fusion-tabs' ).append( view.render().el ).show();

			// Show correct tab only.
			$section.find( '.fusion-tabs' ).show();
			$section.find( '.fusion-panels, .fusion-tabs .fusion-builder-custom-tab' ).hide();
			$section.find( '#tab-fusion-builder-results' ).show();

		},

		/**
		 * Add theme-options panels.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addToPanels: function() {
			var self = this,

				// TO Panels
				panelContainer = this.$el.find( '#fusion-builder-sections-to .fusion-panels' );

			_.each( customizer, function( panel ) {
				var panelSettings,
					panelCid = self.viewManager.generateCid(),
					view;

				if ( panel.label ) {

					panel.cid          = panelCid;
					panel.context      = 'TO';
					panel.innerContext = 'TO';
					panelSettings      = new FusionPageBuilder.Panel( panel );
					view               = new FusionPageBuilder.PanelView( { model: panelSettings } );
					self.viewManager.addView( panelCid, view );
					panelContainer.append( view.render().el );
				}
			} );
		},

		/**
		 * Add Avada-Builder-Elements panels.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		addFBEPanels: function() {
			var self           = this,
				panelContainer = this.$el.find( '#fusion-builder-sections-to .fusion-panels' );

			if ( self.fbeOptionsAdded ) {
				return;
			}
			_.each( FusionApp.data.fusionElementsOptions, function( panel ) {
				var panelSettings,
					panelCid = self.viewManager.generateCid(),
					view;

				if ( panel.label ) {

					panel.cid          = panelCid;
					panel.context      = 'TO';
					panel.innerContext = 'undefined' === typeof panel.addon || ! panel.addon ? 'FBE' : 'FBAO';
					panelSettings      = new FusionPageBuilder.Panel( panel );
					view               = new FusionPageBuilder.PanelView( { model: panelSettings } );
					self.viewManager.addView( panelCid, view );
					panelContainer.append( view.render().el );
				}
			} );
			self.fbeOptionsAdded = true;
		},

		/**
		 * Updates labels.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		updateLabels: function() {
			this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-po"] .fusion-po-label' ).addClass( 'hidden' );
			this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-ps"] .fusion-ps-label' ).addClass( 'hidden' );

			if ( 'archive' !== FusionApp.data.currentPage ) {
				this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-po"] .fusion-page-options' ).removeClass( 'hidden' );
				this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-ps"] .fusion-page-settings' ).removeClass( 'hidden' );
			} else {
				this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-po"] .fusion-taxonomy-options' ).removeClass( 'hidden' );
				this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-ps"] .fusion-category-settings' ).removeClass( 'hidden' );
			}
		},

		/**
		 * Adds page-options panels.
		 *
		 * @since 2.0.0
		 * @param {boolean} samePage - Is this the same page?
		 * @return {void}
		 */
		addPoPanels: function( samePage ) {

			var panelContainer = this.$el.find( '#fusion-builder-sections-po .fusion-panels' ),
				self = this;

			// Same page, no need to re-render page options.
			if ( samePage && panelContainer.find( '.fusion-builder-custom-panel' ).length ) {
				return;
			}

			// Not same page, make sure to destroy existing tabs and panels.
			if ( ! samePage && panelContainer.find( '.fusion-builder-custom-panel' ).length ) {

				// Switch to TO view since it remains.
				if ( this.$el.find( '#tab-fusion-builder-results' ).is( ':visible' ) ) {
					this.$el.find( '#tab-fusion-builder-results .fusion-builder-go-back' ).trigger( 'click' );
				} else if ( this.$el.find( '#fusion-builder-tab .fusion-builder-custom-tab[data-type="PO"]' ).is( ':visible' ) || this.$el.find( 'a[href="#fusion-builder-sections-po"].fusion-active' ).length ) {
					this.$el.find( 'a[href="#fusion-builder-sections-po"]' ).trigger( 'click' );
				}

				this.clearPanels( 'po' );
				this.clearTabs( 'po' );
			}

			// Add PO panels.
			_.each( FusionApp.data.fusionPageOptions, function( panel ) {
				var panelSettings,
					panelCid = self.viewManager.generateCid(),
					view;

				if ( panel.label ) {

					panel.cid     = panelCid;
					panel.context = 'PO';
					panelSettings = new FusionPageBuilder.Panel( panel );
					view          = new FusionPageBuilder.PanelView( { model: panelSettings } );
					panelContainer.append( view.render().el );
					self.viewManager.addView( panelCid, view );
				}
			} );

			if ( jQuery( '.fusion-builder-toggles [href="#fusion-builder-sections-po"]' ).hasClass( 'fusion-active' ) ) {
				jQuery( '.fusion-builder-toggles [href="#fusion-builder-sections-po"]' ).click();
			}
		},

		/**
		 * Clear panels.
		 *
		 * @since 2.0.0
		 * @param {string} context TO/PO etc.
		 * @return {void}
		 */
		clearPanels: function( context ) {
			var self    = this,
				$panels,
				panelView;

			context = 'undefined' === typeof context ? 'to' : context.toLowerCase();
			$panels = this.$el.find( '#fusion-builder-sections-' + context + ' .fusion-builder-custom-panel' );

			if ( $panels.length ) {
				$panels.each( function() {
					panelView = self.viewManager.getView( jQuery( this ).data( 'cid' ) );
					if ( panelView ) {
						panelView.removePanel();
					}
				} );
			}
		},

		/**
		 * Clear tabs.
		 *
		 * @since 2.0.0
		 * @param {string} context - TO/PO etc.
		 * @param {string} tabId - The tab ID.
		 * @param {string} optionId - The option ID.
		 * @return {void}
		 */
		clearTabs: function( context, tabId, optionId ) {
			var self = this,
				$section,
				$tabs,
				tabView;

			context  = 'undefined' === typeof context ? 'to' : context.toLowerCase();
			$section = this.$el.find( '#fusion-builder-sections-' + context );
			$tabs    = 'undefined' === typeof tabId || ! tabId ? $section.find( '.fusion-builder-custom-tab' ) : $section.find( '.fusion-builder-custom-tab#' + tabId );

			if ( 'undefined' !== typeof optionId && optionId ) {
				$tabs = $tabs.find( '.fusion-builder-option[data-option-id="' + optionId + '"]' ).closest( '.fusion-builder-custom-tab' );
			}

			if ( $tabs.length ) {
				$tabs.each( function() {
					tabView = self.viewManager.getView( jQuery( this ).data( 'cid' ) );
					if ( tabView ) {
						tabView.removeTab();
					}
				} );
			}
		},

		/**
		 * Clear inactive tabs.
		 *
		 * @since 2.0.0
		 * @param {string} context - TO/PO etc.
		 * @return {void}
		 */
		clearInactiveTabs: function( context ) {
			var self = this,
				$section,
				$tabs,
				tabView;

			context  = 'undefined' === typeof context ? 'to' : context.toLowerCase();
			$section = this.$el.find( '#fusion-builder-sections-' + context );
			$tabs    = $section.find( '.fusion-builder-custom-tab:not( :visible )' );

			if ( $tabs.length ) {
				$tabs.each( function() {
					tabView = self.viewManager.getView( jQuery( this ).data( 'cid' ) );
					if ( tabView ) {
						tabView.removeTab();
					}
				} );
			}
		},

		/**
		 * Recreate panels.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		recreatePanels: function() {
			this.clearPanels( 'po' );
			this.addPoPanels( true );
		},

		/**
		 * Clear theme-options tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		refreshTo: function() {
			var self = this;

			this.$el.find( '#fusion-builder-sections-to' ).show();

			// Remove existing tabs.
			this.$el.find( '#fusion-builder-tab .fusion-builder-custom-tab[data-type="TO"]' ).each( function() {
				self.viewManager.removeView( jQuery( this ).data( 'cid' ) );
				jQuery( this ).remove();
			} );
		},

		/**
		 * Switch context.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {void}
		 */
		switchContext: function( event ) {
			var $anchor    = jQuery( event.currentTarget ),
				targetHref = $anchor.attr( 'href' ),
				$targetEl  = this.$el.find( targetHref ),
				context    = targetHref.replace( '#fusion-builder-sections-', '' );

			event.preventDefault();

			this.$el.find( '.fusion-active' ).removeClass( 'fusion-active' );
			this.$el.find( '.fusion-sidebar-section' ).hide();

			$targetEl.show();

			// If switching to section with no visible tabs or panels.
			if ( ! $targetEl.is( '#fusion-builder-sections-eo' ) && $targetEl.find( '.fusion-tabs' ).is( ':visible' ) && ! $targetEl.find( '.fusion-tabs' ).find( '.fusion-builder-custom-tab:visible' ).length && ! $targetEl.find( '.fusion-panels' ).is( ':visible' ) ) {
				$targetEl.find( '.fusion-tabs' ).hide();
				$targetEl.find( '.fusion-panels' ).show();
			}
			$anchor.addClass( 'fusion-active' );

			this.setPanelContext( context );
		},

		/**
		 * Close the sidebar.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		closeSidebar: function() {
			this.updatePanelData( 'open', false );
			this.setPanelStyling();
		},

		/**
		 * Close the sidebar.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		openSidebar: function() {
			this.updatePanelData( 'open', true );
			this.setPanelStyling();
		},

		getPanelWidth: function() {
			return this.panelData.width;
		},

		panelIsOpen: function() {
			return this.panelData.open;
		},

		togglePanel: function() {
			if ( this.panelIsOpen() ) {
				this.closeSidebar();
			} else {
				this.openSidebar();
			}
		},

		setPanelContext: function( context ) {
			this.$el.find( '#customize-controls' ).attr( 'data-context', context );
			this.updatePanelData( 'context', context );
		},

		/**
		 * Toggles sidebar open or closed.
		 *
		 * @since 2.0.0
		 * @param {string} context
		 * @param {boolean} noclose
		 * @return {void}
		 */
		togglePanelState: function( context, noclose ) {
			var eventContext  = 'undefined' === typeof context ? 'to' : context,
				panelContext  = this.panelData.context,
				switchContext = ( this.panelIsOpen() && 'undefined' !== typeof panelContext && panelContext && eventContext !== panelContext );

			noclose = 'undefined' === typeof noclose ? false : true;

			// If the panel is already open and we need to change its context
			// Then we don't need to expand/collapse it, just change the data-context attribute
			// ( already done above) and then early exit to prevent running the rest of this method.
			if ( switchContext ) {

				// Toggle active states on the toolbar buttons.
				this.setActiveTab( eventContext );
				return;
			}

			// If we don't want to toggle close, just return early.
			if ( noclose && this.panelIsOpen() ) {
				return;
			}

			this.togglePanel();

			this.setPanelStyling();

			// Only set context if we are opening.
			if ( this.panelIsOpen() ) {
				this.setActiveTab( eventContext );
			}
		},

		setPanelStyling: function() {
			var width     = this.getPanelWidth(),
				direction = this.$body.hasClass( 'sidebar-right' ) ? 'right' : 'left',
				opposite  = this.$body.hasClass( 'sidebar-right' ) ? 'left' : 'right',
				cssUpdate = { width: width };

			cssUpdate[ direction ] = 0;
			cssUpdate[ opposite ]  = 'auto';

			if ( ! this.panelIsOpen() ) {
				this.$body.removeClass( 'expanded' );

				this.$previewPanel.css( 'padding-' + direction, 0 ).css( 'padding-' + opposite, 0 );
				this.$el.find( '#customize-controls' ).css( direction, -width ).css( opposite, 'auto' ).css( 'width', width );
			} else {
				this.$body.addClass( 'expanded' );
				this.$previewPanel.css( 'padding-' + direction, width ).css( 'padding-' + opposite, 0 );
				this.$el.find( '#customize-controls' ).css( cssUpdate );
			}

			FusionEvents.trigger( 'fusion-sidebar-toggled', this.panelIsOpen() );
		},

		setActiveTab: function( context ) {
			var panel         = this.$el,
				passedContext = 'undefined' !== typeof context && context;

			context = 'undefined' === typeof context ? this.panelData.context : context.toLowerCase();

			// Set if this is global or element.
			if ( passedContext ) {
				this.setPanelContext( context );
			}
			if ( ! panel.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-' + context + '"]' ).hasClass( 'fusion-active' ) ) {
				panel.find( '.fusion-builder-toggles > .fusion-active' ).removeClass( 'fusion-active' );
				panel.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-' + context + '"]' ).trigger( 'click' );
			}
		},

		updatePanelData: function( key, value ) {
			this.panelData[ key ] = value;
			this.storePanelStates();

			if ( 'open' === key || 'width' === key ) {
				setTimeout( function() {
					FusionEvents.trigger( 'fusion-frame-size-changed' );
				}, 500 );
			}
		},

		/**
		 * Get stored panel state if it exists and closes panel if set.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		setPanelStates: function() {
			var data;

			if ( 'undefined' !== typeof Storage ) {
				if ( localStorage.getItem( 'fusionPanel' ) ) {
					try {
						data = JSON.parse( localStorage.getItem( 'fusionPanel' ) );
						if ( 'object' === typeof data ) {
							this.panelData = {
								open: 'undefined' !== typeof data.open ? data.open : false,
								width: 'undefined' !== typeof data.width ? data.width : 327,
								context: 'undefined' !== typeof data.context ? data.context : 'to',
								dialog: 'undefined' !== typeof data.dialog ? data.dialog : false
							};
						}
					} catch ( error ) {
						console.log( error );
					}
				}
			}
		},

		/**
		 * Stored side panel open/close state.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		storePanelStates: function() {
			if ( 'undefined' !== typeof Storage ) {
				localStorage.setItem( 'fusionPanel', JSON.stringify( this.panelData ) );
			}
		},

		/**
		 * Convert displayed default values.
		 *
		 * @since 2.0.0
		 * @param {string} to - The theme-option name.
		 * @param {mixed} value - The value.
		 * @param {string} type - The option-type (yesno/showhide/reverseyesno etc).
		 * @param {string} subset - The option subset.
		 * @return {string} - Returns the value as a string.
		 */
		fixToValueName: function( to, value, type, subset ) {
			var flatTo  = this.getFlatToObject(),
				colorObject;

			if ( 'undefined' !== typeof flatTo[ to ] && 'undefined' !== typeof flatTo[ to ].choices && 'undefined' !== typeof flatTo[ to ].choices[ value ] && 'yesno' !== type ) {
				return flatTo[ to ].choices[ value ];
			}
			if ( 'object' === typeof value ) {
				if ( !_.isEmpty( subset ) && 'undefined' !== typeof value[ subset ] ) {
					value = value[ subset ];
				} else {
					value = _.values( value ).join( ', ' );
				}
			}

			if ( _.isString( value ) && awbPalette.getColorSlugFromCssVar( value ) ) {
				colorObject = awbPalette.getColorObject( awbPalette.getColorSlugFromCssVar( value ) );
				if ( ! colorObject ) {
					colorObject = awbPalette.getDefaultColorObject();
				}

				value = colorObject.label;
			}

			switch ( type ) {
			case 'yesno':
				if ( 1 == value ) {
					value = 'Yes';
				} else if ( 0 == value ) {
					value = 'No';
				}
				break;

			case 'showhide':
				if ( 1 == value ) {
					value = 'Show';
				} else if ( 0 == value ) {
					value = 'Hide';
				}
				break;

			case 'reverseyesno':
				value = ( 1 == value || true == value ) ? 'No' : 'Yes';
				break;
			}

			return value;
		},

		/**
		 * Recreates PO tab after posts_slideshow_number is changed.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		recreatePoTab: function() {
			var n    = fusionSanitize.getOption( 'posts_slideshow_number' ),
				self = this,
				fieldId,
				fieldContent = {},
				postType,
				i;

			if ( false === FusionApp.data.is_singular || ( 'post' !== FusionApp.data.postDetails.post_type && 'page' !== FusionApp.data.postDetails.post_type && 'avada_portfolio' !== FusionApp.data.postDetails.post_type ) ) {
				return;
			}

			postType = FusionApp.data.postDetails.post_type;

			// Remove featured image fields.
			jQuery.each( FusionApp.data.fusionPageOptions.fusion_page_settings_section.fields, function( key ) {
				if ( -1 !== key.indexOf( 'kd_featured-image-' ) ) {
					delete ( FusionApp.data.fusionPageOptions.fusion_page_settings_section.fields[ key ] );
				}
			} );

			// Add new fields.
			for ( i = 2; i <= n; i++ ) {
				fieldId                      = 'kd_featured-image-' + i + '_' + postType + '_id';
				fieldContent                 = jQuery.extend( true, {}, FusionApp.data.featured_image_default );
				fieldContent.id              = fieldId;
				fieldContent.label           = fieldContent.label.replace( '$', i );

				// Instead of key renaming.
				fieldContent.partial_refresh[ fieldId ] = jQuery.extend( true, {}, fieldContent.partial_refresh[ 'kd_featured-image-$_#_id' ] );
				delete ( fieldContent.partial_refresh[ 'kd_featured-image-$_#_id' ] );

				FusionApp.data.fusionPageOptions.fusion_page_settings_section.fields[ fieldId ] = jQuery.extend( {}, fieldContent );
			}

			console.log( FusionApp.data.fusionPageOptions.fusion_page_settings_section.fields );

			// Remove existing tab panel.
			self.viewManager.removeView( this.$el.find( '#tab-fusion_page_settings_section' ).data( 'cid' ) );
			this.$el.find( '#tab-fusion_page_settings_section' ).remove();
		},

		/**
		 * Handles changes to custom fonts.
		 *
		 * @since 2.0
		 * @return {Object} this
		 */
		updateCustomFonts: function() {
			var self     = this,
				ajaxData = {
					action: 'avada_custom_fonts_font_faces',
					fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
					custom_fonts: FusionApp.settings.custom_fonts
				};

			// If webfonts are not defined, init them and re-run this method.
			if ( ! FusionApp.assets.webfonts ) {
				jQuery.when( FusionApp.assets.getWebFonts() ).done( function() {
					self.updateCustomFonts();
				} );
				return this;
			}

			FusionApp.assets.webfonts.custom = [];
			_.each( FusionApp.settings.custom_fonts.name, function( name ) {
				FusionApp.assets.webfonts.custom.push( {
					family: name,
					label: name,
					variants: []

				} );
			} );

			// Inject @font-face styles into frame.

			jQuery.post( fusionAppConfig.ajaxurl, ajaxData, function( response ) {
				if ( jQuery( '#fb-preview' ).contents().find( 'head' ).find( '#css-custom_fonts' ).length ) {
					jQuery( '#fb-preview' ).contents().find( 'head' ).find( '#css-custom_fonts' ).remove();
				}
				jQuery( '#fb-preview' ).contents().find( 'head' ).append( '<style type="text/css" id="css-custom_fonts">' + response + '</style>' );
			} );

			// Destroy views so that fonts get updated in controls.
			this.clearPanels( 'po' );

			return this;
		},

		/**
		 * Toggles equal heights on portfolio archive pages.
		 *
		 * @since 2.0
		 */
		togglePortfolioEqualHeights: function() {

			if ( '1' === fusionSanitize.getOption( 'portfolio_equal_heights' ) ) {
				jQuery( '#fb-preview' ).contents().find( '.fusion-portfolio-archive' ).addClass( 'fusion-portfolio-equal-heights' );
			} else {
				jQuery( '#fb-preview' ).contents().find( '.fusion-portfolio-archive' ).removeClass( 'fusion-portfolio-equal-heights' );
			}

			window.frames[ 0 ].dispatchEvent( new Event( 'fusion-element-render-fusion_portfolio' ) );
		},

		switchInnerContext: function( event ) {
			var $target   = jQuery( event.currentTarget ),
				context   = $target.data( 'context' ),
				triggerId = $target.data( 'trigger' ),
				$trigger  = this.$el.find( 'a#' + triggerId ).closest( '.fusion-builder-custom-panel' );

			$target.closest( '.fusion-sidebar-section' ).attr( 'data-context', context );
			this.scrollToElement( $trigger, false );
		},

		/**
		 * Switch inner context.
		 *
		 * @since 2.0.0
		 * @param {string} id - The option ID.
		 * @param {string} context - The new context.
		 * @return {void}
		 */
		switchActiveContext: function( id, context ) {
			this.$el.find( id ).attr( 'data-context', context ).scrollTop( 0 );
		},

		renderElementSettings: function( view ) {
			this.openSidebarAndShowEOTab();
			this.$el.find( '#fusion-builder-sections-eo' ).append( view.render().el );

			FusionPageBuilderApp.SettingsHelpers.renderDialogMoreOptions( view );
			this.changeTabTitle();
		},

		openSidebarAndShowEOTab: function() {
			var $eoTrigger = this.$el.find( '.fusion-builder-toggles a[href="#fusion-builder-sections-eo"]' );

			if ( ! $eoTrigger.hasClass( 'fusion-active' ) ) {
				$eoTrigger.trigger( 'click' );
			}

			// Open sidebar if not open.
			if ( ! this.panelIsOpen() ) {
				this.openSidebar();
			}

			this.$el.find( '#fusion-builder-sections-eo' ).scrollTop( 0 );
		},

		/**
		 * Change the tab title(that are for both navigator and element options) between navigator and element options.
		 */
		changeTabTitle: function() {
			var button = this.$el.find( 'a[href="#fusion-builder-sections-eo"]' );
			var navWrapper = this.$el.find( '.awb-builder-nav-wrapper' );
			var showNavigator = navWrapper.is( ':visible' );

			if ( showNavigator ) {
				button.find( '.label-navigator, .fusiona-navigator' ).removeClass( 'hidden' );
				button.find( '.label-options, .fusiona-pen' ).addClass( 'hidden' );
			} else {
				button.find( '.label-navigator, .fusiona-navigator' ).addClass( 'hidden' );
				button.find( '.label-options, .fusiona-pen' ).removeClass( 'hidden' );
			}
		}

	} );

}( jQuery ) );
;/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	/**
	 * Builder Container View.
	 *
	 * @since 2.0.0
	 */
	FusionPageBuilder.PanelView = Backbone.View.extend( {

		template: FusionPageBuilder.template( jQuery( '#fusion-builder-panel-template' ).html() ),
		className: 'fusion-builder-custom-panel',
		events: {
			'click .fusion-panel-link': 'showTabs',
			'click .fusion-sub-section-link': 'showTabs'
		},

		/**
		 * Initialization.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.$el.attr( 'data-id', this.model.get( 'id' ) );
			this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
			this.$el.attr( 'data-context', this.model.get( 'innerContext' ) );
		},

		/**
		 * Render the model.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		render: function() {
			this.setIcon();

			this.$el.html( this.template( this.model.attributes ) );

			return this;
		},

		setIcon: function() {
			var icon = this.model.get( 'icon' );

			if ( 'undefined' !== typeof this.model.get( 'alt_icon' ) ) {
				icon = this.model.get( 'alt_icon' );
			}
			if ( 'undefined' !== typeof icon && -1 === icon.indexOf( 'fusiona' ) ) {
				delete this.model.attributes.icon;
			} else {
				this.model.set( 'icon', icon );
			}
		},

		/**
		 * Removes panel.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removePanel: function() {

			// Remove view from manager.
			FusionApp.sidebarView.viewManager.removeView( this.model.get( 'cid' ) );

			this.remove();
		},

		/**
		 * Show or hide tabs.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The click event.
		 * @return {void}
		 */
		showTabs: function( event ) {
			var $clickTarget = jQuery( event.currentTarget ),
				$section     = $clickTarget.closest( '.fusion-sidebar-section' ),
				tab,
				tabSettings,
				id,
				tabCid = FusionApp.sidebarView.viewManager.generateCid(),
				view,
				tabView,
				fields = this.model.get( 'fields' ),
				alreadyOpen = false,
				$visiblePanel;

			event.preventDefault();
			FusionApp.data.postMeta._fusion = FusionApp.data.postMeta._fusion || {};

			if ( $clickTarget.parent().find( 'li' ).length ) {
				if ( 'true' === $clickTarget.parent().find( 'a.fusion-panel-link' ).attr( 'aria-expanded' ) ) {
					alreadyOpen = true;
				}

				// Close all open lists first.
				$section.find( '.fusion-builder-custom-panel ul li' ).hide();
				$section.find( '.fusion-builder-custom-panel ul a.fusion-panel-link' ).attr( 'aria-expanded', 'false' );

				// Open the item that was clicked.
				if ( ! alreadyOpen ) {
					$clickTarget.parent().find( 'li' ).show();
					$clickTarget.parent().find( 'a.fusion-panel-link' ).attr( 'aria-expanded', 'true' );
				} else {
					$clickTarget.parent().find( 'li' ).hide();
					$clickTarget.parent().find( 'a.fusion-panel-link' ).attr( 'aria-expanded', 'false' );
				}
			} else {

				// Scroll to top when new tab is opened.
				setTimeout( function() {
					$visiblePanel = $section.find( '.fusion-panels' ).filter( ':visible' );

					if ( 0 === $visiblePanel.length ) {
						$visiblePanel = $section.find( '.fusion-tabs' ).filter( ':visible' );
					}

					$visiblePanel.scrollTop( 0 );

				}, 50 );

				if ( $clickTarget.hasClass( 'fusion-sub-section-link' ) ) {
					id  = $clickTarget.attr( 'id' );
					tab = fields[ id ].fields;
				} else {
					id  = this.model.get( 'id' );
					tab = fields;
				}

				if ( 'shortcode_styling' === id || 'fusion_builder_elements' === id  ) {
					FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBE' );
					return;
				}
				if ( 'fusion_builder_addons' === id ) {
					FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBAO' );
					return;
				}

				// To do, check if tab view has already been created and if so just show.
				if ( ! $section.find( '.fusion-builder-custom-tab#tab-' + id ).length ) {
					tabSettings = {
						model: new FusionPageBuilder.Tab( {
							fields: tab,
							id: id,
							type: 'undefined' !== typeof this.model.get( 'innerContext' ) ? this.model.get( 'innerContext' ).toUpperCase() : this.model.get( 'context' ).toUpperCase(),
							cid: tabCid,
							label: jQuery( event.currentTarget ).data( 'label' )
						} )
					};
					view = new FusionPageBuilder.TabView( tabSettings );
					FusionApp.sidebarView.viewManager.addView( tabCid, view );
					$section.find( '.fusion-tabs' ).append( view.render().el );
				} else {
					tabView = FusionApp.sidebarView.viewManager.getView( $section.find( '.fusion-builder-custom-tab#tab-' + id ).data( 'cid' ) );
					if ( 'undefined' !== typeof tabView ) {
						tabView.initialCheckDependencies();
					}
					tabView.showTab();
				}

				$section.find( '.fusion-tabs' ).show();
				$section.find( '.fusion-panels' ).hide();
				$section.find( '.fusion-builder-custom-tab:not( #tab-' + id + ')' ).hide();
			}
		}
	} );
}( jQuery ) );
;/* global FusionApp, originalOptionName, fusionAppConfig, fusionSanitize, FusionPageBuilder, fusionOptionNetworkNames, fusionReturnMediaQuery */
/* jshint -W098, -W117, -W024 */
/* eslint no-unused-vars: 0 */
/* eslint max-depth: 0 */
( function() {

	/**
	 * Builder Container View.
	 *
	 * @since 6.0
	 */
	window.avadaPanelIFrame = {

		/**
		 * All the fields, flattened.
		 *
		 * @since 6.0
		 */
		fields: {},

		/**
		 * An object containing field-triggering dependencies.
		 *
		 * @since 6.0
		 */
		fieldOutputDependencies: {},

		/**
		 * Apply a callback to an option's value.
		 *
		 * @since 6.0
		 * @param {mixed}  value - The value.
		 * @param {string} callback - The name of the callback function.
		 * @param {mixed}  args - Arguments to pass-on to the callback function.
		 * @return {mixed}            The value after it's been passed through the callback.
		 */
		applyCallback: function( value, callback, args ) {
			args = args || false;
			if ( _.isFunction( FusionApp.callback[ callback ] ) ) {
				return FusionApp.callback[ callback ]( value, args );
			} else if ( _.isFunction( window[ callback ] ) ) {
				return window[ callback ]( value, args );
			} else if ( _.isFunction( fusionSanitize[ callback ] ) ) {
				return fusionSanitize[ callback ]( value, args );
			} else if ( _.isFunction( FusionPageBuilder.Callback.prototype[ callback ] ) ) {
				return FusionPageBuilder.Callback.prototype[ callback ]( value, args );
			}
			return value;
		},

		/**
		 * Apply refresh JS callback.
		 *
		 * @since 6.0
		 * @param {Object} partials - The refresh arguments.
		 * @param {mixed}  value    - The value.
		 * @return {boolean} - Whether or not the tests have passed.
		 */
		applyRefreshCallbacks: function( partials, value ) { // jshint ignore:line
			var self         = this,
				passed       = true,
				initialValue = self.getValueClone( value );

			// Apply callbacks.
			if ( 'undefined' !== typeof partials ) {
				_.each( partials, function( partial ) {

					// Skip if callback is not defined.
					if ( 'undefined' === typeof partial.js_callback ) {
						return false;
					}

					partial.js_callback[ 1 ] = ( _.isUndefined( partial.js_callback[ 1 ] ) ) ? '' : partial.js_callback[ 1 ];
					if ( ! self.applyCallback( initialValue, partial.js_callback[ 0 ], partial.js_callback[ 1 ] ) ) {
						passed = false;
					}
				} );
			}

			return passed;
		},

		/**
		 * Triggers a partial refresh on the preview iframe.
		 *
		 * @param {string} id - The setting ID.
		 * @param {Object} partials - The partial-refresh arguments.
		 * @param {mixed}  value - The value.
		 * @param {string} cid - The model CID.
		 * @return {void}
		 */
		partialRefresh: function( id, partials, value, cid ) { // jshint ignore:line
			var self      = this,
				postData  = FusionApp.getAjaxData( 'fusion_app_partial_refresh' ),
				$element;

			_.each( partials, function( partial, key ) {
				var skip = false;
				if ( partial.skip_for_template ) {
					_.each( partial.skip_for_template, function( overrideSkip ) {
						if ( FusionApp.data.template_override[ overrideSkip ] ) {
							skip = true;
						}
					} );
					if ( skip ) {
						delete partials[ key ];
					}
				}
			} );

			if ( _.isEmpty( partials ) ) {
				return;
			}

			// Add loader.
			if ( 'undefined' !== typeof partials ) {
				_.each( partials, function( partial ) {
					$element = jQuery( '#fb-preview' ).contents().find( 'body' ).find( partial.selector );
					if ( $element.length ) {
						$element.append( '<div id="fusion-loader"><span class="fusion-slider-loading"></span></div>' );
					} else {
						$element = jQuery( '#fb-preview' ).contents().find( 'head' ).find( partial.selector );
						if ( $element.length ) {
							jQuery( 'body' ).append( '<div id="fusion-loader"><span class="fusion-slider-loading"></span></div>' );
						}
					}
				} );

				// Add loader on option.
				jQuery( 'li[data-option-id="' + id + '"]' ).addClass( 'partial-refresh-active' );
			}

			postData.partials = partials;

			jQuery.ajax( {
				type: 'POST',
				url: fusionAppConfig.ajaxurl,
				dataType: 'json',
				data: postData,

				success: function( output ) {
					_.each( output, function( content, scopedID ) {
						var ariaLabel = partials[ scopedID ].aria_label || false,
							successTriggerEvents = 'string' === typeof partials[ scopedID ].success_trigger_event ? partials[ scopedID ].success_trigger_event.split( ' ' ) :  partials[ scopedID ].success_trigger_event;

						content  = FusionApp.removeScripts( content, cid );
						$element = jQuery( '#fb-preview' ).contents().find( 'html' ).find( partials[ scopedID ].selector );
						if ( 'undefined' !== typeof content ) {
							if ( ! content.length ) {
								content = '';
							}
							if ( partials[ scopedID ].after ) {
								$element.after( content );
							} else if ( partials[ scopedID ].container_inclusive ) {
								$element.replaceWith( content );
							} else {
								$element.html( content );
							}
							jQuery( '#fusion-loader' ).remove();
						} else {
							$element.html( '' );
							jQuery( '#fusion-loader' ).remove();
						}

						// Remove loader on option.
						jQuery( 'li[data-option-id="' + id + '"]' ).removeClass( 'partial-refresh-active' );

						if ( partials[ scopedID ].success_trigger_event ) {

							_.each( successTriggerEvents, function( successTriggerEvent ) {

								// Trigger event on parent frame.
								window.dispatchEvent( new Event( successTriggerEvent ) );

								// Trigger event on preview frame.
								window.frames[ 0 ].window.dispatchEvent( new Event( successTriggerEvent ) );

								// If the event is a function, run it.
								if ( 'function' === typeof window[ successTriggerEvent ] ) {
									window[ successTriggerEvent ]();
								}
								if ( 'function' === typeof window.frames[ 0 ].window[ successTriggerEvent ] ) {
									window.frames[ 0 ].window[ successTriggerEvent ]();
								}
							} );
						}

						$element.removeClass( 'fusion-panel-customizable' );
						FusionApp.sidebarView.createEditShortcuts();
					} );

					FusionApp.injectScripts( cid );

					setTimeout( function() {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-partial-' + id );
					}, 100 );
				}
			} );
		},

		/**
		 * Get a clone of a value.
		 * Avoids default JS behavior of creating references instead of clones.
		 *
		 * @since 6.0
		 * @param {mixed} value - The value.
		 * @return {mixed} - Returns a verbatim copy of value.
		 */
		getValueClone: function( value ) {
			return value;
		},

		/**
		 * Gets the value from POs & TOs.
		 * If PO value takes precendence then return PO val, otherwise fallback to TO val.
		 *
		 * @param {string} id - The setting ID.
		 * @param {string} choice - In case the value is an object and we want to get the value of a key in that object.
		 * @return {mixed} - Returns the value.
		 */
		getPoToValue: function( id, choice, toOnly ) {
			var mapKey = this.getGlobalMapKey( id );

			if ( 'undefined' === typeof toOnly ) {
				toOnly = false;
			}

			if ( mapKey ) {
				return this.getPoToValueFromGlobalKey( mapKey, choice, toOnly );
			}
			return null;
		},

		/**
		 * Gets the key from the global Options Map.
		 *
		 * @param {string} id - The setting ID.
		 * @return {string} - Returns the option key from the map.
		 */
		getGlobalMapKey: function( id ) {
			var mapKey = id;

			_.each( [ 'is_home', 'is_tag', 'is_category', 'is_author', 'is_date', 'is_singular_post' ], function( condition ) {
				if ( FusionApp.data[ condition ] ) {
					_.find( fusionOptionNetworkNames, function( item, key ) {
						if ( item[ condition ] && id === item[ condition ] ) {
							mapKey = key;
							return true;
						}
					} );
				}
				if ( mapKey ) {
					return true;
				}
			} );

			return mapKey;
		},

		/**
		 * Gets the value given a key to the option-map.
		 *
		 * @param {string} key - The global option-map key.
		 * @param {string|undefined} choice - Used if we want to get a choice from a value object.
		 * @return {string|Object} - Returns the value.
		 */
		getPoToValueFromGlobalKey: function( key, choice, toOnly ) {
			var value = null,
				skip  = false,
				parts;

			// If we have a TO with that key, get its value.
			if ( FusionApp.settings[ key ] ) {
				value = FusionApp.settings[ key ];
			}

			if ( -1 !== key.indexOf( '[' ) ) {

				// Split the key in parts.
				parts = key.split( '[' );

				// Remove unwanted characters.
				parts[ 0 ] = parts[ 0 ].replace( ']', '' );
				parts[ 1 ] = parts[ 1 ].replace( ']', '' );

				if ( FusionApp.settings[ parts[ 0 ] ] && 'undefined' !== typeof FusionApp.settings[ parts[ 0 ] ][ parts[ 1 ] ] ) {
					value = FusionApp.settings[ parts[ 0 ] ][ parts[ 1 ] ];
				}
			}

			// Return early if we only want TO.
			if ( 'undefined' !== typeof toOnly && toOnly ) {
				return value;
			}

			// Check if we have an option map for this key.
			if ( fusionOptionNetworkNames[ key ] ) {

				// If this is an archive, check if there's an override.
				if ( -1 !== FusionApp.data.postDetails.post_id.toString().indexOf( '-archive' ) ) {

					// Do we have a theme-option defined for this key?
					if ( fusionOptionNetworkNames[ key ].archive ) {

						// If we have a TO value for defined theme-option name, get its value.
						if ( 'object' === typeof fusionOptionNetworkNames[ key ].archive && fusionOptionNetworkNames[ key ].archive[ 0 ] ) {

							if ( FusionApp.settings[ fusionOptionNetworkNames[ key ].archive[ 0 ] ] ) {
								value = FusionApp.settings[ fusionOptionNetworkNames[ key ].archive[ 0 ] ];

								// If we have a choice defined, get its value.
								if ( choice && 'undefined' !== value[ choice ] ) {
									value = value[ choice ];
								}
							}
						} else if ( FusionApp.settings[ fusionOptionNetworkNames[ key ].archive ] ) {
							value = FusionApp.settings[ fusionOptionNetworkNames[ key ].archive ];

							// If we have a choice defined, get its value.
							if ( choice && 'undefined' !== value[ choice ] ) {
								value = value[ choice ];
							}
						}
					}
				}
			}

			// Make sure this is not an override that should not be happening.
			// See https://github.com/Theme-Fusion/Avada/issues/8122 for details.
			switch ( key ) {
				case 'header_bg_repeat':
				case 'header_bg_full':
					skip = ( FusionApp.data.postMeta._fusion.header_bg_image && '' === FusionApp.data.postMeta._fusion.header_bg_image.url );
					break;

				case 'bg_repeat':
				case 'bg_full':
					skip = ( FusionApp.data.postMeta._fusion.bg_image && '' === FusionApp.data.postMeta._fusion.bg_image.url );
					break;

				case 'content_bg_repeat':
				case 'content_bg_full':
					skip = ( FusionApp.data.postMeta._fusion.content_bg_image && '' === FusionApp.data.postMeta._fusion.content_bg_image.url );
					break;
			}

			if ( ! skip ) {

				// If we have a post value for defined name, get its value.
				if (
					'undefined' !== typeof FusionApp.data.postMeta._fusion &&
					'undefined' !== typeof FusionApp.data.postMeta._fusion[ key ] &&
					'' !== FusionApp.data.postMeta._fusion[ key ] &&
					'default' !== FusionApp.data.postMeta._fusion[ key ]
				) {
					value = FusionApp.data.postMeta._fusion[ key ];
				} else if (
					'undefined' !== typeof FusionApp.data.postMeta[ key ] &&
					'' !== FusionApp.data.postMeta[ key ] &&
					'default' !== FusionApp.data.postMeta[ key ]
				) {
					value = FusionApp.data.postMeta[ key ];
				}
			}

			// Hack for PTB values.
			if ( 'page_title_bar' === key || 'blog_show_page_title_bar' === key || 'blog_page_title_bar' === key ) {
				value = value.toLowerCase();
				value = 'yes' === value ? 'bar_and_content' : value;
				value = 'yes_without_bar' === value ? 'content_only' : value;
				value = 'no' === value ? 'hide' : value;
			}

			return value;
		},

		/**
		 * Adds .hover class in addition to :hover.
		 *
		 * @since 6.0
		 * @param {mixed} elements - Elements.
		 * @return {mixed} - Returns the elements.
		 */
		addHoverElements: function( elements ) {
			var fakeHover = '';

			if ( 'string' === typeof elements && elements.indexOf( ',' ) ) {
				elements = elements.split( ',' );
			}

			if ( 'string' === typeof elements && -1 !== elements.indexOf( ':hover' ) ) {
				fakeHover        = elements.replace( ':hover', '.hover' ) + ',';
				return fakeHover + elements;
			} else if ( 'object' === typeof elements ) {
				elements = _.toArray( elements );
				_.each( elements, function( element ) {
					if ( -1 !== element.indexOf( ':hover' ) ) {
						fakeHover = element.replace( ':hover', '.hover' );
						elements.push( fakeHover );
					}
				} );
			}
			return elements;
		},

		/**
		 * Generated the CSS for this setting.
		 *
		 * @since 6.0
		 * @param {string} id - The setting-ID.
		 * @param {Object} output - The output arguments.
		 * @param {Object} cssVars - The css variables object.
		 * @param {string} type - TO/PO.
		 * @param {number} preview - Whether option has preview set.
		 * @param {string} fieldType - The field type.
		 * @return {void}
		 */
		generateCSS: function( id, output, cssVars, type, preview, fieldType ) {
			var values = 'TO' === type || 'FBE' === type ? FusionApp.settings : FusionApp.data.postMeta._fusion,
				self   = this,
				origValue,
				value,
				parentValue,
				css,
				ruleCalc,
				responsiveTypograhy;

			values = values || {};

			values      = 'PS' === type ? FusionApp.data.postDetails : values;
			origValue   = values[ id ];
			value       = origValue;
			parentValue = value;

			type = 'FBE' === type ? 'TO' : type;
			if ( ! self.needsPreviewUpdate( id, type ) ) {
				return;
			}

			// Add the style.
			css = '<style type="text/css" id="css-' + id + '">';

			if ( output ) {

				// Loop all output arguments.
				_.each( output, function( rule ) {

					// Reset value on each loop. In case callback of prior messed with it.
					if ( 'PS' !== type ) {
						value = self.getPoToValue( id, rule.choice );
					}

					// Add any missing arguments.
					// Helps avoid costly checks down the line.
					rule = _.defaults( rule, {
						element: '',
						property: '',
						units: '',
						prefix: '',
						suffix: '',
						js_callback: false,
						callback: false,
						value_pattern: '$',
						pattern_replace: false,
						media_query: false,
						function: 'style'
					} );

					// Make sure any manipulation that takes place does not change object.
					rule = jQuery.extend( true, {}, rule );

					// Get sub-value if "choice" i defined in the rule.
					if ( _.isObject( parentValue ) && ! _.isUndefined( rule.choice ) && ! _.isUndefined( parentValue[ rule.choice ] ) ) {
						value = parentValue[ rule.choice ];
					}

					// PO dimension check.
					if ( 'PO' === type && ! _.isObject( parentValue ) && ! _.isUndefined( rule.choice ) ) {
						value = values[ rule.choice ];
					}

					// If preview is required add hover class.
					if ( preview ) {
						rule.element = self.addHoverElements( rule.element );
					}

					// Make sure the element is a string.
					if ( _.isObject( rule.element ) ) {
						rule.element = jQuery.map( rule.element, function( val ) {
							return [ val ];
						} );

						rule.element = rule.element.join( ',' );
					}

					if ( 'attr' === rule[ 'function' ] ) {
						self.elementAttrs( rule, value );
					} else if ( 'html' === rule[ 'function' ] ) {
						self.elementHtmlContent( rule, value );
					} else {

						// Modify the value for some field-types.
						if ( FusionApp.sidebarView.flatToObject[ id ] && FusionApp.sidebarView.flatToObject[ id ].type ) {
							switch ( FusionApp.sidebarView.flatToObject[ id ].type ) {
							case 'media':
								value = ( value && value.url ) ? value.url : '';
								if ( rule.property && 'background-image' === rule.property && '' === value ) {
									value = 'none';
								}
								break;
							}
						}

						if ( _.isObject( value ) ) {
							ruleCalc = jQuery.extend( true, {}, rule );

							// Add the CSS.
							_.each( value, function( val, key ) {
								if ( rule.element && ( 'margin' === rule.property || 'padding' === rule.property ) && ( 'top' === key || 'bottom' === key || 'left' === key || 'right' === key ) ) {
									ruleCalc.property = rule.property + '-' + key;
								} else if ( ! rule.property || '' === rule.property ) {
									ruleCalc.property = key;
								}
								css += self.getSingleCSS( val, ruleCalc );
							} );
						} else {

							// Value is not an object so this is simple.
							css += self.getSingleCSS( value, rule );
						}
					}
				} );
			}

			if ( cssVars ) {
				_.each( cssVars, function( cssVar ) {

					// Reset value on each loop.  In case callback of prior messed with it.
					var varVal   = 'undefined' !== typeof cssVar.po && false === cssVar.po ? self.getPoToValue( id, cssVar.choice, true ) : self.getPoToValue( id, cssVar.choice, false ),
						selector = ':root';

					// Get sub-value if we have a 3rd argument.
					if ( _.isObject( varVal ) && ! _.isUndefined( cssVar.choice ) && ! _.isUndefined( varVal[ cssVar.choice ] ) ) {
						if ( ( 'font-style' === cssVar.choice || 'font-weight' === cssVar.choice ) && 'string' === typeof varVal[ 'font-family' ] && varVal[ 'font-family' ].includes( 'var(' ) ) {
							varVal = window.awbTypographySelect.getVarString( parentValue[ 'font-family' ], cssVar.choice );
						} else {
							varVal = varVal[ cssVar.choice ];
						}
					}

					// Make sure we have a pattern.
					cssVar.value_pattern = 'undefined' === typeof cssVar.value_pattern ? '$' : cssVar.value_pattern;

					varVal = ( varVal ) ? varVal : '';
					if ( cssVar.choice && varVal && 'object' === typeof varVal[ cssVar.choice ] ) {
						varVal = varVal[ cssVar.choice ];
					}

					if ( cssVar.exclude ) {
						if ( 'string' === typeof cssVar.exclude ) {
							cssVar.exclude = [ cssVar.exclude ];
						}
						_.each( cssVar.exclude, function( exclusion ) {
							if ( varVal === exclusion ) {
								varVal = '';
							}
						} );
					}

					varVal = cssVar.value_pattern.replace( /\$/g, varVal );

					if ( 'undefined' !== typeof cssVar.callback ) {
						varVal = self.applyCallback( varVal, cssVar.callback[ 0 ], cssVar.callback[ 1 ] );
					}

					// If a selector is set use that, since it is more performant.
					if ( 'string' === typeof cssVar.element ) {
						selector = cssVar.element;
					}

					// For live editor, if the text-transform is not set, then override with inherit to display like in front-end.
					if ( 'text-transform' === cssVar.choice && '' === varVal  ) {
						varVal = 'inherit';
					}

					css += selector + '{' + cssVar.name + ':' + varVal + ';}';
				} );
			}
			css += '</style>';

			// Inject into frame.
			if ( jQuery( '#fb-preview' ).contents().find( 'head' ).find( '#css-' + id ).length ) {
				jQuery( '#fb-preview' ).contents().find( 'head' ).find( '#css-' + id ).remove();
			}
			jQuery( '#fb-preview' ).contents().find( 'head' ).append( css );

			// Trigger special JS.
			responsiveTypograhy = [ 'h1_typography', 'h2_typography', 'h3_typography', 'h4_typography', 'h5_typography', 'h6_typography' ];
			if ( _.contains( responsiveTypograhy, id ) ) {
				this.updateResponsiveTypography( id, origValue );
			}

			// Store it on App.
			if ( 'TO' === type || 'FBE' === type ) {
				FusionApp.storedToCSS[ id ] = css;
			} else {
				FusionApp.storedPoCSS[ id ] = css;
			}
		},

		/**
		 * Change the contents of an element.
		 *
		 * @since 6.0
		 * @param {Object} rule - The output arguments.
		 * @param {mixed} value - The value.
		 * @return {void}
		 */
		elementHtmlContent: function( rule, value ) {
			var self = this,
				element,
				exclude = self.getExcludeBool( value, rule );

			if ( true === exclude ) {
				return;
			}

			// Apply value_pattern and js_callback.
			value = self.getCssValue( value, rule );

			// Find the element.
			element = jQuery( '#fb-preview' ).contents().find( rule.element );

			// Change the contents of the element.
			element.html( value );
		},

		/**
		 * Change the attributes of an element.
		 *
		 * @since 6.0
		 * @param {Object} rule - The output arguments.
		 * @param {mixed} value - The value.
		 * @return {void}
		 */
		elementAttrs: function( rule, value ) {
			var self = this,
				attr,
				element,
				exclude = self.getExcludeBool( value, rule );

			if ( _.isUndefined( rule.attr ) || _.isEmpty( rule.attr ) ) {
				return;
			}

			if ( true === exclude ) {
				return;
			}

			// Apply value_pattern and js_callback.
			value = self.getCssValue( value, rule );
			if ( rule.toLowerCase ) {
				value = value.toLowerCase();
			}

			// If value is empty, early exit.
			// This helps with cases where js_callback returns empty
			// since in those cases we don't want the rule applied.
			if ( '' === value ) {
				return;
			}

			// Find the element.
			element = jQuery( '#fb-preview' ).contents().find( rule.element );

			// Get the attribute.
			attr = element.attr( rule.attr );

			if ( _.isUndefined( attr ) ) {
				attr = '';
			}

			// If we want to remove and add attributes, we need some extra calcs (useful for CSS classes).
			if ( ! _.isUndefined( rule.remove_attrs ) && ! _.isEmpty( rule.remove_attrs ) && '' !== attr ) {
				_.each( rule.remove_attrs, function( attrToRemove ) {

					if ( 'class' === rule.attr ) {
						element.removeClass( attrToRemove );
					}

					// Some attributes use comma-separated values (mostly data attributes).
					attr = attr.split( attrToRemove + ',' ).join( '' );

					// If separated using a spoace, remove the space as well.
					attr = attr.split( attrToRemove + ' ' ).join( '' );

					// Final check: Remove attribute if not already removed from the above 2 rules.
					attr = attr.split( attrToRemove ).join( '' );
				} );

				attr += ( '' !== attr ) ? ' ' : '';
				attr += value;

				if ( 'class' === rule.attr ) {
					element.addClass( attr );
				} else {
					element.attr( rule.attr, attr );
				}

				return;
			}

			element.attr( rule.attr, value );
		},

		/**
		 * Live-update custom CSS.
		 *
		 * @param {string} newContent - The new CSS.
		 * @return {void}
		 */
		liveUpdateCustomCSS: function( newContent ) {

			var customCSS = 'undefined' !== typeof newContent ? newContent : '';
			if ( jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).length ) {
				jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).html( customCSS );
			}
		},

		/**
		 * Live-update the preview pane.
		 *
		 * @param {string} newContent - The new CSS.
		 * @return {void}
		 */
		liveUpdatePageCustomCSS: function( newContent ) {

			if ( jQuery( '#fb-preview' ).contents().find( '#fusion-builder-page-css' ).length ) {
				jQuery( '#fb-preview' ).contents().find( '#fusion-builder-page-css' ).html( newContent );
			} else {
				newContent = '<style type="text/css" id="fusion-builder-page-css">' + newContent + '</style>';

				// If TO custom CSS exists, make sure to add after.
				if ( jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).length ) {
					jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).after( newContent );
				} else {
					jQuery( '#fb-preview' ).contents().find( 'head' ).append( newContent );
				}
			}
		},

		/**
		 * Update responsive typography.
		 *
		 *
		 * @since 6.0
		 * @param {string} id - Option id.
		 * @param {Object} values - The values.
		 * @return {void}
		 */
		updateResponsiveTypography: function( id, values ) {
			var heading = id.split( '_' )[ 0 ];
			if ( 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.fusionCalculateResponsiveTypeValues ) {
				document.querySelector( '#fb-preview' ).contentWindow.document.body.dispatchEvent( new CustomEvent( 'fusion-typography', { detail: { heading: heading, values: values } } ) );
			}
		},

		/**
		 * Get CSS from rule.
		 *
		 * This is a helper function that only accepts a string for the value.
		 * Used by the generateCSS method in this object.
		 *
		 * @since 6.0
		 * @param {string} value - The value.
		 * @param {Object} rule - The rule.
		 * @return {string} - The CSS.
		 */
		getSingleCSS: function( value, rule ) {
			var self    = this,
				exclude = self.getExcludeBool( value, rule ),
				css     = '';

			if ( true === exclude ) {
				return '';
			}

			// Apply value_pattern and js_callback.
			value = self.getCssValue( value, rule );

			// Add prefix, units, suffix.
			value = rule.prefix + value + rule.units + rule.suffix;

			// Generate the CSS.
			if ( rule.media_query ) {

				// If the media-query should be dynamically generated, get it using a helper function.
				if ( 0 === rule.media_query.indexOf( 'fusion-' ) ) {
					rule.media_query = fusionReturnMediaQuery( rule.media_query );
				}
				css += rule.media_query + '{';
			}
			css += rule.element + '{' + rule.property + ':' + value + ';}';
			if ( rule.media_query ) {
				css += '}';
			}

			// If value is empty, return empty string, otherwise return our css.
			return ( ! value || _.isEmpty( value ) ) ? '' : css;
		},

		/**
		 * Figure out if the exclude argument evaluates to true or false.
		 *
		 * This is a helper function that only accepts a string for the value.
		 * Used by the generateCSS method in this object.
		 *
		 * @since 6.0
		 * @param {string} value - The value.
		 * @param {Object} rule - The rule.
		 * @return {boolean} - Whether or not the exclude params evaluate to true or not.
		 */
		getExcludeBool: function( value, rule ) {
			var self    = this,
				exclude = false;

			// If we have defined an "exclude" argument, then make sure the value is not in there.
			// If the value is equal to one defined in the exclude argument then skip this.
			if ( ! _.isUndefined( rule.exclude ) ) {
				if ( value === rule.exclude ) {
					exclude = true;
				}
				if ( _.isArray( rule.exclude ) ) {
					_.each( rule.exclude, function( exclusion ) {

						// It's == and not === on purpose, please do not change.
						if ( exclusion == value ) { // jshint ignore:line
							exclude = true;
						}
						if ( ! exclude && _.isEmpty( exclusion ) && _.isEmpty( value ) ) {
							exclude = true;
						}
						if ( ! exclude && ! _.isUndefined( value.url ) && exclusion === value.url ) {
							exclude = true;
						}
					} );
				}
			}
			return exclude;
		},

		/**
		 * Applies any extra rules such as value_pattern and js_callback.
		 *
		 * @since 6.0
		 * @param {mixed}  value - The value.
		 * @param {Object} rule - The rule.
		 * @return {mixed} - Returns the value.
		 */
		getCssValue: function( value, rule ) {
			var self = this;

			// If we have defined a value_pattern, apply it.
			if ( 'undefined' !== typeof rule.value_pattern && false !== rule.value_pattern ) {
				value = rule.value_pattern.replace( /\$/g, value );

				// If we're using pattern_replace, apply those values.
				if ( rule.pattern_replace ) {
					_.each( rule.pattern_replace, function( replaceRuleReplace, replaceRuleSearch ) {

						var replaceSetting = replaceRuleReplace.replace( originalOptionName + '[', '' ).replace( ']', '' ),
							replaceValue   = replaceRuleReplace;

						if ( replaceSetting !== replaceRuleReplace ) {
							replaceValue   = FusionApp.settings[ replaceSetting ];
						}

						value = value.replace( replaceRuleSearch, replaceValue ).replace( replaceRuleSearch, replaceValue );
					} );
				}
			}

			// Apply any functions defined in js_callback.
			if ( rule.js_callback ) {
				rule.js_callback[ 1 ] = ( _.isUndefined( rule.js_callback[ 1 ] ) ) ? '' : rule.js_callback[ 1 ];
				value = self.applyCallback( value, rule.js_callback[ 0 ], rule.js_callback[ 1 ] );
			} else if ( rule.callback ) {
				rule.callback[ 1 ] = ( _.isUndefined( rule.callback[ 1 ] ) ) ? '' : rule.callback[ 1 ];
				value = self.applyCallback( value, rule.callback[ 0 ], rule.callback[ 1 ] );
			}

			if ( rule.property && 'background-image' === rule.property && 'string' === typeof value && -1 === value.indexOf( 'url(' ) ) {

				// Make sure it's a URL. We need this check for compatibility with gradient backgrounds.
				if ( -1 !== value.indexOf( '/' ) && -1 !== value.indexOf( '.' ) && -1 === value.indexOf( '-gradient(' ) ) {
					value = 'url(' + value + ')';
				}
			}

			return value;
		},

		/**
		 * Figure out if this change requires updating the preview.
		 *
		 * @since 6.0
		 * @param {string} id - The setting ID.
		 * @param {string} type - TO|PO|TAXO.
		 * @return {boolean} - Whether or not we should update the preview.
		 */
		needsPreviewUpdate: function( id, type ) {
			if ( 'scheme_type' === id || 'post_title' === id || 'name' === id ) {
				return true;
			}
			if ( 'TO' === type && this.validateChangeContext( id, type )[ 1 ] !== type ) {
				return false;
			}
			return true;
		},

		/**
		 * Gets the context for our option-change.
		 * If we change a TO that has a PO overriding its value then we should not do anything.
		 * Takes into account page-options, term-options & theme-options.
		 *
		 * @since 6.0
		 * @param {string} id - The option ID.
		 * @param {string} type - PO|TO|TAXO.
		 * @return {Array} - [id, type], The option that should actually be applied.
		 */
		validateChangeContext: function( id, type ) {
			var key         = id,
				found       = false,
				networkKeys = {
					TAXO: 'term',
					PO: 'post',
					TO: 'theme'
				};

			// Make sure our "type" param is correct.
			type = ( 'undefined' === typeof networkKeys[ type ] ) ? 'TO' : type;

			// Check if PO/Tax.
			_.each( fusionOptionNetworkNames, function( definition, definitionKey ) {

				// Key found in our options map.
				if ( false === found && ( definitionKey === id || ( 'undefined' !== typeof definition[ networkKeys[ type ] ] && definition[ networkKeys[ type ] ] === id ) ) ) {

					// Should this value override TOs?
					if ( FusionApp.data.postMeta._fusion[ key ] && '' !== FusionApp.data.postMeta._fusion[ key ] && 'default' !== FusionApp.data.postMeta._fusion[ key ] ) {
						found = [ key, type ];
					}
				}
			} );
			return ( false === found ) ? [ key, 'TO' ] : found;
		},

		/**
		 * Populate the field output-dependencies.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		populateFieldOutputDependencies: function() {
			var self = this,
				setting;

			// No need to run if self.fieldOutputDependencies is already populated.
			if ( ! self.fieldOutputDependencies || ! _.isEmpty( self.fieldOutputDependencies ) ) {
				return;
			}
			if ( ! FusionApp.sidebarView.flatToObject || _.isEmpty( FusionApp.sidebarView.flatToObject ) ) {
				FusionApp.sidebarView.getFlatToObject();
			}

			_.each( FusionApp.sidebarView.flatToObject, function( field ) {
				if ( field.output ) {
					_.each( field.output, function( output ) {
						if ( output.js_callback ) {
							if ( output.js_callback && output.js_callback[ 0 ] && 'conditional_return_value' === output.js_callback[ 0 ] ) {
								_.each( output.js_callback[ 1 ].conditions, function( callbackArray ) {
									if ( -1 !== callbackArray[ 0 ].indexOf( originalOptionName + '[' ) ) {
										setting = callbackArray[ 0 ].replace( originalOptionName + '[', '' ).replace( ']', '' );

										if ( ! self.fieldOutputDependencies[ setting ] ) {
											self.fieldOutputDependencies[ setting ] = [];
										}
										if ( -1 === self.fieldOutputDependencies[ setting ].indexOf( field.id ) ) {
											self.fieldOutputDependencies[ setting ].push( field.id );
										}
									}
								} );
							}
						}
						if ( output.pattern_replace ) {
							_.each( output.pattern_replace, function( replaceRuleReplace, replaceRuleSearch ) {

								var scopedSetting = replaceRuleReplace.replace( originalOptionName + '[', '' ).replace( ']', '' );

								if ( ! self.fieldOutputDependencies[ scopedSetting ] ) {
									self.fieldOutputDependencies[ scopedSetting ] = [];
								}
								if ( -1 === self.fieldOutputDependencies[ scopedSetting ].indexOf( field.id ) ) {
									self.fieldOutputDependencies[ scopedSetting ].push( field.id );
								}
							} );
						}
					} );
				}
			} );
		}
	};
}( jQuery ) );
;/* global FusionApp, fusionBuilderTabL10n, fusionAllElements, FusionEvents, fusionBuilderText, avadaPanelIFrame, awbTypographySelect */
/* jshint -W024 */
/* eslint max-depth: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	/**
	 * Builder Container View.
	 *
	 * @since 2.0.0
	 */
	FusionPageBuilder.TabView = Backbone.View.extend( {

		/**
		 * The template.
		 *
		 * @since 2.0.0
		 */
		template: FusionPageBuilder.template( jQuery( '#fusion-builder-tab-template' ).html() ),

		/**
		 * An object containing events and the method
		 * each one of them triggers.
		 *
		 * @since 2.0.0
		 */
		events: {
			'click .fusion-builder-go-back': 'showSections',
			'change input': 'optionChange',
			'fusion-change input': 'optionChange',
			'keyup input:not(.fusion-slider-input)': 'optionChange',
			'change select': 'optionChange',
			'keyup textarea': 'optionChange',
			'change textarea': 'optionChange',
			'click .upload-image-remove': 'removeImage',
			'click .option-preview-toggle': 'previewToggle',
			'click .fusion-panel-description': 'showHideDescription',
			'click .fusion-panel-shortcut': 'defaultPreview',
			'click .fusion-quick-option': 'quickOption',
			'click .option-has-responsive': 'showResponsiveOptions',
			'click .fusion-responsive-options li a': 'changeResponsiveOption',
			'mouseleave .fusion-builder-option': 'hideResponsiveOptions'
		},

		/**
		 * The class-name.
		 *
		 * @since 2.0.0
		 */
		className: 'fusion-builder-custom-tab',

		/**
		 * Initialization method.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialize: function() {
			this.$el.attr( 'id', 'tab-' + this.model.get( 'id' ) );
			this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
			this.$el.attr( 'data-type', this.model.get( 'type' ) );
			this._updatePreview  = _.debounce( _.bind( this.updatePreview, this ), 1000 );
			this._validateOption = _.debounce( _.bind( this.validateOption, this ), 1000 );
			this.options         = this.model.get( 'fields' );
			this.type            = this.model.get( 'type' );

			this.initialCheckDependencies();

			// Active states selected for element.
			this.activeStates     = {};
			this.$targetEl        = false;
			this._tempStateRemove = _.debounce( _.bind( this.tempStateRemove, this ), 3000 );
			this.hasSlug          = true;

			if ( 'import_export' === this.model.get( 'id' ) ) {
				this.listenTo( FusionEvents, 'fusion-to-changed', this.updateExportCode );
			}
			if ( 'import_export_po' === this.model.get( 'id' ) ) {
				this.listenTo( FusionEvents, 'fusion-po-changed', this.updateExportCode );
				this.listenTo( FusionEvents, 'fusion-ps-changed', this.updateExportCode );
			}
		},

		/**
		 * Render the model.
		 *
		 * @since 2.0.0
		 * @return {Object} this
		 */
		render: function() {
			this.$el.html( this.template( this.model.attributes ) );
			this.initOptions();
			FusionApp.sidebarView.$el.find( '.fusion-sidebar-section:visible' ).scrollTop( 0 );
			return this;
		},

		/**
		 * Navigates to a separate tab.
		 *
		 * @since 7.0
		 * @param object event The click event.
		 * @return {void}
		 */
		quickOption: function( event ) {
			var $trigger = jQuery( event.target );

			event.preventDefault();

			FusionApp.sidebarView.openOption( $trigger.data( 'fusion-option' ) );
		},

		/**
		 * Show tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		showTab: function() {
			this.$el.show();
			FusionApp.sidebarView.$el.find( '.fusion-sidebar-section:visible' ).scrollTop( 0 );
		},

		/**
		 * Checks the dependencies for this tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		initialCheckDependencies: function() {
			var self = this;

			// Initialize option dependencies
			setTimeout( function() {

				// Only check dependencies when global option or page option.
				// Ignore dependencies on search.
				if ( 'TO' === self.type || 'PO' === self.type || 'FBE' === self.type ) {
					self.dependencies = new FusionPageBuilder.Dependencies( self.options, self );
				}
			}, 10 );
		},

		/**
		 * Trigger actions when an option changes.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		optionChange: function( event ) {

			// Validation.
			var result = this.validateOption( event ); // jshint ignore:line

			if ( result ) {
				if ( this.needsDebounce( event ) ) {
					this._updatePreview( event );
				} else {
					this.updatePreview( event );
				}
			}
		},

		/**
		 * Removes tab.
		 *
		 * @since 2.0.0
		 * @return {void}
		 */
		removeTab: function() {

			// Remove view from manager.
			FusionApp.sidebarView.viewManager.removeView( this.model.get( 'cid' ) );

			this.remove();
		},

		showHideDescription: function( event ) {
			var $element = jQuery( event.currentTarget );
			var $tooltip = $element.closest( '.fusion-builder-option' ).find( '.fusion-tooltip-description' );
			var $text    = $tooltip.text();

			$element.closest( '.fusion-builder-option' ).find( '.description' ).first().slideToggle( 250 );
			$tooltip.text( $text === fusionBuilderText.fusion_panel_desciption_show ? fusionBuilderText.fusion_panel_desciption_hide : fusionBuilderText.fusion_panel_desciption_show );
			$element.toggleClass( 'active' );
		},

		defaultPreview: function( event ) {
			var $element = jQuery( event.currentTarget );

			if ( event ) {
				event.preventDefault();
			}

			if ( FusionApp.sidebarView ) {
				jQuery( '.fusion-builder-toggles a' ).first().trigger( 'click' );
				FusionApp.sidebarView.openOption( $element.data( 'fusion-option' ), 'to' );
			}
		},

		/**
		 * Initialize the options.
		 *
		 * @since 2.0.0
		 * @param {Object} $element - The jQuery element.
		 * @return {void}
		 */
		initOptions: function( $element ) {
			var $thisEl = 'undefined' !== typeof $element && $element.length ? $element : this.$el;

			this.optionColorpicker( $thisEl );
			this.optionRadioButtonSet( $thisEl );
			this.optionCheckboxButtonSet( $thisEl );
			this.optionDimension( $thisEl );
			this.optionSelect( $thisEl );
			this.optionAjaxSelect( $thisEl );
			this.optionMultiSelect();
			this.optionRange( $thisEl );
			this.optionUpload( $thisEl );
			this.optionMultiUpload( $thisEl );
			this.optionCodeBlock( $thisEl );
			this.optionTypography( $thisEl );
			this.optionTypographySets( $thisEl );
			this.optionSwitch( $thisEl );
			this.optionImport( $thisEl );
			this.optionExport( $thisEl );
			this.optionSortable( $thisEl );
			this.optionColorPalette( $thisEl );
			this.optionRaw( $thisEl );
			this.optionLinkSelector( $thisEl );
			this.optionHubSpotMap( $thisEl );
			this.optionMailchimpMap( $thisEl );
			this.optionIconpicker( $thisEl );
			this.optionLayoutConditions( $thisEl );

			if ( 'undefined' === typeof $element ) {
				this.optionRepeater( this.type );
				this.optionToggle( this.type );
			}
		},

		/**
		 * Checks if option update should use debounce.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		needsDebounce: function( event ) {
			var option     = jQuery( event.currentTarget ).closest( '.fusion-builder-option' ),
				id         = option.data( 'option-id' ),
				fields     = this.model.get( 'fields' ),
				field      = fields[ id ],
				debouncers = [ 'primary_color' ];

			if ( 'undefined' === typeof field && option.parent().hasClass( 'repeater-fields' ) ) {
				id    = option.parent().closest( '.fusion-builder-option' ).data( 'option-id' );
				field = fields[ id ];
			}

			if ( debouncers.includes( id ) ) {
				return true;
			}
			if ( 'undefined' !== typeof field && ( 'undefined' !== typeof field.output || 'undefined' !== typeof field.css_vars || ( 'undefined' !== typeof field.transport && 'postMessage' === field.transport ) ) ) {
				return false;
			}
			if ( 'undefined' !== typeof field && ( 'select' === field.type || 'typography' === field.type ) ) {
				return false;
			}
			return true;
		},

		/**
		 * Handles switching between the
		 * theme-options, page-options and search views.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The JS event.
		 * @return {void}
		 */
		showSections: function( event ) {
			var context,
				$section = this.$el.closest( '.fusion-sidebar-section' );

			if ( event ) {
				event.preventDefault();
			}

			if ( 'search' === this.model.get( 'type' ) ) {
				context = this.model.get( 'context' );
				if ( 'PO' === context || 'PS' === context ) {
					FusionApp.sidebarView.setActiveTab( 'po', context );
					jQuery( '#fusion-builder-sections-po .fusion-panels' ).show();
				} else {
					FusionApp.sidebarView.setActiveTab( 'to', context );
					jQuery( '#fusion-builder-sections-to .fusion-panels' ).show();
				}
				FusionApp.sidebarView.clearSearch();
			} else if ( 'TO' === this.model.get( 'type' ) ) {
				jQuery( '#fusion-builder-sections-to .fusion-panels' ).show();
			} else if ( 'FBE' === this.model.get( 'type' ) ) {
				FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBE' );
				$section.find( '.fusion-tabs' ).hide();
				$section.find( '.fusion-panels' ).show();
			} else if ( 'FBAO' === this.model.get( 'type' ) ) {
				FusionApp.sidebarView.switchActiveContext( '#fusion-builder-sections-to', 'FBAO' );
				$section.find( '.fusion-tabs' ).hide();
				$section.find( '.fusion-panels' ).show();
			} else {
				jQuery( '#fusion-builder-sections-po .fusion-panels' ).show();
			}
			this.$el.closest( '.fusion-tabs' ).hide();

			// Remove view since it is always recreated anyway.
			if ( 'fusion-builder-results' === this.model.get( 'id' ) ) {
				this.remove();
			} else {
				FusionApp.sidebarView.scrollToElement( FusionApp.sidebarView.$el.find( 'a#' + this.model.get( 'id' ) ).closest( '.fusion-builder-custom-panel' ), false );
			}
		},

		/**
		 * Gets value if not regular.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {Object} event - The JS event.
		 * @param {mixed} value - The value.
		 * @return {mixed} - Returns value.
		 */
		getValue: function( $target, event, value ) {
			var $realInput;

			// Tweak for multi selects.
			if ( 'checkbox' === $target.attr( 'type' ) && $target.hasClass( 'fusion-select-option' ) ) {
				value = [];
				_.each( $target.parent().find( '.fusion-select-option:checked' ), function( selectedOption ) {
					value.push( jQuery( selectedOption ).val() );
				} );
				return value;
			}

			// Tweak for checkboxes.
			if ( 'checkbox' === $target.attr( 'type' ) ) {
				return $target.is( ':checked' ) ? '1' : '0';
			}

			// Changed URL preview of upload object, update object only.
			if ( $target.hasClass( 'fusion-url-only-input' ) ) {
				$realInput = $target.closest( '.fusion-upload-area' ).find( '.fusion-image-as-object' );
				if ( $realInput.length ) {
					$realInput.val( JSON.stringify( { url: value } ) ).trigger( 'change' );
				}
			}

			// If code block element then need to use method to get val.
			if ( jQuery( event.currentTarget ).parents( '.fusion-builder-option.code' ).length ) {
				return this.codeEditorOption[ jQuery( event.currentTarget ).parents( '.fusion-builder-option.code' ).attr( 'data-index' ) ].getValue();
			}

			// Slider with default.
			if ( $target.hasClass( 'fusion-with-default' ) ) {
				value = $target.parents( '.fusion-builder-option' ).find( '.fusion-hidden-value' ).val();
				value = value || '';
			}

			// Repeater value.
			if ( $target.hasClass( 'fusion-repeater-value' ) && '' !== value ) {
				value = JSON.parse( value );
			}

			return value;
		},

		/**
		 * Checks whether we need to update or not.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {mixed} value - The value.
		 * @param {string} id - The control ID.
		 * @param {Object} save - The saved data.
		 * @return {void}
		 */
		needsUpdate: function( $target, value, id, save ) {
			var parts,
				base,
				optionName;

			// If value hasn't changed.
			if ( value === save[ id ] ) {
				return false;
			}

			// If its a file upload for import.
			if ( $target.hasClass( 'fusion-dont-update' ) || $target.hasClass( 'fusion-import-file-input' ) || 'demo_import' === id ) {
				return false;
			}

			// Repeater value being changed, trigger on parent only.
			if ( $target.parents( '.fusion-builder-option.repeater' ).length && ! $target.hasClass( 'fusion-repeater-value' ) ) {
				if ( $target.hasClass( 'fusion-image-as-object' ) ) {
					if ( 'undefined' === typeof value || '' === value ) {
						value = {
							url: ''
						};
					} else {
						value = JSON.parse( value );
					}
				}
				this.setRepeaterValue( $target.parents( '.fusion-builder-option.repeater' ).find( '.fusion-repeater-value' ), id, $target.parents( '.repeater-row' ).index(), value );
				return false;
			}

			// If value is empty and option doesn't exist (PO).
			if ( '' === value && _.isUndefined( save[ id ] ) && _.isUndefined( save[ id ] ) ) {
				return false;
			}

			// If it's a colorpicker that hasn't been instantiated yet or it's color palette, early exit.
			if ( ( $target && $target.hasClass( 'color-picker' ) && ! $target.hasClass( 'fusion-color-created' ) ) || $target.hasClass( 'fusion-color-palette-color-picker' ) ) {
				return false;
			}

			if ( _.isObject( save[ id ] ) && $target.parents( '.fusion-builder-dimension' ).length && value === save[ id ][ $target.attr( 'name' ) ] ) {
				return false;
			}

			if ( _.isObject( save[ id ] ) && ( $target.closest( '.fusion-builder-option.typography' ).length || $target.closest( '.fusion-builder-option.typography-sets' ).length ) ) {

				if ( -1 !== $target.attr( 'name' ).indexOf( '[' ) ) {

					// Split the key in parts.
					parts = $target.attr( 'name' ).split( '[' );

					// Set the option base.
					base = save[ parts.shift().replace( ']', '' ) ];

					optionName = parts.pop().replace( ']', '' );

					// if we still have extra parts, add them.
					if ( parts.length ) {
						_.each( parts, function( part ) {
							part = part.replace( ']', '' );

							if ( 'undefined' === typeof base[ part ] ) {
								base[ part ] = {};
							}
							base = base[ part ];
						} );
					}
				}

				if ( 'undefined' !== typeof base[ optionName ] &&  value === base[ optionName ] ) {
					return false;
				}
			}

			return true;
		},

		/**
		 * Saves the change.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {mixed} value - The value.
		 * @param {string} id - The setting ID.
		 * @param {Object} save - Saved data.
		 * @param {string} type - TO/FBE etc.
		 * @return {void}
		 */
		saveChange: function( $target, value, id, save, type ) {
			var parts,
				base,
				optionName;

			// Update the settings object.
			if ( $target.closest( '.fusion-builder-option' ).hasClass( 'typography' ) || $target.closest( '.fusion-builder-option' ).hasClass( 'typography-sets' ) || (
				( _.isObject( save[ id ] ) || _.isUndefined( save[ id ] ) ) && (
				$target.parents( '.fusion-builder-dimension' ).length ||
				$target.parents( '.fusion-builder-repeater' ).length
			) ) ) {

				if ( _.isUndefined( save[ id ] ) ) {
					save[ id ] = {};
				}

				if ( -1 !== $target.attr( 'name' ).indexOf( '[' ) ) {

					// Split the key in parts.
					parts = $target.attr( 'name' ).split( '[' );

					// Set the option base.
					base = save[ parts.shift().replace( ']', '' ) ];

					// Se the final option name.
					optionName = parts.pop().replace( ']', '' );

					// if we still have extra parts, add them.
					if ( parts.length ) {
						_.each( parts, function( part ) {
							part = part.replace( ']', '' );

							if ( 'undefined' === typeof base[ part ] ) {
								base[ part ] = {};
							}
							base = base[ part ];
						} );
					}

					if ( 'variant' === optionName ) {
						if ( base[ 'font-weight' ] === awbTypographySelect.getFontWeightFromVariant( value ) && base[ 'font-style' ] === awbTypographySelect.getFontStyleFromVariant( value ) ) {
							return;
						}
						base.variant = value;
						base[ 'font-weight' ] = awbTypographySelect.getFontWeightFromVariant( value );
						base[ 'font-style' ]  = awbTypographySelect.getFontStyleFromVariant( value );
					} else {
						base[ optionName ] = value;
					}
				} else {
					save[ id ][ $target.attr( 'name' ) ] = value;
				}

			} else if ( $target.hasClass( 'fusion-image-as-object' ) || $target.hasClass( 'awb-palette-save' ) ) {
				value = JSON.parse( value );
				save[ id ] = value;
			} else {
				save[ id ] = value;
			}

			// Trigger relevant content change event.
			if ( 'undefined' !== typeof FusionApp.contentChange ) {
				if ( 'TO' === type || 'FBE' === type ) {
					FusionEvents.trigger( 'fusion-to-changed' );
					FusionEvents.trigger( 'fusion-to-' + id + '-changed' );
					FusionApp.contentChange( 'global', 'theme-option' );
					window.dispatchEvent( new Event( 'fusion-to-' + id + '-changed' ) );
				} else if ( 'PO' === type || 'TAXO' === type ) {
					FusionEvents.trigger( 'fusion-po-changed' );
					FusionEvents.trigger( 'fusion-po-' + id + '-changed' );
					FusionApp.contentChange( 'page', 'page-option' );
					window.dispatchEvent( new Event( 'fusion-po-' + id + '-changed' ) );
				} else if ( 'PS' === type ) {
					FusionEvents.trigger( 'fusion-ps-changed' );
					FusionEvents.trigger( 'fusion-' + id + '-changed' );
					FusionApp.contentChange( 'page', 'page-setting' );
					window.dispatchEvent( new Event( 'fusion-page-' + id + '-changed' ) );
				}
			}
		},

		/**
		 * Get save id.
		 *
		 * @since 2.0.0
		 * @param {Object} $target - jQuery object.
		 * @param {string} id - The setting ID.
		 * @param {string} type - TO/FBE etc.
		 * @return {void}
		 */
		getSaveId: function( $target, id, type ) {
			var fields = this.model.get( 'fields' );
			if ( 'PO' === type ) {
				if ( $target.hasClass( 'fusion-po-dimension' ) ) {
					return $target.attr( 'id' );
				} else if ( ( ! _.isUndefined( fields[ id ] ) && _.isUndefined( fields[ id ].not_pyre ) ) && FusionApp.data.singular ) {
					return id;
				}
			}
			return id;
		},

		getSaveLocation: function( type, id ) {
			switch ( type ) {
				case 'FBE':
				case 'TO':
					return FusionApp.settings;

				case 'PS':
					return FusionApp.data.postDetails;

				case 'PO':
				case 'TAXO':
					if ( '_wp_page_template' === id || '_thumbnail_id' === id || '_fusion_builder_custom_css' === id ) {
						return FusionApp.data.postMeta;
					}
					FusionApp.data.postMeta._fusion = FusionApp.data.postMeta._fusion || {};
					return FusionApp.data.postMeta._fusion;
			}
		},

		/**
		 * Updates the preview iframe.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The event triggering the update.
		 * @param {boolean} forced - If set to true, then skips the needsUpdate check.
		 * @param {Array} alreadyTriggeredFields - An array of fields that have already been triggered.
		 *                                         Avoid infinite loops in case of fields inter-dependencies.
		 * @return {void}
		 */
		updatePreview: function( event, forced, alreadyTriggeredFields ) {
			var self    = this,
				$target = jQuery( event.currentTarget ),
				$option = $target.parents( '.fusion-builder-option' ),
				id      = $option.data( 'option-id' ),
				value   = $target.val(),
				type    = $option.data( 'type' ),
				save    = this.getSaveLocation( type, id ),
				preview = $option.find( '.option-preview-toggle' ).length,
				updated = false,
				saveId  = this.getSaveId( $target, id, type ),
				fields  = this.model.get( 'fields' );

			value = this.getValue( $target, event, value );

			if ( true !== forced && ! this.needsUpdate( $target, value, saveId, save ) ) {
				return;
			}

			this.saveChange( $target, value, saveId, save, type );

			if ( 'TO' === type || 'FBE' === type || 'PO' === type ) {
				FusionApp.createMapObjects();
				if ( 'object' === typeof save[ saveId ] ) {
					this.updateSettingsToParams( id + '[' + $target.attr( 'name' ) + ']', value );
					this.updateSettingsToExtras( id + '[' + $target.attr( 'name' ) + ']', value );
					this.updateSettingsToParams( id, save[ saveId ] );
					this.updateSettingsToExtras( id, save[ saveId ] );
					if ( 'TO' === type || 'FBE' === type ) {
						this.updateSettingsToPo( id, save[ saveId ] );
					}
				} else {
					this.updateSettingsToParams( id, value );
					this.updateSettingsToExtras( id, value );
					if ( 'TO' === type || 'FBE' === type ) {
						this.updateSettingsToPo( id, value );
					}
				}

				FusionEvents.trigger( 'fusion-preview-update', id, value );
			}

			// Custom events defined.
			if ( fields[ id ] && 'object' === typeof fields[ id ].events ) {
				_.each( fields[ id ].events, function( customEvent ) {
					FusionEvents.trigger( customEvent, id, value );
				} );
			}

			// Check update_callback args.
			if ( false === this.checkUpdateCallbacks( fields[ id ] ) ) {
				return;
			}

			if ( 'post_title' === id ) {
				this.maybeUpdateSlug( value );
			}

			// Early exit if it is multi select add new field.
			if ( 'undefined' !== typeof jQuery( event.currentTarget ).attr( 'class' ) && 'fusion-multiselect-input' === jQuery( event.currentTarget ).attr( 'class' ) ) {
				return;
			}

			// Early exit if we don't have a field.
			if ( ! fields[ id ] ) {
				return;
			}

			// Check how to update preview. partial_refresh, output;
			if ( fields[ id ].id && 'color_palette' === fields[ id ].id ) {

				// No need to update preview.
				return;
			}

			// Check how to update preview. partial_refresh, output;
			if ( fields[ id ].id && 'custom_css' === fields[ id ].id ) {
				avadaPanelIFrame.liveUpdateCustomCSS( value );
				return;
			}

			if ( fields[ id ].id && '_fusion_builder_custom_css' === fields[ id ].id ) {
				avadaPanelIFrame.liveUpdatePageCustomCSS( value );
				return;
			}

			if ( fields[ id ].id && ( 'visibility_small' === fields[ id ].id || 'visibility_medium' === fields[ id ].id ) ) {
				jQuery( '#fusion-parent-window-css-vars' ).html( ':root{--small_screen_width:' + FusionApp.settings.visibility_small + 'px;}:root{--medium_screen_width:' + FusionApp.settings.visibility_medium + 'px;}' );
				return;
			}

			if ( fields[ id ].output || fields[ id ].css_vars ) {

				// Apply any functions defined in js_callback.
				if ( false !== avadaPanelIFrame.applyRefreshCallbacks( fields[ id ].css_vars, value ) ) {

					// Trigger temporary active state if exists.
					if ( preview ) {
						this.triggerTemporaryState( $option );
					}

					// Live update.
					avadaPanelIFrame.generateCSS( saveId, fields[ id ].output, fields[ id ].css_vars, type, preview, fields[ id ].type );

					// Handle hard-coded output_fields_trigger_change.
					if ( ! _.isUndefined( fields[ id ].output_fields_trigger_change ) ) {
						if ( ! alreadyTriggeredFields ) {
							alreadyTriggeredFields = [ id ];
						}
						_.each( fields[ id ].output_fields_trigger_change, function( triggerFieldID ) {
							if ( -1 === alreadyTriggeredFields.indexOf( triggerFieldID ) ) {
								alreadyTriggeredFields.push( triggerFieldID );
								self.updatePreview( {
									currentTarget: jQuery( '.fusion-builder-option[data-option-id="' + triggerFieldID + '"] input' )
								}, true, alreadyTriggeredFields );
							}
						} );
					}

					// Handle output-dependencies.
					avadaPanelIFrame.populateFieldOutputDependencies();
					if ( avadaPanelIFrame.fieldOutputDependencies[ id ] ) {
						if ( ! alreadyTriggeredFields ) {
							alreadyTriggeredFields = [ id ];
						}
						_.each( avadaPanelIFrame.fieldOutputDependencies[ id ], function( triggerFieldID ) {
							if ( -1 === alreadyTriggeredFields.indexOf( triggerFieldID ) ) {
								alreadyTriggeredFields.push( triggerFieldID );
								self.updatePreview( {
									currentTarget: jQuery( '.fusion-builder-option[data-option-id="' + triggerFieldID + '"] input' )
								}, true, alreadyTriggeredFields );
							}
						} );
					}
					updated = true;
				}
			}

			if ( ! _.isUndefined( fields[ id ].partial_refresh ) && ! _.isEmpty( fields[ id ].partial_refresh ) ) {

				// Apply any functions defined in js_callback.
				if ( false !== avadaPanelIFrame.applyRefreshCallbacks( fields[ id ].partial_refresh, value ) ) {

					// Partial refresh.
					avadaPanelIFrame.partialRefresh( saveId, fields[ id ].partial_refresh, value, this.model.get( 'cid' ) );
					updated = true;
				}
			}

			if ( ! _.isUndefined( fields[ id ].transport ) && 'postMessage' === fields[ id ].transport ) {
				updated = true;
				FusionEvents.trigger( 'fusion-postMessage-' + id );
				window.dispatchEvent( new Event( 'fusion-postMessage-' + id ) );
			}

			if ( ! updated || ( ! _.isUndefined( fields[ id ].transport ) && 'refresh' === fields[ id ].transport ) ) {

				if ( false !== avadaPanelIFrame.applyRefreshCallbacks( fields[ id ].full_refresh, value ) ) {

					// Full refresh.
					$option.addClass( 'full-refresh-active' );
					FusionApp.fullRefresh();
					FusionEvents.once( 'fusion-app-setup', function() {
						$option.removeClass( 'full-refresh-active' );
					} );
				}
			}
		},

		maybeUpdateSlug: function( value ) {
			var from,
				to,
				$input = this.$el.find( '#post_name' ),
				i,
				l;

			if ( ! $input.length ) {
				return;
			}

			if ( ! $input.val() || '' === $input.val() ) {
				this.hasSlug = false;
			}

			if ( ! value || '' === value || this.hasSlug ) {
				return;
			}

			value = value.replace( /^\s+|\s+$/g, '' ).toLowerCase(),
			from  = 'àáäâèéëêìíïîòóöôùúüûñç·/_,:;',
			to    = 'aaaaeeeeiiiioooouuuunc------';

			for ( i = 0, l = from.length; i < l; i++ ) {
				value = value.replace( new RegExp( from.charAt( i ), 'g' ), to.charAt( i ) );
			}
			value = value.replace( '.', '-' ).replace( /[^a-z0-9 -]/g, '' ).replace( /\s+/g, '-' ).replace( /-+/g, '-' );
			$input.val( value ).trigger( 'change' );
		},

		/**
		 * Clones a value.
		 * Used to avoid JS references.
		 *
		 * @param {mixed} value - A value. Can be anything.
		 * @return {mixed} - Returns the value.
		 */
		cloneValue: function( value ) {
			return value;
		},

		/**
		 * Update settings (TO) and trigger update.
		 *
		 * @since 2.0.0
		 * @param {string} id - The setting-ID.
		 * @param {mixed} value - The value.
		 * @param {boolean} skipRender - Whether we should skip render or not.
		 * @return {void}
		 */
		updateSettingsToParams: function( id, value, skipRender ) {
			var self         = this,
				initialValue = self.cloneValue( value ),
				$target,
				defaultText,
				args,
				type;

			skipRender = 'undefined' === typeof skipRender ? false : skipRender;

			if ( _.isUndefined( FusionApp.settingsToParams[ id ] ) ) {
				return;
			}
			_.each( FusionApp.settingsToParams[ id ], function( rule ) {

				if ( ! _.isUndefined( fusionAllElements[ rule.element ] ) ) {
					if ( rule.callback ) {

						args = {
							id: id,
							type: self.model.get( 'type' )
						};
						value = avadaPanelIFrame.applyCallback( initialValue, rule.callback, args );
					}

					// Update default for element render.
					fusionAllElements[ rule.element ].defaults[ rule.param ] = value;

					if ( ! _.isUndefined( fusionAllElements[ rule.element ].params[ rule.param ] ) && ! _.isUndefined( fusionAllElements[ rule.element ].params[ rule.param ][ 'default' ] ) ) {

						// Only option that uses visual default value should update.
						if ( 'colorpickeralpha' === fusionAllElements[ rule.element ].params[ rule.param ].type || 'color' === fusionAllElements[ rule.element ].params[ rule.param ].type || 'range' === fusionAllElements[ rule.element ].params[ rule.param ].type ) {
							fusionAllElements[ rule.element ].params[ rule.param ][ 'default' ] = value;
							$target = jQuery( '.' + rule.element + ' [data-option-id="' + rule.param + '"]' );

							if ( 1 === $target.length ) {
								FusionEvents.trigger( 'fusion-default-changed-' + $target.closest( '.fusion-builder-module-settings' ).attr( 'data-element-cid' ), rule.param, value );
							}
						}

						// Update the default text value if open.
						if ( jQuery( '.description [data-fusion-option="' + id + '"]' ).length ) {
							type        = jQuery( '.description [data-fusion-option="' + id + '"]' ).closest( '.fusion-builder-option' ).attr( 'class' ).split( ' ' ).pop();
							defaultText = FusionApp.sidebarView.fixToValueName( id, value, type );
							jQuery( '.description [data-fusion-option="' + id + '"]' ).html( defaultText );
						}
					}

					FusionEvents.trigger( 'fusion-param-default-update-' + rule.param, value );

					// Update default for color picker/range.
					if ( ! skipRender ) {

						// Make sure that element type re-renders.
						FusionEvents.trigger( 'fusion-global-update-' + rule.element, rule.param, value );
						self.triggerActiveStates();
					}
				}
			} );
		},

		/**
		 * Update builder element extras (TO) and trigger update.
		 *
		 * @since 2.0.0
		 * @param {string} id - The setting-ID.
		 * @param {mixed} value - The value.
		 * @param {boolean} skipRender - Whether we should skip render or not.
		 * @return {void}
		 */
		updateSettingsToExtras: function( id, value, skipRender ) {
			var self         = this,
				initialValue = self.cloneValue( value );

			if ( 'object' !== typeof FusionApp.settingsToExtras || _.isUndefined( FusionApp.settingsToExtras[ id ] ) ) {
				return;
			}

			skipRender = 'undefined' === typeof skipRender ? false : skipRender;

			_.each( FusionApp.settingsToExtras[ id ], function( rule ) {

				if ( ! _.isUndefined( fusionAllElements[ rule.element ] ) ) {
					if ( rule.callback ) {
						value = avadaPanelIFrame.applyCallback( initialValue, rule.callback, false );
					}

					// Update extra for element render.
					fusionAllElements[ rule.element ].extras[ rule.param ] = value;

					// Make sure that element type re-renders.
					if ( ! skipRender ) {
						FusionEvents.trigger( 'fusion-extra-update-' + rule.element, rule.param, value );
						self.triggerActiveStates();
					}
				}
			} );
		},

		/**
		 * Update settings (PO).
		 *
		 * @since 2.0.0
		 * @param {string} id - The setting-ID.
		 * @param {mixed}  value - The value.
		 * @return {void}
		 */
		updateSettingsToPo: function( id, value ) {
			var initialValue = this.cloneValue( value ),
				option;

			if ( _.isUndefined( FusionApp.settingsToPo[ id ] ) || _.isUndefined( FusionApp.data.fusionPageOptions ) || _.isEmpty( FusionApp.data.fusionPageOptions ) ) {
				return;
			}
			_.each( FusionApp.settingsToPo[ id ], function( rule ) {
				if ( ! _.isUndefined( FusionApp.data.fusionPageOptions[ rule.tab ] ) && ! _.isUndefined( FusionApp.data.fusionPageOptions[ rule.tab ].fields[ rule.option ] ) ) {

					option = FusionApp.data.fusionPageOptions[ rule.tab ].fields[ rule.option ];

					if ( rule.callback ) {
						value = avadaPanelIFrame.applyCallback( initialValue, rule.callback, false );
					}

					// Remove relevant tab.
					FusionApp.sidebarView.clearTabs( 'po', false, rule.option );

					// Only update option types which use a TO as default, not a hardcoded string.
					if ( 'color-alpha' === option.type || 'color' === option.type  || 'slider' === option.type || 'sortable' === option.type ) {
						option[ 'default' ] = value;
					}
				}
			} );
		},

		/**
		 * Handle validation calls for option changes.
		 *
		 * @since 2.0.0
		 * @param {Object} event - The jQuery event.
		 * @return {boolean} - If the vadidation succeeded or failed.
		 */
		validateOption: function( event ) {
			var $target   = jQuery( event.currentTarget ),
				value     = $target.val(),
				$optionEl = $target.parents( '.fusion-builder-option' ),
				id        = $optionEl.data( 'option-id' ),
				valid     = true,
				message   = '';

			if ( $target.hasClass( 'awb-ignore' ) ) {
				return;
			}

			if ( 'checkbox' === ( $target ).attr( 'type' ) ) {
				value = $target.is( ':checked' ) ? '1' : '0';
			}

			// Do not validate variable within value.
			if ( 'string' === typeof value && -1 !== value.indexOf( 'var(' ) ) {
				FusionApp.validate.message( 'remove', id, $target );
				return true;
			}
			if ( $optionEl.hasClass( 'spacing' ) || $optionEl.hasClass( 'dimension' ) ) {
				valid   = FusionApp.validate.cssValue( value );
				message = fusionBuilderTabL10n.invalidCssValue;
			} else if ( $optionEl.hasClass( 'color' ) ) {
				valid   = FusionApp.validate.validateColor( value, 'hex' );
				message = fusionBuilderTabL10n.invalidColor;
			} else if ( $optionEl.hasClass( 'color-alpha' ) ) {
				valid   = FusionApp.validate.validateColor( value );
				message = fusionBuilderTabL10n.invalidColor;
			} else if ( $optionEl.hasClass( 'typography' ) ) {
				if ( 'font-size' === $target.attr( 'name' ) ) {
					valid   = FusionApp.validate.cssValue( value );
					message = fusionBuilderTabL10n.invalidCssValueVar.replace( '%s', $target.attr( 'name' ) );
				} else if ( 'line-height' === $target.attr( 'name' ) || 'letter-spacing' === $target.attr( 'name' ) ) {
					valid = FusionApp.validate.cssValue( value, true );
					message = fusionBuilderTabL10n.invalidCssValueVar.replace( '%s', $target.attr( 'name' ) );
				}
			}

			if ( false === valid ) {
				FusionApp.validate.message( 'add', id, $target, message );
				return false;
			}
			FusionApp.validate.message( 'remove', id, $target );
			return true;
		},

		/**
		 * Show responsive options.
		 *
		 * @param {Object} event
		 */
		showResponsiveOptions: function( event ) {
			var $element = jQuery( event.currentTarget ).parent();

			$element.toggleClass( 'active-item' );
		},

		/**
		 * Change responsive option.
		 *
		 * @param {Object} event
		 */
		changeResponsiveOption: function( event ) {
			var $element   = jQuery( event.currentTarget );
			var $parent    = jQuery( event.currentTarget ).closest( 'li.active-item' );

			jQuery( '.fusion-builder-preview-' + $element.data( 'indicator' ) ).trigger( 'click' );
			$parent.removeClass( 'active-item' );

		},

		/**
		 * Hide responsive option.
		 *
		 * @param {Object} event
		 */
		hideResponsiveOptions: function( event ) {
			var $element   = jQuery( event.currentTarget );

			$element.find( '.fusion-panel-options li.active-item' ).removeClass( 'active-item' );
		},

		/**
		 * Check update_callback arguments and return true|false
		 * depending on the context of the preview pane.
		 *
		 * @param {Object} field
		 * @return {boolean} - If we should update or not.
		 */
		checkUpdateCallbacks: function( field ) {
			var result   = true,
				results  = [],
				subCheck = false;

			if ( field && field.id && field.update_callback ) {
				_.each( field.update_callback, function( updateCallback ) {
					var where;
					if ( updateCallback.operator ) {

						// 1st level chacks are AND.
						where = updateCallback.where ? FusionApp.data[ updateCallback.where ] : FusionApp.data;
						switch ( updateCallback.operator ) {
						case '===':
							if ( where[ updateCallback.condition ] !== updateCallback.value ) {
								results.push( false );
							}
							break;
						case '!==':
							if ( where[ updateCallback.condition ] === updateCallback.value ) {
								results.push( false );
							}
							break;
						}
					} else {

						// Nested checks function as OR conditions.
						_.each( updateCallback, function( subCallback ) {
							if ( subCallback.operator ) {
								where = subCallback.where ? FusionApp.data[ subCallback.where ] : FusionApp.data;
								switch ( subCallback.operator ) {
								case '===':
									if ( where[ subCallback.condition ] === subCallback.value ) {
										subCheck = true;
									}
									break;
								case '!==':
									if ( where[ subCallback.condition ] !== subCallback.value ) {
										subCheck = true;
									}
									break;
								}
							}
						} );
						results.push( subCheck );
					}
				} );
			}
			_.each( results, function( subResult ) {
				if ( ! subResult ) {
					result = false;
				}
			} );
			return result;
		}

	} );

	// Options
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionTypographyField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionTypographySetsField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionCodeBlock );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionColorPicker );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionDimensionField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionOptionUpload );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.radioButtonSet );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionCheckboxButtonSet );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionRangeField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionRepeaterField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionSelectField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionAjaxSelect );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionMultiSelect );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionSwitchField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionImportUpload );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionExport );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionSortable );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionColorPalette );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionRawField );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionLinkSelector );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionHubSpotMap );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionMailchimpMap );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionIconPicker );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionLayoutConditions );
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.options.fusionToggleField );

	// Active states.
	_.extend( FusionPageBuilder.TabView.prototype, FusionPageBuilder.fusionActiveStates );

}( jQuery ) );
;/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	_.extend( FusionPageBuilder.Callback.prototype, {

		fusion_get_alpha: function( value ) {
			var color = jQuery.AWB_Color( value );
			return color.alpha();
		},

		createSocialNetworks: function() {
			var socialMedia = [];

			if ( '0' != FusionApp.settings.sharing_facebook ) {
				socialMedia.push( 'facebook' );
			}
			if ( '0' != FusionApp.settings.sharing_twitter ) {
				socialMedia.push( 'twitter' );
			}
			if ( '0' != FusionApp.settings.sharing_linkedin ) {
				socialMedia.push( 'linkedin' );
			}
			if ( '0' != FusionApp.settings.sharing_reddit ) {
				socialMedia.push( 'reddit' );
			}
			if ( '0' != FusionApp.settings.sharing_whatsapp ) {
				socialMedia.push( 'whatsapp' );
			}
			if ( '0' != FusionApp.settings.sharing_telegram ) {
				socialMedia.push( 'telegram' );
			}
			if ( '0' != FusionApp.settings.sharing_tumblr ) {
				socialMedia.push( 'tumblr' );
			}
			if ( '0' != FusionApp.settings.sharing_pinterest ) {
				socialMedia.push( 'pinterest' );
			}
			if ( '0' != FusionApp.settings.sharing_vk ) {
				socialMedia.push( 'vk' );
			}
			if ( '0' != FusionApp.settings.sharing_email ) {
				socialMedia.push( 'mail' );
			}
			return socialMedia.join( '|' );
		},

		toYes: function( value ) {
			return 1 == value || true === value ? 'yes' : 'no';
		},

		toLowerCase: function( value ) {
			return value.toLowerCase();
		},

		urlFromObject: function( value ) {
			if ( 'object' === typeof value && 'undefined' !== typeof value.url ) {
				return value.url;
			}
			return '';
		},

		portfolioPaginationFormat: function( value ) {
			return value.toLowerCase().replace( / /g, '' ).replace( /\_/g, '-' ).replace( 'scroll', '' ).replace( /-\s*$/, '' ); // eslint-disable-line no-useless-escape
		},

		/**
		 * Checks if there are portfolio grid or carousels in preview frame.
		 *
		 * @return {boolean} - Return whether the page has portfolios or not.
		 */
		noPortfolioOnPage: function() {
			if ( 0 < jQuery( '#fb-preview' ).contents().find( '.fusion-portfolio-layout-grid, .fusion-portfolio-carousel' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Checks if there is pagination on the page.
		 *
		 * @return {boolean} - Return whther the page has pagination or not.
		 */
		isPaginationOnPage: function() {
			if ( 0 === jQuery( '#fb-preview' ).contents().find( '.pagination' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Checks if there is rollover on the page.
		 *
		 * @return {boolean} - Return whether the page has tollover or not.
		 */
		isRolloverOnPage: function() {
			if ( 0 === jQuery( '#fb-preview' ).contents().find( '.fusion-image-wrapper' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Checks if there is masonry on the page.
		 *
		 * @return {boolean} - Return whether the page has masonry or not.
		 */
		isMasonryOnPage: function() {
			if ( 0 === jQuery( '#fb-preview' ).contents().find( '.fusion-blog-layout-masonry, .fusion-portfolio-masonry, .fusion-gallery-layout-masonry' ).length ) {
				return false;
			}
			return true;
		},

		/**
		 * Updates grid separators.
		 *
		 * @param {string} value - The value (using "|" as separator for multiple elements).
		 * @return {boolean} - Always returns true.
		 */
		updateGridSeps: function( value ) {
			var sepClasses = '',
				$sepElems  = jQuery( '#fb-preview' ).contents().find( 'div.fusion-content-sep' );

			_.each( value.split( '|' ), function( sepClass ) {
				sepClasses += ' sep-' + sepClass;
			} );

			$sepElems.removeClass( 'sep-single sep-solid sep-double sep-dashed sep-dotted sep-shadow' );
			$sepElems.addClass( sepClasses );

			return true;
		},

		/**
		 * Checks if there is twitter widget or blog masonry on the page.
		 *
		 * @return {boolean} - Return whether there's a twitter widget or blogmasonry on the page.
		 */
		timeLineColorCallback: function() {
			if ( 0 < jQuery( '#fb-preview' ).contents().find( '.fusion-blog-layout-masonry, .twitter-timeline-rendered' ).length  ) {
				return false;
			}
			return true;
		},

		fusionEditGlobalSidebar: function( $trigger ) {
			var option = 'pages_sidebar';
			if ( FusionApp.data.is_singular_post ) {
				option = 'posts_sidebar';
			} else if ( FusionApp.data.is_portfolio_single ) {
				option = 'portfolio_sidebar';
			} else if ( FusionApp.data.is_portfolio_archive ) {
				option = 'portfolio_archive_sidebar';
			} else if ( FusionApp.data.is_search ) {
				option = 'search_sidebar';
			} else if ( FusionApp.data.is_product ) {
				option = 'woo_sidebar';
			} else if ( FusionApp.data.is_woo_archive ) {
				option = 'woocommerce_archive_sidebar';
			} else if ( FusionApp.data.is_singular_ec ) {
				option = 'ec_sidebar';
			} else if ( FusionApp.data.is_bbpress || FusionApp.data.is_buddypress ) {
				option = 'ppbress_sidebar';
			} else if ( FusionApp.data.is_home || ( FusionApp.data.is_archive && ! FusionApp.data.is_search ) ) {
				option = 'blog_archive_sidebar';
			}
			if ( -1 < $trigger.data( 'fusion-option' ).indexOf( '_2' ) ) {
				option += '_2';
			}
			FusionApp.sidebarView.openOption( option, 'to', $trigger.data( 'fusion-option-open-parent' ) );
		},

		fusionEditFeaturedImage: function( $trigger ) {
			FusionApp.sidebarView.openOption( '_thumbnail_id', 'po', $trigger.data( 'fusion-option-open-parent' ) );
		}
	} );
}( jQuery ) );
