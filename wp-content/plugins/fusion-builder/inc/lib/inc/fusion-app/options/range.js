/* global noUiSlider, wNumb */
var FusionPageBuilder = FusionPageBuilder || {};
FusionPageBuilder.options = FusionPageBuilder.options || {};

FusionPageBuilder.options.fusionRangeField = {
	optionRange: function( $element ) {
		var self = this,
			$rangeSlider;

		$element     = 'undefined' !== typeof $element && $element.length ? $element : this.$el;
		$rangeSlider = $element.find( '.fusion-slider-container' );

		if ( ! $rangeSlider.length ) {
			return;
		}

		if ( 'object' !== typeof this.$rangeSlider ) {
			this.$rangeSlider = {};
		}

		// Method for retreiving decimal places from step
		Number.prototype.countDecimals = function() { // eslint-disable-line no-extend-native
			if ( Math.floor( this.valueOf() ) === this.valueOf() ) {
				return 0;
			}
			return this.toString().split( '.' )[ 1 ].length || 0;
		};

		// Each slider on page, determine settings and create slider
		$rangeSlider.each( function() {

			var $targetId     = jQuery( this ).data( 'id' ),
				$rangeInput   = jQuery( this ).prev( '.fusion-slider-input' ),
				$min          = jQuery( this ).data( 'min' ),
				$max          = jQuery( this ).data( 'max' ),
				$step         = jQuery( this ).data( 'step' ),
				$direction    = 'ltr',
				$value        = $rangeInput.val(),
				$decimals     = $step.countDecimals(),
				$rangeCheck   = 1 === jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-with-default' ).length,
				$rangeDefault = jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-panel-options .fusion-range-default' ).length ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-panel-options .fusion-range-default' ) : false,
				$hiddenValue  = ( $rangeCheck ) ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-hidden-value' ) : false,
				$defaultValue = ( $rangeCheck ) ? jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-range-default' ).attr( 'data-default' ) : jQuery( this ).data( 'value' );

			self.$rangeSlider[ $targetId ] = jQuery( this )[ 0 ];

			// Check if parent has another value set to override TO default.
			if ( 'undefined' !== typeof self.parentValues && 'undefined' !== typeof self.parentValues[ $targetId ] && $rangeDefault ) {

				//  Set default values to new value.
				jQuery( this ).closest( '.fusion-builder-option' ).find( '.fusion-range-default' ).attr( 'data-default', self.parentValues[ $targetId ] );
				$defaultValue = self.parentValues[ $targetId ];

				// If no current value is set, also update $value as representation on load.
				if ( ! $hiddenValue || '' === $hiddenValue.val() ) {
					$value = $defaultValue;
				}
			}

			self.createSlider( $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeCheck, $rangeDefault, $hiddenValue, $defaultValue, $direction );
		} );
	},

	createSlider: function( $targetId, $rangeInput, $min, $max, $step, $value, $decimals, $rangeCheck, $rangeDefault, $hiddenValue, $defaultValue, $direction ) {

		// Create slider with values passed on in data attributes.
		var self    = this,
			$slider = noUiSlider.create( self.$rangeSlider[ $targetId ], {
				start: [ $value ],
				step: $step,
				direction: $direction,
				range: {
					min: $min,
					max: $max
				},
				format: wNumb( {
					decimals: $decimals
				} ),
				default: $defaultValue
			} ),
			$notFirst = false;

		$rangeInput.closest( '.fusion-builder-option' ).attr( 'data-index', $targetId );

		// Check if default is currently set.
		if ( $rangeDefault && '' === $hiddenValue.val() ) {
			$rangeDefault.parent().addClass( 'checked' );
		}

		// If this range has a default option then if checked set slider value to data-value.
		if ( $rangeDefault ) {
			$rangeDefault.on( 'click', function( e ) {
				e.preventDefault();
				self.$rangeSlider[ $targetId ].noUiSlider.set( jQuery( this ).attr( 'data-default' ) );
				$hiddenValue.val( '' ).trigger( 'fusion-change' );
				jQuery( this ).parent().addClass( 'checked' );
			} );
		}

		// On slider move, update input. Also triggered on range init.
		$slider.on( 'update', function( values, handle ) {

			if ( $rangeCheck && $notFirst ) {
				if ( $rangeDefault ) {
					$rangeDefault.parent().removeClass( 'checked' );
				}
				$hiddenValue.val( values[ handle ] ).trigger( 'fusion-change' );
			}

			if ( $rangeDefault && $defaultValue == Object.values( values )[ 0 ] ) {
				$rangeDefault.parent().addClass( 'checked' );
			}

			// Not needed on init, value is already set in template.
			if ( true === $notFirst ) {
				jQuery( this.target ).closest( '.fusion-slider-container' ).prev().val( values[ handle ] ).trigger( 'change' );
			}

			$notFirst = true;
		} );

		// On manual input change, update slider position
		$rangeInput.on( 'blur', function() {

			if ( this.value !== self.$rangeSlider[ $targetId ].noUiSlider.get() ) {

				// This triggers 'update' event.
				self.$rangeSlider[ $targetId ].noUiSlider.set( this.value );
			}
		} );
	}
};
