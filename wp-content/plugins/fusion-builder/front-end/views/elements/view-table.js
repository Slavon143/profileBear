/* eslint-disable dot-notation */
/* eslint no-loop-func: 0 */
/* eslint no-unused-vars: ["error", {"args": "none"}] */

var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Table Element View.
		FusionPageBuilder.fusion_table = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
				var params = this.model.get( 'params' ),
					content,
					styleNew,
					styleOld,
					tableDOM;

				content = 'undefined' === typeof this.$el.find( '[data-param="element_content"]' ).html() ? params.element_content : this.$el.find( '[data-param="element_content"]' ).html();

				tableDOM = jQuery.parseHTML( content.trim() );
				styleOld = jQuery( tableDOM ).attr( 'class' ).replace( /[^\d.]/g, '' );
				styleNew = params.fusion_table_type;

				if ( styleOld !== styleNew ) {
					tableDOM = this.generateTable( tableDOM );
					window.FusionPageBuilderApp.setContent( 'element_content', jQuery( tableDOM ).prop( 'outerHTML' ) );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes       = {},
					values           = atts.params,
					tableElementAtts = this.buildAttr( values ),
					tableDOM,
					tr,
					rowsOld,
					tdOld,
					thTdOld,
					columnsOld;

				if ( 'undefined' !== typeof values.fusion_table_type && '' !== values.fusion_table_type ) {
					values.element_content = values.element_content.replace( /<div .*?">/g, '<div ' + _.fusionGetAttributes( tableElementAtts ) + '>' );
				}

				// Validate values.
				this.validateValues( atts.values );

				// Fix user input error where the amount of cols in element params is larger than actual table markup.
				if ( ! this.renderedYet ) {
					tableDOM = jQuery.parseHTML( values.element_content.trim() );
					tr          = jQuery( tableDOM ).find( 'tbody > tr' );
					rowsOld     = tr.length + 1;
					thTdOld     = jQuery( tableDOM ).find( 'th' ).length;
					tdOld       = tr.first().children( 'td' ).length;
					columnsOld  = Math.max( thTdOld, tdOld );

					if ( 'undefined' !== typeof values.fusion_table_columns && values.fusion_table_columns !== columnsOld ) {
						values.fusion_table_columns = columnsOld;

						this.model.set( 'params', values );
					}

					if ( 'undefined' !== typeof values.fusion_table_rows || values.fusion_table_rows !== rowsOld ) {
						values.fusion_table_rows = rowsOld;

						this.model.set( 'params', values );
					}
				}

				// Table is newly created.
				if ( 'undefined' !== typeof values.fusion_table_columns && '' === values.fusion_table_columns && 'undefined' !== typeof values.fusion_table_rows && '' === values.fusion_table_rows ) {
					values.fusion_table_columns = 2;
					values.fusion_table_rows = 2;
				}

				if ( 'undefined' !== typeof values.fusion_table_columns && '' !== values.fusion_table_columns ) {
					tableDOM = jQuery.parseHTML( values.element_content.trim() );
					tableDOM = this.generateTable( tableDOM );

					values.element_content = jQuery( tableDOM ).prop( 'outerHTML' );
					this.model.set( 'params', values );
				}

				// Any extras that need passed on.
				attributes.cid             = this.model.get( 'cid' );
				attributes.element_content = values.element_content;

				return attributes;
			},

			/**
			 * Modify the values.
			 *
			 * @since 3.8
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = {},
					tableStyle;

				if ( 'undefined' !== typeof values.fusion_table_type && '' !== values.fusion_table_type ) {
					tableStyle = values.element_content.charAt( 19 );

					if ( ( '1' === tableStyle || '2' === tableStyle ) && tableStyle !==  values.fusion_table_type ) {
						values.fusion_table_type = tableStyle;
					}

					attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'table-' + values.fusion_table_type,
						style: ''
					} );

					attr = _.fusionAnimations( values, attr );

					if ( '' !== values.margin_top ) {
						attr.style += 'margin-top:' + values.margin_top + ';';
					}

					if ( '' !== values.margin_right ) {
						attr.style += 'margin-right:' + values.margin_right + ';';
					}

					if ( '' !== values.margin_bottom ) {
						attr.style += 'margin-bottom:' + values.margin_bottom + ';';
					}

					if ( '' !== values.margin_left ) {
						attr.style += 'margin-left:' + values.margin_left + ';';
					}

					if ( '' !== values.class ) {
						attr.class += ' ' + values.class;
					}

					if ( '' !== values.id ) {
						attr.id = values.id;
					}
				}

				return attr;
			},

			/**
			 * Generates table HTML.
			 *
			 * @since 2.0.0
			 * @param {string} tableDOM   - The existing DOM.
			 * @return {string}
			 */
			generateTable: function( tableDOM ) {
				var i, j,
					params     = this.model.get( 'params' ),
					rowsNew    = 'undefined' !== typeof params.fusion_table_rows ? parseInt( params.fusion_table_rows, 10 ) : 0,
					columnsNew = parseInt( params.fusion_table_columns, 10 ),
					tr         = jQuery( tableDOM ).find( 'tbody > tr' ),
					thTdOld    = jQuery( tableDOM ).find( 'th' ).length,
					tdOld      = tr.first().children( 'td' ).length,
					rowsOld    = tr.length + 1,
					columnsOld = Math.max( thTdOld, tdOld ),
					rowMarkup  = '';

				if ( rowsNew > rowsOld ) {
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
				var tableDOM;

				switch ( paramName ) {

				case 'fusion_table_rows':
				case 'fusion_table_columns':
					this.model.attributes.params[ paramName ] = paramValue;

					tableDOM = jQuery.parseHTML( this.model.attributes.params.element_content.trim() );
					tableDOM = this.generateTable( tableDOM );

					window.FusionPageBuilderApp.setContent( 'element_content', jQuery( tableDOM ).prop( 'outerHTML' ) );

					break;
				}
			}

		} );
	} );
}( jQuery ) );
