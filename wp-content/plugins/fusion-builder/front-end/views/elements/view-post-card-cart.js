var FusionPageBuilder = FusionPageBuilder || {};

( function () {


	jQuery( document ).ready( function () {

		// Post Card Cart Component View.
		FusionPageBuilder.fusion_post_card_cart = FusionPageBuilder.ElementView.extend( {

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

					// Split border width into 4.
					if ( 'undefined' === typeof params.button_details_border_top && 'undefined' !== typeof params.button_details_border_width && '' !== params.button_details_border_width ) {
						params.button_details_border_top    = parseInt( params.button_details_border_width ) + 'px';
						params.button_details_border_right  = params.button_details_border_top;
						params.button_details_border_bottom = params.button_details_border_top;
						params.button_details_border_left   = params.button_details_border_top;
						delete params.button_details_border_width;
					}
					this.model.set( 'params', params );
				}
			},

			afterPatch: function () {
				var $quantityBoxes = this.$el.find( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).find( '.qty' ),
					$form = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.variations_form' ) );

				if ( $quantityBoxes.length && 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaAddQuantityBoxes ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaAddQuantityBoxes( '.qty', $quantityBoxes );
				}

				if ( $form.length && 'function' === typeof $form.wc_variation_form ) {
					$form.wc_variation_form();
					$form.on( 'hide_variation', function( e ) {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( e.currentTarget ).find( '.avada-variation' ).closest( 'tr' ).addClass( 'awb-hide-element' );
					} ).on( 'found_variation.wc-variation-form', function( e ) {
						if ( jQuery.trim( jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( e.currentTarget ).find( '.avada-variation' ).text() ).length ) {
							jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( e.currentTarget ).find( '.avada-variation' ).closest( 'tr' ).removeClass( 'awb-hide-element' );
						}
					} );
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 3.3
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function ( atts ) {
				var attributes = {};

				this.values = atts.values;
				this.extras = atts.extras;
				this.query_data = atts.query_data;
				this.setIconDefaults();

				// Any extras that need passed on.
				attributes.cid = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.output = this.buildOutput( atts );
				attributes.styles = this.buildStyleBlock();

				return attributes;
			},

			/**
			 * Set default icons for text links
			 *
			 * @since  3.3
			 */
			setIconDefaults: function() {
				if ( 'custom' !== this.values.button_style ) {
					this.values.icon_position = 'left';
					this.values.button_icon   = 'fa-shopping-cart fas';
				}

				if ( 'custom' !== this.values.product_link_style ) {
					this.values.icon_details_position = 'left';
					this.values.button_details_icon   = 'fa-list-ul fas';
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function ( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-woo-cart fusion-post-card-cart',
					style: ''
				} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				attr[ 'class' ] += ' awb-variation-layout-' + values.variation_layout;

				if ( '' !== values.variation_text_align ) {
					attr[ 'class' ] += ' awb-variation-text-align-' + values.variation_text_align;
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
			buildOutput: function ( atts ) {
				var quantity = '',
						buttons = '',
						output = '';

				if ( 'yes' === atts.values.show_variations && 'variable' === this.getProductType() && 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.fusion_post_card_cart ) {
					output = atts.query_data.fusion_post_card_cart;
				}
				if ( 'yes' === atts.values.show_add_to_cart_button ) {
					buttons += this.buildAddToCart( );
				}
				if ( 'yes' === atts.values.show_product_link_button ) {
					buttons += this.buildProductDetails( );
				}
				if ( 'yes' === atts.values.show_quantity_input ) {
					quantity = this.buildQuantity( );
				}

				// Add wrapper.
				if ( 'yes' === atts.values.show_variations && 'variable' === this.getProductType() ) {
					quantity = '<div class="awb-post-card-cart-variation-wrapper">' + quantity;
				}

				output += quantity;
				if ( this.has_buttons_wrapper() ) {
					output += '<div class="fusion-post-card-cart-button-wrapper">';
				}
				output += buttons;
				if ( this.has_buttons_wrapper() ) {
					output += '</div>';
				}

				// Closing wrapper.
				if ( 'yes' === atts.values.show_variations && 'variable' === this.getProductType() ) {
					output += '</div>';
				}
				return output;
			},

			/**
			 * Builds Quantity
			 *
			 * @since  3.3
			 * @return {String}
			 */
			buildQuantity: function ( ) {
				var output = '<div class="fusion-post-card-cart-quantity">' +
				'<div class="quantity">' +
				'<label class="screen-reader-text" for="quantity_temp">Quis voluptas quos ut in quantity</label>' +
				'<input type="number" id="quantity_temp" class="input-text qty text" step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4" inputmode="numeric" />' +
				'</div></div>';
				return output;
			},

			/**
			 * Builds Add to cart button
			 *
			 * @since  3.3
			 * @return {String}
			 */
			buildAddToCart: function ( ) {
				var output = '',
					button_class = [ 'fusion-post-card-cart-add-to-cart' ];

				if ( '' === this.values.button_size ) {
					button_class.push( 'fusion-button-default-size' );
				}

				if ( 'custom' === this.values.button_style ) {
					button_class.push( 'button-default' );
				}

				output = '<a href="#" data-quantity="1" class="' + button_class.join( ' ' ) + '" aria-label="Add Temp Product" rel="nofollow">';
				if ( '' !== this.values.button_icon && 'left' === this.values.icon_position ) {
					output += '<i class="' + this.values.button_icon + ' button-icon-left" aria-hidden="true"></i>';
				}
				output += this.extras.add_to_cart_text;
				if ( '' !== this.values.button_icon && 'right' === this.values.icon_position ) {
					output += '<i class="' + this.values.button_icon + ' button-icon-right" aria-hidden="true"></i>';
				}
				output += '</a>';
				return output;
			},

			/**
			 * Builds Details/Quick view button
			 *
			 * @since  3.3
			 * @return {String}
			 */
			buildProductDetails: function ( ) {
				var output = '';
				var button_class = '' === this.values.button_details_size ? ' fusion-button-default-size' : '';
				button_class += 'custom' === this.values.product_link_style ? ' button-default' : '';
				if ( '1' === this.values.enable_quick_view || 'yes' === this.values.enable_quick_view ) {
					output = '<a href="#" class="fusion-post-card-cart-product-link fusion-quick-view' + button_class + '">';
					if ( '' !== this.values.button_details_icon && 'left' === this.values.icon_details_position ) {
						output += '<i class="' + this.values.button_details_icon + ' button-icon-left" aria-hidden="true"></i>';
					}
					output += this.extras.quick_view_text;
					if ( '' !== this.values.button_details_icon && 'right' === this.values.icon_details_position ) {
						output += '<i class="' + this.values.button_details_icon + ' button-icon-right" aria-hidden="true"></i>';
					}
					output += '</a>';
				} else {
					output = '<a href="#" class="fusion-post-card-cart-product-link show_details_button' + button_class + '">';
					if ( '' !== this.values.button_details_icon && 'left' === this.values.icon_details_position ) {
						output += '<i class="' + this.values.button_details_icon + ' button-icon-left" aria-hidden="true"></i>';
					}
					output += this.extras.details_text;
					if ( '' !== this.values.button_details_icon && 'right' === this.values.icon_details_position ) {
						output += '<i class="' + this.values.button_details_icon + ' button-icon-right" aria-hidden="true"></i>';
					}
				output += '</a>';
				}

				return output;
			},

			/**
			 * Checks if buttons wrapper needed
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {Boolean}
			 */
			has_buttons_wrapper: function () {
				return ( 'yes' === this.values.show_product_link_button || 'yes' === this.values.show_add_to_cart_button ) &&
					! ( 'floated' === this.values.cart_layout && 'floated' === this.values.buttons_layout && 'no' === this.values.buttons_stretch );
			},

			/**
			 * Builds styles.
			 *
			 * @since  3.3
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function () {
				var selector, quantity_input, hover_color, border_colors, quantity_buttons, quantity_both, height, width, quantity_font, hover_buttons, button, button_hover, button_size_map, button_dimensions, css, cart_layout_selector, fontStyles, select, arrow, both, color_swatch, image_swatch, button_swatch, swatches, active_swatches, hover_swatches, color_swatch_radius, image_swatch_radius, full_swatches;
				this.baseSelector = '.fusion-post-card-cart';
				this.dynamic_css = {};
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

				if ( !this.isDefault( 'margin_top' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-top', _.fusionGetValueWithUnit( this.values.margin_top ) );
				}

				if ( !this.isDefault( 'margin_right' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-right', _.fusionGetValueWithUnit( this.values.margin_right ) );
				}

				if ( !this.isDefault( 'margin_bottom' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-bottom', _.fusionGetValueWithUnit( this.values.margin_bottom ) );
				}

				if ( !this.isDefault( 'margin_left' ) ) {
					this.addCssProperty( this.baseSelector, 'margin-left', _.fusionGetValueWithUnit( this.values.margin_left ) );
				}

				selector = this.baseSelector + ' .fusion-post-card-cart-quantity';
				cart_layout_selector = this.baseSelector;

				if ( !this.isDefault( 'show_variations' ) ) {
					cart_layout_selector = this.baseSelector + ' .awb-post-card-cart-variation-wrapper';
				}
				if ( 'floated' === this.values.cart_layout ) {
					this.addCssProperty( cart_layout_selector, 'display', 'flex' );
					this.addCssProperty( cart_layout_selector, 'flex-direction', 'row' );
					this.addCssProperty( selector, 'flex-direction', 'row' );
					this.addCssProperty( cart_layout_selector, 'justify-content', this.values.justify );
					this.addCssProperty( cart_layout_selector, 'align-items', 'center' );

					if ( !this.isDefault( 'show_variations' ) ) {
						this.addCssProperty( this.baseSelector, 'display', 'inherit' );
					}
				} else {
					this.addCssProperty( selector, 'flex-direction', 'column' );
					this.addCssProperty( this.baseSelector, 'flex-direction', 'column' );
					this.addCssProperty( selector, 'display', 'flex' );
					this.addCssProperty( selector, 'align-items', this.values.align );
				}

				// Button wrapper if both buttons are used.
				if ( this.has_buttons_wrapper( ) ) {
					selector = this.baseSelector + ' .fusion-post-card-cart-button-wrapper';
					if ( 'floated' === this.values.buttons_layout ) {
						this.addCssProperty( selector, 'flex-direction', 'row' );
						this.addCssProperty( selector, 'align-items', 'center' );
						if ( 'stacked' === this.values.cart_layout ) {
							this.addCssProperty( selector, 'justify-content', this.values.buttons_justify );
						}

					} else if ( 'stacked' === this.values.buttons_layout ) {
						this.addCssProperty( selector, 'flex-direction', 'column' );
						this.addCssProperty( selector, 'align-items', this.values.buttons_alignment );
					}

					if ( 'yes' === this.values.buttons_stretch ) {
						this.addCssProperty( selector + ' a', 'justify-content', 'center' );

						// Stacked buttons next to quantity
						if ( 'floated' === this.values.cart_layout ) {
							if ( 'stacked' === this.values.buttons_layout ) {
								// Make the buttons the same width and wrapper expand..
								this.addCssProperty( selector, 'flex', '1' );
								this.addCssProperty( selector, 'align-items', 'stretch' );
							} else {
								// Both floated, button wrapper expand then buttons expand.
								this.addCssProperty( selector, 'flex', '1' );
								this.addCssProperty( selector + ' a', 'flex', '1' );
							}

						} else if ( 'stacked' === this.values.buttons_layout ) {
								// Make the buttons the same width.
								this.addCssProperty( selector, 'align-items', 'stretch' );
							} else {
								// Allow each button to grow equally.
								this.addCssProperty( selector + ' a', 'flex', '1' );
							}

					}

				}

				if ( 'custom' === this.values.quantity_style ) {
					quantity_input = '.fusion-body #main ' + this.baseSelector + ' .quantity input[type="number"].qty';
					quantity_buttons = '.fusion-body #main ' + this.baseSelector + ' .quantity input[type="button"]';
					quantity_both = [ quantity_input, quantity_buttons ];
					selector = this.baseSelector + ' .fusion-post-card-cart-quantity';
					if ( !this.isDefault( 'quantity_margin_top' ) ) {
						this.addCssProperty( selector, 'margin-top', _.fusionGetValueWithUnit( this.values.quantity_margin_top ) );
					}

					if ( !this.isDefault( 'quantity_margin_right' ) ) {
						this.addCssProperty( selector, 'margin-right', _.fusionGetValueWithUnit( this.values.quantity_margin_right ) );
					}

					if ( !this.isDefault( 'quantity_margin_bottom' ) ) {
						this.addCssProperty( selector, 'margin-bottom', _.fusionGetValueWithUnit( this.values.quantity_margin_bottom ) );
					}

					if ( !this.isDefault( 'quantity_margin_left' ) ) {
						this.addCssProperty( selector, 'margin-left', _.fusionGetValueWithUnit( this.values.quantity_margin_left ) );
					}

					height = '36px';
					if ( !this.isDefault( 'quantity_height' ) ) {
						height = _.fusionGetValueWithUnit( this.values.quantity_height );
						this.addCssProperty( quantity_both, 'height', height );
						this.addCssProperty( quantity_buttons, 'width', height );
					}

					width = '36px';
					if ( !this.isDefault( 'quantity_width' ) ) {
						width = _.fusionGetValueWithUnit( this.values.quantity_width );
						if ( false !== width.includes( '%' ) ) {
							this.addCssProperty( quantity_input, 'width', 'calc( 100% - ' + height + ' - ' + height + ' )' );
						} else {
							this.addCssProperty( quantity_input, 'width', width );
						}

					}

					if ( !this.isDefault( 'quantity_width' ) || !this.isDefault( 'quantity_height' ) ) {
						this.addCssProperty( this.baseSelector + ' .quantity', 'width', 'calc( ' + width + ' + ' + height + ' + ' + height + ' )' );
					}

					if ( !this.isDefault( 'quantity_radius_top_left' ) ) {
						this.addCssProperty( this.baseSelector + ' .quantity .minus', 'border-top-left-radius', _.fusionGetValueWithUnit( this.values.quantity_radius_top_left ) );
					}

					if ( !this.isDefault( 'quantity_radius_bottom_left' ) ) {
						this.addCssProperty( this.baseSelector + ' .quantity .minus', 'border-bottom-left-radius', _.fusionGetValueWithUnit( this.values.quantity_radius_bottom_left ) );
					}

					if ( !this.isDefault( 'quantity_radius_top_right' ) ) {
						this.addCssProperty( this.baseSelector + ' .quantity .plus', 'border-top-right-radius', _.fusionGetValueWithUnit( this.values.quantity_radius_top_right ) );
					}

					if ( !this.isDefault( 'quantity_radius_bottom_left' ) ) {
						this.addCssProperty( this.baseSelector + ' .quantity .plus', 'border-bottom-right-radius', _.fusionGetValueWithUnit( this.values.quantity_radius_bottom_right ) );
					}

					if ( !this.isDefault( 'quantity_font_size' ) ) {
						quantity_font = [ quantity_input, quantity_buttons, this.baseSelector + ' .quantity' ];
						this.addCssProperty( quantity_font, 'font-size', _.fusionGetValueWithUnit( this.values.quantity_font_size ) );
					}

					if ( !this.isDefault( 'quantity_color' ) ) {
						this.addCssProperty( quantity_input, 'color', this.values.quantity_color );
					}

					if ( !this.isDefault( 'quantity_background' ) ) {
						this.addCssProperty( quantity_input, 'background-color', this.values.quantity_background );
					}

					if ( !this.isDefault( 'quantity_border_sizes_top' ) ) {
						this.addCssProperty( quantity_input, 'border-top-width', _.fusionGetValueWithUnit( this.values.quantity_border_sizes_top ) );
					}

					if ( !this.isDefault( 'quantity_border_sizes_right' ) ) {
						this.addCssProperty( quantity_input, 'border-right-width', _.fusionGetValueWithUnit( this.values.quantity_border_sizes_right ) );
					}

					if ( !this.isDefault( 'quantity_border_sizes_bottom' ) ) {
						this.addCssProperty( quantity_input, 'border-bottom-width', _.fusionGetValueWithUnit( this.values.quantity_border_sizes_bottom ) );
					}

					if ( !this.isDefault( 'quantity_border_sizes_left' ) ) {
						this.addCssProperty( quantity_input, 'border-left-width', _.fusionGetValueWithUnit( this.values.quantity_border_sizes_left ) );
					}

					if ( !this.isDefault( 'quantity_border_color' ) ) {
						this.addCssProperty( quantity_input, 'border-color', this.values.quantity_border_color );
					}

					if ( !this.isDefault( 'qbutton_border_sizes_top' ) ) {
						this.addCssProperty( quantity_buttons, 'border-top-width', _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_top ) );
					}

					if ( !this.isDefault( 'qbutton_border_sizes_right' ) ) {
						this.addCssProperty( quantity_buttons, 'border-right-width', _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_right ) );
					}

					if ( !this.isDefault( 'qbutton_border_sizes_bottom' ) ) {
						this.addCssProperty( quantity_buttons, 'border-bottom-width', _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_bottom ) );
					}

					if ( !this.isDefault( 'qbutton_border_sizes_left' ) ) {
						this.addCssProperty( quantity_buttons, 'border-left-width', _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_left ) );
					}

					if ( !this.isDefault( 'qbutton_color' ) ) {
						this.addCssProperty( quantity_buttons, 'color', this.values.qbutton_color );
					}

					if ( !this.isDefault( 'qbutton_background' ) ) {
						this.addCssProperty( quantity_buttons, 'background-color', this.values.qbutton_background );
					}

					if ( !this.isDefault( 'qbutton_border_color' ) ) {
						this.addCssProperty( quantity_buttons, 'border-color', this.values.qbutton_border_color );
					}

					hover_buttons = [ quantity_buttons + ':hover', quantity_buttons + ':focus' ];
					// Quantity button hover text color.
					if ( !this.isDefault( 'qbutton_color_hover' ) ) {
						this.addCssProperty( hover_buttons, 'color', this.values.qbutton_color_hover );
					}

					if ( !this.isDefault( 'qbutton_background_hover' ) ) {
						this.addCssProperty( hover_buttons, 'background-color', this.values.qbutton_background_hover );
					}

					if ( !this.isDefault( 'qbutton_border_color_hover' ) ) {
						this.addCssProperty( hover_buttons, 'border-color', this.values.qbutton_border_color_hover );
					}

				}

				selector = [
					this.baseSelector + ' .fusion-post-card-cart-add-to-cart',
					this.baseSelector + '.awb-add-to-cart-style-link .fusion-post-card-cart-add-to-cart'
				];
				if ( !this.isDefault( 'button_margin_top' ) ) {
					this.addCssProperty( selector, 'margin-top', _.fusionGetValueWithUnit( this.values.button_margin_top ) );
				}

				if ( !this.isDefault( 'button_margin_right' ) ) {
					this.addCssProperty( selector, 'margin-right', _.fusionGetValueWithUnit( this.values.button_margin_right ) );
				}

				if ( !this.isDefault( 'button_margin_bottom' ) ) {
					this.addCssProperty( selector, 'margin-bottom', _.fusionGetValueWithUnit( this.values.button_margin_bottom ) );
				}

				if ( !this.isDefault( 'button_margin_left' ) ) {
					this.addCssProperty( selector, 'margin-left', _.fusionGetValueWithUnit( this.values.button_margin_left ) );
				}

				if ( 'custom' === this.values.button_style ) {
					button = '.fusion-body ' + this.baseSelector + ' .fusion-post-card-cart-add-to-cart';
					button_hover = button + ':hover';
					// Button size.
					if ( !this.isDefault( 'button_size' ) ) {
						if ( 'undefined' !== typeof button_size_map[ this.values.button_size ] ) {
							button_dimensions = button_size_map[ this.values.button_size ];
							this.addCssProperty( button, 'padding', button_dimensions.padding );
							this.addCssProperty( button, 'line-height', button_dimensions.line_height );
							this.addCssProperty( button, 'font-size', button_dimensions.font_size );
						}

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

					if ( !this.isDefault( 'button_color' ) ) {
						this.addCssProperty( button, 'color', this.values.button_color );
					}

					if ( ( 'undefined' !== typeof this.values.button_gradient_top && '' !== this.values.button_gradient_top ) ||
						( 'undefined' !== this.values.button_gradient_bottom && '' !== this.values.button_gradient_bottom ) ) {
						this.addCssProperty( button, 'background', this.values.button_gradient_top );
						this.addCssProperty( button, 'background-image', 'linear-gradient( to top, ' + this.values.button_gradient_bottom + ', ' + this.values.button_gradient_top + ' )' );
					}

					if ( !this.isDefault( 'button_border_color' ) ) {
						this.addCssProperty( button, 'border-color', this.values.button_border_color );
					}

					if ( !this.isDefault( 'button_color_hover' ) ) {
						this.addCssProperty( button_hover, 'color', this.values.button_color_hover );
					}

					if ( ( this.values.button_gradient_top_hover && '' !== this.values.button_gradient_top_hover ) ||
						( this.values.button_gradient_bottom_hover && '' !== this.values.button_gradient_bottom_hover ) ) {
						this.addCssProperty( button_hover, 'background', this.values.button_gradient_top_hover );
						this.addCssProperty( button_hover, 'background-image', 'linear-gradient( to top, ' + this.values.button_gradient_bottom_hover + ', ' + this.values.button_gradient_top_hover + ' )' );
					}

					if ( !this.isDefault( 'button_border_color_hover' ) ) {
						this.addCssProperty( button_hover, 'border-color', this.values.button_border_color_hover );
					}

				} else {
					// Link text color.
					if ( !this.isDefault( 'link_color' ) ) {
						this.addCssProperty( selector, 'color', this.values.link_color );
					}

					if ( !this.isDefault( 'link_font_size' ) ) {
						this.addCssProperty( selector, 'font-size', this.values.link_font_size );
					}

					selector = [
						this.baseSelector + ' .fusion-post-card-cart-add-to-cart:hover',
						this.baseSelector + '.awb-add-to-cart-style-link .fusion-post-card-cart-add-to-cart:hover'
					];

					if ( !this.isDefault( 'link_hover_color' ) ) {
						this.addCssProperty( selector, 'color', this.values.link_hover_color );
					}

				}

				// Product link button styling
				selector = this.baseSelector + ' .fusion-post-card-cart-product-link';
				if ( !this.isDefault( 'button_details_margin_top' ) ) {
					this.addCssProperty( selector, 'margin-top', _.fusionGetValueWithUnit( this.values.button_details_margin_top ) );
				}

				if ( !this.isDefault( 'button_details_margin_right' ) ) {
					this.addCssProperty( selector, 'margin-right', _.fusionGetValueWithUnit( this.values.button_details_margin_right ) );
				}

				if ( !this.isDefault( 'button_details_margin_bottom' ) ) {
					this.addCssProperty( selector, 'margin-bottom', _.fusionGetValueWithUnit( this.values.button_details_margin_bottom ) );
				}

				if ( !this.isDefault( 'button_details_margin_left' ) ) {
					this.addCssProperty( selector, 'margin-left', _.fusionGetValueWithUnit( this.values.button_details_margin_left ) );
				}

				if ( 'custom' === this.values.product_link_style ) {
					button = '.fusion-body ' + this.baseSelector + ' .fusion-post-card-cart-product-link';
					// Button size.
					if ( !this.isDefault( 'button_details_size' ) ) {
						if ( 'undefined' !== typeof button_size_map[ this.values.button_details_size ] ) {
							button_dimensions = button_size_map[ this.values.button_details_size ];
							this.addCssProperty( button, 'padding', button_dimensions.padding );
							this.addCssProperty( button, 'line-height', button_dimensions.line_height );
							this.addCssProperty( button, 'font-size', button_dimensions.font_size );
						}

					}

					if (  !  this.isDefault( 'button_details_border_top' ) ) {
						this.addCssProperty( button, 'border-top-width',  _.fusionGetValueWithUnit( this.values.button_details_border_top ) );
					}
					if (  !  this.isDefault( 'button_details_border_right' ) ) {
						this.addCssProperty( button, 'border-right-width',  _.fusionGetValueWithUnit( this.values.button_details_border_right ) );
					}
					if (  !  this.isDefault( 'button_details_border_bottom' ) ) {
						this.addCssProperty( button, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.button_details_border_bottom ) );
					}
					if (  !  this.isDefault( 'button_details_border_left' ) ) {
						this.addCssProperty( button, 'border-left-width',  _.fusionGetValueWithUnit( this.values.button_details_border_left ) );
					}

					if ( !this.isDefault( 'button_details_color' ) ) {
						this.addCssProperty( button, 'color', this.values.button_details_color );
					}

					if ( ( 'undefined' !== typeof this.values.button_details_gradient_top && '' !== this.values.button_details_gradient_top ) ||
					( 'undefined' !== typeof this.values.button_details_gradient_bottom && '' !== this.values.button_details_gradient_bottom ) ) {
						this.addCssProperty( button, 'background', this.values.button_details_gradient_top );
						this.addCssProperty( button, 'background-image', 'linear-gradient( to top, ' + this.values.button_details_gradient_bottom + ', ' + this.values.button_details_gradient_top + ' )' );
					}

					if ( !this.isDefault( 'button_details_border_color' ) ) {
						this.addCssProperty( button, 'border-color', this.values.button_details_border_color );
					}

					button_hover = button + ':hover';
					// Button hover text color.
					if ( !this.isDefault( 'button_details_color_hover' ) ) {
						this.addCssProperty( button_hover, 'color', this.values.button_details_color_hover );
					}

					if ( ( 'undefined' !== typeof this.values.button_details_gradient_top_hover && '' !== this.values.button_details_gradient_top_hover ) ||
					( 'undefined' !== typeof this.values.button_details_gradient_bottom_hover && '' !== this.values.button_details_gradient_bottom_hover ) ) {
						this.addCssProperty( button_hover, 'background', this.values.button_details_gradient_top_hover );
						this.addCssProperty( button_hover, 'background-image', 'linear-gradient( to top, ' + this.values.button_details_gradient_bottom_hover + ', ' + this.values.button_details_gradient_top_hover + ' )' );
					}

					if ( !this.isDefault( 'button_details_border_color_hover' ) ) {
						this.addCssProperty( button_hover, 'border-color', this.values.button_details_border_color_hover );
					}

				} else {
					// Link text color.
					if ( !this.isDefault( 'product_link_color' ) ) {
						this.addCssProperty( selector, 'color', this.values.product_link_color );
					}

					if ( !this.isDefault( 'product_link_hover_color' ) ) {
						this.addCssProperty( selector + ':hover', 'color', this.values.product_link_hover_color );
					}

					if ( !this.isDefault( 'product_link_font_size' ) ) {
						this.addCssProperty( selector, 'font-size', this.values.product_link_font_size );
					}

				}

				// Variation Styles.
				selector = this.baseSelector + ' .reset_variations';
				switch ( this.values.variation_clear ) {
					case 'inline':
						this.addCssProperty( selector, 'display', 'inline-block' );
						this.addCssProperty( selector, 'position', 'static' );
					break;

					case 'hide':
						this.addCssProperty( selector, 'display', 'none', true );
					break;
				}
				selector = this.baseSelector + '.awb-variation-layout-floated .variations tr .label';
				if ( 'floated' === this.values.variation_layout && !this.isDefault( 'variation_label_area_width' ) ) {
					this.addCssProperty( selector, 'width', _.fusionGetValueWithUnit( this.values.variation_label_area_width ) );
				}

				// Variations Typo.
				selector = this.baseSelector + ' .variations .label';
				if ( !this.isDefault( 'show_label' ) ) {
					this.addCssProperty( selector, 'display', 'none', true );
				}
				if ( !this.isDefault( 'label_color' ) ) {
					this.addCssProperty( selector, 'color',  this.values.label_color );
				}
				if ( !this.isDefault( 'label_font_size' ) ) {
					this.addCssProperty( selector, 'font-size',  _.fusionGetValueWithUnit( this.values.label_font_size ) );
				}
				if ( !this.isDefault( 'label_line_height' ) ) {
					this.addCssProperty( selector, 'line-height', this.values.label_line_height );
				}
				if ( !this.isDefault( 'label_text_transform' ) ) {
					this.addCssProperty( selector, 'text-transform', this.values.label_text_transform );
				}
				if ( !this.isDefault( 'label_letter_spacing' ) ) {
					this.addCssProperty( selector, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.label_letter_spacing ) );
				}
				fontStyles = _.fusionGetFontStyle( 'label_typography', this.values, 'object' );
				_.each( fontStyles, function( value, rule ) {
					this.addCssProperty( selector, rule, value );
				}, this );

				if ( !this.isDefault( 'select_style' ) ) {
					select = this.baseSelector + ' .variations select';
					arrow = this.baseSelector + ' .variations .select-arrow';
					both = [ select, arrow ];
					// Select height.
					if (  !  this.isDefault( 'select_height' ) ) {
						this.addCssProperty( select, 'height',  _.fusionGetValueWithUnit( this.values.select_height ) );
					}

					if (  !  this.isDefault( 'select_font_size' ) ) {
						this.addCssProperty( select, 'font-size',  _.fusionGetValueWithUnit( this.values.select_font_size ) );
						this.addCssProperty( arrow, 'font-size', 'calc( ( ' +  _.fusionGetValueWithUnit( this.values.select_font_size ) + ' ) * .75 )', true );
					}
					if ( !this.isDefault( 'select_line_height' ) ) {
						this.addCssProperty( select, 'line-height', this.values.select_line_height );
					}
					if ( !this.isDefault( 'select_text_transform' ) ) {
						this.addCssProperty( select, 'text-transform', this.values.select_text_transform );
					}
					if ( !this.isDefault( 'select_letter_spacing' ) ) {
						this.addCssProperty( select, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.select_letter_spacing ) );
					}
					fontStyles = _.fusionGetFontStyle( 'select_typography', this.values, 'object' );
					_.each( fontStyles, function( value, rule ) {
						this.addCssProperty( select, rule, value );
					}, this );

					if (  !  this.isDefault( 'select_color' ) ) {
						this.addCssProperty( both, 'color',  this.values.select_color );
					}

					if (  !  this.isDefault( 'select_background' ) ) {
						this.addCssProperty( select, 'background-color',  this.values.select_background );
					}

					if (  !  this.isDefault( 'select_border_color' ) ) {
						border_colors = [ select, select + ':focus' ];
						this.addCssProperty( border_colors, 'border-color',  this.values.select_border_color );
					}

					if (  !  this.isDefault( 'select_border_sizes_top' ) && '' !==  this.values.select_border_sizes_top ) {
						this.addCssProperty( select, 'border-top-width',  _.fusionGetValueWithUnit( this.values.select_border_sizes_top ) );
						this.addCssProperty( arrow, 'top',  _.fusionGetValueWithUnit( this.values.select_border_sizes_top ) );
					}

					if (  !  this.isDefault( 'select_border_sizes_right' ) && '' !==  this.values.select_border_sizes_right ) {
						this.addCssProperty( select, 'border-right-width',  _.fusionGetValueWithUnit( this.values.select_border_sizes_right ) );
					}

					if (  !  this.isDefault( 'select_border_sizes_bottom' ) && '' !==  this.values.select_border_sizes_bottom ) {
						this.addCssProperty( select, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.select_border_sizes_bottom ) );
						this.addCssProperty( arrow, 'bottom',  _.fusionGetValueWithUnit( this.values.select_border_sizes_bottom ) );
					}

					if (  !  this.isDefault( 'select_border_sizes_left' ) && '' !==  this.values.select_border_sizes_left ) {
						this.addCssProperty( select, 'border-left-width',  _.fusionGetValueWithUnit( this.values.select_border_sizes_left ) );
					}

					if (  !  this.isDefault( 'select_border_color' ) &&   !  this.isDefault( 'select_border_sizes_right' ) &&   !  this.isDefault( 'select_border_sizes_left' ) ) {
						this.addCssProperty( arrow, 'border-left',  _.fusionGetValueWithUnit( this.values.select_border_sizes_left ) + ' solid ' +  this.values.select_border_color );
					}

					if (  !  this.isDefault( 'border_radius_top_left' ) ) {
						this.addCssProperty( select, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.border_radius_top_left ) );
					}

					if (  !  this.isDefault( 'border_radius_top_right' ) ) {
						this.addCssProperty( select, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.border_radius_top_right ) );
					}

					if (  !  this.isDefault( 'border_radius_bottom_right' ) ) {
						this.addCssProperty( select, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.border_radius_bottom_right ) );
					}

					if (  !  this.isDefault( 'border_radius_bottom_left' ) ) {
						this.addCssProperty( select, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.border_radius_bottom_left ) );
					}

				}

				if ( !this.isDefault( 'swatch_style' ) && this.extras.woocommerce_variations ) {
					color_swatch = this.baseSelector + ' .variations .avada-color-select';
					image_swatch = this.baseSelector + ' .variations .avada-image-select';
					button_swatch = this.baseSelector + ' .variations .avada-button-select';
					swatches = [ color_swatch, image_swatch, button_swatch ];
					active_swatches = [ color_swatch + '[data-checked]', image_swatch + '[data-checked]', button_swatch + '[data-checked]' ];
					hover_swatches = [ color_swatch + ':hover', image_swatch + ':hover', button_swatch + ':hover', color_swatch + ':focus:not( [data-checked] )', image_swatch + ':focus:not( [data-checked] )', button_swatch + ':focus:not( [data-checked] )' ];
					// General swatch styling.
					if ( !this.isDefault( 'swatch_margin_top' ) ) {
						this.addCssProperty( swatches, 'margin-top',  _.fusionGetValueWithUnit( this.values.swatch_margin_top ) );
						}

						if ( !this.isDefault( 'swatch_margin_right' ) ) {
						this.addCssProperty( swatches, 'margin-right',  _.fusionGetValueWithUnit( this.values.swatch_margin_right ) );
						}

						if ( !this.isDefault( 'swatch_margin_bottom' ) ) {
						this.addCssProperty( swatches, 'margin-bottom',  _.fusionGetValueWithUnit( this.values.swatch_margin_bottom ) );
						}

						if ( !this.isDefault( 'swatch_margin_left' ) ) {
						this.addCssProperty( swatches, 'margin-left',  _.fusionGetValueWithUnit( this.values.swatch_margin_left ) );
						}

					if (  !  this.isDefault( 'swatch_background_color' ) ) {
						this.addCssProperty( swatches, 'background-color',  this.values.swatch_background_color );
					}

					if (  !  this.isDefault( 'swatch_background_color_active' ) ) {
						this.addCssProperty( active_swatches, 'background-color',  this.values.swatch_background_color_active );
					}

					if (  !  this.isDefault( 'swatch_border_sizes_top' ) && '' !==  this.values.swatch_border_sizes_top ) {
						this.addCssProperty( swatches, 'border-top-width',  _.fusionGetValueWithUnit( this.values.swatch_border_sizes_top ) );
					}

					if (  !  this.isDefault( 'swatch_border_sizes_right' ) && '' !==  this.values.swatch_border_sizes_right ) {
						this.addCssProperty( swatches, 'border-right-width',  _.fusionGetValueWithUnit( this.values.swatch_border_sizes_right ) );
					}

					if (  !  this.isDefault( 'swatch_border_sizes_bottom' ) && '' !==  this.values.swatch_border_sizes_bottom ) {
						this.addCssProperty( swatches, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.swatch_border_sizes_bottom ) );
					}

					if (  !  this.isDefault( 'swatch_border_sizes_left' ) && '' !==  this.values.swatch_border_sizes_left ) {
						this.addCssProperty( swatches, 'border-left-width',  _.fusionGetValueWithUnit( this.values.swatch_border_sizes_left ) );
					}

					if (  !  this.isDefault( 'swatch_border_color' ) ) {
						this.addCssProperty( swatches, 'border-color',  this.values.swatch_border_color );
					}

					if (  !  this.isDefault( 'swatch_border_color_active' ) ) {
						this.addCssProperty( active_swatches, 'border-color',  this.values.swatch_border_color_active );
						hover_color = jQuery.AWB_Color( this.values.swatch_border_color_active ).alpha( 0.5 ).toVarOrRgbaString();
						this.addCssProperty( hover_swatches, 'border-color', hover_color );
					}

					if (  !  this.isDefault( 'color_swatch_height' ) ) {
						this.addCssProperty( color_swatch, 'height',  _.fusionGetValueWithUnit( this.values.color_swatch_height ) );
					}

					if (  !  this.isDefault( 'color_swatch_width' ) ) {
						width = ( 'auto' ===  this.values.color_swatch_width ) ? 'auto' :  _.fusionGetValueWithUnit( this.values.color_swatch_width );
						this.addCssProperty( color_swatch, 'width', width );
					}

					if (  !  this.isDefault( 'color_swatch_padding_top' ) && '' !==  this.values.color_swatch_padding_top ) {
						this.addCssProperty( color_swatch, 'padding-top',  _.fusionGetValueWithUnit( this.values.color_swatch_padding_top ) );
					}

					if (  !  this.isDefault( 'color_swatch_padding_right' ) && '' !==  this.values.color_swatch_padding_right ) {
						this.addCssProperty( color_swatch, 'padding-right',  _.fusionGetValueWithUnit( this.values.color_swatch_padding_right ) );
					}

					if (  !  this.isDefault( 'color_swatch_padding_bottom' ) && '' !==  this.values.color_swatch_padding_bottom ) {
						this.addCssProperty( color_swatch, 'padding-bottom',  _.fusionGetValueWithUnit( this.values.color_swatch_padding_bottom ) );
					}

					if (  !  this.isDefault( 'color_swatch_padding_left' ) && '' !==  this.values.color_swatch_padding_left ) {
						this.addCssProperty( color_swatch, 'padding-left',  _.fusionGetValueWithUnit( this.values.color_swatch_padding_left ) );
					}
					color_swatch_radius = [
						color_swatch,
						color_swatch + ' span'
					];
					if (  !  this.isDefault( 'color_swatch_border_radius_top_left' ) ) {
						this.addCssProperty( color_swatch_radius, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.color_swatch_border_radius_top_left ) );
					}

					if (  !  this.isDefault( 'color_swatch_border_radius_top_right' ) ) {
						this.addCssProperty( color_swatch_radius, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.color_swatch_border_radius_top_right ) );
					}

					if (  !  this.isDefault( 'color_swatch_border_radius_bottom_right' ) ) {
						this.addCssProperty( color_swatch_radius, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.color_swatch_border_radius_bottom_right ) );
					}

					if (  !  this.isDefault( 'color_swatch_border_radius_bottom_left' ) ) {
						this.addCssProperty( color_swatch_radius, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.color_swatch_border_radius_bottom_left ) );
					}

					if (  !  this.isDefault( 'image_swatch_height' ) ) {
						this.addCssProperty( image_swatch, 'height',  _.fusionGetValueWithUnit( this.values.image_swatch_height ) );
					}

					if (  !  this.isDefault( 'image_swatch_width' ) ) {
						width = ( 'auto' ===  this.values.image_swatch_width ) ? 'auto' :  _.fusionGetValueWithUnit( this.values.image_swatch_width );
						this.addCssProperty( image_swatch, 'width', width );

						if ( 'auto' !== this.values.image_swatch_width ) {
							this.addCssProperty( image_swatch + ' img', 'width', '100%' );
						}
					}

					if (  !  this.isDefault( 'image_swatch_padding_top' ) && '' !==  this.values.image_swatch_padding_top ) {
						this.addCssProperty( image_swatch, 'padding-top',  _.fusionGetValueWithUnit( this.values.image_swatch_padding_top ) );
					}

					if (  !  this.isDefault( 'image_swatch_padding_right' ) && '' !==  this.values.image_swatch_padding_right ) {
						this.addCssProperty( image_swatch, 'padding-right',  _.fusionGetValueWithUnit( this.values.image_swatch_padding_right ) );
					}

					if (  !  this.isDefault( 'image_swatch_padding_bottom' ) && '' !==  this.values.image_swatch_padding_bottom ) {
						this.addCssProperty( image_swatch, 'padding-bottom',  _.fusionGetValueWithUnit( this.values.image_swatch_padding_bottom ) );
					}

					if (  !  this.isDefault( 'image_swatch_padding_left' ) && '' !==  this.values.image_swatch_padding_left ) {
						this.addCssProperty( image_swatch, 'padding-left',  _.fusionGetValueWithUnit( this.values.image_swatch_padding_left ) );
					}
					image_swatch_radius = [
						image_swatch,
						image_swatch + ' img'
					];
					if (  !  this.isDefault( 'image_swatch_border_radius_top_left' ) ) {
						this.addCssProperty( image_swatch_radius, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.image_swatch_border_radius_top_left ) );
					}

					if (  !  this.isDefault( 'image_swatch_border_radius_top_right' ) ) {
						this.addCssProperty( image_swatch_radius, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.image_swatch_border_radius_top_right ) );
					}

					if (  !  this.isDefault( 'image_swatch_border_radius_bottom_right' ) ) {
						this.addCssProperty( image_swatch_radius, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.image_swatch_border_radius_bottom_right ) );
					}

					if (  !  this.isDefault( 'image_swatch_border_radius_bottom_left' ) ) {
						this.addCssProperty( image_swatch_radius, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.image_swatch_border_radius_bottom_left ) );
					}

					if (  !  this.isDefault( 'button_swatch_height' ) ) {
						this.addCssProperty( button_swatch, 'height',  _.fusionGetValueWithUnit( this.values.button_swatch_height ) );
					}

					if (  !  this.isDefault( 'button_swatch_width' ) ) {
						width = ( 'auto' ===  this.values.button_swatch_width ) ? 'auto' :  _.fusionGetValueWithUnit( this.values.button_swatch_width );
						this.addCssProperty( button_swatch, 'width', width );
					}

					if (  !  this.isDefault( 'button_swatch_padding_top' ) && '' !==  this.values.button_swatch_padding_top ) {
						this.addCssProperty( button_swatch, 'padding-top',  _.fusionGetValueWithUnit( this.values.button_swatch_padding_top ) );
					}

					if (  !  this.isDefault( 'button_swatch_padding_right' ) && '' !==  this.values.button_swatch_padding_right ) {
						this.addCssProperty( button_swatch, 'padding-right',  _.fusionGetValueWithUnit( this.values.button_swatch_padding_right ) );
					}

					if (  !  this.isDefault( 'button_swatch_padding_bottom' ) && '' !==  this.values.button_swatch_padding_bottom ) {
						this.addCssProperty( button_swatch, 'padding-bottom',  _.fusionGetValueWithUnit( this.values.button_swatch_padding_bottom ) );
					}

					if (  !  this.isDefault( 'button_swatch_padding_left' ) && '' !==  this.values.button_swatch_padding_left ) {
						this.addCssProperty( button_swatch, 'padding-left',  _.fusionGetValueWithUnit( this.values.button_swatch_padding_left ) );
					}

					if (  !  this.isDefault( 'button_swatch_border_radius_top_left' ) ) {
						this.addCssProperty( button_swatch, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.button_swatch_border_radius_top_left ) );
					}

					if (  !  this.isDefault( 'button_swatch_border_radius_top_right' ) ) {
						this.addCssProperty( button_swatch, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.button_swatch_border_radius_top_right ) );
					}

					if (  !  this.isDefault( 'button_swatch_border_radius_bottom_right' ) ) {
						this.addCssProperty( button_swatch, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.button_swatch_border_radius_bottom_right ) );
					}

					if (  !  this.isDefault( 'button_swatch_border_radius_bottom_left' ) ) {
						this.addCssProperty( button_swatch, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.button_swatch_border_radius_bottom_left ) );
					}

					if (  !  this.isDefault( 'button_swatch_font_size' ) ) {
						this.addCssProperty( button_swatch, 'font-size',  _.fusionGetValueWithUnit( this.values.button_swatch_font_size ) );
					}

					if (  !  this.isDefault( 'button_swatch_color' ) ) {
						this.addCssProperty( button_swatch, 'color',  this.values.button_swatch_color );
					}

					if (  !  this.isDefault( 'button_swatch_color_active' ) ) {
						full_swatches = [ color_swatch + '[data-checked]', image_swatch + '[data-checked]', button_swatch + '[data-checked]', color_swatch + ':hover', image_swatch + ':hover', button_swatch + ':hover', color_swatch + ':focus', image_swatch + ':focus', button_swatch + ':focus' ];
						this.addCssProperty( full_swatches, 'color',  this.values.button_swatch_color_active );
					}

				}

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';
			},

			/**
			 * Get product type.
			 *
			 * @since  3.8.1
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			getProductType: function () {
				var product_type = 'simple';

				if ( 'undefined' !== typeof this.query_data && 'undefined' !== typeof this.query_data.product_type ) {
					product_type = this.query_data.product_type;
				}
				return product_type;
			}
		} );
	} );
}( jQuery ) );
