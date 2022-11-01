( function( $, undef ) {

	var AwbColorPicker,
		alphaImage   = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAIAAAHnlligAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAHJJREFUeNpi+P///4EDBxiAGMgCCCAGFB5AADGCRBgYDh48CCRZIJS9vT2QBAggFBkmBiSAogxFBiCAoHogAKIKAlBUYTELAiAmEtABEECk20G6BOmuIl0CIMBQ/IEMkO0myiSSraaaBhZcbkUOs0HuBwDplz5uFJ3Z4gAAAABJRU5ErkJggg==',
		awbColorParse;

	awbColorParse = function( valString ) {
		var response = {
				slug: false,
				h: false,
				s: false,
				l: false,
				a: false
			},
			varMap = {
				0: 'h',
				1: 's',
				2: 'l',
				3: 'a'
			},
			sign,
			values,
			propertyValues;

		// No valid value.
		if ( 'string' !== typeof valString || '' === valString ) {
			return response;
		}

		// Starts with a variable, means no manipulation
		if ( 0 === valString.indexOf( 'var(--awb-' ) ) {
			response.slug = valString.replace( 'var(--awb-', '' ).replace( ')', '' ).replace( /\s+/g, '' );
			return response;
		}

		// We have some manipulation, we need to do some magic.
		values = valString.replace( 'hsla(', '' ).replace( '))', ')' ).split( ',' );

		jQuery.each( values, function( index, value ) {
			if ( -1 !== value.indexOf( 'calc' ) ) {
				operator = ' + ';
				sign     = '';
				if ( -1 === value.indexOf( ' + ' ) ) {
					operator = ' - ';
					sign     = '-'
				}

				propertyValues = value.split( operator );

				if ( false === response.slug ) {
					response.slug = propertyValues[0].replace( 'calc(', '' ).replace( 'var(--awb-', '' ).replace( ')', '' ).replace( /\s+/g, '' ).replace( '-' + varMap[ index ], '' );
				}
				response[ varMap[ index ] ] = parseFloat( sign + propertyValues[1].replace( /\s+/g, '' ).replace( '%', '' ).replace( ')', '' ) );
			}
		} );

		return response;
	};

	/**
	 * Overwrite Color to enable support for rbga colors.
	 */
	Color.fn.toString = function() {
		var hex,
		    i;

		if ( this._alpha < 1 ) {
			return this.toCSS( 'rgba', this._alpha ).replace( /\s+/g, '' );
		}

		hex = parseInt( this._color, 10 ).toString( 16 );

		if ( this.error ) {
			return '';
		}

		if ( hex.length < 6 ) {
			for ( i = 6 - hex.length - 1; i >= 0; i-- ) {
				hex = '0' + hex;
			}
		}

		return '#' + hex;
	};

	/**
	 * Overwrite iris for alpha.
	 */
	jQuery.widget( 'a8c.iris', jQuery.a8c.iris, {
		_create: function() {
			this._super();

			if ( ! jQuery( this.element[0] ).hasClass( 'fusion-builder-color-picker-hex' ) && ! jQuery( this.element[0] ).hasClass( 'fusion-builder-color-picker-hex-new' ) && ! jQuery( this.element[0] ).hasClass( 'fusion_options' ) && ! jQuery( this.element[0] ).hasClass( 'awb-picker' )  ) {
				return;
			}

			this.element.addClass( 'fusion-color-created' );

			this.element.closest( '.awb-picker-outer' ).addClass( 'awb-picker-init' );

			// Global option for check is mode rbga is enabled
			this.options.alpha  = this.element.data( 'alpha' ) || false;

			// Is not input disabled
			if ( ! this.element.is( ':input' ) ) {
				this.options.alpha = false;
			}

			// Init color alpha.
			if ( 'undefined' !== typeof this.options.alpha && this.options.alpha ) {
				var self       = this,
				    _html      = '<div class="iris-strip iris-slider iris-alpha-slider"><div class="iris-slider-offset iris-slider-offset-alpha"></div></div>',
				    aContainer = jQuery( _html ).appendTo( self.picker.find( '.iris-picker-inner' ) ),
				    aSlider    = aContainer.find( '.iris-slider-offset-alpha' ),
				    controls   = {
						aContainer: aContainer,
						aSlider: aSlider
				    };

				self.picker.closest( '.awb-picker-container' ).addClass( 'awb-picker-alpha-container' );

				self.options.customWidth = 100;
				if ( 'undefined' !== typeof self.element.data( 'custom-width' ) ) {
					self.options.customWidth = parseInt( self.element.data( 'custom-width' ), 10 ) || 0;
				}

				// Set default width for input reset
				self.options.defaultWidth = self.element.width();

				// Update width for input
				if ( self._color._alpha < 1 || self._color.toString().indexOf( 'rgb' ) !== 1 ) {
					self.element.width( parseInt( self.options.defaultWidth + self.options.customWidth, 10 ) );
				}

				// Push new controls
				jQuery.each( controls, function( k, v ) {
					self.controls[ k ] = v;
				});

				// Change size strip and add margin for sliders
				self.controls.square.css({ 'margin-right': '0' });
				var emptyWidth   = ( self.picker.width() - self.controls.square.width() - 20 ),
				    stripsMargin = emptyWidth / 6,
				    stripsWidth  = ( emptyWidth / 2 ) - stripsMargin;

				jQuery.each( [ 'aContainer', 'strip' ], function( k, v ) {
					self.controls[ v ].width( stripsWidth ).css({ 'margin-left': stripsMargin + 'px' });
				});

				// Add new slider
				self._initControls();

				// For updated widget
				self._change();
			}
		},

		_initControls: function() {
			this._super();

			if ( this.options.alpha ) {
				var self     = this,
				    controls = self.controls;

				controls.aSlider.slider({
					orientation: 'vertical',
					min: 0,
					max: 100,
					step: 1,
					value: parseInt( self._color._alpha * 100, 10 ),
					slide: function( event, ui ) {

						// Update alpha value
						self._color._alpha = parseFloat( ui.value / 100 );
						self._change.apply( self, arguments );
					}
				});
			}
		},

		_change: function() {
			this._super();
			var self = this,
			    reset;

			if ( this.options.alpha ) {
				var controls     = self.controls,
				    alpha        = parseInt( self._color._alpha * 100, 10 ),
				    color        = self._color.toRgb(),
				    target       = self.picker.closest( '.awb-picker-wrap' ).prev( '.awb-picker-container' ).find( '.awb-color-result' ),
				    gradient     = [
						'rgb(' + color.r + ',' + color.g + ',' + color.b + ') 0%',
						'rgba(' + color.r + ',' + color.g + ',' + color.b + ', 0) 100%'
				    ];

				// Generate background slider alpha, only for CSS3 old browser fuck!! :)
				controls.aContainer.css({ 'background': 'linear-gradient(to bottom, ' + gradient.join( ', ' ) + '), url(' + alphaImage + ')' });

				if ( target.hasClass( 'awb-picker-open' ) ) {
					// Update alpha value
					controls.aSlider.slider( 'value', alpha );

					/**
					 * Disabled change opacity in default slider Saturation ( only is alpha enabled )
					 * and change input width for view all value
					 */
					if ( self._color._alpha < 1 ) {
						var style = controls.strip.attr( 'style' ).replace( /rgba\(([0-9]+,)(\s+)?([0-9]+,)(\s+)?([0-9]+)(,(\s+)?[0-9\.]+)\)/g, 'rgb($1$3$5)' );

						controls.strip.attr( 'style', style );
					}
				}
			}
		}
	} );

	/**
	 * Creates a jQuery UI color picker that is used in the theme customizer.
	 *
	 * @class $.widget.wp.awbcolorpicker
	 *
	 * @since 3.5.0
	 */
	AwbColorPicker = /** @lends $.widget.wp.awbcolorpicker.prototype */{
		options: {
			defaultColor: false,
			change: false,
			clear: false,
			hide: true,
			palettes: false,
			width: 280,
			globals: true,
			mode: 'hsv',
			type: 'full',
			slider: 'horizontal'
		},

		/**
		 * Creates the color picker, sets default values, css classes and wraps it all in HTML.
		 *
		 * @since 3.5.0
		 * @access private
		 *
		 * @return {void}
		 */
		_create: function() {
			// Return early if Iris support is missing.

			if ( ! $.support.iris ) {
				return;
			}

			var self        = this,
				el          = self.element,
				markup      = '<div class="awb-picker-outer"><div class="awb-picker-container">',
				colorMarkup = '',
				irisInstance,
				cogType     = self._checkCurrentPage(),
				originalEl;

			// Override default options with options bound to the element.
			$.extend( self.options, el.data() );

			// Bind the close event.
			self.close = self.close.bind( self );

			// Set some helper vars.
			self.initialValue         = el.val();
			self.allowGlobals         = 'false' !== self.element.attr( 'data-global' ) && false !== self.element.attr( 'data-global' ) && false !== self.options.globals;
			self.options.defaultColor = self.element.attr( 'data-default' ) || self.element.attr( 'data-default-color' ) || self.options.defaultColor;
			self.palettePicker        = self.element.hasClass( 'awb-palette-picker' );

			self.allowColorPickerToggle = true;
			if ( self.palettePicker || 'false' === self.element.attr( 'data-allow-picker-toggle' ) || false === self.options.allowToggle ) {
				self.allowColorPickerToggle = false;
			}

			// Consistent use of class on input.
			self.element.addClass( 'awb-color-picker' );

			// Set up the toggle button and insert it before the wrapping label.
			if ( ! self.palettePicker ) {
				markup += '<button type="button" class="button awb-color-result" aria-expanded="false" style="background-color:' + self.initialValue + '"></button>';
			} else {
				self.options.hide = false;
			}

			if ( self.allowGlobals ) {
				markup += '<span class="awb-global-label">' + awbPalette.global + '</span><span class="awb-picker-button"><i class="fusiona-globe" aria-hidden="true"></i></span>';
			}

			markup += '</div><div class="awb-picker-wrap"></div>';

			if ( self.allowGlobals ) {
				colorMarkup = this.buildGlobals();
				markup     += '<div class="awb-picker-global"><div class="awb-global-bar"><span class="global-title">' + awbPalette.global + '</span><span class="edit-globals" data-fusion-option="color_palette" data-cog-type="' + cogType + '"><i class="fusiona-cog"></i></span><span class="advanced-edit"><i class="fusiona-preferences"></i></span></div><div class="awb-global-palette">' + colorMarkup + '</div><div class="advanced-color"><div class="hue"><label for="hue">H</label><input class="awb-ignore" type="number" id="hue" name="hue" min="-360" max="360"></div><div class="saturation"><label for="saturation">S</label><input class="awb-ignore" type="number" id="saturation" name="saturation" min="-100" max="100"></div><div class="lightness"><label for="lightness">L</label><input class="awb-ignore" type="number" id="lightness" name="lightness" min="-100" max="100"></div><div class="alpha"><label for="alpha">A</label><input class="awb-ignore" type="number" id="alpha" name="alpha" min="-100" max="100"></div></div>';
			}

			markup += '</div>';

			// Insert the wrapper and detach the original input. Will be inserted later.
			self.outerWrapper = jQuery( markup );
			self.outerWrapper.insertAfter( this.element );
			this.element.detach();

			// Element references to avoid looking up again.
			self.wrap         = self.outerWrapper.find( '.awb-picker-container' );
			self.picker       = self.outerWrapper.find( '.awb-picker-wrap' );

			if ( self.allowGlobals ) {
				self.globalLabel  = self.outerWrapper.find( '.awb-global-label' );
				self.globalColors = self.outerWrapper.find( '.awb-picker-global' );
				self.button       = self.outerWrapper.find( '.awb-picker-button' );
			}

			if ( ! self.palettePicker ) {
				self.toggler = self.wrap.find( '.awb-color-result' );
				this.element.insertAfter( self.toggler );
			} else {
				self.toggler = self.outerWrapper.closest( '.fusion-color-palette-item' ).find( '.awb-palette-title .preview' );
				this.element.prependTo( self.wrap );
			}

			self.element = self.wrap.find( '.awb-color-picker' );

			// Default live builder, appear as color but empty value.
			if ( self.options.defaultColor && '' === self.initialValue ) {
				self.toggler.css( { backgroundColor: self.options.defaultColor } );
			}

			self.element.val( self.initialValue );

			self.element.iris( {
				target: self.picker,
				hide: self.options.hide,
				width: self.options.width,
				mode: self.options.mode,
				palettes: false,
				change: function( event, ui ) {
					self.change( event, ui );
				}
			} );

			self.iris = self.element.find( '.iris-picker' );

			self._addListeners();

			// Put the draggable button inside the color picker to start from the
			// default(inherited global) color, if we don't have a specific color,
			// and a global color is not used.
			if ( '' === self.element.val() && self.options.defaultColor && ! self.options.defaultColor.includes( 'var(' ) ) {
				irisInstance = self.element.iris( 'instance' );
				// Iris won't trigger change event when changing color until init is true.
				irisInstance._inited = false;
				self.element.iris( 'color', self.options.defaultColor );
				irisInstance._inited = true;

				if ( ! self.isGlobalOption() ) {
					self.element.val( '' );
				}
			}

			// Force the color picker to always be closed on initial load.
			if ( ! self.options.hide ) {
				self.toggler.click();
			}
		},

		buildGlobals: function() {
			var colorMarkup = '';
			jQuery.each( awbPalette.data, function( slug, data ) {
				colorMarkup += '<span class="palette" aria-label="' + data.label + '" data-color="' + slug + '" data-value="' + data.color + '" style="background:' + data.color + '"></span>';
			} );
			return colorMarkup;
		},

		_refreshPaletteGlobalColors: function( self ) {
			var colorSlug,
				isGlobalColorCurrentlySetAsValue;

			// Replace all the palette colors with new ones.
			self.globalColors.find( '.awb-global-palette' ).html( self.buildGlobals() );

			// Replace the value and the color preview with new ones if needed(the value is set to a global color).
			if ( -1 !== self.element.val().indexOf( '--awb-' ) ) {
				replaceGlobalInValue();
			}

			function replaceGlobalInValue() {
				var globalColorReplaced = false;

				for ( colorSlug in awbPalette.data ) {
					isGlobalColorCurrentlySetAsValue = ( -1 !== self.element.val().indexOf( '--awb-' + colorSlug ) );
					if ( isGlobalColorCurrentlySetAsValue ) {
						globalColorObject = awbPalette.getColorObject( colorSlug );
						if ( globalColorObject ) {
							globalColorReplaced = true;
							self.globalLabel.text( globalColorObject.label );
							self.toggler.css( 'backgroundColor', globalColorObject.color );
						}
					}
				}

				if ( ! globalColorReplaced ) {
					globalColorObject = awbPalette.getDefaultColorObject();
					self.globalLabel.text( globalColorObject.label );
					self.toggler.css( 'backgroundColor', globalColorObject.color );
				}
			}
		},

		/**
		 * Binds event listeners to the color picker.
		 *
		 * @since 3.5.0
		 * @access private
		 *
		 * @return {void}
		 */
		_addListeners: function() {
			var self = this;
			var dragButton = self.outerWrapper.find( '.iris-square-value' );

			this.closeGlobalPicker = this.closeGlobalPicker.bind( this );
			this.handleClickOutsideGlobal = this.handleClickOutsideGlobal.bind( this );
			this.openGlobalPicker = this.openGlobalPicker.bind( this );

			if ( self.allowColorPickerToggle ) {
				self.toggler.on( 'click', self.toggle.bind( self ) );
				self.element.off( 'focus' );
			} else {
				// Open color picker only if the global options are open.
				self.toggler.on( 'click', function() {
					// If its opened.
					if ( self.outerWrapper.hasClass( 'awb-global-active' ) ) {
						self.open();
					}
				});
			}


			// Do not jump to top of the page when clicking handle.
			self.picker.on( 'click', '.iris-square-value', function( event ) {
				event.preventDefault();
			} );

			if ( self.allowGlobals ) {
				// Toggling open the global picker.
				self.button.on( 'click', function( event ) {

					// Close color picker.
					if ( self.outerWrapper.hasClass( 'awb-picker-active' ) ) {
						self.close();
					}

					if ( ! self.outerWrapper.hasClass( 'awb-global-active' ) ) {
						self.openGlobalPicker();
					} else {
						self.closeGlobalPicker();
						if ( ! self.allowColorPickerToggle ) {
							self.open();
						}
					}
				});

				// Selecting a color in the palette.
				self.globalColors.on( 'click', '.palette', function( event ) {
					var $picker = jQuery( event.target );

					if ( $picker.hasClass( 'active' ) ) {
						return;
					}

					self.globalColors.find( '.active' ).removeClass( 'active' );
					$picker.addClass( 'active' );

					self.updateVarValue();
				});

				// When a global color is changed, update on the globals palette.
				self.refreshPaletteGlobalColors = _.debounce( self._refreshPaletteGlobalColors, 500 );
				jQuery( document ).on( 'awbPalette', self.refreshPaletteGlobalColors.bind( null, self ) );

				// Changing an advanced color modifier.
				self.globalColors.on( 'change', '#hue, #saturation, #lightness, #alpha', function() {
					self.updateVarValue();
				});

				// Toggle the advanced edit area.
				self.globalColors.on( 'click', '.advanced-edit', function( event ) {
					event.preventDefault();

					if ( self.globalColors.hasClass( 'show-advanced' ) ) {
						self.globalColors.removeClass( 'show-advanced' );
					} else {
						self.globalColors.addClass( 'show-advanced' );
					}
				} );

				// Deep link to Global Color Palette.
				self.globalColors.on( 'click', '.edit-globals', function( event ) {
					event.preventDefault();
					var $element = jQuery( event.currentTarget ),
						cogType  = jQuery( event.currentTarget ).attr( 'data-cog-type' );

					if ( 'live' === cogType && FusionApp.sidebarView ) {
						FusionApp.sidebarView.shortcutClick( $element );
					} else if ( 'backend' === cogType ) {
						window.open( awbPalette.goLink + '#color_palette', '_blank' );
					} else if ( 'function' === typeof fusionOpenOption ) {
						fusionOpenOption( 'color_palette' );
					}
				} );
			}
			self.element.on( 'change keyup', self.change.bind( self ) );

			dragButton.on( 'drag', function( event, data ) {
				if( ! data || ! data.position || ! data.position.left ) {
					return;
				}

				if ( data.position.left < 0 ) {
					data.position.left = 0;
				}
				if ( data.position.top < 0 ) {
					data.position.top = 0;
				}
			} );

		},

		openGlobals: function() {
			if ( this.outerWrapper.hasClass( 'awb-picker-active' ) ) {
				this.close();
			}

			this.setGlobals();
			this.outerWrapper.addClass( 'awb-global-active' );
		},

		/**
		 * Opening the globals, work out values for inputs.
		 *
		 * @since 3.5.0
		 *
		 * @return {void}
		 */
		setGlobals: function() {
			var data = awbColorParse( this.element.val() );

			this.globalColors.find( '.palette.active' ).removeClass( 'active' );

			// Initial selected palette.
			if ( data.slug ) {
				this.globalColors.find( '.palette[data-color="' + data.slug + '"]' ).addClass( 'active' );
			}

			// Manipulation values.
			this.globalColors.find( '#hue' ).val( data.h );
			this.globalColors.find( '#saturation' ).val( data.s );
			this.globalColors.find( '#lightness' ).val( data.l );
			this.globalColors.find( '#alpha' ).val( data.a );
		},

		/**
		 * Work out the variable and set it as value.
		 *
		 * @since 3.5.0
		 *
		 * @return {void}
		 */
		updateVarValue: function() {

			// Can rely on active, or get from existing value?
			var $activeColor = this.globalColors.find( '.active' ),
				hue          = this.globalColors.find( '#hue' ).val(),
				saturation   = this.globalColors.find( '#saturation' ).val(),
				lightness    = this.globalColors.find( '#lightness' ).val(),
				alpha        = this.globalColors.find( '#alpha' ).val(),
				hsla,
				varName,
				value;

			// Should empty value?
			if ( ! $activeColor ) {
				return;
			}

			// Check which manipulations we have.
			hue        = 'undefined' !== typeof hue && 0 != hue ? hue : false;
			saturation = 'undefined' !== typeof saturation && 0 != saturation ? saturation : false;
			lightness  = 'undefined' !== typeof lightness && 0 != lightness ? lightness : false;
			alpha      = 'undefined' !== typeof alpha && 0 != alpha ? alpha : false;

			varName = '--awb-' + $activeColor.attr( 'data-color' );

			// If we are manipulating, then use hsla.
			if ( false !== hue || false !== saturation || false !== lightness || false !== alpha ) {
				hsla  = { hue: hue, saturation: saturation, lightness: lightness, alpha: alpha };
				value = 'hsla(';

				jQuery.each( hsla, function( channel, val ) {
					var unit = 'hue' !== channel ? '%' : '';

					if ( false !== val ) {
						if ( 0 < val ) {
							val = ' + ' + val + unit;
						} else {
							val = ' - ' + Math.abs( val ) + unit;
						}
						value += 'calc(var(' + varName + '-' + channel.charAt(0) + ')' + val + '),';
					} else {
						value += 'var(' + varName + '-' + channel.charAt(0) + '),';
					}
				} );

				value = value.slice( 0, -1 );
				value += ')';
			} else {

				// No manipulations, just direct var.
				value = 'var(' + varName + ')';
			}

			this.element.val( value ).trigger( 'change' );
		},

		toggle: function() {
			if ( this.toggler.hasClass( 'awb-picker-open' ) || 'none' !== this.picker.find('.iris-picker').css( 'display' ) ) {
				this.close();
			} else {
				this.open();
			}
		},

		/**
		 * Opens the color picker dialog.
		 *
		 * @since 3.5.0
		 *
		 * @return {void}
		 */
		open: function() {
			var value = this.element.val(),
				width = this.element.hasClass( 'awb-palette-picker' ) ? ( this.element.closest( '#color_palette-list' ).width() - 28 ) : this.element.parent().width(),
				data;

			// If the picker can't fit.
			if ( 200 < width && width < this.element.iris( 'option', 'width' ) ) {
				this.element.iris( 'option', 'width', width );
			}

			this.element.iris( 'show' );
			this.outerWrapper.addClass( 'awb-picker-active' ).removeClass( 'awb-global-active' );
			this.toggler.addClass( 'awb-picker-open' ).attr( 'aria-expanded', 'true' );

			if ( this.allowColorPickerToggle && ! this.isClickOutsideAttached ) {
				this.isClickOutsideAttached   = true;
				this.handleClickOutsidePicker = this.handleClickOutsidePicker.bind( this );
				jQuery( 'body' ).on( 'click.awbColorPickerOpened', this.handleClickOutsidePicker );
			}

			// Opening picker with global set, set to actual color now.
			if ( 1 !== value.indexOf( 'var(--awb-' ) ) {
				data = awbColorParse( value );
				if ( data.slug && awbPalette.getColorObject( data.slug ) ) {
					this.element.val( awbPalette.getColorObject( data.slug ).color ).trigger( 'change' );
				}

				// If its empty, with default color.  Set the color.
			} else if ( this.options.defaultColor && '' === value ) {
				this.element.val( this.options.defaultColor ).trigger( 'change' );
			}

			this.fixIrisDragButtonOutsideDragArea();
		},

		/**
		 * Closes the color picker dialog.
		 *
		 * @since 3.5.0
		 *
		 * @return {void}
		 */
		close: function() {
			this.outerWrapper.removeClass( 'awb-picker-active' );
			this.element.iris( 'hide' );
			this.toggler.removeClass( 'awb-picker-open' ).attr( 'aria-expanded', 'false' );
			this.isClickOutsideAttached = false;
			jQuery( 'body' ).off( 'click.awbColorPickerOpened', this.handleClickOutsidePicker );
		},

		// Annoying Iris visual bug, make sure that the initial draggable button is within the parent.
		fixIrisDragButtonOutsideDragArea: function() {
			var dragButton = this.outerWrapper.find( '.iris-square-value' );
			var dragArea   = dragButton.parent();

			var dragAreaWidth = dragArea.width();

			var dragButtonWidth = dragButton.width();
			var dragButtonLeft = parseInt( dragButton.css( 'left' ) );

			if ( dragButtonLeft + dragButtonWidth > dragAreaWidth ) {
				dragButton.css( 'left', parseInt( dragAreaWidth - dragButtonWidth ) + 'px' );
			}
		},

		/**
		 * Closes the color picker dialog when clicking outside of it.
		 *
		 * @since 3.6.0
		 * @return {void}
		 */
		handleClickOutsidePicker: function( event ) {
			var clickFromOutside = ( this.outerWrapper.find( event.target ).length === 0 ) ? true : false;
			if ( clickFromOutside ) {
				this.close();
			}
		},

		/**
		 * Open the color global picker dialog.
		 *
		 * @since 3.6.0
		 * @return {void}
		 */
		openGlobalPicker: function() {
			var self = this;

			this.setGlobals();
			this.outerWrapper.addClass( 'awb-global-active' );
			// Show advanced HSLA fields if they are modified.
			if ( isAnAdvancedEditsSet() && ! this.globalColors.hasClass( 'show-advanced' ) ) {
				this.globalColors.find( '.advanced-edit' ).trigger( 'click' );
			}
			if ( this.allowColorPickerToggle ) {
				jQuery( 'body' ).on( 'click.awbColorPickerOpened', this.handleClickOutsideGlobal );
			}

			// Whether or not a HSLA field is set differently than 0.
			function isAnAdvancedEditsSet() {
				var inputs = self.globalColors.find( '.advanced-color input' ),
					isAFieldSet = false;

				inputs.each( function() {
					if ( jQuery( this ).val() && '0' !== jQuery( this ).val() ) {
						isAFieldSet = true;
					}
				} );

				return isAFieldSet;
			}
		},

		/**
		 * Closes the color global picker dialog.
		 *
		 * @since 3.6.0
		 * @return {void}
		 */
		closeGlobalPicker: function() {
			this.outerWrapper.removeClass( 'awb-global-active' );
			jQuery( 'body' ).off( 'click.awbColorPickerOpened', this.handleClickOutsideGlobal );
		},

		/**
		 * Closes the color global picker dialog when clicking outside of it.
		 *
		 * @since 3.6.0
		 * @return {void}
		 */
		handleClickOutsideGlobal: function( event ) {
			var clickFromOutside = ( this.outerWrapper.find( event.target ).length === 0 ) ? true : false;
			if ( clickFromOutside ) {
				this.closeGlobalPicker();
			}
		},

		/**
		 * Returns the iris object if no new color is provided. If a new color is provided, it sets the new color.
		 *
		 * @param newColor {string|*} The new color to use. Can be undefined.
		 *
		 * @since 3.5.0
		 *
		 * @return {string} The element's color.
		 */
		color: function( newColor ) {
			if ( newColor === undef ) {
				return this.element.iris( 'option', 'color' );
			}

			this.element.val( newColor ).trigger( 'change' );
		},

		/**
		 * Returns the iris object if no new default color is provided.
		 * If a new default color is provided, it sets the new default color.
		 *
		 * @param newDefaultColor {string|*} The new default color to use. Can be undefined.
		 *
		 * @since 3.5.0
		 *
		 * @return {boolean|string} The element's color.
		 */
		defaultColor: function( newDefaultColor ) {
			if ( newDefaultColor === undef ) {
				return this.options.defaultColor;
			}

			if ( this.element.attr( 'data-default' ) ) {
				this.element.attr( 'data-default', newDefaultColor );
			} else if( this.element.attr( 'data-default-color' ) ) {
				this.element.attr( 'data-default-color', newDefaultColor );
			}

			this.options.defaultColor = newDefaultColor;
		},

		change: function( event, ui ) {
			var $input   = this.element,
				$picker  = this.picker,
				$toggler = this.toggler,
				inputVal = $input.val(),
				val      = 'object' === typeof ui ? ui.color.toString() : inputVal,
				valRGBA,
				inputValRGBA,
				slug,
				data;

			$input.removeClass( 'iris-error' );

			// Special case palette picker.
			if ( $input.hasClass( 'awb-palette-picker' ) ) {
				$toggler = $input.closest( '.fusion-color-palette-item' ).find( '.awb-palette-title .preview' )
				slug     = $picker.closest( '.fusion-color-palette-item' ).attr( 'data-slug' );

				awbPalette.addOrUpdateColor( slug, { color: val } );
			}

			// Setting to a global variable.
			if ( -1 !== inputVal.indexOf( 'var(--awb-' ) ) {
				data = awbColorParse( inputVal );
				if ( data.slug ) {
					var paletteObject = awbPalette.getColorObject( data.slug );
					if ( ! paletteObject ) {
						paletteObject = awbPalette.getDefaultColorObject();
					}

					$toggler.css( { backgroundColor: $input.val() } );
					$input.attr( 'readonly', true ).closest( '.awb-picker-outer' ).addClass( 'awb-global-set' );
					$input.next( '.awb-global-label' ).text( paletteObject.label );

					// If color picker is active, toggle it closed.
					if ( $toggler.hasClass( 'awb-picker-open' ) ) {
						$toggler.trigger( 'click' );
					}
				}
			} else {
				$input.attr( 'readonly', false ).closest( '.awb-picker-outer' ).removeClass( 'awb-global-set' );
				if ( 'object' === typeof ui && ui.color.error ) {
					if ( '' !== val ) {
						$input.addClass( 'iris-error' );
					}
				} else {
					// If alpha is 0 and we're changing to a different color reset alpha to 1.
					valRGBA      = val.replace( / |\(|\)|rgba/g, '' ).split( ',' );
					inputValRGBA = inputVal.replace( / |\(|\)|rgba/g, '' ).split( ',' );

					if (
						val !== inputVal &&
						( valRGBA[ 3 ] && '0' == valRGBA[ 3 ] ) &&
						( ( inputValRGBA[ 3 ] && inputValRGBA[ 3 ] === valRGBA[ 3 ] ) || ( '' === inputVal ) )
					) {
						valRGBA[ 3 ] = 1;

						$input.iris( 'color', 'rgba(' + valRGBA.join( ',' ) + ')' );
					}

					$input.val( val );
					if ( '' === val && this.options.defaultColor ) {
						val = this.options.defaultColor;
					}
					$toggler.css( { backgroundColor: val } );
				}
				$picker.find( '.awb-global-palette .active' ).removeClass( 'active' );
			}

			// Trigger change from here, takes into account input change.
			if ( 'function' === typeof this.options.change ) {
				this.options.change.call( this, event, ui, val );
			}

			// Listener for builder, but without trigger change loop.
			$input.trigger( 'fusion-change' );
		},

		isGlobalOption: function() {
			var optionId;

			// Verify in LE.
			if ( 'TO' === this.element.closest( '.fusion-builder-option' ).attr( 'data-type' ) ) {
				return true;
			}

			// Verify in backend GO.
			if ( this.element.closest( '.fusionredux-field-container' ).length ) {
				// Exclude options from "Avada Builder Elements".
				if ( window.fusionredux && window.fusionredux.optionSections && window.fusionredux.optionSections.fusion_builder_elements ) {
					optionId = this.element.attr( 'data-id' );
					if ( optionId && window.fusionredux.optionSections.fusion_builder_elements[ optionId ] ) {
						return false;
					}
				}

				return true;
			}

			return false;
		},

		/**
		 * Check whether current page is live editor, backend or GO.
		 *
		 * @since 3.5.0
		 */
		_checkCurrentPage: function() {
			if ( 'undefined' !== typeof fusionBuilderConfig ) {
				return 'backend';
			} else if ( 'undefined' !== typeof fusionAppConfig ) {
				return 'live';
			}
			return 'go';
		}
	};

	// Register the color picker as a widget.
	$.widget( 'awb.awbColorPicker', AwbColorPicker );
}( jQuery ) );
