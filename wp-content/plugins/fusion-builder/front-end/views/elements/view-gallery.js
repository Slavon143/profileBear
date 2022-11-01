/* global fusionAppConfig, FusionPageBuilderViewManager, imagesLoaded, fusionBuilderText */
/* jshint -W098 */
/* eslint no-unused-vars: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Gallery View.
		FusionPageBuilder.fusion_gallery = FusionPageBuilder.ParentElementView.extend( {

			/**
			 * Image map of child element images and thumbs.
			 *
			 * @since 2.0
			 */
			imageMap: {
				images: {}
			},

			onInit: function() {
				this.fusionIsotope = new FusionPageBuilder.IsotopeManager( {
					selector: '.fusion-gallery-layout-grid, .fusion-gallery-layout-masonry',
					layoutMode: 'packery',
					itemSelector: '.fusion-gallery-column',
					isOriginLeft: jQuery( 'body.rtl' ).length ? false : true,
					resizable: true,
					initLayout: true,
					view: this,
					sortBy: 'number',
					sortAscending: true
				} );
			},

			onRender: function() {
				var galleryElements = this.$el.find( '.fusion-gallery-column' ),
					self = this;

				imagesLoaded( galleryElements, function() {
					self.setPagination();
					self.fusionIsotope.updateLayout();
					self.setOutlineControlsPosition();
				} );
			},

			/**
			 * Sets position of outlines and controls for the child elements to match column spacing.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			setOutlineControlsPosition: function() {
				var cid = this.model.get( 'cid' ),
					params = this.model.get( 'params' ),
					halfColumnSpacing = ( parseFloat( params.column_spacing ) / 2 ) + 'px',
					css = '';

				this.$el.children( 'style' ).remove();

				css += '<style type="text/css">';
				css += '.fusion-builder-live div[data-cid="' + cid + '"] .fusion-builder-live-child-element:hover:after{ margin:' + halfColumnSpacing + ';}';
				css += '.fusion-builder-live div[data-cid="' + cid + '"] .fusion-builder-live-child-element:hover .fusion-builder-module-controls-container{ bottom: ' + halfColumnSpacing + '; right:' + halfColumnSpacing + ';}';
				css += '</style>';

				this.$el.prepend( css );
			},

			/**
			 * Extendable function for when child elements get generated.
			 *
			 * @since 2.0.0
			 * @param {Object} modules An object of modules that are not a view yet.
			 * @return {void}
			 */
			onGenerateChildElements: function( modules ) {
				var self = this,
					i    = 1;


				setTimeout( function() {
					self.fusionIsotope.init();
				}, 50 );

				this.addImagesToImageMap( modules, false, false );

				// Set child counter. Used for grid layout clearfix.
				_.each( this.model.children, function( child ) {
					child.set( 'counter', i );
					i++;
				} );
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
			addImagesToImageMap: function( childrenData, async, reRender, forceQuery ) {
				var view      = this,
					queryData = {};

				async    = ( 'undefined' === typeof async ) ? true : async;
				reRender = ( 'undefined' === typeof reRender ) ?  true : reRender;

				_.each( childrenData, function( child ) {
					var params = ( 'undefined' !== typeof child.get ) ? child.get( 'params' ) : child.params,
						imageIdIsValid = ( 'undefined' !== typeof params.image_id && null !== params.image_id && '' !== params.image_id );

					if ( imageIdIsValid && ( 'undefined' === typeof view.imageMap.images[ params.image_id ] || forceQuery ) ) {
						queryData[ params.image_id ] = params;
					}
				} );

				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_gallery',
							children: queryData,
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							gallery: view.model.get( 'params' )
						}
					} )
					.done( function( response ) {
						view.updateImageMap( response, forceQuery );
						view.model.set( 'query_data', response );

						if ( reRender ) {
							view.reRender();
						}
					} );
				}
			},

			/**
			 * Update the view's image map.
			 *
			 * @since 2.0
			 * @param {Object} images - The images object to inject.
			 * @return void
			 */
			updateImageMap: function( images, forceUpdate ) {
				var imageMap = this.imageMap;

				_.each( images.images, function( image, imageId ) {
					if ( 'undefined' === typeof imageMap.images[ imageId ] || forceUpdate ) {
						imageMap.images[ imageId ] = image;
					}
				} );

				// TODO: needed ?
				this.imageMap = imageMap;
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				this.appendChildren( '.fusion-gallery-container' );
				this.fusionIsotope.reInit();
				this.checkVerticalImages();

				this.setOutlineControlsPosition();
				this.setPagination();
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {
				var attributes = {};

				// Validate values.
				this.validateValues( atts.values );
				this.values = atts.values;
				this.extras = atts.extras;

				attributes.values            = atts.values;
				attributes.query_data        = atts.query_data;
				attributes.captionStyles     = this.buildCaptionStyles( atts );
				attributes.aspectRatioStyles = this.buildAspectRatioStyles( atts );
				attributes.paginationHTML    = this.buildPaginationHTML( atts.values );

				// Create attribute objects.
				attributes.attr        = this.buildAttr( atts.values );
				attributes.wrapperAttr = this.buildWrapperAttr( atts.values );

				// Whether it has a dynamic data stream.
				attributes.usingDynamic = 'undefined' !== typeof atts.values.multiple_upload && 'Select Images' !== atts.values.multiple_upload;

				return attributes;
			},

			checkVerticalImages: function() {
				var container = this.$el.find( '.fusion-gallery-layout-grid, .fusion-gallery-layout-masonry' );

				if ( container.hasClass( 'fusion-gallery-layout-masonry' ) && 0 < container.find( '.fusion-grid-column:not(.fusion-grid-sizer)' ).not( '.fusion-element-landscape' ).length ) {
					container.addClass( 'fusion-masonry-has-vertical' );
				} else {
					container.removeClass( 'fusion-masonry-has-vertical' );
				}
			},

			/**
			 * Sets pagination.
			 *
			 * @since 3.8
			 * @return {void}
			 */
			setPagination: function() {
				var self = this,
					counter = 0,
					current = FusionPageBuilderViewManager.getView( this.model.get( 'cid' ) ),
					cid,
					childView;

				if ( ! this.model.children.length ) {
					return;
				}

				this.model.children.each( function( child ) {
					cid  = child.attributes.cid;
					childView = FusionPageBuilderViewManager.getView( cid );

					if ( 0 < self.values.limit && counter >= self.values.limit ) {
						childView.$el.addClass( 'awb-gallery-item-hidden' );
					} else {
						childView.$el.removeClass( 'awb-gallery-item-hidden' );
					}
					counter++;
				} );

				current.$el.find( '.awb-gallery-wrapper' ).attr( 'data-page', 1 );
				current.$el.find( '.awb-gallery-wrapper' ).attr( 'data-limit', self.values.limit );
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.column_spacing = ( parseFloat( values.column_spacing ) / 2 ) + 'px';
				values.bordersize     = _.fusionValidateAttrValue( values.bordersize, 'px' );
				values.border_radius  = _.fusionValidateAttrValue( values.border_radius, 'px' );

				if ( 'round' === values.border_radius ) {
					values.border_radius = '50%';
				}

				values.limit = '0' == values.limit ? fusionAppConfig.posts_per_page : values.limit;

				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var totalNumOfColumns = this.model.children.length,
					attr              = {
						class: 'fusion-gallery fusion-gallery-container fusion-child-element fusion-grid-' + values.columns + ' fusion-columns-total-' + totalNumOfColumns + ' fusion-gallery-layout-' + values.layout + ' fusion-gallery-' + this.model.get( 'cid' )
					},
					margin;

				if ( values.column_spacing ) {
					margin = ( -1 ) * parseFloat( values.column_spacing );
					attr.style = 'margin:' + margin + 'px;';
				}

				if ( '' !== values.order_by ) {
					attr[ 'data-order' ] = values.order_by;
				}

				attr[ 'data-empty' ] = this.emptyPlaceholderText;
				if ( '' !== values.aspect_ratio ) {
					attr[ 'class' ] += ' has-aspect-ratio';
				}

				return attr;
			},

			/**
			 * Builds wrapper attributes.
			 *
			 * @since 3.8
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildWrapperAttr: function( values ) {
				var attr = _.fusionVisibilityAtts( values.hide_on_mobile, {
					class: 'awb-gallery-wrapper  awb-gallery-wrapper-' + this.model.get( 'cid' ),
					style: ''
				} );

				if ( '' !==  values.limit && 0 < values.limit ) {
					attr[ 'data-limit' ] =  values.limit;
					attr[ 'data-page' ] = 1;
				}

				if ( '' !== values.load_more_btn_span ) {
					attr[ 'class' ] += ' button-span-' + values.load_more_btn_span;
				}

				if ( '' !==  values.button_alignment ) {
				attr.style += '--more-btn-alignment:' +  values.button_alignment + ';';
				}

				if ( '' !==  values.load_more_btn_color ) {
				attr.style += '--more-btn-color:' +  values.load_more_btn_color + ';';
				}

				if ( '' !==  values.load_more_btn_bg_color ) {
				attr.style += '--more-btn-bg:' +  values.load_more_btn_bg_color + ';';
				}

				if ( '' !==  values.load_more_btn_hover_color ) {
				attr.style += '--more-btn-hover-color:' +  values.load_more_btn_hover_color + ';';
				}

				if ( '' !==  values.load_more_btn_hover_bg_color ) {
				attr.style += '--more-btn-hover-bg:' +  values.load_more_btn_hover_bg_color + ';';
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

				return attr;
			},

			/**
			 * Builds pagination HTML.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildPaginationHTML: function( values ) {
				var html = '';

				if ( 0 < values.limit && ( this.model.children.length > values.limit || ( values.element_content.match( /fusion_gallery_image/g ).length - 1 ) > values.limit ) ) {
					if ( 'button' === values.pagination_type || 'infinite' === values.pagination_type ) {
						html += '<div class="fusion-loading-container fusion-clearfix awb-gallery-posts-loading-container">';
						html += '<div class="fusion-loading-spinner">';
						html += '<div class="fusion-spinner-1"></div>';
						html += '<div class="fusion-spinner-2"></div>';
						html += '<div class="fusion-spinner-3"></div>';
						html += '</div>';
						html += '<div class="fusion-loading-msg"><em>' + fusionBuilderText.gallery_loading_message + '</em></div>';
						html += '</div>';
					}

					if ( 'infinite' === values.pagination_type ) {
						html += '<div class="awb-gallery-infinite-scroll-handle is-active"></div>';
					}

					if ( 'button' === values.pagination_type ) {
						html += '<div class="awb-gallery-buttons">';
						html += '<a href="#" class="fusion-button button-flat button-default fusion-button-default-size awb-gallery-load-more-btn">' + values.load_more_btn_text + '</a>';
						html += '</div>';
					}
				}

				return html;
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
				this.baseSelector = '.fusion-gallery.fusion-gallery-' + this.model.get( 'cid' );

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
			},

			/**
			 * Builds aspect ratio styles.
			 *
			 * @since 3.7
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildAspectRatioStyles: function() {
				var selectors, aspectRatio, width, height;

				if ( '' ===  this.values.aspect_ratio ) {
					return '';
				}

				this.dynamic_css = {};
				this.baseSelector = '.fusion-gallery.fusion-gallery-' + this.model.get( 'cid' );
				selectors = [ this.baseSelector + ' .fusion-gallery-image img' ];

				// Calc Ratio
				if ( 'custom' ===  this.values.aspect_ratio && '' !==  this.values.custom_aspect_ratio ) {
					this.addCssProperty( selectors, 'aspect-ratio', `100 / ${this.values.custom_aspect_ratio}` );
				} else {
					aspectRatio = this.values.aspect_ratio.split( '-' );
					width 		= aspectRatio[ 0 ] || '';
					height 		= aspectRatio[ 1 ] || '';

					this.addCssProperty( selectors, 'aspect-ratio', `${width} / ${height}` );
				}

				//Ratio Position
				if ( '' !==  this.values.aspect_ratio_position ) {
					this.addCssProperty( selectors, 'object-position', this.values.aspect_ratio_position );
				}
				const css = this.parseCSS();

				return css;
			}

		} );

		// Fetch image_date for single image
		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_gallery_image: function( name, value, modelData, args, cid, action, model, elementView ) {
				var queryData  = {},
					reRender   = true,
					async      = true,
					parentView = FusionPageBuilderViewManager.getView( model.attributes.parent ),
					params     = jQuery.extend( true, {}, model.attributes.params ),
					imageId;

				params[ name ] = value;
				imageId        = params.image_id;

				if ( 'undefined' === typeof parentView.imageMap.images[ imageId ] && 'undefined' !== typeof value && '' !== value ) {
					queryData[ imageId ] = params;
				}
				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_gallery',
							children: queryData,
							fusion_load_nonce: fusionAppConfig.fusion_load_nonce,
							gallery: parentView.model.get( 'params' ),
							image: params
						}
					} )
					.done( function( response ) {
						parentView.updateImageMap( response );

						if ( 'undefined' !== typeof response.images[ value ] ) {
							if ( 'undefined' !== typeof response.images[ value ].image_data && 'image_id' === name && 'undefined' !== typeof response.images[ value ].image_data.url ) {
								if ( ! args.skip ) {
									elementView.changeParam( 'image', response.images[ value ].image_data.url );
								}
							}
						}

						elementView.changeParam( name, value );

						if ( reRender ) {
							elementView.reRender();
						}
					} );
				} else {
					if ( ! args.skip && 'undefined' !== typeof name ) {
						elementView.changeParam( name, value );
					}

					if ( reRender ) {
						elementView.reRender();
					}
				}
			}
		} );

		_.extend( FusionPageBuilder.Callback.prototype, {
			fusion_gallery_images: function( name, value, modelData, args, cid, action, model, view ) {
				view.model.attributes.params[ name ] = value;
				view.addImagesToImageMap( view.model.children.models, true, true, true );
			}
		} );

	} );
}( jQuery ) );
