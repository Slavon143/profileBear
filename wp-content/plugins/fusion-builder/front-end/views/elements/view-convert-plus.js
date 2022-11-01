var FusionPageBuilder = FusionPageBuilder || {};
( function() {

	jQuery( document ).ready( function() {

		// Convert plus
		FusionPageBuilder.fusion_convert_plus = FusionPageBuilder.ElementView.extend( {

			beforeRemove: function() {
				var params     = this.model.get( 'params' ),
					moduleType = params.convert_plus_module,
					value      = params[ moduleType + '_id' ];

				if ( 'string' === typeof value && '' !== value ) {
					jQuery( '#fb-preview' ).contents().find( '.' + value ).remove();
				}
			}

		} );
	} );
}( jQuery ) );
