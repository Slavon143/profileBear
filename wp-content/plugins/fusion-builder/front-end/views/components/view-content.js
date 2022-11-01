/* global FusionApp */
/* eslint no-mixed-spaces-and-tabs: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Content Component View.
		FusionPageBuilder.fusion_tb_content = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.2
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
				attributes.styles      = this.buildStyleBlock( atts.values );

				return attributes;
			},

			/**
			 * Builds output.
			 *
			 * @since  3.3
			 * @param  {Object} atts - The attributes object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output    = '',
					stripHTML = ( 'yes' === atts.values.strip_html );

				if ( 'undefined' !== typeof atts.query_data && 'object' === typeof atts.query_data.content ) {
					if ( atts.query_data.content.has_custom_excerpt ) {
						output = _.fusionGetFixedContent( atts.query_data.content, atts.values.excerpt, Number.MAX_SAFE_INTEGER, stripHTML );
					} else {
						output = _.fusionGetFixedContent( atts.query_data.content, atts.values.excerpt, atts.values.excerpt_length, stripHTML );
					}

				}
				if ( '' === output ) {
					output = 'object' === typeof FusionApp.initialData.examplePostDetails && 'string' === typeof FusionApp.initialData.examplePostDetails.post_content ? FusionApp.initialData.examplePostDetails.post_content : _.autop( 'This is some example content.' );
				}

				return output;
			},

			buildStyleBlock: function( values ) {
				var text_styles, css,
self = this;

				this.baseSelector = '.fusion-content-tb-' + this.model.get( 'cid' );
				this.dynamic_css  = {};

				if ( !this.isDefault( 'content_alignment' ) ) {
				  this.addCssProperty( this.baseSelector, 'text-align',  this.values.content_alignment );
				}

				if ( !this.isDefault( 'font_size' ) ) {
				  this.addCssProperty( this.baseSelector, 'font-size',  _.fusionGetValueWithUnit( this.values.font_size ) );
				}

				text_styles = _.fusionGetFontStyle( 'text_font', values, 'object' );
				jQuery.each( text_styles, function( rule, value ) {
					self.addCssProperty( self.baseSelector, rule, value );
				} );

				if ( !this.isDefault( 'line_height' ) ) {
				  this.addCssProperty( this.baseSelector, 'line-height', this.values.line_height );
				}

				if ( !this.isDefault( 'text_transform' ) ) {
				  this.addCssProperty( this.baseSelector, 'text-transform', this.values.text_transform );
				}

				if ( !this.isDefault( 'letter_spacing' ) ) {
				  this.addCssProperty( this.baseSelector, 'letter-spacing',  _.fusionGetValueWithUnit( this.values.letter_spacing ) );
				}

				if ( !this.isDefault( 'text_color' ) ) {
				  this.addCssProperty( this.baseSelector, 'color',  this.values.text_color );
				}

				css = this.parseCSS();
				return ( css ) ? '<style>' + css + '</style>' : '';
			},

			/**
			 * Builds attributes.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr         = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-content-tb fusion-content-tb-' + this.model.get( 'cid' ),
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
			}
		} );
	} );
}( jQuery ) );
