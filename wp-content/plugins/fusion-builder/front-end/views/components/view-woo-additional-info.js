var FusionPageBuilder = FusionPageBuilder || {};

( function() {


	jQuery( document ).ready( function() {

		// Woo Rating Component View.
		FusionPageBuilder.fusion_tb_woo_additional_info = FusionPageBuilder.ElementView.extend( {

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
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-woo-additional-info-tb fusion-woo-additional-info-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

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
			 * @since  3.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-woo-additional-info-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.woo_additional_info ) {
					output = atts.query_data.woo_additional_info;
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
				var self = this,
					css = '',
					cellSelectors,
					headingStyles = {},
					textStyles = {};

				this.baseSelector = '.fusion-woo-additional-info-tb.fusion-woo-additional-info-tb-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				// Heading styles.
				if ( ! this.isDefault( 'heading_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr th', 'color', values.heading_color );
				}

				if ( ! this.isDefault( 'heading_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr th', 'font-size',  _.fusionGetValueWithUnit( values.heading_font_size ) );
				}

				if ( !this.isDefault( 'heading_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr th', 'line-height', this.values.heading_line_height );
				}

				if ( !this.isDefault( 'heading_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr th', 'text-transform', this.values.heading_text_transform );
				}

				if ( !this.isDefault( 'heading_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr th', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.heading_letter_spacing ) );
				}

				// Heading typography styles.
				headingStyles = _.fusionGetFontStyle( 'heading_font', values, 'object' );
				jQuery.each( headingStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' .shop_attributes tr th', rule, value );
				} );

				// Text styles.
				if ( ! this.isDefault( 'text_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr td', 'color', values.text_color );
				}

				if ( ! this.isDefault( 'text_font_size' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr td', 'font-size',  _.fusionGetValueWithUnit( values.text_font_size ) );
				}

				if ( !this.isDefault( 'text_line_height' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr td', 'line-height', this.values.text_line_height );
				}

				if ( !this.isDefault( 'text_text_transform' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr td', 'text-transform', this.values.text_text_transform );
				}

				if ( !this.isDefault( 'text_letter_spacing' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr td', 'letter-spacing',  _.fusionGetValueWithUnit( this.values.text_letter_spacing ) );
				}

				// Text typography styles.
				textStyles = _.fusionGetFontStyle( 'text_font', values, 'object' );
				jQuery.each( textStyles, function( rule, value ) {
					self.addCssProperty( self.baseSelector + ' .shop_attributes tr td', rule, value );
				} );

				// Table Border styles.
				if ( ! this.isDefault( 'border_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes tr', 'border-color',  values.border_color );
				}

				// Cell background.
				if ( ! this.isDefault( 'table_cell_backgroundcolor' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes td', 'background-color',  values.table_cell_backgroundcolor );
				}

				// Heading background.
				if ( ! this.isDefault( 'heading_cell_backgroundcolor' ) ) {
					this.addCssProperty( this.baseSelector + ' .shop_attributes th', 'background-color',  values.heading_cell_backgroundcolor );
				}

				// Table cell selectors.
				cellSelectors = [
					this.baseSelector + ' .shop_attributes tr th',
					this.baseSelector + ' .shop_attributes tr td'
				];

				// Get padding.
				jQuery.each( [ 'top', 'right', 'bottom', 'left' ], function( index, side ) {
					var cellPaddingName = 'cell_padding_' + side,
						marginName      = 'margin_' + side;


					// Add content padding to style.
					if ( '' !==  values[ cellPaddingName ] ) {
						self.addCssProperty( cellSelectors, 'padding-' + side,  _.fusionGetValueWithUnit( values[ cellPaddingName ] ) );
					}

					// Element margin.
					if ( '' !==  values[ marginName ] ) {
						self.addCssProperty( self.baseSelector, 'margin-' + side,  _.fusionGetValueWithUnit( values[ marginName ] ) );
					}
				} );

				css = this.parseCSS();

				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
