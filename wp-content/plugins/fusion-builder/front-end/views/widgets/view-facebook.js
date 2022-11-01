/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.Fusion_Widget_Facebook_Page = FusionPageBuilder.fusion_widget_content.extend( {

			afterGetHTML: function() {
				if ( 'undefined' !== typeof FusionApp.previewWindow.FB ) {
					FusionApp.previewWindow.FB.XFBML.parse();
				}
			}

		} );

	} );

}() );
