/* global FusionApp, fusionOptionNetworkNames */
/* jshint -W098, -W116 */
/* eslint no-unused-vars: 0 */

var fusionTriggerResize = _.debounce( fusionResize, 300 ),
	fusionTriggerScroll = _.debounce( fusionScroll, 300 ),
	fusionTriggerLoad   = _.debounce( fusionLoad, 300 );

var fusionSanitize = {

	/**
	 * Gets the fusionApp settings.
	 *
	 * @since 2.0
	 * @return {Object} - Returns the options object.
	 */
	getSettings: function() {
		var settings = {};
		if ( 'undefined' !== typeof FusionApp ) {
			if ( 'undefined' !== typeof FusionApp.settings ) {
				settings = jQuery.extend( settings, FusionApp.settings );
			}
			if ( 'undefined' !== typeof FusionApp.data && 'undefined' !== typeof FusionApp.data.postMeta ) {
				settings = jQuery.extend( settings, FusionApp.data.postMeta );
			}
		}
		_.each( settings, function( value, key ) {
			if ( 'object' === typeof value ) {
				_.each( value, function( subVal, subKey ) {
					settings[ key + '[' + subKey + ']' ] = subVal;
				} );
			}
		} );
		return settings;
	},

	/**
	 * Get Global Option or page option.
	 * This is a port of the fusion_get_option() PHP function.
	 * We're skipping the 3rd param of the PHP function (post_ID)
	 * because in JS we're only dealing with the current post.
	 *
	 * @param {string} themeOption - Global Option ID.
	 * @param {string} pageOption - Page option ID.
	 * @param {number} postID - Post/Page ID.
	 * @return {string} - Global Option or page option value.
	 */
	getOption: function( themeOption, pageOption ) {
		var self     = this,
			themeVal = '',
			pageVal  = '';

		// Get the theme value.
		if ( 'undefined' !== typeof this.getSettings()[ themeOption ] ) {
			themeVal = self.getSettings()[ themeOption ];
		} else {
			_.each( fusionOptionNetworkNames, function( val, key ) {
				if ( themeOption === key && val.theme ) {
					themeVal = self.getSettings()[ val.theme ];
				}
			} );
		}

		// Get the page value.
		pageOption = pageOption || themeOption;
		pageVal    = this.getPageOption( pageOption );
		_.each( fusionOptionNetworkNames, function( val, key ) {
			if ( themeOption === key ) {

				if ( val.post ) {
					pageVal = self.getPageOption( val.post );
				}

				if ( ! pageVal && val.term ) {
					pageVal = self.getPageOption( val.term );
				}

				if ( ! pageVal && val.archive ) {
					pageVal = self.getPageOption( val.archive );
				}
			}
		} );

		if ( themeOption && pageOption && 'default' !== pageVal && ! _.isEmpty( pageVal ) ) {
			return pageVal;
		}
		return -1 === themeVal.indexOf( '/' ) ? themeVal.toLowerCase() : themeVal;
	},

	/**
	 * Get page option value.
	 * This is a port of the fusion_get_page_option() PHP function.
	 * We're skipping the 3rd param of the PHP function (post_ID)
	 * because in JS we're only dealing with the current post.
	 *
	 * @param {string} option - ID of page option.
	 * @return {string} - Value of page option.
	 */
	getPageOption: function( option ) {
		if ( option ) {
			if ( ! _.isUndefined( FusionApp ) && ! _.isUndefined( FusionApp.data.postMeta ) && ! _.isUndefined( FusionApp.data.postMeta._fusion ) && ! _.isUndefined( FusionApp.data.postMeta._fusion[ option ] ) ) {
				return FusionApp.data.postMeta._fusion[ option ];
			}
			if ( ! _.isUndefined( FusionApp ) && ! _.isUndefined( FusionApp.data.postMeta ) && ! _.isUndefined( FusionApp.data.postMeta[ option ] ) ) {
				return FusionApp.data.postMeta[ option ];
			}
		}
		return '';
	},

	/**
	 * Sanitize values like for example 10px, 30% etc.
	 *
	 * @param {String}	value -The value to sanitize.
	 * @param {Boolean|String} fallback_unit - A fallback unit to use in case no unit is found.
	 * @return {String}
	 */
	size: function ( value, fallback_unit ) {
		var numbersRegex = new RegExp( /[0-9]/ ),
			unit;

		// Trim the value.
		value = value.trim();

		if ( [ 'auto', 'inherit', 'initial' ].includes( value ) ) {
			return value;
		}

		// Return empty if there are no numbers in the value.
		// Prevents some CSS errors.
		if ( ! numbersRegex.test( value ) ) {
			return '';
		}

		if ( false !== value.includes( 'calc' ) ) {
			return value;
		}

		unit = fusionSanitize.get_unit( value );
		if ( fallback_unit && '' === unit ) {
			unit = ( true === fallback_unit ) ? 'px' : fallback_unit;
		}
		return fusionSanitize.number( value ) + unit;

	},

	/**
	 * Return the unit of a given value.
	 *
	 * @since 1.0.0
	 *
	 * @param  {string} - value A value with unit.
	 * @return {string} - The unit of the given value.
	 */
	get_unit: function( value ) {
		var unit_used,
			units = [ 'px', 'rem', 'em', '%', 'vmin', 'vmax', 'vh', 'vw', 'ex', 'cm', 'mm', 'in', 'pt', 'pc', 'ch' ];

		// Trim the value.
		value = value.trim();

		return _.find( units, function( unit ) {
			return value.includes( unit );
		} );
	},

	/**
	 * Sets the alpha channel of a color,
	 *
	 * @since 2.0.0
	 * @param {string}           value - The color we'll be adjusting.
	 * @param {string|number} adjustment - The alpha value.
	 * @return {string} - RBGA color, ready to be used in CSS.
	 */
	color_alpha_set: function( value, adjustment ) {
		var color  = jQuery.AWB_Color( value ),
			adjust = Math.abs( adjustment );

		if ( 1 < adjust ) {
			adjust = adjust / 100;
		}
		return color.alpha( adjust ).toVarOrRgbaString();
	},

	/**
	 * Returns the value if the conditions are met
	 * If they are not, then returns empty string.
	 *
	 * @since 2.0.0
	 * @param {mixed} value - The value.
	 * @param {Array} args - The arguments
	 *                       {
	 *                           conditions: [
	 *                               {option1, '===', value1},
	 *                               {option2, '!==', value2},
	 *                           ],
	 *                           value_pattern: [value, fallback]
	 *                       }
	 * @return {string} The condition check result.
	 */
	conditional_return_value: function( value, args ) {
		var self       = this,
			checks     = [],
			subChecks  = [],
			finalCheck = true,
			fallback   = '',
			success    = '$';

		if ( args.value_pattern ) {
			success  = args.value_pattern[ 0 ];
			fallback = args.value_pattern[ 1 ];
		}

		_.each( args.conditions, function( arg, i ) {
			var settingVal = '';
			if ( 'undefined' !== typeof arg[ 0 ] ) {
				settingVal = self.getSettings()[ arg[ 0 ] ];
				if ( 'undefined' === typeof settingVal || undefined === settingVal ) {
					settingVal = '';
					if ( -1 !== arg[ 0 ].indexOf( '[' ) ) {
						settingVal = self.getSettings()[ arg[ 0 ].split( '[' )[ 0 ] ];
						if ( arg[ 0 ].split( '[' )[ 1 ] && 'undefined' !== typeof settingVal[ arg[ 0 ].split( '[' )[ 1 ].replace( ']', '' ) ] ) {
							settingVal = settingVal[ arg[ 0 ].split( '[' )[ 1 ].replace( ']', '' ) ];
						}
					}
				}

				if ( window.awbTypographySelect && window.awbTypographySelect.isTypographyCssVar( settingVal ) ) {
					settingVal = window.awbTypographySelect.getRealValue( settingVal );
					if ( ! settingVal ) {
						settingVal = '';
					}
				}

				switch ( arg[ 1 ] ) {
				case '===':
					checks[ i ] = ( settingVal === arg[ 2 ] );
					break;
				case '>':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) > parseFloat( arg[ 2 ] ) );
					break;
				case '>=':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) >= parseFloat( arg[ 2 ] ) );
					break;
				case '<':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) < parseFloat( arg[ 2 ] ) );
					break;
				case '<=':
					checks[ i ] = ( parseFloat( self.units_to_px( settingVal ) ) <= parseFloat( arg[ 2 ] ) );
					break;
				case '!==':
					checks[ i ] = ( settingVal !== arg[ 2 ] );
					break;
				case 'in':
					subChecks = [];
					_.each( arg[ 2 ], function( subArg, k ) {
						subChecks[ k ] = ( settingVal !== subArg );
					} );
					checks[ i ] = true;
					_.each( subChecks, function( subVal ) {
						if ( ! subVal ) {
							checks[ i ] = false;
						}
					} );
					break;
				case 'true':
					checks[ i ] = ( true === settingVal || 'true' === settingVal || 1 === settingVal || '1' === settingVal || 'yes' === settingVal );
					break;
				}
			}
		} );

		_.each( checks, function( check ) {
			if ( ! check ) {
				finalCheck = false;
			}
		} );
		if ( false === finalCheck ) {
			return fallback.replace( /\$/g, value );
		}
		return success.replace( /\$/g, value );
	},

	/**
	 * Sanitizes a number value.
	 *
	 * @param {string|number} value - The value to sanitize.
	 * @return {number}
	 */
	number: function( value ) {
		value = value.toString();
		return value.replace( /\D+/g, '' );
	},

	/**
	 * Adds CSS values.
	 *
	 * @static
	 * @access public
	 * @since 7.0
	 * @param {array} values - An array of CSS values.
	 * @return {string} - The combined value.
	 */
	add_css_values( values ) {
		var units 		= [],
			numerics	= [],
			should_calc = false,
			result 		= '';

		if ( ! values && ! _.isArray( values ) && _.isEmpty( values ) ) {
			return;
		}
		// Figure out what we're dealing with.
		_.each( values, function( value, key ) {
			var unit;

			if ( 'auto' === value || 'inherit' === value || 'initial' === value ) {
				return;
			}

			// Trim the value.
			value          = value.trim();
			values[ key ]  = value;

			// Detect if the value uses calc().
			if ( false !== value.includes( 'calc' ) ) {
				should_calc = true;
			}

			// Add unit to the array of units used.
			unit = fusionSanitize.get_unit( value );

			if ( ! _.isEmpty( unit ) && ! units.includes( unit ) ) {
				units.push( unit );
			}

			// Add numeric value to the array of numerics.
			numerics.push( fusionSanitize.number( value ) );
		} );

		// Make sure there's 1 instance of each unit in the array.
		// We need that to figure out if we'll be using calc() or not below.
		units = _.uniq( units );

		// If we're using more than one units then we should use calc().
		if ( 1 < units.length ) {
			should_calc = true;
		}

		// All values added use the same unit and no calc() is necessary.
		// We simply need to return the numeric sum with the defined value.
		if ( ! should_calc ) {

			// No units, so just return the sum of all values.
			if ( 0 === units.length ) {
				return _.reduce( numerics, function( memo, num ) {
					return parseFloat( memo ) + parseFloat( num );
				} );
			}

			// Add values and append the unit.
			return _.reduce( numerics, function( memo, num ) {
				return parseFloat( memo ) + parseFloat( num );
			} ) + units[ 0 ];
		}

		// If we got this far then we need to use calc().
		// eslint-disable-next-line vars-on-top
		for ( var i = 0; i < values.length; i ++ ) {
			// eslint-disable-next-line vars-on-top
			var value = values[ i ];

			// Only add + if this is not the first item in the calculations.
			if ( 0 < i ) {
				result += ' + ';
			}

			if ( false !== value.includes( 'calc' ) ) {
				// Remove calc but keep the parentheses. This fixes a browser bug in older versions of some browsers
				// where nested calc values don't work. Leaving the parentheses has the exact same effect.
				result += value.replace( 'calc', '' );
			} else {
				result += value;
			}

		}

		// Remove multiple spaces.
		result = result.replace( / {5}| {4}| {3}| {2}/, ' ' );
		// A simple tweak to make sure that negative values are substracted.
		result = result.replace( /\+ -/, ' - ' );
		// The above might have resulted is a couple of double-spaces, so make them single again.
		result = result.replace( / {2}/, ' ' );
		// Put it all together and wrap it up.

		return 'calc(' + result + ')';

	},

	/**
	 * Takes any valid CSS unit and converts to pixels.
	 *
	 * @since 2.0.0
	 * @param {string}     value - The CSS value.
	 * @param {string|number} emSize - The size in pixels of an em.
	 * @param {string|number} screenSize - The screen-width in pixels.
	 * @return {string} - The fontsize.
	 */
	units_to_px: function( value, emSize, screenSize ) {
		var number = parseFloat( value ),
			units  = value.replace( /\d+([,.]\d+)?/g, '' );

		screenSize = screenSize || 1600;

		if ( 'em' === units || 'rem' === units ) {
			emSize = emSize || 16;
			return parseInt( number * emSize, 10 ) + 'px';
		}
		if ( '%' === units ) {
			return parseInt( number * screenSize / 100, 10 ) + 'px';
		}
		return parseInt( value, 10 ) + 'px';
	},

	/**
	 * If value is numeric append "px".
	 *
	 * @since 2.0
	 * @param {string} value - The CSS value.
	 * @return {string} - The value including pixels unit.
	 */
	maybe_append_px: function( value ) {
		return ( ! isNaN( value ) ) ? value + 'px' : value;
	},

	/**
	 * Returns a string when the color is solid (alpha = 1).
	 *
	 * @since 2.0.0
	 * @param {string} value - The color.
	 * @param {Object} args - An object with the values we'll return depending if transparent or not.
	 * @param {string} args.transparent - The value to return if transparent.
	 * @param {string} args.opaque - The value to return if color is opaque.
	 * @return {string} - The transparent value.
	 */
	return_color_if_opaque: function( value, args ) {
		var color;
		if ( 'transparent' === value ) {
			return args.transparent;
		}
		color = jQuery.AWB_Color( value );

		if ( 1 === color.alpha() ) {
			return args.opaque;
		}

		return args.transparent;
	},

	/**
	 * Adjusts the brightness of a color,
	 *
	 * @since 2.0.0
	 * @param {string}           value - The color we'll be adjusting.
	 * @param {string|number} adjustment - By how much we'll be adjusting.
	 *                                        Positive numbers increase lightness.
	 *                                        Negative numbers decrease lightness.
	 * @return {string} - RBGA color, ready to be used in CSS.
	 */
	lightness_adjust: function( value, adjustment ) {
		var color  = jQuery.AWB_Color( value ),
			adjust = Math.abs( adjustment ),
			neg    = ( 0 > adjust );

		if ( 1 < adjust ) {
			adjust = adjust / 100;
		}
		if ( neg ) {
			return color.lightness( '-=' + adjust ).toVarOrRgbaString();
		}
		return color.lightness( '+=' + adjust ).toVarOrRgbaString();
	},

	/**
	 * Similar to PHP's str_replace.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @param {Array}  args - The arguments [search,replace].
	 * @return {string} - modified value.
	 */
	string_replace: function( value, args ) {
		if ( ! _.isObject( args ) || _.isUndefined( args[ 0 ] ) || _.isUndefined( args[ 1 ] ) ) {
			return value;
		}
		return value.replace( new RegExp( args[ 0 ], 'g' ), args[ 1 ] );
	},

	/**
	 * Returns a string when the color is transparent.
	 *
	 * @since 2.0.0
	 * @param {string} value - The color.
	 * @param {Object} args - An object with the values we'll return depending if transparent or not.
	 * @param {string} args.transparent - The value to return if transparent. Use "$" to return the value.
	 * @param {string} args.opaque - The value to return if color is not transparent. Use "$" to return the value.
	 * @return {string} - The value depending on transparency.
	 */
	return_string_if_transparent: function( value, args ) {
		var color;
		if ( 'transparent' === value ) {
			return ( '$' === args.transparent ) ? value : args.transparent;
		}
		color = jQuery.AWB_Color( value );

		if ( 0 === color.alpha() ) {
			return ( '$' === args.transparent ) ? value : args.transparent;
		}
		return ( '$' === args.opaque ) ? value : args.opaque;
	},

	/**
	 * If a color is 100% transparent, then return opaque color - no transparency.
	 *
	 * @since 2.0.0
	 * @param {string} value - The color we'll be adjusting.
	 * @return {string} - RGBA/HEX color, ready to be used in CSS.
	 */
	get_non_transparent_color: function( value ) {
		var color = jQuery.AWB_Color( value );

		if ( 0 === color.alpha() ) {
			return color.alpha( 1 ).toVarOrRgbaString();
		}
		return value;
	},

	/**
	 * A header condition.
	 *
	 * @since 2.0.0
	 * @param {string} value - The value.
	 * @param {string} fallback - A fallback value.
	 * @return {string} - The value or fallback.
	 */
	header_border_color_condition_5: function( value, fallback ) {
		fallback = fallback || '';
		if (
			'v6' !== this.getSettings().header_layout &&
			'left' === this.getSettings().header_position &&
			this.getSettings().header_border_color &&
			0 === jQuery.AWB_Color( this.getSettings().header_border_color ).alpha()
		) {
			return value;
		}
		return fallback;
	},

	/**
	 * If the value is empty or does not exist rerurn 0, otherwise the value.
	 *
	 * @param {string} value - The value.
	 * @return {string|0} - Value or (int) 0.
	 */
	fallback_to_zero: function( value ) {
		return ( ! value || '' === value ) ? 0 : value;
	},

	/**
	 * If the value is empty or does not exist return the fallback, otherwise the value.
	 *
	 * @param {string} value - The value.
	 * @param {string|Object} fallback - The fallback .
	 * @return {string} - value or fallback.
	 */
	fallback_to_value: function( value, fallback ) {
		if ( 'object' === typeof fallback && 'undefined' !== typeof fallback[ 0 ] && 'undefined' !== typeof fallback[ 1 ] ) {
			return ( ! value || '' === value ) ? fallback[ 1 ].replace( /\$/g, value ) : fallback[ 0 ].replace( /\$/g, value );
		}
		return ( ! value || '' === value ) ? fallback : value;
	},

	/**
	 * If the value is empty or does not exist return the fallback, otherwise the value.
	 *
	 * @param {string} value - The value.
	 * @param {string|Object} fallback - The fallback .
	 * @return {string} - value or fallback.
	 */
	fallback_to_value_if_empty: function( value, fallback ) {
		if ( 'object' === typeof fallback && 'undefined' !== typeof fallback[ 0 ] && 'undefined' !== typeof fallback[ 1 ] ) {
			return ( '' === value ) ? fallback[ 1 ].replace( /\$/g, value ) : fallback[ 0 ].replace( /\$/g, value );
		}
		return ( '' === value ) ? fallback : value;
	},

	/**
	 * Returns a value if site-width is 100%, otherwise return a fallback value.
	 *
	 * @param {string} value The value.
	 * @param {Array} args [pattern,fallback]
	 * @return {string} - Value.
	 */
	site_width_100_percent: function( value, args ) {
		if ( ! args[ 0 ] ) {
			args[ 0 ] = '$';
		}
		if ( ! args[ 1 ] ) {
			args[ 1 ] = '';
		}
		if ( this.getSettings().site_width && '100%' === this.getSettings().site_width ) {
			return args[ 0 ].replace( /\$/g, value );
		}
		return args[ 1 ].replace( /\$/g, value );
	},

	/**
	 * Get the horizontal negative margin for 100%.
	 * This corresponds to the "$hundredplr_padding_negative_margin" var
	 * in previous versions of Avada's dynamic-css PHP implementation.
	 *
	 * @since 2.0
	 * @param {string} value - The value.
	 * @param {string} fallback - The value to return as a fallback.
	 * @return {string} - Negative margin value.
	 */
	hundred_percent_negative_margin: function() {
		var padding        = this.getOption( 'hundredp_padding', 'hundredp_padding' ),
			paddingValue   = parseFloat( padding ),
			paddingUnit    = 'string' === typeof padding ? padding.replace( /\d+([,.]\d+)?/g, '' ) : padding,
			negativeMargin = '',
			fullWidthMaxWidth;

		negativeMargin = '-' + padding;

		if ( '%' === paddingUnit ) {
			fullWidthMaxWidth = 100 - ( 2 * paddingValue );
			negativeMargin    = paddingValue / fullWidthMaxWidth * 100;
			negativeMargin    = '-' + negativeMargin + '%';
		}
		return negativeMargin;
	},

	/**
	 * Changes slider position.
	 *
	 * @param {string} value - The value.
	 * @param {Object} args - The arguments.
	 * @param {string} args.element - The element we want to affect.
	 * @return {void}
	 */
	change_slider_position: function( value, args ) {
		var $el = window.frames[ 0 ].jQuery( args.element );

		// We need lowercased value, so that's why global object is changed here.
		if ( 'undefined' !== typeof document.getElementById( 'fb-preview' ).contentWindow.avadaFusionSliderVars ) {
			document.getElementById( 'fb-preview' ).contentWindow.avadaFusionSliderVars.slider_position = value.toLowerCase();
		}

		if ( 'above' === value.toLowerCase() ) {
			$el.detach().insertBefore( '.avada-hook-before-header-wrapper' );
		} else if ( 'below' === value.toLowerCase() ) {
			$el.detach().insertAfter( '.avada-hook-after-header-wrapper' );
		}
	},

	/**
	 * Changes slider visibility.
	 *
	 * @param {string} value - The value.
	 * @param {Object} args - The arguments.
	 * @return {void}
	 */
	change_slider_visibility: function( values, args ) {
		var $el             = window.frames[ 0 ].jQuery( args.element ),
			visibilityOptions = [ 'small-visibility', 'medium-visibility', 'large-visibility' ],
			classes           = 'fusion-slider-visibility';

		if ( 'null' === values || '' === values ) {
			values = visibilityOptions;
		}

		values = 'object' !== typeof values ? values.split( ',' ) : values;

		jQuery.each( visibilityOptions, function( index, option ) {
			if ( -1 === jQuery.inArray( option, values ) ) {
				classes += ' fusion-no-' + option;
			}
		} );

		$el.attr( 'class', classes );
	},

	/**
	 * Adds CSS class necessary for changing header position.
	 *
	 * @param {string} value - The value.
	 * @return {void}
	 */
	change_header_position: function( value ) {
		var $body = window.frames[ 0 ].jQuery( 'body' ),
			classeToRemove = 'side-header side-header-left side-header-right fusion-top-header fusion-header-layout-v1 fusion-header-layout-v2 fusion-header-layout-v3 fusion-header-layout-v4 fusion-header-layout-v5 fusion-header-layout-v6 fusion-header-layout-v7';

		value = value.toLowerCase();

		$body.removeClass( classeToRemove );

		if ( 'left' === value || 'right' === value ) {
			$body.addClass( 'side-header side-header-' + value );
		} else if ( 'top' === value ) {
			$body.addClass( 'fusion-top-header fusion-header-layout-' + this.getOption( 'header_layout' ) );
		}
	},

	/**
	 * Toggles a body class.
	 *
	 * @param {string} value - The value.
	 * @param {Object} args - The arguments.
	 * @param {Array}  args.condition - The condition [valueToCheckAgainst,comparisonOperator]
	 * @param {string} args.element - The element we want to affect.
	 * @param {string}|{Array} args.className - The class-name we want to toggle.
	 * @return {void}
	 */
	toggle_class: function( value, args ) {
		var $el = window.frames[ 0 ].jQuery( args.element );
		if ( ! args.className ) {
			return;
		}

		if ( Array.isArray( args.className ) ) {
			jQuery.each( args.condition, function( index, condition ) {
				if ( value === condition ) {
					$el.removeClass( args.className.join( ' ' ) );
					$el.addClass( args.className[ index ] );
					return false;
				}
			} );

			return;
		}


		switch ( args.condition[ 1 ] ) {
		case '===':
			if ( value === args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '==':
			if ( value == args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '!==':
			if ( value !== args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '!=':
			if ( value != args.condition[ 0 ] ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '>=':
			if ( parseFloat( value ) >= parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '<=':
			if ( parseFloat( value ) <= parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '>':
			if ( parseFloat( value ) > parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case '<':
			if ( parseFloat( value ) < parseFloat( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'contains':
			if ( -1 !== value.indexOf( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'does-not-contain':
			if ( -1 === value.indexOf( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'opaque':
			if ( 1 === jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'not-opaque':
			if ( 1 > jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'header-not-opaque':
			if ( 1 > jQuery.AWB_Color( value ).alpha() && 'undefined' !== typeof FusionApp && 'off' !== FusionApp.preferencesData.transparent_header ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'full-transparent':
			if ( 'transparent' === value || 0 === jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'not-full-transparent':
			if ( 'transparent' !== value && 0 < jQuery.AWB_Color( value ).alpha() ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'true':
			if ( 1 === value || '1' === value || true === value || 'true' === value || 'yes' === value ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'false':
			if ( 1 === value || '1' === value || true === value || 'true' === value || 'yes' === value ) {
				$el.removeClass( args.className );
			} else {
				$el.addClass( args.className );
			}
			break;
		case 'has-image':
			if (
				( 'object' === typeof value && 'string' === typeof value.url && '' !== value.url ) ||
					( 'string' === typeof value && 0 <= value.indexOf( 'http' ) )
			) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'equal-to-option':
			if ( value === this.getOption( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'not-equal-to-option':
			if ( value !== this.getOption( args.condition[ 0 ] ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
			break;
		case 'is-zero-or-empty':
			if ( ! value || 0 === parseInt( value, 10 ) ) {
				$el.addClass( args.className );
			} else {
				$el.removeClass( args.className );
			}
		}
	},

	/**
	 * Converts a non-px font size to px.
	 *
	 * This is a JS post of the Fusion_Panel_Callbacks::convert_font_size_to_px() PHP method.
	 *
	 * @since 2.0
	 *
	 * @param {string} value - The font size to be changed.
	 * @param {string} baseFontSize - The font size to base calcs on.
	 * @return {string} - The changed font size.
	 */
	convert_font_size_to_px: function( value, baseFontSize ) {
		var fontSizeUnit,
			fontSizeNumber,
			defaultFontSize,
			addUnits,
			baseFontSizeUnit,
			baseFontSizeNumber;

		if ( 'string' === typeof value && value.includes( '--awb' ) && window.awbTypographySelect ) {
			value = window.awbTypographySelect.getRealValue( value );
		}

		fontSizeUnit       = 'string' === typeof value ? value.replace( /\d+([,.]\d+)?/g, '' ) : value;
		fontSizeNumber     = parseFloat( value );
		defaultFontSize    = 15; // Browser default font size. This is the average between Safari, Chrome and FF.
		addUnits           = 'object' === typeof baseFontSize && 'undefined' !== typeof baseFontSize.addUnits && baseFontSize.addUnits;

		if ( 'object' === typeof baseFontSize && 'undefined' !== typeof baseFontSize.setting ) {
			baseFontSize = this.getOption( baseFontSize.setting );
		}

		baseFontSizeUnit   = 'string' === typeof baseFontSize ? baseFontSize.replace( /\d+([,.]\d+)?/g, '' ) : baseFontSize;
		baseFontSizeNumber = parseFloat( baseFontSize );

		if ( ! fontSizeNumber ) {
			return value;
		}

		if ( 'px' === fontSizeUnit ) {
			return addUnits ? fontSizeNumber + 'px' : fontSizeNumber;
		}

		if ( 'em' === baseFontSizeUnit || 'rem' === baseFontSizeUnit ) {
			baseFontSizeNumber = defaultFontSize * baseFontSizeNumber;
		} else if ( '%' === baseFontSizeUnit ) {
			baseFontSizeNumber = defaultFontSize * baseFontSizeNumber / 100;
		} else if ( 'px' !== baseFontSizeUnit ) {
			baseFontSizeNumber = defaultFontSize;
		}

		if ( 'em' === fontSizeUnit || 'rem' === fontSizeUnit ) {
			fontSizeNumber = baseFontSizeNumber * fontSizeNumber;
		} else if ( '%' === fontSizeUnit ) {
			fontSizeNumber = baseFontSizeNumber * fontSizeNumber / 100;
		} else if ( 'px' !== fontSizeUnit ) {
			fontSizeNumber = baseFontSizeNumber;
		}

		return addUnits ? fontSizeNumber + 'px' : fontSizeNumber;
	},

	/**
	 * Converts the "regular" value to 400 for font-weights.
	 *
	 * @since 2.0
	 *
	 * @param {string} value - The font-weight.
	 * @return {string} - The changed font-weight.
	 */
	font_weight_no_regular: function( value ) {
		return ( 'regular' === value ) ? '400' : value;
	}
};

/**
 * Returns a string when the color is transparent.
 *
 * @since 2.0.0
 * @param {string} value - The color.
 * @param {Object} args - An object with the values we'll return depending if transparent or not.
 * @param {string} args.transparent - The value to return if transparent. Use "$" to return the value.
 * @param {string} args.opaque - The value to return if color is not transparent. Use "$" to return the value.
 * @return {string}
 */
function fusionReturnStringIfTransparent( value, args ) {
	var color;
	if ( 'transparent' === value ) {
		return ( '$' === args.transparent ) ? value : args.transparent;
	}
	color = jQuery.AWB_Color( value );

	if ( 0 === color.alpha() ) {
		return ( '$' === args.transparent ) ? value : args.transparent;
	}
	return ( '$' === args.opaque ) ? value : args.opaque;
}

/**
 * Return 1/0 depending on whether the color has transparency or not.
 *
 * @since 2.0
 * @param {string} value - The color.
 * @return {number}
 */
function fusionReturnColorAlphaInt( value ) {
	var color;
	if ( 'transparent' === value ) {
		return 1;
	}
	color = jQuery.AWB_Color( value );

	if ( 1 === color.alpha() ) {
		return 0;
	}
	return 1;
}

/**
 * This doesn't change the value.
 * What it does is set the window[ args.globalVar ][ args.id ] to the value.
 * After it is set, we use jQuery( window ).trigger( args.trigger );
 * If we have args.runAfter defined and it is a function, then it runs as well.
 *
 * @param {mixed}  value - The value.
 * @param {Object} args - An array of arguments.
 * @param {string} args.globalVar - The global variable we're setting.
 * @param {string} args.id - If globalVar is a global Object, then ID is the key.
 * @param {Array}  args.trigger - An array of actions to trigger.
 * @param {Array}  args.runAfter - An array of callbacks that will be triggered.
 * @param {Array}  args.condition - [setting,operator,setting_value,value_pattern,fallback].
 * @param {Array}  args.condition[0] - The setting we want to check.
 * @param {Array}  args.condition[1] - The comparison operator (===, !==, >= etc).
 * @param {Array}  args.condition[2] - The value we want to check against.
 * @param {Array}  args.condition[3] - The value-pattern to use if comparison is a success.
 * @param {Array}  args.condition[3] - The value-pattern to use if comparison is a failure.
 * @return {mixed} - Same as the input value.
 */
function fusionGlobalScriptSet( value, args ) {

	// If "choice" is defined, make sure we only use that key of the value.
	if ( ! _.isUndefined( args.choice ) && ! _.isUndefined( value[ args.choice ] ) ) {
		value = value[ args.choice ];
	}

	if ( ! _.isUndefined( args.callback ) && ! _.isUndefined( window[ args.callback ] ) && _.isFunction( window[ args.callback ] ) ) {
		value = window[ args.callback ]( value );
	}

	if ( _.isUndefined( window.frames[ 0 ] ) ) {
		return value;
	}

	if ( args.condition && args.condition[ 0 ] && args.condition[ 1 ] && args.condition[ 2 ] && args.condition[ 3 ] && args.condition[ 4 ] ) {
		switch ( args.condition[ 1 ] ) {
		case '===':
			if ( fusionSanitize.getOption( args.condition[ 0 ] ) === args.condition[ 2 ] ) {
				value = args.condition[ 2 ].replace( /\$/g, value );
			} else {
				value = args.condition[ 3 ].replace( /\$/g, value );
			}
			break;
		}
	}

	// If the defined globalVar is not defined, make sure we define it.
	if ( _.isUndefined( window.frames[ 0 ][ args.globalVar ] ) ) {
		window.frames[ 0 ][ args.globalVar ] = {};
	}

	if ( _.isUndefined( args.id ) ) {

		// If the id is not defined in the vars, then set globalVar to the value.
		window.frames[ 0 ][ args.globalVar ] = value;
	} else {

		// All went well, set the value as expected.
		window.frames[ 0 ][ args.globalVar ][ args.id ] = value;
	}

	// Trigger actions defined in the "trigger" argument.
	if ( ! _.isUndefined( args.trigger ) ) {
		_.each( args.trigger, function( eventToTrigger ) {
			fusionTriggerEvent( eventToTrigger );
			if ( 'function' === typeof window[ eventToTrigger ] ) {
				window[ eventToTrigger ]();
			} else if ( 'function' === typeof window.frames[ 0 ][ eventToTrigger ] ) {
				window.frames[ 0 ][ eventToTrigger ]();
			}
		} );
	}

	// Run functions defined in the "runAfter" argument.
	if ( ! _.isUndefined( args.runAfter ) ) {
		_.each( args.runAfter, function( runAfter ) {
			if ( _.isFunction( runAfter ) ) {
				window.frames[ 0 ][ runAfter ]();
			}
		} );
	}

	return value;
}

/**
 * Triggers an event.
 *
 * @param {string} eventToTrigger - The event to trigger.
 * @return {void}
 */
function fusionTriggerEvent( eventToTrigger ) {
	if ( 'resize' === eventToTrigger ) {
		fusionTriggerResize();
	} else if ( 'scroll' === eventToTrigger ) {
		fusionTriggerScroll();
	} else if ( 'load' === eventToTrigger ) {
		fusionTriggerLoad();
	} else {
		window.frames[ 0 ].dispatchEvent( new Event( eventToTrigger ) );
	}
}

/**
 * Triggers the "resize" event.
 *
 * @return {void}
 */
function fusionResize() {
	window.frames[ 0 ].dispatchEvent( new Event( 'resize' ) );
}

/**
 * Triggers the "scroll" event.
 *
 * @return {void}
 */
function fusionScroll() {
	window.frames[ 0 ].dispatchEvent( new Event( 'scroll' ) );
}

/**
 * Triggers the "load" event.
 *
 * @return {void}
 */
function fusionLoad() {
	window.frames[ 0 ].dispatchEvent( new Event( 'load' ) );
}

/**
 * Calculates media-queries.
 * This is a JS port of the PHP Fusion_Media_Query_Scripts::get_media_query() method.
 *
 * @since 2.0
 * @param {Object} args - Our arguments.
 * @param {string} context - Example: 'only screen'.
 * @param {boolean} addMedia - Whether we should prepend "@media" or not.
 * @return {string}
 */
function fusionGetMediaQuery( args, context, addMedia ) {
	var masterQueryArray = [],
		query            = '',
		queryArray;

	if ( ! context ) {
		context = 'only screen';
	}
	queryArray = [ context ],

	_.each( args, function( when ) {

		// If an array then we have multiple media-queries here
		// and we need to process each one separately.
		if ( 'string' !== typeof when[ 0 ] ) {
			queryArray = [ context ];
			_.each( when, function( subWhen ) {

				// Make sure pixels are integers.
				if ( subWhen[ 1 ] && -1 !== subWhen[ 1 ].indexOf( 'px' ) && -1 === subWhen[ 1 ].indexOf( 'dppx' ) ) {
					subWhen[ 1 ] = parseInt( subWhen[ 1 ], 10 ) + 'px';
				}
				queryArray.push( '(' + subWhen[ 0 ] + ': ' + subWhen[ 1 ] + ')' );
			} );
			masterQueryArray.push( queryArray.join( ' and ' ) );
		} else {

			// Make sure pixels are integers.
			if ( when[ 1 ] && -1 !== when.indexOf( 'px' ) && -1 === when.indexOf( 'dppx' ) ) {
				when[ 1 ] = parseInt( when[ 1 ], 10 ) + 'px';
			}
			queryArray.push( '(' + when[ 0 ] + ': ' + when[ 1 ] + ')' );
		}
	} );

	// If we've got multiple queries, then need to be separated using a comma.
	if ( ! _.isEmpty( masterQueryArray ) ) {
		query = masterQueryArray.join( ', ' );
	}

	// If we don't have multiple queries we need to separate arguments with "and".
	if ( ! query ) {
		query = queryArray.join( ' and ' );
	}

	if ( addMedia ) {
		return '@media ' + query;
	}
	return query;
}

/**
 * Returns the media-query
 *
 * @since 2.0.0
 * @param {Array} queryID - The query-ID.
 * @return {string} - The media-query.
 */
function fusionReturnMediaQuery( queryID ) {
	var breakpointRange = 360,
		sideheaderWidth = 0,
		settings        = fusionSanitize.getSettings(),
		mainBreakPoint,
		sixColumnsBreakpoint,
		fiveColumnsBreakpoint,
		fourColumnsBreakpoint,
		threeColumnsBreakpoint,
		twoColumnsBreakpoint,
		oneColumnBreakpoint,
		breakpointInterval;

	if ( 'top' !== settings.header_position ) {
		sideheaderWidth = parseInt( settings.side_header_width, 10 );
	}

	mainBreakPoint = parseInt( settings.grid_main_break_point, 10 );
	if ( 640 < mainBreakPoint ) {
		breakpointRange = mainBreakPoint - 640;
	}

	breakpointInterval = parseInt( breakpointRange / 5, 10 );

	sixColumnsBreakpoint   = mainBreakPoint + sideheaderWidth;
	fiveColumnsBreakpoint  = sixColumnsBreakpoint - breakpointInterval;
	fourColumnsBreakpoint  = fiveColumnsBreakpoint - breakpointInterval;
	threeColumnsBreakpoint = fourColumnsBreakpoint - breakpointInterval;
	twoColumnsBreakpoint   = threeColumnsBreakpoint - breakpointInterval;
	oneColumnBreakpoint    = twoColumnsBreakpoint - breakpointInterval;

	switch ( queryID ) {
	case 'fusion-max-1c':
		return fusionGetMediaQuery( [ [ 'max-width', oneColumnBreakpoint + 'px' ] ] );
	case 'fusion-max-2c':
		return fusionGetMediaQuery( [ [ 'max-width', twoColumnsBreakpoint + 'px' ] ] );
	case 'fusion-min-2c-max-3c':
		return fusionGetMediaQuery( [
			[ 'min-width', twoColumnsBreakpoint + 'px' ],
			[ 'max-width', threeColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-3c-max-4c':
		return fusionGetMediaQuery( [
			[ 'min-width', threeColumnsBreakpoint + 'px' ],
			[ 'max-width', fourColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-4c-max-5c':
		return fusionGetMediaQuery( [
			[ 'min-width', fourColumnsBreakpoint + 'px' ],
			[ 'max-width', fiveColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-5c-max-6c':
		return fusionGetMediaQuery( [
			[ 'min-width', fiveColumnsBreakpoint + 'px' ],
			[ 'max-width', sixColumnsBreakpoint + 'px' ]
		] );
	case 'fusion-min-shbp':
		return fusionGetMediaQuery( [ [ 'min-width', ( parseInt( settings.side_header_break_point, 10 ) + 1 ) + 'px' ] ] );
	case 'fusion-max-shbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ] ] );
	case 'fusion-max-sh-shbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + parseInt( settings.side_header_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-sh-cbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + parseInt( settings.content_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-sh-sbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + parseInt( settings.sidebar_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-shbp-retina':
		return fusionGetMediaQuery( [
			[
				[ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ],
				[ '-webkit-min-device-pixel-ratio', '1.5' ]
			],
			[
				[ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ],
				[ 'min-resolution', '144dpi' ]
			],
			[
				[ 'max-width', parseInt( settings.side_header_break_point, 10 ) + 'px' ],
				[ 'min-resolution', '1.5dppx' ]
			]
		] );
	case 'fusion-max-sh-640':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + 640, 10 ) + 'px' ] ] );
	case 'fusion-max-shbp-18':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( parseInt( settings.side_header_break_point, 10 ) - 18, 10 ) + 'px' ] ] );
	case 'fusion-max-shbp-32':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( parseInt( settings.side_header_break_point, 10 ) - 32, 10 ) + 'px' ] ] );
	case 'fusion-min-sh-cbp':
		return fusionGetMediaQuery( [ [ 'min-width', parseInt( sideheaderWidth + parseInt( settings.content_break_point, 10 ), 10 ) + 'px' ] ] );
	case 'fusion-max-sh-965-woo':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + 965, 10 ) + 'px' ] ] );
	case 'fusion-max-sh-900-woo':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( sideheaderWidth + 900, 10 ) + 'px' ] ] );
	case 'fusion-max-cbp':
		return fusionGetMediaQuery( [ [ 'max-width', parseInt( settings.content_break_point, 10 ) + 'px' ] ] );
	case 'fusion-max-main':
		return fusionGetMediaQuery( [ [ 'max-width', mainBreakPoint + 'px' ] ] );
	case 'fusion-min-cbp-max-main':
		return fusionGetMediaQuery( [
			[ 'max-width', mainBreakPoint + 'px' ],
			[ 'min-width', parseInt( settings.content_break_point, 10 ) + 'px' ]
		] );
	case 'fusion-min-768-max-1024':
		return fusionGetMediaQuery( [
			[ 'min-device-width', '768px' ],
			[ 'max-device-width', '1024px' ]
		] );
	case 'fusion-min-768-max-1024-p':
		return fusionGetMediaQuery( [
			[ 'min-device-width', '768px' ],
			[ 'max-device-width', '1024px' ],
			[ 'orientation', 'portrait' ]
		] );
	case 'fusion-min-768-max-1024-l':
		return fusionGetMediaQuery( [
			[ 'min-device-width', '768px' ],
			[ 'max-device-width', '1024px' ],
			[ 'orientation', 'landscape' ]
		] );
	case 'fusion-max-640':
		return fusionGetMediaQuery( [ [ 'max-device-width', '640px' ] ] );
	case 'fusion-max-768':
		return fusionGetMediaQuery( [ [ 'max-width', '782px' ] ] );
	case 'fusion-max-782':
		return fusionGetMediaQuery( [ [ 'max-width', '782px' ] ] );
	default:

		// FIXME: Default not needed, we only use it while developing.
		// This case should be deleted.
		console.info( 'MEDIA QUERY ' + queryID + ' NOT FOUND' );
	}
}

/**
 * Get the horizontal padding for the 100% width.
 * This corresponds to the "$hundredplr_padding" var
 * in previous versions of Avada's dynamic-css PHP implementation.
 *
 * @since 2.0
 * @return {string}
 */
function fusionGetPercentPaddingHorizontal( value, fallback ) {
	value = fusionSanitize.getOption( 'hundredp_padding', 'hundredp_padding' );
	return ( value ) ? value : fallback;
}

/**
 * Get the horizontal negative margin for 100%.
 * This corresponds to the "$hundredplr_padding_negative_margin" var
 * in previous versions of Avada's dynamic-css PHP implementation.
 *
 * @since 2.0
 * @param {string} value - The value.
 * @param {string} fallback - The value to return as a fallback.
 * @return {string}
 */
function fusionGetPercentPaddingHorizontalNegativeMargin() {
	var padding        = fusionGetPercentPaddingHorizontal(),
		paddingValue   = parseFloat( padding ),
		paddingUnit    = 'string' === typeof padding ? padding.replace( /\d+([,.]\d+)?/g, '' ) : padding,
		negativeMargin = '',
		fullWidthMaxWidth;

	negativeMargin = '-' + padding;

	if ( '%' === paddingUnit ) {
		fullWidthMaxWidth = 100 - ( 2 * paddingValue );
		negativeMargin    = paddingValue / fullWidthMaxWidth * 100;
		negativeMargin    = '-' + negativeMargin + '%';
	}
	return negativeMargin;
}

/**
 * Get the horizontal negative margin for 100%, if the site-width is using %.
 *
 * @since 2.0
 * @param {string} value - The value.
 * @param {string} fallback - The value to return as a fallback.
 * @return {string}
 */
function fusionGetPercentPaddingHorizontalNegativeMarginIfSiteWidthPercent( value, fallback ) {
	if ( fusionSanitize.getSettings().site_width && fusionSanitize.getSettings().site_width.indexOf( '%' ) ) {
		return fusionGetPercentPaddingHorizontalNegativeMargin();
	}
	return fallback;
}

function fusionRecalcAllMediaQueries() {
	var prefixes = [
			'',
			'avada-',
			'fb-'
		],
		suffixes = [
			'',
			'-bbpress',
			'-gravity',
			'-ec',
			'-woo',
			'-sliders',
			'-eslider',
			'-not-responsive',
			'-cf7',
			'-header-legacy'
		],
		queries  = [
			'max-sh-640',
			'max-1c',
			'max-2c',
			'min-2c-max-3c',
			'min-3c-max-4c',
			'min-4c-max-5c',
			'min-5c-max-6c',
			'max-shbp',
			'max-shbp-18',
			'max-shbp-32',
			'max-sh-shbp',
			'min-768-max-1024-p',
			'min-768-max-1024-l',
			'max-sh-cbp',
			'min-sh-cbp',
			'max-sh-sbp',
			'max-640',
			'min-shbp'
		],
		id,
		el,
		currentQuery,
		newQuery;

	// We only need to run this loop once.
	// Store in window.allFusionMediaIDs to improve performance.
	if ( ! window.allFusionMediaIDs ) {
		window.allFusionMediaIDs = {};

		queries.forEach( function( query ) {
			prefixes.forEach( function( prefix ) {
				suffixes.forEach( function( suffix ) {
					window.allFusionMediaIDs[ prefix + query + suffix + '-css' ] = query;
				} );
			} );
		} );
	}

	for ( id in window.allFusionMediaIDs ) { // eslint-disable-line guard-for-in
		el = window.frames[ 0 ].document.getElementById( id );
		if ( el ) {
			currentQuery = el.getAttribute( 'media' );
			newQuery     = fusionReturnMediaQuery( 'fusion-' + window.allFusionMediaIDs[ id ] );
			if ( newQuery !== currentQuery ) {
				el.setAttribute( 'media', newQuery );
			}
		}
	}
}

function fusionRecalcVisibilityMediaQueries() {
	var $previewFrameHead = jQuery( '#fb-preview' ).contents().find( 'head' ),
		mediaQueries = {
			small: fusionGetMediaQuery( [ [ 'max-width', parseInt( fusionSanitize.getOption( 'visibility_small' ), 10 ) + 'px' ] ] ),
			medium: fusionGetMediaQuery( [
				[ 'min-width', parseInt( fusionSanitize.getOption( 'visibility_small' ), 10 ) + 'px' ],
				[ 'max-width', parseInt( fusionSanitize.getOption( 'visibility_medium' ), 10 ) + 'px' ]
			] ),
			large: fusionGetMediaQuery( [ [ 'min-width', parseInt( fusionSanitize.getOption( 'visibility_medium' ), 10 ) + 'px' ] ] )
		},
		css = {
			small: '.fusion-no-small-visibility{display:none !important;}',
			medium: '.fusion-no-medium-visibility{display:none !important;}',
			large: '.fusion-no-large-visibility{display:none !important;}'
		},
		absoluteCss = 'position:absolute;top:auto;width:100%;';

	// Absolute positioning.
	css.small  += 'body .fusion-absolute-position-small{' + absoluteCss + '}';
	css.medium += 'body .fusion-absolute-position-medium{' + absoluteCss + '}';
	css.large  += 'body .fusion-absolute-position-large{' + absoluteCss + '}';

	// Add media queries.
	css.small  = mediaQueries.small + '{' + css.small + '}';
	css.medium = mediaQueries.medium + '{' + css.medium + '}';
	css.large  = mediaQueries.large + '{' + css.large + '}';

	if ( $previewFrameHead.find( '#css-fb-visibility' ).length ) {
		$previewFrameHead.find( '#css-fb-visibility' ).remove();
	}
	$previewFrameHead.append( '<style type="text/css" id="css-fb-visibility">' + css.small + css.medium + css.large + '</style>' );
}
