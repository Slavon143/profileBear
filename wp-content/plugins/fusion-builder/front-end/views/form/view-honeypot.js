var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Honeypot View.
		FusionPageBuilder.fusion_form_honeypot = FusionPageBuilder.FormComponentView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.5
			 * @return {Object}
			 */
			filterTemplateAtts: function() {
				var attributes = {};

				// Create attribute objects.
				attributes.label  = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon   = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				return attributes;
			}

		} );
	} );
}( jQuery ) );
