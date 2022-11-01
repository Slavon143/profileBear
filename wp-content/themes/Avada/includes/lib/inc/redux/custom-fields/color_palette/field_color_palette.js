/*global jQuery */

( function() {
	'use strict';

	fusionredux.field_objects               = fusionredux.field_objects || {};
	fusionredux.field_objects.color_palette = fusionredux.field_objects.color_palette || {};

	fusionredux.field_objects.color_palette.init = function( selector ) {

		if ( ! selector ) {
			selector = jQuery( document ).find( '.fusionredux-group-tab:visible' ).find( '.fusionredux-container-color_palette:visible' );
		}

		jQuery( selector ).each( function() {
			var $paletteContainer = jQuery( this );

			if ( $paletteContainer.hasClass( 'palette-init' ) ) {
				return;
			}
			$paletteContainer.addClass( 'palette-init' );

			// Toggle open and close color.
			$paletteContainer.on( 'click', '.preview, .fusiona-pen', handleToggleColor );

			// Listen for removal of color.
			$paletteContainer.on( 'click', '.fusiona-trash-o', handleTrashIconClick );

			// Listen for the add color button.
			$paletteContainer.on( 'click', '.awb-color-palette-add-btn', handleAddColorBtnClick );

			// Bind input changes to toggle label.
			$paletteContainer.on( 'change keyup', '.color-name', handleColorNameChange );

			// Show or hide the color editing options.
			function handleToggleColor() {
				var picker,
					pickerInstance;

				jQuery( this ).closest( '.fusion-color-palette-item' ).find( '.awb-palette-content' ).slideToggle( 'fast' );

				// Annoying Iris visual bug, make sure that the initial draggable button is within the parent.
				picker = jQuery( this ).closest( '.fusion-color-palette-item' ).find( 'input.awb-color-picker' );
				pickerInstance = picker.awbColorPicker( 'instance' );
				if ( 'object' === typeof pickerInstance && 'function' === typeof pickerInstance.fixIrisDragButtonOutsideDragArea ) {
					pickerInstance.fixIrisDragButtonOutsideDragArea();
				}
			}

			// When an trash icon is clicked, remove the color from the palette.
			function handleTrashIconClick() {
				var paletteItem = jQuery( this ).closest( '.fusion-color-palette-item' ),
					resultConfirm;

				resultConfirm = window.confirm( window.awbPalette.removeColorAlert ); // eslint-disable-line no-alert

				if ( ! resultConfirm ) {
					return;
				}

				paletteItem.find( '.awb-palette-content' ).slideUp( 'fast' );
				paletteItem.slideUp( 'fast', function() {
					jQuery( this ).remove();
				} );

				awbPalette.removeColor( paletteItem.attr( 'data-slug' ) );
			}

			// Add a new color item when a button is clicked.
			function handleAddColorBtnClick( event ) {
				var paletteList = $paletteContainer.find( '.awb-color-palette-list' ),
					newItem     = jQuery( $paletteContainer.find( '.awb-color-palette-color-template script' ).html() ),
					newSlug,
					newPaletteColorObj;

				event.preventDefault();

				paletteList.append( newItem );

				newSlug = generateSlug( newItem );
				changeSlug( newItem, newSlug );

				// Initialize global colors with the new color.
				newPaletteColorObj = {
					color: newItem.find( '.awb-picker' ).val(),
					label: newItem.find( '.color-name' ).val(),
				};

				awbPalette.addOrUpdateColor( newSlug, newPaletteColorObj );

				newItem.find( '.awb-picker' ).awbColorPicker();
			}

			// Bind the color-name field, with the color item title.
			function handleColorNameChange() {
				var paletteItem = jQuery( this ).closest( '.fusion-color-palette-item' ),
					label    = jQuery( this ).val(),
					slug     = paletteItem.attr( 'data-slug' );

				paletteItem.find( '.label' ).text( label );
				awbPalette.addOrUpdateColor( slug, { label: label } );
			}

			// Change the slug of an item.
			function changeSlug( paletteItem, newSlug ) {
				var oldSlug = paletteItem.attr( 'data-slug' );
				if ( ! oldSlug || newSlug === oldSlug) {
					return;
				}

				changeOldAttributeSlug( paletteItem, 'data-slug' );
				changeOldAttributeSlug( paletteItem.find( '.awb-picker' ), 'id', 'id' );
				changeOldAttributeSlug( paletteItem.find( '.awb-picker' ), 'name', 'array' );
				changeOldAttributeSlug( paletteItem.find( '.color-name' ), 'id', 'id' );
				changeOldAttributeSlug( paletteItem.find( '.color-name' ), 'name', 'array' );
				changeOldAttributeSlug( paletteItem.find( '.color-name-label' ), 'for', 'id' );
				changeOldAttributeSlug( paletteItem.find( '.color-code-label' ), 'for', 'id' );

				function changeOldAttributeSlug( input, attribute, replaceType = '' ) {
					var oldAttr = input.attr( attribute ),
						newAttr;

					if ( ! oldAttr ) {
						return;
					}

					if ( 'array' === replaceType ) {
						newAttr = oldAttr.replaceAll( '[' + oldSlug + ']', '[' + newSlug + ']' );
					} else if ( 'id' === replaceType ) {
						newAttr = oldAttr.replaceAll( '-' + oldSlug, '-' + newSlug );
					} else {
						newAttr = oldAttr.replaceAll( oldSlug, newSlug );
					}

					input.attr( attribute, newAttr );
				}
			}

			function generateSlug( paletteItem ) {
				var paletteSlugs = [],
				    number,
					slugWithoutAppendedNumber,
					slug = 'custom_color_1';

				// Make an array with existing slugs.
				$paletteContainer.find( '.fusion-color-palette-item' ).not( paletteItem ).each( function() {
					var itemSlug = jQuery( this ).attr( 'data-slug' );
					if ( itemSlug ) {
						paletteSlugs.push( itemSlug );
					}
				} );

				// Append a number to the end of the slug, if the slug already exists.
				if ( paletteSlugs.includes( slug ) ) {
					number = 2;
					slugWithoutAppendedNumber = slug.replace( /_(\d+)$/, '' );

					while ( paletteSlugs.includes( slugWithoutAppendedNumber + '_' + number ) ) {
						number++;
					}

					slug = slugWithoutAppendedNumber + '_' + number;
				}

				return slug;
			}

		} );
	};

} ( jQuery ) );
