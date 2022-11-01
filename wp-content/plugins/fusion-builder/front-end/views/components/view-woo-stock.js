var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Stock Component View.
		FusionPageBuilder.fusion_tb_woo_stock = FusionPageBuilder.ElementView.extend( {

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
				values.stock_font_size = _.fusionValidateAttrValue( values.stock_font_size, 'px' );
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
						class: 'fusion-woo-stock-tb fusion-woo-stock-tb-' + this.model.get( 'cid' ),
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-stock-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_stock ) {
					output = atts.query_data.woo_stock;
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
				var styles = '<style type="text/css">';

				if ( '' !== values.stock_font_size ) {
					styles += '.fusion-woo-stock-tb.fusion-woo-stock-tb-' + this.model.get( 'cid' ) + ' p.stock{ font-size: ' + values.stock_font_size + '}';
				}

				if ( '' !== values.stock_color ) {
					styles += '.fusion-woo-stock-tb.fusion-woo-stock-tb-' + this.model.get( 'cid' ) + ' p.stock{ color: ' + values.stock_color + '}';
				}

				styles += '</style>';

				return styles;
			}
		} );
	} );
}( jQuery ) );
