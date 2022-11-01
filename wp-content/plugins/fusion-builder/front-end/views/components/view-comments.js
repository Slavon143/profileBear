var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Comments view.
		FusionPageBuilder.fusion_tb_comments = FusionPageBuilder.ElementView.extend( {

			onInit: function() {
				if ( this.model.attributes.markup && '' === this.model.attributes.markup.output ) {
					this.model.attributes.markup.output = this.getComponentPlaceholder();
				}
			},

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
				this.values = atts.values;

				attributes.wrapperAttr = this.buildAttr( atts.values );
				attributes.styles      = this.buildStyleBlock( atts.values );

				attributes.output      = this.buildOutput( atts );
				attributes.placeholder = this.getComponentPlaceholder();

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
				values.border_size = _.fusionValidateAttrValue( values.border_size, 'px' );
				values.padding     = _.fusionValidateAttrValue( values.padding, 'px' );
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
					output = jQuery( jQuery.parseHTML( atts.markup.output ) ).filter( '.fusion-comments-tb' ).html();
					output = ( 'undefined' === typeof output ) ? atts.markup.output : output;
				} else if ( 'undefined' !== typeof atts.query_data && 'undefined' !== typeof atts.query_data.comments ) {
					output = atts.query_data.comments;
				}

				_.each( jQuery( jQuery.parseHTML( output ) ).find( 'h1, h2, h3, h4, h5, h6' ), function( item ) {
					title  = _.buildTitleElement( atts.values, atts.extras, jQuery( item ).html() );
					output = output.replace( jQuery( item ).parent().prop( 'outerHTML' ), title );
				} );

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
						class: 'fusion-comments-tb fusion-live-comments-tb fusion-comments-tb-' + this.model.get( 'cid' ) + ' fusion-order-' + values.template_order.split( ',' )[ 0 ].replace( '_', '-' ),
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
				var selectors, css;
				this.baseSelector = '.fusion-comments-tb.fusion-comments-tb-' +  this.model.get( 'cid' );
				this.dynamic_css  = {};

				selectors = this.baseSelector + ' .commentlist .the-comment';

				if ( !this.isDefault( 'border_size' ) ) {
					this.addCssProperty( selectors, 'border-bottom-width',  values.border_size );
				}

				if ( !this.isDefault( 'border_color' ) ) {
					this.addCssProperty( selectors, 'border-color',  values.border_color );
				}

				if ( 'hide' === values.avatar ) {
					this.addCssProperty( this.baseSelector + ' .commentlist .the-comment .comment-text', 'margin-left',  '0px' );
				}

				if ( 'circle' === values.avatar ) {
					this.addCssProperty( this.baseSelector + '.circle .the-comment .avatar', 'border-radius',  '50%' );
				}

				if ( 'square' === values.avatar ) {
					this.addCssProperty( this.baseSelector + '.square .the-comment .avatar', 'border-radius',  '0' );
				}

				if ( 'hide' === values.avatar ) {
					this.addCssProperty( this.baseSelector + ' .the-comment .avatar', 'display',  'none' );
				}

				if ( '' !== values.padding ) {
					this.addCssProperty( this.baseSelector + ' .commentlist .children', 'padding-left',  values.padding );
				}

				if ( 'hide' === values.headings ) {
					this.addCssProperty( this.baseSelector + ' .fusion-title', 'display',  'none' );
				}

				if ( !this.isDefault( 'heading_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .fusion-title h' + values.heading_size, 'color',  values.heading_color, true );
				}

				if ( !this.isDefault( 'link_color' ) ) {
					this.addCssProperty( this.baseSelector + ' a', 'color', values.link_color );
					this.addCssProperty( this.baseSelector + ' .comment-author.meta a', 'color', values.link_color, true );
				}

				if ( !this.isDefault( 'link_hover_color' ) ) {
					this.addCssProperty( this.baseSelector + ' a:hover', 'color', values.link_hover_color );
					this.addCssProperty( this.baseSelector + ' .comment-author.meta a:hover', 'color', values.link_hover_color, true );
				}

				if ( !this.isDefault( 'text_color' ) ) {
					this.addCssProperty( this.baseSelector, 'color', values.text_color );
				}

				if ( !this.isDefault( 'meta_color' ) ) {
					this.addCssProperty( this.baseSelector + ' .comment-author.meta', 'color', values.meta_color, true );
				}


				css = this.parseCSS();
				return ( css ) ? '<style type="text/css">' + css + '</style>' : '';
			}

		} );
	} );
}( jQuery ) );
