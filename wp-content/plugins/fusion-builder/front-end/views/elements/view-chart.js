/* global FusionPageBuilderApp, fusionBuilderText */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Chart View.
		FusionPageBuilder.fusion_chart = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Chart Datasets.
			 */
			chartDatasets: [],

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				this.$el.find( 'canvas' ).replaceWith( '<canvas width="100" height="100"></canvas>' );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {

				// TODO: save DOM and apply instead of generating
				this.generateChildElements();

				this._refreshJs();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.chartDatasets = FusionPageBuilderApp.findShortcodeMatches( atts.params.element_content, 'fusion_chart_dataset' );

				attributes.showPlaceholder = 0 === this.chartDatasets.length ? true : false;

				if ( ! attributes.showPlaceholder ) {

					// Validate values.
					this.validateValues( atts.values );
				} else {

					// Set placeholder values;
					atts.values.type                                = 'bar';
					this.model.attributes.params.type               = 'bar';
					atts.values.x_axis_labels                       = 'Val 1|Val 2|Val 3';
					this.model.attributes.params.x_axis_labels      = 'Val 1|Val 2|Val 3';
					atts.values.legend_text_colors                  = '#ffffff|#ffffff|#ffffff';
					this.model.attributes.params.legend_text_colors = '#ffffff|#ffffff|#ffffff';
					atts.values.bg_colors                           = '#03a9f4|#8bc34a|#ff9800';
					this.model.attributes.params.bg_colors          = '#03a9f4|#8bc34a|#ff9800';
					atts.values.border_colors                       = '#03a9f4|#8bc34a|#ff9800';
					this.model.attributes.params.border_colors      = '#03a9f4|#8bc34a|#ff9800';

					this.model.attributes.params.element_content = '[fusion_chart_dataset title="Data Set 1" legend_text_color="#ffffff" background_color="#00bcd4" border_color="#00bcd4" values="5|7|9"][/fusion_chart_dataset]';
				}

				// Create attribute objects.
				attributes.chartShortcode      = this.buildChartAttr( atts.values );
				attributes.styles              = this.buildStyles( atts.values );
				attributes.title               = atts.values.title;
				attributes.chartLegendPosition = atts.values.chart_legend_position;

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.chart_padding = {
					top: 'undefined' !== typeof values.padding_top && '' !== values.padding_top ? values.padding_top : 0,
					right: 'undefined' !== typeof values.padding_right && '' !== values.padding_right ? values.padding_right : 0,
					bottom: 'undefined' !== typeof values.padding_bottom && '' !== values.padding_bottom ? values.padding_bottom : 0,
					left: 'undefined' !== typeof values.padding_left && '' !== values.padding_left ? values.padding_left : 0
				};

				if ( '' === values.chart_type ) {
					values.chart_type = 'bar';
				}

				// validate bg_colors.
				if ( values.bg_colors ) {
					values.bg_colors = this.validateGlobalColors( values.bg_colors );
				}

				// validate border_colors.
				if ( values.border_colors ) {
					values.border_colors = this.validateGlobalColors( values.border_colors );
				}

				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Validate global color variable.
			 *
			 * @since 3.6
			 * @param {String} value - The value.
			 * @return {Object|String}
			 */
			validateGlobalColors: function( value ) {
				var colors    = value.split( '|' ),
					newColors = [];

				if ( colors ) {
					_.each( colors, function( v ) {
						var newValue = '' !== v ? jQuery.AWB_Color( v ).toRgbaString() : '';
						newColors.push( newValue );
					} );
					return newColors.join( '|' );
				}
				return value;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildChartAttr: function( values ) {
				var chartShortcode = _.fusionVisibilityAtts(
					values.hide_on_mobile, {
						id: 'fusion-chart-cid' + this.model.get( 'cid' ),
						class: 'fusion-chart fusion-child-element',
						style: ''
					}
				);

				if ( values.chart_type && -1 !== [ 'bar', 'horizontalBar',  'line', 'pie', 'doughnut', 'radar', 'polarArea' ].indexOf( values.chart_type ) ) {
					chartShortcode[ 'data-type' ] = values.chart_type;
				} else {
					chartShortcode[ 'data-type' ] = this.$el.find( '#' + chartShortcode.id ).data( 'type' );
				}

				if ( '' !== values.chart_legend_position && 'off' !== values.chart_legend_position ) {
					chartShortcode[ 'class' ] += ' legend-' + values.chart_legend_position;

					chartShortcode[ 'data-chart_legend_position' ] = values.chart_legend_position;
				}

				if ( '' !== values.x_axis_labels ) {
					chartShortcode[ 'data-x_axis_labels' ] = values.x_axis_labels;
				}

				if ( '' !== values.x_axis_label ) {
					chartShortcode[ 'data-x_axis_label' ] = values.x_axis_label;
				}

				if ( '' !== values.y_axis_label ) {
					chartShortcode[ 'data-y_axis_label' ] = values.y_axis_label;
				}

				if ( '' !== values.show_tooltips ) {
					chartShortcode[ 'data-show_tooltips' ] = values.show_tooltips;
				}

				if ( '' !== values.bg_colors ) {
					chartShortcode[ 'data-bg_colors' ] = values.bg_colors;
				}

				if ( '' !== values.border_colors ) {
					chartShortcode[ 'data-border_colors' ] = values.border_colors;
				}

				if ( '' !== values.legend_labels ) {
					chartShortcode[ 'data-legend_labels' ] = values.legend_labels;
				}

				if ( '' !== values.chart_border_size ) {
					chartShortcode[ 'data-border_size' ] = values.chart_border_size;
				}

				if ( '' !== values.chart_border_type ) {
					chartShortcode[ 'data-border_type' ] = values.chart_border_type;
				}

				if ( '' !== values.chart_fill ) {
					chartShortcode[ 'data-chart_fill' ] = values.chart_fill;
				}

				if ( '' !== values.chart_point_style ) {
					chartShortcode[ 'data-chart_point_style' ] = values.chart_point_style;
				}

				if ( '' !== values.chart_point_size ) {
					chartShortcode[ 'data-chart_point_size' ] = values.chart_point_size;
				}

				if ( '' !== values.chart_point_bg_color ) {
					chartShortcode[ 'data-chart_point_bg_color' ] = jQuery.AWB_Color( values.chart_point_bg_color ).toRgbaString();
				}

				if ( '' !== values.chart_point_border_color ) {
					chartShortcode[ 'data-chart_point_border_color' ] = jQuery.AWB_Color( values.chart_point_border_color ).toRgbaString();
				}

				if ( '' !== values.chart_axis_text_color ) {
					chartShortcode[ 'data-chart_axis_text_color' ] = jQuery.AWB_Color( values.chart_axis_text_color ).toRgbaString();
				}

				if ( '' !== values.chart_gridline_color ) {
					chartShortcode[ 'data-chart_gridline_color' ] = jQuery.AWB_Color( values.chart_gridline_color ).toRgbaString();
				}

				if ( '' !== values.margin_top ) {
					chartShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					chartShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					chartShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					chartShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					chartShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					chartShortcode.id = values.id;
				}

				return chartShortcode;
			},

			/**
			 * Builds the styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildStyles: function( values ) {
				var styles                = '',
					childrenCount         = 0,
					childLegendTextColors = [],
					cid                   = this.model.get( 'cid' ),
					colors,
					colorCount,
					i,
					chartDatasetElement,
					chartDatasetAtts;

				if ( '' !== values.chart_bg_color ) {
					styles += '#fusion-chart-cid' + cid + '{background-color: ' + values.chart_bg_color + ';}';
				}

				if ( values.chart_padding && 'object' === typeof values.chart_padding ) {
					styles += '#fusion-chart-cid' + cid + '{padding: ' + values.chart_padding.top + ' ' + values.chart_padding.right + ' ' + values.chart_padding.bottom + ' ' + values.chart_padding.left + ';}';
				}

				if ( 'undefined' !== typeof this.model && 'undefined' !== typeof this.model.children ) {
					childrenCount = this.model.children.length;

					if ( childrenCount ) {
						_.each( this.model.children.models, function( child, key ) {
							childLegendTextColors[ key ] = child.attributes.params.legend_text_color;
						} );
					} else if ( 'undefined' !== typeof this.model.attributes.params.element_content ) {

						// Render on page load, children are not generated yet.

						_.each( this.chartDatasets, function( chartDataset ) {
							chartDatasetElement = chartDataset.match( FusionPageBuilderApp.regExpShortcode( 'fusion_chart_dataset' ) );
							chartDatasetAtts    = '' !== chartDatasetElement[ 3 ] ? window.wp.shortcode.attrs( chartDatasetElement[ 3 ] ) : '';

							childLegendTextColors.push( chartDatasetAtts.named.legend_text_color );
						} );

						childrenCount = this.chartDatasets.length;
					}
				}

				if ( '' !== values.legend_text_colors ) {
					if ( 'pie' === values.chart_type || 'doughnut' === values.chart_type || 'polarArea' === values.chart_type || ( ( 'bar' === values.chart_type || 'horizontalBar' === values.chart_type ) && 1 === childrenCount ) ) {
						colors = values.legend_text_colors.split( '|' );
					} else {
						colors = childLegendTextColors;
					}

					colorCount = colors.length;
					for ( i = 0; i < colorCount; i++ ) {
						if ( '' !== colors[ i ] ) {
							styles += '#fusion-chart-cid' + cid + ' .fusion-chart-legend-wrap li:nth-child(' + ( i + 1 ) + ') span{color: ' + colors[ i ] + ';}';
						}
					}
				}

				return styles;
			}

		} );
	} );

	_.extend( FusionPageBuilder.Callback.prototype, {
		chartShortcodeFilter: function( name, value, args, view ) {

			var shortcode        = '',
				table            = false,
				labels           = [],
				bgColors         = [],
				borderColors     = [],
				legendTextColors = [],
				params           = {},
				changes          = [];

			if ( ! view ) {
				return;
			}

			table  = jQuery( '[data-cid="' + view.model.get( 'cid' ) + '"] .fusion-table-builder' );
			params = jQuery.extend( true, {}, view.model.get( 'params' ) );

			// Table head (X axis labels).
			table.find( 'thead tr:first-child th' ).each( function( i ) {
				var val = jQuery( this ).find( 'input' ).val();

				if ( 1 < i ) {
					labels.push( val );
				}
			} );

			if ( params.x_axis_labels !== labels.join( '|' ) ) {
				changes.push( { id: 'x_axis_labels', value: labels.join( '|' ), label: fusionBuilderText.x_axis_label } );
			}

			// Table head (label text colors).
			table.find( 'thead tr:nth-child(2) th' ).each( function( i ) {

				if ( 3 < i ) {
					legendTextColors.push( jQuery( this ).find( '.fusion-builder-option:first-child input' ).val() );
					bgColors.push( jQuery( this ).find( '.fusion-builder-option:nth-child(2) input' ).val() );
					borderColors.push( jQuery( this ).find( '.fusion-builder-option:nth-child(3) input' ).val() );
				}
			} );

			if ( 'undefined' !== typeof params.legend_text_colors && params.legend_text_colors !== legendTextColors.join( '|' ) ) {
				changes.push( { id: 'legend_text_colors', value: legendTextColors.join( '|' ), label: fusionBuilderText.legend_text_color } );
			}
			if ( 'undefined' !== typeof params.bg_colors && params.bg_colors !== bgColors.join( '|' ) ) {
				changes.push( { id: 'bg_colors', value: bgColors.join( '|' ), label: fusionBuilderText.background_color } );
			}
			if ( 'undefined' !== typeof params.border_colors && params.border_colors !== borderColors.join( '|' ) ) {
				changes.push( { id: 'border_colors', value: borderColors.join( '|' ), label: fusionBuilderText.border_color } );
			}

			// Table body (each row is data set).
			table.find( 'tbody tr' ).each( function() {
				var $thisTr = jQuery( this ),
					values  = [];

				shortcode += '[fusion_chart_dataset';

				// Table rows (data set title, colors, values).
				$thisTr.find( 'td' ).each( function( i ) {
					var $thisRow = jQuery( this ),
						val      = $thisRow.find( 'input' ).val();

					switch ( i ) {
					case 0:
						shortcode += ' title="' + val + '"';
						break;
					case 1:
						shortcode += ' legend_text_color="' + val + '"';
						break;
					case 2:
						shortcode += ' background_color="' + val + '"';
						break;
					case 3:
						shortcode += ' border_color="' + val + '"';
						break;
					default:
						values.push( val );
					}
				} );

				shortcode += ' values="' + values.join( '|' ) + '" /]';
			} );

			if ( params.element_content.trim() !== shortcode.trim() ) {
				changes.push( { id: 'element_content', value: shortcode, label: fusionBuilderText.chart_dataset } );
			}

			_.each( changes, function( change ) {
				view.changeParam( change.id, change.value, change.label );
			} );

			return {
				render: true
			};
		}
	} );
}( jQuery ) );
