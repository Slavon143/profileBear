var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Checkbox View.
		FusionPageBuilder.fusion_form_checkbox = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.1
			 * @param {Object} atts - The attributes object.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};
				var required_can_be_shown = false;

				if ( 'selection' ===  atts.values.required ) {
					required_can_be_shown = ( 0 < parseInt( atts.values.min_required ) );

					// Do not show "*" if there is no minimum required fields.
					if ( ! required_can_be_shown ) {
						atts.values.required = '';
					}
				}

				// Create attribute objects;
				attributes.styles = this.buildStyles( atts.values );
				attributes.html   = this.generateFormFieldHtml( this.checkbox( atts.values, 'checkbox' ) );

				return attributes;
			}

		} );
	} );
}( jQuery ) );
