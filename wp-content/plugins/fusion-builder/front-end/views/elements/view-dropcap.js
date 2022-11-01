var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Dropcap Element View.
		FusionPageBuilder.fusion_dropcap = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object} - Returns the attributes.
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );

				// Create attribute objects
				attributes.attr = this.buildAttr( atts.values );

				// Any extras that need passed on.
				attributes.output = atts.values.element_content;

				return attributes;
			},

			/**
			 * Modifies values.
			 *
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {

				// Make sure the title text is not wrapped with an unattributed p tag.
				if ( 'undefined' !== typeof values.element_content ) {
					values.element_content = values.element_content.trim();
					values.element_content = values.element_content.replace( /(<p[^>]+?>|<p>|<\/p>)/img, '' );
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object} - Returns the element attributes.
			 */
			buildAttr: function( values ) {
				var params = this.model.get( 'params' ),
					attr   = {
						class: 'fusion-dropcap dropcap',
						style: ''
					},
					usingDefaultColor = ( 'undefined' !== typeof params.color && '' === params.color ) || 'undefined' === typeof params.color;

				if ( 'yes' === values.boxed ) {
					attr[ 'class' ] += ' dropcap-boxed';

					if ( values.boxed_radius || '0' === values.boxed_radius ) {
						values.boxed_radius = ( 'round' === values.boxed_radius ) ? '50%' : values.boxed_radius;
						attr.style = 'border-radius:' + values.boxed_radius + ';';
					}

					if ( ! usingDefaultColor ) {
						attr.style += 'background-color:' + values.color + ';';
						attr.style += 'color:' + values.text_color + ';';
					}
				} else if ( ! usingDefaultColor ) {
					attr.style += 'color:' + values.color + ';';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			}
		} );
	} );
}( jQuery ) );
