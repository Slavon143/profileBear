var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionCodeBlock = {
	optionCodeBlock: function( $element ) {
		var self = this,
			$codeBlock,
			codeBlockId,
			codeElement,
			codeBlockLang,
			codeMirrorJSON;

		$element   = $element || this.$el;
		$codeBlock = $element.find( '.fusion-builder-code-block' );

		self.codeEditorOption = {};

		if ( $codeBlock.length ) {

			$codeBlock.each( function( index ) {
				codeBlockId   = jQuery( this ).attr( 'id' );
				codeElement   = $element.find( '#' + codeBlockId );
				codeBlockLang = jQuery( this ).data( 'language' );

				// Get wp.CodeMirror object json.
				codeMirrorJSON = $element.find( '.' + codeBlockId ).val();
				if ( 'undefined' !== typeof codeMirrorJSON ) {
					codeMirrorJSON = JSON.parse( codeMirrorJSON );
					codeMirrorJSON.lineNumbers = true;
					codeMirrorJSON.lineWrapping = true;
				}
				if ( 'undefined' !== typeof codeBlockLang && 'default' !== codeBlockLang ) {
					codeMirrorJSON.mode = 'text/' + codeBlockLang;
				}

				// Set index so it can be referenced.
				jQuery( this ).closest( ' .fusion-builder-option' ).attr( 'data-index', index );

				self.codeEditorOption[ index ] = wp.CodeMirror.fromTextArea( codeElement[ 0 ], codeMirrorJSON );
				self.codeEditorOption[ index ].on( 'renderLine', function( cm, line, elt ) {
					var off = wp.CodeMirror.countColumn( line.text, null, cm.getOption( 'tabSize' ) ) * self.codeEditorOption[ index ].defaultCharWidth();
					elt.style.textIndent = '-' + off + 'px';
					elt.style.paddingLeft = ( 4 + off ) + 'px';
				} );
				self.codeEditorOption[ index ].refresh();

				// Refresh editor after initialization
				setTimeout( function() {
					self.codeEditorOption[ index ].refresh();
					self.codeEditorOption[ index ].focus();
				}, 100 );

			} );
		}
	}
};
