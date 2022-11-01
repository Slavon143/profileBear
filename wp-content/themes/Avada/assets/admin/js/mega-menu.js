/* global ajaxurl, AvadaMegaMenuVars */
/**
 * Handles the admin manipulation of the mega menu plugin.
 *
 * @author     ThemeFusion
 * @copyright  (c) Copyright by ThemeFusion
 * @link       https://theme-fusion.com
 * @package    Avada
 * @subpackage Core
 * @since      2.0.0
 */

( function() {

	'use strict';

	var fusionMegamenu;

	jQuery( document ).ready( function() {

		// Show or hide megamenu fields on parent and child list items.
		fusionMegamenu.menuItemMouseup();
		fusionMegamenu.megamenuStatusUpdate();
		fusionMegamenu.updateMegamenuFields();
		fusionMegamenu.megamenuFullwidthUpdate();

		fusionMegamenu.specialLinksStatusUpdate();
		fusionMegamenu.wooCartCounterUpdate();

		// Setup automatic thumbnail handling.
		jQuery( '#post-body' ).on( 'click', '.avada-remove-button', function() {
			jQuery( this ).parents( '.fusion-upload-image' ).removeClass( 'fusion-image-set' );
			jQuery( this ).parents( '.fusion-upload-image' ).find( 'img' ).attr( 'src', '' );
			jQuery( this ).parents( '.fusion-upload-image' ).find( '.fusion-builder-upload-field' ).val( '' );
		} );

		jQuery( '.fusion-megamenu-thumbnail-image' ).css( 'display', 'block' );
		jQuery( '.fusion-megamenu-thumbnail-image[src=""]' ).css( 'display', 'none' );

		// Setup new media uploader frame.
		fusionMediaFrameSetup();
	} );

	// "Extending" wpNavMenu.
	fusionMegamenu = {

		menuItemMouseup: function() {
			jQuery( document ).on( 'mouseup', '.menu-item-bar', function( event ) {
				if ( ! jQuery( event.target ).is( 'a' ) ) {
					setTimeout( fusionMegamenu.updateMegamenuFields, 300 );
				}
			} );
		},

		megamenuStatusUpdate: function() {

			jQuery( document ).on( 'click', '.edit-menu-item-megamenu-status a', function() {
				var parentLiItem = jQuery( this ).parents( '.menu-item:eq( 0 )' );

				if ( 'enabled' === jQuery( this ).parent().find( '.button-set-value' ).val() ) {
					parentLiItem.addClass( 'fusion-megamenu' );
				} else {
					parentLiItem.removeClass( 'fusion-megamenu' );
				}

				fusionMegamenu.updateMegamenuFields();
			} );
		},

		wooCartCounterUpdate: function() {

			jQuery( document ).on( 'change', '.edit-menu-item-megamenu-show-woo-cart-counter .button-set-value', function() {
				var parentLiItem = jQuery( this ).closest( '.menu-item' );

				if ( 'yes' === jQuery( this ).val() ) {
					parentLiItem.addClass( 'fusion-woo-cart-counter-enabled' );
				} else {
					parentLiItem.removeClass( 'fusion-woo-cart-counter-enabled' );
				}
			} );
		},

		specialLinksStatusUpdate: function() {

			jQuery( document ).on( 'change', '.fusion-megamenu-special-link', function() {
				var parentLiItem = jQuery( this ).parents( '.menu-item:eq( 0 )' ),
					value = jQuery( this ).val();

				parentLiItem
					.removeClass( 'fusion-special-link-none' )
					.removeClass( 'fusion-special-link-woo-cart' )
					.removeClass( 'fusion-special-link-woo-account' )
					.removeClass( 'fusion-special-link-sliding-bar-toggle' )
					.removeClass( 'fusion-special-link-search' );

				switch ( value ) {
					case 'fusion-woo-cart':
						parentLiItem.addClass( 'fusion-special-link-woo-cart' );
						break;

					case 'fusion-woo-my-account':
						parentLiItem.addClass( 'fusion-special-link-woo-account' );
						break;

					case 'fusion-search':
						parentLiItem.addClass( 'fusion-special-link-search' );
						parentLiItem.addClass( 'fusion-special-link-search-' + parentLiItem.find( '.edit-menu-item-megamenu-searchform-mode input' ).val() );
						break;

					case 'fusion-sliding-bar-toggle':
						parentLiItem.addClass( 'fusion-special-link-sliding-bar-toggle' );
						break;

					default:
						parentLiItem.addClass( 'fusion-special-link-none' );
						break;

				}
			} );

			jQuery( document ).on( 'click', '.edit-menu-item-megamenu-searchform-mode a', function() {
				var parentLiItem = jQuery( this ).parents( '.menu-item:eq( 0 )' );

				parentLiItem
					.removeClass( 'fusion-special-link-search-inline' )
					.removeClass( 'fusion-special-link-search-dropdown' )
					.removeClass( 'fusion-special-link-search-overlay' )
					.addClass( 'fusion-special-link-search-' + jQuery( this ).parent().find( 'input' ).val() );
			} );
		},

		megamenuFullwidthUpdate: function() {
			jQuery( document ).on( 'click', '.edit-menu-item-megamenu-width a', function() {
				var parentLiItem = jQuery( this ).parents( '.menu-item:eq( 0 )' );

				if ( 'fullwidth' === jQuery( this ).parent().find( '.button-set-value' ).val() ) {
					parentLiItem.addClass( 'fusion-megamenu-fullwidth' );
				} else {
					parentLiItem.removeClass( 'fusion-megamenu-fullwidth' );
				}

				fusionMegamenu.updateMegamenuFields();
			} );
		},

		updateMegamenuFields: function() {
			var parentLiItem = jQuery( '.menu-item' );

			parentLiItem.each( function( i ) {

				var megamenuStatus = jQuery( '.edit-menu-item-megamenu-status .button-set-value', this ),
					megamenuFullwidth = jQuery( '.edit-menu-item-megamenu-width .button-set-value', this ),
					checkAgainst;

				if ( ! jQuery( this ).is( '.menu-item-depth-0' ) ) {
					checkAgainst = parentLiItem.filter( ':eq(' + ( i - 1 ) + ')' );

					if ( checkAgainst.is( '.fusion-megamenu' ) ) {
						megamenuStatus.val( 'enabled' );
						jQuery( this ).addClass( 'fusion-megamenu' );
					} else {
						megamenuStatus.val( 'off' );
						jQuery( this ).removeClass( 'fusion-megamenu' );
					}

					if ( checkAgainst.is( '.fusion-megamenu-fullwidth' ) ) {
						megamenuFullwidth.val( 'fullwidth' );
						jQuery( this ).addClass( 'fusion-megamenu-fullwidth' );
					} else {
						megamenuFullwidth.val( 'off' );
						jQuery( this ).removeClass( 'fusion-megamenu-fullwidth' );
					}
				} else {
					if ( 'enabled' === megamenuStatus.val() ) {
						jQuery( this ).addClass( 'fusion-megamenu' );
					}

					if ( 'fullwidth' === megamenuFullwidth.val() ) {
						jQuery( this ).addClass( 'fusion-megamenu-fullwidth' );
					}
				}
			} );
		}

	};

	function fusionMediaFrameSetup() {
		var fusionMediaFrame,
			itemId;

		jQuery( document.body ).on( 'click.fusionOpenMediaManager', '.button-upload', function( e ) {

			e.preventDefault();

			itemId = jQuery( this ).data( 'id' );

			if ( fusionMediaFrame ) {
				fusionMediaFrame.open();
				return;
			}

			fusionMediaFrame = wp.media( {
				className: 'media-frame fusion-media-frame',
				frame: 'select',
				multiple: false,
				library: {
					type: 'image'
				}
			} );

			wp.media.frames.fusionMediaFrame = fusionMediaFrame;

			fusionMediaFrame.on( 'select', function() {

				var mediaAttachment = fusionMediaFrame.state().get( 'selection' ).first().toJSON();

				jQuery( '#edit-menu-item-megamenu-' + itemId ).val( mediaAttachment.url );
				jQuery( '#edit-menu-item-megamenu-' + itemId.replace( '-', '-id-' ) ).val( mediaAttachment.id );
				jQuery( '#fusion-media-img-' + itemId ).attr( 'src', mediaAttachment.url ).css( 'display', 'block' );
				jQuery( '#fusion-media-img-' + itemId ).parents( '.fusion-upload-image' ).addClass( 'fusion-image-set' );

			} );

			fusionMediaFrame.open();
		} );
	}

	/*
	*	Autocomplete
	*/
	jQuery( window ).click( function( event ) { // eslint-disable-line no-unused-vars
		jQuery( '.autocomplete-ajax-results' ).removeClass( 'show' );
	} );
	jQuery( document ).on( 'click', '.fusion-autocomplete-wrap', function( event ) {
		event.stopPropagation();
	} );
	jQuery( document ).on( 'change', '.autocomplete-saved-value', function( event ) { // eslint-disable-line no-unused-vars
		const	val = this.value,
				search = jQuery( this ).parent().find( '.fusion-autocomplete-search' );

		if ( val ) {
			search.addClass( 'hidden' );
		} else {
			search.removeClass( 'hidden' );
		}
	} );
	jQuery( document ).on( 'click', '.autocomplete-container .selected-holder .remove', function( event ) { // eslint-disable-line no-unused-vars
		const	input = jQuery( this ).parents( '.autocomplete-container' ).find( '.autocomplete-saved-value' ),
				selected = jQuery( this ).parent();

		selected.remove();
		input.val( '' ).trigger( 'change' );
	} );

	jQuery( document ).on( 'keyup focus', '.fusion-autocomplete-search', function( event ) { // eslint-disable-line no-unused-vars
		const 	input = jQuery( this ),
				val = input.val(),
				wrap = input.parent(),
				ajaxResults = wrap.find( '.autocomplete-ajax-results' ),
				valueInput = wrap.find( '.autocomplete-saved-value' ),
				selected = wrap.find( '.selected-holder' ),
				postType = input.data( 'post-type' );


		if ( val && 2 < val.length ) {
			jQuery.ajax( {
				type: 'post',
				url: ajaxurl,
				dataType: 'json',
				data: {
					action: 'avada_mega_menu_autocomplete',
					nonce: AvadaMegaMenuVars.nonce,
					keyword: val,
					post_type: postType
				},
				beforeSend: function () {
					ajaxResults.addClass( 'show' ).html( '<a>' + AvadaMegaMenuVars.text_loading + '</a>' );
				},
				success: function ( response ) {
					ajaxResults.html( response.html );
				}
			} );
		} else {
			ajaxResults.removeClass( 'show' );
		}

		wrap.on( 'click', '.autocomplete-ajax-results a', function( e ) {
			e.preventDefault();
			valueInput.val( this.dataset.id ).trigger( 'change' );
			selected.html( '<div class="item">' + this.innerText + ' <span class="remove fas fa-times"></span></div>' );
			input.val( '' );
		} );

	} );

}( jQuery ) );
