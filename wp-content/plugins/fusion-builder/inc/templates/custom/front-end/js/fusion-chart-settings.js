/* global fusionBuilderText, FusionPageBuilderApp, fusionAllElements, FusionApp, FusionEvents */
/* eslint no-shadow: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsChartView = FusionPageBuilder.ElementSettingsView.extend( {

			events: function() {
				return _.extend( {}, FusionPageBuilder.ElementSettingsView.prototype.events, {
					'click .fusion-chart-edit-table': 'editTable'
				} );
			},

			editTable: function( event ) {
				var viewSettings = {
						model: this.model,
						collection: this.collection,
						attributes: {
							settingsView: this
						}
					},
					modalView,
					dialogTitle = 'Edit Chart Data Table';

				event.preventDefault();

				modalView = new FusionPageBuilder.ModuleSettingsChartTableView( viewSettings );

				jQuery( modalView.render().el ).dialog( {
					title: dialogTitle,
					width: FusionApp.dialog.dialogData.width,
					height: FusionApp.dialog.dialogData.height,
					position: FusionApp.dialog.dialogData.position,
					dialogClass: 'fusion-builder-dialog fusion-builder-settings-dialog',
					minWidth: 360,

					open: function( event ) {
						var $dialogContent = jQuery( event.target ),
							$parentDialog = jQuery( $dialogContent ).closest( '.ui-dialog' ),
							dialogContentWidth = $dialogContent.find( '.fusion-builder-table' ).width() + 90;

						if ( dialogContentWidth > jQuery( window ).width() ) {
							dialogContentWidth = jQuery( window ).width() - 50;
						}

						$parentDialog.width( dialogContentWidth + 'px' );
						$parentDialog.css( 'left', ( jQuery( window ).width() - dialogContentWidth ) / 2 );

						// On start can sometimes be laggy/late.
						FusionApp.dialog.addResizingHoverEvent();

						$dialogContent.find( '.fusion-builder-section-name' ).blur();

						FusionPageBuilderApp.$el.addClass( 'fusion-builder-no-ui' );

						jQuery( '.ui-dialog' ).not( $dialogContent.closest( '.ui-dialog' ) ).hide();
					},

					dragStart: function( event ) {

						// Used to close any open drop-downs in TinyMce.
						jQuery( event.target ).trigger( 'click' );
					},

					beforeClose: function() {
						FusionEvents.trigger( 'fusion-content-changed' );
						FusionPageBuilderApp.$el.removeClass( 'fusion-builder-no-ui' );
						jQuery( '.ui-dialog:not( .fusion-video-dialog )' ).first().show();
						modalView.saveSettings();
					},

					resizeStart: function() {
						FusionApp.dialog.addResizingClasses();
					},

					resizeStop: function() {
						FusionApp.dialog.removeResizingClasses();
					}

				} );

			},

			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-chart-template' ).html() ),

			filterAttributes: function( attributes ) {
				attributes.frontOptions = [
					fusionAllElements[ attributes.element_type ].params.chart_type,
					fusionAllElements[ attributes.element_type ].params.title
				];

				attributes.chartOptions = {
					chart_bg_color: {
						heading: fusionBuilderText.chart_bg_color_title,
						description: fusionBuilderText.chart_bg_color_desc,
						param_name: 'chart_bg_color',
						type: 'colorpickeralpha',
						default: FusionApp.settings.chart_bg_color
					},
					chart_padding: {
						heading: fusionBuilderText.chart_padding_title,
						description: fusionBuilderText.chart_padding_desc,
						param_name: 'chart_padding',
						type: 'dimension',
						value: {
							padding_top: '',
							padding_right: '',
							padding_bottom: '',
							padding_left: ''
						}
					},
					chart_border_size: {
						heading: fusionBuilderText.chart_border_size_heading,
						description: fusionBuilderText.chart_border_size_desc,
						param_name: 'chart_border_size',
						type: 'range',
						min: 0,
						max: 50,
						step: 1,
						value: 0
					},
					chart_axis_text_color: {
						heading: fusionBuilderText.chart_axis_text_color_title,
						description: fusionBuilderText.chart_axis_text_color_desc,
						param_name: 'chart_axis_text_color',
						type: 'colorpickeralpha',
						default: FusionApp.settings.chart_axis_text_color
					},
					chart_gridline_color: {
						heading: fusionBuilderText.chart_gridline_color_title,
						description: fusionBuilderText.chart_gridline_color_desc,
						param_name: 'chart_gridline_color',
						type: 'colorpickeralpha',
						default: FusionApp.settings.chart_gridline_color
					}
				};

				attributes.chartOptions = jQuery.extend( true, attributes.chartOptions, fusionAllElements[ attributes.element_type ].params );

				delete attributes.chartOptions.chart_type;
				delete attributes.chartOptions.title;

				return attributes;
			}

		} );

	} );

}( jQuery ) );
