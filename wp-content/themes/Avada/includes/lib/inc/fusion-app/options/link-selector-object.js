var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionLinkSelectorObject = {
	optionLinkSelectorObject: function( $element ) {
		var $linkSelector;
		$element      = $element || this.$el;
		$linkSelector = $element.find( '.fusion-link-selector-object' );

		$linkSelector.each( function() {
			var $thisOption       = jQuery( this ),
				$linkButton       = jQuery( this ).find( '.fusion-builder-link-button' ),
				$toggleButton     = jQuery( this ).find( '.button-link-type-toggle' ),
				$linkSubmit       = jQuery( '#wp-link-submit' ),
				$linkTitle        = jQuery( '.wp-link-text-field' ),
				$linkTarget       = jQuery( '.link-target' ),
				$fusionLinkSubmit = jQuery( '<input type="button" name="fusion-link-submit" id="fusion-link-submit" class="button-primary" value="Set Link">' ),
				wpLinkL10n        = window.wpLinkL10n,
				linkId            = jQuery( this ).find( '.fusion-builder-link-field' ).attr( 'id' ),
				$input,
				$linkDialog,
				linkUrl,
				$inputObject,
				$inputObjectId,
				$option,
				linkObject,
				linkObjectId,
				linkTitle;

			jQuery( $toggleButton ).on( 'click', function() {
				$thisOption.find( '.fusion-builder-link-field' ).removeAttr( 'readonly' );
				$thisOption.find( '.fusion-builder-object-field' ).val( 'custom' );
				$thisOption.find( '.fusion-builder-menu-item-type' ).text( 'custom' );
				$thisOption.find( '.fusion-builder-object-id-field' ).val( 0 );
				$thisOption.find( '.fusion-builder-link-field' ).removeAttr( 'readonly' );
				jQuery( this ).hide();
			} );

			jQuery( $linkButton ).on( 'click', function( event ) {
				$fusionLinkSubmit.insertBefore( $linkSubmit );
				$option = jQuery( event.target ).closest( ' .fusion-link-selector-object' );

				// The 3 inputs.
				$input           = $option.find( '.fusion-builder-link-field' );
				$inputObject     = $option.find( '.fusion-builder-object-field' );
				$inputObjectId   = $option.find( '.fusion-builder-object-id-field' );

				linkUrl  = $input.val();
				$linkSubmit.hide();
				$linkTitle.hide();
				$linkTarget.hide();
				$fusionLinkSubmit.show();

				$linkDialog = ! window.wpLink && jQuery.fn.wpdialog && jQuery( '#wp-link' ).length ? {
					$link: ! 1,
					open: function() {
						this.$link = jQuery( '#wp-link' ).wpdialog( {
							title: wpLinkL10n.title,
							width: 480,
							height: 'auto',
							modal: ! 0,
							dialogClass: 'wp-dialog',
							zIndex: 3e5
						} );

					},
					close: function() {
						this.$link.wpdialog( 'close' );
					}
				} : window.wpLink;

				$linkDialog.fusionUpdateLink = function( scopedEvent, $scopedFusionLinkSubmit ) {
					scopedEvent.preventDefault();
					scopedEvent.stopImmediatePropagation();
					scopedEvent.stopPropagation();

					linkUrl = jQuery( '#wp-link-url' ).length ? jQuery( '#wp-link-url' ).val() : jQuery( '#url-field' ).val();
					linkObject = 'custom';
					linkObjectId = 0;

					if ( jQuery( 'span[data-permalink="' + linkUrl + '"]' ).length ) {
						linkObject = jQuery( 'span[data-permalink="' + linkUrl + '"]' ).data( 'object' );
						linkObjectId = jQuery( 'span[data-permalink="' + linkUrl + '"]' ).data( 'id' );
						$input.attr( 'readonly', true );
						$option.find( '.button-link-type-toggle' ).show();

						// Update the title input.
						linkTitle = jQuery( 'span[data-permalink="' + linkUrl + '"]' ).closest( 'li' ).find( '.item-title' ).text();
						jQuery( '[data-save-id="title"] input' ).val( linkTitle ).trigger( 'change' );
					}

					// Update all 3 inputs.
					$input.val( linkUrl ).trigger( 'change' );
					$inputObject.val( linkObject ).trigger( 'change' );
					$inputObjectId.val( linkObjectId ).trigger( 'change' );

					// Update text of object type.
					$option.find( '.fusion-builder-menu-item-type' ).text( linkObject );

					$linkSubmit.show();
					$linkTitle.show();
					$linkTarget.show();
					$scopedFusionLinkSubmit.remove();
					jQuery( '#wp-link-cancel' ).unbind( 'click' );
					$linkDialog.close();
					window.wpLink.textarea = '';
				},

				$linkDialog.open( linkId );

				jQuery( '#link-options, #wplink-link-existing-content' ).hide();
				jQuery( '#wp-link-wrap' ).addClass( 'fusion-object-link-selector' );
				jQuery( '#wp-link-url' ).val( linkUrl );
				jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
				if ( jQuery( 'span[data-permalink="' + linkUrl + '"]' ).length ) {
					jQuery( 'span[data-permalink="' + linkUrl + '"]' ).closest( 'li' ).addClass( 'selected' );
				}

				jQuery( document ).on( 'click', '#fusion-link-submit', function( scopedEvent ) {
					$linkDialog.fusionUpdateLink( scopedEvent, jQuery( this ) );
				} );
			} );

			jQuery( document ).on( 'click', '#search-panel li', function() {
				jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
				jQuery( this ).addClass( 'selected' );
			} );

			jQuery( document ).on( 'click', '#wp-link-cancel, #wp-link-close, #wp-link-backdrop', function() {
				$linkSubmit.show();
				$linkTitle.show();
				$linkTarget.show();
				$fusionLinkSubmit.remove();
			} );
		} );
	}
};
