var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {
		// Fusion Form Hidden View.
		FusionPageBuilder.fusion_form_hidden = FusionPageBuilder.FormComponentView.extend( {

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
				attributes.name   = atts.values.label;
				attributes.label  = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon   = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				return attributes;
			}

		} );
	} );
}( jQuery ) );
