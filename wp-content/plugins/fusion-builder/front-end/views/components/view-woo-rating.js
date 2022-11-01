var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Rating Component View.
		FusionPageBuilder.fusion_tb_woo_rating = FusionPageBuilder.ElementView.extend( {

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
				values.icon_size       = _.fusionValidateAttrValue( values.icon_size, 'px' );
				values.count_font_size = _.fusionValidateAttrValue( values.count_font_size, 'px' );
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
						class: 'fusion-woo-rating-tb fusion-woo-rating-tb-' + this.model.get( 'cid' ),
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

				if ( 'yes' !== values.show_count ) {
					attr[ 'class' ] += ' hide-count';
				}

				if ( '' !== values.alignment ) {
					attr[ 'class' ] += ' align-' + values.alignment;
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-rating-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_rating ) {
					output = atts.query_data.woo_rating;
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

				if ( '' !== values.icon_size ) {
					styles += '.fusion-woo-rating-tb.fusion-woo-rating-tb-' + this.model.get( 'cid' ) + ' .woocommerce-product-rating .star-rating {';
					styles += 'font-size:' + values.icon_size + ';}';
				}

				if ( '' !== values.icon_color ) {
					styles += '.fusion-woo-rating-tb.fusion-woo-rating-tb-' + this.model.get( 'cid' ) + ' .woocommerce-product-rating .star-rating:before,';
					styles += '.fusion-woo-rating-tb.fusion-woo-rating-tb-' + this.model.get( 'cid' ) + ' .woocommerce-product-rating .star-rating span:before {';
					styles += 'color:' + values.icon_color + ';}';
				}

				styles += '.fusion-woo-rating-tb.fusion-woo-rating-tb-' + this.model.get( 'cid' ) + ' .woocommerce-product-rating a.woocommerce-review-link {';

				if ( '' !== values.count_font_size ) {
					styles += 'font-size:' + values.count_font_size + ';';
				}

				if ( '' !== values.count_color ) {
					styles += 'color:' + values.count_color + ';';
				}

				styles += '}';

				if ( '' !== values.count_hover_color ) {
					styles += '.fusion-woo-rating-tb.fusion-woo-rating-tb-' + this.model.get( 'cid' ) + ' .woocommerce-product-rating a.woocommerce-review-link:hover {';
					styles += 'color:' + values.count_hover_color + ';}';
				}

				styles += '</style>';

				return styles;
			}
		} );
	} );
}( jQuery ) );
