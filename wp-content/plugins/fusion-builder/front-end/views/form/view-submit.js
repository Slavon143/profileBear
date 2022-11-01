var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Hidden View.
		FusionPageBuilder.fusion_form_submit = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
                var attributes = {};

                if ( ! this.btnInstance ) {
                    this.btnInstance = new FusionPageBuilder.fusion_button( {
                        model: this.model,
                        attributes: atts
                    } );
                    this.btnInstance.elementTemplate = FusionPageBuilder.template( jQuery( '#tmpl-fusion_button-shortcode' ).html() );
                }

                // Create attribute
                attributes.html = this.generateFormFieldHtml( this.btnInstance.render().$el.html() );

				return attributes;
			}

		} );
	} );
}( jQuery ) );
