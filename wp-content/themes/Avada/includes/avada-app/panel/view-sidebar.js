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
