( function( jQuery ) {

	'use strict';

	jQuery.fn.awbSelect = function( options ) {
		var checkBoxes         = '',
			$selectField       = jQuery( this ),
			$selectValue       = $selectField.find( '.fusion-select-option-value' ),
			$selectDropdown    = $selectField.find( '.fusion-select-dropdown' ),
			$selectPreview     = $selectField.find( '.fusion-select-preview-wrap' ),
			$selectSearchInput = $selectField.find( '.fusion-select-search input' ),
			fuseOptions        = {
				threshold: 0.2,
				ignoreLocation: true,
				maxPatternLength: 32,
				minMatchCharLength: 0,
				findAllMatches: true,
				keys: [ 'text' ]
			},
			standardFonts = 'object' === typeof options.data[ 0 ] ?  options.data[ 0 ].children : {},
			googleFonts   = 'object' === typeof options.data[ 1 ] ?  options.data[ 1 ].children : {},
			customFonts   = 'object' === typeof options.data[ 2 ] ?  options.data[ 2 ].children : {},
			fonts         = standardFonts.concat( googleFonts ).concat( customFonts ),
			fuse;

		if ( $selectField.hasClass( 'fusion-select-inited' ) ) {
			return $selectField;
		}
		
		fuse = new Fuse( fonts, fuseOptions );
		$selectField.addClass( 'fusion-select-inited' );

		if ( $selectField.closest( '.fusion-builder-option' ).hasClass( 'typography' ) ) {
			checkBoxes += '<label class="fusion-select-label' + ( '' === $selectValue.val() ? ' fusion-option-selected' : '' ) + '" data-value="" data-id="">' + fusionBuilderText.typography_default + '</label>';
		}
		_.each( options.data, function( subset ) {
			checkBoxes += 'string' === typeof subset.text && 'font-family' === options.fieldName ? '<div class="fusion-select-optiongroup">' + subset.text + '</div>' : '';
			_.each( subset.children, function( name ) {
				var checked = name.id === $selectValue.val() ? ' fusion-option-selected' : '',
					id      = 'string' === typeof name.id ? name.id.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() : '';

				checkBoxes += '<label class="fusion-select-label' + checked + '" data-value="' + name.id + '" data-id="' + id + '">' + name.text + '</label>';
			} );
		} );
		$selectField.find( '.fusion-select-options' ).html( checkBoxes );

		// Open select dropdown.
		$selectPreview.on( 'click', function( event ) {
			var open = $selectField.hasClass( 'fusion-open' );

			event.preventDefault();

			if ( ! open ) {
				$selectField.addClass( 'fusion-open' );
				if ( $selectSearchInput.length ) {
					$selectSearchInput.focus();
				}
			} else {
				$selectField.removeClass( 'fusion-open' );
				if ( $selectSearchInput.length ) {
					$selectSearchInput.val( '' ).blur();
				}
				$selectField.find( '.fusion-select-label' ).css( 'display', 'block' );
			}
		} );

		// Option is selected.
		$selectField.on( 'click', '.fusion-select-label', function() {
			$selectPreview.find( '.fusion-select-preview' ).html( jQuery( this ).html() );
			$selectPreview.trigger( 'click' );

			$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
			jQuery( this ).addClass( 'fusion-option-selected' );

			$selectField.find( '.fusion-select-option-value' ).val( jQuery( this ).data( 'value' ) ).trigger( 'change', [ { userClicked: true } ] );
		} );

		$selectField.find( '.fusion-select-option-value' ).on( 'change', function( event, data ) {
			if ( 'undefined' !== typeof data && 'undefined' !== typeof data.userClicked && true !== data.userClicked ) {
				return;
			}

			// Option changed progamatically, we need to update preview.
			$selectPreview.find( '.fusion-select-preview' ).html( $selectField.find( '.fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).html() );
			$selectDropdown.find( '.fusion-select-label' ).removeClass( 'fusion-option-selected' );
			$selectDropdown.find( '.fusion-select-label[data-value="' + jQuery( this ).val() + '"]' ).addClass( 'fusion-option-selected' );
		} );

		// Search field.
		if ( 'font-family' === options.fieldName ) {
			$selectSearchInput.on( 'keyup change paste', function( event ) {
				var value         = jQuery( this ).val(),
					result;

				if ( 3 > value.length ) {
					$selectField.find( '.fusion-select-label' ).css( 'display', 'block' );
					return;
				}

				// Select option on "Enter" press if only 1 option is visible.
				if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $selectField.find( '.fusion-select-label:visible' ).length ) {
					$selectField.find( '.fusion-select-label:visible' ).trigger( 'click' );
					return;
				}

				$selectField.find( '.fusion-select-label' ).css( 'display', 'none' );

				result = fuse.search( value );

				_.each( result, function( resultFont ) {
					$selectField.find( '.fusion-select-label[data-id="' + resultFont.id.replace( /"/g, '' ).replace( /'/g, '' ).toLowerCase() + '"]' ).css( 'display', 'block' );
				} );
			} );
		} else {
			$selectSearchInput.on( 'keyup change paste', function() {
				var val          = jQuery( this ).val(),
					optionInputs = $selectField.find( '.fusion-select-label' );

				// Select option on "Enter" press if only 1 option is visible.
				if ( 'keyup' === event.type && 13 === event.keyCode && 1 === $selectField.find( '.fusion-select-label:visible' ).length ) {
					$selectField.find( '.fusion-select-label:visible' ).trigger( 'click' );
					return;
				}

				_.each( optionInputs, function( optionInput ) {
					if ( -1 === jQuery( optionInput ).html().toLowerCase().indexOf( val.toLowerCase() ) ) {
						jQuery( optionInput ).css( 'display', 'none' );
					} else {
						jQuery( optionInput ).css( 'display', 'block' );
					}
				} );
			} );
		}

		return $selectField;
	};

}( jQuery ) );
