/* global fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Price Component View.
		FusionPageBuilder.fusion_tb_woo_price = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				this.values = atts.values;

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );
				attributes.output      = this.buildOutput( atts );

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				var borderRadiusTopLeft     = 'undefined' !== typeof values.border_radius_top_left && '' !== values.border_radius_top_left ? _.fusionGetValueWithUnit( values.border_radius_top_left ) : '0px',
					borderRadiusTopRight    = 'undefined' !== typeof values.border_radius_top_right && '' !== values.border_radius_top_right ? _.fusionGetValueWithUnit( values.border_radius_top_right ) : '0px',
					borderRadiusBottomRight = 'undefined' !== typeof values.border_radius_bottom_right && '' !== values.border_radius_bottom_right ? _.fusionGetValueWithUnit( values.border_radius_bottom_right ) : '0px',
					borderRadiusBottomLeft  = 'undefined' !== typeof values.border_radius_bottom_left && '' !== values.border_radius_bottom_left ? _.fusionGetValueWithUnit( values.border_radius_bottom_left ) : '0px';

				values.border_radius     = borderRadiusTopLeft + ' ' + borderRadiusTopRight + ' ' + borderRadiusBottomRight + ' ' + borderRadiusBottomLeft;
				values.border_radius     = ( '0px 0px 0px 0px' === values.border_radius ) ? '' : values.border_radius;
				values.badge_border_size = _.fusionValidateAttrValue( values.badge_border_size, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-woo-price-tb fusion-woo-price-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

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

				if ( '' !== values.alignment ) {
					attr.style += 'justify-content:' + values.alignment + ';';
				}

				if ( 'yes' !== values.show_sale ) {
					attr[ 'class' ] += ' hide-sale';
				}

				if ( '0px' !== values.badge_border_size ) {
					attr[ 'class' ] += ' has-border';
				}

				if ( '' !== values.sale_position ) {
					attr[ 'class' ] += ' sale-position-' + values.sale_position;
				}

				if ( '' !== values.layout ) {
					attr[ 'class' ] += ' ' + values.layout;
				}

				if ( '' !== values.badge_position && 'no' !== values.show_badge ) {
					attr[ 'class' ] += ' has-badge badge-position-' + values.badge_position;
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Adds CSS property to object.
			 *
			 * @since  3.2
			 * @param  {String} selectors - The CSS selectors.
			 * @param  {String} property - The CSS property.
			 * @param  {String} value - The CSS property value.
			 * @param  {Bool}   important - Should have important tag.
			 * @return {void}
			 */
			addCssProperty: function ( selectors, property, value, important ) {

				if ( 'object' === typeof selectors ) {
					selectors = Object.values( selectors );
				}

				if ( 'object' === typeof selectors ) {
					selectors = selectors.join( ',' );
				}

				if ( 'object' !== typeof this.dynamic_css[ selectors ] ) {
					this.dynamic_css[ selectors ] = {};
				}

				if ( 'undefined' !== typeof important && important ) {
					value += ' !important';
				}
				if ( 'undefined' === typeof this.dynamic_css[ selectors ][ property ] || ( 'undefined' !== typeof important && important ) || ! this.dynamic_css[ selectors ][ property ].includes( 'important' ) ) {
					this.dynamic_css[ selectors ][ property ] = value;
				}
			},

			/**
			 * Checks if param has got default value or not.
			 *
			 * @since  3.2
			 * @param  {String} param - The param.
			 * @return {Bool}
			 */
			isDefault: function( param ) {
				return this.values[ param ] === fusionAllElements.fusion_tb_woo_price.defaults[ param ];
			},

			/**
			 * Builds output.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-price-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_price ) {
					output = atts.query_data.woo_price;
				}

				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var css, selectors,
				fontStyles = {},
				self = this;

				this.baseSelector = '.fusion-woo-price-tb.fusion-woo-price-tb-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				selectors = [
					this.baseSelector + ' .price',
					this.baseSelector + ' .price ins .amount',
					this.baseSelector + ' .price del .amount',
					this.baseSelector + ' .price > .amount'
				];

				if ( ! this.isDefault( 'price_font_size' ) ) {
					this.addCssProperty( selectors, 'font-size', values.price_font_size );
				}

				if ( !this.isDefault( 'price_line_height' ) ) {
					this.addCssProperty( selectors, 'line-height', this.values.price_line_height );
				}

				if ( !this.isDefault( 'price_text_transform' ) ) {
					this.addCssProperty( selectors, 'text-transform', this.values.price_text_transform );
				}

				if ( !this.isDefault( 'price_letter_spacing' ) ) {
					this.addCssProperty( selectors, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.price_letter_spacing ) );
				}

				if ( ! this.isDefault( 'price_color' ) ) {
					this.addCssProperty( selectors, 'color', values.price_color );
				}

				fontStyles = _.fusionGetFontStyle( 'price_typography', values, 'object' );
				jQuery.each( fontStyles, function( rule, value ) {
					self.addCssProperty( selectors, rule, value );
				} );

				if ( ! this.isDefault( 'sale_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .price del .amount', 'font-size', values.sale_font_size );
				}

				if ( !this.isDefault( 'sale_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' .price del .amount', 'line-height', this.values.sale_line_height );
				}

				if ( !this.isDefault( 'sale_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' .price del .amount', 'text-transform', this.values.sale_text_transform );
				}

				if ( !this.isDefault( 'sale_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' .price del .amount', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.sale_letter_spacing ) );
				}

				if ( ! this.isDefault( 'sale_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .price del .amount', 'color', values.sale_color );
				}

				fontStyles = _.fusionGetFontStyle( 'sale_typography', values, 'object' );
				jQuery.each( fontStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' .price del .amount', rule, value );
				} );

				if ( ! this.isDefault( 'stock_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' p.stock', 'font-size', values.stock_font_size );
				}

				if ( !this.isDefault( 'stock_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' p.stock', 'line-height', this.values.stock_line_height );
				}

				if ( !this.isDefault( 'stock_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' p.stock', 'text-transform', this.values.stock_text_transform );
				}

				if ( !this.isDefault( 'stock_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' p.stock', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.stock_letter_spacing ) );
				}

				if ( ! this.isDefault( 'stock_color' ) ) {
					this.addCssProperty( this.baseSelector + ' p.stock', 'color', values.stock_color );
				}

				fontStyles = _.fusionGetFontStyle( 'stock_typography', values, 'object' );
				jQuery.each( fontStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' p.stock', rule, value );
				} );

				if ( ! this.isDefault( 'badge_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'font-size', values.badge_font_size );
				}

				if ( !this.isDefault( 'badge_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'line-height', this.values.badge_line_height );
				}

				if ( !this.isDefault( 'badge_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'text-transform', this.values.badge_text_transform );
				}

				if ( !this.isDefault( 'badge_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.badge_letter_spacing ) );
				}

				if ( ! this.isDefault( 'badge_text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'color', values.badge_text_color );
				}

				fontStyles = _.fusionGetFontStyle( 'badge_typography', values, 'object' );
				jQuery.each( fontStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' .fusion-onsale', rule, value );
				} );

				if ( ! this.isDefault( 'badge_bg_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'background', values.badge_bg_color );
				}

				if ( ! this.isDefault( 'badge_border_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'border-width', values.badge_border_size );
				}

				if ( ! this.isDefault( 'badge_border_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'border-color', values.badge_border_color );
				}

				if ( ! this.isDefault( 'badge_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'font-size', values.badge_font_size );
				}

				if ( values.border_radius && '' !== values.border_radius ) {
					this.addCssProperty( this.baseSelector + ' .fusion-onsale', 'border-radius', values.border_radius );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';

			},

			/**
			 * Parses CSS.
			 *
			 * @since  3.2
			 * @return {String}
			 */
			parseCSS: function () {
				var css = '';

				if ( 'object' !== typeof this.dynamic_css ) {
					return '';
				}

				_.each( this.dynamic_css, function ( properties, selector ) {
					if ( 'object' === typeof properties ) {
						css += selector + '{';
						_.each( properties, function ( value, property ) {
							css += property + ':' + value + ';';
						} );
						css += '}';
					}
				} );

				return css;
			}
		} );
	} );
}( jQuery ) );
