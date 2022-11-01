/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Breadcrumbs view.
		FusionPageBuilder.fusion_breadcrumbs = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );

				attributes.output      = this.buildOutput( atts );

				// Any extras that need passed on.
				attributes.cid = this.model.get( 'cid' );

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
				values.font_size = _.fusionValidateAttrValue( values.font_size, 'px' );
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-breadcrumbs' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.breadcrumbs ) {
					output = atts.query_data.breadcrumbs;
				}

				if ( ( FusionApp.data.is_home || FusionApp.data.is_front_page ) && 1 < jQuery( jQuery.parseHTML( output ) ).filter( '.fusion-breadcrumb-item' ).length ) {
					output = jQuery( jQuery.parseHTML( output ) ).filter( '.fusion-breadcrumb-item' ).eq( 1 ).remove().html();
				}

				return output;
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
						class: 'fusion-breadcrumbs fusion-live-breadcrumbs fusion-breadcrumbs-' + this.model.get( 'cid' ),
						style: ''
					} );

				attr[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( '' !== values.alignment ) {
					attr.style += 'text-align:' + values.alignment + ';';
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
			 * Builds styles.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildStyleBlock: function( values ) {
				var style = '<style type="text/css">';

				if ( '' !== values.font_size ) {
					style += '.fusion-breadcrumbs.fusion-breadcrumbs-' + this.model.get( 'cid' ) + '{font-size:' + values.font_size + ';}';
				}

				if ( '' !== values.text_hover_color ) {
					style += '.fusion-breadcrumbs.fusion-breadcrumbs-' + this.model.get( 'cid' ) + ' span a:hover{color:' + values.text_hover_color + '!important;}';
				}

				if ( '' !== values.text_color ) {
					style += '.fusion-breadcrumbs.fusion-breadcrumbs-' + this.model.get( 'cid' ) + ',';
					style += '.fusion-breadcrumbs.fusion-breadcrumbs-' + this.model.get( 'cid' ) + ' a{color:' + values.text_color + ';}';
				}

				if ( FusionApp.data.is_home || FusionApp.data.is_front_page ) {
					style += '.fusion-breadcrumbs.fusion-breadcrumbs-' + this.model.get( 'cid' ) + ' .fusion-breadcrumb-prefix{display:none}';
					style += '.fusion-breadcrumbs.fusion-breadcrumbs-' + this.model.get( 'cid' ) + ' .fusion-breadcrumb-sep{display:none}';
				}

				style += '</style>';

				return style;
			}

		} );
	} );
}( jQuery ) );
