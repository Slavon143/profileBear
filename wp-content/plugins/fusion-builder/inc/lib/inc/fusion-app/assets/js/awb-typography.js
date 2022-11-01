( function( jQuery ) {

	'use strict';

	jQuery( document ).ready( function() {

		window.AwbTypography = function( el, parentScope ) {
			var self = this;
			this.$option     = jQuery( el );
			this.$el         = this.$option.find( '.awb-typography' );
			this.isTypoSet   = this.$el.parent().hasClass( 'awb-typo-set-content' );
			this.$quickSet   = this.$option.find( '.option-global-typography' ).length ? this.$option.find( '.option-global-typography' ) : false;
			this.parentScope = parentScope;
			this.hasBackup   = this.$el.find( '.font-backup' ).length;
			this.optionId    = 'undefined' === typeof this.$option.attr( 'data-option-id' ) ? this.$option.attr( 'data-id' ) : this.$option.attr( 'data-option-id' );
			this.optionMap   = this.getOptionMap();
			this.location    = this.getLocation();
			if ( 'live' === this.location ) {
				this.params = 'EO' === this.parentScope.type ? this.parentScope.model.get( 'params' ) : FusionApp.settings;
			}
			this.init();
		}

		AwbTypography.prototype.init = function() {
			var self = this,
				fontFamily,
				fontVariant;

			if ( this.$el.find( '.family-selection' ).length ) {
				this.renderFontSelector();
			}

			// Text transform if it exists.
			if ( this.$el.find( '.radio-button-set' ).length ) {
				this.$el.find( '.buttonset-item' ).on( 'click', this.radioSelect.bind( this ) );
				this.$el.find( '.button-set-value' ).on( 'change', this.radioChange.bind( this ) );
			}

			if ( ! this.isTypoSet ) {

				// Listen for change on fields linked as global.
				this.$el.find( '[data-subset]' ).on( 'change', this.optionChange.bind( this ) );

				// Global icon, add markup for selecting a set.
				this.$el.find( '.awb-global' ).on( 'click', this.toggleSelect.bind( this ) );

				// Unlink from global on label click.
				this.$el.find( '.awb-global-label' ).on( 'click', this.labelClick.bind( this ) );
			}

			// Listen for quick set click.
			if ( this.$quickSet ) {
				this.$quickSet.on( 'click', this.quickSelect.bind( this ) );
			}

			// Fusion-change event is needed for listening on color change.
			this.$el.on( 'change keyup fusion-change', this.refreshTypographyPreview.bind( this ) );

			this.refreshAllStates = this.refreshAllStates.bind( this )
			jQuery( document ).on( 'awbTypoNew awbTypoUpdate awbTypoDeleted', this.refreshAllStates );

			this.setInitialState();

			this.$el.on( 'remove', this.destroy.bind( this ) );
			this.refreshTypographyPreview();

			// Load the initial font. Needed for preview.
			fontFamily  = this.getValue( 'font-family' );
			fontVariant = this.getValue( 'variant' );
			window.awbTypographySelect.webFontLoad( fontFamily, fontVariant );
		};

		AwbTypography.prototype.getLocation = function() {
			return 'undefined' === typeof FusionApp ? 'redux' : 'live';
		};

		AwbTypography.prototype.setInitialState = function() {
			var self = this;
			this.$el.find( '.awb-global' ).each( function() {
				var $wrapper = jQuery( this ).closest( '.input-wrapper' ),
					$input   = $wrapper.find( '[data-subset]' );

				self.setState( $input );
			} );
		};

		AwbTypography.prototype.optionChange = function( event ) {
			this.setState( jQuery( event.currentTarget ) );
		};

		AwbTypography.prototype.radioSelect = function( event ) {
			var $target    = jQuery( event.currentTarget ),
				$container = $target.closest( '.radio-button-set' );

			event.preventDefault();

			$container.find( '.button-set-value' ).val( $target.data( 'value' ) ).trigger( 'change' );
		};

		AwbTypography.prototype.radioChange = function( event ) {
			var $target    = jQuery( event.currentTarget ),
				$container = $target.closest( '.radio-button-set' );

			$container.find( '.ui-state-active' ).removeClass( 'ui-state-active' );
			$container.find( '.buttonset-item[data-value="' + $target.val() + '"]' ).addClass( 'ui-state-active' );
		};

		AwbTypography.prototype.toggleSelect = function( event ) {
			var $global  = jQuery( event.currentTarget );

			if ( $global.next( '.awb-picker-global' ).length ) {
				awbTypographySelect.destroy();
			} else {
				awbTypographySelect.create( $global, this.$el );
			}
		};

		AwbTypography.prototype.quickSelect = function( event ) {
			if ( this.$el.find( '> .awb-picker-global' ).length ) {
				awbTypographySelect.destroy();
			} else {
				awbTypographySelect.create( this.$quickSet, this.$el );
			}
		};

		AwbTypography.prototype.refreshAllStates = function() {
			var self = this;

			this.$el.find( '.awb-global' ).each( function() {
				var $input = jQuery( this ).closest( '.input-wrapper' ).find( '[data-subset]' );
				self.setState( $input );
			} );
		}

		AwbTypography.prototype.setState = function( $input ) {
			var subset          = $input.attr( 'data-subset' ),
				$wrapper        = $input.closest( '.input-wrapper' ),
				$global         = $wrapper.find( '.awb-global' ),
				value           = $input.val(),
				isInvalidGlobal = false,
				slug            = window.awbTypographySelect.stripValue( value ),
				label,
				fontObject,
				stringValue;

			// If its a variable value.
			if ( 'string' === typeof value && '' !== value && value.includes( 'var(--' ) ) {
				fontObject = window.awbTypographySelect.typoData[ slug ];

				// Either this or x so they can remove.
				$global.addClass( 'active' );

				if ( 'undefined' === typeof fontObject ) {
					isInvalidGlobal = true;
					fontObject = window.awbTypographySelect.getFallbackObject();
				}

				label = $wrapper.find( '.awb-global-label' );

				// Would get var from value, then look it up to get label, or display font size from label.
				if ( 'undefined' !== typeof fontObject[ subset ] ) {
					value = stringValue = fontObject[ subset ];

					// If its text transform, get label form of value.
					if ( 'text-transform' === subset && 'string' === typeof window.awbTypographySelect.stringMap[ value ] ) {
						stringValue = window.awbTypographySelect.stringMap[ value ];
					}

					label.attr( 'aria-label', fontObject.label ).attr( 'title', fontObject.label ).attr( 'data-value', value ).html( stringValue );

					// If its Adobe font family, then display the font label instead of font-family
					if ( 'font-family' === subset && window.awbTypographySelect.isAdobeFont( stringValue ) ) {
						label.html( window.awbTypographySelect.getAdobeDisplayName( stringValue ) );
					}
				} else {
					label.attr( 'aria-label', false ).removeAttr( 'title' ).attr( 'data-value', false ).html( fontObject.label );
				}

				if ( isInvalidGlobal ) {
					label.addClass( 'awb-global-label-error' );
				} else {
					label.removeClass( 'awb-global-label-error' );
				}

				// Set a class which toggles the label to be visible and hide input.
				$wrapper.addClass( 'awb-global-set' );
			} else {
				$global.removeClass( 'active' );
				$wrapper.removeClass( 'awb-global-set' );

				// Additionally unlock backup and variant.
				if ( 'font-family' === subset ) {
					this.$el.find( '.fusion-builder-typography, .fusion-font-backup-wrapper' ).removeClass( 'awb-global-set' );


					if ( 'string' === typeof slug && awbTypographySelect.isAdobeFont( slug ) ) {
						label = $wrapper.find( '.fusion-select-preview' );
						label.html( window.awbTypographySelect.getAdobeDisplayName( slug ) );
					}
				}
			}
		};

		AwbTypography.prototype.labelClick = function( event ) {
			var $label   = jQuery( event.currentTarget ),
				$wrapper = $label.closest( '.input-wrapper' ),
				$input   = $wrapper.find( '[data-subset]' ),
				value    = 'undefined' !== typeof $label.attr( 'data-value' ) && false !== $label.attr( 'data-value' ) ? $label.attr( 'data-value' ) : '';

			// Highlight the correct value.
			if ( 'text_transform' === $input.attr( 'data-subset' ) ) {
				$wrapper.find( '.ui-state-active' ).removeClass( 'ui-state-active' );

				// If it has default option, set that, otherwise set from font.
				if ( $wrapper.find( '.ui-button[data-value=""]' ).length ) {
					$wrapper.find( '.ui-button[data-value=""]' ).addClass( 'ui-state-active' );
					value = '';
				} else {
					$wrapper.find( '.ui-button[data-value="' + value + '"]' ).addClass( 'ui-state-active' );
				}
			}

			// Set the value to the input.
			$input.val( value ).trigger( 'change' );

			// Focus so they can type value.
			if ( $input.is( 'input' ) ) {
				$input.focus();
			}
		};

		AwbTypography.prototype.getOptionMap = function() {
			var optionMap = {};
			this.$el.find( '[data-subset]' ).each( function() {
				optionMap[ jQuery( this ).attr( 'data-subset' ) ] = jQuery( this ).attr( 'name' );
			} );
			return optionMap;
		};

		AwbTypography.prototype.renderFontSelector = function() {
			var self          = this,
				$selectField  = this.$el.find( '.family-selection .fusion-select-field' ),
				data          = window.awbTypographySelect.getFontData(),
				fontFamily    = this.getValue( 'font-family' ).replace( /'/g, '"' );

			this.$familySelect = $selectField.awbSelect( {
				fieldId: self.optionId,
				fieldName: 'font-family',
				fieldValue: fontFamily,
				data: data
			} );

			this.$familySelect.find( '.fusion-select-option-value' ).on( 'change', function() {
				var newFamily = jQuery( this ).val();

				// Re-render dependent elements on-change.
				self.renderFamilyDependents( newFamily );

				// Load new font using the webfont-loader.
				window.awbTypographySelect.webFontLoad( jQuery( this ).val(), self.getValue( 'variant' ) );
			} );

			// Render dependent choices.
			setTimeout( function() {
				self.renderFamilyDependents( fontFamily );
			}, 70 );
		};

		AwbTypography.prototype.renderFamilyDependents = function( fontFamily ) {
			this.renderVariantSelector( fontFamily );

			if ( this.hasBackup ) {
				this.renderBackupFontSelector( fontFamily );
			}
		};

		AwbTypography.prototype.renderVariantSelector = function( fontFamily ) {
			var self            = this,
				variants        = window.awbTypographySelect.getVariants( fontFamily ),
				data            = [],
				$variant        = this.$el.find( '[data-subset="variant"]' ),
				$variantWrapper = $variant.closest( '.input-wrapper' ),
				variant         = self.getValue( 'variant' ),
				params;

			if ( false === variants ) {
				$variantWrapper.hide();
			}

			if ( '' === fontFamily ) {

				// Switched to empty family, hide variant selection.
				$variantWrapper.hide();
				return;
			}

			// If we got this far, show the selector.
			$variantWrapper.show();

			_.each( variants, function( scopedVariant ) {

				if ( scopedVariant.id && 'italic' === scopedVariant.id ) {
					scopedVariant.id = '400italic';
				}

				data.push( {
					id: scopedVariant.id,
					text: scopedVariant.label
				} );
			} );

			variant = window.awbTypographySelect.getValidVariant( fontFamily, variant );

			// Clear old values.
			$variant.empty();

			_.each( data, function( font ) {
				var selected = font.id === variant ? 'selected' : '';
				$variant.append( '<option value="' + font.id + '" ' + selected + '>' + font.text + '</option>' );
			} );

			// When the value changes.
			$variant.on( 'fusion.typo-variant-loaded change', function() {

				// Load new font using the webfont-loader.
				window.awbTypographySelect.webFontLoad( self.getValue( 'font-family' ), jQuery( this ).val() );

				self.maybeSetSplitVariant( jQuery( this ).val() );
			} );

			// Set the variant value.
			$variant.val( variant );

			// Only trigger change to ensure default 400 is saved in live builder.
			if ( 'live' === this.location ) {
				$variant.trigger( 'change' );
			}
		};

		AwbTypography.prototype.maybeSetSplitVariant = function( variant ) {
			var weight,
				style;

			// Only need this for back-end globals where params are not set.
			if ( 'redux' === this.location ) {
				if ( 'string' === typeof variant && '' !== variant ) {
					style  = -1 !== variant.indexOf( 'italic' ) ? 'italic' : '';
					weight = variant.replace( 'italic', '' );
				} else {
					style  = '';
					weight = '400';
				}
				this.$el.find( '[data-subset="font-weight"]' ).val( weight );
				this.$el.find( '[data-subset="font-style"]' ).val( style );
			}
		};

		AwbTypography.prototype.renderBackupFontSelector = function( fontFamily ) {
			var self          = this,
				$option       = this.$el.find( '.font-backup' ),
				data          = window.awbTypographySelect.getFontData( true ),
				standardFonts = data.standard,
				$awbSelect; // eslint-disable-line no-unused-vars

			$awbSelect = $option.find( '.fusion-select-field' ).awbSelect( { // eslint-disable-line no-unused-vars
				fieldId: self.optionId,
				fieldName: 'font-backup',
				data: [ { text: 'Standard Fonts', children: standardFonts } ]
			} );

			// Hide if we're not on a google-font and early exit.
			if ( false === window.awbTypographySelect.isGoogleFont( fontFamily ) && false === window.awbTypographySelect.isAdobeFont( fontFamily ) ) {
				$option.hide();
				$awbSelect.find( '.fusion-select-option-value' ).val( '' );
				return;
			}

			$option.show();
		};

		AwbTypography.prototype.getValue = function( option ) {
			var parts,
				fontWeight  = '',
				fontStyle   = '',
				fontVariant = '';

			if ( 'color' === option ) {
				return this.$el.find( '.awb-color-picker' ).val();
			}

			// If we have params get the value from there rather than DOM.
			if ( 'live' === this.location ) {
				if ( 'undefined' !== typeof this.optionMap[ option ] ) {
					if ( -1 !== this.optionMap[ option ].indexOf( '[' ) ) {
						parts = this.optionMap[ option ].split( '[' );
						parts[ 0 ] = parts[ 0 ].replace( ']', '' );
						parts[ 1 ] = parts[ 1 ].replace( ']', '' );
						if ( parts.length === 3 ) {
							parts[ 2 ] = parts[ 2 ].replace( ']', '' );
						}

						if ( 'variant' === option ) {
							fontWeight = '';
							fontStyle  = '';

							if ( 'object' === typeof this.params[ parts[ 0 ] ] ) {
								if ( parts.length === 3 && 'object' === typeof this.params[ parts[ 0 ] ][ parts[ 1 ] ] ) {
									fontWeight  = this.params[ parts[ 0 ] ][ parts[ 1 ] ][ 'font-weight' ];
									fontStyle   = this.params[ parts[ 0 ] ][ parts[ 1 ] ][ 'font-style' ];
									fontVariant = this.params[ parts[ 0 ] ][ parts[ 1 ] ][ 'variant' ];
								} else {
									fontWeight  = this.params[ parts[ 0 ] ][ 'font-weight' ];
									fontStyle   = this.params[ parts[ 0 ] ][ 'font-style' ];
									fontVariant = this.params[ parts[ 0 ] ][ 'variant' ];
								}
							}

							if ( fontVariant ) {
								return fontVariant
							} else {
								fontWeight = ( typeof fontWeight === 'string' ? fontWeight : '' );
								fontStyle  = ( typeof fontStyle === 'string' ? fontStyle : '' );

								return fontWeight + fontStyle;
							}
						}

						if ( 'object' === typeof this.params[ parts[ 0 ] ] && 'undefined' !== typeof this.params[ parts[ 0 ] ][ parts[ 1 ] ] ) {
							if ( parts.length === 3 && 'undefined' !== typeof this.params[ parts[ 0 ] ][ parts[ 1 ] ][ parts[ 2 ] ] ) {
								return this.params[ parts[ 0 ] ][ parts[ 1 ] ][ parts[ 2 ] ];
							} else {
								return this.params[ parts[ 0 ] ][ parts[ 1 ] ];
							}
						}
					} else if ( 'undefined' !== typeof this.params[ this.optionMap[ option ] ] ) {
						return this.params[ this.optionMap[ option ] ];
					}
					this.$el.find( '[data-subset="' + option + '"]' ).attr( 'data-default' );
				}
			} else {
				if ( 'variant' === option ) {
					if ( 'string' === typeof this.$el.find( '[data-subset="variant"]' ).val() && '' !== this.$el.find( '[data-subset="variant"]' ).val() ) {
						return this.$el.find( '[data-subset="variant"]' ).val();
					}
					if ( 'string' === typeof this.$el.find( '[data-subset="variant"]' ).attr( 'data-value' ) && '' !== this.$el.find( '[data-subset="variant"]' ).attr( 'data-value' ) ) {
						return this.$el.find( '[data-subset="variant"]' ).attr( 'data-value' );
					}
					return this.$el.find( '[data-subset="font-weight"]' ).val() + this.$el.find( '[data-subset="font-style"]' ).val();
				}
				return this.$el.find( '[data-subset="' + option + '"]' ).val();
			}
			return '';
		};

		AwbTypography.prototype.refreshTypographyPreview = function() {
			var $previewContainer = this.$option.find( '.typography-preview' ),
				fontFamily        = this.getValue( 'font-family' ),
				fontStyle         = this.getValue( 'font-style' ),
				fontWeight        = this.getValue( 'font-weight' ),
				fontSize          = this.getValue( 'font-size' ),
				lineHeight        = this.getValue( 'line-height' ),
				letterSpacing     = this.getValue( 'letter-spacing' ),
				color             = this.getValue( 'color' ),
				$fontSelection    = this.$option.find( '.family-selection' ),
				slug;


			// When a global is modified, the font-weight and style fields are not actually updated, so we take them from globals.
			if ( $fontSelection.hasClass( 'awb-global-set' ) ) {
				slug = window.awbTypographySelect.stripValue( $fontSelection.find( 'input[data-subset="font-family"]' ).val() );
				if ( awbTypographySelect.typoData[ slug ] ) {
					fontStyle  = awbTypographySelect.getVarString( slug, 'font-style' );
					fontWeight = awbTypographySelect.getVarString( slug, 'font-weight' );
				}
			}

			$previewContainer.css( {
				fontFamily: fontFamily,
				fontStyle: fontStyle,
				fontWeight: fontWeight,
				fontSize: fontSize,
				lineHeight: lineHeight,
				letterSpacing: letterSpacing,
				color: color,
			} );
		}

		AwbTypography.prototype.destroy = function() {
			jQuery( document ).off( 'awbTypoNew awbTypoUpdate awbTypoDeleted', this.refreshAllStates );
		};

	} );
}( jQuery ) );

// When a global value gets added/modified/deleted, update the CSS vars.
( function( jQuery ) {
	'use strict';

	var AdminCSSVars = [];
	var LiveEditorCSSVars = [];

	// When a global palette color changes, also change the live editor global CSS vars.
	jQuery( function() {
		jQuery( document ).on( 'awbTypoNew awbTypoUpdate awbTypoDeleted', updateLiveEditorVars );
		jQuery( document ).on( 'awbTypoNew awbTypoUpdate awbTypoDeleted', updateAdminVars );
	} );

	/**
	 * Update the admin document style, with the CSS variables from the global palette.
	 *
	 * @since 3.6
	 */
	 function _updateAdminVars() {
		removeAllCSSVars( document.documentElement.style, AdminCSSVars );
		addAllCSSVars( document.documentElement.style, AdminCSSVars );
	};
	var updateAdminVars = _.debounce( _updateAdminVars, 200 );

	/**
	 * Update the live editor body style, with the CSS variables from the global typography.
	 *
	 * @since 3.6
	 */
	function _updateLiveEditorVars() {
		var styleObject = getLiveEditorDocumentStyle();

		if ( ! styleObject ) {
			return;
		}

		removeAllCSSVars( styleObject, LiveEditorCSSVars );
		addAllCSSVars( styleObject, LiveEditorCSSVars );
	};
	var updateLiveEditorVars = _.debounce( _updateLiveEditorVars, 200 );

	/**
	 * Remove all the CSS variables from the live editor body style, that comes from global typography.
	 *
	 * @since 3.6
	 */
	function removeAllCSSVars( styleObject, cssVarsCache ) {
		var needToOverwriteDeletedColor;

		cssVarsCache.forEach( function( cssVar ) {
			styleObject.removeProperty( cssVar.varName );

			// Overwrite with an empty string, if the global was removed.
			needToOverwriteDeletedColor = awbTypoData.data && ! awbTypoData.data[ cssVar.slug ];
			if ( needToOverwriteDeletedColor ) {
				styleObject.setProperty( cssVar.varName, '' );
			}
		} );

		cssVarsCache = [];
	};

	/**
	 * Add all the CSS variables that comes from global palette to the live editor body style.
	 *
	 * @since 3.6
	 */
	function addAllCSSVars( styleObject, cssVarsCache ) {
		var typoSlug,
			typographyOptions,
			cssVarName,
			cssVarValue,
			typoData = awbTypoData.data,
			subsets = [
			'font-family',
			'font-size',
			'font-weight',
			'font-style',
			'line-height',
			'letter-spacing',
			'text-transform',
		];

		if ( 'object' !== typeof typoData ) {
			return;
		}

		for ( typoSlug in typoData ) {
			typographyOptions = typoData[ typoSlug ];
			for ( var i = 0; i < subsets.length; i++ ) {
				cssVarName  = '--awb-' + typoSlug + '-' + subsets[ i ];
				cssVarValue = typographyOptions[ subsets[ i ] ];

				if ( 'font-style' === subsets[ i ] && '' === cssVarValue ) {
					cssVarValue = 'normal';
				} else if ( 'font-family' === subsets[ i ] ) {
					cssVarValue = window.awbTypographySelect.combineFontFamily( typographyOptions );
				} else if ( 'letter-spacing' === subsets[ i ] && window.fusionSanitize && window.fusionSanitize.maybe_append_px ) {
					cssVarValue = window.fusionSanitize.maybe_append_px( cssVarValue );
				} else if ( 'font-weight' === subsets[ i ] && window.fusionSanitize && window.fusionSanitize.font_weight_no_regular ) {
					cssVarValue = window.fusionSanitize.font_weight_no_regular( cssVarValue );
				}

				if ( cssVarValue ) {
					styleObject.setProperty( cssVarName, cssVarValue );
					cssVarsCache.push( { varName: cssVarName, slug: typoSlug } );
				}
			}

			window.awbTypographySelect.webFontLoad( typographyOptions['font-family'], typographyOptions['variant'] );
		}
	};

	function getLiveEditorDocumentStyle() {
		var liveEditorIframe = document.getElementById( 'fb-preview' );
		if ( liveEditorIframe && liveEditorIframe.contentWindow && liveEditorIframe.contentWindow.document ) {
			return liveEditorIframe.contentWindow.document.documentElement.style;
		}

		return null;
	};

}( jQuery ) );
