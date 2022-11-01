var FusionPageBuilder = FusionPageBuilder || {};

( function( $ ) {

	$( document ).ready( function() {

		FusionPageBuilder.ModuleSettingsColumnView = FusionPageBuilder.ElementSettingsView.extend( {
			onInit: function() {}, // eslint-disable-line no-empty-function

			filterAttributes: function( attributes ) {
				var params      = this.model.get( 'params' ),
					priceParams = jQuery.extend( true, {}, this.model.get( 'priceParams' ) );

				params = jQuery.extend( true, {}, priceParams, params );

				params.footer_content = this.model.get( 'footerContent' );

				params.feature_rows = this.model.get( 'featureRows' );

				attributes.params = params;

				return attributes;
			}

		} );

	} );

}( jQuery ) );
