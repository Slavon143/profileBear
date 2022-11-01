/* globals awbPalette */
var FusionPageBuilder = FusionPageBuilder || {};

FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColorPalette = {

	optionColorPalette: function( $element ) {
		var $palettes;

		$element  = $element || this.$el;
		$palettes = $element.find( '.fusion-color-palette-options' );

		$palettes.each( function() {
			FusionPageBuilder.options.fusionColorPalette.initColorsPalette( jQuery( this ) );
		} );
	},

	initColorsPalette: function ( $paletteContainer ) {
		var paletteSaveInput;

		$paletteContainer = jQuery( $paletteContainer );
		paletteSaveInput = $paletteContainer.find( '.awb-palette-save' );

		if ( ! $paletteContainer.is( '.fusion-color-palette-options' ) ) {
			return;
		}

		if ( $paletteContainer.hasClass( 'palette-init' ) ) {
			return;
		}
		$paletteContainer.addClass( 'palette-init' );

		initializePickers();

		// Toggle open and close color.
		$paletteContainer.on( 'click', '.preview, .fusiona-pen', handleToggleColor );

		// Listen for removal of color.
		$paletteContainer.on( 'click', '.fusiona-trash-o', handleTrashIconClick );

		// Listen for the add color button.
		$paletteContainer.on( 'click', '.awb-color-palette-add-btn', handleAddColorBtnClick );

		// Bind input changes to toggle label.
		$paletteContainer.on( 'change keyup', '.color-name', handleColorNameChange );

		// Initialize all pickers.
		function initializePickers() {
			$paletteContainer.find( '.awb-picker' ).each( function() {
				initializePicker( this );
			} );
		}

		// Initialize a specific picker.
		function initializePicker( picker ) {
			var handleColorChange = _.debounce( _handleColorChange, 150 );
			jQuery( picker ).awbColorPicker( {
				change: handleColorChange
			} );
		}

		// Update the palette object when a color changes.
		function _handleColorChange( event, ui ) {
			var $target = jQuery( event.target ),
				value   = $target.val(),
				slug    = $target.closest( '.fusion-color-palette-item' ).attr( 'data-slug' );

			if ( 'object' === typeof ui ) {
				value = ui.color.toString();
			}

			addOrUpdateOptionColor( slug, { color: value } );
		}

		// Show or hide the controls to change a color.
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

		function handleColorNameChange() {
			var paletteItem = jQuery( this ).closest( '.fusion-color-palette-item' ),
				label    = jQuery( this ).val(),
				oldLabel,
				slug     = paletteItem.attr( 'data-slug' ),
				object;

			// Change the title to the new label.
			paletteItem.find( '.label' ).text( label );

			// The checks needed because change event can trigger without any value changes.
			// As it triggers both on "keyup" and "change".
			object = getPaletteSaveObject();
			oldLabel = object[ slug ] ? object[ slug ].label : null;
			if ( oldLabel !== label && object[ slug ] ) {
				addOrUpdateOptionColor( slug, { label: label } );
			}
		}

		// When a trash icon is clicked, remove the color from the palette.
		function handleTrashIconClick() {
			var paletteItem = jQuery( this ).closest( '.fusion-color-palette-item' ),
				slug = paletteItem.attr( 'data-slug' ),
				resultConfirm;

			resultConfirm = window.confirm( window.awbPalette.removeColorAlert ); // eslint-disable-line no-alert

			if ( ! resultConfirm ) {
				return;
			}

			paletteItem.find( '.awb-palette-content' ).slideUp( 'fast' );
			paletteItem.slideUp( 'fast', function() {
				jQuery( this ).remove();
			} );

			removeOptionColor( slug );
		}

		function handleAddColorBtnClick( event ) {
			var paletteList = $paletteContainer.find( '.awb-color-palette-list' ),
				newItem     = jQuery( $paletteContainer.find( '.awb-color-palette-color-template' ).html().trim().replaceAll( /(^<!--|-->$)/g, '' ) ),
				newSlug,
				newPaletteColorObj;

			event.preventDefault();

			paletteList.append( newItem );

			newSlug = generateSlug( newItem );
			changeSlugInHTML( newItem, newSlug );

			// Initialize global colors with the new color.
			newPaletteColorObj = {
				color: newItem.find( '.awb-picker' ).val(),
				label: newItem.find( '.color-name' ).val()
			};
			addOrUpdateOptionColor( newSlug, newPaletteColorObj );

			initializePicker( newItem.find( '.awb-picker' ) );
		}

		// Helper functions

		// Update the color object in both save input global palette.
		function addOrUpdateOptionColor( slug, colorObject ) {
			var object;
			awbPalette.addOrUpdateColor( slug, colorObject );

			object = getPaletteSaveObject();
			object[ slug ] = Object.assign( {}, object[ slug ], colorObject ); // eslint-disable-line
			replacePaletteSaveObject( object );
		}

		// Removes the color object in both save input global palette.
		function removeOptionColor( slug ) {
			var object;
			if ( ! slug ) {
				return;
			}

			awbPalette.removeColor( slug );

			object = getPaletteSaveObject();
			if ( object[ slug ] ) {
				delete object[ slug ];
				replacePaletteSaveObject( object );
			}
		}

		// Get the object of colors from the save input.
		function getPaletteSaveObject() {
			var objectString = paletteSaveInput.val(),
				object;

			try {
				object = JSON.parse( objectString );
				return object;
			} catch ( e ) {
				console.error( e );
				return {};
			}
		}

		// Replace the palette save object with a new one.
		function replacePaletteSaveObject( object ) {
			paletteSaveInput.val( JSON.stringify( object ) ).trigger( 'change' );
		}

		// From a name entered by a user, generate a new unique slug.
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

		// Change the slug of a color item, only in HTML.
		function changeSlugInHTML( paletteItem, newSlug ) {
			var oldSlug = paletteItem.attr( 'data-slug' );
			if ( ! oldSlug || newSlug === oldSlug ) {
				return;
			}

			changeOldAttributeSlug( paletteItem, 'data-slug' );
			changeOldAttributeSlug( paletteItem.find( '.awb-picker' ), 'id', 'id' );
			changeOldAttributeSlug( paletteItem.find( '.awb-picker' ), 'name' );
			changeOldAttributeSlug( paletteItem.find( '.color-name' ), 'id', 'id' );
			changeOldAttributeSlug( paletteItem.find( '.color-name' ), 'name' );
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

	}
};
