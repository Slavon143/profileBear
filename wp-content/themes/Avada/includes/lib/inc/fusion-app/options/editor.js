/* global FusionPageBuilderApp */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionEditor = {

	optionEditor: function( $element ) {
		var allowGenerator   = false,
			thisModel        = this.model,
			content          = '',
			$contentTextareaOption,
			textareaID,
			$contentTextareas,
			$theContent;

		$element          = $element || this.$el;
		$contentTextareas = $element.find( '.fusion-editor-field' );

		if ( 'undefined' !== typeof thisModel.get( 'allow_generator' ) && true === thisModel.get( 'allow_generator' ) ) {
			FusionPageBuilderApp.allowShortcodeGenerator = true;
			allowGenerator = true;
		}

		if ( $contentTextareas.length ) {
			$contentTextareas.each( function() {
				var $contentTextarea = jQuery( this );

				$contentTextareaOption = $contentTextarea.closest( '.fusion-builder-option' );

				content = $contentTextarea.html();

				if ( 'undefined' !== typeof thisModel.get( 'multi' ) && 'multi_element_parent' === thisModel.get( 'multi' ) ) {

					$contentTextareaOption.hide();
					$contentTextarea.attr( 'id', 'fusion_builder_content_main' );
					return;
				}

				if ( 'undefined' !== typeof thisModel.get( 'multi' ) && 'multi_element_child' === thisModel.get( 'multi' ) && 'fusion_pricing_column' !== thisModel.get( 'element_type' ) ) {
					$contentTextarea.attr( 'id', 'child_element_content' );
				}

				$contentTextarea.addClass( 'fusion-init' );

				// Called from shortcode generator
				if ( 'generated_element' === thisModel.get( 'type' ) ) {

					// TODO: unique id ( multiple mce )
					if ( 'multi_element_child' === thisModel.get( 'multi' ) ) {
						$contentTextarea.attr( 'id', 'generator_multi_child_content' );
					} else {
						$contentTextarea.attr( 'id', 'generator_element_content' );
					}

					textareaID = $contentTextarea.attr( 'id' );

					setTimeout( function() {
						$contentTextarea.wp_editor( content, textareaID );

						// If it is a placeholder, add an on focus listener.
						if ( jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
							window.tinyMCE.get( textareaID ).on( 'focus', function() {
								$theContent = window.tinyMCE.get( textareaID ).getContent();
								$theContent = jQuery( '<div/>' ).html( $theContent ).text();
								if ( $theContent === jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
									window.tinyMCE.get( textareaID ).setContent( '' );
								}
							} );
						}
						window.tinyMCE.get( textareaID ).on( 'keyup change', function() {
							var editor = window.tinyMCE.get( textareaID );

							$theContent = editor.getContent();
							jQuery( '#' + textareaID ).val( $theContent ).trigger( 'change' );
						} );
					}, 100 );
				} else {
					textareaID = $contentTextarea.attr( 'id' );

					setTimeout( function() {

						$contentTextarea.wp_editor( content, textareaID, allowGenerator );

						// If it is a placeholder, add an on focus listener.
						if ( jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
							window.tinyMCE.get( textareaID ).on( 'focus', function() {
								$theContent = window.tinyMCE.get( textareaID ).getContent();
								$theContent = jQuery( '<div/>' ).html( $theContent ).text();
								if ( $theContent === jQuery( '#' + textareaID ).data( 'placeholder' ) ) {
									window.tinyMCE.get( textareaID ).setContent( '' );
								}
							} );
						}

						if ( window.tinyMCE.get( textareaID ) ) {
							window.tinyMCE.get( textareaID ).on( 'keyup change', function() {
								var editor = window.tinyMCE.get( textareaID );

								$theContent = editor.getContent();
								jQuery( '#' + textareaID ).val( $theContent ).trigger( 'change' );
							} );
						}

					}, 100 );
				}
			} );
		}
	}
};
