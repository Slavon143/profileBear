/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Checkout Shipping Component View.
		FusionPageBuilder.fusion_tb_woo_checkout_shipping = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @return null
			 */
			onRender: function() {
				if ( ! jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).hasClass( 'woocommerce' ) ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).addClass( 'woocommerce' );
				}
			},

			/**
			 * Runs just before view is removed.
			 *
			 * @since 3.3
			 * @return null
			 */
			beforeRemove: function() {
				var self = this,
					removeClass = true;

				_.find( FusionPageBuilderApp.collection.models, function( element ) {
					if ( self.model.cid !== element.cid && -1 !== element.attributes.element_type.indexOf( 'fusion_tb_woo_checkout' ) ) {
						removeClass = false;

						// Break.
						return true;
					}
				} );

				if ( true === removeClass ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).removeClass( 'woocommerce' );
				}
			},

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
				this.params = this.model.get( 'params' );
				this.extras = atts.extras;

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
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-woo-checkout-shipping-tb fusion-woo-checkout-shipping-tb-' + this.model.get( 'cid' ),
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
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-checkout-shipping-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_checkout_shipping ) {
					output = atts.query_data.woo_checkout_shipping;
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
			buildStyleBlock: function() {
				var inputs, hoverColor, placeholderColor, placeHolderInputs, hoverInputs, focusInputs, css;

				this.baseSelector = '.fusion-woo-checkout-shipping-tb-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				inputs = [ this.baseSelector + ' input', this.baseSelector + ' select', this.baseSelector + ' textarea' ];

				if ( ! this.isDefault( 'field_bg_color' ) ) {
					this.addCssProperty( inputs, 'background',  this.values.field_bg_color, true );

					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'background-color', this.values.field_bg_color, true );
				}

				if ( ! this.isDefault( 'field_text_color' ) ) {
					placeholderColor = jQuery.AWB_Color( this.values.field_text_color ).alpha( 0.5 ).toVarOrRgbaString();
					this.addCssProperty( inputs, 'color',  this.values.field_text_color, true );

					placeHolderInputs = [ this.baseSelector + ' input::placeholder', this.baseSelector + ' textarea::placeholder' ];
					this.addCssProperty( placeHolderInputs, 'color',  placeholderColor );

					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'color', this.values.field_text_color, true );
				}

				if ( ! this.isDefault( 'field_border_color' ) ) {
					this.addCssProperty( inputs, 'border-color',  this.values.field_border_color, true );

					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'border-color', this.values.field_border_color, true );
				}

				if ( ! this.isDefault( 'field_border_focus_color' ) ) {
					hoverColor = jQuery.AWB_Color( this.values.field_border_focus_color ).alpha( 0.5 ).toVarOrRgbaString();
					hoverInputs = [ this.baseSelector + ' input:hover', this.baseSelector + ' select:hover', this.baseSelector + ' textarea:hover' ];
					this.addCssProperty( hoverInputs, 'border-color', hoverColor );
					focusInputs = [ this.baseSelector + ' input:focus', this.baseSelector + ' select:focus', this.baseSelector + ' textarea:focus' ];
					this.addCssProperty( focusInputs, 'border-color',  this.values.field_border_focus_color );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
