/* global fusionAllElements, FusionApp, FusionPageBuilderApp, builderConfig */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {
	jQuery( document ).ready( function() {

		// Image Frame Element View.
		FusionPageBuilder.fusion_imageframe = FusionPageBuilder.ElementView.extend( {

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			onRender: function() {
				this.afterPatch();
			},

			/**
			 * Runs after view DOM is patched.
			 *
			 * @since 2.0
			 * @return {void}
			 */
			afterPatch: function() {
				var params = this.model.get( 'params' ),
					link  = jQuery( '#fb-preview' )[ 0 ].contentWindow.jQuery( this.$el.find( '.fusion-lightbox' ) );

				this.$el.removeClass( 'fusion-element-alignment-right fusion-element-alignment-left' );
				if ( ! this.flexDisplay() ) {
					if ( 'undefined' !== typeof params.align && ( 'right' === params.align || 'left' === params.align ) ) {
						this.$el.addClass( 'fusion-element-alignment-' + params.align );
					}
				}

				if ( 'object' === typeof jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox ) {
					if ( 'undefined' !== typeof this.iLightbox ) {
						this.iLightbox.destroy();
					}

					if ( link.length ) {
						this.iLightbox = link.iLightBox( jQuery( '#fb-preview' )[ 0 ].contentWindow.avadaLightBox.prepare_options( 'single' ) );
					}
				}
			},

			/**
			 * Modify template attributes.
			 *
			 * @since 2.0
			 * @return {Object}
			 */
			filterTemplateAtts: function( atts ) {

				if ( 'undefined' !== typeof atts.values.element_content ) {

					this.isFlex 	  = this.flexDisplay();
					// Validate values.
					this.validateValues( atts.values );
					// Create attribute objects
					this.values       = atts.values;
					this.extras       = atts.extras;
					atts.isFlex       = this.isFlex;
					atts.attr         = this.buildAttr( atts.values );
					atts.contentAttr  = this.buildContentAttr( atts.values );
					atts.linkAttr     = this.buildLinktAttr( atts.values );
					atts.borderRadius = this.buildBorderRadius( atts.values );
					atts.imgStyles    = this.buildImgStyles( atts.values );
					if ( this.isFlex ) {
						atts.responsiveAttr = this.buildResponsiveAttr( atts.values );
					}

					this.buildElementContent( atts );


					atts.liftupClasses = this.buildLiftupClasses( atts );
					atts.liftupStyles  = this.buildLiftupStyles( atts );
					atts.captionHtml   = this.generateCaption( atts );

					//styles
					atts.maskStyles        = this.buildMaskStyles( atts );
					atts.aspectRatioStyles = this.buildAspectRatioStyles( atts );
					atts.marginStyles      = this.buildMarginStyles( atts );

					// Add min height sticky.
					atts.stickyStyles = '' !== atts.values.sticky_max_width ? '<style>.fusion-sticky-container.fusion-sticky-transition .imageframe-cid' + this.model.get( 'cid' ) + '{ max-width:' + _.fusionGetValueWithUnit( atts.values.sticky_max_width ) + ' !important; }</style>' : false;
					atts.filter_style_block = _.fusionGetFilterStyleElem( atts.values, '.imageframe-cid' + this.model.get( 'cid' ), this.model.get( 'cid' )  );
				}

				return atts;
			},

			/**
			 * Modifies the values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {void}
			 */
			validateValues: function( values ) {
				values.borderradius  = _.fusionValidateAttrValue( values.borderradius, 'px' );
				values.bordersize    = _.fusionValidateAttrValue( values.bordersize, 'px' );
				values.blur          = _.fusionValidateAttrValue( values.blur, 'px' );
				values.margin_bottom = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_left   = _.fusionValidateAttrValue( values.margin_left, 'px' );
				values.margin_right  = _.fusionValidateAttrValue( values.margin_right, 'px' );
				values.margin_top    = _.fusionValidateAttrValue( values.margin_top, 'px' );

				// If caption style used then disable style type.
				if ( -1 === jQuery.inArray( values.caption_style, [ 'off', 'above', 'below' ] ) ) {
					values.style_type = 'none';
				}

				// If mask used disable style type.
				if ( values.mask ) {
					values.style_type = 'none';
				}

				if ( ! values.style ) {
					values.style = values.style_type;
				}
				if ( values.borderradius && 'bottomshadow' === values.style ) {
					values.borderradius = '0';
				}

				if ( 'round' === values.borderradius ) {
					values.borderradius = '50%';
				}

			},

			/**
			 * Builds responsive container attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildResponsiveAttr: function( values ) {
				var attr = {
					style: '',
					class: ''
				},
				alignLarge  = values.align && 'none' !== values.align ? values.align : false,
				alignMedium = values.align_medium && 'none' !== values.align_medium ? values.align_medium : false,
				alignSmall  = values.align_small && 'none' !== values.align_small ? values.align_small : false;

				if ( this.isFlex ) {
					attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );
				}

				if ( alignLarge ) {
					attr.style += 'text-align:' + alignLarge + ';';
				}

				if ( alignMedium && alignLarge !== alignMedium ) {
					attr[ 'class' ] += ' md-text-align-' + alignMedium;
				}

				if ( alignSmall && alignLarge !== alignSmall ) {
					attr[ 'class' ] += ' sm-text-align-' + alignSmall;
				}

				if ( -1 !== jQuery.inArray( values.caption_style, [ 'above', 'below' ] ) ) {
					attr[ 'class' ] += ' awb-imageframe-style awb-imageframe-style-' + values.caption_style + ' awb-imageframe-style-' + this.model.get( 'cid' );
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
			buildAttr: function( values ) {

				// Main wrapper attributes
				var attr = {
						class: 'fusion-imageframe',
						style: ''
					},
					imgStyles,
					styleColorVal = values.stylecolor ? values.stylecolor : '',
					styleColor    = ( 0 === styleColorVal.indexOf( '#' ) ) ? jQuery.AWB_Color( styleColorVal ).alpha( 0.3 ).toVarOrRgbaString() : jQuery.AWB_Color( styleColorVal ).toVarOrRgbaString(),
					blur          = values.blur,
					blurRadius    = ( parseInt( blur, 10 ) + 4 ) + 'px';

				if (  ! this.isFlex ) {
					attr = _.fusionVisibilityAtts( values.hide_on_mobile, attr );

					attr[ 'class' ] += ' fusion-imageframe-align-' + values.align;
				}

				attr[ 'class' ] += _.fusionGetStickyClass( values.sticky_display );

				if ( ! values.style ) {
					values.style = values.style_type;
				}

				imgStyles   = '';

				// Border style only if not using mask.
				if ( '' === values.mask ) {
					if ( '' != values.bordersize && '0' != values.bordersize && '0px' !== values.bordersize ) {
						imgStyles += 'border:' + values.bordersize + ' solid ' + values.bordercolor + ';';
					}

					if ( '0' != values.borderradius && '0px' !== values.borderradius ) {
						imgStyles += '-webkit-border-radius:' + values.borderradius + ';-moz-border-radius:' + values.borderradius + ';border-radius:' + values.borderradius + ';';

						if ( '50%' === values.borderradius || 100 < parseFloat( values.borderradius ) ) {
							imgStyles += '-webkit-mask-image: -webkit-radial-gradient(circle, white, black);';
						}
					}
				}

				if ( 'glow' === values.style ) {
					imgStyles += '-moz-box-shadow: 0 0 ' + blur + ' ' + styleColor + ';-webkit-box-shadow: 0 0 ' + blur + ' ' + styleColor + ';box-shadow: 0 0 ' + blur + ' ' + styleColor + ';';
				} else if ( 'dropshadow' === values.style ) {
					imgStyles += '-moz-box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + styleColor + ';-webkit-box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + styleColor + ';box-shadow: ' + blur + ' ' + blur + ' ' + blurRadius + ' ' + styleColor + ';';
				}

				if ( '' !== imgStyles ) {
					attr.style += imgStyles;
				}

				attr[ 'class' ] += ' imageframe-' + values.style + ' imageframe-cid' + this.model.get( 'cid' );

				if ( values.z_index ) {
					attr.style += 'z-index:' + values.z_index + ';';
				}

				if ( 'bottomshadow' === values.style ) {
					attr[ 'class' ] += ' element-bottomshadow';
				}

				if ( '' !== values.mask ) {
					attr[ 'class' ] += ' has-mask';
				}
				if ( '' !== values.aspect_ratio ) {
					attr[ 'class' ] += ' has-aspect-ratio';
				}

				if ( 'liftup' !== values.hover_type && -1 !== jQuery.inArray( values.caption_style, [ 'off', 'above', 'below' ] ) ) {
					if ( ! this.isFlex ) {
						if ( 'left' === values.align ) {
							attr.style += 'margin-right:25px;float:left;';
						} else if ( 'right' === values.align ) {
							attr.style += 'margin-left:25px;float:right;';
						}
					}

					attr[ 'class' ] += ' hover-type-' + values.hover_type;
				}

				if ( '' !== values.max_width && '' === values.aspect_ratio ) {
					attr.style += 'max-width:' + _.fusionGetValueWithUnit( values.max_width ) + '';
				}

				// Caption style.
				if ( -1 === jQuery.inArray( values.caption_style, [ 'off', 'above', 'below' ] ) ) {
					attr[ 'class' ] += ' awb-imageframe-style awb-imageframe-style-' + values.caption_style;
				}

				if ( 'undefined' !== typeof values[ 'class' ] && '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( 'undefined' !== typeof values.id && '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds link attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildLinktAttr: function( values ) {

				// Link Attributes
				var linkAttr = {};
				if ( 'yes' === values.lightbox ) {

					// Set the lightbox image to the dedicated link if it is set.
					if ( '' !== values.lightbox_image ) {
						values.pic_link = values.lightbox_image;
					}

					linkAttr.href  = values.pic_link;
					linkAttr[ 'class' ] = 'fusion-lightbox imageframe-shortcode-link';

					if ( '' !== values.gallery_id || '0' === values.gallery_id ) {
						linkAttr[ 'data-rel' ] = 'iLightbox[' + values.gallery_id + ']';
					} else {
						linkAttr[ 'data-rel' ] = 'iLightbox[image-' + this.model.get( 'cid' ) + ']';
					}
				} else if ( values.link ) {
					linkAttr[ 'class' ]  = 'fusion-no-lightbox';
					linkAttr.href   = values.link;
					linkAttr.target = values.linktarget;
					if ( '_blank' === values.linktarget ) {
						linkAttr.rel = 'noopener noreferrer';
					}
				}

				return linkAttr;
			},

			/**
			 * Builds content attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildContentAttr: function( values ) {
				var contentAttr = {},
					title       = '',
					src         = '';

				// values.image_id = '';

				// Could add JS to get image dimensions if necessary.
				if ( ! values.element_content ) {
					return 'no_image_set';
				}
				// eslint-disable-next-line no-useless-escape
				src = values.element_content.match( /(src=["\'](.*?)["\'])/ );

				if ( src && 1 < src.length ) {
					src = src[ 2 ];
				} else if ( -1 === values.element_content.indexOf( '<img' ) && '' !== values.element_content ) {
					src = values.element_content;
				}

				if ( 'undefined' !== typeof src && src && '' !== src ) {
					src             = src.replace( '&#215;', 'x' );
					contentAttr.src = src;
					values.pic_link = src;

					if ( 'no' === values.lightbox && '' !== values.link ) {
						contentAttr.title = title;
					} else {
						contentAttr.title = '';
					}

					contentAttr.alt = values.alt;
				}

				if ( '' !== values.aspect_ratio ) {
					contentAttr[ 'class' ] = 'img-with-aspect-ratio';
				}

				let imageIdSize, imageId, imageSize;
				if ( 'undefined' !== typeof values.image_id && '' !== values.image_id ) {
					const self = this;
					if ( -1 !== values.image_id.indexOf( '|' ) ) {
						imageIdSize = values.image_id.split( '|' );
						imageId     = imageIdSize[ 0 ];
						imageSize   = imageIdSize[ 1 ];
					} else {
						imageId = values.image_id;
					}

					const media = wp.media.attachment( imageId );
					if ( _.isUndefined( media.get( 'title' ) ) ) {
						media.fetch().then( function() {
							self.reRender();
							self._refreshJs();
						} );
					} else if ( imageSize && ! _.isUndefined( media.attributes.sizes ) ) {
						contentAttr.width  = media.attributes.sizes[ imageSize ].width;
						contentAttr.height = media.attributes.sizes[ imageSize ].height;
					} else {
						contentAttr.width  = media.attributes.width;
						contentAttr.height = media.attributes.height;
					}
				}

				return contentAttr;
			},

			/**
			 * Builds border radius.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {string}
			 */
			buildBorderRadius: function( values ) {
				var borderRadius = '';

				if ( values.borderradius && '' !== values.borderradius && 0 !== values.borderradius && '0' !== values.borderradius && '0px' !== values.borderradius ) {
					borderRadius += '-webkit-border-radius:' + values.borderradius + ';-moz-border-radius:' + values.borderradius + ';border-radius:' + values.borderradius + ';';
				}

				return borderRadius;
			},

			/**
			 * Builds image styles.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildImgStyles: function( atts ) {
				var imgStyles = '';
				if ( atts.borderRadius ) {
					imgStyles = ' style="' + atts.borderRadius + '"';
				}

				return imgStyles;
			},

			/**
			 * Builds element content.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 */
			buildElementContent: function( atts ) {
				var imgClasses = 'img-responsive',
					classes    = '',
					imageAtts  = '';

				if ( 'no_image_set' === atts.contentAttr ) {
					return;
				} else if ( _.FusionIsValidJSON( atts.contentAttr.src ) ) {
					atts.values.element_content = this.getLogoImages( atts );
				} else {
					imageAtts =  _.fusionGetAttributes( atts.contentAttr );
					imageAtts = -1 === imageAtts.indexOf( 'alt=' ) ? imageAtts + ' alt' : imageAtts;
					atts.values.element_content = '<img ' + imageAtts + ' />';
				}

				if ( '' !== atts.values.image_id ) {
					imgClasses += ' wp-image-' + atts.values.image_id;
				}

				// Get custom classes from the img tag.
				// eslint-disable-next-line no-useless-escape
				classes = atts.values.element_content.match( /(class=["\'](.*?)["\'])/ );

				if ( classes && 1 < classes.length ) {
					imgClasses += ' ' + classes[ 2 ];
				}

				imgClasses = 'class="' + imgClasses + '"';

				// Add custom and responsive class and the needed styles to the img tag.
				if ( classes && 'undefined' !== typeof classes[ 0 ] ) {
					atts.values.element_content = atts.values.element_content.replace( classes[ 0 ], imgClasses +  atts.imgStyles );
				} else {
					atts.values.element_content = atts.values.element_content.replace( '/>', imgClasses +  atts.imgStyles + '/>' );
				}

				// Set the lightbox image to the dedicated link if it is set.
				if ( '' !== atts.values.lightbox_image ) {
					atts.values.pic_link = atts.values.lightbox_image;
				}

				if ( -1 === jQuery.inArray( atts.values.caption_style, [ 'off', 'above', 'below' ] ) ) {
					atts.values.element_content += this.generateCaption( atts );
				}

				if ( 'yes' === atts.values.lightbox || atts.values.link ) {
					atts.values.element_content = '<a ' + _.fusionGetAttributes( atts.linkAttr ) + '>' + atts.values.element_content + '</a>';
				}

			},

			/**
			 * Builds liftup classes.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildLiftupClasses: function( atts ) {
				var liftupClasses = '',
					cid = this.model.get( 'cid' );

				if ( 'liftup' === atts.values.hover_type || ( 'bottomshadow' === atts.values.style_type && ( 'none' === atts.values.hover_type || 'zoomin' === atts.values.hover_type || 'zoomout' === atts.values.hover_type ) ) ) {
					if ( 'liftup' === atts.values.hover_type ) {
						liftupClasses = 'imageframe-liftup';
						if ( '' !== atts.values.aspect_ratio ) {
							liftupClasses += ' liftup-with-aspect-ratio';
						}
						if ( ! this.isFlex ) {
							if ( 'left' === atts.values.align ) {
								liftupClasses += ' fusion-imageframe-liftup-left';
							} else if ( 'right' === atts.values.align ) {
								liftupClasses += ' fusion-imageframe-liftup-right';
							}
						}

						if ( atts.borderRadius ) {
							liftupClasses += ' imageframe-cid' + cid;
						}

						if ( '' !== atts.values.hover_type && '' !== atts.values.mask ) {
							liftupClasses += ' awb-image-frame hover-with-mask';
						}

					} else {
						liftupClasses += 'fusion-image-frame-bottomshadow image-frame-shadow-cid' + cid;
					}

					liftupClasses += ' imageframe-cid' + cid;
				}

				return liftupClasses;
			},

			/**
			 * Builds mask styles.
			 *
			 * @since 7.6
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildMaskStyles: function( atts ) {
				var maskStyles = '',
					cid = this.model.get( 'cid' );


				if ( atts.values.mask ) {
					const maskUrl = 'custom' === atts.values.mask ? atts.values.custom_mask
									: `${builderConfig.fusion_builder_plugin_dir + '/assets/images/masks/'}${atts.values.mask}.svg`;

					let maskStyle      = '',
						hoverMaskStyle = '';

					if ( atts.values.mask_size ) {
						const maskSize = atts.values.mask_size;
						if ( 'fit' === maskSize ) {
							maskStyle += '-webkit-mask-size: contain;';
							maskStyle += 'mask-size: contain;';

							if ( 'liftup' ===  atts.values.hover_type ) {
								hoverMaskStyle += 'background-size: contain;';
							}
						}
						if ( 'fill' === maskSize ) {
							maskStyle += '-webkit-mask-size: cover;';
							maskStyle += 'mask-size: cover;';

							if ( 'liftup' === atts.values.hover_type ) {
								hoverMaskStyle += 'background-size: cover;';
							}
						}
						if ( 'custom' === maskSize ) {
							maskStyle += '-webkit-mask-size: ' + atts.values.mask_custom_size + ';';
							maskStyle += 'mask-size: ' + atts.values.mask_custom_size + ';';

							if ( 'liftup' === atts.values.hover_type ) {
								hoverMaskStyle += 'background-size: ' + atts.values.mask_custom_size + ';';
							}
						}
					}

					if ( atts.values.mask_position ) {
						const maskPosition = 'custom' !== atts.values.mask_position ? atts.values.mask_position.replace( '-', ' ' ) : atts.values.mask_custom_position;
							maskStyle += '-webkit-mask-position: ' + maskPosition + ';';
							maskStyle += 'mask-position: ' + maskPosition + ';';

							if ( 'liftup' === atts.values.hover_type ) {
								hoverMaskStyle += 'background-position: ' + maskPosition + ';';
							}
					}

					if ( atts.values.mask_repeat ) {
							maskStyle += '-webkit-mask-repeat: ' + atts.values.mask_repeat + ';';
							maskStyle += 'mask-repeat: ' + atts.values.mask_repeat + ';';

							if ( 'liftup' === atts.values.hover_type ) {
								hoverMaskStyle += 'background-repeat: ' + atts.values.mask_repeat + ';';
							}
					}
					maskStyles += maskUrl ? `.fusion-imageframe.imageframe-cid${cid} img {
						-webkit-mask-image: url(${maskUrl});
						mask-image: url(${maskUrl});
						${maskStyle}
					}` : '';

					maskStyles += maskUrl && 'liftup' ===  atts.values.hover_type ? `.imageframe-liftup.imageframe-cid${cid}:before {
						background-image: url(${maskUrl});
						${hoverMaskStyle}
					}` : '';
				}

				return maskStyles;
			},

			/**
			 * Builds aspect ratio styles.
			 *
			 * @since 3.6
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildAspectRatioStyles: function() {
				var selectors, aspectRatio, width, height;

				if ( '' ===  this.values.aspect_ratio ) {
					return '';
				}

				this.dynamic_css = {};
				this.baseSelector = '.fusion-imageframe.imageframe-cid' +  this.model.get( 'cid' );
				selectors = [ this.baseSelector + ' img' ];

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
			},

			/**
			 * Builds liftup styles.
			 *
			 * @since 2.0
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildLiftupStyles: function( atts ) {
				var liftupStyles = '<style>',
					cid = this.model.get( 'cid' ),
					styleColor;

				if ( atts.borderRadius ) {
					liftupStyles += '.imageframe-liftup.imageframe-cid' + cid + ':before{' + atts.borderRadius + '}';
				}

				if ( '' !== atts.values.max_width && '' === atts.values.aspect_ratio ) {
					liftupStyles += '.imageframe-cid' + cid + '{max-width:' + _.fusionGetValueWithUnit( atts.values.max_width ) + '}';
				}

				if ( 'liftup' === atts.values.hover_type || ( 'bottomshadow' === atts.values.style_type && ( 'none' === atts.values.hover_type || 'zoomin' === atts.values.hover_type || 'zoomout' === atts.values.hover_type ) ) ) {
					styleColor = ( 0 === atts.values.stylecolor.indexOf( '#' ) ) ? jQuery.AWB_Color( atts.values.stylecolor ).alpha( 0.4 ).toVarOrRgbaString() : jQuery.AWB_Color( atts.values.stylecolor ).toVarOrRgbaString();

					if ( 'liftup' === atts.values.hover_type ) {
						if ( 'bottomshadow' === atts.values.style_type ) {
							liftupStyles  += '.element-bottomshadow.imageframe-cid' + cid + ':before, .element-bottomshadow.imageframe-cid' + cid + ':after{';
							liftupStyles  += '-webkit-box-shadow: 0 17px 10px ' + styleColor + ';box-shadow: 0 17px 10px ' + styleColor + ';}';
						}
					} else {
						liftupStyles += '.imageframe-cid' + cid + '{display: inline-block}';
						liftupStyles  += '.element-bottomshadow.imageframe-cid' + cid + ':before, .element-bottomshadow.imageframe-cid' + cid + ':after{';
						liftupStyles  += '-webkit-box-shadow: 0 17px 10px ' + styleColor + ';box-shadow: 0 17px 10px ' + styleColor + ';}';
					}
				}

				liftupStyles += '</style>';

				return liftupStyles;
			},

			/**
			 * Builds margin styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildMarginStyles: function( atts ) {
				var extras = jQuery.extend( true, {}, fusionAllElements.fusion_imageframe.extras ),
					elementSelector = 'div.imageframe-cid' + this.model.get( 'cid' ),
					responsiveStyles = '';

				if ( 'liftup' !== atts.values.hover_type && 'bottomshadow' !== atts.values.style ) {
					elementSelector = 'span.imageframe-cid' + this.model.get( 'cid' );
				}

				_.each( [ 'large', 'medium', 'small' ], function( size ) {
					var marginStyles = '',
						marginKey;

					_.each( [ 'top', 'right', 'bottom', 'left' ], function( direction ) {

						// Margin.
						marginKey = 'margin_' + direction + ( 'large' === size ? '' : '_' + size );
						if ( '' !== atts.values[ marginKey ] ) {
							marginStyles += 'margin-' + direction + ' : ' + _.fusionGetValueWithUnit( atts.values[ marginKey ] ) + ';';
						}

					} );

					if ( '' === marginStyles ) {
						return;
					}

					// Wrap CSS selectors
					if ( '' !== marginStyles ) {
						marginStyles = elementSelector + ' {' + marginStyles + '}';
					}

					// Large styles, no wrapping needed.
					if ( 'large' === size ) {
						responsiveStyles += marginStyles;
					} else {
						// Medium and Small size screen styles.
						responsiveStyles += '@media only screen and (max-width:' + extras[ 'visibility_' + size ] + 'px) {' + marginStyles + '}';
					}
				} );

				responsiveStyles += this.buildCaptionStyles( atts );


				return responsiveStyles;
			},

			/**
			 * Builds caption styles.
			 *
			 * @since 3.5
			 * @param {Object} atts - The atts object.
			 * @return {string}
			 */
			buildCaptionStyles: function( atts ) {
				var css, media,
				responsive = '',
				selectors, font_styles, sides, marginName;

				this.dynamic_css  = {};
				this.baseSelector = '.fusion-imageframe.imageframe-cid' + this.model.get( 'cid' );

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
					selectors = [ this.baseSelector ];
					this.addCssProperty( selectors, 'background', atts.values.caption_overlay_color );
				}

				// Background color.
				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'schantel', 'dany' ] ) && ! this.isDefault( 'caption_background_color' ) ) {
					selectors = [ this.baseSelector + ' .awb-imageframe-caption-container .awb-imageframe-caption-text' ];
					this.addCssProperty( selectors, 'background', atts.values.caption_background_color );
				}
				// If no caption bg color then set bg same as overlay color.
				if ( 'dany' === atts.values.caption_style && this.isDefault( 'caption_background_color' ) ) {
					selectors = [ this.baseSelector + ' .awb-imageframe-caption-container .awb-imageframe-caption-text' ];
					this.addCssProperty( selectors, 'background', atts.values.caption_overlay_color );
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

				// when image max-width applied.
				if ( -1 !== jQuery.inArray( atts.values.caption_style, [ 'above', 'below' ] ) && '' !== atts.values.max_width ) {
					_.each( [ '', 'medium', 'small' ], function( size ) {
						var key = 'align' + ( '' === size ? '' : '_' + size ),
							align, props, styles;

						// Check for default value.
						if ( this.isDefault( key ) ) {
							return;
						}

						align = atts.values[ key ];
						if ( 'left' === align ) {
							align = 'right';
						} else if ( 'right' === align ) {
							align = 'left';
						}

						if ( 'center' === align ) {
							props = 'margin: 0 auto;';
						} else {
							props  = 'margin-' + align + ': auto;';
						}
						if ( 'small' === size ) {
							props = 'margin: 0;';
						}

						// Build responsive alignment.
						styles = this.baseSelector + ' .awb-imageframe-caption-container {' + props + '}';

						if ( '' === size ) {
							css += styles;
						} else {
							media       = '@media only screen and (max-width:' + this.extras[ 'visibility_' + size ] + 'px)';
							css += media + '{' + styles + '}';
						}
					}, this );
				}

				return css;
			},

			/**
			 * Generate logos images markup.
			 *
			 * @since 3.0
			 * @param {string} images - The atts object.
			 * @return {string}
			 */
			getLogoImages: function( atts ) {

				var data    	= JSON.parse( atts.contentAttr.src ),
					normalUrl 	= data[ 'default' ] && data[ 'default' ].normal &&  data[ 'default' ].normal.url,
					stickyUrl 	= data.sticky && data.sticky.normal && data.sticky.normal.url,
					mobileUrl	= data.mobile && data.mobile.normal && data.mobile.normal.url,
					content 	= '';

				if ( normalUrl ) {
					content += this.getLogoImage( atts, data[ 'default' ], 'fusion-standard-logo' );
				}
				if ( stickyUrl ) {
					content += this.getLogoImage( atts, data.sticky, 'fusion-sticky-logo' );
				}
				if ( mobileUrl ) {
					content += this.getLogoImage( atts, data.mobile, 'fusion-mobile-logo' );
				}

				return content;
			},

			/**
			 * Generate logos image markup.
			 *
			 * @since 3.0
			 * @param {Object} data      - The data object.
			 * @param {string} itemClass - Class for image.
			 * @return {string}
			 */
			getLogoImage: function( atts, data, itemClass ) {
				var content     = '',
					logoUrl    = '',
					logoData   = {
						'url': '',
						'srcset': '',
						'style': '',
						'retina_url': false,
						'width': '',
						'height': '',
						'class': itemClass
					},
					retinaUrl = ( data.retina && data.retina.url ) || '';

				logoUrl              = data.normal.url;
				logoData.srcset = logoUrl + ' 1x';

				// Get retina logo, if default one is not set.
				if ( '' === logoUrl ) {
					logoUrl            = retinaUrl;
					logoData.srcset = logoUrl + ' 1x';
					logoData.url    = logoUrl;
					logoData.width  = data.retina.width;
					logoData.height = data.retina.height;

					if ( '' !== logoData.width ) {
						logoData.style = 'max-height:' + logoData.height + 'px;height:auto;';
					}
				} else {
					logoData.url        = logoUrl;
					logoData.width      = ( data.normal && data.normal.width ) || '';
					logoData.height     = ( data.normal && data.normal.height ) || '';
				}

				if ( data.normal && '' !== data.normal && '' !== logoData.width && '' !== logoData.height ) {
					logoData.retina_url = retinaUrl;
					logoData.srcset    += ', ' + retinaUrl + ' 2x';

					if ( '' !== logoData.width ) {
						logoData.style = 'max-height:' + logoData.height + 'px;height:auto;';
					}
				}

				atts.attr[ 'class' ] += ' has-' + itemClass;

				content = '<img ' + _.fusionGetAttributes( logoData ) + ' />';

				return content;
			},

			/**
			 * Generate caption markup.
			 *
			 * @since 3.5
			 * @param {string} atts - The atts object.
			 * @return {string}
			 */
			generateCaption: function( atts ) {
				var self = this,
					content = '<div ' + _.fusionGetAttributes( this.buildCaptionAttr( atts.values ) ) + '><div class="awb-imageframe-caption">',
					title = '',
					caption = '',
					title_tag = '',
					image_id,
					media;

				if ( 'off' === atts.values.caption_style ) {
					return '';
				}

				// Get image title & caption from attachment attribute.
				if ( 'undefined' !== typeof atts.values.image_id && '' !== atts.values.image_id ) {
					if ( -1 !== atts.values.image_id.indexOf( '|' ) ) {
						image_id = atts.values.image_id.split( '|' )[ 0 ];
					} else {
						image_id = atts.values.image_id;
					}
					media = wp.media.attachment( image_id );
					if ( _.isUndefined( media.get( 'title' ) ) ) {
						media.fetch().then( function() {
							self.reRender();
							self._refreshJs();
						} );
					} else {
						title = media.get( 'title' );
						caption = media.get( 'caption' );
					}
				}

				// Otherwise get title & caption from custom option.
				if ( '' !== atts.values.caption_title ) {
					title = atts.values.caption_title;
				}
				if ( '' !== atts.values.caption_text ) {
					caption = atts.values.caption_text;
				}

				if ( '' !== title ) {
					title_tag = 'div' === atts.values.caption_title_tag ? 'div' : 'h' + atts.values.caption_title_tag;
					content += '<' + title_tag + ' class="awb-imageframe-caption-title">' + title + '</' + title_tag + '>';
				}
				if ( '' !== caption ) {
					content += '<p class="awb-imageframe-caption-text">' + caption + '</p>';
				}
				content += '</div></div>';

				return content;
			},

			/**
			 * Runs just after render on cancel.
			 *
			 * @since 3.5
			 * @return null
			 */
			beforeGenerateShortcode: function() {
				var elementType = this.model.get( 'element_type' ),
					options     = fusionAllElements[ elementType ].params,
					values      = jQuery.extend( true, {}, fusionAllElements[ elementType ].defaults, _.fusionCleanParameters( this.model.get( 'params' ) ) );

				if ( 'object' !== typeof options ) {
					return;
				}

				// If images needs replaced lets check element to see if we have media being used to add to object.
				if ( 'undefined' !== typeof FusionApp.data.replaceAssets && FusionApp.data.replaceAssets && ( 'undefined' !== typeof FusionApp.data.fusion_element_type || 'fusion_template' === FusionApp.getPost( 'post_type' ) ) ) {

					this.mapStudioImages( options, values );

					if ( '' !== values.element_content ) {
						// If its not within object already, add it.
						if ( 'undefined' === typeof FusionPageBuilderApp.mediaMap.images[ values.element_content ] ) {
								FusionPageBuilderApp.mediaMap.images[ values.element_content ] = true;
							}

						// Check if we have an image ID for this param.
						if ( 'undefined' !== typeof values.image_id && '' !== values.image_id )	{
							if ( 'object' !== typeof FusionPageBuilderApp.mediaMap.images[ values.element_content ] ) {
								FusionPageBuilderApp.mediaMap.images[ values.element_content ] = {};
							}
							FusionPageBuilderApp.mediaMap.images[ values.element_content ].image_id = values.image_id;
						}
					}
				}
			},

			/**
			 * Builds caption attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values object.
			 * @return {Object}
			 */
			buildCaptionAttr: function( values ) {
				// Caption Attributes.
				var attr = {
					'class': 'awb-imageframe-caption-container',
					'style': ''
				};

				if ( '' !== values.max_width && -1 !== jQuery.inArray( values.caption_style, [ 'above', 'below' ] ) && '' === values.aspect_ratio ) {
					attr.style += 'max-width:' + _.fusionGetValueWithUnit( values.max_width ) + ';';
				}

				return attr;
			}

		} );
	} );
}( jQuery ) );
