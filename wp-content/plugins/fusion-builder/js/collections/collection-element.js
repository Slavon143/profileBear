var FusionPageBuilder = FusionPageBuilder || {};
( function() {

	jQuery( document ).ready( function() {

		// Element collection
		FusionPageBuilder.Collection = Backbone.Collection.extend( {
			model: FusionPageBuilder.Element
		} );
		window.FusionPageBuilderElements = new FusionPageBuilder.Collection();
	} );
}( jQuery ) );
