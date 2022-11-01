/* global tinymce, FusionPageBuilderApp, openShortcodeGenerator */
( function( $ ) {
	if ( 'undefined' !== typeof tinymce ) {

		tinymce.PluginManager.add( 'fusion_button', function( editor ) {
			if ( ( ( 'undefined' !== typeof FusionPageBuilderApp && true === FusionPageBuilderApp.allowShortcodeGenerator && true !== FusionPageBuilderApp.shortcodeGenerator ) || 'content' === editor.id || 'excerpt' === editor.id ) || ( ( jQuery( 'body' ).hasClass( 'gutenberg-editor-page' ) || jQuery( 'body' ).hasClass( 'block-editor-page' ) ) && 0 === editor.id.indexOf( 'editor-' ) ) ) {

				editor.addButton( 'fusion_button', {
					title: 'Avada Builder Element Generator',
					icon: 'insertdatetime',
					onclick: function() {

						// Set editor that triggered shortcode generator.
						if ( 'undefined' !== typeof FusionPageBuilderApp ) {
							FusionPageBuilderApp.shortcodeGeneratorActiveEditor = editor;
						}

						// Open shortcode generator.
						openShortcodeGenerator( $( this ) );
					}
				} );
			}
		} );
	}
}( jQuery ) );
