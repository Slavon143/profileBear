/* global FusionPageBuilderApp, FusionPageBuilder, FusionPageBuilderElements */
/* eslint no-unused-vars: 0 */

( function() {

	// Insert shortcode into post editor
	window.fusionBuilderInsertIntoEditor = function( shortcode, editorID ) { // jshint ignore:line
		var editorArea,
			editor;

		if ( 'tinymce' === window.SCmoduleContentEditorMode && ( '' === editorID || 'undefined' === typeof editorID ) ) {

			if ( 'undefined' !== typeof window.tinyMCE ) {

				// Set active editor
				editor = FusionPageBuilderApp.shortcodeGeneratorActiveEditor;
				editor.focus();

				if ( 'excerpt' === editor.id ) {
					FusionPageBuilderApp.fromExcerpt = true;
				}

				// Insert shortcode
				window.tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, shortcode );
				window.tinyMCE.activeEditor.execCommand( 'mceCleanup', false );
			}

		} else {

			if ( null === editorID || '' === editorID || 'undefined' === typeof editorID ) {
				editorArea = jQuery( window.editorArea );

			} else {
				editorArea = jQuery( '#' + editorID );
			}

			if ( 'excerpt' === editorArea.attr( 'id' ) ) {
				FusionPageBuilderApp.fromExcerpt = true;
			}

			if ( 'undefined' === typeof window.cursorPosition ) {
				if ( 0 === editorArea.getCursorPosition() ) {
					editorArea.val( shortcode + editorArea.val() );
				} else if ( editorArea.val().length === editorArea.getCursorPosition() ) {
					editorArea.val( editorArea.val() + shortcode );
				} else {
					editorArea.val( editorArea.val().slice( 0, editorArea.getCursorPosition() ) + shortcode + editorArea.val().slice( editorArea.getCursorPosition() ) );
				}
			} else {
				editorArea.val( [ editorArea.val().slice( 0, window.cursorPosition ), shortcode, editorArea.val().slice( window.cursorPosition ) ].join( '' ) );
			}

			editorArea.trigger( 'change' );
		}

		if ( false === FusionPageBuilderApp.manuallyAdded ) {
			FusionPageBuilderApp.shortcodeGeneratorActiveEditor = '';
		}
	};

}( jQuery ) );

function openShortcodeGenerator( trigger ) { // jshint ignore:line

	// Get editor id from event.trigger.  parent.parent

	var view,
		viewSettings,
		editorArea,
		editorCid;

	if ( 'object' === typeof trigger && 'undefined' !== typeof trigger[ 0 ].$el ) {
		trigger = trigger[ 0 ].$el;
	}

	editorArea = '#' + trigger.parent().parent().find( '.wp-editor-area' ).attr( 'id' );
	editorCid  = trigger.closest( '.fusion-builder-module-settings' ).attr( 'data-element-cid' );

	window.cursorPosition = 0;
	window.editorArea = editorArea;

	// Set shortcode generator flag
	FusionPageBuilderApp.shortcodeGenerator = true;

	// Get active editor mode
	if ( FusionPageBuilderApp.isTinyMceActive() ) {
		window.SCmoduleContentEditorMode = 'tinymce';
	} else {
		window.SCmoduleContentEditorMode = 'html';
	}

	// Get current cursor position ( for html editor )
	if ( 'tinymce' !== window.SCmoduleContentEditorMode ) {
		window.cursorPosition = jQuery( editorArea ).getCursorPosition();
	}

	viewSettings = {
		collection: FusionPageBuilderElements,
		view: this,
		targetCid: editorCid
	},

	view = new FusionPageBuilder.GeneratorElementsView( viewSettings );

	jQuery( view.render().el ).dialog( {
		title: 'Select Element',
		draggable: false,
		modal: true,
		resizable: false,
		dialogClass: 'fusion-builder-dialog fusion-builder-large-library-dialog fusion-builder-element-library-dialog',
		open: function( event, ui ) { // jshint ignore: line
			window.FusionApp.dialog.resizeDialog();
		},
		close: function( event, ui ) { // jshint ignore: line
			view.remove();
		}
	} );
}

// Helper function to check the cursor position of text editor content field before the shortcode generator is opened
( function() {
	jQuery.fn.getCursorPosition = function() {
		var el  = jQuery( this ).get( 0 ),
			pos = 0,
			Sel,
			SelLength;

		if ( 'selectionStart' in el ) {
			pos = el.selectionStart;
		} else if ( 'selection' in document ) {
			el.focus();
			Sel       = document.selection.createRange();
			SelLength = document.selection.createRange().text.length;
			Sel.moveStart( 'character', -el.value.length );
			pos = Sel.text.length - SelLength;
		}
		return pos;
	};
}( jQuery ) );
