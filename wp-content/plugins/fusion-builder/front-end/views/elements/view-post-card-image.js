/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Checkout Billing Component View.
		FusionPageBuilder.fusion_post_card_image = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.values = atts.values;

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.output      = this.buildOutput( atts );
				attributes.styles      = this.buildStyleBlock( atts.values );

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-' + FusionApp.settings.woocommerce_product_box_design + '-product-image-wrapper fusion-woo-product-image fusion-post-card-image fusion-post-card-image-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.aspect_ratio ) {
					attr[ 'class' ] += ' has-aspect-ratio';
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
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-product-image' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.fusion_post_card_image ) {
					output = atts.query_data.fusion_post_card_image;
				}

				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
                var self = this,
                    sides, margin_name, css;

                this.baseSelector = '.fusion-post-card-image.fusion-post-card-image-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

                sides = [ 'top', 'right', 'bottom', 'left' ];

				// Margins.
				jQuery.each( sides, function( index, side ) {
					// Element margin.
					margin_name = 'margin_' + side;
					if ( '' !==  self.values[ margin_name ] ) {
						self.addCssProperty( self.baseSelector, 'margin-' + side,  _.fusionGetValueWithUnit( self.values[ margin_name ] ) );
					}
				} );

				if ( ! this.isDefault( 'crossfade_bg_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .crossfade-images', 'background-color', values.crossfade_bg_color );
				}

				// Border Radius.
				if (  !  this.isDefault( 'border_radius_top_left' ) ) {
					this.addCssProperty( this.baseSelector, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.border_radius_top_left ) );
				}

				if (  !  this.isDefault( 'border_radius_top_right' ) ) {
					this.addCssProperty( this.baseSelector, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.border_radius_top_right ) );
				}

				if (  !  this.isDefault( 'border_radius_bottom_right' ) ) {
					this.addCssProperty( this.baseSelector, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.border_radius_bottom_right ) );
				}

				if (  !  this.isDefault( 'border_radius_bottom_left' ) ) {
					this.addCssProperty( this.baseSelector, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.border_radius_bottom_left ) );
				}

				// Calc Ratio
				let aspectRatio, width, height;
                const selector = '.fusion-post-card-image.fusion-post-card-image-' + this.model.get( 'cid' ) + ' img';

				if ( 'custom' ===  this.values.aspect_ratio && '' !==  this.values.custom_aspect_ratio ) {
					this.addCssProperty( selector, 'aspect-ratio', `100 / ${this.values.custom_aspect_ratio}` );
				} else {
					aspectRatio = this.values.aspect_ratio.split( '-' );
					width 		= aspectRatio[ 0 ] || '';
					height 		= aspectRatio[ 1 ] || '';

					this.addCssProperty( selector, 'aspect-ratio', `${width} / ${height}` );
				}

				//Ratio Position
				if ( '' !==  this.values.aspect_ratio_position ) {
					this.addCssProperty( selector, 'object-position', this.values.aspect_ratio_position );
				}

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
