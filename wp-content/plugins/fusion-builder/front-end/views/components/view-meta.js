/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Meta Component View.
		FusionPageBuilder.fusion_tb_meta = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.4
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.values = atts.values;
				this.extras = atts.extras;

				// Any extras that need passed on.
				attributes.cid         = this.model.get( 'cid' );
				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock();
				attributes.output      = this.buildOutput( atts );

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.border_size = _.fusionValidateAttrValue( values.border_size, 'px' );
				values.height      = _.fusionValidateAttrValue( values.height, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since  2.4
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-meta-tb fusion-meta-tb-' + this.model.get( 'cid' ),
						style: ''
					} );

				if ( '' !== values.padding_top ) {
					attr.style += 'padding-top:' + values.padding_top + ';';
				}

				if ( '' !== values.padding_right ) {
					attr.style += 'padding-right:' + values.padding_right + ';';
				}

				if ( '' !== values.padding_bottom ) {
					attr.style += 'padding-bottom:' + values.padding_bottom + ';';
				}

				if ( '' !== values.padding_left ) {
					attr.style += 'padding-left:' + values.padding_left + ';';
				}

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

				if ( '' !== values.stacked_vertical_align && 'floated' !== values.layout ) {
					attr.style += 'justify-content:' + values.stacked_vertical_align + ';';
				}

				if ( '' !== values.stacked_horizontal_align && 'floated' !== values.layout ) {
					attr.style += 'align-items:' + values.stacked_horizontal_align + ';';
				}

				if ( '' !== values.height ) {
					attr.style += 'min-height:' + values.height + ';';
				}

				if ( '' !== values.font_size ) {
					attr.style += 'font-size:' + values.font_size + ';';
				}

				if ( '' !== values.background_color ) {
					attr.style += 'background-color:' + values.background_color + ';';
				}

				if ( '' !== values.layout ) {
					attr[ 'class' ] += ' ' + values.layout;
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
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-meta-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.meta ) {
					output = atts.query_data.meta;
				}

				return output;
			},

			/**
			 * Builds styles.
			 *
			 * @since  2.4
			 * @return {String}
			 */
			buildStyleBlock: function() {
				var selectors, css, media;
				this.baseSelector = '.fusion-meta-tb.fusion-meta-tb-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				selectors = [ this.baseSelector, this.baseSelector + ' a' ];
				if ( !this.isDefault( 'text_color' ) ) {
				  this.addCssProperty( selectors, 'color',  this.values.text_color );
				}

				if ( !this.isDefault( 'link_color' ) ) {
				  this.addCssProperty( [ this.baseSelector + ' span a' ], 'color',  this.values.link_color );
				}

				// Alignment.
				if ( '' !== this.values.alignment && 'stacked' !== this.values.layout ) {
					this.addCssProperty( [ this.baseSelector ], 'justify-content', this.values.alignment );
				}

				selectors = [ this.baseSelector + ' a:hover', this.baseSelector + ' span a:hover' ];

				if ( !this.isDefault( 'text_hover_color' ) ) {
				  this.addCssProperty( selectors, 'color',  this.values.text_hover_color );
				}

				if ( !this.isDefault( 'border_color' ) ) {
				  this.addCssProperty( [ this.baseSelector ], 'border-color',  this.values.border_color );
				}

				if ( !this.isDefault( 'border_bottom' ) ) {
				  this.addCssProperty( [ this.baseSelector ], 'border-bottom-width',  this.values.border_bottom );
				}

				if ( !this.isDefault( 'border_top' ) ) {
				  this.addCssProperty( [ this.baseSelector ], 'border-top-width',  this.values.border_top );
				}

				if ( !this.isDefault( 'border_left' ) ) {
				  this.addCssProperty( [ this.baseSelector ], 'border-left-width',  this.values.border_left );
				}

				if ( !this.isDefault( 'border_right' ) ) {
				  this.addCssProperty( [ this.baseSelector ], 'border-right-width',  this.values.border_right );
				}

				selectors = [ this.baseSelector + '  > span:not(.fusion-meta-tb-sep)' ];
				if ( !this.isDefault( 'item_border_color' ) ) {
				  this.addCssProperty( selectors, 'border-color',  this.values.item_border_color );
				}

				if ( !this.isDefault( 'item_border_bottom' ) ) {
				  this.addCssProperty( selectors, 'border-bottom-width',  this.values.item_border_bottom );
				}

				if ( !this.isDefault( 'item_border_top' ) ) {
				  this.addCssProperty( selectors, 'border-top-width',  this.values.item_border_top );
				}

				if ( !this.isDefault( 'item_border_left' ) ) {
				  this.addCssProperty( selectors, 'border-left-width',  this.values.item_border_left );
				}

				if ( !this.isDefault( 'item_border_right' ) ) {
				  this.addCssProperty( selectors, 'border-right-width',  this.values.item_border_right );
				}

				if ( !this.isDefault( 'item_background_color' ) ) {
				  this.addCssProperty( selectors, 'background-color',  this.values.item_background_color );
				}

				if ( !this.isDefault( 'item_padding_top' ) ) {
				  this.addCssProperty( selectors, 'padding-top',  this.values.item_padding_top );
				}

				if ( !this.isDefault( 'item_padding_bottom' ) ) {
				  this.addCssProperty( selectors, 'padding-bottom',  this.values.item_padding_bottom );
				}

				if ( !this.isDefault( 'item_padding_left' ) ) {
				  this.addCssProperty( selectors, 'padding-left',  this.values.item_padding_left );
				}

				if ( !this.isDefault( 'item_padding_right' ) ) {
				  this.addCssProperty( selectors, 'padding-right',  this.values.item_padding_right );
				}

				if ( !this.isDefault( 'item_margin_top' ) ) {
				  this.addCssProperty( selectors, 'margin-top',  this.values.item_margin_top );
				}

				if ( !this.isDefault( 'item_margin_bottom' ) ) {
				  this.addCssProperty( selectors, 'margin-bottom',  this.values.item_margin_bottom );
				}

				if ( !this.isDefault( 'item_margin_left' ) ) {
				  this.addCssProperty( selectors, 'margin-left',  this.values.item_margin_left );
				}

				if ( !this.isDefault( 'item_margin_right' ) ) {
				  this.addCssProperty( selectors, 'margin-right',  this.values.item_margin_right );
				}

				css = this.parseCSS();

				// Responsive Alignment.
				if ( 'stacked' !== this.values.layout ) {
					_.each( [ 'medium', 'small' ], function( size ) {
						var key = 'alignment_' + size;
						media = '@media only screen and (max-width:' + this.extras[ 'visibility_' + size ] + 'px)';

						if ( '' === this.values[ key ] ) {
							return;
						}

						this.dynamic_css = {};
						this.addCssProperty( [ this.baseSelector ], 'justify-content', this.values[ key ] );
						css += media + '{' + this.parseCSS() + '}';
					}, this );
				}
				return ( css ) ? '<style type="text/css">' + css + '</style>' : '';
			}
		} );
	} );
}( jQuery ) );
