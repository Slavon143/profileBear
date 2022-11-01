var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSortable = {
	optionSortable: function( $element ) {
		var $sortable;
		$element  = $element || this.$el;
		$sortable = $element.find( '.fusion-sortable-options' );

		$sortable.each( function() {
			if ( '' === jQuery( this ).siblings( '.sort-order' ).val() ) {
				jQuery( this ).closest( '.pyre_metabox_field' ).find( '.fusion-builder-default-reset' ).addClass( 'checked' );
			}

			jQuery( this ).sortable();
			jQuery( this ).on( 'sortupdate', function( event ) {
				var sortContainer = jQuery( event.target ),
					sortOrder = '';

				sortContainer.children( '.fusion-sortable-option' ).each( function() {
					sortOrder += jQuery( this ).data( 'value' ) + ',';
				} );

				sortOrder = sortOrder.slice( 0, -1 );

				sortContainer.siblings( '.sort-order' ).val( sortOrder ).trigger( 'change' );
			} );
		} );
	}
};
