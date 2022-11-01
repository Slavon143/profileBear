var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Lightbox View.
		FusionPageBuilder.layerslider = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				window.FusionApp.injectScripts( this.model.get( 'cid' ) );
			},

			filterTemplateAtts: function( atts ) {
				if ( 'undefined' !== typeof atts.markup && 'undefined' !== typeof atts.markup.output ) {
					atts.markup.output = window.FusionApp.removeScripts( atts.markup.output, this.model.get( 'cid' ) );
				}
				return atts;
			}

		} );
	} );
}( jQuery ) );
