/* global awbTypoData */

( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		// We just need 1 typography select, so move it to separate file and init on ready.
		window.awbTypographySelect = {
			$el: false,
			$global: false,
			$input: false,
			$location: false,
			markup: '',
			stringMap: awbTypoData.strings,
			typoData: awbTypoData.data,
			webfonts: undefined,
			webfontsArray: undefined,
			webfontsGoogleArray: undefined,
			webfontsAdobeArray: undefined,
			webfontsStandardArray: undefined,
			webfontRequest: false,
			fontData: false,
			fontArrays: false,

			init: function() {
				var self        = this,
					testOptions = '',
					cogType     = self._checkCurrentPage();

				this.markup = '<div class="awb-picker-global"><div class="awb-global-bar"><span class="global-title">' + awbTypoData.strings.global + '</span><span class="edit-globals" data-fusion-option="typography_sets" data-cog-type="' + cogType + '"><i class="fusiona-cog"></i></span><span class="close-globals"><i class="fusiona-cross"></i></span></div><div class="awb-global-sets">[typoSets]</div></div>';

				// Build the markup, its the same each time.
				jQuery.each( this.typoData, function( slug, set ) {
			       testOptions += '<div class="awb-typo-option" data-value="' + slug + '">' + set.label + '</div>';
			    } );

				this.markup     = this.markup.replace( '[typoSets]', testOptions );
			},

			updateData: function( slug, subset, value ) {
				var self          = this,
					fontWeight    = '400',
					oldTypography = {},
					fontStyle     = '';

				if ( 'new' === subset ) {
					this.typoData[ slug ] = value;
					this.updateMarkup( slug, value.label );

					// Trigger event, so far no listener.
					jQuery( document ).trigger( 'awbTypoNew', { slug: slug, subset: subset, value: value } );
				} else {
					if ( 'variant' === subset ) {
						if ( value.includes( 'italic' ) ) {
							fontStyle = 'italic';
						}
						fontWeight = value.replace( 'italic', '' );
						this.updateData( slug, 'font-weight', fontWeight );
						this.updateData( slug, 'font-style', fontStyle );
					}

					if ( 'undefined' === typeof this.typoData[ slug ] ) {
						this.typoData[ slug ] = {};
					}
					oldTypography = Object.assign( {}, this.typoData[ slug ] ) ;
					this.typoData[ slug ][ subset ] = value;

					// Trigger event for open typography sets.
					jQuery( document ).trigger( 'awbTypoUpdate', { slug: slug, subset: subset, value: value, oldTypography: oldTypography } );

					// Update label or variable.
					if ( 'label' === subset ) {
						this.updateMarkup( slug, value );
					}
				}

			},

			removeData: function( slug ) {
				delete this.typoData[ slug ];
				this.removeFromMarkup( slug );
				jQuery( document ).trigger( 'awbTypoDeleted', { slug: slug } );
			},

			webFontLoad: function( family, variant ) {
				var isGoogleFont = window.awbTypographySelect.isGoogleFont( family ),
					scriptID,
					script;

				// Early exit if there is no font-family defined.
				if ( _.isUndefined( family ) || '' === family || ! family ) {
					return;
				}

				// Get a valid variant.
				variant = window.awbTypographySelect.getValidVariant( family, variant );

				// Early exit if not a google-font.
				if ( false === isGoogleFont ) {
					return;
				}

				variant = ( _.isUndefined( variant ) || ! variant ) ? ':regular' : ':' + variant;
				family  = family.replace( /"/g, '&quot' );

				script  = family;
				script += ( variant ) ? variant : '';

				scriptID = script.replace( /:/g, '' ).replace( /"/g, '' ).replace( /'/g, '' ).replace( / /g, '' ).replace( /,/, '' );

				if ( 'undefined' !== typeof FusionApp ) {
					if ( ! jQuery( 'head' ).find( '#' + scriptID ).length ) {
						jQuery( 'head' ).first().append( '<script id="' + scriptID + '">WebFont.load({google:{families:["' + script + '"]},context:FusionApp.previewWindow,active: function(){ jQuery( window ).trigger( "fusion-font-loaded"); },});</script>' );
						return false;
					}
				} else {
					if ( window.WebFont && window.WebFont.load ) {
						WebFont.load( { google: { families: [ script ] } } );
					}
				}

				return true;
			},

			isGoogleFont: function( family ) {
				var isGoogleFont = false;

				_.each( window.awbTypographySelect.webfonts.google, function( font ) {
					if ( font.family === family ) {
						isGoogleFont = true;
					}
				} );

				return isGoogleFont;
			},

			isAdobeFont: function ( family ) {
				var isAdobeFont = false;

				if ( ! window.awbTypographySelect.webfonts || ! window.awbTypographySelect.webfonts.adobe ) {
					return isAdobeFont;
				}

				_.each( window.awbTypographySelect.webfonts.adobe, function( font ) {
					if ( font.family === family ) {
						isAdobeFont = true;
					}
				} );

				return isAdobeFont;
			},

			/** For the font-family string, get the display name. */
			getAdobeDisplayName: function ( family ) {
				var displayName = family;

				if ( ! window.awbTypographySelect.webfonts || ! window.awbTypographySelect.webfonts.adobe ) {
					return displayName;
				}

				_.each( window.awbTypographySelect.webfonts.adobe, function( font ) {
					if ( font.family === family && font.label ) {
						displayName = font.label;
					}
				} );

				return displayName;
			},

			getValidVariant: function( family, variant ) {
				var variants   = window.awbTypographySelect.getVariants( family ),
					isValid    = false,
					hasRegular = false,
					first      = ( ! _.isUndefined( variants[ 0 ] ) && ! _.isUndefined( variants[ 0 ].id ) ) ? variants[ 0 ].id : '400';

				if ( 'string' !== typeof variant || '' === variant ) {
					variant = '400';
				}

				// Variable family, set variant value as same variable.
				if ( -1 !== family.indexOf( 'var(' ) ) {
					return family.replace( '-font-family)', ')' );
				}
				if ( this.isCustomFont( family ) ) {
					return '400';
				}

				_.each( variants, function( v ) {
					if ( variant === v.id ) {
						isValid = true;
					}
					if ( 'regular' === v.id || '400' === v.id || 400 === v.id ) {
						hasRegular = true;
					}
				} );

				if ( isValid ) {
					return variant;
				} else if ( hasRegular ) {
					return '400';
				}
				return first;
			},

			// TODO: refactor this so its easier to lookup.
			getVariants: function( fontFamily ) {
				var variants = false,
					matcherFont;

				// Family is a variable, variant only has that selection.
				if ( -1 !== fontFamily.indexOf( 'var(' ) ) {
					return [
						{
							id: fontFamily.replace( '-font-family)', ')' ),
							label: awbTypoData.strings.global
						}
					];
				}

				if ( window.awbTypographySelect.isCustomFont( fontFamily ) ) {
					return [
						{
							id: '400',
							label: 'Normal 400'
						}
					];
				}

				matcherFont = fontFamily.replace( /["']/g, "'" );

				_.each( window.awbTypographySelect.webfonts.standard, function( font ) {
					if ( fontFamily && ( font.family === fontFamily || font.family === matcherFont ) ) {
						variants = font.variants;
						return font.variants;
					}
				} );

				_.each( window.awbTypographySelect.webfonts.google, function( font ) {
					if ( fontFamily && ( font.family === fontFamily || font.family === matcherFont ) ) {
						variants = font.variants;
						return font.variants;
					}
				} );

				_.each( window.awbTypographySelect.webfonts.adobe, function( font ) {
					if ( fontFamily && ( font.family === fontFamily || font.family === matcherFont ) ) {
						variants = font.variants;
						return font.variants;
					}
				} );
				return variants;
			},

			isCustomFont: function( family ) {
				var isCustom = false;

				// Figure out if this is a google-font.
				_.each( window.awbTypographySelect.webfonts.custom, function( font ) {
					if ( font.family === family ) {
						isCustom = true;
					}
				} );

				return isCustom;
			},

			combineFontFamily: function( typography ) {
				var primary_font = typography['font-family'],
					backup_font = typography['font-backup'];

				// Exit early by returning the fallback font
				// in case no primary-font is defined.
				if ( ! primary_font ) {
					return window.awbTypographySelect.formatFontFamily( backup_font );
				}

				// Exit early returning the primary font
				// in case no fallback font is defined.
				if ( ! backup_font || '' === backup_font ) {
					return window.awbTypographySelect.formatFontFamily( primary_font );
				}

				// Exit early returning the google (primary) font
				// in case primary font is set to use standard font and it's the same as fallback font.
				if ( primary_font === backup_font ) {
					return window.awbTypographySelect.formatFontFamily( primary_font );
				}

				// Return the sum of the font-families properly formatted.
				return window.awbTypographySelect.formatFontFamily( primary_font + ', ' + backup_font );
			},

			formatFontFamily: function( family ) {
				var families = [];

				// Remove quotes and double-quotes.
				// We'll add these back later if they are indeed needed.
				family = family.replace( '"', '' ).replace( '\'', '' );

				if ( ! family  ) {
					return '';
				}

				// If multiple font-families, make sure each-one of them is sanitized separately.
				if ( family.includes( ',' ) ) {
					families = family.split( ',' );

					for ( var i = 0; i < families.length; i++ ) {
						families[ i ] = families[ i ].trim();
						if ( family.includes( ' ' ) ) {
							families[ i ] = '"' + families[ i ] + '"';
						}
					}

					family = families.join( ', ' );
				} else {
					// Add quotes if needed.
					if ( family.includes( ' ' ) ) {
						family = '"' + family + '"';
					}
				}
				return family;
			},

			updateMarkup: function( slug, label ) {
				var $markup = jQuery( this.markup );

				if ( $markup.find( '[data-value="' + slug + '"]' ).length ) {
					$markup.find( '[data-value="' + slug + '"]' ).html( label );
					if ( this.$el.length ) {
						this.$el.find( '[data-value="' + slug + '"]' ).html( label );
					}
				} else {
					$markup.find( '.awb-global-sets' ).append( '<div class="awb-typo-option" data-value="' + slug + '">' + label + '</div>' );
					if ( this.$el.length ) {
						this.$el.find( '.awb-global-sets' ).append( '<div class="awb-typo-option" data-value="' + slug + '">' + label + '</div>' );
					}
				}

				this.markup = $markup[0].outerHTML;
				$markup.remove();
			},

			removeFromMarkup: function( slug ) {
				var $markup = jQuery( this.markup );

				$markup.find( '[data-value="' + slug + '"]' ).remove();
				if ( this.$el.length ) {
					this.$el.find( '[data-value="' + slug + '"]' ).remove();
				}

				this.markup = $markup[0].outerHTML;
				$markup.remove();
			},

			/**
			 * Gets the webfonts via AJAX.
			 *
			 * @since 2.0.0
			 * @return {void}
			 */
			getWebFonts: function() {
				var self = this;

				if ( self.webfonts && self.webfontsArray ) {
					return;
				}

				// Back-end globals, typography field type.
				if ( 'undefined' !== typeof fusionredux_ajax_script && 'object' === typeof fusionredux_ajax_script.fusion_web_fonts ) {
					self.webfonts = fusionredux_ajax_script.fusion_web_fonts;
					self.setFontArrays();
					return;
				}

				if ( 'undefined' !== typeof fusionAppConfig && 'object' === typeof fusionAppConfig.fusion_web_fonts ) {
					self.webfonts = fusionAppConfig.fusion_web_fonts;
					self.setFontArrays();
					return;
				}

				if ( false !== self.webfontRequest ) {
					return self.webfontRequest;
				}

				return self.webfontRequest = jQuery.post( ajaxurl, { action: 'fusion_get_webfonts_ajax' }, function( response ) { // eslint-disable-line no-return-assign
					self.webfonts = JSON.parse( response );
					self.setFontArrays();
				} );
			},
			setFontArrays: function() {
				var self = this;

				// Create web font array.
				self.webfontsArray = [];
				_.each( self.webfonts.google, function( font ) {
					self.webfontsArray.push( font.family );
				} );
				self.webfontsGoogleArray = self.webfontsArray;

				self.webfontsStandardArray = [];
				_.each( self.webfonts.standard, function( font ) {
					self.webfontsArray.push( font.family );
					self.webfontsStandardArray.push( font.family );
				} );
			},

			getFontData: function( fontArrays ) {
				var self     = this,
					data     = [],
					fontData = {
						standard: [],
						adobe: [],
						google: [],
						custom: []
					};

				if ( 'undefined' !== typeof fontArrays && false !== this.fontArrays ) {
					return this.fontArrays;
				}

				if ( false !== this.fontData ) {
					return this.fontData;
				}

				if ( ! this.webfonts ) {
					return {};
				}

				// Format standard fonts as an array.
				if ( ! _.isUndefined( this.webfonts.standard ) ) {
					_.each( this.webfonts.standard, function( font ) {
						fontData.standard.push( {
							id: font.family.replace( /&quot;/g, '&#39' ),
							text: font.label
						} );
					} );
				}

				// Format Adobe Fonts as an array.
				if ( ! _.isUndefined( this.webfonts.adobe ) ) {
					_.each( this.webfonts.adobe, function( font ) {
						fontData.adobe.push( {
							id: font.family,
							text: font.label
						} );
					} );
				}

				// Format google fonts as an array.
				if ( ! _.isUndefined( this.webfonts.google ) ) {
					_.each( this.webfonts.google, function( font ) {
						fontData.google.push( {
							id: font.family,
							text: font.label
						} );
					} );
				}

				// Format custom fonts as an array.
				if ( ! _.isUndefined( this.webfonts.custom ) ) {
					_.each( this.webfonts.custom, function( font ) {
						if ( font.family && '' !== font.family ) {
							fontData.custom.push( {
								id: font.family.replace( /&quot;/g, '&#39' ),
								text: font.label
							} );
						}
					} );
				}

				if ( fontData.custom[ 0 ] ) {
					data.push( { text: awbTypoData.strings.custom_fonts, children: fontData.custom } );
				}

				if ( fontData.adobe[ 0 ] ) {
					data.push( { text: awbTypoData.strings.adobe_fonts, children: fontData.adobe } );
				}

				data.push( { text: awbTypoData.strings.standard_fonts, children: fontData.standard } );
				data.push( { text: awbTypoData.strings.google_fonts,   children: fontData.google } );

				this.fontArrays = fontData;
				this.fontData   = data;

				if ( 'undefined' !== typeof fontArrays ) {
					return this.fontArrays;
				}
				return this.fontData;
			},

			create: function( $global, $location ) {
				var self = this,
					value;

				$global.addClass( 'open' );

				// Another is open, remove it.
				if ( false !== this.$el && this.$el.length ) {
					this.destroy();
				}

				// Alter where the selection is going to be placed.
				if ( 'undefined' === typeof $location ) {
					$global.after( this.markup );
					this.$el = $global.next();
				} else {
					this.$location = $location;
					$location.prepend( this.markup ).addClass( 'awb-selecting-global' );
					this.$el = $location.children( '.awb-picker-global' );
				}

				// Set reference to where element is.
				this.$global = $global;
				this.$input  = this.$global.closest( '.input-wrapper, .fusion-builder-option' ).find( '[data-subset]' );

				// If a variable is being used, highlight it in the selection.
				if ( this.$global.hasClass( 'active' ) ) {
					value = this.$input.val();
					if ( 'string' === typeof value && '' !== value ) {
						this.$el.find( '.awb-typo-option[data-value="' + this.stripValue( value ) + '"]' ).addClass( 'active' );
					}
				}

				// Add click listener for the closing of this..
				this.$el.find( '.close-globals' ).on( 'click', function() {
					self.destroy();
				} );

				// Deep link to Global Color Palette.
				this.$el.find( '.edit-globals' ).on( 'click', function( event ) {
					event.preventDefault();
					var $element = jQuery( event.currentTarget ),
						cogType  = jQuery( event.currentTarget ).attr( 'data-cog-type' );

					if ( 'live' === cogType && FusionApp.sidebarView ) {
						FusionApp.sidebarView.shortcutClick( $element );
					} else if ( 'backend' === cogType ) {
						window.open( awbPalette.goLink + '#typography_sets', '_blank' );
					} else if ( 'function' === typeof fusionOpenOption ) {
						fusionOpenOption( 'typography_sets' );
					}
				} );

				// Add click listener for the selection.
				this.$el.find( '.awb-typo-option' ).on( 'click', function() {
					var typoSlug = jQuery( this ).attr( 'data-value' ),
						$input,
						subset;

					if ( self.$global.hasClass( 'awb-quick-set' ) ) {
						$input = self.$global.closest( '.awb-typography-container, .awb-typography, .fusion-builder-option' ).find( '.awb-contains-global [data-subset]' );
						$input.each( function() {
							subset = jQuery( this ).attr( 'data-subset' );
							self.setValue( jQuery( this ), 'var(--awb-' + typoSlug + '-' + subset + ')', typoSlug );
						} );
					} else if ( 1 < self.$input.length ) {
						self.$input.each( function() {
							subset = jQuery( this ).attr( 'data-subset' );
							self.setValue( jQuery( this ), 'var(--awb-' + typoSlug + '-' + subset + ')', typoSlug );
						} );
					} else {
						subset = self.$input.attr( 'data-subset' );
						self.setValue( self.$input, 'var(--awb-' + typoSlug + '-' + subset + ')', typoSlug );
					}

					// Close it.
					self.destroy();
				} );
			},

			setValue: function( $element, value, slug ) {
				// The variant input is select, which does not contain the value of the variable.
				// And also causes a JS error when triggering change if the value is null.
				if ( value.includes( '-variant' ) ) {
					return;
				}

				$element.val( value ).trigger( 'change' );
			},

			destroy: function() {
				this.$global.removeClass( 'open' );
				this.$el.remove();
				if ( false !== this.$location ) {
					this.$location.removeClass( 'awb-selecting-global' );
				}
				this.$global   = false;
				this.$el       = false;
				this.$input    = false;
				this.$location = false;
			},

			getFallbackObject: function() {
				var label = 'Unknown Font';
				if ( awbTypographySelect.stringMap && awbTypographySelect.stringMap.unknown_font ) {
					label = awbTypographySelect.stringMap.unknown_font;
				}

				return {
					'label': label,
					'font-family': label,
					'font-size': '16px',
					'font-backup': '',
					'variant': '400',
					'font-weight': '400',
					'font-style': '',
					'line-height': '1.1',
					'letter-spacing': '0',
					'text-transform': 'none'
				}
			},

			stripValue: function( value ) {
				if ( 'string' !== typeof value ) {
					return '';
				}
				return value.replace( 'var(--awb-', '' ).replace( '-font-size)', '' ).replace( '-font-weight)', '' ).replace( '-font-style)', '' ).replace( '-font-family)', '' ).replace( '-line-height)', '' ).replace( '-letter-spacing)', '' ).replace( '-text-transform)', '' );
			},

			getVarString: function( input, subset ) {
				var slug;
				if ( '' === input ) {
					return '';
				}
				slug = this.stripValue( input );

				if ( '' === subset ) {
					return 'var(--awb-' + slug + ')';
				}
				return 'var(--awb-' + slug + '-' + subset + ')';
			},

			getFontWeightFromVariant: function( variant ) {
				if ( 'string' !== typeof variant ) {
					return '400';
				}
				if ( 'regular' === variant ) {
					return '400'
				}
				return variant.replace( 'italic', '' );
			},

			getFontStyleFromVariant: function( variant ) {
				if ( 'string' === typeof variant && -1 !== variant.indexOf( 'italic' ) ) {
					return 'italic';
				}
				return '';
			},

			/**
			 * Check if a css value is a css typography variable.
			 *
			 * @since 3.6
			 * @param {string} value
			 */
			isTypographyCssVar: function( value ) {
				if ( ! value.includes( 'var(' ) ) {
					return false;
				}

				if ( ! value.includes( 'typography' ) ) {
					return false;
				}

				return true;
			},

			/**
			 * Get the real value of a css variable.
			 *
			 * @since 3.6
			 * @param {string} variable
			 * @return {string}
			 */
			getRealValue: function( cssVar ) {
				var varName = cssVar.replace( 'var(', '' ).replace( ')', '' );
				return jQuery( 'body' ).css( varName );
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
		window.awbTypographySelect.init();

	} );
}( jQuery ) );
