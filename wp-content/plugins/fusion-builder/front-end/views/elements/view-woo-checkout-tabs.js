/* global FusionPageBuilderApp */
/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Checkout Billing Component View.
		FusionPageBuilder.fusion_tb_woo_checkout_tabs = FusionPageBuilder.ElementView.extend( {

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

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.output      = this.buildOutput( atts );
				attributes.styles      = this.buildStyleBlock();

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
						class: 'fusion-woo-checkout-tabs-tb fusion-woo-checkout-tabs-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'horizontal' === values.layout ) {
					attr[ 'class' ] += ' woo-tabs-horizontal';
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-checkout-tabs-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_checkout_tabs ) {
					output = atts.query_data.woo_checkout_tabs;
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
				var self = this,
					sides, margin_name, content_padding_name, nav_padding_name, text_styles, rule, value, title_selectors,
					inputs, placeholderColor, placeHolderInputs, hoverColor, hoverInputs, focusInputs, selector, css;

				this.baseSelector = '.fusion-woo-checkout-tabs-tb.fusion-woo-checkout-tabs-tb-' + this.model.get( 'cid' );
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

				// Paddings.
				jQuery.each( sides, function( index, side ) {
					content_padding_name = 'content_padding_' + side;
					nav_padding_name = 'nav_padding_' + side;
					// Add content padding to style.
					if ( '' !==  self.values[ content_padding_name ] ) {
						self.addCssProperty( self.baseSelector + ' .avada-checkout', 'padding-' + side,  _.fusionGetValueWithUnit( self.values[ content_padding_name ] ) );
					}

					if ( '' !==  self.values[ nav_padding_name ] ) {
						self.addCssProperty( self.baseSelector + ' .woocommerce-checkout-nav > li > a', 'padding-' + side,  _.fusionGetValueWithUnit( self.values[ nav_padding_name ] ) );
					}
				} );

				if ( 'vertical' === this.values.layout && ! this.isDefault( 'nav_content_space' ) ) {
					this.addCssProperty( this.baseSelector + ' .avada-checkout', 'margin-left', 'calc(220px + ' + _.fusionGetValueWithUnit( this.values.nav_content_space ) + ')' );
				}

				if ( !this.isDefault( 'backgroundcolor' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li.is-active > a', 'background-color',  this.values.backgroundcolor );
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li > a:hover', 'background-color',  this.values.backgroundcolor );
					this.addCssProperty( this.baseSelector + ' .avada-checkout', 'background-color',  this.values.backgroundcolor );
				}

				if ( !this.isDefault( 'inactivebackgroundcolor' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li > a', 'background-color',  this.values.inactivebackgroundcolor );
				}

				if ( !this.isDefault( 'active_nav_text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li.is-active > a', 'color',  this.values.active_nav_text_color );
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li.is-active > a:after', 'color',  this.values.active_nav_text_color );
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li > a:hover', 'color',  this.values.active_nav_text_color );
				}

				if ( !this.isDefault( 'inactive_nav_text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav > li > a', 'color',  this.values.inactive_nav_text_color );
				}

				if ( !this.isDefault( 'bordercolor' ) ) {
					if ( 'horizontal' ===  this.values.layout ) {
						this.addCssProperty( this.baseSelector + '.woo-tabs-horizontal > .woocommerce-checkout-nav .is-active', 'border-color',  this.values.bordercolor );
					} else {
						this.addCssProperty( this.baseSelector + ' .woocommerce-checkout-nav li a', 'border-color',  this.values.bordercolor );
					}

					this.addCssProperty( this.baseSelector + ' .avada-checkout', 'border-color',  this.values.bordercolor );
					this.addCssProperty( this.baseSelector + ' .avada-checkout .shop_table tr', 'border-color',  this.values.bordercolor );
					this.addCssProperty( this.baseSelector + ' .avada-checkout .shop_table tfoot', 'border-color',  this.values.bordercolor );
				}

				if ( !this.isDefault( 'text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .avada-checkout', 'color',  this.values.text_color );
					this.addCssProperty( this.baseSelector + ' .avada-checkout .shop_table tfoot .order-total .amount', 'color',  this.values.text_color );
					this.addCssProperty( this.baseSelector + ' .avada-checkout .shop_table tfoot .order-total .amount', 'font-weight',  '700' );
				}

				if ( !this.isDefault( 'text_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .avada-checkout', 'font-size',  _.fusionGetValueWithUnit( this.values.text_font_size ) );
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

				text_styles = _.fusionGetFontStyle( 'text_font', this.values, 'object' );
				for ( rule in text_styles ) { // eslint-disable-line guard-for-in
					var value = text_styles[ rule ]; // eslint-disable-line

					this.addCssProperty( this.baseSelector + ' .avada-checkout', rule, value ); // eslint-disable-line block-scoped-var
				}

				// Link color.
				if ( !this.isDefault( 'link_color' ) ) {
					self.addCssProperty( this.baseSelector + ' a:not(.fusion-button)', 'color', this.values.link_color );
				}

				// Link hover color.
				if ( !this.isDefault( 'link_hover_color' ) ) {
					self.addCssProperty( this.baseSelector + ' a:not(.fusion-button):hover', 'color', this.values.link_hover_color );
				}

				// Title styles.
				title_selectors = [ this.baseSelector + ' .avada-checkout h3' ];
				if ( !this.isDefault( 'title_color' ) ) {
					this.addCssProperty( title_selectors, 'color',  this.values.title_color );
				}

				if ( !this.isDefault( 'title_font_size' ) ) {
					this.addCssProperty( title_selectors, 'font-size',  _.fusionGetValueWithUnit( this.values.title_font_size ) );
				}

				if ( !this.isDefault( 'title_line_height' ) ) {
					this.addCssProperty( title_selectors, 'line-height', this.values.title_line_height );
				}

				if ( !this.isDefault( 'title_text_transform' ) ) {
					this.addCssProperty( title_selectors, 'text-transform', this.values.title_text_transform );
				}

				if ( !this.isDefault( 'title_letter_spacing' ) ) {
					this.addCssProperty( title_selectors, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.title_letter_spacing ) );
				}

				text_styles = _.fusionGetFontStyle( 'title_font', this.values, 'object' );
				for ( rule in text_styles ) { // eslint-disable-line guard-for-in
					var value = text_styles[ rule ]; // eslint-disable-line

					this.addCssProperty( title_selectors, rule, value ); // eslint-disable-line block-scoped-var
				}

				inputs = [ this.baseSelector + ' input', this.baseSelector + ' select', this.baseSelector + ' textarea' ];

				if ( ! this.isDefault( 'field_bg_color' ) ) {
					this.addCssProperty( inputs, 'background-color',  this.values.field_bg_color );

					// Select 2.
					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'background-color', this.values.field_bg_color, true );
				}

				if ( ! this.isDefault( 'field_text_color' ) ) {
					placeholderColor = jQuery.AWB_Color( this.values.field_text_color ).alpha( 0.5 ).toVarOrRgbaString();
					this.addCssProperty( inputs, 'color',  this.values.field_text_color );

					// Select 2.
					this.addCssProperty( this.baseSelector + ' .select2-container--default .select2-selection--single .select2-selection__rendered', 'color', this.values.field_text_color );
					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'color', this.values.field_text_color, true );

					placeHolderInputs = [ this.baseSelector + ' input::placeholder', this.baseSelector + ' textarea::placeholder' ];
					this.addCssProperty( placeHolderInputs, 'color',  placeholderColor );
				}

				if ( ! this.isDefault( 'field_border_color' ) ) {
					this.addCssProperty( inputs, 'border-color',  this.values.field_border_color );

					// Select 2.
					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'border-color', this.values.field_border_color, true );
					this.addCssProperty( this.baseSelector + ' .avada-select-parent .select-arrow', 'color', this.values.field_border_color, true );
				}

				if ( ! this.isDefault( 'field_border_focus_color' ) ) {
					hoverColor = jQuery.AWB_Color( this.values.field_border_focus_color ).alpha( 0.5 ).toVarOrRgbaString();
					hoverInputs = [ this.baseSelector + ' input:hover', this.baseSelector + ' select:hover', this.baseSelector + ' textarea:hover' ];
					this.addCssProperty( hoverInputs, 'border-color', hoverColor );
					focusInputs = [ this.baseSelector + ' input:focus', this.baseSelector + ' select:focus', this.baseSelector + ' textarea:focus' ];
					this.addCssProperty( focusInputs, 'border-color',  this.values.field_border_focus_color );

					// Select 2.
					this.addCssProperty( this.baseSelector + ' .avada-select-parent:hover .select-arrow', 'border-color', hoverColor, true );
					this.addCssProperty( this.baseSelector + ' .avada-select-parent:hover .select-arrow', 'color', hoverColor, true );
				}

				selector =  this.baseSelector + ' .woocommerce-checkout-payment ul.wc_payment_methods li label';
				if ( !this.isDefault( 'payment_label_padding_top' ) ) {
				  this.addCssProperty( selector, 'padding-top',  this.values.payment_label_padding_top );
				}

				if ( !this.isDefault( 'payment_label_padding_bottom' ) ) {
				  this.addCssProperty( selector, 'padding-bottom',  this.values.payment_label_padding_bottom );
				}

				if ( !this.isDefault( 'payment_label_padding_left' ) ) {
				  this.addCssProperty( selector, 'padding-left', 'max(2.5em,' +  this.values.payment_label_padding_left + ')' );
				}

				if ( !this.isDefault( 'payment_label_padding_right' ) ) {
				  this.addCssProperty( selector, 'padding-right',  this.values.payment_label_padding_right );
				}

				if ( !this.isDefault( 'payment_label_bg_color' ) ) {
				  this.addCssProperty( selector, 'background',  this.values.payment_label_bg_color );
				}

				if ( !this.isDefault( 'payment_label_color' ) ) {
				  this.addCssProperty( selector, 'color',  this.values.payment_label_color );
				}

				if ( !this.isDefault( 'payment_label_hover_color' ) ) {
					this.addCssProperty( selector + ':hover', 'color',  this.values.payment_label_hover_color );
					  this.addCssProperty( this.baseSelector + ' ul li input:checked+label', 'color',  this.values.payment_label_hover_color );
				  }

				selector =  this.baseSelector + ' .woocommerce-checkout-payment ul.wc_payment_methods li:hover label';
				if ( !this.isDefault( 'payment_label_bg_hover_color' ) ) {
				  this.addCssProperty( selector, 'background',  this.values.payment_label_bg_hover_color );
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

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
