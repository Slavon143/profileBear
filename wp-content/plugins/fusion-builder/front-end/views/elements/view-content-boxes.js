/* global fusionAllElements */
var FusionPageBuilder = FusionPageBuilder || {};

( function() {

	jQuery( document ).ready( function() {

		// Content Boxes Parent View.
		FusionPageBuilder.fusion_content_boxes = FusionPageBuilder.ParentElementView.extend( {

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
				this.generateChildElements();
				this._refreshJs();
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

				// Create attribute objects.
				attributes.attr   = this.buildAttr( atts.values );

				// Build styles.
				attributes.styles = this.buildStyles( atts.values );

				return attributes;
			},

			/**
			 * Modify values.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {void}
			 */
			validateValues: function( values ) {

				// Backwards compatibility for when we had image width and height params.
				if ( 'undefined' !== typeof values.image_width ) {
					values.image_width = values.image_width ? values.image_width : '35';
				} else {
					values.image_width = values.image_max_width;
				}

				values.title_size            = _.fusionValidateAttrValue( values.title_size, 'px', false );
				values.icon_circle_radius    = _.fusionValidateAttrValue( values.icon_circle_radius, 'px' );
				values.icon_size             = _.fusionValidateAttrValue( values.icon_size, 'px' );
				values.margin_top            = _.fusionValidateAttrValue( values.margin_top, 'px' );
				values.margin_bottom         = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.margin_bottom         = _.fusionValidateAttrValue( values.margin_bottom, 'px' );
				values.circlebordersize      = _.fusionValidateAttrValue( values.circlebordersize, 'px' );
				values.outercirclebordersize = _.fusionValidateAttrValue( values.outercirclebordersize, 'px' );

				if ( values.linktarget ) {
					values.link_target = values.linktarget;
				}

				if ( 'timeline-vertical' === values.layout ) {
					values.columns = 1;
				}

				if ( 'timeline-vertical' === values.layout || 'timeline-horizontal' === values.layout ) {
					values.animation_delay     = 350;
					values.animation_speed     = 0.25;
					values.animation_type      = 'fade';
					values.animation_direction = '';
				}
			},

			/**
			 * Builds attributes.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {Object}
			 */
			buildAttr: function( values ) {
				var attr              = _.fusionVisibilityAtts( values.hide_on_mobile, {
						class: 'fusion-content-boxes content-boxes',
						style: ''
					} ),
					cid               = this.model.get( 'cid' ),
					totalNumOfColumns = 'undefined' !== typeof values.element_content ? values.element_content.match( /\[fusion_content_box ((.|\n|\r)*?)\]/g ) : 1,
					numOfColumns;

				totalNumOfColumns = null !== totalNumOfColumns ? totalNumOfColumns.length : 1;
				numOfColumns      = values.columns;

				if ( '' === numOfColumns || '0' === numOfColumns ) {
					numOfColumns = totalNumOfColumns;
					numOfColumns = Math.max( 6, numOfColumns );
				} else if ( 6 < numOfColumns ) {
					numOfColumns = 6;
				}

				values.columns = numOfColumns;

				attr[ 'class' ] += ' columns row';
				attr[ 'class' ] += ' fusion-columns-' + numOfColumns;
				attr[ 'class' ] += ' fusion-columns-total-' + totalNumOfColumns;
				attr[ 'class' ] += ' fusion-content-boxes-cid' + cid;
				attr[ 'class' ] += ' content-boxes-' + values.layout;
				attr[ 'class' ] += ' content-' + values.icon_align;

				if ( 'timeline-horizontal' === values.layout || 'clean-vertical' === values.layout ) {
					attr[ 'class' ] += ' content-boxes-icon-on-top';
				}

				if ( 'timeline-vertical' === values.layout ) {
					attr[ 'class' ] += ' content-boxes-icon-with-title';
				}
				if ( 'clean-horizontal' === values.layout ) {
					attr[ 'class' ] += ' content-boxes-icon-on-side';
				}

				if ( '' !== values.animation_delay ) {
					attr[ 'data-animation-delay' ] = values.animation_delay;
					attr[ 'class' ] += ' fusion-delayed-animation';
				}

				attr[ 'class' ] += ' fusion-child-element';

				attr.style += 'margin-top:' + values.margin_top + ';';
				attr.style += 'margin-bottom:' + values.margin_bottom + ';';

				if ( '' !== values[ 'class' ] ) {
					attr[ 'class' ] += ' ' + values[ 'class' ];
				}

				if ( '' !== values.id ) {
					attr.id = values.id;
				}

				attr = _.fusionAnimations( values, attr );

				return attr;
			},

			/**
			 * Builds styles.
			 *
			 * @since 2.0
			 * @param {Object} values - The values.
			 * @return {string}
			 */
			buildStyles: function( values ) {

				var styles                 = '',
					cid                    = this.model.get( 'cid' ),
					circleHoverAccentColor = '';

				if ( '' !== values.title_color ) {
					styles += '.fusion-content-boxes-cid' + cid + ' .heading .content-box-heading{color:' + values.title_color + ';}';
				}

				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .content-box-heading, .fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .heading-link .content-box-heading,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover .heading .content-box-heading,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover .heading .heading-link .content-box-heading,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover.link-area-box .fusion-read-more,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover.link-area-box .fusion-read-more::after,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover.link-area-box .fusion-read-more::before,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .fusion-read-more:hover:after,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .fusion-read-more:hover:before,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .fusion-read-more:hover,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover.link-area-box .fusion-read-more,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover.link-area-box .fusion-read-more::after,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover.link-area-box .fusion-read-more::before,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover .icon .circle-no, .fusion-content-boxes-cid' + cid + ' .heading .heading-link:hover .content-box-heading { color: ' + values.hover_accent_color + ';}';

				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover .icon .circle-no {color: ' + values.hover_accent_color + ' !important;}';
				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box.link-area-box-hover .fusion-content-box-button {';
				styles += 'background: ' + fusionAllElements.fusion_button.defaults.button_gradient_top_color_hover + ';';
				styles += 'color: ' +  fusionAllElements.fusion_button.defaults.button_accent_hover_color + ';';

				if ( fusionAllElements.fusion_button.defaults.button_gradient_top_color_hover !== fusionAllElements.fusion_button.defaults.button_gradient_bottom_color_hover ) {
					styles += 'background-image: -webkit-gradient( linear, left bottom, left top, from( ' + fusionAllElements.fusion_button.defaults.button_gradient_bottom_color_hover + ' ), to( ' + fusionAllElements.fusion_button.defaults.button_gradient_top_color_hover + ' ) );';
					styles += 'background-image: linear-gradient( to top, ' + fusionAllElements.fusion_button.defaults.button_gradient_bottom_color_hover + ', ' + fusionAllElements.fusion_button.defaults.button_gradient_top_color_hover + ' )';
				}

				styles += '}';
				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box.link-area-box-hover .fusion-content-box-button .fusion-button-text {';
				styles += 'color: ' + fusionAllElements.fusion_button.defaults.button_accent_hover_color + ';';
				styles += '}';

				circleHoverAccentColor = values.hover_accent_color;

				if ( 'transparent' === values.circlecolor || 0 === jQuery.AWB_Color( values.circlecolor ).alpha() || 'no' === values.icon_circle ) {
					circleHoverAccentColor = 'transparent';
				}

				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .icon > span,';
				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .icon i.circle-yes { background-color: ' + circleHoverAccentColor + ' !important;}';

				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover .heading .icon > span,';
				styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover .heading .icon i.circle-yes { border-color: ' + values.hover_accent_color + ' !important; }';

				if ( 'pulsate' === values.icon_hover_type && '' !== values.hover_accent_color ) {

					styles += '.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover.icon-hover-animation-pulsate .fontawesome-icon:after,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover.icon-hover-animation-pulsate .fontawesome-icon:after,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-link-icon-hover.icon-wrapper-hover-animation-pulsate .icon span:after,.fusion-content-boxes-cid' + cid + ' .fusion-content-box-hover .link-area-box-hover.icon-wrapper-hover-animation-pulsate .icon span:after {-webkit-box-shadow:0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px ' + values.hover_accent_color + ', 0 0 0 10px rgba(255,255,255,0.5);-moz-box-shadow:0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px ' + values.hover_accent_color + ', 0 0 0 10px rgba(255,255,255,0.5);box-shadow: 0 0 0 2px rgba(255,255,255,0.1), 0 0 10px 10px ' + values.hover_accent_color + ', 0 0 0 10px rgba(255,255,255,0.5);}';
				}

				if ( 'clean-horizontal' === values.layout || 'clean-vertical' === values.layout ) {
					styles += '.fusion-content-boxes-cid' + cid + '.fusion-columns-' + values.columns + ' .content-box-column:nth-of-type(' + values.columns + 'n) {border-right-width:1px;}';
				}

				return styles;
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

				async    = ( 'undefined' === typeof async ) ? true : async;
				reRender = ( 'undefined' === typeof reRender ) ?  true : reRender;

				view.initialData = true;

				_.each( childrenData, function( child ) {
					var params  = ( 'undefined' !== typeof child.get ) ? child.get( 'params' ) : child.params,
						cid     = ( 'undefined' !== typeof child.get ) ? child.get( 'cid' ) : child.cid,
						imageId = 'undefined' !== typeof params.image_id && '' !== params.image_id ? params.image_id : false,
						image   = 'undefined' !== typeof params.image && '' !== params.image ? params.image : false;

					// Has neither url or ID set.
					if ( ! imageId && ! image ) {
						return;
					}

					// if it has image id set and available, no need to progress.
					if ( imageId && 'undefined' !== typeof view.imageMap[ imageId ] ) {
						return;
					}

					// if it has image url set and available, no need to progress.
					if ( image && 'undefined' !== typeof view.imageMap[ image ] ) {
						return;
					}

					// Made it this far we need to get image data.
					queryData[ cid ] = params;
				} );

				// Send this data with ajax or rest.
				if ( ! _.isEmpty( queryData ) ) {
					jQuery.ajax( {
						async: async,
						url: window.fusionAppConfig.ajaxurl,
						type: 'post',
						dataType: 'json',
						data: {
							action: 'get_fusion_content_boxes_children_data',
							children: queryData,
							fusion_load_nonce: window.fusionAppConfig.fusion_load_nonce
						}
					} )
					.done( function( response ) {
						view.updateImageMap( response );

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
				var self = this;

				_.each( images, function( imageData, image ) {
					if ( 'undefined' === typeof self.imageMap[ image ] ) {
						self.imageMap[ image ] = imageData;
					}
				} );
			}
		} );
	} );
}( jQuery ) );
