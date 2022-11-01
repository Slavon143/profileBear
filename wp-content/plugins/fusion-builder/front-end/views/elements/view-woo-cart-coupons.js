var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Cart Coupons View.
		FusionPageBuilder.fusion_woo_cart_coupons = FusionPageBuilder.ElementView.extend( {

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
				attributes.attr   = this.buildAttr( atts.values );

				attributes.wooCartCouponsAttr = this.buildAttr( atts.values, attributes.cid );
				attributes.cart_coupons_content = '';
				if ( 'undefined' !== typeof atts.query_data  ) {
					attributes.cart_coupons_content = atts.query_data;
				}
				attributes.styles = this.buildStyleBlock( atts.values );
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

				// WooCartCoupons attributes.
				var wooCartCoupons = {
					class: 'coupon fusion-woo-cart_coupons fusion-woo-cart_coupons-' + cid
				};

				wooCartCoupons = _.fusionVisibilityAtts( values.hide_on_mobile, wooCartCoupons );

				if ( '' !== values[ 'class' ] ) {
					wooCartCoupons[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					wooCartCoupons.id = values.id;
				}

				wooCartCoupons = _.fusionAnimations( values, wooCartCoupons );

				return wooCartCoupons;
			},


			/**
			 * Builds styles.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var inputs, hoverColor, placeholderColor, placeHolderInputs, hoverInputs, focusInputs, css, selector;
				this.values = values;
				// variables into current scope
				this.baseSelector = '.fusion-woo-cart_coupons-' +  this.model.get( 'cid' );
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

				inputs = [ this.baseSelector + ' input', this.baseSelector + ' select', this.baseSelector + ' textarea' ];

				if ( ! this.isDefault( 'field_bg_color' ) ) {
					this.addCssProperty( inputs, 'background',  this.values.field_bg_color );
				}

				if ( ! this.isDefault( 'field_text_color' ) ) {
					placeholderColor = jQuery.AWB_Color( this.values.field_text_color ).alpha( 0.5 ).toVarOrRgbaString();
					this.addCssProperty( inputs, 'color',  this.values.field_text_color );

					placeHolderInputs = [ this.baseSelector + ' input::placeholder', this.baseSelector + ' textarea::placeholder' ];
					this.addCssProperty( placeHolderInputs, 'color',  placeholderColor );
				}

				if ( ! this.isDefault( 'field_border_color' ) ) {
					this.addCssProperty( inputs, 'border-color',  this.values.field_border_color );
				}

				if ( ! this.isDefault( 'field_border_focus_color' ) ) {
					hoverColor = jQuery.AWB_Color( this.values.field_border_focus_color ).alpha( 0.5 ).toVarOrRgbaString();
					hoverInputs = [ this.baseSelector + ' input:hover', this.baseSelector + ' select:hover', this.baseSelector + ' textarea:hover' ];
					this.addCssProperty( hoverInputs, 'border-color', hoverColor );
					focusInputs = [ this.baseSelector + ' input:focus', this.baseSelector + ' select:focus', this.baseSelector + ' textarea:focus' ];
					this.addCssProperty( focusInputs, 'border-color',  this.values.field_border_focus_color );
				}

				selector =  this.baseSelector + ' button.fusion-apply-coupon';
				if ( !this.isDefault( 'button_margin_top' ) ) {
				this.addCssProperty( selector, 'margin-top',  this.values.button_margin_top );
				}

				if ( !this.isDefault( 'button_margin_bottom' ) ) {
				this.addCssProperty( selector, 'margin-bottom',  this.values.button_margin_bottom );
				}

				if ( !this.isDefault( 'button_margin_left' ) ) {
				this.addCssProperty( selector, 'margin-left',  this.values.button_margin_left );
				}

				if ( !this.isDefault( 'button_margin_right' ) ) {
				this.addCssProperty( selector, 'margin-right',  this.values.button_margin_right );
				}

				selector =  this.baseSelector + ' div.avada-coupon-fields';
				if ( 'floated' ===  this.values.buttons_layout ) {
				this.addCssProperty( selector, 'flex-direction', 'row' );
				} else {
				this.addCssProperty( selector, 'flex-direction', 'column', true );
				this.addCssProperty( this.baseSelector + ' input#avada_coupon_code', 'flex', 'auto' );
				this.addCssProperty( this.baseSelector + ' input#avada_coupon_code', 'margin-right', '0' );

				if ( 'yes' ===  this.values.button_span ) {
					this.addCssProperty( selector, 'align-items', 'stretch', true );
					this.addCssProperty( this.baseSelector + ' input#avada_coupon_code', 'width', '100%' );
				} else {
					this.addCssProperty( selector, 'align-items',  this.values.stacked_buttons_alignment, true );
				}

				}
				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );
