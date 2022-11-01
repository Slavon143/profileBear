var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsTableView = FusionPageBuilder.ElementSettingsView.extend( {

			template: FusionPageBuilder.template( $( '#fusion-builder-block-module-settings-table-template' ).html() )

		} );

	} );

}( jQuery ) );
