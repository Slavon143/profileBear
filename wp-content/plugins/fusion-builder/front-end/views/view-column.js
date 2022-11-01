/* global FusionPageBuilderViewManager, fusionAppConfig, fusionAppConfig, FusionApp, fusionGlobalManager, fusionBuilderText, FusionPageBuilderApp, FusionPageBuilderElements, FusionEvents, fusionAllElements */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Column View
		FusionPageBuilder.ColumnView = FusionPageBuilder.BaseColumnView.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-column-template' ).html() ),

			events: {
				'click .fusion-builder-column-settings:not(.fusion-builder-column-inner .fusion-builder-column-setting)': 'settings',
				'click .fusion-builder-column-size:not(.fusion-builder-column-inner .fusion-builder-column-size)': 'sizesShow',
				'hover .fusion-builder-column-content': 'offsetClass',
				'click .column-size:not(.fusion-builder-column-inner .column-size)': 'sizeSelect',
				'click .fusion-builder-add-element:not(.fusion-builder-column-inner .fusion-builder-add-element)': 'addModule',
				'click .fusion-builder-column-remove:not(.fusion-builder-column-inner .fusion-builder-column-remove)': 'removeColumn',
				'click .fusion-builder-column-clone:not(.fusion-builder-column-inner .fusion-builder-column-clone)': 'cloneColumn',
				'click .fusion-builder-column-save:not(.fusion-builder-column-inner .fusion-builder-column-save)': 'openLibrary',
				'click .fusion-builder-column-drag:not(.fusion-builder-column-inner .fusion-builder-column-drag)': 'preventDefault'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {
				var params  = this.model.get( 'params' ),
					spacing = '' !== params.spacing ? spacing : '4%';

				this.renderedYet         = false;
				this.columnSpacer        = false;
				this.forceAppendChildren = false;

				this.listenTo( FusionEvents, 'fusion-view-update-fusion_builder_column', this.reRender );

				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
				this.$el.attr( 'id', 'fusion-column-' + this.model.get( 'cid' ) );
				this.$el.attr( 'data-column-size', this.model.attributes.params.type );
				this.$el.attr( 'data-column-spacing', spacing );

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
					this.$el.attr( 'fusion-global-layout', this.model.attributes.params.fusion_global );
					this.$el.removeClass( 'fusion-global-column' ).addClass( 'fusion-global-column' );
				}

				this.currentClasses = '';

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
					data = this.getTemplateAtts(),
					columnSize = '';

				this.$el.html( this.template( data ) );

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Add active column size CSS class
				columnSize = this.model.attributes.params.type;
				// TODO Check size and update class according.
				this.updateSizeIndicators();

				this.appendChildren();

				setTimeout( function() {
					self.droppableColumn();
				}, 100 );

				// Don't refresh on first render.
				if ( this.renderedYet ) {
					this._refreshJs();
				}

				this.renderedYet = true;

				return this;
			},

			droppableColumn: function() {
				var self = this,
					$el  = this.$el,
					cid,
					$droppables,
					$body;

				if ( ! $el ) {
					return;
				}

				cid         = this.model.get( 'cid' );
				$droppables = $el.find( '.fusion-column-target' );
				$body       = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );

				$el.draggable( {
					appendTo: FusionPageBuilderApp.$el,
					zIndex: 999999,
					delay: 100,
					cursorAt: { top: 15, left: 15 },
					iframeScroll: true,
					containment: $body,
					cancel: '.fusion-builder-live-element, .fusion_builder_row_inner',
					helper: function() {
						var $classes = FusionPageBuilderApp.DraggableHelpers.draggableClasses( cid ),
							style = '';

						if ( $el.css( 'margin-top' ) ) {
							style = 'style="transform: translateY(' + $el.css( 'margin-top' ) + ');"';
						}

						return jQuery( '<div><div class="fusion-column-helper ' + $classes + '" data-cid="' + cid + '"' + style + '><span class="fusiona-column"></span></div></div>' );
					},
					start: function() {
						$body.addClass( 'fusion-column-dragging fusion-active-dragging' );
						$el.addClass( 'fusion-being-dragged' );

						if ( 'large' !== FusionApp.getPreviewWindowSize() ) {
							$body.addClass( 'fusion-column-dragging-responsive-mode' );
							$el.closest( '.fusion-builder-container' ).addClass( 'fusion-has-active-drop-targets' );
						}
					},
					stop: function() {
						setTimeout( function() {
							$body.removeClass( 'fusion-column-dragging fusion-active-dragging' );
						}, 10 );
						$el.removeClass( 'fusion-being-dragged' );

						if ( 'large' !== FusionApp.getPreviewWindowSize() ) {
							$el.closest( '.fusion-builder-container' ).removeClass( 'fusion-has-active-drop-targets' );
							$body.removeClass( 'fusion-column-dragging-responsive-mode' );
						}
					}
				} );

				$droppables.droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-column',
					drop: function( event, ui ) {
						var handleDropColumn = self.handleDropColumn.bind( self );
						handleDropColumn( ui.draggable, $el, jQuery( event.target ) );
					}
				} );

				$el.find( '.fusion-element-target-column' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-live-element, .fusion_builder_row_inner',
					drop: function( event, ui ) {
						var handleElementDropInsideColumn = self.handleElementDropInsideColumn.bind( self );
						handleElementDropInsideColumn( ui.draggable, $el );
					}
				} );
			},

			handleElementDropInsideColumn: function( $element, $targetEl ) {
				var elementView  = FusionPageBuilderViewManager.getView( $element.data( 'cid' ) ),
					newIndex,
					MultiGlobalArgs;

				// Move the actual html.
				$targetEl.find( '.fusion-builder-column-content:not(.fusion_builder_row_inner .fusion-builder-column-content ):not( .fusion-nested-column-content )' ).append( $element );

				newIndex = $element.parent().children( '.fusion-builder-live-element, .fusion_builder_row_inner' ).index( $element );

				FusionPageBuilderApp.onDropCollectionUpdate( elementView.model, newIndex, this.model.get( 'cid' ) );

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

			handleDropColumn: function( $column, $targetEl, $dropTarget ) {
				var destinationRow,
					columnCid      = $column.data( 'cid' ),
					columnView     = FusionPageBuilderViewManager.getView( columnCid ),
					originalCid    = columnView.model.get( 'parent' ),
					parentCid      = $targetEl.closest( '.fusion-builder-row' ).data( 'cid' ),
					originalView,
					newIndex;

				if ( 'large' !== FusionApp.getPreviewWindowSize() && 'undefined' !== typeof this.isFlex && true === this.isFlex ) {

					// Update columns' order.
					FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) )._updateResponsiveColumnsOrder(
						$column,
						$targetEl.closest( '.fusion-builder-row' ).children( '.fusion-builder-column' ),
						parseInt( $dropTarget.closest( '.fusion-builder-column' ).data( 'cid' ) ),
						$dropTarget.hasClass( 'target-after' )
					);

					return;
				}

				// Move the actual html.
				if ( $dropTarget.hasClass( 'target-after' ) ) {
					$targetEl.after( $column );
				} else {
					$targetEl.before( $column );
				}

				destinationRow = FusionPageBuilderViewManager.getView( parentCid );

				newIndex = $column.parent().children( '.fusion-builder-column' ).index( $column );

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
					parentCID            = this.model.get( 'parent' ),
					cid                  = this.model.get( 'cid' ),
					dimensionType		 = _.find( [ 'spacing_', 'margin_', 'padding_' ], function( type ) {
						return paramName.includes( type );
					} ),
					reInitDraggables     = false,
					view                 = {},
					values               = {},
					alphaBackgroundColor = 1;

				// Reverted to history step or user entered value manually.
				if ( 'undefined' === typeof event || ( 'undefined' !== typeof event && ( 'change' !== event.type || ( 'change' === event.type && 'undefined' !== typeof event.srcElement ) ) ) ) {
					reInitDraggables = true;
				}

				if ( 'spacing' === paramName ) {
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
				}

				if ( dimensionType ) {
					this.model.attributes.params[ paramName ] = paramValue;

					if ( true === reInitDraggables ) {

						if ( 'padding_' === dimensionType ) {
							this.destroyPaddingResizable();
							this.paddingDrag();
						} else {
							this.destroyMarginResizable();
							this.marginDrag();
						}

					}
				}

				if ( 'padding' === paramName ) {
					if ( -1 === jQuery( event.target ).attr( 'name' ).indexOf( '_' ) ) {
						this.model.attributes.params[ paramName ] = paramValue;
						this.renderSectionSeps( event );
						this._refreshJs();
					}
				}


				if ( 'padding_left' === paramName || 'padding_right' === paramName ) {
					this.renderSectionSeps( event );
				}

				if ( [ 'border_size', 'border_color', 'border_style', 'border_position' ].includes( paramName ) ) {
					this.model.attributes.params[ paramName ] = paramValue;
				}
			},

			/**
			 * Render the section separators.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			renderSectionSeps: function() {
				var elements = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				_.each( elements, function( element ) {
					if ( 'fusion_section_separator' === element.model.get( 'element_type' ) ) {
						element.reRender();
					}
				} );
			},

			/**
			 * Triggers a refresh.
			 *
			 * @since 2.0.0
			 * @return void
			 */
			refreshJs: function() {
				jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_builder_column', this.model.attributes.cid );
			},

			/**
			 * Changes the border styles for the element.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			borderStyle: function( event ) {
				var borderSize     = this.model.attributes.params.border_size + 'px',
					borderColor    = this.model.attributes.params.border_color,
					borderStyle    = this.model.attributes.params.border_style,
					borderPosition = this.model.attributes.params.border_position,
					positions      = [ 'top', 'right', 'bottom', 'left' ],
					self           = this,
					$target        = ( 'lift_up' === this.model.attributes.params.hover_type ) ? self.$el.find( '.fusion-column-wrapper, .fusion-column-inner-bg-image' ) : self.$el.find( '.fusion-column-wrapper' );

				if ( event ) {
					event.preventDefault();
				}
				self.$el.find( '.fusion-column-wrapper, .fusion-column-inner-bg-image' ).css( 'border', '' );
				if ( 'all' === borderPosition ) {
					_.each( positions, function( position ) {
						$target.css( 'border-' + position, borderSize + ' ' + borderStyle + ' ' + borderColor );
					} );
				} else {
					_.each( positions, function( position ) {
						if ( position === borderPosition ) {
							$target.css( 'border-' + position, borderSize + ' ' + borderStyle + ' ' + borderColor );
						} else {
							$target.css( 'border-' + position, 'none' );
						}
					} );
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
					$thisColumn,
					container;

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
				$thisColumn.find( '.fusion-builder-live-element:not(.fusion-builder-column-inner .fusion-builder-live-element), .fusion-builder-nested-element' ).each( function() {
					var $thisModule,
						moduleCID,
						module,
						elementAttributes,
						$thisInnerRow,
						innerRowCID,
						innerRowView;

					// Standard element
					if ( jQuery( this ).hasClass( 'fusion-builder-live-element' ) ) {
						$thisModule = jQuery( this );
						moduleCID   = 'undefined' === typeof $thisModule.data( 'cid' ) ? $thisModule.find( '.fusion-builder-data-cid' ).data( 'cid' ) : $thisModule.data( 'cid' );

						// Get model from collection by cid
						module = FusionPageBuilderElements.find( function( model ) {
							return model.get( 'cid' ) == moduleCID; // jshint ignore: line
						} );

						// Clone model attritubes
						elementAttributes          = jQuery.extend( true, {}, module.attributes );

						elementAttributes.created  = 'manually';
						elementAttributes.cid      = FusionPageBuilderViewManager.generateCid();
						elementAttributes.parent   = columnAttributes.cid;
						elementAttributes.from     = 'fusion_builder_column';

						// Don't need target element, position is defined from order.
						delete elementAttributes.targetElementPosition;

						FusionPageBuilderApp.collection.add( elementAttributes );

					// Inner row/nested element
					} else if ( jQuery( this ).hasClass( 'fusion_builder_row_inner' ) ) {
						$thisInnerRow = jQuery( this );
						innerRowCID   = 'undefined' === typeof $thisInnerRow.data( 'cid' ) ? $thisInnerRow.find( '.fusion-builder-data-cid' ).data( 'cid' ) : $thisInnerRow.data( 'cid' );
						innerRowView  = FusionPageBuilderViewManager.getView( innerRowCID );

						// Clone inner row
						if ( 'undefined' !== typeof innerRowView ) {
							innerRowView.cloneNestedRow( 'clone', columnAttributes.cid );
						}
					}

				} );

				// If column is cloned manually
				if ( event || forceManually ) {

					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.cloned + ' ' + fusionBuilderText.column );

					container = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

					container.createVirtualRows();
					container.updateColumnsPreview();

					FusionEvents.trigger( 'fusion-content-changed' );
				}
				this._refreshJs();
			},

			/**
			 * Append the column's children to its content.
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

					self.$el.find( '.fusion-builder-column-content:not(.fusion_builder_row_inner .fusion-builder-column-content ):not( .fusion-nested-column-content )' ).append( view.$el );

				} );

				this.delegateChildEvents();
			},

			/**
			 * Gets the column contents.
			 *
			 * @since 2.0.0
			 * @param {Object} $thisColumn - The jQuery object of the element.
			 * @return {string}
			 */
			getColumnContent: function() {
				var shortcode    = '',
					columnParams = {},
					self         = this,
					ColumnAttributesCheck;

				_.each( this.model.get( 'params' ), function( value, name ) {
					columnParams[ name ] = ( 'undefined' === value || 'undefined' === typeof value ) ? '' : value;
				} );

				// Legacy support for new column options
				ColumnAttributesCheck = {
					min_height: '',
					last: 'no',
					hover_type: 'none',
					link: '',
					border_position: 'all'
				};

				_.each( ColumnAttributesCheck, function( value, name ) {
					if ( 'undefined' === typeof columnParams[ name ] ) {
						columnParams[ name ] = value;
					}
				} );

				this.beforeGenerateShortcode();

				// Build column shortcode
				shortcode += '[fusion_builder_column type="' + this.model.attributes.params.type + '"';

				_.each( columnParams, function( value, name ) {
					if ( ( 'on' === fusionAppConfig.removeEmptyAttributes && '' !== value ) || 'off' === fusionAppConfig.removeEmptyAttributes ) {
						shortcode += ' ' + name + '="' + value + '"';
					}
				} );

				shortcode += ']';

				// Find elements inside this column
				this.$el.find( '.fusion-builder-live-element:not(.fusion-builder-column-inner .fusion-builder-live-element), .fusion-builder-nested-element' ).each( function() {
					var $thisRowInner;

					// Find standard elements
					if ( jQuery( this ).hasClass( 'fusion-builder-live-element' ) ) {
						shortcode += FusionPageBuilderApp.generateElementShortcode( jQuery( this ), false );

					// Find inner rows
					} else {
						$thisRowInner = FusionPageBuilderViewManager.getView( jQuery( this ).data( 'cid' ) );
						if ( 'undefined' !== typeof $thisRowInner ) {
							shortcode += $thisRowInner.getInnerRowContent();
						}

					}
				} );

				shortcode += '[/fusion_builder_column]';

				return shortcode;
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
				var elements,
					rowView,
					parentCID = this.model.get( 'parent' );

				if ( event ) {
					event.preventDefault();
				}

				elements = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				_.each( elements, function( element ) {
					if ( 'fusion_builder_row' === element.model.get( 'type' ) || 'fusion_builder_row_inner' === element.model.get( 'type' ) ) {
						element.removeRow();
					} else {
						element.removeElement();
					}
				} );

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this._equalHeights( parentCID );

				FusionEvents.trigger( 'fusion-element-removed', this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				// If the column is deleted manually
				if ( event || forceManually ) {
					// Update preview for spacing.
					rowView = FusionPageBuilderViewManager.getView( parentCID );
					rowView.setRowData();

					FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.deleted + ' ' + fusionBuilderText.column );

					FusionEvents.trigger( 'fusion-content-changed' );

					rowView.$el.find( '.fusion-builder-module-controls-container a' ).trigger( 'mouseleave' );
				}
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
					},
					containerSuffix = ':not(.fusion_builder_row_inner .fusion-builder-column-content)';

				if ( 'undefined' !== typeof element.get( 'multi' ) && 'multi_element_parent' === element.get( 'multi' ) ) {

					if ( 'undefined' !== typeof FusionPageBuilder[ element.get( 'element_type' ) ] ) {
						view = new FusionPageBuilder[ element.get( 'element_type' ) ]( viewSettings );
					} else {
						view = new FusionPageBuilder.ParentElementView( viewSettings );
					}

				} else if ( 'undefined' !== typeof FusionPageBuilder[ element.get( 'element_type' ) ] ) {
					view = new FusionPageBuilder[ element.get( 'element_type' ) ]( viewSettings );
				} else if ( 'fusion_builder_row_inner' === element.get( 'element_type' ) ) {
					view = new FusionPageBuilder.InnerRowView( viewSettings );
				} else {
					view = new FusionPageBuilder.ElementView( viewSettings );
				}

				// Add new view to manager.
				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if (  'undefined' !== typeof this.model && 'fusion_builder_column_inner' === this.model.get( 'type' ) ) {
					containerSuffix = '';
				}

				if ( ! _.isUndefined( element.get( 'targetElement' ) ) && 'undefined' === typeof element.get( 'from' ) ) {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'after' === element.get( 'targetElementPosition' ) ) {
						element.get( 'targetElement' ).after( view.render().el );
					} else {
						element.get( 'targetElement' ).before( view.render().el );
					}
				} else if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'end' === element.get( 'targetElementPosition' ) ) {
					if ( 'fusion_widget' === view.model.get( 'element_type' ) ) {
						// eslint-disable-next-line vars-on-top
						var renderedView = view.render();
						renderedView.$el.find( 'script' ).remove();
						this.$el.find( '.fusion-builder-column-content' + containerSuffix ).append( renderedView.el );
					} else {
						this.$el.find( '.fusion-builder-column-content' + containerSuffix ).append( view.render().el );
					}
				} else {
					this.$el.find( '.fusion-builder-column-content' + containerSuffix ).find( '.fusion-builder-empty-column' ).first().after( view.render().el );
				}

				// Check if we should open the settings or not.
				if ( 'off' !== window.FusionApp.preferencesData.open_settings && 'undefined' !== typeof element.get( 'added' ) ) {
					if ( 'fusion_builder_row_inner' === element.get( 'type' ) ) {
						view.editRow();
					} else {
						view.settings();
					}
				}
			},

			/**
			 * Get the save label.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getSaveLabel: function() {
				return fusionBuilderText.save_column;
			},

			/**
			 * Returns the 'columns' string.
			 *
			 * @since 2.0.0
			 * @return {string}
			 */
			getCategory: function() {
				return 'columns';
			},

			/**
			 * Column spacing dimensions version.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			fallbackColumnSpacing: function( $placeholder, allNo ) {
				var columnSize      = '100%',
					fullcolumnSize  = columnSize,
					existingSpacing = '0%',
					columnWidth     = this.model.attributes.params.type;

				if ( 'yes' === this.model.attributes.params.spacing ) {
					existingSpacing = '4%';
				}

				columnWidth = this.model.attributes.params.type;

				switch ( columnWidth ) {
				case '1_1':
					columnSize     = '100%';
					fullcolumnSize = '100%';
					break;
				case '1_4':
					columnSize     = '22%';
					fullcolumnSize = '25%';
					break;
				case '3_4':
					columnSize     = '74%';
					fullcolumnSize = '75%';
					break;
				case '1_2':
					columnSize     = '48%';
					fullcolumnSize = '50%';
					break;
				case '1_3':
					columnSize     = '30.6666%';
					fullcolumnSize = '33.3333%';
					break;
				case '2_3':
					columnSize     = '65.3333%';
					fullcolumnSize = '66.6666%';
					break;
				case '1_5':
					columnSize     = '16.8%';
					fullcolumnSize = '20%';
					break;
				case '2_5':
					columnSize     = '37.6%';
					fullcolumnSize = '40%';
					break;
				case '3_5':
					columnSize     = '58.4%';
					fullcolumnSize = '60%';
					break;
				case '4_5':
					columnSize     = '79.2%';
					fullcolumnSize = '80%';
					break;
				case '5_6':
					columnSize     = '82.6666%';
					fullcolumnSize = '83.3333%';
					break;
				case '1_6':
					columnSize     = '13.3333%';
					fullcolumnSize = '16.6666%';
					break;
				}

				if ( '4%' !== existingSpacing && ( ! this.model.attributes.params.last || allNo ) ) {
					columnSize = fullcolumnSize;
				}

				this.$el.css( 'width', columnSize );
				$placeholder.css( 'width', columnSize );
				$placeholder.css( 'margin-right', existingSpacing );
				this.$el.css( 'margin-right', existingSpacing );
			},

			/**
			 * Checks if column layout type is block.
			 *
			 * @since 3.0.0
			 * @return {Boolean}
			 */
			isBlockLayout: function() {
				return this.values && 'block' === this.values.content_layout;
			}

		} );
	} );
}( jQuery ) );
