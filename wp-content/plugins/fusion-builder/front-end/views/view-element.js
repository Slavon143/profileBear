/* global FusionApp, FusionPageBuilderApp, FusionEvents, fusionAllElements, FusionPageBuilderViewManager, fusionGlobalManager, fusionBuilderText, FusionPageBuilderElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.ElementView = FusionPageBuilder.BaseView.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-element-template' ).html() ),

			className: 'fusion-builder-live-element fusion-builder-data-cid',

			events: {
				'click .fusion-builder-remove': 'removeElement',
				'click .fusion-builder-clone': 'cloneElement',
				'click .fusion-builder-settings': 'settings',
				'click .fusion-builder-container-save': 'openLibrary',
				'click .fusion-builder-element-save': 'openLibrary',
				'click .fusion-builder-element-content a:not(.fusion-lightbox):not(.rs_error_message_button)': 'disableLink',
				'click .fusion-builder-element-drag': 'preventDefault',
				'click .fusion-tb-source': 'openDynamicSourcePO'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				var elementType,
					inlineElements = [ 'fusion_button', 'fusion_fontawesome', 'fusion_imageframe', 'fusion_text' ];

				this.model.inlineCollection = new FusionPageBuilder.Collection();

				elementType = this.model.get( 'element_type' );

				this.renderedYet = FusionPageBuilderApp.reRenderElements;

				// If triggering a view update.
				this.listenTo( FusionEvents, 'fusion-view-update', this.reRender );
				this.listenTo( FusionEvents, 'fusion-view-update-' + this.model.get( 'cid' ), this.reRender );

				// If there is a template.
				if ( jQuery( '#tmpl-' + this.model.attributes.element_type + '-shortcode' ).length ) {
					this.model.set( 'noTemplate', false );
					this.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-' + this.model.attributes.element_type + '-shortcode' ).html() );
				} else {
					this.model.set( 'noTemplate', true );
					this.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_shortcode-shortcode' ).html() );
				}

				this.model.set( 'editLabel', this.getEditLabel() );
				this.elementIsCloning = false;

				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
				this.$el.attr( 'data-type', elementType );

				if ( 'undefined' !== typeof fusionAllElements[ elementType ].components_per_template && 1 === fusionAllElements[ elementType ].components_per_template ) {
					this.$el.attr( 'data-cloning-disabled', 1 );
				}

				if ( -1 !== jQuery.inArray( elementType, inlineElements ) ) {
					this.$el.addClass( 'fusion-builder-live-element-inline' );
				}

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					this.$el.attr( 'fusion-global-layout', this.model.attributes.params.fusion_global );
					this.$el.removeClass( 'fusion-global-element' ).addClass( 'fusion-global-element' );
				}

				// JQuery trigger.
				this._refreshJs       = _.debounce( _.bind( this.refreshJs, this ), 300 );

				// Make sure the ajax callbacks are not repeated.
				this._triggerCallback = _.debounce( _.bind( this.triggerCallback, this ), 200 );

				this._updateResponsiveTypography = _.debounce( _.bind( this.updateResponsiveTypography, this ), 200 );

				// Undo/redo functionality.

				this._triggerColumn = _.debounce( _.bind( this.triggerColumn, this ), 300 );

				// Check if query_data is not set and element has callback.
				this.needsQuery();

				this.baseInit();

				this.onInit();

				// If inlne editing with overrides.
				this.activeInlineEditing = false;
				this.autoSelectEditor    = false;
				this.model.set( 'inlineEditors', [] );
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var self = this;

				FusionPageBuilderApp.disableDocumentWrite();
				this.beforeRender();

				this.$el.html( this.template( this.model.attributes ) );

				this.renderContent();

				if ( this.renderedYet ) {
					this._refreshJs();

					// Update column trigger.
					this.triggerColumn();

					setTimeout( function() {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-typography-reset', self.model.get( 'cid' ) );
						if ( 800 > jQuery( '#fb-preview' ).width() ) {
							self._updateResponsiveTypography();
						}
					}, 100 );
				}

				this.onRender();

				this.needsGoogle();

				this.renderedYet = true;

				FusionPageBuilderApp.enableDocumentWrite();

				setTimeout( function() {
					self.droppableElement();

					if ( ! self.activeInlineEditing ) {
						FusionPageBuilderApp.inlineEditorHelpers.liveEditorEvent( self );
						self.activeInlineEditing = false;
					}
					if ( FusionPageBuilderApp.inlineEditorHelpers.inlineEditorAllowed( self.model.get( 'element_type' ) ) ) {
						self.renderInlineSettings();
					}
				}, 100 );

				return this;
			},

			/**
			 * Re-Renders the view.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the rerender.
			 * @param {string} param - Param being changed if any.
			 * @return {void}
			 */
			reRender: function( event ) {
				var self    = this,
					element = fusionAllElements[ this.model.get( 'element_type' ) ];

				if ( event && 'object' === typeof event ) {
					event.preventDefault();
				}

				// If element has query callback and no data yet, then fire.
				if ( 'undefined' !== typeof element.callback && 'undefined' === typeof this.model.get( 'query_data' ) ) {
					this.triggerQuery( element.callback );
					return;
				}

				// Neither of above, then just patchView.
				this.patchView( event );

				setTimeout( function() {
					self.droppableElement();

					if ( ! self.activeInlineEditing ) {
						FusionPageBuilderApp.inlineEditorHelpers.liveEditorEvent( self );
					}
					self.activeInlineEditing = false;
				}, 100 );
			},

			/**
			 * Determines if the element is part of a flex column or not.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			flexDisplay: function() {
				var container   = FusionPageBuilderApp.getParentContainer( this ),
					column      = FusionPageBuilderApp.getParentColumn( this ),
					params      = {},
					columnBlock = false;

				if ( column ) {
					params      = column.model.get( 'params' );
					columnBlock = 'undefined' !== typeof params.content_layout && 'block' === params.content_layout;
				}

				return container && container.isFlex() && ! columnBlock;
			},

			/**
			 * Triggers extra query when needed.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			needsQuery: function() {
				var element = fusionAllElements[ this.model.get( 'element_type' ) ],
					callbackFunction;

				// Check for callback set.
				if ( 'undefined' !== typeof element.callback && 'undefined' === typeof this.model.get( 'query_data' ) && 'undefined' === typeof this.model.get( 'markup' ) ) {

					callbackFunction = element.callback;
					this.triggerQuery( callbackFunction );
				}

				// Check for element without template and set shortcode for render function.
				if ( this.model.get( 'noTemplate' ) && 'undefined' === typeof this.model.get( 'markup' ) ) {
					this.model.set( 'shortcode', FusionPageBuilderApp.generateElementShortcode( this.$el ) );
				}
			},

			triggerQuery: function( callbackFunction ) {
				callbackFunction.args   = 'undefined' === typeof callbackFunction.args ? '' : callbackFunction.args;
				callbackFunction.ajax   = 'undefined' === typeof callbackFunction.ajax ? false : callbackFunction.ajax;
				callbackFunction.action = 'undefined' === typeof callbackFunction.action ? false : callbackFunction.action;
				callbackFunction.cid    = this.model.get( 'cid' );

				// If ajax trigger via debounce, else do it here and retun data.
				if ( callbackFunction.ajax ) {
					if ( 'generated_element' !== this.model.get( 'type' ) ) {
						FusionPageBuilderApp.shortcodeAjax = true;
					}
					this._triggerCallback( false, callbackFunction );
				}
			},

			/**
			 * Check if element needs a google font loaded.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			needsGoogle: function() {
				var variant    = ':regular',
					$fontNodes = this.$el.find( '[data-fusion-google-font]' ),
					script,
					scriptID;

				if ( $fontNodes.length ) {
					$fontNodes.each( function() {
						var family = jQuery( this ).attr( 'data-fusion-google-font' );
						family = family.replace( /"/g, '&quot' );

						script  = family;
						script += ( variant ) ? variant : '';

						scriptID = script.replace( /:/g, '' ).replace( /"/g, '' ).replace( /'/g, '' ).replace( / /g, '' ).replace( /,/, '' );

						if ( ! jQuery( 'head' ).find( '#' + scriptID ).length ) {
							jQuery( 'head' ).first().append( '<script id="' + scriptID + '">WebFont.load({google:{families:["' + script + '"]},context:FusionApp.previewWindow,active: function(){ jQuery( window ).trigger( "fusion-font-loaded"); },});</script>' );
						}
					} );
				}
			},

			/**
			 * Triggers for columns.
			 *
			 * @since 2.0.0
			 * @param {Object} parent The parent object.
			 * @return {void}
			 */
			triggerColumn: function( parent ) {
				var parentCid = 'undefined' === typeof parent ? this.model.attributes.parent : parent;
				setTimeout( function() {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-content-changed', parentCid );
				}, 300 );
			},

			/**
			 * Get template attributes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplateAtts: function() {
				var element = fusionAllElements[ this.model.get( 'element_type' ) ],
					templateAttributes = jQuery.extend( true, {}, this.model.attributes ),
					params = jQuery.extend( true, {}, this.model.get( 'params' ) ),
					values = {},
					extras = {};

				// Set values & extras
				if ( element && 'undefined' !== typeof element.defaults ) {
					values = jQuery.extend( true, {}, element.defaults, _.fusionCleanParameters( params ) );
					if ( 'undefined' !== typeof element.extras ) {
						extras = jQuery.extend( true, {}, element.extras );
					}
				}

				templateAttributes.values = values;
				templateAttributes.extras = extras;

				templateAttributes = this.getDynamicAtts( templateAttributes );
				templateAttributes = this.filterTemplateAtts( templateAttributes );

				return templateAttributes;
			},

			/**
			 * Render the content.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			renderContent: function() {
				var $elementContent = this.$el.find( '.fusion-builder-element-content' ),
					element         = fusionAllElements[ this.model.get( 'element_type' ) ],
					self            = this,
					markup;

				// If needs query add loader and either trigger or check where triggered.
				if ( 'undefined' !== typeof element.callback && 'undefined' === typeof this.model.get( 'query_data' ) && true === element.callback.ajax ) {

					// If this is first render, use markup if it exists.
					if ( ! this.renderedYet && 'undefined' !== typeof this.model.get( 'markup' ) ) {
						markup = this.model.get( 'markup' );
						$elementContent.html( markup.output + '<div class="fusion-clearfix"></div>' );

						return;
					}
					this.addLoadingOverlay();
					this.triggerQuery( element.callback );
					return;
				}

				// Otherwise use element template
				$elementContent.html( self.getTemplate() );
			},

			/**
			 * Removes an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event The event triggering the element removal.
			 * @param {bool} forceManually - Force manually, even if it's not an event, to update history and trigger content changes.
			 * @return {void}
			 */
			removeElement: function( event, isAutomated, forceManually ) {
				var parentCid   = this.model.get( 'parent' ),
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parentCid; // jshint ignore: line
					} ),
					colView,
					MultiGlobalArgs;

				if ( event ) {
					event.preventDefault();
				}

				// If the column is deleted manually.
				if ( event || forceManually ) {
					colView = FusionPageBuilderViewManager.getView( parentCid );
					colView.$el.find( '.fusion-builder-module-controls-container a' ).trigger( 'mouseleave' );

					FusionEvents.trigger( 'fusion-content-changed' );
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );
				}

				// Hook to allow custom actions.
				this.beforeRemove();

				// Removes scripts which have been moved to body.
				FusionApp.deleteScripts( this.model.get( 'cid' ) );

				// Remove live editors.
				FusionPageBuilderApp.inlineEditorHelpers.removeLiveEditors( this );

				// Remove element view
				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				// Destroy element model
				this.model.destroy();

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				// Update column trigger.
				this.triggerColumn( parentCid );

				this.remove();

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
			 * Opens dynamic source PO.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 *
			 */
			openDynamicSourcePO: function( event ) { // eslint-disable-line no-unused-vars
				if ( 'undefined' !== typeof FusionApp.sidebarView ) {
					FusionApp.sidebarView.openOption( 'dynamic_content_preview_type', 'po' );
				}
			},

			/**
			 * Clones an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @param {bool} forceManually - Force manually, even if it's not an event, to update history and trigger content changes.
			 * @return {void}
			 *
			 */
			cloneElement: function( event, forceManually ) {
				var elementAttributes,
					currentModel,
					MultiGlobalArgs;

				if ( event ) {
					event.preventDefault();
				}

				if ( ( 'undefined' !== typeof this.$el.data( 'cloning-disabled' ) && 1 === this.$el.data( 'cloning-disabled' ) ) || true === this.elementIsCloning ) {
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

					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.cloned + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );
				}

				// Update column trigger.
				this.triggerColumn();

			},

			/**
			 * Get the content.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getContent: function() {
				return FusionPageBuilderApp.generateElementShortcode( this.$el, false );
			},

			/**
			 * Get the placeholder.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getPlaceholder: function() {
				var label  		= window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				var icon   		= window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				var placeholder = _.template( '<div class="fusion-builder-placeholder-preview"><i class="<%= icon %>" aria-hidden="true"></i> <%= label %></div>' );
				return placeholder( { icon: icon, label: label } );
			},

			/**
			 * Get component placeholder.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getComponentPlaceholder: function() {
				var placeholder = jQuery( this.getPlaceholder() ).append( '<span class="fusion-tb-source-separator"> - </span><a href="#" class="fusion-tb-source">' + fusionBuilderText.dynamic_source + '</a>' );
				return placeholder[ 0 ].outerHTML;
			}

		} );
	} );
}( jQuery ) );
