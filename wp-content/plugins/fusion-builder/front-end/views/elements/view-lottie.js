var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Lottie Element View.
		FusionPageBuilder.fusion_lottie = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				this.isFlex = this.flexDisplay();

				// Create attribute objects
				attributes.attr        = this.buildAttr( atts.values );
				attributes.wrapperAttr = this.buildWrapperAttr( atts.values );
				attributes.tag         = '' !== atts.values.link ? 'a' : 'div';
				attributes.styleBlock  = _.fusionGetFilterStyleElem( atts.values, '.fusion-lottie-' + this.model.get( 'cid' ), this.model.get( 'cid' )  );

				return attributes;
			},

			buildAttr: function( values ) {
				var attr = {
						'class': 'fusion-lottie-animation',
						'style': ''
					},
					alignClasses = {
						'center': 'mx-auto',
						'left': 'mr-auto',
						'right': 'ml-auto'
					},
					alignLarge,
					alignMedium,
					alignSmall;

				if ( '' !== values.json ) {
					attr[ 'data-path' ] = values.json;
					attr[ 'data-loop' ]    = 'yes' === values.loop ? 1 : 0;
					attr[ 'data-reverse' ] = 'yes' === values.reverse ? 1 : 0;
					attr[ 'data-speed' ]   = values.speed;
					attr[ 'data-trigger' ] = values.trigger;
					if ( 'viewport' === values.trigger ) {
						attr[ 'data-animationoffset' ] = values.trigger_offset;
					}
				}

				if ( values.max_width ) {
					attr.style += 'width:100%;max-width:' + values.max_width + ';';

					if ( '' !== values.link ) {
						attr.style += 'display:block;';
					}
				}

				// Link if set.
				if ( '' !== values.link ) {
					attr.href   = values.link;
					attr.target = values.target;
					if ( '_blank' === values.target ) {
						attr.rel = 'noopener noreferrer';
					}
				}

				if ( this.isFlex ) {
					alignLarge  = values.align && 'none' !== values.align ? values.align : false,
					alignMedium = values.align_medium && 'none' !== values.align_medium ? values.align_medium : false,
					alignSmall  = values.align_small && 'none' !== values.align_small ? values.align_small : false;

					if ( alignLarge ) {
						attr[ 'class' ] += ' lg-' + alignClasses[ alignLarge ];
					}

					if ( alignMedium ) {
						attr[ 'class' ] += ' md-' + alignClasses[ alignMedium ];
					}

					if ( alignSmall ) {
						attr[ 'class' ] += ' sm-' + alignClasses[ alignSmall ];
					}
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values ) {

				var attr = {
						style: '',
						'class': 'fusion-lottie fusion-lottie-' + this.model.get( 'cid' ),
						'data-id': this.model.get( 'cid' )
					};

				// Hide on mobile.
				attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

				if ( '' !== values.id ) {
					attr.id = values.id;
				}
				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				attr = _.fusionAnimations( values, attr );

				// Margins.
				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' +  _.fusionValidateAttrValue( values.margin_top, 'px' ) + ';';
				}
				if ( '' !== values.margin_right ) {
					attr.style += 'margin-right:' + _.fusionValidateAttrValue( values.margin_right, 'px' ) + ';';
				}
				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + _.fusionValidateAttrValue( values.margin_bottom, 'px' ) + ';';
				}
				if ( '' !== values.margin_left ) {
					attr.style += 'margin-left:' + _.fusionValidateAttrValue( values.margin_left, 'px' ) + ';';
				}

				return attr;
			}
		} );
	} );
}( jQuery ) );
