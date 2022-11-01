var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Code block view.
		FusionPageBuilder.fusion_code = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Create attribute objects
				attributes.content = atts.params.element_content;
				attributes.label   = window.fusionAllElements[ this.model.get( 'element_type' ) ].name;
				attributes.icon    = window.fusionAllElements[ this.model.get( 'element_type' ) ].icon;

				// Any extras that need passed on.
				attributes.cid = this.model.get( 'cid' );

				return attributes;
			}

		} );
	} );
}( jQuery ) );
