/* global fusionBuilderConfig, fusionHistoryManager, fusionBuilderText, fusionAllElements, FusionPageBuilderEvents, FusionPageBuilderViewManager, FusionPageBuilderApp, FusionPageBuilderElements */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {
		// Base Column View
		FusionPageBuilder.BaseColumnView = window.wp.Backbone.View.extend( {

			initialize: function() {
				this.$el.attr( 'data-cid', this.model.get( 'cid' ) );
				this.$el.attr( 'data-column-size', this.model.get( 'layout' ) );

				this.isNested 			= 'fusion_builder_column' !== this.model.get( 'type' );
				this.parentContainer 	= FusionPageBuilderApp.getParentContainer( this.model.get( 'parent' ) );

				// TODO Find why type is removed in favor of layout...
				this.model.attributes.params.type = this.model.get( 'layout' );

				this.listenTo( this.model, 'change:params', this.onParamsChange );
			},

            render: function() {
				this.$el.html( this.template( this.model.toJSON() ) );

				this.sortableElements();

				this.updateContainerSizeAndLabel( this.model.get( 'layout' ) );

				this.updateFlexOptions();

				if ( ! this.isNested ) {

					// If global, make it.
					if ( 'undefined' !== typeof this.model.attributes.params.fusion_global ) {
						FusionPageBuilderApp.addClassToElement( this.$el, 'fusion-global-column', this.model.attributes.params.fusion_global, this.model.get( 'cid' ) );
					}
				}

				return this;
			},

			updateFlexOptions: function() {
				var params = this.model.get( 'params' );
				if ( 'undefined' !== typeof params.align_self && '' !== params.align_self ) {
					this.$el.css( { 'align-self': params.align_self } );
				}
			},

			updateContainerSizeAndLabel: function( size ) {
				var label = '';

				// If new size is fraction.
				if ( size.includes( '_' ) ) {
					label = size.replace( '_', '/' );
					// Update CSS ClassName
					this.$el.addClass( 'fusion-builder-column-' + size );
					this.$el.find( '.column-size-' + size ).addClass( 'active-size' );
				} else if ( 'auto' === size ) {
					label = 'auto';
					this.$el.css( { 'width': '97%'  } );
				} else {
					label = ( this.validateColumnSize( size ) * 100 ).toFixed( 2 );
					// Update Style.
					this.$el.css( { 'width': label - 3 + '%'  } );
					label += '%';
				}
				this.$el.find( this.isNested ? '.fusion-builder-resize-inner-column' : '.fusion-builder-resize-column' )
					.text( label );
			},

            sortableElements: function() {
				var thisEl 		= this,
					sortableOptions = {
						items: this.isNested ? '.fusion_module_block' : '.fusion_module_block:not(.fusion_builder_row_inner .fusion_module_block), .fusion_builder_row_inner',
						connectWith: this.isNested ? '.fusion-builder-column-inner' : '.fusion-builder-column-outer',
						cancel: '.fusion-builder-settings, .fusion-builder-clone, .fusion-builder-remove, .fusion-builder-add-element, .fusion-builder-insert-column, .fusion-builder-save-module-dialog',
						tolerance: 'pointer',
						update: function( event, ui ) {
							var $moduleBlock = $( ui.item ),
								moduleCID    = ui.item.data( 'cid' ),
								model        = thisEl.collection.find( function( model ) {
									return model.get( 'cid' ) === moduleCID;
								} );

							// If column is empty add element before "Add Element" button
							if ( $( ui.item ).closest( event.target ).length && 1 === $( event.target ).find( '.fusion_module_block, .fusion_builder_row_inner' ).length ) {

								$moduleBlock.insertBefore( $( event.target ).find( '> .fusion-builder-add-element' ) );
							}

							// Moved the element within the same column
							if ( model.get( 'parent' ) === thisEl.model.attributes.cid && $( ui.item ).closest( event.target ).length ) {

							// Moved the element to a different column
							} else {
								model.set( 'parent', thisEl.model.attributes.cid );
							}

							// Save history state
							fusionHistoryManager.turnOnTracking();
							window.fusionHistoryState = fusionBuilderText.moved + ' ' + fusionAllElements[ model.get( 'element_type' ) ].name + ' ' + fusionBuilderText.element;
							FusionPageBuilderEvents.trigger( 'fusion-element-sorted' );
						}
					};

				if ( ! this.isNested ) {
					sortableOptions.cancel += ' .fusion-builder-remove-inner-row, .fusion-builder-save-inner-row-dialog-button, .fusion-builder-remove-inner-row, .fusion_builder_row_inner .fusion-builder-row-content';
					sortableOptions.over = function( event ) {

						// Move sortable palceholder above +Element button for empty columns.
						if ( 1 === $( event.target ).find( '.fusion_module_block, .fusion_builder_row_inner' ).length ) {
							$( event.target ).find( '.ui-sortable-placeholder' ).insertBefore( $( event.target ).find( '.fusion-builder-add-element' ) );
						}
					};
				}

				this.$el.sortable( sortableOptions );
			},

			saveColumnDialog: function( event ) {
				if ( event ) {
					event.preventDefault();
				}
				FusionPageBuilderApp.showLibrary();

				$( '#fusion-builder-layouts-columns-trigger' ).click();

				$( '#fusion-builder-layouts-columns .fusion-builder-layouts-header-element-fields' ).append( '<div class="fusion-save-element-fields"><input type="text" value="" id="fusion-builder-save-element-input" class="fusion-builder-save-element-input" placeholder="' + fusionBuilderText.enter_name + '" /><div class="save-as-global"><label><input type="checkbox" id="fusion_save_global" name="fusion_save_global">' + fusionBuilderText.save_global + '</label><a href="#" class="fusion-builder-save-column fusion-builder-element-button-save" data-element-cid="' + this.model.get( 'cid' ) + '">' + fusionBuilderText.save_column + '</a></div></div>' );
			},

			// Save column
			saveElement: function( event ) {
				var $thisColumn      = this.$el,
					elementContent   = this.getColumnContent( $thisColumn ),
					$mainContainer   = $( '#fusion_builder_main_container' ),
					elementName      = $( '#fusion-builder-save-element-input' ).val(),
					saveGlobal       = $( '#fusion_save_global' ).is( ':checked' ),
					layoutsContainer = $( '#fusion-builder-layouts-columns .fusion-page-layouts' ),
					emptyMessage     = $( '#fusion-builder-layouts-columns .fusion-page-layouts .fusion-empty-library-message' ),
					thisModel        = this.model,
					isDuplicate      = false,
					oldGLobalID      = null,
					params           = {};

				if ( event ) {
					event.preventDefault();
				}

				if ( 'undefined' !== typeof this.model.attributes.params && 'undefined' !== typeof this.model.attributes.params.fusion_global && 0 < $mainContainer.find( '[fusion-global-layout="' + this.model.attributes.params.fusion_global + '"]' ).length ) {

					// Make a copy.
					oldGLobalID = this.model.attributes.params.fusion_global;
					params      = this.model.get( 'params' );

					// Remove temporarily and update model
					delete params.fusion_global;
					this.model.set( 'params', params );

					// Get content.
					elementContent = this.getColumnContent( $thisColumn );

					// Add it back.
					params.fusion_global = oldGLobalID;
					this.model.set( 'params', params );
				}

				$.each( jQuery( 'ul.fusion-page-layouts.fusion-layout-columns li' ), function() {
					var templateName = jQuery( this ).find( 'h4.fusion-page-layout-title' ).html().split( '<div ' )[ 0 ];
					templateName     = templateName.replace( /\u2013|\u2014/g, '-' );
					if ( elementName.toLowerCase().trim() === templateName.toLowerCase().trim() ) {
						alert( fusionBuilderText.duplicate_element_name_error ); // eslint-disable-line no-alert
						isDuplicate = true;
						return false;
					}
				} );

				if ( true === FusionPageBuilderApp.layoutIsSaving || true === isDuplicate ) {
					return;
				}
				FusionPageBuilderApp.layoutIsSaving = true;

				if ( '' !== elementName ) {

					$.ajax( {
						type: 'POST',
						url: fusionBuilderConfig.ajaxurl,
						dataType: 'json',
						data: {
							action: 'fusion_builder_save_layout',
							fusion_load_nonce: fusionBuilderConfig.fusion_load_nonce,
							fusion_layout_name: elementName,
							fusion_save_global: saveGlobal,
							fusion_layout_content: elementContent,
							fusion_layout_post_type: 'fusion_element',
							fusion_layout_new_cat: 'columns'
						},
						complete: function( data ) {
							FusionPageBuilderApp.layoutIsSaving = false;
							layoutsContainer.prepend( data.responseText );
							$( '.fusion-save-element-fields' ).remove();
							emptyMessage.hide();

							// If global, make it.
							if ( saveGlobal ) {
								thisModel.attributes.params.fusion_global = $( data.responseText ).attr( 'data-layout_id' );
								$( 'div[data-cid="' + thisModel.get( 'cid' ) + '"]' ).addClass( 'fusion-global-column' );
								$( 'div[data-cid="' + thisModel.get( 'cid' ) + '"]' ).attr( 'fusion-global-layout', $( data.responseText ).attr( 'data-layout_id' ) );
								$( 'div[data-cid="' + thisModel.get( 'cid' ) + '"]' ).append( '<div class="fusion-builder-global-tooltip" data-cid="' + thisModel.get( 'cid' ) + '"><span>' + fusionBuilderText.global_column + '</span></div>' );
								FusionPageBuilderEvents.trigger( 'fusion-element-added' );
								FusionPageBuilderApp.saveGlobal = true;

								// Check for globals.
								setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, thisModel.get( 'parent' ) );
							}
						}
					} );

				} else {
					FusionPageBuilderApp.layoutIsSaving = false;
					alert( fusionBuilderText.please_enter_element_name ); // eslint-disable-line no-alert
				}
			},

			getColumnContent: function( $thisColumn ) {
				var shortcode    = '',
					columnCID    = $thisColumn.data( 'cid' ),
					module       = FusionPageBuilderElements.findWhere( { cid: columnCID } ),
					colType		   = module.get( 'type' ),
					selector     = colType.includes( 'inner' ) ? '.fusion_module_block' : '.fusion_builder_column_element:not(.fusion-builder-column-inner .fusion_builder_column_element)',
					columnParams = {},
					ColumnAttributesCheck;

				_.each( module.get( 'params' ), function( value, name ) {
					if ( 'undefined' === value || 'undefined' === typeof value ) {
						columnParams[ name ] = '';
					} else {
						columnParams[ name ] = value;
					}

					if ( 'padding' === name && '' === columnParams[ name ] ) {
						columnParams.padding_top    = '';
						columnParams.padding_right  = '';
						columnParams.padding_bottom = '';
						columnParams.padding_left   = '';

						delete columnParams[ name ];
					}

					if ( 'dimension_margin' === name && '' === columnParams[ name ] ) {
						columnParams.margin_top    = '';
						columnParams.margin_bottom = '';

						delete columnParams[ name ];
					}

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

				FusionPageBuilderApp.beforeGenerateShortcode( columnCID );

				// Build column shortcode
				shortcode += '[' + colType + ' type="' + module.get( 'layout' ) + '"';

				// We use layout instead.
				delete columnParams.type;

				// Loops params and add.
				_.each( columnParams, function( value, name ) {
					if ( ( 'on' === fusionBuilderConfig.removeEmptyAttributes && '' !== value ) || 'off' === fusionBuilderConfig.removeEmptyAttributes ) {
						shortcode += ' ' + name + '="' + value + '"';
					}
				} );

				shortcode += ']';

				// Find elements inside this column
				$thisColumn.find( selector ).each( function() {
					var $thisRowInner,
						$rowInnerCID,
						$innerModule;

					// Find standard elements
					if ( $( this ).hasClass( 'fusion_module_block' ) ) {
						shortcode += FusionPageBuilderApp.generateElementShortcode( $( this ), false );

					// Find inner rows
					} else {
						$thisRowInner = $( this );
						$rowInnerCID  = $thisRowInner.data( 'cid' );
						$innerModule  = FusionPageBuilderElements.findWhere( { cid: $rowInnerCID } );
						if ( 'undefined' !== typeof $innerModule.attributes.params && 'undefined' !== typeof $innerModule.attributes.params.fusion_global ) {
							shortcode += '[fusion_builder_row_inner fusion_global="' + $innerModule.attributes.params.fusion_global + '"]';
						} else {
							shortcode += '[fusion_builder_row_inner]';
						}

						// Find nested columns
						$thisRowInner.find( '.fusion-builder-column-inner' ).each( function() {
							var $thisColumnInner  = $( this ),
								columnInnerCID    = $thisColumnInner.data( 'cid' ),
								module            = FusionPageBuilderElements.findWhere( { cid: columnInnerCID } ),
								innerColumnParams = {},
								innerColumnAttributesCheck;

							FusionPageBuilderApp.beforeGenerateShortcode( columnInnerCID );

							_.each( module.get( 'params' ), function( value, name ) {

								if ( 'undefined' === value || 'undefined' === typeof value ) {
									innerColumnParams[ name ] = '';
								} else {
									innerColumnParams[ name ] = value;
								}

							} );

							// Legacy support for new column options
							innerColumnAttributesCheck = {
								min_height: '',
								last: 'no',
								hover_type: 'none',
								link: '',
								border_position: 'all'
							};

							_.each( innerColumnAttributesCheck, function( value, name ) {

								if ( 'undefined' === typeof innerColumnParams[ name ] ) {
									innerColumnParams[ name ] = value;
								}

							} );

							// Build nested column shortcode
							shortcode += '[fusion_builder_column_inner type="' + module.get( 'layout' ) + '"';

							// We use layout instead.
							delete innerColumnParams.type;

							_.each( innerColumnParams, function( value, name ) {
								if ( ( 'on' === fusionBuilderConfig.removeEmptyAttributes && '' !== value ) || 'off' === fusionBuilderConfig.removeEmptyAttributes ) {
									shortcode += ' ' + name + '="' + value + '"';
								}
							} );

							shortcode += ']';

							// Find elements within nested columns
							$thisColumnInner.find( '.fusion_module_block' ).each( function() {
								shortcode += FusionPageBuilderApp.generateElementShortcode( $( this ), false );
							} );

							shortcode += '[/fusion_builder_column_inner]';

						} );

						shortcode += '[/fusion_builder_row_inner]';
					}

				} );

				shortcode += '[/' + colType + ']';

				return shortcode;
			},

			showSettings: function( event ) {
				var modalView,
					viewSettings = {
						model: this.model,
						collection: this.collection,
						attributes: {
							'data-modal_view': 'element_settings'
						}
					};

				if ( event ) {
					event.preventDefault();
				}

				modalView = new FusionPageBuilder.ModalView( viewSettings );

				$( 'body' ).append( modalView.render().el );
			},

			removeColumn: function( event ) {
				var modules,
					parentCID;

				if ( event ) {
					event.preventDefault();
				}

				parentCID = this.model.get( 'parent' );

				modules = FusionPageBuilderViewManager.getChildViews( this.model.get( 'cid' ) );

				_.each( modules, function( module ) {
					if ( 'fusion_builder_row' === module.model.get( 'type' ) || 'fusion_builder_row_inner' === module.model.get( 'type' ) ) {
						module.removeRow();
					} else {
						module.removeElement();
					}
				} );

				FusionPageBuilderViewManager.removeView( this.model.get( 'cid' ) );

				this.model.destroy();

				this.remove();

				// If the column is deleted manually
				if ( event ) {

					// Save history state
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.deleted + ' ' + fusionBuilderText.column;

					FusionPageBuilderEvents.trigger( 'fusion-element-removed' );
				}

				// Check for globals.
				setTimeout( FusionPageBuilderApp.checkGlobalParents, 500, parentCID );
			},

			addModule: function( event ) {
				var view,
					$eventTarget,
					$addModuleButton;

				if ( event ) {
					event.preventDefault();
					event.stopPropagation();
				}

				FusionPageBuilderApp.removeContextMenu();

				FusionPageBuilderApp.innerColumn = this.isNested ? 'true' : 'false';
				FusionPageBuilderApp.parentColumnId = this.model.get( 'cid' );

				$eventTarget     = $( event.target );
				$addModuleButton = $eventTarget.is( 'span' ) ? $eventTarget.parent( '.fusion-builder-add-element' ) : $eventTarget;

				if ( ! $addModuleButton.parent().is( event.delegateTarget ) ) {
					return;
				}

				view = new FusionPageBuilder.ModalView( {
					model: this.model,
					collection: this.collection,
					attributes: {
						'data-modal_view': 'element_library'
					},
					view: this
				} );

				$( 'body' ).append( view.render().el );
			},

			cloneColumn: function( event ) {
				var columnAttributes = $.extend( true, {}, this.model.attributes ),
					$thisColumn,
					contentSelector;

				if ( event ) {
					event.preventDefault();
				}

				columnAttributes.created       = 'manually';
				columnAttributes.cid           = FusionPageBuilderViewManager.generateCid();
				columnAttributes.targetElement = this.$el;
				columnAttributes.cloned        = true;

				FusionPageBuilderApp.collection.add( columnAttributes );

				// Parse column elements
				$thisColumn 	= this.$el;
				contentSelector	= this.isNested ? '.fusion_builder_column_element' : '.fusion_builder_column_element:not(.fusion-builder-column-inner .fusion_builder_column_element)';
				$thisColumn.find( contentSelector ).each( function() {
					var $thisModule,
						moduleCID,
						module,
						elementAttributes,
						$thisInnerRow,
						innerRowCID,
						innerRowView;

					// Standard element
					if ( $( this ).hasClass( 'fusion_module_block' ) ) {
						$thisModule = $( this );
						moduleCID = 'undefined' === typeof $thisModule.data( 'cid' ) ? $thisModule.find( '.fusion-builder-data-cid' ).data( 'cid' ) : $thisModule.data( 'cid' );

						// Get model from collection by cid
						module = FusionPageBuilderElements.find( function( model ) {
							return model.get( 'cid' ) === moduleCID;
						} );

						// Clone model attritubes
						elementAttributes         = $.extend( true, {}, module.attributes );
						elementAttributes.created = 'manually';
						elementAttributes.cid     = FusionPageBuilderViewManager.generateCid();
						elementAttributes.parent  = columnAttributes.cid;
						elementAttributes.from    = 'fusion_builder_column';

						FusionPageBuilderApp.collection.add( elementAttributes );

					// Inner row/nested element
					} else if ( $( this ).hasClass( 'fusion_builder_row_inner' ) ) {
						$thisInnerRow = $( this );
						innerRowCID = 'undefined' === typeof $thisInnerRow.data( 'cid' ) ? $thisInnerRow.find( '.fusion-builder-data-cid' ).data( 'cid' ) : $thisInnerRow.data( 'cid' );

						innerRowView = FusionPageBuilderViewManager.getView( innerRowCID );

						// Clone inner row
						if ( 'undefined' !== typeof innerRowView ) {
							innerRowView.cloneNestedRow( '', columnAttributes.cid );
						}
					}

				} );

				// If column is cloned manually
				if ( event ) {

					// Save history state
					fusionHistoryManager.turnOnTracking();
					if ( this.isNested ) {
						window.fusionHistoryState = fusionBuilderText.cloned_nested_columns;
					} else {
						window.fusionHistoryState = fusionBuilderText.cloned + ' ' + fusionBuilderText.column;
					}

					FusionPageBuilderEvents.trigger( 'fusion-element-cloned' );
				}
			},

			columnSizeDialog: function( event ) {
				var leftPosition = this.$el.position().left,
					modelWidth = this.$el.parents( '.fusion-builder-row-container-inner' ).width(),
					columnSizeWidth = 240;

				if ( event ) {
					event.preventDefault();
				}

				if ( this.isNested && modelWidth - leftPosition < columnSizeWidth ) {
					this.$el.find( '.column-sizes' ).css( { left: 'auto', right: '0' } );
				}

				this.$el.find( '.column-sizes' ).toggle();
			},

			onParamsChange: function( model, params ) { // eslint-disable-line no-unused-vars
				this.columnSize();
			},

			columnSize: function( event ) {
				var currentSize = this.model.get( 'layout' ),
					newSize;

				// Retrieve new size.
				if ( event ) {
					newSize = $( event.currentTarget ).attr( 'data-column-size' );
					event.preventDefault();
				} else {
					newSize = this.model.attributes.params.type;
				}

				// Exit early if no there's no new size.
				if ( 'undefined' === typeof newSize ) {
					return;
				}

				// Remove old CSS ClassName if apply.
				this.$el.removeClass( 'fusion-builder-column-' + currentSize );
				this.$el.find( '.column-sizes .column-size' ).removeClass( 'active-size' );
				// Remove old Style if apply.
				this.$el.css( { 'width': '' } );

				// Set new size.
				this.model.set( 'layout', newSize );
				if ( this.isNested ) {
					this.$el.attr( 'data-column-size', newSize );
				}
				// Update params
				this.model.attributes.params.type = newSize;

				this.updateContainerSizeAndLabel( newSize );

				// Make sure popup is closed.
				this.$el.find( '.column-sizes' ).hide();

				// Save history state
				if ( event ) {
					fusionHistoryManager.turnOnTracking();
					window.fusionHistoryState = fusionBuilderText.resized_column + ' ' + newSize;

					FusionPageBuilderEvents.trigger( 'fusion-element-edited' );
				}

			},

			validateColumnSize: function( columnSize ) {
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
			}
        } );
    } );
}( jQuery ) );
