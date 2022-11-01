/* global FusionPageBuilderApp */
( function( $ ) {

	$( document ).ready( function() {

		// Chart tables. Build chart shortcode.
		FusionPageBuilderApp.chartShortcodeFilter = function( attributes, view ) {

			var shortcode      = '',
				table            = view.$( '.fusion-table-builder' ),
				labels           = [],
				bgColors         = [],
				borderColors     = [],
				legendTextColors = [];

			// Table head (X axis labels).
			table.find( 'thead tr:first-child th' ).each( function( i ) {
				var val = $( this ).find( 'input' ).val();

				if ( 3 < i ) {
					labels.push( val );
				}
			} );

			attributes.params.x_axis_labels = labels.join( '|' );

			// Table head (label text colors).
			table.find( 'thead tr:nth-child(2) th' ).each( function( i ) {
				var val = $( this ).find( 'input' ).val();

				if ( 3 < i ) {
					legendTextColors.push( val );
				}
			} );

			attributes.params.legend_text_colors = legendTextColors.join( '|' );

			// Table head (bg colors).
			table.find( 'thead tr:nth-child(3) th' ).each( function( i ) {
				var val = $( this ).find( 'input' ).val();

				if ( 3 < i ) {
					bgColors.push( val );
				}
			} );

			attributes.params.bg_colors = bgColors.join( '|' );

			// Table head (border colors).
			table.find( 'thead tr:nth-child(4) th' ).each( function( i ) {
				var val = $( this ).find( 'input' ).val();

				if ( 3 < i ) {
					borderColors.push( val );
				}
			} );

			attributes.params.border_colors = borderColors.join( '|' );

			// Border size.
			attributes.params.chart_border_size = '' !== table.find( '#chart_border_size' ).val() ? parseInt( table.find( '#chart_border_size' ).val(), 10 ) : '';

			// Chart background color.
			attributes.params.chart_bg_color = table.find( '#chart_bg_color' ).val();

			// Chart paddings.
			attributes.params.padding_top    = table.find( '#padding_top' ).val();
			attributes.params.padding_right  = table.find( '#padding_right' ).val();
			attributes.params.padding_bottom = table.find( '#padding_bottom' ).val();
			attributes.params.padding_left   = table.find( '#padding_left' ).val();

			// Chart axis text color.
			attributes.params.chart_axis_text_color = table.find( '#chart_axis_text_color' ).val();

			// Chart gridline color.
			attributes.params.chart_gridline_color = table.find( '#chart_gridline_color' ).val();

			// Table body (each row is data set).
			table.find( 'tbody tr' ).each( function() {
				var $thisTr = $( this ),
					values  = [];

				shortcode += '[fusion_chart_dataset';

				// Table rows (data set title, colors, values).
				$thisTr.find( 'td' ).each( function( i ) {
					var $thisRow = $( this ),
						val  = $thisRow.find( 'input' ).val();

					if ( 0 === i ) {
						shortcode += ' title="' + val + '"';
					} else if ( 1 === i ) {
						shortcode += ' legend_text_color="' + val + '"';
					} else if ( 2 === i ) {
						shortcode += ' background_color="' + val + '"';
					} else if ( 3 === i ) {
						shortcode += ' border_color="' + val + '"';
					} else {
						values.push( val );
					}
				} );

				shortcode += ' values="' + values.join( '|' ) + '"]';
				shortcode += '[/fusion_chart_dataset]';

			} );

			attributes.params.element_content = shortcode;
			return attributes;
		};

	} );

}( jQuery ) );
