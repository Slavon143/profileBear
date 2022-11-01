/* eslint-disable no-redeclare */
/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Mini Cart Component View.
		FusionPageBuilder.fusion_woo_mini_cart = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.8
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
				attributes.cid = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.output = this.buildOutput( atts );

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'awb-woo-mini-cart awb-woo-mini-cart-' + this.model.get( 'cid' ),
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

				if ( 'no' !== values.show_buttons ) {
					if ( '' !== values.icon_position ) {
						attr[ 'class' ] += ' icon-position-' + values.icon_position;
					}

					if ( '' !== values.buttons_layout ) {
						attr[ 'class' ] += ' layout-' + values.buttons_layout;
					}

					if ( '' !== values.buttons_stretch ) {
						attr[ 'class' ] += ' button-span-' + values.buttons_stretch;
					}

					if ( '' !== values.link_style ) {
						attr[ 'class' ] += ' link-style-' + values.link_style;
					}

					if ( 'button' === values.link_style ) {
						attr[ 'class' ] += '' !== values.view_cart_button_size ? ' view-cart-button-size-' + values.view_cart_button_size : '';
						attr[ 'class' ] += '' !== values.checkout_button_size ? ' checkout-button-size-' + values.checkout_button_size : '';
					}

				} else {
					attr[ 'class' ] += ' hide-buttons';
				}

				if ( 'yes' !== values.show_subtotal ) {
					attr[ 'class' ] += ' hide-subtotal';
				}

				if ( 'yes' !== values.show_remove_icon ) {
					attr[ 'class' ] += ' hide-remove-icon';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr.style += this.getStyleVariables( values );

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds output.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.awb-woo-mini-cart' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_mini_cart ) {
					output = atts.query_data.woo_mini_cart;
				}

				return output;
			},

			/**
			 * Gets style variables.
			 *
			 * @since  3.8
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getStyleVariables: function( values ) {
				// variables into current scope
				var styles = '',
				text_styles;

				if ( ! this.isDefault( 'images_max_width' ) ) {
				  styles += '--awb-image-max-width:' + _.fusionGetValueWithUnit( values.images_max_width ) + ';';
				}

				if ( ! this.isDefault( 'remove_icon_color' ) ) {
				  styles += '--awb-remove-icon-color:' + values.remove_icon_color + ';';
				}

				if ( ! this.isDefault( 'remove_icon_bg_color' ) ) {
				  styles += '--awb-remove-icon-bg-color:' + values.remove_icon_bg_color + ';';
				}

				if ( ! this.isDefault( 'remove_icon_hover_color' ) ) {
				  styles += '--awb-remove-icon-hover-color:' + values.remove_icon_hover_color + ';';
				}

				if ( ! this.isDefault( 'remove_icon_hover_bg_color' ) ) {
				  styles += '--awb-remove-icon-hover-bg-color:' + values.remove_icon_hover_bg_color + ';';
				}

				if ( ! this.isDefault( 'separator_color' ) ) {
				  styles += '--awb-separator-color:' + values.separator_color + ';';
				}

				text_styles = _.fusionGetFontStyle( 'product_title_font', values, 'array' );
				jQuery.each( text_styles, function( rule, value ) {
				  var value = text_styles[ rule ];

				  styles += '--awb-product-title-' + rule.replace( '_', '-' ) + ':' + value + ';';
				} );

				if ( ! this.isDefault( 'product_title_font_size' ) ) {
				  styles += '--awb-product-title-font-size:' + _.fusionGetValueWithUnit( values.product_title_font_size ) + ';';
				}

				if ( ! this.isDefault( 'product_title_line_height' ) ) {
				  styles += '--awb-product-title-line-height:' + values.product_title_line_height + ';';
				}

				if ( ! this.isDefault( 'product_title_letter_spacing' ) ) {
				  styles += '--awb-product-title-letter-spacing:' + _.fusionGetValueWithUnit( values.product_title_letter_spacing ) + ';';
				}

				if ( ! this.isDefault( 'product_title_text_transform' ) ) {
				  styles += '--awb-product-title-text-transform:' + values.product_title_text_transform + ';';
				}

				if ( ! this.isDefault( 'product_title_color' ) ) {
				  styles += '--awb-product-title-color:' + values.product_title_color + ';';
				}

				if ( ! this.isDefault( 'product_title_hover_color' ) ) {
				  styles += '--awb-product-title-hover-color:' + values.product_title_hover_color + ';';
				}

				text_styles = _.fusionGetFontStyle( 'product_price_font', values, 'array' );
				jQuery.each( text_styles, function( rule, value ) {
				  var value = text_styles[ rule ];

				  styles += '--awb-product-price-' + rule.replace( '_', '-' ) + ':' + value + ';';
				} );

				if ( ! this.isDefault( 'product_price_font_size' ) ) {
				  styles += '--awb-product-price-font-size:' + _.fusionGetValueWithUnit( values.product_price_font_size ) + ';';
				}

				if ( ! this.isDefault( 'product_price_line_height' ) ) {
				  styles += '--awb-product-price-line-height:' + values.product_price_line_height + ';';
				}

				if ( ! this.isDefault( 'product_price_letter_spacing' ) ) {
				  styles += '--awb-product-price-letter-spacing:' + _.fusionGetValueWithUnit( values.product_price_letter_spacing ) + ';';
				}

				if ( ! this.isDefault( 'product_price_color' ) ) {
				  styles += '--awb-product-price-color:' + values.product_price_color + ';';
				}

				if ( 'no' !== values.show_subtotal ) {
				  // Subtotal alignment.
				  if ( ! this.isDefault( 'subtotal_alignment' ) ) {
				    styles += '--awb-subtotal-alignment:' + values.subtotal_alignment + ';';
				  }

				  text_styles = _.fusionGetFontStyle( 'subtotal_text_font', values, 'array' );
				  jQuery.each( text_styles, function( rule, value ) {
				    var value = text_styles[ rule ];

				    styles += '--awb-subtotal-text-' + rule.replace( '_', '-' ) + ':' + value + ';';
				  } );

				  if ( ! this.isDefault( 'subtotal_text_font_size' ) ) {
				    styles += '--awb-subtotal-text-font-size:' + _.fusionGetValueWithUnit( values.subtotal_text_font_size ) + ';';
				  }

				  if ( ! this.isDefault( 'subtotal_text_line_height' ) ) {
				    styles += '--awb-subtotal-text-line-height:' + values.subtotal_text_line_height + ';';
				  }

				  if ( ! this.isDefault( 'subtotal_text_letter_spacing' ) ) {
				    styles += '--awb-subtotal-text-letter-spacing:' + _.fusionGetValueWithUnit( values.subtotal_text_letter_spacing ) + ';';
				  }

				  if ( ! this.isDefault( 'subtotal_text_color' ) ) {
				    styles += '--awb-subtotal-text-color:' + values.subtotal_text_color + ';';
				  }

				  text_styles = _.fusionGetFontStyle( 'subtotal_amount_font', values, 'array' );
				  jQuery.each( text_styles, function( rule, value ) {
				    var value = text_styles[ rule ];

				    styles += '--awb-subtotal-amount-' + rule.replace( '_', '-' ) + ':' + value + ';';
				  } );

				  if ( ! this.isDefault( 'subtotal_amount_font_size' ) ) {
				    styles += '--awb-subtotal-amount-font-size:' + _.fusionGetValueWithUnit( values.subtotal_amount_font_size ) + ';';
				  }

				  if ( ! this.isDefault( 'subtotal_amount_line_height' ) ) {
				    styles += '--awb-subtotal-amount-line-height:' + values.subtotal_amount_line_height + ';';
				  }

				  if ( ! this.isDefault( 'subtotal_amount_letter_spacing' ) ) {
				    styles += '--awb-subtotal-amount-letter-spacing:' + _.fusionGetValueWithUnit( values.subtotal_amount_letter_spacing ) + ';';
				  }

				  if ( ! this.isDefault( 'subtotal_amount_color' ) ) {
				    styles += '--awb-subtotal-amount-color:' + values.subtotal_amount_color + ';';
				  }
				}

				if ( 'no' !== values.show_buttons ) {
				  // View cart text styles.
				  text_styles = _.fusionGetFontStyle( 'view_cart_font', values, 'array' );
				  jQuery.each( text_styles, function( rule, value ) {
				    var value = text_styles[ rule ];

				    styles += '--awb-view-cart-' + rule.replace( '_', '-' ) + ':' + value + ';';
				  } );

				  if ( ! this.isDefault( 'view_cart_font_size' ) ) {
				    styles += '--awb-view-cart-font-size:' + _.fusionGetValueWithUnit( values.view_cart_font_size ) + ';';
				  }

				  if ( ! this.isDefault( 'view_cart_line_height' ) ) {
				    styles += '--awb-view-cart-line-height:' + values.view_cart_line_height + ';';
				  }

				  if ( ! this.isDefault( 'view_cart_letter_spacing' ) ) {
				    styles += '--awb-view-cart-letter-spacing:' + _.fusionGetValueWithUnit( values.view_cart_letter_spacing ) + ';';
				  }

				  if ( ! this.isDefault( 'view_cart_text_transform' ) ) {
				    styles += '--awb-view-cart-text-transform:' + values.view_cart_text_transform + ';';
				  }

				  if ( 'link' === values.link_style ) {
				    if ( ! this.isDefault( 'view_cart_link_color' ) ) {
				      styles += '--awb-view-cart-link-color:' + values.view_cart_link_color + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_link_hover_color' ) ) {
				      styles += '--awb-view-cart-link-hover-color:' + values.view_cart_link_hover_color + ';';
				    }

				  }

				  if ( 'button' === values.link_style ) {
				    // Button border width.
				    if ( ! this.isDefault( 'view_cart_button_border_top' ) ) {
				      styles += '--awb-view-cart-border-top-width:' + _.fusionGetValueWithUnit( values.view_cart_button_border_top ) + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_border_right' ) ) {
				      styles += '--awb-view-cart-border-right-width:' + _.fusionGetValueWithUnit( values.view_cart_button_border_right ) + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_border_bottom' ) ) {
				      styles += '--awb-view-cart-border-bottom-width:' + _.fusionGetValueWithUnit( values.view_cart_button_border_bottom ) + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_border_left' ) ) {
				      styles += '--awb-view-cart-border-left-width:' + _.fusionGetValueWithUnit( values.view_cart_button_border_left ) + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_gradient_top' ) ||  ! this.isDefault( 'view_cart_button_gradient_bottom' ) ) {
				      styles += '--awb-view-cart-button-background:' + values.view_cart_button_gradient_top + ';';
				      styles += '--awb-view-cart-button-background-image:linear-gradient( to top, ' + values.view_cart_button_gradient_bottom + ', ' + values.view_cart_button_gradient_top + ' );';
				    }

				    if ( ! this.isDefault( 'view_cart_button_border_color' ) ) {
				      styles += '--awb-view-cart-border-color:' + values.view_cart_button_border_color + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_gradient_top_hover' ) ||  ! this.isDefault( 'view_cart_button_gradient_bottom_hover' ) ) {
				      styles += '--awb-view-cart-button-hover-background:' + values.view_cart_button_gradient_top_hover + ';';
				      styles += '--awb-view-cart-button-hover-background-image:linear-gradient( to top, ' + values.view_cart_button_gradient_bottom_hover + ', ' + values.view_cart_button_gradient_top_hover + ' );';
				    }

				    if ( ! this.isDefault( 'view_cart_button_border_color_hover' ) ) {
				      styles += '--awb-view-cart-hover-border-color:' + values.view_cart_button_border_color_hover + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_color' ) ) {
				      styles += '--awb-view-cart-button-color:' + values.view_cart_button_color + ';';
				    }

				    if ( ! this.isDefault( 'view_cart_button_color_hover' ) ) {
				      styles += '--awb-view-cart-button-hover-color:' + values.view_cart_button_color_hover + ';';
				    }

				  }

				  text_styles = _.fusionGetFontStyle( 'checkout_font', values, 'array' );
				  jQuery.each( text_styles, function( rule, value ) {
				    var value = text_styles[ rule ];

				    styles += '--awb-checkout-' + rule.replace( '_', '-' ) + ':' + value + ';';
				  } );

				  if ( ! this.isDefault( 'checkout_font_size' ) ) {
				    styles += '--awb-checkout-font-size:' + _.fusionGetValueWithUnit( values.checkout_font_size ) + ';';
				  }

				  if ( ! this.isDefault( 'checkout_line_height' ) ) {
				    styles += '--awb-checkout-line-height:' + values.checkout_line_height + ';';
				  }

				  if ( ! this.isDefault( 'checkout_letter_spacing' ) ) {
				    styles += '--awb-checkout-letter-spacing:' + _.fusionGetValueWithUnit( values.checkout_letter_spacing ) + ';';
				  }

				  if ( ! this.isDefault( 'checkout_text_transform' ) ) {
				    styles += '--awb-checkout-text-transform:' + values.checkout_text_transform + ';';
				  }

				  if ( 'link' === values.link_style ) {
				    if ( ! this.isDefault( 'checkout_link_color' ) ) {
				      styles += '--awb-checkout-link-color:' + values.checkout_link_color + ';';
				    }

				    if ( ! this.isDefault( 'checkout_link_hover_color' ) ) {
				      styles += '--awb-checkout-link-hover-color:' + values.checkout_link_hover_color + ';';
				    }

				  }

				  if ( 'button' === values.link_style ) {
				    // Button border width.
				    if ( ! this.isDefault( 'checkout_button_border_top' ) ) {
				      styles += '--awb-checkout-border-top-width:' + _.fusionGetValueWithUnit( values.checkout_button_border_top ) + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_border_right' ) ) {
				      styles += '--awb-checkout-border-right-width:' + _.fusionGetValueWithUnit( values.checkout_button_border_right ) + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_border_bottom' ) ) {
				      styles += '--awb-checkout-border-bottom-width:' + _.fusionGetValueWithUnit( values.checkout_button_border_bottom ) + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_border_left' ) ) {
				      styles += '--awb-checkout-border-left-width:' + _.fusionGetValueWithUnit( values.checkout_button_border_left ) + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_gradient_top' ) ||  ! this.isDefault( 'checkout_button_gradient_bottom' ) ) {
				      styles += '--awb-checkout-button-background:' + values.checkout_button_gradient_top + ';';
				      styles += '--awb-checkout-button-background-image:linear-gradient( to top, ' + values.checkout_button_gradient_bottom + ', ' + values.checkout_button_gradient_top + ' );';
				    }

				    if ( ! this.isDefault( 'checkout_button_border_color' ) ) {
				      styles += '--awb-checkout-border-color:' + values.checkout_button_border_color + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_gradient_top_hover' ) ||  ! this.isDefault( 'checkout_button_gradient_bottom_hover' ) ) {
				      styles += '--awb-checkout-button-hover-background:' + values.checkout_button_gradient_top_hover + ';';
				      styles += '--awb-checkout-button-hover-background-image:linear-gradient( to top, ' + values.checkout_button_gradient_bottom_hover + ', ' + values.checkout_button_gradient_top_hover + ' );';
				    }

				    if ( ! this.isDefault( 'checkout_button_border_color_hover' ) ) {
				      styles += '--awb-checkout-hover-border-color:' + values.checkout_button_border_color_hover + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_color' ) ) {
				      styles += '--awb-checkout-button-color:' + values.checkout_button_color + ';';
				    }

				    if ( ! this.isDefault( 'checkout_button_color_hover' ) ) {
				      styles += '--awb-checkout-button-hover-color:' + values.checkout_button_color_hover + ';';
				    }
				  }

				  if ( ! this.isDefault( 'links_margin_top' ) ) {
				    styles += '--awb-link-margin-top:' + _.fusionGetValueWithUnit( values.links_margin_top ) + ';';
				  }

				  if ( ! this.isDefault( 'links_margin_bottom' ) ) {
				    styles += '--awb-link-margin-bottom:' + _.fusionGetValueWithUnit( values.links_margin_bottom ) + ';';
				  }

				  if ( ! this.isDefault( 'links_margin_left' ) ) {
				    styles += '--awb-link-margin-left:' + _.fusionGetValueWithUnit( values.links_margin_left ) + ';';
				  }

				  if ( ! this.isDefault( 'links_margin_right' ) ) {
				    styles += '--awb-link-margin-right:' + _.fusionGetValueWithUnit( values.links_margin_right ) + ';';
				  }

				  if ( 'floated' === values.buttons_layout && '' !== values.buttons_justify ) {
				    styles += '--awb-links-justify:' + values.buttons_justify + ';';
				  }

				  if ( 'stacked' === values.buttons_layout && '' !== values.buttons_alignment ) {
				    styles += '--awb-links-alignment:' + values.buttons_alignment + ';';
				  }

				}

				return styles;
			}
		} );
	} );
}( jQuery ) );
