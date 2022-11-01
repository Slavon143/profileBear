/* eslint-disable no-mixed-operators */
/* global fusionBuilderText */
/* eslint no-useless-concat: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsChartTableView = FusionPageBuilder.ElementSettingsView.extend( {

			className: FusionPageBuilder.ElementSettingsView.prototype.className + ' fusion-builder-settings-chart-table-dialog',

			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-chart-table-template' ).html() ),

			columnOffset: 5,

			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'click .fusion-table-builder-add-column': 'addTableColumn',
					'click .fusion-table-builder-add-row': 'addTableRow',
					'click .fusion-builder-table-delete-column': 'removeTableColumn',
					'click .fusion-builder-table-delete-row': 'removeTableRow',
					'click .fusion-builder-open-colorpicker': 'openColorPicker',
					'click': 'handleCloseAllPickers',
					'change .fusion-builder-color-picker-hex': 'updateColorPreview',
					'fusion-change .fusion-builder-color-picker-hex': 'updateColorPreview'
				} );
			},

			openColorPicker: function( event ) {
				var $parent = jQuery( event.target ).parent( '.fusion-builder-option' ),
					$dialog = jQuery( event.target ).closest( '.ui-dialog' );

				event.preventDefault();

				if ( 0 < this.$el.find( '.fusion-color-picker-opened' ).length ) {
					return;
				}

				if ( this.$el.find( '.option-field.fusion-builder-option-container' ).width() > $dialog.offset().left + $dialog.width() - jQuery( event.target ).offset().left ) {
					$parent.addClass( 'fusion-color-picker-flip' );
				}

				setTimeout( function() {
					var $picker,
						$pickerInstance;

					$parent.find( '.wp-color-result' ).trigger( 'click' );
					$parent.addClass( 'fusion-color-picker-opened' );

					// Annoying Iris visual bug, make sure that the initial draggable button is within the parent.
					$picker = $parent.find( 'input.awb-color-picker' );
					$pickerInstance = $picker.awbColorPicker( 'instance' );
					if ( 'object' === typeof $pickerInstance && 'function' === typeof $pickerInstance.fixIrisDragButtonOutsideDragArea ) {
						$pickerInstance.fixIrisDragButtonOutsideDragArea();
					}
				}, 10 );
			},

			handleCloseAllPickers: function( event ) {
				var openedPickers = this.$el.find( '.fusion-builder-option.fusion-color-picker-opened' ),
					closeColorPicker = Object.getPrototypeOf( this ).closeColorPicker;

				event.preventDefault();

				// Filter all open clickers where the click doesn't came from within.
				openedPickers = openedPickers.filter( function() {
					return ! jQuery( this ).has( event.target ).length;
				} );

				openedPickers.each( function() {
					closeColorPicker( jQuery( this ) );
				} );
			},

			closeColorPicker: function( $picker ) {
				var currentColor = $picker.find( '.fusion-builder-color-picker-hex' ).val();

				if ( '' === currentColor ) {
					currentColor = 'rgba(0,0,0,0)';
					$picker.find( '.fusion-builder-color-picker-hex' ).val( currentColor );
				}

				$picker.find( '.fusion-builder-open-colorpicker' ).css( 'background-color', currentColor );
				$picker.removeClass( 'fusion-color-picker-opened' );
			},

			updateColorPreview: function( event ) {
				jQuery( event.currentTarget ).closest( '.fusion-builder-option' ).find( '.fusion-builder-open-colorpicker' ).css( 'background-color', jQuery( event.currentTarget ).val() );
			},

			initColors: function() {
				var self = this;

				jQuery.each( self.$el.find( '.fusion-builder-color-picker-hex-new' ), function() {
					jQuery( this ).awbColorPicker( {
						allowToggle: false,
						change: function() {
							self.updateTablePreview();
						}
					} );
					jQuery( this ).addClass( 'fusion-builder-color-picker-hex' ).removeClass( 'fusion-builder-color-picker-hex-new' );
				} );
			},

			toggleAppearance: function() {
				var chartType   = this.model.attributes.params.chart_type,
					rows        = this.$el.find( '.fusion-builder-table .fusion-table-row' ).length,
					datasetWrap = this.$el.find( '.fusion-table-builder-chart' ),
					chartTypeChanged = ! jQuery( datasetWrap ).hasClass( 'fusion-chart-' + chartType ),
					updateColorPickers = [],
					updateColors = [],
					i;

				if ( ( 'pie' === chartType || 'doughnut' === chartType || 'polarArea' === chartType ) && chartTypeChanged || ( ( 'bar' === chartType || 'horizontalBar' === chartType ) && 1 === rows ) ) {

					// Update colors from 'Y' color pickers.
					updateColorPickers = [
						this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 .colorpickeralpha:first-child input[type="text"]:not(.color-picker-placeholder)' ),
						this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 .colorpickeralpha:nth-child(2) input[type="text"]:not(.color-picker-placeholder)' ),
						this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 .colorpickeralpha:nth-child(3) input[type="text"]:not(.color-picker-placeholder)' )
					];

					updateColors = [
						this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-2 input[type="text"]' ).val(),
						this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-3 input[type="text"]' ).val(),
						this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-4 input[type="text"]' ).val()
					];

					this.$el.find( '.fusion-builder-table' ).addClass( 'showX' ).removeClass( 'showY' );
				} else if ( chartTypeChanged ) {

					// Update colors from 'X' color pickers.
					updateColorPickers = [
						this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-2 input[type="text"]' ),
						this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-3 input[type="text"]' ),
						this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-4 input[type="text"]' )
					];

					updateColors = [
						this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 .colorpickeralpha:first-child input[type="text"]:not(.color-picker-placeholder)' ).val(),
						this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 .colorpickeralpha:nth-child(2) input[type="text"]:not(.color-picker-placeholder)' ).val(),
						this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 .colorpickeralpha:nth-child(3) input[type="text"]:not(.color-picker-placeholder)' ).val()
					];

					this.$el.find( '.fusion-builder-table' ).removeClass( 'showX' ).addClass( 'showY' );
				}

				for ( i = 0; i < updateColorPickers.length; i++ ) {

					// Update color pickers.
					jQuery( updateColorPickers[ i ] ).val( updateColors[ i ] ).trigger( 'change' );
				}

				// Chart type is changed.
				if ( chartTypeChanged ) {
					jQuery.each( this.$el.find( '#chart_type option' ), function( index, elem ) {
						jQuery( datasetWrap ).removeClass( 'fusion-chart-' + jQuery( elem ).val() );
					} );

					jQuery( datasetWrap ).addClass( 'fusion-chart-' + chartType );
				}

				if ( 0 < this.$el.find( '.fusion-color-picker-opened' ).length ) {
					this.$el.find( '.fusion-color-picker-opened' ).removeClass( '.fusion-color-picker-opened' );
				}

				if ( 'bar' === chartType || 'horizontalBar' === chartType ) {
					this.$el.find( '.fusion-builder-layouts-header-info' ).addClass( 'show-note' );
				} else {
					this.$el.find( '.fusion-builder-layouts-header-info' ).removeClass( 'show-note' );
				}
			},

			removeTableRow: function( event ) {
				if ( 2 > this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr' ).length ) {
					return;
				}

				if ( event ) {
					event.preventDefault();
					jQuery( event.currentTarget ).parents( 'tr' ).remove();
				}

				this.toggleAppearance();
			},

			removeTableColumn: function( event ) {
				var columnID;

				if ( event ) {
					event.preventDefault();

					columnID = $( event.currentTarget ).parents( 'th' ).data( 'th-id' );

					this.$el.find( 'td[data-td-id="' + columnID + '"]' ).remove();
					this.$el.find( 'th[data-th-id="' + columnID + '"]' ).remove();

					// Trigger change in order to update table preview.
					this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:first-child .td-1 input' ).trigger( 'change' );
				}
			},

			addTableColumn: function( event ) {
				var columnID;

				if ( event ) {
					event.preventDefault();
				}

				columnID = this.$el.find( '.fusion-table-builder .fusion-builder-table tr:first-child td' ).length + 1;

				// Add th: X axis label.
				this.$el.find( '.fusion-table-builder .fusion-builder-table thead tr:first-child' ).append( '<th class="th-' + columnID + ' fusion-builder-option" data-th-id="' + columnID + '" data-option-id="fake-chart-option"><div class="fusion-builder-table-hold"><div class="fusion-builder-table-column-options"><span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="' + fusionBuilderText.delete_column + '" data-column-id="' + columnID + '" /></div></div><input type="text" placeholder="X Axis L' + ( columnID - ( this.columnOffset - 1 ) ) + '" value="" class="fusion-debounce-change" /></th>' );

				// Add th: legend text color.
				this.$el.find( '.fusion-table-builder .fusion-builder-table thead tr:nth-child(2)' ).append( '<th class="th-' + columnID + '" data-th-id="' + columnID + '"><div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">' + this.getColorPickerMarkup( fusionBuilderText.legend_text_color ) + '</div><div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">' + this.getColorPickerMarkup( fusionBuilderText.background_color ) + '</div><div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">' + this.getColorPickerMarkup( fusionBuilderText.border_color ) + '</div></th>' );

				// Add td
				this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr' ).each( function() {

					$( this ).append( '<td class="td-' + columnID + ' fusion-builder-option" data-td-id="' + columnID + '" data-option-id="fake-chart-option"><input type="text" placeholder="' + fusionBuilderText.enter_value + '" value="" class="fusion-debounce-change" /></td>' );
				} );

				this.initColors();

				// Trigger change in order to update table preview.
				this.updateTablePreview();
			},

			addTableRow: function() {
				var columns   = 0,
					td        = '',
					lastRowID = ( 'undefined' !== typeof this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:last-child' ).data( 'tr-id' ) ) ? this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:last-child' ).data( 'tr-id' ) : 0,
					newRowID  = lastRowID + 1,
					i;

				columns = this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:first-child td' ).length;

				td += '<td class="td-1 fusion-builder-option" data-td-id="1" data-option-id="fake-chart-option"><input type="text" placeholder="' + fusionBuilderText.legend_label + '" value="" class="fusion-debounce-change" /><span class="fa fusiona-trash-o fusion-builder-table-delete-row" title="' + fusionBuilderText.delete_row + '" data-row-id="' + newRowID + '" /></td>';
				td += '<td class="td-2" data-td-id="2"><div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">' + this.getColorPickerMarkup( fusionBuilderText.legend_text_color ) + '</div></td>';
				td += '<td class="td-3" data-td-id="2"><div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">' + this.getColorPickerMarkup( fusionBuilderText.background_color ) + '</div></td>';
				td += '<td class="td-4" data-td-id="3"><div class="fusion-builder-option colorpickeralpha" data-option-id="fake-chart-option">' + this.getColorPickerMarkup( fusionBuilderText.border_color ) + '</div></td>';

				for ( i = this.columnOffset; i <= columns; i++ ) {
					td += '<td class="td-' + i + ' fusion-builder-option" data-td-id="' + i + '" data-option-id="fake-chart-option"><input type="text" placeholder="' + fusionBuilderText.enter_value + '" value="" class="fusion-debounce-change" /></td>';
				}

				// Add tds
				this.$el.find( '.fusion-table-builder .fusion-builder-table tbody' ).append( '<tr class="fusion-table-row tr-' + newRowID + '" data-tr-id="' + newRowID + '">' + td + '</tr>' );

				this.initColors();

				this.toggleAppearance();

			},

			getColorPickerMarkup: function( label, defaultColor ) {
				if ( 'undefined' === typeof defaultColor ) {
					defaultColor = '';
				}

				return '<a href="#" class="fusion-builder-open-colorpicker" style="background-color: ' + defaultColor + ';"><span class="fusiona-color-dropper" aria-label="' + label + '"></span></a><div class="option-field fusion-builder-option-container"><span class="fusion-builder-colorpicker-title">' + label + '</span><div class="fusion-colorpicker-container"><input type="text" value="' + defaultColor + '" class="fusion-builder-color-picker-hex-new color-picker fusion-always-update" data-alpha="true" data-globals="false" data-hide="false" /></div></div>';
			},

			updateTablePreview: function() {
				this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:first-child .td-1 input' ).trigger( 'change' );
			}

		} );

	} );

}( jQuery ) );
