var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionDimensionField = {
	optionDimension: function( element ) {
		var dimensionField;

		element        = element || this.$el;
		dimensionField = element.find( '.single-builder-dimension' );

		if ( dimensionField.length ) {
			dimensionField.each( function() {
				jQuery( this ).find( '.fusion-builder-dimension input' ).on( 'change paste keyup', function() {
					jQuery( this ).closest( '.single-builder-dimension' ).find( 'input[type="hidden"]' ).val(
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(1) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(1) input' ).val() : '0' ) + ' ' +
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(2) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(2) input' ).val() : '0' ) + ' ' +
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(3) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(3) input' ).val() : '0' ) + ' ' +
						( ( jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(4) input' ).val().length ) ? jQuery( this ).closest( '.single-builder-dimension' ).find( 'div:nth-child(4) input' ).val() : '0' )
					).trigger( 'change' );
				} );
			} );
		}
	}
};
