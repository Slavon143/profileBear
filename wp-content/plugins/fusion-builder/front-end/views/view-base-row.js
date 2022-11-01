/* global FusionPageBuilderViewManager, FusionPageBuilderApp, FusionApp, FusionEvents, fusionBuilderText */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Row View
		FusionPageBuilder.BaseRowView = window.wp.Backbone.View.extend( {

			/**
			 * On init for both regular and nested columns.
			 *
			 * @since 3.0
			 * @return null
			 */
			baseRowInit: function() {
				this._updateResponsiveColumnsOrder = _.debounce( this.updateResponsiveColumnsOrder, 100 );
			},

			reRender: function() {
				this.render( true );
			},

			/**
			 * Calculate virtual rows.
			 *
			 * @since 2.0.0
			 * @return {null}
			 */
			createVirtualRows: function() {
				var container = FusionPageBuilderApp.getParentContainer( this.model.get( 'parent' ) );

				// If we are flex, no need for virtual rows.
				if ( 'function' === typeof container.isFlex && container.isFlex() ) {
					return;
				}
				this.updateVirtualRows();
				this.assignColumn();
			},

			/**
			 * Set the initial column data to the model.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			updateVirtualRows: function() {
				var rows        = {},
					column      = {},
					columns     = [],
					count       = 0,
					index       = 0,
					oldRows     = this.model.get( 'rows' ),
					columnWidth;

				this.model.children.each( function( child ) {
					column      = {};
					columnWidth = child.attributes.params.type;

					if ( ! columnWidth ) {
						columnWidth = '1_1';
					}
					columnWidth = columnWidth.split( '_' );
					columnWidth = columnWidth[ 0 ] / columnWidth[ 1 ];
					count += columnWidth;

					if ( 1 < count ) {
						index += 1;
						count = columnWidth;
					}

					column = {
						cid: child.attributes.cid
					};

					if ( 'undefined' === typeof rows[ index ] ) {
						rows[ index ] = [ column ];
					} else {
						rows[ index ].push( column );
					}

					columns[ child.attributes.cid ] = index;
				} );

				this.model.set( 'columns', columns );
				this.model.set( 'rows', rows );

				if ( 'object' === typeof oldRows ) {
					this.model.set( 'oldRows', oldRows );
				}
			},

			/**
			 * Change the column in the model.
			 *
			 * @since 2.0.0
			 * @param {Object} column - The column view.
			 * @return {void}
			 */
			assignColumn: function() {
				var columnParams,
					self         = this,
					oldRows      = this.model.get( 'oldRows' ),
					updatedCols  = false,
					emptySpacing = true;

				// Reset first, last positions
				this.model.children.each( function( column ) {
					columnParams       = jQuery.extend( true, {}, column.get( 'params' ) );
					columnParams.first = false;
					columnParams.last  = false;
					column.set( 'params', columnParams );
				} );

				// Loop over virtual rows
				_.each( this.model.get( 'rows' ), function( row, rowIndex ) {
					var total           = row.length,
						lastIndex       = total - 1,
						rowSame         = true,
						previousSpacing = '';

					// Loop over columns inside virtual row
					_.each( row, function( col, colIndex ) {
						var columnFirst     = false,
							columnLast      = false,
							model           = self.model.children.find( function( model ) {
								return model.get( 'cid' ) == col.cid; // jshint ignore: line
							} ),
							params          = jQuery.extend( true, {}, model.get( 'params' ) ),
							spacing,
							weightedSpacing;

						// First index
						if ( 0 === colIndex ) {
							columnFirst = true;
						}

						if ( lastIndex === colIndex ) {
							columnLast = true;
						}

						params.first = columnFirst;
						params.last  = columnLast;

						// Check if we need legacy column spacing set.
						if ( 'undefined' !== typeof params.spacing && FusionPageBuilderApp.loaded ) {
							spacing = params.spacing;
							if ( 'yes' === spacing ) {
								spacing = '4%';
							} else if ( 'no' === spacing ) {
								spacing = '0px';
							}

							if ( ! params.last && '0px' !== spacing && 0 !== spacing && '0' !== spacing ) {
								emptySpacing = false;
							}
							weightedSpacing = self.getWeightedSpacing( spacing, params, total );

							// Only set params if both are unset.
							if ( 'undefined' === typeof params.spacing_left && 'undefined' === typeof params.spacing_right ) {
								// Use what is set as right spacing.
								if ( ! params.last ) {
									params.spacing_right = weightedSpacing;
								}

								// Check right spacing of previous column.
								if ( '' !== previousSpacing ) {
									params.spacing_left = self.getWeightedSpacing( previousSpacing, params, total );
								}
							}

							previousSpacing = spacing;
						} else {
							emptySpacing = false;
						}

						model.set( 'params', params );

						// Check if col is same as before.
						if ( rowSame ) {
							if ( 'object' !== typeof oldRows || 'undefined' === typeof oldRows[ rowIndex ] || 'undefined' === typeof oldRows[ rowIndex ][ colIndex ] || oldRows[ rowIndex ][ colIndex ].cid !== col.cid ) {
								rowSame = false;
							}
						}
					} );

					if ( ! rowSame && FusionPageBuilderApp.loaded ) {
						if ( false === updatedCols ) {
							updatedCols = [];
						}
						_.each( row, function( col ) {
							updatedCols.push( col.cid );
						} );
					}
				} );

				this.model.set( 'emptySpacing', emptySpacing );
				this.model.set( 'updatedCols', updatedCols );
			},

			getVirtualRowByCID: function( cid ) {
				var rows    = this.model.get( 'rows' ),
					columns = this.model.get( 'columns' ),
					index   = columns[ cid ],
					row     = rows[ index ];

				return row;
			},

			/**
			 * First render, work out legacy column map only once.
			 *
			 * @since 2.0.0
			 * @return {Object} this
			 */
			legacyColumns: function() {
				var container    = FusionPageBuilderApp.getParentContainer( this.model.get( 'parent' ) ),
					emptySpacing = false,
					nestedRows   = {};

				// If we are not in need of legacy conversion then skip.
				if ( ! container || ! container.needsLegacyConversion() ) {
					return;
				}

				// Create map of row to get correct spacing.
				this.updateVirtualRows();
				this.assignColumn();

				if ( ! this.nestedRow ) {
					// This row is all empty spacing.
					emptySpacing = this.model.get( 'emptySpacing' );

					// Run through same process for nested rows.
					this.$el.find( '.fusion-builder-row-inner' ).each( function() {
						var nestedRowCid               = jQuery( this ).attr( 'data-cid' ),
							nestedView                 = FusionPageBuilderViewManager.getView( nestedRowCid );

						// Store for later looping if necessary.
						nestedRows[ nestedRowCid ] = nestedView;

						// Update legacy maps and nested column styles.
						nestedView.legacyColumns();

						// If nested row is not empty spacing, parent row shouldn't be also.
						if ( false === nestedView.model.get( 'emptySpacing' ) ) {
							emptySpacing = false;
						}
					} );

					// If its empty spacing and all nested rows also, we will set spacing on container and re-render.
					if ( emptySpacing ) {

						// Set the spacing on container.
						container = FusionPageBuilderApp.getParentContainer( this.model.get( 'parent' ) );
						if ( container ) {
							container.setEmptySpacing();
						}

						// If we have nested rows, update them visually.
						if ( 'object' === typeof nestedRows && ! _.isEmpty( nestedRows ) ) {
							_.each( nestedRows, function( nestedRow ) {
								nestedRow.recalculateMargins();
							} );
						}

						// Update parent row visually.
						this.recalculateMargins();
					}
				}
				// Update visual appearance for direct children columns.
				this.model.children.each( function( child ) {
					var view = FusionPageBuilderViewManager.getView( child.attributes.cid );

					view.setArgs();
					view.validateArgs();
					view.setExtraArgs();
					view.setColumnMapData();
					view.setResponsiveColumnStyles();

					view.$el.find( '.fusion-column-responsive-styles' ).last().html( view.responsiveStyles );
				} );

				// Set param on container to stop it rerunning.
				if ( container && 'function' === typeof container.setType ) {
					container.setType();
				}
			},

			getHalfSpacing: function( value ) {
				var unitlessSpacing = parseFloat( value ),
					unitlessHalf    = unitlessSpacing / 2;

				return value.replace( unitlessSpacing, unitlessHalf );
			},

			validateColumnWidth: function( columnSize ) {
				var fractions;

				if ( 'undefined' === typeof columnSize ) {
					columnSize = '1_3';
				}

				// Fractional value.
				if ( -1 !== columnSize.indexOf( '_' ) ) {
					fractions = columnSize.split( '_' );
					return parseFloat( fractions[ 0 ] ) / parseFloat( fractions[ 1 ] );
				}

				// Greater than one, assume percentage and divide by 100.
				if ( 1 < parseFloat( columnSize ) ) {
					return parseFloat( columnSize ) / 100;
				}

				return columnSize;
			},

			getWeightedSpacing: function( value, params, total ) {
				var width            = parseFloat( this.validateColumnWidth( params.type ) ),
					unitlessSpacing  = parseFloat( value ),
					unitlessWeighted;

				total = 'undefined' === typeof total || false === total ? false : parseInt( total );

				if ( false !== total && 3 > total ) {
					unitlessWeighted = unitlessSpacing * width;
				} else {
					unitlessWeighted = unitlessSpacing / 2;
				}

				return value.replace( unitlessSpacing, unitlessWeighted );
			},

			updateColumnsPreview: function() {
				var container   = FusionPageBuilderApp.getParentContainer( this.model.get( 'parent' ) ),
					updatedCols = this.model.get( 'updatedCols' ),
					self        = this;

				// Update flex column preview here.
				if ( 'function' === typeof container.isFlex && container.isFlex() ) {
					return;
				}

				if ( true === FusionPageBuilderApp.loaded ) {
					this.model.children.each( function( child ) {
						var view,
							singleRow,
							columnRow;

						if ( false === updatedCols || _.contains( updatedCols, child.attributes.cid ) ) {
							view      = FusionPageBuilderViewManager.getView( child.attributes.cid );
							singleRow = self.getVirtualRowByCID( view.model.get( 'cid' ) );
							columnRow = [];

							// Update first/last classes
							view.$el.removeClass( 'fusion-column-last' );
							view.$el.removeClass( 'fusion-column-first' );

							if ( true === view.model.attributes.params.last ) {
								view.$el.addClass( 'fusion-column-last' );
							}

							if ( true === view.model.attributes.params.first ) {
								view.$el.addClass( 'fusion-column-first' );
							}

							// Update column spacing.
							_.each( singleRow, function( cid ) {
								var model,
									value;

								cid   = cid.cid;
								model = self.collection.find( function( model ) {
									return model.get( 'cid' ) == cid; // jshint ignore: line
								} );
								value = model.attributes.params.spacing;

								columnRow.push( value );
							} );

							view.columnSpacingPreview( columnRow );
						}
					} );
				}
			},

			/**
			 * Sets the row data.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			setRowData: function() {
				this.createVirtualRows();
				this.updateColumnsPreview();
			},

			setSingleRowData: function( cid ) {
				var row = this.getVirtualRowByCID( cid ),
					view;

				_.each( row, function( column ) {
					view = FusionPageBuilderViewManager.getView( column.cid );
					view.reRender();
				} );
			},

			/**
			 * Mode change for container.
			 *
			 * @since 3.0
			 * @return {void}
			 */
			modeChange: function() {
				this.setRowData();
				this.reRender( true );
				this.reRenderColumns();

				// Refresh nested rows if they exist.
				if ( ! this.nestedRow ) {
					this.reRenderNestedRows();
				}
			},

			/**
			 * Mode change for container.
			 *
			 * @since 3.0
			 * @return {void}
			 */
			updateInnerStyles: function() {
				this.setRowData();
				this.reRender( true );

				if ( this.nestedRow ) {
					this.appendChildren( false );
				}

				this.model.children.each( function( child ) {
					var cid  = child.attributes.cid;
					var column = FusionPageBuilderViewManager.getView( cid );

					if ( column ) {
						column.updateInnerStyles();
					}
				} );

				// Refresh nested rows if they exist.
				if ( ! this.nestedRow ) {
					this.$el.find( '.fusion_builder_row_inner' ).each( function( ) {
						var cid = jQuery( this ).attr( 'data-cid' ),
							row = FusionPageBuilderViewManager.getView( cid );
						if ( row ) {
							row.updateInnerStyles();
						}
					} );
				}
				this.delegateChildEvents();
			},

			/**
			 * Re-render the nested rows.
			 *
			 * @since 3.0
			 * @return {void}
			 */
			reRenderNestedRows: function() {
				this.$el.find( '.fusion_builder_row_inner' ).each( function( ) {
					var cid = jQuery( this ).attr( 'data-cid' ),
						row = FusionPageBuilderViewManager.getView( cid );

					if ( 'object' === typeof row ) {
						row.modeChange();
						row.appendChildren();
					}
				} );
			},

			/**
			 * Re-render columns
			 *
			 * @since 3.0
			 * @return {void}
			 */
			reRenderColumns: function() {
				var cid,
					view;
				this.model.children.each( function( child ) {

					cid  = child.attributes.cid;
					view = FusionPageBuilderViewManager.getView( cid );

					if ( view ) {
						view.reRender();
					}
				} );
			},

			/**
			 * Updates columns' order params.
			 * @return {void}
			 */
			updateResponsiveColumnsOrder: function( draggedColumn, columns, targetColumnCID, insertAfterTargetColumn ) {
				var viewportSize       = FusionApp.getPreviewWindowSize(),
					draggedColumnCID   = parseInt( draggedColumn.data( 'cid' ) ),
					draggedColumnOrder = parseInt( draggedColumn.css( 'order' ) ),
					columnsArray       = [],
					index              = 0,
					columnView;

				if ( 'large' === viewportSize ) {
					return;
				}

				jQuery( columns ).each( function( scopedIndex, column ) {

					// TODO: handle case when multiple columns have same order set.
					if ( draggedColumnCID !== jQuery( column ).data( 'cid' ) ) {
						columnsArray.push( [ parseInt( jQuery( column ).data( 'cid' ) ), parseInt( jQuery( column ).css( 'order' ) ) ] );
					}

				} );

				// Sort columns by CSS order.
				columnsArray.sort( function( col1, col2 ) {
					return col1[ 1 ] - col2[ 1 ];
				} );

				// Find index (position) of target column.
				for ( index = 0; index < columnsArray.length; index++ ) {
					if ( targetColumnCID === columnsArray[ index ][ 0 ] ) {
						break;
					}
				}

				// In case we're inserting before target column.
				if ( ! insertAfterTargetColumn ) {
					index--;
				}

				// Insert dragged column in it's place. Note that index is position in 'splice' context (not array index).
				columnsArray.splice( index + 1, 0, [ draggedColumnCID, draggedColumnOrder ] );

				// Index is not longer relevant, using it just as iterator.
				for ( index = 0; index < columnsArray.length; index++ ) {

					// Get column view by CID.
					columnView = FusionPageBuilderViewManager.getView( columnsArray[ index ][ 0 ] );

					// Update order param and value.
					columnView.model.attributes.params[ 'order_' + viewportSize  ] = index;
					columnView.values[ 'order_' + viewportSize  ]                  = index;

					// Update column's responsive styles.
					columnView.setResponsiveColumnStyles();
					columnView.$el.find( '.fusion-column-responsive-styles' ).last().html( columnView.responsiveStyles );

					// Update EO panel if opened.
					if ( jQuery( '.fusion-builder-module-settings[data-element-cid="' + columnsArray[ index ][ 0 ] + '"' ) ) {
						FusionEvents.trigger( 'fusion-param-changed-' + columnView.model.get( 'cid' ), 'order_' + viewportSize, index );
					}
				}

				// Trigger change and add history event.
				FusionEvents.trigger( 'fusion-history-save-step', fusionBuilderText.column + ' Order Changed' );
			},

			scrollHighlight: function( scroll = true, highlight = true ) {
				var $trigger       = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( '.fusion-one-page-text-link' ),
					$el            = this.$el,
					elementIdAdded = false,
					elId           = $el.attr( 'id' );

				scroll = 'undefined' === typeof scroll ? true : scroll;

				if ( ! elId ) {
					$el.attr( 'id', 'fusion-temporary-id-' + this.cid );
					elId = 'fusion-temporary-id-' + this.cid;
					elementIdAdded = true;
				}

				setTimeout( function() {
					if ( scroll && $trigger.length && 'function' === typeof $trigger.fusion_scroll_to_anchor_target ) {
						$trigger.attr( 'href', '#' + elId ).fusion_scroll_to_anchor_target( 15 );
					}

					if ( elementIdAdded ) {
						setTimeout( function() {
							$el.removeAttr( 'id' );
						}, 10 );
					}

					if ( highlight ) {
						$el.addClass( 'fusion-active-highlight' );
						setTimeout( function() {
							$el.removeClass( 'fusion-active-highlight' );
						}, 6000 );
					}
				}, 10 );
			}

		} );
	} );
}( jQuery ) );
