/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Checkout Payment Component View.
		FusionPageBuilder.fusion_tb_woo_checkout_payment = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				var params = this.model.get( 'params' );

				// Check for newer margin params.  If unset but regular is, copy from there.
				if ( 'object' === typeof params ) {

					// Split border width into 4.
					if ( 'undefined' === typeof params.button_border_top && 'undefined' !== typeof params.button_border_width && '' !== params.button_border_width ) {
						params.button_border_top    = parseInt( params.button_border_width ) + 'px';
						params.button_border_right  = params.button_border_top;
						params.button_border_bottom = params.button_border_top;
						params.button_border_left   = params.button_border_top;
						delete params.button_border_width;
					}
					this.model.set( 'params', params );
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

				// Validate values.
				this.values = atts.values;
				this.params = this.model.get( 'params' );
				this.extras = atts.extras;

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
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-woo-checkout-payment-tb fusion-woo-checkout-payment-tb-' + this.model.get( 'cid' ),
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

				if ( '' !== values.button_alignment ) {
					attr[ 'class' ] += ' button-align-' + values.button_alignment;
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-checkout-payment-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_checkout_payment ) {
					output = atts.query_data.woo_checkout_payment;
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
				// variables into current scope
				var self = this,
					button, button_size_map, button_dimensions, button_hover, textStyles, css, selector;


				this.baseSelector = '.fusion-woo-checkout-payment-tb-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				textStyles = _.fusionGetFontStyle( 'text_typography', values, 'object' );
				jQuery.each( textStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector, rule, value );
				} );

				// Text font size.
				if ( !this.isDefault( 'text_font_size' ) ) {
				  this.addCssProperty( this.baseSelector, 'font-size',  _.fusionGetValueWithUnit( this.values.text_font_size ) );
				}

				if ( !this.isDefault( 'text_line_height' ) ) {
					this.addCssProperty( this.baseSelector, 'line-height', this.values.text_line_height );
				}

				if ( !this.isDefault( 'text_text_transform' ) ) {
					this.addCssProperty( this.baseSelector, 'text-transform', this.values.text_text_transform );
				}

				if ( !this.isDefault( 'text_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.text_letter_spacing ) );
				}

				if ( !this.isDefault( 'link_color' ) ) {
				  this.addCssProperty( this.baseSelector + ' a', 'color',  this.values.link_color );
				}

				if ( !this.isDefault( 'link_hover_color' ) ) {
				  this.addCssProperty( this.baseSelector + ' a:hover', 'color',  this.values.link_hover_color );
				}

				selector =  this.baseSelector + ' .woocommerce-checkout-payment ul.wc_payment_methods li > label';
				if ( !this.isDefault( 'label_padding_top' ) ) {
				  this.addCssProperty( selector, 'padding-top',  this.values.label_padding_top );
				}

				if ( !this.isDefault( 'label_padding_bottom' ) ) {
				  this.addCssProperty( selector, 'padding-bottom',  this.values.label_padding_bottom );
				}

				if ( !this.isDefault( 'label_padding_left' ) ) {
				  this.addCssProperty( selector, 'padding-left', 'max(55px,' +  this.values.label_padding_left + ')' );
				}

				if ( !this.isDefault( 'label_padding_right' ) ) {
				  this.addCssProperty( selector, 'padding-right',  this.values.label_padding_right );
				}

				if ( !this.isDefault( 'label_bg_color' ) ) {
				  this.addCssProperty( selector, 'background',  this.values.label_bg_color );
				}

				if ( !this.isDefault( 'label_color' ) ) {
				  this.addCssProperty( selector, 'color',  this.values.label_color );
				}

				if ( !this.isDefault( 'label_hover_color' ) ) {
				  this.addCssProperty( selector + ':hover', 'color',  this.values.label_hover_color );
					this.addCssProperty( this.baseSelector + ' ul li input:checked+label', 'color',  this.values.label_hover_color );
				}

				selector =  this.baseSelector + ' .woocommerce-checkout-payment ul.wc_payment_methods li:hover > label';
				if ( !this.isDefault( 'label_bg_hover_color' ) ) {
				  this.addCssProperty( selector, 'background',  this.values.label_bg_hover_color );
				}

				selector = [ this.baseSelector + ' .woocommerce-checkout-payment ul.wc_payment_methods li .payment_box', this.baseSelector + ' .woocommerce-checkout-payment ul.wc_payment_methods li.woocommerce-notice' ];
				if ( !this.isDefault( 'payment_padding_top' ) ) {
				  this.addCssProperty( selector, 'padding-top',  this.values.payment_padding_top );
				}

				if ( !this.isDefault( 'payment_padding_bottom' ) ) {
				  this.addCssProperty( selector, 'padding-bottom',  this.values.payment_padding_bottom );
				}

				if ( !this.isDefault( 'payment_padding_left' ) ) {
				  this.addCssProperty( selector, 'padding-left',  this.values.payment_padding_left );
				}

				if ( !this.isDefault( 'payment_padding_right' ) ) {
				  this.addCssProperty( selector, 'padding-right',  this.values.payment_padding_right );
				}

				if ( !this.isDefault( 'payment_box_bg' ) ) {
				  this.addCssProperty( selector, 'background',  this.values.payment_box_bg );
				}

				if ( !this.isDefault( 'payment_color' ) ) {
				  this.addCssProperty( selector, 'color',  this.values.payment_color );
				}

				if ( !this.isDefault( 'button_style' ) ) {
				  button = '.fusion-body ' +  this.baseSelector + ' #place_order';
				  // Button size.
				  if (  !  this.isDefault( 'button_size' ) ) {
					button_size_map = {
						small: {
							padding: '9px 20px',
							line_height: '14px',
							font_size: '12px'
						},
						medium: {
							padding: '11px 23px',
							line_height: '16px',
							font_size: '13px'
						},
						large: {
							padding: '13px 29px',
							line_height: '17px',
							font_size: '14px'
						},
						xlarge: {
							padding: '17px 40px',
							line_height: '21px',
							font_size: '18px'
						}
					};

					if ( 'object' === typeof button_size_map[ this.values.button_size ] ) {
				      button_dimensions = button_size_map[ this.values.button_size ];
				      this.addCssProperty( button, 'padding', button_dimensions.padding );
				      this.addCssProperty( button, 'line-height', button_dimensions.line_height );
				      this.addCssProperty( button, 'font-size', button_dimensions.font_size );
				    }

				  }

				  if (  !  this.isDefault( 'button_stretch' ) ) {
				    this.addCssProperty( button, 'flex', '1' );
				    this.addCssProperty( button, 'width', '100%' );
				  }

 				  if (  !  this.isDefault( 'button_border_top' ) ) {
				    this.addCssProperty( button, 'border-top-width',  _.fusionGetValueWithUnit( this.values.button_border_top ) );
				  }
				  if (  !  this.isDefault( 'button_border_right' ) ) {
				    this.addCssProperty( button, 'border-right-width',  _.fusionGetValueWithUnit( this.values.button_border_right ) );
				  }
				  if (  !  this.isDefault( 'button_border_bottom' ) ) {
				    this.addCssProperty( button, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.button_border_bottom ) );
				  }
				  if (  !  this.isDefault( 'button_border_left' ) ) {
				    this.addCssProperty( button, 'border-left-width',  _.fusionGetValueWithUnit( this.values.button_border_left ) );
				  }

				  if (  !  this.isDefault( 'button_color' ) ) {
				    this.addCssProperty( button, 'color',  this.values.button_color );
				  }

				  if ( ( 'string' === typeof this.params.button_gradient_top && '' !==  this.params.button_gradient_top ) ||  ( 'string' === typeof this.params.button_gradient_bottom && '' !==  this.params.button_gradient_bottom ) ) {
				    this.addCssProperty( button, 'background',  this.values.button_gradient_top );
				    this.addCssProperty( button, 'background-image', 'linear-gradient( to top, ' +  this.values.button_gradient_bottom + ', ' +  this.values.button_gradient_top + ' )' );
				  }

				  if (  !  this.isDefault( 'button_border_color' ) ) {
				    this.addCssProperty( button, 'border-color',  this.values.button_border_color );
				  }

				  button_hover = button + ':hover';
				  // Button hover text color
				  if (  !  this.isDefault( 'button_color_hover' ) ) {
				    this.addCssProperty( button_hover, 'color',  this.values.button_color_hover );
				  }

				  if ( ( 'string' === typeof this.params.button_gradient_top_hover && '' !== this.params.button_gradient_top_hover ) ||  ( 'string' === typeof this.params.button_gradient_bottom_hover && '' !== this.params.button_gradient_bottom_hover ) ) {
				    this.addCssProperty( button_hover, 'background',  this.values.button_gradient_top_hover );
				    this.addCssProperty( button_hover, 'background-image', 'linear-gradient( to top, ' +  this.values.button_gradient_bottom_hover + ', ' +  this.values.button_gradient_top_hover + ' )' );
				  }

				  if ( ! this.isDefault( 'button_border_color_hover' ) ) {
				    this.addCssProperty( button_hover, 'border-color',  this.values.button_border_color_hover );
				  }
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
