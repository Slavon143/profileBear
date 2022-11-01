var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Radio View.
		FusionPageBuilder.fusion_form_radio = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.html   = this.generateFormFieldHtml( this.checkbox( atts.values, 'radio' ) );

				return attributes;
			}

		} );
	} );
}( jQuery ) );
