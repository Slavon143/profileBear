/* global FusionApp, FusionEvents, FusionPageBuilderApp, fusionGlobalManager, fusionBuilderText, fusionAllElements, FusionPageBuilderViewManager, fusionMultiElements, FusionPageBuilderElements */
/* eslint no-unused-vars: 0 */
/* eslint guard-for-in: 0 */
/* eslint no-undef: 0 */
/* eslint no-empty-function: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.ParentElementView = FusionPageBuilder.BaseView.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-element-parent-template' ).html() ),

			className: 'fusion-builder-live-element fusion-builder-data-cid',

			events: {
				'click .fusion-builder-remove': 'removeElement',
				'click .fusion-builder-clone': 'cloneElement',
				'click .fusion-builder-settings': 'settings',
				'click .fusion-builder-add-child': 'addChildElement',
				'click .fusion-builder-element-save': 'openLibrary',
				'click a': 'disableLink',
				'click .fusion-builder-element-drag': 'preventDefault'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {

				this.model.children = new FusionPageBuilder.Collection();

				this.listenTo( this.model.children, 'add', this.addChildView );
				this.emptyPlaceholderText = 'undefined' !== typeof fusionAllElements[ this.model.get( 'element_type' ) ] ? fusionBuilderText.empty_parent.replace( '%s', fusionAllElements[ this.model.get( 'element_type' ) ].name ) : '';

				// If triggering a view update.
				this.listenTo( FusionEvents, 'fusion-view-update', this.reRender );
				this.listenTo( FusionEvents, 'fusion-view-update-' + this.model.get( 'cid' ), this.reRender );
				this.listenTo( FusionEvents, 'fusion-view-update-' + this.model.get( 'element_type' ), this.reRender );

				this._triggerCallback = _.debounce( _.bind( this.triggerCallback, this ), 200 );

				// If there is a template.
				if ( jQuery( '#tmpl-' + this.model.attributes.element_type + '-shortcode' ).length ) {
					this.model.set( 'noTemplate', false );
					this.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-' + this.model.attributes.element_type + '-shortcode' ).html() );
				} else {
					this.model.set( 'noTemplate', true );
					this.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_shortcode-shortcode' ).html() );
				}

				this.elementIsCloning = false;
				this.mouseDown = false;

				this.fetchIds = [];

				this.childIds = [];

				this.updateGallery = false;

				this.model.set( 'editLabel', this.getEditLabel() );

				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
				this.$el.attr( 'data-type', this.model.get( 'element_type' ) );

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					this.$el.attr( 'fusion-global-layout', this.model.attributes.params.fusion_global );
					this.$el.removeClass( 'fusion-global-parent-element' ).addClass( 'fusion-global-parent-element' );
				}

				this.baseInit();

				// JQuery trigger.
				this.renderedYet = FusionPageBuilderApp.reRenderElements;
				this._refreshJs  = _.debounce( _.bind( this.refreshJs, this ), 300 );

				this.model.set( 'sortable', 'undefined' === typeof fusionAllElements[ this.model.get( 'element_type' ) ].sortable ? true : fusionAllElements[ this.model.get( 'element_type' ) ].sortable );

				this.onInit();
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function( event ) {
				var self = this;

				this.$el.html( this.template( this.model.attributes ) );

				this.renderContent();

				// If from ajax, do not regenerate children.
				if ( 'string' !== typeof event && 'ajax' !== event ) {
					this.generateChildElements();
				}

				// If no template, no need for sortable children call.
				if ( ! this.model.get( 'noTemplate' ) ) {
					setTimeout( function() {
						self.sortableChildren();
					}, 100 );
				}

				// Don't refresh on first render.
				if ( this.renderedYet ) {
					this._refreshJs();
				}

				this.onRender();

				this.renderedYet = true;

				setTimeout( function() {
					self.droppableElement();
				}, 100 );

				return this;
			},

			/**
			 * Make children sortable.
			 * This is executed from the render() function.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			sortableChildren: function() {
				var self       = this,
					$container = self.$el.find( '.fusion-child-element' );

				if ( false === this.model.get( 'sortable' ) ) {
					return;
				}

				$container.on( 'mousedown', function( event ) {
					if ( ! jQuery( event.originalEvent.target ).hasClass( 'fusion-live-editable' ) ) {
						self.mouseDown = true;
					}
				} );

				$container.on( 'mousemove', function() {
					if ( self.mouseDown ) {
						$container.css( { overflow: 'auto' } );
					}
				} );

				$container.on( 'mouseup', function() {
					self.mouseDown = false;
					$container.css( { overflow: '' } );
				} );

				this.$el.find( '.fusion-builder-element-content' ).sortable( {

					items: '.fusion-builder-live-child-element',
					tolerance: 'pointer',
					appendTo: $container,
					containment: $container,
					cursor: 'grabbing',
					cancel: '.fusion-live-editable',
					zIndex: 99999999,
					helper: 'clone',
					scroll: false,
					revert: 100,
					start: function() {
						FusionPageBuilderApp.$el.addClass( 'fusion-builder-dragging' );
						$container.addClass( 'fusion-parent-sortable' );
					},
					update: function( event, ui ) {
						var MultiGlobalArgs,
							elementView = FusionPageBuilderViewManager.getView( ui.item.data( 'cid' ) ),
							newIndex    = ui.item.parent().children( '.fusion-builder-live-child-element' ).index( ui.item );

						self.updateElementContent();

						// Update collection
						FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, self.model.get( 'cid' ) );

						// Save history state
						FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.moved + ' ' + fusionAllElements[ elementView.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );

						// Handle multiple global elements.
						MultiGlobalArgs = {
							currentModel: self.model,
							handleType: 'save',
							attributes: self.model.attributes
						};
						fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

						FusionEvents.trigger( 'fusion-content-changed' );
					},
					stop: function() {
						self.mouseDown = false;
						$container.css( { overflow: '' } );
						$container.removeClass( 'fusion-parent-sortable' );
						FusionPageBuilderApp.$el.removeClass( 'fusion-builder-dragging' );
					}
				} );
			},

			/**
			 * Updates the element contents.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			updateElementContent: function() {
				var content = '',
					children;

				if ( this.model.get( 'noTemplate' ) ) {
					children = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );
					_.each( children, function( child ) {
						content += child.getContent();
					} );
				} else {
					this.$el.find( '.fusion-builder-live-child-element' ).each( function() {
						var $thisEl = jQuery( this );
						content += FusionPageBuilderApp.generateElementShortcode( $thisEl, false );
					} );
				}

				this.model.attributes.params.element_content = content;
			},

			/**
			 * Get template attributes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplateAtts: function() {
				var templateAttributes = jQuery.extend( true, {}, this.model.attributes ),
					params  = jQuery.extend( true, {}, this.model.get( 'params' ) ),
					values  = {},
					extras  = {},
					element = fusionAllElements[ this.model.get( 'element_type' ) ];

				if ( 'undefined' !== typeof this.elementTemplate ) {

					// Get element values.
					if ( element && 'undefined' !== typeof element.defaults ) {
						values = jQuery.extend( true, {}, element.defaults, _.fusionCleanParameters( params ) );

						// Get element extras.
						if ( 'undefined' !== typeof element.extras ) {
							extras = jQuery.extend( true, {}, element.extras );
						}
					}

					templateAttributes.values    = values;
					templateAttributes.extras    = extras;
					templateAttributes.thisModel = this.model;

					templateAttributes = this.getDynamicAtts( templateAttributes );
					templateAttributes = this.filterTemplateAtts( templateAttributes );

					return templateAttributes;
				}
			},

			/**
			 * Renders the content.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			renderContent: function() {
				if ( 'undefined' !== typeof this.elementTemplate ) {
					this.$el.find( '.fusion-builder-element-content' ).html( this.getTemplate() );
				}
			},

			/**
			 * Removes children.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeChildren: function( event ) {

				var children = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				if ( event ) {
					event.preventDefault();
				}

				_.each( children, function( child ) {
					child.removeElement( '', 'Automated' );
				} );
			},

			/**
			 * Removes an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @param {bool} forceManually - Force manually, even if it's not an event, to update history and trigger content changes.
			 * @return {void}
			 */
			removeElement: function( event, isAutomated, forceManually ) {
				var parentCid   = this.model.get( 'parent' ),
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parentCid; // jshint ignore: line
					} ),
					MultiGlobalArgs,
					colView;

				if ( event || forceManually ) {
					if ( event ) {
						event.preventDefault();
					}

					colView = FusionPageBuilderViewManager.getView( parentCid );
					colView.$el.find( '.fusion-builder-module-controls-container a' ).trigger( 'mouseleave' );
				}

				// Hook to allow custom actions.
				this.beforeRemove();

				// Remove children elements
				this.removeChildren();

				// Remove element view
				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				// Destroy element model
				this.model.destroy();

				this.remove();

				// If element is removed manually
				if ( event || forceManually ) {
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );
				}

				if ( parentModel.children.length && 'undefined' === typeof isAutomated ) {

					// Handle multiple global elements.
					MultiGlobalArgs = {
						currentModel: parentModel.children.models[ 0 ],
						handleType: 'save',
						attributes: parentModel.children.models[ 0 ].attributes
					};
					fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );
				}
			},

			/**
			 * Clones an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @param {bool} forceManually - Force manually, even if it's not an event, to update history and trigger content changes.
			 * @return {void}
			 */
			cloneElement: function( event, forceManually ) {
				var elementAttributes,
					currentModel,
					MultiGlobalArgs;

				if ( event ) {
					event.preventDefault();
				}

				if ( true === this.elementIsCloning ) {
					return;
				}
				this.elementIsCloning = true;

				elementAttributes = jQuery.extend( true, {}, this.model.attributes );
				elementAttributes.created = 'manually';
				elementAttributes.cid = FusionPageBuilderViewManager.generateCid();
				elementAttributes.targetElement = this.$el;
				elementAttributes.at_index = FusionPageBuilderApp.getCollectionIndex( this.$el );

				if ( 'undefined' !== elementAttributes.from ) {
					delete elementAttributes.from;
				}

				currentModel = FusionPageBuilderApp.collection.add( elementAttributes );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.cloned + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );

				this.elementIsCloning = false;

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: currentModel,
					handleType: 'save',
					attributes: currentModel.attributes
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				if ( event || forceManually ) {
					FusionEvents.trigger( 'fusion-content-changed' );
				}
			},

			noTemplateAjaxUpdate: function() {
				this.updateElementContent();
				FusionApp.callback.fusion_do_shortcode( this.model.get( 'cid' ), this.getContent() );
			},

			/**
			 * Generates the child elements.
			 *
			 * @since 2.0.0
			 * @param {boolean|undefined} fixSettingsLvl - Whether we want to fix the settings levels or not.
			 *                                          Use true (bool) for yes. undefined has the same effect as false.
			 * @return {void}
			 */
			generateChildElements: function( fixSettingsLvl ) {
				var thisEl        = this,
					parentAtts    = this.model.get( 'params' ),
					content       = this.model.attributes.params.element_content,
					shortcodeTags = jQuery.map( fusionMultiElements, function( val, i ) { // jshint ignore: line
						return val;
					} ).join( '|' ),
					regExp      = window.wp.shortcode.regexp( shortcodeTags ),
					innerRegExp = FusionPageBuilderApp.regExpShortcode( shortcodeTags ),
					last        = false,
					matches     = 'undefined' !== typeof content ? content.match( regExp ) : false,
					modules     = {};

				// Make sure we don't just keep adding.
				this.removeChildren();
				thisEl.model.children.reset( null );

				if ( ! content ) {
					return;
				}

				_.each( matches, function( shortcode, index ) {
					var shortcodeElement     = shortcode.match( innerRegExp ),
						shortcodeName        = shortcodeElement[ 2 ],
						shortcodeAttributes  = '' !== shortcodeElement[ 3 ] ? window.wp.shortcode.attrs( shortcodeElement[ 3 ] ) : '',
						shortcodeContent     = shortcodeElement[ 5 ],
						moduleCID            = FusionPageBuilderViewManager.generateCid(), // jshint ignore: line
						prefixedAttributes   = { params: ( {} ) },
						tagName              = 'div',

						// Check if shortcode allows generator
						allowGenerator = 'undefined' !== typeof fusionAllElements[ shortcodeName ].allow_generator ? fusionAllElements[ shortcodeName ].allow_generator : '',
						moduleSettings,
						key,
						prefixedKey,
						dependencyOption,
						dependencyOptionValue,
						moduleContent,
						markupContent;

					if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ].tag_name ) {
						tagName = fusionAllElements[ shortcodeName ].tag_name;
					}

					// If last child.
					last = index + 1 === matches.length;

					moduleSettings = {
						type: 'element',
						element_type: shortcodeName,
						cid: FusionPageBuilderViewManager.generateCid(),
						view: thisEl,
						created: 'auto',
						multi: 'multi_element_child',
						child_element: 'true',
						allow_generator: allowGenerator,
						inline_editor: FusionPageBuilderApp.inlineEditorHelpers.inlineEditorAllowed( shortcodeName ),
						params: {},
						parent: thisEl.model.get( 'cid' ),
						tag_name: tagName,
						last: last
					};

					// Get markup from map if set.  Add further checks here so only necessary elements do this check.
					if ( -1 === shortcodeName.indexOf( 'fusion_builder_' ) ) {
						markupContent = FusionPageBuilderApp.extraShortcodes.byShortcode( shortcodeElement[ 0 ] );
						if ( 'undefined' !== typeof markupContent ) {
							moduleSettings.markup = markupContent;
						} else {
							moduleSettings.shortcode = shortcodeElement[ 0 ];
						}
					}

					if ( _.isObject( shortcodeAttributes.named ) ) {

						for ( key in shortcodeAttributes.named ) {

							prefixedKey = key;

							prefixedAttributes.params[ prefixedKey ] = shortcodeAttributes.named[ key ];
						}

						moduleSettings = _.extend( moduleSettings, prefixedAttributes );
					}

					// TODO: check if needed.  Commented out for FB item 420.
					// if ( ! shortcodesInContent ) {
					moduleSettings.params.element_content = shortcodeContent;

					// }.

					// Checks if map has set selectors. If so needs to be set prior to render.
					if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ].selectors ) {
						moduleSettings.selectors = jQuery.extend( true, {}, fusionAllElements[ shortcodeName ].selectors );
					}

					// Set module settings for modules with dependency options
					if ( 'undefined' !== typeof fusionAllElements[ shortcodeName ].option_dependency ) {

						dependencyOption      = fusionAllElements[ shortcodeName ].option_dependency;
						dependencyOptionValue = prefixedAttributes.params[ dependencyOption ];
						moduleContent         = prefixedAttributes.params.element_content;
						prefixedAttributes.params[ dependencyOptionValue ] = moduleContent;

					}

					// Fix for deprecated 'settings_lvl' attribute
					if ( true === fixSettingsLvl ) {
						if ( 'fusion_content_box' === moduleType ) { // jshint ignore: line

							// Reset values that are inherited from parent
							moduleSettings.params.iconcolor              = '';
							moduleSettings.params.backgroundcolor        = '';
							moduleSettings.params.circlecolor            = '';
							moduleSettings.params.circlebordercolor      = '';
							moduleSettings.params.circlebordersize       = '';
							moduleSettings.params.outercirclebordercolor = '';
							moduleSettings.params.outercirclebordersize  = '';

							// Set values from parent element
							moduleSettings.params.animation_type      = parentAtts.animation_type;
							moduleSettings.params.animation_direction = parentAtts.animation_direction;
							moduleSettings.params.animation_speed     = parentAtts.animation_speed;
							moduleSettings.params.link_target         = parentAtts.link_target;
						}
					}

					modules[ moduleSettings.cid ] = moduleSettings;

				} );

				this.onGenerateChildElements( modules );

				// Add child elements to children collection.
				_.each( modules, function( moduleSettings ) {
					thisEl.model.children.add( [ moduleSettings ] );
				} );
			},

			/**
			 * Extendable function for when child elements get generated.
			 *
			 * @since 2.0.0
			 * @param {Object} modules An object of modules that are not a view yet.
			 * @return {void}
			 */
			onGenerateChildElements: function( modules ) {
			},

			/**
			 * Adds a child element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			addChildElement: function( event, predefinedParams ) {

				var params = {},
					defaultParams,
					value,
					moduleSettings,
					allowGenerator,
					childElement,
					newModel,
					MultiGlobalArgs,
					tagName = 'div';

				if ( event ) {
					event.preventDefault();
				}

				childElement = fusionMultiElements[ this.model.get( 'element_type' ) ];

				defaultParams = predefinedParams ? predefinedParams : fusionAllElements[ childElement ].params;

				allowGenerator = ( 'undefined' !== typeof fusionAllElements[ childElement ].allow_generator ) ? fusionAllElements[ childElement ].allow_generator : '';

				// Process default parameters from shortcode
				_.each( defaultParams, function( param )  {
					value = ( _.isObject( param.value ) ) ? param[ 'default' ] : param.value;
					params[ param.param_name ] = value;
				} );

				if ( 'undefined' !== typeof fusionAllElements[ childElement ].tag_name ) {
					tagName = fusionAllElements[ childElement ].tag_name;
				}

				moduleSettings = {
					type: 'element',
					element_type: childElement,
					cid: FusionPageBuilderViewManager.generateCid(),
					view: this,
					created: 'manually',
					multi: 'multi_element_child',
					child_element: 'true',
					params: params,
					allow_generator: allowGenerator,
					inline_editor: FusionPageBuilderApp.inlineEditorHelpers.inlineEditorAllowed( childElement ),
					parent: this.model.get( 'cid' ),
					tag_name: tagName,
					last: true
				};

				// Checks if map has set selectors. If so needs to be set prior to render.
				if ( 'undefined' !== typeof fusionAllElements[ childElement ].selectors ) {
					moduleSettings.selectors = jQuery.extend( true, {}, fusionAllElements[ childElement ].selectors );
				}

				if ( 'undefined' !== typeof event && null !== event && jQuery( event.currentTarget ).closest( '.fusion-builder-live-child-element' ).length ) {
					moduleSettings.targetElement = jQuery( event.currentTarget ).closest( '.fusion-builder-live-child-element' );
				}

				newModel = this.model.children.add( [ moduleSettings ] );

				if ( this.model.get( 'noTemplate' ) ) {
					this.noTemplateAjaxUpdate();
				}

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.added + ' ' + fusionAllElements[ childElement ].name + ' ' + fusionBuilderText.element );

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: newModel[ 0 ],
					handleType: 'changeOption'
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );
				FusionEvents.trigger( 'fusion-content-changed' );

				this.childViewAdded();
			},

			afterPatch: function() {
				var self = this;

				this.generateChildElements();

				setTimeout( function() {
					self.droppableElement();
				}, 100 );
			},

			/**
			 * Adds a child view.
			 *
			 * @since 2.0.0
			 * @param {Object} child - The child element's model.
			 * @return {void}
			 */
			addChildView: function( child ) {
				var view,
					viewSettings = {
						model: child,
						collection: FusionPageBuilderElements
					};

				if ( 'undefined' !== typeof FusionPageBuilder[ child.get( 'element_type' ) ] ) {
					view = new FusionPageBuilder[ child.get( 'element_type' ) ]( viewSettings );
				} else {
					view = new FusionPageBuilder.ChildElementView( viewSettings );
				}

				FusionPageBuilderViewManager.addView( child.get( 'cid' ), view );

				if ( 'undefined' !== typeof child.get( 'targetElement' ) ) {
					if ( 'undefined' === typeof child.get( 'targetElementPosition' ) || 'after' === child.get( 'targetElementPosition' ) ) {
						child.get( 'targetElement' ).after( view.render().el );
					} else {
						child.get( 'targetElement' ).before( view.render().el );
					}
				} else if ( 'undefined' === typeof child.get( 'targetElementPosition' ) || 'end' === child.get( 'targetElementPosition' ) ) {
					this.$el.find( '.fusion-child-element' ).append( view.render().el );
				} else {
					this.$el.find( '.fusion-child-element' ).prepend( view.render().el );
				}

				// Check for extra contents and append to correct location.
				this.appendContents( view );

				this.updateElementContent();
			},

			/**
			 * Fired when child view is added.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			childViewAdded: function() {
			},

			/**
			 * Fired when child view is removed.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			childViewRemoved: function() {
			},

			/**
			 * Fired when child view is cloned.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			childViewCloned: function() {
			},

			/**
			 * Appends content to the view.
			 *
			 * @since 2.0.0
			 * @param {Object} view - The view.
			 * @return {void}
			 */
			appendContents: function( view ) {
				var self        = this,
					extraAppend = view.model.get( 'extraAppend' ),
					contents,
					selector,
					existing;

				if ( 'undefined' !== typeof extraAppend ) {
					contents = extraAppend.contents;
					selector = extraAppend.selector;
					existing = extraAppend.existing;
					if ( 'object' === typeof extraAppend.existing ) {
						_.each( extraAppend.existing, function( old, index ) {
							self.$el.find( selector ).remove( old );
							self.$el.find( selector ).append( contents[ index ] );
						} );
						return;
					}
					this.$el.find( selector ).remove( existing );
					this.$el.find( selector ).append( contents );
				}
			},

			/**
			 * Delegates multiple child elements.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			delegateChildEvents: function() {
				var cid,
					view;

				this.model.children.each( function( child ) {
					cid = child.attributes.cid;
					view = FusionPageBuilderViewManager.getView( cid );

					view.delegateEvents();
				} );
			},

			/**
			 * Sets the content and re-renders.
			 *
			 * @since 2.0.0
			 * @param {string} content - The content.
			 * @return {void}
			 */
			setContent: function( content ) {
				this.model.attributes.params.element_content = content;
				this.reRender();
			},

			/**
			 * Gets the content.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getContent: function() {
				return FusionPageBuilderApp.generateElementShortcode( this.$el, false );
			},

			/**
			 * Append children.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			appendChildren: function( target ) {
				var self = this,
					cid,
					view;

				this.model.children.each( function( child ) {

					cid  = child.attributes.cid;
					view = FusionPageBuilderViewManager.getView( cid );

					self.$el.find( target ).append( view.$el );

					view.reRender();
				} );

				this.delegateChildEvents();
			}

		} );
	} );
}( jQuery ) );
