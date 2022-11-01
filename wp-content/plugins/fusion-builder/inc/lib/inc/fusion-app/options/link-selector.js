var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionLinkSelector = {
	optionLinkSelector: function( $element ) {
		var $linkSelector;
		$element      = $element || this.$el;
		$linkSelector = $element.find( '.fusion-link-selector' );

		if ( $linkSelector.length ) {

			$linkSelector.each( function() {
				var $linkButton       = jQuery( this ).find( '.fusion-builder-link-button' ),
					$linkSubmit       = jQuery( '#wp-link-submit' ),
					$linkTitle        = jQuery( '.wp-link-text-field' ),
					$linkTarget       = jQuery( '.link-target' ),
					$fusionLinkSubmit = jQuery( '<input type="button" name="fusion-link-submit" id="fusion-link-submit" class="button-primary" value="Set Link">' ),
					wpLinkL10n        = window.wpLinkL10n,
					$inputField       = jQuery( this ).find( '.fusion-builder-link-field' ),
					linkId            = $inputField.attr( 'id' ),
					$input,
					$linkDialog,
					linkUrl,
					$option;

				jQuery( $linkButton ).on( 'click', function( event ) {
					if ( 'fusion-link-submit' !== $linkSubmit.prev().attr( 'id' ) ) {
						$fusionLinkSubmit.insertBefore( $linkSubmit );
					}
					$option = jQuery( event.target ).closest( ' .fusion-link-selector' );
					$input  = $option.find( '.fusion-builder-link-field' );
					linkUrl = $input.val();

					$linkSubmit.hide();
					$linkTitle.hide();
					$linkTarget.hide();
					$fusionLinkSubmit.show();

					if ( 'fusion-anchor-href' === linkId ) {
						jQuery( 'body' ).append( $inputField.clone( true ).css( { display: 'none' } ) );
					}

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

						// Update single input.
						$input.val( linkUrl ).trigger( 'change' );

						// Listener in vanilla JS so need different event.
						if ( -1 !== linkId.indexOf( 'fusion-anchor-href' ) && $input.length ) {
							$input[ 0 ].dispatchEvent( new Event( 'change' ) );
						}

						$linkSubmit.show();
						$linkTitle.show();
						$linkTarget.show();
						$scopedFusionLinkSubmit.remove();
						jQuery( '#wp-link-cancel' ).unbind( 'click' );
						$linkDialog.close();
						window.wpLink.textarea = '';
					},

					$linkDialog.open( linkId );

					// jQuery( '#link-options, #wplink-link-existing-content' ).hide();
					jQuery( '#wp-link-wrap' ).addClass( 'fusion-object-link-selector' );
					jQuery( '#wp-link-url' ).val( linkUrl );
					jQuery( '#search-panel li.selected' ).removeClass( 'selected' );
					if ( jQuery( 'span[data-permalink="' + linkUrl + '"]' ).length ) {
						jQuery( 'span[data-permalink="' + linkUrl + '"]' ).closest( 'li' ).addClass( 'selected' );
					}

					jQuery( document ).on( 'click', '#fusion-link-submit', function( scopedEvent ) {
						$linkDialog.fusionUpdateLink( scopedEvent, jQuery( this ) );
						if ( -1 !== linkId.indexOf( 'fusion-anchor-href' ) && jQuery( '#' + linkId ).length ) {
							jQuery( '#' + linkId ).remove();
						}
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

					if ( -1 !== linkId.indexOf( 'fusion-anchor-href' ) && jQuery( '#' + linkId ).length ) {
						jQuery( '#' + linkId ).remove();
					}
				} );
			} );

		}
	}
};
