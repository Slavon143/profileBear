/* global FusionApp, FusionPageBuilderApp, fusionBuilderText, FusionPageBuilderViewManager, FusionEvents, FusionPageBuilderElements, fusionAllElements, fusionSanitize */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Row View
		FusionPageBuilder.RowView = FusionPageBuilder.BaseRowView.extend( {

			template: FusionPageBuilder.template( jQuery( '#fusion-builder-row-template' ).html() ),
			className: 'fusion_builder_row',
			events: {
				'click .fusion-builder-insert-column': 'displayColumnsOptions'
			},

			/**
			 * Init.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			initialize: function() {

				this.baseRowInit();
				this.nestedRow = false;

				this.model.set( 'rows', {} );
				this.model.children = new FusionPageBuilder.Collection();
				this.listenTo( this.model.children, 'add', this.addChildView );
				this.listenTo( FusionEvents, 'fusion-builder-loaded', this.updateColumnsPreview );
				this.listenTo( FusionEvents, 'fusion-builder-loaded', this.legacyColumns );
			},

			/**
			 * Renders the view.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			render: function( forced ) {
				var self = this;

				forced = 'undefined' === typeof forced ? false : forced;

				this.$el.html( this.template( this.getTemplateAtts() ) );

				this.appendChildren();

				setTimeout( function() {
					self.droppableColumn();
				}, 100 );

				// Show column dialog when adding a new row
				if ( 'manually' !== this.model.get( 'created' ) && ! forced ) {
					this.displayContainerLibrary();
				}

				return this;
			},

			/**
			 * The row attributes.
			 *
			 * @since 3.0
			 * @return {Object}
			 */
			attr: function() {
				var attr 			= {
					style: ''
				},
				parentContainer = FusionPageBuilderApp.getParentContainer( this ),
				isFlex			= parentContainer && parentContainer.isFlex(),
				containerParams,
				width,
				widthValue,
				columnSpacingUnit,
				columnSpacingValue,
				widthUnit;

				if ( ! parentContainer ) {
					return attr;
				}

				containerParams = jQuery.extend( true, {}, fusionAllElements.fusion_builder_container.defaults, _.fusionCleanParameters( parentContainer.model.attributes.params ) ),

				attr[ 'class' ] 	= 'fusion-builder-row-container fusion-builder-row fusion-row';
				attr[ 'data-cid' ] 	= this.model.get( 'cid' );


				if ( isFlex ) {
					attr[ 'class' ]  += ' fusion-flex-align-items-' + containerParams.flex_align_items;
					if ( 'stretch' !== containerParams.align_content ) {
						attr[ 'class' ] += ' fusion-flex-align-content-' + containerParams.align_content;
					}
					if ( 'flex-start' !== containerParams.flex_justify_content ) {
						attr[ 'class' ] += ' fusion-flex-justify-content-' + containerParams.flex_justify_content;
					}
					width             = 'yes' === containerParams.hundred_percent ? '100%' :  fusionAllElements.fusion_builder_row.extras.site_width;
					columnSpacingUnit = fusionSanitize.get_unit( containerParams.flex_column_spacing );

					if ( '%' === columnSpacingUnit ) {
						columnSpacingValue = fusionSanitize.number( containerParams.flex_column_spacing );
						widthValue         = fusionSanitize.number( width );
						widthUnit          = fusionSanitize.get_unit( width );

						width = ( widthValue * ( 1 + ( columnSpacingValue / 100 ) ) ) + widthUnit;
					} else {
						width = 'calc( ' + width + ' + ' + containerParams.flex_column_spacing + ' )';
					}

					if ( 'no' === containerParams.hundred_percent ) {
						attr.style += 'max-width:' + width + ';';
					}
					attr.style += 'margin-left: calc(-' + containerParams.flex_column_spacing + ' / 2 );';
					attr.style += 'margin-right: calc(-' + containerParams.flex_column_spacing + ' / 2 );';
				}

				return attr;
			},

			/**
			 * Get template attributes.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getTemplateAtts: function()  {
				var templateAttributes 	= {
						attr: this.attr()
					};

				return templateAttributes;
			},

			/**
			 * Display the column options.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			displayColumnsOptions: function( event ) {

				var viewSettings,
					view;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.parentRowId = this.model.get( 'cid' );

				viewSettings = {
					model: this.model,
					collection: this.collection
				};

				if ( jQuery( event.currentTarget ).closest( '.fusion-builder-column' ).length ) {
					viewSettings.targetElement = jQuery( event.currentTarget ).closest( '.fusion-builder-column' );
				}

				view = new FusionPageBuilder.ColumnLibraryView( viewSettings );

				jQuery( view.render().el ).dialog( {
					title: 'Select Column',
					width: FusionApp.dialog.dialogWidth,
					height: FusionApp.dialog.dialogHeight,
					draggable: false,
					modal: true,
					resizable: false,
					dialogClass: 'fusion-builder-dialog fusion-builder-large-library-dialog fusion-builder-columns-library-dialog',

					open: function() {
						FusionApp.dialog.resizeDialog();
					},

					close: function() {
						view.remove();
					}
				} );
			},

			/**
			 * Display the container library.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			displayContainerLibrary: function( event ) {

				var viewSettings,
					view,
					parentView;

				if ( event ) {
					event.preventDefault();
				}

				FusionPageBuilderApp.parentRowId = this.model.get( 'cid' );
				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				viewSettings = {
					model: this.model,
					collection: this.collection
				};

				view = new FusionPageBuilder.ContainerLibraryView( viewSettings );

				jQuery( view.render().el ).dialog( {
					title: 'Select Container',
					width: FusionApp.dialog.dialogWidth,
					height: FusionApp.dialog.dialogHeight,
					draggable: false,
					modal: true,
					resizable: false,
					dialogClass: 'fusion-builder-dialog fusion-builder-large-library-dialog fusion-builder-container-library-dialog',
					open: function() {
						FusionApp.dialog.resizeDialog();
					},
					close: function() {
						parentView.removeContainer();
						view.remove();
					}
				} );
			},

			/**
			 * Removes a row.
			 *
			 * @since 2.0.0
			 * @param {Object} event - The event.
			 * @return {void}
			 */
			removeRow: function( event ) { // jshint ignore: line

				var columns;

				if ( event ) {
					event.preventDefault();
				}

				columns = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				// Remove all columns
				_.each( columns, function( column ) {
					if ( 'function' === typeof column.removeColumn ) {
						column.removeColumn();
					}
				} );

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				this.setRowData();

				if ( event ) {
					FusionEvents.trigger( 'fusion-content-changed' );
				}
			},

			/**
			 * Creates drop zone for empty row.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			droppableColumn: function() {
				var $el  = this.$el,
					self = this;

				if ( ! $el ) {
					return;
				}

				$el.find( '.fusion-builder-empty-container .fusion-column-target' ).droppable( {
					tolerance: 'touch',
					hoverClass: 'ui-droppable-active',
					accept: '.fusion-builder-column',
					drop: function( event, ui ) {
						var handleColumnDropInsideRow = self.handleColumnDropInsideRow.bind( self );
						handleColumnDropInsideRow( ui.draggable, $el );
					}
				} );
			},

			handleColumnDropInsideRow: function( $element, $targetEl ) {
				var columnCid      = $element.data( 'cid' ),
					columnView     = FusionPageBuilderViewManager.getView( columnCid ),
					originalCid    = columnView.model.get( 'parent' ),
					self           = this,
					originalView,
					newIndex;

				// Move the actual html.
				$targetEl.find( '.fusion-builder-empty-container' ).after( $element );

				newIndex = $element.parent().children( '.fusion-builder-column' ).index( $element );

				FusionPageBuilderApp.onDropCollectionUpdate( columnView.model, newIndex, this.model.get( 'cid' ) );

				// Update destination row which is this current one.
				this.setRowData();

				// If destination row and original row are different, update original as well.
				if ( this.model.get( 'cid' ) !== originalCid ) {
					originalView = FusionPageBuilderViewManager.getView( originalCid );
					originalView.setRowData();
				}

				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.column + ' order changed' );

				setTimeout( function() {
					// If different container type we re-render so that it corrects for new situation.
					if ( 'object' !== typeof originalView || FusionPageBuilderApp.sameContainerTypes( originalView.model.get( 'parent' ), self.model.get( 'parent' ) ) ) {
						columnView.droppableColumn();
					} else {
						FusionEvents.trigger( 'fusion-close-settings-' + columnView.model.get( 'cid' ) );
						columnView.reRender();
					}
				}, 300 );
			},

			/**
			 * Appends children. Calls the delegateEvents function in the view.
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

					self.$el.find( '.fusion-builder-row-container' ).append( view.$el );

					view.delegateEvents();
				} );
			},

			/**
			 * Adds a child view.
			 *
			 * @since 2.0.0
			 * @param {Object} element - The element model.
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

				viewSettings.className = 'fusion-builder-column fusion-builder-column-outer';

				if ( 'string' === typeof element.attributes.params.type && element.attributes.params.type.includes( '_' ) ) {
					viewSettings.className += ' fusion-builder-column-' + element.attributes.params.type;
				}
				view = new FusionPageBuilder.ColumnView( viewSettings );

				// Calculate virtual rows
				this.createVirtualRows();

				// This column was cloned
				if ( ! _.isUndefined( element.get( 'cloned' ) ) && true === element.get( 'cloned' ) ) {
					element.targetElement = view.$el;
					element.unset( 'cloned' );
				}

				FusionPageBuilderViewManager.addView( element.get( 'cid' ), view );

				if ( ! _.isUndefined( element.get( 'targetElement' ) ) && 'undefined' === typeof element.get( 'from' ) ) {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'after' === element.get( 'targetElementPosition' ) ) {
						element.get( 'targetElement' ).after( view.render().el );
					} else {
						element.get( 'targetElement' ).before( view.render().el );
					}
				} else {
					if ( 'undefined' === typeof element.get( 'targetElementPosition' ) || 'end' === element.get( 'targetElementPosition' ) ) {
						this.$el.find( '.fusion-builder-row-container' ).append( view.render().el );
					} else {
						this.$el.find( '.fusion-builder-row-container' ).prepend( view.render().el );
					}
					element.unset( 'from' );
				}

				this.updateColumnsPreview();
			},

			/**
			 * Delegates child events.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			delegateChildEvents: function() {
				var cid,
					view;

				this.model.children.each( function( child ) {

					cid  = child.attributes.cid;
					view = FusionPageBuilderViewManager.getView( cid );

					view.delegateEvents();
					view.delegateChildEvents();
					view.droppableColumn();
				} );
			},

			recalculateMargins: function() {
				var attr = this.attr();

				this.$el.find( '.fusion-builder-row-container' ).first().attr( 'style', attr.style );
			}

		} );
	} );
}( jQuery ) );
