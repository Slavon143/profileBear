var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		// Column View
		FusionPageBuilder.ColumnView = FusionPageBuilder.BaseColumnView.extend( {

			template: FusionPageBuilder.template( $( '#fusion-builder-column-template' ).html() ),

			events: {
				'click .fusion-builder-add-element:not(.fusion-builder-column-inner .fusion-builder-add-element)': 'addModule',
				'click .fusion-builder-settings-column:not(.fusion-builder-column-inner .fusion-builder-settings-column)': 'showSettings',
				'click .fusion-builder-resize-column:not(.fusion-builder-column-inner .fusion-builder-resize-column)': 'columnSizeDialog',
				'click .column-size:not(.fusion-builder-column-inner .column-size)': 'columnSize',
				'click .fusion-builder-clone-column:not(.fusion-builder-column-inner .fusion-builder-clone-column)': 'cloneColumn',
				'click .fusion-builder-remove-column:not(.fusion-builder-column-inner .fusion-builder-remove-column)': 'removeColumn',
				'click .fusion-builder-save-column-dialog:not(.fusion-builder-column-inner .fusion-builder-save-column-dialog)': 'saveColumnDialog'
			}

		} );

	} );

}( jQuery ) );
