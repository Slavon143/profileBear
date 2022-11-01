var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Builder Container View
        FusionPageBuilder.DemoImportModalView = FusionPageBuilder.StudioImportModalView.extend( {
			template: FusionPageBuilder.template( jQuery( '#fusion-builder-demo-import-modal' ).html() )
		} );
	} );
}( jQuery ) );
