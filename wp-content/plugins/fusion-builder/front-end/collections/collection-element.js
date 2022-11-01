var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Element collection
	FusionPageBuilder.Collection = Backbone.Collection.extend( {
		model: FusionPageBuilder.Element
	} );

	window.FusionPageBuilderElements = new FusionPageBuilder.Collection(); // jshint ignore: line

}( jQuery ) );
