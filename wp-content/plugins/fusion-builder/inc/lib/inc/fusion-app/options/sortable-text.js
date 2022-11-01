var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionSortableText = {
	optionSortableText: function( $element ) {
		var $sortable;
		$element  = $element || this.$el;
		$sortable = $element.find( '.fusion-sortable-text-options' );

		$sortable.each( function() {
			var $sort = jQuery( this );

			$sort.sortable( {
				handle: '.fusion-sortable-move'
			} );
			$sort.on( 'sortupdate', function( event ) {
				var sortContainer = jQuery( event.target ),
					sortOrder = '';

				sortContainer.children( '.fusion-sortable-option' ).each( function() {
					sortOrder += jQuery( this ).find( 'input' ).val() + '|';
				} );

				sortOrder = sortOrder.slice( 0, -1 );

				sortContainer.siblings( '.sort-order' ).val( sortOrder ).trigger( 'change' );
			} );

			$sort.on( 'click', '.fusion-sortable-remove', function( event ) {
				event.preventDefault();

				jQuery( event.target ).closest( '.fusion-sortable-option' ).remove();
				$sort.trigger( 'sortupdate' );
			} );

			$sort.on( 'change keyup', 'input', function() {
				$sort.trigger( 'sortupdate' );
			} );

			$sort.prev( '.fusion-builder-add-sortable-child' ).on( 'click', function( event ) {
				var $newItem = $sort.next( '.fusion-placeholder-example' ).clone( true );

				event.preventDefault();

				$newItem.removeClass( 'fusion-placeholder-example' ).removeAttr( 'style' ).appendTo( $sort );

				setTimeout( function() {
					$sort.find( '.fusion-sortable-option:last-child input' ).focus();
				}, 100 );

				$sort.trigger( 'sortupdate' );
			} );
		} );
	}
};
