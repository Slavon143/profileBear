var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Woo Cart Table View.
		FusionPageBuilder.fusion_woo_cart_table = FusionPageBuilder.ElementView.extend( {

			afterPatch: function() {
				var $quantityBoxes = this.$el.find( 'div.quantity:not(.buttons_added), td.quantity:not(.buttons_added)' ).find( '.qty' );

				if ( $quantityBoxes.length && 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaAddQuantityBoxes ) {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaAddQuantityBoxes( '.qty', $quantityBoxes );
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
				attributes.cid    = this.model.get( 'cid' );
				attributes.attr   = this.buildAttr( atts.values );

				attributes.wooCartTable = this.buildAttr( atts.values, attributes.cid );
				attributes.cart_table = '';
				if ( 'undefined' !== typeof atts.query_data  ) {
					attributes.cart_table = atts.query_data;
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

				// WooCartTable attributes.
				var wooCartTable = {
					class: 'shop_table shop_table_responsive cart woocommerce-cart-form__contents fusion-woo-cart_table fusion-woo-cart_table-' + cid
				};

				wooCartTable = _.fusionVisibilityAtts( values.hide_on_mobile, wooCartTable );

				if ( '' !== values[ 'class' ] ) {
					wooCartTable[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					wooCartTable.id = values.id;
				}

				wooCartTable = _.fusionAnimations( values, wooCartTable );

				return wooCartTable;
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
				var selector, css;
				this.values = values;
				this.baseSelector = '.fusion-woo-cart_table-' +  this.model.get( 'cid' );
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

				selector =  this.baseSelector + ' tbody tr td, ' +  this.baseSelector + ' thead tr th';
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

				if ( !this.isDefault( 'cell_padding_top' ) || !this.isDefault( 'cell_padding_bottom' ) ) {
					this.addCssProperty( this.baseSelector + '.shop_table tbody tr', 'height', 'auto' );
				}

				selector =  this.baseSelector + ' thead tr th';
				if ( !this.isDefault( 'heading_cell_backgroundcolor' ) ) {
				this.addCssProperty( selector, 'background-color',  this.values.heading_cell_backgroundcolor );
				}

				if ( !this.isDefault( 'heading_color' ) ) {
				this.addCssProperty( selector, 'color',  this.values.heading_color );
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

				if ( !this.isDefault( 'text_color' ) ) {
				this.addCssProperty( [ selector, selector + ' a', selector + ' .amount' ], 'color',  this.values.text_color, true );
				//this.addCssProperty( selector + ' a', 'color',  this.values.text_color );
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

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';

			}

		} );
	} );
}( jQuery ) );
