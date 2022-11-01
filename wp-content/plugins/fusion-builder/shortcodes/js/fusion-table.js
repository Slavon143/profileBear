/* global FusionPageBuilderApp, fusionBuilderGetContent */
/* eslint no-unused-vars: off */
/* eslint no-loop-func: off */
jQuery( document ).ready( function() {

	jQuery( 'body' ).on( 'change', '#fusion_table_type, #sliderfusion_table_rows, #sliderfusion_table_columns', function() {
		var types = [
				'',
				'table-1',
				'table-2'
			],
			tableOptions = jQuery( this ).closest( '.fusion_table' ),
			type         = tableOptions.find( '#fusion_table_type' ).val(),
			rows         = tableOptions.find( '#sliderfusion_table_rows' ).val(),
			columns      = tableOptions.find( '#sliderfusion_table_columns' ).val(),
			oldContent, newContent,
			i, j, tableDOM;

		if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
			oldContent = fusionBuilderGetContent( 'generator_element_content' );
		} else {
			oldContent = fusionBuilderGetContent( 'element_content' );
		}

		tableDOM = jQuery.parseHTML( oldContent.trim() );
		tableDOM = generateTable( tableDOM, tableOptions );

		newContent = jQuery( tableDOM ).prop( 'outerHTML' );

		setTimeout( function() {
			if ( true === FusionPageBuilderApp.shortcodeGenerator ) {
				FusionPageBuilderApp.fusionBuilderSetContent( 'generator_element_content', newContent );
			} else {
				FusionPageBuilderApp.fusionBuilderSetContent( 'element_content', newContent );
			}

		}, 100 );

	} );

	/**
	 * Generates table HTML.
	 *
	 * @since 2.0.0
	 * @param {string} tableDOM   - The existing DOM.
	 * @return {string}
	 */
	function generateTable( tableDOM, tableOptions ) {
		var i, j,
			styleNew   = tableOptions.find( '#fusion_table_type' ).val(),
			rowsNew    = parseInt( tableOptions.find( '#sliderfusion_table_rows' ).val() ),
			columnsNew = parseInt( tableOptions.find( '#sliderfusion_table_columns' ).val() ),
			styleOld   = jQuery( tableDOM ).attr( 'class' ).replace( /[^\d.]/g, '' ),
			tr         = jQuery( tableDOM ).find( 'tbody > tr' ),
			thTdOld    = jQuery( tableDOM ).find( 'th' ).length,
			tdOld      = tr.first().children( 'td' ).length,
			rowsOld    = tr.length + 1,
			columnsOld = Math.max( thTdOld, tdOld ),
			rowMarkup  = '';

		if ( styleOld !== styleNew ) {
			jQuery( tableDOM ).attr( 'class', jQuery( tableDOM ).attr( 'class' ).replace( styleOld, styleNew ) );
		}

		if ( rowsNew > rowsOld ) {
			if ( ! jQuery( tableDOM ).find( 'tbody' ).length ) {
				jQuery( tableDOM ).find( 'thead' ).after( '<tbody></tbod>' );
			}

			for ( i = rowsOld; i < rowsNew; i++ ) {
				rowMarkup = '';

				for ( j = 1; j <= columnsNew; j++ ) {
					rowMarkup += '<td align="left">Column ' + j + ' Value ' + i + '</td>';
				}

				jQuery( tableDOM ).find( 'tbody' ).append( '<tr>' + rowMarkup + '</tr>' );
			}
		} else if ( rowsNew < rowsOld && 0 !== rowsNew ) {
			for ( i = rowsNew + 1; i <= rowsOld; i++ ) {
				jQuery( tableDOM ).find( 'tbody > tr' ).last().remove();
			}
		}

		if ( columnsNew > columnsOld ) {
			for ( i = columnsOld + 1; i <= columnsNew; i++ ) {
				jQuery( tableDOM ).find( 'thead tr' ).append( '<th align="left">Column ' + i + '</th>' );
				jQuery( tableDOM ).find( 'tbody tr' ).each( function( index ) {
					var rowIndex =  ( 0 < index ) ? ' ' + ( index + 1 ) : '';

					jQuery( this ).append( '<td align="left">Column ' + i + ' Value' + rowIndex + '</td>' );
				} );
			}

		} else if ( columnsNew < columnsOld ) {
			for ( i = columnsNew + 1; i <= columnsOld; i++ ) {
				jQuery( tableDOM ).find( 'thead th' ).last().remove();
				jQuery( tableDOM ).find( 'tbody tr' ).each( function() {
					jQuery( this ).find( 'td' ).last().remove();
				} );
			}
		}

		return tableDOM;
	}
} );
