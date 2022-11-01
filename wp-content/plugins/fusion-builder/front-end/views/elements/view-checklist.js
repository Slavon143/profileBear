/* global fusionSanitize */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Accordion View.
		FusionPageBuilder.fusion_checklist = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.validateValues( atts.values, atts.extras );

				this.values = atts.values;

				// Create attribute objects.
				attributes.checklistShortcode = this.buildChecklistAttr( atts.values );

				// Add computed values that child uses.
				this.buildExtraVars( atts.values );

				// Any extras that need passed on.
				attributes.values = atts.values;
				attributes.cid    = this.model.get( 'cid' );
				attributes.styles = this.buildStyleBlock( atts.values );

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @param {Object} extras - The extras object.
			 * @return {void}
			 */
			validateValues: function( values, extras ) {
				values.size = _.fusionValidateAttrValue( values.size, 'px' );

				// Fallbacks for old size parameter and 'px' check+
				if ( 'small' === values.size ) {
					values.size = '13px';
				} else if ( 'medium' === values.size ) {
					values.size = '18px';
				} else if ( 'large' === values.size ) {
					values.size = '40px';
				} else if ( -1 === values.size.indexOf( 'px' ) ) {
					values.size = fusionSanitize.convert_font_size_to_px( values.size, extras.body_font_size );
				}

				values.circle = ( 1 == values.circle ) ? 'yes' : values.circle;

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
			buildChecklistAttr: function( values ) {

				// Main Attributes
				var checklistShortcode = {};

				checklistShortcode[ 'class' ] = 'fusion-checklist fusion-checklist-' + this.model.get( 'cid' );

				checklistShortcode = _.fusionVisibilityAtts( values.hide_on_mobile, checklistShortcode );

				this.font_size   = parseFloat( values.size );
				this.line_height = this.font_size * 1.7;

				checklistShortcode.style = 'font-size:' + this.font_size + 'px;line-height:' + this.line_height + 'px;';

				if ( '' !== values.margin_top ) {
					checklistShortcode.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					checklistShortcode.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					checklistShortcode.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					checklistShortcode.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( 'yes' === values.divider ) {
					checklistShortcode[ 'class' ] += ' fusion-checklist-divider';
				}

				if ( '' !== values[ 'class' ] ) {
					checklistShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					checklistShortcode.id = values.id;
				}
				checklistShortcode[ 'class' ] += ' fusion-child-element';
				checklistShortcode[ 'data-empty' ] = this.emptyPlaceholderText;

				return checklistShortcode;
			},

			/**
			 * Sets extra args in the model.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			buildExtraVars: function() {
				var extras = {};

				extras.font_size               = this.font_size;
				extras.line_height             = this.line_height;
				extras.circle_yes_font_size    = extras.font_size * 0.88;
				extras.icon_margin             = extras.font_size * 0.7;
				extras.icon_margin_position    = ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'left' : 'right';
				extras.content_margin          = extras.line_height + extras.icon_margin;
				extras.content_margin_position =  ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'right' : 'left';

				this.model.set( 'extras', extras );
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.5
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var css, selectors;

				this.baseSelector = '.fusion-checklist-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				// Divider.
				selectors = [ this.baseSelector + '.fusion-checklist-divider .fusion-li-item' ];
				if ( 'yes' === values.divider ) {
					this.addCssProperty( selectors, 'border-bottom-color', values.divider_color, true );
				}

				// Row Odd & Even BG Color.
				selectors = [ this.baseSelector + ' .fusion-li-item:nth-child(odd)' ];
				if ( '' !== values.odd_row_bgcolor ) {
					this.addCssProperty( selectors, 'background-color', values.odd_row_bgcolor );
				}
				selectors = [ this.baseSelector + ' .fusion-li-item:nth-child(even)' ];
				if ( '' !== values.even_row_bgcolor ) {
					this.addCssProperty( selectors, 'background-color', values.even_row_bgcolor );
				}

				// Padding.
				selectors = [
					this.baseSelector + '.fusion-checklist-divider .fusion-li-item',
					this.baseSelector + ' .fusion-li-item'
				];
				_.each( [ 'top', 'right', 'bottom', 'left' ], function( direction ) {
					var key = 'item_padding_' + direction;
					if ( ! this.isDefault( key ) ) {
						this.addCssProperty( selectors, 'padding-' + direction, values[ key ], true );
					}
				}, this );

				// Text color.
				selectors = [ this.baseSelector + ' .fusion-li-item .fusion-li-item-content' ];
				if ( '' !== values.textcolor ) {
					this.addCssProperty( selectors, 'color', values.textcolor );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );
