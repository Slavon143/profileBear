var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Number View.
		FusionPageBuilder.fusion_form_number = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects;
				attributes.styles = this.buildStyles( atts.values );
				attributes.html   = this.generateFormFieldHtml( this.generateInputField( atts.values, 'number' ) );

				return attributes;
			}

		} );
	} );
}( jQuery ) );
