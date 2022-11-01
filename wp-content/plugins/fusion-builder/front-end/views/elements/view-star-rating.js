var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Star Rating Element View.
		FusionPageBuilder.fusion_star_rating = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @returns {Object}
			 */
			filterTemplateAtts: function( atts ) {
				// Variables we will pass to the template.
				var attributes = {};

				attributes.cid = this.model.get( 'cid' );
				attributes.elementAttr = this.getElementAttr( atts.values );
				attributes.style = this.getStyleTag( atts.values, atts.extras );
				attributes.iconsHtml = this.getIconsHtml( atts.values );
				attributes.ratingTextHtml = this.getRatingTextHtml( atts.values );
				attributes.displayRatingText = ( 'yes' === atts.values.display_rating_text ? true : false );

				return attributes;
			},

			/**
			 * Get the HTML of the icons.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getIconsHtml: function( values ) {
				var html                     = '',
					rating                   = this.getRating( values ),
					maximumRating            = values.maximum_rating,
					isPerfectRoundRating,
					currentStar              = 1;

				if ( '0decimals' === values.rating_number_rounding && 'yes' === values.display_rating_text ) {
					rating = parseInt( rating.toFixed( 0 ) );
				}

				isPerfectRoundRating = ( parseInt( rating ) === rating );

				while ( currentStar <= maximumRating ) {
					html += '<i ' + _.fusionGetAttributes( this.getIconAttributes( values, currentStar ) ) + '>';
					if ( ! isPerfectRoundRating && ( parseInt( rating ) + 1 ) === currentStar ) {
						html += '<i ' + _.fusionGetAttributes( this.getPartialIconAttributes( values, currentStar ) ) + '></i>';
					}
					html += '</i>';
					currentStar++;
				}

				return html;
			},

			/**
			 * Get the HTML of the rating text.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getRatingTextHtml: function( values ) {
				var html          = '',
					rating        = this.getRating( values ),
					maximumRating = values.maximum_rating;

				if ( rating > maximumRating ) {
					rating = maximumRating;
				}

				rating = Number( rating ).toFixed( this.getNumberToRound( values, rating ) );

				html += '<span>' + rating + '</span> / <span>' + maximumRating + '</span>';
				return html;
			},

			getNumberToRound: function( values, rating ) {
				var numParts,
					numToRound;

				if ( '0decimals' === values.rating_number_rounding ) {
					return 0;
				} else if ( '1decimal' === values.rating_number_rounding ) {
					return 1;
				} else if ( '2decimals' === values.rating_number_rounding ) {
					return 2;
				}

				// 'rating_number_rounding' is set to 'auto' if here.

				if ( Math.floor( rating ) === rating ) {
					return 0;
				}

				numParts = rating.toString().trimEnd( '0' ).split( '.' );

				if ( numParts[ 1 ] ) {
					numToRound = numParts[ 1 ].length || 0;
				} else {
					numToRound = 0;
				}

				if ( 0 === numToRound ) {
					return 0;
				} else if ( 1 === numToRound ) {
					return 1;
				}

				return 2;
			},

			/**
			 * Get the attributes of the main HTML element.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getElementAttr: function( values ) {
				var attr = {
					'class': 'awb-stars-rating ' + this.getBaseClassName()
				};

				attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

				if ( 'no' === values.display_rating_text ) {
					attr[ 'class' ] += ' awb-stars-rating-no-text';
				}

				if ( values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Get the attributes of the icon HTML tag.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @param {Object} current_icon_num - The number of the current icon.
			 * @returns {string}
			 */
			getIconAttributes: function( values, currentIconNum ) {
				var attr = {
						'class': _.fusionFontAwesome( values.icon )
					},
					rating                = this.getRating( values ),
					isPerfectRoundRating,
					iconIsPartiallyFilled;

				if ( '0decimals' === values.rating_number_rounding && 'yes' === values.display_rating_text ) {
					rating = parseInt( rating.toFixed( 0 ) );
				}

				isPerfectRoundRating  = ( parseInt( rating ) === rating ),
				iconIsPartiallyFilled = ( ( parseInt( rating ) + 1 ) === currentIconNum );

				if ( currentIconNum <= rating ) {
					attr[ 'class' ] += ' awb-stars-rating-filled-icon';
				} else if ( ! isPerfectRoundRating && iconIsPartiallyFilled ) {
					attr[ 'class' ] += ' awb-stars-rating-partial-icon-wrapper';
				} else {
					attr[ 'class' ] += ' awb-stars-rating-empty-icon';
				}

				return attr;
			},

			/**
			 * Get the attributes of the partial filled icon HTML tag.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getPartialIconAttributes: function( values ) {
				var attr = {
						'class': _.fusionFontAwesome( values.icon ) + ' awb-stars-rating-partial-icon'
					},
					rating        = this.getRating( values ),
					decimals      = rating - parseInt( rating ),
					widthPercent  = parseInt( decimals * 100 ) + '%';

				attr.style = 'width:' + widthPercent + ';';

				return attr;
			},

			/**
			 * Create the style HTML element.
			 *
			 * @since 3.5
			 * @param {Object} values - The values.
			 * @returns {string}
			 */
			getStyleTag: function( values, extras ) {
				var style,
					baseSelector             = '.' + this.getBaseClassName(),
					baseClassSelector        = [ baseSelector + '.awb-stars-rating' ],
					allRelativeIconsSelector = [
						baseSelector + ' .awb-stars-rating-filled-icon',
						baseSelector + ' .awb-stars-rating-empty-icon',
						baseSelector + ' .awb-stars-rating-partial-icon-wrapper'
					],
					allIconsThatHaveActiveColor = [
						baseSelector + ' .awb-stars-rating-filled-icon',
						baseSelector + ' .awb-stars-rating-partial-icon'
					],
					iconsWrapperSelector      = [ baseSelector + ' .awb-stars-rating-icons-wrapper' ],
					rtlIconsWrapperSelector   = [ '.rtl ' + baseSelector + ' .awb-stars-rating-icons-wrapper' ],
					textWrapperSelector       = [ baseSelector + ' .awb-stars-rating-text' ];

				this.dynamic_css = {};
				this.values = values;

				if ( ! this.isDefault( 'icons_distance' ) && ! _.isEmpty( values.icons_distance ) ) {
					this.addCssProperty( allRelativeIconsSelector, 'margin-right', values.icons_distance );
				}

				if ( ! this.isDefault( 'active_color' ) && ! _.isEmpty( values.active_color ) ) {
					this.addCssProperty( allIconsThatHaveActiveColor, 'color', values.active_color );
				}

				if ( ! this.isDefault( 'inactive_color' ) && ! _.isEmpty( values.inactive_color ) ) {
					this.addCssProperty( iconsWrapperSelector, 'color', values.inactive_color );
				}

				if ( ! this.isDefault( 'icon_font_size' ) && ! _.isEmpty( values.icon_font_size ) ) {
					this.addCssProperty( iconsWrapperSelector, 'font-size', values.icon_font_size );
				}

				if ( 'yes' === values.display_rating_text ) {
					if ( ! this.isDefault( 'text_font_size' ) && ! _.isEmpty( values.text_font_size ) ) {
						this.addCssProperty( textWrapperSelector, 'font-size', values.text_font_size );
					}

					if ( ! this.isDefault( 'text_font_color' ) && ! _.isEmpty( values.text_font_color ) ) {
						this.addCssProperty( textWrapperSelector, 'color', values.text_font_color );
					}

					if ( ! this.isDefault( 'icons_text_distance' ) && ! _.isEmpty( values.icons_text_distance ) ) {
						if ( ! extras.is_rtl ) {
							this.addCssProperty( iconsWrapperSelector, 'margin-right', values.icons_text_distance );
						} else {
							this.addCssProperty( rtlIconsWrapperSelector, 'margin-left', values.icons_text_distance );
						}
					}
				}

				if ( ! this.isDefault( 'alignment' ) && ! _.isEmpty( values.alignment ) ) {
					this.addCssProperty( baseClassSelector, 'justify-content', values.alignment );
				}

				if ( ! this.isDefault( 'margin_top' ) && ! _.isEmpty( values.margin_top ) ) {
					this.addCssProperty( baseClassSelector, 'margin-top', values.margin_top );
				}

				if ( ! this.isDefault( 'margin_right' ) && ! _.isEmpty( values.margin_right ) ) {
					this.addCssProperty( baseClassSelector, 'margin-right', values.margin_right );
				}

				if ( ! this.isDefault( 'margin_bottom' ) && ! _.isEmpty( values.margin_bottom ) ) {
					this.addCssProperty( baseClassSelector, 'margin-bottom', values.margin_bottom );
				}

				if ( ! this.isDefault( 'margin_left' ) && ! _.isEmpty( values.margin_left ) ) {
					this.addCssProperty( baseClassSelector, 'margin-left', values.margin_left );
				}

				style = this.parseCSS();

				return style ? '<style>' + style + '</style>' : '';
			},

			/**
			 * Get the rating number.
			 *
			 * @since 3.5
			 * @returns {Number}
			 */
			getRating: function( values ) {
				var avg = values.rating;

				// Let the users use ',' as decimal separator.
				if ( _.isString( avg ) ) {
					avg = avg.replace( ',', '.' );
				}

				if ( ! isNaN( avg ) ) {
					avg = Number( avg );
				} else {
					avg = 0;
				}

				avg = Number( avg );

				return avg;
			},

			/**
			 * Get the class name with an unique id among elements.
			 *
			 * @since 3.5
			 * @returns {string}
			 */
			getBaseClassName: function() {
				return 'awb-stars-rating-' + this.model.get( 'cid' );
			}

		} );

	} );

}( jQuery ) );
