/* eslint-disable no-mixed-operators */
/* global fusionBuilderText */
/* eslint no-unused-vars: 0 */
/* eslint no-useless-concat: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsChartView = FusionPageBuilder.ElementSettingsView.extend( {

			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-chart-template' ).html() ),

			columnOffset: 5,

			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'click .fusion-table-builder-add-column': 'addTableColumn',
					'click .fusion-table-builder-add-row': 'addTableRow',
					'click .fusion-builder-table-delete-column': 'removeTableColumn',
					'click .fusion-builder-table-delete-row': 'removeTableRow',
					'change #chart_type': 'toggleAppearance',
					'click [href="#table"]': 'initColors',
					'click .fusion-tabs-menu': 'initColors'
				} );
			},

			toggleAppearance: function() {
				var chartType        = this.$el.find( '#chart_type' ).val(),
					rows             = this.$el.find( '.fusion-builder-table .fusion-table-row' ).length,
					datasetWrap      = this.$el.find( '.fusion-table-builder-chart' ),
					chartTypeChanged = ! jQuery( datasetWrap ).hasClass( 'fusion-chart-' + chartType );

				if ( ( 'pie' === chartType || 'doughnut' === chartType || 'polarArea' === chartType ) && chartTypeChanged || ( ( 'bar' === chartType || 'horizontalBar' === chartType ) && 1 === rows ) ) {

					// Update colors from 'Y' color pickers.
					this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 input[type="text"]' ).val( this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-2 input[type="text"]' ).val() ).trigger( 'change' );
					this.$el.find( '.fusion-builder-table thead tr:nth-child(3) .th-5 input[type="text"]' ).val( this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-3 input[type="text"]' ).val() ).trigger( 'change' );
					this.$el.find( '.fusion-builder-table thead tr:nth-child(4) .th-5 input[type="text"]' ).val( this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-4 input[type="text"]' ).val() ).trigger( 'change' );

					this.$el.find( '.fusion-builder-table' ).addClass( 'showX' ).removeClass( 'showY' );
				} else if ( chartTypeChanged ) {

					// Update colors from 'X' color pickers.
					this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-2 input[type="text"]' ).val( this.$el.find( '.fusion-builder-table thead tr:nth-child(2) .th-5 input[type="text"]' ).val() ).trigger( 'change' );
					this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-3 input[type="text"]' ).val( this.$el.find( '.fusion-builder-table thead tr:nth-child(3) .th-5 input[type="text"]' ).val() ).trigger( 'change' );
					this.$el.find( '.fusion-builder-table .fusion-table-row.tr-1 .td-4 input[type="text"]' ).val( this.$el.find( '.fusion-builder-table thead tr:nth-child(4) .th-5 input[type="text"]' ).val() ).trigger( 'change' );

					this.$el.find( '.fusion-builder-table' ).removeClass( 'showX' ).addClass( 'showY' );
				}

				// Chart type is changed.
				if ( chartTypeChanged ) {
					jQuery.each( this.$el.find( '#chart_type option' ), function( index, elem ) {
						jQuery( datasetWrap ).removeClass( 'fusion-chart-' + jQuery( elem ).val() );
					} );

					jQuery( datasetWrap ).addClass( 'fusion-chart-' + chartType );
				}

				if ( 'bar' === chartType || 'horizontalBar' === chartType ) {
					this.$el.find( '.fusion-builder-layouts-header-info' ).addClass( 'show-note' );
				} else {
					this.$el.find( '.fusion-builder-layouts-header-info' ).removeClass( 'show-note' );
				}
			},

			initColors: function() {
				$.each( this.$el.find( '.fusion-builder-color-picker-hex-new:not(.color-picker-inited)' ), function() {
					var self = this,
						picker = null;

					picker = $( self ).awbColorPicker( {
						hide: false,
						globals: false
					} );

					$( self ).addClass( 'color-picker-inited' );
				} );
			},

			removeTableRow: function( event ) {
				var rowID;

				if ( 2 > this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr' ).length ) {
					return;
				}

				if ( event ) {
					event.preventDefault();

					rowID = $( event.currentTarget ).data( 'row-id' );

					$( event.currentTarget ).parents( 'tr' ).remove();
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
				}
			},

			addTableColumn: function( event ) {
				var columnID,
					columnIds;

				if ( event ) {
					event.preventDefault();
				}

				columnID = this.$el.find( '.fusion-table-builder .fusion-builder-table tr:first-child td' ).length + 1;

				// Add th: X axis label.
				this.$el.find( '.fusion-table-builder .fusion-builder-table thead tr:first-child' ).append( '<th class="th-' + columnID + '" data-th-id="' + columnID + '"><div class="fusion-builder-table-hold"><div class="fusion-builder-table-column-options"><span class="fa fusiona-trash-o fusion-builder-table-delete-column" title="' + fusionBuilderText.delete_column + '" data-column-id="' + columnID + '" /></div></div><input type="text" placeholder="X Axis L' + ( columnID - ( this.columnOffset - 1 ) ) + '" value="" /></th>' );

				// Add th: legend text color.
				this.$el.find( '.fusion-table-builder .fusion-builder-table thead tr:nth-child(2)' ).append( '<th class="th-' + columnID + '" data-th-id="' + columnID + '"><div class="option-field"><input type="text" value="#ffffff" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>' );

				// Add th: background color.
				this.$el.find( '.fusion-table-builder .fusion-builder-table thead tr:nth-child(3)' ).append( '<th class="th-' + columnID + '" data-th-id="' + columnID + '"><div class="option-field"><input type="text" value="" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>' );

				// Add th: border color.
				this.$el.find( '.fusion-table-builder .fusion-builder-table thead tr:nth-child(4)' ).append( '<th class="th-' + columnID + '" data-th-id="' + columnID + '"><div class="option-field"><input type="text" value="" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></th>' );

				// Add td
				this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr' ).each( function() {

					$( this ).append( '<td class="td-' + columnID + '" data-td-id="' + columnID + '" ><input type="text" placeholder="' + fusionBuilderText.enter_value + '" value="" /></td>' );
				} );

				this.initColors();
			},

			addTableRow: function() {
				var columns   = 0,
					td        = '',
					lastRowID = ( 'undefined' !== typeof this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:last-child' ).data( 'tr-id' ) ) ? this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:last-child' ).data( 'tr-id' ) : 0,
					newRowID  = lastRowID + 1,
					i;

				columns = this.$el.find( '.fusion-table-builder .fusion-builder-table tbody tr:first-child td' ).length;

				td += '<td class="td-1" data-td-id="1" ><input type="text" placeholder="' + fusionBuilderText.legend_label + '" value="" /><span class="fa fusiona-trash-o fusion-builder-table-delete-row" title="' + fusionBuilderText.delete_row + '" data-row-id="' + newRowID + '" /></td>';
				td += '<td class="td-2" data-td-id="2" ><div class="option-field"><input type="text" value="#ffffff" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>';
				td += '<td class="td-3" data-td-id="2" ><div class="option-field"><input type="text" value="" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>';
				td += '<td class="td-4" data-td-id="3" ><div class="option-field"><input type="text" value="" class="fusion-builder-color-picker-hex-new color-picker" data-alpha="true" /></div></td>';

				for ( i = this.columnOffset; i <= columns; i++ ) {
					td += '<td class="td-' + i + '" data-td-id="' + i + '" ><input type="text" placeholder="' + fusionBuilderText.enter_value + '" value="" /></td>';
				}

				// Add tds
				this.$el.find( '.fusion-table-builder .fusion-builder-table tbody' ).append( '<tr class="fusion-table-row tr-' + newRowID + '" data-tr-id="' + newRowID + '">' + td + '</tr>' );

				this.initColors();

				this.toggleAppearance();

			}

		} );

	} );

}( jQuery ) );
