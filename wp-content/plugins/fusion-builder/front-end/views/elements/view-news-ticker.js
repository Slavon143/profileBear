var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		FusionPageBuilder.fusion_news_ticker = FusionPageBuilder.ElementView.extend( {

			/**
			 * Modify template attributes.
			 *
			 * @since 3.5
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes  = {};

				this.animationDuration = this.$el.find( '.awb-news-ticker-item-list' ).css( 'animation-duration' );
				this.values = atts.values;

				attributes.tickerTitle     = atts.values.ticker_title;
				attributes.tickerSpeed     = atts.values.ticker_speed;
				attributes.tickerAttr      = this.buildTickerAttr( atts.values );
				attributes.titleAttr       = this.buildTickerTitleAttr( atts.values );
				attributes.barAttr         = this.buildTickerBarAttr( atts.values );
				attributes.itemsListAttr   = this.buildTickerItemsListAttr( atts.values );
				attributes.titleShape      = atts.values.title_shape;
				attributes.styleTag        = this.getStyleTag( atts.values, atts.extras );
				attributes.tickerItems     = atts.query_data;
				attributes.carouselButtons = this.getCarouselButtonsIfNecessary( atts.values );

				return attributes;
			},

			/**
			 * Build the ticker element attributes.
			 *
			 * @since 3.5
			 * @param {Object} values
			 * @return {Object}
			 */
			buildTickerAttr: function( values ) {
				var attr = {
					'class': 'awb-news-ticker awb-news-ticker-' + this.model.get( 'cid' ),
					'role': 'marquee'
				};

				if ( 'marquee' === values.ticker_type ) {
					attr[ 'class' ] += ' awb-news-ticker-marquee';
				} else if ( 'carousel' === values.ticker_type ) {
					attr[ 'class' ] += ' awb-news-ticker-carousel';
				}

				attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

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
			 * Build the ticker title attributes.
			 *
			 * @since 3.5
			 * @param {Object} values
			 * @return {Object}
			 */
			buildTickerTitleAttr: function( values ) {
				var attr = {
					'class': 'awb-news-ticker-title'
				};

				if ( 'rounded' === values.title_shape ) {
					attr[ 'class' ] += ' awb-news-ticker-title-rounded';
				}

				return attr;
			},

			/**
			 * Build the ticker bar attributes.
			 *
			 * @since 3.5
			 * @param {Object} values
			 * @return {Object}
			 */
			buildTickerBarAttr: function( values ) {
				var attr = {
					'class': 'awb-news-ticker-bar'
				};

				if ( 'marquee' === values.ticker_type ) {
					attr[ 'class' ] += ' awb-news-ticker-bar-marquee';
				} else if ( 'carousel' === values.ticker_type ) {
					attr[ 'class' ] += ' awb-news-ticker-bar-carousel';
				}

				return attr;
			},

			/**
			 * Build the items list wrapper attributes.
			 *
			 * @since 3.5
			 * @param {object} values
			 * @return {object}
			 */
			buildTickerItemsListAttr: function( values ) {
				var attr = {
						'class': 'awb-news-ticker-item-list'
					},
					marginLeftClass;

				if ( 'marquee' === values.ticker_type ) {
					attr[ 'class' ]                += ' awb-news-ticker-item-list-run';
					attr[ 'data-awb-ticker-speed' ] = values.ticker_speed;
				} else if ( 'carousel' === values.ticker_type ) {
					attr[ 'class' ] += ' awb-news-ticker-item-list-carousel';

					marginLeftClass = ' awb-news-ticker-item-list-margin-small';
					if ( 'triangle' === values.title_shape ) {
						marginLeftClass = ' awb-news-ticker-item-list-margin-medium';
					}
					attr[ 'class' ] += marginLeftClass;

					attr[ 'data-awb-news-ticker-display-time' ] = values.carousel_display_time;
				}

				return attr;
			},

			/**
			 * Get style element.
			 *
			 * @since 3.5
			 * @param {object} values
			 * @param {object} extras
			 * @return string
			 */
			getStyleTag: function( values, extras ) {
				var style,
					uniqueClassSelector       = '.awb-news-ticker-' + this.model.get( 'cid' ),
					baseSelector              = uniqueClassSelector + '.awb-news-ticker',
					baseHoverSelector         = uniqueClassSelector + '.awb-news-ticker:hover',
					baseHoverCarouselBtns     = uniqueClassSelector + ':hover .awb-news-ticker-items-buttons',
					titleSelector             = uniqueClassSelector + ' .awb-news-ticker-title',
					itemSeparatorSelector     = uniqueClassSelector + ' .awb-news-ticker-item-separator',
					linkSelector              = uniqueClassSelector + ' .awb-news-ticker-link',
					linkHoverSelector         = uniqueClassSelector + ' .awb-news-ticker-link:hover, ' + uniqueClassSelector + ' .awb-news-ticker-link:focus',
					triangleDecoratorSelector = uniqueClassSelector + ' .awb-news-ticker-title-decorator-triangle',
					carouselListSelector      = uniqueClassSelector + ' .awb-news-ticker-item-list-carousel',
					carouselBtnWrapSelector   = uniqueClassSelector + ' .awb-news-ticker-items-buttons',
					carouselBtnsSelector      = uniqueClassSelector + ' .awb-news-ticker-prev-btn, ' + uniqueClassSelector + ' .awb-news-ticker-next-btn',
					carouselBtnsHoverSelector = uniqueClassSelector + ' .awb-news-ticker-prev-btn:hover, ' + uniqueClassSelector + ' .awb-news-ticker-next-btn:hover',
					carouselBtnsFocusSelector = uniqueClassSelector + ' .awb-news-ticker-prev-btn:focus, ' + uniqueClassSelector + ' .awb-news-ticker-next-btn:focus',
					carouselIndicatorSelector = uniqueClassSelector + ' .awb-news-ticker-carousel-indicator',
					sidePadding;

				this.dynamic_css = {};
				this.values = values;

				if ( values.posts_distance && 'marquee' === values.ticker_type ) {
					sidePadding = ( values.posts_distance / 2 ).toFixed( 1 ) + 'px';
					this.addCssProperty( itemSeparatorSelector, 'padding', '0 ' + sidePadding );
				}

				if ( values.font_size ) {
					this.addCssProperty( baseSelector, '--awb-news-ticker-font-size', values.font_size );
				}

				if ( values.line_height && ! this.isDefault( 'line_height' ) ) {
					this.addCssProperty( baseSelector, '--awb-news-ticker-line-height', values.line_height );
				}

				if ( values.title_font_color ) {
					this.addCssProperty( titleSelector, 'color', values.title_font_color );
				}

				if ( values.title_background_color ) {
					this.addCssProperty( titleSelector, 'background-color', values.title_background_color );
					if ( 'triangle' === values.title_shape ) {
						this.addCssProperty( triangleDecoratorSelector, 'color', values.title_background_color );
					}
				}

				if ( values.ticker_font_color ) {
					this.addCssProperty( itemSeparatorSelector, 'color', values.ticker_font_color );
					this.addCssProperty( linkSelector, 'color', values.ticker_font_color );
				}

				if ( values.ticker_hover_font_color ) {
					this.addCssProperty( linkHoverSelector, 'color', values.ticker_hover_font_color );

				}

				if ( values.ticker_background_color ) {
					this.addCssProperty( baseSelector, 'background-color', values.ticker_background_color );
					this.addCssProperty( carouselBtnWrapSelector, 'background-color', values.ticker_background_color );
				}

				if ( values.ticker_background_hover_color ) {
					this.addCssProperty( baseHoverSelector, 'background-color', values.ticker_background_hover_color );
					this.addCssProperty( baseHoverCarouselBtns, 'background-color', values.ticker_background_hover_color );
				}

				if ( values.ticker_indicators_color ) {
					this.addCssProperty( carouselBtnsSelector, 'color', values.ticker_indicators_color );
					this.addCssProperty( carouselIndicatorSelector, 'background-color', values.ticker_indicators_color );
				}

				if ( values.ticker_indicators_hover_color ) {
					this.addCssProperty( carouselBtnsHoverSelector, 'color', values.ticker_indicators_hover_color );
					this.addCssProperty( carouselBtnsFocusSelector, 'color', values.ticker_indicators_hover_color );
				}

				if ( values.carousel_bar_height && ! this.isDefault( 'carousel_bar_height' ) ) {
					this.addCssProperty( carouselIndicatorSelector, 'height', values.carousel_bar_height + 'px' );
				}

				if ( values.carousel_btn_border_radius && 'border' === values.carousel_arrows_style ) {
					this.addCssProperty( carouselBtnsSelector, 'border-radius', values.carousel_btn_border_radius );
				}

				if ( values.title_padding_right ) {
					this.addCssProperty( titleSelector, 'padding-right', values.title_padding_right );
				}

				if ( values.title_padding_left ) {
					this.addCssProperty( titleSelector, 'padding-left', values.title_padding_left );
				}

				if ( values.ticker_padding_right ) {
					if ( ! extras.is_rtl ) {
						this.addCssProperty( carouselBtnWrapSelector, 'padding-right', values.ticker_padding_right );
					} else {
						this.addCssProperty( carouselListSelector, 'padding-right', values.ticker_padding_right );
					}
				}

				if ( values.ticker_padding_left ) {
					if ( ! extras.is_rtl ) {
						this.addCssProperty( carouselListSelector, 'padding-left', values.ticker_padding_left );
					} else {
						this.addCssProperty( carouselBtnWrapSelector, 'padding-left', values.ticker_padding_left );
					}
				}

				if ( values.margin_top ) {
					this.addCssProperty( baseSelector, 'margin-top', values.margin_top );
				}

				if ( values.margin_right ) {
					this.addCssProperty( baseSelector, 'margin-right', values.margin_right );
				}

				if ( values.margin_bottom ) {
					this.addCssProperty( baseSelector, 'margin-bottom', values.margin_bottom );
				}

				if ( values.margin_left ) {
					this.addCssProperty( baseSelector, 'margin-left', values.margin_left );
				}

				if ( values.border_radius_top_left ) {
					this.addCssProperty( baseSelector, 'border-top-left-radius', values.border_radius_top_left );
				}

				if ( values.border_radius_top_right ) {
					this.addCssProperty( baseSelector, 'border-top-right-radius', values.border_radius_top_right );
				}

				if ( values.border_radius_bottom_right ) {
					this.addCssProperty( baseSelector, 'border-bottom-right-radius', values.border_radius_bottom_right );
				}

				if ( values.border_radius_bottom_left ) {
					this.addCssProperty( baseSelector, 'border-bottom-left-radius', values.border_radius_bottom_left );
				}

				if ( values.carousel_display_time ) {
					this.addCssProperty( carouselIndicatorSelector, 'animation-duration', values.carousel_display_time + 's' );
				}

				if ( 'yes' === values.box_shadow ) {
					this.addCssProperty( baseSelector, 'box-shadow', _.fusionGetBoxShadowStyle( values ).replace( ';', '' ) );
				}

				style = this.parseCSS();
				return style ? '<style>' + style + '</style>' : '';
			},

			/**
			 * Get the carousel buttons HTML if necessary.
			 *
			 * @since 3.5
			 * @param {object} values
			 * @return string
			 */
			getCarouselButtonsIfNecessary: function( values ) {
				var html = '',
					additionalBtnClasses;

				if ( 'carousel' === values.ticker_type ) {
					additionalBtnClasses = '';
					if ( 'border' === values.carousel_arrows_style ) {
						additionalBtnClasses = ' awb-news-ticker-btn-border';
					}

					html += '<div class="awb-news-ticker-items-buttons">';
					html += '<div class="awb-news-ticker-btn-wrapper"><button class="awb-news-ticker-prev-btn' + additionalBtnClasses + '">&#xf104;</button></div>';
					html += '<div class="awb-news-ticker-btn-wrapper"><button class="awb-news-ticker-next-btn' + additionalBtnClasses + '">&#xf105;</button></div>';
					html += '</div>';

					html += '<div class="awb-news-ticker-carousel-indicator"></div>';
				}

				return html;
			},

			/**
			 * Init.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			onInit: function() {
				// Also refresh on init, since the onPageLoad event don't trigger sometimes.
				var previewBody = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );
				previewBody.trigger( 'fusion-element-render-fusion_news_ticker', this.model.attributes.cid );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 3.5
			 * @return {void}
			 */
			afterPatch: function() {
				var previewBody = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' );
				previewBody.trigger( 'fusion-element-render-fusion_news_ticker', this.model.attributes.cid );
			}

		} );
	} );
}( jQuery ) );
