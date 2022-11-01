var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	// Builder element model
	FusionPageBuilder.Element = Backbone.Model.extend( {
		defaults: {
			type: 'element'
		}
	} );
}( jQuery ) );
