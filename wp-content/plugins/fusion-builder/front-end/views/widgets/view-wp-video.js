/* global FusionApp */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.WP_Widget_Media_Video = FusionPageBuilder.fusion_widget_content.extend( {

			onInit: function() {
				this.model.attributes.markup = '';
			},

			afterGetHTML: function() {
				var video = this.$el.find( 'video' );
				video.attr( 'id', 'video-' + this.model.get( 'cid' ) );
				FusionApp.previewWindow.wp.mediaelement.initialize();
			}

		} );

	} );

}() );
