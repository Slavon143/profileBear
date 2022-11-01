/* global fusionSanitize */
/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Cart Component View.
		FusionPageBuilder.fusion_tb_woo_cart = FusionPageBuilder.ElementView.extend( {
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

				this.variationMarkup = this.$el.length && this.$el.find( '.single_variation_wrap' ).length ? this.$el.find( '.single_variation_wrap' ).html() : '';
			},

			beforePatch: function() {
				this.variationMarkup = this.$el.length && this.$el.find( '.single_variation_wrap' ).length ? this.$el.find( '.single_variation_wrap' ).html() : '';
			},

			afterPatch: function() {
				var $form = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.variations_form' ) ),
					self  = this;

				setTimeout( function() {
					self._refreshJs();
				}, 10 );

				if ( $form.length && 'function' === typeof $form.wc_variation_form ) {
					$form.wc_variation_form();
				}

				if ( 'string' === typeof this.variationMarkup && '' !== this.variationMarkup ) {
					setTimeout( function() {
						self.$el.find( '.single_variation_wrap' ).html( self.variationMarkup );
						self.$el.find( '.single_variation' ).css( 'display', 'flex' );
					}, 300 );
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
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = {
					'class': 'fusion-woo-cart fusion-woo-cart-' + this.model.get( 'cid' ),
					'data-layout': values.variation_layout
				};

				if ( ! this.$el.closest( 'body' ).hasClass( 'woocommerce' ) ) {
					attr[ 'class' ] += ' woocommerce';
				}
				attr =  _.fusionVisibilityAtts( values.hide_on_mobile, attr );
				attr = _.fusionAnimations( values, attr );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				if ( 'no' === values.show_price ) {
					attr[ 'class' ] += ' hide-price';
				}

				if ( 'no' === values.show_stock ) {
					attr[ 'class' ] += ' hide-stock';
				}

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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-cart' ).html();
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
			buildStyleBlock: function( values ) {
				// variables into current scope
				var table, table_td, stock, label, table_tr,
					self = this,
					headingStyles = {},
					select, arrow, both, border_colors, color_swatch, image_swatch, button_swatch, swatches, active_swatches, hover_swatches, direction, hover_color, width, full_swatches, info, description, prices, sales, variation_clear, button_wrapper, quantity_input, quantity_buttons, quantity_both, height, quantity_font, hover_buttons, button, button_size_map, button_dimensions, button_hover, css, image_swatch_radius, color_swatch_radius, map_flex,
					topMargin, bottomMargin, button_wrapper_quantity;

				this.baseSelector = '.fusion-woo-cart-' + this.model.get( 'cid' );
				this.dynamic_css  = {};
				// Variation margins.
				table =  this.baseSelector + ' table.variations';
				if ( !this.isDefault( 'margin_top' ) ) {
				  this.addCssProperty( table, 'margin-top',  _.fusionGetValueWithUnit( this.values.margin_top ) );
				}

				if ( !this.isDefault( 'margin_right' ) ) {
				  this.addCssProperty( table, 'margin-right',  _.fusionGetValueWithUnit( this.values.margin_right ) );
				}

				if ( !this.isDefault( 'margin_bottom' ) ) {
				  this.addCssProperty( table, 'margin-bottom',  _.fusionGetValueWithUnit( this.values.margin_bottom ) );
				}

				if ( !this.isDefault( 'margin_left' ) ) {
				  this.addCssProperty( table, 'margin-left',  _.fusionGetValueWithUnit( this.values.margin_left ) );
				}

				table_td =  this.baseSelector + ' table tr > *';
				// Border size.
				if ( !this.isDefault( 'border_sizes_top' ) ) {
				  this.addCssProperty( table_td, 'border-top-width',  _.fusionGetValueWithUnit( this.values.border_sizes_top ) );
				}

				if ( !this.isDefault( 'border_sizes_right' ) ) {
				  this.addCssProperty( table_td, 'border-right-width',  _.fusionGetValueWithUnit( this.values.border_sizes_right ) );
				}

				if ( !this.isDefault( 'border_sizes_bottom' ) ) {
				  this.addCssProperty( table_td, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.border_sizes_bottom ) );
				}

				if ( !this.isDefault( 'border_sizes_left' ) ) {
				  this.addCssProperty( table_td, 'border-left-width',  _.fusionGetValueWithUnit( this.values.border_sizes_left ) );
				}

				if ( !this.isDefault( 'border_color' ) ) {
				  this.addCssProperty( table_td, 'border-color',  this.values.border_color );
				}

				if ( !this.isDefault( 'cell_padding_top' ) ) {
				  this.addCssProperty( table_td, 'padding-top',  _.fusionGetValueWithUnit( this.values.cell_padding_top ) );
				}

				if ( !this.isDefault( 'cell_padding_right' ) ) {
				  this.addCssProperty( table_td, 'padding-right',  _.fusionGetValueWithUnit( this.values.cell_padding_right ) );
				}

				if ( !this.isDefault( 'cell_padding_bottom' ) ) {
				  this.addCssProperty( table_td, 'padding-bottom',  _.fusionGetValueWithUnit( this.values.cell_padding_bottom ) );
				}

				if ( !this.isDefault( 'cell_padding_left' ) ) {
				  this.addCssProperty( table_td, 'padding-left',  _.fusionGetValueWithUnit( this.values.cell_padding_left ) );
				}

				if ( !this.isDefault( 'cell_background' ) ) {
				  this.addCssProperty( table_td, 'background-color',  this.values.cell_background );
				}

				label =  this.baseSelector + ' tr .label';
				if ( 'floated' !== this.values.variation_layout ) {
				  table_tr =  this.baseSelector + ' table tr';
				  this.addCssProperty( table_tr, 'display', 'flex' );
				  this.addCssProperty( table_tr, 'flex-direction', 'column' );
				  this.addCssProperty( table_tr, 'width', '100%' );

				  this.addCssProperty( this.baseSelector + '.fusion-woo-cart tr:not(:first-of-type) > *,' + this.baseSelector + '.fusion-woo-cart  th + td,' + this.baseSelector + '.fusion-woo-cart td + td', 'border-top', 'none', true );

				} else {
					this.addCssProperty( this.baseSelector + '.fusion-woo-cart tr:not(:first-of-type) > *,' + this.baseSelector + '.fusion-woo-cart  th + td,' + this.baseSelector + '.fusion-woo-cart td + td', 'border-top-width',  _.fusionGetValueWithUnit( this.values.border_sizes_top ), true );
					this.addCssProperty( this.baseSelector + '.fusion-woo-cart tr:not(:first-of-type) > *,' + this.baseSelector + '.fusion-woo-cart  th + td,' + this.baseSelector + '.fusion-woo-cart td + td', 'border-top-style', 'solid', true );
					this.addCssProperty( this.baseSelector + '.fusion-woo-cart tr:not(:first-of-type) > *,' + this.baseSelector + '.fusion-woo-cart  th + td,' + this.baseSelector + '.fusion-woo-cart td + td', 'border-top-color', this.values.border_color, true );

					if ( ! this.isDefault( 'label_area_width' ) ) {
				  		this.addCssProperty( label, 'width',  _.fusionGetValueWithUnit( this.values.label_area_width ) );
					}
				}

				if ( !this.isDefault( 'text_align' ) ) {
				  this.addCssProperty( label, 'text-align',  this.values.text_align );

				  map_flex = {
					center: 'center',
					left: ( jQuery( 'body' ).hasClass( 'rtl' ) ? 'flex-end' : 'flex-start' ),
					right: ( jQuery( 'body' ).hasClass( 'rtl' ) ? 'flex-start' : 'flex-end' )
				};
				 this.addCssProperty( table + ' .avada-select-wrapper', 'justify-content', map_flex[ this.values.text_align ] );
				}

				// Label text styling, share with grouped.
				label = [
					this.baseSelector + ' tr .label',
					this.baseSelector + ' .woocommerce-grouped-product-list label',
					this.baseSelector + ' .woocommerce-grouped-product-list label a',
					this.baseSelector + ' .woocommerce-grouped-product-list .amount'
				];

				if ( !this.isDefault( 'label_color' ) ) {
				  this.addCssProperty( label, 'color',  this.values.label_color );
				}

				if ( !this.isDefault( 'label_font_size' ) ) {
				  this.addCssProperty( label, 'font-size',  _.fusionGetValueWithUnit( this.values.label_font_size ) );
				}

				if ( !this.isDefault( 'label_line_height' ) ) {
					this.addCssProperty( label, 'line-height', this.values.label_line_height );
				}

				if ( !this.isDefault( 'label_text_transform' ) ) {
					this.addCssProperty( label, 'text-transform', this.values.label_text_transform );
				}

				if ( !this.isDefault( 'label_letter_spacing' ) ) {
					this.addCssProperty( label, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.label_letter_spacing ) );
				}

				headingStyles = _.fusionGetFontStyle( 'label_typography', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( label, rule, value );
				} );

				if ( !this.isDefault( 'select_style' ) ) {
				  select = table + ' select';
				  arrow = table + ' .select-arrow';
				  both = [ select, arrow ];
				  // Select height.
				  if (  !  this.isDefault( 'select_height' ) ) {
				    this.addCssProperty( select, 'height',  _.fusionGetValueWithUnit( this.values.select_height ) );
				  }

				  if (  !  this.isDefault( 'select_font_size' ) ) {
				    this.addCssProperty( select, 'font-size',  _.fusionGetValueWithUnit( this.values.select_font_size ) );
				    this.addCssProperty( arrow, 'font-size', 'calc( ( ' +  _.fusionGetValueWithUnit( this.values.select_font_size ) + ' ) * .75 )', true );
				  }

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
				  color_swatch = table + ' .avada-color-select';
				  image_swatch = table + ' .avada-image-select';
				  button_swatch = table + ' .avada-button-select';
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

				info =  this.baseSelector + ' .woocommerce-variation';
				// Info padding.
				if ( !this.isDefault( 'info_padding_top' ) ) {
				  this.addCssProperty( info, 'padding-top',  _.fusionGetValueWithUnit( this.values.info_padding_top ) );
				}

				if ( !this.isDefault( 'info_padding_right' ) ) {
				  this.addCssProperty( info, 'padding-right',  _.fusionGetValueWithUnit( this.values.info_padding_right ) );
				}

				if ( !this.isDefault( 'info_padding_bottom' ) ) {
				  this.addCssProperty( info, 'padding-bottom',  _.fusionGetValueWithUnit( this.values.info_padding_bottom ) );
				}

				if ( !this.isDefault( 'info_padding_left' ) ) {
				  this.addCssProperty( info, 'padding-left',  _.fusionGetValueWithUnit( this.values.info_padding_left ) );
				}

				if ( !this.isDefault( 'info_background' ) ) {
				  this.addCssProperty( info, 'background-color',  this.values.info_background );
				}

				if ( !this.isDefault( 'info_border_sizes_top' ) ) {
				  this.addCssProperty( info, 'border-top-width',  _.fusionGetValueWithUnit( this.values.info_border_sizes_top ) );
				}

				if ( !this.isDefault( 'info_border_sizes_right' ) ) {
				  this.addCssProperty( info, 'border-right-width',  _.fusionGetValueWithUnit( this.values.info_border_sizes_right ) );
				}

				if ( !this.isDefault( 'info_border_sizes_bottom' ) ) {
				  this.addCssProperty( info, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.info_border_sizes_bottom ) );
				}

				if ( !this.isDefault( 'info_border_sizes_left' ) ) {
				  this.addCssProperty( info, 'border-left-width',  _.fusionGetValueWithUnit( this.values.info_border_sizes_left ) );
				}

				if ( !this.isDefault( 'info_border_color' ) ) {
				  this.addCssProperty( info, 'border-color',  this.values.info_border_color );
				}

				if ( !this.isDefault( 'info_border_radius_top_left' ) ) {
				  this.addCssProperty( info, 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.info_border_radius_top_left ) );
				}

				if ( !this.isDefault( 'info_border_radius_top_right' ) ) {
				  this.addCssProperty( info, 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.info_border_radius_top_right ) );
				}

				if ( !this.isDefault( 'info_border_radius_bottom_right' ) ) {
				  this.addCssProperty( info, 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.info_border_radius_bottom_right ) );
				}

				if ( !this.isDefault( 'info_border_radius_bottom_left' ) ) {
				  this.addCssProperty( info, 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.info_border_radius_bottom_left ) );
				}

				description = info + ' .woocommerce-variation-description';

				if ( !this.isDefault( 'info_align' ) ) {
				  this.addCssProperty( info, 'justify-content',  this.values.info_align );

					direction = jQuery( 'body' ).hasClass( 'rtl' ) ? 'right' : 'left';
					if ( 'flex-end' === this.values.info_align ) {
						direction = jQuery( 'body' ).hasClass( 'rtl' ) ? 'left' : 'right';
					} else if ( 'center' === this.values.info_align ) {
						direction = 'center';
					}
					this.addCssProperty( description, 'text-align', direction );
				}

				if ( !this.isDefault( 'description_color' ) ) {
				  this.addCssProperty( description, 'color',  this.values.description_color );
				}

				if ( !this.isDefault( 'description_font_size' ) ) {
				  this.addCssProperty( description, 'font-size',  _.fusionGetValueWithUnit( this.values.description_font_size ) );
				}

				if ( !this.isDefault( 'description_line_height' ) ) {
					this.addCssProperty( description, 'line-height', this.values.description_line_height );
				}

				if ( !this.isDefault( 'description_text_transform' ) ) {
					this.addCssProperty( description, 'text-transform', this.values.description_text_transform );
				}

				if ( !this.isDefault( 'description_letter_spacing' ) ) {
					this.addCssProperty( description, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.description_letter_spacing ) );
				}

				headingStyles = _.fusionGetFontStyle( 'description_typography', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( description, rule, value );
				} );

				if ( 'after' ===  this.values.description_order ) {
				  this.addCssProperty( description, 'order', '2' );
				}

				// Hide old sale price.
				if ( 'no' === this.values.show_sale ) {
					this.addCssProperty( info + ' .price del', 'display', 'none' );
				}

				if ( 'before' ===  this.values.sale_order ) {
				   this.addCssProperty( info + ' .price del', 'margin-' +  ( ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'left' : 'right' ), '0.5em' );
				} else {
					this.addCssProperty( info + ' .price', 'flex-direction', 'row-reverse' );
				  this.addCssProperty( info + ' .price del', 'margin-' +  ( ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'right' : 'left' ), '0.5em' );
				}

				// Price font size.
				prices = [ info + ' .price', info + ' .price > .amount', info + ' .price ins > .amount'  ];
				if ( !this.isDefault( 'price_font_size' ) ) {
				  this.addCssProperty( prices, 'font-size',  this.values.price_font_size );
				}

				if ( !this.isDefault( 'price_line_height' ) ) {
					this.addCssProperty( prices, 'line-height', this.values.price_line_height );
				}

				if ( !this.isDefault( 'price_text_transform' ) ) {
					this.addCssProperty( prices, 'text-transform', this.values.price_text_transform );
				}

				if ( !this.isDefault( 'price_letter_spacing' ) ) {
					this.addCssProperty( prices, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.price_letter_spacing ) );
				}

				if ( !this.isDefault( 'price_color' ) ) {
				  this.addCssProperty( prices, 'color',  this.values.price_color );
				}

				headingStyles = _.fusionGetFontStyle( 'price_typography', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( prices, rule, value );
				} );

				sales = [ info + ' .price del .amount', info + ' .price del' ];
				if ( !this.isDefault( 'sale_font_size' ) ) {
				  this.addCssProperty( sales, 'font-size',  this.values.sale_font_size );
				}

				if ( !this.isDefault( 'sale_line_height' ) ) {
					this.addCssProperty( sales, 'line-height', this.values.sale_line_height );
				}

				if ( !this.isDefault( 'sale_text_transform' ) ) {
					this.addCssProperty( sales, 'text-transform', this.values.sale_text_transform );
				}

				if ( !this.isDefault( 'sale_letter_spacing' ) ) {
					this.addCssProperty( sales, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.sale_letter_spacing ) );
				}

				if ( !this.isDefault( 'sale_color' ) ) {
				  this.addCssProperty( sales, 'color',  this.values.sale_color );
				}

				headingStyles = _.fusionGetFontStyle( 'sale_typography', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( sales, rule, value );
				} );

				stock = [ this.baseSelector + ' .stock', info + ' .woocommerce-variation-availability' ];
				if ( !this.isDefault( 'stock_font_size' ) ) {
				  this.addCssProperty( stock, 'font-size',  this.values.stock_font_size );
				}

				if ( !this.isDefault( 'stock_line_height' ) ) {
					this.addCssProperty( stock, 'line-height', this.values.stock_line_height );
				}

				if ( !this.isDefault( 'stock_text_transform' ) ) {
					this.addCssProperty( stock, 'text-transform', this.values.stock_text_transform );
				}

				if ( !this.isDefault( 'stock_letter_spacing' ) ) {
					this.addCssProperty( stock, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.stock_letter_spacing ) );
				}

				if ( !this.isDefault( 'stock_color' ) ) {
				  this.addCssProperty( stock, 'color',  this.values.stock_color );
				}

				headingStyles = _.fusionGetFontStyle( 'stock_typography', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( stock, rule, value );
				} );

				variation_clear = this.baseSelector + ' .reset_variations';
				if ( 'hide' !== this.values.variation_clear ) {
				  if ( 'absolute' !== this.values.variation_clear ) {
				    this.addCssProperty( variation_clear, 'position', 'static' );
				    this.addCssProperty( variation_clear, 'display', 'inline-block' );
				    this.addCssProperty( variation_clear, 'right', 'initial' );
				    this.addCssProperty( variation_clear, 'top', 'initial' );

					if ( 'floated' === this.values.variation_layout ) {
						topMargin    = '' === this.values.clear_margin_top ? '0px' : _.fusionGetValueWithUnit( this.values.clear_margin_top );
						bottomMargin = '' === this.values.clear_margin_bottom ? '0px' : _.fusionGetValueWithUnit( this.values.clear_margin_bottom );
						this.addCssProperty( this.baseSelector + ' .variations tr:last-of-type .label', 'padding-bottom', fusionSanitize.add_css_values( [ this.extras.body_font_size, topMargin, bottomMargin ] ) );
					}
				  }

				  if ( ! this.isDefault( 'clear_margin_top' ) ) {
				    this.addCssProperty( variation_clear, 'margin-top',  _.fusionGetValueWithUnit( this.values.clear_margin_top ) );
				  }

				  if ( ! this.isDefault( 'clear_margin_right' ) ) {
				    this.addCssProperty( variation_clear, 'margin-right',  _.fusionGetValueWithUnit( this.values.clear_margin_right ) );
				  }

				  if ( ! this.isDefault( 'clear_margin_bottom' ) ) {
				    this.addCssProperty( variation_clear, 'margin-bottom',  _.fusionGetValueWithUnit( this.values.clear_margin_bottom ) );
				  }

				  if ( ! this.isDefault( 'clear_margin_left' ) ) {
				    this.addCssProperty( variation_clear, 'margin-left',  _.fusionGetValueWithUnit( this.values.clear_margin_left ) );
				  }

				  if ( ! this.isDefault( 'clear_color' ) ) {
				    this.addCssProperty( variation_clear, 'color',  this.values.clear_color );
				  }

				  if ( ! this.isDefault( 'clear_color_hover' ) ) {
				    this.addCssProperty( variation_clear + ':hover', 'color',  this.values.clear_color_hover );
				  }

				} else {
				  this.addCssProperty( variation_clear, 'display', 'none', true );
				}

				// Button area alignment and spacing.
				button_wrapper = this.baseSelector + ' .fusion-button-wrapper';

				// Button alignment.
				if ( 'stacked' ===  this.values.button_layout ) {
				  this.addCssProperty( button_wrapper, 'display', 'flex' );
				  this.addCssProperty( button_wrapper, 'flex-direction', 'column' );
				  this.addCssProperty( button_wrapper, 'align-items',  this.values.button_align );

				 button_wrapper_quantity = button_wrapper + ' .quantity';
				  this.addCssProperty( button_wrapper_quantity, 'margin-bottom', '1.2em' );
				  this.addCssProperty( button_wrapper_quantity, 'margin-right', '0' );
				} else if ( !this.isDefault( 'button_justify' ) ) {
					this.addCssProperty( button_wrapper, 'display', 'flex' );
				  this.addCssProperty( button_wrapper, 'justify-content',  this.values.button_justify );
				  direction = ( jQuery( 'body' ).hasClass( 'rtl' ) ) ? 'left' : 'right';
				  this.addCssProperty( button_wrapper + ' .quantity', 'margin-' + direction, '1.2em' );
				}

				if ( !this.isDefault( 'button_margin_top' ) ) {
				  this.addCssProperty( button_wrapper, 'margin-top',  _.fusionGetValueWithUnit( this.values.button_margin_top ) );
				}

				if ( !this.isDefault( 'button_margin_right' ) ) {
				  this.addCssProperty( button_wrapper, 'margin-right',  _.fusionGetValueWithUnit( this.values.button_margin_right ) );
				}

				if ( !this.isDefault( 'button_margin_bottom' ) ) {
				  this.addCssProperty( button_wrapper, 'margin-bottom',  _.fusionGetValueWithUnit( this.values.button_margin_bottom ) );
				}

				if ( !this.isDefault( 'button_margin_left' ) ) {
				  this.addCssProperty( button_wrapper, 'margin-left',  _.fusionGetValueWithUnit( this.values.button_margin_left ) );
				}

				if ( !this.isDefault( 'quantity_style' ) ) {
				  quantity_input = '.fusion-body #main ' +  this.baseSelector + ' .quantity input[type="number"].qty';
				  quantity_buttons = '.fusion-body #main ' +  this.baseSelector + ' .quantity input[type="button"]';
				  quantity_both = [ quantity_input, quantity_buttons ];

				  // Quantity height.
				  height = '36px';
				  if (  !  this.isDefault( 'quantity_height' ) ) {
				    height =  _.fusionGetValueWithUnit( this.values.quantity_height );
				    this.addCssProperty( quantity_both, 'height', height );
				    this.addCssProperty( quantity_buttons, 'width', height );
				  }

				  // Quantity width.
				  width = '36px';
				  if (  !  this.isDefault( 'quantity_width' ) ) {
					width =  _.fusionGetValueWithUnit( this.values.quantity_width );

					if ( -1 !== width.indexOf( '%' ) ) {
						this.addCssProperty( quantity_input, 'width', 'calc( 100% - ' + height + ' - ' + height + ' )' );
					} else {
						this.addCssProperty( quantity_input, 'width', width );
					}
				  }

				  if (  !  this.isDefault( 'quantity_width' ) ||   !  this.isDefault( 'quantity_height' ) ) {
				    this.addCssProperty( this.baseSelector + ' .quantity', 'width', 'calc( ' + width + ' + ' + height + ' + ' + height + ' )' );
				  }

				  if (  !  this.isDefault( 'quantity_radius_top_left' ) ) {
				    this.addCssProperty( this.baseSelector + ' .quantity .minus', 'border-top-left-radius',  _.fusionGetValueWithUnit( this.values.quantity_radius_top_left ) );
				  }

				  if (  !  this.isDefault( 'quantity_radius_bottom_left' ) ) {
				    this.addCssProperty( this.baseSelector + ' .quantity .minus', 'border-bottom-left-radius',  _.fusionGetValueWithUnit( this.values.quantity_radius_bottom_left ) );
				  }

				  if (  !  this.isDefault( 'quantity_radius_top_right' ) ) {
				    this.addCssProperty( this.baseSelector + ' .quantity .plus', 'border-top-right-radius',  _.fusionGetValueWithUnit( this.values.quantity_radius_top_right ) );
				  }

				  if (  !  this.isDefault( 'quantity_radius_bottom_left' ) ) {
				    this.addCssProperty( this.baseSelector + ' .quantity .plus', 'border-bottom-right-radius',  _.fusionGetValueWithUnit( this.values.quantity_radius_bottom_right ) );
				  }

				  if (  !  this.isDefault( 'quantity_font_size' ) ) {
				    quantity_font = [ quantity_input, quantity_buttons, this.baseSelector + ' .quantity' ];
				    this.addCssProperty( quantity_font, 'font-size',  _.fusionGetValueWithUnit( this.values.quantity_font_size ) );
				  }

				  if (  !  this.isDefault( 'quantity_color' ) ) {
				    this.addCssProperty( quantity_input, 'color',  this.values.quantity_color );
				  }

				  if (  !  this.isDefault( 'quantity_background' ) ) {
				    this.addCssProperty( quantity_input, 'background-color',  this.values.quantity_background );
				  }

				  if (  !  this.isDefault( 'quantity_border_sizes_top' ) ) {
				    this.addCssProperty( quantity_input, 'border-top-width',  _.fusionGetValueWithUnit( this.values.quantity_border_sizes_top ) );
				  }

				  if (  !  this.isDefault( 'quantity_border_sizes_right' ) ) {
				    this.addCssProperty( quantity_input, 'border-right-width',  _.fusionGetValueWithUnit( this.values.quantity_border_sizes_right ) );
				  }

				  if (  !  this.isDefault( 'quantity_border_sizes_bottom' ) ) {
				    this.addCssProperty( quantity_input, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.quantity_border_sizes_bottom ) );
				  }

				  if (  !  this.isDefault( 'quantity_border_sizes_left' ) ) {
				    this.addCssProperty( quantity_input, 'border-left-width',  _.fusionGetValueWithUnit( this.values.quantity_border_sizes_left ) );
				  }

				  if (  !  this.isDefault( 'quantity_border_color' ) ) {
				    this.addCssProperty( quantity_input, 'border-color',  this.values.quantity_border_color );
				  }

				  if (  !  this.isDefault( 'qbutton_border_sizes_top' ) ) {
				    this.addCssProperty( quantity_buttons, 'border-top-width',  _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_top ) );
				  }

				  if (  !  this.isDefault( 'qbutton_border_sizes_right' ) ) {
				    this.addCssProperty( quantity_buttons, 'border-right-width',  _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_right ) );
				  }

				  if (  !  this.isDefault( 'qbutton_border_sizes_bottom' ) ) {
				    this.addCssProperty( quantity_buttons, 'border-bottom-width',  _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_bottom ) );
				  }

				  if (  !  this.isDefault( 'qbutton_border_sizes_left' ) ) {
				    this.addCssProperty( quantity_buttons, 'border-left-width',  _.fusionGetValueWithUnit( this.values.qbutton_border_sizes_left ) );
				  }

				  if (  !  this.isDefault( 'qbutton_color' ) ) {
				    this.addCssProperty( quantity_buttons, 'color',  this.values.qbutton_color );
				  }

				  if (  !  this.isDefault( 'qbutton_background' ) ) {
				    this.addCssProperty( quantity_buttons, 'background-color',  this.values.qbutton_background );
				  }

				  if (  !  this.isDefault( 'qbutton_border_color' ) ) {
				    this.addCssProperty( quantity_buttons, 'border-color',  this.values.qbutton_border_color );
				  }

				  hover_buttons = quantity_buttons + ':hover';
				  // Quantity button hover text color.
				  if (  !  this.isDefault( 'qbutton_color_hover' ) ) {
				    this.addCssProperty( hover_buttons, 'color',  this.values.qbutton_color_hover );
				  }

				  if (  !  this.isDefault( 'qbutton_background_hover' ) ) {
				    this.addCssProperty( hover_buttons, 'background-color',  this.values.qbutton_background_hover );
				  }

				  if (  !  this.isDefault( 'qbutton_border_color_hover' ) ) {
				    this.addCssProperty( hover_buttons, 'border-color',  this.values.qbutton_border_color_hover );
				  }

				}

				if ( !this.isDefault( 'button_style' ) ) {
				  button = '.fusion-body ' +  this.baseSelector + ' .fusion-button-wrapper .button';
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
