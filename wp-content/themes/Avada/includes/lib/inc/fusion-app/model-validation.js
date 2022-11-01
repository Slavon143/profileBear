var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	FusionPageBuilder.Validate = Backbone.Model.extend( {

		/**
		 * Validates dimension css values.
		 *
		 * @param {string} value - The value we want to validate.
		 * @return {boolean}
		 */
		cssValue: function( value, allowNumeric ) {
			var validUnits    = [ 'rem', 'em', 'ex', '%', 'px', 'cm', 'mm', 'in', 'pt', 'pc', 'ch', 'vh', 'vw', 'vmin', 'vmax' ],
				partsValidity = true,
				self          = this,
				numericValue,
				unit,
				parts;

			// 0 is always a valid value, and we can't check calc() values effectively.
			if ( '0' === value || '' === value || ( 0 <= value.indexOf( 'calc(' ) && 0 <= value.indexOf( ')' ) ) ) {
				return true;
			}

			if ( 0 <= value.indexOf( ' ' ) ) {
				parts = value.split( ' ' );
				_.each( parts, function( part ) {
					if ( ! self.cssValue( part, false ) ) {
						partsValidity = false;
					}
				} );
				return partsValidity;
			}

			// Get the numeric value.
			numericValue = parseFloat( value );

			// Get the unit
			unit = value.replace( numericValue, '' );

			if ( true === allowNumeric && ( '' === unit || ! unit ) ) {
				return true;
			}

			// Check the validity of the numeric value and units.
			if ( isNaN( numericValue ) || 0 > _.indexOf( validUnits, unit ) ) {
				return false;
			}
			return true;
		},

		/**
		 * Color validation.
		 *
		 * @since 2.0.0
		 * @param {string} value - The color-value we're validating.
		 * @param {string} mode - The color-mode (rgba or hex).
		 * @return {boolean}
		 */
		validateColor: function( value, mode ) {
			if ( '' === value ) {
				return true;
			}

			// Invalid value if not a string.
			if ( ! _.isString( value ) ) {
				return false;
			}

			if ( 0 === value.indexOf( '--' ) || ( /var\(\s*--/i ).test( value ) ) {
				return true;
			}

			if ( 'hex' === mode ) {
				return this.colorHEX( value );
			} else if ( 'rgba' === mode ) {
				return this.colorRGBA( value );
			}

			// Validate RGBA.
			if ( -1 !== value.indexOf( 'rgba' ) ) {
				return this.colorRGBA( value );
			}

			// Validate HEX.
			return this.colorHEX( value );
		},

		/**
		 * Validates a hex color.
		 *
		 * @since 2.0.0
		 * @param {string} value - The value we're validating.
		 * @return {boolean}
		 */
		colorHEX: function( value ) {
			var hexValue;

			if ( '' === value ) {
				return true;
			}

			// If value does not include '#', then it's invalid hex.
			if ( -1 === value.indexOf( '#' ) ) {
				return false;
			}

			hexValue = value.replace( '#', '' );

			// Check if hexadecimal.
			return ( ! isNaN( parseInt( hexValue, 16 ) ) );
		},

		/**
		 * Validates an rgba color.
		 *
		 * @since 2.0.0
		 * @param {string} value - The value we're validating.
		 * @return {boolean}
		 */
		colorRGBA: function( value ) {
			var valid = true,
				parts;

			if ( '' === value ) {
				return true;
			}

			if ( -1 === value.indexOf( 'rgba(' ) || -1 === value.indexOf( ')' ) ) {
				return false;
			}

			parts = value.replace( 'rgba(', '' ).replace( ')', '' ).split( ',' );
			if ( 4 !== parts.length ) {
				return false;
			}

			_.each( parts, function( part ) {
				var num = parseFloat( part, 10 );
				if ( isNaN( num ) ) {
					valid = false;
					return false;
				}
				if ( 0 > num || 255 < num ) {
					valid = false;
					return false;
				}
			} );
			return valid;
		},

		/**
		 * Adds and removes messages in the control.
		 *
		 * @param {string} id - The setting ID.
		 * @param {string} message - The message to add.
		 * @return {void}
		 */
		message: function( action, id, input, message ) {
			var element = jQuery( '.fusion-builder-option[data-option-id="' + id + '"]' ),
				messageClass   = 'fusion-builder-validation',
				messageWrapper = '<div class="' + messageClass + ' error"></div>';

			// No reason to proceed if we can't find the element.
			if ( ! element.length ) {
				return;
			}

			if ( 'add' === action ) {

				// If the message wrapper doesn't exist, add it.
				if ( ! element.find( '.' + messageClass ).length ) {
					element.find( '.option-details' ).append( messageWrapper );
					jQuery( input ).addClass( 'error' );
				}

				// Add the message to the validation error wrapper.
				element.find( '.' + messageClass ).html( message );

			} else if ( 'remove' === action ) {
				element.find( '.' + messageClass ).remove();
				jQuery( input ).removeClass( 'error' );
			}
		}
	} );
}( jQuery ) );
