var FusionPageBuilder = FusionPageBuilder || {};
( function() {

	jQuery( document ).ready( function() {

		// Slider revolution View.
		FusionPageBuilder.rev_slider = FusionPageBuilder.ElementView.extend( {

			filterRenderContent: function( output ) {
				return this.filterDuplicates( this.disableInlineScripts( output ) );
			},
			filterOutput: function( output ) {
				return this.filterDuplicates( output );
			},
			filterDuplicates: function( output ) {
				if ( jQuery( '#fb-preview' ).contents().find( 'rs-module-wrap[data-alias="' + this.model.get( 'params' ).alias + '"]' ).length ) {
					return '<div class="fusion-builder-placeholder">' + window.fusionBuilderText.duplicate_slider_revolution + '</div>';
				}
				return output;
			},
			disableInlineScripts: function( output ) {
				if ( -1 !== output.indexOf( 'rev_slider_error' ) || ( -1 !== output.indexOf( 'rs_error_message' ) && -1 !== output.indexOf( '<script' ) && -1 !== output.indexOf( '</script>' ) ) ) {
					output = output.replace( '<script', '<!--<script' ).replace( '</script>', '</script>-->' ).replace( '<rs-module id="', '<rs-module style="dusplay: block; visibility:visible;" id="' );
				}
				return output;
			}
		} );
	} );
}( jQuery ) );
