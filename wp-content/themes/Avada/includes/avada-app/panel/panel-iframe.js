/* global FusionApp, originalOptionName, fusionAppConfig, fusionSanitize, FusionPageBuilder, fusionOptionNetworkNames, fusionReturnMediaQuery */
/* jshint -W098, -W117, -W024 */
/* eslint no-unused-vars: 0 */
/* eslint max-depth: 0 */
( function() {

	/**
	 * Builder Container View.
	 *
	 * @since 6.0
	 */
	window.avadaPanelIFrame = {

		/**
		 * All the fields, flattened.
		 *
		 * @since 6.0
		 */
		fields: {},

		/**
		 * An object containing field-triggering dependencies.
		 *
		 * @since 6.0
		 */
		fieldOutputDependencies: {},

		/**
		 * Apply a callback to an option's value.
		 *
		 * @since 6.0
		 * @param {mixed}  value - The value.
		 * @param {string} callback - The name of the callback function.
		 * @param {mixed}  args - Arguments to pass-on to the callback function.
		 * @return {mixed}            The value after it's been passed through the callback.
		 */
		applyCallback: function( value, callback, args ) {
			args = args || false;
			if ( _.isFunction( FusionApp.callback[ callback ] ) ) {
				return FusionApp.callback[ callback ]( value, args );
			} else if ( _.isFunction( window[ callback ] ) ) {
				return window[ callback ]( value, args );
			} else if ( _.isFunction( fusionSanitize[ callback ] ) ) {
				return fusionSanitize[ callback ]( value, args );
			} else if ( _.isFunction( FusionPageBuilder.Callback.prototype[ callback ] ) ) {
				return FusionPageBuilder.Callback.prototype[ callback ]( value, args );
			}
			return value;
		},

		/**
		 * Apply refresh JS callback.
		 *
		 * @since 6.0
		 * @param {Object} partials - The refresh arguments.
		 * @param {mixed}  value    - The value.
		 * @return {boolean} - Whether or not the tests have passed.
		 */
		applyRefreshCallbacks: function( partials, value ) { // jshint ignore:line
			var self         = this,
				passed       = true,
				initialValue = self.getValueClone( value );

			// Apply callbacks.
			if ( 'undefined' !== typeof partials ) {
				_.each( partials, function( partial ) {

					// Skip if callback is not defined.
					if ( 'undefined' === typeof partial.js_callback ) {
						return false;
					}

					partial.js_callback[ 1 ] = ( _.isUndefined( partial.js_callback[ 1 ] ) ) ? '' : partial.js_callback[ 1 ];
					if ( ! self.applyCallback( initialValue, partial.js_callback[ 0 ], partial.js_callback[ 1 ] ) ) {
						passed = false;
					}
				} );
			}

			return passed;
		},

		/**
		 * Triggers a partial refresh on the preview iframe.
		 *
		 * @param {string} id - The setting ID.
		 * @param {Object} partials - The partial-refresh arguments.
		 * @param {mixed}  value - The value.
		 * @param {string} cid - The model CID.
		 * @return {void}
		 */
		partialRefresh: function( id, partials, value, cid ) { // jshint ignore:line
			var self      = this,
				postData  = FusionApp.getAjaxData( 'fusion_app_partial_refresh' ),
				$element;

			_.each( partials, function( partial, key ) {
				var skip = false;
				if ( partial.skip_for_template ) {
					_.each( partial.skip_for_template, function( overrideSkip ) {
						if ( FusionApp.data.template_override[ overrideSkip ] ) {
							skip = true;
						}
					} );
					if ( skip ) {
						delete partials[ key ];
					}
				}
			} );

			if ( _.isEmpty( partials ) ) {
				return;
			}

			// Add loader.
			if ( 'undefined' !== typeof partials ) {
				_.each( partials, function( partial ) {
					$element = jQuery( '#fb-preview' ).contents().find( 'body' ).find( partial.selector );
					if ( $element.length ) {
						$element.append( '<div id="fusion-loader"><span class="fusion-slider-loading"></span></div>' );
					} else {
						$element = jQuery( '#fb-preview' ).contents().find( 'head' ).find( partial.selector );
						if ( $element.length ) {
							jQuery( 'body' ).append( '<div id="fusion-loader"><span class="fusion-slider-loading"></span></div>' );
						}
					}
				} );

				// Add loader on option.
				jQuery( 'li[data-option-id="' + id + '"]' ).addClass( 'partial-refresh-active' );
			}

			postData.partials = partials;

			jQuery.ajax( {
				type: 'POST',
				url: fusionAppConfig.ajaxurl,
				dataType: 'json',
				data: postData,

				success: function( output ) {
					_.each( output, function( content, scopedID ) {
						var ariaLabel = partials[ scopedID ].aria_label || false,
							successTriggerEvents = 'string' === typeof partials[ scopedID ].success_trigger_event ? partials[ scopedID ].success_trigger_event.split( ' ' ) :  partials[ scopedID ].success_trigger_event;

						content  = FusionApp.removeScripts( content, cid );
						$element = jQuery( '#fb-preview' ).contents().find( 'html' ).find( partials[ scopedID ].selector );
						if ( 'undefined' !== typeof content ) {
							if ( ! content.length ) {
								content = '';
							}
							if ( partials[ scopedID ].after ) {
								$element.after( content );
							} else if ( partials[ scopedID ].container_inclusive ) {
								$element.replaceWith( content );
							} else {
								$element.html( content );
							}
							jQuery( '#fusion-loader' ).remove();
						} else {
							$element.html( '' );
							jQuery( '#fusion-loader' ).remove();
						}

						// Remove loader on option.
						jQuery( 'li[data-option-id="' + id + '"]' ).removeClass( 'partial-refresh-active' );

						if ( partials[ scopedID ].success_trigger_event ) {

							_.each( successTriggerEvents, function( successTriggerEvent ) {

								// Trigger event on parent frame.
								window.dispatchEvent( new Event( successTriggerEvent ) );

								// Trigger event on preview frame.
								window.frames[ 0 ].window.dispatchEvent( new Event( successTriggerEvent ) );

								// If the event is a function, run it.
								if ( 'function' === typeof window[ successTriggerEvent ] ) {
									window[ successTriggerEvent ]();
								}
								if ( 'function' === typeof window.frames[ 0 ].window[ successTriggerEvent ] ) {
									window.frames[ 0 ].window[ successTriggerEvent ]();
								}
							} );
						}

						$element.removeClass( 'fusion-panel-customizable' );
						FusionApp.sidebarView.createEditShortcuts();
					} );

					FusionApp.injectScripts( cid );

					setTimeout( function() {
						jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-partial-' + id );
					}, 100 );
				}
			} );
		},

		/**
		 * Get a clone of a value.
		 * Avoids default JS behavior of creating references instead of clones.
		 *
		 * @since 6.0
		 * @param {mixed} value - The value.
		 * @return {mixed} - Returns a verbatim copy of value.
		 */
		getValueClone: function( value ) {
			return value;
		},

		/**
		 * Gets the value from POs & TOs.
		 * If PO value takes precendence then return PO val, otherwise fallback to TO val.
		 *
		 * @param {string} id - The setting ID.
		 * @param {string} choice - In case the value is an object and we want to get the value of a key in that object.
		 * @return {mixed} - Returns the value.
		 */
		getPoToValue: function( id, choice, toOnly ) {
			var mapKey = this.getGlobalMapKey( id );

			if ( 'undefined' === typeof toOnly ) {
				toOnly = false;
			}

			if ( mapKey ) {
				return this.getPoToValueFromGlobalKey( mapKey, choice, toOnly );
			}
			return null;
		},

		/**
		 * Gets the key from the global Options Map.
		 *
		 * @param {string} id - The setting ID.
		 * @return {string} - Returns the option key from the map.
		 */
		getGlobalMapKey: function( id ) {
			var mapKey = id;

			_.each( [ 'is_home', 'is_tag', 'is_category', 'is_author', 'is_date', 'is_singular_post' ], function( condition ) {
				if ( FusionApp.data[ condition ] ) {
					_.find( fusionOptionNetworkNames, function( item, key ) {
						if ( item[ condition ] && id === item[ condition ] ) {
							mapKey = key;
							return true;
						}
					} );
				}
				if ( mapKey ) {
					return true;
				}
			} );

			return mapKey;
		},

		/**
		 * Gets the value given a key to the option-map.
		 *
		 * @param {string} key - The global option-map key.
		 * @param {string|undefined} choice - Used if we want to get a choice from a value object.
		 * @return {string|Object} - Returns the value.
		 */
		getPoToValueFromGlobalKey: function( key, choice, toOnly ) {
			var value = null,
				skip  = false,
				parts;

			// If we have a TO with that key, get its value.
			if ( FusionApp.settings[ key ] ) {
				value = FusionApp.settings[ key ];
			}

			if ( -1 !== key.indexOf( '[' ) ) {

				// Split the key in parts.
				parts = key.split( '[' );

				// Remove unwanted characters.
				parts[ 0 ] = parts[ 0 ].replace( ']', '' );
				parts[ 1 ] = parts[ 1 ].replace( ']', '' );

				if ( FusionApp.settings[ parts[ 0 ] ] && 'undefined' !== typeof FusionApp.settings[ parts[ 0 ] ][ parts[ 1 ] ] ) {
					value = FusionApp.settings[ parts[ 0 ] ][ parts[ 1 ] ];
				}
			}

			// Return early if we only want TO.
			if ( 'undefined' !== typeof toOnly && toOnly ) {
				return value;
			}

			// Check if we have an option map for this key.
			if ( fusionOptionNetworkNames[ key ] ) {

				// If this is an archive, check if there's an override.
				if ( -1 !== FusionApp.data.postDetails.post_id.toString().indexOf( '-archive' ) ) {

					// Do we have a theme-option defined for this key?
					if ( fusionOptionNetworkNames[ key ].archive ) {

						// If we have a TO value for defined theme-option name, get its value.
						if ( 'object' === typeof fusionOptionNetworkNames[ key ].archive && fusionOptionNetworkNames[ key ].archive[ 0 ] ) {

							if ( FusionApp.settings[ fusionOptionNetworkNames[ key ].archive[ 0 ] ] ) {
								value = FusionApp.settings[ fusionOptionNetworkNames[ key ].archive[ 0 ] ];

								// If we have a choice defined, get its value.
								if ( choice && 'undefined' !== value[ choice ] ) {
									value = value[ choice ];
								}
							}
						} else if ( FusionApp.settings[ fusionOptionNetworkNames[ key ].archive ] ) {
							value = FusionApp.settings[ fusionOptionNetworkNames[ key ].archive ];

							// If we have a choice defined, get its value.
							if ( choice && 'undefined' !== value[ choice ] ) {
								value = value[ choice ];
							}
						}
					}
				}
			}

			// Make sure this is not an override that should not be happening.
			// See https://github.com/Theme-Fusion/Avada/issues/8122 for details.
			switch ( key ) {
				case 'header_bg_repeat':
				case 'header_bg_full':
					skip = ( FusionApp.data.postMeta._fusion.header_bg_image && '' === FusionApp.data.postMeta._fusion.header_bg_image.url );
					break;

				case 'bg_repeat':
				case 'bg_full':
					skip = ( FusionApp.data.postMeta._fusion.bg_image && '' === FusionApp.data.postMeta._fusion.bg_image.url );
					break;

				case 'content_bg_repeat':
				case 'content_bg_full':
					skip = ( FusionApp.data.postMeta._fusion.content_bg_image && '' === FusionApp.data.postMeta._fusion.content_bg_image.url );
					break;
			}

			if ( ! skip ) {

				// If we have a post value for defined name, get its value.
				if (
					'undefined' !== typeof FusionApp.data.postMeta._fusion &&
					'undefined' !== typeof FusionApp.data.postMeta._fusion[ key ] &&
					'' !== FusionApp.data.postMeta._fusion[ key ] &&
					'default' !== FusionApp.data.postMeta._fusion[ key ]
				) {
					value = FusionApp.data.postMeta._fusion[ key ];
				} else if (
					'undefined' !== typeof FusionApp.data.postMeta[ key ] &&
					'' !== FusionApp.data.postMeta[ key ] &&
					'default' !== FusionApp.data.postMeta[ key ]
				) {
					value = FusionApp.data.postMeta[ key ];
				}
			}

			// Hack for PTB values.
			if ( 'page_title_bar' === key || 'blog_show_page_title_bar' === key || 'blog_page_title_bar' === key ) {
				value = value.toLowerCase();
				value = 'yes' === value ? 'bar_and_content' : value;
				value = 'yes_without_bar' === value ? 'content_only' : value;
				value = 'no' === value ? 'hide' : value;
			}

			return value;
		},

		/**
		 * Adds .hover class in addition to :hover.
		 *
		 * @since 6.0
		 * @param {mixed} elements - Elements.
		 * @return {mixed} - Returns the elements.
		 */
		addHoverElements: function( elements ) {
			var fakeHover = '';

			if ( 'string' === typeof elements && elements.indexOf( ',' ) ) {
				elements = elements.split( ',' );
			}

			if ( 'string' === typeof elements && -1 !== elements.indexOf( ':hover' ) ) {
				fakeHover        = elements.replace( ':hover', '.hover' ) + ',';
				return fakeHover + elements;
			} else if ( 'object' === typeof elements ) {
				elements = _.toArray( elements );
				_.each( elements, function( element ) {
					if ( -1 !== element.indexOf( ':hover' ) ) {
						fakeHover = element.replace( ':hover', '.hover' );
						elements.push( fakeHover );
					}
				} );
			}
			return elements;
		},

		/**
		 * Generated the CSS for this setting.
		 *
		 * @since 6.0
		 * @param {string} id - The setting-ID.
		 * @param {Object} output - The output arguments.
		 * @param {Object} cssVars - The css variables object.
		 * @param {string} type - TO/PO.
		 * @param {number} preview - Whether option has preview set.
		 * @param {string} fieldType - The field type.
		 * @return {void}
		 */
		generateCSS: function( id, output, cssVars, type, preview, fieldType ) {
			var values = 'TO' === type || 'FBE' === type ? FusionApp.settings : FusionApp.data.postMeta._fusion,
				self   = this,
				origValue,
				value,
				parentValue,
				css,
				ruleCalc,
				responsiveTypograhy;

			values = values || {};

			values      = 'PS' === type ? FusionApp.data.postDetails : values;
			origValue   = values[ id ];
			value       = origValue;
			parentValue = value;

			type = 'FBE' === type ? 'TO' : type;
			if ( ! self.needsPreviewUpdate( id, type ) ) {
				return;
			}

			// Add the style.
			css = '<style type="text/css" id="css-' + id + '">';

			if ( output ) {

				// Loop all output arguments.
				_.each( output, function( rule ) {

					// Reset value on each loop. In case callback of prior messed with it.
					if ( 'PS' !== type ) {
						value = self.getPoToValue( id, rule.choice );
					}

					// Add any missing arguments.
					// Helps avoid costly checks down the line.
					rule = _.defaults( rule, {
						element: '',
						property: '',
						units: '',
						prefix: '',
						suffix: '',
						js_callback: false,
						callback: false,
						value_pattern: '$',
						pattern_replace: false,
						media_query: false,
						function: 'style'
					} );

					// Make sure any manipulation that takes place does not change object.
					rule = jQuery.extend( true, {}, rule );

					// Get sub-value if "choice" i defined in the rule.
					if ( _.isObject( parentValue ) && ! _.isUndefined( rule.choice ) && ! _.isUndefined( parentValue[ rule.choice ] ) ) {
						value = parentValue[ rule.choice ];
					}

					// PO dimension check.
					if ( 'PO' === type && ! _.isObject( parentValue ) && ! _.isUndefined( rule.choice ) ) {
						value = values[ rule.choice ];
					}

					// If preview is required add hover class.
					if ( preview ) {
						rule.element = self.addHoverElements( rule.element );
					}

					// Make sure the element is a string.
					if ( _.isObject( rule.element ) ) {
						rule.element = jQuery.map( rule.element, function( val ) {
							return [ val ];
						} );

						rule.element = rule.element.join( ',' );
					}

					if ( 'attr' === rule[ 'function' ] ) {
						self.elementAttrs( rule, value );
					} else if ( 'html' === rule[ 'function' ] ) {
						self.elementHtmlContent( rule, value );
					} else {

						// Modify the value for some field-types.
						if ( FusionApp.sidebarView.flatToObject[ id ] && FusionApp.sidebarView.flatToObject[ id ].type ) {
							switch ( FusionApp.sidebarView.flatToObject[ id ].type ) {
							case 'media':
								value = ( value && value.url ) ? value.url : '';
								if ( rule.property && 'background-image' === rule.property && '' === value ) {
									value = 'none';
								}
								break;
							}
						}

						if ( _.isObject( value ) ) {
							ruleCalc = jQuery.extend( true, {}, rule );

							// Add the CSS.
							_.each( value, function( val, key ) {
								if ( rule.element && ( 'margin' === rule.property || 'padding' === rule.property ) && ( 'top' === key || 'bottom' === key || 'left' === key || 'right' === key ) ) {
									ruleCalc.property = rule.property + '-' + key;
								} else if ( ! rule.property || '' === rule.property ) {
									ruleCalc.property = key;
								}
								css += self.getSingleCSS( val, ruleCalc );
							} );
						} else {

							// Value is not an object so this is simple.
							css += self.getSingleCSS( value, rule );
						}
					}
				} );
			}

			if ( cssVars ) {
				_.each( cssVars, function( cssVar ) {

					// Reset value on each loop.  In case callback of prior messed with it.
					var varVal   = 'undefined' !== typeof cssVar.po && false === cssVar.po ? self.getPoToValue( id, cssVar.choice, true ) : self.getPoToValue( id, cssVar.choice, false ),
						selector = ':root';

					// Get sub-value if we have a 3rd argument.
					if ( _.isObject( varVal ) && ! _.isUndefined( cssVar.choice ) && ! _.isUndefined( varVal[ cssVar.choice ] ) ) {
						if ( ( 'font-style' === cssVar.choice || 'font-weight' === cssVar.choice ) && 'string' === typeof varVal[ 'font-family' ] && varVal[ 'font-family' ].includes( 'var(' ) ) {
							varVal = window.awbTypographySelect.getVarString( parentValue[ 'font-family' ], cssVar.choice );
						} else {
							varVal = varVal[ cssVar.choice ];
						}
					}

					// Make sure we have a pattern.
					cssVar.value_pattern = 'undefined' === typeof cssVar.value_pattern ? '$' : cssVar.value_pattern;

					varVal = ( varVal ) ? varVal : '';
					if ( cssVar.choice && varVal && 'object' === typeof varVal[ cssVar.choice ] ) {
						varVal = varVal[ cssVar.choice ];
					}

					if ( cssVar.exclude ) {
						if ( 'string' === typeof cssVar.exclude ) {
							cssVar.exclude = [ cssVar.exclude ];
						}
						_.each( cssVar.exclude, function( exclusion ) {
							if ( varVal === exclusion ) {
								varVal = '';
							}
						} );
					}

					varVal = cssVar.value_pattern.replace( /\$/g, varVal );

					if ( 'undefined' !== typeof cssVar.callback ) {
						varVal = self.applyCallback( varVal, cssVar.callback[ 0 ], cssVar.callback[ 1 ] );
					}

					// If a selector is set use that, since it is more performant.
					if ( 'string' === typeof cssVar.element ) {
						selector = cssVar.element;
					}

					// For live editor, if the text-transform is not set, then override with inherit to display like in front-end.
					if ( 'text-transform' === cssVar.choice && '' === varVal  ) {
						varVal = 'inherit';
					}

					css += selector + '{' + cssVar.name + ':' + varVal + ';}';
				} );
			}
			css += '</style>';

			// Inject into frame.
			if ( jQuery( '#fb-preview' ).contents().find( 'head' ).find( '#css-' + id ).length ) {
				jQuery( '#fb-preview' ).contents().find( 'head' ).find( '#css-' + id ).remove();
			}
			jQuery( '#fb-preview' ).contents().find( 'head' ).append( css );

			// Trigger special JS.
			responsiveTypograhy = [ 'h1_typography', 'h2_typography', 'h3_typography', 'h4_typography', 'h5_typography', 'h6_typography' ];
			if ( _.contains( responsiveTypograhy, id ) ) {
				this.updateResponsiveTypography( id, origValue );
			}

			// Store it on App.
			if ( 'TO' === type || 'FBE' === type ) {
				FusionApp.storedToCSS[ id ] = css;
			} else {
				FusionApp.storedPoCSS[ id ] = css;
			}
		},

		/**
		 * Change the contents of an element.
		 *
		 * @since 6.0
		 * @param {Object} rule - The output arguments.
		 * @param {mixed} value - The value.
		 * @return {void}
		 */
		elementHtmlContent: function( rule, value ) {
			var self = this,
				element,
				exclude = self.getExcludeBool( value, rule );

			if ( true === exclude ) {
				return;
			}

			// Apply value_pattern and js_callback.
			value = self.getCssValue( value, rule );

			// Find the element.
			element = jQuery( '#fb-preview' ).contents().find( rule.element );

			// Change the contents of the element.
			element.html( value );
		},

		/**
		 * Change the attributes of an element.
		 *
		 * @since 6.0
		 * @param {Object} rule - The output arguments.
		 * @param {mixed} value - The value.
		 * @return {void}
		 */
		elementAttrs: function( rule, value ) {
			var self = this,
				attr,
				element,
				exclude = self.getExcludeBool( value, rule );

			if ( _.isUndefined( rule.attr ) || _.isEmpty( rule.attr ) ) {
				return;
			}

			if ( true === exclude ) {
				return;
			}

			// Apply value_pattern and js_callback.
			value = self.getCssValue( value, rule );
			if ( rule.toLowerCase ) {
				value = value.toLowerCase();
			}

			// If value is empty, early exit.
			// This helps with cases where js_callback returns empty
			// since in those cases we don't want the rule applied.
			if ( '' === value ) {
				return;
			}

			// Find the element.
			element = jQuery( '#fb-preview' ).contents().find( rule.element );

			// Get the attribute.
			attr = element.attr( rule.attr );

			if ( _.isUndefined( attr ) ) {
				attr = '';
			}

			// If we want to remove and add attributes, we need some extra calcs (useful for CSS classes).
			if ( ! _.isUndefined( rule.remove_attrs ) && ! _.isEmpty( rule.remove_attrs ) && '' !== attr ) {
				_.each( rule.remove_attrs, function( attrToRemove ) {

					if ( 'class' === rule.attr ) {
						element.removeClass( attrToRemove );
					}

					// Some attributes use comma-separated values (mostly data attributes).
					attr = attr.split( attrToRemove + ',' ).join( '' );

					// If separated using a spoace, remove the space as well.
					attr = attr.split( attrToRemove + ' ' ).join( '' );

					// Final check: Remove attribute if not already removed from the above 2 rules.
					attr = attr.split( attrToRemove ).join( '' );
				} );

				attr += ( '' !== attr ) ? ' ' : '';
				attr += value;

				if ( 'class' === rule.attr ) {
					element.addClass( attr );
				} else {
					element.attr( rule.attr, attr );
				}

				return;
			}

			element.attr( rule.attr, value );
		},

		/**
		 * Live-update custom CSS.
		 *
		 * @param {string} newContent - The new CSS.
		 * @return {void}
		 */
		liveUpdateCustomCSS: function( newContent ) {

			var customCSS = 'undefined' !== typeof newContent ? newContent : '';
			if ( jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).length ) {
				jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).html( customCSS );
			}
		},

		/**
		 * Live-update the preview pane.
		 *
		 * @param {string} newContent - The new CSS.
		 * @return {void}
		 */
		liveUpdatePageCustomCSS: function( newContent ) {

			if ( jQuery( '#fb-preview' ).contents().find( '#fusion-builder-page-css' ).length ) {
				jQuery( '#fb-preview' ).contents().find( '#fusion-builder-page-css' ).html( newContent );
			} else {
				newContent = '<style type="text/css" id="fusion-builder-page-css">' + newContent + '</style>';

				// If TO custom CSS exists, make sure to add after.
				if ( jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).length ) {
					jQuery( '#fb-preview' ).contents().find( '#fusion-builder-custom-css' ).after( newContent );
				} else {
					jQuery( '#fb-preview' ).contents().find( 'head' ).append( newContent );
				}
			}
		},

		/**
		 * Update responsive typography.
		 *
		 *
		 * @since 6.0
		 * @param {string} id - Option id.
		 * @param {Object} values - The values.
		 * @return {void}
		 */
		updateResponsiveTypography: function( id, values ) {
			var heading = id.split( '_' )[ 0 ];
			if ( 'function' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.fusionCalculateResponsiveTypeValues ) {
				document.querySelector( '#fb-preview' ).contentWindow.document.body.dispatchEvent( new CustomEvent( 'fusion-typography', { detail: { heading: heading, values: values } } ) );
			}
		},

		/**
		 * Get CSS from rule.
		 *
		 * This is a helper function that only accepts a string for the value.
		 * Used by the generateCSS method in this object.
		 *
		 * @since 6.0
		 * @param {string} value - The value.
		 * @param {Object} rule - The rule.
		 * @return {string} - The CSS.
		 */
		getSingleCSS: function( value, rule ) {
			var self    = this,
				exclude = self.getExcludeBool( value, rule ),
				css     = '';

			if ( true === exclude ) {
				return '';
			}

			// Apply value_pattern and js_callback.
			value = self.getCssValue( value, rule );

			// Add prefix, units, suffix.
			value = rule.prefix + value + rule.units + rule.suffix;

			// Generate the CSS.
			if ( rule.media_query ) {

				// If the media-query should be dynamically generated, get it using a helper function.
				if ( 0 === rule.media_query.indexOf( 'fusion-' ) ) {
					rule.media_query = fusionReturnMediaQuery( rule.media_query );
				}
				css += rule.media_query + '{';
			}
			css += rule.element + '{' + rule.property + ':' + value + ';}';
			if ( rule.media_query ) {
				css += '}';
			}

			// If value is empty, return empty string, otherwise return our css.
			return ( ! value || _.isEmpty( value ) ) ? '' : css;
		},

		/**
		 * Figure out if the exclude argument evaluates to true or false.
		 *
		 * This is a helper function that only accepts a string for the value.
		 * Used by the generateCSS method in this object.
		 *
		 * @since 6.0
		 * @param {string} value - The value.
		 * @param {Object} rule - The rule.
		 * @return {boolean} - Whether or not the exclude params evaluate to true or not.
		 */
		getExcludeBool: function( value, rule ) {
			var self    = this,
				exclude = false;

			// If we have defined an "exclude" argument, then make sure the value is not in there.
			// If the value is equal to one defined in the exclude argument then skip this.
			if ( ! _.isUndefined( rule.exclude ) ) {
				if ( value === rule.exclude ) {
					exclude = true;
				}
				if ( _.isArray( rule.exclude ) ) {
					_.each( rule.exclude, function( exclusion ) {

						// It's == and not === on purpose, please do not change.
						if ( exclusion == value ) { // jshint ignore:line
							exclude = true;
						}
						if ( ! exclude && _.isEmpty( exclusion ) && _.isEmpty( value ) ) {
							exclude = true;
						}
						if ( ! exclude && ! _.isUndefined( value.url ) && exclusion === value.url ) {
							exclude = true;
						}
					} );
				}
			}
			return exclude;
		},

		/**
		 * Applies any extra rules such as value_pattern and js_callback.
		 *
		 * @since 6.0
		 * @param {mixed}  value - The value.
		 * @param {Object} rule - The rule.
		 * @return {mixed} - Returns the value.
		 */
		getCssValue: function( value, rule ) {
			var self = this;

			// If we have defined a value_pattern, apply it.
			if ( 'undefined' !== typeof rule.value_pattern && false !== rule.value_pattern ) {
				value = rule.value_pattern.replace( /\$/g, value );

				// If we're using pattern_replace, apply those values.
				if ( rule.pattern_replace ) {
					_.each( rule.pattern_replace, function( replaceRuleReplace, replaceRuleSearch ) {

						var replaceSetting = replaceRuleReplace.replace( originalOptionName + '[', '' ).replace( ']', '' ),
							replaceValue   = replaceRuleReplace;

						if ( replaceSetting !== replaceRuleReplace ) {
							replaceValue   = FusionApp.settings[ replaceSetting ];
						}

						value = value.replace( replaceRuleSearch, replaceValue ).replace( replaceRuleSearch, replaceValue );
					} );
				}
			}

			// Apply any functions defined in js_callback.
			if ( rule.js_callback ) {
				rule.js_callback[ 1 ] = ( _.isUndefined( rule.js_callback[ 1 ] ) ) ? '' : rule.js_callback[ 1 ];
				value = self.applyCallback( value, rule.js_callback[ 0 ], rule.js_callback[ 1 ] );
			} else if ( rule.callback ) {
				rule.callback[ 1 ] = ( _.isUndefined( rule.callback[ 1 ] ) ) ? '' : rule.callback[ 1 ];
				value = self.applyCallback( value, rule.callback[ 0 ], rule.callback[ 1 ] );
			}

			if ( rule.property && 'background-image' === rule.property && 'string' === typeof value && -1 === value.indexOf( 'url(' ) ) {

				// Make sure it's a URL. We need this check for compatibility with gradient backgrounds.
				if ( -1 !== value.indexOf( '/' ) && -1 !== value.indexOf( '.' ) && -1 === value.indexOf( '-gradient(' ) ) {
					value = 'url(' + value + ')';
				}
			}

			return value;
		},

		/**
		 * Figure out if this change requires updating the preview.
		 *
		 * @since 6.0
		 * @param {string} id - The setting ID.
		 * @param {string} type - TO|PO|TAXO.
		 * @return {boolean} - Whether or not we should update the preview.
		 */
		needsPreviewUpdate: function( id, type ) {
			if ( 'scheme_type' === id || 'post_title' === id || 'name' === id ) {
				return true;
			}
			if ( 'TO' === type && this.validateChangeContext( id, type )[ 1 ] !== type ) {
				return false;
			}
			return true;
		},

		/**
		 * Gets the context for our option-change.
		 * If we change a TO that has a PO overriding its value then we should not do anything.
		 * Takes into account page-options, term-options & theme-options.
		 *
		 * @since 6.0
		 * @param {string} id - The option ID.
		 * @param {string} type - PO|TO|TAXO.
		 * @return {Array} - [id, type], The option that should actually be applied.
		 */
		validateChangeContext: function( id, type ) {
			var key         = id,
				found       = false,
				networkKeys = {
					TAXO: 'term',
					PO: 'post',
					TO: 'theme'
				};

			// Make sure our "type" param is correct.
			type = ( 'undefined' === typeof networkKeys[ type ] ) ? 'TO' : type;

			// Check if PO/Tax.
			_.each( fusionOptionNetworkNames, function( definition, definitionKey ) {

				// Key found in our options map.
				if ( false === found && ( definitionKey === id || ( 'undefined' !== typeof definition[ networkKeys[ type ] ] && definition[ networkKeys[ type ] ] === id ) ) ) {

					// Should this value override TOs?
					if ( FusionApp.data.postMeta._fusion[ key ] && '' !== FusionApp.data.postMeta._fusion[ key ] && 'default' !== FusionApp.data.postMeta._fusion[ key ] ) {
						found = [ key, type ];
					}
				}
			} );
			return ( false === found ) ? [ key, 'TO' ] : found;
		},

		/**
		 * Populate the field output-dependencies.
		 *
		 * @since 6.0
		 * @return {void}
		 */
		populateFieldOutputDependencies: function() {
			var self = this,
				setting;

			// No need to run if self.fieldOutputDependencies is already populated.
			if ( ! self.fieldOutputDependencies || ! _.isEmpty( self.fieldOutputDependencies ) ) {
				return;
			}
			if ( ! FusionApp.sidebarView.flatToObject || _.isEmpty( FusionApp.sidebarView.flatToObject ) ) {
				FusionApp.sidebarView.getFlatToObject();
			}

			_.each( FusionApp.sidebarView.flatToObject, function( field ) {
				if ( field.output ) {
					_.each( field.output, function( output ) {
						if ( output.js_callback ) {
							if ( output.js_callback && output.js_callback[ 0 ] && 'conditional_return_value' === output.js_callback[ 0 ] ) {
								_.each( output.js_callback[ 1 ].conditions, function( callbackArray ) {
									if ( -1 !== callbackArray[ 0 ].indexOf( originalOptionName + '[' ) ) {
										setting = callbackArray[ 0 ].replace( originalOptionName + '[', '' ).replace( ']', '' );

										if ( ! self.fieldOutputDependencies[ setting ] ) {
											self.fieldOutputDependencies[ setting ] = [];
										}
										if ( -1 === self.fieldOutputDependencies[ setting ].indexOf( field.id ) ) {
											self.fieldOutputDependencies[ setting ].push( field.id );
										}
									}
								} );
							}
						}
						if ( output.pattern_replace ) {
							_.each( output.pattern_replace, function( replaceRuleReplace, replaceRuleSearch ) {

								var scopedSetting = replaceRuleReplace.replace( originalOptionName + '[', '' ).replace( ']', '' );

								if ( ! self.fieldOutputDependencies[ scopedSetting ] ) {
									self.fieldOutputDependencies[ scopedSetting ] = [];
								}
								if ( -1 === self.fieldOutputDependencies[ scopedSetting ].indexOf( field.id ) ) {
									self.fieldOutputDependencies[ scopedSetting ].push( field.id );
								}
							} );
						}
					} );
				}
			} );
		}
	};
}( jQuery ) );
