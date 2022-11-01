var awbPalette = awbPalette || {};

/**
 * Initialize the awbPalette object. This object is meant to be as a singleton.
 * In data property holds all the global colors.
 *
 * Usage:
 * 1. Use getColorObject to retrieve an object, and don't forget to verify if the returned value is not null. Ex:
 * color = awbPalette.getColorObject( slug );
 * if ( ! color ) {
 *  color = awbPalette.getDefaultColorObject();
 * }
 *
 * 2. Use functions like addOrUpdateColor(), removeColor() to add/remove global colors.
 *
 * 3. Listen for any changes if a global color changes, via 'awbPalette' event.
 */
( function( $, undef ) {
    awbPalette.data = awbPalette.data || {};
    awbPalette.LiveEditorCSSVars = [];

    // Make wrapper of jQuery Color to parse global color.
    if ( 'function' === typeof jQuery.Color ) {
        jQuery.Color.prototype.calcLuminance = function() {
            var r = this._rgba[ 0 ], g = this._rgba[ 1 ], b = this._rgba[ 2 ];
            var a = [r, g, b].map(function (v) {
                v /= 255;
                return v <= 0.03928
                    ? v / 12.92
                    : Math.pow( (v + 0.055) / 1.055, 2.4 );
            });
            return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
        };

        jQuery.Color.prototype.contrast = function( compareColor ) {
            var luminance = this.calcLuminance();
            var color2luminance = jQuery.Color( compareColor ).calcLuminance();

            return luminance > color2luminance ? ((luminance + 0.05) / (color2luminance + 0.05)) : ((color2luminance + 0.05) / (luminance + 0.05));
        };

        // Replace alpha function.
        jQuery.Color.prototype.awb_alpha = jQuery.Color.prototype.alpha;
        jQuery.Color.prototype.alpha = function( param ) {
            obj = this.awb_alpha( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Replace hue function.
        jQuery.Color.prototype.awb_hue = jQuery.Color.prototype.hue;
        jQuery.Color.prototype.hue = function( param ) {
            obj = this.awb_hue( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Replace saturation function.
        jQuery.Color.prototype.awb_saturation = jQuery.Color.prototype.saturation;
        jQuery.Color.prototype.saturation = function( param ) {
            obj = this.awb_saturation( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Replace lightness function.
        jQuery.Color.prototype.awb_lightness = jQuery.Color.prototype.lightness;
        jQuery.Color.prototype.lightness = function( param ) {
            obj = this.awb_lightness( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Replace red function.
        jQuery.Color.prototype.awb_red = jQuery.Color.prototype.red;
        jQuery.Color.prototype.red = function( param ) {
            obj = this.awb_red( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Replace green function.
        jQuery.Color.prototype.awb_green = jQuery.Color.prototype.green;
        jQuery.Color.prototype.green = function( param ) {
            obj = this.awb_green( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Replace blue function.
        jQuery.Color.prototype.awb_blue = jQuery.Color.prototype.blue;
        jQuery.Color.prototype.blue = function( param ) {
            obj = this.awb_blue( param );
            if ( 'object' === typeof obj && this.globalColorSlug ) {
                obj.globalColorSlug = this.globalColorSlug;
            }
            return obj;
        };

        // Add a new method that will can return variables if necessary.
        jQuery.Color.prototype.toVarOrRgbaString = function() {
            var cssValue = this.toRgbaString(),
                slug,
                globalColor,
                changed,
                difference,
                sign,
                huePart,
                saturationPart,
                lightnessPart,
                alphaPart;

            slug = this.globalColorSlug;
            if ( ! slug || ! awbPalette.data[slug] || 'string' !== typeof awbPalette.data[ slug ].color ) {
                return cssValue;
            }

            globalColor = jQuery.AWB_Color( awbPalette.data[ slug ].color );
            changed = false;

            huePart = 'var(--awb-' + slug + '-h)';
            saturationPart = 'var(--awb-' + slug + '-s)';
            lightnessPart = 'var(--awb-' + slug + '-l)';
            alphaPart = 'var(--awb-' + slug + '-a)';

            if ( this.hue() !== globalColor.hue() ) {
                difference = Math.round( ( globalColor.hue() - this.hue() ) );
                sign = ( difference > 0 ? ' - ' : ' + ' );

                changed = true;
                huePart = 'calc(' + huePart + sign + Math.abs( difference ) + ')';
            }

            if ( this.saturation() !== globalColor.saturation() ) {
                difference = Math.round( ( globalColor.saturation() - this.saturation() ) * 100 );
                sign = ( difference > 0 ? ' - ' : ' + ' );

                changed = true;
                saturationPart = 'calc(' + saturationPart + sign + Math.abs( difference ) + '%)';
            }

            if ( this.lightness() !== globalColor.lightness() ) {
                difference = Math.round( ( globalColor.lightness() - this.lightness() ) * 100 );
                sign = ( difference > 0 ? ' - ' : ' + ' );

                changed = true;
                lightnessPart = 'calc(' + lightnessPart + sign + Math.abs( difference ) + '%)';
            }

            if ( this.alpha() !== globalColor.alpha() ) {
                difference = Math.round( ( globalColor.alpha() - this.alpha() ) * 100 );
                sign = ( difference > 0 ? ' - ' : ' + ' );

                changed = true;
                alphaPart = 'calc(' + alphaPart + sign + Math.abs( difference ) + '%)';
            }

            if ( changed ) {
                cssValue = 'hsla(' + huePart + ',' + saturationPart + ',' + lightnessPart + ',' + alphaPart + ')';
            } else {
                cssValue = 'var(--awb-' + slug + ')';
            }

            return cssValue;
        };

        // Try to convert the value to a CSS global var if possible, else to rgba.
        jQuery.AWB_Color = function( color ) {
            var obj = jQuery.Color( awbPalette.getRealColor( color ) );
            var slug = awbPalette.getColorSlugFromCssVar( color );

            if ( slug ) {
                obj.globalColorSlug = slug;
            }

            return obj;
        };
    }

    /**
     * Gets the entire color object. Makes sure that the color object returned
     * have all the properties set.
     *
     * @since 3.6
     * @param {string|null} colorSlug
     */
    awbPalette.getColorObject = function( colorSlug ) {
        var color;

        if ( awbPalette.data[ colorSlug ] ) {
            color = Object.assign( {}, awbPalette.data[ colorSlug ] );
            if ( undefined !== color.color && undefined !== color.label ) {
                return color;
            }
        }

        return null;
    };

    /**
     * Gets a default color object, used to replace data the if the color slug
     * needed does not exist.
     *
     * @since 3.6
     */
    awbPalette.getDefaultColorObject = function() {
        return {
            label: awbPalette.unknownColor || 'Unknown Color',
            color: '#ffffff',
        };
    };

    /**
     * Add or update a color to the global palette. The color data passes is
     * merged with the previous one, if it exists.
     *
     * @since 3.6
     * @param {string} colorSlug Color slug to be replaced or added.
     * @param {Object} colorData This object will be merged with the previous one.
     */
    awbPalette.addOrUpdateColor = function( colorSlug, colorData ) {
        var oldObject;

        awbPalette.data[ colorSlug ] = awbPalette.data[ colorSlug ] || {};
        oldObject = Object.assign( {}, awbPalette.data[ colorSlug ] );

        awbPalette.data[ colorSlug ] = Object.assign( {}, awbPalette.data[ colorSlug ], colorData );
        jQuery( document ).trigger( 'awbPalette', { slug: colorSlug, oldObject: oldObject, context: 'addOrUpdateColor' } );
    };

    /**
     * Remove a color from the global palette object.
     *
     * @since 3.6
     * @param {string} colorSlug
     */
    awbPalette.removeColor = function( colorSlug ) {
        var clonedOldObject;

        awbPalette.data[ colorSlug ] = awbPalette.data[ colorSlug ] || {};
        clonedOldObject = Object.assign( {}, awbPalette.data[ colorSlug ] );

        delete awbPalette.data[ colorSlug ];
        jQuery( document ).trigger( 'awbPalette', { slug: colorSlug, oldObject: clonedOldObject, context: 'removeColor' } );
    };

    awbPalette.getColorSlugFromCssVar = function( colorVar ) {
        var isHsla = /^\s*hsla\s*\(/i.test( colorVar ),
            matches;

		if ( isHsla ) {
      matches = colorVar.match( /var\s*\(\s*--awb-\w+-h\W.*var\s*\(\s*--awb-(\w+)-s\W/ );
      if ( null !== matches ) {
        return matches[1];
      } else {
        return false;
      }
		} else if ( /var\s*\(\s*--awb-(\w+)/.test( colorVar ) ) {
            matches = colorVar.match( /var\s*\(\s*--awb-(\w+)/ );
			if ( matches[1] ) {
				return matches[1];
			} else {
				return false;
			}
		}

        return false;
    };

    awbPalette.getRealColor = function( colorVar ) {
        var globalColorSlug  = awbPalette.getColorSlugFromCssVar( colorVar ),
            liveEditorIframe = document.getElementById( 'fb-preview' ),
            styleObject      = false;

        if ( liveEditorIframe && liveEditorIframe.contentWindow && liveEditorIframe.contentWindow.document ) {
            styleObject = liveEditorIframe.contentWindow;
        }

        if ( ! styleObject ) {
            return colorVar;
        }

        if ( globalColorSlug ) {
            if ( styleObject.document.getElementById( 'awb-hidden-el-color' ) ) {
                var el = styleObject.document.getElementById( 'awb-hidden-el-color' );
            } else {
                var el = styleObject.document.createElement( 'span' );
                el.setAttribute( 'id', 'awb-hidden-el-color' );
                el.style.display = 'none';
                styleObject.document.body.appendChild( el );
            }
            el.style.color = colorVar;
            return styleObject.window.getComputedStyle( el ).getPropertyValue( 'color' );
        }

        return colorVar;
    };
}( jQuery ) );


// Initialize awbPalette global events.
( function( $, undef ) {
    var LiveEditorCSSVars = [];
    var AdminCSSVars = [];

    // When a global palette color changes, also change the live editor global CSS vars.
    jQuery( function() {
        jQuery( document ).on( 'awbPalette', updateLiveEditorVars );
        jQuery( document ).on( 'awbPalette', updateAdminVars );
        jQuery( document ).on( 'awbPalette', refreshLiveEditorTargetedElements );
    } );

    /**
     * Update the live editor body style, with the CSS variables from the global palette.
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
     * Remove all the CSS variables from the live editor body style, that comes from global palette.
     *
     * @since 3.6
     */
    function removeAllCSSVars( styleObject, cssVarsCache ) {
        var needToOverwriteDeletedColor;

        cssVarsCache.forEach( function( cssVar ) {
            styleObject.removeProperty( cssVar.varName );

            // Overwrite with default color(white) if a global color was removed.
            needToOverwriteDeletedColor = ( awbPalette.getColorObject( cssVar.slug ) ? false : true );
            if ( needToOverwriteDeletedColor ) {
                styleObject.setProperty( cssVar.varName, awbPalette.getDefaultColorObject().color );
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
        var colorSlug,
            colorValue,
            hsla;

        for ( colorSlug in awbPalette.data ) {
            colorValue = awbPalette.data[ colorSlug ].color;

            hsla = jQuery.Color( colorValue ).hsla();

            addDocumentCSSVar( colorSlug, '--awb-' + colorSlug, colorValue );

            addDocumentCSSVar( colorSlug, '--awb-' + colorSlug + '-h', hsla[ 0 ] );
            addDocumentCSSVar( colorSlug, '--awb-' + colorSlug + '-s', ( hsla[ 1 ] * 100 ) + '%' );
            addDocumentCSSVar( colorSlug, '--awb-' + colorSlug + '-l', ( hsla[ 2 ] * 100 ) + '%' );
            addDocumentCSSVar( colorSlug, '--awb-' + colorSlug + '-a', ( hsla[ 3 ] * 100 ) + '%' );
        }

        function addDocumentCSSVar( slug, varName, varValue ) {
            styleObject.setProperty( varName, varValue );
            cssVarsCache.push( { varName: varName, slug: slug } );
        }
    };

    function getLiveEditorDocumentStyle() {
        var liveEditorIframe = document.getElementById( 'fb-preview' );
        if ( liveEditorIframe && liveEditorIframe.contentWindow && liveEditorIframe.contentWindow.document ) {
            return liveEditorIframe.contentWindow.document.documentElement.style;
        }

        return null;
    };

    function _refreshLiveEditorTargetedElements() {
        var elementsToRefresh = [ 'fusion_section_separator', 'fusion_counter_circle', 'fusion_map', 'fusion_soundcloud', 'fusion_chart' ];

        if ( 'undefined' === typeof FusionPageBuilderViewManager ) {
            return;
        }

        var elementViews = FusionPageBuilderViewManager.getViews();
        for ( var index in elementViews ) {
            if ( 'object' !== typeof elementViews[ index ] ) {
                continue;
            }
            var elementType = elementViews[ index ].model.get( 'element_type' );

            if ( elementsToRefresh.includes( elementType ) ) {
                elementViews[index].reRender();
            }
        }

    }
    var refreshLiveEditorTargetedElements = _.debounce( _refreshLiveEditorTargetedElements, 500 );

}( jQuery ) );
