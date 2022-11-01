/* global FusionPageBuilderViewManager */
/* jshint -W098 */
/* eslint no-empty-function: 0 */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Gallery View.
		FusionPageBuilder.fusion_gallery_image = FusionPageBuilder.ChildElementView.extend( {

			/**
			 * Runs after initial render.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				var self = this,
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				// Re-render the parent view if the child was cloned
				if ( 'undefined' !== typeof self.model.attributes.cloned && true === self.model.attributes.cloned ) {
					delete self.model.attributes.cloned;
					parentView.reRender();
					parentView.fusionIsotope.reloadItems();
				}

				// Update isotope layout
				setTimeout( function() {
					if ( 'undefined' !== typeof parentView.fusionIsotope ) {
						parentView.fusionIsotope.append( self.$el );
						parentView.checkVerticalImages();
					}
				}, 50 );

				this.initLightbox();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforePatch: function() {
			},

			/**
			 * (Re-)Inits the lightbox.
			 *
			 * @since 2.0.3
			 * @return {void}
			 */
			initLightbox: function( remove ) {
				var parentCid           = this.model.get( 'parent' ),
					galleryLightboxName = 'iLightbox[awb_gallery_' + parentCid + ']',
					galleryLightbox,
					elements,
					link;

				if ( 'object' !== typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.$ilInstances ) {
					return;
				}

				galleryLightbox = jQuery( '#fb-preview' )[ 0 ].contentWindow.$ilInstances[ galleryLightboxName ];

				if ( 'undefined' !== typeof remove && remove ) {
					elements = this.$el.closest( '.fusion-gallery' ).find( '.fusion-builder-live-child-element:not([data-cid="' + this.model.get( 'cid' ) + '"]' ).find( '[data-rel="' + galleryLightboxName + '"]' );
				} else {
					elements = this.$el.closest( '.fusion-gallery' ).find( '[data-rel="' + galleryLightboxName + '"]' );
				}

				link = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( elements );

				if ( 'object' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox ) {
					if ( 'undefined' !== typeof galleryLightbox ) {
						galleryLightbox.destroy();

						link.iLightBox( jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox.prepare_options( galleryLightboxName ) );
					}
				}
			},

			/**
			 * Runs before view is removed.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			beforeRemove: function() {
				var self = this,
parentCid           = this.model.get( 'parent' ),
					parentView          = FusionPageBuilderViewManager.getView( parentCid );

				if ( 'undefined' !== typeof parentView.fusionIsotope ) {
					parentView.fusionIsotope.remove( self.$el );
				}

				if ( 'function' === typeof parentView.checkVerticalImages ) {
					parentView.checkVerticalImages();
				}

				setTimeout( function() {

					if ( 'undefined' !== typeof parentView.setPagination ) {
						parentView.setPagination();
					}

					if ( 'undefined' !== typeof parentView.fusionIsotope ) {
						parentView.fusionIsotope.reloadItems();
					}
				}, 100 );

				self.initLightbox( true );
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var parentCid = this.model.get( 'parent' ),
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) );

				if ( 'undefined' !== typeof this.model.attributes.selectors ) {
					this.model.attributes.selectors[ 'class' ] += ' ' + this.className;
					this.setElementAttributes( this.$el, this.model.attributes.selectors );
				}

				this.initLightbox();

				// Force re-render for child option changes.
				setTimeout( function() {
					jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( 'body' ).trigger( 'fusion-element-render-fusion_gallery', parentCid );
					parentView.fusionIsotope.updateLayout();
				}, 100 );
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The attributes.
			 * @return {Object} - Returns the attributes.
			 */
			filterTemplateAtts: function( atts ) {
				var attributes   = {},
					parentView   = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					imageData    = 'undefined' !== typeof parentView.imageMap ? parentView.imageMap.images[ this.model.attributes.params.image_id ] : undefined,
					parentValues = atts.parentValues,
					orientation  = '';

				// Validate values.
				this.validateValues( atts.values );
				this.validateValues( atts.parentValues );

				attributes.values       = atts.values;
				attributes.parentValues = atts.parentValues;

				// Added
				attributes.imageData        = imageData;
				attributes.galleryLayout    = parentValues.layout;
				attributes.galleryLightbox  = parentValues.lightbox;
				attributes.galleryColumns   = parentValues.columns;
				attributes.imageWrapperAttr = this.buildImageWrapperAttr( parentValues );
				attributes.counter          = this.model.get( 'counter' );
				attributes.captionHtml      = this.generateCaption( parentValues, atts.values );

				// Create attribute objects.
				attributes.imagesAttr = this.buildImagesAttr( atts.values );

				if ( 'undefined' !== typeof imageData && 'undefined' !== typeof imageData.element_orientation_class && false !== imageData.element_orientation_class ) {
					orientation = imageData.element_orientation_class;
				} else {
					this.$el.removeClass( 'fusion-element-landscape' );
				}

				this.$el.addClass( 'fusion-grid-column fusion-gallery-column ' + orientation );

				if ( -1 !== jQuery.inArray( parentValues.caption_style, [ 'above', 'below' ] ) ) {
					this.$el.addClass( 'awb-imageframe-style awb-imageframe-style-' + parentValues.caption_style + ' awb-imageframe-style-' + this.model.get( 'parent' ) );
				} else {
					this.$el.removeClass( 'awb-imageframe-style awb-imageframe-style-above awb-imageframe-style-below' );
				}

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
				values.column_spacing = ( parseFloat( values.column_spacing ) / 2 ) + 'px';
				values.bordersize     = _.fusionValidateAttrValue( values.bordersize, 'px' );
				values.border_radius  = _.fusionValidateAttrValue( values.border_radius, 'px' );

				if ( 'round' === values.border_radius ) {
					values.border_radius = '50%';
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object} - Returns the attributes.
			 */
			buildAttr: function( values ) {
				var imageIds          = values.image_ids.split( ',' ),
					totalNumOfColumns = imageIds.length,
					attr              = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-gallery fusion-gallery-container fusion-grid-' + values.columns + ' fusion-columns-total-' + totalNumOfColumns + ' fusion-gallery-layout-' + values.layout
					} ),
					margin;

				if ( values.column_spacing ) {
					margin = ( -1 ) * parseFloat( values.column_spacing );
					attr.style = 'margin:' + margin + 'px;';
				}

				return attr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object} - Returns the attributes for the image wrapper.
			 */
			buildImageWrapperAttr: function( values ) {
				var imageWrapperAttr = {
					class: 'fusion-gallery-image',
					style: ''
				};

				if ( '' !== values.bordersize && 0 !== values.bordersize ) {
					imageWrapperAttr.style += 'border:' + values.bordersize + ' solid ' + values.bordercolor + ';';

					if ( '0' != values.border_radius && '0px' !== values.border_radius && 'px' !== values.border_radius ) {
						imageWrapperAttr.style += '-webkit-border-radius:' + values.border_radius + ';border-radius:' + values.border_radius + ';';

						if ( '50%' === values.border_radius || 100 < parseInt( values.border_radius, 10 ) ) {
							imageWrapperAttr.style += '-webkit-mask-image:-webkit-radial-gradient(circle, white, black);';
						}
					}
				}

				if ( 'liftup' === values.hover_type && -1 !== jQuery.inArray( values.caption_style, [ 'off', 'above', 'below' ] ) ) {
					imageWrapperAttr[ 'class' ] += ' fusion-gallery-image-liftup';
				}

				// Caption style.
				if ( -1 === jQuery.inArray( values.caption_style, [ 'off', 'above', 'below' ] ) ) {
					imageWrapperAttr[ 'class' ] += ' awb-imageframe-style awb-imageframe-style-' + values.caption_style;
				}

				return imageWrapperAttr;
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @param {Object} queryData - The query data.
			 * @return {Object} - Returns the image attributes.
			 */
			buildImagesAttr: function( values ) {
				var imagesAttr = {},
					imageId    = this.model.attributes.params.image_id,
					parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
					image      = 'undefined' !== typeof parentView.imageMap ? parentView.imageMap.images[ imageId ] : undefined,
					columnSpacing,
					isPortrait,
					isLandscape,
					borderColor;

				if ( 'undefined' === typeof image ) {
					image = {};
				}

				columnSpacing = 0;
				isPortrait    = false;
				isLandscape   = false;
				borderColor   = jQuery.AWB_Color( values.bordercolor );

				imagesAttr = {};

				if ( 'masonry' === values.layout ) {
					imagesAttr.masonryWrapper = {
						style: '',
						class: 'fusion-masonry-element-container'
					};

					if ( image.url ) {
						imagesAttr.masonryWrapper.style += 'background-image:url(' + image.url + ');';
					}

					if ( 'undefined' !== typeof image.image_data && true !== image.image_data.specific_element_orientation_class ) {
						image.element_orientation_class = _.fusionGetElementOrientationClass( { imageWidth: image.image_data.width, imageHeight: image.image_data.height }, values.gallery_masonry_grid_ratio, values.gallery_masonry_width_double );
					}
					image.element_base_padding = _.fusionGetElementBasePadding( image.element_orientation_class );

					if ( image.element_base_padding ) {
						columnSpacing = 0;

						if ( 'undefined' !== typeof image.element_orientation_class && false !== image.element_orientation_class ) {
							isLandscape   = -1 !== image.element_orientation_class.indexOf( 'fusion-element-landscape' );
							isPortrait    = -1 !== image.element_orientation_class.indexOf( 'fusion-element-portrait' );
						}

						if ( isLandscape || isPortrait ) {
							columnSpacing = 2 * parseFloat( values.column_spacing );
						}

						// Calculate the correct size of the image wrapper container, based on orientation and column spacing.
						if ( values.bordersize && 'transparent' !== values.bordercolor && 0 !== borderColor.alpha() ) {
							if ( isLandscape || isPortrait ) {
								columnSpacing += 2 * parseFloat( values.bordersize );
							}
						}

						if ( isLandscape && isPortrait ) {
							imagesAttr.masonryWrapper.style += 'padding-top:calc((100% - ' + columnSpacing + 'px) * ' + image.element_base_padding + ' + ' + columnSpacing + 'px);';
						} else if ( isLandscape ) {
							imagesAttr.masonryWrapper.style += 'padding-top:calc((100% - ' + columnSpacing + 'px) * ' + image.element_base_padding + ');';
						} else if ( isPortrait ) {
							imagesAttr.masonryWrapper.style += 'padding-top:calc(100%  * ' + image.element_base_padding + ' + ' + columnSpacing + 'px);';
						} else {
							imagesAttr.masonryWrapper.style += 'padding-top:calc(100%  * ' + image.element_base_padding + ');';
						}
					}

					// Masonry image position.
					if ( '' !== values.masonry_image_position ) {
						imagesAttr.masonryWrapper.style += 'background-position:' + values.masonry_image_position + ';';
					}
				}

				imagesAttr.images = {
					style: '',
					class: ''
				};

				if ( 'liftup' !== values.hover_type ) {
					imagesAttr.images[ 'class' ] += ' hover-type-' + values.hover_type;
				}

				if ( '' !== values.column_spacing ) {
					imagesAttr.images.style = 'padding:' + values.column_spacing + ';';
				}

				if ( values.lightbox && 'no' !== values.lightbox ) {
					imagesAttr.link = {
						href: image.pic_link,
						class: 'fusion-lightbox'
					};

					imagesAttr.link[ 'data-rel' ] = 'iLightbox[awb_gallery_' + this.model.get( 'parent' ) + ']';

					// TODO: fix
					// if ( 'undefined' !== typeof image.image_data ) {

					// 	if ( -1 !== values.lightbox_content.indexOf( 'title' ) ) {
					// 		imagesAttr.link['data-title'] = image.image_data.title;
					// 		imagesAttr.link.title         = image.image_data.title;
					// 	}
					// 	if ( -1 !== values.lightbox_content.indexOf( 'caption' ) ) {
					// 		imagesAttr.link['data-caption'] = image.image_data.caption;
					// 	}
					// }
				}

				return imagesAttr;
			},

			/**
			 * Generate caption markup.
			 *
			 * @since 3.5
			 * @param {string} values - The values object.
			 * @return {string}
			 */
			generateCaption: function( values, childValues ) {
				var content = '<div class="awb-imageframe-caption-container"><div class="awb-imageframe-caption">',
					imageId,
					parentView,
					image,
					title = '',
					caption = '',
					title_tag = '';

				if ( 'off' === values.caption_style ) {
					return '';
				}

				imageId    = this.model.attributes.params.image_id,
				parentView = FusionPageBuilderViewManager.getView( this.model.get( 'parent' ) ),
				image      = 'undefined' !== typeof parentView.imageMap ? parentView.imageMap.images[ imageId ] : undefined;

				if ( 'undefined' === typeof image ) {
					return '';
				}

				// from image data.
				if ( image.image_data.title ) {
					title = image.image_data.title;
				}
				if ( image.image_data.caption ) {
					caption = image.image_data.caption;
				}

				// from element data.
				if ( '' !== childValues.image_title ) {
					title = childValues.image_title;
				}
				if ( '' !== childValues.image_caption ) {
					caption = childValues.image_caption;
				}

				if ( '' !== title ) {
					title_tag = 'div' === values.caption_title_tag ? 'div' : 'h' + values.caption_title_tag;
					content += '<' + title_tag + ' class="awb-imageframe-caption-title">' + title + '</' + title_tag + '>';
				}

				if ( '' !== caption ) {
					content += '<p class="awb-imageframe-caption-text">' + caption + '</p>';
				}
				content += '</div></div>';

				return content;
			}
		} );
	} );
}( jQuery ) );
