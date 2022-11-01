var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Featured Product Slider View.
		FusionPageBuilder.fusion_woo_cart_totals = FusionPageBuilder.ElementView.extend( {


			afterPatch: function() {
				this._refreshJs();
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
				attributes.cid    = this.model.get( 'cid' );

				attributes.wooCartTotalsWrapper = this.buildWrapperAttr( atts.values, attributes.cid );

				attributes.cart_totals = '';
				if ( 'undefined' !== typeof atts.query_data  ) {
					attributes.cart_totals = atts.query_data;
				}

				attributes.styles = this.buildStyleBlock( atts.values );
				return attributes;
			},

			/**
			 * Builds wrapper attributes.
			 *
			 * @since 3.3
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values, cid ) {

				var attributes = {
					class: 'fusion-woo-cart-totals-wrapper fusion-woo-cart-totals-wrapper-' + cid
				};

				attributes = _.fusionVisibilityAtts( values.hide_on_mobile, attributes );

				if ( '' !== values[ 'class' ] ) {
					attributes[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'show' === values.buttons_visibility ) {
					attributes[ 'class' ] += ' show-buttons';
				}

				if ( '' !== values.id ) {
					attributes.id = values.id;
				}

				attributes = _.fusionAnimations( values, attributes );

				return attributes;
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
				var selector, text_selector, css;
				this.values = values;
				this.baseSelector = '.fusion-woo-cart-totals-wrapper-' +  this.model.get( 'cid' );
				this.dynamic_css = {};

				if ( !this.isDefault( 'margin_top' ) ) {
				this.addCssProperty( this.baseSelector, 'margin-top',  this.values.margin_top );
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

				selector =  this.baseSelector + ' tbody tr td, ' +  this.baseSelector + ' tbody tr th';
				if ( !this.isDefault( 'cell_padding_top' ) ) {
				this.addCssProperty( selector, 'padding-top',  this.values.cell_padding_top );
				}

				if ( !this.isDefault( 'cell_padding_bottom' ) ) {
				this.addCssProperty( selector, 'padding-bottom',  this.values.cell_padding_bottom );
				}

				if ( !this.isDefault( 'cell_padding_left' ) ) {
				this.addCssProperty( selector, 'padding-left',  this.values.cell_padding_left );
				}

				if ( !this.isDefault( 'cell_padding_right' ) ) {
				this.addCssProperty( selector, 'padding-right',  this.values.cell_padding_right );
				}

				selector =  this.baseSelector + ' tbody tr th';
				if ( !this.isDefault( 'heading_cell_backgroundcolor' ) ) {
				this.addCssProperty( selector, 'background-color',  this.values.heading_cell_backgroundcolor );
				}

				if ( !this.isDefault( 'fusion_font_family_heading_font' ) ) {
				this.addCssProperty( selector, 'font-family',  this.values.fusion_font_family_heading_font );
				}

				if ( !this.isDefault( 'fusion_font_variant_heading_font' ) ) {
				this.addCssProperty( selector, 'font-weight',  this.values.fusion_font_variant_heading_font );
				}

				if ( !this.isDefault( 'heading_font_size' ) ) {
				this.addCssProperty( selector, 'font-size',  this.values.heading_font_size );
				}

				if ( !this.isDefault( 'heading_line_height' ) ) {
				this.addCssProperty( selector, 'line-height', this.values.heading_line_height );
				}

				if ( !this.isDefault( 'heading_text_transform' ) ) {
				this.addCssProperty( selector, 'text-transform', this.values.heading_text_transform );
				}

				if ( !this.isDefault( 'heading_letter_spacing' ) ) {
				this.addCssProperty( selector, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.heading_letter_spacing ) );
				}

				selector =  this.baseSelector + ' tbody tr td';
				if ( !this.isDefault( 'table_cell_backgroundcolor' ) ) {
				this.addCssProperty( selector, 'background-color',  this.values.table_cell_backgroundcolor );
				}

				text_selector = selector + ', ' +  this.baseSelector + ' a, ' +  this.baseSelector + ' .amount';
				if ( !this.isDefault( 'text_color' ) ) {
				this.addCssProperty( text_selector, 'color',  this.values.text_color, true );
				}

				if ( !this.isDefault( 'heading_color' ) ) {
					this.addCssProperty( this.baseSelector + ' tbody tr th', 'color',  this.values.heading_color, true );
				}

				if ( !this.isDefault( 'fusion_font_family_text_font' ) ) {
				this.addCssProperty( selector, 'font-family',  this.values.fusion_font_family_text_font );
				}

				if ( !this.isDefault( 'fusion_font_variant_text_font' ) ) {
				this.addCssProperty( selector, 'font-weight',  this.values.fusion_font_variant_text_font );
				}

				if ( !this.isDefault( 'text_font_size' ) ) {
				this.addCssProperty( selector, 'font-size',  this.values.text_font_size );
				}

				if ( !this.isDefault( 'text_line_height' ) ) {
				this.addCssProperty( selector, 'line-height', this.values.text_line_height );
				}

				if ( !this.isDefault( 'text_text_transform' ) ) {
				this.addCssProperty( selector, 'text-transform', this.values.text_text_transform );
				}

				if ( !this.isDefault( 'text_letter_spacing' ) ) {
				this.addCssProperty( selector, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.text_letter_spacing ) );
				}

				selector =  this.baseSelector + ' tr, ' +  this.baseSelector + ' tr td, ' +  this.baseSelector + ' tr th';
				if ( !this.isDefault( 'border_color' ) ) {
				this.addCssProperty( selector, 'border-color',  this.values.border_color, true );
				}

				selector = '.fusion-woo-cart-totals-wrapper-' +  this.model.get( 'cid' ) + ' div.wc-proceed-to-checkout';
				if ( 'floated' ===  this.values.buttons_layout ) {
					this.addCssProperty( selector, 'flex-direction', 'row' );
					if ( 'yes' ===  this.values.button_span ) {
						this.addCssProperty( selector, 'justify-content', 'stretch', true );
						this.addCssProperty( selector + ' a', 'flex', '1' );
					} else {
						this.addCssProperty( selector, 'justify-content',  this.values.floated_buttons_alignment, true );
					}
				} else {
					this.addCssProperty( selector, 'flex-direction', 'column', true );
					this.addCssProperty( selector, 'align-items',  this.values.stacked_buttons_alignment, true );
					if ( 'yes' ===  this.values.button_span ) {
						this.addCssProperty( selector, 'align-items', 'stretch', true );
					} else {
						this.addCssProperty( selector, 'align-items',  this.values.stacked_buttons_alignment, true );
					}
				}

				if ( !this.isDefault( 'button_margin_top' ) ) {
				this.addCssProperty( selector + ' a', 'margin-top',  this.values.button_margin_top );
				}

				if ( !this.isDefault( 'button_margin_bottom' ) ) {
				this.addCssProperty( selector + ' a', 'margin-bottom',  this.values.button_margin_bottom );
				}

				if ( !this.isDefault( 'button_margin_left' ) ) {
				this.addCssProperty( selector + ' a', 'margin-left',  this.values.button_margin_left );
				}

				if ( !this.isDefault( 'button_margin_right' ) ) {
				this.addCssProperty( selector + ' a', 'margin-right',  this.values.button_margin_right );
				}


				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );
