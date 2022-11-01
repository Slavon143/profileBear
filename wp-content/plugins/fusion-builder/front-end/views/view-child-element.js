/* global FusionEvents, FusionPageBuilderApp, FusionPageBuilderViewManager, fusionGlobalManager, fusionBuilderText, fusionAllElements, FusionPageBuilderElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Element View
		FusionPageBuilder.ChildElementView = FusionPageBuilder.BaseView.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-child-element-template' ).html() ),

			className: 'fusion-builder-live-child-element fusion-builder-data-cid',
			tagName: function() {
				return this.model.get( 'tag_name' );
			},

			events: {
				'click .fusion-builder-remove-child': 'removeElement',
				'click .fusion-builder-clone-child': 'cloneElement',
				'click .fusion-builder-settings-child': 'settings',
				'click a': 'disableLink'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {

				var parent = this.model.get( 'parent' ),
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parent; // jshint ignore:line
					} );

				this.model.inlineCollection = new FusionPageBuilder.Collection();

				this._triggerCallback = _.debounce( _.bind( this.triggerCallback, this ), 200 );

				// If triggering a view update.
				this.listenTo( FusionEvents, 'fusion-child-view-update', this.reRender );
				this.listenTo( FusionEvents, 'fusion-view-update-' + this.model.get( 'cid' ), this.reRender );
				this.listenTo( FusionEvents, 'fusion-view-update-' + this.model.get( 'element_type' ), this.reRender );

				// If there is a template.
				if ( jQuery( '#tmpl-' + this.model.attributes.element_type + '-shortcode' ).length ) {
					this.model.set( 'noTemplate', false );
					this.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-' + this.model.attributes.element_type + '-shortcode' ).html() );
				} else {
					this.model.set( 'noTemplate', true );
					this.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_shortcode-shortcode' ).html() );
				}

				this.elementIsCloning = false;

				this.model.set( 'editLabel', this.getEditLabel() );

				// JQuery trigger.
				this._refreshJs = _.debounce( _.bind( this.refreshJs, this ), 300 );

				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
				this.$el.attr( 'data-parent-cid', this.model.get( 'parent' ) );
				this.$el.attr( 'data-element-type', this.model.get( 'element_type' ) );

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					this.$el.attr( 'fusion-global-layout', this.model.attributes.params.fusion_global );
					this.$el.removeClass( 'fusion-global-child-element' ).addClass( 'fusion-global-child-element' );
				}

				this.model.set( 'show_ui', 'undefined' === typeof fusionAllElements[ this.model.get( 'element_type' ) ].show_ui ? true : fusionAllElements[ this.model.get( 'element_type' ) ].show_ui );

				if ( ! parentModel.get( 'sortable' ) ) {
					this.$el.attr( 'data-fusion-no-dragging', true );
				}
				this.model.set( 'sortable', parentModel.get( 'sortable' ) );

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

				this.$el.html( this.template( this.model.attributes ) );

				this.renderContent();

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				if ( 'undefined' !== typeof this.model.attributes.extraAppend ) {
					this.updateExtraContents();
				}

				this.$el.find( '.fusion-builder-module-controls-container' ).on( 'mouseenter mouseleave', _.bind( this.changeParentContainerControlsZindex, this ) );

				this.onRender();

				this._refreshJs();

				setTimeout( function() {
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

			afterPatch: function() {
				this._refreshJs();
			},

			/**
			 * Changes the z-index on the controls wrapper of the parent container.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			changeParentContainerControlsZindex: function( event ) {
				if ( 'mouseenter' === event.type ) {
					this.$el.closest( '.fusion-builder-container' ).find( '.fusion-builder-module-controls-container-wrapper' ).css( 'z-index', '0' );
				} else {
					this.$el.closest( '.fusion-builder-container' ).find( '.fusion-builder-module-controls-container-wrapper' ).removeAttr( 'style' );
				}
			},

			/**
			 * Updates extra elements by replacing their contents.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			updateExtraContents: function() {
				var self        = this,
					extraAppend = this.model.get( 'extraAppend' ),
					contents    = extraAppend.contents,
					existing    = extraAppend.existing;

				if ( 'object' === typeof extraAppend.existing ) {
					_.each( existing, function( old, index ) {
						self.updateSingleExtraContent( old, contents[ index ] );
					} );
				} else {
					this.updateSingleExtraContent( existing, contents );
				}

				if ( 'undefined' !== typeof this.model.attributes.extraAppend.trigger ) {
					this.$el.find( 'a[id="' + this.model.attributes.extraAppend.trigger.replace( '#', '' ) + '"]' ).closest( 'li' ).trigger( 'click' );
				}
			},

			updateSingleExtraContent: function( existing, contents ) {
				if ( this.$el.closest( '.fusion-builder-live-element' ).find( existing ).length ) {
					this.$el.closest( '.fusion-builder-live-element' ).find( existing ).replaceWith( FusionPageBuilderApp.renderContent( contents, this.model.get( 'cid' ), this.model.get( 'parent' ) ) );
				}
			},

			/**
			 * Removes extra elements by removing their contents.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			removeExtraContents: function() {
				var $parentEl = this.$el.closest( '.fusion-builder-live-element' ),
					$existing,
					targetId,
					$sibling;

				// Find and remove extra content.
				if ( 'undefined' !== typeof this.model.attributes.extraAppend && 'undefined' !== typeof this.model.attributes.extraAppend.existing ) {
					$existing = Array.isArray( this.model.attributes.extraAppend.existing ) ? $parentEl.find( this.model.attributes.extraAppend.existing.join( ', ' ) ) : $parentEl.find( this.model.attributes.extraAppend.existing );
					const $existingTabContent = Array.isArray( this.model.attributes.extraAppend.existing ) ? $parentEl.find( this.model.attributes.extraAppend.existing[ 1 ] ) : $parentEl.find( this.model.attributes.extraAppend.existing );

					// If tabs element and this tab is active, make another tab active.
					if ( $existing.hasClass( 'active' ) && 'fusion_tab' === this.model.get( 'element_type' ) ) {
						$sibling = $existingTabContent.siblings( '.tab-pane' ).first();

						if ( $sibling.length ) {
							$sibling.addClass( 'active in' );
							targetId = $sibling.attr( 'id' );
							$parentEl.find( '[href="#' + targetId + '"]' ).closest( '.fusion-builder-live-child-element' ).addClass( 'active' );
						}
					}
					$existing.remove();

				}

			},

			/**
			 * Sets the attributes of an element.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element we're updating.
			 * @param {Object} attributes - The attributes we're setting/updating.
			 * @return {void}
			 */
			setElementAttributes: function( element, attributes ) {
				var dataVar;
				if ( 'object' === typeof attributes && element.length ) {
					_.each( attributes, function( values, attribute ) {
						if ( 'class' === attribute ) {
							element.attr( 'class', values );
						} else if ( 'id' === attribute ) {
							element.attr( 'id', values );
						} else if ( 'style' === attribute ) {
							element.attr( 'style', values );
						} else if ( -1 !== attribute.indexOf( 'data' ) ) {
							dataVar = attribute.replace( 'data-', '' );
							if ( element.data( dataVar ) ) {
								element = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( element );
								element.data( dataVar, values );
							}
							attribute = attribute.replace( /_/g, '-' );
							element.attr( attribute, values );
						}
					} );
				}
			},

			/**
			 * Renders the content.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplateAtts: function() {
				var templateAttributes = jQuery.extend( true, {}, this.model.attributes ),
					params = jQuery.extend( true, {}, this.model.get( 'params' ) ),
					values = {},
					extras = {},
					element = fusionAllElements[ this.model.get( 'element_type' ) ],
					parent  = this.model.get( 'parent' ),
					parentValues = {},
					parentModel,
					parentElementContent = '';

				// Use appropriate template.
				if ( 'undefined' !== typeof this.elementTemplate ) {

					// Get parent values.
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parent; // jshint ignore:line
					} );

					if ( parentModel && 'undefined' !== typeof fusionAllElements[ parentModel.get( 'element_type' ) ] ) {
						parentValues = jQuery.extend( true, {}, fusionAllElements[ parentModel.get( 'element_type' ) ].defaults, _.fusionCleanParameters( parentModel.get( 'params' ) ) );
					}

					// Get element values.
					if ( element && 'undefined' !== typeof element.defaults ) {

						// No need to inherit parent's element_content.
						if ( 'undefined' !== typeof parentValues.element_content ) {
							parentElementContent = parentValues.element_content;
							delete parentValues.element_content;
						}

						values = jQuery.extend( true, {}, element.defaults, parentValues, _.fusionCleanParameters( params ) );

						parentValues.element_content = parentElementContent;

						// Get element extras.
						if ( 'undefined' !== typeof element.extras ) {
							extras = jQuery.extend( true, {}, element.extras );
						}
					}

					templateAttributes.parentValues = parentValues;
					templateAttributes.values       = values;
					templateAttributes.extras       = extras;
					templateAttributes.thisModel    = this.model;
					templateAttributes.parentModel  = parentModel;

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

				// Use appropriate template.
				if ( 'undefined' !== typeof this.elementTemplate ) {

					this.$el.find( '.fusion-builder-child-element-content' ).html( this.getTemplate() );
					return;
				}

				// Ajax here
				this.$el.find( '.fusion-builder-child-element-content' ).html( 'no template found' );
			},

			/**
			 * Removes an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element removal.
			 * @return {void}
			 */
			removeElement: function( event, isAutomated ) {
				var parentCid   = this.model.get( 'parent' ),
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					parentModel = FusionPageBuilderElements.find( function( model ) {
						return model.get( 'cid' ) == parentCid; // jshint ignore: line
					} ),
					MultiGlobalArgs;

				if ( event ) {
					event.preventDefault();

					parentView.$el.find( '.fusion-builder-module-controls-container a' ).trigger( 'mouseleave' );
				}

				// Hook to allow custom actions.
				this.beforeRemove();

				// Remove extra content not within view.
				this.removeExtraContents();

				// Remove element view
				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				// Remove live editors.
				FusionPageBuilderApp.inlineEditorHelpers.removeLiveEditors( this );

				// Destroy element model
				this.model.destroy();

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				this.remove();

				// If element is removed manually
				if ( event ) {
					this.forceUpdateParent();
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

				if ( event ) {
					parentView.childViewRemoved();
				}
			},

			/**
			 * Force-updates the parent element.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			forceUpdateParent: function() {

				// Used to make sure parent of child is updated on live edit.
				var parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				if ( 'undefined' !== typeof parentView ) {
					parentView.updateElementContent();
					parentView.refreshJs();
				}

				this.ajaxUpdateParent( parentView );
			},

			ajaxUpdateParent: function( parentView ) {
				parentView = 'undefined' === typeof parentView ? FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ) : parentView;

				// If no template, use ajax to re-render.
				if ( parentView.model.get( 'noTemplate' ) ) {
					parentView.noTemplateAjaxUpdate();
				}
			},

			/**
			 * Clones an element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the element cloning.
			 * @return {void}
			 */
			cloneElement: function( event ) {
				var elementAttributes,
					parentModel,
					currentModel,
					MultiGlobalArgs,
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					self       = this;

				// Get element parent
				parentModel = this.collection.find( function( model ) {
					return model.get( 'cid' ) == self.model.get( 'parent' ); // jshint ignore: line
				} );

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

				// Add a clone flag for fusion gallery child.
				if ( 'fusion_gallery_image' === this.model.get( 'element_type' ) ) {
					elementAttributes.cloned = true;
				}

				if ( 'undefined' !== elementAttributes.from ) {
					delete elementAttributes.from;
				}

				FusionPageBuilderApp.addToChildCollection( elementAttributes );

				currentModel = parentModel.children.find( function( model ) {
					return model.get( 'cid' ) == elementAttributes.cid; // jshint ignore:line
				} );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.cloned + ' ' + fusionAllElements[ this.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: currentModel,
					handleType: 'save',
					attributes: currentModel.attributes
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				this.elementIsCloning = false;

				this.forceUpdateParent();

				if ( event ) {
					FusionEvents.trigger( 'fusion-content-changed' );
				}

				parentView.childViewCloned();
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

			isFirstChild: function() {
				var self = this,
					index,
					parentModel;

				parentModel = FusionPageBuilderApp.collection.find( function( model ) {
					return model.get( 'cid' ) === self.model.get( 'parent' );
				} );

				index = parentModel.children.indexOf( this.model );

				return 0 === index;
			}
		} );
	} );
}( jQuery ) );
