/* global fusionGlobalManager, fusionBuilderText, fusionAppConfig, fusionAllElements, FusionEvents, FusionPageBuilderViewManager, FusionPageBuilderApp, FusionPageBuilderElements, FusionApp */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Nested Column View
		FusionPageBuilder.NestedColumnView = FusionPageBuilder.BaseColumnView.extend( {

			template: FusionPageBuilder.template( $( '#fusion-builder-inner-column-template' ).html() ),

			events: {
				'click .fusion-builder-add-element': 'addModule',
				'click .fusion-builder-settings-column': 'settings',
				'click .fusion-builder-column-remove': 'removeColumn',
				'click .fusion-builder-column-clone': 'cloneColumn',
				'click .fusion-builder-column-size': 'sizesShow',
				'click .fusion-builder-column-drag': 'preventDefault'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
				this.$el.attr( 'data-column-size', this.model.attributes.params.type );

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					this.$el.attr( 'fusion-global-layout', this.model.attributes.params.fusion_global );
					this.$el.removeClass( 'fusion-global-nested-column' ).addClass( 'fusion-global-nested-column' );
				}

				this.columnSpacer        = false;
				this.forceAppendChildren = false;

				this.listenTo( FusionEvents, 'fusion-view-update-fusion_builder_column_inner', this.reRender );

				this.baseColumnInit();
				this.baseInit();
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function() {
				var self = this,
					data = this.getTemplateAtts();

				this.$el.html( this.template( data ) );

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				this.appendChildren();

				setTimeout( function() {
					self.droppableColumn();
				}, 100 );

				return this;
			},

			droppableColumn: function() {
				var self        = this,
					$el         = this.$el,
					cid         = this.model.get( 'cid' ),
					$droppables = $el.find( '.fusion-nested-column-target' ),
					$body       = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );

				$el.draggable( {
					appendTo: FusionPageBuilderApp.$el,
					zIndex: 999999,
					delay: 100,
					cursorAt: { top: 15, left: 15 },
					iframeScroll: true,
					containment: $body,
					cancel: '.fusion-builder-live-element',
					helper: function() {
						var $classes = FusionPageBuilderApp.DraggableHelpers.draggableClasses( cid );
						return jQuery( '<div class="fusion-column-helper ' + $classes + '" data-cid="' + cid + '"><span class="fusiona-column"></span></div>' );
					},
					start: function() {
						$body.addClass( 'fusion-nested-column-dragging fusion-active-dragging' );
						$el.addClass( 'fusion-being-dragged' );
					},
					stop: function() {
						setTimeout( function() {
							$body.removeClass( 'fusion-nested-column-dragging fusion-active-dragging' );
						}, 10 );
						$el.removeClass( 'fusion-being-dragged' );
					}
				} );

				$droppables.droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-column-inner',
					drop: function( event, ui ) {
						var handleColumnNestedDrop = self.handleColumnNestedDrop.bind( self );
						handleColumnNestedDrop( ui.draggable, $el, jQuery( event.target ) );
					}
				} );

				$el.find( '.fusion-element-target-column' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-live-element',
					drop: function( event, ui ) {
						var handleElementDropInsideColumn = self.handleElementDropInsideColumn.bind( self );
						handleElementDropInsideColumn( ui.draggable, $el );
					}
				} );
			},

			handleElementDropInsideColumn: function( $element, $targetEl ) {
				var parentCid   = this.model.get( 'cid' ),
					elementCid  = $element.data( 'cid' ),
					elementView = FusionPageBuilderViewManager.getView( elementCid ),
					newIndex,
					MultiGlobalArgs;

				// Move the actual html.
				$targetEl.find( '.fusion-nested-column-content' ).append( $element );

				newIndex = $element.parent().children( '.fusion-builder-live-element' ).index( $element );

				FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, parentCid );

				// Save history state
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.moved + ' ' + fusionAllElements[ elementView.model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element );

				// Handle multiple global elements.
				MultiGlobalArgs = {
					currentModel: elementView.model,
					handleType: 'save',
					attributes: elementView.model.attributes
				};
				fusionGlobalManager.handleMultiGlobal( MultiGlobalArgs );

				FusionEvents.trigger( 'fusion-content-changed' );

				this._equalHeights();
			},

			handleColumnNestedDrop: function( $column, $targetEl, $dropTarget ) {
				var parentCid,
					destinationRow,
					columnCid      = $column.data( 'cid' ),
					columnView     = FusionPageBuilderViewManager.getView( columnCid ),
					originalCid    = columnView.model.get( 'parent' ),
					originalView,
					newIndex;

				if ( 'large' !== FusionApp.getPreviewWindowSize() && 'undefined' !== typeof this.isFlex && true === this.isFlex ) {

					// Update columns' order.
					FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) )._updateResponsiveColumnsOrder(
						$column,
						$targetEl.closest( '.fusion-builder-row' ).children( '.fusion-builder-column-inner' ),
						parseInt( jQuery( $dropTarget ).closest( '.fusion-builder-column-inner' ).data( 'cid' ) ),
						jQuery( $dropTarget ).hasClass( 'target-after' )
					);

					return;
				}

				// Move the actual html.
				if ( jQuery( $dropTarget ).hasClass( 'target-after' ) ) {
					$targetEl.after( $column );
				} else {
					$targetEl.before( $column );
				}

				parentCid      = $column.closest( '.fusion-builder-row-content' ).data( 'cid' );
				destinationRow = FusionPageBuilderViewManager.getView( parentCid );

				newIndex = $column.parent().children( '.fusion-builder-column-inner' ).index( $column );
				FusionPageBuilderApp.onDropCollectionUpdate( columnView.model, newIndex, parentCid );

				// Update destination row which is this current one.
				destinationRow.setRowData();

				// If destination row and original row are different, update original as well.
				if ( parentCid !== originalCid ) {
					originalView = FusionPageBuilderViewManager.getView( originalCid );
					originalView.setRowData();
				}

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.column + ' Order Changed' );

				setTimeout( function() {
					// If different container type we re-render so that it corrects for new situation.
					if ( 'object' !== typeof originalView || FusionPageBuilderApp.sameContainerTypes( originalView.model.get( 'parent' ), destinationRow.model.get( 'parent' ) ) ) {
						columnView.droppableColumn();
					} else {
						FusionEvents.trigger( 'fusion-close-settings-' + columnView.model.get( 'cid' ) );
						columnView.reRender();
					}
				}, 300 );
			},

			/**
			 * Triggers a refresh.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			refreshJs: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_builder_column_inner', this.model.attributes.cid );
			},

			/**
			 * Removes a column.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event triggering the column removal.
			 * @param {bool} forceManually - Force manually, even if it's not an event, to update history and trigger content changes.
			 * @return {void}
			 */
			removeColumn: function( event, forceManually ) {
				var modules,
					row = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					parentCid = this.$el.closest( '.fusion-builder-column-outer' ).data( 'cid' );

				if ( event ) {
					event.preventDefault();
				}

				setTimeout( function() {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-content-changed', parentCid );
				}, 300 );

				modules = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				_.each( modules, function( module ) {
					module.removeElement();
				} );

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				row.setRowData();

				// If the column is deleted manually
				if ( event || forceManually ) {
					FusionEvents.trigger( 'fusion-content-changed' );
				}
			},

			/**
			 * Appends children to the columns.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			appendChildren: function() {
				var self = this,
					cid,
					view;

				this.model.children.each( function( child ) {

					cid  = child.attributes.cid;
					view = FusionPageBuilderViewManager.getView( cid );

					self.$el.find( '.fusion-builder-column-content' ).append( view.$el );

					view.delegateEvents();
				} );
			},

			/**
			 * Adds a child view.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element.
			 * @return {void}
			 */
			addChildView: function( element ) {

				var view,
					viewSettings = {
						model: element,
						collection: FusionPageBuilderElements,
						attributes: {
							'data-cid': element.get( 'cid' )
						}
					};

				if ( 'undefined' !== typeof element.get( 'multi' ) && 'multi_element_parent' === element.get( 'multi' ) ) {

					if ( 'undefined' !== typeof FusionPageBuilder[ element.get( 'element_type' ) ] ) {
						view = new FusionPageBuilder[ element.get( 'element_type' ) ]( viewSettings );
					} else {
						view = new FusionPageBuilder.ParentElementView( viewSettings );
					}

				} else if ( 'undefined' !== typeof FusionPageBuilder[ element.get( 'element_type' ) ] ) {
					view = new FusionPageBuilder[ element.get( 'element_type' ) ]( viewSettings );
				} else {
					view = new FusionPageBuilder.ElementView( viewSettings );
				}

				// Add new view to manager
				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				// Add element builder view to proper column
				if (  'undefined' !== typeof this.model && 'fusion_builder_column_inner' === this.model.get( 'type' ) ) {

					if ( ! _.isUndefined( element.get( 'targetElement' ) ) && 'undefined' === typeof element.get( 'from' ) ) {
						if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'after' === element.get( 'targetElementPosition' ) ) {
							element.get( 'targetElement' ).after( view.render().el );
						} else {
							element.get( 'targetElement' ).before( view.render().el );
						}
					} else if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'end' === element.get( 'targetElementPosition' ) ) {
						this.$el.find( '.fusion-builder-column-content.fusion-nested-column-content' ).append( view.render().el );
					} else {
						this.$el.find( '.fusion-builder-column-content.fusion-nested-column-content' ).find( '.fusion-builder-empty-column' ).after( view.render().el );
					}

				} else if ( ! _.isUndefined( element.get( 'targetElement' ) ) && 'undefined' === typeof element.get( 'from' ) ) {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'after' === element.get( 'targetElementPosition' ) ) {
						element.get( 'targetElement' ).after( view.render().el );
					} else {
						element.get( 'targetElement' ).before( view.render().el );
					}
				} else if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'end' === element.get( 'targetElementPosition' ) ) {
					this.$el.find( '.fusion-builder-column-content.fusion-nested-column-content' ).append( view.render().el );
				} else {
					this.$el.find( '.fusion-builder-column-content.fusion-nested-column-content' ).find( '.fusion-builder-empty-column' ).after( view.render().el );
				}

				// Check if we should open the settings or not.
				if ( 'off' !== FusionApp.preferencesData.open_settings && 'undefined' !== typeof element.get( 'added' ) ) {
					view.settings();
				}
			},

			/**
			 * Clones a column.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @param {bool} forceManually - Force manually, even if it's not an event, to update history and trigger content changes.
			 * @return {void}
			 */
			cloneColumn: function( event, forceManually ) {
				var columnAttributes = jQuery.extend( true, {}, this.model.attributes ),
					row              = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					$thisColumn;

				if ( event ) {
					event.preventDefault();
				}

				columnAttributes.created       = 'manually';
				columnAttributes.cid           = FusionPageBuilderViewManager.generateCid();
				columnAttributes.targetElement = this.$el;
				columnAttributes.cloned        = true;
				columnAttributes.at_index      = FusionPageBuilderApp.getCollectionIndex( this.$el );

				FusionPageBuilderApp.collection.add( columnAttributes );

				// Parse column elements
				$thisColumn = this.$el;
				$thisColumn.find( '.fusion-builder-live-element' ).each( function() {
					var $thisModule,
						moduleCID,
						module,
						elementAttributes;

					// Standard element
					if ( jQuery( this ).hasClass( 'fusion-builder-live-element' ) ) {
						$thisModule = jQuery( this );
						moduleCID = 'undefined' === typeof $thisModule.data( 'cid' ) ? $thisModule.find( '.fusion-builder-data-cid' ).data( 'cid' ) : $thisModule.data( 'cid' );

						// Get model from collection by cid
						module = FusionPageBuilderElements.find( function( model ) {
							return model.get( 'cid' ) == moduleCID; // jshint ignore: line
						} );

						// Clone model attritubes
						elementAttributes         = jQuery.extend( true, {}, module.attributes );

						elementAttributes.created = 'manually';
						elementAttributes.cid     = FusionPageBuilderViewManager.generateCid();
						elementAttributes.parent  = columnAttributes.cid;
						elementAttributes.from    = 'fusion_builder_column_inner';

						// Don't need target element, position is defined from order.
						delete elementAttributes.targetElementPosition;

						FusionPageBuilderApp.collection.add( elementAttributes );
					}

				} );

				// If column is cloned manually
				if ( event || forceManually ) {

					// Save history state
					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.cloned + ' ' + fusionBuilderText.column );

					row.createVirtualRows();
					row.updateColumnsPreview();

					FusionEvents.trigger( 'fusion-content-changed' );
				}
				this._refreshJs();
			},

			/**
			 * Things to do, places to go when options change.
			 *
			 * @since 2.0.0
			 * @param {string} paramName - The name of the parameter that changed.
			 * @param {mixed}  paramValue - The value of the option that changed.
			 * @param {Object} event - The event triggering the option change.
			 * @return {void}
			 */
			onOptionChange: function( paramName, paramValue, event ) {
				var rowView,
					parentCID = this.model.get( 'parent' ),
					cid = this.model.get( 'cid' ),
					reInitDraggables = false;

				// Reverted to history step or user entered value manually.
				if ( 'undefined' === typeof event || ( 'undefined' !== typeof event && ( 'change' !== event.type || ( 'change' === event.type && 'undefined' !== typeof event.srcElement ) ) ) ) {
					reInitDraggables = true;
				}

				switch ( paramName ) {

				case 'spacing':
					this.model.attributes.params[ paramName ] = paramValue;

					// Only update preview if it a valid unit.
					if ( this.validColumnSpacing( paramValue ) ) {
						rowView = FusionPageBuilderViewManager.getView( parentCID );
						rowView.setSingleRowData( cid );
					}

					if ( true === reInitDraggables ) {
						if ( 'yes' === paramValue || 'no' === paramValue ) {
							this.destroySpacingResizable();
						} else {
							this.columnSpacer = false;
							this.columnSpacing();
						}
					}

					break;

				case 'margin_top':
				case 'margin_bottom':
					this.model.attributes.params[ paramName ] = paramValue;

					if ( true === reInitDraggables ) {
						this.destroyMarginResizable();
						this.marginDrag();
					}
					break;

				case 'padding_top':
				case 'padding_right':
				case 'padding_bottom':
				case 'padding_left':
					this.model.attributes.params[ paramName ] = paramValue;

					if ( true === reInitDraggables ) {
						this.destroyPaddingResizable();
						this.paddingDrag();
					}
					break;
				}
			},

			/**
			 * Checks if column layout type is block.
			 *
			 * @since 3.0.0
			 * @return {Boolean}
			 */
			isBlockLayout: function() {
				return this.values && 'block' === this.values.content_layout;
			},

			/**
			 * Gets the column content.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getColumnContent: function() {
				var columnParams   = {},
					shortcode      = '',
					columnAttributesCheck;

				_.each( this.model.get( 'params' ), function( value, name ) {
					columnParams[ name ] = ( 'undefined' === value || 'undefined' === typeof value ) ? '' : value;
				} );

				// Legacy support for new column options
				columnAttributesCheck = {
					min_height: '',
					last: 'no',
					hover_type: 'none',
					link: '',
					border_position: 'all'
				};

				_.each( columnAttributesCheck, function( value, name ) {
					if ( 'undefined' === typeof columnParams[ name ] ) {
						columnParams[ name ] = value;
					}
				} );

				this.beforeGenerateShortcode();

				// Build column shortcdoe
				shortcode += '[fusion_builder_column_inner type="' + columnParams.type + '"';

				_.each( columnParams, function( value, name ) {
					if ( ( 'on' === fusionAppConfig.removeEmptyAttributes && '' !== value ) || 'off' === fusionAppConfig.removeEmptyAttributes ) {
						shortcode += ' ' + name + '="' + value + '"';
					}
				} );

				shortcode += ']';

				// Find elements in this column
				this.$el.find( '.fusion-builder-live-element' ).each( function() {
					shortcode += FusionPageBuilderApp.generateElementShortcode( jQuery( this ), false );
				} );

				shortcode += '[/fusion_builder_column_inner]';

				return shortcode;
			}

		} );
	} );
}( jQuery ) );
