/* global noUiSlider, wNumb */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionColumnWidth = {

	optionColumnWidth: function( $element ) {
		var $columnWidth;
		$columnWidth		= $element.find( '.fusion-form-column-width' );

		$columnWidth.each( function() {
			// Init
			var $colEl 			= jQuery( this ),
				value 			= $colEl.find( '.width-value' ).val(),
				$sliderElement  = $colEl.find( '.custom-width-range-slider' ),
				$rangeSlider,
				sliderOptions,
				fractionToDecimal;

			fractionToDecimal = function( newValue ) {
				var fraction;

				if ( ! newValue.includes( '_' ) ) {
					return '';
				}

				fraction = newValue.split( '_' );
				if ( '' === newValue ) {
					return 0;
				}
				return ( parseFloat( fraction[ 0 ] ) / parseFloat( fraction[ 1 ] ) * 100 ).toFixed( 2 );
			};

			// Init range slider
			sliderOptions = {
				start: [ value ],
				step: 0.01,
				direction: $sliderElement.data( 'direction' ),
				range: {
					min: 0,
					max: 100
				},
				format: wNumb( {
					decimals: 2
				} )
			};
			$rangeSlider = noUiSlider.create( $sliderElement[ 0 ], sliderOptions );

			// Check if it's fraction else initialize custom width.
			if ( ! value || value.includes( '_' ) || 'auto' === value ) {
				$colEl.data( 'active', 'ui-buttons' );
				$colEl.find( '.ui-input, .width-custom' ).hide();
				$colEl.find( '.ui-button[data-value="' + value + '"]' ).addClass( 'ui-state-active' );
				// Update Slider values
				$rangeSlider.set( fractionToDecimal( value ) );
				$colEl.find( '.ui-input input' ).val( fractionToDecimal( value ) );
			} else {
				$colEl.data( 'active', 'ui-input' );
				$colEl.find( '.ui-buttons, .width-default' ).hide();
			}

			// Event listeners
			$rangeSlider.on( 'slide', function( values, handle ) {
				$colEl.find( '.ui-button' ).removeClass( 'ui-state-active' );
				$colEl.find( '.custom-width-input' ).val( values[ handle ] );
				$colEl.find( '.width-value' ).val( values[ handle ] ).trigger( 'change' );
			} );

			$colEl.on( 'click', '.column-width-toggle', function() {
				$colEl.find( '.ui-input, .ui-buttons, a .label' ).toggle();
			} );

			$colEl.on( 'click', '.ui-button', function( event ) {
				var $widthBtn 		= jQuery( this ),
					width			= $widthBtn.data( 'value' );

				if ( jQuery( this ).hasClass( 'default' ) && event ) {
					event.preventDefault();
				}

				// Update Slider values
				$rangeSlider.set( fractionToDecimal( width ) );
				$colEl.find( '.ui-input input' ).val( fractionToDecimal( width ) );

				$colEl.find( '.ui-button' ).removeClass( 'ui-state-active' );
				$widthBtn.addClass( 'ui-state-active' );
				$colEl.find( '.width-value' ).val( width ).trigger( 'change' );
			} );

			$colEl.on( 'change', '.ui-input input', function() {
				var $widthInput = jQuery( this ),
					width		= $widthInput.val();

				// Update Slider values
				$rangeSlider.set( width );

				$colEl.find( '.ui-button' ).removeClass( 'ui-state-active' );
				$colEl.find( '.width-value' ).val( width ).trigger( 'change' );
			} );
		} );
	}
};
