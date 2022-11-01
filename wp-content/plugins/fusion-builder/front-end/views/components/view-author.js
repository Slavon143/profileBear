var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Author view.
		FusionPageBuilder.fusion_tb_author = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.2
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );

				attributes.output      = this.buildOutput( atts );

				// Any extras that need passed on.
				attributes.cid = this.model.get( 'cid' );

				return attributes;
			},

			/**
			 * Builds output.
			 *
			 * @since  2.2
			 * @param  {Object} values - The values object.
			 * @return {String}
			 */
			buildOutput: function( atts ) {
				var output = '',
					title  = '';

				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output && 'undefined' === typeof atts.query_data ) {
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-author-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.author ) {
					output = atts.query_data.author;
				}

				title  = _.buildTitleElement( atts.values, atts.extras, jQuery( jQuery.parseHTML( output ) ).find( 'h1, h2, h3, h4, h5, h6' ).html() );
				output = output.replace( jQuery( jQuery.parseHTML( output ) ).find( 'h1, h2, h3, h4, h5, h6' ).parent().prop( 'outerHTML' ), title );

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
						class: 'about-author fusion-live-author-tb fusion-author-tb fusion-author-tb-' + this.model.get( 'cid' ),
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

				if ( 'hide' !== values.avatar ) {
					attr[ 'class' ] += ' ' + values.avatar;
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
				var styles = '<style type="text/css">';

				if ( 'circle' === values.avatar ) {
					styles += '.fusion-author-tb-' + this.model.get( 'cid' ) + '.circle .about-author-container .avatar{border-radius: 50%;}';
				}

				if ( 'square' === values.avatar ) {
					styles += '.fusion-author-tb-' + this.model.get( 'cid' ) + '.square .about-author-container .avatar{border-radius: 0;}';
				}

				if ( 'hide' === values.avatar ) {
					styles += '.fusion-author-tb-' + this.model.get( 'cid' ) + ' .about-author-container .avatar{display:none;}';
				}

				if ( 'hide' === values.headings ) {
					styles += '.fusion-author-tb-' + this.model.get( 'cid' ) + ' .fusion-title{display:none;}';
				}

				if ( 'hide' === values.biography ) {
					styles += '.fusion-author-tb-' + this.model.get( 'cid' ) + ' .about-author-container .description{display:none;}';
				}

				styles += '</style>';

				return styles;
			}

		} );
	} );
}( jQuery ) );
