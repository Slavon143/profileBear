var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Highlight Element View.
		FusionPageBuilder.fusion_highlight = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object} - Return the attributes object.
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects
				attributes.attr   = this.buildAttr( atts.values );

				// Any extras that need passed on.
				attributes.output = atts.values.element_content;

				return attributes;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object} - Return the attributes object.
			 */
			buildAttr: function( values ) {
				var highlightShortcode = {
						class: 'fusion-highlight',
						style: ''
					},
					brightnessLevel = jQuery.AWB_Color( values.color ).lightness() * 100;

				if ( values.text_color ) {
					highlightShortcode[ 'class' ] += ' custom-textcolor';
				} else {
					highlightShortcode[ 'class' ] += ( 50 < brightnessLevel ) ? ' light' : ' dark';
				}

				if ( '' !== values[ 'class' ] ) {
					highlightShortcode[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					highlightShortcode.id = values.id;
				}

				if ( 'black' === values.color ) {
					highlightShortcode[ 'class' ] += ' highlight2';
				} else {
					highlightShortcode[ 'class' ] += ' highlight1';
				}

				if ( 'no' !== values.background ) {
					if ( 'full' === values.background_style ) {
						highlightShortcode[ 'class' ] += ' awb-highlight-background';
						highlightShortcode.style += 'background-color:' + values.color + ';';
						if ( 'yes' === values.rounded ) {
							highlightShortcode[ 'class' ] += ' rounded';
						}
					} else {
						highlightShortcode.style += 'background:linear-gradient(to top, ' + values.color + ' 40%, transparent 40%);';
					}
				} else if ( 'yes' === values.gradient_font ) {
					highlightShortcode.style      += _.getGradientFontString( values );
					highlightShortcode[ 'class' ] += ' awb-gradient-text';
				}

				if ( values.text_color ) {
					highlightShortcode.style += 'color:' + values.text_color + ';';
				}

				return highlightShortcode;
			}
		} );
	} );
}( jQuery ) );
