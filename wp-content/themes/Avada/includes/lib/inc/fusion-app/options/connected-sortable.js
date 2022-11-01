var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionConnectedSortable = {
	optionConnectedSortable: function( $element ) {
		var $sortable;
		$sortable = $element.find( '.fusion-connected-sortable' );
		$sortable.sortable( {
			connectWith: '.fusion-connected-sortable',

			stop: function() {
				var $enabled = $element.find( '.fusion-connected-sortable-enabled' ),
					$container = $element.find( '.fusion-builder-option.connected_sortable' ),
					sortOrder     = '';
				$enabled.children( '.fusion-connected-sortable-option' ).each( function() {
					sortOrder += jQuery( this ).data( 'value' ) + ',';
				} );

				sortOrder = sortOrder.slice( 0, -1 );

				$container.find( '.fusion-connected-sortable' ).each( function() {
					if ( jQuery( this ).find( 'li' ).length ) {
						jQuery( this ).removeClass( 'empty' );
					} else {
						jQuery( this ).addClass( 'empty' );
					}
				} );

				$container.find( '.sort-order' ).val( sortOrder ).trigger( 'change' );
			}
		} ).disableSelection();
	}
};
