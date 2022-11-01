/* global FusionPageBuilderViewManager */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Image Carousel Parent View.
		FusionPageBuilder.fusion_images = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Image map of child element images and thumbs.
			 *
			 * @since 2.0
			 */
			imageMap: {},

			/**
			 * Initial data has run.
			 *
			 * @since 2.0
			 */
			initialData: false,

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this.appendChildren( '.fusion-carousel-holder' );
				this._refreshJs();
			},

			onRender: function() {
				var columnView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );
				setTimeout( function() {
					if ( columnView && 'function' === typeof columnView._equalHeights ) {
						columnView._equalHeights();
					}
				}, 500 );
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {},
					images = window.FusionPageBuilderApp.findShortcodeMatches( atts.params.element_content, 'fusion_image' ),
					imageElement,
					imageElementAtts;

				this.model.attributes.showPlaceholder = false;

				if ( 1 <= images.length ) {
					imageElement     = images[ 0 ].match( window.FusionPageBuilderApp.regExpShortcode( 'fusion_image' ) );
					imageElementAtts = '' !== imageElement[ 3 ] ? window.wp.shortcode.attrs( imageElement[ 3 ] ) : '';

					this.model.attributes.showPlaceholder = ( 'undefined' === typeof imageElementAtts.named || 'undefined' === typeof imageElementAtts.named.image ) ? true : false;
				}

				// Validate values.
				this.validateValues( atts.values );
				this.extras = atts.extras;
				this.values = atts.values;

				// Create attribute objects
				attributes.attr          = this.buildAttr( atts.values );
				attributes.attrCarousel  = this.buildCarouselAttr( atts.values );
				attributes.captionStyles = this.buildCaptionStyles( atts );

				// Whether it has a dynamic data stream.
				attributes.usingDynamic = 'undefined' !== typeof atts.values.multiple_upload && 'Select Images' !== atts.values.multiple_upload;

				// Any extras that need passed on.
				attributes.show_nav = atts.values.show_nav;

				return attributes;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.column_spacing = _.fusionValidateAttrValue( values.column_spacing, 'px' );
				values.margin_bottom  = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left    = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right   = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top     = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'fusion-image-carousel fusion-image-carousel-' + this.model.get( 'cid' ),
					style: ''
				} );

				attr[ 'class' ] += ' fusion-image-carousel-' + values.picture_size;

				if ( true === this.model.attributes.showPlaceholder ) {
					attr[ 'class' ] += ' fusion-show-placeholder';
				}

				if ( '' !== values.margin_top ) {
					attr.style += 'margin-top:' + values.margin_top + ';';
				}

				if ( '' !== values.margin_right ) {
					attr.style += 'margin-right:' + values.margin_right + ';';
				}

				if ( '' !== values.margin_bottom ) {
					attr.style += 'margin-bottom:' + values.margin_bottom + ';';
				}

				if ( '' !== values.margin_left ) {
					attr.style += 'margin-left:' + values.margin_left + ';';
				}

				if ( 'yes' === values.lightbox ) {
					attr[ 'class' ] += ' lightbox-enabled';
				}

				if ( 'yes' === values.border ) {
					attr[ 'class' ] += ' fusion-carousel-border';
				}

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( -1 !== jQuery.inArray( values.caption_style, [ 'above', 'below' ] ) ) {
					attr[ 'class' ] += ' awb-image-carousel-top-below-caption awb-imageframe-style awb-imageframe-style-' + values.caption_style + ' awb-imageframe-style-' + this.model.get( 'cid' );
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildCarouselAttr: function( values ) {
				var attr = {
					class: 'fusion-carousel',
					style: ''
				};

				attr[ 'data-autoplay' ]    = values.autoplay;
				attr[ 'data-columns' ]     = values.columns;
				attr[ 'data-itemmargin' ]  = values.column_spacing.toString();
				attr[ 'data-itemwidth' ]   = '180';
				attr[ 'data-touchscroll' ] = values.mouse_scroll;
				attr[ 'data-imagesize' ]   = values.picture_size;
				attr[ 'data-scrollitems' ] = values.scroll_items;

				return attr;
			},

			/**
			 * Extendable function for when child elements get generated.
			 *
			 * @since 2.0.0
			 * @param {Object} modules An object of modules that are not a view yet.
			 * @return {void}
			 */
			onGenerateChildElements: function( modules ) {
				this.addImagesToImageMap( modules, false, false );
			},

			/**
			 * Add images to the view's image map.
			 *
			 * @since 2.0
			 * @param {Object} childrenData - The children for which images need added to the map.
			 * @param bool async - Determines if the AJAX call should be async.
			 * @param bool async - Determines if the view should be re-rendered.
			 * @return void
			 */
			addImagesToImageMap: function( childrenData, async, reRender ) {
				var view      = this,
					queryData = {};

				async     = ( 'undefined' === typeof async ) ? true : async;
				reRender  = ( 'undefined' === typeof reRender ) ?  true : reRender;

				view.initialData = true;

				_.each( childrenData, function( child ) {
					var params = ( 'undefined' !== typeof child.get ) ? child.get( 'params' ) : child.params,
						cid    = ( 'undefined' !== typeof child.get ) ? child.get( 'cid' ) : child.cid,
						image  = params.image;

					if ( 'undefined' === typeof view.imageMap[ image ] && image ) {
						queryData[ cid ] = params;
					}
				} );

				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: window.fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_image_carousel_children_data',
							children: queryData,
							fusion_load_nonce: window.fusionAppConfig.fusion_load_nonce
						}
					} )
					.done( function( response ) {
						view.updateImageMap( response );

						_.each( response, function( imageSizes, image ) {
							if ( 'undefined' === typeof view.imageMap[ image ] ) {
								view.imageMap[ image ] = imageSizes;
							}
						} );

						view.model.set( 'query_data', response );

						if ( reRender ) {
							view.reRender();
						}
					} );
				} else if ( reRender ) {
					view.reRender();
				}
			},

			/**
			 * Update the view's image map.
			 *
			 * @since 2.0
			 * @param {Object} images - The images object to inject.
			 * @return void
			 */
			updateImageMap: function( images ) {
				var imageMap = this.imageMap;

				_.each( images, function( imageSizes, image ) {
					if ( 'undefined' === typeof imageMap[ image ] ) {
						imageMap[ image ] = imageSizes;
					}
				} );
			},

			/**
			 * Builds caption styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildCaptionStyles: function( atts ) {
				var selectors, font_styles, sides, marginName, css, media,
responsive = '';
				this.dynamic_css  = {};
				this.baseSelector = '.fusion-image-carousel.fusion-image-carousel-' + this.model.get( 'cid' );

				if ( 'off' === atts.values.caption_style ) {
					return '';
				}

				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'above', 'below' ] ) ) {
					this.baseSelector = '.awb-imageframe-style.awb-imageframe-style-' + this.model.get( 'cid' );
				}

				selectors = [ this.baseSelector + ' .awb-imageframe-caption-container .awb-imageframe-caption-title' ];
				// title color.
				if ( ! this.isDefault( 'caption_title_color' ) ) {
					this.addCssProperty( selectors, 'color', atts.values.caption_title_color, true );
				}
				// title size.
				if ( ! this.isDefault( 'caption_title_size' ) ) {
					this.addCssProperty( selectors, 'font-size', _.fusionGetValueWithUnit( atts.values.caption_title_size ), true );
				}
				// title font.
				font_styles = _.fusionGetFontStyle( 'caption_title_font', atts.values, 'object' );
				for ( rule in font_styles ) { // eslint-disable-line
					var value = font_styles[ rule ]; // eslint-disable-line

					this.addCssProperty( selectors, rule, value, true ); // eslint-disable-line
				}
				// title transform.
				if ( ! this.isDefault( 'caption_title_transform' ) ) {
					this.addCssProperty( selectors, 'text-transform', atts.values.caption_title_transform, true );
				}
				// Line height.
				if ( ! this.isDefault( 'caption_title_line_height' ) ) {
					this.addCssProperty( selectors, 'line-height', atts.values.caption_title_line_height, true );
				}
				// Letter spacing.
				if ( ! this.isDefault( 'caption_title_letter_spacing' ) ) {
					this.addCssProperty( selectors, 'letter-spacing', _.fusionGetValueWithUnit( atts.values.caption_title_letter_spacing ), true );
				}

				selectors = [ this.baseSelector + ' .awb-imageframe-caption-container .awb-imageframe-caption-text' ];
				// text color.
				if ( ! this.isDefault( 'caption_text_color' ) ) {
					this.addCssProperty( selectors, 'color', atts.values.caption_text_color );
				}
				// text size.
				if ( ! this.isDefault( 'caption_text_size' ) ) {
					this.addCssProperty( selectors, 'font-size', _.fusionGetValueWithUnit( atts.values.caption_text_size ) );
				}
				// text font.
				font_styles = _.fusionGetFontStyle( 'caption_text_font', atts.values, 'object' );
				for ( rule in font_styles ) { // eslint-disable-line
					var value = font_styles[ rule ]; // eslint-disable-line

					this.addCssProperty( selectors, rule, value, true ); // eslint-disable-line
				}
				// text transform.
				if ( ! this.isDefault( 'caption_text_transform' ) ) {
					this.addCssProperty( selectors, 'text-transform', atts.values.caption_text_transform );
				}
				// Line height.
				if ( ! this.isDefault( 'caption_text_line_height' ) ) {
					this.addCssProperty( selectors, 'line-height', atts.values.caption_text_line_height );
				}
				// Letter spacing.
				if ( ! this.isDefault( 'caption_text_letter_spacing' ) ) {
					this.addCssProperty( selectors, 'letter-spacing', _.fusionGetValueWithUnit( atts.values.caption_text_letter_spacing ) );
				}

				// Border color.
				if ( 'resa' === atts.values.caption_style && ! this.isDefault( 'caption_border_color' ) ) {
					selectors = [ this.baseSelector + ' .awb-imageframe-caption-container:before' ];
					this.addCssProperty( selectors, 'border-top-color', atts.values.caption_border_color );
					this.addCssProperty( selectors, 'border-bottom-color', atts.values.caption_border_color );
					selectors = [ this.baseSelector + ' .awb-imageframe-caption-container:after' ];
					this.addCssProperty( selectors, 'border-right-color', atts.values.caption_border_color );
					this.addCssProperty( selectors, 'border-left-color', atts.values.caption_border_color );
				}

				if ( 'dario' === atts.values.caption_style && ! this.isDefault( 'caption_border_color' ) ) {
					selectors = [ this.baseSelector + ' .awb-imageframe-caption .awb-imageframe-caption-title:after' ];
					this.addCssProperty( selectors, 'background', atts.values.caption_border_color );
				}

				// Overlay color.
				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'dario', 'resa', 'schantel', 'dany', 'navin' ] ) ) {
					selectors = [ this.baseSelector + ' .awb-imageframe-style' ];
					this.addCssProperty( selectors, 'background', atts.values.caption_overlay_color );
				}

				// Background color.
				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'schantel', 'dany' ] ) && ! this.isDefault( 'caption_background_color' ) ) {
					selectors = [ this.baseSelector + ' .awb-imageframe-caption-container .awb-imageframe-caption-text' ];
					this.addCssProperty( selectors, 'background', atts.values.caption_background_color );
				}

				// Caption margin.
				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'above', 'below' ] ) ) {
					sides     = [ 'top', 'right', 'bottom', 'left' ];
					selectors = [ this.baseSelector + ' .awb-imageframe-caption-container' ];

					_.each( sides, function( side ) {
						marginName = 'caption_margin_' + side;

						if ( ! this.isDefault( marginName ) ) {
							this.addCssProperty( selectors, 'margin-' + side, _.fusionGetValueWithUnit( atts.values[ marginName ] ) );
						}
					}, this );

					if ( ! this.isDefault( 'caption_title' ) ) {
						selectors = [ this.baseSelector + ' .awb-imageframe-caption-container .awb-imageframe-caption-text' ];
						this.addCssProperty( selectors, 'margin-top', '0.5em' );
					}
				}

				css = this.parseCSS();

				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'above', 'below' ] ) ) {
					_.each( [ '', 'medium', 'small' ], function( size ) {
						var key = 'caption_align' + ( '' === size ? '' : '_' + size );

						// Check for default value.
						if ( this.isDefault( key ) ) {
							return;
						}

						this.dynamic_css  = {};

						// Build responsive alignment.
						selectors = [ this.baseSelector + ' .awb-imageframe-caption-container' ];
						this.addCssProperty( selectors, 'text-align', atts.values[ key ] );

						if ( '' === size ) {
							responsive += this.parseCSS();
						} else {
							media       = '@media only screen and (max-width:' + this.extras[ 'visibility_' + size ] + 'px)';
							responsive += media + '{' + this.parseCSS() + '}';
						}
					}, this );
					css += responsive;
				}

				return ( css ) ? '<style>' + css + '</style>' : '';
			}
		} );

		// Image carousel children data callback.
		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_carousel_images: function( name, value, modelData, args, cid, action, model, view ) { // jshint ignore: line
				view.model.attributes.params[ name ] = value;

				// TODO: on initial load we shouldn't really need to re-render, but may cause issues.
				view.addImagesToImageMap( view.model.children.models, true, view.initialData );

			}
		} );
	} );
}( jQuery ) );
