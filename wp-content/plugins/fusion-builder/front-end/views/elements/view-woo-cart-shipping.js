var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Cart Shipping View.
		FusionPageBuilder.fusion_woo_cart_shipping = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				attributes.cid    = this.model.get( 'cid' );

				attributes.wooCartShippingAttr = this.buildAttr( atts.values, attributes.cid );
				attributes.cart_shipping_content = '';
				if ( 'undefined' !== typeof atts.query_data  ) {
					attributes.cart_shipping_content = atts.query_data;
				}
				attributes.styles = this.buildStyleBlock( atts.values, atts.extras );

				return attributes;
			},

			/**
			 * Builds main attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAttr: function( values, cid ) {

				// WooFeaturedProductsSliderShortcode attributes.
				var wooCartShippingAttr = {
					class: 'woocommerce-shipping-calculator fusion-woocommerce-shipping-calculator fusion-woocommerce-shipping-calculator-' + cid
				};

				wooCartShippingAttr = _.fusionVisibilityAtts( values.hide_on_mobile, wooCartShippingAttr );

				if ( '' !== values[ 'class' ] ) {
					wooCartShippingAttr[ 'class' ] += ' ' + values[ 'class' ];
				}


				if ( '' !== values.id ) {
					wooCartShippingAttr.id = values.id;
				}

				wooCartShippingAttr = _.fusionAnimations( values, wooCartShippingAttr );

				return wooCartShippingAttr;
			},


			/**
			 * Builds styles.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @param  {Object} extras - The extras object.
			 * @return {String}
			 */
			buildStyleBlock: function( values, extras ) {
				var inputs, hoverColor, placeholderColor, placeHolderInputs, hoverInputs, css;
				this.values = values;
				// variables into current scope
				this.baseSelector = '.fusion-woocommerce-shipping-calculator-' +  this.model.get( 'cid' );
				this.dynamic_css = {};


				if ( !this.isDefault( 'margin_top' ) ) {
				this.addCssProperty( this.baseSelector, 'margin-top',  this.values.margin_top, true );
				}

				if ( !this.isDefault( 'margin_bottom' ) ) {
				this.addCssProperty( this.baseSelector, 'margin-bottom',  this.values.margin_bottom );
				}

				if ( !this.isDefault( 'margin_left' ) ) {
				this.addCssProperty( this.baseSelector, 'margin-left',  this.values.margin_left );
				}

				if ( !this.isDefault( 'margin_right' ) ) {
				this.addCssProperty( this.baseSelector, 'margin-right',  this.values.margin_right );
				}

				inputs = [ this.baseSelector + ' input', this.baseSelector + ' select', this.baseSelector + ' textarea', this.baseSelector + ' .avada-select-parent .select-arrow', this.baseSelector + '.select2-container--default .select2-selection--single' ];

				if ( ! this.isDefault( 'field_bg_color' ) ) {
					this.addCssProperty( inputs, 'background',  this.values.field_bg_color, true );

				}

				if ( ! this.isDefault( 'field_text_color' ) ) {
					placeholderColor = jQuery.AWB_Color( this.values.field_text_color ).alpha( 0.5 ).toVarOrRgbaString();
					this.addCssProperty( inputs, 'color',  this.values.field_text_color, true );

					placeHolderInputs = [ this.baseSelector + ' input::placeholder', this.baseSelector + ' textarea::placeholder' ];
					this.addCssProperty( placeHolderInputs, 'color',  placeholderColor );
				}

				if ( ! this.isDefault( 'field_border_color' ) ) {
					this.addCssProperty( inputs, 'border-color',  this.values.field_border_color, true );

					// Select 2.
					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'border-color', this.values.field_border_color, true );
					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'color', this.values.field_border_color, true );
				}

				if ( ! this.isDefault( 'field_border_focus_color' ) ) {
					hoverColor = jQuery.AWB_Color( this.values.field_border_focus_color ).alpha( 0.5 ).toVarOrRgbaString();

					hoverInputs = [
						this.baseSelector + ' input:hover',
						this.baseSelector + ' select:hover',
						this.baseSelector + ' textarea:hover',
						this.baseSelector + ' input:focus',
						this.baseSelector + ' select:focus',
						this.baseSelector + ' textarea:focus'
					];
					this.addCssProperty( hoverInputs, 'border-color', hoverColor, true );

					// Select 2.
					this.addCssProperty( this.baseSelector + ' .avada-select-parent:hover .select-arrow', 'border-color', hoverColor, true );
					this.addCssProperty( this.baseSelector + ' .avada-select-parent:hover .select-arrow', 'color', hoverColor, true );
				}


				css  = this.parseCSS();
				css += this.mediaQueryStyles( extras );

				return ( css ) ? '<style>' + css + '</style>' : '';

			},

			/**
			 * Builds media query styles.
			 *
			 * @since  3.3
			 * @param  {Object} extras - The extras object.
			 * @return {String}
			 */
			mediaQueryStyles: function( extras ) {
				var baseSelector = '.fusion-woocommerce-shipping-calculator-' + this.model.get( 'cid' ),
					css = '';

				css  = '@media only screen and (max-width:' + extras.content_break_point + 'px) {';
				css += baseSelector + ' p.fusion-layout-column.fusion-column-last:last-of-type {';
				css += 'margin-bottom: 0px;';
				css += '}}';

				css  = '@media only screen and (min-width:' + extras.content_break_point + 'px) {';
				css += baseSelector + ' .fusion-layout-column.fusion-column-last {';
				css += 'margin-bottom: 0px;';
				css += '}}';

				return css;
			}

		} );
	} );
}( jQuery ) );
