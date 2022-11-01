var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Product Images Component View.
		FusionPageBuilder.fusion_tb_woo_product_images = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs before view DOM is patched.
			 *
			 * @since 3.2
			 * @return {void}
			 */
			beforePatch: function() {
				var element = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.woocommerce-product-gallery' ) );

				if ( 'undefined' !== typeof element.data( 'flexslider' ) ) {
					element.flexslider( 'destroy' );
				}
			},


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
				this.values = atts.values;
				this.params = this.model.get( 'params' );

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock();
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
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-woo-product-images fusion-woo-product-images-' + this.model.get( 'cid' ),
						style: '',
						'data-zoom_enabled': 'yes' === values.product_images_zoom ? 1 : 0,
						'data-photoswipe_enabled': 'woocommerce' === values.product_images_layout ? 1 : 0
					} );

				if ( '' !== values.alignment ) {
					attr.style += 'justify-content:' + values.alignment + ';';
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-product-images' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.markup ) {
					output = atts.query_data.markup;
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
			buildStyleBlock: function() {
				var css;

				this.baseSelector = '.fusion-woo-product-images-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				this.addCssProperty( this.baseSelector + ' .woocommerce-product-gallery', 'max-width', _.fusionGetValueWithUnit( this.values.product_images_width ) );

				if ( ! this.isDefault( 'margin_top' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-top',  _.fusionGetValueWithUnit( this.values.margin_top ) );
				}

				if ( ! this.isDefault( 'margin_right' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-right',  _.fusionGetValueWithUnit( this.values.margin_right ) );
				}

				if ( ! this.isDefault( 'margin_bottom' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-bottom',  _.fusionGetValueWithUnit( this.values.margin_bottom ) );
				}

				if ( ! this.isDefault( 'margin_left' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-left',  _.fusionGetValueWithUnit( this.values.margin_left ) );
				}

				css = this.parseCSS();
				return ( css ) ? '<style type="text/css">' + css + '</style>' : '';

			}
		} );
	} );
}( jQuery ) );
