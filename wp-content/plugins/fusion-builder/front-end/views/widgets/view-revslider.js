/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.RevSliderWidget = FusionPageBuilder.fusion_widget_content.extend( {

			beforeGetHTML: function() {
				var sliderId = this.$el.find( 'rs-module' ).attr( 'id' );
				// use preview context to correctly disable the revolution slider
				this.sliderInitiated = Boolean( sliderId );
				FusionApp.previewWindow.jQuery( '#' + sliderId ).revkill();
			},

            beforeRemove: function() {
                var sliderId = this.$el.find( 'rs-module' ).attr( 'id' );
                // use preview context to correctly disable the revolution slider
                FusionApp.previewWindow.jQuery( '#' + sliderId ).revkill();
            },

            filterRenderContent: function( output ) {
				var result = this.filterDuplicates( this.disableInlineScripts( output, this.sliderInitiated ) );
				return result;
			},

            filterDuplicates: function( output ) {
				var alias = output.match( /(data-alias="(.*?)")/g );
				alias = alias && 0 > alias.length ? alias[ 0 ].split( '=' )[ 1 ] : '""';

				if ( jQuery( '#fb-preview' ).contents().find( 'rs-module-wrap[data-alias=' + alias + ']' ).length ) {
					return '<div class="fusion-builder-placeholder">' + window.fusionBuilderText.duplicate_slider_revolution + '</div>';
				}
				return output;
			},
			disableInlineScripts: function( output, force ) {
				if ( ( -1 !== output.indexOf( 'rev_slider_error' ) && -1 !== output.indexOf( '<script' ) && -1 !== output.indexOf( '</script>' ) )  || force ) {
					output = output.replace( '<script', '<!--<script' ).replace( '</script>', '</script>-->' );
				}
				return output;
			}

		} );

	} );

}() );
