var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Rating Component View.
		FusionPageBuilder.fusion_tb_woo_short_description = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.values = atts.values;

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );
				attributes.output      = this.buildOutput( atts );

				return attributes;
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
						class: 'fusion-woo-short-description-tb fusion-woo-short-description-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

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
			 * Builds output.
			 *
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-short-description-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_short_description ) {
					output = atts.query_data.woo_short_description;
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
				var css,
					self = this,
					textStyles = {};

				this.baseSelector = '.fusion-woo-short-description-tb.fusion-woo-short-description-tb-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				// Text styles.
				if ( ! this.isDefault( 'text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-product-details__short-description', 'color', values.text_color );
				}

				if ( ! this.isDefault( 'text_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-product-details__short-description', 'font-size',  _.fusionGetValueWithUnit( values.text_font_size ) );
				}

				if ( !this.isDefault( 'text_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-product-details__short-description', 'line-height', this.values.text_line_height );
				}

				if ( !this.isDefault( 'text_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-product-details__short-description', 'text-transform', this.values.text_text_transform );
				}

				if ( !this.isDefault( 'text_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-product-details__short-description', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.text_letter_spacing ) );
				}

				// Text typography styles.
				textStyles = _.fusionGetFontStyle( 'text_font', values, 'object' );
				jQuery.each( textStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' .woocommerce-product-details__short-description', rule, value );
				} );

				jQuery.each( [ 'top', 'right', 'bottom', 'left' ], function( index, side ) {
					var marginName      = 'margin_' + side;

					// Element margin.
					if ( '' !==  values[ marginName ] ) {
						self.addCssProperty( self.baseSelector, 'margin-' + side,  _.fusionGetValueWithUnit( values[ marginName ] ) );
					}
				} );

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
